<?php

/**
 * get user's time zone offset 
 * 
 * @return active users timezone
 */
if (!function_exists('get_timezone_offset')) {

    function get_timezone_offset($date = "now") {
        $timeZone = new DateTimeZone(get_setting("timezone"));
        $dateTime = new DateTime($date, $timeZone);
        return $timeZone->getOffset($dateTime);
    }
}

/**
 * convert a local time to UTC 
 * 
 * @param string $date
 * @param string $format
 * @return utc date
 */
if (!function_exists('convert_date_local_to_utc')) {

    function convert_date_local_to_utc($date = "", $format = "Y-m-d H:i:s") {
        if (!$date) {
            return "";
        }

        //local timezone
        $time_offset = get_timezone_offset($date) * -1;

        //add time offset
        return date($format, strtotime($date ? $date : "") + $time_offset);
    }
}

/**
 * get current utc time
 * 
 * @param string $format
 * @return utc date
 */
if (!function_exists('get_current_utc_time')) {

    function get_current_utc_time($format = "Y-m-d H:i:s") {
        $d = DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d H:i:s"));
        $d->setTimeZone(new DateTimeZone("UTC"));
        return $d->format($format);
    }
}

/**
 * convert a UTC time to local timezon as defined on users setting
 * 
 * @param string $date_time
 * @param string $format
 * @return local date
 */
if (!function_exists('convert_date_utc_to_local')) {

    function convert_date_utc_to_local($date_time, $format = "Y-m-d H:i:s") {
        $date = new DateTime($date_time . ' +00:00');
        $date->setTimezone(new DateTimeZone(get_setting('timezone')));
        return $date->format($format);
    }
}

/**
 * get current users local time
 * 
 * @param string $format
 * @return local date
 */
if (!function_exists('get_my_local_time')) {

    function get_my_local_time($format = "Y-m-d H:i:s") {
        return date($format, strtotime(get_current_utc_time()) + get_timezone_offset());
    }
}

/**
 * convert time string to 24 hours format 
 * 01:00 AM will be converted as 13:00:00 
 * 
 * @param string $time  required time format = 01:00 AM/PM
 * @return 24hrs time
 */
if (!function_exists('convert_time_to_24hours_format')) {

    function convert_time_to_24hours_format($time = "00:00 AM") {
        if (!$time)
            $time = "00:00 AM";

        if (strpos($time, "AM")) {
            $time = trim(str_replace("AM", "", $time));
            $check_time = explode(":", $time);
            if ($check_time[0] == 12) {
                $time = "00:" . get_array_value($check_time, 1);
            }
        } else if (strpos($time, "PM")) {
            $time = trim(str_replace("PM", "", $time));
            $check_time = explode(":", $time);
            if ($check_time[0] > 0 && $check_time[0] < 12) {
                $time = $check_time[0] + 12 . ":" . get_array_value($check_time, 1);
            }
        }

        $array_time = explode(":", $time);

        $hour = get_array_value($array_time, 0) ? get_array_value($array_time, 0) : "00";
        $minute = get_array_value($array_time, 1) ? get_array_value($array_time, 1) : "00";
        $secound = get_array_value($array_time, 2) ? get_array_value($array_time, 2) : "00";

        if ($hour < 10) {
            $hour = "0" . ($hour * 1);
        }

        return $hour . ":" . $minute . ":" . $secound;
    }
}

/**
 * convert time string to 12 hours format 
 * 13:00:00 will be converted as 01:00 AM
 * 
 * @param string $time  required time format =  00:00:00
 * @return 12hrs time
 */
