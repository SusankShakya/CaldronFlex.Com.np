<div class="modal-body clearfix general-form">
    <div class="container-fluid">
        <div class="form-group">
            <div class="col-md-12 notepad-title">
                <strong><?php echo $model_info->title; ?></strong>
            </div>
        </div>

        <div class="col-md-12 mb15">
            <?php

            $note_labels = js_anchor(
                $model_info->labels_list ? make_labels_view_data($model_info->labels_list) : "<span class='text-off'>" . app_lang("add") . " " . app_lang("labels") . "<span>",
                array(
                    "class" => "mr10",
                    "data-id" => $model_info->id,
                    "data-value" => $model_info->labels,
                    "data-act" => "update-todo-info",
                    "data-modifier-group" => "todo_info",
                    "data-field" => "labels",
                    "data-multiple-tags" => "1",
                    "data-action-url" => get_uri("todo/update_todo_info/$model_info->id/labels")
                )
            );

            $date = js_anchor(
                is_date_exists($model_info->start_date) ? format_to_date($model_info->start_date, false) : "<span class='text-off'>" . app_lang("add") . " " . app_lang("date") . "<span>",
                array(
                    "data-id" => $model_info->id,
                    "data-value" => $model_info->start_date,
                    "data-act" => "update-todo-info",
                    "data-modifier-group" => "todo_info",
                    "data-action-type" => "date",
                    "data-action-url" => get_uri("todo/update_todo_info/$model_info->id/start_date")
                )
            );

            echo $note_labels . " " . $date;

            ?>
        </div>

        <?php if ($model_info->description) { ?>
            <div class="col-md-12 mb15 notepad">
                <?php
                echo $model_info->description ? custom_nl2br(link_it(process_images_from_content($model_info->description))) : "";
                ?>
            </div>
        <?php } ?>

        <?php if ($model_info->files) { ?>
            <div class="col-md-12 mt15">
                <?php
                if ($model_info->files) {
                    $files = unserialize($model_info->files);
                    echo view("includes/timeline_preview", array("files" => $files, "seperate_audio" => true));
                }
                ?>
            </div>
        <?php } ?>


    </div>
</div>

<div class="modal-footer">
    <?php
    echo modal_anchor(get_uri("todo/modal_form"), "<i data-feather='edit-2' class='icon-16'></i> " . app_lang('edit'), array("class" => "btn btn-default", "data-post-id" => $model_info->id, "title" => app_lang('edit')));
    ?>
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('body').on('click', '[data-act=update-todo-info]', function(e) {
            $(this).appModifier({
                dropdownData: {
                    labels: <?php echo json_encode($label_suggestions); ?>,
                }
            });

            return false;
        });
    });
</script>