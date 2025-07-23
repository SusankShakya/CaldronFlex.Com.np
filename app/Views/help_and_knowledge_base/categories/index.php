<div id="page-content" class="page-wrapper clearfix">

    <ul class="nav nav-tabs bg-white title" role="tablist">
        <?php echo view("help_and_knowledge_base/tabs", array("active_tab" => "categories_list", "type" => $type)); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
            <?php echo modal_anchor(get_uri("help/category_modal_form/" . $type), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_category'), array("class" => "btn btn-default", "title" => app_lang('add_category'))); ?>
            </div>
        </div>

    </ul>

    <div class="card border-top-0 rounded-top-0 xs-no-bottom-margin">
        <div class="table-responsive">
        <table id="category-table" class="display" cellspacing="0" width="100%">            
        </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#category-table").appTable({
            source: '<?php echo_uri("help/categories_list_data/" . $type) ?>',
            columns: [
                {title: '<?php echo app_lang("title"); ?>', "class": "all"},
                {title: '<?php echo app_lang("description"); ?>'},
                {title: '<?php echo app_lang("status"); ?>'},
                {title: '<?php echo app_lang("sort"); ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: [0, 1, 2, 3]
        });
    });
</script>