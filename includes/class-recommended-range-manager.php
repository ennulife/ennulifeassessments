<?php
/**
 * ENNU Recommended Range Manager
 *
 * Manages recommended biomarker ranges and displays for users
 * Integrates with existing biomarker profiles and configuration patterns
 *
 * @package ENNU_Life_Assessments
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Recommended_Range_Manager {

	/**
	 * Initialize the Recommended Range Manager
	 */
	public function __construct() {
		add_action( 'wp_ajax_ennu_get_recommended_ranges', array( $this, 'ajax_get_recommended_ranges' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_recommended_ranges', array( $this, 'ajax_get_recommended_ranges' ) );
		add_action( 'wp_ajax_ennu_update_user_ranges', array( $this, 'ajax_update_user_ranges' ) );
		add_action( 'wp_ajax_ennu_synchronize_user_data', array( $this, 'ajax_synchronize_user_data' ) );
		add_action( 'ennu_biomarker_uploaded', array( $this, 'check_biomarker_ranges' ), 10, 2 );
		
		// Hook to synchronize user data when AI Medical Team updates ranges
		add_action( 'ennu_ai_medical_team_ranges_updated', array( $this, 'synchronize_user_biomarker_data' ) );
	}

	/**
	 * Get recommended ranges for a specific biomarker
	 *
	 * @param string $biomarker_name The biomarker name
	 * @param array $user_data User demographic data (age, gender, etc.)
	 * @return array Recommended range data
	 */
	public function get_recommended_range( $biomarker_name, $user_data = array() ) {
		$biomarker_config = $this->get_biomarker_configuration();

		if ( ! isset( $biomarker_config[ $biomarker_name ] ) ) {
			return array(
				'error'     => 'Biomarker not found',
				'biomarker' => $biomarker_name,
			);
		}

		$config      = $biomarker_config[ $biomarker_name ];
		$user_age    = isset( $user_data['age'] ) ? intval( $user_data['age'] ) : 35;
		$user_gender = isset( $user_data['gender'] ) ? strtolower( $user_data['gender'] ) : 'male';

		$ranges = $this->calculate_personalized_ranges( $config, $user_age, $user_gender );

		return array(
			'biomarker'    => $biomarker_name,
			'display_name' => $config['display_name'],
			'unit'         => $config['unit'],
			'optimal_min'  => $ranges['optimal_min'],
			'optimal_max'  => $ranges['optimal_max'],
			'normal_min'   => $ranges['normal_min'],
			'normal_max'   => $ranges['normal_max'],
			'critical_min' => $ranges['critical_min'],
			'critical_max' => $ranges['critical_max'],
			'description'  => $config['description'],
			'factors'      => isset( $config['factors'] ) ? $config['factors'] : array(),
			'age_group'    => $this->get_age_group( $user_age ),
			'gender'       => $user_gender,
		);
	}

	/**
	 * Get all recommended ranges for a user
	 *
	 * @param int $user_id User ID
	 * @return array All recommended ranges for user
	 */
	public function get_user_recommended_ranges( $user_id ) {
		$user_data        = $this->get_user_demographic_data( $user_id );
		$biomarker_config = $this->get_biomarker_configuration();
		$user_ranges      = array();

		foreach ( $biomarker_config as $biomarker_name => $config ) {
			$user_ranges[ $biomarker_name ] = $this->get_recommended_range( $biomarker_name, $user_data );
		}

		return $user_ranges;
	}

	/**
	 * Calculate personalized ranges based on age and gender
	 *
	 * @param array $config Biomarker configuration
	 * @param int $age User age
	 * @param string $gender User gender
	 * @return array Calculated ranges
	 */
	private function calculate_personalized_ranges( $config, $age, $gender ) {
		$base_ranges = $config['ranges'];
		$age_group   = $this->get_age_group( $age );

		if ( isset( $config['age_adjustments'][ $age_group ] ) ) {
			$adjustments = $config['age_adjustments'][ $age_group ];
			$base_ranges = $this->apply_range_adjustments( $base_ranges, $adjustments );
		}

		if ( isset( $config['gender_adjustments'][ $gender ] ) ) {
			$adjustments = $config['gender_adjustments'][ $gender ];
			$base_ranges = $this->apply_range_adjustments( $base_ranges, $adjustments );
		}

		return $base_ranges;
	}

	/**
	 * Apply range adjustments
	 *
	 * @param array $ranges Base ranges
	 * @param array $adjustments Adjustment factors
	 * @return array Adjusted ranges
	 */
	private function apply_range_adjustments( $ranges, $adjustments ) {
		// Apply adjustments to the base ranges
		foreach ( $adjustments as $range_type => $value ) {
			if ( isset( $ranges[ $range_type ] ) ) {
				// Apply the adjustment value directly (this replaces the base value)
				$ranges[ $range_type ] = $value;
			}
		}

		return $ranges;
	}

	/**
	 * Get age group for range calculations
	 *
	 * @param int $age User age
	 * @return string Age group
	 */
	private function get_age_group( $age ) {
		if ( $age < 25 ) {
			return 'young';
		}
		if ( $age < 35 ) {
			return 'adult';
		}
		if ( $age < 50 ) {
			return 'adult';
		}
		if ( $age < 65 ) {
			return 'adult';
		}
		return 'senior';
	}

	/**
	 * Get user demographic data
	 *
	 * @param int $user_id User ID
	 * @return array User demographic data
	 */
	private function get_user_demographic_data( $user_id ) {
		$user_meta = get_user_meta( $user_id );

		$dob = isset( $user_meta['ennu_date_of_birth'][0] ) ? $user_meta['ennu_date_of_birth'][0] : '';
		$age = 35; // Default age

		if ( ! empty( $dob ) ) {
			$birth_date   = new DateTime( $dob );
			$current_date = new DateTime();
			$age          = $current_date->diff( $birth_date )->y;
		}

		return array(
			'age'    => $age,
			'gender' => isset( $user_meta['ennu_gender'][0] ) ? $user_meta['ennu_gender'][0] : 'male',
			'height' => isset( $user_meta['ennu_height'][0] ) ? floatval( $user_meta['ennu_height'][0] ) : 0,
			'weight' => isset( $user_meta['ennu_weight'][0] ) ? floatval( $user_meta['ennu_weight'][0] ) : 0,
		);
	}

	/**
	 * Get biomarker configuration with recommended ranges
	 *
	 * @return array Biomarker configuration
	 */
	public function get_biomarker_configuration() {
		// Load all biomarker ranges from AI medical specialists with error handling
		$cardiovascular_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/dr-victor-pulse/cardiovascular-ranges.php' );
		$hematology_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/dr-harlan-vitalis/hematology-ranges.php' );
		$neurology_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/dr-nora-cognita/neurology-ranges.php' );
		$endocrinology_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/dr-elena-harmonix/endocrinology-ranges.php' );
		$health_coaching_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/coach-aria-vital/health-coaching-ranges.php' );
		$sports_medicine_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/dr-silas-apex/sports-medicine-ranges.php' );
		$gerontology_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/dr-linus-eternal/gerontology-ranges.php' );
		$nephrology_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/dr-renata-flux/nephrology-hepatology-ranges.php' );
		$general_practice_ranges = $this->safe_include_file( '../ai-medical-research/official-documentation/specialist-assignments/biomarker-ranges/dr-orion-nexus/general-practice-ranges.php' );

		// Ensure all ranges are arrays before merging
		$all_ranges_arrays = array_filter( array(
			$cardiovascular_ranges,
			$hematology_ranges,
			$neurology_ranges,
			$endocrinology_ranges,
			$health_coaching_ranges,
			$sports_medicine_ranges,
			$gerontology_ranges,
			$nephrology_ranges,
			$general_practice_ranges
		), 'is_array' );

		// Merge all ranges into single configuration
		$all_ranges = array();
		foreach ( $all_ranges_arrays as $ranges ) {
			if ( is_array( $ranges ) ) {
				$all_ranges = array_merge( $all_ranges, $ranges );
			}
		}

		// Convert AI specialist format to ENNU system format with enhanced safety checks
		$biomarker_config = array();
		foreach ( $all_ranges as $biomarker_name => $ai_data ) {
			// Enhanced safety check for ai_data structure
			if ( ! is_array( $ai_data ) ) {
				error_log( "ENNU Recommended Range Manager: Invalid ai_data for {$biomarker_name}: not an array" );
				continue;
			}

			// Check for required fields with safe array access
			if ( ! array_key_exists( 'display_name', $ai_data ) || 
				 ! array_key_exists( 'unit', $ai_data ) || 
				 ! array_key_exists( 'description', $ai_data ) || 
				 ! array_key_exists( 'ranges', $ai_data ) ) {
				error_log( "ENNU Recommended Range Manager: Missing required fields for {$biomarker_name}" );
				continue;
			}

			$biomarker_config[ $biomarker_name ] = array(
				'display_name'       => $ai_data['display_name'],
				'unit'               => $ai_data['unit'],
				'description'        => $ai_data['description'],
				'ranges'             => $ai_data['ranges'],
				'age_adjustments'    => array_key_exists( 'age_adjustments', $ai_data ) ? $ai_data['age_adjustments'] : array(),
				'gender_adjustments' => array_key_exists( 'gender_adjustments', $ai_data ) ? $ai_data['gender_adjustments'] : array(),
				'risk_factors'       => array_key_exists( 'risk_factors', $ai_data ) ? $ai_data['risk_factors'] : array(),
				'clinical_significance' => array_key_exists( 'clinical_significance', $ai_data ) ? $ai_data['clinical_significance'] : '',
				'optimization_recommendations' => array_key_exists( 'optimization_recommendations', $ai_data ) ? $ai_data['optimization_recommendations'] : array(),
				'flag_criteria'      => array_key_exists( 'flag_criteria', $ai_data ) ? $ai_data['flag_criteria'] : array(),
				'scoring_algorithm'  => array_key_exists( 'scoring_algorithm', $ai_data ) ? $ai_data['scoring_algorithm'] : array(),
				'target_setting'     => array_key_exists( 'target_setting', $ai_data ) ? $ai_data['target_setting'] : array(),
				'sources'            => array_key_exists( 'sources', $ai_data ) ? $ai_data['sources'] : array(),
				'factors'            => array_key_exists( 'factors', $ai_data ) ? $ai_data['factors'] : array(),
			);
		}

		return $biomarker_config;
	}

	/**
	 * Safely include a file with error handling
	 *
	 * @param string $file_path Relative path to the file
	 * @return array|false Array data or false on error
	 */
	private function safe_include_file( $file_path ) {
		$full_path = plugin_dir_path( __FILE__ ) . $file_path;
		
		if ( ! file_exists( $full_path ) ) {
			error_log( "ENNU Recommended Range Manager: File not found: {$full_path}" );
			return array();
		}

		try {
			$data = include( $full_path );
			
			if ( ! is_array( $data ) ) {
				error_log( "ENNU Recommended Range Manager: File does not return array: {$full_path}" );
				return array();
			}
			
			return $data;
		} catch ( Exception $e ) {
			error_log( "ENNU Recommended Range Manager: Error including file {$full_path}: " . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Check if biomarker value is within recommended range
	 *
	 * @param string $biomarker_name Biomarker name
	 * @param float $value Biomarker value
	 * @param array $user_data User demographic data
	 * @return array Range status information
	 */
	public function check_biomarker_range( $biomarker_name, $value, $user_data = array() ) {
		$range_data = $this->get_recommended_range( $biomarker_name, $user_data );

		if ( isset( $range_data['error'] ) ) {
			return $range_data;
		}

		$status          = 'unknown';
		$message         = '';
		$recommendations = array();

		if ( $value >= $range_data['optimal_min'] && $value <= $range_data['optimal_max'] ) {
			$status            = 'optimal';
			$message           = 'Your ' . $range_data['display_name'] . ' is in the optimal range.';
			$recommendations[] = 'Maintain current lifestyle and health practices.';
		} elseif ( $value >= $range_data['normal_min'] && $value <= $range_data['normal_max'] ) {
			$status          = 'normal';
			$message         = 'Your ' . $range_data['display_name'] . ' is within normal range but could be optimized.';
			$recommendations = $this->get_optimization_recommendations( $biomarker_name, $value, $range_data );
		} elseif ( $value < $range_data['critical_min'] || $value > $range_data['critical_max'] ) {
			$status            = 'critical';
			$message           = 'Your ' . $range_data['display_name'] . ' is outside the safe range. Consult with a healthcare provider.';
			$recommendations[] = 'Schedule an appointment with your healthcare provider immediately.';
		} else {
			$status          = 'suboptimal';
			$message         = 'Your ' . $range_data['display_name'] . ' is outside the optimal range.';
			$recommendations = $this->get_improvement_recommendations( $biomarker_name, $value, $range_data );
		}

		return array(
			'biomarker'       => $biomarker_name,
			'value'           => $value,
			'status'          => $status,
			'message'         => $message,
			'recommendations' => $recommendations,
			'range_data'      => $range_data,
		);
	}

	/**
	 * Get optimization recommendations for normal range values
	 *
	 * @param string $biomarker_name Biomarker name
	 * @param float $value Current value
	 * @param array $range_data Range data
	 * @return array Recommendations
	 */
	private function get_optimization_recommendations( $biomarker_name, $value, $range_data ) {
		$recommendations = array();

		switch ( $biomarker_name ) {
			case 'testosterone_total':
				$recommendations[] = 'Consider strength training and adequate sleep for optimization.';
				$recommendations[] = 'Ensure adequate zinc and vitamin D intake.';
				break;
			case 'vitamin_d':
				$recommendations[] = 'Increase sun exposure or consider vitamin D3 supplementation.';
				$recommendations[] = 'Include vitamin D-rich foods in your diet.';
				break;
			case 'thyroid_tsh':
				$recommendations[] = 'Monitor thyroid function regularly.';
				$recommendations[] = 'Ensure adequate iodine and selenium intake.';
				break;
			case 'insulin':
				$recommendations[] = 'Focus on low-glycemic diet and regular exercise.';
				$recommendations[] = 'Consider intermittent fasting under medical guidance.';
				break;
			default:
				$recommendations[] = 'Maintain healthy lifestyle practices.';
				$recommendations[] = 'Monitor levels regularly and consult with healthcare provider.';
		}

		return $recommendations;
	}

	/**
	 * Get improvement recommendations for suboptimal values
	 *
	 * @param string $biomarker_name Biomarker name
	 * @param float $value Current value
	 * @param array $range_data Range data
	 * @return array Recommendations
	 */
	private function get_improvement_recommendations( $biomarker_name, $value, $range_data ) {
		$recommendations = array();
		$is_low          = $value < $range_data['optimal_min'];

		switch ( $biomarker_name ) {
			case 'testosterone_total':
				if ( $is_low ) {
					$recommendations[] = 'Implement strength training and high-intensity interval training.';
					$recommendations[] = 'Optimize sleep quality (7-9 hours nightly).';
					$recommendations[] = 'Consider zinc, vitamin D, and magnesium supplementation.';
					$recommendations[] = 'Manage stress through meditation or yoga.';
				} else {
					$recommendations[] = 'Reduce excessive exercise intensity.';
					$recommendations[] = 'Monitor for potential underlying conditions.';
				}
				break;
			case 'vitamin_d':
				if ( $is_low ) {
					$recommendations[] = 'Take vitamin D3 supplement (2000-4000 IU daily).';
					$recommendations[] = 'Increase safe sun exposure (15-30 minutes daily).';
					$recommendations[] = 'Include fatty fish and fortified foods in diet.';
				}
				break;
			case 'thyroid_tsh':
				if ( $is_low ) {
					$recommendations[] = 'Monitor for hyperthyroid symptoms.';
					$recommendations[] = 'Reduce stress and excessive stimulants.';
				} else {
					$recommendations[] = 'Support thyroid function with iodine and selenium.';
					$recommendations[] = 'Reduce goitrogenic foods (raw cruciferous vegetables).';
				}
				break;
			case 'insulin':
				if ( ! $is_low ) {
					$recommendations[] = 'Adopt low-carbohydrate or ketogenic diet.';
					$recommendations[] = 'Increase physical activity, especially resistance training.';
					$recommendations[] = 'Consider intermittent fasting protocols.';
					$recommendations[] = 'Reduce processed foods and added sugars.';
				}
				break;
			default:
				$recommendations[] = 'Consult with healthcare provider for personalized recommendations.';
				$recommendations[] = 'Focus on overall health optimization through diet and exercise.';
		}

		return $recommendations;
	}

	/**
	 * AJAX handler for getting recommended ranges
	 */
	public function ajax_get_recommended_ranges() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not authenticated' );
			return;
		}

		$biomarker = sanitize_text_field( $_POST['biomarker'] ?? '' );

		if ( empty( $biomarker ) ) {
			$ranges = $this->get_user_recommended_ranges( $user_id );
		} else {
			$user_data = $this->get_user_demographic_data( $user_id );
			$ranges    = $this->get_recommended_range( $biomarker, $user_data );
		}

		wp_send_json_success( $ranges );
	}

	/**
	 * AJAX handler for updating user-specific ranges
	 */
	public function ajax_update_user_ranges() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not authenticated' );
			return;
		}

		$biomarker = sanitize_text_field( $_POST['biomarker'] ?? '' );
		$value     = floatval( $_POST['value'] ?? 0 );

		if ( empty( $biomarker ) || $value <= 0 ) {
			wp_send_json_error( 'Invalid biomarker or value' );
			return;
		}

		$user_data   = $this->get_user_demographic_data( $user_id );
		$range_check = $this->check_biomarker_range( $biomarker, $value, $user_data );

		$user_ranges = get_user_meta( $user_id, 'ennu_biomarker_ranges', true );
		if ( ! is_array( $user_ranges ) ) {
			$user_ranges = array();
		}

		$user_ranges[ $biomarker ] = array(
			'value'           => $value,
			'status'          => $range_check['status'],
			'checked_date'    => current_time( 'mysql' ),
			'recommendations' => $range_check['recommendations'],
		);

		update_user_meta( $user_id, 'ennu_biomarker_ranges', $user_ranges );

		wp_send_json_success( $range_check );
	}
	
	/**
	 * AJAX handler for synchronizing all user biomarker data
	 * Admin-only function to manually trigger synchronization
	 */
	public function ajax_synchronize_user_data() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
			return;
		}
		
		$biomarker = sanitize_text_field( $_POST['biomarker'] ?? '' );
		
		// Perform synchronization
		$this->synchronize_user_biomarker_data($biomarker);
		
		$biomarker_text = !empty($biomarker) ? "biomarker '{$biomarker}'" : 'all biomarkers';
		wp_send_json_success( "Successfully synchronized {$biomarker_text} for all users" );
	}
	
	/**
	 * Synchronize all user biomarker data when AI Medical Team updates ranges
	 * This ensures all users get the latest validated ranges
	 * 
	 * @param string $biomarker Biomarker key (optional, if empty syncs all)
	 */
	public function synchronize_user_biomarker_data($biomarker = '') {
		global $wpdb;
		
		// Get all users
		$users = get_users(array('fields' => 'ID'));
		
		foreach ($users as $user_id) {
			// Get user's current biomarker data
			$user_biomarker_data = get_user_meta($user_id, 'ennu_biomarker_data', true);
			$doctor_targets = get_user_meta($user_id, 'ennu_doctor_targets', true);
			
			if (!is_array($user_biomarker_data)) {
				$user_biomarker_data = array();
			}
			if (!is_array($doctor_targets)) {
				$doctor_targets = array();
			}
			
			// Get user demographic data
			$user_data = $this->get_user_demographic_data($user_id);
			
			// If specific biomarker, only update that one
			if (!empty($biomarker)) {
				$this->update_user_biomarker_if_exists($user_id, $biomarker, $user_biomarker_data, $doctor_targets, $user_data);
			} else {
				// Update all biomarkers for this user
				$biomarker_config = $this->get_biomarker_configuration();
				foreach ($biomarker_config as $biomarker_key => $config) {
					$this->update_user_biomarker_if_exists($user_id, $biomarker_key, $user_biomarker_data, $doctor_targets, $user_data);
				}
			}
			
			// Save updated data
			update_user_meta($user_id, 'ennu_biomarker_data', $user_biomarker_data);
			update_user_meta($user_id, 'ennu_doctor_targets', $doctor_targets);
		}
		
		$biomarker_text = !empty($biomarker) ? "biomarker '{$biomarker}'" : 'all biomarkers';
		error_log("ENNU Recommended Range Manager: Synchronized {$biomarker_text} data for " . count($users) . " users");
	}
	
	/**
	 * Update user biomarker data if it exists
	 * 
	 * @param int $user_id User ID
	 * @param string $biomarker_key Biomarker key
	 * @param array $user_biomarker_data User biomarker data (by reference)
	 * @param array $doctor_targets Doctor targets (by reference)
	 * @param array $user_data User demographic data
	 */
	private function update_user_biomarker_if_exists($user_id, $biomarker_key, &$user_biomarker_data, &$doctor_targets, $user_data) {
		// Get latest range for this biomarker
		$latest_range = $this->get_recommended_range($biomarker_key, $user_data);
		
		if (isset($latest_range['error'])) {
			return; // Skip if biomarker not found
		}
		
		// Update biomarker data if it exists for this user
		if (isset($user_biomarker_data[$biomarker_key])) {
			// Update unit if it changed
			if (isset($latest_range['unit'])) {
				$user_biomarker_data[$biomarker_key]['unit'] = $latest_range['unit'];
			}
			
			// Update range validation
			$current_value = $user_biomarker_data[$biomarker_key]['value'];
			$range_check = $this->check_biomarker_range($biomarker_key, $current_value, $user_data);
			
			$user_biomarker_data[$biomarker_key]['status'] = $range_check['status'];
			$user_biomarker_data[$biomarker_key]['last_validated'] = current_time('mysql');
		}
		
		// Update doctor targets if they exist for this user
		if (isset($doctor_targets[$biomarker_key])) {
			$target_value = $doctor_targets[$biomarker_key];
			$optimal_min = $latest_range['optimal_min'] ?? 0;
			$optimal_max = $latest_range['optimal_max'] ?? 100;
			
			// If target is outside new optimal range, flag it for review
			if ($target_value < $optimal_min || $target_value > $optimal_max) {
				$doctor_targets[$biomarker_key . '_needs_review'] = true;
				$doctor_targets[$biomarker_key . '_review_date'] = current_time('mysql');
			}
		}
	}

	/**
	 * Check biomarker ranges when new biomarker data is uploaded
	 *
	 * @param int $user_id User ID
	 * @param array $biomarker_data Uploaded biomarker data
	 */
	public function check_biomarker_ranges( $user_id, $biomarker_data ) {
		$user_data     = $this->get_user_demographic_data( $user_id );
		$range_results = array();

		foreach ( $biomarker_data as $biomarker => $value ) {
			$range_check                 = $this->check_biomarker_range( $biomarker, $value, $user_data );
			$range_results[ $biomarker ] = $range_check;

			if ( $range_check['status'] === 'critical' ) {
				error_log( "ENNU: Critical biomarker value detected for user {$user_id}: {$biomarker} = {$value}" );

				do_action( 'ennu_critical_biomarker_detected', $user_id, $biomarker, $value, $range_check );
			}
		}

		update_user_meta( $user_id, 'ennu_biomarker_ranges', $range_results );

		return $range_results;
	}

	/**
	 * Get range display HTML for dashboard
	 *
	 * @param string $biomarker_name Biomarker name
	 * @param float $value Current value
	 * @param array $user_data User demographic data
	 * @return string HTML output
	 */
	public function get_range_display_html( $biomarker_name, $value, $user_data = array() ) {
		$range_check = $this->check_biomarker_range( $biomarker_name, $value, $user_data );

		if ( isset( $range_check['error'] ) ) {
			return '<div class="ennu-range-error">Range data not available</div>';
		}

		$status_class = 'ennu-range-' . $range_check['status'];
		$range_data   = $range_check['range_data'];

		$html  = '<div class="ennu-biomarker-range ' . $status_class . '">';
		$html .= '<div class="ennu-range-header">';
		$html .= '<h4>' . esc_html( $range_data['display_name'] ) . '</h4>';
		$html .= '<span class="ennu-range-value">' . esc_html( $value ) . ' ' . esc_html( $range_data['unit'] ) . '</span>';
		$html .= '</div>';

		$html .= '<div class="ennu-range-bar">';
		$html .= '<div class="ennu-range-optimal" style="left: ' . $this->calculate_range_position( $range_data['optimal_min'], $range_data ) . '%; width: ' . $this->calculate_range_width( $range_data['optimal_min'], $range_data['optimal_max'], $range_data ) . '%"></div>';
		$html .= '<div class="ennu-range-marker" style="left: ' . $this->calculate_value_position( $value, $range_data ) . '%"></div>';
		$html .= '</div>';

		$html .= '<div class="ennu-range-labels">';
		$html .= '<span class="ennu-range-min">' . esc_html( $range_data['normal_min'] ) . '</span>';
		$html .= '<span class="ennu-range-optimal-label">Optimal: ' . esc_html( $range_data['optimal_min'] ) . '-' . esc_html( $range_data['optimal_max'] ) . '</span>';
		$html .= '<span class="ennu-range-max">' . esc_html( $range_data['normal_max'] ) . '</span>';
		$html .= '</div>';

		$html .= '<div class="ennu-range-status">';
		$html .= '<p class="ennu-range-message">' . esc_html( $range_check['message'] ) . '</p>';
		if ( ! empty( $range_check['recommendations'] ) ) {
			$html .= '<ul class="ennu-range-recommendations">';
			foreach ( $range_check['recommendations'] as $recommendation ) {
				$html .= '<li>' . esc_html( $recommendation ) . '</li>';
			}
			$html .= '</ul>';
		}
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Calculate position percentage for range visualization
	 *
	 * @param float $value Value to position
	 * @param array $range_data Range data
	 * @return float Position percentage
	 */
	private function calculate_value_position( $value, $range_data ) {
		$min   = $range_data['critical_min'];
		$max   = $range_data['critical_max'];
		$range = $max - $min;

		if ( $range <= 0 ) {
			return 50;
		}

		$position = ( ( $value - $min ) / $range ) * 100;
		return max( 0, min( 100, $position ) );
	}

	/**
	 * Calculate range position for visualization
	 *
	 * @param float $range_min Range minimum
	 * @param array $range_data Full range data
	 * @return float Position percentage
	 */
	private function calculate_range_position( $range_min, $range_data ) {
		return $this->calculate_value_position( $range_min, $range_data );
	}

	/**
	 * Calculate range width for visualization
	 *
	 * @param float $range_min Range minimum
	 * @param float $range_max Range maximum
	 * @param array $range_data Full range data
	 * @return float Width percentage
	 */
	private function calculate_range_width( $range_min, $range_max, $range_data ) {
		$min_pos = $this->calculate_value_position( $range_min, $range_data );
		$max_pos = $this->calculate_value_position( $range_max, $range_data );
		return $max_pos - $min_pos;
	}
}
