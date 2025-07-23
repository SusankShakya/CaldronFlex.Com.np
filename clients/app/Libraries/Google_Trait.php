<?php

namespace App\Libraries;

use Google\Service\Gmail as Google_Service_Gmail;

trait Google_Trait {

    private $type;

    //authorize connection
    public function authorize() {
        $client = $this->_get_client_credentials();
        $this->_check_access_token($client, true);
    }

    public function set_type($type) {
        $this->type = $type;
    }

    //check access token
    private function _check_access_token($client, $redirect_to_settings = false) {
        //load previously authorized token from database, if it exists.
        $oauth_access_token_setting_name = "gmail_" . $this->type . "_oauth_access_token";
        $accessToken = $this->Settings_model->get_setting($oauth_access_token_setting_name);
        if ($accessToken && !$redirect_to_settings) {
            $accessToken = decode_id($accessToken, $oauth_access_token_setting_name);
            $client->setAccessToken(json_decode($accessToken, true));
        }

        $settings_url = "ticket_types/index/imap";
        if ($this->type == "smtp") {
            $settings_url = "settings/email";
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                if ($redirect_to_settings) {
                    app_redirect($settings_url);
                }
            } else {
                $authUrl = $client->createAuthUrl();
                app_redirect($authUrl, true);
            }
        } else {
            if ($redirect_to_settings) {
                app_redirect($settings_url);
            }
        }
    }

    //fetch access token with auth code and save to database
    public function save_access_token($auth_code) {
        $client = $this->_get_client_credentials();

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($auth_code);

        $error = get_array_value($accessToken, "error");

        if ($error)
            die($error);


        $client->setAccessToken($accessToken);

        // Save the token to database
        $new_access_token = json_encode($client->getAccessToken());

        if ($new_access_token) {
            $oauth_access_token_setting_name = "gmail_" . $this->type . "_oauth_access_token";
            $new_access_token = encode_id($new_access_token, $oauth_access_token_setting_name);
            $this->Settings_model->save_setting($oauth_access_token_setting_name, $new_access_token);
            $this->Settings_model->save_setting($this->type . "_authorized", 1);

            if ($this->type == "imap") {
                $google_oauth = new \Google_Service_Oauth2($client);
                $google_account_info = $google_oauth->userinfo->get();
                $this->Settings_model->save_setting("gmail_" . $this->type . "_email", $google_account_info->email);
            } else {
                //send test email if any
                $test_mail_to = get_setting("send_test_mail_to");
                if ($test_mail_to) {
                    $this->send_app_mail($test_mail_to, "Test message", "This is a test message to check mail configuration.");

                    //delete temporary data
                    $this->Settings_model->save_setting("send_test_mail_to", "");
                }
            }
        }
    }

    //get client credentials
    private function _get_client_credentials() {
        $scopes = array(
            "email",
            Google_Service_Gmail::MAIL_GOOGLE_COM, // Full access to Gmail
        );
        if ($this->type == "smtp") {
            $scopes = array(
                Google_Service_Gmail::GMAIL_SEND,  // Allow sending emails only
                Google_Service_Gmail::GMAIL_COMPOSE, // Allow composing emails
                'https://www.googleapis.com/auth/gmail.settings.basic', // Allow accessing basic settings
                'email',
                'profile'
            );
        }

        $url = get_uri("google_api/save_gmail_" . $this->type . "_access_token");

        $client = new \Google_Client();
        $client->setApplicationName(get_setting('app_title'));
        $client->setRedirectUri($url);
        $client->setClientId(get_setting("gmail_" . $this->type . "_client_id"));
        $client->setClientSecret(decode_id(get_setting("gmail_" . $this->type . "_client_secret"), "gmail_" . $this->type . "_client_secret"));
        $client->setScopes($scopes);
        $client->setAccessType("offline");
        $client->setPrompt('select_account consent');

        return $client;
    }
}
