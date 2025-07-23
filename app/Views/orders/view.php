<div class="page-content order-details-view clearfix">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div id="order-details-top-bar">
                    <?php echo view("orders/order_top_bar"); ?>
                </div>

                <div class="details-view-wrapper d-flex">
                    <div class="w-100">
                        <?php echo view("orders/details"); ?>
                    </div>
                    <div class="flex-shrink-0 details-view-right-section">
                        <div id="order-details-order-info">
                            <?php echo view("orders/order_info"); ?>
                        </div>

                        <?php echo view("orders/order_actions"); ?>

                        <?php
                        if ($can_view_invoices) {
                            echo view("orders/order_invoice_lists");

                            echo view("orders/order_invoice_payment_list");
                        }
                        ?>

                        <?php echo view("orders/tasks/index"); ?>

                        <?php if (can_access_reminders_module()) { ?>
                            <div class="card reminders-card" id="order-reminders">
                                <div class="card-header fw-bold">
                                    <i data-feather="clock" class="icon-16"></i> &nbsp;<?php echo app_lang("reminders") . " (" . app_lang('private') . ")"; ?>
                                </div>
                                <div class="card-body">
                                    <?php echo view("reminders/reminders_view_data", array("order_id" => $order_info->id, "hide_form" => true, "reminder_view_type" => "order")); ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo view("orders/update_order_status_script", array("details_view" => true)); ?>