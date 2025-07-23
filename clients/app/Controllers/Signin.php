<?php

namespace App\Controllers;

use App\Libraries\ReCAPTCHA;

class Signin extends App_Controller {

    private $signin_validation_errors;

    function __construct() {
        parent::__construct();
        $this->signin_validation_errors = array();
        helper('email');
    }

    function index() {
        if ($this->Users_model->login_user_id()) {
            app_redirect('dashboard/view');
        } else {

            $view_data["redirect"] = "";
            if (isset($_REQUEST["redirect"])) {
                $view_data["redirect"] = $_REQUEST["redirect"];
            }

            $this->validate_submitted_data(array(
                "redirect" => "valid_url_strict"
            ), false, false);

            return $this->template->view('signin/index', $view_data);
        }
    }

    private function has_recaptcha_error() {

        $ReCAPTCHA = new ReCAPTCHA();
        $response = $ReCAPTCHA->validate_recaptcha(false);

        if ($response === true) {
            return true;
        } else {
            array_push($this->signin_validation_errors, $response);
            return false;
        }
    }

    // check authentication
    function authenticate() {
        $validation = $this->validate_submitted_data(array(
            "email" => "required|valid_email",
            "password" => "required"
        ), true);

        $email = $this->request->getPost("email");
        $password = $this->request->getPost("password");
        if (!$email) {
            //loaded the page directly
            app_redirect('signin');
        }

        if (is_array($validation)) {
            //has validation errors
            $this->signin_validation_errors = $validation;
        }

        //check if there reCaptcha is enabled
        //if reCaptcha is enabled, check the validation
        if (get_setting("re_captcha_secret_key")) {
            //in this function, if any error found in recaptcha, that will be added
            $this->has_recaptcha_error();
        }

        //don't check password if there is any error
        if ($this->signin_validation_errors) {
            $this->session->setFlashdata("signin_validation_errors", $this->signin_validation_errors);
            app_redirect('signin');
        }

        if (!$this->Users_model->authenticate($email, $password)) {
            //authentication failed
            array_push($this->signin_validation_errors, app_lang("authentication_failed"));
            $this->session->setFlashdata("signin_validation_errors", $this->signin_validation_errors);
            app_redirect('signin');
        }

        //authentication success
        $redirect = $this->request->getPost("redirect");
        if ($redirect) {
            $allowed_host = $_SERVER['HTTP_HOST'];

            $parsed_redirect = parse_url($redirect);
            $redirect_host = get_array_value($parsed_redirect, "host");
            if ($allowed_host === $redirect_host) {
                return redirect()->to($redirect);
            } else {
                app_redirect('dashboard/view');
            }
        } else {
            app_redirect('dashboard/view');
        }
    }

    function sign_out() {
        $this->Users_model->sign_out();
    }

