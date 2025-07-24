<?php

namespace App\Controllers;

use App\Libraries\Cron_job;

class Cron extends App_Controller {

    private $cron_job;

    function __construct() {
        parent::__construct();
        $this->cron_job = new Cron_job();
    }

    function index() {
        ini_set('max_execution_time', 300); //execute maximum 300 seconds 

        $last_cron_job_time = get_setting('last_cron_job_time');

        $minimum_cron_interval_seconds = get_setting('minimum_cron_interval_seconds');
        if (!$minimum_cron_interval_seconds) {
            $minimum_cron_interval_seconds = 300; //5 minutes
        }

        $current_time = strtotime(get_current_utc_time());

        if ($last_cron_job_time == "" || ($current_time > ($last_cron_job_time * 1 + $minimum_cron_interval_seconds))) {
            $this->cron_job->run();
            app_hooks()->do_action("app_hook_after_cron_run");
            $this->Settings_model->save_setting("last_cron_job_time", $current_time);
            echo "Cron job executed.";
        } else {
            $start = new \DateTime(date("Y-m-d H:i:s", $last_cron_job_time * 1 + $minimum_cron_interval_seconds));
            $end = new \DateTime();
            $diff = $end->diff($start);
            $format = "%i minutes, %s seconds.";

            if ($diff->i <= 0) {
                $format = "%s seconds.";
            }
            echo "Please try after " . $end->diff($start)->format($format);
        }
    }

    /**
     * Check inventory levels and send low stock alerts
     * Should be run hourly or as configured
     */
    public function check_inventory_levels() {
        ini_set('max_execution_time', 300);
        
        // Load the inventory notification service
        $notification_service = new \App\Services\Inventory_notification_service();
        
        // Check all inventory items for low stock
        $result = $notification_service->check_all_inventory_levels();
        
        if ($result['success']) {
            echo "Inventory check completed. ";
            echo "Alerts sent: " . $result['alerts_sent'] . ", ";
            echo "Items checked: " . $result['items_checked'];
        } else {
            echo "Inventory check failed: " . $result['message'];
        }
    }

    /**
     * Send periodic inventory reports
     * Should be run daily, weekly, or monthly as configured
     */
    public function send_inventory_reports() {
        ini_set('max_execution_time', 600);
        
        // Load the inventory notification service
        $notification_service = new \App\Services\Inventory_notification_service();
        
        // Get report frequency from settings
        $frequency = get_setting('inventory_report_frequency');
        if (!$frequency) {
            $frequency = 'weekly'; // Default to weekly
        }
        
        // Check if it's time to send the report
        $last_report_date = get_setting('last_inventory_report_date');
        $should_send = false;
        
        if (!$last_report_date) {
            $should_send = true;
        } else {
            $last_date = new \DateTime($last_report_date);
            $current_date = new \DateTime();
            $interval = $current_date->diff($last_date);
            
            switch ($frequency) {
                case 'daily':
                    $should_send = $interval->days >= 1;
                    break;
                case 'weekly':
                    $should_send = $interval->days >= 7;
                    break;
                case 'monthly':
                    $should_send = $interval->m >= 1 || $interval->y > 0;
                    break;
            }
        }
        
        if ($should_send) {
            $result = $notification_service->send_inventory_reports($frequency);
            
            if ($result['success']) {
                // Update last report date
                $this->Settings_model->save_setting("last_inventory_report_date", date('Y-m-d'));
                echo "Inventory reports sent successfully. ";
                echo "Reports sent: " . $result['reports_sent'];
            } else {
                echo "Failed to send inventory reports: " . $result['message'];
            }
        } else {
            echo "Not time to send inventory reports yet.";
        }
    }

    /**
     * Clean up old notification history
     * Should be run daily
     */
    public function cleanup_old_notifications() {
        ini_set('max_execution_time', 300);
        
        // Load the inventory notification service
        $notification_service = new \App\Services\Inventory_notification_service();
        
        // Get retention period from settings (default 30 days)
        $retention_days = get_setting('notification_history_retention_days');
        if (!$retention_days) {
            $retention_days = 30;
        }
        
        $result = $notification_service->cleanup_old_notifications($retention_days);
        
        if ($result['success']) {
            echo "Notification cleanup completed. ";
            echo "Records deleted: " . $result['deleted_count'];
        } else {
            echo "Notification cleanup failed: " . $result['message'];
        }
    }
}

/* End of file Cron.php */
/* Location: ./app/controllers/Cron.php */