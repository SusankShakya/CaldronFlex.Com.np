<?php

namespace App\Services;

use App\Libraries\Pricing_engine;
use App\Models\Items_model;
use App\Models\Pricing_rules_model;
use App\Models\Custom_quotes_model;
use App\Models\Variants_model;
use App\Models\Variant_combinations_model;

class Price_calculator_service {

    private $pricing_engine;
    private $items_model;
    private $pricing_rules_model;
    private $custom_quotes_model;
    private $variants_model;
    private $variant_combinations_model;

    function __construct() {
        $this->pricing_engine = new Pricing_engine();
        $this->items_model = new Items_model();
        $this->pricing_rules_model = new Pricing_rules_model();
        $this->custom_quotes_model = new Custom_quotes_model();
        $this->variants_model = new Variants_model();
        $this->variant_combinations_model = new Variant_combinations_model();
    }

    /**
     * Calculate price with all modifiers and return detailed breakdown
     */
    public function calculate_price_with_breakdown($params) {
        $item_id = get_array_value($params, 'item_id');
        $quantity = get_array_value($params, 'quantity', 1);
        $dimensions = get_array_value($params, 'dimensions');
        $variant_combination_id = get_array_value($params, 'variant_combination_id');
        $customer_id = get_array_value($params, 'customer_id');
        
        // Validate input
        if (!$item_id) {
            return array(
                'success' => false,
                'error' => 'Item ID is required'
            );
        }

        // Get item details
        $item = $this->items_model->get_one($item_id);
        if (!$item) {
            return array(
                'success' => false,
                'error' => 'Item not found'
            );
        }

        // Initialize breakdown
        $breakdown = array(
            'base_price' => 0,
            'area_price' => 0,
            'variant_modifier' => 0,
            'quantity_discount' => 0,
            'customer_discount' => 0,
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'price_per_unit' => 0,
            'calculation_details' => array()
        );

        // Step 1: Calculate base price
        if ($item->pricing_type === 'area_based' && $dimensions) {
            $width = get_array_value($dimensions, 'width');
            $height = get_array_value($dimensions, 'height');
            
            $area_result = $this->pricing_engine->calculate_area_based_price(
                $width,
                $height,
                $item->rate,
                $item_id
            );
            
            $breakdown['base_price'] = $area_result['base_price'];
            $breakdown['area_price'] = $area_result['total_price'];
            $breakdown['calculation_details']['area'] = $area_result;
        } else {
            $breakdown['base_price'] = $item->rate * $quantity;
        }

        // Step 2: Apply variant modifiers
        if ($variant_combination_id) {
            $variant_result = $this->apply_variant_modifiers($variant_combination_id, $breakdown['base_price']);
            if ($variant_result['success']) {
                $breakdown['variant_modifier'] = $variant_result['modifier_amount'];
                $breakdown['calculation_details']['variants'] = $variant_result['details'];
            }
        }
        // Step 3: Apply quantity discounts
        $base_with_variants = $breakdown['base_price'] + $breakdown['variant_modifier'];
        $quantity_discount_result = $this->pricing_engine->apply_quantity_discounts(
            $base_with_variants,
            $quantity,
            $item_id,
            $customer_id
        );

        if ($quantity_discount_result['discount_amount'] > 0) {
            $breakdown['quantity_discount'] = -$quantity_discount_result['discount_amount'];
            $breakdown['calculation_details']['quantity_discount'] = $quantity_discount_result;
        }

        // Step 4: Apply customer-specific pricing
        if ($customer_id) {
            $price_after_qty_discount = ($breakdown['base_price'] + $breakdown['variant_modifier']) - abs($breakdown['quantity_discount']);
            $customer_pricing_result = $this->pricing_engine->get_customer_specific_pricing(
                $item_id,
                $customer_id,
                $price_after_qty_discount
            );
            
            if ($customer_pricing_result['has_special_pricing']) {
                $customer_discount = $price_after_qty_discount - $customer_pricing_result['final_price'];
                if ($customer_discount > 0) {
                    $breakdown['customer_discount'] = -$customer_discount;
                    $breakdown['calculation_details']['customer_discount'] = $customer_pricing_result;
                }
            }
        }

        // Calculate subtotal
        $breakdown['subtotal'] = $breakdown['base_price'] 
                               + $breakdown['variant_modifier'] 
                               + $breakdown['quantity_discount'] 
                               + $breakdown['customer_discount'];

        // Apply tax if configured
        $tax_rate = get_setting('default_tax_rate') ?: 0;
        if ($tax_rate > 0) {
            $breakdown['tax'] = $breakdown['subtotal'] * ($tax_rate / 100);
        }

        // Calculate total
        $breakdown['total'] = $breakdown['subtotal'] + $breakdown['tax'];
        $breakdown['price_per_unit'] = $quantity > 0 ? $breakdown['total'] / $quantity : 0;

        // Log the calculation
        $this->log_pricing_calculation(array(
            'item_id' => $item_id,
            'customer_id' => $customer_id,
            'quantity' => $quantity,
            'dimensions' => $dimensions,
            'variant_combination' => $variant_combination_id,
            'base_price' => $breakdown['base_price'],
            'final_price' => $breakdown['total'],
            'breakdown' => json_encode($breakdown)
        ));

        return array(
            'success' => true,
            'item' => $item,
            'breakdown' => $breakdown,
            'formatted_total' => to_currency($breakdown['total']),
            'formatted_per_unit' => to_currency($breakdown['price_per_unit'])
        );
    }

