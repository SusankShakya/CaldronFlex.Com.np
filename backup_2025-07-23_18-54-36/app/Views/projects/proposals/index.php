<div class="bg-white p10 mb20 rounded">
    <i data-feather="link" class="icon-16"></i>
    <?php echo (anchor(get_uri("proposals/view/" . $project_info->proposal_id), get_proposal_id($project_info->proposal_id))); ?>
</div>