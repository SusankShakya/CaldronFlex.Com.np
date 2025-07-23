<?php
if ($page_view) {
    $view_from = "client_view";
} else {
    $view_from = "client_details_view";
}
?>
<?php if ($page_view) { ?>
    <div id="page-content" class="page-wrapper clearfix">
    <?php } ?>
    <div class="clearfix">
        <ul id="client-files-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title rounded-top-0" role="tablist">
            <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#file-list-tab"><?php echo app_lang("files_list"); ?></a></li>
            <?php if (get_setting("module_file_manager") == "1") { ?>
                <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/explore/" . $folder_id . "/1/" . $view_from . "/" . $client_id); ?>" data-bs-target="#file-grid-tab"><?php echo app_lang('folders'); ?></a></li>
            <?php } ?>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <?php
                    if ($login_user->user_type == "staff" || ($login_user->user_type == "client" && get_setting("client_can_add_files"))) {
                        echo modal_anchor(get_uri("clients/file_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_files'), array("class" => "btn btn-default", "id" => "general-add-files-button", "title" => app_lang('add_files'), "data-post-client_id" => $client_id));
                    }
                    ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="file-list-tab">
                <div class="card border-top-0 rounded-top-0">
                    <div class="table-responsive">
                        <table id="client-file-table" class="display" width="100%">
                        </table>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade default-bg" id="file-grid-tab"></div>
        </div>
    </div>
    <?php if ($page_view) { ?>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#client-file-table").appTable({
            source: '<?php echo_uri("clients/files_list_data/" . $client_id) ?>',
            order: [
                [0, "desc"]
            ],
            columns: [{
                    title: '<?php echo app_lang("id") ?>'
                },
                {
                    title: '<?php echo app_lang("file") ?>',
                    "class": "all file-name-section"
                },
                {
                    title: '<?php echo app_lang("size") ?>'
                },
                {
                    title: '<?php echo app_lang("uploaded_by") ?>'
                },
                {
                    title: '<?php echo app_lang("created_date") ?>'
                },
                {
                    title: '<i data-feather="menu" class="icon-16"></i>',
                    "class": "text-center option w100"
                }
            ],
            printColumns: [0, 1, 2, 3, 4],
            xlsColumns: [0, 1, 2, 3, 4]
        });

        setTimeout(function() {
            var tab = "<?php echo $tab; ?>";
            if (tab === "file_manager" || "<?php echo $folder_id; ?>" != 0) {
                $("[data-bs-target='#file-grid-tab']").trigger("click");
            }
        }, 150);

        $("[data-bs-target='#file-grid-tab']").click(function() {
            // Check if this is not page view and $tab is not containing "file_manager"
            if (!window.location.href.includes('file_manager') && !"<?php echo $page_view; ?>") {
                var browserState = {
                    Url: window.location.href + '/file_manager/#'
                };
                history.pushState(browserState, "", browserState.Url);
            }
        });

        //hide the general add button on changing tab
        var addGeneralFileButton = $("#general-add-files-button");
        $(".nav-tabs li").click(function() {
            var activeField = $(this).find("a").attr("data-bs-target");

            if (activeField === "#file-grid-tab") {
                addGeneralFileButton.addClass("hide");
            } else {
                addGeneralFileButton.removeClass("hide");
            }
        });
    });
</script>