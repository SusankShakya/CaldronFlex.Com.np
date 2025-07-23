<?php

namespace App\Libraries;


class ReCAPTCHA {

    private $re_captcha_secret_key;
    private $re_captcha_protocol;

    public function __construct() {
        $this->re_captcha_secret_key = get_setting("re_captcha_secret_key");
        $this->re_captcha_protocol = get_setting("re_captcha_protocol");
    }

    public function validate_recaptcha($show_error = true) {
        if (!$this->re_captcha_secret_key) {
            return true; // recaptcha is not enabled
        }

        $request = \Config\Services::request();

        if ($this->re_captcha_protocol === "v3") {
            $re_captcha_token = $request->getPost("re_captcha_token");
            $response = $this->_is_valid_recaptcha_v3($re_captcha_token);
            return $this->_process_response($response, $show_error);
        } else {
            $response = $this->_is_valid_recaptcha_v2($request->getPost("g-recaptcha-response"));
            return $this->_process_response($response, $show_error);
        }
    }

    private function _process_response($response, $show_error) {
        if ($response !== true) {

            if ($this->re_captcha_protocol !== "v3") {
                $response = $response ? app_lang("re_captcha_error-" . $response) : app_lang("re_captcha_expired");
            }

            if ($show_error) {
                echo json_encode(array('success' => false, 'message' => $response));
                exit();
            } else {
                return $response;
            }
        } else {
            return true;
        }
    }

    private function _is_valid_recaptcha_v3($recaptcha_post_data) {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';

        // Make and decode POST request:
        $response = file_get_contents($recaptcha_url . '?secret=' . $this->re_captcha_secret_key . '&response=' . $recaptcha_post_data);
        $responseKeys = json_decode($response, true);

        // Check the response
        if ($responseKeys["success"]) {
            // Verified - proceed with form processing
            if ($responseKeys["success"] && $responseKeys["score"] >= 0.5) {
                // Likely a human, proceed with form processing
                return true;
            } else {
                return app_lang("re_captcha_suspicious_activity");
            }
        } else {
            return app_lang("re_captcha_error-bad-request");
        }
    }

    private function _is_valid_recaptcha_v2($recaptcha_post_data) {
        //load recaptcha lib
        require_once(APPPATH . "ThirdParty/recaptcha/autoload.php");
        $recaptcha = new \ReCaptcha\ReCaptcha($this->re_captcha_secret_key);
        $resp = $recaptcha->verify($recaptcha_post_data, $_SERVER['REMOTE_ADDR']);

        if ($resp->isSuccess()) {
            return true;
        } else {

            $error = "";
            foreach ($resp->getErrorCodes() as $code) {
                $error = $code;
            }

            return $error;
        }
    }
}
