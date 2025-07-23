<div class="no-border clearfix mb0">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <?php echo form_open(get_uri("proposals/save_view"), array("id" => "proposal-editor-form", "class" => "general-form", "role" => "form")); ?>
    <div class="bg-all-white editor-preview pt-4">

        <input type="hidden" name="id" value="<?php echo $proposal_info->id; ?>" />

        <div class="skip-dark-editor">
            <div class="clearfix pl5 pr5 pb10 preview-editor-button-group">
                <?php echo modal_anchor(get_uri("proposal_templates/insert_template_modal_form"), "<i data-feather='rotate-ccw' class='icon-16'></i> " . app_lang('change_template'), array("class" => "btn btn-default float-start", "title" => app_lang('change_template'))); ?>
                <button type="button" class="btn btn-primary ml10 float-end" id="proposal-save-and-show-btn"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save_and_show'); ?></button>
                <button type="submit" class="btn btn-primary float-end"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
            </div>

            <div class=" col-md-12">
                <?php
                echo form_textarea(array(
                    "id" => "proposal-view",
                    "name" => "view",
                    "value" => process_images_from_content($proposal_info->content, false),
                    "placeholder" => app_lang('view'),
                    "class" => "form-control",
                    "data-toolbar" => "pdf_friendly_toolbar",
                    "data-height" => 0,
                    "data-encode_ajax_post_data" => "1",
                    "data-clean_pdf_html" => "1",
                    "data-full_size_image" => 1
                ));
                ?>
            </div>
        </div>

        <div class="pt10"><strong><?php echo app_lang("avilable_variables") . ": "; ?></strong></div>
        <?php
        $available_variable_groups = get_available_proposal_variables();

        foreach ($available_variable_groups as $group => $variables) {
            $last_index = count($variables) - 1;

            echo "<div class='mb10'>";
            foreach ($variables as $index => $variable) {
                echo "<span class='js-variable-tag clickable' data-bs-toggle='tooltip' data-bs-placement='bottom' data-title='" . app_lang('copy') . "' data-after-click-title='" . app_lang('copied') . "' title='" . app_lang('copy') . "'>{" . $variable . "}</span>";
                if ($index !== $last_index) {
                    echo ", ";
                }
            }
            echo "</div>";
        }
        ?>

    </div>
    <?php echo form_close(); ?>

</div>

<script>
    $(document).ready(function() {
        $("#proposal-editor-form").appForm({
            isModal: false,
            onSuccess: function(response) {
                appAlert.success(response.message, {
                    duration: 10000
                });
            }
        });

        initWYSIWYGEditor("#proposal-view");

        //insert proposal template
        $("body").on("click", "#proposal-template-table tr", function() {
            var id = $(this).find(".proposal_template-row").attr("data-id");
            appLoader.show({
                container: "#insert-template-section",
                css: "left:0;"
            });

            appAjaxRequest({
                url: "<?php echo get_uri('proposal_templates/get_template_data') ?>" + "/" + id,
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        setWYSIWYGEditorHTML("#proposal-view", result.template);

                        //close the modal
                        $("#close-template-modal-btn").trigger("click");
                    } else {
                        appAlert.error(result.message);
                    }
                }
            });

        });

        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>