<div id="kanban-wrapper">
    <?php
    $columns_data = array();

    foreach ($leads as $lead) {

        $exising_items = get_array_value($columns_data, $lead->lead_status_id);
        if (!$exising_items) {
            $exising_items = "";
        }

        $source = "";
        if ($lead->lead_source_title) {
            $source = "<div class='float-start'><i data-feather='anchor' class='icon-14 text-off mr5'></i> " . $lead->lead_source_title . "</div>";
        }

        $owner = "";
        if ($lead->owner_id) {
            $owner = "<div class='float-end'><span class='avatar float-none' data-bs-toggle='tooltip' title='" . $lead->owner_name . "'><img src='" . get_avatar($lead->owner_avatar) . "' class='me-0'></span></div>";
        }

        $lead_labels = "";
        $lead_labels_data = make_labels_view_data($lead->labels_list);
        if ($lead_labels_data) {
            $lead_labels .= "<div class='meta mr5'>$lead_labels_data</div>";
        }

        $leads_total_counts = "<div class='mt10 float-end'>";

        if (!$lead_labels) {
            $leads_total_counts = "<div class='float-start'>";
        }

        //total contacts
        if ($lead->total_contacts_count) {
            $leads_total_counts .= "<span class='mr5' title='" . app_lang("contacts") . "'><i data-feather='users' class='icon-14 text-off'></i> " . $lead->total_contacts_count . "</span> ";
        }

        //total events
        if ($lead->total_events_count) {
            $leads_total_counts .= "<span class='mr5' title='" . app_lang("events") . "'><i data-feather='calendar' class='icon-14 text-off'></i> " . $lead->total_events_count . "</span> ";
        }

        //total notes
        if ($lead->total_notes_count) {
            $leads_total_counts .= "<span class='mr5' title='" . app_lang("notes") . "'><i data-feather='book' class='icon-14 text-off'></i> " . $lead->total_notes_count . "</span> ";
        }

        //total estimates
        if ($lead->total_estimates_count) {
            $leads_total_counts .= "<span class='mr5' title='" . app_lang("estimates") . "'><i data-feather='file' class='icon-14 text-off'></i> " . $lead->total_estimates_count . "</span> ";
        }

        //total estimate requests
        if ($lead->total_estimate_requests_count) {
            $leads_total_counts .= "<span class='mr5' title='" . app_lang("estimate_requests") . "'><i data-feather='file' class='icon-14 text-off'></i> " . $lead->total_estimate_requests_count . "</span> ";
        }

        //total files
        if ($lead->total_files_count) {
            $leads_total_counts .= "<span class='mr5' title='" . app_lang("files") . "'><i data-feather='file-text' class='icon-14 text-off'></i> " . $lead->total_files_count . "</span> ";
        }

        $leads_total_counts .= "</div>";

        $open_in_new_tab = anchor(get_uri("leads/view/" . $lead->id), "<i data-feather='external-link' class='icon-14'></i>", array("target" => "_blank", "class" => "float-end", "title" => app_lang("details")));

        $make_client = modal_anchor(get_uri("leads/make_client_modal_form/") . $lead->id, "<i data-feather='briefcase' class='icon-14'></i>", array("title" => app_lang('make_client'), "class" => "float-end mr10"));

        //custom fields to show in kanban
        $kanban_custom_fields_data = "";
        $kanban_custom_fields = get_custom_variables_data("leads", $lead->id, $login_user->is_admin);
        if ($kanban_custom_fields) {
            foreach ($kanban_custom_fields as $kanban_custom_field) {
                $kanban_custom_fields_data .= "<br /><small>" . get_array_value($kanban_custom_field, "custom_field_title") . ": " . view("custom_fields/output_" . get_array_value($kanban_custom_field, "custom_field_type"), array("value" => get_array_value($kanban_custom_field, "value"))) . "</small>";
            }
        }

        $item = $exising_items . "<span class='lead-kanban-item kanban-item' data-id='$lead->id' data-sort='$lead->new_sort' data-post-id='$lead->id'>
                    <div class='selection-pe-none'><span class='avatar'><img src='" . get_avatar($lead->primary_contact_avatar) . "'></span>" . anchor(get_uri("leads/view/" . $lead->id), $lead->company_name) . $open_in_new_tab . $make_client . "</div><div class='clearfix'></div>" .
                "<div class='mt15'>" . $source . $owner . "</div>" . $kanban_custom_fields_data . "<div class='clearfix'></div>" .
                $leads_total_counts . $lead_labels . "</span>";

        $columns_data[$lead->lead_status_id] = $item;
    }
    ?>

    <ul id="kanban-container" class="kanban-container clearfix">

        <?php foreach ($columns as $column) { ?>
            <li class="kanban-col" >
                <div class="kanban-col-title" style="border-bottom: 3px solid <?php echo $column->color ? $column->color : "#2e4053"; ?>;"> <?php echo $column->title; ?> </div>

                <div class="kanban-input general-form hide">
                    <?php
                    echo form_input(array(
                        "id" => "title",
                        "name" => "title",
                        "value" => "",
                        "class" => "form-control",
                        "placeholder" => app_lang('add_a_lead')
                    ));
                    ?>
                </div>

                <div  id="kanban-item-list-<?php echo $column->id; ?>" class="kanban-item-list" data-lead_status_id="<?php echo $column->id; ?>">
                    <?php echo get_array_value($columns_data, $column->id); ?>
                </div>
            </li>
        <?php } ?>

    </ul>
