/**
 * Inventory Management JavaScript Module
 * Handles stock levels, reservations, and low stock warnings
 * Part of Sprint 3 Customization Features
 */

$(document).ready(function () {
    "use strict";

    // Initialize inventory management functionality
    var InventoryManagement = {
        
        // Configuration
        config: {
            stockLevelInput: '#current_stock',
            reservedStockInput: '#reserved_stock',
            availableStockDisplay: '#available_stock',
            lowStockThresholdInput: '#low_stock_threshold',
            warehouseSelect: '#warehouse_id',
            itemSelect: '#item_id',
            transactionTypeSelect: '#transaction_type',
            quantityInput: '#quantity',
            notesTextarea: '#notes',
            stockWarningContainer: '#stock-warning',
            stockHistoryTable: '#stock-history-table',
            errorContainer: '#inventory-errors',
            updateStockUrl: '/inventory/update_stock',
            getStockUrl: '/inventory/get_stock_info',
            transactionHistoryUrl: '/inventory/get_transaction_history'
        },

        // Transaction types
        transactionTypes: {
            'purchase': { label: 'Purchase', modifier: 1, class: 'text-success' },
            'sale': { label: 'Sale', modifier: -1, class: 'text-danger' },
            'adjustment_add': { label: 'Adjustment (Add)', modifier: 1, class: 'text-info' },
            'adjustment_remove': { label: 'Adjustment (Remove)', modifier: -1, class: 'text-warning' },
            'transfer_in': { label: 'Transfer In', modifier: 1, class: 'text-primary' },
            'transfer_out': { label: 'Transfer Out', modifier: -1, class: 'text-secondary' },
            'return': { label: 'Return', modifier: 1, class: 'text-success' },
            'damage': { label: 'Damage/Loss', modifier: -1, class: 'text-danger' }
        },

        // Initialize the module
        init: function() {
            this.bindEvents();
            this.calculateAvailableStock();
            this.checkLowStock();
            this.loadTransactionHistory();
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // Stock level changes
            $(this.config.stockLevelInput + ', ' + this.config.reservedStockInput).on('input change', function() {
                self.calculateAvailableStock();
                self.checkLowStock();
            });

            // Low stock threshold change
            $(this.config.lowStockThresholdInput).on('input change', function() {
                self.checkLowStock();
            });

            // Warehouse or item selection change
            $(this.config.warehouseSelect + ', ' + this.config.itemSelect).on('change', function() {
                self.loadStockInfo();
            });

            // Transaction type change
            $(this.config.transactionTypeSelect).on('change', function() {
                self.updateTransactionUI();
            });

            // Stock update form submission
            $('#stock-update-form').on('submit', function(e) {
                e.preventDefault();
                self.updateStock();
            });

            // Quick stock adjustment buttons
            $('.quick-adjust-btn').on('click', function(e) {
                e.preventDefault();
                var adjustment = $(this).data('adjustment');
                self.quickAdjustStock(adjustment);
            });

            // Real-time stock check
            if ($(this.config.itemSelect).length > 0) {
                setInterval(function() {
                    self.checkRealTimeStock();
                }, 30000); // Check every 30 seconds
            }
        },

        // Calculate available stock
        calculateAvailableStock: function() {
            var currentStock = parseInt($(this.config.stockLevelInput).val()) || 0;
            var reservedStock = parseInt($(this.config.reservedStockInput).val()) || 0;
            var availableStock = currentStock - reservedStock;

            // Ensure available stock is not negative
            if (availableStock < 0) {
                availableStock = 0;
                this.showError(AppLang.reserved_exceeds_current || 'Reserved stock exceeds current stock');
            }

            // Update display
            $(this.config.availableStockDisplay).text(availableStock);
            
            // Update visual indicator
            this.updateStockIndicator(availableStock);

            // Store for other calculations
            $(this.config.availableStockDisplay).data('stock', availableStock);

            return availableStock;
        },

        // Update stock level indicator
        updateStockIndicator: function(availableStock) {
            var $indicator = $(this.config.availableStockDisplay);
            
            // Remove all classes
            $indicator.removeClass('text-success text-warning text-danger');
            
            // Add appropriate class based on stock level
            if (availableStock <= 0) {
                $indicator.addClass('text-danger');
            } else if (availableStock <= this.getLowStockThreshold()) {
                $indicator.addClass('text-warning');
            } else {
                $indicator.addClass('text-success');
            }
        },

        // Check for low stock warning
        checkLowStock: function() {
            var availableStock = $(this.config.availableStockDisplay).data('stock') || 0;
            var threshold = this.getLowStockThreshold();
            var $warningContainer = $(this.config.stockWarningContainer);

            // Clear previous warnings
            $warningContainer.empty();

            if (availableStock <= 0) {
                this.showStockWarning('danger', AppLang.out_of_stock || 'Out of Stock!', 
                    AppLang.out_of_stock_message || 'This item is currently out of stock.');
            } else if (availableStock <= threshold) {
                this.showStockWarning('warning', AppLang.low_stock || 'Low Stock Warning!', 
                    (AppLang.low_stock_message || 'Only {0} units available.').replace('{0}', availableStock));
            }
        },

        // Get low stock threshold
        getLowStockThreshold: function() {
            return parseInt($(this.config.lowStockThresholdInput).val()) || 10;
        },

        // Show stock warning
        showStockWarning: function(type, title, message) {
            var warningHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show">';
            warningHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            warningHtml += '<h5 class="alert-heading"><i class="feather icon-alert-triangle"></i> ' + title + '</h5>';
            warningHtml += '<p class="mb-0">' + message + '</p>';
            warningHtml += '</div>';

            $(this.config.stockWarningContainer).html(warningHtml);
        },

        // Load stock information for selected item/warehouse
        loadStockInfo: function() {
            var self = this;
            var itemId = $(this.config.itemSelect).val();
            var warehouseId = $(this.config.warehouseSelect).val();

            if (!itemId) {
                return;
            }

            // Show loading
            if (typeof appLoader !== 'undefined') {
                appLoader.show();
            }

            $.ajax({
                url: this.config.getStockUrl,
                type: 'POST',
                data: {
                    item_id: itemId,
                    warehouse_id: warehouseId,
                    csrf_token: getCsrfToken()
                },
                success: function(response) {
                    if (response.success) {
                        // Update stock levels
                        $(self.config.stockLevelInput).val(response.current_stock);
                        $(self.config.reservedStockInput).val(response.reserved_stock);
                        $(self.config.lowStockThresholdInput).val(response.low_stock_threshold);
                        
                        // Recalculate
                        self.calculateAvailableStock();
                        self.checkLowStock();
                        
                        // Update history if available
                        if (response.recent_transactions) {
                            self.updateTransactionHistory(response.recent_transactions);
                        }
                    } else {
                        self.showError(response.message || 'Failed to load stock information');
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

        // Update transaction UI based on type
        updateTransactionUI: function() {
            var transactionType = $(this.config.transactionTypeSelect).val();
            var typeInfo = this.transactionTypes[transactionType];

            if (typeInfo) {
                // Update UI elements based on transaction type
                var $quantityGroup = $(this.config.quantityInput).closest('.form-group');
                var $label = $quantityGroup.find('label');

                // Update label
                if (typeInfo.modifier > 0) {
                    $label.html('<i class="feather icon-plus text-success"></i> ' + (AppLang.quantity_to_add || 'Quantity to Add'));
                } else {
                    $label.html('<i class="feather icon-minus text-danger"></i> ' + (AppLang.quantity_to_remove || 'Quantity to Remove'));
                }

                // Show/hide relevant fields based on type
                if (transactionType.includes('transfer')) {
                    $('#transfer-fields').show();
                } else {
                    $('#transfer-fields').hide();
                }
            }
        },

        // Update stock
        updateStock: function() {
            var self = this;
            var formData = $('#stock-update-form').serialize();

            // Validate form
            if (!this.validateStockUpdate()) {
                return;
            }

            // Show loading
            if (typeof appLoader !== 'undefined') {
                appLoader.show();
            }

            $.ajax({
                url: this.config.updateStockUrl,
                type: 'POST',
                data: formData + '&csrf_token=' + getCsrfToken(),
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        appAlert.success(response.message || 'Stock updated successfully');
                        
                        // Update stock displays
                        $(self.config.stockLevelInput).val(response.new_stock_level);
                        self.calculateAvailableStock();
                        self.checkLowStock();
                        
                        // Clear form
                        self.clearTransactionForm();
                        
                        // Reload transaction history
                        self.loadTransactionHistory();
                        
                        // Trigger event for other modules
                        $(document).trigger('stockUpdated', {
                            itemId: $(self.config.itemSelect).val(),
                            warehouseId: $(self.config.warehouseSelect).val(),
                            newStock: response.new_stock_level
                        });
                    } else {
                        self.showError(response.message || 'Failed to update stock');
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

        // Validate stock update form
        validateStockUpdate: function() {
            var quantity = parseInt($(this.config.quantityInput).val()) || 0;
            var transactionType = $(this.config.transactionTypeSelect).val();
            var currentStock = parseInt($(this.config.stockLevelInput).val()) || 0;
            var typeInfo = this.transactionTypes[transactionType];

            // Clear previous errors
            this.clearErrors();

            // Validate quantity
            if (quantity <= 0) {
                this.showError(AppLang.quantity_required || 'Please enter a valid quantity');
                return false;
            }

            // Check if removing more than available
            if (typeInfo && typeInfo.modifier < 0) {
                var availableStock = $(this.config.availableStockDisplay).data('stock') || 0;
                if (quantity > availableStock) {
                    this.showError(AppLang.insufficient_stock || 'Insufficient stock available');
                    return false;
                }
            }

            // Validate notes for adjustments
            if (transactionType.includes('adjustment') && !$(this.config.notesTextarea).val().trim()) {
                this.showError(AppLang.adjustment_notes_required || 'Please provide notes for stock adjustment');
                return false;
            }

            return true;
        },

        // Quick stock adjustment
        quickAdjustStock: function(adjustment) {
            var currentQuantity = parseInt($(this.config.quantityInput).val()) || 0;
            var newQuantity = currentQuantity + adjustment;
            
            if (newQuantity < 0) {
                newQuantity = 0;
            }
            
            $(this.config.quantityInput).val(newQuantity);
        },

        // Load transaction history
        loadTransactionHistory: function() {
            var self = this;
            var itemId = $(this.config.itemSelect).val();
            var warehouseId = $(this.config.warehouseSelect).val();

            if (!itemId || !$(this.config.stockHistoryTable).length) {
                return;
            }

            $.ajax({
                url: this.config.transactionHistoryUrl,
                type: 'POST',
                data: {
                    item_id: itemId,
                    warehouse_id: warehouseId,
                    limit: 10,
                    csrf_token: getCsrfToken()
                },
                success: function(response) {
                    if (response.success && response.transactions) {
                        self.updateTransactionHistory(response.transactions);
                    }
                },
                error: function() {
                    // Silently fail for history loading
                }
            });
        },

        // Update transaction history display
        updateTransactionHistory: function(transactions) {
            var self = this;
            var $tbody = $(this.config.stockHistoryTable).find('tbody');
            
            if (!$tbody.length) {
                return;
            }

            $tbody.empty();

            if (transactions.length === 0) {
                $tbody.html('<tr><td colspan="6" class="text-center">' + 
                    (AppLang.no_transactions || 'No transactions found') + '</td></tr>');
                return;
            }

            $.each(transactions, function(index, transaction) {
                var typeInfo = self.transactionTypes[transaction.type] || {};
                var row = '<tr>';
                row += '<td>' + transaction.date + '</td>';
                row += '<td><span class="' + (typeInfo.class || '') + '">' + 
                       (typeInfo.label || transaction.type) + '</span></td>';
                row += '<td class="text-center">' + transaction.quantity + '</td>';
                row += '<td class="text-center">' + transaction.stock_before + '</td>';
                row += '<td class="text-center"><strong>' + transaction.stock_after + '</strong></td>';
                row += '<td>' + (transaction.notes || '-') + '</td>';
                row += '</tr>';
                
                $tbody.append(row);
            });
        },

        // Check real-time stock (for multi-user environments)
        checkRealTimeStock: function() {
            var self = this;
            var itemId = $(this.config.itemSelect).val();
            var warehouseId = $(this.config.warehouseSelect).val();

            if (!itemId) {
                return;
            }

            $.ajax({
                url: this.config.getStockUrl,
                type: 'POST',
                data: {
                    item_id: itemId,
                    warehouse_id: warehouseId,
                    check_only: true,
                    csrf_token: getCsrfToken()
                },
                success: function(response) {
                    if (response.success && response.current_stock !== parseInt($(self.config.stockLevelInput).val())) {
                        // Stock has changed, update display
                        $(self.config.stockLevelInput).val(response.current_stock);
                        self.calculateAvailableStock();
                        self.checkLowStock();
                        
                        // Show notification
                        if (typeof appAlert !== 'undefined') {
                            appAlert.info(AppLang.stock_updated || 'Stock levels have been updated');
                        }
                    }
                },
                error: function() {
                    // Silently fail for real-time checks
                }
            });
        },

        // Clear transaction form
        clearTransactionForm: function() {
            $(this.config.quantityInput).val('');
            $(this.config.notesTextarea).val('');
            $(this.config.transactionTypeSelect).val('').trigger('change');
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

        // Get current stock info
        getStockInfo: function() {
            return {
                currentStock: parseInt($(this.config.stockLevelInput).val()) || 0,
                reservedStock: parseInt($(this.config.reservedStockInput).val()) || 0,
                availableStock: $(this.config.availableStockDisplay).data('stock') || 0,
                lowStockThreshold: this.getLowStockThreshold()
            };
        },

        // Public method to reserve stock
        reserveStock: function(quantity) {
            var currentReserved = parseInt($(this.config.reservedStockInput).val()) || 0;
            var newReserved = currentReserved + quantity;
            
            $(this.config.reservedStockInput).val(newReserved).trigger('change');
        },

        // Public method to release reserved stock
        releaseReservedStock: function(quantity) {
            var currentReserved = parseInt($(this.config.reservedStockInput).val()) || 0;
            var newReserved = Math.max(0, currentReserved - quantity);
            
            $(this.config.reservedStockInput).val(newReserved).trigger('change');
        }
    };

    // Initialize if inventory elements exist
    if ($(InventoryManagement.config.stockLevelInput).length > 0) {
        InventoryManagement.init();
        
        // Make InventoryManagement available globally
        window.InventoryManagement = InventoryManagement;
    }

    // Integration with Rise CRM's modal forms
    $(document).on('ajaxSuccess', function(event, xhr, settings) {
        // Re-initialize after AJAX content loads
        if ($(InventoryManagement.config.stockLevelInput).length > 0 &&
            !$(InventoryManagement.config.stockLevelInput).data('im-initialized')) {
            InventoryManagement.init();
            $(InventoryManagement.config.stockLevelInput).data('im-initialized', true);
        }
    });

    // Integration with order/invoice creation
    $(document).on('itemAdded', function(event, data) {
        // Auto-reserve stock when item is added to order/invoice
        if (InventoryManagement && data.itemId && data.quantity) {
            // Check if we should auto-reserve
            if (AppHelper.settings.autoReserveStock) {
                InventoryManagement.reserveStock(data.quantity);
            }
        }
    });
});

// Helper function to get CSRF token (Rise CRM pattern)
function getCsrfToken() {
    return $('[name="csrf_token"]').val() || AppHelper.csrfToken || '';
}