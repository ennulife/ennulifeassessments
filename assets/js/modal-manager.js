/**
 * ENNU Modal Manager
 * Centralized modal system for progress feedback across the application
 * 
 * @package ENNU_Life
 * @version 1.0.0
 */

class ENNUModalManager {
    constructor() {
        this.activeModal = null;
        this.modalConfigs = {
            current_weight_update: {
                title: 'Updating Current Weight',
                subtitle: 'Please wait while we update your current weight',
                steps: [
                    { title: 'Validating Weight', description: 'Checking your input' },
                    { title: 'Updating Profile', description: 'Saving your current weight' },
                    { title: 'Recalculating BMI', description: 'Updating health metrics' },
                    { title: 'Complete', description: 'Current weight updated successfully!' }
                ],
                duration: 4000,
                stepDuration: 1000
            },
            target_weight_update: {
                title: 'Updating Target Weight',
                subtitle: 'Please wait while we update your target weight',
                steps: [
                    { title: 'Validating Target', description: 'Checking your target weight' },
                    { title: 'Setting Goal', description: 'Saving your weight goal' },
                    { title: 'Calculating Target BMI', description: 'Updating target metrics' },
                    { title: 'Complete', description: 'Target weight updated successfully!' }
                ],
                duration: 4000,
                stepDuration: 1000
            },
            health_goals: {
                title: 'Updating Health Goals',
                subtitle: 'Applying your personalized health preferences',
                steps: [
                    { title: 'Processing Goals', description: 'Analyzing your selections' },
                    { title: 'Updating Scores', description: 'Recalculating health metrics' },
                    { title: 'Applying Boosts', description: 'Optimizing your assessments' },
                    { title: 'Complete', description: 'Goals updated successfully!' }
                ],
                duration: 5000,
                stepDuration: 1250
            },
            assessment_submission: {
                title: 'Processing Your Assessment',
                subtitle: 'Creating your personalized health profile',
                steps: [
                    { title: 'Saving Responses', description: 'Recording your assessment data' },
                    { title: 'Calculating Scores', description: 'Analyzing your health metrics' },
                    { title: 'Generating Insights', description: 'Creating personalized recommendations' },
                    { title: 'Complete', description: 'Assessment submitted successfully!' }
                ],
                duration: 6000,
                stepDuration: 1500
            },
            labcorp_upload: {
                title: 'Processing LabCorp Results',
                subtitle: 'Importing your biomarker data',
                steps: [
                    { title: 'Reading PDF', description: 'Extracting lab data' },
                    { title: 'Validating Biomarkers', description: 'Checking values and ranges' },
                    { title: 'Updating Profile', description: 'Saving biomarker data' },
                    { title: 'Recalculating Scores', description: 'Adjusting health metrics' },
                    { title: 'Complete', description: 'Lab results imported successfully!' }
                ],
                duration: 5000,
                stepDuration: 1000
            }
        };
    }

    /**
     * Show a progress modal with animated steps
     * @param {string} type - Type of modal (weight_update, health_goals, etc.)
     * @param {function} onComplete - Callback when modal completes
     * @param {object} customConfig - Optional custom configuration
     */
    showModal(type, onComplete = null, customConfig = null) {
        console.log('ENNUModalManager.showModal called with type:', type);
        
        // Get configuration
        const config = customConfig || this.modalConfigs[type];
        if (!config) {
            console.error('Modal type not found:', type);
            return;
        }
        
        console.log('Modal config:', config);

        // Close any existing modal
        this.closeModal();

        // Create modal
        this.activeModal = this.createModal(config);
        console.log('Modal created:', this.activeModal);
        
        document.body.appendChild(this.activeModal);
        console.log('Modal appended to body');

        // Add active class after a small delay for animation
        setTimeout(() => {
            if (this.activeModal) {
                this.activeModal.classList.add('active');
                console.log('Modal activated with "active" class');
            }
        }, 50);

        // Animate through steps
        this.animateSteps(config, onComplete);
    }

