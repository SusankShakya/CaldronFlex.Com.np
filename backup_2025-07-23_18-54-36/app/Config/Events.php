<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;
use Config\App;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }

    //load php hooks library
    require_once(APPPATH . "ThirdParty/PHP-Hooks/php-hooks.php");

    helper('plugin');

    define('PLUGINPATH', ROOTPATH . 'plugins/'); //define plugin path
    define('PLUGIN_URL_PATH', 'plugins/'); //define plugin path

    load_plugin_indexes();

    include APPPATH . 'Config/RiseHooks.php';
    include APPPATH . 'Config/RiseCustomHooks.php';

    set_default_csp_directives();
});

function load_plugin_indexes() {
    $plugins = file_get_contents(APPPATH . "Config/activated_plugins.json");
    $plugins = @json_decode($plugins);

    if (!($plugins && is_array($plugins) && count($plugins))) {
        return false;
    }

    foreach ($plugins as $plugin) {
        $index_file = PLUGINPATH . $plugin . '/index.php';

        if (file_exists($index_file)) {
            include $index_file;
        }
    }
}

function set_default_csp_directives() {
    $response = service('response');
    $csp = $response->getCSP();

    if ($csp->enabled()) {

        $default_allow = base64_decode("aHR0cHM6Ly9yZWxlYXNlcy5mYWlyc2tldGNoLmNvbQ==");

        // required for system updates
        $csp->addImageSrc($default_allow);
        $csp->addChildSrc($default_allow);

        $App = new App();
        if (!isset($App->do_not_add_default_csp)) {

            $csp->setDefaultSrc('self unsafe-inline');
            $csp->addScriptSrc('unsafe-inline');
            $csp->addStyleSrc('unsafe-inline');
            $csp->addFontSrc('self');
            $csp->addManifestSrc('self');
            $csp->addFrameSrc('self');
            $csp->addMediaSrc('self');

            // For reCaptcha
            $csp->addScriptSrc('https://www.google.com/recaptcha/api.js');
            $csp->addScriptSrc('https://www.gstatic.com/recaptcha/');
            $csp->addFrameSrc('https://www.google.com/');
            $csp->addConnectSrc('https://www.google.com/');

            // For Pusher
            $pusher_clusters = array('mt1', 'ap1', 'ap2', 'ap3', 'ap4', 'us2', 'us3', 'eu', 'sa1');

            foreach ($pusher_clusters as $cluster) {
                $csp->addConnectSrc('wss://ws-' . $cluster . '.pusher.com');
                $csp->addConnectSrc('https://sockjs-' . $cluster . '.pusher.com');
            }

            $csp->addConnectSrc('https://*.pushnotifications.pusher.com');

            // For TinyMCE
            $csp->addScriptSrc('https://cdn.tiny.cloud/');
            $csp->addImageSrc('https://sp.tinymce.com/');
            $csp->addStyleSrc('https://cdn.tiny.cloud/');
            $csp->addConnectSrc('https://cdn.tiny.cloud/');
            $csp->addConnectSrc('https://hyperlinking.iad.tiny.cloud/');

            // Google Drive
            $csp->addImageSrc(array('data:', 'https://lh3.googleusercontent.com', 'https://drive.google.com'));
        }

        // Finalize the CSP header
        $csp->finalize($response);
    }
}
