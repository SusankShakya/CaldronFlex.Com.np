<?php

namespace App\Controllers;

use App\Libraries\Imap;
use App\Libraries\Stripe;

class Settings extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
    }

    function index() {
        app_redirect('settings/general');
    }

    function general() {
        return $this->template->rander("settings/general");
    }

    function save_general_settings() {
        $settings = array("site_logo", "favicon", "show_background_image_in_signin_page", "show_logo_in_signin_page", "app_title", "accepted_file_formats", "landing_page", "item_purchase_code", "default_theme_color");
        $has_php_file_format = false;

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if ($setting == "landing_page" || $setting == "show_logo_in_signin_page" || $setting == "show_background_image_in_signin_page") {
                $this->Settings_model->save_setting($setting, $value); //can be saved as blank also
            } else if ($value || $value === "0") {
                if ($setting === "site_logo") {
                    $value = str_replace("~", ":", $value);
                    $value = serialize(move_temp_file("site-logo.png", get_setting("system_file_path"), "", $value));

                    //delete old file
                    delete_app_files(get_setting("system_file_path"), get_system_files_setting_value("site_logo"));
                } else if ($setting === "item_purchase_code" && $value === "******") {
                    $value = get_setting('item_purchase_code');
                } else if ($setting === "favicon") {
                    $value = str_replace("~", ":", $value);
                    $value = serialize(move_temp_file("favicon.png", get_setting("system_file_path"), "", $value));

                    //delete old file
                    if (get_setting("favicon")) {
                        delete_app_files(get_setting("system_file_path"), get_system_files_setting_value("favicon"));
                    }
                } else if ($setting === "accepted_file_formats") {
                    //php file format should not be saved
                    $file_formats = explode(',', $value);
                    if (($key = array_search("php", $file_formats)) !== false) {
                        $has_php_file_format = true;
                        unset($file_formats[$key]);
                        $value = implode(",", $file_formats);
                    }
                }


                $this->Settings_model->save_setting($setting, $value);
            }
        }

        $reload_page = false;

        //save signin page background
        $files_data = move_files_from_temp_dir_to_permanent_dir(get_setting("system_file_path"), "system");
        $unserialize_files_data = unserialize($files_data);
        $sigin_page_background = get_array_value($unserialize_files_data, 0);
        if ($sigin_page_background) {
            delete_app_files(get_setting("system_file_path"), get_system_files_setting_value("signin_page_background"));
            $this->Settings_model->save_setting("signin_page_background", serialize($sigin_page_background));
            $reload_page = true;
        }

        if ($_FILES) {
            $files = array("site_logo_file", "favicon_file");

            foreach ($files as $file) {
                $file_data = get_array_value($_FILES, $file);

                if (!($file_data && is_array($file_data) && count($file_data))) {
                    continue;
                }

                $temp_name = get_array_value($file_data, "tmp_name");
                $file_name = get_array_value($file_data, "name");
                $file_size = get_array_value($file_data, "size");
                if (!$file_name) {
                    continue;
                }

                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                $new_file_name = "site-logo." . $file_ext;

                $setting_name = "site_logo";
                if ($file === "favicon_file") {
                    $new_file_name = "favicon." . $file_ext;
                    $setting_name = "favicon";
                }

                $new_file_data = serialize(move_temp_file($new_file_name, get_setting("system_file_path"), "", $temp_name, "", "", false, $file_size));
                //delete old file
                delete_app_files(get_setting("system_file_path"), get_system_files_setting_value($setting_name));
                $this->Settings_model->save_setting($setting_name, $new_file_data);
            }

            $reload_page = true;
        }

        try {
            app_hooks()->do_action("app_hook_general_settings_save_data");
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
        }

        if ($has_php_file_format) {
            echo json_encode(array("success" => false, 'message' => app_lang('php_file_format_is_not_allowed')));
        } else {
            echo json_encode(array("success" => true, 'message' => app_lang('settings_updated'), 'reload_page' => $reload_page));
        }
    }

    function ui_options() {
        return $this->template->view("settings/ui_options");
    }

    function save_ui_options_settings() {
        $settings = array("rows_per_page", "scrollbar", "enable_rich_text_editor", "show_theme_color_changer", "enable_audio_recording", "filter_bar");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if ($setting == "enable_audio_recording") {
                $file_formates = explode(',', get_setting("accepted_file_formats"));
                if ($value == 1 && !in_array("webm", $file_formates)) {
                    echo json_encode(array("success" => false, 'message' => app_lang('add_webm_file_format_to_enable_audio_recording')));
                    exit();
                }
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function email() {
        return $this->template->rander("settings/email");
    }

    function save_email_settings() {
        $settings = array("email_sent_from_address", "email_sent_from_name", "email_protocol", "email_smtp_host", "email_smtp_port", "email_smtp_user", "email_smtp_pass", "email_smtp_security_type", "outlook_smtp_client_id", "outlook_smtp_client_secret", "gmail_smtp_client_id", "gmail_smtp_client_secret");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (!$value) {
                $value = "";
            }

            if ($setting == "email_smtp_pass" || $setting == "gmail_smtp_client_secret" || $setting == "outlook_smtp_client_secret") {
                if ($value === "******") {
                    $value = get_setting($setting);
                } else {
                    $value = encode_id($value, $setting);
                }
            } else {
                $value = remove_quotations($value);
            }

            $this->Settings_model->save_setting($setting, $value);

            //reload the configs
            config('Rise')->app_settings_array[$setting] = $value;
        }

        //if user change credentials, flag smtp as unauthorized
        $this->Settings_model->save_setting("smtp_authorized", "0");

        $test_email_to = $this->request->getPost("send_test_mail_to");
        $email_protocol = $this->request->getPost("email_protocol");

        if (!$test_email_to) {
            echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
            return true;
        }

        if ($test_email_to && ($email_protocol === "microsoft_outlook" || $email_protocol === "gmail_smtp")) {
            // for microsoft smtp we've to send the test email after authorization
            $this->Settings_model->save_setting("send_test_mail_to", $test_email_to);
            echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
            return true;
        }

        if (send_app_mail($test_email_to, "Test message", "This is a test message to check mail configuration.")) {
            echo json_encode(array("success" => true, 'message' => app_lang('test_mail_sent')));
            return false;
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('test_mail_send_failed')));
            return false;
        }
    }

    function ip_restriction() {
        return $this->template->rander("settings/ip_restriction");
    }

    function save_ip_settings() {
        $this->Settings_model->save_setting("allowed_ip_addresses", $this->request->getPost("allowed_ip_addresses"));

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function client_permissions() {
        $team_members = $this->Users_model->get_all_where(array("deleted" => 0, "user_type" => "staff"))->getResult();
        $members_dropdown = array();

        foreach ($team_members as $team_member) {
            $members_dropdown[] = array("id" => $team_member->id, "text" => $team_member->first_name . " " . $team_member->last_name);
        }

        $hidden_menus = array(
            "announcements",
            "contracts",
            "events",
            "proposals",
            "estimates",
            "invoices",
            "subscriptions",
            "knowledge_base",
            "projects",
            "payments",
            "store",
            "tickets"
        );

        $hidden_menu_dropdown = array();
        foreach ($hidden_menus as $hidden_menu) {
            $hidden_menu_dropdown[] = array("id" => $hidden_menu, "text" => app_lang($hidden_menu));
        }

        $view_data['hidden_menu_dropdown'] = json_encode($hidden_menu_dropdown);
        $view_data['members_dropdown'] = json_encode($members_dropdown);
        $view_data["available_menus_for_clients_dropdown"] = get_available_menus_for_clients_dropdown();

        $view_data['project_tabs_dropdown'] = $this->_get_client_project_tabs_dropdown();

        return $this->template->rander("settings/client_permissions", $view_data);
    }

    function save_client_settings() {
        $settings = array(
            "disable_client_login",
            "disable_client_signup",
            "client_message_users",
            "hidden_client_menus",
            "client_can_create_projects",
            "client_can_create_tasks",
            "client_can_edit_tasks",
            "client_can_assign_tasks",
            "client_can_view_tasks",
            "client_can_comment_on_tasks",
            "client_can_view_project_files",
            "client_can_add_project_files",
            "client_can_comment_on_files",
            "client_can_delete_own_files_in_project",
            "client_can_view_milestones",
            "client_can_view_overview",
            "client_can_view_gantt",
            "client_can_view_files",
            "client_can_add_files",
            "client_can_edit_projects",
            "client_can_view_activity",
            "client_message_own_contacts",
            "disable_user_invitation_option_by_clients",
            "client_can_access_store",
            "disable_access_favorite_project_option_for_clients",
            "disable_editing_left_menu_by_clients",
            "disable_topbar_menu_customization",
            "disable_dashboard_customization_by_clients",
            "verify_email_before_client_signup",
            "client_can_create_reminders",
            "client_can_access_notes",
            "default_permissions_for_non_primary_contact",
            "project_tab_order_of_clients"
        );

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        try {
            app_hooks()->do_action("app_hook_client_permissions_save_data");
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function invoices() {
        $view_data["last_id"] = $this->Invoices_model->get_last_invoice_sequence();
        return $this->template->rander("settings/invoices/index", $view_data);
    }

    function invoice_general() {
        return $this->template->view("settings/invoices/invoice_general");
    }

    function invoice_reminders() {
        return $this->template->view("settings/invoices/invoice_reminders");
    }

    function save_invoice_settings() {
        $settings = array("invoice_prefix", "invoice_color", "invoice_item_list_background", "enable_background_image_for_invoice_pdf", "set_invoice_pdf_background_only_on_first_page", "invoice_footer",  "invoice_style", "initial_number_of_the_invoice", "invoice_number_format", "year_based_on", "reset_invoice_number_every_year", "enable_invoice_id_editing");
        $reload_page = false;

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if ($setting == "invoice_footer") {
                $value = decode_ajax_post_data($value);
            }

            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);

            if ($setting === "initial_number_of_the_invoice") {
                $this->Invoices_model->save_initial_number_of_invoice($value);
            }
        }

        //save invoice pdf background image
        $files_data = move_files_from_temp_dir_to_permanent_dir(get_setting("timeline_file_path"), "invoice");
        $unserialize_files_data = unserialize($files_data);
        $invoice_pdf_background_image = get_array_value($unserialize_files_data, 0);
        if ($invoice_pdf_background_image) {
            delete_app_files(get_setting("timeline_file_path"), get_system_files_setting_value("invoice_pdf_background_image"));
            $this->Settings_model->save_setting("invoice_pdf_background_image", serialize($invoice_pdf_background_image));
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated'), "reload_page" => $reload_page));
    }


    function save_invoice_general_settings() {
        $settings = array("default_due_date_after_billing_date", "send_bcc_to", "allow_partial_invoice_payment_from_clients", "client_can_pay_invoice_without_login", "enable_invoice_lock_state", "generate_reports_based_on");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }


    function e_invoice() {
        $E_invoice_templates_model = model("App\Models\E_invoice_templates_model");
        $view_data['e_invoice_templates_dropdown'] = array("" => "-") + $E_invoice_templates_model->get_dropdown_list(array("title"), "id");
        $view_data['e_invoice_templates_dropdown_for_credit_note'] = $view_data['e_invoice_templates_dropdown'];

        return $this->template->view("settings/invoices/e_invoice", $view_data);
    }

    function save_e_invoice_settings() {
        $settings = array("enable_e_invoice", "default_e_invoice_template", "default_e_invoice_template_for_credit_note", "send_e_invoice_attachment_with_email");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function save_invoice_reminders_settings() {
        $settings = array("send_invoice_due_pre_reminder", "send_invoice_due_pre_second_reminder", "send_invoice_due_after_reminder", "send_invoice_due_after_second_reminder", "send_recurring_invoice_reminder_before_creation");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function events() {
        return $this->template->rander("settings/events");
    }

    function save_event_settings() {
        $settings = array("enable_google_calendar_api", "google_calendar_client_id", "google_calendar_client_secret");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            if ($setting == "google_calendar_client_secret") {
                if ($value === "******") {
                    $value = get_setting($setting);
                } else {
                    $value = encode_id($value, $setting);
                }
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        //if user change credentials, flag google calendar as unauthorized
        $this->Settings_model->save_setting('google_calendar_authorized', "0");

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function notifications() {
        $category_suggestions = array(
            array("id" => "", "text" => "- " . app_lang('category') . " -"),
            array("id" => "announcement", "text" => app_lang("announcement")),
            array("id" => "client", "text" => app_lang("client")),
            array("id" => "contract", "text" => app_lang("contract")),
            array("id" => "event", "text" => app_lang("event")),
            array("id" => "estimate", "text" => app_lang("estimate")),
            array("id" => "invoice", "text" => app_lang("invoice")),
            array("id" => "leave", "text" => app_lang("leave")),
            array("id" => "lead", "text" => app_lang("lead")),
            array("id" => "message", "text" => app_lang("message")),
            array("id" => "order", "text" => app_lang("order")),
            array("id" => "project", "text" => app_lang("project")),
            array("id" => "proposal", "text" => app_lang("proposal")),
            array("id" => "subscription", "text" => app_lang("subscription")),
            array("id" => "ticket", "text" => app_lang("ticket")),
            array("id" => "timeline", "text" => app_lang("timeline")),
            array("id" => "general_task", "text" => app_lang("general_task"))
        );

        //get data from hook to show in filter
        try {
            $category_suggestions = app_hooks()->apply_filters('app_filter_notification_category_suggestion', $category_suggestions);
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
        }

        $view_data['categories_dropdown'] = json_encode($category_suggestions);
        return $this->template->rander("settings/notifications/index", $view_data);
    }

    function notification_modal_form() {
        $id = $this->request->getPost("id");
        if ($id) {

            helper('notifications');

            $model_info = $this->Notification_settings_model->get_details(array("id" => $id))->getRow();
            $notify_to = get_notification_config($model_info->event, "notify_to");

            if (!$notify_to) {
                $notify_to = array();
            }

            $members_dropdown = array();
            $team_dropdown = array();

            //prepare team dropdown list
            if (in_array("team_members", $notify_to)) {
                $team_members = $this->Users_model->get_all_where(array("deleted" => 0, "user_type" => "staff"))->getResult();

                foreach ($team_members as $team_member) {
                    $members_dropdown[] = array("id" => $team_member->id, "text" => $team_member->first_name . " " . $team_member->last_name);
                }
            }


            //prepare team member dropdown list
            if (in_array("team", $notify_to)) {
                $teams = $this->Team_model->get_all_where(array("deleted" => 0))->getResult();
                foreach ($teams as $team) {
                    $team_dropdown[] = array("id" => $team->id, "text" => $team->title);
                }
            }

            //prepare notify to terms
            if ($model_info->notify_to_terms) {
                $model_info->notify_to_terms = explode(",", $model_info->notify_to_terms);
            } else {
                $model_info->notify_to_terms = array();
            }

            $view_data['members_dropdown'] = json_encode($members_dropdown);
            $view_data['team_dropdown'] = json_encode($team_dropdown);

            $view_data["notify_to"] = $notify_to;
            $view_data["model_info"] = $model_info;

            return $this->template->view("settings/notifications/modal_form", $view_data);
        }
    }

    function notification_settings_list_data() {

        $options = array("category" => $this->request->getPost("category"));
        $list_data = $this->Notification_settings_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_notification_settings_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    private function _notification_list_data($id) {
        $options = array("id" => $id);
        $data = $this->Notification_settings_model->get_details($options)->getRow();
        return $this->_make_notification_settings_row($data);
    }

    private function _make_notification_settings_row($data) {

        $yes = "<i data-feather='check-circle' class='icon-16'></i>";
        $no = "<i data-feather='check-circle' class='icon-16' style='opacity:0.2'></i>";

        $notify_to = "";

        if ($data->notify_to_terms) {
            $terms = explode(",", $data->notify_to_terms);
            foreach ($terms as $term) {
                if ($term) {
                    $notify_to .= "<li>" . app_lang($term) . "</li>";
                }
            }
        }

        if ($data->notify_to_team_members) {
            $notify_to .= "<li>" . app_lang("team_members") . ": " . $data->team_members_list . "</li>";
        }

        if ($data->notify_to_team) {
            $notify_to .= "<li>" . app_lang("team") . ": " . $data->team_list . "</li>";
        }

        if ($notify_to) {
            $notify_to = "<ul class='pl15'>" . $notify_to . "</ul>";
        }

        return array(
            $data->sort,
            app_lang($data->event),
            $notify_to,
            app_lang($data->category),
            $data->enable_email ? $yes : $no,
            $data->enable_web ? $yes : $no,
            $data->enable_slack ? $yes : $no,
            modal_anchor(get_uri("settings/notification_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('notification'), "data-post-id" => $data->id))
        );
    }

    function save_notification_settings() {
        $id = $this->request->getPost("id");

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $data = array(
            "enable_web" => $this->request->getPost("enable_web"),
            "enable_email" => $this->request->getPost("enable_email"),
            "enable_slack" => $this->request->getPost("enable_slack"),
            "notify_to_team" => "",
            "notify_to_team_members" => "",
            "notify_to_terms" => "",
        );

        //get post data and prepare notificaton terms
        $notify_to_terms_list = $this->Notification_settings_model->notify_to_terms();
        $notify_to_terms = "";

        foreach ($notify_to_terms_list as $key => $term) {

            if ($term == "team") {
                $data["notify_to_team"] = $this->request->getPost("team"); //set team
            } else if ($term == "team_members") {
                $data["notify_to_team_members"] = $this->request->getPost("team_members"); //set team members
            } else {
                //prepare comma separated terms
                $other_term = $this->request->getPost($term);

                if ($other_term) {
                    if ($notify_to_terms) {
                        $notify_to_terms .= ",";
                    }

                    $notify_to_terms .= $term;
                }
            }
        }


        $data["notify_to_terms"] = $notify_to_terms;

        $save_id = $this->Notification_settings_model->ci_save($data, $id);

        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_notification_list_data($save_id), 'id' => $save_id, 'message' => app_lang('settings_updated')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function modules() {
        return $this->template->rander("settings/modules");
    }

    function save_module_settings() {
        $settings = array("module_timeline", "module_event", "module_todo", "module_note", "module_message", "module_chat", "module_invoice", "module_expense", "module_attendance", "module_leave", "module_estimate", "module_estimate_request", "module_lead", "module_ticket", "module_announcement", "module_project_timesheet", "module_help", "module_knowledge_base", "module_gantt", "module_order", "module_proposal", "module_contract", "module_file_manager", "module_reminder", "module_subscription");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    /* show the cron job tab */

    function cron_job() {
        return $this->template->rander("settings/cron_job");
    }

    /* show the integration tab */

    function integration($tab = "") {
        $view_data["tab"] = clean_data($tab);
        return $this->template->rander("settings/integration/index", $view_data);
    }

    /* load content in reCAPTCHA tab */

    function re_captcha() {
        return $this->template->view("settings/integration/re_captcha");
    }

    /* save reCAPTCHA settings */

    function save_re_captcha_settings() {

        $settings = array("re_captcha_protocol", "re_captcha_site_key", "re_captcha_secret_key");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if (!is_null($value)) {
                $value = remove_quotations($value);
            } else {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    /* load content in bitbucket tab */

    function bitbucket() {
        return $this->template->view("settings/integration/bitbucket");
    }

    /* save bitbucket settings */

    function save_bitbucket_settings() {

        $settings = array("enable_bitbucket_commit_logs_in_tasks");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    /* show the ticket settings tab */

    function tickets() {
        return $this->template->view("settings/tickets/index");
    }

    /* save ticket settings */

    function save_ticket_settings() {

        $settings = array("show_recent_ticket_comments_at_the_top", "ticket_prefix", "project_reference_in_tickets", "auto_close_ticket_after", "auto_reply_to_tickets", "auto_reply_to_tickets_message", "enable_embedded_form_to_get_tickets");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    //show task settings
    function tasks() {
        $values = array(
            "id",
            "project_name",
            "client_name",
            "parent_task"
        );

        $show_in_kanban_dropdown = array();
        foreach ($values as $value) {
            $show_in_kanban_dropdown[] = array("id" => $value, "text" => app_lang($value));
        }

        $view_data['show_in_kanban_dropdown'] = json_encode($show_in_kanban_dropdown);

        return $this->template->rander("settings/tasks", $view_data);
    }

    /* show imap settings tab */

    function imap_settings() {
        return $this->template->view("settings/tickets/imap_settings");
    }

    /* push notification integration settings tab */

    function push_notification() {
        return $this->template->view("settings/integration/push_notification/index");
    }

    //save task settings
    function save_task_settings() {

        $settings = array("project_task_reminder_on_the_day_of_deadline", "project_task_deadline_pre_reminder", "project_task_deadline_overdue_reminder", "enable_recurring_option_for_tasks", "task_point_range", "create_recurring_tasks_before", "show_in_kanban", "show_time_with_task_start_date_and_deadline", "show_the_status_checkbox_in_tasks_list", "support_only_project_related_tasks_globally");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    /* save imap settings */

    function save_imap_settings() {
        $settings = array("enable_email_piping", "create_tickets_only_by_registered_emails", "imap_encryption", "imap_host", "imap_port", "imap_email", "imap_password", "imap_type", "outlook_imap_client_id", "outlook_imap_client_secret", "gmail_imap_client_id", "gmail_imap_client_secret");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            if ($setting == "imap_password" || $setting == "outlook_imap_client_secret" || $setting == "gmail_imap_client_secret") {
                if ($value === "******") {
                    $value = get_setting($setting);
                } else {
                    $value = encode_id($value, $setting);
                }
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        //if user change credentials, flag imap as unauthorized
        $this->Settings_model->save_setting("imap_authorized", "0");

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    /* save push notification settings */

    function save_push_notification_settings() {
        $settings = array("enable_push_notification", "pusher_app_id", "pusher_key", "pusher_secret", "pusher_cluster", "enable_chat_via_pusher", "pusher_beams_instance_id", "pusher_beams_primary_key");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if (!is_null($value)) {
                $value = remove_quotations($value);
            } else {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    /* show the google drive settings tab */

    function google_drive() {
        return $this->template->view("settings/integration/google_drive");
    }

    /* save google drive settings */

    function save_google_drive_settings() {
        $settings = array("enable_google_drive_api_to_upload_file", "google_drive_client_id", "google_drive_client_secret");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if (!is_null($value)) {
                $value = remove_quotations($value);
            } else {
                $value = "";
            }

            if ($setting == "google_drive_client_secret") {
                if ($value === "******") {
                    $value = get_setting($setting);
                } else {
                    $value = encode_id($value, $setting);
                }
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        //if user change credentials, flag google drive as unauthorized
        $this->Settings_model->save_setting("google_drive_authorized", "0");

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    //authorize imap
    function authorize_imap() {
        if (get_setting("enable_email_piping")) {
            $imap = new Imap();

            if (!$imap->authorize_imap_and_get_inbox()) {
                $this->session->setFlashdata("error_message", app_lang("imap_error_credentials_message"));
            }
            app_redirect("ticket_types/index/imap");
        }
    }

    function estimates() {
        $estimate_info = $this->Estimates_model->get_estimate_last_id();
        $view_data["last_id"] = $estimate_info;

        return $this->template->rander("settings/estimates", $view_data);
    }

    function save_estimate_settings() {
        $settings = array("estimate_prefix", "estimate_color", "estimate_footer", "send_estimate_bcc_to", "initial_number_of_the_estimate", "create_new_projects_automatically_when_estimates_gets_accepted", "enable_comments_on_estimates", "show_most_recent_estimate_comments_at_the_top", "add_signature_option_on_accepting_estimate", "enable_estimate_lock_state");
        $reload_page = false;

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if ($setting === "estimate_footer") {
                $value = decode_ajax_post_data($value);
            }

            if (is_null($value)) {
                $value = "";
            }


            $this->Settings_model->save_setting($setting, $value);

            if ($setting === "initial_number_of_the_estimate") {
                $this->Estimates_model->save_initial_number_of_estimate($value);
            }
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated'), "reload_page" => $reload_page));
    }

    //show a demo push notification
    function test_push_notification() {
        helper('notifications');
        if (send_push_notifications("test_push_notification", $this->login_user->id, $this->login_user->id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('push_notification_error_message')));
        }
    }

    /* show timesheet settings tab */

    function timesheets() {
        return $this->template->rander("settings/timesheets");
    }

    /* save timesheet settings */

    function save_timesheets_settings() {
        $settings = array(
            "users_can_start_multiple_timers_at_a_time",
            "users_can_input_only_total_hours_instead_of_period"
        );

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function gdpr() {
        return $this->template->rander("settings/gdpr");
    }

    function save_gdpr_settings() {
        $settings = array("enable_gdpr", "allow_clients_to_export_their_data", "clients_can_request_account_removal", "show_terms_and_conditions_in_client_signup_page", "gdpr_terms_and_conditions_link");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function footer() {
        //check available menus
        $footer_menus_data = "";

        $footer_menus = unserialize(get_setting("footer_menus"));
        if ($footer_menus && is_array($footer_menus)) {
            foreach ($footer_menus as $footer) {
                $footer_menus_data .= $this->_make_footer_menu_item_data($footer->menu_name, $footer->url);
            }
        }

        $view_data["footer_menus"] = $footer_menus_data;

        return $this->template->view("settings/footer/index", $view_data);
    }

    private function _make_footer_menu_item_data($menu_name, $url, $type = "") {

        $edit = modal_anchor(get_uri("settings/footer_item_edit_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "float-end mr10 dark footer-menu-edit-btn", "title" => app_lang('edit_footer_menu'), "data-post-menu_name" => $menu_name, "data-post-url" => $url));
        $delete = "<span class='footer-menu-delete-btn  float-end clickable'><i data-feather='x' class='icon-16'></i></span>";
        $title = "<a href='$url' target='_blank'>$menu_name</a>";
        $move_icon = "<div class='float-start move-icon'><i data-feather='menu' class='icon-16'></i></div>";

        if ($type == "data") {
            return $move_icon . $title . $delete . $edit;
        } else {
            return "<div class='list-group-item footer-menu-item b-a rounded mb10' data-footer_menu_temp_id='" . rand(2000, 400000000) . "'>" . $move_icon . $title . $delete . $edit . "</div>";
        }
    }

    function save_footer_settings() {
        $settings = array("enable_footer", "footer_menus", "footer_copyright_text");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            if ($setting == "footer_menus") {
                $value = json_decode($value);
                $value = $value ? serialize($value) : serialize(array());
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function footer_item_edit_modal_form() {
        $model_info = new \stdClass();
        $model_info->menu_name = $this->request->getPost("menu_name");
        $model_info->url = $this->request->getPost("url");

        $view_data["model_info"] = $model_info;

        return $this->template->view("settings/footer/modal_form", $view_data);
    }

    function save_footer_menu() {
        $menu_name = $this->request->getPost("menu_name");
        $url = $this->request->getPost("url");
        $type = $this->request->getPost("type");

        if ($menu_name && $url) {
            echo json_encode(array("success" => true, 'data' => $this->_make_footer_menu_item_data($menu_name, $url, $type)));
        }
    }

    function top_menu() {
        //check available menus
        $top_menus_data = "";

        $top_menus = unserialize(get_setting("top_menus"));

        if ($top_menus && is_array($top_menus)) {
            foreach ($top_menus as $menu) {
                $top_menus_data .= $this->_make_top_menu_item_data($menu->menu_name, $menu->url);
            }
        }

        $view_data["top_menus"] = $top_menus_data;

        return $this->template->view("settings/top_menu/index", $view_data);
    }

    private function _make_top_menu_item_data($menu_name, $url, $type = "") {
        $edit = modal_anchor(get_uri("settings/top_menu_item_edit_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "float-end mr10 dark top-menu-edit-btn", "title" => app_lang('edit_top_menu'), "data-post-menu_name" => $menu_name, "data-post-url" => $url));
        $delete = "<span class='top-menu-delete-btn float-end clickable'><i data-feather='x' class='icon-16'></i></span>";
        $title = "<a href='$url' target='_blank'>$menu_name</a>";
        $move_icon = "<div class='float-start move-icon'><i data-feather='menu' class='icon-16'></i></div>";

        if ($type == "data") {
            return $move_icon . $title . $delete . $edit;
        } else {
            return "<div class='list-group-item top-menu-item b-a rounded mb10' data-top_menu_temp_id='" . rand(2000, 400000000) . "'>" . $move_icon . $title . $delete . $edit . "</div>";
        }
    }

    function save_top_menu_settings() {
        $settings = array("enable_top_menu", "top_menus");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            if ($setting == "top_menus") {
                $value = json_decode($value);
                $value = $value ? serialize($value) : serialize(array());
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function top_menu_item_edit_modal_form() {
        $model_info = new \stdClass();
        $model_info->menu_name = $this->request->getPost("menu_name");
        $model_info->url = $this->request->getPost("url");

        $view_data["model_info"] = $model_info;

        return $this->template->view("settings/top_menu/modal_form", $view_data);
    }

    function save_top_menu() {
        $menu_name = $this->request->getPost("menu_name");
        $url = $this->request->getPost("url");
        $type = $this->request->getPost("type");

        if ($menu_name && $url) {
            echo json_encode(array("success" => true, 'data' => $this->_make_top_menu_item_data($menu_name, $url, $type)));
        }
    }

    private function get_client_hidden_fields_dropdown() {
        $hidden_fields = array(
            "company_name",
            "first_name",
            "last_name",
            "email",
            "address",
            "city",
            "state",
            "zip",
            "country",
            "phone"
        );

        $hidden_fields_dropdown = array();
        foreach ($hidden_fields as $hidden_field) {
            $hidden_fields_dropdown[] = array("id" => $hidden_field, "text" => app_lang($hidden_field));
        }

        return json_encode($hidden_fields_dropdown);
    }

    function estimate_request_settings() {
        $view_data['hidden_fields_dropdown'] = $this->get_client_hidden_fields_dropdown();
        return $this->template->view("settings/estimate_requests", $view_data);
    }

    function save_estimate_request_settings() {
        $settings = array(
            "hidden_client_fields_on_public_estimate_requests"
        );

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            //email can't be shown without first name or last name
            $value_array = explode(',', $value);
            if (in_array("first_name", $value_array) && in_array("last_name", $value_array) && !in_array("email", $value_array)) {
                echo json_encode(array("success" => false, 'message' => app_lang("estimate_request_name_email_error_message")));
                return false;
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function orders() {
        $order_info = $this->Orders_model->get_order_last_id();
        $view_data["last_id"] = $order_info;
        $view_data['taxes_dropdown'] = $this->Taxes_model->get_dropdown_list_with_blank_option(array("title"));

        return $this->template->rander("settings/orders", $view_data);
    }

    function save_order_settings() {
        $settings = array("order_prefix", "order_color", "order_footer", "initial_number_of_the_order", "order_tax_id", "order_tax_id2");
        $reload_page = false;

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if ($setting === "order_footer") {
                $value = decode_ajax_post_data($value);
            }

            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);

            if ($setting === "initial_number_of_the_order") {
                $this->Orders_model->save_initial_number_of_order($value);
            }
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated'), "reload_page" => $reload_page));
    }

    function projects() {
        $project_tabs = array(
            "overview",
            "tasks_list",
            "tasks_kanban",
            "milestones",
            "gantt",
            "notes",
            "files",
            "comments",
            "customer_feedback",
            "timesheets",
            "invoices",
            "payments",
            "expenses",
            "contracts",
            "tickets"
        );

        $project_tabs_of_hook_of_staff = array();
        $project_tabs_of_hook_of_staff = app_hooks()->apply_filters('app_filter_team_members_project_details_tab', $project_tabs_of_hook_of_staff, 0); //0 means no specific project here
        if ($project_tabs_of_hook_of_staff && is_array($project_tabs_of_hook_of_staff)) {
            foreach ($project_tabs_of_hook_of_staff as $key => $value) {
                if (!in_array($key, $project_tabs)) {
                    array_push($project_tabs, $key);
                }
            }
        }

        $project_tabs_dropdown = array();
        foreach ($project_tabs as $project_tab) {
            $project_tabs_dropdown[] = array("id" => $project_tab, "text" => app_lang($project_tab));
        }

        $view_data['project_tabs_dropdown'] = json_encode($project_tabs_dropdown);
        return $this->template->rander("settings/projects", $view_data);
    }

    function save_projects_settings() {
        $settings = array(
            "project_tab_order",
        );

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function slack() {
        return $this->template->view("settings/integration/slack");
    }

    function save_slack_settings() {
        $settings = array("enable_slack", "slack_webhook_url", "slack_dont_send_any_projects");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if (!is_null($value)) {
                $value = remove_quotations($value);
            } else {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    private function _get_client_project_tabs_dropdown() {
        $project_tabs = array(
            "overview",
            "tasks_list",
            "tasks_kanban",
            "files",
            "comments",
            "milestones",
            "gantt",
            "timesheets",
            "invoices"
        );

        $project_tabs_of_hook_of_client = array();
        $project_tabs_of_hook_of_client = app_hooks()->apply_filters('app_filter_clients_project_details_tab', $project_tabs_of_hook_of_client, 0); //0 means no specific project here

        if ($project_tabs_of_hook_of_client && is_array($project_tabs_of_hook_of_client)) {
            foreach ($project_tabs_of_hook_of_client as $key => $value) {
                if (!in_array($key, $project_tabs)) {
                    array_push($project_tabs, $key);
                }
            }
        }

        $project_tabs_dropdown = array();
        foreach ($project_tabs as $project_tab) {
            $project_tabs_dropdown[] = array("id" => $project_tab, "text" => app_lang($project_tab));
        }

        return json_encode($project_tabs_dropdown);
    }


    /* load content in github tab */

    function github() {
        return $this->template->view("settings/integration/github");
    }

    /* save github settings */

    function save_github_settings() {

        $settings = array("enable_github_commit_logs_in_tasks");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function test_slack_notification() {
        helper('notifications');
        if (send_slack_notification("test_slack_notification", $this->login_user->id, 0, get_setting("slack_webhook_url"))) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('slack_notification_error_message')));
        }
    }

    function contracts() {
        $contract_info = $this->Contracts_model->get_contract_last_id();
        $view_data["last_id"] = $contract_info;
        $Contract_templates_model = model("App\Models\Contract_templates_model");
        $view_data['contract_templates_dropdown'] = array("" => "-") + $Contract_templates_model->get_dropdown_list(array("title"), "id");

        return $this->template->rander("settings/contracts", $view_data);
    }

    function save_contract_settings() {
        $settings = array("contract_prefix", "contract_color", "send_contract_bcc_to", "initial_number_of_the_contract", "add_signature_option_on_accepting_contract", "default_contract_template", "add_signature_option_for_team_members", "enable_contract_lock_state", "disable_contract_pdf_for_clients");

        $reload_page = false;

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);

            if ($setting === "initial_number_of_the_contract") {
                $this->Contracts_model->save_initial_number_of_contract($value);
            }
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated'), "reload_page" => $reload_page));
    }

    /* load content in leads tab */

    function leads() {
        $view_data['hidden_fields_dropdown'] = $this->get_client_hidden_fields_dropdown();
        return $this->template->view("settings/leads", $view_data);
    }

    /* save lead settings */

    function save_lead_settings() {

        $settings = array("can_create_lead_from_public_form", "enable_embedded_form_to_get_leads", "after_submit_action_of_public_lead_form", "after_submit_action_of_public_lead_form_redirect_url", "hidden_fields_on_lead_embedded_form");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function proposals() {
        $proposal_info = $this->Proposals_model->get_proposal_last_id();
        $view_data["last_id"] = $proposal_info;
        $Proposal_templates_model = model("App\Models\Proposal_templates_model");
        $view_data['proposal_templates_dropdown'] = array("" => "-") + $Proposal_templates_model->get_dropdown_list(array("title"), "id");

        return $this->template->rander("settings/proposals", $view_data);
    }

    function save_proposal_settings() {
        $settings = array("proposal_prefix", "proposal_color", "send_proposal_bcc_to", "initial_number_of_the_proposal", "add_signature_option_on_accepting_proposal", "default_proposal_template", "enable_proposal_lock_state", "enable_comments_on_proposals", "show_most_recent_proposal_comments_at_the_top", "disable_proposal_pdf_for_clients");
        $reload_page = false;

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);

            if ($setting === "initial_number_of_the_proposal") {
                $this->Proposals_model->save_initial_number_of_proposal($value);
            }
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated'), "reload_page" => $reload_page));
    }

    function localization() {
        $tzlist = \DateTimeZone::listIdentifiers();
        $view_data['timezone_dropdown'] = array();
        foreach ($tzlist as $zone) {
            $view_data['timezone_dropdown'][$zone] = $zone;
        }

        $view_data['language_dropdown'] = get_language_list();

        $view_data["currency_dropdown"] = get_international_currency_code_dropdown();
        return $this->template->rander("settings/localization", $view_data);
    }

    /* save localization settings */

    function save_localization_settings() {

        $settings = array("language", "timezone", "date_format", "time_format", "first_day_of_week", "weekends", "default_currency", "currency_symbol", "currency_position", "decimal_separator", "no_of_decimals", "conversion_rate_currency");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            if ($setting === "conversion_rate_currency") {
                $conversion_rates = $this->request->getPost("conversion_rate");
                $value = $this->prepare_conversion_rates($value, $conversion_rates);
                $setting = "conversion_rate";
            }

            if ($setting === "currency_symbol") {
                $value = remove_quotations($value);
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated'))); // show localization settings success response
    }

    private function prepare_conversion_rates($conversion_rate_currencies, $conversion_rates) {
        $conversion_rate = array();

        if ($conversion_rate_currencies) {
            foreach ($conversion_rate_currencies as $key => $conversion_rate_currency) {
                if (get_array_value($conversion_rates, $key)) {
                    $conversion_rate[$conversion_rate_currency] = unformat_currency(get_array_value($conversion_rates, $key));
                }
            }
        }

        return serialize($conversion_rate);
    }

    function store() {
        $order_statuses_dropdown = array("" => "-");
        $store_statuses = $this->Order_status_model->get_details()->getResult();
        foreach ($store_statuses as $store_status) {
            $order_statuses_dropdown[$store_status->id] = $store_status->title;
        }

        $view_data['order_statuses_dropdown'] = $order_statuses_dropdown;

        return $this->template->rander("settings/store", $view_data);
    }

    function save_store_settings() {
        $settings = array("visitors_can_see_store_before_login", "show_payment_option_after_submitting_the_order", "accept_order_before_login", "order_status_after_payment");

        $visitors_can_see_store_before_login = $this->request->getPost("visitors_can_see_store_before_login");
        $show_payment_option_after_submitting_the_order = $this->request->getPost("show_payment_option_after_submitting_the_order");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            if ($setting === "accept_order_before_login" && !($visitors_can_see_store_before_login && !$show_payment_option_after_submitting_the_order)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);
        }

        //save banner image
        $files_data = move_files_from_temp_dir_to_permanent_dir(get_setting("timeline_file_path"), "store");
        $unserialize_files_data = unserialize($files_data);
        $banner_image_on_public_store = get_array_value($unserialize_files_data, 0);
        if ($banner_image_on_public_store) {
            delete_app_files(get_setting("timeline_file_path"), get_system_files_setting_value("banner_image_on_public_store"));
            $this->Settings_model->save_setting("banner_image_on_public_store", serialize($banner_image_on_public_store));
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function subscriptions() {
        $last_subscription_id = $this->Subscriptions_model->get_last_subscription_id();

        $view_data["last_id"] = $last_subscription_id;

        $stripe_payment_method = $this->Payment_methods_model->get_oneline_payment_method("stripe");
        $view_data["stripe_payment_method_enabled"] = $stripe_payment_method->available_on_invoice ? true : false;

        return $this->template->rander("settings/subscriptions/index", $view_data);
    }

    function save_subscription_settings() {
        $settings = array("subscription_prefix", "initial_number_of_the_subscription", "enable_stripe_subscription", "webhook_listener_link_of_stripe_subscription");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $this->Settings_model->save_setting($setting, $value);

            if ($setting === "initial_number_of_the_subscription") {
                $this->Subscriptions_model->save_initial_number_of_subscription($value);
            }
        }

        if ($this->request->getPost("enable_stripe_subscription")) {
            $webhook_listener_link_of_stripe_subscription = $this->request->getPost("webhook_listener_link_of_stripe_subscription");
            $this->save_stripe_webhook($webhook_listener_link_of_stripe_subscription);
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    private function save_stripe_webhook($webhook_listener_link_of_stripe_subscription) {
        //create webhook in stripe
        if (!$webhook_listener_link_of_stripe_subscription) {
            return false;
        }

        $Stripe = new Stripe();
        $stripe_webhook_id = get_setting("stripe_webhook_id");
        $create_new_webhook = true;

        if ($stripe_webhook_id) {
            try {
                $Stripe->update_webhook($stripe_webhook_id, $webhook_listener_link_of_stripe_subscription);
                $create_new_webhook = false; //if webhook id not found or any error raised, try creating new webhook
            } catch (\Exception $ex) {
            }
        }

        if ($create_new_webhook) {
            try {
                $stripe_webhook_id = $Stripe->create_webhook($webhook_listener_link_of_stripe_subscription)->id;
                $this->Settings_model->save_setting("stripe_webhook_id", $stripe_webhook_id);
            } catch (\Exception $ex) {
                echo json_encode(array("success" => false, 'message' => $ex->getMessage()));
                return false;
            }
        }
    }

    function pwa() {
        return $this->template->view("settings/pwa/index");
    }

    function save_pwa_settings() {
        $pwa_theme_color = $this->request->getPost("pwa_theme_color") ? $this->request->getPost("pwa_theme_color") : "";
        $this->Settings_model->save_setting("pwa_theme_color", $pwa_theme_color);

        $pwa_icon = $this->request->getPost("pwa_icon") ? $this->request->getPost("pwa_icon") : "";
        if ($pwa_icon) {
            $pwa_icon = str_replace("~", ":", $pwa_icon);
            $pwa_icon = serialize(move_temp_file("pwa_icon.png", get_setting("system_file_path") . "pwa/", "", $pwa_icon, "", "", false, 0, true));

            //delete old file
            delete_app_files(get_setting("system_file_path") . "pwa/", get_system_files_setting_value("pwa_icon"));

            $this->Settings_model->save_setting("pwa_icon", $pwa_icon);
        }

        $reload_page = false;

        if ($_FILES) {
            $files = array("pwa_icon_file");

            foreach ($files as $file) {
                $file_data = get_array_value($_FILES, $file);

                if (!($file_data && is_array($file_data) && count($file_data))) {
                    continue;
                }

                $file_name = get_array_value($file_data, "tmp_name");
                $file_size = get_array_value($file_data, "size");
                if (!$file_name) {
                    continue;
                }

                $new_file_name = "pwa_icon.png";
                $setting_name = "pwa_icon";

                $pwa_icon = serialize(move_temp_file($new_file_name, get_setting("system_file_path") . "pwa/", "", $file_name, "", "", false, $file_size, true));
                //delete old file
                delete_app_files(get_setting("system_file_path") . "pwa/", get_system_files_setting_value($setting_name));
                $this->Settings_model->save_setting($setting_name, $pwa_icon);
            }

            $reload_page = true;
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated'), 'reload_page' => $reload_page));
    }

    /* load content in tinymce tab */

    function tinymce() {
        return $this->template->view("settings/integration/tinymce");
    }

    /* save tinymce settings */

    function save_tinymce_settings() {

        $settings = array("enable_tinymce", "tinymce_api_key");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);

            if (!is_null($value)) {
                $value = remove_quotations($value);
            } else {
                $value = "";
            }

            if ($setting == "enable_tinymce") {
                if ($value) {
                    $this->Settings_model->save_setting("rich_text_editor_name", "tinymce");
                } else {
                    $this->Settings_model->save_setting("rich_text_editor_name", "");
                }
            }

            $this->Settings_model->save_setting($setting, $value);
        }
        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    function save_details_page_layout_settings() {
        $setting_name = "details_page_layout";
        $context = $this->request->getPost("context");
        $value = $this->request->getPost("value");

        $context = clean_data($context);
        $value = clean_data($value);

        $existing_value = $this->Settings_model->get_setting($setting_name);

        $settings_array = [];
        if ($existing_value) {
            $settings_array = unserialize($existing_value);
            if (!is_array($settings_array)) {
                $settings_array = [];
            }
        }

        $settings_array[$context] = $value;

        $this->Settings_model->save_setting($setting_name, serialize($settings_array));

        echo json_encode(["success" => true, "message" => app_lang("settings_updated")]);
    }
}

/* End of file Settings.php */
/* Location: ./app/Controllers/Settings.php */