<?php

namespace App\Controllers;

class Knowledge_base extends App_Controller {

    public $login_user;
    protected $access_type = "";
    protected $allowed_members = array();
    protected $Help_categories_model;
    protected $Help_articles_model;
    protected $Article_helpful_status_model;

    function __construct() {
        parent::__construct();

        $this->login_user = new \stdClass();
        $login_user_id = $this->Users_model->login_user_id();
        if ($login_user_id) {
            //initialize login users required information
            $this->login_user = $this->Users_model->get_access_info($login_user_id);

            //initialize login users access permissions
            if ($this->login_user->permissions) {
                $permissions = unserialize($this->login_user->permissions);
                $this->login_user->permissions = is_array($permissions) ? $permissions : array();
            } else {
                $this->login_user->permissions = array();
            }
        }


        $this->Help_categories_model = model('App\Models\Help_categories_model');
        $this->Help_articles_model = model('App\Models\Help_articles_model');
        $this->Article_helpful_status_model = model('App\Models\Article_helpful_status_model');
    }

    private function _allow_auto_slagging() {
        return get_setting("disable_auto_slagging") != "1";
    }

    //show knowledge base page
    function index() {
        if (!get_setting("module_knowledge_base")) {
            show_404();
        }

        $this->_validate_client_access_permission();

        $type = "knowledge_base";
        $view_data["categories"] = $this->Help_categories_model->get_details(array("type" => $type, "only_active_categories" => true))->getResult();
        $view_data["type"] = $type;

        if (!isset($this->login_user->id)) {
            $view_data['topbar'] = "includes/public/topbar";
            $view_data['left_menu'] = false;
        }

        $view_data['can_manage_help_and_kb'] = $this->_can_manage_help_and_kb();

        return $this->template->rander("help_and_knowledge_base/index", $view_data);
    }

    //show knowledge base category
    function category($id) {
        if (!$id || !is_numeric($id)) {
            show_404();
        }

        $this->_validate_client_access_permission();

        $category_info = $this->Help_categories_model->get_one($id);
        if (!$category_info || !$category_info->id || $category_info->type != "knowledge_base") {
            show_404();
        }

        $view_data['page_type'] = "articles_list_view";
        $view_data['type'] = $category_info->type;
        $view_data['selected_category_id'] = $category_info->id;
        $view_data['categories'] = $this->Help_categories_model->get_details(array("type" => $category_info->type, "only_active_categories" => true))->getResult();

        $view_data["articles"] = $this->Help_articles_model->get_articles_of_a_category($id, "", $category_info->articles_order)->getResult();
        $view_data["category_info"] = $category_info;

        if (!isset($this->login_user->id)) {
            $view_data['topbar'] = "includes/public/topbar";
            $view_data['left_menu'] = false;
        }

        $related_articles = array();
        if ($category_info->related_articles) {
            $related_articles = $this->Help_articles_model->get_articles_of_a_category("", $category_info->related_articles, $category_info->articles_order)->getResult();
        }

        $view_data["related_articles"] = $related_articles;

        $view_data['can_manage_help_and_kb'] = $this->_can_manage_help_and_kb();

        return $this->template->rander("help_and_knowledge_base/articles/view_page", $view_data);
    }

    //show article
    function view($id = 0) {
        if (!$id) {
            show_404();
        }

        if ($this->_allow_auto_slagging()) {
            $id = explode("-", $id)[0];
        }

        if (!is_numeric($id)) {
            show_404();
        }

        $this->_validate_client_access_permission();

        //        $login_user_id = $this->login_user->id;
        if (!isset($this->login_user->id)) {
            $login_user_id = 0;
        } else {
            $login_user_id = $this->login_user->id;
        }

        $model_info = $this->Help_articles_model->get_details(array("id" => $id, "login_user_id" => $login_user_id))->getRow();

        if (!$model_info || $model_info->type != "knowledge_base") {
            show_404();
        }

        $this->Help_articles_model->increas_page_view($id);

        $view_data['selected_category_id'] = $model_info->category_id;
        $view_data['type'] = $model_info->type;
        $view_data['categories'] = $this->Help_categories_model->get_details(array("type" => $model_info->type, "only_active_categories" => true))->getResult();
        $view_data['page_type'] = "article_view";

        $view_data['article_info'] = $model_info;

        if (!isset($this->login_user->id)) {
            $view_data['topbar'] = "includes/public/topbar";
            $view_data['left_menu'] = false;

            $view_data['article_info']->article_helpful_status = "";
        }

        $view_data["scroll_to_content"] = true;

        $view_data['can_manage_help_and_kb'] = $this->_can_manage_help_and_kb();

        $view_data['article_label_classes'] = $this->_make_article_label_classes("knowledge_base", $model_info->labels_list);

        return $this->template->rander('help_and_knowledge_base/articles/view_page', $view_data);
    }

    private function _can_manage_help_and_kb() {
        return (isset($this->login_user->id) && ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "help_and_knowledge_base") == "all"));
    }

    function get_article_suggestion() {
        $search = $this->request->getPost("search");
        if ($search) {
            $result = $this->Help_articles_model->get_suggestions("knowledge_base", $search);

            echo json_encode($result);
        }
    }

    // download files 
    function download_files($id = 0) {
        $info = $this->Help_articles_model->get_one($id);
        return $this->download_app_files(get_setting("timeline_file_path"), $info->files);
    }

    function article_helpful_status($article_id, $status) {
        if (!($article_id && $status)) {
            show_404();
        }

        $login_user_id = "";
        if (!isset($this->login_user->id)) {
            $login_user_id = 0;
        } else {
            $login_user_id = $this->login_user->id;
        }

        validate_numeric_value($article_id);
        $data = array(
            "article_id" => $article_id,
            "status" => $status == "yes" ? "yes" : "no",
            "created_by" => $login_user_id,
            "created_at" => get_current_utc_time()
        );

        $save_id = $this->Article_helpful_status_model->ci_save($data);

        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $save_id));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    private function _validate_client_access_permission() {

        if (isset($this->login_user->user_type) && isset($this->login_user->id) && $this->login_user->user_type == "client") {
            //get the array of hidden menu
            $hidden_client_menus = explode(",", get_setting("hidden_client_menus"));

            //get the client contact permissions
            $users_model = $this->Users_model->get_one($this->login_user->id);
            $client_permissions = explode(",", $users_model->client_permissions);

            if (in_array("knowledge_base", $hidden_client_menus) || !(in_array("all", $client_permissions) || in_array("knowledge_base", $client_permissions))) {
                app_redirect("forbidden");
            }
        }
    }
}

/* End of file help.php */
/* Location: ./app/controllers/help.php */