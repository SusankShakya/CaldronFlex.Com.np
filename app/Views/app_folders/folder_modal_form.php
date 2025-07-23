<?php echo form_open(get_uri($controller_slag . "/save_folder"), array("id" => "folder-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="parent_id" value="<?php echo $model_info->parent_id; ?>" />
        <input type="hidden" name="context" value="<?php echo $model_info->context; ?>" />
        <input type="hidden" name="context_id" value="<?php echo $model_info->context_id; ?>" />

        <div class="form-group">
            <div class="row">
                <label for="title" class=" col-md-3"><?php echo app_lang('title'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "title",
                        "name" => "title",
                        "value" => $model_info->title,
                        "class" => "form-control",
                        "placeholder" => app_lang('title'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#folder-form").appForm({
            onSuccess: function(result) {
                if (result.success) {
                    openFolderWindow(result.parent_folder_id);
                    updateFavoritesSection();
                }
            }
        });
        setTimeout(function() {
            $("#title").focus().select();
        }, 200);
    });
</script>