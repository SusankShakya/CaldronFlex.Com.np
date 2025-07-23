setThemeColor();
window.lastAjaxRequestTime = 0;
window.minAjaxRequestInterval = 300;

function appAjaxRequest(options) {
    const now = Date.now();
    const scheduledTime = Math.max(window.lastAjaxRequestTime + window.minAjaxRequestInterval, now);
    const delay = scheduledTime - now;

    window.lastAjaxRequestTime = scheduledTime;

    setTimeout(() => {
        $.ajax(options);
    }, delay);
}

const IDBHelper = (() => {
    const DB_NAME = 'RISE_indexedDB';
    const STORE_NAME = 'rise_store';
    const DB_VERSION = 1;

    function isSupported() {
        return 'indexedDB' in window;
    }

    function getUserId() {
        return (window.AppHelper && AppHelper.userId) ? AppHelper.userId : 0;
    }

    function openDB() {
        return new Promise((resolve, reject) => {
            if (!isSupported()) {
                return reject('IndexedDB not supported');
            }

            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    const store = db.createObjectStore(STORE_NAME, { keyPath: 'id' });
                    store.createIndex('userId', 'userId', { unique: false });
                }
            };

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function setValue(key, value) {
        if (!isSupported()) {
            return new Promise((resolve, reject) => {
                console.log("IndexedDB not supported");
                resolve(false); //don't show error message
            });
        }
        const userId = getUserId();
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);

        const data = {
            id: `user_${userId}_${key}`,
            userId: userId,
            key: key,
            value: value
        };

        return new Promise((resolve, reject) => {
            const req = store.put(data);
            req.onsuccess = () => resolve(true);
            req.onerror = () => reject(req.error);
        });
    }

    async function getValue(key) {
        if (!isSupported()) {
            return new Promise((resolve, reject) => {
                console.log("IndexedDB not supported");
                resolve(""); //don't show error message
            });
        }
        const userId = getUserId();
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);

        const id = `user_${userId}_${key}`;

        return new Promise((resolve, reject) => {
            const req = store.get(id);
            req.onsuccess = () => resolve(req.result ? req.result.value : null);
            req.onerror = () => reject(req.error);
        });
    }

    return {
        isSupported,
        setValue,
        getValue,
    };
})();


$(window).on('load', function () {
    $('#pre-loader').delay(250).fadeOut(function () {
        $('#pre-loader').remove();
    });
});

