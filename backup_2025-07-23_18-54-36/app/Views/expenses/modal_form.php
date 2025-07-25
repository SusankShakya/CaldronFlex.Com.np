<?php echo form_open(get_uri("expenses/save"), array("id" => "expense-form", "class" => "general-form", "role" => "form")); ?>
<div id="expense-dropzone" class="post-dropzone">
    <div class="modal-body clearfix">
        <div class="container-fluid">
            <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

            <?php if ($is_clone) { ?>
                <input type="hidden" name="is_clone" value="1" />
            <?php } ?>

            <div class=" form-group">
                <div class="row">
                    <label for="expense_date" class=" col-md-3"><?php echo app_lang('date_of_expense'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "expense_date",
                            "name" => "expense_date",
                            "value" => $model_info->expense_date ? $model_info->expense_date : get_my_local_time("Y-m-d"),
                            "class" => "form-control recurring_element",
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="category_id" class=" col-md-3"><?php echo app_lang('category'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_dropdown("category_id", $categories_dropdown, $model_info->category_id, "class='select2 validate-hidden' id='category_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="title" class=" col-md-3"><?php echo app_lang('amount'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "amount",
                            "name" => "amount",
                            "value" => $model_info->amount ? to_decimal_format($model_info->amount) : "",
                            "class" => "form-control",
                            "placeholder" => app_lang('amount'),
                            "autofocus" => true,
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class=" form-group">
                <div class="row">
                    <label for="title" class=" col-md-3"><?php echo app_lang('title'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "title",
                            "name" => "title",
                            "value" => $model_info->title,
                            "class" => "form-control",
                            "placeholder" => app_lang("title")
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
                            "value" => $model_info->description ? process_images_from_content($model_info->description, false) : "",
                            "class" => "form-control",
                            "placeholder" => app_lang('description'),
                            "data-rich-text-editor" => true
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <?php

            if ($clients_dropdown) { ?>
                <div class="form-group">
                    <div class="row">
                        <label for="client_id" class=" col-md-3"><?php echo app_lang('client'); ?></label>
                        <div class=" col-md-9">
                            <?php
                            echo form_input(array(
                                "id" => "expense_client_id",
                                "name" => "expense_client_id",
                                "value" => $model_info->client_id,
                                "class" => "form-control",
                                "placeholder" => app_lang('client'),
                                "data-roload_dropdown_on_change" => "#expense_project_id",
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>


            <div class="form-group">
                <div class="row">
                    <label for="expense_project_id" class="col-md-3"><?php echo app_lang('project'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "expense_project_id",
                            "name" => "expense_project_id",
                            "value" => $model_info->project_id,
                            "class" => "form-control",
                            "data-source_url" => get_uri("expenses/get_projects_dropdown"),
                            "data-post_field_values_of" => "expense_client_id",
                        ));

                        ?>
                    </div>

                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="expense_user_id" class=" col-md-3"><?php echo app_lang('team_member'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_dropdown("expense_user_id", $members_dropdown, $model_info->user_id, "class='select2 validate-hidden' id='expense_user_id'");
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="tax_id" class=" col-md-3"><?php echo app_lang('tax'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_dropdown("tax_id", $taxes_dropdown, array($model_info->tax_id), "class='select2'");
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="tax_id" class=" col-md-3"><?php echo app_lang('second_tax'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_dropdown("tax_id2", $taxes_dropdown, array($model_info->tax_id2), "class='select2'");
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="expense_recurring" class=" col-md-3"><?php echo app_lang('recurring'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather="help-circle" class="icon-16"></i></span></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_checkbox("recurring", "1", $model_info->recurring ? true : false, "id='expense_recurring' class='form-check-input'");
                        ?>
                    </div>
                </div>
            </div>
            <div id="recurring_fields" class="<?php if (!$model_info->recurring) echo "hide"; ?>">
                <div class="form-group">
                    <div class="row">
                        <label for="repeat_every" class=" col-md-3"><?php echo app_lang('repeat_every'); ?></label>
                        <div class="col-md-4">
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
                        <div class="col-md-5">
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
                            <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('recurring_cycle_instructions'); ?>"><i data-feather="help-circle" class="icon-16"></i></span>
                        </div>
                    </div>
                </div>

                <div class="form-group hide" id="next_recurring_date_container">
                    <div class="row">
                        <label for="next_recurring_date" class=" col-md-3"><?php echo app_lang('next_recurring_date'); ?> </label>
                        <div class=" col-md-9">
                            <?php
                            echo form_input(array(
                                "id" => "next_recurring_date",
                                "name" => "next_recurring_date",
                                "class" => "form-control",
                                "placeholder" => app_lang('next_recurring_date'),
                                "autocomplete" => "off",
                                "data-rule-required" => true,
                                "data-msg-required" => app_lang("field_required"),
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?>

            <?php if ($is_clone) { ?>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <i data-feather="info" class="icon-16"></i> <?php echo app_lang("files_will_not_be_copied"); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="form-group">
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

        $("#expense-form").appForm({
            onSuccess: function(result) {
                if (typeof $EXPENSE_TABLE !== 'undefined') {
                    $EXPENSE_TABLE.appTable({
                        newData: result.data,
                        dataId: result.id
                    });
                } else {
                    location.reload();
                }
            },
            onAjaxSuccess: function(result) {
                if (!result.success && result.next_recurring_date_error) {
                    $("#next_recurring_date").val(result.next_recurring_date_value);
                    $("#next_recurring_date_container").removeClass("hide");

                    $("#expense-form").data("validator").showErrors({
                        "next_recurring_date": result.next_recurring_date_error
                    });
                }
            }
        });

        setDatePicker("#expense_date");

        $("#expense-form .select2").appDropdown();


        <?php if ($clients_dropdown) { ?>
            $("#expense_client_id").appDropdown({
                list_data: <?php echo $clients_dropdown; ?>
            });
        <?php } ?>

        $("#expense_project_id").appDropdown({
            list_data: <?php echo json_encode($projects_dropdown); ?>
        });


        $('[data-bs-toggle="tooltip"]').tooltip();

        //show/hide recurring fields
        $("#expense_recurring").click(function() {
            if ($(this).is(":checked")) {
                $("#recurring_fields").removeClass("hide");
            } else {
                $("#recurring_fields").addClass("hide");
            }
        });

        var dynamicDates = getDynamicDates();
        setDatePicker("#next_recurring_date", {
            startDate: dynamicDates.tomorrow //set min date = tomorrow
        });

    });
</script>