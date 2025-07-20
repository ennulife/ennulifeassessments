/**
 * ENNU Assessment Form - Modern JavaScript Implementation
 * Replaces jQuery dependencies for assessment form functionality
 */

class ENNUAssessmentForm {
    constructor(formElement) {
        this.form = formElement;
        this.currentStep = 0;
        this.totalSteps = 0;
        this.responses = {};
        this.isSubmitting = false;
        
        this.init();
    }

    init() {
        this.setupForm();
        this.bindEvents();
        this.initializeProgress();
        this.loadSavedProgress();
    }

    setupForm() {
        this.steps = Array.from(this.form.querySelectorAll('.assessment-step'));
        this.totalSteps = this.steps.length;
        this.progressBar = this.form.querySelector('.progress-bar');
        this.nextButton = this.form.querySelector('.btn-next');
        this.prevButton = this.form.querySelector('.btn-prev');
        this.submitButton = this.form.querySelector('.btn-submit');
        
        if (this.totalSteps > 0) {
            this.showStep(0);
        }
    }

    bindEvents() {
        if (this.nextButton) {
            this.nextButton.addEventListener('click', this.handleNext.bind(this));
        }
        
        if (this.prevButton) {
            this.prevButton.addEventListener('click', this.handlePrev.bind(this));
        }
        
        if (this.submitButton) {
            this.submitButton.addEventListener('click', this.handleSubmit.bind(this));
        }
        
        this.form.addEventListener('change', this.handleInputChange.bind(this));
        this.form.addEventListener('input', this.debounce(this.saveProgress.bind(this), 1000));
        
        document.addEventListener('keydown', this.handleKeydown.bind(this));
    }

    handleNext(event) {
        event.preventDefault();
        
        if (this.validateCurrentStep()) {
            this.nextStep();
        }
    }

    handlePrev(event) {
        event.preventDefault();
        this.prevStep();
    }

    handleSubmit(event) {
        event.preventDefault();
        
        if (this.isSubmitting) return;
        
        if (this.validateAllSteps()) {
            this.submitAssessment();
        }
    }

    handleInputChange(event) {
        const input = event.target;
        const questionId = input.getAttribute('data-question-id');
        
        if (questionId) {
            this.responses[questionId] = this.getInputValue(input);
            this.updateStepValidation();
        }
    }

