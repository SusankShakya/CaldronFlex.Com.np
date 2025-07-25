<?php

namespace App\Controllers;

class Proposals extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("proposal");
    }

    private function validate_proposal_access($proposal_id = 0, $check_client = false) {
        if (!$this->can_access_this_proposal($proposal_id, $check_client)) {
            app_redirect("forbidden");
        }
    }

    /* load proposal list view */

    function index() {
        $this->check_module_availability("module_proposal");
        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("proposals", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("proposals", $this->login_user->is_admin, $this->login_user->user_type);

        if ($this->login_user->user_type === "staff") {
            $this->access_only_allowed_members();

            return $this->template->rander("proposals/index", $view_data);
        } else {
            //client view
            if (!$this->can_client_access("proposal")) {
                app_redirect("forbidden");
            }

            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";

            return $this->template->rander("clients/proposals/client_portal", $view_data);
        }
    }

    /* load new proposal modal */

    function modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $is_clone = $this->request->getPost('is_clone');

        $this->validate_proposal_access($id);
        if (!$this->_is_proposal_editable($id, $is_clone)) {
            app_redirect("forbidden");
        }

        $client_id = $this->request->getPost('client_id');
        $view_data['model_info'] = $this->Proposals_model->get_one($id);

        $project_client_id = $client_id;
        if ($view_data['model_info']->client_id) {
            $project_client_id = $view_data['model_info']->client_id;
        }

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = $this->get_proposal_clients_and_leads_dropdown();

        //don't show clients dropdown for lead's proposal editing
        $client_info = $this->Clients_model->get_one($view_data['model_info']->client_id);
        if ($client_info->is_lead) {
            $client_id = $client_info->id;
        }

        $view_data['client_id'] = $client_id;

        //clone proposal data
        $view_data['is_clone'] = $is_clone;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("proposals", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        $view_data['companies_dropdown'] = $this->_get_companies_dropdown();
        if (!$view_data['model_info']->company_id) {
            $view_data['model_info']->company_id = get_default_company_id();
        }

        return $this->template->view('proposals/modal_form', $view_data);
    }

    private function get_proposal_clients_and_leads_dropdown() {
        $clients_dropdown = array("" => "-");
        $clients = $this->Clients_model->get_all_where(array("deleted" => 0), 0, 0, "is_lead")->getResult();

        foreach ($clients as $client) {
            $company_name = $client->is_lead ? (app_lang("lead") . ": " . $client->company_name) : (app_lang("client") . ": " . $client->company_name);
            $clients_dropdown[$client->id] = $company_name;
        }

        return $clients_dropdown;
    }

    function save_view() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost("id");

        $this->validate_proposal_access($id);

        $proposal_data = array(
            "content" => decode_ajax_post_data($this->request->getPost('view'))
        );

        $this->Proposals_model->ci_save($proposal_data, $id);

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    /* add, edit or clone an proposal */

    function save() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "proposal_client_id" => "required|numeric",
            "proposal_date" => "required",
            "valid_until" => "required"
        ));

        $client_id = $this->request->getPost('proposal_client_id');
        $id = $this->request->getPost('id');
        $is_clone = $this->request->getPost('is_clone');

        $this->validate_proposal_access($id);
        if (!$this->_is_proposal_editable($id, $is_clone)) {
            app_redirect("forbidden");
        }

        $proposal_data = array(
            "client_id" => $client_id,
            "proposal_date" => $this->request->getPost('proposal_date'),
            "valid_until" => $this->request->getPost('valid_until'),
            "tax_id" => $this->request->getPost('tax_id') ? $this->request->getPost('tax_id') : 0,
            "tax_id2" => $this->request->getPost('tax_id2') ? $this->request->getPost('tax_id2') : 0,
            "company_id" => $this->request->getPost('company_id') ? $this->request->getPost('company_id') : get_default_company_id(),
            "note" => $this->request->getPost('proposal_note')
        );

        //save random code for new proposal
        if (!$id) {
            $proposal_data["created_by"] = $this->login_user->id;
            $proposal_data["public_key"] = make_random_string();

            //add default template
            if (get_setting("default_proposal_template")) {
                $Proposal_templates_model = model("App\Models\Proposal_templates_model");
                $proposal_data["content"] = $Proposal_templates_model->get_one(get_setting("default_proposal_template"))->template;
            }
        }

        $main_proposal_id = "";
        if ($is_clone && $id) {
            $main_proposal_id = $id; //store main proposal id to get items later
            $id = ""; //on cloning proposal, save as new
            //save discount when cloning
            $main_proposal_info = $this->Proposals_model->get_one($main_proposal_id);
            $proposal_data["discount_amount"] = $main_proposal_info->discount_amount;
            $proposal_data["discount_amount_type"] = $main_proposal_info->discount_amount_type;
            $proposal_data["discount_type"] = $main_proposal_info->discount_type;
            $proposal_data["content"] = $main_proposal_info->content;
            $proposal_data["public_key"] = make_random_string();
            $proposal_data["created_by"] = $this->login_user->id;
        }

        $proposal_id = $this->Proposals_model->ci_save($proposal_data, $id);
        if ($proposal_id) {

            if ($is_clone && $main_proposal_id) {
                //add proposal items

                save_custom_fields("proposals", $proposal_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a proposal

                $proposal_items = $this->Proposal_items_model->get_all_where(array("proposal_id" => $main_proposal_id, "deleted" => 0))->getResult();

                foreach ($proposal_items as $proposal_item) {
                    //prepare new proposal item data
                    $proposal_item_data = (array) $proposal_item;
                    unset($proposal_item_data["id"]);
                    $proposal_item_data['proposal_id'] = $proposal_id;

                    $proposal_item = $this->Proposal_items_model->ci_save($proposal_item_data);
                }
            } else {
                save_custom_fields("proposals", $proposal_id, $this->login_user->is_admin, $this->login_user->user_type);
            }

            echo json_encode(array("success" => true, "data" => $this->_row_data($proposal_id), 'id' => $proposal_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //update proposal status
    function update_proposal_status($proposal_id, $status) {
        if ($proposal_id && $status) {
            validate_numeric_value($proposal_id);
            $proposal_info = $this->Proposals_model->get_one($proposal_id);
            $this->validate_proposal_access($proposal_id, true);

            if ($this->login_user->user_type == "client") {
                //updating by client
                //client can only update the status once and the value should be either accepted or declined
                if ($proposal_info->status == "sent" && ($status == "accepted" || $status == "declined")) {

                    $proposal_data = array("status" => $status);
                    if ($status == "accepted") {
                        $proposal_data["accepted_by"] = $this->login_user->id;
                    }

                    $proposal_id = $this->Proposals_model->ci_save($proposal_data, $proposal_id);

                    //create notification
                    if ($status == "accepted") {
                        log_notification("proposal_accepted", array("proposal_id" => $proposal_id));
                    } else if ($status == "declined") {
                        log_notification("proposal_rejected", array("proposal_id" => $proposal_id));
                    }
                }
            } else {
                //updating by team members
                if ($status == "accepted" || $status == "declined" || $status == "sent") {
                    $proposal_data = array("status" => $status);
                    $proposal_id = $this->Proposals_model->ci_save($proposal_data, $proposal_id);
                }
            }
        }
    }

    /* delete or undo an proposal */

    function delete() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $this->validate_proposal_access($id);

        if ($this->Proposals_model->delete($id)) {
            //delete signature file
            $proposal_info = $this->Proposals_model->get_one($id);
            $signer_info = @unserialize($proposal_info->meta_data);
            if ($signer_info && is_array($signer_info) && get_array_value($signer_info, "signature")) {
                $signature_file = unserialize(get_array_value($signer_info, "signature"));
                delete_app_files(get_setting("timeline_file_path"), $signature_file);
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* list of proposals, prepared for datatable  */

    function list_data() {
        $this->access_only_allowed_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("proposals", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status" => $this->request->getPost("status"),
            "start_date" => $this->request->getPost("start_date"),
            "end_date" => $this->request->getPost("end_date"),
            "last_email_seen_start_date" => $this->request->getPost("last_email_seen_start_date"),
            "last_email_seen_end_date" => $this->request->getPost("last_email_seen_end_date"),
            "last_preview_seen_start_date" => $this->request->getPost("last_preview_seen_start_date"),
            "last_preview_seen_end_date" => $this->request->getPost("last_preview_seen_end_date"),
            "show_own_proposals_only_user_id" => $this->show_own_proposals_only_user_id(),
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("proposals", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $list_data = $this->Proposals_model->get_details($options)->getResult();
        $getResult = array();
        foreach ($list_data as $data) {
            $getResult[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $getResult));
    }

    /* list of proposal of a specific client, prepared for datatable  */

    function proposal_list_data_of_client($client_id) {
        validate_numeric_value($client_id);
        $this->access_only_allowed_members_or_client_contact($client_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("proposals", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("client_id" => $client_id, "status" => $this->request->getPost("status"), "custom_fields" => $custom_fields, "custom_field_filter" => $this->prepare_custom_field_filter_values("proposals", $this->login_user->is_admin, $this->login_user->user_type));

        if ($this->login_user->user_type == "client") {
            //don't show draft proposals to clients.
            $options["exclude_draft"] = true;
        }

        $list_data = $this->Proposals_model->get_details($options)->getResult();
        $getResult = array();
        foreach ($list_data as $data) {
            $getResult[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $getResult));
    }

    /* return a row of proposal list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("proposals", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Proposals_model->get_details($options)->getRow();
        return $this->_make_row($data, $custom_fields);
    }

    /* prepare a row of proposal list table */

    private function _make_row($data, $custom_fields) {
        $proposal_url = "";
        if ($this->login_user->user_type == "staff") {
            $proposal_url = anchor(get_uri("proposals/view/" . $data->id), get_proposal_id($data->id));
        } else {
            //for client client
            $proposal_url = anchor(get_uri("proposals/preview/" . $data->id), get_proposal_id($data->id));
        }

        $client = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name ? $data->company_name : "");
        if ($data->is_lead) {
            $client = anchor(get_uri("leads/view/" . $data->client_id), $data->company_name ? $data->company_name : "");
        }

        $last_email_read_time = "-";
        if ($data->last_email_read_time) {
            $last_email_read_time = format_to_relative_time($data->last_email_read_time);
        }

        $last_preview_seen = "-";
        if ($data->last_preview_seen) {
            $last_preview_seen = format_to_relative_time($data->last_preview_seen);
        }

        $row_data = array(
            $proposal_url,
            $client,
            $data->proposal_date,
            format_to_date($data->proposal_date, false),
            $data->valid_until,
            format_to_date($data->valid_until, false),
            $last_email_read_time,
            $last_preview_seen,
            to_currency($data->proposal_value, $data->currency_symbol),
            $this->_get_proposal_status_label($data),
        );

        $comment_link = "";

        $total_comments = "";
        if ($data->total_comments) {
            $total_comments = $data->total_comments;
        }

        if (get_setting("enable_comments_on_proposals") && $data->status !== "draft") {
            $comment_link = modal_anchor(get_uri("proposals/comment_modal_form"), $total_comments . " <i data-feather='message-circle' class='icon-16'></i>", array("class" => "text-muted", "title" => app_lang("proposal") . " #" . $data->id . " " . app_lang("comments"), "data-post-proposal_id" => $data->id));
        }

        $row_data[] = $comment_link;

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
        }

        $edit = "";
        if ($this->_is_proposal_editable($data)) {
            $edit = modal_anchor(get_uri("proposals/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_proposal'), "data-post-id" => $data->id));
        }

        $row_data[] = anchor(get_uri("offer/preview/" . $data->id . "/" . $data->public_key), "<i data-feather='external-link' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('proposal') . " " . app_lang("url"), "target" => "_blank"))
            . $edit
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_proposal'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("proposals/delete"), "data-action" => "delete-confirmation"));

        return $row_data;
    }

    //prepare proposal status label 
    private function _get_proposal_status_label($proposal_info, $return_html = true, $extra_classes = "") {
        $proposal_status_class = "bg-secondary";

        //don't show sent status to client, change the status to 'new' from 'sent'

        if ($this->login_user->user_type == "client") {
            if ($proposal_info->status == "sent") {
                $proposal_info->status = "new";
            } else if ($proposal_info->status == "declined") {
                $proposal_info->status = "rejected";
            }
        }

        if ($proposal_info->status == "draft") {
            $proposal_status_class = "bg-secondary";
        } else if ($proposal_info->status == "declined" || $proposal_info->status == "rejected") {
            $proposal_status_class = "bg-danger";
        } else if ($proposal_info->status == "accepted") {
            $proposal_status_class = "bg-success";
        } else if ($proposal_info->status == "sent") {
            $proposal_status_class = "bg-primary";
        } else if ($proposal_info->status == "new") {
            $proposal_status_class = "bg-warning";
        }

        $proposal_status = "<span class='mt0 badge $proposal_status_class $extra_classes'>" . app_lang($proposal_info->status) . "</span>";
        if ($return_html) {
            return $proposal_status;
        } else {
            return $proposal_info->status;
        }
    }

    /* load proposal details view */

    function view($proposal_id = 0) {
        validate_numeric_value($proposal_id);
        $this->validate_proposal_access($proposal_id);

        if ($proposal_id) {

            $view_data = get_proposal_making_data($proposal_id);

            $sort_as_decending = get_setting("show_most_recent_proposal_comments_at_the_top");
            $comments_options = array(
                "proposal_id" => $proposal_id,
                "sort_as_decending" => $sort_as_decending
            );
            $view_data['comments'] = $this->Proposal_comments_model->get_details($comments_options)->getResult();
            $view_data["sort_as_decending"] = $sort_as_decending;

            if ($view_data) {
                $view_data['proposal_status_label'] = $this->_get_proposal_status_label($view_data["proposal_info"], true, "large rounded-pill");
                $view_data['proposal_status'] = $this->_get_proposal_status_label($view_data["proposal_info"], false);

                $view_data["can_create_projects"] = $this->can_create_projects();

                $access_info = $this->get_access_info("invoice");
                $view_data["show_invoice_option"] = (get_setting("module_invoice") && $access_info->access_type == "all") ? true : false;

                $access_info = $this->get_access_info("estimate");
                $view_data["show_estimate_option"] = (get_setting("module_estimate") && $access_info->access_type == "all") ? true : false;

                $access_contract = $this->get_access_info("contract");
                $view_data["show_contract_option"] = (get_setting("module_contract") && $access_contract->access_type == "all") ? true : false;

                $view_data["proposal_id"] = $proposal_id;
                $view_data["is_proposal_editable"] = $this->_is_proposal_editable($proposal_id);

                $view_data["custom_field_headers_of_task"] = $this->Custom_fields_model->get_custom_field_headers_for_table("tasks", $this->login_user->is_admin, $this->login_user->user_type);

                $view_type = $this->request->getPost('view_type');
                if ($view_type == "proposal_meta") {
                    echo json_encode(array(
                        "success" => true,
                        "top_bar" => $this->template->view("proposals/proposal_top_bar",  $view_data),
                    ));
                } else {
                    return $this->template->rander("proposals/view", $view_data);
                }
            } else {
                show_404();
            }
        }
    }

    /* proposal total section */

    private function _get_proposal_total_view($proposal_id = 0) {
        $view_data["proposal_total_summary"] = $this->Proposals_model->get_proposal_total_summary($proposal_id);
        $view_data["proposal_id"] = $proposal_id;
        $view_data["is_proposal_editable"] = $this->_is_proposal_editable($proposal_id);
        return $this->template->view('proposals/proposal_total_section', $view_data);
    }

    /* load discount modal */

    function discount_modal_form() {
        $this->validate_submitted_data(array(
            "proposal_id" => "required|numeric"
        ));

        $proposal_id = $this->request->getPost('proposal_id');
        $this->validate_proposal_access($proposal_id);
        if (!$this->_is_proposal_editable($proposal_id)) {
            app_redirect("forbidden");
        }

        $view_data['model_info'] = $this->Proposals_model->get_one($proposal_id);

        return $this->template->view('proposals/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount() {
        $this->validate_submitted_data(array(
            "proposal_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $proposal_id = $this->request->getPost('proposal_id');
        $this->validate_proposal_access($proposal_id);
        if (!$this->_is_proposal_editable($proposal_id)) {
            app_redirect("forbidden");
        }

        $data = array(
            "discount_type" => $this->request->getPost('discount_type'),
            "discount_amount" => $this->request->getPost('discount_amount'),
            "discount_amount_type" => $this->request->getPost('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Proposals_model->ci_save($data, $proposal_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "proposal_total_view" => $this->_get_proposal_total_view($proposal_id), 'message' => app_lang('record_saved'), "proposal_id" => $proposal_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* load item modal */

    function item_modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $proposal_id = $this->request->getPost('proposal_id');
        $this->validate_proposal_access($proposal_id);
        if (!$this->_is_proposal_editable($proposal_id)) {
            app_redirect("forbidden");
        }

        $view_data['model_info'] = $this->Proposal_items_model->get_one($this->request->getPost('id'));
        if (!$proposal_id) {
            $proposal_id = $view_data['model_info']->proposal_id;
        }
        $view_data['proposal_id'] = $proposal_id;
        return $this->template->view('proposals/item_modal_form', $view_data);
    }

    /* add or edit an proposal item */

    function save_item() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "proposal_id" => "required|numeric"
        ));

        $proposal_id = $this->request->getPost('proposal_id');
        $this->validate_proposal_access($proposal_id);
        if (!$this->_is_proposal_editable($proposal_id)) {
            app_redirect("forbidden");
        }

        $id = $this->request->getPost('id');
        $rate = unformat_currency($this->request->getPost('proposal_item_rate'));
        $quantity = unformat_currency($this->request->getPost('proposal_item_quantity'));
        $proposal_item_title = $this->request->getPost('proposal_item_title');
        $item_id = 0;

        if (!$id) {
            //on adding item for the first time, get the id to store
            $item_id = $this->request->getPost('item_id');
        }

        //check if the add_new_item flag is on, if so, add the item to libary. 
        $add_new_item_to_library = $this->request->getPost('add_new_item_to_library');
        if ($add_new_item_to_library) {
            $library_item_data = array(
                "title" => $proposal_item_title,
                "description" => $this->request->getPost('proposal_item_description'),
                "unit_type" => $this->request->getPost('proposal_unit_type'),
                "rate" => unformat_currency($this->request->getPost('proposal_item_rate'))
            );
            $item_id = $this->Items_model->ci_save($library_item_data);
        }

        $proposal_item_data = array(
            "proposal_id" => $proposal_id,
            "title" => $this->request->getPost('proposal_item_title'),
            "description" => $this->request->getPost('proposal_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->request->getPost('proposal_unit_type'),
            "rate" => unformat_currency($this->request->getPost('proposal_item_rate')),
            "total" => $rate * $quantity,
        );

        if ($item_id) {
            $proposal_item_data["item_id"] = $item_id;
        }

        $proposal_item_id = $this->Proposal_items_model->ci_save($proposal_item_data, $id);
        if ($proposal_item_id) {
            $options = array("id" => $proposal_item_id);
            $item_info = $this->Proposal_items_model->get_details($options)->getRow();
            echo json_encode(array("success" => true, "proposal_id" => $item_info->proposal_id, "data" => $this->_make_item_row($item_info), "proposal_total_view" => $this->_get_proposal_total_view($item_info->proposal_id), 'id' => $proposal_item_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete or undo an proposal item */

    function delete_item() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $item_info = $this->Proposal_items_model->get_one($id);
        $this->validate_proposal_access($item_info->proposal_id);
        if (!$this->_is_proposal_editable($item_info->proposal_id)) {
            app_redirect("forbidden");
        }

        if ($this->request->getPost('undo')) {
            if ($this->Proposal_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Proposal_items_model->get_details($options)->getRow();
                echo json_encode(array("success" => true, "proposal_id" => $item_info->proposal_id, "data" => $this->_make_item_row($item_info), "proposal_total_view" => $this->_get_proposal_total_view($item_info->proposal_id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Proposal_items_model->delete($id)) {
                $item_info = $this->Proposal_items_model->get_one($id);
                echo json_encode(array("success" => true, "proposal_id" => $item_info->proposal_id, "proposal_total_view" => $this->_get_proposal_total_view($item_info->proposal_id), 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of proposal items, prepared for datatable  */

    function item_list_data($proposal_id = 0) {
        validate_numeric_value($proposal_id);
        $this->validate_proposal_access($proposal_id);

        $list_data = $this->Proposal_items_model->get_details(array("proposal_id" => $proposal_id))->getResult();
        $getResult = array();
        foreach ($list_data as $data) {
            $getResult[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $getResult));
    }

    /* prepare a row of proposal item list table */

    private function _make_item_row($data) {
        $move_icon = "";
        $desc_style = "";

        if ($this->_is_proposal_editable($data->proposal_id)) {
            $move_icon = "<div class='float-start move-icon'><i data-feather='menu' class='icon-16'></i></div>";
            $desc_style = " style='margin-left:30px'";
        }

        $item = "<div class='item-row strong mb5' data-id='$data->id'>$move_icon $data->title</div>";
        if ($data->description) {
            $item .= "<div $desc_style >" . custom_nl2br($data->description) . "</div>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        return array(
            $data->sort,
            $item,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol),
            to_currency($data->total, $data->currency_symbol),
            modal_anchor(get_uri("proposals/item_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_proposal'), "data-post-id" => $data->id, "data-post-proposal_id" => $data->proposal_id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("proposals/delete_item"), "data-action" => "delete"))
        );
    }

    /* prepare suggestion of proposal item */

    function get_proposal_item_suggestion() {
        $key = $this->request->getPost("q");
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->id, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . app_lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_proposal_item_info_suggestion() {
        $item_id = $this->request->getPost("item_id");
        validate_numeric_value($item_id);

        $item = $this->Invoice_items_model->get_item_info_suggestion(array("item_id" => $item_id));
        if ($item) {
            $item->rate = $item->rate ? to_decimal_format($item->rate) : "";
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($proposal_id = 0, $show_close_preview = false, $is_editor_preview = false) {
        validate_numeric_value($proposal_id);

        $view_data = array();

        if ($proposal_id) {

            $proposal_data = get_proposal_making_data($proposal_id);
            $this->_check_proposal_access_permission($proposal_data);

            //get the label of the proposal
            $proposal_info = get_array_value($proposal_data, "proposal_info");

            if ($this->login_user->user_type == "client" && $proposal_info->status == "sent") {

                $this->Proposals_model->update_proposal_preview_activity($proposal_id);
                log_notification("proposal_preview_opened", array("proposal_id" => $proposal_id), $this->login_user->id);
            }

            $proposal_data['proposal_status_label'] = $this->_get_proposal_status_label($proposal_info);

            $view_data['proposal_preview'] = prepare_proposal_view($proposal_data);

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['proposal_id'] = $proposal_id;

            $sort_as_decending = get_setting("show_most_recent_proposal_comments_at_the_top");
            $comments_options = array(
                "proposal_id" => $proposal_id,
                "sort_as_decending" => $sort_as_decending
            );
            $view_data['comments'] = $this->Proposal_comments_model->get_details($comments_options)->getResult();
            $view_data["sort_as_decending"] = $sort_as_decending;

            $view_data["has_pdf_access"] = $this->check_proposal_pdf_access_for_clients($this->login_user->user_type);

            if ($is_editor_preview) {
                $view_data["is_editor_preview"] = clean_data($is_editor_preview);
                return $this->template->view("proposals/proposal_preview", $view_data);
            } else {
                return $this->template->rander("proposals/proposal_preview", $view_data);
            }
        } else {
            show_404();
        }
    }

    private function _check_proposal_access_permission($proposal_data) {
        //check for valid proposal
        if (!$proposal_data) {
            show_404();
        }

        //check for security
        $proposal_info = get_array_value($proposal_data, "proposal_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $proposal_info->client_id || $proposal_info->status === "draft" || !$this->can_client_access("proposal")) {
                app_redirect("forbidden");
            }
        } else {
            $this->access_only_allowed_members();
        }
    }

    function send_proposal_modal_form($proposal_id) {
        validate_numeric_value($proposal_id);
        $this->validate_proposal_access($proposal_id);

        if ($proposal_id) {
            $options = array("id" => $proposal_id);
            $proposal_info = $this->Proposals_model->get_details($options)->getRow();
            $view_data['proposal_info'] = $proposal_info;

            $is_lead = $this->request->getPost('is_lead');
            if ($is_lead) {
                $contacts_options = array("user_type" => "lead", "client_id" => $proposal_info->client_id);
            } else {
                $contacts_options = array("user_type" => "client", "client_id" => $proposal_info->client_id);
            }

            $contacts = $this->Users_model->get_details($contacts_options)->getResult();

            $primary_contact_info = "";
            $contacts_dropdown = array();
            foreach ($contacts as $contact) {
                if ($contact->is_primary_contact) {
                    $primary_contact_info = $contact;
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name . " (" . app_lang("primary_contact") . ")";
                }
            }

            foreach ($contacts as $contact) {
                if (!$contact->is_primary_contact) {
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name;
                }
            }

            $view_data['contacts_dropdown'] = $contacts_dropdown;

            $template_data = $this->get_send_proposal_template($proposal_id, 0, "", $proposal_info, $primary_contact_info);
            $view_data['message'] = get_array_value($template_data, "message");
            $view_data['subject'] = get_array_value($template_data, "subject");
            $view_data["has_pdf_access"] = $this->check_proposal_pdf_access_for_clients();

            return $this->template->view('proposals/send_proposal_modal_form', $view_data);
        } else {
            show_404();
        }
    }

    function get_send_proposal_template($proposal_id = 0, $contact_id = 0, $return_type = "", $proposal_info = "", $contact_info = "") {
        $this->validate_proposal_access($proposal_id);

        validate_numeric_value($proposal_id);
        validate_numeric_value($contact_id);

        if (!$proposal_info) {
            $options = array("id" => $proposal_id);
            $proposal_info = $this->Proposals_model->get_details($options)->getRow();
        }

        if (!$contact_info) {
            $contact_info = $this->Users_model->get_one($contact_id);
        }

        $contact_language = $contact_info->language;

        $email_template = $this->Email_templates_model->get_final_template("proposal_sent", true);

        $parser_data["PROPOSAL_ID"] = $proposal_info->id;
        $parser_data["CONTACT_FIRST_NAME"] = $contact_info->first_name;
        $parser_data["CONTACT_LAST_NAME"] = $contact_info->last_name;
        $parser_data["PROPOSAL_URL"] = get_uri("proposals/preview/" . $proposal_info->id);
        $parser_data["PUBLIC_PROPOSAL_URL"] = get_uri("offer/preview/" . $proposal_info->id . "/" . $proposal_info->public_key);
        $parser_data['SIGNATURE'] = get_array_value($email_template, "signature_$contact_language") ? get_array_value($email_template, "signature_$contact_language") : get_array_value($email_template, "signature_default");
        $parser_data["LOGO_URL"] = get_logo_url();
        $parser_data["RECIPIENTS_EMAIL_ADDRESS"] = $contact_info->email;

        $parser = \Config\Services::parser();

        $message = get_array_value($email_template, "message_$contact_language") ? get_array_value($email_template, "message_$contact_language") : get_array_value($email_template, "message_default");
        $subject = get_array_value($email_template, "subject_$contact_language") ? get_array_value($email_template, "subject_$contact_language") : get_array_value($email_template, "subject_default");

        $message = $parser->setData($parser_data)->renderString($message);
        $subject = $parser->setData($parser_data)->renderString($subject);
        $message = htmlspecialchars_decode($message);
        $subject = htmlspecialchars_decode($subject);

        if ($return_type == "json") {
            echo json_encode(array("success" => true, "message_view" => $message));
        } else {
            return array(
                "message" => $message,
                "subject" => $subject
            );
        }
    }

    function send_proposal() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $proposal_id = $this->request->getPost('id');
        $this->validate_proposal_access($proposal_id);

        $contact_id = $this->request->getPost('contact_id');
        $cc = $this->request->getPost('proposal_cc');

        $custom_bcc = $this->request->getPost('proposal_bcc');
        $subject = $this->request->getPost('subject');
        $message = decode_ajax_post_data($this->request->getPost('message'));

        $attach_pdf = $this->request->getPost('attach_pdf');

        $attachments = array();
        if ($attach_pdf) {
            $proposal_data = get_proposal_making_data($proposal_id);
            $proposal_data['proposal_preview'] = prepare_proposal_view($proposal_data);

            $attachement_url = prepare_proposal_pdf($proposal_data, "send_email");

            $attachment_size = filesize($attachement_url);

            if ($attachment_size / 1000000 > 10) {
                echo json_encode(array("success" => false, 'message' => app_lang("attachment_size_is_too_large")));
                exit();
            }

            //add proposal pdf
            array_unshift($attachments, array("file_path" => $attachement_url));
        }

        $contact = $this->Users_model->get_one($contact_id);

        $default_bcc = get_setting('send_proposal_bcc_to');
        $bcc_emails = "";

        if ($default_bcc && $custom_bcc) {
            $bcc_emails = $default_bcc . "," . $custom_bcc;
        } else if ($default_bcc) {
            $bcc_emails = $default_bcc;
        } else if ($custom_bcc) {
            $bcc_emails = $custom_bcc;
        }

        //insert the data in the event tracker. 
        $now = get_current_utc_time();
        $event_tracker_data = array(
            "context" => "proposal",
            "context_id" => $proposal_id,
            "event_type" => "proposal_email",
            "random_id" => make_random_string(),
            "created_at" => $now
        );

        $event_tracker_model = model("App\Models\Event_tracker_model");
        $event_tracker_model->ci_save($event_tracker_data);

        $src = get_uri('event_tracker/load/' . $event_tracker_data["random_id"]);
        $message .= "<img src='$src' alt='.' />";

        if (send_app_mail($contact->email, $subject, $message, array("attachments" => $attachments, "cc" => $cc, "bcc" => $bcc_emails))) {
            // change email status
            $status_data = array("status" => "sent", "last_email_sent_date" => get_my_local_time());
            if ($this->Proposals_model->ci_save($status_data, $proposal_id)) {
                echo json_encode(array('success' => true, 'message' => app_lang("proposal_sent_message"), "proposal_id" => $proposal_id));
            }

            if ($attach_pdf) {
                // delete the temp proposal
                if (file_exists($attachement_url)) {
                    unlink($attachement_url);
                }
            }
        } else {
            echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
        }
    }

    //update the sort value for proposal item
    function update_item_sort_values($id = 0) {
        validate_numeric_value($id);

        $sort_values = $this->request->getPost("sort_values");
        if ($sort_values) {

            //extract the values from the comma separated string
            $sort_array = explode(",", $sort_values);

            //update the value in db
            foreach ($sort_array as $value) {
                $sort_item = explode("-", $value); //extract id and sort value

                $id = get_array_value($sort_item, 0);
                validate_numeric_value($id);

                $sort = get_array_value($sort_item, 1);
                validate_numeric_value($sort);

                $data = array("sort" => $sort);
                $this->Proposal_items_model->ci_save($data, $id);
            }
        }
    }

    function editor($proposal_id = 0) {
        validate_numeric_value($proposal_id);
        $view_data['proposal_info'] = $this->Proposals_model->get_details(array("id" => $proposal_id))->getRow();
        return $this->template->view("proposals/proposal_editor", $view_data);
    }

    //prevent editing of proposal after certain state
    private function _is_proposal_editable($_proposal, $is_clone = 0) {
        if (get_setting("enable_proposal_lock_state")) {
            $proposal_info = is_object($_proposal) ? $_proposal : $this->Proposals_model->get_one($_proposal);
            if (!$proposal_info->id || $is_clone) {
                return true;
            }

            if ($proposal_info->status != "accepted") {
                return true;
            }
        } else {
            return true;
        }
    }

    function email_view_report($proposal_id) {
        validate_numeric_value($proposal_id);

        $this->validate_proposal_access($proposal_id);

        $options = array(
            "context" => "proposal",
            "context_id" => $proposal_id
        );

        $tracking_info = $this->Event_tracker_model->get_details($options)->getResult();

        $all_logs = array();
        foreach ($tracking_info as $tracking_data) {
            $logs = unserialize($tracking_data->logs);

            if (is_array($logs)) {
                foreach ($logs as $log) {
                    if (isset($log['read_at'])) {
                        $all_logs[] = $log['read_at'];
                    }
                }
            }
        }

        rsort($all_logs);
        $view_data["email_read_logs"] = $all_logs;

        return $this->template->view("proposals/email_view_report", $view_data);
    }

    function download_pdf($proposal_id = 0, $mode = "download", $user_language = "") {
        if (!$proposal_id) {
            show_404();
        }

        if (!$this->check_proposal_pdf_access_for_clients($this->login_user->user_type)) {
            show_404();
        }

        validate_numeric_value($proposal_id);
        $proposal_data = get_proposal_making_data($proposal_id);
        $proposal_data['proposal_preview'] = prepare_proposal_view($proposal_data);
        $this->_check_proposal_access_permission($proposal_data);

        if ($user_language) {
            $language = service('language');

            $active_locale = $language->getLocale();

            if ($user_language && $user_language !== $active_locale) {
                $language->setLocale($user_language);
            }

            prepare_proposal_pdf($proposal_data, $mode);

            if ($user_language && $user_language !== $active_locale) {
                // Reset to active locale
                $language->setLocale($active_locale);
            }
        } else {
            prepare_proposal_pdf($proposal_data, $mode);
        }
    }

    /* load proposal comment modal */

    function comment_modal_form() {
        $this->validate_submitted_data(array(
            "proposal_id" => "numeric|required"
        ));

        if (get_setting("enable_comments_on_proposals") !== "1") {
            app_redirect("forbidden");
        }

        $proposal_id = $this->request->getPost('proposal_id');
        $this->validate_proposal_access($proposal_id);

        $view_data['proposal_id'] = $proposal_id;

        $sort_as_decending = get_setting("show_most_recent_proposal_comments_at_the_top");

        $view_data = get_proposal_making_data($proposal_id);

        $comments_options = array(
            "proposal_id" => $proposal_id,
            "sort_as_decending" => $sort_as_decending
        );
        $view_data['comments'] = $this->Proposal_comments_model->get_details($comments_options)->getResult();
        $view_data["sort_as_decending"] = $sort_as_decending;

        return $this->template->view('proposals/comment_form', $view_data);
    }

    /* save proposal comments */

    function save_comment() {
        $proposal_id = $this->request->getPost('proposal_id');
        $this->validate_proposal_access($proposal_id, true);

        $now = get_current_utc_time();

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "proposal");

        $this->validate_submitted_data(array(
            "description" => "required",
            "proposal_id" => "required|numeric"
        ));

        $comment_data = array(
            "description" => $this->request->getPost('description'),
            "proposal_id" => $proposal_id,
            "created_by" => $this->login_user->id,
            "created_at" => $now,
            "files" => $files_data
        );

        $comment_data = clean_data($comment_data);
        $comment_data["files"] = $files_data; //don't clean serilized data

        $comment_id = $this->Proposal_comments_model->ci_save($comment_data);
        if ($comment_id) {
            $comments_options = array("id" => $comment_id);
            $view_data['comment'] = $this->Proposal_comments_model->get_details($comments_options)->getRow();
            $comment_view = $this->template->view("proposals/comment_row", $view_data, true);

            echo json_encode(array("success" => true, "data" => $comment_view, 'message' => app_lang('comment_submited')));
            log_notification("proposal_commented", array("proposal_id" => $proposal_id, "proposal_comment_id" => $comment_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete proposal comments */

    function delete_comment($id = 0) {
        if (!$id) {
            exit();
        }

        $this->validate_proposal_access($id, true);

        $comment_info = $this->Proposal_comments_model->get_one($id);

        //only admin and creator can delete the comment
        if (!($this->login_user->is_admin || $comment_info->created_by == $this->login_user->id)) {
            redirect("forbidden");
        }

        //delete the comment and files
        if ($this->Proposal_comments_model->delete($id) && $comment_info->files) {

            //delete the files
            $file_path = get_setting("timeline_file_path");
            $files = unserialize($comment_info->files);

            foreach ($files as $file) {
                $source_path = $file_path . get_array_value($file, "file_name");
                delete_file_from_directory($source_path);
            }
        }
    }

    /* download files by zip */

    function download_comment_files($id) {
        validate_numeric_value($id);

        $files = $this->Proposal_comments_model->get_one($id)->files;
        return $this->download_app_files(get_setting("timeline_file_path"), $files);
    }
}

/* End of file Proposals.php */
/* Location: ./app/Controllers/Proposals.php */