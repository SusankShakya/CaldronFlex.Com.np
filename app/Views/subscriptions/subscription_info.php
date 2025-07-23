<div class="card">
    <div class="card-header fw-bold">
        <span class="inline-block mt-1">
            <i data-feather="repeat" class="icon-16"></i> &nbsp;<?php echo app_lang("subscription_info"); ?>
        </span>

        <?php if ($can_edit_subscriptions && $subscription_status !== "cancelled") { ?>
            <div class="float-end">
                <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                    <i data-feather="more-horizontal" class="icon-16"></i>
                </div>
                <ul class="dropdown-menu" role="menu">
                    <?php if ($subscription_status !== "cancelled" && $subscription_status !== "active" && !$subscription_info->stripe_subscription_id && get_setting("enable_stripe_subscription")) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("subscriptions/activate_as_stripe_subscription_modal_form/" . $subscription_info->id), "<i data-feather='credit-card' class='icon-16'></i> " . app_lang('activate_as_stripe_subscription'), array("title" => app_lang('activate_as_stripe_subscription'), "data-post-id" => $subscription_info->id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>

                    <?php if ($subscription_status == "draft" && $subscription_status !== "cancelled" && $subscription_info->type === "app") { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("subscriptions/activate_as_internal_subscription_modal_form/" . $subscription_info->id), "<i data-feather='check' class='icon-16'></i> " . app_lang('activate_as_internal_subscription'), array("title" => app_lang("activate_as_internal_subscription"), "data-post-id" => $subscription_info->id, "class" => "dropdown-item")); ?> </li>
                    <?php } else if ($subscription_status == "pending" || $subscription_status == "active") { ?>
                        <li role="presentation"><?php echo js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang('cancel_subscription'), array('title' => app_lang('cancel_subscription'), "data-action-url" => get_uri("subscriptions/update_subscription_status/" . $subscription_info->id . "/cancelled"), "data-action" => "delete-confirmation", "data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                    <?php } ?>

                    <?php if ($subscription_status !== "active" && $subscription_status !== "cancelled") { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("subscriptions/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_subscription'), array("title" => app_lang('edit_subscription'), "data-post-id" => $subscription_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>

    <div class="card-body">
        <ul class="list-group info-list">
            <li class="list-group-item no-border pt0">
                <span title="<?php echo app_lang("client"); ?>"><i data-feather="briefcase" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("clients/view/" . $subscription_info->client_id), $subscription_info->company_name)); ?></span>
            </li>

            <?php if ($subscription_info->bill_date) { ?>
                <li class="list-group-item"><strong><?php echo app_lang("first_billing_date") . ": "; ?></strong><label><?php echo format_to_date($subscription_info->bill_date, false); ?></label></li>
            <?php } ?>

            <li class="list-group-item"><strong><?php echo app_lang("type") . ": "; ?></strong><label><?php echo $subscription_type_label; ?></label></li>

            <?php if ($subscription_info->payment_status === "failed") { ?>
                <li class="list-group-item"><strong><?php echo app_lang("payment_status") . ": "; ?></strong><span class="mt0 badge bg-danger"><?php echo app_lang("failed"); ?></span></li>
            <?php } ?>

            <?php if ($subscription_info->cancelled_at) { ?>
                <li class="list-group-item"><strong><?php echo app_lang("cancelled_at") . ": "; ?></strong><label><?php echo format_to_relative_time($subscription_info->cancelled_at); ?></label></li>
            <?php } ?>

            <?php if ($subscription_info->cancelled_by) { ?>
                <li class="list-group-item"><strong><?php echo app_lang("cancelled_by") . ": "; ?></strong><span><?php echo get_team_member_profile_link($subscription_info->cancelled_by, $subscription_info->cancelled_by_user); ?></span></li>
            <?php } ?>

            <li class="list-group-item"><strong><?php echo app_lang("repeat_every") . ": "; ?></strong><label><?php echo $subscription_info->repeat_every . " " . app_lang("interval_" . $subscription_info->repeat_type); ?></label></li>

        </ul>
    </div>
</div>