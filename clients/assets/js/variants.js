/**
 * Product Variants Management JavaScript
 * For CaldronFlex Printing Business
 */

var Variants = {
    
    /**
     * Initialize variant management functionality
     */
    init: function() {
        this.bindEvents();
        this.initVariantSelector();
    },
    
    /**
     * Bind event handlers
     */
    bindEvents: function() {
        // Handle variant type changes
        $(document).on('change', '.variant-type-selector', function() {
            Variants.updateVariantOptions($(this));
        });
        
        // Handle variant selection for price calculation
        $(document).on('change', '.variant-option-selector', function() {
            var container = $(this).closest('.variant-selection-container');
            Variants.calculateVariantPrice(container);
        });
        
        // Handle combination form submission
        $(document).on('submit', '#variant-combination-form', function(e) {
            e.preventDefault();
            Variants.saveCombination($(this));
        });
        
        // Handle stock update
        $(document).on('blur', '.variant-stock-input', function() {
            Variants.updateStock($(this));
        });
    },
    
    /**
     * Initialize variant selector for item forms
     */
    initVariantSelector: function() {
        $('.item-variant-selector').each(function() {
            var itemId = $(this).data('item-id');
            if (itemId) {
                Variants.loadVariantSelector($(this), itemId);
            }
        });
    },
    
    /**
     * Load variant selector via AJAX
     */
    loadVariantSelector: function(container, itemId) {
        $.ajax({
            url: AppHelper.baseUrl + 'item_variants/get_variant_selector',
            type: 'POST',
            data: {item_id: itemId},
            success: function(result) {
                if (result.success) {
                    container.html(result.html);
                    container.find('.select2').select2();
                }
            }
        });
    },
    
    /**
     * Calculate price based on selected variants
     */
    calculateVariantPrice: function(container) {
        var itemId = container.data('item-id');
        var selectedVariants = [];
        
        container.find('.variant-option:checked').each(function() {
            selectedVariants.push($(this).val());
        });
        
        if (selectedVariants.length === 0) {
            return;
        }
        
        $.ajax({
            url: AppHelper.baseUrl + 'item_variants/calculate_price',
            type: 'POST',
            data: {
                item_id: itemId,
                variant_ids: selectedVariants
            },
            success: function(result) {
                if (result.success) {
                    container.find('.variant-price-display').html(result.price);
                    container.find('.variant-stock-status').html(
                        result.in_stock ? 
                        '<span class="badge bg-success">' + AppLang.in_stock + '</span>' : 
                        '<span class="badge bg-danger">' + AppLang.out_of_stock + '</span>'
                    );
                    
                    // Update hidden fields for form submission
                    container.find('input[name="calculated_price"]').val(result.price_value);
                    container.find('input[name="selected_variants"]').val(JSON.stringify(selectedVariants));
                }
            }
        });
    },
    
    /**
     * Save variant combination
     */
    saveCombination: function(form) {
        var data = form.serialize();
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            success: function(result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 3000});
                    
                    // Refresh combination table if exists
                    if ($('#combination-table').length) {
                        $('#combination-table').DataTable().ajax.reload();
                    }
                    
                    // Close modal if in modal
                    if (form.closest('.modal').length) {
                        form.closest('.modal').modal('hide');
                    }
                } else {
                    appAlert.error(result.message);
                }
            }
        });
    },
    
    /**
     * Update variant stock
     */
    updateStock: function(input) {
        var combinationId = input.data('combination-id');
        var newStock = input.val();
        
        $.ajax({
            url: AppHelper.baseUrl + 'item_variants/update_stock',
            type: 'POST',
            data: {
                combination_id: combinationId,
                stock_quantity: newStock
            },
            success: function(result) {
                if (result.success) {
                    appAlert.success(AppLang.stock_updated, {duration: 2000});
                }
            }
        });
    },
    
    /**
     * Generate SKU for combination
     */
    generateSKU: function(itemCode, variantValues) {
        var sku = itemCode;
        variantValues.forEach(function(value) {
            sku += '-' + value.substring(0, 3).toUpperCase();
        });
        return sku;
    },
    
    /**
     * Validate variant selection
     */
    validateVariantSelection: function(container) {
        var requiredGroups = container.find('.variant-group[data-required="true"]');
        var isValid = true;
        
        requiredGroups.each(function() {
            if (!$(this).find('.variant-option:checked').length) {
                isValid = false;
                $(this).addClass('has-error');
            } else {
                $(this).removeClass('has-error');
            }
        });
        
        return isValid;
    },
    
    /**
     * Handle variant import from CSV
     */
    importVariants: function(file) {
        var formData = new FormData();
        formData.append('file', file);
        
        $.ajax({
            url: AppHelper.baseUrl + 'item_variants/import',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(result) {
                if (result.success) {
                    appAlert.success(result.message);
                    // Refresh variant table
                    if ($('#variant-table').length) {
                        $('#variant-table').DataTable().ajax.reload();
                    }
                } else {
                    appAlert.error(result.message);
                }
            }
        });
    },
    
    /**
     * Export variants to CSV
     */
    exportVariants: function(itemId) {
        window.location.href = AppHelper.baseUrl + 'item_variants/export?item_id=' + itemId;
    },
    
    /**
     * Bulk update variant prices
     */
    bulkUpdatePrices: function(variantIds, priceModifier, modifierType) {
        $.ajax({
            url: AppHelper.baseUrl + 'item_variants/bulk_update_prices',
            type: 'POST',
            data: {
                variant_ids: variantIds,
                price_modifier: priceModifier,
                modifier_type: modifierType
            },
            success: function(result) {
                if (result.success) {
                    appAlert.success(result.message);
                    // Refresh variant table
                    if ($('#variant-table').length) {
                        $('#variant-table').DataTable().ajax.reload();
                    }
                }
            }
        });
    },
    
    /**
     * Clone variant configuration
     */
    cloneVariantConfig: function(fromItemId, toItemId) {
        $.ajax({
            url: AppHelper.baseUrl + 'item_variants/clone_configuration',
            type: 'POST',
            data: {
                from_item_id: fromItemId,
                to_item_id: toItemId
            },
            success: function(result) {
                if (result.success) {
                    appAlert.success(result.message);
                    location.reload();
                }
            }
        });
    }
};

// Initialize on document ready
$(document).ready(function() {
    Variants.init();
});

// Helper functions for variant management
var VariantHelpers = {
    /**
     * Format price with currency
     */
    formatPrice: function(price, currencySymbol) {
        currencySymbol = currencySymbol || '$';
        return currencySymbol + parseFloat(price).toFixed(2);
    },
    
    /**
     * Calculate percentage modifier
     */
    calculatePercentage: function(basePrice, percentage) {
        return basePrice * (percentage / 100);
    },
    
    /**
     * Validate SKU format
     */
    validateSKU: function(sku) {
        // SKU should be alphanumeric with hyphens allowed
        var pattern = /^[A-Za-z0-9\-]+$/;
        return pattern.test(sku);
    },
    
    /**
     * Sort variants by type and order
     */
    sortVariants: function(variants) {
        return variants.sort(function(a, b) {
            if (a.variant_type !== b.variant_type) {
                return a.variant_type.localeCompare(b.variant_type);
            }
            return a.sort_order - b.sort_order;
        });
    }
};

// Export for use in other modules
window.Variants = Variants;
window.VariantHelpers = VariantHelpers;