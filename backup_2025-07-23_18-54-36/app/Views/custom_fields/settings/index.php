<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "custom_fields";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>
        <div class="col-sm-9 col-lg-10">

            <div class="card clearfix">

                <ul id="custom-field-tab" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title scrollable-tabs" role="tablist">
                    <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("custom_fields"); ?></h4></li>
                    <li><a role="presentation" data-bs-toggle="tab" data-related_to="clients"  href="javascript:;" data-bs-target="#custom-field-clients"><?php echo app_lang("clients"); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" data-related_to="client_contacts" class="" href="<?php echo_uri("custom_fields/client_contacts/"); ?>" data-bs-target="#custom-field-client_contacts"><?php echo app_lang("client_contacts"); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" data-related_to="companies" href="<?php echo_uri("custom_fields/companies/"); ?>" data-bs-target="#custom-field-companies"><?php echo app_lang('companies'); ?></a></li>
                    <?php if (get_setting("module_contract")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="contracts" href="<?php echo_uri("custom_fields/contracts/"); ?>" data-bs-target="#custom-field-contracts"><?php echo app_lang('contracts'); ?></a></li>
                    <?php } ?>
                    <?php if (get_setting("module_estimate")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="estimates" href="<?php echo_uri("custom_fields/estimates/"); ?>" data-bs-target="#custom-field-estimates"><?php echo app_lang('estimates'); ?></a></li>
                    <?php } ?>
                    <?php if (get_setting("module_event")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="events" href="<?php echo_uri("custom_fields/events/"); ?>" data-bs-target="#custom-field-events"><?php echo app_lang('events'); ?></a></li>
                    <?php } ?>
                    <?php if (get_setting("module_expense")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="expenses" href="<?php echo_uri("custom_fields/expenses/"); ?>" data-bs-target="#custom-field-expenses"><?php echo app_lang('expenses'); ?></a></li>
                    <?php } ?>
                    <?php if (get_setting("module_invoice")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="invoices" href="<?php echo_uri("custom_fields/invoices/"); ?>" data-bs-target="#custom-field-invoices"><?php echo app_lang('invoices'); ?></a></li>
                    <?php } ?>
          
                    <li><a role="presentation" data-bs-toggle="tab" data-related_to="items" href="<?php echo_uri("custom_fields/items/"); ?>" data-bs-target="#custom-field-items"><?php echo app_lang('items'); ?></a></li>

                    <?php if (get_setting("module_lead")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="leads"  href="<?php echo_uri("custom_fields/leads/"); ?>" data-bs-target="#custom-field-leads"><?php echo app_lang("leads"); ?></a></li>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="lead_contacts" class="" href="<?php echo_uri("custom_fields/lead_contacts/"); ?>" data-bs-target="#custom-field-lead_contacts"><?php echo app_lang("lead_contacts"); ?></a></li>
                    <?php } ?>

                    <?php if (get_setting("module_order")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="orders" href="<?php echo_uri("custom_fields/orders/"); ?>" data-bs-target="#custom-field-orders"><?php echo app_lang('orders'); ?></a></li>
                    <?php } ?>

                    <li><a role="presentation" data-bs-toggle="tab" data-related_to="projects" href="<?php echo_uri("custom_fields/projects/"); ?>" data-bs-target="#custom-field-projects"><?php echo app_lang('projects'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" data-related_to="project_files" href="<?php echo_uri("custom_fields/project_files/"); ?>" data-bs-target="#custom-field-project_files"><?php echo app_lang('project_files'); ?></a></li>
                    <?php if (get_setting("module_proposal")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="proposals" href="<?php echo_uri("custom_fields/proposals/"); ?>" data-bs-target="#custom-field-proposals"><?php echo app_lang('proposals'); ?></a></li>
                    <?php } ?>
                    <?php if (get_setting("module_subscription")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="subscriptions" href="<?php echo_uri("custom_fields/subscriptions/"); ?>" data-bs-target="#custom-field-subscriptions"><?php echo app_lang('subscriptions'); ?></a></li>
                    <?php } ?>
                    <li><a role="presentation" data-bs-toggle="tab" data-related_to="tasks" href="<?php echo_uri("custom_fields/tasks/"); ?>" data-bs-target="#custom-field-tasks"><?php echo app_lang('tasks'); ?></a></li>

                    <li><a role="presentation" data-bs-toggle="tab" data-related_to="team_members" href="<?php echo_uri("custom_fields/team_members/"); ?>" data-bs-target="#custom-field-team_members"><?php echo app_lang('team_members'); ?></a></li>
                    
                    <?php if (get_setting("module_ticket")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="tickets" href="<?php echo_uri("custom_fields/tickets/"); ?>" data-bs-target="#custom-field-tickets"><?php echo app_lang('tickets'); ?></a></li>
                    <?php } ?>

                    <?php if (get_setting("module_project_timesheet")) { ?>
                        <li><a role="presentation" data-bs-toggle="tab" data-related_to="timesheets" href="<?php echo_uri("custom_fields/timesheets/"); ?>" data-bs-target="#custom-field-timesheets"><?php echo app_lang('timesheets'); ?></a></li>
                    <?php } ?>

                    <div class="tab-title clearfix no-border">
                        <div class="title-button-group">
                            <?php echo modal_anchor(get_uri("custom_fields/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_field'), array("class" => "btn btn-default", "id" => "add-field-button", "data-post-related_to" => "clients", "title" => app_lang('add_field'))); ?>
                        </div>
                    </div>
                </ul>


                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade clearfix" id="custom-field-clients">
                        <div class="mb0 p20">
                            <div class="table-responsive general-form">
                                <table id="custom-field-table-clients" class="display no-thead b-t b-b-only no-hover" cellspacing="0" width="100%">            
                                </table>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-client_contacts"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-lead_contacts"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-leads"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-projects"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-tasks"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-team_members"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-tickets"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-invoices"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-events"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-expenses"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-estimates"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-contracts"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-proposals"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-orders"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-timesheets"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-project_files"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-subscriptions"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-companies"></div>
                    <div role="tabpanel" class="tab-pane fade" id="custom-field-items"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#custom-field-tab a").click(function () {
            $("#add-field-button").attr("data-post-related_to", $(this).attr("data-related_to"));
        });

        setTimeout(function () {
            var tab = "<?php echo $tab; ?>";
            if (tab) {
                $("[data-bs-target='#custom-field-" + tab + "']").trigger("click");
            }
        }, 210);


        loadCustomFieldTable("clients");

    });

    loadCustomFieldTable = function (relatedTo) {

        $("#custom-field-table-" + relatedTo).appTable({
            source: '<?php echo_uri("custom_fields/list_data") ?>' + "/" + relatedTo,
            order: [[1, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {title: '', "class" :'w25'},
                {title: "<?php echo app_lang('title') ?>"},
                {visible: false},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-right option w100"}
            ],
            onInitComplete: function () {
                //apply sortable
                $("#custom-field-table-" + relatedTo).find("tbody").attr("id", "custom-field-table-sortable-" + relatedTo);
                var $selector = $("#custom-field-table-sortable-" + relatedTo);

                Sortable.create($selector[0], {
                    animation: 150,
                    handle: ".move-icon",
                    chosenClass: "sortable-chosen",
                    ghostClass: "sortable-ghost",
                    onUpdate: function (e) {
                        appLoader.show();
                        //prepare sort indexes 
                        var data = "";
                        $.each($selector.find(".field-row"), function (index, ele) {
                            if (data) {
                                data += ",";
                            }

                            data += $(ele).attr("data-id") + "-" + index;
                        });

                        //update sort indexes
                        appAjaxRequest({
                            url: '<?php echo_uri("custom_fields/update_field_sort_values") ?>' + "/" + relatedTo,
                            type: "POST",
                            data: {sort_values: data},
                            success: function () {
                                appLoader.hide();
                            }
                        });
                    }
                });

            }
        });
    };


</script>