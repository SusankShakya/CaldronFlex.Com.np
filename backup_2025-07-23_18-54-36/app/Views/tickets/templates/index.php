<div id="page-content" class="page-wrapper pb0 clearfix ticket-templates-view">

    <ul class="nav nav-tabs bg-white title" role="tablist">

        <?php echo view("tickets/index", array("active_tab" => "ticket_templates")); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("tickets/ticket_template_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_template'), array("class" => "btn btn-default", "title" => app_lang('add_template'))); ?>
            </div>
        </div>

    </ul>

    <div class="card border-top-0 rounded-top-0">
        <div class="table-responsive">
            <table id="ticket-template-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#ticket-template-table").appTable({
            source: '<?php echo_uri("tickets/ticket_template_list_data") ?>',
            order: [[0, 'desc']],
            columns: [
                {title: '<?php echo app_lang("title"); ?>', "class": "w300 all"},
                {title: '<?php echo app_lang("description") ?>'},
                {title: '<?php echo app_lang("category") ?>', "class": "w150"},
                {title: '<?php echo app_lang("private") ?>', "class": "w100"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });
    });
</script>