$(document).ready(function () {
    $.ajaxSetup({ cache: false });

    setThemeColor();

    $("#theme-color-meta-tag").attr("content", $("body").css("background-color"));

    //clicked on toggle button
    $('.sidebar-toggle-btn').on('click', function () {
        toggleLeftMenu(true);
    });

    //it's already in minimized state from server
    if ($("body").hasClass('sidebar-toggled')) {
        setTimeout(function () {
            toggleLeftMenu();
        }, 200);
    }

    //call the feather.replace() method
    feather.replace();

    $(document).bind("ajaxComplete", function () {
        feather.replace();
    });

    //expand or collapse sidebar menu 
    $("#sidebar-toggle-md").click(function () {
        $("#sidebar").toggleClass('collapsed');
        if ($("#sidebar").hasClass("collapsed")) {
            $(this).find(".fa").removeClass("fa-dedent");
            $(this).find(".fa").addClass("fa-indent");
        } else {
            $(this).find(".fa").addClass("fa-dedent");
            $(this).find(".fa").removeClass("fa-indent");
        }
    });

    $("#sidebar-collapse").click(function () {
        $("#sidebar").addClass('collapsed');
    });

    //expand or collaps sidebar menu items
    $("#sidebar-menu > .expand > a").click(function () {
        var $target = $(this).parent();
        if ($target.hasClass('main')) {
            if ($target.hasClass('open')) {
                $target.removeClass('open');
            } else {
                $("#sidebar-menu >.expand").removeClass('open');
                $target.addClass('open');
            }
            if (!$(this).closest(".collapsed").length) {
                return false;
            }
        }
    });


    $("#sidebar-toggle").click(function () {
        $("body").toggleClass("off-screen");
        $("#sidebar").removeClass("collapsed");
        $("#sidebar").toggleClass("w100p");
        $("#page-container").toggleClass("hide");
    });

    $(".change-theme").click(function () {
        if ($(this).attr("data-color")) {
            $(".custom-theme-color").remove();
            //set theme color
            setCookie("theme_color", $(this).attr("data-color"));
            setThemeColor();
        } else {
            //reset theme
            $(".custom-theme-color").remove();
            setCookie("theme_color", "");
        }

    });

    //set custom scrollbar
    setPageScrollable();
    setMenuScrollable();
    $(window).resize(function () {
        setPageScrollable();
        setMenuScrollable();
    });

    $('body').on('click', '.timeline-images:not(.app-modal-view) a', function () {

        var $gallery = $(this).closest(".timeline-images");

        $gallery.magnificPopup({
            delegate: 'a',
            type: 'image',
            closeOnContentClick: false,
            closeBtnInside: false,
            mainClass: 'mfp-with-zoom mfp-img-mobile',
            gallery: {
                enabled: true
            },
            image: {
                titleSrc: 'data-title'
            },
            callbacks: {
                change: function (item) {

                    var itemData = $(item.el).data();
                    setTimeout(function () {
                        if (itemData && itemData.viewer === 'google') {
                            $(".mfp-content").addClass("full-width-mfp-content");
                        } else {
                            $(".mfp-content").removeClass("full-width-mfp-content");
                        }
                    });

                }
            }
        });
        $gallery.magnificPopup('open');

        return false;

    });


    //convert buttons to dropdown in mobive view
    convertTabButtonsToDropdownOnMobileView();
    convertTabButtonsToDropdownOnMobileView(".convert-to-dropdown-on-mobile");
    //show home button if there is no dropdown to show
    if (!$("#mobile-function-button").html()) {
        var dashboardLink = $("#dashboard-link").attr("href");
        $("#mobile-function-button").html("<a class='nav-link home-btn' href='" + dashboardLink + "'><i data-feather='home' class='icon'></i></a>");
    }

    //replace icon on row collapsing in responsive state of datatable
    $('body').on('click', '.dataTable tr', function () {
        if ($(this).hasClass("parent")) {
            feather.replace();
        }
    });

    //add a hidden filed in form when clicking on delete file link
    $('body').on('click', '.delete-saved-file', function () {
        var fileName = $(this).attr("data-file_name");
        //add a hidden filed with the file name for delete
        $(this).closest(".saved-file-item-container").html("<input type='hidden' name=delete_file[] value='" + fileName + "' />");
        return false;
    });

    //apply summernote on textarea after click
    $('body').on('focus', 'textarea', function () {
        initOnDemandWYSIWYGEditor($(this));
    });



    //show/hide summernote dropdown
    $('body').on('click', ".note-editor [data-toggle='dropdown']", function (e) {
        $(this).closest("div").find("ul.dropdown-menu").toggleClass("show");
    });

    //hide dropdown on clicking outside of the content
    $('body').on('click', function (e) {
        if (!($(e.target).hasClass("dropdown-toggle") || $(e.target).closest(".dropdown-toggle").length)) {
            $(".note-editor [data-toggle='dropdown']").each(function () {
                $(this).closest("div").find("ul.dropdown-menu").removeClass("show");
            });
        }
    });

    setTimeout(function () {
        $('body').on('click', '.note-btn', function () {
            var $noteBtn = $(this);
            setTimeout(function () {
                if ($noteBtn.hasClass("note-icon-link")
                    || $noteBtn.find(".note-icon-link").length
                    || $noteBtn.hasClass("note-icon-picture")
                    || $noteBtn.find(".note-icon-picture").length
                    || $noteBtn.hasClass("note-icon-video")
                    || $noteBtn.find(".note-icon-video").length
                ) {
                    var $modals = $('.modal');
                    $modals.each(function () {
                        var $modalEl = $(this);
                        if ($modalEl.hasClass("note-modal")) {
                            var modalInstance = bootstrap.Modal.getInstance($modalEl[0]);
                            if (modalInstance) {
                                modalInstance.dispose();
                            }
                        }
                    });
                }
            }, 300);

            $(".note-modal .btn-close, .note-link-btn, .note-image-btn, .note-video-btn").click(function () {
                $(".note-modal").remove();
            });
        });
    }, 1000);


    //show dropdowns of navbar like a collapse panel in mobile devices
    $("#personal-language-icon, #web-notification-icon, #message-notification-icon, #user-dropdown-icon, #project-timer-icon, #quick-add-icon").click(function () {
        if (isMobile()) {
            var $dropdown = $(this).closest("li").find('.dropdown-menu'),
                handlerId = $(this).attr("id");

            $("#navbar").find('.dropdown-menu').addClass("hide");

            if ($("#navbar").find("[data-clone-id='" + handlerId + "']").attr("data-clone-id")) {
                //close dropdown
                $(this).closest("#navbar").find("[is-clone='1']").remove();
            } else {
                //open dropdown
                $(this).closest("#navbar").find("[is-clone='1']").remove(); //remove previously opened dropdown first
                appendDropdownClone($dropdown, handlerId);
            }

        }

    });

    //save the selected tab of ajax-tab list to cookie user-wise
    $('body').on('click', '[data-bs-toggle="ajax-tab"] li a', function () {
        var tab = $(this).attr("data-bs-target"),
            tabList = $(this).closest("ul").attr("id");

        setCookie("user_" + AppHelper.userId + "_" + tabList, tab);
    });

    //set keyboard condition
    document.onkeyup = function (e) {
        if (document.activeElement) {
            var activeElement = document.activeElement.tagName;

            if (activeElement) {
                activeElement = activeElement.toLowerCase();
            }

            //Shortcut isn't triggers when typing in rich text editor
            var isInTextEditor = $(document.activeElement).closest(".note-frame").length;
            if (activeElement !== "input" && activeElement !== "textarea" && !isInTextEditor && !$("#ajaxModal").hasClass('in') && !$("#confirmationModal").hasClass('in') && (!AppHelper.settings.disableKeyboardShortcuts || AppHelper.settings.disableKeyboardShortcuts === "0")) {
                var triggerBtn = keyboardShortcuts(e.which);

                $("body").find(triggerBtn).first().trigger("click");
            }
        }

        //close modal if esc pressed
        if (e.keyCode === 27) {
            $('#ajaxModal').modal("hide");
        }
    };

    //close popover on clicking outside
    //don't close popover on clicking popover content
    //this is for custom popover
    $(document).on('click', function (e) {
        $('.app-popover').each(function () {
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(".app-popover").remove();
            }

            //Ref: https://stackoverflow.com/a/14857326/10735160
        });
    });

    //close popover on clicking outside
    //don't close popover on clicking popover content
    //this is for bs popover
    $('body').on('click', function (e) {
        //did not click a popover toggle or popover content
        if (!($(e.target).attr("aria-describedby") || $(e.target).hasClass("bs-popover-end") || $(e.target).closest(".bs-popover-end").length)) {
            $('[data-bs-toggle="popover"]').popover('hide');
        }
    });


    var color = getCookie("theme_color");
    if (color == "1E202D") {
        $(".g-recaptcha").attr("data-theme", "dark");
    }

    var addCommentLink = function (event) {
        //modify comment link copied text on pasting
        var clipboardData = event.originalEvent.clipboardData.getData('text/plain');
        if (clipboardData.indexOf('/#comment') > -1) {
            //pasted comment link
            event.preventDefault();

            var splitClipboardData = clipboardData.split("/"),
                splitClipboardDataCount = splitClipboardData.length,
                commentId = splitClipboardData[splitClipboardDataCount - 1];

            if (!commentId) {
                //there has an extra / at last
                splitClipboardDataCount = splitClipboardDataCount - 1;
                commentId = splitClipboardData[splitClipboardDataCount - 1];
            }

            var splitCommentId = commentId.split("-");
            commentId = splitCommentId[1];

            var taskId = splitClipboardData[splitClipboardDataCount - 2];

            var newClipboardData = "#[" + taskId + "-" + commentId + "] (" + AppLanugage.comment + ") ";

            document.execCommand('insertText', false, newClipboardData);
        }
    };

    //normal input/textarea
    $('body').on('paste', 'input, textarea', function (e) {
        addCommentLink(e);
    });

    //summernote
    $('body').on('summernote.paste', function (e, ne) {
        addCommentLink(ne);
    });

    // Copy the clicked variable to clipboard
    $('body').on('click', '.js-variable-tag', function () {
        var $this = $(this);
        var textToCopy = $this.text().trim();

        var originalTitle = $this.attr("data-title") || "Copy";
        var afterClickTitle = $this.attr("data-after-click-title") || "Copied";

        navigator.clipboard.writeText(textToCopy).then(function () {
            $this.attr("data-bs-original-title", afterClickTitle).tooltip("show");

            setTimeout(function () {
                $this.attr("data-bs-original-title", originalTitle).tooltip("hide");
            }, 1500);
        }).catch(function (e) {
        });
    });

});

