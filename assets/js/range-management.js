/**
 * ENNU Range Management JavaScript
 * 
 * Handles the range management interface functionality including:
 * - Tab switching
 * - Biomarker selector
 * - Form validation
 * - AJAX operations
 */

jQuery(document).ready(function($) {
    console.log('ENNU Range Management JavaScript loaded');
    
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
    
    // Panel filter functionality
    $('.ennu-panel-filter-btn').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all filter buttons
        $('.ennu-panel-filter-btn').removeClass('active');
        
        // Add active class to clicked button
        $(this).addClass('active');
        
        // Get the selected panel
        var selectedPanel = $(this).data('panel');
        
        // Filter biomarker rows
        if (selectedPanel === '') {
            // Show all panels
            $('.ennu-panel-header, .ennu-biomarker-row').show();
        } else {
            // Hide all panels first
            $('.ennu-panel-header, .ennu-biomarker-row').hide();
            
            // Show only the selected panel
            $('.ennu-panel-header[data-panel="' + selectedPanel + '"]').show();
            $('.ennu-biomarker-row[data-panel="' + selectedPanel + '"]').show();
        }
        
        // Update panel count display
        updatePanelCount(selectedPanel);
    });
    
    // Update panel count display
    function updatePanelCount(selectedPanel) {
        if (selectedPanel === '') {
            var totalBiomarkers = $('.ennu-biomarker-row').length;
            console.log('Total biomarkers: ' + totalBiomarkers);
        } else {
            var panelBiomarkers = $('.ennu-biomarker-row[data-panel="' + selectedPanel + '"]').length;
            console.log('Panel ' + selectedPanel + ' biomarkers: ' + panelBiomarkers);
        }
    }
    
    // Biomarker selector functionality
    $('#biomarker-selector').on('change', function() {
        var selectedBiomarker = $(this).val();
        
        if (selectedBiomarker) {
            // Show the range editing form
            $('#range-editing-form').show();
            
            // Set the hidden biomarker field
            $('#biomarker-field').val(selectedBiomarker);
            
            // Load existing range data if available
            loadBiomarkerRange(selectedBiomarker);
        } else {
            // Hide the range editing form
            $('#range-editing-form').hide();
        }
    });
    
    // Load biomarker range data
    function loadBiomarkerRange(biomarker) {
        // AJAX call to get existing range data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_biomarker_range',
                biomarker: biomarker,
                nonce: ennu_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    populateRangeForm(response.data);
                } else {
                    // Set default values for new biomarker
                    setDefaultValues(biomarker);
                }
            },
            error: function() {
                console.log('Error loading biomarker range data');
                setDefaultValues(biomarker);
            }
        });
    }
    
    // Populate range form with existing data
    function populateRangeForm(rangeData) {
        if (rangeData.unit) {
            $('#unit').val(rangeData.unit);
        }
        
        if (rangeData.ranges && rangeData.ranges.default) {
            var defaultRange = rangeData.ranges.default;
            $('#default_min').val(defaultRange.min || '');
            $('#default_max').val(defaultRange.max || '');
            $('#optimal_min').val(defaultRange.optimal_min || '');
            $('#optimal_max').val(defaultRange.optimal_max || '');
            $('#suboptimal_min').val(defaultRange.suboptimal_min || '');
            $('#suboptimal_max').val(defaultRange.suboptimal_max || '');
            $('#poor_min').val(defaultRange.poor_min || '');
            $('#poor_max').val(defaultRange.poor_max || '');
        }
    }
    
    // Set default values for new biomarker
    function setDefaultValues(biomarker) {
        // Clear all fields
        $('#unit').val('');
        $('#default_min, #default_max').val('');
        $('#optimal_min, #optimal_max').val('');
        $('#suboptimal_min, #suboptimal_max').val('');
        $('#poor_min, #poor_max').val('');
        
        // Set default unit based on biomarker type
        var defaultUnits = {
            'glucose': 'mg/dL',
            'hba1c': '%',
            'testosterone_total': 'ng/dL',
            'cholesterol': 'mg/dL',
            'vitamin_d': 'ng/mL'
        };
        
        if (defaultUnits[biomarker]) {
            $('#unit').val(defaultUnits[biomarker]);
        }
    }
    
    // Form validation
    $('.ennu-range-form').on('submit', function(e) {
        var isValid = true;
        var errors = [];
        
        // Check required fields
        if (!$('#biomarker-field').val()) {
            errors.push('Please select a biomarker');
            isValid = false;
        }
        
        if (!$('#unit').val()) {
            errors.push('Unit is required');
            isValid = false;
        }
        
        // Check range logic
        var defaultMin = parseFloat($('#default_min').val());
        var defaultMax = parseFloat($('#default_max').val());
        var optimalMin = parseFloat($('#optimal_min').val());
        var optimalMax = parseFloat($('#optimal_max').val());
        var suboptimalMin = parseFloat($('#suboptimal_min').val());
        var suboptimalMax = parseFloat($('#suboptimal_max').val());
        var poorMin = parseFloat($('#poor_min').val());
        var poorMax = parseFloat($('#poor_max').val());
        
        // Validate range logic
        if (defaultMin >= defaultMax) {
            errors.push('Default min must be less than default max');
            isValid = false;
        }
        
        if (optimalMin >= optimalMax) {
            errors.push('Optimal min must be less than optimal max');
            isValid = false;
        }
        
        if (suboptimalMin >= suboptimalMax) {
            errors.push('Suboptimal min must be less than suboptimal max');
            isValid = false;
        }
        
        if (poorMin >= poorMax) {
            errors.push('Poor min must be less than poor max');
            isValid = false;
        }
        
        // Check that ranges don't overlap inappropriately
        if (optimalMin < defaultMin || optimalMax > defaultMax) {
            errors.push('Optimal range must be within default range');
            isValid = false;
        }
        
        if (suboptimalMin < defaultMin || suboptimalMax > defaultMax) {
            errors.push('Suboptimal range must be within default range');
            isValid = false;
        }
        
        if (poorMin < defaultMin || poorMax > defaultMax) {
            errors.push('Poor range must be within default range');
            isValid = false;
        }
        
        // Show errors if any
        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
        }
    });
    
    // Auto-save functionality (optional)
    var autoSaveTimer;
    $('.ennu-range-form input, .ennu-range-form select').on('change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Auto-save after 2 seconds of inactivity
            // This could be implemented as an AJAX call
            console.log('Auto-save triggered');
        }, 2000);
    });
    
    // Range preview functionality
    function updateRangePreview() {
        var defaultMin = $('#default_min').val();
        var defaultMax = $('#default_max').val();
        var optimalMin = $('#optimal_min').val();
        var optimalMax = $('#optimal_max').val();
        var suboptimalMin = $('#suboptimal_min').val();
        var suboptimalMax = $('#suboptimal_max').val();
        var poorMin = $('#poor_min').val();
        var poorMax = $('#poor_max').val();
        
        // Create visual range preview
        var preview = '';
        if (defaultMin && defaultMax) {
            preview += '<div class="range-preview">';
            preview += '<div class="range-bar">';
            preview += '<div class="range-segment poor" style="left: ' + ((poorMin - defaultMin) / (defaultMax - defaultMin) * 100) + '%; width: ' + ((poorMax - poorMin) / (defaultMax - defaultMin) * 100) + '%;"></div>';
            preview += '<div class="range-segment suboptimal" style="left: ' + ((suboptimalMin - defaultMin) / (defaultMax - defaultMin) * 100) + '%; width: ' + ((suboptimalMax - suboptimalMin) / (defaultMax - defaultMin) * 100) + '%;"></div>';
            preview += '<div class="range-segment optimal" style="left: ' + ((optimalMin - defaultMin) / (defaultMax - defaultMin) * 100) + '%; width: ' + ((optimalMax - optimalMin) / (defaultMax - defaultMin) * 100) + '%;"></div>';
            preview += '</div>';
            preview += '<div class="range-labels">';
            preview += '<span class="range-label">' + defaultMin + '</span>';
            preview += '<span class="range-label">' + defaultMax + '</span>';
            preview += '</div>';
            preview += '</div>';
        }
        
        $('#range-preview').html(preview);
    }
    
    // Update preview when values change
    $('.ennu-range-form input[type="number"]').on('input', updateRangePreview);
    
    // Initialize
    updateRangePreview();
    
    // Panel Filter Functionality
    $('.ennu-panel-filter-btn').on('click', function(e) {
        e.preventDefault();
        
        console.log('Panel filter button clicked');
        
        // Remove active class from all filter buttons
        $('.ennu-panel-filter-btn').removeClass('active');
        
        // Add active class to clicked button
        $(this).addClass('active');
        
        // Get the panel to filter by
        var selectedPanel = $(this).data('panel');
        console.log('Selected panel:', selectedPanel);
        
        // Debug: Check if elements exist
        console.log('Panel headers found:', $('.ennu-panel-header').length);
        console.log('Biomarker rows found:', $('.ennu-biomarker-row').length);
        
        // Show/hide biomarker rows based on panel
        if (selectedPanel === '') {
            // Show all panels
            $('.ennu-panel-header').show();
            $('.ennu-biomarker-row').show();
            console.log('Showing all panels');
        } else {
            // Hide all panel headers first
            $('.ennu-panel-header').hide();
            $('.ennu-biomarker-row').hide();
            
            // Show only the selected panel
            var panelHeaders = $('.ennu-panel-header[data-panel="' + selectedPanel + '"]');
            var biomarkerRows = $('.ennu-biomarker-row[data-panel="' + selectedPanel + '"]');
            
            console.log('Found panel headers for ' + selectedPanel + ':', panelHeaders.length);
            console.log('Found biomarker rows for ' + selectedPanel + ':', biomarkerRows.length);
            
            panelHeaders.show();
            biomarkerRows.show();
            console.log('Showing panel:', selectedPanel);
        }
    });
    
    // Search functionality
    $('#biomarker-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm === '') {
            // Show all biomarkers when search is empty
            $('.ennu-panel-header, .ennu-biomarker-row').show();
            updateSearchResults('All biomarkers shown');
        } else {
            // Filter biomarkers based on search term
            var foundBiomarkers = [];
            var shownPanels = new Set();
            
            $('.ennu-biomarker-row').each(function() {
                var biomarkerName = $(this).find('.ennu-biomarker-name strong').text().toLowerCase();
                var biomarkerId = $(this).find('.ennu-biomarker-name small:last').text().toLowerCase();
                
                if (biomarkerName.includes(searchTerm) || biomarkerId.includes(searchTerm)) {
                    $(this).show();
                    foundBiomarkers.push(biomarkerName);
                    
                    // Show the panel header for this biomarker
                    var panelKey = $(this).attr('data-panel');
                    if (panelKey && !shownPanels.has(panelKey)) {
                        $('.ennu-panel-header[data-panel="' + panelKey + '"]').show();
                        shownPanels.add(panelKey);
                    }
                } else {
                    $(this).hide();
                }
            });
            
            // Hide panels that don't have visible biomarkers
            $('.ennu-panel-header').each(function() {
                var panelKey = $(this).attr('data-panel');
                if (!shownPanels.has(panelKey)) {
                    $(this).hide();
                }
            });
            
            updateSearchResults(foundBiomarkers.length + ' biomarkers found for "' + searchTerm + '"');
        }
    });
    
    // Clear search functionality
    $('#clear-search').on('click', function() {
        $('#biomarker-search').val('');
        $('.ennu-panel-header, .ennu-biomarker-row').show();
        $('#search-results-message').remove();
    });
    
    // Update search results display
    function updateSearchResults(message) {
        $('#search-results-message').remove();
        $('.ennu-range-management-header').after('<div id="search-results-message" class="notice notice-info"><p><strong>Search Results:</strong> ' + message + '</p></div>');
    }
    
    // Commercial Panel Filter Functionality (NEWLY ADDED)
    $('.ennu-commercial-panel-filter-btn').on('click', function(e) {
        e.preventDefault();
        
        console.log('Commercial panel filter button clicked');
        
        // Remove active class from all commercial filter buttons
        $('.ennu-commercial-panel-filter-btn').removeClass('active');
        
        // Add active class to clicked button
        $(this).addClass('active');
        
        // Get the commercial panel to filter by
        var selectedCommercialPanel = $(this).data('commercial-panel');
        console.log('Selected commercial panel:', selectedCommercialPanel);
        
        // Debug: Log all available biomarkers in the system
        var allBiomarkers = [];
        $('.ennu-biomarker-row').each(function() {
            var biomarker = $(this).attr('data-biomarker');
            var panel = $(this).attr('data-panel');
            if (biomarker) {
                allBiomarkers.push(biomarker + ' (panel: ' + panel + ')');
            }
        });
        console.log('All available biomarkers in system:', allBiomarkers);
        
        // Remove any existing filter message
        $('#commercial-filter-message').remove();
        
        // Show/hide biomarker rows based on commercial panel
        if (selectedCommercialPanel === '') {
            // Show all panels when "All Commercial Panels" is selected
            $('.ennu-panel-header').show();
            $('.ennu-biomarker-row').show();
            console.log('Showing all commercial panels');
        } else {
            // Hide all panel headers and biomarker rows first
            $('.ennu-panel-header').hide();
            $('.ennu-biomarker-row').hide();
            
            // Define commercial panel biomarker mappings (OFFICIAL 103 BIOMARKERS)
            var commercialPanelBiomarkers = {
                'Foundation Panel': [
                    // Physical Measurements (8 biomarkers)
                    'weight', 'bmi', 'body_fat_percent', 'waist_measurement', 'neck_measurement', 'blood_pressure', 'heart_rate', 'temperature',
                    // Basic Metabolic Panel (8 biomarkers)
                    'glucose', 'hba1c', 'bun', 'creatinine', 'gfr', 'bun_creatinine_ratio', 'sodium', 'potassium',
                    // Electrolytes (4 biomarkers)
                    'chloride', 'carbon_dioxide', 'calcium', 'magnesium',
                    // Protein Panel (2 biomarkers)
                    'protein', 'albumin',
                    // Liver Function (3 biomarkers)
                    'alkaline_phosphate', 'ast', 'alt',
                    // Complete Blood Count (8 biomarkers)
                    'wbc', 'rbc', 'hemoglobin', 'hematocrit', 'mcv', 'mch', 'mchc', 'rdw', 'platelets',
                    // Lipid Panel (5 biomarkers)
                    'cholesterol', 'triglycerides', 'hdl', 'vldl', 'ldl',
                    // Hormones (6 biomarkers)
                    'testosterone_free', 'testosterone_total', 'lh', 'fsh', 'dhea', 'prolactin',
                    // Thyroid (3 biomarkers)
                    'vitamin_d', 'tsh', 't4', 't3',
                    // Performance (1 biomarker)
                    'igf_1'
                ],
                'Guardian Panel': [
                    'apoe_genotype', 'ptau_217', 'beta_amyloid_ratio', 'gfap'
                ],
                'Protector Panel': [
                    'tmao', 'nmr_lipoprofile', 'ferritin', 'one_five_ag'
                ],
                'Catalyst Panel': [
                    'insulin', 'glycomark', 'uric_acid', 'adiponectin'
                ],
                'Detoxifier Panel': [
                    'arsenic', 'lead', 'mercury'
                ],
                'Timekeeper Panel': [
                    'chronological_age', 'gender', 'height', 'weight', 'systolic_blood_pressure', 'diastolic_blood_pressure', 'fasting_glucose', 'hba1c'
                ],
                'Hormone Optimization Panel': [
                    'estradiol', 'progesterone', 'shbg', 'cortisol', 'free_t3', 'free_t4'
                ],
                'Cardiovascular Health Panel': [
                    'apob', 'hs_crp', 'homocysteine', 'lp_a', 'omega_3_index'
                ],
                'Longevity & Performance Panel': [
                    'telomere_length', 'nad', 'tac', 'gut_microbiota_diversity', 'mirna_486', 'creatine_kinase', 'il_6', 'grip_strength', 'il_18', 'uric_acid'
                ],
                'Cognitive & Energy Panel': [
                    'apoe_genotype', 'coq10', 'heavy_metals_panel', 'ferritin', 'folate'
                ],
                'Metabolic Optimization Panel': [
                    'fasting_insulin', 'homa_ir', 'leptin', 'ghrelin'
                ]
            };
            
            // Get biomarkers for the selected commercial panel
            var panelBiomarkers = commercialPanelBiomarkers[selectedCommercialPanel] || [];
            console.log('Panel biomarkers for ' + selectedCommercialPanel + ':', panelBiomarkers);
            
            if (panelBiomarkers.length > 0) {
                // Show only biomarkers that belong to the selected commercial panel
                var shownPanels = new Set();
                var foundBiomarkers = [];
                var missingBiomarkers = [];
                
                panelBiomarkers.forEach(function(biomarker) {
                    var biomarkerRows = $('.ennu-biomarker-row[data-biomarker="' + biomarker + '"]');
                    
                    if (biomarkerRows.length > 0) {
                        biomarkerRows.show();
                        foundBiomarkers.push(biomarker);
                        
                        // Get the panel for this biomarker and show its header
                        var panelKey = biomarkerRows.attr('data-panel');
                        if (panelKey && !shownPanels.has(panelKey)) {
                            $('.ennu-panel-header[data-panel="' + panelKey + '"]').show();
                            shownPanels.add(panelKey);
                        }
                    } else {
                        missingBiomarkers.push(biomarker);
                    }
                });
                
                console.log('Filtered to show ' + foundBiomarkers.length + ' biomarkers for ' + selectedCommercialPanel);
                console.log('Found biomarkers:', foundBiomarkers);
                console.log('Missing biomarkers:', missingBiomarkers);
                console.log('Shown panels:', Array.from(shownPanels));
                
                // Show a success message with found count
                if (foundBiomarkers.length > 0) {
                    $('.ennu-range-management-header').after('<div id="commercial-filter-message" class="notice notice-success"><p><strong>Commercial Panel Filter:</strong> Showing <strong>' + foundBiomarkers.length + '</strong> biomarkers for <strong>' + selectedCommercialPanel + '</strong></p></div>');
                }
            } else {
                // If no biomarkers found, show a message
                $('.ennu-range-management-header').after('<div id="commercial-filter-message" class="notice notice-warning"><p><strong>No Biomarkers Found:</strong> No biomarkers were found for the selected commercial panel: <strong>' + selectedCommercialPanel + '</strong></p></div>');
            }
        }
    });
    
}); console.log('ENNU Range Management JavaScript loaded');
