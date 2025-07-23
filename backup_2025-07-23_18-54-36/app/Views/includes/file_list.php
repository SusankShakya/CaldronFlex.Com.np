<?php

$show_options = "";
if (isset($mode_type) && $mode_type == "view") {
    $show_options = "d-none";
}

if (isset($files) && $files) {
    $files = unserialize($files);
    if (count($files)) {
        $timeline_file_path = get_setting("timeline_file_path");

        foreach ($files as $key => $value) {
            $file_name = get_array_value($value, "file_name");
            $actual_file_name = remove_file_prefix($file_name);
            $actual_file_name_without_extension = remove_file_extension($actual_file_name);

            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            echo "<div class='col-md-2 col-sm-6 pr0 file-grid-view-container'>";

            if ((isset($mode_type) && $mode_type == "view") && (isset($context) && $context)) {
                $public_key = "";
                if ($context == "contract") {
                    $public_key = "/" . $model_info->public_key . "";
                }
                echo "<a href='#' data-toggle='app-modal' data-sidebar='0' data-url='" . get_uri("$context/file_preview/" . $model_info->id . "/" . $key . $public_key) . "'>";
            }

            if (is_viewable_image_file($file_name)) {
                $thumbnail = get_source_url_of_file($value, $timeline_file_path, "thumbnail");
                echo "<div class='saved-file-item-container' title='$actual_file_name'><div style='background-image: url($thumbnail)' class='edit-image-file mb15' ><span href='#' class='delete-saved-file $show_options' data-file_name='$file_name'><span data-feather='x' class='icon-16'></span></span></div></div>";
            } else if ($file_extension === "webm" && strpos($file_name, 'recording')) {
                $url = get_source_url_of_file($value, $timeline_file_path);
                echo "<div class='saved-file-item-container position-relative saved-recording-file' title='$actual_file_name'><div class='edit-image-file mb15'><div class='audio-container' class=''><audio src='$url' controls='' class='' id=''></audio></div><span href='#' class='delete-saved-file $show_options' data-file_name='$file_name'><span data-feather='x' class='icon-16'></span></span><span href='#' class='copy-file-link copy-file-link-btn $show_options' data-file-name='$actual_file_name_without_extension'><span data-feather='copy' class='icon-16'></span></span></div></div>";
            } else {
                echo "<div class='saved-file-item-container position-relative saved-recording-file' title='$actual_file_name'><div class='edit-image-file mb15'><div class='saved-file-info'><small>$file_extension</small></div><div class='other-saved-file-container'><span data-feather='file'></span></div><span href='#' class='delete-saved-file $show_options' data-file_name='$file_name'><span data-feather='x' class='icon-16'></span></span></div></div>";
            }

            if (isset($mode_type) && $mode_type == "view") {
                echo "</a>";
            }

            echo "</div>";
        }
    }
}
