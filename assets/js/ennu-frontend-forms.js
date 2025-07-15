/**
 * ENNU Life Frontend Assessment Forms JavaScript
 * Version: 24.0.0-ENHANCED
 * Matches working v22.8 functionality exactly
 */
console.log('ENNU Life Frontend Forms script loaded.');
(function($) {
    'use strict';

    class ENNUAssessmentForm {
        constructor(formElement) {
            this.form = $(formElement);
            this.currentStep = 0; // Start at 0, so first question is 1
            this.totalSteps = 0;
            this.isSubmitting = false;

            // --- DEFINITIVE FIX ---
            // Properties are now jQuery objects, found within the specific form instance.
            this.questions = this.form.find('.question-slide');
            this.progressBar = this.form.closest('.ennu-assessment').find('.ennu-progress-fill');
            this.progressText = this.form.closest('.ennu-assessment').find('.progress-text .current-question');
            this.totalStepsText = this.form.closest('.ennu-assessment').find('.progress-text .total-questions');
            
            this.totalSteps = this.questions.length;

            this.init();
        }

        init() {
            if (this.totalSteps === 0) {
                this.form.html('<p class="ennu-error">No questions found for this assessment.</p>');
                return;
            }

            this.totalStepsText.text(this.totalSteps);
            this.showQuestion(1); // Show the first question
            this.calculateAge(); // Calculate age on initial load
            this.bindEvents();
        }

        bindEvents() {
            // Use .on() for delegated events on the form
            this.form.on('change', 'input[type="radio"]', (e) => {
                setTimeout(() => this.nextQuestion(), 300);
            });

            this.form.on('click', '.nav-button.next', (e) => {
                e.preventDefault();
                this.nextQuestion();
            });

            this.form.on('click', '.nav-button.prev', (e) => {
                e.preventDefault();
                this.prevQuestion();
            });

            this.form.on('change', 'select[name^="dob_"]', () => {
                this.calculateAge();
            });

            this.form.on('blur', 'input[name="email"]', (e) => {
                this.checkEmailExists(e.target);
            });

            this.form.on('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });

            // --- New: Symptom Qualification Engine Logic ---
            this.form.find('.question-slide[data-question-type="multiselect"]').on('change', 'input[type="checkbox"]', (e) => {
                const $checkbox = $(e.currentTarget);
                const symptomValue = $checkbox.val();
                const $slide = $checkbox.closest('.question-slide');
                const $qualifiersContainer = $slide.find('.qualifiers-container');
                const $qualifierGroup = $qualifiersContainer.find(`.qualifier-group[data-qualifier-for="${symptomValue}"]`);

                if ($checkbox.is(':checked')) {
                    $qualifiersContainer.slideDown();
                    $qualifierGroup.slideDown();
                } else {
                    $qualifierGroup.slideUp();
                    // If no other qualifiers are visible, hide the whole container
                    if ($qualifiersContainer.find('.qualifier-group:visible').length === 1) {
                        $qualifiersContainer.slideUp();
                    }
                }
            });
        }

        showQuestion(step, direction = 'next') {
            if (step < 1 || step > this.totalSteps) {
                return;
            }
            this.currentStep = step;
            const currentSlide = this.questions.eq(step - 1);
            
            this.questions.removeClass('active').hide();
            currentSlide.addClass('active').show();
            this.updateProgressBar();

            // --- AUTO-SKIP LOGIC ---
            // Only run the auto-skip if navigating forward
            if (direction !== 'prev' && currentSlide.data('is-global')) {
                const inputs = currentSlide.find('input[required], select[required]');
                let allPrefilled = true;
                if (inputs.length > 0) {
                    inputs.each((i, input) => {
                        const $input = $(input);
                        if ($input.is(':radio') || $input.is(':checkbox')) {
                            if (currentSlide.find(`input[name="${$input.attr('name')}"]:checked`).length === 0) {
                                allPrefilled = false;
                            }
                        } else {
                            if (!$input.val() || $input.val().trim() === '') {
                                allPrefilled = false;
                            }
                        }
                    });

                    if (allPrefilled) {
                        setTimeout(() => {
                            this.nextQuestion();
                        }, 500); // A brief delay to show the pre-filled data
                    }
                }
            }
        }

        nextQuestion() {
            if (this.validateCurrentQuestion()) {
                if (this.currentStep < this.totalSteps) {
                    this.showQuestion(this.currentStep + 1);
                } else {
                    this.submitForm();
                }
            }
        }

        prevQuestion() {
            if (this.currentStep > 1) {
                this.showQuestion(this.currentStep - 1, 'prev');
            }
        }

        updateProgressBar() {
            if (this.progressBar.length) {
                const progress = (this.currentStep / this.totalSteps) * 100;
                this.progressBar.css('width', `${progress}%`);
            }
            if (this.progressText.length) {
                this.progressText.text(this.currentStep);
            }
        }

        validateCurrentQuestion() {
            const currentSlide = this.questions.eq(this.currentStep - 1);
            currentSlide.find('.error-message').remove();
            let allValid = true;
        
            const uniqueRequiredFields = {};
        
            // Find all visible, required fields and group them by name
            currentSlide.find(':input[required]:visible').each(function() {
                if (this.name) {
                    uniqueRequiredFields[this.name] = $(this);
                }
            });
        
            for (const name in uniqueRequiredFields) {
                const $input = uniqueRequiredFields[name];
                let isGroupValid = true;
        
                if ($input.is(':radio') || $input.is(':checkbox')) {
                    if (currentSlide.find(`input[name="${name}"]:checked`).length === 0) {
                        isGroupValid = false;
                    }
                } else {
                    if (!$input.val() || $input.val().trim() === '') {
                        isGroupValid = false;
                    }
                }
        
                if (!isGroupValid) {
                    allValid = false;
                    const $container = $input.closest('.answer-options, .contact-field, .hw-field, .dob-dropdowns');
                    this.showError($container, 'Please complete all required fields to continue.');
                }
            }
        
            return allValid;
        }
        
        showError(element, message) {
            if (element.next('.error-message').length === 0) {
                element.after(`<div class="error-message" style="color: #ef4444; font-size: 0.875rem; text-align: left; margin-top: 0.5rem;">${message}</div>`);
            }
        }

        checkEmailExists(emailField) {
            const $emailField = $(emailField);
            const email = $emailField.val();
            const $fieldContainer = $emailField.parent();

            // Clear previous messages
            $fieldContainer.find('.email-check-message').remove();

            if (!email || !this.validateCurrentQuestion()) {
                return;
            }

            $.post(ennu_ajax.ajax_url, {
                action: 'ennu_check_email',
                email: email,
                nonce: ennu_ajax.nonce
            })
            .done((response) => {
                if (response.success && response.data.exists) {
                    const messageDiv = `<div class="email-check-message notice notice-warning">${response.data.message}</div>`;
                    $fieldContainer.append(messageDiv);
                }
            });
        }

        calculateAge() {
            const month = parseInt(this.form.find('select[name="dob_month"]').val(), 10);
            const day = parseInt(this.form.find('select[name="dob_day"]').val(), 10);
            const year = parseInt(this.form.find('select[name="dob_year"]').val(), 10);
            const ageDisplay = this.form.find('.calculated-age-display');

            if (month && day && year) {
                const today = new Date();
                const birthDate = new Date(year, month - 1, day);
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                if (age >= 0) {
                    ageDisplay.text(`Your age is ${age}`);
                    this.combineDOB();
                    // Auto-advance after a short delay
                    setTimeout(() => this.nextQuestion(), 400);
                } else {
                    ageDisplay.text('');
                }
            }
        }

        combineDOB() {
            const year = this.form.find('select[name="dob_year"]').val();
            const month = this.form.find('select[name="dob_month"]').val();
            const day = this.form.find('select[name="dob_day"]').val();

            if (month && day && year) {
                const monthPadded = month.toString().padStart(2, '0');
                const dayPadded = day.toString().padStart(2, '0');
                const dobString = `${year}-${monthPadded}-${dayPadded}`;
                this.form.find('input[name="dob_combined"]').val(dobString);
            }
        }
        
        collectFormData() {
            return new FormData(this.form[0]);
        }

        submitForm() {
            if (this.isSubmitting || !this.validateCurrentQuestion()) {
                return;
            }
            this.isSubmitting = true;
            this.form.addClass('loading');
            
            const formData = this.collectFormData();
            const nonce = this.form.data('nonce'); // Get nonce directly from the form's data attribute

            if (!nonce) {
                alert('Security token missing. Please refresh the page.');
                this.isSubmitting = false;
                this.form.removeClass('loading');
                return;
            }
            formData.append('nonce', nonce);

            $.ajax({
                url: ennu_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json', // Ensure jQuery parses the response as JSON
                success: (response) => {
                    // Definitive Fix: The server sends back a JSON object.
                    // We must check the 'success' property within that object.
                    if (response && response.success === true) {
                        if (response.data && response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            // Fallback if redirect URL is missing but was successful
                            this.showError(this.form, 'Submission successful, but there was an issue redirecting. Please check your dashboard.');
                        }
                    } else {
                        // Handle cases where the server returns a success:false response
                        const message = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                        this.showError(this.form, message);
                    }
                },
                error: (xhr) => {
                    let errorMessage = 'An unexpected error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        // Try to parse as HTML and find a relevant error message
                        try {
                            const errorHtml = $(xhr.responseText);
                            const errorText = errorHtml.find('p').first().text();
                            if(errorText) errorMessage = errorText;
                        } catch(e) {
                             // Fallback if responseText is not HTML
                             errorMessage = 'An error occurred. Check the console for details.';
                             console.error(xhr.responseText);
                        }
                    }
                     this.showError(this.form, errorMessage);
                },
                complete: () => {
                    this.isSubmitting = false;
                    this.form.removeClass('loading');
                }
            });
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        $('.ennu-assessment-form').each(function() {
            new ENNUAssessmentForm(this);
        });
    });

})(jQuery);

