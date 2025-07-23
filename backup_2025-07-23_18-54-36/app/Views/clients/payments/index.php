<?php if (isset($page_type) && $page_type === "full") { ?>
    <div id="page-content" class="page-wrapper clearfix">

        <div class="card">
            <div class="page-title clearfix">
                <h1><?php echo app_lang('payments'); ?></h1>
            </div>

            <div class="table-responsive">
                <table id="invoice-payment-table" class="display" width="100%">
                </table>
            </div>
        </div>
    <?php } else { ?>
        <!-- Team member's portal -->

        <div class="clearfix">
            <ul id="client-payments-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title rounded-top-0" role="tablist">
                <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#payments-list-tab"><?php echo app_lang("payments"); ?></a></li>

                <?php if ($show_client_wallet) { ?>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo get_uri("invoice_payments/client_wallet/" . $client_id . "/") ?>" data-bs-target="#client-wallet-tab"><?php echo app_lang("wallet"); ?></a></li>
                <?php } ?>

                <?php if ($can_edit_invoices) { ?>
                    <div class="tab-title clearfix no-border">
                        <div class="title-button-group">
                            <?php echo modal_anchor(get_uri("invoice_payments/payment_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_payment'), array("class" => "btn btn-default mb0", "title" => app_lang('add_payment'), "data-post-client_id" => $client_id, "id" => "add-client-wallet-amount-btn")); ?>
                        </div>
                    </div>
                <?php } ?>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade" id="payments-list-tab">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="table-responsive">
                            <table id="invoice-payment-table" class="display" width="100%">
                            </table>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade default-bg" id="client-wallet-tab"></div>
            </div>
        </div>

    <?php } ?>

    <?php if (isset($page_type) && $page_type === "full") { ?>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function() {

        <?php if ($can_edit_invoices) { ?>
            var $addButton = $("#add-client-wallet-amount-btn");
            $("#client-payments-tabs li").click(function() {
                var activeField = $(this).find("a").attr("data-bs-target");

                if (activeField === "#client-wallet-tab") {
                    $addButton.removeClass("hide");
                    $addButton.attr("title", "<?php echo app_lang('add_payment') . " " . strtolower(app_lang("to")) . " " . $client_wallet_payment_method_info->title; ?>");
                    $addButton.attr("data-title", "<?php echo app_lang('add_payment') . " " . strtolower(app_lang("to")) . " " . $client_wallet_payment_method_info->title; ?>");
                    $addButton.attr("data-action-url", "<?php echo get_uri("invoice_payments/add_client_wallet_amount_modal_form"); ?>");
                } else if (activeField === "#payments-list-tab") {
                    $addButton.removeClass("hide");
                    $addButton.attr("title", "<?php echo app_lang("add_payment"); ?>");
                    $addButton.attr("data-title", "<?php echo app_lang("add_payment"); ?>");
                    $addButton.attr("data-action-url", "<?php echo get_uri("invoice_payments/payment_modal_form"); ?>");
                }
            });
        <?php } ?>

        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        $("#invoice-payment-table").appTable({
            source: '<?php echo_uri("invoice_payments/payment_list_data_of_client/" . $client_id) ?>',
            order: [
                [1, "desc"]
            ],
            filterDropdown: [
                <?php if ($login_user->user_type === "staff") { ?> {
                        name: "payment_method_id",
                        class: "w200",
                        options: <?php echo $payment_methods_dropdown; ?>
                    }
                <?php } ?>
            ],
            columns: [{
                    title: '<?php echo app_lang("invoice_id") ?> ',
                    "class": "w10p all"
                },
                {
                    visible: false,
                    searchable: false
                },
                {
                    title: '<?php echo app_lang("payment_date") ?> ',
                    "class": "w15p all",
                    "iDataSort": 1
                },
                {
                    title: '<?php echo app_lang("payment_method") ?>',
                    "class": "w15p"
                },
                {
                    title: '<?php echo app_lang("note") ?>'
                },
                {
                    title: '<?php echo app_lang("amount") ?>',
                    "class": "text-right w15p"
                }
            ],
            printColumns: [0, 2, 3, 4, 5],
            xlsColumns: [0, 2, 3, 4, 5],
            summation: [{
                column: 5,
                dataType: 'currency',
                currencySymbol: currencySymbol
            }]
        });

    });
</script>