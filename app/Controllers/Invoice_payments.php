<?php

namespace App\Controllers;

use App\Libraries\Paytm;
use App\Libraries\Stripe;
use App\Libraries\Paypal;

class Invoice_payments extends Security_Controller {

    private $Client_wallet_model;

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("invoice");
        $this->Client_wallet_model = model("App\Models\Client_wallet_model");
    }

    /* load invoice list view */

    function index() {
        if ($this->login_user->user_type === "staff") {
            $view_data['payment_method_dropdown'] =  $this->Payment_methods_model->get_payment_methods_dropdown();
            $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
            $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_expenses("payments");
            $view_data["conversion_rate"] = $this->get_conversion_rate_with_currency_symbol();

            $can_edit_invoices = false;
            if ($this->can_edit_invoices()) {
                $can_edit_invoices = true;
            }
            $view_data["can_edit_invoices"] = $can_edit_invoices;

            return $this->template->rander("invoices/payment_received", $view_data);
        } else {
            if (!($this->can_client_access("invoice") && $this->can_client_access("payment", false))) {
                app_redirect("forbidden");
            }

            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";

            $view_data['show_client_wallet'] = false;
            $view_data["can_edit_invoices"] = false;

            return $this->template->rander("clients/payments/index", $view_data);
        }
    }

    /* load payment modal */

    function payment_modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "invoice_id" => "numeric"
        ));

        $view_data['model_info'] = $this->Invoice_payments_model->get_one($this->request->getPost('id'));

        $invoice_id = $this->request->getPost('invoice_id') ? $this->request->getPost('invoice_id') : $view_data['model_info']->invoice_id;

        if (!$invoice_id) {
            //prepare invoices dropdown
            $client_id = $this->request->getPost('client_id');
            $invoices = $this->Invoices_model->get_invoices_dropdown_list($client_id)->getResult();
            $invoices_dropdown = array();

            // calculate max length
            $max_chr_length = 0;
            foreach ($invoices as $invoice) {
                if (strlen($invoice->display_id) > $max_chr_length) {
                    $max_chr_length = strlen($invoice->display_id);
                }
            }
            $max_chr_length = $max_chr_length + 1;

            $default_currency_symbol = get_setting("currency_symbol");
            foreach ($invoices as $invoice) {

                $chr_length = $max_chr_length - strlen($invoice->display_id);
                $no_of_nbsp_needed = $chr_length;
                $nbsp = "";
                for ($i = 0; $i < $no_of_nbsp_needed; $i++) {
                    $nbsp .= "&nbsp;&nbsp;";
                }

                $currency_symbol = $invoice->currency_symbol ? $invoice->currency_symbol : $default_currency_symbol;
                $invoices_dropdown[$invoice->id] = $invoice->display_id . $nbsp . "  (" . app_lang("due") . ": " . to_currency($invoice->invoice_due, $currency_symbol) . ")";
            }

            $view_data['invoices_dropdown'] = array("" => "-") + $invoices_dropdown;
        }

        $amount = $view_data['model_info']->amount ? to_decimal_format($view_data['model_info']->amount) : "";
        if (!$view_data['model_info']->amount && $invoice_id) {
            $amount = to_decimal_format($this->Invoices_model->get_invoice_total_summary($invoice_id)->balance_due);
        }

        $view_data["amount"] = $amount;

        helper('cookie');
        $selected_payment_method = get_cookie("user_" . $this->login_user->id . "_payment_method");
        $view_data['payment_methods_dropdown'] = $this->Payment_methods_model->get_payment_methods_dropdown(true, $selected_payment_method);
        $view_data['invoice_id'] = $invoice_id;

        return $this->template->view('invoices/payment_modal_form', $view_data);
    }

    /* add or edit a payment */

    function save_payment() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "invoice_id" => "required|numeric",
            "invoice_payment_method_id" => "required|numeric",
            "invoice_payment_date" => "required",
            "invoice_payment_amount" => "required"
        ));

        $id = $this->request->getPost('id');
        $invoice_id = $this->request->getPost('invoice_id');
        $amount = unformat_currency($this->request->getPost('invoice_payment_amount'));
        $payment_method_id = $this->request->getPost('invoice_payment_method_id');

        // check if the payment method is client wallet
        // if so, check if there has enough balance
        $payment_method_info = $this->Payment_methods_model->get_one($payment_method_id);
        if ($payment_method_info->type == "client_wallet") {

            $invoice_info = $this->Invoices_model->get_one($invoice_id);
            $Client_wallet_model = model("App\Models\Client_wallet_model");
            $client_wallet_summary = $Client_wallet_model->get_client_wallet_summary($invoice_info->client_id);

            if ($client_wallet_summary->balance < $amount) {
                echo json_encode(array("success" => false, 'message' => app_lang("insufficient_balance_in_client_wallet")));
                exit();
            }
        }

        $invoice_payment_data = array(
            "invoice_id" => $invoice_id,
            "payment_date" => $this->request->getPost('invoice_payment_date'),
            "payment_method_id" => $payment_method_id,
            "note" => $this->request->getPost('invoice_payment_note'),
            "amount" => $amount,
            "created_at" => get_current_utc_time(),
            "created_by" => $this->login_user->id,
        );

        $invoice_payment_data = clean_data($invoice_payment_data);

        $invoice_payment_id = $this->Invoice_payments_model->ci_save($invoice_payment_data, $id);
        if ($invoice_payment_id) {

            //As receiving payment for the invoice, we'll remove the 'draft' status from the invoice 
            $this->Invoices_model->update_invoice_status($invoice_id);

            if (!$id) {
                //show payment confirmation and payment received notification for new payments only
                log_notification("invoice_payment_confirmation", array("invoice_payment_id" => $invoice_payment_id, "invoice_id" => $invoice_id), "0");
                log_notification("invoice_manual_payment_added", array("invoice_payment_id" => $invoice_payment_id, "invoice_id" => $invoice_id), $this->login_user->id);
            }

            echo json_encode(array("success" => true, "id" => $invoice_payment_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete or undo a payment */

    function delete_payment() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Invoice_payments_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Invoice_payments_model->get_details($options)->getRow();
                echo json_encode(array("success" => true, "invoice_id" => $item_info->invoice_id, "data" => $this->_make_payment_row($item_info),  "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Invoice_payments_model->delete($id)) {
                $item_info = $this->Invoice_payments_model->get_one($id);
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of invoice payments, prepared for datatable  */

    function payment_list_data($invoice_id = 0, $is_mobile = 0) {
        if (!$this->can_view_invoices($invoice_id)) {
            app_redirect("forbidden");
        }

        $payment_id = $this->request->getPost('id');

        validate_numeric_value($invoice_id);
        validate_numeric_value($is_mobile);
        validate_numeric_value($payment_id);

        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        $payment_method_id = $this->request->getPost('payment_method_id');
        $options = array(
            "id" => $payment_id,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "invoice_id" => $invoice_id,
            "payment_method_id" => $payment_method_id,
            "currency" => $this->request->getPost("currency"),
            "project_id" => $this->request->getPost("project_id"),
            "show_own_client_invoice_user_id" => $this->show_own_client_invoice_user_id(),
            "show_own_invoices_only_user_id" => $this->show_own_invoices_only_user_id()
        );

        $list_data = $this->Invoice_payments_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data, $is_mobile);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of invoice payments, prepared for datatable  */

    function payment_list_data_of_client($client_id = 0) {
        if (!$this->can_view_invoices(0, $client_id)) {
            app_redirect("forbidden");
        }

        validate_numeric_value($client_id);
        $options = array(
            "client_id" => $client_id,
            "payment_method_id" => $this->request->getPost('payment_method_id'),
        );
        $list_data = $this->Invoice_payments_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of invoice payments, prepared for datatable  */

    function payment_list_data_of_project($project_id = 0) {
        validate_numeric_value($project_id);
        $options = array("project_id" => $project_id);

        $list_data = $this->Invoice_payments_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    private function _payment_row_data($id) {
        $options = array("id" => $id);
        $data = $this->Invoice_payments_model->get_details($options)->getRow();

        $is_mobile = 0;
        if ($this->request->getPost('mobile_mirror')) {
            $is_mobile = 1;
        }

        return $this->_make_payment_row($data, $is_mobile);
    }

    /* prepare a row of invoice payment list table */

    private function _make_payment_row($data, $is_mobile = 0) {
        $invoice_url = "";
        if (!$this->can_view_invoices($data->invoice_id, $data->client_id)) {
            app_redirect("forbidden");
        }

        if ($this->login_user->user_type == "staff") {
            $invoice_url = anchor(get_uri("invoices/view/" . $data->invoice_id), $data->display_id);
        } else {
            $invoice_url = anchor(get_uri("invoices/preview/" . $data->invoice_id), $data->display_id);
        }

        $payment_date = format_to_date($data->payment_date, false);
        $title = "";
        if ($is_mobile) {
            $title = "<div>
            <span>$data->payment_date</span>
            <span class='float-end strong'>" . to_currency($data->amount, $data->currency_symbol) . "</span>
            <span class='float-end me-3'>$data->payment_method_title</span>
            <div class='text-wrap text-off mt5'>$data->note</div>
            </div>";
        }

        return array(
            $invoice_url,
            $data->payment_date,
            $is_mobile ? $title : $payment_date,
            $data->payment_method_title,
            $data->note,
            to_currency($data->amount, $data->currency_symbol),
            modal_anchor(get_uri("invoice_payments/payment_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_payment'), "data-post-id" => $data->id, "data-post-invoice_id" => $data->invoice_id,))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("invoice_payments/delete_payment"), "data-action" => "delete-confirmation"))
        );
    }


    //load the expenses yearly chart view
    function yearly_chart() {
        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
        return $this->template->view("invoices/yearly_payments_chart", $view_data);
    }

    function yearly_chart_data() {

        $months = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");

        $year = $this->request->getPost("year");
        if ($year) {
            $currency = $this->request->getPost("currency");

            $options = array(
                "year" => $year,
                "currency" => $currency,
                "show_own_client_invoice_user_id" => $this->show_own_client_invoice_user_id(),
                "show_own_invoices_only_user_id" => $this->show_own_invoices_only_user_id()
            );

            $payments = $this->Invoice_payments_model->get_yearly_payments_data($options);
            $values = array();
            foreach ($payments as $value) {
                $converted_rate = get_converted_amount($value->currency, $value->total);
                $values[$value->month - 1] = isset($values[$value->month - 1]) ? ($values[$value->month - 1] + $converted_rate) : $converted_rate; //in array the month january(1) = index(0)
            }

            foreach ($months as $key => $month) {
                $value = get_array_value($values, $key);
                $short_months[] = app_lang("short_" . $month);
                $data[] = $value ? $value : 0;
            }

            echo json_encode(array("months" => $short_months, "data" => $data, "currency_symbol" => $currency));
        }
    }

    function get_paytm_checksum_hash() {
        $paytm = new Paytm();
        $payment_data = $paytm->get_paytm_checksum_hash($this->request->getPost("input_data"), $this->request->getPost("verification_data"));

        if ($payment_data) {
            echo json_encode(array("success" => true, "checksum_hash" => get_array_value($payment_data, "checksum_hash"), "payment_verification_code" => get_array_value($payment_data, "payment_verification_code")));
        } else {
            echo json_encode(array("success" => false, "message" => app_lang("paytm_checksum_hash_error_message")));
        }
    }

    function get_stripe_checkout_session() {
        $this->access_only_clients();
        $stripe = new Stripe();
        try {
            $session = $stripe->get_stripe_checkout_session($this->request->getPost("input_data"), $this->login_user->id);
            if ($session->id) {
                echo json_encode(array("success" => true, "checkout_url" => $session->url));
            } else {
                echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
            }
        } catch (\Exception $ex) {
            echo json_encode(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    function get_paypal_checkout_url() {
        $this->access_only_clients();
        $paypal = new Paypal();
        try {
            $checkout_url = $paypal->get_paypal_checkout_url($this->request->getPost("input_data"), $this->login_user->id);
            if ($checkout_url) {
                echo json_encode(array("success" => true, "checkout_url" => $checkout_url));
            } else {
                echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
            }
        } catch (\Exception $ex) {
            echo json_encode(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    function payments_summary() {
        if (!$this->can_view_invoices()) {
            app_redirect("forbidden");
        }

        $view_data['can_access_clients'] = $this->can_access_clients();
        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown(false);
        $view_data['payment_method_dropdown'] = $this->Payment_methods_model->get_payment_methods_dropdown();
        return $this->template->rander("invoices/reports/yearly_payments_summary", $view_data);
    }

    function yearly_payment_summary_list_data() {
        if (!$this->can_view_invoices()) {
            app_redirect("forbidden");
        }

        //get the month name
        $month_array = array(" ", "january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");

        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        $options = array(
            "start_date" => $start_date,
            "end_date" => $end_date,
            "currency" => $this->request->getPost("currency"),
            "payment_method_id" => $this->request->getPost('payment_method_id')
        );

        $list_data = $this->Invoice_payments_model->get_yearly_summary_details($options)->getResult();

        $default_currency_symbol = get_setting("currency_symbol");

        $result = array();
        foreach ($list_data as $data) {
            $currency_symbol = $data->currency_symbol ? $data->currency_symbol : $default_currency_symbol;
            $month = get_array_value($month_array, $data->month);

            $result[] = array(
                app_lang($month),
                $data->payment_count,
                to_currency($data->amount, $currency_symbol)
            );
        }

        echo json_encode(array("data" => $result));
    }

    function clients_payment_summary() {
        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown(false);
        $view_data['payment_method_dropdown'] = $this->Payment_methods_model->get_payment_methods_dropdown();
        return $this->template->view("invoices/reports/clients_payment_summary", $view_data);
    }

    function clients_payment_summary_list_data() {
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        $options = array(
            "start_date" => $start_date,
            "end_date" => $end_date,
            "currency" => $this->request->getPost("currency"),
            "payment_method_id" => $this->request->getPost('payment_method_id')
        );

        $list_data = $this->Invoice_payments_model->get_clients_summary_details($options)->getResult();

        $default_currency_symbol = get_setting("currency_symbol");

        $result = array();
        foreach ($list_data as $data) {
            $currency_symbol = $data->currency_symbol ? $data->currency_symbol : $default_currency_symbol;

            $result[] = array(
                anchor(get_uri("clients/view/" . $data->client_id), $data->client_name ? $data->client_name : "-"),
                $data->payment_count,
                to_currency($data->amount, $currency_symbol)
            );
        }

        echo json_encode(array("data" => $result));
    }

    function get_invoice_payment_amount_suggestion($invoice_id) {
        validate_numeric_value($invoice_id);

        $invoice_total_summary = $this->Invoices_model->get_invoice_total_summary($invoice_id);
        if ($invoice_total_summary) {
            $invoice_total_summary->balance_due = $invoice_total_summary->balance_due ? to_decimal_format($invoice_total_summary->balance_due) : "";
            echo json_encode(array("success" => true, "invoice_total_summary" => $invoice_total_summary));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    /* list of invoice payments, prepared for datatable  */

    function payment_list_data_of_order($order_id, $client_id = 0, $is_mobile = 0) {
        if (!$this->can_view_invoices(0, $client_id)) {
            app_redirect("forbidden");
        }

        validate_numeric_value($order_id);
        validate_numeric_value($client_id);
        validate_numeric_value($is_mobile);

        $options = array("order_id" => $order_id,);
        $list_data = $this->Invoice_payments_model->get_details($options)->getResult();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data, $is_mobile);
        }
        echo json_encode(array("data" => $result));
    }

    /* Client wallet */

    function client_wallet($client_id) {
        $this->access_only_allowed_members();

        $view_type = $this->request->getPost('view_type');

        $view_data['client_id'] = $client_id;

        $Client_wallet_model = model("App\Models\Client_wallet_model");
        $client_wallet_summary = $Client_wallet_model->get_client_wallet_summary($client_id);
        $view_data["currency_symbol"] = $client_wallet_summary->currency_symbol ? $client_wallet_summary->currency_symbol : get_setting("currency_symbol");
        $view_data["client_wallet_summary"] = $client_wallet_summary;

        if ($view_type == "wallet_summary") {
            echo json_encode(array("success" => true, "wallet_summary" => $this->template->view("clients/client_wallet/client_wallet_info", $view_data)));
        } else {
            return $this->template->view("clients/client_wallet/index", $view_data);
        }
    }

    function add_client_wallet_amount_modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric|required"
        ));

        $client_id = $this->request->getPost('client_id');
        $view_data['model_info'] = $this->Client_wallet_model->get_one($this->request->getPost('id'));
        $view_data['client_id'] = $client_id;

        return $this->template->view('clients/client_wallet/modal_form', $view_data);
    }

    function client_wallet_list_data($client_id) {
        $this->access_only_allowed_members();

        $options = array(
            "client_id" => $client_id
        );

        $list_data = $this->Client_wallet_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_client_wallet_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    private function _make_client_wallet_row($data) {
        $created_by = "-";

        if ($data->created_by) {
            $image_url = get_avatar($data->created_by_avatar);
            $created_by_user = "<span class='avatar avatar-xs mr10'><img src='$image_url' alt='...'></span> $data->created_by_user";
            $created_by = get_team_member_profile_link($data->created_by, $created_by_user); // only team members can add 
        }

        return array(
            $data->payment_date,
            format_to_date($data->payment_date, false),
            $created_by,
            $data->note,
            to_currency($data->amount, $data->currency_symbol),
            modal_anchor(get_uri("invoice_payments/add_client_wallet_amount_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_payment'), "data-post-id" => $data->id, "data-post-client_id" => $data->client_id,))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("invoice_payments/delete_client_wallet_amount"), "data-action" => "delete-confirmation"))
        );
    }

    function delete_client_wallet_amount() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->Client_wallet_model->delete($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    private function _client_wallet_row_data($id) {
        $options = array("id" => $id);
        $data = $this->Client_wallet_model->get_details($options)->getRow();

        return $this->_make_client_wallet_row($data);
    }

    function save_client_wallet_amount() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "required|numeric",
            "client_wallet_amount" => "required",
            "client_wallet_payment_date" => "required",
        ));

        $id = $this->request->getPost('id');

        $client_wallet_amount_data = array(
            "client_id" => $this->request->getPost('client_id'),
            "note" => $this->request->getPost('client_wallet_note'),
            "amount" => unformat_currency($this->request->getPost('client_wallet_amount')),
            "payment_date" => $this->request->getPost('client_wallet_payment_date')
        );

        if (!$id) {
            $client_wallet_amount_data['created_at'] = get_current_utc_time();
            $client_wallet_amount_data['created_by'] = $this->login_user->id;
        }

        $client_wallet_amount_data = clean_data($client_wallet_amount_data);

        $save_id = $this->Client_wallet_model->ci_save($client_wallet_amount_data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_client_wallet_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function statement($client_id) {
        validate_numeric_value($client_id);
        $this->access_only_allowed_members();

        $view_data['client_id'] = $client_id;

        return $this->template->view("clients/statement/index", $view_data);
    }

    function statement_data($client_id) {
        validate_numeric_value($client_id);
        $this->access_only_allowed_members();

        $options = array(
            "start_date" => $this->request->getPost("start_date"),
            "end_date" => $this->request->getPost("end_date"),
            "client_id" => $client_id
        );

        $view_data = get_statement_making_data($options);
        echo prepare_statement_pdf($view_data, "html");
    }

    function download_pdf($client_id = 0, $mode = "download") {
        if ($client_id) {
            validate_numeric_value($client_id);
            $this->access_only_allowed_members();

            $options = array(
                "start_date" => $this->request->getGet("start_date"),
                "end_date" => $this->request->getGet("end_date"),
                "client_id" => $client_id
            );

            $statement_info = get_statement_making_data($options);
            prepare_statement_pdf($statement_info, $mode);
        } else {
            show_404();
        }
    }
}

/* End of file Invoice_payments.php */
/* Location: ./app/Controllers/Invoice_payments.php */