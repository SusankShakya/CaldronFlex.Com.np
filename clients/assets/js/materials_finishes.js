/**
 * Materials and Finishes JavaScript Module
 * Handles material/finish selection and price calculations
 * Part of Sprint 3 Customization Features
 */

$(document).ready(function () {
    "use strict";

    // Initialize materials and finishes functionality
    var MaterialsFinishes = {
        
        // Configuration
        config: {
            materialSelect: '#material_id',
            finishSelect: '#finish_id',
            priceModifierDisplay: '#price_modifier_display',
            totalPriceDisplay: '#material_finish_total',
            basePriceInput: '#base_price',
            quantityInput: '#quantity',
            materialDataUrl: '/materials/get_material_data',
            finishDataUrl: '/finishes/get_compatible_finishes',
            errorContainer: '#material-finish-errors'
        },

        // Cache for material and finish data
        cache: {
            materials: {},
            finishes: {},
            compatibleFinishes: {}
        },

        // Initialize the module
        init: function() {
            this.bindEvents();
            this.loadInitialData();
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // Material selection change
            $(this.config.materialSelect).on('change', function() {
                var materialId = $(this).val();
                self.onMaterialChange(materialId);
            });

            // Finish selection change
            $(this.config.finishSelect).on('change', function() {
                var finishId = $(this).val();
                self.onFinishChange(finishId);
            });

            // Base price or quantity change
            $(this.config.basePriceInput + ', ' + this.config.quantityInput).on('input change', function() {
                self.calculateTotalPrice();
            });

            // Handle dynamic select2 initialization
            if ($.fn.select2) {
                $(this.config.materialSelect + ', ' + this.config.finishSelect).select2({
                    placeholder: AppLang.select || "Select an option",
                    allowClear: true
                });
            }
        },

        // Load initial data
        loadInitialData: function() {
            var materialId = $(this.config.materialSelect).val();
            if (materialId) {
                this.onMaterialChange(materialId, true);
            }
        },

        // Handle material change
        onMaterialChange: function(materialId, isInitial) {
            var self = this;
            
            if (!materialId) {
                this.resetFinishes();
                this.calculateTotalPrice();
                return;
            }

            // Show loading indicator
            if (typeof appLoader !== 'undefined') {
                appLoader.show();
            }

            // Check cache first
            if (this.cache.materials[materialId] && this.cache.compatibleFinishes[materialId]) {
                this.updateFinishOptions(this.cache.compatibleFinishes[materialId]);
                this.applyMaterialModifier(this.cache.materials[materialId]);
                
                if (typeof appLoader !== 'undefined') {
                    appLoader.hide();
                }
                return;
            }

            // Fetch material data and compatible finishes
            $.ajax({
                url: this.config.materialDataUrl,
                type: 'POST',
                data: {
                    material_id: materialId,
                    csrf_token: getCsrfToken() // Rise CRM CSRF token
                },
                success: function(response) {
                    if (response.success) {
                        // Cache the data
                        self.cache.materials[materialId] = response.material;
                        self.cache.compatibleFinishes[materialId] = response.compatible_finishes;
                        
                        // Update UI
                        self.updateFinishOptions(response.compatible_finishes);
                        self.applyMaterialModifier(response.material);
                        
                        // If initial load, select the previously selected finish
                        if (isInitial) {
                            var selectedFinish = $(self.config.finishSelect).data('selected');
                            if (selectedFinish) {
                                $(self.config.finishSelect).val(selectedFinish).trigger('change');
                            }
                        }
                    } else {
                        self.showError(response.message || 'Failed to load material data');
                    }
                },
                error: function() {
                    self.showError(AppLang.error_occurred || 'An error occurred');
                },
                complete: function() {
                    if (typeof appLoader !== 'undefined') {
                        appLoader.hide();
                    }
                }
            });
        },

        // Handle finish change
        onFinishChange: function(finishId) {
            if (!finishId) {
                this.removeFinishModifier();
                this.calculateTotalPrice();
                return;
            }

            // Apply finish modifier if we have the data
            var finishData = this.getFinishData(finishId);
            if (finishData) {
                this.applyFinishModifier(finishData);
            }
        },

        // Update finish dropdown options
        updateFinishOptions: function(finishes) {
            var $finishSelect = $(this.config.finishSelect);
            var currentValue = $finishSelect.val();
            
            // Clear existing options
            $finishSelect.empty();
            $finishSelect.append('<option value="">' + (AppLang.select_finish || 'Select Finish') + '</option>');
            
            // Add compatible finishes
            $.each(finishes, function(index, finish) {
                var option = $('<option></option>')
                    .attr('value', finish.id)
                    .text(finish.name);
                
                // Add price modifier info to option
                if (finish.price_modifier_type && finish.price_modifier_value) {
                    var modifierText = finish.price_modifier_type === 'percentage' 
                        ? finish.price_modifier_value + '%' 
                        : '+' + self.formatCurrency(finish.price_modifier_value);
                    option.text(finish.name + ' (' + modifierText + ')');
                }
                
                $finishSelect.append(option);
                
                // Cache finish data
                self.cache.finishes[finish.id] = finish;
            });
            
            // Restore previous selection if still valid
            if (currentValue && $finishSelect.find('option[value="' + currentValue + '"]').length > 0) {
                $finishSelect.val(currentValue);
            }
            
            // Refresh select2 if initialized
            if ($.fn.select2 && $finishSelect.hasClass('select2-hidden-accessible')) {
                $finishSelect.trigger('change.select2');
            }
        },

        // Apply material price modifier
        applyMaterialModifier: function(material) {
            if (!material || !material.price_modifier_type) {
                return;
            }

            var modifier = {
                type: material.price_modifier_type,
                value: parseFloat(material.price_modifier_value) || 0,
                name: material.name
            };

            $(this.config.materialSelect).data('modifier', modifier);
            this.calculateTotalPrice();
        },

        // Apply finish price modifier
        applyFinishModifier: function(finish) {
            if (!finish || !finish.price_modifier_type) {
                this.removeFinishModifier();
                return;
            }

            var modifier = {
                type: finish.price_modifier_type,
                value: parseFloat(finish.price_modifier_value) || 0,
                name: finish.name
            };

            $(this.config.finishSelect).data('modifier', modifier);
            this.calculateTotalPrice();
        },

        // Remove finish modifier
        removeFinishModifier: function() {
            $(this.config.finishSelect).removeData('modifier');
        },

        // Calculate total price with modifiers
        calculateTotalPrice: function() {
            var basePrice = parseFloat($(this.config.basePriceInput).val()) || 0;
            var quantity = parseInt($(this.config.quantityInput).val()) || 1;
            var materialModifier = $(this.config.materialSelect).data('modifier');
            var finishModifier = $(this.config.finishSelect).data('modifier');
            
            var totalPrice = basePrice;
            var modifierDetails = [];

            // Apply material modifier
            if (materialModifier) {
                var materialAdjustment = this.calculateModifier(basePrice, materialModifier);
                totalPrice += materialAdjustment;
                modifierDetails.push({
                    name: materialModifier.name,
                    type: materialModifier.type,
                    value: materialModifier.value,
                    adjustment: materialAdjustment
                });
            }

            // Apply finish modifier
            if (finishModifier) {
                var finishAdjustment = this.calculateModifier(basePrice, finishModifier);
                totalPrice += finishAdjustment;
                modifierDetails.push({
                    name: finishModifier.name,
                    type: finishModifier.type,
                    value: finishModifier.value,
                    adjustment: finishAdjustment
                });
            }

            // Apply quantity
            totalPrice *= quantity;

            // Update displays
            this.updatePriceDisplay(totalPrice, modifierDetails);

            // Trigger event for other modules
            $(document).trigger('materialFinishPriceCalculated', {
                basePrice: basePrice,
                totalPrice: totalPrice,
                quantity: quantity,
                modifiers: modifierDetails
            });
        },

        // Calculate modifier amount
        calculateModifier: function(basePrice, modifier) {
            if (modifier.type === 'percentage') {
                return basePrice * (modifier.value / 100);
            } else {
                return modifier.value;
            }
        },

        // Update price displays
        updatePriceDisplay: function(totalPrice, modifierDetails) {
            // Update total price
            $(this.config.totalPriceDisplay).text(this.formatCurrency(totalPrice));

            // Update modifier details display
            var modifierHtml = '';
            if (modifierDetails.length > 0) {
                modifierHtml = '<div class="price-modifiers">';
                modifierHtml += '<small class="text-muted">' + (AppLang.price_modifiers || 'Price Modifiers:') + '</small>';
                modifierHtml += '<ul class="list-unstyled mb-0">';
                
                $.each(modifierDetails, function(index, modifier) {
                    var sign = modifier.adjustment >= 0 ? '+' : '';
                    var typeText = modifier.type === 'percentage' ? modifier.value + '%' : AppLang.fixed || 'Fixed';
                    modifierHtml += '<li><small>' + modifier.name + ' (' + typeText + '): ' + 
                                   sign + self.formatCurrency(modifier.adjustment) + '</small></li>';
                });
                
                modifierHtml += '</ul></div>';
            }
            
            $(this.config.priceModifierDisplay).html(modifierHtml);
        },

        // Get finish data from cache
        getFinishData: function(finishId) {
            return this.cache.finishes[finishId] || null;
        },

        // Reset finish options
        resetFinishes: function() {
            var $finishSelect = $(this.config.finishSelect);
            $finishSelect.empty();
            $finishSelect.append('<option value="">' + (AppLang.select_material_first || 'Select Material First') + '</option>');
            
            if ($.fn.select2 && $finishSelect.hasClass('select2-hidden-accessible')) {
                $finishSelect.trigger('change.select2');
            }
            
            this.removeFinishModifier();
        },

        // Format currency
        formatCurrency: function(amount) {
            // Use Rise CRM's currency formatting if available
            if (typeof toCurrency === 'function') {
                return toCurrency(amount);
            }
            
            // Fallback formatting
            var currencySymbol = AppHelper.settings.currencySymbol || '$';
            var decimalSeparator = AppHelper.settings.decimalSeparator || '.';
            var thousandSeparator = AppHelper.settings.thousandSeparator || ',';
            
            var formatted = amount.toFixed(2);
            formatted = formatted.replace('.', decimalSeparator);
            
            // Add thousand separators
            var parts = formatted.split(decimalSeparator);
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
            formatted = parts.join(decimalSeparator);
            
            return currencySymbol + formatted;
        },

        // Show error message
        showError: function(message) {
            var errorHtml = '<div class="alert alert-danger alert-dismissible">';
            errorHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            errorHtml += message;
            errorHtml += '</div>';
            
            $(this.config.errorContainer).html(errorHtml);
        },

        // Clear errors
        clearErrors: function() {
            $(this.config.errorContainer).empty();
        },

        // Public method to get current selection
        getSelection: function() {
            return {
                materialId: $(this.config.materialSelect).val(),
                finishId: $(this.config.finishSelect).val(),
                materialModifier: $(this.config.materialSelect).data('modifier'),
                finishModifier: $(this.config.finishSelect).data('modifier')
            };
        }
    };

    // Initialize if material/finish elements exist
    if ($(MaterialsFinishes.config.materialSelect).length > 0) {
        MaterialsFinishes.init();
        
        // Make MaterialsFinishes available globally
        window.MaterialsFinishes = MaterialsFinishes;
    }

    // Integration with Rise CRM's modal forms
    $(document).on('ajaxSuccess', function(event, xhr, settings) {
        // Re-initialize after AJAX content loads
        if ($(MaterialsFinishes.config.materialSelect).length > 0 && 
            !$(MaterialsFinishes.config.materialSelect).data('mf-initialized')) {
            MaterialsFinishes.init();
            $(MaterialsFinishes.config.materialSelect).data('mf-initialized', true);
        }
    });
});

// Helper function to get CSRF token (Rise CRM pattern)
function getCsrfToken() {
    return $('[name="csrf_token"]').val() || AppHelper.csrfToken || '';
}