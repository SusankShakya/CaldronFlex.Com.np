<div class="card no-border clearfix mb0">

    <?php echo form_open(get_uri("settings/save_tinymce_settings"), array("id" => "tinymce-form", "class" => "general-form dashed-row", "role" => "form")); ?>

    <div class="card-body">

        <div class="form-group">
            <div class="row">
                <label for="enable_tinymce" class="col-md-3"><?php echo app_lang('enable_tinymce'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_checkbox("enable_tinymce", "1", get_setting("enable_tinymce") ? true : false, "id='enable_tinymce' class='form-check-input'");
                    ?>
                </div>
            </div>
        </div>

        <div class="tinymce-details-area <?php echo get_setting("enable_tinymce") ? "" : "hide" ?>">
            <div class="form-group">
                <div class="row">
                    <label class=" col-md-12">
                        <?php echo app_lang("get_your_key_from_here") . " " . anchor("https://www.tiny.cloud/", "TinyMCE", array("target" => "_blank")); ?>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="tinymce_api_key" class=" col-md-2"><?php echo app_lang('tinymce_api_key'); ?></label>
                    <div class=" col-md-10">
                        <?php
                        echo form_input(array(
                            "id" => "tinymce_api_key",
                            "name" => "tinymce_api_key",
                            "value" => get_setting("tinymce_api_key"),
                            "class" => "form-control",
                            "placeholder" => app_lang('tinymce_api_key'),
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required")
                        ));
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>
    <?php echo form_close(); ?>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        var $enableTinymce = $("#enable_tinymce"),
            $tinymceDetailsArea = $(".tinymce-details-area");

        $("#tinymce-form").appForm({
            isModal: false,
            onSuccess: function(result) {
                appAlert.success(result.message, {
                    duration: 10000
                });
            }
        });

        //show/hide tinymce details area
        $enableTinymce.click(function() {
            if ($(this).is(":checked")) {
                $tinymceDetailsArea.removeClass("hide");
            } else {
                $tinymceDetailsArea.addClass("hide");
            }
        });
    });
</script>