    handleKeydown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.tagName !== 'TEXTAREA') {
                event.preventDefault();
                if (this.currentStep < this.totalSteps - 1) {
                    this.handleNext(event);
                } else {
                    this.handleSubmit(event);
                }
            }
        }
    }

    nextStep() {
        if (this.currentStep < this.totalSteps - 1) {
            this.currentStep++;
            this.showStep(this.currentStep);
            this.updateProgress();
            this.updateButtons();
        }
    }

    prevStep() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateProgress();
            this.updateButtons();
        }
    }

    showStep(stepIndex) {
        this.steps.forEach((step, index) => {
            if (index === stepIndex) {
                step.classList.add('active');
                step.style.display = 'block';
                
                requestAnimationFrame(() => {
                    step.style.opacity = '1';
                    step.style.transform = 'translateX(0)';
                });
                
                const firstInput = step.querySelector('input, select, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            } else {
                step.classList.remove('active');
                step.style.opacity = '0';
                step.style.transform = index < stepIndex ? 'translateX(-20px)' : 'translateX(20px)';
                
                setTimeout(() => {
                    if (!step.classList.contains('active')) {
                        step.style.display = 'none';
                    }
                }, 300);
            }
        });
    }

    updateProgress() {
        const progress = ((this.currentStep + 1) / this.totalSteps) * 100;
        
        if (this.progressBar) {
            this.progressBar.style.width = progress + '%';
            this.progressBar.setAttribute('aria-valuenow', progress);
        }
        
        const progressText = this.form.querySelector('.progress-text');
        if (progressText) {
            progressText.textContent = `Step ${this.currentStep + 1} of ${this.totalSteps}`;
        }
    }

    updateButtons() {
        if (this.prevButton) {
            this.prevButton.style.display = this.currentStep === 0 ? 'none' : 'inline-block';
        }
        
        if (this.nextButton) {
            this.nextButton.style.display = this.currentStep === this.totalSteps - 1 ? 'none' : 'inline-block';
        }
        
        if (this.submitButton) {
            this.submitButton.style.display = this.currentStep === this.totalSteps - 1 ? 'inline-block' : 'none';
        }
    }

    validateCurrentStep() {
        const currentStepElement = this.steps[this.currentStep];
        const requiredInputs = currentStepElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredInputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    validateAllSteps() {
        let isValid = true;
        
        this.steps.forEach(step => {
            const requiredInputs = step.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                if (!this.validateInput(input)) {
                    isValid = false;
                }
            });
        });
        
        return isValid;
    }

    validateInput(input) {
        const value = this.getInputValue(input);
        let isValid = true;
        
        if (input.hasAttribute('required') && (!value || value.trim() === '')) {
            isValid = false;
        }
        
        if (input.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
        }
        
        if (input.type === 'number') {
            const min = input.getAttribute('min');
            const max = input.getAttribute('max');
            const numValue = parseFloat(value);
            
            if (min && numValue < parseFloat(min)) isValid = false;
            if (max && numValue > parseFloat(max)) isValid = false;
        }
        
        this.updateInputValidation(input, isValid);
        return isValid;
    }

    updateInputValidation(input, isValid) {
        const errorElement = input.parentNode.querySelector('.error-message');
        
        if (isValid) {
            input.classList.remove('error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        } else {
            input.classList.add('error');
            if (errorElement) {
                errorElement.style.display = 'block';
            }
        }
    }

    updateStepValidation() {
        const isValid = this.validateCurrentStep();
        
        if (this.nextButton) {
            this.nextButton.disabled = !isValid;
        }
        
        if (this.submitButton && this.currentStep === this.totalSteps - 1) {
            this.submitButton.disabled = !this.validateAllSteps();
        }
    }

    getInputValue(input) {
        if (input.type === 'checkbox') {
            return input.checked;
        } else if (input.type === 'radio') {
            const radioGroup = this.form.querySelectorAll(`input[name="${input.name}"]`);
            const checked = Array.from(radioGroup).find(radio => radio.checked);
            return checked ? checked.value : '';
        } else if (input.tagName === 'SELECT' && input.multiple) {
            return Array.from(input.selectedOptions).map(option => option.value);
        } else {
            return input.value;
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    saveProgress() {
        const assessmentType = this.form.getAttribute('data-assessment-type');
        if (!assessmentType) return;
        
        const progressData = {
            currentStep: this.currentStep,
            responses: this.responses,
            timestamp: Date.now()
        };
        
        this.makeRequest('/wp-admin/admin-ajax.php', {
            action: 'ennu_save_assessment_progress',
            assessment_type: assessmentType,
            progress_data: JSON.stringify(progressData),
            nonce: window.ennuAssessmentAjax?.nonce || ''
        })
        .then(response => {
            if (response.success) {
                this.showSaveIndicator();
            }
        })
        .catch(error => {
        });
    }

    loadSavedProgress() {
        const assessmentType = this.form.getAttribute('data-assessment-type');
        if (!assessmentType) return;
        
        this.makeRequest('/wp-admin/admin-ajax.php', {
            action: 'ennu_load_assessment_progress',
            assessment_type: assessmentType,
            nonce: window.ennuAssessmentAjax?.nonce || ''
        })
        .then(response => {
            if (response.success && response.data) {
                const progressData = JSON.parse(response.data.progress_data || '{}');
                
                if (progressData.responses) {
                    this.responses = progressData.responses;
                    this.populateFormWithResponses();
                }
                
                if (progressData.currentStep && progressData.currentStep > 0) {
                    this.currentStep = Math.min(progressData.currentStep, this.totalSteps - 1);
                    this.showStep(this.currentStep);
                    this.updateProgress();
                    this.updateButtons();
                }
            }
        })
        .catch(error => {
        });
    }

    populateFormWithResponses() {
        Object.keys(this.responses).forEach(questionId => {
            const input = this.form.querySelector(`[data-question-id="${questionId}"]`);
            if (input) {
                const value = this.responses[questionId];
                
                if (input.type === 'checkbox') {
                    input.checked = Boolean(value);
                } else if (input.type === 'radio') {
                    const radioGroup = this.form.querySelectorAll(`input[name="${input.name}"]`);
                    radioGroup.forEach(radio => {
                        radio.checked = radio.value === value;
                    });
                } else if (input.tagName === 'SELECT' && input.multiple) {
                    Array.from(input.options).forEach(option => {
                        option.selected = Array.isArray(value) && value.includes(option.value);
                    });
                } else {
                    input.value = value;
                }
            }
        });
    }

    submitAssessment() {
        this.isSubmitting = true;
        this.showLoadingState();
        
        const assessmentType = this.form.getAttribute('data-assessment-type');
        
        this.makeRequest('/wp-admin/admin-ajax.php', {
            action: 'ennu_submit_assessment',
            assessment_type: assessmentType,
            responses: JSON.stringify(this.responses),
            nonce: window.ennuAssessmentAjax?.nonce || ''
        })
        .then(response => {
            if (response.success) {
                this.showSuccessMessage();
                this.clearSavedProgress();
                
                const redirectUrl = this.form.getAttribute('data-redirect-after');
                if (redirectUrl) {
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 2000);
                }
            } else {
                throw new Error(response.data?.message || 'Submission failed');
            }
        })
        .catch(error => {
            this.showErrorMessage(error.message);
        })
        .finally(() => {
            this.isSubmitting = false;
            this.hideLoadingState();
        });
    }

    clearSavedProgress() {
        const assessmentType = this.form.getAttribute('data-assessment-type');
        if (!assessmentType) return;
        
        this.makeRequest('/wp-admin/admin-ajax.php', {
            action: 'ennu_clear_assessment_progress',
            assessment_type: assessmentType,
            nonce: window.ennuAssessmentAjax?.nonce || ''
        });
    }

    makeRequest(url, data) {
        const formData = new FormData();
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });

        return fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
    }

    showLoadingState() {
        if (this.submitButton) {
            this.submitButton.disabled = true;
            this.submitButton.textContent = 'Submitting...';
        }
    }

    hideLoadingState() {
        if (this.submitButton) {
            this.submitButton.disabled = false;
            this.submitButton.textContent = 'Submit Assessment';
        }
    }

    showSaveIndicator() {
        const indicator = this.form.querySelector('.save-indicator') || this.createSaveIndicator();
        indicator.style.display = 'block';
        indicator.style.opacity = '1';
        
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 300);
        }, 2000);
    }

    createSaveIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'save-indicator';
        indicator.textContent = 'Progress saved';
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(indicator);
        this.form.appendChild(indicator);
        return indicator;
    }

    showSuccessMessage() {
        this.showNotification('Assessment submitted successfully!', 'success');
    }

    showErrorMessage(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `ennu-notification ennu-notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 10000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
        `;
        
        document.body.appendChild(notification);
        
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }

    initializeProgress() {
        this.updateProgress();
        this.updateButtons();
    }

    debounce(func, wait) {
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
}

document.addEventListener('DOMContentLoaded', () => {
    const assessmentForms = document.querySelectorAll('.ennu-assessment-form');
    assessmentForms.forEach(form => {
        new ENNUAssessmentForm(form);
    });
});