if (!function_exists('convert_time_to_12hours_format')) {

    function convert_time_to_12hours_format($time = "", $is_short_time = false) {
        if ($time) {
            $am = " AM";
            $pm = " PM";
            if (get_setting("time_format") === "small") {
                $am = " am";
                $pm = " pm";
            }
            $check_time = explode(":", $time);
            $hour = $check_time[0] * 1;
            $minute = get_array_value($check_time, 1) * 1;
            $minute = ($minute < 10) ? "0" . $minute : $minute;

            $second = get_array_value($check_time, 2) * 1;
            $second = ($second < 10) ? ":0" . $second : ":" . $second;
            if (!$second) {
                $second = "00";
            } else if ($is_short_time) {
                $second = "";
            }

            if ($hour == 0) {
                $time = "12:" . $minute . $second . $am;
            } else if ($hour == 12) {
                $time = $hour . ":" . $minute . $second . $pm;
            } else if ($hour > 12) {
                $hour = $hour - 12;
                $hour = ($hour < 10) ? "0" . $hour : $hour;
                $time = $hour . ":" . $minute . $second . $pm;
            } else {
                $hour = ($hour < 10) ? "0" . $hour : $hour;
                $time = $hour . ":" . $minute . $second . $am;
            }
            return $time;
        }
    }
}

/**
 * prepare a decimal value from a time string
 * 
 * @param string $time  required time format =  00:00:00
 * @return number
 */
if (!function_exists('convert_time_string_to_decimal')) {

    function convert_time_string_to_decimal($time = "00:00:00") {
        $hms = explode(":", $time);
        return $hms[0] + (get_array_value($hms, "1") / 60) + (get_array_value($hms, "2") / 3600);
    }
}

/**
 * prepare a human readable time format from a decimal value of seconds
 * 
 * @param string $seconds
 * @return time
 */
if (!function_exists('convert_seconds_to_time_format')) {

    function convert_seconds_to_time_format($seconds = 0) {
        $is_negative = false;
        if ($seconds < 0) {
            $seconds = $seconds * -1;
            $is_negative = true;
        }
        $seconds = $seconds * 1;
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds - ($hours * 3600)) / 60);
        $secs = floor($seconds % 60);

        $hours = ($hours < 10) ? "0" . $hours : $hours;
        $mins = ($mins < 10) ? "0" . $mins : $mins;
        $secs = ($secs < 10) ? "0" . $secs : $secs;

        $string = $hours . ":" . $mins . ":" . $secs;
        if ($is_negative) {
            $string = "-" . $string;
        }
        return $string;
    }
}

/**
 * get seconds form a given time string
 * 
 * @param string $time
 * @return seconds
 */
if (!function_exists('convert_time_string_to_second')) {

    function convert_time_string_to_second($time = "00:00:00") {
        $hms = explode(":", $time);
        return $hms[0] * 3600 + ($hms[1] * 60) + ($hms[2]);
    }
}


/**
 * convert a datetime string to relative time 
 * ex: $date_time = "2015-01-01 23:10:00" will return like this: Today at 23:10 PM
 * 
 * @param string $date_time .. it will be considered as UTC time.
 * @param string $convert_to_local .. to prevent conversion, pass $convert_to_local=false 
 * @return date time
 */
if (!function_exists('format_to_relative_time')) {

    function format_to_relative_time($date_time, $convert_to_local = true, $is_short_date = false, $is_short_time = false) {
        $date_time = filter_valid_datetime_string($date_time);
        if (!$date_time) {
            return "";
        }

        if ($convert_to_local) {
            $date_time = convert_date_utc_to_local($date_time);
        }

        $target_date = new DateTime($date_time);
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone(get_setting('timezone')));
        $today = $now->format("Y-m-d");
        $date = "";
        $short_date = "";
        if ($now->format("Y-m-d") == $target_date->format("Y-m-d")) {
            $date = app_lang("today_at");   //today
            $short_date = app_lang("today");
        } else if (date('Y-m-d', strtotime(' -1 day', strtotime($today))) === $target_date->format("Y-m-d")) {
            $date = app_lang("yesterday_at"); //yesterday
            $short_date = app_lang("yesterday");
        } else {
            $date = format_to_date($date_time);
            $short_date = format_to_date($date_time);
        }
        if ($is_short_date) {
            return $short_date;
        } else if ($is_short_time) {
            if (get_setting("time_format") == "24_hours") {
                return $date . " " . $target_date->format("H:i");
            } else {
                return $date . " " . convert_time_to_12hours_format($target_date->format("H:i:s"), true);
            }
        } else {
            if (get_setting("time_format") == "24_hours") {
                return $date . " " . $target_date->format("H:i");
            } else {
                return $date . " " . convert_time_to_12hours_format($target_date->format("H:i:s"));
            }
        }
    }
}

