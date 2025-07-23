<div class="card" id="contract-note-card">
    <div class="card-header fw-bold">
        <i data-feather="file-text" class="icon-16"></i> &nbsp;<?php echo app_lang("note"); ?>
    </div>
    <div class="card-body">
        <?php echo custom_nl2br($contract_info->note ? process_images_from_content($contract_info->note) : ""); ?>
    </div>
</div>