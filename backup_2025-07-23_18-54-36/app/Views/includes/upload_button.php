<div class="me-auto">
    <?php
    $upload_button_id = make_random_string();
    if (!isset($upload_url)) {
        $upload_url = get_uri("uploader/upload_file");
    }
    if (!isset($validation_url)) {
        $validation_url = get_uri("uploader/validate_file");
    }
    ?>

    <?php
    if (!isset($upload_button_text)) {
        $upload_button_text = app_lang("upload_file");
    }
    ?>

    <button id="<?php echo $upload_button_id; ?>" class="btn btn-default upload-file-button float-start round round-btn-xs" type="button"><i data-feather="paperclip" class="icon-16"></i> <span class="hidden-xs"><?php echo $upload_button_text; ?></span>
    </button>
    <?php

    $https = !empty($_SERVER['HTTPS']);
    if (!$https) {
        $https = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    $show_recording = get_setting("enable_audio_recording");

    if (isset($hide_recording) && $hide_recording) {
        $hide_recording = true;
    } else {
        $hide_recording = false;
    }

    if ($show_recording && $https && !$hide_recording) {
    ?>
        <button type="button" id="record-start-button" class="btn btn-default record-start-btn ml10" style="color:#7988a2"><i data-feather="mic" class="icon-16"></i></button>
        <button type="button" id="record-stop-button" class="btn btn-default record-end-btn ml10 hide">
            <div class="stop-recording"></div>
        </button>
        <span class="recording-text ml5 hide"><?php echo app_lang('recording'); ?></span>

    <?php
        load_js(array(
            "assets/js/recordrtc/RecordRTC.min.js",
        ));
    } else if ($show_recording && !$https && !$hide_recording) {
    ?>
        <span class="ml10"><span class=" help" data-bs-toggle="tooltip" title="<?php echo app_lang('https_required'); ?>"><span class="btn btn-default record-start-btn disabled opacity-25"><i data-feather="mic" class="icon-16"></i></span></span></span>

    <?php }
    ?>
</div>



<script type="text/javascript">
    $(document).ready(function() {

        var $dropzoneElement = $("#<?php echo $upload_button_id; ?>").closest(".post-dropzone");
        var drozoneId = $dropzoneElement.attr("id");
        if (!window.formDropzone) {
            window.formDropzone = [];
        }

        var dropzoneOptions = {};
        <?php if (isset($single_file) && $single_file) { ?>
            dropzoneOptions.maxFiles = 1;
        <?php } ?>

        window.formDropzone[drozoneId] = attachDropzoneWithForm("#" + drozoneId, "<?php echo $upload_url; ?>", "<?php echo $validation_url; ?>", dropzoneOptions);

        $('[data-bs-toggle="tooltip"]').tooltip();


        var enableRecording = "<?php echo $https && $show_recording ? '1' : ''; ?>";


        if (enableRecording) {

            //for recording
            var startRecordButton = document.getElementById('record-start-button');
            var stopRecordButton = document.getElementById('record-stop-button');

            var recordOptions = {
                type: 'audio',
                mimeType: 'audio/webm'
            };

            //variables to store the recording blob data
            var recorder, audioBlob,
                duration = {};

            // Event listener for the start recording button
            startRecordButton.addEventListener('click', function() {
                if (!recorder) {
                    // Start recording
                    duration.start = new Date();
                    navigator.mediaDevices.getUserMedia({
                        audio: true
                    }).then(function(stream) {
                        recorder = RecordRTC(stream, recordOptions);
                        recorder.startRecording();
                        $("#record-button").addClass("btn-success");
                        $(".recording-text").removeClass("hide");
                        $(".record-end-btn").removeClass("hide");
                        $(".record-start-btn").addClass("hide");
                    });
                }
            });

            // Event listener for the stop recording button
            stopRecordButton.addEventListener('click', function() {
                if (recorder) {

                    duration.end = new Date();
                    recorder.stopRecording(function() {
                        // Get the recorded audio blob
                        audioBlob = recorder.getBlob();

                        uploadAudioBlob(audioBlob, duration);

                        // Reset the recorder and button style
                        recorder = null;
                        $("#record-button").removeClass("btn-success");
                        $("#record-button").addClass("btn-default");
                        $(".recording-text").addClass("hide");
                        $(".record-start-btn").removeClass("hide");
                        $(".record-end-btn").addClass("hide");
                        $(".post-file-upload-row").addClass("audio-preview");
                    });
                }
            });

            // Function to upload the audio blob to the specified URL
            function uploadAudioBlob(blob, duration) {

                const timeDifference = calculateTimeDifference(duration);

                var blobName = 'recording-' + timeDifference + new Date().getMilliseconds() + "ms";
                blob.name = blobName + '.webm';

                //Upload the audio using dropzone
                window.formDropzone[drozoneId].addFile(blob);

                // Create an audio element to preview the recording
                var audioElement = document.createElement('audio');
                audioElement.src = URL.createObjectURL(blob);
                audioElement.controls = true;

                // Create a div element to wrap the audio element
                var audioContainer = $('<div class="audio-container">');
                audioContainer.append(audioElement);
                $(".preview:last-child").append(audioContainer);

                var showLinkCopyButton = "<?php echo isset($show_link_copy_button) && $show_link_copy_button ? '1' : ''; ?>";
                if (showLinkCopyButton) {
                    $(".preview:last-child").append('<span class="copy-link copy-file-link-btn" data-file-name="' + blobName + '" data-context="notes"><i data-feather="link" class="icon-14"></i> Copy</span>');
                }

                copyLink();
            }

            copyLink();

            function copyLink() {
                $(".copy-file-link-btn").click(function() {
                    var fileName = $(this).attr('data-file-name');
                    var reference = "<?php echo app_lang('reference'); ?>";
                    var tempInput = document.createElement("input");
                    tempInput.style = "position: absolute; left: -1000px; top: -1000px";
                    tempInput.value = "#[" + fileName + "] (" + reference + ")";
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand("copy");
                    document.body.removeChild(tempInput);

                    var tooltip = $('<div class="tooltip bs-tooltip-auto fade show" style="position: absolute; inset: auto auto 0px 0px; margin: 0px; transform: translate(-20px, -24px);" data-popper-placement="top"><div class="tooltip-arrow" style="position: absolute; left: 0px; transform: translate(27px, 0px);"></div><div class="tooltip-inner"><?php echo app_lang("link_copied"); ?></div></div>');

                    $(this).append(tooltip);

                    setTimeout(function() {
                        tooltip.remove();
                    }, 1500);

                });
            }

            function calculateTimeDifference(duration) {
                var date1 = new Date(duration.start);
                var date2 = new Date(duration.end);

                var timeDifference = Math.abs(date2 - date1);

                var hours = Math.floor(timeDifference / 3600000); // 1 hour = 3600000 milliseconds
                var minutes = Math.floor((timeDifference % 3600000) / 60000); // 1 minute = 60000 milliseconds
                var seconds = Math.floor((timeDifference % 60000) / 1000); // 1 second = 1000 milliseconds

                // Format the output string
                var formattedTime = `${hours}h${minutes}m${seconds}s`;

                return formattedTime;
            }

        }

    });
</script>