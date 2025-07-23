<div id="page-content" class="page-wrapper clearfix">
    <div class="card view-container">
        <div id="announcement-dropzone" class="post-dropzone">
            <?php echo form_open(get_uri("announcements/save"), array("id" => "announcement-form", "class" => "general-form", "role" => "form")); ?>

            <div>

                <div class="page-title clearfix">
                    <?php if ($model_info->id) { ?>
                        <h1><?php echo app_lang('edit_announcement'); ?></h1>
                        <div class="title-button-group">
                            <?php echo anchor(get_uri("announcements/view/" . $model_info->id), "<i data-feather='search' class='icon-16'></i> " . app_lang('view'), array("class" => "btn btn-default", "title" => app_lang('view'))); ?>
                        </div>
                    <?php } else { ?>
                        <h1><?php echo app_lang('add_announcement'); ?></h1>
                    <?php } ?>
                </div>

                <div class="card-body">
                    <div class="container-fluid">
                        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
                        <div class="form-group">
                            <div class="row">
                                <label for="title" class="col-md-12"><?php echo app_lang('title'); ?></label>
                                <div class=" col-md-12">
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
                                <div class=" col-md-12">
                                    <?php
                                    echo form_textarea(array(
                                        "id" => "description",
                                        "name" => "description",
                                        "value" => process_images_from_content($model_info->description, false),
                                        "placeholder" => app_lang('description'),
                                        "class" => "form-control",
                                        "data-toolbar" => "page_builder_toolbar",
                                        "data-encode_ajax_post_data" => "1",
                                        "data-height" => 250
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix">
                            <div class="row">
                                <label for="start_date" class="col-md-2"><?php echo app_lang('start_date'); ?></label>
                                <div class="form-group col-md-4">
                                    <?php
                                    echo form_input(array(
                                        "id" => "start_date",
                                        "name" => "start_date",
                                        "value" => $model_info->start_date,
                                        "class" => "form-control",
                                        "placeholder" => "YYYY-MM-DD",
                                        "autocomplete" => "off",
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required")
                                    ));
                                    ?>
                                </div>

                                <label for="end_date" class="col-md-2"><?php echo app_lang('end_date'); ?></label>
                                <div class="form-group col-md-4">
                                    <?php
                                    echo form_input(array(
                                        "id" => "end_date",
                                        "name" => "end_date",
                                        "value" => $model_info->end_date,
                                        "class" => "form-control",
                                        "placeholder" => "YYYY-MM-DD",
                                        "autocomplete" => "off",
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                        "data-rule-greaterThanOrEqual" => "#start_date",
                                        "data-msg-greaterThanOrEqual" => app_lang("end_date_must_be_equal_or_greater_than_start_date")
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="share_with" class=" col-md-2"><?php echo app_lang('share_with'); ?></label>
                                <div class=" col-md-10">

                                    <div id="share_with_container">
                                        <?php echo $get_sharing_options_view; ?>
                                    </div>

                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class=" col-md-2"></label>
                                    <div class="col-md-10">
                                        <?php
                                        echo view("includes/file_list", array("files" => $model_info->files));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php echo view("includes/dropzone_preview"); ?>

                </div>
                <div class="card-footer clearfix">
                    <?php echo view("includes/upload_button"); ?>
                    <button type="submit" class="btn btn-primary float-end"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#announcement-form").appForm({
            isModal: false,
            onSuccess: function(response) {
                appAlert.success(response.message, {
                    duration: 10000
                });
                setTimeout(function() {
                    window.location.href = response.recirect_to;
                }, 1000)

            }
        });

        setTimeout(function() {
            $("#title").focus();
        }, 200);

        initWYSIWYGEditor("#description");

        setDatePicker("#start_date");
        setDatePicker("#end_date");

    });
</script>