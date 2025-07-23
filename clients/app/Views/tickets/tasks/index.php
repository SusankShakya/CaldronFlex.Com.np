<div class="card ticket-tasks-container">
    <div class="card-header fw-bold">
        <i data-feather="check-circle" class="icon-16"></i> &nbsp;<?php echo app_lang("tasks"); ?>
    </div>

    <div class="card-body">
        <?php if ($ticket_info->project_id != "0" && $show_project_reference == "1" && $can_create_tasks) { ?>
            <div class="dropdown-toggle btn btn-outline-default" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                <i data-feather="zap" class="icon-16"></i> <?php echo app_lang('actions'); ?>
            </div>
            <ul class="dropdown-menu" role="menu">
                <li role="presentation"><?php echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_task'), array("class" => "dropdown-item", "data-post-ticket_id" => $ticket_info->id, "title" => app_lang('create_new_task'))); ?></li>
                <li role="presentation"><?php echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_task_in_project'), array("title" => app_lang('create_new_task'), "data-post-project_id" => $ticket_info->project_id, "data-post-ticket_id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>

            </ul>
        <?php
        } else {
            echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_task'), array("class" => "inline-block", "data-post-ticket_id" => $ticket_info->id, "title" => app_lang('add_task')));
        } ?>
    </div>

    <div class="table-responsive">
        <table id="ticket-details-page-task-table" class="display no-thead b-t b-b-only no-hover hide-dtr-control hide-status-checkbox" width="100%">       
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        
        $("#ticket-details-page-task-table").appTable({
            source: '<?php echo_uri("tasks/list_data/ticket/" . $ticket_info->id) ?>' + '/1',
            order: [[0, "desc"]],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            reloadHooks: [{
                    type: "app_form",
                    id: "task-form",
                    filter: {ticket_id: "<?php echo $ticket_info->id ?>"},
                },
                {
                    type: "app_table_row_update",
                    tableId: "ticket-details-page-task-table"
                },
                {
                    type: "app_modifier",
                    group: "task_info"
                }
            ],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang('id') ?>", order_by: "id"},
                {title: "<?php echo app_lang('title') ?>", "class": "all mobile-mirror", order_by: "title"},
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
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option"}
            ],
            rowCallback: tasksTableRowCallback //load this function from the task_table_common_script.php 
        });

    });
</script>
<?php echo view("tasks/task_table_common_script"); ?>