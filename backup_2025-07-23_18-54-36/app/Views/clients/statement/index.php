<div class="card">
    <div class="tab-title clearfix">
        <h4><?php echo app_lang('statement'); ?></h4>
    </div>
    <div class="display-block w100p">
        <div class="clearfix b-b">
            <div id="statement-filters" class="float-start"></div>
            <div class="float-end">
                <?php echo anchor("", "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "class" => "btn btn-default mt15 mr15", "id" => "download-statement-pdf-btn")); ?>
            </div>
        </div>
        <div class="invoice-preview">
            <div id="invoice-preview" class="invoice-preview-container bg-white mt15 mb15">
                <div id="load-statement"></div>
            </div>
        </div>
    </div>

</div>

<?php
load_css(array(
    "assets/css/invoice.css",
));
?>

<script type="text/javascript">
    $(document).ready(function() {

        function updateDownloadPdfButtonUrl(startDate, endDate) {
            var url = '<?php echo_uri("invoice_payments/download_pdf/" . $client_id . "/") ?>';
            url = url + "?start_date=" + startDate + "&end_date=" + endDate;

            $("#download-statement-pdf-btn").attr("href", url);
        }

        $("#statement-filters").appFilters({
            source: '<?php echo_uri("invoice_payments/statement_data/" . $client_id . "/") ?>',
            targetSelector: '#load-statement',
            rangeRadioButtons: [{
                name: "range_radio_button",
                selectedOption: 'yearly',
                options: ['monthly', 'yearly', 'custom']
            }],
            beforeRelaodCallback: function(instance, params) {
                updateDownloadPdfButtonUrl(params.start_date, params.end_date);
            },
            afterRelaodCallback: function(instance, params) {
                updateDownloadPdfButtonUrl(params.start_date, params.end_date);
            },
            onInitComplete: function(instance, params) {
                updateDownloadPdfButtonUrl(params.start_date, params.end_date);
            },
        });
    });
</script>