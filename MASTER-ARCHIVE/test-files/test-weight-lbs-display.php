<?php
/**
 * Test Weight Display in Pounds
 * 
 * This test verifies that weight is now displaying in pounds (lbs) instead of kilograms
 * in the biomarker dashboard.
 */

// Load WordPress
require_once '../../../wp-load.php';

// Check if user is logged in
if ( ! is_user_logged_in() ) {
	echo '<h2>❌ User not logged in</h2>';
	echo '<p>Please log in to test the weight display system.</p>';
	exit;
}

$user_id = get_current_user_id();
$user = get_userdata( $user_id );

echo '<h1>⚖️ Weight Display Test (Pounds)</h1>';
echo '<p><strong>Testing for user:</strong> ' . esc_html( $user->display_name ) . ' (ID: ' . $user_id . ')</p>';

// Test 1: Check global height/weight data
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
	echo '<p>This means weight won\'t be available for display.</p>';
}

// Test 2: Check auto-sync biomarker data
echo '<h2>🔄 Test 2: Auto-Sync Biomarker Data</h2>';
$auto_sync_data = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
if ( is_array( $auto_sync_data ) && ! empty( $auto_sync_data ) ) {
	echo '<p style="color: green;">✅ Auto-sync biomarker data found:</p>';
	echo '<ul>';
	foreach ( $auto_sync_data as $biomarker_key => $biomarker_info ) {
		echo '<li><strong>' . esc_html( ucfirst( $biomarker_key ) ) . ':</strong> ' . esc_html( $biomarker_info['value'] ) . ' ' . esc_html( $biomarker_info['unit'] ) . '</li>';
	}
	echo '</ul>';
	
	// Check specifically for weight
	if ( isset( $auto_sync_data['weight'] ) ) {
		$weight_data = $auto_sync_data['weight'];
		if ( $weight_data['unit'] === 'lbs' ) {
			echo '<p style="color: green;">✅ Weight is correctly stored in pounds (lbs)</p>';
		} else {
			echo '<p style="color: red;">❌ Weight is stored in ' . esc_html( $weight_data['unit'] ) . ' instead of lbs</p>';
		}
	} else {
		echo '<p style="color: orange;">⚠️ No weight data found in auto-sync</p>';
	}
} else {
	echo '<p style="color: orange;">⚠️ No auto-sync biomarker data found</p>';
}

// Test 3: Check Biomarker Manager integration
echo '<h2>📋 Test 3: Biomarker Manager Integration</h2>';
if ( class_exists( 'ENNU_Biomarker_Manager' ) ) {
	echo '<p style="color: green;">✅ ENNU_Biomarker_Manager class found</p>';
	
	// Test getting user biomarkers
	$user_biomarkers = ENNU_Biomarker_Manager::get_user_biomarkers( $user_id );
	
	if ( is_array( $user_biomarkers ) && ! empty( $user_biomarkers ) ) {
		echo '<p style="color: green;">✅ Biomarker Manager retrieved data:</p>';
		echo '<ul>';
		foreach ( $user_biomarkers as $biomarker_key => $biomarker_info ) {
			echo '<li><strong>' . esc_html( ucfirst( $biomarker_key ) ) . ':</strong> ' . esc_html( $biomarker_info['value'] ) . ' ' . esc_html( $biomarker_info['unit'] ) . '</li>';
		}
		echo '</ul>';
		
		// Check specifically for weight
		if ( isset( $user_biomarkers['weight'] ) ) {
			$weight_data = $user_biomarkers['weight'];
			if ( $weight_data['unit'] === 'lbs' ) {
				echo '<p style="color: green;">✅ Weight is correctly displayed in pounds (lbs)</p>';
			} else {
				echo '<p style="color: red;">❌ Weight is displayed in ' . esc_html( $weight_data['unit'] ) . ' instead of lbs</p>';
			}
		} else {
			echo '<p style="color: orange;">⚠️ No weight data found in Biomarker Manager</p>';
		}
	} else {
		echo '<p style="color: orange;">⚠️ Biomarker Manager returned no data</p>';
	}
	
	// Test specific biomarker measurement data
	if ( ! empty( $height_weight_data ) ) {
		echo '<h3>🔍 Test 3a: Specific Weight Measurement Data</h3>';
		
		$weight_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data( 'weight', $user_id );
		if ( isset( $weight_data['current_value'] ) && ! empty( $weight_data['current_value'] ) ) {
			echo '<p style="color: green;">✅ Weight measurement data: ' . esc_html( $weight_data['current_value'] ) . ' ' . esc_html( $weight_data['unit'] ) . '</p>';
			
			if ( $weight_data['unit'] === 'lbs' ) {
				echo '<p style="color: green;">✅ Weight measurement is correctly in pounds (lbs)</p>';
			} else {
				echo '<p style="color: red;">❌ Weight measurement is in ' . esc_html( $weight_data['unit'] ) . ' instead of lbs</p>';
			}
		} else {
			echo '<p style="color: orange;">⚠️ Weight measurement data not available</p>';
		}
	}
} else {
	echo '<p style="color: red;">❌ ENNU_Biomarker_Manager class not found</p>';
}

