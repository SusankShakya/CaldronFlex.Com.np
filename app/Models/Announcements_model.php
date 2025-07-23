<?php

namespace App\Models;

class Announcements_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'announcements';
        parent::__construct($this->table);
    }

    function get_unread_announcements($options = array()) {
        $announcements_table = $this->db->prefixTable('announcements');
        $user_id = $this->_get_clean_value($options, "user_id");

        $now = get_my_local_time("Y-m-d");
        $where = $this->_prepare_share_with_query($announcements_table, $options);

        $sql = "SELECT $announcements_table.*
        FROM $announcements_table
        WHERE $announcements_table.deleted=0 AND start_date<='$now' AND end_date>='$now' AND FIND_IN_SET($user_id,$announcements_table.read_by) = 0 $where";

        return $this->db->query($sql);
    }

    private function _prepare_share_with_query($announcements_table, $options = array()) {
        $where = "";
        $user_type = $this->_get_clean_value($options, "user_type");
        if (!$user_type) {
            //if no user type found, we'll assume the user has permission to access all
            return $where;
        }

        $user_id = $this->_get_clean_value($options, "user_id");
        $created_by = $this->_get_clean_value($options, "created_by");
        $client_group_ids = $this->_get_clean_value($options, "client_group_ids");
        $team_ids = $this->_get_clean_value($options, "team_ids");

        if ($user_type === "staff") {

            //find events where share with the user and his/her team
            $team_search_sql = "";

            //searh for teams
            if ($team_ids) {
                $teams_array = explode(",", $team_ids);
                foreach ($teams_array as $team_id) {
                    $team_search_sql .= " OR (FIND_IN_SET('team:$team_id', $announcements_table.share_with)) ";
                }
            }

            // the creator can manage the announcement but can not see in the dashboard
            $created_by_where = "";
            if ($created_by) {
                $created_by_where = " $announcements_table.created_by=$user_id OR ";
            }

            //searh for user and teams
            $where = " AND ($created_by_where (FIND_IN_SET('all_members', $announcements_table.share_with))
                OR (FIND_IN_SET('member:$user_id', $announcements_table.share_with))
                $team_search_sql
                    )";
        } else {

            $client_groups_where = "";
            if ($client_group_ids) {
                $client_group_ids = explode(',', $client_group_ids);
                foreach ($client_group_ids as $group_id) {
                    $client_groups_where .= " OR FIND_IN_SET('cg:$group_id', $announcements_table.share_with)";
                }
            }

            $where = " AND (FIND_IN_SET('all_clients', $announcements_table.share_with) $client_groups_where )";
        }

        return $where;
    }

    function get_details($options = array()) {
        $announcements_table = $this->db->prefixTable('announcements');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $announcements_table.id=$id";
        }

        $where .= $this->_prepare_share_with_query($announcements_table, $options);

        $sql = "SELECT $announcements_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user, $users_table.image AS created_by_avatar
        FROM $announcements_table
        LEFT JOIN $users_table ON $users_table.id= $announcements_table.created_by
        WHERE $announcements_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function mark_as_read($id, $user_id) {
        $id = $this->_get_clean_value($id);
        $user_id = $this->_get_clean_value($user_id);

        $announcements_table = $this->db->prefixTable('announcements');
        $sql = "UPDATE $announcements_table SET $announcements_table.read_by = CONCAT($announcements_table.read_by,',',$user_id)
        WHERE $announcements_table.id=$id AND FIND_IN_SET($user_id,$announcements_table.read_by) = 0";
        return $this->db->query($sql);
    }

    function get_last_announcement($options = array()) {
        $announcements_table = $this->db->prefixTable('announcements');

        $where = "";
        $where .= $this->_prepare_share_with_query($announcements_table, $options);

        $sql = "SELECT $announcements_table.id, $announcements_table.title
        FROM $announcements_table
        WHERE $announcements_table.deleted=0 $where
        ORDER BY $announcements_table.id DESC
        LIMIT 1";
        return $this->db->query($sql)->getRow();
    }
}
