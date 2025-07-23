<?php
if (isset($folder_info) && $folder_info && $folder_details) {
?>
    <div class="pb15">
        <div class="d-flex">
            <div class="flex-shrink-0 me-3 icon-wrapper">
                <i data-feather='folder' class='icon-40 bold-folder-icon'></i>
            </div>
            <div class="w-100">
                <div class="folder-name"><?php echo $folder_info->title; ?></div>
                <small class="text-off folder-info">
                    <?php
                    if ($folder_info->subfolder_count) {
                        echo $folder_info->subfolder_count . " ";

                        if ($folder_info->subfolder_count > 1) {
                            echo app_lang("folders");
                        } else {
                            echo app_lang("folder");
                        }
                    }

                    if ($folder_info->subfile_count) {

                        if ($folder_info->subfolder_count) {
                            echo ", ";
                        }

                        echo $folder_info->subfile_count . " ";

                        if ($folder_info->subfile_count > 1) {
                            echo app_lang("files");
                        } else {
                            echo app_lang("file");
                        }
                    }

                    if (!$folder_info->subfolder_count && !$folder_info->subfile_count) {
                        echo app_lang('empty');
                    }
                    ?>
                </small>
            </div>
        </div>
    </div>

    <?php if ($can_manage_folder_access_permissions && $folder_info->context !== "client" && $folder_info->context !== "project") { ?>

        <div class="b-t pb20 pt20">
            <h4><?php echo app_lang("who_has_access"); ?></h4>

            <?php

            // Display the permissions
            function display_permission($parent_permissions_list, $permissions_list)
            {
                echo "<ul class='list-group access-list'>";
                foreach ($parent_permissions_list as $item) {
                    echo "<li class='list-group-item'><i data-feather='" . $item['icon'] . "' class='icon-16 mr10 text-off'></i> <i data-feather='git-merge' class='icon-16 mr10 text-off text-warning'></i>" . $item['text'] . "</li>";
                }
                foreach ($permissions_list as $item) {
                    echo "<li class='list-group-item'><i data-feather='" . $item['icon'] . "' class='icon-16 mr10 text-off'></i>" . $item['text'] . "</li>";
                }

                echo "</ul>";
            }

            if (count($full_access_list) || count($parent_full_access_list)) {
                echo "<div class='text-off'>" . app_lang("full_access") . "</div>";
                display_permission($parent_full_access_list, $full_access_list);
            }

            if (count($upload_and_organize_list) || count($parent_upload_and_organize_list)) {
                echo "<div class='text-off'>" . app_lang("upload_and_organize") . "</div>";
                display_permission($parent_upload_and_organize_list, $upload_and_organize_list);
            }

            if (count($upload_only_list) || count($parent_upload_only_list)) {
                echo "<div class='text-off'>" . app_lang("upload_only") . "</div>";
                display_permission($parent_upload_only_list, $upload_only_list);
            }

            if (count($read_only_list) || count($parent_read_only_list)) {
                echo "<div class='text-off'>" . app_lang("read_only") . "</div>";
                display_permission($parent_read_only_list, $read_only_list);
            }

            echo modal_anchor(get_uri($controller_slag . "/folder_permissions_modal_form"), "<i data-feather='key' class='icon-16 mr5'></i>" . app_lang('manage_access'), array("class" => "btn btn-default", "data-post-id" => $folder_info->id, "title" => app_lang('manage_access') . ": " . $folder_info->title));

            ?>
        </div>
    <?php } ?>

    <?php if ($login_user->user_type == "staff") { ?>
        <div class="b-t pt20 pb20 pt20">
            <h4><?php echo app_lang("folder_details"); ?></h4>

            <div class="text-off"><?php echo app_lang("created_by"); ?></div>
            <ul class="list-group access-list">
                <li class="list-group-item"><?php echo get_team_member_profile_link($folder_info->created_by, $folder_info->created_by_user_name); ?></li>
            </ul>

            <div class="text-off"><?php echo app_lang("created_at"); ?></div>
            <ul class="list-group access-list">
                <li class="list-group-item"><?php echo format_to_relative_time($folder_info->created_at); ?></li>
            </ul>
        </div>
    <?php } ?>

<?php
} else {
?>
    <div class="no-file-selected">
        <div class="files-icon">
            <i class="no-file-selected-icon" data-feather="file-text"></i>
            <div class="no-file-selected-text font-12 text-off"><?php echo app_lang("select_a_file_to_view_details"); ?></div>
        </div>
    </div>
<?php
}
