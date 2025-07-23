<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix">
            <h1><?php echo app_lang('orders'); ?></h1>
            <div class="title-button-group">
                <?php echo js_anchor("<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_order'), array("class" => "btn btn-default", "id" => "add-order-btn")); ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="orders-table" class="display" cellspacing="0" width="100%">
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#orders-table").appTable({
            source: '<?php echo_uri("orders/list_data") ?>',
            order: [[0, "desc"]],
            smartFilterIdentity: "orders_list", //a to z and _ only. should be unique to avoid conflicts
            rangeRadioButtons: [{name: "range_radio_button", selectedOption: 'monthly', options: ['monthly', 'yearly', 'custom', 'dynamic'], dynamicRanges:['this_month', 'last_month', 'next_month', 'this_year', 'last_year']}],
            filterDropdown: [{name: "status_id", class: "w150", options: <?php echo view("orders/order_statuses_dropdown"); ?>}, <?php echo $custom_field_filters; ?>],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("order") ?> ", "class": "w10p all", "iDataSort": 0},
                {title: "<?php echo app_lang("client") ?>", "class": "w20p all"},
                {title: "<?php echo app_lang("invoices") ?>", "class": "w20p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("order_date") ?>", "iDataSort": 4, "class": "w20p"},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-right w10p"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"}
<?php echo $custom_field_headers; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 4, 5, 6], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 4, 5, 6], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 6, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol}]
        });


        $("#add-order-btn").click(function () {
            window.location.href = "<?php echo get_uri("store"); ?>";
        });
    });

</script>

<?php echo view("orders/update_order_status_script"); ?>