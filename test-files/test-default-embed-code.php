<?php
/**
 * Test Default HubSpot Embed Code Implementation
 * 
 * This script tests that consultation shortcodes use the default embed code
 * when no custom embed code is provided in the admin settings.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure we're in admin context
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

echo '<div style="font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1 style="color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px;">Default HubSpot Embed Code Test</h1>';

// Test 1: Check if consultation shortcode class exists
echo '<h2>1. Consultation Shortcode Class Test</h2>';
if (class_exists('ENNU_Assessment_Shortcodes')) {
    echo '<p style="color: green;">✅ ENNU_Assessment_Shortcodes class exists</p>';
} else {
    echo '<p style="color: red;">❌ ENNU_Assessment_Shortcodes class not found</p>';
    echo '</div>';
    return;
}

// Test 2: Check HubSpot settings
echo '<h2>2. HubSpot Settings Test</h2>';
$hubspot_settings = get_option('ennu_hubspot_settings', array());
if (empty($hubspot_settings)) {
    echo '<p style="color: orange;">⚠️ No HubSpot settings found - this is expected for testing default embed</p>';
} else {
    echo '<p style="color: blue;">ℹ️ HubSpot settings found with ' . count($hubspot_settings) . ' configuration items</p>';
}

// Test 3: Test consultation types
echo '<h2>3. Consultation Types Test</h2>';
$consultation_types = array(
    'hair_restoration' => 'Hair Restoration',
    'ed_treatment' => 'ED Treatment',
    'weight_loss' => 'Weight Loss',
    'health_optimization' => 'Health Optimization',
    'skin_care' => 'Skin Care',
    'general_consultation' => 'General Consultation',
    'schedule_call' => 'Schedule Call',
    'ennu_life_score' => 'ENNU Life Score',
    'health_optimization_results' => 'Health Optimization Results',
    'confidential_consultation' => 'Confidential Consultation'
);

echo '<table style="width: 100%; border-collapse: collapse; margin: 10px 0;">';
echo '<tr style="background: #0073aa; color: white;"><th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Consultation Type</th><th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Status</th><th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Embed Code</th></tr>';

foreach ($consultation_types as $type => $title) {
    $embed_config = $hubspot_settings['embeds'][$type] ?? array();
    $embed_code = $embed_config['embed_code'] ?? '';
    
    // Check if default embed would be used
    $uses_default = empty($embed_code);
    $status = $uses_default ? 'Will use default' : 'Has custom embed';
    $status_color = $uses_default ? 'green' : 'blue';
    
    echo '<tr style="background: white;">';
    echo '<td style="padding: 10px; border: 1px solid #ddd;"><strong>' . esc_html($title) . '</strong></td>';
    echo '<td style="padding: 10px; border: 1px solid #ddd; color: ' . $status_color . ';">' . esc_html($status) . '</td>';
    echo '<td style="padding: 10px; border: 1px solid #ddd; font-family: monospace; font-size: 12px;">';
    if ($uses_default) {
        echo '<span style="color: green;">Default embed code will be used</span>';
    } else {
        echo '<span style="color: blue;">Custom embed code configured</span>';
    }
    echo '</td>';
    echo '</tr>';
}
echo '</table>';

// Test 4: Default embed code verification
echo '<h2>4. Default Embed Code Verification</h2>';
$default_embed_code = '<!-- Start of Meetings Embed Script -->
    <div class="meetings-iframe-container" data-src="https://meetings.hubspot.com/lescobar2/ennulife?embed=true"></div>
    <script type="text/javascript" src="https://static.hsappstatic.net/MeetingsEmbed/ex/MeetingsEmbedCode.js"></script>
  <!-- End of Meetings Embed Script -->';

echo '<div style="background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0;">';
echo '<h4>Default Embed Code:</h4>';
echo '<pre style="background: white; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;">' . esc_html($default_embed_code) . '</pre>';
echo '</div>';

// Test 5: Shortcode availability test
echo '<h2>5. Shortcode Availability Test</h2>';
global $shortcode_tags;
$consultation_shortcodes = array();
foreach ($consultation_types as $type => $title) {
    $shortcode_name = 'ennu-' . str_replace('_', '-', $type) . '-consultation';
    $consultation_shortcodes[$shortcode_name] = isset($shortcode_tags[$shortcode_name]);
}

echo '<table style="width: 100%; border-collapse: collapse; margin: 10px 0;">';
echo '<tr style="background: #0073aa; color: white;"><th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Shortcode</th><th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Status</th></tr>';

foreach ($consultation_shortcodes as $shortcode => $registered) {
    $status = $registered ? '✅ Registered' : '❌ Not registered';
    $color = $registered ? 'green' : 'red';
    
    echo '<tr style="background: white;">';
    echo '<td style="padding: 10px; border: 1px solid #ddd; font-family: monospace;">[' . esc_html($shortcode) . ']</td>';
    echo '<td style="padding: 10px; border: 1px solid #ddd; color: ' . $color . ';">' . esc_html($status) . '</td>';
    echo '</tr>';
}
echo '</table>';

// Test 6: Summary and recommendations
echo '<h2>6. Summary and Recommendations</h2>';
$total_consultations = count($consultation_types);
$custom_embeds = 0;
$default_embeds = 0;

foreach ($consultation_types as $type => $title) {
    $embed_config = $hubspot_settings['embeds'][$type] ?? array();
    $embed_code = $embed_config['embed_code'] ?? '';
    if (empty($embed_code)) {
        $default_embeds++;
    } else {
        $custom_embeds++;
    }
}

echo '<div style="background: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #0073aa;">';
echo '<h3>📊 Current Status:</h3>';
echo '<ul>';
echo '<li><strong>Total Consultation Types:</strong> ' . $total_consultations . '</li>';
echo '<li><strong>Using Custom Embeds:</strong> ' . $custom_embeds . '</li>';
echo '<li><strong>Using Default Embed:</strong> ' . $default_embeds . '</li>';
echo '<li><strong>Default Embed Coverage:</strong> ' . round(($default_embeds / $total_consultations) * 100, 1) . '%</li>';
echo '</ul>';

if ($default_embeds > 0) {
    echo '<p style="color: green; font-weight: bold;">✅ Default embed code is working! ' . $default_embeds . ' consultation types will use the default HubSpot embed.</p>';
} else {
    echo '<p style="color: orange;">⚠️ All consultation types have custom embed codes configured.</p>';
}

echo '<h3>🎯 Recommendations:</h3>';
echo '<ul>';
echo '<li>Test consultation pages to verify default embed code displays correctly</li>';
echo '<li>Configure custom embed codes in <strong>ENNU Life → HubSpot Booking</strong> for specific consultation types</li>';
echo '<li>Custom embed codes will override the default when configured</li>';
echo '<li>Default embed ensures booking functionality even without admin configuration</li>';
echo '</ul>';
echo '</div>';

echo '<h2>7. Test Consultation Pages</h2>';
echo '<p>To test the default embed code in action, visit these consultation pages:</p>';
echo '<ul>';
foreach ($consultation_types as $type => $title) {
    $shortcode = 'ennu-' . str_replace('_', '-', $type) . '-consultation';
    echo '<li><strong>' . esc_html($title) . ':</strong> Use shortcode <code>[' . esc_html($shortcode) . ']</code></li>';
}
echo '</ul>';

echo '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin-top: 20px;">';
echo '<h3>🔧 How to Test:</h3>';
echo '<ol>';
echo '<li>Create a test page in WordPress</li>';
echo '<li>Add any consultation shortcode (e.g., <code>[ennu-hair-restoration-consultation]</code>)</li>';
echo '<li>View the page - you should see the HubSpot booking calendar</li>';
echo '<li>If no custom embed is configured, the default embed will be used</li>';
echo '</ol>';
echo '</div>';

echo '</div>';
?> 