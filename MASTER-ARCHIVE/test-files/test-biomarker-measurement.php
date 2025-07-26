<?php
/**
 * Test Biomarker Measurement Component
 * 
 * This script tests the new biomarker measurement component implementation
 * 
 * @package ENNU_Life_Assessments
 * @version 62.2.10
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure user is logged in
if (!is_user_logged_in()) {
    wp_die('Please log in to test the biomarker measurement component.');
}

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Test data for biomarkers
$test_biomarker_data = array(
    'vitamin_d' => array(
        'value' => 18,
        'unit' => 'ng/mL',
        'name' => 'Vitamin D',
        'test_date' => date('Y-m-d H:i:s', strtotime('-30 days'))
    ),
    'testosterone' => array(
        'value' => 450,
        'unit' => 'ng/dL',
        'name' => 'Testosterone Total',
        'test_date' => date('Y-m-d H:i:s', strtotime('-15 days'))
    ),
    'cortisol' => array(
        'value' => 25,
        'unit' => 'mcg/dL',
        'name' => 'Cortisol AM',
        'test_date' => date('Y-m-d H:i:s', strtotime('-7 days'))
    ),
    'hdl' => array(
        'value' => 65,
        'unit' => 'mg/dL',
        'name' => 'HDL Cholesterol',
        'test_date' => date('Y-m-d H:i:s', strtotime('-45 days'))
    ),
    'ldl' => array(
        'value' => 120,
        'unit' => 'mg/dL',
        'name' => 'LDL Cholesterol',
        'test_date' => date('Y-m-d H:i:s', strtotime('-45 days'))
    )
);

// Test target values
$test_targets = array(
    'vitamin_d' => 30,
    'testosterone' => 600,
    'cortisol' => 20,
    'hdl' => 60,
    'ldl' => 100
);

// Save test data
update_user_meta($user_id, 'ennu_biomarker_data', $test_biomarker_data);
update_user_meta($user_id, 'ennu_doctor_targets', $test_targets);

// Add some test flags
$test_flags = array(
    'vitamin_d' => array(
        array(
            'reason' => 'Deficient levels detected - Below optimal range',
            'severity' => 'high',
            'flagged_at' => date('Y-m-d H:i:s', strtotime('-25 days'))
        )
    ),
    'testosterone' => array(
        array(
            'reason' => 'Suboptimal levels - Below optimal range',
            'severity' => 'moderate',
            'flagged_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
        )
    )
);

update_user_meta($user_id, 'ennu_biomarker_flags', $test_flags);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biomarker Measurement Component Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f0f0f;
            color: #ffffff;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .test-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .test-header h1 {
            color: #10b981;
            margin: 0 0 10px 0;
        }
        
        .test-header p {
            margin: 0;
            color: #888;
        }
        
        .biomarker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .test-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .test-info h3 {
            color: #3b82f6;
            margin: 0 0 10px 0;
        }
        
        .test-info ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .test-info li {
            margin-bottom: 5px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #10b981;
            text-decoration: none;
            padding: 10px 20px;
            border: 1px solid #10b981;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: #10b981;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <a href="<?php echo admin_url(); ?>" class="back-link">← Back to Admin</a>
        
        <div class="test-header">
            <h1>🧪 Biomarker Measurement Component Test</h1>
            <p>Testing the new biomarker measurement component with sample data</p>
        </div>
        
        <div class="test-info">
            <h3>Test Data Summary</h3>
            <ul>
                <li><strong>User:</strong> <?php echo esc_html($current_user->display_name); ?> (ID: <?php echo $user_id; ?>)</li>
                <li><strong>Biomarkers:</strong> <?php echo count($test_biomarker_data); ?> test biomarkers loaded</li>
                <li><strong>Targets:</strong> <?php echo count($test_targets); ?> target values set</li>
                <li><strong>Flags:</strong> <?php echo count($test_flags); ?> flagged biomarkers</li>
                <li><strong>Test Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
            </ul>
        </div>
        
        <div class="biomarker-grid">
            <?php
            // Test the measurement component for each biomarker
            foreach ($test_biomarker_data as $biomarker_id => $data) {
                $measurement_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data($biomarker_id, $user_id);
                echo render_biomarker_measurement($measurement_data);
            }
            ?>
        </div>
        
        <div class="test-info" style="margin-top: 30px;">
            <h3>Test Instructions</h3>
            <ul>
                <li>Click on the info icon (i) to see biomarker details</li>
                <li>Click on the flag icon (🚩) to see flag information</li>
                <li>Hover over the range bar markers to see tooltips</li>
                <li>Click anywhere on the measurement component to see details</li>
                <li>Test responsive behavior by resizing the browser window</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Include the JavaScript functionality from the main template
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Biomarker Measurement Test: DOM loaded');
            
            // Initialize biomarker measurements
            initializeBiomarkerMeasurements();
        });
        
        // Initialize biomarker measurement components
        function initializeBiomarkerMeasurements() {
            console.log('Test: Initializing biomarker measurements...');
            
            // Add click handlers for info icons
            document.querySelectorAll('.biomarker-info-icon').forEach(icon => {
                icon.addEventListener('click', function(e) {
                    e.preventDefault();
                    const measurement = this.closest('.biomarker-measurement');
                    const biomarkerId = measurement.dataset.biomarkerId;
                    showBiomarkerDetails(biomarkerId);
                });
            });
            
            // Add click handlers for flag icons
            document.querySelectorAll('.biomarker-flag-icon').forEach(icon => {
                icon.addEventListener('click', function(e) {
                    e.preventDefault();
                    const measurement = this.closest('.biomarker-measurement');
                    const biomarkerId = measurement.dataset.biomarkerId;
                    showBiomarkerFlags(biomarkerId);
                });
            });
            
            // Add hover effects for markers
            document.querySelectorAll('.biomarker-current-marker, .biomarker-target-marker').forEach(marker => {
                marker.addEventListener('mouseenter', function() {
                    this.style.transform = this.style.transform.replace('scale(1)', 'scale(1.2)');
                });
                
                marker.addEventListener('mouseleave', function() {
                    this.style.transform = this.style.transform.replace('scale(1.2)', 'scale(1)');
                });
            });
            
            // Add click handlers for measurement containers
            document.querySelectorAll('.biomarker-measurement').forEach(measurement => {
                measurement.addEventListener('click', function(e) {
                    if (e.target.closest('.biomarker-info-icon, .biomarker-flag-icon, .biomarker-current-marker, .biomarker-target-marker')) {
                        return;
                    }
                    
                    const biomarkerId = this.dataset.biomarkerId;
                    showBiomarkerDetails(biomarkerId);
                });
            });
        }
        
        // Show biomarker details modal
        function showBiomarkerDetails(biomarkerId) {
            console.log('Test: Showing details for biomarker:', biomarkerId);
            
            const modalContent = `
                <div class="biomarker-details-modal">
                    <div class="modal-header">
                        <h3>${biomarkerId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h3>
                        <button class="modal-close" onclick="closeBiomarkerModal()">&times;</button>
                    </div>
                    <div class="modal-content">
                        <p>Detailed information for ${biomarkerId} will be displayed here.</p>
                        <p>This could include:</p>
                        <ul>
                            <li>Historical trends</li>
                            <li>Health implications</li>
                            <li>Optimization recommendations</li>
                            <li>Related symptoms</li>
                        </ul>
                    </div>
                </div>
            `;
            
            showModal(modalContent);
        }
        
        // Show biomarker flags modal
        function showBiomarkerFlags(biomarkerId) {
            console.log('Test: Showing flags for biomarker:', biomarkerId);
            
            const modalContent = `
                <div class="biomarker-flags-modal">
                    <div class="modal-header">
                        <h3>Flags for ${biomarkerId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h3>
                        <button class="modal-close" onclick="closeBiomarkerModal()">&times;</button>
                    </div>
                    <div class="modal-content">
                        <p>Flagged biomarkers require attention:</p>
                        <ul>
                            <li>Review with healthcare provider</li>
                            <li>Consider lifestyle changes</li>
                            <li>Monitor for improvements</li>
                            <li>Set appropriate targets</li>
                        </ul>
                    </div>
                </div>
            `;
            
            showModal(modalContent);
        }
        
        // Generic modal functionality
        function showModal(content) {
            const existingModal = document.querySelector('.biomarker-modal-overlay');
            if (existingModal) {
                existingModal.remove();
            }
            
            const modalOverlay = document.createElement('div');
            modalOverlay.className = 'biomarker-modal-overlay';
            modalOverlay.innerHTML = content;
            
            document.body.appendChild(modalOverlay);
            
            modalOverlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeBiomarkerModal();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeBiomarkerModal();
                }
            });
            
            setTimeout(() => {
                modalOverlay.style.opacity = '1';
                modalOverlay.querySelector('.biomarker-details-modal, .biomarker-flags-modal').style.transform = 'scale(1)';
            }, 10);
        }
        
        // Close biomarker modal
        function closeBiomarkerModal() {
            const modalOverlay = document.querySelector('.biomarker-modal-overlay');
            if (modalOverlay) {
                modalOverlay.style.opacity = '0';
                modalOverlay.querySelector('.biomarker-details-modal, .biomarker-flags-modal').style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    modalOverlay.remove();
                }, 300);
            }
        }
    </script>
    
    <!-- Include the CSS from the main template -->
    <style>
        /* Biomarker Measurement Component Styles */
        .biomarker-measurement {
            margin-top: 0.75rem;
            padding: 1rem;
            background: rgba(16, 185, 129, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(16, 185, 129, 0.1);
            transition: all 0.3s ease;
        }

        .biomarker-measurement:hover {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.2);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
        }

        .biomarker-measurement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .biomarker-measurement-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
        }

        .biomarker-measurement-icons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .biomarker-flag-icon {
            color: #ef4444;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .biomarker-flag-icon:hover {
            transform: scale(1.1);
        }

        .biomarker-info-icon {
            width: 16px;
            height: 16px;
            background: rgba(16, 185, 129, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 0.5rem;
            transition: all 0.2s ease;
        }

        .biomarker-info-icon:hover {
            background: rgba(16, 185, 129, 0.3);
            transform: scale(1.1);
        }

        .biomarker-info-icon::before {
            content: "i";
            font-size: 0.7rem;
            font-weight: bold;
            color: #10b981;
        }

        .biomarker-range-container {
            position: relative;
            margin: 0.5rem 0;
        }

        .biomarker-range-bar {
            height: 8px;
            background: linear-gradient(90deg, #EF4444 0%, #F59E0B 25%, #3B82F6 50%, #1E40AF 100%);
            border-radius: 4px;
            position: relative;
            overflow: hidden;
        }

        .biomarker-range-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.25rem;
        }

        .biomarker-range-label {
            font-size: 0.7rem;
            color: #888;
            font-weight: 500;
        }

        .biomarker-current-marker {
            width: 16px;
            height: 16px;
            background: #3b82f6;
            border: 2px solid white;
            border-radius: 50%;
            position: absolute;
            top: -4px;
            transform: translateX(-50%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 2;
            transition: all 0.3s ease;
        }

        .biomarker-current-marker:hover {
            transform: translateX(-50%) scale(1.2);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .biomarker-target-marker {
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 12px solid #10b981;
            position: absolute;
            top: -2px;
            transform: translateX(-50%);
            z-index: 3;
            transition: all 0.3s ease;
        }

        .biomarker-target-marker:hover {
            transform: translateX(-50%) scale(1.1);
        }

        .biomarker-values-display {
            display: flex;
            justify-content: space-between;
            margin-top: 0.75rem;
            font-size: 0.8rem;
        }

        .biomarker-current-value,
        .biomarker-target-value {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .biomarker-value-label {
            color: #888;
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
        }

        .biomarker-value-display {
            color: #ffffff;
            font-weight: 600;
        }

        .biomarker-current-value .biomarker-value-display {
            color: #3b82f6;
        }

        .biomarker-target-value .biomarker-value-display {
            color: #10b981;
        }

        .biomarker-status-display {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
            border-left: 3px solid #10b981;
        }

        .biomarker-status-text {
            font-size: 0.8rem;
            font-weight: 500;
            color: #ffffff;
            margin: 0;
        }

        .biomarker-status-text.below-optimal {
            color: #ef4444;
            border-left-color: #ef4444;
        }

        .biomarker-status-text.optimal {
            color: #10b981;
            border-left-color: #10b981;
        }

        .biomarker-status-text.above-optimal {
            color: #f59e0b;
            border-left-color: #f59e0b;
        }

        .biomarker-health-vector {
            margin-top: 0.5rem;
            padding: 0.25rem 0.5rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            display: inline-block;
        }

        .biomarker-health-vector-text {
            font-size: 0.7rem;
            color: #10b981;
            font-weight: 500;
        }

        .biomarker-achievement {
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .biomarker-achievement-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .biomarker-achievement-icon.achieved {
            background: #10b981;
        }

        .biomarker-achievement-icon.in-progress {
            background: #f59e0b;
        }

        .biomarker-achievement-icon::before {
            font-size: 0.6rem;
            color: white;
            font-weight: bold;
        }

        .biomarker-achievement-icon.achieved::before {
            content: "✓";
        }

        .biomarker-achievement-icon.in-progress::before {
            content: "→";
        }

        .biomarker-achievement-text {
            font-size: 0.8rem;
            color: #ffffff;
            font-weight: 500;
        }

        .biomarker-override-indicator {
            margin-top: 0.5rem;
            padding: 0.25rem 0.5rem;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 4px;
            border-left: 2px solid #f59e0b;
        }

        .biomarker-override-text {
            font-size: 0.7rem;
            color: #f59e0b;
            font-weight: 500;
        }

        /* Modal Styles */
        .biomarker-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .biomarker-details-modal,
        .biomarker-flags-modal {
            background: #1a1a1a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header h3 {
            margin: 0;
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: #888;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .modal-content {
            padding: 1.5rem;
            color: #ffffff;
        }

        .modal-content p {
            margin: 0 0 1rem 0;
            line-height: 1.6;
        }

        .modal-content ul {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .modal-content li {
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .biomarker-measurement {
                padding: 0.75rem;
                margin-top: 0.5rem;
            }

            .biomarker-measurement-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .biomarker-measurement-icons {
                align-self: flex-end;
            }

            .biomarker-values-display {
                flex-direction: column;
                gap: 0.5rem;
            }

            .biomarker-current-value,
            .biomarker-target-value {
                align-items: flex-start;
            }

            .biomarker-details-modal,
            .biomarker-flags-modal {
                width: 95%;
                max-height: 90vh;
            }

            .modal-header {
                padding: 1rem;
            }

            .modal-content {
                padding: 1rem;
            }

            .modal-header h3 {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .biomarker-measurement {
                padding: 0.5rem;
            }

            .biomarker-range-bar {
                height: 6px;
            }

            .biomarker-current-marker {
                width: 14px;
                height: 14px;
                top: -4px;
            }

            .biomarker-target-marker {
                border-left: 6px solid transparent;
                border-right: 6px solid transparent;
                border-bottom: 10px solid #10b981;
                top: -2px;
            }

            .biomarker-range-labels {
                font-size: 0.6rem;
            }

            .biomarker-values-display {
                font-size: 0.75rem;
            }
        }
    </style>
</body>
</html> 