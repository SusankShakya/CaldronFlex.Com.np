<?php echo form_open(get_uri("item_variants/save_combination"), array("id" => "combination-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info ? $model_info->id : ''; ?>" />
        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>" />
        
        <?php if (!empty($variants)) { ?>
            <div class="form-group">
                <label class="col-md-12 mb-2"><strong><?php echo app_lang('select_variants'); ?></strong></label>
                <?php 
                $selected_variants = $model_info && $model_info->variant_combination ? json_decode($model_info->variant_combination) : array();
                foreach ($variants as $variant_type => $variant_options) { 
                    $variant_type_label = ucfirst(str_replace('_', ' ', $variant_type));
                ?>
                <div class="form-group">
                    <div class="row">
                        <label class="<?php echo $label_column; ?>"><?php echo $variant_type_label; ?></label>
                        <div class="<?php echo $field_column; ?>">
                            <select name="variants[]" class="form-control select2 variant-select" data-rule-required="true" data-msg-required="<?php echo app_lang('field_required'); ?>">
                                <option value="">- <?php echo app_lang('select'); ?> -</option>
                                <?php foreach ($variant_options as $variant) { ?>
                                    <option value="<?php echo $variant->id; ?>" 
                                        <?php echo in_array($variant->id, $selected_variants) ? 'selected' : ''; ?>
                                        data-price-modifier="<?php echo $variant->price_modifier; ?>"
                                        data-modifier-type="<?php echo $variant->price_modifier_type; ?>">
                                        <?php echo $variant->variant_value; ?>
                                        <?php if ($variant->price_modifier > 0) { ?>
                                            (+<?php echo $variant->price_modifier_type == 'percentage' ? $variant->price_modifier . '%' : to_currency($variant->price_modifier); ?>)
                                        <?php } ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="form-group">
            <div class="row">
                <label for="sku" class="<?php echo $label_column; ?>"><?php echo app_lang('sku'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "sku",
                        "name" => "sku",
                        "value" => $model_info ? $model_info->sku : '',
                        "class" => "form-control",
                        "placeholder" => app_lang('sku'),
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
                <label for="additional_price" class="<?php echo $label_column; ?>"><?php echo app_lang('additional_price'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "additional_price",
                        "name" => "additional_price",
                        "value" => $model_info && $model_info->additional_price ? to_decimal_format($model_info->additional_price) : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('additional_price'),
                    ));
                    ?>
                    <small class="form-text text-muted"><?php echo app_lang('combination_price_help'); ?></small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="stock_quantity" class="<?php echo $label_column; ?>"><?php echo app_lang('stock_quantity'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "stock_quantity",
                        "name" => "stock_quantity",
                        "value" => $model_info ? $model_info->stock_quantity : "0",
                        "class" => "form-control",
                        "placeholder" => app_lang('stock_quantity'),
                        "type" => "number",
                        "min" => "0"
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
                    $status = $model_info ? $model_info->status : 1;
                    echo form_checkbox("status", "1", $status ? true : false, "id='status' class='form-check-input'");
                    ?>
                    <label for="status" class="form-check-label"><?php echo app_lang('active'); ?></label>
                </div>
            </div>
        </div>

        <?php if ($model_info && $model_info->id) { ?>
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
        $("#combination-form").appForm({
            onSuccess: function (result) {
                if (result.success) {
                    $("#combination-table").appTable({reload: true});
                }
            }
        });

        $(".variant-select").select2();
        
        // Calculate total price modifier when variants are selected
        $('.variant-select').on('change', function() {
            var totalModifier = 0;
            $('.variant-select').each(function() {
                var selected = $(this).find('option:selected');
                if (selected.val()) {
                    var modifier = parseFloat(selected.data('price-modifier')) || 0;
                    var modifierType = selected.data('modifier-type');
                    
                    if (modifierType === 'fixed') {
                        totalModifier += modifier;
                    }
                    // Note: Percentage modifiers would need base price to calculate
                }
            });
            
            // Update the additional price field as a suggestion
            if (!$('#additional_price').val() || confirm('<?php echo app_lang("update_combination_price"); ?>')) {
                $('#additional_price').val(totalModifier.toFixed(2));
            }
        });
    });
</script>