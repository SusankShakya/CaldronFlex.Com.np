<?php

namespace App\Services;

use App\Models\Custom_dimensions_model;
use App\Models\Items_model;

class Custom_dimensions_validation_service {

    private $custom_dimensions_model;
    private $items_model;
    
    // Define dimension limits
    const MIN_DIMENSION = 0.1; // Minimum 0.1 unit
    const MAX_DIMENSION = 10000; // Maximum 10000 units
    const MIN_AREA = 0.01; // Minimum area in square units
    const MAX_AREA = 100000000; // Maximum area in square units
    const MIN_VOLUME = 0.001; // Minimum volume in cubic units
    const MAX_VOLUME = 1000000000; // Maximum volume in cubic units

    function __construct() {
        $this->custom_dimensions_model = new Custom_dimensions_model();
        $this->items_model = new Items_model();
    }

    /**
     * Validate custom dimensions for an item
     * 
     * @param array $data Dimension data to validate
     * @return array Validation result with success status and messages
     */
    public function validate_dimensions($data) {
        $errors = array();
        
        // Extract dimension values
        $width = get_array_value($data, 'width');
        $height = get_array_value($data, 'height');
        $depth = get_array_value($data, 'depth');
        $unit = get_array_value($data, 'unit', 'cm');
        $item_id = get_array_value($data, 'item_id');
        
        // Validate required fields
        if (!$width && !$height && !$depth) {
            $errors[] = app_lang('at_least_one_dimension_required');
        }
        
        // Validate numeric values
        if ($width !== null && $width !== '') {
            if (!is_numeric($width)) {
                $errors[] = app_lang('width_must_be_numeric');
            } elseif ($width <= 0) {
                $errors[] = app_lang('width_must_be_positive');
            } elseif (!$this->validate_dimension_range($width)) {
                $errors[] = sprintf(app_lang('width_out_of_range'), self::MIN_DIMENSION, self::MAX_DIMENSION);
            }
        }
        
        if ($height !== null && $height !== '') {
            if (!is_numeric($height)) {
                $errors[] = app_lang('height_must_be_numeric');
            } elseif ($height <= 0) {
                $errors[] = app_lang('height_must_be_positive');
            } elseif (!$this->validate_dimension_range($height)) {
                $errors[] = sprintf(app_lang('height_out_of_range'), self::MIN_DIMENSION, self::MAX_DIMENSION);
            }
        }
        
        if ($depth !== null && $depth !== '') {
            if (!is_numeric($depth)) {
                $errors[] = app_lang('depth_must_be_numeric');
            } elseif ($depth <= 0) {
                $errors[] = app_lang('depth_must_be_positive');
            } elseif (!$this->validate_dimension_range($depth)) {
                $errors[] = sprintf(app_lang('depth_out_of_range'), self::MIN_DIMENSION, self::MAX_DIMENSION);
            }
        }
        
        // Validate unit
        if (!$this->validate_unit($unit)) {
            $errors[] = app_lang('invalid_measurement_unit');
        }
        
        // Validate area if width and height are provided
        if (is_numeric($width) && is_numeric($height) && $width > 0 && $height > 0) {
            $area = $width * $height;
            if (!$this->validate_area_range($area)) {
                $errors[] = sprintf(app_lang('area_out_of_range'), self::MIN_AREA, self::MAX_AREA);
            }
        }
        
        // Validate volume if all dimensions are provided
        if (is_numeric($width) && is_numeric($height) && is_numeric($depth) && 
            $width > 0 && $height > 0 && $depth > 0) {
            $volume = $width * $height * $depth;
            if (!$this->validate_volume_range($volume)) {
                $errors[] = sprintf(app_lang('volume_out_of_range'), self::MIN_VOLUME, self::MAX_VOLUME);
            }
        }
        
        // Validate item-specific constraints if item_id is provided
        if ($item_id) {
            $item_errors = $this->validate_item_constraints($item_id, $data);
            $errors = array_merge($errors, $item_errors);
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? app_lang('dimensions_valid') : implode('<br/>', $errors)
        );
    }

    /**
     * Validate dimension range
     */
    private function validate_dimension_range($value) {
        return $value >= self::MIN_DIMENSION && $value <= self::MAX_DIMENSION;
    }

    /**
     * Validate area range
     */
    private function validate_area_range($area) {
        return $area >= self::MIN_AREA && $area <= self::MAX_AREA;
    }

    /**
     * Validate volume range
     */
    private function validate_volume_range($volume) {
        return $volume >= self::MIN_VOLUME && $volume <= self::MAX_VOLUME;
    }