function convertTabButtonsToDropdownOnMobileView(element = ".title-button-group", mobileMirror = false) {
    if (isMobile() || mobileMirror) {

        var $dropdownMenu = $('<div class="dropdown-menu mt-1 mobile-function-button-dropdown" x-placement="top-start" role="menu"></div>');

        if (!element) {
            element = ".title-button-group"
        }
        $(element + ':not(.skip-dropdown-migration)').children().each(function () {
            var $listItem = $('<div role="presentation"></div>');
            var $it = $(this);
            if ($it.is('a')) {
                $it.addClass("dropdown-item").removeClass("btn");
                $listItem.append($it);
            } else if ($it.hasClass('dropdown')) {

                $dropdownMenu.prepend("<div class='dropdown-divider'></div>")
                $it.find(".dropdown-menu").children().each(function () {
                    $listItem.append($(this).find("a"));
                })
                $it.remove();
            }
            $dropdownMenu.prepend($listItem);
        });

        if ($dropdownMenu.children().length) {

            var icon = "grid",
                iconClass = "";
            if (mobileMirror) {
                icon = "more-vertical";
                iconClass = "icon-16";
            }

            var $dropdown = $('<div class="dropdown"></div>');
            var $dropdownToggle = $('<div class="dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="' + icon + '" class="icon ' + iconClass + '"></i></div>');
            $dropdown.append($dropdownToggle);
            $dropdown.append($dropdownMenu);
            $("#mobile-function-button").html($dropdown);
        }
    }

    $(element).addClass("skip-dropdown-migration");
    if (!$(element).children().length) {
        $(element).remove();
    }
}

function toggleLeftMenu(keyPressed) {
    if (keyPressed) {
        $("body").toggleClass('sidebar-toggled');

        if (!isMobile()) {
            if ($("body").hasClass('sidebar-toggled')) {
                //minimized
                setCookie("left_menu_minimized", "1");
            } else {
                //maximized
                setCookie("left_menu_minimized", "");
            }
        }
    }

    $("body").find("div#left-menu-toggle-mask").removeAttr("style");
    $("body").find("div#left-menu-toggle-mask").find("div.sidebar").removeAttr("style");

    if ($("body").hasClass('sidebar-toggled')) {
        initScrollbar('#left-menu-toggle-mask', {
            setHeight: $(window).height() + 20
        });
    }

    $("body").find("div#left-menu-toggle-mask").find("div.main-scrollable-page").removeAttr("style");
    $("body").find("div#left-menu-toggle-mask").find("div.main-scrollable-page").toggleClass("scrollable-page");
    $("body").find("div#left-menu-toggle-mask").find("div.main-scrollable-page").closest("div.page-container").toggleClass("overflow-auto");

    if ($("body").hasClass('sidebar-toggled')) {
        if ($(window).width() >= 990) {
            $("body").find("div#left-menu-toggle-mask").find("div.sidebar").css({ "height": $("body").find("div#left-menu-toggle-mask")[0].scrollHeight });
        }

        if (typeof window.fullCalendar !== 'undefined') {
            window.fullCalendar.refetchEvents();
        }

        setTimeout(function () {
            $("#timeline-content").removeAttr("style");
            $("#user-list-container").removeAttr("style");
        }, 100);
    }

    setPageScrollable();
};

function keyboardShortcuts(keyupCode) {
    var shortcuts = {
        "84": "#js-quick-add-task",
        "77": "#js-quick-add-multiple-task",
        "73": "#js-quick-add-project-time",
        "69": "#js-quick-add-event",
        "78": "#js-quick-add-note",
        "68": "#js-quick-add-to-do",
        "83": "#js-quick-add-ticket",
        "191": "#global-search-btn",
        "37": ".app-modal-previous-button",
        "39": ".app-modal-next-button"
    };

    return shortcuts[keyupCode];
};

