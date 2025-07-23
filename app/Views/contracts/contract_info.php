<div class="card" id="contract-info-card">
    <div class="card-header fw-bold">
        <span class="inline-block mt-1">
            <i data-feather="file-plus" class="icon-16"></i> &nbsp;<?php echo app_lang("contract_info"); ?>
        </span>

        <div class="float-end">
            <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                <i data-feather="more-horizontal" class="icon-16"></i>
            </div>
            <ul class="dropdown-menu" role="menu">
                <?php if ($is_contract_editable) { ?>
                    <li role="presentation"><?php echo modal_anchor(get_uri("contracts/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_contract'), array("title" => app_lang('edit_contract'), "data-post-id" => $contract_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                <?php } ?>
                <li role="presentation"><?php echo modal_anchor(get_uri("contracts/modal_form"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('clone_contract'), array("data-post-is_clone" => true, "data-post-id" => $contract_info->id, "title" => app_lang('clone_contract'), "class" => "dropdown-item")); ?></li>

                <?php if (!$contract_info->staff_signed_by && get_setting("add_signature_option_for_team_members")) { ?>
                    <li role="presentation"><?php echo modal_anchor(get_uri("contract/accept_contract_modal_form/$contract_info->id"), "<i data-feather='edit-3' class='icon-16'></i> " . app_lang('sign_contract'), array("title" => app_lang('sign_contract'), "class" => "dropdown-item")); ?></li>
                <?php } ?>

                <?php if ($contract_status == "accepted") { ?>
                    <li role="presentation" class="dropdown-divider"></li>
                    <?php if ($show_estimate_option) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form/"), "<i data-feather='file' class='icon-16'></i> " . app_lang('create_estimate'), array("title" => app_lang("create_estimate"), "data-post-contract_id" => $contract_info->id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                    <?php if ($show_invoice_option) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form/"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('create_invoice'), array("title" => app_lang("create_invoice"), "data-post-contract_id" => $contract_info->id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                <?php } ?>

                <li role="presentation" class="dropdown-divider"></li>

                <?php if ($contract_status == "draft" || $contract_status == "sent") { ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                    <?php if ($contract_status == "draft") { ?>
                        <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/sent"), "<i data-feather='send' class='icon-16'></i> " . app_lang('mark_as_sent'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                <?php } else if ($contract_status == "accepted") { ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                <?php } else if ($contract_status == "declined") { ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                <?php } ?>

            </ul>
        </div>
    </div>
    <div class="card-body">
        <ul class="list-group info-list">
            <li class="list-group-item no-border pt0">
                <?php if ($contract_info->is_lead) { ?>
                    <span title="<?php echo app_lang("lead"); ?>"><i data-feather="layers" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("leads/view/" . $contract_info->client_id), $contract_info->company_name)); ?></span>
                <?php } else { ?>
                    <span title="<?php echo app_lang("client"); ?>"><i data-feather="briefcase" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("clients/view/" . $contract_info->client_id), $contract_info->company_name)); ?></span>
                <?php } ?>
            </li>

            <li class="list-group-item">
                <strong><?php echo app_lang("contract_date") . ": "; ?></strong><span class='ml5'><?php echo format_to_date($contract_info->contract_date, false); ?></span>
            </li>

            <li class="list-group-item">
                <strong><?php echo app_lang("valid_until") . ": "; ?></strong><span class='ml5'><?php echo format_to_date($contract_info->valid_until, false); ?></span>
            </li>

            <?php if ($contract_info->project_id) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("project"); ?>"><i data-feather="command" class="icon-16 mr5"></i> <?php echo anchor(get_uri("projects/view/" . $contract_info->project_id), $contract_info->project_title); ?></span>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>