<?php
$dir = 'ltr';
if (app_lang("text_direction") == "rtl") {
    $dir = 'rtl';
}

helper('cookie');
$left_menu_minimized = get_cookie("left_menu_minimized");

$dynamic_class = "public-view";
if (isset($login_user)) {
    if (isset($login_user->is_admin) && $login_user->is_admin) {
        $dynamic_class = "admin-view";
    } else if (isset($login_user->user_type) && $login_user->user_type == "staff") {
        $dynamic_class = "team-member-view";
    } else if (isset($login_user->user_type) && $login_user->user_type == "client") {
        $dynamic_class = "client-view";
    }
}

$router = service('router');
$dynamic_class .= " " . strtolower(get_actual_controller_name($router)) . "-page";

?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo $dir; ?>">
<?php echo view('includes/head'); ?>

<body class="<?php echo $left_menu_minimized ? "sidebar-toggled" : ""; ?> <?php echo $dynamic_class; ?>">

    <?php
    if ($topbar) {
        echo view($topbar);
    }

    $left_menu_toggle_id = "left-menu-toggle-mask";
    $page_container_class = "page-container";
    $scrollable_page_class = "scrollable-page main-scrollable-page";

    if ($left_menu) {
        echo view('messages/chat/index.php');
    } else {
        //don't have left menu. So it's a public page. 
        $page_container_class .= " public-page-container";
        $left_menu_toggle_id = "";
    }


    //don't use page container class if there is no topbar 
    if (!$topbar) {
        $page_container_class = "";
    }


    //show cartbox only in the store page
    $uri_string = uri_string();
    if ($uri_string == "store" || $uri_string == "/store") {
        echo view('items/cart/index');
    }
    ?>

    <div id="<?php echo $left_menu_toggle_id; ?>">
        <?php
        if ($left_menu) {
            echo $left_menu;
        }
        ?>
        <div class="overflow-auto <?php echo $page_container_class ?>">
            <div id="pre-loader">
                <div id="pre-loade" class="app-loader">
                    <div class="loading"></div>
                </div>
            </div>

            <div class="<?php echo $scrollable_page_class; ?>">
                <?php
                if (isset($content_view) && $content_view != "") {
                    echo view($content_view);
                }

                app_hooks()->do_action('app_hook_layout_main_view_extension');
                ?>
                <?php
                if ($topbar == "includes/public/topbar") {
                    echo view("includes/footer");
                }
                ?>
            </div>


        </div>
    </div>

    <?php echo view('modal/index'); ?>
    <?php echo view('modal/confirmation'); ?>


    <?php if (get_setting("rich_text_editor_name") === "tinymce") {
        echo view("includes/tinymce");
    } else {
        echo view("includes/summernote");
    } ?>

    <nav class="mobile-bottom-menu navbar-expand-lg b-t bg-white fixed-bottom d-block d-sm-none" id="mobile-bottom-menu">
        <div class="d-flex justify-content-between pl15 pr15">
            <a class="menu-item sidebar-toggle-btn" aria-current="page" href="#">
                <i data-feather="menu" class="icon"></i>
            </a>
            <?php if (get_setting("module_todo")) { ?>
                <a class="menu-item todo-btn" href="<?php echo_uri('todo'); ?>">
                    <i data-feather="check-circle" class="icon"></i>
                </a>
            <?php } ?>
            <div id="mobile-function-button" class=""></div>
            <?php if (get_setting("module_chat")) { ?>
                <div id="mobile-chat-menu-button" class="menu-item"></div>
            <?php } ?>
            <div id="mobile-quick-add-button" class="menu-item dropdown"></div>
        </div>
    </nav>

    <div style='display: none;'>
        <script type='text/javascript'>
            feather.replace();

            <?php
            $session = \Config\Services::session();
            $error_message = $session->getFlashdata("error_message");
            $success_message = $session->getFlashdata("success_message");
            if (isset($error)) {
                echo 'appAlert.error("' . $error . '");';
            }
            if (isset($error_message)) {
                echo 'appAlert.error("' . $error_message . '");';
            }
            if (isset($success_message)) {
                echo 'appAlert.success("' . $success_message . '", {duration: 10000});';
            }
            ?>
        </script>

        <?php
        echo view("includes/navigator_service_worker");
        ?>

    </div>

</body>

</html>