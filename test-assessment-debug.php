<?php
/**
 * Simple test page to debug assessment submission
 * Add this file to your WordPress root directory and visit it in your browser
 * to test the assessment submission process.
 */

// Load WordPress
require_once('./wp-config.php');
require_once('./wp-load.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test if the shortcode handlers are registered
echo "<h1>ENNU Assessment Debug Test</h1>";

// Check if classes exist
echo "<h2>Class Status:</h2>";
echo "ENNU_Assessment_Shortcodes: " . (class_exists('ENNU_Assessment_Shortcodes') ? 'EXISTS' : 'NOT FOUND') . "<br>";
echo "ENNU_Form_Handler: " . (class_exists('ENNU_Form_Handler') ? 'EXISTS' : 'NOT FOUND') . "<br>";

// Check if AJAX actions are registered
echo "<h2>AJAX Actions:</h2>";
global $wp_filter;
$ajax_actions = array(
    'wp_ajax_ennu_submit_assessment',
    'wp_ajax_nopriv_ennu_submit_assessment'
);

foreach ($ajax_actions as $action) {
    $registered = isset($wp_filter[$action]) && !empty($wp_filter[$action]->callbacks);
    echo "$action: " . ($registered ? 'REGISTERED' : 'NOT REGISTERED') . "<br>";
    
    if ($registered) {
        echo "&nbsp;&nbsp;Handlers:<br>";
        foreach ($wp_filter[$action]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) && is_object($callback['function'][0])) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;- " . get_class($callback['function'][0]) . "::" . $callback['function'][1] . "<br>";
                } else {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;- " . print_r($callback['function'], true) . "<br>";
                }
            }
        }
    }
}

// Check if shortcodes are registered
echo "<h2>Shortcodes:</h2>";
$shortcodes = array(
    'ennu-welcome-assessment',
    'ennu-hair-assessment',
    'ennu-ed-treatment-assessment',
    'ennu-weight-loss-assessment',
    'ennu-health-assessment',
    'ennu-skin-assessment'
);

foreach ($shortcodes as $shortcode) {
    $registered = shortcode_exists($shortcode);
    echo "[$shortcode]: " . ($registered ? 'REGISTERED' : 'NOT REGISTERED') . "<br>";
}

// Test assessment form rendering
echo "<h2>Test Assessment Form:</h2>";
echo do_shortcode('[ennu-hair-assessment]');

// Check recent error logs
echo "<h2>Recent Error Logs:</h2>";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $recent_logs = array_slice(explode("\n", $logs), -20); // Last 20 lines
    echo "<pre>" . implode("\n", $recent_logs) . "</pre>";
} else {
    echo "No debug.log file found. Make sure WP_DEBUG_LOG is enabled in wp-config.php";
}

// Instructions
echo "<h2>Instructions:</h2>";
echo "<ol>";
echo "<li>Try submitting the assessment form above</li>";
echo "<li>Check the browser console for JavaScript errors</li>";
echo "<li>Check this page for updated error logs</li>";
echo "<li>Check your WordPress admin → Users → (any user) → ENNU Assessment Data section</li>";
echo "</ol>";

echo "<h2>Expected Flow:</h2>";
echo "<ol>";
echo "<li>Form submission triggers JavaScript</li>";
echo "<li>JavaScript sends AJAX request to wp-admin/admin-ajax.php</li>";
echo "<li>WordPress routes to handle_assessment_submission() in class-assessment-shortcodes.php</li>";
echo "<li>Data is validated and saved to user meta</li>";
echo "<li>Success response is returned</li>";
echo "</ol>";
?>