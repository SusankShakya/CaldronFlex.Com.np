<?php

namespace App\Models;

class Folders_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'folders';
        parent::__construct($this->table);
    }

    function get_folder_details($options = array()) {
        $context = $this->_get_clean_value($options, "context");
        $info = new \stdClass();

        if (!$context) {
            $context = "nothing"; //don't show anything if context is blank
        }

        $has_full_access = get_array_value($options, "has_full_access");

        $id = $this->_get_clean_value($options, "id");
        $folder_id = $this->_get_clean_value($options, "folder_id");

        $login_client_id = $this->_get_clean_value($options, "login_client_id");
        $project_id = $this->_get_clean_value($options, "project_id");

        if ($context == "project") {
            $context_id = $project_id;
        } else {
            $context_id = $login_client_id;
        }

        $folder_info = $this->_get_folder_info($context, $folder_id, $id, $context_id);

        $current_folder_id = $folder_info ? $folder_info->id : 0;

        $authorized_folders_info = $this->_get_all_authorized_folders_info($context, $options);
        $authorized_folder_ids = get_array_value($authorized_folders_info, "root_folder_ids");
        $accessable_folder_ids = get_array_value($authorized_folders_info, "accessable_folder_ids");

        if ((($context == "client" || $context == "project") && $context_id && $folder_id && !$folder_info) || $current_folder_id && !$has_full_access && !$this->_is_authorized_folder($authorized_folder_ids, $current_folder_id, $folder_info->level)) {
            $info->not_authorized = true;
            return $info;
        }

        //find the parent folder info 

        $parent_folder_info = null;

        if ($has_full_access && $this->_has_parent_id($folder_info)) { //can view everything, no need to check the parent folder permissions
            $parent_folder_info = $this->_get_parent_folder_info($context, $folder_info->parent_id);
        } else if (!$this->_is_a_root_folder($current_folder_id, $authorized_folder_ids) && $this->_has_parent_id($folder_info)) {
            $parent_folder_info = $this->_get_parent_folder_info($context, $folder_info->parent_id);
        }

        $parent_folder_permissions = array();

        if ($folder_info) {
            $rank_info = $this->_get_folder_permission_rank($authorized_folders_info, $folder_info, $options);
            $folder_info->inherited_permission_rank = $rank_info->inherited_permission_rank;
            $folder_info->this_folder_permission_rank = $rank_info->this_folder_permission_rank;
            $folder_info->actual_permission_rank = $rank_info->actual_permission_rank;

            if ($has_full_access) {
                $folder_info->actual_permission_rank = 9;
            }

            $parent_folder_permissions = $this->_parent_folder_permissions($folder_info);
        }

        //find the folders list. 

        $get_moveable_folders = get_array_value($options, "get_moveable_folders");
        if ($get_moveable_folders && $accessable_folder_ids) {
            $folders_list = $this->_get_folders_list($current_folder_id, $context, $accessable_folder_ids, $options);
        } else {
            $folders_list = $this->_get_folders_list($current_folder_id, $context, $authorized_folder_ids, $options);
        }

        $info->folder_info = $folder_info;
        $info->parent_folder_info = $parent_folder_info;
        $info->folders_list = $folders_list;
        $info->parent_folder_permissions = $parent_folder_permissions;
        return $info;
    }

    private function _get_folder_permission_rank($authorized_folders_info, $folder_info, $options) {

        $info = new \stdClass();

        $inherited_rank = 1;
        $level = $folder_info->level ? $folder_info->level : "";
        $level_folders = explode(",", $level);

        $full_access = get_array_value($authorized_folders_info, "full_access_folders");
        $upload_and_organize = get_array_value($authorized_folders_info, "upload_and_organize_folders");
        $upload_only = get_array_value($authorized_folders_info, "upload_only_folders");

        if (array_intersect($level_folders, $full_access)) {
            $inherited_rank = 9;
        } else if (array_intersect($level_folders, $upload_and_organize)) {
            $inherited_rank = 6;
        } else if (array_intersect($level_folders, $upload_only)) {
            $inherited_rank = 3;
        }

        $info->this_folder_permission_rank = $this->_get_higher_rank_of_folder($folder_info, $options);
        $info->inherited_permission_rank = $inherited_rank;

        $info->actual_permission_rank = $info->this_folder_permission_rank;

        if ($info->inherited_permission_rank > $info->this_folder_permission_rank) {
            $info->actual_permission_rank = $info->inherited_permission_rank;
        }

        return $info;
    }

    private function _is_authorized_folder($root_folder_ids, $current_folder_id, $level) {
        $current_folder_array = array();

        if ($level) {
            $current_folder_array = explode(",", $level);
            $current_folder_array[] = $current_folder_id;
        } else {
            $current_folder_array[] = $current_folder_id;
        }

        return array_intersect($current_folder_array, $root_folder_ids);
    }

    private function _has_parent_id($folder_info) {
        return $folder_info ? $folder_info->parent_id : 0;
    }

    private function _is_a_root_folder($current_folder_id, $authorized_folder_ids) {
        //$ids = $this->_get_authorized_root_folder_ids($authorized_folder_ids);
        return in_array($current_folder_id, $authorized_folder_ids);
    }

    private function _get_folders_list($parent_id, $context, $root_folder_ids, $options) {
        $folders_table = $this->db->prefixTable('folders');
        $general_files_table = $this->db->prefixTable('general_files');
        $project_files_table = $this->db->prefixTable('project_files');

        $has_full_access = get_array_value($options, "has_full_access");

        $where = "";

        if ($parent_id) {
            $where .= " AND $folders_table.parent_id=$parent_id ";

            if (!$has_full_access) {
                $has_permission_on_root_where = "";
                foreach ($root_folder_ids as $authorized_folder_id) {
                    if ($has_permission_on_root_where) {
                        $has_permission_on_root_where .= " OR ";
                    }
                    $has_permission_on_root_where .= " $folders_table.level LIKE '%," . $authorized_folder_id . ",%' ";
                }

                if ($has_permission_on_root_where) {
                    $where .= " AND ($has_permission_on_root_where)";
                }
            }
        } else {

            if (!$has_full_access) {

                $root_ids = implode(",", $root_folder_ids);

                $get_moveable_folders = get_array_value($options, "get_moveable_folders");
                if ($get_moveable_folders) {
                    $login_client_id = $this->_get_clean_value($options, "login_client_id");
                    $context_where = ($context == "client_portal") ? "(($folders_table.context='client' AND $folders_table.context_id=$login_client_id) OR $folders_table.context='file_manager')" : "$folders_table.context='$context'";

                    $where .= "AND $folders_table.id IN ($root_ids) OR ($folders_table.parent_id IN ($root_ids) AND $context_where) ";
                } else {
                    $where .= " AND  FIND_IN_SET($folders_table.id , '$root_ids') ";
                }
            }
        }

        $show_root_folders_only = $this->_get_clean_value($options, "show_root_folders_only");

        if ($show_root_folders_only) {
            $where .= " AND $folders_table.parent_id=0 ";
        }

        $left_join_where = "";

        $context_id = $this->_get_clean_value($options, "context_id");
        if ($context_id) {
            $where .= " AND $folders_table.context_id=$context_id ";

            $left_join_where = " AND $folders_table.context_id=$context_id ";
        }

        $login_client_id = $this->_get_clean_value($options, "login_client_id");

        $context_where = ($context == "client_portal") ? "(($folders_table.context='client' AND $folders_table.context_id=$login_client_id) OR $folders_table.context='file_manager')" : "$folders_table.context='$context'";

        $subfolder_context_where = ($context == "client_portal") ? "((sub_folders.context='client' AND sub_folders.context_id=$login_client_id) OR sub_folders.context='file_manager')" : "sub_folders.context='$context'";

        $authorized_folders_info = $this->_get_all_authorized_folders_info($context, $options);

        if ($context == "project") {
            $subfile_count_sql = "(SELECT COUNT(1) FROM $project_files_table sub_files WHERE sub_files.deleted=0 AND sub_files.folder_id = $folders_table.id) AS subfile_count";
        } else {
            $subfile_count_sql = "(SELECT COUNT(1) FROM $general_files_table sub_files WHERE sub_files.deleted=0 AND sub_files.folder_id = $folders_table.id) AS subfile_count";
        }

        $folders_sql = "SELECT $folders_table.*,  
                       (SELECT COUNT(1) FROM $folders_table sub_folders WHERE sub_folders.deleted=0 AND $subfolder_context_where AND sub_folders.parent_id = $folders_table.id) AS subfolder_count, $subfile_count_sql
                       FROM $folders_table
                       WHERE $folders_table.deleted=0 AND $context_where $where 
                       ORDER BY $folders_table.title ASC ";

        $folders_result = $this->db->query($folders_sql)->getResult();

        // Get folder permission for each folder using _get_folder_permission_rank
        foreach ($folders_result as $folder) {
            $rank_info = $this->_get_folder_permission_rank($authorized_folders_info, $folder, $options);
            $folder->actual_permission_rank = $rank_info->actual_permission_rank;
        }

        return $folders_result;
    }

    private function _get_all_authorized_folders_info($context, $options) {

        $folders_table = $this->db->prefixTable('folders');

        $where_any = "";

        // it's a team member 
        $member_id = $this->_get_clean_value($options, "member_id");
        if ($member_id) {
            $where_any .= " $folders_table.permissions LIKE '%all_team_members,%' OR $folders_table.permissions LIKE '%member:$member_id,%' ";

            $team_ids = $this->_get_clean_value($options, "team_ids");
            if ($team_ids) {
                $teams = explode(",", $team_ids);
                foreach ($teams as $team_id) {
                    $where_any .= " OR $folders_table.permissions LIKE '%team:$team_id,%' ";
                }
            }

            $is_a_project_member = get_array_value($options, "is_a_project_member");
            if ($is_a_project_member) {
                $where_any .= " OR $folders_table.permissions LIKE '%project_members,%' ";
            }

            $is_a_authorize_member = get_array_value($options, "is_a_authorize_member");
            if ($is_a_authorize_member) {
                $where_any .= " OR $folders_table.permissions LIKE '%authorized_team_members,%' ";
            }
        }

        // it's a client 
        $login_client_id = $this->_get_clean_value($options, "login_client_id");
        if ($login_client_id) {
            $where_any .= " $folders_table.permissions LIKE '%all_clients,%' OR $folders_table.permissions LIKE '%client:$login_client_id,%' OR $folders_table.context_id=$login_client_id ";

            $client_group_ids = $this->_get_clean_value($options, "client_group_ids");
            if ($client_group_ids) {
                $client_groups = explode(",", $client_group_ids);
                foreach ($client_groups as $client_group_id) {
                    $where_any .= " OR $folders_table.permissions LIKE '%client_group:$client_group_id,%' ";
                }
            }
        }

        // it's a project
        $project_id = $this->_get_clean_value($options, "project_id");

        if (trim($where_any)) {
            $where_any = " AND (" . $where_any . ")";
        }


        $where = "";
        $context_id = $this->_get_clean_value($options, "context_id");
        if ($context_id) {
            $where .= " AND $folders_table.context_id=$context_id ";
        }

        if ($context == "project" && $project_id) {
            $context_where = "$folders_table.context='project' AND $folders_table.context_id=$project_id";
        } else {
            $context_where = ($context == "client_portal") ? "(($folders_table.context='client' AND $folders_table.context_id=$login_client_id) OR $folders_table.context='file_manager')" : "$folders_table.context='$context'";
        }

        $folders_sql = "SELECT $folders_table.id, $folders_table.permissions, $folders_table.level, $folders_table.context, $folders_table.context_id
                       FROM $folders_table
                       WHERE $folders_table.deleted=0 AND $context_where $where  $where_any";

        $results = $this->db->query($folders_sql)->getResult();

        return $this->_get_permission_rank_wise_folders($results, $options);
    }

    private function _get_permission_rank_wise_folders($authorized_folders_data, $options) {

        $full_access_folders = array();
        $upload_and_organize_folders = array();
        $upload_only_folders = array();
        $read_only_folders = array();
        $all_folder_ids = array();
        $accessable_folder_ids = array();

        $root_folder_ids = array(); //initiallly all authorized folders
        foreach ($authorized_folders_data as $row) {
            $root_folder_ids[] = $row->id;
        }

        $team_ids = get_array_value($options, "team_ids");
        $teams = array();
        if ($team_ids) {
            $teams = explode(",", $team_ids);
        }

        $client_group_ids = get_array_value($options, "client_group_ids");
        $client_groups = array();
        if ($client_group_ids) {
            $client_groups = explode(",", $client_group_ids);
        }

        $login_client_id = get_array_value($options, "login_client_id");
        $has_full_access = get_array_value($options, "has_full_access");

        foreach ($authorized_folders_data as $folder) {
            $context = $folder->context;
            $context_id = $folder->context_id;

            // Check if the folder context is 'client' and the logged in user is a client with the same ID as the folder context ID,
            // or if the user is an admin
            if (($context === 'client' && isset($login_client_id) && $login_client_id === $context_id && !$folder->permissions) || ($context === 'client' && isset($has_full_access) && $has_full_access)) {
                $full_access_folders[] = $folder->id;
            }

            $level_array = explode(",", $folder->level ? $folder->level : "");

            if (count(array_intersect($level_array, $root_folder_ids))) {
                //this is a sub folder and the there a parent folder where the user has access. 
                //remove this from the root folders list 
                $root_folder_ids = array_diff($root_folder_ids, array($folder->id));
            }

            $higher_access_rank_of_this_folder = $this->_get_higher_rank_of_folder($folder, $options);

            if ($higher_access_rank_of_this_folder == 9) {
                $full_access_folders[] = $folder->id;
            } else if ($higher_access_rank_of_this_folder == 6) {
                $upload_and_organize_folders[] = $folder->id;
            } else if ($higher_access_rank_of_this_folder == 3) {
                $upload_only_folders[] = $folder->id;
            } else if ($higher_access_rank_of_this_folder == 1) {
                $read_only_folders[] = $folder->id;
            }

            $all_folder_ids = array_unique(array_merge($full_access_folders, $upload_and_organize_folders, $upload_only_folders, $read_only_folders, $root_folder_ids));
            $accessable_folder_ids = array_merge($full_access_folders, $upload_and_organize_folders);
        }

        $result = array(
            "full_access_folders" => $full_access_folders,
            "upload_and_organize_folders" => $upload_and_organize_folders,
            "upload_only_folders" => $upload_only_folders,
            "read_only_folders" => $read_only_folders,
            "root_folder_ids" => $root_folder_ids,
            "all_folder_ids" => $all_folder_ids,
            "accessable_folder_ids" => $accessable_folder_ids
        );

        return $result;
    }

    private function _get_higher_rank_of_folder($folder_info, $options) {
        $login_member_id = get_array_value($options, "member_id");
        $login_client_id = get_array_value($options, "login_client_id");
        $is_a_project_member = get_array_value($options, "is_a_project_member");
        $is_a_authorized_member = get_array_value($options, "is_a_authorized_member");


        $team_ids = get_array_value($options, "team_ids");
        $teams = array();
        if ($team_ids) {
            $teams = explode(",", $team_ids);
        }

        $client_group_ids = get_array_value($options, "client_group_ids");
        $client_group_ids = $client_group_ids ? $client_group_ids : "";
        $client_groups = explode(",", $client_group_ids);

        $permissions_array = explode(",", $folder_info->permissions ? $folder_info->permissions : "");

        $higher_access_rank_of_this_folder = 1;

        foreach ($permissions_array as $permission) {
            $access_rank = get_first_letter($permission);

            $permission = substr($permission, 2); //remove 1st 2 letters. Ex  1-member:3 to member:3

            $permission_parts = explode(":", $permission);

            $permission_identifier = get_array_value($permission_parts, 0);
            $permission_value = get_array_value($permission_parts, 1);

            $has_permission = false;

            if ($login_member_id) {
                //check the team member related permissions. 
                if ($permission_identifier == "all_team_members") {
                    $has_permission = true;
                } else if ($permission_identifier == "member" && $permission_value == $login_member_id) {
                    $has_permission = true;
                } else if ($permission_identifier == "team" && in_array($permission_value, $teams)) {
                    $has_permission = true;
                } else if ($permission_identifier == "project_members" && $is_a_project_member) {
                    $has_permission = true;
                } else if ($permission_identifier == "authorized_team_members" && $is_a_authorized_member) {
                    $has_permission = true;
                }
            } else if ($login_client_id) {
                //check the client related permissions. 
                if ($permission_identifier == "all_clients") {
                    $has_permission = true;
                } else if ($permission_identifier == "client" && $permission_value == $login_client_id) {
                    $has_permission = true;
                } else if ($permission_identifier == "client_group" && in_array($permission_value, $client_groups)) {
                    $has_permission = true;
                }
            }

            if ($has_permission && $access_rank > $higher_access_rank_of_this_folder) {
                $higher_access_rank_of_this_folder = $access_rank;
            }
        }

        return $higher_access_rank_of_this_folder;
    }


    private function _parent_folder_permissions($folder_info) {
        $folders_table = $this->db->prefixTable('folders');
        $permissions = array();

        if ($folder_info->level) {
            $level = $folder_info->level;
            $info_sql = "SELECT $folders_table.permissions
            FROM $folders_table
            WHERE $folders_table.deleted=0 AND FIND_IN_SET($folders_table.id, '$level')";
            $result =  $this->db->query($info_sql)->getResult();

            $output_array = array();
            foreach ($result as $row) {
                if ($row->permissions) {
                    $permissions_array = explode(",", $row->permissions);
                    $output_array =  array_merge($output_array, $permissions_array);
                }
            }

            $permissions = array_unique($output_array);
        }


        return implode(",", $permissions);
    }


    private function _get_folder_info($context, $folder_id, $id = 0, $context_id = 0) {
        $folders_table = $this->db->prefixTable('folders');
        $users_table = $this->db->prefixTable('users');
        $general_files_table = $this->db->prefixTable('general_files');
        $project_files_table = $this->db->prefixTable('project_files');

        $where = "";

        if ($folder_id) {
            $where = " AND $folders_table.folder_id='$folder_id' ";
        }

        if ($id) {
            $where = " AND $folders_table.id=$id ";
        }

        if (!$where) {
            return false;
        }

        $context_where = "";
        if ($context == "client" || $context == "project") {
            $context_where = "$folders_table.context='$context' AND $folders_table.context_id=$context_id";
        } else if ($context == "client_portal") {
            $context_where = "($folders_table.context='client' OR $folders_table.context='file_manager')";
        } else {
            $context_where = "$folders_table.context='$context'";
        }

        $subfolder_context_where = ($context == "client_portal") ? "((sub_folders.context='client' AND sub_folders.context_id=$context_id) OR sub_folders.context='file_manager')" : "sub_folders.context='$context'";

        if ($context == "project") {
            $subfile_count_sql = "(SELECT COUNT(1) FROM $project_files_table sub_files WHERE sub_files.deleted=0 AND sub_files.folder_id = $folders_table.id) AS subfile_count";
        } else {
            $subfile_count_sql = "(SELECT COUNT(1) FROM $general_files_table sub_files WHERE sub_files.deleted=0 AND sub_files.folder_id = $folders_table.id) AS subfile_count";
        }

        $info_sql = "SELECT $folders_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user_name, $users_table.image AS created_by_user_image, $users_table.user_type AS created_by_user_type,
        (SELECT COUNT(1) FROM $folders_table sub_folders WHERE sub_folders.deleted=0 AND $subfolder_context_where AND sub_folders.parent_id = $folders_table.id) AS subfolder_count, $subfile_count_sql
        FROM $folders_table
        LEFT JOIN $users_table ON $users_table.id= $folders_table.created_by
        WHERE $folders_table.deleted=0 AND $context_where $where";

        return $this->db->query($info_sql)->getRow();
    }

    private function _get_parent_folder_info($context, $parent_id) {

        $folders_table = $this->db->prefixTable('folders');
        $parent_folder_info = null;

        $context_where = ($context == "client_portal") ? "($folders_table.context='client' OR $folders_table.context='file_manager')" : "$folders_table.context='$context'";

        if ($parent_id) {
            $parent_sql = "SELECT $folders_table.*
            FROM $folders_table
            WHERE $folders_table.deleted=0 AND $context_where AND $folders_table.id=$parent_id";
            $parent_folder_info = $this->db->query($parent_sql)->getRow();
        }
        return $parent_folder_info;
    }

    function add_remove_favorites($folder_id, $user_id, $type = "add") {
        $folders_table = $this->db->prefixTable('folders');

        $folder_id = $this->_get_clean_value($folder_id);
        $user_id = $this->_get_clean_value($user_id);

        $action = " CONCAT($folders_table.starred_by,',',':$user_id:') ";
        $where = " AND FIND_IN_SET(':$user_id:',$folders_table.starred_by) = 0"; //don't add duplicate

        if ($type != "add") {
            $action = " REPLACE($folders_table.starred_by, ',:$user_id:', '') ";
            $where = "";
        }

        $sql = "UPDATE $folders_table SET $folders_table.starred_by = $action
        WHERE $folders_table.id=$folder_id $where";
        return $this->db->query($sql);
    }

    function get_favourite_folders($user_id, $options = array()) {
        $folders_table = $this->db->prefixTable('folders');
        $user_id = $this->_get_clean_value($user_id);
        
        $where = "";
        $context = $this->_get_clean_value($options, "context");
        if ($context == "client_portal") {
            $where .= " AND ($folders_table.context='client' OR $folders_table.context='file_manager') ";
        } else {
            $where .= " AND $folders_table.context='$context' ";
        }

        $authorized_folders_info = $this->_get_all_authorized_folders_info($context, $options);
        $all_folder_ids = get_array_value($authorized_folders_info, "all_folder_ids");

        $all_folder_ids_list = implode(',', $all_folder_ids);
        if (!$all_folder_ids_list) {
            $all_folder_ids_list = "0";
        }


        //check the sub folders of the authorized folders.
        $find_sub_folders = " ";
        $all_folder_ids_REGEXP = implode('|', $all_folder_ids);

        if ($all_folder_ids_REGEXP) {
            $find_sub_folders = " OR $folders_table.level REGEXP ',($all_folder_ids_REGEXP),' ";
        }

        $sql = "SELECT $folders_table.*
        FROM $folders_table
        WHERE $folders_table.deleted=0 AND FIND_IN_SET(':$user_id:', $folders_table.starred_by) AND ($folders_table.id IN ($all_folder_ids_list) $find_sub_folders) $where
        ORDER BY $folders_table.title ASC";

        return $this->db->query($sql);
    }
}
