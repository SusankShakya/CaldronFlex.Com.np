<div class="card">
    <div class="card-header fw-bold border-bottom-0">
        <i data-feather="file-text" class="icon-16"></i> &nbsp;<?php echo app_lang("sub_invoices"); ?>
    </div>

    <div class="table-responsive">
        <table id="invoice-details-page-sub-invoice-table" class="display no-thead b-t b-b-only no-hover hide-dtr-control" width="100%">
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#invoice-details-page-sub-invoice-table").appTable({
            source: '<?php echo_uri("invoices/sub_invoices_list_data/" . $invoice_id) ?>' + '/1',
            order: [[0, "desc"]],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("invoice_id") ?>", "class": "w10p all", "iDataSort": 0},
                {visible: false},
                {visible: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("bill_date") ?>", "class": "w10p", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("due_date") ?>", "class": "w10p", "iDataSort": 6},
                {title: "<?php echo app_lang("total_invoiced") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("payment_received") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("due") ?>", "class": "w10p text-right "},
                {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"}
            ]
        });

    });
</script>