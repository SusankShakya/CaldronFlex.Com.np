<?php

namespace App\Models;

class Automation_settings_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'automation_settings';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $automation_settings_table = $this->db->prefixTable('automation_settings');
        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $automation_settings_table.id=$id";
        }

        $related_to = $this->_get_clean_value($options, "related_to");
        if ($related_to) {
            $where .= " AND $automation_settings_table.related_to='$related_to'";
        }

        $event_name = $this->_get_clean_value($options, "event_name");
        if ($event_name) {
            $where .= " AND $automation_settings_table.event_name='$event_name'";
        }

        $sql = "SELECT $automation_settings_table.*
        FROM $automation_settings_table
        WHERE $automation_settings_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function save_setting($data, $id = 0) {
        $saved_id = parent::ci_save($data, $id);

        if (!$id) {
            $event_name = get_array_value($data, "event_name");
            $this->_add_an_event_in_the_automation_settings($event_name);
        }

        return $saved_id;
    }

    function delete_setting($options) {
        $id = $this->_get_clean_value($options, "id");
        $undo = $this->_get_clean_value($options, "undo");
        $info = $this->get_one($id);

        if ($info) {
            $found_rows_of_this_event = $this->get_details(array("event_name" => $info->event_name, "related_to" => $info->related_to))->resultID->num_rows;
            if ($undo) {
                if ($found_rows_of_this_event <= 0) {
                    //don't have any settings for this event, enable this event. 
                    $this->_add_an_event_in_the_automation_settings($info->event_name);
                }

                return parent::delete($id, true);
            } else {
                if ($found_rows_of_this_event <= 1) {
                    //don't have any other rows, disable this event. 
                    $this->_remove_an_event_from_the_automation_settings($info->event_name);
                }

                return parent::delete($id);
            }
        }
    }

    private function _get_automation_settings_array() {
        $automation_settings = get_setting("automation_settings");
        if (!$automation_settings) $automation_settings = "";
        $automation_settings =  explode(",", $automation_settings);
        return array_map('trim', $automation_settings);
    }

    private function _remove_an_event_from_the_automation_settings($event_name) {
        $settings = $this->_get_automation_settings_array();
        $settings = array_filter($settings, function ($item) use ($event_name) {
            return $item !== $event_name;
        });

        $automation_settings = implode(",", $settings);
        return $this->_save_automation_settings_in_settings($automation_settings);
    }

    private function _add_an_event_in_the_automation_settings($event_name) {
        $settings = $this->_get_automation_settings_array();

        array_push($settings, trim($event_name));
        $settings = array_unique($settings);

        $automation_settings = implode(",", $settings);
        return $this->_save_automation_settings_in_settings($automation_settings);
    }

    private function _save_automation_settings_in_settings($automation_settings) {

        $Settings_model = new Settings_model();
        return  $Settings_model->save_setting("automation_settings", $automation_settings);
    }
}
