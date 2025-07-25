<?php echo get_reports_topbar(); ?>

<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card clearfix">
        <ul id="income-vs-expenses-chart-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white inner scrollable-tabs clearfix" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("income_vs_expenses"); ?></h4></li>
            <li><a id="income-vs-expenses-chart-button" role="presentation"  data-bs-toggle="tab"  href="javascript:;" data-bs-target="#income-vs-expenses-chart-tab"><?php echo app_lang("chart"); ?></a></li>
            <li><a role="presentation"  data-bs-toggle="tab" href="<?php echo_uri("expenses/income_vs_expenses_summary/"); ?>" data-bs-target="#income-vs-expenses-summary"><?php echo app_lang("summary"); ?></a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="income-vs-expenses-chart-tab">
                <div>
                    <div class="card-header clearfix">
                        <div class="income-vs-expenses-chart-button">
                            <?php
                            if ($projects_dropdown) {
                                echo form_input(array(
                                    "id" => "projects-dropdown",
                                    "name" => "projects-dropdown",
                                    "class" => "select2 w200 reload-chart font-normal",
                                    "placeholder" => app_lang('project')
                                ));
                            }
                            ?>

                            <div class="inline-block chart-date-range-button" id="yearly-chart-date-range-selector"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="income-vs-expenses-chart" style="width: 100%; height: 350px;"></canvas>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="income-vs-expenses-summary"></div>
        </div>

    </div>
</div>

<script type="text/javascript">
    var incomeExpensesChartContent;

    var initIncomeExpenseChart = function (income, expense) {

        var incomeExpensesChart = document.getElementById("income-vs-expenses-chart");

        if (incomeExpensesChartContent) {
            incomeExpensesChartContent.destroy();
        }

        incomeExpensesChartContent = new Chart(incomeExpensesChart, {
            type: 'line',
            data: {
                labels: ["<?php echo app_lang('short_january'); ?>", "<?php echo app_lang('short_february'); ?>", "<?php echo app_lang('short_march'); ?>", "<?php echo app_lang('short_april'); ?>", "<?php echo app_lang('short_may'); ?>", "<?php echo app_lang('short_june'); ?>", "<?php echo app_lang('short_july'); ?>", "<?php echo app_lang('short_august'); ?>", "<?php echo app_lang('short_september'); ?>", "<?php echo app_lang('short_october'); ?>", "<?php echo app_lang('short_november'); ?>", "<?php echo app_lang('short_december'); ?>"],
                datasets: [{
                        label: "<?php echo app_lang('income'); ?>",
                        borderColor: '#36a2eb',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        data: income
                    }, {
                        label: "<?php echo app_lang('expense'); ?>",
                        borderColor: '#ff8c1a',
                        backgroundColor: 'rgba(255, 205, 86, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        data: expense
                    }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        label: function (tooltipItems, data) {
                            if (tooltipItems) {
                                return data.datasets[tooltipItems.datasetIndex].label + ": " + toCurrency(tooltipItems.yLabel);
                            } else {
                                return false;
                            }
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        fontColor: "#898fa9"
                    }
                },
                scales: {
                    xAxes: [{
                            gridLines: {
                                color: 'rgba(127,127,127,0.1)'
                            },
                            ticks: {
                                fontColor: "#898fa9"
                            }
                        }],
                    yAxes: [{
                            gridLines: {
                                color: 'rgba(127,127,127,0.1)'
                            },
                            ticks: {
                                fontColor: "#898fa9"
                            }
                        }]
                }
            }
        });
    };

    var prepareExpensesChart = function (data) {
        var project_id = $("#projects-dropdown").val() || "0";
        data.project_id = project_id;

        appLoader.show();
        appAjaxRequest({
            url: "<?php echo_uri("expenses/income_vs_expenses_chart_data") ?>",
            data: data,
            cache: false,
            type: 'POST',
            dataType: "json",
            success: function (response) {
                appLoader.hide();
                initIncomeExpenseChart(response.income, response.expenses);
            }
        });
    };

    $(document).ready(function () {
        $("#income-vs-expenses-chart-button").click(function () {
            prepareExpensesChart(data);
        });

        var $projectsDropdown = $("#projects-dropdown"),
                data = {};

<?php if ($projects_dropdown) { ?>
            $projectsDropdown.select2({
                data: <?php echo $projects_dropdown; ?>
            });
<?php } ?>

        $(".reload-chart").change(function () {
            prepareExpensesChart(data);
        });

        $("#yearly-chart-date-range-selector").appDateRange({
            dateRangeType: "yearly",
            onChange: function (dateRange) {
                data = dateRange;
                prepareExpensesChart(dateRange);
            },
            onInit: function (dateRange) {
                data = dateRange;
                prepareExpensesChart(dateRange);
            }
        });
    });
</script>
