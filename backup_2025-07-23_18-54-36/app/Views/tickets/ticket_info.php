<div class="card-header fw-bold ticket-info">
    <span class="d-inline-block mt-1">
        <i data-feather="life-buoy" class="icon-16"></i> &nbsp;<?php echo app_lang("ticket_info"); ?>
    </span>

    <div class="float-end">
        <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
            <i data-feather="more-horizontal" class="icon-16"></i>
        </div>
        <ul class="dropdown-menu" role="menu">
            <?php if ($login_user->user_type == "staff") { ?>
                <li role="presentation"><?php echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit'), array("title" => app_lang('ticket'), "data-post-view" => "details", "data-post-id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                <li role="presentation"><?php echo modal_anchor(get_uri("tickets/merge_ticket_modal_form"), "<i data-feather='git-merge' class='icon-16'></i> " . app_lang('merge'), array("title" => app_lang('merge'), "data-post-ticket_id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
            <?php } ?>

            <?php if ($ticket_info->assigned_to === "0" && $login_user->user_type == "staff") { ?>
                <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/assign_to_me/$ticket_info->id"), "<i data-feather='user' class='icon-16'></i> " . app_lang('assign_to_me'), array("class" => "dropdown-item", "title" => app_lang('assign_myself_in_this_ticket'), "data-reload-on-success" => "1")); ?></li>
            <?php } ?>
        </ul>
    </div>
</div>

<div class="card-body">
    <?php if ($ticket_info->ticket_type) { ?>
        <div class="support-type-label">
            <?php echo $ticket_info->ticket_type; ?>
        </div>
    <?php }
    $in_message_count = $ticket_info->in_message_count ? $ticket_info->in_message_count : 0;
    $out_message_count = $ticket_info->out_message_count ? $ticket_info->out_message_count : 0;

    if ($login_user->user_type == "client") {
        $in_message_count = $out_message_count;
        $out_message_count = $ticket_info->in_message_count ? $ticket_info->in_message_count : 0;
    }

    ?>

    <div class="box mb15">
        <div class="box-content">
            <div class="pt-3 pb-3 text-center">
                <div class=" b-r">
                    <h4 class="mt-0 strong mb5 text-danger"><?php echo $in_message_count; ?></h4>
                    <div><?php echo app_lang("in_messages"); ?></div>
                </div>
            </div>
        </div>

        <div class="box-content">
            <div class="p-3 text-center">
                <h4 class="mt-0 strong mb5 text-primary"><?php echo $out_message_count; ?></h4>
                <div><?php echo app_lang("out_messages"); ?></div>
            </div>
        </div>
    </div>

    <ul class="list-group info-list">
        <li class="list-group-item d-md-none">
            <?php
            $last_activity_at = format_since_then($ticket_info->last_activity_at, false);
            echo "<span> <i data-feather='clock' class='icon-16 mr5'></i>" . $last_activity_at . " <span class='text-off'>(" . app_lang("last_activity") . ")</span></span>";
            ?>
        </li>

        <li class="list-group-item mt0">
            <span title="<?php echo app_lang("created_at") . ": " . format_to_relative_time($ticket_info->created_at); ?>"><i data-feather="calendar" class="icon-16 mr5"></i> <?php echo format_to_relative_time($ticket_info->created_at); ?></span>
        </li>

        <?php if ($ticket_info->closed_at && $ticket_info->status == "closed") { ?>
            <div class="list-group-item">
                <span title="<?php echo app_lang("closed") . ": " . format_to_relative_time($ticket_info->closed_at); ?>"><i data-feather="check-square" class="icon-16 mr5"></i> <?php echo format_to_relative_time($ticket_info->closed_at); ?></span>
            </div>
        <?php } ?>

        <?php if ($ticket_info->project_id != "0" && $show_project_reference == "1") { ?>
            <div class="list-group-item">
                <span><i data-feather="command" class="icon-16 mr5"></i> <?php echo $ticket_info->project_title ? anchor(get_uri("projects/view/" . $ticket_info->project_id), $ticket_info->project_title, array("class" => "dark")) : "-"; ?></span>
            </div>
        <?php } ?>

        <?php if ($ticket_info->merged_with_ticket_id) { ?>
            <div class="list-group-item">
                <span title="<?php echo app_lang("moved_to"); ?>"><i data-feather="git-merge" class="icon-16 mr5"></i> <?php echo anchor(get_uri("tickets/view/" . $ticket_info->merged_with_ticket_id), get_ticket_id($ticket_info->merged_with_ticket_id), array()); ?></span>
            </div>
        <?php } ?>

        <?php
        if (count($custom_fields_list)) {
            $fields = "";
            foreach ($custom_fields_list as $data) {
                if ($data->value) {
                    $fields .= "<div class='list-group-item'>$data->title: " . view("custom_fields/output_" . $data->field_type, array("value" => $data->value)) . "</div>";
                }
            }
            if ($fields) {
                echo $fields;
            }
        }
        ?>
    </ul>
</div>