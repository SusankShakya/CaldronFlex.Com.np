<?php if (isset($page_type) && $page_type === "full") { ?>
    <div id="page-content" class="page-wrapper clearfix">
    <?php } ?>

    <div class="card">
        <?php if (isset($page_type) && $page_type === "full") { ?>
            <div class="page-title clearfix">
                <h1><?php echo app_lang('subscriptions'); ?></h1>
            </div>
        <?php } else { ?>
            <div class="tab-title clearfix">
                <h4><?php echo app_lang('subscriptions'); ?></h4>
                <div class="title-button-group">
                    <?php
                    if ($can_edit_subscriptions) {
                        echo modal_anchor(get_uri("subscriptions/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_subscription'), array("class" => "btn btn-default mb0", "data-post-client_id" => $client_id, "title" => app_lang('add_subscription')));
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <div class="table-responsive">
            <table id="subscription-table" class="display" width="100%">
            </table>
        </div>
    </div>
    <?php if (isset($page_type) && $page_type === "full") { ?>
    </div>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function () {
        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        $("#subscription-table").appTable({
            source: '<?php echo_uri("subscriptions/subscription_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("subscription_id") ?>", "class": "w10p"},
                {title: "<?php echo app_lang("title") ?> ", "class": "all"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("first_billing_date") ?>", "iDataSort": 5, "class": "w10p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("next_billing_date") ?>", "iDataSort": 7, "class": "w10p"},
                {title: "<?php echo app_lang("repeat_every") ?>", "class": "w10p text-center"},
                {title: "<?php echo app_lang("cycles") ?>", "class": "w10p text-center"},
                {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"},
                {title: "<?php echo app_lang("amount") ?>", "class": "w10p text-right"}
<?php echo $custom_field_headers; ?>,
                {visible: false, searchable: false}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 6, 8, 9, 10, 11, 12], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 6, 7, 8, 9, 10], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 12, dataType: 'currency', currencySymbol: currencySymbol}]
        });
    });
</script>