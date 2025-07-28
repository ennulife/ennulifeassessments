<?php
/**
 * ENNU Life Data Manager - Modern Data Persistence Service
 *
 * Consolidates all data persistence logic from legacy system into a unified,
 * modern service-oriented architecture. Handles user meta, assessment data,
 * scoring, caching, and integration with external systems.
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     64.6.20
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Data_Manager {

	/**
	 * Logger instance
	 *
	 * @var ENNU_Logger
	 */
	private $logger;

	/**
	 * Cache instance
	 *
	 * @var ENNU_Score_Cache
	 */
	private $cache;

	/**
	 * Enhanced database instance
	 *
	 * @var ENNU_Life_Enhanced_Database
	 */
	private $database;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->logger = new ENNU_Logger();
		$this->database = ENNU_Life_Enhanced_Database::get_instance();
		
		// Initialize cache if available
		if ( class_exists( 'ENNU_Score_Cache' ) ) {
			$this->cache = new ENNU_Score_Cache();
		}
	}

	/**
	 * Save assessment data with comprehensive validation and error handling
	 *
	 * @param int    $user_id         User ID
	 * @param string $assessment_type Assessment type
	 * @param array  $form_data       Form data
	 * @param array  $scores          Calculated scores (optional)
	 * @return ENNU_Form_Result Result object
	 */
	public function save_assessment_data( $user_id, $assessment_type, $form_data, $scores = null ) {
		$this->logger->log( 'Data Manager: Starting assessment data save', array(
			'user_id' => $user_id,
			'assessment_type' => $assessment_type,
			'data_count' => count( $form_data )
		) );

		try {
			// 1. Validate input
			if ( ! $user_id || ! $assessment_type || empty( $form_data ) ) {
				return ENNU_Form_Result::error( 'invalid_input', 'Missing required parameters for data save' );
			}

			// 2. Sanitize assessment type
			$assessment_type = sanitize_text_field( $assessment_type );

			// 3. Save using enhanced database
			$result = $this->database->save_assessment( $assessment_type, $form_data, $scores, $user_id );

			if ( ! $result ) {
				return ENNU_Form_Result::error( 'save_failed', 'Failed to save assessment data' );
			}

			// 4. Update centralized symptoms if available
			if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
				ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $assessment_type );
				$this->logger->log( 'Data Manager: Updated centralized symptoms' );
			}

			// 5. Trigger completion hook
			do_action( 'ennu_assessment_completed', $user_id, $assessment_type );

			// 6. Invalidate caches
			if ( $this->cache ) {
				$this->cache->invalidate_cache( $user_id, $assessment_type );
			}

			$this->logger->log( 'Data Manager: Assessment data saved successfully' );

			return ENNU_Form_Result::success( array(
				'user_id' => $user_id,
				'assessment_type' => $assessment_type,
				'saved_at' => current_time( 'mysql' )
			) );

		} catch ( Exception $e ) {
			$this->logger->log( 'Data Manager: Error saving assessment data', $e->getMessage() );
			return ENNU_Form_Result::error( 'save_exception', $e->getMessage() );
		}
	}

	/**
	 * Save global fields (shared across assessments)
	 *
	 * @param int   $user_id   User ID
	 * @param array $form_data Form data containing global fields
	 * @return ENNU_Form_Result Result object
	 */
	public function save_global_fields( $user_id, $form_data ) {
		$this->logger->log( 'Data Manager: Saving global fields', array( 'user_id' => $user_id ) );

		try {
			$saved_fields = array();
			$global_keys = $this->get_global_field_keys();

			foreach ( $global_keys as $global_key ) {
				$question_id = str_replace( 'ennu_global_', '', $global_key );
				
				if ( isset( $form_data[ $question_id ] ) ) {
					$value = $this->sanitize_global_field( $question_id, $form_data[ $question_id ] );
					
					if ( $value !== null ) {
						$result = update_user_meta( $user_id, $global_key, $value );
						if ( $result !== false ) {
							$saved_fields[] = $global_key;
						}
					}
				}
			}

			$this->logger->log( 'Data Manager: Global fields saved', array( 'saved_count' => count( $saved_fields ) ) );

			return ENNU_Form_Result::success( array(
				'saved_fields' => $saved_fields,
				'count' => count( $saved_fields )
			) );

		} catch ( Exception $e ) {
			$this->logger->log( 'Data Manager: Error saving global fields', $e->getMessage() );
			return ENNU_Form_Result::error( 'global_save_failed', $e->getMessage() );
		}
	}

	/**
	 * Get user assessment data with caching
	 *
	 * @param int    $user_id         User ID
	 * @param string $assessment_type Assessment type
	 * @return array Assessment data
	 */
	public function get_user_assessment_data( $user_id, $assessment_type ) {
		// Try cache first
		if ( $this->cache ) {
			$cached = $this->cache->get_cached_score( $user_id, $assessment_type );
			if ( $cached !== false ) {
				return $cached;
			}
		}

		// Get from database
		$data = $this->database->get_user_assessment_data( $user_id, $assessment_type );

		// Cache the result
		if ( $this->cache && ! empty( $data ) ) {
			$this->cache->cache_score( $user_id, $assessment_type, $data );
		}

		return $data;
	}

	/**
	 * Get user global data
	 *
	 * @param int $user_id User ID
	 * @return array Global data
	 */
	public function get_user_global_data( $user_id ) {
		$global_keys = $this->get_global_field_keys();
		$data = array();

		foreach ( $global_keys as $key ) {
			$value = get_user_meta( $user_id, $key, true );
			if ( ! empty( $value ) ) {
				$data[ $key ] = $value;
			}
		}

		return $data;
	}

	/**
	 * Calculate and store scores
	 *
	 * @param int    $user_id         User ID
	 * @param string $assessment_type Assessment type
	 * @param array  $form_data       Form data
	 * @return array|false Score data or false on failure
	 */
	public function calculate_and_store_scores( $user_id, $assessment_type, $form_data ) {
		try {
			$score_data = $this->database->calculate_and_store_scores( $assessment_type, $form_data, null, $user_id );
			
			if ( $score_data ) {
				// Update centralized symptoms
				if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
					ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $assessment_type );
				}

				// Calculate all user scores
				if ( class_exists( 'ENNU_Scoring_System' ) ) {
					ENNU_Scoring_System::calculate_and_save_all_user_scores( $user_id );
				}
			}

			return $score_data;

		} catch ( Exception $e ) {
			$this->logger->log( 'Data Manager: Error calculating scores', $e->getMessage() );
			return false;
		}
	}

	/**
	 * Store results in transient for immediate access
	 *
	 * @param int    $user_id         User ID
	 * @param string $assessment_type Assessment type
	 * @param array  $scores          Calculated scores
	 * @param array  $form_data       Form data
	 * @return string Results token
	 */
	public function store_results_transient( $user_id, $assessment_type, $scores, $form_data ) {
		$token = wp_generate_password( 32, false );

		$transient_data = array(
			'user_id' => $user_id,
			'assessment_type' => $assessment_type,
			'scores' => $scores,
			'form_data' => $form_data,
			'timestamp' => current_time( 'mysql' ),
		);

		set_transient( 'ennu_results_' . $token, $transient_data, HOUR_IN_SECONDS );

		$this->logger->log( 'Data Manager: Results transient stored', array( 'token' => $token ) );

		return $token;
	}

	/**
	 * Get results from transient
	 *
	 * @param string $token Results token
	 * @return array|false Transient data or false if not found
	 */
	public function get_results_transient( $token ) {
		return get_transient( 'ennu_results_' . $token );
	}

	/**
	 * Save assessment progress for partial submissions
	 *
	 * @param int   $user_id         User ID
	 * @param array $progress_data   Progress data
	 * @return ENNU_Form_Result Result object
	 */
	public function save_progress( $user_id, $progress_data ) {
		try {
			if ( ! $user_id ) {
				return ENNU_Form_Result::error( 'not_logged_in', 'User must be logged in to save progress' );
			}

			$assessment_type = $progress_data['assessment_type'] ?? 'unknown';
			$meta_key = 'ennu_' . $assessment_type . '_progress';
			
			$result = update_user_meta( $user_id, $meta_key, $progress_data );

			if ( $result !== false ) {
				$this->logger->log( 'Data Manager: Progress saved', array( 'user_id' => $user_id, 'assessment_type' => $assessment_type ) );
				return ENNU_Form_Result::success();
			} else {
				return ENNU_Form_Result::error( 'save_failed', 'Failed to save progress' );
			}

		} catch ( Exception $e ) {
			return ENNU_Form_Result::error( 'save_exception', $e->getMessage() );
		}
	}

	/**
	 * Get global field keys
	 *
	 * @return array Array of global field keys
	 */
	private function get_global_field_keys() {
		return array(
			'ennu_global_contact_info_first_name',
			'ennu_global_contact_info_last_name',
			'ennu_global_contact_info_email',
			'ennu_global_contact_info_phone',
			'ennu_global_gender',
			'ennu_global_date_of_birth',
			'ennu_global_height_weight',
			'ennu_global_health_goals',
			'ennu_global_age_range',
			'ennu_global_age_category',
		);
	}

	/**
	 * Sanitize global field value
	 *
	 * @param string $field_name Field name
	 * @param mixed  $value      Field value
	 * @return mixed Sanitized value or null if invalid
	 */
	private function sanitize_global_field( $field_name, $value ) {
		switch ( $field_name ) {
			case 'contact_info_email':
				return is_email( $value ) ? sanitize_email( $value ) : null;
			
			case 'date_of_birth':
				// Handle DOB fields
				if ( is_array( $value ) && isset( $value['year'], $value['month'], $value['day'] ) ) {
					return $value['year'] . '-' . 
						   str_pad( $value['month'], 2, '0', STR_PAD_LEFT ) . '-' . 
						   str_pad( $value['day'], 2, '0', STR_PAD_LEFT );
				}
				return sanitize_text_field( $value );
			
			case 'height_weight':
				// Handle height/weight fields
				if ( is_array( $value ) && isset( $value['ft'], $value['in'], $value['lbs'] ) ) {
					return array(
						'ft' => absint( $value['ft'] ),
						'in' => absint( $value['in'] ),
						'lbs' => absint( $value['lbs'] ),
					);
				}
				return null;
			
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Clear assessment data for retake
	 *
	 * @param int    $user_id         User ID
	 * @param string $assessment_type Assessment type
	 * @return ENNU_Form_Result Result object
	 */
	public function clear_assessment_data( $user_id, $assessment_type ) {
		try {
			global $wpdb;

			// Delete score data
			$wpdb->delete(
				$wpdb->usermeta,
				array(
					'user_id' => $user_id,
					'meta_key' => $assessment_type . '_calculated_score',
				)
			);

			// Delete all assessment-specific meta
			$meta_keys = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT meta_key FROM {$wpdb->usermeta} 
					 WHERE user_id = %d AND meta_key LIKE %s",
					$user_id,
					'ennu_' . $assessment_type . '_%'
				)
			);

			foreach ( $meta_keys as $meta_key ) {
				delete_user_meta( $user_id, $meta_key );
			}

			// Invalidate cache
			if ( $this->cache ) {
				$this->cache->invalidate_cache( $user_id, $assessment_type );
			}

			$this->logger->log( 'Data Manager: Assessment data cleared', array(
				'user_id' => $user_id,
				'assessment_type' => $assessment_type,
				'deleted_keys' => count( $meta_keys )
			) );

			return ENNU_Form_Result::success( array(
				'deleted_keys' => count( $meta_keys )
			) );

		} catch ( Exception $e ) {
			return ENNU_Form_Result::error( 'clear_failed', $e->getMessage() );
		}
	}
} 