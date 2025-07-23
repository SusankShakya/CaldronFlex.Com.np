<div class="clearfix default-bg details-view-container">
    <div class="card p15 w-100">
        <div id="page-content" class="clearfix grid-button">
            <div style="max-width: 1000px; margin: auto;">
                <div class="clearfix p20">
                    <!-- small font size is required to generate the pdf, overwrite that for screen -->
                    <style type="text/css">
                        .invoice-meta {
                            font-size: 100% !important;
                        }
                    </style>

                    <?php
                    $color = get_setting("invoice_color");
                    if (!$color) {
                        $color = "#2AA384";
                    }
                    $invoice_style = get_setting("invoice_style");
                    $data = array(
                        "client_info" => $client_info,
                        "color" => $color,
                        "invoice_info" => $invoice_info
                    );

                    if ($invoice_style === "style_3") {
                        echo view('invoices/invoice_parts/header_style_3.php', $data);
                    } else if ($invoice_style === "style_2") {
                        echo view('invoices/invoice_parts/header_style_2.php', $data);
                    } else {
                        echo view('invoices/invoice_parts/header_style_1.php', $data);
                    }
                    ?>
                </div>

                <div class="table-responsive mt15 pl15 pr15">
                    <table id="invoice-item-table" class="display" width="100%">
                    </table>
                </div>

                <div class="clearfix">
                    <?php if ($can_edit_invoices && $is_invoice_editable) { ?>
                        <div class="float-start mt20 ml15">
                            <?php echo modal_anchor(get_uri("invoices/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-primary text-white add-item-btn", "title" => app_lang('add_item'), "data-post-invoice_id" => $invoice_info->id)); ?>
                        </div>
                    <?php } ?>
                    <div class="float-end pr15" id="invoice-total-section">
                        <?php echo $invoice_total_section; ?>
                    </div>
                </div>

                <?php
                $files = @unserialize($invoice_info->files);
                if ($files && is_array($files) && count($files)) {
                ?>
                    <div class="clearfix">
                        <div class="col-md-12 mt20 row">
                            <p class="b-t"></p>
                            <div class="mb10 strong"><?php echo app_lang("files"); ?></div>
                            <?php
                            echo view("includes/file_list", array("files" => $invoice_info->files, "model_info" => $invoice_info, "mode_type" => "view", "context" => "invoices"));
                            ?>
                        </div>
                    </div>
                <?php } ?>

                <p class="b-t b-info pt10 m15"><?php echo custom_nl2br($invoice_info->note ? process_images_from_content($invoice_info->note) : ""); ?></p>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var optionVisibility = false;
        if ("<?php echo $can_edit_invoices ?>") {
            optionVisibility = true;
        }
        var delay;
        var taxableRows = [];

        $("#invoice-item-table").appTable({
            source: '<?php echo_uri("invoices/item_list_data/" . $invoice_info->id . "/") ?>',
            order: [
                [0, "asc"]
            ],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            // prettier-ignore
            columns: [{
                    visible: false,
                    searchable: false
                },
                {
                    title: '<?php echo app_lang("item") ?> ',
                    sortable: false,
                    "class": "all"
                },
                {
                    title: '<?php echo app_lang("quantity") ?>',
                    "class": "text-right w15p",
                    sortable: false
                },
                {
                    title: '<?php echo app_lang("rate") ?>',
                    "class": "text-right w15p",
                    sortable: false
                },
                {
                    title: '<?php echo app_lang("taxable") ?>',
                    "class": "text-right w85",
                    sortable: false
                },
                {
                    title: '<?php echo app_lang("total") ?>',
                    "class": "text-right w15p all",
                    sortable: false
                },
                {
                    title: '<i data-feather="menu" class="icon-16"></i>',
                    "class": "text-center option w85",
                    sortable: false,
                    visible: optionVisibility
                }
            ],
            rowCallback: function(nRow, aData) {
                var column = $("#invoice-item-table").DataTable().column(4);
                var taxableColumn = "<?php echo get_setting('taxable_column'); ?>";
                if (taxableColumn == "always_show") {
                    column.visible(true);
                } else if (taxableColumn == "never_show") {
                    column.visible(false);
                } else {
                    taxableRows.push(aData[4]);
                    clearTimeout(delay);
                    delay = setTimeout(function() {
                        var unique = getUniqueArray(taxableRows);

                        if (unique.length === 2) {
                            column.visible(true);
                        } else {
                            column.visible(false);
                        }
                        taxableRows = [];
                    }, 100);
                }

            },
            onInitComplete: function() {
                <?php if ($can_edit_invoices) { ?>
                    //apply sortable
                    $("#invoice-item-table").find("tbody").attr("id", "invoice-item-table-sortable");
                    var $selector = $("#invoice-item-table-sortable");

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
                                url: '<?php echo_uri("invoices/update_item_sort_values") ?>',
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
    });
</script>