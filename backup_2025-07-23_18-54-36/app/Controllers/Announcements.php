<?php

namespace App\Controllers;

class Announcements extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("announcement");
    }

    //show announcements list
    function index() {
        $this->check_module_availability("module_announcement");

        if ($this->login_user->user_type === "client") {
            if (!$this->can_client_access("announcement")) {
                app_redirect("forbidden");
            }
        }

        $view_data["show_add_button"] = true;
        $view_data["show_option"] = true;
        if ($this->access_type !== "all") {
            $view_data["show_add_button"] = false;
            $view_data["show_option"] = false;
        }

        return $this->template->rander("announcements/index", $view_data);
    }

    //show add/edit announcement form
    function form($id = 0) {
        validate_numeric_value($id);

        $this->access_only_allowed_members();

        $view_data['model_info'] = $this->Announcements_model->get_one($id);

        $view_data['get_sharing_options_view'] = $this->get_sharing_options_view(false, $view_data['model_info']);

        return $this->template->rander('announcements/modal_form', $view_data);
    }

    function get_client_groups_dropdown() {

        $this->access_only_allowed_members();

        $client_groups = $this->Client_groups_model->get_all()->getResult();
        $groups_dropdown = array();

        foreach ($client_groups as $group) {
            $groups_dropdown[] = array("id" => "cg:" . $group->id, "text" => $group->title);
        }

        return json_encode($groups_dropdown);
    }

    //show a specific announcement
    function view($id = 0) {
        validate_numeric_value($id);

        if ($id) {
            if ($this->login_user->user_type === "client") {
                if (!$this->can_client_access("announcement")) {
                    app_redirect("forbidden");
                }
            }

            //show only the allowed announcement
            $options = array("id" => $id);

            $options = $this->_prepare_access_options($options);

            $announcement = $this->Announcements_model->get_details($options)->getRow();
            if ($announcement) {
                $view_data['announcement'] = $announcement;

                //mark the announcement as read for loged in user
                $this->Announcements_model->mark_as_read($id, $this->login_user->id);
                return $this->template->rander("announcements/view", $view_data);
            }
        }

        //not matched the requirement. show 404 page
        show_404();
    }

    private function _prepare_access_options($options = array()) {
        if ($this->access_type === "all") {
            return $options;
        }

        $options["user_type"] = $this->login_user->user_type;

        if ($this->login_user->user_type === "client") {
            $group_ids = $this->Clients_model->get_one($this->login_user->client_id)->group_ids;
            if ($group_ids) {
                $options["client_group_ids"] = $group_ids;
            }
        } else {
            $options["team_ids"] = $this->login_user->team_ids;
            $options["created_by"] = $this->login_user->id;
            $options["user_id"] = $this->login_user->id;
        }

        return $options;
    }

    //mark the announcement as read for loged in user
    function mark_as_read($id) {
        validate_numeric_value($id);

        $this->Announcements_model->mark_as_read($id, $this->login_user->id);
    }

    //add/edit an announcement
    function save() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "start_date" => "required",
            "end_date" => "required"
        ));

        $id = $this->request->getPost('id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "announcement");
        $new_files = unserialize($files_data);

        $share_with = $this->request->getPost('share_with');
        validate_share_with_value($share_with);

        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => decode_ajax_post_data($this->request->getPost('description')),
            "start_date" => $this->request->getPost('start_date'),
            "end_date" => $this->request->getPost('end_date'),
            "created_by" => $this->login_user->id,
            "created_at" => get_current_utc_time(),
            "share_with" => $share_with
        );

        //is editing? update the files if required
        if ($id) {
            $expense_info = $this->Announcements_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $expense_info->files, $new_files);
        }

        $data["files"] = serialize($new_files);

        if (!$id) {
            $data["read_by"] = 0; //set default value
        }

        $save_id = $this->Announcements_model->ci_save($data, $id);

        if ($save_id) {

            //send log notification
            if (!$id && $data["share_with"]) {
                log_notification("new_announcement_created", array("announcement_id" => $save_id));
            }

            echo json_encode(array("success" => true, "recirect_to" => get_uri("announcements/form/" . $save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }


    // download files 
    function download_announcement_files($id = 0) {

        $options = array("id" => $id);
        $options = $this->_prepare_access_options($options);

        $info = $this->Announcements_model->get_details($options)->getRow();

        return $this->download_app_files(get_setting("timeline_file_path"), $info->files);
    }

    //delete/undo an announcement
    function delete() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Announcements_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Announcements_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    //perepare the list data for announcement list
    function list_data() {

        //show only the allowed announcements
        $options = $this->_prepare_access_options();

        $list_data = $this->Announcements_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of announcement list row
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Announcements_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    //make a row of announcement list
    private function _make_row($data) {
        $image_url = get_avatar($data->created_by_avatar);
        $user = "<span class='avatar avatar-xs mr10'><img src='$image_url' alt=''></span> $data->created_by_user";
        $option = "";
        if ($this->access_type === "all") {
            $option = anchor(get_uri("announcements/form/" . $data->id), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_announcement')))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_announcement'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("announcements/delete"), "data-action" => "delete"));
        }
        return array(
            anchor(get_uri("announcements/view/" . $data->id), $data->title, array("class" => "", "title" => app_lang('view'))),
            get_team_member_profile_link($data->created_by, $user),
            $data->start_date,
            format_to_date($data->start_date, false),
            $data->end_date,
            format_to_date($data->end_date, false),
            $option
        );
    }

    function get_members_and_teams_dropdown() {
        $this->access_only_allowed_members();

        $hide_team_members_list_user_id = 0;
        if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
            $hide_team_members_list_user_id = $this->login_user->id;
        }

        return json_encode(get_team_members_and_teams_select2_data_list(true, $hide_team_members_list_user_id));
    }

    function get_sharing_options_view($return_json = false, $model_info = null) {

        $view_data["id"] =  isset($model_info->id) ? $model_info->id : $this->request->getPost('id');
        $view_data["share_with"] = isset($model_info->share_with) ? $model_info->share_with : $this->request->getPost('share_with');

        $view_data["options"] = array("all_members", "specific_members_and_teams", "all_clients", "specific_cg");

        $view_data["members_and_teams_dropdown_source_url"] = get_uri("announcements/get_members_and_teams_dropdown");
        $view_data["client_groups_source_url"] = get_uri("announcements/get_client_groups_dropdown");

        $sharing_options_view = view("includes/sharing_options", $view_data);

        if ($return_json) {
            return json_encode(array("sharing_options_view" => $sharing_options_view));
        } else {
            return $sharing_options_view;
        }
    }
}

/* End of file announcements.php */
/* Location: ./app/controllers/announcements.php */