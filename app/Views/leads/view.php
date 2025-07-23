<div id="page-content" class="clearfix page-content">
    <div class="container-fluid leads-details-view">
        <div class="row">
            <div class="col-md-12">
                <div class="page-title clearfix no-border no-border-top-radius no-bg leads-page-title">
                    <h1 class="pl0">
                        <i data-feather="layers" class="icon"></i> <?php echo $lead_info->company_name ?>
                    </h1>
                    <div class="title-button-group mr0">
                        <?php echo modal_anchor(get_uri("leads/make_client_modal_form/") . $lead_info->id, "<i data-feather='briefcase' class='icon-16'></i> " . app_lang('make_client'), array("class" => "btn btn-primary float-end mr15", "title" => app_lang('make_client'))); ?>
                    </div>
                </div>

                <ul data-bs-toggle="ajax-tab" data-do-not-save-state="1" class="nav nav-tabs scrollable-tabs rounded mb20" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri(("leads/overview/" . $lead_info->id)); ?>" data-bs-target="#lead-overview"> <?php echo app_lang('overview'); ?></a></li>

                    <?php if ($show_estimate_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("leads/estimates/" . $lead_info->id); ?>" data-bs-target="#lead-estimates"> <?php echo app_lang('estimates'); ?></a></li>
                    <?php } ?>
                    <?php if ($show_proposal_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("leads/proposals/" . $lead_info->id); ?>" data-bs-target="#lead-proposals"> <?php echo app_lang('proposals'); ?></a></li>
                    <?php } ?>
                    <?php if ($show_contract_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("leads/contracts/" . $lead_info->id); ?>" data-bs-target="#lead-contracts"> <?php echo app_lang('contracts'); ?></a></li>
                    <?php } ?>
                    <?php if ($show_ticket_info) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("leads/tickets/" . $lead_info->id); ?>" data-bs-target="#lead-tickets"> <?php echo app_lang('tickets'); ?></a></li>
                    <?php } ?>

                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("leads/files/" . $lead_info->id); ?>" data-bs-target="#lead-files"><?php echo app_lang('files'); ?></a></li>

                    <?php
                    $hook_tabs = array();
                    $hook_tabs = app_hooks()->apply_filters('app_filter_lead_details_ajax_tab', $hook_tabs, $lead_info->id);
                    $hook_tabs = is_array($hook_tabs) ? $hook_tabs : array();
                    foreach ($hook_tabs as $hook_tab) {
                    ?>
                        <li><a role="presentation" data-bs-toggle="tab" href="<?php echo get_array_value($hook_tab, 'url') ?>" data-bs-target="#<?php echo get_array_value($hook_tab, 'target') ?>"><?php echo get_array_value($hook_tab, 'title') ?></a></li>
                    <?php
                    }
                    ?>
                </ul>
                <div class="tab-content lead-tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="lead-overview"></div>
                    <div role="tabpanel" class="tab-pane fade" id="lead-estimates"></div>
                    <div role="tabpanel" class="tab-pane fade" id="lead-proposals"></div>
                    <div role="tabpanel" class="tab-pane fade" id="lead-contracts"></div>
                    <div role="tabpanel" class="tab-pane fade" id="lead-files"></div>
                    <div role="tabpanel" class="tab-pane fade" id="lead-tickets"></div>
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
        var tab = "<?php echo $tab; ?>";
        if (tab === "info") {
            $("[data-bs-target='#lead-info']").trigger("click");
        }
    });
</script>