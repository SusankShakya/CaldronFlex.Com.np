<?php
if (!isset($project_id)) {
    $project_id = "";
}
?>

<script type="text/javascript">
    tasksTableRowCallback = function(nRow, aData) {

        var $td = $('td:eq(0)', nRow);
        if ($td.css('display') === 'none') {
            $td = $('td:eq(1)', nRow);
        }

        $td.attr("style", "border-left-color:" + aData[0] + " !important;").addClass('list-status-border');
        //add activated sub task filter class
        setTimeout(function() {
            var searchValue = $('#task-table').closest(".dataTables_wrapper").find("input[type=search]").val();
            if (searchValue && searchValue.substring(0, 1) === "#") {
                $('#task-table').find("[main-task-id='" + searchValue + "']").removeClass("filter-sub-task-button").addClass("remove-filter-button sub-task-filter-active");
            }
        }, 50);
    };

    $(document).ready(function() {
        setTimeout(function() {
            appAjaxRequest({
                url: "<?php echo get_uri('tasks/get_task_statuses_dropdown/' . $project_id) ?>",
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    if (result) {
                        $('body').on('click', '[data-act=update-task-status]', function() {
                            $(this).appModifier({
                                value: $(this).attr('data-value'),
                                actionUrl: '<?php echo_uri("tasks/save_task_status") ?>/' + $(this).attr('data-id'),
                                select2Option: {
                                    data: result
                                },
                                onSuccess: function(response, newValue) {
                                    if (response.success) {
                                        $("#task-table").appTable({
                                            newData: response.data,
                                            dataId: response.id
                                        });
                                    }
                                }
                            });

                            return false;
                        });
                    }
                }
            });
        }, 3000);

        $('body').on('click', '[data-act=update-task-status-checkbox]', function() {
            $(this).find("span").removeClass("checkbox-checked");
            $(this).find("span").addClass("inline-loader");
            appAjaxRequest({
                url: '<?php echo_uri("tasks/save_task_status") ?>/' + $(this).attr('data-id'),
                type: 'POST',
                dataType: 'json',
                data: {
                    value: $(this).attr('data-value')
                },
                success: function(response) {
                    if (response.success) {
                        $("#task-table").appTable({
                            newData: response.data,
                            dataId: response.id
                        });
                    }
                }
            });
        });
    });
</script>