# Inventory Notification System Integration Guide

## Overview
This guide documents the integration points for the inventory notification system with the Inventory_model and user notification preferences in Rise CRM.

## Table of Contents
1. [Inventory_model Integration](#inventory_model-integration)
2. [User Notification Preferences](#user-notification-preferences)
3. [Service Usage](#service-usage)
4. [Configuration](#configuration)
5. [Cron Job Setup](#cron-job-setup)
6. [Email Templates](#email-templates)
7. [Database Schema](#database-schema)

## Inventory_model Integration

### Required Methods in Inventory_model

Add these methods to `clients/app/Models/Inventory_model.php`:

```php
/**
 * Get items with low stock based on threshold
 * 
 * @param array $options - Optional filters
 * @return array
 */
public function get_low_stock_items($options = []) {
    $where = "";
    
    if (isset($options['warehouse_id'])) {
        $where .= " AND i.warehouse_id = " . $this->db->escapeString($options['warehouse_id']);
    }
    
    if (isset($options['item_id'])) {
        $where .= " AND i.item_id = " . $this->db->escapeString($options['item_id']);
    }
    
    $sql = "SELECT i.*, it.title as item_name, it.unit_type, w.name as warehouse_name,
            CASE 
                WHEN i.quantity = 0 THEN 'out_of_stock'
                WHEN i.quantity <= i.min_stock_level THEN 'critical'
                WHEN i.quantity <= (i.min_stock_level * 1.5) THEN 'low'
                ELSE 'normal'
            END as stock_status
            FROM inventory i
            LEFT JOIN items it ON i.item_id = it.id
            LEFT JOIN warehouses w ON i.warehouse_id = w.id
            WHERE i.deleted = 0 
            AND (i.quantity <= i.min_stock_level OR i.quantity = 0)
            $where
            ORDER BY stock_status DESC, i.quantity ASC";
    
    return $this->db->query($sql)->getResult();
}

/**
 * Get inventory statistics for reporting
 * 
 * @param array $options
 * @return object
 */
public function get_inventory_statistics($options = []) {
    $where = "";
    
    if (isset($options['warehouse_id'])) {
        $where .= " AND warehouse_id = " . $this->db->escapeString($options['warehouse_id']);
    }
    
    $sql = "SELECT 
            COUNT(DISTINCT item_id) as total_items,
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
            SUM(CASE WHEN quantity > 0 AND quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock_count,
            SUM(quantity * unit_cost) as total_inventory_value,
            AVG(quantity) as average_stock_level
            FROM inventory
            WHERE deleted = 0 $where";
    
    return $this->db->query($sql)->getRow();
}

/**
 * Update last notification sent timestamp
 * 
 * @param int $item_id
 * @param int $warehouse_id
 * @return bool
 */
public function update_notification_sent($item_id, $warehouse_id) {
    $data = [
        'last_notification_sent' => get_current_utc_time()
    ];
    
    $where = [
        'item_id' => $item_id,
        'warehouse_id' => $warehouse_id
    ];
    
    return $this->update_where($data, $where);
}
```

### Integration Points

1. **Stock Level Monitoring**
   ```php
   // In your inventory update logic
   $inventory_model = new \App\Models\Inventory_model();
   $notification_service = new \App\Services\Inventory_notification_service();
   
   // After updating inventory
   $inventory_model->update($id, $data);
   
   // Check if notification needed
   $item = $inventory_model->get_one($id);
   if ($item->quantity <= $item->min_stock_level) {
       $notification_service->check_and_notify_single_item($item);
   }
   ```

2. **Bulk Operations**
   ```php
   // After bulk import or update
   $notification_service->check_inventory_levels();
   ```

## User Notification Preferences

### Settings Integration

Add to `clients/app/Views/settings/notifications/index.php`:

```php
<!-- Inventory Notifications Section -->
<div class="form-group">
    <label for="inventory_notifications"><?php echo app_lang('inventory_notifications'); ?></label>
    <div>
        <?php
        echo form_checkbox("enable_inventory_low_stock_email", "1", 
            get_setting("enable_inventory_low_stock_email") ? true : false, 
            "id='enable_inventory_low_stock_email' class='form-check-input'");
        ?>
        <label for="enable_inventory_low_stock_email" class="form-check-label">
            <?php echo app_lang('receive_low_stock_email_notifications'); ?>
        </label>
    </div>
    
    <div class="mt-2">
        <?php
        echo form_checkbox("enable_inventory_reports", "1", 
            get_setting("enable_inventory_reports") ? true : false, 
            "id='enable_inventory_reports' class='form-check-input'");
        ?>
        <label for="enable_inventory_reports" class="form-check-label">
            <?php echo app_lang('receive_weekly_inventory_reports'); ?>
        </label>
    </div>
    
    <div class="mt-2">
        <label><?php echo app_lang('inventory_report_frequency'); ?></label>
        <?php
        echo form_dropdown("inventory_report_frequency", [
            "daily" => app_lang("daily"),
            "weekly" => app_lang("weekly"),
            "monthly" => app_lang("monthly")
        ], get_setting("inventory_report_frequency"), "class='select2 mini'");
        ?>
    </div>
</div>
```

### User-Level Preferences

Add to user profile settings:

```php
// In Users_model or Team_members_model
public function get_inventory_notification_preferences($user_id) {
    $user = $this->get_one($user_id);
    
    return [
        'low_stock_email' => $user->enable_inventory_notifications ?? true,
        'inventory_reports' => $user->enable_inventory_reports ?? false,
        'notification_threshold' => $user->inventory_notification_threshold ?? 'all', // all, critical, none
        'warehouses' => $this->get_user_warehouse_preferences($user_id)
    ];
}

public function update_inventory_notification_preferences($user_id, $preferences) {
    $data = [
        'enable_inventory_notifications' => $preferences['low_stock_email'] ?? 1,
        'enable_inventory_reports' => $preferences['inventory_reports'] ?? 0,
        'inventory_notification_threshold' => $preferences['notification_threshold'] ?? 'all'
    ];
    
    return $this->update($user_id, $data);
}
```

## Service Usage

### Basic Usage

```php
use App\Services\Inventory_notification_service;

// Initialize service
$notification_service = new Inventory_notification_service();

// Check all inventory levels and send notifications
$notification_service->check_inventory_levels();

// Send inventory report to specific users
$notification_service->send_inventory_report([1, 2, 3]); // user IDs

// Check single item
$inventory_item = $inventory_model->get_one($id);
$notification_service->check_and_notify_single_item($inventory_item);

// Clean up old notifications
$notification_service->cleanup_old_notifications(30); // days to keep
```

### Advanced Usage

```php
// Custom notification for specific warehouse
$notification_service->check_inventory_levels(['warehouse_id' => 5]);

// Get notification history
$history = $notification_service->get_notification_history([
    'item_id' => 10,
    'start_date' => '2025-01-01',
    'end_date' => '2025-01-31'
]);

// Batch notifications with custom template
$items = $inventory_model->get_low_stock_items();
$notification_service->send_batch_notification($items, 'custom_template');
```

## Configuration

### Application Settings

Add these settings to your application configuration:

```php
// In app/Config/App.php or settings table
'inventory_notification_enabled' => true,
'inventory_check_frequency' => 'hourly', // hourly, daily, weekly
'inventory_notification_batch_size' => 50,
'inventory_critical_threshold_multiplier' => 0.5, // 50% of min_stock_level
'inventory_warning_threshold_multiplier' => 1.5, // 150% of min_stock_level
'inventory_notification_cooldown_hours' => 24, // Don't send same notification within 24 hours
```

### Email Configuration

```php
// Email settings for inventory notifications
'inventory_notification_from_email' => get_setting('email_sent_from_address'),
'inventory_notification_from_name' => get_setting('email_sent_from_name') . ' - Inventory System',
'inventory_notification_reply_to' => get_setting('email_reply_to') ?: get_setting('email_sent_from_address'),
```

## Cron Job Setup

### Linux/Unix Crontab

```bash
# Check inventory levels every hour
0 * * * * cd /path/to/rise && php index.php cron check_inventory_levels

# Send daily inventory reports at 8 AM
0 8 * * * cd /path/to/rise && php index.php cron send_inventory_reports

# Clean up old notifications weekly
0 2 * * 0 cd /path/to/rise && php index.php cron cleanup_old_notifications
```

### Windows Task Scheduler

Create scheduled tasks with these commands:
```
php.exe "C:\path\to\rise\index.php" cron check_inventory_levels
php.exe "C:\path\to\rise\index.php" cron send_inventory_reports
php.exe "C:\path\to\rise\index.php" cron cleanup_old_notifications
```

## Email Templates

### Available Templates

1. **low_stock_alert.php** - Sent when items reach low stock threshold
2. **out_of_stock_alert.php** - Sent when items are completely out of stock
3. **inventory_report.php** - Periodic inventory status report

### Template Variables

All templates have access to:
- `$items` - Array of inventory items
- `$user` - Recipient user object
- `$company` - Company information
- `$statistics` - Inventory statistics (in report template)

### Customizing Templates

Templates support Rise CRM's language system:
```php
app_lang('low_stock_alert_subject')
app_lang('item_below_minimum_stock')
```

## Database Schema

### inventory_notifications Table

```sql
CREATE TABLE `inventory_notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `item_id` int(11) NOT NULL,
    `warehouse_id` int(11) NOT NULL,
    `notification_type` enum('low_stock','out_of_stock','restock','report') NOT NULL,
    `alert_level` enum('warning','critical','info') DEFAULT 'warning',
    `quantity_at_notification` decimal(10,2) NOT NULL,
    `min_stock_at_notification` decimal(10,2) NOT NULL,
    `recipients` text,
    `sent_at` datetime NOT NULL,
    `created_by` int(11) DEFAULT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_item_warehouse` (`item_id`,`warehouse_id`),
    KEY `idx_sent_at` (`sent_at`),
    KEY `idx_notification_type` (`notification_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

## API Endpoints (Optional)

If implementing API access:

```php
// routes/api.php
$routes->group('api/v1/inventory', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->get('notifications', 'Inventory_api::get_notifications');
    $routes->post('notifications/test', 'Inventory_api::test_notification');
    $routes->get('low-stock', 'Inventory_api::get_low_stock_items');
    $routes->put('notifications/preferences', 'Inventory_api::update_preferences');
});
```

## Troubleshooting

### Common Issues

1. **Notifications not sending**
   - Check cron job is running: `php index.php cron check_inventory_levels`
   - Verify email settings in Settings > Email
   - Check notification history in database

2. **Duplicate notifications**
   - Adjust `inventory_notification_cooldown_hours` setting
   - Check cron frequency

3. **Performance issues**
   - Increase `inventory_notification_batch_size`
   - Add indexes to inventory table if needed
   - Use warehouse-specific checks

### Debug Mode

Enable debug logging:
```php
// In Inventory_notification_service constructor
$this->debug = true; // Logs to app/logs/inventory_notifications.log
```

## Best Practices

1. **Set appropriate thresholds** - Min stock levels should reflect actual lead times
2. **Use batch processing** - For large inventories, process in batches
3. **Implement rate limiting** - Avoid notification fatigue
4. **Regular cleanup** - Remove old notification records
5. **Monitor performance** - Track notification sending times
6. **Test thoroughly** - Use test mode before production

## Support

For issues or questions:
1. Check Rise CRM documentation
2. Review error logs in `app/logs/`
3. Verify all integration points are implemented
4. Contact support with notification history details