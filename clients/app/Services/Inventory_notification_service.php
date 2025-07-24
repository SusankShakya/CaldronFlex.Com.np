<?php

namespace App\Services;

use App\Models\Inventory_model;
use App\Models\Warehouses_model;
use App\Models\Items_model;
use App\Models\Users_model;
use App\Models\Notifications_model;
use App\Models\Settings_model;
use CodeIgniter\Email\Email;
use CodeIgniter\I18n\Time;

/**
 * Inventory Notification Service
 *
 * Monitors inventory levels and triggers alerts via multiple channels.
 * Handles batching, notification history, alert levels, and integrates with Rise CRM's notification system.
 */
class Inventory_notification_service
{
    protected $inventoryModel;
    protected $warehousesModel;
    protected $itemsModel;
    protected $usersModel;
    protected $notificationsModel;
    protected $settingsModel;
    protected $email;
    protected $db;
    protected $batchSize = 20;
    protected $notificationCooldown = 24; // hours

    // Alert levels
    const ALERT_WARNING = 'warning';
    const ALERT_CRITICAL = 'critical';
    const ALERT_OUT_OF_STOCK = 'out_of_stock';

    // Notification types
    const TYPE_EMAIL = 'email';
    const TYPE_INAPP = 'inapp';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inventoryModel = model('App\Models\Inventory_model');
        $this->warehousesModel = model('App\Models\Warehouses_model');
        $this->itemsModel = model('App\Models\Items_model');
        $this->usersModel = model('App\Models\Users_model');
        $this->notificationsModel = model('App\Models\Notifications_model');
        $this->settingsModel = model('App\Models\Settings_model');
        $this->email = \Config\Services::email();
        $this->db = \Config\Database::connect();
    }

    /**
     * Monitor inventory and trigger alerts for all items/warehouses.
     * Called by cron or manually.
     */
    public function monitor_and_notify()
    {
        $lowStockItems = $this->get_low_stock_items();
        $notificationsToSend = [];

        foreach ($lowStockItems as $item) {
            $alertLevel = $this->determine_alert_level($item);
            if ($alertLevel && $this->should_notify($item, $alertLevel)) {
                $notificationsToSend[] = [
                    'item' => $item,
                    'alert_level' => $alertLevel
                ];
            }
        }

        if (!empty($notificationsToSend)) {
            $this->send_batch_notifications($notificationsToSend);
        }

        return count($notificationsToSend);
    }

    /**
     * Get items with low stock across all warehouses
     */
    protected function get_low_stock_items()
    {
        $sql = "SELECT 
                    i.id as item_id,
                    i.title as item_name,
                    i.item_code,
                    inv.warehouse_id,
                    w.name as warehouse_name,
                    inv.quantity as stock,
                    inv.min_stock_level as warning_threshold,
                    inv.reorder_level as critical_threshold
                FROM rise_inventory inv
                JOIN rise_items i ON i.id = inv.item_id
                JOIN rise_warehouses w ON w.id = inv.warehouse_id
                WHERE inv.deleted = 0 
                AND i.deleted = 0 
                AND w.deleted = 0
                AND (inv.quantity <= inv.min_stock_level OR inv.quantity <= 0)
                ORDER BY inv.quantity ASC";

        $query = $this->db->query($sql);
        return $query->getResultArray();
    }

    /**
     * Determine alert level based on item stock and thresholds.
     */
    protected function determine_alert_level($item)
    {
        if ($item['stock'] <= 0) {
            return self::ALERT_OUT_OF_STOCK;
        } elseif ($item['stock'] <= $item['critical_threshold']) {
            return self::ALERT_CRITICAL;
        } elseif ($item['stock'] <= $item['warning_threshold']) {
            return self::ALERT_WARNING;
        }
        return null;
    }

    /**
     * Check if notification should be sent (prevents duplicates).
     */
    protected function should_notify($item, $alertLevel)
    {
        // Check notification history for this item/warehouse/alert level
        $sql = "SELECT * FROM rise_inventory_notifications 
                WHERE item_id = ? 
                AND warehouse_id = ? 
                AND notification_type = ?
                AND sent_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY sent_at DESC
                LIMIT 1";

        $query = $this->db->query($sql, [
            $item['item_id'],
            $item['warehouse_id'],
            $alertLevel,
            $this->notificationCooldown
        ]);

        return $query->getNumRows() === 0;
    }

    /**
     * Send notifications in batches to avoid spam.
     */
    protected function send_batch_notifications($notifications)
    {
        $batches = array_chunk($notifications, $this->batchSize);
        
        foreach ($batches as $batch) {
            foreach ($batch as $notification) {
                $this->send_notification($notification['item'], $notification['alert_level']);
            }
            // Small delay between batches to avoid overwhelming the system
            if (count($batches) > 1) {
                sleep(1);
            }
        }
    }

    /**
     * Send notification for a single item/warehouse/alert level.
     */
    public function send_notification($item, $alertLevel)
    {
        $recipients = $this->get_notification_recipients($item['warehouse_id']);
        
        foreach ($recipients as $recipient) {
            // Check user notification preferences
            if ($this->should_notify_user($recipient, $alertLevel)) {
                // Send email notification
                if ($this->user_wants_email($recipient)) {
                    $this->send_email_notification($recipient, $item, $alertLevel);
                }
                
                // Send in-app notification
                if ($this->user_wants_inapp($recipient)) {
                    $this->send_inapp_notification($recipient, $item, $alertLevel);
                }
            }
        }

        // Record notification in history
        $this->record_notification($item, $alertLevel);
    }

    /**
     * Get users who should receive notifications for a warehouse
     */
    protected function get_notification_recipients($warehouseId)
    {
        // Get warehouse managers and inventory managers
        $sql = "SELECT DISTINCT u.* 
                FROM rise_users u
                LEFT JOIN rise_team_member_job_info tmj ON tmj.user_id = u.id
                WHERE u.deleted = 0 
                AND u.status = 'active'
                AND (
                    u.user_type = 'staff' 
                    OR u.id IN (
                        SELECT user_id FROM rise_warehouse_managers 
                        WHERE warehouse_id = ? AND deleted = 0
                    )
                    OR u.role_id IN (
                        SELECT id FROM rise_roles 
                        WHERE permissions LIKE '%inventory%' 
                        AND deleted = 0
                    )
                )";

        $query = $this->db->query($sql, [$warehouseId]);
        return $query->getResultArray();
    }

    /**
     * Check if user should be notified based on their preferences
     */
    protected function should_notify_user($user, $alertLevel)
    {
        // Check user's notification settings
        $settings = $this->get_user_notification_settings($user['id']);
        
        // Default to true if no specific settings
        if (!$settings) {
            return true;
        }

        // Check if user wants this alert level
        return isset($settings['inventory_alerts'][$alertLevel]) && 
               $settings['inventory_alerts'][$alertLevel] === true;
    }

    /**
     * Get user notification settings
     */
    protected function get_user_notification_settings($userId)
    {
        $sql = "SELECT * FROM rise_notification_settings 
                WHERE user_id = ? 
                AND deleted = 0";
        
        $query = $this->db->query($sql, [$userId]);
        $result = $query->getRowArray();
        
        if ($result && $result['settings_data']) {
            return json_decode($result['settings_data'], true);
        }
        
        return null;
    }

    /**
     * Check if user wants email notifications
     */
    protected function user_wants_email($user)
    {
        $settings = $this->get_user_notification_settings($user['id']);
        return !isset($settings['email_notifications']) || 
               $settings['email_notifications'] !== false;
    }

    /**
     * Check if user wants in-app notifications
     */
    protected function user_wants_inapp($user)
    {
        $settings = $this->get_user_notification_settings($user['id']);
        return !isset($settings['inapp_notifications']) || 
               $settings['inapp_notifications'] !== false;
    }

    /**
     * Send email notification
     */
    protected function send_email_notification($recipient, $item, $alertLevel)
    {
        $emailTemplate = $this->get_email_template($alertLevel);
        $subject = $this->get_email_subject($alertLevel, $item);
        
        $data = [
            'recipient_name' => $recipient['first_name'] . ' ' . $recipient['last_name'],
            'item_name' => $item['item_name'],
            'item_code' => $item['item_code'],
            'warehouse_name' => $item['warehouse_name'],
            'current_stock' => $item['stock'],
            'warning_threshold' => $item['warning_threshold'],
            'critical_threshold' => $item['critical_threshold'],
            'alert_level' => $alertLevel,
            'app_title' => get_setting('app_title'),
            'logo_url' => get_logo_url(),
            'dashboard_url' => base_url('inventory')
        ];

        $message = view("email_templates/" . $emailTemplate, $data);

        $this->email->setTo($recipient['email']);
        $this->email->setSubject($subject);
        $this->email->setMessage($message);
        $this->email->send();
    }

    /**
     * Send in-app notification
     */
    protected function send_inapp_notification($recipient, $item, $alertLevel)
    {
        $message = $this->get_notification_message($item, $alertLevel);
        
        $notificationData = [
            'user_id' => $recipient['id'],
            'description' => $message,
            'created_at' => get_current_utc_time(),
            'notify_to' => $recipient['id'],
            'read' => 0,
            'event' => 'inventory_alert',
            'project_id' => 0,
            'task_id' => 0,
            'project_comment_id' => 0,
            'ticket_id' => 0,
            'ticket_comment_id' => 0,
            'project_file_id' => 0,
            'leave_id' => 0,
            'post_id' => 0,
            'to_user_id' => $recipient['id'],
            'created_by' => 0, // System notification
            'notification_multiple_tasks' => ''
        ];

        $this->notificationsModel->ci_save($notificationData);
    }

    /**
     * Get email template based on alert level
     */
    protected function get_email_template($alertLevel)
    {
        switch ($alertLevel) {
            case self::ALERT_OUT_OF_STOCK:
                return 'out_of_stock_alert';
            case self::ALERT_CRITICAL:
            case self::ALERT_WARNING:
                return 'low_stock_alert';
            default:
                return 'low_stock_alert';
        }
    }

    /**
     * Get email subject based on alert level
     */
    protected function get_email_subject($alertLevel, $item)
    {
        $appTitle = get_setting('app_title');
        
        switch ($alertLevel) {
            case self::ALERT_OUT_OF_STOCK:
                return sprintf('[%s] Out of Stock Alert: %s', $appTitle, $item['item_name']);
            case self::ALERT_CRITICAL:
                return sprintf('[%s] Critical Stock Level: %s', $appTitle, $item['item_name']);
            case self::ALERT_WARNING:
                return sprintf('[%s] Low Stock Warning: %s', $appTitle, $item['item_name']);
            default:
                return sprintf('[%s] Inventory Alert: %s', $appTitle, $item['item_name']);
        }
    }

    /**
     * Get notification message
     */
    protected function get_notification_message($item, $alertLevel)
    {
        switch ($alertLevel) {
            case self::ALERT_OUT_OF_STOCK:
                return sprintf(
                    'Out of Stock: %s (%s) in %s warehouse',
                    $item['item_name'],
                    $item['item_code'],
                    $item['warehouse_name']
                );
            case self::ALERT_CRITICAL:
                return sprintf(
                    'Critical Stock Level: %s (%s) has only %d units left in %s warehouse',
                    $item['item_name'],
                    $item['item_code'],
                    $item['stock'],
                    $item['warehouse_name']
                );
            case self::ALERT_WARNING:
                return sprintf(
                    'Low Stock Warning: %s (%s) has %d units left in %s warehouse',
                    $item['item_name'],
                    $item['item_code'],
                    $item['stock'],
                    $item['warehouse_name']
                );
            default:
                return sprintf(
                    'Inventory Alert: %s in %s warehouse',
                    $item['item_name'],
                    $item['warehouse_name']
                );
        }
    }

    /**
     * Record notification in history
     */
    protected function record_notification($item, $alertLevel)
    {
        $data = [
            'item_id' => $item['item_id'],
            'warehouse_id' => $item['warehouse_id'],
            'notification_type' => $alertLevel,
            'stock_level' => $item['stock'],
            'threshold_level' => $alertLevel === self::ALERT_WARNING ? 
                              $item['warning_threshold'] : 
                              $item['critical_threshold'],
            'sent_at' => get_current_utc_time(),
            'created_by' => 0 // System
        ];

        $this->db->table('inventory_notifications')->insert($data);
    }

    /**
     * Generate inventory report for email
     */
    public function generate_inventory_report($warehouseId = null, $period = 'daily')
    {
        $reportData = [
            'low_stock_items' => $this->get_low_stock_summary($warehouseId),
            'out_of_stock_items' => $this->get_out_of_stock_summary($warehouseId),
            'recent_movements' => $this->get_recent_movements($warehouseId, $period),
            'warehouse_summary' => $this->get_warehouse_summary($warehouseId),
            'period' => $period,
            'generated_at' => Time::now()
        ];

        return $reportData;
    }

    /**
     * Get low stock summary for report
     */
    protected function get_low_stock_summary($warehouseId = null)
    {
        $sql = "SELECT
                    i.id, i.title, i.item_code,
                    inv.quantity, inv.min_stock_level,
                    w.name as warehouse_name
                FROM rise_inventory inv
                JOIN rise_items i ON i.id = inv.item_id
                JOIN rise_warehouses w ON w.id = inv.warehouse_id
                WHERE inv.deleted = 0
                AND i.deleted = 0
                AND w.deleted = 0
                AND inv.quantity > 0
                AND inv.quantity <= inv.min_stock_level";

        if ($warehouseId) {
            $sql .= " AND inv.warehouse_id = " . $this->db->escape($warehouseId);
        }

        $sql .= " ORDER BY inv.quantity ASC LIMIT 20";

        $query = $this->db->query($sql);
        return $query->getResultArray();
    }

    /**
     * Get out of stock summary for report
     */
    protected function get_out_of_stock_summary($warehouseId = null)
    {
        $sql = "SELECT
                    i.id, i.title, i.item_code,
                    w.name as warehouse_name,
                    inv.last_updated
                FROM rise_inventory inv
                JOIN rise_items i ON i.id = inv.item_id
                JOIN rise_warehouses w ON w.id = inv.warehouse_id
                WHERE inv.deleted = 0
                AND i.deleted = 0
                AND w.deleted = 0
                AND inv.quantity <= 0";

        if ($warehouseId) {
            $sql .= " AND inv.warehouse_id = " . $this->db->escape($warehouseId);
        }

        $sql .= " ORDER BY inv.last_updated DESC LIMIT 20";

        $query = $this->db->query($sql);
        return $query->getResultArray();
    }

    /**
     * Get recent inventory movements for report
     */
    protected function get_recent_movements($warehouseId = null, $period = 'daily')
    {
        $dateFilter = match($period) {
            'daily' => "DATE_SUB(NOW(), INTERVAL 1 DAY)",
            'weekly' => "DATE_SUB(NOW(), INTERVAL 7 DAY)",
            'monthly' => "DATE_SUB(NOW(), INTERVAL 30 DAY)",
            default => "DATE_SUB(NOW(), INTERVAL 1 DAY)"
        };

        $sql = "SELECT
                    im.*,
                    i.title as item_name,
                    i.item_code,
                    w.name as warehouse_name,
                    u.first_name, u.last_name
                FROM rise_inventory_movements im
                JOIN rise_items i ON i.id = im.item_id
                JOIN rise_warehouses w ON w.id = im.warehouse_id
                LEFT JOIN rise_users u ON u.id = im.created_by
                WHERE im.created_at >= $dateFilter";

        if ($warehouseId) {
            $sql .= " AND im.warehouse_id = " . $this->db->escape($warehouseId);
        }

        $sql .= " ORDER BY im.created_at DESC LIMIT 50";

        $query = $this->db->query($sql);
        return $query->getResultArray();
    }

    /**
     * Get warehouse summary for report
     */
    protected function get_warehouse_summary($warehouseId = null)
    {
        $sql = "SELECT
                    w.id, w.name,
                    COUNT(DISTINCT inv.item_id) as total_items,
                    SUM(CASE WHEN inv.quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                    SUM(CASE WHEN inv.quantity > 0 AND inv.quantity <= inv.min_stock_level THEN 1 ELSE 0 END) as low_stock_count,
                    SUM(inv.quantity * i.rate) as total_value
                FROM rise_warehouses w
                LEFT JOIN rise_inventory inv ON inv.warehouse_id = w.id AND inv.deleted = 0
                LEFT JOIN rise_items i ON i.id = inv.item_id AND i.deleted = 0
                WHERE w.deleted = 0";

        if ($warehouseId) {
            $sql .= " AND w.id = " . $this->db->escape($warehouseId);
        }

        $sql .= " GROUP BY w.id, w.name";

        $query = $this->db->query($sql);
        return $query->getResultArray();
    }

    /**
     * Send inventory report to specified recipients
     */
    public function send_inventory_report($recipients, $warehouseId = null, $period = 'daily')
    {
        $reportData = $this->generate_inventory_report($warehouseId, $period);
        
        $data = array_merge($reportData, [
            'app_title' => get_setting('app_title'),
            'logo_url' => get_logo_url(),
            'dashboard_url' => base_url('inventory')
        ]);

        $subject = sprintf(
            '[%s] %s Inventory Report - %s',
            get_setting('app_title'),
            ucfirst($period),
            Time::now()->toDateString()
        );

        foreach ($recipients as $recipient) {
            $data['recipient_name'] = $recipient['first_name'] . ' ' . $recipient['last_name'];
            $message = view("email_templates/inventory_report", $data);

            $this->email->setTo($recipient['email']);
            $this->email->setSubject($subject);
            $this->email->setMessage($message);
            $this->email->send();
        }
    }

    /**
     * Clean up old notification records
     */
    public function cleanup_old_notifications($daysToKeep = 90)
    {
        $sql = "DELETE FROM rise_inventory_notifications
                WHERE sent_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        return $this->db->query($sql, [$daysToKeep]);
    }

    /**
     * Get notification statistics
     */
    public function get_notification_statistics($startDate = null, $endDate = null)
    {
        $sql = "SELECT
                    notification_type,
                    COUNT(*) as count,
                    DATE(sent_at) as date
                FROM rise_inventory_notifications
                WHERE 1=1";

        if ($startDate) {
            $sql .= " AND sent_at >= " . $this->db->escape($startDate);
        }

        if ($endDate) {
            $sql .= " AND sent_at <= " . $this->db->escape($endDate);
        }

        $sql .= " GROUP BY notification_type, DATE(sent_at)
                  ORDER BY date DESC";

        $query = $this->db->query($sql);
        return $query->getResultArray();
    }

    /**
     * Update user notification preferences
     */
    public function update_notification_preferences($userId, $preferences)
    {
        $existingSettings = $this->get_user_notification_settings($userId);
        
        if ($existingSettings) {
            $settings = array_merge($existingSettings, ['inventory_alerts' => $preferences]);
        } else {
            $settings = ['inventory_alerts' => $preferences];
        }

        $data = [
            'user_id' => $userId,
            'settings_data' => json_encode($settings),
            'updated_at' => get_current_utc_time()
        ];

        // Check if record exists
        $sql = "SELECT id FROM rise_notification_settings WHERE user_id = ?";
        $query = $this->db->query($sql, [$userId]);
        
        if ($query->getNumRows() > 0) {
            $this->db->table('notification_settings')
                     ->where('user_id', $userId)
                     ->update($data);
        } else {
            $data['created_at'] = get_current_utc_time();
            $this->db->table('notification_settings')->insert($data);
        }

        return true;
    }
}