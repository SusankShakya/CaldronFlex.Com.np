<?php

namespace App\Models;

class Tasks_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'tasks';
        parent::__construct($this->table);
        parent::init_activity_log("task", "title", "project", "project_id");
    }

    function schema() {
        if (get_setting("show_time_with_task_start_date_and_deadline")) {
            $type = "date_time";
        } else {
            $type = "date";
        }

        return array(
            "id" => array(
                "label" => app_lang("id"),
                "type" => "int"
            ),
            "title" => array(
                "label" => app_lang("title"),
                "type" => "text"
            ),
            "description" => array(
                "label" => app_lang("description"),
                "type" => "text"
            ),
            "assigned_to" => array(
                "label" => app_lang("assigned_to"),
                "type" => "foreign_key",
                "linked_model" => model("App\Models\Users_model"),
                "label_fields" => array("first_name", "last_name"),
            ),
            "collaborators" => array(
                "label" => app_lang("collaborators"),
                "type" => "foreign_key",
                "link_type" => "user_group_list",
                "linked_model" => model("App\Models\Users_model"),
                "label_fields" => array("user_group_name"),
            ),
            "milestone_id" => array(
                "label" => app_lang("milestone"),
                "type" => "foreign_key",
                "linked_model" => model("App\Models\Milestones_model"),
                "label_fields" => array("title"),
            ),
            "labels" => array(
                "label" => app_lang("labels"),
                "type" => "foreign_key",
                "link_type" => "label_group_list",
                "linked_model" => model("App\Models\Labels_model"),
                "label_fields" => array("label_group_name"),
            ),
            "status" => array(
                "label" => app_lang("status"),
                "type" => "language_key" //we'are not using this field from 1.9 but don't delete it for existing data.
            ),
            "status_id" => array(
                "label" => app_lang("status"),
                "type" => "foreign_key",
                "linked_model" => model("App\Models\Task_status_model"),
                "label_fields" => array("title"),
            ),
            "start_date" => array(
                "label" => app_lang("start_date"),
                "type" => $type
            ),
            "deadline" => array(
                "label" => app_lang("deadline"),
                "type" => $type
            ),
            "project_id" => array(
                "label" => app_lang("project"),
                "type" => "foreign_key"
            ),
            "points" => array(
                "label" => app_lang("points"),
                "type" => "int"
            ),
            "deleted" => array(
                "label" => app_lang("deleted"),
                "type" => "int"
            ),
            "sort" => array(
                "label" => app_lang("priority"),
                "type" => "int"
            ),
            "ticket_id" => array(
                "label" => app_lang("ticket"),
                "type" => "foreign_key",
                "linked_model" => model("App\Models\Tickets_model"),
                "label_fields" => array("title"),
            ),
            "no_of_cycles" => array(
                "label" => app_lang("cycles"),
                "type" => "int"
            ),
            "recurring" => array(
                "label" => app_lang("recurring"),
                "type" => "int"
            ),
            "repeat_type" => array(
                "label" => app_lang("repeat_type"),
                "type" => "text"
            ),
            "repeat_every" => array(
                "label" => app_lang("repeat_every"),
                "type" => "int"
            ),
            "priority_id" => array(
                "label" => app_lang("priority"),
                "type" => "foreign_key",
                "linked_model" => model("App\Models\Task_priority_model"),
                "label_fields" => array("title"),
            ),
        );
    }

    function get_details($options = array()) {
        $tasks_table = $this->db->prefixTable('tasks');
        $users_table = $this->db->prefixTable('users');
        $projects_table = $this->db->prefixTable('projects');
        $milestones_table = $this->db->prefixTable('milestones');
        $project_members_table = $this->db->prefixTable('project_members');
        $task_status_table = $this->db->prefixTable('task_status');
        $ticket_table = $this->db->prefixTable('tickets');
        $task_priority_table = $this->db->prefixTable('task_priority');
        $notifications_table = $this->db->prefixTable("notifications");
        $clients_table = $this->db->prefixTable("clients");
        $contracts_table = $this->db->prefixTable("contracts");
        $subscriptions_table = $this->db->prefixTable("subscriptions");
        $expenses_table = $this->db->prefixTable('expenses');
        $invoices_table = $this->db->prefixTable('invoices');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $tasks_table.id=$id";
        }

        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $tasks_table.project_id=$project_id";
        }

        $parent_task_id = $this->_get_clean_value($options, "parent_task_id");
        if ($parent_task_id) {
            $where .= " AND $tasks_table.parent_task_id=$parent_task_id";
        }

        $exclude_task_ids = $this->_get_clean_value($options, "exclude_task_ids");
        if ($exclude_task_ids) {
            $where .= " AND $tasks_table.id NOT IN($exclude_task_ids)";
        }

        $status_ids = $this->_get_clean_value($options, "status_ids");
        if ($status_ids) {
            $where .= " AND FIND_IN_SET($tasks_table.status_id,'$status_ids')";
        }

        $task_ids = $this->_get_clean_value($options, "task_ids");
        if ($task_ids) {
            $where .= " AND $tasks_table.ID IN($task_ids)";
        }

        $exclude_status_id = $this->_get_clean_value($options, "exclude_status_id");
        if ($exclude_status_id) {
            $where .= " AND $tasks_table.status_id!=$exclude_status_id ";
        }

        $assigned_to = $this->_get_clean_value($options, "assigned_to");
        if ($assigned_to) {
            $where .= " AND $tasks_table.assigned_to=$assigned_to";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $tasks_table.client_id=$client_id";
        }

        $lead_id = $this->_get_clean_value($options, "lead_id");
        if ($lead_id) {
            $where .= " AND $tasks_table.lead_id=$lead_id";
        }

        $invoice_id = $this->_get_clean_value($options, "invoice_id");
        if ($invoice_id) {
            $where .= " AND $tasks_table.invoice_id=$invoice_id";
        }

        $estimate_id = $this->_get_clean_value($options, "estimate_id");
        if ($estimate_id) {
            $where .= " AND $tasks_table.estimate_id=$estimate_id";
        }

        $order_id = $this->_get_clean_value($options, "order_id");
        if ($order_id) {
            $where .= " AND $tasks_table.order_id=$order_id";
        }

        $contract_id = $this->_get_clean_value($options, "contract_id");
        if ($contract_id) {
            $where .= " AND $tasks_table.contract_id=$contract_id";
        }

        $proposal_id = $this->_get_clean_value($options, "proposal_id");
        if ($proposal_id) {
            $where .= " AND $tasks_table.proposal_id=$proposal_id";
        }

        $subscription_id = $this->_get_clean_value($options, "subscription_id");
        if ($subscription_id) {
            $where .= " AND $tasks_table.subscription_id=$subscription_id";
        }

        $expense_id = $this->_get_clean_value($options, "expense_id");
        if ($expense_id) {
            $where .= " AND $tasks_table.expense_id=$expense_id";
        }

        $priority_id = $this->_get_clean_value($options, "priority_id");
        if ($priority_id) {
            $where .= " AND $tasks_table.priority_id=$priority_id";
        }

        $specific_user_id = $this->_get_clean_value($options, "specific_user_id");
        if ($specific_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$specific_user_id OR FIND_IN_SET('$specific_user_id', $tasks_table.collaborators))";
        }

        $show_assigned_tasks_only_user_id = $this->_get_clean_value($options, "show_assigned_tasks_only_user_id");
        if ($show_assigned_tasks_only_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$show_assigned_tasks_only_user_id OR FIND_IN_SET('$show_assigned_tasks_only_user_id', $tasks_table.collaborators))";
        }


        $project_status = $this->_get_clean_value($options, "project_status");
        $context_options = get_array_value($options, "context_options");
        if ($project_status && !$context_options) {
            $where .= " AND $projects_table.status_id ='$project_status' ";
        }


        $milestone_id = $this->_get_clean_value($options, "milestone_id");
        if ($milestone_id) {
            $where .= " AND $tasks_table.milestone_id=$milestone_id";
        }

        $context = $this->_get_clean_value($options, "context");
        if ($context) {
            $where .= " AND $tasks_table.context='$context'";
        }

        $task_status_id = $this->_get_clean_value($options, "task_status_id");
        if ($task_status_id) {
            $where .= " AND $tasks_table.status_id=$task_status_id";
        }

        $label_id = $this->_get_clean_value($options, "label_id");
        if ($label_id) {
            $where .= " AND (FIND_IN_SET('$label_id', $tasks_table.labels)) ";
        }

        $where .= $this->make_context_query($context_options, $tasks_table, $clients_table, $ticket_table, $projects_table, $project_members_table, $proposals_table);

        $for_events = $this->_get_clean_value($options, "for_events");

        $start_date = $this->_get_clean_value($options, "start_date");
        $deadline = $this->_get_clean_value($options, "deadline");
        if ($start_date && $deadline) {
            if ($for_events) {
                $deadline_for_events = $this->_get_clean_value($options, "deadline_for_events");
                $start_date_for_events = $this->_get_clean_value($options, "start_date_for_events");

                if ($start_date_for_events && $deadline_for_events) {
                    $where .= " AND ("
                        . "($tasks_table.deadline IS NOT NULL AND $tasks_table.deadline BETWEEN '$start_date' AND '$deadline')"
                        . " OR ($milestones_table.due_date BETWEEN '$start_date' AND '$deadline')"
                        . " OR ($tasks_table.start_date IS NOT NULL AND $tasks_table.start_date BETWEEN '$start_date' AND '$deadline'))";
                } else if ($start_date_for_events) {
                    $where .= " AND ($tasks_table.start_date IS NOT NULL AND $tasks_table.start_date BETWEEN '$start_date' AND '$deadline')";
                } else if ($deadline_for_events) {
                    $where .= " AND (($tasks_table.deadline IS NOT NULL AND $tasks_table.deadline BETWEEN '$start_date' AND '$deadline') OR $milestones_table.due_date BETWEEN '$start_date' AND '$deadline')";
                }
            } else {
                $where .= " AND ($tasks_table.deadline BETWEEN '$start_date' AND '$deadline') ";
            }
        } else if ($deadline) {
            $now = get_my_local_time("Y-m-d");
            if ($deadline === "expired") {
                $where .= " AND IF($tasks_table.deadline IS NULL, $milestones_table.due_date<'$now', $tasks_table.deadline<'$now')";
            } else if ($deadline == $now) { //today
                $where .= " AND IF($tasks_table.deadline IS NULL, $milestones_table.due_date='$deadline', $tasks_table.deadline='$deadline')";
            } else { //future deadlines, extract today's
                $now = add_period_to_date($now, 1);
                $where .= " AND IF($tasks_table.deadline IS NULL, $milestones_table.due_date BETWEEN '$now' AND '$deadline', $tasks_table.deadline BETWEEN '$now' AND '$deadline')";
            }
        }

        $exclude_reminder_date = $this->_get_clean_value($options, "exclude_reminder_date");
        if ($exclude_reminder_date) {
            $where .= " AND ($tasks_table.reminder_date IS NULL OR $tasks_table.reminder_date !='$exclude_reminder_date') ";
        }

        $ticket_id = $this->_get_clean_value($options, "ticket_id");
        if ($ticket_id) {
            $where .= " AND $tasks_table.ticket_id=$ticket_id";
        }

        $order = "";
        $sort_by_project = $this->_get_clean_value($options, "sort_by_project");
        if ($sort_by_project) {
            $order = " ORDER BY $tasks_table.project_id ASC";
        }

        $extra_left_join = "";
        $project_member_id = $this->_get_clean_value($options, "project_member_id");
        if ($project_member_id) {
            $where .= " AND $project_members_table.user_id=$project_member_id";
        } else if (isset($context_options["project"]) && is_array($context_options["project"]) && array_key_exists("project_member_id", $context_options["project"])) {
            $project_member_id = $this->_get_clean_value($context_options["project"]["project_member_id"]);
        }

        if ($project_member_id) {
            $extra_left_join = " LEFT JOIN $project_members_table ON $tasks_table.project_id= $project_members_table.project_id AND $project_members_table.deleted=0 AND $project_members_table.user_id=$project_member_id";
        }

        $quick_filter = $this->_get_clean_value($options, "quick_filter");
        if ($quick_filter) {
            $where .= $this->make_quick_filter_query($quick_filter, $tasks_table);
        }

        $unread_status_user_id = $this->_get_clean_value($options, "unread_status_user_id");
        if (!$unread_status_user_id) {
            $unread_status_user_id = 0;
        }


        $select_labels_data_query = $this->get_labels_data_query();

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }

        $available_order_by_list = array(
            "id" => $tasks_table . ".id",
            "title" => $tasks_table . ".title",
            "start_date" => $tasks_table . ".start_date",
            "deadline" => $tasks_table . ".deadline",
            "milestone" => "milestone_title",
            "assigned_to" => "assigned_to_user",
            "status" => $tasks_table . ".status_id",
            "project" => $projects_table . ".title",
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }

        $search_by = get_array_value($options, "search_by"); //clean later since we need to check the #
        if ($search_by) {
            $search_by = $this->db->escapeLikeString($search_by);
            $labels_table = $this->db->prefixTable("labels");

            if (strpos($search_by, '#') !== false) {
                //get sub tasks of this task
                $search_by = substr($search_by, 1);

                $search_by = $this->_get_clean_value($search_by);

                $where .= " AND ($tasks_table.id='$search_by' OR $tasks_table.parent_task_id='$search_by')";
            } else {
                $search_by = $this->_get_clean_value($search_by);

                $where .= " AND (";
                $where .= " $tasks_table.id LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $tasks_table.title LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $tasks_table.start_date LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $tasks_table.deadline LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $milestones_table.title LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR CONCAT($users_table.first_name, ' ', $users_table.last_name) LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $task_status_table.key_name LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $task_status_table.title LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $projects_table.title LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR (SELECT GROUP_CONCAT($labels_table.title, ', ') FROM $labels_table WHERE FIND_IN_SET($labels_table.id, $tasks_table.labels)) LIKE '%$search_by%' ESCAPE '!' ";
                $where .= $this->get_custom_field_search_query($tasks_table, "tasks", $search_by);
                $where .= " )";
            }
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");

        $custom_field_query_info = $this->prepare_custom_field_query_string("tasks", $custom_fields, $tasks_table, $custom_field_filter);

        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $sql = "SELECT SQL_CALC_FOUND_ROWS $tasks_table.*, $task_status_table.key_name AS status_key_name, $task_status_table.title AS status_title,  $task_status_table.color AS status_color, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS assigned_to_user, $users_table.image as assigned_to_avatar, $users_table.user_type,
                    $projects_table.title AS project_title, $milestones_table.title AS milestone_title, IF($tasks_table.deadline IS NULL, $milestones_table.due_date,$tasks_table.deadline) AS deadline,$ticket_table.title AS ticket_title,
                    (SELECT GROUP_CONCAT($users_table.id, '--::--', $users_table.first_name, ' ', $users_table.last_name, '--::--' , IFNULL($users_table.image,''), '--::--', $users_table.user_type) FROM $users_table WHERE $users_table.deleted=0 AND FIND_IN_SET($users_table.id, $tasks_table.collaborators)) AS collaborator_list,
                    $task_priority_table.title AS priority_title, $task_priority_table.icon AS priority_icon, $task_priority_table.color AS priority_color,
                    IFNULL($clients_table.company_name, leads_table.company_name) AS company_name,
                    $contracts_table.title AS contract_title,
                    $subscriptions_table.title AS subscription_title,
                    $expenses_table.expense_date, $expenses_table.title AS expense_title,
                    $invoices_table.display_id AS invoice_display_id,
                    IF($tasks_table.deadline IS NULL, $milestones_table.title, '') AS deadline_milestone_title, notification_table.task_id AS unread, sub_task_table.total_sub_tasks AS has_sub_tasks, $select_labels_data_query $select_custom_fieds 
                        
        FROM $tasks_table
        LEFT JOIN $users_table ON $users_table.id= $tasks_table.assigned_to
        LEFT JOIN $projects_table ON $tasks_table.project_id=$projects_table.id 
        LEFT JOIN $milestones_table ON $tasks_table.milestone_id=$milestones_table.id 
        LEFT JOIN $contracts_table ON $contracts_table.id=$tasks_table.contract_id 
        LEFT JOIN $subscriptions_table ON $subscriptions_table.id=$tasks_table.subscription_id 
        LEFT JOIN $expenses_table ON $expenses_table.id=$tasks_table.expense_id 
        LEFT JOIN $clients_table ON $clients_table.id=$tasks_table.client_id
        LEFT JOIN $clients_table AS leads_table ON leads_table.id=$tasks_table.lead_id
        LEFT JOIN $task_status_table ON $tasks_table.status_id = $task_status_table.id 
        LEFT JOIN $task_priority_table ON $tasks_table.priority_id = $task_priority_table.id 
        LEFT JOIN $ticket_table ON $tasks_table.ticket_id = $ticket_table.id
        LEFT JOIN $invoices_table ON $tasks_table.invoice_id = $invoices_table.id
        LEFT JOIN (SELECT COUNT($tasks_table.id) AS total_sub_tasks, $tasks_table.parent_task_id FROM $tasks_table WHERE $tasks_table.deleted = 0 AND $tasks_table.parent_task_id!=0 GROUP BY $tasks_table.parent_task_id) AS sub_task_table ON sub_task_table.parent_task_id = $tasks_table.id
        LEFT JOIN (SELECT $notifications_table.task_id FROM $notifications_table WHERE $notifications_table.deleted=0 AND $notifications_table.event='project_task_commented' AND !FIND_IN_SET('$unread_status_user_id', $notifications_table.read_by) AND $notifications_table.user_id!=$unread_status_user_id  GROUP BY $notifications_table.task_id) AS notification_table ON notification_table.task_id = $tasks_table.id
        $extra_left_join 
        $join_custom_fieds 
        WHERE $tasks_table.deleted=0 $where $custom_fields_where 
        $order $limit_offset";

        $raw_query = $this->db->query($sql);

        $total_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow();

        if ($limit) {
            return array(
                "data" => $raw_query->getResult(),
                "recordsTotal" => $total_rows->found_rows,
                "recordsFiltered" => $total_rows->found_rows,
            );
        } else {
            return $raw_query;
        }
    }

    private function make_context_query($context_options, $tasks_table, $clients_table, $tickets_table, $projects_table, $project_members_table, $proposals_table) {
        if (!$context_options) {
            return "";
        }

        $estimates_table = $this->db->prefixTable("estimates");
        $context_where = "";

        foreach ($context_options as $context => $context_permissions) {
            $context_where_for_this_context = "";
            $project_where = "";

            foreach ($context_permissions as $context_permission => $permission_value) {
                //has restriction on this context, check each permissions

                if (!$permission_value) {
                    continue;
                }

                $permission_value =  $this->_get_clean_value($permission_value);

                if ($context_permission === "project_status") {
                    $project_where .= " AND $projects_table.status_id = '$permission_value' ";
                } else if ($context_permission === "project_member_id") {
                    $project_where .= " AND $project_members_table.user_id=$permission_value ";
                } else if ($context_permission === "show_assigned_tasks_only_user_id") {
                    $project_where .= " AND ($tasks_table.assigned_to=$permission_value OR FIND_IN_SET('$permission_value', $tasks_table.collaborators)) ";
                } else if ($context_permission === "show_own_clients_only_user_id") {
                    $context_where_for_this_context .= " ($tasks_table.context='$context'
                                                AND $tasks_table.client_id IN(
                                                    SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0 AND ($clients_table.created_by=$permission_value OR $clients_table.owner_id=$permission_value OR FIND_IN_SET('$permission_value', $clients_table.managers))
                                                )
                                        )";
                } else if ($context_permission === "client_groups") {
                    $client_groups_where_query = $this->prepare_allowed_client_groups_query($clients_table, explode(',', $permission_value));
                    $context_where_for_this_context .= " ($tasks_table.context='$context'
                                                AND $tasks_table.client_id IN(
                                                    SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0 $client_groups_where_query
                                                )
                                        )";
                } else if ($context_permission === "show_own_leads_only_user_id") {
                    $context_where_for_this_context .= " ($tasks_table.context='$context'
                                                AND $tasks_table.lead_id IN(
                                                    SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.is_lead=1 AND ($clients_table.owner_id=$permission_value OR FIND_IN_SET('$permission_value', $clients_table.managers))
                                                )
                                        )";
                } else if ($context_permission === "show_own_estimates_only_user_id") {
                    $context_where_for_this_context .= " ($tasks_table.context='$context'
                                                AND $tasks_table.estimate_id IN(
                                                    SELECT $estimates_table.id FROM $estimates_table WHERE $estimates_table.deleted=0 AND $estimates_table.created_by=$permission_value
                                                )
                                        )";
                } else if ($context_permission === "show_assigned_tickets_only_user_id") {
                    $context_where_for_this_context .= " ($tasks_table.context='$context'
                                                AND $tasks_table.ticket_id IN(
                                                    SELECT $tickets_table.id FROM $tickets_table WHERE $tickets_table.deleted=0 AND $tickets_table.assigned_to=$permission_value
                                                )
                                        )";
                } else if ($context_permission === "ticket_types") {
                    $context_where_for_this_context .= " ($tasks_table.context='$context'
                                                AND $tasks_table.ticket_id IN(
                                                    SELECT $tickets_table.id FROM $tickets_table WHERE $tickets_table.deleted=0 AND FIND_IN_SET($tickets_table.ticket_type_id, '$permission_value')
                                                )
                                        )";
                } else if ($context_permission === "show_own_proposals_only_user_id") {
                    $context_where_for_this_context .= " ($tasks_table.context='$context'
                                                AND $tasks_table.proposal_id IN(
                                                    SELECT $proposals_table.id FROM $proposals_table WHERE $proposals_table.deleted=0 AND $proposals_table.created_by=$permission_value
                                                )
                                        )";
                }
            }

            if ($project_where) {
                $context_where_for_this_context .= " ($tasks_table.context='$context' $project_where )";
            }

            if ($context_where) {
                $context_where .= " OR ";
            }

            if ($context_where_for_this_context) {
                $context_where .= $context_where_for_this_context;
            } else {
                //has no restriction on this context
                $context_where .= " $tasks_table.context='$context' ";
            }
        }

        if ($context_where) {
            $context_where = " AND ($context_where) ";
        }

        return $context_where;
    }

    private function make_quick_filter_query($filter, $tasks_table) {
        $project_comments_table = $this->db->prefixTable("project_comments");
        $query = "";

        if ($filter == "recently_meaning") {
            return $query;
        }

        $users_model = model("App\Models\Users_model", false);
        $login_user_id = $users_model->login_user_id();

        $recently_updated_last_time = prepare_last_recently_date_time($login_user_id);

        $project_comments_table_query = "SELECT $project_comments_table.task_id 
                           FROM $project_comments_table 
                           WHERE $project_comments_table.deleted=0 AND $project_comments_table.task_id!=0";
        $project_comments_table_group_by = "GROUP BY $project_comments_table.task_id";

        if ($filter === "recently_updated") {
            $query = " AND ($tasks_table.status_changed_at IS NOT NULL AND $tasks_table.status_changed_at>='$recently_updated_last_time')";
        } else if ($filter === "recently_commented") {
            $query = " AND $tasks_table.id IN($project_comments_table_query AND $project_comments_table.created_at>='$recently_updated_last_time' $project_comments_table_group_by)";
        } else if ($filter === "mentioned_me" && $login_user_id) {
            $mention_string = ":" . $login_user_id . "]";
            $query = " AND $tasks_table.id IN($project_comments_table_query AND $project_comments_table.description LIKE '%$mention_string%' $project_comments_table_group_by)";
        } else if ($filter === "recently_mentioned_me" && $login_user_id) {
            $mention_string = ":" . $login_user_id . "]";
            $query = " AND $tasks_table.id IN($project_comments_table_query AND $project_comments_table.description LIKE '%$mention_string%' AND $project_comments_table.created_at>='$recently_updated_last_time' $project_comments_table_group_by)";
        } else if ($filter === "recurring_tasks") {
            $query = " AND ($tasks_table.recurring=1)";
        } else {
            $query = " AND ($tasks_table.status_changed_at IS NOT NULL AND $tasks_table.status_changed_at>='$recently_updated_last_time' AND $tasks_table.status_id=$filter)";
        }

        return $query;
    }

    function get_kanban_details($options = array()) {
        $tasks_table = $this->db->prefixTable('tasks');
        $users_table = $this->db->prefixTable('users');
        $projects_table = $this->db->prefixTable('projects');
        $milestones_table = $this->db->prefixTable('milestones');
        $labels_table = $this->db->prefixTable('labels');
        $project_members_table = $this->db->prefixTable('project_members');
        $task_priority_table = $this->db->prefixTable('task_priority');
        $clients_table = $this->db->prefixTable('clients');
        $notifications_table = $this->db->prefixTable("notifications");
        $checklist_items_table = $this->db->prefixTable('checklist_items');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $tasks_table.id=$id";
        }

        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $tasks_table.project_id=$project_id";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $projects_table.client_id=$client_id";
        }


        $status_id = $this->_get_clean_value($options, "status_id");
        if ($status_id) {
            $where .= " AND $tasks_table.status_id = $status_id";
        }

        $get_after_max_sort = $this->_get_clean_value($options, "get_after_max_sort");
        if ($get_after_max_sort) {
            $where .= " AND $tasks_table.sort > $get_after_max_sort";
        }


        $project_status = $this->_get_clean_value($options, "project_status");
        $context_options = get_array_value($options, "context_options");
        if ($project_status && !$context_options) {
            $where .= " AND $projects_table.status_id = '$project_status'";
        }


        $assigned_to = $this->_get_clean_value($options, "assigned_to");
        if ($assigned_to) {
            $where .= " AND $tasks_table.assigned_to=$assigned_to";
        }

        $priority_id = $this->_get_clean_value($options, "priority_id");
        if ($priority_id) {
            $where .= " AND $tasks_table.priority_id=$priority_id";
        }

        $specific_user_id = $this->_get_clean_value($options, "specific_user_id");
        if ($specific_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$specific_user_id OR FIND_IN_SET('$specific_user_id', $tasks_table.collaborators))";
        }

        $show_assigned_tasks_only_user_id = $this->_get_clean_value($options, "show_assigned_tasks_only_user_id");
        if ($show_assigned_tasks_only_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$show_assigned_tasks_only_user_id OR FIND_IN_SET('$show_assigned_tasks_only_user_id', $tasks_table.collaborators))";
        }

        $milestone_id = $this->_get_clean_value($options, "milestone_id");
        if ($milestone_id) {
            $where .= " AND $tasks_table.milestone_id=$milestone_id";
        }

        $context = $this->_get_clean_value($options, "context");
        if ($context) {
            $where .= " AND $tasks_table.context='$context'";
        }

        $label_id = $this->_get_clean_value($options, "label_id");
        if ($label_id) {
            $where .= " AND (FIND_IN_SET('$label_id', $tasks_table.labels)) ";
        }

        $deadline = $this->_get_clean_value($options, "deadline");
        if ($deadline) {
            $now = get_my_local_time("Y-m-d");
            if ($deadline === "expired") {
                $where .= " AND IF($tasks_table.deadline IS NULL, $milestones_table.due_date<'$now', $tasks_table.deadline<'$now')";
            } else if ($deadline == $now) { //today
                $where .= " AND IF($tasks_table.deadline IS NULL, $milestones_table.due_date='$deadline', $tasks_table.deadline='$deadline')";
            } else { //future deadlines, extract today's
                $now = add_period_to_date($now, 1);
                $where .= " AND IF($tasks_table.deadline IS NULL, $milestones_table.due_date BETWEEN '$now' AND '$deadline', $tasks_table.deadline BETWEEN '$now' AND '$deadline')";
            }
        }

        $search = get_array_value($options, "search");

        if ($search) {
            if (strpos($search, '#') !== false) {
                //get sub tasks of this task
                $search = substr($search, 1);
                $search = $this->_get_clean_value($search);
                $where .= " AND ($tasks_table.id='$search' OR $tasks_table.parent_task_id='$search')";
            } else {
                //normal search
                $search = $this->db->escapeLikeString($search);
                $search = $this->_get_clean_value($search);
                $where .= " AND ($tasks_table.title LIKE '%$search%' ESCAPE '!' OR FIND_IN_SET((SELECT $labels_table.id FROM $labels_table WHERE $labels_table.deleted=0 AND $labels_table.context='task' AND $labels_table.title LIKE '%$search%' ESCAPE '!' LIMIT 1), $tasks_table.labels) OR $tasks_table.id='$search')";
            }
        }

        $limit_sql = "";

        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $limit_sql = " LIMIT $limit";
        }


        $extra_left_join = "";
        $project_member_id = $this->_get_clean_value($options, "project_member_id");
        if ($project_member_id) {
            $where .= " AND $project_members_table.user_id=$project_member_id";
        } else if (isset($context_options["project"]) && is_array($context_options["project"]) && array_key_exists("project_member_id", $context_options["project"])) {
            $project_member_id = $this->_get_clean_value($context_options["project"]["project_member_id"]);
        }

        if ($project_member_id) {
            $extra_left_join = " LEFT JOIN $project_members_table ON $tasks_table.project_id= $project_members_table.project_id AND $project_members_table.deleted=0 AND $project_members_table.user_id=$project_member_id";
        }

        $quick_filter = $this->_get_clean_value($options, "quick_filter");
        if ($quick_filter) {
            $where .= $this->make_quick_filter_query($quick_filter, $tasks_table);
        }

        $ticket_table = $this->db->prefixTable('tickets');
        $where .= $this->make_context_query($context_options, $tasks_table, $clients_table, $ticket_table, $projects_table, $project_members_table, $proposals_table);

        $custom_field_filter = $this->_get_clean_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("tasks", "", $tasks_table, $custom_field_filter);
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $unread_status_user_id = $this->_get_clean_value($options, "unread_status_user_id");
        if (!$unread_status_user_id) {
            $unread_status_user_id = 0;
        }

        $select_labels_data_query = $this->get_labels_data_query();

        $this->db->query('SET SQL_BIG_SELECTS=1');

        if (get_array_value($options, "return_task_counts_only")) {
            $sql = "SELECT COUNT($tasks_table.id) as tasks_count, $tasks_table.status_id
            FROM $tasks_table
            LEFT JOIN $projects_table ON $tasks_table.project_id=$projects_table.id 
            LEFT JOIN $milestones_table ON $tasks_table.milestone_id=$milestones_table.id 
            $extra_left_join
            WHERE $tasks_table.deleted=0 $where $custom_fields_where 
            GROUP BY $tasks_table.status_id";
        } else {
            $sql = "SELECT $tasks_table.id, $tasks_table.context, $tasks_table.created_by, $tasks_table.title, $tasks_table.start_date, $tasks_table.deadline, $tasks_table.sort, IF($tasks_table.sort!=0, $tasks_table.sort, $tasks_table.id) AS new_sort, $tasks_table.assigned_to, $tasks_table.labels, $tasks_table.status_id, $tasks_table.project_id, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS assigned_to_user, $tasks_table.priority_id, $projects_table.title AS project_title, 
                (SELECT $clients_table.company_name FROM $clients_table WHERE id=(
                    IF($tasks_table.context='client', $tasks_table.client_id, (SELECT $projects_table.client_id FROM $projects_table WHERE $projects_table.id=$tasks_table.project_id))
                )) AS client_name, 
                $projects_table.project_type AS project_type,
                $task_priority_table.title AS priority_title, $task_priority_table.icon AS priority_icon, $task_priority_table.color AS priority_color,
                $users_table.image as assigned_to_avatar, $tasks_table.parent_task_id, sub_tasks_table.id AS has_sub_tasks, parent_tasks_table.title AS parent_task_title, $notifications_table.id AS unread, 
                (SELECT COUNT($checklist_items_table.id) FROM $checklist_items_table WHERE $checklist_items_table.deleted=0 AND $checklist_items_table.task_id=$tasks_table.id) AS total_checklist,
                (SELECT COUNT($checklist_items_table.id) FROM $checklist_items_table WHERE $checklist_items_table.is_checked=1 AND $checklist_items_table.deleted=0 AND $checklist_items_table.task_id=$tasks_table.id) AS total_checklist_checked,
                COUNT(sub_tasks_table.id) AS total_sub_tasks, completed_sub_tasks_table.total_sub_tasks_done, $tasks_table.client_id, $tasks_table.contract_id, $tasks_table.estimate_id, $tasks_table.expense_id, $tasks_table.invoice_id, $tasks_table.lead_id, $tasks_table.order_id, $tasks_table.proposal_id, $tasks_table.subscription_id, $tasks_table.ticket_id, $tasks_table.collaborators,
                $select_labels_data_query
        FROM $tasks_table
        LEFT JOIN (
            SELECT $tasks_table.id, $tasks_table.parent_task_id, $tasks_table.title
            FROM $tasks_table 
            WHERE $tasks_table.deleted=0 AND $tasks_table.parent_task_id!=0
        ) AS sub_tasks_table ON sub_tasks_table.parent_task_id=$tasks_table.id
        LEFT JOIN (
            SELECT COUNT($tasks_table.id) as total_sub_tasks_done, $tasks_table.parent_task_id
            FROM $tasks_table 
            WHERE $tasks_table.deleted=0 AND $tasks_table.parent_task_id!=0 AND $tasks_table.status_id=3
            GROUP BY $tasks_table.parent_task_id
        ) AS completed_sub_tasks_table ON completed_sub_tasks_table.parent_task_id=$tasks_table.id
        LEFT JOIN (
            SELECT $tasks_table.id, $tasks_table.parent_task_id, $tasks_table.title
            FROM $tasks_table 
            WHERE $tasks_table.deleted=0
        ) AS parent_tasks_table ON parent_tasks_table.id=$tasks_table.parent_task_id
        LEFT JOIN $users_table ON $users_table.id= $tasks_table.assigned_to
        LEFT JOIN $projects_table ON $tasks_table.project_id=$projects_table.id 
        LEFT JOIN $task_priority_table ON $tasks_table.priority_id = $task_priority_table.id 
        LEFT JOIN $milestones_table ON $tasks_table.milestone_id=$milestones_table.id 
        LEFT JOIN $notifications_table ON $notifications_table.task_id = $tasks_table.id AND $notifications_table.deleted=0 AND $notifications_table.event='project_task_commented' AND !FIND_IN_SET('$unread_status_user_id', $notifications_table.read_by) AND $notifications_table.user_id!=$unread_status_user_id
        $extra_left_join
        WHERE $tasks_table.deleted=0 $where $custom_fields_where 
        GROUP BY $tasks_table.id
        ORDER BY $tasks_table.sort ASC $limit_sql";
        }

        return $this->db->query($sql);
    }

    function count_my_open_tasks($user_id) {
        $tasks_table = $this->db->prefixTable('tasks');
        $projects_table = $this->db->prefixTable('projects');

        $user_id =  $this->_get_clean_value($user_id);

        $sql = "SELECT COUNT($tasks_table.id) AS total
        FROM $tasks_table
        WHERE $tasks_table.deleted=0 AND (($tasks_table.assigned_to=$user_id OR FIND_IN_SET('$user_id', $tasks_table.collaborators)) AND $tasks_table.status_id !=3
        AND ($tasks_table.project_id IN (
            SELECT $projects_table.id
            FROM $projects_table
            WHERE $projects_table.deleted = 0 AND $projects_table.status_id='1') OR $tasks_table.context !='project'))";

        return $this->db->query($sql)->getRow()->total;
    }

    function get_label_suggestions($project_id) {
        $project_id = $this->_get_clean_value($project_id);

        $tasks_table = $this->db->prefixTable('tasks');
        $sql = "SELECT GROUP_CONCAT(labels) as label_groups
        FROM $tasks_table
        WHERE $tasks_table.deleted=0 AND $tasks_table.project_id=$project_id";
        return $this->db->query($sql)->getRow()->label_groups;
    }

    function get_my_projects_dropdown_list($user_id = 0) {
        $project_members_table = $this->db->prefixTable('project_members');
        $projects_table = $this->db->prefixTable('projects');

        $where = "";
        if ($user_id) {
            $user_id = $this->_get_clean_value($user_id);
            $where .= " AND $project_members_table.user_id=$user_id";
        }

        $sql = "SELECT $project_members_table.project_id, $projects_table.title AS project_title
        FROM $project_members_table
        LEFT JOIN $projects_table ON $projects_table.id= $project_members_table.project_id
        WHERE $project_members_table.deleted=0 AND $projects_table.deleted=0 $where 
        GROUP BY $project_members_table.project_id";
        return $this->db->query($sql);
    }

    function get_task_statistics($options = array()) {
        $tasks_table = $this->db->prefixTable('tasks');
        $task_status_table = $this->db->prefixTable('task_status');
        $task_priority_table = $this->db->prefixTable('task_priority');
        $project_members_table = $this->db->prefixTable('project_members');
        $milestones_table = $this->db->prefixTable('milestones');

        try {
            $this->db->query("SET sql_mode = ''");
        } catch (\Exception $e) {
        }
        $where = "";

        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $tasks_table.project_id=$project_id";
        }

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $tasks_table.assigned_to=$user_id";
        }

        $show_assigned_tasks_only_user_id = $this->_get_clean_value($options, "show_assigned_tasks_only_user_id");
        if ($show_assigned_tasks_only_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$show_assigned_tasks_only_user_id OR FIND_IN_SET('$show_assigned_tasks_only_user_id', $tasks_table.collaborators))";
        }

        $extra_left_join = "";
        $project_member_id = $this->_get_clean_value($options, "project_member_id");
        if ($project_member_id) {
            $where .= " AND $project_members_table.user_id=$project_member_id";
            $extra_left_join = " LEFT JOIN $project_members_table ON $tasks_table.project_id= $project_members_table.project_id AND $project_members_table.deleted=0 AND $project_members_table.user_id=$project_member_id";
        }

        $now = get_my_local_time("Y-m-d");

        $task_statuses = "SELECT COUNT($tasks_table.id) AS total, $tasks_table.status_id, $task_status_table.key_name, $task_status_table.title, $task_status_table.color
        FROM $tasks_table
        LEFT JOIN $task_status_table ON $task_status_table.id = $tasks_table.status_id
        $extra_left_join
        WHERE $tasks_table.deleted=0 $where
        GROUP BY $tasks_table.status_id
        ORDER BY $task_status_table.sort ASC";

        $task_priorities = "SELECT COUNT($tasks_table.id) AS total, $tasks_table.priority_id, $task_priority_table.title, $task_priority_table.icon, $task_priority_table.color
        FROM $tasks_table
        LEFT JOIN $task_priority_table ON $task_priority_table.id = $tasks_table.priority_id
        $extra_left_join
        WHERE $tasks_table.deleted=0 AND $tasks_table.priority_id!=0 AND $tasks_table.status_id!=3 $where
        GROUP BY $tasks_table.priority_id
        ORDER BY $tasks_table.priority_id DESC";

        $expired_tasks = "SELECT COUNT($tasks_table.id) AS total
        FROM $tasks_table
        LEFT JOIN $milestones_table ON $tasks_table.milestone_id=$milestones_table.id 
        $extra_left_join
        WHERE $tasks_table.deleted=0 AND IF($tasks_table.deadline IS NULL, $milestones_table.due_date<'$now', $tasks_table.deadline<'$now') AND $tasks_table.status_id!=3 $where";

        $info = new \stdClass();
        $info->task_statuses = $this->db->query($task_statuses)->getResult();
        $info->task_priorities = $this->db->query($task_priorities)->getResult();
        $info->expired_tasks = $this->db->query($expired_tasks)->getRow()->total;

        return $info;
    }

    function set_task_comments_as_read($task_id, $user_id = 0) {
        $notifications_table = $this->db->prefixTable('notifications');

        $task_id = $this->_get_clean_value($task_id);
        $user_id = $this->_get_clean_value($user_id);

        $sql = "UPDATE $notifications_table SET $notifications_table.read_by = CONCAT($notifications_table.read_by,',',$user_id)
        WHERE $notifications_table.task_id=$task_id AND FIND_IN_SET($user_id, $notifications_table.read_by) = 0 AND $notifications_table.event='project_task_commented'";
        return $this->db->query($sql);
    }

    function save_reminder_date(&$data = array(), $id = 0) {
        if ($id) {
            $where = array("id" => $id);
            $this->update_where($data, $where);
        }
    }

    //get the recurring tasks which are ready to renew as on a given date
    function get_renewable_tasks($date) {
        $tasks_table = $this->db->prefixTable('tasks');

        $date = $this->_get_clean_value($date);

        $sql = "SELECT * FROM $tasks_table
                        WHERE $tasks_table.deleted=0 AND $tasks_table.recurring=1
                        AND $tasks_table.next_recurring_date IS NOT NULL AND $tasks_table.next_recurring_date<='$date'
                        AND ($tasks_table.no_of_cycles < 1 OR ($tasks_table.no_of_cycles_completed < $tasks_table.no_of_cycles ))";

        return $this->db->query($sql);
    }

    function get_all_dependency_for_this_task($task_id, $type) {
        $tasks_table = $this->db->prefixTable('tasks');

        $task_id = $this->_get_clean_value($task_id);

        $where = "";
        if ($type == "blocked_by") {
            $where = "AND $tasks_table.blocking LIKE '%$task_id%'";
        } else {
            $where = "AND $tasks_table.blocked_by LIKE '%$task_id%'";
        }

        $sql = "SELECT GROUP_CONCAT($tasks_table.id) AS dependency_task_ids FROM $tasks_table WHERE $tasks_table.deleted=0 AND $tasks_table.id!=$task_id $where";

        return $this->db->query($sql)->getRow()->dependency_task_ids;
    }

    function update_custom_data(&$data = array(), $id = 0) {
        if ($id) {
            $where = array("id" => $id);
            $this->update_where($data, $where);

            return $id;
        }
    }

    function get_search_suggestion($search = "", $options = array()) {
        $tasks_table = $this->db->prefixTable('tasks');
        $project_members_table = $this->db->prefixTable('project_members');
        $clients_table = $this->db->prefixTable('clients');
        $ticket_table = $this->db->prefixTable('tickets');
        $projects_table = $this->db->prefixTable('projects');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";

        $search = $this->_get_clean_value($search);
        if ($search) {
            $search = $this->db->escapeLikeString($search);
            $where .= " AND ($tasks_table.title LIKE '%$search%' ESCAPE '!' OR $tasks_table.id LIKE '%$search%' ESCAPE '!') ";
        }

        $context_options = get_array_value($options, "context_options");
        $where .= $this->make_context_query($context_options, $tasks_table, $clients_table, $ticket_table, $projects_table, $project_members_table, $proposals_table);

        $extra_left_join = "";
        $project_member_id = $this->_get_clean_value($options, "project_member_id");
        if ($project_member_id) {
            $where .= " AND $project_members_table.user_id=$project_member_id";
        } else if (isset($context_options["project"]) && is_array($context_options["project"]) && array_key_exists("project_member_id", $context_options["project"])) {
            $project_member_id = $this->_get_clean_value($context_options["project"]["project_member_id"]);
        }

        if ($project_member_id) {
            $extra_left_join = " LEFT JOIN $project_members_table ON $tasks_table.project_id= $project_members_table.project_id AND $project_members_table.deleted=0 AND $project_members_table.user_id=$project_member_id";
        }

        $sql = "SELECT $tasks_table.id, $tasks_table.title
        FROM $tasks_table
        LEFT JOIN $projects_table ON $projects_table.id=$tasks_table.project_id
        LEFT JOIN $clients_table ON $clients_table.id=$projects_table.client_id
        LEFT JOIN $ticket_table ON $ticket_table.id=$tasks_table.ticket_id
        $extra_left_join
        WHERE $tasks_table.deleted=0 $where
        ORDER BY $tasks_table.title ASC
        LIMIT 0, 10";

        return $this->db->query($sql);
    }

    function get_all_tasks_where_have_dependency($project_id) {
        $tasks_table = $this->db->prefixTable('tasks');

        $project_id = $this->_get_clean_value($project_id);

        $sql = "SELECT $tasks_table.id, $tasks_table.blocked_by, $tasks_table.blocking
        FROM $tasks_table  
        WHERE $tasks_table.deleted=0 AND $tasks_table.project_id=$project_id AND ($tasks_table.blocked_by!='' OR $tasks_table.blocking!='')";

        return $this->db->query($sql);
    }

    function save_gantt_task_date($data, $task_id) {
        parent::disable_log_activity();
        return $this->ci_save($data, $task_id);
    }

    function count_sub_task_status($options = array()) {
        $tasks_table = $this->db->prefixTable('tasks');

        $where = "";

        $parent_task_id = $this->_get_clean_value($options, "parent_task_id");
        if ($parent_task_id) {
            $where .= " AND $tasks_table.parent_task_id=$parent_task_id";
        }

        $status_id = $this->_get_clean_value($options, "status_id");
        if ($status_id) {
            $where .= " AND $tasks_table.status_id=$status_id";
        }

        $sql = "SELECT COUNT($tasks_table.id) AS total
        FROM $tasks_table
        WHERE $tasks_table.deleted=0 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function delete_task_and_sub_items($task_id) {
        $tasks_table = $this->db->prefixTable('tasks');
        $project_comments_table = $this->db->prefixTable("project_comments");
        $checklist_items_table = $this->db->prefixTable("checklist_items");

        $task_id = $this->_get_clean_value($task_id);

        //get task comment files info to delete the files from directory 
        $task_comment_files_sql = "SELECT * FROM $project_comments_table WHERE $project_comments_table.deleted=0 AND $project_comments_table.task_id=$task_id; ";
        $task_comments = $this->db->query($task_comment_files_sql)->getResult();

        //delete comments
        $delete_comments_sql = "UPDATE $project_comments_table SET $project_comments_table.deleted=1 WHERE $project_comments_table.task_id=$task_id; ";
        $this->db->query($delete_comments_sql);

        //delete task
        $delete_task_sql = "UPDATE $tasks_table SET $tasks_table.deleted=1 WHERE $tasks_table.id=$task_id; ";
        $this->db->query($delete_task_sql);

        //delete checklists 
        $delete_checklists_sql = "UPDATE $checklist_items_table SET $checklist_items_table.deleted=1 WHERE $checklist_items_table.task_id=$task_id; ";
        $this->db->query($delete_checklists_sql);

        //delete the task comment files from directory
        $comment_file_path = get_setting("timeline_file_path");

        foreach ($task_comments as $comment_info) {
            if ($comment_info->files && $comment_info->files != "a:0:{}") {
                $files = unserialize($comment_info->files);
                foreach ($files as $file) {
                    delete_app_files($comment_file_path, array($file));
                }
            }
        }

        return true;
    }

    function get_next_sort_value($project_id = 0, $status_id = 1) {
        $tasks_table = $this->db->prefixTable('tasks');

        $project_id = $this->_get_clean_value($project_id);
        $status_id = $this->_get_clean_value($status_id);

        $where = "";

        if ($project_id) {
            $where .= " AND $tasks_table.project_id = $project_id ";
        }


        if (!$status_id) {
            return 1000;
        }

        $sql = "SELECT $tasks_table.sort
        FROM $tasks_table
        WHERE $tasks_table.deleted=0 AND $tasks_table.status_id = $status_id $where
        ORDER BY $tasks_table.sort DESC LIMIT 1";

        $row = $this->db->query($sql)->getRow();

        if ($row) {
            return $row->sort + 10;
        } else {
            return 1000; //could be any positive value
        }
    }
}
