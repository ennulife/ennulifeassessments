/**
 * ENNU Accessibility JavaScript
 * WCAG 2.1 AA compliance enhancements
 */

(function() {
    'use strict';
    
    const ENNUAccessibility = {
        
        init: function() {
            this.setupKeyboardNavigation();
            this.setupFocusManagement();
            this.setupAriaLiveRegions();
            this.setupScreenReaderAnnouncements();
            this.setupReducedMotion();
            this.setupHighContrast();
        },
        
        setupKeyboardNavigation: function() {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    document.body.classList.add('keyboard-navigation');
                }
                
                if (e.key === 'Escape') {
                    ENNUAccessibility.closeModals();
                }
                
                if (e.key === 'Enter' || e.key === ' ') {
                    const target = e.target;
                    if (target.getAttribute('role') === 'button' && !target.disabled) {
                        e.preventDefault();
                        target.click();
                    }
                }
            });
            
            document.addEventListener('mousedown', function() {
                document.body.classList.remove('keyboard-navigation');
            });
        },
        
        setupFocusManagement: function() {
            const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
            
            document.querySelectorAll('.ennu-modal').forEach(function(modal) {
                const focusableContent = modal.querySelectorAll(focusableElements);
                const firstFocusable = focusableContent[0];
                const lastFocusable = focusableContent[focusableContent.length - 1];
                
                modal.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        if (e.shiftKey) {
                            if (document.activeElement === firstFocusable) {
                                lastFocusable.focus();
                                e.preventDefault();
                            }
                        } else {
                            if (document.activeElement === lastFocusable) {
                                firstFocusable.focus();
                                e.preventDefault();
                            }
                        }
                    }
                });
            });
        },
        
        setupAriaLiveRegions: function() {
            if (!document.getElementById('ennu-announcements')) {
                const announcements = document.createElement('div');
                announcements.id = 'ennu-announcements';
                announcements.setAttribute('aria-live', 'polite');
                announcements.setAttribute('aria-atomic', 'true');
                announcements.className = 'sr-only';
                document.body.appendChild(announcements);
            }
            
            if (!document.getElementById('ennu-status')) {
                const status = document.createElement('div');
                status.id = 'ennu-status';
                status.setAttribute('aria-live', 'assertive');
                status.setAttribute('aria-atomic', 'true');
                status.className = 'sr-only';
                document.body.appendChild(status);
            }
        },
        
        announce: function(message, priority = 'polite') {
            const regionId = priority === 'assertive' ? 'ennu-status' : 'ennu-announcements';
            const region = document.getElementById(regionId);
            
            if (region) {
                region.textContent = '';
                setTimeout(function() {
                    region.textContent = message;
                }, 100);
            }
        },
        
        setupScreenReaderAnnouncements: function() {
            document.addEventListener('click', function(e) {
                const target = e.target;
                
                if (target.classList.contains('goal-pill')) {
                    const isSelected = target.getAttribute('aria-pressed') === 'true';
                    const goalName = target.querySelector('.goal-pill-text').textContent;
                    const message = isSelected ? 
                        ennuA11y.goalSelected.replace('%s', goalName) :
                        ennuA11y.goalDeselected.replace('%s', goalName);
                    ENNUAccessibility.announce(message);
                }
                
                if (target.classList.contains('ennu-btn') && target.classList.contains('loading')) {
                    ENNUAccessibility.announce(ennuA11y.loading, 'assertive');
                }
            });
            
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    ENNUAccessibility.announce(ennuA11y.formSubmitting, 'assertive');
                });
            });
        },
        
        setupReducedMotion: function() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.body.classList.add('reduced-motion');
                
                const style = document.createElement('style');
                style.textContent = `
                    .reduced-motion *,
                    .reduced-motion *::before,
                    .reduced-motion *::after {
                        animation-duration: 0.01ms !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: 0.01ms !important;
                        scroll-behavior: auto !important;
                    }
                `;
                document.head.appendChild(style);
            }
        },
        
        setupHighContrast: function() {
            if (window.matchMedia('(prefers-contrast: high)').matches) {
                document.body.classList.add('high-contrast');
            }
        },
        
        closeModals: function() {
            const openModals = document.querySelectorAll('.ennu-modal[aria-hidden="false"]');
            openModals.forEach(function(modal) {
                const closeButton = modal.querySelector('.ennu-modal-close');
                if (closeButton) {
                    closeButton.click();
                }
            });
        },
        
        updateProgress: function(current, total, context = '') {
            const percentage = Math.round((current / total) * 100);
            const message = context ? 
                `${context}: ${percentage}% complete, step ${current} of ${total}` :
                `Progress: ${percentage}% complete, step ${current} of ${total}`;
            
            this.announce(message);
        },
        
        validateForm: function(form) {
            const errors = [];
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    const label = form.querySelector(`label[for="${field.id}"]`);
                    const fieldName = label ? label.textContent : field.name || 'Field';
                    errors.push(`${fieldName} is required`);
                    
                    field.setAttribute('aria-invalid', 'true');
                    field.classList.add('ennu-error');
                } else {
                    field.setAttribute('aria-invalid', 'false');
                    field.classList.remove('ennu-error');
                }
            });
            
            if (errors.length > 0) {
                const errorMessage = `Form has ${errors.length} error${errors.length > 1 ? 's' : ''}: ${errors.join(', ')}`;
                this.announce(errorMessage, 'assertive');
                return false;
            }
            
            return true;
        },
        
        addTooltip: function(element, text) {
            element.setAttribute('aria-describedby', `tooltip-${Date.now()}`);
            
            element.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'ennu-tooltip';
                tooltip.id = element.getAttribute('aria-describedby');
                tooltip.textContent = text;
                tooltip.setAttribute('role', 'tooltip');
                
                document.body.appendChild(tooltip);
                
                const rect = element.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            });
            
            element.addEventListener('mouseleave', function() {
                const tooltip = document.getElementById(element.getAttribute('aria-describedby'));
                if (tooltip) {
                    tooltip.remove();
                }
            });
        }
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            ENNUAccessibility.init();
        });
    } else {
        ENNUAccessibility.init();
    }
    
    window.ENNUAccessibility = ENNUAccessibility;
    
})();
