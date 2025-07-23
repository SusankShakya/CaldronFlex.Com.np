<?php

namespace App\Libraries;

class Automations {

    private $available_operators = array(
        'equal' => array("is_equal", "is_not_equal"),
        'list' => array("is_in_list", "is_not_in_list"),
        'contains' => array("is_contains", "is_not_contains"),
        'list_contains' => array("is_contains_in_list", "is_not_contains_in_list")
    );

    public function __construct() {
    }

    private function _events_config() {
        $events = array();

        $events["imap_email_received"] = array(
            "related_to" => "tickets",
            "conditions" => array(
                "email_address" => $this->_fixed_string_condition(),
                "email_subject" => $this->_searchable_string_condition(),
                "email_content" => $this->_searchable_string_condition()
            ),
            "actions" => array(
                $this->_get_reset_data_action("do_not_create_ticket")
            )
        );

        $events["new_ticket_created_by_imap_email"] = array(
            "related_to" => "tickets",
            "conditions" => array(
                "title" => $this->_searchable_string_condition(),
                "description" => $this->_searchable_string_condition()
            ),
            "actions" => array(
                $this->_get_update_field_action("assigned_to", "team_members_dropdown", "dropdown", "assign_to"),
                $this->_get_update_field_action("labels", "ticket_labels_dropdown", "multiselect_dropdown"),
                $this->_get_update_field_action("ticket_type_id", "ticket_types_dropdown", "dropdown", "ticket_type"),
            )
        );

        return $events;
    }


    function trigger_automations($event_name, &$matching_data, $id = 0) {

        $automations = $this->_get_automation_settings($event_name);
        if (!$automations) return;

        log_message('notice', 'Automation: Found active automation. Event - ' . $event_name . ', id - ' . $id);

        $all_actions = array();

        foreach ($automations as $automation) {
            if (!$automation->actions) continue; //don't have any actions, nothing to do.

            $actions = unserialize($automation->actions);

            if (!count($actions)) continue;

            if ($automation->conditions) {
                $conditions = unserialize($automation->conditions);
                if (is_array($matching_data) && $this->_does_the_conditions_match($matching_data, $conditions, $automation->matching_type)) {
                    $all_actions = array_merge($all_actions, $actions);
                }
            }
        }

        if (count($all_actions)) {
            log_message('notice', 'Automation: Conditions matched. Event - ' . $event_name . ', id - ' . $id);
            $this->_do_the_actions($event_name, $all_actions, $id, $matching_data);
        } else {
            log_message('notice', 'Automation: Conditions does not matched. Event - ' . $event_name . ', id- ' . $id . ', matching_data - ' . serialize($matching_data));
        }
    }

    function get_events_list($related_to = "") {
        $list = array();
        foreach ($this->_events_config() as $key => $value) {
            if (!$related_to || get_array_value($value, "related_to") == $related_to) {
                $list[$key] = app_lang($key);
            }
        }
        return $list;
    }

    function get_fields_dropdown($event_name) {
        $event_info = $this->_get_event($event_name);
        if (!$event_info) {
            return null;
        }
        $dropdown = array();

        foreach (get_array_value($event_info, "conditions") as $field_name => $condition) {
            $dropdown[] = array("id" => $field_name, "text" => app_lang($field_name));
        }

        return $dropdown;
    }

    function get_operators_dropdown($operators) {
        if (!is_array($operators)) return array();

        $dropdown = array();
        foreach ($operators as $operator) {
            $dropdown[] = array("id" =>  $operator, "text" => app_lang("small_letter_condition_" . $operator));
        }

        return $dropdown;
    }


    function is_a_list_field($operator) {
        if (!$operator) return false;

        $contains_at_the_end = "_in_list";

        if (substr($operator, -strlen($contains_at_the_end)) === $contains_at_the_end) {
            return true;
        }
    }

