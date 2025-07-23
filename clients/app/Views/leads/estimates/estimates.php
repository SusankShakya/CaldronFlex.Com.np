<div class="card">
    <ul data-bs-toggle="ajax-tab" data-do-not-save-state="1" class="nav nav-tabs bg-white title" role="tablist">
        <li class="nav-item"><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#lead-estimates-tab"><?php echo app_lang("estimates"); ?></a></li>
        <?php if ($show_estimate_request_info) { ?>
            <li class="nav-item"><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("leads/estimate_requests/" . $lead_info->id); ?>" data-bs-target="#lead-estimate-requests"> <?php echo app_lang('estimate_requests'); ?></a></li>
        <?php } ?>
        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_estimate'), array("class" => "btn btn-default", "data-post-client_id" => $client_id, "title" => app_lang('add_estimate'))); ?>
            </div>
        </div>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="lead-estimates-tab">
            <div class="table-responsive">
                <table id="lead-details-page-estimate-table" class="display" width="100%">
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right"><?php echo app_lang("total") ?>:</th>
                            <th class="text-right" data-current-page="4"></th>
                            <th> </th>
                        </tr>
                        <tr data-section="all_pages">
                            <th colspan="4" class="text-right"><?php echo app_lang("total_of_all_pages") ?>:</th>
                            <th class="text-right" data-all-page="4"></th>
                            <th> </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="lead-estimate-requests"></div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var currencySymbol = "<?php echo $lead_info->currency_symbol; ?>";
        var showCommentOption = false;
        if ("<?php echo get_setting("enable_comments_on_estimates") == "1" ?>") {
            showCommentOption = true;
        }

        $("#lead-details-page-estimate-table").appTable({
            source: '<?php echo_uri("estimates/estimate_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            filterDropdown: [<?php echo $custom_field_filters; ?>],
            columns: [
                {title: "<?php echo app_lang("estimate") ?>", "class": "w20p all"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("estimate_date") ?>", "iDataSort": 2, "class": "w20p all"},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-right w25p"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center w25p"},
                {visible: showCommentOption, title: '<i data-feather="message-circle" class="icon-16"></i>', "class": "text-center w50"}
<?php echo $custom_field_headers; ?>,
                {visible: false}
            ],
            summation: [{column: 4, dataType: 'currency', currencySymbol: currencySymbol}]
        });
    });
</script>