<?php

namespace App\Controllers;

class Notes extends Security_Controller {

    protected $Note_category_model;

    function __construct() {
        parent::__construct();

        $this->Note_category_model = model('App\Models\Note_category_model');
    }

    protected function validate_access_to_note($note_info, $edit_mode = false) {

        if ($note_info->is_public) {
            //it's a public note. visible to all available users
            if ($edit_mode) {
                //for edit mode, only creator and admin can access
                if ($this->login_user->id !== $note_info->created_by && !$this->login_user->is_admin) {
                    app_redirect("forbidden");
                }
            }
        } else {
            if ($note_info->client_id) {
                //this is a client/lead note. check access permission
                $client_info = $this->Clients_model->get_one($note_info->client_id);

                if ($client_info->is_lead) {
                    if (!$this->can_access_this_lead($note_info->client_id)) {
                        app_redirect("forbidden");
                    }
                } else {
                    if (!$this->can_view_clients($note_info->client_id, $edit_mode)) {
                        app_redirect("forbidden");
                    }
                }
            } else if ($note_info->user_id) {
                //this is a user's note. check user's access permission.
                if (!$this->can_access_team_members_note($note_info->user_id)) {
                    app_redirect("forbidden");
                }
            } else {
                //this is a private note. only available to creator
                if ($this->login_user->id !== $note_info->created_by) {
                    app_redirect("forbidden");
                }
            }
        }
    }

    private function can_access_notes() {
        $this->check_module_availability("module_note");

        if ($this->login_user->user_type == "client") {
            if (!get_setting("client_can_access_notes")) {
                app_redirect("forbidden");
            }
        }
    }

    //load note list view
    function index() {
        $this->can_access_notes();

        $options = array("user_id" => $this->login_user->id);
        $note_categories = $this->Note_category_model->get_details($options)->getResult();
        $note_categories_dropdown = array(array("id" => "", "text" => "- " . app_lang("category") . " -"));

        if ($note_categories) {
            foreach ($note_categories as $note_category) {
                $note_categories_dropdown[] = array("id" => $note_category->id, "text" => $note_category->name);
            }
        }

        $view_data["note_categories_dropdown"] = json_encode($note_categories_dropdown);

        $view_data['labels_dropdown'] = json_encode($this->make_labels_dropdown("note", "", true));

        return $this->template->rander("notes/index", $view_data);
    }

    function modal_form() {
        $this->can_access_notes();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Notes_model->get_one($this->request->getPost('id'));
        $view_data['project_id'] = $this->request->getPost('project_id') ? $this->request->getPost('project_id') : $view_data['model_info']->project_id;
        $view_data['client_id'] = $this->request->getPost('client_id') ? $this->request->getPost('client_id') : $view_data['model_info']->client_id;
        $view_data['user_id'] = $this->request->getPost('user_id') ? $this->request->getPost('user_id') : $view_data['model_info']->user_id;

        //check permission for saved note
        if ($view_data['model_info']->id) {
            $this->validate_access_to_note($view_data['model_info'], true);
        }

        $view_data['label_suggestions'] = $this->make_labels_dropdown("note", $view_data['model_info']->labels, false);

        $note_categories = $this->Note_category_model->get_details(array("user_id" => $this->login_user->id))->getResult();
        $note_categories_dropdown = array("" => "- " . app_lang("category") . " -");

        if ($note_categories) {
            foreach ($note_categories as $note_category) {
                $note_categories_dropdown[$note_category->id] = $note_category->name;
            }
        }

        $view_data["note_categories_dropdown"] = $note_categories_dropdown;

        return $this->template->view('notes/modal_form', $view_data);
    }

