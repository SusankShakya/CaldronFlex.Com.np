<?php

namespace App\Libraries;

class Pusher_connect {
    private $channel_instance = null;
    public function __construct() {
        $this->_init_channel_instance();
    }

    public function is_channel_ready() {
        return $this->channel_instance ? true : false;
    }

    public function trigger_channel_event($channel, $event, $data) {
        if ($this->channel_instance) {
            return $this->channel_instance->trigger($channel, $event, $data);
        }
        return false;
    }

    public function trigger_beams_event($beams_interests, $notification_data) {
        $pusher_beams_instance_id = get_setting("pusher_beams_instance_id");
        $pusher_beams_primary_key = get_setting("pusher_beams_primary_key");
        if (!$pusher_beams_instance_id || !$pusher_beams_primary_key) {
            return false;
        }

        $url = "https://" . $pusher_beams_instance_id . ".pushnotifications.pusher.com/publish_api/v1/instances/" . $pusher_beams_instance_id . "/publishes";

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $pusher_beams_primary_key
        ];

        $data = [
            "interests" => $beams_interests,
            "web" => [
                "notification" => [
                    "title" => get_array_value($notification_data, "title"),
                    "body" => get_array_value($notification_data, "message"),
                    "icon" =>  get_array_value($notification_data, "icon"),
                    "hide_notification_if_site_has_focus" => true
                ],
                "data" => [
                    "notification_id" => get_array_value($notification_data, "notification_id"),
                    "url_attributes" => get_array_value($notification_data, "url_attributes")
                ]
            ]
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            log_message('error', '[ERROR] {exception}', ['exception' => curl_error($ch)]);
            return false;
        }

        curl_close($ch);
        return true;
    }


    private function _init_channel_instance() {
        if ($this->channel_instance) {
            return $this->channel_instance;
        }

        $pusher_app_id = get_setting("pusher_app_id");
        $pusher_key = get_setting("pusher_key");
        $pusher_secret = get_setting("pusher_secret");
        $pusher_cluster = get_setting("pusher_cluster");

        if ($pusher_app_id && $pusher_key && $pusher_secret && $pusher_cluster) {
            require_once(APPPATH . "ThirdParty/Pusher/vendor/autoload.php");

            $pusher = new \Pusher\Pusher(
                $pusher_key,
                $pusher_secret,
                $pusher_app_id,
                array(
                    'cluster' => $pusher_cluster,
                    'useTLS' => true,
                    'encrypted' => true
                )
            );
            $this->channel_instance = $pusher;
            return $pusher;
        }
    }
}
