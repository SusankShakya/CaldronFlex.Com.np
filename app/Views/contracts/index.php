<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card">
        <div class="page-title clearfix">
            <h1><?php echo app_lang('contracts'); ?></h1>
            <div class="title-button-group">
                <?php 
                if($can_edit_contracts){
                    echo modal_anchor(get_uri("contracts/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_contract'), array("class" => "btn btn-default", "title" => app_lang('add_contract'))); 
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="contract-table" class="display" cellspacing="0" width="100%">   
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var showOptionColumn = <?php echo $can_edit_contracts ? "true" : "false"; ?>;

        $("#contract-table").appTable({
            source: '<?php echo_uri("contracts/list_data") ?>',
            serverSide: true,
            order: [[0, "desc"]],
            smartFilterIdentity: "contracts_list", //a to z and _ only. should be unique to avoid conflicts
            rangeRadioButtons: [{name: "range_radio_button", selectedOption: 'yearly', options: ['monthly', 'yearly', 'custom', 'dynamic'], dynamicRanges:['this_month', 'last_month', 'next_month', 'this_year', 'last_year']}],
            filterDropdown: [{name: "status", class: "w150", options: <?php echo view("contracts/contract_statuses_dropdown"); ?>}, <?php echo $custom_field_filters; ?>],
            columns: [
                {title: '<?php echo app_lang("contract") ?>', "class": "w100", order_by: "id"},
                {title: "<?php echo app_lang("title") ?> ", "class": "w15p all", order_by: "title"},
                {title: "<?php echo app_lang("client") ?>", "class": "w15p all", order_by: "company_name" },
                {title: "<?php echo app_lang("project") ?>", "class": "w15p"},
                {visible: false, searchable: false, order_by: "contract_date"},
                {title: "<?php echo app_lang("contract_date") ?>", "iDataSort": 4, "class": "w10p"},
                {visible: false, searchable: false, order_by: "valid_until"},
                {title: "<?php echo app_lang("valid_until") ?>", "iDataSort": 6, "class": "w10p"},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-right w10p"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"}
<?php echo $custom_field_headers; ?>,
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w150", visible: showOptionColumn}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 8, fieldName: "total_contract_value", dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol}]
        });
    });
</script>