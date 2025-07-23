$(document).ready(function () {
    $.ajaxSetup({ cache: false });

    //set locale of moment js
    moment.locale(AppLanugage.locale);
    moment.fn.customFormat = function (format) {
        const year = this.year();
        const month = String(this.month() + 1).padStart(2, '0'); // Months are 0-indexed
        const day = String(this.date()).padStart(2, '0');

        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day);
    };

    //set locale for datepicker
    (function ($) {
        $.fn.datepicker.dates['custom'] = {
            days: AppLanugage.days,
            daysShort: AppLanugage.daysShort,
            daysMin: AppLanugage.daysMin,
            months: AppLanugage.months,
            monthsShort: AppLanugage.monthsShort,
            today: AppLanugage.today
        };
    }(jQuery));

    //set datepicker language

    $('body').on('click', '[data-act=ajax-modal]', function (e) {

        if ($(this).closest("td.all").length > 0) {
            $(this).closest("td.all").trigger("click");
        }

        var data = { ajaxModal: 1 },
            url = $(this).attr('data-action-url'),
            isLargeModal = $(this).attr('data-modal-lg'),
            isFullscreenModal = $(this).attr('data-modal-fullscreen'),
            isCustomBgModal = $(this).attr('data-modal-custom-bg'),
            isCloseModal = $(this).attr('data-modal-close'),
            title = $(this).attr('data-title'),
            modalClass = $(this).attr('data-modal-class');

        if (!url) {
            console.log('Ajax Modal: Set data-action-url!');
            return false;
        }
        if (title) {
            $("#ajaxModalTitle").html(title);
        } else {
            $("#ajaxModalTitle").html($("#ajaxModalTitle").attr('data-title'));
        }

        if ($(this).attr("data-post-hide-header")) {
            $("#ajaxModal .modal-header").addClass("hide");
            $("#ajaxModal .modal-footer").addClass("hide");
        } else {
            $("#ajaxModal .modal-header").removeClass("hide");
            $("#ajaxModal .modal-footer").removeClass("hide");
        }

        $("#ajaxModalContent").html($("#ajaxModalOriginalContent").html());
        $("#ajaxModalContent").find(".original-modal-body").removeClass("original-modal-body").addClass("modal-body");
        $("#ajaxModal").modal('show');
        $("#ajaxModal").find(".modal-dialog").removeClass("custom-modal-lg");
        $("#ajaxModal").find(".modal-dialog").removeClass("modal-fullscreen");
        $("#ajaxModal").find(".modal-dialog").removeClass("custom-bg-modal");
        $("#ajaxModal").removeClass("global-search-modal");

        var existingModalClass = $("#ajaxModal").find(".modal-dialog").attr("data-modal-class");
        if (existingModalClass) {
            $("#ajaxModal").find(".modal-dialog").removeClass(existingModalClass);
        }

        $(this).each(function () {
            $.each(this.attributes, function () {
                if (this.specified && this.name.match("^data-post-")) {
                    var dataName = this.name.replace("data-post-", "");
                    data[dataName] = this.value;
                }
            });
        });
        ajaxModalXhr = $.ajax({
            url: url,
            data: data,
            cache: false,
            type: 'POST',
            success: function (response) {
                $("#ajaxModal").find(".modal-dialog").removeClass("mini-modal");
                if (isLargeModal === "1") {
                    $("#ajaxModal").find(".modal-dialog").addClass("custom-modal-lg");
                } else if (isFullscreenModal === "1") {
                    $("#ajaxModal").find(".modal-dialog").addClass("modal-fullscreen");
                }

                if (isCloseModal === "1") {
                    $("#ajaxModal").addClass("global-search-modal");
                }

                if (isCustomBgModal === "1") {
                    $("#ajaxModal").find(".modal-dialog").addClass("custom-bg-modal");
                }

                if (modalClass) {
                    $("#ajaxModal").find(".modal-dialog").addClass(modalClass).attr("data-modal-class", modalClass);
                }

                $("#ajaxModalContent").html(response);

                initAllNotEmptyWYSIWYGEditors(true, $("#ajaxModalContent"));
                setModalScrollbar();

                for (let i = 0; i < 5; i++) {
                    setTimeout(function () {
                        $modalBody = $("#ajaxModalContent").find(".modal-body");
                        if ($modalBody.length && $modalBody.data("scrollbar-added") != "1") {
                            setModalScrollbar();
                        }
                    }, 100 * (i + 1));
                }

                feather.replace();
            },
            statusCode: {
                403: function () {
                    console.log("403: Session expired.");
                    location.reload();
                },
                404: function () {
                    $("#ajaxModalContent").find('.modal-body').html("");
                    appAlert.error("404: Page not found.", { container: '.modal-body', animate: false });
                }
            },
            error: function () {
                $("#ajaxModalContent").find('.modal-body').html("");
                appAlert.error(AppLanugage.somethingWentWrong, { container: '.modal-body', animate: false });
            }
        });
        return false;
    });

    //abort ajax request on modal close.
    $('#ajaxModal').on('hidden.bs.modal', function (e) {
        if (e.target && e.target.id === 'ajaxModal') {
            ajaxModalXhr.abort();
            $("#ajaxModal").find(".modal-dialog").removeClass("modal-lg");
            $("#ajaxModal").find(".modal-dialog").addClass("modal-lg");

            $("#ajaxModalContent").html("");
        }
    });

    //common ajax request
    $('body').on('click show.bs.dropdown', '[data-act=ajax-request]', function () {

        if ($(this).closest("td.all").length > 0) {
            $(this).closest("td.all").trigger("click");
        }

        var data = {},
            $selector = $(this),
            url = $selector.attr('data-action-url'),
            removeOnSuccess = $selector.attr('data-remove-on-success'),
            removeOnClick = $selector.attr('data-remove-on-click'),
            fadeOutOnSuccess = $selector.attr('data-fade-out-on-success'),
            fadeOutOnClick = $selector.attr('data-fade-out-on-click'),
            inlineLoader = $selector.attr('data-inline-loader'),
            targetLoader = $selector.attr('data-target-loader'),
            reloadOnSuccess = $selector.attr('data-reload-on-success'),
            showResponse = $selector.attr('data-show-response'),
            successCallbackFunction = $selector.attr("data-success-callback"),
            contentType = $selector.attr("data-content-type");


        var $target = "";
        if ($selector.attr('data-real-target')) {
            $target = $($selector.attr('data-real-target'));
        } else if ($selector.attr('data-closest-target')) {
            $target = $selector.closest($selector.attr('data-closest-target'));
        }

        if (!url) {
            console.log('Ajax Request: Set data-action-url!');
            return false;
        }

        //remove the target element
        if (removeOnClick && $(removeOnClick).length) {
            $(removeOnClick).remove();
        }

        //remove the target element with fade out effect
        if (fadeOutOnClick && $(fadeOutOnClick).length) {
            $(fadeOutOnClick).fadeOut(function () {
                $(this).remove();
            });
        }

        var headers = {};

        $selector.each(function () {
            $.each(this.attributes, function () {
                if (this.specified && this.name.match("^data-post-")) {
                    var dataName = this.name.replace("data-post-", "");
                    data[dataName] = this.value;
                }

                if (this.specified && this.name.match("^data-header-")) {
                    var headerName = this.name.replace("data-header-", "");
                    headers[headerName] = this.value;
                }
            });
        });


        if (inlineLoader === "1") {
            $selector.addClass("spinning");
        } else if (targetLoader === "1") {
            appLoader.show({ container: $target });
        } else {
            appLoader.show();
        }

        var ajaxOptions = {
            url: url,
            data: data,
            cache: false,
            type: 'POST',
            headers: headers,
            success: function (response) {
                if (inlineLoader === "1") {
                    $selector.removeClass("spinning");
                }

                if (successCallbackFunction && typeof window[successCallbackFunction] != 'undefined') {
                    window[successCallbackFunction](response, $selector);
                }

                if (showResponse && response) {
                    if (response.success) {
                        if (response.message) {
                            appAlert.success(response.message, { duration: 10000 });
                        }

                        if (reloadOnSuccess) {
                            location.reload();
                        }

                    } else {
                        appAlert.error(response.message);
                    }
                } else if (reloadOnSuccess) {
                    location.reload();
                }

                //remove the target element
                if (removeOnSuccess && $(removeOnSuccess).length) {
                    $(removeOnSuccess).remove();
                }

                //remove the target element with fade out effect
                if (fadeOutOnSuccess && $(fadeOutOnSuccess).length) {
                    $(fadeOutOnSuccess).fadeOut(function () {
                        $(this).remove();
                    });
                }

                //trigger ajaxRequestHooks
                var group = $selector.attr("data-request-group");
                if (group && window.ajaxRequestHooks && window.ajaxRequestHooks[group]) {

                    window.ajaxRequestHooks[group].forEach(function (hook) {
                        if (typeof hook.onSuccess === 'function') {
                            hook.onSuccess(data);
                        }
                    });
                }

                appLoader.hide();
                if ($target.length) {
                    if ($selector.attr("data-append")) {
                        $selector.remove();
                        $target.append(response);
                    } else {
                        $target.html(response);
                    }
                }
            },
            statusCode: {
                404: function () {
                    appLoader.hide();
                    appAlert.error("404: Page not found.");
                }
            },
            error: function () {
                appLoader.hide();
                appAlert.error(AppLanugage.somethingWentWrong);
            }
        };

        if (showResponse) {
            ajaxOptions.dataType = 'json';
        }

        if (contentType == "application/json") {
            ajaxOptions.contentType = contentType;
            ajaxOptions.data = JSON.stringify(ajaxOptions.data);
        }

        ajaxRequestXhr = $.ajax(ajaxOptions);

    });

    //bind ajax tab
    $('body').on('click', '[data-bs-toggle="ajax-tab"] a', function () {
        var $this = $(this),
            loadurl = $this.attr('href'),
            target = $this.attr('data-bs-target');
        if (!target)
            return false;

        if ($this.attr("data-reload")) {
            //remove data first if it's need to reload everytime
            $(target).html("");
        }

        if ($(target).html() === "" || $this.attr("data-reload")) {
            appLoader.show({ container: target, css: "right:50%; bottom:auto;" });

            appAjaxRequest({
                url: loadurl,
                cache: false,
                type: 'GET',
                success: function (response) {
                    $(target).html(response);
                    feather.replace();
                    selectLastlySelectedTab(target);
                },
                statusCode: {
                    403: function () {
                        console.log("403: Session expired.");
                        location.reload();
                    },
                    404: function () {
                        appLoader.hide();
                        appAlert.error("404: Page not found.");
                    }
                },
                error: function () {
                    appLoader.hide();
                    appAlert.error(AppLanugage.somethingWentWrong);
                }
            });

            //            $.get(loadurl, function (data, test, test2) {
            //                $(target).html(data);
            //                feather.replace();
            //                selectLastlySelectedTab(target);
            //            });
        }
        $this.tab('show');
        return false;
    });

    selectLastlySelectedTab();

    $('body').on('click', '[data-toggle="app-modal"]', function () {
        var sidebar = true;

        if ($(this).attr("data-sidebar") === "0") {
            sidebar = false;
        }
        appContentModal.init({ url: $(this).attr("data-url"), content_url: $(this).attr("data-content_url"), sidebar: sidebar, sourceElement: $(this) });
        return false;
    });




    //prepare common delete confimation
    var recordDeleteHandler = function (result, $target) {
        var callbackFunction = $target.attr("data-success-callback");

        if (callbackFunction && typeof window[callbackFunction] != 'undefined') {
            window[callbackFunction](result, $target);

            if (result.message) {
                appAlert.warning(result.message, { duration: 20000 });
            }

        }
    };

    var linkDeleteConfirmationHandler = function (e) {
        deleteConfirmationHandler(e, recordDeleteHandler);
    };


    //bind the delete confimation modal which links are not in tables. because there is an another logic for datatable.
    $('body').on('click', 'a[data-action=delete-confirmation]:not(table a)', linkDeleteConfirmationHandler);

});


