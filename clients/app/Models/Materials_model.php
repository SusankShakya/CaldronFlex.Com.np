<?php

namespace App\Models;

use App\Models\Crud_model;

class Materials_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'materials';
        parent::__construct($this->table);
    }

    /**
     * Get active materials
     */
    function get_active_materials($category = null) {
        $where = array("is_active" => 1, "deleted" => 0);
        
        if ($category) {
            $where["category"] = $category;
        }
        
        return $this->get_all_where($where, 0, 0, "sort_order")->getResult();
    }

    /**
     * Get materials for an item
     */
    function get_item_materials($item_id) {
        $sql = "SELECT m.*, im.is_default 
                FROM materials m
                JOIN item_materials im ON m.id = im.material_id
                WHERE im.item_id = ? AND m.deleted = 0 AND m.is_active = 1
                ORDER BY m.sort_order";
        
        return $this->db->query($sql, array($item_id))->getResult();
    }

    /**
     * Get material by code
     */
    function get_material_by_code($code) {
        return $this->get_one_where(array("code" => $code, "deleted" => 0));
    }

    /**
     * Calculate material price modifier
     */
    function calculate_material_price($base_price, $material_id) {
        $material = $this->get_one($material_id);
        
        if (!$material) {
            return $base_price;
        }
        
        if ($material->price_modifier_type == 'percentage') {
            return $base_price + ($base_price * $material->price_modifier / 100);
        } else {
            return $base_price + $material->price_modifier;
        }
    }

    /**
     * Get material categories
     */
    function get_material_categories() {
        $sql = "SELECT DISTINCT category 
                FROM materials 
                WHERE deleted = 0 AND category IS NOT NULL 
                ORDER BY category";
        
        return $this->db->query($sql)->getResult();
    }

    /**
     * Check if material is used in any item
     */
    function is_material_used($material_id) {
        $sql = "SELECT COUNT(*) as count 
                FROM item_materials 
                WHERE material_id = ?";
        
        $result = $this->db->query($sql, array($material_id))->getRow();
        return $result->count > 0;
    }

    /**
     * Set default material for item
     */
    function set_default_material($item_id, $material_id) {
        // First, remove all defaults for this item
        $this->db->table('item_materials')
            ->where('item_id', $item_id)
            ->update(array('is_default' => 0));
        
        // Then set the new default
        return $this->db->table('item_materials')
            ->where('item_id', $item_id)
            ->where('material_id', $material_id)
            ->update(array('is_default' => 1));
    }

    /**
     * Add material to item
     */
    function add_item_material($item_id, $material_id, $is_default = 0) {
        // Check if already exists
        $existing = $this->db->table('item_materials')
            ->where('item_id', $item_id)
            ->where('material_id', $material_id)
            ->get()
            ->getRow();
        
        if ($existing) {
            return true;
        }
        
        $data = array(
            'item_id' => $item_id,
            'material_id' => $material_id,
            'is_default' => $is_default,
            'created_at' => get_current_utc_time()
        );
        
        return $this->db->table('item_materials')->insert($data);
    }

    /**
     * Remove material from item
     */
    function remove_item_material($item_id, $material_id) {
        return $this->db->table('item_materials')
            ->where('item_id', $item_id)
            ->where('material_id', $material_id)
            ->delete();
    }

    /**
     * Get materials with pricing for item
     */
    function get_materials_with_pricing($item_id) {
        $item = $this->db->table('items')->where('id', $item_id)->get()->getRow();
        
        if (!$item) {
            return array();
        }
        
        $materials = $this->get_item_materials($item_id);
        
        foreach ($materials as &$material) {
            $material->calculated_price = $this->calculate_material_price($item->rate, $material->id);
            $material->price_difference = $material->calculated_price - $item->rate;
        }
        
        return $materials;
    }
}