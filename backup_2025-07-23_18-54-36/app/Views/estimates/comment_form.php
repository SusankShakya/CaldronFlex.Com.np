<div class="card" id="estimate-comments-section">
    <div class="card-header fw-bold">
        <span class="inline-block">
            <i data-feather="message-square" class="icon-16"></i> &nbsp;<?php echo app_lang("comments"); ?>
        </span>
    </div>
    <div class="card-body">
        <?php
        //for assending mode, show the comment box at the top
        if (!$sort_as_decending) {
            foreach ($comments as $comment) {
                echo view("estimates/comment_row", array("comment" => $comment));
            }
        }
        ?>
        <div id="comment-form-container">
            <?php echo form_open(get_uri("estimates/save_comment"), array("id" => "comment-form", "class" => "general-form", "role" => "form")); ?>
            <div class="">
                <div id="estimate-comment-dropzone" class="post-dropzone form-group">
                    <input type="hidden" name="estimate_id" value="<?php echo $estimate_info->id; ?>">
                    <?php
                    echo form_textarea(array(
                        "id" => "description",
                        "name" => "description",
                        "class" => "form-control",
                        "placeholder" => app_lang('write_a_comment'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                        "data-rich-text-editor" => true,
                    ));
                    ?>
                    <?php echo view("includes/dropzone_preview"); ?>
                    <footer class="card-footer b-a clearfix ">
                        <div class="float-start">
                            <?php echo view("includes/upload_button", array("upload_button_text" => "")); ?>
                        </div>

                        <button class="btn btn-primary float-end" type="submit"><i data-feather="send" class='icon-16'></i> <?php echo app_lang("post_comment"); ?></button>
                    </footer>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>

        <?php
        //for decending mode, show the comment box at the bottom
        if ($sort_as_decending) {
            foreach ($comments as $comment) {
                echo view("estimates/comment_row", array("comment" => $comment));
            }
        }
        ?>

    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        setEstimatePreviewScrollable();
        $(window).resize(function() {
            setEstimatePreviewScrollable();
        });

        var decending = "<?php echo $sort_as_decending; ?>";

        $("#comment-form").appForm({
            isModal: false,
            onSuccess: function(result) {
                $("#description").val("");

                if (decending) {
                    $(result.data).insertAfter("#comment-form-container");
                } else {
                    $(result.data).insertBefore("#comment-form-container");
                }

                appAlert.success(result.message, {
                    duration: 10000
                });

                if (window.formDropzone) {
                    window.formDropzone['estimate-comment-dropzone'].removeAllFiles();
                }
            }
        });
    });

    setEstimatePreviewScrollable = function() {
        var options = {
            setHeight: $(window).height() - 85
        };
        initScrollbar('#estimate-comment-container', options);

        //don't apply scrollbar for mobile devices
        if ($(window).width() <= 640) {
            $('#estimate-preview-content').css({
                "overflow": "auto"
            });
        } else {
            initScrollbar("#estimate-preview-content", options);
        }
    };
</script>