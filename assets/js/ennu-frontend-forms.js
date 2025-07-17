/**
 * ENNU Life Frontend Assessment Forms JavaScript
 * Version: 25.0.0-MODERN
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ennu-assessment-form').forEach(form => {
        new ENNUAssessmentForm(form);
    });
});

class ENNUAssessmentForm {
    constructor(formElement) {
        this.form = formElement;
        this.currentStep = 0;
        this.isSubmitting = false;
        
        // Store reference to this instance on the form element
        this.form.ennuFormInstance = this;
        
        this.questions = this.form.querySelectorAll('.question-slide');
        this.totalSteps = this.questions.length;
        
        const assessmentContainer = this.form.closest('.ennu-assessment');
        this.progressBar = assessmentContainer.querySelector('.ennu-progress-fill');
        this.progressText = assessmentContainer.querySelector('.progress-text .current-question');
        this.totalStepsText = assessmentContainer.querySelector('.progress-text .total-questions');
        
        this.init();
    }

    init() {
        if (this.totalSteps === 0) {
            this.form.innerHTML = '<p class="ennu-error">No questions found for this assessment.</p>';
            return;
        }

        // Update total steps display
        if (this.totalStepsText) {
            this.totalStepsText.textContent = this.totalSteps;
        }
        
        this.showQuestion(0);
        this.bindEvents();
        
        // Check for auto-submission flag
        this.checkAutoSubmissionReady();
    }
    
    checkAutoSubmissionReady() {
        const autoSubmitFlag = this.form.querySelector('input[name="auto_submit_ready"]');
        if (autoSubmitFlag && autoSubmitFlag.value === '1') {
            // User has complete contact info - enable auto submission after last real question
            this.autoSubmitReady = true;
            console.log('ENNU Assessment: Auto-submission enabled for user with complete contact info');
        } else {
            this.autoSubmitReady = false;
        }
    }

    bindEvents() {
        // Next/Previous buttons
        this.form.addEventListener('click', (e) => {
            if (e.target.classList.contains('next')) {
                e.preventDefault();
                if (e.target.classList.contains('submit')) {
                    this.handleSubmitButton();
                } else {
                    this.nextQuestion();
                }
            } else if (e.target.classList.contains('prev')) {
                e.preventDefault();
                this.prevQuestion();
            }
        });

        // Radio button auto-advance (except on contact form)
        this.form.addEventListener('change', (e) => {
            if (e.target.type === 'radio') {
                const currentSlide = this.questions[this.currentStep];
                const isContactForm = currentSlide && currentSlide.hasAttribute('data-is-contact-form');
                const isWelcomeAssessment = this.form.dataset.assessment === 'welcome';
                
                // Don't auto-advance on welcome assessment or contact form
                if (!isContactForm && !isWelcomeAssessment) {
                    setTimeout(() => this.nextQuestion(), 300);
                }
            }
        });

        // DOB calculation
        this.form.addEventListener('change', (e) => {
            if (['dob_day', 'dob_month', 'dob_year'].includes(e.target.name)) {
                this.calculateAge();
            }
        });
    }
    
    handleSubmitButton() {
        const currentSlide = this.questions[this.currentStep];
        const isLastQuestion = this.currentStep >= this.totalSteps - 1;
        const isContactForm = currentSlide && currentSlide.hasAttribute('data-is-contact-form');
        
        // If this is the last question and auto-submit is ready, submit immediately
        if (isLastQuestion && this.autoSubmitReady && !isContactForm) {
            console.log('ENNU Assessment: Auto-submitting after last question (no contact form needed)');
            this.submitForm();
            return;
        }
        
        // If this is a contact form or the last question, submit
        if (isContactForm || isLastQuestion) {
            this.submitForm();
            return;
        }
        
        // Otherwise, go to next question
        this.nextQuestion();
    }

    nextQuestion() {
        if (!this.validateCurrentQuestion()) {
            return;
        }

        const nextStep = this.currentStep + 1;
        
        // Check if we're at the last real question and auto-submit is ready
        if (nextStep >= this.totalSteps && this.autoSubmitReady) {
            console.log('ENNU Assessment: Reached end with auto-submit ready - submitting automatically');
            this.submitForm();
            return;
        }
        
        // Check if next step exists
        if (nextStep < this.totalSteps) {
            this.showQuestion(nextStep);
        } else {
            // Should not happen with proper contact form logic, but safety check
            console.log('ENNU Assessment: Reached end without auto-submit - this should not happen');
            this.submitForm();
        }
    }

    prevQuestion() {
        if (this.currentStep > 0) {
            this.showQuestion(this.currentStep - 1);
        }
    }

    showQuestion(stepIndex) {
        // Hide all questions
        this.questions.forEach(q => q.classList.remove('active'));
        
        // Show current question
        if (this.questions[stepIndex]) {
            this.questions[stepIndex].classList.add('active');
            this.currentStep = stepIndex;
            
            // Update progress
            this.updateProgress();
            
            // Focus first input in the question
            const firstInput = this.questions[stepIndex].querySelector('input, select, textarea');
            if (firstInput && !firstInput.readOnly && !firstInput.disabled) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    updateProgress() {
        const progress = ((this.currentStep + 1) / this.totalSteps) * 100;
        
        if (this.progressBar) {
            this.progressBar.style.width = progress + '%';
        }
        
        if (this.progressText) {
            this.progressText.textContent = this.currentStep + 1;
        }
    }

    validateCurrentQuestion() {
        const currentSlide = this.questions[this.currentStep];
        if (!currentSlide) return true;

        const requiredInputs = currentSlide.querySelectorAll('[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            // Remove previous error styling
            input.classList.remove('error');
            
            if (input.type === 'radio') {
                const radioGroup = currentSlide.querySelectorAll(`input[name="${input.name}"]`);
                const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                if (!isChecked) {
                    radioGroup.forEach(radio => radio.closest('.answer-option')?.classList.add('error'));
                    isValid = false;
                }
            } else if (input.type === 'checkbox') {
                const checkboxGroup = currentSlide.querySelectorAll(`input[name="${input.name}"]`);
                const isChecked = Array.from(checkboxGroup).some(checkbox => checkbox.checked);
                if (!isChecked) {
                    checkboxGroup.forEach(checkbox => checkbox.closest('.answer-option')?.classList.add('error'));
                    isValid = false;
                }
            } else {
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            this.showError(currentSlide, 'Please complete all required fields before continuing.');
        }

        return isValid;
    }

    showError(container, message) {
        // Remove existing error messages
        const existingError = container.querySelector('.ennu-error-message');
        if (existingError) {
            existingError.remove();
        }

        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'ennu-error-message';
        errorDiv.style.cssText = 'color: #d63638; margin-top: 10px; padding: 10px; background: #fcf0f1; border: 1px solid #d63638; border-radius: 4px;';
        errorDiv.textContent = message;
        
        const questionContent = container.querySelector('.question-content');
        if (questionContent) {
            questionContent.appendChild(errorDiv);
        }

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }

    calculateAge() {
        const day = this.form.querySelector('select[name="dob_day"]')?.value;
        const month = this.form.querySelector('select[name="dob_month"]')?.value;
        const year = this.form.querySelector('select[name="dob_year"]')?.value;
        const ageDisplay = this.form.querySelector('.age-display');

        if (day && month && year) {
            const birthDate = new Date(year, month - 1, day);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age >= 0 && ageDisplay) {
                ageDisplay.textContent = `Your age is ${age}`;
                this.combineDOB();
                setTimeout(() => this.nextQuestion(), 400);
            } else if (ageDisplay) {
                ageDisplay.textContent = '';
            }
        }
    }

    combineDOB() {
        const year = this.form.querySelector('select[name="dob_year"]')?.value;
        const month = this.form.querySelector('select[name="dob_month"]')?.value;
        const day = this.form.querySelector('select[name="dob_day"]')?.value;
        const combinedField = this.form.querySelector('input[name="dob_combined"]');

        if (month && day && year && combinedField) {
            const dobString = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            combinedField.value = dobString;
        }
    }
    
    submitForm() {
        if (this.isSubmitting || !this.validateCurrentQuestion()) return;
        
        this.isSubmitting = true;
        this.form.classList.add('loading');
        
        const formData = new FormData(this.form);
        const nonce = this.form.dataset.nonce;

        if (!nonce) {
            alert('Security token missing. Please refresh the page.');
            this.isSubmitting = false;
            this.form.classList.remove('loading');
            return;
        }
        formData.append('nonce', nonce);

        fetch(ennu_ajax.ajax_url, {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success === true) {
                if (data.data && data.data.redirect_url) {
                    console.log('ENNU Assessment: Successful submission, checking for auth state data...');
                    
                    // Check if auth state data is included in the response
                    if (data.data.auth_state) {
                        console.log('ENNU Assessment: Auth state data received in response', data.data.auth_state);
                        // Use the auth state data directly from the response
                        this.updateAllFormsAuthState(data.data.auth_state);
                        
                        // Add a small delay to ensure DOM updates are complete
                        setTimeout(() => {
                            console.log('ENNU Assessment: Redirecting to', data.data.redirect_url);
                            window.location.href = data.data.redirect_url;
                        }, 100);
                    } else {
                        console.log('ENNU Assessment: No auth state in response, making separate AJAX call...');
                        // Fallback to separate AJAX call if auth state not in response
                        this.refreshAuthenticationState().then((authState) => {
                            console.log('ENNU Assessment: Auth state refresh complete', authState);
                            // Add a small delay to ensure DOM updates are complete
                            setTimeout(() => {
                                console.log('ENNU Assessment: Redirecting to', data.data.redirect_url);
                                window.location.href = data.data.redirect_url;
                            }, 100);
                        }).catch((error) => {
                            console.error('ENNU Assessment: Auth state refresh failed, redirecting anyway', error);
                            // Redirect even if auth state refresh fails
                            window.location.href = data.data.redirect_url;
                        });
                    }
                } else {
                    this.showError(this.form, 'Submission successful, but there was an issue redirecting. Please check your dashboard.');
                }
            } else {
                const message = data.data && data.data.message ? data.data.message : 'An unknown error occurred.';
                this.showError(this.form, message);
            }
        })
        .catch(error => {
            console.error('Submission Error:', error);
            this.showError(this.form, 'An unexpected error occurred. Please try again.');
        })
        .finally(() => {
            this.isSubmitting = false;
            this.form.classList.remove('loading');
        });
    }

    /**
     * Refresh authentication state for all assessment forms on the page
     * This handles the case where a user creates an account during submission
     */
    refreshAuthenticationState() {
        console.log('ENNU Assessment: Starting authentication state refresh...');
        
        return fetch(ennu_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'ennu_check_auth_state',
                nonce: ennu_ajax.nonce // Use the global nonce instead of form-specific one
            })
        })
        .then(response => {
            console.log('ENNU Assessment: Auth state response received', response);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('ENNU Assessment: Auth state data received', data);
            if (data && data.success) {
                const authState = data.data;
                console.log('ENNU Assessment: Authentication state refreshed', authState);
                
                // Update all assessment forms on the page with new auth state
                this.updateAllFormsAuthState(authState);
                return authState;
            } else {
                console.warn('ENNU Assessment: Auth state check failed', data);
                return null;
            }
        })
        .catch(error => {
            console.error('ENNU Assessment: Could not refresh auth state', error);
            // Silently fail - this is not critical for the primary submission flow
            return null;
        });
    }

    /**
     * Update all assessment forms on the page with new authentication state
     */
    updateAllFormsAuthState(authState) {
        // Find all assessment forms on the page
        const allForms = document.querySelectorAll('.ennu-assessment form');
        console.log(`ENNU Assessment: Found ${allForms.length} forms to update with auth state`, authState);
        
        allForms.forEach((form, index) => {
            console.log(`ENNU Assessment: Updating form ${index + 1} with auth state`);
            
            // Update auto-submit flag
            let autoSubmitFlag = form.querySelector('input[name="auto_submit_ready"]');
            if (!autoSubmitFlag) {
                autoSubmitFlag = document.createElement('input');
                autoSubmitFlag.type = 'hidden';
                autoSubmitFlag.name = 'auto_submit_ready';
                form.appendChild(autoSubmitFlag);
                console.log(`ENNU Assessment: Created auto_submit_ready flag for form ${index + 1}`);
            }
            const oldValue = autoSubmitFlag.value;
            autoSubmitFlag.value = authState.auto_submit_ready ? '1' : '0';
            console.log(`ENNU Assessment: Updated auto_submit_ready from "${oldValue}" to "${autoSubmitFlag.value}" for form ${index + 1}`);
            
            // Update contact form visibility if needed
            const contactSlide = form.querySelector('.question-slide[data-is-contact-form]');
            if (contactSlide) {
                console.log(`ENNU Assessment: Found contact slide for form ${index + 1}`);
                if (authState.needs_contact_form) {
                    contactSlide.style.display = '';
                    console.log(`ENNU Assessment: Showing contact form for form ${index + 1}`);
                    // Update missing fields in contact form
                    this.updateContactFormFields(contactSlide, authState);
                } else {
                    contactSlide.style.display = 'none';
                    console.log(`ENNU Assessment: Hiding contact form for form ${index + 1}`);
                }
            } else {
                console.log(`ENNU Assessment: No contact slide found for form ${index + 1}`);
            }
            
            // Update form instance auto-submit status if it exists
            if (form.ennuFormInstance) {
                const oldAutoSubmit = form.ennuFormInstance.autoSubmitReady;
                form.ennuFormInstance.autoSubmitReady = authState.auto_submit_ready;
                console.log(`ENNU Assessment: Updated form instance auto-submit from ${oldAutoSubmit} to ${authState.auto_submit_ready} for form ${index + 1}`);
            } else {
                console.log(`ENNU Assessment: No form instance found for form ${index + 1}`);
            }
        });
    }

    /**
     * Update contact form fields with current user data and missing field visibility
     */
    updateContactFormFields(contactSlide, authState) {
        if (!authState.is_logged_in || !authState.user_data) {
            return; // Keep all fields visible for logged out users
        }
        
        const userData = authState.user_data;
        const missingFields = authState.missing_fields || [];
        
        // Update field values and visibility
        const fieldMap = {
            'first_name': userData.first_name || '',
            'last_name': userData.last_name || '',
            'email': userData.email || '',
            'billing_phone': userData.billing_phone || ''
        };
        
        Object.keys(fieldMap).forEach(fieldName => {
            const input = contactSlide.querySelector(`input[name="${fieldName}"]`);
            if (input) {
                input.value = fieldMap[fieldName];
                
                // Hide fields that are complete for logged in users
                const fieldContainer = input.closest('.contact-field');
                if (fieldContainer) {
                    if (missingFields.includes(fieldName) || missingFields.includes(fieldName.replace('billing_', ''))) {
                        fieldContainer.style.display = '';
                    } else {
                        fieldContainer.style.display = 'none';
                    }
                }
            }
        });
    }
}

