<?php
$page_wrapper = "page-wrapper";
if ($view_type) {
    $page_wrapper = "ptb20";
}
?>

<div class="<?php echo $page_wrapper; ?> clearfix">
    <div class="box">
        <?php if ($show_left_menu) { ?>
            <div class="box-content content-sidebar pr15" id="file-manager-sidebar">
                <ul class="list-group mb10">
                    <div class="input-group search-box">
                        <?php
                        echo form_input(array(
                            "id" => "file-manager-search-box",
                            "name" => "search",
                            "value" => "",
                            "autocomplete" => "false",
                            "class" => "form-control help-search-box",
                            "placeholder" => app_lang('search_folder_or_file')
                        ));
                        ?>
                        <span class="spinning-btn"></span>
                    </div>
                </ul>
                <ul class="list-group ">
                    <?php
                    if ($client_id || $project_id) {
                    ?>
                        <a href="javascript:;" class="list-group-item explore-favourite-folder" data-folder_id=""><i data-feather="home" class="icon-16 mr10"></i><?php echo app_lang('root_folder') ?></a>
                    <?php
                    } else {
                        echo anchor(get_uri($controller_slag . "/explore"), "<i data-feather='home' class='icon-16 mr10'></i>" . app_lang('root_folder'), array("class" => "list-group-item", "id" => "root-folder-link", "data-folder_id" => ""));
                    }
                    ?>
                </ul>
                <label class="p15 text-off"><?php echo app_lang("favorites"); ?></label>

                <ul class="list-group" id="favourite-folders">
                    <?php echo view("app_folders/favourite_folders"); ?>
                </ul>
            </div>
        <?php } ?>

        <div class="box-content" id="file-manager-items-box">
            <div class="card grid-button" id="file-manager-container-card">
                <div class="page-title" id="file-manger-title-bar">
                    <?php echo view('app_folders/title_bar'); ?>


                </div>
                <div class="card-body box file-manager-container" id="file-manager-container">

                    <?php echo view('app_folders/window'); ?>

                </div>
            </div>
        </div>

        <div class="box-content w300" id="file-details-box">
            <div class="sticky-details-section">
                <div class="card">
                    <div class="page-title">
                        <h1> <?php echo app_lang('details'); ?></h1>
                        <div class="title-button-group">
                            <button id="close-details-button" type="button" class="btn-close p10 mt15"></button>
                        </div>
                    </div>
                    <div class="card-body box" id="file-manager-right-panel">
                        <?php echo view('app_folders/folder_info'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="folder-context-menu" class="dropdown-menu">

</div>


<div id="modal-button-container"></div>
<?php echo js_anchor(" ", array('id' => "app-modal-button", 'title' => "", "data-toggle" => "app-modal")); ?>

<script type="text/javascript">
    function setFolderWindowHeight() {
        var minHeight = $(window).height() - 290;

        $("#file-manager-window-area").css("min-height", minHeight);
        $("#file-manager-container").css("min-height", minHeight + 115);
        $("#file-manager-right-panel").css("height", minHeight + 115);

    }

    function openFolderWindow(folder_id = "") {
        $("#file-manager-container").html("");


        var fileManagerRightPanel = '<div class="no-file-selected"><div class="files-icon"><i data-feather="file-text" class="no-file-selected-icon" style=""></i><div class="no-file-selected-text font-12 text-off"><?php echo app_lang("select_a_file_to_view_details"); ?></div></div></div>';
        $("#file-manager-right-panel").html(fileManagerRightPanel);
        //$("#new_folder_button").attr("data-post-parent_id", folder_id);

        var viewFrom = "<?php echo $view_from; ?>";

        appLoader.show({
            container: "#file-manager-container",
            zIndex: 1,
            css: "top:50%; right:48%;"
        });

        if (history) {
            var browserState = {};

            // Set the URL based on the value of $view_from variable for access file manager from different pages
            if (viewFrom == "client_view") {
                browserState = {
                    Url: window.location.href.replace(/(\/page_view)?\/([^\/]+)\/?$/, '/page_view/' + folder_id)
                };

            } else if (viewFrom == "client_details_view" || viewFrom == "project_view") {
                browserState = {
                    Url: window.location.href.replace(/\/?([^\/]*)$/, '/' + folder_id)
                };
            } else {
                browserState = {
                    Url: "<?php echo get_uri($controller_slag . "/explore/"); ?>" + folder_id
                };
            }

            // Push the browser state to the history and update the URL
            history.pushState(browserState, "", browserState.Url);
        }

        var clientId = "<?php echo $client_id ? $client_id : 0; ?>";
        var projectId = "<?php echo $project_id ? $project_id : 0; ?>";
        var folderId = folder_id ? folder_id : 0;

        appAjaxRequest({
            url: "<?php echo get_uri($controller_slag . '/get_folder_items/'); ?>" + folderId + "/" + clientId + "/" + projectId + "/" + viewFrom,
            dataType: "json",
            success: function(result) {
                if (result.success) {
                    $("#file-manager-container").html(result.window_content);
                    $("#file-manger-title-bar").html(result.title_bar_content);
                    setFolderWindowHeight();

                }
                appLoader.hide();
            }
        });
    }

    function showFilePreviewAppModal($element) {

        var $newElement = $element.clone(),
            buttonId = "tempAppModalButton";

        $newElement.attr({
            "data-toggle": "app-modal",
            "data-target_group": "window_files",
            "id": buttonId
        }).removeAttr("data-group"); //remove the attribute so that it'll not be counted

        $("body").append($newElement);
        $("#" + buttonId).trigger("click");
        setTimeout(function() {
            $("#" + buttonId).remove();
        });

    }

    function updateFavoritesSection() {
        appLoader.show({
            container: "#favourite-folders",
            zIndex: 1,
            css: "top:50%; right:48%;"
        });

        var viewFrom = "<?php echo $view_from; ?>";
        if (viewFrom == "client_view" || viewFrom == "client_details_view") {
            context = "client";
            contextId = "<?php echo $client_id ? $client_id : 0; ?>";
        } else if (viewFrom == "project_view") {
            context = "project";
            contextId = "<?php echo $project_id ? $project_id : 0; ?>";
        } else {
            context = "file_manager";
            contextId = 0;
        }

        appAjaxRequest({
            url: "<?php echo get_uri($controller_slag . '/get_favourite_folders/'); ?>" + context + "/" + contextId,
            dataType: "json",
            success: function(result) {
                if (result.success) {
                    $("#favourite-folders").html(result.content);
                }
                appLoader.hide();
            }
        });
    }

    function showContextMenu(it, e, type) {
        var left = e.clientX,
            top = e.clientY;

        if ($(window).width() > 576 && $(".sidebar").width() > 70) {
            left = left - 250;
            top = top - 70;
        }

        if ("<?php echo $view_type; ?>") {
            left = left;
            top = top;
        }

        var viewFrom = "<?php echo $view_from; ?>";
        if (viewFrom == "client_details_view" || viewFrom == "project_view") {
            top = top;
            left = left;
        }

        var scrollTop = $(".main-scrollable-page").scrollTop();
        var clientId = "<?php echo $client_id ? $client_id : 0; ?>";
        var context = "file_manager";
        var contextId = 0;
        if (viewFrom == "client_view" || viewFrom == "client_details_view") {
            context = "client";
            contextId = "<?php echo $client_id ? $client_id : 0; ?>";
        } else if (viewFrom == "project_view") {
            context = "project";
            contextId = "<?php echo $project_id ? $project_id : 0; ?>";
        }

        $('#folder-context-menu').removeClass('hide');
        $('.folder-item-content').removeClass('focus');
        $('.selected-folder-item').removeClass('selected-folder-item');
        $(e.target).closest('.folder-item-content').addClass('focus');

        if (type === "folder-context-menu" || type === "file-context-menu") {
            window.clickedOnFolderItem = true;

            var parentFolderItem = it.closest('.folder-item');
            var dataId = parentFolderItem.data("id");
            var folderName = parentFolderItem.find('.folder-name').text();
            var folderInfo = parentFolderItem.find('.folder-info').text();
            var folderId = parentFolderItem.data('folder_id');
            var fileName = parentFolderItem.find('.file-name').text();
            var fileSize = parentFolderItem.find('.file-size').text();
            var isFavourite = parentFolderItem.data('is_favourite');
            var hasWritePermission = parentFolderItem.closest('.files-and-folders-list').data('has_write_permission');
            var hasThisFolderWritePermission = parentFolderItem.data('has_this_folder_write_permission');
            var hasOnlyUploadPermission = parentFolderItem.data('has_only_upload_permission');
            var parentId = $("#new_folder_button").data('post-parent_id');

            if (type === "folder-context-menu") {
                var exploreMenu = '<div class="dropdown-item clickable explore-folder-menu"><i data-feather="zap" class="icon-16 mr10"></i><?php echo app_lang('explore'); ?></div>';

                if (isFavourite) {
                    var removeFavouriteUrl = '<?php echo get_uri($controller_slag . '/add_remove_favorites/remove/'); ?>' + dataId;
                    var removeFavourite = '<?php echo ajax_anchor('', '<i data-feather="star" class="icon-16 mr10"></i>' . app_lang('remove_from_favorites'), array('class' => 'dropdown-item', 'data-reload-on-success' => true)); ?>';
                } else {
                    var addFavouriteUrl = '<?php echo get_uri($controller_slag . '/add_remove_favorites/add/'); ?>' + dataId;
                    var addFavourite = '<?php echo ajax_anchor('', '<i data-feather="star" class="icon-16 mr10"></i>' . app_lang('add_to_favorites'), array('class' => 'dropdown-item', 'data-reload-on-success' => true)); ?>';
                }

                var renameMenu = '',
                    moveMenu = '',
                    deleteMenu = '';

                if (hasThisFolderWritePermission) {
                    var renameMenu = '<?php echo modal_anchor(get_uri($controller_slag . '/folder_modal_form'), '<i data-feather="edit-2" class="icon-16 mr10"></i>' . app_lang('rename'), array('title' => app_lang('rename_folder'), 'class' => 'dropdown-item', 'data-post-id' => '')); ?>';

                    var moveMenu = '<?php echo modal_anchor(get_uri($controller_slag . '/move_folder_or_file_modal_form'), '<i data-feather="corner-down-right" class="icon-16 mr10"></i>' . app_lang('move'), array('title' => app_lang('move_folder'), 'class' => 'dropdown-item', 'data-post-folder_id' => '')); ?>';

                    var deleteMenu = '<?php echo js_anchor('<i data-feather="trash" class="icon-16 mr10"></i>' . app_lang('delete'), array('title' => app_lang('delete'), 'class' => 'dropdown-item', 'data-id' => '', 'data-action-url' => get_uri($controller_slag . '/delete_folder'), 'data-action' => 'delete-confirmation', 'data-reload-on-success' => true)); ?>';

                    var infoMenu = '<div class="dropdown-item clickable item-info-button" data-type="folder" data-id=""><i data-feather="info" class="icon-16 mr10"></i><?php echo app_lang('info'); ?></div>';
                }

                $('#folder-context-menu').html('').append(exploreMenu).append($(addFavourite).attr('data-action-url', addFavouriteUrl)).append($(removeFavourite).attr('data-action-url', removeFavouriteUrl)).append($(renameMenu).attr('data-post-id', dataId).attr('data-title', '<?php echo app_lang('rename_folder'); ?>' + ': ' + folderName)).append($(moveMenu).attr('data-post-folder_id', dataId).attr('data-title', '<?php echo app_lang('move_folder'); ?>' + ': ' + folderName).attr('data-post-context', context).attr('data-post-context_id', contextId)).append($(deleteMenu).attr('data-id', dataId)).append($(infoMenu).attr('data-id', dataId));

                //Change delete confirmation modal content for folder delation
                var folderDetails = "<div class='mt15'><div class='d-flex'><div class='flex-shrink-0 me-3 icon-wrapper'><i data-feather='folder' class='icon-40 bold-folder-icon'></i></div><div class='w-100'><div>" + folderName + "</div><small class='text-off'>" + folderInfo + "</small></div></div></div>";
                $("#confirmationModalContent .container-fluid").html('<?php echo app_lang("folder_delete_confirmation_message"); ?>' + folderDetails);
            } else {
                var $button = parentFolderItem.find("a");
                var buttonData = $button.data();

                var viewFileMenu = '<div class="dropdown-item clickable file-preview-menu"><i data-feather="zap" class="icon-16 mr10"></i><?php echo app_lang('view'); ?></div>';

                var moveMenu = '',
                    deleteMenu = '';

                if (hasWritePermission && "<?php echo $login_user->user_type ?>" == "staff") {
                    moveMenu = '<?php echo modal_anchor(get_uri($controller_slag . '/move_folder_or_file_modal_form'), '<i data-feather="corner-down-right" class="icon-16 mr10"></i>' . app_lang('move'), array('title' => app_lang('move_file'), 'class' => 'dropdown-item', 'data-post-file_id' => '')); ?>';
                    deleteMenu = '<?php echo js_anchor('<i data-feather="trash" class="icon-16 mr10"></i>' . app_lang('delete'), array('title' => app_lang('delete'), 'class' => 'dropdown-item', 'data-id' => '', 'data-action-url' => get_uri($controller_slag . '/delete_folder_file'), 'data-action' => 'delete-confirmation', 'data-reload-on-success' => true)); ?>';
                }

                var downloadUrl = '<?php echo get_uri($controller_slag . '/download_folder_file/'); ?>' + dataId;
                var downloadMenu = '<?php echo anchor("#", '<i data-feather="download-cloud" class="icon-16 mr10"></i><span>' . app_lang('download') . '</span>', array("title" => app_lang("download"), 'class' => 'dropdown-item', 'id' => 'downloadMenu')); ?>';

                // Update the 'href' attribute with the dynamically generated URL
                downloadMenu = $(downloadMenu).attr('href', downloadUrl);

                var infoMenu = '<div class="dropdown-item clickable item-info-button" data-type="file" data-id=""><i data-feather="info" class="icon-16 mr10"></i><?php echo app_lang('info'); ?></div>';

                $('#folder-context-menu').html('').append(viewFileMenu).append($(moveMenu).attr('data-post-file_id', dataId).attr('data-post-context', context).attr('data-post-context_id', contextId).attr('data-post-parent_folder_id', parentId)).append($(deleteMenu).attr('data-id', dataId)).append(downloadMenu).append($(infoMenu).attr('data-id', dataId));

                //Change delete confirmation modal content for file delation
                var fileDetails = "<div class='mt15'><div class='d-flex'><div class='flex-shrink-0 me-3 icon-wrapper'><i data-feather='file' class='icon-40 bold-file-icon'></i></div><div class='w-100'><div class='text-break'>" + fileName + "</div><small class='text-off'>" + fileSize + "</small></div></div></div>";
                $("#confirmationModalContent .container-fluid").html('<?php echo app_lang("file_delete_confirmation_message"); ?>' + fileDetails);
            }

            setTimeout(function() {
                window.clickedOnFolderItem = false;
            }, 300);

            feather.replace();

            $('.explore-folder-menu').click(function() {
                openFolderWindow(folderId);
            });

            $('.file-preview-menu').click(function() {
                if (buttonData && buttonData.preview_function && typeof window[buttonData.preview_function] === "function") {
                    window[buttonData.preview_function]($button);
                }
            });
        }

        if (type === "window-context-menu") {
            setTimeout(function() {
                if (!window.clickedOnFolderItem) {
                    $('#folder-context-menu').html('');

                    var parentId = $("#new_folder_button").data('post-parent_id');

                    var addNewFolderMenu = '';
                    var hasWritePermission = $('.files-and-folders-list').data('has_write_permission');
                    if (hasWritePermission) {
                        var addNewFolderMenu = '<?php echo modal_anchor(get_uri($controller_slag . '/folder_modal_form'), '<i data-feather="folder-plus" class="icon-16 mr5"></i>' . app_lang('new_folder'), array('title' => app_lang('new_folder'), 'class' => 'dropdown-item', 'data-post-parent_id' => '', 'data-post-context' => '', 'data-post-context_id' => '')); ?>';
                    }

                    var uploadFilesMenu = '';
                    var hasUploadPermission = $('.files-and-folders-list').data('has_upload_permission');
                    if (hasUploadPermission) {
                        var uploadFilesMenu = '<?php echo $add_files_button; ?>';
                    }

                    if (hasWritePermission || hasUploadPermission) {
                        $('#folder-context-menu').html('').append($(addNewFolderMenu).attr('data-post-parent_id', parentId).attr('data-post-context', 'folder').attr('data-post-context_id', contextId).attr('data-post-context', context)).append($(uploadFilesMenu).attr('class', 'dropdown-item').attr('data-post-folder_id', parentId).attr('data-post-client_id', clientId));
                    } else {
                        $('#folder-context-menu').addClass("hide");
                    }

                    feather.replace();
                }
            });
        }


        // Position the context menu at the mouse pointer's location
        $('#folder-context-menu').css({
            display: 'block',
            position: "aboslute",
            left: left,
            top: top + scrollTop
        });

        if ($(window).width() < 576) {
            $('#folder-context-menu').css({
                display: 'block',
                position: "fixed",
                left: left - 168,
                top: top + scrollTop
            });
        }
    }

    function getItemDetails(type, id) {
        setTimeout(function() {
            if (!window.isDoubleClick) {
                var url = "<?php echo get_uri($controller_slag . '/'); ?>";
                if (type === "folder") {
                    url = url + "get_folder_info";
                } else if (type === "file") {
                    url = url + "get_folder_file_info";
                }

                if (!url) {
                    return false;
                }

                var clientId = "<?php echo $client_id ? $client_id : 0; ?>";
                var projectId = "<?php echo $project_id ? $project_id : 0; ?>";

                var fileDetailsContainer = "#file-manager-right-panel";
                $(fileDetailsContainer).html("");
                appLoader.show({
                    container: fileDetailsContainer,
                    zIndex: 1,
                    css: "top:10%; right:40%;"
                });

                appAjaxRequest({
                    url: url,
                    data: {
                        id: id,
                        client_id: clientId,
                        project_id: projectId
                    },
                    type: "POST",
                    dataType: "json",
                    success: function(result) {
                        if (result.success) {
                            $(fileDetailsContainer).html(result.content);
                            setFolderWindowHeight();
                        }
                        appLoader.hide();
                    }
                });
            }
        }, 300);
    }

    $(document).ready(function() {
        $('body').on('contextmenu', '.show-context-menu', function(e) {
            e.preventDefault();
            var it = $(this);
            var type = "window-context-menu";

            if (it.hasClass("file-thumb-area")) {
                type = "file-context-menu";
            } else if (it.hasClass("folder-thumb-area")) {
                type = "folder-context-menu";
            }

            showContextMenu(it, e, type);
        });

        $(document).on('click', '.file-manager-more-menu', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var it = $(this);

            var parent = it.closest(".folder-item");
            var type = "window-context-menu";

            if (parent.find(".file-thumb-area").length) {
                type = "file-context-menu";
            } else if (parent.find(".folder-thumb-area").length) {
                type = "folder-context-menu";
            }

            showContextMenu(it, e, type);
        });

        // Hide the context menu when clicking anywhere else in the document
        $(document).on('click', function() {
            $('#folder-context-menu').css('display', 'none');
        });

        setFolderWindowHeight();

        $('body').on('click', "#view-details-button, #close-details-button", function(e) {
            $("#file-details-box").toggleClass("hide");
        });

        window.isDoubleClick = false;

        $('body').on('click', '#file-manager-container-card .folder-item-content', function() {
            window.isDoubleClick = false;
            var $this = $(this).closest("li");

            $(".selected-folder-item").removeClass("selected-folder-item");
            $(".folder-item-content").removeClass('focus');
            $this.find(".folder-item-content").addClass("selected-folder-item");

            if (!window.isDoubleClick) {
                var data = $this.closest("li").data();

                getItemDetails(data.type, data.id);
            }
        });

        //show details section
        $(document).on('click', '.item-info-button', function() {
            window.isDoubleClick = false;
            var $this = $(this);
            var data = $this.data();
            $("#file-details-box").removeClass("hide");

            getItemDetails(data.type, data.id);
            if ($(window).width() < 576) {
                //prepare for small devices
                $("#file-manager-items-box").addClass("hide");
                $("#file-details-box").addClass("d-block");
                $("#file-details-box").addClass("w-100");
            }
        });

        var mouseEvent = 'dblclick';
        if ($(window).width() < 576) {
            mouseEvent = 'click';

            $('body').on('click', "#close-details-button", function(e) {
                $("#file-details-box").addClass("hide");
                $("#file-manager-items-box").removeClass("hide");
            });
        }

        $('body').on(mouseEvent, '.folder-item-content', function() {
            if ($(window).width() > 576) {
                window.isDoubleClick = true;
            }

            var data = $(this).closest("li").data();

            if (data.type === "folder") {
                openFolderWindow(data.folder_id);
            } else if (data.type === "file") {
                var $button = $(this).find("a");
                var buttonData = $button.data();

                if (buttonData && buttonData.preview_function && typeof window[buttonData.preview_function] === "function") {
                    window[buttonData.preview_function]($button);
                }
            }
        });


        $('body').on('click', '.breadcrumb-folder-item', function() {

            var folder_id = $(this).data().folder_id;
            openFolderWindow(folder_id);

        });

        $('body').on('click', '.explore-favourite-folder', function() {
            var favouriteFolderId = $(this).data().folder_id;
            openFolderWindow(favouriteFolderId);
        });

        $(".scrollable-page").on('scroll', function() {
            var StickySectionTop = $('#file-manager-items-box').offset().top;
            if (85 > StickySectionTop && !$('.sticky-details-section').hasClass('stick')) {
                $('.sticky-details-section').addClass('stick w300');
            } else if (85 < StickySectionTop && $('.sticky-details-section').hasClass('stick')) {
                $('.sticky-details-section').removeClass('stick w300');
            }
        });

        //search file or folder
        $("#file-manager-search-box").on("input", function(e) {
            var $fileManagerItems = $('.files-and-folders-list').find(".item-name");
            var searchTerm = $(this).val().toLowerCase();
            $fileManagerItems.each(function() {
                var $folderItem = $(this).closest('.folder-item');
                var itemText = $(this).html().toLowerCase();
                if (itemText.includes(searchTerm)) {
                    $folderItem.removeClass("hide");
                } else {
                    $folderItem.addClass("hide");
                }
            });
        });

        $('body').on('click', "#folder-context-menu .dropdown-item", function(e) {
            $("#folder-context-menu").addClass("hide");
        });

    });
</script>