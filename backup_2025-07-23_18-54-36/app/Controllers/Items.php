<?php

namespace App\Controllers;

use App\Libraries\Excel_import;

class Items extends Security_Controller {

    use Excel_import;

    private $categories_id_by_title = array();

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("order");
    }

    protected function validate_access_to_items() {
        $access_invoice = $this->get_access_info("invoice");
        $access_estimate = $this->get_access_info("estimate");

        //don't show the items if invoice/estimate module is not enabled
        if (!(get_setting("module_invoice") == "1" || get_setting("module_estimate") == "1")) {
            app_redirect("forbidden");
        }

        if ($this->login_user->is_admin) {
            return true;
        } else if (
            $access_invoice->access_type === "all"
            || $access_invoice->access_type === "manage_own_client_invoices"
            || $access_invoice->access_type === "manage_own_client_invoices_except_delete"
            || $access_invoice->access_type === "manage_only_own_created_invoices"
            || $access_invoice->access_type === "manage_only_own_created_invoices_except_delete"
            || $access_estimate->access_type === "all"
            || $access_estimate->access_type === "own"
        ) {
            return true;
        } else {
            app_redirect("forbidden");
        }
    }

    //load items list view
    function index() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $view_data['categories_dropdown'] = $this->_get_categories_dropdown();

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("items", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("items", $this->login_user->is_admin, $this->login_user->user_type);

        return $this->template->rander("items/index", $view_data);
    }

    //get categories dropdown
    private function _get_categories_dropdown() {
        $categories = $this->Item_categories_model->get_all_where(array("deleted" => 0), 0, 0, "title")->getResult();

        $categories_dropdown = array(array("id" => "", "text" => "- " . app_lang("category") . " -"));
        foreach ($categories as $category) {
            $categories_dropdown[] = array("id" => $category->id, "text" => $category->title);
        }

        return json_encode($categories_dropdown);
    }

    /* load item modal */

    function modal_form() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Items_model->get_one($this->request->getPost('id'));
        $view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("items", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        return $this->template->view('items/modal_form', $view_data);
    }

    /* add or edit an item */

    function save() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "category_id" => "required",
        ));

        $id = $this->request->getPost('id');

        $item_data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "category_id" => $this->request->getPost('category_id'),
            "unit_type" => $this->request->getPost('unit_type'),
            "rate" => unformat_currency($this->request->getPost('item_rate')),
            "show_in_client_portal" => $this->request->getPost('show_in_client_portal') ? $this->request->getPost('show_in_client_portal') : "",
            "taxable" => ""
        );

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "item");
        $new_files = unserialize($files_data);

        if ($id) {
            $item_info = $this->Items_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $item_info->files, $new_files);
        }

        $item_data["files"] = serialize($new_files);

        $item_id = $this->Items_model->ci_save($item_data, $id);
        if ($item_id) {
            save_custom_fields("items", $item_id, $this->login_user->is_admin, $this->login_user->user_type);

            echo json_encode(array("success" => true, "id" => $item_id, "data" => $this->_item_row_data($item_id), 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete or undo an item */

    function delete() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Items_model->delete($id, true)) {
                echo json_encode(array("success" => true, "id" => $id, "data" => $this->_item_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Items_model->delete($id)) {
                $item_info = $this->Items_model->get_one($id);
                echo json_encode(array("success" => true, "id" => $item_info->id, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of items, prepared for datatable  */

    function list_data() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("items", $this->login_user->is_admin, $this->login_user->user_type);

        $category_id = $this->request->getPost('category_id');
        $options = array(
            "category_id" => $category_id,
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("items", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $list_data = $this->Items_model->get_details($options)->getResult();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* return a row of item list table */

    private function _item_row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("items", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Items_model->get_details($options)->getRow();
        return $this->_make_item_row($data, $custom_fields);
    }

    /* prepare a row of item list table */

    private function _make_item_row($data, $custom_fields) {
        $type = $data->unit_type ? $data->unit_type : "";

        $show_in_client_portal_icon = "";
        if ($data->show_in_client_portal && get_setting("module_order")) {
            $show_in_client_portal_icon = "<span title='" . app_lang("showing_in_client_portal") . "'><i data-feather='shopping-bag' class='icon-16'></i></span> ";
        }

        $row_data =  array(
            modal_anchor(get_uri("items/view"), $show_in_client_portal_icon . $data->title, array("title" => app_lang("item_details"), "data-post-id" => $data->id)),
            custom_nl2br($data->description ? $data->description : ""),
            $data->category_title ? $data->category_title : "-",
            $type,
            to_decimal_format($data->rate)
        );

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
        }

        $row_data[] = modal_anchor(get_uri("items/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_item'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("items/delete"), "data-action" => "delete"));

        return $row_data;
    }

    function view() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $model_info = $this->Items_model->get_details(array("id" => $this->request->getPost('id'), "login_user_id" => $this->login_user->id))->getRow();

        $view_data['model_info'] = $model_info;
        $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
        $view_data['custom_fields_list'] = $this->Custom_fields_model->get_combined_details("items", $model_info->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        return $this->template->view('items/view', $view_data);
    }

    function save_files_sort() {
        $this->access_only_allowed_members();
        $id = $this->request->getPost("id");
        $sort_values = $this->request->getPost("sort_values");
        if ($id && $sort_values) {
            //extract the values from the :,: separated string
            $sort_array = explode(":,:", $sort_values);

            $item_info = $this->Items_model->get_one($id);
            if ($item_info->id) {
                $updated_file_indexes = update_file_indexes($item_info->files, $sort_array);
                $item_data = array(
                    "files" => serialize($updated_file_indexes)
                );

                $this->Items_model->ci_save($item_data, $id);
            }
        }
    }

    private function _validate_excel_import_access() {
        return ($this->access_only_team_members() && $this->validate_access_to_items());
    }

    private function _get_controller_slag() {
        return "items";
    }

    private function _get_custom_field_context() {
        return "items";
    }

    private function _get_headers_for_import() {
        return array(
            array("name" => "title", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("title"))),
            array("name" => "description"),
            array("name" => "category", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("category"))),
            array("name" => "unit_type"),
            array("name" => "rate", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("rate"))),
            array("name" => "show_in_client_portal")
        );
    }

    function download_sample_excel_file() {
        $this->access_only_team_members();
        $this->validate_access_to_items();
        return $this->download_app_files(get_setting("system_file_path"), serialize(array(array("file_name" => "import-items-sample.xlsx"))));
    }

    private function _init_required_data_before_starting_import() {
        $categories = $this->Item_categories_model->get_details()->getResult();
        $categories_id_by_title = array();
        foreach ($categories as $category) {
            $categories_id_by_title[$category->title] = $category->id;
        }

        $this->categories_id_by_title = $categories_id_by_title;
    }

    private function _save_a_row_of_excel_data($row_data) {
        $item_data_array = $this->_prepare_item_data($row_data);
        $item_data = get_array_value($item_data_array, "item_data");

        //couldn't prepare valid data
        if (!($item_data && count($item_data) > 1)) {
            return false;
        }

        //save item data
        $saved_id = $this->Items_model->ci_save($item_data);
        if (!$saved_id) {
            return false;
        }
    }

    private function _prepare_item_data($row_data) {

        $item_data = array();

        foreach ($row_data as $column_index => $value) {
            if (!$value) {
                continue;
            }

            $column_name = $this->_get_column_name($column_index);
            if ($column_name == "category") {
                $category_id = get_array_value($this->categories_id_by_title, $value);
                if ($category_id) {
                    $item_data["category_id"] = $category_id;
                } else {
                    $category_data = array("title" => $value);
                    $saved_category_id = $this->Item_categories_model->ci_save($category_data);
                    $item_data["category_id"] = $saved_category_id;
                    $this->categories_id_by_title[$value] = $saved_category_id;
                }
            } else if ($column_name == "rate") {
                $item_data["rate"] = unformat_currency($value);
            } else if ($column_name == "show_in_client_portal") {
                $item_data["show_in_client_portal"] = ($value === "Yes" ? 1 : "");
            } else {
                $item_data[$column_name] = $value;
            }
        }

        return array(
            "item_data" => $item_data
        );
    }
}

/* End of file items.php */
/* Location: ./app/controllers/items.php */