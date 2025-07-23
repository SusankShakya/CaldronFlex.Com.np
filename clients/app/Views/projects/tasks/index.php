<div class="card">
    <div class="card-header title-tab">
        <h4 class="float-start"><?php echo app_lang('tasks'); ?></h4>
        <div class="title-button-group grid-button-xs">
            <?php
            if ($login_user->user_type == "staff" && $can_edit_tasks) {
                echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "task"));
            }
            if ($can_create_tasks) {
                echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_multiple_tasks'), array("class" => "btn btn-default hidden-xs", "title" => app_lang('add_multiple_tasks'), "data-post-project_id" => $project_id, "data-post-add_type" => "multiple"));
                echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task'), array("class" => "btn btn-default", "title" => app_lang('add_task'), "data-post-project_id" => $project_id));
            }
            ?>
        </div>
    </div>
    <div class="table-responsive">
        <table id="task-table" class="display xs-hide-dtr-control" width="100%">
        </table>
    </div>
</div>

<?php
//prepare status dropdown list
//select the non completed tasks for team members by default
//show all tasks for client by default.
$statuses = array();
foreach ($task_statuses as $status) {
    $is_selected = false;
    if ($login_user->user_type == "staff") {
        if ($status->key_name != "done") {
            $is_selected = true;
        }
    }

    $statuses[] = array("text" => ($status->key_name ? app_lang($status->key_name) : $status->title), "value" => $status->id, "isChecked" => $is_selected);
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        var userType = "<?php echo $login_user->user_type; ?>";
        var canEditOrDeleteTasks = "<?php echo ($can_edit_tasks || $can_delete_tasks); ?>";
        var optionVisibility = false;
        if (canEditOrDeleteTasks) {
            optionVisibility = true;
        }

        var milestoneVisibility = false;
        var showMilestoneInfo = "<?php echo $show_milestone_info; ?>";
        if (showMilestoneInfo) {
            milestoneVisibility = true;
        }

        var showResponsiveOption = true,
                showIdColumn = true,
                titleColumnClass = "all",
                optionColumnClass = "w100";
        if(isMobile()) {
            showIdColumn = false;            
        }

        var idColumnClass = "";
        if ("<?php echo get_setting("show_the_status_checkbox_in_tasks_list"); ?>" === "1") {
            idColumnClass = "w10p";
        }

        var mobileView = 0;
        if (isMobile()) {
            mobileView = 1;
        }

        var rowCallback = function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left-color:" + aData[0] + " !important;").addClass('list-status-border');
                //add activated sub task filter class
                setTimeout(function () {
                    var searchValue = $('#task-table').closest(".dataTables_wrapper").find("input[type=search]").val();
                    if (searchValue.substring(0, 1) === "#") {
                        $('#task-table').find("[main-task-id='" + searchValue + "']").removeClass("filter-sub-task-button").addClass("remove-filter-button sub-task-filter-active");
                    }
                }, 50);
            };


        if (userType === "client") {
            //don't show assignee and options to clients

            var filterDropdown = [];
            if (showMilestoneInfo) {
                filterDropdown.push({name: "milestone_id", class: "w150", options: <?php echo $milestone_dropdown; ?>});
            }
            filterDropdown.push({name: "assigned_to", class: "w150", options: <?php echo $assigned_to_dropdown; ?>});
            filterDropdown.push(<?php echo $custom_field_filters; ?>);
            $("#task-table").appTable({
                source: '<?php echo_uri("tasks/list_data/project/" . $project_id) ?>' + "/" + mobileView,
                serverSide: true,
                order: [[1, "desc"]],
                filterDropdown: filterDropdown,
                multiSelect: [
                    {
                        name: "status_id",
                        text: "<?php echo app_lang('status'); ?>",
                        options: <?php echo json_encode($statuses); ?>,
                        saveSelection: true
                    }
                ],
                columns: [
                    {visible: false, searchable: false},
                    {title: "<?php echo app_lang('id') ?>", visible: showIdColumn, "class": idColumnClass, order_by: "id"},
                    {title: "<?php echo app_lang('title') ?>", "class": titleColumnClass, order_by: "title"},
                    {title: "<?php echo app_lang('title') ?>", visible: false, searchable: false},
                    {title: "<?php echo app_lang('label') ?>", visible: false, searchable: false},
                    {title: "<?php echo app_lang('priority') ?>", visible: false, searchable: false},
                    {title: "<?php echo app_lang('points') ?>", visible: false, searchable: false},
                    {visible: false, searchable: false, order_by: "start_date"},
                    {title: "<?php echo app_lang('start_date') ?>", "iDataSort": 7, order_by: "start_date"},
                    {visible: false, searchable: false, order_by: "deadline"},
                    {title: "<?php echo app_lang('deadline') ?>", "iDataSort": 9, order_by: "deadline"},
                    {title: "<?php echo app_lang('milestone') ?>", visible: milestoneVisibility, order_by: "milestone"},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {title: "<?php echo app_lang('status') ?>", order_by: "status"}
                    <?php echo $custom_field_headers; ?>,
                    {title: '<i data-feather="menu" class="icon-16"></i>', visible: optionVisibility, "class": "text-center option " + optionColumnClass}
                ],
                printColumns: combineCustomFieldsColumns([1, 3, 4, 5, 6, 8, 10, 11, 15], '<?php echo $custom_field_headers; ?>'),
                xlsColumns: combineCustomFieldsColumns([1, 3, 4, 5, 6, 8, 10, 11, 15], '<?php echo $custom_field_headers; ?>'),
                rowCallback: tasksTableRowCallback //load this function from the task_table_common_script.php 
            });
        } else {

            var filterDropdown = [
                {name: "quick_filter", class: "w200", showHtml: true, options: <?php echo view("tasks/quick_filters_dropdown"); ?>},
                {name: "milestone_id", class: "w200", options: <?php echo $milestone_dropdown; ?>},
                {name: "priority_id", class: "w200", options: <?php echo $priorities_dropdown; ?>},
                {name: "label_id", class: "w200", options: <?php echo $labels_dropdown; ?>}

            ];
            var showAssignedTasksOnly = "<?php echo $show_assigned_tasks_only; ?>";
            if (!showAssignedTasksOnly) {
                filterDropdown.push({name: "assigned_to", class: "w200", options: <?php echo $assigned_to_dropdown; ?>});
            }
            filterDropdown.push(<?php echo $custom_field_filters; ?>);

            var batchUpdateUrl = "<?php echo get_uri("tasks/batch_update_modal_form"); ?>";
            var batchDeleteUrl = "<?php echo_uri('tasks/delete_selected_tasks'); ?>";

            var dynamicDates = getDynamicDates();
            $("#task-table").appTable({
                source: '<?php echo_uri("tasks/list_data/project/" . $project_id) ?>' + "/" + mobileView,
                serverSide: true,
                order: [[1, "desc"]],
                smartFilterIdentity: "project_tasks_list", //a to z and _ only. should be unique to avoid conflicts 
                contextMeta: {contextId: "<?php echo $project_id; ?>", dependencies: ["milestone_id"]}, //useful to seperate instance related filters. Ex. Milestones are different for each projects.
                selectionHandler: {postData:{project_id: "<?php echo $project_id; ?>"}, batchUpdateUrl: batchUpdateUrl, batchDeleteUrl: batchDeleteUrl},
                filterDropdown: filterDropdown,
                singleDatepicker: [{name: "deadline", defaultText: "<?php echo app_lang('deadline') ?>", class: "w200",
                        options: [
                            {value: "expired", text: "<?php echo app_lang('expired') ?>"},
                            {value: dynamicDates.today, text: "<?php echo app_lang('today') ?>"},
                            {value: dynamicDates.tomorrow, text: "<?php echo app_lang('tomorrow') ?>"},
                            {value: dynamicDates.in_next_7_days, text: "<?php echo sprintf(app_lang('in_number_of_days'), 7); ?>"},
                            {value: dynamicDates.in_next_15_days, text: "<?php echo sprintf(app_lang('in_number_of_days'), 15); ?>"}
                        ]}],
                multiSelect: [
                    {
                        name: "status_id",
                        text: "<?php echo app_lang('status'); ?>",
                        options: <?php echo json_encode($statuses); ?>,
                        saveSelection: true,
                        class: "w200"
                    }
                ],
                columns: [
                    {visible: false, searchable: false},
                    {title: "<?php echo app_lang('id') ?>", visible: showIdColumn, "class": idColumnClass, order_by: "id"},
                    {title: "<?php echo app_lang('title') ?>", "class": "all", order_by: "title"},
                    {title: "<?php echo app_lang('title') ?>", visible: false, searchable: false},
                    {title: "<?php echo app_lang('label') ?>", visible: false, searchable: false},
                    {title: "<?php echo app_lang('priority') ?>", visible: false, searchable: false},
                    {title: "<?php echo app_lang('points') ?>", visible: false, searchable: false},
                    {visible: false, searchable: false, order_by: "start_date"},
                    {title: "<?php echo app_lang('start_date') ?>", "iDataSort": 7, order_by: "start_date"},
                    {visible: false, searchable: false, order_by: "deadline"},
                    {title: "<?php echo app_lang('deadline') ?>", "iDataSort": 9, order_by: "deadline"},
                    {title: "<?php echo app_lang("milestone") ?>", order_by: "milestone"},
                    {visible: false, searchable: false},
                    {title: "<?php echo app_lang('assigned_to') ?>", "class": "min-w150", order_by: "assigned_to"},
                    {title: "<?php echo app_lang('collaborators') ?>", visible: showResponsiveOption},
                    {title: "<?php echo app_lang('status') ?>", order_by: "status"}
                    <?php echo $custom_field_headers; ?>,
                    {title: '<i data-feather="menu" class="icon-16"></i>', visible: optionVisibility, "class": "text-center option " + optionColumnClass}
                ],
                printColumns: combineCustomFieldsColumns([1, 3, 4, 5, 6, 8, 10, 11, 13, 14, 15], '<?php echo $custom_field_headers; ?>'),
                xlsColumns: combineCustomFieldsColumns([1, 3, 4, 5, 6, 8, 10, 11, 13, 14, 15], '<?php echo $custom_field_headers; ?>'),
                rowCallback: tasksTableRowCallback, //load this function from the task_table_common_script.php
            });
        }
    });
</script>

<?php echo view("tasks/task_table_common_script", array("project_id" => $project_id)); ?>
<?php echo view("tasks/update_task_read_comments_status_script"); ?>
<?php echo view("tasks/quick_filters_helper_js"); ?>