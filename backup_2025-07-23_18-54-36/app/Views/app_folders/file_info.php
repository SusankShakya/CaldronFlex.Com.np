<?php
if (isset($file_info) && $file_info) {

    $sidebar = 0;
    if (isset($show_file_preview_sidebar) && $show_file_preview_sidebar) {
        $sidebar = 1;
    }

    $file_name = $file_info->file_name;
    echo "<div class='file-manager-preview-section overflow-hidden'>";
    echo view("includes/file_preview");
    echo "</div>";
?>

    <div>
        <div class=" text-break strong"><?php echo js_anchor(remove_file_prefix($file_name), array('title' => "", "data-group" => "details", "data-toggle" => "app-modal", "data-sidebar" => $sidebar, "data-url" => $file_preview_url)); ?></div>
        <?php
        if (isset($file_info->file_size) && $file_info->file_size) {
            echo "<div class='text-off b-b pb10'>" . convert_file_size($file_info->file_size) . "</div>";
        }
        ?>
    </div>
    <div class="pt20 pb20">
        <h4><?php echo app_lang("file_details"); ?></h4>
        <div class="text-off"><?php echo app_lang("uploaded_by"); ?></div>
        <ul class="list-group access-list">
            <li class="list-group-item">
                <?php
                if (isset($file_info->uploaded_by_user_name) && isset($file_info->uploaded_by) && $file_info->uploaded_by_user_name) {
                    echo get_team_member_profile_link($file_info->uploaded_by, $file_info->uploaded_by_user_name);
                } ?>
            </li>
        </ul>

        <div class="text-off"><?php echo app_lang("uploaded_at"); ?></div>
        <ul class="list-group access-list">
            <li class="list-group-item">
                <?php
                if (isset($file_info->created_at) && $file_info->created_at) {
                    echo format_to_relative_time($file_info->created_at);
                } ?>
            </li>
        </ul>
    </div>

<?php } ?>