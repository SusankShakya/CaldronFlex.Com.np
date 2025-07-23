<?php echo form_open(get_uri("settings/save_invoice_reminders_settings"), array("id" => "invoice-reminder-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

<div class="card-body">
    <?php
    $days_dropdown = array(
        "" => " - ",
        "1" => "1 " . app_lang("day"),
        "2" => "2 " . app_lang("days"),
        "3" => "3 " . app_lang("days"),
        "5" => "5 " . app_lang("days"),
        "7" => "7 " . app_lang("days"),
        "10" => "10 " . app_lang("days"),
        "14" => "14 " . app_lang("days"),
        "15" => "15 " . app_lang("days"),
        "20" => "20 " . app_lang("days"),
        "30" => "30 " . app_lang("days"),
    );
    ?>

    <div class="form-group">
        <div class="row">
            <label for="send_invoice_due_pre_reminder" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('send_first_due_invoice_reminder_notification_before'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_dropdown(
                    "send_invoice_due_pre_reminder",
                    $days_dropdown,
                    get_setting('send_invoice_due_pre_reminder'),
                    "class='select2 mini'"
                );
                ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label for="send_invoice_due_pre_second_reminder" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('send_second_due_invoice_reminder_notification_before'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>

            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_dropdown(
                    "send_invoice_due_pre_second_reminder",
                    $days_dropdown,
                    get_setting('send_invoice_due_pre_second_reminder'),
                    "class='select2 mini'"
                );
                ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label for="send_invoice_due_after_reminder" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('send_first_invoice_overdue_reminder_after'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_dropdown(
                    "send_invoice_due_after_reminder",
                    $days_dropdown,
                    get_setting('send_invoice_due_after_reminder'),
                    "class='select2 mini'"
                );
                ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label for="send_invoice_due_after_second_reminder" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('send_second_invoice_overdue_reminder_after'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_dropdown(
                    "send_invoice_due_after_second_reminder",
                    $days_dropdown,
                    get_setting('send_invoice_due_after_second_reminder'),
                    "class='select2 mini'"
                );
                ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label for="send_recurring_invoice_reminder_before_creation" class="col-md-4 col-xs-8 col-sm-4"><?php echo app_lang('send_recurring_invoice_reminder_before_creation'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>
            <div class="col-md-8 col-xs-4 col-sm-8">
                <?php
                echo form_dropdown(
                    "send_recurring_invoice_reminder_before_creation",
                    $days_dropdown,
                    get_setting('send_recurring_invoice_reminder_before_creation'),
                    "class='select2 mini'"
                );
                ?>
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
        $("#invoice-reminder-settings-form").appForm({
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

        $("#invoice-reminder-settings-form .select2").select2();
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>