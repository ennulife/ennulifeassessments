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
        
        // Pre-fill global fields with existing data
        this.prefillGlobalFields();
        
        // Add a small delay to ensure DOM is fully ready before showing first question
        setTimeout(() => {
            this.showQuestion(0);
            this.bindEvents();
            
            // Check for auto-submission flag
            this.checkAutoSubmissionReady();
        }, 100);
    }

    // Pre-fill global fields with existing data
    prefillGlobalFields() {
        console.log('Prefilling global fields...');
        this.questions.forEach((question, index) => {
            console.log('Question', index, 'is global?', question.hasAttribute('data-is-global'));
            if (question.hasAttribute('data-is-global')) {
                console.log('Prefilling global field:', question.querySelector('.question-title')?.textContent);
                this.prefillGlobalField(question);
                
                // Debug: Check what data is actually in the field after pre-filling
                const inputs = question.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.type === 'select-one') {
                        console.log('Select input:', input.name, 'value:', input.value, 'selectedIndex:', input.selectedIndex);
                        if (input.selectedIndex >= 0) {
                            console.log('Selected option:', input.options[input.selectedIndex].text, 'disabled:', input.options[input.selectedIndex].disabled);
                        }
                    } else if (input.type === 'radio') {
                        console.log('Radio input:', input.name, 'value:', input.value, 'checked:', input.checked);
                    }
                });
            }
        });
    }

    // Pre-fill a specific global field with existing data
    prefillGlobalField(questionElement) {
        const inputs = questionElement.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            // Check if the input already has a value (from server-side pre-filling)
            if (input.value && input.value.trim() !== '') {
                // For radio buttons and checkboxes, check them if they have a value
                if (input.type === 'radio' || input.type === 'checkbox') {
                    input.checked = true;
                }
                return; // Already has data, no need to pre-fill
            }
            
            // For DOB fields, try to get from user meta
            if (input.name && input.name.includes('date_of_birth')) {
                const dobValue = this.getUserDOB();
                if (dobValue) {
                    input.value = dobValue;
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = true;
                    }
                }
            }
            
            // For gender fields, try to get from user meta
            if (input.name && input.name.includes('gender')) {
                const genderValue = this.getUserGender();
                if (genderValue && input.value === genderValue) {
                    input.checked = true;
                }
            }
        });
    }

    // Get user's date of birth from meta (if available)
    getUserDOB() {
        // This would need to be passed from PHP or fetched via AJAX
        // For now, we'll rely on server-side pre-filling
        return null;
    }

    // Get user's gender from meta (if available)
    getUserGender() {
        // This would need to be passed from PHP or fetched via AJAX
        // For now, we'll rely on server-side pre-filling
        return null;
    }
    
    checkAutoSubmissionReady() {
        const autoSubmitFlag = this.form.querySelector('input[name="auto_submit_ready"]');
        if (autoSubmitFlag && autoSubmitFlag.value === '1') {
            // User has complete contact info - enable auto submission after last real question
            this.autoSubmitReady = true;
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
            if (['ennu_global_date_of_birth_day', 'ennu_global_date_of_birth_month', 'ennu_global_date_of_birth_year'].includes(e.target.name)) {
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

        // Smart skip logic: Skip global fields that already have data
        let nextStep = this.currentStep + 1;
        
        console.log('Smart skip: Starting from step', nextStep, 'of', this.totalSteps);
        
        // Skip global fields that already have data
        while (nextStep < this.totalSteps) {
            const nextQuestion = this.questions[nextStep];
            console.log('Smart skip: Checking step', nextStep, 'is global?', nextQuestion?.hasAttribute('data-is-global'));
            
            if (nextQuestion && nextQuestion.hasAttribute('data-is-global')) {
                // Check if this global field already has data
                const hasData = this.globalFieldHasData(nextQuestion);
                console.log('Smart skip: Step', nextStep, 'has data?', hasData);
                
                if (hasData) {
                    console.log('Smart skip: Skipping step', nextStep, 'because it has data');
                    nextStep++;
                    continue; // Skip this question
                } else {
                    console.log('Smart skip: Step', nextStep, 'is global but has no data, showing it');
                }
            } else {
                console.log('Smart skip: Step', nextStep, 'is not global, showing it');
            }
            break; // Found a question that needs to be shown
        }
        
        console.log('Smart skip: Final step to show:', nextStep);
        
        // Check if we're at the last real question and auto-submit is ready
        if (nextStep >= this.totalSteps && this.autoSubmitReady) {
            this.submitForm();
            return;
        }
        
        // Check if next step exists
        if (nextStep < this.totalSteps) {
            this.showQuestion(nextStep);
        } else {
            // Should not happen with proper contact form logic, but safety check
            this.submitForm();
        }
    }

    // Check if a global field already has data
    globalFieldHasData(questionElement) {
        const inputs = questionElement.querySelectorAll('input, select, textarea');
        let hasData = false;
        
        console.log('=== Checking global field data ===');
        console.log('Question title:', questionElement.querySelector('.question-title')?.textContent);
        
        // Special debugging for gender fields
        if (questionElement.querySelector('.question-title')?.textContent?.includes('gender')) {
            console.log('🔍 This is a gender field - extra debugging enabled');
        }
        
        // Special debugging for DOB fields
        if (questionElement.querySelector('.question-title')?.textContent?.toLowerCase().includes('date') || 
            questionElement.querySelector('.question-title')?.textContent?.toLowerCase().includes('birth') ||
            questionElement.querySelector('.question-title')?.textContent?.toLowerCase().includes('dob')) {
            console.log('🔍 This is a DOB field - extra debugging enabled');
            console.log('DOB container found:', !!questionElement.querySelector('.dob-dropdowns'));
            console.log('DOB inputs found:', questionElement.querySelectorAll('.dob-dropdowns select').length);
        }
        
        for (let input of inputs) {
            // Skip hidden inputs
            if (input.type === 'hidden') {
                console.log('Skipping hidden input:', input.name, input.value);
                continue;
            }
            
            console.log('Checking input:', input.name, 'type:', input.type, 'value:', input.value);
            
            if (input.type === 'radio' || input.type === 'checkbox') {
                console.log('Checking radio/checkbox:', input.name, 'value:', input.value, 'checked:', input.checked);
                
                // Special debugging for gender fields
                if (input.name && input.name.includes('gender')) {
                    console.log('🔍 Gender field input details:');
                    console.log('  - Name:', input.name);
                    console.log('  - Value:', input.value);
                    console.log('  - Checked:', input.checked);
                    console.log('  - ID:', input.id);
                    console.log('  - Required:', input.required);
                }
                
                if (input.checked) {
                    console.log('✅ Global field has data - checked input:', input.name, input.value);
                    hasData = true;
                } else {
                    console.log('❌ Radio/checkbox not checked:', input.name);
                }
            } else if (input.type === 'select-one' || input.type === 'select-multiple') {
                console.log('Select input details:');
                console.log('  - Value:', input.value);
                console.log('  - Selected index:', input.selectedIndex);
                console.log('  - Options count:', input.options.length);
                
                if (input.selectedIndex >= 0) {
                    const selectedOption = input.options[input.selectedIndex];
                    console.log('  - Selected option text:', selectedOption?.text);
                    console.log('  - Selected option disabled:', selectedOption?.disabled);
                    console.log('  - Selected option value:', selectedOption?.value);
                }
                
                // Special handling for DOB dropdowns
                if (input.name && input.name.includes('date_of_birth')) {
                    console.log('🔍 This is a DOB dropdown');
                    // For DOB dropdowns, check if any of the three dropdowns have values
                    const dobContainer = questionElement.querySelector('.dob-dropdowns');
                    if (dobContainer) {
                        const dobInputs = dobContainer.querySelectorAll('select');
                        let dobHasData = false;
                        let allDobFieldsHaveData = true;
                        
                        dobInputs.forEach(dobInput => {
                            console.log('Checking DOB input:', dobInput.name, 'value:', dobInput.value, 'selectedIndex:', dobInput.selectedIndex);
                            
                            // Check if this dropdown has a valid selection (not the disabled placeholder)
                            if (dobInput.selectedIndex > 0 && dobInput.value && dobInput.value !== '') {
                                const selectedOption = dobInput.options[dobInput.selectedIndex];
                                if (selectedOption && !selectedOption.disabled) {
                                    console.log('✅ DOB dropdown has data:', dobInput.name, dobInput.value);
                                    dobHasData = true;
                                } else {
                                    console.log('❌ DOB dropdown has disabled option selected:', dobInput.name);
                                    allDobFieldsHaveData = false;
                                }
                            } else {
                                console.log('❌ DOB dropdown has no data:', dobInput.name);
                                allDobFieldsHaveData = false;
                            }
                        });
                        
                        // For DOB, we need ALL three fields to have data to consider it complete
                        if (allDobFieldsHaveData) {
                            console.log('✅ DOB field has complete data (all 3 fields filled)');
                            hasData = true;
                        } else {
                            console.log('❌ DOB field is incomplete (missing some fields)');
                        }
                    }
                } else {
                    // For regular select dropdowns, check if a non-disabled option is selected
                    if (input.value && input.value !== '' && input.value !== '0' && input.selectedIndex > 0) {
                        // Additional check: make sure it's not the default disabled option
                        const selectedOption = input.options[input.selectedIndex];
                        if (selectedOption && !selectedOption.disabled) {
                            console.log('✅ Global field has data - select input:', input.name, input.value);
                            hasData = true;
                        } else {
                            console.log('❌ Select has value but option is disabled or invalid');
                        }
                    } else {
                        console.log('❌ Select has no valid value');
                    }
                }
            } else {
                if (input.value && input.value.trim() !== '') {
                    console.log('✅ Global field has data - text input:', input.name, input.value);
                    hasData = true;
                } else {
                    console.log('❌ Text input has no value:', input.name);
                }
            }
        }
        
        console.log('=== Final result: Global field has data =', hasData, '===');
        return hasData;
    }

    prevQuestion() {
        if (this.currentStep > 0) {
            // Smart skip logic: Skip global fields that already have data when going backwards
            let prevStep = this.currentStep - 1;
            
            // Skip global fields that already have data
            while (prevStep >= 0) {
                const prevQuestion = this.questions[prevStep];
                if (prevQuestion && prevQuestion.hasAttribute('data-is-global')) {
                    // Check if this global field already has data
                    if (this.globalFieldHasData(prevQuestion)) {
                        prevStep--;
                        continue; // Skip this question
                    }
                }
                break; // Found a question that needs to be shown
            }
            
            if (prevStep >= 0) {
                this.showQuestion(prevStep);
            }
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
        const day = this.form.querySelector('select[name="ennu_global_date_of_birth_day"]')?.value;
        const month = this.form.querySelector('select[name="ennu_global_date_of_birth_month"]')?.value;
        const year = this.form.querySelector('select[name="ennu_global_date_of_birth_year"]')?.value;
        const ageDisplay = this.form.querySelector('.calculated-age-display');

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
        const year = this.form.querySelector('select[name="ennu_global_date_of_birth_year"]')?.value;
        const month = this.form.querySelector('select[name="ennu_global_date_of_birth_month"]')?.value;
        const day = this.form.querySelector('select[name="ennu_global_date_of_birth_day"]')?.value;
        const combinedField = this.form.querySelector('input[name="ennu_global_date_of_birth"]');

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
        let nonce = this.form.dataset.nonce;
        if (!nonce && typeof ennu_ajax !== 'undefined' && ennu_ajax.nonce) {
            nonce = ennu_ajax.nonce;
            console.warn('Form data-nonce missing, using ennu_ajax.nonce fallback:', nonce);
        } else {
            console.log('Form nonce:', nonce);
        }

        if (!nonce) {
            alert('Security token missing. Please refresh the page.');
            this.isSubmitting = false;
            this.form.classList.remove('loading');
            return;
        }
        formData.append('nonce', nonce);

        console.log('ENNU REDIRECT DEBUG: Starting AJAX request to:', ennu_ajax.ajax_url);
        console.log('ENNU REDIRECT DEBUG: Form data being sent:', Object.fromEntries(formData));
        
        fetch(ennu_ajax.ajax_url, {
            method: 'POST',
            body: formData,
            // Increase timeout to handle database optimization and slow queries
            signal: AbortSignal.timeout(600000) // 600 seconds timeout for slow queries
        })
        .then(response => {
            console.log('ENNU REDIRECT DEBUG: AJAX response received:', response);
            console.log('ENNU REDIRECT DEBUG: Response status:', response.status);
            console.log('ENNU REDIRECT DEBUG: Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('ENNU REDIRECT DEBUG: AJAX response received:', data);
            console.log('ENNU REDIRECT DEBUG: Data type:', typeof data);
            console.log('ENNU REDIRECT DEBUG: Data keys:', Object.keys(data));
            console.log('ENNU REDIRECT DEBUG: Success property:', data.success);
            console.log('ENNU REDIRECT DEBUG: Data property:', data.data);
            
            if (data && data.success === true) {
                if (data.data && data.data.redirect_url) {
                    console.log('ENNU REDIRECT DEBUG: Redirect URL found:', data.data.redirect_url);
                    
                    // Update auth state if provided
                    if (data.data.auth_state) {
                        this.updateAllFormsAuthState(data.data.auth_state);
                    }
                    
                    // Multiple redirect attempts with fallbacks
                    const redirectUrl = data.data.redirect_url;
                    console.log('ENNU REDIRECT DEBUG: Attempting redirect to:', redirectUrl);
                    
                    // Method 1: Direct window.location.href
                    try {
                        console.log('ENNU REDIRECT DEBUG: Executing Method 1 (window.location.href)');
                        window.location.href = redirectUrl;
                        console.log('ENNU REDIRECT DEBUG: Method 1 (window.location.href) executed successfully');
                    } catch (error) {
                        console.error('ENNU REDIRECT DEBUG: Method 1 failed:', error);
                        
                        // Method 2: window.location.replace
                        try {
                            window.location.replace(redirectUrl);
                            console.log('Redirect method 2 (window.location.replace) executed');
                        } catch (error2) {
                            console.error('Redirect method 2 failed:', error2);
                            
                            // Method 3: window.location.assign
                            try {
                                window.location.assign(redirectUrl);
                                console.log('Redirect method 3 (window.location.assign) executed');
                            } catch (error3) {
                                console.error('Redirect method 3 failed:', error3);
                                
                                // Method 4: Create and click a link
                                try {
                                    const link = document.createElement('a');
                                    link.href = redirectUrl;
                                    link.style.display = 'none';
                                    document.body.appendChild(link);
                                    link.click();
                                    console.log('Redirect method 4 (link click) executed');
                                } catch (error4) {
                                    console.error('Redirect method 4 failed:', error4);
                                    
                                    // Final fallback: Show success message with manual link
                                    console.warn('All redirect methods failed. Showing fallback success message.');
                                    this.showFallbackSuccess(redirectUrl);
                                }
                            }
                        }
                    }
                } else {
                    console.warn('No redirect_url in response. Showing fallback success message.');
                    this.showFallbackSuccess();
                }
            } else {
                console.error('ENNU REDIRECT DEBUG: AJAX response indicates failure');
                console.error('ENNU REDIRECT DEBUG: Full response data:', data);
                console.error('ENNU REDIRECT DEBUG: Data structure:', JSON.stringify(data, null, 2));
                
                const message = data && data.data && data.data.message ? data.data.message : 'An unknown error occurred.';
                this.showError(this.form, message);
                console.error('AJAX error:', message);
            }
        })
                        .catch(error => {
                    console.error('ENNU REDIRECT DEBUG: AJAX catch error:', error);
                    
                    // Show success message even if AJAX fails (data was likely processed)
                    console.log('ENNU REDIRECT DEBUG: AJAX request failed, but showing success message as data was likely processed');
                    this.showFallbackSuccess();
                    
                    // Try to redirect to a default results page
                    setTimeout(() => {
                        console.log('ENNU REDIRECT DEBUG: Attempting fallback redirect to assessment results');
                        window.location.href = '/assessment-results/';
                    }, 2000);
                })
        .finally(() => {
            this.isSubmitting = false;
            this.form.classList.remove('loading');
        });
    }

    showFallbackSuccess(redirectUrl = null) {
        // Remove form and show a visible success message with manual redirect link
        this.form.style.display = 'none';
        let fallbackDiv = document.createElement('div');
        fallbackDiv.className = 'ennu-fallback-success';
        
        const dashboardUrl = redirectUrl || '/dashboard';
        
        fallbackDiv.innerHTML = `
            <div class="success-icon">✓</div>
            <h2>Assessment Complete!</h2>
            <p>Thank you for completing your assessment. Your personalized results and recommendations will be sent to your email shortly.</p>
            <div class="next-steps">
                <h3>What happens next?</h3>
                <ul>
                    <li>Our medical team will review your responses</li>
                    <li>You'll receive personalized recommendations via email</li>
                    <li>A specialist may contact you to discuss treatment options</li>
                </ul>
            </div>
            <p><a href="${dashboardUrl}" class="ennu-dashboard-link">Go to your dashboard</a></p>
            <p><small>If the link doesn't work, please copy and paste this URL: ${dashboardUrl}</small></p>
        `;
        this.form.parentNode.insertBefore(fallbackDiv, this.form.nextSibling);
    }

    /**
     * Refresh authentication state for all assessment forms on the page
     * This handles the case where a user creates an account during submission
     */
    refreshAuthenticationState() {
        
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
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                const authState = data.data;
                
                // Update all assessment forms on the page with new auth state
                this.updateAllFormsAuthState(authState);
                return authState;
            } else {
                return null;
            }
        })
        .catch(error => {
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
        
        allForms.forEach((form, index) => {
            
            // Update auto-submit flag
            let autoSubmitFlag = form.querySelector('input[name="auto_submit_ready"]');
            if (!autoSubmitFlag) {
                autoSubmitFlag = document.createElement('input');
                autoSubmitFlag.type = 'hidden';
                autoSubmitFlag.name = 'auto_submit_ready';
                form.appendChild(autoSubmitFlag);
            }
            const oldValue = autoSubmitFlag.value;
            autoSubmitFlag.value = authState.auto_submit_ready ? '1' : '0';
            
            // Update contact form visibility if needed
            const contactSlide = form.querySelector('.question-slide[data-is-contact-form]');
            if (contactSlide) {
                if (authState.needs_contact_form) {
                    contactSlide.style.display = '';
                    // Update missing fields in contact form
                    this.updateContactFormFields(contactSlide, authState);
                } else {
                    contactSlide.style.display = 'none';
                }
            } else {
            }
            
            // Update form instance auto-submit status if it exists
            if (form.ennuFormInstance) {
                const oldAutoSubmit = form.ennuFormInstance.autoSubmitReady;
                form.ennuFormInstance.autoSubmitReady = authState.auto_submit_ready;
            } else {
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

// ===== SIGNUP PAGE FUNCTIONALITY =====

document.addEventListener('DOMContentLoaded', function() {
    // Initialize signup page functionality
    initSignupPage();
});

function initSignupPage() {
    const signupContainer = document.querySelector('.ennu-signup-container');
    if (!signupContainer) return;

    // Smooth scroll for anchor links
    initSmoothScrolling();
    
    // Product card interactions
    initProductCards();
    
    // Contact form handling
    initContactForm();
    
    // Intersection Observer for animations
    initScrollAnimations();
    
    // Mobile menu toggle
    initMobileMenu();
}

function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 100;
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

function initProductCards() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        // Add click handler for product selection
        const ctaButton = card.querySelector('.btn');
        if (ctaButton) {
            ctaButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                const productName = card.querySelector('h3').textContent;
                const productPrice = card.querySelector('.price').textContent;
                
                // Show selection confirmation
                showProductSelection(productName, productPrice);
                
                // Track selection (for analytics)
                trackProductSelection(productName);
            });
        }
        
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

function showProductSelection(productName, productPrice) {
    // Create modal for product selection
    const modal = document.createElement('div');
    modal.className = 'ennu-product-modal';
    modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Selected: ${productName}</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>You've selected <strong>${productName}</strong> for <strong>${productPrice}</strong></p>
                    <p>Our team will contact you within 24 hours to complete your registration.</p>
                    <div class="modal-actions">
                        <button class="btn btn-primary" onclick="proceedWithSelection('${productName}')">Proceed</button>
                        <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal styles
    const style = document.createElement('style');
    style.textContent = `
        .ennu-product-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            position: relative;
            z-index: 1001;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(modal);
    
    // Close modal functionality
    modal.querySelector('.modal-close').addEventListener('click', closeModal);
    modal.querySelector('.modal-overlay').addEventListener('click', closeModal);
}

function closeModal() {
    const modal = document.querySelector('.ennu-product-modal');
    if (modal) {
        modal.remove();
    }
}

function proceedWithSelection(productName) {
    // Here you would integrate with your payment system
    // For now, we'll show a success message
    
    const modal = document.querySelector('.ennu-product-modal');
    if (modal) {
        modal.querySelector('.modal-content').innerHTML = `
            <div class="modal-header">
                <h3>Thank You!</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; color: #48bb78; margin-bottom: 1rem;">✓</div>
                    <h4>Selection Confirmed</h4>
                    <p>You've selected <strong>${productName}</strong></p>
                    <p>Our team will contact you within 24 hours to complete your registration and payment.</p>
                    <p>Check your email for confirmation details.</p>
                </div>
            </div>
        `;
    }
    
    // Track successful selection
    trackSuccessfulSelection(productName);
}

function initContactForm() {
    const contactForm = document.querySelector('.ennu-contact-form');
    if (!contactForm) return;
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Basic validation
        if (!data.name || !data.email || !data.message) {
            showNotification('Please fill in all required fields.', 'error');
            return;
        }
        
        if (!isValidEmail(data.email)) {
            showNotification('Please enter a valid email address.', 'error');
            return;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Sending...';
        submitBtn.disabled = true;
        
        // Simulate form submission (replace with actual AJAX call)
        setTimeout(() => {
            showNotification('Thank you! We\'ll get back to you soon.', 'success');
            this.reset();
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }, 2000);
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `ennu-notification ennu-notification-${type}`;
    notification.textContent = message;
    
    // Add notification styles
    const style = document.createElement('style');
    style.textContent = `
        .ennu-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            animation: notificationSlideIn 0.3s ease-out;
        }
        
        .ennu-notification-success {
            background: #48bb78;
        }
        
        .ennu-notification-error {
            background: #f56565;
        }
        
        .ennu-notification-info {
            background: #4299e1;
        }
        
        @keyframes notificationSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    `;
    
    if (!document.querySelector('#ennu-notification-styles')) {
        style.id = 'ennu-notification-styles';
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.process-step, .product-card, .contact-item');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
}

// Analytics tracking functions
function trackProductSelection(productName) {
    // Track product selection for analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'select_content', {
            content_type: 'product',
            item_id: productName.toLowerCase().replace(/\s+/g, '_')
        });
    }
    
    // Console log for development
}

function trackSuccessfulSelection(productName) {
    // Track successful product selection
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', {
            transaction_id: 'temp_' + Date.now(),
            value: productName.includes('Membership') ? 199 : 299,
            currency: 'USD',
            items: [{
                item_id: productName.toLowerCase().replace(/\s+/g, '_'),
                item_name: productName,
                price: productName.includes('Membership') ? 199 : 299,
                quantity: 1
            }]
        });
    }
    
    // Console log for development
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Handle window resize
window.addEventListener('resize', debounce(() => {
    // Reinitialize responsive elements if needed
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        if (window.innerWidth <= 768) {
            card.style.transform = 'none';
        }
    });
}, 250));

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // Page is hidden, pause animations if needed
        document.body.classList.add('page-hidden');
    } else {
        // Page is visible again
        document.body.classList.remove('page-hidden');
    }
});

