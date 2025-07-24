<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;

class Materials extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->init_permission_checker("item");
        $this->Materials_model = model('App\Models\Materials_model');
    }

    /**
     * Load materials list view
     */
    function index() {
        $this->check_module_availability("module_item");
        
        $view_data['can_create'] = $this->can_create_items();
        return $this->template->rander("materials/index", $view_data);
    }

    /**
     * Load material add/edit modal
     */
    function modal_form() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";
        
        $view_data['model_info'] = $this->Materials_model->get_one($id);
        
        return $this->template->view('materials/modal_form', $view_data);
    }

    /**
     * Save material
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
        
        $save_id = $this->Materials_model->ci_save($data, $id);
        
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /**
     * Delete material
     */
    function delete() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        if ($this->request->getPost('undo')) {
            if ($this->Materials_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Materials_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /**
     * Get materials list data
     */
    function list_data() {
        $this->check_module_availability("module_item");
        
        $list_data = $this->Materials_model->get_details()->getResult();
        $result = array();
        
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    /**
     * Get one row of material
     */
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Materials_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    /**
     * Make a row of material
     */
    private function _make_row($data) {
        $modifier_display = $this->Materials_model->get_price_modifier_display($data->price_modifier, $data->modifier_type);
        $status = $data->is_active ? '<span class="badge bg-success">' . app_lang('active') . '</span>' : '<span class="badge bg-danger">' . app_lang('inactive') . '</span>';
        
        $row_data = array(
            $data->name,
            $data->description,
            $modifier_display,
            $status
        );
        
        $row_data[] = modal_anchor(get_uri("materials/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_material'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("materials/delete"), "data-action" => "delete"));
        
        return $row_data;
    }

    /**
     * Get materials for dropdown
     */
    function get_materials_dropdown() {
        $materials = $this->Materials_model->get_all_where(array("is_active" => 1, "deleted" => 0))->getResult();
        
        $materials_dropdown = array();
        foreach ($materials as $material) {
            $materials_dropdown[] = array(
                "id" => $material->id,
                "text" => $material->name,
                "price_modifier" => $material->price_modifier,
                "modifier_type" => $material->modifier_type
            );
        }
        
        echo json_encode($materials_dropdown);
    }

    /**
     * Get materials for an item
     */
    function get_item_materials() {
        $item_id = $this->request->getPost('item_id');
        
        if (!$item_id) {
            echo json_encode(array("success" => false));
            return;
        }
        
        $materials = $this->Materials_model->get_item_materials($item_id);
        
        echo json_encode(array("success" => true, "materials" => $materials));
    }

    /**
     * Save item materials
     */
    function save_item_materials() {
        $this->check_module_availability("module_item");
        
        $item_id = $this->request->getPost('item_id');
        $material_ids = $this->request->getPost('material_ids');
        
        if (!$item_id) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            return;
        }
        
        // Delete existing materials
        $this->Materials_model->delete_item_materials($item_id);
        
        // Add new materials
        if ($material_ids && is_array($material_ids)) {
            foreach ($material_ids as $material_id) {
                $this->Materials_model->add_item_material($item_id, $material_id);
            }
        }
        
        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }
}