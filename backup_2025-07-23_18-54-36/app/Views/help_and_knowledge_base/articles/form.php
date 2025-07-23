<div id="page-content" class="page-wrapper clearfix">
    <div class="card view-container">
        <div id="help-dropzone" class="post-dropzone">
            <?php echo form_open(get_uri("help/save_article"), array("id" => "article-form", "class" => "general-form", "role" => "form")); ?>

            <div>

                <div class="page-title clearfix">
                    <?php if ($model_info->id) { ?>
                        <h1><?php echo app_lang('edit_article') . " (" . app_lang($type) . ")"; ?></h1>
                        <div class="title-button-group">
                            <?php echo anchor(get_uri("help/" . $type . "_articles"), "<i data-feather='book-open' class='icon-16'></i> " . app_lang('articles'), array("class" => "btn btn-default round", "title" => app_lang('articles'))); ?>
                            <?php echo anchor(get_uri("$type/view/" . $model_info->id), "<i data-feather='search' class='icon-16'></i> " . app_lang('view'), array("class" => "btn btn-default round", "title" => app_lang('view'))); ?>
                            <?php echo anchor(get_uri("help/article_form/" . $type), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_article'), array("class" => "btn btn-default", "title" => app_lang('add_article'))); ?>
                        </div>
                    <?php } else { ?>
                        <h1><?php echo app_lang('add_article') . " (" . app_lang($type) . ")"; ?></h1>
                        <div class="title-button-group">
                            <?php echo anchor(get_uri("help/" . $type . "_articles"), "<i data-feather='book-open' class='icon-16'></i> " . app_lang('articles'), array("class" => "btn btn-default round", "title" => app_lang('articles'))); ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="card-body">

                    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
                    <input type="hidden" name="type" value="<?php echo $type; ?>" />

                    <div class="form-group">
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
                    <div class="form-group">
                        <label for="category_id" class=" col-md-12"><?php echo app_lang('category'); ?></label>
                        <div class=" col-md-12">
                            <?php
                            echo form_dropdown("category_id", $categories_dropdown, $model_info->category_id, "class='select2 validate-hidden' id='category_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                            ?>
                        </div>
                    </div>
                    <div class="form-group">

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

                    <div class="form-group">
                        <div class="row">
                            <label for="sort" class="col-md-12"><?php echo app_lang('sort'); ?></label>
                            <div class="col-md-12">
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
                            <label for="article_labels" class="col-md-12"><?php echo app_lang('labels'); ?></label>
                            <div class="col-md-12">
                                <?php
                                echo form_input(array(
                                    "id" => "article_labels",
                                    "name" => "labels",
                                    "value" => $model_info->labels,
                                    "class" => "form-control",
                                    "placeholder" => app_lang('labels')
                                ));
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class=" col-md-12">
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

                    <div class="form-group">
                        <div class="col-md-12">
                            <?php
                            echo view("includes/file_list", array("files" => $model_info->files));
                            ?>
                        </div>
                    </div>

                </div>
                <?php echo view("includes/dropzone_preview"); ?>

                <div class="card-footer clearfix">
                    <div class="float-start">
                        <?php echo view("includes/upload_button"); ?>
                    </div>
                    <button type="submit" class="btn btn-primary float-end"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                </div>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#article-form").appForm({
            ajaxSubmit: false
        });
        setTimeout(function() {
            $("#title").focus();
        }, 200);
        initWYSIWYGEditor("#description");


        $("#category_id").select2();

        $("#article_labels").select2({
            multiple: true,
            data: <?php echo json_encode($label_suggestions); ?>
        });

    });
</script>