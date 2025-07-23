<?php
if ($files && count($files)) {

    $group_id = make_random_string();

    $box_class = "mb15";
    $caption_class = "more";
    $caption_lang = " " . app_lang('more');
    if (isset($is_message_row)) {
        $box_class = "message-images mb5 mt5";
        $caption_class .= " message-more";
        $caption_lang = "";
    }

    $file_count = 0;

    echo "<div class='timeline-images app-modal-view " . $box_class . "'>";

    $is_localhost = is_localhost();

    $timeline_file_path = isset($file_path) ? $file_path : get_setting("timeline_file_path");

    // Initialize arrays to collect webm files and other files
    $recording_files = "";
    $other_files = "";
    $preview_image = "";

    // Separate webm files containing "recording" from other files
    foreach ($files as $file) {

        $file_name = get_array_value($file, "file_name");
        $file_id = get_array_value($file, "file_id");
        $service_type = get_array_value($file, "service_type");

        $is_google_drive_file = ($file_id && $service_type == "google") ? true : false;

        $actual_file_name = remove_file_prefix($file_name);
        $thumbnail = get_source_url_of_file($file, $timeline_file_path, "thumbnail");
        $url = get_source_url_of_file($file, $timeline_file_path);

        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $image = "";

        if ($file_id && $is_google_drive_file) {
            $url = get_source_url_of_google_drive_file($file_id, "", false, $actual_file_name);
        }

        if (isset($seperate_audio) && $seperate_audio && $extension === "webm" && strpos($file_name, 'recording')) {

            $actual_file_name_without_extension = remove_file_extension($actual_file_name);
            
            $recording_files .= "<audio src='$url' controls='' class='audio file-highlight-section' id='$actual_file_name_without_extension'></audio>";

        } else {

            if (is_viewable_image_file($file_name)) {

                if (!$file_count) {
                    $preview_image = "<img src='$thumbnail' alt='$file_name'/>";
                    $image = $preview_image;
                }
                $other_files .= "<a href='#' class='' data-toggle='app-modal' data-group='$group_id' data-sidebar='0' data-type='image'  data-content_url='$url' data-title='" . $actual_file_name . "'>$image</a>";

            } else if ($extension === "webm") {

                if (!$file_count) {
                    $preview_image = "<img src='" . get_file_uri("assets/images/video_preview.jpg") . "' alt='video'/>";
                    $image = $preview_image;
                }
                $other_files .= "<a href='#' class='' data-toggle='app-modal' data-group='$group_id' data-sidebar='0' data-type='audio'  data-content_url='$url' data-title='" . $actual_file_name . "'>$image</a>";
            } else if ($extension === "txt") {

                if (!$file_count) {
                    $preview_image = "<div class='inline-block'><div class='file-mockup'><i data-feather='" . get_file_icon($extension) . "' width='10rem' height='10rem' class='mt-12'></i></div></div>";
                    $image = $preview_image;
                }

                $other_files .= "<a href='#' class='' data-toggle='app-modal' data-group='$group_id' data-sidebar='0' data-type='txt' data-content_url='$url' data-title='" . $actual_file_name . "'>$image</a>";
            } else if (is_iframe_preview_available($file_name)) {

                if (!$file_count) {
                    $preview_image = "<div class='inline-block'><div class='file-mockup'><i data-feather='" . get_file_icon($extension) . "' width='10rem' height='10rem' class='mt-12'></i></div></div>";
                    $image = $preview_image;
                }

                $other_files .= "<a href='#' class='' data-toggle='app-modal' data-group='$group_id' data-sidebar='0' data-type='iframe'  data-content_url='$url' data-title='" . $actual_file_name . "'>$image</a>";
            } else if ((is_viewable_video_file($file_name) && !$file_id && $service_type != "google") || (is_viewable_video_file($file_name) && $file_id && $service_type == "google" && !get_setting("disable_google_preview"))) {

                if (!$file_count) {
                    $preview_image = "<img src='" . get_file_uri("assets/images/video_preview.jpg") . "' alt='video'/>";
                    $image = $preview_image;
                }
                $other_files .= "<a href='#' class='' data-toggle='app-modal' data-group='$group_id' data-sidebar='0' data-type='iframe'  data-content_url='$url' data-title='" . $actual_file_name . "'>$image</a>";
            } else {
                if (!$file_count) {
                    $preview_image = "<div class='inline-block'><div class='file-mockup'><i data-feather='" . get_file_icon($extension) . "' width='10rem' height='10rem' class='mt-12'></i></div></div>";
                    $image = $preview_image;
                }


                if (!$is_localhost && is_google_preview_available($file_name) && !get_setting("disable_google_preview")) {
                    $other_files .= "<a href='#' class='' data-toggle='app-modal' data-group='$group_id' data-sidebar='0' data-type='iframe'  data-content_url='https://drive.google.com/viewerng/viewer?url=$url?pid=explorer&efh=false&a=v&chrome=false&embedded=true' data-title='" . $actual_file_name . "'>$image</a>";
                } else {
                    $other_files .= "<a href='#' class='' data-toggle='app-modal' data-group='$group_id' data-sidebar='0' data-type='not_viewable' data-filename='$actual_file_name' data-description='" . app_lang("file_preview_is_not_available") . "'  data-content_url='$url' data-title='" . $actual_file_name . "'>$image</a>";
                }
            }


            $file_count++;
        }
    }

    $more_image = "";
    if ($file_count > 1) {
        $more_image = "<span class='$caption_class'>+" . ($file_count - 1) . $caption_lang . "</span>";
    }


    if ($recording_files) {
        echo $recording_files;
    }

    echo $other_files . $more_image;

    echo "</div>";
}
?>

<script>
    $(document).ready(function() {
        $(".file-highlight-link").click(function(e) {
            var fileId = $(this).attr('data-file-id');

            e.preventDefault();

            highlightSpecificFile(fileId);
        });

        function highlightSpecificFile(fileId) {
            $(".file-highlight-section").removeClass("file-highlight");
            $("#recording-" + fileId).addClass("file-highlight");
            window.location.hash = ""; //remove first to scroll with main link
            window.location.hash = "recording-" + fileId;
        }

    });
</script>