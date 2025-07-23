<div class="card" id="proposal-info-card">
    <div class="card-header fw-bold">
        <span class="inline-block mt-1">
            <i data-feather="anchor" class="icon-16"></i> &nbsp;<?php echo app_lang("proposal_info"); ?>
        </span>

        <div class="float-end">
            <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                <i data-feather="more-horizontal" class="icon-16"></i>
            </div>
            <ul class="dropdown-menu" role="menu">
                <?php if ($is_proposal_editable) { ?>
                    <li role="presentation"><?php echo modal_anchor(get_uri("proposals/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_proposal'), array("title" => app_lang('edit_proposal'), "data-post-id" => $proposal_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                <?php } ?>
                <li role="presentation"><?php echo modal_anchor(get_uri("proposals/modal_form"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('clone_proposal'), array("data-post-is_clone" => true, "data-post-id" => $proposal_info->id, "title" => app_lang('clone_proposal'), "class" => "dropdown-item")); ?></li>

                <?php if ($proposal_status == "accepted") { ?>
                    <li role="presentation" class="dropdown-divider"></li>
                    <?php if ($can_create_projects && !$proposal_info->project_id) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='command' class='icon-16'></i> " . app_lang('create_project'), array("data-post-context" => "proposal", "data-post-context_id" => $proposal_info->id, "title" => app_lang('create_project'), "data-post-client_id" => $proposal_info->client_id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                    <?php if ($show_estimate_option) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form/"), "<i data-feather='file' class='icon-16'></i> " . app_lang('create_estimate'), array("title" => app_lang("create_estimate"), "data-post-proposal_id" => $proposal_info->id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                    <?php if ($show_invoice_option) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form/"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('create_invoice'), array("title" => app_lang("create_invoice"), "data-post-proposal_id" => $proposal_info->id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                    <?php if ($show_contract_option) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("contracts/modal_form/"), "<i data-feather='file-plus' class='icon-16'></i> " . app_lang('create_contract'), array("title" => app_lang("create_contract"), "data-post-proposal_id" => $proposal_info->id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                <?php } ?>

                <li role="presentation" class="dropdown-divider"></li>

                <?php if ($proposal_status == "draft" || $proposal_status == "sent") { ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                    <?php if ($proposal_status == "draft") { ?>
                        <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/sent"), "<i data-feather='send' class='icon-16'></i> " . app_lang('mark_as_sent'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                <?php } else if ($proposal_status == "accepted") { ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                <?php } else if ($proposal_status == "declined") { ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                <?php } ?>

            </ul>
        </div>
    </div>
    <div class="card-body">
        <ul class="list-group info-list">
            <li class="list-group-item no-border pt0">
                <?php if ($proposal_info->is_lead) { ?>
                    <span title="<?php echo app_lang("lead"); ?>"><i data-feather="layers" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("leads/view/" . $proposal_info->client_id), $proposal_info->company_name)); ?></span>
                <?php } else { ?>
                    <span title="<?php echo app_lang("client"); ?>"><i data-feather="briefcase" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("clients/view/" . $proposal_info->client_id), $proposal_info->company_name)); ?></span>
                <?php } ?>
            </li>

            <li class="list-group-item">
                <strong><?php echo app_lang("proposal_date") . ": "; ?></strong><span class='ml5'><?php echo format_to_date($proposal_info->proposal_date, false); ?></span>
            </li>

            <li class="list-group-item">
                <strong><?php echo app_lang("valid_until") . ": "; ?></strong><span class='ml5'><?php echo format_to_date($proposal_info->valid_until, false); ?></span>
            </li>

            <?php if ($proposal_info->last_preview_seen) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("last_preview_seen"); ?>"><i data-feather="target" class="icon-16 mr5"></i> <?php echo format_to_relative_time($proposal_info->last_preview_seen); ?></span>
                </li>
            <?php } ?>


            <?php
            $read_count = "";
            if ($total_read_count) {
                $read_count = "<span class='ml5' title='" . app_lang("email_seen_count") . "'>" . ajax_anchor(get_uri("proposals/email_view_report/" . $proposal_info->id), " (" . $total_read_count . ")", array("data-real-target" => "#proposal-email-view-report", "class" => "strong")) . "</span>";
            }
            ?>

            <?php if ($proposal_info->last_email_read_time) { ?>
                <li class="list-group-item pb0">
                    <span title="<?php echo app_lang("last_email_seen"); ?>"><i data-feather="mail" class="icon-16 mr5"></i> <?php echo format_to_relative_time($proposal_info->last_email_read_time) . $read_count; ?></span>
                </li>
            <?php } ?>

            <?php if ($total_read_count) { ?>
                <div id="proposal-email-view-report"></div>
            <?php } ?>

            <?php if ($proposal_info->project_id) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("project"); ?>"><i data-feather="command" class="icon-16 mr5"></i> <?php echo anchor(get_uri("projects/view/" . $proposal_info->project_id), $proposal_info->project_title); ?></span>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>