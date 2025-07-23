<?php echo form_open(get_uri("settings/save_invoice_general_settings"), array("id" => "invoice-general-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

<div class="card-body">
    <div class="form-group">
        <div class="row">
            <label for="default_due_date_after_billing_date" class="col-md-4 col-sm-4 col-xs-12"><?php echo app_lang('default_due_date_after_billing_date'); ?></label>
            <div class="col-md-3 col-sm-7 col-xs-12">
                <?php
                echo form_input(array(
                    "id" => "default_due_date_after_billing_date",
                    "name" => "default_due_date_after_billing_date",
                    "type" => "number",
                    "value" => get_setting("default_due_date_after_billing_date"),
                    "class" => "form-control mini",
                    "min" => 0
                ));
                ?>
            </div>
            <label class="col-md-1 col-sm-1 col-xs-12 mt5"><?php echo app_lang('days'); ?></label>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label for="send_bcc_to" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('send_bcc_to'); ?></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_input(array(
                    "id" => "send_bcc_to",
                    "name" => "send_bcc_to",
                    "value" => get_setting("send_bcc_to"),
                    "class" => "form-control",
                    "placeholder" => app_lang("email")
                ));
                ?>
            </div>
        </div>
    </div>
    <div class="form-group form-switch">
        <div class="row">
            <label for="allow_partial_invoice_payment_from_clients" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('allow_partial_invoice_payment_from_clients'); ?></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_checkbox("allow_partial_invoice_payment_from_clients", "1", get_setting("allow_partial_invoice_payment_from_clients") ? true : false, "id='allow_partial_invoice_payment_from_clients' class='form-check-input'");
                ?>
            </div>
        </div>
    </div>

    <div class="form-group form-switch">
        <div class="row">
            <label for="client_can_pay_invoice_without_login" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('client_can_pay_invoice_without_login'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('client_can_pay_invoice_without_login_help_message'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_checkbox("client_can_pay_invoice_without_login", "1", get_setting("client_can_pay_invoice_without_login") ? true : false, "id='client_can_pay_invoice_without_login' class='form-check-input'");
                ?>
            </div>
        </div>
    </div>
    <div class="form-group form-switch">
        <div class="row">
            <label for="enable_invoice_lock_state" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('enable_lock_state'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('invoice_lock_state_description'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_checkbox("enable_invoice_lock_state", "1", get_setting("enable_invoice_lock_state") ? true : false, "id='enable_invoice_lock_state' class='form-check-input'");
                ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <label for="type" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('generate_reports_based_on'); ?></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_radio(array(
                    "id" => "generate_reports_based_on_bill_date",
                    "name" => "generate_reports_based_on",
                    "class" => "form-check-input",
                    "data-msg-required" => app_lang("field_required"),
                ), "bill_date", (get_setting("generate_reports_based_on") === "bill_date" ? true : false));
                ?>
                <label for="generate_reports_based_on_bill_date" class="mr15"><?php echo app_lang('bill_date'); ?></label>

                <?php
                echo form_radio(array(
                    "id" => "generate_reports_based_on_due_date",
                    "name" => "generate_reports_based_on",
                    "class" => "form-check-input",
                    "data-msg-required" => app_lang("field_required"),
                ), "due_date", (get_setting("generate_reports_based_on") === "due_date" ? true : (!get_setting("generate_reports_based_on") ? true : false))); // show due date by default
                ?>
                <label for="generate_reports_based_on_due_date" class="mr15"><?php echo app_lang('due_date'); ?></label>
            </div>
        </div>
    </div>

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#invoice-general-settings-form").appForm({
            isModal: false,
            onSuccess: function(result) {
                if (result.success) {
                    appAlert.success(result.message, {
                        duration: 10000
                    });
                } else {
                    appAlert.error(result.message);
                }
            }
        });

        $("#invoice-general-settings-form .select2").select2();
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>