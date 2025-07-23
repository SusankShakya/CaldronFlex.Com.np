<?php

namespace App\Libraries;

use Google\Service\Gmail as Google_Service_Gmail;
use Google\Service\Gmail\Message as Google_Service_Gmail_Message;
use App\Libraries\Gmail_mime_email;
use App\Libraries\Google_Trait;

class Gmail_smtp {

    use Google_Trait;
    private $Settings_model;

    public function __construct() {
        $this->Settings_model = model("App\Models\Settings_model");
        $this->set_type("smtp");

        // Load Google API client
        require_once(APPPATH . "ThirdParty/Google/2-18-3/autoload.php");
    }

    function send_app_mail($to, $subject, $message, $options = array(), $convert_message_to_html = true) {
        try {
            // Get the Gmail client
            $client = $this->_get_client_credentials();
            $this->_check_access_token($client);

            // Initialize Gmail service
            $gmail_service = new Google_Service_Gmail($client);

            // Create and configure the email using Gmail_mime_email
            $email = new Gmail_mime_email();
            $email->initialize(array(
                'charset' => 'utf-8',
                'mailType' => 'html',
                'protocol' => 'smtp',
            ));

            $email->clear(true); //clear previous message and attachment
            $email->setNewline("\r\n");
            $email->setCRLF("\r\n");

            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($convert_message_to_html ? htmlspecialchars_decode($message) : $message);

            // Fetch default email and name from Gmail profile and sendAs settings
            $google_oauth = new \Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            $email->setFrom($google_account_info->email, ($google_account_info->given_name . " " . $google_account_info->family_name));

            // Set reply-to if provided
            $reply_to = get_array_value($options, 'reply_to');
            if ($reply_to) {
                $email->setReplyTo($reply_to);
            }

            // Add CC if present
            $cc = get_array_value($options, 'cc');
            if ($cc) {
                $email->setCC($cc);
            }

            // Add BCC if present
            $bcc = get_array_value($options, 'bcc');
            if ($bcc) {
                $email->setBCC($bcc);
            }

            // Add attachments if any
            $attachments = get_array_value($options, "attachments");
            if (is_array($attachments)) {
                foreach ($attachments as $value) {
                    $file_path = get_array_value($value, "file_path");
                    $file_name = get_array_value($value, "file_name");
                    $email->attach(trim($file_path), "attachment", $file_name);
                }
            }

            // Get the raw MIME message and encode it for Gmail API
            $raw_message = $email->prepareForGmail();

            // Create and send the Gmail message
            $gmail_message = new Google_Service_Gmail_Message();
            $gmail_message->setRaw($raw_message);

            $gmail_service->users_messages->send('me', $gmail_message);

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Gmail API Error: ' . $e->getMessage());
            log_message('error', 'Trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}
