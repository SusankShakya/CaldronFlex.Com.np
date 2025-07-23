<?php
$user_id = $login_user->id;
?>

<div class="ticket-top-button clearfix">
    <?php
    echo view("includes/back_button", array("button_url" => "", "button_text" => app_lang("tickets"), "extra_class" => "float-start dark"));

    echo "<a href='javascript:;' class='ticket-reply-button dark d-sm-none pe-auto'><i data-feather='corner-up-left' class='icon-22 mr5 pe-auto'></i> " . app_lang('reply') . "</a>";
    ?>
</div>

<div class="page-content ticket-details-view xs-full-width clearfix hide-under-modal">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div id="ticket-details-top-bar"><?php echo view("tickets/top_bar"); ?></div>
                <?php echo view("tickets/details"); ?>
            </div>
        </div>
    </div>
</div>

<?php echo view("tickets/bottom_menu_bar"); ?>

<textarea id="signature-text" class="hide"><?php echo get_setting('user_' . $user_id . '_signature'); ?></textarea>
<style type="text/css">
    #mobile-bottom-menu {
        pointer-events: auto;
        will-change: transform;
        -webkit-transform: translate3d(0, 0, 0);
        touch-action: manipulation;
        overscroll-behavior: contain;
    }
</style>
<script type="text/javascript">
    $(document).ready(function() {

        appContentBuilder.init("<?php echo get_uri('tickets/view/' . $ticket_info->id); ?>", {
            id: "ticket-details-page-builder",
            data: {
                view_type: "ticket_meta"
            },
            reloadHooks: [{
                    type: "app_form",
                    id: "ticket-form"
                },
                {
                    type: "app_form",
                    id: "comment-form"
                },
                {
                    type: "ajax_request",
                    group: "ticket_status"
                },
                {
                    type: "app_modifier",
                    group: "ticket_info"
                },
                {
                    type: "app_table_row_update",
                    tableId: "ticket-table"
                }
            ],
            reload: function(bind, result) {
                bind("#ticket-details-top-bar", result.top_bar);
                bind("#ticket-details-ticket-info", result.ticket_info);
                bind("#ticket-details-client-info", result.client_info);
            }
        });

        var decending = "<?php echo $sort_as_decending; ?>";

        $("#comment-form").appForm({
            isModal: false,
            onSuccess: function(result) {

                if (decending) {
                    $(result.data).insertAfter("#comment-form-container");
                } else {
                    $(result.data).insertBefore("#comment-form-container");
                }

                appAlert.success(result.message, {
                    duration: 10000
                });

                if (result.validation_error) {
                    appAlert.error(result.message, {
                        duration: 10000
                    });
                }

                if (window.formDropzone) {
                    window.formDropzone['ticket-comment-dropzone'].removeAllFiles();
                }

                if (AppHelper.settings.enableRichTextEditor === "1") {
                    setTimeout(function() {
                        $("#description").val($("#signature-text").val());
                        initWYSIWYGEditor("#description");
                    }, 200);
                } else {
                    $("#description").val($("#signature-text").val() || "");
                }
            }
        });

        if (!$("#signature-text").val()) {
            $("#description").text() ? $("#description").text("\n" + $("#description").text()) : "";
            $("#description").focus();
        }

        if (AppHelper.settings.enableRichTextEditor === "1") {
            initWYSIWYGEditor("#description");
        }

        var $inputField = $("#description"),
            $lastFocused;


        $inputField.focus(function() {
            $lastFocused = document.activeElement;
        });

        function insertTemplate(text) {

            if (AppHelper.settings.enableRichTextEditor === "1") {
                insertHTMLintoWYSIWYGEditor($inputField, text);
            } else {
                if ($lastFocused === undefined) {
                    return;
                }

                var scrollPos = $lastFocused.scrollTop;
                var pos = 0;
                var browser = (($lastFocused.selectionStart || $lastFocused.selectionStart === "0") ? "ff" : (document.selection ? "ie" : false));

                if (browser === "ff") {
                    pos = $lastFocused.selectionStart;
                }

                var front = ($lastFocused.value).substring(0, pos);
                var back = ($lastFocused.value).substring(pos, $lastFocused.value.length);
                $lastFocused.value = front + text + back;
                pos = pos + text.length;

                $lastFocused.scrollTop = scrollPos;
            }

            //close the modal
            $("#close-template-modal-btn").trigger("click");
        }

        // Common function for inserting template
        function insertTemplateIntoEditor(template) {
            if (AppHelper.settings.enableRichTextEditor !== "1") {
                //insert only text when rich editor isn't enabled
                template = $("<div>").html(template).text();
            }

            if ($lastFocused === undefined) {
                if (AppHelper.settings.enableRichTextEditor === "1") {
                    insertTemplate(template);
                } else {
                    $("#description").text(template);
                }
            } else {
                insertTemplate(template);
            }

            // Close modal if exists
            $("#close-template-modal-btn").trigger("click");
        }

        // When clicking on the ticket template table row
        $("body").on("click", "#ticket-template-table tr", function() {
            var template = $(this).find(".js-description").html();
            insertTemplateIntoEditor(template);
        });

        $("body").on("click", ".insert-into-editor-button", function() {
            var template = $(this).closest(".ticket-comment-container").find("#ticket-comment-description").val();
            insertTemplateIntoEditor(template);
        });

        //set value 1, when click save as button
        $("#save-as-note-button").click(function() {
            $("#is-note").val('1');
            $(this).trigger("submit");
            setTimeout(function() {
                $("#is-note").val('0');
            }, 300);
        });

        //set value 0, when click post comment button
        $("#save-ticket-comment-button").click(function() {
            $("#is-note").val('0');
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

        $(".pin-comment-button").click(function() {
            var comment_id = $(this).attr('data-pin-comment-id');
            var ticketId = "<?php echo $ticket_info->id; ?>";

            appLoader.show();
            appAjaxRequest({
                url: "<?php echo get_uri("tickets/pin_comment/"); ?>/" + comment_id + "/" + ticketId,
                type: 'POST',
                dataType: "json",
                success: function(result) {
                    if (result.success) {
                        $("#ticket-pinned-comments").find(".card-body").append(result.data);
                        appLoader.hide();
                    } else {
                        appAlert.error(result.message);
                    }

                    if (result.status) {
                        $("#pin-comment-button-" + comment_id).addClass("hide");
                        $("#unpin-comment-button-" + comment_id).removeClass("hide");
                        $("#ticket-pinned-comments").removeClass("hide");
                    }
                }
            });
        });

        $(".unpin-comment-button").click(function() {
            var comment_id = $(this).attr('data-pin-comment-id');
            $("#pin-comment-button-" + comment_id).removeClass("hide");
            $("#unpin-comment-button-" + comment_id).addClass("hide");
        });

        //remove comment link from url
        var commentHash = window.location.hash;
        if (commentHash.indexOf('#ticket-comment') > -1) {
            history.replaceState("", "", window.location.pathname);
        }

        function highlightSpecificComment(commentId) {
            $(".comment-highlight-section").removeClass("comment-highlight");
            var $comment = $("#ticket-comment-" + commentId);
            $comment.addClass("comment-highlight");

            if (isMobile()) {
                // For mobile, manually scroll to the element with offset
                $('html, body').animate({
                    scrollTop: $comment.offset().top - 70
                }, 200);
            } else {
                // Default behavior for desktop
                window.location.hash = "";
                window.location.hash = "#ticket-comment-" + commentId;
            }
        }

        $(".pinned-comment-highlight-link").click(function(e) {
            var comment_id = $(this).attr('data-original-comment-link-id');
            highlightSpecificComment(comment_id);
        });

        // In mobile view, when navigate back button is clicked, slide out the details page to the right
        $(".navigate-back").on("click", function(event) {
            event.stopPropagation();
            event.preventDefault();

            var ticketListSection = $(".tickets-list-section"),
                compactDetailsPage = $("#compact-details-page"),
                ticketBottomMenu = $("#ticket-bottom-menu"),
                mobileBottomMenu = $("#mobile-bottom-menu");

            if (!ticketListSection.length) {
                window.location.href = "<?php echo get_uri('tickets/index'); ?>"
            }
            if (!compactDetailsPage.length) {
                return;
            }

            // Slide out the details page to the right
            compactDetailsPage.removeClass("slide-in-right").addClass("slide-out-right");
            ticketBottomMenu.removeClass("slide-in-right").addClass("slide-out-right");

            setTimeout(function() {
                compactDetailsPage.addClass("hide");
                ticketBottomMenu.addClass("hide");
            }, 50);

            // After animation completes, show the list 
            setTimeout(function() {
                ticketListSection.removeClass("hide").removeClass("slide-out-left").addClass("slide-in-left");
                mobileBottomMenu.removeClass("slide-out-left").addClass("in-out-left");
                $(".navbar").removeClass("hide");
                $(".navbar-custom").removeClass("hide");

                compactDetailsPage.addClass("hide").removeClass("slide-out-right");
                ticketBottomMenu.addClass("hide").removeClass("slide-out-right");

                window.scrollTo({
                    top: window.lastScrollPosition,
                    behavior: "instant" // default, instant scroll with no animation
                });

            }, 250);
        });

        <?php if ($can_edit_ticket) { ?>

            $('body').on('click', '[data-act=ticket-modifier]', function(e) {
                $(this).appModifier({
                    dropdownData: {
                        labels: <?php echo json_encode($label_suggestions); ?>,
                        assigned_to: <?php echo json_encode($assign_to_dropdown); ?>,
                        cc_contacts_and_emails: <?php echo json_encode($cc_contacts_dropdown); ?>,
                        status: <?php echo json_encode($status_dropdown); ?>
                    }
                });

                return false;
            });

        <?php } ?>

        var ticketTopButton = $(".ticket-top-button");
        var initialOffsetTop = ticketTopButton.offset().top;

        var scrollHandler = function() {
            var scrollTop = $(window).scrollTop();

            if ($("#compact-details-page").length) {
                initialOffsetTop = 20;
            }

            if (scrollTop > initialOffsetTop) {
                ticketTopButton.addClass("position-fixed-top");
            } else {
                ticketTopButton.removeClass("position-fixed-top");
            }
        };

        // Handle both touch and scroll events for better mobile support
        $(window).on('scroll touchmove', scrollHandler);

        $(".ticket-reply-button").click(function(event) {
            event.stopPropagation();
            event.preventDefault();

            $("#comment-form-container").removeClass("hidden-xs");
            setTimeout(function() {
                window.scrollTo({
                    top: 0,
                    behavior: "instant" // default, instant scroll with no animation
                });
                setTimeout(function() {
                    if ($(".note-editable").length) {
                        $(".note-editable").focus();
                    } else {
                        $("#description").focus();
                    }
                });
            }, 200);
        });

        $('#ticket-bottom-menu .menu-item').on('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var target = $(this).attr('data-target'),
                $target = $('#' + target),
                isCompactView = $("#compact-details-page").length,
                isFixedTopBtn = $(".ticket-top-button").hasClass("position-fixed-top");

            if ($target.length) {
                var headerOffset = isCompactView ? 75 : 115;
                if (isFixedTopBtn && !isCompactView) {
                    headerOffset -= 42;
                }

                if (!isFixedTopBtn && isCompactView) {
                    headerOffset += 42;
                }

                var scrollPosition = $target.offset().top - headerOffset;
                requestAnimationFrame(() => {
                    window.scrollTo({
                        top: scrollPosition,
                        behavior: 'smooth'
                    });
                });
            }
            return false;
        });

        if (isMobile() && $("#compact-details-page").length) {
            var $ticketListSection = $(".tickets-list-section"),
                $compactDetailsPage = $("#compact-details-page"),
                $ticketBottomMenu = $("#ticket-bottom-menu"),
                $mobileBottomMenu = $("#mobile-bottom-menu");

            $ticketListSection.addClass("hide");

            $(".navbar").addClass("hide");
            $(".navbar-custom").addClass("hide");
            $ticketListSection.removeClass("slide-in-left").addClass("slide-out-left");
            $mobileBottomMenu.removeClass("slide-in-left").addClass("slide-out-left");

            setTimeout(function() {
                $ticketListSection.addClass("hide");
                $compactDetailsPage.addClass("fixed-details");
            }, 50);

            setTimeout(function() {
                $compactDetailsPage.removeClass("hide").addClass("slide-in-right");
                $ticketBottomMenu.addClass("slide-in-right");
            }, 50);

            setTimeout(function() {
                $compactDetailsPage.removeClass("slide-in-right");
                $ticketBottomMenu.removeClass("slide-in-right");
            }, 250);
        }
    });
</script>