<?php echo form_open(get_uri("tickets/save"), array("id" => "ticket-form", "class" => "general-form", "role" => "form")); ?>
<div id="new-ticket-dropzone" class="post-dropzone">
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

            <!-- client can't be changed during editing -->
            <?php if ($client_id || $model_info->client_id) { ?>
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>" />
            <?php } else if (!$model_info->creator_email) { ?>
                <div class="form-group">
                    <div class="row">
                        <label for="client_id" class=" col-md-3"><?php echo app_lang('client'); ?></label>
                        <div class="col-md-9">
                            <?php
                            echo form_input(array(
                                "id" => "client_id",
                                "name" => "client_id",
                                "value" => $model_info->client_id,
                                "class" => "form-control",
                                "placeholder" => app_lang('client'),
                                "data-rule-required" => true,
                                "data-msg-required" => app_lang("field_required"),
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if (!$requested_by_id && !$model_info->id) { ?>
                <?php if ($login_user->user_type == "staff") { ?>
                    <div class="form-group">
                        <div class="row">
                            <label for="requested_by_id" class=" col-md-3"><?php echo app_lang('requested_by'); ?></label>
                            <div class="col-md-9" id="requested-by-dropdown-section">
                                <?php
                                if ($project_id || $client_id) {
                                    echo form_dropdown("requested_by_id", $requested_by_dropdown, "", "class='select2'");
                                } else {
                                    echo form_input(array(
                                        "id" => "requested_by_id",
                                        "name" => "requested_by_id",
                                        "value" => $model_info->requested_by,
                                        "class" => "form-control",
                                        "placeholder" => app_lang('requested_by')
                                    ));
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php } else if ($login_user->user_type == "client") { ?>
                    <input type="hidden" name="requested_by_id" value="<?php echo $login_user->id; ?>" />
                <?php } ?>
            <?php } else { ?>
                <input type="hidden" name="requested_by_id" value="<?php echo $requested_by_id; ?>" />
            <?php } ?>

            <?php if ($show_project_reference == "1") { ?>
                <?php if ($project_id) { ?>
                    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
                <?php } else { ?>
                    <div class="form-group">
                        <div class="row">
                            <label for="project_id" class=" col-md-3"><?php echo app_lang('project'); ?></label>
                            <div class="col-md-9" id="porject-dropdown-section">
                                <?php
                                echo form_input(array(
                                    "id" => "project_id",
                                    "name" => "project_id",
                                    "value" => $model_info->project_id,
                                    "class" => "form-control",
                                    "placeholder" => app_lang('project')
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>

            <div class="form-group">
                <div class="row">
                    <label for="ticket_type_id" class=" col-md-3"><?php echo app_lang('ticket_type'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_dropdown("ticket_type_id", $ticket_types_dropdown, $model_info->ticket_type_id, "class='select2'");
                        ?>
                    </div>
                </div>
            </div>

            <?php if (!$model_info->id) { ?>
                <!-- description can't be changed during editing -->
                <div class="form-group">
                    <div class="row">
                        <label for="description" class=" col-md-3"><?php echo app_lang('description'); ?></label>
                        <div class=" col-md-9">
                            <?php
                            echo form_textarea(array(
                                "id" => "description",
                                "name" => "description",
                                "class" => "form-control",
                                "placeholder" => app_lang('description'),
                                "data-rule-required" => true,
                                "data-msg-required" => app_lang("field_required"),
                                "data-rich-text-editor" => true
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- Assign to only visible to team members -->
            <?php if ($login_user->user_type == "staff") { ?>
                <div class="form-group">
                    <div class="row">
                        <label for="assigned_to" class=" col-md-3"><?php echo app_lang('assign_to'); ?></label>
                        <div class="col-md-9">
                            <?php
                            echo form_dropdown("assigned_to", $assigned_to_dropdown, $model_info->assigned_to, "class='select2'");
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <label for="ticket_labels" class=" col-md-3"><?php echo app_lang('labels'); ?></label>
                        <div class=" col-md-9">
                            <?php
                            echo form_input(array(
                                "id" => "ticket_labels",
                                "name" => "labels",
                                "value" => $model_info->labels,
                                "class" => "form-control",
                                "placeholder" => app_lang('labels')
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php echo view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?>

            <?php echo view("includes/dropzone_preview"); ?>
        </div>
    </div>

    <div class="modal-footer">

        <!-- file can't be uploaded during editing -->
        <?php
        if (!$model_info->id) {
            echo view("includes/upload_button");
        }
        ?>

        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>
</div>
<?php echo form_close(); ?>



<script type="text/javascript">
    $(document).ready(function() {

        var editMode = "<?php echo $model_info->id; ?>";

        $("#ticket-form").appForm({
            onSuccess: function(result) {
                appAlert.success(result.message, {
                    duration: 10000
                });
            }
        });
        setTimeout(function() {
            $("#title").focus();
        }, 200);
        $("#ticket-form .select2").select2();

        $("#ticket_labels").select2({
            multiple: true,
            data: <?php echo json_encode($label_suggestions); ?>
        });


        $('#project_id').select2({
            data: <?php echo json_encode($projects_suggestion); ?>
        });


        //load all client contacts of selected client

        $("#client_id").appDropdown({
            list_data: <?php echo $clients_dropdown; ?>,
            onChangeCallback: function(client_id, instance) {
                if (client_id) {
                    $('#requested_by_id').select2("destroy");
                    $("#requested_by_id").hide();
                    appLoader.show({
                        container: "#requested-by-dropdown-section",
                        zIndex: 1
                    });
                    appAjaxRequest({
                        url: "<?php echo get_uri("tickets/get_client_contact_suggestion") ?>" + "/" + client_id,
                        dataType: "json",
                        success: function(result) {
                            $("#requested_by_id").show().val("");
                            $('#requested_by_id').select2({
                                data: result
                            });
                            appLoader.hide();
                        }
                    });

                    <?php if ($show_project_reference == "1") { ?>
                        $('#project_id').select2("destroy");
                        $("#project_id").hide();
                        appLoader.show({
                            container: "#porject-dropdown-section",
                            zIndex: 1
                        });
                        appAjaxRequest({
                            url: "<?php echo get_uri("tickets/get_project_suggestion") ?>" + "/" + client_id,
                            dataType: "json",
                            success: function(result) {
                                $("#project_id").show().val("");
                                $('#project_id').select2({
                                    data: result
                                });
                                appLoader.hide();
                            }
                        });
                    <?php } ?>
                }
            }
        });

        $('#requested_by_id').select2({
            data: <?php echo json_encode($requested_by_dropdown); ?>
        });

        if ("<?php echo $project_id; ?>") {
            $("#client_id").select2("readonly", true);
        }

    });
</script>