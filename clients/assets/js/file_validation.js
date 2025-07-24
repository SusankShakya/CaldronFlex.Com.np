/**
 * File Validation JavaScript Module
 * Handles client-side file validation before upload
 * Part of Sprint 3 Customization Features
 */

$(document).ready(function () {
    "use strict";

    // Initialize file validation functionality
    var FileValidation = {
        
        // Configuration
        config: {
            fileInput: '.file-upload-input',
            dropzoneElement: '.file-dropzone',
            progressBar: '.upload-progress-bar',
            progressText: '.upload-progress-text',
            fileListContainer: '.file-list-container',
            errorContainer: '.file-validation-errors',
            maxFileSize: 10 * 1024 * 1024, // 10MB default
            allowedFormats: {
                'image': ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'],
                'document': ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
                'design': ['ai', 'psd', 'eps', 'indd', 'sketch', 'fig'],
                'print': ['pdf', 'tiff', 'tif', 'eps', 'ai'],
                'archive': ['zip', 'rar', '7z', 'tar', 'gz']
            },
            validationRules: {
                checkFileSize: true,
                checkFileFormat: true,
                checkImageDimensions: true,
                checkColorSpace: true,
                scanForVirus: false // Server-side only
            }
        },

        // File validation results cache
        validationCache: {},

        // Initialize the module
        init: function() {
            this.bindEvents();
            this.setupDropzone();
            this.loadValidationRules();
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // File input change
            $(document).on('change', this.config.fileInput, function(e) {
                var files = e.target.files;
                self.validateFiles(files, $(this));
            });

            // Drag and drop events
            $(this.config.dropzoneElement).on({
                'dragover dragenter': function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass('drag-over');
                },
                'dragleave': function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('drag-over');
                },
                'drop': function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('drag-over');
                    
                    var files = e.originalEvent.dataTransfer.files;
                    self.validateFiles(files, $(this));
                }
            });

            // Remove file button
            $(document).on('click', '.remove-file-btn', function(e) {
                e.preventDefault();
                var fileId = $(this).data('file-id');
                self.removeFile(fileId);
            });

            // Retry validation button
            $(document).on('click', '.retry-validation-btn', function(e) {
                e.preventDefault();
                var fileId = $(this).data('file-id');
                self.retryValidation(fileId);
            });
        },

        // Setup dropzone if not already initialized
        setupDropzone: function() {
            var $dropzone = $(this.config.dropzoneElement);
            
            if ($dropzone.length && !$dropzone.hasClass('dropzone-initialized')) {
                $dropzone.addClass('dropzone-initialized');
                
                // Add helpful text if not present
                if (!$dropzone.find('.dropzone-text').length) {
                    $dropzone.append('<div class="dropzone-text">' + 
                        '<i class="feather icon-upload-cloud"></i><br>' +
                        (AppLang.drag_drop_files || 'Drag & drop files here or click to browse') + 
                        '</div>');
                }
            }
        },

        // Load validation rules from server
        loadValidationRules: function() {
            var self = this;
            
            // Check if we have item-specific validation rules
            var itemId = $('#item_id').val();
            if (!itemId) {
                return;
            }

            $.ajax({
                url: '/file_validation/get_rules',
                type: 'POST',
                data: {
                    item_id: itemId,
                    csrf_token: getCsrfToken()
                },
                success: function(response) {
                    if (response.success && response.rules) {
                        // Update validation config
                        if (response.rules.max_file_size) {
                            self.config.maxFileSize = response.rules.max_file_size * 1024 * 1024; // Convert MB to bytes
                        }
                        if (response.rules.allowed_formats) {
                            self.config.allowedFormats.custom = response.rules.allowed_formats;
                        }
                        if (response.rules.validation_rules) {
                            $.extend(self.config.validationRules, response.rules.validation_rules);
                        }
                    }
                },
                error: function() {
                    // Use default rules on error
                }
            });
        },

        // Validate multiple files
        validateFiles: function(files, $input) {
            var self = this;
            var validFiles = [];
            var errors = [];

            // Clear previous errors
            this.clearErrors();

            // Validate each file
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var validation = this.validateSingleFile(file);
                
                if (validation.valid) {
                    validFiles.push(file);
                    this.addFileToList(file, validation);
                } else {
                    errors = errors.concat(validation.errors);
                }
            }

            // Show errors if any
            if (errors.length > 0) {
                this.showErrors(errors);
            }

            // Process valid files
            if (validFiles.length > 0) {
                this.processValidFiles(validFiles, $input);
            }
        },

        // Validate single file
        validateSingleFile: function(file) {
            var errors = [];
            var warnings = [];
            var fileId = this.generateFileId(file);

            // Check cache first
            if (this.validationCache[fileId]) {
                return this.validationCache[fileId];
            }

            // File size validation
            if (this.config.validationRules.checkFileSize) {
                if (file.size > this.config.maxFileSize) {
                    errors.push((AppLang.file_too_large || 'File "{0}" is too large. Maximum size is {1}MB')
                        .replace('{0}', file.name)
                        .replace('{1}', (this.config.maxFileSize / 1024 / 1024).toFixed(2)));
                }
            }

            // File format validation
            if (this.config.validationRules.checkFileFormat) {
                var extension = this.getFileExtension(file.name);
                if (!this.isFormatAllowed(extension)) {
                    errors.push((AppLang.invalid_file_format || 'File "{0}" has invalid format. Allowed formats: {1}')
                        .replace('{0}', file.name)
                        .replace('{1}', this.getAllowedFormatsString()));
                }
            }

            // Image-specific validations
            if (this.isImageFile(file)) {
                // These validations will be done asynchronously
                this.validateImageFile(file, fileId);
            }

            var result = {
                valid: errors.length === 0,
                errors: errors,
                warnings: warnings,
                fileId: fileId
            };

            // Cache the result
            this.validationCache[fileId] = result;

            return result;
        },

        // Validate image file (dimensions, color space, etc.)
        validateImageFile: function(file, fileId) {
            var self = this;
            var reader = new FileReader();

            reader.onload = function(e) {
                var img = new Image();
                
                img.onload = function() {
                    var validation = {
                        width: this.width,
                        height: this.height,
                        aspectRatio: (this.width / this.height).toFixed(2)
                    };

                    // Check minimum dimensions
                    if (self.config.validationRules.checkImageDimensions) {
                        var minWidth = parseInt($('#min_width').val()) || 300;
                        var minHeight = parseInt($('#min_height').val()) || 300;
                        var minDPI = parseInt($('#min_dpi').val()) || 72;

                        if (validation.width < minWidth || validation.height < minHeight) {
                            self.addImageValidationError(fileId, 
                                (AppLang.image_too_small || 'Image dimensions ({0}x{1}) are below minimum ({2}x{3})')
                                .replace('{0}', validation.width)
                                .replace('{1}', validation.height)
                                .replace('{2}', minWidth)
                                .replace('{3}', minHeight)
                            );
                        }

                        // Estimate DPI (assuming standard print size)
                        var estimatedDPI = self.estimateImageDPI(validation.width, validation.height, file.size);
                        if (estimatedDPI < minDPI) {
                            self.addImageValidationWarning(fileId,
                                (AppLang.low_resolution_warning || 'Image may have low resolution for printing (estimated {0} DPI)')
                                .replace('{0}', estimatedDPI)
                            );
                        }
                    }

                    // Update file info display
                    self.updateFileInfo(fileId, validation);
                };

                img.onerror = function() {
                    self.addImageValidationError(fileId, AppLang.invalid_image_file || 'Invalid or corrupted image file');
                };

                img.src = e.target.result;
            };

            reader.readAsDataURL(file);
        },

        // Process valid files for upload
        processValidFiles: function(files, $input) {
            var self = this;

            // If using Rise CRM's file upload
            if (typeof uploadFiles === 'function') {
                uploadFiles(files, {
                    onProgress: function(progress) {
                        self.updateProgress(progress);
                    },
                    onComplete: function(response) {
                        self.handleUploadComplete(response);
                    },
                    onError: function(error) {
                        self.showErrors([error]);
                    }
                });
            } else {
                // Custom upload implementation
                this.uploadFiles(files);
            }
        },

        // Custom file upload implementation
        uploadFiles: function(files) {
            var self = this;
            var formData = new FormData();

            // Add files to form data
            for (var i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            // Add additional data
            formData.append('csrf_token', getCsrfToken());
            formData.append('item_id', $('#item_id').val() || '');

            // Show progress bar
            this.showProgress();

            $.ajax({
                url: '/file_validation/upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    
                    // Upload progress
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
                            self.updateProgress(percentComplete);
                        }
                    }, false);
                    
                    return xhr;
                },
                success: function(response) {
                    self.handleUploadComplete(response);
                },
                error: function(xhr, status, error) {
                    self.showErrors([AppLang.upload_failed || 'Upload failed: ' + error]);
                    self.hideProgress();
                }
            });
        },

        // Add file to list display
        addFileToList: function(file, validation) {
            var fileId = validation.fileId;
            var $container = $(this.config.fileListContainer);
            
            if (!$container.length) {
                return;
            }

            var fileHtml = '<div class="file-item" data-file-id="' + fileId + '">';
            fileHtml += '<div class="file-icon"><i class="' + this.getFileIcon(file) + '"></i></div>';
            fileHtml += '<div class="file-info">';
            fileHtml += '<div class="file-name">' + file.name + '</div>';
            fileHtml += '<div class="file-size">' + this.formatFileSize(file.size) + '</div>';
            fileHtml += '<div class="file-status"><span class="badge bg-info">' + 
                        (AppLang.validating || 'Validating...') + '</span></div>';
            fileHtml += '</div>';
            fileHtml += '<div class="file-actions">';
            fileHtml += '<button class="btn btn-sm btn-danger remove-file-btn" data-file-id="' + fileId + '">';
            fileHtml += '<i class="feather icon-x"></i></button>';
            fileHtml += '</div>';
            fileHtml += '</div>';

            $container.append(fileHtml);
        },

        // Update file info after validation
        updateFileInfo: function(fileId, info) {
            var $fileItem = $('.file-item[data-file-id="' + fileId + '"]');
            
            if ($fileItem.length && info) {
                var $fileInfo = $fileItem.find('.file-info');
                
                // Add dimension info for images
                if (info.width && info.height) {
                    $fileInfo.append('<div class="file-dimensions text-muted small">' + 
                        info.width + ' × ' + info.height + ' px</div>');
                }
            }
        },

        // Show upload progress
        showProgress: function() {
            $(this.config.progressBar).parent().show();
            this.updateProgress(0);
        },

        // Hide upload progress
        hideProgress: function() {
            $(this.config.progressBar).parent().hide();
        },

        // Update progress bar
        updateProgress: function(percent) {
            percent = Math.round(percent);
            $(this.config.progressBar).css('width', percent + '%');
            $(this.config.progressText).text(percent + '%');
        },

        // Handle upload completion
        handleUploadComplete: function(response) {
            this.hideProgress();

            if (response.success) {
                // Update file statuses
                if (response.files) {
                    var self = this;
                    $.each(response.files, function(index, file) {
                        self.updateFileStatus(file.id, file.status, file.message);
                    });
                }

                // Show success message
                if (typeof appAlert !== 'undefined') {
                    appAlert.success(response.message || 'Files uploaded successfully');
                }

                // Trigger event for other modules
                $(document).trigger('filesUploaded', response);
            } else {
                this.showErrors([response.message || 'Upload failed']);
            }
        },

        // Update file status in list
        updateFileStatus: function(fileId, status, message) {
            var $fileItem = $('.file-item[data-file-id="' + fileId + '"]');
            var $status = $fileItem.find('.file-status');

            if ($status.length) {
                var badgeClass = 'bg-info';
                var statusText = message || status;

                switch(status) {
                    case 'success':
                        badgeClass = 'bg-success';
                        statusText = AppLang.validated || 'Validated';
                        break;
                    case 'error':
                        badgeClass = 'bg-danger';
                        break;
                    case 'warning':
                        badgeClass = 'bg-warning';
                        break;
                }

                $status.html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');
            }
        },

        // Remove file from list
        removeFile: function(fileId) {
            $('.file-item[data-file-id="' + fileId + '"]').fadeOut(300, function() {
                $(this).remove();
            });
            
            // Remove from cache
            delete this.validationCache[fileId];
        },

        // Retry validation for a file
        retryValidation: function(fileId) {
            // Implementation depends on how files are stored
            // This is a placeholder
            this.showErrors([AppLang.retry_not_implemented || 'Retry functionality not implemented']);
        },

        // Helper functions
        generateFileId: function(file) {
            return file.name.replace(/[^a-zA-Z0-9]/g, '_') + '_' + file.size + '_' + Date.now();
        },

        getFileExtension: function(filename) {
            return filename.split('.').pop().toLowerCase();
        },

        isFormatAllowed: function(extension) {
            var allowed = false;
            
            $.each(this.config.allowedFormats, function(category, formats) {
                if (formats.indexOf(extension) !== -1) {
                    allowed = true;
                    return false; // Break the loop
                }
            });
            
            return allowed;
        },

        getAllowedFormatsString: function() {
            var formats = [];
            
            $.each(this.config.allowedFormats, function(category, categoryFormats) {
                formats = formats.concat(categoryFormats);
            });
            
            // Remove duplicates
            formats = formats.filter(function(item, pos) {
                return formats.indexOf(item) === pos;
            });
            
            return formats.join(', ');
        },

        isImageFile: function(file) {
            var imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/svg+xml', 'image/webp'];
            return imageTypes.indexOf(file.type) !== -1;
        },

        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        getFileIcon: function(file) {
            var extension = this.getFileExtension(file.name);
            var iconMap = {
                // Images
                'jpg': 'feather icon-image',
                'jpeg': 'feather icon-image',
                'png': 'feather icon-image',
                'gif': 'feather icon-image',
                'bmp': 'feather icon-image',
                'svg': 'feather icon-image',
                'webp': 'feather icon-image',
                // Documents
                'pdf': 'feather icon-file-text',
                'doc': 'feather icon-file-text',
                'docx': 'feather icon-file-text',
                'xls': 'feather icon-grid',
                'xlsx': 'feather icon-grid',
                'ppt': 'feather icon-monitor',
                'pptx': 'feather icon-monitor',
                // Design
                'ai': 'feather icon-pen-tool',
                'psd': 'feather icon-layers',
                'eps': 'feather icon-pen-tool',
                'indd': 'feather icon-layout',
                'sketch': 'feather icon-pen-tool',
                'fig': 'feather icon-figma',
                // Archives
                'zip': 'feather icon-archive',
                'rar': 'feather icon-archive',
                '7z': 'feather icon-archive',
                'tar': 'feather icon-archive',
                'gz': 'feather icon-archive'
            };
            
            return iconMap[extension] || 'feather icon-file';
        },

        estimateImageDPI: function(width, height, fileSize) {
            // Rough estimation based on file size and dimensions
            // This is not accurate but gives a general idea
            var megapixels = (width * height) / 1000000;
            var compressionRatio = fileSize / (megapixels * 1000000 * 3); // Assuming 3 bytes per pixel uncompressed
            
            // Estimate DPI based on typical print sizes
            var printWidth = 8; // inches
            var estimatedDPI = width / printWidth;
            
            return Math.round(estimatedDPI);
        },

        addImageValidationError: function(fileId, error) {
            var $fileItem = $('.file-item[data-file-id="' + fileId + '"]');
            if ($fileItem.length) {
                this.updateFileStatus(fileId, 'error', error);
            }
            
            // Add to validation cache
            if (this.validationCache[fileId]) {
                this.validationCache[fileId].errors.push(error);
                this.validationCache[fileId].valid = false;
            }
        },

        addImageValidationWarning: function(fileId, warning) {
            var $fileItem = $('.file-item[data-file-id="' + fileId + '"]');
            if ($fileItem.length) {
                // Add warning indicator
                var $fileInfo = $fileItem.find('.file-info');
                if (!$fileInfo.find('.file-warning').length) {
                    $fileInfo.append('<div class="file-warning text-warning small">' +
                        '<i class="feather icon-alert-triangle"></i> ' + warning + '</div>');
                }
            }
            
            // Add to validation cache
            if (this.validationCache[fileId]) {
                this.validationCache[fileId].warnings.push(warning);
            }
        },

        // Show error messages
        showErrors: function(errors) {
            var errorHtml = '<div class="alert alert-danger alert-dismissible">';
            errorHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            errorHtml += '<h5 class="alert-heading"><i class="feather icon-alert-circle"></i> ' +
                        (AppLang.validation_errors || 'Validation Errors') + '</h5>';
            errorHtml += '<ul class="mb-0">';
            
            errors.forEach(function(error) {
                errorHtml += '<li>' + error + '</li>';
            });
            
            errorHtml += '</ul></div>';
            
            $(this.config.errorContainer).html(errorHtml);
        },

        // Clear errors
        clearErrors: function() {
            $(this.config.errorContainer).empty();
        }
    };

    // Initialize if file upload elements exist
    if ($(FileValidation.config.fileInput).length > 0 || $(FileValidation.config.dropzoneElement).length > 0) {
        FileValidation.init();
        
        // Make FileValidation available globally
        window.FileValidation = FileValidation;
    }

    // Integration with Rise CRM's modal forms
    $(document).on('ajaxSuccess', function(event, xhr, settings) {
        // Re-initialize after AJAX content loads
        if (($(FileValidation.config.fileInput).length > 0 || $(FileValidation.config.dropzoneElement).length > 0) &&
            !$(document.body).data('fv-initialized')) {
            FileValidation.init();
            $(document.body).data('fv-initialized', true);
        }
    });

    // Integration with form submission
    $(document).on('submit', 'form', function(e) {
        // Check if there are any validation errors
        var hasErrors = false;
        
        $.each(FileValidation.validationCache, function(fileId, validation) {
            if (!validation.valid) {
                hasErrors = true;
                return false; // Break the loop
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            FileValidation.showErrors([AppLang.fix_file_errors || 'Please fix file validation errors before submitting']);
            return false;
        }
    });
});

// Helper function to get CSRF token (Rise CRM pattern)
function getCsrfToken() {
    return $('[name="csrf_token"]').val() || AppHelper.csrfToken || '';
}