<?php

namespace App\Models;

class Pin_comments_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'pin_comments';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $pin_comments_table = $this->db->prefixTable('pin_comments');
        $users_table = $this->db->prefixTable('users');
        $project_comments_table = $this->db->prefixTable('project_comments');
        $ticket_comments_table = $this->db->prefixTable('ticket_comments');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $pin_comments_table.id=$id";
        }

        $pinned_by = $this->_get_clean_value($options, "pinned_by");
        if ($pinned_by) {
            $where .= " AND $pin_comments_table.pinned_by=$pinned_by";
        }

        $extra_left_join = "";
        $created_at_field = "";

        $task_id = $this->_get_clean_value($options, "task_id");
        $ticket_id = $this->_get_clean_value($options, "ticket_id");

        if ($task_id) {
            $where .= " AND $project_comments_table.task_id=$task_id";
            $extra_left_join = " LEFT JOIN $project_comments_table ON $project_comments_table.id = $pin_comments_table.project_comment_id
                                LEFT JOIN $users_table ON $users_table.id = $project_comments_table.created_by";
            $created_at_field = "$project_comments_table.created_at";
        }


        if ($ticket_id) {
            $where .= " AND $ticket_comments_table.ticket_id=$ticket_id";
            $extra_left_join = " LEFT JOIN $ticket_comments_table ON $ticket_comments_table.id = $pin_comments_table.ticket_comment_id
                                LEFT JOIN $users_table ON $users_table.id = $ticket_comments_table.created_by";
            $created_at_field = "$ticket_comments_table.created_at";
        }

        $sql = "SELECT $pin_comments_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS commented_by_user,$users_table.image as commented_by_avatar, $created_at_field as comment_created_at
        FROM $pin_comments_table
        $extra_left_join
        WHERE $pin_comments_table.deleted=0 $where";
        return $this->db->query($sql);
    }
}
