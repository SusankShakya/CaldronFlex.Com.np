<?php

namespace App\Models;

class Variants_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'item_variants';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $item_variants_table = $this->db->prefixTable('item_variants');
        $items_table = $this->db->prefixTable('items');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $item_variants_table.id=$id";
        }

        $item_id = $this->_get_clean_value($options, "item_id");
        if ($item_id) {
            $where .= " AND $item_variants_table.item_id=$item_id";
        }

        $variant_type = $this->_get_clean_value($options, "variant_type");
        if ($variant_type) {
            $where .= " AND $item_variants_table.variant_type='$variant_type'";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status !== null) {
            $where .= " AND $item_variants_table.status=$status";
        }

        $limit_query = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $offset = $this->_get_clean_value($options, "offset");
            $limit_query = "LIMIT $offset, $limit";
        }

        $sql = "SELECT $item_variants_table.*, $items_table.title as item_title,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user
                FROM $item_variants_table
                LEFT JOIN $items_table ON $items_table.id=$item_variants_table.item_id
                LEFT JOIN $users_table ON $users_table.id=$item_variants_table.created_by
                WHERE $item_variants_table.deleted=0 $where
                ORDER BY $item_variants_table.sort_order ASC, $item_variants_table.id ASC
                $limit_query";

        return $this->db->query($sql);
    }

    function get_variant_types() {
        return array(
            "size" => app_lang("size"),
            "material" => app_lang("material"),
            "finish" => app_lang("finish"),
            "quality" => app_lang("quality")
        );
    }

    function get_price_modifier_types() {
        return array(
            "fixed" => app_lang("fixed_amount"),
            "percentage" => app_lang("percentage")
        );
    }

    function get_variants_by_item($item_id) {
        $item_variants_table = $this->db->prefixTable('item_variants');
        
        $sql = "SELECT * FROM $item_variants_table 
                WHERE item_id=$item_id AND deleted=0 AND status=1
                ORDER BY variant_type, sort_order ASC";
        
        $result = $this->db->query($sql);
        
        $variants = array();
        foreach ($result->getResult() as $variant) {
            if (!isset($variants[$variant->variant_type])) {
                $variants[$variant->variant_type] = array();
            }
            $variants[$variant->variant_type][] = $variant;
        }
        
        return $variants;
    }

    function get_variant_combinations($item_id) {
        $combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        $sql = "SELECT * FROM $combinations_table 
                WHERE item_id=$item_id AND deleted=0 AND status=1
                ORDER BY id ASC";
        
        return $this->db->query($sql);
    }

    function save_combination($data = array()) {
        $combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        if (get_array_value($data, "id")) {
            // Update existing combination
            $id = $data['id'];
            unset($data['id']);
            
            $this->db_builder = $this->db->table($combinations_table);
            $this->db_builder->where('id', $id);
            return $this->db_builder->update($data);
        } else {
            // Insert new combination
            $data['created_at'] = get_current_utc_time();
            $data['created_by'] = $this->login_user_id;
            
            $this->db_builder = $this->db->table($combinations_table);
            $this->db_builder->insert($data);
            return $this->db->insertID();
        }
    }

    function delete_combination($id) {
        $combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        $this->db_builder = $this->db->table($combinations_table);
        $this->db_builder->where('id', $id);
        return $this->db_builder->update(array('deleted' => 1));
    }

    function get_combination_details($id) {
        $combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        $sql = "SELECT * FROM $combinations_table 
                WHERE id=$id AND deleted=0 LIMIT 1";
        
        $result = $this->db->query($sql);
        return $result->getRow();
    }

    function save_price_history($data = array()) {
        $history_table = $this->db->prefixTable('variant_price_history');
        
        $data['changed_at'] = get_current_utc_time();
        $data['changed_by'] = $this->login_user_id;
        
        $this->db_builder = $this->db->table($history_table);
        $this->db_builder->insert($data);
        return $this->db->insertID();
    }

    function get_price_history($options = array()) {
        $history_table = $this->db->prefixTable('variant_price_history');
        $users_table = $this->db->prefixTable('users');
        
        $where = "";
        
        $item_id = $this->_get_clean_value($options, "item_id");
        if ($item_id) {
            $where .= " AND $history_table.item_id=$item_id";
        }
        
        $variant_id = $this->_get_clean_value($options, "variant_id");
        if ($variant_id) {
            $where .= " AND $history_table.variant_id=$variant_id";
        }
        
        $combination_id = $this->_get_clean_value($options, "combination_id");
        if ($combination_id) {
            $where .= " AND $history_table.combination_id=$combination_id";
        }
        
        $limit_query = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $offset = $this->_get_clean_value($options, "offset");
            $limit_query = "LIMIT $offset, $limit";
        }
        
        $sql = "SELECT $history_table.*,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS changed_by_user
                FROM $history_table
                LEFT JOIN $users_table ON $users_table.id=$history_table.changed_by
                WHERE 1=1 $where
                ORDER BY $history_table.changed_at DESC
                $limit_query";
        
        return $this->db->query($sql);
    }

    function check_sku_exists($sku, $exclude_id = 0) {
        $combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        $sql = "SELECT id FROM $combinations_table 
                WHERE sku='$sku' AND deleted=0";
        
        if ($exclude_id) {
            $sql .= " AND id!=$exclude_id";
        }
        
        $result = $this->db->query($sql);
        return $result->getRow() ? true : false;
    }

    function update_combination_stock($combination_id, $quantity, $operation = "add") {
        $combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        if ($operation === "add") {
            $sql = "UPDATE $combinations_table 
                    SET stock_quantity = stock_quantity + $quantity 
                    WHERE id=$combination_id";
        } else {
            $sql = "UPDATE $combinations_table 
                    SET stock_quantity = stock_quantity - $quantity 
                    WHERE id=$combination_id";
        }
        
        return $this->db->query($sql);
    }

    function get_variant_combination_by_variants($item_id, $variant_ids = array()) {
        $combinations_table = $this->db->prefixTable('item_variant_combinations');
        
        $variant_combination = json_encode($variant_ids);
        $variant_combination = $this->db->escapeString($variant_combination);
        
        $sql = "SELECT * FROM $combinations_table 
                WHERE item_id=$item_id 
                AND variant_combination='$variant_combination' 
                AND deleted=0 AND status=1 
                LIMIT 1";
        
        $result = $this->db->query($sql);
        return $result->getRow();
    }
}