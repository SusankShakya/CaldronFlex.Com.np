<?php

namespace App\Services;

use App\Models\Printing_specifications_model;
use App\Models\Items_model;
use App\Models\Materials_model;

class Printing_specifications_validation_service {

    private $printing_specifications_model;
    private $items_model;
    private $materials_model;
    
    // Define validation constants
    const MIN_DPI = 72;
    const MAX_DPI = 2400;
    const MIN_QUANTITY = 1;
    const MAX_QUANTITY = 1000000;
    const MIN_BLEED = 0;
    const MAX_BLEED = 50; // mm
    const VALID_COLOR_MODES = array('CMYK', 'RGB', 'Pantone', 'Grayscale', 'Black and White');
    const VALID_FILE_FORMATS = array('PDF', 'AI', 'EPS', 'PSD', 'TIFF', 'PNG', 'JPG', 'SVG');

    function __construct() {
        $this->printing_specifications_model = new Printing_specifications_model();
        $this->items_model = new Items_model();
        $this->materials_model = new Materials_model();
    }

    /**
     * Validate printing specifications
     * 
     * @param array $data Printing specification data to validate
     * @return array Validation result with success status and messages
     */
    public function validate_specifications($data) {
        $errors = array();
        
        // Extract specification data
        $item_id = get_array_value($data, 'item_id');
        $resolution_dpi = get_array_value($data, 'resolution_dpi');
        $color_mode = get_array_value($data, 'color_mode');
        $bleed_area = get_array_value($data, 'bleed_area');
        $file_format = get_array_value($data, 'file_format');
        $print_quantity = get_array_value($data, 'print_quantity');
        $pantone_colors = get_array_value($data, 'pantone_colors', array());
        $special_instructions = get_array_value($data, 'special_instructions');
        
        // Validate item
        if (!$item_id) {
            $errors[] = app_lang('item_required');
        } else {
            $item = $this->items_model->get_one($item_id);
            if (!$item) {
                $errors[] = app_lang('item_not_found');
            }
        }
        
        // Validate resolution
        $resolution_validation = $this->validate_resolution($resolution_dpi);
        if (!$resolution_validation['success']) {
            $errors = array_merge($errors, $resolution_validation['errors']);
        }
        
        // Validate color mode
        if (!$this->validate_color_mode($color_mode)) {
            $errors[] = app_lang('invalid_color_mode');
        }
        
        // Validate Pantone colors if color mode is Pantone
        if ($color_mode === 'Pantone' && !empty($pantone_colors)) {
            $pantone_validation = $this->validate_pantone_colors($pantone_colors);
            if (!$pantone_validation['success']) {
                $errors = array_merge($errors, $pantone_validation['errors']);
            }
        }
        
        // Validate bleed area
        $bleed_validation = $this->validate_bleed_area($bleed_area);
        if (!$bleed_validation['success']) {
            $errors = array_merge($errors, $bleed_validation['errors']);
        }
        
        // Validate file format
        if (!$this->validate_file_format($file_format)) {
            $errors[] = app_lang('invalid_file_format');
        }
        
        // Validate file format compatibility with color mode
        $compatibility_check = $this->validate_format_compatibility($file_format, $color_mode);
        if (!$compatibility_check['success']) {
            $errors[] = $compatibility_check['error'];
        }
        
        // Validate print quantity
        $quantity_validation = $this->validate_quantity($print_quantity);
        if (!$quantity_validation['success']) {
            $errors = array_merge($errors, $quantity_validation['errors']);
        }
        
        // Validate special instructions length
        if ($special_instructions && strlen($special_instructions) > 1000) {
            $errors[] = app_lang('special_instructions_too_long');
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? app_lang('specifications_valid') : implode('<br/>', $errors)
        );
    }

    /**
     * Validate resolution (DPI)
     * 
     * @param int $dpi Resolution in DPI
     * @return array Validation result
     */
    public function validate_resolution($dpi) {
        $errors = array();
        
        if (!is_numeric($dpi)) {
            $errors[] = app_lang('resolution_must_be_numeric');
        } elseif ($dpi < self::MIN_DPI) {
            $errors[] = sprintf(app_lang('resolution_too_low'), self::MIN_DPI);
        } elseif ($dpi > self::MAX_DPI) {
            $errors[] = sprintf(app_lang('resolution_too_high'), self::MAX_DPI);
        }
        
        // Warn about quality issues
        if (empty($errors) && $dpi < 300) {
            $warnings = array(app_lang('low_resolution_warning'));
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'warnings' => isset($warnings) ? $warnings : array(),
            'recommended_dpi' => $this->get_recommended_dpi($dpi)
        );
    }

    /**
     * Get recommended DPI based on print type
     */
    private function get_recommended_dpi($current_dpi) {
        if ($current_dpi < 150) {
            return 300; // Standard print quality
        } elseif ($current_dpi < 300) {
            return 300; // Standard print quality
        } elseif ($current_dpi < 600) {
            return 600; // High quality
        } else {
            return $current_dpi; // Keep current if already high
        }
    }

