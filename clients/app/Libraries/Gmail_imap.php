<?php

namespace App\Libraries;

use Google\Service\Gmail as Google_Service_Gmail;
use Google\Service\Gmail\ModifyMessageRequest as Google_Service_Gmail_ModifyMessageRequest;
use App\Libraries\Google_Trait;
use App\Libraries\Ticket;

class Gmail_imap {

    use Google_Trait;

    private $client;
    private $service;
    private $Settings_model;

    public function __construct() {
        $this->Settings_model = model("App\Models\Settings_model");
        $this->set_type("imap");

        // Load Google API client
        require_once(APPPATH . "ThirdParty/Google/2-18-3/autoload.php");
        require_once(APPPATH . "ThirdParty/Imap/mail-mime-parser/autoload.php"); //load mail-mime-parser resources
        require_once(APPPATH . "ThirdParty/Imap/EmailReplyParser/vendor/autoload.php"); //load EmailReplyParser resources
    }

    // Initialize Gmail service
    private function _init_service($client) {
        $this->client = $client;
        $this->service = new Google_Service_Gmail($this->client);
    }

    // Process emails from Gmail
    public function run_imap() {
        $client = $this->_get_client_credentials();
        $this->_check_access_token($client);
        $this->_init_service($client);

        try {
            // Get unread messages
            $pageToken = null;
            $messages = [];
            $opt_param = [
                'maxResults' => 50,
                'labelIds' => ['INBOX', 'UNREAD']
            ];

            do {
                if ($pageToken) {
                    $opt_param['pageToken'] = $pageToken;
                }

                $messagesResponse = $this->service->users_messages->listUsersMessages('me', $opt_param);
                $messages = array_merge($messages, $messagesResponse->getMessages());
                $pageToken = $messagesResponse->getNextPageToken();
            } while ($pageToken);

            $last_seen_settings_name = "last_seen_gmail_message_id";
            $saved_last_message = get_setting($last_seen_settings_name);

            foreach ($messages as $message) {
                $messageId = $message->getId();

                if ($saved_last_message && $messageId <= $saved_last_message) {
                    continue;
                }

                $this->_process_single_message($messageId);
                $this->Settings_model->save_setting($last_seen_settings_name, $messageId);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Gmail IMAP Error: ' . $e->getMessage());
            return false;
        }
    }

    // Process a single email message
    private function _process_single_message($messageId) {
        try {
            // Get the full raw message
            $message = $this->service->users_messages->get('me', $messageId, ['format' => 'raw']);

            $Ticket = new Ticket();
            $data = $this->_prepare_data($message);
            $Ticket->create_ticket_from_imap($data);

            // Mark as read
            $mods = new Google_Service_Gmail_ModifyMessageRequest();
            $mods->setRemoveLabelIds(['UNREAD']);
            $this->service->users_messages->modify('me', $messageId, $mods);
        } catch (\Exception $e) {
            log_message('error', 'Error processing Gmail message ' . $messageId . ': ' . $e->getMessage());
        }
    }

    // Decode base64 URL safe string
    private function _decode_body($data) {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $data = base64_decode($data);
        return $data;
    }

    // Parse raw email message to extract headers
    private function _parse_raw_message($raw_message) {
        $result = [
            'subject' => '',
            'from_email' => '',
            'from_name' => '',
            'date' => ''
        ];

        // Split headers and body
        $header_parts = preg_split("/\r?\n\r?\n/", $raw_message, 2);
        $headers = get_array_value($header_parts, 0, '');

        // Parse headers
        $header_lines = preg_split("/\r?\n/", $headers);

        foreach ($header_lines as $line) {
            if (preg_match('/^Subject:\s*(.*)/i', $line, $matches)) {
                $result['subject'] = iconv_mime_decode(trim(get_array_value($matches, 1, '')), 0, 'UTF-8');
            } elseif (preg_match('/^From:\s*(.*)/i', $line, $matches)) {
                $from = trim(get_array_value($matches, 1, ''));
                if (preg_match('/(.*)<(.*)>/', $from, $from_matches)) {
                    $result['from_name'] = trim(trim(get_array_value($from_matches, 1, '')), ' \"');
                    $result['from_email'] = trim(get_array_value($from_matches, 2, ''));
                } else {
                    $result['from_email'] = $from;
                }
            } elseif (preg_match('/^Date:\s*(.*)/i', $line, $matches)) {
                $result['date'] = trim(get_array_value($matches, 1, ''));
            }
        }

        return $result;
    }

    private function _prepare_data($message_info) {
        // Get the raw email content
        $raw_content = $message_info->getRaw();
        $decoded_raw = $this->_decode_body($raw_content);

        // Get message details from the raw content
        $parsed_message = $this->_parse_raw_message($decoded_raw);

        $mail_mime_parser = \ZBateson\MailMimeParser\Message::from($decoded_raw, false);
        $email_content = $mail_mime_parser->getHtmlContent();

        //get content inside body tag only if it exists
        if ($email_content) {
            preg_match("/<body[^>]*>(.*?)<\/body>/is", $email_content, $body_matches);
            $email_content = isset($body_matches[1]) ? $body_matches[1] : $email_content;
        }

        $data = array();
        $data["email_address"] = get_array_value($parsed_message, 'from_email');
        $data["email_subject"] = get_array_value($parsed_message, 'subject');
        $data["email_sender_name"] = get_array_value($parsed_message, 'from_name');
        $data["email_content"] = $email_content;
        $data["email_reply_content"] = $this->_prepare_replying_message($email_content);
        $data["email_attachments"] = $this->_prepare_attachment_data($message_info);

        return $data;
    }

    private function _prepare_replying_message($email_content = "") {
        try {
            $reply_parser = new \EmailReplyParser\EmailReplyParser();
            return $reply_parser->parseReply($email_content);
        } catch (\Exception $ex) {
            return "";
        }
    }

    // Prepare attachment data from Gmail message
    private function _prepare_attachment_data($message) {
        $files_data = [];

        try {
            // Get the full message with attachments
            $message = $this->service->users_messages->get('me', $message->getId(), ['format' => 'full']);
            $payload = $message->getPayload();

            if (!$payload || !method_exists($payload, 'getParts')) {
                return $files_data;
            }

            $parts = $payload->getParts();

            // Process each part of the message
            foreach ($parts as $part) {
                $filename = $part->getFilename();

                // Skip if not an attachment
                if (empty($filename) && !$this->_is_attachment($part)) {
                    continue;
                }


                // Get attachment content
                $body = $part->getBody();
                if ($body) {
                    $file_content = '';
                    if ($body->getAttachmentId()) {
                        $file_content = $this->_get_attachment_content($message->getId(), $body->getAttachmentId());
                    } else if ($body->getData()) {
                        $file_content = $this->_decode_body($body->getData());
                    }

                    if (!empty($file_content)) {
                        $filename = $filename ?: 'attachment_' . uniqid();
                        $filename = str_replace("/", "-", $filename);

                        // Save file to the same directory structure as IMAP
                        $file_data = move_temp_file($filename, get_setting("timeline_file_path"), "imap_ticket", NULL, "", $file_content);

                        if ($file_data) {
                            $files_data[] = $file_data;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error preparing attachments: ' . $e->getMessage());
        }

        return $files_data;
    }

    // Check if a message part is an attachment
    private function _is_attachment($part) {
        $headers = $part->getHeaders();
        if ($headers) {
            foreach ($headers as $header) {
                if (strtolower($header->getName()) === 'content-disposition') {
                    return strpos(strtolower($header->getValue()), 'attachment') !== false;
                }
            }
        }
        return false;
    }

    // Get attachment content
    private function _get_attachment_content($messageId, $attachmentId) {
        try {
            $attachment = $this->service->users_messages_attachments->get('me', $messageId, $attachmentId);
            return $this->_decode_body($attachment->getData());
        } catch (\Exception $e) {
            log_message('error', 'Error getting attachment: ' . $e->getMessage());
            return '';
        }
    }
}
