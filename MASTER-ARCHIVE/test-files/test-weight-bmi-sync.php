<?php
/**
 * Test Weight and BMI Auto-Sync System
 * 
 * This test verifies that weight and BMI from global fields are properly
 * synced to the biomarker system and displayed on the user dashboard.
 */

// Load WordPress
require_once '../../../wp-load.php';

// Check if user is logged in
if ( ! is_user_logged_in() ) {
	echo '<h2>❌ User not logged in</h2>';
	echo '<p>Please log in to test the weight/BMI sync system.</p>';
	exit;
}

$user_id = get_current_user_id();
$user = get_userdata( $user_id );

echo '<h1>🧪 Weight & BMI Auto-Sync Test</h1>';
echo '<p><strong>Testing for user:</strong> ' . esc_html( $user->display_name ) . ' (ID: ' . $user_id . ')</p>';

// Test 1: Check if global height/weight data exists
echo '<h2>📊 Test 1: Global Height/Weight Data</h2>';
$height_weight_data = get_user_meta( $user_id, 'ennu_global_height_weight', true );
if ( ! empty( $height_weight_data ) && is_array( $height_weight_data ) ) {
	echo '<p style="color: green;">✅ Global height/weight data found:</p>';
	echo '<ul>';
	echo '<li><strong>Height:</strong> ' . esc_html( $height_weight_data['ft'] ?? 'N/A' ) . 'ft ' . esc_html( $height_weight_data['in'] ?? 'N/A' ) . 'in</li>';
	echo '<li><strong>Weight:</strong> ' . esc_html( $height_weight_data['weight'] ?? 'N/A' ) . ' lbs</li>';
	echo '</ul>';
} else {
	echo '<p style="color: orange;">⚠️ No global height/weight data found</p>';
	echo '<p>This means weight and BMI won\'t be available for auto-sync.</p>';
}

// Test 2: Check if auto-sync class exists
echo '<h2>🔧 Test 2: Auto-Sync System</h2>';
if ( class_exists( 'ENNU_Biomarker_Auto_Sync' ) ) {
	echo '<p style="color: green;">✅ ENNU_Biomarker_Auto_Sync class found</p>';
	
	// Test the sync
	$auto_sync = new ENNU_Biomarker_Auto_Sync();
	$sync_results = $auto_sync->sync_user_biomarkers( $user_id );
	
	if ( $sync_results['success'] ) {
		echo '<p style="color: green;">✅ Auto-sync completed successfully</p>';
		if ( ! empty( $sync_results['updated_fields'] ) ) {
			echo '<p><strong>Updated fields:</strong> ' . esc_html( implode( ', ', $sync_results['updated_fields'] ) ) . '</p>';
		}
	} else {
		echo '<p style="color: red;">❌ Auto-sync failed</p>';
		if ( ! empty( $sync_results['errors'] ) ) {
			echo '<p><strong>Errors:</strong> ' . esc_html( implode( ', ', $sync_results['errors'] ) ) . '</p>';
		}
	}
} else {
	echo '<p style="color: red;">❌ ENNU_Biomarker_Auto_Sync class not found</p>';
}

// Test 3: Check if biomarker data was created
echo '<h2>🧬 Test 3: Biomarker Data</h2>';
$biomarker_data = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
if ( is_array( $biomarker_data ) && ! empty( $biomarker_data ) ) {
	echo '<p style="color: green;">✅ Auto-sync biomarker data found:</p>';
	echo '<ul>';
	foreach ( $biomarker_data as $biomarker_key => $biomarker_info ) {
		echo '<li><strong>' . esc_html( ucfirst( $biomarker_key ) ) . ':</strong> ' . esc_html( $biomarker_info['value'] ) . ' ' . esc_html( $biomarker_info['unit'] ) . '</li>';
	}
	echo '</ul>';
} else {
	echo '<p style="color: orange;">⚠️ No auto-sync biomarker data found</p>';
}

