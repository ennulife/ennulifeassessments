<?php
/**
 * Direct Biomarker Test
 * 
 * This script can be accessed directly via URL to test biomarker functionality
 * without requiring WordPress admin context.
 */

// Load WordPress
if (!defined('ABSPATH')) {
    // Try to find wp-load.php
    $wp_load_paths = [
        '../../../wp-load.php',
        '../../../../wp-load.php',
        '../../../../../wp-load.php',
        'wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('Could not load WordPress. Please ensure this file is in the correct plugin directory.');
    }
}

// Basic security check
if (!function_exists('wp_get_current_user')) {
    die('WordPress not properly loaded.');
}

echo '<!DOCTYPE html>';
echo '<html><head>';
echo '<title>Biomarker Management Test</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }';
echo '.wrap { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
echo 'h1 { color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }';
echo 'h2 { color: #333; margin-top: 30px; }';
echo '.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }';
echo '.error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }';
echo '.warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }';
echo '.info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }';
echo 'ul { margin: 10px 0; }';
echo 'li { margin: 5px 0; }';
echo '.biomarker-interface { background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-radius: 5px; margin: 20px 0; }';
echo '</style>';
echo '</head><body>';

echo '<div class="wrap">';
echo '<h1>🧪 Direct Biomarker Management Test</h1>';

// Test 1: WordPress Environment
echo '<h2>1. WordPress Environment Check</h2>';
$wp_functions = [
    'wp_get_current_user',
    'get_current_user_id',
    'class_exists',
    'method_exists'
];

$wp_ok = true;
foreach ($wp_functions as $func) {
    $exists = function_exists($func);
    echo '<p>' . ($exists ? '✅' : '❌') . ' ' . $func . '(): ' . ($exists ? 'AVAILABLE' : 'MISSING') . '</p>';
    if (!$exists) $wp_ok = false;
}

if (!$wp_ok) {
    echo '<div class="error">';
    echo '<h3>❌ WordPress Environment Issue</h3>';
    echo '<p>Essential WordPress functions are missing. Cannot proceed with testing.</p>';
    echo '</div>';
    echo '</div></body></html>';
    return;
}

// Test 2: Plugin Classes
echo '<h2>2. Plugin Classes Check</h2>';
$required_classes = [
    'ENNU_Enhanced_Admin',
    'ENNU_Biomarker_Manager',
    'ENNU_Lab_Import_Manager',
    'ENNU_Smart_Recommendation_Engine'
];

$classes_ok = true;
foreach ($required_classes as $class) {
    $exists = class_exists($class);
    echo '<p>' . ($exists ? '✅' : '❌') . ' ' . $class . ': ' . ($exists ? 'EXISTS' : 'MISSING') . '</p>';
    if (!$exists) $classes_ok = false;
}

if (!$classes_ok) {
    echo '<div class="error">';
    echo '<h3>❌ Missing Required Classes</h3>';
    echo '<p>Some required classes are missing. Please ensure the ENNU Life plugin is properly activated.</p>';
    echo '</div>';
    echo '</div></body></html>';
    return;
}

// Test 3: Admin Method Check
echo '<h2>3. Admin Method Check</h2>';
try {
    $admin = new ENNU_Enhanced_Admin();
    $has_method = method_exists($admin, 'add_biomarker_management_tab');
    echo '<p>' . ($has_method ? '✅' : '❌') . ' add_biomarker_management_tab method: ' . ($has_method ? 'EXISTS' : 'MISSING') . '</p>';
    
    if (!$has_method) {
        throw new Exception('Biomarker management method not found');
    }
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<h3>❌ Admin Method Error</h3>';
    echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
    echo '</div>';
    echo '</div></body></html>';
    return;
}

