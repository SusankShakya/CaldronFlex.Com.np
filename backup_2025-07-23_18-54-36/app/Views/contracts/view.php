<?php
load_js(array(
    "assets/js/signature/signature_pad.min.js",
));
?>

<div class="page-content xs-full-width clearfix">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="contract-details-view">
                    <div id="contract-details-top-bar">
                        <?php echo view("contracts/contract_top_bar"); ?>
                    </div>

                    <div class="details-view-wrapper d-flex">
                        <div class="w-100">
                            <?php echo view("contracts/details"); ?>
                        </div>

                        <div class="flex-shrink-0 details-view-right-section">
                            <?php echo view("contracts/contract_info"); ?>

                            <?php echo view("contracts/contract_actions"); ?>

                            <?php echo view("contracts/contract_custom_fields_info"); ?>

                            <?php if ($contract_info->note) { ?>
                                <div id="contract-note-section">
                                    <?php echo view("contracts/contract_note"); ?>
                                </div>
                            <?php } ?>

                            <?php echo view("contracts/signer_info"); ?>

                            <div id="contract-tasks-section">
                                <?php echo view("contracts/tasks/index"); ?>
                            </div>

                            <?php if (can_access_reminders_module()) { ?>
                                <div class="card reminders-card" id="contract-reminders">
                                    <div class="card-header fw-bold">
                                        <i data-feather="clock" class="icon-16"></i> &nbsp;<?php echo app_lang("reminders") . " (" . app_lang('private') . ")"; ?>
                                    </div>
                                    <div class="card-body">
                                        <?php echo view("reminders/reminders_view_data", array("contract_id" => $contract_info->id, "hide_form" => true, "reminder_view_type" => "contract")); ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
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
        <?php if ($is_contract_editable) { ?>
            optionVisibility = true;
        <?php } ?>

        $("#contract-item-table").appTable({
            source: '<?php echo_uri("contracts/item_list_data/" . $contract_info->id . "/") ?>',
            order: [
                [0, "asc"]
            ],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            columns: [{
                    visible: false,
                    searchable: false
                },
                {
                    title: "<?php echo app_lang("item") ?> ",
                    sortable: false,
                    "class": "all"
                },
                {
                    title: "<?php echo app_lang("quantity") ?>",
                    "class": "text-right w15p",
                    sortable: false
                },
                {
                    title: "<?php echo app_lang("rate") ?>",
                    "class": "text-right w15p",
                    sortable: false
                },
                {
                    title: "<?php echo app_lang("total") ?>",
                    "class": "text-right w15p all",
                    sortable: false
                },
                {
                    title: "<i data-feather='menu' class='icon-16'></i>",
                    "class": "text-center option w100",
                    sortable: false,
                    visible: optionVisibility
                }
            ],

            onInitComplete: function() {
                <?php if ($is_contract_editable) { ?>
                    //apply sortable
                    $("#contract-item-table").find("tbody").attr("id", "contract-item-table-sortable");
                    var $selector = $("#contract-item-table-sortable");

                    Sortable.create($selector[0], {
                        animation: 150,
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
                                url: '<?php echo_uri("contracts/update_item_sort_values") ?>',
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
            },

            onDeleteSuccess: function(result) {
                $("#contract-total-section").html(result.contract_total_view);
            },
            onUndoSuccess: function(result) {
                $("#contract-total-section").html(result.contract_total_view);
            }
        });

        $("body").on("click", "#contract-save-and-show-btn", function() {
            $(this).trigger("submit");
            $("#contract-preview-btn")[0].click();
        });

        setTimeout(function() {
            $(".hidden-input-field").focus();
            $(".hidden-input-field").remove();
        });

        //make sticky when scroll
        var editorToolbar = $(".preview-editor-button-group, .note-toolbar");

        $(".scrollable-page").scroll(function() {
            var offset = editorToolbar.offset().top;

            if ($(window).scrollTop() > offset) {
                editorToolbar.addClass("sticky-top");
                $(".note-toolbar").css("top", 52);
                $(".note-toolbar").css("box-shadow", "rgba(0, 0, 0, 0.1) 0 3px 6px -3px");
            }
        });

    });

    appContentBuilder.init("<?php echo get_uri('contracts/view/' . $contract_info->id); ?>", {
        id: "contract-details-page-builder",
        data: {
            view_type: "contract_meta"
        },
        reloadHooks: [{
            type: "app_form",
            id: "send-contract-form"
        }],
        reload: function(bind, result) {
            bind("#contract-details-top-bar", result.top_bar);
        }
    });
</script>

<?php echo view("contracts/print_contract_helper_js"); ?>