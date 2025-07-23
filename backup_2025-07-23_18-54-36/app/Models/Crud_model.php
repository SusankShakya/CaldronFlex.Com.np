<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

//extend from this model to execute basic db operations
class Crud_model extends Model {

    protected $table;
    protected $table_without_prefix;
    protected $db;
    protected $db_builder = null;
    private $log_activity = false;
    private $log_type = "";
    private $log_type_title_key = "";
    private $log_for = "";
    private $log_for_key = "";
    private $log_for2 = "";
    private $log_for_key2 = "";
    protected $allowedFields = array();
    private $Activity_logs_model;

    function __construct($table = null, $db = null) {
        $this->Activity_logs_model = model("App\Models\Activity_logs_model");
        $this->db = $db ? $db : db_connect('default');
        $this->db->query("SET sql_mode = ''");
        $this->use_table($table);
    }

    protected function use_table($table) {
        $db_prefix = $this->db->getPrefix();
        $this->table = $db_prefix . $table;
        $this->table_without_prefix = $table;
        $this->db_builder = $this->db->table($this->table);
    }

    protected function disable_log_activity() {
        $this->log_activity = false;
    }

    protected function init_activity_log($log_type = "", $log_type_title_key = "", $log_for = "", $log_for_key = 0, $log_for2 = "", $log_for_key2 = 0) {
        if ($log_type) {
            $this->log_activity = true;
            $this->log_type = $log_type;
            $this->log_type_title_key = $log_type_title_key;
            $this->log_for = $log_for;
            $this->log_for_key = $log_for_key;
            $this->log_for2 = $log_for2;
            $this->log_for_key2 = $log_for_key2;
        }
    }

    function get_one($id = 0) {
        return $this->get_one_where(array('id' => $id));
    }

    function get_one_where($where = array()) {
        $where = $this->_get_clean_value($where, "", false); //since query builder will take care of the values, don't escape them

        $result = $this->db_builder->getWhere($where, 1);

        if ($result->getRow()) {
            return $result->getRow();
        } else {
            $db_fields = $this->db->getFieldNames($this->table);
            $fields = new \stdClass();
            foreach ($db_fields as $field) {
                $fields->$field = "";
            }

            return $fields;
        }
    }

    function get_all($include_deleted = false) {
        $where = array("deleted" => 0);
        if ($include_deleted) {
            $where = array();
        }
        return $this->get_all_where($where);
    }

    function escape_array($values = array()) { //use _get_clean_value instead. It'll be removed.
        return $this->_get_clean_value($values);
    }

    function get_all_where($where = array(), $limit = 1000000, $offset = 0, $sort_by_field = null, $select_field_names = null) {

        $where = $this->_get_clean_value($where);

        if ($select_field_names) {
            $this->db_builder->select($select_field_names);
        }

        $where_in = get_array_value($where, "where_in");
        if ($where_in) {
            foreach ($where_in as $key => $value) {
                $this->db_builder->whereIn($key, $value);
            }
            unset($where["where_in"]);
        }

        if ($sort_by_field) {
            $this->db_builder->orderBy($sort_by_field, 'ASC');
        }

        return $this->db_builder->getWhere($where, $limit, $offset);
    }

