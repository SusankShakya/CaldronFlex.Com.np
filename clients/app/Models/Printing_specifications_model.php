<?php

namespace App\Models;

use App\Models\Crud_model;

class Printing_specifications_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'printing_specifications';
        parent::__construct($this->table);
    }

    /**
     * Get specifications for an order or estimate
     */
    function get_specifications($order_id = null, $estimate_id = null, $item_id = null) {
        $where = array("deleted" => 0);
        
        if ($order_id) {
            $where["order_id"] = $order_id;
        }
        
        if ($estimate_id) {
            $where["estimate_id"] = $estimate_id;
        }
        
        if ($item_id) {
            $where["item_id"] = $item_id;
        }
        
        return $this->get_all_where($where)->getResult();
    }

    /**
     * Get specification by order and item
     */
    function get_order_item_specification($order_id, $item_id) {
        return $this->get_one_where(array(
            "order_id" => $order_id,
            "item_id" => $item_id,
            "deleted" => 0
        ));
    }

    /**
     * Get specification by estimate and item
     */
    function get_estimate_item_specification($estimate_id, $item_id) {
        return $this->get_one_where(array(
            "estimate_id" => $estimate_id,
            "item_id" => $item_id,
            "deleted" => 0
        ));
    }

    /**
     * Copy specifications from estimate to order
     */
    function copy_from_estimate_to_order($estimate_id, $order_id) {
        $specifications = $this->get_specifications(null, $estimate_id);
        
        foreach ($specifications as $spec) {
            $data = (array) $spec;
            unset($data['id']);
            unset($data['estimate_id']);
            $data['order_id'] = $order_id;
            $data['created_at'] = get_current_utc_time();
            $data['updated_at'] = null;
            
            $this->ci->db->table($this->table)->insert($data);
        }
        
        return true;
    }

    /**
     * Validate print specifications
     */
    function validate_specifications($data) {
        $errors = array();
        
        // Validate resolution
        if (isset($data['resolution_dpi']) && $data['resolution_dpi'] < 72) {
            $errors[] = "Resolution must be at least 72 DPI";
        }
        
        // Validate bleed size
        if (isset($data['bleed_size']) && $data['bleed_size'] < 0) {
            $errors[] = "Bleed size cannot be negative";
        }
        
        // Validate safe zone
        if (isset($data['safe_zone']) && $data['safe_zone'] < 0) {
            $errors[] = "Safe zone cannot be negative";
        }
        
        // Validate turnaround time
        if (isset($data['turnaround_time']) && $data['turnaround_time'] < 1) {
            $errors[] = "Turnaround time must be at least 1 hour";
        }
        
        // Validate Pantone colors format
        if (isset($data['pantone_colors']) && !empty($data['pantone_colors'])) {
            $pantone_array = json_decode($data['pantone_colors'], true);
            if (!is_array($pantone_array)) {
                $errors[] = "Pantone colors must be a valid JSON array";
            }
        }
        
        return $errors;
    }

    /**
     * Get print methods
     */
    function get_print_methods() {
        return array(
            'digital' => 'Digital Printing',
            'offset' => 'Offset Printing',
            'screen' => 'Screen Printing',
            'large_format' => 'Large Format Printing',
            'vinyl' => 'Vinyl Printing',
            'sublimation' => 'Sublimation Printing',
            'uv' => 'UV Printing'
        );
    }

    /**
     * Get color modes
     */
    function get_color_modes() {
        return array(
            'CMYK' => 'CMYK (Full Color)',
            'RGB' => 'RGB',
            'Pantone' => 'Pantone (Spot Colors)',
            'Grayscale' => 'Grayscale'
        );
    }

    /**
     * Get proof types
     */
    function get_proof_types() {
        return array(
            'digital' => 'Digital Proof',
            'physical' => 'Physical Proof',
            'both' => 'Both Digital and Physical'
        );
    }

    /**
     * Calculate additional cost for specifications
     */
    function calculate_specification_cost($spec_id) {
        $spec = $this->get_one($spec_id);
        
        if (!$spec) {
            return 0;
        }
        
        $additional_cost = 0;
        
        // Rush order surcharge
        if ($spec->rush_order) {
            $additional_cost += 50; // Base rush charge
            
            // Additional charge based on turnaround time
            if ($spec->turnaround_time <= 24) {
                $additional_cost += 100; // Same day/next day
            } elseif ($spec->turnaround_time <= 48) {
                $additional_cost += 50; // 2 days
            }
        }
        
        // Proof charges
        if ($spec->proof_type == 'physical') {
            $additional_cost += 25;
        } elseif ($spec->proof_type == 'both') {
            $additional_cost += 35;
        }
        
        // High resolution charge
        if ($spec->resolution_dpi > 600) {
            $additional_cost += 20;
        }
        
        // Pantone color charges
        if ($spec->pantone_colors) {
            $pantone_array = json_decode($spec->pantone_colors, true);
            if (is_array($pantone_array)) {
                $additional_cost += count($pantone_array) * 15; // Per color charge
            }
        }
        
        return $additional_cost;
    }

    /**
     * Get specifications summary
     */
    function get_specifications_summary($spec_id) {
        $spec = $this->get_one($spec_id);
        
        if (!$spec) {
            return "";
        }
        
        $summary = array();
        
        // Print method
        $print_methods = $this->get_print_methods();
        if (isset($print_methods[$spec->print_method])) {
            $summary[] = $print_methods[$spec->print_method];
        }
        
        // Color mode and resolution
        $summary[] = $spec->color_mode . " @ " . $spec->resolution_dpi . " DPI";
        
        // Bleed and safe zone
        if ($spec->bleed_size > 0) {
            $summary[] = "Bleed: " . $spec->bleed_size . '"';
        }
        
        // Rush order
        if ($spec->rush_order) {
            $summary[] = "RUSH ORDER (" . $spec->turnaround_time . " hours)";
        }
        
        // Proof type
        $proof_types = $this->get_proof_types();
        if (isset($proof_types[$spec->proof_type])) {
            $summary[] = $proof_types[$spec->proof_type];
        }
        
        return implode(" | ", $summary);
    }
}