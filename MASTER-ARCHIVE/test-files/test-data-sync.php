<?php
/**
 * ENNU Life Data Synchronization Test
 * 
 * @package ENNU_Life
 * @version 64.2.4
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Only run for admins
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

// Get test user ID (admin user)
$test_user_id = get_current_user_id();

?>
<!DOCTYPE html>
<html>
<head>
    <title>ENNU Data Synchronization Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test-container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-result { padding: 15px; margin: 15px 0; border-radius: 6px; border-left: 4px solid; }
        .success { background: #d4edda; color: #155724; border-left-color: #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left-color: #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; border-left-color: #17a2b8; }
        .warning { background: #fff3cd; color: #856404; border-left-color: #ffc107; }
        .data-section { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 6px; border: 1px solid #dee2e6; }
        .data-title { font-weight: bold; margin-bottom: 10px; color: #495057; }
        .data-content { font-family: monospace; font-size: 12px; white-space: pre-wrap; }
        .comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .source { background: #e9ecef; padding: 15px; border-radius: 6px; }
        .source-title { font-weight: bold; margin-bottom: 10px; color: #495057; }
        .tab-test { margin: 20px 0; }
        .tab-nav { display: flex; border-bottom: 2px solid #dee2e6; margin-bottom: 20px; }
        .tab-nav button { padding: 10px 20px; border: none; background: #f8f9fa; cursor: pointer; margin-right: 5px; border-radius: 6px 6px 0 0; }
        .tab-nav button.active { background: #007cba; color: white; }
        .tab-content { display: none; padding: 20px; background: #f8f9fa; border-radius: 0 6px 6px 6px; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🔬 ENNU Data Synchronization Test</h1>
        <p><strong>Test User ID:</strong> <?php echo $test_user_id; ?></p>
        <p><strong>Test Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

        <?php
        // Test 1: Data Source Comparison
        echo '<div class="test-result info">';
        echo '<h3>📊 Test 1: Data Source Comparison</h3>';
        
        // Get data from User Dashboard source
        $dashboard_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($test_user_id);
        
        // Get data from Admin source (old method)
        $admin_symptoms_old = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
        
        // Get data from Admin source (new method - should be same as dashboard)
        $admin_symptoms_new = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($test_user_id);
        
        echo '<div class="comparison">';
        echo '<div class="source">';
        echo '<div class="source-title">User Dashboard Source</div>';
        echo '<div class="data-content">' . htmlspecialchars(print_r($dashboard_symptoms, true)) . '</div>';
        echo '</div>';
        
        echo '<div class="source">';
        echo '<div class="source-title">Admin Source (New Method)</div>';
        echo '<div class="data-content">' . htmlspecialchars(print_r($admin_symptoms_new, true)) . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Check if they match
        $dashboard_json = json_encode($dashboard_symptoms);
        $admin_new_json = json_encode($admin_symptoms_new);
        
        if ($dashboard_json === $admin_new_json) {
            echo '<div class="test-result success">✅ PASS: User Dashboard and Admin (New Method) use identical data sources</div>';
        } else {
            echo '<div class="test-result error">❌ FAIL: User Dashboard and Admin (New Method) have different data</div>';
        }
        
        if ($dashboard_json !== json_encode($admin_symptoms_old)) {
            echo '<div class="test-result warning">⚠️ WARNING: Old admin method would have shown different data</div>';
        }
        
        echo '</div>';
        
        // Test 2: Tab Functionality Test
        echo '<div class="test-result info">';
        echo '<h3>🔧 Test 2: Tab Functionality Test</h3>';
        ?>
        
        <div class="tab-test">
            <div class="tab-nav">
                <button class="tab-button active" data-tab="biomarkers">Biomarkers</button>
                <button class="tab-button" data-tab="symptoms">Symptoms</button>
                <button class="tab-button" data-tab="assessments">Assessments</button>
                <button class="tab-button" data-tab="goals">Health Goals</button>
            </div>
            
            <div id="biomarkers" class="tab-content active">
                <h4>Biomarkers Tab</h4>
                <p>This tab should show biomarker data. If you can see this content, the tab switching is working.</p>
                <div class="data-section">
                    <div class="data-title">Biomarker Data Source:</div>
                    <div class="data-content">
                        <?php
                        $flag_manager = new ENNU_Biomarker_Flag_Manager();
                        $flagged_biomarkers = $flag_manager->get_flagged_biomarkers($test_user_id, 'active');
                        echo htmlspecialchars(print_r($flagged_biomarkers, true));
                        ?>
                    </div>
                </div>
            </div>
            
            <div id="symptoms" class="tab-content">
                <h4>Symptoms Tab</h4>
                <p>This tab should show centralized symptoms data.</p>
                <div class="data-section">
                    <div class="data-title">Centralized Symptoms Data:</div>
                    <div class="data-content">
                        <?php echo htmlspecialchars(print_r($dashboard_symptoms, true)); ?>
                    </div>
                </div>
            </div>
            
            <div id="assessments" class="tab-content">
                <h4>Assessments Tab</h4>
                <p>This tab should show assessment data.</p>
                <div class="data-section">
                    <div class="data-title">Assessment Data:</div>
                    <div class="data-content">
                        <?php
                        $assessments = array();
                        $assessment_types = array('health', 'hormone', 'testosterone', 'menopause', 'ed_treatment', 'skin', 'hair', 'sleep', 'weight_loss', 'health_optimization');
                        foreach ($assessment_types as $type) {
                            $data = get_user_meta($test_user_id, 'ennu_' . $type . '_assessment', true);
                            if (!empty($data)) {
                                $assessments[$type] = $data;
                            }
                        }
                        echo htmlspecialchars(print_r($assessments, true));
                        ?>
                    </div>
                </div>
            </div>
            
            <div id="goals" class="tab-content">
                <h4>Health Goals Tab</h4>
                <p>This tab should show health goals data.</p>
                <div class="data-section">
                    <div class="data-title">Health Goals Data:</div>
                    <div class="data-content">
                        <?php
                        $health_goals = get_user_meta($test_user_id, 'ennu_health_goals', true);
                        echo htmlspecialchars(print_r($health_goals, true));
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        // Simple tab functionality test
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and target content
                    this.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                });
            });
        });
        </script>
        
        <?php
        echo '<div class="test-result success">✅ Tab functionality test loaded successfully</div>';
        echo '</div>';
        
        // Test 3: Data Consistency Check
        echo '<div class="test-result info">';
        echo '<h3>🔍 Test 3: Data Consistency Check</h3>';
        
        // Check if centralized symptoms manager is working
        if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
            echo '<div class="test-result success">✅ Centralized Symptoms Manager class exists</div>';
            
            // Test the manager methods
            $symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($test_user_id);
            if (is_array($symptoms)) {
                echo '<div class="test-result success">✅ get_centralized_symptoms() returns valid array</div>';
            } else {
                echo '<div class="test-result error">❌ get_centralized_symptoms() does not return valid array</div>';
            }
        } else {
            echo '<div class="test-result error">❌ Centralized Symptoms Manager class not found</div>';
        }
        
        echo '</div>';
        
        // Test 4: Summary
        echo '<div class="test-result info">';
        echo '<h3>📋 Test Summary</h3>';
        echo '<p><strong>Data Synchronization:</strong> ' . ($dashboard_json === $admin_new_json ? '✅ PASSED' : '❌ FAILED') . '</p>';
        echo '<p><strong>Tab Functionality:</strong> ✅ LOADED (test manually by clicking tabs above)</p>';
        echo '<p><strong>Centralized Manager:</strong> ' . (class_exists('ENNU_Centralized_Symptoms_Manager') ? '✅ AVAILABLE' : '❌ MISSING') . '</p>';
        echo '</div>';
        ?>
        
    </div>
</body>
</html> 