function delayAction(callback, ms) {
    var timer = 0;
    return function () {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}



//select the lastly selected ajax-tab automatically if it exists user-wise
function selectLastlySelectedTab(target) {
    if (!target) {
        target = "";
    }

    $(target + " [data-bs-toggle='ajax-tab']").each(function () {
        var $this = $(this),
            tabList = $this.attr("id"),
            lastTab = getCookie("user_" + AppHelper.userId + "_" + tabList),
            $specificTab = $this.find("[data-bs-target='" + lastTab + "']");

        if ($this.attr("data-do-not-save-state") == "1") {
            lastTab = null;
        }

        if (lastTab && $specificTab.attr("data-bs-target")) {
            setTimeout(function () {
                $specificTab.trigger("click");
            }, 50);
        } else {
            //load first tab
            $this.find("a").first().trigger("click");
        }
    });
}

var registerNewHook = function (referenceId, onSuccess, hookVarName, hookType, contextId) {
    if (!referenceId || typeof onSuccess !== 'function') {
        return false;
    }

    if (!window[hookVarName]) {
        window[hookVarName] = {};
    }

    if (!window[hookVarName][referenceId]) {
        window[hookVarName][referenceId] = [];
    }

    // Remove the existing hook if it matches hookType and contextId
    if (hookType && contextId) {
        window[hookVarName][referenceId] = window[hookVarName][referenceId].filter(function (hook) {
            return !(hook.hookType === hookType && hook.contextId === contextId);
        });
    }

    window[hookVarName][referenceId].push({
        onSuccess: onSuccess,
        hookType: hookType,
        contextId: contextId
    });
}

var registerAppFormHook = function (formId, onSuccess, hookType, contextId) {
    registerNewHook(formId, onSuccess, "appFormHooks", hookType, contextId);
};

var registerAjaxRequestHook = function (groupId, onSuccess, hookType, contextId) {
    registerNewHook(groupId, onSuccess, "ajaxRequestHooks", hookType, contextId);
};

var registerAppModifierHook = function (groupId, onSuccess, hookType, contextId) {
    registerNewHook(groupId, onSuccess, "appModifierHooks", hookType, contextId);
};

var registerAppTableRowUpdateHook = function (tableId, onSuccess, hookType, contextId) {
    registerNewHook(tableId, onSuccess, "appTableRowUpdateHook", hookType, contextId);
};
var registerAppTableRowDeleteHook = function (tableId, onSuccess, hookType, contextId) {
    registerNewHook(tableId, onSuccess, "appTableRowDeleteHook", hookType, contextId);
};


//custom app form controller
(function ($) {
    $.fn.appForm = function (options) {

        var defaults = {
            ajaxSubmit: true,
            isModal: true,
            closeModalOnSuccess: true,
            dataType: "json",
            showLoader: true,
            onModalClose: function () {
            },
            onSuccess: function () {
            },
            onError: function () {
                return true;
            },
            onSubmit: function () {
            },
            onAjaxSuccess: function () {
            },
            beforeAjaxSubmit: function (data, self, options) {
            }
        };
        var settings = $.extend({}, defaults, options);
        this.each(function () {
            if (settings.ajaxSubmit) {
                validateForm($(this), function (form) {
                    settings.onSubmit();


                    if (settings.isModal) {
                        maskModal($("#ajaxModalContent").find(".modal-body"));
                    } else {
                        $(form).find('[type="submit"]').attr('disabled', 'disabled');
                    }

                    //set empty value to all textarea, if they are empty
                    if (AppHelper.settings.enableRichTextEditor === "1") {
                        $("textarea").each(function () {
                            var $instance = $(this);
                            if ($instance.attr("data-rich-text-editor")) {
                                if ($instance.val() === '<p><br></p>') {
                                    $instance.val('');
                                }
                            }
                        });
                    }

                    $(form).ajaxSubmit({
                        dataType: settings.dataType,
                        beforeSubmit: function (data, self, options) {

                            //Modified \assets\js\jquery-validation\jquery.form.js #1178.
                            //Added data  a.push({name: n, value: v, type: el.type, required: el.required, data: $(el).data()});

                            //to set the convertDateFormat with the input fields, we used the setDatePicker function.
                            //it is the easiest way to regognize the date fields.
                            var removeIndexes = [],
                                checkboxes = {};

                            $.each(data, function (index, obj) {

                                if (obj.data && obj.data.encode_ajax_post_data == "1") {
                                    //data[index]["value"] = encodeAjaxPostData(getWYSIWYGEditorHTML($(form).find('[name="'+obj.name+'"]')));
                                    data[index]["value"] = encodeAjaxPostData(obj.value);
                                }

                                if (obj.data && obj.data.convertDateFormat && obj.value) {
                                    data[index]["value"] = convertDateToYMD(obj.value);
                                }

                                // Replace the current value with the comma-separated values
                                if (obj.data && obj.data.prepare_checkboxes_data == "1") {

                                    if (!checkboxes[obj.name]) {
                                        checkboxes[obj.name] = obj;
                                    } else {
                                        checkboxes[obj.name].value += checkboxes[obj.name].value ? ", " + obj.value : obj.value ? obj.value : "";
                                    }

                                    removeIndexes.push(index);
                                }
                            });

                            Object.keys(checkboxes).forEach(checkoxKey => {
                                data.push(checkboxes[checkoxKey]);
                            });

                            if (removeIndexes.length > 0) {
                                data = data.filter(function (obj, index) {
                                    // Return true to keep the field, false to remove it
                                    if (removeIndexes.includes(index)) {
                                        return false;
                                    } else {
                                        return true;
                                    }
                                });
                            }

                            if (!settings.isModal && settings.showLoader) {
                                appLoader.show({ container: form, css: "top:2%; right:46%;" });
                            }

                            var callbackResult = settings.beforeAjaxSubmit(data, self, options);

                            if (callbackResult === false) {
                                unmaskModal();
                                return false;
                            }

                            self.data('app_post_data', data);

                        },
                        success: function (result, statusText, xhr, $form) {
                            settings.onAjaxSuccess(result);

                            if (result.success) {
                                settings.onSuccess(result);
                                if (settings.isModal && settings.closeModalOnSuccess) {
                                    closeAjaxModal(true);
                                }

                                if (!settings.isModal) {
                                    $(form).find("textarea").each(function () {
                                        if ($(this).attr("data-rich-text-editor") != undefined && $(this).attr("data-keep-rich-text-editor-after-submit") == undefined) {
                                            destroyWYSIWYGEditor($(this))
                                        }
                                    });
                                }

                                //trigger appFormHooks
                                if ($form && window.appFormHooks) {
                                    var formId = $(form).attr('id');
                                    if (formId && window.appFormHooks[formId]) {
                                        var formPostData = {};
                                        $.each($form.serializeArray(), function () {
                                            formPostData[this.name] = this.value;
                                        });

                                        window.appFormHooks[formId].forEach(function (hook) {
                                            if (typeof hook.onSuccess === 'function') {
                                                hook.onSuccess(formPostData, result);
                                            }
                                        });

                                    }
                                }

                                appLoader.hide();
                            } else {
                                if (settings.onError(result)) {
                                    if (settings.isModal) {
                                        unmaskModal();
                                        if (result.message) {
                                            appAlert.error(result.message, { container: '.modal-body', animate: false });
                                        }
                                    } else if (result.message) {
                                        appAlert.error(result.message);
                                    }
                                }
                            }

                            $(form).find('[type="submit"]').removeAttr('disabled');
                        }
                    });
                });
            } else {
                validateForm($(this));
            }
        });
        /*
         * @form : the form we want to validate;
         * @customSubmit : execute custom js function insted of form submission. 
         * don't pass the 2nd parameter for regular form submission
         */

        function convertDateToYMD(date) {
            if (date) {
                var dateFormat = AppHelper.settings.dateFormat || "Y.m.d",
                    dateFormat = dateFormat.toLowerCase(),
                    separator = dateFormat.charAt("1"),
                    dateFormatArray = dateFormat.split(separator),
                    yearIndex = 0,
                    monthIndex = 1,
                    dayIndex = 2;

                if (dateFormatArray[1] === "y") {
                    yearIndex = 1;
                } else if (dateFormatArray[2] === "y") {
                    yearIndex = 2;
                }

                if (dateFormatArray[0] === "m") {
                    monthIndex = 0;
                } else if (dateFormatArray[2] === "m") {
                    monthIndex = 2;
                }

                if (dateFormatArray[0] === "d") {
                    dayIndex = 0;
                } else if (dateFormatArray[1] === "d") {
                    dayIndex = 1;
                }

                var dateValue = date.split(separator);

                return dateValue[yearIndex] + "-" + dateValue[monthIndex] + "-" + dateValue[dayIndex];
            }

        }

        function validateForm(form, customSubmit) {
            //add custom method
            $.validator.addMethod("greaterThanOrEqual",
                function (value, element, params) {
                    var paramsVal = params;
                    if (params && (params.indexOf("#") === 0 || params.indexOf(".") === 0)) {
                        paramsVal = $(params).val();
                    }

                    if (typeof $(element).attr("data-rule-required") === 'undefined' && !value) {
                        return true;
                    }

                    if (!/Invalid|NaN/.test(new Date(convertDateToYMD(value)))) {
                        return !paramsVal || (new Date(convertDateToYMD(value)) >= new Date(convertDateToYMD(paramsVal)));
                    }
                    return isNaN(value) && isNaN(paramsVal)
                        || (Number(value) >= Number(paramsVal));
                }, 'Must be greater than {0}.');

            //add custom method
            $.validator.addMethod("greaterThan",
                function (value, element, params) {
                    var paramsVal = params;
                    if (params && (params.indexOf("#") === 0 || params.indexOf(".") === 0)) {
                        paramsVal = $(params).val();
                    }
                    if (!/Invalid|NaN/.test(new Number(value))) {
                        return new Number((value)) > new Number((paramsVal));
                    }
                    return isNaN(value) && isNaN(paramsVal)
                        || (Number(value) > Number(paramsVal));
                }, 'Must be greater than.');

            //add custom method
            $.validator.addMethod("mustBeSameYear",
                function (value, element, params) {
                    var paramsVal = params;
                    if (params && (params.indexOf("#") === 0 || params.indexOf(".") === 0)) {
                        paramsVal = $(params).val();
                    }
                    if (!/Invalid|NaN/.test(new Date(convertDateToYMD(value)))) {
                        var dateA = new Date(convertDateToYMD(value)), dateB = new Date(convertDateToYMD(paramsVal));
                        return (dateA && dateB && dateA.getFullYear() === dateB.getFullYear());
                    }
                }, 'The year must be same for both dates.');

            $(form).validate({
                submitHandler: function (form) {
                    if (customSubmit) {
                        customSubmit(form);
                    } else {
                        return true;
                    }
                },
                highlight: function (element) {
                    $(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-error');
                },
                errorElement: 'span',
                errorClass: 'help-block',
                ignore: ":hidden:not(.validate-hidden)",
                errorPlacement: function (error, element) {
                    if (element.parent('.input-group').length) {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                }
            });
            //handeling the hidden field validation like select2
            $(".validate-hidden").click(function () {
                $(this).closest('.form-group').removeClass('has-error').find(".help-block").hide();
            });
        }

        //show loadig mask on modal before form submission;
        function maskModal($maskTarget) {
            var padding = $maskTarget.height() - 80;
            if (padding > 0) {
                padding = Math.floor(padding / 2);
            }
            $maskTarget.after("<div class='modal-mask'><div class='circle-loader'></div></div>");
            //check scrollbar
            var height = $maskTarget.outerHeight();
            $('.modal-mask').css({ "width": $maskTarget.width() + 22 + "px", "height": height + "px", "padding-top": padding + "px" });
            $maskTarget.closest('.modal-dialog').find('[type="submit"]').attr('disabled', 'disabled');
            $maskTarget.addClass("hide");
        }

        //remove loadig mask from modal
        function unmaskModal() {
            var $maskTarget = $(".modal-body").removeClass("hide");
            $maskTarget.closest('.modal-dialog').find('[type="submit"]').removeAttr('disabled');
            $maskTarget.removeClass("hide");
            $(".modal-mask").remove();
        }

        //colse ajax modal and show success check mark
        function closeAjaxModal(success) {
            if (success) {
                $(".modal-mask").html("<div class='circle-done'><i data-feather='check' stroke-width='5'></i></div>");
                setTimeout(function () {
                    $(".modal-mask").find('.circle-done').addClass('ok');
                }, 30);
            }
            setTimeout(function () {
                $(".modal-mask").remove();
                $("#ajaxModal").modal('toggle');
                settings.onModalClose();
            }, 1000);
        }


        this.closeModal = function () {
            closeAjaxModal(true);
        };

        return this;
    };
})(jQuery);


(function ($) {

    $.fn.appDropdown = function (options) {
        var defaults = {
            list_data: [],
            showListAfterSearch: false
        };

        if (options === 'destroy') {
            return this.each(function () {
                var $selector = $(this);
                if ($selector.data("select2")) {
                    $selector.select2("destroy");
                }
            });
        }

        var settings = $.extend({}, defaults, options);

        return this.each(function () {
            var $selector = $(this);
            var select2Options = {
                placeholder: $selector.attr('placeholder')
            };

            if (settings.list_data && settings.list_data[0]) {
                var firstItem = settings.list_data[0];
                if (firstItem.dropdown_type == "MAX_DROPDOWN_ITEMS_REACHED" && firstItem.source_url) {
                    select2Options.minimumInputLength = 2;
                    select2Options.query = function (query) {
                        clearTimeout(this._t);
                        this._t = setTimeout(function () {
                            appAjaxRequest({
                                url: firstItem.source_url,
                                type: 'POST',
                                dataType: 'json',
                                data: { search: query.term, blank_option_text: firstItem.blank_option_text },
                                success: function (data) {
                                    query.callback({ results: data });
                                }
                            });
                        }, 350); // delay of 350ms
                    }

                    select2Options.initSelection = function (element, callback) {
                        //set the selected value in edit mode.
                        if ($selector.val()) {
                            appAjaxRequest({
                                url: firstItem.source_url,
                                type: 'POST',
                                dataType: 'json',
                                data: { id: $selector.val() },
                                success: function (response) {
                                    if (response && response[0] && response[0].id == $selector.val() && response[0].text) {
                                        callback({ id: response[0].id, text: response[0].text });
                                    }
                                }
                            });
                        } else if (firstItem && firstItem.blank_option_text) {
                            callback({ id: "", text: firstItem.blank_option_text });
                        }
                    }

                    select2Options.formatInputTooShort = function () { return AppLanugage.enterMinimum2characters }
                    // select2Options.formatNoMatches = function () { return "Nothing found"; }
                    // select2Options.formatSearching = function () { return "Looking..."; }
                    // select2Options.escapeMarkup = function (m) {
                    //     return m; // prevent escaping the returned strings
                    // }
                }
            }

            if ($selector.is("select")) {
                //in mobile, don't show the search option if options length is less than 20
                if (isMobile()) {
                    $selector.find("option").length < 20 ? select2Options.minimumResultsForSearch = -1 : "";
                }

                if (settings.onChangeCallback) {
                    $selector.select2(select2Options).on("change", function () {
                        var instance = $(this);
                        settings.onChangeCallback(instance.val(), instance);
                    });
                } else {
                    $selector.select2(select2Options);
                }

            } else if ($selector.is("input")) {


                if (settings.multiple) {
                    $selector.data("multiple", 1);
                    select2Options.multiple = true;
                } else if ($selector.data("multiple") == 1) {
                    select2Options.multiple = true;
                }

                if (settings.canCreateTags) {
                    select2Options.tags = settings.list_data;
                } else if ($selector.data("can-create-tags") == 1) {
                    select2Options.tags = settings.list_data;
                } else {
                    select2Options.data = settings.list_data;
                }

                if (settings.escapeMarkup) {
                    select2Options.escapeMarkup = settings.escapeMarkup;
                }

                //in mobile, don't show the search option if options length is less than 20
                if (isMobile() && select2Options.data) {
                    select2Options.data.length < 20 ? select2Options.minimumResultsForSearch = -1 : "";
                }

                var selectorData = $selector.data();

                $selector.select2(select2Options).on("change", function () {
                    // If there are dependent dropdowns to reload on change
                    if (selectorData.roload_dropdown_on_change) {
                        var elements = selectorData.roload_dropdown_on_change.split(",");

                        elements.forEach(function (element) {
                            var $element = $(element);

                            if ($element.length) {
                                var elementData = $element.data();

                                if (elementData.source_url) {
                                    var data = {};

                                    // If post_field_values_of is specified, prepare data for the AJAX request
                                    if (elementData.post_field_values_of) {
                                        var postFieldValuesOf = elementData.post_field_values_of.split(",");

                                        postFieldValuesOf.forEach(function (fieldName) {
                                            data[fieldName] = $("[name='" + fieldName + "']").val();
                                        });
                                    }

                                    // Destroy existing select2 if initialized and clear value
                                    if ($element.data("select2")) {
                                        $element.select2("destroy").val("");
                                    }

                                    appAjaxRequest({
                                        url: elementData.source_url,
                                        type: 'POST',
                                        data: data,
                                        dataType: 'json',
                                        success: function (newListData) {
                                            var elementOptions = {
                                                list_data: newListData
                                            };
                                            if ($element.data("multiple") == 1) {
                                                elementOptions.multiple = true;
                                            }

                                            if ($element.data("can-create-tags") == 1) {
                                                elementOptions.canCreateTags = true;
                                            }

                                            $element.appDropdown(elementOptions);
                                        }
                                    });
                                }
                            }
                        });
                    }

                    if (settings.onChangeCallback) {
                        var instance = $(this);
                        settings.onChangeCallback(instance.val(), instance);
                    }

                });
            }
        });
    };

})(jQuery);


function getWeekRange(date) {
    //set first and last day of week
    if (!date)
        date = moment().customFormat("YYYY-MM-DD");

    var dayOfWeek = moment(date).format("E"),
        diff = dayOfWeek - AppHelper.settings.firstDayOfWeek,
        range = {};

    if (diff < 7) {
        range.firstDateOfWeek = moment(date).subtract(diff, 'days').customFormat("YYYY-MM-DD");
    } else {
        range.firstDateOfWeek = moment(date).customFormat("YYYY-MM-DD");
    }

    if (diff < 0) {
        range.firstDateOfWeek = moment(range.firstDateOfWeek).subtract(7, 'days').customFormat("YYYY-MM-DD");
    }

    range.lastDateOfWeek = moment(range.firstDateOfWeek).add(6, 'days').customFormat("YYYY-MM-DD");
    return range;
};

//find saved filter
function getFilterInfo(filterId) {
    var filterInfo = null;
    $.each(AppHelper.settings.filters || [], function (index, filter) {
        if (filterId === filter.id) {
            filterInfo = filter;
        }
    });
    return filterInfo;
}

//always check the getContextFilterInfo to apply filter 
function getContextFilterInfo(filterId, settings) {
    var filterInfo = getFilterInfo(filterId),
        context = settings.smartFilterIdentity,
        context_id = settings.contextMeta ? settings.contextMeta.contextId : "";
    if ((filterInfo && context) && (filterInfo.context !== context && filterInfo.context !== context + "_" + context_id)) {
        filterInfo = null; // context doesn't matched 
    }
    return filterInfo;
}


function getDynamicDateRanges() {
    return {
        'today': [moment().customFormat("YYYY-MM-DD"), moment().customFormat("YYYY-MM-DD")],
        'yesterday': [moment().subtract(1, 'days').customFormat("YYYY-MM-DD"), moment().subtract(1, 'days').customFormat("YYYY-MM-DD")],
        'tomorrow': [moment().add(1, 'days').customFormat("YYYY-MM-DD"), moment().add(1, 'days').customFormat("YYYY-MM-DD")],
        'last_7_days': [moment().subtract(6, 'days').customFormat("YYYY-MM-DD"), moment().customFormat("YYYY-MM-DD")],
        'next_7_days': [moment().customFormat("YYYY-MM-DD"), moment().add(6, 'days').customFormat("YYYY-MM-DD")],
        'last_30_days': [moment().subtract(29, 'days').customFormat("YYYY-MM-DD"), moment().customFormat("YYYY-MM-DD")],
        'this_month': [moment().startOf('month').customFormat("YYYY-MM-DD"), moment().endOf('month').customFormat("YYYY-MM-DD")],
        'last_month': [moment().subtract(1, 'month').startOf('month').customFormat("YYYY-MM-DD"), moment().subtract(1, 'month').endOf('month').customFormat("YYYY-MM-DD")],
        'next_month': [moment().add(1, 'month').startOf('month').customFormat("YYYY-MM-DD"), moment().add(1, 'month').endOf('month').customFormat("YYYY-MM-DD")],
        'this_year': [moment().startOf('year').customFormat("YYYY-MM-DD"), moment().endOf('year').customFormat("YYYY-MM-DD")],
        'next_year': [moment().add(1, 'year').startOf('year').customFormat("YYYY-MM-DD"), moment().add(1, 'year').endOf('year').customFormat("YYYY-MM-DD")],
        'last_year': [moment().subtract(1, 'year').startOf('year').customFormat("YYYY-MM-DD"), moment().subtract(1, 'year').endOf('year').customFormat("YYYY-MM-DD")]
    };

}

function getDynamicDates() {
    return {
        'today': moment().customFormat("YYYY-MM-DD"),
        'yesterday': moment().subtract(1, 'days').customFormat("YYYY-MM-DD"),
        'tomorrow': moment().add(1, 'days').customFormat("YYYY-MM-DD"),
        'in_last_2_days': moment().subtract(2, 'days').customFormat("YYYY-MM-DD"),
        'in_last_7_days': moment().subtract(7, 'days').customFormat("YYYY-MM-DD"),
        'in_last_15_days': moment().subtract(15, 'days').customFormat("YYYY-MM-DD"),
        'in_next_7_days': moment().add(7, 'days').customFormat("YYYY-MM-DD"),
        'in_next_15_days': moment().add(15, 'days').customFormat("YYYY-MM-DD"),
        'in_last_30_days': moment().add(30, 'days').customFormat("YYYY-MM-DD"),
        'in_last_1_month': moment().subtract(1, 'months').customFormat("YYYY-MM-DD"),
        'in_last_3_months': moment().subtract(3, 'months').customFormat("YYYY-MM-DD"),
        'start_of_month': moment().startOf('month').customFormat("YYYY-MM-DD"),
        'end_of_month': moment().endOf('month').customFormat("YYYY-MM-DD"),
        'start_of_year': moment().startOf('year').customFormat("YYYY-MM-DD"),
        'end_of_year': moment().endOf('year').customFormat("YYYY-MM-DD")
    };

}


function getContextFilters(settings) {
    var filters = [],
        context = settings.smartFilterIdentity,
        context_id = settings.contextMeta ? settings.contextMeta.contextId : "";

    var context_with_id = "";
    if (context_id) {
        context_with_id = context + "_" + context_id;
    }

    if (context) {
        $.each(AppHelper.settings.filters || [], function (index, filter) {
            if (filter.context === context || filter.context === context_with_id) {
                filters.push(filter);
            }
        });
    }

    filters.sort(function (a, b) {
        var fa = a.title.toLowerCase(),
            fb = b.title.toLowerCase();

        if (fa < fb) {
            return -1;
        }
        if (fa > fb) {
            return 1;
        }
        return 0;
    });

    return filters;
}


class DefaultFilters {
    constructor(settings) {
        this.settings = settings;
        this.init();
        return this.settings;
    }
    init() {
        var filterId = getFilterIdFromCookie(this.settings);
        if (filterId && this.settings.stateSave && !this.settings.ignoreSavedFilter && getContextFilterInfo(filterId, this.settings)) {
            this.initSelectedFilter(filterId);

        } else {
            this.prepareDefaultDateRangeFilterParams();
            this.prepareDefaultCheckBoxFilterParams();
            this.prepareDefaultMultiSelectilterParams();
            this.prepareDefaultRadioFilterParams();
            this.prepareDefaultDropdownFilterParams();
            this.prepareDefaultrSingleDatepickerFilterParams();
            this.prepareDefaultrRngeDatepickerFilterParams();
            this.prepareDefaultRangeRadioButtonsFilterParams();
            this.prepareDefaultDynamicRangeFilterParams();
        }
    }
    initSelectedFilter(filterId) {
        if (filterId) {
            var filterParams = {};
            var filterInfo = getContextFilterInfo(filterId, this.settings);
            if (filterInfo) {
                filterParams = cloneDeep(filterInfo.params);
            }

            this.settings.filterParams = cloneDeep(filterParams);
            this.applyInitialFilterHook();

        }
    }
    applyInitialFilterHook() {
        //if you need to modify any selected filter, you can do that here
        var dynamicFilterName = "";

        $.each(this.settings.filterParams, function (paramName, value) {
            //for dynamic filter, we have to set the value at the end of this process.
            //otherwise the range value could be overwritten on the next loop. 
            if (value == "dynamic") {
                dynamicFilterName = paramName;
            }

        });

        if (dynamicFilterName && this.settings.filterParams && this.settings.filterParams[dynamicFilterName + "_dynamic"]) {

            var defaultRanges = getDynamicDateRanges();
            var dynamicRangeName = this.settings.filterParams[dynamicFilterName + "_dynamic"];

            if (defaultRanges && defaultRanges[dynamicRangeName]) {

                //todo: add a common fuction to set start_date and end_date
                this.settings.filterParams.start_date = defaultRanges[dynamicRangeName][0];
                this.settings.filterParams.end_date = defaultRanges[dynamicRangeName][1];
            }
        }

    }
    prepareDefaultDateRangeFilterParams(dateRangeType) {
        var settings = this.settings;
        if (!dateRangeType) {
            dateRangeType = settings.dateRangeType;
        }

        if (dateRangeType === "daily") {
            settings.filterParams.start_date = moment().customFormat(settings._inputDateFormat);
            settings.filterParams.end_date = settings.filterParams.start_date;
        } else if (dateRangeType === "monthly") {
            var daysInMonth = moment().daysInMonth(),
                yearMonth = moment().customFormat("YYYY-MM");
            settings.filterParams.start_date = yearMonth + "-01";
            settings.filterParams.end_date = yearMonth + "-" + daysInMonth;
        } else if (dateRangeType === "yearly") {
            var year = moment().customFormat("YYYY");
            settings.filterParams.start_date = year + "-01-01";
            settings.filterParams.end_date = year + "-12-31";
        } else if (dateRangeType === "weekly") {
            var range = getWeekRange();
            settings.filterParams.start_date = range.firstDateOfWeek;
            settings.filterParams.end_date = range.lastDateOfWeek;
        }
        this.settings = settings;
    }
    prepareDefaultCheckBoxFilterParams() {
        var settings = this.settings;
        var values = [],
            name = "";
        $.each(settings.checkBoxes, function (index, option) {
            name = option.name;
            if (option.isChecked) {
                values.push(option.value);
            }
        });
        settings.filterParams[name] = values;
        this.settings = settings;
    }
    prepareDefaultMultiSelectilterParams() {
        var settings = this.settings;
        $.each(settings.multiSelect, function (index, option) {
            var saveSelection = option.saveSelection,
                selections = getCookie(option.name);

            var values = [];

            if (saveSelection && selections) {
                selections = selections.split("-");
                values = selections;
            } else {
                $.each(option.options, function (index, listOption) {
                    if (listOption.isChecked) {
                        values.push(listOption.value);
                    }
                });
            }

            settings.filterParams[option.name] = values;
        });

        this.settings = settings;
    }
    prepareDefaultRadioFilterParams() {
        var settings = this.settings;
        $.each(settings.radioButtons, function (index, option) {
            if (option.isChecked) {
                settings.filterParams[option.name] = option.value;
            }
        });
        this.settings = settings;
    }
    prepareDefaultDropdownFilterParams() {
        var settings = this.settings;
        $.each(settings.filterDropdown || [], function (index, dropdown) {
            if (dropdown.value) {
                settings.filterParams[dropdown.name] = dropdown.value;
            } else {
                $.each(dropdown.options, function (index, option) {
                    if (option.isSelected) {
                        settings.filterParams[dropdown.name] = option.id;
                    }
                });
            }
        });
        this.settings = settings;
    }
    prepareDefaultrSingleDatepickerFilterParams() {
        var settings = this.settings;
        $.each(settings.singleDatepicker || [], function (index, datepicker) {
            $.each(datepicker.options || [], function (index, option) {
                if (option.isSelected) {
                    settings.filterParams[datepicker.name] = option.value;
                }
            });
        });
        this.settings = settings;
    }
    prepareDefaultrRngeDatepickerFilterParams() {

        var settings = this.settings;
        $.each(settings.rangeDatepicker || [], function (index, datepicker) {

            if (datepicker.startDate && datepicker.startDate.value) {
                settings.filterParams[datepicker.startDate.name] = datepicker.startDate.value;
            }

            if (datepicker.startDate && datepicker.endDate.value) {
                settings.filterParams[datepicker.endDate.name] = datepicker.endDate.value;
            }

        });
        this.settings = settings;
    }

    prepareDefaultRangeRadioButtonsFilterParams() {
        var settings = this.settings;
        var it = this;
        $.each(settings.rangeRadioButtons || [], function (index, option) {
            if (option.selectedOption) {
                //remove it.. settings.dateRangeType = option.selectedOption;
                settings.filterParams[option.name] = option.selectedOption;

                it.prepareDefaultDateRangeFilterParams(option.selectedOption);
            }
        });
        this.settings = settings;
    }

    prepareDefaultDynamicRangeFilterParams() {
        var settings = this.settings;
        $.each(settings.rangeRadioButtons || [], function (index, option) {
            if (option.dynamicRanges) {
                var filterName = option.name + "_dynamic";
                settings.filterParams[filterName] = option.selectedDynamicRange || option.dynamicRanges[0];
            }
        });
        this.settings = settings;
    }

}

var prepareDefaultFilters = function (settings) {
    var filters = new DefaultFilters(settings);
    return filters;
};

function cloneDeep(value) {
    if (typeof value !== 'object' || value === null) {
        return value;
    }

    let clone;

    if (Array.isArray(value)) {
        clone = [];
        for (let i = 0; i < value.length; i++) {
            clone[i] = cloneDeep(value[i]);
        }
    } else {
        clone = {};
        for (let key in value) {
            if (value.hasOwnProperty(key)) {
                clone[key] = cloneDeep(value[key]);
            }
        }
    }

    return clone;
}

function getFilterIdFromCookie(settings) {
    var userId = AppHelper.userId ? AppHelper.userId : "public";
    return getCookie("filter_" + settings.smartFilterIdentity + "_" + userId);
}


class BuildFilters {
    constructor(settings, $instanceWrapper, $instance) {
        this.leftFilterSectionClsss = ".filter-section-left";
        this.rightFilterSectionClsss = ".filter-section-right";
        this.filterFormClass = ".filter-form";
        this.settings = settings;
        this.$instanceWrapper = $instanceWrapper;
        this.$instance = $instance;
        this.randomId = getRandomAlphabet(5);
        this.filterElements = []; // [paramName] = {setValue: function()}
        this.activeFilterId = "";
        this.state = "new_filter"; //new_filter/change_filter
    }
    init() {
        this.prepareSurchOption();
        //this.prepareCollapsePannelButton();
        this.prepareReloadButton();
        this.prepareSmartFilterDropdown();
        this.prepareFilterFormShowButton();
        this.prepareBookmarkFilterButtons();
        this.hideFilterForm();

        this.prepareDropdownFilters();
        this.prepareDateRangePicker();
        this.prepareDatePickerFilter();
        this.prepareSingleDatePicker();
        this.prepareMultiselectFilter();
        this.prepareCheckboxFilter();
        this.prepareRadioFilter();

        this.prepareRangeRadioButtons();

        this.prepareSaveFilterButton();
        this.prepareCancelFilterFormButton();

        this.initActiveFilterFromCookie();

        this.prepareSelectionHandler();

        if (!window.Filters) {
            window.Filters = [];
        }

        window.Filters[this.settings.smartFilterIdentity] = this;
    }
    saveSelectedFilter() {
        var userId = AppHelper.userId ? AppHelper.userId : "public";
        setCookie("filter_" + this.settings.smartFilterIdentity + "_" + userId, this.activeFilterId);
    }
    initActiveFilterFromCookie() {
        if (this.settings.stateSave && !this.settings.ignoreSavedFilter) {
            var filterId = getFilterIdFromCookie(this.settings);

            if (filterId) {
                var filterInfo = getContextFilterInfo(filterId, this.settings);
                if (filterInfo) {
                    this.activeFilterId = filterId;
                    this.applySelectedFilter(filterId, false);
                }
            }
        }
    }
    reloadInstance() {
        if (this.$instance.is("table")) {
            this.$instance.appTable({ reload: true, filterParams: this.settings.filterParams });
        } else {
            this.$instance.appFilters({ reload: true, filterParams: this.settings.filterParams });

            //Reset selection after reload
            var targetSelector = this.settings.targetSelector;
            $(targetSelector).trigger("reset-selection-menu");
        }
    }

    prepareSelectionHandler() {

        var it = this;

        var selectionHandler = it.settings.selectionHandler;
        if (!selectionHandler) return false;

        var viewType = "kanban",
            $selectionMenuWrapper = null,
            $itemWrapper = null;

        if (it.$instanceWrapper.hasClass("dataTables_wrapper")) {
            viewType = "table";
            $selectionMenuWrapper = it.$instanceWrapper;
            $itemWrapper = it.$instanceWrapper.find(".dataTable");
        }

        if (viewType == "kanban") {
            $selectionMenuWrapper = it.$instance;
            $itemWrapper = $(it.settings.targetSelector);
        }

        var hideButton = it.settings.selectionHandler.hideButton;
        var hideButtonClass = "";
        if (hideButton) {
            hideButtonClass = "hide";
        }

        var dropdown = "<div class='dropdown btn-group mr5 hidden-xs'>"
            + "<button class='btn btn-default dropdown-toggle selection-handler-dropdown-btn " + hideButtonClass + "' type='button' data-bs-toggle='dropdown' aria-expanded='true' data-view_type='" + viewType + "'>"
            + "<i data-feather='crosshair' class='icon-16'></i>"
            + "</button>"
            + "<ul class='dropdown-menu' role='menu'>";

        var postData = selectionHandler.postData;
        var dataPost = '';
        if (postData) {
            $.each(postData, function (key, value) {
                dataPost = 'data-post-' + key + '="' + value + '" ';
            });
        }

        dropdown += "<li role='presentation'><a href='#' class='dropdown-item select-all-btn'>" + AppLanugage.selectAll + "</a></li>";
        dropdown += "<li role='presentation'><a href='#' class='dropdown-item select-specific-btn'>" + AppLanugage.selectSpecific + "</a></li>";

        if (it.settings.selectionHandler.batchUpdateUrl) {
            dropdown += "<li role='presentation'><a class='dropdown-item batch-update-btn hide' data-act='ajax-modal' data-action-url='' " + dataPost + " data-title='" + AppLanugage.batchUpdate + "' type='button'>" + AppLanugage.batchUpdate + "</a></li>";
        }

        if (it.settings.selectionHandler.batchDownloadUrl) {
            dropdown += "<li role='presentation'><a class='dropdown-item download-selected-btn hide' data-action-url='' data-title='" + AppLanugage.downloadSelectedItems + "' type='button'>" + AppLanugage.downloadSelectedItems + "</a></li>";
        }

        if (it.settings.selectionHandler.batchDeleteUrl) {
            dropdown += "<li role='presentation'><a class='dropdown-item delete-selected-btn hide' data-action-url='' data-title='" + AppLanugage.deleteSelectedItems + "' type='button' data-action = 'delete-confirmation' data-reload-on-success = 'true'>" + AppLanugage.deleteSelectedItems + "</a></li>";
        }

        dropdown += "<li role='presentation'><a href='#' class='dropdown-item clear-selection-btn hide'>" + AppLanugage.clearSelection + "</a></li>";

        dropdown += "</ul></div>";


        it.$instanceWrapper.find(".filter-section-right").prepend(dropdown);

        var batchIds = [];
        var isSelectionMode = false;

        $(".select-specific-btn").on("click", function () {
            toggleSelectionMode($itemWrapper);
        });

        var toggleSelectionMode = function ($container, enable = true) {
            if (enable) {
                $container.addClass("js-selection-mode");
                disableLinks($container);
                isSelectionMode = true;
            } else {
                $container.removeClass("js-selection-mode");
                enableLinks($container);
                isSelectionMode = false;
            }
        }

        var disableLinks = function ($container) {
            if ($container.hasClass('dataTable')) {
                $container.find("a").addClass("pe-none");
            } else {
                $container.find('[data-act="ajax-modal"]').attr('data-act', 'ajax-modal-disabled');
            }
            $container.find(".selection-pe-none").addClass("pe-none");
        }

        var enableLinks = function ($container) {
            if ($container.hasClass('dataTable')) {
                $container.find("a").removeClass("pe-none");
            } else {
                $container.find('[data-act="ajax-modal-disabled"]').attr('data-act', 'ajax-modal');
            }
            $container.find(".selection-pe-none").removeClass("pe-none");
        }

        $selectionMenuWrapper.find(".select-all-btn").on("click", function () {
            toggleSelectionMode($itemWrapper);

            var $items;
            if ($itemWrapper.hasClass('dataTable')) {
                $items = $itemWrapper.find("tbody tr");
            } else {
                $items = $itemWrapper.find(".kanban-item");
            }

            $items.each(function () {
                $(this).addClass("batch-operation-selected");

                if ($itemWrapper.hasClass('dataTable')) {
                    var id = $(this).find(".js-selection-id").data("id");
                } else {
                    var id = $(this).data("id");
                }

                if (!batchIds.includes(id)) {
                    batchIds.push(id);
                }
            });

            it.updateSelection($selectionMenuWrapper, $itemWrapper, batchIds);
            $selectionMenuWrapper.find(".clear-selection-btn").removeClass("hide");
        });

        $itemWrapper.on('click', 'tbody tr', function () {
            var $item = $(this);
            var id = $item.find(".js-selection-id").data("id");

            handelSelectSpecific($item, id, $selectionMenuWrapper, $itemWrapper);
        });

        $itemWrapper.on('click', '.kanban-item', function () {
            var $item = $(this);
            var id = $item.data("id");

            handelSelectSpecific($item, id, $selectionMenuWrapper, $itemWrapper);
        });

        var handelSelectSpecific = function ($item, id, $selectionMenuWrapper, $itemWrapper) {
            if (!isSelectionMode) return false;

            if ($.inArray(id, batchIds) !== -1) {
                var index = batchIds.indexOf(id);
                batchIds.splice(index, 1);
                $item.removeClass("batch-operation-selected");
            } else {
                batchIds.push(id);
                $item.addClass("batch-operation-selected");
            }

            it.updateSelection($selectionMenuWrapper, $itemWrapper, batchIds);
            $selectionMenuWrapper.find(".clear-selection-btn").removeClass("hide");
        }

        $selectionMenuWrapper.find(".clear-selection-btn").on("click", function () {
            handelClearSelection();
        });

        $itemWrapper.on('reset-selection-menu', function () {
            handelClearSelection();
        });

        var handelClearSelection = function () {
            toggleSelectionMode($itemWrapper, false);

            var $items;
            if ($itemWrapper.hasClass('dataTable')) {
                $items = $itemWrapper.find("tbody tr");
            } else {
                $items = $itemWrapper.find(".kanban-item");
            }

            $items.each(function () {
                $(this).removeClass("batch-operation-selected");

                if ($itemWrapper.hasClass('dataTable')) {
                    var id = $(this).find(".js-selection-id").data("id");
                } else {
                    var id = $(this).data("id");
                }

                batchIds = batchIds.filter(batchId => batchId !== id);
            });

            $selectionMenuWrapper.find(".clear-selection-btn").addClass("hide");
            it.updateSelection($selectionMenuWrapper, $itemWrapper, batchIds);
        }

    }

    updateSelection($selectionMenuWrapper, $itemWrapper, batchIds) {
        var it = this;

        if (batchIds.length) {
            $selectionMenuWrapper.find(".batch-update-btn").removeClass("hide");
            $selectionMenuWrapper.find(".delete-selected-btn").removeClass("hide");
            $selectionMenuWrapper.find(".selection-handler-dropdown-btn").addClass("active");
        } else {
            $selectionMenuWrapper.find(".batch-update-btn").addClass("hide");
            $selectionMenuWrapper.find(".delete-selected-btn").addClass("hide");
            $selectionMenuWrapper.find(".selection-handler-dropdown-btn").removeClass("active");
        }

        var $items;
        if ($itemWrapper.hasClass('dataTable')) {
            $items = $itemWrapper.find("tbody tr");
        } else {
            $items = $itemWrapper.find(".kanban-item");
        }

        if ($items.length === batchIds.length && batchIds.length !== 0) {
            $selectionMenuWrapper.find(".select-all-btn").addClass("hide");
        } else {
            $selectionMenuWrapper.find(".select-all-btn").removeClass("hide");
        }

        if (batchIds.length === 0) {
            $selectionMenuWrapper.find(".select-specific-btn").removeClass("hide");
        } else {
            $selectionMenuWrapper.find(".select-specific-btn").addClass("hide");
        }

        var serializedIds = batchIds.join("-");

        var batchUpdateUrl = it.settings.selectionHandler.batchUpdateUrl;
        if (batchUpdateUrl) {
            $selectionMenuWrapper.find(".batch-update-btn").attr("data-action-url", batchUpdateUrl).attr("data-post-ids", serializedIds);
        }

        var batchDeleteUrl = it.settings.selectionHandler.batchDeleteUrl;
        if (batchDeleteUrl) {
            $selectionMenuWrapper.find(".delete-selected-btn").attr("data-action-url", batchDeleteUrl).attr("data-post-ids", serializedIds);
        }

    }

    prepareSmartFilterDropdown() {
        if (this.settings.smartFilterIdentity) {

            var it = this;


            var dataPostAttrs = " data-post-context='" + it.settings.smartFilterIdentity + "' "
                + " data-post-instance_id='" + it.getInstanceId() + "' ";

            if (it.getContextId()) {
                dataPostAttrs += " data-post-context_id= '" + it.getContextId() + "' ";
            }


            var actionUrl = AppHelper.baseUrl + "index.php/filters/manage_modal/" + it.settings.smartFilterIdentity;

            var dropdown = "<div class='dropdown-menu w300'>"
                + '<div class="pb10 pl10">'
                + '<a class="inline-block btn btn-default manage-filters-button" data-act="ajax-modal" data-title="' + AppLanugage.manageFilters + '" ' + dataPostAttrs + '  type="button" data-action-url="' + actionUrl + '" ><i data-feather="tool" class="icon-16 mr5"></i>' + AppLanugage.manageFilters + ' </a>'
                + '<a class="inline-block btn btn-default clear-filter-button ml10 hide" href="#"><i data-feather="delete" class="icon-16 mr5"></i>' + AppLanugage.clear + '</a></div>'
                + '<input type="text" class="form-control search-filter" placeholder="' + AppLanugage.search + '">'
                + '<div class="dropdown-divider"></div>'
                + "<ul class='list-group smart-filter-list-group'></ul>"
                + "</div>";

            var smartFilterDropdownDom = '<div class="filter-item-box smart-filter-dropdown-box">'
                + '<div class="dropdown smart-filter-dropdown-container">'
                + '<button class="btn btn-default smart-filter-dropdown dropdown-toggle caret" type="button" data-bs-toggle="dropdown" aria-expanded="true"></button>'
                + dropdown
                + '</div>'
                + '</div>';

            this.$instanceWrapper.find(it.leftFilterSectionClsss).append(smartFilterDropdownDom);
            this.refreshFilterDropdown();

            this.$instanceWrapper.find(".smart-filter-dropdown-container").on('click', '.smart-filter-item', function () {
                var data = $(this).data() || {},
                    filterId = data.id;
                it.state = "new_filter";
                it.applySelectedFilter(filterId);
            });

            var $dropdownSearch = this.$instanceWrapper.find(".search-filter");
            var $dropdown = this.$instanceWrapper.find(".smart-filter-dropdown-container");

            var addScrollOnDropdown = function () {
                var $listGroup = it.$instanceWrapper.find('.smart-filter-list-group');
                var $target = it.$instanceWrapper.find(".smart-filter-item.active");
                if (it.$instanceWrapper.find(".smart-filter-item:visible").length > 6) {
                    $listGroup.css({ "overflow-y": "scroll", "height": "270px" });
                    var targetTop = $target.offset() ? $target.offset().top : 0;
                    var listGroupTop = $listGroup.offset() ? $listGroup.offset().top : 0;

                    if ((targetTop - listGroupTop) > $listGroup.height()) {
                        $listGroup.scrollTop(targetTop - listGroupTop);
                    }
                } else {
                    $listGroup.css({ "overflow-y": "scroll", "height": "auto" });
                }
            };

            $dropdown.on("show.bs.dropdown", function () {
                setTimeout(function () {
                    addScrollOnDropdown();
                    $dropdownSearch.val("").focus();
                    if (!it.$instanceWrapper.find(".smart-filter-item.active").length) {
                        it.$instanceWrapper.find(".smart-filter-item").first().addClass("active");
                    }
                });

            });


            $dropdownSearch.on("input", function (e) {
                var $dropdownItems = it.$instanceWrapper.find(".smart-filter-item");
                var searchTerm = $(this).val().toLowerCase();
                var hasActive = false;
                $dropdownItems.each(function () {
                    var itemText = $(this).html().toLowerCase(),
                        removeActive = true;
                    if (itemText.includes(searchTerm)) {
                        $(this).parent().removeClass("hide");
                        if (!hasActive) {
                            $(this).addClass("active");
                            hasActive = true;
                            removeActive = false;
                        }
                    } else {
                        $(this).parent().addClass("hide");
                    }
                    if (removeActive) {
                        $(this).removeClass("active");
                    }

                });
                addScrollOnDropdown();
            });

            $dropdownSearch.on("keydown", function (e) {
                var $activeDropdown = it.$instanceWrapper.find(".smart-filter-item.active");

                if (e.keyCode === 40) { // Arrow Down
                    e.preventDefault();
                    if ($activeDropdown.parent().nextAll(":visible").length) {
                        $activeDropdown.removeClass("active");
                        $activeDropdown = $activeDropdown.parent().nextAll(":visible").first().find("a").addClass("active");
                    }
                } else if (e.keyCode === 38) { // Arrow Up
                    e.preventDefault();
                    if ($activeDropdown.parent().prevAll(":visible").length) {
                        $activeDropdown.removeClass("active");
                        $activeDropdown = $activeDropdown.parent().prevAll(":visible").first().find("a").addClass("active");
                    }

                } else if (e.keyCode === 13) { // Enter
                    e.preventDefault();
                    it.$instanceWrapper.find(".smart-filter-item.active").trigger("click");
                    $dropdown.dropdown("toggle");
                }

                var $listGroup = it.$instanceWrapper.find('.smart-filter-list-group');

                if ($activeDropdown.length && ($activeDropdown.offset().top + $activeDropdown.outerHeight() - $listGroup.offset().top) > $listGroup.height()) {
                    $listGroup.scrollTop($listGroup.scrollTop() + $activeDropdown.outerHeight());
                } else if ($activeDropdown.length && ($activeDropdown.offset().top - $listGroup.offset().top) < 0) {
                    $listGroup.scrollTop($listGroup.scrollTop() - $activeDropdown.outerHeight());
                }

            });

            this.$instanceWrapper.find(".clear-filter-button").click(function () {
                it.activeFilterId = "";
                it.clearAllFilters();
                it.refreshFilterDropdown();
                it.reloadInstance();
                it.saveSelectedFilter();
            });
        }
    }
    initChangeFilter(filterId) {
        this.activeFilterId = filterId;
        this.showFilterForm();
        this.state = "change_filter";
        this.applySelectedFilter(filterId);
    }
    applySelectedFilter(filterId, reload = true) {
        var it = this;


        if (filterId) {
            it.activeFilterId = filterId;

            var filterParams = [];

            var filterInfo = getContextFilterInfo(filterId, this.settings);
            it.settings.filterParams = cloneDeep(filterInfo.params);
            filterParams = cloneDeep(filterInfo.params);

            //set filter values
            var hasFilter = [];

            var shiftDynamicToEnd = function (obj) {
                var newObj = {};
                var dynamicKey = Object.keys(obj).find(key => obj[key] === "dynamic");
                var dynamicRangName = "";
                for (var key in obj) {
                    if (obj.hasOwnProperty(key) && key !== dynamicKey) {
                        newObj[key] = obj[key];
                    }

                    if (obj.hasOwnProperty(key) && key.endsWith('_dynamic')) {
                        dynamicRangName = key.slice(0, -8);
                    }

                }

                if (dynamicKey) {
                    newObj[dynamicKey] = obj[dynamicKey];
                }

                //remove the dynamic key if the value is not dynamic. 

                if (dynamicRangName && obj[dynamicRangName] != "dynamic") {
                    delete newObj[dynamicRangName + "_dynamic"];
                }

                return newObj;
            }

            //if there is any dynamic fiter, we should call that at the end
            filterParams = shiftDynamicToEnd(filterParams);


            $.each(filterParams, function (paramName, value) {
                hasFilter.push(paramName);
                var filterMap = it.filterElements[paramName];
                if (filterMap) {
                    filterMap.setValue(value, cloneDeep(filterParams));
                }

            });

            //reset other filters 

            for (var key in it.filterElements) {
                if (!hasFilter.includes(key)) {
                    var filterMap = it.filterElements[key];
                    if (filterMap) {
                        filterMap.setValue("");
                    }
                }
            }

            it.refreshFilterDropdown();
            if (reload !== false) {
                it.reloadInstance();
            }

            it.showHideClearFilterButton();
            it.updateFilterModalState(filterInfo);
            it.saveSelectedFilter();
        }

    }
    refreshFilterDropdown() {
        var options = "",
            it = this,
            filters = it.getFilters(),
            title = "";

        $.each(filters, function (index, filterItem) {

            var active = "";
            if (filterItem.id === it.activeFilterId) {
                active = "active";
                title = filterItem.title;
            }
            options += '<li><a href="#" class="dropdown-item smart-filter-item list-group-item clickable ' + active + ' "data-id="' + filterItem.id + '">';
            options += filterItem.title;
            options += '</a></li>';
        });

        this.$instanceWrapper.find(".smart-filter-list-group").html(options);

        if (!title) {
            title = AppLanugage.filters;
        }
        var smartFilterButtonText = '<i data-feather="filter" class="icon-16 mr5"></i>' + title;

        this.$instanceWrapper.find(".smart-filter-dropdown").html(smartFilterButtonText);

        if (filters.length) {
            this.$instanceWrapper.find(".smart-filter-dropdown-container").removeClass("hide").closest(".filter-item-box").css("position", "initial");
            this.$instanceWrapper.find(".show-filter-form-button").find(".add-filter-text").addClass("hide");
        } else {
            this.$instanceWrapper.find(".smart-filter-dropdown-container").addClass("hide").closest(".filter-item-box").css("position", "absolute");
            this.$instanceWrapper.find(".show-filter-form-button").find(".add-filter-text").removeClass("hide");
        }

        feather.replace();
    }
    getFilters() {
        return getContextFilters(this.settings);
    }
    prepareSurchOption() {
        var settings = this.settings,
            it = this;
        if (settings.search && settings.search.show !== false) {
            var searchDom = '<div class="filter-item-box">'
                + '<input type="search" class="custom-filter-search" name="' + settings.search.name + '" placeholder="' + settings.customLanguage.searchPlaceholder + '">'
                + '</div>';
            it.$instanceWrapper.find(it.rightFilterSectionClsss).append(searchDom);

            var wait;
            it.$instanceWrapper.find(".custom-filter-search").keyup(function () {
                appLoader.show();

                var $search = $(this);
                clearTimeout(wait);

                wait = setTimeout(function () {
                    it.settings.filterParams[settings.search.name] = $search.val();
                    it.reloadInstance();
                }, 700);

            });
        }
    }
    prepareCollapsePannelButton() {
        if (this.settings.isMobile && !this.settings.smartFilterIdentity) {

            if (this.settings.dateRangeType || typeof this.settings.checkBoxes[0] !== 'undefined' || typeof this.settings.multiSelect[0] !== 'undefined' || typeof this.settings.radioButtons[0] !== 'undefined' || typeof this.settings.singleDatepicker[0] !== 'undefined' || typeof this.settings.rangeDatepicker[0] !== 'undefined' || typeof this.settings.filterDropdown[0] !== 'undefined') {

                var collapsePanelDom = "<div class='float-end filter-collapse-button'>\
                        <button title='" + AppLanugage.filters + "' class='dropdown-toggle btn btn-default mt0' data-bs-toggle='collapse' data-bs-target='#table-collapse-filter-" + this.randomId + "' aria-expanded='false'><i data-feather='sliders' class='icon-18'></i></button>\
                    </div>\
                    <div id='table-collapse-filter-" + this.randomId + "' class='navbar-collapse collapse w100p'></div>";

                this.$instanceWrapper.find(this.leftFilterSectionClsss).append(collapsePanelDom);
            }
        }
    }
    prepareReloadButton() {
        var it = this;
        if (it.settings.reloadSelector) {
            if (!$(it.settings.reloadSelector).length) {
                var reloadDom = '<div class="filter-item-box reload-button-container">'
                    + '<button class="btn btn-default" id="' + it.settings.reloadSelector.slice(1) + '"><i data-feather="refresh-cw" class="icon-16"></i></button>'
                    + '</div>';
                this.$instanceWrapper.find(this.leftFilterSectionClsss).append(reloadDom);  //bind refresh icon
            }

            if ($(it.settings.reloadSelector).length) {
                $(it.settings.reloadSelector).click(function () {
                    appLoader.show();
                    it.reloadInstance();
                });
            }
        }
    }
    showHideClearFilterButton() {
        if (this.activeFilterId) {
            this.$instanceWrapper.find(".clear-filter-button").removeClass("hide");
        } else {
            this.$instanceWrapper.find(".clear-filter-button").addClass("hide");
        }
    }
    clearAllFilters() {
        var it = this;
        it.activeFilterId = "";
        for (var key in this.filterElements) {
            var filterMap = it.filterElements[key];
            it.settings.filterParams[key] = "";
            if (filterMap) {
                filterMap.setValue("");
            }
        }

        it.showHideClearFilterButton();
    }
    prepareFilterFormShowButton() {
        if (this.settings.smartFilterIdentity) {
            var filters = this.getFilters();
            var filterText = '<span class="add-filter-text ml5">' + AppLanugage.addNewFilter + '</span>';
            if (filters.length) {
                filterText = "";
            } else {
                this.$instanceWrapper.find(".smart-filter-dropdown-container").addClass("hide").closest(".filter-item-box").css("position", "absolute");
            }
            var smartFilterDropdownDom = '<div class="filter-item-box show-hide-filter-button-box">'
                + '<button class="btn btn-default show-filter-form-button" type="button"><i data-feather="plus" class="icon-16"></i>' + filterText + '</button>'
                + '</div>';

            var it = this;

            this.$instanceWrapper.find(this.leftFilterSectionClsss).append(smartFilterDropdownDom);

            this.$instanceWrapper.find(".show-filter-form-button").click(function () {
                //toggle
                if (it.$instanceWrapper.find(it.filterFormClass).hasClass("hide")) {
                    it.showFilterForm();
                } else {
                    it.hideFilterForm();
                }

            });

            //show filter bar based on settings
            var it = this;
            setTimeout(function () {
                var showFilterBar = false;
                var barType = AppHelper && AppHelper.settings && AppHelper.settings.filterBar ? AppHelper.settings.filterBar : "";
                if (barType === "always_expanded") {
                    showFilterBar = true;
                } else if (barType === "expanded_until_saved_filter_selected") {
                    var filterId = getFilterIdFromCookie(it.settings);
                    var filteInfo = getContextFilterInfo(filterId, it.settings);
                    if (!filteInfo) {
                        showFilterBar = true;
                    }
                }

                if (showFilterBar && !(it.settings.isMobile || it.settings.mobileMirror)) {
                    it.$instanceWrapper.find(".show-filter-form-button").trigger("click");
                }

            });

        }
    }
    prepareBookmarkFilterButtons() {
        if (this.settings.smartFilterIdentity) {

            var it = this;
            it.refreshBookmarkFilterButtons();

            it.$instanceWrapper.find(".filter-section-container").on('click', '.bookmarked-filter-button', function () {
                var data = $(this).data() || {},
                    filterId = data.id;
                it.state = "new_filter";
                it.applySelectedFilter(filterId);
            });
        }
    }
    refreshBookmarkFilterButtons() {
        if (this.settings.smartFilterIdentity) {
            var it = this,
                filters = it.getFilters();
            it.$instanceWrapper.find(".bookmarked-filter-button-wrapper").remove();

            it.$instanceWrapper.find(it.leftFilterSectionClsss).append("<div class='bookmarked-filter-button-container scrollable-container'></div>");

            $.each(filters, function (index, filterItem) {
                if (filterItem.bookmark == "1") {
                    var bookmarkButtonContent = filterItem.title;
                    if (filterItem.icon) {
                        bookmarkButtonContent = '<i data-feather="' + filterItem.icon + '" class="icon-16"></i>';
                    }

                    var smartFilterDropdownDom = '<div class="filter-item-box bookmarked-filter-button-wrapper">'
                        + '<button class="btn btn-default bookmarked-filter-button round" type="button" data-id="' + filterItem.id + '"  >' + bookmarkButtonContent + '</button>'
                        + '</div>';

                    it.$instanceWrapper.find(it.leftFilterSectionClsss).find(".bookmarked-filter-button-container").append(smartFilterDropdownDom);
                }
            });

            feather.replace();
        }
    }

    hideFilterForm() {
        this.state = "new_filter";
        this.$instanceWrapper.find(this.filterFormClass).addClass("hide");
        this.showFilterFormButton();
    }
    showFilterForm() {
        this.$instanceWrapper.find(this.filterFormClass).removeClass("hide");
        this.hideFilterFormButton();
        this.showSaveFilterButton();
        this.updateFilterModalState();
    }
    hideFilterFormButton() {
        var $targetElement = this.$instanceWrapper.find(".show-filter-form-button").closest(".filter-item-box");
        if ($targetElement.find(".add-filter-text").html()) {
            $targetElement.addClass("hide");
        } else {
            $targetElement.find("button").find("svg").css({ "transform": "rotate(45deg)", "transition": "all 0.2s ease 0s" });
        }
    }
    showFilterFormButton() {

        var $targetElement = this.$instanceWrapper.find(".show-filter-form-button").closest(".filter-item-box");
        if ($targetElement.find(".add-filter-text").html()) {
            $targetElement.removeClass("hide");
        } else {
            $targetElement.find("button").find("svg").css("transform", "rotate(0deg)");
        }
    }
    updateFilterModalState(filterInfo) {
        var title = AppLanugage.newFilter;
        var $button = this.$instanceWrapper.find(".save-filter-button");

        if (this.state === "change_filter") {
            title = AppLanugage.updateFilter;
            if (filterInfo) {
                title += " (" + filterInfo.title + ")";
            }
            $button.attr("data-title", title);
            $button.attr("data-post-id", this.activeFilterId);
            $button.attr("data-post-change_filter", "1");
        } else {
            $button.attr("data-title", title);
            $button.attr("data-post-id", getRandomAlphabet(10));
            $button.attr("data-post-change_filter", "");
        }


    }
    showSaveFilterButton() {

        var filters = this.getFilters();

        if (filters.length) {
            this.$instanceWrapper.find(".save-filter-button").addClass("btn-default").removeClass("btn-success");
        } else {
            this.$instanceWrapper.find(".save-filter-button").addClass("btn-success").removeClass("btn-default");
        }

        this.$instanceWrapper.find(".save-filter-button").closest(".filter-item-box").removeClass("hide");
    }
    hideSaveSelectedFilterButton() {
        this.$instanceWrapper.find(".save-filter-button").closest(".filter-item-box").addClass("hide");
    }
    getInstanceId() {
        return this.$instance.attr("id");
    }
    getContextId() {
        if (this.settings.contextMeta && this.settings.contextMeta.contextId) {
            return this.settings.contextMeta.contextId;
        } else {
            return "";
        }
    }
    getContextDependencies() {
        if (this.settings.contextMeta && this.settings.contextMeta.dependencies) {
            return this.settings.contextMeta.dependencies;
        } else {
            return "";
        }
    }
    prepareSaveFilterButton() {
        if (this.settings.smartFilterIdentity) {
            var it = this;

            var dataPostAttrs = " data-post-context='" + it.settings.smartFilterIdentity + "' "
                + " data-post-instance_id='" + it.getInstanceId() + "' ";

            if (it.getContextId()) {
                dataPostAttrs += " data-post-context_id= '" + it.getContextId() + "' ";
            }


            var actionUrl = AppHelper.baseUrl + "index.php/filters/modal_form";
            var smartFilterCreateButon = '<div class="filter-item-box save-filter-box hide">'
                + '<button class="btn btn-default save-filter-button" data-act="ajax-modal" data-title="" ' + dataPostAttrs + '  type="button" data-action-url="' + actionUrl + '" ><i data-feather="check-circle" class="icon-16"></i></button>'
                + '</div>';

            this.$instanceWrapper.find(this.filterFormClass).append(smartFilterCreateButon);
        }
    }
    prepareCancelFilterFormButton() {
        if (this.settings.smartFilterIdentity) {
            var it = this;

            var smartFilterCancelButton = '<div class="filter-item-box filter-cancel-box">'
                + '<button class="btn btn-default cancel-filter-button" type="button" ><i data-feather="x-circle" class="icon-16"></i></button>'
                + '</div>';


            this.$instanceWrapper.find(this.filterFormClass).append(smartFilterCancelButton);

            this.$instanceWrapper.find(".cancel-filter-button").click(function () {
                it.hideFilterForm();
            });

        }
    }
    appendFilterDom(dom, rangeRadioButton = false) {
        if (this.settings.smartFilterIdentity) {
            if (rangeRadioButton) {
                this.$instanceWrapper.find(".range-radio-button").after(dom);
            } else {
                this.$instanceWrapper.find(this.filterFormClass).append(dom);
            }
        } else {
            this.$instanceWrapper.find(this.leftFilterSectionClsss).append(dom);
        }
    }
    prepareDateRangePicker(fromDateRangeFilter = false) {
        var it = this,
            settings = this.settings,
            $instance = this.$instance,
            $instanceWrapper = this.$instanceWrapper;

        if (settings.dateRangeType) {
            var dateRangeFilterDom = '<div class="filter-item-box btn-group">'
                + '<button data-act="prev" class="btn btn-default date-range-selector"><i data-feather="chevron-left" class="icon"></i></button>'
                + '<button data-act="datepicker" class="btn btn-default"></button>'
                + '<button data-act="next"  class="btn btn-default date-range-selector"><i data-feather="chevron-right" class="icon"></i></button>'
                + '</div>';

            if (fromDateRangeFilter) {
                this.appendFilterDom(dateRangeFilterDom, true);
            } else {
                this.appendFilterDom(dateRangeFilterDom);
            }

            var $datepicker = $instanceWrapper.find("[data-act='datepicker']"),
                $dateRangeSelector = $instanceWrapper.find(".date-range-selector");

            //init single day selector
            if (settings.dateRangeType === "daily") {
                var initSingleDaySelectorText = function ($elector) {
                    if (settings.filterParams.start_date === moment().customFormat(settings._inputDateFormat)) {
                        $elector.html(settings.customLanguage.today);
                    } else if (settings.filterParams.start_date === moment().subtract(1, 'days').customFormat(settings._inputDateFormat)) {
                        $elector.html(settings.customLanguage.yesterday);
                    } else if (settings.filterParams.start_date === moment().add(1, 'days').customFormat(settings._inputDateFormat)) {
                        $elector.html(settings.customLanguage.tomorrow);
                    } else {
                        $elector.html(moment(settings.filterParams.start_date).format("Do MMMM YYYY"));
                    }
                };
                // prepareDefaultDateRangeFilterParams();
                initSingleDaySelectorText($datepicker);

                //bind the click events
                $datepicker.datepicker({
                    format: settings._inputDateFormat,
                    autoclose: true,
                    todayHighlight: true,
                    language: "custom",
                    orientation: "bottom"
                }).on('changeDate', function (e) {
                    var date = moment(e.date).customFormat(settings._inputDateFormat);
                    settings.filterParams.start_date = date;
                    settings.filterParams.end_date = date;
                    initSingleDaySelectorText($datepicker);

                    it.reloadInstance();

                });

                $dateRangeSelector.click(function () {
                    var type = $(this).attr("data-act"), date = "";
                    if (type === "next") {
                        date = moment(settings.filterParams.start_date).add(1, 'days').customFormat(settings._inputDateFormat);
                    } else if (type === "prev") {
                        date = moment(settings.filterParams.start_date).subtract(1, 'days').customFormat(settings._inputDateFormat)
                    }
                    settings.filterParams.start_date = date;
                    settings.filterParams.end_date = date;
                    initSingleDaySelectorText($datepicker);
                    it.reloadInstance();
                });

                it.filterElements['start_date'] = {
                    setValue: function (value) {
                        $datepicker.datepicker('update', value);
                        initSingleDaySelectorText($datepicker);
                    }
                };

            }


            //init month selector
            if (settings.dateRangeType === "monthly") {
                var initMonthSelectorText = function ($elector) {
                    $elector.html(moment(settings.filterParams.start_date).format("MMMM YYYY"));
                };

                //prepareDefaultDateRangeFilterParams();
                initMonthSelectorText($datepicker);

                //bind the click events
                $datepicker.datepicker({
                    format: "YYYY-MM",
                    viewMode: "months",
                    minViewMode: "months",
                    autoclose: true,
                    language: "custom",
                    orientation: "bottom"
                }).on('changeDate', function (e) {
                    var date = moment(e.date).customFormat(settings._inputDateFormat);
                    var daysInMonth = moment(date).daysInMonth(),
                        yearMonth = moment(date).customFormat("YYYY-MM");
                    settings.filterParams.start_date = yearMonth + "-01";
                    settings.filterParams.end_date = yearMonth + "-" + daysInMonth;
                    initMonthSelectorText($datepicker);
                    it.reloadInstance();
                });

                $dateRangeSelector.click(function () {
                    var type = $(this).attr("data-act"),
                        startDate = moment(settings.filterParams.start_date),
                        endDate = moment(settings.filterParams.end_date);
                    if (type === "next") {
                        var nextMonth = startDate.add(1, 'months'),
                            daysInMonth = nextMonth.daysInMonth(),
                            yearMonth = nextMonth.customFormat("YYYY-MM");

                        startDate = yearMonth + "-01";
                        endDate = yearMonth + "-" + daysInMonth;

                    } else if (type === "prev") {
                        var lastMonth = startDate.subtract(1, 'months'),
                            daysInMonth = lastMonth.daysInMonth(),
                            yearMonth = lastMonth.customFormat("YYYY-MM");

                        startDate = yearMonth + "-01";
                        endDate = yearMonth + "-" + daysInMonth;
                    }

                    settings.filterParams.start_date = startDate;
                    settings.filterParams.end_date = endDate;

                    initMonthSelectorText($datepicker);
                    it.reloadInstance();
                });

                it.filterElements['start_date'] = {
                    setValue: function (value) {
                        $datepicker.datepicker('update', value);
                        initMonthSelectorText($datepicker);
                    }
                };
            }

            //init year selector
            if (settings.dateRangeType === "yearly") {
                var inityearSelectorText = function ($elector) {
                    $elector.html(moment(settings.filterParams.start_date).customFormat("YYYY"));
                };
                // prepareDefaultDateRangeFilterParams();
                inityearSelectorText($datepicker);

                //bind the click events
                $datepicker.datepicker({
                    format: "YYYY-MM",
                    viewMode: "years",
                    minViewMode: "years",
                    autoclose: true,
                    language: "custom",
                    orientation: "bottom"
                }).on('changeDate', function (e) {
                    var date = moment(e.date).customFormat(settings._inputDateFormat),
                        year = moment(date).customFormat("YYYY");
                    settings.filterParams.start_date = year + "-01-01";
                    settings.filterParams.end_date = year + "-12-31";
                    inityearSelectorText($datepicker);
                    it.reloadInstance();
                });

                $dateRangeSelector.click(function () {
                    var type = $(this).attr("data-act"),
                        startDate = moment(settings.filterParams.start_date),
                        endDate = moment(settings.filterParams.end_date);
                    if (type === "next") {
                        startDate = startDate.add(1, 'years').customFormat(settings._inputDateFormat);
                        endDate = endDate.add(1, 'years').customFormat(settings._inputDateFormat);
                    } else if (type === "prev") {
                        startDate = startDate.subtract(1, 'years').customFormat(settings._inputDateFormat);
                        endDate = endDate.subtract(1, 'years').customFormat(settings._inputDateFormat);
                    }
                    settings.filterParams.start_date = startDate;
                    settings.filterParams.end_date = endDate;
                    inityearSelectorText($datepicker);
                    it.reloadInstance();
                });


                it.filterElements['start_date'] = {
                    setValue: function (value) {
                        $datepicker.datepicker('update', value);
                        inityearSelectorText($datepicker);
                    }
                };
            }

            //init week selector
            if (settings.dateRangeType === "weekly") {
                var initWeekSelectorText = function ($elector) {
                    var from = moment(settings.filterParams.start_date).format("Do MMM"),
                        to = moment(settings.filterParams.end_date).format("Do MMM, YYYY");
                    $datepicker.datepicker({
                        format: "YYYY-MM-DD",
                        autoclose: true,
                        calendarWeeks: true,
                        language: "custom",
                        orientation: "bottom",
                        weekStart: AppHelper.settings.firstDayOfWeek
                    });
                    $elector.html(from + " - " + to);
                };

                //prepareDefaultDateRangeFilterParams();
                initWeekSelectorText($datepicker);

                //bind the click events
                $dateRangeSelector.click(function () {
                    var type = $(this).attr("data-act"),
                        startDate = moment(settings.filterParams.start_date),
                        endDate = moment(settings.filterParams.end_date);
                    if (type === "next") {
                        startDate = startDate.add(7, 'days').customFormat(settings._inputDateFormat);
                        endDate = endDate.add(7, 'days').customFormat(settings._inputDateFormat);
                    } else if (type === "prev") {
                        startDate = startDate.subtract(7, 'days').customFormat(settings._inputDateFormat);
                        endDate = endDate.subtract(7, 'days').customFormat(settings._inputDateFormat);
                    }
                    settings.filterParams.start_date = startDate;
                    settings.filterParams.end_date = endDate;
                    initWeekSelectorText($datepicker);
                    it.reloadInstance();
                });

                $datepicker.datepicker({
                    format: settings._inputDateFormat,
                    autoclose: true,
                    calendarWeeks: true,
                    language: "custom",
                    weekStart: AppHelper.settings.firstDayOfWeek
                }).on("show", function () {
                    $(".datepicker").addClass("week-view");
                    $(".datepicker-days").find(".active").siblings(".day").addClass("active");
                }).on('changeDate', function (e) {
                    var range = getWeekRange(e.date);
                    settings.filterParams.start_date = range.firstDateOfWeek;
                    settings.filterParams.end_date = range.lastDateOfWeek;
                    initWeekSelectorText($datepicker);
                    it.reloadInstance();
                });

                it.filterElements['start_date'] = {
                    setValue: function (value) {
                        $datepicker.datepicker('update', value);
                        initWeekSelectorText($datepicker);
                    }
                };
            }
        }
    }
    prepareDropdownFilters() {
        var settings = this.settings,
            it = this,
            $instanceWrapper = this.$instanceWrapper;

        if (typeof settings.filterDropdown[0] !== 'undefined') {

            $.each(settings.filterDropdown, function (index, dropdown) {

                var selectedValue = "",
                    placeholder = "";

                if (dropdown.value) {
                    selectedValue = dropdown.value;
                } else {
                    $.each(dropdown.options, function (index, option) {
                        if (option.isSelected) {
                            selectedValue = option.id;
                        }
                    });
                }

                if (dropdown.options && dropdown.options[0] && !dropdown.options[0].id && dropdown.options[0].text) {
                    placeholder = dropdown.options[0].text;
                }

                if (dropdown.name) {
                    settings.filterParams[dropdown.name] = selectedValue;
                }

                var selectDom = '<div class="filter-item-box">'
                    + '<input class="' + dropdown.class + '" name="' + dropdown.name + '" value="' + selectedValue + '" placeholder="' + placeholder + '" />'
                    + '</div>';

                it.appendFilterDom(selectDom);

                var $dropdown = $instanceWrapper.find("[name='" + dropdown.name + "']");
                var dropdownOnchangeCallback = function ($selector) {
                    var filterName = $selector.attr("name"),
                        value = $selector.val();

                    //set the new value to settings
                    settings.filterParams[filterName] = value;

                    //check if there any dependent files,
                    //reset the dependent fields if this value is empty
                    //re-load the dependent fields if this value is not empty

                    if (dropdown.dependent && dropdown.dependent.length) {
                        it.prepareDependentFilter(filterName, value, settings.filterDropdown, settings.filterParams);
                    }

                    //callback
                    if (dropdown.onChangeCallback) {
                        dropdown.onChangeCallback(value, settings.filterParams);
                    }

                    it.reloadInstance();
                }


                if (window.Select2 !== undefined) {
                    var appDropdownOptions = {
                        list_data: dropdown.options,
                        onChangeCallback: function (value, instance) {
                            dropdownOnchangeCallback(instance);
                        }
                    };

                    if (dropdown.showHtml) {
                        appDropdownOptions.escapeMarkup = function (markup) {
                            return markup;
                        };
                    }

                    $dropdown.appDropdown(appDropdownOptions);

                }

                // $dropdown.change(function () {
                //     var $selector = $(this),
                //         filterName = $selector.attr("name"),
                //         value = $selector.val();

                //     //set the new value to settings
                //     settings.filterParams[filterName] = value;

                //     //check if there any dependent files,
                //     //reset the dependent fields if this value is empty
                //     //re-load the dependent fields if this value is not empty

                //     if (dropdown.dependent && dropdown.dependent.length) {
                //         it.prepareDependentFilter(filterName, value, settings.filterDropdown, settings.filterParams);
                //     }

                //     //callback
                //     if (dropdown.onChangeCallback) {
                //         dropdown.onChangeCallback(value, settings.filterParams);
                //     }

                //     it.reloadInstance();
                // });

                it.filterElements[dropdown.name] = {
                    setValue: function (value, newFilterParams) {
                        $dropdown.select2("val", value);
                        if (dropdown.showHtml && !value) {
                            if (dropdown.options && dropdown.options[0] && !dropdown.options[0].id && dropdown.options[0].text) {
                                $dropdown.siblings(".select2-container").find(".select2-chosen").html(dropdown.options[0].text);
                            }
                        }

                        window[dropdown.name] = $dropdown;
                        if (dropdown.dependent && dropdown.dependent.length) {
                            it.prepareDependentFilter(dropdown.name, value, settings.filterDropdown, settings.filterParams, newFilterParams);
                        }

                        if (dropdown.onChangeCallback) {
                            dropdown.onChangeCallback(value, newFilterParams);
                        }
                    }
                };


            });
        }
    }

    getDynamicDateRanges() {
        return getDynamicDateRanges();
    }

    prepareDatePickerFilter(fromDateRangeFilter = false) {

        var settings = this.settings,
            it = this,
            $instance = this.$instance,
            $instanceWrapper = this.$instanceWrapper;

        if (typeof settings.rangeDatepicker[0] !== 'undefined') {

            $.each(settings.rangeDatepicker, function (index, datePicker) {

                var startDate = datePicker.startDate || {},
                    endDate = datePicker.endDate || {},
                    showClearButton = datePicker.showClearButton ? true : false,
                    emptyText = '<i data-feather="calendar" class="icon-16"></i>',
                    startButtonText = startDate.value ? moment(startDate.value, settings._inputDateFormat).format("Do MMMM YYYY") : emptyText,
                    endButtonText = endDate.value ? moment(endDate.value, settings._inputDateFormat).format("Do MMMM YYYY") : emptyText;

                //set filter params
                settings.filterParams[startDate.name] = startDate.value;
                settings.filterParams[endDate.name] = endDate.value;

                var reloadDateRangeFilter = function (name, date) {
                    settings.filterParams[name] = date;
                    it.reloadInstance();
                };


                var defaultRanges = it.getDynamicDateRanges();


                var devider = '<span class="input-group-addon">-</span>';
                var showRange = false;

                if (datePicker.label) {
                    devider = '<span class="input-group-addon custom-date-range-lable">' + datePicker.label + '</span>';


                    if (datePicker.ranges) {

                        var options = "";
                        $.each(datePicker.ranges, function (index, range) {
                            if (defaultRanges[range]) {
                                options += '<li><a href="#" class="dropdown-item list-group-item clickable" data-range="' + range + '">';
                                options += AppLanugage[range];
                                options += '</a></li>';
                            }
                        });
                        if (options) {
                            showRange = true;
                            var rangeDropdownDom = ''
                                + '<div class="dropdown">'
                                + '<div class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="true">' + datePicker.label + '</div>'
                                + '<div class="dropdown-menu">'
                                + '<ul class="list-group">' + options + '</ul>'
                                + '</div>'
                                + '</div>'
                                ;

                            devider = '<span class="input-group-addon custom-date-range-dropdown clickable">' + rangeDropdownDom + '</span>';

                        }

                    }
                }


                var dateRangeClass = "daterange-" + getRandomAlphabet(5);

                //prepare DOM
                var selectDom = '<div class="filter-item-box">'
                    + '<div class="input-daterange input-group ' + dateRangeClass + '">'
                    + '<button class="btn btn-default form-control" name="' + startDate.name + '" data-date="' + startDate.value + '">' + startButtonText + '</button>'
                    + devider
                    + '<button class="btn btn-default form-control" name="' + endDate.name + '" data-date="' + endDate.value + '">' + endButtonText + ''
                    + '</div>'
                    + '</div>';

                if (fromDateRangeFilter) {
                    it.appendFilterDom(selectDom, true);
                } else {
                    it.appendFilterDom(selectDom);
                }

                var $datePicker = $instanceWrapper.find("." + dateRangeClass),
                    inputs = $datePicker.find('button').toArray();

                var showButtonText = function () {
                    var s_date = settings.filterParams[startDate.name],
                        e_date = settings.filterParams[endDate.name];
                    $(inputs[0]).html(s_date ? moment(s_date, settings._inputDateFormat).format("Do MMMM YYYY") : emptyText);
                    $(inputs[1]).html(e_date ? moment(e_date, settings._inputDateFormat).format("Do MMMM YYYY") : emptyText);
                };

                //init datepicker
                $datePicker.datepicker({
                    format: "yyyy-mm-dd",
                    autoclose: true,
                    todayHighlight: true,
                    language: "custom",
                    weekStart: AppHelper.settings.firstDayOfWeek,
                    orientation: "bottom",
                    inputs: inputs
                }).on('changeDate', function (e) {
                    var date = moment(e.date, settings._inputDateFormat).customFormat(settings._inputDateFormat);

                    //set save value if anyone is empty
                    if (!settings.filterParams[startDate.name]) {
                        settings.filterParams[startDate.name] = date;
                    }

                    if (!settings.filterParams[endDate.name]) {
                        settings.filterParams[endDate.name] = date;
                    }

                    reloadDateRangeFilter($(e.target).attr("name"), date);

                    //show button text
                    showButtonText();

                }).on("show", function () {

                    //show clear button
                    if (showClearButton) {
                        $(".datepicker-clear-selection").show();
                        if (!$(".datepicker-clear-selection").length) {
                            $(".datepicker").append("<div class='datepicker-clear-selection p5 clickable text-center'>" + AppLanugage.clear + "</div>");

                            //bind click event for clear button
                            $(".datepicker .datepicker-clear-selection").click(function () {
                                settings.filterParams[startDate.name] = "";
                                reloadDateRangeFilter(endDate.name, "");

                                $(inputs[0]).html(emptyText);
                                $(inputs[1]).html(emptyText);
                                $(".datepicker").hide();
                            });
                        }
                    }
                });


                if (showRange) {
                    it.$instanceWrapper.find("." + dateRangeClass).on('click', '.list-group-item', function () {
                        var data = $(this).data() || {};
                        var date = defaultRanges[data.range];
                        settings.filterParams[endDate.name] = date[1];

                        reloadDateRangeFilter(startDate.name, date[0]);

                        showButtonText();
                    });
                }


                it.filterElements[startDate.name] = {

                    setValue: function (value) {
                        settings.filterParams[startDate.name] = value;
                        $datePicker.datepicker('update', value);
                        showButtonText();
                    }
                };
                it.filterElements[endDate.name] = {
                    setValue: function (value) {

                        settings.filterParams[endDate.name] = value;
                        $datePicker.datepicker('update', value);
                        showButtonText();
                    }
                };

            });
        }
    }
    prepareSingleDatePicker() {
        var settings = this.settings,
            it = this,
            $instance = this.$instance,
            $instanceWrapper = this.$instanceWrapper;

        if (typeof settings.singleDatepicker[0] !== 'undefined') {

            $.each(settings.singleDatepicker, function (index, datePicker) {

                var options = " ", value = "", selectedText = "";

                if (!datePicker.options)
                    datePicker.options = [];

                //add custom datepicker selector
                datePicker.options.push({ value: "show-date-picker", text: AppLanugage.custom });

                //prepare custom list
                $.each(datePicker.options, function (index, option) {
                    var isSelected = "";
                    if (option.isSelected) {
                        isSelected = "active";
                        value = option.value;
                        selectedText = option.text;
                    }

                    options += '<div class="list-group-item ' + isSelected + '" data-value="' + option.value + '">' + option.text + '</div>';
                });

                if (!selectedText) {
                    selectedText = "- " + datePicker.defaultText + " -";
                    options = '<div class="list-group-item active" data-value="">' + selectedText + '</div>' + options;
                }



                //set filter params
                if (datePicker.name) {
                    settings.filterParams[datePicker.name] = value;
                }

                var reloadDatePickerFilter = function (date) {
                    settings.filterParams[datePicker.name] = date;
                    it.reloadInstance();
                };

                var getDatePickerText = function (text) {
                    return text + "<span class='ml10 dropdown-toggle'></span>";
                };



                //prepare DOM
                var customList = '<div class="datepicker-custom-list list-group mb0">'
                    + options
                    + '</div>';

                var datePickerClass = "";
                if (datePicker.class) {
                    datePickerClass = datePicker.class;
                }


                var selectDom = '<div class="filter-item-box">'
                    + '<button name="' + datePicker.name + '" class="btn ' + datePickerClass + ' datepicker-custom-selector">'
                    + getDatePickerText(selectedText)
                    + '</button>'
                    + '</div>';

                it.appendFilterDom(selectDom);

                var $datePicker = $instanceWrapper.find("[name='" + datePicker.name + "']"),
                    showCustomRange = typeof datePicker.options[1] === 'undefined' ? false : true; //don't show custom range if options not > 1

                //init datepicker
                $datePicker.datepicker({
                    format: settings._inputDateFormat,
                    autoclose: true,
                    todayHighlight: true,
                    language: "custom",
                    weekStart: AppHelper.settings.firstDayOfWeek,
                    orientation: "bottom"
                }).on("show", function () {

                    //has custom dates, show them otherwise show the datepicker
                    if (showCustomRange) {
                        $(".datepicker-days, .datepicker-months, .datepicker-years, .datepicker-decades, .table-condensed").hide();
                        $(".datepicker-custom-list").show();
                        if (!$(".datepicker-custom-list").length) {
                            $(".datepicker").append(customList);

                            //bind click events
                            $(".datepicker .list-group-item").click(function () {
                                $(".datepicker .list-group-item").removeClass("active");
                                $(this).addClass("active");
                                var value = $(this).attr("data-value");
                                //show datepicker for custom date
                                if (value === "show-date-picker") {
                                    $(".datepicker-custom-list, .datepicker-months, .datepicker-years, .datepicker-decades, .table-condensed").hide();
                                    $(".datepicker-days, .table-condensed").show();
                                } else {
                                    $(".datepicker").hide();

                                    if (moment(value, settings._inputDateFormat).isValid()) {
                                        value = moment(value, settings._inputDateFormat).customFormat(settings._inputDateFormat);
                                    }

                                    $datePicker.html(getDatePickerText($(this).html()));
                                    reloadDatePickerFilter(value);
                                }
                            });
                        }
                    }
                }).on('changeDate', function (e) {
                    $datePicker.html(getDatePickerText(moment(e.date, settings._inputDateFormat).format("Do MMMM YYYY")));
                    reloadDatePickerFilter(moment(e.date, settings._inputDateFormat).customFormat(settings._inputDateFormat));
                });

                it.filterElements[datePicker.name] = {
                    setValue: function (value) {
                        $datePicker.datepicker('update', value);
                        //prepare custom list
                        var text = "";

                        $.each(datePicker.options, function (index, option) {
                            if (value === option.value) {
                                text = option.text;
                            }
                        });

                        if (value && text) {
                            $datePicker.html(getDatePickerText(text));
                        } else if (value) {
                            $datePicker.html(getDatePickerText(moment(value, settings._inputDateFormat).format("Do MMMM YYYY")));
                        } else if (datePicker.defaultText) {
                            $datePicker.html(getDatePickerText(datePicker.defaultText)); //set default text if option if don't have any value
                        }

                        $(".datepicker .list-group-item").removeClass("active");
                        $(".datepicker .list-group-item").each(function () {
                            if (value === $(this).attr("data-value")) {
                                $(this).addClass("active");
                            }
                        });
                    }
                };

            });
        }
    }
    prepareRadioFilter(rangeRadioButtonOptions = null) {
        var settings = this.settings,
            it = this,
            $instance = this.$instance,
            $instanceWrapper = this.$instanceWrapper;

        if (typeof settings.radioButtons[0] !== 'undefined' || rangeRadioButtonOptions) {

            if (rangeRadioButtonOptions) {
                settings.radioButtons = rangeRadioButtonOptions.rangeOptions;
            }

            var radiobuttons = "",
                filterName = "",
                value = "";
            $.each(settings.radioButtons, function (index, option) {
                var checked = "", active = "";
                filterName = option.name;
                if (option.isChecked) {
                    checked = " checked";
                    active = " active";
                    settings.filterParams[option.name] = option.value;
                    value = option.value;
                }
                radiobuttons += '<label class="btn btn-default mb0 ' + active + '">';
                radiobuttons += '<input type="radio" name="' + option.name + '" value="' + option.value + '" autocomplete="off" ' + checked + '>' + option.text;
                radiobuttons += '</label>';
            });

            var rangeRadioButtonClass = "";
            if (rangeRadioButtonOptions) {
                rangeRadioButtonClass = "range-radio-button";
            }

            var radioDom = '<div class="filter-item-box ' + rangeRadioButtonClass + '">'
                + '<div class="btn-group filter" data-act="radio" data-toggle="buttons">'
                + radiobuttons
                + '</div>'
                + '</div>';

            it.appendFilterDom(radioDom);

            var $radioButtons = $instanceWrapper.find("[data-act='radio'] input[type=radio]");

            if (rangeRadioButtonOptions && rangeRadioButtonOptions.onInit) {
                rangeRadioButtonOptions.onInit(value);
            }


            $radioButtons.click(function () {
                setTimeout(function () {
                    var value = "";
                    $radioButtons.each(function () {
                        $(this).closest("label").removeClass("active");
                        if ($(this).is(":checked")) {
                            settings.filterParams[$(this).attr("name")] = $(this).val();
                            value = $(this).val();
                            $(this).closest("label").addClass("active");
                        }
                    });
                    if (rangeRadioButtonOptions && rangeRadioButtonOptions.onChange) {
                        rangeRadioButtonOptions.onChange(value);
                        it.reloadInstance();
                    } else {
                        it.reloadInstance();
                    }

                });
            });

            it.filterElements[filterName] = {

                setValue: function (value) {
                    $radioButtons.each(function () {
                        $(this).closest("label").removeClass("active");
                        if ($(this).val() == value) {
                            $(this).closest("label").addClass("active");
                            $(this).prop("checked", true);
                        } else {
                            $(this).prop("checked", false);
                        }
                    });


                    if (rangeRadioButtonOptions) {
                        rangeRadioButtonOptions.onInit(value);

                        if (filterName && settings.filterParams[filterName] == "dynamic" && settings.filterParams[filterName + "_dynamic"]) {
                            if (it.filterElements[filterName + "_dynamic"]) {
                                it.filterElements[filterName + "_dynamic"].setValue(settings.filterParams[filterName + "_dynamic"]);
                            }

                        }
                    }

                }
            };


        }
    }
    prepareMultiselectFilter() {
        var settings = this.settings,
            it = this,
            $instance = this.$instance,
            $instanceWrapper = this.$instanceWrapper;

        if (typeof settings.multiSelect[0] !== 'undefined') {

            $.each(settings.multiSelect, function (index, select) {

                var multiSelect = "", values = [],
                    saveSelection = select.saveSelection,
                    selections = getCookie(select.name);

                if (selections) {
                    selections = selections.split("-");
                }

                $.each(select.options, function (index, listOption) {
                    var active = "";

                    if (
                        (saveSelection && selections && (selections.indexOf(listOption.value) > -1)) ||
                        (saveSelection && !selections && listOption.isChecked) ||
                        (!saveSelection && listOption.isChecked)
                    ) {
                        active = " active";
                        values.push(listOption.value);
                    }
                    //<li class=" list-group-item clickable toggle-table-column" data-column="1">ID</li>
                    multiSelect += '<li class="list-group-item clickable ' + active + '" data-name="' + select.name + '" data-value="' + listOption.value + '">';
                    multiSelect += listOption.text;
                    multiSelect += '</li>';
                });


                multiSelect = "<div class='dropdown-menu'><ul class='list-group' data-act='multiselect'>" + multiSelect + "</ul></div>";


                var multiSelectClass = "";
                if (select.class) {
                    multiSelectClass = select.class;
                }


                settings.filterParams[select.name] = values;
                var multiSelectDom = '<div class="filter-item-box">'
                    + '<span class="dropdown inline-block filter-multi-select">'
                    + '<button class="' + multiSelectClass + ' btn btn-default dropdown-toggle caret " type="button" data-bs-toggle="dropdown" aria-expanded="true">' + select.text + ' </button>'
                    + multiSelect
                    + '</span>'
                    + '</div>';

                it.appendFilterDom(multiSelectDom);

                var $multiSelect = $instanceWrapper.find("[data-name='" + select.name + "']");
                $multiSelect.click(function () {
                    var $selector = $(this);
                    $selector.toggleClass("active");
                    setTimeout(function () {
                        var values = [],
                            name = "";
                        $selector.parent().find("li").each(function () {
                            name = $(this).attr("data-name");
                            if ($(this).hasClass("active")) {
                                values.push($(this).attr("data-value"));
                            }
                        });

                        if (saveSelection) {
                            //save selected options to browser cookies
                            selections = values.join("-");
                            setCookie(select.name, selections);
                        }

                        settings.filterParams[name] = values;
                        it.reloadInstance();
                    });
                    return false;
                });


                it.filterElements[select.name] = {

                    setValue: function (values) {
                        if (!values) {
                            values = [];
                        }
                        $multiSelect.each(function () {

                            if (values.includes($(this).attr("data-value"))) {
                                $(this).addClass("active");
                            } else {
                                $(this).removeClass("active");
                            }

                        });
                    }
                };

            });


        }

    }
    prepareCheckboxFilter() {
        var settings = this.settings,
            it = this,
            $instance = this.$instance,
            $instanceWrapper = this.$instanceWrapper;

        if (typeof settings.checkBoxes[0] !== 'undefined') {
            var checkboxes = "", values = [], name = "";
            $.each(settings.checkBoxes, function (index, option) {
                var checked = "", active = "";
                name = option.name;
                if (option.isChecked) {
                    checked = " checked";
                    active = " active";
                    values.push(option.value);
                }
                checkboxes += '<label class="btn btn-default mb0 ' + active + '">';
                checkboxes += '<input type="checkbox" name="' + option.name + '" value="' + option.value + '" autocomplete="off" ' + checked + '>' + option.text;
                checkboxes += '</label>';
            });
            settings.filterParams[name] = values;
            var checkboxDom = '<div class="filter-item-box">'
                + '<div class="btn-group filter" data-act="checkbox" data-toggle="buttons">'
                + checkboxes
                + '</div>'
                + '</div>';

            it.appendFilterDom(checkboxDom);

            var $checkbox = $instanceWrapper.find("[data-act='checkbox']");
            $checkbox.click(function () {
                var $selector = $(this);
                setTimeout(function () {
                    var values = [],
                        name = "";
                    $selector.parent().find("input:checkbox").each(function () {
                        name = $(this).attr("name");
                        if ($(this).is(":checked")) {
                            values.push($(this).val());
                            $(this).closest("label").addClass("active");
                        } else {
                            $(this).closest("label").removeClass("active");
                        }
                    });
                    settings.filterParams[name] = values;
                    it.reloadInstance();
                });
            });



            it.filterElements[name] = {
                setValue: function (values) {
                    if (!values) {
                        values = [];
                    }
                    $instanceWrapper.find("input:checkbox").each(function () {
                        //it'll find all checkboxes. Match with name
                        if (name === $(this).attr("name")) {
                            if (values.includes($(this).val())) {
                                $(this).closest("label").addClass("active");
                            } else {
                                $(this).closest("label").removeClass("active");
                            }
                        }
                    });
                }
            };


        }

    }
    prepareDependentFilter(filterName, filterValue, filterDropdown, filterParams, newFilterParams) {

        var
            it = this,
            $instanceWrapper = this.$instanceWrapper;

        //check all dropdowns and prepre the dependency dropdown list

        $.each(filterDropdown, function (index, option) {

            //is there any dependency for selected field (filterName)? Prepare the dropdown list 
            if (option.dependency && option.dependency.length && option.dependency.indexOf(filterName) !== -1) {

                var $dependencySelector = $instanceWrapper.find("select[name=" + option.name + "]"); //select box
                var dependentFilterName = option.name;

                //we'll call ajax to get the data list
                if (((option.selfDependency && !filterValue) || filterValue) && option.dataSource) {
                    appAjaxRequest({
                        url: option.dataSource,
                        data: filterParams,
                        type: "POST",
                        dataType: 'json',
                        success: function (response) {
                            //if we found the dropdown list, we'll show the options in dropdown
                            if (response && response.length) {
                                var newOptions = "",
                                    firstValue = "";

                                $.each(response, function (index, value) {

                                    if (!index) {
                                        firstValue = value.id; //auto select the first option in select box
                                    }

                                    newOptions += "<option value='" + value.id + "'>" + value.text + "</option>";
                                });

                                //set the new dropdown list in select box
                                $dependencySelector.html(newOptions);


                                if (newFilterParams && newFilterParams[dependentFilterName]) {
                                    $dependencySelector.select2("val", newFilterParams[dependentFilterName]);
                                } else {
                                    $dependencySelector.select2("val", firstValue);
                                }
                            }
                        }
                    });

                } else {
                    //no value selected in parent, reset the dropdown box

                    var $firstOption = $dependencySelector.find("option:first");
                    $dependencySelector.html("<option value='" + $firstOption.val() + "'>" + $firstOption.html() + "</option>");
                    $dependencySelector.select2("val", $firstOption.val());
                }

                //reset the filter param
                if (filterParams && newFilterParams && newFilterParams[dependentFilterName]) {
                    filterParams[dependentFilterName] = newFilterParams[dependentFilterName];
                } else if (filterParams) {
                    var $firstOption = $dependencySelector.find("option:first");
                    filterParams[option.name] = $firstOption.val();
                }

            }

        });

    }

    initDynamicFilter(rangeOptions, value, isOnChange = false) {
        var settings = this.settings,
            it = this,
            $instanceWrapper = it.$instanceWrapper;


        var $startDiv = $instanceWrapper.find('.range-radio-button');

        $startDiv.nextAll('.filter-item-box').find('[data-act="datepicker"]').closest('.filter-item-box').remove();
        $startDiv.nextAll('.filter-item-box').find('.input-daterange').closest('.filter-item-box').remove();
        $startDiv.nextAll('.filter-item-box').find('#dynamic-range-dropdown').closest('.filter-item-box').remove();


        // var $parentFilterBox = $instanceWrapper.find('[data-act="datepicker"]').closest('.filter-item-box');
        // $parentFilterBox.remove();

        // var $dateRangeParentFilterBox = $instanceWrapper.find('.input-daterange').closest('.filter-item-box');
        // $dateRangeParentFilterBox.remove();

        // var $dynamicDateRangeParentFilterBox = $instanceWrapper.find('#dynamic-range-dropdown').closest('.filter-item-box');
        // $dynamicDateRangeParentFilterBox.remove();

        if (value === "monthly") {
            settings.dateRangeType = "monthly";
            if (isOnChange || !settings.filterParams.start_date) {

                var daysInMonth = moment().daysInMonth(),
                    yearMonth = moment().customFormat("YYYY-MM");
                settings.filterParams.start_date = yearMonth + "-01";
                settings.filterParams.end_date = yearMonth + "-" + daysInMonth;
            }

            it.prepareDateRangePicker(true);

        } else if (value === "yearly") {
            settings.dateRangeType = "yearly";
            if (isOnChange) {
                var year = moment().customFormat("YYYY");
                settings.filterParams.start_date = year + "-01-01";
                settings.filterParams.end_date = year + "-12-31";
            }

            it.prepareDateRangePicker(true);
        } else if (value === "custom") {
            var datePickerOptions = [
                {
                    "startDate": {
                        "name": "start_date",
                        "value": isOnChange ? settings.filterParams.start_date : moment().customFormat("YYYY-MM-DD")
                    },
                    "endDate": {
                        "name": "end_date",
                        "value": isOnChange ? settings.filterParams.end_date : moment().customFormat("YYYY-MM-DD")
                    },
                    "showClearButton": true
                }
            ];

            settings.rangeDatepicker = datePickerOptions;

            it.prepareDatePickerFilter(true);
        } else if (value === "dynamic") {


            var dynamicOption = rangeOptions.find(function (option) {
                return option.value === "dynamic";
            });

            var dynamicRangeFilterName = dynamicOption.name + "_dynamic";

            settings.selectedDynamicRange = settings.filterParams[dynamicRangeFilterName];
            settings.dynamicRanges = dynamicOption.dynamicRanges;
            settings.dynamicRangeFilterName = dynamicRangeFilterName;

            it.prepareDynamicFilterDomAndEvents();

            if (isOnChange) {

                var filterMap = it.filterElements[dynamicRangeFilterName];
                if (filterMap) {
                    if (settings.dynamicRanges) {

                        if (!settings.selectedDynamicRange || !settings.dynamicRanges.includes(settings.selectedDynamicRange)) {
                            settings.selectedDynamicRange = settings.dynamicRanges[0]; //auto select the 1st object if there is no selected value for the dynamic range.
                        }
                    }
                    filterMap.setValue(settings.selectedDynamicRange);
                }
            }


        }

        setTimeout(function () {
            feather.replace();
        }, 1);

    }


    prepareRangeRadioButtons() {

        var settings = this.settings,
            it = this;

        if (settings.rangeRadioButtons && typeof settings.rangeRadioButtons[0] !== 'undefined') {
            var rangeRadioOptions = {};
            var rangeOptions = [];

            $.each(settings.rangeRadioButtons, function (index, optionObj) {

                $.each(optionObj.options, function (i, option) {
                    rangeOptions.push({
                        text: AppLanugage[option],
                        name: optionObj.name,
                        value: option,
                        isChecked: option === optionObj.selectedOption,
                        dynamicRanges: optionObj.dynamicRanges,
                        selectedDynamicRange: optionObj.selectedDynamicRange
                    });
                });

                rangeRadioOptions.rangeOptions = rangeOptions;

            });


            rangeRadioOptions.onInit = function (value) {
                it.initDynamicFilter(rangeOptions, value);
            }
            rangeRadioOptions.onChange = function (value) {
                it.initDynamicFilter(rangeOptions, value, true);
            };


            it.prepareRadioFilter(rangeRadioOptions);


        }
    }

    prepareDynamicFilterDomAndEvents() {
        var settings = this.settings,
            it = this,
            $instance = this.$instance,
            $instanceWrapper = this.$instanceWrapper;

        if (typeof settings.dynamicRanges !== 'undefined' && settings.dynamicRanges.length > 0) {
            var defaultRanges = it.getDynamicDateRanges();

            var buttonDom = '<div class="filter-item-box">' +
                '<div class="btn-group">';


            if (settings.selectedDynamicRange && settings.dynamicRanges) {
                if (!settings.dynamicRanges.includes(settings.selectedDynamicRange)) {
                    settings.selectedDynamicRange = settings.dynamicRanges[0]; //auto select the 1st object if there is no selected value for the dynamic range.
                }
            }


            var dropDownId = "dynamic-range-dropdown";

            buttonDom += '<div class="dropdown dynamic-range-filter">' +
                '<button class="btn btn-default dropdown-toggle caret" type="button" id="' + dropDownId + '" data-bs-toggle="dropdown">' +
                AppLanugage[settings.selectedDynamicRange] +
                '</button>' +
                '<div class="dropdown-menu"><ul class="list-group">';


            $.each(settings.dynamicRanges, function (index, range) {
                var activeClass = "";
                if (range == settings.selectedDynamicRange) {
                    activeClass = " active ";
                }
                buttonDom += '<li class="list-group-item clickable' + activeClass + '" data-range="' + range + '">' + AppLanugage[range] + '</li>';
            });

            buttonDom += '</ul></div>' +
                '</div>';

            buttonDom += '</div></div>';

            it.appendFilterDom(buttonDom, true);

            var filterName = settings.dynamicRangeFilterName;
            var $dynamicDropDown = $instanceWrapper.find('#' + dropDownId);

            var selectDynamicFilter = function (rangeName) {

                if (defaultRanges && defaultRanges[rangeName]) {
                    settings.filterParams.start_date = defaultRanges[rangeName][0];
                    settings.filterParams.end_date = defaultRanges[rangeName][1];
                    settings.filterParams[filterName] = rangeName;
                }

                $dynamicDropDown.html(AppLanugage[rangeName]);

                $dynamicDropDown.closest(".dropdown").find('.list-group-item.active').removeClass("active");
                $dynamicDropDown.closest(".dropdown").find('.list-group-item[data-range="' + rangeName + '"]').addClass("active");
            }


            it.filterElements[filterName] = {
                setValue: function (value) {
                    if (value) {
                        selectDynamicFilter(value);
                    }

                }
            };

            var $dropdownItem = $dynamicDropDown.closest(".dropdown").find('.dropdown-menu .list-group-item');
            $dropdownItem.on('click', function () {
                var range_name = $(this).data('range');

                if (range_name) {
                    selectDynamicFilter(range_name);
                    it.reloadInstance();
                }

            });



        }
    }

}

var buildFilterDom = function (settings, $instanceWrapper, $instance) {
    var filters = new BuildFilters(settings, $instanceWrapper, $instance);
    filters.init();
};

if (typeof TableTools != 'undefined') {
    TableTools.DEFAULTS.sSwfPath = AppHelper.assetsDirectory + "js/datatable/TableTools/swf/copy_csv_xls_pdf.swf";
}

var $appFilterXhrRequest = 'new';

(function ($) {
    //appTable using datatable
    $.fn.appTable = function (options) {

        //set default display length
        var displayLength = AppHelper.settings.displayLength * 1;

        if (isNaN(displayLength) || !displayLength) {
            displayLength = 10;
        }

        var responsive = false;
        if (AppHelper.settings.disableResponsiveDataTable === "1") {
            responsive = false;
        } else if ((AppHelper.settings.disableResponsiveDataTableForMobile !== "1") && (window.outerWidth < 800)) {
            responsive = true;
        }

        var defaults = {
            source: "", //data url
            columns: [], //column title and options
            order: [[0, "asc"]], //default sort value
            serverSide: false,
            xlsColumns: [], // array of excel exportable column numbers
            printColumns: [], // array of printable column numbers
            pdfColumns: [], // array of pdf exportable column numbers
            hideTools: false, //show/hide tools section
            columnShowHideOption: true, //show a option to show/hide the columns,
            tableRefreshButton: false, //show a option to refresh the table
            displayLength: displayLength, //default rows per page
            filterParams: { datatable: true }, //will post this vales on source url
            smartFilterIdentity: null, //a to z and _ only. should be unique to avoid conflicts 
            ignoreSavedFilter: false, //sometimes, need to click on widget link to show specific filter. Enable for that. 
            dateRangeType: "", // type: daily, weekly, monthly, yearly. output params: start_date and end_date
            checkBoxes: [], // [{text: "Caption", name: "status", value: "in_progress", isChecked: true}] 
            multiSelect: [], // [{text: "Caption", name: "status", options:[{text: "Caption", value: "in_progress", isChecked: true}]}] 
            radioButtons: [], // [{text: "Caption", name: "status", value: "in_progress", isChecked: true}] 
            filterDropdown: [], // [{id: 10, text:'Caption', isSelected:true}] 
            singleDatepicker: [], // [{name: '', value:'', options:[]}] 
            rangeDatepicker: [], // [{startDate:{name:"", value:""},endDate:{name:"", value:""}}] 
            rangeRadioButtons: [], // [{options: ['monthly', 'yearly', 'custom', 'dynamic'], name: anything_range_radio_button, selectedOption: 'monthly', rangeFromName:"", rangeToName:""}]
            isMobile: window.matchMedia("(max-width: 800px)").matches,
            responsive: responsive, //by default, apply the responsive design only on the mobile view
            stateSave: true, //save user state
            stateDuration: 60 * 60 * 24 * 60, //remember for 60 days
            summation: "", /* {column: 5, dataType: 'currency'}  dataType:currency, time */
            onDeleteSuccess: function () { },
            onUndoSuccess: function () { },
            onInitComplete: function () { },
            footerCallback: function (row, data, start, end, display) { },
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) { },
            onRelaodCallback: function () { },
            reloadHooks: [],
            customLanguage: {
                noRecordFoundText: AppLanugage.noRecordFound,
                searchPlaceholder: AppLanugage.search,
                printButtonText: AppLanugage.print,
                excelButtonText: AppLanugage.excel,
                printButtonToolTip: AppLanugage.printButtonTooltip,
                today: AppLanugage.today,
                yesterday: AppLanugage.yesterday,
                tomorrow: AppLanugage.tomorrow
            }
        };

        var $instance = $(this);

        //check if this binding with a table or not
        if (!$instance.is("table")) {
            console.log("appTable: Element must have to be a table", this);
            return false;
        }

        $instance.on('length.dt page.dt order.dt search.dt', function () {
            setTimeout(function () {
                feather.replace();
            }, 1);
        });

        var settings = $.extend({}, defaults, options);


        // reload
        var instanceId = $(this).attr("id");

        if (settings.reload) {
            var table = $(this).dataTable();

            var instanceSettings = {};

            if (window.InstanceCollection) {
                instanceSettings = window.InstanceCollection[instanceId];
            }

            if (!instanceSettings) {
                instanceSettings = settings;
            }
            var tableId = table.get(0).id;

            if (instanceSettings.serverSide) {
                window.appTables[tableId]._fnReDraw();
            } else if (table && table.fnReloadAjax) {
                table.fnReloadAjax(instanceSettings.filterParams);
            }

            if ($(this).data("onRelaodCallback")) {
                $(this).data("onRelaodCallback")(table, instanceSettings.filterParams);
            }

            //Reset selection after reload
            $("#" + table.attr("id")).trigger("reset-selection-menu");

            return false;
        }



        // add/edit row
        if (settings.newData) {

            var table = $(this).dataTable();

            if (settings.dataId) {
                //check for existing row; if found, delete the row; 

                var $row = $(this).find("[data-post-id='" + settings.dataId + "']");

                if (!$row.length) {
                    $row = $(this).find("[data-index-id='" + settings.dataId + "']");
                }

                if ($row.length) {
                    // .fnDeleteRow($row.closest('tr'));

                    table.api().row(table.api().row($row.closest('tr')).index()).data(settings.newData);

                    table.fnUpdateRow(null, table.api().page()); //update existing row
                } else {
                    table.fnUpdateRow(settings.newData); //add new row
                }


            } else if (settings.rowDeleted) {
                table.fnUpdateRow(settings.newData, table.api().page(), true); //refresh row after delete
            } else {
                table.fnUpdateRow(settings.newData); //add new row
            }

            return false;
        }



        var matchesQuery = function (data, query) {
            return Object.entries(query).every(([key, value]) => data[key] === value);
        }


        var matchesHookFilter = function (postData, filterData) {

            var filterArray = Object.entries(filterData).map(([key, value]) => ({
                name: key,
                value: value
            }));

            return filterArray.every(filterItem => {
                return postData.some(postItem =>
                    postItem.name == filterItem.name && postItem.value == filterItem.value
                );
            });
        }

        var updateSingleRow = function (settings, id) {
            var postData = {};
            postData.id = id;
            postData.server_side = 0; //disable server side for this request

            appAjaxRequest({
                url: settings.source,
                type: 'POST',
                dataType: 'json',
                data: postData,
                success: function (result) {
                    if (result.data && result.data[0]) {
                        $("#" + instanceId).appTable({
                            newData: result.data[0],
                            dataId: id
                        });
                    }
                }
            });

        }


        $.each(settings.reloadHooks || [], function (index, hook) {

            if (hook.type === "app_form" && hook.id) {
                registerAppFormHook(hook.id, function (appFormPostData, appFormSuccessResult) {

                    if (hook.filter && !matchesQuery(appFormPostData, hook.filter)) return "continue";

                    if (!appFormPostData) appFormPostData = {};

                    var idField = hook.mapPostData && hook.mapPostData.id ? hook.mapPostData.id : "id";

                    var listDataId = appFormPostData[idField];

                    if (!listDataId && appFormSuccessResult.id) {
                        listDataId = appFormSuccessResult.id;
                    }
                    updateSingleRow(settings, listDataId);
                }, "appTable", instanceId);

            } else if (hook.type === "ajax_request" && hook.group) {
                registerAjaxRequestHook(hook.group, function (ajaxRequestPostData) {

                    if (!ajaxRequestPostData) ajaxRequestPostData = {};

                    var idField = hook.mapPostData && hook.mapPostData.id ? hook.mapPostData.id : "id";

                    var listDataId = ajaxRequestPostData[idField];

                    if (!listDataId) {
                        console.log("The id data is missing on the ajaxRequestData");
                        return false;
                    }

                    updateSingleRow(settings, listDataId);

                }, "appTable", instanceId);
            } else if (hook.type === "app_modifier" && hook.group) {
                registerAppModifierHook(hook.group, function (appModifierData, result) {

                    if (!appModifierData) appModifierData = {};

                    var idField = hook.mapPostData && hook.mapPostData.id ? hook.mapPostData.id : "id";

                    var listDataId = appModifierData[idField];

                    if (!listDataId) {
                        console.log("The id data is missing on the appModifierData");
                        return false;
                    }

                    updateSingleRow(settings, listDataId);

                }, "appTable", instanceId);

            } else if (hook.type === "app_table_row_update" && hook.tableId) {
                registerAppTableRowUpdateHook(hook.tableId, function (appTableRowUpdateData) {

                    if (!appTableRowUpdateData) appTableRowUpdateData = {};

                    var idField = hook.mapPostData && hook.mapPostData.id ? hook.mapPostData.id : "id";

                    var listDataId = appTableRowUpdateData[idField];

                    if (!listDataId) {
                        console.log("The id data is missing on the appTableRowUpdateData");
                        return false;
                    }

                    updateSingleRow(settings, listDataId);

                }, "appTable", instanceId);

            }
            //add other hooks here.

        });


        //add nowrap class in responsive view
        if (settings.responsive) {
            $instance.addClass("nowrap");
        }



        var _prepareFooter = function (settings, page, lable) {
            var tr = "",
                trSection = '';

            if (page === "all") {
                trSection = 'data-section="all_pages"';
            }

            tr += "<tr " + trSection + ">";

            $.each(settings.columns, function (index, column) {

                var thAttr = "class = 'tf-blank' ",
                    thLable = " ";


                if (settings.summation[0] && settings.summation[0].column - 1 === index) {
                    thLable = lable;
                    thAttr = "class = 'tf-lable' ";
                }

                $.each(settings.summation, function (fIndex, sumColumn) {
                    if (sumColumn.column === index) {
                        thAttr = "class = 'tf-result text-right' ";
                        thAttr += 'data-' + page + '-page="' + sumColumn.column + '"';
                    }
                });

                tr += "<th " + thAttr + ">";
                tr += thLable;
                tr += "</th>";

            });
            tr += "</tr>";

            return tr;

        };

        //add summation footer 
        //don't add it on mobile view. We'll show another field in mobile view.

        if (settings.summation && settings.summation.length && !settings.isMobile) {
            var content = "<tfoot>";

            content += _prepareFooter(settings, 'current', AppLanugage.total);
            content += _prepareFooter(settings, 'all', AppLanugage.totalOfAllPages);

            content += "</tfoot>";

            $instance.html(content);
        }




        settings._visible_columns = [];

        //check if there is any 'all' class with any column
        //if so, add the 'desktop' class with other columns
        var hasAllClass = false;
        if (settings.columns.find(column => column.class && column.class.includes('all'))) {
            hasAllClass = true;
        }

        $.each(settings.columns, function (index, column) {
            if (column.visible !== false) {
                settings._visible_columns.push(index);
            }

            //set orderable: false if serverSide:true and don't have order_by reference. 
            //also check if dependant sorting column has order_by reference
            var orderable = false;

            if (settings.serverSide) {
                if (column.order_by) {
                    orderable = true;
                } else if (!column.order_by && column.iDataSort && settings.columns[column.iDataSort].order_by) {
                    orderable = true;
                }
            } else {
                if (column.sortable !== false) {
                    orderable = true;
                }
            }

            settings.columns[index].orderable = orderable;
            if (hasAllClass && settings.isMobile) {
                if ((column.class && !column.class.includes("all")) || !column.class) {
                    settings.columns[index].class = settings.columns[index].class ? settings.columns[index].class + " desktop" : "desktop";
                }
            }

            if (!settings.isMobile && settings.mobileMirror) {
                if ((column.class && !column.class.includes("all")) || !column.class) {
                    settings.columns[index].class = settings.columns[index].class ? settings.columns[index].class + " mobile-only" : "mobile-only";
                }
            }
        });


        settings._exportable = settings.xlsColumns.length + settings.pdfColumns.length + settings.printColumns.length;
        settings._firstDayOfWeek = AppHelper.settings.firstDayOfWeek || 0;
        settings._inputDateFormat = "YYYY-MM-DD";


        settings = prepareDefaultFilters(settings);

        var aLengthMenu = [[10, 25, 50, 100, -1], [10, 25, 50, 100, AppLanugage.all]];
        if (settings.serverSide) {
            var menuOptions = [10, 25, 50, 100];

            if (window.addAppTableDisplayOption && Number(window.addAppTableDisplayOption) > 0) {
                menuOptions.push(Number(window.addAppTableDisplayOption));
            }

            aLengthMenu = [menuOptions, menuOptions];
        }



        var responsive = settings.responsive,
            stateSave = cloneDeep(settings.stateSave),
            displayLength = cloneDeep(settings.displayLength);


        if (!settings.isMobile && settings.mobileMirror) {
            responsive = {
                breakpoints: [
                    { name: 'all', width: Infinity },
                    { name: 'mobile-only', width: 480 }
                ]
            };
            stateSave = false;
            displayLength = 25;
        }


        var datatableOptions = {

            // sAjaxSource: settings.source,

            ajax: {
                url: settings.source,
                type: "POST",
                data: function (postData) {



                    var order_by_index = (postData.order && postData.order[0]) ? postData.order[0].column : "",
                        order_dir = (postData.order && postData.order[0]) ? postData.order[0].dir : "",
                        search = postData.search ? postData.search['value'] : "";

                    if (order_dir) {
                        order_dir = order_dir.toUpperCase();
                    }


                    var server_side = 0;
                    if (settings.serverSide) {
                        server_side = 1;
                    }

                    return $.extend({
                        order_by: settings.columns[order_by_index] ? settings.columns[order_by_index].order_by : "",
                        order_dir: order_dir,
                        search_by: search,
                        skip: postData.start,
                        limit: postData.length,
                        draw: postData.draw,
                        server_side: server_side
                    }, settings.filterParams);
                },
                error: function (xhr, error, thrown) {
                    appAlert.error(AppLanugage.somethingWentWrong);
                },
                dataSrc: function (response) {
                    settings.summationInfo = response.summation;
                    return response.data;
                }
            },
            sServerMethod: "POST",
            columns: settings.columns,
            bProcessing: true,
            serverSide: settings.serverSide,
            iDisplayLength: displayLength,
            aLengthMenu: aLengthMenu,
            bAutoWidth: false,
            bSortClasses: false,
            order: settings.order,
            stateSave: stateSave,
            responsive: responsive,
            fnStateLoadParams: function (oSettings, oData) {
                //if the stateSave is true, we'll remove the search value after next reload. 
                if (oData && oData.search) {
                    oData.search.search = "";
                }

            },
            stateDuration: settings.stateDuration,
            fnInitComplete: function () {
                settings.onInitComplete(this);
            },
            language: {
                lengthMenu: "_MENU_",
                zeroRecords: settings.customLanguage.noRecordFoundText,
                info: "_START_-_END_ / _TOTAL_",
                sInfo: "_START_-_END_ / _TOTAL_",
                infoFiltered: "(_MAX_)",
                search: "",
                searchPlaceholder: settings.customLanguage.searchPlaceholder,
                sInfoEmpty: "0-0 / 0",
                sInfoFiltered: "(_MAX_)",
                sInfoPostFix: "",
                sInfoThousands: ",",
                sProcessing: "<div class='table-loader'><span class='loading'></span></div>",
                "oPaginate": {
                    "sPrevious": "<i data-feather='chevron-left' class='icon-16'></i>",
                    "sNext": "<i data-feather='chevron-right' class='icon-16'></i>"
                }

            },
            sDom: "",
            footerCallback: function (row, data, start, end, display) {
                var instance = this;
                if (settings.summation) {

                    var pageInfo = instance.api().page.info(),
                        summationContent = "",
                        pageTotalContent = "",
                        allPageTotalContent = "";

                    if (pageInfo.recordsTotal) {
                        $(instance).find("tfoot").show();
                    } else {
                        $(instance).find("tfoot").hide();
                        return false;
                    }

                    $.each(settings.summation, function (index, option) {
                        // total value of current page
                        var pageTotal = calculateDatatableTotal(instance, option.column, function (currentValue) {

                            //if we get <b> tag, we'll assume that is a group total. ignore the value
                            if (currentValue && !currentValue.startsWith("<b>")) {
                                if (option.dataType === "currency") {
                                    if (option.dynamicSymbol) { //find out currency symbol 
                                        var x = currentValue;
                                        option.currencySymbol = x.replace(/[0-9.,-]/g, "");
                                    }

                                    return unformatCurrency(currentValue, option.conversionRate);
                                } else if (option.dataType === "time") {
                                    return moment.duration(currentValue).asSeconds();
                                } else if (option.dataType === "number") {
                                    return unformatCurrency(currentValue);
                                } else {
                                    return currentValue;
                                }
                            } else {
                                return 0;
                            }

                        }, true);

                        if (option.dataType === "currency") {
                            pageTotal = toCurrency(pageTotal, option.currencySymbol);
                        } else if (option.dataType === "time") {
                            pageTotal = secondsToTimeFormat(pageTotal);
                        } else if (option.dataType === "number") {
                            pageTotal = toCurrency(pageTotal, "none");
                        }

                        var pagTotalTitle = table.column(option.column).header();
                        if (pagTotalTitle) {
                            pageTotalContent += "<div class='box'><div class='box-content'>" + $(pagTotalTitle).html() + "</div><div class='box-content text-right'>" + pageTotal + "</div></div>";
                        }

                        $(instance).find("[data-current-page=" + option.column + "]").html(pageTotal);

                        // total value of all pages
                        if (pageInfo.pages > 1) {
                            $(instance).find("[data-section='all_pages']").show();

                            var total = 0;

                            if (settings.serverSide) {
                                var summationInfo = settings.summationInfo;

                                if (summationInfo && option.fieldName) {
                                    total = summationInfo[option.fieldName] ? summationInfo[option.fieldName] : 0;
                                }

                            } else {
                                total = calculateDatatableTotal(instance, option.column, function (currentValue) {
                                    //if we get <b> tag, we'll assume that is a group total. ignore the value
                                    if (currentValue && !currentValue.startsWith("<b>")) {
                                        if (option.dataType === "currency") {
                                            return unformatCurrency(currentValue, option.conversionRate);
                                        } else if (option.dataType === "time") {
                                            return moment.duration(currentValue).asSeconds();
                                        } else if (option.dataType === "number") {
                                            return unformatCurrency(currentValue);
                                        } else {
                                            return currentValue;
                                        }
                                    } else {
                                        return 0;
                                    }
                                });
                            }



                            if (option.dataType === "currency") {
                                total = toCurrency(total, option.currencySymbol);
                            } else if (option.dataType === "time") {
                                total = secondsToTimeFormat(total);
                            } else if (option.dataType === "number") {
                                total = toCurrency(total, "none");
                            }

                            var title = table.column(option.column).header();
                            if (title) {
                                allPageTotalContent += "<div class='box'><div class='box-content'>" + $(title).html() + "</div><div class='box-content text-right'>" + total + "</div></div>";
                            }

                            $(instance).find("[data-all-page=" + option.column + "]").html(total);
                        } else {
                            $(instance).find("[data-section='all_pages']").hide();
                        }
                    });



                    //add summation section for mobile view.

                    if (settings.isMobile || settings.mobileMirror) {
                        if (pageTotalContent) {
                            summationContent += "<div class='box'><div class='box-content strong'>" + AppLanugage.total + "</div></div>" + pageTotalContent;
                        }
                        if (allPageTotalContent) {
                            summationContent += "<div class='box'><div class='box-content strong'>" + AppLanugage.totalOfAllPages + "</div></div>" + allPageTotalContent;
                        }

                        $(".summation-section").html(summationContent);
                    }

                }

                settings.footerCallback(row, data, start, end, display, instance);
            },
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                settings.rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull);
            },
            preDrawCallback: function (settings) {
                if (settings.aoData.length > 0) {
                    $(".summation-section").removeClass("hide");
                } else {
                    $(".summation-section").addClass("hide");
                }

            }
        };



        //to save the datatatable state in cookie, we'll use the user's reference.
        //sometime the same user (most of the time the admin user) will login to different account to check. 
        //since the table columns are different for different users, 
        //we'll save the coockie based on table reference + user reference 

        if (AppHelper.userId) {

            datatableOptions.stateSaveParams = function (dataTableSettings, data) {
                if (dataTableSettings.sInstance.indexOf("-user-ref-") === -1) {
                    dataTableSettings.sInstance += "-user-ref-" + AppHelper.userId;
                }
            };


            datatableOptions.stateLoadCallback = function (dataTableSettings) {
                if (dataTableSettings.sInstance.indexOf("-user-ref-") === -1) {
                    dataTableSettings.sInstance += "-user-ref-" + AppHelper.userId;
                }
                try {

                    var pathname = location.pathname;
                    if (settings.mobileMirror) {
                        pathname = pathname.replace(/\/compact_view\/.*/, '');
                    }

                    return JSON.parse(
                        (dataTableSettings.iStateDuration === -1 ? sessionStorage : localStorage).getItem(
                            'DataTables_' + dataTableSettings.sInstance + '_' + pathname
                        )
                    );
                } catch (e) {
                }
            };
        }




        var sDomExport = "";

        if (settings._exportable) {
            var datatableButtons = [];

            if (settings.xlsColumns.length) {
                //add excel button

                datatableButtons.push({
                    extend: 'excelHtml5',
                    footer: true,
                    text: settings.customLanguage.excelButtonText,
                    exportOptions: {
                        columns: settings.xlsColumns
                    },
                    customize: function (xls) {
                        //append the total of all pages row, if it exists
                        if ($instance.find("[data-section='all_pages']") && $instance.find("[data-section='all_pages']").css('display') !== "none") {
                            var sheet = xls.xl.worksheets['sheet1.xml'];

                            var $sheetSelector = $(sheet.childNodes[0].childNodes[1]);
                            var thisRowNumber = parseInt($sheetSelector.find("row:last-child").attr("r")) + 1;

                            //here should define the actual position of the item using the abc character
                            var chars = 'abcdefghijklmnopqrstuvwxyz',
                                rowCounted = 0;

                            var rowDom = '<row r="' + thisRowNumber + '">';

                            $instance.find("[data-section='all_pages'] th").each(function () {
                                if ($(this).text()) {
                                    rowDom += '<c t="inlineStr" r="' + chars[rowCounted].toUpperCase() + thisRowNumber + '" s="2">';
                                    rowDom += '<is>';
                                    rowDom += '<t>' + $(this).text() + '</t>';
                                    rowDom += '</is>';
                                    rowDom += '</c>';
                                }

                                //increase the position variable
                                rowCounted = rowCounted + 1;
                            });

                            rowDom += '</row>';

                            //add the row finally
                            sheet.childNodes[0].childNodes[1].innerHTML = sheet.childNodes[0].childNodes[1].innerHTML + rowDom;
                        }
                    }
                });
            }

            if (settings.pdfColumns.length) {
                //add pdf button
                datatableButtons.push({
                    extend: 'pdfHtml5',
                    exportOptions: {
                        // columns: settings.pdfColumns
                        columns: ':visible:not(.option)'
                    }
                });
            }

            if (settings.printColumns.length) {
                datatableButtons.push({
                    extend: 'print',
                    autoPrint: false,
                    text: settings.customLanguage.printButtonText,
                    footer: true,
                    exportOptions: {
                        columns: settings.printColumns
                    },
                    customize: function (win) {
                        $(win.document.body).closest("html").addClass("dt-print-view");

                        //append the total of all pages row, if it exists
                        if ($instance.find("[data-section='all_pages']") && $instance.find("[data-section='all_pages']").css('display') !== "none") {
                            var totalOfAllPagesClone = $instance.find("[data-section='all_pages']").clone();
                            $(win.document.body).find("tfoot").append(totalOfAllPagesClone);
                        }
                    },
                    customizeData: function (a, b, c) {

                    }
                });
            }

            datatableOptions.buttons = datatableButtons;

            sDomExport = "<'datatable-export filter-item-box'B >";
        }

        var filterFormDom = "";
        if (settings.smartFilterIdentity) {
            filterFormDom = "<'filter-form'>";
        }

        var sDomExportMobile = "";
        var footerSection1Class = "col-md-3",
            footerSection2Class = "col-md-9";

        if (settings.isMobile || settings.mobileMirror) {
            sDomExportMobile = sDomExport;
            sDomExport = ""; //show the export button on the bottom for mobile devices
            footerSection1Class = "col-md-12";
            footerSection2Class = "col-md-12 mini-list-pagination-container";
        }

        //set custom toolbar
        if (!settings.hideTools) {
            datatableOptions.sDom = "<'filter-section-container' <'filter-section-flex-row' <'filter-section-left'> <'filter-section-right' " + sDomExport + " <'filter-item-box' f> > > " + filterFormDom + " r>t<'datatable-tools clearfix row'<'" + footerSection1Class + " pl15'<'summation-section'> <'table-bottom-left' li ><'float-end'" + sDomExportMobile + ">><'" + footerSection2Class + " pr15'p>>";
        }

        datatableOptions.drawCallback = function () {
            if (settings.serverSide) {
                var $searchBox = $instance.closest(".dataTables_wrapper").find("input[type=search]");
                if (!$searchBox.val() && settings.filterParams && settings.filterParams.search_by) {
                    $searchBox.val(settings.filterParams && settings.filterParams.search_by);
                }
            }
        };

        var oTable = $instance.dataTable(datatableOptions),
            $instanceWrapper = $instance.closest(".dataTables_wrapper");

        var tableId = $instance.get(0) ? $instance.get(0).id : "id_not_found";

        if (!window.appTables) {
            window.appTables = [];
        }
        window.appTables[tableId] = oTable;

        $instanceWrapper.find("select").select2({
            minimumResultsForSearch: -1
        });


        //add the column show/hide option
        if (settings.columnShowHideOption) {

            var tableId = $instance.attr("id");
            table = $instance.DataTable();

            //prepare a popover
            var popover = '<button class="btn btn-default column-show-hide-popover" data-container="body" data-bs-toggle="popover" data-placement="bottom"><i data-feather="columns" class="icon-16"></i></button>';

            if (settings.isMobile || settings.mobileMirror) {
                // $instanceWrapper.find(".table-bottom-left").prepend('<div class="float-start mr10">' + popover + '</div>');
            } else {
                $instanceWrapper.find(".filter-section-left").append('<div class="filter-item-box">' + popover + '</div>');
            }


            //prepare the list of columns when opening the popover
            $instanceWrapper.find(".column-show-hide-popover").popover({
                html: true,
                sanitize: false,
                content: function () {
                    var tableColumns = "";

                    $.each(settings.columns, function (index, column) {
                        //in coulmn list, show only the visible columns
                        if (column.visible !== false) {

                            var tableColumn = table.column(index),
                                columnHiddenClass = "",
                                eyeOnOffIcon = "";

                            if (!tableColumn.visible()) {
                                columnHiddenClass = "active";
                                eyeOnOffIcon = "<i data-feather='eye-off' class='icon-16 mr10'></i>";
                            }


                            //prepare a list of columns
                            tableColumns += "<li class='" + columnHiddenClass + " list-group-item clickable toggle-table-column' data-column='" + index + "'>" + eyeOnOffIcon + column.title + "</li>";
                        }
                    });

                    return "<ul class='list-group' data-table='" + tableId + "'>" + tableColumns + "</ul>";

                }
            });


            //show/hide column when clicking on the list items    

            $instanceWrapper.find(".column-show-hide-popover").on('shown.bs.popover', function () {
                feather.replace();

                $(".toggle-table-column").on('click', function () {

                    var instanceId = $(this).closest(".list-group").attr("data-table");

                    var column = $("#" + instanceId).DataTable().column($(this).attr('data-column'));


                    // check the actual status of the table column and toggle it
                    if (column) {
                        column.visible(!column.visible());

                        $(this).toggleClass("active");
                    }

                });
            });

        }


        if (settings.tableRefreshButton) {
            //prepare a refreshButton

            var refreshButton = '<div class="filter-item-box float-start "><button class="btn btn-default at-table-refresh-button ml15"><i data-feather="refresh-cw" class="icon-16"></i></button></div>';
            $instanceWrapper.find(".filter-section-left").append(refreshButton);

            $instanceWrapper.find(".at-table-refresh-button").on('click', function () {
                $instance.appTable({ reload: true, filterParams: settings.filterParams });
            });
        }

        //hide popover when clicks on outside of the popover
        if (!$('body').hasClass("destroy-popover")) {
            $('body').addClass("destroy-popover"); //don't initiate this multiple time

            $('.destroy-popover').on('click', function (e) {
                if ($(e.target).closest("button").attr("data-bs-toggle") !== "popover" && !$(e.target).closest(".popover").length && !$(e.target).hasClass("editable")) {
                    var visiblePopoverId = $(".popover.in").attr("id");
                    $("[aria-describedby=" + visiblePopoverId + "]").trigger("click");

                }
            });
        }

        //set onReloadCallback
        $instance.data("onRelaodCallback", settings.onRelaodCallback);

        // add delay in search when applied serverside
        if (settings.serverSide) {
            var $searchBox = $instanceWrapper.find("input[type=search]");

            $searchBox.unbind().bind('input', (delayAction(function (e) {
                settings.filterParams.search_by = $(this).val();
                $instance.appTable({ reload: true, filterParams: settings.filterParams });
                return;
            }, 1000)));

            //search datatable when clicks on the labels.
            $('body').on('click', "#" + $instance.get(0).id + ' .badge.clickable', function () {
                var isSelectionMode = $(this).closest(".js-selection-mode").length;
                if (isSelectionMode) return false;

                settings.filterParams.search_by = $(this).text();
                $instance.appTable({ reload: true, filterParams: settings.filterParams });
                return false;
            });

            //search datatable when clicks on filter sub task icon
            $('body').on('click', "#" + $instance.get(0).id + ' .filter-sub-task-button', function () {
                settings.filterParams.search_by = $(this).attr('main-task-id');
                $instance.appTable({ reload: true, filterParams: settings.filterParams });
                return false;
            });

            //remove sub tasks filter
            $('body').on('click', "#" + $instance.get(0).id + ' .remove-filter-button', function () {
                settings.filterParams.search_by = "";
                $instance.appTable({ reload: true, filterParams: settings.filterParams });
                return false;
            });


        } else {
            //if not serverSide, then just re-draw the table when clicks on the labels. 
            $('body').on('click', "#" + $instance.get(0).id + ' .badge.clickable', function () {
                var value = $(this).text();

                $(this).closest(".dataTable").DataTable().search(value).draw();
                $(this).closest(".dataTables_wrapper").find("input[type=search]").val(value).focus().select();
                return false;
            });
        }



        buildFilterDom(settings, $instanceWrapper, $instance);
        var undoHandler = function (eventData) {
            $('<a class="undo-delete" href="javascript:;"><strong>' + AppLanugage.undo + '</strong></a>').insertAfter($(eventData.alertSelector).find(".app-alert-message"));
            $(eventData.alertSelector).find(".undo-delete").bind("click", function () {
                $(eventData.alertSelector).remove();
                appLoader.show();
                appAjaxRequest({
                    url: eventData.url,
                    type: 'POST',
                    dataType: 'json',
                    data: { id: eventData.id, undo: true },
                    success: function (result) {
                        appLoader.hide();
                        if (result.success) {
                            $instance.appTable({ newData: result.data, rowDeleted: true });
                            //fire success callback
                            settings.onUndoSuccess(result);

                            var tableId = $instance.attr("id");
                            if (tableId && window.appTableRowDeleteHook && window.appTableRowDeleteHook[tableId]) {

                                window.appTableRowDeleteHook[tableId].forEach(function (hook) {
                                    if (typeof hook.onSuccess === 'function') {
                                        hook.onSuccess({ id: eventData.id });
                                    }
                                });
                            }
                        }
                    }
                });
            });
        };


        var rowDeleteHandler = function (result, $target) {
            var tr = $target.closest('tr'),
                table = $instance.DataTable(),
                undo = $target.attr('data-undo'),
                url = $target.attr('data-action-url'),
                id = $target.attr('data-id');

            if (tr.hasClass("child")) {
                tr = tr.prev('.parent');
            }

            oTable.fnDeleteRow(table.row(tr).index(), function () {
                table.page(table.page()).draw('page');
            }, false);

            var alertId = appAlert.warning(result.message, { duration: 20000 });

            //fire success callback
            settings.onDeleteSuccess(result);

            //bind undo selector
            if (undo !== "0") {
                undoHandler({
                    alertSelector: alertId,
                    url: url,
                    id: id
                });
            }
        };

        var appTableDeleteConfirmationHandler = function (e) {
            deleteConfirmationHandler(e, rowDeleteHandler);
        };

        var appTableSimpleDeleteHandler = function (e) {
            deleteHandler(e, rowDeleteHandler);
        };

        var updateHandler = function (e) {
            appLoader.show();
            var $target = $(e.currentTarget);

            if (e.data && e.data.target) {
                $target = e.data.target;
            }

            var url = $target.attr("data-action-url");
            var tableId = $target.closest("table").attr("id");

            appAjaxRequest({
                url: url,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        if (response.data) {
                            $("#" + tableId).appTable({ newData: response.data, dataId: response.id });
                        }

                        appAlert.success(response.message, { duration: 10000 });

                        if (tableId && window.appTableRowUpdateHook && window.appTableRowUpdateHook[tableId]) {

                            window.appTableRowUpdateHook[tableId].forEach(function (hook) {
                                if (typeof hook.onSuccess === 'function') {
                                    hook.onSuccess({ id: response.id });
                                }
                            });
                        }

                    } else {
                        appAlert.error(response.message);
                    }
                    appLoader.hide();
                }
            });
        };


        window.InstanceCollection = window.InstanceCollection || {};
        window.InstanceCollection[$(this).attr("id")] = settings;

        $('body').find($instance).on('click', 'a[data-action=delete]', appTableSimpleDeleteHandler);
        $('body').find($instance).on('click', 'a[data-action=delete-confirmation]', appTableDeleteConfirmationHandler);
        $('body').find($instance).on('click', '[data-action=update]', updateHandler);

        $.fn.dataTableExt.oApi.getSettings = function (oSettings) {
            return oSettings;
        };

        $.fn.dataTableExt.oApi.fnReloadAjax = function (oSettings, filterParams) {
            this.fnClearTable(this);
            this.oApi._fnProcessingDisplay(oSettings, true);
            var that = this;

            if ($appFilterXhrRequest !== 'new') {
                //an another xhr request is already running
                return;
            }

            $appFilterXhrRequest = $.ajax({
                url: oSettings.ajax.url,
                type: "POST",
                dataType: "json",
                data: filterParams,
                success: function (json) {
                    $appFilterXhrRequest = 'new';

                    /* Got the data - add it to the table */
                    for (var i = 0; i < json.data.length; i++) {
                        that.oApi._fnAddData(oSettings, json.data[i]);
                    }

                    oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
                    that.fnDraw(that);
                    that.oApi._fnProcessingDisplay(oSettings, false);
                }
            });
        };
        $.fn.dataTableExt.oApi.fnUpdateRow = function (oSettings, data, page, renderBeforePageChange) {
            //oSettings is not any parameter, we'll get it automatically.

            // var serverSideOrigninal = oSettings.oFeatures.bServerSide;
            // if (serverSideOrigninal) {
            //     oSettings.oFeatures.bServerSide = false; //disable serverside processing temporarily
            // }

            if (data) {
                this.oApi._fnAddData(oSettings, data);
            }

            if (renderBeforePageChange) {
                this.fnDraw(this);
            }

            if (page) {
                this.oApi._fnPageChange(oSettings, page, true);
            } else {
                this.fnDraw(this);
            }

            //oSettings.oFeatures.bServerSide = serverSideOrigninal; //revert to orignal value

        };

    };
})(jQuery);


