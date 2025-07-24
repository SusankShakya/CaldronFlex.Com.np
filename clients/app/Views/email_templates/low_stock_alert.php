<?php
/**
 * Low Stock Alert Email Template
 * Variables available:
 * - $recipient_name
 * - $item_name
 * - $item_code
 * - $warehouse_name
 * - $current_stock
 * - $warning_threshold
 * - $critical_threshold
 * - $alert_level
 * - $app_title
 * - $logo_url
 * - $dashboard_url
 * 
 * Internationalization: Use app_lang() for static text.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo app_lang('low_stock_alert'); ?> - <?php echo esc($app_title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; background: #f9f9f9; }
        .container { background: #fff; border-radius: 8px; max-width: 600px; margin: 30px auto; padding: 32px 24px; box-shadow: 0 2px 8px #eee; }
        .header { text-align: center; margin-bottom: 24px; }
        .logo { max-height: 48px; }
        .alert { color: #b85c00; font-weight: bold; }
        .critical { color: #b80000; font-weight: bold; }
        .stock-table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        .stock-table th, .stock-table td { border: 1px solid #eee; padding: 8px 12px; text-align: left; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 32px; }
        a.button { background: #007bff; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <?php if (!empty($logo_url)): ?>
                <img src="<?php echo esc($logo_url); ?>" alt="<?php echo esc($app_title); ?> Logo" class="logo">
            <?php endif; ?>
            <h2><?php echo app_lang('low_stock_alert'); ?></h2>
        </div>
        <p>
            <?php echo app_lang('hello'); ?> <?php echo esc($recipient_name); ?>,
        </p>
        <p>
            <?php
                if ($alert_level === 'critical') {
                    echo '<span class="critical">' . app_lang('critical_stock_level_reached') . '</span>';
                } else {
                    echo '<span class="alert">' . app_lang('low_stock_warning') . '</span>';
                }
            ?>
        </p>
        <table class="stock-table">
            <tr>
                <th><?php echo app_lang('item'); ?></th>
                <td><?php echo esc($item_name); ?> (<?php echo esc($item_code); ?>)</td>
            </tr>
            <tr>
                <th><?php echo app_lang('warehouse'); ?></th>
                <td><?php echo esc($warehouse_name); ?></td>
            </tr>
            <tr>
                <th><?php echo app_lang('current_stock'); ?></th>
                <td><?php echo esc($current_stock); ?></td>
            </tr>
            <tr>
                <th><?php echo app_lang('warning_threshold'); ?></th>
                <td><?php echo esc($warning_threshold); ?></td>
            </tr>
            <tr>
                <th><?php echo app_lang('critical_threshold'); ?></th>
                <td><?php echo esc($critical_threshold); ?></td>
            </tr>
        </table>
        <p>
            <?php echo app_lang('please_review_inventory'); ?>
        </p>
        <p style="text-align:center;">
            <a href="<?php echo esc($dashboard_url); ?>" class="button"><?php echo app_lang('view_inventory_dashboard'); ?></a>
        </p>
        <div class="footer">
            <?php echo esc($app_title); ?> &copy; <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>