    /**
     * Validate measurement unit
     */
    private function validate_unit($unit) {
        $valid_units = array('mm', 'cm', 'm', 'in', 'ft');
        return in_array($unit, $valid_units);
    }

    /**
     * Validate unit conversion
     * 
     * @param float $value Original value
     * @param string $from_unit Source unit
     * @param string $to_unit Target unit
     * @return array Validation result with converted value
     */
    public function validate_unit_conversion($value, $from_unit, $to_unit) {
        if (!is_numeric($value) || $value <= 0) {
            return array(
                'success' => false,
                'error' => app_lang('invalid_value_for_conversion')
            );
        }
        
        if (!$this->validate_unit($from_unit) || !$this->validate_unit($to_unit)) {
            return array(
                'success' => false,
                'error' => app_lang('invalid_units_for_conversion')
            );
        }
        
        // Convert to base unit (meters)
        $base_value = $this->convert_to_meters($value, $from_unit);
        
        // Convert from base unit to target unit
        $converted_value = $this->convert_from_meters($base_value, $to_unit);
        
        return array(
            'success' => true,
            'original_value' => $value,
            'original_unit' => $from_unit,
            'converted_value' => $converted_value,
            'converted_unit' => $to_unit
        );
    }

    /**
     * Convert value to meters (base unit)
     */
    private function convert_to_meters($value, $unit) {
        $conversions = array(
            'mm' => 0.001,
            'cm' => 0.01,
            'm' => 1,
            'in' => 0.0254,
            'ft' => 0.3048
        );
        
        return $value * $conversions[$unit];
    }

    /**
     * Convert value from meters to target unit
     */
    private function convert_from_meters($value, $unit) {
        $conversions = array(
            'mm' => 1000,
            'cm' => 100,
            'm' => 1,
            'in' => 39.3701,
            'ft' => 3.28084
        );
        
        return $value * $conversions[$unit];
    }

    /**
     * Validate area calculation
     * 
     * @param array $dimensions Dimension data
     * @return array Validation result with calculated area
     */
    public function validate_area_calculation($dimensions) {
        $width = get_array_value($dimensions, 'width');
        $height = get_array_value($dimensions, 'height');
        $unit = get_array_value($dimensions, 'unit', 'cm');
        
        // Validate inputs
        if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) {
            return array(
                'success' => false,
                'error' => app_lang('invalid_dimensions_for_area_calculation')
            );
        }
        
        $area = $width * $height;
        
