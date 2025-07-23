<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "email";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_email_settings"), array("id" => "email-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="card">
                <div class="card-header">
                    <h4><?php echo app_lang("email_settings"); ?></h4>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <div class="row">
                            <label for="email_protocol" class=" col-md-2"><?php echo app_lang('email_protocol'); ?></label>
                            <div class=" col-md-10">
                                <?php
                                $email_protocols = array(
                                    "mail" => "Mail",
                                    "smtp" => "SMTP",
                                    "microsoft_outlook" => "Microsoft Outlook",
                                    "gmail_smtp" => "Gmail API",
                                );
                                echo form_dropdown(
                                    "email_protocol",
                                    $email_protocols,
                                    get_setting('email_protocol'),
                                    "class='select2 mini' id='email-protocol'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>

                    <div id="email-send-from-name" class="form-group">
                        <div class="row">
                            <label for="email_sent_from_name" class=" col-md-2"><?php echo app_lang('email_sent_from_name'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_input(array(
                                    "id" => "email_sent_from_name",
                                    "name" => "email_sent_from_name",
                                    "value" => get_setting('email_sent_from_name'),
                                    "class" => "form-control",
                                    "placeholder" => "Company Name",
                                    "data-rule-required" => true,
                                    "data-msg-required" => app_lang("field_required"),
                                ));
                                ?>
                            </div>
                        </div>
                    </div>

                    <div id="mail-settings-area" class="form-group">
                        <div class="row">
                            <label for="email_sent_from_address" class=" col-md-2"><?php echo app_lang('email_sent_from_address'); ?></label>
                            <div class=" col-md-10">
                                <?php
                                echo form_input(array(
                                    "id" => "email_sent_from_address",
                                    "name" => "email_sent_from_address",
                                    "value" => get_setting('email_sent_from_address'),
                                    "class" => "form-control",
                                    "placeholder" => "somemail@somedomain.com",
                                    "data-rule-required" => true,
                                    "data-msg-required" => app_lang("field_required"),
                                ));
                                ?>
                            </div>
                        </div>
                    </div>

                    <div id="smtp-settings-area">
                        <div class="form-group">
                            <div class="row">
                                <label for="email_smtp_host" class=" col-md-2"><?php echo app_lang('email_smtp_host'); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "email_smtp_host",
                                        "name" => "email_smtp_host",
                                        "value" => get_setting('email_smtp_host'),
                                        "class" => "form-control",
                                        "placeholder" => app_lang('email_smtp_host'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="email_smtp_user" class=" col-md-2"><?php echo app_lang('email_smtp_user'); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "email_smtp_user",
                                        "name" => "email_smtp_user",
                                        "value" => get_setting('email_smtp_user'),
                                        "class" => "form-control",
                                        "placeholder" => app_lang('email_smtp_user'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="email_smtp_pass" class=" col-md-2"><?php echo app_lang('email_smtp_password'); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_password(array(
                                        "id" => "email_smtp_pass",
                                        "name" => "email_smtp_pass",
                                        "value" => get_setting('email_smtp_pass') ? "******" : "",
                                        "class" => "form-control",
                                        "placeholder" => app_lang('email_smtp_password'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="email_smtp_port" class=" col-md-2"><?php echo app_lang('email_smtp_port'); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "email_smtp_port",
                                        "name" => "email_smtp_port",
                                        "value" => get_setting('email_smtp_port'),
                                        "class" => "form-control",
                                        "placeholder" => app_lang('email_smtp_port'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="email_smtp_security_type" class=" col-md-2"><?php echo app_lang('security_type'); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_dropdown(
                                        "email_smtp_security_type",
                                        array(
                                            "none" => "-",
                                            "tls" => "TLS",
                                            "ssl" => "SSL"
                                        ),
                                        get_setting('email_smtp_security_type'),
                                        "class='select2 mini'"
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group"></div> <!-- to prevent border issue-->

                    </div>

                    <div id="microsoft-smtp-area">
                        <div class="form-group">
                            <div class="row">
                                <label class=" col-md-12">
                                    <?php echo app_lang("get_your_app_credentials_from_here") . " " . anchor("https://portal.azure.com/", "Microsoft Azure Portal", array("target" => "_blank")); ?>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="outlook_smtp_client_id" class=" col-md-2"><?php echo app_lang('google_client_id'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "outlook_smtp_client_id",
                                        "name" => "outlook_smtp_client_id",
                                        "value" => get_setting("outlook_smtp_client_id"),
                                        "class" => "form-control",
                                        "placeholder" => app_lang('google_client_id'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="outlook_smtp_client_secret" class=" col-md-2"><?php echo app_lang('google_client_secret'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "outlook_smtp_client_secret",
                                        "name" => "outlook_smtp_client_secret",
                                        "value" => get_setting('outlook_smtp_client_secret') ? "******" : "",
                                        "class" => "form-control",
                                        "placeholder" => app_lang('google_client_secret'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="redirect_uri" class=" col-md-2"><i data-feather="alert-triangle" class="icon-16 text-warning"></i> <?php echo app_lang('remember_to_add_this_url_in_authorized_redirect_uri'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo "<pre class='mt5'>" . get_uri("microsoft_api/save_outlook_smtp_access_token") . "</pre>"
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group"></div> <!-- to prevent border issue-->
                    </div>

                    <!-- Gmail SMTP Settings -->
                    <div id="gmail-smtp-area">
                        <div class="form-group">
                            <div class="row">
                                <label class=" col-md-12">
                                    <?php echo app_lang("get_your_app_credentials_from_here") . " " . anchor("https://console.developers.google.com", "Google API Console", array("target" => "_blank")); ?>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="gmail_smtp_client_id" class="col-md-2"><?php echo app_lang('google_client_id'); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "gmail_smtp_client_id",
                                        "name" => "gmail_smtp_client_id",
                                        "value" => get_setting("gmail_smtp_client_id"),
                                        "class" => "form-control",
                                        "placeholder" => app_lang('google_client_id'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="gmail_smtp_client_secret" class="col-md-2"><?php echo app_lang('google_client_secret'); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "gmail_smtp_client_secret",
                                        "name" => "gmail_smtp_client_secret",
                                        "value" => get_setting("gmail_smtp_client_secret") ? "******" : "",
                                        "class" => "form-control",
                                        "placeholder" => app_lang('google_client_secret'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label class="col-md-2"><i data-feather="alert-triangle" class="icon-16 text-warning"></i> <?php echo app_lang('remember_to_add_this_url_in_authorized_redirect_uri'); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo "<pre class='mt5'>" . get_uri("google_api/save_gmail_smtp_access_token") . "</pre>"
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group"></div> <!-- to prevent border issue-->
                    </div>

                    <div id="email-smtp-status" class="form-group">
                        <div class="row">
                            <label for="status" class=" col-md-2"><?php echo app_lang('status'); ?></label>
                            <div class=" col-md-10">
                                <?php if (get_setting("smtp_authorized")) { ?>
                                    <span class="ml5 badge bg-success"><?php echo app_lang("authorized"); ?></span>
                                <?php } else { ?>
                                    <span class="ml5 badge" style="background:#F9A52D;"><?php echo app_lang("unauthorized"); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <label for="send_test_mail_to" class=" col-md-2"><?php echo app_lang('send_test_mail_to'); ?>
                                <span class="help" data-container="body" data-bs-toggle="tooltip" title="Keep it blank if you are not interested to send test mail"><i data-feather="help-circle" class="icon-16"></i></span>

                            </label>
                            <div class="col-md-10">
                                <?php
                                echo form_input(array(
                                    "id" => "send_test_mail_to",
                                    "name" => "send_test_mail_to",
                                    "value" => "",
                                    "class" => "form-control",
                                    "placeholder" => "youremail@address.com",
                                    "data-rule-email" => true
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button id="save-button" type="submit" class="btn btn-primary"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                    <button id="save-and-authorize-button" type="submit" class="btn btn-primary ml5"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save_and_authorize'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        $("#email-settings-form").appForm({
            isModal: false,
            onSubmit: function() {
                appLoader.show();
            },
            onSuccess: function(result) {
                appLoader.hide();
                appAlert.success(result.message, {
                    duration: 10000
                });

                //for microsoft outlook, redirect to authorization
                if ($("#email-protocol").val() === "microsoft_outlook") {
                    window.location.href = "<?php echo_uri('microsoft_api/authorize_outlook_smtp'); ?>";
                } else if ($("#email-protocol").val() === "gmail_smtp") {
                    window.location.href = "<?php echo_uri('google_api/authorize_gmail_smtp'); ?>";
                }
            },
            onError: function(result) {
                appLoader.hide();
                appAlert.error(result.message);
            }
        });

        $("#email-settings-form .select2").select2();

        function showHideElements(value) {
            // Define configurations for each protocol type
            const protocols = {
                mail: {
                    show: ['emailSendFromName', 'emailSmtpSentFromAddress', 'saveBtn'],
                    hide: ['smtpSettingsArea', 'microsoftSmtpArea', 'gmailSmtpArea', 'emailSmtpStatus', 'saveAndAuthorizeBtn']
                },
                smtp: {
                    show: ['smtpSettingsArea', 'emailSendFromName', 'saveBtn', 'emailSmtpSentFromAddress'],
                    hide: ['microsoftSmtpArea', 'gmailSmtpArea', 'emailSmtpStatus', 'saveAndAuthorizeBtn']
                },
                microsoft_outlook: {
                    show: ['microsoftSmtpArea', 'emailSmtpStatus', 'saveAndAuthorizeBtn'],
                    hide: ['smtpSettingsArea', 'gmailSmtpArea', 'emailSendFromName', 'emailSmtpSentFromAddress', 'saveBtn']
                },
                gmail_smtp: {
                    show: ['gmailSmtpArea', 'emailSmtpStatus', 'saveAndAuthorizeBtn'],
                    hide: ['smtpSettingsArea', 'microsoftSmtpArea', 'emailSendFromName', 'emailSmtpSentFromAddress', 'saveBtn']
                }
            };

            // All elements with direct jQuery selections
            const allElements = {
                smtpSettingsArea: $("#smtp-settings-area"),
                microsoftSmtpArea: $("#microsoft-smtp-area"),
                gmailSmtpArea: $("#gmail-smtp-area"),
                emailSendFromName: $("#email-send-from-name"),
                emailSmtpSentFromAddress: $("#mail-settings-area"),
                emailSmtpStatus: $("#email-smtp-status"),
                saveBtn: $("#save-button"),
                saveAndAuthorizeBtn: $("#save-and-authorize-button")
            };

            // Apply the configuration for the selected protocol
            if (protocols[value]) {
                // Hide all elements first
                Object.values(allElements).forEach($el => $el.addClass('hide'));

                // Show configured elements
                protocols[value].show.forEach(key => allElements[key].removeClass('hide'));
            }
        }

        $("#email-protocol").select2().on("change", function() {
            var value = $(this).val();
            showHideElements(value);
        });

        showHideElements("<?php echo get_setting("email_protocol"); ?>");
    });
</script>