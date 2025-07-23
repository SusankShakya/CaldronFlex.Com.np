<?php

namespace App\Controllers;

use App\Libraries\ReCAPTCHA;

class Request_estimate extends App_Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        if (!get_setting("module_estimate_request")) {
            show_404();
        }

        $view_data['topbar'] = "includes/public/topbar";
        $view_data['left_menu'] = false;

        $view_data["estimate_forms"] = $this->Estimate_forms_model->get_all_where(array("status" => "active", "public" => "1", "deleted" => 0))->getResult();
        return $this->template->rander("request_estimate/index", $view_data);
    }

    function form($id = 0, $embedded = 0) {
        if (!get_setting("module_estimate_request")) {
            show_404();
        }

        if (!$id) {
            app_redirect("request_estimate");
        }

        validate_numeric_value($id);

        if ($embedded) {
            $view_data['topbar'] = false;
        } else {
            $view_data['topbar'] = "includes/public/topbar";
        }

        $view_data['left_menu'] = false;

        $view_data['embedded'] = clean_data($embedded);

        $model_info = $this->Estimate_forms_model->get_one_where(array("id" => $id, "public" => "1", "status" => "active", "deleted" => 0));

        if (get_setting("module_estimate_request") && $model_info->id) {
            $view_data['model_info'] = $model_info;
            return $this->template->rander('request_estimate/estimate_request_form', $view_data);
        } else {
            show_404();
        }
    }

    //save estimate request from client
    function save_estimate_request() {
        $form_id = $this->request->getPost('form_id');
        $assigned_to = $this->request->getPost('assigned_to');

        $this->validate_submitted_data(array(
            "form_id" => "required|numeric",
            "email" => "valid_email"
        ));

        //check if there reCaptcha is enabled
        //if reCaptcha is enabled, check the validation
        $ReCAPTCHA = new ReCAPTCHA();
        $ReCAPTCHA->validate_recaptcha();

        $options = array("related_to" => "estimate_form-" . $form_id);
        $form_fields = $this->Custom_fields_model->get_details($options)->getResult();

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "estimate");

        //match with the existing email
        $email = $this->request->getPost('email');
        $user_info = $this->Users_model->get_one_where(array("email" => $email, "deleted" => 0));

        if ($user_info->client_id) {
            //created by existing client/lead
            $request_data = array(
                "estimate_form_id" => $form_id,
                "created_by" => $user_info->id,
                "created_at" => get_current_utc_time(),
                "client_id" => $user_info->client_id ? $user_info->client_id : 0,
                "lead_id" => 0,
                "assigned_to" => $assigned_to ? $assigned_to : 0,
                "status" => "new"
            );
        } else {
            $company_name = $this->request->getPost('company_name');
            $first_name = $this->request->getPost('first_name');
            $last_name = $this->request->getPost('last_name');

            //unknown client
            $leads_data = array(
                "company_name" => $company_name,
                "address" => $this->request->getPost('address'),
                "city" => $this->request->getPost('city'),
                "state" => $this->request->getPost('state'),
                "zip" => $this->request->getPost('zip'),
                "country" => $this->request->getPost('country'),
                "phone" => $this->request->getPost('phone'),
                "is_lead" => 1,
                "lead_status_id" => $this->Lead_status_model->get_first_status(),
                "created_date" => get_current_utc_time(),
                "owner_id" => $assigned_to ? $assigned_to : 0
            );

            if ($company_name) {
                $leads_data["type"] = "organization";
            } else {
                $leads_data["type"] = "person";
                $leads_data["company_name"] = $first_name . " " . $last_name;
            }

            $leads_data = clean_data($leads_data);

            $lead_id = $this->Clients_model->ci_save($leads_data);

            $lead_contact_id = 999999995; //999999995 is the id for unknown client

            if ($lead_id && ($first_name || $last_name || $email)) {
                //lead created, create a contact on that lead
                $lead_contact_data = array(
                    "first_name" => $first_name,
                    "last_name" => $last_name,
                    "client_id" => $lead_id,
                    "user_type" => "lead",
                    "email" => trim($email),
                    "created_at" => get_current_utc_time(),
                    "is_primary_contact" => 1
                );

                $lead_contact_data = clean_data($lead_contact_data);
                $lead_contact_id = $this->Users_model->ci_save($lead_contact_data);
            }

            $request_data = array(
                "estimate_form_id" => $form_id,
                "created_by" => $lead_contact_id,
                "created_at" => get_current_utc_time(),
                "client_id" => $lead_id,
                "lead_id" => 0,
                "assigned_to" => $assigned_to ? $assigned_to : 0,
                "status" => "new"
            );
        }

        $request_data = clean_data($request_data);

        $request_data["files"] = $files_data; //don't clean serilized data

        $save_id = $this->Estimate_requests_model->ci_save($request_data);
        if ($save_id) {

            //estimate request has been saved, now save the field values
            foreach ($form_fields as $field) {
                $value = $this->request->getPost("custom_field_" . $field->id);
                if ($value) {
                    $field_value_data = array(
                        "related_to_type" => "estimate_request",
                        "related_to_id" => $save_id,
                        "custom_field_id" => $field->id,
                        "value" => $value
                    );

                    $field_value_data = clean_data($field_value_data);

                    $this->Custom_field_values_model->ci_save($field_value_data);
                }
            }

            $user_id = $user_info->id ? $user_info->id : $lead_contact_id;

            //create notification
            log_notification("estimate_request_received", array("estimate_request_id" => $save_id, "user_id" => $user_id));

            echo json_encode(array("success" => true, 'message' => app_lang('estimate_submission_message')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //prepare data for datatable for estimate form's field list
    function estimate_form_filed_list_data($id = 0) {
        validate_numeric_value($id);

        $options = array("related_to" => "estimate_form-" . $id);
        $list_data = $this->Custom_fields_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_form_field_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //prepare a row of estimates form's field list
    private function _make_form_field_row($data) {

        $required = "";
        if ($data->required) {
            $required = "*";
        }

        $title = "";
        if ($data->title_language_key) {
            $title = app_lang($data->title_language_key);
        } else {
            $title = $data->title;
        }

        $placeholder = "";
        if ($data->placeholder_language_key) {
            $placeholder = app_lang($data->placeholder_language_key);
        } else {
            $placeholder = $data->placeholder;
        }

        $field = "<label for='custom_field_$data->id' data-id='$data->id' class='field-row text-break-space'>$title $required</label>";

        $field .= "<div class='form-group'>" . $this->template->view("custom_fields/input_" . $data->field_type, array("field_info" => $data, "placeholder" => $placeholder)) . "</div>";

        //extract estimate id from related_to field. 2nd index should be the id
        $estimate_form_id = get_array_value(explode("-", $data->related_to), 1);

        return array(
            $field,
            $data->sort,
            modal_anchor(get_uri("estimate_requests/estimate_form_field_modal_form/" . $estimate_form_id), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_form'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimate_requests/estimate_form_field_delete"), "data-action" => "delete"))
        );
    }
}

/* End of file quotations.php */
/* Location: ./app/controllers/quotations.php */