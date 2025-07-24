<?php

namespace App\Services;

use App\Models\File_validation_model;
use App\Models\Items_model;

class File_validation_service {

    private $file_validation_model;
    private $items_model;
    
    // Define validation constants
    const MAX_FILE_SIZE = 52428800; // 50MB in bytes
    const MIN_FILE_SIZE = 1024; // 1KB in bytes
    const MAX_IMAGE_WIDTH = 10000; // pixels
    const MAX_IMAGE_HEIGHT = 10000; // pixels
    const MIN_IMAGE_WIDTH = 100; // pixels
    const MIN_IMAGE_HEIGHT = 100; // pixels
    const MIN_DPI = 72;
    const MAX_DPI = 2400;
    
    // Allowed file extensions by category
    const ALLOWED_EXTENSIONS = array(
        'image' => array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 'webp', 'svg'),
        'document' => array('pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'),
        'design' => array('ai', 'psd', 'eps', 'indd', 'cdr', 'sketch', 'fig'),
        'print' => array('pdf', 'ai', 'eps', 'tiff', 'tif', 'psd'),
        'archive' => array('zip', 'rar', '7z', 'tar', 'gz')
    );
    
    // MIME types for validation
    const ALLOWED_MIME_TYPES = array(
        'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff',
        'image/webp', 'image/svg+xml', 'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain', 'application/rtf', 'application/vnd.oasis.opendocument.text',
        'application/postscript', 'application/x-photoshop', 'application/illustrator',
        'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'
    );

    function __construct() {
        $this->file_validation_model = new File_validation_model();
        $this->items_model = new Items_model();
    }

    /**
     * Validate file upload
     * 
     * @param array $file File data from $_FILES
     * @param array $options Validation options
     * @return array Validation result with success status and messages
     */
    public function validate_file_upload($file, $options = array()) {
        $errors = array();
        
        // Extract file data
        $file_name = get_array_value($file, 'name');
        $file_size = get_array_value($file, 'size');
        $file_tmp = get_array_value($file, 'tmp_name');
        $file_error = get_array_value($file, 'error');
        $file_type = get_array_value($file, 'type');
        
        // Extract options
        $category = get_array_value($options, 'category', 'image');
        $max_size = get_array_value($options, 'max_size', self::MAX_FILE_SIZE);
        $min_size = get_array_value($options, 'min_size', self::MIN_FILE_SIZE);
        $check_dimensions = get_array_value($options, 'check_dimensions', true);
        $check_virus = get_array_value($options, 'check_virus', true);
        
        // Check upload error
        if ($file_error !== UPLOAD_ERR_OK) {
            $errors[] = $this->get_upload_error_message($file_error);
            return array(
                'success' => false,
                'errors' => $errors,
                'message' => implode('<br/>', $errors)
            );
        }
        
        // Validate file exists
        if (!$file_tmp || !file_exists($file_tmp)) {
            $errors[] = app_lang('file_not_found');
        }
        
        // Validate file name
        $name_validation = $this->validate_file_name($file_name);
        if (!$name_validation['success']) {
            $errors = array_merge($errors, $name_validation['errors']);
        }
        
        // Validate file extension
        $extension_validation = $this->validate_file_extension($file_name, $category);
        if (!$extension_validation['success']) {
            $errors[] = $extension_validation['error'];
        }
        
        // Validate MIME type
        if ($file_tmp && file_exists($file_tmp)) {
            $mime_validation = $this->validate_mime_type($file_tmp, $file_type);
            if (!$mime_validation['success']) {
                $errors[] = $mime_validation['error'];
            }
        }
        
        // Validate file size
        $size_validation = $this->validate_file_size($file_size, $min_size, $max_size);
        if (!$size_validation['success']) {
            $errors = array_merge($errors, $size_validation['errors']);
        }
        
        // Validate image dimensions if applicable
        if ($check_dimensions && $this->is_image_file($file_name) && file_exists($file_tmp)) {
            $dimension_validation = $this->validate_image_dimensions($file_tmp);
            if (!$dimension_validation['success']) {
                $errors = array_merge($errors, $dimension_validation['errors']);
            }
        }
        
        // Validate color space for print files
        if ($category === 'print' && file_exists($file_tmp)) {
            $color_validation = $this->validate_color_space($file_tmp, $file_name);
            if (!$color_validation['success']) {
                $errors[] = $color_validation['error'];
            }
        }
        
        // Virus scan (placeholder - would integrate with actual antivirus)
        if ($check_virus && file_exists($file_tmp)) {
            $virus_scan = $this->scan_for_virus($file_tmp);
            if (!$virus_scan['success']) {
                $errors[] = $virus_scan['error'];
            }
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? app_lang('file_valid') : implode('<br/>', $errors),
            'file_info' => empty($errors) ? array(
                'name' => $file_name,
                'size' => $file_size,
                'extension' => $extension_validation['extension'] ?? '',
                'mime_type' => $mime_validation['mime_type'] ?? ''
            ) : null
        );
    }

    /**
     * Get upload error message
     */
    private function get_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return app_lang('file_exceeds_server_limit');
            case UPLOAD_ERR_FORM_SIZE:
                return app_lang('file_exceeds_form_limit');
            case UPLOAD_ERR_PARTIAL:
                return app_lang('file_partially_uploaded');
            case UPLOAD_ERR_NO_FILE:
                return app_lang('no_file_uploaded');
            case UPLOAD_ERR_NO_TMP_DIR:
                return app_lang('missing_temp_folder');
            case UPLOAD_ERR_CANT_WRITE:
                return app_lang('failed_to_write_file');
            case UPLOAD_ERR_EXTENSION:
                return app_lang('upload_stopped_by_extension');
            default:
                return app_lang('unknown_upload_error');
        }
    }

    /**
     * Validate file name
     * 
     * @param string $file_name File name
     * @return array Validation result
     */
    public function validate_file_name($file_name) {
        $errors = array();
        
        if (empty($file_name)) {
            $errors[] = app_lang('file_name_required');
        }
        
        // Check for invalid characters
        if (preg_match('/[<>:"|?*]/', $file_name)) {
            $errors[] = app_lang('file_name_contains_invalid_characters');
        }
        
        // Check length
        if (strlen($file_name) > 255) {
            $errors[] = app_lang('file_name_too_long');
        }
        
        // Check for directory traversal attempts
        if (strpos($file_name, '..') !== false || strpos($file_name, '/') !== false || strpos($file_name, '\\') !== false) {
            $errors[] = app_lang('invalid_file_path');
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'sanitized_name' => $this->sanitize_file_name($file_name)
        );
    }

    /**
     * Sanitize file name
     */
    private function sanitize_file_name($file_name) {
        // Remove invalid characters
        $file_name = preg_replace('/[<>:"|?*\/\\\\]/', '', $file_name);
        
        // Replace spaces with underscores
        $file_name = str_replace(' ', '_', $file_name);
        
        // Remove multiple dots
        $file_name = preg_replace('/\.+/', '.', $file_name);
        
        // Ensure it doesn't start or end with a dot
        $file_name = trim($file_name, '.');
        
        return $file_name;
    }

    /**
     * Validate file extension
     * 
     * @param string $file_name File name
     * @param string $category File category
     * @return array Validation result
     */
    public function validate_file_extension($file_name, $category = 'image') {
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (empty($extension)) {
            return array(
                'success' => false,
                'error' => app_lang('file_extension_required')
            );
        }
        
        $allowed_extensions = isset(self::ALLOWED_EXTENSIONS[$category]) 
            ? self::ALLOWED_EXTENSIONS[$category] 
            : array_merge(...array_values(self::ALLOWED_EXTENSIONS));
        
        if (!in_array($extension, $allowed_extensions)) {
            return array(
                'success' => false,
                'error' => sprintf(app_lang('invalid_file_extension'), $extension, implode(', ', $allowed_extensions))
            );
        }
        
        return array(
            'success' => true,
            'extension' => $extension
        );
    }

    /**
     * Validate MIME type
     * 
     * @param string $file_path File path
     * @param string $reported_type Reported MIME type
     * @return array Validation result
     */
    public function validate_mime_type($file_path, $reported_type = null) {
        // Get actual MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actual_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        // Check if MIME type is allowed
        if (!in_array($actual_type, self::ALLOWED_MIME_TYPES)) {
            return array(
                'success' => false,
                'error' => sprintf(app_lang('invalid_mime_type'), $actual_type)
            );
        }
        
        // Check for MIME type mismatch (potential security issue)
        if ($reported_type && $reported_type !== $actual_type) {
            // Log potential security issue
            log_message('warning', "MIME type mismatch: reported=$reported_type, actual=$actual_type");
        }
        
        return array(
            'success' => true,
            'mime_type' => $actual_type
        );
    }

    /**
     * Validate file size
     * 
     * @param int $size File size in bytes
     * @param int $min_size Minimum size in bytes
     * @param int $max_size Maximum size in bytes
     * @return array Validation result
     */
    public function validate_file_size($size, $min_size = null, $max_size = null) {
        $errors = array();
        
        $min_size = $min_size ?: self::MIN_FILE_SIZE;
        $max_size = $max_size ?: self::MAX_FILE_SIZE;
        
        if ($size < $min_size) {
            $errors[] = sprintf(app_lang('file_too_small'), $this->format_bytes($min_size));
        }
        
        if ($size > $max_size) {
            $errors[] = sprintf(app_lang('file_too_large'), $this->format_bytes($max_size));
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'size' => $size,
            'formatted_size' => $this->format_bytes($size)
        );
    }

    /**
     * Format bytes to human readable
     */
    private function format_bytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Check if file is an image
     */
    private function is_image_file($file_name) {
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        return in_array($extension, self::ALLOWED_EXTENSIONS['image']);
    }

    /**
     * Validate image dimensions
     * 
     * @param string $file_path File path
     * @return array Validation result
     */
    public function validate_image_dimensions($file_path) {
        $errors = array();
        
        $image_info = @getimagesize($file_path);
        
        if (!$image_info) {
            return array(
                'success' => false,
                'errors' => array(app_lang('cannot_read_image_dimensions'))
            );
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        
        // Validate minimum dimensions
        if ($width < self::MIN_IMAGE_WIDTH) {
            $errors[] = sprintf(app_lang('image_width_too_small'), self::MIN_IMAGE_WIDTH);
        }
        
        if ($height < self::MIN_IMAGE_HEIGHT) {
            $errors[] = sprintf(app_lang('image_height_too_small'), self::MIN_IMAGE_HEIGHT);
        }
        
        // Validate maximum dimensions
        if ($width > self::MAX_IMAGE_WIDTH) {
            $errors[] = sprintf(app_lang('image_width_too_large'), self::MAX_IMAGE_WIDTH);
        }
        
        if ($height > self::MAX_IMAGE_HEIGHT) {
            $errors[] = sprintf(app_lang('image_height_too_large'), self::MAX_IMAGE_HEIGHT);
        }
        
        return array(
            'success' => empty($errors),
            'errors' => $errors,
            'dimensions' => array