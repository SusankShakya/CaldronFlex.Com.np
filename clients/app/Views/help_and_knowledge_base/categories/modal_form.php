<?php echo form_open(get_uri("help/save_category"), array("id" => "category-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix post-dropzone">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="type" value="<?php echo $type; ?>" />

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
        <div class="form-group">
            <div class="row">
                <label for="description" class=" col-md-3"><?php echo app_lang('description'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_textarea(array(
                        "id" => "description",
                        "name" => "description",
                        "value" => process_images_from_content($model_info->description, false),
                        "class" => "form-control",
                        "placeholder" => app_lang('description'),
                        "data-rich-text-editor" => true
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="sort" class=" col-md-3"><?php echo app_lang('sort'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "sort",
                        "name" => "sort",
                        "value" => $model_info->sort,
                        "class" => "form-control",
                        "placeholder" => app_lang('sort'),
                        "type" => "number",
                        "min" => "0"
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="sort" class=" col-md-3"><?php echo app_lang('articles_order'); ?></label>
                <div class=" col-md-9">
                    <?php

                    echo form_dropdown(
                        "articles_order",
                        array(
                            "" => "A-Z",
                            "Z-A" => "Z-A"
                        ),
                        $model_info->articles_order,
                        "class='select2 mini'"
                    );

                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="related_article_labels" class="col-md-3"><?php echo app_lang('show_related_articles_by_labels'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "related_article_labels",
                        "name" => "related_articles",
                        "value" => $model_info->related_articles,
                        "class" => "form-control",
                        "placeholder" => app_lang('labels')
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label class=" col-md-3"><?php echo app_lang('banner_image'); ?></label>
                <div class=" col-md-9">

                    <?php if ($model_info->banner_image) {
                        $banner_image = unserialize($model_info->banner_image);
                    ?>
                        <div class="float-start mr15">
                            <img id="banner-image-preview" style="max-width: 100px; max-height: 80px;" src="<?php echo get_source_url_of_file($banner_image, get_setting("timeline_file_path"), "thumbnail"); ?>" alt="..." />
                        </div>
                    <?php } ?>

                    <div class="float-start mr15">
                        <?php echo view("includes/dropzone_preview"); ?>
                    </div>
                    <div class="float-start upload-file-button btn btn-default btn-sm pt5">
                        <span><i data-feather="upload-cloud" class="icon-16"></i></span>
                    </div>
                    <?php if ($model_info->banner_image) { ?>
                        <input type="hidden" name="remove_banner_image" id="remove_banner_image" value="" />
                        <div class="float-start btn btn-default btn-sm ml10 pt5" id="remove-banner-image-button">
                            <span><i data-feather="x" class="icon-16"></i></span>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="banner_url" class=" col-md-3"><?php echo app_lang('banner_url'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "banner_url",
                        "name" => "banner_url",
                        "value" => $model_info->banner_url,
                        "class" => "form-control",
                        "placeholder" => app_lang('banner_url')
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="status" class=" col-md-3"><?php echo app_lang('status'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_radio(array(
                        "id" => "status_active",
                        "name" => "status",
                        "class" => "form-check-input",
                        "data-msg-required" => app_lang("field_required"),
                    ), "active", ($model_info->status === "active") ? true : (($model_info->status !== "inactive") ? true : false));
                    ?>
                    <label for="status_active" class="mr15"><?php echo app_lang('active'); ?></label>
                    <?php
                    echo form_radio(array(
                        "id" => "status_inactive",
                        "name" => "status",
                        "class" => "form-check-input",
                        "data-msg-required" => app_lang("field_required"),
                    ), "inactive", ($model_info->status === "inactive") ? true : false);
                    ?>
                    <label for="status_inactive" class=""><?php echo app_lang('inactive'); ?></label>
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
        $("#category-form").appForm({
            onSuccess: function(result) {
                // check if category table exists
                if ($("#category-table").length > 0) {
                    $("#category-table").appTable({
                        newData: result.data,
                        dataId: result.id
                    });
                } else {
                    location.reload();
                }
            }
        });

        $("#category-form .select2").select2();

        $("#related_article_labels").select2({
            multiple: true,
            data: <?php echo json_encode($label_suggestions); ?>
        });

        var uploadUrl = "<?php echo get_uri("uploader/upload_file"); ?>";
        var validationUrl = "<?php echo get_uri("uploader/validate_file"); ?>";

        var dropzone = attachDropzoneWithForm("#category-form", uploadUrl, validationUrl, {
            maxFiles: 1
        });

        $("#remove-banner-image-button").click(function() {
            $("#banner-image-preview").fadeOut();
            $("#remove_banner_image").val("1");
            $("#banner_url").val("");
        });
    });
</script>