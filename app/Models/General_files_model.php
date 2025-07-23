<?php

namespace App\Models;

class General_files_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'general_files';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {

        // File manager, existing: 
        // Array ( [folder_id] =>  [context_type] => file_manager [client_id] => 0 [is_admin] => 1 [show_root_files] => 1 )

        // Client details page, existing:
        // Array ( [folder_id] =>  [context_type] => client [client_id] => 4 [is_admin] => 1 [show_root_files] => 1 )

        // Client portal, existing
        // Array ( [folder_id] => [context_type] => client_portal [client_id] => 4 [is_admin] => 0 [show_root_files] => 1 )


        //in the users list, show only the user_id related files. 

        //in the global file manager list
        //  - in root, show the global_files (don't have any folder id and context = global_files) .. only admins can see 
        //  - in a folder show files related with the folder,  

        //in the client details view
        //  - in root (show the client files where don't have any folder id and context = client and client_id is the client_id)  
        //  - in a folder (show the client files with the folder id and context = client and client_id is the client_id)
        //  - in the list view (show the client files where client_id is the client_id)

        //in the client portal
        //  - in root (show the client files  where don't have any folder id and context = client and context_id is the client_id)
        //  - in a folder (show the client files with the folder id where context is client or global_files)
        //  - in the list view (show the client files with context = client and context_id is the client_id) + (show global files where the client has permissions -  find by folder)

        $general_files_table = $this->db->prefixTable('general_files');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $general_files_table.id=$id ";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $general_files_table.client_id=$client_id ";
        }

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $general_files_table.user_id = $user_id ";
        }

        $folder_id = $this->_get_clean_value($options, "folder_id");
        $context_type = $this->_get_clean_value($options, "context_type");
        $is_admin = $this->_get_clean_value($options, "is_admin");

        if ($context_type == "file_manager") { // file manager view
            if ($folder_id) {
                //in a folder in the file manager
                $where = " AND $general_files_table.folder_id=$folder_id AND $general_files_table.context='global_files' ";
            } else {
                //root in the file manager
                if ($is_admin) {
                    $where = " AND $general_files_table.folder_id<=0 AND $general_files_table.context='global_files' ";
                } else {
                    $where = " AND $general_files_table.context='dont_show_global_files' "; //don't show any root files for non admin users. 
                }
            }
        } else if ($context_type == "client") { // client details view
            if ($folder_id) {
                //in a folder in the client details page
                $where = " AND $general_files_table.folder_id=$folder_id AND $general_files_table.context='client' AND $general_files_table.client_id = $client_id ";
            } else {
                //root in the client details page
                $where = " AND $general_files_table.folder_id<=0 AND $general_files_table.context='client' AND $general_files_table.client_id = $client_id ";
            }
        } else if ($context_type == "client_portal") { // client_portal folder view
            if ($folder_id) {
                //in a folder in the client details page
                //$where = " AND $general_files_table.folder_id=$folder_id AND (($general_files_table.context='client' AND $general_files_table.client_id = $client_id) OR ($general_files_table.context='global_files')) ";
                $where = " AND $general_files_table.folder_id=$folder_id AND (($general_files_table.context='client' AND $general_files_table.client_id = $client_id) OR ($general_files_table.context='global_files')) ";
            } else {
                //root in the client details page
                $where = " AND $general_files_table.folder_id<=0 AND $general_files_table.context='client' AND $general_files_table.client_id = $client_id ";
            }
        } else if ($context_type == "client_portal_list_view") { // client_portal list view
            //$where = " AND (($general_files_table.context='client' AND $general_files_table.client_id = $client_id) OR ($general_files_table.context='global_files' AND $general_files_table.folder_id=$folder_id)) ";
            $where = " AND $general_files_table.context='client' AND $general_files_table.client_id = $client_id ";
        }


        $sql = "SELECT $general_files_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS uploaded_by_user_name, $users_table.image AS uploaded_by_user_image, $users_table.user_type AS uploaded_by_user_type
        FROM $general_files_table
        LEFT JOIN $users_table ON $users_table.id= $general_files_table.uploaded_by
        WHERE $general_files_table.deleted=0 $where";
        return $this->db->query($sql);
    }
}
