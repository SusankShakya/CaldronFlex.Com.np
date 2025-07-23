<?php

namespace App\Libraries;

use App\Controllers\App_Controller;

class Reminders {

    private $ci;
    private $today = null;

    public function __construct() {
        $this->ci = new App_Controller();
        $this->today = get_today_date();
    }

    function create_reminders($context) {
        $reminders_info = $this->ci->Reminder_settings_model->get_reminders_by_context($context);

        $weekly_dates = [];
        $monthly_dates = [];
        $yearly_dates = [];

        foreach ($reminders_info as $reminder_info) {
            if ($reminder_info->reminder_event == "subscription_weekly_reminder") {
                if ($reminder_info->reminder1) {
                    $weekly_dates[] = add_period_to_date($this->today, $reminder_info->reminder1, "days");
                }
                if ($reminder_info->reminder2) {
                    $weekly_dates[] = add_period_to_date($this->today, $reminder_info->reminder2, "days");
                }
            } else if ($reminder_info->reminder_event == "subscription_monthly_reminder") {
                if ($reminder_info->reminder1) {
                    $monthly_dates[] = add_period_to_date($this->today, $reminder_info->reminder1, "days");
                }
                if ($reminder_info->reminder2) {
                    $monthly_dates[] = add_period_to_date($this->today, $reminder_info->reminder2, "days");
                }
            } else if ($reminder_info->reminder_event == "subscription_yearly_reminder") {
                if ($reminder_info->reminder1) {
                    $yearly_dates[] = add_period_to_date($this->today, $reminder_info->reminder1, "days");
                }
                if ($reminder_info->reminder2) {
                    $yearly_dates[] = add_period_to_date($this->today, $reminder_info->reminder2, "days");
                }
            }
        }

        $reminders = $this->ci->Reminder_settings_model->get_reminders(array(
            "status" => "active",
            "context" => $context,
            "weekly_dates" => implode(',', $weekly_dates),
            "monthly_dates" => implode(',', $monthly_dates),
            "yearly_dates" => implode(',', $yearly_dates),
            "exclude_reminder_date" => $this->today
        ))->getResult();

        foreach ($reminders as $reminder) {
            $data = array(
                "context" => $context,
                "context_id" => $reminder->id,
                "reminder_date" => $this->today,
            );

            if ($context == "subscription") {
                $data["reminder_event"] = "subscription_renewal_reminder";
            }

            $this->ci->Reminder_logs_model->ci_save($data);
        }
    }

    function send_available_reminders() {
        $available_reminders = $this->ci->Reminder_logs_model->get_details(array("notification_status" => "draft"))->getResult();

        foreach ($available_reminders as $available_reminder) {
            // Create dynamic key based on the context
            $context_key = $available_reminder->context . "_id";
            $notification_data = array($context_key => $available_reminder->context_id, "reminder_log_id" => $available_reminder->id);

            log_notification($available_reminder->reminder_event, $notification_data, "0");
        }
    }
}
