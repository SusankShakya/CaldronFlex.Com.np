<?php
if (!$user_id) {
    echo get_reports_topbar();
?>
    <div id="page-content" class="page-wrapper clearfix grid-button">
    <?php } ?>

    <div class="card border-top-0 rounded-top-0">
        <ul id="project-all-timesheet-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title scrollable-tabs" role="tablist">
            <li class="title-tab">
                <h4 class="pl15 pt10 pr15"><?php echo app_lang("timesheets"); ?></h4>
            </li>

            <li><a id="timesheet-details-button" role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#timesheet-details"><?php echo app_lang("details"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("projects/all_timesheet_summary/$user_id"); ?>" data-bs-target="#timesheet-summary"><?php echo app_lang('summary'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("projects/timesheet_chart/0/$user_id"); ?>" data-bs-target="#timesheet-chart"><?php echo app_lang('chart'); ?></a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="timesheet-details">
                <div class="table-responsive">
                    <table id="all-project-timesheet-table" class="display" width="100%">
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="timesheet-summary"></div>
            <div role="tabpanel" class="tab-pane fade" id="timesheet-chart"></div>
        </div>
    </div>

    <?php if (!$user_id) { ?>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {
        var endTimeVisibility = true;
<?php if (get_setting("users_can_input_only_total_hours_instead_of_period")) { ?>
            endTimeVisibility = false;
<?php } ?>
    
    var optionVisibility = false;
<?php if ($login_user->user_type === "staff" && ($login_user->is_admin || get_array_value($login_user->permissions, "timesheet_manage_permission"))) { ?>
            optionVisibility = true;
<?php } ?>

        var filterDropdowns = [];
        var userId = <?php echo $user_id; ?>;
        if (!userId) {
            filterDropdowns.push({name: "user_id", class: "w200", options: <?php echo $members_dropdown; ?>});
        }

        filterDropdowns.push({name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?>, dependency: ["client_id"], dataSource: '<?php echo_uri("projects/get_projects_of_selected_client_for_filter") ?>', selfDependency: true});  //projects are dependent on client. but we have to show all projects, if there is no selected client

        <?php if (!$user_id && ($login_user->is_admin || get_array_value($login_user->permissions, "client"))) { ?>
        filterDropdowns.push({name: "client_id", class: "w200", options: <?php echo $clients_dropdown; ?>, dependent: ["project_id"]}); //reset projects on changing of client
        <?php } ?>

        filterDropdowns.push(<?php echo $custom_field_filters; ?>);
        var dynamicDates = getDynamicDates();
        $("#all-project-timesheet-table").appTable({
            source: '<?php echo_uri("projects/timesheet_list_data/" . $user_id) ?>',
            serverSide: true,
            filterDropdown: filterDropdowns,
            rangeDatepicker: [{startDate: {name: "start_date", value: dynamicDates.today}, endDate: {name: "end_date", value: dynamicDates.today}, showClearButton: true, label: "<?php echo app_lang('date'); ?>", ranges: ['today', 'yesterday', 'last_7_days', 'last_30_days', 'this_month', 'last_month', 'this_year', 'last_year' ]}],
            columns: [
                {title: "<?php echo app_lang('member') ?>", "class": "all", order_by: "member_name"},
                {title: "<?php echo app_lang('project') ?>", order_by: "project"},
                {title: "<?php echo app_lang('client') ?>", order_by: "client"},
                {title: "<?php echo app_lang('task') ?>", order_by: "task_title"},
                {visible: false, searchable: false, order_by: "start_time"},
                {title: "<?php echo get_setting("users_can_input_only_total_hours_instead_of_period") ? app_lang("date") : app_lang('start_time') ?>", "iDataSort": 4, order_by: "start_time"},
                {visible: false, searchable: false, order_by: "end_time"},
                {title: "<?php echo app_lang('end_time') ?>", "iDataSort": 6, visible: endTimeVisibility, order_by: "end_time"},
                {title: "<?php echo app_lang('total') ?>", "class": "text-right all"},
                {visible: false, title: "<?php echo app_lang('hours') ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('hours') ?>", "class": "text-right"},//follow the decimal seperator setting. Only for print. 
                {title: '<i data-feather="message-circle" class="icon-16"></i>', "class": "w50"}
<?php echo $custom_field_headers; ?>,
                {visible: optionVisibility, title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 11], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9, 11], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 8, fieldName: "total_timesheet_value", dataType: 'time'}]
        });
    });
</script>