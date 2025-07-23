<?php

namespace App\Controllers;

class Help extends Security_Controller {

    protected $Help_categories_model;
    protected $Help_articles_model;

    function __construct() {
        parent::__construct();
        $this->access_only_team_members();
        $this->init_permission_checker("help_and_knowledge_base");

        $this->Help_categories_model = model('App\Models\Help_categories_model');
        $this->Help_articles_model = model('App\Models\Help_articles_model');
    }

    private function _allow_auto_slagging() {
        return get_setting("disable_auto_slagging") != "1";
    }

    //show help page
    function index() {
        $this->check_module_availability("module_help");

        $type = "help";

        $view_data["categories"] = $this->Help_categories_model->get_details(array("type" => $type, "only_active_categories" => true))->getResult();
        $view_data["type"] = $type;
        $view_data['can_manage_help_and_kb'] = $this->_can_manage_help_and_kb();
        return $this->template->rander("help_and_knowledge_base/index", $view_data);
    }

    private function _can_manage_help_and_kb() {
        return ($this->login_user->is_admin || $this->access_type === "all");
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

        $model_info = $this->Help_articles_model->get_details(array("id" => $id))->getRow();

        if (!$model_info || $model_info->type != "help") {
            show_404();
        }


        $this->Help_articles_model->increas_page_view($id);

        $view_data['selected_category_id'] = $model_info->category_id;
        $view_data['type'] = $model_info->type;
        $view_data['categories'] = $this->Help_categories_model->get_details(array("type" => $model_info->type, "only_active_categories" => true))->getResult();
        $view_data['page_type'] = "article_view";

        $view_data['article_info'] = $model_info;

        $view_data['can_manage_help_and_kb'] = $this->_can_manage_help_and_kb();

        $view_data['article_label_classes'] = $this->_make_article_label_classes("help", $model_info->labels_list);

        return $this->template->rander('help_and_knowledge_base/articles/view_page', $view_data);
    }

    //get search suggestion for autocomplete
    function get_article_suggestion() {
        $search = $this->request->getPost("search");
        if ($search) {
            $result = $this->Help_articles_model->get_suggestions("help", $search);

            echo json_encode($result);
        }
    }

    //show help category
    function category($id) {
        if (!$id || !is_numeric($id)) {
            show_404();
        }

        $category_info = $this->Help_categories_model->get_one($id);
        if (!$category_info || !$category_info->id) {
            show_404();
        }

        $view_data['page_type'] = "articles_list_view";
        $view_data['type'] = $category_info->type;
        $view_data['selected_category_id'] = $category_info->id;
        $view_data['categories'] = $this->Help_categories_model->get_details(array("type" => $category_info->type, "only_active_categories" => true))->getResult();

        $view_data["articles"] = $this->Help_articles_model->get_articles_of_a_category($id, "", $category_info->articles_order)->getResult();

        $related_articles = array();
        if ($category_info->related_articles) {
            $related_articles = $this->Help_articles_model->get_articles_of_a_category("", $category_info->related_articles, $category_info->articles_order)->getResult();
        }

        $view_data["related_articles"] = $related_articles;

        $view_data["category_info"] = $category_info;

        $view_data['can_manage_help_and_kb'] = $this->_can_manage_help_and_kb();

        return $this->template->rander("help_and_knowledge_base/articles/view_page", $view_data);
    }

    //show help articles list
    function help_articles() {
        $this->access_only_allowed_members();

        $view_data["type"] = "help";
        $view_data['categories_dropdown'] = $this->_get_categories_dropdown($view_data["type"]);
        $view_data['labels_dropdown'] = json_encode($this->make_labels_dropdown($view_data["type"], "", true));
        $view_data['status_dropdown'] = json_encode($this->_get_status_dropdown());
        return $this->template->rander("help_and_knowledge_base/articles/index", $view_data);
    }

    private function _get_status_dropdown() {
        return array(
            array("id" => "", "text" => "- " . app_lang("status") . " -"),
            array("id" => "active", "text"  => app_lang("active")),
            array("id" => "inactive", "text"  => app_lang("inactive"))
        );
    }

