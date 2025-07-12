/**
 * ENNU Life Frontend Assessment Forms JavaScript
 * Version: 24.0.0-ENHANCED
 * Matches working v22.8 functionality exactly
 */

(function($) {
    'use strict';

    class ENNUAssessmentForm {
        constructor(formElement) {
            this.form = $(formElement);
            this.currentStep = 1;
            this.totalSteps = 0;
            this.questions = [];
            this.answers = {};
            this.isSubmitting = false;
            
            console.log('ENNU Assessment Form initializing...', this.form);
            this.init();
        }

        init() {
            // Find all question slides
            this.questions = this.form.find('.question-slide');
            this.totalSteps = this.questions.length;
            
            console.log('Total questions found:', this.totalSteps);
            
            if (this.totalSteps === 0) {
                console.error('No questions found! Looking for .question-slide elements');
                return;
            }

            // Update progress display
            this.updateProgress();
            
            // Show first question
            this.showQuestion(1);
            
            // Bind events
            this.bindEvents();
            
            console.log('ENNU Assessment Form initialized successfully');
        }

        bindEvents() {
            // Radio button auto-advance (like v22.8)
            this.form.on('change', 'input[type="radio"]', (e) => {
                const $radio = $(e.target);
                const questionSlide = $radio.closest('.question-slide');
                const questionNumber = parseInt(questionSlide.data('step'));
                
                // Store answer
                this.answers[$radio.attr('name')] = $radio.val();
                
                // Auto-advance after short delay (like v22.8)
                setTimeout(() => {
                    if (questionNumber < this.totalSteps) {
                        this.nextQuestion();
                    }
                }, 300);
            });

            // Navigation buttons
            this.form.on('click', '.nav-button.next', (e) => {
                e.preventDefault();
                this.nextQuestion();
            });

            this.form.on('click', '.nav-button.prev', (e) => {
                e.preventDefault();
                this.prevQuestion();
            });

            // DOB dropdowns
            this.form.on('change', '.dob-dropdown', () => {
                this.calculateAge();
                this.combineDOB();
            });

            // Form submission
            this.form.on('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });

            // Contact form validation
            this.form.on('blur', 'input[type="email"]', (e) => {
                this.validateEmail($(e.target));
            });

            this.form.on('blur', 'input[required]', (e) => {
                this.validateRequired($(e.target));
            });
        }

        showQuestion(questionNumber) {
            console.log(`Showing question ${questionNumber} of ${this.totalSteps}`);
            
            // Hide all questions
            this.questions.removeClass('active').hide();
            
            // Show current question
            const currentQuestion = this.questions.eq(questionNumber - 1);
            currentQuestion.addClass('active').show();
            
            this.currentStep = questionNumber;
            this.updateProgress();
            
            // Only scroll on question transitions, not initial load
            if (questionNumber > 1) {
                this.form[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        nextQuestion() {
            if (this.currentStep < this.totalSteps) {
                // Validate current question before advancing
                if (this.validateCurrentQuestion()) {
                    this.showQuestion(this.currentStep + 1);
                }
            } else {
                // Last question - submit form
                this.submitForm();
            }
        }

        prevQuestion() {
            if (this.currentStep > 1) {
                this.showQuestion(this.currentStep - 1);
            }
        }

        validateCurrentQuestion() {
            const currentQuestion = this.questions.eq(this.currentStep - 1);
            const questionType = currentQuestion.data('question-type');
            
            // Clear previous errors
            currentQuestion.find('.error-message').remove();
            currentQuestion.find('.field-error').removeClass('field-error');
            
            let isValid = true;
            
            if (questionType === 'dob_dropdowns') {
                // Validate DOB dropdowns
                const month = currentQuestion.find('.dob-month').val();
                const day = currentQuestion.find('.dob-day').val();
                const year = currentQuestion.find('.dob-year').val();
                
                if (!month || !day || !year) {
                    this.showError(currentQuestion, 'Please select your complete date of birth.');
                    isValid = false;
                }
            } else if (questionType === 'contact_info') {
                // Validate contact fields
                currentQuestion.find('input[required]').each((index, field) => {
                    const $field = $(field);
                    if (!$field.val().trim()) {
                        $field.addClass('field-error');
                        this.showError($field.parent(), `${$field.prev('label').text()} is required.`);
                        isValid = false;
                    }
                });
                
                // Validate email format
                const emailField = currentQuestion.find('input[type="email"]');
                if (emailField.length && !this.isValidEmail(emailField.val())) {
                    emailField.addClass('field-error');
                    this.showError(emailField.parent(), 'Please enter a valid email address.');
                    isValid = false;
                }
            } else {
                // Validate radio button selection
                const radioName = currentQuestion.find('input[type="radio"]').first().attr('name');
                if (radioName && !currentQuestion.find(`input[name="${radioName}"]:checked`).length) {
                    this.showError(currentQuestion, 'Please select an answer to continue.');
                    isValid = false;
                }
            }
            
            return isValid;
        }

        showError(container, message) {
            const errorDiv = $(`<div class="error-message">${message}</div>`);
            container.append(errorDiv);
        }

        validateEmail(emailField) {
            const email = emailField.val();
            if (email && !this.isValidEmail(email)) {
                emailField.addClass('field-error');
                this.showError(emailField.parent(), 'Please enter a valid email address.');
                return false;
            } else {
                emailField.removeClass('field-error');
                emailField.parent().find('.error-message').remove();
                return true;
            }
        }

        validateRequired(field) {
            const $field = $(field);
            if (!$field.val().trim()) {
                $field.addClass('field-error');
                this.showError($field.parent(), `${$field.prev('label').text()} is required.`);
                return false;
            } else {
                $field.removeClass('field-error');
                $field.parent().find('.error-message').remove();
                return true;
            }
        }

        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        calculateAge() {
            const month = this.form.find('.dob-month').val();
            const day = this.form.find('.dob-day').val();
            const year = this.form.find('.dob-year').val();
            
            if (month && day && year) {
                const birthDate = new Date(year, month - 1, day);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                // Update age display
                this.form.find('.calculated-age').text(age);
                this.form.find('.calculated-age-field').val(age);
                
                return age;
            }
            
            return null;
        }

        combineDOB() {
            const month = this.form.find('.dob-month').val();
            const day = this.form.find('.dob-day').val();
            const year = this.form.find('.dob-year').val();
            
            if (month && day && year) {
                const dobString = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
                this.form.find('.dob-combined').val(dobString);
            }
        }

        updateProgress() {
            // Update progress bar
            const progressPercent = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
            this.form.find('.ennu-progress-fill').css('width', progressPercent + '%');
            
            // Update progress text
            this.form.find('.current-question').text(this.currentStep);
            this.form.find('.total-questions').text(this.totalSteps);
            
            // Update page elements if they exist
            $('#currentStep').text(this.currentStep);
            $('#totalSteps').text(this.totalSteps);
            
            console.log(`Progress updated: ${this.currentStep} of ${this.totalSteps} (${progressPercent}%)`);
        }

        collectFormData() {
            // The FormData constructor correctly handles all input types, including
            // creating an array for checkboxes that share the same name with `[]`.
            const formData = new FormData(this.form[0]);
            
            // Debug: Log all form data to verify
            console.log('Form data collected:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            return formData;
        }

        submitForm() {
            if (this.isSubmitting) {
                return;
            }
            
            // Validate final question
            if (!this.validateCurrentQuestion()) {
                return;
            }
            
            this.isSubmitting = true;
            this.form.addClass('loading');
            
            const formData = this.collectFormData();
            
            // Add AJAX action and nonce
            formData.append('action', 'ennu_submit_assessment');
            
            // The 'ennu_ajax' object is now the single source of truth, localized correctly from the main plugin file.
            if (typeof ennu_ajax !== 'undefined' && ennu_ajax.nonce) {
                formData.append('nonce', ennu_ajax.nonce);
            } else {
                console.error('CRITICAL: Nonce object not found. Aborting submission.');
                this.showSubmissionError('Security token missing. Please refresh the page.');
                this.isSubmitting = false;
                this.form.removeClass('loading');
                return;
            }
            
            const ajaxUrl = (typeof ennu_ajax !== 'undefined' && ennu_ajax.ajax_url) ? ennu_ajax.ajax_url : '/wp-admin/admin-ajax.php';
            
            console.log('Submitting assessment form...');
            console.log('AJAX URL:', ajaxUrl);
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    console.log('AJAX success response received:', response);
                    // Print the backend debug log if it exists
                    if (response && response.success && response.data && response.data.debug_log) {
                        console.group('Backend Execution Log');
                        response.data.debug_log.forEach(log => console.log(log));
                        console.groupEnd();
                    }
                    this.showSuccess(response);
                },
                error: (xhr, status, error) => {
                    console.error('Assessment submission failed:', error);
                    console.error('Response:', xhr.responseText);
                    this.showSubmissionError();
                },
                complete: () => {
                    this.isSubmitting = false;
                    this.form.removeClass('loading');
                }
            });
        }

        showSuccess(response) {
            // Check if the response is valid and contains a redirect URL
            if (response && response.success && response.data && response.data.redirect_url) {
                // Redirect the user to the specified "Thank You" page
                window.location.href = response.data.redirect_url;
            } else {
                // Fallback for unexpected responses: just show a generic success message
                console.warn('Success response received, but no redirect URL found. Showing generic message.');
                // Hide form
                this.form.find('.questions-container').hide();
                
                // Show success message
                const successMessage = this.form.find('.assessment-success');
                if (successMessage.length) {
                    successMessage.show();
                } else {
                    // Create success message if it doesn't exist
                    const successHtml = `
                        <div class="assessment-success">
                            <div class="success-icon">✓</div>
                            <h2>Assessment Complete!</h2>
                            <p>Thank you for completing your assessment. Your personalized results and recommendations will be sent to your email shortly.</p>
                        </div>
                    `;
                    this.form.append(successHtml);
                }
                
                // Scroll to success message
                this.form.find('.assessment-success')[0].scrollIntoView({ behavior: 'smooth' });
            }
        }

        showSubmissionError(message = 'There was a problem submitting your assessment. Please try again.') {
            const errorHtml = `
                <div class="validation-message error">
                    <strong>Submission Error:</strong> ${message}
                </div>
            `;
            
            const currentQuestion = this.questions.eq(this.currentStep - 1);
            currentQuestion.find('.validation-message').remove();
            currentQuestion.append(errorHtml);
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        console.log('ENNU Assessment Forms: DOM ready, initializing...');
        
        // Initialize all assessment forms
        $('.assessment-form').each(function() {
            new ENNUAssessmentForm(this);
        });
        
        // Debug: Log total questions found on page
        const totalQuestions = $('.question-slide').length;
        console.log(`Total question slides found on page: ${totalQuestions}`);
        
        if (totalQuestions === 0) {
            console.warn('No .question-slide elements found. Check HTML structure.');
        }
    });

})(jQuery);

