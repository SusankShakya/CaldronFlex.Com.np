document.addEventListener('DOMContentLoaded', function () {
    NotificationHelper.initNotificationDropdownEvents();
    NotificationHelper.subscribePusherNotifications();

    (async () => {
        //run immediately but don't block the app. load notification counts from indexdb
        await NotificationHelper.renderTopbarNotificationCount("web", 0, "initial");
        await NotificationHelper.renderTopbarNotificationCount("message", 0, "initial");
    })();

    setTimeout(() => {
        NotificationHelper.initTimeBasedNotificationChecking("message");
        NotificationHelper.initTimeBasedNotificationChecking("web");
    }, 10000);
});

const NotificationHelper = (() => {
    const notificationSelector = {
        web: "#web-notification-icon",
        message: "#message-notification-icon"
    };

    const notificationName = {
        web: "web_notification_count",
        message: "message_notification_count"
    };

    let pusherSubscribed = false;

    function playNotificationSound() {
        const volume = _getNotificationSoundVolume();
        if (!volume) {
            return;
        }
        setTimeout(function () {
            const playerId = "notificationPlayer";
            if (!document.getElementById(playerId)) {
                const player = document.createElement("audio");
                player.src = AppHelper.notificationSoundSrc;
                player.id = playerId;
                player.type = "audio/mpeg";
                player.volume = volume;
                document.body.appendChild(player);
            }
            document.getElementById(playerId).play();
        });
    }

    function subscribePusherNotifications() {

        if (!_isPusherEnabled() || !_isHttps() || !_isWebNotificationEnabled()) {
            return;
        }

        const pusher = new Pusher(AppHelper.settings.pusherKey, {
            cluster: AppHelper.settings.pusherCluster,
            forceTLS: true
        });

        const channel = pusher.subscribe("user_" + AppHelper.userId + "_channel");

        channel.bind('rise-pusher-event', function (data) {
            if (data) {
                if (_canShowBrowserNotification(data)) {
                    showBrowserNotification(data);
                } else {
                    showAppNotification(data);
                }

                _fetchNotificationCount("web");
            }
        });
        channel.bind('pusher:subscription_succeeded', function () {
            pusherSubscribed = true;
        });
    }

    function showAppNotification(data) {
        var appAlertText = data.title + " " + data.message;
        if (data.url_attributes) {
            appAlertText = "<a class='color-white' " + data.url_attributes + ">" + appAlertText + "</a>";
        }
        appAlert.info(appAlertText, { duration: 10000 });
        playNotificationSound();
    }

    function showBrowserNotification(data) {
        var notificationData = {
            icon: data.icon,
            body: data.message,
            tag: data.notification_id, //to prevent multiple notifications for multiple tab
        };

        if (isMobile()) {
            showAppNotification(data);
        } else {
            var notification = new Notification(data.title, notificationData);

            var timeout = data.notificationTimeout ? data.notificationTimeout : 10000;
            setTimeout(notification.close.bind(notification), timeout);

            notification.onclick = function () {
                handleNotificationClick(data);
                //remove notification
                notification.close();
            };
        }

        playNotificationSound();
    };

    function requestBrowserNotificationPermission() {
        if (!_isHttps() || !_isWebNotificationEnabled() || !Notification || Notification.permission == 'denied') {
            return;
        }

        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }
    }

    function initTimeBasedNotificationChecking(type) {
        if (type == "web" && _isWebNotificationEnabled()) {
            _fetchNotificationCount(type, true);
        } else if (type == "message" && !_isChatViaPusherEnabled()) {
            _fetchNotificationCount(type, true);
        }
    }

    async function renderTopbarNotificationCount(type, count, countType = "reset") {

        if (type != "web" && type != "message") {
            return;
        }

        //countType = initial/fetch_count/reset

        const selectorId = notificationSelector[type];
        const indexedItemName = notificationName[type];

        const savedCount = await IDBHelper.getValue(indexedItemName);

        if (countType == "initial") {
            count = savedCount;
        } else {
            IDBHelper.setValue(indexedItemName, count);
        }

        let badge = "";
        if (count > 0) {
            badge = "<span class='badge bg-danger up'>" + count + "</span>";
        }
        $(selectorId).find(".notification-badge-container").html(badge);

        if (type == "message" && window.prepareUnreadMessageChatBox && count > 0) {
            window.prepareUnreadMessageChatBox(count);
        }

        //compaire if there are new notifications, if so, show the notification
        if (countType == "fetch_count" && count > savedCount && !pusherSubscribed) {
            playNotificationSound();
        }
    }

    function initNotificationDropdownEvents() {
        $(notificationSelector.message).on('click', function () {
            _fetchNotificationList("message", true);
        });

        $(notificationSelector.web).on('click', function () {
            _fetchNotificationList("web", true);
        });
    }

    function updateLastMessageCheckingStatus() {
        _updateLastNotificationCheckingStatus("message");
    }

    function _getNotificationSoundVolume() {
        if (AppHelper && AppHelper.settings && AppHelper.settings.notificationSoundVolume) {
            return Number("0." + AppHelper.settings.notificationSoundVolume) || 0;
        }
        return 0;
    }

    function _isHttps() {
        if (AppHelper && AppHelper.https == "1") {
            return true;
        }
    }

    function _isPusherEnabled() {
        return AppHelper && AppHelper.settings && AppHelper.settings.enablePushNotification == "1" && AppHelper.settings.pusherKey && AppHelper.settings.pusherCluster && typeof Pusher !== 'undefined';
    }

    function _isChatViaPusherEnabled() {
        return AppHelper && AppHelper.settings && AppHelper.settings.enableChatViaPusher == "1";
    }

    function _isWebNotificationEnabled() {
        return AppHelper && AppHelper.userId && AppHelper.settings && AppHelper.settings.userEnableWebNotification == "1";
    }

    function _canShowBrowserNotification(data) {
        if (data && data.test_event) {
            return true;
        }

        if (document.visibilityState === 'visible' && document.hasFocus()) {
            return false; // Page is fully active. better to show the notification in app
        }

        if (AppHelper && AppHelper.userId && getCookie("pusher_beams_started_" + AppHelper.userId) == "1") {
            return false; // Beams is already started. Don't show duplicate notification. Show in app notification
        }

        return _isHttps() && Notification && Notification.permission == 'granted' && AppHelper && AppHelper.settings && AppHelper.settings.userDisablePushNotification !== "1";
    }

    function _fetchNotificationList(type, updateStatus = false) {
        const $selector = $(notificationSelector[type]);
        const notificationListUrl = $selector.data("list_url");
        if (!notificationListUrl) {
            return;
        }
        appAjaxRequest({
            url: notificationListUrl,
            type: "POST",
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    $selector.parent().find(".dropdown-details").html(result.notification_list);

                    if (updateStatus) {
                        _updateLastNotificationCheckingStatus(type);
                    }
                }
            }
        });
    }

    function _fetchNotificationCount(type, recursive = false) {
        const countUrl = $(notificationSelector[type]).data("count_url");
        if (countUrl) {
            let data = { check_notification: 1 };

            if (type == "message") {
                data = { active_message_id: getCookie("active_chat_id") };
            }

            appAjaxRequest({
                url: countUrl,
                type: "POST",
                data: data,
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        let count = result.total_notifications || 0;
                        renderTopbarNotificationCount(type, Number(count), "fetch_count");
                        NotificationHelper.requestBrowserNotificationPermission();
                    }

                    if (recursive && !_isPusherEnabled()) {
                        let fetchInterval = $(notificationSelector[type]).data("fetch_interval") || 10;
                        fetchInterval = fetchInterval * 1000;
                        if (fetchInterval < 10000) {
                            fetchInterval = 10000; //don't allow to call this requiest before 10 seconds
                        }

                        //for chat, it should be 5000
                        if (type == "message") {
                            fetchInterval = 5000;
                        }
                        setTimeout(function () {
                            _fetchNotificationCount(type, recursive);
                        }, fetchInterval);
                    }

                    if (result.success && result.total_notifications > 0) {
                        NotificationHelper.requestBrowserNotificationPermission();
                    }
                }
            });
        }

    }

    function _updateLastNotificationCheckingStatus(type = "web") {

        const statusUpdateUrl = $(notificationSelector[type]).data("status_update_url");
        if (!statusUpdateUrl) {
            return;
        }
        appAjaxRequest({
            url: statusUpdateUrl,
            type: "POST",
            success: function () {
                renderTopbarNotificationCount(type, 0);
            }
        });
    }

    function handleNotificationClick(data) {
        if (data && data.url_attributes && data.notification_id) {
            var linkId = 'push-notification-link-' + data.notification_id;
            $("#default-navbar").append("<a id='" + linkId + "' " + data.url_attributes + "></a>");

            var $link = $("#" + linkId);

            //mark the notification as read
            if (!data.isReminder) {
                appAjaxRequest({
                    url: AppHelper.settings.pushNotficationMarkAsReadUrl + '/' + data.notification_id
                });
            }

            if ($link.attr("data-act")) {
                $link.trigger("click");  //if the link is modal
            } else if ($link.attr("href")) {
                window.location.href = $link.attr("href"); //if the link is not a modal
            }

            //remove link
            $link.remove();
            window.focus();
        }
    }

    return {
        initTimeBasedNotificationChecking,
        initNotificationDropdownEvents,
        playNotificationSound,
        renderTopbarNotificationCount,
        requestBrowserNotificationPermission,
        subscribePusherNotifications,
        showBrowserNotification,
        updateLastMessageCheckingStatus,
        handleNotificationClick
    };
})();
