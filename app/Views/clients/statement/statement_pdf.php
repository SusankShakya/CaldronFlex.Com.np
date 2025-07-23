<div style=" margin: auto;">
    <?php
    $colspan = 3;
    $show_taxable = false;

    $color = get_setting("invoice_color");
    if (!$color) {
        $color = "#2AA384";
    }

    $item_background = get_setting("invoice_item_list_background");

    // prepare statement rows
    function generate_client_statement_pdf_row($date, $description, $invoice_total, $payment, &$balance, $currency_symbol, $item_background) {
        if ($invoice_total !== "-") {
            $balance += $invoice_total;
        } elseif ($payment !== "-") {
            $balance -= $payment;
        }

        return '<tr style="background-color: ' . $item_background . ';">
            <td style="border: 1px solid #fff; padding: 10px;">' . format_to_date($date, false) . '</td>
            <td style="text-align: left; border: 1px solid #fff;">' . $description . '</td>
            <td style="text-align: right; border: 1px solid #fff;">' . ($invoice_total !== "-" ? to_currency($invoice_total, $currency_symbol) : "-") . '</td>
            <td style="text-align: right; border: 1px solid #fff;">' . ($payment !== "-" ? to_currency($payment, $currency_symbol) : "-") . '</td>
            <td style="text-align: right; border: 1px solid #fff;">' . to_currency($balance, $currency_symbol) . '</td>
        </tr>';
    }

    $balance = $opening_balance;
    if (!$start_date) {
        $balance = 0;
    }

    // add the opening amount row
    $statement_rows = generate_client_statement_pdf_row($start_date, app_lang("opening_balance"), "-", "-", $balance, $currency_symbol, $item_background);

    // calculate summary
    $total_invoiced = 0;
    $payment_received = 0;

    // add the statement rows
    foreach ($client_statement as $item) {

        $total_invoiced += $item->invoice_total;
        $payment_received += $item->payment;

        $invoice_total = $item->type == "invoice" ? $item->invoice_total : "-";
        $payment = $item->type == "payment" ? $item->payment : "-";
        $statement_rows .= generate_client_statement_pdf_row($item->date, $item->description, $invoice_total, $payment, $balance, $currency_symbol, $item_background);
    }

    ?>

    <table class="header-style" style="font-size: 13.5px;">
        <tr class="invoice-preview-header-row">
            <td style="width: 45%; vertical-align: top;">
                <?php echo get_company_logo(0, "statement"); ?>
            </td>
            <td class="hidden-invoice-preview-row" style="width: 10%;"></td>
            <td class="invoice-info-container invoice-header-style-one" style="width: 45%; vertical-align: top; text-align: right">
                <p><span class="invoice-info-title" style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo app_lang("account_statement"); ?>&nbsp;</span></p>

                <p>
                    <?php
                    $period = app_lang("period") . ": ";
                    if ($start_date && $end_date) {
                        $period .= "<span style='font-weight: bold;'>" . format_to_date($start_date, false) . "</span> to <span style='font-weight: bold;'>" . format_to_date($end_date, false) . "</span>";
                    } else {
                        $period .= app_lang("all_time");
                    }

                    echo $period;
                    ?>
                </p>
                <span class="invoice-meta text-default"><?php
                                                        echo app_lang("opening_balance") . ": " . to_currency($opening_balance, $currency_symbol);
                                                        echo "<br />";
                                                        echo app_lang("total_invoiced") . ": " . to_currency($total_invoiced, $currency_symbol);
                                                        echo "<br />";
                                                        echo app_lang("payment_received") . ": " . to_currency($payment_received, $currency_symbol);
                                                        echo "<br />";
                                                        echo app_lang("due") . ": " . to_currency($balance, $currency_symbol);
                                                        ?></span>
            </td>
        </tr>
        <tr>
            <td style="padding: 5px;"></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><?php
                echo company_widget(0, "statement");
                ?>
            </td>
            <td></td>
            <td><div><b><?php echo app_lang("to"); ?></b></div>
                <div style="line-height: 3px;"> </div>
                <strong><?php echo $client_info->company_name; ?> </strong>
                <div style="line-height: 3px;"> </div>
                <span class="invoice-meta text-default">
                    <?php if ($client_info->address || $client_info->vat_number || (isset($client_info->custom_fields) && $client_info->custom_fields)) { ?>
                        <div><?php echo nl2br($client_info->address ? $client_info->address : ""); ?>
                            <?php if ($client_info->city) { ?>
                                <br /><?php echo $client_info->city; ?>
                            <?php } ?>
                            <?php if ($client_info->state) { ?>
                                <br /><?php echo $client_info->state; ?>
                            <?php } ?>
                            <?php if ($client_info->zip) { ?>
                                <br /><?php echo $client_info->zip; ?>
                            <?php } ?>
                            <?php if ($client_info->country) { ?>
                                <br /><?php echo $client_info->country; ?>
                            <?php } ?>
                            <?php if ($client_info->vat_number || $client_info->gst_number) { ?>
                                <?php if ($client_info->vat_number) { ?>
                                    <br /><?php echo app_lang("vat_number") . ": " . $client_info->vat_number; ?>
                                <?php } else { ?>
                                    <br /><?php echo app_lang("gst_number") . ": " . $client_info->gst_number; ?>
                                <?php } ?>
                            <?php } ?>
                            <?php
                            if (isset($client_info->custom_fields) && $client_info->custom_fields) {
                                foreach ($client_info->custom_fields as $field) {
                                    if ($field->value) {
                                        echo "<br />" . $field->custom_field_title . ": " . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value));
                                    }
                                }
                            }
                            ?>


                        </div>
                    <?php } ?>
                </span>
            </td>
        </tr>
    </table>


</div>

<table style="width: 100%;">
    <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
        <th style="width: 15%; border-right: 1px solid #eee;"> <?php echo app_lang("date"); ?> </th>
        <th style="width: 40%; text-align: left; border-right: 1px solid #eee;"> <?php echo app_lang("description"); ?></th>
        <th style="width: 15%; text-align: right; border-right: 1px solid #eee;"> <?php echo app_lang("invoice"); ?></th>
        <th style="width: 15%; text-align: right; border-right: 1px solid #eee;"> <?php echo app_lang("payment"); ?></th>
        <th style="width: 15%; text-align: right; "> <?php echo app_lang("balance"); ?></th>
    </tr>
    <?php echo $statement_rows; ?>
    <tr>
        <td colspan="4" style="text-align: right; font-weight: bold; "><?php echo app_lang("due"); ?></td>
        <td style="text-align: right; border: 1px solid #fff; background-color: <?php echo $color; ?>; color: #fff; font-weight: bold; "><?php echo to_currency($balance, $currency_symbol); ?></td>
    </tr>
</table>