<?php echo form_open(get_uri($controller_slag . "/save_folder_permissions"), array("id" => "folder-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="parent_id" value="<?php echo $model_info->parent_id; ?>" />

        <div class="form-group">
            <div class="row">
                <div class="col-md-12"> <?php echo app_lang('folder_permission_instruction'); ?></div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="title" class=" col-md-3"><?php echo app_lang('full_access'); ?>

                    <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('admin_user_has_all_power'); ?>"><i data-feather="help-circle" class="icon-14"></i></span>
                </label>
                <div class=" col-md-9">
                    <input type="text" value="<?php echo $full_access_value; ?>" name="full_access" id="full_access_options" class="w100p" placeholder="<?php echo app_lang('full_access_placeholder'); ?>"  />
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="title" class=" col-md-3"><?php echo app_lang('upload_and_organize'); ?></label>
                <div class=" col-md-9">
                    <input type="text" value="<?php echo $upload_and_organize_value; ?>" name="upload_and_organize" id="upload_and_organize_options" class="w100p" placeholder="<?php echo app_lang('upload_and_organize'); ?>"  />
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="title" class=" col-md-3"><?php echo app_lang('upload_only'); ?></label>
                <div class=" col-md-9">
                    <input type="text" value="<?php echo $upload_only_value; ?>" name="upload_only" id="upload_only_options" class="w100p" placeholder="<?php echo app_lang('upload_only'); ?>"  />

                </div>
            </div>
        </div>


        <div class="form-group">
            <div class="row">
                <label for="title" class=" col-md-3"><?php echo app_lang('read_only'); ?></label>
                <div class=" col-md-9">
                    <input type="text" value="<?php echo $read_only_value; ?>" name="read_only" id="read_only_options" class="w100p" placeholder="<?php echo app_lang('read_only'); ?>"  />
                </div>
            </div>
        </div>





    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {

        $("#folder-form").appForm({
            onSuccess: function (result) {
                if (result.success) {
                    $("#file-manager-right-panel").html(result.folder_info_content);
                }
            }
        });

        init_permissions_dropdown($("#full_access_options"), <?php echo $full_access_dropdown; ?>);
        init_permissions_dropdown($("#upload_and_organize_options"), <?php echo $upload_and_organize_dropdown; ?>);
        init_permissions_dropdown($("#upload_only_options"), <?php echo $upload_only_dropdown; ?>);
        init_permissions_dropdown($("#read_only_options"), <?php echo $read_only_dropdown; ?>);


        permissionsSelect2Format = function (option) {
            var formatIcons = <?php echo $format_icons; ?>;

            return "<i data-feather='" + formatIcons[option.type] + "' class='icon-16 info'></i> " + option.text;
        };


        function init_permissions_dropdown(container, data) {
            setTimeout(function () {
                container.select2({
                    multiple: true,
                    formatResult: permissionsSelect2Format,
                    formatSelection: permissionsSelect2Format,
                    data: data
                }).on('select2-open change', function (e) {
                    setTimeout(function () {
                        feather.replace();
                    });
                });

                feather.replace();
            });
        }

        $('[data-bs-toggle="tooltip"]').tooltip();

    });
</script>