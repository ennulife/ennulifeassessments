/**
 * ENNU Assessment Form JavaScript
 * Handles form submission, validation, and user interactions
 * 
 * @package ENNU_Life
 * @version 64.55.0
 */

(function($) {
    'use strict';

    window.ENNUAssessmentForm = {
        // Configuration
        config: {
            ajaxUrl: ennuAssessmentData ? ennuAssessmentData.ajax_url : '/wp-admin/admin-ajax.php',
            nonce: ennuAssessmentData ? ennuAssessmentData.nonce : '',
            debug: true
        },

        // Initialize the form
        init: function() {
            this.bindEvents();
            this.initializeProgressBar();
            this.loadSavedData();
            this.log('Assessment form initialized');
        },

        // Bind form events
        bindEvents: function() {
            var self = this;

            // Form submission
            $(document).on('submit', '.ennu-assessment-form', function(e) {
                e.preventDefault();
                self.submitForm($(this));
            });

            // Auto-save on field change
            $(document).on('change', '.ennu-assessment-form input, .ennu-assessment-form select, .ennu-assessment-form textarea', function() {
                self.autoSave();
            });

            // Height/Weight special handling
            $(document).on('change', '.height-feet, .height-inches, .weight-pounds', function() {
                self.updateHeightWeight($(this));
            });

            // Date of birth handling
            $(document).on('change', '.dob-month, .dob-day, .dob-year', function() {
                self.updateDateOfBirth($(this));
            });

            // Progress tracking
            $(document).on('change', '.ennu-assessment-form input[required], .ennu-assessment-form select[required]', function() {
                self.updateProgress();
            });

            // Multi-select checkbox handling
            $(document).on('change', '.ennu-assessment-form input[type="checkbox"]', function() {
                self.handleMultiSelect($(this));
            });
        },

        // Submit the form
        submitForm: function($form) {
            var self = this;
            
            // Validate form
            if (!this.validateForm($form)) {
                return false;
            }

            // Show loading state
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.text();
            $submitBtn.prop('disabled', true).text('Processing...');

            // Prepare form data
            var formData = new FormData($form[0]);
            formData.append('action', 'ennu_submit_assessment');
            formData.append('nonce', this.config.nonce);
            formData.append('assessment_type', $form.data('assessment-type'));

            // Submit via AJAX
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    self.log('Form submitted successfully', response);
                    
                    if (response.success) {
                        // Clear saved data
                        self.clearSavedData();
                        
                        // Redirect to results page
                        if (response.data && response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            // No redirect URL provided - show success and redirect to dashboard
                            self.showMessage('Assessment completed successfully! Redirecting to dashboard...', 'success');
                            // Redirect to dashboard as fallback (not hardcoded results page)
                            setTimeout(function() {
                                window.location.href = '/?page_id=3732'; // Dashboard page as safe fallback
                            }, 2000);
                        }
                    } else {
                        self.showMessage(response.data.message || 'An error occurred. Please try again.', 'error');
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    self.log('Form submission error', error);
                    self.showMessage('An error occurred. Please try again.', 'error');
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        // Validate form
        validateForm: function($form) {
            var isValid = true;
            var self = this;

            // Check required fields
            $form.find('[required]').each(function() {
                var $field = $(this);
                var value = $field.val();

                if (!value || value.trim() === '') {
                    self.showFieldError($field, 'This field is required');
                    isValid = false;
                } else {
                    self.clearFieldError($field);
                }
            });

            // Validate email
            $form.find('input[type="email"]').each(function() {
                var $field = $(this);
                var email = $field.val();
                if (email && !self.isValidEmail(email)) {
                    self.showFieldError($field, 'Please enter a valid email address');
                    isValid = false;
                }
            });

            // Validate phone
            $form.find('input[type="tel"]').each(function() {
                var $field = $(this);
                var phone = $field.val();
                if (phone && !self.isValidPhone(phone)) {
                    self.showFieldError($field, 'Please enter a valid phone number');
                    isValid = false;
                }
            });

            return isValid;
        },

        // Auto-save form data
        autoSave: function() {
            var formData = {};
            $('.ennu-assessment-form').find('input, select, textarea').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                if (name) {
                    if ($field.attr('type') === 'checkbox') {
                        if (!formData[name]) {
                            formData[name] = [];
                        }
                        if ($field.is(':checked')) {
                            formData[name].push($field.val());
                        }
                    } else if ($field.attr('type') === 'radio') {
                        if ($field.is(':checked')) {
                            formData[name] = $field.val();
                        }
                    } else {
                        formData[name] = $field.val();
                    }
                }
            });

            // Save to localStorage
            localStorage.setItem('ennu_assessment_data', JSON.stringify(formData));
            this.log('Form data auto-saved');
        },

        // Load saved data
        loadSavedData: function() {
            var savedData = localStorage.getItem('ennu_assessment_data');
            if (savedData) {
                try {
                    var data = JSON.parse(savedData);
                    var self = this;
                    
                    $.each(data, function(name, value) {
                        if (Array.isArray(value)) {
                            // Handle checkboxes
                            $.each(value, function(i, val) {
                                $('input[name="' + name + '"][value="' + val + '"]').prop('checked', true);
                            });
                        } else {
                            // Handle other inputs
                            var $field = $('[name="' + name + '"]');
                            if ($field.attr('type') === 'radio') {
                                $field.filter('[value="' + value + '"]').prop('checked', true);
                            } else {
                                $field.val(value);
                            }
                        }
                    });
                    
                    this.log('Saved form data loaded');
                    this.updateProgress();
                } catch (e) {
                    this.log('Error loading saved data', e);
                }
            }
        },

        // Clear saved data
        clearSavedData: function() {
            localStorage.removeItem('ennu_assessment_data');
            this.log('Saved data cleared');
        },

        // Update height/weight combined field
        updateHeightWeight: function($field) {
            var $container = $field.closest('.height-weight-inputs');
            var feet = $container.find('.height-feet').val() || 0;
            var inches = $container.find('.height-inches').val() || 0;
            var weight = $container.find('.weight-pounds').val() || 0;

            var combined = feet + 'ft ' + inches + 'in|' + weight + 'lbs';
            $container.find('input[type="hidden"]').val(combined);
            
            this.log('Height/Weight updated: ' + combined);
        },

        // Update date of birth combined field
        updateDateOfBirth: function($field) {
            var $container = $field.closest('.dob-dropdowns');
            var month = $container.find('.dob-month').val();
            var day = $container.find('.dob-day').val();
            var year = $container.find('.dob-year').val();

            if (month && day && year) {
                var combined = year + '-' + month.padStart(2, '0') + '-' + day.padStart(2, '0');
                $container.find('input[type="hidden"]').val(combined);
                this.log('DOB updated: ' + combined);
            }
        },

        // Handle multi-select checkboxes
        handleMultiSelect: function($checkbox) {
            var name = $checkbox.attr('name');
            var value = $checkbox.val();

            // Handle "none" option
            if (value === 'none' && $checkbox.is(':checked')) {
                // Uncheck all other options
                $('input[name="' + name + '"]').not('[value="none"]').prop('checked', false);
            } else if ($checkbox.is(':checked')) {
                // Uncheck "none" if other option is selected
                $('input[name="' + name + '"][value="none"]').prop('checked', false);
            }
        },

        // Initialize progress bar
        initializeProgressBar: function() {
            var $progressBar = $('.ennu-progress-bar');
            if ($progressBar.length) {
                this.updateProgress();
            }
        },

        // Update progress bar
        updateProgress: function() {
            var $form = $('.ennu-assessment-form');
            var totalRequired = $form.find('[required]').length;
            var filledRequired = 0;

            $form.find('[required]').each(function() {
                var $field = $(this);
                if ($field.attr('type') === 'radio') {
                    if ($('input[name="' + $field.attr('name') + '"]:checked').length > 0) {
                        filledRequired++;
                    }
                } else if ($field.val() && $field.val().trim() !== '') {
                    filledRequired++;
                }
            });

            var progress = totalRequired > 0 ? Math.round((filledRequired / totalRequired) * 100) : 0;
            
            $('.ennu-progress-bar-fill').css('width', progress + '%');
            $('.ennu-progress-text').text(progress + '% Complete');
            
            this.log('Progress updated: ' + progress + '%');
        },

        // Show field error
        showFieldError: function($field, message) {
            var $container = $field.closest('.ennu-field-group, .form-group');
            $container.addClass('has-error');
            
            var $error = $container.find('.field-error');
            if (!$error.length) {
                $error = $('<span class="field-error"></span>');
                $container.append($error);
            }
            $error.text(message).show();
        },

        // Clear field error
        clearFieldError: function($field) {
            var $container = $field.closest('.ennu-field-group, .form-group');
            $container.removeClass('has-error');
            $container.find('.field-error').hide();
        },

        // Show message
        showMessage: function(message, type) {
            var $message = $('<div class="ennu-message ennu-message-' + type + '">' + message + '</div>');
            $('.ennu-assessment-form').prepend($message);
            
            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        // Email validation
        isValidEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        // Phone validation
        isValidPhone: function(phone) {
            var cleaned = phone.replace(/\D/g, '');
            return cleaned.length >= 10;
        },

        // Debug logging
        log: function() {
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.ennu-assessment-form').length) {
            ENNUAssessmentForm.init();
        }
    });

})(jQuery);