<?php
/**
 * ENNU Life User CSV Import Shortcode Demo
 * This page demonstrates the [ennu_user_csv_import] shortcode
 */

// Load WordPress
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

// Check if user is logged in
$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>ENNU Life User CSV Import Shortcode Demo</title>";
echo "<meta charset='utf-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }";
echo ".demo-container { max-width: 1200px; margin: 0 auto; }";
echo ".demo-header { background: #2c3e50; color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; text-align: center; }";
echo ".demo-header h1 { margin: 0; font-size: 2.5em; }";
echo ".demo-header p { margin: 10px 0 0 0; font-size: 1.2em; opacity: 0.9; }";
echo ".demo-section { background: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".demo-section h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }";
echo ".code-block { background: #2c3e50; color: #ecf0f1; padding: 20px; border-radius: 6px; font-family: 'Courier New', monospace; margin: 15px 0; overflow-x: auto; }";
echo ".status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; margin-left: 10px; }";
echo ".status-logged-in { background: #27ae60; color: white; }";
echo ".status-logged-out { background: #e74c3c; color: white; }";
echo ".shortcode-example { background: #f8f9fa; border: 1px solid #e9ecef; padding: 20px; border-radius: 6px; margin: 15px 0; }";
echo ".shortcode-example h4 { margin: 0 0 10px 0; color: #495057; }";
echo ".shortcode-output { border: 2px dashed #dee2e6; padding: 20px; border-radius: 6px; margin: 15px 0; background: #f8f9fa; }";
echo ".shortcode-output h4 { margin: 0 0 15px 0; color: #495057; }";
echo ".login-actions { text-align: center; margin: 20px 0; }";
echo ".login-actions a { display: inline-block; padding: 12px 24px; margin: 0 10px; text-decoration: none; border-radius: 6px; font-weight: 600; }";
echo ".btn-primary { background: #3498db; color: white; }";
echo ".btn-primary:hover { background: #2980b9; }";
echo ".btn-secondary { background: #95a5a6; color: white; }";
echo ".btn-secondary:hover { background: #7f8c8d; }";
echo ".feature-list { list-style: none; padding: 0; }";
echo ".feature-list li { padding: 8px 0; border-bottom: 1px solid #eee; }";
echo ".feature-list li:before { content: '✓ '; color: #27ae60; font-weight: bold; }";
echo ".feature-list li:last-child { border-bottom: none; }";
echo "</style>";
echo "</head><body>";

echo "<div class='demo-container'>";
echo "<div class='demo-header'>";
echo "<h1>🔬 ENNU Life User CSV Import Shortcode Demo</h1>";
echo "<p>Demonstrating the [ennu_user_csv_import] shortcode functionality</p>";
echo "<div style='margin-top: 15px;'>";
echo "<span class='status-badge " . ($is_logged_in ? 'status-logged-in' : 'status-logged-out') . "'>";
echo ($is_logged_in ? 'Logged In' : 'Not Logged In');
echo "</span>";
if ($is_logged_in) {
    echo "<span style='margin-left: 10px; color: #ecf0f1;'>as: " . esc_html($current_user->display_name) . "</span>";
}
echo "</div>";
echo "</div>";

// Authentication Status
echo "<div class='demo-section'>";
echo "<h2>🔐 Authentication Status</h2>";
if ($is_logged_in) {
    echo "<p><strong>✅ You are logged in as:</strong> " . esc_html($current_user->display_name) . " (" . esc_html($current_user->user_email) . ")</p>";
    echo "<p>The shortcode will display the full import form below.</p>";
} else {
    echo "<p><strong>❌ You are not logged in.</strong></p>";
    echo "<p>The shortcode will display a login message instead of the import form.</p>";
    echo "<div class='login-actions'>";
    echo "<a href='" . esc_url(wp_login_url(get_permalink())) . "' class='btn-primary'>Login</a>";
    if (get_option('users_can_register')) {
        echo "<a href='" . esc_url(wp_registration_url()) . "' class='btn-secondary'>Register</a>";
    }
    echo "</div>";
}
echo "</div>";

// Shortcode Usage Examples
echo "<div class='demo-section'>";
echo "<h2>📝 Shortcode Usage Examples</h2>";

echo "<div class='shortcode-example'>";
echo "<h4>Basic Usage:</h4>";
echo "<div class='code-block'>[ennu_user_csv_import]</div>";
echo "<p>Displays the complete import form with instructions and sample data.</p>";
echo "</div>";

echo "<div class='shortcode-example'>";
echo "<h4>Hide Instructions:</h4>";
echo "<div class='code-block'>[ennu_user_csv_import show_instructions=\"false\"]</div>";
echo "<p>Hides the 'How to Import Your Data' instructions section.</p>";
echo "</div>";

