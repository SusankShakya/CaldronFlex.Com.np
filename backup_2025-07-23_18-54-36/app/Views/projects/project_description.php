<?php
if($project_info->description){ ?>
<div class="card">
    <div class="card-header">
        <h6 class="float-start"><?php echo app_lang('description'); ?></h6>
    </div>
    <div class="card-body">
        <?php echo $project_info->description ? custom_nl2br(link_it(process_images_from_content($project_info->description))) : ""; ?>
    </div>
</div>

<?php } ?>