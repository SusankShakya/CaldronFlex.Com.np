<div class="card rounded-top-0 border-top-0">
    <div class="table-responsive">
        <table id="contact-table" class="display" width="100%">
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var showOptions = true;
        if (!"<?php echo $can_edit_clients; ?>") {
            showOptions = false;
        }

        var quick_filters_dropdown = <?php echo view("clients/contacts/quick_filters_dropdown"); ?>;
        if (window.selectedContactQuickFilter) {
            var filterIndex = quick_filters_dropdown.findIndex(x => x.id === window.selectedContactQuickFilter);
            if ([filterIndex] > -1) {
                //match found
                quick_filters_dropdown[filterIndex].isSelected = true;
            }
        }

        $("#contact-table").appTable({
            source: '<?php echo_uri("clients/contacts_list_data/") ?>',
            serverSide: true,
            filterDropdown: [{name: "quick_filter", class: "w200", options: quick_filters_dropdown}, <?php echo $custom_field_filters; ?>],
            order: [[1, "asc"]],
            columns: [
                {title: '', "class": "w50 text-center all"},
                {title: "<?php echo app_lang("name") ?>", "class": "w150 all", order_by: "first_name"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("client_name") ?>", "class": "w150", order_by: "company_name"},
                {title: "<?php echo app_lang("job_title") ?>", "class": "w15p", order_by: "job_title"},
                {title: "<?php echo app_lang("email") ?>", "class": "w20p", order_by: "email"},
                {title: "<?php echo app_lang("phone") ?>", "class": "w100", order_by: "phone"},
                {title: 'Skype', "class": "w15p", order_by: "skype"},
                {visible: false, searchable: false},
                {visible: false, searchable: false}
<?php echo $custom_field_headers; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w50", visible: showOptions}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>