<div id="page-content" class="page-wrapper clearfix grid-button notes-grid-view">

    <ul class="nav nav-tabs bg-white title scrollable-tabs" role="tablist">
        <li class="title-tab">
            <h4 class="pl15 pt10 pr15"><?php echo app_lang('notes') . " (" . app_lang('private') . ")"; ?></h4>
        </li>

        <?php echo view("notes/tabs", array("active_tab" => "grid")); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "note")); ?>
                <?php echo modal_anchor(get_uri("notes/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_note'), array("class" => "btn btn-default", "title" => app_lang('add_note'), "id" => "add-note-button")); ?>
            </div>
        </div>

    </ul>

    <div class="card border-top-0 rounded-top-0">
        <div class="bg-white">
            <div id="notes-grid-filters"></div>
        </div>
        <div id="load-notes-grid"></div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function() {
        var filterDropdown = [{
                name: "category_id",
                class: "w200",
                options: <?php echo $note_categories_dropdown; ?>
            },
            {
                name: "label_id",
                class: "w200",
                options: <?php echo $labels_dropdown; ?>
            },
        ];

        var scrollLeft = 0;
        $("#notes-grid-filters").appFilters({
            source: '<?php echo_uri("notes/grid_data") ?>',
            targetSelector: '#load-notes-grid',
            reloadSelector: "#reload-notes-grid-button",
            smartFilterIdentity: "private_notes_grid", //a to z and _ only. should be unique to avoid conflicts 
            search: {
                name: "search"
            },
            filterDropdown: filterDropdown
        });
    });
</script>