/**
 * convert a datetime string to date format as defined on settings
 * ex: $date_time = "2015-01-01 23:10:00" will return like this: Today at 23:10 PM
 * 
 * @param string $date_time .. it will be considered as UTC time.
 * @param string $convert_to_local .. to prevent conversion, pass $convert_to_local=false 
 * @return date
 */
if (!function_exists('format_to_date')) {

    function format_to_date($date_time, $convert_to_local = true) {

        $date_time = filter_valid_datetime_string($date_time);
        if (!$date_time) {
            return "";
        }

        if ($convert_to_local) {
            $date_time = convert_date_utc_to_local($date_time);
        }

        $target_date = new DateTime($date_time);
        return $target_date->format(get_setting('date_format'));
    }
}

/**
 * convert a datetime string to 12 hours time format
 * 
 * @param string $date_time .. it will be considered as UTC time.
 * @param string $convert_to_local .. to prevent conversion, pass $convert_to_local=false 
 * @return time
 */
if (!function_exists('format_to_time')) {

    function format_to_time($date_time, $convert_to_local = true, $is_short_time = false) {
        $date_time = filter_valid_datetime_string($date_time);
        if (!$date_time) {
            return "";
        }

        if ($convert_to_local) {
            $date_time = convert_date_utc_to_local($date_time);
        }

        $target_date = new DateTime($date_time);

        if (get_setting("time_format") == "24_hours") {
            return $target_date->format("H:i");
        } else {
            if ($is_short_time) {
                return convert_time_to_12hours_format($target_date->format("H:i:s"), true);
            } else {
                return convert_time_to_12hours_format($target_date->format("H:i:s"));
            }
        }
    }
}

if (!function_exists('filter_valid_datetime_string')) {

    function filter_valid_datetime_string($date_time) {
        if (empty($date_time)) {
            return "";
        }

        $date_time_parts = explode(" ", $date_time);
        $date = get_array_value($date_time_parts, 0);

        $date_parts = explode("-", $date);
        $year = get_array_value($date_parts, 0);
        $month = get_array_value($date_parts, 1);
        $day = get_array_value($date_parts, 2);

        if (count($date_parts) < 3 || empty($year) || empty($month) || empty($day)) {
            return ""; // Return empty if the date is not valid
        }

        if ($year && !is_numeric($year) || strlen($year) != 4) {
            return "";
        }

        if ($month && !is_numeric($month) || strlen($month) != 2) {
            return "";
        }

        if ($day && !is_numeric($day) || strlen($day) != 2) {
            return "";
        }

        $time = get_array_value($date_time_parts, 1) ? get_array_value($date_time_parts, 1) : "";
        $time_parts = $time ? explode(":", $time) : [];

        // Validate time components (hours, minutes, seconds)
        if (count($time_parts)) {

            $hour = get_array_value($time_parts, 0);
            $minute = get_array_value($time_parts, 1);
            $second = get_array_value($time_parts, 2);

            if (count($time_parts) < 2 || empty($hour) || empty($minute)) {
                $time = "";
            }

            if ($hour && !is_numeric($hour) || (int) $hour < 0 || (int)$hour > 23) {
                $time = "";
            }
            if ($minute && !is_numeric($minute) || (int)$minute < 0 || (int)$minute > 59) {
                $time = "";
            }
            if ($second && !is_numeric($second) || (int)$second < 0 || (int)$second > 59) {
                $time = "";
            }

            if ($time) {
                if (!$hour) {
                    $hour = "00";
                }
                if (!$second) {
                    $second = "00";
                }
                if (!$minute) {
                    $minute = "00";
                }

                $time = " " . $hour . ":" . $minute . ":" . $second;
            }
        }

        return $date . $time;
    }
}

