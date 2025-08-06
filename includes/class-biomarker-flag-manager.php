<?php
/**
 * ENNU Biomarker Flag Manager
 * Manages biomarker flagging system for medical providers
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Biomarker_Flag_Manager {

	/**
	 * Biomarker manager instance
	 */
	private $biomarker_manager;

	/**
	 * Initialize biomarker flag manager
	 */
	public function __construct() {
		add_action( 'ennu_biomarker_data_updated', array( $this, 'on_biomarker_data_updated' ), 10, 2 );
	}

	/**
	 * Auto-flag biomarkers based on imported lab data
	 *
	 * @param int $user_id User ID
	 * @param array $biomarker_data Imported biomarker data
	 */
	public function auto_flag_biomarkers( $user_id, $biomarker_data ) {
		if ( empty( $biomarker_data ) || ! is_array( $biomarker_data ) ) {
			return;
		}

		foreach ( $biomarker_data as $biomarker_name => $data ) {
			if ( $this->should_auto_flag( $biomarker_name, $data ) ) {
				$this->flag_biomarker( $user_id, $biomarker_name, 'auto_flagged', $this->get_auto_flag_reason( $biomarker_name, $data ) );
			}
		}
	}

	/**
	 * Flag a biomarker for medical attention
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_name Biomarker name
	 * @param string $flag_type Flag type (manual, auto_flagged, critical, symptom_triggered)
	 * @param string $reason Reason for flagging
	 * @param int $flagged_by User ID of person who flagged (optional)
	 * @param string $assessment_source Assessment that triggered the flag (optional)
	 * @param string $symptom_trigger DEPRECATED: Symptom that triggered the flag (optional)
	 * @param string $symptom_key The sanitized key of the symptom that triggered the flag.
	 * @return bool Success status
	 */
	public function flag_biomarker( $user_id, $biomarker_name, $flag_type = 'manual', $reason = '', $flagged_by = null, $assessment_source = '', $symptom_trigger = '', $symptom_key = '' ) {
		error_log( "ENNU Biomarker Flag Manager: Attempting to flag biomarker '{$biomarker_name}' for user {$user_id}" );
		
		$flag_data = array(
			'user_id'            => $user_id,
			'biomarker_name'     => $biomarker_name,
			'flag_type'          => $flag_type,
			'reason'             => $reason,
			'flagged_by'         => $flagged_by ?: get_current_user_id(),
			'flagged_at'         => current_time( 'mysql' ),
			'status'             => 'active',
			'assessment_source'  => $assessment_source,
			'symptom_trigger'    => $symptom_trigger, // Keep for backward compatibility
			'symptom_key'        => $symptom_key, // Add the new, reliable key
		);

		$existing_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
		if ( ! is_array( $existing_flags ) ) {
			$existing_flags = array();
		}

		$flag_id                    = $biomarker_name . '_' . time();
		$existing_flags[ $flag_id ] = $flag_data;

		error_log( "ENNU Biomarker Flag Manager: Saving flags for user {$user_id}: " . print_r($existing_flags, true) );

		$success = update_user_meta( $user_id, 'ennu_biomarker_flags', $existing_flags );

		error_log( "ENNU Biomarker Flag Manager: update_user_meta result for user {$user_id}: " . ($success ? 'SUCCESS' : 'FAILED') );

		if ( $success ) {
			$this->log_flag_action( $user_id, $biomarker_name, 'flagged', $flag_data );
			do_action( 'ennu_biomarker_flagged', $user_id, $biomarker_name, $flag_data );
			error_log( "ENNU Biomarker Flag Manager: Successfully flagged biomarker '{$biomarker_name}' for user {$user_id}" );
		} else {
			error_log( "ENNU Biomarker Flag Manager: Failed to flag biomarker '{$biomarker_name}' for user {$user_id}" );
		}

		return $success;
	}

	/**
	 * Remove flag from biomarker
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_name Biomarker name
	 * @param string $removal_reason Reason for removal
	 * @param int $removed_by User ID of person who removed flag
	 * @return bool Success status
	 */
	public function remove_flag( $user_id, $biomarker_name, $removal_reason = '', $removed_by = null ) {
		$existing_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
		if ( ! is_array( $existing_flags ) ) {
			return false;
		}

		$flag_removed = false;
		foreach ( $existing_flags as $flag_id => $flag_data ) {
			if ( $flag_data['biomarker_name'] === $biomarker_name && $flag_data['status'] === 'active' ) {
				$existing_flags[ $flag_id ]['status']         = 'removed';
				$existing_flags[ $flag_id ]['removed_at']     = current_time( 'mysql' );
				$existing_flags[ $flag_id ]['removed_by']     = $removed_by ?: get_current_user_id();
				$existing_flags[ $flag_id ]['removal_reason'] = $removal_reason;
				$flag_removed                                 = true;
			}
		}

		if ( $flag_removed ) {
			$success = update_user_meta( $user_id, 'ennu_biomarker_flags', $existing_flags );
			if ( $success ) {
				$this->log_flag_action( $user_id, $biomarker_name, 'removed', array( 'reason' => $removal_reason ) );
				do_action( 'ennu_biomarker_flag_removed', $user_id, $biomarker_name, $removal_reason );
			}
			return $success;
		}

		return false;
	}

	/**
	 * Get biomarker flags for a specific biomarker
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_name Biomarker name
	 * @return array Array of flags for the biomarker
	 */
	public function get_biomarker_flags( $user_id, $biomarker_name ) {
		$all_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
		
		if ( empty( $all_flags ) || ! is_array( $all_flags ) ) {
			return array();
		}

		$biomarker_flags = array();
		foreach ( $all_flags as $flag_id => $flag_data ) {
			if ( isset( $flag_data['biomarker_name'] ) && $flag_data['biomarker_name'] === $biomarker_name ) {
				if ( isset( $flag_data['status'] ) && $flag_data['status'] === 'active' ) {
					$biomarker_flags[] = array(
						'type' => $flag_data['flag_type'],
						'reason' => $flag_data['reason'],
						'flagged_at' => $flag_data['flagged_at']
					);
				}
			}
		}

		return $biomarker_flags;
	}

	/**
	 * Get flagged biomarkers for a user with deduplication
	 *
	 * @param int $user_id User ID
	 * @param string $status Flag status (active, resolved, all)
	 * @param bool $deduplicate Whether to deduplicate by biomarker name
	 * @return array Array of flagged biomarkers
	 */
	public function get_flagged_biomarkers( $user_id, $status = 'active', $deduplicate = true ) {
		$all_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
		if ( ! is_array( $all_flags ) ) {
			return array();
		}

		$filtered_flags = array();
		foreach ( $all_flags as $flag_id => $flag_data ) {
			if ( $status === 'all' || $flag_data['status'] === $status ) {
				$filtered_flags[ $flag_id ] = $flag_data;
			}
		}

		// If no deduplication requested, return as-is
		if ( ! $deduplicate ) {
			return $filtered_flags;
		}

		// Deduplicate by biomarker name and add multiple assessment explanations
		$deduplicated = array();
		$biomarker_sources = array();

		foreach ( $filtered_flags as $flag_id => $flag_data ) {
			$biomarker_name = $flag_data['biomarker_name'] ?? '';
			
			if ( empty( $biomarker_name ) ) {
				continue;
			}

			// Track sources for this biomarker
			if ( ! isset( $biomarker_sources[ $biomarker_name ] ) ) {
				$biomarker_sources[ $biomarker_name ] = array();
			}

			$assessment_source = $flag_data['assessment_source'] ?? 'Unknown';
			$assessment_display = $this->get_assessment_display_name( $assessment_source );
			
			$biomarker_sources[ $biomarker_name ][] = $assessment_display;

			// Keep the most recent flag for this biomarker
			if ( ! isset( $deduplicated[ $biomarker_name ] ) || 
				 strtotime( $flag_data['flagged_at'] ) > strtotime( $deduplicated[ $biomarker_name ]['flagged_at'] ) ) {
				$deduplicated[ $biomarker_name ] = $flag_data;
			}
		}

		// Add multiple assessment explanations
		foreach ( $deduplicated as $biomarker_name => &$flag_data ) {
			$sources = array_unique( $biomarker_sources[ $biomarker_name ] );
			
			if ( count( $sources ) > 1 ) {
				$flag_data['multiple_assessments'] = true;
				$flag_data['assessment_count'] = count( $sources );
				$flag_data['all_assessments'] = $sources;
				$flag_data['explanation'] = sprintf(
					'Flagged by %d assessments: %s',
					count( $sources ),
					implode( ', ', $sources )
				);
			} else {
				$flag_data['multiple_assessments'] = false;
				$flag_data['assessment_count'] = 1;
				$flag_data['all_assessments'] = $sources;
				$flag_data['explanation'] = sprintf(
					'Flagged by: %s',
					$sources[0]
				);
			}
		}

		return $deduplicated;
	}

	/**
	 * Get all flagged biomarkers without deduplication (for detailed analysis)
	 *
	 * @param int $user_id User ID
	 * @param string $status Flag status (active, resolved, all)
	 * @return array Array of all flagged biomarkers
	 */
	public function get_all_flagged_biomarkers( $user_id, $status = 'active' ) {
		return $this->get_flagged_biomarkers( $user_id, $status, false );
	}

	/**
	 * Get flagged biomarkers grouped by assessment source
	 *
	 * @param int $user_id User ID
	 * @param string $status Flag status (active, resolved, all)
	 * @return array Array of flagged biomarkers grouped by assessment
	 */
	public function get_flagged_biomarkers_by_assessment( $user_id, $status = 'active' ) {
		$all_flags = $this->get_flagged_biomarkers( $user_id, $status );
		
		$grouped_flags = array();
		
		foreach ( $all_flags as $flag_id => $flag_data ) {
			$assessment_source = $flag_data['assessment_source'] ?? 'Unknown Assessment';
			
			// Clean up assessment source name for display
			$assessment_display_name = $this->get_assessment_display_name( $assessment_source );
			
			if ( ! isset( $grouped_flags[ $assessment_display_name ] ) ) {
				$grouped_flags[ $assessment_display_name ] = array();
			}
			
			$grouped_flags[ $assessment_display_name ][ $flag_id ] = $flag_data;
		}
		
		return $grouped_flags;
	}

	/**
	 * Get assessment display name from assessment source
	 *
	 * @param string $assessment_source Assessment source identifier
	 * @return string Human-readable assessment name
	 */
	private function get_assessment_display_name( $assessment_source ) {
		$assessment_names = array(
			'health_optimization_assessment' => 'Health Optimization Assessment',
			'testosterone_assessment' => 'Testosterone Assessment',
			'hormone_assessment' => 'Hormone Assessment',
			'menopause_assessment' => 'Menopause Assessment',
			'ed_treatment_assessment' => 'ED Treatment Assessment',
			'skin_assessment' => 'Skin Assessment',
			'hair_assessment' => 'Hair Assessment',
			'sleep_assessment' => 'Sleep Assessment',
			'weight_loss_assessment' => 'Weight Loss Assessment',
			'welcome_assessment' => 'Welcome Assessment',
			'auto_flagged' => 'Lab Results Analysis',
			'manual' => 'Manual Flag',
			'critical' => 'Critical Alert',
			'' => 'Unknown Assessment',
		);
		
		return $assessment_names[ $assessment_source ] ?? ucwords( str_replace( '_', ' ', $assessment_source ) );
	}

	/**
	 * Check if biomarker should be auto-flagged
	 *
	 * @param string $biomarker_name Biomarker name
	 * @param array $data Biomarker data
	 * @return bool Whether to auto-flag
	 */
	private function should_auto_flag( $biomarker_name, $data ) {
		if ( ! isset( $data['value'] ) || ! isset( $data['status'] ) ) {
			return false;
		}

		$auto_flag_conditions = $this->get_auto_flag_conditions();

		if ( ! isset( $auto_flag_conditions[ $biomarker_name ] ) ) {
			return false;
		}

		$conditions = $auto_flag_conditions[ $biomarker_name ];
		$value      = floatval( $data['value'] );
		$status     = $data['status'];

		if ( $status === 'critical' ) {
			return true;
		}

		if ( isset( $conditions['critical_high'] ) && $value >= $conditions['critical_high'] ) {
			return true;
		}

		if ( isset( $conditions['critical_low'] ) && $value <= $conditions['critical_low'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get auto-flag conditions for biomarkers
	 *
	 * @return array Auto-flag conditions
	 */
	private function get_auto_flag_conditions() {
		return array(
			'testosterone_total' => array(
				'critical_low'  => 200,
				'critical_high' => 1500,
			),
			'testosterone_free'  => array(
				'critical_low'  => 5,
				'critical_high' => 50,
			),
			'estradiol'          => array(
				'critical_low'  => 10,
				'critical_high' => 200,
			),
			'thyroid_tsh'        => array(
				'critical_low'  => 0.1,
				'critical_high' => 10,
			),
			'vitamin_d'          => array(
				'critical_low'  => 20,
				'critical_high' => 150,
			),
			'hemoglobin_a1c'     => array(
				'critical_high' => 7.0,
			),
			'cholesterol_total'  => array(
				'critical_high' => 300,
			),
			'triglycerides'      => array(
				'critical_high' => 500,
			),
		);
	}

	/**
	 * Get auto-flag reason
	 *
	 * @param string $biomarker_name Biomarker name
	 * @param array $data Biomarker data
	 * @return string Flag reason
	 */
	private function get_auto_flag_reason( $biomarker_name, $data ) {
		$value      = floatval( $data['value'] );
		$status     = $data['status'];
		$conditions = $this->get_auto_flag_conditions()[ $biomarker_name ] ?? array();

		if ( $status === 'critical' ) {
			return 'Lab result marked as critical';
		}

		if ( isset( $conditions['critical_high'] ) && $value >= $conditions['critical_high'] ) {
			return "Value {$value} exceeds critical high threshold of {$conditions['critical_high']}";
		}

		if ( isset( $conditions['critical_low'] ) && $value <= $conditions['critical_low'] ) {
			return "Value {$value} below critical low threshold of {$conditions['critical_low']}";
		}

		return 'Automatically flagged based on lab results';
	}

	/**
	 * Log flag action
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_name Biomarker name
	 * @param string $action Action performed
	 * @param array $data Additional data
	 */
	private function log_flag_action( $user_id, $biomarker_name, $action, $data = array() ) {
		$log_entry = array(
			'timestamp'      => current_time( 'mysql' ),
			'user_id'        => $user_id,
			'biomarker_name' => $biomarker_name,
			'action'         => $action,
			'data'           => $data,
			'performed_by'   => get_current_user_id(),
		);

		$existing_log   = get_option( 'ennu_biomarker_flag_log', array() );
		$existing_log[] = $log_entry;

		update_option( 'ennu_biomarker_flag_log', array_slice( $existing_log, -1000 ) );
	}

	/**
	 * Handle AJAX flag biomarker request
	 */
	public function handle_flag_biomarker() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$user_id        = intval( $_POST['user_id'] ?? 0 );
		$biomarker_name = sanitize_text_field( $_POST['biomarker_name'] ?? '' );
		$reason         = sanitize_textarea_field( $_POST['reason'] ?? '' );

		if ( ! $user_id || ! $biomarker_name ) {
			wp_send_json_error( 'Missing required parameters' );
		}

		$success = $this->flag_biomarker( $user_id, $biomarker_name, 'manual', $reason );

		if ( $success ) {
			wp_send_json_success( array( 'message' => 'Biomarker flagged successfully' ) );
		} else {
			wp_send_json_error( 'Failed to flag biomarker' );
		}
	}

	/**
	 * Handle AJAX remove flag request
	 */
	public function handle_remove_flag() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$user_id        = intval( $_POST['user_id'] ?? 0 );
		$biomarker_name = sanitize_text_field( $_POST['biomarker_name'] ?? '' );
		$reason         = sanitize_textarea_field( $_POST['reason'] ?? '' );

		if ( ! $user_id || ! $biomarker_name ) {
			wp_send_json_error( 'Missing required parameters' );
		}

		$success = $this->remove_flag( $user_id, $biomarker_name, $reason );

		if ( $success ) {
			wp_send_json_success( array( 'message' => 'Flag removed successfully' ) );
		} else {
			wp_send_json_error( 'Failed to remove flag' );
		}
	}

	/**
	 * Handle AJAX get flagged biomarkers request
	 */
	public function handle_get_flagged_biomarkers() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$user_id = intval( $_POST['user_id'] ?? 0 );
		$status  = sanitize_text_field( $_POST['status'] ?? 'active' );

		if ( ! $user_id ) {
			wp_send_json_error( 'Missing user ID' );
		}

		$flagged_biomarkers = $this->get_flagged_biomarkers( $user_id, $status );

		wp_send_json_success( array( 'flagged_biomarkers' => $flagged_biomarkers ) );
	}

	/**
	 * Enhanced flag biomarker method with anonymous user support
	 */
	public function flag_biomarker_enhanced( $user_id, $biomarker_name, $flag_type = 'manual', $reason = '', $flagged_by = null, $assessment_source = '', $symptom_trigger = '', $symptom_key = '' ) {
		// Handle anonymous users with session-based storage
		if ( ! $user_id ) {
			$session_id = $this->get_anonymous_session_id();
			if ( $session_id ) {
				return $this->flag_anonymous_biomarker( $session_id, $biomarker_name, $flag_type, $reason, $assessment_source, $symptom_trigger, $symptom_key );
			}
			return false;
		}
		
		// Existing logic for logged-in users
		error_log( "ENNU Biomarker Flag Manager: Attempting to flag biomarker '{$biomarker_name}' for user {$user_id}" );
		
		$flag_data = array(
			'user_id'            => $user_id,
			'biomarker_name'     => $biomarker_name,
			'flag_type'          => $flag_type,
			'reason'             => $reason,
			'flagged_by'         => $flagged_by ?: get_current_user_id(),
			'flagged_at'         => current_time( 'mysql' ),
			'status'             => 'active',
			'assessment_source'  => $assessment_source,
			'symptom_trigger'    => $symptom_trigger,
			'symptom_key'        => $symptom_key,
		);

		$existing_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
		if ( ! is_array( $existing_flags ) ) {
			$existing_flags = array();
		}

		$flag_id                    = $biomarker_name . '_' . time();
		$existing_flags[ $flag_id ] = $flag_data;

		error_log( "ENNU Biomarker Flag Manager: Saving flags for user {$user_id}: " . print_r($existing_flags, true) );

		$success = update_user_meta( $user_id, 'ennu_biomarker_flags', $existing_flags );

		error_log( "ENNU Biomarker Flag Manager: update_user_meta result for user {$user_id}: " . ($success ? 'SUCCESS' : 'FAILED') );

		if ( $success ) {
			$this->log_flag_action( $user_id, $biomarker_name, 'flagged', $flag_data );
			do_action( 'ennu_biomarker_flagged', $user_id, $biomarker_name, $flag_data );
			error_log( "ENNU Biomarker Flag Manager: Successfully flagged biomarker '{$biomarker_name}' for user {$user_id}" );
		} else {
			error_log( "ENNU Biomarker Flag Manager: Failed to flag biomarker '{$biomarker_name}' for user {$user_id}" );
		}

		return $success;
	}
	
	/**
	 * Flag biomarker for anonymous users
	 */
	private function flag_anonymous_biomarker( $session_id, $biomarker_name, $flag_type = 'manual', $reason = '', $assessment_source = '', $symptom_trigger = '', $symptom_key = '' ) {
		$flag_data = array(
			'biomarker_name' => $biomarker_name,
			'flag_type' => $flag_type,
			'reason' => $reason,
			'flagged_at' => current_time( 'mysql' ),
			'session_id' => $session_id,
			'assessment_source' => $assessment_source,
			'symptom_trigger' => $symptom_trigger,
			'symptom_key' => $symptom_key,
			'status' => 'active'
		);
		
		$anonymous_flags = get_transient( 'ennu_anonymous_biomarker_flags_' . $session_id ) ?: array();
		$flag_id = $biomarker_name . '_' . time();
		$anonymous_flags[$flag_id] = $flag_data;
		set_transient( 'ennu_anonymous_biomarker_flags_' . $session_id, $anonymous_flags, 3600 );
		
		return true;
	}
	
	/**
	 * Get anonymous session ID
	 */
	private function get_anonymous_session_id() {
		if ( ! isset( $_COOKIE['ennu_anonymous_session'] ) ) {
			$session_id = wp_generate_password( 32, false );
			setcookie( 'ennu_anonymous_session', $session_id, time() + 3600, '/' );
			return $session_id;
		}
		
		return $_COOKIE['ennu_anonymous_session'];
	}
	
	/**
	 * Migrate anonymous biomarker flags to user account
	 */
	public function migrate_anonymous_biomarker_flags( $user_id, $session_id ) {
		$anonymous_flags = get_transient( 'ennu_anonymous_biomarker_flags_' . $session_id );
		
		if ( $anonymous_flags ) {
			$existing_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
			if ( ! is_array( $existing_flags ) ) {
				$existing_flags = array();
			}
			
			// Convert session-based flags to user-based flags
			foreach ( $anonymous_flags as $biomarker_name => $flag_data ) {
				$flag_data['user_id'] = $user_id;
				unset( $flag_data['session_id'] );
				$existing_flags[$biomarker_name] = $flag_data;
			}
			
			update_user_meta( $user_id, 'ennu_biomarker_flags', $existing_flags );
			delete_transient( 'ennu_anonymous_biomarker_flags_' . $session_id );
			return true;
		}
		
		return false;
	}
}
