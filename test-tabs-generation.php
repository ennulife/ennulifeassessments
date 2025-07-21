<?php
/**
 * ENNU Life Tabs Generation Test
 * 
 * This script tests what tabs are being generated and shows the exact HTML structure
 * 
 * @package ENNU_Life
 * @version 57.2.6
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions to run this test.');
}

echo '<!DOCTYPE html>';
echo '<html><head><title>ENNU Tabs Generation Test</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }';
echo '.success { color: green; }';
echo '.error { color: red; }';
echo '.warning { color: orange; }';
echo '.section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }';
echo '.code-block { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-family: monospace; font-size: 12px; overflow-x: auto; }';
echo '</style>';
echo '</head><body>';

echo '<h1>ENNU Life Tabs Generation Test</h1>';

// Test 1: Check what assessments are available
echo '<div class="section">';
echo '<h2>1. Available Assessments</h2>';

$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
if ($plugin) {
    $shortcode_handler = $plugin->get_shortcode_handler();
    if ($shortcode_handler) {
        $assessments = array_keys($shortcode_handler->get_all_assessment_definitions());
        
        echo '<p><strong>All assessments found:</strong></p>';
        echo '<ul>';
        foreach ($assessments as $assessment) {
            $status = ($assessment === 'welcome_assessment') ? ' (will be filtered out)' : ' (will be included)';
            echo '<li>' . $assessment . $status . '</li>';
        }
        echo '</ul>';
        
        echo '<p><strong>Assessments that will create tabs:</strong></p>';
        echo '<ul>';
        foreach ($assessments as $assessment) {
            if ($assessment !== 'welcome_assessment') {
                $label = ucwords(str_replace(array('_', 'assessment'), ' ', $assessment));
                echo '<li>' . $assessment . ' → "' . $label . '"</li>';
            }
        }
        echo '</ul>';
        
    } else {
        echo '<p class="error">❌ Shortcode handler not found</p>';
    }
} else {
    echo '<p class="error">❌ Plugin instance not found</p>';
}

echo '</div>';

// Test 2: Generate the actual tab HTML
echo '<div class="section">';
echo '<h2>2. Generated Tab HTML</h2>';

$current_user_id = get_current_user_id();
$user = get_userdata($current_user_id);

if ($user) {
    echo '<p><strong>Generating tabs for user ID:</strong> ' . $current_user_id . '</p>';
    
    // Capture the output
    ob_start();
    
    // Call the function that generates the tabs
    $admin = $plugin->get_admin();
    if ($admin) {
        $admin->show_user_assessment_fields($user);
        $generated_html = ob_get_clean();
        
        echo '<p class="success">✅ Tab HTML generated successfully</p>';
        
        // Show the HTML structure
        echo '<h3>Generated HTML:</h3>';
        echo '<div class="code-block">' . htmlspecialchars($generated_html) . '</div>';
        
        // Extract tab information
        echo '<h3>Tab Analysis:</h3>';
        
        // Count tab links
        $tab_link_count = substr_count($generated_html, '<a href="#tab-');
        echo '<p><strong>Tab links found:</strong> ' . $tab_link_count . '</p>';
        
        // Count tab contents
        $tab_content_count = substr_count($generated_html, 'class="ennu-admin-tab-content');
        echo '<p><strong>Tab contents found:</strong> ' . $tab_content_count . '</p>';
        
        // Extract tab IDs
        preg_match_all('/href="#tab-([^"]+)"/', $generated_html, $matches);
        if (!empty($matches[1])) {
            echo '<p><strong>Tab IDs found:</strong></p>';
            echo '<ul>';
            foreach ($matches[1] as $tab_id) {
                echo '<li>#' . $tab_id . '</li>';
            }
            echo '</ul>';
        }
        
        // Check for tab container
        if (strpos($generated_html, 'class="ennu-admin-tabs"') !== false) {
            echo '<p class="success">✅ Tab container found</p>';
        } else {
            echo '<p class="error">❌ Tab container NOT found</p>';
        }
        
        // Check for tab navigation
        if (strpos($generated_html, 'class="ennu-admin-tab-nav"') !== false) {
            echo '<p class="success">✅ Tab navigation found</p>';
        } else {
            echo '<p class="error">❌ Tab navigation NOT found</p>';
        }
        
    } else {
        echo '<p class="error">❌ Admin instance not found</p>';
    }
} else {
    echo '<p class="error">❌ User not found</p>';
}

echo '</div>';

// Test 3: Test the actual tab functionality
echo '<div class="section">';
echo '<h2>3. Live Tab Test</h2>';

// Enqueue the required assets
wp_enqueue_style('ennu-admin-tabs-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-tabs-enhanced.css', array(), ENNU_LIFE_VERSION);
wp_enqueue_script('ennu-admin-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin-enhanced.js', array('jquery'), ENNU_LIFE_VERSION, true);

wp_localize_script('ennu-admin-enhanced', 'ennuAdmin', array( 
    'nonce' => wp_create_nonce('ennu_admin_nonce'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'confirm_msg' => 'Are you sure?',
    'plugin_url' => ENNU_LIFE_PLUGIN_URL,
    'debug' => true
));

echo '<p>Below is a live test of the tab functionality:</p>';

// Generate the tabs again for live testing
if ($user && $admin) {
    $admin->show_user_assessment_fields($user);
}

echo '</div>';

// Test 4: Instructions
echo '<div class="section">';
echo '<h2>4. Next Steps</h2>';
echo '<p><strong>If tabs are generated but not working:</strong></p>';
echo '<ol>';
echo '<li>Check browser console for JavaScript errors</li>';
echo '<li>Verify the tab IDs match between links and content</li>';
echo '<li>Check if CSS is loading correctly</li>';
echo '<li>Test clicking the tabs above</li>';
echo '</ol>';
echo '</div>';

echo '</body></html>';
?> 