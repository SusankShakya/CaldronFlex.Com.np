<?php

namespace App\Controllers;

use App\Libraries\Automations;

class Automation extends Security_Controller {

    private $Automation_settings_model;
    private $automations;

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->automations = new Automations();
        $this->Automation_settings_model = model('App\Models\Automation_settings_model');
    }

    function index() {
        show_404();
    }

    function ticket_automation() {
        return $this->template->view("automation/settings/tickets");
    }

    function modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "related_to" => "string"
        ));

        $model_info = $this->Automation_settings_model->get_one($this->request->getPost('id'));
        $model_info->related_to = $model_info->related_to ? $model_info->related_to : $this->request->getPost('related_to');

        if ($model_info->conditions) {
            $conditions = unserialize($model_info->conditions);
            $model_info->field_name_dropdown = $this->_get_condition_field_name_dropdown($model_info->event_name);

            $model_info->conditions = array_map(function ($row) use ($model_info) {
                return $this->_prepare_conditions_row($row, $model_info);
            }, $conditions);
        }

        if ($model_info->actions) {
            $actions = unserialize($model_info->actions);
            $model_info->action_dropdown = $this->_get_action_dropdown($model_info->event_name);

            $model_info->actions = array_map(function ($row) use ($model_info) {
                return $this->_prepare_actions_row($row, $model_info);
            }, $actions);
        }

        $view_data['model_info'] = $model_info;
        $view_data["automation_events_dropdown"] = array_merge(array("" => "-"), $this->automations->get_events_list($model_info->related_to));

        return $this->template->view('automation/modal_form', $view_data);
    }



    function save() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "string|required",
            "matching_type" => "string|required",
            "event_name" => "string|required",
            "related_to" => "string|required"
        ));

        $id = $this->request->getPost('id');

        $data = array(
            "title" => $this->request->getPost('title'),
            "event_name" => $this->request->getPost('event_name'),
            "matching_type" => $this->request->getPost('matching_type'),
            "related_to" => $this->request->getPost('related_to'),
        );

        $conditions = array();
        $conditions_row_count = $this->request->getPost('conditions_row_count');
        $conditions_row_count = $conditions_row_count ? $conditions_row_count : 0;

        for ($i = 1; $i <= $conditions_row_count; $i++) {
            array_push(
                $conditions,
                array(
                    "field_name" => $this->request->getPost('field_name_' . $i),
                    "operator" => $this->request->getPost('operator_' . $i),
                    "expected_value_1" => $this->request->getPost('expected_value_1_' . $i)
                )
            );
        }

        $actions = array();
        $actions_row_count = $this->request->getPost('actions_row_count');
        $actions_row_count = $actions_row_count ? $actions_row_count : 0;

        for ($i = 1; $i <= $actions_row_count; $i++) {
            array_push(
                $actions,
                array(
                    "action" => $this->request->getPost('action_' . $i),
                    "action_value" => $this->request->getPost('action_value_' . $i),
                )
            );
        }

        $data = clean_data($data);

        $data["conditions"] = serialize($conditions);
        $data["actions"] = serialize($actions);

        $save_id = $this->Automation_settings_model->save_setting($data, $id);
        echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, "message" => app_lang("record_saved")));
    }

    function list_data($related_to = "") {
        if (!$related_to) {
            show_404();
        }

        $options = array("related_to" => $related_to);
        $list_data = $this->Automation_settings_model->get_details($options)->getResult();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    function delete() {
        $id = $this->request->getPost('id');
        validate_numeric_value($id);

        if (!$id) return false;

        if ($this->request->getPost('undo')) {
            if ($this->Automation_settings_model->delete_setting(array("id" => $id, "undo" => true))) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            $deleted = $this->Automation_settings_model->delete_setting(array("id" => $id));
            if ($deleted) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('something_went_wrong')));
            }
        }
    }


    function add_condition_row() {

        $this->validate_submitted_data(array(
            "event_name" => "string|required"
        ));

        $event_name = $this->request->getPost('event_name');

        $result = array();
        $result = $this->_prepare_field_name($result, $event_name);

        echo json_encode($result);
    }

    function update_condition_row() {

        $this->validate_submitted_data(array(
            "event_name" => "string|required",
            "value" => "string|required",
            "field_type" => "string|required"
        ));

        $event_name = $this->request->getPost('event_name');
        $field_type = $this->request->getPost('field_type');
        $field_type_value = $this->request->getPost('value');

        $result = array();

        if ($field_type == "field_name") {
            $result = $this->_prepare_operator($result, $event_name, $field_type_value);
            $result = $this->_prepare_expected_value_1($result, get_array_value($result, "operator"));
        } else if ($field_type == "operator") {
            $result = $this->_prepare_expected_value_1($result, $field_type_value);
        } else if ($field_type == "expected_value_1") {
            $result = $this->_prepare_expected_value_1($result, $this->request->getPost('selected_operator'), $field_type_value);
        }

        $result["success"] = true;
        echo json_encode($result);
    }

    function add_action_row() {
        $event_name = $this->request->getPost('event_name');

        $this->validate_submitted_data(array(
            "event_name" => "string|required"
        ));

        $result = array();
        $result = $this->_prepare_action($result, $event_name);

        echo json_encode($result);
    }

    function update_action_row() {
        $this->validate_submitted_data(array(
            "event_name" => "string|required",
            "value" => "string|required",
            "field_type" => "string|required"
        ));

        $event_name = $this->request->getPost('event_name');
        $field_type = $this->request->getPost('field_type');
        $field_type_value = $this->request->getPost('value');

        $result = array();

        if ($field_type == "action") {
            $result = $this->_prepare_action_value($result, $event_name, $field_type_value);
        } else if ($field_type == "action_value") {
            $result = $this->_prepare_action_value($result, $event_name, $this->request->getPost('selected_action'), $field_type_value);
        }

        $result["success"] = true;
        echo json_encode($result);
    }

    private function _prepare_field_name($result, $event_name, $value = "", $field_name_dropdown = null) {

        $result["field_name_text"] = $this->_get_select_tag($value ? app_lang($value) : app_lang("small_letter_field"), "field_name", $value);
        $result["field_name_dropdown"] = $field_name_dropdown ? $field_name_dropdown : $this->_get_condition_field_name_dropdown($event_name);

        return $result;
    }

    private function _prepare_operator($result, $event_name, $field_name, $selected_operator = "") {

        $operator_dropdown = $this->_get_condition_operator_dropdown($event_name, $field_name);
        if (!$selected_operator) {
            $selected_operator = get_array_value($operator_dropdown, 0);
            $selected_operator = $selected_operator ? get_array_value($selected_operator, "id") : "";
        }


        $result["operator"] = $selected_operator;
        $result["operator_dropdown"] = $operator_dropdown;
        $result["operator_text"] = $this->_get_select_tag(app_lang("small_letter_condition_" . $selected_operator), "operator", $selected_operator);

        return $result;
    }

    private function _prepare_expected_value_1($result, $operator, $value = "") {

        $is_a_list = $this->automations->is_a_list_field($operator);
        $default_title = $value ? $value : app_lang("small_letter_something");

        $result["expected_value_1"] = $value;

        if ($value) {
            $title_array = explode(",", $default_title);

            $title = "";
            $class = "";
            if (count($title_array) > 1) {
                $class = "text-default badge bg-off-white font-100p  white-space-normal mr5 text-left";
            }

            foreach ($title_array as $_title) {
                $title .= "<div class='$class'>" . $_title . "</div>";
            }
        } else {
            $title = $default_title;
        }

        if ($is_a_list) {
            $result["expected_value_1_text"] = $this->_get_select_tag($title, "expected_value_1", $value, true);
        } else {
            $result["expected_value_1_text"] = $this->_get_clickable_tag($title, "expected_value_1", $value, array("data-show-buttons" => "1", "data-action-type" => "text"));
        }

        return $result;
    }

    private function _prepare_action($result, $event_name, $value = "", $action_dropdown = null) {
        $result["action_text"] = $this->_get_select_tag($value ? $value : app_lang("do_something"), "action", $value);
        $result["action_dropdown"] = $action_dropdown ? $action_dropdown : $this->_get_action_dropdown($event_name);

        return $result;
    }

    private function _prepare_action_value($result, $event_name, $action, $action_value = "") {
        if ($action) {
            $action_details = $this->automations->get_action($event_name, $action);

            if (!$action_details) {
                echo json_encode(array("success" => false, "message" => "something_went_wrong"));
                exit();
            }

            $input = get_array_value($action_details, "input");
            if ($input) {
                $select_type = get_array_value($input, "type");
                $result["action_value_dropdown"] = $this->_get_input_dropdown(get_array_value($input, "name"));
                $value_text =  $this->_get_dropdow_values_from_ids($result["action_value_dropdown"], $action_value);
                $result["action_value_text"] = $this->_get_select_tag($value_text ? $value_text : app_lang("small_letter_something"), "action_value", $action_value, false, $select_type);
            }
        }

        return $result;
    }

    private function _prepare_actions_row($row, $model_info) {
        $action = get_array_value($row, "action");
        $action_value = get_array_value($row, "action_value");

        $row = $this->_prepare_action($row, $model_info->event_name, $action, $model_info->action_dropdown);
        $row = $this->_prepare_action_value($row, $model_info->event_name, $action, $action_value);

        return $row;
    }


    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Automation_settings_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    private function _make_row($data) {

        $option_links = modal_anchor(get_uri("automation/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_automation'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_automation'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("automation/delete"), "data-action" => "delete"));

        return array(
            $data->title,
            app_lang($data->event_name),
            $option_links
        );
    }

    private function _prepare_conditions_row($row, $model_info) {
        $field_name = get_array_value($row, "field_name");
        $operator = get_array_value($row, "operator");
        $expected_value_1 = get_array_value($row, "expected_value_1");

        $row = $this->_prepare_field_name($row, $model_info->event_name, $field_name, $model_info->field_name_dropdown);
        $row = $this->_prepare_operator($row, $model_info->event_name, $field_name, $operator);
        $row = $this->_prepare_expected_value_1($row, $operator, $expected_value_1);

        return $row;
    }

    private function _get_condition_field_name_dropdown($event_name) {
        $field_name_dropdown = $this->automations->get_fields_dropdown($event_name);
        if (!$field_name_dropdown) {
            echo json_encode(array("success" => false, "message" => "something_went_wrong"));
            exit();
        }

        return  $field_name_dropdown;
    }

    private function _get_condition_operator_dropdown($event_name, $field_name) {
        $operators = $this->automations->get_operators($event_name, $field_name);
        $operator_dropdown = $this->automations->get_operators_dropdown($operators);
        if (!$operator_dropdown) {
            echo json_encode(array("success" => false, "message" => "something_went_wrong"));
            exit();
        }
        return  $operator_dropdown;
    }

    private function _get_action_dropdown($event_name) {
        $action_dropdown = $this->automations->get_actions_dropdown($event_name);
        if (!$action_dropdown) {
            echo json_encode(array("success" => false, "message" => "something_went_wrong"));
            exit();
        }
        return $action_dropdown;
    }


    private function _get_select_tag($title, $name, $value = "", $support_tags = false, $select_type = false) {
        $options = array(
            "data-class-name" => "w200",
            "data-placeholder" => app_lang("select_placeholder")
        );

        if ($support_tags) {
            $options["data-class-name"] = "w250";
            $options["data-placeholder"] = app_lang("select_placeholder_type_and_press_enter");

            $options["data-can-create-tags"] = "1";
        }

        if ($select_type == "multiselect_dropdown") {
            $options["data-class-name"] = "w250";
            $options["data-multiple-tags"] = "1";
        }

        return $this->_get_clickable_tag($title, $name, $value,  $options);
    }

    private function _get_clickable_tag($title, $name, $value = "", $attrs = array()) {
        $class = "";
        if (!$value) {
            $class = "empty-input-tag";
        }

        $value_array = explode(",", $value);

        if (count($value_array) == 1) {
            $class .= " single-input-tag";
        }


        $options = array(
            'title' => "",
            'class' => "text-default badge bg-light font-100p mr15 inline-block text-left $class condition-field-" . strtolower($name),
            "data-value" => $value,
            "data-act" => "automation-inline-edit",
            "data-act-name" => $name,
            "data-rule-required" => true,
            "data-msg-required" => app_lang("field_required"),
        );

        foreach ($attrs as $attr_name => $attr_value) {
            $options[$attr_name] = $attr_value;
        }

        return js_anchor($title,  $options);
    }

    private function _get_input_dropdown($name) {
        switch ($name) {
            case "team_members_dropdown":
                return $this->_get_team_members_dropdown();
            case "ticket_labels_dropdown":
                return $this->_get_ticket_labels_dropdown();
            case "ticket_types_dropdown":
                return $this->_get_ticket_types_dropdown();
            default:
                return null;
        }
    }

    private function _get_team_members_dropdown() {
        $list = $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", array("deleted" => 0, "status" => "active", "user_type" => "staff"));
        return $this->_get_dropdown($list);
    }

    private function _get_ticket_labels_dropdown() {
        return $this->make_labels_dropdown("ticket", "");
    }

    private function _get_ticket_types_dropdown() {
        $list = $this->Ticket_types_model->get_dropdown_list(array("title"), "id");
        return $this->_get_dropdown($list);
    }

    private function _get_dropdown($list) {
        $dropdown = array();
        foreach ($list as $key => $value) {
            $dropdown[] = array("id" => $key, "text" => $value);
        }
        return $dropdown;
    }


    function _get_dropdow_values_from_ids($dropdown, $comma_separated_ids) {
        if (!$comma_separated_ids) return "";
        $ids = explode(",", $comma_separated_ids);

        $texts = [];
        foreach ($dropdown as $item) {
            if (in_array($item['id'], $ids)) {
                $texts[] = $item['text'];
            }
        }
        return implode(', ', $texts);
    }
}

/* End of file Automation.php */
/* Location: ./app/Controllers/Automation.php */