<?php

namespace App\Models;

class Help_articles_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'help_articles';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $help_categories_table = $this->db->prefixTable('help_categories');
        $help_articles_table = $this->db->prefixTable('help_articles');
        $article_helpful_status_table = $this->db->prefixTable('article_helpful_status');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $help_articles_table.id=$id";
        }

        $type = $this->_get_clean_value($options, "type");
        if ($type) {
            $where .= " AND $help_categories_table.type='$type'";
        }


        $only_active_categories = $this->_get_clean_value($options, "only_active_categories");
        if ($only_active_categories) {
            $where .= " AND $help_categories_table.status='active'";
        }


        $status = $this->_get_clean_value($options, "status");
        if ($status == "active") {
            $where .= " AND $help_articles_table.status='active'";
        } else if ($status == "inactive") {
            $where .= " AND $help_articles_table.status='inactive'";
        }


        $category_id = $this->_get_clean_value($options, "category_id");
        if ($category_id) {
            $where .= " AND $help_articles_table.category_id=$category_id";
        }

        $extra_select = "";
        $login_user_id = $this->_get_clean_value($options, "login_user_id");
        if ($login_user_id) {
            $extra_select = ", (SELECT count($article_helpful_status_table.id) FROM $article_helpful_status_table WHERE $article_helpful_status_table.article_id=$help_articles_table.id AND $article_helpful_status_table.deleted=0 AND $article_helpful_status_table.created_by=$login_user_id) as article_helpful_status,
                    (SELECT count($article_helpful_status_table.id) FROM $article_helpful_status_table WHERE $article_helpful_status_table.article_id=$help_articles_table.id AND $article_helpful_status_table.deleted=0 AND $article_helpful_status_table.status='yes') as helpful_status_yes,
                    (SELECT count($article_helpful_status_table.id) FROM $article_helpful_status_table WHERE $article_helpful_status_table.article_id=$help_articles_table.id AND $article_helpful_status_table.deleted=0 AND $article_helpful_status_table.status='no') as helpful_status_no";
        }

        $label_id = $this->_get_clean_value($options, "label_id");
        if ($label_id) {
            $where .= " AND (FIND_IN_SET('$label_id', $help_articles_table.labels)) ";
        }

        $select_labels_data_query = $this->get_labels_data_query();

        $sql = "SELECT $help_articles_table.*, $help_categories_table.title AS category_title, $help_categories_table.type $extra_select , $select_labels_data_query
        FROM $help_articles_table
        LEFT JOIN $help_categories_table ON $help_categories_table.id=$help_articles_table.category_id
        WHERE $help_articles_table.deleted=0 AND $help_categories_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_articles_of_a_category($category_id = "", $related_articles = "", $order = "") {
        $help_articles_table = $this->db->prefixTable('help_articles');

        $where = "";
        $category_id = $this->_get_clean_value($category_id);
        if ($category_id) {
            $where .= " AND $help_articles_table.category_id=$category_id";
        }

        $related_articles = $this->_get_clean_value($related_articles);
        if ($related_articles) {
            $related_articles = explode(",", $related_articles);
            $find_ind_set_query = "";
            foreach ($related_articles as $label_id) {
                if ($find_ind_set_query) {
                    $find_ind_set_query .= " OR ";
                }
                $find_ind_set_query .= "FIND_IN_SET('$label_id', $help_articles_table.labels) > 0";
            }

            // related_articles is comma separated label ids 
            $where .= " AND ($find_ind_set_query)";
        }

        $order_by = "ASC";
        if ($order == "Z-A") {
            $order_by = "DESC";
        }

        $sql = "SELECT $help_articles_table.id, $help_articles_table.title
        FROM $help_articles_table
     
        WHERE $help_articles_table.deleted=0 AND $help_articles_table.status='active' $where
        ORDER BY $help_articles_table.sort $order_by, $help_articles_table.title $order_by";

        return $this->db->query($sql);
    }

    function increas_page_view($id) {
        $id = $this->_get_clean_value($id);
        $help_articles_table = $this->db->prefixTable('help_articles');

        $sql = "UPDATE $help_articles_table
        SET total_views = total_views+1 
        WHERE $help_articles_table.id=$id";

        return $this->db->query($sql);
    }

    function get_suggestions($type, $search) {
        $help_articles_table = $this->db->prefixTable('help_articles');
        $help_categories_table = $this->db->prefixTable('help_categories');

        $type = $this->_get_clean_value($type);

        $where = "";

        $search = $this->_get_clean_value($search);
        if ($search) {
            $search = $this->db->escapeLikeString($search);
            $where = " AND $help_articles_table.title LIKE '%$search%' ESCAPE '!' ";
        }

        $sql = "SELECT $help_articles_table.id, $help_articles_table.title
        FROM $help_articles_table
        LEFT JOIN $help_categories_table ON $help_categories_table.id=$help_articles_table.category_id   
        WHERE $help_articles_table.deleted=0 AND $help_articles_table.status='active' AND $help_categories_table.deleted=0 AND $help_categories_table.status='active' AND $help_categories_table.type='$type'
        $where
        ORDER BY $help_articles_table.title ASC
        LIMIT 0, 10";

        $result = $this->db->query($sql)->getResult();

        $result_array = array();
        foreach ($result as $value) {
            $result_array[] = array("value" => $value->id, "label" => $value->title);
        }

        return $result_array;
    }
}
