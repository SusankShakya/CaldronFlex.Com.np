<div id="invoice-top-bar" class="details-view-status-section mb20">
    <div class="page-title no-bg clearfix mb5 no-border">
        <h1 class="pl0">
            <span><i data-feather="file-text" class='icon'></i></span>
            <?php
            if ($invoice_info->type == "credit_note") {
                echo app_lang("credit_note") . " - ";
            }
            ?>
            <?php echo $invoice_info->display_id; ?>
            <?php
            if ($invoice_info->recurring) {
                $recurring_status_class = "text-primary";
                if ($invoice_info->no_of_cycles_completed > 0 && $invoice_info->no_of_cycles_completed == $invoice_info->no_of_cycles) {
                    $recurring_status_class = "text-danger";
                }
            ?>
                <span class="label ml15"><span class="<?php echo $recurring_status_class; ?>"><?php echo app_lang('recurring'); ?></span></span>
            <?php } ?>
        </h1>

        <div class="title-button-group mr0">
            <?php if ($invoice_status !== "cancelled" && $can_edit_invoices) { ?>
                <?php if ($invoice_info->type == "invoice") { ?>
                    <?php echo modal_anchor(get_uri("invoices/send_invoice_modal_form/" . $invoice_info->id), "<i data-feather='mail' class='icon-16'></i> " . app_lang('email_invoice_to_client'), array("class" => "btn btn-primary mr0", "title" => app_lang('email_invoice_to_client'), "data-post-id" => $invoice_info->id)); ?>
                <?php } else { ?>
                    <?php echo modal_anchor(get_uri("invoices/send_invoice_modal_form/" . $invoice_info->id), "<i data-feather='mail' class='icon-16'></i> " . app_lang('email_credit_note_to_client'), array("class" => "btn btn-primary mr0", "title" => app_lang('email_credit_note_to_client'), "data-post-id" => $invoice_info->id)); ?>
                <?php } ?>
            <?php } ?>
            <span class="dropdown inline-block">
                <?php if ($invoice_status == "draft" && $invoice_status !== "cancelled" && $can_edit_invoices) { ?>
                    <?php echo ajax_anchor(get_uri("invoices/update_invoice_status/" . $invoice_info->id . "/not_paid"), "<i data-feather='check' class='icon-16'></i> " . app_lang('mark_invoice_as_not_paid'), array("data-inline-loader" => "1", "class" => "btn btn-warning text-white spinning-btn mr0 ml10", "data-request-group" => "invoice_status")); ?>
                <?php } ?>
            </span>
        </div>
    </div>

    <?php
    echo "<span>$invoice_status_label</span>";

    $invoice_labels = make_labels_view_data($invoice_info->labels_list, false, true, "rounded-pill");

    $labels = $can_edit_invoices ? "<span class='text-off ml10 mr10'>" . app_lang("add") . " " . app_lang("label") . "<span>" : "";

    if (isset($invoice_labels) && $invoice_labels) {
        $labels = $invoice_labels;
    }

    if ($can_edit_invoices) {
        echo js_anchor($labels, array(
            'title' => "",
            "class" => "mr5",
            "data-id" => $invoice_info->id,
            "data-value" => $invoice_info->labels,
            "data-act" => "invoice-modifier",
            "data-modifier-group" => "invoice_info",
            "data-field" => "labels",
            "data-multiple-tags" => "1",
            "data-action-url" => get_uri("invoices/update_invoice_info/$invoice_info->id/labels")
        ));
    } else {
        echo $labels;
    }

    $last_email_sent_date = (is_date_exists($invoice_info->last_email_sent_date)) ? format_to_date($invoice_info->last_email_sent_date, FALSE) : app_lang("never");
    echo "<span class='badge rounded-pill large text-default b-a' title='" . app_lang("last_email_sent") . "'><i data-feather='mail' class='icon-14 text-off '></i> $last_email_sent_date</span>";
    ?>
</div>