    function ci_save(&$data = array(), $id = 0) {
        //allowed fields should be assigned
        $db_fields = $this->db->getFieldNames($this->table);
        foreach ($db_fields as $field) {
            if ($field !== "id") {
                array_push($this->allowedFields, $field);
            }
        }

        //unset custom created by field if it's defined for activity log
        $activity_log_created_by_app = false;
        if (get_array_value($data, "activity_log_created_by_app")) {
            $activity_log_created_by_app = true;
            unset($data["activity_log_created_by_app"]);
        }

        if ($id) {
            $id = $this->_get_clean_value($id);
            if (!$id) {
                return false; //invalid id
            }

            //update
            $where = array("id" => $id);

            //to log an activity we have to know the changes. now collect the data before update anything
            if ($this->log_activity) {
                $data_before_update = $this->get_one($id);
            }

            $success = $this->update_where($data, $where);
            if ($success) {
                if ($this->log_activity) {
                    //unset status_changed_at field for task update
                    if ($this->log_type === "task" && isset($data["status_changed_at"])) {
                        unset($data["status_changed_at"]);
                    }

                    //to log this activity, check the changes
                    $fields_changed = array();
                    foreach ($data as $field => $value) {
                        if ($data_before_update->$field != $value) {
                            $fields_changed[$field] = array("from" => $data_before_update->$field, "to" => $value);
                        }
                    }
                    //has changes? log the changes.
                    if (count($fields_changed)) {
                        $log_for_id = 0;
                        if ($this->log_for_key) {
                            $log_for_key = $this->log_for_key;
                            $log_for_id = $data_before_update->$log_for_key;
                        }

                        $log_for_id2 = 0;
                        if ($this->log_for_key2) {
                            $log_for_key2 = $this->log_for_key2;
                            $log_for_id2 = $data_before_update->$log_for_key2;
                        }

                        $log_type_title_key = $this->log_type_title_key;
                        $log_type_title = isset($data_before_update->$log_type_title_key) ? $data_before_update->$log_type_title_key : "";

                        if ($this->log_type === "task" && $data_before_update->context != "project") {
                            $log_for = "general_task";
                        } else {
                            $log_for = $this->log_for;
                        }

                        $log_data = array(
                            "action" => "updated",
                            "log_type" => $this->log_type,
                            "log_type_title" => $log_type_title,
                            "log_type_id" => $id,
                            "changes" => serialize($fields_changed),
                            "log_for" => $log_for,
                            "log_for_id" => $log_for_id,
                            "log_for2" => $this->log_for2,
                            "log_for_id2" => $log_for_id2,
                        );
                        $this->Activity_logs_model->ci_save($log_data, $activity_log_created_by_app);
                        $activity_log_id = $this->db->insertID();
                        $data["activity_log_id"] = $activity_log_id;
                    }
                }
            }

            try {
                app_hooks()->do_action("app_hook_data_update", array(
                    "id" => $id,
                    "table" => $this->table,
                    "table_without_prefix" => $this->table_without_prefix,
                    "data" => $data
                ));
            } catch (\Exception $ex) {
                log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
            }

            return $success;
        } else {
            //insert

            try {
                $data_from_hook = app_hooks()->apply_filters("app_filter_data_before_insert", array(
                    "table" => $this->table,
                    "table_without_prefix" => $this->table_without_prefix,
                    "data" => $data
                ));

                // if there is no hook is triggering, we'll get the same sent data
                $data = get_array_value($data_from_hook, "data");

                // the data could be modified from the hook or if it's undefined we have to assume that this data shouldn't be saved
                if (!$data) {
                    return false;
                }
            } catch (\Exception $ex) {
                log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
            }

            if ($this->db_builder->insert($data)) {
                $insert_id = $this->db->insertID();
                if ($this->log_activity) {
                    //log this activity
                    $log_for_id = 0;
                    if ($this->log_for_key) {
                        $log_for_id = get_array_value($data, $this->log_for_key);
                    }

                    $log_for_id2 = 0;
                    if ($this->log_for_key2) {
                        $log_for_id2 = get_array_value($data, $this->log_for_key2);
                    }

                    if ($this->log_type === "task" && get_array_value($data, "context") != "project") {
                        $log_for = "general_task";
                    } else {
                        $log_for = $this->log_for;
                    }

                    $log_type_title = get_array_value($data, $this->log_type_title_key);
                    $log_data = array(
                        "action" => "created",
                        "log_type" => $this->log_type,
                        "log_type_title" => $log_type_title ? $log_type_title : "",
                        "log_type_id" => $insert_id,
                        "log_for" => $log_for,
                        "log_for_id" => $log_for_id,
                        "log_for2" => $this->log_for2,
                        "log_for_id2" => $log_for_id2,
                    );
                    $this->Activity_logs_model->ci_save($log_data, $activity_log_created_by_app);
                    $activity_log_id = $this->db->insertID();
                    $data["activity_log_id"] = $activity_log_id;
                }

                try {
                    app_hooks()->do_action("app_hook_data_insert", array(
                        "id" => $insert_id,
                        "table" => $this->table,
                        "table_without_prefix" => $this->table_without_prefix,
                        "data" => $data
                    ));
                } catch (\Exception $ex) {
                    log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
                }

                return $insert_id;
            }
        }
    }

