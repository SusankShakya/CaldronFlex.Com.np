<?php

namespace App\Models;

class Client_wallet_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'client_wallet';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $client_wallet_table = $this->db->prefixTable('client_wallet');
        $clients_table = $this->db->prefixTable('clients');
        $users_table = $this->db->prefixTable('users');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $client_wallet_table.id=$id";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $client_wallet_table.client_id=$client_id";
        }

        $sql = "SELECT $client_wallet_table.*, $clients_table.company_name AS company_name, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$client_wallet_table.client_id limit 1) AS currency_symbol,
            CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user, $users_table.image AS created_by_avatar
        FROM $client_wallet_table
        LEFT JOIN $clients_table ON $clients_table.id=$client_wallet_table.client_id
        LEFT JOIN $users_table ON $users_table.id=$client_wallet_table.created_by
        WHERE $client_wallet_table.deleted=0 AND $clients_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_client_wallet_summary($client_id, $options = array()) {
        $client_wallet_table = $this->db->prefixTable('client_wallet');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $payment_methods_table = $this->db->prefixTable('payment_methods');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');

        $result = new \stdClass();
        $client_id = $this->_get_clean_value($client_id);

        $where = "";

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($client_wallet_table.payment_date BETWEEN '$start_date' AND '$end_date')";
        }

        $client_wallet_sql = "SELECT SUM($client_wallet_table.amount) AS total_client_wallet_amount
        FROM $client_wallet_table
        WHERE $client_wallet_table.deleted=0 AND $client_wallet_table.client_id=$client_id $where";

        $client_wallet = $this->db->query($client_wallet_sql)->getRow();
        $result->total_client_wallet_amount = is_null($client_wallet->total_client_wallet_amount) ? 0 : $client_wallet->total_client_wallet_amount;
        $result->total_client_wallet_amount = number_format($result->total_client_wallet_amount, 2, ".", "");

        $invoice_payments_sql = "SELECT SUM($invoice_payments_table.amount) AS total_distributed_amount,
            (SELECT $payment_methods_table.id FROM $payment_methods_table WHERE deleted=0 AND type='client_wallet') AS client_wallet_payment_method_id
        FROM $invoice_payments_table
        WHERE $invoice_payments_table.deleted=0 
            AND $invoice_payments_table.payment_method_id=(
                SELECT $payment_methods_table.id FROM $payment_methods_table WHERE deleted=0 AND type='client_wallet') 
            AND $invoice_payments_table.invoice_id IN(
                SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.client_id=$client_id)";

        $invoice_payments = $this->db->query($invoice_payments_sql)->getRow();
        $result->total_distributed_amount = is_null($invoice_payments->total_distributed_amount) ? 0 : $invoice_payments->total_distributed_amount;
        $result->total_distributed_amount = number_format($result->total_distributed_amount, 2, ".", "");
        $result->client_wallet_payment_method_id = $invoice_payments->client_wallet_payment_method_id;

        $result->balance = $result->total_client_wallet_amount - $result->total_distributed_amount;

        $currency_symbol_sql = "SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$client_id limit 1";
        $result->currency_symbol = $this->db->query($currency_symbol_sql)->getRow()->currency_symbol;

        return $result;
    }
}
