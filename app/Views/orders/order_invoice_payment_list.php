<div class="card">
    <div class="card-header fw-bold">
        <i data-feather="credit-card" class="icon-16"></i> &nbsp;<?php echo app_lang("invoice_payment_list"); ?>
    </div>
    <div class="table-responsive">
        <table id="order-details-page-invoice-payment-table" class="display no-thead b-b-only no-hover hide-dtr-control" cellspacing="0" width="100%">            
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#order-details-page-invoice-payment-table").appTable({
            source: '<?php echo_uri("invoice_payments/payment_list_data_of_order/" . $order_info->id . "/$order_info->client_id") ?>' + '/1',
            order: [[0, "desc"]],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            columns: [
                {targets: [0], visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("payment_date") ?> ', "class": "w15p all", "iDataSort": 1},
                {title: '<?php echo app_lang("payment_method") ?>', "class": "w15p"},
                {title: '<?php echo app_lang("note") ?>', "class": "w15p"},
                {title: '<?php echo app_lang("amount") ?>', "class": "text-right w15p"},
                {visible: false, searchable: false}
            ]
        });
    });
</script>