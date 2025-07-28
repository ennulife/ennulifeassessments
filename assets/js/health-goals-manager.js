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
        return;
    }

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
    const updateButton = $('<button>', {
        text: 'Update My Health Goals',
        class: 'update-goals-button',
        style: 'display: none; margin-top: 20px;'
    });
    $('.health-goals-container').append(updateButton);

    const notificationArea = $('<div>', {
        id: 'ennu-notification-area',
        style: 'position: fixed; top: 20px; right: 20px; z-index: 9999;'
    });
    $('body').append(notificationArea);

    // --- Event Handlers ---

    // Handle clicks on the goal pills using event delegation
    $('.health-goals-grid').on('click', '.goal-pill', function() {
        const pill = $(this);
        const goalId = pill.data('goal-id');

        // Toggle visual state immediately
        pill.toggleClass('selected');
        pill.addClass('changed');

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
    updateButton.on('click', function() {
        const goalsArray = Array.from(currentGoals);
        
        // Show loading state
        $(this).prop('disabled', true).text(ennuHealthGoalsAjax.messages.updating);

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
                    showNotification(ennuHealthGoalsAjax.messages.success, 'success');
                    // Reload the page after a short delay to show the updated scores
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(response.data.message || ennuHealthGoalsAjax.messages.error, 'error');
                    updateButton.prop('disabled', false).text('Update My Health Goals');
                }
            },
            error: function() {
                showNotification(ennuHealthGoalsAjax.messages.network_error, 'error');
                updateButton.prop('disabled', false).text('Update My Health Goals');
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
        if (hasChanges) {
            updateButton.fadeIn();
        } else {
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
});  