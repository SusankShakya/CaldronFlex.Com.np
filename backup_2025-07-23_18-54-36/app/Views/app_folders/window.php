<div id="file-manager-window-area" class="show-context-menu">

    <ul class="files-and-folders-list" data-has_write_permission="<?php echo $has_write_permission; ?>" data-has_upload_permission="<?php echo $has_upload_permission; ?>">
        <?php
        foreach ($folders_list as $folder) {
            $is_favourite = strpos($folder->starred_by, ":" . $login_user->id . ":") ? 1 : '';
            $has_this_folder_write_permission = false;

            if ($login_user->is_admin || ($folder->context == "file_manager" && $folder->actual_permission_rank >= 6) || ($folder->context != "file_manager" && $login_user->user_type == "staff")) {
                $has_this_folder_write_permission = true;
            }
        ?>
            <li class="folder-item" data-id="<?php echo $folder->id; ?>" data-folder_id="<?php echo $folder->folder_id; ?>" data-type='folder' data-is_favourite="<?php echo $is_favourite; ?>" data-has_this_folder_write_permission="<?php echo $has_this_folder_write_permission; ?>">
                <div class='folder-item-content show-context-menu folder-thumb-area'>
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3 icon-wrapper">
                            <i data-feather='folder' class='icon-40 bold-folder-icon'></i>
                        </div>
                        <div class="w-100">
                            <div class="folder-name item-name"><?php echo $folder->title; ?></div>
                            <small class="text-off folder-info">
                                <?php
                                if ($folder->subfolder_count) {
                                    echo $folder->subfolder_count . " ";

                                    if ($folder->subfolder_count > 1) {
                                        echo app_lang("folders");
                                    } else {
                                        echo app_lang("folder");
                                    }
                                }

                                if ($folder->subfile_count) {

                                    if ($folder->subfolder_count) {
                                        echo ", ";
                                    }

                                    echo $folder->subfile_count . " ";

                                    if ($folder->subfile_count > 1) {
                                        echo app_lang("files");
                                    } else {
                                        echo app_lang("file");
                                    }
                                }

                                if (!$folder->subfolder_count && !$folder->subfile_count) {
                                    echo app_lang('empty');
                                }
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
                <span class="file-manager-more-menu">
                    <i data-feather='more-horizontal' class='icon-18'></i>
                </span>
            </li>
            <?php
        }

        foreach ($folder_items as $folder_item) {
            if ($folder_item_type == "file") {
                $file_name = short_file_name(remove_file_prefix($folder_item->file_name));
                $file_size = convert_file_size($folder_item->file_size);

                $preview_link_attr = $file_preview_link_attributes;

                $data_url = $file_preview_url . "/" . $folder_item->id;
                if ($client_id) {
                    $data_url .= "/" . $client_id;
                }

                $preview_link_attr["data-url"] = $data_url;

                $preview_link_attr["data-preview_function"] = "showFilePreviewAppModal";
                $preview_link_attr["data-group"] = "window_files";
            ?>
                <li class="folder-item" data-id="<?php echo $folder_item->id; ?>" data-type='file'>
                    <div class='folder-item-content show-context-menu file-thumb-area'>
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3 icon-wrapper">
                                <i data-feather='file' class='icon-40 bold-file-icon'></i>
                                <?php
                                try {
                                    app_hooks()->do_action('app_hook_file_manager_file_icon_extension', $folder_item);
                                } catch (\Exception $ex) {
                                    log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
                                }
                                ?>
                            </div>
                            <div class="w-100">
                                <div class="text-break"><?php echo js_anchor($file_name, $preview_link_attr); ?></div>
                                <small class="text-off file-size"><?php echo $file_size; ?></small>
                            </div>
                        </div>
                    </div>
                    <span class="file-manager-more-menu">
                        <i data-feather='more-horizontal' class='icon-18'></i>
                    </span>
                </li>
        <?php
            }
        }
        ?>
    </ul>
</div>

<?php
try {
    app_hooks()->do_action('app_hook_file_manager_window_extension');
} catch (\Exception $ex) {
    log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
}
?>