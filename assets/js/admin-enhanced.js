jQuery(document).ready(function($) {

    // Generic function to display admin notices
    function showAdminNotice(message, type = 'success') {
        // type can be 'success', 'warning', 'error', 'info'
        const noticeHtml = `
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `;
        // Add the notice to the top of the main content area
        $('.wrap').before(noticeHtml);

        // Make the dismiss button work
        $('.notice.is-dismissible .notice-dismiss').on('click', function() {
            $(this).closest('.notice').fadeOut(300, function() {
                $(this).remove();
            });
        });
    }

    // Function to handle AJAX requests for symptom management
    function handleSymptomAction(action, button) {
        const userId = button.data('user-id');
        const nonce = $('#ennu_assessment_nonce').val();

        // Disable button to prevent multiple clicks
        button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: action,
                user_id: userId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    showAdminNotice(response.data.message, 'success');
                    // Optional: Reload the page to see updated data
                    // location.reload();
                } else {
                    showAdminNotice(response.data.message, 'error');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showAdminNotice('An unexpected error occurred: ' + errorThrown, 'error');
            },
            complete: function() {
                // Re-enable the button with its original text
                button.prop('disabled', false).text(button.data('original-text'));
            }
        });
    }

    // --- Event Handlers ---

    // Cache original button text
    $('#ennu-update-centralized-symptoms, #ennu-populate-centralized-symptoms, #ennu-clear-symptom-history').each(function() {
        $(this).data('original-text', $(this).text());
    });
    
    // Update/Populate Centralized Symptoms
    $('#ennu-update-centralized-symptoms, #ennu-populate-centralized-symptoms').on('click', function() {
        handleSymptomAction($(this).is('#ennu-update-centralized-symptoms') ? 'ennu_update_centralized_symptoms' : 'ennu_populate_centralized_symptoms', $(this));
    });

    // Clear Symptom History
    $('#ennu-clear-symptom-history').on('click', function() {
        // Add a confirmation dialog for this destructive action
        if (window.confirm('Are you sure you want to permanently clear the symptom history for this user? This action cannot be undone.')) {
            handleSymptomAction('ennu_clear_symptom_history', $(this));
        }
    });

    // Recalculate all scores
    $('#ennu-recalculate-scores').on('click', function() {
        // Add similar handling if this button needs AJAX functionality
    });
}); 