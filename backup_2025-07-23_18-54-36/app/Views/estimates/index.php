<div id="page-content" class="page-wrapper clearfix grid-button">

    <div class="card clearfix">
        <div class="page-title clearfix">
            <h1><?php echo app_lang('estimates'); ?></h1>
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_estimate'), array("class" => "btn btn-default", "title" => app_lang('add_estimate'))); ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="estimates-table" class="display" cellspacing="0" width="100%">   
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var showCommentOption = false;
        if ("<?php echo get_setting("enable_comments_on_estimates") == "1" ?>") {
            showCommentOption = true;
        }

        var idColumnClass = "w10p";
        if (isMobile()) {
            idColumnClass = "";
        }

        $("#estimates-table").appTable({
            source: '<?php echo_uri("estimates/list_data") ?>',
            order: [[0, "desc"]],
            smartFilterIdentity: "estimates_list", //a to z and _ only. should be unique to avoid conflicts
            rangeRadioButtons: [{name: "range_radio_button", selectedOption: 'yearly', options: ['monthly', 'yearly', 'custom', 'dynamic'], dynamicRanges:['this_month', 'last_month', 'next_month', 'this_year', 'last_year']}],
            filterDropdown: [{name: "status", class: "w150", options: <?php echo view("estimates/estimate_statuses_dropdown"); ?>}, <?php echo $custom_field_filters; ?>],
            columns: [
                {title: "<?php echo app_lang("estimate") ?> ", "class": idColumnClass + "all"},
                {title: "<?php echo app_lang("client") ?>", "class": "all"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("estimate_date") ?>", "iDataSort": 2, "class": "w20p"},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-right w20p"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"},
                {visible: showCommentOption, title: '<i data-feather="message-circle" class="icon-16"></i>', "class": "text-center w50"}
<?php echo $custom_field_headers; ?>,
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w150"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 4, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol, conversionRate: <?php echo $conversion_rate; ?>}]
        });
    });

</script>