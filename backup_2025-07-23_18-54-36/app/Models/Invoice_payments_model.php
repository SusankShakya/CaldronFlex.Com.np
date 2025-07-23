<?php

namespace App\Models;

class Invoice_payments_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'invoice_payments';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $payment_methods_table = $this->db->prefixTable('payment_methods');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $invoice_payments_table.id=$id";
        }

        $invoice_id = $this->_get_clean_value($options, "invoice_id");
        if ($invoice_id) {
            $where .= " AND $invoice_payments_table.invoice_id=$invoice_id";
        }

        $order_id = $this->_get_clean_value($options, "order_id");
        if ($order_id) {
            $where .= " AND $invoice_payments_table.invoice_id IN(SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.order_id=$order_id)";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $invoices_table.client_id=$client_id";
        }

        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $invoices_table.project_id=$project_id";
        }

        $payment_method_id = $this->_get_clean_value($options, "payment_method_id");
        if ($payment_method_id) {
            $where .= " AND $invoice_payments_table.payment_method_id=$payment_method_id";
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($invoice_payments_table.payment_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $currency = $this->_get_clean_value($options, "currency");
        if ($currency) {
            $where .= $this->_get_clients_of_currency_query($currency, $invoices_table, $clients_table);
        }

        $show_own_client_invoice_user_id = $this->_get_clean_value($options, "show_own_client_invoice_user_id");
        if ($show_own_client_invoice_user_id) {
            $where .= " AND $clients_table.owner_id = $show_own_client_invoice_user_id";
        }

        $show_own_invoices_only_user_id = $this->_get_clean_value($options, "show_own_invoices_only_user_id");
        if ($show_own_invoices_only_user_id) {
            $where .= " AND $invoices_table.created_by = $show_own_invoices_only_user_id";
        }

        $sql = "SELECT $invoice_payments_table.*, $invoices_table.client_id, $invoices_table.display_id, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$invoices_table.client_id limit 1) AS currency_symbol, $payment_methods_table.title AS payment_method_title
        FROM $invoice_payments_table
        LEFT JOIN $invoices_table ON $invoices_table.id=$invoice_payments_table.invoice_id
        LEFT JOIN $clients_table ON $clients_table.id = $invoices_table.client_id
        LEFT JOIN $payment_methods_table ON $payment_methods_table.id = $invoice_payments_table.payment_method_id
        WHERE $invoice_payments_table.deleted=0 AND $invoices_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_yearly_payments_data($options = array()) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');

        $year = $this->_get_clean_value($options, "year");
        $project_id = $this->_get_clean_value($options, "project_id");

        $where = "";
        $currency = $this->_get_clean_value($options, "currency");
        if ($currency) {
            $where = $this->_get_clients_of_currency_query($currency, $invoices_table, $clients_table);
        }

        if ($project_id) {
            $where .= " AND $payments_table.invoice_id IN(SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.project_id=$project_id)";
        }

        $show_own_client_invoice_user_id = get_array_value($options, "show_own_client_invoice_user_id");
        if ($show_own_client_invoice_user_id) {
            $where .= " AND $payments_table.invoice_id IN(SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.owner_id = $show_own_client_invoice_user_id))";
        }

        $show_own_invoices_only_user_id = get_array_value($options, "show_own_invoices_only_user_id");
        if ($show_own_invoices_only_user_id) {
            $where .= " AND $payments_table.invoice_id IN(SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.created_by = $show_own_invoices_only_user_id)";
        }

        $payments = "SELECT SUM($payments_table.amount) AS total, MONTH($payments_table.payment_date) AS month,
            (SELECT $clients_table.currency FROM $clients_table WHERE $clients_table.id=(
                SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$payments_table.invoice_id
                )
            ) AS currency
            FROM $payments_table
            LEFT JOIN $invoices_table ON $invoices_table.id=$payments_table.invoice_id
            WHERE $payments_table.deleted=0 AND YEAR($payments_table.payment_date)= $year AND $invoices_table.deleted=0 $where
            GROUP BY MONTH($payments_table.payment_date), currency";

        return $this->db->query($payments)->getResult();
    }

    function get_yearly_payments_chart_data($options = array()) {
        $payments_data = $this->get_yearly_payments_data($options);

        $payments = array_fill(1, 12, 0);
        foreach ($payments_data as $payment) {
            $payments[(int)$payment->month] = get_converted_amount($payment->currency, $payment->total);
        }

        return array_values($payments);
    }

    function get_used_projects($type) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $projects_table = $this->db->prefixTable('projects');
        $expenses_table = $this->db->prefixTable('expenses');

        $payments_where = "SELECT $invoices_table.project_id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.project_id!=0 AND $invoices_table.id IN(SELECT $payments_table.invoice_id FROM $payments_table WHERE $payments_table.deleted=0 GROUP BY $payments_table.invoice_id) GROUP BY $invoices_table.project_id";
        $expenses_where = "SELECT $expenses_table.project_id FROM $expenses_table WHERE $expenses_table.deleted=0 AND $expenses_table.project_id!=0 GROUP BY $expenses_table.project_id";

        $where = "";
        if ($type == "all") {
            $where = " AND $projects_table.id IN($payments_where) OR $projects_table.id IN($expenses_where)";
        } else if ($type == "payments") {
            $where = " AND $projects_table.id IN($payments_where)";
        } else if ($type == "expenses") {
            $where = " AND $projects_table.id IN($expenses_where)";
        }

        $sql = "SELECT $projects_table.id, $projects_table.title 
            FROM $projects_table 
            WHERE $projects_table.deleted=0 $where
            GROUP BY $projects_table.id";

        return $this->db->query($sql);
    }

    function get_yearly_summary_details($options = array()) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($payments_table.payment_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $payment_method_id = $this->_get_clean_value($options, "payment_method_id");
        if ($payment_method_id) {
            $where .= " AND $payments_table.payment_method_id=$payment_method_id";
        }

        $selected_currency = get_array_value($options, "currency");
        $default_currency = get_setting("default_currency");
        $currency = $selected_currency ? $selected_currency : get_setting("default_currency");

        $currency = $this->_get_clean_value(array("currency" => $currency), "currency");

        $where .= ($currency == $default_currency) ? " AND ($clients_table.currency='$default_currency' OR $clients_table.currency='' OR $clients_table.currency IS NULL)" : " AND $clients_table.currency='$currency'";

        $sql = "SELECT COUNT($payments_table.id) AS payment_count, SUM($payments_table.amount) AS amount, MONTH($payments_table.payment_date) AS month, $clients_table.currency, $clients_table.currency_symbol
        FROM $payments_table
        LEFT JOIN $invoices_table ON $invoices_table.id=$payments_table.invoice_id
        LEFT JOIN $clients_table ON $clients_table.id=(SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$payments_table.invoice_id LIMIT 1)
        WHERE $payments_table.deleted=0 $where
        GROUP BY MONTH($payments_table.payment_date)";

        return $this->db->query($sql);
    }

    function get_clients_summary_details($options = array()) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($payments_table.payment_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $payment_method_id = $this->_get_clean_value($options, "payment_method_id");
        if ($payment_method_id) {
            $where .= " AND $payments_table.payment_method_id=$payment_method_id";
        }

        $selected_currency = get_array_value($options, "currency");
        $default_currency = get_setting("default_currency");
        $currency = $selected_currency ? $selected_currency : get_setting("default_currency");
        $currency = $this->_get_clean_value(array("currency" => $currency), "currency");

        $where .= ($currency == $default_currency) ? " AND ($clients_table.currency='$default_currency' OR $clients_table.currency='' OR $clients_table.currency IS NULL)" : " AND $clients_table.currency='$currency'";

        $sql = "SELECT COUNT($payments_table.id) AS payment_count, SUM($payments_table.amount) AS amount, $invoices_table.client_id, $clients_table.company_name AS client_name, $clients_table.currency, $clients_table.currency_symbol
        FROM $payments_table
        LEFT JOIN $invoices_table ON $invoices_table.id=$payments_table.invoice_id
        LEFT JOIN $clients_table ON $clients_table.id=(SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$payments_table.invoice_id LIMIT 1)
        WHERE $payments_table.deleted=0 AND $invoices_table.deleted=0 AND $clients_table.deleted=0 $where
        GROUP BY $invoices_table.client_id";

        return $this->db->query($sql);
    }

    function get_client_statement($options = array()) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $payment_methods_table = $this->db->prefixTable('payment_methods');
        $client_wallet_table = $this->db->prefixTable('client_wallet');

        $invoices_where = "";
        $invoices_where_for_payments = "";
        $payments_where = "";
        $client_wallet_where = "";

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");

        $generate_reports_based_on = "$invoices_table.due_date";
        if (get_setting("generate_reports_based_on") == "bill_date") {
            $generate_reports_based_on = "$invoices_table.bill_date";
        }

        if ($start_date && $end_date) {
            $invoices_where .= " AND ($generate_reports_based_on BETWEEN '$start_date' AND '$end_date')";
            $payments_where .= " AND ($payments_table.payment_date BETWEEN '$start_date' AND '$end_date')";
            $client_wallet_where .= " AND ($client_wallet_table.payment_date BETWEEN '$start_date' AND '$end_date')";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $invoices_where .= " AND $invoices_table.client_id=$client_id";
            $invoices_where_for_payments .= " AND $invoices_table.client_id=$client_id";
            $client_wallet_where .= " AND $client_wallet_table.client_id=$client_id";
        }

        $sql = "(SELECT $generate_reports_based_on AS date, $invoices_table.display_id AS description, $invoices_table.invoice_total AS invoice_total, 0 AS payment, 'invoice' AS type
        FROM $invoices_table
        WHERE $invoices_table.deleted=0 AND $invoices_table.status!='draft' AND $invoices_table.status!='cancelled' $invoices_where)
        UNION ALL
        (SELECT $payments_table.payment_date AS date, $payment_methods_table.title AS description, 0 AS invoice_total, $payments_table.amount AS payment, 'payment' AS type
        FROM $payments_table
        LEFT JOIN $payment_methods_table ON $payment_methods_table.id=$payments_table.payment_method_id
        LEFT JOIN $invoices_table ON $invoices_table.id=$payments_table.invoice_id
        WHERE $payments_table.deleted=0 AND $invoices_table.deleted=0 AND $invoices_table.status!='draft' AND $invoices_table.status!='cancelled' $invoices_where_for_payments
            AND $payments_table.payment_method_id!=(
                SELECT $payment_methods_table.id FROM $payment_methods_table WHERE $payment_methods_table.type='client_wallet'
            ) $payments_where)
        UNION ALL
        (SELECT $client_wallet_table.payment_date AS date, (SELECT $payment_methods_table.title FROM $payment_methods_table WHERE $payment_methods_table.type='client_wallet') AS description, 0 AS invoice_total, $client_wallet_table.amount AS payment, 'payment' AS type
        FROM $client_wallet_table
        WHERE $client_wallet_table.deleted=0 $client_wallet_where)
        ORDER by date ASC";

        return $this->db->query($sql);
    }

    function get_opening_balance_of_client($options = array()) {
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $payment_methods_table = $this->db->prefixTable('payment_methods');
        $client_wallet_table = $this->db->prefixTable('client_wallet');

        $start_date = $this->_get_clean_value($options, "start_date");
        $client_id = $this->_get_clean_value($options, "client_id");

        $generate_reports_based_on = "$invoices_table.due_date";
        if (get_setting("generate_reports_based_on") == "bill_date") {
            $generate_reports_based_on = "$invoices_table.bill_date";
        }

        $result = new \stdClass();

        $where = "";
        $invoices_where = "";
        if ($start_date) {
            $where .= " AND $generate_reports_based_on<'$start_date' ";
        }

        if ($client_id) {
            $invoices_where .= " AND $invoices_table.client_id=$client_id";
        }

        $sql = "SELECT SUM($invoices_table.invoice_total) AS total_invoiced
        FROM $invoices_table
        WHERE $invoices_table.deleted=0 AND $invoices_table.status!='draft' AND $invoices_table.status!='cancelled' $where $invoices_where";
        $total_invoiced = $this->db->query($sql)->getRow()->total_invoiced;

        $where = "";
        if ($start_date) {
            $where .= " AND $invoice_payments_table.payment_date<'$start_date' ";
        }

        $sql = "SELECT SUM($invoice_payments_table.amount) AS payment_received
        FROM $invoice_payments_table
        WHERE $invoice_payments_table.deleted=0 
            AND $invoice_payments_table.payment_method_id!=(
                SELECT $payment_methods_table.id FROM $payment_methods_table WHERE deleted=0 AND type='client_wallet') 
            AND $invoice_payments_table.invoice_id IN(
                SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted=0 $invoices_where) $where";

        $payment_received = $this->db->query($sql)->getRow()->payment_received;

        $where = "";
        if ($start_date) {
            $where .= " AND $client_wallet_table.payment_date<'$start_date' ";
        }

        if ($client_id) {
            $where .= " AND $client_wallet_table.client_id=$client_id";
        }

        $sql = "SELECT SUM($client_wallet_table.amount) AS total_client_wallet_amount
        FROM $client_wallet_table
        WHERE $client_wallet_table.deleted=0 $where";
        $total_client_wallet_amount = $this->db->query($sql)->getRow()->total_client_wallet_amount;

        return $total_invoiced - $payment_received - $total_client_wallet_amount;
    }
}
