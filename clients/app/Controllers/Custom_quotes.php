<?php
namespace App\Controllers;

use App\Models\Custom_quotes_model;
use App\Models\Clients_model;
use App\Models\Items_model;
use App\Services\Price_calculator_service;

class Custom_quotes extends App_Controller {

    protected $custom_quotes_model;
    protected $price_calculator_service;

    public function __construct() {
        parent::__construct();
        $this->custom_quotes_model = new Custom_quotes_model();
        $this->price_calculator_service = new Price_calculator_service();
    }

    public function index() {
        return $this->template->rander("custom_quotes/index");
    }

    public function modal_form() {
        $id = $this->request->getPost('id');
        $model_info = $this->custom_quotes_model->get_one($id);

        $clients_model = new Clients_model();
        $items_model = new Items_model();

        $view_data['model_info'] = $model_info;
        $view_data['clients_dropdown'] = $clients_model->get_dropdown_list(array("company_name"));
        $view_data['items_dropdown'] = $items_model->get_dropdown_list(array("title"));
        
        return $this->template->view('custom_quotes/modal_form', $view_data);
    }
    
    public function save() {
        $id = $this->request->getPost('id');

        validate_submitted_data(array(
            "client_id" => "required|numeric",
            "item_id" => "required|numeric"
        ));

        $quote_data = array(
            'client_id' => $this->request->getPost('client_id'),
            'item_id' => $this->request->getPost('item_id'),
            'quantity' => $this->request->getPost('quantity'),
            'dimensions' => json_decode($this->request->getPost('dimensions'), true),
            'variant_combination_id' => $this->request->getPost('variant_combination_id'),
            'valid_until' => $this->request->getPost('valid_until'),
            'notes' => $this->request->getPost('notes'),
            'item_description' => $this->request->getPost('item_description')
        );

        $result = $this->price_calculator_service->create_custom_quote($quote_data);

        if ($result['success']) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($result['quote_id']), 'id' => $result['quote_id'], 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => $result['error']));
        }
    }

    public function delete() {
        $id = $this->request->getPost('id');
        if ($this->custom_quotes_model->delete($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    public function list_data() {
        $list_data = $this->custom_quotes_model->get_details()->getResult();
        $result_data = array();
        foreach ($list_data as $data) {
            $result_data[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result_data));
    }

    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->custom_quotes_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    private function _make_row($data) {
        return array(
            $data->id,
            $data->client_company,
            $data->item_title,
            $data->quantity,
            to_currency($data->total_amount),
            $data->quote_status,
            format_to_date($data->valid_until),
            modal_anchor(get_uri("custom_quotes/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_quote'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_quote'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("custom_quotes/delete"), "data-action" => "delete-confirmation"))
        );
    }
}