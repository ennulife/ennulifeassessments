/**
 * ENNU Panel Configuration JavaScript
 * 
 * Handles the panel configuration interface functionality including:
 * - Panel management (create, edit, delete, duplicate)
 * - Biomarker assignment to panels
 * - Pricing configuration
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
    
    // Add New Panel functionality
    $('#add-new-panel').on('click', function() {
        $('#panel-creation-form').show();
        $('#panel_id_field').val(''); // Clear for new panel
        $('#panel_name').val('');
        $('#panel_description').val('');
        $('#panel_category').val('core');
        $('#base_price').val('');
        $('#member_price').val('');
        $('#currency').val('USD');
        $('#panel_status').val('draft');
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#panel-creation-form').offset().top - 50
        }, 500);
    });
    
    // Panel selector functionality
    $('#panel_selector').on('change', function() {
        var selectedPanel = $(this).val();
        
        if (selectedPanel) {
            // Show the biomarker assignment interface
            $('#biomarker-assignment-interface').show();
            
            // Set the hidden panel field
            $('#assignment_panel_id').val(selectedPanel);
            
            // Load existing panel data
            loadPanelBiomarkers(selectedPanel);
        } else {
            // Hide the biomarker assignment interface
            $('#biomarker-assignment-interface').hide();
        }
    });
    
    // Load panel biomarkers
    function loadPanelBiomarkers(panelId) {
        // AJAX call to get existing panel data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_biomarker_panel',
                panel_id: panelId,
                nonce: ennu_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    populateBiomarkerAssignment(response.data);
                } else {
                    // Clear all checkboxes for new panel
                    $('input[name="panel_biomarkers[]"]').prop('checked', false);
                }
            },
            error: function() {
                $('input[name="panel_biomarkers[]"]').prop('checked', false);
            }
        });
    }
    
    // Populate biomarker assignment form
    function populateBiomarkerAssignment(panelData) {
        // Clear all checkboxes first
        $('input[name="panel_biomarkers[]"]').prop('checked', false);
        
        // Check the biomarkers that are assigned to this panel
        if (panelData.biomarkers && Array.isArray(panelData.biomarkers)) {
            panelData.biomarkers.forEach(function(biomarker) {
                $('#biomarker_' + biomarker).prop('checked', true);
            });
        }
    }
    
    // Edit panel functionality
    $('.edit-panel').on('click', function() {
        var panelId = $(this).data('panel-id');
        
        // AJAX call to get panel data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_biomarker_panel',
                panel_id: panelId,
                nonce: ennu_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    populatePanelForm(response.data, panelId);
                    $('#panel-creation-form').show();
                    
                    // Scroll to form
                    $('html, body').animate({
                        scrollTop: $('#panel-creation-form').offset().top - 50
                    }, 500);
                }
            },
            error: function() {
                alert('Error loading panel data');
            }
        });
    });
    
    // Populate panel form with existing data
    function populatePanelForm(panelData, panelId) {
        $('#panel_id_field').val(panelId);
        $('#panel_name').val(panelData.name || '');
        $('#panel_description').val(panelData.description || '');
        $('#panel_category').val(panelData.category || 'core');
        $('#base_price').val(panelData.pricing ? panelData.pricing.base_price : '');
        $('#member_price').val(panelData.pricing ? panelData.pricing.member_price : '');
        $('#currency').val(panelData.pricing ? panelData.pricing.currency : 'USD');
        $('#panel_status').val(panelData.status || 'draft');
    }
    
    // Duplicate panel functionality
    $('.duplicate-panel').on('click', function() {
        var panelId = $(this).data('panel-id');
        var newPanelId = panelId + '_copy_' + Date.now();
        
        // AJAX call to duplicate panel
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'duplicate_biomarker_panel',
                panel_id: panelId,
                new_panel_id: newPanelId,
                nonce: ennu_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Panel duplicated successfully!');
                    location.reload(); // Refresh to show new panel
                } else {
                    alert('Error duplicating panel: ' + response.data);
                }
            },
            error: function() {
                alert('Error duplicating panel');
            }
        });
    });
    
    // Delete panel functionality
    $('.delete-panel').on('click', function() {
        var panelId = $(this).data('panel-id');
        
        if (confirm('Are you sure you want to delete this panel? This action cannot be undone.')) {
            // AJAX call to delete panel
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_biomarker_panel',
                    panel_id: panelId,
                    nonce: ennu_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Panel deleted successfully!');
                        location.reload(); // Refresh to remove panel
                    } else {
                        alert('Error deleting panel: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error deleting panel');
                }
            });
        }
    });
    
    // Form validation for panel creation/editing
    $('.ennu-panel-form').on('submit', function(e) {
        var isValid = true;
        var errors = [];
        
        // Check required fields
        if (!$('#panel_name').val().trim()) {
            errors.push('Panel name is required');
            isValid = false;
        }
        
        if (!$('#panel_category').val()) {
            errors.push('Panel category is required');
            isValid = false;
        }
        
        // Check pricing
        var basePrice = parseFloat($('#base_price').val());
        var memberPrice = parseFloat($('#member_price').val());
        
        if (basePrice < 0) {
            errors.push('Base price cannot be negative');
            isValid = false;
        }
        
        if (memberPrice < 0) {
            errors.push('Member price cannot be negative');
            isValid = false;
        }
        
        if (memberPrice > basePrice) {
            errors.push('Member price cannot be higher than base price');
            isValid = false;
        }
        
        // Show errors if any
        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
        }
    });
    
    // Auto-generate panel ID from name
    $('#panel_name').on('input', function() {
        var panelName = $(this).val();
        var panelId = panelName.toLowerCase()
            .replace(/[^a-z0-9]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
        
        if (panelId && !$('#panel_id_field').val()) {
            $('#panel_id_field').val(panelId);
        }
    });
    
    // Biomarker grid functionality
    $('.ennu-biomarker-item input[type="checkbox"]').on('change', function() {
        var checkedCount = $('.ennu-biomarker-item input[type="checkbox"]:checked').length;
        updateBiomarkerCount(checkedCount);
    });
    
    function updateBiomarkerCount(count) {
        // Update any count display
        $('.biomarker-count').text(count + ' biomarkers selected');
    }
    
    // Initialize biomarker count
    updateBiomarkerCount(0);
    
    // Pricing calculator
    $('#base_price, #member_price').on('input', function() {
        var basePrice = parseFloat($('#base_price').val()) || 0;
        var memberPrice = parseFloat($('#member_price').val()) || 0;
        var currency = $('#currency').val();
        
        if (basePrice > 0 && memberPrice > 0) {
            var savings = basePrice - memberPrice;
            var savingsPercent = ((savings / basePrice) * 100).toFixed(1);
            
            $('.member-savings').text(currency + ' ' + savings.toFixed(2) + ' (' + savingsPercent + '% savings)');
        }
    });
    
    // Panel status indicators
    function updateStatusIndicators() {
        $('.ennu-status-active').addClass('status-active');
        $('.ennu-status-draft').addClass('status-draft');
        $('.ennu-status-archived').addClass('status-archived');
    }
    
    // Initialize status indicators
    updateStatusIndicators();
    
    // Bulk operations
    $('#select-all-biomarkers').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.ennu-biomarker-item input[type="checkbox"]').prop('checked', isChecked);
        updateBiomarkerCount(isChecked ? $('.ennu-biomarker-item input[type="checkbox"]').length : 0);
    });
    
    // Search functionality for biomarkers
    $('#biomarker-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $('.ennu-biomarker-item').each(function() {
            var biomarkerName = $(this).find('label strong').text().toLowerCase();
            var biomarkerCategory = $(this).find('label small').text().toLowerCase();
            
            if (biomarkerName.includes(searchTerm) || biomarkerCategory.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Category filter for biomarkers
    $('#biomarker-category-filter').on('change', function() {
        var selectedCategory = $(this).val();
        
        if (selectedCategory === 'all') {
            $('.ennu-biomarker-item').show();
        } else {
            $('.ennu-biomarker-item').each(function() {
                var biomarkerCategory = $(this).find('label small').text().toLowerCase();
                if (biomarkerCategory.includes(selectedCategory.toLowerCase())) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
    
    // Auto-save functionality
    var autoSaveTimer;
    $('.ennu-panel-form input, .ennu-panel-form select, .ennu-panel-form textarea').on('change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Auto-save after 3 seconds of inactivity
        }, 3000);
    });
    
    // Initialize tooltips and help text
    $('.ennu-help-text').each(function() {
        $(this).tooltip({
            position: { my: 'left+5 center', at: 'right center' }
        });
    });
    
}); 