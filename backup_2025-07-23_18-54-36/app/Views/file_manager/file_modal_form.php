<?php echo form_open(get_uri("file_manager/save_file"), array("id" => "file-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="folder_id" value="<?php echo $folder_id; ?>" />
        <input type="hidden" name="context" value="<?php echo $context; ?>" />
        <input type="hidden" name="context_id" value="<?php echo $context_id; ?>" />
        <?php echo view("includes/multi_file_uploader"); ?>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default cancel-upload" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" disabled="disabled" class="btn btn-primary start-upload"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#file-form").appForm({
            onSuccess: function(result) {
                location.reload();
            }
        });

    });
</script>