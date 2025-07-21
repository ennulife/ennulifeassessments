<?php
/**
 * ENNU Life Comprehensive Shortcode Verification Script
 * 
 * This script verifies that ALL shortcodes are properly registered and match page creation.
 * 
 * @package ENNU_Life
 * @version 62.1.2
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/wp-load.php' );

// Security check
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied.' );
}

echo '<h1>ENNU Life Comprehensive Shortcode Verification - v62.1.2</h1>';
echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>';

// Check if plugin is active
if ( ! is_plugin_active( 'ennulifeassessments/ennu-life-plugin.php' ) ) {
	echo '<p class="error">❌ Plugin is not active!</p>';
	exit;
}

echo '<p class="success">✅ Plugin is active</p>';

// Get plugin instance and shortcodes
$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
$shortcodes = $plugin->get_shortcodes();
$all_definitions = $shortcodes->get_all_assessment_definitions();

echo '<h2>Assessment Definitions Found</h2>';
echo '<table>';
echo '<tr><th>Config File</th><th>Assessment Key</th><th>Title</th></tr>';

foreach ( $all_definitions as $key => $config ) {
	echo '<tr>';
	echo '<td>' . $key . '.php</td>';
	echo '<td>' . $key . '</td>';
	echo '<td>' . (isset($config['title']) ? $config['title'] : 'No title') . '</td>';
	echo '</tr>';
}
echo '</table>';

// Check registered shortcodes
global $shortcode_tags;
echo '<h2>Registered Shortcodes Analysis</h2>';

// Expected shortcodes based on config files
$expected_assessment_shortcodes = array();
$expected_results_shortcodes = array();
$expected_details_shortcodes = array();
$expected_consultation_shortcodes = array(
	'ennu-hair-consultation',
	'ennu-ed-treatment-consultation',
	'ennu-weight-loss-consultation',
	'ennu-health-optimization-consultation',
	'ennu-skin-consultation',
	'ennu-health-consultation',
	'ennu-hormone-consultation',
	'ennu-menopause-consultation',
	'ennu-testosterone-consultation',
	'ennu-sleep-consultation'
);
$expected_core_shortcodes = array(
	'ennu-user-dashboard',
	'ennu-assessment-results'
);

foreach ( $all_definitions as $key => $config ) {
	$expected_assessment_shortcodes[] = 'ennu-' . $key;
	$expected_results_shortcodes[] = 'ennu-' . $key . '-results';
	$expected_details_shortcodes[] = 'ennu-' . $key . '-assessment-details';
}

echo '<h3>Assessment Shortcodes</h3>';
echo '<table>';
echo '<tr><th>Expected Shortcode</th><th>Registered</th><th>Status</th></tr>';

foreach ( $expected_assessment_shortcodes as $shortcode ) {
	$registered = isset( $shortcode_tags[ $shortcode ] );
	$status = $registered ? '<span class="success">✅ Registered</span>' : '<span class="error">❌ Missing</span>';
	echo '<tr>';
	echo '<td>' . $shortcode . '</td>';
	echo '<td>' . ($registered ? 'Yes' : 'No') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
}
echo '</table>';

echo '<h3>Results Shortcodes</h3>';
echo '<table>';
echo '<tr><th>Expected Shortcode</th><th>Registered</th><th>Status</th></tr>';

foreach ( $expected_results_shortcodes as $shortcode ) {
	$registered = isset( $shortcode_tags[ $shortcode ] );
	$status = $registered ? '<span class="success">✅ Registered</span>' : '<span class="error">❌ Missing</span>';
	echo '<tr>';
	echo '<td>' . $shortcode . '</td>';
	echo '<td>' . ($registered ? 'Yes' : 'No') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
}
echo '</table>';

echo '<h3>Details Shortcodes</h3>';
echo '<table>';
echo '<tr><th>Expected Shortcode</th><th>Registered</th><th>Status</th></tr>';

foreach ( $expected_details_shortcodes as $shortcode ) {
	$registered = isset( $shortcode_tags[ $shortcode ] );
	$status = $registered ? '<span class="success">✅ Registered</span>' : '<span class="error">❌ Missing</span>';
	echo '<tr>';
	echo '<td>' . $shortcode . '</td>';
	echo '<td>' . ($registered ? 'Yes' : 'No') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
}
echo '</table>';

echo '<h3>Consultation Shortcodes</h3>';
echo '<table>';
echo '<tr><th>Expected Shortcode</th><th>Registered</th><th>Status</th></tr>';

foreach ( $expected_consultation_shortcodes as $shortcode ) {
	$registered = isset( $shortcode_tags[ $shortcode ] );
	$status = $registered ? '<span class="success">✅ Registered</span>' : '<span class="error">❌ Missing</span>';
	echo '<tr>';
	echo '<td>' . $shortcode . '</td>';
	echo '<td>' . ($registered ? 'Yes' : 'No') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
}
echo '</table>';

echo '<h3>Core Shortcodes</h3>';
echo '<table>';
echo '<tr><th>Expected Shortcode</th><th>Registered</th><th>Status</th></tr>';

foreach ( $expected_core_shortcodes as $shortcode ) {
	$registered = isset( $shortcode_tags[ $shortcode ] );
	$status = $registered ? '<span class="success">✅ Registered</span>' : '<span class="error">❌ Missing</span>';
	echo '<tr>';
	echo '<td>' . $shortcode . '</td>';
	echo '<td>' . ($registered ? 'Yes' : 'No') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
}
echo '</table>';

// Summary
$total_expected = count($expected_assessment_shortcodes) + count($expected_results_shortcodes) + 
                  count($expected_details_shortcodes) + count($expected_consultation_shortcodes) + 
                  count($expected_core_shortcodes);

$total_registered = 0;
foreach ( array_merge($expected_assessment_shortcodes, $expected_results_shortcodes, 
                      $expected_details_shortcodes, $expected_consultation_shortcodes, 
                      $expected_core_shortcodes) as $shortcode ) {
	if ( isset( $shortcode_tags[ $shortcode ] ) ) {
		$total_registered++;
	}
}

echo '<h2>Summary</h2>';
echo '<p><strong>Total Expected Shortcodes:</strong> ' . $total_expected . '</p>';
echo '<p><strong>Total Registered Shortcodes:</strong> ' . $total_registered . '</p>';
echo '<p><strong>Success Rate:</strong> ' . round(($total_registered / $total_expected) * 100, 1) . '%</p>';

if ( $total_registered === $total_expected ) {
	echo '<p class="success">🎉 ALL SHORTCODES ARE PROPERLY REGISTERED!</p>';
} else {
	echo '<p class="error">⚠️ SOME SHORTCODES ARE MISSING!</p>';
}

// Test page creation shortcodes
echo '<h2>Page Creation Shortcode Verification</h2>';
echo '<p class="info">Verifying that page creation uses the correct shortcodes...</p>';

$page_creation_shortcodes = array(
	'[ennu-user-dashboard]',
	'[ennu-welcome]',
	'[ennu-assessment-results]',
	'[ennu-hair]',
	'[ennu-ed-treatment]',
	'[ennu-weight-loss]',
	'[ennu-health]',
	'[ennu-health-optimization]',
	'[ennu-skin]',
	'[ennu-sleep]',
	'[ennu-hormone]',
	'[ennu-menopause]',
	'[ennu-testosterone]',
	'[ennu-hair-results]',
	'[ennu-ed-treatment-results]',
	'[ennu-weight-loss-results]',
	'[ennu-health-results]',
	'[ennu-health-optimization-results]',
	'[ennu-skin-results]',
	'[ennu-sleep-results]',
	'[ennu-hormone-results]',
	'[ennu-menopause-results]',
	'[ennu-testosterone-results]',
	'[ennu-hair-assessment-details]',
	'[ennu-ed-treatment-assessment-details]',
	'[ennu-weight-loss-assessment-details]',
	'[ennu-health-assessment-details]',
	'[ennu-health-optimization-assessment-details]',
	'[ennu-skin-assessment-details]',
	'[ennu-sleep-assessment-details]',
	'[ennu-hormone-assessment-details]',
	'[ennu-menopause-assessment-details]',
	'[ennu-testosterone-assessment-details]',
	'[ennu-hair-consultation]',
	'[ennu-ed-treatment-consultation]',
	'[ennu-weight-loss-consultation]',
	'[ennu-health-optimization-consultation]',
	'[ennu-skin-consultation]',
	'[ennu-health-consultation]',
	'[ennu-hormone-consultation]',
	'[ennu-menopause-consultation]',
	'[ennu-testosterone-consultation]',
	'[ennu-sleep-consultation]'
);

echo '<table>';
echo '<tr><th>Page Creation Shortcode</th><th>Registered</th><th>Status</th></tr>';

foreach ( $page_creation_shortcodes as $shortcode ) {
	$shortcode_name = str_replace( array('[', ']'), '', $shortcode );
	$registered = isset( $shortcode_tags[ $shortcode_name ] );
	$status = $registered ? '<span class="success">✅ Registered</span>' : '<span class="error">❌ Missing</span>';
	echo '<tr>';
	echo '<td>' . $shortcode . '</td>';
	echo '<td>' . ($registered ? 'Yes' : 'No') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
}
echo '</table>';

echo '<h2>Verification Complete</h2>';
echo '<p class="info">This comprehensive verification ensures all shortcodes are properly registered and match the page creation system.</p>';
?> 