//apply scrollbar on modal
function setModalScrollbar() {
    var $scroll = $("#ajaxModalContent").find(".modal-body"),
        height = $scroll.height(),
        maxHeight = $(window).height() - 200;

    if (isMobile()) {
        //show full screen in mobile devices
        maxHeight = $(window).height() - 130;
    }

    if (height > maxHeight) {
        height = maxHeight;
        initScrollbar($scroll, { setHeight: height });
    } else {
        if (isMobile()) {
            var lessHeight = 130;
            if (!$("#ajaxModalContent").find(".modal-footer").length) {
                lessHeight = 60;
            } else if ($("#ajaxModalContent").find(".modal-footer").closest(".modal-body").length && $("#ajaxModalContent").find(".modal-footer").length == 1) {
                lessHeight = 60;
            }
            $scroll.css({ "min-height": $(window).height() - lessHeight });
        };
    }
};

//upload pasted image in server for summernote input box and return reference as image element
function uploadPastedImage(file, $instance) {
    appLoader.show();

    var data = new FormData();
    data.append("file", file);

    if ($instance.data("full_size_image")) {
        data.append("full_size_image", 1);
    }

    appAjaxRequest({
        url: AppHelper.uploadPastedImageLink,
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        success: function (imageHtml) {
            if (imageHtml) {
                insertHTMLintoWYSIWYGEditor($instance, imageHtml);
            }

            appLoader.hide();
        }
    });
};


//append dropdown clone to the topbar
function appendDropdownClone($dropdown, handlerId) {
    var $dropdownClone = $dropdown.clone();
    $dropdownClone.attr({ "is-clone": "1", "data-clone-id": handlerId }); //add attributes to grab later
    $dropdownClone.css({ "display": "block", "width": "100%", "min-width": "100%", "margin-top": "0" });
    $dropdownClone.removeClass("hide");
    $("#navbar").append($dropdownClone);
}

//set scrollbar on page
function setPageScrollable() {

    $("#page-content").css("min-height", $(window).height() - 115);

    if ($(window).width() <= 640) {
        $('html').css({ "overflow": "initial" });
        $('body').css({ "overflow": "initial" });
    } else {
        if ($("body").find("div.footer").length) {
            //has footer
            if ($("body").find("nav.navbar").length) {

                //has topbar
                initScrollbar('.scrollable-page', {
                    setHeight: $(window).height() - 60
                });
            } else {
                initScrollbar('.scrollable-page', {
                    setHeight: $(window).height() - 48
                });
            }
        } else {

            initScrollbar('.scrollable-page', {
                setHeight: $(window).height() - 65
            });
        }
    }
};
//set scrollbar on left menu
function setMenuScrollable() {
    initScrollbar('.sidebar-scroll', {
        setHeight: $(window).height() - 65
    });
};

function initScrollbar(selector, options) {
    if (!options) {
        options = {};
    }

    if (!$(selector).length)
        return false;

    if (selector && (typeof selector === "object")) {
        //it's a jquery element
        //add a id with the elment and then apply scrollbar
        var id = getRandomAlphabet(8);
        selector.attr("id", id);
        selector = "#" + id;
    }

    var defaults = {
        wheelPropagation: true
    },
        settings = $.extend({}, defaults, options);


    if (options.setHeight) {
        $(selector).css({ "height": settings.setHeight + "px", position: "relative" })
    }

    if (AppHelper.settings.scrollbar == "native") {
        $(selector).css({ "overflow-y": "scroll" });
    } else {
        var ps = new PerfectScrollbar(selector);
    }

    $(selector).data("scrollbar-added", "1");

};

// generate reandom string 
function getRndomString(length) {
    var result = '',
        chars = '!-().0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for (var i = length; i > 0; --i)
        result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
};


// getnerat random small alphabet 
function getRandomAlphabet(length) {
    var result = '',
        chars = 'abcdefghijklmnopqrstuvwxyz';
    for (var i = length; i > 0; --i)
        result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
};


