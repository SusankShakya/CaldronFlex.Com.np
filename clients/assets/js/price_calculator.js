/**
 * Price Calculator JavaScript Module
 * Handles real-time price calculations and UI updates
 */

var PriceCalculator = (function() {
    'use strict';
    
    // Module configuration
    var config = {
        endpoints: {
            calculate: AppHelper.settings.baseUrl + 'index.php/items/calculate_price',
            variants: AppHelper.settings.baseUrl + 'index.php/items/get_variant_price'
        },
        selectors: {
            form: '#price-calculator-form',
            itemSelect: '#item_id',
            clientSelect: '#client_id',
            quantityInput: '#quantity',
            areaInput: '#area',
            dateInput: '#date',
            variantSelects: '.variant-select',
            priceDisplay: '#calculated-price',
            priceRange: '#price-range',
            exactPrice: '#exact-price',
            finalPriceInput: '#final_price',
            calculateBtn: '#calculate-price-btn',
            loadingIndicator: '.price-loading',
            errorMessage: '.price-error'
        },
        debounceDelay: 500
    };
    
    // State management
    var state = {
        isCalculating: false,
        lastCalculation: null,
        selectedVariants: {},
        areaCalculator: null
    };
    
    /**
     * Initialize the price calculator
     */
    function init() {
        bindEvents();
        initializeAreaCalculator();
        loadInitialData();
    }
    
    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Form field changes
        $(config.selectors.itemSelect).on('change', handleItemChange);
        $(config.selectors.clientSelect).on('change', calculatePrice);
        $(config.selectors.quantityInput).on('input', debounce(calculatePrice, config.debounceDelay));
        $(config.selectors.areaInput).on('input', debounce(calculatePrice, config.debounceDelay));
        $(config.selectors.dateInput).on('change', calculatePrice);
        
        // Variant selections
        $(document).on('change', config.selectors.variantSelects, handleVariantChange);
        
        // Calculate button
        $(config.selectors.calculateBtn).on('click', function(e) {
            e.preventDefault();
            calculatePrice(true);
        });
        
        // Final price adjustment (staff only)
        $(config.selectors.finalPriceInput).on('input', function() {
            updatePriceDisplay();
        });
    }
    
    /**
     * Initialize area calculator integration
     */
    function initializeAreaCalculator() {
        if (typeof AreaCalculator !== 'undefined') {
            state.areaCalculator = new AreaCalculator('area-calculator-container');
            
            // Listen for area updates from visual calculator
            $(document).on('areaCalculated', function(e, area) {
                $(config.selectors.areaInput).val(area).trigger('input');
            });
        }
    }
    
    /**
     * Load initial data if editing
     */
    function loadInitialData() {
        var itemId = $(config.selectors.itemSelect).val();
        if (itemId) {
            loadItemVariants(itemId);
        }
    }
    
    /**
     * Handle item selection change
     */
    function handleItemChange() {
        var itemId = $(this).val();
        
        // Reset variants
        state.selectedVariants = {};
        $('#variant-container').empty();
        
        if (itemId) {
            loadItemVariants(itemId);
        }
        
        calculatePrice();
    }
    
    /**
     * Load variants for selected item
     */
    function loadItemVariants(itemId) {
        $.ajax({
            url: AppHelper.settings.baseUrl + 'index.php/item_variants/get_item_variants/' + itemId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.variants) {
                    renderVariantSelectors(response.variants);
                }
            },
            error: function() {
                showError('Failed to load item variants');
            }
        });
    }
    
    /**
     * Render variant selection UI
     */
    function renderVariantSelectors(variants) {
        var container = $('#variant-container');
        container.empty();
        
        variants.forEach(function(variant) {
            var html = '<div class="form-group">' +
                '<label for="variant_' + variant.id + '">' + variant.name + '</label>' +
                '<select class="form-control variant-select" data-variant-id="' + variant.id + '" id="variant_' + variant.id + '">' +
                '<option value="">Select ' + variant.name + '</option>';
            
            variant.options.forEach(function(option) {
                html += '<option value="' + option.id + '" data-price-impact="' + option.price_impact + '">' +
                    option.name + ' (+' + AppHelper.settings.currencySymbol + option.price_impact + ')' +
                    '</option>';
            });
            
            html += '</select></div>';
            container.append(html);
        });
    }
    
    /**
     * Handle variant selection change
     */
    function handleVariantChange() {
        var variantId = $(this).data('variant-id');
        var optionId = $(this).val();
        
        if (optionId) {
            state.selectedVariants[variantId] = optionId;
        } else {
            delete state.selectedVariants[variantId];
        }
        
        calculatePrice();
    }
    
    /**
     * Calculate price based on current selections
     */
    function calculatePrice(showLoading) {
        if (state.isCalculating) {
            return;
        }
        
        var formData = collectFormData();
        
        if (!validateFormData(formData)) {
            return;
        }
        
        state.isCalculating = true;
        
        if (showLoading) {
            showLoadingIndicator();
        }
        
        $.ajax({
            url: config.endpoints.calculate,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    state.lastCalculation = response;
                    updatePriceDisplay(response);
                } else {
                    showError(response.message || 'Failed to calculate price');
                }
            },
            error: function() {
                showError('An error occurred while calculating price');
            },
            complete: function() {
                state.isCalculating = false;
                hideLoadingIndicator();
            }
        });
    }
    
    /**
     * Collect form data for price calculation
     */
    function collectFormData() {
        return {
            item_id: $(config.selectors.itemSelect).val(),
            client_id: $(config.selectors.clientSelect).val(),
            quantity: $(config.selectors.quantityInput).val() || 1,
            area: $(config.selectors.areaInput).val() || 0,
            date: $(config.selectors.dateInput).val(),
            variants: state.selectedVariants,
            csrf_token: $('[name="csrf_token"]').val()
        };
    }
    
    /**
     * Validate form data
     */
    function validateFormData(data) {
        if (!data.item_id) {
            showError('Please select an item');
            return false;
        }
        
        if (data.quantity <= 0) {
            showError('Quantity must be greater than 0');
            return false;
        }
        
        clearError();
        return true;
    }
    
    /**
     * Update price display
     */
    function updatePriceDisplay(priceData) {
        if (!priceData) {
            priceData = state.lastCalculation;
        }
        
        if (!priceData) {
            return;
        }
        
        var currency = AppHelper.settings.currencySymbol;
        
        // Update price range (visible to all)
        if (priceData.price_range) {
            $(config.selectors.priceRange).html(
                '<strong>Price Range:</strong> ' + 
                currency + priceData.price_range.min + ' - ' + 
                currency + priceData.price_range.max
            ).show();
        }
        
        // Update exact price (staff only)
        if (priceData.exact_price && $(config.selectors.exactPrice).length) {
            $(config.selectors.exactPrice).html(
                '<strong>Calculated Price:</strong> ' + 
                currency + priceData.exact_price
            ).show();
            
            // Update final price input if empty
            if (!$(config.selectors.finalPriceInput).val()) {
                $(config.selectors.finalPriceInput).val(priceData.exact_price);
            }
        }
        
        // Show breakdown if available
        if (priceData.breakdown) {
            updatePriceBreakdown(priceData.breakdown);
        }
        
        // Trigger custom event
        $(document).trigger('priceCalculated', [priceData]);
    }
    
    /**
     * Update price breakdown display
     */
    function updatePriceBreakdown(breakdown) {
        var html = '<div class="price-breakdown">' +
            '<h5>Price Breakdown</h5>' +
            '<table class="table table-sm">';
        
        if (breakdown.base_price) {
            html += '<tr><td>Base Price:</td><td>' + AppHelper.settings.currencySymbol + breakdown.base_price + '</td></tr>';
        }
        
        if (breakdown.quantity_adjustment) {
            html += '<tr><td>Quantity Adjustment:</td><td>' + breakdown.quantity_adjustment + '%</td></tr>';
        }
        
        if (breakdown.area_adjustment) {
            html += '<tr><td>Area Adjustment:</td><td>' + breakdown.area_adjustment + '%</td></tr>';
        }
        
        if (breakdown.client_discount) {
            html += '<tr><td>Client Discount:</td><td>-' + breakdown.client_discount + '%</td></tr>';
        }
        
        if (breakdown.date_adjustment) {
            html += '<tr><td>Date Adjustment:</td><td>' + breakdown.date_adjustment + '%</td></tr>';
        }
        
        if (breakdown.variant_total) {
            html += '<tr><td>Variant Options:</td><td>+' + AppHelper.settings.currencySymbol + breakdown.variant_total + '</td></tr>';
        }
        
        html += '</table></div>';
        
        $('#price-breakdown-container').html(html);
    }
    
    /**
     * Show loading indicator
     */
    function showLoadingIndicator() {
        $(config.selectors.loadingIndicator).show();
        $(config.selectors.calculateBtn).prop('disabled', true);
    }
    
    /**
     * Hide loading indicator
     */
    function hideLoadingIndicator() {
        $(config.selectors.loadingIndicator).hide();
        $(config.selectors.calculateBtn).prop('disabled', false);
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        $(config.selectors.errorMessage).text(message).show();
    }
    
    /**
     * Clear error message
     */
    function clearError() {
        $(config.selectors.errorMessage).hide().text('');
    }
    
    /**
     * Debounce function for input events
     */
    function debounce(func, wait) {
        var timeout;
        return function executedFunction() {
            var context = this;
            var args = arguments;
            var later = function() {
                timeout = null;
                func.apply(context, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Public API
     */
    return {
        init: init,
        calculate: calculatePrice,
        getLastCalculation: function() {
            return state.lastCalculation;
        },
        reset: function() {
            state.selectedVariants = {};
            state.lastCalculation = null;
            $(config.selectors.form)[0].reset();
            $('#variant-container').empty();
            $('#price-breakdown-container').empty();
            $(config.selectors.priceRange).hide();
            $(config.selectors.exactPrice).hide();
        }
    };
})();

// Initialize on document ready
$(document).ready(function() {
    if ($('#price-calculator-form').length) {
        PriceCalculator.init();
    }
});