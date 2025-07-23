<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="page-title clearfix">
                    <h1><?php echo app_lang('variant_combinations') . " - " . $item_info->title; ?></h1>
                    <div class="title-button-group">
                        <a href="<?php echo get_uri("items"); ?>" class="btn btn-default"><i data-feather="arrow-left" class="icon-16"></i> <?php echo app_lang('back_to_items'); ?></a>
                        <?php echo modal_anchor(get_uri("item_variants/combination_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_combination'), array("class" => "btn btn-default", "title" => app_lang('add_combination'), "data-post-item_id" => $item_id)); ?>
                    </div>
                </div>

                <?php if (!empty($variants)) { ?>
                <div class="table-responsive">
                    <table id="combination-table" class="display" cellspacing="0" width="100%">            
                    </table>
                </div>
                <?php } else { ?>
                <div class="alert alert-info">
                    <i data-feather="info" class="icon-16"></i>
                    <?php echo app_lang('no_variants_defined'); ?>
                    <a href="<?php echo get_uri("item_variants"); ?>" class="btn btn-sm btn-primary ms-2">
                        <?php echo app_lang('manage_variants'); ?>
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?php if (!empty($variants)) { ?>
        $("#combination-table").appTable({
            source: '<?php echo get_uri("item_variants/combinations_list_data") ?>',
            order: [[0, "asc"]],
            filterParams: {item_id: <?php echo $item_id; ?>},
            columns: [
                {title: '<?php echo app_lang("sku") ?>', "class": "w150"},
                {title: '<?php echo app_lang("variant_combination") ?>'},
                {title: '<?php echo app_lang("additional_price") ?>', "class": "w100 text-right"},
                {title: '<?php echo app_lang("stock_quantity") ?>', "class": "w100 text-center"},
                {title: '<?php echo app_lang("status") ?>', "class": "w50 text-center"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: [0, 1, 2, 3, 4],
            xlsColumns: [0, 1, 2, 3, 4]
        });
        <?php } ?>
    });
</script>