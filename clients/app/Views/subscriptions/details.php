<div class="clearfix default-bg details-view-container">
    <div class="card b-t w-100" id="subscription-item-section">
        <div class="card-header fw-bold">
            <span class="inline-block mt-1">
                <i data-feather="list" class="icon-16"></i> &nbsp;<?php echo app_lang("subscription_items"); ?>
            </span>
        </div>

        <div class="table-responsive mt15 pl15 pr15">
            <table id="subscription-item-table" class="display" width="100%">
            </table>
        </div>

        <div class="clearfix">
            <?php if (!$has_item_in_this_subscription && $subscription_info->status != "active") { ?>
                <div class="float-start mt20 ml15" id="subscription-add-item-btn">
                    <?php echo modal_anchor(get_uri("subscriptions/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-primary text-white", "title" => app_lang('add_item'), "data-post-subscription_id" => $subscription_info->id)); ?>
                </div>
            <?php } ?>
            <div class="float-end pr15" id="subscription-total-section">
                <?php echo view("subscriptions/subscription_total_section", array("subscription_id" => $subscription_info->id, "can_edit_subscriptions" => $can_edit_subscriptions)); ?>
            </div>
        </div>

        <?php
        $files = @unserialize($subscription_info->files);
        if ($files && is_array($files) && count($files)) {
        ?>
            <div class="clearfix">
                <div class="col-md-12 mt20 row">
                    <p class="b-t"></p>
                    <div class="mb5 strong"><?php echo app_lang("files"); ?></div>
                    <?php
                    echo view("includes/file_list", array("files" => $subscription_info->files, "model_info" => $subscription_info, "mode_type" => "view", "context" => "subscriptions"));
                    ?>
                </div>
            </div>
        <?php } ?>

        <p class="b-t b-info pt10 m15"><?php echo custom_nl2br($subscription_info->note); ?></p>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var optionVisibility = false;
<?php if ($can_edit_subscriptions && $subscription_status !== "active") { ?>
            optionVisibility = true;
<?php } ?>

        $("#subscription-item-table").appTable({
            source: '<?php echo_uri("subscriptions/item_list_data/" . $subscription_info->id . "/") ?>',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {title: '<?php echo app_lang("item") ?> ', sortable: false, "class": "all"},
                {title: '<?php echo app_lang("quantity") ?>', "class": "text-right w15p", sortable: false},
                {title: '<?php echo app_lang("rate") ?>', "class": "text-right w15p", sortable: false},
                {title: '<?php echo app_lang("total") ?>', "class": "text-right w15p all", sortable: false},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100", sortable: false, visible: optionVisibility}
            ]
        });

        //modify the delete confirmation texts
        $("#confirmationModalTitle").html("<?php echo app_lang('cancel') . "?"; ?>");
        $("#confirmDeleteButton").html("<i data-feather='x' class='icon-16'></i> <?php echo app_lang("cancel"); ?>");
    });
</script>