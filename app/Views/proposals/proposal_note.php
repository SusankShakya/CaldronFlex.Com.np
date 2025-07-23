<div class="card" id="proposal-note-card">
    <div class="card-header fw-bold">
        <i data-feather="file-text" class="icon-16"></i> &nbsp;<?php echo app_lang("note"); ?>
    </div>
    <div class="card-body">
        <?php echo custom_nl2br($proposal_info->note ? process_images_from_content($proposal_info->note) : ""); ?>
    </div>
</div>