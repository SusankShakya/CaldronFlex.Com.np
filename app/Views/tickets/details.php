<div class="clearfix ">
    <div class="details-view-wrapper ticket-details-container d-flex">
        <div class="w-100">
            <div class="card p15 b-t w-100 ticket-comments-section" id="ticket-comments-section">
                <?php echo view("tickets/ticket_comments"); ?>
            </div>
        </div>
        <div class="flex-shrink-0 details-view-right-section">
            <div class="card" id="ticket-details-ticket-info"><?php echo view("tickets/ticket_info"); ?></div>

            <?php if ($login_user->user_type === "staff") { ?>
                <div id="ticket-details-client-info"><?php echo view("tickets/ticket_client_info"); ?></div>

                <div id="ticket-tasks-section"><?php echo view("tickets/tasks/index", array("ticket_info" => $ticket_info)); ?></div>
            <?php } ?>

            <?php if (can_access_reminders_module()) { ?>
                <div class="card reminders-card" id="ticket-reminders">
                    <div class="card-header fw-bold">
                        <i data-feather="clock" class="icon-16"></i> &nbsp;<?php echo app_lang("reminders") . " (" . app_lang('private') . ")"; ?>
                    </div>
                    <div class="card-body">
                        <?php echo view("reminders/reminders_view_data", array("ticket_id" => $ticket_info->id, "hide_form" => true, "reminder_view_type" => "ticket")); ?>
                    </div>
                </div>
            <?php } ?>

            <?php
            $pinned_status = "hide";
            if (count($pinned_comments)) {
                $pinned_status = "";
            }
            ?>

            <div class="card <?php echo $pinned_status; ?>" id="ticket-pinned-comments">
                <div class="card-header fw-bold">
                    <i data-feather="map-pin" class="icon-16"></i> &nbsp;<?php echo app_lang("pinned_comments"); ?>
                </div>
                <div class="card-body">
                    <?php echo view("lib/pin_comments/comments_list"); ?>
                </div>
            </div>
        </div>
    </div>
</div>