<?php

namespace App\Models;

class Tickets_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'tickets';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $tickets_table = $this->db->prefixTable('tickets');
        $ticket_types_table = $this->db->prefixTable('ticket_types');
        $clients_table = $this->db->prefixTable('clients');
        $users_table = $this->db->prefixTable('users');
        $project_table = $this->db->prefixTable("projects");
        $task_table = $this->db->prefixTable("tasks");
        $ticket_comments_table = $this->db->prefixTable('ticket_comments');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $tickets_table.id=$id";
        }
        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $tickets_table.client_id=$client_id";
        }
        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $project_table.id=$project_id";
        }
        $task_id = $this->_get_clean_value($options, "task_id");
        if ($task_id) {
            $where .= " AND $task_table.id=$task_id";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status === "closed") {
            $where .= " AND $tickets_table.status='$status'";
        }

        if ($status === "open") {
            $where .= " AND FIND_IN_SET($tickets_table.status, 'new,open,client_replied')";
        }

        $statuses = $this->_get_clean_value($options, "statuses");
        if ($statuses) {
            $where .= " AND FIND_IN_SET($tickets_table.status, '$statuses')";
        }

        $ticket_label = $this->_get_clean_value($options, "ticket_label");
        if ($ticket_label) {
            $where .= " AND (FIND_IN_SET('$ticket_label', $tickets_table.labels)) ";
        }



        $show_assigned_tickets_only_user_id = $this->_get_clean_value($options, "show_assigned_tickets_only_user_id");
        if ($show_assigned_tickets_only_user_id) {
            $where .= " AND $tickets_table.assigned_to=$show_assigned_tickets_only_user_id";
        } else {
            $assigned_to = $this->_get_clean_value($options, "assigned_to");
            if ($assigned_to && is_numeric($assigned_to)) {
                $where .= " AND $tickets_table.assigned_to=$assigned_to";
            } else if ($assigned_to == "unassigned") {
                $where .= " AND $tickets_table.assigned_to = 0";
            }
        }

        $ticket_types = $this->_get_clean_value($options, "ticket_types");

        if ($ticket_types && count($ticket_types)) {
            $ticket_types = implode(",", $ticket_types); //prepare comma separated value
            $where .= " AND FIND_IN_SET($ticket_types_table.id, '$ticket_types')";
        }

        $ticket_type_id = $this->_get_clean_value($options, "ticket_type_id");
        if ($ticket_type_id) {
            $where .= " AND $tickets_table.ticket_type_id=$ticket_type_id";
        }

        $created_at = $this->_get_clean_value($options, "created_at");
        if ($created_at) {
            $where .= " AND ($tickets_table.created_at IS NOT NULL AND $tickets_table.created_at>='$created_at')";
        }

        $exclude_ticket_id = $this->_get_clean_value($options, "exclude_ticket_id");
        if ($exclude_ticket_id) {
            $where .= " AND $tickets_table.id NOT IN($exclude_ticket_id)";
        }

        $creator_email = $this->_get_clean_value($options, "creator_email");
        if ($creator_email) {
            $where .= " AND $tickets_table.creator_email='$creator_email'";
        }

        $select_labels_data_query = $this->get_labels_data_query();

        $last_activity_date_or_before = $this->_get_clean_value($options, "last_activity_date_or_before");
        if ($last_activity_date_or_before) {
            $where .= " AND ($tickets_table.last_activity_at IS NOT NULL AND DATE($tickets_table.last_activity_at)<='$last_activity_date_or_before')";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("tickets", $custom_fields, $tickets_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }


        $available_order_by_list = array(
            "id" => $tickets_table . ".id",
            "title" => $tickets_table . ".title",
            "client" => $clients_table . ".company_name",
            "project" => "project_title",
            "ticket_type" => "ticket_type",
            "assigned_to" => "assigned_to_user",
            "last_activity" => $tickets_table . ".last_activity_at",
            "client_last_activity_at" => $tickets_table . ".client_last_activity_at",
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        $order = "";

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }

        $search_by = get_array_value($options, "search_by");
        $search_by = $search_by ? $this->db->escapeLikeString($search_by) : "";
        $search_by = $this->_get_clean_value($options, "search_by");
        if ($search_by) {
            $labels_table = $this->db->prefixTable("labels");
            $where .= " AND (";
            $where .= " $tickets_table.id LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $tickets_table.title LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $clients_table.company_name LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $project_table.title LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $ticket_types_table.title LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR CONCAT(assigned_table.first_name, ' ',assigned_table.last_name) LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR (SELECT GROUP_CONCAT($labels_table.title, ', ') FROM $labels_table WHERE FIND_IN_SET($labels_table.id, $tickets_table.labels)) LIKE '%$search_by%' ESCAPE '!' ";
            $where .= $this->get_custom_field_search_query($tickets_table, "tickets", $search_by);
            $where .= " )";
        }

        $ticket_client_info_select = "";
        $client_tickets_count_join = "";
        $in_out_message_count_select = "";
        $in_out_ticket_comments_count_join = "";

        if ($id) {
            $ticket_client_info_select = ", $clients_table.phone AS company_phone ";

            //count tickets by client id
            $ticket_client_info_select .= ", client_tickets_table.total_tickets ";
            $client_tickets_count_join = " LEFT JOIN (
                SELECT client_id, COUNT(id) AS total_tickets 
                FROM $tickets_table 
                WHERE deleted=0 
                GROUP BY client_id
            ) AS client_tickets_table 
            ON client_tickets_table.client_id = $tickets_table.client_id ";

            //count in and out messages
            $in_out_message_count_select = ", in_message_table.in_message_count, out_message_table.out_message_count";
            $in_out_ticket_comments_count_join = "
            LEFT JOIN (
                SELECT ticket_id, COUNT(id) AS in_message_count
                FROM $ticket_comments_table
                WHERE deleted=0 AND created_by IN (SELECT id FROM $users_table WHERE $users_table.user_type = 'client')
                GROUP BY ticket_id
            ) AS in_message_table ON in_message_table.ticket_id = $tickets_table.id 
             
            LEFT JOIN (
                SELECT ticket_id, COUNT(id) AS out_message_count
                FROM $ticket_comments_table
                WHERE deleted=0 AND is_note = 0 AND created_by IN (SELECT id FROM $users_table WHERE $users_table.user_type = 'staff')
                GROUP BY ticket_id
            ) AS out_message_table ON out_message_table.ticket_id = $tickets_table.id";
        }

        // $total_message_count_select = "";
        // $show_total_message_count = $this->_get_clean_value($options, "total_message_count");
        // if ($show_total_message_count) {
        //     $total_message_count_select = ", (SELECT COUNT($ticket_comments_table.id) FROM $ticket_comments_table WHERE $ticket_comments_table.ticket_id = $tickets_table.id AND $ticket_comments_table.deleted = 0) AS total_message_count";
        // }

        $sql = "SELECT SQL_CALC_FOUND_ROWS $tickets_table.*, $ticket_types_table.title AS ticket_type, $clients_table.company_name, $project_table.title AS project_title, $task_table.title AS task_title,
              CONCAT(assigned_table.first_name, ' ',assigned_table.last_name) AS assigned_to_user, assigned_table.image as assigned_to_avatar, $select_labels_data_query $select_custom_fieds,
              CONCAT(requested_table.first_name, ' ',requested_table.last_name) AS requested_by_name, requested_table.image as requested_by_avatar, requested_table.email as requested_by_email $ticket_client_info_select $in_out_message_count_select,
              (SELECT GROUP_CONCAT($users_table.first_name, ' ', $users_table.last_name) FROM $users_table WHERE $users_table.deleted = 0 AND FIND_IN_SET($users_table.id, $tickets_table.cc_contacts_and_emails)) AS cc_contacts_list
        FROM $tickets_table
        LEFT JOIN $ticket_types_table ON $ticket_types_table.id= $tickets_table.ticket_type_id
        LEFT JOIN $clients_table ON $clients_table.id= $tickets_table.client_id
        LEFT JOIN $users_table AS assigned_table ON assigned_table.id= $tickets_table.assigned_to
        LEFT JOIN $users_table AS requested_table ON requested_table.id= $tickets_table.requested_by
        LEFT JOIN $project_table ON $project_table.id= $tickets_table.project_id
        LEFT JOIN $task_table ON $task_table.id= $tickets_table.task_id
        $client_tickets_count_join
        $in_out_ticket_comments_count_join
        $join_custom_fieds    
        WHERE $tickets_table.deleted=0 $where $custom_fields_where
        $order $limit_offset";

        $raw_query = $this->db->query($sql);

        if ($limit) {
            $total_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow();

            return array(
                "data" => $raw_query->getResult(),
                "recordsTotal" => $total_rows->found_rows,
                "recordsFiltered" => $total_rows->found_rows,
            );
        } else {
            return $raw_query;
        }
    }

    function count_new_tickets($ticket_types = "", $show_assigned_tickets_only_user_id = 0) {
        $tickets_table = $this->db->prefixTable('tickets');
        $where = "";
        $ticket_types = $this->_get_clean_value($ticket_types);
        $show_assigned_tickets_only_user_id = $this->_get_clean_value($show_assigned_tickets_only_user_id);

        if ($ticket_types) {
            $where = " AND FIND_IN_SET($tickets_table.ticket_type_id, '$ticket_types')";
        }

        if ($show_assigned_tickets_only_user_id) {
            $where .= " AND $tickets_table.assigned_to=$show_assigned_tickets_only_user_id";
        }

        $sql = "SELECT COUNT($tickets_table.id) AS total
        FROM $tickets_table
        WHERE $tickets_table.deleted=0  AND $tickets_table.status='new' $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function get_label_suggestions() {
        $tickets_table = $this->db->prefixTable('tickets');
        $sql = "SELECT GROUP_CONCAT(labels) as label_groups
        FROM $tickets_table
        WHERE $tickets_table.deleted=0";
        return $this->db->query($sql)->getRow()->label_groups;
    }

    function delete_ticket_and_sub_items($ticket_id) {
        $tickets_table = $this->db->prefixTable('tickets');
        $ticket_comments_table = $this->db->prefixTable('ticket_comments');
        $ticket_id = $this->_get_clean_value($ticket_id);

        //get ticket comments info to delete the files from directory 
        $ticket_comments_sql = "SELECT * FROM $ticket_comments_table WHERE $ticket_comments_table.deleted=0 AND $ticket_comments_table.ticket_id=$ticket_id; ";
        $ticket_comments = $this->db->query($ticket_comments_sql)->getResult();

        //delete the ticket and sub items
        $delete_ticket_sql = "UPDATE $tickets_table SET $tickets_table.deleted=1 WHERE $tickets_table.id=$ticket_id; ";
        $this->db->query($delete_ticket_sql);

        $delete_comments_sql = "UPDATE $ticket_comments_table SET $ticket_comments_table.deleted=1 WHERE $ticket_comments_table.ticket_id=$ticket_id; ";
        $this->db->query($delete_comments_sql);

        //delete the files from directory
        $comment_file_path = get_setting("timeline_file_path");

        foreach ($ticket_comments as $comment_info) {
            if ($comment_info->files && $comment_info->files != "a:0:{}") {
                $files = unserialize($comment_info->files);
                foreach ($files as $file) {
                    delete_app_files($comment_file_path, array($file));
                }
            }
        }

        return true;
    }

    function count_tickets($options = array()) {
        $tickets_table = $this->db->prefixTable('tickets');

        $where = "";

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $tickets_table.client_id=$client_id";
        }

        $allowed_ticket_types = $this->_get_clean_value($options, "allowed_ticket_types");
        if ($allowed_ticket_types && count($allowed_ticket_types)) {
            $implode_allowed_ticket_types = implode(",", $allowed_ticket_types);
            $where .= " AND FIND_IN_SET($tickets_table.ticket_type_id, '$implode_allowed_ticket_types')";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND FIND_IN_SET($tickets_table.status, '$status')";
        }

        $show_assigned_tickets_only_user_id = $this->_get_clean_value($options, "show_assigned_tickets_only_user_id");
        if ($show_assigned_tickets_only_user_id) {
            $where .= " AND $tickets_table.assigned_to=$show_assigned_tickets_only_user_id";
        }

        $sql = "SELECT COUNT($tickets_table.id) AS total
        FROM $tickets_table
        WHERE $tickets_table.deleted=0 $where";

        return $this->db->query($sql)->getRow()->total;
    }

    function get_ticket_statistics($options = array()) {
        $tickets_table = $this->db->prefixTable('tickets');
        $ticket_types_table = $this->db->prefixTable('ticket_types');

        $where = "";

        $offset = convert_seconds_to_time_format(get_timezone_offset());

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND DATE(ADDTIME($tickets_table.created_at,'$offset')) BETWEEN '$start_date' AND '$end_date'";
        }


        $status = $this->_get_clean_value($options, "status");
        if ($status === "closed") {
            $where .= " AND $tickets_table.status='$status'";
        }
        if ($status === "open") {
            $where .= " AND FIND_IN_SET($tickets_table.status, 'new,open,client_replied')";
        }

        $ticket_type_id = $this->_get_clean_value($options, "ticket_type_id");
        if ($ticket_type_id) {
            $where .= " AND $tickets_table.ticket_type_id=$ticket_type_id";
        }

        $assigned_to = $this->_get_clean_value($options, "assigned_to");
        if ($assigned_to) {
            $where .= " AND $tickets_table.assigned_to=$assigned_to";
        }

        $ticket_label = $this->_get_clean_value($options, "ticket_label");
        if ($ticket_label) {
            $where .= " AND (FIND_IN_SET('$ticket_label', $tickets_table.labels)) ";
        }


        $allowed_ticket_types = $this->_get_clean_value($options, "allowed_ticket_types");
        if ($allowed_ticket_types && count($allowed_ticket_types)) {
            $implode_allowed_ticket_types = implode(",", $allowed_ticket_types);
            $where .= " AND FIND_IN_SET($tickets_table.ticket_type_id, '$implode_allowed_ticket_types')";
        }

        $show_assigned_tickets_only_user_id = $this->_get_clean_value($options, "show_assigned_tickets_only_user_id");
        if ($show_assigned_tickets_only_user_id) {
            $where .= " AND $tickets_table.assigned_to=$show_assigned_tickets_only_user_id";
        }

        $timeZone = new \DateTimeZone(get_setting("timezone"));
        $dateTime = new \DateTime("now", $timeZone);
        $offset_in_gmt = $dateTime->format('P');
        $select_tz_start_time = "CONVERT_TZ($tickets_table.created_at,'+00:00','$offset_in_gmt')";

        $sql = "";
        $group_by = $this->_get_clean_value($options, "group_by");
        if ($group_by == "created_date") {
            $sql = "SELECT DATE($select_tz_start_time) AS date, DATE_FORMAT($select_tz_start_time,'%d') as day, COUNT($tickets_table.id) AS total
                    FROM $tickets_table 
                    WHERE $tickets_table.deleted=0 $where
                    GROUP BY DATE($select_tz_start_time)";
        } else if ($group_by == "ticket_type") {
            $sql = "SELECT COUNT($tickets_table.id) AS total, $ticket_types_table.id AS ticket_type_id, $ticket_types_table.title AS ticket_type_title
                    FROM $tickets_table
                    LEFT JOIN $ticket_types_table ON $ticket_types_table.id = $tickets_table.ticket_type_id
                    WHERE $tickets_table.deleted=0 AND $tickets_table.status!='closed' $where
                    GROUP BY $tickets_table.ticket_type_id";
        } else if ($group_by == "ticket_status") {
            $sql = "SELECT $tickets_table.status, COUNT($tickets_table.id) AS total
                    FROM $tickets_table
                    WHERE $tickets_table.deleted=0 $where
                    GROUP BY $tickets_table.status";
        }

        return $this->db->query($sql);
    }
}
