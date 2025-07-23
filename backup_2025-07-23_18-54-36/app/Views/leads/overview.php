<div class="clearfix mt15">
    <div class="details-view-wrapper d-flex">
        <div class="w-100">
            <div class="w-100 details-view-left-section">
                <div id="lead-summary">
                    <?php echo view("leads/lead_overview_info"); ?>
                </div>

                <div id="lead-view-contacts-section">
                    <?php echo view("leads/contacts/index", array("client_id" => $lead_info->id)); ?>
                </div>

                <?php if ($show_event_info) { ?>
                    <div id="lead-events-section">
                        <?php echo view("events/index", array("is_mobile" => true)); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="flex-shrink-0 details-view-right-section">
            <div id="lead-details-lead-info">
                <?php echo view("leads/lead_info"); ?>
            </div>

            <div id="lead-details-lead-custom-fields-info">
                <?php echo view("leads/lead_custom_fields_info"); ?>
            </div>

            <div id="lead-tasks-section">
                <?php echo view("leads/tasks/index"); ?>
            </div>

            <?php if ($show_note_info) { ?>
                <div id="lead-notes-section">
                    <?php echo view("leads/notes/index"); ?>
                </div>
            <?php } ?>

            <?php if (can_access_reminders_module()) { ?>
                <div class="card reminders-card" id="lead-reminders">
                    <div class="card-header fw-bold">
                        <i data-feather="clock" class="icon-16"></i> &nbsp;<?php echo app_lang("reminders") . " (" . app_lang('private') . ")"; ?>
                    </div>
                    <div class="card-body">
                        <?php echo view("reminders/reminders_view_data", array("lead_id" => $lead_info->id, "hide_form" => true, "reminder_view_type" => "lead")); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        appContentBuilder.init("<?php echo get_uri('leads/overview/' . $lead_info->id); ?>", {
            id: "lead-details-page-builder",
            data: {
                view_type: "lead_meta"
            },
            reloadHooks: [{
                    type: "app_form",
                    id: "lead-form"
                },
                {
                    type: "app_modifier",
                    group: "lead_info"
                }
            ],
            reload: function(bind, result) {
                bind("#lead-details-lead-info", result.lead_info);
                bind("#lead-details-lead-custom-fields-info", result.lead_custom_fields_info);
            }
        });

        $('body').on('click', '[data-act=lead-modifier]', function(e) {
            $(this).appModifier({
                dropdownData: {
                    status: <?php echo json_encode($lead_statuses); ?>,
                    owner_id: <?php echo json_encode($team_members_dropdown); ?>,
                    source: <?php echo json_encode($lead_sources); ?>,
                    labels: <?php echo json_encode($label_suggestions); ?>,
                    managers: <?php echo json_encode($team_members_dropdown); ?>
                }
            });
            return false;
        });

        //trigger related tab
        $('body').on('click', '.lead-overview-widget-link', function(e) {
            var tab = $(this).attr("data-target");
            if (tab) {
                if (tab === "#lead-estimate-requests") {
                    $("[data-bs-target='#lead-estimates']").attr("data-reload", "1").trigger("click");

                    setTimeout(function() {
                        $("[data-bs-target='#lead-estimate-requests']").trigger("click");
                    }, 100);
                } else {
                    $("[data-bs-target='" + tab + "']").attr("data-reload", "1").trigger("click");
                }
            }
        });
    });
</script>