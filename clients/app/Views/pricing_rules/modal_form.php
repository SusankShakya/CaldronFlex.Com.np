<?php echo form_open(get_uri("pricing_rules/save"), array("id" => "pricing-rule-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

    <div class="form-group">
        <label for="item_id" class=" col-md-3"><?php echo app_lang('item'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_dropdown("item_id", $items_dropdown, array($model_info->item_id), "class='select2 validate-hidden' id='item_id' data-rule-required='true' data-msg-required='" . app_lang('field_required') . "'");
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="rule_type" class=" col-md-3"><?php echo app_lang('rule_type'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_dropdown("rule_type", array(
                "quantity_tier" => app_lang("quantity_tier"),
                "customer_specific" => app_lang("customer_specific"),
                "area_based" => app_lang("area_based")
            ), array($model_info->rule_type), "class='select2 validate-hidden' id='rule_type' data-rule-required='true' data-msg-required='" . app_lang('field_required') . "'");
            ?>
        </div>
    </div>

    <div id="quantity-tier-fields">
        <div class="form-group">
            <label for="min_quantity" class=" col-md-3"><?php echo app_lang('min_quantity'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_input(array(
                    "id" => "min_quantity",
                    "name" => "min_quantity",
                    "value" => $model_info->min_quantity ? $model_info->min_quantity : 0,
                    "class" => "form-control",
                    "placeholder" => app_lang('min_quantity'),
                    "type" => "number"
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="max_quantity" class=" col-md-3"><?php echo app_lang('max_quantity'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_input(array(
                    "id" => "max_quantity",
                    "name" => "max_quantity",
                    "value" => $model_info->max_quantity ? $model_info->max_quantity : 0,
                    "class" => "form-control",
                    "placeholder" => app_lang('max_quantity'),
                    "type" => "number"
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="discount_type" class=" col-md-3"><?php echo app_lang('discount_type'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_dropdown("discount_type", array(
                    "percentage" => app_lang("percentage"),
                    "fixed_amount" => app_lang("fixed_amount")
                ), array($model_info->discount_type), "class='select2 validate-hidden' id='discount_type'");
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="discount_amount" class=" col-md-3"><?php echo app_lang('discount_amount'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_input(array(
                    "id" => "discount_amount",
                    "name" => "discount_amount",
                    "value" => $model_info->discount_amount ? $model_info->discount_amount : 0,
                    "class" => "form-control",
                    "placeholder" => app_lang('discount_amount'),
                    "type" => "number"
                ));
                ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="valid_from" class="col-md-3"><?php echo app_lang('valid_from'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "valid_from",
                "name" => "valid_from",
                "value" => $model_info->valid_from,
                "class" => "form-control",
                "placeholder" => "YYYY-MM-DD"
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="valid_until" class="col-md-3"><?php echo app_lang('valid_until'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "valid_until",
                "name" => "valid_until",
                "value" => $model_info->valid_until,
                "class" => "form-control",
                "placeholder" => "YYYY-MM-DD"
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-9">
            <?php
            echo form_checkbox("status", "1", $model_info->status ? true : false, "id='status' class='form-check-input'");
            ?>
            <label for="status" class=""><?php echo app_lang('active'); ?></label>
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
        $("#pricing-rule-form").appForm({
            onSuccess: function (result) {
                $("#pricing-rules-table").appTable({newData: result.data, dataId: result.id});
            }
        });

        $("#pricing-rule-form .select2").select2();
        
        set_datetime_picker("#valid_from, #valid_until");

        // Show/hide fields based on rule type
        var ruleType = "<?php echo $model_info->rule_type; ?>";
        toggleRuleTypeFields(ruleType);

        $("#rule_type").on("change", function () {
            toggleRuleTypeFields($(this).val());
        });

        function toggleRuleTypeFields(ruleType) {
            if (ruleType === "quantity_tier") {
                $("#quantity-tier-fields").show();
            } else {
                $("#quantity-tier-fields").hide();
            }
        }
    });
</script>