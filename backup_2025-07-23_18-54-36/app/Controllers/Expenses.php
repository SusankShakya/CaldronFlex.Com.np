<?php

namespace App\Controllers;

use App\Libraries\Excel_import;
use App\Libraries\Dropdown_list;

class Expenses extends Security_Controller {

    use Excel_import;

    private $categories_id_by_title = array();
    private $projects_id_by_title = array();
    private $team_members_id_by_name = array();
    private $clients_id_by_title = array();
    private $taxes_id_by_percentage = array();

    function __construct() {
        parent::__construct();

        $this->init_permission_checker("expense");

        $this->access_only_allowed_members();
    }

    private function validate_expense_access($expense_id = 0) {
        if (!$this->can_access_this_expense($expense_id)) {
            app_redirect("forbidden");
        }
    }

    //load the expenses list view
    function index() {
        $this->check_module_availability("module_expense");

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("expenses", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("expenses", $this->login_user->is_admin, $this->login_user->user_type);

        $view_data['categories_dropdown'] = $this->_get_categories_dropdown();
        $members_dropdown = $this->Users_model->get_id_and_text_dropdown(array("first_name", "last_name"), array("deleted" => 0, "status" => "active", "user_type" => "staff"),  "- " . app_lang("member") . " -");
        $view_data['members_dropdown'] = json_encode($members_dropdown);
        $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_expenses("expenses");

        return $this->template->rander("expenses/index", $view_data);
    }

    //get categories dropdown
    private function _get_categories_dropdown() {
        $categories = $this->Expense_categories_model->get_all_where(array("deleted" => 0), 0, 0, "title")->getResult();

        $categories_dropdown = array(array("id" => "", "text" => "- " . app_lang("category") . " -"));
        foreach ($categories as $category) {
            $categories_dropdown[] = array("id" => $category->id, "text" => $category->title);
        }

        return json_encode($categories_dropdown);
    }

    //load the expenses list summary view
    function summary() {
        $this->check_module_availability("module_expense");

        return $this->template->rander("expenses/reports/expenses_summary");
    }

    //load the recurring view of expense list 
    function recurring() {
        return $this->template->view("expenses/recurring_expenses_list");
    }

    //load the add/edit expense form
    function modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $this->validate_expense_access($id);

        $client_id = $this->request->getPost('client_id');
        $project_id = $this->request->getPost('project_id');

        $model_info = $this->Expenses_model->get_one($id);

        $model_info->project_id = $model_info->project_id ? $model_info->project_id : $project_id;
        $model_info->client_id = $model_info->client_id ? $model_info->client_id : $client_id;
        $model_info->user_id = $model_info->user_id ? $model_info->user_id : $this->request->getPost('user_id');

        $view_data['categories_dropdown'] = $this->Expense_categories_model->get_dropdown_list(array("title"));

        $members_where = array("user_type" => "staff");
        if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
            $members_where["id"] = $this->login_user->id;
        }

        $view_data['members_dropdown'] = $this->Users_model->get_dropdown_list_with_blank_option(array("first_name", "last_name"), "-", $members_where);

        $dropdown_list = new Dropdown_list($this);
        $view_data['clients_dropdown'] = $dropdown_list->get_clients_id_and_text_dropdown(array("blank_option_text" => "-"));


        $project_info = null;

        if ($model_info->project_id) {
            $project_info = $this->Projects_model->get_details(array("id" => $model_info->project_id))->getRow();
        }

        if ($project_info) {
            //For project expense, show only the client of the project
            $view_data['clients_dropdown'] = json_encode(array(
                array("id" => "", "text" => "-"),
                array("id" => $project_info->client_id, "text" => $project_info->company_name)
            ));

            if ($project_info->client_id) {
                $view_data['projects_dropdown'] = array(
                    array("id" => "", "text" => "-"),
                    array("id" => $project_info->id, "text" => $project_info->title)
                );
            }
        }

