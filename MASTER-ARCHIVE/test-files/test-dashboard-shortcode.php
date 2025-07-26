<?php
/**
 * Test Dashboard Shortcode Functionality
 * 
 * This script will diagnose why the user dashboard shortcode is not rendering.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    // Load WordPress if not already loaded
    if ( ! file_exists( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-config.php' ) ) {
        die( 'WordPress not found. Please run this from within WordPress.' );
    }
    
    require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-config.php';
}

// Ensure we're in WordPress context
if ( ! function_exists( 'wp_get_current_user' ) ) {
    die( 'WordPress not properly loaded.' );
}

echo '<h1>ENNU Life Dashboard Shortcode Diagnostic</h1>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
</style>';

// Test 1: Check if plugin is active
echo '<div class="section">';
echo '<h2>1. Plugin Status Check</h2>';

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( is_plugin_active( 'ennulifeassessments/ennu-life-plugin.php' ) ) {
    echo '<p class="success">✓ Plugin is active</p>';
} else {
    echo '<p class="error">✗ Plugin is not active</p>';
    die();
}
echo '</div>';

// Test 2: Check if shortcode class exists
echo '<div class="section">';
echo '<h2>2. Shortcode Class Check</h2>';

if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    echo '<p class="success">✓ ENNU_Assessment_Shortcodes class exists</p>';
} else {
    echo '<p class="error">✗ ENNU_Assessment_Shortcodes class not found</p>';
    die();
}
echo '</div>';

// Test 3: Check if shortcode is registered
echo '<div class="section">';
echo '<h2>3. Shortcode Registration Check</h2>';

global $shortcode_tags;
if ( isset( $shortcode_tags['ennu-user-dashboard'] ) ) {
    echo '<p class="success">✓ ennu-user-dashboard shortcode is registered</p>';
    echo '<p class="info">Callback: ' . ( is_array( $shortcode_tags['ennu-user-dashboard'] ) ? 
         get_class( $shortcode_tags['ennu-user-dashboard'][0] ) . '::' . $shortcode_tags['ennu-user-dashboard'][1] : 
         'Function: ' . $shortcode_tags['ennu-user-dashboard'] ) . '</p>';
} else {
    echo '<p class="error">✗ ennu-user-dashboard shortcode is NOT registered</p>';
    
    // Try to manually register it
    echo '<p class="info">Attempting to manually register shortcode...</p>';
    
    $plugin = ENNU_Life_Enhanced_Plugin::get_instance();
    if ( $plugin ) {
        $shortcodes = $plugin->get_shortcodes();
        if ( $shortcodes ) {
            add_shortcode( 'ennu-user-dashboard', array( $shortcodes, 'render_user_dashboard' ) );
            echo '<p class="success">✓ Manually registered shortcode</p>';
        } else {
            echo '<p class="error">✗ Could not get shortcodes instance</p>';
        }
    } else {
        echo '<p class="error">✗ Could not get plugin instance</p>';
    }
}
echo '</div>';

// Test 4: Check user authentication
echo '<div class="section">';
echo '<h2>4. User Authentication Check</h2>';

$current_user = wp_get_current_user();
if ( $current_user->exists() ) {
    echo '<p class="success">✓ User is logged in: ' . esc_html( $current_user->user_email ) . '</p>';
    echo '<p class="info">User ID: ' . $current_user->ID . '</p>';
} else {
    echo '<p class="warning">⚠ User is not logged in - dashboard will show login form</p>';
}
echo '</div>';

// Test 5: Check if shortcode method exists
echo '<div class="section">';
echo '<h2>5. Shortcode Method Check</h2>';

$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
if ( $plugin ) {
    $shortcodes = $plugin->get_shortcodes();
    if ( $shortcodes ) {
        if ( method_exists( $shortcodes, 'render_user_dashboard' ) ) {
            echo '<p class="success">✓ render_user_dashboard method exists</p>';
        } else {
            echo '<p class="error">✗ render_user_dashboard method does not exist</p>';
        }
    } else {
        echo '<p class="error">✗ Could not get shortcodes instance</p>';
    }
} else {
    echo '<p class="error">✗ Could not get plugin instance</p>';
}
echo '</div>';

// Test 6: Test shortcode rendering
echo '<div class="section">';
echo '<h2>6. Shortcode Rendering Test</h2>';

if ( isset( $shortcode_tags['ennu-user-dashboard'] ) ) {
    echo '<p class="info">Testing shortcode rendering...</p>';
    
    // Capture any errors
    ob_start();
    $result = do_shortcode( '[ennu-user-dashboard]' );
    $errors = ob_get_clean();
    
    if ( ! empty( $errors ) ) {
        echo '<p class="error">✗ Errors during rendering:</p>';
        echo '<pre>' . esc_html( $errors ) . '</pre>';
    }
    
    if ( ! empty( $result ) ) {
        echo '<p class="success">✓ Shortcode rendered successfully</p>';
        echo '<p class="info">Output length: ' . strlen( $result ) . ' characters</p>';
        echo '<div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0; max-height: 300px; overflow-y: auto;">';
        echo '<h3>Rendered Output:</h3>';
        echo $result;
        echo '</div>';
    } else {
        echo '<p class="error">✗ Shortcode returned empty output</p>';
    }
} else {
    echo '<p class="error">✗ Cannot test rendering - shortcode not registered</p>';
}
echo '</div>';

// Test 7: Check for JavaScript errors
echo '<div class="section">';
echo '<h2>7. Asset Loading Check</h2>';

// Check if required assets exist
$plugin_url = plugin_dir_url( __FILE__ );
$assets_to_check = array(
    'assets/css/user-dashboard.css',
    'assets/js/user-dashboard.js',
    'assets/js/chart.umd.js'
);

foreach ( $assets_to_check as $asset ) {
    $full_url = $plugin_url . $asset;
    $file_path = plugin_dir_path( __FILE__ ) . $asset;
    
    if ( file_exists( $file_path ) ) {
        echo '<p class="success">✓ Asset exists: ' . $asset . '</p>';
    } else {
        echo '<p class="error">✗ Asset missing: ' . $asset . '</p>';
    }
}
echo '</div>';

// Test 8: Check WordPress hooks
echo '<div class="section">';
echo '<h2>8. WordPress Hooks Check</h2>';

global $wp_filter;
$hooks_to_check = array(
    'wp_ajax_ennu_submit_assessment',
    'wp_ajax_nopriv_ennu_submit_assessment',
    'wp_ajax_ennu_check_email',
    'wp_ajax_nopriv_ennu_check_email',
    'wp_ajax_ennu_check_auth_state',
    'wp_ajax_nopriv_ennu_check_auth_state'
);

foreach ( $hooks_to_check as $hook ) {
    if ( isset( $wp_filter[$hook] ) ) {
        echo '<p class="success">✓ Hook registered: ' . $hook . '</p>';
    } else {
        echo '<p class="warning">⚠ Hook not registered: ' . $hook . '</p>';
    }
}
echo '</div>';

// Test 9: Check database connectivity
echo '<div class="section">';
echo '<h2>9. Database Connectivity Check</h2>';

global $wpdb;
if ( $wpdb && $wpdb->db_connect() ) {
    echo '<p class="success">✓ Database connection successful</p>';
    
    // Check if user meta table is accessible
    $user_id = get_current_user_id();
    if ( $user_id ) {
        $user_meta = get_user_meta( $user_id );
        if ( $user_meta !== false ) {
            echo '<p class="success">✓ User meta accessible</p>';
        } else {
            echo '<p class="error">✗ Cannot access user meta</p>';
        }
    }
} else {
    echo '<p class="error">✗ Database connection failed</p>';
}
echo '</div>';

// Test 10: Manual shortcode test
echo '<div class="section">';
echo '<h2>10. Manual Shortcode Test</h2>';

echo '<p class="info">You can test the shortcode manually by adding this to any page:</p>';
echo '<code>[ennu-user-dashboard]</code>';

echo '<p class="info">Or test it here:</p>';
echo '<div style="border: 2px solid #007cba; padding: 20px; margin: 20px 0; background: #f0f0f0;">';
echo '<h3>Manual Shortcode Test:</h3>';
echo do_shortcode( '[ennu-user-dashboard]' );
echo '</div>';
echo '</div>';

echo '<div class="section">';
echo '<h2>Diagnostic Complete</h2>';
echo '<p class="info">If the shortcode is still not working, check the WordPress debug log for additional errors.</p>';
echo '<p class="info">Debug log location: ' . WP_CONTENT_DIR . '/debug.log</p>';
echo '</div>';
?> 