<?php

namespace App\Models;

class Reminder_logs_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'reminder_logs';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $reminder_logs_table = $this->db->prefixTable('reminder_logs');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $reminder_logs_table.id=$id";
        }

        $notification_status = $this->_get_clean_value($options, "notification_status");
        if ($notification_status) {
            $where .= " AND $reminder_logs_table.notification_status='$notification_status'";
        }

        $sql = "SELECT $reminder_logs_table.*
                FROM $reminder_logs_table
                WHERE $reminder_logs_table.deleted=0 $where
                LIMIT 5";

        return $this->db->query($sql);
    }
}