deleteHandler = function (e, callback, postData = {}) {
    appLoader.show();
    var $target = $(e.currentTarget);

    if (e.data && e.data.target) {
        $target = e.data.target;
    }

    var url = $target.attr('data-action-url'),
        id = $target.attr('data-id'),
        reloadOnSuccess = $target.attr('data-reload-on-success'),
        tableId = $target.closest('table').attr('id');

    if (!postData) {
        postData = {};
    }

    postData.id = id;

    appAjaxRequest({
        url: url,
        type: 'POST',
        dataType: 'json',
        data: postData,
        success: function (result) {
            if (result.success) {

                if (callback) {
                    callback(result, $target);
                }

                if (reloadOnSuccess) {
                    location.reload();
                }

                if (tableId && window.appTableRowDeleteHook && window.appTableRowDeleteHook[tableId]) {

                    window.appTableRowDeleteHook[tableId].forEach(function (hook) {
                        if (typeof hook.onSuccess === 'function') {
                            hook.onSuccess({ id: id });
                        }
                    });
                }

            } else {
                appAlert.error(result.message);
            }
            appLoader.hide();
        }
    });
}


deleteConfirmationHandler = function (e, callback) {
    var $deleteButton = $("#confirmDeleteButton"),
        $target = $(e.currentTarget);
    //copy attributes

    var postData = {};
    $target.each(function () {
        $.each(this.attributes, function () {
            if (this.specified && this.name.match("^data-")) {
                $deleteButton.attr(this.name, this.value);
            }

            if (this.specified && this.name.match("^data-post-")) {
                var dataName = this.name.replace("data-post-", "");
                postData[dataName] = this.value;
            }

        });
    });

    $target.attr("data-undo", "0"); //don't show undo

    //bind click event
    $deleteButton.unbind("click");
    $deleteButton.on("click", { target: $target }, function (e) {
        deleteHandler(e, callback, postData);
    });

    $("#confirmationModal").modal('show');
};



