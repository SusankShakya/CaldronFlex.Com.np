<div class="card">
    <div class="card-header fw-bold">
        <i data-feather="file-text" class="icon-16"></i> &nbsp;<?php echo app_lang("invoices"); ?>
    </div>
    <div class="table-responsive">
        <table id="order-details-page-invoices-table" class="display no-thead b-b-only no-hover hide-dtr-control" cellspacing="0" width="100%">            
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var currencySymbol = "<?php echo $order_info->currency_symbol; ?>";
        $("#order-details-page-invoices-table").appTable({
            source: '<?php echo_uri("invoices/invoice_list_data_of_order/" . $order_info->id . "/$order_info->client_id") ?>' + '/1',
            order: [[0, "desc"]],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("invoice_id") ?>", "class": "w10p all", "iDataSort": 0},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("bill_date") ?>", "class": "w10p", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("due_date") ?>", "class": "w10p", "iDataSort": 6},
                {title: "<?php echo app_lang("total_invoiced") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("payment_received") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("due") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"}
<?php echo $custom_field_headers_of_invoice; ?>,
                {visible: false, searchable: false}
            ],
            printColumns: combineCustomFieldsColumns([1, 5, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers_of_invoice; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 5, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers_of_invoice; ?>')
        });
    });
</script>