<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;

class Inventory extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->init_permission_checker("item");
        $this->Inventory_model = model('App\Models\Inventory_model');
        $this->Warehouses_model = model('App\Models\Warehouses_model');
        $this->Items_model = model('App\Models\Items_model');
    }

    /**
     * Load inventory list view
     */
    function index() {
        $this->check_module_availability("module_item");
        
        $view_data['can_create'] = $this->can_create_items();
        $view_data['warehouses'] = $this->Warehouses_model->get_all_where(array("deleted" => 0))->getResult();
        return $this->template->rander("inventory/index", $view_data);
    }

    /**
     * Load inventory add/edit modal
     */
    function modal_form() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";
        
        $view_data['model_info'] = $this->Inventory_model->get_one($id);
        
        // Get dropdowns
        $view_data['items_dropdown'] = $this->_get_items_dropdown();
        $view_data['warehouses_dropdown'] = $this->_get_warehouses_dropdown();
        
        return $this->template->view('inventory/modal_form', $view_data);
    }

    /**
     * Save inventory
     */
    function save() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        
        $id = $this->request->getPost('id');
        
        $data = array(
            "item_id" => $this->request->getPost('item_id'),
            "warehouse_id" => $this->request->getPost('warehouse_id'),
            "quantity" => $this->request->getPost('quantity'),
            "reserved_quantity" => $this->request->getPost('reserved_quantity') ?: 0,
            "reorder_level" => $this->request->getPost('reorder_level'),
            "reorder_quantity" => $this->request->getPost('reorder_quantity')
        );
        
        $save_id = $this->Inventory_model->ci_save($data, $id);
        
        if ($save_id) {
            // Check for low stock
            $this->Inventory_model->check_low_stock($save_id);
            
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /**
     * Delete inventory
     */
    function delete() {
        $this->check_module_availability("module_item");
        
        validate_numeric_value($this->request->getPost('id'));
        $id = $this->request->getPost('id');
        
        if ($this->request->getPost('undo')) {
            if ($this->Inventory_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Inventory_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /**
     * Get inventory list data
     */
    function list_data() {
        $this->check_module_availability("module_item");
        
        $warehouse_id = $this->request->getPost('warehouse_id');
        
        $options = array();
        if ($warehouse_id) {
            $options["warehouse_id"] = $warehouse_id;
        }
        
        $list_data = $this->Inventory_model->get_details($options)->getResult();
        $result = array();
        
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    /**
     * Get one row of inventory
     */
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Inventory_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    /**
     * Make a row of inventory
     */
    private function _make_row($data) {
        $available = $this->Inventory_model->get_available_quantity($data->id);
        $status = $this->Inventory_model->get_stock_status($data->id);
        
        $status_badge = "";
        if ($status == "out_of_stock") {
            $status_badge = '<span class="badge bg-danger">' . app_lang('out_of_stock') . '</span>';
        } else if ($status == "low_stock") {
            $status_badge = '<span class="badge bg-warning">' . app_lang('low_stock') . '</span>';
        } else {
            $status_badge = '<span class="badge bg-success">' . app_lang('in_stock') . '</span>';
        }
        
        $row_data = array(
            $data->item_title,
            $data->warehouse_name,
            $data->quantity,
            $data->reserved_quantity,
            $available,
            $data->reorder_level,
            $data->reorder_quantity,
            $status_badge
        );
        
        $row_data[] = modal_anchor(get_uri("inventory/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_inventory'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("inventory/delete"), "data-action" => "delete"));
        
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
     * Get warehouses dropdown
     */
    private function _get_warehouses_dropdown() {
        $warehouses = $this->Warehouses_model->get_all_where(array("deleted" => 0))->getResult();
        $warehouses_dropdown = array("" => "-");
        
        foreach ($warehouses as $warehouse) {
            $warehouses_dropdown[$warehouse->id] = $warehouse->name;
        }
        
        return $warehouses_dropdown;
    }

    /**
     * Stock adjustment modal
     */
    function stock_adjustment_modal() {
        $this->check_module_availability("module_item");
        
        $inventory_id = $this->request->getPost('inventory_id');
        
        $view_data['inventory_info'] = $this->Inventory_model->get_details(array("id" => $inventory_id))->getRow();
        $view_data['adjustment_types'] = array(
            "add" => app_lang('add_stock'),
            "remove" => app_lang('remove_stock'),
            "damage" => app_lang('damage'),
            "return" => app_lang('return')
        );
        
        return $this->template->view('inventory/stock_adjustment_modal', $view_data);
    }

    /**
     * Save stock adjustment
     */
    function save_stock_adjustment() {
        $this->check_module_availability("module_item");
        
        $inventory_id = $this->request->getPost('inventory_id');
        $adjustment_type = $this->request->getPost('adjustment_type');
        $quantity = $this->request->getPost('quantity');
        $notes = $this->request->getPost('notes');
        
        $result = $this->Inventory_model->adjust_stock($inventory_id, $adjustment_type, $quantity, $notes, $this->login_user->id);
        
        if ($result) {
            echo json_encode(array("success" => true, 'message' => app_lang('stock_adjusted_successfully')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /**
     * Stock transfer modal
     */
    function stock_transfer_modal() {
        $this->check_module_availability("module_item");
        
        $view_data['warehouses_dropdown'] = $this->_get_warehouses_dropdown();
        $view_data['items_dropdown'] = $this->_get_items_dropdown();
        
        return $this->template->view('inventory/stock_transfer_modal', $view_data);
    }

    /**
     * Save stock transfer
     */
    function save_stock_transfer() {
        $this->check_module_availability("module_item");
        
        $item_id = $this->request->getPost('item_id');
        $from_warehouse_id = $this->request->getPost('from_warehouse_id');
        $to_warehouse_id = $this->request->getPost('to_warehouse_id');
        $quantity = $this->request->getPost('quantity');
        $notes = $this->request->getPost('notes');
        
        $result = $this->Inventory_model->transfer_stock($item_id, $from_warehouse_id, $to_warehouse_id, $quantity, $notes, $this->login_user->id);
        
        if ($result['success']) {
            echo json_encode(array("success" => true, 'message' => app_lang('stock_transferred_successfully')));
        } else {
            echo json_encode(array("success" => false, 'message' => $result['message']));
        }
    }

    /**
     * View transaction history
     */
    function transactions($inventory_id = 0) {
        $this->check_module_availability("module_item");
        
        if (!$inventory_id) {
            show_404();
        }
        
        $view_data['inventory_info'] = $this->Inventory_model->get_details(array("id" => $inventory_id))->getRow();
        
        return $this->template->rander("inventory/transactions", $view_data);
    }

    /**
     * Get transaction list data
     */
    function transaction_list_data($inventory_id = 0) {
        $this->check_module_availability("module_item");
        
        $list_data = $this->Inventory_model->get_transactions($inventory_id);
        $result = array();
        
        foreach ($list_data as $data) {
            $result[] = $this->_make_transaction_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    /**
     * Make a transaction row
     */
    private function _make_transaction_row($data) {
        $type_label = "";
        if ($data->transaction_type == "add") {
            $type_label = '<span class="badge bg-success">' . app_lang('added') . '</span>';
        } else if ($data->transaction_type == "remove") {
            $type_label = '<span class="badge bg-danger">' . app_lang('removed') . '</span>';
        } else if ($data->transaction_type == "transfer_in") {
            $type_label = '<span class="badge bg-info">' . app_lang('transfer_in') . '</span>';
        } else if ($data->transaction_type == "transfer_out") {
            $type_label = '<span class="badge bg-warning">' . app_lang('transfer_out') . '</span>';
        }
        
        return array(
            format_to_datetime($data->created_at),
            $type_label,
            $data->quantity,
            $data->reference_type,
            $data->reference_id,
            $data->notes,
            get_team_member_profile_link($data->created_by, $data->created_by_user)
        );
    }

    /**
     * Low stock alerts
     */
    function alerts() {
        $this->check_module_availability("module_item");
        
        $view_data['can_create'] = $this->can_create_items();
        
        return $this->template->rander("inventory/alerts", $view_data);
    }

    /**
     * Get alerts list data
     */
    function alerts_list_data() {
        $this->check_module_availability("module_item");
        
        $list_data = $this->Inventory_model->get_low_stock_items();
        $result = array();
        
        foreach ($list_data as $data) {
            $result[] = $this->_make_alert_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    /**
     * Make an alert row
     */
    private function _make_alert_row($data) {
        $available = $this->Inventory_model->get_available_quantity($data->id);
        
        $urgency = "";
        if ($available <= 0) {
            $urgency = '<span class="badge bg-danger">' . app_lang('critical') . '</span>';
        } else if ($available < $data->reorder_level / 2) {
            $urgency = '<span class="badge bg-warning">' . app_lang('urgent') . '</span>';
        } else {
            $urgency = '<span class="badge bg-info">' . app_lang('low') . '</span>';
        }
        
        return array(
            $data->item_title,
            $data->warehouse_name,
            $available,
            $data->reorder_level,
            $data->reorder_quantity,
            $urgency,
            modal_anchor(get_uri("inventory/stock_adjustment_modal"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_stock'), array("class" => "btn btn-primary btn-sm", "data-post-inventory_id" => $data->id))
        );
    }
}