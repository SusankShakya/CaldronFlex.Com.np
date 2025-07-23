<script type="text/javascript">
    $(document).ready(function() {
        $("#todo-inline-form").appForm({
            isModal: false,
            onSuccess: function(result) {
                $("#todo-title").val("");
                $("#todo-table").appTable({
                    newData: result.data,
                    dataId: result.id
                });
                appAlert.success(result.message, {
                    duration: 5000
                });
            }
        });

        var mobileView = 0,
            titlecolumnWidth = "w80",
            titleVisibility = true,
            dateVisibility = true,
            actionsVisibility = true,
            responsive = false,
            mobileMirror = false,
            displayLength = 10,
            todoListDelay = 0,
            viewType = "<?php echo isset($view_type) ? $view_type : ""; ?>";

        if (isMobile() || viewType == "widget") {
            mobileView = 1;
            titlecolumnWidth = "";
            titleVisibility = false;
            dateVisibility = false;
            actionsVisibility = false;
            responsive = true;
            mobileMirror = true;
            displayLength = 25;
        }

        if(viewType == "widget") {
            todoListDelay = 1000;
        }

        setTimeout(function() {
            $("#todo-table").appTable({
                source: '<?php echo_uri("todo/list_data/") ?>' + mobileView,
                order: [
                    [1, 'desc']
                ],
                stateSave: false,
                responsive: responsive,
                mobileMirror: mobileMirror,
                displayLength: displayLength,
                columns: [{
                        visible: false,
                        searchable: false
                    },
                    {
                        visible: false,
                        searchable: false
                    },
                    {
                        title: '',
                        "class": "all " + titlecolumnWidth,
                        sortable: false,
                    },
                    {
                        title: '<?php echo app_lang("title"); ?>',
                        "class": "",
                        sortable: false,
                        visible: titleVisibility
                    },
                    {
                        visible: false,
                        searchable: false
                    },
                    {
                        title: '<?php echo app_lang("date"); ?>',
                        "iDataSort": 4,
                        "class": "w175",
                        sortable: false,
                        visible: dateVisibility
                    },
                    {
                        title: '<i data-feather="menu" class="icon-16"></i>',
                        "class": "text-center option w100",
                        sortable: false,
                        visible: actionsVisibility
                    }
                ],
                radioButtons: [{
                        text: '<?php echo app_lang("to_do") ?>',
                        name: "status",
                        value: "to_do",
                        isChecked: true
                    },
                    {
                        text: '<?php echo app_lang("done") ?>',
                        name: "status",
                        value: "done",
                        isChecked: false
                    }
                ],
                printColumns: [3, 5],
                xlsColumns: [3, 5],
                rowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    $('td:eq(0)', nRow).addClass(aData[0]);
                },
                onInitComplete: function() {

                    if (isMobile()) {
                        $("#todo-sortable-switch-container").removeClass("hide");
                        $("#todo-table").addClass("no-thead hide-dtr-control");
                    } else {
                        // apply sortable for non-mobile
                        toggleSortableTodoItems(true);
                    }
                },
                reloadHooks: [{
                    type: "app_modifier",
                    group: "todo_info"
                }],
            });
        }, todoListDelay);

        $('body').on('click', '[data-act=update-todo-status-checkbox]', function() {
            $(this).find("span").removeClass("checkbox-checked");
            $(this).find("span").addClass("inline-loader");

            appAjaxRequest({
                url: '<?php echo_uri("todo/save_status/") ?>' + mobileView,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: $(this).attr('data-id'),
                    status: $(this).attr('data-value')
                },
                success: function(response) {
                    if (response.success) {
                        $("#todo-table").appTable({
                            newData: response.data,
                            dataId: response.id
                        });

                        //hide the row after update
                        // $("#todo-table").find(".todo-row-" + response.id).closest("tr").fadeOut();
                    }
                }
            });
        });

        $("#todo-sortable-switch").click(function() {
            $("#todo-table").toggleClass("sortable");

            // Call function to handle enabling or disabling sorting
            toggleSortableTodoItems();
        });

        function toggleSortableTodoItems(sortable = false) {
            var $selector = $("#todo-table");

            if ($selector.hasClass("sortable") || sortable) {
                $selector.appSortable({
                    actionUrl: '<?php echo_uri("todo/update_sort_value") ?>',
                    rowClass: ".todo-row",
                    sortDirection: "desc"
                });
            } else {
                // Destroy the Sortable instance if the class is removed
                if (Sortable.get($selector.find("tbody")[0])) {
                    Sortable.get($selector.find("tbody")[0]).destroy();
                }
            }
        }

    });
</script>