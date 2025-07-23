<div class="clearfix default-bg details-view-container">
    <div class="card p15 w-100">
        <div id="page-content" class="clearfix">
            <div style="max-width: 1000px; margin: auto;">
                <div>
                    <div class="clearfix p20">
                        <!-- small font size is required to generate the pdf, overwrite that for screen -->
                        <style type="text/css">
                            .invoice-meta {
                                font-size: 100% !important;
                            }
                        </style>

                        <?php
                        $color = get_setting("estimate_color");
                        if (!$color) {
                            $color = get_setting("invoice_color");
                        }
                        $style = get_setting("invoice_style");
                        ?>
                        <?php
                        $data = array(
                            "client_info" => $client_info,
                            "color" => $color ? $color : "#2AA384",
                            "estimate_info" => $estimate_info
                        );

                        if ($style === "style_3") {
                            echo view('estimates/estimate_parts/header_style_3.php', $data);
                        } else if ($style === "style_2") {
                            echo view('estimates/estimate_parts/header_style_2.php', $data);
                        } else {
                            echo view('estimates/estimate_parts/header_style_1.php', $data);
                        }
                        ?>

                    </div>

                    <div class="table-responsive mt15 pl15 pr15">
                        <table id="estimate-item-table" class="display" width="100%">
                        </table>
                    </div>

                    <div class="clearfix">
                        <?php if ($is_estimate_editable) { ?>
                            <div class="float-start mt20 ml15">
                                <?php echo modal_anchor(get_uri("estimates/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-primary text-white add-item-btn", "title" => app_lang('add_item'), "data-post-estimate_id" => $estimate_info->id)); ?>
                            </div>
                        <?php } ?>
                        <div class="float-end pr15" id="estimate-total-section">
                            <?php echo view("estimates/estimate_total_section", array("is_estimate_editable" => $is_estimate_editable)); ?>
                        </div>
                    </div>

                    <p class="b-t b-info pt10 m15 pb10"><?php echo custom_nl2br($estimate_info->note ? process_images_from_content($estimate_info->note) : ""); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>