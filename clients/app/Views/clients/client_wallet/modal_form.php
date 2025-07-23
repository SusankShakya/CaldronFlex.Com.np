<?php echo form_open(get_uri("invoice_payments/save_client_wallet_amount"), array("id" => "client-wallet-amount-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>" />

        <div class="form-group">
            <div class="row">
                <label for="client_wallet_payment_date" class=" col-md-3"><?php echo app_lang('payment_date'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "client_wallet_payment_date",
                        "name" => "client_wallet_payment_date",
                        "value" => $model_info->payment_date ? $model_info->payment_date : get_my_local_time("Y-m-d"),
                        "class" => "form-control",
                        "placeholder" => app_lang('payment_date'),
                        "autocomplete" => "off",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required")
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="client_wallet_amount" class=" col-md-3"><?php echo app_lang('amount'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "client_wallet_amount",
                        "name" => "client_wallet_amount",
                        "value" => $model_info->amount ? to_decimal_format($model_info->amount) : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('amount'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="client_wallet_note" class="col-md-3"><?php echo app_lang('note'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_textarea(array(
                        "id" => "client_wallet_note",
                        "name" => "client_wallet_note",
                        "value" => $model_info->note ? process_images_from_content($model_info->note, false) : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('description'),
                        "data-rich-text-editor" => true
                    ));
                    ?>
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
    $(document).ready(function() {
        $("#client-wallet-amount-form").appForm({
            onSuccess: function(result) {
                $("#client-wallet-table").appTable({newData: result.data, dataId: result.id});
            }
        });

        setDatePicker("#client_wallet_payment_date");
    });
</script>