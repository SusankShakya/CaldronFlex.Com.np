<?php

namespace App\Models;

class Reminder_settings_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'reminder_settings';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $reminder_settings_table = $this->db->prefixTable('reminder_settings');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $reminder_settings_table.id=$id";
        }

        $context = $this->_get_clean_value($options, "context");
        if ($context) {
            $where .= " AND $reminder_settings_table.context='$context'";
        }

        $reminder_event = $this->_get_clean_value($options, "reminder_event");
        if ($reminder_event) {
            $where .= " AND $reminder_settings_table.reminder_event='$reminder_event'";
        }

        $sql = "SELECT $reminder_settings_table.*
                FROM $reminder_settings_table
                WHERE $reminder_settings_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_reminders_by_context($context) {
        $reminder_settings_table = $this->db->prefixTable('reminder_settings');

        $context = $this->_get_clean_value($context);

        $sql = "SELECT $reminder_settings_table.*
                FROM $reminder_settings_table
                WHERE $reminder_settings_table.context = '$context' AND $reminder_settings_table.deleted = 0";

        return $this->db->query($sql, [$context])->getResult();
    }

    function get_reminders($options = array()) {
        $subscriptions_table = $this->db->prefixTable('subscriptions');
        $reminder_logs_table = $this->db->prefixTable('reminder_logs');

        $where = "";

        $reminder_logs_where = "";
        $exclude_reminder_date = $this->_get_clean_value($options, "exclude_reminder_date");
        if($exclude_reminder_date){
            $reminder_logs_where .= " AND $reminder_logs_table.reminder_date='$exclude_reminder_date'";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $subscriptions_table.status='$status'";
        }

        $date_conditions = array();

        $weekly_dates = $this->_get_clean_value($options, "weekly_dates");
        if ($weekly_dates) {
            $date_conditions[] = "FIND_IN_SET($subscriptions_table.next_recurring_date, '$weekly_dates') AND $subscriptions_table.repeat_type='weeks'";
        }

        $monthly_dates = $this->_get_clean_value($options, "monthly_dates");
        if ($monthly_dates) {
            $date_conditions[] = "FIND_IN_SET($subscriptions_table.next_recurring_date, '$monthly_dates') AND $subscriptions_table.repeat_type='months'";
        }

        $yearly_dates = $this->_get_clean_value($options, "yearly_dates");
        if ($yearly_dates) {
            $date_conditions[] = "FIND_IN_SET($subscriptions_table.next_recurring_date, '$yearly_dates') AND $subscriptions_table.repeat_type='years'";
        }

        if (!empty($date_conditions)) {
            $where .= " AND (" . implode(' OR ', $date_conditions) . ")";
        }

        $sql = "SELECT $subscriptions_table.*
                FROM $subscriptions_table
                WHERE $subscriptions_table.deleted=0
                AND $subscriptions_table.id NOT IN (
                    SELECT $reminder_logs_table.context_id
                    FROM $reminder_logs_table
                    WHERE $reminder_logs_table.context='subscription' AND $reminder_logs_table.deleted=0 $reminder_logs_where
                )
                $where";

        return $this->db->query($sql);
    }
}
