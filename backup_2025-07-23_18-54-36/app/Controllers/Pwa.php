<?php

namespace App\Controllers;

class Pwa extends App_Controller {
  function __construct() {
    parent::__construct();
    helper(array('general'));
  }

  public function manifest() {
    $base_url = base_url();

    $pwa_theme_color = get_setting("pwa_theme_color");
    if (!$pwa_theme_color) {
      $pwa_theme_color = "#1c2026";
    }


    $icon_name = "default-pwa-icon.png";
    $pwa_icon = get_setting("pwa_icon");

    if ($pwa_icon) {
      try {
        $pwa_icon = unserialize($pwa_icon);
        if (is_array($pwa_icon)) {
          $icon_name = get_array_value($pwa_icon, "file_name");
        }
      } catch (\Exception $ex) {
      }
    }

    $system_file_path = get_setting("system_file_path");

    // Detect if the device is mobile
    $isMobile = preg_match('/(android|iphone|ipad|windows phone)/i', get_array_value($_SERVER, 'HTTP_USER_AGENT'));

    $display_mode = "standalone";
    if (!$isMobile) {
      $display_mode = "minimal-ui";
    }

    $manifest = [
      "name" => get_setting("app_title"),
      "short_name" => get_setting("app_title"),
      "start_url" => "{$base_url}index.php",
      "display" => $display_mode,
      "background_color" => $pwa_theme_color,
      "theme_color" => $pwa_theme_color,
      "icons" => [
        [
          "src" => "{$base_url}{$system_file_path}pwa/{$icon_name}",
          "sizes" => "192x192",
          "type" => "image/png"
        ]
      ]
    ];

    // Set the content type to application/json
    return $this->response->setContentType('application/json')
      ->setBody(json_encode($manifest));
  }

  private function _urls_to_cache() {
    $base_url = base_url();
    $app_version = get_setting("app_version");

    $urls = array(
      "assets/js/app.all.js",
      "assets/bootstrap/css/bootstrap.min.css",
      "assets/js/select2/select2.css",
      "assets/js/select2/select2-bootstrap.min.css",
      "assets/css/app.all.css",
      "assets/js/push_notification/pusher/pusher.min.js",
      "assets/js/push_notification/pusher/pusher.beams.min.js",
      "assets/js/summernote/summernote.css",
      "assets/js/summernote/summernote.min.js",
      "assets/js/summernote/lang/summernote-en-US.js",
      "assets/js/fullcalendar/fullcalendar.min.css",
      "assets/js/fullcalendar/locales-all.min.js",
      "assets/js/fullcalendar/fullcalendar.min.js"
    );

    foreach ($urls as $key => $url) {
      $urls[$key] = "{$base_url}{$url}?v={$app_version}";
    }

    $urls[] = "{$base_url}assets/images/avatar.jpg";

    return $urls;
  }

  //standalone service worker for browser
  public function service_worker() {
    header("Content-Type: application/javascript");
    header("Service-Worker-Allowed: /");

    $app_version = get_setting("app_version");
    $base_url = base_url();
    $urlsToCache = $this->_urls_to_cache();

    // Convert PHP array to JS array string
    $jsUrlsArray = json_encode($urlsToCache, JSON_UNESCAPED_SLASHES);


    $pusher_beams_instance_id = get_setting("pusher_beams_instance_id");
    $pusher_enabled = get_setting("enable_push_notification");

    $serviceWorkerScript = "
      const CACHE_NAME = 'pwa-cache-{$app_version}';
      const urlsToCache = {$jsUrlsArray};

      self.addEventListener('install', event => {
        event.waitUntil(
          caches.open(CACHE_NAME)
            .then(cache => {
              return cache.addAll(urlsToCache);
            })
        );
      });
      
      /*
      self.addEventListener('fetch', event => {
        const url = new URL(event.request.url);
        
        // Skip cross-origin requests
        if (url.origin !== self.location.origin) return;
        
        // Only handle requests that are in urlsToCache
        const shouldCache = urlsToCache.some(cacheUrl => {
          return url.href === new URL(cacheUrl, self.location.origin).href;
        });
        
        if (!shouldCache) return;
        
        event.respondWith(
          caches.match(event.request).then(response => {
            return response || fetch(event.request);
          })
        );
        
      });
      */

      self.addEventListener('activate', event => {
        const cacheWhitelist = [CACHE_NAME];
        event.waitUntil(
          caches.keys().then(cacheNames => {
            return Promise.all(
              cacheNames.map(cacheName => {
                if (cacheWhitelist.indexOf(cacheName) === -1) {
                  return caches.delete(cacheName);
                }
              })
            );
          })
        );
      });

      self.addEventListener('notificationclick', event => {
       
        event.waitUntil(
          clients.matchAll({ type: 'window' }).then(clientsArr => {
            if (clientsArr.length > 0 && clientsArr[0]) {
              clientsArr[0].postMessage({ type: 'NOTIFICATION_CLICKED', data: event.notification.data });
              clientsArr[0].focus();
            }
          })
        );
        event.notification.close();
       
      });

      importScripts('{$base_url}assets/js/service-worker.js?v={$app_version}');
      
      ";

    // Set the content type to application/javascript and return the script
    return $this->response->setContentType('application/javascript')->setBody($serviceWorkerScript);
  }
}
