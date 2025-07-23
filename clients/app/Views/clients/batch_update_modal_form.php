<?php echo form_open(get_uri("clients/save_batch_update"), array("id" => "batch-update-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="client_ids" value="<?php echo $client_ids; ?>" />
        <input type="hidden" name="batch_fields" value="" id="batch_fields" />

        <div class="form-group">
            <div class="row">
                <div class="col-md-1">
                    <?php
                    echo form_checkbox("", "1", false, "class=' batch-update-checkbox form-check-input field-required'");
                    ?>
                </div>
                <label for="created_by" class="col-md-2 text-off"><?php echo app_lang('owner'); ?>
                    <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo app_lang('the_person_who_will_manage_this_client') ?>"><i data-feather="help-circle" class="icon-16"></i></span>
                </label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "owner_id",
                        "name" => "owner_id",
                        "class" => "form-control",
                        "placeholder" => app_lang('owner'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required")
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-1">
                    <?php
                    echo form_checkbox("", "1", false, "class=' batch-update-checkbox form-check-input'");
                    ?>
                </div>
                <label for="groups" class="col-md-2 text-off"><?php echo app_lang('client_groups'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "group_ids",
                        "name" => "group_ids",
                        "class" => "form-control",
                        "placeholder" => app_lang('client_groups')
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-1">
                    <?php
                    echo form_checkbox("", "1", false, "class=' batch-update-checkbox form-check-input'");
                    ?>
                </div>
                <label for="client_labels" class=" col-md-2 text-off"><?php echo app_lang('labels'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "client_labels",
                        "name" => "labels",
                        "class" => "form-control",
                        "placeholder" => app_lang('labels')
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><i data-feather="x" class="icon-16"></i> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><i data-feather="check-circle" class="icon-16"></i> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        //store all checked field name to an input field
        var batchFields = [];

        $("#batch-update-form").appForm({
            beforeAjaxSubmit: function(data) {
                var batchFieldsIndex = 0;

                $.each(data, function(index, obj) {
                    var $checkBox = $("[name='" + obj.name + "']").closest(".form-group").find("input.batch-update-checkbox");
                    if ($checkBox && $checkBox.is(":checked")) {
                        batchFields.push(obj.name);
                    }

                    if (obj.name === "batch_fields") {
                        batchFieldsIndex = index;
                    }
                });

                var serializeOfArray = batchFields.join("-");
                data[batchFieldsIndex]["value"] = serializeOfArray;
            },
            onSuccess: function(result) {
                batchFields = [];

                if (result.success) {
                    if ($(".dataTable:visible").attr("id")) {
                        //update data of clients table 
                        $("#" + $(".dataTable:visible").attr("id")).appTable({
                            reload: true
                        });
                        $("#" + $(".dataTable:visible").attr("id")).trigger("reset-selection-menu");
                    } else {
                        //reload kanban
                        $("#reload-kanban-button:visible").trigger("click");
                        $("#load-kanban").trigger("reset-selection-menu");
                    }

                    appAlert.success(result.message, {
                        duration: 10000
                    });
                }
            }
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

        $("#batch-update-form .select2").select2();

        $('#owner_id').select2({
            data: <?php echo $owners_dropdown; ?>
        });

        $('#group_ids').select2({
            multiple: true,
            data: <?php echo json_encode($groups_dropdown); ?>
        });

        $("#client_labels").select2({
            multiple: true,
            data: <?php echo json_encode($label_suggestions); ?>
        });

        //toggle checkbox and label
        $(".form-group .col-md-9 input, select").on('change', function() {
            var checkBox = $(this).closest(".form-group").find("input.batch-update-checkbox"),
                label = $(this).closest(".form-group").find("label");

            if ($(this).val()) {
                if (!checkBox.is(":checked")) {
                    checkBox.trigger('click');
                    label.removeClass("text-off");
                }
            } else {
                checkBox.removeAttr("checked");
                label.addClass("text-off");
            }
        });

        //toggle labels
        $(".batch-update-checkbox").click(function() {
            var label = $(this).closest(".form-group").find("label");

            if ($(this).is(":checked")) {
                label.removeClass("text-off");
            } else {
                label.addClass("text-off");
            }
        });

        $(".field-required").click(function() {
            var formGroup = $(this).closest(".form-group");
            if ($(this).is(":checked")) {
                formGroup.find(".form-control").addClass("validate-hidden");
            } else {
                formGroup.find(".form-control").removeClass("validate-hidden");
            }
        });
    });
</script>