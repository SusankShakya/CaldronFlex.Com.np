<?php

namespace App\Models;

class Pricing_rules_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'pricing_rules';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $pricing_rules_table = $this->db->prefixTable('pricing_rules');
        $items_table = $this->db->prefixTable('items');
        $item_categories_table = $this->db->prefixTable('item_categories');
        $clients_table = $this->db->prefixTable('clients');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $pricing_rules_table.id=$id";
        }

        $item_id = $this->_get_clean_value($options, "item_id");
        if ($item_id) {
            $where .= " AND $pricing_rules_table.item_id=$item_id";
        }

        $category_id = $this->_get_clean_value($options, "category_id");
        if ($category_id) {
            $where .= " AND $pricing_rules_table.category_id=$category_id";
        }

        $customer_id = $this->_get_clean_value($options, "customer_id");
        if ($customer_id) {
            $where .= " AND $pricing_rules_table.customer_id=$customer_id";
        }

        $rule_type = $this->_get_clean_value($options, "rule_type");
        if ($rule_type) {
            $where .= " AND $pricing_rules_table.rule_type='$rule_type'";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status !== null) {
            $where .= " AND $pricing_rules_table.status=$status";
        }

        $current_date = $this->_get_clean_value($options, "current_date");
        if ($current_date) {
            $where .= " AND ($pricing_rules_table.start_date IS NULL OR $pricing_rules_table.start_date <= '$current_date')";
            $where .= " AND ($pricing_rules_table.end_date IS NULL OR $pricing_rules_table.end_date >= '$current_date')";
        }

        $limit_query = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $offset = $this->_get_clean_value($options, "offset");
            $limit_query = "LIMIT $offset, $limit";
        }

        $sql = "SELECT $pricing_rules_table.*,
                $items_table.title as item_title,
                $item_categories_table.title as category_title,
                $clients_table.company_name as customer_name,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user
                FROM $pricing_rules_table
                LEFT JOIN $items_table ON $items_table.id=$pricing_rules_table.item_id
                LEFT JOIN $item_categories_table ON $item_categories_table.id=$pricing_rules_table.category_id
                LEFT JOIN $clients_table ON $clients_table.id=$pricing_rules_table.customer_id
                LEFT JOIN $users_table ON $users_table.id=$pricing_rules_table.created_by
                WHERE $pricing_rules_table.deleted=0 $where
                ORDER BY $pricing_rules_table.priority DESC, $pricing_rules_table.id DESC
                $limit_query";

        return $this->db->query($sql);
    }

    /**
     * Get area-based pricing rules for an item
     */
    function get_area_based_rules($item_id, $area) {
        $pricing_rules_table = $this->db->prefixTable('pricing_rules');
        $current_date = date('Y-m-d');

        $sql = "SELECT * FROM $pricing_rules_table
                WHERE deleted=0 
                AND status=1
                AND rule_type='area_based'
                AND (item_id=$item_id OR item_id IS NULL)
                AND (min_area IS NULL OR min_area <= $area)
                AND (max_area IS NULL OR max_area >= $area)
                AND (start_date IS NULL OR start_date <= '$current_date')
                AND (end_date IS NULL OR end_date >= '$current_date')
                ORDER BY priority DESC, id DESC
                LIMIT 1";

        $result = $this->db->query($sql);
        return $result->getRow();
    }

    /**
     * Get quantity discount rules
     */
    function get_quantity_discount_rules($item_id, $quantity, $customer_id = null) {
        $pricing_rules_table = $this->db->prefixTable('pricing_rules');
        $current_date = date('Y-m-d');

        $where = "";
        if ($customer_id) {
            $where .= " AND (customer_id=$customer_id OR customer_id IS NULL)";
        } else {
            $where .= " AND customer_id IS NULL";
        }

        $sql = "SELECT * FROM $pricing_rules_table
                WHERE deleted=0 
                AND status=1
                AND rule_type IN ('quantity_tier', 'customer_specific')
                AND (item_id=$item_id OR item_id IS NULL)
                AND min_quantity <= $quantity
                AND (max_quantity IS NULL OR max_quantity >= $quantity)
                AND (start_date IS NULL OR start_date <= '$current_date')
                AND (end_date IS NULL OR end_date >= '$current_date')
                $where
                ORDER BY customer_id DESC, priority DESC, discount_value DESC";

        return $this->db->query($sql)->getResult();
    }

    /**
     * Get customer-specific pricing rules
     */
    function get_customer_specific_rules($item_id, $customer_id) {
        $pricing_rules_table = $this->db->prefixTable('pricing_rules');
        $current_date = date('Y-m-d');

        $sql = "SELECT * FROM $pricing_rules_table
                WHERE deleted=0 
                AND status=1
                AND rule_type='customer_specific'
                AND customer_id=$customer_id
                AND (item_id=$item_id OR item_id IS NULL)
                AND (start_date IS NULL OR start_date <= '$current_date')
                AND (end_date IS NULL OR end_date >= '$current_date')
                ORDER BY item_id DESC, priority DESC
                LIMIT 1";

        $result = $this->db->query($sql);
        return $result->getRow();
    }

    /**
     * Get rule types
     */
    function get_rule_types() {
        return array(
            "area_based" => app_lang("area_based_pricing"),
            "quantity_tier" => app_lang("quantity_tier_discount"),
            "customer_specific" => app_lang("customer_specific_pricing"),
            "custom" => app_lang("custom_pricing")
        );
    }

    /**
     * Get discount types
     */
    function get_discount_types() {
        return array(
            "percentage" => app_lang("percentage"),
            "fixed" => app_lang("fixed_amount")
        );
    }

    /**
     * Check if a rule overlaps with existing rules
     */
    function check_rule_overlap($data, $exclude_id = 0) {
        $pricing_rules_table = $this->db->prefixTable('pricing_rules');

        $where = "";
        if ($exclude_id) {
            $where .= " AND id != $exclude_id";
        }

        // Check for same rule type and overlapping conditions
        $item_id = get_array_value($data, "item_id");
        $category_id = get_array_value($data, "category_id");
        $customer_id = get_array_value($data, "customer_id");
        $rule_type = get_array_value($data, "rule_type");
        $min_quantity = get_array_value($data, "min_quantity", 0);
        $max_quantity = get_array_value($data, "max_quantity");
        $min_area = get_array_value($data, "min_area");
        $max_area = get_array_value($data, "max_area");
        $start_date = get_array_value($data, "start_date");
        $end_date = get_array_value($data, "end_date");

        // Build conditions based on rule type
        if ($rule_type === 'area_based' && $item_id) {
            $sql = "SELECT COUNT(*) as count FROM $pricing_rules_table
                    WHERE deleted=0 
                    AND status=1
                    AND rule_type='area_based'
                    AND item_id=$item_id
                    $where";

            if ($min_area && $max_area) {
                $sql .= " AND ((min_area <= $max_area AND max_area >= $min_area)
                          OR (min_area >= $min_area AND min_area <= $max_area)
                          OR (max_area >= $min_area AND max_area <= $max_area))";
            }
        } else if ($rule_type === 'quantity_tier' && $item_id) {
            $sql = "SELECT COUNT(*) as count FROM $pricing_rules_table
                    WHERE deleted=0 
                    AND status=1
                    AND rule_type='quantity_tier'
                    AND item_id=$item_id
                    AND ((min_quantity <= $min_quantity AND (max_quantity IS NULL OR max_quantity >= $min_quantity))
                         OR (min_quantity >= $min_quantity AND min_quantity <= " . ($max_quantity ?: 999999) . "))
                    $where";
        } else if ($rule_type === 'customer_specific' && $customer_id && $item_id) {
            $sql = "SELECT COUNT(*) as count FROM $pricing_rules_table
                    WHERE deleted=0 
                    AND status=1
                    AND rule_type='customer_specific'
                    AND customer_id=$customer_id
                    AND item_id=$item_id
                    $where";
        } else {
            return false;
        }

        // Check date overlap
        if ($start_date || $end_date) {
            if ($start_date && $end_date) {
                $sql .= " AND ((start_date <= '$end_date' AND (end_date IS NULL OR end_date >= '$start_date'))
                          OR (start_date >= '$start_date' AND start_date <= '$end_date')
                          OR ((end_date IS NULL OR end_date >= '$start_date') AND (end_date IS NULL OR end_date <= '$end_date')))";
            } else if ($start_date) {
                $sql .= " AND (end_date IS NULL OR end_date >= '$start_date')";
            } else if ($end_date) {
                $sql .= " AND (start_date IS NULL OR start_date <= '$end_date')";
            }
        }

        $result = $this->db->query($sql)->getRow();
        return $result->count > 0;
    }

    /**
     * Save pricing rule with validation
     */
    function save_with_check($data, $id = 0) {
        // Check for overlapping rules
        if ($this->check_rule_overlap($data, $id)) {
            return array("success" => false, "message" => app_lang("pricing_rule_overlap_error"));
        }

        $save_id = $this->ci_save($data, $id);
        if ($save_id) {
            return array("success" => true, "id" => $save_id);
        }

        return array("success" => false, "message" => app_lang("error_occurred"));
    }
}