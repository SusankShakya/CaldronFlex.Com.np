<div class="">
    <div class="w-100 mt20">
        <div id="client-wallet-summary">
            <?php echo view("clients/client_wallet/client_wallet_info"); ?>
        </div>
    </div>
    <div class="w-100">
        <div class="card">
            <div class="table-responsive">
                <table id="client-wallet-table" class="display" width="100%">
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        appContentBuilder.init("<?php echo get_uri('invoice_payments/client_wallet/' . $client_id); ?>", {
            id: "client-wallet-summary-builder",
            data: {
                view_type: "wallet_summary"
            },
            reloadHooks: [{
                    type: "app_form",
                    id: "client-wallet-amount-form"
                },
                {
                    type: "app_form",
                    id: "invoice-payment-form"
                },
                {
                    type: "app_table_row_delete",
                    tableId: "client-wallet-table"
                }
            ],
            reload: function(bind, result) {
                bind("#client-wallet-summary", result.wallet_summary);
            }
        });


        var currencySymbol = "<?php echo $currency_symbol; ?>";
        $("#client-wallet-table").appTable({
            source: '<?php echo_uri("invoice_payments/client_wallet_list_data/$client_id") ?>',
            order: [
                [0, "asc"]
            ],
            columns: [{
                    visible: false,
                    searchable: false
                },
                {
                    title: '<?php echo app_lang("payment_date") ?> ',
                    "class": "w15p",
                    "iDataSort": 0
                },
                {
                    title: '<?php echo app_lang("added_by") ?> ',
                    "class": "w15p",
                },
                {
                    title: '<?php echo app_lang("note") ?>'
                },
                {
                    title: '<?php echo app_lang("amount") ?>',
                    "class": "text-right w15p all"
                },
                {
                    title: '<i data-feather="menu" class="icon-16"></i>',
                    "class": "text-center option w100"
                }
            ],
            printColumns: [1, 2, 3, 4],
            xlsColumns: [1, 2, 3, 4],
            summation: [{
                column: 4,
                dataType: 'currency',
                currencySymbol: currencySymbol
            }]
        });
    });
</script>