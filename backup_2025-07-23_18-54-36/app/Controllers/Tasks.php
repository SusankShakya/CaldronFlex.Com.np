<?php

namespace App\Controllers;

use App\Libraries\Excel_import;

class Tasks extends Security_Controller {

    use Excel_import;

    protected $Task_priority_model;
    protected $Checklist_items_model;
    protected $Pin_comments_model;
    protected $Project_settings_model;
    private $project_member_memory = array(); //array([project_id]=>true/false)
    private $project_client_memory = array(); //array([project_id]=>true/false)
    private $can_edit_client_memory = array(); //array([client_id]=>true/false, [any_clients]=>true/false)
    private $can_access_lead_memory = array(); //array([client_id]=>true/false, [any_leads]=>true/false)
    private $can_access_estimate_memory = array(); //array([client_id]=>true/false, [any_estimates]=>true/false)
    private $can_edit_subscription_memory = array(); //array([client_id]=>true/false, [any_subscriptions]=>true/false)
    private $can_edit_ticket_memory = array(); //array([client_id]=>true/false, [any_tickets]=>true/false)
    private $projects_id_by_title = array();
    private $milestones_id_by_title = array();
    private $users_id_by_name = array();
    private $task_statuses_id_by_title = array();
    private $task_labels_id_by_title = array();
    private $task_priorities_id_by_title = array();

    public function __construct() {
        parent::__construct();
        $this->Task_priority_model = model("App\Models\Task_priority_model");
        $this->Checklist_items_model = model('App\Models\Checklist_items_model');
        $this->Pin_comments_model = model('App\Models\Pin_comments_model');
        $this->Project_settings_model = model('App\Models\Project_settings_model');
    }

    private function get_context_id_pairs() {
        return array(
            array("context" => "general", "id_key" => "", "id" => null),
            array("context" => "project", "id_key" => "project_id", "id" => null), //keep the 1st item as project since it'll be used maximum times
            array("context" => "client", "id_key" => "client_id", "id" => null),
            array("context" => "contract", "id_key" => "contract_id", "id" => null),
            array("context" => "estimate", "id_key" => "estimate_id", "id" => null),
            array("context" => "expense", "id_key" => "expense_id", "id" => null),
            array("context" => "invoice", "id_key" => "invoice_id", "id" => null),
            array("context" => "lead", "id_key" => "lead_id", "id" => null),
            array("context" => "order", "id_key" => "order_id", "id" => null),
            array("context" => "proposal", "id_key" => "proposal_id", "id" => null),
            array("context" => "subscription", "id_key" => "subscription_id", "id" => null),
            array("context" => "ticket", "id_key" => "ticket_id", "id" => null)
        );
    }

    private function get_context_and_id($model_info = null) {
        $context_id_pairs = $this->get_context_id_pairs();

        $request = request();
        $context = $request->getPost("context");

        foreach ($context_id_pairs as $pair) {
            $id_key = $pair["id_key"];

            if ($id_key) {
                $id = $model_info ? ($model_info->$id_key ? $model_info->$id_key : null) : null;

                if ($id !== null) {
                    $pair["id"] = $id;
                } else if ($request->getPost($id_key)) { //here the $request->getPost will be needed when loading controller from widget helper
                    $pair["id"] = $request->getPost($id_key);
                }

                if ($pair["id"] !== null) {
                    return $pair;
                }
            } else {
                // there is no id key for general context
                if ($context === "general") {
                    return $pair;
                }
            }
        }

        return array("context" => "project", "id" => null);
    }

    private function _client_can_create_tasks($context, $project_id) {
        //check settings for client's project permission. Client can cteate task only in own projects. 
        if ($context == "project" && get_setting("client_can_create_tasks")) {
            if ($project_id) {
                //check if it's client's project
                return $this->_is_clients_project($project_id);
            } else {
                //client has permission to create tasks on own projects
                return true;
            }
        }

        return false; //client can't create tasks in any other context except the project
    }

    private function _can_edit_clients($context_id) {

        $memory_index = $context_id ? $context_id : "any_clients";

        //this method will be used a lot in loop. To reduce db call, save the value in memory. 
        $can_edit = get_array_value($this->can_edit_client_memory, $memory_index);
        if (is_null($can_edit)) {
            $can_edit = $this->can_edit_clients($context_id);

            $this->can_edit_client_memory[$memory_index] = $can_edit;
        }

        return $can_edit;
    }

    private function _can_access_this_lead($context_id) {

        $memory_index = $context_id ? $context_id : "any_leads";

        //this method will be used a lot in loop. To reduce db call, save the value in memory. 
        $can_edit = get_array_value($this->can_access_lead_memory, $memory_index);
        if (is_null($can_edit)) {
            $can_edit = $this->can_access_this_lead($context_id);

            $this->can_access_lead_memory[$memory_index] = $can_edit;
        }

        return $can_edit;
    }

    private function _can_access_this_estimate($context_id) {

        $memory_index = $context_id ? $context_id : "any_estimates";

        //this method will be used a lot in loop. To reduce db call, save the value in memory. 
        $can_edit = get_array_value($this->can_access_estimate_memory, $memory_index);
        if (is_null($can_edit)) {
            $can_edit = $this->can_access_this_estimate($context_id);

            $this->can_access_estimate_memory[$memory_index] = $can_edit;
        }

        return $can_edit;
    }

    private function _can_edit_subscriptions($context_id) {

        $memory_index = $context_id ? $context_id : "any_subscriptions";

        //this method will be used a lot in loop. To reduce db call, save the value in memory. 
        $can_edit = get_array_value($this->can_edit_subscription_memory, $memory_index);
        if (is_null($can_edit)) {
            $can_edit = $this->can_edit_subscriptions($context_id);

            $this->can_edit_subscription_memory[$memory_index] = $can_edit;
        }

        return $can_edit;
    }

    private function _can_edit_tickets($context_id) {

        if ($this->login_user->user_type === "staff") {
            $memory_index = $context_id ? $context_id : "any_tickets";

            //this method will be used a lot in loop. To reduce db call, save the value in memory. 
            $can_edit = get_array_value($this->can_edit_ticket_memory, $memory_index);
            if (is_null($can_edit)) {
                $can_edit = $this->can_access_tickets($context_id);

                $this->can_edit_ticket_memory[$memory_index] = $can_edit;
            }

            return $can_edit;
        } else {
            return false;
        }
    }

    private function can_create_tasks($_context = null) {
        //check both with or without $context_id for all contexts

        $context_data = $this->get_context_and_id();
        $context = $_context ? $_context : $context_data["context"];
        $context_id = $context_data["id"];

        if ($this->login_user->user_type != "staff") {
            return $this->_client_can_create_tasks($context, $context_id);
        }

        if (!$_context && count($this->_get_accessible_contexts("create"))) {
            return true; //calling to show modal or button. Allow it if has access in any context. 
        }

        $permissions = $this->login_user->permissions;

        if ($context == "general") {
            return true;
        } else if ($context == "project" && $this->has_all_projects_restricted_role()) {
            return false;
        } else if ($context == "project" && $this->can_manage_all_projects()) {
            return true; // user has permission to create task in all projects 
        } else if ($context == "project" && $this->_user_has_project_task_creation_permission() && $context_id && $this->_is_user_a_project_member($context_id)) {
            return true; // in a project, user must be a project member with task creation permission to create tasks
        } else if ($context == "project" && $this->_user_has_project_task_creation_permission() && !$context_id) {
            return true; // don't have any project id yet. helpful when calling it from global task creation modal. 
        } else if ($context == "client" && $this->_can_edit_clients($context_id)) {
            return true;  //we're using client edit permission for creating clients or client tasks . this function will check both for a specific client or without any client
        } else if ($context == "lead" && $this->_can_access_this_lead($context_id)) {
            return true; //this function will check both for a specific lead or without any lead
        } else if ($context == "invoice" && $this->can_edit_invoices()) {
            return true;
        } else if ($context == "estimate" && $this->_can_access_this_estimate($context_id)) {
            return true;
        } else if ($context == "order" && ($this->login_user->is_admin || get_array_value($permissions, "order"))) {
            return true;
        } else if ($context == "contract" && ($this->login_user->is_admin || get_array_value($permissions, "contract"))) {
            return true;
        } else if ($context == "proposal" && ($this->login_user->is_admin || get_array_value($permissions, "proposal"))) {
            return true;
        } else if ($context == "subscription" && $this->_can_edit_subscriptions($context_id)) {
            return true;
        } else if ($context == "expense" && $this->can_access_expenses()) {
            return true;
        } else if ($context == "ticket" && $this->_can_edit_tickets($context_id)) {
            return true;
        }
    }

    private function _is_clients_project($project_id) {
        //this method will be used a lot in loop. To reduce db call, save the value in memory. 
        $is_client_project = get_array_value($this->project_client_memory, $project_id);
        if (is_null($is_client_project)) {
            $project_info = $this->Projects_model->get_one($project_id);

            $is_client_project = ($project_info->client_id == $this->login_user->client_id);
            $this->project_client_memory[$project_id] = $is_client_project;
        }

        return $is_client_project;
    }

    private function _is_user_a_project_member($project_id) {

        //this method will be used a lot in loop. To reduce db call, save the value in memory. 
        $is_member = get_array_value($this->project_member_memory, $project_id);
        if (is_null($is_member)) {
            $is_member = $this->Project_members_model->is_user_a_project_member($project_id, $this->login_user->id);
            $this->project_member_memory[$project_id] = $is_member;
        }

        return $is_member;
    }

    private function _can_edit_project_tasks($project_id) {
        //check if the user has permission to edit tasks of this project

        if ($this->login_user->user_type != "staff") {
            //check settings for client's project permission. Client can edit task only in own projects and check task edit permission also
            if ($project_id && get_setting("client_can_edit_tasks") && $this->_is_clients_project($project_id)) {
                return true;
            }

            return false;
        }

        if ($project_id && $this->can_manage_all_projects()) {
            return true; // user has permission to edit task in all projects 
        } else if ($project_id && $this->_user_has_project_task_edit_permission() && $this->_is_user_a_project_member($project_id)) {
            return true; // in a project, user must be a project member with task creation permission to create tasks
        }
    }

    private function _can_comment_on_tasks($task_info) {
        //check if the user has permission to comment on tasks

        $project_id = $task_info->project_id;

        if ($this->login_user->user_type != "staff") {
            //check settings for client's task comment permission. Client can comemnt on task only in own projects
            if ($project_id && get_setting("client_can_comment_on_tasks") && $this->_is_clients_project($project_id)) {
                return true;
            }

            return false;
        }

        if ($project_id && $this->can_manage_all_projects()) {
            return true; // user has permission to edit task in all projects 
        } else if ($project_id && $this->_user_has_project_task_comment_permission() && $this->_is_user_a_project_member($project_id)) {
            return true; // in a project, user must be a project member with task creation permission to create tasks
        } else if (!$project_id) {
            return $this->can_edit_tasks($task_info);
        }
    }

    private function _can_edit_non_context_tasks($task_info) {
        return $this->login_user->is_admin || !isset($task_info->id) || $task_info->created_by === $this->login_user->id || $task_info->assigned_to === $this->login_user->id || in_array($this->login_user->id, explode(',', $task_info->collaborators));
    }

    private function _can_delete_non_context_tasks($task_info) {
        return $this->login_user->is_admin || $task_info->created_by === $this->login_user->id;
    }

    private function can_edit_tasks($_task = null) {
        $task_info = is_object($_task) ? $_task : $this->Tasks_model->get_one($_task); //the $_task is either task id or task info
        $permissions = $this->login_user->permissions;

        if ($this->login_user->user_type === "client" && !$task_info->project_id) {
            return false; //client can't edit tasks in any other context except the project
        }

        //check permisssion for team members

        if ($task_info->context === "general" && $this->_can_edit_non_context_tasks($task_info)) {
            return true;
        } else if ($task_info->project_id && $this->_can_edit_project_tasks($task_info->project_id)) {
            return true;
        } else if ($task_info->client_id && $this->_can_edit_clients($task_info->client_id)) {
            //we're using client edit permission for editing clients or client tasks 
            //this function will check both for a specific client or without any client
            return true;
        } else if ($task_info->lead_id && $this->_can_access_this_lead($task_info->lead_id)) {
            return true; //this function will check both for a specific lead or without any lead
        } else if ($task_info->invoice_id && $this->can_edit_invoices()) {
            return true;
        } else if ($task_info->estimate_id && $this->_can_access_this_estimate($task_info->estimate_id)) {
            return true;
        } else if ($task_info->order_id && ($this->login_user->is_admin || get_array_value($permissions, "order"))) {
            return true;
        } else if ($task_info->contract_id && ($this->login_user->is_admin || get_array_value($permissions, "contract"))) {
            return true;
        } else if ($task_info->proposal_id && ($this->login_user->is_admin || get_array_value($permissions, "proposal"))) {
            return true;
        } else if ($task_info->subscription_id && $this->_can_edit_subscriptions($task_info->subscription_id)) {
            return true;
        } else if ($task_info->expense_id && $this->can_access_expenses()) {
            return true;
        } else if ($task_info->ticket_id && $this->_can_edit_tickets($task_info->ticket_id)) {
            return true;
        }
    }

    private function _can_edit_task_status($task_info) {
        if ($task_info->project_id && get_array_value($this->login_user->permissions, "can_update_only_assigned_tasks_status") == "1") {
            //task is specified and user has permission to edit only assigned tasks
            $collaborators_array = explode(',', $task_info->collaborators);
            if ($task_info->assigned_to == $this->login_user->id || in_array($this->login_user->id, $collaborators_array)) {
                return true;
            }
        } else {
            return $this->can_edit_tasks($task_info);
        }
    }

    private function can_view_tasks($context = "", $context_id = 0, $task_info = null) {
        if ($task_info) {
            $context_data = $this->get_context_and_id($task_info);
            $context = $context_data["context"];
            $context_id = $context_data["id"];
        }

        if ($this->login_user->user_type != "staff") {
            //check settings for client's project permission. Client can view task only in own projects. 
            if ($context == "project" && get_setting("client_can_view_tasks") && $this->_is_clients_project($context_id) && $this->can_client_access("project", false)) {
                return true;
            }

            return false; //client can't view tasks in any other context except the project
        }

        //check permisssion for team members
        $permissions = $this->login_user->permissions;

        if (($context === "general" || (isset($task_info->context) && $task_info->context === "general")) && $this->_can_edit_non_context_tasks($task_info)) {
            return true;
        } else if ($context == "project" && $this->has_all_projects_restricted_role()) {
            return false;
        } else if ($context == "project" && $this->can_manage_all_projects()) {
            return true; // user has permission to view task in all projects 
        } else if ($context == "project" && $context_id && !get_array_value($this->login_user->permissions, "show_assigned_tasks_only") && $this->_is_user_a_project_member($context_id)) {
            return true; // in a project, all team members who has access to project can view tasks who doesn't have any other restriction
        } else if ($context == "project" && $task_info && get_array_value($this->login_user->permissions, "show_assigned_tasks_only") == "1") {
            //task is specified and user has permission to view only assigned tasks
            $collaborators_array = explode(',', $task_info->collaborators);
            if ($task_info->assigned_to == $this->login_user->id || in_array($this->login_user->id, $collaborators_array)) {
                return true;
            }
        } else if ($context == "project" && !$context_id && !$task_info && get_array_value($this->login_user->permissions, "show_assigned_tasks_only") == "1") {
            //task is specified and user has permission to view only assigned tasks. 
            //in global tasks list view, we have to allow this but check the specific tasks in query
            return true;
        } else if ($context == "project" && !$task_info && get_array_value($this->login_user->permissions, "show_assigned_tasks_only") == "1") {
            //task is specified and user has permission to view only assigned tasks. 
            //in tasks list view, we have to allow this but check the specific tasks in query
            return $this->_is_user_a_project_member($context_id);
        } else if ($context == "project" && !$task_info && !$context_id && !get_array_value($this->login_user->permissions, "do_not_show_projects") == "1") {
            //user can see project tasks on golbal tasks list. 
            return true;
        } else if ($context == "client" && $this->can_view_clients($context_id)) {
            return true;
        } else if ($context == "lead" && $this->_can_access_this_lead($context_id)) {
            return true;
        } else if ($context == "invoice" && $this->can_view_invoices($context_id)) {
            return true;
        } else if ($context == "estimate" && $this->_can_access_this_estimate($context_id)) {
            return true;
        } else if ($context == "order" && ($this->login_user->is_admin || get_array_value($permissions, "order"))) {
            return true;
        } else if ($context == "contract" && ($this->login_user->is_admin || get_array_value($permissions, "contract"))) {
            return true;
        } else if ($context == "proposal" && ($this->login_user->is_admin || get_array_value($permissions, "proposal"))) {
            return true;
        } else if ($context == "subscription" && $this->can_view_subscriptions()) {
            return true;
        } else if ($context == "expense" && $this->can_access_expenses()) {
            return true;
        } else if ($context == "ticket" && $this->_can_edit_tickets($context_id)) {
            return true;
        }
    }

    private function _can_delete_project_tasks($project_id) {
        //check if the user has permission to edit tasks of this project

        if ($this->login_user->user_type != "staff") {
            //check settings for client's project permission. Client can edit task only in own projects and check task edit permission also
            if ($project_id && get_setting("client_can_delete_tasks") && $this->_is_clients_project($project_id)) {
                return true;
            }

            return false;
        }

        if ($project_id && $this->can_manage_all_projects()) {
            return true; // user has permission to edit task in all projects 
        } else if ($project_id && $this->_user_has_project_task_delete_permission() && $this->_is_user_a_project_member($project_id)) {
            return true; // in a project, user must be a project member with task creation permission to create tasks
        }
    }