// Test 4: Check if Biomarker Manager can retrieve the data
echo '<h2>📋 Test 4: Biomarker Manager Integration</h2>';
if ( class_exists( 'ENNU_Biomarker_Manager' ) ) {
	echo '<p style="color: green;">✅ ENNU_Biomarker_Manager class found</p>';
	
	// Test getting user biomarkers (should include auto-sync data)
	$user_biomarkers = ENNU_Biomarker_Manager::get_user_biomarkers( $user_id );
	
	if ( is_array( $user_biomarkers ) && ! empty( $user_biomarkers ) ) {
		echo '<p style="color: green;">✅ Biomarker Manager retrieved data:</p>';
		echo '<ul>';
		foreach ( $user_biomarkers as $biomarker_key => $biomarker_info ) {
			echo '<li><strong>' . esc_html( ucfirst( $biomarker_key ) ) . ':</strong> ' . esc_html( $biomarker_info['value'] ) . ' ' . esc_html( $biomarker_info['unit'] ) . '</li>';
		}
		echo '</ul>';
	} else {
		echo '<p style="color: orange;">⚠️ Biomarker Manager returned no data</p>';
	}
	
	// Test specific biomarker measurement data
	if ( ! empty( $height_weight_data ) ) {
		echo '<h3>🔍 Test 4a: Specific Biomarker Measurement Data</h3>';
		
		// Test weight
		$weight_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data( 'weight', $user_id );
		if ( isset( $weight_data['current_value'] ) && ! empty( $weight_data['current_value'] ) ) {
			echo '<p style="color: green;">✅ Weight measurement data: ' . esc_html( $weight_data['current_value'] ) . ' ' . esc_html( $weight_data['unit'] ) . '</p>';
		} else {
			echo '<p style="color: orange;">⚠️ Weight measurement data not available</p>';
		}
		
		// Test BMI
		$bmi_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data( 'bmi', $user_id );
		if ( isset( $bmi_data['current_value'] ) && ! empty( $bmi_data['current_value'] ) ) {
			echo '<p style="color: green;">✅ BMI measurement data: ' . esc_html( $bmi_data['current_value'] ) . ' ' . esc_html( $bmi_data['unit'] ) . '</p>';
		} else {
			echo '<p style="color: orange;">⚠️ BMI measurement data not available</p>';
		}
	}
} else {
	echo '<p style="color: red;">❌ ENNU_Biomarker_Manager class not found</p>';
}

// Test 5: Summary
echo '<h2>📈 Test 5: Summary</h2>';
$has_global_data = ! empty( $height_weight_data ) && is_array( $height_weight_data );
$has_auto_sync = class_exists( 'ENNU_Biomarker_Auto_Sync' );
$has_biomarker_manager = class_exists( 'ENNU_Biomarker_Manager' );
$has_synced_data = is_array( $biomarker_data ) && ! empty( $biomarker_data );

if ( $has_global_data && $has_auto_sync && $has_biomarker_manager && $has_synced_data ) {
	echo '<p style="color: green; font-size: 18px; font-weight: bold;">🎉 SUCCESS: Weight and BMI auto-sync system is working correctly!</p>';
	echo '<p>Users will now see their current weight and BMI values in the biomarker dashboard.</p>';
} else {
	echo '<p style="color: orange; font-size: 18px; font-weight: bold;">⚠️ PARTIAL: Some components are missing or not working</p>';
	echo '<ul>';
	echo '<li>Global data: ' . ( $has_global_data ? '✅' : '❌' ) . '</li>';
	echo '<li>Auto-sync class: ' . ( $has_auto_sync ? '✅' : '❌' ) . '</li>';
	echo '<li>Biomarker Manager: ' . ( $has_biomarker_manager ? '✅' : '❌' ) . '</li>';
	echo '<li>Synced data: ' . ( $has_synced_data ? '✅' : '❌' ) . '</li>';
	echo '</ul>';
}

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ul>';
echo '<li>Visit the user dashboard to see weight and BMI in the biomarker panels</li>';
echo '<li>Update global height/weight data to test real-time sync</li>';
echo '<li>Check that weight and BMI show current values instead of "No Data Available"</li>';
echo '</ul>';
?> 