<?php

namespace App\Controllers;

class Todo extends Security_Controller {

    function __construct() {
        parent::__construct();
    }

    protected function validate_access($todo_info) {
        if ($this->login_user->id !== $todo_info->created_by) {
            app_redirect("forbidden");
        }
    }

    //load todo list view
    function index() {
        $this->check_module_availability("module_todo");

        return $this->template->rander("todo/index");
    }

    function modal_form() {
        $id = $this->request->getPost('id');
        validate_numeric_value($id);

        $view_data['model_info'] = $this->Todo_model->get_one($id);

        //check permission for saved todo list
        if ($view_data['model_info']->id) {
            $this->validate_access($view_data['model_info']);
        }

        $view_data['label_suggestions'] = $this->make_labels_dropdown("to_do", $view_data['model_info']->labels);
        return $this->template->view('todo/modal_form', $view_data);
    }

    function save($is_mobile = 0) {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required"
        ));

        $id = $this->request->getPost('id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "todo");
        $new_files = unserialize($files_data);

        $view_type = $this->request->getPost('view_type');
        if ($view_type === "responsive") {
            $is_mobile = 1;
        }

        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description') ? $this->request->getPost('description') : "",
            "created_by" => $this->login_user->id,
            "labels" => $this->request->getPost('labels') ? $this->request->getPost('labels') : "",
            "start_date" => $this->request->getPost('start_date'),
        );

        if (!$id) {
            $data["sort"] = $this->Todo_model->get_next_sort_value($this->login_user->id);
        }

        $data = clean_data($data);

        //set null value after cleaning the data
        if (!$data["start_date"]) {
            $data["start_date"] = NULL;
        }

        if ($id) {
            //saving existing todo. check permission
            $todo_info = $this->Todo_model->get_one($id);

            $this->validate_access($todo_info);

            $new_files = update_saved_files($target_path, $todo_info->files, $new_files);
        } else {
            $data['created_at'] = get_current_utc_time();
        }

        $data["files"] = serialize($new_files);

        $save_id = $this->Todo_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id, $is_mobile), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* upadate a task status */

    function save_status($is_mobile = 0) {

        $this->validate_submitted_data(array(
            "id" => "numeric|required",
            "status" => "required"
        ));

        $todo_info = $this->Todo_model->get_one($this->request->getPost('id'));
        $this->validate_access($todo_info);

        $data = array(
            "status" => $this->request->getPost('status')
        );

        $data = clean_data($data);

        $save_id = $this->Todo_model->ci_save($data, $this->request->getPost('id'));

        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id, $is_mobile), 'id' => $save_id, "message" => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }
    }

    function delete() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $todo_info = $this->Todo_model->get_one($id);
        $this->validate_access($todo_info);

        if ($this->request->getPost('undo')) {
            if ($this->Todo_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Todo_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    function list_data($is_mobile = 0) {
        validate_numeric_value($is_mobile);

        $status = $this->request->getPost('status');
        $options = array(
            "created_by" => $this->login_user->id,
            "status" => $status,
            "id" => get_only_numeric_value($this->request->getPost('id'))
        );

        $list_data = $this->Todo_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $is_mobile);
        }
        echo json_encode(array("data" => $result));
    }

    private function _row_data($id, $is_mobile = 0) {
        validate_numeric_value($is_mobile);

        $options = array("id" => $id);
        $data = $this->Todo_model->get_details($options)->getRow();

        return $this->_make_row($data, $is_mobile);
    }

    private function _make_row($data, $is_mobile = 0) {
        $todo_title = modal_anchor(get_uri("todo/view/" . $data->id), $data->title, array("class" => "edit todo-row", "title" => app_lang('todo'), "data-post-id" => $data->id, "data-sort-value" => $data->sort, "data-id" => $data->id));
        $title = $todo_title;

        if ($data->description) {
            $title .= "<div class='truncate-ellipsis w-75 text-off'><span>" . strip_tags($data->description) . "</span></div>";
        }

        $todo_labels = "";
        if ($data->labels_list) {
            $todo_labels = make_labels_view_data($data->labels_list, true);
            $title .= "<span>" . $todo_labels . "</span>";
        }

        $files_label = "";
        if ($data->files) {
            $files = unserialize($data->files);
            if (count($files)) {
                $files_label = "<span class='ml10'><i data-feather='paperclip' class='icon-14 mt-1'></i></span>";
                $title .= $files_label;
            }
        }

        $status_class = "";
        $checkbox_class = "checkbox-blank mr15";
        if ($data->status === "to_do") {
            $status_class = "b-warning todo-row-$data->id";
        } else {
            $checkbox_class = "checkbox-checked mr15";
            $status_class = "b-success todo-row-$data->id";
        }

        $hide_class = "";
        if ($is_mobile) {
            $hide_class = "hide";
        }

        $check_status = js_anchor("<span class='$checkbox_class'></span>", array('title' => "", "class" => "float-start update-todo-status-checkbox", "data-id" => $data->id, "data-value" => $data->status === "done" ? "to_do" : "done", "data-act" => "update-todo-status-checkbox"));
        $move_icon = "<div class='float-start move-icon $hide_class'><i data-feather='menu' class='icon-20'></i></div>";

        $start_date_text = "";
        if (is_date_exists($data->start_date)) {
            $start_date_text = "<span class='mr5'>" . format_to_date($data->start_date, false) . "</span> ";
            if (get_my_local_time("Y-m-d") > $data->start_date && $data->status != "done") {
                $start_date_text = "<span class='text-danger mr5'>" . $start_date_text . "</span> ";
            } else if (get_my_local_time("Y-m-d") == $data->start_date && $data->status != "done") {
                $start_date_text = "<span class='text-warning mr5'>" . $start_date_text . "</span> ";
            }

            // show expired, today, tomorrow, in 7 days labels based on start date if status is not done
            if ($data->status != "done") {
                $today = get_today_date();
                if ($data->start_date < $today) {
                    $start_date_text .= "<span class='badge rounded-pill text-default b-a clickable'>" . app_lang("expired") . "</span>";
                } else if ($data->start_date == $today) {
                    $start_date_text .= "<span class='badge rounded-pill text-default b-a clickable'>" . app_lang("today") . "</span>";
                } else if ($data->start_date == add_period_to_date($today, "1")) {
                    $start_date_text .= "<span class='badge rounded-pill text-default b-a clickable'>" . app_lang("tomorrow") . "</span>";
                } else if ($data->start_date <= add_period_to_date($today, "7")) {
                    $start_date_text .= "<span class='badge rounded-pill text-default b-a clickable'>" . sprintf(app_lang("in_number_of_days"), 7) . "</span>";
                }
            }
        }

        if ($is_mobile) {
            $actions = "<span class='dropdown inline-block'>
                            <button class='action-option dropdown-toggle mt0 mb0' type='button' data-bs-toggle='dropdown' aria-expanded='true' data-bs-display='static'>
                                <i data-feather='more-horizontal' class='icon-16'></i>
                            </button>
                            <ul class='dropdown-menu dropdown-menu-end' role='menu'>
                                <li role='presentation'>" . modal_anchor(get_uri("todo/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit'), array("class" => "dropdown-item", "title" => app_lang('edit'), "data-post-id" => $data->id)) . "</li>
                                <li role='presentation'>" . js_anchor("<i data-feather='x' class='icon-16'></i>" . app_lang('delete'), array('title' => app_lang('delete'), "class" => "dropdown-item", "data-id" => $data->id, "data-action-url" => get_uri("todo/delete"), "data-action" => "delete")) . "</li>
                            </ul>
                        </span>";

            $title = "<div class='box-wrapper'>
                        <div class='align-self-center d-inline-flex'>" . $move_icon . $check_status . "</div>
                        <div class='box-label'>
                            <div>" . $todo_title . "</div>
                            <div class='text-off text-wrap-ellipsis w80p'>" . strip_tags($data->description) . "</div>
                            <div class=''>" . $start_date_text . "</div>
                            <div>" . $todo_labels . $files_label . "</div>
                            <div class='position-absolute end10'>" . $actions . "</div>
                        </div>
                    </div>";
        }

        return array(
            $status_class,
            $data->sort,
            $is_mobile ? $title : "<i class='hide'>" . $data->id . "</i>" . $move_icon . $check_status,
            $title,
            $data->start_date,
            $start_date_text,
            modal_anchor(get_uri("todo/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("todo/delete"), "data-action" => "delete"))
        );
    }

    function update_sort_value() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric",
            "sort" => "required|numeric"
        ));

        $id = $this->request->getPost("id");
        $todo_info = $this->Todo_model->get_one($id);
        $this->validate_access($todo_info);

        $sort = $this->request->getPost("sort");
        $data = array(
            "sort" => $sort
        );

        $data = clean_data($data);

        $this->Todo_model->ci_save($data, $id);
    }

    function view() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $model_info = $this->Todo_model->get_details(array("id" => $this->request->getPost('id')))->getRow();

        $this->validate_access($model_info);

        $view_data['model_info'] = $model_info;

        $view_data['label_suggestions'] = $this->make_labels_dropdown("to_do");

        return $this->template->view('todo/view', $view_data);
    }

    function update_todo_info($id = 0, $data_field = "") {
        if (!$id) {
            return false;
        }

        validate_numeric_value($id);
        $todo_info = $this->Todo_model->get_one($id);
        $this->validate_access($todo_info);

        $value = $this->request->getPost('value');
        if ($data_field == "labels") {
            validate_list_of_numbers($value);
        }

        $data = array(
            $data_field => $value
        );

        $data = clean_data($data);

        $save_id = $this->Todo_model->ci_save($data, $id);
        if (!$save_id) {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
            return false;
        }

        $todo_info = $this->Todo_model->get_details(array("id" => $save_id))->getRow(); // get data after save

        $success_array = array("success" => true, 'id' => $save_id, "message" => app_lang('record_saved'));

        if ($data_field == "labels") {
            $success_array["content"] = $todo_info->labels_list ? make_labels_view_data($todo_info->labels_list) : "<span class='text-off'>" . app_lang("add") . " " . app_lang("label") . "<span>";
        }

        if ($data_field == "start_date") {
            $date = "-";
            if (is_date_exists($todo_info->$data_field)) {
                $date = format_to_date($todo_info->$data_field, false);
            }
            $success_array["content"] = $date;
        }

        echo json_encode($success_array);
    }
}

/* End of file todo.php */
/* Location: ./app/controllers/todo.php */