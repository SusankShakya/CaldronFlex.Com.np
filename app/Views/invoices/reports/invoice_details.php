<?php echo get_reports_topbar(); ?>

<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card clearfix">
        <ul id="invoices-summary-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white inner scrollable-tabs" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("invoice_details"); ?></h4></li>
            <li><a role="presentation" data-bs-toggle="tab"  href="javascript:;" data-bs-target="#yearly-invoice-details"><?php echo app_lang("yearly"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("invoices/monthly_invoice_details"); ?>" data-bs-target="#monthly-invoice-details"><?php echo app_lang('monthly'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("invoices/custom_invoice_details"); ?>" data-bs-target="#custom-invoice-details"><?php echo app_lang('custom'); ?></a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="yearly-invoice-details">
                <div class="table-responsive">
                    <table id="yearly-invoice-details-table" class="display" width="100%">
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="monthly-invoice-details"></div>
            <div role="tabpanel" class="tab-pane fade" id="custom-invoice-details"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadInvoiceDetailsTable = function (selector, dateRange) {
        var customDatePicker = "";
        if (dateRange === "custom") {
            customDatePicker = [{startDate: {name: "start_date", value: ""}, endDate: {name: "end_date", value: ""}, showClearButton: true}];

            dateRange = "";
        }
        var currency_dropdown = <?php echo $currencies_dropdown; ?>;

        var filtrDropdown = [];
        if (currency_dropdown.length > 1) {
            filtrDropdown = [
                {name: "currency", class: "w150", options: <?php echo $currencies_dropdown; ?>}
            ];
        }

        var clientsDropdown = <?php echo $clients_dropdown; ?>;

        if (clientsDropdown.length > 1) {
            filtrDropdown.push({name: "client_id", class: "w150", options: clientsDropdown});
        }

        $(selector).appTable({
            source: '<?php echo_uri("invoices/invoice_details_list_data") ?>',
            dateRangeType: dateRange,
            filterDropdown: filtrDropdown,
            rangeDatepicker: customDatePicker,
            columns: [
                {title: '<?php echo app_lang("invoice_id") ?>', "class": "all"},
                {title: '<?php echo app_lang("client") ?>', "class": "all"},
                {title: '<?php echo app_lang("client_vat_or_gst_number") ?>'},
                {title: '<?php echo app_lang("bill_date") ?>'},
                {title: '<?php echo app_lang("due_date") ?>'},
                {title: '<?php echo app_lang("invoice_total") ?>', class: "text-right all"},
                {title: '<?php echo app_lang("discount") ?>', class: "text-right"},
                {title: '<?php echo app_lang("tax") ?>', class: "text-right"},
                {title: '<?php echo app_lang("second_tax") ?>', class: "text-right"},
                {title: '<?php echo app_lang("tax_deducted_at_source") ?>', class: "text-right"},
                {title: '<?php echo app_lang("payment_received") ?>', class: "text-right"},
                {title: '<?php echo app_lang("due") ?>', class: "text-right"},
                {title: '<?php echo app_lang("status") ?>'},
            ],
            printColumns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            xlsColumns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            summation: [
                {column: 5, dataType: 'currency', dynamicSymbol: true},
                {column: 6, dataType: 'currency', dynamicSymbol: true},
                {column: 7, dataType: 'currency', dynamicSymbol: true},
                {column: 8, dataType: 'currency', dynamicSymbol: true},
                {column: 9, dataType: 'currency', dynamicSymbol: true},
                {column: 10, dataType: 'currency', dynamicSymbol: true},
                {column: 11, dataType: 'currency', dynamicSymbol: true},
            ]
        });
    };

    $(document).ready(function () {
        loadInvoiceDetailsTable("#yearly-invoice-details-table", "yearly");
    });
</script>