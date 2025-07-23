<?php
$creator_name = $ticket_info->creator_name;
if ($creator_name) {
    $name_parts = explode(" ", trim($creator_name));
    $first_name = $name_parts[0];
    $last_name = implode(" ", array_slice($name_parts, 1));

    $link_with_new_client_steps = array(
        array(
            "name" => "create_new_client",
            "title" => app_lang("create_new_client"),
            "url" => get_uri("clients/save"),
            "data" => array(
                "company_name" => $creator_name,
                "contact_email" => $ticket_info->creator_email
            )
        ),
        array(
            "name" => "create_client_contact",
            "title" => app_lang("add_client_contact"),
            "url" => get_uri("clients/save_contact"),
            "data" => array(
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email" => $ticket_info->creator_email,
                "client_id" => 0, //set new client id
            )
        ),
        array(
            "name" => "link_client",
            "title" => app_lang("link_the_client_with_this_ticket"),
            "url" => get_uri("tickets/link_client_to_ticket"),
            "data" => array(
                "ticket_id" => $ticket_info->id,
                "client_id" => 0, //set new client id
                "contact_id" => 0, //set new contact id
            )
        )
    );
}

?>
<div class="card">
    <div class="card-body">
        <div class="text-center mb10">
            <?php if ($ticket_info->client_id) { ?>
                <?php if ($ticket_info->requested_by) { ?>
                    <div class="avatar avatar-xs mb5">
                        <img src="<?php echo get_avatar($ticket_info->requested_by_avatar); ?>" alt="..." />
                    </div>
                    <div><?php echo anchor(get_uri("clients/contact_profile/" . $ticket_info->requested_by), $ticket_info->requested_by_name ? $ticket_info->requested_by_name : "", array("class" => "dark")); ?></div>
                    <div><?php echo $ticket_info->requested_by_email; ?></div>
                <?php } else {
                    echo $ticket_info->company_name ? anchor(get_uri("clients/view/" . $ticket_info->client_id), $ticket_info->company_name) : "-";
                } ?>
            <?php } else { ?>
                <div class="clearfix">
                    <div class="float-start mt-1"><?php echo $ticket_info->creator_name . " [" . app_lang("unknown_client") . "]" ?></div>
                    <div class="float-end">
                        <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                            <i data-feather="more-horizontal" class="icon-16"></i>
                        </div>
                        <ul class="dropdown-menu" role="menu">
                            <?php if ($ticket_info->client_id === "0" && $login_user->user_type == "staff") { ?>
                                <?php if ($can_create_client) { ?>
                                    <li role="presentation"><a href="javascript:;" class="dropdown-item create-client-link" data-ticket-id="<?php echo $ticket_info->id; ?>"><i data-feather='plus' class='icon-16'></i> <?php echo app_lang('link_to_new_client'); ?></a></li>
                                <?php } ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("tickets/add_client_modal_form/$ticket_info->id"), "<i data-feather='link' class='icon-16'></i> " . app_lang('link_to_existing_client'), array("title" => app_lang('link_to_existing_client'), "class" => "dropdown-item")); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>
        </div>

        <ul class="list-group info-list">

            <?php if ($ticket_info->company_name && $ticket_info->requested_by && ($ticket_info->company_name != $ticket_info->requested_by)) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("client"); ?>"><i data-feather="briefcase" class="icon-16 mr5"></i> <?php echo $ticket_info->company_name ? anchor(get_uri("clients/view/" . $ticket_info->client_id), $ticket_info->company_name, array("class" => "dark")) : "-"; ?></span>
                </li>
            <?php } ?>

            <?php if ($ticket_info->client_id && $ticket_info->company_phone) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("phone"); ?>"><i data-feather="phone" class="icon-16 mr5"></i> <?php echo $ticket_info->company_phone; ?></span>
                </li>
            <?php } ?>
            <?php
            if ($ticket_info->client_id && ($ticket_info->total_tickets > 1)) {
                $url = get_uri("tickets/index/all/0/" . $ticket_info->client_id);
                if ($view_type === "compact_view") {
                    $url = get_uri("tickets/compact_view/$ticket_info->id/" . $ticket_info->client_id);
                }
            ?>
                <li class="list-group-item">
                    <span><i data-feather="package" class="icon-16 mr5"></i> <?php echo anchor($url, $ticket_info->total_tickets . " " . app_lang("tickets"), array("class" => "dark")); ?></span>
                    <?php
                    if (isset($selected_client_id) && $selected_client_id) {
                        echo anchor(get_uri("tickets/compact_view/$ticket_info->id"), "<i data-feather='refresh-cw' class='icon-16'></i>", array("class" => "float-end", "title" => app_lang("reset_filter")));
                    }
                    ?>
                </li>
            <?php } else if (!$ticket_info->client_id) { ?>
                <li class="list-group-item">
                    <span title="<?php echo app_lang("email"); ?>"><i data-feather="mail" class="icon-16 mr5"></i> <?php echo $ticket_info->creator_email ? $ticket_info->creator_email : "-"; ?></span>
                </li>
            <?php } ?>

            <?php
            $ticket_modifier_attrs = array();

            if ($login_user->user_type === "staff" && $can_edit_ticket) {
                // Prepare the ticket-modifier attributes
                $ticket_modifier_attrs = array(
                    'title' => "",
                    'class' => "",
                    'data-id' => $ticket_info->id,
                    'data-value' => $ticket_info->cc_contacts_and_emails,
                    'data-act' => 'ticket-modifier',
                    'data-modifier-group' => 'ticket_info',
                    'data-field' => 'cc_contacts_and_emails',
                    'data-multiple-tags' => '1',
                    'data-can-create-tags' => '1',
                    'data-action-url' => get_uri("tickets/update_ticket_info/$ticket_info->id/cc_contacts_and_emails")
                );

                if (!$ticket_info->cc_contacts_and_emails) {
                    echo "<li class='list-group-item'>";
                    echo "<i data-feather='mail' class='icon-16 mr5' title='CC'></i>" . js_anchor("<span class='badge rounded-pill large bg-transparent mt0'>CC</span>", $ticket_modifier_attrs);
                    echo "</li>";
                }
            }
            ?>

            <?php if ($ticket_info->cc_contacts_and_emails && $login_user->user_type === "staff") { ?>
                <li class="list-group-item">
                    <?php
                    if ($can_edit_ticket) {
                        echo 'CC: ' . js_anchor($cc_contacts_list, $ticket_modifier_attrs);
                    } else {
                        echo 'CC: ' . $cc_contacts_list;
                    }
                    ?>
                </li>
            <?php } ?>
        </ul>

        <?php if ($ticket_info->client_id === "0" && $creator_name) { ?>
            <div class="step-confirmation-section mt10 pt10 b-t d-none">
                <div class="step-confirmation-container">
                    <p class="strong mb10"><?php echo app_lang("are_you_sure"); ?></p>
                    <p class="mb15"><?php echo app_lang("this_action_will_do_the_following"); ?></p>
                    <?php foreach ($link_with_new_client_steps as $index => $step) { ?>
                        <div class="mt10 step-confirmation" data-step="<?php echo $step['name']; ?>">
                            <span class="icon-container">
                                <i data-feather="circle" class="icon-16 mr10"></i>
                            </span>
                            <span class="step-title"><?php echo $step['title']; ?></span>
                        </div>
                    <?php } ?>

                    <div class="mt15 client-create-and-link-btn-section">
                        <button type="button" id="client-create-and-link-btn" class="btn btn-primary"><?php echo app_lang("yes"); ?></button>
                        <button type="button" id="client-create-and-link-hide-btn" class="btn btn-light ml10"><?php echo app_lang("no"); ?></button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.create-client-link').on('click', function() {
            $('.step-confirmation-section').removeClass('d-none');
        });

        $('#client-create-and-link-hide-btn').on('click', function() {
            $('.step-confirmation-section').addClass('d-none');
        });

        $('#client-create-and-link-btn').on('click', function() {
            $('.client-create-and-link-btn-section').addClass('d-none');
            var steps = [];
            <?php if (isset($link_with_new_client_steps)) {  ?>
                steps = <?php echo json_encode($link_with_new_client_steps); ?>;
            <?php  } ?>

            setTimeout(function() {
                processStep(steps, 0);
            }, 500); // 0.5 second delay before starting
        });

        function setStepInfo(stepName, type, errorMessage) {
            var $confirmationContainer = $('.step-confirmation-container');
            var $stepElement = $confirmationContainer.find('.step-confirmation[data-step="' + stepName + '"]');
            var $iconContainer = $stepElement.find('.icon-container');

            if (type == "start") {
                $iconContainer.html('<span class="spinning-btn spinning mr10"></span>');
            } else if (type == "success") {
                $iconContainer.html('<i data-feather="check-circle" class="icon-16 mr10"></i>');
            } else if (type == "error") {
                $iconContainer.html('<i data-feather="x-circle" class="icon-16 mr10 text-danger"></i>');
                $confirmationContainer.append('<div class="alert alert-danger mt10">' +
                    (errorMessage || '<?php echo app_lang("error_occurred"); ?>') +
                    '</div>');
            }

            feather.replace();
        }

        function processStep(stepArray, stepIndex) {
            if (stepIndex >= stepArray.length) {

                appAlert.success('<?php echo app_lang('record_saved'); ?>', {
                    duration: 3000
                });
                setTimeout(function() {
                    location.reload();
                }, 2000); // 2 seconds delay before reload
                return;
            }

            var step = stepArray[stepIndex]
            setStepInfo(step.name, "start");

            appAjaxRequest({
                url: step.url,
                type: "POST",
                data: step.data,
                cache: false,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        setStepInfo(step.name, "success");

                        if (step.name == "create_new_client" && response.id) {
                            //update the client id for next steps 
                            stepArray[1].data.client_id = response.id;
                            stepArray[2].data.client_id = response.id;
                        } else if (step.name == "create_client_contact" && response.id) {
                            stepArray[2].data.contact_id = response.id; //update the contact id for next step
                        }

                        // Start next step
                        processStep(stepArray, stepIndex + 1);

                    } else {
                        var errorMessage = response.message;
                        if (response.error_type == "email_exists") {
                            errorMessage = "<?php echo app_lang('link_client_to_existing_client_suggestion'); ?>";
                        }
                        setStepInfo(step.name, "error", errorMessage);
                    }
                },
                error: function() {
                    setStepInfo(step.name, "error");
                }
            });
        }
    });
</script>