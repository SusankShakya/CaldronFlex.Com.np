<?php
$isMobile = preg_match('/(android|iphone|ipad|windows phone)/i', get_array_value($_SERVER, 'HTTP_USER_AGENT'));
$url = $isMobile ? "todo/save/1" : "todo/save";
?>

<div id="page-content" class="page-wrapper clearfix todo-page">

    <?php echo form_open(get_uri($url), array("id" => "todo-inline-form", "class" => "", "role" => "form")); ?>
    <div class="todo-input-box">

        <div class="input-group">
            <?php
            echo form_input(array(
                "id" => "todo-title",
                "name" => "title",
                "value" => "",
                "class" => "form-control",
                "placeholder" => app_lang('add_a_todo'),
                "autocomplete" => "off",
                "autofocus" => true
            ));
            ?>
            <span class="input-group-btn">
                <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
            </span>
        </div>

    </div>
    <?php echo form_close(); ?>

    <div class="card">
        <div class="page-title clearfix">
            <h1> <?php echo app_lang('todo') . " (" . app_lang('private') . ")"; ?></h1>

            <div id="todo-sortable-switch-container" class="float-end mt20 me-4 hide">
                <div class="form-check form-switch form-check-reverse mb0">
                    <input class="form-check-input" type="checkbox" role="switch" id="todo-sortable-switch">
                    <label class="form-check-label mb0" for="todo-sortable-switch"><?php echo app_lang("sortable"); ?></label>
                </div>
            </div>
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "to_do")); ?>
                <?php echo modal_anchor(get_uri("todo/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_todo'), array("class" => "btn btn-default", "title" => app_lang('add_todo'))); ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="todo-table" class="display" cellspacing="0" width="100%">
            </table>
        </div>
    </div>
</div>

<?php echo view("todo/helper_js"); ?>