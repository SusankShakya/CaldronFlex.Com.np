<div class="table-responsive">
    <table id="automation-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#automation-table").appTable({
            source: "<?php echo_uri('automation/list_data/tickets') ?>",
            order: [[0, 'desc']],
            columns: [
                {title: "<?php echo app_lang('title'); ?>", "class": "all"},
                {title: "<?php echo app_lang('event'); ?>"},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100"}
            ]
        });
    });
</script>