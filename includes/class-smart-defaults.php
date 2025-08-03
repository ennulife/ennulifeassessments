<?php
/**
 * ENNU Life Assessments - Smart Defaults System
 *
 * Provides intelligent form pre-filling, auto-complete suggestions,
 * and progressive enhancement to improve user experience.
 *
 * @package   ENNU Life Assessments
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     3.37.14
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Smart Defaults System
 *
 * Manages intelligent form pre-filling, auto-complete,
 * and progressive enhancement for optimal user experience.
 *
 * @since 3.37.14
 */
class ENNU_Smart_Defaults {

	/**
	 * Smart defaults configuration
	 *
	 * @var array
	 */
	private $smart_defaults = array();

	/**
	 * Auto-complete suggestions
	 *
	 * @var array
	 */
	private $auto_complete_data = array();

	/**
	 * User preferences
	 *
	 * @var array
	 */
	private $user_preferences = array();

	/**
	 * Initialize the smart defaults system
	 *
	 * @since 3.37.14
	 */
	public function __construct() {
		$this->init_hooks();
		$this->setup_smart_defaults();
		$this->setup_auto_complete_data();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 3.37.14
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_smart_defaults_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_smart_defaults_assets' ) );
		add_action( 'wp_ajax_ennu_get_smart_defaults', array( $this, 'get_smart_defaults_ajax' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_smart_defaults', array( $this, 'get_smart_defaults_ajax' ) );
		add_action( 'wp_ajax_ennu_save_user_preferences', array( $this, 'save_user_preferences' ) );
		add_action( 'wp_ajax_nopriv_ennu_save_user_preferences', array( $this, 'save_user_preferences' ) );
		add_filter( 'ennu_form_defaults', array( $this, 'apply_smart_defaults' ), 10, 2 );
		add_filter( 'ennu_auto_complete_suggestions', array( $this, 'get_auto_complete_suggestions' ), 10, 2 );
	}

	/**
	 * Setup smart defaults configuration
	 *
	 * @since 3.37.14
	 */
	private function setup_smart_defaults() {
		$this->smart_defaults = array(
			'personal_info' => array(
				'age' => array(
					'type' => 'calculated',
					'source' => 'user_meta',
					'field' => 'age',
					'fallback' => '',
				),
				'gender' => array(
					'type' => 'user_meta',
					'source' => 'user_meta',
					'field' => 'gender',
					'fallback' => '',
				),
				'height' => array(
					'type' => 'user_meta',
					'source' => 'user_meta',
					'field' => 'height',
					'fallback' => '',
				),
				'weight' => array(
					'type' => 'user_meta',
					'source' => 'user_meta',
					'field' => 'weight',
					'fallback' => '',
				),
			),
			'biomarkers' => array(
				'blood_pressure_systolic' => array(
					'type' => 'last_measurement',
					'source' => 'biomarker_history',
					'field' => 'blood_pressure_systolic',
					'fallback' => '',
				),
				'blood_pressure_diastolic' => array(
					'type' => 'last_measurement',
					'source' => 'biomarker_history',
					'field' => 'blood_pressure_diastolic',
					'fallback' => '',
				),
				'cholesterol_total' => array(
					'type' => 'last_measurement',
					'source' => 'biomarker_history',
					'field' => 'cholesterol_total',
					'fallback' => '',
				),
				'glucose' => array(
					'type' => 'last_measurement',
					'source' => 'biomarker_history',
					'field' => 'glucose',
					'fallback' => '',
				),
			),
			'assessment_preferences' => array(
				'preferred_assessment_type' => array(
					'type' => 'user_preference',
					'source' => 'user_preferences',
					'field' => 'preferred_assessment_type',
					'fallback' => 'comprehensive',
				),
				'notification_frequency' => array(
					'type' => 'user_preference',
					'source' => 'user_preferences',
					'field' => 'notification_frequency',
					'fallback' => 'weekly',
				),
				'data_sharing_preference' => array(
					'type' => 'user_preference',
					'source' => 'user_preferences',
					'field' => 'data_sharing_preference',
					'fallback' => 'limited',
				),
			),
		);
	}

	/**
	 * Setup auto-complete data
	 *
	 * @since 3.37.14
	 */
	private function setup_auto_complete_data() {
		$this->auto_complete_data = array(
			'common_symptoms' => array(
				'fatigue',
				'brain_fog',
				'anxiety',
				'depression',
				'insomnia',
				'weight_gain',
				'weight_loss',
				'muscle_weakness',
				'joint_pain',
				'headaches',
				'digestive_issues',
				'low_libido',
				'mood_swings',
				'irritability',
				'concentration_problems',
			),
			'common_medications' => array(
				'aspirin',
				'ibuprofen',
				'acetaminophen',
				'vitamin_d',
				'multivitamin',
				'omega_3',
				'probiotics',
				'magnesium',
				'zinc',
				'vitamin_c',
			),
			'common_activities' => array(
				'walking',
				'running',
				'cycling',
				'swimming',
				'yoga',
				'weight_training',
				'pilates',
				'dancing',
				'hiking',
				'tennis',
			),
			'common_diets' => array(
				'mediterranean',
				'keto',
				'paleo',
				'vegan',
				'vegetarian',
				'low_carb',
				'low_fat',
				'intermittent_fasting',
				'whole30',
				'plant_based',
			),
		);
	}

	/**
	 * Enqueue smart defaults assets
	 *
	 * @since 3.37.14
	 */
	public function enqueue_smart_defaults_assets() {
		wp_enqueue_script(
			'ennu-smart-defaults',
			plugins_url( 'assets/js/smart-defaults.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			ENNU_LIFE_VERSION,
			true
		);

		wp_enqueue_style(
			'ennu-smart-defaults',
			plugins_url( 'assets/css/smart-defaults.css', dirname( __FILE__ ) ),
			array(),
			ENNU_LIFE_VERSION
		);

		// Localize script with smart defaults data
		wp_localize_script(
			'ennu-smart-defaults',
			'ennuSmartDefaults',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'ennu_smart_defaults_nonce' ),
				'smartDefaults' => $this->smart_defaults,
				'autoCompleteData' => $this->auto_complete_data,
				'userPreferences' => $this->get_user_preferences(),
				'strings'    => array(
					'auto_complete' => __( 'Auto-complete', 'ennulifeassessments' ),
					'smart_suggestion' => __( 'Smart suggestion', 'ennulifeassessments' ),
					'use_suggestion' => __( 'Use suggestion', 'ennulifeassessments' ),
					'clear_suggestion' => __( 'Clear suggestion', 'ennulifeassessments' ),
				),
			)
		);
	}

	/**
	 * Get smart defaults via AJAX
	 *
	 * @since 3.37.14
	 */
	public function get_smart_defaults_ajax() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_smart_defaults_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$form_type = sanitize_text_field( wp_unslash( $_POST['form_type'] ) );
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			wp_send_json_error( __( 'User not logged in', 'ennulifeassessments' ) );
		}

