<div class="card">
    <div class="card-header fw-bold">
        <i data-feather="check-circle" class="icon-16"></i> &nbsp;<?php echo app_lang("tasks"); ?>
    </div>

    <div class="card-body">
        <?php echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_task'), array("class" => "", "data-post-proposal_id" => $proposal_id, "title" => app_lang('add_task'))); ?>
    </div>

    <div class="table-responsive">
        <table id="proposal-details-page-task-table" class="display no-thead b-t b-b-only no-hover hide-dtr-control hide-status-checkbox" width="100%">
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#proposal-details-page-task-table").appTable({
            source: '<?php echo_uri("tasks/list_data/proposal/" . $proposal_id) ?>' + '/1',
            order: [[0, "desc"]],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            reloadHooks: [{
                    type: "app_form",
                    id: "task-form",
                    filter: {proposal_id: "<?php echo $proposal_id ?>"},
                },
                {
                    type: "app_table_row_update",
                    tableId: "proposal-details-page-task-table"
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
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            rowCallback: tasksTableRowCallback //load this function from the task_table_common_script.php 
        });
    });
</script>
<?php echo view("tasks/task_table_common_script"); ?>