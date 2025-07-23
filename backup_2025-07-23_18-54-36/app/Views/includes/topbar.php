<?php $user = $login_user->id; ?>

<nav class="navbar navbar-expand fixed-top navbar-light navbar-custom" role="navigation" id="default-navbar">
    <div class="container-fluid">
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-lg-0">
                <li class="nav-item hidden-xs sidebar-toggle-btn-li">
                    <a class="nav-link sidebar-toggle-btn" aria-current="page" href="#">
                        <i data-feather="menu" class="icon"></i>
                    </a>
                </li>

                <li class="nav-item d-block d-sm-none">
                    <?php
                    $user = $login_user->id;
                    $dashboard_link = get_uri("dashboard");
                    $user_dashboard = get_setting("user_" . $user . "_dashboard");
                    if ($user_dashboard) {
                        $dashboard_link = get_uri("dashboard/view/" . $user_dashboard);
                    }
                    ?>
                    <a id="dashboard-link" class="brand-logo" href="<?php echo $dashboard_link; ?>"><img class="dashboard-image" src="<?php echo get_logo_url(); ?>" /></a>

                </li>

                <?php
                //get the array of hidden topbar menus
                $hidden_topbar_menus = explode(",", get_setting("user_" . $user . "_hidden_topbar_menus"));

                if (!in_array("to_do", $hidden_topbar_menus)) {
                    echo view("todo/topbar_icon");
                }
                if (!in_array("favorite_projects", $hidden_topbar_menus) && !(get_setting("disable_access_favorite_project_option_for_clients") && $login_user->user_type == "client") && !($login_user->user_type == "staff" && get_array_value($login_user->permissions, "do_not_show_projects"))) {
                    echo view("projects/star/topbar_icon");
                }
                if (!in_array("favorite_clients", $hidden_topbar_menus)) {
                    echo view("clients/star/topbar_icon");
                }
                if (!in_array("dashboard_customization", $hidden_topbar_menus) && (get_setting("disable_new_dashboard_icon") != 1)) {
                    echo view("dashboards/list/topbar_icon");
                }
                ?>

                <?php
                if (has_my_open_timers()) {
                    echo view("projects/open_timers_topbar_icon");
                }

                if ($login_user->user_type === "client") {
                    show_clients_of_this_client_contact($login_user);
                }
                ?>
            </ul>

            <div class="d-flex w-auto">
                <ul class="navbar-nav">

                    <?php
                    if ($login_user->user_type == "staff") { ?>
                        <li id="topbar-search-btn" class="nav-item hidden-sm" title="<?php echo app_lang('search') . ' (/)'; ?>">
                            <?php echo modal_anchor(get_uri("search/search_modal_form"), "<i data-feather='search' class='icon'></i>", array("class" => "nav-link", "data-modal-title" => app_lang('search') . ' (/)', "data-post-hide-header" => true, "data-modal-close" => "1", "id" => "global-search-btn")); ?>
                        </li>
                    <?php } ?>

                    <?php
                    if (!in_array("quick_add", $hidden_topbar_menus)) {
                        echo view("settings/topbar_parts/quick_add");
                    }
                    ?>

                    <?php if (!in_array("language", $hidden_topbar_menus) && (($login_user->user_type == "staff" && !get_setting("disable_language_selector_for_team_members")) || ($login_user->user_type == "client" && !get_setting("disable_language_selector_for_clients")))) { ?>

                        <li id="topbar-language-dropdown" class="nav-item dropdown hidden-xs">
                            <?php echo js_anchor("<i data-feather='globe' class='icon'></i>", array("id" => "personal-language-icon", "class" => "nav-link dropdown-toggle p20", "data-bs-toggle" => "dropdown")); ?>

                            <ul class="dropdown-menu dropdown-menu-end language-dropdown">
                                <li>
                                    <?php
                                    $user_language = $login_user->language;
                                    $system_language = get_setting("language");

                                    foreach (get_language_list() as $language) {
                                        $language_status = "";
                                        $language_text = $language;

                                        if ($user_language == strtolower($language) || (!$user_language && $system_language == strtolower($language))) {
                                            $language_status = "<span class='float-end checkbox-checked m0'></span>";
                                            $language_text = "<strong>" . $language . "</strong>";
                                        }

                                        if ($login_user->user_type == "staff") {
                                            echo ajax_anchor(get_uri("team_members/save_personal_language/$language"), $language_text . $language_status, array("class" => "dropdown-item clearfix", "data-reload-on-success" => "1"));
                                        } else {
                                            echo ajax_anchor(get_uri("clients/save_personal_language/$language"), $language_text . $language_status, array("class" => "dropdown-item clearfix", "data-reload-on-success" => "1"));
                                        }
                                    }
                                    ?>
                                </li>
                            </ul>
                        </li>

                    <?php } ?>

                    <?php if (can_access_reminders_module()) { ?>
                        <li class="nav-item dropdown">
                            <?php echo modal_anchor(get_uri("events/reminders"), "<i data-feather='clock' class='icon'></i>", array("class" => "nav-link", "id" => "reminder-icon", "data-post-reminder_view_type" => "global", "title" => app_lang('reminders') . " (" . app_lang('private') . ")")); ?>
                        </li>
                        <?php reminders_widget(); ?>
                    <?php } ?>

                    <li class="nav-item dropdown">
                        <?php echo js_anchor("<i data-feather='bell' class='icon'></i><span class='notification-badge-container'></span>", array(
                            "id" => "web-notification-icon",
                            "class" => "nav-link dropdown-toggle",
                            "data-bs-toggle" => "dropdown",
                            "data-count_url" => get_uri('notifications/count_notifications'),
                            "data-list_url" => get_uri('notifications/get_notifications'),
                            "data-status_update_url" => get_uri('notifications/update_notification_checking_status'),
                            "data-fetch_interval" => get_setting('check_notification_after_every'),
                        )); ?>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown w400">
                            <div class="card m0">
                                <div class="dropdown-details bg-white m0">
                                    <div class="list-group">
                                        <span class="list-group-item inline-loader p10"></span>
                                    </div>
                                </div>
                                <div class="card-footer text-center">
                                    <?php echo anchor("notifications", app_lang('see_all'), array("class" => "w-100 d-block")); ?>
                                </div>
                            </div>
                        </div>
                    </li>

                    <?php if (get_setting("module_message") && can_access_messages_module()) { ?>
                        <li class="nav-item dropdown hidden-sm <?php echo ($login_user->user_type === "client" && !get_setting("client_message_users")) ? "hide" : ""; ?>">
                            <?php echo js_anchor("<i data-feather='mail' class='icon'></i><span class='notification-badge-container'></span>", array(
                                "id" => "message-notification-icon",
                                "class" => "nav-link dropdown-toggle",
                                "data-bs-toggle" => "dropdown",
                                "data-count_url" => get_uri('messages/count_notifications'),
                                "data-list_url" => get_uri('messages/get_notifications'),
                                "data-status_update_url" => get_uri('messages/update_notification_checking_status'),
                                "data-fetch_interval" => get_setting('check_notification_after_every'),
                            )); ?>
                            <div class="dropdown-menu dropdown-menu-end w300 message-dropdown">
                                <div class="card m0">
                                    <div class="dropdown-details bg-white">
                                        <div class="list-group">
                                            <span class="list-group-item inline-loader p10"></span>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <?php echo anchor("messages", app_lang('see_all'), array("class" => "w-100 d-block")); ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php } ?>

                    <li class="nav-item dropdown">
                        <a id="user-dropdown" href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                            <span class="avatar-xs avatar me-1">
                                <img alt="..." src="<?php echo get_avatar($login_user->image); ?>">
                            </span>
                            <span class="user-name ml10"><?php echo $login_user->first_name . " " . $login_user->last_name; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end w200 user-dropdown-menu">
                            <?php if ($login_user->user_type == "client") { ?>
                                <div class="company-switch-option d-none"><?php show_clients_of_this_client_contact($login_user, true); ?></div>
                                <li><?php echo get_client_contact_profile_link($login_user->id . '/general', "<i data-feather='user' class='icon-16 me-2'></i>" . app_lang('my_profile'), array("class" => "dropdown-item")); ?></li>
                                <li><?php echo get_client_contact_profile_link($login_user->id . '/account', "<i data-feather='key' class='icon-16 me-2'></i>" . app_lang('change_password'), array("class" => "dropdown-item")); ?></li>
                                <li><?php echo get_client_contact_profile_link($login_user->id . '/my_preferences', "<i data-feather='settings' class='icon-16 me-2'></i>" . app_lang('my_preferences'), array("class" => "dropdown-item")); ?></li>
                            <?php } else { ?>
                                <li><?php echo get_team_member_profile_link($login_user->id . '/general', "<i data-feather='user' class='icon-16 me-2'></i>" . app_lang('my_profile'), array("class" => "dropdown-item")); ?></li>
                                <li><?php echo get_team_member_profile_link($login_user->id . '/account', "<i data-feather='key' class='icon-16 me-2'></i>" . app_lang('change_password'), array("class" => "dropdown-item")); ?></li>
                                <li><?php echo get_team_member_profile_link($login_user->id . '/my_preferences', "<i data-feather='settings' class='icon-16 me-2'></i>" . app_lang('my_preferences'), array("class" => "dropdown-item")); ?></li>
                            <?php } ?>

                            <?php if (get_setting("show_theme_color_changer") === "yes") { ?>

                                <li class="dropdown-divider"></li>
                                <li class="pl10 ms-2 mt10 theme-changer">
                                    <?php echo get_custom_theme_color_list(); ?>
                                </li>

                            <?php } ?>

                            <li class="dropdown-divider"></li>
                            <li><a href="<?php echo_uri('signin/sign_out'); ?>" class="dropdown-item"><i data-feather="log-out" class='icon-16 me-2'></i> <?php echo app_lang('sign_out'); ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<script type="text/javascript">
    //close navbar collapse panel on clicking outside of the panel
    $(document).click(function(e) {
        if (!$(e.target).is('#navbar') && isMobile()) {
            $('#navbar').collapse('hide');
        }
    });

    $(document).ready(async function() {
        $('body').on('click', "#reminder-icon", function() {
            $("#ajaxModal").addClass("reminder-modal");
        });

        $("body").on("click", ".notification-dropdown a[data-act='ajax-modal'], #js-quick-add-task, #js-quick-add-multiple-task, #task-details-edit-btn, #task-modal-view-link, #parent-task-link", function() {
            if ($(".task-preview").length) {
                // Store the current location
                var currentLocation = window.location.href;

                //remove task details view when it's already opened to prevent selector duplication
                $("#page-content").remove();
                $('#ajaxModal').on('hidden.bs.modal', function() {
                    window.location.href = currentLocation;
                });
            }
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

        if (isMobile()) {
            moveTopbarButtonsToLeftMenu($("#topbar-language-dropdown"));
            moveTopbarButtonsToLeftMenu($("#topbar-search-btn"));
            moveTopbarButtonsToLeftMenu($("#topbar-timer-dropdown"));
        }
    });

    function moveTopbarButtonsToLeftMenu($element) {
        if ($element.html()) {
            $("#left-menu-topbar-button-container").append("<div class='menu-item d-block d-sm-none dropdown float-end'>" + $element.html() + "</div>");
            $element.remove();
        }
    }
</script>