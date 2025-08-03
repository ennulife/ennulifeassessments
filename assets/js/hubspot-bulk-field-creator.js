/**
 * HubSpot Bulk Field Creator JavaScript
 * 
 * Handles all AJAX interactions for the HubSpot Bulk Field Creator admin page
 */

// Global functions for inline script calls - must be defined before jQuery ready
window.loadCustomObjects = function() {
    console.log('Loading custom objects...');
    // This function is called from inline script but doesn't need to do anything
    // as the custom objects are loaded via AJAX when needed
};

window.loadFieldStatistics = function() {
    console.log('Loading field statistics...');
    if (typeof jQuery !== 'undefined') {
        jQuery('#refresh-all-stats').trigger('click');
    }
};

window.loadAssessments = function() {
    console.log('Loading assessments...');
    // This function is called from inline script but doesn't need to do anything
    // as the assessments are loaded via AJAX when needed
};

jQuery(document).ready(function($) {
    'use strict';

    // Test API Connection
    $('#test-connection').on('click', function() {
        var button = $(this);
        var statusDiv = $('#test-status');
        
        button.prop('disabled', true).text('Testing...');
        statusDiv.html('<div class="notice notice-info"><p>🔄 Testing HubSpot API connection...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_test_api_connection',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusDiv.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
                } else {
                    statusDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                statusDiv.html('<div class="notice notice-error"><p>❌ Connection failed. Please check your HubSpot API credentials.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Test API Connection');
            }
        });
    });

    // Get Custom Objects
    $('#get-objects').on('click', function() {
        var button = $(this);
        var resultsDiv = $('#objects-results');
        
        button.prop('disabled', true).text('Loading...');
        resultsDiv.html('<div class="notice notice-info"><p>🔄 Loading custom objects...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_get_custom_objects',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    resultsDiv.html('<div class="notice notice-success"><p>✅ Found ' + response.data.length + ' custom objects</p><pre>' + JSON.stringify(response.data, null, 2) + '</pre></div>');
                } else {
                    resultsDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                resultsDiv.html('<div class="notice notice-error"><p>❌ Failed to load custom objects.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Get Custom Objects');
            }
        });
    });

    // Import Assessment Fields
    $('#import-assessment-fields').on('click', function() {
        var button = $(this);
        var objectType = $('#assessment-object-type').val();
        var assessmentName = $('#assessment-name').val();
        var resultsDiv = $('#import-results');
        
        if (!objectType || !assessmentName) {
            alert('Please select both Object Type and Assessment Name.');
            return;
        }
        
        button.prop('disabled', true).text('Importing...');
        resultsDiv.html('<div class="notice notice-info"><p>🔄 Importing assessment fields...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_import_assessment_fields',
                object_type: objectType,
                assessment_name: assessmentName,
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    resultsDiv.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
                } else {
                    resultsDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                resultsDiv.html('<div class="notice notice-error"><p>❌ Import failed. Please try again.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Import Assessment Fields');
            }
        });
    });

    // Load Assessments
    $('#assessment-object-type').on('change', function() {
        var objectType = $(this).val();
        var assessmentSelect = $('#assessment-name');
        
        if (!objectType) {
            assessmentSelect.html('<option value="">Select Object Type first</option>');
            return;
        }
        
        assessmentSelect.html('<option value="">Loading assessments...</option>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_get_assessments',
                object_type: objectType,
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var options = '<option value="">Select Assessment</option>';
                    response.data.forEach(function(assessment) {
                        options += '<option value="' + assessment.value + '">' + assessment.label + '</option>';
                    });
                    assessmentSelect.html(options);
                } else {
                    assessmentSelect.html('<option value="">Error loading assessments</option>');
                }
            },
            error: function() {
                assessmentSelect.html('<option value="">Failed to load assessments</option>');
            }
        });
    });

    // Preview Assessment Fields
    $('#preview-assessment-fields').on('click', function() {
        var button = $(this);
        var objectType = $('#assessment-object-type').val();
        var assessmentName = $('#assessment-name').val();
        var previewDiv = $('#preview-results');
        
        if (!objectType || !assessmentName) {
            alert('Please select both Object Type and Assessment Name.');
            return;
        }
        
        button.prop('disabled', true).text('Previewing...');
        previewDiv.html('<div class="notice notice-info"><p>🔄 Generating preview...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_preview_assessment_fields',
                object_type: objectType,
                assessment_name: assessmentName,
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    previewDiv.html('<div class="notice notice-success"><p>✅ Preview generated successfully</p><pre>' + JSON.stringify(response.data, null, 2) + '</pre></div>');
                } else {
                    previewDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                previewDiv.html('<div class="notice notice-error"><p>❌ Preview failed. Please try again.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Preview Assessment Fields');
            }
        });
    });

    // Refresh Field Statistics
    $('#refresh-all-stats').on('click', function() {
        var button = $(this);
        var statsContainer = $('#field-stats-container');
        
        button.prop('disabled', true).text('Refreshing...');
        statsContainer.html('<div style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px;"><div style="font-size: 24px; font-weight: bold;">🔄</div><div>Refreshing Statistics...</div></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_get_field_statistics',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    statsContainer.html(response.data);
                } else {
                    statsContainer.html('<div style="text-align: center; padding: 15px; background: rgba(255,0,0,0.1); border-radius: 6px;"><div style="font-size: 24px; font-weight: bold;">❌</div><div>Error loading statistics</div></div>');
                }
            },
            error: function() {
                statsContainer.html('<div style="text-align: center; padding: 15px; background: rgba(255,0,0,0.1); border-radius: 6px;"><div style="font-size: 24px; font-weight: bold;">❌</div><div>Failed to load statistics</div></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('🔄 Refresh All Statistics');
            }
        });
    });

    // Test Statistics
    $('#test-stats').on('click', function() {
        var button = $(this);
        
        button.prop('disabled', true).text('Testing...');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_test_field_statistics',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Statistics test completed successfully!');
                } else {
                    alert('❌ Statistics test failed: ' + response.data);
                }
            },
            error: function() {
                alert('❌ Statistics test failed. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false).text('🧪 Test Statistics');
            }
        });
    });

    // Create Weight Loss Fields
    $('#create-weight-loss-fields').on('click', function() {
        var button = $(this);
        var statusDiv = $('#weight-loss-fields-creation-status');
        
        if (!confirm('Are you sure you want to create all Weight Loss assessment fields? This will create 22 fields in HubSpot.')) {
            return;
        }
        
        button.prop('disabled', true).text('Creating...');
        statusDiv.html('<div class="notice notice-info"><p>🔄 Creating Weight Loss fields...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_create_weight_loss_fields',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusDiv.html('<div class="notice notice-success"><p>✅ ' + response.data.message + '</p></div>');
                    // Refresh statistics after creation
                    $('#refresh-all-stats').trigger('click');
                } else {
                    statusDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                statusDiv.html('<div class="notice notice-error"><p>❌ Field creation failed. Please try again.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('🚀 Create Weight Loss Fields (22 total)');
            }
        });
    });

    // Preview Weight Loss Fields
    $('#preview-weight-loss-fields').on('click', function() {
        var button = $(this);
        var previewDiv = $('#weight-loss-fields-preview');
        var createButton = $('#create-weight-loss-fields');
        
        button.prop('disabled', true).text('Previewing...');
        previewDiv.html('<div class="notice notice-info"><p>🔄 Generating Weight Loss fields preview...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_preview_weight_loss_fields',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    previewDiv.html('<div class="notice notice-success"><p>✅ Preview generated successfully</p><pre>' + JSON.stringify(response.data, null, 2) + '</pre></div>');
                    createButton.show();
                } else {
                    previewDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                previewDiv.html('<div class="notice notice-error"><p>❌ Preview failed. Please try again.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('👁️ Preview Weight Loss Fields');
            }
        });
    });

    // Create Global Fields
    $('#create-global-fields').on('click', function() {
        var button = $(this);
        var statusDiv = $('#global-fields-creation-status');
        
        if (!confirm('Are you sure you want to create all Global Shared fields? This will create 6 fields in HubSpot.')) {
            return;
        }
        
        button.prop('disabled', true).text('Creating...');
        statusDiv.html('<div class="notice notice-info"><p>🔄 Creating Global Shared fields...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_create_global_fields',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusDiv.html('<div class="notice notice-success"><p>✅ ' + response.data.message + '</p></div>');
                    // Refresh statistics after creation
                    $('#refresh-all-stats').trigger('click');
                } else {
                    statusDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                statusDiv.html('<div class="notice notice-error"><p>❌ Field creation failed. Please try again.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('🚀 Create Global Fields (6 total)');
            }
        });
    });

    // Preview Global Fields
    $('#preview-global-fields').on('click', function() {
        var button = $(this);
        var previewDiv = $('#global-fields-preview');
        var createButton = $('#create-global-fields');
        
        button.prop('disabled', true).text('Previewing...');
        previewDiv.html('<div class="notice notice-info"><p>🔄 Generating Global Shared fields preview...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_preview_global_fields',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    previewDiv.html('<div class="notice notice-success"><p>✅ Preview generated successfully</p><pre>' + JSON.stringify(response.data, null, 2) + '</pre></div>');
                    createButton.show();
                } else {
                    previewDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                previewDiv.html('<div class="notice notice-error"><p>❌ Preview failed. Please try again.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('👁️ Preview Global Fields');
            }
        });
    });

    // Delete Weight Loss Fields
    $('#delete-weight-loss-fields').on('click', function() {
        var button = $(this);
        var statusDiv = $('#weight-loss-fields-creation-status');
        
        if (!confirm('Are you sure you want to delete all Weight Loss assessment fields? This action cannot be undone.')) {
            return;
        }
        
        button.prop('disabled', true).text('Deleting...');
        statusDiv.html('<div class="notice notice-info"><p>🔄 Deleting Weight Loss fields...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_delete_weight_loss_fields',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusDiv.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
                    // Refresh statistics after deletion
                    $('#refresh-all-stats').trigger('click');
                } else {
                    statusDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                statusDiv.html('<div class="notice notice-error"><p>❌ Field deletion failed. Please try again.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('🗑️ Delete Weight Loss Fields');
            }
        });
    });

    // Delete Global Fields
    $('#delete-global-fields').on('click', function() {
        var button = $(this);
        var statusDiv = $('#global-fields-creation-status');
        
        if (!confirm('Are you sure you want to delete all Global Shared fields? This action cannot be undone.')) {
            return;
        }
        
        button.prop('disabled', true).text('Deleting...');
        statusDiv.html('<div class="notice notice-info"><p>🔄 Deleting Global Shared fields...</p></div>');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_delete_global_fields',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusDiv.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
                    // Refresh statistics after deletion
                    $('#refresh-all-stats').trigger('click');
                } else {
                    statusDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            },
            error: function() {
                statusDiv.html('<div class="notice notice-error"><p>❌ Field deletion failed. Please try again.</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text('🗑️ Delete Global Fields');
            }
        });
    });

    // Save Sync Settings
    $('#save-sync-settings').on('click', function() {
        var button = $(this);
        var syncEnabled = $('#sync-enabled').is(':checked');
        
        button.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_save_sync_settings',
                sync_enabled: syncEnabled,
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Sync settings saved successfully!');
                } else {
                    alert('❌ Failed to save sync settings: ' + response.data);
                }
            },
            error: function() {
                alert('❌ Failed to save sync settings. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false).text('Save Settings');
            }
        });
    });

    // Retry Failed Syncs
    $('#retry-failed-syncs').on('click', function() {
        var button = $(this);
        
        button.prop('disabled', true).text('Retrying...');
        
        $.ajax({
            url: ennu_hubspot_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ennu_retry_failed_syncs',
                nonce: ennu_hubspot_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Failed syncs retried successfully!');
                    location.reload();
                } else {
                    alert('❌ Failed to retry syncs: ' + response.data);
                }
            },
            error: function() {
                alert('❌ Failed to retry syncs. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false).text('Retry Failed Syncs');
            }
        });
    });

    // Load initial statistics
    $('#refresh-all-stats').trigger('click');
}); 