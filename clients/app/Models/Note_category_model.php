<?php

namespace App\Models;

class Note_category_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'note_category';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $note_category_table = $this->db->prefixTable('note_category');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $note_category_table.id=$id";
        }

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $note_category_table.user_id=$user_id";
        }

        $sql = "SELECT $note_category_table.*
        FROM $note_category_table
        WHERE $note_category_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