function attachDropzoneWithForm(dropzoneTarget, uploadUrl, validationUrl, options) {
    var $dropzonePreviewArea = $(dropzoneTarget),
        $dropzonePreviewScrollbar = $dropzonePreviewArea.find(".post-file-dropzone-scrollbar"),
        $previews = $dropzonePreviewArea.find(".post-file-previews"),
        $postFileUploadRow = $dropzonePreviewArea.find(".post-file-upload-row"),
        $uploadFileButton = $dropzonePreviewArea.find(".upload-file-button"),
        $submitButton = $dropzonePreviewArea.find("button[type=submit]"),
        previewsContainer = getRandomAlphabet(15),
        postFileUploadRowId = getRandomAlphabet(15),
        uploadFileButtonId = getRandomAlphabet(15);

    //set random id with the previws 
    $previews.attr("id", previewsContainer);
    $postFileUploadRow.attr("id", postFileUploadRowId);
    $uploadFileButton.attr("id", uploadFileButtonId);


    //get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
    var previewNode = document.querySelector("#" + postFileUploadRowId);
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);

    if (!options)
        options = {};

    var postFilesDropzone = new Dropzone(dropzoneTarget, {
        url: uploadUrl,
        thumbnailWidth: 80,
        thumbnailHeight: 80,
        parallelUploads: 20,
        maxFilesize: 3000,
        previewTemplate: previewTemplate,
        dictDefaultMessage: AppLanugage.fileUploadInstruction,
        autoQueue: true,
        previewsContainer: "#" + previewsContainer,
        clickable: "#" + uploadFileButtonId,
        maxFiles: options.maxFiles ? options.maxFiles : 1000,
        timeout: 20000000, //20000 seconds
        sending: function (file, xhr, formData) {
            formData.append(AppHelper.csrfTokenName, AppHelper.csrfHash);
        },
        init: function () {
            this.on("maxfilesexceeded", function (file) {
                this.removeAllFiles();
                this.addFile(file);
            });
        },
        accept: function (file, done) {
            if (file.name.length > 200) {
                done(AppLanugage.fileNameTooLong);
            }

            if (file.size > AppHelper.uploadMaxFileSize) {
                done(AppLanugage.fileSizeTooLong);
                appAlert.error(AppLanugage.fileSizeTooLong);
            }

            $dropzonePreviewScrollbar.removeClass("hide");
            initScrollbar($dropzonePreviewScrollbar, { setHeight: 90 });

            $dropzonePreviewScrollbar.parent().removeClass("hide");
            $dropzonePreviewArea.find("textarea").focus();

            var postData = { file_name: file.name, file_size: file.size };

            //validate the file
            appAjaxRequest({
                url: validationUrl,
                data: postData,
                cache: false,
                type: 'POST',
                dataType: "json",
                success: function (response) {
                    if (response.success) {

                        $(file.previewTemplate).append('<input type="hidden" name="file_names[]" value="' + file.name + '" />\n\
                                 <input type="hidden" name="file_sizes[]" value="' + file.size + '" />');
                        done();
                    } else {
                        appAlert.error(response.message);
                        $(file.previewTemplate).find("input").remove();
                        done(response.message);

                    }
                }
            });
        },
        processing: function () {
            $submitButton.prop("disabled", true);
            appLoader.show();
        },
        queuecomplete: function () {
            $submitButton.prop("disabled", false);
            appLoader.hide();
        },
        reset: function (file) {
            $dropzonePreviewScrollbar.addClass("hide");
        },
        fallback: function () {
            //add custom fallback;
            $("body").addClass("dropzone-disabled");

            $uploadFileButton.click(function () {
                //fallback for old browser
                $(this).html("<i data-feather='camera' class='icon-16'></i> Add more");

                $dropzonePreviewScrollbar.removeClass("hide");
                initScrollbar($dropzonePreviewScrollbar, { setHeight: 90 });

                $dropzonePreviewScrollbar.parent().removeClass("hide");
                $previews.prepend("<div class='clearfix p5 file-row'><button type='button' class='btn btn-xs btn-danger pull-left mr10 remove-file'><i data-feather='x' class='icon-16'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>");

            });
            $previews.on("click", ".remove-file", function () {
                $(this).parent().remove();
            });
        },
        success: function (file) {
            setTimeout(function () {
                $(file.previewElement).find(".progress-bar-striped").addClass("progress-bar-success").removeClass("progress-bar-striped progress-bar-animated bg-warning");
            }, 1000);
        }
    });

    //bind image pasting feature in a raw input box if there has any dropzone with the form
    $dropzonePreviewArea.find("textarea").each(function () {
        var $textArea = $(this);

        if (AppHelper.settings.enableRichTextEditor === "0" || !AppHelper.settings.enableRichTextEditor || (AppHelper.settings.enableRichTextEditor === "1" && $textArea.attr("data-rich-text-editor") == undefined)) {
            $textArea.on('paste', function (e) {
                var data = e.originalEvent;

                if (data.clipboardData && data.clipboardData.items) {
                    var items = data.clipboardData.items;

                    for (var i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf('image') !== -1) {

                            //so, pasted item is an image
                            var image = items[i].getAsFile(),
                                imageName = "image_" + getRandomAlphabet(5) + ".png", //add random string to upload multiple images
                                blob = image.slice(0, image.size, image.type),
                                newImage = new File([blob], imageName, { type: image.type });

                            postFilesDropzone.addFile(newImage);
                        }
                    }
                }

            });
        }

    });

    return postFilesDropzone;
};

function teamAndMemberSelect2Format(option) {
    if (option.type === "team") {
        return "<i data-feather='users' class='icon-16 info'></i> " + option.text;
    } else {
        return "<i data-feather='user' class='icon-16'></i> " + option.text;
    }
};

function setDatePicker(element, options) {
    if (!options) {
        options = {};
    }
    var dateFormat = getJsDateFormat();
    var settings = $.extend({}, {
        autoclose: true,
        language: "custom",
        todayHighlight: true,
        weekStart: AppHelper.settings.firstDayOfWeek,
        format: dateFormat,
        onChangeDate: function (response) {
        }
    }, options);


    //set dateformat
    $.each(element.split(","), function (index, el) {
        $(el).attr("data-convert-date-format", "1");
        if (isMobile()) {
            $(el).attr("readonly", "true"); //make fields read only for mobile devices
        }

        var value = $(el).val();

        if (value) {
            var dateArray = value.split("-"),
                year = dateArray[0],
                month = dateArray[1],
                day = dateArray[2];

            if (year && month && day) {
                value = dateFormat.replace("yyyy", year).replace("mm", month).replace("dd", day);
                $(el).val(value);
            }
        }
        if (!$(el).attr("placeholder") || $(el).attr("placeholder") === "YYYY-MM-DD") {
            $(el).attr("placeholder", dateFormat.toUpperCase());
        }

    });


    $(element).datepicker(settings).on('changeDate', function (response) {
        settings.onChangeDate(new Date(response.date.getTime() - (response.date.getTimezoneOffset() * 60000)).toISOString().split("T")[0]);
    });


};


function getJsDateFormat() {
    var formats = {
        "d-m-Y": "dd-mm-yyyy",
        "m-d-Y": "mm-dd-yyyy",
        "Y-m-d": "yyyy-mm-dd",
        "d/m/Y": "dd/mm/yyyy",
        "m/d/Y": "mm/dd/yyyy",
        "Y/m/d": "yyyy/mm/dd",
        "d.m.Y": "dd.mm.yyyy",
        "m.d.Y": "mm.dd.yyyy",
        "Y.m.d": "yyyy.mm.dd"
    };

    return formats[AppHelper.settings.dateFormat] || "yyyy-mm-dd";
};

