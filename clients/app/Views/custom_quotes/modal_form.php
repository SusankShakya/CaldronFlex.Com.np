<?php echo form_open(get_uri("custom_quotes/save"), array("id" => "custom-quote-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        
        <div class="form-group">
            <div class="row">
                <label for="client_id" class="<?php echo $label_column; ?>"><?php echo app_lang('client'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_dropdown("client_id", $clients_dropdown, array($model_info->client_id), "class='select2 validate-hidden' id='client_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="item_id" class="<?php echo $label_column; ?>"><?php echo app_lang('item'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_dropdown("item_id", $items_dropdown, array($model_info->item_id), "class='select2 validate-hidden' id='item_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="quantity" class="<?php echo $label_column; ?>"><?php echo app_lang('quantity'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "quantity",
                        "name" => "quantity",
                        "value" => $model_info->quantity ?: 1,
                        "class" => "form-control",
                        "placeholder" => app_lang('quantity'),
                        "type" => "number",
                        "min" => "1",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="area" class="<?php echo $label_column; ?>"><?php echo app_lang('area_sqft'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "area",
                        "name" => "area",
                        "value" => $model_info->area,
                        "class" => "form-control",
                        "placeholder" => app_lang('area_sqft'),
                        "type" => "number",
                        "step" => "0.01",
                        "min" => "0"
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="base_price" class="<?php echo $label_column; ?>"><?php echo app_lang('base_price'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "base_price",
                        "name" => "base_price",
                        "value" => $model_info->base_price,
                        "class" => "form-control",
                        "placeholder" => app_lang('base_price'),
                        "readonly" => true
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="custom_price" class="<?php echo $label_column; ?>"><?php echo app_lang('custom_price'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "custom_price",
                        "name" => "custom_price",
                        "value" => $model_info->custom_price,
                        "class" => "form-control",
                        "placeholder" => app_lang('custom_price'),
                        "type" => "number",
                        "step" => "0.01",
                        "min" => "0",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="discount_percentage" class="<?php echo $label_column; ?>"><?php echo app_lang('discount_percentage'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <div class="input-group">
                        <?php
                        echo form_input(array(
                            "id" => "discount_percentage",
                            "name" => "discount_percentage",
                            "value" => $model_info->discount_percentage,
                            "class" => "form-control",
                            "placeholder" => app_lang('discount_percentage'),
                            "type" => "number",
                            "step" => "0.01",
                            "min" => "0",
                            "max" => "100"
                        ));
                        ?>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="valid_until" class="<?php echo $label_column; ?>"><?php echo app_lang('valid_until'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_input(array(
                        "id" => "valid_until",
                        "name" => "valid_until",
                        "value" => $model_info->valid_until,
                        "class" => "form-control",
                        "placeholder" => app_lang('valid_until'),
                        "autocomplete" => "off",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="notes" class="<?php echo $label_column; ?>"><?php echo app_lang('notes'); ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                    echo form_textarea(array(
                        "id" => "notes",
                        "name" => "notes",
                        "value" => $model_info->notes,
                        "class" => "form-control",
                        "placeholder" => app_lang('notes'),
                        "rows" => 3
                    ));
                    ?>
                </div>
            </div>
        </div>

        <?php if ($model_info->id) { ?>
            <div class="form-group">
                <div class="row">
                    <label for="status" class="<?php echo $label_column; ?>"><?php echo app_lang('status'); ?></label>
                    <div class="<?php echo $field_column; ?>">
                        <?php
                        $status_dropdown = array(
                            "pending" => app_lang("pending"),
                            "approved" => app_lang("approved"),
                            "rejected" => app_lang("rejected"),
                            "expired" => app_lang("expired")
                        );
                        echo form_dropdown("status", $status_dropdown, array($model_info->status), "class='select2' id='status'");
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="form-group">
            <div class="row">
                <div class="<?php echo $label_column; ?>"></div>
                <div class="<?php echo $field_column; ?>">
                    <button type="button" class="btn btn-info" id="calculate-price-btn">
                        <i data-feather="calculator" class="icon-16"></i> <?php echo app_lang('calculate_price'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div id="price-breakdown" class="mt15" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h6 class="float-start"><?php echo app_lang('price_breakdown'); ?></h6>
                </div>
                <div class="card-body" id="price-breakdown-content">
                    <!-- Price breakdown will be loaded here -->
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#custom-quote-form").appForm({
            onSuccess: function (result) {
                $("#custom-quotes-table").appTable({newData: result.data, dataId: result.id});
            }
        });

        $("#client_id").select2();
        $("#item_id").select2();
        $("#status").select2();

        setDatePicker("#valid_until");

        // Calculate price button
        $("#calculate-price-btn").click(function () {
            var itemId = $("#item_id").val();
            var clientId = $("#client_id").val();
            var quantity = $("#quantity").val();
            var area = $("#area").val();

            if (!itemId || !clientId || !quantity) {
                appAlert.error("<?php echo app_lang('please_fill_required_fields'); ?>");
                return;
            }

            appLoader.show();

            $.ajax({
                url: "<?php echo get_uri('items/calculate_price'); ?>",
                type: 'POST',
                dataType: 'json',
                data: {
                    item_id: itemId,
                    client_id: clientId,
                    quantity: quantity,
                    area: area
                },
                success: function (result) {
                    appLoader.hide();
                    if (result.success) {
                        $("#base_price").val(result.price_data.final_price);
                        
                        // Show price breakdown
                        var breakdownHtml = '<table class="table table-sm">';
                        breakdownHtml += '<tr><td><?php echo app_lang("base_price"); ?>:</td><td class="text-right">' + result.price_data.base_price + '</td></tr>';
                        
                        if (result.price_data.rules_applied && result.price_data.rules_applied.length > 0) {
                            breakdownHtml += '<tr><td colspan="2"><strong><?php echo app_lang("rules_applied"); ?>:</strong></td></tr>';
                            result.price_data.rules_applied.forEach(function(rule) {
                                breakdownHtml += '<tr><td class="ps-3">' + rule.name + ':</td><td class="text-right">' + rule.adjustment + '</td></tr>';
                            });
                        }
                        
                        breakdownHtml += '<tr class="border-top"><td><strong><?php echo app_lang("final_price"); ?>:</strong></td><td class="text-right"><strong>' + result.price_data.final_price + '</strong></td></tr>';
                        breakdownHtml += '</table>';
                        
                        $("#price-breakdown-content").html(breakdownHtml);
                        $("#price-breakdown").show();
                        
                        // Update custom price if empty
                        if (!$("#custom_price").val()) {
                            $("#custom_price").val(result.price_data.final_price);
                        }
                    } else {
                        appAlert.error(result.message);
                    }
                },
                error: function () {
                    appLoader.hide();
                    appAlert.error("<?php echo app_lang('something_went_wrong'); ?>");
                }
            });
        });

        // Auto-calculate discount when custom price changes
        $("#custom_price").on("input", function () {
            var basePrice = parseFloat($("#base_price").val()) || 0;
            var customPrice = parseFloat($(this).val()) || 0;
            
            if (basePrice > 0 && customPrice > 0) {
                var discount = ((basePrice - customPrice) / basePrice) * 100;
                $("#discount_percentage").val(discount.toFixed(2));
            }
        });

        // Auto-calculate custom price when discount changes
        $("#discount_percentage").on("input", function () {
            var basePrice = parseFloat($("#base_price").val()) || 0;
            var discount = parseFloat($(this).val()) || 0;
            
            if (basePrice > 0) {
                var customPrice = basePrice * (1 - discount / 100);
                $("#custom_price").val(customPrice.toFixed(2));
            }
        });
    });
</script>