<div class="card lead-notes-container">
    <div class="card-header fw-bold">
        <i data-feather="book" class="icon-16"></i> &nbsp;<?php echo app_lang("notes"); ?>
    </div>
    <div class="card-body">
        <?php echo modal_anchor(get_uri("notes/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_note'), array("class" => "", "title" => app_lang('add_note'), "data-post-client_id" => $client_id)); ?>
    </div>

    <div class="table-responsive">
        <table id="lead-details-page-note-table" class="display no-thead b-t b-b-only no-hover hide-dtr-control" cellspacing="0" width="100%">
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#lead-details-page-note-table").appTable({
            source: '<?php echo_uri("notes/list_data/client/" . $client_id) ?>' + '/1',
            order: [[0, "desc"]],
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            reloadHooks: [{
                    type: "app_form",
                    id: "note-form",
                    filter: {client_id: "<?php echo $client_id ?>"},
                },
                {
                    type: "app_table_row_update",
                    tableId: "lead-details-page-note-table"
                }
            ],
            columns: [
                {targets: [1], visible: false},
                {title: '<?php echo app_lang("created_date"); ?>', "class": "w200"},
                {title: '<?php echo app_lang("title"); ?>', "class": "all"},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("files") ?>', "class": "w250"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });
    });
</script>