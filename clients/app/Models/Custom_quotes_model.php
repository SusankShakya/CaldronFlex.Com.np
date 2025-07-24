<?php

namespace App\Models;

class Custom_quotes_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'custom_quotes';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $custom_quotes_table = $this->db->prefixTable('custom_quotes');
        $clients_table = $this->db->prefixTable('clients');
        $items_table = $this->db->prefixTable('items');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $custom_quotes_table.id=$id";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $custom_quotes_table.client_id=$client_id";
        }

        $item_id = $this->_get_clean_value($options, "item_id");
        if ($item_id) {
            $where .= " AND $custom_quotes_table.item_id=$item_id";
        }

        $status = $this->_get_clean_value($options, "quote_status");
        if ($status) {
            $where .= " AND $custom_quotes_table.quote_status='$status'";
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($custom_quotes_table.created_at BETWEEN '$start_date' AND '$end_date') ";
        }

        $sql = "SELECT $custom_quotes_table.*, 
                $clients_table.company_name, 
                $items_table.title as item_title,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user
                FROM $custom_quotes_table
                LEFT JOIN $clients_table ON $clients_table.id = $custom_quotes_table.client_id
                LEFT JOIN $items_table ON $items_table.id = $custom_quotes_table.item_id
                LEFT JOIN $users_table ON $users_table.id = $custom_quotes_table.created_by
                WHERE $custom_quotes_table.deleted=0 $where";

        return $this->db->query($sql);
    }

    /**
     * Generate a unique quote number
     */
    function generate_quote_number() {
        $prefix = get_setting("custom_quote_prefix") ?: "QT-";
        $last_quote = $this->get_all_where(array(), "id", "DESC", 1)->getRow();
        $last_id = $last_quote ? $last_quote->id : 0;
        return $prefix . str_pad($last_id + 1, 5, "0", STR_PAD_LEFT);
    }

    /**
     * Save a custom quote
     */
    function save_quote($data, $id = 0) {
        if (!$id) {
            $data['quote_number'] = $this->generate_quote_number();
            $data['created_at'] = get_current_utc_time();
            $data['created_by'] = $this->login_user->id;
        }

        $data['updated_at'] = get_current_utc_time();
        $data['updated_by'] = $this->login_user->id;
        
        // Calculate total if not provided
        if (!isset($data['total_amount'])) {
            $unit_price = get_array_value($data, 'unit_price', 0);
            $quantity = get_array_value($data, 'quantity', 1);
            $discount = get_array_value($data, 'discount_amount', 0);
            $data['total_amount'] = ($unit_price * $quantity) - $discount;
        }

        return $this->ci_save($data, $id);
    }

    /**
     * Convert a quote to an order
     */
    function convert_quote_to_order($quote_id) {
        $quote = $this->get_one($quote_id);
        if (!$quote || $quote->quote_status !== 'accepted') {
            return false;
        }
        
        $Orders_model = model("App\Models\Orders_model");
        $Order_items_model = model("App\Models\Order_items_model");
        
        // Create order data
        $order_data = array(
            "client_id" => $quote->client_id,
            "order_date" => date("Y-m-d"),
            "status_id" => get_setting("default_order_status"),
            "tax_id" => 0,
            "tax_id2" => 0,
            "discount_amount" => $quote->discount_amount,
            "discount_type" => 'fixed',
            "total" => $quote->total_amount,
            "notes" => "Converted from quote #{$quote->quote_number}\n\n" . $quote->notes,
        );
        
        $order_id = $Orders_model->ci_save($order_data);
        
        if ($order_id) {
            // Create order item data
            $order_item_data = array(
                "order_id" => $order_id,
                "item_id" => $quote->item_id,
                "title" => $quote->item_title,
                "description" => $quote->item_description,
                "quantity" => $quote->quantity,
                "unit_type" => '',
                "rate" => $quote->unit_price,
                "total" => $quote->total_amount,
                "variant_combination" => $quote->variant_combination,
                "dimensions" => $quote->dimensions
            );
            
            $Order_items_model->ci_save($order_item_data);
            
            // Update quote status
            $quote_update_data = array(
                "quote_status" => "converted",
                "converted_to_order_id" => $order_id
            );
            $this->ci_save($quote_update_data, $quote_id);
            
            return $order_id;
        }
        
        return false;
    }

    /**
     * Get quote status options
     */
    function get_status_options() {
        return array(
            "draft" => app_lang("draft"),
            "sent" => app_lang("sent"),
            "accepted" => app_lang("accepted"),
            "rejected" => app_lang("rejected"),
            "expired" => app_lang("expired"),
            "converted" => app_lang("converted_to_order")
        );
    }
}