<?php
if (!$project_id) {
    load_css(array(
        "assets/js/gantt-chart/frappe-gantt.css"
    ));
    load_js(array(
        "assets/js/gantt-chart/frappe-gantt.js"
    ));
    echo "<div id='page-content' class='page-wrapper clearfix'>";
}
?>

<?php $add_task_button =  modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task'), array("class" => "btn btn-default ml10", "title" => app_lang('add_task'), "data-post-project_id" => $project_id)); ?>

<?php if (isset($show_tasks_tab) && $show_tasks_tab == true) { ?>
    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="title-tab">
            <h4 class="pl15 pt10 pr15"><?php echo app_lang("tasks"); ?></h4>
        </li>
        <?php echo view("tasks/tabs", array("active_tab" => "gantt", "selected_tab" => "")); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php echo $add_task_button; ?>
            </div>
        </div>

    </ul>
<?php } ?>

<div class="card border-top-0 rounded-top-0">
    <div id="gantt_view_selector_section" class="custom-toolbar filter-item-box">
        <?php
        $gantt_view_dropdown = array(
            array("id" => "Day", "text" => app_lang("days_view")),
            array("id" => "Week", "text" => app_lang("weeks_view")),
            array("id" => "Month", "text" => app_lang("months_view"))
        );

        helper('cookie');

        echo form_input(array(
            "id" => "gantt-view-dropdown",
            "name" => "gantt-view-dropdown",
            "class" => "w150 text-left",
            "value" => get_cookie("gantt_view_of_user_" . $login_user->id) ? get_cookie("gantt_view_of_user_" . $login_user->id) : "Day"
        ));

        if (!isset($show_tasks_tab)) {
            echo $add_task_button;
        }

        ?>
    </div>
    <div id="gantt-filters"></div>
    <div id="gantt-chart-container" class="w100p">
    </div>
</div>

<?php
if (!$project_id) {
    echo "</div>";
}

echo modal_anchor(get_uri("tasks/view"), "", array("id" => "show_task_hidden", "class" => "hide", "data-modal-lg" => "1"));
?>

<script type="text/javascript">
    $(document).ready(function() {
        var filterDropdown = [];
        filterDropdown.push({
            name: "group_by",
            class: "w200",
            options: <?php echo $group_by_dropdown; ?>
        });
        <?php if (!$project_id) { ?>
            filterDropdown.push({
                name: "project_id",
                class: "w200",
                options: <?php echo $projects_dropdown; ?>,
                dependent: ["milestone_id"]
            });
        <?php } ?>
        <?php if ($show_project_members_dropdown) { ?>
            filterDropdown.push({
                name: "user_id",
                class: "w200",
                options: <?php echo $project_members_dropdown; ?>
            });
        <?php } ?>
        <?php if ($show_milestone_info) { ?>
            filterDropdown.push({
                name: "milestone_id",
                class: "w200",
                options: <?php echo $milestone_dropdown; ?>,
                dependency: ["project_id"],
                dataSource: '<?php echo_uri("tasks/get_milestones_for_filter") ?>'
            });
        <?php } ?>

        filterDropdown.push(<?php echo $custom_field_filters; ?>);

        var smartFilterContext = "all_tasks_gantt";
        <?php if ($project_id) { ?>
            smartFilterContext = "project_tasks_gantt";
        <?php } ?>

        $("#gantt-filters").appFilters({
            source: '<?php echo_uri("tasks/gantt_chart_view/" . $project_id); ?>',
            targetSelector: '#gantt-chart-container',
            reloadSelector: '#reload-gantt-button',
            smartFilterIdentity: smartFilterContext, //a to z and _ only. should be unique to avoid conflicts
            contextMeta: {
                contextId: "<?php echo $project_id; ?>",
                dependencies: ["milestone_id"]
            }, //useful to seperate instance related filters. Ex. Milestones are different for each projects. 
            filterDropdown: filterDropdown,
            multiSelect: [{
                class: "w200",
                name: "status_id",
                text: "<?php echo app_lang('status'); ?>",
                options: <?php echo $status_dropdown; ?>
            }],
            beforeRelaodCallback: function() {},
            afterRelaodCallback: function() {}
        });

        setTimeout(function() {
            $("#gantt-filters").find(".filter-section-right").append($("#gantt_view_selector_section"));
            $("#gantt-view-dropdown").select2({
                data: <?php echo json_encode($gantt_view_dropdown); ?>
            });
        })

        window.ganttScrollToLast = false;
        window.ganttScrollLeft = 0;
        window.reloadGantt = function(scrollToLast) {
            window.ganttScrollLeft = $("#gantt-chart .gantt-container").scrollLeft();
            window.ganttScrollToLast = scrollToLast;
            $("#reload-gantt-button").trigger("click");
        };

    });
</script>