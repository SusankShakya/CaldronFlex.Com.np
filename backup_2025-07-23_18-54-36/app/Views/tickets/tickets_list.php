<div id="page-content" class="clearfix grid-button tickets-list-view page-wrapper">
    <div class="flex-shrink-0">
        <div class="tickets-list-section">
            <ul class=" nav nav-tabs bg-white title scrollable-tabs" role="tablist">
                <?php echo view("tickets/index", array("active_tab" => "tickets_list")); ?>

                <div class="tab-title clearfix no-border tickets-page-title">
                    <div class="title-button-group">
                        <?php
                        echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "ticket"));
                        echo modal_anchor(get_uri("tickets/settings_modal_form"), "<i data-feather='settings' class='icon-16'></i> " . app_lang('settings'), array("class" => "btn btn-default", "title" => app_lang('settings')));
                        echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_ticket'), array("class" => "btn btn-default", "title" => app_lang('add_ticket')));
                        ?>
                    </div>
                </div>
            </ul>

            <div class="card border-top-0 rounded-top-0 xs-no-bottom-margin">
                <div class="table-responsive scrollable-table">
                    <table id="ticket-table" class="display xs-hide-dtr-control no-title" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

$statuses = array();
$ticket_statuses = array("new", "open", "client_replied", "closed");
$selected_status = isset($status) ? $status : "";

//Check the clickable links from dashboard
$ignore_saved_filter = false;

foreach ($ticket_statuses as $status) {
    $is_selected = false;

    if ($selected_status) {
        //if there is any specific status selected, select only the status.
        if ($selected_status == "all") {
            $is_selected = false;
            $ignore_saved_filter = true;
        } else if ($selected_status == $status) {
            $is_selected = true;
            $ignore_saved_filter = true;
        }
    } else if ($status === "new" || $status === "open" || $status === "client_replied") {
        //select the New, Open & Client replied filter by default
        $is_selected = true;
    }

    $statuses[] = array("text" => app_lang($status), "value" => $status, "isChecked" => $is_selected);
}

?>

