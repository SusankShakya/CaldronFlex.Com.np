<?php
$total_projects = $client_overview_info->total_projects ? $client_overview_info->total_projects : 0;
$total_proposals = $client_overview_info->total_proposals ? $client_overview_info->total_proposals : 0;
$total_subscriptions = $client_overview_info->total_subscriptions ? $client_overview_info->total_subscriptions : 0;
$total_estimates = $client_overview_info->total_estimates ? $client_overview_info->total_estimates : 0;
$total_orders = $client_overview_info->total_orders ? $client_overview_info->total_orders : 0;
?>

<div class="card">
    <div class="box">
        <?php if ($show_project_info) { ?>
            <div class="box-content">
                <a href="javascript:;" data-target="#client-projects" class="client-overview-widget-link text-default">
                    <div class="pt-3 pb-3 text-center">
                        <div class="b-r">
                            <h4 class="strong mb-1 mt-0 <?php echo $total_projects == 0 ? 'text-off text-default' : ''; ?>" style="color: #01B393;"><?php echo $total_projects; ?></h4>
                            <span><?php echo app_lang("projects"); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>
        <?php if ($show_subscription_info) { ?>
            <div class="box-content">
                <a href="javascript:;" data-target="#client-subscriptions" class="client-overview-widget-link text-default">
                    <div class="pt-3 pb-3 text-center">
                        <div class="b-r">
                            <h4 class="strong mb-1 mt-0 <?php echo $total_subscriptions == 0 ? 'text-off text-default' : 'text-danger'; ?>"><?php echo $total_subscriptions; ?></h4>
                            <span><?php echo app_lang("subscriptions"); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>
        <?php if ($show_order_info) { ?>
            <div class="box-content">
                <a href="javascript:;" data-target="#client-orders" class="client-overview-widget-link text-default">
                    <div class="pt-3 pb-3 text-center">
                        <div class="b-r">
                            <h4 class="strong mb-1 mt-0 <?php echo $total_orders == 0 ? 'text-off text-default' : 'text-success'; ?>"><?php echo $total_orders; ?></h4>
                            <span><?php echo app_lang("orders"); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>
        <?php if ($show_estimate_info) { ?>
            <div class="box-content">
                <a href="javascript:;" data-target="#client-estimates" class="client-overview-widget-link text-default">
                    <div class="pt-3 pb-3 text-center">
                        <div class="b-r">
                            <h4 class="strong mb-1 mt-0 <?php echo $total_estimates == 0 ? 'text-off text-default' : 'text-primary'; ?>"><?php echo $total_estimates; ?></h4>
                            <span><?php echo app_lang("estimates"); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>
        <?php if ($show_proposal_info) { ?>
            <div class="box-content">
                <a href="javascript:;" data-target="#client-proposals" class="client-overview-widget-link text-default">
                    <div class="pt-3 pb-3 text-center">
                        <div class="">
                            <h4 class="strong mb-1 mt-0 <?php echo $total_proposals == 0 ? 'text-off text-default' : 'text-warning'; ?>"><?php echo $total_proposals; ?></h4>
                            <span><?php echo app_lang("proposals"); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>
    </div>
</div>