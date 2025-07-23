<div class="box h-100">
    <div class="box-content">
        <div id="proposal-preview-content" class="<?php echo isset($is_editor_preview) ? "bg-all-white" : "page-wrapper"; ?> clearfix">
            <?php
            load_css(array(
                "assets/css/invoice.css",
            ));

            load_js(array(
                "assets/js/signature/signature_pad.min.js",
            ));
            ?>

            <div class="proposal-preview">
                <?php
                if (!isset($is_editor_preview)) {

                    $action_buttons = "<div class='clearfix float-end grid-button-group'>";

                    if ($show_close_preview) {
                        echo "<div class='text-center'>" . anchor("proposals/view/" . $proposal_info->id, app_lang("close_preview"), array("class" => "btn btn-default round mb20 mr5")) . "</div>";
                    }

                    $action_buttons .= "<div class='float-start'>" . js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print'), array('id' => 'print-proposal-btn', "class" => "btn btn-default round mr10")) . "</div>";

                    if ($has_pdf_access) {
                        $action_buttons .= "<div class='float-start'>" . anchor(get_uri("proposals/download_pdf/" . $proposal_info->id), "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "class" => "btn btn-default round mr10")) . "</div>";
                    }


                    $action_buttons .= "</div>";

                    if ($proposal_info->status === "accepted" || $proposal_info->status === "declined" || $proposal_info->status === "rejected") {
                ?>
                        <div class="card p15 no-border grid-button">
                            <div class="clearfix proposal-preview-button">
                                <div class="float-start mt5 grid-button-group">
                                    <?php if ($proposal_info->status === "accepted") { ?>
                                        <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("proposal_accepted"); ?>
                                    <?php } else { ?>
                                        <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("proposal_rejected"); ?>
                                    <?php } ?>
                                </div>

                                <?php echo $action_buttons; ?>
                            </div>
                        </div>
                        <?php
                    } else {
                        if ($login_user->user_type === "staff" || ($login_user->user_type === "client" && $proposal_info->status == "new")) {
                        ?>

                            <div class="card p15 no-border grid-button">

                                <div class="clearfix proposal-preview-button">
                                    <div class="mr15 strong float-start grid-button-group">
                                        <?php
                                        if ($login_user->user_type === "client" && get_setting("add_signature_option_on_accepting_proposal")) {
                                            echo modal_anchor(get_uri("offer/accept_proposal_modal_form/$proposal_info->id"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('accept_proposal'), array("class" => "btn btn-success mr15", "title" => app_lang('accept_proposal')));
                                        } else {
                                            echo ajax_anchor(get_uri("proposals/update_proposal_status/$proposal_info->id/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("class" => "btn btn-success mr15", "title" => app_lang('mark_as_accepted'), "data-reload-on-success" => "1"));
                                        }
                                        ?>

                                        <?php echo ajax_anchor(get_uri("proposals/update_proposal_status/$proposal_info->id/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("class" => "btn btn-danger mr15", "title" => app_lang('mark_as_rejected'), "data-reload-on-success" => "1")); ?>
                                    </div>

                                    <?php echo $action_buttons; ?>
                                </div>
                            </div>
                <?php
                        }
                    }
                }
                ?>

                <div id="proposal-preview" class="invoice-preview-container proposal-preview-container bg-white mt15 mb20">
                    <?php
                    echo $proposal_preview;
                    ?>
                </div>

                <?php
                if (!isset($is_editor_preview)) {
                    echo view("proposals/signer_info", array("proposal_status" => $proposal_info->status));
                }
                ?>
            </div>

        </div>

    </div>

    <?php if (get_setting("enable_comments_on_proposals") && $proposal_info->status != "draft" && !isset($is_editor_preview)) { ?>
        <div class="hidden-xs box-content bg-white no-card-style" style="width: 400px; min-height: 100%;">
            <div id="proposal-comment-container">
                <?php echo view("proposals/comment_form"); ?>
            </div>
        </div>
    <?php } ?>

    <?php echo view("proposals/print_proposal_helper_js"); ?>
</div>