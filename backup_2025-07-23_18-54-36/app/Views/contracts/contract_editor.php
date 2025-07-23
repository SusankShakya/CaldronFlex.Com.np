<div class="no-border clearfix mb0">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <?php echo form_open(get_uri("contracts/save_view"), array("id" => "contract-editor-form", "class" => "general-form", "role" => "form")); ?>
    <div class="bg-all-white editor-preview pt-4">

        <input type="hidden" name="id" value="<?php echo $contract_info->id; ?>" />

        <div class="skip-dark-editor">
            <div class="clearfix pl5 pr5 pb10 preview-editor-button-group">
                <?php echo modal_anchor(get_uri("contract_templates/insert_template_modal_form"), "<i data-feather='rotate-ccw' class='icon-16'></i> " . app_lang('change_template'), array("class" => "btn btn-default float-start", "title" => app_lang('change_template'))); ?>
                <button type="button" class="btn btn-primary ml10 float-end" id="contract-save-and-show-btn"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save_and_show'); ?></button>
                <button type="submit" class="btn btn-primary float-end"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
            </div>

            <div class=" col-md-12">
                <?php
                echo form_textarea(array(
                    "id" => "contract-view",
                    "name" => "view",
                    "value" => process_images_from_content($contract_info->content, false),
                    "placeholder" => app_lang('view'),
                    "class" => "form-control",
                    "data-toolbar" => "pdf_friendly_toolbar",
                    "data-height" => 0,
                    "data-encode_ajax_post_data" => "1"
                ));
                ?>
            </div>
        </div>

        <div class="p15 pt0"><strong><?php echo app_lang("avilable_variables"); ?></strong>:
            <?php
            $avilable_variables = get_available_contract_variables();
            foreach ($avilable_variables as $variable) {
                echo "<span class='js-variable-tag clickable' data-bs-toggle='tooltip' data-bs-placement='bottom' data-title='" . app_lang('copy') . "' data-after-click-title='" . app_lang('copied') . "' title='" . app_lang('copy') . "'>{" . $variable . "}</span>, ";
            }
            ?></div>

    </div>
    <?php echo form_close(); ?>

</div>

<script>
    $(document).ready(function() {
        $("#contract-editor-form").appForm({
            isModal: false,
            onSuccess: function(response) {
                appAlert.success(response.message, {
                    duration: 10000
                });
            }
        });

        initWYSIWYGEditor("#contract-view");

        $('[data-bs-toggle="tooltip"]').tooltip();

        //insert contract template
        $("body").on("click", "#contract-template-table tr", function() {
            var id = $(this).find(".contract_template-row").attr("data-id");
            appLoader.show({
                container: "#insert-template-section",
                css: "left:0;"
            });

            appAjaxRequest({
                url: "<?php echo get_uri('contract_templates/get_template_data') ?>" + "/" + id,
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    if (result.success) {

                        setWYSIWYGEditorHTML("#contract-view", result.template);

                        //close the modal
                        $("#close-template-modal-btn").trigger("click");
                    } else {
                        appAlert.error(result.message);
                    }
                }
            });

        });
    });
</script>