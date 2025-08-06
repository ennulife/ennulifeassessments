/**
 * ENNU Life Assessments - Auto-Save JavaScript
 * 
 * Handles automatic form saving and progress persistence
 * 
 * @package ENNU Life Assessments
 * @since 3.37.14
 */

(function($) {
    'use strict';
    
    // Auto-save configuration
    const AUTOSAVE_INTERVAL = 30000; // 30 seconds
    const STORAGE_KEY_PREFIX = 'ennu_assessment_';
    
    // Initialize auto-save system
    $(document).ready(function() {
        const $form = $('.ennu-assessment-form');
        
        if ($form.length === 0) {
            return; // No assessment form on this page
        }
        
        // Get assessment type from form
        const assessmentType = $form.data('assessment-type') || $form.find('input[name="assessment_type"]').val();
        const userId = $form.data('user-id') || ennuAutoSave.userId || 0;
        
        if (!assessmentType) {
            console.warn('ENNU Auto-Save: Assessment type not found');
            return;
        }
        
        const storageKey = STORAGE_KEY_PREFIX + assessmentType + '_' + userId;
        
        // Restore saved data on page load
        restoreSavedData($form, storageKey);
        
        // Set up auto-save interval
        let autoSaveTimer = setInterval(function() {
            saveFormData($form, storageKey);
        }, AUTOSAVE_INTERVAL);
        
        // Save on input change (debounced)
        let saveTimeout;
        $form.on('change input', 'input, select, textarea', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                saveFormData($form, storageKey);
            }, 2000); // Save 2 seconds after user stops typing
        });
        
        // Clear saved data on successful submission
        $form.on('submit', function() {
            // Check if form is valid before clearing
            if ($form[0].checkValidity && $form[0].checkValidity()) {
                clearSavedData(storageKey);
                clearInterval(autoSaveTimer);
            }
        });
        
        // Handle AJAX form submission
        $(document).on('ennu_assessment_completed', function() {
            clearSavedData(storageKey);
            clearInterval(autoSaveTimer);
        });
        
        // Save before page unload
        $(window).on('beforeunload', function() {
            saveFormData($form, storageKey);
        });
        
        // Show auto-save indicator
        if (ennuAutoSave.showIndicator) {
            showAutoSaveIndicator($form);
        }
    });
    
    /**
     * Save form data to localStorage
     */
    function saveFormData($form, storageKey) {
        const formData = {};
        
        // Collect all form inputs
        $form.find('input, select, textarea').each(function() {
            const $input = $(this);
            const name = $input.attr('name');
            
            if (!name) return;
            
            // Handle different input types
            if ($input.attr('type') === 'radio') {
                if ($input.is(':checked')) {
                    formData[name] = $input.val();
                }
            } else if ($input.attr('type') === 'checkbox') {
                if (!formData[name]) {
                    formData[name] = [];
                }
                if ($input.is(':checked')) {
                    formData[name].push($input.val());
                }
            } else {
                formData[name] = $input.val();
            }
        });
        
        // Add timestamp
        formData._timestamp = Date.now();
        
        try {
            localStorage.setItem(storageKey, JSON.stringify(formData));
            
            // Trigger saved event
            $form.trigger('ennu:autosaved', [formData]);
            
            // Update indicator
            updateAutoSaveIndicator('saved');
            
            // Also save to server if user is logged in
            if (ennuAutoSave.userId && ennuAutoSave.ajaxUrl) {
                saveToServer(formData);
            }
        } catch (e) {
            console.error('ENNU Auto-Save: Failed to save data', e);
            updateAutoSaveIndicator('error');
        }
    }
    
    /**
     * Restore saved form data from localStorage
     */
    function restoreSavedData($form, storageKey) {
        try {
            const savedData = localStorage.getItem(storageKey);
            
            if (!savedData) return;
            
            const formData = JSON.parse(savedData);
            
            // Check if saved data is recent (within 7 days)
            const timestamp = formData._timestamp || 0;
            const maxAge = 7 * 24 * 60 * 60 * 1000; // 7 days
            
            if (Date.now() - timestamp > maxAge) {
                clearSavedData(storageKey);
                return;
            }
            
            // Ask user if they want to restore
            if (ennuAutoSave.askBeforeRestore) {
                if (!confirm('We found saved progress for this assessment. Would you like to continue where you left off?')) {
                    clearSavedData(storageKey);
                    return;
                }
            }
            
            // Restore form values
            Object.keys(formData).forEach(function(name) {
                if (name === '_timestamp') return;
                
                const value = formData[name];
                const $input = $form.find('[name="' + name + '"]');
                
                if ($input.length === 0) return;
                
                // Handle different input types
                if ($input.attr('type') === 'radio') {
                    $input.filter('[value="' + value + '"]').prop('checked', true).trigger('change');
                } else if ($input.attr('type') === 'checkbox') {
                    if (Array.isArray(value)) {
                        value.forEach(function(val) {
                            $input.filter('[value="' + val + '"]').prop('checked', true).trigger('change');
                        });
                    }
                } else {
                    $input.val(value).trigger('change');
                }
            });
            
            // Show restored message
            showRestoredMessage($form);
            
            // Trigger restored event
            $form.trigger('ennu:autorestored', [formData]);
            
        } catch (e) {
            console.error('ENNU Auto-Save: Failed to restore data', e);
        }
    }
    
    /**
     * Clear saved form data
     */
    function clearSavedData(storageKey) {
        try {
            localStorage.removeItem(storageKey);
        } catch (e) {
            console.error('ENNU Auto-Save: Failed to clear saved data', e);
        }
    }
    
    /**
     * Save form data to server via AJAX
     */
    function saveToServer(formData) {
        if (!ennuAutoSave.ajaxUrl || !ennuAutoSave.nonce) return;
        
        $.ajax({
            url: ennuAutoSave.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ennu_save_assessment_progress',
                nonce: ennuAutoSave.nonce,
                form_data: formData
            },
            success: function(response) {
                if (response.success) {
                    console.log('ENNU Auto-Save: Progress saved to server');
                }
            },
            error: function() {
                console.error('ENNU Auto-Save: Failed to save to server');
            }
        });
    }
    
    /**
     * Show auto-save indicator
     */
    function showAutoSaveIndicator($form) {
        const indicator = $('<div class="ennu-autosave-indicator">' +
            '<span class="indicator-icon"></span>' +
            '<span class="indicator-text">Auto-save enabled</span>' +
            '</div>');
        
        $form.prepend(indicator);
    }
    
    /**
     * Update auto-save indicator status
     */
    function updateAutoSaveIndicator(status) {
        const $indicator = $('.ennu-autosave-indicator');
        
        if ($indicator.length === 0) return;
        
        $indicator.removeClass('saving saved error').addClass(status);
        
        const messages = {
            saving: 'Saving...',
            saved: 'Progress saved',
            error: 'Save failed'
        };
        
        $indicator.find('.indicator-text').text(messages[status] || 'Auto-save enabled');
        
        // Reset to default after 3 seconds
        if (status === 'saved' || status === 'error') {
            setTimeout(function() {
                $indicator.removeClass(status);
                $indicator.find('.indicator-text').text('Auto-save enabled');
            }, 3000);
        }
    }
    
    /**
     * Show message when form data is restored
     */
    function showRestoredMessage($form) {
        const message = $('<div class="ennu-restored-message">' +
            '<p>Your previous progress has been restored.</p>' +
            '<button type="button" class="dismiss-message">Dismiss</button>' +
            '</div>');
        
        $form.prepend(message);
        
        message.find('.dismiss-message').on('click', function() {
            message.fadeOut(function() {
                message.remove();
            });
        });
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            message.fadeOut(function() {
                message.remove();
            });
        }, 5000);
    }
    
})(jQuery);