    function get_operators($event_name, $field_name) {
        $event_info = $this->_get_event($event_name);
        if (!$event_info) {
            return null;
        }

        $conditions = get_array_value($event_info, "conditions");
        $field_details = get_array_value($conditions, $field_name);

        if (!$field_details) {
            return null;
        }

        return get_array_value($field_details, "operators");
    }

    function get_action($event_name, $action_name) {

        $actions = $this->get_actions($event_name);

        if (!$actions) {
            return null;
        }

        $action_info = null;
        foreach ($actions as $action) {
            if ($action['name'] === $action_name) {
                $action_info =  $action;
            }
        }

        return $action_info;
    }

    function get_actions($event_name) {
        $event_info = $this->_get_event($event_name);

        if (!$event_info) {
            return null;
        }

        $actions = get_array_value($event_info, "actions");

        if (!$actions) {
            return null;
        }

        return $actions;
    }

    function get_actions_dropdown($event_name) {
        $actions = $this->get_actions($event_name);
        if (!$actions) {
            return null;
        }

        $dropdown = array();
        foreach ($actions as $action) {
            $ation_name = get_array_value($action, "name");
            $dropdown[] = array("id" =>  $ation_name, "text" => $ation_name);
        }

        return $dropdown;
    }

    private function _do_the_actions($event_name, $actions, $id, &$matching_data) {

        $update_field_data = array();



        foreach ($actions as $action_row) {
            $action = get_array_value($action_row, "action");
            $action_value = get_array_value($action_row, "action_value");

            $action_details = $this->get_action($event_name, $action);
            $action_type =  get_array_value($action_details, "action_type");

            if ($action_type == "update_field") {
                $db_field = get_array_value($action_details, "db_field");
                if ($db_field && $action_value) {
                    $update_field_data[$db_field] = $action_value;
                }
            } else if ($action_type == "reset_data") {
                $matching_data = null;
            }
        };

        if (count($update_field_data) > 0) {
            log_message('notice', 'Automation: Do update_field action. Event - ' . $event_name . ', id - ' . $id . ', action_type - ' . $action_type . ', Data -' . serialize($update_field_data));
            $this->_trigger_update_field_action($event_name, $update_field_data, $id);
        }
    }

    private function _trigger_update_field_action($event_name, $data, $id) {
        $event_details = $this->_get_event($event_name);
        $related_to = get_array_value($event_details, "related_to");
        if ($related_to && count($data) && $id) {
            $model = $this->_related_to_model($related_to);
            $save_id = $model->ci_save($data, $id);
            if ($save_id) {
                log_message('notice', 'Automation: Data updated in db. Event - ' . $event_name . ', save_id - ' . $save_id);
            }
        } else {
            log_message('notice', 'Automation: Required params missing. Event - ' . $event_name . ', related_to - ' . $related_to . ', id - ' . $id);
        }
    }


    private function _related_to_model($related_to) {
        $models = [
            "tickets" => "App\Models\Tickets_model",
        ];

        $model_name = get_array_value($models, $related_to);

        if ($model_name) {
            return model($model_name);
        }
    }



