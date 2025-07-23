<?php
$re_captcha_protocol = get_setting("re_captcha_protocol") ? get_setting("re_captcha_protocol") : "v2";
$site_key = get_setting("re_captcha_site_key");
$secret_key = get_setting("re_captcha_secret_key");

if ($site_key && $secret_key) {
    if ($re_captcha_protocol === "v2") {
?>

        <div class="form-group">
            <div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>"></div>
        </div>

        <script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo app_lang('language_locale'); ?>"></script>

    <?php } else if ($re_captcha_protocol === "v3") { ?>

        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo get_setting('re_captcha_site_key'); ?>&hl=<?php echo app_lang('language_locale'); ?>"></script>

        <input type="hidden" name="re_captcha_token" id="re_captcha_token">

        <div class="form-group">
            <div id="recaptcha-container"></div>
        </div>

        <script>
            grecaptcha.ready(function() {
                grecaptcha.execute('<?php echo $site_key; ?>', {
                    action: 'submit'
                }).then(function(token) {
                    document.getElementById('re_captcha_token').value = token;
                });
            });
        </script>

<?php }
} ?>