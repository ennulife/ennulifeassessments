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
        
        // Check for preset gender from shortcode attribute
        this.presetGender = this.form.dataset.presetGender || null;
        if (this.presetGender) {
            console.log('🎯 Gender preset detected:', this.presetGender);
            
            // Ensure the hidden gender input has the correct value
            const hiddenGenderInput = this.form.querySelector('input[type="hidden"][name="ennu_global_gender"]');
            if (hiddenGenderInput) {
                hiddenGenderInput.value = this.presetGender;
            }
        }

        // Update total steps display - account for hidden gender question
        if (this.totalStepsText) {
            // If gender is preset, reduce total count by 1 (since gender question is hidden)
            const displayTotal = this.presetGender ? this.totalSteps : this.totalSteps;
            this.totalStepsText.textContent = displayTotal;
        }
        
        // Pre-fill global fields with existing data
        this.prefillGlobalFields();
        
        // Add a small delay to ensure DOM is fully ready before showing first question
        setTimeout(() => {
            // ULTRA FIX: Force DOM state synchronization BEFORE any detection
            this.synchronizeDOMWithHTMLAttributes();
            
            this.showQuestion(0);
            this.bindEvents();
            
            // Check for auto-submission flag
            this.checkAutoSubmissionReady();

            // ULTRA FIX: Multiple attempts at skipping with increasing delays
            setTimeout(() => this.skipPrefilledGlobalSlides(), 100);
            setTimeout(() => this.skipPrefilledGlobalSlides(), 300);
            setTimeout(() => this.skipPrefilledGlobalSlides(), 500);
        }, 150);
    }

    // ULTRA FIX: Force DOM state to match HTML attributes
    synchronizeDOMWithHTMLAttributes() {
        console.log('🔧 ULTRA FIX: Synchronizing DOM with HTML attributes...');
        
        // Find all radio buttons and checkboxes, especially gender fields
        const allInputs = this.form.querySelectorAll('input[type="radio"], input[type="checkbox"]');
        let syncedCount = 0;
        
        allInputs.forEach(input => {
            // Force DOM checked property to match HTML checked attribute
            if (input.hasAttribute('checked')) {
                const isGender = input.name === 'ennu_global_gender';
                if (!input.checked) {
                    input.checked = true;
                    syncedCount++;
                    console.log(`🔧 Synced ${isGender ? 'GENDER' : 'input'}: ${input.name}=${input.value} -> checked=true`);
                }
            } else {
                if (input.checked) {
                    // If DOM says checked but HTML doesn't have attribute, trust the DOM
                    console.log(`⚠️ DOM checked but no HTML attribute: ${input.name}=${input.value}`);
                }
            }
        });
        
        console.log(`🔧 Synchronized ${syncedCount} inputs`);
    }

    // Skip over consecutive global slides that already have saved data (no user interaction required)
    skipPrefilledGlobalSlides() {
        console.log('ENNU Debug: skipPrefilledGlobalSlides called');
        
        // Do not auto-skip on contact slide
        const isContact = (slide) => slide && slide.hasAttribute('data-is-contact-form');
        // Only skip when logged-in or when inputs are already pre-selected
        const canSkip = () => true;

        let advanced = false;
        let skippedSlides = [];
        
        while (this.currentStep < this.totalSteps) {
            const slide = this.questions[this.currentStep];
            if (!slide) break;

            // Only consider global slides, never contact form
            const isGlobal = slide.hasAttribute('data-is-global');
            const isGenderField = slide.querySelector('input[name="ennu_global_gender"]') !== null;
            
            console.log(`ENNU Debug: Checking slide ${this.currentStep}, isGlobal: ${isGlobal}, isContact: ${isContact(slide)}, isGenderField: ${isGenderField}`);
            
            if (!isGlobal || isContact(slide)) {
                console.log(`ENNU Debug: Breaking - not global (${!isGlobal}) or is contact (${isContact(slide)})`);
                break;
            }

            // If this global slide already has a value (e.g., gender), skip it
            const hasData = this.globalFieldHasData(slide);
            console.log(`ENNU Debug: Slide ${this.currentStep} hasData: ${hasData}`);
            
            if (hasData && canSkip()) {
                const nextIndex = this.currentStep + 1;
                if (nextIndex < this.totalSteps) {
                    skippedSlides.push(`Slide ${this.currentStep}${isGenderField ? ' (Gender)' : ''}`);
                    this.showQuestion(nextIndex);
                    advanced = true;
                    continue;
                }
            }
            console.log('ENNU Debug: Breaking - no data or cannot skip');
            break;
        }

        if (advanced) {
            console.log(`ENNU Debug: Advanced through slides, skipped: ${skippedSlides.join(', ')}`);
            this.updateProgress();
        } else {
            console.log('ENNU Debug: No slides were skipped');
        }
    }

    // Pre-fill global fields with existing data
    prefillGlobalFields() {
        // CRITICAL: Only prefill for logged-in users
        // Check if user is logged in by looking for user_id input or checking auth state
        const userIdInput = this.form.querySelector('input[name="user_id"]');
        const isLoggedIn = userIdInput && userIdInput.value && userIdInput.value !== '0';
        
        if (!isLoggedIn) {
            console.log('🚫 User not logged in - skipping prefill to prevent data contamination');
            // Clear any localStorage data for logged-out users
            try {
                localStorage.removeItem('ennu_assessment_data');
                localStorage.removeItem('ennu_contact_info_saved');
                localStorage.removeItem('ennu_global_fields_saved');
            } catch (e) {
                console.error('Failed to clear localStorage:', e);
            }
            return;
        }
        
        // Only prefill for logged-in users
        this.questions.forEach((question, index) => {
            if (question.hasAttribute('data-is-global')) {
                this.prefillGlobalField(question);
                
                // Debug: Check what data is actually in the field after pre-filling
                const inputs = question.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.type === 'select-one') {
                        if (input.selectedIndex >= 0) {
                        }
                    } else if (input.type === 'radio') {
                    }
                });
            }
        });
    }

    // Pre-fill a specific global field with existing data
    prefillGlobalField(questionElement) {
        const inputs = questionElement.querySelectorAll('input, select, textarea');
        
        // Enhanced global field detection - use correct field name
        const isGenderField = questionElement.querySelector('input[name="ennu_global_gender"]') !== null;
        const isDOBField = questionElement.querySelector('input[name*="date_of_birth"]') !== null;
        
        // Try to get saved values for this field from a data attribute or JS state
        let savedValues = [];
        if (questionElement.dataset && questionElement.dataset.savedValues) {
            try {
                savedValues = JSON.parse(questionElement.dataset.savedValues);
            } catch (e) {
                savedValues = [];
            }
        }
        
        inputs.forEach(input => {
            // For checkboxes: only check if value is in savedValues
            if (input.type === 'checkbox') {
                input.checked = Array.isArray(savedValues) && savedValues.includes(input.value);
                return;
            }
            // For radio: only check if value matches saved value
            if (input.type === 'radio') {
                input.checked = savedValues === input.value;
                
                // Enhanced gender field prefilling - check if this radio is already checked server-side
                if (input.name === 'ennu_global_gender') {
                    if (input.hasAttribute('checked') && input.getAttribute('checked') === 'checked') {
                        input.checked = true;
                        console.log(`ENNU Gender Debug: Prefilled gender from server-side checked attribute: ${input.value}`);
                    }
                }
                return;
            }
            // For other types, prefill if value exists
            if (input.value && input.value.trim() !== '') {
                return; // Already has data, no need to pre-fill
            }
            // For DOB fields, try to get from user meta
            if (input.name && input.name.includes('date_of_birth')) {
                const dobValue = this.getUserDOB();
                if (dobValue) {
                    input.value = dobValue;
                }
            }
            // For gender fields, try to get from user meta
            if (input.name === 'ennu_global_gender') {
                const genderValue = this.getUserGender();
                if (genderValue && input.value === genderValue) {
                    input.checked = true;
                    console.log(`ENNU Gender Debug: Prefilled gender from getUserGender(): ${genderValue}`);
                }
            }
        });
        
        // Additional debugging for gender fields
        if (isGenderField) {
            const checkedInput = questionElement.querySelector('input[name="ennu_global_gender"]:checked');
            if (checkedInput) {
                console.log(`ENNU Gender Debug: After prefill, found checked gender input: ${checkedInput.value}`);
            } else {
                console.log('ENNU Gender Debug: After prefill, no checked gender input found');
                // List all gender inputs and their states
                const genderInputs = questionElement.querySelectorAll('input[name="ennu_global_gender"]');
                genderInputs.forEach(input => {
                    console.log(`ENNU Gender Debug: Gender input ${input.value} - checked: ${input.checked}, hasAttribute('checked'): ${input.hasAttribute('checked')}`);
                });
            }
        }
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
        
        // Track the last email to detect changes
        this.lastKnownEmail = null;
        
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

        // Email change detection - Clear storage if email changes
        this.form.addEventListener('change', (e) => {
            if (e.target.name === 'email' || e.target.name === 'ennu_global_email') {
                const newEmail = e.target.value.toLowerCase().trim();
                
                // Check if email has changed from what we had before
                if (this.lastKnownEmail && this.lastKnownEmail !== newEmail) {
                    console.log('⚠️ Email changed - clearing stored data to prevent contamination');
                    
                    // Clear all localStorage data when email changes
                    try {
                        Object.keys(localStorage).forEach(key => {
                            if (key.startsWith('ennu_') && !key.includes('theme')) {
                                localStorage.removeItem(key);
                            }
                        });
                        
                        // Also clear any form-specific storage
                        localStorage.removeItem('ennu_assessment_data');
                        localStorage.removeItem('ennu_contact_info_saved');
                        localStorage.removeItem('ennu_global_fields_saved');
                        
                        console.log('✅ Cleared stored data for previous email');
                    } catch (err) {
                        console.error('Failed to clear localStorage:', err);
                    }
                }
                
                // Update the last known email
                this.lastKnownEmail = newEmail;
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

        // Smart skip logic: Skip global fields that already have data (only for logged-in users)
        let nextStep = this.currentStep + 1;
        
        
        // Only apply smart skip logic for logged-in users
        const isLoggedIn = this.form.querySelector('input[name="user_id"]')?.value || 
                          this.form.querySelector('input[name="email"]')?.value;
        
        if (isLoggedIn) {
            
            // Skip global fields that already have data
            while (nextStep < this.totalSteps) {
                const nextQuestion = this.questions[nextStep];
                
                if (nextQuestion && nextQuestion.hasAttribute('data-is-global')) {
                    // Check if this global field already has data
                    const hasData = this.globalFieldHasData(nextQuestion);
                    
                    if (hasData) {
                        nextStep++;
                        continue; // Skip this question
                    } else {
                    }
                } else {
                }
                break; // Found a question that needs to be shown
            }
        } else {
        }
        
        
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

    // ULTRA FIX: Completely rewritten global field detection
    globalFieldHasData(questionElement) {
        console.log('🔍 ULTRA FIX: Checking if global field has data...');
        
        const inputs = questionElement.querySelectorAll('input, select, textarea');
        let hasData = false;
        
        // Enhanced field type detection with debugging
        const isGenderField = questionElement.querySelector('input[name="ennu_global_gender"]') !== null;
        const isDOBField = questionElement.querySelector('input[name*="date_of_birth"]') !== null || 
                          questionElement.querySelector('.dob-dropdowns') !== null;
        const isHeightWeightField = questionElement.querySelector('input[name*="height_ft"]') !== null ||
                                   questionElement.querySelector('input[name*="weight"]') !== null;
        
        // Debug logging removed for production
        
        // SPECIAL HANDLING FOR GENDER FIELDS (most critical)
        if (isGenderField) {
            const genderInputs = questionElement.querySelectorAll('input[name="ennu_global_gender"]');
            console.log(`🎯 GENDER FIELD: Found ${genderInputs.length} gender inputs`);
            
            let genderHasData = false;
            genderInputs.forEach((input, index) => {
                const domChecked = input.checked;
                const htmlChecked = input.hasAttribute('checked');
                const checkedValue = input.getAttribute('checked');
                
                console.log(`  Gender input ${index}: value=${input.value}, DOM.checked=${domChecked}, hasAttribute('checked')=${htmlChecked}, getAttribute('checked')=${checkedValue}`);
                
                // Multiple ways to detect if this is the selected gender
                if (domChecked || htmlChecked || checkedValue === 'checked') {
                    console.log(`✅ GENDER DETECTED: ${input.value} (DOM:${domChecked}, HTML:${htmlChecked}, attr:${checkedValue})`);
                    genderHasData = true;
                }
            });
            
            if (genderHasData) {
                console.log('✅ GENDER FIELD HAS DATA - will be skipped');
                return true;
            } else {
                console.log('❌ GENDER FIELD NO DATA - will be shown');
                return false;
            }
        }
        
        // ENHANCED GENERAL DETECTION for non-gender fields
        for (let input of inputs) {
            // Skip hidden inputs
            if (input.type === 'hidden') continue;
            
            if (input.type === 'radio' || input.type === 'checkbox') {
                // Check both DOM property and HTML attribute
                if (input.checked || (input.hasAttribute('checked') && input.getAttribute('checked') === 'checked')) {
                    console.log(`✅ Found checked radio/checkbox: ${input.name} = ${input.value}`);
                    hasData = true;
                    break;
                }
            } else if (input.type === 'select-one' || input.type === 'select-multiple') {
                
                if (input.selectedIndex >= 0) {
                    const selectedOption = input.options[input.selectedIndex];
                }
                
                // Special handling for DOB dropdowns
                if (input.name && input.name.includes('date_of_birth')) {
                    // For DOB dropdowns, check if any of the three dropdowns have values
                    const dobContainer = questionElement.querySelector('.dob-dropdowns');
                    if (dobContainer) {
                        const dobInputs = dobContainer.querySelectorAll('select');
                        let dobHasData = false;
                        let allDobFieldsHaveData = true;
                        
                        dobInputs.forEach(dobInput => {
                            
                            // Check if this dropdown has a valid selection (not the disabled placeholder)
                            if (dobInput.selectedIndex > 0 && dobInput.value && dobInput.value !== '') {
                                const selectedOption = dobInput.options[dobInput.selectedIndex];
                                if (selectedOption && !selectedOption.disabled) {
                                    dobHasData = true;
                                } else {
                                    allDobFieldsHaveData = false;
                                }
                            } else {
                                allDobFieldsHaveData = false;
                            }
                        });
                        
                        // For DOB, we need ALL three fields to have data to consider it complete
                        if (allDobFieldsHaveData) {
                            hasData = true;
                        } else {
                        }
                    }
                } else {
                    // For regular select dropdowns, check if a non-disabled option is selected
                    if (input.value && input.value !== '' && input.value !== '0' && input.selectedIndex > 0) {
                        // Additional check: make sure it's not the default disabled option
                        const selectedOption = input.options[input.selectedIndex];
                        if (selectedOption && !selectedOption.disabled) {
                            hasData = true;
                        } else {
                        }
                    } else {
                    }
                }
            } else {
                if (input.value && input.value.trim() !== '') {
                    hasData = true;
                } else {
                }
            }
        }
        
        return hasData;
    }

    prevQuestion() {
        if (this.currentStep > 0) {
            // Smart skip logic: Skip global fields that already have data when going backwards (only for logged-in users)
            let prevStep = this.currentStep - 1;
            
            // Only apply smart skip logic for logged-in users
            const isLoggedIn = this.form.querySelector('input[name="user_id"]')?.value || 
                              this.form.querySelector('input[name="email"]')?.value;
            
            if (isLoggedIn) {
                
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
            } else {
            }
            
            if (prevStep >= 0) {
                this.showQuestion(prevStep);
            }
        }
    }

    showQuestion(stepIndex) {
        console.log(`🎬 ULTRA FIX: Showing question ${stepIndex}`);
        
        // Hide all questions
        this.questions.forEach(q => q.classList.remove('active'));
        
        // Show current question
        if (this.questions[stepIndex]) {
            this.questions[stepIndex].classList.add('active');
            this.currentStep = stepIndex;
            
            // Update progress
            this.updateProgress();
            
            // ULTRA FIX: Enhanced skip detection with multiple checks
            const currentSlide = this.questions[this.currentStep];
            const isGlobal = currentSlide && currentSlide.hasAttribute('data-is-global');
            const isContact = currentSlide && currentSlide.hasAttribute('data-is-contact-form');
            const isGenderField = currentSlide && currentSlide.querySelector('input[name="ennu_global_gender"]') !== null;
            
            console.log(`🎬 Question ${stepIndex}: isGlobal=${isGlobal}, isContact=${isContact}, isGenderField=${isGenderField}`);
            
            if (isGlobal && !isContact) {
                // Force sync before checking (critical for gender fields)
                if (isGenderField) {
                    const genderInputs = currentSlide.querySelectorAll('input[name="ennu_global_gender"]');
                    genderInputs.forEach(input => {
                        if (input.hasAttribute('checked') && !input.checked) {
                            input.checked = true;
                            console.log(`🔧 CRITICAL SYNC: Fixed gender input ${input.value}`);
                        }
                    });
                }
                
                // Check if field has data
                const hasData = this.globalFieldHasData(currentSlide);
                console.log(`🎬 Global field hasData: ${hasData}`);
                
                if (hasData) {
                    console.log(`⏭️ ULTRA FIX: Auto-skipping question ${stepIndex} (${isGenderField ? 'GENDER' : 'global'})`);
                    // Multiple skip attempts with different delays for reliability
                    setTimeout(() => this.nextQuestion(), 50);
                    setTimeout(() => {
                        if (this.currentStep === stepIndex) {
                            console.log(`⏭️ ULTRA FIX: Secondary skip attempt for question ${stepIndex}`);
                            this.nextQuestion();
                        }
                    }, 200);
                    return;
                }
            }

            // Focus first input in the question (only if not skipped)
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
    
    createProgressModal(isLoggedIn = false) {
        // Create modal container
        const modal = document.createElement('div');
        modal.className = 'ennu-progress-modal';
        
        // Different content based on user login status
        const headerTitle = isLoggedIn 
            ? "Processing Your Assessment" 
            : "Creating Your Personalized Health Profile";
        
        const step1Title = isLoggedIn 
            ? "Validating Your Responses" 
            : "Creating Your Account";
        
        const step1Subtitle = isLoggedIn 
            ? "Reviewing your assessment data" 
            : "Setting up your secure health profile";
        
        modal.innerHTML = `
            <div class="ennu-progress-overlay"></div>
            <div class="ennu-progress-content">
                <div class="progress-logo">
                    <img src="${ennu_ajax.plugin_url}/assets/img/ennu-logo-black.png" alt="ENNU Life" />
                </div>
                <div class="progress-header">
                    <h2>${headerTitle}</h2>
                    <p class="progress-subtitle">Please wait while we prepare your results...</p>
                </div>
                
                <div class="progress-steps">
                    <div class="progress-step active" data-step="1">
                        <div class="step-icon">
                            <svg class="step-spinner" viewBox="0 0 50 50">
                                <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                            </svg>
                            <svg class="step-check" style="display:none;" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="step-text">
                            <h3>${step1Title}</h3>
                            <p>${step1Subtitle}</p>
                        </div>
                    </div>
                    
                    <div class="progress-step" data-step="2">
                        <div class="step-icon">
                            <svg class="step-spinner" viewBox="0 0 50 50">
                                <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                            </svg>
                            <svg class="step-check" style="display:none;" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="step-text">
                            <h3>Saving Your Responses</h3>
                            <p>Securely storing your assessment data</p>
                        </div>
                    </div>
                    
                    <div class="progress-step" data-step="3">
                        <div class="step-icon">
                            <svg class="step-spinner" viewBox="0 0 50 50">
                                <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                            </svg>
                            <svg class="step-check" style="display:none;" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="step-text">
                            <h3>Calculating Your Scores</h3>
                            <p>Analyzing your health metrics</p>
                        </div>
                    </div>
                    
                    <div class="progress-step" data-step="4">
                        <div class="step-icon">
                            <svg class="step-spinner" viewBox="0 0 50 50">
                                <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                            </svg>
                            <svg class="step-check" style="display:none;" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="step-text">
                            <h3>Preparing Your Results</h3>
                            <p>Generating personalized recommendations</p>
                        </div>
                    </div>
                </div>
                
                <div class="progress-footer">
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: 0%"></div>
                    </div>
                    <p class="progress-message">This usually takes 5-10 seconds...</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Force layout and add active class for animation
        modal.offsetHeight;
        modal.classList.add('active');
        
        return modal;
    }
    
    updateProgressModal(modal, step) {
        const steps = modal.querySelectorAll('.progress-step');
        const progressBar = modal.querySelector('.progress-bar-fill');
        const progressMessage = modal.querySelector('.progress-message');
        
        // Update step states
        steps.forEach((stepEl, index) => {
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
                stepEl.querySelector('.step-spinner').style.display = 'block';
                stepEl.querySelector('.step-check').style.display = 'none';
            }
        });
        
        // Update progress bar
        const progressPercent = ((step - 1) / 4) * 100;
        progressBar.style.width = progressPercent + '%';
        
        // Update message based on step
        const messages = {
            1: 'Creating your secure account...',
            2: 'Saving your assessment responses...',
            3: 'Analyzing your health data...',
            4: 'Almost there! Preparing your personalized results...'
        };
        progressMessage.textContent = messages[step] || 'Processing...';
    }
    
    removeProgressModal(modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 800); // Increased from 300ms for smoother fade out
    }

    submitForm() {
        
        if (this.isSubmitting || !this.validateCurrentQuestion()) {
            return;
        }
        
        this.isSubmitting = true;
        this.form.classList.add('loading');
        
        const formData = new FormData(this.form);
        let nonce = this.form.dataset.nonce;
        if (!nonce && typeof ennu_ajax !== 'undefined' && ennu_ajax.nonce) {
            nonce = ennu_ajax.nonce;
        } else {
        }

        if (!nonce) {
            alert('Security token missing. Please refresh the page.');
            this.isSubmitting = false;
            this.form.classList.remove('loading');
            return;
        }
        formData.append('nonce', nonce);

        // Check user status for modal display
        const email = formData.get('email');
        const userId = formData.get('user_id');
        const isLoggedIn = Boolean(userId);
        let progressModal = null;
        let modalStep = 1;
        
        // Show progress modal for all users with appropriate messaging
        // Always show modal to provide feedback during processing
        if (email) {
            progressModal = this.createProgressModal(isLoggedIn);
            
            // Simulate progress steps - EXTENDED TIMING for better user experience
            setTimeout(() => {
                if (progressModal && modalStep === 1) {
                    modalStep = 2;
                    this.updateProgressModal(progressModal, 2);
                }
            }, 3000); // Increased from 1500ms to 3000ms (3 seconds)
            
            setTimeout(() => {
                if (progressModal && modalStep === 2) {
                    modalStep = 3;
                    this.updateProgressModal(progressModal, 3);
                }
            }, 6000); // Increased from 3000ms to 6000ms (6 seconds)
            
            setTimeout(() => {
                if (progressModal && modalStep === 3) {
                    modalStep = 4;
                    this.updateProgressModal(progressModal, 4);
                }
            }, 9000); // Increased from 4500ms to 9000ms (9 seconds)
        }
        
        fetch(ennu_ajax.ajax_url, {
            method: 'POST',
            body: formData,
            // Increase timeout to handle database optimization and slow queries
            signal: AbortSignal.timeout(600000) // 600 seconds timeout for slow queries
        })
        .then(response => {
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
			
			if (data && data.success === true) {
                			if (data.data && data.data.redirect_url && data.data.redirect_url !== false) {
				
				// CRITICAL: Clear all localStorage data after successful submission
				// This prevents data from persisting for the next user on the same device
				try {
					// Clear all assessment-related localStorage keys
					localStorage.removeItem('ennu_assessment_data');
					localStorage.removeItem('ennu_assessment_progress');
					localStorage.removeItem('ennu_contact_info_saved');
					localStorage.removeItem('ennu_global_fields_saved');
					
					// Clear any auto-save data with assessment type prefix
					const assessmentType = this.form.querySelector('input[name="assessment_type"]')?.value;
					if (assessmentType) {
						localStorage.removeItem(`ennu_${assessmentType}_progress`);
						localStorage.removeItem(`ennu_${assessmentType}_data`);
					}
					
					// Clear all keys that start with 'ennu_' to be thorough
					Object.keys(localStorage).forEach(key => {
						if (key.startsWith('ennu_') && !key.includes('theme')) {
							localStorage.removeItem(key);
						}
					});
					
					console.log('✅ Successfully cleared all assessment data from localStorage');
				} catch (e) {
					console.error('Failed to clear localStorage:', e);
				}
				
				// Update auth state if provided
				if (data.data.auth_state) {
					this.updateAllFormsAuthState(data.data.auth_state);
				}
				
				// Check if we need to force a hard refresh (for newly registered users)
				const requiresRefresh = data.data.requires_refresh || data.data.newly_registered;
				
				// Single redirect method - no fallbacks
				const redirectUrl = data.data.redirect_url;
				
				// Complete the progress modal if it exists, then redirect
				if (progressModal) {
					// Mark all steps as complete
					this.updateProgressModal(progressModal, 5); // Beyond last step to show all complete
					const steps = progressModal.querySelectorAll('.progress-step');
					steps.forEach(step => {
						step.classList.add('completed');
						step.classList.remove('active');
						step.querySelector('.step-spinner').style.display = 'none';
						step.querySelector('.step-check').style.display = 'block';
					});
					progressModal.querySelector('.progress-bar-fill').style.width = '100%';
					progressModal.querySelector('.progress-message').textContent = 'Success! Redirecting to your results...';
					
					// Wait for user to see success message, then redirect
					setTimeout(() => {
						this.removeProgressModal(progressModal);
						
						// Perform redirect after modal is closed
						if (redirectUrl && redirectUrl !== 'false' && redirectUrl !== false) {
							if (requiresRefresh) {
								// Force hard refresh to ensure session is recognized
								window.location.replace(redirectUrl);
								// Also reload to ensure cache is cleared
								setTimeout(() => {
									window.location.reload(true);
								}, 100);
							} else {
								window.location.href = redirectUrl;
							}
						} else {
							this.showError(this.form, 'Assessment submitted successfully, but no results page is configured. Please contact support.');
						}
					}, 1500); // Increased from 1000ms to 1500ms to give user time to see success
				} else {
					// No modal, redirect immediately
					if (redirectUrl && redirectUrl !== 'false' && redirectUrl !== false) {
						if (requiresRefresh) {
							// Force hard refresh to ensure session is recognized
							window.location.replace(redirectUrl);
							// Also reload to ensure cache is cleared
							setTimeout(() => {
								window.location.reload(true);
							}, 100);
						} else {
							window.location.href = redirectUrl;
						}
					} else {
						this.showError(this.form, 'Assessment submitted successfully, but no results page is configured. Please contact support.');
					}
				}
			} else {
				// Remove modal on error
				if (progressModal) {
					this.removeProgressModal(progressModal);
				}
				this.showError(this.form, 'Assessment submitted successfully, but no results page is configured. Please contact support.');
			}
            } else {
                // Remove progress modal on error
                if (progressModal) {
                    this.removeProgressModal(progressModal);
                }
                
                // Handle specific error types
                if (data && data.data && data.data.action === 'login_required') {
                    const message = data.data.message || 'An account with this email already exists. Please log in to continue.';
                    const loginUrl = data.data.login_url || '/wp-login.php';
                    
                    // Show login required message with link
                    this.showLoginRequiredMessage(message, loginUrl);
                } else {
                    const message = data && data.data && data.data.message ? data.data.message : 'An unknown error occurred.';
                    this.showError(this.form, message);
                }
            }
        })
                        .catch(error => {
                    // Remove progress modal on error
                    if (progressModal) {
                        this.removeProgressModal(progressModal);
                    }
                    this.showError(this.form, 'Assessment submission failed. Please try again or contact support if the problem persists.');
                })
        .finally(() => {
            this.isSubmitting = false;
            this.form.classList.remove('loading');
        });
    }

    showLoginRequiredMessage(message, loginUrl = '/wp-login.php') {
        // Remove form and show login required message
        this.form.style.display = 'none';
        let loginDiv = document.createElement('div');
        loginDiv.className = 'ennu-login-required';
        
        loginDiv.innerHTML = `
            <div class="login-icon">🔐</div>
            <h2>Account Already Exists</h2>
            <div class="login-message">${message}</div>
            <div class="login-actions">
                <a href="${loginUrl}" class="btn btn-primary">Log In</a>
            </div>
            <p><small>If you don't remember your password, you can <a href="${loginUrl}?action=lostpassword">reset it here</a>.</small></p>
        `;
        this.form.parentNode.insertBefore(loginDiv, this.form.nextSibling);
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

