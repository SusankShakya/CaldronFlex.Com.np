<div class="price-calculator-component card">
    <div class="card-header">
        <h5><?php echo app_lang('price_calculator'); ?></h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="calc-item-id"><?php echo app_lang('item'); ?></label>
                    <?php
                    echo form_dropdown("calc_item_id", $items_dropdown, array($selected_item_id), "class='select2' id='calc-item-id' data-rule-required='true'");
                    ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="calc-client-id"><?php echo app_lang('client'); ?></label>
                    <?php
                    echo form_dropdown("calc_client_id", $clients_dropdown, array($selected_client_id), "class='select2' id='calc-client-id'");
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="calc-quantity"><?php echo app_lang('quantity'); ?></label>
                    <?php
                    echo form_input(array(
                        "id" => "calc-quantity",
                        "name" => "calc_quantity",
                        "value" => $quantity ?: 1,
                        "class" => "form-control",
                        "placeholder" => app_lang('quantity'),
                        "type" => "number",
                        "min" => "1",
                        "data-rule-required" => true
                    ));
                    ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="calc-area"><?php echo app_lang('area_sqft'); ?></label>
                    <div class="input-group">
                        <?php
                        echo form_input(array(
                            "id" => "calc-area",
                            "name" => "calc_area",
                            "value" => $area,
                            "class" => "form-control",
                            "placeholder" => app_lang('area_sqft'),
                            "type" => "number",
                            "step" => "0.01",
                            "min" => "0"
                        ));
                        ?>
                        <button class="btn btn-outline-secondary" type="button" id="area-calculator-btn" title="<?php echo app_lang('visual_area_calculator'); ?>">
                            <i data-feather="grid" class="icon-16"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variants section (dynamically loaded based on selected item) -->
        <div id="calc-variants-section" style="display: none;">
            <div class="form-group">
                <label><?php echo app_lang('variants'); ?></label>
                <div id="calc-variants-container">
                    <!-- Variants will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Custom fields section -->
        <?php if (isset($custom_fields) && $custom_fields) { ?>
            <div class="form-group">
                <label><?php echo app_lang('custom_fields'); ?></label>
                <?php foreach ($custom_fields as $field) { ?>
                    <div class="row mb-2">
                        <label class="col-md-4"><?php echo $field->title; ?></label>
                        <div class="col-md-8">
                            <?php echo $this->template->view("custom_fields/input_" . $field->field_type, array("field" => $field, "field_name" => "calc_custom_field_" . $field->id)); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="form-group">
            <button type="button" class="btn btn-primary" id="calculate-price-btn">
                <i data-feather="calculator" class="icon-16"></i> <?php echo app_lang('calculate_price'); ?>
            </button>
            <button type="button" class="btn btn-secondary" id="get-custom-quote-btn" style="display: none;">
                <i data-feather="file-text" class="icon-16"></i> <?php echo app_lang('request_custom_quote'); ?>
            </button>
        </div>

        <!-- Price display section - Modified to show price range -->
        <div id="price-display-section" style="display: none;">
            <div class="alert alert-info">
                <h6><?php echo app_lang('estimated_price_range'); ?></h6>
                <div class="row">
                    <div class="col-md-12">
                        <div class="price-range-display text-center">
                            <h4>
                                <span id="display-min-price" class="text-success">-</span>
                                <span class="mx-2">-</span>
                                <span id="display-max-price" class="text-success">-</span>
                            </h4>
                            <small class="text-muted"><?php echo app_lang('final_price_will_be_determined_at_invoice'); ?></small>
                        </div>
                    </div>
                </div>
                
                <!-- Show calculated price for staff only -->
                <?php if (isset($show_exact_price) && $show_exact_price) { ?>
                <div class="mt-3 pt-3 border-top">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><?php echo app_lang('base_price'); ?>:</strong> <span id="display-base-price">-</span>
                        </div>
                        <div class="col-md-6">
                            <strong><?php echo app_lang('calculated_price'); ?>:</strong> <span id="display-calculated-price">-</span>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <div id="price-rules-applied" class="mt-2" style="display: none;">
                    <small class="text-muted"><?php echo app_lang('pricing_factors_applied'); ?>: <span id="applied-rules-list"></span></small>
                </div>
            </div>

            <!-- Price breakdown table (for staff only) -->
            <?php if (isset($show_breakdown) && $show_breakdown) { ?>
            <div id="price-breakdown-section" style="display: none;">
                <h6><?php echo app_lang('price_breakdown'); ?></h6>
                <div class="table-responsive">
                    <table class="table table-sm" id="price-breakdown-table">
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Loading indicator -->
        <div id="price-calculator-loading" style="display: none;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden"><?php echo app_lang('loading'); ?>...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Area Calculator Modal -->
<div class="modal fade" id="area-calculator-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo app_lang('visual_area_calculator'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="area-calculator-container">
                    <!-- Area calculator will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo app_lang('cancel'); ?></button>
                <button type="button" class="btn btn-primary" id="apply-calculated-area"><?php echo app_lang('apply'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var priceCalculator = {
            init: function() {
                this.bindEvents();
                this.initializeSelects();
            },

            bindEvents: function() {
                $("#calc-item-id").on("change", this.onItemChange.bind(this));
                $("#calculate-price-btn").on("click", this.calculatePrice.bind(this));
                $("#get-custom-quote-btn").on("click", this.requestCustomQuote.bind(this));
                $("#area-calculator-btn").on("click", this.openAreaCalculator.bind(this));
                $("#apply-calculated-area").on("click", this.applyCalculatedArea.bind(this));
            },

            initializeSelects: function() {
                $("#calc-item-id").select2();
                $("#calc-client-id").select2();
            },

            onItemChange: function() {
                var itemId = $("#calc-item-id").val();
                if (itemId) {
                    this.loadItemVariants(itemId);
                } else {
                    $("#calc-variants-section").hide();
                }
            },

            loadItemVariants: function(itemId) {
                // Load variants for the selected item
                $.ajax({
                    url: "<?php echo get_uri('items/get_item_variants'); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {item_id: itemId},
                    success: function(result) {
                        if (result.success && result.variants && result.variants.length > 0) {
                            var html = '';
                            result.variants.forEach(function(variant) {
                                html += '<div class="row mb-2">';
                                html += '<label class="col-md-4">' + variant.name + '</label>';
                                html += '<div class="col-md-8">';
                                html += '<select class="form-control variant-select" data-variant-id="' + variant.id + '">';
                                html += '<option value="">Select ' + variant.name + '</option>';
                                variant.options.forEach(function(option) {
                                    html += '<option value="' + option + '">' + option + '</option>';
                                });
                                html += '</select>';
                                html += '</div>';
                                html += '</div>';
                            });
                            $("#calc-variants-container").html(html);
                            $("#calc-variants-section").show();
                        } else {
                            $("#calc-variants-section").hide();
                        }
                    }
                });
            },

            calculatePrice: function() {
                var itemId = $("#calc-item-id").val();
                var clientId = $("#calc-client-id").val();
                var quantity = $("#calc-quantity").val();
                var area = $("#calc-area").val();

                if (!itemId || !quantity) {
                    appAlert.error("<?php echo app_lang('please_fill_required_fields'); ?>");
                    return;
                }

                // Collect variant data
                var variantsData = {};
                $(".variant-select").each(function() {
                    var variantId = $(this).data("variant-id");
                    var value = $(this).val();
                    if (value) {
                        variantsData[variantId] = value;
                    }
                });

                // Collect custom fields
                var customFields = {};
                $("[name^='calc_custom_field_']").each(function() {
                    var fieldId = $(this).attr("name").replace("calc_custom_field_", "");
                    customFields[fieldId] = $(this).val();
                });

                $("#price-calculator-loading").show();
                $("#price-display-section").hide();

                $.ajax({
                    url: "<?php echo get_uri('items/calculate_price'); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        item_id: itemId,
                        client_id: clientId,
                        quantity: quantity,
                        area: area,
                        variants_data: JSON.stringify(variantsData),
                        custom_fields: JSON.stringify(customFields)
                    },
                    success: function(result) {
                        $("#price-calculator-loading").hide();
                        if (result.success) {
                            priceCalculator.displayPriceRange(result.price_data);
                            $("#price-display-section").show();
                            $("#get-custom-quote-btn").show();
                        } else {
                            appAlert.error(result.message);
                        }
                    },
                    error: function() {
                        $("#price-calculator-loading").hide();
                        appAlert.error("<?php echo app_lang('something_went_wrong'); ?>");
                    }
                });
            },

            displayPriceRange: function(priceData) {
                // Calculate price range (±15% by default, can be configured)
                var basePrice = parseFloat(priceData.final_price);
                var rangePercentage = priceData.range_percentage || 15;
                var minPrice = basePrice * (1 - rangePercentage / 100);
                var maxPrice = basePrice * (1 + rangePercentage / 100);

                // Display price range
                $("#display-min-price").text(toCurrency(minPrice));
                $("#display-max-price").text(toCurrency(maxPrice));

                // Display exact prices for staff
                <?php if (isset($show_exact_price) && $show_exact_price) { ?>
                $("#display-base-price").text(toCurrency(priceData.base_price));
                $("#display-calculated-price").text(toCurrency(priceData.final_price));
                <?php } ?>

                // Display applied rules
                if (priceData.rules_applied && priceData.rules_applied.length > 0) {
                    var rulesList = priceData.rules_applied.map(function(rule) {
                        return rule.name;
                    }).join(", ");
                    $("#applied-rules-list").text(rulesList);
                    $("#price-rules-applied").show();
                } else {
                    $("#price-rules-applied").hide();
                }

                // Display breakdown for staff
                <?php if (isset($show_breakdown) && $show_breakdown) { ?>
                if (priceData.breakdown) {
                    var breakdownHtml = '';
                    priceData.breakdown.forEach(function(item) {
                        breakdownHtml += '<tr>';
                        breakdownHtml += '<td>' + item.description + '</td>';
                        breakdownHtml += '<td class="text-right">' + toCurrency(item.amount) + '</td>';
                        breakdownHtml += '</tr>';
                    });
                    $("#price-breakdown-table tbody").html(breakdownHtml);
                    $("#price-breakdown-section").show();
                }
                <?php } ?>
            },

            requestCustomQuote: function() {
                var itemId = $("#calc-item-id").val();
                var clientId = $("#calc-client-id").val();
                var quantity = $("#calc-quantity").val();
                var area = $("#calc-area").val();

                // Collect variant data
                var variantsData = {};
                $(".variant-select").each(function() {
                    var variantId = $(this).data("variant-id");
                    var value = $(this).val();
                    if (value) {
                        variantsData[variantId] = value;
                    }
                });

                // Collect custom fields
                var customFields = {};
                $("[name^='calc_custom_field_']").each(function() {
                    var fieldId = $(this).attr("name").replace("calc_custom_field_", "");
                    customFields[fieldId] = $(this).val();
                });

                // Open custom quote modal
                var url = "<?php echo get_uri('custom_quotes/modal_form'); ?>";
                url += "?item_id=" + itemId;
                url += "&client_id=" + clientId;
                url += "&quantity=" + quantity;
                url += "&area=" + area;
                url += "&variants_data=" + encodeURIComponent(JSON.stringify(variantsData));
                url += "&custom_fields=" + encodeURIComponent(JSON.stringify(customFields));

                appModalForm(url, {
                    onSuccess: function() {
                        appAlert.success("<?php echo app_lang('custom_quote_requested_successfully'); ?>");
                    }
                });
            },

            openAreaCalculator: function() {
                // Load area calculator
                $("#area-calculator-modal").modal("show");
                // Initialize area calculator if not already loaded
                if (!this.areaCalculatorInitialized) {
                    this.initializeAreaCalculator();
                }
            },

            initializeAreaCalculator: function() {
                // This will be implemented in area_calculator.js
                if (typeof AreaCalculator !== 'undefined') {
                    this.areaCalculator = new AreaCalculator({
                        container: "#area-calculator-container",
                        unit: "sqft"
                    });
                    this.areaCalculatorInitialized = true;
                }
            },

            applyCalculatedArea: function() {
                if (this.areaCalculator) {
                    var calculatedArea = this.areaCalculator.getTotalArea();
                    $("#calc-area").val(calculatedArea.toFixed(2));
                    $("#area-calculator-modal").modal("hide");
                }
            }
        };

        // Initialize price calculator
        priceCalculator.init();

        // Helper function to format currency
        function toCurrency(amount) {
            return "<?php echo get_setting('currency_symbol'); ?>" + parseFloat(amount).toFixed(2);
        }
    });
</script>