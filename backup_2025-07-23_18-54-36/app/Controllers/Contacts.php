<?php

namespace App\Controllers;

use App\Libraries\Excel_import;

class Contacts extends Security_Controller {

    use Excel_import;

    private $clients_id_by_title = array();

    function __construct() {
        parent::__construct();

        //check permission to access this module
        $this->init_permission_checker("client");
    }

    private function _validate_client_manage_access($client_id = 0) {
        if (!$this->can_edit_clients($client_id)) {
            app_redirect("forbidden");
        }
    }

    private function _validate_excel_import_access() {
        return $this->_validate_client_manage_access();
    }

    private function _get_controller_slag() {
        return "contacts";
    }

    private function _get_custom_field_context() {
        return "contacts";
    }

    private function _get_headers_for_import() {
        $this->_init_required_data_before_starting_import();

        return array(
            array("name" => "first_name", "required" => true, "required_message" => app_lang("import_contact_error_name_field_required")),
            array("name" => "last_name", "required" => true, "required_message" => app_lang("import_contact_error_name_field_required")),
            array("name" => "client_name", "custom_validation" => function ($client, $row_data) {
                //client field is required and chek if the client exist or not

                if (!$client) {
                    return array("error" => app_lang("import_contact__error_client_field_required"));
                } else {
                    $client_id = get_array_value($this->clients_id_by_title, $client);
                    if (!$client_id) {
                        return array("error" => app_lang("import_contact_error_client_name"));
                    }
                }
            }),
            array("name" => "email", "custom_validation" => function ($email, $row_data) {
                //validate duplicate email address
                $client_id = get_array_value($this->clients_id_by_title, trim($row_data[2]));
                if ($this->Users_model->is_email_exists($email, $client_id)) {
                    return array("error" => app_lang("duplicate_email"));
                }
            }),
            array("name" => "phone"),
            array("name" => "job_title"),
            array("name" => "gender", "custom_validation" => function ($gender, $row_data) {
                //the gender should match criterias
                $gender_values_array = array("male", "female", "other");
                if ($gender && !in_array(strtolower($gender), $gender_values_array)) {
                    return array("error" => app_lang("import_gender_is_invalid"));
                }
            })
        );
    }

    function download_sample_excel_file() {
        $this->_validate_client_manage_access();
        return $this->download_app_files(get_setting("system_file_path"), serialize(array(array("file_name" => "import-client-contacts-sample.xlsx"))));
    }

    private function _init_required_data_before_starting_import() {

        $clients = $this->Clients_model->get_clients_id_and_name()->getResult();
        $clients_id_by_title = array();
        foreach ($clients as $client) {
            $clients_id_by_title[$client->name] = $client->id;
        }

        $this->clients_id_by_title = $clients_id_by_title;
    }

    private function _save_a_row_of_excel_data($row_data) {
        $now = get_current_utc_time();

        $contact_data_array = $this->_prepare_contact_data($row_data);
        $contact_data = get_array_value($contact_data_array, "contact_data");
        $custom_field_values_array = get_array_value($contact_data_array, "custom_field_values_array");

        //couldn't prepare valid data
        if (!($contact_data && count($contact_data) > 1)) {
            return false;
        }

        //found information about lead, add some additional info
        $contact_data["user_type"] = "client";
        $contact_data["created_at"] = $now;

        //save contact data
        $saved_id = $this->Users_model->ci_save($contact_data);
        if (!$saved_id) {
            return false;
        }

        //save custom fields
        $this->_save_custom_fields($saved_id, $custom_field_values_array);
        return true;
    }

    private function _prepare_contact_data($row_data) {

        $contact_data = array("client_permissions" => "all");
        $custom_field_values_array = array();

        foreach ($row_data as $column_index => $value) {
            if (!$value) {
                continue;
            }

            $column_name = $this->_get_column_name($column_index);
            if ($column_name == "client_name") {
                //get existing client

                $client_id = get_array_value($this->clients_id_by_title, trim($value));
                if ($client_id) {
                    $contact_data["client_id"] = $client_id;
                }
            } else if (strpos($column_name, 'cf') !== false) {
                $this->_prepare_custom_field_values_array($column_name, $value, $custom_field_values_array);
            } else {
                $contact_data[$column_name] = $value;
            }
        }

        return array(
            "contact_data" => $contact_data,
            "custom_field_values_array" => $custom_field_values_array
        );
    }
}

/* End of file Contacts.php */
/* Location: ./app/controllers/contacts.php */