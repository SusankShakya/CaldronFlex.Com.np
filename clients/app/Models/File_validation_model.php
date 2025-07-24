<?php

namespace App\Models;

use App\Models\Crud_model;

class File_validation_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'file_validation_rules';
        parent::__construct($this->table);
    }

    /**
     * Get active validation rules for a category
     */
    function get_active_rules($file_category) {
        $where = array("file_category" => $file_category, "is_active" => 1, "deleted" => 0);
        return $this->get_all_where($where, 0, 0, "priority DESC")->getResult();
    }

    /**
     * Validate a file against rules
     */
    function validate_file($file, $file_category) {
        $rules = $this->get_active_rules($file_category);
        $errors = array();
        $warnings = array();

        foreach ($rules as $rule) {
            $allowed_extensions = json_decode($rule->allowed_extensions, true);
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_extensions)) {
                $errors[] = $this->get_error_message($rule, 'file_type', "Invalid file type");
            }
            if ($file['size'] > ($rule->max_file_size_mb * 1024 * 1024)) {
                $errors[] = $this->get_error_message($rule, 'file_size', "File size exceeds limit");
            }
            // Additional checks (resolution, color mode, etc.) would go here
        }

        return array("errors" => $errors, "warnings" => $warnings);
    }

    /**
     * Get error message from rule or fallback
     */
    function get_error_message($rule, $key, $default) {
        $messages = json_decode($rule->error_messages, true);
        return isset($messages[$key]) ? $messages[$key] : $default;
    }

    /**
     * Log file validation result
     */
    function log_validation($file, $rule_id, $status, $errors = [], $warnings = [], $meta = [], $ref_type = null, $ref_id = null, $uploaded_by = null) {
        $data = array(
            "file_name" => $file['name'],
            "file_size" => $file['size'],
            "file_type" => $file['type'],
            "validation_rule_id" => $rule_id,
            "validation_status" => $status,
            "validation_errors" => json_encode($errors),
            "validation_warnings" => json_encode($warnings),
            "metadata" => json_encode($meta),
            "reference_type" => $ref_type,
            "reference_id" => $ref_id,
            "uploaded_by" => $uploaded_by,
            "created_at" => get_current_utc_time()
        );
        return $this->db->table('file_validation_logs')->insert($data);
    }

    /**
     * Get validation logs for a reference
     */
    function get_logs($ref_type, $ref_id) {
        $where = array("reference_type" => $ref_type, "reference_id" => $ref_id);
        return $this->db->table('file_validation_logs')->where($where)->get()->getResult();
    }
}