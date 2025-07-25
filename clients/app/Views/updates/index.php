<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "updates";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">
                <div class="page-title clearfix">
                    <h4> <?php echo app_lang('updates'); ?></h4>
                    <div class="title-button-group">
                       <a href='https://risedocs.fairsketch.com/doc/view/56' class='btn btn-info text-white' target='_blank'><i data-feather='help-circle' class='icon-16'></i> <?php echo app_lang('help'); ?></a>
                    </div>
                </div>

                <div id="app-update-container" class="card-body font-14">
                    <p>
                        <strong><?php echo app_lang("current_version") . " : " . $current_version; ?></strong>
                    </p>

                    <?php if (count($installable_updates) || count($downloadable_updates)) { ?>

                        <script type='text/javascript'>
                            $(document).ready(function () {
                                appAlert.warning("Please backup all files and database before start the installation.", {container: "#app-update-container", animate: false});
                                $(".app-alert-message").css("max-width", 1000);
                            });
                        </script>

                        <?php
                        foreach ($installable_updates as $salt => $version) {
                            echo "<p><a class='do-update' data-version='$version' href='#'>Click here to Install the version - <b>$version</b></a></p>";
                        }
                        foreach ($downloadable_updates as $salt => $version) {
                            echo "<p class='download-updates' data-salt='$salt' data-version='$version'>Version - <b>$version</b> available, awaiting for download.</p>";
                        }
                    } else {
                        echo "<p>No updates found.</p>";
                    }

                    if ($current_version === "3.4") {
                        //check session configs between App.php and Session.php
                        //both configs should be same
                        $app_config = config("App");
                        $session_config = config("Session");
                        if (!($app_config->sessionDriver === $session_config->driver && $app_config->sessionSavePath === $session_config->savePath)) {
                            echo "<div class='alert alert-warning'>There has custom session configurations in ...app/Config/App.php file. Please add these configurations to the ...app/Config/Session.php file.</div>";
                        }
                    }
                    ?>


                    <p>
                        <?php
                        if ($supported_until) {
                            if ($has_support) {
                                echo $supported_until . "<span class='badge bg-primary ml5'>Supported</span> ";
                            } else {
                                echo $supported_until . "<span class='badge bg-danger ml5'>Support Expired</span> ";
                                echo "<br>To purchase support, please visit <a href='https://codecanyon.net/item/rise-ultimate-project-manager/15455641'>here</a>.";
                            }
                        }
                        ?>
                    </p>

                    <div class="b-t pt15 mt10">
                        <?php echo anchor("Updates/systeminfo", "Php Info", array("class" => "btn btn-warning", "target" => "_blank")); ?>
                    </div>


                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var startDownload = function () {
            var $link = $(".download-updates").first(),
                    version = $link.attr("data-version"),
                    salt = $link.attr("data-salt");

            if ($link.length) {
                $link.replaceWith("<p class='downloading downloading-" + version + "'><span class='download-loader spinning-btn spinning'></span> Downloading the version - <b>" + version + "</b>. Please wait...</p>");
                appAjaxRequest({
                    url: "<?php echo_uri("updates/download_updates/"); ?>" + "/" + version + "/" + salt,
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            $(".downloading").html("<a class='do-update' data-version='" + version + "' href='#'>Click here to Install the version - <b>" + version + "</b></a>").removeClass("downloading");
                            startDownload();
                        } else {
                            $(".downloading").html("<p>" + response.message + "</p>").removeClass("downloading").addClass("alert alert-danger");
                        }
                    }
                });
            }
        };
        startDownload();


        $('body').on('click', '.do-update', function () {
            var version = $(this).attr("data-version");
            var acknowledged = $(this).attr("data-acknowledged");
            if (!acknowledged) {
                acknowledged = 0;
            }
            $("#app-update-container").html("<h3><span class='download-loader-lg spinning-btn spinning'></span> Installing version - " + version + ". Please wait... </h3>");
            appAjaxRequest({
                url: "<?php echo_uri("updates/do_update/"); ?>" + "/" + version + "/" + acknowledged,
                dataType: "json",
                success: function (response) {
                    $("#app-update-container").html("");
                    if (response.response_type === "success") {
                        appAlert.success(response.message, {container: "#app-update-container", animate: false});
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else if (response.response_type === "acknowledgement_required") {
                        appAlert.info(response.message, {container: "#app-update-container", animate: false});
                        $("#app-update-container").append("<p><a class='do-update' data-acknowledged='1' data-version='" + version + "' href='#'>I understand, install the version - <b>" + version + "</b></a></p>");
                    } else {
                        appAlert.error(response.message, {container: "#app-update-container", animate: false});
                    }

                }
            });
        });

    });
</script>