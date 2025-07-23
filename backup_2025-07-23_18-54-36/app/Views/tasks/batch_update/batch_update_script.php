<script>
    function showHideTheBatchUpdateButton() {
        var projectId = $("[name='project_id']").val();

        var $batchUpdateBtn = $(".batch-update-btn"),
            $selectionHandlerBtn = $(".selection-handler-dropdown-btn");


        var hideBatchActiveBtn = function() {
            $selectionHandlerBtn.addClass("hide");
            $batchUpdateBtn.removeAttr("data-post-project_id");
            $("#" + $(".dataTable:visible").attr("id")).trigger("reset-selection-menu");
        };

        if (projectId) {
            //check user's permission
            appAjaxRequest({
                url: '<?php echo_uri("tasks/can_edit_task_of_the_project") ?>' + '/' + projectId,
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        $batchUpdateBtn.attr("data-post-project_id", projectId);
                        $selectionHandlerBtn.removeClass("hide");
                    } else {
                        hideBatchActiveBtn();
                    }
                }
            });
        } else {
            hideBatchActiveBtn();
        }
    }


    $(document).ready(function() {
        var $batchUpdateBtn = $(".batch-update-btn");

        //show batch update button after selecting project on global tasks
        $('body').on('change', "[name='project_id']", function() {
            showHideTheBatchUpdateButton();
        });

        //show batch update button after clicking smart filter
        $('body').on('click', ".bookmarked-filter-button", function() {
            showHideTheBatchUpdateButton();
        });
    });
</script>