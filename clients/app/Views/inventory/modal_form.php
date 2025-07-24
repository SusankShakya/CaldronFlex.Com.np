<?php echo form_open(get_uri("inventory/save"), array("id" => "inventory-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        
        <div class="form-group">
            <div class="row">
                <label for="item_id" class="<?php echo $label_column; ?>"><?php echo app_lang('item'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_dropdown("item_id", $items_dropdown, array($model_info->item_id), "class='select2 validate-hidden' id='item_id' data-rule-required='true' data-msg-required='" . app_lang('field_required') . "'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="warehouse_id" class="<?php echo $label_column; ?>"><?php echo app_lang('warehouse'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_dropdown("warehouse_id", $warehouses_dropdown, array($model_info->warehouse_id), "class='select2 validate-hidden' id='warehouse_id' data-rule-required='true' data-msg-required='" . app_lang('field_required') . "'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="current_stock" class="<?php echo $label_column; ?>"><?php echo app_lang('current_stock'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "current_stock",
                        "name" => "current_stock",
                        "value" => $model_info->current_stock ? $model_info->current_stock : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('current_stock'),
                        "type" => "number",
                        "min" => "0",
                        "step" => "0.01",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="reserved_stock" class="<?php echo $label_column; ?>"><?php echo app_lang('reserved_stock'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "reserved_stock",
                        "name" => "reserved_stock",
                        "value" => $model_info->reserved_stock ? $model_info->reserved_stock : "0",
                        "class" => "form-control",
                        "placeholder" => app_lang('reserved_stock'),
                        "type" => "number",
                        "min" => "0",
                        "step" => "0.01"
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="reorder_level" class="<?php echo $label_column; ?>"><?php echo app_lang('reorder_level'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "reorder_level",
                        "name" => "reorder_level",
                        "value" => $model_info->reorder_level ? $model_info->reorder_level : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('reorder_level'),
                        "type" => "number",
                        "min" => "0",
                        "step" => "0.01",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="unit_of_measure" class="<?php echo $label_column; ?>"><?php echo app_lang('unit_of_measure'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "unit_of_measure",
                        "name" => "unit_of_measure",
                        "value" => $model_info->unit_of_measure,
                        "class" => "form-control",
                        "placeholder" => app_lang('unit_of_measure')
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="notes" class="<?php echo $label_column; ?>"><?php echo app_lang('notes'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_textarea(array(
                        "id" => "notes",
                        "name" => "notes",
                        "value" => $model_info->notes,
                        "class" => "form-control",
                        "placeholder" => app_lang('notes'),
                        "rows" => 3
                    ));
                    ?>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#inventory-form").appForm({
            onSuccess: function (result) {
                $("#inventory-table").appTable({newData: result.data, dataId: result.id});
            }
        });

        $("#item_id").select2();
        $("#warehouse_id").select2();
    });
</script>