		$defaults = $this->get_smart_defaults_for_form( $form_type, $user_id );
		wp_send_json_success( $defaults );
	}

	/**
	 * Save user preferences via AJAX
	 *
	 * @since 3.37.14
	 */
	public function save_user_preferences() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_smart_defaults_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( __( 'User not logged in', 'ennulifeassessments' ) );
		}

		$preferences = sanitize_text_field( wp_unslash( $_POST['preferences'] ) );
		$preferences_array = json_decode( stripslashes( $preferences ), true );

		if ( is_array( $preferences_array ) ) {
			update_user_meta( $user_id, '_ennu_user_preferences', $preferences_array );
			wp_send_json_success( __( 'Preferences saved', 'ennulifeassessments' ) );
		} else {
			wp_send_json_error( __( 'Invalid preferences data', 'ennulifeassessments' ) );
		}
	}

	/**
	 * Apply smart defaults to forms
	 *
	 * @since 3.37.14
	 * @param array $defaults Current defaults.
	 * @param string $form_type Form type.
	 * @return array
	 */
	public function apply_smart_defaults( $defaults, $form_type ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $defaults;
		}

		$smart_defaults = $this->get_smart_defaults_for_form( $form_type, $user_id );
		return array_merge( $defaults, $smart_defaults );
	}

	/**
	 * Get auto-complete suggestions
	 *
	 * @since 3.37.14
	 * @param array $suggestions Current suggestions.
	 * @param string $field_type Field type.
	 * @return array
	 */
	public function get_auto_complete_suggestions( $suggestions, $field_type ) {
		if ( isset( $this->auto_complete_data[ $field_type ] ) ) {
			return array_merge( $suggestions, $this->auto_complete_data[ $field_type ] );
		}

		return $suggestions;
	}

	/**
	 * Get smart defaults for a specific form
	 *
	 * @since 3.37.14
	 * @param string $form_type Form type.
	 * @param int    $user_id User ID.
	 * @return array
	 */
	private function get_smart_defaults_for_form( $form_type, $user_id ) {
		$defaults = array();

		if ( ! isset( $this->smart_defaults[ $form_type ] ) ) {
			return $defaults;
		}

		foreach ( $this->smart_defaults[ $form_type ] as $field => $config ) {
			$value = $this->get_smart_default_value( $config, $user_id );
			if ( ! empty( $value ) ) {
				$defaults[ $field ] = $value;
			}
		}

		return $defaults;
	}

	/**
	 * Get smart default value for a specific field
	 *
	 * @since 3.37.14
	 * @param array $config Field configuration.
	 * @param int   $user_id User ID.
	 * @return mixed
	 */
	private function get_smart_default_value( $config, $user_id ) {
		switch ( $config['type'] ) {
			case 'user_meta':
				return get_user_meta( $user_id, $config['field'], true );

			case 'calculated':
				return $this->calculate_smart_default( $config, $user_id );

			case 'last_measurement':
				return $this->get_last_measurement( $config, $user_id );

			case 'user_preference':
				return $this->get_user_preference( $config, $user_id );

			default:
				return $config['fallback'] ?? '';
		}
	}

	/**
	 * Calculate smart default value
	 *
	 * @since 3.37.14
	 * @param array $config Field configuration.
	 * @param int   $user_id User ID.
	 * @return mixed
	 */
	private function calculate_smart_default( $config, $user_id ) {
		switch ( $config['field'] ) {
			case 'age':
				$birth_date = get_user_meta( $user_id, 'birth_date', true );
				if ( ! empty( $birth_date ) ) {
					$birth = new DateTime( $birth_date );
					$now = new DateTime();
					$age = $now->diff( $birth )->y;
					return $age;
				}
				break;

			case 'bmi':
				$height = get_user_meta( $user_id, 'height', true );
				$weight = get_user_meta( $user_id, 'weight', true );
				if ( ! empty( $height ) && ! empty( $weight ) ) {
					$height_m = $height / 100; // Convert cm to meters
					$bmi = $weight / ( $height_m * $height_m );
					return round( $bmi, 1 );
				}
				break;
		}

		return $config['fallback'] ?? '';
	}

	/**
	 * Get last measurement for a biomarker
	 *
	 * @since 3.37.14
	 * @param array $config Field configuration.
	 * @param int   $user_id User ID.
	 * @return mixed
	 */
	private function get_last_measurement( $config, $user_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ennu_biomarker_measurements';
		$last_measurement = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT value FROM {$table_name} WHERE user_id = %d AND biomarker_key = %s ORDER BY measurement_date DESC LIMIT 1",
				$user_id,
				$config['field']
			)
		);

		return $last_measurement ?: $config['fallback'] ?? '';
	}

	/**
	 * Get user preference
	 *
	 * @since 3.37.14
	 * @param array $config Field configuration.
	 * @param int   $user_id User ID.
	 * @return mixed
	 */
	private function get_user_preference( $config, $user_id ) {
		$preferences = get_user_meta( $user_id, '_ennu_user_preferences', true );
		
		if ( is_array( $preferences ) && isset( $preferences[ $config['field'] ] ) ) {
			return $preferences[ $config['field'] ];
		}

		return $config['fallback'] ?? '';
	}

	/**
	 * Get user preferences
	 *
	 * @since 3.37.14
	 * @return array
	 */
	private function get_user_preferences() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}

		$preferences = get_user_meta( $user_id, '_ennu_user_preferences', true );
		return is_array( $preferences ) ? $preferences : array();
	}

	/**
	 * Render smart defaults trigger
	 *
	 * @since 3.37.14
	 * @param string $form_type Form type.
	 * @return string
	 */
	public function render_smart_defaults_trigger( $form_type ) {
		ob_start();
		?>
		<div class="ennu-smart-defaults-trigger" data-form-type="<?php echo esc_attr( $form_type ); ?>">
			<button class="ennu-smart-defaults-btn" id="ennu-smart-defaults-btn" aria-label="<?php esc_attr_e( 'Apply smart defaults', 'ennulifeassessments' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
					<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
				</svg>
				<?php esc_html_e( 'Smart Fill', 'ennulifeassessments' ); ?>
			</button>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render auto-complete field
	 *
	 * @since 3.37.14
	 * @param string $field_name Field name.
	 * @param string $field_type Field type.
	 * @param array  $config Field configuration.
	 * @return string
	 */
	public function render_auto_complete_field( $field_name, $field_type, $config = array() ) {
		$default_config = array(
			'placeholder' => '',
			'required'    => false,
			'class'       => '',
		);

		$config = wp_parse_args( $config, $default_config );

		ob_start();
		?>
		<div class="ennu-auto-complete-field <?php echo esc_attr( $config['class'] ); ?>">
			<input type="text" 
				   name="<?php echo esc_attr( $field_name ); ?>" 
				   id="<?php echo esc_attr( $field_name ); ?>"
				   class="ennu-auto-complete-input"
				   data-field-type="<?php echo esc_attr( $field_type ); ?>"
				   placeholder="<?php echo esc_attr( $config['placeholder'] ); ?>"
				   <?php echo $config['required'] ? 'required' : ''; ?>>
			<div class="ennu-auto-complete-suggestions" id="ennu-auto-complete-<?php echo esc_attr( $field_name ); ?>"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get smart defaults statistics
	 *
	 * @since 3.37.14
	 * @return array
	 */
	public function get_smart_defaults_stats() {
		$stats = array(
			'total_uses' => 0,
			'most_used_forms' => array(),
			'average_time_saved' => 0,
		);

		// Get smart defaults usage statistics
		global $wpdb;
		
		$usage_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				'_ennu_smart_defaults_usage_%'
			)
		);
		$stats['total_uses'] = (int) $usage_count;

		return $stats;
	}

	/**
	 * Track smart defaults usage
	 *
	 * @since 3.37.14
	 * @param string $form_type Form type.
	 */
	public function track_smart_defaults_usage( $form_type ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$usage = get_user_meta( $user_id, '_ennu_smart_defaults_usage', true );
		if ( ! is_array( $usage ) ) {
			$usage = array();
		}

		$usage[] = array(
			'form_type' => $form_type,
			'timestamp' => current_time( 'mysql' ),
		);

		update_user_meta( $user_id, '_ennu_smart_defaults_usage', $usage );
	}

	/**
	 * Add custom smart default
	 *
	 * @since 3.37.14
	 * @param string $form_type Form type.
	 * @param string $field Field name.
	 * @param array  $config Field configuration.
	 */
	public function add_smart_default( $form_type, $field, $config ) {
		if ( ! isset( $this->smart_defaults[ $form_type ] ) ) {
			$this->smart_defaults[ $form_type ] = array();
		}

		$this->smart_defaults[ $form_type ][ $field ] = $config;
	}

	/**
	 * Add custom auto-complete data
	 *
	 * @since 3.37.14
	 * @param string $type Data type.
	 * @param array  $data Auto-complete data.
	 */
	public function add_auto_complete_data( $type, $data ) {
		$this->auto_complete_data[ $type ] = $data;
	}
}

// Initialize the smart defaults system
if ( class_exists( 'ENNU_Smart_Defaults' ) ) {
	new ENNU_Smart_Defaults();
} 