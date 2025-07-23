<?php

namespace App\Controllers;

class E_invoice_templates extends Security_Controller {

    protected $E_invoice_templates_model;

    function __construct() {
        parent::__construct();
        $this->E_invoice_templates_model = model("App\Models\E_invoice_templates_model");
    }

    //load the e-Invoice Template view
    function index() {
        $this->access_only_admin_or_settings_admin();
        return $this->template->view("e_invoice_templates/index");
    }

    function get_available_e_invoice_variables() {
        $company_template_variables = $this->Custom_fields_model->get_email_template_variables_array("companies", 0, $this->login_user->is_admin, $this->login_user->user_type);
        $client_template_variables = $this->Custom_fields_model->get_email_template_variables_array("clients", 0, $this->login_user->is_admin, $this->login_user->user_type);
        $item_template_variables = $this->Custom_fields_model->get_email_template_variables_array("items", 0, $this->login_user->is_admin, $this->login_user->user_type);
        $invoice_template_variables = $this->Custom_fields_model->get_email_template_variables_array("invoices", 0, $this->login_user->is_admin, $this->login_user->user_type);

        sort($invoice_template_variables);
        sort($company_template_variables);
        sort($client_template_variables);
        sort($item_template_variables);

        $invoice_variables = array(
            "CURRENCY_CODE",
            "INVOICE_ID",
            "INVOICE_NUMBER",
            "INVOICE_BILL_DATE",
            "INVOICE_DUE_DATE",
            "TAX1_AMOUNT",
            "TAX1_PERCENT",
            "TAX1_CATEGORY_ID",
            "TAX2_AMOUNT",
            "TAX2_PERCENT",
            "TAX2_CATEGORY_ID",
            "TAX_TOTAL_AMOUNT",

            "TDS_AMOUNT",
            "TDS_PERCENT",

            "INVOICE_SUBTOTAL",
            "INVOICE_TAXABLE_SUBTOTAL",
            "INVOICE_NON_TAXABLE_SUBTOTAL",

            "INVOICE_DISCOUNT_TOTAL",
            "INVOICE_TAXABLE_ITEM_DISCOUNT",
            "INVOICE_NON_TAXABLE_ITEM_DISCOUNT",

            "INVOICE_TOTAL_BEFORE_TAX",
            "INVOICE_TOTAL",
            "INVOICE_BALANCE_DUE",
            "BATCH_IDENTIFIER",
        );

        $invoice_lines = array(
            "INVOICE_LINE_SERIAL",
            "INVOICE_LINE_ITEM_ID",
            "INVOICE_LINE_TITLE",
            "INVOICE_LINE_DESCRIPTION",
            "INVOICE_LINE_QUANTITY",
            "INVOICE_LINE_UNIT_TYPE",
            "INVOICE_LINE_RATE",
            "INVOICE_LINE_TOTAL",
            "INVOICE_LINE_TAX1_CATEGORY_ID",
            "INVOICE_LINE_TAX1_PERCENT",
            "INVOICE_LINE_TAX2_CATEGORY_ID",
            "INVOICE_LINE_TAX2_PERCENT",
            "INVOICE_LINE_TAX_TOTAL"
        );

        $invoice_lines = array_merge($invoice_lines, $item_template_variables);

        return array(
            "invoice" => array_merge($invoice_variables, $invoice_template_variables),
            "company_or_supplier_or_seller" => array_merge(
                array(
                    "COMPANY_NAME",
                    "COMPANY_ADDRESS",
                    "COMPANY_PHONE",
                    "COMPANY_EMAIL",
                    "COMPANY_GST_NUMBER",
                    "COMPANY_VAT_NUMBER"
                ),
                $company_template_variables
            ),
            "client_or_customer_or_buyer" => array_merge(
                array(
                    "CLIENT_ID",
                    "CLIENT_NAME",
                    "CLIENT_ADDRESS",
                    "CLIENT_CITY",
                    "CLIENT_STATE",
                    "CLIENT_ZIP",
                    "CLIENT_COUNTRY_CODE_ALPHA_2",
                    "CLIENT_COUNTRY_CODE_ALPHA_3",
                    "CLIENT_GST_NUMBER",
                    "CLIENT_VAT_NUMBER"

                ),
                $client_template_variables
            ),
            "invoice_items" => array(
                "INVOICE_LINES" => $invoice_lines,
                "/INVOICE_LINES"
            )
        );
    }

    //load the e-Invoice Template add/edit modal
    function modal_form() {
        $this->access_only_admin_or_settings_admin();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->E_invoice_templates_model->get_one($this->request->getPost('id'));
        $view_data['available_variables'] = $this->get_available_e_invoice_variables();
        $view_data['e_invoice_templates_dropdown'] = array("" => "-") + $this->E_invoice_templates_model->get_dropdown_list(array("title"), "id");
        return $this->template->view('e_invoice_templates/modal_form', $view_data);
    }

    function save() {
        $this->access_only_admin_or_settings_admin();
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "template" => "required"
        ));

        $id = $this->request->getPost('id');

        $data = array(
            "title" => $this->request->getPost('title'),
            "template" => $this->request->getPost('template')
        );

        $save_id = $this->E_invoice_templates_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //delete or undo a e-Invoice Template
    function delete() {
        $this->access_only_admin_or_settings_admin();
        $this->validate_submitted_data(array(
            "id" => "numeric|required"
        ));

        $id = $this->request->getPost('id');
        if (get_setting("default_e_invoice_template") == $id || get_setting("default_e_invoice_template_for_credit_note") == $id) {
            app_redirect("forbidden");
        }

        if ($this->request->getPost('undo')) {
            if ($this->E_invoice_templates_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->E_invoice_templates_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    //get e-Invoice Template list data
    function list_data() {
        $this->access_only_admin_or_settings_admin();

        $list_data = $this->E_invoice_templates_model->get_details()->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of e-Invoice Template list
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->E_invoice_templates_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    //make a row of e-Invoice Template list table
    private function _make_row($data) {
        $delete = "";

        if (get_setting("default_e_invoice_template") !== $data->id && get_setting("default_e_invoice_template_for_credit_note") !== $data->id) {
            $delete = js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_e_invoice_template'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("e_invoice_templates/delete"), "data-action" => "delete"));
        }

        return array(
            $data->title,
            modal_anchor(get_uri("e_invoice_templates/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "", "title" => app_lang('edit_e_invoice_template'), "data-post-id" => $data->id, "data-modal-fullscreen" => 1))
                . $delete
        );
    }
}

/* End of file E_invoice_templates.php */
/* Location: ./app/controllers/E_invoice_templates.php */