function setTimePicker(element, options) {
    if (!options) {
        options = {};
    }

    var appendWidgetTo = "#ajaxModal";
    if (!$("#ajaxModal").hasClass("show")) {
        appendWidgetTo = "body";
    }

    var showMeridian = AppHelper.settings.timeFormat == "24_hours" ? false : true;

    var settings = $.extend({}, {
        minuteStep: AppHelper.settings.timepickerMinutesInterval,
        defaultTime: "",
        appendWidgetTo: appendWidgetTo,
        showMeridian: showMeridian
    }, options);

    $(element).timepicker(settings);

    $(element).timepicker().on('show.timepicker', function (e) {
        feather.replace();
    });
};


function getSummernoteToolbarConfig(toolbar_type) {

    var summernoteTollbarConfig = [];
    summernoteTollbarConfig["no_toolbar"] = [];

    summernoteTollbarConfig["mini_toolbar"] = [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['para', ['ul', 'ol']],
        ['table', ['table']],
        ['insert', ['link', 'hr']],
        ['view', ['fullscreen', 'codeview']]
    ];

    summernoteTollbarConfig["pdf_friendly_toolbar"] = [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link', 'hr', 'picture']],
        ['view', ['fullscreen', 'codeview']]
    ];

    summernoteTollbarConfig["page_builder_toolbar"] = [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['fontname', ['fontname']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['height', ['height']],
        ['table', ['table']],
        ['insert', ['link', 'hr', 'picture', 'video']],
        ['view', ['fullscreen', 'codeview']]
    ];

    if (summernoteTollbarConfig[toolbar_type]) {
        return summernoteTollbarConfig[toolbar_type];
    } else {
        return summernoteTollbarConfig["mini_toolbar"];
    }
}

function getTinyMceToolbarConfig(toolbar_type) {

    var tinyMceTollbarConfig = [];
    tinyMceTollbarConfig["no_toolbar"] = [];
    tinyMceTollbarConfig["mini_toolbar"] = 'blocks bold italic underline strikethrough link  table  checklist numlist bullist code';
    tinyMceTollbarConfig["pdf_friendly_toolbar"] = 'blocks bold italic underline strikethrough forecolor fontsize link image table checklist numlist bullist align code';
    tinyMceTollbarConfig["page_builder_toolbar"] = 'blocks bold italic underline strikethrough fontfamily forecolor fontsize link image media table checklist numlist bullist indent outdent align lineheight code';

    if (tinyMceTollbarConfig[toolbar_type]) {
        return tinyMceTollbarConfig[toolbar_type];
    } else {
        return tinyMceTollbarConfig["mini_toolbar"];
    }
}

//apply richTextEditor to all textarea, if those have any values
function initAllNotEmptyWYSIWYGEditors(notFocus, $area) {

    var $element = $("textarea");
    if ($area) {
        $element = $area.find("textarea");
    }

    $element.each(function () {
        var $instance = $(this);
        if ($instance.val()) {

            setTimeout(function () {
                initOnDemandWYSIWYGEditor($instance, notFocus);
            }, 100);
        }
    });
};


function initOnDemandWYSIWYGEditor($instance, notFocus) {

    if (AppHelper.settings.enableRichTextEditor != "1") {
        return false;
    }

    if ($instance.attr("data-rich-text-editor") != undefined) {

        // $instance.fadeOut(100, function () {
        if (notFocus) {
            $instance.attr("data-no_focus", "1");
        }

        if (!$instance.attr("data-height")) {
            $instance.attr("data-height", 150);
        }


        if (!$instance.attr("data-toolbar")) {
            $instance.attr("data-toolbar", "mini_toolbar");
        }

        initWYSIWYGEditor($instance);
        // });


    }
};


function cleanEditorStyles(instance) {
    var $editor = $(instance);
    if (!$editor) {
        return false;
    }

    var editor_data = $editor.data();

    if (editor_data && editor_data.clean_pdf_html == "1") {
        $editor.next().find('.note-editable [style]').each(function () {
            let styleAttr = this.getAttribute('style'); // Get raw style
            // Remove only specific properties with exact values (matching `var()` syntax)

            if (styleAttr) {
                styleAttr = styleAttr
                    .replace(/color:\s*var\([^\)]+\);?/g, '') // Match any color: var(...)
                    .replace(/font-weight:\s*var\([^\)]+\);?/g, '') // Match any font-weight: var(...)
                    .replace(/text-align:\s*var\([^\)]+\);?/g, '') // Match any text-align: var(...)
                    .replace(/background-color:\s*var\([^\)]+\);?/g, ''); // Match any background-color: var(...)

                // Trim and reapply styles
                styleAttr = styleAttr.trim();
                if (styleAttr === '') {
                    this.removeAttribute('style'); // Remove empty style attribute
                } else {
                    this.setAttribute('style', styleAttr); // Update the style attribute
                }
            }
        });

        //clear all span tags and keep only value
        $(instance).next().find('.note-editable span').each(function () {
            var value = $(this).html();
            if (!value.includes('<img')) {
                //Replace the <span> with its text content
                $(this).replaceWith($(this).text());
            }
        });

        //remove empty p tags.
        $(instance).next().find('.note-editable p').each(function () {
            var value = $(this).html().trim();
            if (value == '') {
                $(this).remove();
            }
        });

        //convert ------ in h5 
        $(instance).next().find('.note-editable h5').each(function () {
            var text = $(this).text();
            var dashCount = (text.match(/-/g) || []).length;
            if (dashCount >= 5) {
                var textAlign = $(this).css("text-align");
                var h5Color = $(this).css("color");
                var color = $(this).find("font").attr("color") || h5Color;
                var defaultStyle = 'line-height:1px; letter-spacing:-2px;';
                if (textAlign) {
                    defaultStyle += ' text-align:' + textAlign + ';';
                }
                if (color) {
                    defaultStyle += ' color:' + color + ';';
                }

                $(this).attr('style', defaultStyle);

                $(this).html(text);
            }
        });


    }


    // In template, support the variable as a link href and don't add the http by default
    $(instance).next().find('.note-editable a').each(function () {
        var href = $(this).attr('href');

        if (href) {
            // Clean the href if it matches the pattern
            var cleanedHref = href.replace(/(https?:\/\/)(\{[^}]+\})/g, '$2');
            $(this).attr('href', cleanedHref);
        }
    });

}