</div>

<img id="move-icon" class="hide" src="<?php echo get_file_uri("assets/images/move.png"); ?>" alt="..." />

<script type="text/javascript">
    var kanbanContainerWidth = "";

    adjustViewHeightWidth = function () {

        if (!$("#kanban-container").length) {
            return false;
        }


        var totalColumns = "<?php echo $total_columns ?>";
        var columnWidth = (335 * totalColumns) + 5;
        if(isMobile()){
            columnWidth = (230 * totalColumns) + 5;
        }

        if (columnWidth > kanbanContainerWidth) {
            $("#kanban-container").css({width: columnWidth + "px"});
        } else {
            $("#kanban-container").css({width: "100%"});
        }


        //set wrapper scroll
        if ($("#kanban-wrapper")[0].offsetWidth < $("#kanban-wrapper")[0].scrollWidth) {
            $("#kanban-wrapper").css("overflow-x", "scroll");
        } else {
            $("#kanban-wrapper").css("overflow-x", "hidden");
        }


        //set column scroll

        var columnHeight = $(window).height() - $(".kanban-item-list").offset().top - 30;
        if (isMobile()) {
            columnHeight = $(window).height() - 30;
        }

        $(".kanban-item-list").height(columnHeight);

        $(".kanban-item-list").each(function (index) {

            //set scrollbar on column... if requred
            if ($(this)[0].offsetHeight < $(this)[0].scrollHeight) {
                $(this).css("overflow-y", "scroll");
            } else {
                $(this).css("overflow-y", "hidden");
            }

        });
    };


    saveStatusAndSort = function ($item, status) {
        appLoader.show();
        adjustViewHeightWidth();

        var $prev = $item.prev(),
                $next = $item.next(),
                prevSort = 0, nextSort = 0, newSort = 0,
                step = 100000, stepDiff = 500,
                id = $item.attr("data-id");

        if ($prev && $prev.attr("data-sort")) {
            prevSort = $prev.attr("data-sort") * 1;
        }

        if ($next && $next.attr("data-sort")) {
            nextSort = $next.attr("data-sort") * 1;
        }


        if (!prevSort && nextSort) {
            //item moved at the top
            newSort = nextSort - stepDiff;

        } else if (!nextSort && prevSort) {
            //item moved at the bottom
            newSort = prevSort + step;

        } else if (prevSort && nextSort) {
            //item moved inside two items
            newSort = (prevSort + nextSort) / 2;

        } else if (!prevSort && !nextSort) {
            //It's the first item of this column
            newSort = step * 100; //set a big value for 1st item
        }

        $item.attr("data-sort", newSort);


        appAjaxRequest({
            url: '<?php echo_uri("leads/save_lead_sort_and_status") ?>',
            type: "POST",
            data: {id: id, sort: newSort, lead_status_id: status},
            success: function () {
                appLoader.hide();

                if (isMobile()) {
                    adjustViewHeightWidth();
                }
            }
        });

    };



    $(document).ready(function () {
        kanbanContainerWidth = $("#kanban-container").width();

        if (isMobile() && window.scrollToKanbanContent) {
            window.scrollTo(0, 220); //scroll to the content for mobile devices
            window.scrollToKanbanContent = false;
        }

        var isChrome = !!window.chrome && !!window.chrome.webstore;


        $(".kanban-item-list").each(function (index) {
            var id = this.id;

            var options = {
                animation: 150,
                group: "kanban-item-list",
                onAdd: function (e) {
                    //moved to another column. update bothe sort and status
                    saveStatusAndSort($(e.item), $(e.item).closest(".kanban-item-list").attr("data-lead_status_id"));
                },
                onUpdate: function (e) {
                    //updated sort
                    saveStatusAndSort($(e.item));
                }
            };

            if (isMobile()) {
                    options.handle = '.avatar';
                }


            //apply only on chrome because this feature is not working perfectly in other browsers.
            if (isChrome) {
                options.setData = function (dataTransfer, dragEl) {
                    var img = document.createElement("img");
                    img.src = $("#move-icon").attr("src");
                    img.style.opacity = 1;
                    dataTransfer.setDragImage(img, 5, 10);
                };

                options.ghostClass = "kanban-sortable-ghost";
                options.chosenClass = "kanban-sortable-chosen";
            }

            Sortable.create($("#" + id)[0], options);
        });


        adjustViewHeightWidth();

        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    $(window).resize(function () {
        adjustViewHeightWidth();
    });

</script>
