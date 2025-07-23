<div class="page-content estimate-details-view clearfix">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div id="estimate-details-top-bar">
                    <?php echo view("estimates/estimate_top_bar"); ?>
                </div>

                <div class="details-view-wrapper d-flex">
                    <div class="w-100">
                        <?php echo view("estimates/details"); ?>
                    </div>
                    <div class="flex-shrink-0 details-view-right-section">
                        <div id="estimate-details-estimate-info">
                            <?php echo view("estimates/estimate_info"); ?>
                        </div>

                        <?php echo view("estimates/estimate_actions"); ?>

                        <?php echo view("estimates/signer_info") ?>

                        <div id="estimate-tasks-section">
                            <?php echo view("estimates/tasks/index"); ?>
                        </div>

                        <?php if (can_access_reminders_module()) { ?>
                            <div class="card reminders-card" id="estimate-reminders">
                                <div class="card-header fw-bold">
                                    <i data-feather="clock" class="icon-16"></i> &nbsp;<?php echo app_lang("reminders") . " (" . app_lang('private') . ")"; ?>
                                </div>
                                <div class="card-body">
                                    <?php echo view("reminders/reminders_view_data", array("estimate_id" => $estimate_info->id, "hide_form" => true, "reminder_view_type" => "estimate")); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php
                        if (get_setting("enable_comments_on_estimates") && !($estimate_info->status === "draft")) {
                            echo view("estimates/comment_form");
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    //RELOAD_VIEW_AFTER_UPDATE = true;
    $(document).ready(function() {
        var optionVisibility = false;
        <?php if ($is_estimate_editable) { ?>
            optionVisibility = true;
        <?php } ?>

        $("#estimate-item-table").appTable({
            source: '<?php echo_uri("estimates/item_list_data/" . $estimate_info->id . "/") ?>',
            order: [
                [0, "asc"]
            ],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("item") ?> ", sortable: false, "class": "all"},
                {title: "<?php echo app_lang("quantity") ?>", "class": "text-right w15p", sortable: false},
                {title: "<?php echo app_lang("rate") ?>", "class": "text-right w15p", sortable: false},
                {title: "<?php echo app_lang("total") ?>", "class": "text-right w15p all", sortable: false},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100", sortable: false, visible: optionVisibility}
            ],

            onInitComplete: function() {
                <?php if ($is_estimate_editable) { ?>
                    //apply sortable
                    $("#estimate-item-table").find("tbody").attr("id", "estimate-item-table-sortable");
                    var $selector = $("#estimate-item-table-sortable");

                    Sortable.create($selector[0], {
                        animation: 150,
                        handle: '.move-icon',
                        chosenClass: "sortable-chosen",
                        ghostClass: "sortable-ghost",
                        onUpdate: function(e) {
                            appLoader.show();
                            //prepare sort indexes 
                            var data = "";
                            $.each($selector.find(".item-row"), function(index, ele) {
                                if (data) {
                                    data += ",";
                                }

                                data += $(ele).attr("data-id") + "-" + index;
                            });

                            //update sort indexes
                            appAjaxRequest({
                                url: '<?php echo_uri("estimates/update_item_sort_values") ?>',
                                type: "POST",
                                data: {
                                    sort_values: data
                                },
                                success: function() {
                                    appLoader.hide();
                                }
                            });
                        }
                    });
                <?php } ?>
            }
        });

        //print estimate
        $("#print-estimate-btn").click(function() {
            appLoader.show();

            appAjaxRequest({
                url: "<?php echo get_uri('estimates/print_estimate/' . $estimate_info->id) ?>",
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        document.body.innerHTML = result.print_view; //add estimate's print view to the page
                        $("html").css({
                            "overflow": "visible"
                        });

                        setTimeout(function() {
                            window.print();
                        }, 200);
                    } else {
                        appAlert.error(result.message);
                    }

                    appLoader.hide();
                }
            });
        });

        //reload page after finishing print action
        window.onafterprint = function() {
            location.reload();
        };

        appContentBuilder.init("<?php echo get_uri('estimates/view/' . $estimate_info->id); ?>", {
            id: "estimate-details-page-builder",
            data: {
                view_type: "estimate_meta"
            },
            reloadHooks: [{
                    type: "app_form",
                    id: "discount-form"
                },
                {
                    type: "app_form",
                    id: "estimate-item-form"
                },
                {
                    type: "app_form",
                    id: "send-estimate-form"
                },
                {
                    type: "app_table_row_delete",
                    tableId: "estimate-item-table"
                },
                {
                    type: "app_form",
                    id: "project-form"
                }
            ],
            reload: function(bind, result) {
                bind("#estimate-total-section", result.estimate_total_section);
                bind("#estimate-details-top-bar", result.top_bar);
                bind("#estimate-details-estimate-info", result.estimate_info);
            }
        });
    });
</script>