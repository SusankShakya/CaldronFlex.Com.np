<?php
$signer_info = @unserialize($contract_info->meta_data);
if (!($signer_info && is_array($signer_info))) {
    $signer_info = array();
}
?>

<?php if ($contract_status === "accepted" && ($signer_info || $contract_info->accepted_by)) { ?>
    <div class="card">
        <div class="card-header fw-bold">
            <i data-feather="edit-3" class="icon-16"></i> &nbsp;<?php echo app_lang("signer_info") . " (" . app_lang("client") . ")"; ?>
        </div>
        <div class="p15">
            <div><strong><?php echo app_lang("name"); ?>: </strong><?php echo $contract_info->accepted_by ? get_client_contact_profile_link($contract_info->accepted_by, $contract_info->signer_name) : get_array_value($signer_info, "name"); ?></div>
            <div><strong><?php echo app_lang("email"); ?>: </strong><?php echo $contract_info->signer_email ? $contract_info->signer_email : get_array_value($signer_info, "email"); ?></div>
            <?php if (get_array_value($signer_info, "signed_date")) { ?>
                <div><strong><?php echo app_lang("signed_date"); ?>: </strong><?php echo format_to_relative_time(get_array_value($signer_info, "signed_date")); ?></div>
            <?php } ?>

            <?php
            if (get_array_value($signer_info, "signature")) {
                $signature_file = @unserialize(get_array_value($signer_info, "signature"));
                $signature_file_name = get_array_value($signature_file, "file_name");
                $signature_file = get_source_url_of_file($signature_file, get_setting("timeline_file_path"), "thumbnail");
            ?>
                <div><strong><?php echo app_lang("signature"); ?>: </strong><br /><img class="signature-image" src="<?php echo $signature_file; ?>" alt="<?php echo $signature_file_name; ?>" /></div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
<?php if ($contract_info->staff_signed_by) { ?>
    <div class="card">
        <div class="card-header fw-bold">
            <i data-feather="edit-3" class="icon-16"></i> &nbsp;<?php echo app_lang("signer_info") . " (" . app_lang("team_member") . ")"; ?>
        </div>
        <div class="p15">
            <div><strong><?php echo app_lang("name"); ?>: </strong><?php echo get_team_member_profile_link($contract_info->staff_signed_by, $contract_info->staff_signer_name); ?></div>
            <?php if (get_array_value($signer_info, "staff_signed_date")) { ?>
                <div><strong><?php echo app_lang("signed_date"); ?>: </strong><?php echo format_to_relative_time(get_array_value($signer_info, "staff_signed_date")); ?></div>
            <?php } ?>

            <?php
            if (get_array_value($signer_info, "staff_signature")) {
                $signature_file = @unserialize(get_array_value($signer_info, "staff_signature"));
                $signature_file_name = get_array_value($signature_file, "file_name");
                $signature_file = get_source_url_of_file($signature_file, get_setting("timeline_file_path"), "thumbnail");
            ?>
                <div><strong><?php echo app_lang("signature"); ?>: </strong><br /><img class="signature-image" src="<?php echo $signature_file; ?>" alt="<?php echo $signature_file_name; ?>" /></div>
            <?php } ?>
        </div>
    </div>
<?php } ?>