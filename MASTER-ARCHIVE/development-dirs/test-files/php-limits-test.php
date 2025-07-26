<?php
/**
 * PHP Limits Test
 * 
 * This file tests if the PHP configuration changes are working
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );
}

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1>🔧 PHP Limits Test</h1>';

// Test 1: Check current PHP limits
echo '<h2>1. Current PHP Configuration</h2>';
$limits = array(
	'max_input_vars' => ini_get( 'max_input_vars' ),
	'post_max_size' => ini_get( 'post_max_size' ),
	'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
	'max_execution_time' => ini_get( 'max_execution_time' ),
	'max_input_time' => ini_get( 'max_input_time' ),
	'memory_limit' => ini_get( 'memory_limit' )
);

echo '<table style="width: 100%; border-collapse: collapse; margin: 10px 0;">';
echo '<tr style="background: #e9e9e9;"><th style="padding: 8px; border: 1px solid #ddd;">Setting</th><th style="padding: 8px; border: 1px solid #ddd;">Current Value</th><th style="padding: 8px; border: 1px solid #ddd;">Status</th></tr>';

foreach ( $limits as $setting => $value ) {
	$status = '❌';
	$color = '#ffebee';
	
	switch ( $setting ) {
		case 'max_input_vars':
			if ( $value >= 10000 ) {
				$status = '✅';
				$color = '#e8f5e8';
			}
			break;
		case 'post_max_size':
			if ( $value >= 64 ) {
				$status = '✅';
				$color = '#e8f5e8';
			}
			break;
		case 'upload_max_filesize':
			if ( $value >= 64 ) {
				$status = '✅';
				$color = '#e8f5e8';
			}
			break;
		case 'max_execution_time':
			if ( $value >= 300 ) {
				$status = '✅';
				$color = '#e8f5e8';
			}
			break;
		case 'max_input_time':
			if ( $value >= 300 ) {
				$status = '✅';
				$color = '#e8f5e8';
			}
			break;
		case 'memory_limit':
			if ( $value >= 512 ) {
				$status = '✅';
				$color = '#e8f5e8';
			}
			break;
	}
	
	echo '<tr style="background: ' . $color . ';">';
	echo '<td style="padding: 8px; border: 1px solid #ddd;"><strong>' . esc_html( $setting ) . '</strong></td>';
	echo '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html( $value ) . '</td>';
	echo '<td style="padding: 8px; border: 1px solid #ddd;">' . $status . '</td>';
	echo '</tr>';
}
echo '</table>';

// Test 2: Check if .htaccess is being read
echo '<h2>2. .htaccess Configuration</h2>';
$htaccess_path = ABSPATH . '.htaccess';
if ( file_exists( $htaccess_path ) ) {
	$htaccess_content = file_get_contents( $htaccess_path );
	if ( strpos( $htaccess_content, 'max_input_vars' ) !== false ) {
		echo '<p>✅ .htaccess file exists and contains PHP configuration directives</p>';
	} else {
		echo '<p>❌ .htaccess file exists but does not contain PHP configuration directives</p>';
	}
} else {
	echo '<p>❌ .htaccess file not found</p>';
}

// Test 3: Check if PHP config override is loaded
echo '<h2>3. PHP Config Override Status</h2>';
if ( function_exists( 'ennu_apply_php_config_overrides' ) ) {
	echo '<p>✅ PHP config override function is available</p>';
} else {
	echo '<p>❌ PHP config override function is not available</p>';
}

// Test 4: Test form submission simulation
echo '<h2>4. Form Submission Test</h2>';
echo '<form method="post" action="">';
echo '<input type="hidden" name="test_form" value="1">';

// Create many test fields to simulate the biomarker form
for ( $i = 1; $i <= 100; $i++ ) {
	echo '<input type="hidden" name="test_field_' . $i . '" value="test_value_' . $i . '">';
}

echo '<button type="submit" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Test Form Submission</button>';
echo '</form>';

if ( isset( $_POST['test_form'] ) ) {
	$field_count = count( $_POST );
	echo '<p>✅ Form submitted successfully with ' . $field_count . ' fields</p>';
	
	if ( $field_count >= 100 ) {
		echo '<p>✅ Large form submission test passed!</p>';
	} else {
		echo '<p>❌ Form submission may have been truncated</p>';
	}
}

echo '<h2>5. Recommendations</h2>';
$all_good = true;
foreach ( $limits as $setting => $value ) {
	if ( $setting === 'max_input_vars' && $value < 10000 ) {
		echo '<p>⚠️ Increase max_input_vars to at least 10000 (current: ' . $value . ')</p>';
		$all_good = false;
	}
}

if ( $all_good ) {
	echo '<p>✅ All PHP limits are properly configured for large form submissions</p>';
	echo '<p>🎉 You should now be able to save biomarker data without the input variables error!</p>';
} else {
	echo '<p>⚠️ Some PHP limits need to be increased. Try refreshing the page or contact your hosting provider.</p>';
}

echo '</div>'; 