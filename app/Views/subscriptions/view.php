<div class="page-content subscription-details-view clearfix">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div id="subscription-details-top-bar">
                    <?php echo view("subscriptions/subscription_top_bar"); ?>
                </div>

                <div class="details-view-wrapper d-flex">
                    <div class="w-100">
                        <?php echo view("subscriptions/details"); ?>
                        <?php if ($can_view_invoices) {
                            echo view("subscriptions/invoices/index");
                        } ?>
                    </div>
                    <div class="flex-shrink-0 details-view-right-section">
                        <?php echo view("subscriptions/subscription_info"); ?>

                        <div id="subscription-details-subscription-custom-fields-info">
                            <?php echo view("subscriptions/subscription_custom_fields_info"); ?>
                        </div>

                        <div id="subscription-tasks-section">
                            <?php echo view("subscriptions/tasks/index"); ?>
                        </div>

                        <?php if (can_access_reminders_module()) { ?>
                            <div class="card reminders-card" id="subscription-reminders">
                                <div class="card-header fw-bold">
                                    <i data-feather="clock" class="icon-16"></i> &nbsp;<?php echo app_lang("reminders") . " (" . app_lang('private') . ")"; ?>
                                </div>
                                <div class="card-body">
                                    <?php echo view("reminders/reminders_view_data", array("subscription_id" => $subscription_info->id, "hide_form" => true, "reminder_view_type" => "subscription")); ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('body').on('click', '[data-act=subscription-modifier]', function(e) {
            $(this).appModifier({
                dropdownData: {
                    labels: <?php echo json_encode($label_suggestions); ?>
                }
            });
            return false;
        });

        appContentBuilder.init("<?php echo get_uri('subscriptions/view/' . $subscription_info->id); ?>", {
            id: "subscription-details-page-builder",
            data: {
                view_type: "subscription_meta"
            },
            reloadHooks: [{
                type: "app_modifier",
                group: "subscription_info"
            }],
            reload: function(bind, result) {
                bind("#subscription-details-top-bar", result.top_bar);
            }
        });

    });
</script>