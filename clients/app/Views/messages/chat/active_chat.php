<div class="rise-chat-header box">
    <div class="box-content chat-back" id="js-back-to-chat-tabs">
        <i data-feather="chevron-left" class="icon-16 mt-1"></i>
    </div>
    <div class="box-content chat-title">
        <div><?php
                $user_id = "";
                if ($message_info->from_user_id == $login_user->id) {
                    $user_id = $message_info->to_user_id;
                } else {
                    $user_id = $message_info->from_user_id;
                }

                if ($message_info->another_user_id === $login_user->id) {
                    $user_name = $message_info->user_name;
                    $user_image = get_avatar($message_info->user_image);
                } else {
                    $user_name = $message_info->another_user_name;
                    $user_image = get_avatar($message_info->another_user_image);
                }

                $online = "";
                if (is_online_user($message_info->another_user_last_online)) {
                    $online = "<i id='js-active-chat-online-icon' class='online' data-user_id='$user_id'></i>";
                }

                echo "<span class='avatar avatar-xs'><img src='$user_image' />$online</span>";
                echo "<span>$user_name</span>";
                ?>
        </div>
    </div>
</div>

<div class="rise-chat-body clearfix">
    <div id="js-chat-messages-container" class="clearfix"></div>
    <div id="js-chat-reply-indicator"></div>
</div>