    private function _does_the_conditions_match($matching_data, $conditions, $matching_type) {
        foreach ($conditions as $condition) {

            $field_name = get_array_value($condition, "field_name");
            $operator = get_array_value($condition, "operator");
            $find = get_array_value($condition, "expected_value_1");

            $source_value = get_array_value($matching_data, $field_name);

            $source_value = $source_value ? strtolower($source_value) : "";
            $find =  $find ? strtolower($find) : "";

            $result = false;

            // Apply the corresponding matching function based on the operator
            switch ($operator) {
                case 'is_equal':
                    $result = $this->_is_equal($source_value, $find);
                    break;
                case 'is_not_equal':
                    $result = $this->_is_not_equal($source_value, $find);
                    break;
                case 'is_in_list':
                    $result = $this->_is_in_list($source_value, $find);
                    break;
                case 'is_not_in_list':
                    $result = $this->_is_not_in_list($source_value, $find);
                    break;
                case 'is_contains':
                    $result = $this->_is_contains($source_value, $find);
                    break;
                case 'is_not_contains':
                    $result = $this->_is_not_contains($source_value, $find);
                    break;
                case 'is_contains_in_list':
                    $result = $this->_is_contains_in_list($source_value, $find);
                    break;
                case 'is_not_contains_in_list':
                    $result = $this->_is_not_contains_in_list($source_value, $find);
                    break;
                default:
                    break;
            }

            if ($matching_type == 'match_all') {
                if (!$result) { // For "AND" logic, return false if any condition fails
                    return false;
                }
            } else if ($matching_type == 'match_any') {
                if ($result) {  // For "OR" logic, return true if any condition passes
                    return true;
                }
            }
        }

        if ($matching_type == 'match_all') {
            return true; // All conditions matched
        } else if ($matching_type == 'match_any') {
            return false; // None of the conditions matched
        }
    }

    private function _is_equal($source_value, $find) {
        return $source_value == $find;
    }

    private function _is_not_equal($source_value, $find) {
        return $this->_is_equal($source_value, $find) ? false : true;
    }

    private function _is_in_list($source_value, $find) {
        if (!$find) return false;

        $find_array = explode(",", $find);
        return in_array($source_value, $find_array);
    }

    private function _is_not_in_list($source_value, $find) {
        return $this->_is_in_list($source_value, $find) ? false : true;
    }

    private function _is_contains($source_value, $find) {
        return strpos($source_value, $find) !== false;
    }

    private function _is_not_contains($source_value, $find) {
        return $this->_is_contains($source_value, $find) ? false : true;
    }

    private function _is_contains_in_list($source_value, $find) {
        if (!$find) return false;
        $find_array = explode(",", $find);
        $found = false;
        foreach ($find_array as $find_text) {
            if ($this->_is_contains($source_value, $find_text)) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    private function _is_not_contains_in_list($source_value, $find) {
        return $this->_is_contains_in_list($source_value, $find) ? false : true;
    }

    private function _get_operators($operator_names) {
        if (count($operator_names) == 1) {
            return get_array_value($this->available_operators, $operator_names[0]);
        } else {
            $operators = array();
            foreach ($operator_names as $operator_name) {
                $operators2 = get_array_value($this->available_operators, $operator_name);

                if (is_array($operators2)) {
                    $operators = array_merge($operators, $operators2);
                }
            }
            return $operators;
        }
    }

    private function _get_automation_settings($event_name) {

        $automation_settings = get_setting("automation_settings");
        if (!$automation_settings) {
            return false; //don't have any automation for this event
        }

        $automation_settings =  explode(",", $automation_settings);

        if (!in_array($event_name, $automation_settings)) {
            return false;
        }

        $Automation_settings_model = model("App\Models\Automation_settings_model");
        return $Automation_settings_model->get_details(array("event_name" => $event_name))->getResult();
    }

    private function _get_event($event_name) {
        return get_array_value($this->_events_config(), $event_name);
    }

    private function _fixed_string_condition() {
        return array(
            "data_type" => "string",
            "operators" => $this->_get_operators(array("equal", "list"))
        );
    }

    private function _searchable_string_condition() {
        return array(
            "data_type" => "string",
            "operators" => $this->_get_operators(array("contains", "list_contains"))
        );
    }

    private function _get_reset_data_action($name) {
        return array(
            "name" => app_lang($name),
            "action_type" => "reset_data"
        );
    }

    private function _get_update_field_action($db_field, $input_name, $input_type, $field_name = "") {
        return array(
            "name" => app_lang("set_field_") . ": " . (app_lang($field_name ? $field_name : $db_field)),
            "action_type" => "update_field",
            "db_field" => $db_field,
            "input" => array(
                "name" => $input_name,
                "type" => $input_type
            )
        );
    }
}