// Test 4: Biomarker Manager Test
echo '<h2>4. Biomarker Manager Test</h2>';
try {
    $biomarker_manager = new ENNU_Biomarker_Manager();
    $current_user_id = get_current_user_id();
    $user_biomarkers = $biomarker_manager->get_user_biomarkers($current_user_id);
    
    echo '<div class="success">';
    echo '<p>✅ Biomarker Manager: WORKING</p>';
    echo '<p>📊 User ID: ' . $current_user_id . '</p>';
    echo '<p>📊 Biomarkers Count: ' . count($user_biomarkers) . '</p>';
    echo '</div>';
    
    if (!empty($user_biomarkers)) {
        echo '<h3>Current Biomarkers:</h3>';
        echo '<ul>';
        foreach ($user_biomarkers as $biomarker_name => $tests) {
            $latest = end($tests);
            echo '<li><strong>' . esc_html($biomarker_name) . '</strong>: ' . esc_html($latest['value']) . ' ' . esc_html($latest['unit']) . ' (' . esc_html($latest['status']) . ')</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="info">';
        echo '<p>📝 No biomarker data found for current user.</p>';
        echo '</div>';
    }
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<h3>❌ Biomarker Manager Error</h3>';
    echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
    echo '</div>';
}

// Test 5: Smart Recommendation Engine Test
echo '<h2>5. Smart Recommendation Engine Test</h2>';
try {
    $smart_engine = new ENNU_Smart_Recommendation_Engine();
    $current_user_id = get_current_user_id();
    $recommendations = $smart_engine->get_updated_recommendations($current_user_id);
    
    echo '<div class="success">';
    echo '<p>✅ Smart Recommendation Engine: WORKING</p>';
    echo '<p>🎯 Recommendations Count: ' . count($recommendations) . '</p>';
    echo '</div>';
    
    if (!empty($recommendations)) {
        echo '<h3>Current Recommendations:</h3>';
        echo '<ul>';
        foreach (array_slice($recommendations, 0, 5) as $rec) {
            echo '<li><strong>' . esc_html($rec['biomarker']) . '</strong>: ' . esc_html($rec['reason']) . ' (' . esc_html($rec['urgency']) . ' urgency)</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="info">';
        echo '<p>📝 No recommendations at this time.</p>';
        echo '</div>';
    }
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<h3>❌ Smart Recommendation Engine Error</h3>';
    echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
    echo '</div>';
}

// Test 6: Lab Import Manager Test
echo '<h2>6. Lab Import Manager Test</h2>';
try {
    $lab_import_manager = new ENNU_Lab_Import_Manager();
    $supported_providers = $lab_import_manager->get_supported_providers();
    
    echo '<div class="success">';
    echo '<p>✅ Lab Import Manager: WORKING</p>';
    echo '<p>🏥 Supported Providers: ' . count($supported_providers) . '</p>';
    echo '</div>';
    
    echo '<h3>Supported Lab Providers:</h3>';
    echo '<ul>';
    foreach ($supported_providers as $key => $provider) {
        echo '<li><strong>' . esc_html($provider['name']) . '</strong> (' . esc_html($key) . ')</li>';
    }
    echo '</ul>';
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<h3>❌ Lab Import Manager Error</h3>';
    echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
    echo '</div>';
}

// Test 7: Direct Biomarker Interface Display
echo '<h2>7. Biomarker Management Interface Display</h2>';
try {
    $current_user = wp_get_current_user();
    
    echo '<div class="biomarker-interface">';
    echo '<h3>🔬 Biomarker Management Interface</h3>';
    
    // Call the biomarker management tab method directly
    $admin->add_biomarker_management_tab($current_user);
    
    echo '</div>';
    
    echo '<div class="success">';
    echo '<h3>✅ SUCCESS: Biomarker Interface Displayed</h3>';
    echo '<p>The biomarker management interface is working correctly!</p>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<h3>❌ Interface Display Error</h3>';
    echo '<p>Error displaying biomarker interface: ' . esc_html($e->getMessage()) . '</p>';
    echo '</div>';
}

// Test 8: WordPress Profile Integration Instructions
echo '<h2>8. WordPress Profile Integration</h2>';
echo '<div class="info">';
echo '<h3>🔍 To See Biomarkers in WordPress Admin Profile:</h3>';
echo '<ol>';
echo '<li><strong>Deactivate and Reactivate</strong> the ENNU Life plugin</li>';
echo '<li><strong>Go to</strong>: <a href="' . admin_url('profile.php') . '" target="_blank">' . admin_url('profile.php') . '</a></li>';
echo '<li><strong>Scroll Down</strong> to find the "Biomarker Management" section</li>';
echo '<li><strong>If Not Visible</strong>: Clear any caching plugins</li>';
echo '</ol>';
echo '</div>';

echo '</div></body></html>';
?> 