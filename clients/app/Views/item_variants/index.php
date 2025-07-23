<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "item_variants";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">
                <div class="page-title clearfix">
                    <h4><?php echo app_lang('item_variants'); ?></h4>
                    <div class="title-button-group">
                        <?php echo modal_anchor(get_uri("item_variants/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_variant'), array("class" => "btn btn-default", "title" => app_lang('add_variant'))); ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="variant-table" class="display" cellspacing="0" width="100%">            
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#variant-table").appTable({
            source: '<?php echo get_uri("item_variants/list_data") ?>',
            filterDropdown: [
                {name: "item_id", class: "w200", options: <?php echo $items_dropdown; ?>},
                {name: "variant_type", class: "w150", options: [
                    {id: "", text: "- <?php echo app_lang('variant_type'); ?> -"},
                    <?php
                    foreach ($variant_types as $key => $value) {
                        echo '{id: "' . $key . '", text: "' . $value . '"},';
                    }
                    ?>
                ]}
            ],
            columns: [
                {title: '<?php echo app_lang("item") ?>', "class": "w150"},
                {title: '<?php echo app_lang("variant_type") ?>', "class": "w100"},
                {title: '<?php echo app_lang("variant_name") ?>', "class": "w150"},
                {title: '<?php echo app_lang("variant_value") ?>', "class": "w150"},
                {title: '<?php echo app_lang("price_modifier") ?>', "class": "w100"},
                {title: '<?php echo app_lang("sort_order") ?>', "class": "w50 text-center"},
                {title: '<?php echo app_lang("status") ?>', "class": "w50 text-center"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: [0, 1, 2, 3, 4, 5, 6],
            xlsColumns: [0, 1, 2, 3, 4, 5, 6]
        });
    });
</script>