    function update_where($data = array(), $where = array()) {
        if (count($where)) {
            $where = $this->_get_clean_value($where);

            if ($this->db_builder->update($data, $where)) {
                $id = get_array_value($where, "id");
                if ($id) {
                    return $id;
                } else {
                    return true;
                }
            }
        }
    }

    function delete($id = 0, $undo = false) {
        validate_numeric_value($id);
        $data = array('deleted' => 1);
        if ($undo === true) {
            $data = array('deleted' => 0);
        }
        $this->db_builder->where("id", $id);
        $success = $this->db_builder->update($data);
        if ($success) {
            if ($this->log_activity) {
                if ($undo) {
                    // remove previous deleted log.
                    $this->Activity_logs_model->delete_where(array("action" => "deleted", "log_type" => $this->log_type, "log_type_id" => $id));
                } else {
                    //to log this activity check the title
                    $model_info = $this->get_one($id);
                    $log_for_id = 0;
                    if ($this->log_for_key) {
                        $log_for_key = $this->log_for_key;
                        $log_for_id = $model_info->$log_for_key;
                    }
                    $log_type_title_key = $this->log_type_title_key;
                    $log_type_title = $model_info->$log_type_title_key;
                    $log_data = array(
                        "action" => "deleted",
                        "log_type" => $this->log_type,
                        "log_type_title" => $log_type_title ? $log_type_title : "",
                        "log_type_id" => $id,
                        "log_for" => $this->log_for,
                        "log_for_id" => $log_for_id,
                    );
                    $this->Activity_logs_model->ci_save($log_data);
                }
            }
        }

        try {
            app_hooks()->do_action("app_hook_data_delete", array(
                "id" => $id,
                "table" => $this->table,
                "table_without_prefix" => $this->table_without_prefix,
            ));
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
        }

        return $success;
    }

    function get_dropdown_list($option_fields = array(), $key = "id", $where = array()) {
        return $this->_get_dropdown_list($option_fields, $key, $where);
    }

    function get_dropdown_list_with_blank_option($option_fields = array(), $blank_option_text = "-", $where = array(), $key = "id") {
        return $this->_get_dropdown_list($option_fields, $key, $where, false, $blank_option_text);
    }

    function get_id_and_text_dropdown($option_fields = array(), $where = array(), $blank_option_text = "",  $key = "id") {
        return $this->_get_dropdown_list($option_fields, $key, $where, true, $blank_option_text);
    }

    private function _get_dropdown_list($option_fields = array(), $key = "id", $where = array(), $prepare_as_id_and_text = false, $blank_option_text = "") {
        $option_fields = $this->_get_clean_value($option_fields);
        $key = $this->_get_clean_value($key);

        $first_field_name = get_array_value($option_fields, 0);
        if (!$first_field_name) {
            die("Option field is required to get dropdown list");
        }
       
        $select_field_names = $key . ", " . implode(", ", $option_fields);

        $where["deleted"] = 0;

        $list_data = $this->get_all_where($where, 0, 0, $first_field_name, $select_field_names)->getResult();

        return $this->_prepare_dropdown($list_data, $option_fields, $key, $prepare_as_id_and_text, $blank_option_text);
    }

    protected function _prepare_dropdown($list_data, $option_fields, $key, $prepare_as_id_and_text = false, $blank_option_text = "") {
        $result = array();
        $select_multiple_fields = count($option_fields) > 1;
        $first_field_name = get_array_value($option_fields, 0);
        
        if ($blank_option_text) {
            if ($prepare_as_id_and_text) {
                $result[] =  array("id" => "", "text" => $blank_option_text);
            } else {
                $result[""] = $blank_option_text;
            }
        }

        foreach ($list_data as $data) {
            $id = $data->$key;
            $text = "";

            if ($select_multiple_fields) {
                foreach ($option_fields as $option) {
                    $text .= $data->$option . " "; //Combine all fields
                }
            } else {
                $text = $data->$first_field_name;
            }

            if ($prepare_as_id_and_text) {
                $result[] =  array("id" => $id, "text" => $text);
            } else {
                $result[$id] = $text;
            }
        }
        return $result;
    }


