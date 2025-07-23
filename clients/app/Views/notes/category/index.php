<div id="page-content" class="page-wrapper pb0 clearfix note-categories-view">

    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang('notes') . " (" . app_lang('private') . ")"; ?></h4></li>

        <?php echo view("notes/tabs", array("active_tab" => "categories")); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("notes/category_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_category'), array("class" => "btn btn-default", "title" => app_lang('add_category'))); ?>
            </div>
        </div>

    </ul>

    <div class="card border-top-0 rounded-top-0">
        <div class="table-responsive">
            <table id="note-category-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#note-category-table").appTable({
            source: '<?php echo_uri("notes/category_list_data") ?>',
            order: [[0, "desc"]],
            columns: [
                {title: '<?php echo app_lang("name") ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: [0],
            xlsColumns: [0]
        });
    });
</script>