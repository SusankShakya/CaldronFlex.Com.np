<li class="list-group-item mt10">
    <div class="d-flex">
        <div class="float-start mr10">
        <span title="<?php echo app_lang('email_seen_at'); ?>"><i data-feather='eye' class='icon-16'></i></span>
        </div>
        <div>
            <?php foreach ($email_read_logs as $log) { ?>
                <div><?php echo format_to_relative_time($log); ?></div>
            <?php } ?>
        </div>
    </div>
</li>