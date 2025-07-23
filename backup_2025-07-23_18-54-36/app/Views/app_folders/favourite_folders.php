<?php
foreach ($favourite_folders as $favourite_folder) {
    ?>
    <a href="javascript:;" class="list-group-item clickable explore-favourite-folder folder-<?php echo $favourite_folder->id; ?>"  data-folder_id="<?php echo $favourite_folder->folder_id; ?>">
        <i data-feather="circle" class="icon-16 mr10"></i><?php echo $favourite_folder->title; ?>
    </a>
    <?php
}
?>