/**
 * convert a datetime string to datetime format as defined on settings
 * 
 * @param string $date_time .. it will be considered as UTC time.
 * @param string $convert_to_local .. to prevent conversion, pass $convert_to_local=false 
 * @return date time
 */
if (!function_exists('format_to_datetime')) {

    function format_to_datetime($date_time, $convert_to_local = true) {
        $date_time = filter_valid_datetime_string($date_time);
        if (!$date_time) {
            return "";
        }
        if ($convert_to_local) {
            $date_time = convert_date_utc_to_local($date_time);
        }
        $target_date = new DateTime($date_time);
        $date = $target_date->format(get_setting('date_format'));

        if (get_setting("time_format") == "24_hours") {
            return $date . " " . $target_date->format("H:i");
        } else {
            return $date . " " . convert_time_to_12hours_format($target_date->format("H:i:s"));
        }
    }
}



/**
 * return users local time (today)
 * 
 * @return date
 */
if (!function_exists('get_today_date')) {

    function get_today_date() {
        return date("Y-m-d", strtotime(get_my_local_time()));
    }
}


/**
 * return users local time (tomorrow)
 * 
 * @return date
 */
if (!function_exists('get_tomorrow_date')) {

    function get_tomorrow_date() {
        $today = get_today_date();
        return date('Y-m-d', strtotime($today . ' + 1 days'));
    }
}

/**
 * add days with a given date
 * 
 * $date should be Y-m-d
 * $period_type should be days/months/years/weeks
 * 
 * @return date
 */
if (!function_exists('add_period_to_date')) {

    function add_period_to_date($date, $no_of = 0, $period_type = "days", $format = "Y-m-d") {
        return date($format, strtotime("+$no_of $period_type", strtotime($date ? $date : "")));
    }
}

/**
 * subtract days from a given date
 * 
 * $date should be Y-m-d
 * $period_type should be days/months/years/weeks/hours
 * 
 * @return date
 */
if (!function_exists('subtract_period_from_date')) {

    function subtract_period_from_date($date, $no_of = 0, $period_type = "days", $format = "Y-m-d") {
        return date($format, strtotime("-$no_of $period_type", strtotime($date ? $date : "")));
    }
}


/**
 * get date difference in days
 * 
 * $start_date && $end_date should be Y-m-d format
 * 
 * @return difference in days
 */
if (!function_exists('get_date_difference_in_days')) {

    function get_date_difference_in_days($start_date, $end_date) {

        if (!$start_date) {
            $start_date = "";
        }

        if (!$end_date) {
            $end_date = "";
        }

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);

        return $end->diff($start)->format("%a");
    }
}


/**
 * is online user? if the last online <= 1 minute then we'll assume that the user is online
 * 
 * $start_date && $end_date 
 * 
 * @return boolean
 */
if (!function_exists('is_online_user')) {

    function is_online_user($last_online = "") {

        if (!$last_online) {
            //if we don't get any last online status that means the user is offline
            return false;
        } else {
            //if last online <= 5 minute then we'll assume that the user is online

            $now = get_my_local_time();
            $last_online = convert_date_utc_to_local($last_online);

            $diff_seconds = abs(strtotime($now) - strtotime($last_online));
            if ($diff_seconds <= (60 * 5)) {
                return true;
            } else {
                return false;
            }
        }
    }
}



/**
 * Check if the date string is not empty.
 * 
 * $date 
 * 
 * @return boolean
 */
if (!function_exists('is_date_exists')) {

    function is_date_exists($date = "") {

        if (!$date || $date == "NULL" || is_null($date) || $date == "0000-00-00") {
            return false;
        } else {
            return true;
        }
    }
}

//convert to hours from humanize data
if (!function_exists('convert_humanize_data_to_hours')) {

    function convert_humanize_data_to_hours($hours = "") {
        require_once(APPPATH . "ThirdParty/php-duration-master/init.php");

        $duration = \Init_duration::init($hours);
        $hours = $duration->toMinutes(null, 0); //convert in minutes
        $hours = $hours / 60; //final hours format

        return round($hours, 2);
    }
}