<script type="text/javascript">
    var compactViewId = 0;
    <?php if (isset($ticket_id) && $ticket_id) { ?>
        compactViewId = "<?php echo $ticket_id; ?>";
    <?php } ?>

    $(document).ready(function() {

        var ignoreSavedFilter = false;
        <?php if ($ignore_saved_filter) { ?>
            ignoreSavedFilter = true;
        <?php } ?>

        var clientId = "";
        <?php if (isset($selected_client_id) && $selected_client_id) { ?>
            clientId = "<?php echo $selected_client_id; ?>";
            ignoreSavedFilter = true;
        <?php } ?>

        var ticketCompactView = appCompactView.init({
            compactViewId: compactViewId,
            backButtonUrl: "<?php echo_uri('tickets'); ?>",
            backButtonText: "<?php echo app_lang('back'); ?>",
            dataSourceUrl: "<?php echo get_uri('tickets/view/' . $ticket_id . '/'); ?>" + clientId,
            compactViewBaseUrl: "<?php echo get_uri('tickets/compact_view/'); ?>",
        });

        var applyActiveRow = delayAction(function() {
            ticketCompactView.setActiveRow();
        }, 100);


        var optionsVisibility = false;
        if ("<?php
                if (isset($show_options_column) && $show_options_column) {
                    echo '1';
                }
                ?>" == "1") {
            optionsVisibility = true;
        }

        var projectVisibility = false;
        if ("<?php echo $show_project_reference; ?>" == "1") {
            projectVisibility = true;
        }

        var filterDropdowns = [];

        var clientAccessPermission = "<?php echo get_array_value($login_user->permissions, "client"); ?>";
        if (clientAccessPermission === "all" || <?php echo $login_user->is_admin ?>) {
            filterDropdowns.push({
                name: "client_id",
                class: "w200",
                options: <?php echo $clients_dropdown; ?>,
                value: clientId
            });
        }

        filterDropdowns.push({
            name: "ticket_type_id",
            class: "w200",
            options: <?php echo $ticket_types_dropdown; ?>
        });
        filterDropdowns.push({
            name: "ticket_label",
            class: "w200",
            options: <?php echo $ticket_labels_dropdown; ?>
        });
        filterDropdowns.push({
            name: "assigned_to",
            class: "w200",
            options: <?php echo $assigned_to_dropdown; ?>
        });
        filterDropdowns.push(<?php echo $custom_field_filters; ?>);

        var batchUpdateUrl = "<?php echo get_uri("tickets/batch_update_modal_form"); ?>";

        var mobileView = 0,
            sortColumn = 9;
        if (isMobile() || compactViewId) {
            mobileView = 1,
                sortColumn = 10; //sort by client_last_activity_at
        }

        var dynamicDates = getDynamicDates();
        $("#ticket-table").appTable({
            source: '<?php echo_uri("tickets/list_data/0/") ?>' + mobileView,
            serverSide: true,
            mobileMirror: mobileView,
            compactView: compactViewId ? true : false,
            order: [
                [sortColumn, "desc"]
            ],
            smartFilterIdentity: "tickets_list", //a to z and _ only. should be unique to avoid conflicts
            ignoreSavedFilter: ignoreSavedFilter,
            reloadHooks: [{
                    type: "app_form",
                    id: "ticket-form"
                },
                {
                    type: "app_form",
                    id: "comment-form",
                    mapPostData: {
                        id: "ticket_id"
                    }
                },
                {
                    type: "ajax_request",
                    group: "ticket_status"
                },
                {
                    type: "app_modifier",
                    group: "ticket_info"
                },
                {
                    type: "app_table_row_update",
                    tableId: "ticket-table"
                }
            ],
            multiSelect: [{
                class: "w150",
                name: "status",
                text: "<?php echo app_lang('status'); ?>",
                options: <?php echo json_encode($statuses); ?>
            }],
            filterDropdown: filterDropdowns,
            selectionHandler: {
                batchUpdateUrl: batchUpdateUrl
            },
            singleDatepicker: [{
                name: "created_at",
                defaultText: "<?php echo app_lang('created') ?>",
                options: [{
                        value: dynamicDates.in_last_2_days,
                        text: "<?php echo sprintf(app_lang('in_last_number_of_days'), 2); ?>"
                    },
                    {
                        value: dynamicDates.in_last_7_days,
                        text: "<?php echo sprintf(app_lang('in_last_number_of_days'), 7); ?>"
                    },
                    {
                        value: dynamicDates.in_last_15_days,
                        text: "<?php echo sprintf(app_lang('in_last_number_of_days'), 15); ?>"
                    },
                    {
                        value: dynamicDates.in_last_1_month,
                        text: "<?php echo sprintf(app_lang('in_last_number_of_month'), 1); ?>"
                    },
                    {
                        value: dynamicDates.in_last_3_months,
                        text: "<?php echo sprintf(app_lang('in_last_number_of_months'), 3); ?>"
                    }
                ]
            }],
            columns: [{
                    visible: false,
                    searchable: false
                },
                {
                    visible: false,
                    searchable: false,
                    order_by: "id"
                },
                {
                    title: "<?php echo app_lang("ticket_id") ?>",
                    "iDataSort": 1,
                    "class": "w10p",
                    order_by: "id"
                },
                {
                    title: "<?php echo app_lang("title") ?>",
                    "class": "all",
                    order_by: "title"
                },
                {
                    title: "<?php echo app_lang("client") ?>",
                    "class": "",
                    order_by: "client"
                },
                {
                    title: "<?php echo app_lang("project") ?>",
                    "class": "",
                    visible: projectVisibility,
                    order_by: "project"
                },
                {
                    title: "<?php echo app_lang("ticket_type") ?>",
                    "class": "w10p",
                    order_by: "ticket_type"
                },
                {
                    title: "<?php echo app_lang("labels") ?>",
                    "class": "w5p"
                },
                {
                    title: "<?php echo app_lang("assigned_to") ?>",
                    "class": "w10p",
                    order_by: "assigned_to"
                },
                {
                    visible: false,
                    searchable: false,
                    order_by: "last_activity"
                },
                {
                    visible: false,
                    searchable: false,
                    order_by: "client_last_activity_at"
                },
                {
                    title: "<?php echo app_lang("last_activity") ?>",
                    "iDataSort": 9,
                    "class": "w10p",
                    order_by: "last_activity"
                },
                {
                    title: "<?php echo app_lang("status") ?>",
                    "class": "w5p"
                }
                <?php echo $custom_field_headers; ?>,
                {
                    title: '<i data-feather="menu" class="icon-16"></i>',
                    "class": "text-center w80",
                    visible: optionsVisibility
                }
            ],
            rowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var colorRow = 'td:eq(0)';
                if (mobileView) {
                    colorRow = 'td:eq(1)';
                }

                $(colorRow, nRow).attr("style", "border-left-color:" + aData[0] + " !important;").addClass('list-status-border');
                if (compactViewId) {
                    applyActiveRow();
                }

            },
            onInitComplete: function() {
                if (compactViewId) {
                    $("#ticket-table").wrap("<div id='ticket-table-container'></div>");
                    var windowHeight = $(window).height();
                    var tickestListHeight = 388;
                    var heightDiff = windowHeight - tickestListHeight;
                    $("#ticket-table-container").attr('style', 'min-height: ' + heightDiff + 'px !important');
                }
            },
            printColumns: combineCustomFieldsColumns([2, 3, 4, 5, 6, 8, 10, 11], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([2, 3, 4, 5, 6, 8, 10, 11], '<?php echo $custom_field_headers; ?>')
        });

        if (isMobile()) {
            $(document).on("click", "#ticket-table .box-label", function(e) {
                e.preventDefault();

                // Store the current scroll position in window object
                window.lastScrollPosition = $(window).scrollTop();
            });
        };
    });
</script>