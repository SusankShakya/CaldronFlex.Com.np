<?php

namespace App\Controllers;

use App\Libraries\ReCAPTCHA;

class Signup extends App_Controller {

    public $Verification_model;

    function __construct() {
        parent::__construct();
        helper('email');
        $this->Verification_model = model('App\Models\Verification_model');
    }

    function index() {
        //by default only client can signup directly
        //if client login/signup is disabled then show 404 page
        if (get_setting("disable_client_signup")) {
            show_404();
        }

        $view_data["type"] = "client";
        $view_data["signup_type"] = "new_client";
        $view_data["signup_message"] = app_lang("create_an_account_as_a_new_client");

        //check if the email verification before signup is active
        if (get_setting("verify_email_before_client_signup")) {
            $view_data["signup_type"] = "send_verify_email";
        }

        return $this->template->view("signup/index", $view_data);
    }

    //redirected from email
    function accept_invitation($signup_key = "") {
        $valid_key = $this->is_valid_invitation_key($signup_key);
        if ($valid_key) {
            $email = get_array_value($valid_key, "email");
            $type = get_array_value($valid_key, "type");
            $role_id = get_array_value($valid_key, "role_id");
            $client_id = get_array_value($valid_key, "client_id");

            if ($this->Users_model->is_email_exists($email, $client_id)) {
                $view_data["heading"] = "Account exists!";
                $view_data["message"] = app_lang("account_already_exists_for_your_mail") . " " . anchor("signin", app_lang("signin"));
                return $this->template->view("errors/html/error_general", $view_data);
            }

            if ($type === "staff") {
                $view_data["signup_message"] = app_lang("create_an_account_as_a_team_member");
            } else if ($type === "client") {
                $view_data["signup_message"] = app_lang("create_an_account_as_a_client_contact");
            }

            $view_data["signup_type"] = "invitation";
            $view_data["type"] = $type;
            $view_data["signup_key"] = $signup_key;
            $view_data["role_id"] = $role_id;
            return $this->template->view("signup/index", $view_data);
        } else {
            $view_data["heading"] = "406 Not Acceptable";
            $view_data["message"] = app_lang("invitation_expaired_message");
            return $this->template->view("errors/html/error_general", $view_data);
        }
    }

