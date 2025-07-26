<?php
/**
 * ENNU Biomarker Panel Configuration - Phase 3 Test
 * 
 * This file tests the Phase 3 panel configuration system implementation
 * Run this file in the browser to verify all Phase 3 features are working
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

echo "<h1>ENNU Biomarker Panel Configuration - Phase 3 Test</h1>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Version:</strong> 62.34.0</p>";

$tests_passed = 0;
$total_tests = 0;

// Test 1: Check if orchestrator exists and has panel AJAX handlers
$total_tests++;
echo "<h2>Test 1: Panel AJAX Handlers</h2>";
if ( class_exists( 'ENNU_Biomarker_Range_Orchestrator' ) ) {
    $orchestrator = ENNU_Biomarker_Range_Orchestrator::get_instance();
    
    // Check if AJAX handlers are registered
    global $wp_filter;
    $ajax_handlers = array(
        'wp_ajax_get_biomarker_panel',
        'wp_ajax_save_biomarker_panel', 
        'wp_ajax_delete_biomarker_panel',
        'wp_ajax_duplicate_biomarker_panel'
    );
    
    $handlers_found = 0;
    foreach ( $ajax_handlers as $handler ) {
        if ( isset( $wp_filter[$handler] ) ) {
            $handlers_found++;
        }
    }
    
    if ( $handlers_found === 4 ) {
        echo "<p style='color: green;'>✅ All 4 panel AJAX handlers registered successfully</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>❌ Only {$handlers_found}/4 panel AJAX handlers found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ ENNU_Biomarker_Range_Orchestrator class not found</p>";
}

// Test 2: Check if admin class has panel configuration methods
$total_tests++;
echo "<h2>Test 2: Panel Configuration Methods</h2>";
if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
    $admin_methods = array(
        'render_biomarker_panel_configuration_page',
        'handle_save_biomarker_panel',
        'handle_delete_biomarker_panel', 
        'handle_duplicate_biomarker_panel',
        'render_panel_management_tab',
        'render_biomarker_assignment_tab',
        'render_pricing_tiers_tab',
        'render_panel_analytics_tab',
        'get_available_panels',
        'get_default_panels'
    );
    
    $methods_found = 0;
    foreach ( $admin_methods as $method ) {
        if ( method_exists( 'ENNU_Enhanced_Admin', $method ) ) {
            $methods_found++;
        }
    }
    
    if ( $methods_found === 10 ) {
        echo "<p style='color: green;'>✅ All 10 panel configuration methods found</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>❌ Only {$methods_found}/10 panel configuration methods found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ ENNU_Enhanced_Admin class not found</p>";
}

// Test 3: Check if admin menu is registered with panel configuration
$total_tests++;
echo "<h2>Test 3: Admin Menu Registration</h2>";
global $submenu;
if ( isset( $submenu['ennu-biomarkers'] ) ) {
    $panel_config_found = false;
    foreach ( $submenu['ennu-biomarkers'] as $submenu_item ) {
        if ( strpos( $submenu_item[0], 'Panel Configuration' ) !== false ) {
            $panel_config_found = true;
            break;
        }
    }
    
    if ( $panel_config_found ) {
        echo "<p style='color: green;'>✅ Panel Configuration submenu found</p>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>❌ Panel Configuration submenu not found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ ENNU Biomarkers menu not found</p>";
}

// Test 4: Check if panel configuration assets are enqueued
$total_tests++;
echo "<h2>Test 4: Panel Configuration Assets</h2>";
global $wp_styles, $wp_scripts;

$css_found = false;
$js_found = false;

foreach ( $wp_styles->registered as $handle => $style ) {
    if ( $handle === 'ennu-panel-configuration' ) {
        $css_found = true;
        break;
    }
}

foreach ( $wp_scripts->registered as $handle => $script ) {
    if ( $handle === 'ennu-panel-configuration' ) {
        $js_found = true;
        break;
    }
}

if ( $css_found && $js_found ) {
    echo "<p style='color: green;'>✅ Panel configuration CSS and JS assets registered</p>";
    $tests_passed++;
} else {
    echo "<p style='color: red;'>❌ Panel configuration assets missing: CSS=" . ($css_found ? 'Yes' : 'No') . ", JS=" . ($js_found ? 'Yes' : 'No') . "</p>";
}

// Test 5: Check database integration for panels
$total_tests++;
echo "<h2>Test 5: Panel Database Integration</h2>";

// Test saving a panel
$test_panel_id = 'test_panel_' . time();
$test_panel_data = array(
    'name' => 'Test Panel',
    'description' => 'Test panel for Phase 3 verification',
    'category' => 'core',
    'biomarkers' => array( 'glucose', 'hba1c' ),
    'pricing' => array(
        'base_price' => 99.99,
        'member_price' => 79.99,
        'currency' => 'USD'
    ),
    'status' => 'active',
    'created_by' => 1,
    'created_date' => current_time( 'mysql' ),
    'last_modified' => current_time( 'mysql' )
);

$save_result = update_option( "ennu_biomarker_panel_{$test_panel_id}", $test_panel_data );

if ( $save_result ) {
    // Test retrieving the panel
    $retrieved_panel = get_option( "ennu_biomarker_panel_{$test_panel_id}" );
    
    if ( $retrieved_panel && $retrieved_panel['name'] === 'Test Panel' ) {
        echo "<p style='color: green;'>✅ Panel database save and retrieve working</p>";
        $tests_passed++;
        
        // Clean up test data
        delete_option( "ennu_biomarker_panel_{$test_panel_id}" );
        echo "<p style='color: blue;'>ℹ️ Test panel cleaned up</p>";
    } else {
        echo "<p style='color: red;'>❌ Panel retrieve failed</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Panel save failed</p>";
}

// Test 6: Check default panels
$total_tests++;
echo "<h2>Test 6: Default Panels</h2>";

// Create admin instance to test default panels
if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
    $admin = new ENNU_Enhanced_Admin();
    $default_panels = $admin->get_default_panels();
    
    if ( isset( $default_panels['core_optimization'] ) && isset( $default_panels['neuro_health'] ) ) {
        echo "<p style='color: green;'>✅ Default panels found: " . count( $default_panels ) . " panels</p>";
        echo "<ul>";
        foreach ( $default_panels as $panel_id => $panel_data ) {
            echo "<li><strong>{$panel_data['name']}</strong> ({$panel_data['category']}) - " . count( $panel_data['biomarkers'] ) . " biomarkers</li>";
        }
        echo "</ul>";
        $tests_passed++;
    } else {
        echo "<p style='color: red;'>❌ Default panels not found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Cannot test default panels - admin class not available</p>";
}

// Test 7: Check plugin version
$total_tests++;
echo "<h2>Test 7: Plugin Version</h2>";
if ( defined( 'ENNU_LIFE_VERSION' ) && ENNU_LIFE_VERSION === '62.34.0' ) {
    echo "<p style='color: green;'>✅ Plugin version correctly set to 62.34.0</p>";
    $tests_passed++;
} else {
    echo "<p style='color: red;'>❌ Plugin version incorrect: " . ( defined( 'ENNU_LIFE_VERSION' ) ? ENNU_LIFE_VERSION : 'Not defined' ) . "</p>";
}

// Summary
echo "<h2>Test Summary</h2>";
echo "<p><strong>Tests Passed:</strong> {$tests_passed}/{$total_tests}</p>";
echo "<p><strong>Success Rate:</strong> " . round( ( $tests_passed / $total_tests ) * 100, 1 ) . "%</p>";

if ( $tests_passed === $total_tests ) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 PHASE 3 IMPLEMENTATION COMPLETE!</p>";
    echo "<p style='color: green; font-weight: bold;'>All Phase 3 features are working correctly.</p>";
    echo "<p style='color: blue;'>Ready for Phase 4: Enhanced Import/Export System</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ PHASE 3 IMPLEMENTATION INCOMPLETE</p>";
    echo "<p style='color: red;'>Some tests failed. Please review the implementation.</p>";
}

echo "<hr>";
echo "<p><strong>Phase 3 Features Tested:</strong></p>";
echo "<ul>";
echo "<li>Panel AJAX Handlers (4 endpoints)</li>";
echo "<li>Panel Configuration Methods (10 methods)</li>";
echo "<li>Admin Menu Registration</li>";
echo "<li>Asset Enqueuing (CSS/JS)</li>";
echo "<li>Database Integration</li>";
echo "<li>Default Panels</li>";
echo "<li>Plugin Version</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Phase 4: Enhanced Import/Export System</li>";
echo "<li>Phase 5: Evidence Management System</li>";
echo "<li>Phase 6: Analytics & Reporting System</li>";
echo "</ul>";

echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?> 