// appAlert
(function (define) {
    define(['jquery'], function ($) {
        return (function () {
            var appAlert = {
                info: info,
                success: success,
                warning: warning,
                error: error,
                options: {
                    container: "body", // append alert on the selector
                    duration: 0, // don't close automatically,
                    showProgressBar: true, // duration must be set
                    clearAll: true, //clear all previous alerts
                    animate: true //show animation
                }
            };

            return appAlert;

            function info(message, options) {
                this._settings = _prepear_settings(options);
                this._settings.alertType = "info";
                _show(message);
                return "#" + this._settings.alertId;
            }

            function success(message, options) {
                this._settings = _prepear_settings(options);
                this._settings.alertType = "success";
                _show(message);
                return "#" + this._settings.alertId;
            }

            function warning(message, options) {
                this._settings = _prepear_settings(options);
                this._settings.alertType = "warning";
                _show(message);
                return "#" + this._settings.alertId;
            }

            function error(message, options) {
                this._settings = _prepear_settings(options);
                this._settings.alertType = "error";
                _show(message);
                return "#" + this._settings.alertId;
            }

            function _template(message) {
                var className = "info";
                if (this._settings.alertType === "error") {
                    className = "danger";
                } else if (this._settings.alertType === "success") {
                    className = "success";
                } else if (this._settings.alertType === "warning") {
                    className = "warning";
                }

                if (this._settings.animate) {
                    className += " animate";
                }

                return '<div id="' + this._settings.alertId + '" class="app-alert alert alert-' + className + ' alert-dismissible " role="alert">'
                    + '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>'
                    + '<div class="app-alert-message">' + message + '</div>'
                    + '<div class="progress">'
                    + '<div class="progress-bar bg-' + className + ' hide" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 100%">'
                    + '</div>'
                    + '</div>'
                    + '</div>';
            }

            function _prepear_settings(options) {
                if (!options)
                    var options = {};
                options.alertId = "app-alert-" + _randomId();
                return this._settings = $.extend({}, appAlert.options, options);
            }

            function _randomId() {
                var id = "";
                var keys = "abcdefghijklmnopqrstuvwxyz0123456789";
                for (var i = 0; i < 5; i++)
                    id += keys.charAt(Math.floor(Math.random() * keys.length));
                return id;
            }

            function _clear() {
                if (this._settings.clearAll) {
                    $("[role='alert']").remove();
                }
            }

            function _show(message) {
                _clear();
                var container = $(this._settings.container);
                if (container.length) {
                    if (this._settings.animate) {
                        //show animation
                        setTimeout(function () {
                            $(".app-alert").animate({
                                opacity: 1,
                                right: "40px"
                            }, 500, function () {
                                $(".app-alert").animate({
                                    right: "15px"
                                }, 300);
                            });
                        }, 20);
                    }

                    $(this._settings.container).prepend(_template(message));
                    _progressBarHandler();
                } else {
                    console.log("appAlert: container must be an html selector!");
                }
            }

            function _progressBarHandler() {
                if (this._settings.duration && this._settings.showProgressBar) {
                    var alertId = "#" + this._settings.alertId;
                    var $progressBar = $(alertId).find('.progress-bar');

                    $progressBar.removeClass('hide').width(0);
                    var css = "width " + this._settings.duration + "ms ease";
                    $progressBar.css({
                        WebkitTransition: css,
                        MozTransition: css,
                        MsTransition: css,
                        OTransition: css,
                        transition: css
                    });

                    setTimeout(function () {
                        if ($(alertId).length > 0) {
                            $(alertId).remove();
                        }
                    }, this._settings.duration);
                }
            }
        })();
    });
}(function (d, f) {
    window['appAlert'] = f(window['jQuery']);
}));


