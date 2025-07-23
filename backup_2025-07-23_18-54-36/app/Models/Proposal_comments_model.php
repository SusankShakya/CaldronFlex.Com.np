<?php

namespace App\Models;

class Proposal_comments_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'proposal_comments';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $proposal_comments_table = $this->db->prefixTable('proposal_comments');
        $users_table = $this->db->prefixTable('users');
        $where = "";
        $sort = "ASC";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $proposal_comments_table.id=$id";
        }

        $proposal_id = $this->_get_clean_value($options, "proposal_id");
        if ($proposal_id) {
            $where .= " AND $proposal_comments_table.proposal_id=$proposal_id";
        }

        $sort_decending = $this->_get_clean_value($options, "sort_as_decending");
        if ($sort_decending) {
            $sort = "DESC";
        }

        $sql = "SELECT $proposal_comments_table.*, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS created_by_user, $users_table.image as created_by_avatar, $users_table.user_type
        FROM $proposal_comments_table
        LEFT JOIN $users_table ON $users_table.id= $proposal_comments_table.created_by
        WHERE $proposal_comments_table.deleted=0 $where
        ORDER BY $proposal_comments_table.created_at $sort";

        return $this->db->query($sql);
    }

}
