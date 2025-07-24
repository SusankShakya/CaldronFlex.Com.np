<?php

namespace App\Services;

use App\Models\Inventory_model;
use App\Models\Warehouses_model;
use App\Models\Items_model;
use App\Models\Materials_model;

class Inventory_validation_service {

    private $inventory_model;
    private $warehouses_model;
    private $items_model;
    private $materials_model;
    
    // Define validation constants
    const MIN_STOCK_LEVEL = 0;
    const MAX_STOCK_LEVEL = 999999999;
    const MIN_TRANSACTION_QUANTITY = 0.01;
    const MAX_TRANSACTION_QUANTITY = 999999;
    const VALID_TRANSACTION_TYPES = array('in', 'out', 'adjustment', 'transfer', 'return', 'damage');
    const VALID_ADJUSTMENT_TYPES = array('increase', 'decrease', 'set');

    function __construct() {
        $this->inventory_model = new Inventory_model();
        $this->warehouses_model = new Warehouses_model();
        $this->items_model = new Items_model();
        $this->materials_model = new Materials_model();
    }

    /**
     * Validate inventory operation
     * 
     * @param array $data Inventory operation data
     * @return array Validation result with success status and messages
     */
    public function validate_inventory_operation($data) {
        $errors = array();
        
        // Extract operation data
        $item_id = get_array_value($data, 'item_id');
        $material_id = get_array_value($data, 'material_id');
        $warehouse_id = get_array_value($data, 'warehouse_id');
        $quantity = get_array_value($data, 'quantity');
        $transaction_type = get_array_value($data, 'transaction_type');
        $minimum_stock = get_array_value($data, 'minimum_stock', 0);
        $maximum_stock = get_array_value($data, 'maximum_stock');
        
        // Validate item or material (at least one required)
        if (!$item_id && !$material_id) {
            $errors[] = app_lang('item_or_material_required');
        } elseif ($item_id && $material_id) {
            $errors[] = app_lang('cannot_specify_both_item_and_material');
        }
        
        // Validate item exists
        if ($item_id) {
            $item = $this->items_model->get_one($item_id);
            if (!$item) {
                $errors[] = app_lang('item_not_found');
            }
        }
        
        // Validate material exists
        if ($material_id) {
            $material = $this->materials_model->get_one($material_id);
            if (!$material) {
                $errors[] = app_lang('material_not_found');
            }
        }
        
        // Validate warehouse
        $warehouse_validation = $this->validate_warehouse($warehouse_id);
        if (!$warehouse_validation['success']) {
            $errors[] = $warehouse_validation['error'];
        }
        
        // Validate quantity
        $quantity_validation = $this->validate_quantity($quantity);
        if (!$quantity_validation['success']) {
            $errors = array_merge($errors, $quantity_validation['errors']);
        }
        
        // Validate transaction type
        if (!$this->validate_transaction_type($transaction_type)) {
            $errors[] = app_lang('invalid_transaction_type');
        }
        
        // Validate stock levels
        $stock_validation = $this->validate_stock_levels($minimum_stock, $maximum_stock);
        if (!$stock_validation['success']) {
            $errors = array_merge($errors, $stock_validation['errors']);
        }
        
        // Validate specific transaction requirements
        if ($transaction_type === 'out' || $transaction_type === 'transfer') {
            $availability_check = $this->validate_stock_availability(
                $item_id ?: $material_id, 
                $warehouse_id, 
                $quantity,
                $item_id ? 'item' : 'material'
            );
            if (!$availability_check['success']) {
                $errors[] = $availability_check['error'];
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? app_lang('inventory_operation_valid') : implode('<br/>', $errors)
        );
    }

    /**
     * Validate warehouse existence and status
     * 
     * @param int $warehouse_id Warehouse ID
     * @return array Validation result
     */
    public function validate_warehouse($warehouse_id) {
        if (!$warehouse_id) {
            return array(
                'success' => false,
                'error' => app_lang('warehouse_required')
            );
        }
        
        $warehouse = $this->warehouses_model->get_one($warehouse_id);
        
        if (!$warehouse) {
            return array(
                'success' => false,
                'error' => app_lang('warehouse_not_found')
            );
        }
        
        if ($warehouse->status !== 'active') {
            return array(
                'success' => false,
                'error' => app_lang('warehouse_not_active'),
                'warehouse_status' => $warehouse->status
            );
        }
        
        return array(
            'success' => true,
            'warehouse' => $warehouse
        );
    }

    /**
     * Validate quantity for transactions
     * 
     * @param float $quantity Transaction quantity
     * @return array Validation result
     */
    public function validate_quantity($quantity) {
        $errors = array();
        
        if ($quantity === null || $quantity === '') {
            $errors[] = app_lang('quantity_required');
        } elseif (!is_numeric($quantity)) {
            $errors[] = app_lang('quantity_must_be_numeric');
        } elseif ($quantity < self::MIN_TRANSACTION_QUANTITY) {
            $errors[] = sprintf(app_lang('quantity_too_small'), self::MIN_TRANSACTION_QUANTITY);
        } elseif ($quantity > self::MAX_TRANSACTION_QUANTITY) {
            $errors[] = sprintf(app_lang('quantity_too_large'), self::MAX_TRANSACTION_QUANTITY);
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'validated_quantity' => floatval($quantity)
        );
    }

    /**
     * Validate transaction type
     */
    private function validate_transaction_type($type) {
        return in_array($type, self::VALID_TRANSACTION_TYPES);
    }

    /**
     * Validate stock levels (min/max)
     * 
     * @param float $minimum_stock Minimum stock level
     * @param float $maximum_stock Maximum stock level
     * @return array Validation result
     */
    public function validate_stock_levels($minimum_stock, $maximum_stock) {
        $errors = array();
        
        // Validate minimum stock
        if ($minimum_stock !== null && $minimum_stock !== '') {
            if (!is_numeric($minimum_stock)) {
                $errors[] = app_lang('minimum_stock_must_be_numeric');
            } elseif ($minimum_stock < self::MIN_STOCK_LEVEL) {
                $errors[] = app_lang('minimum_stock_cannot_be_negative');
            } elseif ($minimum_stock > self::MAX_STOCK_LEVEL) {
                $errors[] = sprintf(app_lang('minimum_stock_too_large'), self::MAX_STOCK_LEVEL);
            }
        }
        
        // Validate maximum stock
        if ($maximum_stock !== null && $maximum_stock !== '') {
            if (!is_numeric($maximum_stock)) {
                $errors[] = app_lang('maximum_stock_must_be_numeric');
            } elseif ($maximum_stock < self::MIN_STOCK_LEVEL) {
                $errors[] = app_lang('maximum_stock_cannot_be_negative');
            } elseif ($maximum_stock > self::MAX_STOCK_LEVEL) {
                $errors[] = sprintf(app_lang('maximum_stock_too_large'), self::MAX_STOCK_LEVEL);
            }
        }
        
        // Validate min/max relationship
        if (is_numeric($minimum_stock) && is_numeric($maximum_stock) && 
            $minimum_stock > $maximum_stock) {
            $errors[] = app_lang('minimum_stock_cannot_exceed_maximum');
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors
        );
    }

    /**
     * Validate stock availability for outgoing transactions
     * 
     * @param int $product_id Item or Material ID
     * @param int $warehouse_id Warehouse ID
     * @param float $quantity Required quantity
     * @param string $type 'item' or 'material'
     * @return array Validation result
     */
    public function validate_stock_availability($product_id, $warehouse_id, $quantity, $type = 'item') {
        // Get current stock level
        $current_stock = $this->inventory_model->get_stock_level($product_id, $warehouse_id, $type);
        $available_quantity = $current_stock ? $current_stock->quantity : 0;
        
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
     * Validate stock transfer operation
     * 
     * @param array $data Transfer data
     * @return array Validation result
     */
    public function validate_stock_transfer($data) {
        $errors = array();
        
        $from_warehouse_id = get_array_value($data, 'from_warehouse_id');
        $to_warehouse_id = get_array_value($data, 'to_warehouse_id');
        $product_id = get_array_value($data, 'item_id') ?: get_array_value($data, 'material_id');
        $quantity = get_array_value($data, 'quantity');
        $type = get_array_value($data, 'item_id') ? 'item' : 'material';
        
        // Validate source warehouse
        $from_validation = $this->validate_warehouse($from_warehouse_id);
        if (!$from_validation['success']) {
            $errors[] = 'Source warehouse: ' . $from_validation['error'];
        }
        
        // Validate destination warehouse
        $to_validation = $this->validate_warehouse($to_warehouse_id);
        if (!$to_validation['success']) {
            $errors[] = 'Destination warehouse: ' . $to_validation['error'];
        }
        
        // Validate warehouses are different
        if ($from_warehouse_id == $to_warehouse_id) {
            $errors[] = app_lang('source_and_destination_must_be_different');
        }
        
        // Validate quantity
        $quantity_validation = $this->validate_quantity($quantity);
        if (!$quantity_validation['success']) {
            $errors = array_merge($errors, $quantity_validation['errors']);
        }
        
        // Validate stock availability at source
        if (empty($errors)) {
            $availability_check = $this->validate_stock_availability(
                $product_id, 
                $from_warehouse_id, 
                $quantity,
                $type
            );
            if (!$availability_check['success']) {
                $errors[] = $availability_check['error'];
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? app_lang('transfer_valid') : implode('<br/>', $errors)
        );
    }

    /**
     * Validate stock adjustment
     * 
     * @param array $data Adjustment data
     * @return array Validation result
     */
    public function validate_stock_adjustment($data) {
        $errors = array();
        
        $adjustment_type = get_array_value($data, 'adjustment_type');
        $quantity = get_array_value($data, 'quantity');
        $reason = get_array_value($data, 'reason');
        $product_id = get_array_value($data, 'item_id') ?: get_array_value($data, 'material_id');
        $warehouse_id = get_array_value($data, 'warehouse_id');
        $type = get_array_value($data, 'item_id') ? 'item' : 'material';
        
        // Validate adjustment type
        if (!in_array($adjustment_type, self::VALID_ADJUSTMENT_TYPES)) {
            $errors[] = app_lang('invalid_adjustment_type');
        }
        
        // Validate quantity
        $quantity_validation = $this->validate_quantity($quantity);
        if (!$quantity_validation['success']) {
            $errors = array_merge($errors, $quantity_validation['errors']);
        }
        
        // Validate reason
        if (empty($reason)) {
            $errors[] = app_lang('adjustment_reason_required');
        } elseif (strlen($reason) < 10) {
            $errors[] = app_lang('adjustment_reason_too_short');
        }
        
        // For decrease adjustments, check stock availability
        if ($adjustment_type === 'decrease' && empty($errors)) {
            $availability_check = $this->validate_stock_availability(
                $product_id, 
                $warehouse_id, 
                $quantity,
                $type
            );
            if (!$availability_check['success']) {
                $errors[] = $availability_check['error'];
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? app_lang('adjustment_valid') : implode('<br/>', $errors)
        );
    }

    /**
     * Validate low stock threshold
     * 
     * @param int $product_id Item or Material ID
     * @param int $warehouse_id Warehouse ID
     * @param string $type 'item' or 'material'
     * @return array Validation result with warnings
     */
    public function validate_low_stock_threshold($product_id, $warehouse_id, $type = 'item') {
        $warnings = array();
        
        // Get current stock and thresholds
        $inventory = $this->inventory_model->get_inventory_info($product_id, $warehouse_id, $type);
        
        if (!$inventory) {
            return array(
                'success' => true,
                'warnings' => array(),
                'stock_status' => 'unknown'
            );
        }
        
        $current_stock = $inventory->quantity;
        $minimum_stock = $inventory->minimum_stock ?: 0;
        $reorder_level = $inventory->reorder_level ?: $minimum_stock * 1.5;
        
        // Check stock levels
        if ($current_stock <= 0) {
            $warnings[] = app_lang('out_of_stock');
            $stock_status = 'out_of_stock';
        } elseif ($current_stock < $minimum_stock) {
            $warnings[] = sprintf(app_lang('stock_below_minimum'), $current_stock, $minimum_stock);
            $stock_status = 'critical';
        } elseif ($current_stock < $reorder_level) {
            $warnings[] = sprintf(app_lang('stock_at_reorder_level'), $current_stock, $reorder_level);
            $stock_status = 'low';
        } else {
            $stock_status = 'normal';
        }
        
        return array(
            'success' => true,
            'warnings' => $warnings,
            'stock_status' => $stock_status,
            'current_stock' => $current_stock,
            'minimum_stock' => $minimum_stock,
            'reorder_level' => $reorder_level
        );
    }

    /**
     * Validate multi-warehouse stock
     *
     * @param int $product_id Item or Material ID
     * @param string $type 'item' or 'material'
     * @return array Validation result with warehouse stock details
     */
    public function validate_multi_warehouse_stock($product_id, $type = 'item') {
        $warehouses = $this->warehouses_model->get_all_where(array('status' => 'active', 'deleted' => 0))->getResult();
        $stock_details = array();
        $total_stock = 0;
        $warnings = array();
        
        foreach ($warehouses as $warehouse) {
            $stock = $this->inventory_model->get_stock_level($product_id, $warehouse->id, $type);
            $quantity = $stock ? $stock->quantity : 0;
            $minimum = $stock ? $stock->minimum_stock : 0;
            
            $stock_details[] = array(
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'quantity' => $quantity,
                'minimum_stock' => $minimum,
                'status' => $quantity <= 0 ? 'out_of_stock' : ($quantity < $minimum ? 'low' : 'normal')
            );
            
            $total_stock += $quantity;
            
            if ($quantity <= 0) {
                $warnings[] = sprintf(app_lang('out_of_stock_at_warehouse'), $warehouse->name);
            } elseif ($quantity < $minimum) {
                $warnings[] = sprintf(app_lang('low_stock_at_warehouse'), $warehouse->name, $quantity, $minimum);
            }
        }
        
        return array(
            'success' => true,
            'total_stock' => $total_stock,
            'warehouse_details' => $stock_details,
            'warnings' => $warnings,
            'warehouses_with_stock' => count(array_filter($stock_details, function($w) { return $w['quantity'] > 0; })),
            'warehouses_out_of_stock' => count(array_filter($stock_details, function($w) { return $w['quantity'] <= 0; }))
        );
    }

    /**
     * Validate bulk inventory operations
     *
     * @param array $operations Array of inventory operations
     * @return array Validation results
     */
    public function validate_bulk_operations($operations) {
        $results = array();
        $warehouse_impacts = array(); // Track cumulative impact per warehouse
        
        foreach ($operations as $index => $operation) {
            // Validate individual operation
            $validation = $this->validate_inventory_operation($operation);
            
            // Check cumulative impact
            if ($validation['success']) {
                $warehouse_id = get_array_value($operation, 'warehouse_id');
                $product_id = get_array_value($operation, 'item_id') ?: get_array_value($operation, 'material_id');
                $quantity = get_array_value($operation, 'quantity');
                $transaction_type = get_array_value($operation, 'transaction_type');
                $type = get_array_value($operation, 'item_id') ? 'item' : 'material';
                
                // Track cumulative changes
                $key = $warehouse_id . '_' . $product_id . '_' . $type;
                if (!isset($warehouse_impacts[$key])) {
                    $current_stock = $this->inventory_model->get_stock_level($product_id, $warehouse_id, $type);
                    $warehouse_impacts[$key] = array(
                        'current' => $current_stock ? $current_stock->quantity : 0,
                        'change' => 0
                    );
                }
                
                // Calculate impact
                if ($transaction_type === 'out' || $transaction_type === 'transfer') {
                    $warehouse_impacts[$key]['change'] -= $quantity;
                } elseif ($transaction_type === 'in') {
                    $warehouse_impacts[$key]['change'] += $quantity;
                }
                
                // Check if cumulative change would result in negative stock
                $projected_stock = $warehouse_impacts[$key]['current'] + $warehouse_impacts[$key]['change'];
                if ($projected_stock < 0) {
                    $validation['success'] = false;
                    $validation['errors'][] = app_lang('cumulative_operations_would_result_in_negative_stock');
                }
            }
            
            $results[$index] = array(
                'operation_index' => $index,
                'success' => $validation['success'],
                'errors' => $validation['errors'],
                'message' => $validation['message']
            );
        }
        
        return array(
            'success' => !array_filter($results, function($r) { return !$r['success']; }),
            'results' => $results,
            'total' => count($operations),
            'valid' => count(array_filter($results, function($r) { return $r['success']; })),
            'invalid' => count(array_filter($results, function($r) { return !$r['success']; })),
            'warehouse_impacts' => $warehouse_impacts
        );
    }

    /**
     * Validate inventory report parameters
     *
     * @param array $params Report parameters
     * @return array Validation result
     */
    public function validate_report_parameters($params) {
        $errors = array();
        
        $report_type = get_array_value($params, 'report_type');
        $date_from = get_array_value($params, 'date_from');
        $date_to = get_array_value($params, 'date_to');
        $warehouse_id = get_array_value($params, 'warehouse_id');
        
        // Validate report type
        $valid_report_types = array('stock_summary', 'transactions', 'low_stock', 'movement', 'valuation');
        if (!in_array($report_type, $valid_report_types)) {
            $errors[] = app_lang('invalid_report_type');
        }
        
        // Validate date range
        if ($date_from && $date_to) {
            if (strtotime($date_from) > strtotime($date_to)) {
                $errors[] = app_lang('date_from_cannot_be_after_date_to');
            }
            
            // Check if date range is too large (e.g., more than 1 year)
            $diff = strtotime($date_to) - strtotime($date_from);
            if ($diff > 365 * 24 * 60 * 60) {
                $errors[] = app_lang('date_range_too_large');
            }
        }
        
        // Validate warehouse if specified
        if ($warehouse_id) {
            $warehouse_validation = $this->validate_warehouse($warehouse_id);
            if (!$warehouse_validation['success']) {
                $errors[] = $warehouse_validation['error'];
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? app_lang('report_parameters_valid') : implode('<br/>', $errors)
        );
    }
}