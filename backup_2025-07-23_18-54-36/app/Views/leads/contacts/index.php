<div class="card">
<div class="card-header border-bottom-0">
        <span class="fw-bold">
            <i data-feather="users" class="icon-16"></i> &nbsp;<?php echo app_lang("contacts"); ?>
        </span>

        <div class="card-header-button float-end">
            <?php
            echo modal_anchor(get_uri("leads/add_new_contact_modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('add_contact'), array("class" => "", "title" => app_lang('add_contact'), "data-post-client_id" => $client_id));
            ?>
        </div>
    </div>

    <div class="table-responsive">
        <table id="lead-details-page-contact-table" class="display no-thead b-t no-hover hide-dtr-control" width="100%">            
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        var responsiveClass = "all",
            emailPhoneVisibility = true,
            emailVisibility = false,
            phoneVsibility = false;
                

        if (isMobile()) {
            responsiveClass = "";
            emailPhoneVisibility = false;
            emailVisibility = true;
            phoneVsibility = true;
        }

        $("#lead-details-page-contact-table").appTable({
            source: '<?php echo_uri("leads/contacts_list_data/" . $client_id) ?>' + '/1',
            serverSide: true,
            hideTools: true,
            displayLength: 100,
            stateSave: false,
            responsive: true,
            mobileMirror: true,
            columns: [
                {title: '', "class": "text-center all"},
                {title: "<?php echo app_lang("title") ?>", "class": "all"},
                {title: "<?php echo app_lang("contact_info") ?>", "class": responsiveClass, visible: emailPhoneVisibility},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("email") ?>", visible: emailVisibility},
                {title: "<?php echo app_lang("phone") ?>", visible: phoneVsibility},
                {visible: false, searchable: false}
<?php echo $custom_field_headers_of_lead_contacts; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w50" + responsiveClass}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 3, 4, 5], '<?php echo $custom_field_headers_of_lead_contacts; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 5], '<?php echo $custom_field_headers_of_lead_contacts; ?>')
        });
    });
</script>