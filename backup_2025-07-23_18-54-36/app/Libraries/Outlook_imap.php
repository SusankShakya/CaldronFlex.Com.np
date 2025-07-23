<?php

namespace App\Libraries;

use App\Libraries\Ticket;

class Outlook_imap {

    private $Settings_model;
    private $client_id = "";
    private $client_secret = "";
    private $login_url = "";
    private $graph_url = "";
    private $redirect_uri = "";

    public function __construct() {
        $this->Settings_model = model('App\Models\Settings_model');
        $this->client_id = get_setting("outlook_imap_client_id");
        $this->client_secret = decode_id(get_setting('outlook_imap_client_secret'), "outlook_imap_client_secret");
        $this->login_url = "https://login.microsoftonline.com/common/oauth2/v2.0";
        $this->graph_url = "https://graph.microsoft.com/beta/me/";
        $this->redirect_uri = get_uri("microsoft_api/save_outlook_imap_access_token");

        //load EmailReplyParser resources
        require_once(APPPATH . "ThirdParty/Imap/EmailReplyParser/vendor/autoload.php");
    }

    public function run_imap() {
        $messages = $this->do_request("GET", 'mailFolders/inbox/messages');

        foreach ($messages->value as $message) {
            if ($message->isRead) {
                continue; //create tickets for unread mails only
            }

            $Ticket = new Ticket();
            $data = $this->_prepare_data($message);
            $Ticket->create_ticket_from_imap($data);

            //mark the mail as read
            $this->do_request("PATCH", "messages/$message->id", array("isRead" => true));
        }
    }

    private function _prepare_data($message_info) {
        $data = array();
        $data["email_address"] = $message_info->from->emailAddress->address;
        $data["email_subject"] = $message_info->subject;
        $data["email_sender_name"] = $message_info->from->emailAddress->name;
        $data["email_content"] = $this->_prepare_email_content($message_info);
        $data["email_reply_content"] = $this->_prepare_replying_message($data["email_content"]);
        $data["email_attachments"] = $this->_prepare_attachment_data_of_mail($message_info);

        return $data;
    }

    //authorize connection
    public function authorize() {
        $url = "$this->login_url/authorize?";
        $auth_array = array(
            "client_id" => $this->client_id,
            "response_type" => "code",
            "redirect_uri" => $this->redirect_uri,
            "response_mode" => "query",
            "scope" => "offline_access%20user.read%20IMAP.AccessAsUser.All%20Mail.ReadWrite",
        );

        foreach ($auth_array as $key => $value) {
            $url .= "$key=$value";

            if ($key !== "scope") {
                $url .= "&";
            }
        }

        app_redirect($url, true);
    }

    private function common_error_handling_for_curl($result, $err, $decode_result = true) {
        if ($decode_result) {
            try {
                $result = json_decode($result);
            } catch (\Exception $ex) {
                echo json_encode(array("success" => false, 'message' => $ex->getMessage()));
                log_message('error', $ex); //log error for every exception
                exit();
            }
        }

        if ($err) {
            //got curl error
            echo json_encode(array("success" => false, 'message' => "cURL Error #:" . $err));
            log_message('error', $err); //log error for every exception
            exit();
        }

        if (isset($result->error_description) && $result->error_description) {
            //got error message from curl
            echo json_encode(array("success" => false, 'message' => $result->error_description));
            log_message('error', $result->error_description); //log error for every exception
            exit();
        }

        if (
            isset($result->error) && $result->error &&
            isset($result->error->message) && $result->error->message &&
            isset($result->error->code) && $result->error->code !== "InvalidAuthenticationToken"
        ) {
            //got error message from curl
            echo json_encode(array("success" => false, 'message' => $result->error->message));
            log_message('error', $result->error->message); //log error for every exception
            exit();
        }

        return $result;
    }

