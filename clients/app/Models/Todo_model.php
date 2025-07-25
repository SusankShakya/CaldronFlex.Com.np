<?php

namespace App\Models;

class Todo_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'to_do';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $todo_table = $this->db->prefixTable('to_do');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $todo_table.id=$id";
        }


        $created_by = $this->_get_clean_value($options, "created_by");
        if ($created_by) {
            $where .= " AND $todo_table.created_by=$created_by";
        }


        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND FIND_IN_SET($todo_table.status,'$status')";
        }

        $select_labels_data_query = $this->get_labels_data_query();

        $sql = "SELECT $todo_table.*, $select_labels_data_query
        FROM $todo_table
        WHERE $todo_table.deleted=0 $where
        ORDER BY $todo_table.sort DESC";
        return $this->db->query($sql);
    }

    function get_label_suggestions($user_id) {
        $user_id = $this->_get_clean_value(array("user_id" => $user_id), "user_id");

        $todo_table = $this->db->prefixTable('to_do');
        $sql = "SELECT GROUP_CONCAT(labels) as label_groups
        FROM $todo_table
        WHERE $todo_table.deleted=0 AND $todo_table.created_by=$user_id";
        return $this->db->query($sql)->getRow()->label_groups;
    }

    function get_search_suggestion($search = "", $created_by = 0) {
        $todo_table = $this->db->prefixTable('to_do');
        $created_by = $this->_get_clean_value($created_by);

        $where = "";
        $search = $this->_get_clean_value($search);
        if ($search) {
            $search = $this->db->escapeLikeString($search);
            $where .= " AND $todo_table.title LIKE '%$search%' ESCAPE '!' ";
        }

        $sql = "SELECT $todo_table.id, $todo_table.title
        FROM $todo_table  
        WHERE $todo_table.deleted=0 AND $todo_table.created_by=$created_by $where
        ORDER BY $todo_table.title ASC
        LIMIT 0, 10";

        return $this->db->query($sql);
    }

    function get_next_sort_value($created_by) {
        $todo_table = $this->db->prefixTable('to_do');

        $created_by = $this->_get_clean_value($created_by);

        $sql = "SELECT $todo_table.sort
        FROM $todo_table
        WHERE $todo_table.deleted=0 AND $todo_table.created_by = $created_by
        ORDER BY $todo_table.sort DESC LIMIT 1";

        $row = $this->db->query($sql)->getRow();

        if ($row) {
            return $row->sort + 50;
        } else {
            return 1000; //could be any positive value
        }
    }
}
