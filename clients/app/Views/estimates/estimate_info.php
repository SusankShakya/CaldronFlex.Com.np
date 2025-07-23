<div class="card">
    <div class="card-header fw-bold">
        <span class="inline-block mt-1">
            <i data-feather="file" class="icon-16"></i> &nbsp;<?php echo app_lang("estimate_info"); ?>
        </span>

        <div class="float-end">
            <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                <i data-feather="more-horizontal" class="icon-16"></i>
            </div>
            <ul class="dropdown-menu" role="menu">
                <?php if ($is_estimate_editable) { ?>
                    <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_estimate'), array("title" => app_lang('edit_estimate'), "data-post-id" => $estimate_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                <?php } ?>

                <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('clone_estimate'), array("data-post-is_clone" => true, "data-post-id" => $estimate_info->id, "title" => app_lang('clone_estimate'), "class" => "dropdown-item")); ?></li>
                <li role="presentation" class="dropdown-divider"></li>
                <?php
                if ($estimate_status == "draft" || $estimate_status == "sent") {
                ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_declined'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                <?php } else if ($estimate_status == "accepted") {
                ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_declined'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                <?php
                } else if ($estimate_status == "declined") {
                ?>
                    <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                <?php
                }
                ?>

                <?php if ($estimate_status == "accepted") { ?>
                    <li role="presentation" class="dropdown-divider"></li>
                    <?php if ($can_create_projects && !$estimate_info->project_id) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='command' class='icon-16'></i> " . app_lang('create_project'), array("data-post-context" => "estimate", "data-post-context_id" => $estimate_info->id, "title" => app_lang('create_project'), "data-post-client_id" => $estimate_info->client_id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                    <?php if ($show_invoice_option) { ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form"), "<i data-feather='refresh-cw' class='icon-16'></i> " . app_lang('create_invoice'), array("title" => app_lang("create_invoice"), "data-post-estimate_id" => $estimate_info->id, "class" => "dropdown-item")); ?> </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="card-body">
        <ul class="list-group info-list">
            <li class="list-group-item no-border pt0">
                <?php if ($estimate_info->is_lead) { ?>
                    <span title="<?php echo app_lang("lead"); ?>"><i data-feather="layers" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("leads/view/" . $estimate_info->client_id), $estimate_info->company_name)); ?></span>
                <?php } else { ?>
                    <span title="<?php echo app_lang("client"); ?>"><i data-feather="briefcase" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("clients/view/" . $estimate_info->client_id), $estimate_info->company_name)); ?></span>
                <?php } ?>
            </li>
            <?php if (!$estimate_info->estimate_request_id == 0) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("estimate_request"); ?>"><i data-feather="corner-down-right" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("estimate_requests/view_estimate_request/" . $estimate_info->estimate_request_id), app_lang('estimate_request') . " - " . $estimate_info->estimate_request_id)); ?></span>
                </li>
            <?php } ?>
            <?php if ($estimate_info->project_id) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("project"); ?>"><i data-feather="command" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("projects/view/" . $estimate_info->project_id), $estimate_info->project_title)); ?></span>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>