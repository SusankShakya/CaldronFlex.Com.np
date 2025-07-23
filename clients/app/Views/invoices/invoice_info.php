<div class="card" id="invoice-details-invoice-info">
    <div class="card-header fw-bold">
        <span class="inline-block mt-1">
            <i data-feather="file-text" class="icon-16"></i> &nbsp;<?php echo app_lang("invoice_info"); ?>
        </span>

        <?php if ($can_edit_invoices) { ?>
            <div class="float-end">
                <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                    <i data-feather="more-horizontal" class="icon-16"></i>
                </div>
                <ul class="dropdown-menu" role="menu">
                    <?php if ($can_edit_invoices && $invoice_info->type == "invoice") { ?>
                        <?php
                        $edit_url = "invoices/modal_form";
                        if (get_setting("enable_invoice_lock_state") && !$is_invoice_editable) {
                            $edit_url = "invoices/recurring_modal_form";
                        }
                        ?>

                        <li role="presentation"><?php echo modal_anchor(get_uri($edit_url), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_invoice'), array("title" => app_lang('edit_invoice'), "data-post-id" => $invoice_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>

                        <?php if ($invoice_status == "not_paid" || $invoice_status == "overdue" || $invoice_status == "partially_paid") { ?>
                            <li role="presentation"><?php echo js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang('mark_invoice_as_cancelled'), array('title' => app_lang('mark_invoice_as_cancelled'), "data-action-url" => get_uri("invoices/update_invoice_status/" . $invoice_info->id . "/cancelled"), "data-action" => "delete-confirmation", "data-reload-on-success" => "1", "class" => "dropdown-item mark-as-cancelled-btn")); ?> </li>
                        <?php } ?>

                        <?php if ($invoice_status !== "draft" && $invoice_status !== "cancelled" && $invoice_info->status !== "credited") { ?>
                            <li role="presentation"><?php echo modal_anchor(get_uri("invoices/create_credit_note_modal_form/" . $invoice_info->id), "<i data-feather='file-minus' class='icon-16'></i> " . app_lang('create_credit_note'), array("title" => app_lang("create_credit_note"), "data-post-id" => $invoice_info->id, "class" => "dropdown-item")); ?> </li>
                        <?php } ?>

                        <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('clone_invoice'), array("data-post-is_clone" => true, "data-post-id" => $invoice_info->id, "title" => app_lang('clone_invoice'), "class" => "dropdown-item")); ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>
    <div class="card-body">
        <ul class="list-group info-list">
            <?php if ($invoice_info->client_id) { ?>
                <li class="list-group-item no-border pt0">
                    <span title="<?php echo app_lang("client"); ?>"><i data-feather="briefcase" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("clients/view/" . $invoice_info->client_id), $invoice_info->company_name)); ?></span>
                </li>
            <?php } ?>
            <?php if ($invoice_info->project_id) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("project"); ?>"><i data-feather="command" class="icon-16 mr5"></i> <?php echo anchor(get_uri("projects/view/" . $invoice_info->project_id), $invoice_info->project_title); ?></span>
                </li>
            <?php } ?>
            <?php if ($invoice_info->recurring_invoice_id) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("created_from"); ?>"><i data-feather="corner-down-right" class="icon-16 mr5"></i> <?php echo anchor(get_uri("invoices/view/" . $invoice_info->recurring_invoice_id), $invoice_info->recurring_invoice_display_id); ?></span>
                </li>
            <?php } ?>
            <?php if ($invoice_info->subscription_id) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("created_from"); ?>"><i data-feather="corner-down-right" class="icon-16 mr5"></i> <?php echo anchor(get_uri("subscriptions/view/" . $invoice_info->subscription_id), get_subscription_id($invoice_info->subscription_id)); ?></span>
                </li>
            <?php } ?>
            <?php if ($invoice_info->estimate_id && $login_user->is_admin) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("created_from"); ?>"><i data-feather="corner-down-right" class="icon-16 mr5"></i> <?php echo anchor(get_uri("estimates/view/" . $invoice_info->estimate_id), get_estimate_id($invoice_info->estimate_id)); ?></span>
                </li>
            <?php } ?>

            <?php if ($invoice_info->cancelled_at && $invoice_info->cancelled_by) { ?>
                <li class="list-group-item">
                    <div><i data-feather="x" class="icon-16 mr5"></i><?php echo app_lang("cancelled") . ": "; ?></div>
                    <div class="mt5 ml25">
                        <span><?php echo format_to_relative_time($invoice_info->cancelled_at); ?></span> <?php echo app_lang("by") ?>
                        <span><?php echo get_team_member_profile_link($invoice_info->cancelled_by, $invoice_info->cancelled_by_user); ?></span>
                    </div>
                </li>
            <?php } ?>

            <?php if ($invoice_info->recurring) { ?>
                <?php
                $recurring_stopped = false;
                $recurring_cycle_class = "";
                if ($invoice_info->no_of_cycles_completed > 0 && $invoice_info->no_of_cycles_completed == $invoice_info->no_of_cycles) {
                    $recurring_cycle_class = "text-danger";
                    $recurring_stopped = true;
                }


                $cycles = $invoice_info->no_of_cycles_completed . "/" . $invoice_info->no_of_cycles;
                if (!$invoice_info->no_of_cycles) { //if not no of cycles, so it's infinity
                    $cycles = $invoice_info->no_of_cycles_completed . "/&#8734;";
                }
                ?>

                <li class="list-group-item">
                    <span title="<?php echo app_lang("repeat_every"); ?>"><i data-feather="repeat" class="icon-16 mr5"></i></span>
                    <span><?php echo $invoice_info->repeat_every . " " . app_lang("interval_" . $invoice_info->repeat_type); ?></span>
                    <span class="float-end <?php echo $recurring_cycle_class; ?>" title="<?php echo app_lang("cycles"); ?>"><?php echo $cycles; ?></span>
                    <?php if (!$recurring_stopped && (int) $invoice_info->next_recurring_date) { ?>
                        <div class="mt5 ml25"><?php echo app_lang("next_recurring_date") . ": " . format_to_date($invoice_info->next_recurring_date, false); ?></div>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>