    function save() {
        $this->can_access_notes();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "project_id" => "numeric",
            "client_id" => "numeric",
            "user_id" => "numeric",
            "is_public" => "numeric",
            "category_id" => "numeric"
        ));

        $id = $this->request->getPost('id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "note");
        $new_files = unserialize($files_data);

        $labels = $this->request->getPost('labels');
        validate_list_of_numbers($labels);

        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            
            "labels" => $labels,
            "color" => $this->request->getPost('color'),
            "project_id" => $this->request->getPost('project_id') ? $this->request->getPost('project_id') : 0,
            "client_id" => $this->request->getPost('client_id') ? $this->request->getPost('client_id') : 0,
            "user_id" => $this->request->getPost('user_id') ? $this->request->getPost('user_id') : 0,
            "is_public" => $this->request->getPost('is_public') ? $this->request->getPost('is_public') : 0,
            "category_id" => $this->request->getPost('category_id') ? $this->request->getPost('category_id') : 0
        );
        $data = clean_data($data);
        
        if ($id) {
            $note_info = $this->Notes_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $note_info->files, $new_files);
        }

        
        $data["files"] = serialize($new_files);

        if ($id) {
            //saving existing note. check permission
            $note_info = $this->Notes_model->get_one($id);

            $this->validate_access_to_note($note_info, true);
        } else {
            $data['created_by'] = $this->login_user->id;
            $data['created_at'] = get_current_utc_time();
        }

        $save_id = $this->Notes_model->ci_save($data, $id);

        $data = $this->_row_data($save_id);
        $is_grid = $this->request->getPost('is_grid') ? true : false;
        if ($is_grid) {
            $note_info = $this->Notes_model->get_details(array("id" => $save_id))->getRow();
            $data = view("notes/grid/note", array("note" => $note_info, "data_only" => true));
        }

        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $data, 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function delete() {
        $this->can_access_notes();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $note_info = $this->Notes_model->get_one($id);
        $this->validate_access_to_note($note_info, true);

        if ($this->Notes_model->delete($id)) {
            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($note_info->files) {
                $files = unserialize($note_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    function list_data($type = "", $id = 0, $is_mobile = 0) {
        $this->can_access_notes();

        validate_numeric_value($id);

        $this->validate_submitted_data(array(
            "category_id" => "numeric"
        ));

        $options = array(
            "category_id" => $this->request->getPost("category_id"),
            "label_id" => $this->request->getPost('label_id'),
        );

        if ($type == "project" && $id) {
            $options["created_by"] = $this->login_user->id;
            $options["project_id"] = $id;
        } else if ($type == "client" && $id) {
            $options["client_id"] = $id;
        } else if ($type == "user" && $id) {
            $options["user_id"] = $id;
        } else {
            $options["created_by"] = $this->login_user->id;
            $options["my_notes"] = true;
        }

        $list_data = $this->Notes_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $is_mobile);
        }
        echo json_encode(array("data" => $result));
    }

    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Notes_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    private function _make_row($data, $is_mobile = 0) {
        $public_icon = "";
        if ($data->is_public) {
            $public_icon = "<i data-feather='globe' class='icon-16'></i> ";
        }

        $title_color_tag = "<span class='note-color-tag' style='background-color: " . ($data->color ? $data->color : "#83c340") . "'></span>";
        $title = $title_color_tag . modal_anchor(get_uri("notes/view/" . $data->id), $public_icon . $data->title, array("title" => app_lang('note'), "data-post-id" => $data->id, "class" => "text-break-space w250"));

        $note_labels = "";
        if ($data->labels_list) {
            if ($is_mobile) {
                $note_labels = make_labels_view_data($data->labels_list);
            } else {
                $note_labels = make_labels_view_data($data->labels_list, true);
            }

            $title .= "<div>" . $note_labels . "</div>";
        }

        $files_link = "";
        $file_download_link = "";
        if ($data->files) {
            $files = unserialize($data->files);
            if (count($files)) {
                foreach ($files as $key => $value) {
                    $file_name = get_array_value($value, "file_name");
                    $link = get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                    $file_download_link = anchor(get_uri("notes/download_files/" . $data->id), "<i data-feather='download-cloud' class='icon-14'></i>", array("title" => app_lang("download"), "class" => "file-list-view file-download"));
                    $files_link .= js_anchor("<i data-feather='$link' class='icon-14'></i>", array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "class" => "file-list-view", "title" => remove_file_prefix($file_name), "data-url" => get_uri("notes/file_preview/" . $data->id . "/" . $key)));
                }
            }
        }

        //only creator and admin can edit/delete notes
        $actions = modal_anchor(get_uri("notes/view/" . $data->id), "<i data-feather='cloud-lightning' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('note_details'), "data-modal-title" => app_lang("note"), "data-post-id" => $data->id));
        if ($data->created_by == $this->login_user->id || $this->login_user->is_admin || $data->client_id) {
            $actions = modal_anchor(get_uri("notes/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_note'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_note'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("notes/delete"), "data-action" => "delete-confirmation"));
        }

        if ($is_mobile) {
            $image_url = get_avatar($data->created_by_avatar);
            $created_by_avatar = "<span class='avatar avatar-xs'><img src='$image_url' alt='...'></span>";

            $title = "<div class='box-wrapper'>
            <div class='box-avatar hover'>$created_by_avatar</div>" .
                modal_anchor(
                    get_uri("notes/view/" . $data->id),
                    "<div class='text-default'><span class='text-wrap strong'>" . $title_color_tag . $data->title . "</span>
                    <div class='mt5 mt5 truncate-ellipsis w-75'><span>" . strip_tags($data->description) . "</span></div>
                    <span class='text-off'>" . format_to_relative_time($data->created_at, true, false, true) . "</span>
                    <span class='float-end'>" . $note_labels . "</span></div>",
                    array(
                        "title" => app_lang('note'),
                        "class" => "box-label",
                        "data-post-id" => $data->id
                    )
                ) .
                "</div>";
        } else {
            $title = $title;
        }

        return array(
            $data->created_at,
            format_to_relative_time($data->created_at),
            $title,
            $data->category_name ? $data->category_name : "-",
            $file_download_link . $files_link,
            $actions
        );
    }

    function view() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $model_info = $this->Notes_model->get_details(array("id" => $this->request->getPost('id')))->getRow();

        $this->validate_access_to_note($model_info);

        $view_data['model_info'] = $model_info;
        return $this->template->view('notes/view', $view_data);
    }

    function file_preview($id = "", $key = "") {
        if ($id) {
            validate_numeric_value($id);
            $note_info = $this->Notes_model->get_one($id);
            $this->validate_access_to_note($note_info);

            $files = unserialize($note_info->files);
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

            return $this->template->view("notes/file_preview", $view_data);
        } else {
            show_404();
        }
    }

    /* download files */

    function download_files($id) {
        validate_numeric_value($id);

        $note_info = $this->Notes_model->get_one($id);
        $this->validate_access_to_note($note_info);

        $files = $note_info->files;
        return $this->download_app_files(get_setting("timeline_file_path"), $files);
    }

    function categories() {
        $this->can_access_notes();
        return $this->template->rander("notes/category/index");
    }


    function category_list_data() {
        $this->can_access_notes();
        $options = array("user_id" => $this->login_user->id);
        $list_data = $this->Note_category_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_category_row($data);
        }

        echo json_encode(array("data" => $result));
    }

    private function _category_row_data($id) {
        $options = array("id" => $id);
        $data = $this->Note_category_model->get_details($options)->getRow();

        return $this->_make_category_row($data);
    }

    private function _make_category_row($data) {
        $options = modal_anchor(get_uri("notes/category_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_category'), "data-post-id" => $data->id));
        $options .= js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_category'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("notes/delete_category"), "data-action" => "delete"));

        return array(
            $data->name,
            $options
        );
    }

    function category_modal_form() {
        $this->can_access_notes();
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        if ($id && !$this->can_access_this_category($id)) {
            app_redirect("forbidden");
        }

        $view_data['model_info'] = $this->Note_category_model->get_one($id);
        return $this->template->view('notes/category/modal_form', $view_data);
    }

    private function can_access_this_category($category_id) {
        return $this->Note_category_model->get_one($category_id)->user_id == $this->login_user->id;
    }

    function save_category() {
        $this->can_access_notes();
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost("id");
        if ($id && !$this->can_access_this_category($id)) {
            app_redirect("forbidden");
        }

        $data = array(
            "name" => $this->request->getPost('name')
        );

        if (!$id) {
            $data["user_id"] = $this->login_user->id;
        }

        $save_id = $this->Note_category_model->ci_save($data, $id);

        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_category_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {

            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function delete_category() {
        $this->can_access_notes();
        $id = $this->request->getPost('id');
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        if ($id && !$this->can_access_this_category($id)) {
            app_redirect("forbidden");
        }

        if ($this->request->getPost('undo')) {
            if ($this->Note_category_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_category_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Note_category_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    function grid() {
        $this->can_access_notes();

        $options = array("user_id" => $this->login_user->id);
        $note_categories = $this->Note_category_model->get_details($options)->getResult();
        $note_categories_dropdown = array(array("id" => "", "text" => "- " . app_lang("category") . " -"));

        if ($note_categories) {
            foreach ($note_categories as $note_category) {
                $note_categories_dropdown[] = array("id" => $note_category->id, "text" => $note_category->name);
            }
        }

        $view_data["note_categories_dropdown"] = json_encode($note_categories_dropdown);

        $view_data['labels_dropdown'] = json_encode($this->make_labels_dropdown("note", "", true));

        return $this->template->rander("notes/grid/index", $view_data);
    }

    function grid_data() {
        $this->can_access_notes();
        $this->validate_submitted_data(array(
            "category_id" => "numeric"
        ));

        $options = array(
            "category_id" => $this->request->getPost("category_id"),
            "label_id" => $this->request->getPost('label_id'),
            "created_by" => $this->login_user->id, //the grid view is only available on personal private notes for now
            "my_notes" => true,
            "search" => $this->request->getPost('search')
        );

        $view_data["notes"] = $this->Notes_model->get_details($options)->getResult();

        return $this->template->view('notes/grid/grid_view', $view_data);
    }
}

/* End of file Notes.php */
/* Location: ./app/Controllers/Notes.php */