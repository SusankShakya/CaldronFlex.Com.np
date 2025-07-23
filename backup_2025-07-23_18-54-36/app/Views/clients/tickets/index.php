<?php
$page_type = isset($page_type) && $page_type === "full" ? "full" : "";
$view_type = isset($view_type) && $view_type === "overview" ? "overview" : "";

$is_full_page = $page_type === "full";
$is_overview = $view_type === "overview";

$table_wrapper_classes = (!$is_full_page && $is_overview) ? 'no-display-length client-view-tickets-card' : '';
?>

<?php if ($page_type === "full") { ?>
    <div id="page-content" class="page-wrapper clearfix">
    <?php } ?>

    <div class="card">
        <?php if ($page_type === "full") { ?>
            <div class="page-title clearfix">
                <h1><?php echo app_lang('tickets'); ?></h1>
                <div class="title-button-group">
                    <?php echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_ticket'), array("class" => "btn btn-default", "data-post-client_id" => $client_id, "title" => app_lang('add_ticket'))); ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="card-header">
                <span class="fw-bold"><i data-feather="life-buoy" class="icon-16"></i> &nbsp;<?php echo app_lang("tickets"); ?></span>
                <div class="float-end">
                    <?php
                    $button_class = $view_type != 'overview' ? 'btn btn-default mr5' : 'mr5';
                    echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_ticket'), array("class" => $button_class, "data-post-client_id" => $client_id, "title" => app_lang('add_ticket')));

                    echo view("clients/layout_settings_dropdown", array("view_type" => $view_type, "context" => "client_details_tickets")); ?>
                </div>
            </div>
        <?php } ?>

        <div class="table-responsive <?php echo $table_wrapper_classes; ?>">
            <table id="ticket-table" class="display xs-hide-dtr-control no-title <?php echo $is_overview ? 'no-thead hide-dtr-control' : ''; ?>" width="100%">
            </table>
        </div>
    </div>
    <?php if ($page_type === "full") { ?>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function() {
        var userType = "<?php echo $login_user->user_type; ?>";

        var projectVisibility = false;
        if ("<?php echo $show_project_reference; ?>" == "1") {
            projectVisibility = true;
        }

        var radioButtons = [];
        if (userType === "staff") {
            radioButtons.push({
                text: '<?php echo app_lang("open") ?>',
                name: "status",
                value: "open",
                isChecked: true
            }, {
                text: '<?php echo app_lang("closed") ?>',
                name: "status",
                value: "closed"
            });
        }

        var pageType = "<?php echo isset($page_type) && $page_type === 'full' ? 'full' : ''; ?>";
        var viewType = "<?php echo isset($view_type) && $view_type === 'overview' ? 'overview' : ''; ?>";

        var isMobileDevice = isMobile();
        var isOverview = viewType === "overview";
        var isWidgetView = isMobileDevice || isOverview;
        var isFullPage = pageType === "full";

        var url = isWidgetView ?
            "<?php echo get_uri("tickets/ticket_list_data_of_client") . "/" . $client_id . "/0/1/widget"; ?>" :
            "<?php echo get_uri("tickets/ticket_list_data_of_client/" . $client_id); ?>";

        var filterDropdown = !isOverview ? [<?php echo $custom_field_filters_of_tickets; ?>] : [];

        var mobileMirror = isWidgetView;
        var stateSave = !isWidgetView;
        var responsive = isWidgetView;

        $("#ticket-table").appTable({
            source: url,
            order: [
                [9, "desc"]
            ],
            radioButtons: radioButtons,
            filterDropdown: filterDropdown,
            stateSave: stateSave,
            responsive: responsive,
            mobileMirror: mobileMirror,
            reloadHooks: [{
                type: "app_form",
                id: "ticket-form"
            }],
            columns: [{
                    visible: false,
                    searchable: false
                },
                {
                    visible: false,
                    searchable: false
                },
                {
                    title: "<?php echo app_lang("ticket_id") ?>",
                    "iDataSort": 1,
                    "class": "w10p"
                },
                {
                    title: "<?php echo app_lang("title") ?>",
                    "class": "all"
                },
                {
                    visible: false,
                    searchable: false
                },
                {
                    title: "<?php echo app_lang("project") ?>",
                    "class": "w15p",
                    visible: projectVisibility
                },
                {
                    title: "<?php echo app_lang("ticket_type") ?>",
                    "class": "w15p"
                },
                {
                    title: "<?php echo app_lang("labels") ?>",
                    visible: userType == "staff" ? true : false
                }, //show only to team members
                {
                    title: "<?php echo app_lang("assigned_to") ?>",
                    visible: userType == "staff" ? true : false
                }, //show only to team members
                {
                    visible: false,
                    searchable: false
                },
                {
                    visible: false,
                    searchable: false
                },
                {
                    title: "<?php echo app_lang("last_activity") ?>",
                    "iDataSort": 9,
                    "class": "w15p"
                },
                {
                    title: "<?php echo app_lang("status") ?>"
                }
                <?php echo $custom_field_headers_of_tickets; ?>
            ],
            rowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var colorRow = 'td:eq(0)';
                if (isWidgetView) {
                    colorRow = 'td:eq(1)';
                }

                $(colorRow, nRow).attr("style", "border-left-color:" + aData[0] + " !important;").addClass('list-status-border');
            },
        });
    });
</script>