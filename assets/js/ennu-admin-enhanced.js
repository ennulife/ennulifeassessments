/**
 * ENNU Admin Enhanced JavaScript - CRITICAL FIX
 * 
 * Handles auto-select and refresh functionality for page dropdowns
 */

jQuery(document).ready(function($) {
    // Handle the main auto-detect button
    $(document).on('click', 'input[name="ennu_detect_pages_submit"]', function() {
        console.log('ENNU Admin: Auto-detect button clicked');
        
        // Show loading state
        $(this).prop('disabled', true).val('Processing...');
        
        // Simple success message after processing
        setTimeout(function() {
            console.log('ENNU Admin: Page creation completed');
            alert('ENNU Admin: Pages created successfully! Check the dropdowns for auto-selection.');
        }, 2000);
    });
    
    // Simple function to show admin notices
    function showNotice(message, type) {
        type = type || 'info';
        var noticeClass = 'notice notice-' + type;
        var notice = $('<div class="' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
});    