<?php echo form_open(get_uri("automation/save"), array("id" => "automation-form", "class" => "general-form automation-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="related_to" value="<?php echo $model_info->related_to; ?>" />
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
                <label for="event_name" class=" col-md-3"><?php echo app_lang('event'); ?></label>
                <div class=" col-md-9">
                    <?php
                    if ($model_info->id) {
                        echo app_lang($model_info->event_name);
                        echo '<input type="hidden" name="event_name" id="automation_event_name" value="' . $model_info->event_name . '" />';
                    } else {
                        echo form_dropdown("event_name", $automation_events_dropdown, $model_info->event_name, "class='select2 validate-hidden'  data-rule-required='true', data-msg-required='" . app_lang('field_required') . "' id='automation_event_name'");
                    }
                    ?>
                </div>
            </div>
        </div>


        <div id="conditions_and_actions_container">
            <div class="form-group mb15">
                <div class="col-md-12 mb20 mt20">
                    <label for="conditions"><strong><?php echo app_lang('conditions'); ?></strong></label>
                    <div id="matching_type_container" class="inline-block ml20">
                        <?php
                        echo form_radio(array(
                            "id" => "match_all",
                            "name" => "matching_type",
                            "class" => "form-check-input",
                        ), "match_all", $model_info->matching_type != "match_any" ? true : false);
                        ?>
                        <label for="match_all" class="mr15"><?php echo app_lang('match_all'); ?></label>

                        <?php
                        echo form_radio(array(
                            "id" => "match_any",
                            "name" => "matching_type",
                            "class" => "form-check-input",
                        ), "match_any", $model_info->matching_type == "match_any" ? true : false);
                        ?>
                        <label for="match_any" class="mr15"><?php echo app_lang('match_any'); ?></label>
                    </div>
                </div>

                <div class="col-md-12">
                    <div id="conditions_list" class="condition-form clearfix pb10 ml10 mr10 mb20"></div>
                    <?php echo js_anchor("<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_more'), array("id" => "add-more-condition")); ?>
                </div>
            </div>
            <br />

            <div class="form-group mb15">
                <label for="actions" class=" col-md-12"><strong>
                        <?php echo app_lang('actions'); ?>
                    </strong></label>
                <div class="col-md-12">
                    <div class="action-field">
                        <div id="actions_list" class="action-form clearfix pb10 ml10 mr10">
                        </div>
                    </div>

                    <?php echo js_anchor("<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_more'), array("id" => "add-more-action")); ?>

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

<?php
$conditions = $model_info->conditions ? $model_info->conditions : [];
$conditions = json_encode($conditions);

$actions = $model_info->actions ? $model_info->actions : [];
$actions = json_encode($actions);

?>

<script type="text/javascript">
    $(document).ready(function() {
        var Automation = {

            init() {
                this.$conditionsList = $("#conditions_list");
                this.$actionsList = $("#actions_list");
                this.$automationEvent = $("#automation_event_name");


                this.conditionsData = [];
                this.actionsData = [];
                this.numberOfActionsAvailable = 0;

                this.initAppForm();
                this.prepareEditModeForm();
                this.showHideMatchingTypes();
                this.showHideAddCondition();

                this.initClickEvents();
                this.initChangeEvents();


            },
            resetConditionsData() {
                this.conditionsData = [];
            },
            resetActionsData() {
                this.actionsData = [];
                this.numberOfActionsAvailable = 0;
            },
            removeCondtionRow(rowId) {
                delete this.conditionsData[rowId];
            },
            removeActionRow(rowId) {
                delete this.actionsData[rowId];
            },
            setConditionsData(rowId, typeName, value) {
                if (!this.conditionsData[rowId]) {
                    this.conditionsData[rowId] = [];
                }
                this.conditionsData[rowId][typeName] = value;
            },
            setConditionsRowData(rowId, rowData) {
                this.conditionsData[rowId] = rowData;
            },
            setActionsRowData(rowId, rowData) {
                this.actionsData[rowId] = rowData;
            },
            getConditionsData(rowId, typeName) {
                var row = this.conditionsData[rowId];
                if (row) {
                    return this.conditionsData[rowId][typeName];
                }
            },
            setActionsData(rowId, typeName, value) {
                if (!this.actionsData[rowId]) {
                    this.actionsData[rowId] = [];
                }
                this.actionsData[rowId][typeName] = value;
            },
            getActionsData(rowId, typeName) {
                var row = this.actionsData[rowId];
                if (row) {
                    return this.actionsData[rowId][typeName];
                }
            },

            prepareConditionsDataForSubmit(data) {
                var it = this,
                    i = 0;
                it.hasEmptyCondition = false;

                for (key in it.conditionsData) {
                    i++;

                    if (it.conditionsData[key]) {

                        var field_name = it.conditionsData[key]['field_name'],
                            operator = it.conditionsData[key]['operator'];

                        if (!field_name || !operator) {
                            it.hasEmptyCondition = true;
                        }

                        data.push({
                            "name": "field_name_" + i,
                            "value": field_name
                        });
                        data.push({
                            "name": "operator_" + i,
                            "value": operator
                        });
                        data.push({
                            "name": "expected_value_1_" + i,
                            "value": it.conditionsData[key]['expected_value_1']
                        });

                    }
                }

                data.push({
                    "name": "conditions_row_count",
                    "value": i
                });

                return data;
            },
            prepareActionsDataForSubmit(data) {
                var it = this,
                    i = 0;

                for (key in it.actionsData) {
                    i++;

                    if (it.actionsData[key]) {

                        var action = it.actionsData[key]['action'];
                        if (action) {

                            data.push({
                                "name": "action_" + i,
                                "value": action
                            });
                            data.push({
                                "name": "action_value_" + i,
                                "value": it.actionsData[key]['action_value']
                            });
                        }

                    }
                }

                data.push({
                    "name": "actions_row_count",
                    "value": i
                });

                return data;
            },

            prepareEditModeForm() {
                var it = this;
                var conditions = <?php echo $conditions; ?>;
                conditions.forEach(function(row) {
                    var rowId = getRandomAlphabet(6);
                    it.setConditionsRowData(rowId, row);
                    it.$conditionsList.append(it.getConditionRowTemplate(rowId));
                });

                if (conditions.length) {
                    it.setMatchingTypeText();
                }


                var actions = <?php echo $actions; ?>;
                actions.forEach(function(row) {
                    var rowId = getRandomAlphabet(6);
                    it.setActionsRowData(rowId, row);
                    it.$actionsList.append(it.getActionRowTemplate(rowId));
                });

                it.setAvailableActionsNumber();
                it.showHideAddActionButton();


            },
            initAppForm() {
                var it = this;
                $("#automation-form").appForm({
                    beforeAjaxSubmit: function(data, self, options) {

                        data = it.prepareConditionsDataForSubmit(data);
                        data = it.prepareActionsDataForSubmit(data);

                        if (it.hasEmptyCondition) {

                            appAlert.error("<?php echo app_lang('please_input_all_required_fields'); ?>", {
                                container: '.modal-body',
                                animate: false
                            });
                            return false;
                        }
                    },
                    onSuccess: function(result) {
                        $("#automation-table").appTable({
                            newData: result.data,
                            dataId: result.id
                        });
                    }
                });

                setTimeout(function() {
                    $("#title").focus();
                    feather.replace();
                }, 200);
            },
            getConditionRowTemplate(rowId) {
                var data = this.conditionsData[rowId] || {};

                var deleteButton = `<a href="#" class="js-remove-condition-row delete ml10 mt-2" data-id="${rowId}"><i data-feather="x" class="icon-16"></i></a>`;
                return `<div id="${rowId}" class="row js-condition-row">
                            <div class="col-md-11 pt5 pb5 d-flex">
                                <span class="js_matching_type_lable inline-block w50 condition-type-container"></span>
                                <span class="js_condition_field_container">${data.field_name_text ? data.field_name_text : ""}</span>
                                <span class="js_operator_container operator-container">${data.operator_text ? data.operator_text : ""}</span>
                                <span class="js_expected_value_1_container">${data.expected_value_1_text ? data.expected_value_1_text : ""}</span>
                                <span class="js_expected_value_2_container"></span>
                            </div>
                            <div class="col-md-1">
                                ${deleteButton}
                            </div>
                    </div>`;
            },
            getActionRowTemplate(rowId) {
                var data = this.actionsData[rowId] || {};
                var deleteButton = `<a href="#" class="js-remove-action-row delete ml10 mt-2" data-id="${rowId}"><i data-feather="x" class="icon-16"></i></a>`;
                return `<div id="${rowId}" class="row js-action-row">
                            <div class="col-md-11 pt5 pb5">
                                <span class="js_action_field_container">${data.action_text ? data.action_text : ""}</span>
                                <span class="js_action_value_container">${data.action_value_text ? data.action_value_text : ""}</span>
                            </div>
                            <div class="col-md-1">
                                ${deleteButton}
                            </div>
                    </div>`;
            },
            addCondition(event_name) {
                var it = this,
                    url = "<?php echo get_uri('automation/add_condition_row') ?>",
                    data = {
                        event_name: event_name
                    };

                it.addRow(url, data, function(result, rowId) {
                    it.setConditionsData(rowId, "field_name_dropdown", result.field_name_dropdown);
                    it.setConditionsData(rowId, "field_name_text", result.field_name_text);
                    it.setConditionsData(rowId, "operator_text", "<div class='blank-operator-container'><?php echo app_lang('small_letter_is_something') ?></div>");

                    it.$conditionsList.append(it.getConditionRowTemplate(rowId));
                    it.setMatchingTypeText();
                });
            },
            addAction(event_name) {
                var it = this,
                    url = "<?php echo get_uri('automation/add_action_row') ?>",
                    data = {
                        event_name: event_name
                    };

                it.addRow(url, data, function(result, rowId) {
                    it.setActionsData(rowId, "action_dropdown", result.action_dropdown);
                    it.setActionsData(rowId, "action_text", result.action_text);
                    it.$actionsList.append(it.getActionRowTemplate(rowId));
                    it.setAvailableActionsNumber();
                    it.showHideAddActionButton();
                });
            },
            setAvailableActionsNumber() {
                var it = this;
                for (key in it.actionsData) {
                    if (it.actionsData[key]) {
                        var actionDropdown = it.actionsData[key]['action_dropdown'];

                        if (actionDropdown) {
                            it.numberOfActionsAvailable = Object.values(actionDropdown).length;
                        }
                    }
                }
            },
            addRow(url, data, callback) {
                appAjaxRequest({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        var rowId = getRandomAlphabet(6);
                        if (callback) {
                            callback(result, rowId);
                        }
                    }
                });
            },

            showHideMatchingTypes() {
                if (this.$conditionsList.children().length > 1) {
                    $("#matching_type_container").show();
                } else {
                    $("#matching_type_container").hide();
                }
            },
            showHideAddCondition() {
                if (this.getSelectedEvent()) {
                    $("#conditions_and_actions_container").show();
                } else {
                    $("#conditions_and_actions_container").hide();
                }
            },
            showHideAddActionButton() {
                var actionDropdown = [],
                    it = this;

                if (Object.values(it.actionsData).length >= it.numberOfActionsAvailable) {
                    $("#add-more-action").hide();
                } else {
                    $("#add-more-action").show();
                }
            },
            getSelectedEvent() {
                return this.$automationEvent.val();
            },
            setMatchingTypeText() {
                var text_to_show = "<?php echo app_lang('small_letter_and'); ?>";
                if ($("input[name='matching_type']:checked").val() == "match_any") {
                    text_to_show = "<?php echo app_lang('small_letter_or'); ?>";
                }
                $(".js_matching_type_lable:first").html("<?php echo app_lang('if'); ?>");
                $(".js_matching_type_lable").not(':first').html(text_to_show);
                this.showHideMatchingTypes();
            },
            initClickEvents() {
                var it = this;
                $("input[name='matching_type']").on("click", function() {
                    it.setMatchingTypeText();
                });

                $("#add-more-condition").on("click", function() {
                    it.addCondition(it.getSelectedEvent());
                });

                $("#add-more-action").on("click", function() {
                    it.addAction(it.getSelectedEvent());
                });

                it.$conditionsList.on('click', '.js-remove-condition-row', function() {
                    var rowId = $(this).attr("data-id");
                    if (rowId) {
                        it.removeCondtionRow(rowId);
                        $("#" + rowId).remove();
                        it.setMatchingTypeText();
                    }
                });

                it.$actionsList.on('click', '.js-remove-action-row', function() {
                    var rowId = $(this).attr("data-id");
                    if (rowId) {
                        it.removeActionRow(rowId);
                        $("#" + rowId).remove();
                        it.showHideAddActionButton();
                    }
                });

                it.initEditable();
            },

            initEditable() {
                var it = this;
                $("#automation-form").on('click', '[data-act=automation-inline-edit]', function(e) {
                    var $instance = $(this),
                        name = $instance.attr('data-act-name'),
                        value = $instance.attr('data-value'),
                        postData = {},
                        select2Option = {},
                        conditionRowId = $instance.closest(".js-condition-row").attr("id"),
                        actionRowId = $instance.closest(".js-action-row").attr("id"),
                        modifierUrl = '<?php echo_uri("automation/update_condition_row") ?>';

                    postData.event_name = it.getSelectedEvent();
                    postData.field_type = name;

                    if (actionRowId) {
                        select2Option.data = it.getActionsData(actionRowId, name + "_dropdown") || {};
                        modifierUrl = '<?php echo_uri("automation/update_action_row") ?>';
                    } else {
                        select2Option.data = it.getConditionsData(conditionRowId, name + "_dropdown") || {};
                    }


                    if (name == "expected_value_1" || name == "expected_value_2") {
                        postData.selected_operator = it.getConditionsData(conditionRowId, "operator");
                    }

                    if (name == "action_value") {
                        postData.selected_action = it.getActionsData(actionRowId, "action");
                    }

                    $instance.appModifier({
                        actionUrl: modifierUrl,
                        select2Option: select2Option,
                        postData: postData,
                        value: value,
                        onSuccess: function(response, newValue) {

                            $instance.removeClass("empty-input-tag");

                            if (actionRowId) {
                                //actions
                                var $actionsContainer = $instance.closest(".js-action-row");
                                it.setActionsData(actionRowId, postData.field_type, newValue);

                                response.action_value_text ? $actionsContainer.find(".js_action_value_container").html(response.action_value_text) : "";

                                response.action_value_dropdown ? it.setActionsData(actionRowId, "action_value_dropdown", response.action_value_dropdown) : "";

                            } else {
                                //conditions 
                                var $conditionContainer = $instance.closest(".js-condition-row");

                                it.setConditionsData(conditionRowId, postData.field_type, newValue);

                                response.operator ? it.setConditionsData(conditionRowId, "operator", response.operator) : "";

                                response.expected_value_1 ? it.setConditionsData(conditionRowId, "expected_value_1", response.expected_value_1) : "";
                                response.expected_value_2 ? it.setConditionsData(conditionRowId, "expected_value_2", response.expected_value_2) : "";

                                response.operator_text ? $conditionContainer.find(".js_operator_container").html(response.operator_text) : "";
                                response.expected_value_1_text ? $conditionContainer.find(".js_expected_value_1_container").html(response.expected_value_1_text) : "";
                                response.expected_value_2_text ? $conditionContainer.find(".js_expected_value_2_container").html(response.expected_value_2_text) : "";

                                response.operator_dropdown ? it.setConditionsData(conditionRowId, "operator_dropdown", response.operator_dropdown) : "";
                                response.expected_value_1_dropdown ? it.setConditionsData(conditionRowId, "expected_value_1_dropdown", response.expected_value_1_dropdown) : "";

                            }


                        }
                    });

                    return false;
                });
            },
            initChangeEvents() {
                var id = "<?php echo $model_info->id; ?>";
                if (id) return false; //not applicable in edit mode. 

                var it = this;
                it.$automationEvent.select2().on("change", function() {
                    it.$conditionsList.html("");
                    it.$actionsList.html("");
                    it.resetConditionsData();
                    it.resetActionsData();

                    var value = $(this).val();
                    if (value) {
                        it.addCondition(value);
                        it.addAction(value);
                    }
                    it.showHideAddCondition();
                    it.showHideMatchingTypes();
                });

            }
        }

        Automation.init();
    });
</script>