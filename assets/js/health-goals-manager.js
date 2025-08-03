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

    // Check if the health goals container exists on the page
    if ($('.health-goals-grid').length === 0) {
        console.log('ENNU Health Goals: Health goals grid not found on page');
        return;
    }

    console.log('ENNU Health Goals: Initializing health goals manager');

    // --- State Management ---
    let originalGoals = new Set();
    let currentGoals = new Set();

    // Cache the initial state of the goals on page load
    $('.goal-pill.selected').each(function() {
        const goalId = $(this).data('goal-id');
        originalGoals.add(goalId);
        currentGoals.add(goalId);
        console.log('ENNU Health Goals: Initial goal selected:', goalId);
    });

    console.log('ENNU Health Goals: Original goals:', Array.from(originalGoals));
    console.log('ENNU Health Goals: Current goals:', Array.from(currentGoals));

    // --- UI Elements ---
    const updateButton = $('.update-health-goals-btn');
    if (updateButton.length === 0) {
        console.error('ENNU Health Goals: Update button not found in template');
        return;
    }

    console.log('ENNU Health Goals: Found update button:', updateButton.length);

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

        console.log('ENNU Health Goals: Goal pill clicked:', goalId);

        // Toggle visual state immediately
        pill.toggleClass('selected');
        pill.addClass('changed');

        // Update aria-pressed attribute for accessibility
        const isSelected = pill.hasClass('selected');
        pill.attr('aria-pressed', isSelected ? 'true' : 'false');

        // Update the current set of selected goals
        if (currentGoals.has(goalId)) {
            currentGoals.delete(goalId);
            console.log('ENNU Health Goals: Goal removed:', goalId);
        } else {
            currentGoals.add(goalId);
            console.log('ENNU Health Goals: Goal added:', goalId);
        }

        console.log('ENNU Health Goals: Current goals after change:', Array.from(currentGoals));

        // Check if the current selection is different from the original
        checkForChanges();
    });

    // Handle the "Update" button click
    updateButton.on('click', function(e) {
        e.preventDefault();
        const goalsArray = Array.from(currentGoals);
        
        console.log('ENNU Health Goals: Update button clicked');
        console.log('ENNU Health Goals: Goals to save:', goalsArray);
        console.log('ENNU Health Goals: AJAX URL:', ennuHealthGoalsAjax.ajax_url);
        console.log('ENNU Health Goals: Nonce:', ennuHealthGoalsAjax.nonce);
        
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
                console.log('ENNU Health Goals: AJAX success response:', response);
                if (response.success) {
                    showNotification(ennuHealthGoalsAjax.messages.success, 'success');
                    // Reload the page after a short delay to show the updated scores
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    console.log('ENNU Health Goals: AJAX error response:', response);
                    showNotification(response.data.message || ennuHealthGoalsAjax.messages.error, 'error');
                    $btn.prop('disabled', false);
                    $btnText.show();
                    $btnLoading.hide();
                }
            },
            error: function(xhr, status, error) {
                console.log('ENNU Health Goals: AJAX error:', {xhr: xhr, status: status, error: error});
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
        console.log('ENNU Health Goals: Checking for changes - hasChanges:', hasChanges);
        console.log('ENNU Health Goals: Original goals:', Array.from(originalGoals));
        console.log('ENNU Health Goals: Current goals:', Array.from(currentGoals));
        
        if (hasChanges) {
            console.log('ENNU Health Goals: Changes detected, showing update button');
            updateButton.fadeIn();
        } else {
            console.log('ENNU Health Goals: No changes, hiding update button');
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