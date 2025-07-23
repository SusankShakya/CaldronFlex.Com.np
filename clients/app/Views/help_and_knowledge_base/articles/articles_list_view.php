<?php if (!$category_info->banner_image) { ?>
    <h2 class="mb20 pb20 b-b"> <?php echo $category_info->title; ?></h2>
<?php } ?>

<nav aria-label="breadcrumb" class="help-breadcrumb pb10">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo get_uri($category_info->type); ?>"><i data-feather='home' class='icon-14'></i></a></li>
        <li class="breadcrumb-item"><?php echo $category_info->title; ?></li>

        <li class="ms-auto">
            <?php
            if (isset($can_manage_help_and_kb) && $can_manage_help_and_kb) {
                echo anchor(get_uri("help/" . $type . "_articles"), "<i data-feather='book-open' class='icon-16'></i> " . app_lang('articles'), array("class" => "btn btn-default round ml5 mr5", "title" => app_lang('articles')));
                echo anchor(get_uri("help/" . $type . "_categories"), "<i data-feather='codepen' class='icon-16'></i> " . app_lang('categories'), array("class" => "btn btn-default round ml5 mr5 ", "title" => app_lang('categories')));
                echo modal_anchor(get_uri("help/category_modal_form/" . $category_info->type), "<i data-feather='edit' class='icon-16'></i>", array("class" => "action-option", "title" => app_lang('edit_category'), "data-post-id" => $category_info->id));
            }
            ?>
        </li>
    </ol>
</nav>


<p class="mb20"><?php echo custom_nl2br($category_info->description ? process_images_from_content($category_info->description) : ""); ?></p>
<?php
foreach ($articles as $article) {

    echo anchor(get_uri($category_info->type . "/view/" . $article->id . generate_slug_from_title($article->title)), "<i data-feather='file-text' class='icon-16 mr15'></i>" . $article->title, array("class" => "list-group-item"));
}
?>
<div class="mb20"></div>

<?php if ($related_articles) { ?>
    <div class="mb20 mt20"><?php echo app_lang("related_articles"); ?>: </div>
    <?php
    foreach ($related_articles as $related_article) {

        echo anchor(get_uri($category_info->type . "/view/" . $related_article->id . generate_slug_from_title($related_article->title)), "<i data-feather='file-text' class='icon-16 mr15'></i>" . $related_article->title, array("class" => "list-group-item"));
    }
    ?>
    <div class="mb20"></div>
<?php } ?>