    /**
     * Create the modal DOM element
     * @param {object} config - Modal configuration
     * @returns {HTMLElement} Modal element
     */
    createModal(config) {
        const modal = document.createElement('div');
        modal.className = 'ennu-progress-modal';
        
        // Get plugin URL for logo - try multiple sources
        let pluginUrl = '';
        if (typeof ennu_ajax !== 'undefined' && ennu_ajax.plugin_url) {
            pluginUrl = ennu_ajax.plugin_url;
        } else if (typeof ennuDashboard !== 'undefined' && ennuDashboard.plugin_url) {
            pluginUrl = ennuDashboard.plugin_url;
        } else if (typeof ennuHealthGoalsAjax !== 'undefined' && ennuHealthGoalsAjax.plugin_url) {
            pluginUrl = ennuHealthGoalsAjax.plugin_url;
        } else if (typeof ENNU_LIFE_PLUGIN_URL !== 'undefined') {
            pluginUrl = ENNU_LIFE_PLUGIN_URL;
        } else {
            // Fallback - try to determine from current page
            pluginUrl = '/wp-content/plugins/ennulifeassessments';
        }
        
        // Ensure no trailing slash
        pluginUrl = pluginUrl.replace(/\/$/, '');
        
        console.log('Modal using plugin URL:', pluginUrl);
        
        modal.innerHTML = `
            <div class="ennu-progress-overlay"></div>
            <div class="ennu-progress-content">
                <div class="progress-logo">
                    <img src="${pluginUrl}/assets/img/ennu-logo-black.png" alt="ENNU Life" onerror="this.style.display='none'" />
                </div>
                <div class="progress-header">
                    <h2>${config.title}</h2>
                    <p class="progress-subtitle">${config.subtitle}</p>
                </div>
                
                <div class="progress-steps">
                    ${config.steps.map((step, index) => `
                        <div class="progress-step${index === 0 ? ' active' : ''}" data-step="${index + 1}">
                            <div class="step-icon">
                                <svg class="step-spinner" viewBox="0 0 50 50">
                                    <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                                </svg>
                                <svg class="step-check" style="display:none;" viewBox="0 0 24 24">
                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="currentColor"/>
                                </svg>
                            </div>
                            <div class="step-text">
                                <h3>${step.title}</h3>
                                <p>${step.description}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Animate through the modal steps
     * @param {object} config - Modal configuration
     * @param {function} onComplete - Callback when complete
     */
    animateSteps(config, onComplete) {
        let currentStep = 0;
        const steps = this.activeModal.querySelectorAll('.progress-step');
        const stepDuration = config.stepDuration || (config.duration / config.steps.length);

        const interval = setInterval(() => {
            if (currentStep > 0) {
                // Mark previous step as complete
                const prevStep = steps[currentStep - 1];
                if (prevStep) {
                    prevStep.classList.remove('active');
                    prevStep.classList.add('completed');
                    
                    // Show checkmark
                    const spinner = prevStep.querySelector('.step-spinner');
                    const check = prevStep.querySelector('.step-check');
                    if (spinner) spinner.style.display = 'none';
                    if (check) check.style.display = 'block';
                }
            }

            // Move to next step
            currentStep++;

            if (currentStep < steps.length) {
                // Activate next step
                const nextStep = steps[currentStep];
                if (nextStep) {
                    nextStep.classList.add('active');
                }
            } else {
                // All steps complete
                clearInterval(interval);
                
                // Mark last step as complete
                const lastStep = steps[steps.length - 1];
                if (lastStep) {
                    lastStep.classList.remove('active');
                    lastStep.classList.add('completed');
                    
                    const spinner = lastStep.querySelector('.step-spinner');
                    const check = lastStep.querySelector('.step-check');
                    if (spinner) spinner.style.display = 'none';
                    if (check) check.style.display = 'block';
                }

                // Close modal after a short delay
                setTimeout(() => {
                    this.closeModal();
                    if (onComplete) onComplete();
                }, 800);
            }
        }, stepDuration);
    }

    /**
     * Close the active modal
     */
    closeModal() {
        if (this.activeModal) {
            this.activeModal.classList.remove('active');
            setTimeout(() => {
                if (this.activeModal && this.activeModal.parentNode) {
                    this.activeModal.parentNode.removeChild(this.activeModal);
                }
                this.activeModal = null;
            }, 600);
        }
    }

    /**
     * Show a simple success modal (single step, auto-close)
     * @param {string} message - Success message
     * @param {function} onComplete - Callback when complete
     */
    showSuccess(message, onComplete = null) {
        const config = {
            title: 'Success!',
            subtitle: message,
            steps: [
                { title: 'Complete', description: 'Your changes have been saved' }
            ],
            duration: 2000,
            stepDuration: 2000
        };
        
        this.showModal(null, onComplete, config);
    }

    /**
     * Show an error modal
     * @param {string} message - Error message
     */
    showError(message) {
        // For now, just use alert. Could enhance with custom error modal later
        alert('Error: ' + message);
    }
}

// Create global instance
window.ENNUModalManager = new ENNUModalManager();