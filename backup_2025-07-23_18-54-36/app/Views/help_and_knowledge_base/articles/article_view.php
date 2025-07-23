<h2 class="mb20 pb20 b-b"> <?php echo $article_info->title; ?></h2>
<nav aria-label="breadcrumb" class="help-breadcrumb pb10">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo get_uri($article_info->type); ?>"><i data-feather='home' class='icon-14'></i></a></li>
        <li class="breadcrumb-item"><a href="<?php echo get_uri($article_info->type . "/category/" . $article_info->category_id); ?>"><?php echo $article_info->category_title; ?></a></li>
        <li class="breadcrumb-item"><?php echo $article_info->title; ?></li>

        <li class="ms-auto">
            <?php
            if (isset($can_manage_help_and_kb) && $can_manage_help_and_kb) {
                echo anchor(get_uri("help/" . $type . "_articles"), "<i data-feather='book-open' class='icon-16'></i> " . app_lang('articles'), array("class" => "btn btn-default round ml5 mr5", "title" => app_lang('articles')));
                echo anchor(get_uri("help/article_form/" . $type . "/" . $article_info->id), "<i data-feather='edit' class='icon-16'></i>", array("class" => "action-option", "title" => app_lang('edit_article')));
            }
            ?>
        </li>

    </ol>
</nav>

<?php
$category_class = str_replace(' ', '-', strtolower($article_info->category_title));
?>

<div id="help-page-view-content-area" class="article-category-<?php echo $category_class; ?> <?php echo $article_label_classes; ?>">
    <div>
        <?php echo process_images_from_content($article_info->description); ?>
    </div>
    <div class="mt20 mb20">
        <?php
        if ($article_info->files) {
            $files = unserialize($article_info->files);
            $total_files = count($files);
            echo view("includes/timeline_preview", array("files" => $files));

            if ($total_files) {
                $download_caption = app_lang('download');
                if ($total_files > 1) {
                    $download_caption = sprintf(app_lang('download_files'), $total_files);
                }
                echo anchor(get_uri($article_info->type . "/download_files/" . $article_info->id), $download_caption, array("class" => "", "title" => $download_caption));
            }
        }
        ?>
    </div>
</div>

<?php if ($article_info->type == "knowledge_base") { ?>
    <?php if (!$article_info->article_helpful_status) { ?>
        <div class="b-t">
            <div class="feedback-section mt-3">
                <div class="mb-2"><?php echo app_lang("was_this_article_helpful"); ?></div>

                <?php echo js_anchor("<i data-feather='check-circle' class='icon-16'></i> " . app_lang('yes'), array("class" => "btn btn-success mr5 article_vote_button", "data-action-url" => get_uri($article_info->type . "/article_helpful_status/" . $article_info->id . "/yes"), "data-article-id" => "$article_info->id", "data-feedback-status" => "yes")); ?>
                <?php echo js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang('no'), array("class" => "btn btn-default article_vote_button", "data-action-url" => get_uri($article_info->type . "/article_helpful_status/" . $article_info->id . "/no"), "data-article-id" => "$article_info->id", "data-feedback-status" => "no")); ?>
            </div>
        </div>
    <?php } ?>
<?php } ?>

<?php
if ($article_info->description && preg_match('/<textarea[^>]*class=["\'][^"\']*xml-code-view[^"\']*["\'][^>]*>/i', $article_info->description) === 1) {
    load_css(array(
        "assets/js/codemirror/codemirror.min.css",
        "assets/js/codemirror/material.min.css",

    ));

    load_js(array(
        "assets/js/codemirror/codemirror.min.js",
        "assets/js/codemirror/xml.min.js",
        "assets/js/codemirror/matchbrackets.min.js",
        "assets/js/codemirror/closebrackets.min.js",
    ));
?>
    <script type="text/javascript">
        $(document).ready(function() {
            $("textarea.xml-code-view").each(function() {
                var editor = CodeMirror.fromTextArea(this, {
                    mode: "xml",
                    theme: "material",
                    lineNumbers: true,
                    matchBrackets: true,
                    autoCloseBrackets: true,
                    readOnly: true
                });
                editor.setSize(null, "500px");
            });
        });
    </script>
<?php
}

?>
<script type="text/javascript">
    var slag = "<?php echo generate_slug_from_title($article_info->title); ?>";


    let currentUrl = window.location.href;
    let urlPattern = /(\/view\/\d+)(-[^/]*)?$/; // Matches "/view/{id}" with or without slug

    if (slag && history && urlPattern.test(currentUrl)) {
        let match = currentUrl.match(urlPattern);
        let baseUrl = match[1]; // "/view/3"
        let existingSlug = match[2]; // "-some-title-here" (if exists)

        if (!existingSlug) {
            let newUrl = currentUrl.replace(urlPattern, baseUrl + slag);
            history.replaceState(null, "", newUrl);
        }
    }


    $(document).ready(function() {
        //load message notifications
        $("#help-page-view-content-area").css({
            "min-height": $(window).height() - 370 + "px"
        });
    });
</script>