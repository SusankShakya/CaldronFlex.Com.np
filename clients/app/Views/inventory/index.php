<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix">
            <h1><?php echo app_lang('inventory'); ?></h1>
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("inventory/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_inventory'), array("class" => "btn btn-default", "title" => app_lang('add_inventory'))); ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="inventory-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#inventory-table").appTable({
            source: '<?php echo_uri("inventory/list_data") ?>',
            columns: [
                {title: "<?php echo app_lang('id') ?>", "class": "w50"},
                {title: "<?php echo app_lang('item') ?>"},
                {title: "<?php echo app_lang('warehouse') ?>"},
                {title: "<?php echo app_lang('current_stock') ?>", "class": "text-right"},
                {title: "<?php echo app_lang('reserved_stock') ?>", "class": "text-right"},
                {title: "<?php echo app_lang('available_stock') ?>", "class": "text-right"},
                {title: "<?php echo app_lang('reorder_level') ?>", "class": "text-right"},
                {title: "<?php echo app_lang('status') ?>", "class": "text-center"},
                {title: "<?php echo app_lang('last_updated') ?>"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
            xlsColumns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
        });
    });
</script>