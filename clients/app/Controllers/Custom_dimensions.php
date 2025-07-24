<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;

class Custom_dimensions extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->init_permission_checker("item");
        $this->Custom_dimensions_model = model('App\Models\Custom_dimensions_model');
        $this->Items_model = model('App\Models\Items_model');
    }

    /**
     * Load custom dimensions list view
     */
    function index() {
        $this->check_module_availability("module_item");
        
        $view_data['can_create'] = $this->can_create_items();
        return $this->template->rander("custom_dimensions/index", $view_data);
    }

    /**
     * Load custom dimension add/edit modal
     */
    function modal_form() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";
        
        $view_data['model_info'] = $this->Custom_dimensions_model->get_one($id);
        
        // Get items for dropdown
        $view_data['items_dropdown'] = $this->_get_items_dropdown();
        
        return $this->template->view('custom_dimensions/modal_form', $view_data);
    }

    /**
     * Save custom dimension
     */
    function save() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        
        $id = $this->request->getPost('id');
        
        $data = array(
            "item_id" => $this->request->getPost('item_id'),
            "width" => unformat_currency($this->request->getPost('width')),
            "height" => unformat_currency($this->request->getPost('height')),
            "unit" => $this->request->getPost('unit'),
            "min_width" => unformat_currency($this->request->getPost('min_width')),
            "max_width" => unformat_currency($this->request->getPost('max_width')),
            "min_height" => unformat_currency($this->request->getPost('min_height')),
            "max_height" => unformat_currency($this->request->getPost('max_height')),
            "price_per_unit" => unformat_currency($this->request->getPost('price_per_unit')),
            "is_active" => $this->request->getPost('is_active') ? 1 : 0
        );
        
        $save_id = $this->Custom_dimensions_model->ci_save($data, $id);
        
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /**
     * Delete custom dimension
     */
    function delete() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        if ($this->request->getPost('undo')) {
            if ($this->Custom_dimensions_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Custom_dimensions_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /**
     * Get custom dimensions list data
     */
    function list_data() {
        $this->check_module_availability("module_item");
        
        $list_data = $this->Custom_dimensions_model->get_details()->getResult();
        $result = array();
        
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    /**
     * Get one row of custom dimension
     */
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Custom_dimensions_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    /**
     * Make a row of custom dimension
     */
    private function _make_row($data) {
        $area = $this->Custom_dimensions_model->calculate_area($data->width, $data->height, $data->unit);
        $price = $this->Custom_dimensions_model->calculate_price($data->width, $data->height, $data->unit, $data->price_per_unit);
        
        $status = $data->is_active ? '<span class="badge bg-success">' . app_lang('active') . '</span>' : '<span class="badge bg-danger">' . app_lang('inactive') . '</span>';
        
        $row_data = array(
            $data->item_title,
            $data->width . " x " . $data->height . " " . $data->unit,
            number_format($area, 2) . " sq " . $data->unit,
            to_currency($price),
            $data->min_width . " - " . $data->max_width . " " . $data->unit,
            $data->min_height . " - " . $data->max_height . " " . $data->unit,
            $status
        );
        
        $row_data[] = modal_anchor(get_uri("custom_dimensions/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_custom_dimension'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("custom_dimensions/delete"), "data-action" => "delete"));
        
        return $row_data;
    }

    /**
     * Get items dropdown
     */
    private function _get_items_dropdown() {
        $items = $this->Items_model->get_all_where(array("deleted" => 0))->getResult();
        $items_dropdown = array("" => "-");
        
        foreach ($items as $item) {
            $items_dropdown[$item->id] = $item->title;
        }
        
        return $items_dropdown;
    }

    /**
     * Get custom dimensions for an item
     */
    function get_item_dimensions() {
        $item_id = $this->request->getPost('item_id');
        
        if (!$item_id) {
            echo json_encode(array("success" => false));
            return;
        }
        
        $dimensions = $this->Custom_dimensions_model->get_all_where(array(
            "item_id" => $item_id,
            "is_active" => 1,
            "deleted" => 0
        ))->getResult();
        
        echo json_encode(array("success" => true, "dimensions" => $dimensions));
    }

    /**
     * Calculate price based on dimensions
     */
    function calculate_price() {
        $dimension_id = $this->request->getPost('dimension_id');
        $width = unformat_currency($this->request->getPost('width'));
        $height = unformat_currency($this->request->getPost('height'));
        
        $dimension = $this->Custom_dimensions_model->get_one($dimension_id);
        
        if (!$dimension->id) {
            echo json_encode(array("success" => false));
            return;
        }
        
        // Validate dimensions
        $validation = $this->Custom_dimensions_model->validate_dimensions($width, $height, $dimension);
        
        if (!$validation['valid']) {
            echo json_encode(array("success" => false, "message" => $validation['message']));
            return;
        }
        
        $area = $this->Custom_dimensions_model->calculate_area($width, $height, $dimension->unit);
        $price = $this->Custom_dimensions_model->calculate_price($width, $height, $dimension->unit, $dimension->price_per_unit);
        
        echo json_encode(array(
            "success" => true,
            "area" => number_format($area, 2),
            "price" => to_currency($price),
            "price_value" => $price
        ));
    }
}