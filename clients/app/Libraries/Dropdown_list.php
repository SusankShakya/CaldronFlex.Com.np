<?php

namespace App\Libraries;

use App\Libraries\Permission_manager;

class Dropdown_list {

    private $ci;
    private $permission_manager;
    private $max_dropdown_items = 5000;

    public function __construct($security_controller_instance) {
        $this->ci = $security_controller_instance;
        $this->permission_manager = new Permission_manager($security_controller_instance);

        if (get_setting("max_dropdown_items")) {
            $this->max_dropdown_items = get_setting("max_dropdown_items");
        }
    }

    private function _get_clients_dropdown_data($search = "", $id = 0) {

        $can_view_clients = $this->permission_manager->can_view_clients();

        if ($can_view_clients) {
            $options = array();
            $options["owner_id_or_created_by"] = $this->permission_manager->get_own_clients_only_user_id();
            $options["client_groups"] = $this->permission_manager->get_allowed_client_group_ids_array();
            $options["search"] = $search;
            $options["only_clients"] = true;
            $options["id"] = get_only_numeric_value($id);
            if ($search) {
                $options["limit"] = 10;
            }

            return $this->ci->Clients_model->get_dropdown_suggestions($options);
        }
    }

    private function _get_leads_dropdown_data($search = "", $id = 0) {

        $can_view_leads = $this->permission_manager->can_view_leads();

        if ($can_view_leads) {
            $options = array();
            $options["owner_id_or_created_by"] = $this->permission_manager->get_own_leads_only_user_id();
            $options["search"] = $search;
            $options["only_leads"] = true;
            $options["id"] = get_only_numeric_value($id);
            if ($search) {
                $options["limit"] = 10;
            }
            return $this->ci->Clients_model->get_dropdown_suggestions($options);
        }
    }

    private function _prepare_dropdownd_data($options = array(), $json_encode = true) {
        $search = get_array_value($options, "search");
        $blank_option_text = get_array_value($options, "blank_option_text");
        $dropdown_data = get_array_value($options, "dropdown_data");
        $additonal_dropdowns = get_array_value($options, "additonal_dropdowns");
        $source_url = get_array_value($options, "source_url");
        $id = get_only_numeric_value(get_array_value($options, "id"));

        if (!$dropdown_data && !$additonal_dropdowns) {
            if ($json_encode) {
                return json_encode(array());
            }
            return array();
        }

        $dropdown = array();

        if ($blank_option_text) {
            $dropdown[] = array("id" => "", "text" => $blank_option_text);
        }

        $total_items_found = get_array_value($dropdown_data, "total_items_found") ? get_array_value($dropdown_data, "total_items_found") : 0;

        if ($additonal_dropdowns && is_array($additonal_dropdowns)) {
            foreach ($additonal_dropdowns as $additonal_dropdown) {
                $total_items_found += get_array_value($additonal_dropdown, "total_items_found") ? get_array_value($additonal_dropdown, "total_items_found") : 0;
            }
        }

        $max_dropdown_items_reached = false;
        if (!$search && $total_items_found > $this->max_dropdown_items) {
            $max_dropdown_items_reached = true;
        }


        if (!$max_dropdown_items_reached || $id) { //when searched by id, we need to show one item (specially on edit modal)
            $dropdown_data = get_array_value($dropdown_data, "data");
            if ($dropdown_data && is_array($dropdown_data)) {
                foreach ($dropdown_data as $item) {
                    $dropdown[] = array("id" => $item->id, "text" => $item->title);
                }
            }


            //sometimes we need to show the dropdown option for different types of data
            //check if there is any aditional dropdown dataset, if so, append the data set with existing data

            if ($additonal_dropdowns && is_array($additonal_dropdowns)) {

                foreach ($additonal_dropdowns as $additonal_dropdown) {
                    $additonal_dropdown_data = get_array_value($additonal_dropdown, "data");

                    if ($additonal_dropdown_data && is_array($additonal_dropdown_data)) {
                        $additonal_dropdown_text_prefix = get_array_value($additonal_dropdown, "text_prefix") ? get_array_value($additonal_dropdown, "text_prefix") : "";

                        foreach ($additonal_dropdown_data as $item) {
                            $dropdown[] = array("id" => $item->id, "text" => $additonal_dropdown_text_prefix . $item->title);
                        }
                    }
                }
            }
        }

        //inject MAX_DROPDOWN_ITEMS_REACHED options
        if (!$search && $max_dropdown_items_reached) {
            if (!get_array_value($dropdown, 0)) {
                $dropdown[] = array("id" => "", "text" => "");
            }

            $dropdown[0]["dropdown_type"] = "MAX_DROPDOWN_ITEMS_REACHED";
            $dropdown[0]["source_url"] = $source_url;
            $dropdown[0]["blank_option_text"] = $blank_option_text;
        }

        if ($json_encode) {
            return json_encode($dropdown);
        } else {
            return $dropdown;
        }
    }

    public function get_clients_and_leads_id_and_text_dropdown($options = array(), $json_encode = true) {

        $search = get_array_value($options, "search");
        $id = get_only_numeric_value(get_array_value($options, "id"));

        $options["dropdown_data"] = $this->_get_clients_dropdown_data($search, $id);

        $leads_dropdown_data = $this->_get_leads_dropdown_data($search, $id);

        if ($leads_dropdown_data) {
            $options["additonal_dropdowns"] = array();

            $leads_dropdown_data["text_prefix"] = app_lang("lead") . ": ";
            $options["additonal_dropdowns"][] = $leads_dropdown_data;
        }

        if (!get_array_value($options, "source_url")) {
            $options["source_url"] = get_uri("clients/search_clients_and_leads_id_and_text_dropdown");
        }

        return $this->_prepare_dropdownd_data($options, $json_encode);
    }

    public function get_clients_id_and_text_dropdown($options = array(), $json_encode = true) {
        $search = get_array_value($options, "search");
        $id = get_only_numeric_value(get_array_value($options, "id"));

        $options["dropdown_data"] = $this->_get_clients_dropdown_data($search, $id);

        if (!get_array_value($options, "source_url")) {
            $options["source_url"] = get_uri("clients/search_clients_id_and_text_dropdown");
        }

        return $this->_prepare_dropdownd_data($options, $json_encode);
    }
}