//convert humanize data to hours
if (!function_exists('convert_hours_to_humanize_data')) {

    function convert_hours_to_humanize_data($hours = "") {
        require_once(APPPATH . "ThirdParty/php-duration-master/init.php");

        $duration = \Init_duration::init($hours . "h");
        $minutes = round($duration->toMinutes(), 0); //remove decimals from minutes first

        $duration = \Init_duration::init($minutes * 60);
        return $duration->humanize();
    }
}

//prepare last recently date time
if (!function_exists('prepare_last_recently_date_time')) {

    function prepare_last_recently_date_time($login_user_id = 0) {
        $now = get_current_utc_time();
        $recently_meaning = get_setting("user_" . $login_user_id . "_recently_meaning");
        $recently_meaning = $recently_meaning ? $recently_meaning : "1_days";

        $explode_recently_meaning = explode('_', $recently_meaning);
        $no_of = get_array_value($explode_recently_meaning, 0);
        $period_type = get_array_value($explode_recently_meaning, 1);

        return subtract_period_from_date($now, $no_of, $period_type, "Y-m-d H:i:s");
    }
}

/**
 * return date from datetime
 * 
 * $date 
 * 
 * @return date
 */
if (!function_exists('get_date_from_datetime')) {

    function get_date_from_datetime($date = "") {
        if ($date && is_date_exists($date)) {
            return substr($date, 0, 10);
        } else {
            return "";
        }
    }
}

/**
 * return time from datetime
 * 
 * $date 
 * 
 * @return date
 */
if (!function_exists('get_time_from_datetime')) {

    function get_time_from_datetime($date = "") {
        if ($date && is_date_exists($date)) {
            return substr($date, 11);
        } else {
            return "";
        }
    }
}

/**
 * Return time from datetime in a relative format
 * 
 * This function calculates the time difference between the current time 
 * 
 * @param bool $short Whether to return short format (e.g., "5 min") or long format (e.g., "5 minutes ago")
 * 
 * @return string Relative time label (e.g., "5 min", "7 h", "10 d", "4 mo", "2 y")
 */
if (!function_exists('format_since_then')) {
    function format_since_then($datetime, $short = true) {
        $current_time = strtotime(get_current_utc_time());
        $target_time = strtotime($datetime);
        $diff_seconds = $current_time - $target_time;

        // Calculate time differences
        $minute = 60;
        $hour = 60 * 60;
        $day = 24 * 60 * 60;
        $month = 30 * 24 * 60 * 60;
        $year = 12 * $month;

        // If the difference is less than 5 minutes, show "just now"
        if ($diff_seconds < (2 * $minute)) {
            return app_lang('just_now');
        }

        // Determine time difference and format accordingly
        if ($diff_seconds < $hour) {
            $minutes = floor($diff_seconds / $minute);
            return $minutes . ($short ? " min" : ($minutes > 1 ? " " . app_lang('minutes_ago') : " " . app_lang('minute_ago')));
        } elseif ($diff_seconds < $day) {
            $hours = floor($diff_seconds / $hour);
            return $hours . ($short ? " h" : ($hours > 1 ? " " . app_lang('hours_ago') : " " . app_lang('hour_ago')));
        } elseif ($diff_seconds < $month) {
            $days = floor($diff_seconds / $day);
            return $days . ($short ? " d" : ($days > 1 ? " " . app_lang('days_ago') : " " . app_lang('day_ago')));
        } elseif ($diff_seconds < $year) {
            $months = floor($diff_seconds / $month);
            return $months . ($short ? " m" : ($months > 1 ? " " . app_lang('months_ago') : " " . app_lang('month_ago')));
        } else {
            $years = floor($diff_seconds / $year);
            return $years . ($short ? " y" : ($years > 1 ? " " . app_lang('years_ago') : " " . app_lang('year_ago')));
        }
    }
}
