<?php
$fields = "";
if (isset($contract_info->custom_fields) && $contract_info->custom_fields) {
    foreach ($contract_info->custom_fields as $field) {
        if ($field->value) {
            $fields .= "<li class='list-group-item first-child-no-top-style'><div class='d-flex'><span class='mr5'>"
                . view("custom_fields/render_icon", array("field_type" => $field->custom_field_type)) . "</span><span class='flex-fill'><strong>$field->custom_field_title: </strong>"
                . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value))
                . "</span></div></li>";
        }
    }
}

if ($fields) {
?>
    <div class="card">
        <div class="card-body">
            <ul class="list-group info-list pt0">
                <?php echo $fields; ?>
            </ul>
        </div>
    </div>
<?php } ?>