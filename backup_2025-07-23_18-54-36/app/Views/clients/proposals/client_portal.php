<?php if (isset($page_type) && $page_type === "full") { ?>
    <div id="page-content" class="clearfix page-wrapper">
    <?php } ?>

    <div class="card clearfix">
        <?php if (isset($page_type) && $page_type === "full") { ?>
            <div class="page-title clearfix">
                <h1><?php echo app_lang('proposals'); ?></h1>
            </div>
        <?php } else { ?>
            <div class="tab-title clearfix">
                <h4><?php echo app_lang('proposals'); ?></h4>
            </div>
        <?php } ?>

        <div class="table-responsive">
            <table id="proposal-table" class="display" width="100%">
            </table>
        </div>
    </div>
    <?php if (isset($page_type) && $page_type === "full") { ?>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {
        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        var showCommentOption = false;
        if ("<?php echo get_setting("enable_comments_on_proposals") == "1" ?>") {
            showCommentOption = true;
        }

        var idColumnClass = "w25p";

        if (isMobile()) {
            idColumnClass = "";
        }

        $("#proposal-table").appTable({
            source: '<?php echo_uri("proposals/proposal_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            filterDropdown: [<?php echo $custom_field_filters; ?>],
            columns: [
                {title: "<?php echo app_lang("proposal") ?>", "class": idColumnClass + " all"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("proposal_date") ?>", "class": "all", "iDataSort": 2},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("valid_until") ?>", "iDataSort": 4, "class": "w20p"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-right"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"},
                {visible: showCommentOption, title: "<?php echo app_lang("comments") ?>", "class": "text-center w50"}
<?php echo $custom_field_headers; ?>,
                {visible: false}
            ],
            summation: [{column: 8, dataType: 'currency', currencySymbol: currencySymbol}]
        });
    });
</script>