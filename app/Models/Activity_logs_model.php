<?php

namespace App\Models;

use CodeIgniter\Model;

class Activity_logs_model extends Model {

    protected $db;
    protected $db_builder = null;
    protected $allowedFields = array();

    function __construct() {
        $this->db = db_connect('default');
        $this->db_builder = $this->db->table("activity_logs");
    }

    function ci_save($data, $activity_log_created_by_app = false) {
        //allowed fields should be assigned
        $db_fields = $this->db->getFieldNames("activity_logs");
        foreach ($db_fields as $field) {
            if ($field !== "id") {
                array_push($this->allowedFields, $field);
            }
        }

        $data["created_at"] = get_current_utc_time();

        $created_by = 0;
        if (!$activity_log_created_by_app) {
            $users_model = model("App\Models\Users_model", false);
            $created_by = $users_model->login_user_id();
        }

        $data["created_by"] = $created_by;
        $this->db_builder->insert($data);
        return $this->db->insertID();
    }

    function delete_where($where = array()) {
        $where = $this->_get_clean_value($where);

        if (count($where)) {
            return $this->db_builder->delete($where);
        }
    }

    function get_details($options = array()) {
        $activity_logs_table = $this->db->prefixTable('activity_logs');
        $project_members_table = $this->db->prefixTable('project_members');
        $users_table = $this->db->prefixTable('users');
        $projects_table = $this->db->prefixTable('projects');
        $tasks_table = $this->db->prefixTable('tasks');

        $where = "";
        $limit = $this->_get_clean_value($options, "limit");
        $limit = $limit ? $limit : "20";
        $offset = $this->_get_clean_value($options, "offset");
        $offset = $offset ? $offset : "0";

        $extra_join_info = "";
        $extra_select = "";

        $log_for = $this->_get_clean_value($options, "log_for");
        if ($log_for) {
            $where .= " AND $activity_logs_table.log_for='$log_for'";

            $log_for_id = $this->_get_clean_value($options, "log_for_id");
            if ($log_for_id) {
                $where .= " AND $activity_logs_table.log_for_id=$log_for_id";
            } else {
                //link with the parent
                if ($log_for === "project") {
                    $link_with_table = $this->db->prefixTable('projects');
                    $extra_join_info = " LEFT JOIN $link_with_table ON $activity_logs_table.log_for_id=$link_with_table.id ";
                    $extra_select = " , $link_with_table.title as log_for_title";
                }
            }
        }

        $log_type = $this->_get_clean_value($options, "log_type");
        $log_type_id = $this->_get_clean_value($options, "log_type_id");
        if ($log_type && $log_type_id) {
            $where .= " AND $activity_logs_table.log_type='$log_type' AND $activity_logs_table.log_type_id=$log_type_id";
        }

        //don't show all project's log for none admin users
        $project_join = "";
        $project_where = "";
        $user_id = $this->_get_clean_value($options, "user_id");
        $is_admin = $this->_get_clean_value($options, "is_admin");
        $user_type = $this->_get_clean_value($options, "user_type");
        if (!$is_admin && $user_id && $user_type !== "client") {
            $project_join = " LEFT JOIN (SELECT $project_members_table.user_id, $project_members_table.project_id FROM $project_members_table WHERE $project_members_table.user_id=$user_id AND $project_members_table.deleted=0 GROUP BY $project_members_table.project_id) AS project_members_table ON project_members_table.project_id= $activity_logs_table.log_for_id AND log_for='project' ";
            $project_where = " AND project_members_table.user_id=$user_id";

            $show_assigned_tasks_only = $this->_get_clean_value($options, "show_assigned_tasks_only");
            if ($show_assigned_tasks_only) {
                //this is restricted only for tasks related logs
                //task created/updated/deleted
                $where .= " AND IF($activity_logs_table.log_type='task', $activity_logs_table.log_type_id IN(SELECT $tasks_table.id FROM $tasks_table WHERE $tasks_table.id=$activity_logs_table.log_type_id AND ($tasks_table.assigned_to=$user_id OR FIND_IN_SET('$user_id', $tasks_table.collaborators))), $activity_logs_table.log_type!='task')";

                //task commented
                $where .= " AND IF($activity_logs_table.log_type='task_comment', $activity_logs_table.log_for_id2 IN(SELECT $tasks_table.id FROM $tasks_table WHERE $tasks_table.id=$activity_logs_table.log_for_id2 AND ($tasks_table.assigned_to=$user_id OR FIND_IN_SET('$user_id', $tasks_table.collaborators))), $activity_logs_table.log_type!='task_comment')";
            }
        }

        //show client's own projects activity
        if ($user_type == "client") {
            $client_id = $this->_get_clean_value($options, "client_id");

            if ($client_id) {
                $project_join = " LEFT JOIN (SELECT $projects_table.client_id, $projects_table.id FROM $projects_table WHERE $projects_table.client_id=$client_id AND $projects_table.deleted=0 GROUP BY $projects_table.id) AS projects_table ON projects_table.id= $activity_logs_table.log_for_id AND log_for='project' ";
                $project_where = " AND projects_table.client_id=$client_id";
            }

            //don't show project's comments to clients
            $where .= " AND $activity_logs_table.log_type!='project_comment'";
        }



        $sql = "SELECT SQL_CALC_FOUND_ROWS $activity_logs_table.*,  CONCAT($users_table.first_name, ' ',$users_table.last_name) AS created_by_user, $users_table.image as created_by_avatar, $users_table.user_type $extra_select
        FROM $activity_logs_table
        LEFT JOIN $users_table ON $users_table.id= $activity_logs_table.created_by
        $extra_join_info
        $project_join
        WHERE $activity_logs_table.deleted=0 $where $project_where
        ORDER BY $activity_logs_table.created_at DESC
        LIMIT $offset, $limit";
        $data = new \stdClass();
        $data->result = $this->db->query($sql)->getResult();
        $data->found_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow()->found_rows;
        return $data;
    }

    function get_one($id = 0) {
        return $this->get_one_where(array('id' => $id));
    }

    function get_one_where($where = array()) {
        $where = $this->_get_clean_value($where);

        $result = $this->db_builder->getWhere($where, 1);
        if (count($result->getResult())) {
            return $result->getRow();
        } else {
            $db_fields = $this->db->getFieldNames("activity_logs");
            $fields = new \stdClass();
            foreach ($db_fields as $field) {
                $fields->$field = "";
            }
            return $fields;
        }
    } 

    function update_where($data = array(), $where = array()) {
        $where = $this->_get_clean_value($where);
        if (count($where)) {
            return $this->db_builder->update($data, $where);
        }
    }

    protected function _get_clean_value($options_or_value, $key = "") {
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

            return $this->db->escapeString($value);
        } else if (is_int($value) || is_numeric($value)) {
            return intval($value);
        } else if (is_bool($value)) {
            return $value;
        } else if (is_array($value)) {
            foreach ($value as $array_key => $new_value) {
                $value[$array_key] = $this->_get_clean_value($new_value);
            }
            return $value;
        } else {
            return null;
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

}