(function (define) {
    define(['jquery'], function ($) {
        return (function () {
            var appLoader = {
                show: show,
                hide: hide,
                options: {
                    container: 'body',
                    zIndex: "auto",
                    css: "",
                }
            };

            return appLoader;

            function show(options) {
                var $template = $("#app-loader");
                this._settings = _prepear_settings(options);
                if (!$template.length) {
                    var $container = $(this._settings.container);
                    if ($container.length) {

                        if (!this._settings.css) {
                            this._settings.css = "top:25%; right:" + Math.round($container.outerWidth() / 2) + "px;";
                        }
                        $container.append('<div id="app-loader" class="app-loader" style="z-index:' + this._settings.zIndex + ';' + this._settings.css + '"><div class="loading"></div></div>');
                    } else {
                        console.log("appLoader: container must be an html selector!");
                    }

                }
            }

            function hide() {
                var $template = $("#app-loader");
                if ($template.length) {
                    $template.remove();
                }
            }

            function _prepear_settings(options) {
                if (!options)
                    var options = {};
                return this._settings = $.extend({}, appLoader.options, options);
            }
        })();
    });
}(function (d, f) {
    window['appLoader'] = f(window['jQuery']);
}));


(function (define) {
    define(['jquery'], function ($) {
        return (function () {
            var compactView = {
                init: init,
                setActiveRow: setActiveRow,
                _initListClickEvent: _initListClickEvent,
                _settings: {},
                options: {
                    dataSourceUrl: "",
                    backButtonUrl: "",
                    backButtonText: "",
                    compactViewBaseUrl: "",
                    compactViewId: null,

                    pageId: "#page-content",
                    pageWrapperClassName: "page-wrapper",
                    compactDetailsPageIdName: "compact-details-page",
                    appContentBuilderData: {
                        view_type: "compact_view"
                    },
                    appContentBuilderReloadHooks: [],
                }
            };

            function _initListClickEvent() {
                var _settings = this._settings;
                if (!_settings.pageId) return false;

                $(_settings.pageId).on('click', '[data-action=load_compact_view]', function () {

                    if ($(this).closest("td.all").length > 0) {
                        $(this).closest("td.all").trigger("click");
                    }



                    var $selector = $(this),
                        selectorData = $selector.data() || {},
                        $target = $("#" + _settings.compactDetailsPageIdName);

                    //change the browser url to match with compact view url
                    if (_settings.compactViewBaseUrl && selectorData.compact_view_id) {
                        var browserState = {
                            Url: _settings.compactViewBaseUrl + selectorData.compact_view_id
                        };
                        history.pushState(browserState, "", browserState.Url);

                        window.compact_view_id = selectorData.compact_view_id;

                        $selector.closest("table").find("tr.active").removeClass("active");
                        compactView.setActiveRow();
                    }

                    $target.children().fadeOut();

                    if (isMobile()) {
                        $selector.closest("table").find("tr.active .mini-list-item .spinning-btn").addClass("spinning");
                    } else {
                        appLoader.show({ container: $target });
                    }

                    var ajaxOptions = {
                        url: selectorData.actionUrl,
                        data: { view_type: "compact_view" },
                        cache: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if (isMobile()) {
                                $selector.closest("table").find("tr.active .mini-list-item .spinning-btn").removeClass("spinning");
                            }

                            appLoader.hide();
                            $target.html(response.content);
                        },
                        statusCode: {
                            404: function () {
                                appLoader.hide();
                                appAlert.error("404: Page not found.");
                            }
                        },
                        error: function () {
                            appLoader.hide();
                            appAlert.error(AppLanugage.somethingWentWrong);
                        }
                    };

                    return $.ajax(ajaxOptions);

                });
            }


            function init(options) {
                if (!options) options = {};

                var _settings = $.extend({}, compactView.options, options);
                this._settings = _settings;

                var pageId = _settings.pageId;

                $(pageId).append('<div id="' + _settings.compactDetailsPageIdName + '" class="w-100"></div>');
                compactView._initListClickEvent();

                if (!_settings.compactViewId) return false;
                window.compact_view_id = _settings.compactViewId;

                compactView.setActiveRow();

                if (isMobile()) window.location.href = _settings.dataSourceUrl; //redirect to detils page in mobile

                //Re-structure the page
                $("." + _settings.pageWrapperClassName).removeClass(_settings.pageWrapperClassName);

                $('body').addClass('compact-view-active');

                $(pageId + ' div:first').addClass('mobile-mirror');
                $(pageId + ' div:first').addClass('compact-view-left-panel');
                $(pageId).wrapInner('<div class="d-flex"></div>');
                $(pageId).find('table.xs-hide-dtr-control').addClass('hide-dtr-control');
                if (_settings.backButtonUrl) {
                    $(pageId).find("ul.nav-tabs").prepend('<a class="back-btn dark" href="' + _settings.backButtonUrl + '"><i data-feather="arrow-left" class="icon-16"></i> ' + _settings.backButtonText + '</a>');
                }
                $(pageId).find("ul.nav-tabs").append('<div id="mobile-function-button" class="more-options-btn"></div>');

                //Convert buttons to dropdown
                $(".title-button-group").removeClass("skip-dropdown-migration");
                convertTabButtonsToDropdownOnMobileView(".title-button-group", true);

                //init content refresher
                appContentBuilder.init(_settings.dataSourceUrl, {
                    data: _settings.appContentBuilderData,
                    reloadHooks: _settings.appContentBuilderReloadHooks,
                    reload: function (bind, response) {
                        bind("#" + _settings.compactDetailsPageIdName, response.content);
                    }
                }).reload();

                return compactView;
            }

            function setActiveRow() {
                var _settings = this._settings;
                $(_settings.pageId).find('[data-action=load_compact_view][data-compact_view_id=' + window.compact_view_id + ']').closest("tr").addClass("active");
            }

            return compactView;

        })();
    });
}(function (d, f) {
    window['appCompactView'] = f(window['jQuery']);
}));

