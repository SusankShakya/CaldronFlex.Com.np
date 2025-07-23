<?php

$options = $options ?? array();
$share_with = $share_with ?? "";
$client_id = $client_id ?? "";
$id = $id ?? "";
$members_and_teams_dropdown_source_url = $members_and_teams_dropdown_source_url ?? "";
$client_groups_source_url = $client_groups_source_url ?? "";
$client_contacts_of_selected_client_source_url = $client_contacts_of_selected_client_source_url ?? "";

$available_options = array(
    "only_me" => array(
        "label_text" => app_lang('only_me'),
        "field_value" => ""
    ),
    "all_team_members" => array(
        "label_text" => app_lang('all_team_members'),
        "field_value" => "all"
    ),
    "all_members" => array( // this is to support backward compatibility but it's recommended also 
        "label_text" => app_lang('all_team_members'),
        "field_value" => "all_members"
    ),
    "specific_members" => array(
        "source_url" => $members_and_teams_dropdown_source_url,
        "label_text" => app_lang('specific_members'),
        "placeholder_text" => app_lang('choose_members'),
        "value_to_be_matched" => "member"
    ),
    "specific_members_and_teams" => array(
        "show_if_not_checked" => array_key_exists("all_team_members", $options) ? "all_team_members" : "all_members",
        "source_url" => $members_and_teams_dropdown_source_url,
        "label_text" => app_lang('specific_members_and_teams'),
        "placeholder_text" => app_lang('choose_members_and_or_teams'),
        "value_to_be_matched" => "team,member"
    ),
    "all_clients" => array(
        "show_if_not_has_value" => $client_id,
        "label_text" => app_lang('all_clients'),
        "field_value" => "all_clients"
    ),
    "specific_cg" => array( // this is to support backward compatibility
        "show_if_not_checked" => "all_clients",
        "show_if_not_has_value" => $client_id,
        "source_url" => $client_groups_source_url,
        "label_text" => app_lang('specific_client_groups'),
        "placeholder_text" => app_lang('choose_client_groups'),
        "value_to_be_matched" => "cg"
    ),
    "specific_client_groups" => array(
        "show_if_not_checked" => "all_clients",
        "show_if_not_has_value" => $client_id,
        "source_url" => $client_groups_source_url,
        "label_text" => app_lang('specific_client_groups'),
        "placeholder_text" => app_lang('choose_client_groups'),
        "value_to_be_matched" => "client_group"
    ),
    "all_contacts_of_the_client" => array(
        "show_if_has_value" => $client_id,
        "label_text" => app_lang('all_contacts_of_the_client'),
        "field_value" => "all_contacts"
    ),
    "specific_contacts_of_the_client" => array(
        "show_if_not_checked" => "all_contacts_of_the_client",
        "show_if_has_value" => $client_id,
        "source_url" => $client_contacts_of_selected_client_source_url . "/" . ($client_id ? $client_id : "0"),
        "label_text" => app_lang('specific_contacts_of_the_client'),
        "placeholder_text" => app_lang('choose_client_contacts'),
        "value_to_be_matched" => "contact"
    )
);

?>

<div id="sharing-options-container">
    <input type="hidden" id="share_with" name="share_with" value="<?php echo $share_with; ?>" />
</div>

