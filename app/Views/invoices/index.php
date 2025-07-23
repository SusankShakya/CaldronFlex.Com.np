<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card clearfix">
        <ul id="invoices-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab">
                <h4 class="pl15 pt10 pr15"><?php echo app_lang("invoices"); ?></h4>
            </li>
            <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#invoices-list"><?php echo app_lang("list"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("invoices/recurring/"); ?>" data-bs-target="#recurring-invoices"><?php echo app_lang('recurring'); ?></a></li>
            <div class="tab-title clearfix no-border invoices-view">
                <div class="title-button-group">
                    <?php if ($can_edit_invoices) { ?>
                        <?php echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default mb0", "title" => app_lang('manage_labels'), "data-post-type" => "invoice")); ?>
                        <?php echo modal_anchor(get_uri("invoice_payments/payment_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_payment'), array("class" => "btn btn-default mb0", "title" => app_lang('add_payment'))); ?>
                        <?php echo modal_anchor(get_uri("invoices/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_invoice'), array("class" => "btn btn-default mb0", "title" => app_lang('add_invoice'))); ?>
                    <?php } ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="invoices-list">
                <div class="table-responsive">
                    <table id="invoice-list-table" class="display" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="recurring-invoices"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var ignoreSavedFilter = false;

        var optionVisibility = false;
        if ("<?php echo $can_edit_invoices ?>") {
            optionVisibility = true;
        }

        var idColumnClass = "w10p";
        if (isMobile()) {
            idColumnClass = "";
        }

        var RangeButtonSelectedOption = 'monthly';
        var tab = "<?php echo $tab; ?>";
        if (tab === "custom") {
            var ignoreSavedFilter = true;
            RangeButtonSelectedOption = 'custom';
        }

        var status = "<?php echo $status; ?>";
        var invoice_statuses_dropdown = <?php echo view("invoices/invoice_statuses_dropdown"); ?>;
        if (status !== "") {
            var filterIndex = invoice_statuses_dropdown.findIndex(x => x.id === status);
            if ([filterIndex] > -1) {
                //match found
                invoice_statuses_dropdown[filterIndex].isSelected = true;

                var ignoreSavedFilter = true;
            }
        }

        $("#invoice-list-table").appTable({
            source: '<?php echo_uri("invoices/list_data") ?>',
            order: [[0, "desc"]],
            smartFilterIdentity: 'invoice_list', //a to z and _ only. should be unique to avoid conflicts
            ignoreSavedFilter: ignoreSavedFilter,
            rangeRadioButtons: [{name: "range_radio_button", selectedOption: RangeButtonSelectedOption, options: ['monthly', 'yearly', 'custom', 'dynamic'], dynamicRanges:['this_month', 'last_month', 'next_month', 'this_year', 'last_year']}],
            filterDropdown: [
            {name: "type", class: "w150", options: <?php echo $types_dropdown; ?>},
            {name: "status", class: "w150", options: invoice_statuses_dropdown}
            <?php if ($currencies_dropdown) { ?>
                , {name: "currency", class: "w150", options: <?php echo $currencies_dropdown; ?>}
            <?php } ?>
            , <?php echo $custom_field_filters; ?>
            ],
            columns: [
            {visible: false, searchable: false},
            {title: "<?php echo app_lang("invoice_id") ?>", "class": idColumnClass + " all", "iDataSort": 0},
            {title: "<?php echo app_lang("client") ?>", "class": "all"},
            {title: "<?php echo app_lang("project") ?>", "class": "w15p"},
            {visible: false, searchable: false},
            {title: "<?php echo app_lang("bill_date") ?>", "class": "w10p", "iDataSort": 4},
            {visible: false, searchable: false},
            {title: "<?php echo app_lang("due_date") ?>", "class": "w10p", "iDataSort": 6},
            {title: "<?php echo app_lang("total_invoiced") ?>", "class": "w10p text-right"},
            {title: "<?php echo app_lang("payment_received") ?>", "class": "w10p text-right"},
            {title: "<?php echo app_lang("due") ?>", "class": "w10p text-right"},
            {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"}
            <?php echo $custom_field_headers; ?>,
            {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center w100", visible: optionVisibility}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 3, 4, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers; ?>'),
            summation: [
            {column: 8, dataType: 'currency', conversionRate: <?php echo $conversion_rate; ?>},
            {column: 9, dataType: 'currency', conversionRate: <?php echo $conversion_rate; ?>},
            {column: 10, dataType: 'currency', conversionRate: <?php echo $conversion_rate; ?>}
            ]
    });

});
</script>