// Test 4: Check biomarker range configuration
echo '<h2>⚙️ Test 4: Biomarker Range Configuration</h2>';
if ( class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
	echo '<p style="color: green;">✅ ENNU_Recommended_Range_Manager class found</p>';
	
	$range_manager = new ENNU_Recommended_Range_Manager();
	$user_data = array(
		'age' => get_user_meta( $user_id, 'ennu_age', true ) ?: 35,
		'gender' => get_user_meta( $user_id, 'ennu_gender', true ) ?: 'male'
	);
	
	$weight_range = $range_manager->get_recommended_range( 'weight', $user_data );
	if ( ! isset( $weight_range['error'] ) ) {
		echo '<p style="color: green;">✅ Weight range configuration found:</p>';
		echo '<ul>';
		echo '<li><strong>Unit:</strong> ' . esc_html( $weight_range['unit'] ) . '</li>';
		echo '<li><strong>Optimal Range:</strong> ' . esc_html( $weight_range['optimal_min'] ) . ' - ' . esc_html( $weight_range['optimal_max'] ) . ' ' . esc_html( $weight_range['unit'] ) . '</li>';
		echo '<li><strong>Normal Range:</strong> ' . esc_html( $weight_range['normal_min'] ) . ' - ' . esc_html( $weight_range['normal_max'] ) . ' ' . esc_html( $weight_range['unit'] ) . '</li>';
		echo '</ul>';
		
		if ( $weight_range['unit'] === 'lbs' ) {
			echo '<p style="color: green;">✅ Weight range configuration is correctly in pounds (lbs)</p>';
		} else {
			echo '<p style="color: red;">❌ Weight range configuration is in ' . esc_html( $weight_range['unit'] ) . ' instead of lbs</p>';
		}
	} else {
		echo '<p style="color: orange;">⚠️ Weight range configuration error: ' . esc_html( $weight_range['error'] ) . '</p>';
	}
} else {
	echo '<p style="color: red;">❌ ENNU_Recommended_Range_Manager class not found</p>';
}

// Test 5: Summary
echo '<h2>📈 Test 5: Summary</h2>';
$has_global_data = ! empty( $height_weight_data ) && is_array( $height_weight_data );
$has_auto_sync = class_exists( 'ENNU_Biomarker_Auto_Sync' );
$has_biomarker_manager = class_exists( 'ENNU_Biomarker_Manager' );
$has_range_manager = class_exists( 'ENNU_Recommended_Range_Manager' );
$weight_in_lbs = false;

// Check if weight is in lbs in any of the data sources
if ( $has_global_data ) {
	$auto_sync_data = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
	if ( is_array( $auto_sync_data ) && isset( $auto_sync_data['weight'] ) ) {
		$weight_in_lbs = ( $auto_sync_data['weight']['unit'] === 'lbs' );
	}
}

if ( $has_global_data && $has_auto_sync && $has_biomarker_manager && $has_range_manager && $weight_in_lbs ) {
	echo '<p style="color: green; font-size: 18px; font-weight: bold;">🎉 SUCCESS: Weight is now displaying in pounds (lbs) in the biomarker dashboard!</p>';
	echo '<p>Users will now see their weight in pounds instead of kilograms.</p>';
} else {
	echo '<p style="color: orange; font-size: 18px; font-weight: bold;">⚠️ PARTIAL: Some components are missing or not working</p>';
	echo '<ul>';
	echo '<li>Global data: ' . ( $has_global_data ? '✅' : '❌' ) . '</li>';
	echo '<li>Auto-sync class: ' . ( $has_auto_sync ? '✅' : '❌' ) . '</li>';
	echo '<li>Biomarker Manager: ' . ( $has_biomarker_manager ? '✅' : '❌' ) . '</li>';
	echo '<li>Range Manager: ' . ( $has_range_manager ? '✅' : '❌' ) . '</li>';
	echo '<li>Weight in lbs: ' . ( $weight_in_lbs ? '✅' : '❌' ) . '</li>';
	echo '</ul>';
}

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ul>';
echo '<li>Visit the user dashboard to see weight displayed in pounds (lbs)</li>';
echo '<li>Check that weight ranges are appropriate for pounds</li>';
echo '<li>Verify that BMI calculation still works correctly</li>';
echo '</ul>';

echo '<hr>';
echo '<p><strong>Expected Results:</strong></p>';
echo '<ul>';
echo '<li>Weight should display in pounds (lbs) instead of kilograms (kg)</li>';
echo '<li>Weight ranges should be appropriate for pounds (e.g., 100-220 lbs)</li>';
echo '<li>BMI should still display in kg/m² (standard unit)</li>';
echo '<li>All calculations should work correctly with the new units</li>';
echo '</ul>';
?> 