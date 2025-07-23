<div id="ticket-comment-<?php echo $comment->id; ?>" class="b-b p10 m0 text-break bg-white comment-highlight-section comment-container ticket-comment-container <?php echo $comment->is_note ? "note-background" : "" ?>">
    <div class="d-flex">
        <div class="flex-shrink-0 mr10">
            <span class="avatar avatar-xs">
                <?php if (!$comment->created_by || $comment->created_by == 999999999) { ?>
                    <img src="<?php echo get_avatar("system_bot"); ?>" alt="..." />
                <?php } else { ?>
                    <img src="<?php echo get_avatar($comment->created_by_avatar); ?>" alt="..." />
                <?php
                }
                ?>
            </span>
        </div>
        <div class="w-100">
            <div>
                <?php
                if ($comment->created_by == 999999999) {
                    //user is an app boot for auto reply tickets
                    echo "<span class='dark strong'>" . get_setting('app_title') . "</span>";
                } else if (!$comment->created_by && $comment->creator_email) {
                    //user is an undefined client from email
                    echo "<span class='dark strong'>" . $comment->creator_name . " [" . app_lang("unknown_client") . "]" . "</span>";
                } else {
                    if ($comment->user_type === "staff") {
                        echo get_team_member_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                    } else {
                        echo get_client_contact_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                    }
                }
                ?>
                <small><span class="text-off"><?php echo format_to_relative_time($comment->created_at); ?></span></small>

                <?php if ($login_user->user_type == "staff") { ?>
                    <span class="float-end dropdown comment-dropdown">
                        <div class="text-off dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                            <i data-feather="chevron-down" class="icon-16 clickable"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end" role="menu">

                            <?php
                            $pin_status = "";
                            $unpin_status = "";

                            if ($comment->pinned_comment_status) {
                                $pin_status = "hide";
                                $unpin_status = "";
                            } else {
                                $pin_status = "";
                                $unpin_status = "hide";
                            }
                            ?>

                            <?php if ($comment->is_note) { ?>
                                <li role="presentation"><a href="javascript:;" title="<?php echo app_lang('insert_into_editor') ?>" class="insert-into-editor-button dropdown-item"><span data-feather="clipboard" class="icon-16"></span> <?php echo app_lang('insert_into_editor'); ?></a></li>
                            <?php } else { ?>
                                <li role="presentation"><?php echo js_anchor("<i data-feather='map-pin' class='icon-16'></i> " . app_lang('pin_comment'), array("id" => "pin-comment-button-$comment->id", "class" => "dropdown-item pin-comment-button $pin_status", 'title' => app_lang('pin_comment'), "data-action-url" => get_uri("tickets/pin_comment/" . $comment->id), "data-pin-comment-id" => $comment->id)); ?> </li>
                                <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/pin_comment/" . $comment->id . "/" . $comment->ticket_id), "<i data-feather='map-pin' class='icon-16'></i> " . app_lang('unpin_comment'), array("id" => "unpin-comment-button-$comment->id", "class" => "dropdown-item unpin-comment-button $unpin_status", 'title' => app_lang('unpin_comment'), "data-pin-comment-id" => $comment->id, "data-fade-out-on-success" => "#pinned-comment-$comment->id")); ?> </li>
                            <?php } ?>

                            <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/delete_comment/$comment->id"), "<i data-feather='x' class='icon-16'></i> " . app_lang('delete'), array("class" => "dropdown-item", "title" => app_lang('delete'), "data-fade-out-on-success" => "#ticket-comment-$comment->id")); ?> </li>
                        </ul>
                    </span>
                <?php } ?>

                <?php if (!$comment->created_by && $comment->creator_email) { ?>
                    <div class="block text-off"><?php echo $comment->creator_email; ?></div>
                <?php } ?>
            </div>

            <?php if ($comment->is_note) {
                echo "<textarea id='ticket-comment-description' class='hide'>" . $comment->description . "</textarea>";
            } ?>

            <p>
                <?php
                $files = array();
                if ($comment->files) {
                    $files = unserialize($comment->files);
                }

                $comment_description = "";
                if ($comment->description) {
                    $comment_description = custom_nl2br(link_it(process_images_from_content($comment->description)), true);

                    foreach ($files as $key => $attachment) {
                        if (
                            !get_array_value($attachment, "content_id") ||
                            !str_contains($comment_description, "cid:" . get_array_value($attachment, "content_id"))
                        ) {
                            continue;
                        }

                        $url = get_source_url_of_file($attachment, get_setting("timeline_file_path"), "thumbnail");
                        $comment_description = str_replace("cid:" . get_array_value($attachment, "content_id"), $url, $comment_description);

                        // don't show this file anymore as attachment
                        unset($files[$key]);
                    }
                }

                echo $comment_description;
                ?>
            </p>

            <div class="comment-image-box clearfix">

                <?php
                if ($files) {
                    $total_files = count($files);
                    echo view("includes/timeline_preview", array("files" => $files));

                    if ($total_files) {
                        $download_caption = app_lang('download');
                        if ($total_files > 1) {
                            $download_caption = sprintf(app_lang('download_files'), $total_files);
                        }
                        echo "<i data-feather='paperclip' class='icon-16'></i>";
                        echo anchor(get_uri("tickets/download_comment_files/" . $comment->id), $download_caption, array("class" => "float-end", "title" => $download_caption));
                    }
                }

                ?>
            </div>
        </div>
    </div>
</div>