    function create_account() {

        $signup_key = $this->request->getPost("signup_key");
        $verify_email_key = $this->request->getPost("verify_email_key");

        $this->validate_submitted_data(array(
            "first_name" => "required",
            "last_name" => "required",
            "password" => "required"
        ));

        //check if there reCaptcha is enabled
        //if reCaptcha is enabled, check the validation
        $ReCAPTCHA = new ReCAPTCHA();
        $ReCAPTCHA->validate_recaptcha();

        $first_name = $this->request->getPost("first_name");
        $last_name = $this->request->getPost("last_name");
        $password = $this->request->getPost("password");
        $password = clean_data($password);

        $user_data = array(
            "first_name" => $first_name,
            "last_name" => $last_name,
            "job_title" => $this->request->getPost("job_title") ? $this->request->getPost("job_title") : "Untitled",
            "created_at" => get_current_utc_time()
        );

        $user_data = clean_data($user_data);

        // don't clean password since there might be special characters 
        $user_data["password"] = password_hash($password, PASSWORD_DEFAULT);

        if ($signup_key) {
            //it is an invitation, validate the invitation key
            $valid_key = $this->is_valid_invitation_key($signup_key);

            if ($valid_key) {

                $email = get_array_value($valid_key, "email");
                $type = get_array_value($valid_key, "type");
                $clent_id = get_array_value($valid_key, "client_id");

                $role_id = get_array_value($valid_key, "role_id");
                $client_permissions = get_array_value($valid_key, "client_permissions");

                //show error message if email already exists
                if ($this->Users_model->is_email_exists($email, $clent_id)) {
                    echo json_encode(array("success" => false, 'message' => app_lang("account_already_exists_for_your_mail") . " " . anchor(get_uri("signin"), app_lang('signin'), array("class" => "text-white text-off"))));
                    return false;
                }

                $user_data["email"] = $email;
                $user_data["user_type"] = $type;
                $user_data["role_id"] = $role_id ? $role_id : "";

                $delete_verification_code = true;

                if ($type === "staff") {
                    //create a team member account
                    $user_id = $this->Users_model->ci_save($user_data);
                    if ($user_id) {
                        //save team members job info
                        $job_data = array(
                            "user_id" => $user_id,
                            "salary" => 0,
                            "salary_term" => 0,
                            "date_of_hire" => ""
                        );
                        $this->Users_model->save_job_info($job_data);
                    }
                } else {
                    //check client id and create client contact account
                    $client = $this->Clients_model->get_one($clent_id);
                    if (isset($client->id) && $client->deleted == 0) {
                        $user_data["client_id"] = $clent_id;

                        //has any primary contact for this clinet? if not, make this contact as a primary contact
                        $primary_contact = $this->Clients_model->get_primary_contact($clent_id);
                        if (!$primary_contact) {
                            $user_data['is_primary_contact'] = 1;
                        }

                        if (isset($user_data['is_primary_contact']) && $user_data['is_primary_contact'] == 1) {
                            $user_data['client_permissions'] = "all";
                        } else {
                            $user_data["client_permissions"] = $client_permissions;
                        }

                        //create a client contact account
                        $user_id = $this->Users_model->ci_save($user_data);
                        if ($user_id) {
                            log_notification("invited_client_contact_signed_up", array("client_id" => $clent_id), $user_id);
                        }
                    } else {
                        //invalid client
                        $delete_verification_code = false;
                        echo json_encode(array("success" => false, 'message' => app_lang("something_went_wrong")));
                        return false;
                    }
                }

                //user can't create account two times with the same code
                if ($delete_verification_code) {
                    $options = array("code" => $signup_key, "type" => "invitation");
                    $verification_info = $this->Verification_model->get_details($options)->getRow();
                    if ($verification_info->id) {
                        $this->Verification_model->delete_permanently($verification_info->id);
                    }
                }
            } else {
                //invalid key. show an error message
                echo json_encode(array("success" => false, 'message' => app_lang("invitation_expaired_message")));
                return false;
            }
        } else {
            //create a client directly
            if (get_setting("disable_client_signup")) {
                show_404();
            }

            if (get_setting("verify_email_before_client_signup") && !$verify_email_key) {
                show_404();
            }

            //for a email verified user, get the email using the key
            if ($verify_email_key) {
                $valid_key = $this->is_valid_email_verification_key($verify_email_key);
                if ($valid_key) {
                    $email = get_array_value($valid_key, "email");
                } else {
                    show_404();
                }
            } else {
                $this->validate_submitted_data(array(
                    "email" => "required|valid_email"
                ));

                $email = $this->request->getPost("email");

                if ($this->Users_model->is_email_exists($email)) {
                    echo json_encode(array("success" => false, 'message' => app_lang("account_already_exists_for_your_mail") . " " . anchor(get_uri("signin"), app_lang('signin'), array("class" => "text-white"))));
                    return false;
                }
            }

            $company_name = $this->request->getPost("company_name") ? $this->request->getPost("company_name") : $first_name . " " . $last_name; //save user name as company name if there is no company name entered

            $client_data = array(
                "company_name" => $company_name,
                "type" => $this->request->getPost("account_type"),
                "created_by" => 1 //add default admin
            );

            $client_data = clean_data($client_data);

            //check duplicate company name, if found then show an error message
            if (get_setting("disallow_duplicate_client_company_name") == "1" && $this->Clients_model->is_duplicate_company_name($company_name)) {
                echo json_encode(array("success" => false, 'message' => app_lang("account_already_exists_for_your_company_name") . " " . anchor(get_uri("signin"), app_lang('signin'), array("class" => "text-white text-off"))));
                return false;
            }


            //create a client
            $client_id = $this->Clients_model->ci_save($client_data);
            if ($client_id) {
                //client created, now create the client contact
                $user_data["user_type"] = "client";
                $user_data["email"] = $email;
                $user_data["client_id"] = $client_id;
                $user_data["is_primary_contact"] = 1;
                $user_data['client_permissions'] = "all";
                $user_id = $this->Users_model->ci_save($user_data);

                //user can't create account two times with the same code
                if ($verify_email_key) {
                    $options = array("code" => $verify_email_key, "type" => "verify_email");
                    $verification_info = $this->Verification_model->get_details($options)->getRow();
                    if ($verification_info->id) {
                        $this->Verification_model->delete_permanently($verification_info->id);
                    }
                }

                log_notification("client_signup", array("client_id" => $client_id), $user_id);

                //send welcome email
                $email_template = $this->Email_templates_model->get_final_template("new_client_greetings"); //use default template since creating new client

                $parser_data["SIGNATURE"] = $email_template->signature;
                $parser_data["CONTACT_FIRST_NAME"] = get_array_value($user_data, "first_name");
                $parser_data["CONTACT_LAST_NAME"] = get_array_value($user_data, "last_name");

                $Company_model = model('App\Models\Company_model');
                $company_info = $Company_model->get_one_where(array("is_default" => true));
                $parser_data["COMPANY_NAME"] = $company_info->name;

                $parser_data["DASHBOARD_URL"] = base_url();
                $parser_data["CONTACT_LOGIN_EMAIL"] = get_array_value($user_data, "email");
                $parser_data["CONTACT_LOGIN_PASSWORD"] = $password;
                $parser_data["LOGO_URL"] = get_logo_url();

                $message = $this->parser->setData($parser_data)->renderString($email_template->message);
                $subject = $this->parser->setData($parser_data)->renderString($email_template->subject);

                send_app_mail($email, $subject, $message);
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
                return false;
            }
        }


        if ($user_id) {
            echo json_encode(array("success" => true, 'message' => app_lang('account_created') . " " . anchor(get_uri("signin"), app_lang('signin'), array("class" => "text-white text-off"))));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //send an email to verify the identity
    function send_verification_mail() {
        $this->validate_submitted_data(array(
            "email" => "required|valid_email"
        ));

        //check if there reCaptcha is enabled
        //if reCaptcha is enabled, check the validation
        $ReCAPTCHA = new ReCAPTCHA();
        $ReCAPTCHA->validate_recaptcha();

        $email = $this->request->getPost("email");

        if ($this->Users_model->is_email_exists($email)) {
            echo json_encode(array("success" => false, 'message' => app_lang("account_already_exists_for_your_mail") . " " . anchor(get_uri("signin"), app_lang('signin'), array("class" => "text-white text-off"))));
            return false;
        }

        $email_template = $this->Email_templates_model->get_final_template("verify_email"); //use default template

        $parser_data["SIGNATURE"] = $email_template->signature;
        $parser_data["LOGO_URL"] = get_logo_url();
        $parser_data["SITE_URL"] = get_uri();
        $code = make_random_string();

        $verification_data = array(
            "type" => "verify_email",
            "code" => $code,
            "params" => serialize(array(
                "email" => $email,
                "expire_time" => time() + (24 * 60 * 60)
            ))
        );

        $this->Verification_model->ci_save($verification_data);

        $parser_data['VERIFY_EMAIL_URL'] = get_uri("signup/continue_signup/" . $code);

        $message = $this->parser->setData($parser_data)->renderString($email_template->message);
        $subject = $this->parser->setData($parser_data)->renderString($email_template->subject);

        if (send_app_mail($email, $subject, $message)) {
            echo json_encode(array('success' => true, 'message' => app_lang("reset_info_send")));
        } else {
            echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
        }
    }

    //continue sign up process
    function continue_signup($key = "") {
        if ($key && !get_setting("disable_client_signup")) {
            $valid_key = $this->is_valid_email_verification_key($key);

            if ($valid_key) {
                $view_data["type"] = "client";
                $view_data["signup_type"] = "verify_email";
                $view_data["signup_message"] = app_lang("please_continue_your_signup_process");
                $view_data["key"] = clean_data($key);

                return $this->template->view("signup/index", $view_data);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
    }

    //check valid key
    private function is_valid_email_verification_key($verification_code = "") {

        if ($verification_code) {
            if (strlen($verification_code) !== 10) {
                return false;
            }

            $options = array("code" => $verification_code, "type" => "verify_email");
            $verification_info = $this->Verification_model->get_details($options)->getRow();

            if ($verification_info && $verification_info->id) {
                $email_verification_info = unserialize($verification_info->params);

                $email = get_array_value($email_verification_info, "email");
                $expire_time = get_array_value($email_verification_info, "expire_time");

                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) && $expire_time && $expire_time > time()) {
                    return array("email" => $email);
                }
            }
        }
    }

    //check valid key
    private function is_valid_invitation_key($verification_code = "") {
        if ($verification_code) {
            if (strlen($verification_code) !== 10) {
                return false;
            }

            $options = array("code" => $verification_code, "type" => "invitation");
            $verification_info = $this->Verification_model->get_details($options)->getRow();

            if ($verification_info && $verification_info->id) {
                $invitation_info = unserialize($verification_info->params);

                $email = get_array_value($invitation_info, "email");
                $expire_time = get_array_value($invitation_info, "expire_time");
                $type = get_array_value($invitation_info, "type");
                $client_id = get_array_value($invitation_info, "client_id");

                $role_id = get_array_value($invitation_info, "role_id");
                $client_permissions = get_array_value($invitation_info, "client_permissions");

                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) && $expire_time && $expire_time > time()) {
                    return array("email" => $email, "type" => $type, "client_id" => $client_id, "role_id" => $role_id, "client_permissions" => $client_permissions);
                }
            }
        }
    }
}
