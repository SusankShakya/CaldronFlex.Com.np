<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="page-title clearfix">
            <h1><?php echo app_lang('pricing_rules'); ?></h1>
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("pricing_rules/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_pricing_rule'), array("class" => "btn btn-default", "title" => app_lang('add_pricing_rule'))); ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="pricing-rules-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#pricing-rules-table").appTable({
            source: '<?php echo_uri("pricing_rules/list_data") ?>',
            columns: [
                {title: '<?php echo app_lang("item"); ?>'},
                {title: '<?php echo app_lang("rule_type"); ?>'},
                {title: '<?php echo app_lang("quantity_range"); ?>'},
                {title: '<?php echo app_lang("discount"); ?>'},
                {title: '<?php echo app_lang("validity"); ?>'},
                {title: '<?php echo app_lang("status"); ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });
    });
</script>