<?php echo form_open(get_uri("e_invoice_templates/save"), array("id" => "e-invoice-template-form", "class" => "general-form bg-white", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <div id="xml-validation-message">

        </div>
        <div class="row">
            <div class="col-md-9">
                <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            echo form_input(array(
                                "id" => "title",
                                "name" => "title",
                                "value" => $model_info->title,
                                "class" => "form-control",
                                "placeholder" => app_lang('e_invoice_template_title'),
                                "autofocus" => true,
                                "data-rule-required" => true,
                                "data-msg-required" => app_lang("field_required"),
                            ));
                            ?>
                        </div>
                    </div>
                </div>

                <div class="undefined_variables text-danger"></div>

                <div id="xml-editor"></div>

                <div class="form-group d-none">
                    <div class="row">
                        <div class=" col-md-12">
                            <?php
                            echo form_textarea(array(
                                "name" => "template",
                                "id" => "e-invoice-template-textarea",
                                "value" => $model_info->template,
                                "class" => "form-control",
                                "placeholder" => app_lang('e_invoice_template'),
                                "data-rule-required" => true,
                                "data-msg-required" => app_lang("field_required"),
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 e-invoice-template-variables">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div class="b-b pb15"><strong><?php echo app_lang("avilable_variables"); ?></strong></div>
                                </li>
                                <?php foreach ($available_variables as $group_name => $variables) { ?>
                                    <li class="list-group-item pb-0"><strong><?php echo app_lang($group_name); ?>:</strong></li>
                                    <ul>
                                        <?php foreach ($variables as $key => $variable) { ?>
                                            <?php if (is_array($variable)) { ?>
                                                <li class="list-group-item p-0 js-variable-tag clickable" data-bs-toggle="tooltip" data-bs-placement="left" title="<?php echo app_lang('copy'); ?>" data-title="<?php echo app_lang('copy'); ?>" data-after-click-title="<?php echo app_lang('copied'); ?>">{<?php echo $key; ?>}</li>
                                                <ul>
                                                    <?php foreach ($variable as $nested_variable) { ?>
                                                        <li class="list-group-item p-0 js-variable-tag clickable" data-bs-toggle="tooltip" data-bs-placement="left" title="<?php echo app_lang('copy'); ?>" data-title="<?php echo app_lang('copy'); ?>" data-after-click-title="<?php echo app_lang('copied'); ?>">{<?php echo $nested_variable; ?>}</li>
                                                    <?php } ?>
                                                </ul>
                                            <?php } else { ?>
                                                <li class="list-group-item p-0 js-variable-tag clickable" data-bs-toggle="tooltip" data-bs-placement="left" title="<?php echo app_lang('copy'); ?>" data-title="<?php echo app_lang('copy'); ?>" data-after-click-title="<?php echo app_lang('copied'); ?>">{<?php echo $variable; ?>}</li>
                                            <?php } ?>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                                <li class="list-group-item">
                                    <div class="b-t pt15"><i data-feather="info" class="icon-16"></i> <?php echo app_lang('e_invoice_template_custom_field_variable_info'); ?></div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="button" class="btn btn-danger spinning-btn validate-xml-btn"><span data-feather="arrow-right" class="icon-16"></span> <?php echo app_lang('validate_and_save'); ?></button>
    <button type="submit" class="btn btn-primary save-btn"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        var editorInstance; // Global variable for CodeMirror instance

        setTimeout(function() {
            $("#title").focus();
            editorInstance = $("#xml-editor").xmlEditor(); // Initialize and store editor instance

            // editorInstance.on("change", function() {
            //     $(".validate-xml-btn").removeClass("d-none");
            //     //$(".save-btn").addClass("d-none");
            // });
        }, 200);

        $("#e-invoice-template-form").appForm({
            beforeAjaxSubmit: function(data) {
                $.each(data, function(index, obj) {
                    if (obj.name === "template") {
                        data[index]["value"] = editorInstance.getValue();
                    }
                });
            },
            onSuccess: function(result) {
                $("#e-invoice-templates-table").appTable({
                    newData: result.data,
                    dataId: result.id
                });
            }
        });



        $("#e-invoice-template-form .select2").select2();

        var modalHeight = $(window).height(),
            modalInnerHeight = modalHeight - 200;

        $(".e-invoice-template-variables").css("max-height", modalInnerHeight).css("overflow-y", "scroll");

        setTimeout(function() {
            $(".CodeMirror").css("min-height", modalInnerHeight - 65);
        }, 200);

        function getXMLValidationMessage(xmlString) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(xmlString, "application/xml");
            var errorNode = doc.querySelector("parsererror");

            if (!errorNode) return "";

            // Parse the inner HTML into a DOM fragment
            const tempDoc = document.createElement("div");
            tempDoc.innerHTML = errorNode.innerHTML;

            // Replace all <h3> with <div>
            tempDoc.querySelectorAll("h3").forEach(h => {
                const div = document.createElement("div");
                div.innerHTML = h.innerHTML;
                h.replaceWith(div);
            });

            // Remove the last child (the "Below is a rendering..." message)
            if (tempDoc.lastElementChild) {
                tempDoc.removeChild(tempDoc.lastElementChild);
            }

            return tempDoc.innerHTML;
        }

        function getUnknownVariablesMessage(xmlContent) {
            var availableVariables = [],
                unknownVariables = [];

            // Collect all available variables from the page
            $(".js-variable-tag").each(function() {
                var variableText = $(this).text().trim();
                var variableName = variableText.replace(/[{}]/g, ""); // Remove { and }
                availableVariables.push(variableName);
            });

            // 1. Check variables inside { ... } tags
            xmlContent.replace(/\{([^{}]+)\}/g, function(match, contentInsideBraces) {
                // Skip special tags like {endif}, {else}, {elseif}, etc.
                if (/^(\/?if|else(if)?|endif)/.test(contentInsideBraces.trim())) {
                    return;
                }

                // Handle normal variables with optional filters
                var variableName = contentInsideBraces.split('|')[0].trim();

                if (
                    variableName &&
                    !availableVariables.includes(variableName) &&
                    !unknownVariables.includes(variableName)
                ) {
                    unknownVariables.push(variableName);
                }
            });

            // 2. Check variables inside {if $variable ...} conditions
            xmlContent.replace(/\{if\s+([^}]+)\}/g, function(match, conditionContent) {
                // Find all $variables inside condition
                var matches = conditionContent.match(/\$[a-zA-Z0-9_.]+/g);
                if (matches) {
                    matches.forEach(function(fullVar) {
                        var varName = fullVar.replace(/^\$/, ''); // Remove leading $

                        if (
                            varName &&
                            !availableVariables.includes(varName) &&
                            !unknownVariables.includes(varName)
                        ) {
                            unknownVariables.push(varName);
                        }
                    });
                }
            });

            if (unknownVariables.length > 0) {
                return `<div><span class="fw-bold"><?php echo app_lang('undefined_variables'); ?>: </span> ${unknownVariables.map(v => `{${v}}`).join(", ")}</div>`;
            }

            return "";
        }



        $(".validate-xml-btn").click(function() {
            var $button = $(this);

            if (!editorInstance) {
                return;
            }

            var xmlContent = editorInstance.getValue().trim(); // Get XML from CodeMirror

            setTimeout(function() {

                var alertHtml = getXMLValidationMessage(xmlContent);
                alertHtml = alertHtml += getUnknownVariablesMessage(xmlContent);

                if (alertHtml) {
                    appAlert.error(alertHtml, {
                        animate: false,
                        container: "#xml-validation-message"
                    });

                    $(".app-alert-message").css({
                        "max-width": "none"
                    });

                } else {
                    $button.addClass("d-none");
                    $(".save-btn").trigger("click");
                }

            }, 200);
        });

        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    (function($) {
        $.fn.xmlEditor = function(options) {
            var settings = $.extend({
                xmlString: $("#e-invoice-template-textarea").val()
            }, options);

            var container = this;
            var textarea = $('<textarea class="xml-textarea"></textarea>').text(settings.xmlString);
            container.append(textarea);

            // Apply CodeMirror for syntax highlighting
            var editor = CodeMirror.fromTextArea(textarea[0], {
                mode: 'application/xml',
                lineNumbers: true,
                theme: 'material',
                indentUnit: 2,
                matchBrackets: true,
                autoCloseTags: true,
                viewportMargin: Infinity,
                refresh: true
            });

            return editor;
        };
    }(jQuery));
</script>