var initSummernote = function ($instance) {

    var editorData = $instance.data() || {};
    var options = {};
    options.toolbar = getSummernoteToolbarConfig(editorData.toolbar);
    options.height = editorData.height;
    options.lang = AppLanugage.localeLong;
    options.focus = editorData.no_focus == "1" ? false : true;


    var settings = $.extend({}, {
        height: 250,
        disableDragAndDrop: true,
        styleWithSpan: false,

        callbacks: {

            onChange: delayAction(function () {
                var instance = this;
                cleanEditorStyles(instance);
            }, 1000),
            onBlur: delayAction(function () {
                var instance = this;
                cleanEditorStyles(instance);
            }, 1000),
            onFocus: function () {
                var instance = this;
                cleanEditorStyles(instance);
            },
            onImageUpload: function (files, editor, $editable) {
                for (var i = 0; i < files.length; i++) {
                    uploadPastedImage(files[i], $instance);
                }
                $(".note-modal").remove();
            }
        }
    }, options);

    if (editorData.mention != undefined) {
        //generate mention data for summernote
        appAjaxRequest({
            url: editorData.mention_source,
            data: { project_id: editorData.mention_project_id },
            dataType: "json",
            method: "POST",
            success: function (result) {
                if (result.success && result.data) {
                    settings.hint = {
                        mentions: result.data,
                        match: /\B@(\w*)$/,
                        search: function (keyword, callback) {
                            callback($.grep(this.mentions, function (item) {
                                return item.name.toLowerCase().indexOf(keyword.toLowerCase()) === 0;
                            }));
                        },
                        template: function (item) {
                            return item.name;
                        },
                        content: function (item) {
                            return $('<span>' + item.content + '&nbsp;</span>')[0];
                        }
                    };
                }

                $instance.summernote(settings);
            }
        });
    } else {
        $instance.summernote(settings);

        $instance.on('summernote.codeview.toggled', function (e) {
            var $this = $(e.target);

            if ($this.summernote('codeview.isActivated')) {
                var htmlContent = $this.summernote('code');

                if (htmlContent) {
                    // Insert line breaks between normal tags
                    htmlContent = htmlContent.replace(/></g, '>\n<');

                    // Merge self-closing tags (like <br>, <img>) back to previous line
                    htmlContent = htmlContent.replace(/\n(<(br|hr|img|input|meta|link|source|area|col|embed|param|track)[^>]*?>)/gi, '$1');

                    // Merge empty tags (like <p><br></p>) into one line
                    htmlContent = htmlContent.replace(/<(\w+)([^>]*)>\s*(<(br|hr|img|input|meta|link|source|area|col|embed|param|track)[^>]*?>)\s*<\/\1>/gi, '<$1$2>$3</$1>');

                    // Merge totally empty tags (like <div></div>) into one line
                    htmlContent = htmlContent.replace(/<(\w+)([^>]*)>\s*<\/\1>/g, '<$1$2></$1>');

                    $this.summernote('code', htmlContent);
                }
            }
        });

    }

}

var getTinyMceSelector = function ($instance) {
    var id = $instance.data("tinymce_selector");
    var tagname = $instance.get(0) ? $instance.get(0).tagName : "";
    if (tagname) {
        return tagname.toLowerCase() + "[data-tinymce_selector='" + id + "']";
    } else {
        return "textarea[data-tinymce_selector='" + id + "']";
    }
}


var initTinyMCE = function ($instance) {

    if (!$instance.attr("data-tinymce_selector")) {
        $instance.attr("data-tinymce_selector", getRandomAlphabet(8));
    }

    var selector = getTinyMceSelector($instance);

    destroyWYSIWYGEditor(selector, true);

    var editorData = $instance.data() || {};
    var options = {};
    options.toolbar = getTinyMceToolbarConfig(editorData.toolbar);
    options.height = (editorData.height ? editorData.height : 600) + 100;
    options.lang = AppLanugage.localeLong;
    options.directionality = $(document).attr("dir") == "rtl" ? "rtl" : "ltr";

    var contentStyle = 'body { color: #4e5e6a; }';
    if ($("body").attr("data-color") == "1E202D") {
        contentStyle = 'body { color: #898fa9; }';
    }

    var settings = $.extend({}, {
        selector: selector,
        menubar: false,
        //statusbar: false,
        branding: false,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount linkchecker code',
        toolbar_mode: "wrap",  //'floating', 'sliding', 'scrolling', 'wrap'
        content_style: contentStyle,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {

            appLoader.show();

            var data = new FormData();
            data.append("file", blobInfo.blob(), blobInfo.filename());

            if (editorData.full_size_image == "1") {
                data.append("full_size_image", 1);
            }

            appAjaxRequest({
                url: AppHelper.uploadPastedImageLink,
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                success: function (imageHtml) {
                    if (imageHtml) {

                        tinymce.activeEditor.insertContent(imageHtml);

                        var imageUrl = imageHtml.match(/<img[^>]+src=['"]([^'"]+)['"]/i);

                        var finalImageUrl = "";
                        if (imageUrl && imageUrl[1]) {
                            finalImageUrl = imageUrl[1];
                        }

                        resolve(finalImageUrl);

                        $('[data-mce-name="close"]').click();

                        var content = tinymce.activeEditor.getContent();

                        // Find the <img> tag with the data-mce-src attribute
                        content = content.replace(/<img\s+[[^>]*data-mce-src=['"][^'"]+['"][^>]*>/gi, imageHtml);

                        //remove image tag which is using the data:base64
                        content = content.replace(/<img\s+[^>]*src="data:[^"]*"[^>]*>/gi, '');
                        tinymce.activeEditor.setContent(content);
                    }

                    appLoader.hide();
                },
                onerror: function () {
                    reject('HTTP Error');
                }
            });

        }),
        init_instance_callback: function (editor) {
            if (editorData.mention_source) {
                $(editor.contentDocument.activeElement).appMention({
                    source: editorData.mention_source,
                    data: { project_id: editorData.mention_project_id }
                });
            }

            if ($instance.attr("data-move-cursor-to-first") == "1") {
                tinymce.activeEditor.selection.setCursorLocation(
                    tinymce.activeEditor.dom.select("p")[0],
                    0
                );
            }

        },
        setup: (editor) => {
            editor.on('init', function (e) {
                if (editorData.no_focus != "1") {
                    editor.selection.select(editor.getBody(), true);
                    editor.selection.collapse(false);
                    editor.focus();
                }
            }),
                editor.on('change', function () {
                    editor.save();
                })
        }
    }, options);

    tinymce.init(settings);

}

