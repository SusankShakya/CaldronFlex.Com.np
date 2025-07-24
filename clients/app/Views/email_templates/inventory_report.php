<?php
/**
 * Inventory Report Email Template
 * Variables available:
 * - $recipient_name
 * - $report_period (e.g., "Weekly", "Monthly")
 * - $report_date
 * - $total_items
 * - $low_stock_items (array)
 * - $out_of_stock_items (array)
 * - $warehouse_summary (array)
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
    <title><?php echo app_lang('inventory_report'); ?> - <?php echo esc($app_title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; background: #f9f9f9; }
        .container { background: #fff; border-radius: 8px; max-width: 800px; margin: 30px auto; padding: 32px 24px; box-shadow: 0 2px 8px #eee; }
        .header { text-align: center; margin-bottom: 24px; }
        .logo { max-height: 48px; }
        .summary-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 16px; margin: 16px 0; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin: 16px 0; }
        .stat-card { background: #fff; border: 1px solid #e9ecef; border-radius: 4px; padding: 16px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #495057; }
        .stat-label { font-size: 14px; color: #6c757d; margin-top: 4px; }
        .alert-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .alert-table th, .alert-table td { border: 1px solid #dee2e6; padding: 8px 12px; text-align: left; }
        .alert-table th { background: #f8f9fa; font-weight: bold; }
        .low-stock { color: #b85c00; }
        .out-of-stock { color: #dc3545; font-weight: bold; }
        .warehouse-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .warehouse-table th, .warehouse-table td { border: 1px solid #dee2e6; padding: 8px 12px; text-align: left; }
        .warehouse-table th { background: #f8f9fa; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 32px; }
        a.button { background: #007bff; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; }
        .section-title { font-size: 18px; font-weight: bold; margin: 24px 0 12px 0; color: #343a40; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <?php if (!empty($logo_url)): ?>
                <img src="<?php echo esc($logo_url); ?>" alt="<?php echo esc($app_title); ?> Logo" class="logo">
            <?php endif; ?>
            <h2><?php echo esc($report_period); ?> <?php echo app_lang('inventory_report'); ?></h2>
            <p style="color: #6c757d;"><?php echo app_lang('report_generated_on'); ?>: <?php echo esc($report_date); ?></p>
        </div>
        
        <p>
            <?php echo app_lang('hello'); ?> <?php echo esc($recipient_name); ?>,
        </p>
        <p>
            <?php echo app_lang('inventory_report_intro'); ?>
        </p>
        
        <div class="summary-box">
            <h3 style="margin-top: 0;"><?php echo app_lang('inventory_summary'); ?></h3>
            <div class="summary-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo esc($total_items); ?></div>
                    <div class="stat-label"><?php echo app_lang('total_items'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number low-stock"><?php echo count($low_stock_items); ?></div>
                    <div class="stat-label"><?php echo app_lang('low_stock_items'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number out-of-stock"><?php echo count($out_of_stock_items); ?></div>
                    <div class="stat-label"><?php echo app_lang('out_of_stock_items'); ?></div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($out_of_stock_items)): ?>
        <div class="section-title"><?php echo app_lang('out_of_stock_items'); ?></div>
        <table class="alert-table">
            <thead>
                <tr>
                    <th><?php echo app_lang('item_code'); ?></th>
                    <th><?php echo app_lang('item_name'); ?></th>
                    <th><?php echo app_lang('warehouse'); ?></th>
                    <th><?php echo app_lang('current_stock'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($out_of_stock_items as $item): ?>
                <tr>
                    <td><?php echo esc($item['item_code']); ?></td>
                    <td><?php echo esc($item['item_name']); ?></td>
                    <td><?php echo esc($item['warehouse_name']); ?></td>
                    <td class="out-of-stock"><?php echo esc($item['current_stock']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <?php if (!empty($low_stock_items)): ?>
        <div class="section-title"><?php echo app_lang('low_stock_items'); ?></div>
        <table class="alert-table">
            <thead>
                <tr>
                    <th><?php echo app_lang('item_code'); ?></th>
                    <th><?php echo app_lang('item_name'); ?></th>
                    <th><?php echo app_lang('warehouse'); ?></th>
                    <th><?php echo app_lang('current_stock'); ?></th>
                    <th><?php echo app_lang('warning_threshold'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($low_stock_items as $item): ?>
                <tr>
                    <td><?php echo esc($item['item_code']); ?></td>
                    <td><?php echo esc($item['item_name']); ?></td>
                    <td><?php echo esc($item['warehouse_name']); ?></td>
                    <td class="low-stock"><?php echo esc($item['current_stock']); ?></td>
                    <td><?php echo esc($item['warning_threshold']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <?php if (!empty($warehouse_summary)): ?>
        <div class="section-title"><?php echo app_lang('warehouse_summary'); ?></div>
        <table class="warehouse-table">
            <thead>
                <tr>
                    <th><?php echo app_lang('warehouse'); ?></th>
                    <th><?php echo app_lang('total_items'); ?></th>
                    <th><?php echo app_lang('low_stock_count'); ?></th>
                    <th><?php echo app_lang('out_of_stock_count'); ?></th>
                    <th><?php echo app_lang('inventory_health'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($warehouse_summary as $warehouse): ?>
                <tr>
                    <td><?php echo esc($warehouse['name']); ?></td>
                    <td><?php echo esc($warehouse['total_items']); ?></td>
                    <td class="low-stock"><?php echo esc($warehouse['low_stock_count']); ?></td>
                    <td class="out-of-stock"><?php echo esc($warehouse['out_of_stock_count']); ?></td>
                    <td>
                        <?php 
                        $health_percentage = $warehouse['health_percentage'];
                        $health_color = $health_percentage >= 80 ? '#28a745' : ($health_percentage >= 60 ? '#ffc107' : '#dc3545');
                        ?>
                        <span style="color: <?php echo $health_color; ?>; font-weight: bold;">
                            <?php echo esc($health_percentage); ?>%
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <p style="margin-top: 32px;">
            <?php echo app_lang('inventory_report_footer'); ?>
        </p>
        
        <p style="text-align:center;">
            <a href="<?php echo esc($dashboard_url); ?>" class="button"><?php echo app_lang('view_full_inventory_dashboard'); ?></a>
        </p>
        
        <div class="footer">
            <?php echo app_lang('automated_report_notice'); ?><br>
            <?php echo esc($app_title); ?> &copy; <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>