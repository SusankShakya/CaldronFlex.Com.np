<?php
$login_user_id = isset($login_user->id) ? $login_user->id : null;

if ($login_user_id) {
    $pusher_enabled = get_setting("enable_push_notification");
    $pusher_beams_instance_id = get_setting("pusher_beams_instance_id");
    $disable_push_notification = get_setting("user_" . $login_user_id . "_disable_push_notification");
    $pusher_beams_started = get_cookie("pusher_beams_started_" . $login_user_id);
    $web_notification_enabled = isset($login_user->enable_web_notification) ? $login_user->enable_web_notification : false;

    $pusher_beams_enabled = false;
    $lets_start_pusher_beams = false;
    $lets_stop_pusher_beams = false;

    if ($pusher_enabled && $pusher_beams_instance_id) {
        $pusher_beams_enabled = true;
    }

    if ($pusher_beams_enabled && $web_notification_enabled && !$pusher_beams_started && !$disable_push_notification) {
        $lets_start_pusher_beams = true;
    } else if ($pusher_beams_enabled && $pusher_beams_started && $disable_push_notification) {
        $lets_stop_pusher_beams = true;
    }

    if ($lets_start_pusher_beams || $lets_stop_pusher_beams) {
        load_js(array(
            "assets/js/push_notification/pusher/pusher.beams.min.js" //it's only needed for register push notification
        ));
    }
?>
    <script type='text/javascript'>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                //service worker for app
                navigator.serviceWorker.register("<?php echo get_uri('pwa/service_worker'); ?>", {
                    scope: '/'
                }).then(registration => {

                    <?php if ($lets_start_pusher_beams || $lets_stop_pusher_beams) { ?>

                        var beamsClient = new PusherPushNotifications.Client({
                            instanceId: "<?php echo $pusher_beams_instance_id; ?>",
                            serviceWorkerRegistration: registration
                        });

                        <?php if ($lets_start_pusher_beams) { ?>
                            beamsClient.start()
                                .then(() => beamsClient.addDeviceInterest("user_" + <?php echo $login_user_id; ?>))
                                .then(() => {
                                    <?php
                                    set_cookie("pusher_beams_started_" . $login_user_id, "1", 3600); //set the cookie for 1 hour. 
                                    set_cookie("pusher_beams_started_" . $login_user_id, "1", 3600, "", "/", "", true, false); //HTTP only = false for js. 
                                    ?>
                                    console.log("Pusher Beams started successfully!");
                                })
                                .catch(error => {
                                    console.log("Pusher Beams start failed: " + error);
                                });
                        <?php } ?>

                        <?php if ($lets_stop_pusher_beams) { ?>
                            beamsClient.stop()
                                .then(() => {
                                    <?php
                                    delete_cookie("pusher_beams_started_" . $login_user_id);
                                    ?>
                                    console.log("Pusher Beams stopped successfully!");
                                })
                                .catch(error => {
                                    console.log("Pusher Beams stop failed: " + error);
                                });
                        <?php } ?>

                    <?php } ?>

                }).catch(error => {
                    console.log("Service Worker registration failed: " + error);
                });

                navigator.serviceWorker.addEventListener('message', function(event) {
                    if (event.data && event.data.type === 'NOTIFICATION_CLICKED') {
                        var clickedData = (event.data.data && event.data.data.pusher && event.data.data.pusher.customerPayload && event.data.data.pusher.customerPayload.data) ? event.data.data.pusher.customerPayload.data : null;
                        if (clickedData) {
                            NotificationHelper.handleNotificationClick(clickedData);
                        }
                    }
                });
            });

        }
    </script>
<?php
}
?>