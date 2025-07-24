<?php

namespace App\Libraries;

use App\Models\Pricing_rules_model;
use App\Models\Variants_model;
use App\Models\Items_model;

/**
 * Pricing Engine Library
 * Handles all pricing calculations for CaldronFlex
 */
class Pricing_engine {
    
    private $ci;
    private $cache_prefix = 'pricing_';
    private $cache_duration = 3600; // 1 hour cache duration
    private $pricing_rules_model;
    private $variants_model;
    private $items_model;
    
    public function __construct() {
        $this->ci = &get_instance();
        $this->pricing_rules_model = model("App\Models\Pricing_rules_model");
        $this->variants_model = model("App\Models\Variants_model");
        $this->items_model = model("App\Models\Items_model");
    }
    
    /**
     * Calculate area-based price for flex banners
     * 
     * @param float $width Width in feet
     * @param float $height Height in feet
     * @param float $base_rate Base rate per square foot
     * @param int $item_id Item ID for getting specific rates
     * @return array Price calculation details
     */
    public function calculate_area_based_price($width, $height, $base_rate, $item_id = null) {
        // Calculate area
        $area = $width * $height;
        
        // Get area-based pricing rules if item_id provided
        if ($item_id) {
            $area_rules = $this->pricing_rules_model->get_area_based_rules($item_id, $area);
            if ($area_rules && $area_rules->base_rate) {
                $base_rate = $area_rules->base_rate;
            }
        }
        
        // Calculate base price
        $base_price = $area * $base_rate;
        
        return array(
            'width' => $width,
            'height' => $height,
            'area' => round($area, 2),
            'rate_per_sqft' => $base_rate,
            'base_price' => round($base_price, 2),
            'calculation_method' => 'area_based'
        );
    }
    
    /**
     * Apply quantity-based discounts
     * 
     * @param float $base_price Base price before discounts
     * @param int $quantity Quantity ordered
     * @param int $item_id Item ID
     * @param int $customer_id Customer ID (optional)
     * @return array Discount calculation details
     */
    public function apply_quantity_discounts($base_price, $quantity, $item_id, $customer_id = null) {
        // Get applicable quantity discount rules
        $discount_rules = $this->pricing_rules_model->get_quantity_discount_rules($item_id, $quantity, $customer_id);
        
        $total_discount = 0;
        $applied_rules = array();
        
        if ($discount_rules) {
            foreach ($discount_rules as $rule) {
                $discount_amount = 0;
                
                if ($rule->discount_type === 'percentage') {
                    $discount_amount = $base_price * ($rule->discount_value / 100);
                } else {
                    // Fixed discount
                    $discount_amount = $rule->discount_value;
                }
                
                $total_discount += $discount_amount;
                $applied_rules[] = array(
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->rule_name,
                    'discount_type' => $rule->discount_type,
                    'discount_value' => $rule->discount_value,
                    'discount_amount' => round($discount_amount, 2)
                );
            }
        }
        
        return array(
            'original_price' => $base_price,
            'quantity' => $quantity,
            'total_discount' => round($total_discount, 2),
            'discounted_price' => round($base_price - $total_discount, 2),
            'applied_rules' => $applied_rules
        );
    }
    
