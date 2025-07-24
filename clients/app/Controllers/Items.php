<?php

namespace App\Controllers;

use App\Libraries\Excel_import;
use App\Services\PriceCalculatorService;

class Items extends Security_Controller {

    use Excel_import;

    private $categories_id_by_title = array();
    protected $price_calculator_service;

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("order");
        $this->price_calculator_service = new PriceCalculatorService();
    }

    protected function validate_access_to_items() {
        $access_invoice = $this->get_access_info("invoice");
        $access_estimate = $this->get_access_info("estimate");

        //don't show the items if invoice/estimate module is not enabled
        if (!(get_setting("module_invoice") == "1" || get_setting("module_estimate") == "1")) {
            app_redirect("forbidden");
        }

        if ($this->login_user->is_admin) {
            return true;
        } else if (
            $access_invoice->access_type === "all"
            || $access_invoice->access_type === "manage_own_client_invoices"
            || $access_invoice->access_type === "manage_own_client_invoices_except_delete"
            || $access_invoice->access_type === "manage_only_own_created_invoices"
            || $access_invoice->access_type === "manage_only_own_created_invoices_except_delete"
            || $access_estimate->access_type === "all"
            || $access_estimate->access_type === "own"
        ) {
            return true;
        } else {
            app_redirect("forbidden");
        }
    }

    //load items list view
    function index() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $view_data['categories_dropdown'] = $this->_get_categories_dropdown();

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("items", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("items", $this->login_user->is_admin, $this->login_user->user_type);

        return $this->template->rander("items/index", $view_data);
    }

    //get categories dropdown
    private function _get_categories_dropdown() {
        $categories = $this->Item_categories_model->get_all_where(array("deleted" => 0), 0, 0, "title")->getResult();

        $categories_dropdown = array(array("id" => "", "text" => "- " . app_lang("category") . " -"));
        foreach ($categories as $category) {
            $categories_dropdown[] = array("id" => $category->id, "text" => $category->title);
        }

        return json_encode($categories_dropdown);
    }

    /* load item modal */

    function modal_form() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Items_model->get_one($this->request->getPost('id'));
        $view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("items", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        return $this->template->view('items/modal_form', $view_data);
    }

    /* add or edit an item */

    function save() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "category_id" => "required",
        ));

        $id = $this->request->getPost('id');

        $item_data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "category_id" => $this->request->getPost('category_id'),
            "unit_type" => $this->request->getPost('unit_type'),
            "rate" => unformat_currency($this->request->getPost('item_rate')),
            "show_in_client_portal" => $this->request->getPost('show_in_client_portal') ? $this->request->getPost('show_in_client_portal') : "",
            "taxable" => ""
        );

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "item");
        $new_files = unserialize($files_data);

        if ($id) {
            $item_info = $this->Items_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $item_info->files, $new_files);
        }

        $item_data["files"] = serialize($new_files);

        $item_id = $this->Items_model->ci_save($item_data, $id);
        if ($item_id) {
            save_custom_fields("items", $item_id, $this->login_user->is_admin, $this->login_user->user_type);

            echo json_encode(array("success" => true, "id" => $item_id, "data" => $this->_item_row_data($item_id), 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete or undo an item */

    function delete() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Items_model->delete($id, true)) {
                echo json_encode(array("success" => true, "id" => $id, "data" => $this->_item_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Items_model->delete($id)) {
                $item_info = $this->Items_model->get_one($id);
                echo json_encode(array("success" => true, "id" => $item_info->id, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of items, prepared for datatable  */

    function list_data() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("items", $this->login_user->is_admin, $this->login_user->user_type);

        $category_id = $this->request->getPost('category_id');
        $options = array(
            "category_id" => $category_id,
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("items", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $list_data = $this->Items_model->get_details($options)->getResult();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* return a row of item list table */

    private function _item_row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("items", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Items_model->get_details($options)->getRow();
        return $this->_make_item_row($data, $custom_fields);
    }

    /* prepare a row of item list table */

    private function _make_item_row($data, $custom_fields) {
        $type = $data->unit_type ? $data->unit_type : "";

        $show_in_client_portal_icon = "";
        if ($data->show_in_client_portal && get_setting("module_order")) {
            $show_in_client_portal_icon = "<span title='" . app_lang("showing_in_client_portal") . "'><i data-feather='shopping-bag' class='icon-16'></i></span> ";
        }

        $row_data =  array(
            modal_anchor(get_uri("items/view"), $show_in_client_portal_icon . $data->title, array("title" => app_lang("item_details"), "data-post-id" => $data->id)),
            custom_nl2br($data->description ? $data->description : ""),
            $data->category_title ? $data->category_title : "-",
            $type,
            to_decimal_format($data->rate)
        );

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
        }

        $row_data[] = modal_anchor(get_uri("items/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_item'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("items/delete"), "data-action" => "delete"));

        return $row_data;
    }

    function view() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $model_info = $this->Items_model->get_details(array("id" => $this->request->getPost('id'), "login_user_id" => $this->login_user->id))->getRow();

        $view_data['model_info'] = $model_info;
        $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
        $view_data['custom_fields_list'] = $this->Custom_fields_model->get_combined_details("items", $model_info->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        return $this->template->view('items/view', $view_data);
    }

    function save_files_sort() {
        $this->access_only_allowed_members();
        $id = $this->request->getPost("id");
        $sort_values = $this->request->getPost("sort_values");
        if ($id && $sort_values) {
            //extract the values from the :,: separated string
            $sort_array = explode(":,:", $sort_values);

            $item_info = $this->Items_model->get_one($id);
            if ($item_info->id) {
                $updated_file_indexes = update_file_indexes($item_info->files, $sort_array);
                $item_data = array(
                    "files" => serialize($updated_file_indexes)
                );

                $this->Items_model->ci_save($item_data, $id);
            }
        }
    }

    private function _validate_excel_import_access() {
        return ($this->access_only_team_members() && $this->validate_access_to_items());
    }

    private function _get_controller_slag() {
        return "items";
    }

    private function _get_custom_field_context() {
        return "items";
    }

    private function _get_headers_for_import() {
        return array(
            array("name" => "title", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("title"))),
            array("name" => "description"),
            array("name" => "category", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("category"))),
            array("name" => "unit_type"),
            array("name" => "rate", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("rate"))),
            array("name" => "show_in_client_portal")
        );
    }

    function download_sample_excel_file() {
        $this->access_only_team_members();
        $this->validate_access_to_items();
        return $this->download_app_files(get_setting("system_file_path"), serialize(array(array("file_name" => "import-items-sample.xlsx"))));
    }

    private function _init_required_data_before_starting_import() {
        $categories = $this->Item_categories_model->get_details()->getResult();
        $categories_id_by_title = array();
        foreach ($categories as $category) {
            $categories_id_by_title[$category->title] = $category->id;
        }

        $this->categories_id_by_title = $categories_id_by_title;
    }

    private function _save_a_row_of_excel_data($row_data) {
        $item_data_array = $this->_prepare_item_data($row_data);
        $item_data = get_array_value($item_data_array, "item_data");

        //couldn't prepare valid data
        if (!($item_data && count($item_data) > 1)) {
            return false;
        }

        //save item data
        $saved_id = $this->Items_model->ci_save($item_data);
        if (!$saved_id) {
            return false;
        }
    }

    private function _prepare_item_data($row_data) {

        $item_data = array();

        foreach ($row_data as $column_index => $value) {
            if (!$value) {
                continue;
            }

            $column_name = $this->_get_column_name($column_index);
            if ($column_name == "category") {
                $category_id = get_array_value($this->categories_id_by_title, $value);
                if ($category_id) {
                    $item_data["category_id"] = $category_id;
                } else {
                    $category_data = array("title" => $value);
                    $saved_category_id = $this->Item_categories_model->ci_save($category_data);
                    $item_data["category_id"] = $saved_category_id;
                    $this->categories_id_by_title[$value] = $saved_category_id;
                }
            } else if ($column_name == "rate") {
                $item_data["rate"] = unformat_currency($value);
            } else if ($column_name == "show_in_client_portal") {
                $item_data["show_in_client_portal"] = ($value === "Yes" ? 1 : "");
            } else {
                $item_data[$column_name] = $value;
            }
        }

/* Dynamic Pricing Endpoints */

    /**
     * Calculate dynamic price for an item
     */
    function calculate_price() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "item_id" => "required|numeric",
            "quantity" => "numeric",
            "area" => "numeric"
        ));

        $item_id = $this->request->getPost('item_id');
        $quantity = $this->request->getPost('quantity') ?: 1;
        $area = $this->request->getPost('area');
        $client_id = $this->request->getPost('client_id');
        $variants_data = $this->request->getPost('variants_data');
        $custom_fields = $this->request->getPost('custom_fields');

        $calculation_data = array(
            'item_id' => $item_id,
            'quantity' => $quantity,
            'area' => $area,
            'client_id' => $client_id,
            'variants_data' => $variants_data,
            'custom_fields' => $custom_fields
        );

        $result = $this->price_calculator_service->calculate_item_price($calculation_data);

        if ($result['success']) {
            echo json_encode(array(
                "success" => true, 
                "price_data" => $result['price_data'],
                "message" => app_lang('price_calculated_successfully')
            ));
        } else {
            echo json_encode(array(
                "success" => false, 
                'message' => $result['error']
            ));
        }
    }

    /**
     * Get applicable pricing rules for an item
     */
    function get_pricing_rules() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "item_id" => "required|numeric"
        ));

        $item_id = $this->request->getPost('item_id');
        $client_id = $this->request->getPost('client_id');

        $result = $this->price_calculator_service->get_applicable_rules($item_id, $client_id);

        if ($result['success']) {
            echo json_encode(array(
                "success" => true, 
                "rules" => $result['rules']
            ));
        } else {
            echo json_encode(array(
                "success" => false, 
                'message' => $result['error']
            ));
        }
    }

    /**
     * Create custom quote from item
     */
    function create_custom_quote() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "item_id" => "required|numeric",
            "client_id" => "required|numeric"
        ));

        $quote_data = array(
            'item_id' => $this->request->getPost('item_id'),
            'client_id' => $this->request->getPost('client_id'),
            'variants_data' => $this->request->getPost('variants_data'),
            'calculation_data' => $this->request->getPost('calculation_data'),
            'custom_fields' => $this->request->getPost('custom_fields'),
            'notes' => $this->request->getPost('notes')
        );

        $result = $this->price_calculator_service->create_custom_quote($quote_data);

        if ($result['success']) {
            echo json_encode(array(
                "success" => true, 
                "quote_id" => $result['quote_id'],
                "message" => app_lang('custom_quote_created_successfully')
            ));
        } else {
            echo json_encode(array(
                "success" => false, 
                'message' => $result['error']
            ));
        }
    }

    /**
     * Get price breakdown for an item
     */
    function get_price_breakdown() {
        $this->access_only_team_members();
        $this->validate_access_to_items();

        $this->validate_submitted_data(array(
            "item_id" => "required|numeric"
        ));

        $item_id = $this->request->getPost('item_id');
        $quantity = $this->request->getPost('quantity') ?: 1;
        $area = $this->request->getPost('area');
        $client_id = $this->request->getPost('client_id');
        $variants_data = $this->request->getPost('variants_data');

        $calculation_data = array(
            'item_id' => $item_id,
            'quantity' => $quantity,
            'area' => $area,
            'client_id' => $client_id,
            'variants_data' => $variants_data
        );

        $result = $this->price_calculator_service->get_price_breakdown($calculation_data);

        if ($result['success']) {
            echo json_encode(array(
                "success" => true, 
                "breakdown" => $result['breakdown']
            ));
        } else {
            echo json_encode(array(
                "success" => false, 
                'message' => $result['error']
            ));
        }
/**
     * Get item variants for price calculation
     */
    public function get_item_variants() {
        $this->access_only_allowed_members();
        
        $item_id = $this->request->getPost('item_id');
        
        if (!$item_id) {
            echo json_encode(array("success" => false, "message" => app_lang('invalid_item')));
            return;
        }
        
        // Load Variants model
        $Variants_model = model("App\Models\Variants_model");
        
        // Get variants for the item
        $variants = $Variants_model->get_all_where(array(
            "item_id" => $item_id,
            "deleted" => 0,
            "status" => 1
        ))->getResult();
        
        $variants_data = array();
        
        foreach ($variants as $variant) {
            $variant_data = array(
                "id" => $variant->id,
                "name" => $variant->variant_name,
                "type" => $variant->variant_type,
                "options" => array()
            );
            
            // Parse variant values
            $values = json_decode($variant->variant_value, true);
            if (!is_array($values)) {
                $values = explode(",", $variant->variant_value);
            }
            
            foreach ($values as $value) {
                $value = trim($value);
                $price_impact = 0;
                
                // Check if there's a price modifier for this option
                if ($variant->price_modifier_type && $variant->price_modifier_value) {
                    // For simplicity, apply the same modifier to all options
                    // In a real scenario, you might have different modifiers per option
                    if ($variant->price_modifier_type === 'fixed') {
                        $price_impact = $variant->price_modifier_value;
                    }
                }
                
                $variant_data['options'][] = array(
                    "id" => $variant->id . "_" . $value,
                    "name" => $value,
                    "price_impact" => $price_impact
                );
            }
            
            $variants_data[] = $variant_data;
        }
        
        echo json_encode(array(
            "success" => true,
            "variants" => $variants_data
        ));
    }
    
    /**
     * Get variant price for combination
     */
    public function get_variant_price() {
        $this->access_only_allowed_members();
        
        $item_id = $this->request->getPost('item_id');
        $variants = $this->request->getPost('variants');
        
        if (!$item_id) {
            echo json_encode(array("success" => false, "message" => app_lang('invalid_item')));
            return;
        }
        
        // Load Variant_combinations_model
        $Variant_combinations_model = model("App\Models\Variant_combinations_model");
        
        // Build combination string from selected variants
        $combination_values = array();
        if ($variants && is_array($variants)) {
            foreach ($variants as $variant_id => $option_value) {
                // Extract the actual value from the option ID
                $parts = explode("_", $option_value, 2);
                if (count($parts) > 1) {
                    $combination_values[] = $parts[1];
                }
            }
        }
        
        // Sort to ensure consistent combination matching
        sort($combination_values);
        $combination_string = implode("-", $combination_values);
        
        // Look for matching combination
        $combination = $Variant_combinations_model->get_one_where(array(
            "item_id" => $item_id,
            "combination" => $combination_string,
            "status" => 1,
            "deleted" => 0
        ));
        
        $price_adjustment = 0;
        $stock_status = "in_stock";
        
        if ($combination) {
            // Use combination-specific price if available
            if ($combination->price) {
                $price_adjustment = $combination->price;
            }
            
            // Check stock
            if ($combination->stock !== null && $combination->stock <= 0) {
                $stock_status = "out_of_stock";
            }
        } else {
            // Calculate price based on individual variant modifiers
            $Variants_model = model("App\Models\Variants_model");
            
            foreach ($variants as $variant_id => $option_value) {
                $variant = $Variants_model->get_one($variant_id);
                if ($variant && $variant->price_modifier_type && $variant->price_modifier_value) {
                    if ($variant->price_modifier_type === 'fixed') {
                        $price_adjustment += $variant->price_modifier_value;
                    } else if ($variant->price_modifier_type === 'percentage') {
                        // This would need the base price to calculate percentage
                        // For now, we'll handle this in the pricing engine
                    }
                
                    /**
                     * Get item dimensions by item_id
                     */
                    public function get_item_dimensions()
                    {
                        $this->access_only_team_members();
                        $this->validate_access_to_items();
                
                        $this->validate_submitted_data([
                            "item_id" => "required|numeric"
                        ]);
                
                        $item_id = $this->request->getPost('item_id');
                        $ItemDimensions_model = model("App\Models\ItemDimensions_model");
                        $dimensions = $ItemDimensions_model->get_by_item_id($item_id);
                
                        if ($dimensions) {
                            echo json_encode([
                                "success" => true,
                                "dimensions" => $dimensions
                            ]);
                        } else {
                            echo json_encode([
                                "success" => false,
                                "message" => app_lang('no_dimensions_found')
                            ]);
                        }
                    }
                
                    /**
                     * Save or update item dimensions
                     */
                    public function save_item_dimensions()
                    {
                        $this->access_only_team_members();
                        $this->validate_access_to_items();
                
                        $this->validate_submitted_data([
                            "item_id" => "required|numeric",
                            "width" => "required|numeric",
                            "height" => "required|numeric",
                            "unit" => "required"
                        ]);
                
                        $item_id = $this->request->getPost('item_id');
                        $width = $this->request->getPost('width');
                        $height = $this->request->getPost('height');
                        $unit = $this->request->getPost('unit');
                
                        $ItemDimensions_model = model("App\Models\ItemDimensions_model");
                        $result = $ItemDimensions_model->save_or_update([
                            "item_id" => $item_id,
                            "width" => $width,
                            "height" => $height,
                            "unit" => $unit
                        ]);
                
                        if ($result) {
                            echo json_encode([
                                "success" => true,
                                "message" => app_lang('record_saved')
                            ]);
                        } else {
                            echo json_encode([
                                "success" => false,
                                "message" => app_lang('error_occurred')
                            ]);
                        }
                    }
                }
            }
        }
        
        echo json_encode(array(
            "success" => true,
            "price_adjustment" => $price_adjustment,
            "stock_status" => $stock_status,
            "combination" => $combination ? $combination : null
        ));
    }
    
    private function _item_row_data($id) {
    }
        return array(
            "item_data" => $item_data
        );
    }
}

/* End of file items.php */
/* Location: ./app/controllers/items.php */