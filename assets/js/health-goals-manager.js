/**
 * Health Goals Interactive Manager
 * Handles user interactions with health goal pills and AJAX updates
 *
 * @package ENNU_Life
 * @version 62.1.67
 * @author The World's Greatest WordPress Developer
 */

class HealthGoalsManager {
    constructor() {
        this.originalGoals = new Set();
        this.currentGoals = new Set();
        this.updateButton = null;
        this.notificationSystem = new NotificationSystem();
        this.isUpdating = false;
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initialize());
        } else {
            this.initialize();
        }
    }
    
    initialize() {
        this.cacheOriginalGoals();
        this.setupEventListeners();
        this.createUpdateButton();
        this.addGoalAttributes();
        
        console.log('ENNU Health Goals Manager: Initialized with', this.originalGoals.size, 'original goals');
    }
    
    /**
     * Cache original goals for change detection
     */
    cacheOriginalGoals() {
        document.querySelectorAll('.goal-pill.selected').forEach(pill => {
            const goalId = this.extractGoalId(pill);
            if (goalId) {
                this.originalGoals.add(goalId);
                this.currentGoals.add(goalId);
            }
        });
    }
    
    /**
     * Extract goal ID from pill element
     */
    extractGoalId(pill) {
        // Try multiple methods to get goal ID
        if (pill.dataset.goalId) {
            return pill.dataset.goalId;
        }
        
        // Extract from text content as fallback
        const goalText = pill.querySelector('.goal-pill-text');
        if (goalText) {
            const text = goalText.textContent.trim().toLowerCase();
            return this.textToGoalId(text);
        }
        
        return null;
    }
    
    /**
     * Convert goal text to goal ID
     */
    textToGoalId(text) {
        const goalMap = {
            'weight loss': 'weight_loss',
            'muscle gain': 'strength', 
            'energy boost': 'energy',
            'better sleep': 'sleep',
            'stress reduction': 'stress',
            'skin improvement': 'aesthetics',
            'hair health': 'aesthetics',
            'hormone balance': 'hormonal_balance',
            'sexual health': 'libido',
            'menopause support': 'hormonal_balance',
            'immune boost': 'longevity',
            'cognitive function': 'cognitive_health',
            'longevity & healthy aging': 'longevity',
            'improve energy & vitality': 'energy',
            'build strength & muscle': 'strength',
            'enhance libido & sexual health': 'libido',
            'achieve & maintain healthy weight': 'weight_loss',
            'hormonal balance': 'hormonal_balance',
            'sharpen cognitive function': 'cognitive_health',
            'support heart health': 'heart_health',
            'improve hair, skin & nails': 'aesthetics',
            'improve sleep quality': 'sleep',
            'reduce stress & improve resilience': 'stress'
        };
        
        return goalMap[text] || text.replace(/[^a-z]/g, '_');
    }
    
    /**
     * Add goal ID attributes to pills
     */
    addGoalAttributes() {
        document.querySelectorAll('.goal-pill').forEach(pill => {
            if (!pill.dataset.goalId) {
                const goalId = this.extractGoalId(pill);
                if (goalId) {
                    pill.dataset.goalId = goalId;
                }
            }
        });
    }
    
    /**
     * Setup event listeners for goal pills
     */
    setupEventListeners() {
        // Use event delegation for dynamic content
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('goal-pill') || e.target.closest('.goal-pill')) {
                const pill = e.target.classList.contains('goal-pill') ? e.target : e.target.closest('.goal-pill');
                this.handleGoalPillClick(pill, e);
            }
            
            if (e.target.classList.contains('update-goals-button')) {
                e.preventDefault();
                this.handleUpdateGoalsClick();
            }
        });
        
        // Keyboard accessibility
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                if (e.target.classList.contains('goal-pill')) {
                    e.preventDefault();
                    this.handleGoalPillClick(e.target, e);
                }
                
                if (e.target.classList.contains('update-goals-button')) {
                    e.preventDefault();
                    this.handleUpdateGoalsClick();
                }
            }
        });
    }
    
    /**
     * Handle individual goal pill clicks
     */
    handleGoalPillClick(pill, event) {
        if (this.isUpdating) {
            return; // Prevent interactions during updates
        }
        
        const goalId = pill.dataset.goalId;
        if (!goalId) {
            console.warn('ENNU Health Goals: No goal ID found for pill', pill);
            return;
        }
        
        const isCurrentlySelected = pill.classList.contains('selected');
        
        // Toggle visual state immediately for responsive feel
        pill.classList.toggle('selected');
        pill.classList.add('changed');
        
        // Update current goals set
        if (isCurrentlySelected) {
            this.currentGoals.delete(goalId);
        } else {
            this.currentGoals.add(goalId);
        }
        
        // Check if changes exist
        this.checkForChanges();
        
        // Add visual feedback
        this.addPillFeedback(pill);
        
        console.log('ENNU Health Goals: Toggled goal', goalId, 'to', !isCurrentlySelected);
    }
    
    /**
     * Add visual feedback to pill interaction
     */
    addPillFeedback(pill) {
        pill.style.transform = 'scale(1.05)';
        pill.style.transition = 'transform 0.1s ease';
        
        setTimeout(() => {
            pill.style.transform = 'scale(1)';
        }, 100);
        
        // Add ripple effect
        this.createRippleEffect(pill);
    }
    
    /**
     * Create ripple effect on pill click
     */
    createRippleEffect(pill) {
        const ripple = document.createElement('span');
        ripple.className = 'goal-pill-ripple';
        
        const rect = pill.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = '50%';
        ripple.style.top = '50%';
        ripple.style.transform = 'translate(-50%, -50%) scale(0)';
        
        pill.style.position = 'relative';
        pill.appendChild(ripple);
        
        // Animate ripple
        requestAnimationFrame(() => {
            ripple.style.transform = 'translate(-50%, -50%) scale(1)';
            ripple.style.opacity = '0';
        });
        
        // Remove ripple after animation
        setTimeout(() => {
            if (ripple.parentNode) {
                ripple.parentNode.removeChild(ripple);
            }
        }, 300);
    }
    
    /**
     * Check for changes and show/hide update button
     */
    checkForChanges() {
        const hasChanges = !this.setsEqual(this.originalGoals, this.currentGoals);
        
        if (hasChanges) {
            this.showUpdateButton();
        } else {
            this.hideUpdateButton();
            this.clearChangedIndicators();
        }
    }
    
    /**
     * Compare two sets for equality
     */
    setsEqual(set1, set2) {
        if (set1.size !== set2.size) return false;
        return [...set1].every(x => set2.has(x));
    }
    
    /**
     * Create and inject update button
     */
    createUpdateButton() {
        this.updateButton = document.createElement('button');
        this.updateButton.className = 'update-goals-button';
        this.updateButton.setAttribute('type', 'button');
        this.updateButton.setAttribute('tabindex', '0');
        this.updateButton.setAttribute('aria-label', 'Update your health goals');
        this.updateButton.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
            <span>Update My Health Goals</span>
        `;
        this.updateButton.style.display = 'none';
        
        // Insert after health goals grid
        const goalsGrid = document.querySelector('.health-goals-grid');
        if (goalsGrid && goalsGrid.parentNode) {
            goalsGrid.parentNode.insertBefore(this.updateButton, goalsGrid.nextSibling);
        }
    }
    
    /**
     * Show update button with animation
     */
    showUpdateButton() {
        if (this.updateButton) {
            this.updateButton.style.display = 'flex';
            this.updateButton.style.opacity = '0';
            this.updateButton.style.transform = 'translateY(10px)';
            
            requestAnimationFrame(() => {
                this.updateButton.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
                this.updateButton.style.opacity = '1';
                this.updateButton.style.transform = 'translateY(0)';
            });
        }
    }
    
    /**
     * Hide update button with animation
     */
    hideUpdateButton() {
        if (this.updateButton) {
            this.updateButton.style.transition = 'all 0.3s ease';
            this.updateButton.style.opacity = '0';
            this.updateButton.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                this.updateButton.style.display = 'none';
            }, 300);
        }
    }
    
    /**
     * Clear changed indicators from all pills
     */
    clearChangedIndicators() {
        document.querySelectorAll('.goal-pill.changed').forEach(pill => {
            pill.classList.remove('changed');
        });
    }
    
    /**
     * Handle update goals button click
     */
    handleUpdateGoalsClick() {
        if (this.isUpdating) {
            return; // Prevent double clicks
        }
        
        this.isUpdating = true;
        const goalsArray = Array.from(this.currentGoals);
        
        console.log('ENNU Health Goals: Updating goals to:', goalsArray);
        
        // Show loading state
        this.setUpdateButtonLoading(true);
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'ennu_update_health_goals');
        
        // Check if AJAX data is available
        if (typeof ennuHealthGoalsAjax !== 'undefined') {
            formData.append('nonce', ennuHealthGoalsAjax.nonce);
            
            // Add each goal as separate form field
            goalsArray.forEach(goal => {
                formData.append('health_goals[]', goal);
            });
            
            // Send AJAX request
            fetch(ennuHealthGoalsAjax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.isUpdating = false;
                this.setUpdateButtonLoading(false);
                
                if (data.success) {
                    this.handleUpdateSuccess(data.data);
                } else {
                    this.handleUpdateError(data.data);
                }
            })
            .catch(error => {
                this.isUpdating = false;
                this.setUpdateButtonLoading(false);
                console.error('ENNU Health Goals: Network error:', error);
                this.handleUpdateError({ 
                    message: ennuHealthGoalsAjax?.messages?.network_error || 'Network error occurred' 
                });
            });
        } else {
            // Fallback if AJAX data not available
            this.isUpdating = false;
            this.setUpdateButtonLoading(false);
            this.notificationSystem.show(
                'Unable to update goals: AJAX configuration missing',
                'error'
            );
            console.error('ENNU Health Goals: ennuHealthGoalsAjax not defined');
        }
    }
    
    /**
     * Set loading state on update button
     */
    setUpdateButtonLoading(isLoading) {
        if (!this.updateButton) return;
        
        if (isLoading) {
            this.updateButton.disabled = true;
            this.updateButton.innerHTML = `
                <div class="loading-spinner" aria-hidden="true"></div>
                <span>${ennuHealthGoalsAjax?.messages?.updating || 'Updating Goals...'}</span>
            `;
            this.updateButton.classList.add('loading');
        } else {
            this.updateButton.disabled = false;
            this.updateButton.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
                <span>Update My Health Goals</span>
            `;
            this.updateButton.classList.remove('loading');
        }
    }
    
    /**
     * Handle successful update
     */
    handleUpdateSuccess(data) {
        const message = data.message || ennuHealthGoalsAjax?.messages?.success || 'Goals updated successfully!';
        this.notificationSystem.show(message, 'success');
        
        // Update original goals to match current
        this.originalGoals = new Set(this.currentGoals);
        
        // Hide update button and clear indicators
        this.hideUpdateButton();
        this.clearChangedIndicators();
        
        console.log('ENNU Health Goals: Successfully updated to:', data.goals);
        
        // Refresh page if needed for score update
        if (data.redirect_needed) {
            this.notificationSystem.show(
                'Refreshing page to update your scores...', 
                'info',
                1500
            );
            
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    }
    
    /**
     * Handle update error
     */
    handleUpdateError(data) {
        const message = data?.message || ennuHealthGoalsAjax?.messages?.error || 'Failed to update health goals';
        this.notificationSystem.show(message, 'error');
        
        console.error('ENNU Health Goals: Update failed:', data);
        
        // Revert visual changes
        this.revertVisualChanges();
    }
    
    /**
     * Revert visual changes on error
     */
    revertVisualChanges() {
        // Reset current goals to original
        this.currentGoals = new Set(this.originalGoals);
        
        // Update pill visual states
        document.querySelectorAll('.goal-pill').forEach(pill => {
            const goalId = pill.dataset.goalId;
            if (goalId) {
                const shouldBeSelected = this.originalGoals.has(goalId);
                pill.classList.toggle('selected', shouldBeSelected);
                pill.classList.remove('changed');
            }
        });
        
        // Hide update button
        this.hideUpdateButton();
    }
}

