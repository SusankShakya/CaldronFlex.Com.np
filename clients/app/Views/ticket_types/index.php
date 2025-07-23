<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "tickets";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">

                <ul id="ticket-type-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#ticket-types-tab"> <?php echo app_lang('ticket_types'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("settings/tickets"); ?>" data-bs-target="#tickets-tab"><?php echo app_lang('tickets'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("settings/imap_settings"); ?>" data-bs-target="#imap_settings-tab"><?php echo app_lang('imap_settings'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("automation/ticket_automation"); ?>" data-bs-target="#ticket-automations-tab"><?php echo app_lang('automations'); ?></a></li>
                    <div class="tab-title clearfix no-border">
                        <div class="title-button-group">
                            <?php echo modal_anchor(get_uri("ticket_types/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_ticket_type'), array("class" => "btn btn-default", "title" => app_lang('add_ticket_type'), "id" => "add-ticket-type-button")); ?>
                        </div>
                    </div>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="ticket-types-tab">
                        <div class="table-responsive">
                            <table id="ticket-type-table" class="display" cellspacing="0" width="100%">
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="tickets-tab"></div>
                    <div role="tabpanel" class="tab-pane fade" id="imap_settings-tab"></div>
                    <div role="tabpanel" class="tab-pane fade" id="ticket-automations-tab"></div>
                </div>

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#ticket-type-table").appTable({
            source: '<?php echo_uri("ticket_types/list_data") ?>',
            columns: [{
                    title: '<?php echo app_lang("name"); ?>'
                },
                {
                    title: '<i data-feather="menu" class="icon-16"></i>',
                    "class": "text-center option w100"
                }
            ],
            printColumns: [0]
        });

        setTimeout(function() {
            var tab = "<?php echo $tab; ?>";
            if (tab === "imap") {
                $("[data-bs-target='#imap_settings-tab']").trigger("click");
            }
        }, 210);

        //change the add button attributes on changing tab panel
        var $addButton = $("#add-ticket-type-button");
        $(".nav-tabs li").click(function() {
            var activeField = $(this).find("a").attr("data-bs-target");

            if (activeField === "#ticket-automations-tab") {
                $addButton.attr("title", "<?php echo app_lang("add_automation"); ?>");
                $addButton.attr("data-title", "<?php echo app_lang("add_automation"); ?>");
                $addButton.attr("data-action-url", "<?php echo get_uri("automation/modal_form"); ?>");
                $addButton.attr("data-post-related_to", "tickets");

                $addButton.html("<?php echo "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_automation'); ?>");
                $addButton.show();
            } else if (activeField === "#ticket-types-tab") {
                $addButton.attr("title", "<?php echo app_lang("add_ticket_type"); ?>");
                $addButton.attr("data-title", "<?php echo app_lang("add_ticket_type"); ?>");
                $addButton.attr("data-action-url", "<?php echo get_uri("ticket_types/modal_form"); ?>");

                $addButton.html("<?php echo "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_ticket_type'); ?>");

                $addButton.show();
            } else {
                $addButton.hide();
            }
            feather.replace();
        });
    });
</script>