<script type="text/javascript">
    $(document).ready(function() {

        var options = <?php echo json_encode($options); ?>,
            existingShareWithValue = "<?php echo $share_with; ?>",
            existingId = "<?php echo $id; ?>",
            availableOptions = <?php echo json_encode($available_options); ?>;

        function ifAnyValueMatches(input, valueToBeMatched) {
            var patterns = valueToBeMatched.split(/[,\/]/); // Split valueToBeMatched by commas and slashes
            return patterns.some(pattern => input.includes(pattern)); // Check if the input matches any pattern
        }

        function getCheckboxDOM(key, value) {
            var specificDropdownDom = "";
            if (value.source_url) {
                var requiredMessage = "<?php echo app_lang('field_required'); ?>";
                specificDropdownDom = `<div class="specific_dropdown hide">
                    <input type="text" value="" name="specific_${key}" id="specific_${key}" class="w100p" data-rule-required="true" data-msg-required="${requiredMessage}" placeholder="${value.placeholder_text}" />
                </div>`;
            }

            var checked = false;
            if (existingId) {
                if (!existingShareWithValue) { // only me
                    checked = true;
                } else if (value.field_value) { // other selection without any dropdown
                    var existingShareWithArray = existingShareWithValue ? existingShareWithValue.split(',') : [];
                    if (existingShareWithArray.includes(value.field_value)) {
                        checked = true;
                    }
                } else if (!value.field_value && value.value_to_be_matched && ifAnyValueMatches(existingShareWithValue, value.value_to_be_matched)) { // check if this dropdown type matches
                    checked = true;
                }
            }

            var dom = `<div>
                <input type="checkbox" name="${key}" id="${key}" ${checked ? " data-is-checked='1' " : ""} class="form-check-input sharing-option-checkbox" data-show-if-not-checked="${value.show_if_not_checked || ''}" /> 
                <label for="${key}"">${value.label_text}</label>
                ${specificDropdownDom}
            </div>`;

            if (
                !options.includes(key) ||
                (value.hasOwnProperty("show_if_not_has_value") && value.show_if_not_has_value) ||
                (value.hasOwnProperty("show_if_has_value") && (!value.show_if_has_value || value.show_if_has_value === "0" || value.show_if_has_value === "undefined"))
            ) {
                dom = "";
            }

            return dom;
        }

        function prepareShareWithValue() {
            var shareWithValue = "";

            $.each(options, function(index, option) {

                var optionProperties = availableOptions[option];
                if ($("#" + option).closest('div').is(':visible') && $("#" + option).is(":checked")) {

                    if (optionProperties.field_value) {
                        shareWithValue += shareWithValue ? ("," + optionProperties.field_value) : optionProperties.field_value;
                    } else { // find the value from the dropdown options
                        var dropdownValue = $("#specific_" + option).val();
                        shareWithValue += shareWithValue ? ("," + dropdownValue) : dropdownValue;
                    }
                }
            });

            if (!shareWithValue || shareWithValue === "undefined") {
                shareWithValue = "";
            }

            $("#share_with").val(shareWithValue);
        }


        // hide other fields if only me is selected
        function hideThisFieldBecauseOnlyMeIsChecked($checkbox) {
            var isOnlyMeChecked = $("#only_me").is(":checked");
            if (isOnlyMeChecked && $checkbox.attr("id") !== "only_me") {
                $checkbox.closest('div').hide();
                return true;
            }
        }

        function extractOtherValuesExcept(input, valueToBeMatched) {
            valueToBeMatched = valueToBeMatched.replace(/,/g, '|'); // Replace commas (,) with pipes (|)
            var matches = input.match(new RegExp(`(${valueToBeMatched}):\\d+`, 'g'));
            return matches ? matches.join(',') : '';
        }

        function toggleSpecificDropdown($checkbox) {
            var $dropdownContainer = $checkbox.closest('div').find('.specific_dropdown'),
                $dropdown = $dropdownContainer.find("input");

            if (!$checkbox.is(":checked") || !$checkbox.closest('div').is(':visible')) {
                $dropdownContainer.addClass("hide");
                $dropdown.removeClass("validate-hidden");
                return;
            }

            var optionKey = $checkbox.attr("id"),
                optionProperties = availableOptions[optionKey];
            if (!optionProperties.source_url) {
                return;
            }

            appAjaxRequest({
                url: optionProperties.source_url,
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    $dropdownContainer.removeClass("hide");
                    $dropdown.addClass("validate-hidden");
                    $dropdown.select2("destroy");

                    // show existing data
                    if (existingId && existingShareWithValue) {
                        var valueForThisDropdown = extractOtherValuesExcept(existingShareWithValue, optionProperties.value_to_be_matched);
                        $dropdown.val(valueForThisDropdown);
                    }

                    $dropdown.select2({
                        multiple: true,
                        formatResult: teamAndMemberSelect2Format,
                        formatSelection: teamAndMemberSelect2Format,
                        data: result
                    }).on('select2-open change', function(e) {
                        feather.replace();
                    });


                }
            });
        }

        function showHideField($checkbox) {
            var showIfNotChecked = $checkbox.data('show-if-not-checked');
            if (showIfNotChecked) {
                if ($("#" + showIfNotChecked).is(":checked")) {
                    $checkbox.closest('div').hide();
                } else {
                    $checkbox.closest('div').show();
                }
            } else {
                $checkbox.closest('div').show();
            }
        }

        function updateVisibility() {
            $(".sharing-option-checkbox").each(function() {
                if (hideThisFieldBecauseOnlyMeIsChecked($(this))) {
                    return true; // continue
                }

                showHideField($(this));
                toggleSpecificDropdown($(this));
            });
        }

        function checkTheElementForEditMode() {
            $("[data-is-checked='1']").each(function() {
                $(this).prop("checked", true);
            });
        }

        Object.entries(availableOptions).forEach(function([key, value]) {
            var checkboxDOM = getCheckboxDOM(key, value);
            $("#sharing-options-container").append(checkboxDOM);
        });

        $("#sharing-options-container").on('change', '.sharing-option-checkbox, .specific_dropdown', function() {
            if ($(this).is('.sharing-option-checkbox')) {
                updateVisibility();
            }

            prepareShareWithValue();
        });

        checkTheElementForEditMode();
        updateVisibility(); //update the visibility initially
    });
</script>