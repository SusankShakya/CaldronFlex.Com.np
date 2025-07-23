<div class="card">
    <div class="card-header fw-bold">
        <span class="inline-block mt-1">
            <i data-feather="shopping-cart" class="icon-16"></i> &nbsp;<?php echo app_lang("order_info"); ?>
        </span>

        <div class="float-end">
            <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                <i data-feather="more-horizontal" class="icon-16"></i>
            </div>
            <ul class="dropdown-menu" role="menu">
                <li role="presentation"><?php echo modal_anchor(get_uri("orders/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_order'), array("title" => app_lang('edit_order'), "data-post-id" => $order_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                <li role="presentation" class="dropdown-divider"></li>
                <?php if ($show_estimate_option) { ?>
                    <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='file' class='icon-16'></i> " . app_lang('create_estimate'), array("title" => app_lang("create_estimate"), "data-post-order_id" => $order_info->id, "class" => "dropdown-item")); ?> </li>
                <?php } ?>
                <?php if ($can_create_projects && !$order_info->project_id) { ?>
                    <li role="presentation"><?php echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='command' class='icon-16'></i> " . app_lang('create_project'), array("title" => app_lang("create_project"), "data-post-context" => "order", "data-post-context_id" => $order_info->id, "data-post-client_id" => $order_info->client_id, "class" => "dropdown-item")); ?> </li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="card-body">
        <ul class="list-group info-list">
            <li class="list-group-item no-border pt0">
                <span title="<?php echo app_lang("client"); ?>"><i data-feather="briefcase" class="icon-16 mr5"></i> <?php echo (anchor(get_uri("clients/view/" . $order_info->client_id), $order_info->company_name)); ?></span>
            </li>
            <li class="list-group-item">
                <?php
                $created_by_user = $order_info->created_by_user;
                if ($order_info->created_by_user_type == "staff") {
                    $created_by = get_team_member_profile_link($order_info->created_by, $created_by_user);
                } else {
                    $created_by = get_client_contact_profile_link($order_info->created_by, $created_by_user);
                }
                ?>
                <span title="<?php echo app_lang("created_by"); ?>"><i data-feather="user" class="icon-16 mr5"></i> <?php echo $created_by; ?></span>
            </li>
            <?php if ($order_info->project_id) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("project"); ?>"><i data-feather="command" class="icon-16 mr5"></i> <?php echo anchor(get_uri("projects/view/" . $order_info->project_id), $order_info->project_title); ?></span>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>