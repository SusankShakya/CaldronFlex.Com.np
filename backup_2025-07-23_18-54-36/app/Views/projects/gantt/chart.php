<div id="gantt-chart" style="width: 100%;"></div>
<script type="text/javascript">
    $(document).ready(function() {
        var initGanttChart = function(result) {
            appLoader.hide();
            if (!result.length) {
                $("#gantt-chart").html("<div class='text-off text-center' style='padding: 41px;'><?php echo app_lang("no_result_found"); ?></div>");
                return;
            }

            $("#gantt-chart").html("");

            var viewMode = $("#gantt-view-dropdown").val();

            var gantt = new Gantt("#gantt-chart", result, {
                language: "custom",
                month_languages: AppLanugage.months,
                popup_trigger: "mouseover",
                view_mode: viewMode,
                on_click: function(task) {
                    if (task.dependencies.length && !task.group_task) {
                        $("#show_task_hidden").attr("data-post-id", task.id);
                        $("#show_task_hidden").attr("data-title", "<?php echo app_lang('task_info') . " #" ?>" + task.id);
                        $("#show_task_hidden").trigger("click");

                    } else {
                        collapseScrollLeft = $("#gantt-chart .gantt-container").scrollLeft();
                        gantt.collapse_group(task.id);
                        $("#gantt-chart .gantt-container").scrollLeft(collapseScrollLeft);
                    }
                },
                on_date_change: function(task, start, end) {
                    appLoader.show();

                    var data = {
                        start_date: moment(start, "YYYY-MM-DD").format("YYYY-MM-DD"),
                        deadline: moment(end, "YYYY-MM-DD").format("YYYY-MM-DD"),
                        task_id: task.id
                    };

                    appAjaxRequest({
                        url: "<?php echo get_uri('tasks/save_gantt_task_date') ?>",
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            appLoader.hide();
                            if (!result.success) {
                                appAlert.error(result.message);
                            }
                        }
                    });
                },
                custom_popup_html: function(task) {
                    var dateFormat = getJsDateFormat().toUpperCase(),
                        start = moment(task._start, "YYYY-MM-DD"),
                        end = moment(task._end, "YYYY-MM-DD"),
                        startDate = start.format(dateFormat),
                        endDate = end.subtract(1, 'days').format(dateFormat), //it's giving unnecessarily 1 extra day
                        daysCount = Math.abs(start.startOf('day').diff(end.startOf('day'), 'days')) + 1;

                    if (daysCount) {
                        if (daysCount === 1) {
                            daysCount = daysCount + " <?php echo app_lang("day"); ?>";
                        } else {
                            daysCount = daysCount + " <?php echo app_lang("days"); ?>";
                        }
                    }

                    return `
                    <div class="gantt-task-popup">
                        <div class="mb5">
                            <strong>${task.name}</strong>
                        </div>
                        <div><strong><?php echo app_lang("start_date"); ?>: </strong> ${startDate}</div>
                        <div><strong><?php echo app_lang("deadline"); ?>: </strong> ${endDate}</div>
                        <div><strong><?php echo app_lang("total"); ?>: </strong> ${daysCount}</div>
                    </div>
                `;
                }
            });

            //change view mode
            var $ganttView = $("#gantt-view-dropdown");

            $ganttView.on("change", function() {
                var type = $(this).val();
                changeGanttView(type);

                //save cookie
                setCookie("gantt_view_of_user_<?php echo $login_user->id; ?>", type);
            });

            function changeGanttView(type) {
                gantt.change_view_mode(type);
            }

            if (window.ganttScrollToLast && window.ganttScrollLeft) {
                setTimeout(function() {
                    $("#gantt-chart .gantt-container").animate({
                        scrollLeft: window.ganttScrollLeft
                    }, 'slow');
                }, 500);
            }

            //dragable board
            setTimeout(function() {
                var slider = document.querySelector('.gantt-container');
                var isDown = false;
                var startX;
                var scrollLeft;

                slider.addEventListener('mousedown', (e) => {
                    if ($(e.target).hasClass("grid-row")) {
                        isDown = true;
                        slider.classList.add('active');
                        startX = e.pageX - slider.offsetLeft;
                        scrollLeft = slider.scrollLeft;
                    }

                });
                slider.addEventListener('mouseleave', () => {
                    isDown = false;
                    slider.classList.remove('active');
                });
                slider.addEventListener('mouseup', () => {
                    isDown = false;
                    slider.classList.remove('active');
                });
                slider.addEventListener('mousemove', (e) => {
                    if (!isDown)
                        return;
                    e.preventDefault();
                    var x = e.pageX - slider.offsetLeft;
                    var walk = (x - startX) * 3; //scroll-fast
                    slider.scrollLeft = scrollLeft - walk;
                });
            });

        }

        initGanttChart(<?php echo $gantt_data; ?>);

    });
</script>