    private function can_delete_tasks($_task = null) {
        $task_info = is_object($_task) ? $_task : $this->Tasks_model->get_one($_task); //the $_task is either task id or task info
        $permissions = $this->login_user->permissions;

        if ($this->login_user->user_type === "client" && !$task_info->project_id) {
            return false; //client can't edit tasks in any other context except the project
        }

        //check permisssion for team members

        if ($task_info->context === "general" && $this->_can_delete_non_context_tasks($task_info)) {
            return true;
        } else if ($task_info->project_id && $this->_can_delete_project_tasks($task_info->project_id)) {
            return true;
        } else if ($task_info->client_id && $this->_can_edit_clients($task_info->client_id)) {
            //we're using client edit permission for editing clients or client tasks 
            //this function will check both for a specific client or without any client
            return true;
        } else if ($task_info->lead_id && $this->_can_access_this_lead($task_info->lead_id)) {
            return true; //this function will check both for a specific lead or without any lead
        } else if ($task_info->invoice_id && $this->can_edit_invoices()) {
            return true;
        } else if ($task_info->estimate_id && $this->_can_access_this_estimate($task_info->estimate_id)) {
            return true;
        } else if ($task_info->order_id && ($this->login_user->is_admin || get_array_value($permissions, "order"))) {
            return true;
        } else if ($task_info->contract_id && ($this->login_user->is_admin || get_array_value($permissions, "contract"))) {
            return true;
        } else if ($task_info->proposal_id && ($this->login_user->is_admin || get_array_value($permissions, "proposal"))) {
            return true;
        } else if ($task_info->subscription_id && $this->_can_edit_subscriptions($task_info->subscription_id)) {
            return true;
        } else if ($task_info->expense_id && $this->can_access_expenses()) {
            return true;
        } else if ($task_info->ticket_id && $this->_can_edit_tickets($task_info->ticket_id)) {
            return true;
        }
    }

    private function _user_has_project_task_creation_permission() {
        return get_array_value($this->login_user->permissions, "can_create_tasks") == "1";
    }

    private function _user_has_project_task_edit_permission() {
        return get_array_value($this->login_user->permissions, "can_edit_tasks") == "1";
    }

    private function _user_has_project_task_delete_permission() {
        return get_array_value($this->login_user->permissions, "can_delete_tasks") == "1";
    }

    private function _user_has_project_task_comment_permission() {
        return get_array_value($this->login_user->permissions, "can_comment_on_tasks") == "1";
    }

    private function _is_active_module($module_name) {
        if (get_setting($module_name) == "1") {
            return true;
        }
    }

    private function _get_accessible_contexts($type = "create", $task_info = null) {

        $context_id_pairs = $this->get_context_id_pairs();

        $available_contexts = array();

        foreach ($context_id_pairs as $pair) {
            $context = $pair["context"];

            $always_enabled_module = array("general", "project", "client");
            if (!(in_array($context, $always_enabled_module) || $this->_is_active_module("module_" . $context))) {
                continue;
            }

            if ($type == "view") {
                if ($this->can_view_tasks($context)) {
                    $available_contexts[] = $context;
                }
            } else if ($type == "edit") {
                if ($this->can_edit_tasks($task_info)) {
                    $available_contexts[] = $context;
                }
            } else {
                if ($this->can_create_tasks($context)) {
                    $available_contexts[] = $context;
                }
            }
        }

        return $available_contexts;
    }

    //this will be applied to staff users only except project context
    private function _prepare_query_parameters_for_accessible_contexts($contexts) {
        $context_options = array();

        if ($this->login_user->is_admin) {
            return $context_options;
        }

        $permissions = $this->login_user->permissions;

        foreach ($contexts as $context) {
            $context_options[$context] = array();

            if ($context === "project") {

                $context_options[$context]["show_assigned_tasks_only_user_id"] = $this->show_assigned_tasks_only_user_id();
                // $context_options[$context]["project_status"] = 1; //open projects

                if (!$this->can_manage_all_projects()) {
                    $context_options[$context]["project_member_id"] = $this->login_user->id; //don't show all tasks to non-admin users
                }
            } else if ($context === "client") {

                $context_options[$context]["show_own_clients_only_user_id"] = $this->show_own_clients_only_user_id();

                if (get_array_value($permissions, "client") === "specific") {
                    $context_options[$context]["client_groups"] = get_array_value($permissions, "client_specific");
                }
            } else if ($context === "lead") {

                $context_options[$context]["show_own_leads_only_user_id"] = $this->show_own_leads_only_user_id();
            } else if ($context === "estimate") {

                $context_options[$context]["show_own_estimates_only_user_id"] = $this->show_own_estimates_only_user_id();
            } else if ($context === "ticket") {

                $context_options[$context]["show_assigned_tickets_only_user_id"] = $this->show_assigned_tickets_only_user_id();

                if (get_array_value($permissions, "ticket") === "specific") {
                    $context_options[$context]["ticket_types"] = get_array_value($permissions, "ticket_specific");
                }
            } else if ($context === "proposal") {

                $context_options[$context]["show_own_proposals_only_user_id"] = $this->show_own_proposals_only_user_id();
            }
        }

        return array("context_options" => $context_options);
    }

