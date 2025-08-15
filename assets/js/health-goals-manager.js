/**
 * ENNU Life Health Goals Manager
 *
 * Handles interactive health goals functionality on the user dashboard,
 * including AJAX updates and UI feedback.
 *
 * @package ENNU_Life
 * @version 64.6.0
 */

jQuery(document).ready(function($) {

    // Enhanced debug logging
    console.log('Health Goals Manager: Starting initialization...');
    console.log('Health Goals Manager: Looking for .health-goals-grid elements...');
    
    // Check if the health goals container exists on the page
    const healthGoalsGrids = $('.health-goals-grid');
    console.log('Health Goals Manager: Found', healthGoalsGrids.length, 'health-goals-grid elements');
    
    if (healthGoalsGrids.length === 0) {
        console.warn('Health Goals Manager: No .health-goals-grid found, exiting...');
        return;
    }
    
    console.log('Health Goals Manager: Initializing with', healthGoalsGrids.length, 'grid(s)');
    
    // Check if ennuHealthGoalsAjax is defined
    if (typeof ennuHealthGoalsAjax === 'undefined') {
        console.error('Health Goals Manager: ennuHealthGoalsAjax object not found - AJAX will not work!');
        return;
    }
    
    console.log('Health Goals Manager: ennuHealthGoalsAjax object found:', ennuHealthGoalsAjax);


    // --- State Management ---
    let originalGoals = new Set();
    let currentGoals = new Set();

    // Cache the initial state of the goals on page load
    $('.goal-pill.selected').each(function() {
        const goalId = $(this).data('goal-id');
        originalGoals.add(goalId);
        currentGoals.add(goalId);
    });


    // --- UI Elements ---
    const updateButton = $('.update-health-goals-btn');
    console.log('Health Goals Manager: Found update button:', updateButton.length);
    if (updateButton.length === 0) {
        console.error('Health Goals Manager: Update button not found!');
        return;
    }
    
    console.log('Health Goals Manager: Update button initial state - visible:', updateButton.is(':visible'), 'hidden class:', updateButton.hasClass('hidden'));


    const notificationArea = $('<div>', {
        id: 'ennu-notification-area',
        style: 'position: fixed; top: 20px; right: 20px; z-index: 9999;'
    });
    $('body').append(notificationArea);

    // --- Event Handlers ---

    // Handle clicks on the goal pills using event delegation
    $('.health-goals-grid').on('click', '.goal-pill', function(e) {
        e.preventDefault();
        const pill = $(this);
        const goalId = pill.data('goal-id');

        console.log('Health Goals Manager: Goal pill clicked:', goalId);

        // Toggle visual state immediately
        pill.toggleClass('selected');
        pill.addClass('changed');

        // Update aria-pressed attribute for accessibility
        const isSelected = pill.hasClass('selected');
        pill.attr('aria-pressed', isSelected ? 'true' : 'false');

        console.log('Health Goals Manager: Goal', goalId, 'is now:', isSelected ? 'selected' : 'unselected');

        // Update the current set of selected goals
        if (currentGoals.has(goalId)) {
            currentGoals.delete(goalId);
        } else {
            currentGoals.add(goalId);
        }

        // Check if the current selection is different from the original
        checkForChanges();
    });

    // Handle the "Update" button click
    updateButton.on('click', function(e) {
        e.preventDefault();
        const goalsArray = Array.from(currentGoals);
        
        
        // Show loading state
        const $btn = $(this);
        const $btnText = $btn.find('.btn-text');
        const $btnLoading = $btn.find('.btn-loading');
        
        $btn.prop('disabled', true);
        $btnText.hide();
        $btnLoading.show();

        // Perform the AJAX request
        $.ajax({
            url: ennuHealthGoalsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'ennu_update_health_goals',
                nonce: ennuHealthGoalsAjax.nonce,
                health_goals: goalsArray
            },
            success: function(response) {
                if (response.success) {
                    // Hide the loading state
                    $btn.prop('disabled', false);
                    $btnText.show();
                    $btnLoading.hide();
                    
                    // Show progress modal if available
                    if (window.ENNUModalManager) {
                        window.ENNUModalManager.showModal('health_goals', function() {
                            // Show success notification
                            showNotification(ennuHealthGoalsAjax.messages.success, 'success');
                            // Reload page after modal completes
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        });
                    } else {
                        // Fallback: show notification and reload
                        showNotification(ennuHealthGoalsAjax.messages.success, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                } else {
                    showNotification(response.data.message || ennuHealthGoalsAjax.messages.error, 'error');
                    $btn.prop('disabled', false);
                    $btnText.show();
                    $btnLoading.hide();
                }
            },
            error: function(xhr, status, error) {
                showNotification(ennuHealthGoalsAjax.messages.network_error, 'error');
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoading.hide();
            }
        });
    });

    // --- Helper Functions ---

    /**
     * Checks if the current goal selection differs from the original
     * and shows/hides the update button accordingly.
     */
    function checkForChanges() {
        const hasChanges = !setsAreEqual(originalGoals, currentGoals);
        
        console.log('Health Goals Manager: Checking for changes...');
        console.log('  - Original goals:', Array.from(originalGoals));
        console.log('  - Current goals:', Array.from(currentGoals));
        console.log('  - Has changes:', hasChanges);
        
        if (hasChanges) {
            console.log('Health Goals Manager: Changes detected, showing update button');
            updateButton.fadeIn();
        } else {
            console.log('Health Goals Manager: No changes, hiding update button');
            updateButton.fadeOut();
            $('.goal-pill.changed').removeClass('changed');
        }
    }

    /**
     * Compares two Sets for equality.
     * @param {Set} setA 
     * @param {Set} setB 
     * @returns {boolean}
     */
    function setsAreEqual(setA, setB) {
        if (setA.size !== setB.size) {
            return false;
        }
        for (const item of setA) {
            if (!setB.has(item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Displays a notification message.
     * @param {string} message The message to display.
     * @param {string} type The type of notification ('success' or 'error').
     */
    function showNotification(message, type) {
        const notification = $('<div>', {
            class: 'ennu-notification ' + type,
            text: message
        }).hide();

        notificationArea.append(notification);
        notification.fadeIn();

        // Auto-dismiss the notification after 5 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Initial check for changes
    checkForChanges();
});  