<div class="table-responsive">
    <table id="lead-details-page-estimate-request-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {

        var fieldVisibility = false;
        if ("<?php echo $login_user->user_type; ?>" === "staff") {
            fieldVisibility = true;
        }

        $("#lead-details-page-estimate-request-table").appTable({
            source: '<?php echo_uri("estimate_requests/estimate_requests_list_data_of_client/" . $client_id) ?>',
            order: [[0, 'desc']],
            columns: [
                {title: "<?php echo app_lang('id'); ?>"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang('title'); ?>", "class": "all"},
                {title: "<?php echo app_lang('assigned_to'); ?>", visible: fieldVisibility},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("created_date") ?>', "iDataSort": 3},
                {title: "<?php echo app_lang('status'); ?>"},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center dropdown-option w100", visible: fieldVisibility}
            ],
            printColumns: [0, 2, 3, 5, 6]
        });
    });
</script>