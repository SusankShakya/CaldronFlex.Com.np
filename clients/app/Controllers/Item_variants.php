<?php

namespace App\Controllers;

class Item_variants extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("order");
        $this->access_only_team_members();
        
        // Initialize models
        $this->Variants_model = model("App\Models\Variants_model");
        $this->Items_model = model("App\Models\Items_model");
    }

    protected function validate_access_to_variants() {
        $access_invoice = $this->get_access_info("invoice");
        $access_estimate = $this->get_access_info("estimate");

        //don't show the variants if invoice/estimate module is not enabled
        if (!(get_setting("module_invoice") == "1" || get_setting("module_estimate") == "1")) {
            app_redirect("forbidden");
        }

        if ($this->login_user->is_admin) {
            return true;
        } else if (
            $access_invoice->access_type === "all"
            || $access_estimate->access_type === "all"
        ) {
            return true;
        } else {
            app_redirect("forbidden");
        }
    }

    //load variants list view
    function index() {
        $this->validate_access_to_variants();

        $view_data['variant_types'] = $this->Variants_model->get_variant_types();
        $view_data['items_dropdown'] = $this->_get_items_dropdown();

        return $this->template->rander("item_variants/index", $view_data);
    }

    //get items dropdown
    private function _get_items_dropdown() {
        $items = $this->Items_model->get_all_where(array("deleted" => 0), 0, 0, "title")->getResult();

        $items_dropdown = array(array("id" => "", "text" => "- " . app_lang("select_item") . " -"));
        foreach ($items as $item) {
            $items_dropdown[] = array("id" => $item->id, "text" => $item->title);
        }

        return json_encode($items_dropdown);
    }

    /* load variant modal */
    function modal_form() {
        $this->validate_access_to_variants();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "item_id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $view_data['model_info'] = $this->Variants_model->get_one($id);
        
        if ($id) {
            $view_data['item_id'] = $view_data['model_info']->item_id;
        } else {
            $view_data['item_id'] = $this->request->getPost('item_id');
        }

        $view_data['items_dropdown'] = $this->Items_model->get_dropdown_list(array("title"));
        $view_data['variant_types'] = $this->Variants_model->get_variant_types();
        $view_data['price_modifier_types'] = $this->Variants_model->get_price_modifier_types();

        return $this->template->view('item_variants/modal_form', $view_data);
    }

    /* add or edit a variant */
    function save() {
        $this->validate_access_to_variants();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "item_id" => "required|numeric",
            "variant_type" => "required",
            "variant_name" => "required",
            "variant_value" => "required"
        ));

        $id = $this->request->getPost('id');
        $item_id = $this->request->getPost('item_id');

        $variant_data = array(
            "item_id" => $item_id,
            "variant_type" => $this->request->getPost('variant_type'),
            "variant_name" => $this->request->getPost('variant_name'),
            "variant_value" => $this->request->getPost('variant_value'),
            "price_modifier_type" => $this->request->getPost('price_modifier_type'),
            "price_modifier" => unformat_currency($this->request->getPost('price_modifier')),
            "sort_order" => $this->request->getPost('sort_order') ? $this->request->getPost('sort_order') : 0,
            "status" => $this->request->getPost('status') ? 1 : 0
        );

        if ($id) {
            // Track price changes
            $old_variant = $this->Variants_model->get_one($id);
            if ($old_variant->price_modifier != $variant_data['price_modifier']) {
                $this->Variants_model->save_price_history(array(
                    "variant_id" => $id,
                    "item_id" => $item_id,
                    "old_price" => $old_variant->price_modifier,
                    "new_price" => $variant_data['price_modifier'],
                    "change_reason" => $this->request->getPost('price_change_reason')
                ));
            }
        } else {
            $variant_data["created_by"] = $this->login_user->id;
            $variant_data["created_at"] = get_current_utc_time();
        }

        $save_id = $this->Variants_model->ci_save($variant_data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete a variant */
    function delete() {
        $this->validate_access_to_variants();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->Variants_model->delete($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* list of variants, prepared for datatable */
    function list_data() {
        $this->validate_access_to_variants();

        $item_id = $this->request->getPost('item_id');
        $variant_type = $this->request->getPost('variant_type');

        $options = array(
            "item_id" => $item_id,
            "variant_type" => $variant_type
        );

        $list_data = $this->Variants_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of variant list table */
    private function _make_row($data) {
        $variant_types = $this->Variants_model->get_variant_types();
        $variant_type_label = get_array_value($variant_types, $data->variant_type);

        $price_modifier = "";
        if ($data->price_modifier_type == "percentage") {
            $price_modifier = $data->price_modifier . "%";
        } else {
            $price_modifier = to_currency($data->price_modifier);
        }

        $status = "";
        if ($data->status == 1) {
            $status = "<span class='badge bg-success'>" . app_lang("active") . "</span>";
        } else {
            $status = "<span class='badge bg-danger'>" . app_lang("inactive") . "</span>";
        }

        return array(
            $data->item_title,
            $variant_type_label,
            $data->variant_name,
            $data->variant_value,
            $price_modifier,
            $data->sort_order,
            $status,
            modal_anchor(get_uri("item_variants/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_variant'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_variant'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("item_variants/delete"), "data-action" => "delete"))
        );
    }

    /* return a row of variant list table */
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Variants_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    /* load variant combinations view */
    function combinations($item_id = 0) {
        $this->validate_access_to_variants();
        
        if (!$item_id) {
            show_404();
        }

        $view_data['item_id'] = $item_id;
        $view_data['item_info'] = $this->Items_model->get_one($item_id);
        $view_data['variants'] = $this->Variants_model->get_variants_by_item($item_id);

        return $this->template->rander("item_variants/combinations", $view_data);
    }

    /* load combination modal */
    function combination_modal_form() {
        $this->validate_access_to_variants();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "item_id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $item_id = $this->request->getPost('item_id');

        $view_data['model_info'] = $id ? $this->Variants_model->get_combination_details($id) : "";
        $view_data['item_id'] = $item_id;
        $view_data['variants'] = $this->Variants_model->get_variants_by_item($item_id);

        return $this->template->view('item_variants/combination_modal_form', $view_data);
    }

    /* save variant combination */
    function save_combination() {
        $this->validate_access_to_variants();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "item_id" => "required|numeric",
            "sku" => "required"
        ));

        $id = $this->request->getPost('id');
        $item_id = $this->request->getPost('item_id');
        $sku = $this->request->getPost('sku');

        // Check if SKU already exists
        if ($this->Variants_model->check_sku_exists($sku, $id)) {
            echo json_encode(array("success" => false, 'message' => app_lang('sku_already_exists')));
            return;
        }

        $selected_variants = $this->request->getPost('variants');
        if (!$selected_variants || !is_array($selected_variants)) {
            echo json_encode(array("success" => false, 'message' => app_lang('please_select_variants')));
            return;
        }

        $combination_data = array(
            "item_id" => $item_id,
            "variant_combination" => json_encode($selected_variants),
            "sku" => $sku,
            "stock_quantity" => $this->request->getPost('stock_quantity') ? $this->request->getPost('stock_quantity') : 0,
            "additional_price" => unformat_currency($this->request->getPost('additional_price')),
            "status" => $this->request->getPost('status') ? 1 : 0
        );

        if ($id) {
            $combination_data['id'] = $id;
            
            // Track price changes
            $old_combination = $this->Variants_model->get_combination_details($id);
            if ($old_combination->additional_price != $combination_data['additional_price']) {
                $this->Variants_model->save_price_history(array(
                    "combination_id" => $id,
                    "item_id" => $item_id,
                    "old_price" => $old_combination->additional_price,
                    "new_price" => $combination_data['additional_price'],
                    "change_reason" => $this->request->getPost('price_change_reason')
                ));
            }
        }

        $save_id = $this->Variants_model->save_combination($combination_data);
        if ($save_id) {
            echo json_encode(array("success" => true, 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete combination */
    function delete_combination() {
        $this->validate_access_to_variants();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->Variants_model->delete_combination($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* list of combinations */
    function combinations_list_data() {
        $this->validate_access_to_variants();

        $item_id = $this->request->getPost('item_id');
        
        $combinations = $this->Variants_model->get_variant_combinations($item_id)->getResult();
        $variants = $this->Variants_model->get_variants_by_item($item_id);
        
        $result = array();
        foreach ($combinations as $combination) {
            $result[] = $this->_make_combination_row($combination, $variants);
        }
        
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of combination list table */
    private function _make_combination_row($data, $all_variants) {
        $variant_ids = json_decode($data->variant_combination);
        $variant_names = array();
        
        foreach ($variant_ids as $variant_id) {
            foreach ($all_variants as $type => $variants) {
                foreach ($variants as $variant) {
                    if ($variant->id == $variant_id) {
                        $variant_names[] = $variant->variant_name . ": " . $variant->variant_value;
                    }
                }
            }
        }

        $status = "";
        if ($data->status == 1) {
            $status = "<span class='badge bg-success'>" . app_lang("active") . "</span>";
        } else {
            $status = "<span class='badge bg-danger'>" . app_lang("inactive") . "</span>";
        }

        return array(
            $data->sku,
            implode(", ", $variant_names),
            to_currency($data->additional_price),
            $data->stock_quantity,
            $status,
            modal_anchor(get_uri("item_variants/combination_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_combination'), "data-post-id" => $data->id, "data-post-item_id" => $data->item_id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_combination'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("item_variants/delete_combination"), "data-action" => "delete"))
        );
    }

    /* get variant selector for store frontend */
    function get_variant_selector() {
        $item_id = $this->request->getPost('item_id');
        
        if (!$item_id) {
            echo json_encode(array("success" => false));
            return;
        }

        $view_data['item_id'] = $item_id;
        $view_data['variants'] = $this->Variants_model->get_variants_by_item($item_id);
        
        $html = $this->template->view('item_variants/variant_selector', $view_data);
        echo json_encode(array("success" => true, "html" => $html));
    }

    /* calculate price with selected variants */
    function calculate_price() {
        $item_id = $this->request->getPost('item_id');
        $variant_ids = $this->request->getPost('variant_ids');
        
        if (!$item_id) {
            echo json_encode(array("success" => false));
            return;
        }

        $price = $this->Items_model->calculate_item_price_with_variants($item_id, $variant_ids);
        $stock = $this->Items_model->get_item_stock_with_variants($item_id, $variant_ids);
        
        echo json_encode(array(
            "success" => true, 
            "price" => to_currency($price),
            "price_value" => $price,
            "stock" => $stock,
            "in_stock" => $stock > 0
        ));
    }
}