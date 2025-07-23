<?php
$ticket_status_class = "bg-danger";
if ($ticket_info->status === "new" || $ticket_info->status === "client_replied") {
    $ticket_status_class = "bg-warning";
} else if ($ticket_info->status === "closed") {
    $ticket_status_class = "bg-success";
}

if ($ticket_info->status === "client_replied" && $login_user->user_type === "client") {
    $ticket_info->status = "open"; //don't show client_replied status to client
}

$status = "<span class='badge rounded-pill large " . $ticket_status_class . "'>" . app_lang($ticket_info->status) . "</span>";
?>

<div class="ticket-top-bar mb20">
    <div class="ticket-title-section">
        <div class="page-title no-bg clearfix no-border">
            <h1 class="pl0">
                <span><i data-feather="life-buoy" class='icon'></i></span>
                <?php echo get_ticket_id($ticket_info->id) . " - " . $ticket_info->title ?>
            </h1>

            <div class="title-button-group mr0 hidden-xs">
                <?php if ($ticket_info->status === "closed") { ?>
                    <?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/open"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_open'), array("class" => "btn btn-danger spinning-btn", "title" => app_lang('mark_as_open'), "data-inline-loader" => "1", "data-post-id" => $ticket_info->id, "data-request-group" => "ticket_status")); ?>
                <?php } else { ?>
                    <?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/closed"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_closed'), array("class" => "btn btn-primary spinning-btn", "title" => app_lang('mark_as_closed'), "data-inline-loader" => "1", "data-post-id" => $ticket_info->id, "data-request-group" => "ticket_status")); ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="ticket-status-section">
        <?php
        $status = "<span class='mt0 badge rounded-pill large $ticket_status_class'>" . app_lang($ticket_info->status) . "</span>";

        if ($can_edit_ticket) {
            echo js_anchor($status, array(
                'title' => "",
                "class" => "",
                "data-id" => $ticket_info->id,
                "data-value" => $ticket_info->status,
                "data-act" => "ticket-modifier",
                "data-modifier-group" => "ticket_info",
                "data-field" => "status",
                "data-action-url" => get_uri("tickets/update_ticket_info/$ticket_info->id/status")
            ));
        } else {
            echo $status;
        }

        if ($login_user->user_type === "staff") {
            $labels = $can_edit_ticket ? "<span class='text-off mlr10'>" . app_lang("add") . " " . app_lang("label") . "<span>" : "";

            if (isset($ticket_labels) && $ticket_labels) {
                $labels = $ticket_labels;
            }

            if ($can_edit_ticket) {
                echo js_anchor($labels, array(
                    'title' => "",
                    "class" => "ml5 mr10",
                    "data-id" => $ticket_info->id,
                    "data-value" => $ticket_info->labels,
                    "data-act" => "ticket-modifier",
                    "data-modifier-group" => "ticket_info",
                    "data-field" => "labels",
                    "data-multiple-tags" => "1",
                    "data-action-url" => get_uri("tickets/update_ticket_info/$ticket_info->id/labels")
                ));
            } else {
                echo $labels;
            }

            $image_url = get_avatar($ticket_info->assigned_to_avatar);
            $assigned_to_avatar = "<span class='avatar avatar-xxs mr5'><img id='ticket-assigned-to-avatar' src='$image_url' alt='...'></span>";

            if ($can_edit_ticket) {
                echo js_anchor(
                    $ticket_info->assigned_to_user ? $assigned_to_avatar . "<span class='hidden-sm'>" . $ticket_info->assigned_to_user . "</span>" : "<span class='text-off'>" . app_lang("add") . " " . app_lang("assignee") . "<span>",
                    array(
                        'title' => "",
                        "class" => "ticket-assigned-to",
                        "data-id" => $ticket_info->id,
                        "data-value" => $ticket_info->assigned_to,
                        "data-act" => "ticket-modifier",
                        "data-modifier-group" => "ticket_info",
                        "data-field" => "assigned_to",
                        "data-action-url" => get_uri("tickets/update_ticket_info/$ticket_info->id/assigned_to")
                    )
                );
            }
        }

        $last_activity_at = format_since_then($ticket_info->last_activity_at, false);
        echo "<span class='badge rounded-pill large bg-transparent hidden-xs ml10' title='" . format_to_datetime($ticket_info->last_activity_at) . "'>" . $last_activity_at . "</span>";
        ?>
    </div>
</div>