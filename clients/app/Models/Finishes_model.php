<?php

namespace App\Models;

use App\Models\Crud_model;

class Finishes_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'finishes';
        parent::__construct($this->table);
    }

    /**
     * Get active finishes
     */
    function get_active_finishes($type = null) {
        $where = array("is_active" => 1, "deleted" => 0);
        
        if ($type) {
            $where["type"] = $type;
        }
        
        return $this->get_all_where($where, 0, 0, "sort_order")->getResult();
    }

    /**
     * Get finishes for an item
     */
    function get_item_finishes($item_id, $material_id = null) {
        $sql = "SELECT f.*, if.is_default, if.material_id 
                FROM finishes f
                JOIN item_finishes if ON f.id = if.finish_id
                WHERE if.item_id = ? AND f.deleted = 0 AND f.is_active = 1";
        
        $params = array($item_id);
        
        if ($material_id) {
            $sql .= " AND (if.material_id = ? OR if.material_id IS NULL)";
            $params[] = $material_id;
        }
        
        $sql .= " ORDER BY f.sort_order";
        
        return $this->db->query($sql, $params)->getResult();
    }

    /**
     * Get finish by code
     */
    function get_finish_by_code($code) {
        return $this->get_one_where(array("code" => $code, "deleted" => 0));
    }

    /**
     * Calculate finish price modifier
     */
    function calculate_finish_price($base_price, $finish_id) {
        $finish = $this->get_one($finish_id);
        
        if (!$finish) {
            return $base_price;
        }
        
        if ($finish->price_modifier_type == 'percentage') {
            return $base_price + ($base_price * $finish->price_modifier / 100);
        } else {
            return $base_price + $finish->price_modifier;
        }
    }

    /**
     * Get finish types
     */
    function get_finish_types() {
        $sql = "SELECT DISTINCT type 
                FROM finishes 
                WHERE deleted = 0 AND type IS NOT NULL 
                ORDER BY type";
        
        return $this->db->query($sql)->getResult();
    }

    /**
     * Check if finish is used in any item
     */
    function is_finish_used($finish_id) {
        $sql = "SELECT COUNT(*) as count 
                FROM item_finishes 
                WHERE finish_id = ?";
        
        $result = $this->db->query($sql, array($finish_id))->getRow();
        return $result->count > 0;
    }

    /**
     * Set default finish for item
     */
    function set_default_finish($item_id, $finish_id, $material_id = null) {
        // First, remove all defaults for this item/material combination
        $where = array('item_id' => $item_id);
        if ($material_id) {
            $where['material_id'] = $material_id;
        }
        
        $this->db->table('item_finishes')
            ->where($where)
            ->update(array('is_default' => 0));
        
        // Then set the new default
        $where['finish_id'] = $finish_id;
        
        return $this->db->table('item_finishes')
            ->where($where)
            ->update(array('is_default' => 1));
    }

    /**
     * Add finish to item
     */
    function add_item_finish($item_id, $finish_id, $material_id = null, $is_default = 0) {
        // Check if already exists
        $where = array(
            'item_id' => $item_id,
            'finish_id' => $finish_id
        );
        
        if ($material_id) {
            $where['material_id'] = $material_id;
        } else {
            $where['material_id IS'] = NULL;
        }
        
        $existing = $this->db->table('item_finishes')
            ->where($where)
            ->get()
            ->getRow();
        
        if ($existing) {
            return true;
        }
        
        $data = array(
            'item_id' => $item_id,
            'finish_id' => $finish_id,
            'material_id' => $material_id,
            'is_default' => $is_default,
            'created_at' => get_current_utc_time()
        );
        
        return $this->db->table('item_finishes')->insert($data);
    }

    /**
     * Remove finish from item
     */
    function remove_item_finish($item_id, $finish_id, $material_id = null) {
        $where = array(
            'item_id' => $item_id,
            'finish_id' => $finish_id
        );
        
        if ($material_id) {
            $where['material_id'] = $material_id;
        }
        
        return $this->db->table('item_finishes')
            ->where($where)
            ->delete();
    }

    /**
     * Get finishes with pricing for item
     */
    function get_finishes_with_pricing($item_id, $material_id = null) {
        $item = $this->db->table('items')->where('id', $item_id)->get()->getRow();
        
        if (!$item) {
            return array();
        }
        
        $finishes = $this->get_item_finishes($item_id, $material_id);
        
        foreach ($finishes as &$finish) {
            $finish->calculated_price = $this->calculate_finish_price($item->rate, $finish->id);
            $finish->price_difference = $finish->calculated_price - $item->rate;
            $finish->total_processing_time = $finish->processing_time ? $finish->processing_time : 0;
        }
        
        return $finishes;
    }

    /**
     * Get material-specific finishes
     */
    function get_material_finishes($item_id, $material_id) {
        return $this->get_item_finishes($item_id, $material_id);
    }
}