/**
 * ENNU Contact Form JavaScript
 * Handles form submission, validation, and modal integration
 * 
 * @package ENNU_Life
 * @version 72.0.0
 */

(function($) {
    'use strict';

    // Contact Form Handler
    const ENNUContactForm = {
        
        /**
         * Initialize all contact forms on the page
         */
        init: function() {
            $('.ennu-contact-form').each(function() {
                ENNUContactForm.initializeForm($(this));
            });
        },

        /**
         * Initialize a single form
         */
        initializeForm: function($form) {
            const formId = $form.attr('id');
            
            // IMPORTANT: Never auto-submit, even if fields are pre-filled
            // User must always click the submit button
            
            // Phone number formatting
            $form.find('input[type="tel"]').on('input', function() {
                ENNUContactForm.formatPhoneNumber($(this));
            });

            // Real-time validation
            $form.find('input[required]').on('blur', function() {
                if ($(this).attr('type') !== 'checkbox') {
                    ENNUContactForm.validateField($(this));
                }
            });

            // Compliance checkbox validation
            $form.find('.compliance-checkbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $(this).closest('.form-group').removeClass('error');
                    $(this).closest('.form-group').find('.error-message').hide();
                }
            });

            // Form submission - only on user action
            $form.on('submit', function(e) {
                e.preventDefault();
                ENNUContactForm.submitForm($form);
            });

            // Update progress bar on input
            $form.find('input').on('input', function() {
                ENNUContactForm.updateProgress($form);
            });
            
            // Update initial progress if fields are pre-filled
            ENNUContactForm.updateProgress($form);
        },

        /**
         * Format phone number as user types
         */
        formatPhoneNumber: function($input) {
            let value = $input.val().replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = `(${value}`;
                } else if (value.length <= 6) {
                    value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
                } else {
                    value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                }
            }
            $input.val(value);
        },

        /**
         * Validate a single field
         */
        validateField: function($field) {
            const fieldType = $field.attr('type');
            const fieldValue = $field.val().trim();
            let isValid = true;
            let errorMessage = '';

            // Remove previous error state
            $field.removeClass('error');
            $field.siblings('.error-message').hide();

            if (!fieldValue) {
                isValid = false;
                errorMessage = 'This field is required';
            } else {
                switch(fieldType) {
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(fieldValue)) {
                            isValid = false;
                            errorMessage = 'Please enter a valid email address';
                        }
                        break;
                    case 'tel':
                        const phoneRegex = /^\(\d{3}\)\s\d{3}-\d{4}$/;
                        if (!phoneRegex.test(fieldValue)) {
                            isValid = false;
                            errorMessage = 'Please enter a valid phone number';
                        }
                        break;
                }
            }

            if (!isValid) {
                $field.addClass('error');
                $field.siblings('.error-message').text(errorMessage).show();
            }

            return isValid;
        },

        /**
         * Update progress bar based on filled fields
         */
        updateProgress: function($form) {
            const totalFields = $form.find('input[required]').length;
            let filledFields = 0;

            $form.find('input[required]').each(function() {
                if ($(this).val().trim()) {
                    filledFields++;
                }
            });

            const progress = (filledFields / totalFields) * 100;
            $form.find('.progress-fill').css('width', progress + '%');
        },

        /**
         * Submit the form
         */
        submitForm: function($form) {
            // Soft validation - show errors but don't block submission
            $form.find('input[required]').each(function() {
                if ($(this).attr('type') === 'checkbox') {
                    // Special handling for checkboxes
                    if (!$(this).is(':checked')) {
                        $(this).closest('.form-group').addClass('error');
                        $(this).closest('.form-group').find('.error-message').show();
                    } else {
                        $(this).closest('.form-group').removeClass('error');
                        $(this).closest('.form-group').find('.error-message').hide();
                    }
                } else {
                    ENNUContactForm.validateField($(this));
                }
            });
            
            // Continue with submission regardless of validation

            // Disable submit button
            const $submitBtn = $form.find('.btn-submit');
            const originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('Processing...');

            // Prepare form data
            const formData = new FormData($form[0]);
            
            // Explicitly handle compliance checkbox (unchecked checkboxes aren't sent)
            const $complianceCheckbox = $form.find('.compliance-checkbox');
            if ($complianceCheckbox.length > 0) {
                if ($complianceCheckbox.is(':checked')) {
                    formData.set('compliance_agreement', 'on');
                } else {
                    formData.delete('compliance_agreement');
                }
            }
            
            // Add AJAX action and nonce
            formData.append('action', 'ennu_submit_contact_form');
            formData.append('nonce', ennu_contact.nonce);
            
            // Add CSRF cookie if it exists
            const csrfCookie = document.cookie.split('; ').find(row => row.startsWith('ennu_csrf_token='));
            if (csrfCookie) {
                formData.append('csrf_token', csrfCookie.split('=')[1]);
            }

            // Send AJAX request
            $.ajax({
                url: ennu_contact.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        ENNUContactForm.handleSuccess(response.data, $form);
                    } else {
                        ENNUContactForm.handleError(response.data, $form);
                    }
                },
                error: function(xhr, status, error) {
                    // Check if we got a valid JSON response despite the error status
                    if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.success) {
                                ENNUContactForm.handleSuccess(response.data, $form);
                                return;
                            } else if (response && response.data) {
                                ENNUContactForm.handleError(response.data, $form);
                                return;
                            }
                        } catch (e) {
                            // Not JSON, continue to error handling
                        }
                    }
                    
                    ENNUContactForm.handleError({
                        message: 'An error occurred. Please try again.',
                        type: 'network_error'
                    }, $form);
                },
                complete: function() {
                    // Re-enable submit button
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            });
        },

        /**
         * Handle successful form submission
         */
        handleSuccess: function(data, $form) {
            const createAccount = $form.data('create-account') === 'yes';
            const redirectUrl = data.redirect || $form.data('redirect');

            // Show progress modal if indicated (for new accounts only)
            if (data.show_progress_modal && !data.is_logged_in) {
                ENNUContactForm.showProgressModal(data.modal_type, createAccount, function() {
                    // After modal completes, redirect if URL provided
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        // Show success message
                        ENNUContactForm.showSuccessMessage($form, data.message);
                    }
                });
            } else {
                // Direct redirect or success message
                if (redirectUrl) {
                    // Force redirect with a small delay to ensure form data is processed
                    setTimeout(function() {
                        window.location.href = redirectUrl;
                    }, 100);
                } else {
                    ENNUContactForm.showSuccessMessage($form, data.message);
                }
            }
        },

        /**
         * Handle form submission errors
         */
        handleError: function(data, $form) {
            // Always redirect if URL is provided, even on error
            const redirectUrl = $form.data('redirect');
            if (redirectUrl) {
                // Still redirect even if there was an error
                setTimeout(function() {
                    window.location.href = redirectUrl;
                }, 100);
            } else {
                // Only show error if no redirect URL
                if (data.type === 'user_exists' && data.show_login) {
                    ENNUContactForm.showLoginPrompt(data.email, $form);
                } else {
                    ENNUContactForm.showErrorMessage($form, data.message);
                }
            }
        },

        /**
         * Show progress modal (reusing existing modal from frontend forms)
         */
        showProgressModal: function(modalType, createAccount, callback) {
            const modal = document.createElement('div');
            modal.className = 'ennu-progress-modal';
            
            // Determine modal content based on type
            let title, subtitle, steps;
            
            if (modalType === 'account_creation') {
                title = 'Creating Your Account';
                subtitle = 'Please wait while we set up your personalized experience';
                steps = [
                    { title: 'Creating Account', description: 'Setting up your profile' },
                    { title: 'Saving Information', description: 'Storing your contact details' },
                    { title: 'Preparing Dashboard', description: 'Customizing your experience' },
                    { title: 'Finalizing', description: 'Almost ready!' }
                ];
            } else {
                title = 'Processing Your Information';
                subtitle = 'Please wait while we save your details';
                steps = [
                    { title: 'Validating Data', description: 'Checking your information' },
                    { title: 'Saving Details', description: 'Storing your contact info' },
                    { title: 'Preparing Next Step', description: 'Getting things ready' },
                    { title: 'Complete', description: 'All done!' }
                ];
            }

            modal.innerHTML = `
                <div class="ennu-progress-overlay"></div>
                <div class="ennu-progress-content">
                    <div class="progress-logo">
                        <img src="${ennu_contact.plugin_url}/assets/img/ennu-logo-black.png" alt="ENNU Life" />
                    </div>
                    <div class="progress-header">
                        <h2>${title}</h2>
                        <p class="progress-subtitle">${subtitle}</p>
                    </div>
                    <div class="progress-steps">
                        ${steps.map((step, index) => `
                            <div class="progress-step ${index === 0 ? 'active' : ''}" data-step="${index + 1}">
                                <div class="step-icon">
                                    <svg class="step-spinner" viewBox="0 0 24 24" style="${index > 0 ? 'display:none;' : ''}">
                                        <circle class="path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle>
                                    </svg>
                                    <svg class="step-check" viewBox="0 0 24 24" style="display:none;">
                                        <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="step-text">
                                    <h3>${step.title}</h3>
                                    <p>${step.description}</p>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="progress-footer">
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: 25%;"></div>
                        </div>
                        <p class="progress-message">${steps[0].description}...</p>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            
            // Activate modal with slight delay for animation
            setTimeout(() => {
                modal.classList.add('active');
            }, 100);

            // Simulate progress through steps
            let currentStep = 1;
            const stepDuration = 2500; // 2.5 seconds per step

            const progressInterval = setInterval(() => {
                currentStep++;
                
                if (currentStep > steps.length) {
                    clearInterval(progressInterval);
                    
                    // Remove modal and execute callback
                    setTimeout(() => {
                        modal.classList.remove('active');
                        setTimeout(() => {
                            modal.remove();
                            if (callback) callback();
                        }, 800);
                    }, 500);
                } else {
                    ENNUContactForm.updateModalStep(modal, currentStep, steps);
                }
            }, stepDuration);
        },

        /**
         * Update modal step
         */
        updateModalStep: function(modal, step, steps) {
            const modalSteps = modal.querySelectorAll('.progress-step');
            const progressBar = modal.querySelector('.progress-bar-fill');
            const progressMessage = modal.querySelector('.progress-message');

            modalSteps.forEach((stepEl, index) => {
                const stepNum = index + 1;
                
                if (stepNum < step) {
                    // Completed step
                    stepEl.classList.remove('active');
                    stepEl.classList.add('completed');
                    stepEl.querySelector('.step-spinner').style.display = 'none';
                    stepEl.querySelector('.step-check').style.display = 'block';
                } else if (stepNum === step) {
                    // Current step
                    stepEl.classList.add('active');
                    stepEl.classList.remove('completed');
                    stepEl.querySelector('.step-spinner').style.display = 'block';
                    stepEl.querySelector('.step-check').style.display = 'none';
                } else {
                    // Future step
                    stepEl.classList.remove('active', 'completed');
                    stepEl.querySelector('.step-spinner').style.display = 'none';
                    stepEl.querySelector('.step-check').style.display = 'none';
                }
            });

            // Update progress bar
            const progressPercent = (step / steps.length) * 100;
            progressBar.style.width = progressPercent + '%';

            // Update message
            if (step <= steps.length) {
                progressMessage.textContent = steps[step - 1].description + '...';
            }
        },

        /**
         * Show login prompt for existing users
         */
        showLoginPrompt: function(email, $form) {
            const promptHtml = `
                <div class="ennu-login-prompt">
                    <div class="prompt-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="#ffc107" stroke-width="2"/>
                            <path d="M12 8v4m0 4h.01" stroke="#ffc107" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>Account Already Exists</h3>
                    <p>An account with <strong>${email}</strong> already exists.</p>
                    <p>Please log in to continue.</p>
                    <div class="prompt-actions">
                        <a href="/login?redirect_to=${encodeURIComponent(window.location.href)}" class="btn btn-primary">Log In</a>
                        <button type="button" class="btn btn-secondary" onclick="location.reload()">Try Different Email</button>
                    </div>
                </div>
            `;

            // Replace form with login prompt
            $form.parent().html(promptHtml);
        },

        /**
         * Show success message
         */
        showSuccessMessage: function($form, message) {
            const successHtml = `
                <div class="ennu-success-message">
                    <div class="success-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="#28a745" stroke-width="2"/>
                            <path d="M8 12l2 2 4-4" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3>Success!</h3>
                    <p>${message}</p>
                </div>
            `;

            // Replace form with success message
            $form.parent().html(successHtml);
        },

        /**
         * Show error message
         */
        showErrorMessage: function($form, message) {
            // Create or update error container
            let $errorContainer = $form.find('.form-error-message');
            if (!$errorContainer.length) {
                $errorContainer = $('<div class="form-error-message"></div>');
                $form.find('.form-header').after($errorContainer);
            }

            $errorContainer.html(`
                <div class="alert alert-danger">
                    <strong>Error:</strong> ${message}
                </div>
            `).show();

            // Auto-hide after 5 seconds
            setTimeout(() => {
                $errorContainer.fadeOut();
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ENNUContactForm.init();
    });

})(jQuery);