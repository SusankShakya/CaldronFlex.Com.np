<?php

namespace App\Controllers;

use App\Libraries\Dropdown_list;

class Search extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_team_members();
    }

    public function index() {
    }

    function search_modal_form() {
        $search_fields = array(
            "task",
            "project"
        );

        if ($this->permission_manager->can_view_clients()) {
            $search_fields[] = "client";
        }

        if (get_setting("module_todo")) {
            $search_fields[] = "todo";
        }

        $search_fields_dropdown = array();
        foreach ($search_fields as $search_field) {
            $search_fields_dropdown[] = array("id" => $search_field, "text" => app_lang($search_field));
        }

        $view_data['search_fields_dropdown'] = json_encode($search_fields_dropdown);

        return $this->template->view("search/modal_form", $view_data);
    }

    function get_global_search_suggestion() {
        $search = $this->request->getPost("search");
        $search_field = $this->request->getPost("search_field");

        if ($search && $search_field) {
            $options = array();
            $result = array();

            if ($search_field == "project") { //project
                if (!$this->can_manage_all_projects()) {
                    $options["user_id"] = $this->login_user->id;
                }
                $result = $this->Projects_model->get_search_suggestion($search, $options)->getResult();
            } else if ($search_field == "client") { //client
                if (!$this->permission_manager->can_view_clients()) {
                    app_redirect("forbidden");
                }

                $dropdown_list = new Dropdown_list($this);
                $result = $dropdown_list->get_clients_id_and_text_dropdown(array("search" => $search), false);
            } else if ($search_field == "todo" && get_setting("module_todo")) { //todo
                $result = $this->Todo_model->get_search_suggestion($search, $this->login_user->id)->getResult();
            }

            $result_array = array();
            foreach ($result as $item) {
                if ($search_field == "client") {
                    $result_array[] = array("value" => get_array_value($item, "id"), "label" => get_array_value($item, "text"));
                } else {
                    $result_array[] = array("value" => $item->id, "label" => $item->title);
                }
            }

            echo json_encode($result_array);
        }
    }
}

/* End of file Search.php */
/* Location: ./app/controllers/Search.php */