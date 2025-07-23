<div id="install-pwa-button" class="install-pwa-button alert alert-warning alert-dismissible m15 mb0 p0">
    <div id="install-pwa" class="clickable p15">
        <i data-feather='smartphone' class='icon mr10'></i> <?php echo js_anchor(app_lang("install_this_app")); ?>
    </div>
    <?php echo js_anchor("", array("class" => "btn-close", "id" => "close-install-pwa")); ?>
</div>

<div id="pwa-install-message-iphone">
    <h4><?php echo app_lang("install_this_app"); ?></h4>
    <div class="install-message"><?php echo sprintf(app_lang("pwa_install_message_for_iphone"), '<i data-feather="share" class="icon-14"></i>'); ?></div>
    <button id="pwa-install-message-close-btn" type="button" class="btn btn-default"> <?php echo app_lang("got_it"); ?></button>
</div>

<div style='display: none;'>
    <script type='text/javascript'>
        let deferredPrompt;

        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the default browser prompt from showing
            e.preventDefault();
            // Store the event to be used later
            deferredPrompt = e;
        });

        // Add a click event listener to the install button
        document.getElementById('install-pwa').addEventListener('click', () => {
            if (deferredPrompt) {
                // Show the installation prompt
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    // Clear the deferred prompt
                    deferredPrompt = null;
                });
            }
        });

        // Function to check if the app is installed
        function checkIfAppIsInstalled() {
            // For iOS
            if (window.navigator.standalone) {
                return true;
            }
            // For other platforms, include minimal-ui in the check
            if (window.matchMedia('(display-mode: standalone)').matches ||
                window.matchMedia('(display-mode: minimal-ui)').matches) {
                return true;
            }
            return false;
        }

        // Hide the button if the app is installed
        if (checkIfAppIsInstalled()) {
            document.getElementById('install-pwa-button').style.display = 'none';
        }

        document.getElementById('close-install-pwa').addEventListener('click', function() {
            if (document.getElementById("install-pwa-button")) {
                document.getElementById("install-pwa-button").remove();
            }
        });

        // Check if device is an iPhone
        function isIphone() {
            return /iPhone/.test(navigator.userAgent) && !window.MSStream;
        }

        // Show message if on iPhone
        if (isIphone()) {
            document.getElementById("install-pwa").addEventListener('click', function() {
                document.getElementById("pwa-install-message-iphone").style.display = 'block';
            });

            document.getElementById("pwa-install-message-close-btn").addEventListener('click', function() {
                document.getElementById("pwa-install-message-iphone").style.display = 'none';
            })
        }
    </script>
</div>