    //show knowledge base articles list
    function knowledge_base_articles() {
        $this->access_only_allowed_members();

        $view_data["type"] = "knowledge_base";
        $view_data['categories_dropdown'] = $this->_get_categories_dropdown($view_data["type"]);
        $view_data['labels_dropdown'] = json_encode($this->make_labels_dropdown($view_data["type"], "", true));
        $view_data['status_dropdown'] = json_encode($this->_get_status_dropdown());
        
        return $this->template->rander("help_and_knowledge_base/articles/index", $view_data);
    }


    private function _get_categories_dropdown($type) {
        $categories_json_dropdown = array(array("id" => "", "text" => "- " . app_lang("category") . " -"));
        $categories = $this->Help_categories_model->get_details(array("type" => $type, "only_active_categories" => true))->getResult();

        foreach ($categories as $category) {

            $categories_json_dropdown[] = array("id" => $category->id, "text" => $category->title);
        }

        return json_encode($categories_json_dropdown);
    }

    //show help articles list
    function help_categories() {
        $this->access_only_allowed_members();

        $view_data["type"] = "help";
        return $this->template->rander("help_and_knowledge_base/categories/index", $view_data);
    }

    //show knowledge base articles list
    function knowledge_base_categories() {
        $this->access_only_allowed_members();

        $view_data["type"] = "knowledge_base";
        return $this->template->rander("help_and_knowledge_base/categories/index", $view_data);
    }

    //show add/edit category modal
    function category_modal_form($type) {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $view_data['model_info'] = $this->Help_categories_model->get_one($id);
        $view_data['type'] = clean_data($type);

        //prepare label suggestions
        $view_data['label_suggestions'] = $this->make_labels_dropdown($type, $view_data['model_info']->related_articles);

        return $this->template->view('help_and_knowledge_base/categories/modal_form', $view_data);
    }

