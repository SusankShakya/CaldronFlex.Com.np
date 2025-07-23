<?php

namespace App\Models;

class E_invoice_templates_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'e_invoice_templates';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $e_invoice_templates_table = $this->db->prefixTable('e_invoice_templates');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $e_invoice_templates_table.id=$id";
        }

        $sql = "SELECT $e_invoice_templates_table.*
        FROM $e_invoice_templates_table
        WHERE $e_invoice_templates_table.deleted=0 $where";
        return $this->db->query($sql);
    }
}
