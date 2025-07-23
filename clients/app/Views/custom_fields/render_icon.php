<?php if (isset($field_type) && $field_type) {
    $icon = "hexagon";

    if ($field_type == "textarea") {
        $icon = "message-square";
    } else if ($field_type == "email") {
        $icon = "mail";
    } else if ($field_type == "date") {
        $icon = "calendar";
    } else if ($field_type == "time") {
        $icon = "clock";
    } else if ($field_type == "number") {
        $icon = "hash";
    } else if ($field_type == "external_link") {
        $icon = "external-link";
    } else if ($field_type == "select" || $field_type == "multi_select" || $field_type == "multiple_choice" || $field_type == "checkboxes") {
        $icon = "square";
    }
?>
    <i data-feather="<?php echo $icon; ?>" class="icon-16"></i>
<?php } ?>