<?php

namespace App\Controllers;

use App\Libraries\Google;
use App\Libraries\Google_calendar;
use App\Libraries\Google_calendar_events;
use App\Libraries\Gmail_imap;
use App\Libraries\Gmail_smtp;

class Google_api extends Security_Controller {

    private $google;
    private $Google_calendar;
    private $Google_calendar_events;
    private $Gmail_imap;
    private $Gmail_smtp;

    function __construct() {
        parent::__construct();
        $this->google = new Google();
        $this->Google_calendar = new Google_calendar();
        $this->Google_calendar_events = new Google_calendar_events();
        $this->Gmail_imap = new Gmail_imap();
        $this->Gmail_smtp = new Gmail_smtp();
    }

    function index() {
        app_redirect("google_api/authorize");
    }

    //authorize google drive
    function authorize() {
        $this->access_only_admin_or_settings_admin();
        $this->google->authorize();
    }

    //get access token of drive and save
    function save_access_token() {
        $this->access_only_admin_or_settings_admin();

        if (!empty($_GET)) {
            $this->google->save_access_token(get_array_value($_GET, 'code'));
            app_redirect("settings/integration/google_drive");
        }
    }

    //authorize google calendar
    function authorize_calendar() {
        $this->Google_calendar->authorize();
    }

    //get access code and save
    function save_access_token_of_calendar() {
        if (!empty($_GET)) {
            $this->Google_calendar->save_access_token(get_array_value($_GET, 'code'));
            app_redirect("settings/events");
        }
    }

    //authorize google calendar
    function authorize_own_calendar() {
        $this->Google_calendar_events->authorize($this->login_user->id);
    }

    //get access code and save
    function save_access_token_of_own_calendar() {
        if (!empty($_GET)) {
            $this->Google_calendar_events->save_access_token(get_array_value($_GET, 'code'), $this->login_user->id);
            app_redirect("events");
        }
    }

    //authorize gmail imap
    function authorize_gmail_imap() {
        $this->access_only_admin_or_settings_admin();
        $this->Gmail_imap->authorize();
    }

    //get access code and save
    function save_gmail_imap_access_token() {
        $this->access_only_admin_or_settings_admin();
        if (!empty($_GET)) {
            $this->Gmail_imap->save_access_token(get_array_value($_GET, 'code'));
            app_redirect("ticket_types/index/imap");
        }
    }

    //authorize gmail smtp
    function authorize_gmail_smtp() {
        $this->access_only_admin_or_settings_admin();
        $this->Gmail_smtp->authorize();
    }

    //get access code and save
    function save_gmail_smtp_access_token() {
        $this->access_only_admin_or_settings_admin();
        if (!empty($_GET)) {
            $this->Gmail_smtp->save_access_token(get_array_value($_GET, 'code'));
            app_redirect("settings/email");
        }
    }
}

/* End of file Google_api.php */
/* Location: ./app/controllers/Google_api.php */