/**
 * Notification System for user feedback
 */
class NotificationSystem {
    constructor() {
        this.container = this.createContainer();
        this.notifications = new Map();
        this.nextId = 1;
    }
    
    createContainer() {
        let container = document.querySelector('.ennu-notifications-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'ennu-notifications-container';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-label', 'Notifications');
            document.body.appendChild(container);
        }
        return container;
    }
    
    show(message, type = 'info', duration = 3000) {
        const id = this.nextId++;
        const notification = this.createNotification(id, message, type);
        
        // Add to container
        this.container.appendChild(notification);
        this.notifications.set(id, notification);
        
        // Trigger show animation
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });
        
        // Auto-hide after duration
        setTimeout(() => {
            this.hide(id);
        }, duration);
        
        // Focus management for accessibility
        if (type === 'error') {
            notification.focus();
        }
        
        return id;
    }
    
    createNotification(id, message, type) {
        const notification = document.createElement('div');
        notification.className = `ennu-notification ennu-notification-${type}`;
        notification.setAttribute('role', type === 'error' ? 'alert' : 'status');
        notification.setAttribute('tabindex', '-1');
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon" aria-hidden="true">
                    ${this.getIcon(type)}
                </div>
                <div class="notification-message">${this.escapeHtml(message)}</div>
                <button class="notification-close" aria-label="Close notification" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        `;
        
        // Add event listener for close button
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.hide(id));
        
        // Add keyboard support
        notification.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hide(id);
            }
        });
        
        return notification;
    }
    
    hide(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;
        
        notification.classList.remove('show');
        notification.classList.add('hiding');
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
            this.notifications.delete(id);
        }, 300);
    }
    
    hideAll() {
        this.notifications.forEach((notification, id) => {
            this.hide(id);
        });
    }
    
    getIcon(type) {
        const icons = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>'
        };
        return icons[type] || icons.info;
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.health-goals-grid')) {
        console.log('ENNU Health Goals: Initializing manager...');
        window.ennuHealthGoalsManager = new HealthGoalsManager();
    }
}); 