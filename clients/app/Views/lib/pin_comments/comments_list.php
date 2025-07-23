<?php
foreach ($pinned_comments as $comment) {
    $comment_id = $comment->project_comment_id;
    $comment_link = "#comment-" . $comment_id;
    $comment_type = "project";
    if ($comment->ticket_comment_id) {
        $comment_id = $comment->ticket_comment_id;
        $comment_link = "#ticket-comment-" . $comment_id;
        $comment_type = "ticket";
    }
?>
    <a href="<?php echo $comment_link; ?>" id="<?php echo $comment_id; ?>" class="pin-comment-preview pinned-comment-highlight-link" data-bs-trigger="hover" data-bs-toggle="popover" data-original-comment-link-id="<?php echo $comment_id; ?>" data-original-comment-id="<?php echo "comment-" . $comment_id; ?>" data-comment-type="<?php echo $comment_type; ?>">
        <div id="pinned-comment-<?php echo $comment_id; ?>" class="d-flex">
            <div class="flex-shrink-0">
                <span class="avatar avatar-xs mt5">
                    <img src="<?php echo get_avatar($comment->commented_by_avatar); ?>" alt="..." />
                </span>
            </div>
            <div class="w-100 pl10">
                <div class="float-start dark">
                    <?php echo $comment->commented_by_user; ?>
                    <small>
                        <p class='text-off'><?php echo format_to_relative_time($comment->comment_created_at); ?></p>
                    </small>
                </div>
                <div class="float-end pin mt5">
                    <i data-feather="map-pin" class="icon-16 text-info"></i>
                </div>
            </div>
        </div>
    </a>
<?php }
?>

<script type="text/javascript">
    $(document).ready(function() {
        $(".pin-comment-preview").one('mousemove', function() {
            var messageId = this.id;
            var commentType = $(this).data('comment-type');

            $("#" + messageId).popover({
                placement: 'left',
                container: 'body',
                html: true,
                content: function() {
                    if (commentType === "project") {
                        return $('#prject-comment-container-task-' + messageId).html();
                    } else if (commentType === "ticket") {
                        return $('#ticket-comment-' + messageId).html();
                    }
                }
            }).popover('show');
        });
    });
</script>