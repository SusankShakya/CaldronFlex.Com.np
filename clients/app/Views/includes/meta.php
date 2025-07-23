<link rel="manifest" href="<?php echo get_uri('pwa/manifest'); ?>">
<meta id="theme-color-meta-tag" name="theme-color" content="<?php echo get_setting("pwa_theme_color") ? get_setting("pwa_theme_color") : "#1c2026"; ?>">

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="fairsketch">
<link rel="icon" href="<?php echo get_favicon_url(); ?>" />

<title>
    <?php
    $router = service('router');
    $controller_name = strtolower(get_actual_controller_name($router));
    $title = get_setting('app_title');
    if (strpos(app_lang($controller_name), '.') === false) {
        $title = app_lang($controller_name) . " | " . $title;
    }
    echo $title;
    ?>
</title>