<?php
$user_id = "";
$enable_web_notification = 0;

if (isset($login_user->id)) {
    $user_id = $login_user->id;
    $enable_web_notification = $login_user->enable_web_notification;
}

$https = 0;
if (substr(base_url(), 0, 5) == "https") {
    $https = 1;
}

$csrf_token_name = "";
$csrf_hash = "";

if (get_setting("csrf_protection")) {
    $csrf_token_name = csrf_token();
    $csrf_hash = csrf_hash();
}

$timepicker_minutes_interval = 5;
$timepicker_interval = get_escaped_value(get_setting("timepicker_minutes_interval"));

if ($timepicker_interval) {
    if ($timepicker_interval <= 0 || $timepicker_interval > 30) {
        $timepicker_minutes_interval = 5;
    } else {
        $timepicker_minutes_interval = $timepicker_interval;
    }
}

$upload_max_filesize = ini_get("upload_max_filesize");

if (!$upload_max_filesize) {
    $upload_max_filesize = "1M";
}

preg_match('/([a-zA-Z])/', $upload_max_filesize, $limit_type_data);
preg_match('|\d+|', $upload_max_filesize, $max_size);

$limit_type = get_array_value($limit_type_data, 0);
$max_filesize = intval(get_array_value($max_size, 0));
if ($limit_type && strtoupper($limit_type) == "M") {
    $max_filesize = $max_filesize * 1024 * 1024; //convert MB to byte
} else if ($limit_type && strtoupper($limit_type) == "G") {
    $max_filesize = $max_filesize * 1024 * 1024 * 1024; //convert GB to byte.
}


$custom_filters = get_setting("user_" . $user_id . "_filters"); // it won't work escaping here, it's already cleaned before saving
if (!$custom_filters) {
    $custom_filters = "a:0:{}";
}
$custom_filters = unserialize($custom_filters);

?>


<script type="text/javascript">
    AppHelper = {};
    AppHelper.baseUrl = "<?php echo base_url(); ?>";
    AppHelper.assetsDirectory = "<?php echo base_url("assets") . "/"; ?>";
    AppHelper.userId = "<?php echo $user_id; ?>";
    AppHelper.notificationSoundSrc = "<?php echo get_file_uri(get_setting("system_file_path") . "notification.mp3"); ?>";
    AppHelper.https = "<?php echo $https; ?>";
    AppHelper.csrfTokenName = "<?php echo $csrf_token_name; ?>";
    AppHelper.csrfHash = "<?php echo $csrf_hash; ?>";

    AppHelper.uploadPastedImageLink = "<?php echo get_uri("upload_pasted_image/save"); ?>";
    AppHelper.uploadMaxFileSize = "<?php echo echo_escaped_value($max_filesize); ?>";
    AppHelper.appVersion = "<?php echo get_setting("app_version"); ?>";

    AppHelper.settings = {};
    AppHelper.settings.firstDayOfWeek = "<?php echo_escaped_value((int) get_setting("first_day_of_week") * 1); ?>" || 0;
    AppHelper.settings.weekends = "<?php echo_escaped_value(get_setting("weekends")); ?>";

    AppHelper.settings.currencySymbol = "<?php echo_escaped_value(get_setting("currency_symbol")); ?>";
    AppHelper.settings.currencyPosition = "<?php echo_escaped_value(get_setting("currency_position")); ?>" || "left";
    AppHelper.settings.decimalSeparator = "<?php echo_escaped_value(get_setting("decimal_separator")); ?>";
    AppHelper.settings.thousandSeparator = "<?php echo_escaped_value(get_setting("thousand_separator")); ?>";
    AppHelper.settings.noOfDecimals = ("<?php echo_escaped_value(get_setting("no_of_decimals")); ?>" == "0") ? 0 : 2;
    AppHelper.settings.displayLength = "<?php echo_escaped_value(get_setting("rows_per_page")); ?>";

    AppHelper.settings.dateFormat = "<?php echo_escaped_value(get_setting("date_format")); ?>";
    AppHelper.settings.timeFormat = "<?php echo_escaped_value(get_setting("time_format")); ?>";
    AppHelper.settings.scrollbar = "<?php echo_escaped_value(get_setting("scrollbar")); ?>";
    AppHelper.settings.enableRichTextEditor = "<?php echo_escaped_value(get_setting('enable_rich_text_editor')); ?>";
    AppHelper.settings.wysiwygEditor = "<?php echo get_escaped_value(get_setting("rich_text_editor_name")) === "tinymce" ? "tinymce" : "summernote"; ?>";

    //push notification
    AppHelper.settings.enablePushNotification = "<?php echo_escaped_value(get_setting("enable_push_notification")); ?>";
    AppHelper.settings.enableChatViaPusher = "<?php echo_escaped_value(get_setting("enable_chat_via_pusher")); ?>";
    AppHelper.settings.userEnableWebNotification = "<?php echo_escaped_value($enable_web_notification); ?>";
    AppHelper.settings.userDisablePushNotification = "<?php echo_escaped_value(get_setting("user_" . $user_id . "_disable_push_notification")); ?>";
    AppHelper.settings.pusherKey = "<?php echo_escaped_value(get_setting("pusher_key")); ?>";
    AppHelper.settings.pusherCluster = "<?php echo_escaped_value(get_setting("pusher_cluster")); ?>";
    AppHelper.settings.pushNotficationMarkAsReadUrl = "<?php echo get_uri("notifications/set_notification_status_as_read"); ?>";
    AppHelper.settings.pusherBeamsInstanceId = "<?php echo_escaped_value(get_setting("pusher_beams_instance_id")); ?>";

    AppHelper.settings.notificationSoundVolume = "<?php echo_escaped_value(get_setting("user_" . $user_id . "_notification_sound_volume")); ?>";
    AppHelper.settings.disableKeyboardShortcuts = "<?php echo_escaped_value(get_setting('user_' . $user_id . '_disable_keyboard_shortcuts')); ?>";

    AppHelper.settings.disableResponsiveDataTableForMobile = "<?php echo_escaped_value(get_setting("disable_responsive_datatable_for_mobile")); ?>";
    AppHelper.settings.disableResponsiveDataTable = "<?php echo_escaped_value(get_setting("disable_responsive_datatable")); ?>";

    AppHelper.settings.defaultThemeColor = "<?php echo_escaped_value(get_setting("default_theme_color")); ?>";
    AppHelper.settings.timepickerMinutesInterval = <?php echo $timepicker_minutes_interval; ?>;

    AppHelper.settings.filters = <?php echo json_encode($custom_filters); ?>;
    AppHelper.settings.filterBar = "<?php echo_escaped_value(get_setting("filter_bar")); ?>";
</script>