    //fetch access token with auth code and save to database
    public function save_access_token($code, $is_refresh_token = false) {
        $fields = array(
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "redirect_uri" => $this->redirect_uri,
            "scope" => "IMAP.AccessAsUser.All Mail.ReadWrite",
            "grant_type" => "authorization_code",
        );

        if ($is_refresh_token) {
            $fields["refresh_token"] = $code;
            $fields["grant_type"] = "refresh_token";
        } else {
            $fields["code"] = $code;
        }

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, "$this->login_url/token");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
        ));

        //So that curl_exec returns the contents of the cURL;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $result = $this->common_error_handling_for_curl($result, $err);

        if (!(
            (!$is_refresh_token && isset($result->access_token) && isset($result->refresh_token)) ||
            ($is_refresh_token && isset($result->access_token))
        )) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            exit();
        }

        if ($is_refresh_token) {
            //while refreshing token, refresh_token value won't be available
            $result->refresh_token = $code;
        }

        // Save the token to database
        $new_access_token = json_encode($result);
        if (!$new_access_token) {
            return false;
        }

        $new_access_token = encode_id($new_access_token, "outlook_imap_oauth_access_token");
        $this->Settings_model->save_setting('outlook_imap_oauth_access_token', $new_access_token);

        if (!$is_refresh_token) {
            //store email address for the first time
            $user_info = $this->do_request("GET");
            if (isset($user_info->userPrincipalName) && $user_info->userPrincipalName) {
                $this->Settings_model->save_setting('outlook_imap_email', $user_info->userPrincipalName);
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
                exit();
            }
        }

        //got the valid access token. store to setting that it's authorized
        $this->Settings_model->save_setting('imap_authorized', "1");
    }

    private function headers($access_token) {
        return array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        );
    }

    private function do_request($method, $path = "", $body = array(), $decode_result = true) {
        if (is_array($body)) {
            // Treat an empty array in the body data as if no body data was set
            if (!count($body)) {
                $body = '';
            } else {
                $body = json_encode($body);
            }
        }

        $oauth_access_token = $this->Settings_model->get_setting('outlook_imap_oauth_access_token');
        $oauth_access_token = decode_id($oauth_access_token, "outlook_imap_oauth_access_token");
        $oauth_access_token = json_decode($oauth_access_token);

        $method = strtoupper($method);
        $url = $this->graph_url . $path;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers($oauth_access_token->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (in_array($method, array('DELETE', 'PATCH', 'POST', 'PUT', 'GET'))) {

            // All except DELETE can have a payload in the body
            if ($method != 'DELETE' && strlen($body)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $result = $this->common_error_handling_for_curl($result, $err, $decode_result);

        if (isset($result->error->code) && $result->error->code === "InvalidAuthenticationToken") {
            //access token is expired
            $this->save_access_token($oauth_access_token->refresh_token, true);
            return $this->do_request($method, $path, $body, $decode_result);
        }

        return $result;
    }

    private function _prepare_email_content($message_info) {
        $email_content = $message_info->body->content;

        try {
            //get content inside body tag only if it exists
            if ($email_content) {
                preg_match("/<body[^>]*>(.*?)<\/body>/is", $email_content, $body_matches);
                $email_content = isset($body_matches[1]) ? $body_matches[1] : $email_content;
            }
        } catch (\Exception $ex) {
        }

        return $email_content;
    }

    private function _prepare_replying_message_by_library($message = "") {
        try {
            $reply_parser = new \EmailReplyParser\EmailReplyParser();
            return $reply_parser->parseReply($message);
        } catch (\Exception $ex) {
            return "";
        }
    }

    private function _prepare_replying_message($message = "") {
        try {

            if (get_setting("parse_outlook_email_reply_by_library")) {
                return $this->_prepare_replying_message_by_library($message);
            } else {

                // Remove any inline images or attachments
                $message = preg_replace('/<img[^>]+>/i', '', $message);

                // Remove quoted text and signatures
                $message = preg_replace('/<div class="gmail_quote">.*<\/div>|<div class="AppleMailSignature">.*<\/div>|<div class="OutlookMessageHeader">.*<\/div>/s', '', $message);

                // Remove everything inside <!-- -->
                $message = preg_replace('/<!--(.*?)-->/s', '', $message);

                $pattern = '/<div style="border:none; border-top:solid #E1E1E1 1\.0pt; padding:3\.0pt 0cm 0cm 0cm">.*<\/div>/s';
                $message = preg_replace($pattern, '', $message);

                // Trim leading and trailing whitespace
                return trim($message);
            }
        } catch (\Exception $ex) {
            return "";
        }
    }

    //download attached files to local
    private function _prepare_attachment_data_of_mail($message_info = null) {
        $files_data = array();
        if (!$message_info) {
            return $files_data;
        }

        $attachments = $this->do_request("GET", "messages/$message_info->id/attachments");
        if (!$attachments) {
            return $files_data;
        }

        foreach ($attachments->value as $attachment) {
            $content = $this->do_request("GET", "messages/$message_info->id/attachments/$attachment->id/" . '$value', array(), false);
            $file_data = move_temp_file($attachment->name, get_setting("timeline_file_path"), "imap_ticket", NULL, "", $content);
            if (!$file_data) {
                continue;
            }

            if ($attachment->contentId) {
                $file_data["content_id"] = $attachment->contentId;
            }

            array_push($files_data, $file_data);
        }

        return $files_data;
    }
}