    /**
     * Apply variant modifiers to price
     *
     * @param float $base_price Base price
     * @param array $variant_data Array of selected variant data or IDs
     * @return array Variant modifier details
     */
    public function apply_variant_modifiers($base_price, $variant_data) {
        $total_modifier = 0;
        $variant_details = array();
        
        if (!empty($variant_data)) {
            // Load variant combinations model
            $variant_combinations_model = model("App\Models\Variant_combinations_model");
            
            // Check if we have a combination of variants
            if (is_array($variant_data) && count($variant_data) > 1) {
                // Build combination string
                $combination_values = array();
                foreach ($variant_data as $variant_id => $option_value) {
                    if (is_string($option_value) && strpos($option_value, '_') !== false) {
                        $parts = explode('_', $option_value, 2);
                        if (count($parts) > 1) {
                            $combination_values[] = $parts[1];
                        }
                    }
                }
                
                if (!empty($combination_values)) {
                    sort($combination_values);
                    $combination_string = implode('-', $combination_values);
                    
                    // Look for matching combination
                    $combination = $variant_combinations_model->get_one_where(array(
                        'combination' => $combination_string,
                        'status' => 1,
                        'deleted' => 0
                    ));
                    
                    if ($combination && $combination->price) {
                        // Use combination price as modifier
                        $total_modifier = $combination->price - $base_price;
                        $variant_details[] = array(
                            'type' => 'combination',
                            'combination' => $combination_string,
                            'modifier_amount' => round($total_modifier, 2)
                        );
                        
                        return array(
                            'base_price' => $base_price,
                            'total_modifier' => round($total_modifier, 2),
                            'modified_price' => round($combination->price, 2),
                            'variant_details' => $variant_details
                        );
                    }
                }
            }
            
            // Process individual variants
            foreach ($variant_data as $variant_id => $option_value) {
                // Extract variant ID if needed
                if (is_string($variant_id) && !is_numeric($variant_id)) {
                    continue;
                }
                
                $variant = $this->variants_model->get_one($variant_id);
                if ($variant) {
                    $modifier_amount = 0;
                    
                    if ($variant->price_modifier_type === 'percentage' && $variant->price_modifier_value) {
                        $modifier_amount = $base_price * ($variant->price_modifier_value / 100);
                    } else if ($variant->price_modifier_type === 'fixed' && $variant->price_modifier_value) {
                        // Fixed modifier
                        $modifier_amount = $variant->price_modifier_value;
                    }
                    
                    $total_modifier += $modifier_amount;
                    $variant_details[] = array(
                        'variant_id' => $variant->id,
                        'variant_type' => $variant->variant_type,
                        'variant_name' => $variant->variant_name,
                        'modifier_type' => $variant->price_modifier_type,
                        'modifier_value' => $variant->price_modifier_value,
                        'modifier_amount' => round($modifier_amount, 2)
                    );
                }
            }
        }
        
        return array(
            'base_price' => $base_price,
            'total_modifier' => round($total_modifier, 2),
            'modified_price' => round($base_price + $total_modifier, 2),
            'variant_details' => $variant_details
        );
    }
    
    /**
     * Get customer-specific pricing
     * 
     * @param int $item_id Item ID
     * @param int $customer_id Customer ID
     * @param float $base_price Base price
     * @return array Customer pricing details
     */
    public function get_customer_specific_pricing($item_id, $customer_id, $base_price) {
        // Check cache first
        $cache_key = $this->cache_prefix . "customer_{$customer_id}_item_{$item_id}";
        $cached_data = cache($cache_key);
        
        if ($cached_data !== null) {
            return $cached_data;
        }
        
        // Get customer-specific pricing rules
        $customer_rules = $this->pricing_rules_model->get_customer_specific_rules($item_id, $customer_id);
        
        $final_price = $base_price;
        $discount_amount = 0;
        $has_special_pricing = false;
        
        if ($customer_rules) {
            $has_special_pricing = true;
            
            if ($customer_rules->base_rate) {
                // Customer has special base rate
                $final_price = $customer_rules->base_rate;
            } else if ($customer_rules->discount_type && $customer_rules->discount_value > 0) {
                // Apply customer discount
                if ($customer_rules->discount_type === 'percentage') {
                    $discount_amount = $base_price * ($customer_rules->discount_value / 100);
                } else {
                    $discount_amount = $customer_rules->discount_value;
                }
                $final_price = $base_price - $discount_amount;
            }
        }
        
        $result = array(
            'customer_id' => $customer_id,
            'has_special_pricing' => $has_special_pricing,
            'original_price' => $base_price,
            'discount_amount' => round($discount_amount, 2),
            'final_price' => round($final_price, 2)
        );
        
        // Cache the result
        cache()->save($cache_key, $result, $this->cache_duration);
        
        return $result;
    }
    
