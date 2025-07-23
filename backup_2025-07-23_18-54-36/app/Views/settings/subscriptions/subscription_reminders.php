<?php echo form_open(get_uri("reminder_settings/save_subscription_reminders_settings"), array("id" => "subscription-reminder-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

<div class="card-body">
    <?php
    function generate_subscription_reminder_days_dropdown($options) {
        $days_dropdown = array();
        foreach ($options as $option) {
            if ($option === "") {
                $days_dropdown[$option] = " - ";
            } else if ($option == 1) {
                $days_dropdown[$option] = "1 " . app_lang("day");
            } else {
                $days_dropdown[$option] = $option . " " . app_lang("days");
            }
        }
        return $days_dropdown;
    }

    $days_dropdown_1 = generate_subscription_reminder_days_dropdown(array("", 1, 2, 3, 4, 5, 6));
    $days_dropdown_2 = generate_subscription_reminder_days_dropdown(array("", 1, 2, 3, 5, 7, 10, 14, 15, 20, 28));
    $days_dropdown_3 = generate_subscription_reminder_days_dropdown(array("", 1, 2, 5, 7, 15, 20, 30, 40, 50, 60));
    ?>

    <div class="row">
        <div class="mb20">
            <span class="highlight-toolbar strong ml0"><?php echo app_lang("weekly") ?></span>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="send_first_pre_reminder_before" class=" col-md-2"><?php echo app_lang('send_first_reminder_before'); ?></label>

                <div class="col-md-10">
                    <?php
                    echo form_dropdown(
                        "subscription_weekly_reminder_1",
                        $days_dropdown_1,
                        isset($weekly_reminder_info->reminder1) ? $weekly_reminder_info->reminder1 : "",
                        "class='select2 mini mr10'"
                    );
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="send_second_pre_reminder_before" class=" col-md-2"><?php echo app_lang('send_second_reminder_before'); ?></label>

                <div class="col-md-10">
                    <?php
                    echo form_dropdown(
                        "subscription_weekly_reminder_2",
                        $days_dropdown_1,
                        isset($weekly_reminder_info->reminder2) ? $weekly_reminder_info->reminder2 : "",
                        "class='select2 mini mr10'"
                    );
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="mb20">
            <span class="highlight-toolbar strong ml0"><?php echo app_lang("monthly") ?></span>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="send_first_pre_reminder_before" class=" col-md-2"><?php echo app_lang('send_first_reminder_before'); ?></label>

                <div class="col-md-10">
                    <?php
                    echo form_dropdown(
                        "subscription_monthly_reminder_1",
                        $days_dropdown_2,
                        isset($monthly_reminder_info->reminder1) ? $monthly_reminder_info->reminder1 : "",
                        "class='select2 mini mr10'"
                    );
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="send_second_pre_reminder_before" class=" col-md-2"><?php echo app_lang('send_second_reminder_before'); ?></label>

                <div class="col-md-10">
                    <?php
                    echo form_dropdown(
                        "subscription_monthly_reminder_2",
                        $days_dropdown_2,
                        isset($monthly_reminder_info->reminder2) ? $monthly_reminder_info->reminder2 : "",
                        "class='select2 mini mr10'"
                    );
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="mb20">
            <span class="highlight-toolbar strong ml0"><?php echo app_lang("yearly") ?></span>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="send_first_pre_reminder_before" class=" col-md-2"><?php echo app_lang('send_first_reminder_before'); ?></label>

                <div class="col-md-10">
                    <?php
                    echo form_dropdown(
                        "subscription_yearly_reminder_1",
                        $days_dropdown_3,
                        isset($yearly_reminder_info->reminder1) ? $yearly_reminder_info->reminder1 : "",
                        "class='select2 mini mr10'"
                    );
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="send_second_pre_reminder_before" class=" col-md-2"><?php echo app_lang('send_second_reminder_before'); ?></label>

                <div class="col-md-10">
                    <?php
                    echo form_dropdown(
                        "subscription_yearly_reminder_2",
                        $days_dropdown_3,
                        isset($yearly_reminder_info->reminder2) ? $yearly_reminder_info->reminder2 : "",
                        "class='select2 mini mr10'"
                    );
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <i data-feather="info" class="icon-16"></i>
                    <span><?php echo app_lang("cron_job_required"); ?></span>
                </div>
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
        $("#subscription-reminder-settings-form").appForm({
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

        $("#subscription-reminder-settings-form .select2").select2();
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>