    /**
     * Apply variant modifiers to base price
     */
    private function apply_variant_modifiers($variant_combination_id, $base_price) {
        $combination = $this->variant_combinations_model->get_one($variant_combination_id);
        if (!$combination) {
            return array('success' => false, 'modifier_amount' => 0);
        }

        $total_modifier = 0;
        $details = array();

        // Apply combination-level price adjustment
        if ($combination->price_adjustment > 0) {
            if ($combination->price_adjustment_type === 'percentage') {
                $adjustment = $base_price * ($combination->price_adjustment / 100);
            } else {
                $adjustment = $combination->price_adjustment;
            }
            $total_modifier += $adjustment;
            $details[] = array(
                'type' => 'combination',
                'description' => 'Combination price adjustment',
                'amount' => $adjustment
            );
        }

        // Get individual variant modifiers
        $variant_values = json_decode($combination->variant_values, true);
        if ($variant_values) {
            foreach ($variant_values as $variant_id => $value_id) {
                $variant = $this->variants_model->get_one($variant_id);
                if ($variant && $variant->affect_price) {
                    // Find the specific value's price modifier
                    $values = json_decode($variant->values, true);
                    foreach ($values as $value) {
                        if ($value['id'] == $value_id && isset($value['price_modifier'])) {
                            $modifier = floatval($value['price_modifier']);
                            if ($variant->price_modifier_type === 'percentage') {
                                $adjustment = $base_price * ($modifier / 100);
                            } else {
                                $adjustment = $modifier;
                            }
                            $total_modifier += $adjustment;
                            $details[] = array(
                                'type' => 'variant',
                                'name' => $variant->name,
                                'value' => $value['name'],
                                'amount' => $adjustment
                            );
                        }
                    }
                }
            }
        }

        return array(
            'success' => true,
            'modifier_amount' => $total_modifier,
            'details' => $details
        );
    }

    /**
     * Get pricing rules for an item
     */
    public function get_item_pricing_rules($item_id) {
        $rules = array(
            'area_based' => array(),
            'quantity_tiers' => array(),
            'customer_specific' => array()
        );

        // Get area-based rules
        $area_rules = $this->pricing_rules_model->get_all_where(array(
            'item_id' => $item_id,
            'rule_type' => 'area_based',
            'status' => 1,
            'deleted' => 0
        ))->getResult();
        $rules['area_based'] = $area_rules;

        // Get quantity tier rules
        $quantity_rules = $this->pricing_rules_model->get_all_where(array(
            'item_id' => $item_id,
            'rule_type' => 'quantity_tier',
            'status' => 1,
            'deleted' => 0
        ))->getResult();
        $rules['quantity_tiers'] = $quantity_rules;

        return $rules;
    }

