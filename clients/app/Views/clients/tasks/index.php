<?php $view_type = isset($view_type) ? $view_type : ""; ?>

<div class="card client-tasks-container">
    <div class="card-header fw-bold">
        <span class="d-inline-block mt-1">
            <i data-feather="check-circle" class="icon-16"></i> &nbsp;<?php echo app_lang("tasks"); ?>
        </span>

        <div class="float-end">
            <?php echo view("clients/layout_settings_dropdown", array("view_type" => $view_type, "context" => "client_details_tasks")); ?>

            <?php if ($view_type != "overview") { ?>
                <?php echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task'), array("class" => "btn btn-default mr5", "title" => app_lang('add_task'), "data-post-client_id" => $client_id)); ?>
            <?php } ?>
        </div>
    </div>
    <?php if ($view_type === "overview") { ?>
        <div class="card-body">
            <?php if ($can_create_tasks) {
                echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_task'), array("class" => "", "data-post-client_id" => $client_id, "title" => app_lang('add_task')));
            } ?>
        </div>
    <?php } ?>

    <div class="table-responsive">
        <table id="client-details-page-task-table" class="display <?php echo $view_type === "overview" ? "no-thead b-t b-b-only no-hover hide-dtr-control hide-status-checkbox" : "" ?>" width="100%">
        </table>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var viewType = <?php echo $view_type === "overview" ? 1 : 0 ?>;
        var hideTools = false,
            displayLength = 10,
            responsive = false,
            mobileMirror = false,
            dateColumnVisibility = true;

        if (viewType) {
            hideTools = true;
            displayLength = 100;
            responsive = true;
            mobileMirror = true;
        }

        $("#client-details-page-task-table").appTable({
            source: '<?php echo_uri("tasks/list_data/client/" . $client_id) ?>' + '/' + viewType,
            order: [
                [0, "desc"]
            ],
            hideTools: hideTools,
            displayLength: displayLength,
            stateSave: false,
            responsive: responsive,
            mobileMirror: mobileMirror,
            reloadHooks: [{
                    type: "app_form",
                    id: "task-form",
                    filter: {
                        client_id: "<?php echo $client_id ?>"
                    },
                },
                {
                    type: "app_table_row_update",
                    tableId: "client-details-page-task-table"
                },
                {
                    type: "app_modifier",
                    group: "task_info"
                }
            ],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang('id') ?>", order_by: "id"},
                {title: "<?php echo app_lang('title') ?>", "class": "all", order_by: "title"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false, order_by: "start_date"},
                {title: "<?php echo app_lang('start_date') ?>", "iDataSort": 7, order_by: "start_date"},
                {visible: false, searchable: false, order_by: "deadline"},
                {title: "<?php echo app_lang('deadline') ?>", "iDataSort": 9, order_by: "deadline"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang('assigned_to') ?>", "class": "min-w150", order_by: "assigned_to"},
                {title: "<?php echo app_lang('collaborators') ?>"},
                {title: "<?php echo app_lang('status') ?>", order_by: "status"}
<?php echo $custom_field_headers_of_task; ?>,
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100"}
            ],
            rowCallback: tasksTableRowCallback //load this function from the task_table_common_script.php 
        });
    });
</script>
<?php echo view("tasks/task_table_common_script"); ?>