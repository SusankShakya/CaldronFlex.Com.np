<div class="table-responsive">
    <table id="all-timesheet-summary-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var filterDropdowns = [{name: "group_by", class: "w200", options: <?php echo $group_by_dropdown; ?>}];
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
        $("#all-timesheet-summary-table").appTable({
            source: '<?php echo_uri("projects/timesheet_summary_list_data/" . $user_id); ?>',
            filterDropdown: filterDropdowns,
            rangeDatepicker: [{startDate: {name: "start_date", value: dynamicDates.today}, endDate: {name: "end_date", value: dynamicDates.today}, showClearButton: true, label: "<?php echo app_lang('date'); ?>", ranges: ['today', 'yesterday', 'last_7_days', 'last_30_days', 'this_month', 'last_month', 'this_year', 'last_year' ]}],
            columns: [
                {title: "<?php echo app_lang('project'); ?>", "class": "all"},
                {title: "<?php echo app_lang('client') ?>"},
                {title: "<?php echo app_lang('member'); ?>", "class": "all"},
                {title: "<?php echo app_lang('task'); ?>"},
                {title: "<?php echo app_lang('duration'); ?>", "class": "w15p text-right all"},
                {title: "<?php echo app_lang('hours'); ?>", "class": "w15p  text-right"}, 
                {visible: false, title: "<?php echo app_lang('hours') ?>", "class": "text-right"} //decimal seperator as dot always
            ],
            printColumns: [0, 1, 2, 3, 4, 5],
            xlsColumns: [0, 1, 2, 3, 4, 6],
            summation: [{column: 4, dataType: 'time'}, {column: 5, dataType: 'number'}],
            onRelaodCallback: function (tableInstance, filterParams) {

                //we'll show/hide the task/member column based on the group by status

                if (filterParams && filterParams.group_by === "member") {
                    //show member
                    showHideAppTableColumn(tableInstance, 0, false);
                    showHideAppTableColumn(tableInstance, 1, false);
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, false);
                } else if (filterParams && filterParams.group_by === "project") {
                    //show project
                    showHideAppTableColumn(tableInstance, 0, true);
                    showHideAppTableColumn(tableInstance, 1, true);
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 3, false);
                } else if (filterParams && filterParams.group_by === "task") {
                    //show task
                    showHideAppTableColumn(tableInstance, 0, false);
                    showHideAppTableColumn(tableInstance, 1, false);
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 3, true);
                } else {
                    //show all
                    showHideAppTableColumn(tableInstance, 0, true);
                    showHideAppTableColumn(tableInstance, 1, true);
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, true);
                }

                //clear this status for next time load
                clearAppTableState(tableInstance);
            }
        });
    });
</script>