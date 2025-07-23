<div id="page-content" class="page-wrapper clearfix grid-button notes-list-view">

    <ul class="nav nav-tabs bg-white title scrollable-tabs" role="tablist">
        <li class="title-tab">
            <h4 class="pl15 pt10 pr15"><?php echo app_lang('notes') . " (" . app_lang('private') . ")"; ?></h4>
        </li>

        <?php echo view("notes/tabs", array("active_tab" => "list")); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "note")); ?>
                <?php echo modal_anchor(get_uri("notes/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_note'), array("class" => "btn btn-default", "title" => app_lang('add_note'))); ?>
            </div>
        </div>

    </ul>

    <div class="card border-top-0 rounded-top-0">
        <div class="table-responsive pb50">
            <table id="note-table" class="display" cellspacing="0" width="100%">
            </table>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#note-table").appTable({
            source: '<?php echo_uri("notes/list_data") ?>',
            smartFilterIdentity: "private_notes_list", //a to z and _ only. should be unique to avoid conflicts
            order: [[0, 'desc']],
            filterDropdown: [
                {name: "category_id", class: "w200", options: <?php echo $note_categories_dropdown; ?>},
                {name: "label_id", class: "w200", options: <?php echo $labels_dropdown; ?>},
            ],
            columns: [
                {targets: [1], visible: false},
                {title: '<?php echo app_lang("created_date"); ?>', "class": "w200"},
                {title: '<?php echo app_lang("title"); ?>', "class": "all"},
                {title: '<?php echo app_lang("category") ?>'},
                {title: '<?php echo app_lang("files") ?>', "class": "w250"},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100"}
            ]
        });
    });
</script>