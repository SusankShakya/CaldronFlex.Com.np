<?php echo form_open(get_uri("todo/save"), array("id" => "todo-form", "class" => "general-form", "role" => "form")); ?>
<div id="todo-dropzone" class="post-dropzone">
    <div class="modal-body clearfix">
        <div class="container-fluid">
            <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
            <input type="hidden" name="view_type" id="view_type" value="" />
            <div class="form-group">
                <div class="col-md-12">
                    <?php
                    echo form_input(array(
                        "id" => "title",
                        "name" => "title",
                        "value" => $model_info->title,
                        "class" => "form-control notepad-title",
                        "placeholder" => app_lang('title'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <div class="notepad">
                        <?php
                        echo form_textarea(array(
                            "id" => "description",
                            "name" => "description",
                            "value" => process_images_from_content($model_info->description, false),
                            "class" => "form-control",
                            "placeholder" => app_lang('description') . "...",
                            "data-rich-text-editor" => true,
                            "data-height" => 300
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <div class="notepad">
                        <?php
                        echo form_input(array(
                            "id" => "todo_labels",
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
                <div class="col-md-12">
                    <?php
                    echo form_input(array(
                        "id" => "todo_start_date",
                        "name" => "start_date",
                        "value" => is_date_exists($model_info->start_date) ? $model_info->start_date : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('date'),
                        "autocomplete" => "off"
                    ));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-12 row">
                    <?php
                    echo view("includes/file_list", array("files" => $model_info->files));
                    ?>
                </div>
            </div>

            <?php echo view("includes/dropzone_preview"); ?>

        </div>
    </div>

    <div class="modal-footer">
        <?php echo view("includes/upload_button", array("show_link_copy_button" => true)); ?>

        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#todo-form").appForm({
            onSuccess: function(result) {
                $("#todo-table").appTable({
                    newData: result.data,
                    dataId: result.id
                });
            }
        });

        setTimeout(function() {
            $("#title").focus();
        }, 200);

        $("#todo_labels").select2({
            multiple: true,
            data: <?php echo json_encode($label_suggestions); ?>
        });

        setDatePicker("#todo_start_date");


        var widgetTable = $("#todo-table").hasClass("widget-table");
        if (widgetTable || isMobile()) {
            $("#view_type").val("responsive");
        }

    });
</script>