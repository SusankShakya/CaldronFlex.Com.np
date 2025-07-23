<div>
    <div id="expense-chart-filters" class="chart-date-range-button">
    </div>
    <div id="load-expense-chart"></div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var dynamicDates = getDynamicDates();
        $("#expense-chart-filters").appFilters({
            source: '<?php echo_uri("expenses/category_chart_view") ?>',
            targetSelector: '#load-expense-chart',
            rangeDatepicker: [{startDate: {name: "start_date", value: dynamicDates.start_of_year}, endDate: {name: "end_date", value: dynamicDates.end_of_year}, showClearButton: true, label: "<?php echo app_lang('date'); ?>", ranges: ['this_month', 'last_month', 'this_year', 'last_year', 'last_30_days', 'last_7_days']}],
            beforeRelaodCallback: function () {

            },
            afterRelaodCallback: function () {

            }
        });
    });
</script>