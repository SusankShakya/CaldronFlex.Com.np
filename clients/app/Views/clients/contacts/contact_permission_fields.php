<?php
$label_column = isset($label_column) ? $label_column : "col-md-3";
$field_column = isset($field_column) ? $field_column : "col-md-9";

$disable = "";
$can_access_everything = false;
$can_access_only = get_setting("default_permissions_for_non_primary_contact");

if (isset($user_info) && $user_info) {
    $can_access_everything = $user_info->client_permissions == "all" ? true : false;

    if ($user_info->client_permissions != "all") {
        $can_access_only = $user_info->client_permissions;
    }

    // is set primary contact, disable the checkbox
    if ($user_info->is_primary_contact) {
        $can_access_everything = true;
        $disable = "disabled='disabled'";
    }
} else {
?>
    <div class="form-group">
        <div class="row">
            <label class="<?php echo $label_column; ?> strong"><?php echo app_lang('permissions'); ?></label>
        </div>
    </div>
<?php
}

$contact_suffix = isset($lead_contact_id) ? "_" . $lead_contact_id : "";
$can_access_everything_id = "can_access_everything" . $contact_suffix;
$can_access_only_id = "can_access_only" . $contact_suffix;
$specific_permission_section_id = "specific_permission_section" . $contact_suffix;
?>

<div class="form-group">
    <div class="row">
        <label for="<?php echo $can_access_everything_id; ?>" class="<?php echo $label_column; ?>"><?php echo app_lang('can_access_everything'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_checkbox("can_access_everything$contact_suffix", "1", "$can_access_everything", "id='$can_access_everything_id' class='form-check-input mt-2' $disable");
            ?>
        </div>
    </div>
</div>
<div id="<?php echo $specific_permission_section_id; ?>" class="form-group <?php echo $can_access_everything ? "hide" : ""; ?>">
    <div class="form-group">
        <div class="row">
            <label for="<?php echo $can_access_only_id; ?>" class="<?php echo $label_column; ?>"><?php echo app_lang('can_access_only'); ?></label>
            <div class="<?php echo $field_column; ?>">
                <?php
                echo form_input(array(
                    "id" => $can_access_only_id,
                    "name" => "specific_permissions$contact_suffix",
                    "value" => $can_access_only,
                    "class" => "form-control",
                    "placeholder" => app_lang('can_access_only')
                ));
                ?>
            </div>
        </div>
    </div>
</div>

<?php if (!isset($user_info)) { ?>
    <div class="form-group">
        <div class="row">
            <div class="col-md-9 ms-auto text-off">
                <i data-feather="info" class="icon-16"></i> <?php echo app_lang("primary_contact_will_get_full_permission_message"); ?>
            </div>
        </div>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#<?php echo $can_access_everything_id; ?>").click(function() {
            if ($(this).is(":checked")) {
                $("#<?php echo $specific_permission_section_id; ?>").addClass("hide");
            } else {
                $("#<?php echo $specific_permission_section_id; ?>").removeClass("hide");
            }
        });

        $("#<?php echo $can_access_only_id; ?>").select2({
            multiple: true,
            data: <?php echo ($available_menus); ?>
        });
    });
</script>