<?php
$label_column = isset($label_column) ? $label_column : "col-md-3";
$field_column = isset($field_column) ? $field_column : "col-md-9";

$return_only_field = isset($return_only_field) && $return_only_field == "1" ? true : false;

foreach ($custom_fields as $field) {

    $title = "";
    if ($field->title_language_key) {
        $title = app_lang($field->title_language_key);
    } else {
        $title = $field->title;
    }

    $placeholder = "";
    if ($field->placeholder_language_key) {
        $placeholder = app_lang($field->placeholder_language_key);
    } else {
        $placeholder = $field->placeholder;
    }

    if ($return_only_field) {
        $field_type = $field->field_type;
        if ($field_type == "multi_select") {
            $field_type = "checkboxes";
        }

        $field_html =  view("custom_fields/input_" . $field_type, array("field_info" => $field, "placeholder" => $placeholder, "return_only_field" => 1));

        $field_html = preg_replace('/\s*class\s*=\s*"[^"]*"/i', '', $field_html); // Remove class attributes
        $field_html = preg_replace('/\s*data-rule-email(\s*=\s*([\'"])?[^\'"]*\\2)?/i', '', $field_html);  //Remove data-rule-email tag

        $field_html = preg_replace('/\s*data-rule-required\s*=\s*([\'"])[^\'"]*\1/i', '', $field_html);  // Replace data-rule-required with required="required"

        $field_html = preg_replace('/\s*data-msg-required\s*=\s*"[^"]*"/i', '', $field_html); // Remove data-msg-required attributes
        $field_html = preg_replace('/\s*data-msg-email\s*=\s*"[^"]*"/i', '', $field_html); // Remove data-msg-email attributes

        $field_html = preg_replace('/\s*placeholder\s*=\s*([\'"])\s*\1/i', '', $field_html); // Remove placeholder if it's empty (placeholder='' or placeholder="")

        echo "
        <div><!-- Custom field: $title -->
            $field_html
        </div>
        ";

        continue;
    }
?>
    <div class="form-group " data-field-type="<?php echo $field->field_type; ?>">
        <div class="row">
            <label for="custom_field_<?php echo $field->id ?>" class="<?php echo $label_column; ?>"><?php echo $title; ?></label>

            <div class="<?php echo $field_column; ?>">
                <?php
                if ($field->disable_editing_by_clients && (!isset($login_user->user_type) || $login_user->user_type == "client")) {
                    //for clients, if the 'Disable editing by clients' setting is enabled
                    //show the output instead of input
                    echo view("custom_fields/output_" . $field->field_type, array("value" => $field->value));
                } else {
                    echo view("custom_fields/input_" . $field->field_type, array("field_info" => $field, "placeholder" => $placeholder));
                }
                ?>
            </div>
        </div>
    </div>
<?php } ?>