    //prepare a query string to get custom fields like as a normal field
    protected function prepare_custom_field_query_string($related_to, $custom_fields, $related_to_table, $custom_field_filter = array()) {

        $join_string = "";
        $select_string = "";
        $custom_field_values_table = $this->db->prefixTable('custom_field_values');
        $field_type_array = array();
        if ($related_to && $custom_fields) {
            $related_to = $this->_get_clean_value($related_to);

            foreach ($custom_fields as $cf) {
                $cf_id = $cf->id;
                $field_type_array[$cf_id] = $cf->field_type;
                $virtual_table = "cfvt_$cf_id"; //custom field values virtual table

                $select_string .= " , $virtual_table.value AS cfv_$cf_id ";
                $join_string .= " LEFT JOIN $custom_field_values_table AS $virtual_table ON $virtual_table.related_to_type='$related_to' AND $virtual_table.related_to_id=$related_to_table.id AND $virtual_table.deleted=0 AND $virtual_table.custom_field_id=$cf_id ";
            }
        }

        $where_string = "";
        if (is_null($custom_field_filter) || !$custom_field_filter) {
            $custom_field_filter = array();
        }
        foreach ($custom_field_filter as $cf_id => $cf_filter) {

            $cf_filter = $this->_get_clean_value($cf_filter);

            $field_type = get_array_value($field_type_array, $cf_id);
            $_where = " $custom_field_values_table.value= '$cf_filter'";
            if ($field_type == "multi_select") {
                $_where = " FIND_IN_SET('$cf_filter', $custom_field_values_table.value)";
            }

            $where_string .= " AND $related_to_table.id IN(SELECT $custom_field_values_table.related_to_id FROM $custom_field_values_table WHERE $custom_field_values_table.related_to_type='$related_to' AND $custom_field_values_table.deleted=0 AND $custom_field_values_table.custom_field_id=$cf_id AND $_where) ";
        }

        return array("select_string" => $select_string, "join_string" => $join_string, "where_string" => $where_string);
    }

    //get query of clients data according to to currency
    protected function _get_clients_of_currency_query($currency, $invoices_table, $clients_table) {
        $default_currency = get_setting("default_currency");
        $currency = $currency ? $currency : $default_currency;

        $currency = $this->_get_clean_value(array("currency" => $currency), "currency");

        $client_where = ($currency == $default_currency) ? " AND ($clients_table.currency='$default_currency' OR $clients_table.currency='' OR $clients_table.currency IS NULL)" : " AND $clients_table.currency='$currency'";

        return " AND $invoices_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 $client_where)";
    }

    protected function get_labels_data_query() {
        $labels_table = $this->db->prefixTable("labels");

        return "(SELECT GROUP_CONCAT($labels_table.id, '--::--', $labels_table.title, '--::--', $labels_table.color, ':--::--:') FROM $labels_table WHERE FIND_IN_SET($labels_table.id, $this->table.labels)) AS labels_list";
    }

    function delete_permanently($id = 0) {
        if ($id) {
            validate_numeric_value($id);
            $this->db_builder->where('id', $id);
            $result = $this->db_builder->delete();

            if ($result) {
                try {
                    app_hooks()->do_action("app_hook_data_delete", array(
                        "id" => $id,
                        "table" => $this->table,
                        "table_without_prefix" => $this->table_without_prefix,
                    ));
                } catch (\Exception $ex) {
                    log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
                }
                return true;
            }
        }
    }

    protected function prepare_allowed_client_groups_query($clients_table, $client_groups = array()) {
        $where = "";

        if (is_array($client_groups) && count($client_groups) > 0) {
            $client_groups_where = "";
            foreach ($client_groups as $client_group) {
                if ($client_groups_where) {
                    $client_groups_where .= " OR ";
                }

                $client_groups_where .= " FIND_IN_SET('$client_group', $clients_table.group_ids)";
            }

            if ($client_groups_where) {
                $where .= " AND ($client_groups_where) ";
            }
        }

        return $where;
    }

