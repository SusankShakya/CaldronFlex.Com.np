<?php

namespace App\Services;

use App\Models\Materials_model;
use App\Models\Finishes_model;
use App\Models\Items_model;
use App\Models\Inventory_model;

class Materials_validation_service {

    private $materials_model;
    private $finishes_model;
    private $items_model;
    private $inventory_model;
    
    // Define validation constants
    const MIN_PRICE_MODIFIER = -100; // Maximum 100% discount
    const MAX_PRICE_MODIFIER = 1000; // Maximum 1000% markup
    const MIN_STOCK_LEVEL = 0;
    const MAX_STOCK_LEVEL = 999999999;

    function __construct() {
        $this->materials_model = new Materials_model();
        $this->finishes_model = new Finishes_model();
        $this->items_model = new Items_model();
        $this->inventory_model = new Inventory_model();
    }

    /**
     * Validate material data
     * 
     * @param array $data Material data to validate
     * @return array Validation result with success status and messages
     */
    public function validate_material($data) {
        $errors = array();
        
        // Extract material data
        $material_id = get_array_value($data, 'id');
        $name = get_array_value($data, 'name');
        $code = get_array_value($data, 'code');
        $status = get_array_value($data, 'status');
        $price_modifier = get_array_value($data, 'price_modifier');
        $price_modifier_type = get_array_value($data, 'price_modifier_type', 'fixed');
        $minimum_stock = get_array_value($data, 'minimum_stock', 0);
        $properties = get_array_value($data, 'properties', array());
        
        // Validate required fields
        if (empty($name)) {
            $errors[] = app_lang('material_name_required');
        }
        
        if (empty($code)) {
            $errors[] = app_lang('material_code_required');
        } else {
            // Check for duplicate code
            if ($this->is_duplicate_code($code, $material_id)) {
                $errors[] = app_lang('material_code_already_exists');
            }
        }
        
        // Validate status
        if (!$this->validate_status($status)) {
            $errors[] = app_lang('invalid_material_status');
        }
        
        // Validate price modifier
        if ($price_modifier !== null && $price_modifier !== '') {
            $price_validation = $this->validate_price_modifier($price_modifier, $price_modifier_type);
            if (!$price_validation['success']) {
                $errors = array_merge($errors, $price_validation['errors']);
            }
        }
        
        // Validate minimum stock
        if (!is_numeric($minimum_stock) || $minimum_stock < 0) {
            $errors[] = app_lang('minimum_stock_must_be_positive');
        }
        
        // Validate material properties
        if (!empty($properties)) {
            $property_validation = $this->validate_material_properties($properties);
            if (!$property_validation['success']) {
                $errors = array_merge($errors, $property_validation['errors']);
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? app_lang('material_valid') : implode('<br/>', $errors)
        );
    }

    /**
     * Validate material availability and status
     * 
     * @param int $material_id Material ID
     * @param array $options Additional options (quantity, warehouse_id, etc.)
     * @return array Validation result
     */
    public function validate_availability($material_id, $options = array()) {
        $material = $this->materials_model->get_one($material_id);
        
        if (!$material) {
            return array(
                'success' => false,
                'error' => app_lang('material_not_found')
            );
        }
        
        // Check material status
        if ($material->status !== 'active') {
            return array(
                'success' => false,
                'error' => app_lang('material_not_active'),
                'status' => $material->status
            );
        }
        
        // Check stock availability if quantity is specified
        $quantity = get_array_value($options, 'quantity');
        $warehouse_id = get_array_value($options, 'warehouse_id');
        
        if ($quantity) {
            $stock_validation = $this->validate_stock_availability($material_id, $quantity, $warehouse_id);
            if (!$stock_validation['success']) {
                return $stock_validation;
            }
        }
        
        return array(
            'success' => true,
            'material' => $material,
            'available' => true
        );
    }

    /**
     * Validate price modifier
     * 
     * @param float $modifier Price modifier value
     * @param string $type Modifier type (percentage or fixed)
     * @return array Validation result
     */
    public function validate_price_modifier($modifier, $type = 'fixed') {
        $errors = array();
        
        if (!is_numeric($modifier)) {
            $errors[] = app_lang('price_modifier_must_be_numeric');
            return array('success' => false, 'errors' => $errors);
        }
        
        $modifier = floatval($modifier);
        
        if ($type === 'percentage') {
            if ($modifier < self::MIN_PRICE_MODIFIER || $modifier > self::MAX_PRICE_MODIFIER) {
                $errors[] = sprintf(app_lang('price_modifier_percentage_out_of_range'), 
                    self::MIN_PRICE_MODIFIER, self::MAX_PRICE_MODIFIER);
            }
        } else {
            // For fixed amount, just check if it's not negative beyond reason
            if ($modifier < -999999999) {
                $errors[] = app_lang('price_modifier_too_negative');
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'validated_modifier' => $modifier,
            'type' => $type
        );
    }

    /**
     * Validate material compatibility with finishes
     * 
     * @param int $material_id Material ID
     * @param array $finish_ids Array of finish IDs
     * @return array Validation result
     */
    public function validate_finish_compatibility($material_id, $finish_ids) {
        $material = $this->materials_model->get_one($material_id);
        
        if (!$material) {
            return array(
                'success' => false,
                'error' => app_lang('material_not_found')
            );
        }
        
        $incompatible_finishes = array();
        $compatible_finishes = array();
        
        // Get material compatibility rules
        $compatibility_rules = json_decode($material->finish_compatibility, true) ?: array();
        
        foreach ($finish_ids as $finish_id) {
            $finish = $this->finishes_model->get_one($finish_id);
            
            if (!$finish) {
                $incompatible_finishes[] = array(
                    'id' => $finish_id,
                    'reason' => app_lang('finish_not_found')
                );
                continue;
            }
            
            // Check if finish is explicitly incompatible
            if (isset($compatibility_rules['incompatible']) && 
                in_array($finish_id, $compatibility_rules['incompatible'])) {
                $incompatible_finishes[] = array(
                    'id' => $finish_id,
                    'name' => $finish->name,
                    'reason' => app_lang('finish_incompatible_with_material')
                );
                continue;
            }
            
            // Check if finish is in the compatible list (if specified)
            if (isset($compatibility_rules['compatible']) && 
                !empty($compatibility_rules['compatible']) &&
                !in_array($finish_id, $compatibility_rules['compatible'])) {
                $incompatible_finishes[] = array(
                    'id' => $finish_id,
                    'name' => $finish->name,
                    'reason' => app_lang('finish_not_in_compatible_list')
                );
                continue;
            }
            
            // Check finish properties compatibility
            $property_check = $this->check_property_compatibility($material, $finish);
            if (!$property_check['compatible']) {
                $incompatible_finishes[] = array(
                    'id' => $finish_id,
                    'name' => $finish->name,
                    'reason' => $property_check['reason']
                );
                continue;
            }
            
            $compatible_finishes[] = array(
                'id' => $finish_id,
                'name' => $finish->name
            );
        }
        
        return array(
            'success' => empty($incompatible_finishes),
            'compatible' => $compatible_finishes,
            'incompatible' => $incompatible_finishes,
            'message' => empty($incompatible_finishes) ? 
                app_lang('all_finishes_compatible') : 
                app_lang('some_finishes_incompatible')
        );
    }

    /**
     * Validate stock availability
     * 
     * @param int $material_id Material ID
     * @param float $quantity Required quantity
     * @param int $warehouse_id Optional warehouse ID
     * @return array Validation result
     */
    public function validate_stock_availability($material_id, $quantity, $warehouse_id = null) {
        if (!is_numeric($quantity) || $quantity <= 0) {
            return array(
                'success' => false,
                'error' => app_lang('invalid_quantity')
            );
        }
        
        // Get current stock level
        if ($warehouse_id) {
            $stock = $this->inventory_model->get_material_stock($material_id, $warehouse_id);
        } else {
            $stock = $this->inventory_model->get_total_material_stock($material_id);
        }
        
        $available_quantity = $stock ? $stock->quantity : 0;
        
        if ($available_quantity < $quantity) {
            return array(
                'success' => false,
                'error' => app_lang('insufficient_stock'),
                'required' => $quantity,
                'available' => $available_quantity,
                'shortage' => $quantity - $available_quantity
            );
        }
        
        return array(
            'success' => true,
            'required' => $quantity,
            'available' => $available_quantity,
            'remaining_after' => $available_quantity - $quantity
        );
    }

    /**
     * Validate material properties
     * 
     * @param array $properties Material properties
     * @return array Validation result
     */
    public function validate_material_properties($properties) {
        $errors = array();
        
        // Define required properties
        $required_properties = array('density', 'durability', 'flexibility');
        
        foreach ($required_properties as $prop) {
            if (!isset($properties[$prop]) || empty($properties[$prop])) {
                $errors[] = sprintf(app_lang('property_required'), $prop);
            }
        }
        
        // Validate property values
        if (isset($properties['density'])) {
            if (!is_numeric($properties['density']) || $properties['density'] <= 0) {
                $errors[] = app_lang('density_must_be_positive');
            }
        }
        
        if (isset($properties['durability'])) {
            $valid_durability = array('low', 'medium', 'high', 'very_high');
            if (!in_array($properties['durability'], $valid_durability)) {
                $errors[] = app_lang('invalid_durability_value');
            }
        }
        
        if (isset($properties['flexibility'])) {
            $valid_flexibility = array('rigid', 'semi_flexible', 'flexible', 'very_flexible');
            if (!in_array($properties['flexibility'], $valid_flexibility)) {
                $errors[] = app_lang('invalid_flexibility_value');
            }
        }
        
        // Validate temperature resistance if provided
        if (isset($properties['temperature_resistance'])) {
            $temp = $properties['temperature_resistance'];
            if (!is_array($temp) || !isset($temp['min']) || !isset($temp['max'])) {
                $errors[] = app_lang('invalid_temperature_resistance_format');
            } elseif ($temp['min'] >= $temp['max']) {
                $errors[] = app_lang('min_temperature_must_be_less_than_max');
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'validated_properties' => $properties
        );
    }

    /**
     * Check if material code is duplicate
     */
    private function is_duplicate_code($code, $exclude_id = null) {
        $where = array('code' => $code, 'deleted' => 0);
        if ($exclude_id) {
            $where['id !='] = $exclude_id;
        }
        
        return $this->materials_model->get_one_where($where) ? true : false;
    }

    /**
     * Validate material status
     */
    private function validate_status($status) {
        $valid_statuses = array('active', 'inactive', 'discontinued', 'pending');
        return in_array($status, $valid_statuses);
    }

    /**
     * Check property compatibility between material and finish
     */
    private function check_property_compatibility($material, $finish) {
        $material_props = json_decode($material->properties, true) ?: array();
        $finish_props = json_decode($finish->properties, true) ?: array();
        
        // Example compatibility checks
        if (isset($material_props['flexibility']) && isset($finish_props['application_method'])) {
            if ($material_props['flexibility'] === 'rigid' && 
                $finish_props['application_method'] === 'spray') {
                return array(
                    'compatible' => false,
                    'reason' => app_lang('spray_finish_not_compatible_with_rigid_material')
                );
            }
        }
        
        // Check temperature compatibility
        if (isset($material_props['temperature_resistance']) && 
            isset($finish_props['curing_temperature'])) {
            $material_temp = $material_props['temperature_resistance'];
            $curing_temp = $finish_props['curing_temperature'];
            
            if ($curing_temp > $material_temp['max']) {
                return array(
                    'compatible' => false,
                    'reason' => app_lang('finish_curing_temperature_exceeds_material_limit')
                );
            }
        }
        
        return array('compatible' => true);
    }

    /**
     * Validate bulk material operations
     * 
     * @param array $materials Array of material data
     * @return array Validation results
     */
    public function validate_bulk_materials($materials) {
        $results = array();
        
        foreach ($materials as $index => $material) {
            $validation = $this->validate_material($material);
            $results[$index] = array(
                'material_id' => get_array_value($material, 'id'),
                'material_code' => get_array_value($material, 'code'),
                'success' => $validation['success'],
                'errors' => $validation['errors'],
                'message' => $validation['message']
            );
        }
        
        return array(
            'success' => !array_filter($results, function($r) { return !$r['success']; }),
            'results' => $results,
            'total' => count($materials),
            'valid' => count(array_filter($results, function($r) { return $r['success']; })),
            'invalid' => count(array_filter($results, function($r) { return !$r['success']; }))
        );
    }

    /**
     * Calculate price impact of material
     *
     * @param int $material_id Material ID
     * @param float $base_price Base price to apply modifier to
     * @return array Price calculation result
     */
    public function calculate_price_impact($material_id, $base_price) {
        $material = $this->materials_model->get_one($material_id);
        
        if (!$material) {
            return array(
                'success' => false,
                'error' => app_lang('material_not_found')
            );
        }
        
        if (!is_numeric($base_price) || $base_price < 0) {
            return array(
                'success' => false,
                'error' => app_lang('invalid_base_price')
            );
        }
        
        $price_modifier = $material->price_modifier ?: 0;
        $price_modifier_type = $material->price_modifier_type ?: 'fixed';
        
        if ($price_modifier_type === 'percentage') {
            $impact = $base_price * ($price_modifier / 100);
        } else {
            $impact = $price_modifier;
        }
        
        $final_price = $base_price + $impact;
        
        return array(
            'success' => true,
            'base_price' => $base_price,
            'modifier' => $price_modifier,
            'modifier_type' => $price_modifier_type,
            'impact' => $impact,
            'final_price' => $final_price,
            'percentage_change' => $base_price > 0 ? (($final_price - $base_price) / $base_price) * 100 : 0
        );
    }
}