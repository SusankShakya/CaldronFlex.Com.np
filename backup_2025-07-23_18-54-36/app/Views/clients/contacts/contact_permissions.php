<div class="tab-content">
    <?php echo form_open(get_uri("clients/save_contact_permissions/" . $user_info->id), array("id" => "contact-permissions-form", "class" => "general-form dashed-row white", "role" => "form")); ?>
    <div class="card border-top-0 rounded-top-0">
        <div class=" card-header">
            <h4><?php echo app_lang('user_permissions'); ?></h4>
        </div>
        <div class="card-body">
            <?php if ($login_user->is_admin) { ?>
                <div class="form-group ">
                    <div class="row">
                        <label for="is_primary_contact" class="<?php echo $label_column; ?>"><?php echo app_lang('primary_contact'); ?>
                            <span class="help" data-container="body" data-bs-toggle="tooltip" title="<?php echo app_lang('primary_contact_can_manage_the_permission_of_other_contacts') ?>"><i data-feather="help-circle" class="icon-16"></i></span>
                        </label>

                        <div class="<?php echo $field_column; ?>">
                            <?php
                            //is set primary contact, disable the checkbox
                            $disable = "";
                            if ($user_info->is_primary_contact) {
                                $disable = "disabled='disabled'";
                            }
                            echo form_checkbox("is_primary_contact", "1", $user_info->is_primary_contact, "id='is_primary_contact' class='form-check-input mt-2' $disable");
                            ?>
                            <label id="make_primary_contact_help_message" class="ml10 hide mb0 mt5"><i data-feather="alert-triangle" class="icon-16 text-warning"></i> <?php echo app_lang("make_primary_contact_help_message"); ?></label>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php echo view("clients/contacts/contact_permission_fields"); ?>
        </div>
        <div class="card-footer rounded-0">
            <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#contact-permissions-form").appForm({
            isModal: false,
            onSuccess: function(result) {
                appAlert.success(result.message, {
                    duration: 10000
                });
            }
        });

        $("#is_primary_contact").change(function() {
            if ($(this).is(":checked")) {
                $("#can_access_everything").trigger("click");
                $("#can_access_everything").prop("checked", true);
                $("#can_access_everything").prop("disabled", true);
                $("#specific_permission_section").addClass("hide");
                $("#make_primary_contact_help_message").removeClass("hide");
            } else {
                $("#can_access_everything").prop("disabled", false);
                $("#make_primary_contact_help_message").addClass("hide");
            }
        });

        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>