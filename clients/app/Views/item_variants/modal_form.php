<?php echo form_open(get_uri("item_variants/save"), array("id" => "variant-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        
        <div class="form-group">
            <div class="row">
                <label for="item_id" class="<?php echo $label_column; ?>"><?php echo app_lang('item'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_dropdown("item_id", $items_dropdown, array($item_id ? $item_id : $model_info->item_id), "class='select2 validate-hidden' id='item_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="variant_type" class="<?php echo $label_column; ?>"><?php echo app_lang('variant_type'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_dropdown("variant_type", $variant_types, array($model_info->variant_type), "class='select2 validate-hidden' id='variant_type' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="variant_name" class="<?php echo $label_column; ?>"><?php echo app_lang('variant_name'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "variant_name",
                        "name" => "variant_name",
                        "value" => $model_info->variant_name,
                        "class" => "form-control",
                        "placeholder" => app_lang('variant_name'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="variant_value" class="<?php echo $label_column; ?>"><?php echo app_lang('variant_value'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "variant_value",
                        "name" => "variant_value",
                        "value" => $model_info->variant_value,
                        "class" => "form-control",
                        "placeholder" => app_lang('variant_value'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="price_modifier_type" class="<?php echo $label_column; ?>"><?php echo app_lang('price_modifier_type'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_dropdown("price_modifier_type", $price_modifier_types, array($model_info->price_modifier_type), "class='select2' id='price_modifier_type'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="price_modifier" class="<?php echo $label_column; ?>"><?php echo app_lang('price_modifier'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "price_modifier",
                        "name" => "price_modifier",
                        "value" => $model_info->price_modifier ? to_decimal_format($model_info->price_modifier) : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('price_modifier'),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="sort_order" class="<?php echo $label_column; ?>"><?php echo app_lang('sort_order'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "sort_order",
                        "name" => "sort_order",
                        "value" => $model_info->sort_order,
                        "class" => "form-control",
                        "placeholder" => app_lang('sort_order'),
                        "type" => "number"
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="status" class="<?php echo $label_column; ?>"><?php echo app_lang('status'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_checkbox("status", "1", $model_info->status ? true : false, "id='status' class='form-check-input'");
                    ?>
                    <label for="status" class="form-check-label"><?php echo app_lang('active'); ?></label>
                </div>
            </div>
        </div>

        <?php if ($model_info->id) { ?>
        <div class="form-group">
            <div class="row">
                <label for="price_change_reason" class="<?php echo $label_column; ?>"><?php echo app_lang('price_change_reason'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_textarea(array(
                        "id" => "price_change_reason",
                        "name" => "price_change_reason",
                        "class" => "form-control",
                        "placeholder" => app_lang('price_change_reason'),
                        "rows" => 3
                    ));
                    ?>
                </div>
            </div>
        </div>
        <?php } ?>

    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#variant-form").appForm({
            onSuccess: function (result) {
                $("#variant-table").appTable({newData: result.data, dataId: result.id});
            }
        });

        $("#item_id, #variant_type, #price_modifier_type").select2();
        
        $('#price_modifier').on('blur', function() {
            var type = $('#price_modifier_type').val();
            var value = $(this).val();
            
            if (type === 'percentage' && value) {
                // Ensure percentage values are reasonable
                if (parseFloat(value) > 100) {
                    $(this).val('100');
                }
            }
        });
    });
</script>