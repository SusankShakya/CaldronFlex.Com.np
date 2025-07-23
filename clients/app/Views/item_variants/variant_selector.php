<?php if (!empty($variants)) { ?>
<div class="variant-selector-container" data-item-id="<?php echo $item_id; ?>">
    <?php foreach ($variants as $variant_type => $variant_options) { 
        $variant_type_label = ucfirst(str_replace('_', ' ', $variant_type));
    ?>
    <div class="form-group variant-group mb-3">
        <label class="form-label"><?php echo $variant_type_label; ?> <span class="text-danger">*</span></label>
        <div class="variant-options">
            <?php foreach ($variant_options as $variant) { ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input variant-option" 
                       type="radio" 
                       name="variant_<?php echo $variant_type; ?>" 
                       id="variant_<?php echo $variant->id; ?>" 
                       value="<?php echo $variant->id; ?>"
                       data-variant-id="<?php echo $variant->id; ?>"
                       data-variant-type="<?php echo $variant_type; ?>"
                       data-price-modifier="<?php echo $variant->price_modifier; ?>"
                       data-modifier-type="<?php echo $variant->price_modifier_type; ?>">
                <label class="form-check-label" for="variant_<?php echo $variant->id; ?>">
                    <?php echo $variant->variant_value; ?>
                    <?php if ($variant->price_modifier > 0) { ?>
                        <span class="text-muted">
                            (+<?php echo $variant->price_modifier_type == 'percentage' ? $variant->price_modifier . '%' : to_currency($variant->price_modifier); ?>)
                        </span>
                    <?php } ?>
                </label>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
    
    <div class="variant-price-display mt-3" style="display: none;">
        <div class="alert alert-info">
            <strong><?php echo app_lang('price'); ?>:</strong> 
            <span class="variant-final-price"></span>
        </div>
    </div>
    
    <div class="variant-stock-display mt-2" style="display: none;">
        <div class="alert alert-warning">
            <strong><?php echo app_lang('stock'); ?>:</strong> 
            <span class="variant-stock-info"></span>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var $container = $('.variant-selector-container[data-item-id="<?php echo $item_id; ?>"]');
    
    $container.find('.variant-option').on('change', function() {
        var selectedVariants = [];
        
        $container.find('.variant-option:checked').each(function() {
            selectedVariants.push($(this).val());
        });
        
        if (selectedVariants.length === $container.find('.variant-group').length) {
            // All variants selected, calculate price
            $.ajax({
                url: '<?php echo get_uri("item_variants/calculate_price"); ?>',
                type: 'POST',
                data: {
                    item_id: <?php echo $item_id; ?>,
                    variant_ids: selectedVariants
                },
                success: function(result) {
                    if (result.success) {
                        $container.find('.variant-final-price').html(result.price);
                        $container.find('.variant-price-display').show();
                        
                        if (result.in_stock) {
                            $container.find('.variant-stock-info').html(result.stock + ' <?php echo app_lang("in_stock"); ?>');
                            $container.find('.variant-stock-display').removeClass('alert-danger').addClass('alert-warning');
                        } else {
                            $container.find('.variant-stock-info').html('<?php echo app_lang("out_of_stock"); ?>');
                            $container.find('.variant-stock-display').removeClass('alert-warning').addClass('alert-danger');
                        }
                        $container.find('.variant-stock-display').show();
                        
                        // Store the calculated price for form submission
                        $container.data('calculated-price', result.price_value);
                        $container.data('selected-variants', selectedVariants);
                    }
                }
            });
        }
    });
});
</script>
<?php } ?>