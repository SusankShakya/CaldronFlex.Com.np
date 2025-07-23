<div class="clearfix page-content xs-full-width">
    <div class="container-fluid">
        <div class="row clients-view-button">
            <div class="col-md-12">
                <div class="page-title clearfix no-border no-border-top-radius no-bg">
                    <h1 class="pl0">
                        <i data-feather="briefcase" class="icon"></i> <?php echo $client_info->company_name ?>
                        <span id="star-mark">
                            <?php
                            if ($is_starred) {
                                echo view('clients/star/starred', array("client_id" => $client_info->id));
                            } else {
                                echo view('clients/star/not_starred', array("client_id" => $client_info->id));
                            }
                            ?>
                        </span>

                        <?php if ($client_info->lead_status_id) { ?>
                            <?php $lead_information = app_lang("past_lead_information") . "<br />"; ?>
                            <?php if ($client_info->created_date) { ?>
                                <?php $lead_information .= app_lang("lead_created_at") . ": " . format_to_date($client_info->created_date, false) . "<br />"; ?>
                            <?php } ?>
                            <?php if ($client_info->client_migration_date && is_date_exists($client_info->client_migration_date)) { ?>
                                <?php $lead_information .= app_lang("migrated_to_client_at") . ": " . format_to_date($client_info->client_migration_date, false) . "<br />"; ?>
                            <?php } ?>
                            <?php if ($client_info->last_lead_status) { ?>
                                <?php $lead_information .= app_lang("last_status") . ": " . $client_info->last_lead_status . "<br />"; ?>
                            <?php } ?>
                            <?php if ($client_info->owner_id) { ?>
                                <?php $lead_information .= app_lang("owner") . ": " . $client_info->owner_name; ?>
                            <?php } ?>

                            <span data-bs-toggle="tooltip" data-bs-html="true" title="<?php echo $lead_information; ?>"><i data-feather="help-circle" class="icon-16"></i></span>
                        <?php } ?>

                    </h1>
                </div>

                <ul id="client-details-tabs" data-bs-toggle="ajax-tab" data-do-not-save-state="1" class="nav nav-tabs scrollable-tabs rounded mb20" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri(("clients/overview/" . $client_info->id)); ?>" data-bs-target="#client-overview"> <?php echo app_lang('overview'); ?></a></li>

                    <?php if ($show_project_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/projects/" . $client_info->id); ?>" data-bs-target="#client-projects"><?php echo app_lang('projects'); ?></a></li>
                    <?php } ?>

                    <?php if ($show_subscription_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/subscriptions/" . $client_info->id); ?>" data-bs-target="#client-subscriptions"> <?php echo app_lang('subscriptions'); ?></a></li>
                    <?php } ?>

                    <?php if ($show_invoice_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/invoices/" . $client_info->id); ?>" data-bs-target="#client-invoices"> <?php echo app_lang('invoices'); ?></a></li>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/payments/" . $client_info->id); ?>" data-bs-target="#client-payments"> <?php echo app_lang('payments'); ?></a></li>
                    <?php } ?>

                    <?php if ($can_edit_invoices) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo get_uri("invoice_payments/statement/" . $client_info->id . "/") ?>" data-bs-target="#statement-tab"><?php echo app_lang("statement"); ?></a></li>
                    <?php } ?>

                    <?php if ($show_order_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/orders/" . $client_info->id); ?>" data-bs-target="#client-orders"> <?php echo app_lang('orders'); ?></a></li>
                    <?php } ?>

                    <?php if ($show_estimate_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/estimates/" . $client_info->id); ?>" data-bs-target="#client-estimates"> <?php echo app_lang('estimates'); ?></a></li>
                    <?php } ?>

                    <?php if ($show_proposal_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/proposals/" . $client_info->id); ?>" data-bs-target="#client-proposals"> <?php echo app_lang('proposals'); ?></a></li>
                    <?php } ?>

                    <?php if ($show_contract_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/contracts/" . $client_info->id); ?>" data-bs-target="#client-contracts"> <?php echo app_lang('contracts'); ?></a></li>
                    <?php } ?>

                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/files/" . $client_info->id . "/" . $files_tab . "/" . $folder_id); ?>" data-bs-target="#client-files"><?php echo app_lang('files'); ?></a></li>

                    <?php if ($show_expense_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/expenses/" . $client_info->id); ?>" data-bs-target="#client-expenses"> <?php echo app_lang('expenses'); ?></a></li>
                    <?php } ?>
                    <?php if ($show_note_info && $note_card_layout === "tab") { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/notes/" . $client_info->id); ?>" data-bs-target="#client-notes"> <?php echo app_lang('notes'); ?></a></li>
                    <?php } ?>
                    <?php if ($task_card_layout === "tab") { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/tasks/" . $client_info->id); ?>" data-bs-target="#client-tasks"> <?php echo app_lang('tasks'); ?></a></li>
                    <?php } ?>
                    <?php if ($show_ticket_info && $ticket_card_layout === "tab") { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/tickets/" . $client_info->id); ?>" data-bs-target="#client-tickets"> <?php echo app_lang('tickets'); ?></a></li>
                    <?php } ?>
                    <?php if ($show_event_info && $event_card_layout === "tab") { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/events/" . $client_info->id); ?>" data-bs-target="#client-events"> <?php echo app_lang('events'); ?></a></li>
                    <?php } ?>

                    <?php
                    $hook_tabs = array();
                    $hook_tabs = app_hooks()->apply_filters('app_filter_client_details_ajax_tab', $hook_tabs, $client_info->id);
                    $hook_tabs = is_array($hook_tabs) ? $hook_tabs : array();
                    foreach ($hook_tabs as $hook_tab) {
                    ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo get_array_value($hook_tab, 'url') ?>" data-bs-target="#<?php echo get_array_value($hook_tab, 'target') ?>"><?php echo get_array_value($hook_tab, 'title') ?></a></li>
                    <?php
                    }
                    ?>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="client-overview"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-projects"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-subscriptions"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-invoices"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-payments"></div>
                    <div role="tabpanel" class="tab-pane fade default-bg" id="statement-tab"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-orders"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-estimates"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-proposals"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-contracts"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-files"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-expenses"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-notes"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-tasks"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-tickets"></div>
                    <div role="tabpanel" class="tab-pane fade" id="client-events"></div>
                    <?php foreach ($hook_tabs as $hook_tab) { ?>
                        <div role="tabpanel" class="tab-pane fade" id="<?php echo get_array_value($hook_tab, 'target') ?>"></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        setTimeout(function() {
            var tab = "<?php echo $tab; ?>";
            if (tab === "projects") {
                $("[data-bs-target='#client-projects']").trigger("click");
            } else if (tab === "invoices") {
                $("[data-bs-target='#client-invoices']").trigger("click");
            } else if (tab === "payments") {
                $("[data-bs-target='#client-payments']").trigger("click");
            } else if (tab === "file_manager") {
                $("[data-bs-target='#client-files']").trigger("click");
            } else if (tab === "proposals") {
                $("[data-bs-target='#client-proposals']").trigger("click");
            } else if (tab === "subscriptions") {
                $("[data-bs-target='#client-subscriptions']").trigger("click");
            } else if (tab === "estimates") {
                $("[data-bs-target='#client-estimates']").trigger("click");
            } else if (tab === "orders") {
                $("[data-bs-target='#client-orders']").trigger("click");
            }
        }, 210);

        $('[data-bs-toggle="tooltip"]').tooltip();

    });
</script>