        if (!isset($view_data['projects_dropdown'])) {
            $view_data['projects_dropdown'] = $this->get_projects_dropdown($model_info->client_id, true);
        }


        $view_data['taxes_dropdown'] = $this->Taxes_model->get_dropdown_list_with_blank_option(array("title"));

        $view_data['model_info'] = $model_info;
        $view_data['client_id'] = $model_info->client_id;
        $view_data['project_id'] = $model_info->project_id;

        //clone invoice
        $is_clone = $this->request->getPost('is_clone');
        $view_data['is_clone'] = $is_clone;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("expenses", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();
        return $this->template->view('expenses/modal_form', $view_data);
    }

    function get_projects_dropdown($client_id = 0, $return_as_list_data = false) {

        if (!$client_id) {
            $client_id = $this->request->getPost('expense_client_id');
        }

        validate_numeric_value($client_id);

        $options = array();
        if ($client_id) {
            $options["client_id"]  = $client_id;
        }

        $projects_dropdown = $this->Projects_model->get_id_and_text_dropdown(array("title"), $options, "-");

        if ($return_as_list_data) {
            return $projects_dropdown;
        } else {
            echo json_encode($projects_dropdown);
        }
    }


    //save an expense
    function save() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "expense_date" => "required",
            "category_id" => "required",
            "amount" => "required"
        ));

        $id = $this->request->getPost('id');
        $this->validate_expense_access($id);

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "expense");
        $new_files = unserialize($files_data);

        $recurring = $this->request->getPost('recurring') ? 1 : 0;
        $expense_date = $this->request->getPost('expense_date');
        $repeat_every = $this->request->getPost('repeat_every');
        $repeat_type = $this->request->getPost('repeat_type');
        $no_of_cycles = $this->request->getPost('no_of_cycles');

        $data = array(
            "expense_date" => $expense_date,
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "category_id" => $this->request->getPost('category_id'),
            "amount" => unformat_currency($this->request->getPost('amount')),
            "client_id" => $this->request->getPost('expense_client_id') ? $this->request->getPost('expense_client_id') : 0,
            "project_id" => $this->request->getPost('expense_project_id'),
            "user_id" => $this->request->getPost('expense_user_id'),
            "tax_id" => $this->request->getPost('tax_id') ? $this->request->getPost('tax_id') : 0,
            "tax_id2" => $this->request->getPost('tax_id2') ? $this->request->getPost('tax_id2') : 0,
            "recurring" => $recurring,
            "repeat_every" => $repeat_every ? $repeat_every : 0,
            "repeat_type" => $repeat_type ? $repeat_type : NULL,
            "no_of_cycles" => $no_of_cycles ? $no_of_cycles : 0,
        );

        if (!$id) {
            $data["created_by"] = $this->login_user->id;
        }

        $expense_info = $this->Expenses_model->get_one($id);

        //is editing? update the files if required
        if ($id) {
            $timeline_file_path = get_setting("timeline_file_path");
            $new_files = update_saved_files($timeline_file_path, $expense_info->files, $new_files);
        }

        $is_clone = $this->request->getPost('is_clone');

        if ($is_clone && $id) {
            $id = "";
        }

        if ($recurring) {
            //set next recurring date for recurring expenses

            if ($id) {
                //update
                if ($this->request->getPost('next_recurring_date')) { //submitted any recurring date? set it.
                    $data['next_recurring_date'] = $this->request->getPost('next_recurring_date');
                } else {
                    //re-calculate the next recurring date, if any recurring fields has changed.
                    if ($expense_info->recurring != $data['recurring'] || $expense_info->repeat_every != $data['repeat_every'] || $expense_info->repeat_type != $data['repeat_type'] || $expense_info->expense_date != $data['expense_date']) {
                        $data['next_recurring_date'] = add_period_to_date($expense_date, $repeat_every, $repeat_type);
                    }
                }
            } else {
                //insert new
                $data['next_recurring_date'] = add_period_to_date($expense_date, $repeat_every, $repeat_type);
            }


            //recurring date must have to set a future date
            if (get_array_value($data, "next_recurring_date") && get_today_date() >= $data['next_recurring_date']) {
                echo json_encode(array("success" => false, 'message' => app_lang('past_recurring_date_error_message_title'), 'next_recurring_date_error' => app_lang('past_recurring_date_error_message'), "next_recurring_date_value" => $data['next_recurring_date']));
                return false;
            }
        }

        $data = clean_data($data);

        $data["files"] = serialize($new_files);

        $save_id = $this->Expenses_model->ci_save($data, $id);
        if ($save_id) {
            save_custom_fields("expenses", $save_id, $this->login_user->is_admin, $this->login_user->user_type);

            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //delete/undo an expense
    function delete() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $this->validate_expense_access($id);
        $expense_info = $this->Expenses_model->get_one($id);

        if ($this->Expenses_model->delete($id)) {
            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($expense_info->files) {
                $files = unserialize($expense_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    //get the expnese list data
    function list_data($recurring = false) {
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        $category_id = $this->request->getPost('category_id');
        $project_id = $this->request->getPost('project_id');
        $user_id = $this->request->getPost('user_id');

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("expenses", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "start_date" => $start_date,
            "end_date" => $end_date,
            "category_id" => $category_id,
            "project_id" => $project_id,
            "user_id" => $user_id,
            "custom_fields" => $custom_fields,
            "recurring" => $recurring,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("expenses", $this->login_user->is_admin, $this->login_user->user_type),
            "show_own_expenses_only_user_id" => $this->show_own_expenses_only_user_id()
        );

        $list_data = $this->Expenses_model->get_details($options)->getResult();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of expnese list
    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("expenses", $this->login_user->is_admin, $this->login_user->user_type);
        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Expenses_model->get_details($options)->getRow();
        return $this->_make_row($data, $custom_fields);
    }

    //prepare a row of expnese list
    private function _make_row($data, $custom_fields) {

        $description = $data->description;
        if ($data->linked_client_name) {
            if ($description) {
                $description .= "<br />";
            }
            $description .= app_lang("client") . ": " . $data->linked_client_name;
        }

        if ($data->project_title) {
            if ($description) {
                $description .= "<br /> ";
            }
            $description .= app_lang("project") . ": " . $data->project_title;
        }

        if ($data->linked_user_name) {
            if ($description) {
                $description .= "<br /> ";
            }
            $description .= app_lang("team_member") . ": " . $data->linked_user_name;
        }

        if ($data->recurring) {
            //show recurring information
            $recurring_stopped = false;
            $recurring_cycle_class = "";
            if ($data->no_of_cycles_completed > 0 && $data->no_of_cycles_completed == $data->no_of_cycles) {
                $recurring_cycle_class = "text-danger";
                $recurring_stopped = true;
            }

            $cycles = $data->no_of_cycles_completed . "/" . $data->no_of_cycles;
            if (!$data->no_of_cycles) { //if not no of cycles, so it's infinity
                $cycles = $data->no_of_cycles_completed . "/&#8734;";
            }

            if ($description) {
                $description .= "<br /> ";
            }

            $description .= app_lang("repeat_every") . ": " . $data->repeat_every . " " . app_lang("interval_" . $data->repeat_type);
            $description .= "<br /> ";
            $description .= "<span class='$recurring_cycle_class'>" . app_lang("cycles") . ": " . $cycles . "</span>";

            if (!$recurring_stopped && (int) $data->next_recurring_date) {
                $description .= "<br /> ";
                $description .= app_lang("next_recurring_date") . ": " . format_to_date($data->next_recurring_date, false);
            }
        }

        if ($data->recurring_expense_id) {
            if ($description) {
                $description .= "<br /> ";
            }
            $description .= modal_anchor(get_uri("expenses/expense_details"), app_lang("original_expense"), array("title" => app_lang("expense_details"), "data-post-id" => $data->recurring_expense_id));
        }

        $files_link = "";
        $file_download_link = "";
        if ($data->files) {
            $files = unserialize($data->files);
            if (count($files)) {
                foreach ($files as $key => $value) {
                    $file_name = get_array_value($value, "file_name");
                    $link = get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                    $file_download_link = anchor(get_uri("expenses/download_files/" . $data->id), "<i data-feather='download'></i>", array("title" => app_lang("download")));
                    $files_link .= js_anchor("<i data-feather='$link'></i>", array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "class" => "float-start mr10", "title" => remove_file_prefix($file_name), "data-url" => get_uri("expenses/file_preview/" . $data->id . "/" . $key)));
                }
            }
        }

        $tax = 0;
        $tax2 = 0;
        if ($data->tax_percentage) {
            $tax = $data->amount * ($data->tax_percentage / 100);
        }
        if ($data->tax_percentage2) {
            $tax2 = $data->amount * ($data->tax_percentage2 / 100);
        }

        $row_data = array(
            $data->expense_date,
            modal_anchor(get_uri("expenses/expense_details"), format_to_date($data->expense_date, false), array("title" => app_lang("expense_details"), "data-post-id" => $data->id, "data-modal-lg" => "1")),
            $data->category_title,
            $data->title,
            $description,
            $files_link . $file_download_link,
            to_currency($data->amount),
            to_currency($tax),
            to_currency($tax2),
            to_currency($data->amount + $tax + $tax2)
        );

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
        }

        $row_data[] = modal_anchor(get_uri("expenses/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_expense'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_expense'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("expenses/delete"), "data-action" => "delete-confirmation"));

        return $row_data;
    }

    function file_preview($id = "", $key = "") {
        validate_numeric_value($id);
        if ($id) {
            $this->validate_expense_access($id);
            $expense_info = $this->Expenses_model->get_one($id);
            $files = unserialize($expense_info->files);
            $file = get_array_value($files, $key);

            $file_name = get_array_value($file, "file_name");
            $file_id = get_array_value($file, "file_id");
            $service_type = get_array_value($file, "service_type");

            $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
            $view_data["is_image_file"] = is_image_file($file_name);
            $view_data["is_iframe_preview_available"] = is_iframe_preview_available($file_name);
            $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
            $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
            $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;
            $view_data["is_iframe_preview_available"] = is_iframe_preview_available($file_name);

            return $this->template->view("expenses/file_preview", $view_data);
        } else {
            show_404();
        }
    }

    //load the expenses yearly chart view
    function yearly_chart() {
        return $this->template->view("expenses/reports/yearly_chart");
    }

    function yearly_chart_data() {

        $months = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");

        $year = $this->request->getPost("year");
        if ($year) {
            $expenses = $this->Expenses_model->get_yearly_expenses_data($year, 0, $this->show_own_expenses_only_user_id());
            $values = array();
            foreach ($expenses as $value) {
                $values[$value->month - 1] = $value->total; //in array the month january(1) = index(0)
            }

            foreach ($months as $key => $month) {
                $value = get_array_value($values, $key);
                $short_months[] = app_lang("short_" . $month);
                $data[] = $value ? $value : 0;
            }

            echo json_encode(array("months" => $short_months, "data" => $data));
        }
    }

    function income_vs_expenses() {
        $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_expenses();
        return $this->template->rander("expenses/reports/income_vs_expenses_chart", $view_data);
    }

    function income_vs_expenses_chart_data() {

        $year = $this->request->getPost("year");
        $project_id = $this->request->getPost("project_id");
        validate_numeric_value($year);
        validate_numeric_value($project_id);

        if ($year) {
            $expenses_array = $this->Expenses_model->get_yearly_expenses_chart_data($year, $project_id, $this->show_own_expenses_only_user_id());

            $options = array(
                "year" => $year,
                "project_id" => $project_id,
                "show_own_client_invoice_user_id" => $this->show_own_client_invoice_user_id(),
                "show_own_invoices_only_user_id" => $this->show_own_invoices_only_user_id()
            );

            $payments_array = $this->Invoice_payments_model->get_yearly_payments_chart_data($options);

            echo json_encode(array("income" => $payments_array, "expenses" => $expenses_array));
        }
    }

    function income_vs_expenses_summary() {
        $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_expenses();
        return $this->template->view("expenses/reports/income_vs_expenses_summary", $view_data);
    }

    function income_vs_expenses_summary_list_data() {

        $year = explode("-", $this->request->getPost("start_date"));
        $project_id = $this->request->getPost("project_id");
        validate_numeric_value($project_id);

        if ($year) {
            $expenses_data = $this->Expenses_model->get_yearly_expenses_data($year[0], $project_id, $this->show_own_expenses_only_user_id());

            $options = array(
                "year" => $year[0],
                "project_id" => $project_id,
                "show_own_client_invoice_user_id" => $this->show_own_client_invoice_user_id(),
                "show_own_invoices_only_user_id" => $this->show_own_invoices_only_user_id()
            );

            $payments_data = $this->Invoice_payments_model->get_yearly_payments_data($options);

            $payments = array();
            $expenses = array();

            for ($i = 1; $i <= 12; $i++) {
                $payments[$i] = 0;
                $expenses[$i] = 0;
            }

            foreach ($payments_data as $payment) {
                $payments[$payment->month] = $payments[$payment->month] + get_converted_amount($payment->currency, $payment->total);
            }
            foreach ($expenses_data as $expense) {
                $expenses[$expense->month] = $expense->total;
            }

            //get the list of summary
            $result = array();
            for ($i = 1; $i <= 12; $i++) {
                $result[] = $this->_row_data_of_summary($i, $payments[$i], $expenses[$i]);
            }

            echo json_encode(array("data" => $result));
        }
    }

    //get the row of summary
    private function _row_data_of_summary($month_index, $payments, $expenses) {
        //get the month name
        $month_array = array(" ", "january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");

        $month = get_array_value($month_array, $month_index);

        $month_name = app_lang($month);
        $profit = $payments - $expenses;

        return array(
            $month_index,
            $month_name,
            to_currency($payments),
            to_currency($expenses),
            to_currency($profit)
        );
    }

    /* list of expense of a specific client, prepared for datatable  */

    function expense_list_data_of_client($client_id) {
        $this->access_only_team_members();
        validate_numeric_value($client_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("expenses", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "client_id" => $client_id,
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("expenses", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $list_data = $this->Expenses_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    function expense_details() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $expense_id = $this->request->getPost('id');
        $this->validate_expense_access($expense_id);

        $options = array("id" => $expense_id);
        $info = $this->Expenses_model->get_details($options)->getRow();
        if (!$info) {
            show_404();
        }

        $view_data["expense_info"] = $info;
        $view_data['custom_fields_list'] = $this->Custom_fields_model->get_combined_details("expenses", $expense_id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        return $this->template->view("expenses/expense_details", $view_data);
    }

    //get the expneses summary list data
    function summary_list_data() {
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');

        $options = array(
            "start_date" => $start_date,
            "end_date" => $end_date,
            "show_own_expenses_only_user_id" => $this->show_own_expenses_only_user_id()
        );

        $list_data = $this->Expenses_model->get_summary_details($options)->getResult();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = array(
                $data->category_title,
                to_currency($data->amount),
                to_currency($data->tax),
                to_currency($data->tax2),
                to_currency($data->amount + $data->tax + $data->tax2)
            );
        }

        echo json_encode(array("data" => $result));
    }

    /* download files */

    function download_files($id) {
        validate_numeric_value($id);
        $this->validate_expense_access($id);

        $files = $this->Expenses_model->get_one($id)->files;
        return $this->download_app_files(get_setting("timeline_file_path"), $files);
    }

    private function _validate_excel_import_access() {
        return $this->access_only_allowed_members();
    }

    private function _get_controller_slag() {
        return "expenses";
    }

    private function _get_custom_field_context() {
        return "expenses";
    }

    private function _get_headers_for_import() {
        $this->_init_required_data_before_starting_import();

        return array(
            array("name" => "date", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("date")), "custom_validation" => function ($date, $row_data) {
                if (!$this->_check_valid_date($date)) {
                    return app_lang("import_date_error_message");
                }
            }),
            array("name" => "category", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("category"))),
            array("name" => "title"),
            array("name" => "description"),
            array("name" => "amount", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("amount"))),
            array("name" => "project", "custom_validation" => function ($project) {
                //chek if the project exist or not
                if ($project) {
                    $project_id = get_array_value($this->projects_id_by_title, strtolower(trim($project)));
                    if (!$project_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("project")));
                    }
                }
            }),
            array("name" => "team_member", "custom_validation" => function ($team_member) {
                //chek if the team member exist or not
                if ($team_member) {
                    $team_member_id = get_array_value($this->team_members_id_by_name, trim($team_member));
                    if (!$team_member_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("team_member")));
                    }
                }
            }),
            array("name" => "client", "custom_validation" => function ($client) {
                //chek if the team member exist or not
                if ($client) {
                    $client_id = get_array_value($this->clients_id_by_title, $client);
                    if (!$client_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("client")));
                    }
                }
            }),
            array("name" => "tax", "custom_validation" => function ($tax) {
                //chek if the team member exist or not
                if ($tax) {
                    $tax_id = get_array_value($this->taxes_id_by_percentage, $tax);
                    if (!$tax_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("tax")));
                    }
                }
            }),
            array("name" => "second_tax", "custom_validation" => function ($second_tax) {
                //chek if the team member exist or not
                if ($second_tax) {
                    $tax_id = get_array_value($this->taxes_id_by_percentage, $second_tax);
                    if (!$tax_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("tax")));
                    }
                }
            })
        );
    }

    function download_sample_excel_file() {
        $this->access_only_allowed_members();
        return $this->download_app_files(get_setting("system_file_path"), serialize(array(array("file_name" => "import-expenses-sample.xlsx"))));
    }

    private function _init_required_data_before_starting_import() {
        $categories = $this->Expense_categories_model->get_details()->getResult();
        $categories_id_by_title = array();
        foreach ($categories as $category) {
            $categories_id_by_title[$category->title] = $category->id;
        }

        $projects = $this->Projects_model->get_projects_id_and_name()->getResult();
        $projects_id_by_title = array();
        foreach ($projects as $project) {
            $projects_id_by_title[strtolower($project->title)] = $project->id;
        }

        $team_members = $this->Users_model->get_team_members_id_and_name()->getResult();
        $team_members_id_by_name = array();
        foreach ($team_members as $team_member) {
            $team_members_id_by_name[$team_member->user_name] = $team_member->id;
        }

        $clients = $this->Clients_model->get_clients_id_and_name()->getResult();
        $clients_id_by_title = array();
        foreach ($clients as $client) {
            $clients_id_by_title[$client->name] = $client->id;
        }

        $taxes = $this->Taxes_model->get_details()->getResult();
        $taxes_id_by_percentage = array();
        foreach ($taxes as $tax) {
            $taxes_id_by_percentage[$tax->percentage] = $tax->id;
        }

        $this->categories_id_by_title = $categories_id_by_title;
        $this->projects_id_by_title = $projects_id_by_title;
        $this->team_members_id_by_name = $team_members_id_by_name;
        $this->clients_id_by_title = $clients_id_by_title;
        $this->taxes_id_by_percentage = $taxes_id_by_percentage;
    }

    private function _save_a_row_of_excel_data($row_data) {
        $expense_data_array = $this->_prepare_expense_data($row_data);
        $expense_data = get_array_value($expense_data_array, "expense_data");
        $custom_field_values_array = get_array_value($expense_data_array, "custom_field_values_array");

        //couldn't prepare valid data
        if (!($expense_data && count($expense_data) > 1)) {
            return false;
        }

        //save expense data
        $saved_id = $this->Expenses_model->ci_save($expense_data);
        if (!$saved_id) {
            return false;
        }

        //save custom fields
        $this->_save_custom_fields($saved_id, $custom_field_values_array);
        return true;
    }

    private function _prepare_expense_data($row_data) {

        $expense_data = array();
        $custom_field_values_array = array();

        foreach ($row_data as $column_index => $value) {
            if (!$value) {
                continue;
            }

            $column_name = $this->_get_column_name($column_index);
            if ($column_name == "date") {
                $expense_data["expense_date"] = $this->_check_valid_date($value);
            } else if ($column_name == "category") {
                $category_id = get_array_value($this->categories_id_by_title, $value);
                if ($category_id) {
                    $expense_data["category_id"] = $category_id;
                } else {
                    $category_data = array("title" => $value);
                    $saved_category_id = $this->Expense_categories_model->ci_save($category_data);
                    $expense_data["category_id"] = $saved_category_id;
                    $this->categories_id_by_title[$value] = $saved_category_id;
                }
            } else if ($column_name == "project") {
                $project_id = get_array_value($this->projects_id_by_title, strtolower(trim($value)));
                if ($project_id) {
                    $expense_data["project_id"] = $project_id;
                }
            } else if ($column_name == "team_member") {
                $team_member_id = get_array_value($this->team_members_id_by_name, $value);
                if ($team_member_id) {
                    $expense_data["user_id"] = $team_member_id;
                }
            } else if ($column_name == "client") {
                $client_id = get_array_value($this->clients_id_by_title, $value);
                if ($client_id) {
                    $expense_data["client_id"] = $client_id;
                }
            } else if ($column_name == "tax") {
                $tax_id = get_array_value($this->taxes_id_by_percentage, $value);
                if ($tax_id) {
                    $expense_data["tax_id"] = $tax_id;
                }
            } else if ($column_name == "second_tax") {
                $tax_id = get_array_value($this->taxes_id_by_percentage, $value);
                if ($tax_id) {
                    $expense_data["tax_id2"] = $tax_id;
                }
            } else if (strpos($column_name, 'cf') !== false) {
                $this->_prepare_custom_field_values_array($column_name, $value, $custom_field_values_array);
            } else {
                $expense_data[$column_name] = $value;
            }
        }

        return array(
            "expense_data" => $expense_data,
            "custom_field_values_array" => $custom_field_values_array
        );
    }

    /* load tasks tab  */

    function tasks($expense_id) {
        validate_numeric_value($expense_id);
        $view_data["custom_field_headers_of_task"] = $this->Custom_fields_model->get_custom_field_headers_for_table("tasks", $this->login_user->is_admin, $this->login_user->user_type);

        $view_data['expense_id'] = clean_data($expense_id);
        return $this->template->view("expenses/tasks/index", $view_data);
    }

    //load the expenses monthly summary view
    function monthly_summary() {
        return $this->template->view("expenses/reports/monthly_summary");
    }

    //load the expenses custom summary view
    function custom_summary() {
        return $this->template->view("expenses/reports/custom_summary");
    }

    //load the expenses category chart view
    function category_chart() {
        return $this->template->view("expenses/reports/category_chart_container");
    }

    function category_chart_view() {
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');

        $options = array(
            "start_date" => $start_date,
            "end_date" => $end_date,
            "show_own_expenses_only_user_id" => $this->show_own_expenses_only_user_id()
        );

        $categories_data = $this->Expenses_model->get_summary_details($options)->getResult();
        $category_title = array();
        $category_value = array();

        foreach ($categories_data as $category_data) {
            $category_title[] = $category_data->category_title;
            $category_value[] = $category_data->amount + $category_data->tax + $category_data->tax2;
        }

        $view_data["label"] = $category_title;
        $view_data["data"] = $category_value;


        return $this->template->view("expenses/reports/category_chart", $view_data);
    }
}

/* End of file expenses.php */
/* Location: ./app/controllers/expenses.php */