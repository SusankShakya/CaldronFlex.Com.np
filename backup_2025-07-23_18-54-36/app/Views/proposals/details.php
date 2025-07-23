<div class="clearfix default-bg details-view-container">
    <div class="card" id="proposal-items-card">
        <div class="card-header fw-bold">
            <span class="d-inline-block mt-1"><i data-feather="list" class="icon-16"></i> &nbsp;<?php echo app_lang("proposal_items"); ?></span>
            <div class="float-end">
                <div class="action-option light js-cookie-button" data-bs-toggle="collapse" data-bs-target="#proposal-items-content" aria-expanded="true" aria-controls="proposal-items-content">
                    <i data-feather="chevron-right" class="icon-16"></i>
                </div>
            </div>
        </div>
        <div class="collapse show" id="proposal-items-content">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="proposal-item-table" class="display no-header-top-border" width="100%">
                    </table>
                </div>
                <div class="clearfix">
                    <div class="col-sm-8"></div>
                    <?php if ($is_proposal_editable) { ?>
                        <div class="float-start ml15 mt20 mb20">
                            <?php echo modal_anchor(get_uri("proposals/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-primary text-white", "title" => app_lang('add_item'), "data-post-proposal_id" => $proposal_info->id)); ?>
                        </div>
                    <?php } ?>
                    <div class="float-end pr15" id="proposal-total-section">
                        <?php echo view("proposals/proposal_total_section", array("is_proposal_editable" => $is_proposal_editable)); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" id="proposal-editor-card">
        <div class="card-body">
            <?php echo view("proposals/proposal_editor"); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var userID = "<?php echo $login_user->id; ?>",
            widgetCookieName = "proposal_view_proposal_items_list_" + userID,
            $widgetContent = $("#proposal-items-content"),
            $toggleButton = $(".js-cookie-button"),
            $widgetContainer = $("#proposal-items-card"),
            $cardHeader = $widgetContainer.find(".card-header");

        var widgetVisibility = getCookie(widgetCookieName);

        // If no cookie is set (first visit), or it's "visible", show the widget
        if (!widgetVisibility || widgetVisibility === "visible") {
            $widgetContent.addClass("show");
            $toggleButton.removeClass("collapsed");
            $cardHeader.removeClass("rounded");
        } else {
            $widgetContent.removeClass("show");
            $toggleButton.addClass("collapsed");
            $cardHeader.addClass("rounded");
        }

        $widgetContent.on("shown.bs.collapse", function() {
            setCookie(widgetCookieName, "visible");
            $cardHeader.removeClass("rounded");
        });

        $widgetContent.on("hidden.bs.collapse", function() {
            setCookie(widgetCookieName, "hidden");
            $cardHeader.addClass("rounded");
        });
    });
</script>