function destroyWYSIWYGEditor(selector, intialLoad) {
    $instance = selector;
    if (!($instance instanceof jQuery)) {
        $instance = $(selector);
    }

    if (AppHelper.settings.wysiwygEditor == "tinymce") {
        var tinySelector = getTinyMceSelector($instance);
        tinymce.remove(tinySelector);
        if (!intialLoad) {
            $instance.val("");
        }
    } else {
        $instance.summernote('destroy');
    }
};


function initWYSIWYGEditor(selector) {
    var $instance = selector;

    if (!($instance instanceof jQuery)) {
        $instance = $(selector);
    }

    if (AppHelper.settings.wysiwygEditor == "tinymce") {
        initTinyMCE($instance);
    } else {
        initSummernote($instance);
    }
};

function getWYSIWYGEditorHTML(selector) {
    var $instance = selector;
    if (!($instance instanceof jQuery)) {
        $instance = $(selector);
    }

    if (AppHelper.settings.wysiwygEditor == "tinymce") {
        return tinymce.get(getTinyMceSelector($instance)).getContent();
    } else {
        return $instance.summernote('code');
    }
};

function setWYSIWYGEditorHTML(selector, html) {
    var $instance = selector;
    if (!($instance instanceof jQuery)) {
        $instance = $(selector);
    }

    if (AppHelper.settings.wysiwygEditor == "tinymce") {
        if ($instance.attr("id") && tinymce.get($instance.attr("id"))) {
            tinymce.get($instance.attr("id")).setContent(html);
        }
    } else {
        $instance.summernote('code', html);
    }
};

function insertHTMLintoWYSIWYGEditor(selector, html) {
    var $instance = selector;
    if (!($instance instanceof jQuery)) {
        $instance = $(selector);
    }

    if (AppHelper.settings.wysiwygEditor == "tinymce") {
        if ($instance.attr("id") && tinymce.get($instance.attr("id"))) {
            tinymce.get($instance.attr("id")).insertContent(html);
        }
    } else {
        $instance.summernote('restoreRange');
        $instance.summernote('pasteHTML', html);
    }

};


function combineCustomFieldsColumns(defaultFields, customFieldString) {
    if (defaultFields && customFieldString) {

        var startAfter = defaultFields.slice(-1)[0];
        //count no of custom fields
        var noOfCustomFields = customFieldString.split(',').length - 1;
        if (noOfCustomFields) {
            for (var i = 1; i <= noOfCustomFields; i++) {
                defaultFields.push(i + startAfter);
            }
        }
    }
    return defaultFields;
};


function setCookie(cname, cvalue, exdays) {
    if (!exdays) {
        exdays = 1000;
    }

    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function setThemeColor() {
    var color = getCookie("theme_color") || AppHelper.settings.defaultThemeColor;
    if (color && color !== "F2F2F2") {
        var href = AppHelper.assetsDirectory + "css/color/" + color + ".css";
        $("#custom-theme-color").remove();
        $('head').append('<link id="custom-theme-color" class="custom-theme-color" rel="stylesheet" href="' + href + '" type="text/css" />');
    }

    $("body").addClass("color-" + color).removeClass("color-" + $("body").attr("data-color")).attr("data-color", color);

}

function isMobile() {
    return window.matchMedia("(max-width: 800px)").matches;
}


function getUniqueArray(array) {
    var returnUique = function (value, index, array) {
        return array.indexOf(value) === index;
    };
    return array.filter(returnUique);
}

function initSignature(element, options) {
    if (!options) {
        options = {};
    }

    var settings = $.extend({}, {
        backgroundColor: 'rgb(255, 255, 255)',
        onEnd: function () {
            storeSignatureData();
        }
    }, options);

    //create an input field to grab image data
    var dom = "<input type='hidden' name='" + element + "' id='" + element + "-data' " + (settings.required ? (" class='validate-hidden' data-rule-required='true' data-msg-required='" + settings.requiredMessage + "' ") : "") + " />";
    $("#" + element).append(dom);

    var wrapper = document.getElementById(element);
    var canvas = wrapper.querySelector("canvas");

    var signaturePad = new SignaturePad(canvas, settings);

    //save base64 image data on input field
    function storeSignatureData() {
        var encodedData = signaturePad.toDataURL();
        if (signaturePad.isEmpty()) {
            encodedData = "";
        }

        $("#" + element + "-data").val(encodedData);
    }

    //set canvas width
    canvas.width = wrapper.offsetWidth - 2;

};