<?php
/**
 * Test script for User CSV Import Shortcode
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

echo "=== ENNU Life User CSV Import Shortcode Test ===\n";
echo "Plugin Version: " . ENNU_LIFE_VERSION . "\n\n";

// Check if the shortcode class exists
if (class_exists('ENNU_User_CSV_Import_Shortcode')) {
    echo "✓ ENNU_User_CSV_Import_Shortcode class found\n";
    
    // Test shortcode registration
    global $shortcode_tags;
    if (isset($shortcode_tags['ennu_user_csv_import'])) {
        echo "✓ Shortcode 'ennu_user_csv_import' is registered\n";
    } else {
        echo "✗ Shortcode 'ennu_user_csv_import' not found in registered shortcodes\n";
    }
    
    // Test AJAX action registration
    global $wp_filter;
    if (isset($wp_filter['wp_ajax_ennu_user_csv_import'])) {
        echo "✓ AJAX action 'ennu_user_csv_import' is registered for logged-in users\n";
    } else {
        echo "✗ AJAX action 'ennu_user_csv_import' not found for logged-in users\n";
    }
    
    if (isset($wp_filter['wp_ajax_nopriv_ennu_user_csv_import'])) {
        echo "✓ AJAX action 'ennu_user_csv_import' is registered for non-logged-in users\n";
    } else {
        echo "✗ AJAX action 'ennu_user_csv_import' not found for non-logged-in users\n";
    }
    
    // Test shortcode rendering
    $shortcode = new ENNU_User_CSV_Import_Shortcode();
    
    // Test with no user logged in
    echo "\n=== Testing Shortcode Rendering (No User Logged In) ===\n";
    $output = $shortcode->render_import_form(array());
    if (strpos($output, 'Login Required') !== false) {
        echo "✓ Shortcode correctly shows login message for non-logged-in users\n";
    } else {
        echo "✗ Shortcode does not show login message for non-logged-in users\n";
    }
    
    // Test with user logged in
    echo "\n=== Testing Shortcode Rendering (User Logged In) ===\n";
    $admin_user = get_user_by('login', 'admin');
    if ($admin_user) {
        wp_set_current_user($admin_user->ID);
        $output = $shortcode->render_import_form(array());
        
        if (strpos($output, 'Import Your Biomarker Data') !== false) {
            echo "✓ Shortcode correctly shows import form for logged-in users\n";
        } else {
            echo "✗ Shortcode does not show import form for logged-in users\n";
        }
        
        if (strpos($output, 'admin') !== false) {
            echo "✓ Shortcode shows correct user name\n";
        } else {
            echo "✗ Shortcode does not show user name\n";
        }
        
        if (strpos($output, 'Supported Biomarkers') !== false) {
            echo "✓ Shortcode shows supported biomarkers section\n";
        } else {
            echo "✗ Shortcode does not show supported biomarkers section\n";
        }
        
        if (strpos($output, 'Sample CSV Format') !== false) {
            echo "✓ Shortcode shows sample CSV format section\n";
        } else {
            echo "✗ Shortcode does not show sample CSV format section\n";
        }
        
    } else {
        echo "✗ Could not find admin user for testing\n";
    }
    
    // Test shortcode attributes
    echo "\n=== Testing Shortcode Attributes ===\n";
    $output = $shortcode->render_import_form(array('show_instructions' => 'false'));
    if (strpos($output, 'How to Import Your Data') === false) {
        echo "✓ Shortcode correctly hides instructions when show_instructions=false\n";
    } else {
        echo "✗ Shortcode does not hide instructions when show_instructions=false\n";
    }
    
    $output = $shortcode->render_import_form(array('show_sample' => 'false'));
    if (strpos($output, 'Sample CSV Format') === false) {
        echo "✓ Shortcode correctly hides sample section when show_sample=false\n";
    } else {
        echo "✗ Shortcode does not hide sample section when show_sample=false\n";
    }
    
} else {
    echo "✗ ENNU_User_CSV_Import_Shortcode class not found\n";
}

// Test asset files
echo "\n=== Testing Asset Files ===\n";
$css_file = ENNU_LIFE_PLUGIN_URL . 'assets/css/user-csv-import.css';
$js_file = ENNU_LIFE_PLUGIN_URL . 'assets/js/user-csv-import.js';

if (file_exists(dirname(__FILE__) . '/assets/css/user-csv-import.css')) {
    echo "✓ CSS file exists: user-csv-import.css\n";
} else {
    echo "✗ CSS file missing: user-csv-import.css\n";
}

if (file_exists(dirname(__FILE__) . '/assets/js/user-csv-import.js')) {
    echo "✓ JavaScript file exists: user-csv-import.js\n";
} else {
    echo "✗ JavaScript file missing: user-csv-import.js\n";
}

// Test shortcode usage examples
echo "\n=== Shortcode Usage Examples ===\n";
echo "Basic usage: [ennu_user_csv_import]\n";
echo "Hide instructions: [ennu_user_csv_import show_instructions=\"false\"]\n";
echo "Hide sample: [ennu_user_csv_import show_sample=\"false\"]\n";
echo "Custom file size: [ennu_user_csv_import max_file_size=\"10\"]\n";

// Test available biomarkers
echo "\n=== Testing Available Biomarkers ===\n";
if (class_exists('ENNU_User_CSV_Import_Shortcode')) {
    $shortcode = new ENNU_User_CSV_Import_Shortcode();
    $reflection = new ReflectionClass($shortcode);
    $method = $reflection->getMethod('get_available_biomarkers');
    $method->setAccessible(true);
    $biomarkers = $method->invoke($shortcode);
    
    echo "✓ Available biomarkers: " . count($biomarkers) . "\n";
    echo "Sample biomarkers:\n";
    $count = 0;
    foreach ($biomarkers as $key => $info) {
        if ($count < 5) {
            echo "  - $key: {$info['name']} ({$info['unit']})\n";
            $count++;
        }
    }
}

echo "\n=== Test Complete ===\n";
echo "\nTo test the shortcode on a page:\n";
echo "1. Create a new page in WordPress\n";
echo "2. Add the shortcode: [ennu_user_csv_import]\n";
echo "3. Publish the page and visit it\n";
echo "4. Test both logged-in and non-logged-in states\n"; 