    protected function _get_clean_value($options_or_value, $key = "", $escape = true) {
        $value = $options_or_value;

        if (is_array($options_or_value) && $key) {
            $value = get_array_value($options_or_value, $key);
        }

        if (is_string($value)) {

            $length = strlen($value);

            // if ($length > 255) {
            //     $backtrace = $this->get_backtrace();
            //     log_message('error', 'Input is too long detected by _get_clean_value where the key: ' . $key . $backtrace);
            //     exit();
            // }

            //check for valid date YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
            if (($length === 10 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
                || ($length === 19 && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value))
            ) {

                $date_format = (strlen($value) === 10) ? 'Y-m-d' : 'Y-m-d H:i:s';
                $d = \DateTime::createFromFormat($date_format, $value);
                if (!$d || $d->format($date_format) !== $value) {

                    $backtrace = $this->get_backtrace();

                    log_message('error', 'Invalid date detected by _get_clean_value where the key: ' . $key . ' and value: ' . $value . $backtrace);
                    exit();
                }
                return $value; // It's a valid date or date-time string, return as-is
            }

            // Block harmful SQL functions like ASCII, SUBSTRING, etc.
            if (preg_match('/\b(ASCII|SUBSTRING|MID|LENGTH|DATABASE|SCHEMA|BENCHMARK|SLEEP|VERSION|CHAR|CONCAT)\b/i', $value)) {
                $backtrace = $this->get_backtrace();

                log_message('error', 'SQL function injection detected by _get_clean_value where the key: ' . $key . ' and value: ' . $value . $backtrace);
                exit();
            }

            // Protect against common SQL keywords, harmful characters, and patterns
            if (
                preg_match('/(?<!\w)\b(TABLE|UNION(?:\s+ALL)?|INSERT|DELETE|UPDATE|EXEC|DROP|ALTER|TRUNCATE|REPLACE|LOAD_FILE|OUTFILE|INTO|GROUP\s+BY|ORDER\s+BY|HAVING|CASE|LIKE|--|#|\/\*)\b(?!\w)/i', $value)
                || preg_match('/["]/', $value)  // Dangerous characters (excluding semicolons)
                || preg_match('/0x[0-9a-f]+/i', $value)  // Hexadecimal pattern
                || preg_match('/\/\*.*\*\//', $value)  // SQL comments
                || preg_match('/\b\d+\s*[!=<>]\s*\d+\b(?!,)/', $value)  // Detect numeric comparisons (e.g., 1=1)
                || preg_match('/%[0-9a-f]{2}/i', $value)  // URL encoding like %27 for '
                || preg_match('/(?<!\w)-\d+\s+(DELETE|UPDATE|INSERT|DROP|ALTER|UNION)\b/i', $value)
            ) {
                $backtrace = $this->get_backtrace();

                log_message('error', 'Harmful injection detected by _get_clean_value where the key: ' . $key . ' and value: ' . $value . $backtrace);
                exit();
            }

            if ($escape) {
                return $this->db->escapeString($value);
            }
            return $value;
            
        } else if (is_int($value) || is_numeric($value)) {
            return intval($value);
        } else if (is_bool($value)) {
            return $value;
        } else if (is_array($value)) {
            foreach ($value as $array_key => $new_value) {
                $value[$array_key] = $this->_get_clean_value($new_value, "", $escape);
            }
            return $value;
        } else {
            return null;
        }
    }

    protected function _get_clean_id($id) {
        if (!$id) {
            return 0;
        }
        if (is_int($id) || is_numeric($id)) {
            return intval($id);
        } else {
            $backtrace = $this->get_backtrace();
            log_message('error', 'Invalid id detected by _get_clean_id where the id: ' . $id  . $backtrace);
            exit();
        }
    }

    private function get_backtrace() {
        $backtrace_path = "\n";
        $limited_backtrace = array_slice(debug_backtrace(), 1, 5);
        foreach ($limited_backtrace as $trace) {
            $backtrace_path .= "Function: " . $trace['function'] . " ";
            if (isset($trace['file'])) {
                $backtrace_path .= "File: " . $trace['file'] . " ";
            }
            if (isset($trace['line'])) {
                $backtrace_path .= "Line: " . $trace['line'] . " ";
            }
            $backtrace_path .= "\n";
        }
        return $backtrace_path;
    }

