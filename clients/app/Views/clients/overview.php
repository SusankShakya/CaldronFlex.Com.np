<div class="clearfix mt15">
    <div class="details-view-wrapper d-flex">
        <div class="w-100">
            <div class="w-100 details-view-left-section">
                <div id="client-summary">
                    <?php echo view("clients/client_overview_info"); ?>
                </div>

                <?php if ($show_invoice_info) { ?>
                    <div id="client-view-invoice-statistics">
                        <?php echo invoice_overview_widget(array("client_id" => $client_info->id, "currency" => $client_info->currency, "currency_symbol" => $client_info->currency_symbol)); ?>
                    </div>
                <?php } ?>

                <div id="client-view-contacts-section">
                    <?php echo view("clients/contacts/contacts_card", array("client_id" => $client_info->id)); ?>
                </div>

                <div class="row">
                    <?php
                    $show_event_card = (!$event_card_layout || $event_card_layout != "tab");
                    $show_ticket_card = (!$ticket_card_layout || $ticket_card_layout != "tab");

                    $ticketClass = $show_event_info && $show_event_card ? 'col-md-6' : 'col-md-12';
                    $eventClass = $show_ticket_info && $show_ticket_card ? 'col-md-6' : 'col-md-12';
                    ?>

                    <?php if ($show_ticket_info && $show_ticket_card) { ?>
                        <div class="<?php echo $ticketClass ?>">
                            <?php echo view("clients/tickets/index", array("view_type" => "overview")); ?>
                        </div>
                    <?php } ?>

                    <?php if ($show_event_info && $show_event_card) { ?>
                        <div class="<?php echo $eventClass ?>">
                            <div id="client-events-section">
                                <?php echo view("events/index", array("is_mobile" => true, "view_type" => "overview")); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="flex-shrink-0 details-view-right-section">
            <div id="client-details-client-info">
                <?php echo view("clients/client_info"); ?>
            </div>

            <div id="client-details-client-custom-fields-info">
                <?php echo view("clients/client_custom_fields_info"); ?>
            </div>

            <?php if (!$task_card_layout || $task_card_layout != "tab") { ?>
                <div id="client-tasks-section">
                    <?php echo view("clients/tasks/index", array("view_type" => "overview")); ?>
                </div>
            <?php } ?>

            <?php if ($show_note_info && (!$note_card_layout || $note_card_layout != "tab")) { ?>
                <div id="client-notes-section">
                    <?php echo view("clients/notes/index", array("view_type" => "overview")); ?>
                </div>
            <?php } ?>

            <?php if (can_access_reminders_module()) { ?>
                <div class="card reminders-card" id="client-reminders">
                    <div class="card-header fw-bold">
                        <i data-feather="clock" class="icon-16"></i> &nbsp;<?php echo app_lang("reminders") . " (" . app_lang('private') . ")"; ?>
                    </div>
                    <div class="card-body">
                        <?php echo view("reminders/reminders_view_data", array("client_id" => $client_info->id, "hide_form" => true, "reminder_view_type" => "client")); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        appContentBuilder.init("<?php echo get_uri('clients/overview/' . $client_info->id); ?>", {
            id: "client-details-page-builder",
            data: {
                view_type: "client_meta"
            },
            reloadHooks: [{
                    type: "app_form",
                    id: "client-form"
                },
                {
                    type: "app_modifier",
                    group: "client_info"
                }
            ],
            reload: function(bind, result) {
                bind("#client-details-client-info", result.client_info);
                bind("#client-details-client-custom-fields-info", result.client_custom_fields_info);
            }
        });

        <?php if ($can_edit_clients) { ?>
            $('body').on('click', '[data-act=client-modifier]', function(e) {
                $(this).appModifier({
                    dropdownData: {
                        labels: <?php echo json_encode($label_suggestions); ?>,
                        group_ids: <?php echo json_encode($groups_dropdown); ?>,
                        owner_id: <?php echo json_encode($team_members_dropdown); ?>,
                        managers: <?php echo json_encode($team_members_dropdown); ?>
                    }
                });
                return false;
            });
        <?php } ?>

        //trigger related tab when it's client overview widget
        $('body').on('click', '.client-overview-widget-link', function(e) {
            var tab = $(this).attr("data-target");
            if (tab) {
                $("[data-bs-target='" + tab + "']").attr("data-reload", "1").trigger("click");
            }
        });

        //trigger invoices tab when click on invoice overview widget
        $('body').on('click', '.invoice-overview-widget-link', function(e) {
            e.preventDefault();

            var filter = $(this).attr("data-filter");
            if (filter) {
                window.selectedInvoiceQuickFilter = filter;
                $("[data-bs-target='#client-invoices']").attr("data-reload", "1").trigger("click");
            }
        });
    });
</script>