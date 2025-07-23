<?php $view_type = isset($view_type) ? $view_type : ""; ?>

<div class="card client-notes-container">
    <div class="card-header fw-bold">
        <span class="d-inline-block mt-1">
            <i data-feather="book" class="icon-16"></i> &nbsp;<?php echo app_lang("notes"); ?>
        </span>

        <div class="float-end">
            <?php echo view("clients/layout_settings_dropdown", array("view_type" => $view_type, "context" => "client_details_notes")); ?>

            <?php if ($view_type != "overview") { ?>
                <?php echo modal_anchor(get_uri("notes/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_note'), array("class" => "btn btn-default", "title" => app_lang('add_note'), "data-post-client_id" => $client_id)); ?>
            <?php } ?>
        </div>
    </div>

    <?php if ($view_type === "overview") { ?>
        <div class="card-body">
            <?php echo modal_anchor(get_uri("notes/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_note'), array("title" => app_lang('add_note'), "data-post-client_id" => $client_id)); ?>
        </div>
    <?php } ?>

    <div class="table-responsive">
        <table id="client-details-page-note-table" class="display <?php echo $view_type === "overview" ? "no-thead b-t b-b-only no-hover hide-dtr-control hide-status-checkbox" : "" ?>" width="100%">
        </table>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {

        var viewType = <?php echo $view_type === "overview" ? 1 : 0 ?>;
        var hideTools = false,
            displayLength = 10,
            responsive = false,
            mobileMirror = false,
            dateColumnVisibility = true;

        if (viewType) {
            hideTools = true;
            displayLength = 100;
            responsive = true;
            mobileMirror = true;
            dateColumnVisibility = false;
        }

        $("#client-details-page-note-table").appTable({
            source: '<?php echo_uri("notes/list_data/client/" . $client_id) ?>' + '/' + viewType,
            order: [
                [0, "desc"]
            ],
            hideTools: hideTools,
            displayLength: displayLength,
            stateSave: false,
            responsive: responsive,
            mobileMirror: mobileMirror,
            reloadHooks: [{
                    type: "app_form",
                    id: "note-form",
                    filter: {
                        client_id: "<?php echo $client_id ?>"
                    },
                },
                {
                    type: "app_table_row_update",
                    tableId: "client-details-page-note-table"
                }
            ],
            columns: [{
                    targets: [1],
                    visible: false
                },
                {
                    title: '<?php echo app_lang("created_date"); ?>',
                    "class": "w200",
                    visible: dateColumnVisibility
                },
                {
                    title: '<?php echo app_lang("title"); ?>',
                    "class": "all"
                },
                {
                    visible: false,
                    searchable: false
                },
                {
                    title: '<?php echo app_lang("files") ?>'
                },
                {
                    title: '<i data-feather="menu" class="icon-16"></i>',
                    "class": "text-center option w100"
                }
            ]
        });
    });
</script>