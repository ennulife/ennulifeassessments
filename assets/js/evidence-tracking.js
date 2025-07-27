/**
 * ENNU Evidence Tracking JavaScript
 * 
 * Handles the evidence tracking interface functionality including:
 * - Import/Export operations
 * - Conflict resolution
 * - Evidence management
 * - Data validation
 * - AJAX operations
 */

jQuery(document).ready(function($) {
    
    // Tab switching functionality
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs and content
        $('.nav-tab').removeClass('nav-tab-active');
        $('.tab-content').removeClass('active');
        
        // Add active class to clicked tab
        $(this).addClass('nav-tab-active');
        
        // Show corresponding content
        var targetTab = $(this).data('tab');
        $('#' + targetTab).addClass('active');
    });
    
    // Import/Export Tab Functionality
    
    // File upload validation
    $('#evidence_file').on('change', function() {
        var file = this.files[0];
        var maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file && file.size > maxSize) {
            alert('File size exceeds 10MB limit. Please choose a smaller file.');
            this.value = '';
            return;
        }
        
        var allowedTypes = ['application/json', 'text/csv', 'text/plain'];
        if (file && !allowedTypes.includes(file.type) && !file.name.match(/\.(json|csv)$/i)) {
            alert('Please select a valid JSON or CSV file.');
            this.value = '';
            return;
        }
        
        // Show file info
        if (file) {
            $('.file-info').remove();
            $(this).after('<p class="file-info">Selected: ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</p>');
        }
    });
    
    // Export format change handler
    $('#export_format').on('change', function() {
        var format = $(this).val();
        if (format === 'json') {
            $('.ennu-export-options label').show();
        } else {
            // For CSV, limit options
            $('.ennu-export-options label:first').show();
            $('.ennu-export-options label:not(:first)').hide();
        }
    });
    
    // Import mode change handler
    $('#import_mode').on('change', function() {
        var mode = $(this).val();
        if (mode === 'replace') {
            if (!confirm('Replace mode will overwrite all existing data. Are you sure?')) {
                $(this).val('update');
            }
        }
    });
    
    // Conflict Resolution Tab Functionality
    
    // Resolve conflict
    $('.resolve-conflict').on('click', function() {
        var conflictId = $(this).data('conflict-id');
        var conflictItem = $(this).closest('.ennu-conflict-item');
        
        // Show resolution dialog
        showConflictResolutionDialog(conflictId, conflictItem);
    });
    
    // Ignore conflict
    $('.ignore-conflict').on('click', function() {
        var conflictId = $(this).data('conflict-id');
        var conflictItem = $(this).closest('.ennu-conflict-item');
        
        if (confirm('Are you sure you want to ignore this conflict?')) {
            conflictItem.fadeOut(function() {
                $(this).remove();
                updateConflictCount();
            });
        }
    });
    
    // Evidence Management Tab Functionality
    
    // Edit evidence source
    $('.edit-source').on('click', function() {
        var sourceId = $(this).data('source-id');
        loadSourceData(sourceId);
    });
    
    // Delete evidence source
    $('.delete-source').on('click', function() {
        var sourceId = $(this).data('source-id');
        
        if (confirm('Are you sure you want to delete this evidence source?')) {
            // AJAX call to delete source
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_evidence_source',
                    source_id: sourceId,
                    nonce: ennu_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting source: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error deleting source');
                }
            });
        }
    });
    
    // Data Validation Tab Functionality
    
    // Run validation
    $('#run-validation').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('Running Validation...');
        
        // AJAX call to run validation
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'run_data_validation',
                nonce: ennu_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateValidationResults(response.data);
                } else {
                    alert('Error running validation: ' + response.data);
                }
            },
            error: function() {
                alert('Error running validation');
            },
            complete: function() {
                button.prop('disabled', false).text('Run Validation');
            }
        });
    });
    
    // Auto-refresh validation results
    function autoRefreshValidation() {
        if ($('#validation').hasClass('active')) {
            $('#run-validation').click();
        }
    }
    
    // Refresh validation every 5 minutes when tab is active
    setInterval(autoRefreshValidation, 300000);
    
    // Helper Functions
    
    function showConflictResolutionDialog(conflictId, conflictItem) {
        var dialog = $('<div class="ennu-conflict-dialog">' +
            '<h4>Resolve Conflict</h4>' +
            '<p>Choose how to resolve this conflict:</p>' +
            '<div class="ennu-resolution-options">' +
            '<label><input type="radio" name="resolution" value="keep_current"> Keep Current Values</label>' +
            '<label><input type="radio" name="resolution" value="use_default"> Use Default Values</label>' +
            '<label><input type="radio" name="resolution" value="manual"> Manual Edit</label>' +
            '</div>' +
            '<div class="ennu-dialog-actions">' +
            '<button type="button" class="button button-primary resolve-conflict-btn">Resolve</button>' +
            '<button type="button" class="button cancel-dialog">Cancel</button>' +
            '</div>' +
            '</div>');
        
        $('body').append(dialog);
        
        // Handle resolution
        $('.resolve-conflict-btn').on('click', function() {
            var resolution = $('input[name="resolution"]:checked').val();
            if (resolution) {
                resolveConflict(conflictId, resolution, conflictItem);
                dialog.remove();
            } else {
                alert('Please select a resolution option.');
            }
        });
        
        // Handle cancel
        $('.cancel-dialog').on('click', function() {
            dialog.remove();
        });
    }
    
    function resolveConflict(conflictId, resolution, conflictItem) {
        // AJAX call to resolve conflict
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'resolve_range_conflict',
                conflict_id: conflictId,
                resolution: resolution,
                nonce: ennu_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    conflictItem.fadeOut(function() {
                        $(this).remove();
                        updateConflictCount();
                    });
                } else {
                    alert('Error resolving conflict: ' + response.data);
                }
            },
            error: function() {
                alert('Error resolving conflict');
            }
        });
    }
    
    function loadSourceData(sourceId) {
        // AJAX call to get source data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_evidence_source',
                source_id: sourceId,
                nonce: ennu_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateSourceForm(response.data);
                } else {
                    alert('Error loading source data: ' + response.data);
                }
            },
            error: function() {
                alert('Error loading source data');
            }
        });
    }
    
    function populateSourceForm(sourceData) {
        $('#source_name').val(sourceData.name || '');
        $('#source_type').val(sourceData.type || 'medical_journal');
        $('#reliability_score').val(sourceData.reliability_score || 5);
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('.ennu-add-source').offset().top - 50
        }, 500);
    }
    
    function updateValidationResults(results) {
        // Update validation summary
        $('.ennu-stat-number').each(function() {
            var label = $(this).next('.ennu-stat-label').text();
            if (label.includes('Total')) {
                $(this).text(results.total_biomarkers);
            } else if (label.includes('Valid')) {
                $(this).text(results.valid_biomarkers);
            } else if (label.includes('Warnings')) {
                $(this).text(results.warnings);
            } else if (label.includes('Errors')) {
                $(this).text(results.errors);
            }
        });
        
        // Update issues list
        var issuesList = $('.ennu-validation-issues ul');
        issuesList.empty();
        
        if (results.issues && results.issues.length > 0) {
            results.issues.forEach(function(issue) {
                issuesList.append('<li class="ennu-issue-' + issue.severity + '">' +
                    '<strong>' + issue.biomarker + ':</strong> ' + issue.message +
                    '</li>');
            });
            $('.ennu-validation-issues').show();
        } else {
            $('.ennu-validation-issues').hide();
        }
    }
    
    function updateConflictCount() {
        var count = $('.ennu-conflict-item').length;
        if (count === 0) {
            $('.ennu-conflicts-list').html('<p class="ennu-no-conflicts">No conflicts detected. All biomarker ranges are consistent.</p>');
        }
    }
    
    // Form validation
    $('.ennu-import-form').on('submit', function(e) {
        var file = $('#evidence_file')[0].files[0];
        if (!file) {
            e.preventDefault();
            alert('Please select a file to import.');
            return;
        }
        
        var mode = $('#import_mode').val();
        if (mode === 'replace') {
            if (!confirm('This will replace all existing data. Are you sure?')) {
                e.preventDefault();
                return;
            }
        }
    });
    
    $('.ennu-export-form').on('submit', function(e) {
        var selectedOptions = $('input[name="export_biomarkers[]"]:checked');
        if (selectedOptions.length === 0) {
            e.preventDefault();
            alert('Please select at least one data type to export.');
            return;
        }
    });
    
    // Source form validation
    $('.ennu-source-form').on('submit', function(e) {
        var sourceName = $('#source_name').val().trim();
        var reliabilityScore = parseInt($('#reliability_score').val());
        
        if (!sourceName) {
            e.preventDefault();
            alert('Please enter a source name.');
            return;
        }
        
        if (reliabilityScore < 1 || reliabilityScore > 10) {
            e.preventDefault();
            alert('Reliability score must be between 1 and 10.');
            return;
        }
    });
    
    // Auto-save functionality
    var autoSaveTimer;
    $('.ennu-source-form input, .ennu-source-form select, .ennu-source-form textarea').on('change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Auto-save after 3 seconds of inactivity
            console.log('Auto-save triggered for source form');
        }, 3000);
    });
    
    // Progress indicators
    function showProgress(message) {
        var progress = $('<div class="ennu-progress">' +
            '<div class="ennu-progress-bar"></div>' +
            '<div class="ennu-progress-text">' + message + '</div>' +
            '</div>');
        $('body').append(progress);
    }
    
    function hideProgress() {
        $('.ennu-progress').remove();
    }
    
    // Initialize tooltips
    $('.ennu-help-text').each(function() {
        $(this).tooltip({
            position: { my: 'left+5 center', at: 'right center' }
        });
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+S to save
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('.ennu-source-form button[type="submit"]').click();
        }
        
        // Ctrl+E to export
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            $('.ennu-export-form button[type="submit"]').click();
        }
        
        // Ctrl+I to import
        if (e.ctrlKey && e.key === 'i') {
            e.preventDefault();
            $('#evidence_file').click();
        }
    });
    
    // Responsive design adjustments
    function adjustForMobile() {
        if ($(window).width() < 768) {
            $('.ennu-form-row').addClass('mobile-layout');
            $('.ennu-export-options').addClass('mobile-layout');
        } else {
            $('.ennu-form-row').removeClass('mobile-layout');
            $('.ennu-export-options').removeClass('mobile-layout');
        }
    }
    
    $(window).on('resize', adjustForMobile);
    adjustForMobile();
    
}); 