    /**
     * Validate color mode
     */
    private function validate_color_mode($color_mode) {
        return in_array($color_mode, self::VALID_COLOR_MODES);
    }

    /**
     * Validate Pantone colors
     * 
     * @param array $pantone_colors Array of Pantone color codes
     * @return array Validation result
     */
    public function validate_pantone_colors($pantone_colors) {
        $errors = array();
        
        if (!is_array($pantone_colors)) {
            $errors[] = app_lang('pantone_colors_must_be_array');
            return array('success' => false, 'errors' => $errors);
        }
        
        foreach ($pantone_colors as $color) {
            if (!$this->is_valid_pantone_code($color)) {
                $errors[] = sprintf(app_lang('invalid_pantone_code'), $color);
            }
        }
        
        // Check for duplicates
        $unique_colors = array_unique($pantone_colors);
        if (count($unique_colors) < count($pantone_colors)) {
            $errors[] = app_lang('duplicate_pantone_colors');
        }
        
        // Limit number of Pantone colors (cost consideration)
        if (count($unique_colors) > 8) {
            $errors[] = app_lang('too_many_pantone_colors');
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'color_count' => count($unique_colors)
        );
    }

    /**
     * Validate Pantone color code format
     */
    private function is_valid_pantone_code($code) {
        // Basic Pantone code validation (e.g., "Pantone 185 C", "Pantone 7547 U")
        return preg_match('/^Pantone\s+\d{3,4}\s*[CU]?$/i', $code);
    }