        return array(
            'success' => true,
            'width' => $width,
            'height' => $height,
            'area' => $area,
            'unit' => $unit,
            'area_unit' => $unit . '²'
        );
    }

    /**
     * Validate volume calculation
     * 
     * @param array $dimensions Dimension data
     * @return array Validation result with calculated volume
     */
    public function validate_volume_calculation($dimensions) {
        $width = get_array_value($dimensions, 'width');
        $height = get_array_value($dimensions, 'height');
        $depth = get_array_value($dimensions, 'depth');
        $unit = get_array_value($dimensions, 'unit', 'cm');
        
        // Validate inputs
        if (!is_numeric($width) || !is_numeric($height) || !is_numeric($depth) || 
            $width <= 0 || $height <= 0 || $depth <= 0) {
            return array(
                'success' => false,
                'error' => app_lang('invalid_dimensions_for_volume_calculation')
            );
        }
        
        $volume = $width * $height * $depth;
        
        return array(
            'success' => true,
            'width' => $width,
            'height' => $height,
            'depth' => $depth,
            'volume' => $volume,
            'unit' => $unit,
            'volume_unit' => $unit . '³'
        );
    }

    /**
     * Validate price calculation based on dimensions
     * 
     * @param array $data Dimension and pricing data
     * @return array Validation result with calculated price
     */
    public function validate_price_calculation($data) {
        $dimensions = get_array_value($data, 'dimensions', array());
        $pricing_type = get_array_value($data, 'pricing_type', 'fixed');
        $base_price = get_array_value($data, 'base_price', 0);
        $price_per_unit = get_array_value($data, 'price_per_unit', 0);
        
        // Validate base price
        if (!is_numeric($base_price) || $base_price < 0) {
            return array(
                'success' => false,
                'error' => app_lang('invalid_base_price')
            );
        }
        
        // Calculate price based on pricing type
        switch ($pricing_type) {
            case 'area_based':
                $area_result = $this->validate_area_calculation($dimensions);
                if (!$area_result['success']) {
                    return $area_result;
                }
                
                if (!is_numeric($price_per_unit) || $price_per_unit <= 0) {
                    return array(
                        'success' => false,
                        'error' => app_lang('invalid_price_per_unit')
                    );
                }
                
                $calculated_price = $base_price + ($area_result['area'] * $price_per_unit);
                break;
                
            case 'volume_based':
                $volume_result = $this->validate_volume_calculation($dimensions);
                if (!$volume_result['success']) {
                    return $volume_result;
                }
                
                if (!is_numeric($price_per_unit) || $price_per_unit <= 0) {
                    return array(
                        'success' => false,
                        'error' => app_lang('invalid_price_per_unit')
                    );
                }
                
                $calculated_price = $base_price + ($volume_result['volume'] * $price_per_unit);
                break;
                
            case 'perimeter_based':
                $width = get_array_value($dimensions, 'width', 0);
                $height = get_array_value($dimensions, 'height', 0);
                
                if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) {
                    return array(
                        'success' => false,
                        'error' => app_lang('invalid_dimensions_for_perimeter_calculation')
                    );
                }
                
                $perimeter = 2 * ($width + $height);
                $calculated_price = $base_price + ($perimeter * $price_per_unit);
                break;
                
            default:
                $calculated_price = $base_price;
        }
        
        return array(
            'success' => true,
            'pricing_type' => $pricing_type,
            'base_price' => $base_price,
            'calculated_price' => $calculated_price,
            'currency' => get_setting('default_currency')
        );
    }

    /**
     * Validate item-specific dimension constraints
     */
    private function validate_item_constraints($item_id, $dimensions) {
        $errors = array();
        
        // Get item details
        $item = $this->items_model->get_one($item_id);
        if (!$item) {
            $errors[] = app_lang('item_not_found');
            return $errors;
        }
        
        // Check if item has dimension constraints
        $constraints = $this->custom_dimensions_model->get_item_constraints($item_id);
        if ($constraints) {
            $width = get_array_value($dimensions, 'width');
            $height = get_array_value($dimensions, 'height');
            $depth = get_array_value($dimensions, 'depth');
            
            // Check minimum constraints
            if ($constraints->min_width && $width && $width < $constraints->min_width) {
                $errors[] = sprintf(app_lang('width_below_minimum'), $constraints->min_width);
            }
            if ($constraints->min_height && $height && $height < $constraints->min_height) {
                $errors[] = sprintf(app_lang('height_below_minimum'), $constraints->min_height);
            }
            if ($constraints->min_depth && $depth && $depth < $constraints->min_depth) {
                $errors[] = sprintf(app_lang('depth_below_minimum'), $constraints->min_depth);
            }
            
            // Check maximum constraints
            if ($constraints->max_width && $width && $width > $constraints->max_width) {
                $errors[] = sprintf(app_lang('width_above_maximum'), $constraints->max_width);
            }
            if ($constraints->max_height && $height && $height > $constraints->max_height) {
                $errors[] = sprintf(app_lang('height_above_maximum'), $constraints->max_height);
            }
            if ($constraints->max_depth && $depth && $depth > $constraints->max_depth) {
                $errors[] = sprintf(app_lang('depth_above_maximum'), $constraints->max_depth);
            }
            
            // Check aspect ratio constraints
            if ($constraints->aspect_ratio && $width && $height) {
                $actual_ratio = $width / $height;
                $expected_ratio = $constraints->aspect_ratio;
                $tolerance = 0.1; // 10% tolerance
                
                if (abs($actual_ratio - $expected_ratio) > $tolerance) {
                    $errors[] = sprintf(app_lang('aspect_ratio_mismatch'), $expected_ratio);
                }
            }
        }
        
        return $errors;
    }

    /**
     * Validate dimensions for bulk operations
     *
     * @param array $items Array of items with dimensions
     * @return array Validation results for each item
     */
    public function validate_bulk_dimensions($items) {
        $results = array();
        
        foreach ($items as $index => $item) {
            $validation = $this->validate_dimensions($item);
            $results[$index] = array(
                'item_id' => get_array_value($item, 'item_id'),
                'success' => $validation['success'],
                'errors' => $validation['errors'],
                'message' => $validation['message']
            );
        }
        
        return array(
            'success' => !array_filter($results, function($r) { return !$r['success']; }),
            'results' => $results,
            'total' => count($items),
            'valid' => count(array_filter($results, function($r) { return $r['success']; })),
            'invalid' => count(array_filter($results, function($r) { return !$r['success']; }))
        );
    }
}