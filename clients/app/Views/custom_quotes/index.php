<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix">
            <h1><?php echo app_lang('custom_quotes'); ?></h1>
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("custom_quotes/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_custom_quote'), array("class" => "btn btn-default", "title" => app_lang('add_custom_quote'))); ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="custom-quotes-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#custom-quotes-table").appTable({
            source: '<?php echo_uri("custom_quotes/list_data") ?>',
            order: [[0, 'desc']],
            columns: [
                {title: "<?php echo app_lang('id') ?>", "class": "w50"},
                {title: "<?php echo app_lang('quote_number') ?>"},
                {title: "<?php echo app_lang('client') ?>"},
                {title: "<?php echo app_lang('item') ?>"},
                {title: "<?php echo app_lang('quantity') ?>", "class": "text-right"},
                {title: "<?php echo app_lang('base_price') ?>", "class": "text-right"},
                {title: "<?php echo app_lang('custom_price') ?>", "class": "text-right"},
                {title: "<?php echo app_lang('discount') ?>", "class": "text-right"},
                {title: "<?php echo app_lang('valid_until') ?>", "class": "w100"},
                {title: "<?php echo app_lang('status') ?>", "class": "w100 text-center"},
                {title: "<?php echo app_lang('created_by') ?>"},
                {title: "<?php echo app_lang('created_date') ?>", "class": "w100"},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100"}
            ],
            printColumns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            xlsColumns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
        });
    });
</script>