<div class="rise-chat-footer">
    <div id="chat-reply-form-dropzone" class="post-dropzone">
        <?php echo form_open(get_uri("messages/reply/1"), array("id" => "chat-message-reply-form", "class" => "general-form", "role" => "form")); ?>


        <?php echo view("includes/dropzone_preview"); ?>


        <input type="hidden" id="is_user_online" name="is_user_online" value="<?php echo is_online_user($message_info->another_user_last_online) ? 1 : 0; ?>">
        <input type="hidden" name="message_id" value="<?php echo $message_id; ?>">
        <input type="hidden" name="last_message_id" value="">

        <div class="chat-message-textarea">
            <?php
            echo form_textarea(array(
                "id" => "js-chat-message-textarea",
                "name" => "reply_message",
                "data-rule-required" => true,
                "autofocus" => true,
                "data-msg-required" => "",
                "placeholder" => app_lang('write_a_message')
            ));
            ?>
        </div>
        <div class="chat-button-section clearfix">
            <div class="chat-file-upload-icon float-start">
                <?php
                echo view("includes/upload_button", array("upload_button_text" => ""));
                ?>
            </div>
            <span class="btn btn-default float-end round message-send-button"><i data-feather="send" class="icon-16"></i></span>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {

        var textarea = document.querySelector('.rise-chat-footer textarea');
        textarea.addEventListener('keydown', autosizeRISEChatBox);

        function autosizeRISEChatBox() {
            var el = this;
            setTimeout(function() {
                if (el.scrollHeight < 110) {
                    $(".rise-chat-body").height(405 - el.scrollHeight);
                    el.style.cssText = 'height:' + el.scrollHeight + 'px';
                }
            });
        }




        loadMessages(1);
        $('.rise-chat-header').mousedown(handle_mousedown);
        $("#js-chat-message-textarea").keypress(function(e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                $("#chat-message-reply-form").submit();
                $(this).attr("style", "")
                return false;
            }
        });

        $("#chat-message-reply-form").appForm({
            isModal: false,
            showLoader: false,
            beforeAjaxSubmit: function(data) {

                //send the last message id
                $.each(data, function(index, obj) {
                    if (obj.name === "last_message_id") {
                        data[index]["value"] = $(".chat-msg").last().attr("data-message_id");
                    }
                });
                //clear message input box
                $("#js-chat-message-textarea").val("");
                $("#chat-message-reply-form").append('<div id="fast-loader" class="fast-line"></div>');
            },
            onSuccess: function(response) {
                if (window.formDropzone) {
                    window.formDropzone['chat-reply-form-dropzone'].removeAllFiles();
                }
                if (response.success) {
                    renderMessages(response.data);
                    $("#fast-loader").remove();
                }

            }
        });


        //set focus

        setTimeout(function() {
            $("#js-chat-message-textarea").focus();
        }, 200);

        $("#js-back-to-chat-tabs").click(function() {
            loadChatTabs(); // this method should be loaded when chat box loaded

            //reset the previous interval timer
            if (window.activeChatChecker) {
                window.clearInterval(window.activeChatChecker);
            }
        });
        //bind scroll with chat messages and load more messages when scrolling on top
        var fatchNewData = true,
            topMessageId = 0;
        $("#js-chat-messages-container").scroll(function() {
            if ($(this).scrollTop() < 50 && fatchNewData) {
                fatchNewData = false;
                loadMoreMessages(function() {
                    fatchNewData = true; //reset the status so that it can call again
                });
            }
        });

        if ("<?php echo get_setting('enable_chat_via_pusher') ?>" && "<?php echo get_setting('enable_push_notification') ?>") {
            var pusherKey = "<?php echo get_setting("pusher_key"); ?>";
            var pusherCluster = "<?php echo get_setting("pusher_cluster"); ?>";

            var pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                encrypted: true
            });

            var channel = pusher.subscribe("user_" + "<?php echo $login_user->id; ?>" + "_message_id_" + "<?php echo $message_id ?>" + "_channel");

            channel.bind('rise-chat-event',
                function(data) {
                    appAjaxRequest({
                        url: "<?php echo get_uri('messages/view_chat'); ?>",
                        type: "POST",
                        data: {
                            message_id: "<?php echo $message_id; ?>",
                        },
                        success: function(response) {
                            if (response) {
                                $("#js-chat-messages-container").append(response);
                                $("#js-chat-reply-indicator").html(" ");
                                chatScrollToBottom();
                            }
                        }
                    });
                });

            channel.bind('rise-chat-typing-event',
                function(data) {
                    $("#js-chat-reply-indicator").html(data);
                    chatScrollToBottom();

                    setTimeout(function() {
                        $("#js-chat-reply-indicator").html(" ");
                    }, 8000);
                });
        }

        $(".message-send-button").click(function() {
            $(this).trigger("submit");
        });

    });

    function handle_mousedown(e) {
        var dragging = {};
        dragging.pageX0 = e.pageX;
        dragging.pageY0 = e.pageY;
        dragging.offset0 = $(this).offset();

        function handleDragging(e) {
            var left = dragging.offset0.left + (e.pageX - dragging.pageX0);
            var top = dragging.offset0.top + (e.pageY - dragging.pageY0);
            $(".rise-chat-wrapper").offset({
                top: top,
                left: left
            });
        }

        function handleMouseup(e) {
            $('body').off('mousemove', handleDragging).off('mouseup', handleMouseup);
        }
        $('body').on('mouseup', handleMouseup).on('mousemove', handleDragging);
    }

    function chatScrollToBottom(is_mobile = false) {
        //scroll to bottom only if the foucs on textarea
        var $focused = $(':focus');
        if (($focused && $focused.is("textarea")) || is_mobile) {
            $(".rise-chat-body").animate({
                scrollTop: 10000000
            }, 100);
        }
    }

    function loadMessages(firstLoad) {
        checkNewMessagesAutomatically();
        var message_id = "<?php echo $message_id; ?>";
        appAjaxRequest({
            url: "<?php echo get_uri('messages/view_chat'); ?>",
            type: "POST",
            data: {
                message_id: message_id,
                last_message_id: $(".js-chat-msg").last().attr("data-message_id"),
                is_first_load: firstLoad,
                another_user_id: $("#js-active-chat-online-icon").attr("data-user_id")
            },
            success: function(response) {
                if (response) {
                    renderMessages(response);
                }

            }
        });
    }

    function loadMoreMessages(callback) {
        if ($("#js-chat-old-messages").attr("no-messages") === "1")
            return false; //there is no messages to show.

        var message_id = "<?php echo $message_id; ?>";

        $("#js-chat-old-messages").prepend("<div id='loading-more-chat-messages-" + message_id + "' class='inline-loader' >....<br></br></div>");

        appAjaxRequest({
            url: "<?php echo get_uri('messages/view_chat'); ?>",
            type: "POST",
            data: {
                message_id: "<?php echo $message_id; ?>",
                top_message_id: $(".js-chat-msg").first().attr("data-message_id"),
                another_user_id: $("#js-active-chat-online-icon").attr("data-user_id")
            },
            success: function(response) {
                if (response) {
                    $("#js-chat-old-messages").prepend(response);
                    if (callback) {
                        callback(); //has more data?
                    }
                }

                //if we got empty message, then we'll add a flag to stop finding new messages for next calls.
                if (!$(response).find("#temp-script").remove().text()) {
                    $("#js-chat-old-messages").attr("no-messages", "1");
                }



                $('#loading-more-chat-messages-' + message_id).remove();

            }
        });
    }


    function renderMessages(html) {
        $("#js-chat-messages-container").append(html);
        chatScrollToBottom(true);
    }


    //reset existing timmer and check new message after a certain time
    function checkNewMessagesAutomatically() {

        //reset the previous interval timer
        if (window.activeChatChecker) {
            window.clearInterval(window.activeChatChecker);
        }

        if ("<?php echo (get_setting('enable_chat_via_pusher') && get_setting("enable_push_notification")) != 1 ?>") {
            window.activeChatChecker = window.setInterval(function() {
                loadMessages();
            }, 5000); //check message in every 5 seconds
        }
    }

    //send typing status to pusher
    if ("<?php echo get_setting('enable_chat_via_pusher') ?>" && "<?php echo get_setting('enable_push_notification') ?>") {
        addKeyup();

        function addKeyup() {
            $("#chat-message-reply-form").one('keyup', function(e) {
                appAjaxRequest({
                    url: '<?php echo get_uri("messages/send_typing_indicator_to_pusher"); ?>',
                    type: "POST",
                    data: {
                        message_id: "<?php echo $message_id ?>"
                    }
                });

                setTimeout(addKeyup, 10000);
            });
        }
    }
</script>