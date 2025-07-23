<?php if (!(isset($data_only) && $data_only)) { ?>
    <div id="note-grid-<?php echo $note->id ?>" class="col-md-3 col-sm-6">
    <?php } ?>

    <div class="card" style="background-color: <?php echo $note->color ? $note->color : "#83c340" ?>40 !important; border-top: 5px solid <?php echo $note->color ? $note->color : "#83c340" ?>60  !important;">
        <div class="card-body p15 note-grid-card">

            <?php
            $description_line_clamp = 2;
            if (!$note->category_name) {
                $description_line_clamp = $description_line_clamp + 1;
            }

            // Initialize $files to avoid undefined variable error
            $files = [];

            if ($note->files) {
                $files = unserialize($note->files);
                if (!count($files)) {
                    $description_line_clamp = $description_line_clamp + 1;
                }
            } else {
                $description_line_clamp = $description_line_clamp + 1;
            }

            if (!$note->labels_list) {
                $description_line_clamp = $description_line_clamp + 1;
            }

            // we need to determine the description line clamps based on the category, files and labels section
            if ($description_line_clamp === 4 || $description_line_clamp === 5) {
                // we can show more 1 lines if there has 1 or 2 sections
                $description_line_clamp = $description_line_clamp + 1;
            } else if ($description_line_clamp === 6) {
                // we can show more 2 lines if there are no sections of these 3
                $description_line_clamp = $description_line_clamp + 2;
            }

            ?>

            <div>
                <div class="d-flex">
                    <?php echo modal_anchor(get_uri("notes/view/" . $note->id), $note->title, array("data-modal-title" => app_lang('note'), "data-post-id" => $note->id, "class" => "font-16 strong notes-grid-text-ellipsis webkit-line-clamp-2 flex-grow-1 dark", "title" => $note->title)); ?>

                    <span class="dropdown pl5 pr5">
                        <div class="dropdown-toggle clickable" data-bs-toggle="dropdown" aria-expanded="true">
                            <i data-feather="more-vertical" class="icon-16"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end mt-1" role="menu">
                            <li role="presentation"><?php echo modal_anchor(get_uri("notes/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit'), array("class" => "dropdown-item note-grid-edit-button", "title" => app_lang('edit_note'), "data-post-id" => $note->id)); ?> </li>
                            <li role="presentation"><?php echo js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang('delete'), array('title' => app_lang('delete_note'), "class" => "dropdown-item", "data-id" => $note->id, "data-action-url" => get_uri("notes/delete"), "data-action" => "delete-confirmation", "data-success-callback" => "noteGridDeleted")); ?> </li>
                        </ul>
                    </span>

                </div>
                <div class="mt5 text-off"><i class="icon-14" data-feather="clock"></i> <?php echo format_to_relative_time($note->created_at); ?></div>
                <div class="mt15 notes-grid-text-ellipsis webkit-line-clamp-<?php echo $description_line_clamp; ?>"><?php echo $note->description ? custom_nl2br(html_entity_decode(strip_tags(convert_comment_link(process_images_from_content($note->description))))) : "-"; ?></div>


                <div class="mt15 position-absolute bottom-15 w-100">
                    <?php if ($note->category_name) { ?>
                        <div class="text-wrap-ellipsis"><i class="icon-16" data-feather="package"></i> <?php echo $note->category_name; ?></div>
                    <?php } ?>

                    <?php if (count($files)) { ?>
                        <div class="mt5 d-flex overflow-hidden w86p">
                            <?php
                            $files_link = "";
                            $file_download_link = "";
                            if ($note->files) {
                                if (count($files)) {
                                    foreach ($files as $key => $value) {
                                        $file_name = get_array_value($value, "file_name");
                                        $link = get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                                        $file_download_link = anchor(get_uri("notes/download_files/" . $note->id), "<i data-feather='download-cloud' class='icon-14'></i>", array("title" => app_lang("download"), "class" => "file-list-view file-download"));
                                        $files_link .= js_anchor("<i data-feather='$link' class='icon-14'></i>", array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "class" => "file-list-view", "title" => remove_file_prefix($file_name), "data-url" => get_uri("notes/file_preview/" . $note->id . "/" . $key)));
                                    }
                                }
                            }

                            echo $file_download_link . $files_link;
                            ?>
                        </div>
                    <?php } ?>

                    <?php if ($note->labels_list) { ?>
                        <div class="mt5 text-wrap-ellipsis"><?php echo make_labels_view_data($note->labels_list, true); ?></div>
                    <?php } ?>

                </div>
            </div>

        </div>
    </div>

    <?php if (!(isset($data_only) && $data_only)) { ?>
    </div>
<?php } ?>