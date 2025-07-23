<?php
$total_estimates = $lead_overview_info->total_estimates ? $lead_overview_info->total_estimates : 0;
$total_estimate_requests = $lead_overview_info->total_estimate_requests ? $lead_overview_info->total_estimate_requests : 0;
$total_proposals = $lead_overview_info->total_proposals ? $lead_overview_info->total_proposals : 0;
?>
<?php if ($show_estimate_info || $show_estimate_request_info || $show_proposal_info) { ?>
    <div class="card">
        <div class="box">
            <?php if ($show_estimate_info) { ?>
                <div class="box-content">
                    <a href="javascript:;" data-target="#lead-estimates" class="lead-overview-widget-link text-default">
                        <div class="pt-3 pb-3 text-center">
                            <div class="b-r">
                                <h4 class="strong mb-1 mt-0 <?php echo $total_estimates == 0 ? 'text-off text-default' : 'text-primary'; ?>"><?php echo $total_estimates; ?></h4>
                                <span><?php echo app_lang("estimates"); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
            <?php if ($show_estimate_request_info) { ?>
                <div class="box-content">
                    <a href="javascript:;" data-target="#lead-estimate-requests" class="lead-overview-widget-link text-default">
                        <div class="pt-3 pb-3 text-center">
                            <div class="b-r">
                                <h4 class="strong mb-1 mt-0 <?php echo $total_estimate_requests == 0 ? 'text-off text-default' : 'text-danger'; ?>"><?php echo $total_estimate_requests; ?></h4>
                                <span><?php echo app_lang("estimate_requests"); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
            <?php if ($show_proposal_info) { ?>
                <div class="box-content">
                    <a href="javascript:;" data-target="#lead-proposals" class="lead-overview-widget-link text-default">
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
<?php } ?>