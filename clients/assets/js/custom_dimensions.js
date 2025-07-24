/**
 * Custom Dimensions JavaScript Module
 * Handles real-time dimension calculations and price updates
 * Part of Sprint 3 Customization Features
 */

$(document).ready(function () {
    "use strict";

    // Initialize custom dimensions functionality
    var CustomDimensions = {
        
        // Configuration
        config: {
            widthInput: '#custom_width',
            heightInput: '#custom_height',
            unitSelect: '#dimension_unit',
            areaDisplay: '#calculated_area',
            priceDisplay: '#calculated_price',
            totalPriceDisplay: '#total_price',
            quantityInput: '#quantity',
            pricePerUnitInput: '#price_per_unit',
            minWidthInput: '#min_width',
            maxWidthInput: '#max_width',
            minHeightInput: '#min_height',
            maxHeightInput: '#max_height',
            errorContainer: '#dimension-errors'
        },

        // Initialize the module
        init: function() {
            this.bindEvents();
            this.calculateInitialValues();
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // Dimension input changes
            $(this.config.widthInput + ', ' + this.config.heightInput).on('input change', function() {
                self.validateAndCalculate();
            });

            // Unit change
            $(this.config.unitSelect).on('change', function() {
                self.validateAndCalculate();
            });

            // Quantity change
            $(this.config.quantityInput).on('input change', function() {
                self.calculateTotalPrice();
            });

            // Price per unit change
            $(this.config.pricePerUnitInput).on('input change', function() {
                self.validateAndCalculate();
            });

            // Form submission validation
            $('#custom-dimensions-form').on('submit', function(e) {
                if (!self.validateDimensions()) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        // Calculate initial values on page load
        calculateInitialValues: function() {
            if ($(this.config.widthInput).val() && $(this.config.heightInput).val()) {
                this.validateAndCalculate();
            }
        },

        // Validate dimensions and calculate area/price
        validateAndCalculate: function() {
            this.clearErrors();
            
            if (this.validateDimensions()) {
                this.calculateArea();
                this.calculatePrice();
                this.calculateTotalPrice();
            }
        },

        // Validate dimension constraints
        validateDimensions: function() {
            var width = parseFloat($(this.config.widthInput).val()) || 0;
            var height = parseFloat($(this.config.heightInput).val()) || 0;
            var minWidth = parseFloat($(this.config.minWidthInput).val()) || 0;
            var maxWidth = parseFloat($(this.config.maxWidthInput).val()) || Infinity;
            var minHeight = parseFloat($(this.config.minHeightInput).val()) || 0;
            var maxHeight = parseFloat($(this.config.maxHeightInput).val()) || Infinity;
            var errors = [];

            // Check if dimensions are provided
            if (width <= 0) {
                errors.push(AppLang.width_required || "Width is required and must be greater than 0");
            }
            if (height <= 0) {
                errors.push(AppLang.height_required || "Height is required and must be greater than 0");
            }

            // Check min/max constraints
            if (width < minWidth) {
                errors.push((AppLang.width_min_error || "Width must be at least {0}").replace("{0}", minWidth));
            }
            if (width > maxWidth) {
                errors.push((AppLang.width_max_error || "Width cannot exceed {0}").replace("{0}", maxWidth));
            }
            if (height < minHeight) {
                errors.push((AppLang.height_min_error || "Height must be at least {0}").replace("{0}", minHeight));
            }
            if (height > maxHeight) {
                errors.push((AppLang.height_max_error || "Height cannot exceed {0}").replace("{0}", maxHeight));
            }

            // Display errors if any
            if (errors.length > 0) {
                this.showErrors(errors);
                return false;
            }

            return true;
        },

        // Calculate area based on dimensions
        calculateArea: function() {
            var width = parseFloat($(this.config.widthInput).val()) || 0;
            var height = parseFloat($(this.config.heightInput).val()) || 0;
            var unit = $(this.config.unitSelect).val() || 'cm';
            
            var area = width * height;
            
            // Format area with appropriate decimal places
            var formattedArea = area.toFixed(2);
            
            // Update area display
            $(this.config.areaDisplay).text(formattedArea + ' ' + unit + '²');
            
            // Store calculated area for price calculation
            $(this.config.areaDisplay).data('area', area);
            
            return area;
        },

        // Calculate price based on area and price per unit
        calculatePrice: function() {
            var area = $(this.config.areaDisplay).data('area') || 0;
            var pricePerUnit = parseFloat($(this.config.pricePerUnitInput).val()) || 0;
            
            var price = area * pricePerUnit;
            
            // Format price with currency
            var formattedPrice = this.formatCurrency(price);
            
            // Update price display
            $(this.config.priceDisplay).text(formattedPrice);
            
            // Store calculated price
            $(this.config.priceDisplay).data('price', price);
            
            return price;
        },

        // Calculate total price including quantity
        calculateTotalPrice: function() {
            var unitPrice = $(this.config.priceDisplay).data('price') || 0;
            var quantity = parseInt($(this.config.quantityInput).val()) || 1;
            
            var totalPrice = unitPrice * quantity;
            
            // Format total price
            var formattedTotalPrice = this.formatCurrency(totalPrice);
            
            // Update total price display
            $(this.config.totalPriceDisplay).text(formattedTotalPrice);
            
            // Trigger custom event for other modules
            $(document).trigger('customDimensionsPriceCalculated', {
                unitPrice: unitPrice,
                quantity: quantity,
                totalPrice: totalPrice
            });
            
            return totalPrice;
        },

        // Format currency based on system settings
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

        // Show validation errors
        showErrors: function(errors) {
            var errorHtml = '<div class="alert alert-danger">';
            errorHtml += '<ul class="mb-0">';
            
            errors.forEach(function(error) {
                errorHtml += '<li>' + error + '</li>';
            });
            
            errorHtml += '</ul></div>';
            
            $(this.config.errorContainer).html(errorHtml);
        },

        // Clear validation errors
        clearErrors: function() {
            $(this.config.errorContainer).empty();
        },

        // Public method to get current dimensions
        getDimensions: function() {
            return {
                width: parseFloat($(this.config.widthInput).val()) || 0,
                height: parseFloat($(this.config.heightInput).val()) || 0,
                unit: $(this.config.unitSelect).val() || 'cm',
                area: $(this.config.areaDisplay).data('area') || 0,
                price: $(this.config.priceDisplay).data('price') || 0,
                quantity: parseInt($(this.config.quantityInput).val()) || 1,
                totalPrice: $(this.config.totalPriceDisplay).data('totalPrice') || 0
            };
        },

        // Public method to set dimensions programmatically
        setDimensions: function(width, height, unit) {
            $(this.config.widthInput).val(width);
            $(this.config.heightInput).val(height);
            if (unit) {
                $(this.config.unitSelect).val(unit);
            }
            this.validateAndCalculate();
        }
    };

    // Initialize if custom dimensions elements exist
    if ($(CustomDimensions.config.widthInput).length > 0) {
        CustomDimensions.init();
        
        // Make CustomDimensions available globally
        window.CustomDimensions = CustomDimensions;
    }

    // Integration with Rise CRM's form validation
    if (typeof appLoader !== 'undefined') {
        $(document).on('ajaxSuccess', function(event, xhr, settings) {
            // Re-initialize after AJAX content loads
            if ($(CustomDimensions.config.widthInput).length > 0 && !$(CustomDimensions.config.widthInput).data('initialized')) {
                CustomDimensions.init();
                $(CustomDimensions.config.widthInput).data('initialized', true);
            }
        });
    }
});