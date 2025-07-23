<div class="card">
    <div class="card-header fw-bold">
        <i data-feather="credit-card" class="icon-16"></i> &nbsp;<?php echo app_lang("payments"); ?>
    </div>

    <?php if ($invoice_status !== "cancelled" && $invoice_info->status !== "credited" && $can_edit_invoices) { ?>
        <div class="card-body">
            <?php
            echo modal_anchor(get_uri("invoice_payments/payment_modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_payment'), array("class" => "inline-block", "data-post-invoice_id" => $invoice_id, "title" => app_lang('add_payment')));
            ?>
        </div>
    <?php } ?>

    <div class="table-responsive">
        <table id="invoice-details-page-payment-table" class="display no-thead b-t b-b-only no-hover hide-dtr-control" width="100%">
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var optionVisibility = false;
        if ("<?php echo $can_edit_invoices ?>") {
            optionVisibility = true;
        }

        $("#invoice-details-page-payment-table").appTable({
            source: '<?php echo_uri("invoice_payments/payment_list_data/" . $invoice_id) ?>' + '/1',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            reloadHooks: [{
                    type: "app_form",
                    id: "invoice-payment-form",
                    filter: {invoice_id: "<?php echo $invoice_id ?>"},
                }
            ],
            columns: [
                {targets: [0], visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("payment_date") ?> ', "class": "w15p all", "iDataSort": 1},
                {title: '<?php echo app_lang("payment_method") ?>', "class": "w15p"},
                {title: '<?php echo app_lang("note") ?>', "class": "text-wrap"},
                {title: '<?php echo app_lang("amount") ?>', "class": "text-right w15p"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100", visible: optionVisibility}
            ]
        });
    });
</script>