    /**
     * Create a custom quote
     */
    public function create_custom_quote($data) {
        // Calculate the price first
        $price_result = $this->calculate_price_with_breakdown(array(
            'item_id' => get_array_value($data, 'item_id'),
            'quantity' => get_array_value($data, 'quantity'),
            'dimensions' => get_array_value($data, 'dimensions'),
            'variant_combination_id' => get_array_value($data, 'variant_combination_id'),
            'customer_id' => get_array_value($data, 'client_id')
        ));

        if (!$price_result['success']) {
            return array('success' => false, 'error' => $price_result['error']);
        }

        // Prepare quote data
        $quote_data = array(
            'client_id' => get_array_value($data, 'client_id'),
            'item_id' => get_array_value($data, 'item_id'),
            'item_title' => $price_result['item']->title,
            'item_description' => get_array_value($data, 'item_description'),
            'quantity' => get_array_value($data, 'quantity'),
            'unit_price' => $price_result['breakdown']['price_per_unit'],
            'dimensions' => json_encode(get_array_value($data, 'dimensions')),
            'variant_combination' => get_array_value($data, 'variant_combination_id'),
            'pricing_breakdown' => json_encode($price_result['breakdown']),
            'discount_amount' => abs($price_result['breakdown']['quantity_discount'] + $price_result['breakdown']['customer_discount']),
            'total_amount' => $price_result['breakdown']['total'],
            'valid_until' => get_array_value($data, 'valid_until'),
            'notes' => get_array_value($data, 'notes'),
            'quote_status' => 'draft'
        );

        $quote_id = $this->custom_quotes_model->save_quote($quote_data);

        if ($quote_id) {
            return array(
                'success' => true,
                'quote_id' => $quote_id,
                'quote' => $this->custom_quotes_model->get_one($quote_id)
            );
        }

        return array('success' => false, 'error' => 'Failed to create quote');
    }

    /**
     * Log pricing calculation
     */
    private function log_pricing_calculation($data) {
        $log_data = array(
            'item_id' => get_array_value($data, 'item_id'),
            'client_id' => get_array_value($data, 'customer_id'),
            'calculation_type' => 'service',
            'input_parameters' => json_encode($data),
            'base_price' => get_array_value($data, 'base_price'),
            'final_price' => get_array_value($data, 'final_price'),
            'calculation_details' => get_array_value($data, 'breakdown'),
            'calculated_at' => get_current_utc_time(),
            'calculated_by' => get_array_value($this, 'login_user_id')
        );
        
        $db = \Config\Database::connect();
        $db->table('pricing_calculations_log')->insert($log_data);
    }

    /**
     * Validate pricing for an order
     */
    public function validate_order_pricing($order_items) {
        $validation_results = array();
        $total_amount = 0;

        foreach ($order_items as $item) {
            $price_result = $this->calculate_price_with_breakdown($item);
            
            if ($price_result['success']) {
                $validation_results[] = array(
                    'item_id' => $item['item_id'],
                    'valid' => true,
                    'calculated_price' => $price_result['breakdown']['total'],
                    'submitted_price' => get_array_value($item, 'total', 0),
                    'difference' => abs($price_result['breakdown']['total'] - get_array_value($item, 'total', 0))
                );
                $total_amount += $price_result['breakdown']['total'];
            } else {
                $validation_results[] = array(
                    'item_id' => $item['item_id'],
                    'valid' => false,
                    'error' => $price_result['error']
                );
            }
        }

        return array(
            'valid' => !array_filter($validation_results, function($r) { return !$r['valid']; }),
            'total_amount' => $total_amount,
            'items' => $validation_results
        );
    }
}