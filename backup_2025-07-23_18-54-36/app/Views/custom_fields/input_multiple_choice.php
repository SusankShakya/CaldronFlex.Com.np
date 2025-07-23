<?php
$uid = "_" . uniqid(rand());

$options = $field_info->options;
$options_array = explode(",", $options);

if ($options && count($options_array)) {
    foreach ($options_array as $value) {
        $value = trim($value);

        echo form_radio(array(
            "id" => $value . $uid,
            "name" => "custom_field_" . $field_info->id,
            "class" => "form-check-input validate-hidden",
            "data-rule-required" =>  $field_info->required ? "true" : "false",
            "data-msg-required" => app_lang("field_required"),
        ), $value, (isset($field_info->value) && $field_info->value === $value) ? true : false);

        echo '
        <label for="' . $value . $uid . '" class="mr15">' . $value . '</label>
        ';
    }
}
