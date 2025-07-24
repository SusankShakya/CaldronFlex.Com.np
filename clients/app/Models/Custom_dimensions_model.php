<?php

namespace App\Models;

use App\Models\Crud_model;

class Custom_dimensions_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'custom_dimensions';
        parent::__construct($this->table);
    }

    /**
     * Get custom dimensions for an item
     */
    function get_item_dimensions($item_id, $order_id = null, $estimate_id = null) {
        $where = array("item_id" => $item_id, "deleted" => 0);
        
        if ($order_id) {
            $where["order_id"] = $order_id;
        }
        
        if ($estimate_id) {
            $where["estimate_id"] = $estimate_id;
        }
        
        return $this->get_all_where($where)->getResult();
    }

    /**
     * Calculate area based on dimensions
     */
    function calculate_area($width, $height, $unit = 'ft') {
        $area = $width * $height;
        
        // Convert to square feet if needed
        if ($unit == 'in') {
            $area = $area / 144; // Convert square inches to square feet
        } else if ($unit == 'm') {
            $area = $area * 10.764; // Convert square meters to square feet
        } else if ($unit == 'cm') {
            $area = $area / 929.03; // Convert square cm to square feet
        }
        
        return round($area, 2);
    }

    /**
     * Validate dimensions against min/max constraints
     */
    function validate_dimensions($item_id, $width, $height, $unit = 'ft') {
        $errors = array();
        
        // Get item dimension constraints
        $item = $this->db->table('items')->where('id', $item_id)->get()->getRow();
        
        if (!$item) {
            $errors[] = "Invalid item";
            return $errors;
        }
        
        // Convert dimensions to feet for validation
        $width_ft = $this->convert_to_feet($width, $unit);
        $height_ft = $this->convert_to_feet($height, $unit);
        
        // Check against constraints if they exist
        $constraints = $this->get_dimension_constraints($item_id);
        
        if ($constraints) {
            if ($constraints->min_width && $width_ft < $constraints->min_width) {
                $errors[] = "Width must be at least " . $constraints->min_width . " ft";
            }
            
            if ($constraints->max_width && $width_ft > $constraints->max_width) {
                $errors[] = "Width cannot exceed " . $constraints->max_width . " ft";
            }
            
            if ($constraints->min_height && $height_ft < $constraints->min_height) {
                $errors[] = "Height must be at least " . $constraints->min_height . " ft";
            }
            
            if ($constraints->max_height && $height_ft > $constraints->max_height) {
                $errors[] = "Height cannot exceed " . $constraints->max_height . " ft";
            }
            
            // Check minimum area
            $area = $width_ft * $height_ft;
            if ($constraints->min_area && $area < $constraints->min_area) {
                $errors[] = "Minimum area is " . $constraints->min_area . " sq ft";
            }
        }
        
        return $errors;
    }

    /**
     * Get dimension constraints for an item
     */
    function get_dimension_constraints($item_id) {
        return $this->db->table('item_dimension_constraints')
            ->where('item_id', $item_id)
            ->where('deleted', 0)
            ->get()
            ->getRow();
    }

    /**
     * Convert dimension to feet
     */
    function convert_to_feet($value, $unit) {
        switch ($unit) {
            case 'in':
                return $value / 12;
            case 'm':
                return $value * 3.281;
            case 'cm':
                return $value / 30.48;
            default:
                return $value;
        }
    }

    /**
     * Calculate price based on dimensions
     */
    function calculate_dimension_price($item_id, $width, $height, $unit = 'ft') {
        $area = $this->calculate_area($width, $height, $unit);
        
        // Get item pricing
        $item = $this->db->table('items')->where('id', $item_id)->get()->getRow();
        
        if (!$item) {
            return 0;
        }
        
        // Get area-based pricing rules
        $pricing_rule = $this->db->table('pricing_rules')
            ->where('item_id', $item_id)
            ->where('rule_type', 'area_based')
            ->where('is_active', 1)
            ->where('deleted', 0)
            ->get()
            ->getRow();
        
        if ($pricing_rule) {
            $rule_data = json_decode($pricing_rule->rule_data, true);
            $price_per_sqft = isset($rule_data['price_per_sqft']) ? $rule_data['price_per_sqft'] : $item->rate;
            return $area * $price_per_sqft;
        }
        
        // Default to item rate * area
        return $area * $item->rate;
    }

    /**
     * Get dimension history for reporting
     */
    function get_dimension_history($filters = array()) {
        $where = array("deleted" => 0);
        
        if (isset($filters['start_date'])) {
            $where["created_at >="] = $filters['start_date'];
        }
        
        if (isset($filters['end_date'])) {
            $where["created_at <="] = $filters['end_date'];
        }
        
        if (isset($filters['item_id'])) {
            $where["item_id"] = $filters['item_id'];
        }
        
        return $this->get_all_where($where)->getResult();
    }
}