    //save category
    function save_category() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "type" => "required"
        ));

        $id = $this->request->getPost('id');
        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "type" => $this->request->getPost('type'),
            "sort" => $this->request->getPost('sort'),
            "articles_order" => $this->request->getPost('articles_order'),
            "status" => $this->request->getPost('status'),
            "banner_url" => $this->request->getPost('banner_url')
        );

        $related_articles = $this->request->getPost('related_articles');
        validate_list_of_numbers($related_articles);
        $data["related_articles"] = $related_articles ? $related_articles : "";

        // save banner image
        $files_data = move_files_from_temp_dir_to_permanent_dir(get_setting("timeline_file_path"), "help_and_kb");
        $unserialize_files_data = unserialize($files_data);
        $banner_image = get_array_value($unserialize_files_data, 0);

        if ($banner_image) {

            $this->_delete_banner_image($id); // delete old banner image
            $data["banner_image"] = serialize($banner_image);
        } else if ($this->request->getPost("remove_banner_image")) {

            // delete banner image
            $this->_delete_banner_image($id);
            $data["banner_image"] = "";
        }

        $save_id = $this->Help_categories_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_category_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    private function _delete_banner_image($category_id) {
        $category_info = $this->Help_categories_model->get_one($category_id);
        if ($category_info->banner_image) {
            delete_app_files(get_setting("timeline_file_path"), unserialize($category_info->banner_image));
        }
    }

    //delete/undo a category 
    function delete_category() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Help_categories_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_category_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Help_categories_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    //prepare categories list data
    function categories_list_data($type) {
        $this->access_only_allowed_members();

        $list_data = $this->Help_categories_model->get_details(array("type" => $type))->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_category_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of category row
    private function _category_row_data($id) {
        $options = array("id" => $id);
        $data = $this->Help_categories_model->get_details($options)->getRow();
        return $this->_make_category_row($data);
    }

    //make a row of category row
    private function _make_category_row($data) {
        return array(
            anchor($data->type . "/category/" . $data->id, $data->title),
            $data->description ? $data->description : "-",
            app_lang($data->status),
            $data->sort,
            modal_anchor(get_uri("help/category_modal_form/" . $data->type), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_category'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_category'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("help/delete_category"), "data-action" => "delete"))
        );
    }

    //show add/edit article form
    function article_form($type, $id = 0) {
        $this->access_only_allowed_members();

        $view_data['model_info'] = $this->Help_articles_model->get_one($id);
        $view_data['type'] = clean_data($type);
        $view_data['categories_dropdown'] = $this->Help_categories_model->get_dropdown_list(array("title"), "id", array("type" => $type));

        //prepare label suggestions
        $view_data['label_suggestions'] = $this->make_labels_dropdown($type, $view_data['model_info']->labels);

        return $this->template->rander('help_and_knowledge_base/articles/form', $view_data);
    }

    //save article
    function save_article() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "category_id" => "numeric|required"
        ));

        $id = $this->request->getPost('id');
        $type = $this->request->getPost('type');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "help");
        $new_files = unserialize($files_data);

        $description = decode_ajax_post_data($this->request->getPost('description'));

        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $description,
            "category_id" => $this->request->getPost('category_id'),
            "sort" => $this->request->getPost('sort'),
            "status" => $this->request->getPost('status')
        );

        $labels = $this->request->getPost('labels');
        validate_list_of_numbers($labels);
        $data["labels"] = $labels ? $labels : "";

        //is editing? update the files if required
        if ($id) {
            $expense_info = $this->Help_articles_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $expense_info->files, $new_files);
        }

        $data["files"] = serialize($new_files);

        if (!$id) {
            $data["created_by"] = $this->login_user->id;
            $data["created_at"] = get_my_local_time();
        }


        $save_id = $this->Help_articles_model->ci_save($data, $id);
        if ($save_id) {
            $this->session->setFlashdata("success_message", app_lang('record_saved'));
            app_redirect("help/article_form/" . $type . "/" . $save_id);
        } else {
            $this->session->setFlashdata("error_message", app_lang('error_occurred'));
            app_redirect("help/article_form/" . $type);
        }
    }

    //delete/undo an article 
    function delete_article() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Help_articles_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_article_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Help_articles_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    //prepare article list data
    function articles_list_data($type) {
        $this->access_only_allowed_members();

        $options = array(
            "type" => $type,
            "login_user_id" => $this->login_user->id,
            "category_id" => $this->request->getPost("category_id"),
            "label_id" => $this->request->getPost('label_id'),
            "status" => $this->request->getPost('status')
        );

        $list_data = $this->Help_articles_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_article_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of article row
    private function _article_row_data($id) {
        $options = array("id" => $id, "login_user_id" => $this->login_user->id);
        $data = $this->Help_articles_model->get_details($options)->getRow();
        return $this->_make_article_row($data);
    }

    //make a row of article row
    private function _make_article_row($data) {
        $title = anchor(get_uri("help/view/" . $data->id), $data->title);

        $feedback = "";

        if ($data->type == "knowledge_base") {
            $title = anchor(get_uri("knowledge_base/view/" . $data->id), $data->title);

            if ($data->helpful_status_yes) {
                $feedback .= "<span class='badge bg-success mt0'>" . $data->helpful_status_yes . " " . app_lang("yes") . "</span> ";
            }

            if ($data->helpful_status_no) {
                $feedback .= "<span class='badge bg-danger mt0'>" . $data->helpful_status_no . " " . app_lang("no") . "</span>";
            }
        }

        $labels = make_labels_view_data($data->labels_list, true);
        if ($labels) {
            $title .= "<br />" . $labels;
        }

        return array(
            $title,
            $data->category_title,
            $data->created_at,
            format_to_relative_time($data->created_at, true, false, true),
            app_lang($data->status),
            $data->total_views,
            $feedback,
            $data->sort,
            anchor(get_uri("help/article_form/" . $data->type . "/" . $data->id), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_article')))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_article'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("help/delete_article"), "data-action" => "delete"))
        );
    }

    // download files 
    function download_files($id = 0) {
        $info = $this->Help_articles_model->get_one($id);
        return $this->download_app_files(get_setting("timeline_file_path"), $info->files);
    }
}

/* End of file help.php */
/* Location: ./app/controllers/help.php */