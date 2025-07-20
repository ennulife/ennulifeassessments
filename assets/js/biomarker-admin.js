jQuery(document).ready(function($) {
    
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    $('input[name="import_method"]').on('change', function() {
        if ($(this).val() === 'csv') {
            $('.csv-upload-row').show();
            $('.manual-entry-section').hide();
        } else {
            $('.csv-upload-row').hide();
            $('.manual-entry-section').show();
        }
    });
    
    $('#add-biomarker-entry').on('click', function() {
        var template = $('.biomarker-entry').first().clone();
        template.find('input, select').val('');
        $('#biomarker-entries').append(template);
    });
    
    $(document).on('click', '.remove-biomarker-entry', function() {
        if ($('.biomarker-entry').length > 1) {
            $(this).closest('.biomarker-entry').remove();
        }
    });
    
    $('#ennu-lab-import-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'ennu_import_lab_data');
        formData.append('nonce', ennuBiomarkerAdmin.nonce);
        
        var submitBtn = $(this).find('input[type="submit"]');
        var originalText = submitBtn.val();
        submitBtn.val('Importing...').prop('disabled', true);
        
        $.ajax({
            url: ennuBiomarkerAdmin.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.data.message);
                    if (response.data.errors && response.data.errors.length > 0) {
                        showMessage('error', 'Some errors occurred: ' + response.data.errors.join(', '));
                    }
                } else {
                    showMessage('error', response.data.message || 'Import failed');
                }
            },
            error: function() {
                showMessage('error', 'Network error occurred');
            },
            complete: function() {
                submitBtn.val(originalText).prop('disabled', false);
            }
        });
    });
    
    $('#targets-user-select').on('change', function() {
        var userId = $(this).val();
        if (userId) {
            loadUserBiomarkers(userId);
            $('#doctor-targets-section').show();
        } else {
            $('#doctor-targets-section').hide();
        }
    });
    
    $('#ennu-doctor-targets-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=ennu_save_doctor_targets&nonce=' + ennuBiomarkerAdmin.nonce;
        
        var submitBtn = $(this).find('input[type="submit"]');
        var originalText = submitBtn.val();
        submitBtn.val('Saving...').prop('disabled', true);
        
        $.post(ennuBiomarkerAdmin.ajaxurl, formData, function(response) {
            if (response.success) {
                showMessage('success', response.data.message);
            } else {
                showMessage('error', response.data.message || 'Save failed');
            }
        }).fail(function() {
            showMessage('error', 'Network error occurred');
        }).always(function() {
            submitBtn.val(originalText).prop('disabled', false);
        });
    });
    
    function loadUserBiomarkers(userId) {
        $('#targets-container').html('<div class="loading-spinner"></div>');
        
        $.post(ennuBiomarkerAdmin.ajaxurl, {
            action: 'ennu_get_user_biomarkers',
            user_id: userId,
            nonce: ennuBiomarkerAdmin.nonce
        }, function(response) {
            if (response.success) {
                renderTargetsForm(response.data.biomarkers, response.data.targets);
            } else {
                $('#targets-container').html('<p>Error loading biomarkers</p>');
            }
        }).fail(function() {
            $('#targets-container').html('<p>Network error occurred</p>');
        });
    }
    
    function renderTargetsForm(biomarkers, existingTargets) {
        var html = '';
        
        if (biomarkers && Object.keys(biomarkers).length > 0) {
            for (var biomarker in biomarkers) {
                var data = biomarkers[biomarker];
                var currentTarget = existingTargets[biomarker] || '';
                
                html += '<div class="target-input-group">';
                html += '<label>' + (data.name || biomarker.replace(/_/g, ' ')) + ' (' + (data.unit || '') + ')</label>';
                html += '<input type="number" step="0.01" name="targets[' + biomarker + ']" value="' + currentTarget + '" placeholder="Target value">';
                html += '<span class="current-value">Current: ' + (data.value || 'N/A') + '</span>';
                html += '</div>';
            }
        } else {
            html = '<p>No biomarker data found for this user. Import lab data first.</p>';
        }
        
        $('#targets-container').html(html);
    }
    
    function showMessage(type, message) {
        var messageClass = type === 'success' ? 'success-message' : 'error-message';
        var messageHtml = '<div class="' + messageClass + '">' + message + '</div>';
        
        $('.wrap').prepend(messageHtml);
        
        setTimeout(function() {
            $('.' + messageClass).fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
});
