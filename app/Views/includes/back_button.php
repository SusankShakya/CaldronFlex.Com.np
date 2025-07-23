<?php
if (isset($button_url) && $button_url) {
    $url = echo_uri($button_url);
    $class = "";
} else {
    $url = "javascript:;";
    $class = "navigate-back";
}

$button_text = isset($button_text) ? $button_text : app_lang('back');
$extra_class = isset($extra_class) ? $extra_class : "";
?>

<div class="d-sm-none">
    <a class="back-action-btn pe-auto <?php echo $class . " " . $extra_class; ?>" href="<?php echo $url; ?>"><i data-feather='chevron-left' class='icon-24 mr5 pe-auto'></i><?php echo $button_text; ?></a>
</div>