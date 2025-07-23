<?php
$uid = "_" . uniqid(rand());

$options = $field_info->options;
$options_array = explode(",", $options);

$saved_values = isset($field_info->value) ? array_map('trim', explode(",", $field_info->value)) : [];

if ($options && count($options_array)) {
    foreach ($options_array as $value) {
        $value = trim($value);
        $isChecked = in_array($value, $saved_values);

        echo form_checkbox(array(
            "id" => $value . $uid,
            "name" => "custom_field_" . $field_info->id,
            "class" => "form-check-input validate-hidden",
            "data-rule-required" => $field_info->required ? "true" : "false",
            "data-msg-required" => app_lang("field_required"),
            "data-prepare_checkboxes_data" => "1",
        ), $value, $isChecked);

        echo '
        <label for="' . $value . $uid . '" class="mr15">' . $value . '</label>
        ';
    }
}
