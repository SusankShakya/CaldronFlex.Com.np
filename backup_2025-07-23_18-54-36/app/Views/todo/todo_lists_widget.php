<div class="card bg-white">
    <div class="card-header">
        <i data-feather="check-square" class="icon-16"></i>&nbsp; <?php echo app_lang('todo') . " (" . app_lang('private') . ")"; ?>

        <div class="float-end">
            <div class="form-check form-switch form-check-reverse mb0">
                <input class="form-check-input" type="checkbox" role="switch" id="todo-sortable-switch">
                <label class="form-check-label mb0" for="todo-sortable-switch"><?php echo app_lang("sortable"); ?></label>
            </div>
        </div>
    </div>

    <?php echo form_open(get_uri("todo/save/1"), array("id" => "todo-inline-form", "class" => "", "role" => "form")); ?>
    <div class="widget-todo-input-box mb0 todo-input-box">

        <div class="input-group pb15">
            <?php
            echo form_input(array(
                "id" => "todo-title",
                "name" => "title",
                "value" => "",
                "class" => "form-control",
                "placeholder" => app_lang('add_a_todo')
            ));
            ?>
            <span class="input-group-btn">
                <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
            </span>
        </div>

    </div>
    <?php echo form_close(); ?>

    <div class="table-responsive" id="todo-list-widget-table">
        <table id="todo-table" class="display no-thead hide-dtr-control widget-table" cellspacing="0" width="100%">
        </table>
    </div>
</div>

<?php echo view("todo/helper_js"); ?>

<script type="text/javascript">
    $(document).ready(function() {
        if (!isMobile()) {
            initScrollbar('#todo-list-widget-table', {
                setHeight: 653
            });
        }

        $("#todo-table_length").addClass("hide");
    });
</script>