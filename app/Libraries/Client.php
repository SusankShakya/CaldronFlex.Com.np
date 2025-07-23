<?php

namespace App\Libraries;

use App\Libraries\Permission_manager;

class Client {
    private $ci;
    private $permission_manager;

    public function __construct($security_controller_instance) {
        $this->ci = $security_controller_instance;
        $this->permission_manager = new Permission_manager($security_controller_instance);
    }

    function save_client($data) {
        if (!$data) {
            return false;
        }

        $this->ci->validate_data($data, array(
            "company_name" => "required",
            "client_id" => "numeric",
            "contact_id" => "numeric",
            "owner_id" => "numeric",
            "type" => "in_list[person,organization]"
        ));

        $client_id = get_array_value($data, "client_id");
        $company_name = get_array_value($data, "company_name");
        $owner_id = get_array_value($data, "owner_id");
        $currency_symbol = get_array_value($data, "currency_symbol");
        $currency = get_array_value($data, "currency");

        if (!$this->permission_manager->can_manage_clients($client_id)) {
            echo json_encode(array("success" => false, 'message' => app_lang('access_denied')));
            exit();
        }

        $fields = [
            "company_name",
            "type",
            "address",
            "city",
            "state",
            "zip",
            "country",
            "phone",
            "website",
            "vat_number",
            "gst_number"
        ];

        $client_data = [];
        foreach ($fields as $field) {
            $client_data[$field] = get_array_value($data, $field) ?? "";
        }

        $client_data["is_lead"] = 0;
        $client_data["type"] = $client_data["type"] ?? "organization";

        //set permission specific fields
        if ($this->permission_manager->can_manage_invoices()) {
            $client_data["currency_symbol"] = $currency_symbol ?? "";
            $client_data["currency"] = $currency_symbol ?? "";
            $client_data["disable_online_payment"] = get_array_value($data, "disable_online_payment") ?? 0;
        }

        if ($this->permission_manager->is_team_member()) {
            $group_ids = get_array_value($data, "group_ids");
            $labels = get_array_value($data, "labels");
            $managers = get_array_value($data, "managers");

            validate_list_of_numbers($group_ids);
            validate_list_of_numbers($labels);
            validate_list_of_numbers($managers);

            $client_data["group_ids"] = $group_ids ?? "";
            $client_data["labels"] = $labels ?? "";
            $client_data["managers"] = $managers ?? "";

            $client_data["owner_id"] = $owner_id ?? $this->ci->login_user->id;
        }


        //set values during insert/update only
        if (!$client_id) {
            //insert
            $client_data["created_date"] = get_current_utc_time();
            $client_data["created_by"] = $this->ci->login_user->id;

            //to create a client, email is not required but you can pre validate the contact email before creating the client 

            $contact_email = get_array_value($data, "contact_email");
            if ($contact_email) {
                $this->ci->validate_data($data, array(
                    "contact_email" => "valid_email|max_length[100]",
                ));

                $contact_email = trim($contact_email);
                if ($this->ci->Users_model->is_email_exists($contact_email)) {
                    echo json_encode(array("success" => false, 'message' => app_lang('duplicate_email'), "error_type" => "email_exists"));
                    exit();
                }
            }
        } else {
            //update
            if ($this->permission_manager->can_manage_invoices() && $currency) {
                //currency can't be updated if there are existing invoices, estimates etc.
                $client_info = $this->ci->Clients_model->get_one($client_id);
                if ($client_info->currency !== $currency && !$this->ci->Clients_model->is_currency_editable($client_id)) {
                    echo json_encode(array("success" => false, 'message' => app_lang('client_currency_not_editable_message')));
                    exit();
                }
            }
        }

        $client_data = clean_data($client_data);

        //check duplicate company name, if found then show an error message
        if (get_setting("disallow_duplicate_client_company_name") == "1" && $this->ci->Clients_model->is_duplicate_company_name($client_data["company_name"], $client_id)) {
            echo json_encode(array("success" => false, 'message' => app_lang("account_already_exists_for_your_company_name")));
            exit();
        }

        $save_id = $this->ci->Clients_model->ci_save($client_data, $client_id);

        if ($save_id) {
            save_custom_fields("clients", $save_id, $this->permission_manager->is_admin(), $this->ci->login_user->user_type);
            echo json_encode(array("success" => true, "message" => app_lang('client_created_successfully'), "id" => $save_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function save_client_contact($data) {
        if (!is_array($data)) {
            return false;
        }

        $this->ci->validate_data($data, array(
            "first_name" => "required",
            "client_id" => "required|numeric",
            "contact_id" => "numeric"
        ));

        $contact_id = get_array_value($data, "contact_id");
        $client_id = get_array_value($data, "client_id");

        $password = get_array_value($data, "login_password");
        $password = clean_data($password);

        if (!$this->permission_manager->can_manage_clients($client_id)) {
            echo json_encode(array("success" => false, 'message' => app_lang('access_denied')));
            exit();
        }

        $fields = [
            "first_name",
            "last_name",
            "phone",
            "skype",
            "job_title",
            "gender",
            "note"
        ];

        $user_data = [];

        foreach ($fields as $field) {
            $user_data[$field] = get_array_value($data, $field) ?? "";
        }

        if (!$contact_id) {

            $this->ci->validate_data($data, array(
                "email" => "required|valid_email|max_length[100]",
            ));


            //we'll save following fields only when creating a new contact
            $user_data["client_id"] = $client_id;
            $user_data["email"] = trim(get_array_value($data, "email"));
            $user_data["password"] = $password ? password_hash($password, PASSWORD_DEFAULT) : "";
            $user_data["created_at"] = get_current_utc_time();

            //validate duplicate email address
            if ($this->ci->Users_model->is_email_exists($user_data["email"], $client_id)) {
                echo json_encode(array("success" => false, 'message' => app_lang('duplicate_email')));
                exit();
            }

            //by default, the first contact of a client is the primary contact
            //check existing primary contact. if not found then set the first contact = primary contact
            $has_primary_contact = $this->ci->Clients_model->get_primary_contact($client_id);
            if (!$has_primary_contact) {
                $user_data['is_primary_contact'] = 1;
            }

            if ((isset($user_data['is_primary_contact']) && $user_data['is_primary_contact'] == 1) || get_array_value($data, "can_access_everything") == 1) {
                $user_data['client_permissions'] = "all";
            } else {
                $user_data['client_permissions'] = get_array_value($data, "specific_permissions");

                if (get_setting("disable_client_login")) {
                    $user_data['client_permissions'] = get_setting("default_permissions_for_non_primary_contact") ?? "projects";
                }
            }

            if (!$user_data['client_permissions']) {
                echo json_encode(array("success" => false, 'message' => app_lang('permission_is_required')));
                exit();
            }
        }

        $user_data = clean_data($user_data);

        $save_id = $this->ci->Users_model->ci_save($user_data, $contact_id);
        if ($save_id) {

            save_custom_fields("client_contacts", $save_id, $this->ci->login_user->is_admin, $this->ci->login_user->user_type);

            //send login details to user only for first time. when creating a new contact
            if (!$contact_id && get_array_value($data, "email_login_details")) {
                $this->email_login_details($save_id, $user_data["email"], $password);
            }

            echo json_encode(array("success" => true, 'id' => $save_id, "client_id" => $client_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function email_login_details($user_id, $email, $password) {

        if (get_setting("disable_client_login") == "1") {
            return false;
        }

        $contact_info = $this->ci->Users_model->get_one($user_id);
        if (!$contact_info || $contact_info->user_type != "client" || !$email || !$password) {
            return false;
        }

        $email_template = $this->ci->Email_templates_model->get_final_template("login_info", true);

        $user_language = $contact_info->language;
        $parser_data["SIGNATURE"] = get_array_value($email_template, "signature_$user_language") ? get_array_value($email_template, "signature_$user_language") : get_array_value($email_template, "signature_default");
        $parser_data["USER_FIRST_NAME"] = clean_data($contact_info->first_name);
        $parser_data["USER_LAST_NAME"] = clean_data($contact_info->last_name);
        $parser_data["USER_LOGIN_EMAIL"] = $email;
        $parser_data["USER_LOGIN_PASSWORD"] = $password;
        $parser_data["DASHBOARD_URL"] = base_url();
        $parser_data["LOGO_URL"] = get_logo_url();

        $message = get_array_value($email_template, "message_$user_language") ? get_array_value($email_template, "message_$user_language") : get_array_value($email_template, "message_default");
        $subject = get_array_value($email_template, "subject_$user_language") ? get_array_value($email_template, "subject_$user_language") : get_array_value($email_template, "subject_default");

        $message = $this->ci->parser->setData($parser_data)->renderString($message);
        $subject = $this->ci->parser->setData($parser_data)->renderString($subject);
        send_app_mail($email, $subject, $message);
    }
}
