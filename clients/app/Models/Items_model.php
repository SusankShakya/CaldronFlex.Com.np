<?php

namespace App\Models;

class Items_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $items_table = $this->db->prefixTable('items');
        $order_items_table = $this->db->prefixTable('order_items');
        $item_categories_table = $this->db->prefixTable('item_categories');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $items_table.id=$id";
        }

        $search = $this->_get_clean_value($options, "search");
        if ($search) {
            $search = $this->db->escapeLikeString($search);
            $where .= " AND ($items_table.title LIKE '%$search%' ESCAPE '!' OR $items_table.description LIKE '%$search%' ESCAPE '!')";
        }

        $show_in_client_portal = $this->_get_clean_value($options, "show_in_client_portal");
        if ($show_in_client_portal) {
            $where .= " AND $items_table.show_in_client_portal=1";
        }

        $category_id = $this->_get_clean_value($options, "category_id");
        if ($category_id) {
            $where .= " AND $items_table.category_id=$category_id";
        }

        $extra_select = "";
        $login_user_id = $this->_get_clean_value($options, "login_user_id");
        $created_by_hash = $this->_get_clean_value($options, "created_by_hash");
        if ($login_user_id || $created_by_hash) {

            $extra_where = "";
            if ($login_user_id) {
                $extra_where = " AND $order_items_table.created_by=$login_user_id ";
            } else if ($created_by_hash) {
                $extra_where = " AND $order_items_table.created_by_hash='$created_by_hash' ";
            }

            if ($login_user_id && $created_by_hash) {
                $extra_where = " AND ($order_items_table.created_by=$login_user_id OR $order_items_table.created_by_hash='$created_by_hash') ";
            }

            $extra_select = ", (SELECT COUNT($order_items_table.id) FROM $order_items_table WHERE $order_items_table.deleted=0 AND $order_items_table.order_id=0 AND $order_items_table.item_id=$items_table.id $extra_where ) AS added_to_cart";
        }

        $limit_query = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $offset = $this->_get_clean_value($options, "offset");
            $limit_query = "LIMIT $offset, $limit";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("items", $custom_fields, $items_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $sql = "SELECT $items_table.*, $item_categories_table.title as category_title $extra_select $select_custom_fieds
        FROM $items_table
        LEFT JOIN $item_categories_table ON $item_categories_table.id= $items_table.category_id
        $join_custom_fieds
        WHERE $items_table.deleted=0 $where $custom_fields_where
        ORDER BY $items_table.title ASC
        $limit_query";

        return $this->db->query($sql);
    }

    // Variant-related methods
    function get_variants($item_id) {
        $Variants_model = model("App\Models\Variants_model");
        return $Variants_model->get_variants_by_item($item_id);
    }

    function get_item_with_variants($item_id) {
        $item_info = $this->get_one($item_id);
        if ($item_info && $item_info->id) {
            $item_info->variants = $this->get_variants($item_id);
            $Variants_model = model("App\Models\Variants_model");
            $item_info->variant_combinations = $Variants_model->get_variant_combinations($item_id)->getResult();
        }
        return $item_info;
    }

    function calculate_item_price_with_variants($item_id, $variant_ids = array()) {
        $item_info = $this->get_one($item_id);
        if (!$item_info || !$item_info->id) {
            return false;
        }

        $base_price = $item_info->rate;
        $total_price = $base_price;

        if (!empty($variant_ids)) {
            $Variants_model = model("App\Models\Variants_model");
            
            // First check if there's a specific combination price
            $combination = $Variants_model->get_variant_combination_by_variants($item_id, $variant_ids);
            if ($combination && $combination->additional_price) {
                $total_price = $base_price + $combination->additional_price;
            } else {
                // Calculate price based on individual variant modifiers
                $variants = $this->db->table('item_variants')
                    ->whereIn('id', $variant_ids)
                    ->where('item_id', $item_id)
                    ->where('deleted', 0)
                    ->where('status', 1)
                    ->get()
                    ->getResult();

                foreach ($variants as $variant) {
                    if ($variant->price_modifier_type === 'fixed') {
                        $total_price += $variant->price_modifier;
                    } else if ($variant->price_modifier_type === 'percentage') {
                        $total_price += ($base_price * $variant->price_modifier / 100);
                    }
                }
            }
        }

        return $total_price;
    }

    function get_item_stock_with_variants($item_id, $variant_ids = array()) {
        if (empty($variant_ids)) {
            // Return base item stock if no variants selected
            return PHP_INT_MAX; // Assuming unlimited stock for base items
        }

        $Variants_model = model("App\Models\Variants_model");
        $combination = $Variants_model->get_variant_combination_by_variants($item_id, $variant_ids);
        
        if ($combination) {
            return $combination->stock_quantity;
        }

        return 0; // No stock if combination doesn't exist
    }

    function has_variants($item_id) {
        $variants_table = $this->db->prefixTable('item_variants');
        
        $sql = "SELECT COUNT(id) as total FROM $variants_table
                WHERE item_id=$item_id AND deleted=0 AND status=1";
        
        $result = $this->db->query($sql)->getRow();
        return $result && $result->total > 0;
    }

    function get_items_with_variant_info($options = array()) {
        $result = $this->get_details($options);
        $items = $result->getResult();
        
        foreach ($items as &$item) {
            $item->has_variants = $this->has_variants($item->id);
            if ($item->has_variants) {
                $item->variant_count = $this->db->table('item_variants')
                    ->where('item_id', $item->id)
                    ->where('deleted', 0)
                    ->countAllResults();
            }
        }
        
        return $items;
    }
}
