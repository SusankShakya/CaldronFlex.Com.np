<?php

namespace App\Models;

class Event_tracker_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'event_tracker';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $event_tracker_table = $this->db->prefixTable("event_tracker");

        $where = "";

        $context = $this->_get_clean_value($options, "context");
        if ($context) {
            $where .= " AND $event_tracker_table.context='$context'";
        }

        $context_id = $this->_get_clean_value($options, "context_id");
        if ($context_id) {
            $where .= " AND $event_tracker_table.context_id=$context_id";
        }

        $sql = "SELECT $event_tracker_table.*
        FROM $event_tracker_table
        WHERE $event_tracker_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function total_read_count($options = array()) {
        $event_tracker_table = $this->db->prefixTable("event_tracker");

        $where = "";

        $context = $this->_get_clean_value($options, "context");
        if ($context) {
            $where .= " AND $event_tracker_table.context='$context'";
        }

        $context_id = $this->_get_clean_value($options, "context_id");
        if ($context_id) {
            $where .= " AND $event_tracker_table.context_id=$context_id";
        }

        $sql = "SELECT SUM($event_tracker_table.read_count) AS total_read_count
        FROM $event_tracker_table
        WHERE $event_tracker_table.deleted=0 $where";
        return $this->db->query($sql)->getRow()->total_read_count;
    }

}
