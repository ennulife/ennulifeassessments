<?php
/**
 * ENNU Biomarker Auto-Sync System
 * Automatically syncs global fields to corresponding biomarker fields
 *
 * @package ENNU_Life
 * @version 64.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Biomarker_Auto_Sync {

	/**
	 * Initialize the auto-sync system
	 */
	public function __construct() {
		add_action( 'user_register', array( $this, 'sync_user_biomarkers' ) );
		add_action( 'profile_update', array( $this, 'sync_user_biomarkers' ) );
		add_action( 'wp_ajax_ennu_sync_biomarkers', array( $this, 'ajax_sync_biomarkers' ) );
		add_action( 'wp_ajax_nopriv_ennu_sync_biomarkers', array( $this, 'ajax_sync_biomarkers' ) );
		
		// Trigger auto-sync when user dashboard loads to ensure weight/BMI are current
		add_action( 'wp_ajax_ennu_load_user_dashboard', array( $this, 'ensure_biomarker_sync' ) );
		add_action( 'wp_ajax_nopriv_ennu_load_user_dashboard', array( $this, 'ensure_biomarker_sync' ) );
	}

	/**
	 * Sync global fields to biomarker fields for a user
	 *
	 * @param int $user_id User ID
	 * @return array Sync results
	 */
	public function sync_user_biomarkers( $user_id ) {
		$sync_results = array(
			'success' => true,
			'updated_fields' => array(),
			'errors' => array(),
			'synced_at' => current_time( 'mysql' ),
		);

		try {
			// Get current biomarker data
			$existing_biomarkers = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
			if ( ! is_array( $existing_biomarkers ) ) {
				$existing_biomarkers = array();
			}

			// Initialize array to collect all updated biomarkers
			$updated_biomarkers = array();

			// Sync height and weight from global fields
			$height_weight_sync = $this->sync_height_weight_biomarkers( $user_id, $updated_biomarkers );
			if ( $height_weight_sync['success'] ) {
				$sync_results['updated_fields'] = array_merge( $sync_results['updated_fields'], $height_weight_sync['updated_fields'] );
			} else {
				$sync_results['errors'] = array_merge( $sync_results['errors'], $height_weight_sync['errors'] );
			}

			// Sync age and gender from global fields
			$demographics_sync = $this->sync_demographics_biomarkers( $user_id, $updated_biomarkers );
			if ( $demographics_sync['success'] ) {
				$sync_results['updated_fields'] = array_merge( $sync_results['updated_fields'], $demographics_sync['updated_fields'] );
			} else {
				$sync_results['errors'] = array_merge( $sync_results['errors'], $demographics_sync['errors'] );
			}

			// Sync health goals to biomarker targets
			$goals_sync = $this->sync_health_goals_biomarkers( $user_id, $updated_biomarkers );
			if ( $goals_sync['success'] ) {
				$sync_results['updated_fields'] = array_merge( $sync_results['updated_fields'], $goals_sync['updated_fields'] );
			} else {
				$sync_results['errors'] = array_merge( $sync_results['errors'], $goals_sync['errors'] );
			}

			// Update the biomarker data using the centralized manager
			if ( ! empty( $updated_biomarkers ) ) {
				$update_success = ENNU_Biomarker_Manager::save_user_biomarkers( $user_id, $updated_biomarkers, 'auto_sync' );
				if ( ! $update_success ) {
					$sync_results['errors'][] = 'Failed to save auto-synced biomarker data via manager';
					$sync_results['success'] = false;
				}
			}

			// Log sync results
			$this->log_sync_results( $user_id, $sync_results );

		} catch ( Exception $e ) {
			$sync_results['errors'][] = 'Sync error: ' . $e->getMessage();
			$sync_results['success'] = false;
			error_log( 'ENNU Biomarker Auto-Sync Error: ' . $e->getMessage() );
		}

		return $sync_results;
	}

	/**
	 * Sync height and weight data to biomarker fields
	 *
	 * @param int $user_id User ID
	 * @param array &$updated_biomarkers Array to be populated with new data
	 * @return array Sync results
	 */
	private function sync_height_weight_biomarkers( $user_id, &$updated_biomarkers ) {
		$results = array(
			'success' => true,
			'updated_fields' => array(),
			'errors' => array(),
		);

		// Get height/weight from global fields
		$height_weight_data = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		
		if ( ! empty( $height_weight_data ) && is_array( $height_weight_data ) ) {
			$ft = isset( $height_weight_data['ft'] ) ? intval( $height_weight_data['ft'] ) : 0;
			$in = isset( $height_weight_data['in'] ) ? intval( $height_weight_data['in'] ) : 0;
			$weight_lbs = isset( $height_weight_data['weight'] ) ? floatval( $height_weight_data['weight'] ) : 0;

			// Calculate height in inches and centimeters
			$height_inches = ( $ft * 12 ) + $in;
			$height_cm = $height_inches * 2.54;
			$weight_kg = $weight_lbs * 0.453592;

			// Calculate BMI
			$bmi = 0;
			if ( $height_inches > 0 && $weight_lbs > 0 ) {
				$bmi = ( $weight_lbs / ( $height_inches * $height_inches ) ) * 703;
			}

			// Update biomarker fields
			$updated = false;

			// Height biomarker
			if ( $height_cm > 0 ) {
				$updated_biomarkers['height'] = array(
					'value' => round( $height_cm, 1 ),
					'unit' => 'cm',
				);
				$results['updated_fields'][] = 'height';
				$updated = true;
			}

			// Weight biomarker
			if ( $weight_lbs > 0 ) {
				$updated_biomarkers['weight'] = array(
					'value' => round( $weight_lbs, 1 ),
					'unit' => 'lbs',
				);
				$results['updated_fields'][] = 'weight';
				$updated = true;
			}

			// BMI biomarker (BMI calculation works with lbs and inches)
			if ( $bmi > 0 ) {
				$updated_biomarkers['bmi'] = array(
					'value' => round( $bmi, 1 ),
					                'unit' => '',
				);
				$results['updated_fields'][] = 'bmi';
				$updated = true;
			}

			if ( $updated ) {
				// Also update individual user meta fields for compatibility
				update_user_meta( $user_id, 'ennu_height', $height_cm );
				update_user_meta( $user_id, 'ennu_weight', $weight_lbs ); // Store in lbs
				update_user_meta( $user_id, 'ennu_bmi', $bmi );
				update_user_meta( $user_id, 'ennu_calculated_bmi', $bmi ); // Also save to the key the dashboard expects
				
				// Save to combined height/weight field that dashboard expects
				$height_weight_data = array(
					'ft' => $ft,
					'in' => $in,
					'weight' => $weight_lbs
				);
				update_user_meta( $user_id, 'ennu_global_height_weight', $height_weight_data );
			}
		}

		return $results;
	}

	/**
	 * Sync demographics data to biomarker fields
	 *
	 * @param int $user_id User ID
	 * @param array &$updated_biomarkers Array to be populated with new data
	 * @return array Sync results
	 */
	private function sync_demographics_biomarkers( $user_id, &$updated_biomarkers ) {
		$results = array(
			'success' => true,
			'updated_fields' => array(),
			'errors' => array(),
		);

		// Get age data
		$age = get_user_meta( $user_id, 'ennu_age', true );
		if ( ! empty( $age ) && is_numeric( $age ) ) {
			$updated_biomarkers['age'] = array(
				'value' => intval( $age ),
				'unit' => 'years',
			);
			$results['updated_fields'][] = 'age';
		}

		// Get gender data
		$gender = get_user_meta( $user_id, 'ennu_gender', true );
		if ( ! empty( $gender ) ) {
			$updated_biomarkers['gender'] = array(
				'value' => $gender,
				'unit' => 'categorical',
			);
			$results['updated_fields'][] = 'gender';
		}

		return $results;
	}

	/**
	 * Sync health goals to biomarker targets
	 *
	 * @param int $user_id User ID
	 * @param array &$updated_biomarkers Array to be populated with new data
	 * @return array Sync results
	 */
	private function sync_health_goals_biomarkers( $user_id, &$updated_biomarkers ) {
		$results = array(
			'success' => true,
			'updated_fields' => array(),
			'errors' => array(),
		);

		// Get health goals
		$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
		
		if ( ! empty( $health_goals ) && is_array( $health_goals ) ) {
			// Map health goals to biomarker targets
			$goal_biomarker_mapping = array(
				'weight_loss' => array(
					'biomarker' => 'bmi',
					'target_type' => 'decrease',
					'target_value' => 24.9, // Healthy BMI target
					'notes' => 'Target healthy BMI for weight loss goal',
				),
				'energy' => array(
					'biomarker' => 'vitamin_d',
					'target_type' => 'increase',
					'target_value' => 30, // ng/mL
					'notes' => 'Target optimal Vitamin D for energy',
				),
				'strength' => array(
					'biomarker' => 'testosterone',
					'target_type' => 'increase',
					'target_value' => 600, // ng/dL for men
					'notes' => 'Target optimal testosterone for strength',
				),
				'longevity' => array(
					'biomarker' => 'hs_crp',
					'target_type' => 'decrease',
					'target_value' => 1.0, // mg/L
					'notes' => 'Target low inflammation for longevity',
				),
			);

			foreach ( $health_goals as $goal ) {
				if ( isset( $goal_biomarker_mapping[ $goal ] ) ) {
					$mapping = $goal_biomarker_mapping[ $goal ];
					$biomarker_key = $mapping['biomarker'];

					// Create or update biomarker target
					if ( ! isset( $updated_biomarkers['targets'] ) ) {
						$updated_biomarkers['targets'] = array();
					}

					$updated_biomarkers['targets'][ $biomarker_key ] = array(
						'goal' => $goal,
						'target_type' => $mapping['target_type'],
						'target_value' => $mapping['target_value'],
						'unit' => $this->get_biomarker_unit( $biomarker_key ),
						'notes' => $mapping['notes'],
					);

					$results['updated_fields'][] = 'target_' . $biomarker_key;
				}
			}
		}

		return $results;
	}

	/**
	 * Get biomarker unit
	 *
	 * @param string $biomarker_key Biomarker key
	 * @return string Unit
	 */
	private function get_biomarker_unit( $biomarker_key ) {
		$units = array(
			'height' => 'cm',
			'weight' => 'lbs',
			'bmi' => '',
			'age' => 'years',
			'gender' => 'categorical',
			'vitamin_d' => 'ng/mL',
			'testosterone' => 'ng/dL',
			'hs_crp' => 'mg/L',
		);

		return isset( $units[ $biomarker_key ] ) ? $units[ $biomarker_key ] : '';
	}

	/**
	 * Log sync results
	 *
	 * @param int $user_id User ID
	 * @param array $results Sync results
	 */
	private function log_sync_results( $user_id, $results ) {
		$log_entry = array(
			'user_id' => $user_id,
			'timestamp' => current_time( 'mysql' ),
			'success' => $results['success'],
			'updated_fields' => $results['updated_fields'],
			'errors' => $results['errors'],
		);

		// Store in user meta for tracking
		$sync_log = get_user_meta( $user_id, 'ennu_biomarker_sync_log', true );
		if ( ! is_array( $sync_log ) ) {
			$sync_log = array();
		}

		// Keep only last 10 entries
		$sync_log = array_slice( array_merge( array( $log_entry ), $sync_log ), 0, 10 );
		update_user_meta( $user_id, 'ennu_biomarker_sync_log', $sync_log );

		// Log to error log if there are errors
		if ( ! empty( $results['errors'] ) ) {
			// REMOVED: error_log( 'ENNU Biomarker Auto-Sync Errors for user ' . $user_id . ': ' . implode( ', ', $results['errors'] ) );
		}
	}

	/**
	 * AJAX handler for manual sync
	 */
	public function ajax_sync_biomarkers() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_die( 'User not logged in' );
		}

		$results = $this->sync_user_biomarkers( $user_id );

		wp_send_json( array(
			'success' => $results['success'],
			'message' => $results['success'] ? 
				'Successfully synced ' . count( $results['updated_fields'] ) . ' biomarker fields' : 
				'Error syncing biomarkers: ' . implode( ', ', $results['errors'] ),
			'updated_fields' => $results['updated_fields'],
			'errors' => $results['errors'],
		) );
	}

	/**
	 * Get sync status for a user
	 *
	 * @param int $user_id User ID
	 * @return array Sync status
	 */
	public function get_sync_status( $user_id ) {
		$sync_log = get_user_meta( $user_id, 'ennu_biomarker_sync_log', true );
		$biomarkers = get_user_meta( $user_id, 'ennu_user_biomarkers', true );

		return array(
			'last_sync' => ! empty( $sync_log ) ? $sync_log[0]['timestamp'] : null,
			'last_success' => ! empty( $sync_log ) && $sync_log[0]['success'] ? $sync_log[0]['timestamp'] : null,
			'last_errors' => ! empty( $sync_log ) ? $sync_log[0]['errors'] : array(),
			'biomarker_count' => is_array( $biomarkers ) ? count( $biomarkers ) : 0,
			'sync_log' => $sync_log,
		);
	}

	/**
	 * Force sync for all users (admin function)
	 */
	public function sync_all_users_biomarkers() {
		$users = get_users( array( 'fields' => 'ID' ) );
		$results = array(
			'total_users' => count( $users ),
			'successful_syncs' => 0,
			'failed_syncs' => 0,
			'errors' => array(),
		);

		foreach ( $users as $user_id ) {
			$sync_result = $this->sync_user_biomarkers( $user_id );
			if ( $sync_result['success'] ) {
				$results['successful_syncs']++;
			} else {
				$results['failed_syncs']++;
				$results['errors'][] = 'User ' . $user_id . ': ' . implode( ', ', $sync_result['errors'] );
			}
		}

		return $results;
	}

	/**
	 * Ensure biomarker sync is up-to-date when dashboard loads
	 * This ensures weight and BMI are always current from global fields
	 */
	public function ensure_biomarker_sync() {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id = get_current_user_id();
		
		// Check if we need to sync (only if global height/weight data exists)
		$height_weight_data = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		if ( ! empty( $height_weight_data ) && is_array( $height_weight_data ) ) {
			// Trigger sync to ensure weight and BMI are current
			$this->sync_user_biomarkers( $user_id );
		}
	}
}

// Initialize the auto-sync system
new ENNU_Biomarker_Auto_Sync(); 