    //send an email to users mail with reset password link
    function send_reset_password_mail() {
        $this->validate_submitted_data(array(
            "email" => "required|valid_email|max_length[100]"
        ));

        //if reCaptcha is enabled, check the validation
        $ReCAPTCHA = new ReCAPTCHA();
        $ReCAPTCHA->validate_recaptcha();

        $email = $this->request->getPost("email");
        $email_exists = $this->Users_model->is_email_exists($email);

        //send reset password email if found account with this email
        if ($email_exists) {
            $user = $this->Users_model->get_one_where(array("email" => $email, "deleted" => 0, "status" => "active"));

            if ($user && (($user->user_type == "staff") || ($user->user_type == "client" && get_setting("disable_client_login") != "1"))) {
                $email_template = $this->Email_templates_model->get_final_template("reset_password", true);

                $user_language = $user->language;
                $parser_data["ACCOUNT_HOLDER_NAME"] =  clean_data($user->first_name . " " . $user->last_name);

                $parser_data["SIGNATURE"] = get_array_value($email_template, "signature_$user_language") ? get_array_value($email_template, "signature_$user_language") : get_array_value($email_template, "signature_default");
                $parser_data["LOGO_URL"] = get_logo_url();
                $parser_data["SITE_URL"] = get_uri();
                $parser_data["RECIPIENTS_EMAIL_ADDRESS"] = $user->email;
                $code = make_random_string();

                $verification_data = array(
                    "type" => "reset_password",
                    "code" => $code,
                    "params" => serialize(array(
                        "email" => $user->email,
                        "expire_time" => time() + (24 * 60 * 60) //Expire after 24 hours
                    ))
                );

                $this->Verification_model->ci_save($verification_data);
                $parser_data['RESET_PASSWORD_URL'] = get_uri("signin/new_password/" . $code);

                $message = get_array_value($email_template, "message_$user_language") ? get_array_value($email_template, "message_$user_language") : get_array_value($email_template, "message_default");
                $subject = get_array_value($email_template, "subject_$user_language") ? get_array_value($email_template, "subject_$user_language") : get_array_value($email_template, "subject_default");

                $message = $this->parser->setData($parser_data)->renderString($message);
                $subject = $this->parser->setData($parser_data)->renderString($subject);

                if (send_app_mail($email, $subject, $message)) {
                    echo json_encode(array('success' => true, 'message' => app_lang("reset_info_send")));
                } else {
                    echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
                }
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang("reset_info_send")));
            }
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang("reset_info_send")));
            return false;
        }
    }

    //show forgot password recovery form
    function request_reset_password() {
        $view_data["form_type"] = "request_reset_password";
        return $this->template->view('signin/index', $view_data);
    }

    //when user clicks to reset password link from his/her email, redirect to this url
    function new_password($key) {
        
        if (strlen($key) !== 10) {
            show_404();
        }

        $valid_key = $this->is_valid_reset_password_key($key);

        if ($valid_key) {
            $email = get_array_value($valid_key, "email");

            if ($this->Users_model->is_email_exists($email)) {
                $view_data["key"] = clean_data($key);
                $view_data["form_type"] = "new_password";
                return $this->template->view('signin/index', $view_data);
            }
        }

        //else show error
        $view_data["heading"] = "Invalid Request";
        $view_data["message"] = "The key has expaired or something went wrong!";
        return $this->template->view("errors/html/error_general", $view_data);
    }

    //finally reset the old password and save the new password
    function do_reset_password() {
        $this->validate_submitted_data(array(
            "key" => "required",
            "password" => "required"
        ));

        $key = $this->request->getPost("key");
        if (strlen($key) !== 10) {
            show_404();
        }

        $password = $this->request->getPost("password");
        $valid_key = $this->is_valid_reset_password_key($key);

        if ($valid_key) {
            $email = get_array_value($valid_key, "email");
            $this->Users_model->update_password($email, password_hash($password, PASSWORD_DEFAULT));

            //user can't reset password two times with the same code
            $verification_id = get_array_value($valid_key, "verification_id");
            $this->Verification_model->delete_permanently($verification_id);

            echo json_encode(array("success" => true, 'message' => app_lang("password_reset_successfully") . " " . anchor("signin", app_lang("signin"))));
            return true;
        }

        echo json_encode(array("success" => false, 'message' => app_lang("error_occurred")));
    }

    //check valid key
    private function is_valid_reset_password_key($verification_code = "") {

        if ($verification_code) {
            $options = array("code" => $verification_code, "type" => "reset_password");
            $verification_info = $this->Verification_model->get_details($options)->getRow();

            if ($verification_info && $verification_info->id) {
                $reset_password_info = unserialize($verification_info->params);

                $email = get_array_value($reset_password_info, "email");
                $expire_time = get_array_value($reset_password_info, "expire_time");

                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) && $expire_time && $expire_time > time()) {
                    return array("email" => $email, "verification_id" => $verification_info->id);
                }
            }
        }
    }
}
