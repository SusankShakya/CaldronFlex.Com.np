<div class="card bg-white">
    <div class="card-header">
        <i data-feather="list" class="icon-16"></i>&nbsp; <?php echo app_lang('my_tasks'); ?>
    </div>

    <div class="table-responsive" id="my-task-list-widget-table">
        <table id="task-table" class="display" cellspacing="0" width="100%">            
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        if(!isMobile()){
            initScrollbar('#my-task-list-widget-table', {
                setHeight: 330
            });
        }

        var showIdColumn = true;

        if (isMobile()) {
            showIdColumn = false;
        }

        setTimeout(function() {
            $("#task-table").appTable({
                source: '<?php echo_uri("tasks/all_tasks_list_data/1") ?>',
                order: [[5, "desc"]],
                displayLength: 30,
                columns: [
                    {visible: false, searchable: false},
                    {title: "<?php echo app_lang('id') ?>", "class": "w70", visible: showIdColumn},
                    {title: "<?php echo app_lang('title') ?>", "class": "all"},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {title: "<?php echo app_lang('start_date') ?>", "iDataSort": 7, "class": "w80"},
                    {visible: false, searchable: false},
                    {title: "<?php echo app_lang('deadline') ?>", "iDataSort": 9, "class": "w80"},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {visible: false, searchable: false},
                    {title: "<?php echo app_lang('status') ?>", "class": "w80"},
                    {visible: false, searchable: false}
                ],
                onInitComplete: function () {
                    $("#task-table_wrapper .datatable-tools").addClass("hide");
                },
                rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    $('td:eq(0)', nRow).attr("style", "border-left-color:" + aData[0] + " !important;").addClass('list-status-border');
                }
            });
        }, 700);
        
    });
</script>
<?php echo view("tasks/task_table_common_script"); ?>