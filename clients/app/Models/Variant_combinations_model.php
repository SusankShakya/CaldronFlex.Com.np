<?php

namespace App\Models;

class Variant_combinations_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'item_variant_combinations';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $variant_combinations_table = $this->db->prefixTable('item_variant_combinations');
        $items_table = $this->db->prefixTable('items');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $variant_combinations_table.id=$id";
        }

        $item_id = $this->_get_clean_value($options, "item_id");
        if ($item_id) {
            $where .= " AND $variant_combinations_table.item_id=$item_id";
        }

        $sku = $this->_get_clean_value($options, "sku");
        if ($sku) {
            $where .= " AND $variant_combinations_table.sku='$sku'";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status !== null) {
            $where .= " AND $variant_combinations_table.status=$status";
        }

        $sql = "SELECT $variant_combinations_table.*, $items_table.title as item_title,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user
                FROM $variant_combinations_table
                LEFT JOIN $items_table ON $items_table.id=$variant_combinations_table.item_id
                LEFT JOIN $users_table ON $users_table.id=$variant_combinations_table.created_by
                WHERE $variant_combinations_table.deleted=0 $where";

        return $this->db->query($sql);
    }

    function get_available_combinations($item_id) {
        $variant_combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        $sql = "SELECT * FROM $variant_combinations_table
                WHERE item_id=$item_id 
                AND deleted=0 
                AND status=1 
                AND (stock_quantity > 0 OR stock_quantity IS NULL)
                ORDER BY id";
                
        return $this->db->query($sql)->getResult();
    }

    function update_stock($combination_id, $quantity_change) {
        $variant_combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        $sql = "UPDATE $variant_combinations_table 
                SET stock_quantity = stock_quantity + $quantity_change 
                WHERE id=$combination_id";
                
        return $this->db->query($sql);
    }

    function get_combination_price_adjustment($combination_id) {
        $combination = $this->get_one($combination_id);
        if ($combination) {
            return $combination->additional_price;
        }
        return 0;
    }
}