echo "<div class='shortcode-example'>";
echo "<h4>Hide Sample Data:</h4>";
echo "<div class='code-block'>[ennu_user_csv_import show_sample=\"false\"]</div>";
echo "<p>Hides the sample CSV format and download link.</p>";
echo "</div>";

echo "<div class='shortcode-example'>";
echo "<h4>Custom File Size Limit:</h4>";
echo "<div class='code-block'>[ennu_user_csv_import max_file_size=\"10\"]</div>";
echo "<p>Sets maximum file size to 10MB (default is 5MB).</p>";
echo "</div>";

echo "<div class='shortcode-example'>";
echo "<h4>Minimal Form:</h4>";
echo "<div class='code-block'>[ennu_user_csv_import show_instructions=\"false\" show_sample=\"false\"]</div>";
echo "<p>Shows only the essential import form without extra sections.</p>";
echo "</div>";
echo "</div>";

// Features List
echo "<div class='demo-section'>";
echo "<h2>✨ Key Features</h2>";
echo "<ul class='feature-list'>";
echo "<li>User authentication and permission handling</li>";
echo "<li>Drag-and-drop file upload support</li>";
echo "<li>Real-time validation and error feedback</li>";
echo "<li>Comprehensive biomarker validation (40+ supported)</li>";
echo "<li>Mobile-responsive design</li>";
echo "<li>Accessibility features and keyboard navigation</li>";
echo "<li>Import history tracking</li>";
echo "<li>Overwrite protection for existing data</li>";
echo "<li>Automatic score updates after import</li>";
echo "<li>Sample CSV file download</li>";
echo "<li>Customizable shortcode attributes</li>";
echo "<li>Security with nonce verification</li>";
echo "</ul>";
echo "</div>";

// Live Shortcode Demo
echo "<div class='demo-section'>";
echo "<h2>🎯 Live Shortcode Demo</h2>";
echo "<p>Below is the actual output of the <code>[ennu_user_csv_import]</code> shortcode:</p>";

echo "<div class='shortcode-output'>";
echo "<h4>Shortcode Output:</h4>";

// Render the shortcode
if (class_exists('ENNU_User_CSV_Import_Shortcode')) {
    $shortcode = new ENNU_User_CSV_Import_Shortcode();
    echo $shortcode->render_import_form(array());
} else {
    echo "<p style='color: #e74c3c;'><strong>Error:</strong> ENNU_User_CSV_Import_Shortcode class not found.</p>";
    echo "<p>Please ensure the plugin is properly loaded.</p>";
}

echo "</div>";
echo "</div>";

// Technical Details
echo "<div class='demo-section'>";
echo "<h2>🔧 Technical Details</h2>";
echo "<h3>Files Created:</h3>";
echo "<ul>";
echo "<li><code>includes/class-user-csv-import-shortcode.php</code> - Main shortcode class</li>";
echo "<li><code>assets/css/user-csv-import.css</code> - Frontend styles</li>";
echo "<li><code>assets/js/user-csv-import.js</code> - Frontend JavaScript</li>";
echo "</ul>";

echo "<h3>AJAX Endpoints:</h3>";
echo "<ul>";
echo "<li><code>wp_ajax_ennu_user_csv_import</code> - For logged-in users</li>";
echo "<li><code>wp_ajax_nopriv_ennu_user_csv_import</code> - For non-logged-in users</li>";
echo "</ul>";

echo "<h3>User Meta Fields:</h3>";
echo "<ul>";
echo "<li><code>ennu_biomarker_data</code> - Stored biomarker data</li>";
echo "<li><code>ennu_last_user_csv_import</code> - Last import timestamp</li>";
echo "<li><code>ennu_user_csv_import_history</code> - Import history log</li>";
echo "</ul>";
echo "</div>";

// Implementation Guide
echo "<div class='demo-section'>";
echo "<h2>📖 Implementation Guide</h2>";
echo "<h3>Step 1: Add to Page</h3>";
echo "<p>Add the shortcode to any WordPress page or post:</p>";
echo "<div class='code-block'>[ennu_user_csv_import]</div>";

echo "<h3>Step 2: Customize (Optional)</h3>";
echo "<p>Use attributes to customize the display:</p>";
echo "<div class='code-block'>[ennu_user_csv_import show_instructions=\"false\" show_sample=\"false\"]</div>";

echo "<h3>Step 3: Test</h3>";
echo "<p>Test with both logged-in and non-logged-in users to ensure proper functionality.</p>";

echo "<h3>Step 4: Monitor</h3>";
echo "<p>Check the WordPress debug log for any import activity or errors.</p>";
echo "</div>";

echo "</div>";
echo "</body></html>"; 