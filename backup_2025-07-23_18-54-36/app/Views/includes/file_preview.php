<?php
//always show the google drive files using iframe. 
if ($is_image_file) {
    echo "<img src='" . $file_url . "'>";
} else if ($is_viewable_video_file || (isset($is_iframe_preview_available) && $is_iframe_preview_available)) {
    //show with default iframe
?>

    <iframe id="iframe-file-viewer" src="<?php echo $file_url ?>" style="width: 100%; border: 0; height: 100%; background:#fff;"></iframe>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#iframe-file-viewer").closest("div.app-modal-content-area").css({
                "height": "100%",
                display: "table",
                width: "100%"
            });
        });
    </script>
<?php
} else if (!get_setting("disable_google_preview") && !is_localhost() && $is_google_preview_available) {
    //show some files using the google drive viewer
    //don't show in localhost
    //don't show if the google preive is disabled from config

    $src_url = "https://drive.google.com/viewerng/viewer?url=$file_url&pid=explorer&efh=false&a=v&chrome=false&embedded=true&usp=sharing";
?>
    <iframe id='google-file-viewer' src="<?php echo $src_url; ?>" style="width: 100%; height:100%; margin: 0; border: 0;"></iframe>
    <script type="text/javascript">
        $(document).ready(function() {
            $(".app-modal-content-area").css({
                "width": "100%"
            });
            $(".app-modal-content-area #google-file-viewer").css({
                height: $(window).height() + "px"
            });
        });
    </script>

<?php
} else {
    //Preview is not avaialble. 
    echo "<div class='text-white'>" . app_lang("file_preview_is_not_available") . "<br />";
    echo anchor($file_url, app_lang("download")) . "</div>";
}
?>