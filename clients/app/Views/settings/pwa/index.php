<?php echo form_open(get_uri("settings/save_pwa_settings"), array("id" => "pwa-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

<div class="card-body post-dropzone">

    <div class="form-group">
        <div class="row">
            <label for="pwa_icon" class=" col-md-2"><?php echo app_lang('icon'); ?> (192x192) </label>
            <div class=" col-md-10">
                <div class="float-start mr15">
                    <img id="pwa_icon-preview" src="<?php echo get_file_from_setting("pwa_icon", false, get_setting("system_file_path") . "pwa/"); ?>" alt="..." style="width: 50px" />
                </div>
                <div class="float-start file-upload btn btn-default btn-sm">
                    <i data-feather="upload" class="icon-14"></i> <?php echo app_lang("upload_and_crop"); ?>
                    <input id="pwa_icon_file" class="cropbox-upload upload" name="pwa_icon_file" type="file" data-height="192" data-width="192" data-preview-container="#pwa_icon-preview" data-input-field="#pwa_icon" />
                </div>
                <div class="mt10 ml10 float-start">
                    <?php
                    echo form_upload(array(
                        "id" => "pwa_icon_file_upload",
                        "name" => "pwa_icon_file",
                        "class" => "no-outline hidden-input-file"
                    ));
                    ?>
                    <label for="pwa_icon_file_upload" class="btn btn-default btn-sm">
                        <i data-feather="upload" class="icon-14"></i> <?php echo app_lang("upload"); ?>
                    </label>
                </div>
                <input type="hidden" id="pwa_icon" name="pwa_icon" value="" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <label for="pwa_theme_color" class="col-md-2"><?php echo app_lang('app_color'); ?> </label>
            <div class=" col-md-10">
                <input type="color" id="pwa_theme_color" name="pwa_theme_color" value="<?php echo get_setting("pwa_theme_color") ? get_setting("pwa_theme_color") : "#1c2026"; ?>" />
            </div>
        </div>
    </div>

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#pwa-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function(data) {
                $.each(data, function(index, obj) {
                    if (obj.name === "pwa_icon") {
                        var image = replaceAll(":", "~", data[index]["value"]);
                        data[index]["value"] = image;
                    }
                });
            },
            onSuccess: function(result) {
                if (result.success) {
                    appAlert.success(result.message, {
                        duration: 10000
                    });

                    if ($("#pwa_icon").val() || result.reload_page) {
                        location.reload();
                    }
                } else {
                    appAlert.error(result.message);
                }
            }
        });

        var uploadUrl = "<?php echo get_uri("uploader/upload_file"); ?>";
        var validationUrl = "<?php echo get_uri("uploader/validate_file"); ?>";

        $(".cropbox-upload").change(function() {
            showCropBox(this);
        });
    });
</script>