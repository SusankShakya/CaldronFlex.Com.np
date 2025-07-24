<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;

class Finishes extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->init_permission_checker("item");
        $this->Finishes_model = model('App\Models\Finishes_model');
    }

    /**
     * Load finishes list view
     */
    function index() {
        $this->check_module_availability("module_item");
        
        $view_data['can_create'] = $this->can_create_items();
        return $this->template->rander("finishes/index", $view_data);
    }

    /**
     * Load finish add/edit modal
     */
    function modal_form() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";
        
        $view_data['model_info'] = $this->Finishes_model->get_one($id);
        
        return $this->template->view('finishes/modal_form', $view_data);
    }

    /**
     * Save finish
     */
    function save() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        
        $id = $this->request->getPost('id');
        
        $data = array(
            "name" => $this->request->getPost('name'),
            "description" => $this->request->getPost('description'),
            "price_modifier" => unformat_currency($this->request->getPost('price_modifier')),
            "modifier_type" => $this->request->getPost('modifier_type'),
            "is_active" => $this->request->getPost('is_active') ? 1 : 0
        );
        
        $save_id = $this->Finishes_model->ci_save($data, $id);
        
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /**
     * Delete finish
     */
    function delete() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        if ($this->request->getPost('undo')) {
            if ($this->Finishes_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Finishes_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /**
     * Get finishes list data
     */
    function list_data() {
        $this->check_module_availability("module_item");
        
        $list_data = $this->Finishes_model->get_details()->getResult();
        $result = array();
        
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    /**
     * Get one row of finish
     */
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Finishes_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    /**
     * Make a row of finish
     */
    private function _make_row($data) {
        $modifier_display = $this->Finishes_model->get_price_modifier_display($data->price_modifier, $data->modifier_type);
        $status = $data->is_active ? '<span class="badge bg-success">' . app_lang('active') . '</span>' : '<span class="badge bg-danger">' . app_lang('inactive') . '</span>';
        
        $row_data = array(
            $data->name,
            $data->description,
            $modifier_display,
            $status
        );
        
        $row_data[] = modal_anchor(get_uri("finishes/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_finish'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("finishes/delete"), "data-action" => "delete"));
        
        return $row_data;
    }

    /**
     * Get finishes for dropdown
     */
    function get_finishes_dropdown() {
        $finishes = $this->Finishes_model->get_all_where(array("is_active" => 1, "deleted" => 0))->getResult();
        
        $finishes_dropdown = array();
        foreach ($finishes as $finish) {
            $finishes_dropdown[] = array(
                "id" => $finish->id,
                "text" => $finish->name,
                "price_modifier" => $finish->price_modifier,
                "modifier_type" => $finish->modifier_type
            );
        }
        
        echo json_encode($finishes_dropdown);
    }

    /**
     * Get finishes for an item
     */
    function get_item_finishes() {
        $item_id = $this->request->getPost('item_id');
        
        if (!$item_id) {
            echo json_encode(array("success" => false));
            return;
        }
        
        $finishes = $this->Finishes_model->get_item_finishes($item_id);
        
        echo json_encode(array("success" => true, "finishes" => $finishes));
    }

    /**
     * Save item finishes
     */
    function save_item_finishes() {
        $this->check_module_availability("module_item");
        
        $item_id = $this->request->getPost('item_id');
        $finish_ids = $this->request->getPost('finish_ids');
        
        if (!$item_id) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            return;
        }
        
        // Delete existing finishes
        $this->Finishes_model->delete_item_finishes($item_id);
        
        // Add new finishes
        if ($finish_ids && is_array($finish_ids)) {
            foreach ($finish_ids as $finish_id) {
                $this->Finishes_model->add_item_finish($item_id, $finish_id);
            }
        }
        
        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }
}