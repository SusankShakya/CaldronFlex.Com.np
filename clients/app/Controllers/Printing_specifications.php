<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;

class Printing_specifications extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->init_permission_checker("item");
        $this->Printing_specifications_model = model('App\Models\Printing_specifications_model');
        $this->Items_model = model('App\Models\Items_model');
    }

    /**
     * Load printing specifications list view
     */
    function index() {
        $this->check_module_availability("module_item");
        
        $view_data['can_create'] = $this->can_create_items();
        return $this->template->rander("printing_specifications/index", $view_data);
    }

    /**
     * Load printing specification add/edit modal
     */
    function modal_form() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";
        
        $view_data['model_info'] = $this->Printing_specifications_model->get_one($id);
        
        // Get items for dropdown
        $view_data['items_dropdown'] = $this->_get_items_dropdown();
        
        return $this->template->view('printing_specifications/modal_form', $view_data);
    }

    /**
     * Save printing specification
     */
    function save() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        
        $id = $this->request->getPost('id');
        
        $data = array(
            "item_id" => $this->request->getPost('item_id'),
            "print_method" => $this->request->getPost('print_method'),
            "color_mode" => $this->request->getPost('color_mode'),
            "resolution_dpi" => $this->request->getPost('resolution_dpi'),
            "max_colors" => $this->request->getPost('max_colors'),
            "setup_cost" => unformat_currency($this->request->getPost('setup_cost')),
            "per_unit_cost" => unformat_currency($this->request->getPost('per_unit_cost')),
            "min_quantity" => $this->request->getPost('min_quantity'),
            "production_time_days" => $this->request->getPost('production_time_days'),
            "is_active" => $this->request->getPost('is_active') ? 1 : 0
        );
        
        $save_id = $this->Printing_specifications_model->ci_save($data, $id);
        
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /**
     * Delete printing specification
     */
    function delete() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        if ($this->request->getPost('undo')) {
            if ($this->Printing_specifications_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Printing_specifications_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /**
     * Get printing specifications list data
     */
    function list_data() {
        $this->check_module_availability("module_item");
        
        $list_data = $this->Printing_specifications_model->get_details()->getResult();
        $result = array();
        
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    /**
     * Get one row of printing specification
     */
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Printing_specifications_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    /**
     * Make a row of printing specification
     */
    private function _make_row($data) {
        $status = $data->is_active ? '<span class="badge bg-success">' . app_lang('active') . '</span>' : '<span class="badge bg-danger">' . app_lang('inactive') . '</span>';
        
        $row_data = array(
            $data->item_title,
            $data->print_method,
            $data->color_mode,
            $data->resolution_dpi . " DPI",
            $data->max_colors,
            to_currency($data->setup_cost),
            to_currency($data->per_unit_cost),
            $data->min_quantity,
            $data->production_time_days . " " . app_lang('days'),
            $status
        );
        
        $row_data[] = modal_anchor(get_uri("printing_specifications/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_printing_specification'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("printing_specifications/delete"), "data-action" => "delete"));
        
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
     * Get printing specifications for an item
     */
    function get_item_specifications() {
        $item_id = $this->request->getPost('item_id');
        
        if (!$item_id) {
            echo json_encode(array("success" => false));
            return;
        }
        
        $specifications = $this->Printing_specifications_model->get_all_where(array(
            "item_id" => $item_id,
            "is_active" => 1,
            "deleted" => 0
        ))->getResult();
        
        echo json_encode(array("success" => true, "specifications" => $specifications));
    }

    /**
     * Calculate printing cost
     */
    function calculate_cost() {
        $specification_id = $this->request->getPost('specification_id');
        $quantity = $this->request->getPost('quantity');
        $colors = $this->request->getPost('colors');
        
        $specification = $this->Printing_specifications_model->get_one($specification_id);
        
        if (!$specification->id) {
            echo json_encode(array("success" => false));
            return;
        }
        
        // Validate specification
        $validation = $this->Printing_specifications_model->validate_specification($specification, $quantity, $colors);
        
        if (!$validation['valid']) {
            echo json_encode(array("success" => false, "message" => $validation['message']));
            return;
        }
        
        $cost = $this->Printing_specifications_model->calculate_cost($specification, $quantity);
        
        echo json_encode(array(
            "success" => true,
            "setup_cost" => to_currency($cost['setup_cost']),
            "unit_cost" => to_currency($cost['unit_cost']),
            "total_cost" => to_currency($cost['total_cost']),
            "cost_value" => $cost['total_cost']
        ));
    }

    /**
     * Get print methods dropdown
     */
    function get_print_methods() {
        $methods = $this->Printing_specifications_model->get_print_methods();
        echo json_encode($methods);
    }

    /**
     * Get color modes dropdown
     */
    function get_color_modes() {
        $modes = $this->Printing_specifications_model->get_color_modes();
        echo json_encode($modes);
    }
}