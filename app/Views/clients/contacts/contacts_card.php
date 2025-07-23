<div class="card">
    <div class="card-header border-bottom-0">
        <span class="fw-bold">
            <i data-feather="users" class="icon-16"></i> &nbsp;<?php echo app_lang("contacts"); ?>
        </span>

        <div class="card-header-button float-end">
            <?php
            if ($can_edit_clients) {
                echo modal_anchor(get_uri("clients/invitation_modal"), "<i data-feather='mail' class='icon-16'></i> " . app_lang('send_invitation'), array("class" => "p10", "title" => app_lang('send_invitation'), "data-post-client_id" => $client_id));

                echo modal_anchor(get_uri("clients/add_new_contact_modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_contact'), array("class" => "p10", "title" => app_lang('add_contact'), "data-post-client_id" => $client_id));
            }
            ?>
        </div>
    </div>

    <div class="table-responsive">
        <table id="client-details-page-contact-table" class="display no-thead b-t no-hover hide-dtr-control" width="100%">
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var showOptions = true;
        if (!"<?php echo $can_edit_clients; ?>") {
            showOptions = false;
        }

        var responsiveClass = "all",
        emailPhoneVisibility = true,
        emailVisibility = false,
        phoneVsibility = false;
        if(isMobile()) {
            responsiveClass = "";
            emailPhoneVisibility = false;
            emailVisibility = true;
            phoneVsibility = true;
        }

        $("#client-details-page-contact-table").appTable({
            source: '<?php echo_uri("clients/contacts_list_data/" . $client_id) ?>' + '/1',
            serverSide: true,
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            columns: [
                {title: '', "class": "all"},
                {title: "<?php echo app_lang("title") ?>", "class": "all"},
                {title: "<?php echo app_lang("email") ?>", "class": responsiveClass, visible: emailPhoneVisibility},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("email") ?>", visible: emailVisibility},
                {title: "<?php echo app_lang("phone") ?>", visible: phoneVsibility},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("last_online") ?>"}
                <?php echo $custom_field_headers_of_client_contacts; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w50" + responsiveClass, visible: showOptions}
            ],
            reloadHooks: [{
                    type: "app_form",
                    id: "contact-form"
                }
            ]
        });
    });
</script>