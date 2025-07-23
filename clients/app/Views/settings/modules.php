<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "modules";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_module_settings"), array("id" => "module-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="card">
                <div class="card-header">
                    <h4><?php echo app_lang("manage_modules"); ?></h4>
                    <div><?php echo app_lang("module_settings_instructions"); ?></div>
                </div>
                <div class="card-body">

                    <?php
                    $modules = [
                        "self_improvements" => [
                            "todo" => app_lang('todo'),
                            "note" => app_lang('note'),
                            "reminder" => app_lang('reminder'),
                            "event" => app_lang('event'),
                        ],
                        "business_growth" => [
                            "lead" => app_lang('lead'),
                            "expense" => app_lang('expense'),
                        ],
                        "sales_management" => [
                            "contract" => app_lang('contract'),
                            "proposal" => app_lang('proposal'),
                            "estimate" => app_lang('estimate'),
                            "estimate_request" => app_lang('estimate_request'),
                            "invoice" => app_lang('invoice'),
                            "subscription" => app_lang('subscription'),
                            "order" => app_lang('order'),
                        ],
                        "customer_support" => [
                            "ticket" => app_lang('ticket'),
                            "knowledge_base" => app_lang('knowledge_base') . " (" . app_lang("public") . ")",
                        ],
                        "team_management" => [
                            "leave" => app_lang('leave'),
                            "attendance" => app_lang('attendance'),
                            "project_timesheet" => app_lang('project_timesheet'),
                            "gantt" => app_lang('gantt'),
                            "help" => app_lang('help') . " (" . app_lang("team_members") . ")",
                        ],
                        "collaboration" => [
                            "message" => app_lang('message'),
                            "chat" => app_lang('chat'),
                            "file_manager" => app_lang('file_manager'),
                            "timeline" => app_lang('timeline'),
                            "announcement" => app_lang('announcement'),
                        ],
                    ];

                    foreach ($modules as $category => $items) {
                        echo '<div class="mb20"><span class="highlight-toolbar strong ml0">' . app_lang($category) . '</span></div>';
                        echo '<div class="row">';
                        foreach ($items as $key => $label) {
                            echo '<div class="col-md-3 col-sm-6">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="card-text form-switch">
                                                ' . form_checkbox("module_$key", "1", get_setting("module_$key") ? true : false, "id='module_$key' class='form-check-input'") . '
                                                <label for="module_' . $key . '" class="block">' . $label . '</label>
                                            </div>
                                        </div>
                                    </div>
                                  </div>';
                        }
                        echo '</div>';
                    }
                    ?>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#module-settings-form").appForm({
            isModal: false,
            onSuccess: function(result) {
                appAlert.success(result.message, {
                    duration: 10000
                });
                location.reload();
            }
        });
    });
</script>