<?php echo form_open(get_uri("invoices/send_invoice"), array("id" => "send-invoice-form", "class" => "general-form", "role" => "form")); ?>
<div id="send_invoice-dropzone" class="post-dropzone">
    <div class="modal-body clearfix">
        <div class="container-fluid">
            <input type="hidden" name="id" value="<?php echo $invoice_info->id; ?>" />
            <input type="hidden" name="user_language" id="user_language" value="<?php echo $user_language; ?>" />

            <div class="form-group">
                <div class="row">
                    <label for="contact_id" class=" col-md-3"><?php echo app_lang('to'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_dropdown("contact_id", $contacts_dropdown, array(), "class='select2 validate-hidden' id='contact_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="invoice_cc" class=" col-md-3">CC</label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "invoice_cc",
                            "name" => "invoice_cc",
                            "value" => "",
                            "class" => "form-control",
                            "placeholder" => "CC"
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="invoice_bcc" class=" col-md-3">BCC</label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "invoice_bcc",
                            "name" => "invoice_bcc",
                            "value" => "",
                            "class" => "form-control",
                            "placeholder" => "BCC"
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="subject" class=" col-md-3"><?php echo app_lang("subject"); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "subject",
                            "name" => "subject",
                            "value" => $subject,
                            "class" => "form-control",
                            "placeholder" => app_lang("subject")
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class=" col-md-12">
                        <?php
                        echo form_textarea(array(
                            "id" => "message",
                            "name" => "message",
                            "value" => process_images_from_content($message, false),
                            "class" => "form-control",
                            "data-height" => 400,
                            "data-toolbar" => "no_toolbar",
                            "data-encode_ajax_post_data" => "1"
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group ml15">
                <i data-feather="check-circle" class='icon-16' style="color: #5CB85C;"></i> <?php echo app_lang('attached') . ' ' . anchor(get_uri("invoices/download_pdf/" . $invoice_info->id . "/download/$user_language"), get_hyphenated_string($invoice_info->display_id) . ".pdf", array("target" => "_blank", "id" => "attachment-url")); ?>
            </div>
            <?php if (get_setting("send_e_invoice_attachment_with_email")) { ?>
                <input type="hidden" name="attached_xml" value="1" id="attached_xml" />
                <div class="form-group ml15 mb-1 text-off">
                    <i data-feather="help-circle" class='icon-16'></i> <?php echo app_lang('you_can_validate_the_xml_file_before_sending_it'); ?>
                </div>
                <div class="form-group ml15">
                    <span class="xml-attachment-url"><i data-feather="check-circle" class='icon-16' style="color: #5CB85C;"></i> <?php echo app_lang('attached'); ?></span><?php echo " " . anchor(get_uri("invoices/download_xml/" . $invoice_info->id), get_hyphenated_string($invoice_info->display_id) . ".xml", array("target" => "_blank", "id" => "attachment-url")); ?>
                    <span class="unlink-xml-btn clickable" title="<?php echo app_lang('unlink_xml_attachment'); ?>"><i data-feather="minus-circle" class='icon-16' style="color: #f5325c;"></i></span>
                </div>
            <?php } ?>

            <?php echo view("includes/dropzone_preview"); ?>
        </div>
    </div>

    <div class="modal-footer">
        <?php echo view("includes/upload_button"); ?>
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span data-feather="send" class="icon-16"></span> <?php echo app_lang('send'); ?></button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#send-invoice-form .select2').select2();
        $("#send-invoice-form").appForm({
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

        initWYSIWYGEditor("#message");

        //load template view on changing of client contact
        $("#contact_id").select2().on("change", function() {
            var contact_id = $(this).val();
            if (contact_id) {
                appLoader.show();
                appAjaxRequest({
                    url: "<?php echo get_uri('invoices/get_send_invoice_template/' . $invoice_info->id) ?>" + "/" + contact_id + "/json",
                    dataType: "json",
                    success: function(result) {
                        if (result.success) {
                            setWYSIWYGEditorHTML("#message", result.message_view);

                            $("#user_language").val(result.user_language);
                            $("#attachment-url").attr("href", "<?php echo get_uri('invoices/download_pdf/' . $invoice_info->id . '/download/'); ?>" + result.user_language);

                            appLoader.hide();
                        }
                    }
                });
            }
        });

        $('#invoice_cc').select2({
            tags: <?php echo json_encode($cc_contacts_dropdown); ?>
        });

        $(".unlink-xml-btn").click(function() {
            $("#attached_xml").val("");
            $(".xml-attachment-url").html("<i data-feather='x-circle' class='icon-16' style='color: #f5325c;'></i> <?php echo app_lang('unattached'); ?>").addClass("text-off");
            $(this).closest(".form-group").find("a").addClass("text-off");
            feather.replace();
        });

    });
</script>