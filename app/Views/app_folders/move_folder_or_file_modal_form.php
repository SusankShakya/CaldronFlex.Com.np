<?php echo form_open(get_uri($controller_slag . "/move_file_or_folder"), array("id" => "move-folder-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="folder_id" value="<?php echo $folder_id; ?>" />
        <input type="hidden" name="file_id" value="<?php echo $file_id; ?>" />
        <input type="hidden" name="parent_id" id="parent_id" value="" />

        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <?php

                    function display_folders_as_menu($folders, $folder_id, $level = 0)
                    {
                        if ($level === 0) {
                            echo "<div class='file-manager'>";
                        }
                        foreach ($folders as $folder) {
                            if ($folder->id == $folder_id) {
                                // Skip this folder when it matches $folder_id
                                continue;
                            }

                            if (!empty($folder->subfolders)) {
                                echo '<div data-bs-toggle="collapse" data-bs-target="#folder-' . $folder->id . '" class="file-manager-anchor folder-list collapsed"><span data-folder_id="' . $folder->id . '" class="select-folder-for-move"><i data-feather="folder-plus" class="icon-14 me-3"></i>' . $folder->title . '</span></div>';
                                echo '<div class="collapse" id="folder-' . $folder->id . '">';
                                echo "<ul class='sub-folder'>";
                                display_folders_as_menu($folder->subfolders, $folder_id, $level + 1);
                                echo '</ul>';
                                echo '</div>';
                            } else {
                                echo '<div data-bs-toggle="collapse" data-bs-target="#folder-' . $folder->id . '" class="folder-list">';
                                echo '<span data-folder_id="' . $folder->id . '" class="select-folder-for-move"><i data-feather="folder" class="icon-14 me-3"></i>' . $folder->title . '</span>';
                                echo '<div class="collapse" id="folder-' . $folder->id . '"></div>';
                                echo '</div>';
                            }
                        }
                        if ($level === 0) {
                            echo '</div>';
                        }
                    }

                    display_folders_as_menu($hierarchical_folders, $folder_id);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#move-folder-form").appForm({
            onSuccess: function(result) {
                if (result.success) {
                    location.reload();
                }
            }
        });

        $(".select-folder-for-move").click(function() {
            $(".select-folder-for-move").removeClass("active");
            $(this).addClass("active");

            var folderId = $(this).data("folder_id");
            $("#parent_id").val(folderId);
        });
    });
</script>