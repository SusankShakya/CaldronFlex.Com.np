<?php
//for assending mode, show the comment box at the top
if (!$sort_as_decending) {
?>
    <div id="ticket-comments-list">
        <?php foreach ($comments as $comment) {
            echo view("tickets/comment_row", array("comment" => $comment));
        } ?>
    </div>
<?php
}
?>

<div id="comment-form-container" class="hidden-xs">
    <?php echo form_open(get_uri("tickets/save_comment"), array("id" => "comment-form", "class" => "general-form", "role" => "form")); ?>
    <div class="p15 d-flex">
        <div class="flex-shrink-0 hidden-xs">
            <div class="avatar avatar-xs mr15">
                <img src="<?php echo get_avatar($login_user->image); ?>" alt="..." />
            </div>
        </div>

        <div class="w-100">
            <div id="ticket-comment-dropzone" class="post-dropzone form-group">
                <input type="hidden" name="ticket_id" value="<?php echo $ticket_info->id; ?>">
                <input type="hidden" id="is-note" name="is_note" value="0">
                <?php
                echo form_textarea(array(
                    "id" => "description",
                    "name" => "description",
                    "class" => "form-control",
                    "style" => "height: 200px",
                    "value" => process_images_from_content(get_setting('user_' . $login_user->id . '_signature'), false),
                    "placeholder" => app_lang('write_a_comment'),
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                    "data-rich-text-editor" => true,
                    // "data-keep-rich-text-editor-after-submit"=>"1",
                    "data-move-cursor-to-first" => "1",
                    "data-toolbar" => "mini_toolbar",
                    "data-encode_ajax_post_data" => "1"
                ));
                ?>
                <?php echo view("includes/dropzone_preview"); ?>
                <footer class="card-footer b-a clearfix ticket-view-footer-button">
                    <div class="float-start"><?php echo view("includes/upload_button"); ?></div>

                    <?php
                    if ($login_user->user_type === "staff" && $view_type != "modal_view") {
                        echo modal_anchor(get_uri("tickets/insert_template_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> <span class='hidden-xs'>" . app_lang('template') . "</span>", array("class" => "btn btn-default float-start round round-btn-xs ml10", "title" => app_lang('insert_template'), "data-post-ticket_type_id" => $ticket_info->ticket_type_id, "id" => "insert-template-btn"));
                    }
                    ?>

                    <div class="float-end">
                        <?php if ($login_user->user_type === "staff") { ?>
                            <button id="save-as-note-button" class="btn btn-info text-white" type="button" data-bs-toggle="tooltip" title="<?php echo app_lang('client_will_not_see_any_notes') ?>"><i data-feather='message-circle' class='icon-16'></i><span class="hidden-xs ml5""><?php echo app_lang("save_as_note"); ?></span></button>
                        <?php } ?>
                        <button id=" save-ticket-comment-button" class="btn btn-primary ml5" type="submit"><i data-feather='send' class='icon-16'></i><span class="ml5"><?php echo app_lang("send"); ?></span></button>
                    </div>
                </footer>
            </div>
        </div>

    </div>
    <?php echo form_close(); ?>
</div>

<?php
//for decending mode, show the comment box at the bottom
if ($sort_as_decending) {
?>
    <div id="ticket-comments-list">
        <?php foreach ($comments as $comment) {
            echo view("tickets/comment_row", array("comment" => $comment));
        } ?>
    </div>
<?php
}
?>