<?php
namespace App\Controllers;

use App\Models\Pricing_rules_model;
use App\Models\Items_model;

class Pricing_rules extends App_Controller {

    protected $pricing_rules_model;
    protected $items_model;

    public function __construct() {
        parent::__construct();
        $this->pricing_rules_model = new Pricing_rules_model();
        $this->items_model = new Items_model();
    }

    public function index() {
        return $this->template->rander("pricing_rules/index");
    }

    public function modal_form() {
        $id = $this->request->getPost('id');
        $model_info = $this->pricing_rules_model->get_one($id);

        $view_data['model_info'] = $model_info;
        $view_data['items_dropdown'] = $this->items_model->get_dropdown_list(array("title"), "id", array("where" => array("deleted" => 0)));

        return $this->template->view('pricing_rules/modal_form', $view_data);
    }

    public function save() {
        $id = $this->request->getPost('id');

        validate_submitted_data(array(
            "item_id" => "required|numeric",
            "rule_type" => "required"
        ));

        $data = array(
            "item_id" => $this->request->getPost('item_id'),
            "rule_type" => $this->request->getPost('rule_type'),
            "min_quantity" => $this->request->getPost('min_quantity') ? $this->request->getPost('min_quantity') : 0,
            "max_quantity" => $this->request->getPost('max_quantity') ? $this->request->getPost('max_quantity') : 0,
            "discount_type" => $this->request->getPost('discount_type'),
            "discount_amount" => $this->request->getPost('discount_amount') ? $this->request->getPost('discount_amount') : 0,
            "valid_from" => $this->request->getPost('valid_from'),
            "valid_until" => $this->request->getPost('valid_until'),
            "status" => $this->request->getPost('status') ? 1 : 0,
        );

        $save_id = $this->pricing_rules_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    public function delete() {
        $id = $this->request->getPost('id');
        if ($this->pricing_rules_model->delete($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    public function list_data() {
        $list_data = $this->pricing_rules_model->get_details()->getResult();
        $result_data = array();
        foreach ($list_data as $data) {
            $result_data[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result_data));
    }

    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->pricing_rules_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    private function _make_row($data) {
        $validity = $data->valid_from ? format_to_date($data->valid_from) . " to " . format_to_date($data->valid_until) : 'Always active';
        $status = $data->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';

        return array(
            $data->item_name,
            app_lang($data->rule_type),
            $data->min_quantity . " - " . $data->max_quantity,
            $data->discount_amount . ($data->discount_type == 'percentage' ? '%' : ' ' . to_currency(0)),
            $validity,
            $status,
            modal_anchor(get_uri("pricing_rules/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_pricing_rule'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_pricing_rule'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("pricing_rules/delete"), "data-action" => "delete-confirmation"))
        );
    }
}