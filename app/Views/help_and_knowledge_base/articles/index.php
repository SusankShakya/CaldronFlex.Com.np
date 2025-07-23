<div id="page-content" class="page-wrapper clearfix">

    <ul class="nav nav-tabs bg-white title" role="tablist">
        <?php echo view("help_and_knowledge_base/tabs", array("active_tab" => "articles_list", "type" => $type)); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php
                echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => $type));
                echo anchor(get_uri("help/article_form/" . $type), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_article'), array("class" => "btn btn-default", "title" => app_lang('add_article')));
                ?>
            </div>
        </div>

    </ul>

    <div class="card border-top-0 rounded-top-0 xs-no-bottom-margin">
        <div class="table-responsive">
            <table id="article-table" class="display" cellspacing="0" width="100%">
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        var showFeedback = false;
        <?php if ($type == "knowledge_base" && $login_user->is_admin) { ?>
            showFeedback = true;
        <?php } ?>

        $("#article-table").appTable({
            source: '<?php echo_uri("help/articles_list_data/" . $type) ?>',
            smartFilterIdentity: "help_and_knowledge_base_articles_list",
            order: [[0, "desc"]],
            filterDropdown: [
                {name: "category_id", class: "w200", options: <?php echo $categories_dropdown; ?>},
                {name: "label_id", class: "w200", options: <?php echo $labels_dropdown; ?>},
                {name: "status", class: "w200", options: <?php echo $status_dropdown; ?>}
            ],
            columns: [
                {title: "<?php echo app_lang('title') ?>", "class": "all"},
                {title: "<?php echo app_lang('category') ?>", "class": ""},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("created_at") ?>", "iDataSort": 2, "class": ""},
                {title: "<?php echo app_lang('status') ?>", "class": ""},
                {title: "<?php echo app_lang('total_views') ?>", "class": ""},
                {visible: showFeedback, title: "<?php echo app_lang('feedback') ?>", "class": ""},
                {title: "<?php echo app_lang('sort'); ?>"},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100"}
            ],
            printColumns: [0, 1, 3, 4, 5]
        });
    });
</script>