    protected function get_custom_field_search_query($table, $related_to_type, $search_by) {
        $custom_field_values_table = $this->db->prefixTable('custom_field_values');
        return " OR $table.id IN( SELECT $custom_field_values_table.related_to_id FROM $custom_field_values_table WHERE $custom_field_values_table.deleted=0 AND $custom_field_values_table.related_to_type='$related_to_type' AND $custom_field_values_table.value LIKE '%$search_by%' ESCAPE '!' ) ";
    }

    protected function get_sales_total_meta($id, $main_table, $items_table) {

        //$main_table like as invoices table
        //$items_table like as invoice_items_table
        $taxes_table = $this->db->prefixTable('taxes');

        $invoice_sql = "SELECT $main_table.id, $main_table.discount_amount, $main_table.discount_amount_type, $main_table.discount_type,
                tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2, tax_table3.percentage AS tax_percentage3,
                tax_table.title AS tax_name, tax_table2.title AS tax_name2, tax_table3.title AS tax_name3,
                taxable_item.total_taxable, non_taxable_item.total_non_taxable
                FROM $main_table
                LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage, $taxes_table.title FROM $taxes_table) AS tax_table ON tax_table.id = $main_table.tax_id
                LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage, $taxes_table.title FROM $taxes_table) AS tax_table2 ON tax_table2.id = $main_table.tax_id2
                LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage, $taxes_table.title FROM $taxes_table) AS tax_table3 ON tax_table3.id = $main_table.tax_id3
                LEFT JOIN (SELECT SUM($items_table.total) AS total_taxable, $items_table.invoice_id FROM $items_table WHERE $items_table.deleted=0 AND $items_table.taxable = 1 GROUP BY $items_table.invoice_id) AS taxable_item ON taxable_item.invoice_id = $main_table.id
                LEFT JOIN (SELECT SUM($items_table.total) AS total_non_taxable, $items_table.invoice_id  FROM $items_table WHERE $items_table.deleted=0 AND $items_table.taxable = 0 GROUP BY $items_table.invoice_id) AS non_taxable_item ON non_taxable_item.invoice_id = $main_table.id
                WHERE $main_table.deleted=0 AND $main_table.id = $id";

        $invoice_info = $this->db->query($invoice_sql)->getRow();

        if (!$invoice_info->id) {
            return null;
        }

        $total_taxable = $invoice_info->total_taxable ? $invoice_info->total_taxable : 0;
        $total_non_taxable = $invoice_info->total_non_taxable ? $invoice_info->total_non_taxable : 0;
        $sub_total = $total_taxable + $total_non_taxable;
        $discount_total = 0;
        $invoice_total = 0;

        if ($invoice_info->discount_amount_type == "percentage") {

            $non_taxable_discount_value = $total_non_taxable * ($invoice_info->discount_amount / 100);

            if ($invoice_info->discount_type == "before_tax") {
                $taxable_discount_value = $total_taxable * ($invoice_info->discount_amount / 100);
                $total_taxable = $total_taxable - $taxable_discount_value; //apply discount before tax
            }

            $tax1 = $total_taxable * ($invoice_info->tax_percentage / 100);
            $tax2 = $total_taxable * ($invoice_info->tax_percentage2 / 100);
            $tax3 = $total_taxable * ($invoice_info->tax_percentage3 / 100);
            $total_taxable = $total_taxable + $tax1 + $tax2 - $tax3;

            $invoice_total = $total_taxable + $total_non_taxable - $non_taxable_discount_value; //deduct only non-taxable discount since the taxable discount already deducted 

            if ($invoice_info->discount_type == "after_tax") {
                $taxable_discount_value = $total_taxable * ($invoice_info->discount_amount / 100);
                $invoice_total = $total_taxable + $total_non_taxable - $taxable_discount_value - $non_taxable_discount_value;
            }

            $discount_total = $taxable_discount_value + $non_taxable_discount_value;
        } else {
            //discount_amount_type is fixed_amount

            $discount_total = $invoice_info->discount_amount; //fixed amount 
            //fixed amount discount. fixed amount can't be applied before tax when there are both taxable and non-taxable items.
            //calculate all togather 

            if ($invoice_info->discount_type == "before_tax" && $total_taxable > 0) {
                $total_taxable = $total_taxable - $discount_total;
            } else if ($invoice_info->discount_type == "before_tax" && $total_taxable == 0) {
                $total_non_taxable = $total_non_taxable - $discount_total;
            }


            $tax1 = $total_taxable * ($invoice_info->tax_percentage / 100);
            $tax2 = $total_taxable * ($invoice_info->tax_percentage2 / 100);
            $tax3 = $total_taxable * ($invoice_info->tax_percentage3 / 100);
            $invoice_total = $total_taxable + $total_non_taxable + $tax1 + $tax2 - $tax3; //discount before tax

            if ($invoice_info->discount_type == "after_tax") {
                $invoice_total = $total_taxable + $total_non_taxable + $tax1 + $tax2 - $tax3 - $discount_total;
            }
        }

        $info = new \stdClass();
        $info->invoice_total = number_format($invoice_total, 2, ".", "") * 1;
        $info->invoice_subtotal = number_format($sub_total, 2, ".", "") * 1;
        $info->discount_total = number_format($discount_total, 2, ".", "") * 1;

        $info->tax_percentage = $invoice_info->tax_percentage;
        $info->tax_percentage2 = $invoice_info->tax_percentage2;
        $info->tax_percentage3 = $invoice_info->tax_percentage3;
        $info->tax_name = $invoice_info->tax_name;
        $info->tax_name2 = $invoice_info->tax_name2;
        $info->tax_name3 = $invoice_info->tax_name3;

        $info->tax = number_format($tax1, 2, ".", "") * 1;
        $info->tax2 = number_format($tax2, 2, ".", "") * 1;
        $info->tax3 = number_format($tax3, 2, ".", "") * 1;

        $info->discount_type = $invoice_info->discount_type;
        return $info;
    }

    function get_share_with_users_of_event($event_info = null, $query_for_notification = false) {
        if (!($event_info && $event_info->share_with)) {
            return "";
        }

        $users_table = $this->db->prefixTable('users');
        $team_table = $this->db->prefixTable('team');

        $where = "";

        $created_by = $this->_get_clean_value($event_info->created_by);
        $share_with_value = $this->_get_clean_value($event_info->share_with);
        $share_with_array = explode(",", $share_with_value); // found an array like this array("member:1", "member:2", "team:1")
        $or_query_array = array();

        if (in_array("all", $share_with_array)) { // has 'all' access
            $or_query_array[] = " $users_table.user_type='staff' ";
        }

        if (in_array("all_contacts", $share_with_array)) { // has 'all_contacts' access
            $client_id = $this->_get_clean_value($event_info->client_id);
            $or_query_array[] = " $users_table.client_id=$client_id ";
        }

        // has member/team/contact access
        $event_users = array();
        $event_team = array();
        $event_contact = array();

        foreach ($share_with_array as $share) {
            $share_context_explode = explode(":", $share);
            if (count($share_context_explode) != 2) continue;

            list($context, $context_id) = $share_context_explode;
            if ($context === "member") $event_users[] = $context_id;
            if ($context === "team") $event_team[] = $context_id;
            if ($context === "contact") $event_contact[] = $context_id;
        }

        //find team members
        if (count($event_users)) {
            $or_query_array[] = " FIND_IN_SET($users_table.id, '" . join(',', $event_users) . "') ";
        }

        //find team
        if (count($event_team)) {
            $or_query_array[] = " FIND_IN_SET($users_table.id, (SELECT GROUP_CONCAT($team_table.members) AS team_users FROM $team_table WHERE $team_table.deleted=0 AND FIND_IN_SET($team_table.id, '" . join(',', $event_team) . "'))) ";
        }

        //find client contacts
        if (count($event_contact)) {
            $or_query_array[] = " FIND_IN_SET($users_table.id, '" . join(',', $event_contact) . "') ";
        }

        $where = " (" . join(" OR ", $or_query_array) . ") ";

        if ($query_for_notification) {
            $where = " OR " . $where;
            return $where;
        } else {
            $where = " AND " . $where;
        }

        $sql = "SELECT $users_table.id, $users_table.email FROM $users_table
                WHERE $users_table.deleted=0 AND $users_table.status='active' AND $users_table.id!=$created_by $where";

        return $this->db->query($sql);
    }
}
