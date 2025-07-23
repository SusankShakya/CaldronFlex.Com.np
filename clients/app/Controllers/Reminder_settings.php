<?php

namespace App\Controllers;

class Reminder_settings extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
    }

    function subscription_reminders() {
        $reminders_info = $this->Reminder_settings_model->get_reminders_by_context("subscription");

        $view_data = array();

        foreach ($reminders_info as $reminder_info) {
            if ($reminder_info->reminder_event == "subscription_weekly_reminder") {
                $view_data["weekly_reminder_info"] = $reminder_info;
            } elseif ($reminder_info->reminder_event == "subscription_monthly_reminder") {
                $view_data["monthly_reminder_info"] = $reminder_info;
            } elseif ($reminder_info->reminder_event == "subscription_yearly_reminder") {
                $view_data["yearly_reminder_info"] = $reminder_info;
            }
        }

        return $this->template->view("settings/subscriptions/subscription_reminders", $view_data);
    }

    private function _save_reminder_setting($type) {
        $reminder_1 = $this->request->getPost("subscription_{$type}_reminder_1");
        $reminder_2 = $this->request->getPost("subscription_{$type}_reminder_2");

        if ($reminder_1 || $reminder_2) {
            $data = array(
                "context" => "subscription",
                "reminder_event" => "subscription_{$type}_reminder",
                "reminder1" => $reminder_1,
                "reminder2" => $reminder_2
            );

            $reminder_info = $this->Reminder_settings_model->get_details(array("context" => "subscription", "reminder_event" => "subscription_{$type}_reminder"))->getRow();

            if ($reminder_info) {
                $this->Reminder_settings_model->ci_save($data, $reminder_info->id);
            } else {
                $this->Reminder_settings_model->ci_save($data);
            }
        }
    }

    function save_subscription_reminders_settings() {
        $this->_save_reminder_setting("weekly");
        $this->_save_reminder_setting("monthly");
        $this->_save_reminder_setting("yearly");

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }
}

/* End of file Reminder_settings.php */
/* Location: ./app/controllers/Reminder_settings.php */