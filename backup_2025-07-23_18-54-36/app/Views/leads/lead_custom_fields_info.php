<?php
$fields = "";
if (count($custom_fields_list)) {
    foreach ($custom_fields_list as $data) {
        if ($data->value) {
            $fields .= "<li class='list-group-item first-child-no-top-style'><div class='d-flex'><span class='mr5'>"
                . view("custom_fields/render_icon", array("field_type" => $data->field_type)) . "</span><span class='flex-fill'><strong>$data->title: </strong>"
                . view("custom_fields/output_" . $data->field_type, array("value" => $data->value))
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