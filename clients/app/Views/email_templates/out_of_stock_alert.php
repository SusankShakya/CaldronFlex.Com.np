<?php
/**
 * Out of Stock Alert Email Template
 * Variables available:
 * - $recipient_name
 * - $item_name
 * - $item_code
 * - $warehouse_name
 * - $current_stock
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
    <title><?php echo app_lang('out_of_stock_alert'); ?> - <?php echo esc($app_title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; background: #f9f9f9; }
        .container { background: #fff; border-radius: 8px; max-width: 600px; margin: 30px auto; padding: 32px 24px; box-shadow: 0 2px 8px #eee; }
        .header { text-align: center; margin-bottom: 24px; }
        .logo { max-height: 48px; }
        .urgent { color: #fff; background: #dc3545; padding: 12px 20px; border-radius: 4px; font-weight: bold; display: inline-block; margin: 16px 0; }
        .stock-table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        .stock-table th, .stock-table td { border: 1px solid #eee; padding: 8px 12px; text-align: left; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 32px; }
        a.button { background: #dc3545; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; }
        .action-required { background: #fff3cd; border: 1px solid #ffeaa7; padding: 16px; border-radius: 4px; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <?php if (!empty($logo_url)): ?>
                <img src="<?php echo esc($logo_url); ?>" alt="<?php echo esc($app_title); ?> Logo" class="logo">
            <?php endif; ?>
            <h2><?php echo app_lang('out_of_stock_alert'); ?></h2>
        </div>
        <p>
            <?php echo app_lang('hello'); ?> <?php echo esc($recipient_name); ?>,
        </p>
        <p style="text-align:center;">
            <span class="urgent"><?php echo app_lang('urgent_out_of_stock'); ?></span>
        </p>
        <div class="action-required">
            <strong><?php echo app_lang('immediate_action_required'); ?></strong><br>
            <?php echo app_lang('item_completely_out_of_stock'); ?>
        </div>
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
                <td style="color: #dc3545; font-weight: bold;"><?php echo esc($current_stock); ?></td>
            </tr>
        </table>
        <p>
            <strong><?php echo app_lang('recommended_actions'); ?>:</strong>
        </p>
        <ul>
            <li><?php echo app_lang('place_purchase_order_immediately'); ?></li>
            <li><?php echo app_lang('check_pending_orders'); ?></li>
            <li><?php echo app_lang('notify_sales_team'); ?></li>
            <li><?php echo app_lang('update_product_availability'); ?></li>
        </ul>
        <p style="text-align:center;">
            <a href="<?php echo esc($dashboard_url); ?>" class="button"><?php echo app_lang('go_to_inventory_dashboard'); ?></a>
        </p>
        <div class="footer">
            <?php echo esc($app_title); ?> &copy; <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>