(function (define) {
    define(['jquery'], function ($) {
        return (function () {

            var appContentBuilder = {
                id: null,
                ajaxConfig: {},
                reloadHooks: [],
                reloadCallback: null,
                init: initBuilder,
                attachHooks: attachReloadHooks,
                reload: reloadContent
            };

            return appContentBuilder;

            function initBuilder(url, options) {
                this.ajaxConfig.url = url;
                this.ajaxConfig.data = options.data || {};  // POST data
                this.reloadHooks = options.reloadHooks || [];  // Hooks to trigger reload
                this.reloadCallback = options.reload || null;  // Optional callback for reload
                this.id = options.id || getRandomAlphabet(5);
                this.attachHooks();  // Attach hooks to trigger reload
                return this;
            }

            function attachReloadHooks() {
                var self = this;

                if (!window.LinkHooks) {
                    window.LinkHooks = {};
                }

                $.each(self.reloadHooks, function (index, hook) {
                    if (hook.type === "app_form" && hook.id) {
                        registerAppFormHook(hook.id, function () {
                            self.reload();
                        }, "appContentBuilder", self.id);

                    } else if (hook.type === "ajax_request" && hook.group) {
                        registerAjaxRequestHook(hook.group, function () {
                            self.reload();
                        }, "appContentBuilder", self.id);
                    } else if (hook.type === "app_modifier" && hook.group) {
                        registerAppModifierHook(hook.group, function () {
                            self.reload();
                        }, "appContentBuilder", self.id);
                    } else if (hook.type === "app_table_row_update" && hook.tableId) {
                        registerAppTableRowUpdateHook(hook.tableId, function () {
                            self.reload();
                        }, "appContentBuilder", self.id);
                    } else if (hook.type === "app_table_row_delete" && hook.tableId) {
                        registerAppTableRowDeleteHook(hook.tableId, function () {
                            self.reload();
                        }, "appContentBuilder", self.id);
                    }
                    //add other hooks here.

                });
            }

            function reloadContent() {
                var self = this;
                appAjaxRequest({
                    url: self.ajaxConfig.url,
                    method: 'POST',
                    data: self.ajaxConfig.data,
                    dataType: 'json',
                    success: function (result) {
                        if (typeof self.reloadCallback === 'function') {
                            self.reloadCallback(bindContent, result);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error reloading appContentBuilder content:', error);
                    }
                });
            }

            function bindContent(selector, content) {
                $(selector).html(content);
            }

        })();
    });
}(function (d, f) {
    window['appContentBuilder'] = f(window['jQuery']);
}));


/*prepare html form data for suitable ajax submit*/
function encodeAjaxPostData(html) {

    html = replaceAll("background-image", "00bg-img00", html);
    html = replaceAll("&quot;", "00quotation00", html);
    html = replaceAll("=", "00~00", html);
    html = replaceAll("&", "00^00", html);

    return html;
}

//replace all occurrences of a string
function replaceAll(find, replace, str) {
    return str.replace(new RegExp(find, 'g'), replace);
}

(function (define) {
    define(['jquery'], function ($) {
        return (function () {

            var appContentModal = {
                init: init,
                destroy: destroy,
                _settings: {},
                options: {
                    url: "",
                    css: "",
                    sidebar: false,
                    modalId: getRandomAlphabet(5)
                }
            };

            return appContentModal;

            function escKeyEvent(e) {
                if (e.keyCode === 27) {
                    destroy(e.data.settings);
                }
            }

            function init(options) {
                this._settings = _prepear_settings(this, options);
                destroy(this._settings);
                _load_template(this._settings);
            }

            function destroy(settings) {
                var newId = getRandomAlphabet(5);
                $("#" + settings.modalId).attr("id", newId).fadeOut(200); //update the id. Otherwise next modal will not work due to same id. 
                if ($("#" + newId).length) {
                    setTimeout(function () {
                        $("#" + newId).remove();
                    }, 200);
                }

                $(document).unbind("keyup", escKeyEvent);
                if (typeof appModalXhr !== 'undefined') {
                    appModalXhr.abort();
                }
                $("body").removeClass("app-modal-open");
            }

            function _prepear_settings(it, options) {
                if (!options)
                    options = {};

                return $.extend({}, it.options, options);
            }

            function _load_modal_content(settings, content) {
                var modalId = settings.modalId;
                var sourceData = settings.sourceElement.data();
                var windowHeight = $(window).height(),
                    windowWidth = $(window).width();

                if (content) {
                    var $content = $(content);

                    $("#" + modalId + " .app-modal-content-area").html($content.find(".app-modal-content").html());
                    var sidebarContent = $content.find(".app-modal-sidebar").html();
                    $("#" + modalId + " .app-modal-sidebar-area").html(sidebarContent);
                    $content.remove();
                } else if (sourceData.content_url) {
                    var contentHtml = '';
                    if (sourceData.type === "image") {
                        contentHtml = "<img id='img_" + modalId + "' src='" + sourceData.content_url + "'>";
                    } else if (sourceData.type === "iframe") {
                        contentHtml = "<div style='background:#fff;'><iframe id='iframe-file-viewer' src='" + sourceData.content_url + "' style='width:" + windowWidth + "px; margin:0; border:0; height:" + windowHeight + "px; '><div>";
                    } else if (sourceData.type === "txt") {
                        if (windowWidth > 1300) {
                            windowWidth = windowWidth - 200;
                        }
                        contentHtml = "<div style='background:#fff;'><iframe id='iframe-file-viewer' src='" + sourceData.content_url + "' style='width:" + windowWidth + "px; margin:0; border:0; height:" + windowHeight + "px; '><div>";
                    } else if (sourceData.type === "audio") {
                        contentHtml = "<audio src='" + sourceData.content_url + "' controls='' class='audio'></audio>";
                    } else if (sourceData.type === "not_viewable") {
                        contentHtml = "<div class='card'><div class='card-body'><h5 class='card-title'>" + sourceData.description + "</h5><p class='card-text'>" + sourceData.filename + "</p></div></div>";
                    }

                    $("#" + modalId + " .app-modal-content-area").html(contentHtml);

                }


                $("#" + modalId).removeClass("loading");

                if (sidebarContent) {
                    $("#" + modalId).addClass("has-sidebar");

                    setTimeout(function () {
                        if ($("#" + modalId + " #file-preview-comment-container").height() > ($(window).height() - 300)) {
                            initScrollbar("#" + modalId + " #file-preview-comment-container", {
                                setHeight: $(window).height() - 300
                            });
                        }
                    });

                } else {
                    $("#" + modalId).removeClass("has-sidebar");
                }

                appLoader.hide();

                var $viewableElements = $("[data-toggle='app-modal']");
                var preview_function = null;


                if (sourceData.target_group) {
                    $viewableElements = $("[data-group='" + sourceData.target_group + "']");
                } else if (sourceData.group) {
                    $viewableElements = $("[data-group='" + sourceData.group + "']");
                }

                if (sourceData.preview_function) {
                    preview_function = sourceData.preview_function;
                }

                var previousIndex = 0,
                    nextIndex = 0,
                    currentIndex = 0;

                if ($viewableElements.length > 1) {
                    $viewableElements.each(function (index) {
                        var data = $(this).data();
                        if (data && data.url && data.url === settings.url) {
                            currentIndex = index;
                        } else if (data && data.content_url && data.content_url === settings.content_url) {
                            currentIndex = index;
                        }
                    });

                    if ($viewableElements[currentIndex - 1]) {
                        previousIndex = currentIndex - 1;
                    } else {
                        previousIndex = $viewableElements.length - 1; //previous element doesn't exists. show last element (circular)
                    }


                    if ($viewableElements[currentIndex + 1]) {
                        nextIndex = currentIndex + 1;
                    } else {
                        nextIndex = 0; //next element doesn't exists. show first element (circular)
                    }


                    var _trigger_preview = function ($element, preview_function) {
                        if (preview_function) {

                            if (typeof window[preview_function] === "function") {
                                window[preview_function]($element);
                            } else {
                                console.log("Undefined preview_function", preview_function);
                            }
                        } else {
                            $element.trigger("click");
                        }
                    };

                    $("#" + modalId + " .app-modal-previous-button").click(function () {
                        $viewableElements.each(function (index) {
                            if (index === previousIndex) {
                                _trigger_preview($(this), preview_function);
                            }
                        });

                    });

                    $("#" + modalId + " .app-modal-next-button").click(function () {
                        $viewableElements.each(function (index) {
                            if (index === nextIndex) {
                                _trigger_preview($(this), preview_function);
                            }
                        });

                    });

                    feather.replace();
                    $("#" + modalId + " .app-modal-previous-button").removeClass("hide");
                    $("#" + modalId + " .app-modal-next-button").removeClass("hide");

                }

                $("#" + modalId + " .app-modal-zoom-in-button").click(function () {

                    if ($(this).hasClass("disabled")) {
                        return false;
                    }

                    $("#" + modalId + " .app-modal-content-area").addClass("scrollable");
                    $("#" + modalId + " .app-modal-content").removeClass("fit-window-height");

                    $(this).addClass("disabled");
                    $("#" + modalId + " .app-modal-zoom-out-button").removeClass("disabled");

                });

                $("#" + modalId + " .app-modal-zoom-out-button").click(function () {
                    if ($(this).hasClass("disabled")) {
                        return false;
                    }

                    $("#" + modalId + " .app-modal-content-area").removeClass("scrollable");
                    $("#" + modalId + " .app-modal-content").addClass("fit-window-height");

                    $(this).addClass("disabled");
                    $("#" + modalId + " .app-modal-zoom-in-button").removeClass("disabled");

                });


                var img = document.getElementById('img_' + modalId);
                if (img) {
                    img.addEventListener('load', function () {
                        var windowHeight = $(window).height();
                        var $img = $("#" + modalId + " .app-modal-content-area").find("img");
                        if ($img && $img[0] && $img[0].naturalHeight > windowHeight) {
                            $("#" + modalId + " .app-modal-zoom-in-button").removeClass("hide");
                            $("#" + modalId + " .app-modal-zoom-out-button").removeClass("hide").addClass("disabled");
                        }

                        if ($.fn.mCustomScrollbar) {
                            $("#" + modalId + " .app-moadl-sidebar-scrollbar").mCustomScrollbar({ setHeight: windowHeight, theme: "minimal-dark", autoExpandScrollbar: true });
                        }
                    });
                }

                setTimeout(function () {

                    var windowHeight = $(window).height();
                    var $img = $("#" + modalId + " .app-modal-content-area").find("img");
                    if ($img && $img[0] && $img[0].naturalHeight > windowHeight) {
                        $("#" + modalId + " .app-modal-zoom-in-button").removeClass("hide");
                        $("#" + modalId + " .app-modal-zoom-out-button").removeClass("hide").addClass("disabled");
                    }

                    if ($.fn.mCustomScrollbar) {
                        $("#" + modalId + " .app-moadl-sidebar-scrollbar").mCustomScrollbar({ setHeight: windowHeight, theme: "minimal-dark", autoExpandScrollbar: true });
                    }

                }, 300);


            }


            function _load_template(settings) {
                var modalId = settings.modalId;
                var sidebar = "<div class='app-modal-sidebar hidden-xs'>\
                                        <div class='app-modal-close'><span>&times;</span></div>\
                                        <div class='app-moadl-sidebar-scrollbar'>\
                                            <div class='app-modal-sidebar-area'>\
                                            </div>\
                                        </div>\
                                    </div>";
                var controlIcon = "<span class='expand round-button hidden-xs'><i data-feather='maximize-2' class='icon-16'></i></span>";

                if (settings.sidebar === false || isMobile()) {
                    sidebar = "";
                    controlIcon = "<div class='app-modal-close app-modal-fixed-close-button'><span>&times;</span></div>";
                }

                controlIcon += "<span class='app-modal-zoom-in-button hide round-button clickable'><i data-feather='zoom-in' class='icon-18'></i></span>";
                controlIcon += "<span class='app-modal-zoom-out-button hide round-button clickable'><i data-feather='zoom-out' class='icon-18'></i></span>";

                controlIcon += "<span class='app-modal-previous-button hide round-button clickable'><i data-feather='chevron-left' class='icon-18'></i></span>";
                controlIcon += "<span class='app-modal-next-button hide round-button clickable'><i data-feather='chevron-right' class='icon-18'></i></span>";


                var template = "<div class='app-modal loading' id='" + modalId + "'>\
                                <span class='compress round-button'><i data-feather='minimize-2' class='icon-16'></i></span>\
                                <div class='app-modal-body'>\
                                    <div class='app-modal-content fit-window-height'>" + controlIcon +
                    "<div class='hide app-modal-close'><span>&times;</span></div>\
                                        <div class='app-modal-content-area d-inline-block'>\
                                        </div>\
                        </div>" + sidebar + "</div>\
                            </div>";

                $("body").addClass("app-modal-open").prepend(template);

                $("#" + modalId + " .expand").click(function () {
                    $(".app-modal").addClass("full-content");
                });

                $("#" + modalId + " .compress").click(function () {
                    $(".app-modal").removeClass("full-content");
                });
                $("#" + modalId + " .app-modal-close").click(function () {
                    destroy(settings);
                });

                $(document).bind("keyup", { settings: settings }, escKeyEvent);

                if ($("#" + modalId + " .app-modal-sidebar").is(":visible")) {
                    appLoader.show({ container: '#' + modalId, css: "top:35%; right:55%;" });
                } else {
                    appLoader.show({ container: '#' + modalId, css: "top:35%; right:48%;" });
                }

                if (settings.url) {
                    appModalXhr = $.ajax({
                        url: settings.url || "",
                        data: {},
                        cache: false,
                        type: 'POST',
                        success: function (response) {
                            _load_modal_content(settings, response);
                        },
                        statusCode: {
                            404: function () {
                                appContentModal.destroy(settings);
                                appAlert.error("404: Page not found.");
                            }
                        },
                        error: function () {
                            appContentModal.destroy(settings);
                            appAlert.error(AppLanugage.somethingWentWrong);
                        }
                    });

                } else {
                    _load_modal_content(settings);
                }

            }
        })();
    });
}(function (d, f) {
    window['appContentModal'] = f(window['jQuery']);
}));

//custom daterange controller
(function ($) {
    $.fn.appDateRange = function (options) {
        var defaults = {
            dateRangeType: "yearly",
            filterParams: {},
            onChange: function (dateRange) {
            },
            onInit: function (dateRange) {
            }
        };
        var settings = $.extend({}, defaults, options);
        settings._inputDateFormat = "YYYY-MM-DD";

        this.each(function () {

            var $instance = $(this);
            var dom = '<div class="ml15 btn-group">'
                + '<button data-act="prev" class="btn btn-default date-range-selector"><i data-feather="chevron-left" class="icon-16"></i></button>'
                + '<button data-act="datepicker" class="btn btn-default"></button>'
                + '<button data-act="next"  class="btn btn-default date-range-selector"><i data-feather="chevron-right" class="icon-16"></i></button>'
                + '</div>';
            $instance.append(dom);

            var $datepicker = $instance.find("[data-act='datepicker']"),
                $dateRangeSelector = $instance.find(".date-range-selector");

            if (settings.dateRangeType === "yearly") {
                var inityearSelectorText = function ($elector) {
                    $elector.html(moment(settings.filterParams.start_date).customFormat("YYYY"));
                };

                inityearSelectorText($datepicker);

                //bind the click events
                $datepicker.datepicker({
                    format: "YYYY-MM",
                    viewMode: "years",
                    minViewMode: "years",
                    autoclose: true,
                    language: "custom",
                    orientation: "bottom"
                }).on('changeDate', function (e) {
                    var date = moment(e.date).customFormat(settings._inputDateFormat),
                        year = moment(date).customFormat("YYYY");
                    settings.filterParams.start_date = year + "-01-01";
                    settings.filterParams.end_date = year + "-12-31";
                    settings.filterParams.year = year;
                    inityearSelectorText($datepicker);
                    settings.onChange(settings.filterParams);
                });

                //init default date
                var year = moment().customFormat("YYYY");
                settings.filterParams.start_date = year + "-01-01";
                settings.filterParams.end_date = year + "-12-31";
                settings.filterParams.year = year;
                settings.onInit(settings.filterParams);


                $dateRangeSelector.click(function () {
                    var type = $(this).attr("data-act"),
                        startDate = moment(settings.filterParams.start_date),
                        endDate = moment(settings.filterParams.end_date);
                    if (type === "next") {
                        startDate = startDate.add(1, 'years').customFormat(settings._inputDateFormat);
                        endDate = endDate.add(1, 'years').customFormat(settings._inputDateFormat);
                    } else if (type === "prev") {
                        startDate = startDate.subtract(1, 'years').customFormat(settings._inputDateFormat);
                        endDate = endDate.subtract(1, 'years').customFormat(settings._inputDateFormat);
                    }

                    settings.filterParams.start_date = startDate;
                    settings.filterParams.end_date = endDate;
                    settings.filterParams.year = moment(startDate).customFormat("YYYY");

                    inityearSelectorText($datepicker);
                    settings.onChange(settings.filterParams);
                });


            } else if (settings.dateRangeType === "monthly") {

                var initMonthSelectorText = function ($elector) {
                    $elector.html(moment(settings.filterParams.start_date).format("MMMM YYYY"));
                };

                //prepareDefaultDateRangeFilterParams();
                initMonthSelectorText($datepicker);

                //bind the click events
                $datepicker.datepicker({
                    format: "YYYY-MM",
                    viewMode: "months",
                    minViewMode: "months",
                    autoclose: true,
                    language: "custom",
                }).on('changeDate', function (e) {
                    var date = moment(e.date).customFormat(settings._inputDateFormat);
                    var daysInMonth = moment(date).daysInMonth(),
                        yearMonth = moment(date).customFormat("YYYY-MM");
                    settings.filterParams.start_date = yearMonth + "-01";
                    settings.filterParams.end_date = yearMonth + "-" + daysInMonth;
                    initMonthSelectorText($datepicker);
                    settings.onChange(settings.filterParams);
                });

                //init default date
                var year = moment().customFormat("YYYY");
                var yearMonth = moment().customFormat("YYYY-MM");
                var daysInMonth = moment().daysInMonth();

                settings.filterParams.start_date = yearMonth + "-01";
                settings.filterParams.end_date = yearMonth + "-" + daysInMonth;
                settings.filterParams.year = year;
                settings.onInit(settings.filterParams);

                $dateRangeSelector.click(function () {
                    var type = $(this).attr("data-act"),
                        startDate = moment(settings.filterParams.start_date),
                        endDate = moment(settings.filterParams.end_date);
                    if (type === "next") {
                        var nextMonth = startDate.add(1, 'months'),
                            daysInMonth = nextMonth.daysInMonth(),
                            yearMonth = nextMonth.customFormat("YYYY-MM");

                        startDate = yearMonth + "-01";
                        endDate = yearMonth + "-" + daysInMonth;

                    } else if (type === "prev") {
                        var lastMonth = startDate.subtract(1, 'months'),
                            daysInMonth = lastMonth.daysInMonth(),
                            yearMonth = lastMonth.customFormat("YYYY-MM");

                        startDate = yearMonth + "-01";
                        endDate = yearMonth + "-" + daysInMonth;
                    }

                    settings.filterParams.start_date = startDate;
                    settings.filterParams.end_date = endDate;
                    settings.filterParams.year = moment(startDate).customFormat("YYYY-MM");

                    initMonthSelectorText($datepicker);
                    settings.onChange(settings.filterParams);
                });
            }


        });
    };
})(jQuery);


var loadFilterView = function (settings) {
    if (settings.source && settings.targetSelector) {
        appAjaxRequest({
            url: settings.source,
            data: settings.filterParams,
            cache: false,
            type: 'POST',
            success: function (response) {
                $(settings.targetSelector).html(response);
                appLoader.hide();
            },
            statusCode: {
                404: function () {
                    appLoader.hide();
                    appAlert.error("404: Page not found.", { container: '.modal-body', animate: false });
                }
            },
            error: function () {
                appLoader.hide();
                appAlert.error(AppLanugage.somethingWentWrong, { container: '.modal-body', animate: false });
            }
        });
    }
};

//custom filters controller
(function ($) {

    $.fn.appFilters = function (options) {
        appLoader.show();

        var defaults = {
            source: "", //data url
            targetSelector: "",
            reloadSelector: "",
            dateRangeType: "", // type: daily, weekly, monthly, yearly. output params: start_date and end_date
            checkBoxes: [], // [{text: "Caption", name: "status", value: "in_progress", isChecked: true}] 
            multiSelect: [], // [{text: "Caption", name: "status", options:[{text: "Caption", value: "in_progress", isChecked: true}]}] 
            radioButtons: [], // [{text: "Caption", name: "status", value: "in_progress", isChecked: true}] 
            filterDropdown: [], // [{id: 10, text:'Caption', isSelected:true}] 
            singleDatepicker: [], // [{name: '', value:'', options:[]}] 
            rangeDatepicker: [], // [{startDate:{name:"", value:""},endDate:{name:"", value:""}}] 
            stateSave: true,
            ignoreSavedFilter: false, //sometimes, need to click on widget link to show specific filter. Enable for that. 
            isMobile: window.matchMedia("(max-width: 800px)").matches,
            filterParams: { customFilter: true }, //will post this vales on source url
            search: { show: false },
            customLanguage: {
                searchPlaceholder: AppLanugage.search,
                today: AppLanugage.today,
                yesterday: AppLanugage.yesterday,
                tomorrow: AppLanugage.tomorrow
            },
            beforeRelaodCallback: function () { },
            afterRelaodCallback: function () { },
            onInitComplete: function () { }
        };

        var $instance = $(this),
            $instanceWrapper = $instance; //$instanceWrapper is same as instance in this case

        var settings = $.extend({}, defaults, options);

        if (settings.reload) {
            var instance = $(this);
            var instanceSettings = window.InstanceCollection[instance.attr("id")];


            if (instance.data("beforeRelaodCallback")) {
                instance.data("beforeRelaodCallback")(instance, instanceSettings.filterParams);
            }


            loadFilterView(instanceSettings);

            if (instance.data("afterRelaodCallback")) {
                instance.data("afterRelaodCallback")(instance, instanceSettings.filterParams);
            }


            return false;
        } else {

            var filterForm = "";
            if (settings.smartFilterIdentity) {
                filterForm = "<div class='filter-form'></div>";
            }

            $instanceWrapper.append("<div class='filter-section-container'>\n\
                    <div class='filter-section-flex-row'>\n\
                            <div class='filter-section-left'></div><div class='filter-section-right'></div>\n\
                    </div>" + filterForm + "</div>");
        }

        settings._firstDayOfWeek = AppHelper.settings.firstDayOfWeek || 0;
        settings._inputDateFormat = "YYYY-MM-DD";


        settings = prepareDefaultFilters(settings);

        buildFilterDom(settings, $instanceWrapper, $instance);
        window.InstanceCollection = window.InstanceCollection || {};
        window.InstanceCollection[$instance.attr("id")] = settings;
        if (settings.onInitComplete) {
            settings.onInitComplete($instance, settings.filterParams);
        }

        loadFilterView(settings);


        //bind calbacks
        $instance.data("beforeRelaodCallback", settings.beforeRelaodCallback);
        $instance.data("afterRelaodCallback", settings.afterRelaodCallback);

    };
})(jQuery);



//find and replace all search string
replaceAllString = function (string, find, replaceWith) {
    return string.split(find).join(replaceWith);
};

//convert a number to curency format
toCurrency = function (number, currencySymbol) {

    if (AppHelper.settings.noOfDecimals == "0") {
        number = Math.round(parseFloat(number)) + ".00"; //round it and the add static 2 decimals
    } else {
        number = parseFloat(number).toFixed(2);
    }

    if (!currencySymbol) {
        currencySymbol = AppHelper.settings.currencySymbol;
    }
    var result = number.replace(/(\d)(?=(\d{3})+\.)/g, "$1,");

    //remove (,) if thousand separator is (space)
    if (AppHelper.settings.thousandSeparator === " ") {
        result = result.replace(',', ' ');
    }
    if (AppHelper.settings.decimalSeparator === ",") {
        result = replaceAllString(result, ".", "_");
        result = replaceAllString(result, ",", ".");
        result = replaceAllString(result, "_", ",");
    }
    if (currencySymbol === "none") {
        currencySymbol = "";
    }
    if (AppHelper.settings.noOfDecimals == "0") {
        result = result.slice(0, -3); //remove decimals
    }

    if (AppHelper.settings.currencyPosition === "right") {
        return result + "" + currencySymbol;
    } else {
        if (result.indexOf("-") == "0") {
            result = result.replace('-', '');
            return "-" + currencySymbol + result;
        } else {
            return currencySymbol + "" + result;
        }
    }
};


calculateDatatableTotal = function (instance, columnNumber, valueModifier, currentPage) {
    var api = instance.api(),
        columnOption = {};
    if (currentPage) {
        columnOption = { page: 'current' };
    }

    return api.column(columnNumber, columnOption).data()
        .reduce(function (previousValue, currentValue, test, test2) {
            if (valueModifier) {
                return previousValue + valueModifier(currentValue);
            } else {
                return previousValue + currentValue;
            }
        }, 0);
};

// rmove the formatting to get integer data
unformatCurrency = function (currency, conversionRate) {
    currency = currency.toString();
    var mainAmount = currency, decimalSeparatorUnformatted = false;

    if (currency) {
        currency = currency.replace(/[^0-9.,-]/g, '');

        if (conversionRate) {
            //prepare converted amount
            var currencySymbol = mainAmount.replace(currency, '');
            if (conversionRate[currencySymbol]) {
                //conversion rate exists for this currency
                currency = unformatDecimalSeparator(currency);
                currency = ((1 / conversionRate[currencySymbol]) * 1) * currency;
                currency = currency.toString();
                decimalSeparatorUnformatted = true;
            }
        }

        if (currency.indexOf(".") == 0 || currency.indexOf(",") == 0) {
            currency = currency.slice(1);
        }

        if (!decimalSeparatorUnformatted) {
            currency = unformatDecimalSeparator(currency);
        }

        currency = currency * 1;
    }
    if (currency) {
        return currency;
    }
    return 0;
};

unformatDecimalSeparator = function (currency) {
    if (AppHelper.settings.decimalSeparator === ",") {
        currency = replaceAllString(currency, ".", "");
        currency = replaceAllString(currency, ",", ".");
    } else {
        currency = replaceAllString(currency, ",", "");
    }
    return currency;
};

// convert seconds to hours:minutes:seconds format
secondsToTimeFormat = function (sec) {
    var sec_num = parseInt(sec, 10),
        hours = Math.floor(sec_num / 3600),
        minutes = Math.floor((sec_num - (hours * 3600)) / 60),
        seconds = sec_num - (hours * 3600) - (minutes * 60);
    if (hours < 10) {
        hours = "0" + hours;
    }
    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    if (seconds < 10) {
        seconds = "0" + seconds;
    }
    var time = hours + ':' + minutes + ':' + seconds;
    return time;
};

//clear datatable state
clearAppTableState = function (tableInstance) {
    if (tableInstance) {
        setTimeout(function () {
            tableInstance.api().state.clear();
        }, 200);
    }
};

//show/hide datatable column
showHideAppTableColumn = function (tableInstance, columnIndex, visible) {
    tableInstance.fnSetColumnVis(columnIndex, !!visible);
};

//appMention using at.js
(function ($) {

    $.fn.appMention = function (options) {
        var defaults = {
            at: "@",
            dataType: "json",
            source: "",
            data: {}
        };

        var settings = $.extend({}, defaults, options);

        var selector = this;

        appAjaxRequest({
            url: settings.source,
            data: settings.data,
            dataType: settings.dataType,
            method: "POST",
            success: function (result) {
                if (result.success) {
                    $(selector).atwho({
                        at: settings.at,
                        data: result.data,
                        insertTpl: '${content}'
                    });
                }
            }
        });

    };
})(jQuery);

//custom multi-select controller
(function ($) {
    $.fn.appMultiSelect = function (options) {
        var defaults = {
            text: "",
            options: [],
            onChange: function (values) {
            },
            onInit: function (values) {
            }
        };
        var settings = $.extend({}, defaults, options);

        this.each(function () {

            var $instance = $(this);
            var multiSelect = "", values = [];

            $.each(settings.options, function (index, listOption) {
                var active = "";

                if (listOption.isChecked) {
                    active = " active";
                    values.push(listOption.id);
                }
                //<li class=" list-group-item clickable toggle-table-column" data-column="1">ID</li>
                multiSelect += '<li class="list-group-item clickable ' + active + '" data-name="' + settings.name + '" data-value="' + listOption.id + '">';
                multiSelect += listOption.text;
                multiSelect += '</li>';
            });

            multiSelect = "<div class='dropdown-menu'><ul class='list-group' data-act='multiselect'>" + multiSelect + "</ul></div>";

            var dom = '<div class="">'
                + '<span class="dropdown inline-block filter-multi-select">'
                + '<button class="btn btn-default dropdown-toggle caret " type="button" data-bs-toggle="dropdown" aria-expanded="true">' + settings.text + ' </button>'
                + multiSelect + '</span>'
                + '</div>';

            $instance.append(dom);
            settings.onInit(values);

            var $multiselect = $instance.find("[data-name='" + settings.name + "']");
            $multiselect.click(function () {
                var $selector = $(this);
                $selector.toggleClass("active");
                setTimeout(function () {
                    var values = [];
                    $selector.parent().find("li").each(function () {
                        if ($(this).hasClass("active")) {
                            values.push($(this).attr("data-value"));
                        }
                    });
                    settings.onChange(values);
                });
                return false;
            });
        });
    };
})(jQuery);

//instant popover modifier
(function ($) {
    $.fn.appModifier = function (options) {
        var defaults = {
            actionUrl: "", //the url where the response will go after modification
            value: "", //existing value
            actionType: "dropdown", //action type
            showButtons: false, //show submit/cancel button
            datepicker: {}, //options for datepicker
            select2Option: {}, //options for select2
            timepickerOptions: {}, //options for timepicker
            dataType: 'json',
            postData: {},
            className: "",
            placeholder: "",
            ruleRequired: false,
            msgRequired: "",
            dropdownData: {},
            onSuccess: function () {
            }
        };

        var $instance = $(this);
        var instanceData = $instance.data() ? $instance.data() : {};

        var settings = $.extend({}, defaults, instanceData, options);

        if (settings.actionType == "select2") {  //backward compatibility
            settings.actionType = "dropdown";
        }

        if (settings.actionType === "dropdown" && settings.field && settings.dropdownData[settings.field]) {
            settings.select2Option.data = settings.dropdownData[settings.field];
        }

        if ($instance.attr('data-value')) {
            settings.value = $instance.attr('data-value');
        }


        //prepare select2 tags
        if (settings.canCreateTags == "1") {
            settings.showButtons = true;
            settings.select2Option.tags = settings.select2Option.data ? settings.select2Option.data : [];
            delete settings.select2Option.data;
        }

        if (settings.multipleTags == "1") {
            settings.showButtons = true;
            settings.select2Option.multiple = true;
        }



        //create popover content dom
        var tempId = getRandomAlphabet(5);

        //prepare submit or close buttons
        var buttonDom = "";
        if (settings.showButtons) {
            buttonDom = "<div class='custom-popover-button-area mt10 clearfix row'>\n\
                            <div id='custom-popover-submit-btn-" + tempId + "' class='col-md-6 pr5'><button class='btn btn-primary btn-sm w100p'><i data-feather='check' class='icon-16'></i></button></div>\n\
                            <div class='col-md-6 pl5 custom-popover-close-btn'><button class='btn btn-default btn-sm w100p'><i data-feather='x' class='icon-16'></i></button></div>\n\
                        </div>";
        }

        var required = "";
        if (settings.ruleRequired) {
            required = " data-rule-required=1 ";

            if (settings.msgRequired) {
                required += ' data-msg-required= "' + settings.msgRequired + '" ';
            }
        }


        //prepare container dom
        var containerDom = "";
        if (settings.actionType === "dropdown") {
            containerDom = '<input id="' + tempId + '" value="' + settings.value + '" placeholder="' + settings.placeholder + '" type="text" class="form-control popover-tempId ' + settings.className + '" ' + required + '/> ' + buttonDom;
        } else if (settings.actionType === "date") {
            var dateFormat = getJsDateFormat();
            var dateArray = settings.value.split("-"),
                year = dateArray[0],
                month = dateArray[1],
                day = dateArray[2];
            var dateValue = dateFormat.replace("yyyy", year).replace("mm", month).replace("dd", day);

            containerDom = '<div style="height: 240px;" id="' + tempId + '"  data-date="' + dateValue + '" data-date-format="' + dateFormat + '" class="popover-tempId" ' + required + '></div>'; //set height first for right popover position
        } else if (settings.actionType === "time") {
            containerDom = '<input class="form-control" type="text" id="' + tempId + '"  value="' + settings.value + '" /><div id="popover-timepicker-container-' + tempId + '" ' + required + '></div>' + buttonDom;
        } else if (settings.actionType === "text") {
            containerDom = '<input class="form-control" type="text" id="' + tempId + '"  value="' + settings.value + '" ' + required + ' /><div class="js-help-message"></div> ' + buttonDom;
        }



        //show popover
        var offset = $instance.offset();
        var top = offset.top;
        var leftOffset = offset.left;
        var topOffset = top + $instance.outerHeight() + 10; //10 for arrow

        //create popover dom
        var popoverDom = "<div class='app-popover' style='max-width: 350px; top: " + topOffset + "px; left: " + leftOffset + "px'>\n\
                                <span class='app-popover-arrow' ></span>\n\
                                <div class='app-popover-body'>\n\
                                    <div class='loader-container inline-loader hide'></div>\n\
                                    " + containerDom + " \n\
                                </div>\n\
                            </div>";

        $(".app-popover").remove();
        $("body").append(popoverDom);
        feather.replace();

        //apply select2/datepicker on popover content
        var $inputField = $("#" + tempId);
        var $timepickerContainer = $("#popover-timepicker-container-" + tempId);
        if (settings.actionType === "dropdown") {

            if (settings.showButtons) {
                //submit with buttons
                $("#" + tempId).select2(settings.select2Option);
            } else {
                if (settings.select2Option.data.length > 0 && settings.select2Option.data.length <= 7) {


                    window["initAjaxAction_" + tempId] = function (value, changedText) {
                        initAjaxAction($instance, value, settings, changedText);
                        if ($("#" + tempId).next()) {
                            $("#" + tempId).next().addClass("hide");
                        }
                    }
                    var dropdownList = "";
                    settings.select2Option.data.forEach(item => {
                        var activeClass = '';
                        if (settings.value == item.id) {
                            activeClass = 'active';
                        }
                        dropdownList += `<a href="#" class="dropdown-item list-group-item ${activeClass}" onclick='initAjaxAction_${tempId}("${item.id}", "${item.text}")'>${item.text}</a>`;
                    });
                    $("#" + tempId).closest(".app-popover-body").addClass("pl0 pr0").append("<div>" + dropdownList + "</div>");
                    $("#" + tempId).addClass("hide");
                } else {
                    $("#" + tempId).select2(settings.select2Option).change(function (action) {
                        initAjaxAction($instance, $(this).val(), settings, action["added"]["text"]);
                    });
                }


            }
        } else if (settings.actionType === "date") {
            settings.datepicker.onChangeDate = function (response) {
                initAjaxAction($instance, response, settings);
            };

            setDatePicker("#" + tempId, settings.datepicker);
        } else if (settings.actionType === "time") {

            var appendWidgetTo = "#popover-timepicker-container-" + tempId;
            var showMeridian = AppHelper.settings.timeFormat == "24_hours" ? false : true;

            var timepickerSettings = $.extend({}, {
                minuteStep: AppHelper.settings.timepickerMinutesInterval,
                defaultTime: "",
                appendWidgetTo: appendWidgetTo,
                showMeridian: showMeridian,
                isInline: true
            }, settings.timepickerOptions);

            $inputField.timepicker(timepickerSettings);

            $inputField.timepicker().on('show.timepicker', function (e) {
                feather.replace();
            });

            setTimeout(function () {
                $inputField.focus();
                setTimeout(function () {
                    $(".bootstrap-timepicker-widget").removeClass("dropdown-menu");
                });
            });
        } else if (settings.actionType === "text") {

            $inputField.on("keydown", function (e) {
                if (e.keyCode === 13) { // Enter
                    e.preventDefault();
                    $("div#custom-popover-submit-btn-" + tempId).trigger("click");
                }
            });

            setTimeout(function () {
                $inputField.focus().select();
            });
        }

        //check if the right side is overflowed
        $("body").find(".app-popover").each(function () {
            //position content
            var right = $(window).width() - ($(this).offset().left + $(this).outerWidth());
            if (right < 0) {
                //overflowed
                $(this).css({ "left": "unset", "right": "10px" });

                //position arrow
                var right = $(window).width() - ($instance.offset().left + (($instance.outerWidth() / 2) * 1));
                $(this).find(".app-popover-arrow").css({ "left": "unset", "right": right });
            }
        });

        //submit button
        $("div#custom-popover-submit-btn-" + tempId).click(function () {
            if (settings.ruleRequired && !$inputField.val()) {
                $inputField.parent().addClass("has-error").find(".js-help-message").html('<span class="help-block" style="">' + settings.msgRequired + '</span>');
                return false;
            }
            if ($inputField.val()) {
                $inputField.parent().removeClass("has-error").find(".js-help-message").html("");
            }

            initAjaxAction($instance, $inputField.val(), settings);
        });

        //close button
        $(".custom-popover-close-btn").click(function () {
            $(".app-popover").remove(); //hide popover
        });

        function initAjaxAction($instance, value, settings, changedText) {
            var popoverContentHeight = $inputField.closest(".app-popover-body").height();
            var popoverContentWidth = $inputField.closest(".app-popover-body").width();
            $inputField.closest(".app-popover-body").find(".loader-container").removeClass("hide").css({ "height": popoverContentHeight, "width": popoverContentWidth });
            $inputField.closest(".app-popover-body").find(".custom-popover-button-area").addClass("hide");
            $inputField.addClass("hide");
            $timepickerContainer.addClass("hide");

            var postData = $.extend({}, settings.postData, { value: value });

            appAjaxRequest({
                url: settings.actionUrl,
                type: 'POST',
                dataType: settings.dataType,
                data: postData,
                success: function (result) {
                    $(".app-popover").remove(); //hide popover
                    setTimeout(function () {
                        $inputField.closest(".app-popover-body").find(".loader-container").addClass("hide");
                        $inputField.closest(".app-popover-body").find(".custom-popover-button-area").removeClass("hide");
                        $inputField.removeClass("hide");
                        $timepickerContainer.removeClass("hide");
                    }, 200);

                    if (result.success) {
                        settings.onSuccess(result, value);

                        //update for dropdown
                        if (result.content) {
                            $instance.html(result.content);
                        } else if (changedText) {
                            $instance.text(changedText);
                        }

                        $instance.attr("data-value", value); //update value for instant future use
                        $(".app-popover").remove();

                        var group = $instance.attr("data-modifier-group");
                        if (group && window.appModifierHooks && window.appModifierHooks[group]) {

                            window.appModifierHooks[group].forEach(function (hook) {
                                if (typeof hook.onSuccess === 'function') {
                                    hook.onSuccess($instance.data(), result);
                                }
                            });
                        }

                    } else {
                        appAlert.error(result.message);
                    }
                }
            });
        }
    };
})(jQuery);

//instant popover modifier
(function ($) {
    $.fn.appConfirmation = function (options) {
        var defaults = {
            title: "",
            btnConfirmLabel: "",
            btnCancelLabel: "",
            onConfirm: function () {
            }
        };

        var settings = $.extend({}, defaults, options);

        var $instance = $(this);
        //show popover
        var offset = $instance.offset();
        var top = offset.top;
        var leftOffset = offset.left;
        var bottomOffset = $(window).height() - (top - 10); //10 for arrow

        //create popover dom
        var popoverDom = "<div class='app-popover' style='bottom: " + bottomOffset + "px; left: " + leftOffset + "px'>\n\
                                <span class='app-popover-arrow bottom-arrow' ></span>\n\
                                <div class='loader-container inline-loader hide'></div>\n\
                                <div class='app-popover-content-container'>\n\
                                    <div class='confirmation-title'>" + settings.title + "</div>\n\
                                    <div class='app-popover-body pt0'>\n\
                                        <div class='custom-popover-button-area mt15 clearfix row'>\n\
                                            <div class='col-md-6 pr5'><button class='btn btn-danger w100p confirmation-confirm-button'><i data-feather='check' class='icon-16'></i> " + settings.btnConfirmLabel + "</button></div>\n\
                                            <div class='col-md-6 pl5'><button class='btn btn-default w100p confirmation-cancel-button'><i data-feather='x' class='icon-16'></i> " + settings.btnCancelLabel + "</button></div>\n\
                                        </div>\n\
                                    </div>\n\
                                </div>\n\
                            </div>";

        $(".app-popover").remove();
        $("body").append(popoverDom);
        feather.replace();

        //submit button
        $(".confirmation-confirm-button").click(function () {
            $(".app-popover").remove(); //hide popover
            settings.onConfirm();
        });

        //close button
        $(".confirmation-cancel-button").click(function () {
            $(".app-popover").remove(); //hide popover
        });
    };
})(jQuery);

// Make any items sortable
// The row should have attributes: data-sort-value, data-id
(function ($) {
    $.fn.appSortable = function (options) {
        var defaults = {
            actionUrl: "", // the url where the response will go after modification
            rowClass: "", // the class of the row to be sorted
            sortDirection: "asc", // the direction of the sort (asc or desc)
        };

        var $instance = $(this);
        var $selector = $instance;
        var settings = $.extend({}, defaults, options);

        // if it's a table then apply the sortable on the tbody
        if ($instance.attr("id").endsWith("table")) {
            var randomId = getRandomAlphabet(5);
            $instance.find("tbody").attr("id", randomId);
            $selector = $("#" + randomId);
        }

        Sortable.create($selector[0], {
            animation: 150,
            handle: '.move-icon',
            chosenClass: "sortable-chosen",
            ghostClass: "sortable-ghost",
            onUpdate: function (e) {
                appLoader.show();

                // get the sort value based on the previous and next item
                if ($instance.attr("id").endsWith("table")) {
                    var $movedItem = $(e.item).find(settings.rowClass);
                    var $prevItem = $movedItem.closest('tr').prev('tr').find(settings.rowClass);
                    var $nextItem = $movedItem.closest('tr').next('tr').find(settings.rowClass);
                } else {
                    var $movedItem = $(e.item);
                    var $prevItem = $movedItem.prev(settings.rowClass);
                    var $nextItem = $movedItem.next(settings.rowClass);
                }

                var prevSortValue = $prevItem.attr('data-sort-value') || null;
                var nextSortValue = $nextItem.attr('data-sort-value') || null;
                prevSortValue = prevSortValue ? prevSortValue * 1 : null;
                nextSortValue = nextSortValue ? nextSortValue * 1 : null;

                let newSort;

                // prepare sort value for the moved item
                if (prevSortValue !== null && nextSortValue !== null) {
                    newSort = (prevSortValue + nextSortValue) / 2; // moved in the middle, calculate the middle value
                } else if (prevSortValue == null) {
                    newSort = ((settings.sortDirection === "asc") ? (nextSortValue - 0.1) : (nextSortValue + 1));
                } else if (nextSortValue == null) {
                    newSort = ((settings.sortDirection === "asc") ? (prevSortValue + 1) : (prevSortValue - 0.1));
                } else {
                    newSort = 1; // default value
                }

                newSort = Math.round(newSort * 100000) / 100000; // use upto 5 decimal places
                $movedItem.attr('data-sort-value', newSort);

                $.ajax({
                    url: settings.actionUrl,
                    type: "POST",
                    data: {
                        id: $movedItem.attr('data-id'),
                        sort: newSort
                    },
                    success: function () {
                        appLoader.hide();
                    }
                });
            }
        });
    };
})(jQuery);