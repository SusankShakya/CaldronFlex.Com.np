<?php
$progress_overdue = 0;
$progress_not_paid = 0;
$progress_partially_paid = 0;
$progress_fully_paid = 0;
$progress_draft = 0;

if ($total_invoices) {
    $progress_overdue = round($overdue_invoices / $total_invoices * 100);
    $progress_not_paid = round($not_paid_invoices / $total_invoices * 100);
    $progress_partially_paid = round($partially_paid_invoices / $total_invoices * 100);
    $progress_fully_paid = round($fully_paid_invoices / $total_invoices * 100);
    $progress_draft = round($draft_invoices / $total_invoices * 100);
}
?>

<div id="invoice-overview-widget-container">
    <div class="card bg-white">
        <div class="card-header">
            <span class="d-inline-block <?php echo $client_id ? "mt-1" : '' ?>"><i data-feather="file-text" class="icon-16"></i> &nbsp;<?php echo app_lang("invoice_overview"); ?></span>

            <?php if ($currencies && $login_user->user_type == "staff" && !$client_id) { ?>
                <div class="float-end">
                    <span class="float-end dropdown">
                        <div class="dropdown-toggle clickable" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                            <span class="ml10 mr10"><i data-feather="more-horizontal" class="icon"></i></span>
                        </div>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <?php
                                $default_currency = get_setting("default_currency");
                                $default_currency_symbol = get_setting("currency_symbol");
                                echo js_anchor($default_currency, array("class" => "dropdown-item load-currency-wise-data-for-invoice-overview", "data-value" => $default_currency, "data-currency-symbol" => $default_currency_symbol)); //default currency

                                foreach ($currencies as $currency) {
                                    echo js_anchor($currency->currency, array("class" => "dropdown-item load-currency-wise-data-for-invoice-overview", "data-value" => $currency->currency, "data-currency-symbol" => $currency->currency_symbol));
                                }
                                ?>
                            </li>
                        </ul>
                    </span>
                </div>
            <?php } ?>

            <?php if ($client_id) { ?>
                <div class="float-end">
                    <div class="action-option light js-cookie-button" data-bs-toggle="collapse" data-bs-target="#invoice-overview-widget-content" aria-expanded="true" aria-controls="invoice-overview-widget-content">
                        <i data-feather="chevron-right" class="icon-16"></i>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if ($client_id) { ?>
            <div class="collapse show" id="invoice-overview-widget-content">
            <?php } ?>
            <div class="card-body rounded-bottom" id="invoice-overview-container">
                <div class="d-flex xs-column-reverse">
                    <div class="d-block flex-fill <?php echo $client_id ? "b-r pe-4" : ""; ?>">
                        <a href="<?php echo get_uri('invoices/index/custom/overdue/' . $invoice_currency); ?>" data-filter="overdue" class="invoice-overview-widget-link text-default">
                            <div class="d-flex p-2">
                                <div class="w40p text-truncate">
                                    <div style="background-color: #F5325C;" class="color-tag border-circle wh10"></div><?php echo app_lang("overdue"); ?>
                                </div>
                                <div class="w20p">
                                    <div class='progress widget-progress-bar' title='<?php echo $progress_overdue; ?>%'>
                                        <div class='progress-bar bg-danger' role='progressbar' style="width: <?php echo $progress_overdue; ?>%;" aria-valuenow='<?php echo $progress_overdue; ?>%' aria-valuemin='0' aria-valuemax='100'></div>
                                    </div>
                                </div>
                                <div class="w15p text-center"><?php echo $overdue_invoices; ?></div>
                                <div class="w25p text-end"><?php echo to_currency($invoices_info->overdue, $currency_symbol); ?></div>
                            </div>
                        </a>
                        <a href="<?php echo get_uri('invoices/index/custom/not_paid/' . $invoice_currency); ?>" data-filter="not_paid" class="invoice-overview-widget-link text-default">
                            <div class="d-flex p-2">
                                <div class="w40p text-truncate">
                                    <div style="background-color: #FAC108;" class="color-tag border-circle wh10"></div><?php echo app_lang("not_paid"); ?>
                                </div>
                                <div class="w20p">
                                    <div class='progress widget-progress-bar' title='<?php echo $progress_not_paid; ?>%'>
                                        <div class='progress-bar bg-orange' role='progressbar' style="width: <?php echo $progress_not_paid; ?>%;" aria-valuenow='<?php echo $progress_not_paid; ?>%' aria-valuemin='0' aria-valuemax='100'></div>
                                    </div>
                                </div>
                                <div class="w15p text-center"><?php echo $not_paid_invoices; ?></div>
                                <div class="w25p text-end"><?php echo to_currency($invoices_info->not_paid, $currency_symbol); ?></div>
                            </div>
                        </a>
                        <a href="<?php echo get_uri('invoices/index/custom/partially_paid/' . $invoice_currency); ?>" data-filter="partially_paid" class="invoice-overview-widget-link text-default">
                            <div class="d-flex p-2">
                                <div class="w40p text-truncate">
                                    <div style="background-color: #6690F4;" class="color-tag border-circle wh10"></div><?php echo app_lang("partially_paid"); ?>
                                </div>
                                <div class="w20p">
                                    <div class='progress widget-progress-bar' title='<?php echo $progress_partially_paid; ?>%'>
                                        <div class='progress-bar' role='progressbar' style="width: <?php echo $progress_partially_paid; ?>%; background-color: #6690F4;" aria-valuenow='<?php echo $progress_partially_paid; ?>%' aria-valuemin='0' aria-valuemax='100'></div>
                                    </div>
                                </div>
                                <div class="w15p text-center"><?php echo $partially_paid_invoices; ?></div>
                                <div class="w25p text-end"><?php echo to_currency($invoices_info->partially_paid_total, $currency_symbol); ?></div>
                            </div>
                        </a>
                        <a href="<?php echo get_uri('invoices/index/custom/fully_paid/' . $invoice_currency); ?>" data-filter="fully_paid" class="invoice-overview-widget-link text-default">
                            <div class="d-flex p-2">
                                <div class="w40p text-truncate">
                                    <div style="background-color: #485BBD;" class="color-tag border-circle wh10"></div><?php echo app_lang("fully_paid"); ?>
                                </div>
                                <div class="w20p">
                                    <div class='progress widget-progress-bar' title='<?php echo $progress_fully_paid; ?>%'>
                                        <div class='progress-bar' role='progressbar' style="width: <?php echo $progress_fully_paid; ?>%; background-color: #485BBD;" aria-valuenow='<?php echo $progress_fully_paid; ?>%' aria-valuemin='0' aria-valuemax='100'></div>
                                    </div>
                                </div>
                                <div class="w15p text-center"><?php echo $fully_paid_invoices; ?></div>
                                <div class="w25p text-end"><?php echo to_currency($invoices_info->fully_paid_total, $currency_symbol); ?></div>
                            </div>
                        </a>
                        <a href="<?php echo get_uri('invoices/index/custom/draft/' . $invoice_currency); ?>" data-filter="draft" class="invoice-overview-widget-link text-default">
                            <div class="d-flex p-2">
                                <div class="w40p text-truncate">
                                    <div style="background-color: #6C757D;" class="color-tag border-circle wh10"></div><?php echo app_lang("draft"); ?>
                                </div>
                                <div class="w20p">
                                    <div class='progress widget-progress-bar' title='<?php echo $progress_draft; ?>%'>
                                        <div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo $progress_draft; ?>%;" aria-valuenow="<?php echo $progress_draft; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="w15p text-center"><?php echo $draft_invoices; ?></div>
                                <div class="w25p text-end"><?php echo to_currency($invoices_info->draft_total, $currency_symbol); ?></div>
                            </div>
                        </a>

                        <?php if ($client_id) { ?>
                            <div class="position-relative w95p">
                                <div class="row">
                                    <div class='widget-footer pt20 col-md-12'>
                                        <div><?php echo app_lang("last_12_months"); ?></div>
                                        <div class="invoice-line-chart-container">
                                            <canvas id="invoice-overview-chart" style="width: 100%; height: 70px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class='widget-footer bottom-25 position-absolute w90p'>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class='col-md-5 col-5 ps-4'>
                                            <div><?php echo app_lang("total_invoiced"); ?></div>
                                            <div class="strong"><?php echo to_currency($invoices_info->invoices_total, $currency_symbol); ?></div>

                                            <div class="mt10"><?php echo app_lang("due"); ?></div>
                                            <div class="strong "><?php echo to_currency($invoices_info->due, $currency_symbol); ?></div>
                                        </div>
                                        <div class="col-md-7 col-7">
                                            <div><?php echo app_lang("last_12_months"); ?></div>
                                            <div class="invoice-line-chart-container">
                                                <canvas id="invoice-overview-chart" style="width: 100%; height: 70px;"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <?php
                    if ($client_id) {
                        $total_invoiced = $invoices_info->invoices_total;
                        $payments = $invoices_info->payments_total;
                        $due = $invoices_info->due;
                        $wallet_balance = $client_wallet_summary->balance;
                    ?>
                        <div class="w250">
                            <div class="text-end">
                                <h3 class="mb0 mt0 <?php echo $total_invoiced == 0 ? 'text-off text-default' : ''; ?>"><?php echo to_currency($total_invoiced, $currency_symbol); ?></h3>
                                <div><?php echo app_lang("total_invoiced"); ?></div>

                                <h3 class="<?php echo $wallet_balance ? "mt20" : "mt40"; ?> mb0 text-primary <?php echo $payments == 0 ? 'text-off text-default' : ''; ?>"><?php echo to_currency($payments, $currency_symbol); ?></h3>
                                <div><?php echo app_lang("payments"); ?></div>

                                <h3 class="<?php echo $wallet_balance ? "mt20" : "mt40"; ?> mb0 text-danger <?php echo $due == 0 ? 'text-off text-default' : ''; ?>"><?php echo to_currency($due, $currency_symbol); ?></h3>
                                <div><?php echo app_lang("due"); ?></div>

                                <?php if ($wallet_balance) { ?>
                                    <h3 class="mt20 mb0 text-success"><?php echo to_currency($wallet_balance, $currency_symbol); ?></h3>
                                    <div><?php echo app_lang("wallet") . " " . app_lang("balance"); ?></div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php if ($client_id) { ?>
            </div>
        <?php } ?>
    </div>
</div>

<style>
    #invoice-overview-widget-container {
        container-type: inline-size;
        container-name: invoice-overview-widget-container;
    }

    @container invoice-overview-widget-container (max-width: 400px) {
        .w25p {
            width: 40%;
        }

        .w20p {
            width: 5%;
        }
    }
</style>

<script>
    $(document).ready(function() {
        var clientID = "<?php echo $client_id; ?>";

        if (!(isMobile() && clientID)) {
            initScrollbar('#invoice-overview-container', {
                setHeight: 330
            });
        }

        var invoicePaymentChart = document.getElementById("invoice-overview-chart");
        var ticks = <?php echo $ticks; ?>;
        new Chart(invoicePaymentChart, {
            type: 'line',
            data: {
                labels: ticks,
                datasets: [{
                    label: "<?php echo app_lang('invoices'); ?>",
                    borderColor: '#6690F4',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: true,
                    data: <?php echo $invoices; ?>
                }]
            },
            options: {
                responsive: true,
                tooltips: {
                    intersect: false,
                    enabled: true,
                    callbacks: {
                        title: function(tooltipItems, data) {
                            return false;
                        },
                        label: function(tooltipItems, data) {
                            if (tooltipItems) {
                                return tooltipItems.xLabel + ": " + toCurrency(tooltipItems.yLabel, " <?php echo $currency_symbol; ?>");
                            } else {
                                return false;
                            }
                        }
                    }
                },
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            display: false
                        }
                    }]
                }
            }
        });


        $(".load-currency-wise-data-for-invoice-overview").click(function() {
            var currencyValue = $(this).attr("data-value");
            var currencySymbol = $(this).attr("data-currency-symbol");

            appAjaxRequest({
                url: "<?php echo get_uri('invoices/load_invoice_overview_statistics_of_selected_currency') ?>",
                type: 'POST',
                data: {
                    "currency": currencyValue,
                    "currency_symbol": currencySymbol
                },
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        $("#invoice-overview-widget-container").html(result.statistics);
                    }
                }
            });
        });

        var userID = "<?php echo $login_user->id; ?>",
            widgetCookieName = "client_view_invoice_overview_widget_" + userID,
            $widgetContent = $("#invoice-overview-widget-content"),
            $toggleButton = $(".js-cookie-button"),
            $widgetContainer = $("#invoice-overview-widget-container"),
            $cardHeader = $widgetContainer.find(".card-header");

        if (clientID) {
            var widgetVisibility = getCookie(widgetCookieName);

            // If no cookie is set (first visit), or it's "visible", show the widget
            if (!widgetVisibility || widgetVisibility === "visible") {
                $widgetContent.addClass("show");
                $toggleButton.removeClass("collapsed");
                $cardHeader.removeClass("rounded");
            } else {
                $widgetContent.removeClass("show");
                $toggleButton.addClass("collapsed");
                $cardHeader.addClass("rounded");
            }

            $widgetContent.on("shown.bs.collapse", function() {
                setCookie(widgetCookieName, "visible");
                $cardHeader.removeClass("rounded");
            });

            $widgetContent.on("hidden.bs.collapse", function() {
                setCookie(widgetCookieName, "hidden");
                $cardHeader.addClass("rounded");
            });
        }
    });
</script>