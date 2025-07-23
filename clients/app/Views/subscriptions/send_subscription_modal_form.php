<?php echo form_open(get_uri("subscriptions/send_subscription"), array("id" => "send-subscription-form", "class" => "general-form", "role" => "form")); ?>
<div id="send_subscription-dropzone" class="post-dropzone">
    <div class="modal-body clearfix">
        <div class="container-fluid">
            <input type="hidden" name="id" value="<?php echo $subscription_info->id; ?>" />

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
                    <label for="subscription_cc" class=" col-md-3">CC</label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "subscription_cc",
                            "name" => "subscription_cc",
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
                    <label for="subscription_bcc" class=" col-md-3">BCC</label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "subscription_bcc",
                            "name" => "subscription_bcc",
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
                            "value" => $message,
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
                <i data-feather="check-circle" class='icon-16' style="color: #5CB85C;"></i> <?php echo app_lang('attached') . ' ' . anchor(get_uri("subscriptions/download_pdf/" . $subscription_info->id), get_hyphenated_string(get_subscription_id($subscription_info->id)) . ".pdf", array("target" => "_blank")); ?> 
            </div>

            <?php echo view("includes/dropzone_preview"); ?>
        </div>
    </div>


    <div class="modal-footer">
        <?php
        echo view("includes/upload_button", array("upload_button_text" => app_lang("add_attachment")));
        ?>

        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span data-feather="send" class="icon-16"></span> <?php echo app_lang('send'); ?></button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {

        $('#send-subscription-form .select2').select2();
        $("#send-subscription-form").appForm({
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                    if (typeof updateSubscriptionStatusBar == 'function') {
                        updateSubscriptionStatusBar(result.subscription_id);
                    }

                } else {
                    appAlert.error(result.message);
                }
            }
        });

        initWYSIWYGEditor("#message");

        //load template view on changing of client contact
        $("#contact_id").select2().on("change", function () {
            var contact_id = $(this).val();
            if (contact_id) {

                appLoader.show();
                appAjaxRequest({
                    url: "<?php echo get_uri('subscriptions/get_send_subscription_template/' . $subscription_info->id) ?>" + "/" + contact_id + "/json",
                    dataType: "json",
                    success: function (result) {
                        if (result.success) {
                            setWYSIWYGEditorHTML("#message", result.message_view);
                            appLoader.hide();
                        }
                    }
                });
            }
        });

        $('#subscription_cc').select2({
            tags: <?php echo json_encode($cc_contacts_dropdown); ?>
        });

    });
</script>