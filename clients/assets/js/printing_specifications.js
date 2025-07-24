/**
 * Printing Specifications JavaScript Module
 * Handles print resolution, color modes, and additional costs
 * Part of Sprint 3 Customization Features
 */

$(document).ready(function () {
    "use strict";

    // Initialize printing specifications functionality
    var PrintingSpecifications = {
        
        // Configuration
        config: {
            resolutionInput: '#print_resolution',
            colorModeSelect: '#color_mode',
            bleedSizeInput: '#bleed_size',
            specialInstructionsTextarea: '#special_instructions',
            additionalCostDisplay: '#printing_additional_cost',
            totalCostDisplay: '#printing_total_cost',
            basePriceInput: '#base_price',
            quantityInput: '#quantity',
            errorContainer: '#printing-errors',
            warningContainer: '#printing-warnings',
            // DPI constraints
            minDPI: 72,
            maxDPI: 2400,
            recommendedDPI: {
                'web': 72,
                'standard': 300,
                'high_quality': 600,
                'professional': 1200
            }
        },

        // Color mode pricing modifiers
        colorModePricing: {
            'CMYK': { type: 'percentage', value: 0 },
            'RGB': { type: 'percentage', value: -5 },
            'Pantone': { type: 'percentage', value: 15 },
            'Grayscale': { type: 'percentage', value: -20 },
            'Black_White': { type: 'percentage', value: -30 }
        },

        // Initialize the module
        init: function() {
            this.bindEvents();
            this.loadInitialValues();
            this.validateAndCalculate();
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // Resolution input change
            $(this.config.resolutionInput).on('input change', function() {
                self.validateResolution();
                self.calculateAdditionalCosts();
            });

            // Color mode change
            $(this.config.colorModeSelect).on('change', function() {
                self.onColorModeChange();
            });

            // Bleed size change
            $(this.config.bleedSizeInput).on('input change', function() {
                self.validateBleedSize();
                self.calculateAdditionalCosts();
            });

            // Special instructions change
            $(this.config.specialInstructionsTextarea).on('input', function() {
                self.validateSpecialInstructions();
            });

            // Base price or quantity change
            $(this.config.basePriceInput + ', ' + this.config.quantityInput).on('input change', function() {
                self.calculateAdditionalCosts();
            });

            // Quick resolution presets
            $('.dpi-preset').on('click', function(e) {
                e.preventDefault();
                var preset = $(this).data('preset');
                var dpi = self.config.recommendedDPI[preset];
                if (dpi) {
                    $(self.config.resolutionInput).val(dpi).trigger('change');
                }
            });

            // Form validation
            $('#printing-specifications-form').on('submit', function(e) {
                if (!self.validateAll()) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        // Load initial values
        loadInitialValues: function() {
            // Set default resolution if empty
            if (!$(this.config.resolutionInput).val()) {
                $(this.config.resolutionInput).val(this.config.recommendedDPI.standard);
            }

            // Initialize select2 for color mode if available
            if ($.fn.select2 && $(this.config.colorModeSelect).length > 0) {
                $(this.config.colorModeSelect).select2({
                    placeholder: AppLang.select_color_mode || "Select Color Mode",
                    allowClear: false
                });
            }
        },

        // Validate print resolution (DPI)
        validateResolution: function() {
            var dpi = parseInt($(this.config.resolutionInput).val()) || 0;
            var isValid = true;
            var warnings = [];
            var errors = [];

            // Clear previous messages
            this.clearMessages();

            // Validate DPI range
            if (dpi < this.config.minDPI) {
                errors.push((AppLang.dpi_too_low || "Resolution must be at least {0} DPI").replace("{0}", this.config.minDPI));
                isValid = false;
            } else if (dpi > this.config.maxDPI) {
                errors.push((AppLang.dpi_too_high || "Resolution cannot exceed {0} DPI").replace("{0}", this.config.maxDPI));
                isValid = false;
            }

            // Add warnings for quality recommendations
            if (isValid) {
                if (dpi < this.config.recommendedDPI.standard) {
                    warnings.push(AppLang.low_quality_warning || "Resolution below 300 DPI may result in poor print quality");
                } else if (dpi > this.config.recommendedDPI.professional) {
                    warnings.push(AppLang.excessive_dpi_warning || "Resolution above 1200 DPI may not provide noticeable quality improvement");
                }
            }

            // Display messages
            if (errors.length > 0) {
                this.showErrors(errors);
            }
            if (warnings.length > 0) {
                this.showWarnings(warnings);
            }

            // Update UI state
            this.updateResolutionIndicator(dpi);

            return isValid;
        },

        // Update resolution quality indicator
        updateResolutionIndicator: function(dpi) {
            var quality = '';
            var cssClass = '';

            if (dpi < this.config.recommendedDPI.standard) {
                quality = AppLang.low_quality || 'Low Quality';
                cssClass = 'text-danger';
            } else if (dpi < this.config.recommendedDPI.high_quality) {
                quality = AppLang.standard_quality || 'Standard Quality';
                cssClass = 'text-warning';
            } else if (dpi < this.config.recommendedDPI.professional) {
                quality = AppLang.high_quality || 'High Quality';
                cssClass = 'text-info';
            } else {
                quality = AppLang.professional_quality || 'Professional Quality';
                cssClass = 'text-success';
            }

            var indicator = '<span class="' + cssClass + '"><i class="feather icon-info"></i> ' + quality + '</span>';
            $('#resolution-quality-indicator').html(indicator);
        },

        // Handle color mode change
        onColorModeChange: function() {
            var colorMode = $(this.config.colorModeSelect).val();
            
            // Update color mode information
            this.updateColorModeInfo(colorMode);
            
            // Calculate costs
            this.calculateAdditionalCosts();
            
            // Show/hide Pantone color picker if applicable
            if (colorMode === 'Pantone') {
                $('#pantone-color-selector').show();
            } else {
                $('#pantone-color-selector').hide();
            }
        },

        // Update color mode information display
        updateColorModeInfo: function(colorMode) {
            var info = '';
            
            switch(colorMode) {
                case 'CMYK':
                    info = AppLang.cmyk_info || 'Standard 4-color printing (Cyan, Magenta, Yellow, Black)';
                    break;
                case 'RGB':
                    info = AppLang.rgb_info || 'Digital color mode (Red, Green, Blue) - requires conversion for print';
                    break;
                case 'Pantone':
                    info = AppLang.pantone_info || 'Spot color printing for precise color matching';
                    break;
                case 'Grayscale':
                    info = AppLang.grayscale_info || 'Black and white with shades of gray';
                    break;
                case 'Black_White':
                    info = AppLang.black_white_info || 'Pure black and white only';
                    break;
            }
            
            $('#color-mode-info').html('<small class="text-muted"><i class="feather icon-info"></i> ' + info + '</small>');
        },

        // Validate bleed size
        validateBleedSize: function() {
            var bleedSize = parseFloat($(this.config.bleedSizeInput).val()) || 0;
            var warnings = [];

            if (bleedSize < 3 && bleedSize > 0) {
                warnings.push(AppLang.small_bleed_warning || "Bleed size less than 3mm may cause white edges");
            } else if (bleedSize > 10) {
                warnings.push(AppLang.large_bleed_warning || "Bleed size greater than 10mm is excessive");
            }

            if (warnings.length > 0) {
                this.showWarnings(warnings);
            }

            return true;
        },

        // Validate special instructions
        validateSpecialInstructions: function() {
            var instructions = $(this.config.specialInstructionsTextarea).val();
            var maxLength = 500;
            var remaining = maxLength - instructions.length;

            // Update character counter
            $('#instructions-char-counter').text(remaining + ' ' + (AppLang.characters_remaining || 'characters remaining'));

            if (remaining < 0) {
                this.showErrors([AppLang.instructions_too_long || 'Special instructions exceed maximum length']);
                return false;
            }

            return true;
        },

        // Calculate additional costs based on specifications
        calculateAdditionalCosts: function() {
            var basePrice = parseFloat($(this.config.basePriceInput).val()) || 0;
            var quantity = parseInt($(this.config.quantityInput).val()) || 1;
            var additionalCost = 0;
            var costBreakdown = [];

            // Resolution-based cost (higher DPI = higher cost)
            var dpi = parseInt($(this.config.resolutionInput).val()) || 0;
            if (dpi > this.config.recommendedDPI.standard) {
                var dpiMultiplier = (dpi - this.config.recommendedDPI.standard) / 1000;
                var dpiCost = basePrice * dpiMultiplier * 0.1; // 10% increase per 1000 DPI above standard
                additionalCost += dpiCost;
                costBreakdown.push({
                    name: AppLang.high_resolution_cost || 'High Resolution',
                    amount: dpiCost
                });
            }

            // Color mode cost
            var colorMode = $(this.config.colorModeSelect).val();
            if (colorMode && this.colorModePricing[colorMode]) {
                var colorModifier = this.colorModePricing[colorMode];
                var colorCost = this.calculateModifier(basePrice, colorModifier);
                additionalCost += colorCost;
                if (colorCost !== 0) {
                    costBreakdown.push({
                        name: AppLang.color_mode_cost || 'Color Mode: ' + colorMode,
                        amount: colorCost
                    });
                }
            }

            // Bleed cost
            var bleedSize = parseFloat($(this.config.bleedSizeInput).val()) || 0;
            if (bleedSize > 0) {
                var bleedCost = bleedSize * 2; // $2 per mm of bleed
                additionalCost += bleedCost;
                costBreakdown.push({
                    name: AppLang.bleed_cost || 'Bleed (' + bleedSize + 'mm)',
                    amount: bleedCost
                });
            }

            // Update displays
            this.updateCostDisplay(additionalCost, costBreakdown, basePrice, quantity);

            // Trigger event for other modules
            $(document).trigger('printingSpecificationsCostCalculated', {
                basePrice: basePrice,
                additionalCost: additionalCost,
                totalCost: (basePrice + additionalCost) * quantity,
                specifications: this.getSpecifications()
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

        // Update cost displays
        updateCostDisplay: function(additionalCost, breakdown, basePrice, quantity) {
            // Update additional cost
            $(this.config.additionalCostDisplay).text(this.formatCurrency(additionalCost));

            // Update total cost
            var totalCost = (basePrice + additionalCost) * quantity;
            $(this.config.totalCostDisplay).text(this.formatCurrency(totalCost));

            // Update breakdown display
            var breakdownHtml = '';
            if (breakdown.length > 0) {
                breakdownHtml = '<div class="cost-breakdown mt-2">';
                breakdownHtml += '<small class="text-muted">' + (AppLang.additional_costs || 'Additional Costs:') + '</small>';
                breakdownHtml += '<ul class="list-unstyled mb-0">';
                
                $.each(breakdown, function(index, item) {
                    var sign = item.amount >= 0 ? '+' : '';
                    breakdownHtml += '<li><small>' + item.name + ': ' + 
                                   sign + self.formatCurrency(item.amount) + '</small></li>';
                });
                
                breakdownHtml += '</ul></div>';
            }
            
            $('#cost-breakdown').html(breakdownHtml);
        },

        // Validate all specifications
        validateAll: function() {
            var isValid = true;

            // Validate resolution
            if (!this.validateResolution()) {
                isValid = false;
            }

            // Validate bleed size
            if (!this.validateBleedSize()) {
                isValid = false;
            }

            // Validate special instructions
            if (!this.validateSpecialInstructions()) {
                isValid = false;
            }

            // Validate color mode selection
            if (!$(this.config.colorModeSelect).val()) {
                this.showErrors([AppLang.color_mode_required || 'Please select a color mode']);
                isValid = false;
            }

            return isValid;
        },

        // Validate and calculate all values
        validateAndCalculate: function() {
            if (this.validateAll()) {
                this.calculateAdditionalCosts();
            }
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

        // Show error messages
        showErrors: function(errors) {
            var errorHtml = '<div class="alert alert-danger alert-dismissible">';
            errorHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            errorHtml += '<ul class="mb-0">';
            
            errors.forEach(function(error) {
                errorHtml += '<li>' + error + '</li>';
            });
            
            errorHtml += '</ul></div>';
            
            $(this.config.errorContainer).html(errorHtml);
        },

        // Show warning messages
        showWarnings: function(warnings) {
            var warningHtml = '<div class="alert alert-warning alert-dismissible">';
            warningHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            warningHtml += '<ul class="mb-0">';
            
            warnings.forEach(function(warning) {
                warningHtml += '<li>' + warning + '</li>';
            });
            
            warningHtml += '</ul></div>';
            
            $(this.config.warningContainer).html(warningHtml);
        },

        // Clear all messages
        clearMessages: function() {
            $(this.config.errorContainer).empty();
            $(this.config.warningContainer).empty();
        },

        // Get current specifications
        getSpecifications: function() {
            return {
                resolution: parseInt($(this.config.resolutionInput).val()) || 0,
                colorMode: $(this.config.colorModeSelect).val(),
                bleedSize: parseFloat($(this.config.bleedSizeInput).val()) || 0,
                specialInstructions: $(this.config.specialInstructionsTextarea).val(),
                additionalCost: parseFloat($(this.config.additionalCostDisplay).text().replace(/[^0-9.-]+/g, '')) || 0
            };
        }
    };

    // Initialize if printing specification elements exist
    if ($(PrintingSpecifications.config.resolutionInput).length > 0) {
        PrintingSpecifications.init();
        
        // Make PrintingSpecifications available globally
        window.PrintingSpecifications = PrintingSpecifications;
    }

    // Integration with Rise CRM's modal forms
    $(document).on('ajaxSuccess', function(event, xhr, settings) {
        // Re-initialize after AJAX content loads
        if ($(PrintingSpecifications.config.resolutionInput).length > 0 &&
            !$(PrintingSpecifications.config.resolutionInput).data('ps-initialized')) {
            PrintingSpecifications.init();
            $(PrintingSpecifications.config.resolutionInput).data('ps-initialized', true);
        }
    });
});