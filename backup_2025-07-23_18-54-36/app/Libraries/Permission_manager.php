<?php

namespace App\Libraries;

class Permission_manager {

    private $ci = null;
    private $permissions = array();

    public function __construct($security_controller_instance) {
        $this->ci = $security_controller_instance;
        if (!$this->ci || !$this->login_user_id()) {
            return false;
        }

        $this->permissions = $this->ci->login_user->permissions ? $this->ci->login_user->permissions: array();
    }

    function is_admin() {
        return $this->ci->login_user->is_admin;
    }

    function is_team_member() {
        return $this->ci->login_user->user_type == "staff";
    }

    function is_client() {
        return $this->ci->login_user->user_type == "client";
    }

    function login_user_id() {
        return isset($this->ci->login_user->id) ? $this->ci->login_user->id : null;
    }

    function is_active_module($module_name) {
        return get_setting($module_name) == "1";
    }

    function can_manage_invoices() {

        if (!$this->is_active_module("module_invoice")) {
            return false;
        }

        if ($this->is_admin()) {
            return true;
        }

        if ($this->is_team_member()) {
            $invoice_permission = get_array_value($this->permissions, "invoice");

            return in_array($invoice_permission, [
                "all",
                "manage_own_client_invoices",
                "manage_own_client_invoices_except_delete",
                "manage_only_own_created_invoices",
                "manage_only_own_created_invoices_except_delete"
            ]);
        }
    }

    function can_manage_estimates() {
        if (!$this->is_active_module("module_estimate")) {
            return false;
        }

        if ($this->is_admin()) {
            return true;
        }

        if ($this->is_team_member()) {
            $estimate_permission = get_array_value($this->permissions, "estimate");

            return in_array($estimate_permission, [
                "all",
                "own"
            ]);
        }
    }

    function can_manage_items() {
        return $this->can_manage_invoices() || $this->can_manage_estimates();
    }

    function can_manage_clients($client_id = 0) {

        if ($this->is_admin()) {
            return true;
        }

        if ($this->is_team_member()) {
            $client_permission = get_array_value($this->permissions, "client");

            // can manager all clients. Id wise permission is not required
            if ($client_permission == "all") {
                return true;
            }

            if ($client_id && ($client_permission == "own" || $client_permission == "specific")) {
                if (!is_numeric($client_id)) {
                    return false; // invalid client id
                }

                $client_info = $this->ci->Clients_model->get_one($client_id);
                if (!$client_info) {
                    return false; // client not found
                }

                //can manage own
                if ($client_permission == "own" && ($client_info->created_by == $this->login_user_id() || $client_info->owner_id == $this->login_user_id())) {
                    return true;
                }

                //can manage specific client groups
                $client_specific_permission = get_array_value($this->permissions, "client_specific");
                if ($client_permission == "specific" && $client_specific_permission && $client_info->group_ids) {
                    if (array_intersect(explode(',', $client_specific_permission), explode(',', $client_info->group_ids))) {
                        return true;
                    }
                }
            }

            //since the client id is not provided and has some permissions, this can be allowed for client insert
            if (!$client_id && ($client_permission == "own" || $client_permission == "specific")) {
                return true;
            }
        }

        if ($this->is_client() && $this->ci->login_user->client_id == $client_id) {
            return true;
        }
    }

    function can_view_clients($client_id = 0) {
        if ($this->is_team_member()) {
            //if team member has readonly access,  then can view client
            $client_permission = get_array_value($this->permissions, "client");
            if ($client_permission == "read_only") {
                return true;
            }
        }

        // if the user has client manage permission, then can view client
        return $this->can_manage_clients($client_id);
    }

    function get_allowed_client_group_ids_array() {

        if ($this->is_team_member()) {
            $client_permission = get_array_value($this->permissions, "client");

            if ($client_permission == "specific") {
                $client_specific = get_array_value($this->permissions, "client_specific");
                if ($client_specific) {
                    return explode(',', $client_specific);
                }
            }
        }
    }

    function get_own_clients_only_user_id() {
        if ($this->is_team_member()) {
            $client_permission = get_array_value($this->permissions, "client");
            if ($client_permission == "own") {
                return $this->login_user_id();
            }
        }
    }

    function get_own_leads_only_user_id() {
        if ($this->is_team_member()) {
            $client_permission = get_array_value($this->permissions, "lead");
            if ($client_permission == "own") {
                return $this->login_user_id();
            }
        }
    }

    function can_manage_leads($lead_id = 0) {
        if ($this->is_admin()) {
            return true;
        }

        if ($this->is_team_member()) {
            $lead_permission = get_array_value($this->permissions, "lead");
            if ($lead_permission == "all") {
                return true;
            }

            if ($lead_id && $lead_permission == "own") {
                if (!is_numeric($lead_id)) {
                    return false; // invalid lead id
                }

                $lead_info = $this->ci->Clients_model->get_one($lead_id);
                if (!$lead_info) {
                    return false; // lead not found
                }

                if ($lead_info->id && $lead_info->owner_id == $this->login_user_id()) {
                    return true;
                }
            }

            //since the lead id is not provided and has some permissions, this can be allowed for lead insert
            if (!$lead_id && $lead_permission == "own") {
                return true;
            }
        }
    }

    function can_view_leads($lead_id = 0) {
        // if the user has lead manage permission, then can view lead        
        return $this->can_manage_leads($lead_id);
    }
}
