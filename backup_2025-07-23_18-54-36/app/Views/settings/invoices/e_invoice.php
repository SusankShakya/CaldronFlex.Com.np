<?php echo form_open(get_uri("settings/save_e_invoice_settings"), array("id" => "e-invoice-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

<div class="card-body">
    <div class="form-group form-switch">
        <div class="row">
            <label for="enable_e_invoice" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('enable_e_invoice'); ?> </label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_checkbox("enable_e_invoice", "1", get_setting("enable_e_invoice") ? true : false, "id='enable_e_invoice' class='form-check-input'");
                ?>
            </div>
        </div>
    </div>
    <div class="e_invoice_settings_section <?php echo get_setting("enable_e_invoice") ? "" : "hide" ?>">
        <div class="form-group">
            <div class="row">
                <label for="default_e_invoice_template" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('default_e_invoice_template'); ?></label>
                <div class="col-md-8 col-xs-4 col-sm-8">
                    <?php
                    echo form_dropdown("default_e_invoice_template", $e_invoice_templates_dropdown, get_setting("default_e_invoice_template"), "class='select2' id='default_e_invoice_template'");
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="default_e_invoice_template_for_credit_note" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('default_e_invoice_template_for_credit_note'); ?></label>
                <div class="col-md-8 col-xs-4 col-sm-8">
                    <?php
                    echo form_dropdown("default_e_invoice_template_for_credit_note", $e_invoice_templates_dropdown_for_credit_note, get_setting("default_e_invoice_template_for_credit_note"), "class='select2' id='default_e_invoice_template_for_credit_note'");
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group form-switch">
            <div class="row">
                <label for="send_e_invoice_attachment_with_email" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('send_e_invoice_attachment_with_email'); ?> </label>
                <div class="col-md-8 col-xs-4 col-sm-8">
                    <?php
                    echo form_checkbox("send_e_invoice_attachment_with_email", "1", get_setting("send_e_invoice_attachment_with_email") ? true : false, "id='send_e_invoice_attachment_with_email' class='form-check-input'");
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="p20 b-t b-b">
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>

<?php echo form_close(); ?>


<div class="e_invoice_settings_section <?php echo get_setting("enable_e_invoice") ? "" : "hide" ?>">
    <div class="card-header clearfix">
        <div class="fw-bold float-start pt10"><i data-feather="file-text" class="icon-16"></i> &nbsp;<?php echo app_lang("e_invoice_templates"); ?></div>
        <div class="float-end">
            <?php echo modal_anchor(get_uri("e_invoice_templates/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_template'), array("class" => "btn btn-default mt5", "title" => app_lang('add_template'), "data-modal-fullscreen" => 1)); ?>
        </div>
    </div>
    <div class="card-body">
        <?php echo view("e_invoice_templates/index"); ?>
    </div>
</div>



<script type="text/javascript">
    $(document).ready(function() {
        $("#e-invoice-settings-form").appForm({
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

        $("#e-invoice-settings-form .select2").select2();

        //show/hide default e-invoice template section
        $("#enable_e_invoice").click(function() {
            if ($(this).is(":checked")) {
                $(".e_invoice_settings_section").removeClass("hide");
            } else {
                $(".e_invoice_settings_section").addClass("hide");
            }
        });
    });
</script>