    /**
     * Validate bleed area
     * 
     * @param float $bleed Bleed area in mm
     * @return array Validation result
     */
    public function validate_bleed_area($bleed) {
        $errors = array();
        
        if ($bleed !== null && $bleed !== '') {
            if (!is_numeric($bleed)) {
                $errors[] = app_lang('bleed_must_be_numeric');
            } elseif ($bleed < self::MIN_BLEED) {
                $errors[] = app_lang('bleed_cannot_be_negative');
            } elseif ($bleed > self::MAX_BLEED) {
                $errors[] = sprintf(app_lang('bleed_too_large'), self::MAX_BLEED);
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'standard_bleed' => 3 // Standard 3mm bleed
        );
    }

    /**
     * Validate file format
     */
    private function validate_file_format($format) {
        return in_array(strtoupper($format), self::VALID_FILE_FORMATS);
    }

    /**
     * Validate file format compatibility with color mode
     * 
     * @param string $format File format
     * @param string $color_mode Color mode
     * @return array Validation result
     */
    public function validate_format_compatibility($format, $color_mode) {
        $incompatible_combinations = array(
            'RGB' => array('EPS'), // EPS typically doesn't support RGB well
            'CMYK' => array('PNG'), // PNG doesn't support CMYK
            'Pantone' => array('JPG', 'PNG') // Raster formats don't support spot colors
        );
        
        if (isset($incompatible_combinations[$color_mode]) && 
            in_array(strtoupper($format), $incompatible_combinations[$color_mode])) {
            return array(
                'success' => false,
                'error' => sprintf(app_lang('format_incompatible_with_color_mode'), $format, $color_mode)
            );
        }
        
        return array('success' => true);
    }

    /**
     * Validate print quantity
     * 
     * @param int $quantity Print quantity
     * @return array Validation result
     */
    public function validate_quantity($quantity) {
        $errors = array();
        
        if (!is_numeric($quantity) || $quantity != intval($quantity)) {
            $errors[] = app_lang('quantity_must_be_integer');
        } elseif ($quantity < self::MIN_QUANTITY) {
            $errors[] = sprintf(app_lang('quantity_too_low'), self::MIN_QUANTITY);
        } elseif ($quantity > self::MAX_QUANTITY) {
            $errors[] = sprintf(app_lang('quantity_too_high'), self::MAX_QUANTITY);
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'quantity' => intval($quantity)
        );
    }

    /**
     * Calculate additional cost for printing specifications
     * 
     * @param array $specifications Printing specifications
     * @return array Cost calculation result
     */
    public function calculate_additional_cost($specifications) {
        $additional_cost = 0;
        $cost_breakdown = array();
        
        // High resolution cost
        $dpi = get_array_value($specifications, 'resolution_dpi', 300);
        if ($dpi > 600) {
            $resolution_cost = ($dpi - 600) * 0.01; // $0.01 per DPI above 600
            $additional_cost += $resolution_cost;
            $cost_breakdown['high_resolution'] = $resolution_cost;
        }
        
        // Color mode cost
        $color_mode = get_array_value($specifications, 'color_mode');
        switch ($color_mode) {
            case 'CMYK':
                $color_cost = 10; // Base CMYK cost
                break;
            case 'Pantone':
                $pantone_colors = get_array_value($specifications, 'pantone_colors', array());
                $color_cost = count($pantone_colors) * 25; // $25 per Pantone color
                break;
            case 'RGB':
                $color_cost = 5; // RGB conversion cost
                break;
            default:
                $color_cost = 0;
        }
        if ($color_cost > 0) {
            $additional_cost += $color_cost;
            $cost_breakdown['color_mode'] = $color_cost;
        }
        
        // Bleed area cost
        $bleed = get_array_value($specifications, 'bleed_area', 0);
        if ($bleed > 3) { // More than standard 3mm
            $bleed_cost = ($bleed - 3) * 2; // $2 per mm above standard
            $additional_cost += $bleed_cost;
            $cost_breakdown['extra_bleed'] = $bleed_cost;
        }
        
        // Special file format handling
        $format = get_array_value($specifications, 'file_format');
        if (in_array($format, array('AI', 'PSD'))) {
            $format_cost = 15; // Processing cost for complex formats
            $additional_cost += $format_cost;
            $cost_breakdown['file_processing'] = $format_cost;
        }
        
        // Quantity-based discounts or surcharges
        $quantity = get_array_value($specifications, 'print_quantity', 1);
        if ($quantity < 100) {
            $setup_cost = 50; // Small quantity setup cost
            $additional_cost += $setup_cost;
            $cost_breakdown['small_quantity_setup'] = $setup_cost;
        }
        
        return array(
            'success' => true,
            'additional_cost' => $additional_cost,
            'cost_breakdown' => $cost_breakdown,
            'currency' => get_setting('default_currency')
        );
    }

    /**
     * Validate specifications for a specific material
     * 
     * @param array $specifications Printing specifications
     * @param int $material_id Material ID
     * @return array Validation result
     */
    public function validate_material_compatibility($specifications, $material_id) {
        $material = $this->materials_model->get_one($material_id);
        
        if (!$material) {
            return array(
                'success' => false,
                'error' => app_lang('material_not_found')
            );
        }
        
        $material_properties = json_decode($material->properties, true) ?: array();
        $incompatibilities = array();
        
        // Check if material supports the color mode
        $color_mode = get_array_value($specifications, 'color_mode');
        $supported_color_modes = get_array_value($material_properties, 'supported_color_modes', array());
        
        if (!empty($supported_color_modes) && !in_array($color_mode, $supported_color_modes)) {
            $incompatibilities[] = sprintf(app_lang('material_does_not_support_color_mode'), 
                $material->name, $color_mode);
        }
        
        // Check resolution compatibility
        $dpi = get_array_value($specifications, 'resolution_dpi');
        $max_dpi = get_array_value($material_properties, 'max_resolution_dpi');
        
        if ($max_dpi && $dpi > $max_dpi) {
            $incompatibilities[] = sprintf(app_lang('material_max_resolution_exceeded'),
                $material->name, $max_dpi);
        }
        
        // Check bleed compatibility
        $bleed = get_array_value($specifications, 'bleed_area');
        $max_bleed = get_array_value($material_properties, 'max_bleed_mm');
        
        if ($max_bleed && $bleed > $max_bleed) {
            $incompatibilities[] = sprintf(app_lang('material_max_bleed_exceeded'),
                $material->name, $max_bleed);
        }
        
        return array(
            'success' => empty($incompatibilities),
            'incompatibilities' => $incompatibilities,
            'material' => $material
        );
    }

    /**
     * Validate bulk printing specifications
     *
     * @param array $specifications Array of specification data
     * @return array Validation results
     */
    public function validate_bulk_specifications($specifications) {
        $results = array();
        
        foreach ($specifications as $index => $spec) {
            $validation = $this->validate_specifications($spec);
            $results[$index] = array(
                'item_id' => get_array_value($spec, 'item_id'),
                'success' => $validation['success'],
                'errors' => $validation['errors'],
                'message' => $validation['message']
            );
        }
        
        return array(
            'success' => !array_filter($results, function($r) { return !$r['success']; }),
            'results' => $results,
            'total' => count($specifications),
            'valid' => count(array_filter($results, function($r) { return $r['success']; })),
            'invalid' => count(array_filter($results, function($r) { return !$r['success']; }))
        );
    }

    /**
     * Get recommended specifications based on item type
     *
     * @param int $item_id Item ID
     * @return array Recommended specifications
     */
    public function get_recommended_specifications($item_id) {
        $item = $this->items_model->get_one($item_id);
        
        if (!$item) {
            return array(
                'success' => false,
                'error' => app_lang('item_not_found')
            );
        }
        
        // Default recommendations
        $recommendations = array(
            'resolution_dpi' => 300,
            'color_mode' => 'CMYK',
            'bleed_area' => 3,
            'file_format' => 'PDF'
        );
        
        // Adjust based on item category or type
        if (isset($item->category_id)) {
            switch ($item->category_id) {
                case 1: // Business cards
                    $recommendations['resolution_dpi'] = 300;
                    $recommendations['bleed_area'] = 3;
                    break;
                case 2: // Large format printing
                    $recommendations['resolution_dpi'] = 150;
                    $recommendations['bleed_area'] = 5;
                    break;
                case 3: // Fine art prints
                    $recommendations['resolution_dpi'] = 600;
                    $recommendations['color_mode'] = 'RGB';
                    break;
            }
        }
        
        return array(
            'success' => true,
            'recommendations' => $recommendations,
            'item' => $item
        );
    }
}