    /**
     * Calculate complete price for an item
     * 
     * @param array $params Calculation parameters
     * @return array Complete price calculation
     */
    public function calculate_complete_price($params) {
        $item_id = get_array_value($params, 'item_id');
        $quantity = get_array_value($params, 'quantity', 1);
        $width = get_array_value($params, 'width');
        $height = get_array_value($params, 'height');
        $variant_ids = get_array_value($params, 'variant_ids', array());
        $customer_id = get_array_value($params, 'customer_id');
        
        // Get item details
        $item = $this->items_model->get_one($item_id);
        if (!$item) {
            return array('error' => 'Item not found');
        }
        
        $calculation_steps = array();
        $base_price = 0;
        
        // Step 1: Calculate base price
        if ($item->pricing_type === 'area_based' && $width && $height) {
            $area_calc = $this->calculate_area_based_price($width, $height, $item->rate, $item_id);
            $base_price = $area_calc['base_price'];
            $calculation_steps['area_calculation'] = $area_calc;
        } else {
            // Fixed pricing
            $base_price = $item->rate;
            $calculation_steps['base_price'] = $base_price;
        }
        
        // Step 2: Apply variant modifiers
        if (!empty($variant_ids)) {
            $variant_calc = $this->apply_variant_modifiers($base_price, $variant_ids);
            $base_price = $variant_calc['modified_price'];
            $calculation_steps['variant_modifiers'] = $variant_calc;
        }
        
        // Step 3: Apply quantity discounts
        $quantity_calc = $this->apply_quantity_discounts($base_price, $quantity, $item_id, $customer_id);
        $unit_price = $quantity_calc['discounted_price'];
        $calculation_steps['quantity_discounts'] = $quantity_calc;
        
        // Step 4: Apply customer-specific pricing
        if ($customer_id) {
            $customer_calc = $this->get_customer_specific_pricing($item_id, $customer_id, $unit_price);
            if ($customer_calc['has_special_pricing']) {
                $unit_price = $customer_calc['final_price'];
                $calculation_steps['customer_pricing'] = $customer_calc;
            }
        }
        
        // Calculate total
        $total_price = $unit_price * $quantity;
        
        // Generate cache key for this calculation
        $cache_key = $this->generate_cache_key($params);
        
        $result = array(
            'item_id' => $item_id,
            'item_title' => $item->title,
            'quantity' => $quantity,
            'unit_price' => round($unit_price, 2),
            'total_price' => round($total_price, 2),
            'currency' => get_setting('default_currency'),
            'calculation_steps' => $calculation_steps,
            'cache_key' => $cache_key,
            'calculated_at' => get_current_utc_time()
        );
        
        // Log the calculation
        $this->log_calculation($result, $params);
        
        return $result;
    }
    
    /**
     * Generate cache key for pricing calculation
     * 
     * @param array $params Calculation parameters
     * @return string Cache key
     */
    private function generate_cache_key($params) {
        $key_parts = array(
            $this->cache_prefix,
            'calc',
            get_array_value($params, 'item_id'),
            get_array_value($params, 'quantity', 1),
            get_array_value($params, 'width', 0),
            get_array_value($params, 'height', 0),
            implode('_', get_array_value($params, 'variant_ids', array())),
            get_array_value($params, 'customer_id', 0)
        );
        
        return implode('_', array_filter($key_parts));
    }
    
    /**
     * Log pricing calculation for audit
     * 
     * @param array $result Calculation result
     * @param array $params Original parameters
     */
    private function log_calculation($result, $params) {
        $log_data = array(
            'item_id' => get_array_value($params, 'item_id'),
            'client_id' => get_array_value($params, 'customer_id'),
            'calculation_type' => 'real_time',
            'input_parameters' => json_encode($params),
            'base_price' => get_array_value($result['calculation_steps'], 'base_price', 0),
            'final_price' => $result['total_price'],
            'calculation_details' => json_encode($result),
            'cache_key' => $result['cache_key'],
            'calculated_at' => get_current_utc_time(),
            'calculated_by' => $this->ci->login_user->id ?? null,
            'ip_address' => $this->ci->input->ip_address()
        );
        
        // Save to pricing calculations log
        $this->ci->db->table('pricing_calculations_log')->insert($log_data);
    }
    
    /**
     * Clear pricing cache for an item
     *
     * @param int $item_id Item ID
     */
    public function clear_item_cache($item_id) {
        // Clear all cache entries related to this item
        cache()->deleteMatching($this->cache_prefix . "*_item_{$item_id}_*");
    }
    
    /**
     * Clear pricing cache for a customer
     *
     * @param int $customer_id Customer ID
     */
    public function clear_customer_cache($customer_id) {
        // Clear all cache entries related to this customer
        cache()->deleteMatching($this->cache_prefix . "*customer_{$customer_id}_*");
    }
}