    function modal_form() {
        $id = $this->request->getPost('id');
        $add_type = $this->request->getPost('add_type');
        $last_id = $this->request->getPost('last_id');

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "last_id" => "numeric"
        ));


        $model_info = $this->Tasks_model->get_one($id);

        if ($add_type == "multiple" && $last_id) {
            //we've to show the lastly added information if it's the operation of adding multiple tasks
            $model_info = $this->Tasks_model->get_one($last_id);
        }

        $contexts = $this->_get_accessible_contexts();
        $selected_context = get_array_value($contexts, 0);
        $view_data["show_contexts_dropdown"] = count($contexts) > 1 ? true : false; //don't show context if there is only one context
        $selected_context_id = 0;

        foreach ($this->get_context_id_pairs() as $obj) {
            $context_id_key = get_array_value($obj, "id_key");
            if (!$context_id_key) {
                continue;
            }

            $value = $this->request->getPost($context_id_key) ? $this->request->getPost($context_id_key) : $model_info->{$context_id_key};
            $view_data[$context_id_key] = $value ? $value : ""; // prepare project_id, client_id, etc variables

            if ($value && !$selected_context_id) {
                $selected_context = get_array_value($obj, "context");
                $selected_context_id = $value;
                $view_data["show_contexts_dropdown"] = false; //don't show context dropdown if any context is selected. 
            }
        }

        if ($model_info->context) {
            $selected_context = $model_info->context; //has highest priority 
            $context_id_key = ($selected_context === "general" ? "" : ($selected_context . "_id"));
            $selected_context_id = $context_id_key ? $model_info->{$context_id_key} : "";
        }

        $dropdowns = $this->_get_task_related_dropdowns($selected_context, $selected_context_id, ($selected_context_id ? true : false));
        $view_data = array_merge($view_data, $dropdowns);

        if ($id) {
            if (!$this->can_edit_tasks($model_info)) {
                app_redirect("forbidden");
            }

            if ($model_info->context == "general") {
                $view_data["show_contexts_dropdown"] = true; //show context dropdown when editing general tasks
            } else {
                $contexts = array($model_info->context); //context can't be edited dureing edit. So, pass only the saved context
                $view_data["show_contexts_dropdown"] = false; //don't show context when editing 
            }
        } else {
            //Going to create new task. Check if the user has access in any context
            if (!$this->can_create_tasks()) {
                app_redirect("forbidden");
            }
        }

        $view_data['selected_context'] = $selected_context;
        $view_data['contexts'] = $contexts;
        $view_data['model_info'] = $model_info;
        $view_data["add_type"] = $add_type;
        $view_data['is_clone'] = $this->request->getPost('is_clone');
        $view_data['view_type'] = $this->request->getPost("view_type");

        $view_data['show_assign_to_dropdown'] = true;
        if ($this->login_user->user_type == "client") {
            if (!get_setting("client_can_assign_tasks")) {
                $view_data['show_assign_to_dropdown'] = false;
            }
        } else {
            //set default assigne to for new tasks
            if (!$id && !$view_data['model_info']->assigned_to) {
                $view_data['model_info']->assigned_to = $this->login_user->id;
            }
        }

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("tasks", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        $view_data['has_checklist'] = $this->Checklist_items_model->get_details(array("task_id" => $id))->resultID->num_rows;
        $view_data['has_sub_task'] = count($this->Tasks_model->get_all_where(array("parent_task_id" => $id, "deleted" => 0))->getResult());

        $view_data["project_deadline"] = $this->_get_project_deadline_for_task(get_array_value($view_data, "project_id"));
        $view_data["show_time_with_task"] = (get_setting("show_time_with_task_start_date_and_deadline")) ? true : false;
        $view_data['time_format_24_hours'] = get_setting("time_format") == "24_hours" ? true : false;

        return $this->template->view('tasks/modal_form', $view_data);
    }

    private function get_removed_task_status_ids($project_id = 0) {
        if (!$project_id) {
            return "";
        }

        $this->init_project_settings($project_id);
        return get_setting("remove_task_statuses");
    }

    private function _get_task_related_dropdowns($context = "", $context_id = 0, $return_empty_context = false) {

        //get milestone dropdown
        $milestones_dropdown = array(array("id" => "", "text" => "-"));
        if ($context == "project" && $context_id) {
            $milestones = $this->Milestones_model->get_details(array("project_id" => $context_id, "deleted" => 0))->getResult();
            foreach ($milestones as $milestone) {
                $milestones_dropdown[] = array("id" => $milestone->id, "text" => $milestone->title);
            }
        }

        $assign_to_dropdown = array(array("id" => "", "text" => "-"));
        $collaborators_dropdown = array();

        //get project members and collaborators dropdown
        if ($context == "project" && $context_id) {
            $show_client_contacts = $this->can_access_clients(true);
            if ($this->login_user->user_type === "client" && get_setting("client_can_assign_tasks")) {
                $show_client_contacts = true;
            }

            $user_ids = array();
            if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
                $user_ids[] = $this->login_user->id;
            }

            $project_members = $this->Project_members_model->get_project_members_id_and_text_dropdown($context_id, $user_ids, $show_client_contacts, true);
        } else if ($context == "project") {
            $project_members = array();
        } else {

            $options = array("status" => "active", "user_type" => "staff");
            if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
                $options["id"] = $this->login_user->id;
            }

            $project_members = $this->Users_model->get_id_and_text_dropdown(array("first_name", "last_name"), $options);
        }

        if ($project_members) {
            $assign_to_dropdown = array_merge($assign_to_dropdown, $project_members);
            $collaborators_dropdown = array_merge($collaborators_dropdown, $project_members);
        }

        //get labels suggestion
        $label_suggestions = $this->make_labels_dropdown("task");

        $task_status_options = array();
        if ($context == "project" && $context_id) {
            $task_status_options["exclude_status_ids"] = $this->get_removed_task_status_ids($context_id);
        } else {
            $task_status_options["hide_from_non_project_related_tasks"] = 0;
        }

        //statues dropdown
        $statuses_dropdown = array();
        $statuses = $this->Task_status_model->get_details($task_status_options)->getResult();
        foreach ($statuses as $status) {
            $statuses_dropdown[] = array("id" => $status->id, "text" => $status->key_name ? app_lang($status->key_name) : $status->title);
        }

        //task points dropdown 
        $task_points = array();
        $task_point_range = get_setting("task_point_range");
        $task_point_start = 1;
        if (str_starts_with($task_point_range, '0')) {
            $task_point_start = 0;
        }

        for ($i = $task_point_start; $i <= $task_point_range * 1; $i++) {
            if ($i <= 1) {
                $task_points[$i] = $i . " " . app_lang('point');
            } else {
                $task_points[$i] = $i . " " . app_lang('points');
            }
        }


        //properties dropdown 
        $priorities = $this->Task_priority_model->get_details()->getResult();
        $priorities_dropdown = array(array("id" => "", "text" => "-"));
        foreach ($priorities as $priority) {
            $priorities_dropdown[] = array("id" => $priority->id, "text" => $priority->title);
        }




        $projects_dropdown = array(array("id" => "", "text" => "-"));
        if ($context == "project" && !$return_empty_context) {
            //$project_options = array("status_id" => 1);
            $project_options = array();
            if ($this->login_user->user_type == "staff") {
                if (!$this->can_manage_all_projects()) {
                    $project_options["user_id"] = $this->login_user->id; //normal user's should be able to see only the projects where they are added as a team mmeber.
                }
            } else {
                $project_options["client_id"] = $this->login_user->client_id; //get client's projects
            }

            $projects = $this->Projects_model->get_details($project_options)->getResult();

            foreach ($projects as $project) {
                $projects_dropdown[] = array("id" => $project->id, "text" => $project->title);
            }
        }


        $clients_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "client" && !$return_empty_context) {
            //get clients dropdown
            $this->init_permission_checker("client");
            $options = array(
                "show_own_clients_only_user_id" => $this->show_own_clients_only_user_id(),
                "client_groups" => $this->allowed_client_groups
            );

            $clients = $this->Clients_model->get_details($options)->getResult();
            foreach ($clients as $client) {
                $clients_dropdown[] = array("id" => $client->id, "text" => $client->company_name);
            }
        }

        $leads_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "lead" && !$return_empty_context) {
            //get leads dropdown
            $this->init_permission_checker("lead");
            $options = array(
                "leads_only" => true,
                "owner_id" => $this->show_own_leads_only_user_id()
            );

            $leads = $this->Clients_model->get_details($options)->getResult();
            foreach ($leads as $lead) {
                $leads_dropdown[] = array("id" => $lead->id, "text" => $lead->company_name);
            }
        }

        $invoices_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "invoice" && !$return_empty_context) {
            //get invoices dropdown
            $invoices = $this->Invoices_model->get_all_where(array("deleted" => 0))->getResult();
            foreach ($invoices as $invoice) {
                $invoices_dropdown[] = array("id" => $invoice->id, "text" => $invoice->display_id);
            }
        }

        $estimates_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "estimate" && !$return_empty_context) {
            //get estimates dropdown
            $options = array(
                "show_own_estimates_only_user_id" => $this->show_own_estimates_only_user_id(),
            );

            $estimates = $this->Estimates_model->get_details($options)->getResult();
            foreach ($estimates as $estimate) {
                $estimates_dropdown[] = array("id" => $estimate->id, "text" => get_estimate_id($estimate->id));
            }
        }

        $orders_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "order" && !$return_empty_context) {
            //get orders dropdown
            $orders = $this->Orders_model->get_all_where(array("deleted" => 0))->getResult();
            foreach ($orders as $order) {
                $orders_dropdown[] = array("id" => $order->id, "text" => get_order_id($order->id));
            }
        }

        $contracts_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "contract" && !$return_empty_context) {
            //get contracts dropdown
            $contracts = $this->Contracts_model->get_all_where(array("deleted" => 0))->getResult();
            foreach ($contracts as $contract) {
                $contracts_dropdown[] = array("id" => $contract->id, "text" => $contract->title);
            }
        }

        $proposals_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "proposal" && !$return_empty_context) {
            //get proposals dropdown
            $options = array(
                "show_own_proposals_only_user_id" => $this->show_own_proposals_only_user_id(),
            );

            $proposals = $this->Proposals_model->get_details($options)->getResult();
            foreach ($proposals as $proposal) {
                $proposals_dropdown[] = array("id" => $proposal->id, "text" => get_proposal_id($proposal->id));
            }
        }

        $subscriptions_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "subscription" && !$return_empty_context) {
            //get subscriptions dropdown
            $subscriptions = $this->Subscriptions_model->get_all_where(array("deleted" => 0))->getResult();
            foreach ($subscriptions as $subscription) {
                $subscriptions_dropdown[] = array("id" => $subscription->id, "text" => $subscription->title);
            }
        }

        $expenses_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "expense" && !$return_empty_context) {
            //get expenses dropdown
            $expenses = $this->Expenses_model->get_all_where(array("deleted" => 0))->getResult();
            foreach ($expenses as $expense) {
                $expenses_dropdown[] = array("id" => $expense->id, "text" => ($expense->title ? $expense->title : format_to_date($expense->expense_date, false)));
            }
        }

        $tickets_dropdown = array(array("id" => "", "text" => "-"));
        if ($context === "ticket" && !$return_empty_context) {
            $this->init_permission_checker("ticket");

            $options = array(
                "ticket_types" => $this->allowed_ticket_types,
                "show_assigned_tickets_only_user_id" => $this->show_assigned_tickets_only_user_id()
            );

            //get tickets dropdown
            $tickets = $this->Tickets_model->get_details($options)->getResult();
            foreach ($tickets as $ticket) {
                $tickets_dropdown[] = array("id" => $ticket->id, "text" => $ticket->title);
            }
        }

        return array(
            "milestones_dropdown" => $milestones_dropdown,
            "assign_to_dropdown" => $assign_to_dropdown,
            "collaborators_dropdown" => $collaborators_dropdown,
            "label_suggestions" => $label_suggestions,
            "statuses_dropdown" => $statuses_dropdown,
            "points_dropdown" => $task_points,
            "priorities_dropdown" => $priorities_dropdown,
            "projects_dropdown" => $projects_dropdown,
            "clients_dropdown" => $clients_dropdown,
            "leads_dropdown" => $leads_dropdown,
            "invoices_dropdown" => $invoices_dropdown,
            "estimates_dropdown" => $estimates_dropdown,
            "orders_dropdown" => $orders_dropdown,
            "contracts_dropdown" => $contracts_dropdown,
            "proposals_dropdown" => $proposals_dropdown,
            "subscriptions_dropdown" => $subscriptions_dropdown,
            "expenses_dropdown" => $expenses_dropdown,
            "tickets_dropdown" => $tickets_dropdown
        );
    }

    private function _get_project_deadline_for_task($project_id = 0) {
        if (!$project_id) {
            return "";
        }

        $project_deadline_date = "";
        $project_deadline = $this->Projects_model->get_one($project_id)->deadline;
        if (get_setting("task_deadline_should_be_before_project_deadline") && is_date_exists($project_deadline)) {
            $project_deadline_date = format_to_date($project_deadline, false);
        }

        return $project_deadline_date;
    }

    private function _send_task_updated_notification($task_info, $activity_log_id) {
        if ($task_info->context === "project") {
            log_notification("project_task_updated", array("project_id" => $task_info->project_id, "task_id" => $task_info->id, "activity_log_id" => $activity_log_id));
        } else if ($task_info->context === "general") {
            log_notification("general_task_updated", array("task_id" =>  $task_info->id, "activity_log_id" => $activity_log_id));
        } else {
            $context_id_key = $task_info->context . "_id";
            $context_id_value = $task_info->{$task_info->context . "_id"};

            log_notification("general_task_updated", array("$context_id_key" => $context_id_value, "task_id" => $task_info->id, "activity_log_id" => $activity_log_id));
        }
    }

    private function _send_task_created_notification($task_info) {
        if ($task_info->context === "project") {
            log_notification("project_task_created", array("project_id" => $task_info->project_id, "task_id" => $task_info->id));
        } else if ($task_info->context === "general") {
            log_notification("general_task_created", array("task_id" => $task_info->id));
        } else {
            $context_id_key = $task_info->context . "_id";
            $context_id_value = $task_info->{$task_info->context . "_id"};

            log_notification("general_task_created", array("$context_id_key" => $context_id_value, "task_id" => $task_info->id));
        }
    }

    /* insert/upadate/clone a task */

    function save() {

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "ticket_id" => "numeric",
        ));

        $project_id = $this->request->getPost('project_id');
        $id = $this->request->getPost('id');
        $add_type = $this->request->getPost('add_type');
        $now = get_current_utc_time();

        $is_clone = $this->request->getPost('is_clone');
        $main_task_id = "";
        if ($is_clone && $id) {
            $main_task_id = $id; //store main task id to get items later
            $id = ""; //on cloning task, save as new
        }

        $client_id = $this->request->getPost('client_id');
        $lead_id = $this->request->getPost('lead_id');
        $invoice_id = $this->request->getPost('invoice_id');
        $estimate_id = $this->request->getPost('estimate_id');
        $order_id = $this->request->getPost('order_id');
        $contract_id = $this->request->getPost('contract_id');
        $proposal_id = $this->request->getPost('proposal_id');
        $subscription_id = $this->request->getPost('subscription_id');
        $expense_id = $this->request->getPost('expense_id');
        $ticket_id = $this->request->getPost('ticket_id');

        $context_data = $this->get_context_and_id();
        $context = $context_data["context"] ? $context_data["context"] : "project";

        if ($id) {
            $task_info = $this->Tasks_model->get_one($id);
            if (!$this->can_edit_tasks($task_info)) {
                app_redirect("forbidden");
            }
        } else {
            if (!$this->can_create_tasks($context)) {
                app_redirect("forbidden");
            }
        }

        $collaborators = $this->request->getPost('collaborators');
        validate_list_of_numbers($collaborators);

        $labels = $this->request->getPost('labels');
        validate_list_of_numbers($labels);

        $assigned_to = $this->request->getPost('assigned_to');
        $recurring = $this->request->getPost('recurring') ? 1 : 0;
        $repeat_every = $this->request->getPost('repeat_every');
        $repeat_type = $this->request->getPost('repeat_type');
        $no_of_cycles = $this->request->getPost('no_of_cycles');
        $status_id = $this->request->getPost('status_id');
        $priority_id = $this->request->getPost('priority_id');
        $milestone_id = $this->request->getPost('milestone_id');

        $start_date = $this->request->getPost('start_date');
        $deadline = $this->request->getPost('deadline');

        //convert to 24hrs time format
        $start_time = $this->request->getPost('start_time');
        $end_time = $this->request->getPost('end_time');
        if (get_setting("time_format") != "24_hours") {
            $start_time = convert_time_to_24hours_format($start_time);
            $end_time = convert_time_to_24hours_format($end_time);
        }

        if ($start_time && (strlen($start_time) == 4 || strlen($start_time) == 7)) {
            $start_time = "0" . $start_time; // ex. convert 9:00 to 09:00
        }

        if ($end_time && (strlen($end_time) == 4 || strlen($end_time) == 7)) {
            $end_time = "0" . $end_time; // ex. convert 9:00 to 09:00
        }

        if ($start_date) {
            //join date with time
            if ($start_time) {
                $start_date = $start_date . " " . $start_time;
            }
        }
        if ($deadline) {
            if ($end_time) {
                $deadline = $deadline . " " . $end_time;
            }
        }

        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "project_id" => $project_id ? $project_id : 0,
            "milestone_id" => $milestone_id ? $milestone_id : 0,
            "points" => $this->request->getPost('points'),
            "status_id" => $status_id,
            "client_id" => $client_id ? $client_id : 0,
            "lead_id" => $lead_id ? $lead_id : 0,
            "invoice_id" => $invoice_id ? $invoice_id : 0,
            "estimate_id" => $estimate_id ? $estimate_id : 0,
            "order_id" => $order_id ? $order_id : 0,
            "contract_id" => $contract_id ? $contract_id : 0,
            "proposal_id" => $proposal_id ? $proposal_id : 0,
            "expense_id" => $expense_id ? $expense_id : 0,
            "subscription_id" => $subscription_id ? $subscription_id : 0,
            "priority_id" => $priority_id ? $priority_id : 0,
            "labels" => $labels,
            "start_date" => $start_date,
            "deadline" => $deadline,
            "recurring" => $recurring,
            "repeat_every" => $repeat_every ? $repeat_every : 0,
            "repeat_type" => $repeat_type ? $repeat_type : NULL,
            "no_of_cycles" => $no_of_cycles ? $no_of_cycles : 0,
        );

        if (!$id) {
            $data["created_date"] = $now;
            $data["context"] = $context;
            $data["sort"] = $this->Tasks_model->get_next_sort_value($project_id, $status_id);
            $data["created_by"] = $this->login_user->id;
        }

        //save context when editing general tasks
        if ($id && $task_info->context == "general" && $context) {
            $data["context"] = $context;
        }

        if ($ticket_id) {
            $data["ticket_id"] = $ticket_id;
        }

        //clint can't save the assign to and collaborators
        if ($this->login_user->user_type == "client") {
            if (get_setting("client_can_assign_tasks")) {
                $data["assigned_to"] = $assigned_to;
            } else if (!$id) { //it's new data to save
                $data["assigned_to"] = 0;
            }

            $data["collaborators"] = "";
        } else {
            $data["assigned_to"] = $assigned_to;
            $data["collaborators"] = $collaborators;
        }

        $data = clean_data($data);

        //set null value after cleaning the data
        if (!$data["start_date"]) {
            $data["start_date"] = NULL;
        }

        if (!$data["deadline"]) {
            $data["deadline"] = NULL;
        }

        //deadline must be greater or equal to start date
        if ($data["start_date"] && $data["deadline"] && $data["deadline"] < $data["start_date"]) {
            echo json_encode(array("success" => false, 'message' => app_lang('deadline_must_be_equal_or_greater_than_start_date')));
            return false;
        }

        $copy_checklist = $this->request->getPost("copy_checklist");

        $next_recurring_date = "";

        if ($recurring && get_setting("enable_recurring_option_for_tasks")) {
            //set next recurring date for recurring tasks

            if ($id) {
                //update
                if ($this->request->getPost('next_recurring_date')) { //submitted any recurring date? set it.
                    $next_recurring_date = $this->request->getPost('next_recurring_date');
                } else {
                    //re-calculate the next recurring date, if any recurring fields has changed.
                    if ($task_info->recurring != $data['recurring'] || $task_info->repeat_every != $data['repeat_every'] || $task_info->repeat_type != $data['repeat_type'] || $task_info->start_date != $data['start_date']) {
                        $recurring_start_date = $start_date ? $start_date : $task_info->created_date;
                        $next_recurring_date = add_period_to_date($recurring_start_date, $repeat_every, $repeat_type);
                    }
                }
            } else {
                //insert new
                $recurring_start_date = $start_date ? $start_date : get_array_value($data, "created_date");
                $next_recurring_date = add_period_to_date($recurring_start_date, $repeat_every, $repeat_type);
            }


            //recurring date must have to set a future date
            if ($next_recurring_date && get_today_date() >= $next_recurring_date) {
                echo json_encode(array("success" => false, 'message' => app_lang('past_recurring_date_error_message_title_for_tasks'), 'next_recurring_date_error' => app_lang('past_recurring_date_error_message'), "next_recurring_date_value" => $next_recurring_date));
                return false;
            }
        }

        //save status changing time for edit mode
        if ($id) {
            if ($task_info->status_id !== $status_id) {
                $data["status_changed_at"] = $now;
            }

            $this->check_sub_tasks_statuses($status_id, $id);
        }

        $create_as_a_non_subtask = $this->request->getPost("create_as_a_non_subtask");
        if ($is_clone && $main_task_id && !$create_as_a_non_subtask) {
            $data["parent_task_id"] = $this->request->getPost("parent_task_id") ? $this->request->getPost("parent_task_id") : 0;
        }

        $save_id = $this->Tasks_model->ci_save($data, $id);
        if ($save_id) {

            if ($is_clone && $main_task_id) {
                //clone task checklist
                if ($copy_checklist) {
                    $checklist_items = $this->Checklist_items_model->get_all_where(array("task_id" => $main_task_id, "deleted" => 0))->getResult();
                    foreach ($checklist_items as $checklist_item) {
                        //prepare new checklist data
                        $checklist_item_data = (array) $checklist_item;
                        unset($checklist_item_data["id"]);
                        $checklist_item_data['task_id'] = $save_id;

                        $checklist_item = $this->Checklist_items_model->ci_save($checklist_item_data);
                    }
                }

                //clone sub tasks
                if ($this->request->getPost("copy_sub_tasks")) {
                    $sub_tasks = $this->Tasks_model->get_all_where(array("parent_task_id" => $main_task_id, "deleted" => 0))->getResult();
                    foreach ($sub_tasks as $sub_task) {
                        //prepare new sub task data
                        $sub_task_data = (array) $sub_task;

                        unset($sub_task_data["id"]);
                        unset($sub_task_data["blocked_by"]);
                        unset($sub_task_data["blocking"]);

                        $sub_task_data['status_id'] = 1;
                        $sub_task_data['parent_task_id'] = $save_id;
                        $sub_task_data['created_date'] = $now;
                        $sub_task_data['created_by'] = $this->login_user->id;

                        $sub_task_data["sort"] = $this->Tasks_model->get_next_sort_value($sub_task_data["project_id"], $sub_task_data['status_id']);

                        $sub_task_save_id = $this->Tasks_model->ci_save($sub_task_data);

                        //clone sub task checklist
                        if ($copy_checklist) {
                            $checklist_items = $this->Checklist_items_model->get_all_where(array("task_id" => $sub_task->id, "deleted" => 0))->getResult();
                            foreach ($checklist_items as $checklist_item) {
                                //prepare new checklist data
                                $checklist_item_data = (array) $checklist_item;
                                unset($checklist_item_data["id"]);
                                $checklist_item_data['task_id'] = $sub_task_save_id;

                                $this->Checklist_items_model->ci_save($checklist_item_data);
                            }
                        }
                    }
                }
            }

            //save next recurring date 
            if ($next_recurring_date) {
                $recurring_task_data = array(
                    "next_recurring_date" => $next_recurring_date
                );
                $this->Tasks_model->save_reminder_date($recurring_task_data, $save_id);
            }

            // if created from project's ticket then save the task id with the ticket
            if ($ticket_id && $project_id) {
                $data = array("task_id" => $save_id);
                $this->Tickets_model->ci_save($data, $ticket_id);
            }

            $activity_log_id = get_array_value($data, "activity_log_id");

            $new_activity_log_id = save_custom_fields("tasks", $save_id, $this->login_user->is_admin, $this->login_user->user_type, $activity_log_id);

            if ($id) {
                //updated
                $this->_send_task_updated_notification($task_info, ($new_activity_log_id ? $new_activity_log_id : $activity_log_id));
            } else {
                $task_info = $this->Tasks_model->get_one($save_id);
                $this->_send_task_created_notification($task_info);

                //save uploaded files as comment
                $target_path = get_setting("timeline_file_path");
                $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "project_comment");

                if ($files_data && $files_data != "a:0:{}") {
                    $comment_data = array(
                        "created_by" => $this->login_user->id,
                        "created_at" => $now,
                        "project_id" => $project_id,
                        "task_id" => $save_id
                    );

                    $comment_data = clean_data($comment_data);

                    $comment_data["files"] = $files_data; //don't clean serilized data

                    $this->Project_comments_model->save_comment($comment_data);
                }
            }

            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved'), "add_type" => $add_type));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /*
     * list of tasks, prepared for datatable
     * @param string $context. client/lead/invoice etc.
     * @param int $id. client_id/lead_id etc.
     */

    function list_data($context = "", $context_id = 0, $is_mobile = 0) {
        validate_numeric_value($context_id);
        validate_numeric_value($is_mobile);

        if (!$this->can_view_tasks($context, $context_id)) {
            app_redirect("forbidden");
        }
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("tasks", $this->login_user->is_admin, $this->login_user->user_type);

        $milestone_id = $this->request->getPost('milestone_id');

        $quick_filter = $this->request->getPost('quick_filter');
        if ($quick_filter) {
            $status = "";
        } else {
            $status = $this->request->getPost('status_id') ? implode(",", $this->request->getPost('status_id')) : "";
        }

        $show_time_with_task = (get_setting("show_time_with_task_start_date_and_deadline")) ? true : false;

        $id = get_only_numeric_value($this->request->getPost('id'));

        $options = array(
            "id" => $id,
            "assigned_to" => $this->request->getPost('assigned_to'),
            "deadline" => $this->request->getPost('deadline'),
            "status_ids" => $status,
            "milestone_id" => $milestone_id,
            "priority_id" => $this->request->getPost('priority_id'),
            "custom_fields" => $custom_fields,
            "unread_status_user_id" => $this->login_user->id,
            "quick_filter" => $quick_filter,
            "label_id" => $this->request->getPost('label_id'),
            "custom_field_filter" => $this->prepare_custom_field_filter_values("tasks", $this->login_user->is_admin, $this->login_user->user_type)
        );

        //add the context data like $options["client_id"] = 2;
        $context_id_pairs = $this->get_context_id_pairs();
        $pair_key = array_keys(array_column($context_id_pairs, 'context'), $context);
        $pair_key = get_array_value($pair_key, 0);
        $pair = get_array_value($context_id_pairs, $pair_key);
        $options[get_array_value($pair, "id_key")] = $context_id;

        if ($context === "project") {
            $options["show_assigned_tasks_only_user_id"] = $this->show_assigned_tasks_only_user_id();
        }

        $all_options = append_server_side_filtering_commmon_params($options);

        $result = $this->Tasks_model->get_details($all_options);

        //by this, we can handel the server side or client side from the app table prams.
        if (get_array_value($all_options, "server_side")) {
            $list_data = get_array_value($result, "data");
        } else {
            $list_data = $result->getResult();
            $result = array();
        }

        $tasks_edit_permissions = $this->_get_tasks_edit_permissions($list_data);
        $tasks_status_edit_permissions = $this->_get_tasks_status_edit_permissions($list_data, $tasks_edit_permissions);

        $result_data = array();
        foreach ($list_data as $data) {
            $result_data[] = $this->_make_row($data, $custom_fields, $show_time_with_task, $tasks_edit_permissions, $tasks_status_edit_permissions, $is_mobile);
        }

        $result["data"] = $result_data;

        echo json_encode($result);
    }

    /* return a row of task list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("tasks", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Tasks_model->get_details($options)->getRow();

        $show_time_with_task = (get_setting("show_time_with_task_start_date_and_deadline")) ? true : false;

        $tasks_edit_permissions = $this->_get_tasks_edit_permissions(array($data));
        $tasks_status_edit_permissions = $this->_get_tasks_status_edit_permissions(array($data), $tasks_edit_permissions);

        $is_mobile = 0;
        if ($this->request->getPost('mobile_mirror')) {
            $is_mobile = 1;
        }

        return $this->_make_row($data, $custom_fields, $show_time_with_task, $tasks_edit_permissions, $tasks_status_edit_permissions, $is_mobile);
    }

    /* prepare a row of task list table */

    private function _make_row($data, $custom_fields, $show_time_with_task, $tasks_edit_permissions, $tasks_status_edit_permissions, $is_mobile = 0) {
        $task_title_class = "js-selection-id ";
        $icon = "";
        if (isset($data->unread) && $data->unread && $data->unread != "0") {
            $task_title_class .= " unread-comments-of-tasks";
            $icon = "<i data-feather='message-circle' class='icon-16 ml5 unread-comments-of-tasks-icon'></i>";
        }

        $title = "";
        $main_task_id = "#" . $data->id;
        $sub_task_search_column = "#" . $data->id;

        $sub_task = "";
        if ($data->parent_task_id) {
            $sub_task_search_column = "#" . $data->parent_task_id;
            //this is a sub task
            $sub_task = "<span class='sub-task-icon mr5' title='" . app_lang("sub_task") . "'><i data-feather='git-merge' class='icon-14'></i></span>";
            $title = $sub_task;
        }

        $toggle_sub_task_icon = "";

        if ($data->has_sub_tasks) {
            $toggle_sub_task_icon = "<span class='filter-sub-task-button clickable ml5' title='" . app_lang("show_sub_tasks") . "' main-task-id= '$main_task_id'><i data-feather='filter' class='icon-16'></i></span>";
        }

        $title .= modal_anchor(get_uri("tasks/view"), $data->title . $icon, array("title" => app_lang('task_info') . " #$data->id", "data-post-id" => $data->id, "data-search" => $sub_task_search_column, "class" => $task_title_class, "data-id" => $data->id, "data-modal-lg" => "1"));

        $task_point = "";
        if ($data->points > 1) {
            $task_point .= "<span class='badge badge-light clickable mt0' title='" . app_lang('points') . "'>" . $data->points . "</span> ";
        }
        $title .= "<span class='float-end ml5'>" . $task_point . "</span>";

        $task_priority = "";
        if ($data->priority_id) {
            $task_priority = "<span class='float-end circle-badge' title='" . app_lang('priority') . ": " . $data->priority_title . "'>
                            <span class='sub-task-icon priority-badge' style='background: $data->priority_color'><i data-feather='$data->priority_icon' class='icon-14'></i></span> $toggle_sub_task_icon
                      </span>";

            $title .= $task_priority;
        } else {
            $title .= "<span class='float-end'>" . $toggle_sub_task_icon . "</span>";
        }

        $task_labels = make_labels_view_data($data->labels_list, $is_mobile ? false : true);

        $title .= "<span class='float-end mr5'>" . $task_labels . "</span>";

        $context_title = "";
        if ($data->project_id) {
            $context_title = anchor(get_uri("projects/view/" . $data->project_id), $data->project_title ? $data->project_title : "");
        } else if ($data->client_id) {
            $context_title = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name ? $data->company_name : "");
        } else if ($data->lead_id) {
            $context_title = anchor(get_uri("leads/view/" . $data->lead_id), $data->company_name ? $data->company_name : "");
        } else if ($data->invoice_id) {
            $context_title = anchor(get_uri("invoices/view/" . $data->invoice_id), $data->invoice_display_id);
        } else if ($data->estimate_id) {
            $context_title = anchor(get_uri("estimates/view/" . $data->estimate_id), get_estimate_id($data->estimate_id));
        } else if ($data->order_id) {
            $context_title = anchor(get_uri("orders/view/" . $data->order_id), get_order_id($data->order_id));
        } else if ($data->contract_id) {
            $context_title = anchor(get_uri("contracts/view/" . $data->contract_id), $data->contract_title ? $data->contract_title : "");
        } else if ($data->proposal_id) {
            $context_title = anchor(get_uri("proposals/view/" . $data->proposal_id), get_proposal_id($data->proposal_id));
        } else if ($data->subscription_id) {
            $context_title = anchor(get_uri("subscriptions/view/" . $data->subscription_id), $data->subscription_title ? $data->subscription_title : "");
        } else if ($data->expense_id) {
            $context_title = modal_anchor(get_uri("expenses/expense_details"), ($data->expense_title ? $data->expense_title : format_to_date($data->expense_date, false)), array("title" => app_lang("expense_details"), "data-post-id" => $data->expense_id, "data-modal-lg" => "1"));
        } else if ($data->ticket_id) {
            $context_title = anchor(get_uri("tickets/view/" . $data->ticket_id), $data->ticket_title ? $data->ticket_title : "");
        }

        $milestone_title = "-";
        if ($data->milestone_title) {
            $milestone_title = $data->milestone_title;
        }

        $assigned_to = "-";

        if ($data->assigned_to) {
            $image_url = get_avatar($data->assigned_to_avatar);
            $assigned_to_user = "<span class='avatar avatar-xs mr10'><img src='$image_url' alt='...'></span> $data->assigned_to_user";
            $assigned_to = get_team_member_profile_link($data->assigned_to, $assigned_to_user);

            if ($data->user_type != "staff") {
                $assigned_to = get_client_contact_profile_link($data->assigned_to, $assigned_to_user);
            }

            $assigned_to_avatar = "<span class='avatar avatar-xs'><img src='$image_url' alt='...'></span>";
        } else {
            $assigned_to_avatar = "<span class='avatar avatar-xs'><img src='" . get_avatar() . "' alt='...'></span>";
        }


        $collaborators = $this->_get_collaborators($data->collaborator_list);

        if (!$collaborators) {
            $collaborators = "-";
        }


        $checkbox_class = "checkbox-blank";
        if ($data->status_key_name === "done") {
            $checkbox_class = "checkbox-checked";
        }

        if (get_array_value($tasks_status_edit_permissions, $data->id)) {
            //show changeable status checkbox and link to team members
            $check_status = js_anchor("<span class='$checkbox_class mr15 float-start'></span>", array('title' => "", "data-id" => $data->id, "data-value" => $data->status_key_name === "done" ? "1" : "3", "data-act" => "update-task-status-checkbox")) . $data->id;
            $status = js_anchor($data->status_key_name ? app_lang($data->status_key_name) : $data->status_title, array('title' => "", "class" => "", "data-id" => $data->id, "data-value" => $data->status_id, "data-act" => "update-task-status", "data-modifier-group" => "task_info"));
        } else {
            //don't show clickable checkboxes/status to client
            if ($checkbox_class == "checkbox-blank") {
                $checkbox_class = "checkbox-un-checked";
            }
            $check_status = "<span class='$checkbox_class mr15 float-start'></span> " . $data->id;
            $status = $data->status_key_name ? app_lang($data->status_key_name) : $data->status_title;
        }

        $id = $data->id;
        if (get_setting("show_the_status_checkbox_in_tasks_list")) {
            $id = $check_status;
        }

        $deadline_text = "-";
        if ($data->deadline && is_date_exists($data->deadline)) {

            if ($show_time_with_task) {
                if (date("H:i:s", strtotime($data->deadline)) == "00:00:00") {
                    $deadline_text = format_to_date($data->deadline, false);
                } else {
                    $deadline_text = format_to_relative_time($data->deadline, false, false, true);
                }
            } else {
                $deadline_text = format_to_date($data->deadline, false);
            }

            if (get_my_local_time("Y-m-d") > $data->deadline && $data->status_id != "3") {
                $deadline_text = "<span class='text-danger'>" . $deadline_text . "</span> ";
            } else if (format_to_date(get_my_local_time(), false) == format_to_date($data->deadline, false) && $data->status_id != "3") {
                $deadline_text = "<span class='text-warning'>" . $deadline_text . "</span> ";
            }
        }


        $start_date = "-";
        if (is_date_exists($data->start_date)) {
            if ($show_time_with_task) {
                if (date("H:i:s", strtotime($data->start_date)) == "00:00:00") {
                    $start_date = format_to_date($data->start_date, false);
                } else {
                    $start_date = format_to_relative_time($data->start_date, false, false, true);
                }
            } else {
                $start_date = format_to_date($data->start_date, false);
            }
        }

        if ($is_mobile) {
            $title = "<div class='box-wrapper'>
            <div class='box-avatar hover'>$assigned_to_avatar</div>" .
                modal_anchor(
                    get_uri("tasks/view"),
                    "<div class='dark text-wrap'>" . $sub_task . " <span class='mini-view-task-id'>" . $data->id . " - </span>" . $data->title . "</div>
                        <div class='d-flex'>" . $task_point . $task_priority . $task_labels . "</div>",
                    array(
                        "class" => "box-label",
                        "data-post-id" => $data->id,
                        "data-modal-lg" => "1"
                    )
                ) .
                "</div>";
        }

        $options = "";

        if (get_array_value($tasks_edit_permissions, $data->id)) {
            $options .= modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_task'), "data-post-id" => $data->id));
        }
        if ($this->can_delete_tasks($data)) {
            $options .= js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_task'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("tasks/delete"), "data-action" => "delete-confirmation"));
        }

        $row_data = array(
            $data->status_color,
            $id,
            $title,
            $data->title,
            $task_labels,
            $data->priority_title,
            $data->points,
            $data->start_date,
            $start_date,
            $data->deadline,
            $deadline_text,
            $milestone_title,
            $context_title,
            $assigned_to,
            $collaborators,
            $status
        );

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
        }

        $row_data[] = $options;

        return $row_data;
    }

    /* delete or undo a task */

    function delete() {

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $info = $this->Tasks_model->get_one($id);

        if (!$this->can_delete_tasks($info)) {
            app_redirect("forbidden");
        }

        if ($this->Tasks_model->delete_task_and_sub_items($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));

            $task_info = $this->Tasks_model->get_one($id);

            if ($task_info->context === "project") {
                log_notification("project_task_deleted", array("project_id" => $task_info->project_id, "task_id" => $id));
            } else if ($task_info->context === "general") {
                log_notification("general_task_deleted", array("task_id" =>  $task_info->id));
            } else {
                $context_id_key = $task_info->context . "_id";
                $context_id_value = $task_info->{$task_info->context . "_id"};

                log_notification("general_task_deleted", array("$context_id_key" => $context_id_value, "task_id" => $id));
            }

            try {
                app_hooks()->do_action("app_hook_data_delete", array(
                    "id" => $id,
                    "table" => get_db_prefix() . "tasks",
                    "table_without_prefix" => "tasks",
                ));
            } catch (\Exception $ex) {
                log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
            }
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    private function _get_collaborators($collaborator_list, $clickable = true) {
        $collaborators = "";
        if ($collaborator_list) {

            $collaborators_array = explode(",", $collaborator_list);
            foreach ($collaborators_array as $collaborator) {
                $collaborator_parts = explode("--::--", $collaborator);

                $collaborator_id = get_array_value($collaborator_parts, 0);
                $collaborator_name = get_array_value($collaborator_parts, 1);

                $image_url = get_avatar(get_array_value($collaborator_parts, 2));
                $user_type = get_array_value($collaborator_parts, 3);

                $_comma = "";
                if ($collaborators) {
                    $_comma = ", ";
                }
                $collaboratr_image = "<span class='avatar avatar-xs mr10'><img src='$image_url' alt='...'></span><span class='hide'>$_comma $collaborator_name</span>";

                if ($clickable) {
                    if ($user_type == "staff") {
                        $collaborators .= get_team_member_profile_link($collaborator_id, $collaboratr_image, array("title" => $collaborator_name));
                    } else if ($user_type == "client") {
                        $collaborators .= get_client_contact_profile_link($collaborator_id, $collaboratr_image, array("title" => $collaborator_name));
                    }
                } else {
                    $collaborators .= "<span title='$collaborator_name'>$collaboratr_image</span>";
                }
            }
        }
        return $collaborators;
    }

    //parent task can't be marked as done if there is any sub task which is not done yet
    private function check_sub_tasks_statuses($status_id = 0, $parent_task_id = 0) {
        if ($status_id !== "3") {
            //parent task isn't marking as done
            return true;
        }

        $sub_tasks = $this->Tasks_model->get_details(array("parent_task_id" => $parent_task_id, "deleted" => 0))->getResult();

        foreach ($sub_tasks as $sub_task) {
            if ($sub_task->status_id !== "3") {
                //this sub task isn't done yet, show error and exit
                echo json_encode(array("success" => false, 'message' => app_lang("parent_task_completing_error_message")));
                exit();
            }
        }
    }

    private function _make_checklist_item_row($data, $return_type = "row") {
        $checkbox_class = "checkbox-blank";
        $title_class = "";
        $is_checked_value = 1;
        $title_value = link_it($data->title);

        $move_icon_class = "";
        if ($data->is_checked == 1) {
            $is_checked_value = 0;
            $checkbox_class = "checkbox-checked";
            $title_class = "text-line-through text-off";
            $title_value = $data->title;
            $move_icon_class = "move-icon-checked";
        }

        $move_icon = "<div class='float-start checklist-sort-icon move-icon hide $move_icon_class'><i data-feather='menu' class='icon-16'></i></div>";

        $status = js_anchor("<span class='$checkbox_class mr15 mt-1 float-start'></span>", array('title' => "", "data-id" => $data->id, "data-value" => $is_checked_value, "data-act" => "update-checklist-item-status-checkbox", "class" => "update-checklist-item-status-checkbox"));
        if (!$this->can_edit_tasks($data->task_id)) {
            $status = "";
        }

        $title = "<span class='font-13 $title_class'>" . $title_value . "</span>";

        $delete = ajax_anchor(get_uri("tasks/delete_checklist_item/$data->id"), "<div class='float-end'><i data-feather='x' class='icon-16'></i></div>", array("class" => "delete-checklist-item ms-auto", "title" => app_lang("delete_checklist_item"), "data-fade-out-on-success" => "#checklist-item-row-$data->id"));
        if (!$this->can_edit_tasks($data->task_id)) {
            $delete = "";
        }

        if ($return_type == "data") {
            return $move_icon . $status . $title . $delete;
        }

        return "<div id='checklist-item-row-$data->id' class='list-group-item mb5 checklist-item-row b-a rounded text-break d-flex' data-id='$data->id' data-sort-value='$data->sort'>" . $move_icon . $status . $title . $delete . "</div>";
    }

    private function _make_sub_task_row($data, $return_type = "row") {

        $checkbox_class = "checkbox-blank";
        $title_class = "";

        if ($data->status_key_name == "done") {
            $checkbox_class = "checkbox-checked";
            $title_class = "text-line-through text-off";
        }

        $status = "";
        if ($this->can_edit_tasks($data)) {
            $status = js_anchor("<span class='$checkbox_class mr15 float-start'></span>", array('title' => "", "data-id" => $data->id, "data-value" => $data->status_key_name === "done" ? "1" : "3", "data-act" => "update-sub-task-status-checkbox"));
        }

        $title = anchor(get_uri("tasks/view/$data->id"), $data->title, array("class" => "font-13", "target" => "_blank"));

        $status_label = "<span class='float-end'><span class='badge mt0' style='background: $data->status_color;'>" . ($data->status_key_name ? app_lang($data->status_key_name) : $data->status_title) . "</span></span>";

        if ($return_type == "data") {
            return $status . $title . $status_label;
        }

        return "<div class='list-group-item mb5 b-a rounded sub-task-row' data-id='$data->id'>" . $status . $title . $status_label . "</div>";
    }

    function view($task_id = 0) {
        validate_numeric_value($task_id);
        $view_type = "";

        if ($task_id) { //details page
            $view_type = "details";
        } else { //modal view
            $task_id = $this->request->getPost('id');
        }

        $model_info = $this->Tasks_model->get_details(array("id" => $task_id))->getRow();
        if (!$model_info || !$model_info->id) {
            show_404();
        }

        $this->init_project_settings($model_info->project_id);

        if (!$this->can_view_tasks("", 0, $model_info)) {
            app_redirect("forbidden");
        }

        if ($model_info->context == "project" && $this->has_all_projects_restricted_role()) {
            app_redirect("forbidden");
        }

        $context_id_key = ($model_info->context === "general") ? "" : ($model_info->context . "_id");
        $context_id_value = $context_id_key ? $model_info->$context_id_key : "";

        $view_data = $this->_get_task_related_dropdowns($model_info->context, $context_id_value, true);

        $view_data['show_assign_to_dropdown'] = true;
        if ($this->login_user->user_type == "client" && !get_setting("client_can_assign_tasks")) {
            $view_data['show_assign_to_dropdown'] = false;
        }

        $view_data['can_edit_tasks'] = $this->can_edit_tasks($model_info);
        $view_data['can_edit_task_status'] = $this->_can_edit_task_status($model_info);

        $view_data['can_comment_on_tasks'] = $this->_can_comment_on_tasks($model_info);

        $view_data['model_info'] = $model_info;
        $view_data['collaborators'] = $this->_get_collaborators($model_info->collaborator_list, false);

        $view_data['labels'] = make_labels_view_data($model_info->labels_list);

        $options = array("task_id" => $task_id, "login_user_id" => $this->login_user->id);
        $view_data['comments'] = $this->Project_comments_model->get_details($options)->getResult();
        $view_data['task_id'] = $task_id;

        $view_data['custom_fields_list'] = $this->Custom_fields_model->get_combined_details("tasks", $task_id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        $view_data['pinned_comments'] = $this->Pin_comments_model->get_details(array("task_id" => $task_id, "pinned_by" => $this->login_user->id))->getResult();

        //get checklist items
        $checklist_items_array = array();
        $checklist_items = $this->Checklist_items_model->get_details(array("task_id" => $task_id))->getResult();
        foreach ($checklist_items as $checklist_item) {
            $checklist_items_array[] = $this->_make_checklist_item_row($checklist_item);
        }
        $view_data["checklist_items"] = json_encode($checklist_items_array);

        //get sub tasks
        $sub_tasks_array = array();
        $sub_tasks = $this->Tasks_model->get_details(array("parent_task_id" => $task_id))->getResult();
        foreach ($sub_tasks as $sub_task) {
            $sub_tasks_array[] = $this->_make_sub_task_row($sub_task);
        }
        $view_data["sub_tasks"] = json_encode($sub_tasks_array);
        $view_data["total_sub_tasks"] = $this->Tasks_model->count_sub_task_status(array("parent_task_id" => $task_id));
        $view_data["completed_sub_tasks"] = $this->Tasks_model->count_sub_task_status(array("parent_task_id" => $task_id, "status_id" => 3));

        $view_data["show_timer"] = get_setting("module_project_timesheet") ? true : false;

        if ($this->login_user->user_type === "client") {
            $view_data["show_timer"] = false;
        }

        //disable the start timer button if user has any timer in this project or if it's an another project and the setting is disabled
        $view_data["disable_timer"] = false;
        $user_has_any_timer = $this->Timesheets_model->user_has_any_timer($this->login_user->id);
        if ($user_has_any_timer && !get_setting("users_can_start_multiple_timers_at_a_time")) {
            $view_data["disable_timer"] = true;
        }

        $timer = $this->Timesheets_model->get_task_timer_info($task_id, $this->login_user->id)->getRow();
        if ($timer) {
            $view_data['timer_status'] = "open";
        } else {
            $view_data['timer_status'] = "";
        }

        $view_data['project_id'] = $model_info->project_id;

        $view_data['can_create_tasks'] = $this->can_create_tasks($model_info->context); //for sub task cration. context should be same. 

        $view_data['parent_task_title'] = $this->Tasks_model->get_one($model_info->parent_task_id)->title;

        $view_data["view_type"] = $view_type;

        $view_data["blocked_by"] = $this->_make_dependency_tasks_view_data($this->_get_all_dependency_for_this_task_specific($model_info->blocked_by, $task_id, "blocked_by"), $task_id, "blocked_by");
        $view_data["blocking"] = $this->_make_dependency_tasks_view_data($this->_get_all_dependency_for_this_task_specific($model_info->blocking, $task_id, "blocking"), $task_id, "blocking");

        $view_data["project_deadline"] = $this->_get_project_deadline_for_task($model_info->project_id);

        //count total worked hours in a task
        $timesheet_options = array("project_id" => $model_info->project_id, "task_id" => $model_info->id);

        //get allowed member ids
        $members = $this->_get_members_to_manage_timesheet();
        if ($members != "all" && $this->login_user->user_type == "staff") {
            //if user has permission to access all members, query param is not required
            //client can view all timesheet
            $timesheet_options["allowed_members"] = $members;
        }

        $info = $this->Timesheets_model->count_total_time($timesheet_options);
        $view_data["total_task_hours"] = convert_seconds_to_time_format($info->timesheet_total);
        $view_data["show_timesheet_info"] = $this->can_view_timesheet($model_info->project_id);
        $view_data["show_time_with_task"] = (get_setting("show_time_with_task_start_date_and_deadline")) ? true : false;

        $view_data['contexts'] = $this->_get_accessible_contexts();

        $view_data["checklist_templates"] = $this->Checklist_template_model->get_details()->getResult();
        $view_data["checklist_groups"] = $this->Checklist_groups_model->get_details()->getResult();

        if ($view_type == "details") {
            return $this->template->rander('tasks/view', $view_data);
        } else {
            return $this->template->view('tasks/view', $view_data);
        }
    }

    private function _make_dependency_tasks_view_data($task_ids = "", $task_id = 0, $type = "") {
        if ($task_ids) {
            $tasks = "";

            $tasks_list = $this->Tasks_model->get_details(array("task_ids" => $task_ids))->getResult();

            foreach ($tasks_list as $task) {
                $tasks .= $this->_make_dependency_tasks_row_data($task, $task_id, $type);
            }

            return $tasks;
        }
    }

    private function _make_dependency_tasks_row_data($task_info, $task_id, $type) {
        $tasks = "";

        $tasks .= "<div id='dependency-task-row-$task_info->id' class='list-group-item mb5 dependency-task-row b-a rounded' style='border-left: 5px solid $task_info->status_color !important;'>";

        if ($this->can_edit_tasks($task_info)) {
            $tasks .= ajax_anchor(get_uri("tasks/delete_dependency_task/$task_info->id/$task_id/$type"), "<div class='float-end'><i data-feather='x' class='icon-16'></i></div>", array("class" => "delete-dependency-task", "title" => app_lang("delete"), "data-fade-out-on-success" => "#dependency-task-row-$task_info->id", "data-dependency-type" => $type));
        }

        $tasks .= modal_anchor(get_uri("tasks/view"), $task_info->title, array("data-post-id" => $task_info->id, "data-modal-lg" => "1"));

        $tasks .= "</div>";

        return $tasks;
    }

    private function _get_all_dependency_for_this_task_specific($task_ids = "", $task_id = 0, $type = "") {
        if ($task_id && $type) {
            //find the other tasks dependency with this task
            $dependency_tasks = $this->Tasks_model->get_all_dependency_for_this_task($task_id, $type);

            if ($dependency_tasks) {
                if ($task_ids) {
                    $task_ids .= "," . $dependency_tasks;
                } else {
                    $task_ids = $dependency_tasks;
                }
            }

            return $task_ids;
        }
    }

    function delete_dependency_task($dependency_task_id, $task_id, $type) {
        validate_numeric_value($dependency_task_id);
        validate_numeric_value($task_id);
        $task_info = $this->Tasks_model->get_one($task_id);

        if (!$this->can_edit_tasks($task_info)) {
            app_redirect("forbidden");
        }

        //the dependency task could be resided in both place
        //so, we've to search on both        
        $dependency_tasks_of_own = $task_info->$type;
        if ($type == "blocked_by") {
            $dependency_tasks_of_others = $this->Tasks_model->get_one($dependency_task_id)->blocking;
        } else {
            $dependency_tasks_of_others = $this->Tasks_model->get_one($dependency_task_id)->blocked_by;
        }

        //first check if it contains only a single task
        if (!strpos($dependency_tasks_of_own, ',') && $dependency_tasks_of_own == $dependency_task_id) {
            $data = array($type => "");
            $this->Tasks_model->update_custom_data($data, $task_id);
        } else if (!strpos($dependency_tasks_of_others, ',') && $dependency_tasks_of_others == $task_id) {
            $data = array((($type == "blocked_by") ? "blocking" : "blocked_by") => "");
            $this->Tasks_model->update_custom_data($data, $dependency_task_id);
        } else {
            //have multiple values
            $dependency_tasks_of_own_array = explode(',', $dependency_tasks_of_own);
            $dependency_tasks_of_others_array = explode(',', $dependency_tasks_of_others);

            if (in_array($dependency_task_id, $dependency_tasks_of_own_array)) {
                unset($dependency_tasks_of_own_array[array_search($dependency_task_id, $dependency_tasks_of_own_array)]);
                $dependency_tasks_of_own_array = implode(',', $dependency_tasks_of_own_array);
                $data = array($type => $dependency_tasks_of_own_array);
                $this->Tasks_model->update_custom_data($data, $task_id);
            } else if (in_array($task_id, $dependency_tasks_of_others_array)) {
                unset($dependency_tasks_of_others_array[array_search($task_id, $dependency_tasks_of_others_array)]);
                $dependency_tasks_of_others_array = implode(',', $dependency_tasks_of_others_array);
                $data = array((($type == "blocked_by") ? "blocking" : "blocked_by") => $dependency_tasks_of_others_array);
                $this->Tasks_model->update_custom_data($data, $dependency_task_id);
            }
        }

        echo json_encode(array("success" => true));
    }

    /* checklist */

    function save_checklist_item() {

        $task_id = $this->request->getPost("task_id");
        $is_checklist_group = $this->request->getPost("is_checklist_group");

        $this->validate_submitted_data(array(
            "task_id" => "required|numeric"
        ));

        $task_info = $this->Tasks_model->get_one($task_id);

        if ($task_id) {
            if (!$this->can_edit_tasks($task_info)) {
                app_redirect("forbidden");
            }
        }

        $sort = $this->Checklist_items_model->get_next_sort_value($task_id);

        $success_data = "";
        if ($is_checklist_group) {
            $checklist_group_id = $this->request->getPost("checklist-add-item");
            $checklists = $this->Checklist_template_model->get_details(array("group_id" => $checklist_group_id))->getResult();
            foreach ($checklists as $checklist) {
                $data = array(
                    "task_id" => $task_id,
                    "title" => $checklist->title,
                    "sort" => $sort
                );
                $data = clean_data($data);
                $save_id = $this->Checklist_items_model->ci_save($data);
                if ($save_id) {
                    $item_info = $this->Checklist_items_model->get_details(array("id" => $save_id))->getRow();
                    $success_data .= $this->_make_checklist_item_row($item_info);
                }

                $sort = $sort + 10; // increase sort value for the next item
            }
        } else {
            $data = array(
                "task_id" => $task_id,
                "title" => $this->request->getPost("checklist-add-item"),
                "sort" => $sort
            );
            $data = clean_data($data);
            $save_id = $this->Checklist_items_model->ci_save($data);
            if ($save_id) {
                $item_info = $this->Checklist_items_model->get_details(array("id" => $save_id))->getRow();
                $success_data = $this->_make_checklist_item_row($item_info);
            }
        }

        if ($success_data) {
            echo json_encode(array("success" => true, "data" => $success_data, 'id' => $save_id));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    function save_checklist_item_status($id = 0) {
        validate_numeric_value($id);

        $task_id = $this->Checklist_items_model->get_one($id)->task_id;

        $task_info = $this->Tasks_model->get_one($task_id);

        if (!$this->can_edit_tasks($task_info)) {
            app_redirect("forbidden");
        }

        $data = array(
            "is_checked" => $this->request->getPost('value')
        );

        $data = clean_data($data);

        $save_id = $this->Checklist_items_model->ci_save($data, $id);

        if ($save_id) {
            $item_info = $this->Checklist_items_model->get_details(array("id" => $save_id))->getRow();
            echo json_encode(array("success" => true, "data" => $this->_make_checklist_item_row($item_info, "data"), 'id' => $save_id));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    function save_checklist_items_sort() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric",
            "sort" => "required|numeric"
        ));

        $id = $this->request->getPost("id");
        $sort = $this->request->getPost("sort");
        $data = array(
            "sort" => $sort
        );

        $data = clean_data($data);

        $this->Checklist_items_model->ci_save($data, $id);
    }

    function delete_checklist_item($id) {
        validate_numeric_value($id);

        $task_id = $this->Checklist_items_model->get_one($id)->task_id;

        if ($id) {
            if (!$this->can_edit_tasks($task_id)) {
                app_redirect("forbidden");
            }
        }

        if ($this->Checklist_items_model->delete($id)) {
            echo json_encode(array("success" => true));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //load global gantt view
    function all_gantt() {
        $this->access_only_team_members();

        if ($this->has_all_projects_restricted_role()) {
            app_redirect("forbidden");
        }

        $view_data = $this->_prepare_common_gantt_filters();

        $project_id = 0;
        $view_data['project_id'] = $project_id;

        //prepare members list
        $view_data['milestone_dropdown'] = $this->_get_milestones_dropdown_list($project_id);
        $view_data["show_milestone_info"] = $this->can_view_milestones();
        $view_data['status_dropdown'] = $this->_get_task_statuses_dropdown($project_id);
        $view_data['show_tasks_tab'] = true;
        $view_data["has_all_projects_restricted_role"] = $this->has_all_projects_restricted_role();

        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("tasks", $this->login_user->is_admin, $this->login_user->user_type);

        return $this->template->rander("projects/gantt/index", $view_data);
    }

    function save_dependency_tasks() {
        $this->validate_submitted_data(array(
            "task_id" => "numeric"
        ));

        $task_id = $this->request->getPost("task_id");
        if (!$task_id) {
            return false;
        }

        $dependency_task = $this->request->getPost("dependency_task");
        $dependency_type = $this->request->getPost("dependency_type");

        if (!$dependency_task) {
            return false;
        }

        //add the new task with old
        $task_info = $this->Tasks_model->get_one($task_id);

        if (!$this->can_edit_tasks($task_info)) {
            app_redirect("forbidden");
        }

        $dependency_tasks = $task_info->$dependency_type;
        if ($dependency_tasks) {
            $dependency_tasks .= "," . $dependency_task;
        } else {
            $dependency_tasks = $dependency_task;
        }

        $data = array(
            $dependency_type => $dependency_tasks
        );

        $data = clean_data($data);

        $this->Tasks_model->update_custom_data($data, $task_id);
        $dependency_task_info = $this->Tasks_model->get_details(array("id" => $dependency_task))->getRow();

        echo json_encode(array("success" => true, "data" => $this->_make_dependency_tasks_row_data($dependency_task_info, $task_id, $dependency_type), 'message' => app_lang('record_saved')));
    }

    private function _get_all_dependency_for_this_task($task_id) {
        $task_info = $this->Tasks_model->get_one($task_id);
        $blocked_by = $this->_get_all_dependency_for_this_task_specific($task_info->blocked_by, $task_id, "blocked_by");
        $blocking = $this->_get_all_dependency_for_this_task_specific($task_info->blocking, $task_id, "blocking");

        $all_tasks = $blocked_by;
        if ($blocking) {
            if ($all_tasks) {
                $all_tasks .= "," . $blocking;
            } else {
                $all_tasks = $blocking;
            }
        }

        return $all_tasks;
    }

    function get_existing_dependency_tasks($task_id = 0) {
        if (!$task_id) {
            return false;
        }

        validate_numeric_value($task_id);
        $model_info = $this->Tasks_model->get_details(array("id" => $task_id))->getRow();

        if (!$this->can_view_tasks("", 0, $model_info)) {
            app_redirect("forbidden");
        }

        $all_dependency_tasks = $this->_get_all_dependency_for_this_task($task_id);

        //add this task id
        if ($all_dependency_tasks) {
            $all_dependency_tasks .= "," . $task_id;
        } else {
            $all_dependency_tasks = $task_id;
        }

        //make tasks dropdown
        $options = array("exclude_task_ids" => $all_dependency_tasks);

        $context_id_pairs = $this->get_context_id_pairs();

        foreach ($context_id_pairs as $pair) {
            $id_key = get_array_value($pair, "id_key");
            if (!$id_key) continue;

            $options[$id_key] = $model_info->$id_key;
        }


        $tasks_dropdown = array();
        $tasks = $this->Tasks_model->get_details($options)->getResult();
        foreach ($tasks as $task) {
            $tasks_dropdown[] = array("id" => $task->id, "text" => $task->id . " - " . $task->title);
        }

        echo json_encode(array("success" => true, "tasks_dropdown" => $tasks_dropdown));
    }

    function save_gantt_task_date() {
        $this->validate_submitted_data(array(
            "task_id" => "numeric|required",
        ));

        $task_id = $this->request->getPost("task_id");
        if (!$this->can_edit_tasks($task_id)) {
            app_redirect("forbidden");
        }

        $start_date = $this->request->getPost("start_date");
        $deadline = $this->request->getPost("deadline");

        $data = array(
            "start_date" => $start_date,
            "deadline" => $deadline,
        );

        $data = clean_data($data);

        $save_id = $this->Tasks_model->save_gantt_task_date($data, $task_id);
        if ($save_id) {

            /* Send notification
              $activity_log_id = get_array_value($data, "activity_log_id");

              $new_activity_log_id = save_custom_fields("tasks", $save_id, $this->login_user->is_admin, $this->login_user->user_type, $activity_log_id);

              log_notification("project_task_updated", array("project_id" => $task_info->project_id, "task_id" => $save_id, "activity_log_id" => $new_activity_log_id ? $new_activity_log_id : $activity_log_id));
             */

            echo json_encode(array("success" => true));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    private function _get_collaborators_ids($collaborators_data) {
        $explode_collaborators = explode(", ", $collaborators_data);
        if (!($explode_collaborators && count($explode_collaborators))) {
            return false;
        }

        $groups_ids = "";

        foreach ($explode_collaborators as $collaborator) {
            $collaborator = trim($collaborator);

            $existing_user = $this->Users_model->get_user_from_full_name($collaborator);
            if ($existing_user) {
                //user exists, add the user id to collaborator ids
                if ($groups_ids) {
                    $groups_ids .= ",";
                }
                $groups_ids .= $existing_user->id;
            } else {
                //flag error that anyone of the list isn't exists
                return false;
            }
        }

        if ($groups_ids) {
            return $groups_ids;
        }
    }

    /* load task list view tab */

    function project_tasks($project_id) {
        validate_numeric_value($project_id);

        if (!$this->can_view_tasks("project", $project_id)) {
            app_redirect("forbidden");
        }

        $this->init_project_permission_checker($project_id);

        $view_data['project_id'] = $project_id;
        $view_data['view_type'] = "project_tasks";

        $view_data['can_create_tasks'] = $this->can_create_tasks("project");
        $view_data['can_edit_tasks'] = $this->_can_edit_project_tasks($project_id);
        $view_data['can_delete_tasks'] = $this->_can_delete_project_tasks($project_id);
        $view_data["show_milestone_info"] = $this->can_view_milestones();

        $view_data['milestone_dropdown'] = $this->_get_milestones_dropdown_list($project_id);
        $view_data['priorities_dropdown'] = $this->_get_priorities_dropdown_list();
        $view_data['assigned_to_dropdown'] = $this->_get_project_members_dropdown_list($project_id);
        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("tasks", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("tasks", $this->login_user->is_admin, $this->login_user->user_type);

        $exclude_status_ids = $this->get_removed_task_status_ids($project_id);
        $view_data['task_statuses'] = $this->Task_status_model->get_details(array("exclude_status_ids" => $exclude_status_ids))->getResult();

        $view_data["show_assigned_tasks_only"] = get_array_value($this->login_user->permissions, "show_assigned_tasks_only");
        $view_data['labels_dropdown'] = json_encode($this->make_labels_dropdown("task", "", true));

        return $this->template->view("projects/tasks/index", $view_data);
    }

    /* load task kanban view of view tab */

    function project_tasks_kanban($project_id) {
        validate_numeric_value($project_id);

        if (!$this->can_view_tasks("project", $project_id)) {
            app_redirect("forbidden");
        }

        $this->init_project_permission_checker($project_id);

        $view_data['project_id'] = $project_id;

        $view_data['can_create_tasks'] = $this->can_create_tasks("project");
        $view_data["show_milestone_info"] = $this->can_view_milestones();

        $view_data['milestone_dropdown'] = $this->_get_milestones_dropdown_list($project_id);
        $view_data['priorities_dropdown'] = $this->_get_priorities_dropdown_list();
        $view_data['assigned_to_dropdown'] = $this->_get_project_members_dropdown_list($project_id);

        $exclude_status_ids = $this->get_removed_task_status_ids($project_id);
        $view_data['task_statuses'] = $this->Task_status_model->get_details(array("exclude_status_ids" => $exclude_status_ids))->getResult();
        $view_data['can_edit_tasks'] = $this->_can_edit_project_tasks($project_id);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("tasks", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data['labels_dropdown'] = json_encode($this->make_labels_dropdown("task", "", true));

        return $this->template->view("projects/tasks/kanban/project_tasks", $view_data);
    }

    private function _get_milestones_dropdown_list($project_id = 0) {
        $milestones = $this->Milestones_model->get_details(array("project_id" => $project_id, "deleted" => 0))->getResult();
        $milestone_dropdown = array(array("id" => "", "text" => "- " . app_lang("milestone") . " -"));

        foreach ($milestones as $milestone) {
            $milestone_dropdown[] = array("id" => $milestone->id, "text" => $milestone->title);
        }
        return json_encode($milestone_dropdown);
    }

    private function _get_priorities_dropdown_list($priority_id = 0) {
        $priorities = $this->Task_priority_model->get_details()->getResult();
        $priorities_dropdown = array(array("id" => "", "text" => "- " . app_lang("priority") . " -"));

        //if there is any specific priority selected, select only the priority.
        $selected_status = false;
        foreach ($priorities as $priority) {
            if (isset($priority_id) && $priority_id) {
                if ($priority->id == $priority_id) {
                    $selected_status = true;
                } else {
                    $selected_status = false;
                }
            }

            $priorities_dropdown[] = array("id" => $priority->id, "text" => $priority->title, "isSelected" => $selected_status);
        }
        return json_encode($priorities_dropdown);
    }

    private function _get_project_members_dropdown_list($project_id = 0) {
        if ($this->login_user->user_type === "staff") {
            $assigned_to_dropdown = array(array("id" => "", "text" => "- " . app_lang("assigned_to") . " -"));
            $user_ids = array();
            if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
                array_push($user_ids, $this->login_user->id);
            }
            $assigned_to_list = $this->Project_members_model->get_project_members_id_and_text_dropdown($project_id, $user_ids, true, true);
            $assigned_to_dropdown = array_merge($assigned_to_dropdown, $assigned_to_list);
        } else {
            $assigned_to_dropdown = array(
                array("id" => "", "text" => app_lang("all_tasks")),
                array("id" => $this->login_user->id, "text" => app_lang("my_tasks"))
            );
        }

        return json_encode($assigned_to_dropdown);
    }

    function all_tasks($tab = "", $status_id = 0, $priority_id = 0, $type = "", $deadline = "") {
        $this->access_only_team_members();
        $view_data['project_id'] = 0;

        $projects = $this->Tasks_model->get_my_projects_dropdown_list($this->_get_only_own_projects_user_id())->getResult();
        $projects_dropdown = array(array("id" => "", "text" => "- " . app_lang("project") . " -"));
        foreach ($projects as $project) {
            if ($project->project_id && $project->project_title) {
                $projects_dropdown[] = array("id" => $project->project_id, "text" => $project->project_title);
            }
        }

        $team_members_dropdown = array(array("id" => "", "text" => "- " . app_lang("team_member") . " -"));

        $options = array("status" => "active", "user_type" => "staff");
        if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
            $options["id"] = $this->login_user->id;
        }

        $assigned_to_list = $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", $options);
        foreach ($assigned_to_list as $key => $value) {

            if (($status_id || $priority_id || $deadline) && $type != "my_tasks_overview") {
                $team_members_dropdown[] = array("id" => $key, "text" => $value);
            } else {
                if ($key == $this->login_user->id) {
                    $team_members_dropdown[] = array("id" => $key, "text" => $value, "isSelected" => true);
                } else {
                    $team_members_dropdown[] = array("id" => $key, "text" => $value);
                }
            }
        }

        $view_data['tab'] = $tab;
        $view_data['selected_status_id'] = $status_id;
        $view_data['selected_priority_id'] = $priority_id;
        $view_data['selected_deadline'] = $deadline;

        $view_data['team_members_dropdown'] = json_encode($team_members_dropdown);
        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("tasks", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("tasks", $this->login_user->is_admin, $this->login_user->user_type);

        $view_data['task_statuses'] = $this->Task_status_model->get_details()->getResult();

        $view_data['projects_dropdown'] = json_encode($projects_dropdown);
        $view_data['can_create_tasks'] = $this->can_create_tasks();
        $view_data['priorities_dropdown'] = $this->_get_priorities_dropdown_list($priority_id);
        $view_data['contexts_dropdown'] = json_encode($this->_get_accessible_contexts_dropdown());
        $view_data["has_all_projects_restricted_role"] = $this->has_all_projects_restricted_role();
        $view_data['labels_dropdown'] = json_encode($this->make_labels_dropdown("task", "", true));

        return $this->template->rander("tasks/all_tasks", $view_data);
    }

    function _get_accessible_contexts_dropdown($type = "view") {
        $contexts = $this->_get_accessible_contexts($type);

        $contexts_dropdown = array(array("id" => "", "text" => "- " . app_lang("related_to") . " -"));

        foreach ($contexts as $context) {

            $text = app_lang($context);
            if ($context === "general") {
                $text = app_lang("none");
            }

            $contexts_dropdown[] = array("id" => $context, "text" => $text);
        }

        return $contexts_dropdown;
    }

    private function _get_only_own_projects_user_id() {
        //only admin/ the user has permission to manage all projects, can see all projects, other team mebers can see only their own projects.
        $only_own_projects_user_id = 0;
        if (!$this->can_manage_all_projects()) {
            $only_own_projects_user_id = $this->login_user->id;
        }

        return $only_own_projects_user_id;
    }

    function all_tasks_kanban() {
        $projects = $this->Tasks_model->get_my_projects_dropdown_list($this->_get_only_own_projects_user_id())->getResult();
        $projects_dropdown = array(array("id" => "", "text" => "- " . app_lang("project") . " -"));
        foreach ($projects as $project) {
            if ($project->project_id && $project->project_title) {
                $projects_dropdown[] = array("id" => $project->project_id, "text" => $project->project_title);
            }
        }

        $options = array("status" => "active", "user_type" => "staff");
        if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
            $options["id"] = $this->login_user->id;
        }

        $team_members_dropdown = array(array("id" => "", "text" => "- " . app_lang("team_member") . " -"));
        $assigned_to_list = $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", $options);
        foreach ($assigned_to_list as $key => $value) {

            if ($key == $this->login_user->id) {
                $team_members_dropdown[] = array("id" => $key, "text" => $value, "isSelected" => true);
            } else {
                $team_members_dropdown[] = array("id" => $key, "text" => $value);
            }
        }

        $view_data['team_members_dropdown'] = json_encode($team_members_dropdown);
        $view_data['priorities_dropdown'] = $this->_get_priorities_dropdown_list();

        $view_data['projects_dropdown'] = json_encode($projects_dropdown);
        $view_data['can_create_tasks'] = $this->can_create_tasks();

        $view_data['task_statuses'] = $this->Task_status_model->get_details()->getResult();
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("tasks", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data['contexts_dropdown'] = json_encode($this->_get_accessible_contexts_dropdown());
        $view_data["has_all_projects_restricted_role"] = $this->has_all_projects_restricted_role();
        $view_data['labels_dropdown'] = json_encode($this->make_labels_dropdown("task", "", true));

        return $this->template->rander("tasks/kanban/all_tasks", $view_data);
    }

    //check user's task editting permission on changing of project
    function can_edit_task_of_the_project($project_id = 0) {
        validate_numeric_value($project_id);
        if ($project_id) {

            if ($this->_can_edit_project_tasks($project_id)) {
                echo json_encode(array("success" => true));
            } else {
                echo json_encode(array("success" => false));
            }
        }
    }

    function all_tasks_kanban_data() {

        $this->access_only_team_members();

        $project_id = $this->request->getPost('project_id');

        $specific_user_id = $this->request->getPost('specific_user_id');

        $options = array(
            "specific_user_id" => $specific_user_id,
            "project_id" => $project_id,
            "milestone_id" => $this->request->getPost('milestone_id'),
            "priority_id" => $this->request->getPost('priority_id'),
            "deadline" => $this->request->getPost('deadline'),
            "search" => $this->request->getPost('search'),
            "context" => $this->request->getPost('context'),
            "unread_status_user_id" => $this->login_user->id,
            "quick_filter" => $this->request->getPost("quick_filter"),
            "label_id" => $this->request->getPost('label_id'),
            "custom_field_filter" => $this->prepare_custom_field_filter_values("tasks", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $view_data['can_edit_project_tasks'] = $this->_can_edit_project_tasks($project_id);
        $view_data['project_id'] = $project_id;

        //prepare accessible query parameters
        $contexts = $this->_get_accessible_contexts("view");
        $options = array_merge($options, $this->_prepare_query_parameters_for_accessible_contexts($contexts));

        if (count($contexts) == 0) {
            //don't show anything 
            $options["context"] = "noting";
        }

        $max_sort = $this->request->getPost('max_sort');
        $column_id = $this->request->getPost('kanban_column_id');

        if ($column_id) {
            //load only signle column data. load more.. 
            $options["get_after_max_sort"] = $max_sort;
            $options["status_id"] = $column_id;
            $options["limit"] = 100;

            $view_data["tasks"] = $this->Tasks_model->get_kanban_details($options)->getResult();
            $tasks_edit_permissions = $this->_get_tasks_status_edit_permissions($view_data["tasks"]);
            $view_data["tasks_edit_permissions"] = $tasks_edit_permissions;
            return $this->template->view('tasks/kanban/kanban_column_items', $view_data);
        } else {
            $task_count_query_options = $options;
            $task_count_query_options["return_task_counts_only"] = true;
            $task_counts = $this->Tasks_model->get_kanban_details($task_count_query_options)->getResult();
            $column_tasks_count = array();
            foreach ($task_counts as $task_count) {
                $column_tasks_count[$task_count->status_id] = $task_count->tasks_count;
            }

            $exclude_status_ids = $this->get_removed_task_status_ids($project_id);
            $task_status_options = array("hide_from_kanban" => 0, "exclude_status_ids" => $exclude_status_ids);
            if (!$project_id) {
                $task_status_options["hide_from_non_project_related_tasks"] = 0;
            }
            $statuses = $this->Task_status_model->get_details($task_status_options);

            $view_data["total_columns"] = $statuses->resultID->num_rows;
            $columns = $statuses->getResult();

            $tasks_list = array();
            $tasks_edit_permissions_list = array();

            foreach ($columns as $column) {
                $status_id = $column->id;

                //find the tasks if there is any task
                if (get_array_value($column_tasks_count, $status_id)) {
                    $options["status_id"] = $status_id;
                    $options["limit"] = 15;

                    $tasks = $this->Tasks_model->get_kanban_details($options)->getResult();
                    $tasks_list[$status_id] = $tasks;
                    $tasks_edit_permissions_list[$status_id] = $this->_get_tasks_status_edit_permissions($tasks);
                }
            }
            $view_data["tasks_edit_permissions_list"] = $tasks_edit_permissions_list;
            $view_data["columns"] = $columns;
            $view_data['column_tasks_count'] = $column_tasks_count;
            $view_data['tasks_list'] = $tasks_list;

            return $this->template->view('tasks/kanban/kanban_view', $view_data);
        }
    }

    private function _get_tasks_edit_permissions($tasks = array()) {

        $permissions = array();
        foreach ($tasks as $task_info) {
            $permissions[$task_info->id] = $this->can_edit_tasks($task_info);
        }
        return $permissions;
    }

    private function _get_tasks_status_edit_permissions($tasks = array(), $tasks_edit_permissions = array()) {
        $permissions = array();
        foreach ($tasks as $task_info) {
            if (get_array_value($tasks_edit_permissions, $task_info->id)) {
                $permissions[$task_info->id] = true; //to reduce load, check already checking data. If user has permission to edit, he/she can update the status also. 
            } else {
                $permissions[$task_info->id] = $this->_can_edit_task_status($task_info);
            }
        }
        return $permissions;
    }

    /* prepare data for the project view's kanban tab  */

    function project_tasks_kanban_data($project_id = 0) {
        validate_numeric_value($project_id);

        if (!$this->can_view_tasks("project", $project_id)) {
            app_redirect("forbidden");
        }

        $specific_user_id = $this->request->getPost('specific_user_id');

        $options = array(
            "specific_user_id" => $specific_user_id,
            "project_id" => $project_id,
            "assigned_to" => $this->request->getPost('assigned_to'),
            "milestone_id" => $this->request->getPost('milestone_id'),
            "priority_id" => $this->request->getPost('priority_id'),
            "deadline" => $this->request->getPost('deadline'),
            "search" => $this->request->getPost('search'),
            "unread_status_user_id" => $this->login_user->id,
            "show_assigned_tasks_only_user_id" => $this->show_assigned_tasks_only_user_id(),
            "quick_filter" => $this->request->getPost('quick_filter'),
            "label_id" => $this->request->getPost('label_id'),
            "custom_field_filter" => $this->prepare_custom_field_filter_values("tasks", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $view_data['can_edit_project_tasks'] = $this->_can_edit_project_tasks($project_id);
        $view_data['project_id'] = $project_id;

        $max_sort = $this->request->getPost('max_sort');
        $column_id = $this->request->getPost('kanban_column_id');

        if ($column_id) {
            //load only signle column data. load more.. 
            $options["get_after_max_sort"] = $max_sort;
            $options["status_id"] = $column_id;
            $options["limit"] = 100;
            $view_data["tasks"] = $this->Tasks_model->get_kanban_details($options)->getResult();
            $tasks_edit_permissions = $this->_get_tasks_status_edit_permissions($view_data["tasks"]);
            $view_data["tasks_edit_permissions"] = $tasks_edit_permissions;
            return $this->template->view('tasks/kanban/kanban_column_items', $view_data);
        } else {
            //load initial data. full view.
            $task_count_query_options = $options;
            $task_count_query_options["return_task_counts_only"] = true;
            $task_counts = $this->Tasks_model->get_kanban_details($task_count_query_options)->getResult();
            $column_tasks_count = [];
            foreach ($task_counts as $task_count) {
                $column_tasks_count[$task_count->status_id] = $task_count->tasks_count;
            }

            $exclude_status_ids = $this->get_removed_task_status_ids($project_id);
            $statuses = $this->Task_status_model->get_details(array("hide_from_kanban" => 0, "exclude_status_ids" => $exclude_status_ids));

            $view_data["total_columns"] = $statuses->resultID->num_rows;
            $columns = $statuses->getResult();

            $tasks_list = array();
            $tasks_edit_permissions_list = array();

            foreach ($columns as $column) {
                $status_id = $column->id;

                //find the tasks if there is any task
                if (get_array_value($column_tasks_count, $status_id)) {
                    $options["status_id"] = $status_id;
                    $options["limit"] = 15;

                    $tasks = $this->Tasks_model->get_kanban_details($options)->getResult();
                    $tasks_list[$status_id] = $tasks;
                    $tasks_edit_permissions_list[$status_id] = $this->_get_tasks_status_edit_permissions($tasks);
                }
            }

            $view_data["tasks_edit_permissions_list"] = $tasks_edit_permissions_list;
            $view_data["columns"] = $columns;
            $view_data['column_tasks_count'] = $column_tasks_count;
            $view_data['tasks_list'] = $tasks_list;
            return $this->template->view('tasks/kanban/kanban_view', $view_data);
        }
    }

    function set_task_comments_as_read($task_id = 0) {
        if ($task_id) {
            validate_numeric_value($task_id);
            $this->Tasks_model->set_task_comments_as_read($task_id, $this->login_user->id);
        }
    }

    /* get all related data of selected project */

    function get_dropdowns($context = "", $context_id = 0, $return_empty_context = false) {
        $dropdowns = $this->_get_task_related_dropdowns($context, $context_id, $return_empty_context);
        echo json_encode($dropdowns);
    }

    function save_sub_task() {
        $client_id = $this->request->getPost('client_id');
        $lead_id = $this->request->getPost('lead_id');
        $invoice_id = $this->request->getPost('invoice_id');
        $project_id = $this->request->getPost('project_id');
        $estimate_id = $this->request->getPost('estimate_id');
        $order_id = $this->request->getPost('order_id');
        $contract_id = $this->request->getPost('contract_id');
        $proposal_id = $this->request->getPost('proposal_id');
        $subscription_id = $this->request->getPost('subscription_id');
        $expense_id = $this->request->getPost('expense_id');
        $ticket_id = $this->request->getPost('ticket_id');
        $context = $this->request->getPost('context');

        $this->validate_submitted_data(array(
            "parent_task_id" => "required|numeric"
        ));

        if (!$this->can_create_tasks($context)) {
            app_redirect("forbidden");
        }

        $data = array(
            "title" => $this->request->getPost('sub-task-title'),
            "project_id" => $project_id,
            "client_id" => $client_id,
            "lead_id" => $lead_id,
            "invoice_id" => $invoice_id,
            "estimate_id" => $estimate_id,
            "order_id" => $order_id,
            "contract_id" => $contract_id,
            "proposal_id" => $proposal_id,
            "expense_id" => $expense_id,
            "subscription_id" => $subscription_id,
            "ticket_id" => $ticket_id,
            "context" => $context,
            "milestone_id" => $this->request->getPost('milestone_id'),
            "parent_task_id" => $this->request->getPost('parent_task_id'),
            "status_id" => 1,
            "created_date" => get_current_utc_time(),
            "created_by" => $this->login_user->id
        );

        //don't get assign to id if login user is client
        if ($this->login_user->user_type == "client") {
            $data["assigned_to"] = 0;
        } else {
            $data["assigned_to"] = $this->login_user->id;
        }

        $data = clean_data($data);

        $data["sort"] = $this->Tasks_model->get_next_sort_value($project_id, $data['status_id']);

        $save_id = $this->Tasks_model->ci_save($data);

        if ($save_id) {
            $task_info = $this->Tasks_model->get_details(array("id" => $save_id))->getRow();
            $this->_send_task_created_notification($task_info);

            echo json_encode(array("success" => true, "task_data" => $this->_make_sub_task_row($task_info), "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* upadate a task status */

    function save_task_status($id = 0) {
        validate_numeric_value($id);
        $status_id = $this->request->getPost('value');
        $data = array(
            "status_id" => $status_id
        );

        $this->check_sub_tasks_statuses($status_id, $id);

        $task_info = $this->Tasks_model->get_details(array("id" => $id))->getRow();

        if (!$this->_can_edit_task_status($task_info)) {
            app_redirect("forbidden");
        }

        if ($task_info->status_id !== $status_id) {
            $data["status_changed_at"] = get_current_utc_time();
        }

        $data = clean_data($data);
        $save_id = $this->Tasks_model->ci_save($data, $id);

        if ($save_id) {
            $task_info = $this->Tasks_model->get_details(array("id" => $id))->getRow();
            echo json_encode(array("success" => true, "data" => (($this->request->getPost("type") == "sub_task") ? $this->_make_sub_task_row($task_info, "data") : $this->_row_data($save_id)), 'id' => $save_id, "message" => app_lang('record_saved')));

            $this->_send_task_updated_notification($task_info,  get_array_value($data, "activity_log_id"));
        } else {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }
    }

    function update_task_info($id = 0, $data_field = "") {
        if (!$id) {
            return false;
        }

        validate_numeric_value($id);
        $task_info = $this->Tasks_model->get_one($id);

        if (!$this->can_edit_tasks($task_info)) {
            app_redirect("forbidden");
        }

        $value = $this->request->getPost('value');

        $start_date = get_date_from_datetime($task_info->start_date);
        $deadline = get_date_from_datetime($task_info->deadline);
        $start_time = get_time_from_datetime($task_info->start_date);
        $end_time = get_time_from_datetime($task_info->deadline);

        if ($data_field == "start_date") {

            if ($deadline && $deadline < $value) {
                echo json_encode(array("success" => false, 'message' => app_lang('deadline_must_be_equal_or_greater_than_start_date')));
                return false;
            }

            $data = array(
                $data_field => $value . " " . $start_time
            );
        } else if ($data_field == "deadline") {
            //deadline must be greater or equal to start date
            if ($start_date && $value < $start_date) {
                echo json_encode(array("success" => false, 'message' => app_lang('deadline_must_be_equal_or_greater_than_start_date')));
                return false;
            }

            $data = array(
                $data_field => $value . " " . $end_time
            );
        } else if ($data_field == "start_time" || $data_field == "end_time") {
            if (get_setting("time_format") != "24_hours") {
                $value = convert_time_to_24hours_format($value);
            }

            if ($data_field == "start_time") {

                if ($end_time && $end_time != "00:00:00" && ($start_date . " " . $value) > $task_info->deadline) {
                    echo json_encode(array("success" => false, 'message' => app_lang('deadline_must_be_equal_or_greater_than_start_date')));
                    return false;
                }

                $data["start_date"] = $start_date . " " . $value;
            } else if ($data_field == "end_time") {

                if ($task_info->start_date > ($deadline . " " . $value)) {
                    echo json_encode(array("success" => false, 'message' => app_lang('deadline_must_be_equal_or_greater_than_start_date')));
                    return false;
                }

                $data["deadline"] = $deadline . " " . $value;
            }
        } else if ($data_field == "collaborators" || $data_field == "labels") {
            validate_list_of_numbers($value);
            $data = array(
                $data_field => $value
            );
        } else {
            $data = array(
                $data_field => $value
            );
        }

        if ($data_field === "status_id" && $task_info->status_id !== $value) {
            $data["status_changed_at"] = get_current_utc_time();
        }

        if ($data_field == "status_id") {
            $this->check_sub_tasks_statuses($value, $id);
        }

        $data = clean_data($data);

        $save_id = $this->Tasks_model->ci_save($data, $id);
        if (!$save_id) {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
            return false;
        }

        $task_info = $this->Tasks_model->get_details(array("id" => $save_id))->getRow(); //get data after save

        $success_array = array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, "message" => app_lang('record_saved'));

        if ($data_field == "assigned_to") {
            $success_array["assigned_to_avatar"] = get_avatar($task_info->assigned_to_avatar);
            $success_array["assigned_to_id"] = $task_info->assigned_to;
        }

        if ($data_field == "labels") {
            $success_array["labels"] = $task_info->labels_list ? make_labels_view_data($task_info->labels_list) : "<span class='text-off'>" . app_lang("add") . " " . app_lang("label") . "<span>";
        }

        if ($data_field == "milestone_id") {
            $success_array["milestone_id"] = $task_info->milestone_id;
        }

        if ($data_field == "points") {
            $success_array["points"] = $task_info->points;
        }

        if ($data_field == "status_id") {
            $success_array["status_color"] = $task_info->status_color;
        }

        if ($data_field == "priority_id") {
            $success_array["priority_pill"] = "<span class='sub-task-icon priority-badge' style='background: $task_info->priority_color'><i data-feather='$task_info->priority_icon' class='icon-14'></i></span> ";
            $success_array["priority_id"] = $task_info->priority_id;
        }

        if ($data_field == "collaborators") {
            $success_array["collaborators"] = $task_info->collaborator_list ? $this->_get_collaborators($task_info->collaborator_list, false) : "<span class='text-off'>" . app_lang("add") . " " . app_lang("collaborators") . "<span>";
        }

        if ($data_field == "start_date" || $data_field == "deadline") {
            $date = "-";
            if (is_date_exists($task_info->$data_field)) {
                $date = format_to_date($task_info->$data_field, false);
            }
            $success_array["date"] = $date;

            if (get_setting("show_time_with_task_start_date_and_deadline")) {
                if ($data_field == "start_date" && !$start_date) {
                    $success_array["time"] = " " . js_anchor("<span class='text-off'>" . app_lang("add") . " " . app_lang("start_time") . "<span>", array("data-id" => $save_id, "data-value" => "", "data-act" => "update-task-info", "data-act-type" => "start_time"));
                } else if ($data_field == "deadline" && !$deadline) {
                    $success_array["time"] = " " . js_anchor("<span class='text-off'>" . app_lang("add") . " " . app_lang("end_time") . "<span>", array("data-id" => $save_id, "data-value" => "", "data-act" => "update-task-info", "data-act-type" => "end_time"));
                }
            }
        }

        if ($data_field == "start_time" || $data_field == "end_time") {
            $time = "-";
            if ($data_field == "start_time") {
                if (is_date_exists($task_info->start_date)) {
                    $time = format_to_time($task_info->start_date, false, true);
                }
            } else if ($data_field == "end_time") {
                if (is_date_exists($task_info->deadline)) {
                    $time = format_to_time($task_info->deadline, false, true);
                }
            }

            $success_array["time"] = $time;
        }

        echo json_encode($success_array);

        $this->_send_task_updated_notification($task_info, get_array_value($data, "activity_log_id"));
    }

    /* upadate a task status */

    function save_task_sort_and_status() {

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $task_info = $this->Tasks_model->get_one($id);

        if (!$this->_can_edit_task_status($task_info)) {
            app_redirect("forbidden");
        }

        $status_id = $this->request->getPost('status_id');
        $this->check_sub_tasks_statuses($status_id, $id);
        $data = array(
            "sort" => $this->request->getPost('sort')
        );

        if ($status_id) {
            $data["status_id"] = $status_id;

            if ($task_info->status_id !== $status_id) {
                $data["status_changed_at"] = get_current_utc_time();
            }
        }

        $data = clean_data($data);

        $save_id = $this->Tasks_model->ci_save($data, $id);

        if ($save_id) {
            if ($status_id) {
                $this->_send_task_updated_notification($task_info, get_array_value($data, "activity_log_id"));
            }
        } else {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }
    }

    /* list of tasks, prepared for datatable  */

    function all_tasks_list_data($is_widget = 0, $is_mobile = 0) {
        $this->access_only_team_members();

        $project_id = $this->request->getPost('project_id');

        $specific_user_id = $this->request->getPost('specific_user_id');

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("tasks", $this->login_user->is_admin, $this->login_user->user_type);

        $quick_filter = $this->request->getPost('quick_filter');
        if ($quick_filter) {
            $status = "";
        } else {
            $status = $this->request->getPost('status_id') ? implode(",", $this->request->getPost('status_id')) : "";
        }

        $context = $this->request->getPost('context');

        $options = array(
            "specific_user_id" => $specific_user_id,
            "project_id" => $project_id,
            "context" => $context,
            "milestone_id" => $this->request->getPost('milestone_id'),
            "priority_id" => $this->request->getPost('priority_id'),
            "deadline" => $this->request->getPost('deadline'),
            "custom_fields" => $custom_fields,
            "status_ids" => $status,
            "unread_status_user_id" => $this->login_user->id,
            "quick_filter" => $quick_filter,
            "label_id" => $this->request->getPost('label_id'),
            "custom_field_filter" => $this->prepare_custom_field_filter_values("tasks", $this->login_user->is_admin, $this->login_user->user_type)
        );

        //prepare accessible query parameters
        $contexts = $this->_get_accessible_contexts("view");
        $options = array_merge($options, $this->_prepare_query_parameters_for_accessible_contexts($contexts));

        if (count($contexts) == 0) {
            //don't show anything 
            $options["context"] = "noting";
        }

        if ($is_widget) {
            $todo_status_id = $this->Task_status_model->get_one_where(array("key_name" => "done", "deleted" => 0));
            if ($todo_status_id) {
                $options["exclude_status_id"] = $todo_status_id->id;
                $options["specific_user_id"] = $this->login_user->id;
            }
        }

        $all_options = append_server_side_filtering_commmon_params($options);

        $result = $this->Tasks_model->get_details($all_options);

        $show_time_with_task = (get_setting("show_time_with_task_start_date_and_deadline")) ? true : false;

        //by this, we can handel the server side or client side from the app table prams.
        if (get_array_value($all_options, "server_side")) {
            $list_data = get_array_value($result, "data");
        } else {
            $list_data = $result->getResult();
            $result = array();
        }


        $tasks_edit_permissions = $this->_get_tasks_edit_permissions($list_data);
        $tasks_status_edit_permissions = $this->_get_tasks_status_edit_permissions($list_data, $tasks_edit_permissions);

        $result_data = array();
        foreach ($list_data as $data) {
            $result_data[] = $this->_make_row($data, $custom_fields, $show_time_with_task, $tasks_edit_permissions, $tasks_status_edit_permissions, $is_mobile);
        }

        $result["data"] = $result_data;

        echo json_encode($result);
    }

    //load gantt tab
    function gantt($project_id = 0) {

        if ($project_id) {
            validate_numeric_value($project_id);

            $this->init_project_permission_checker($project_id);

            $view_data = $this->_prepare_common_gantt_filters($project_id);

            $view_data['project_id'] = $project_id;

            $exclude_status_ids = $this->get_removed_task_status_ids($project_id);
            $task_status_options = array("exclude_status_ids" => $exclude_status_ids);
            if (!$project_id) {
                $task_status_options["hide_from_non_project_related_tasks"] = 0;
            }

            $view_data['status_dropdown'] = $this->_get_task_statuses_dropdown($project_id);

            $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("tasks", $this->login_user->is_admin, $this->login_user->user_type);

            return $this->template->view("projects/gantt/index", $view_data);
        }
    }

    //prepare gantt data for gantt chart
    function gantt_chart_view($project_id = 0) {
        $group_by = $this->request->getPost("group_by") ? $this->request->getPost("group_by") : "milestones";
        $milestone_id = $this->request->getPost("milestone_id");
        $user_id = $this->request->getPost("user_id");
        $status = $this->request->getPost('status_id') ? implode(",", $this->request->getPost('status_id')) : "";
        $project_id = $this->request->getPost("project_id") ? $this->request->getPost("project_id") : $project_id;

        $can_edit_tasks = true;
        if ($project_id) {
            if (!$this->_can_edit_project_tasks($project_id)) {
                $can_edit_tasks = false;
            }
        }

        $options = array(
            "status_ids" => $status,
            "show_assigned_tasks_only_user_id" => $this->show_assigned_tasks_only_user_id(),
            "milestone_id" => $milestone_id,
            "assigned_to" => $user_id,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("tasks", $this->login_user->is_admin, $this->login_user->user_type)
        );

        if (!$status) {
            $options["exclude_status"] = 3; //don't show completed tasks by default
        }

        $options["project_id"] = $project_id;

        if ($this->login_user->user_type == "staff" && !$this->can_manage_all_projects()) {
            $options["user_id"] = $this->login_user->id;
        }

        if ($this->login_user->user_type == "client") {
            if (!$project_id) {
                app_redirect("forbidden");
            }
            if (!$this->can_view_tasks("project", $project_id)) {
                app_redirect("forbidden");
            }
        }


        $gantt_data = $this->Projects_model->get_gantt_data($options);
        $now = get_current_utc_time("Y-m-d");

        $tasks_array = array();
        $group_array = array();
        $group_key_array = array();
        $blocking_tasks_array = array();
        $parent_tasks_array = array();

        foreach ($gantt_data as $data) {

            $start_date = is_date_exists($data->start_date) ? $data->start_date : $now;
            $end_date = is_date_exists($data->end_date) ? $data->end_date : add_period_to_date($start_date, 1, "days");

            $group_id = 0;
            $group_name = "";
            $task_type_class = "";

            if ($group_by === "milestones") {
                $group_id = $data->milestone_id;
                $group_name = $data->milestone_title;
            } else if ($group_by === "members") {
                $group_id = $data->assigned_to;
                $group_name = $data->assigned_to_name;
            } else if ($group_by === "projects") {
                $group_id = $data->project_id;
                $group_name = $data->project_name;
            }

            //prepare final group credentials
            $group_id = $group_by . "-" . $group_id;
            if (!$group_name) {
                $group_name = app_lang("not_specified");
            }

            $color = $data->status_color;

            //has deadline? change the color of date based on status
            if ($data->status_id == "1" && is_date_exists($data->end_date) && get_my_local_time("Y-m-d") > $data->end_date) {
                $color = "#d9534f";
            }

            if ($end_date < $start_date) {
                $end_date = add_period_to_date($start_date, 1, "days");
            }

            //don't add any tasks if more than 5 years before of after
            if ($this->invalid_date_of_gantt($start_date, $end_date)) {
                continue;
            }

            if (!in_array($group_id, array_column($group_array, "id"))) {
                $task_type_class = "gt-group-task";
                //it's a group and not added, add it first
                $gantt_array_data = array(
                    "id" => $group_id,
                    "name" => $group_name,
                    "start" => $start_date,
                    "end" => $end_date,
                    "draggable" => false, //disable group dragging
                    "custom_class" => "no-drag",
                    "progress" => 0, //we've to add this to prevent error
                    "arrow_class" => "parent-group-arrow $task_type_class"
                );

                //add group seperately 
                $group_array[] = $gantt_array_data;
                $group_key_array[$group_id] = array("start_date" => $start_date, "end_date" => $end_date);
            }




            if ($group_key_array[$group_id]["start_date"] > $start_date) {
                $group_key_array[$group_id]["start_date"] = $start_date;
            }

            if ($group_key_array[$group_id]["end_date"] < $end_date) {
                $group_key_array[$group_id]["end_date"] = $end_date;
            }





            $dependencies = $group_id;
            $arrow_class = "regular-arrow";
            $task_type_class = "";

            //link parent task
            if ($data->parent_task_id) {
                $dependencies .= ", " . $data->parent_task_id;

                $arrow_class = "parent-task-arrow";
                $task_type_class = "gt-child-task";

                if (!get_array_value($parent_tasks_array, $data->parent_task_id)) {
                    //prepare parent tasks array
                    $parent_tasks_array[$data->parent_task_id] = array("start_date" => $start_date, "end_date" => $end_date, "sub_tasks" => array());
                }
                if ($parent_tasks_array[$data->parent_task_id]["start_date"] > $start_date) {
                    $parent_tasks_array[$data->parent_task_id]["start_date"] = $start_date;
                }
                if ($parent_tasks_array[$data->parent_task_id]["end_date"] < $end_date) {
                    $parent_tasks_array[$data->parent_task_id]["end_date"] = $end_date;
                }
            }


            if ($data->blocked_by) {
                $dependencies .= ", " . $data->blocked_by;
                $arrow_class = "blocked-task-arrow";
                $task_type_class = "gt-blocked-task";
            }


            if ($data->blocking) {
                $blocking_ids = explode(",", $data->blocking);

                foreach ($blocking_ids as $id) {
                    if (!get_array_value($blocking_tasks_array, $id)) {
                        $blocking_tasks_array[$id] = array();
                    }
                    $blocking_tasks_array[$id][] = $data->task_id;
                }
            }




            //add task data under a group
            $gantt_array_data = array(
                "id" => $data->task_id,
                "name" => $data->task_title,
                "start" => $start_date,
                "end" => $end_date,
                "bg_color" => $color,
                "progress" => 0, //we've to add this to prevent error
                "dependencies" => $dependencies,
                "draggable" => $can_edit_tasks ? true : false, //disable dragging for non-permitted users
                "arrow_class" => $arrow_class . " " . $task_type_class,
                "parent_task_id" => $data->parent_task_id
            );

            if ($data->parent_task_id) {
                $gantt_array_data["is_child_task"] = true;
                $parent_tasks_array[$data->parent_task_id]["sub_tasks"][] = $gantt_array_data;
            } else {
                $tasks_array[$group_id][] = $gantt_array_data;
            }
        }


        foreach ($group_array as $index => $group) {
            $group_id = $group["id"];
            $group_array[$index]["start"] = $group_key_array[$group_id]["start_date"];
            $group_array[$index]["end"] = $group_key_array[$group_id]["end_date"];
        }



        $gantt = array();

        //prepare final gantt data
        foreach ($tasks_array as $key => $tasks) {
            //add group first


            $gantt[] = get_array_value($group_array, array_search($key, array_column($group_array, "id")));

            //add tasks
            foreach ($tasks as $task) {
                $task_id = $task["id"];

                $parent_task_id = 0;
                //check parent task
                if (get_array_value($parent_tasks_array, $task_id)) {
                    //prepare parent task
                    $parent_task_id = $task_id;
                    $task["start"] = $parent_tasks_array[$task_id]["start_date"];
                    $task["end"] = $task["end"] < $parent_tasks_array[$task_id]["end_date"] ? $parent_tasks_array[$task_id]["end_date"] : $task["end"];
                    $task["draggable"] = false;
                    $task["custom_class"] = "no-drag";
                    $task["group_task"] = true;
                    $task["progress"] = 0;
                    $task["parent_task"] = 1;
                    $task["arrow_class"] = "gt-parent-task";
                }

                $blocking_tasks = get_array_value($blocking_tasks_array, $task_id);
                if ($blocking_tasks) {

                    foreach ($blocking_tasks as $blockign_task_id) {
                        $task["dependencies"] .= ", " . $blockign_task_id;
                    }

                    $task["arrow_class"] = "gt-blocked-task";
                }

                $gantt[] = $task;
                if ($parent_task_id) {
                    foreach ($parent_tasks_array[$parent_task_id]["sub_tasks"] as $subtask) {

                        $blocking_tasks = get_array_value($blocking_tasks_array, $subtask["id"]);

                        if ($blocking_tasks) {

                            foreach ($blocking_tasks as $blockign_task_id) {
                                $subtask["dependencies"] .= ", " . $blockign_task_id;
                                $subtask["arrow_class"] = $subtask["arrow_class"] . " gt-blocked-task";
                            }
                        }

                        $gantt[] = $subtask;
                    }
                }
            }
        }
        $view_data["gantt_data"] = json_encode($gantt);

        return $this->template->view("projects/gantt/chart", $view_data);
    }

    private function invalid_date_of_gantt($start_date, $end_date) {
        $start_year = explode('-', $start_date);
        $start_year = get_array_value($start_year, 0);

        $end_year = explode('-', $end_date);
        $end_year = get_array_value($end_year, 0);

        $current_year = get_today_date();
        $current_year = explode('-', $current_year);
        $current_year = get_array_value($current_year, 0);

        if (($current_year - $start_year) > 5 || ($start_year - $current_year) > 5 || ($current_year - $end_year) > 5 || ($end_year - $current_year) > 5) {
            return true;
        }
    }

    /* get list of milestones for filter */

    function get_milestones_for_filter() {

        $this->access_only_team_members();
        $project_id = $this->request->getPost("project_id");
        if ($project_id) {
            echo $this->_get_milestones_dropdown_list($project_id);
        }
    }

    /* batch update modal form */

    function batch_update_modal_form() {
        $this->access_only_team_members();
        $project_id = $this->request->getPost("project_id");
        $task_ids = $this->request->getPost("ids");

        validate_numeric_value($project_id);

        if ($task_ids && $project_id) {
            $view_data = $this->_get_task_related_dropdowns("project", $project_id, true);
            $view_data["task_ids"] = clean_data($task_ids);
            $view_data["project_id"] = $project_id;

            return $this->template->view("tasks/batch_update/modal_form", $view_data);
        } else {
            show_404();
        }
    }

    /* save batch tasks */

    function save_batch_update() {
        $this->access_only_team_members();

        $batch_fields = $this->request->getPost("batch_fields");
        if (!$batch_fields) {
            echo json_encode(array('success' => false, 'message' => app_lang('no_field_has_selected')));
            exit();
        }

        $allowed_fields = array("milestone_id", "assigned_to", "collaborators", "status_id", "priority_id", "labels", "start_date", "deadline");

        $post_fields = explode('-', $batch_fields);

        $data = array();
        foreach ($post_fields as $field) {
            if (in_array($field, $allowed_fields)) {

                $value = $this->request->getPost($field);
                $data[$field] = $value;

                if (($field == "start_date" || $field == "deadline") && !$data[$field]) {
                    $data[$field] = "SET_NULL";
                }

                if ($field == "labels" || $field == "collaborators") {
                    validate_list_of_numbers($value);
                }
            }
        }

        $data = clean_data($data);

        //set null value after cleaning the data
        if (get_array_value($data, "start_date") == "SET_NULL") {
            $data["start_date"] = NULL;
        }

        if (get_array_value($data, "deadline") == "SET_NULL") {
            $data["deadline"] = NULL;
        }

        $task_ids = $this->request->getPost("task_ids");
        validate_list_of_numbers($task_ids);
        if (!$task_ids) {
            echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
            exit();
        }

        $tasks_ids_array = explode('-', $task_ids);
        $now = get_current_utc_time();

        foreach ($tasks_ids_array as $id) {
            unset($data["activity_log_id"]);
            unset($data["status_changed_at"]);

            //check user's permission on this task's project
            $task_info = $this->Tasks_model->get_one($id);
            if (!$this->can_edit_tasks($task_info)) {
                app_redirect("forbidden");
            }

            if (array_key_exists("status_id", $data) && $task_info->status_id !== get_array_value($data, "status_id")) {
                $data["status_changed_at"] = $now;
            }

            $data = clean_data($data);
            $save_id = $this->Tasks_model->ci_save($data, $id);

            if ($save_id) {
                //we don't send notification if the task is changing on the same position
                $activity_log_id = get_array_value($data, "activity_log_id");
                if ($activity_log_id) {
                    $this->_send_task_updated_notification($task_info, $activity_log_id);
                }
            }
        }

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    function get_checklist_group_suggestion() {
        $task_id = $this->request->getPost("task_id");
        validate_numeric_value($task_id);
        $task_info = $this->Tasks_model->get_one($task_id);
        if (!$this->can_edit_tasks($task_info)) {
            app_redirect("forbidden");
        }


        $key = $this->request->getPost("q");
        $suggestion = array();

        $items = $this->Checklist_groups_model->get_group_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->id, "text" => $item->title);
        }

        echo json_encode($suggestion);
    }

    //prepare suggestion of checklist template
    function get_checklist_template_suggestion() {
        $task_id = $this->request->getPost("task_id");
        validate_numeric_value($task_id);
        $task_info = $this->Tasks_model->get_one($task_id);
        if (!$this->can_edit_tasks($task_info)) {
            app_redirect("forbidden");
        }

        $key = $this->request->getPost("q");
        $suggestion = array();

        $items = $this->Checklist_template_model->get_template_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        echo json_encode($suggestion);
    }

    /* save task comments */

    function save_comment() {
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost('id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "project_comment");

        $project_id = $this->request->getPost('project_id');
        $task_id = $this->request->getPost('task_id');
        $description = $this->request->getPost('description');

        $data = array(
            "created_by" => $this->login_user->id,
            "created_at" => get_current_utc_time(),
            "project_id" => $project_id ? $project_id : 0,
            "file_id" => 0,
            "task_id" => $task_id,
            "customer_feedback_id" => 0,
            "comment_id" => 0,
            "description" => $description
        );

        $data = clean_data($data);

        $data["files"] = $files_data; //don't clean serilized data

        $save_id = $this->Project_comments_model->save_comment($data, $id);
        if ($save_id) {
            $response_data = "";
            $options = array("id" => $save_id, "login_user_id" => $this->login_user->id);

            if ($this->request->getPost("reload_list")) {
                $view_data['comments'] = $this->Project_comments_model->get_details($options)->getResult();
                $response_data = $this->template->view("projects/comments/comment_list", $view_data);
            }
            echo json_encode(array("success" => true, "data" => $response_data, 'message' => app_lang('comment_submited')));

            $comment_info = $this->Project_comments_model->get_one($save_id);
            $task_info = $this->Tasks_model->get_one($comment_info->task_id);

            $notification_options = array("task_id" => $comment_info->task_id, "project_comment_id" => $save_id);

            if ($comment_info->project_id) {
                $notification_options["project_id"] = $comment_info->project_id;
                log_notification("project_task_commented", $notification_options);
            } else {
                if ($task_info->context !== "general") {
                    $context_id_key = $task_info->context . "_id";
                    $context_id_value = $task_info->{$task_info->context . "_id"};

                    $notification_options["$context_id_key"] = $context_id_value;
                }

                log_notification("general_task_commented", $notification_options);
            }
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* download task files by zip */

    function download_comment_files($id) {
        validate_numeric_value($id);

        $info = $this->Project_comments_model->get_one($id);
        $task_info = $this->Tasks_model->get_one($info->task_id);

        if (!$this->can_view_tasks("", 0, $task_info)) {
            app_redirect("forbidden");
        }

        return $this->download_app_files(get_setting("timeline_file_path"), $info->files);
    }

    function get_task_labels_dropdown_for_filter() {
        $labels_dropdown = array(array("id" => "", "text" => "- " . app_lang("label") . " -"));

        $options = array(
            "context" => "task"
        );

        $labels = $this->Labels_model->get_details($options)->getResult();
        foreach ($labels as $label) {
            $labels_dropdown[] = array("id" => $label->id, "text" => $label->title);
        }

        return $labels_dropdown;
    }

    /* get member suggestion with start typing '@' */

    function get_member_suggestion_to_mention() {
        $options = array("status" => "active", "user_type" => "staff");
        if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
            $options["id"] = $this->login_user->id;
        }

        $members = $this->Users_model->get_details($options)->getResult();
        $members_dropdown = array();
        foreach ($members as $member) {
            $members_dropdown[] = array("name" => $member->first_name . " " . $member->last_name, "content" => "@[" . $member->first_name . " " . $member->last_name . " :" . $member->id . "]");
        }

        if ($members_dropdown) {
            echo json_encode(array("success" => TRUE, "data" => $members_dropdown));
        } else {
            echo json_encode(array("success" => FALSE));
        }
    }

    private function _get_task_statuses_dropdown($project_id = 0) {
        $exclude_status_ids = $this->get_removed_task_status_ids($project_id);
        $task_status_options = array("exclude_status_ids" => $exclude_status_ids);
        if (!$project_id) {
            $task_status_options["hide_from_non_project_related_tasks"] = 0;
        }

        $statuses = $this->Task_status_model->get_details($task_status_options)->getResult();

        $status_dropdown = array();

        foreach ($statuses as $status) {
            $status_dropdown[] = array("id" => $status->id, "value" => $status->id, "text" => ($status->key_name ? app_lang($status->key_name) : $status->title));
        }

        return json_encode($status_dropdown);
    }

    function get_task_statuses_dropdown($project_id = 0) {
        validate_numeric_value($project_id);
        echo $this->_get_task_statuses_dropdown($project_id);
    }

    private function _validate_excel_import_access() {
        return $this->can_create_tasks("project");
    }

    private function _get_controller_slag() {
        return "tasks";
    }

    private function _get_custom_field_context() {
        return "tasks";
    }

    private function _get_headers_for_import() {
        $this->_init_required_data_before_starting_import();

        return array(
            array("name" => "title", "required" => true, "required_message" => sprintf(app_lang("import_error_field_required"), app_lang("title"))),
            array("name" => "description"),
            array("name" => "project", "custom_validation" => function ($project) {
                //check project name is exist or not, if not then show error
                if ($project) {
                    $project_id = get_array_value($this->projects_id_by_title, strtolower(trim($project)));
                    if (!$project_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("project")));
                    }
                } else {
                    return array("error" => sprintf(app_lang("import_error_field_required"), app_lang("project")));
                }
            }),
            array("name" => "points", "custom_validation" => function ($points) {
                //check task point is valid or not
                if ($points && get_setting("task_point_range") >= $points) {
                    return true;
                } else {
                    return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("points")));
                }
            }),
            array("name" => "milestone", "custom_validation" => function ($milestone) {
                //check milestone is exist or not, if not then show error
                if ($milestone) {
                    $milestone_id = get_array_value($this->milestones_id_by_title, strtolower(trim($milestone)));
                    if (!$milestone_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("milestone")));
                    }
                }
            }),
            array("name" => "assigned_to", "custom_validation" => function ($assigned_to) {
                //check the user is exist or not
                if ($assigned_to) {
                    $user_id = get_array_value($this->users_id_by_name, trim($assigned_to));
                    if (!$user_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("user")));
                    }
                }
            }),
            array("name" => "collaborators", "custom_validation" => function ($collaborators) {
                //check the users is exist or not
                if ($collaborators) {
                    $task_collaborators = $this->_get_collaborators_ids($collaborators);
                    if (!$task_collaborators) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("user")));
                    }
                }
            }),
            array("name" => "status", "custom_validation" => function ($status) {
                //check status is exist or not, if not then show error
                if ($status) {
                    $status_id = get_array_value($this->task_statuses_id_by_title, strtolower(trim($status)));
                    if (!$status_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("status")));
                    }
                } else {
                    return array("error" => sprintf(app_lang("import_error_field_required"), app_lang("status")));
                }
            }),
            array("name" => "priority", "custom_validation" => function ($priority) {
                //check the priority is exist or not using the title, if not then show error
                if ($priority) {
                    $priority_id = get_array_value($this->task_priorities_id_by_title, strtolower(trim($priority)));
                    if (!$priority_id) {
                        return array("error" => sprintf(app_lang("import_not_exists_error_message"), app_lang("priority")));
                    }
                }
            }),
            array("name" => "labels"),
            array("name" => "start_date"),
            array("name" => "deadline")
        );
    }

    function download_sample_excel_file() {
        $this->can_create_tasks("project");
        return $this->download_app_files(get_setting("system_file_path"), serialize(array(array("file_name" => "import-tasks-sample.xlsx"))));
    }

    private function _init_required_data_before_starting_import() {

        $projects = $this->Projects_model->get_projects_id_and_name()->getResult();
        $projects_id_by_title = array();
        foreach ($projects as $project) {
            $projects_id_by_title[strtolower($project->title)] = $project->id;
        }

        $milestones = $this->Milestones_model->get_details()->getResult();
        $milestones_id_by_title = array();
        foreach ($milestones as $milestone) {
            $milestones_id_by_title[strtolower($milestone->title)] = $milestone->id;
        }

        $users = $this->Users_model->get_team_members_id_and_name()->getResult();
        $users_id_by_name = array();
        foreach ($users as $user) {
            $users_id_by_name[$user->user_name] = $user->id;
        }

        $task_statuses = $this->Task_status_model->get_details()->getResult();
        $task_statuses_id_by_title = array();
        foreach ($task_statuses as $status) {
            $task_statuses_id_by_title[strtolower(trim($status->title))] = $status->id;
        }

        $priorities = $this->Task_priority_model->get_details()->getResult();
        $task_priorities_id_by_title = array();
        foreach ($priorities as $priority) {
            $task_priorities_id_by_title[strtolower(trim($priority->title))] = $priority->id;
        }

        $task_labels = $this->Labels_model->get_details(array("context" => "task"))->getResult();
        $task_labels_id_by_title = array();
        foreach ($task_labels as $label) {
            $task_labels_id_by_title[$label->title] = $label->id;
        }

        $this->projects_id_by_title = $projects_id_by_title;
        $this->milestones_id_by_title = $milestones_id_by_title;
        $this->users_id_by_name = $users_id_by_name;
        $this->task_statuses_id_by_title = $task_statuses_id_by_title;
        $this->task_priorities_id_by_title = $task_priorities_id_by_title;
        $this->task_labels_id_by_title = $task_labels_id_by_title;
    }

    private function _save_a_row_of_excel_data($row_data) {
        $now = get_current_utc_time();
        $sort = 100; //random value

        $task_data_array = $this->_prepare_task_data($row_data);
        $task_data = get_array_value($task_data_array, "task_data");
        $custom_field_values_array = get_array_value($task_data_array, "custom_field_values_array");

        //couldn't prepare valid data
        if (!($task_data && count($task_data) > 1)) {
            return false;
        }

        //found information about task, add some additional info
        $task_data["created_date"] = $now;
        $task_data["sort"] = $sort;
        $task_data["context"] = "project";

        //save task data
        $saved_id = $this->Tasks_model->ci_save($task_data);
        if (!$saved_id) {
            return false;
        }

        //save custom fields
        $this->_save_custom_fields($saved_id, $custom_field_values_array);
        return true;
    }

    private function _prepare_task_data($row_data) {

        $task_data = array("created_by" => $this->login_user->id);
        $custom_field_values_array = array();

        foreach ($row_data as $column_index => $value) {
            if (!$value) {
                continue;
            }

            $column_name = $this->_get_column_name($column_index);
            if ($column_name == "project") {
                //get existing project

                $project_id = get_array_value($this->projects_id_by_title, strtolower(trim($value)));
                if ($project_id) {
                    $task_data["project_id"] = $project_id;
                }
            } else if ($column_name == "milestone") {
                //get existing milestone

                $milestone_id = get_array_value($this->milestones_id_by_title, strtolower(trim($value)));
                if ($milestone_id) {
                    $task_data["milestone_id"] = $milestone_id;
                }
            } else if ($column_name == "assigned_to") {
                //get existing user for assigned to

                $user_id = get_array_value($this->users_id_by_name, trim($value));
                if ($user_id) {
                    $task_data["assigned_to"] = $user_id;
                }
            } else if ($column_name == "collaborators") {
                $task_data["collaborators"] = $this->_get_collaborators_ids($value);
            } else if ($column_name == "status") {
                //get existing status

                $status_id = get_array_value($this->task_statuses_id_by_title, strtolower(trim($value)));
                if ($status_id) {
                    $task_data["status_id"] = $status_id;
                }
            } else if ($column_name == "priority") {
                //get existing priority

                $priority_id = get_array_value($this->task_priorities_id_by_title, strtolower(trim($value)));
                if ($priority_id) {
                    $task_data["priority_id"] = $priority_id;
                }
            } else if ($column_name == "labels") {
                if ($value) {
                    $labels = "";
                    $labels_array = explode(",", $value);
                    foreach ($labels_array as $label) {
                        $label_id = get_array_value($this->task_labels_id_by_title, trim($label));

                        if ($labels) {
                            $labels .= ",";
                        }

                        if ($label_id) {
                            $labels .= $label_id;
                        } else {
                            $label_data = array("title" => trim($label), "context" => "task", "color" => "#4A89F4");
                            $saved_label_id = $this->Labels_model->ci_save($label_data);
                            $labels .= $saved_label_id;
                            $this->task_labels_id_by_title[$value] = $saved_label_id;
                        }
                    }
                    $task_data["labels"] = $labels;
                }
            } else if ($column_name == "start_date") {
                $task_data["start_date"] = $this->_check_valid_date($value);
            } else if ($column_name == "deadline") {
                $task_data["deadline"] = $this->_check_valid_date($value);
            } else if (strpos($column_name, 'cf') !== false) {
                $this->_prepare_custom_field_values_array($column_name, $value, $custom_field_values_array);
            } else {
                $task_data[$column_name] = $value;
            }
        }

        return array(
            "task_data" => $task_data,
            "custom_field_values_array" => $custom_field_values_array
        );
    }


    private function _prepare_common_gantt_filters($project_id = 0) {
        $view_data['milestone_dropdown'] = $this->_get_milestones_dropdown_list($project_id);
        $view_data["show_milestone_info"] = $this->can_view_milestones();

        $view_data['show_project_members_dropdown'] = true;
        if ($this->login_user->user_type == "client") {
            $view_data['show_project_members_dropdown'] = false;
        }

        $group_by_dropdown = array();

        if ($view_data['show_project_members_dropdown']) {
            $milestones_and_members_group_by = array(
                array("id" => "", "text" => "- " . app_lang("group_by") . " -"),
                array("id" => "milestones", "text" => app_lang("milestones")),
                array("id" => "members", "text" => app_lang("team_members"))
            );

            $project_group_by = array();
            if (!$project_id) {
                $project_group_by = array(array("id" => "projects", "text" => app_lang("projects")));
            }

            $gantt_group_by = array_merge($milestones_and_members_group_by, $project_group_by);

            $group_by_dropdown = $gantt_group_by;
        }

        $view_data["group_by_dropdown"] = json_encode($group_by_dropdown);

        //only admin/ the user has permission to manage all projects, can see all projects, other team mebers can see only their own projects.
        $options = array();
        if (!$this->can_manage_all_projects()) {
            $options["user_id"] = $this->login_user->id;
        }

        $projects = $this->Projects_model->get_details($options)->getResult();

        // Get projects dropdown
        $projects_dropdown = array(array("id" => "", "text" => "- " . app_lang("project") . " -"));
        foreach ($projects as $project) {
            $projects_dropdown[] = array("id" => $project->id, "text" => $project->title);
        }
        $view_data['projects_dropdown'] = json_encode($projects_dropdown);

        if ($project_id) {
            $view_data['project_members_dropdown'] = $this->_get_project_members_dropdown_list($project_id);
        } else {
            $options = array("user_type" => "staff");
            if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
                $options["id"] = $this->login_user->id;
            }

            $team_members_dropdown = $this->Users_model->get_id_and_text_dropdown(
                array("first_name", "last_name"),
                $options,
                "- " . app_lang("assigned_to") . " -",
            );

            $view_data['project_members_dropdown'] = json_encode($team_members_dropdown);
        }

        $view_data['show_project_members_dropdown'] = true;
        if ($this->login_user->user_type == "client") {
            $view_data['show_project_members_dropdown'] = false;
        }

        return $view_data;
    }

    /* delete selected tasks */

    function delete_selected_tasks() {
        $this->access_only_team_members();
        $task_ids = $this->request->getPost("ids");

        if ($task_ids) {
            $task_ids_array = explode('-', $task_ids);

            foreach ($task_ids_array as $id) {
                validate_numeric_value($id);
                $task_info = $this->Tasks_model->get_one($id);
                if (!$this->can_delete_tasks($task_info)) {
                    app_redirect("forbidden");
                }

                if ($this->Tasks_model->delete_task_and_sub_items($id)) {
                    $is_success = true;
                } else {
                    $is_success = false;
                }
            }

            if ($is_success) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        } else {
            show_404();
        }
    }

    function get_global_search_suggestion() {
        $this->access_only_team_members();
        $search = $this->request->getPost("search");

        if ($search) {
            $options = array();
            $result = array();

            //prepare accessible query parameters
            $contexts = $this->_get_accessible_contexts("view");
            $options = array_merge($options, $this->_prepare_query_parameters_for_accessible_contexts($contexts));

            $result = $this->Tasks_model->get_search_suggestion($search, $options)->getResult();

            $result_array = array();
            foreach ($result as $value) {
                $result_array[] = array("value" => $value->id, "label" => app_lang("task") . " $value->id: " . $value->title);
            }

            echo json_encode($result_array);
        }
    }
}
