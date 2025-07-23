<?php echo form_open(get_uri("events/save"), array("id" => "event-form", "class" => "general-form", "role" => "form")); ?>
<div id="events-dropzone" class="post-dropzone">
    <div class="modal-body clearfix">
        <div class="container-fluid">
            <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
            <div class="form-group">
                <div class="row">
                    <label for="title" class=" col-md-3"><?php echo app_lang('title'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "title",
                            "name" => "title",
                            "value" => $model_info->title,
                            "class" => "form-control",
                            "placeholder" => app_lang('title'),
                            "autofocus" => true,
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="description" class=" col-md-3"><?php echo app_lang('description'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_textarea(array(
                            "id" => "description",
                            "name" => "description",
                            "value" => process_images_from_content($model_info->description, false),
                            "class" => "form-control",
                            "placeholder" => app_lang('description'),
                            "data-rich-text-editor" => true
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <div class="clearfix">
                <div class="row">
                    <label for="start_date" class=" col-md-3 col-sm-3"><?php echo app_lang('start_date'); ?></label>
                    <div class="col-md-4 col-sm-4 form-group">
                        <?php
                        echo form_input(array(
                            "id" => "start_date",
                            "name" => "start_date",
                            "value" => $model_info->start_date,
                            "class" => "form-control",
                            "placeholder" => app_lang('start_date'),
                            "autocomplete" => "off",
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                    <label for="start_time" class=" col-md-2 col-sm-2"><?php echo app_lang('start_time'); ?></label>
                    <div class=" col-md-3 col-sm-3">
                        <?php
                        if (is_date_exists($model_info->start_time) && $model_info->start_time == "00:00:00") {
                            $start_time = "";
                        } else {
                            $start_time = $model_info->start_time;
                        }

                        if ($time_format_24_hours) {
                            $start_time = $start_time ? date("H:i", strtotime($start_time)) : "";
                        } else {
                            $start_time = $start_time ? convert_time_to_12hours_format(date("H:i:s", strtotime($start_time))) : "";
                        }

                        echo form_input(array(
                            "id" => "start_time",
                            "name" => "start_time",
                            "value" => $start_time,
                            "class" => "form-control",
                            "placeholder" => app_lang('start_time')
                        ));
                        ?>
                    </div>
                </div>
            </div>


            <div class="clearfix">
                <div class="row">
                    <label for="end_date" class=" col-md-3 col-sm-3"><?php echo app_lang('end_date'); ?></label>
                    <div class=" col-md-4 col-sm-4 form-group">
                        <?php
                        echo form_input(array(
                            "id" => "end_date",
                            "name" => "end_date",
                            "value" => $model_info->end_date,
                            "class" => "form-control",
                            "placeholder" => app_lang('end_date'),
                            "autocomplete" => "off",
                            "data-rule-greaterThanOrEqual" => "#start_date",
                            "data-msg-greaterThanOrEqual" => app_lang("end_date_must_be_equal_or_greater_than_start_date")
                        ));
                        ?>
                    </div>
                    <label for="end_time" class=" col-md-2 col-sm-2"><?php echo app_lang('end_time'); ?></label>
                    <div class=" col-md-3 col-sm-3">
                        <?php
                        if (is_date_exists($model_info->end_time) && $model_info->end_time == "00:00:00") {
                            $end_time = "";
                        } else {
                            $end_time = $model_info->end_time;
                        }

                        if ($time_format_24_hours) {
                            $end_time = $end_time ? date("H:i", strtotime($end_time)) : "";
                        } else {
                            $end_time = $end_time ? convert_time_to_12hours_format(date("H:i:s", strtotime($end_time))) : "";
                        }

                        echo form_input(array(
                            "id" => "end_time",
                            "name" => "end_time",
                            "value" => $end_time,
                            "class" => "form-control",
                            "placeholder" => app_lang('end_time')
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="location" class=" col-md-3"><?php echo app_lang('location'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "location",
                            "name" => "location",
                            "value" => $model_info->location,
                            "class" => "form-control",
                            "placeholder" => app_lang('location'),
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="event_labels" class=" col-md-3"><?php echo app_lang('labels'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "event_labels",
                            "name" => "labels",
                            "value" => $model_info->labels,
                            "class" => "form-control",
                            "placeholder" => app_lang('labels')
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <?php if ($client_id) { ?>
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>" />
            <?php } else if (isset($clients_dropdown)) { ?>
                <div class="form-group">
                    <div class="row">
                        <label for="client_id" class=" col-md-3"><?php echo app_lang('client'); ?></label>
                        <div class=" col-md-9">
                            <?php
                            echo form_input(array(
                                "id" => "clients_dropdown",
                                "name" => "client_id",
                                "value" => $model_info->client_id,
                                "class" => "form-control",
                                "placeholder" => app_lang('client')
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php echo view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?>

            <?php if ($can_share_events) { ?>
                <div class="form-group">
                    <div class="row">
                        <label for="share_with" class=" col-md-3"><?php echo app_lang('share_with'); ?></label>
                        <div class=" col-md-9">

                            <div id="share_with_container">
                                <?php echo $get_sharing_options_view; ?>
                            </div>

                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <input type="hidden" name="share_with" value="" />
            <?php } ?>

            <div class="form-group">
                <div class="row">
                    <label for="event_recurring" class=" col-md-3 col-xs-5 col-sm-4"><?php echo app_lang('repeat'); ?></label>
                    <div class=" col-md-9 col-xs-7 col-sm-8">
                        <?php
                        echo form_checkbox("recurring", "1", $model_info->recurring ? true : false, "id='event_recurring' class='form-check-input'");
                        ?>
                    </div>
                </div>
            </div>

            <div id="recurring_fields" class="<?php if (!$model_info->recurring) echo "hide"; ?>">
                <div class="form-group">
                    <div class="row">
                        <label for="repeat_every" class=" col-md-3 col-xs-12"><?php echo app_lang('repeat_every'); ?></label>
                        <div class="col-md-4 col-xs-6">
                            <?php
                            echo form_input(array(
                                "id" => "repeat_every",
                                "name" => "repeat_every",
                                "type" => "number",
                                "value" => $model_info->repeat_every ? $model_info->repeat_every : 1,
                                "min" => 1,
                                "class" => "form-control recurring_element",
                                "placeholder" => app_lang('repeat_every'),
                                "data-rule-required" => true,
                                "data-msg-required" => app_lang("field_required")
                            ));
                            ?>
                        </div>
                        <div class="col-md-5 col-xs-6">
                            <?php
                            echo form_dropdown(
                                "repeat_type",
                                array(
                                    "days" => app_lang("interval_days"),
                                    "weeks" => app_lang("interval_weeks"),
                                    "months" => app_lang("interval_months"),
                                    "years" => app_lang("interval_years"),
                                ),
                                $model_info->repeat_type ? $model_info->repeat_type : "months",
                                "class='select2 recurring_element' id='repeat_type'"
                            );
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <label for="no_of_cycles" class=" col-md-3"><?php echo app_lang('cycles'); ?></label>
                        <div class="col-md-4">
                            <?php
                            echo form_input(array(
                                "id" => "no_of_cycles",
                                "name" => "no_of_cycles",
                                "type" => "number",
                                "min" => 1,
                                "value" => $model_info->no_of_cycles ? $model_info->no_of_cycles : "",
                                "class" => "form-control",
                                "placeholder" => app_lang('cycles')
                            ));
                            ?>
                        </div>
                        <div class="col-md-5 mt5">
                            <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('recurring_cycle_instructions'); ?>"><i data-feather="help-circle" class="icon-14"></i></span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-9 ms-auto">
                        <?php echo view("includes/color_plate"); ?>
                    </div>
                </div>
            </div>

            <div class="form-group mb0">
                <div class="col-md-12 row">
                    <?php
                    echo view("includes/file_list", array("files" => $model_info->files));
                    ?>
                </div>
            </div>

            <?php echo view("includes/dropzone_preview"); ?>
        </div>
    </div>

    <div class="modal-footer">
        <?php echo view("includes/upload_button"); ?>
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {



        $("#event-form").appForm({
            onSuccess: function(result) {
                if ($("#event-calendar").length) {
                    window.fullCalendar.refetchEvents();
                    setTimeout(function() {
                        feather.replace();
                    }, 100);
                }

                if (typeof getReminders === 'function') {
                    getReminders();
                }
            }
        });

        setDatePicker("#start_date, #end_date");
        setTimePicker("#start_time, #end_time");

        setTimeout(function() {
            $("#title").focus();
        }, 200);

        $("#event_labels").select2({
            multiple: true,
            data: <?php echo json_encode($label_suggestions); ?>
        });
        $("#event-form .select2").select2();


        <?php if (isset($clients_dropdown)) { ?>
            $("#clients_dropdown").appDropdown({
                list_data: <?php echo $clients_dropdown; ?>,
                onChangeCallback: function(clientId) {
                    //show the specific client contacts readio button after select any client
                    appAjaxRequest({
                        url: "<?php echo_uri("events/get_sharing_options_view") ?>/1",
                        type: 'POST',
                        data: {
                            id: "<?php echo $model_info->id; ?>",
                            client_id: clientId,
                            share_with: "<?php echo $model_info->share_with; ?>",
                        },
                        dataType: 'json',
                        success: function(result) {
                            $("#share_with_container").html(result.sharing_options_view)
                        }
                    });
                }
            });
        <?php } ?>





        //show/hide recurring fields
        $("#event_recurring").click(function() {
            if ($(this).is(":checked")) {
                $("#recurring_fields").removeClass("hide");
            } else {
                $("#recurring_fields").addClass("hide");
            }
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

    });
</script>