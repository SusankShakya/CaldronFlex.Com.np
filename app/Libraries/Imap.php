<?php

namespace App\Libraries;

use App\Libraries\Ticket;

class Imap {

    private $Settings_model;

    public function __construct() {
        $this->Settings_model = model('App\Models\Settings_model');

        require_once(APPPATH . "ThirdParty/Imap/EmailReplyParser/vendor/autoload.php"); //load EmailReplyParser resources
        require_once(APPPATH . "ThirdParty/Imap/mail-mime-parser/autoload.php"); //load mail-mime-parser resources

        if (version_compare(PHP_VERSION, '8.3.0') > 0) { // for php 8.3 and above
            require_once(APPPATH . "ThirdParty/Imap/ddeboer-imap-v1-21-0/autoload.php");
        } else { // for php 8.2 and below
            require_once(APPPATH . "ThirdParty/Imap/ddeboer-imap-php8/vendor/autoload.php");
        }
    }

    function authorize_imap_and_get_inbox($is_cron = false) {
        $host = get_setting("imap_host");
        $port = get_setting("imap_port");
        $encryption = get_setting('imap_encryption');
        $email_address = get_setting("imap_email");
        $password = decode_password(get_setting('imap_password'), "imap_password");

        $server = new \Ddeboer\Imap\Server($host, $port, $encryption);

        //reset failed login attempts count after running from settings page
        if (!$is_cron) {
            $this->Settings_model->save_setting("imap_failed_login_attempts_count", 0);
        }

        //try to login 10 times and save the count on each load of cron job
        //after a success login, reset the count to 0
        try {
            $connection = $server->authenticate($email_address, $password);

            //the credentials is valid. store to settings that it's authorized
            $this->Settings_model->save_setting("imap_authorized", 1);

            //reset failed login attempts count
            $this->Settings_model->save_setting("imap_failed_login_attempts_count", 0);

            return $connection;
        } catch (\Exception $exc) {
            //the credentials is invalid, increase attempt count and store
            $attempts_count = get_setting("imap_failed_login_attempts_count");
            if ($is_cron) {
                $attempts_count = $attempts_count ? ($attempts_count * 1 + 1) : 1;
                $this->Settings_model->save_setting("imap_failed_login_attempts_count", $attempts_count);
            }

            //log error for every exception
            log_message('error', $exc);

            if ($attempts_count === 10 || !$is_cron) {
                //flag it's unauthorized, only after 10 failed attempts
                $this->Settings_model->save_setting("imap_authorized", 0);
            }

            return false;
        }
    }

    public function run_imap() {
        $connection = $this->authorize_imap_and_get_inbox(true);
        if (!$connection) {
            return false;
        }

        $mailbox_name = "";

        if ($connection->hasMailbox("INBOX")) {
            $mailbox_name = "INBOX";
        } else if ($connection->hasMailbox("Inbox")) {
            $mailbox_name = "Inbox";
        } else if ($connection->hasMailbox("inbox")) {
            $mailbox_name = "inbox";
        }

        if (!$mailbox_name) {
            log_message('error', 'IMAP integration will not work since there is no mailbox named INBOX');
            return false;
        }

        $mailbox = $connection->getMailbox($mailbox_name); //get mails of inbox only

        $messages = $mailbox->getMessages();

        $email_address = get_setting("imap_email");
        $last_seen_settings_name = "last_seen_imap_message_number_" . $email_address;
        $saved_last_message = get_setting($last_seen_settings_name);
        $saved_last_message = $saved_last_message ? $saved_last_message : 0;

        $last_number = 0;
        foreach ($messages as $key => $message) {

            $last_number = $messages[$key];
            if ($saved_last_message > $last_number) {
                continue; //Skip already seen messages Nothing to do there.
            }

            if ($message->isSeen()) {
                continue; //create tickets for unread mails only
            }

            $Ticket = new Ticket();
            $data = $this->_prepare_data($message);
            $Ticket->create_ticket_from_imap($data);

            //mark the mail as read
            $message->markAsSeen();
        }

        $this->Settings_model->save_setting($last_seen_settings_name, $last_number);
    }

    private function _prepare_data($message_info) {
        $data = array();
        $data["email_address"] = $message_info->getFrom()->getAddress();
        $data["email_subject"] = $message_info->getSubject();
        $data["email_sender_name"] = $message_info->getFrom()->getName();
        $data["email_content"] = $this->_prepare_email_content($message_info);
        $data["email_reply_content"] = $this->_prepare_replying_message($data["email_content"]);
        $data["email_attachments"] = $this->_prepare_attachment_data_of_mail($message_info);

        return $data;
    }

    private function _prepare_email_content($message_info) {
        $email_content = $message_info->getBodyText();
        if ($email_content) {
            return $email_content;
        }

        //parse email content if the predefined method returns empty
        $encoding_type = $message_info->getEncoding();
        $raw_content = $message_info->getRawMessage();

        //parse with another library
        try {
            $mail_mime_parser = \ZBateson\MailMimeParser\Message::from($raw_content, false);
            $email_content = $mail_mime_parser->getHtmlContent();

            //get content inside body tag only if it exists
            if ($email_content) {
                preg_match("/<body[^>]*>(.*?)<\/body>/is", $email_content, $body_matches);
                $email_content = isset($body_matches[1]) ? $body_matches[1] : $email_content;
            }
        } catch (\Exception $ex) {
        }

        if (!$email_content) {
            //get content after X-Yandex-Forward: random strings (32) + new lines
            $email_content = substr($raw_content, strpos($raw_content, "X-Yandex-Forward") + 52);

            //parse for different encoding types
            if ($encoding_type == "7bit") {
                $email_content = quoted_printable_decode($email_content);
            } else if ($encoding_type == "base64") {
                $email_content = imap_base64($email_content);
            } else if ($encoding_type == "quoted-printable") {
                $email_content = imap_qprint($email_content);
            }
        }

        return $email_content;
    }

    private function _prepare_replying_message($email_content = "") {
        try {
            $reply_parser = new \EmailReplyParser\EmailReplyParser();
            return $reply_parser->parseReply($email_content);
        } catch (\Exception $ex) {
            return "";
        }
    }

    //download attached files to local
    private function _prepare_attachment_data_of_mail($message_info = null) {
        if (!$message_info) {
            return false;
        }

        $files_data = array();
        $attachments = $message_info->getAttachments();

        foreach ($attachments as $attachment) {
            //move files to the directory
            $file_data = move_temp_file($attachment->getFilename(), get_setting("timeline_file_path"), "imap_ticket", NULL, "", $attachment->getDecodedContent());
            if (!$file_data) {
                continue;
            }

            array_push($files_data, $file_data);
        }

        return $files_data;
    }
}
