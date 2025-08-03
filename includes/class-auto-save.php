<?php
/**
 * ENNU Life Assessments - Auto-Save System
 *
 * Provides automatic form saving, progress persistence,
 * and recovery options to prevent data loss.
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
 * ENNU Auto-Save System
 *
 * Manages automatic form saving, progress persistence,
 * and recovery options for optimal user experience.
 *
 * @since 3.37.14
 */
class ENNU_Auto_Save {

	/**
	 * Auto-save configuration
	 *
	 * @var array
	 */
	private $auto_save_config = array();

	/**
	 * Form data cache
	 *
	 * @var array
	 */
	private $form_cache = array();

	/**
	 * Initialize the auto-save system
	 *
	 * @since 3.37.14
	 */
	public function __construct() {
		$this->init_hooks();
		$this->setup_auto_save_config();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 3.37.14
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_auto_save_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_auto_save_assets' ) );
		add_action( 'wp_ajax_ennu_auto_save_form', array( $this, 'auto_save_form' ) );
		add_action( 'wp_ajax_nopriv_ennu_auto_save_form', array( $this, 'auto_save_form' ) );
		add_action( 'wp_ajax_ennu_restore_form_data', array( $this, 'restore_form_data' ) );
		add_action( 'wp_ajax_nopriv_ennu_restore_form_data', array( $this, 'restore_form_data' ) );
		add_action( 'wp_ajax_ennu_clear_auto_save', array( $this, 'clear_auto_save' ) );
		add_action( 'wp_ajax_nopriv_ennu_clear_auto_save', array( $this, 'clear_auto_save' ) );
		add_action( 'wp_footer', array( $this, 'render_auto_save_notifications' ) );
		add_action( 'admin_footer', array( $this, 'render_auto_save_notifications' ) );
		add_filter( 'ennu_form_auto_save', array( $this, 'apply_auto_save' ), 10, 2 );
	}

	/**
	 * Setup auto-save configuration
	 *
	 * @since 3.37.14
	 */
	private function setup_auto_save_config() {
		$this->auto_save_config = array(
			'assessment_form' => array(
				'interval' => 30000, // 30 seconds
				'enabled' => true,
				'fields' => array(
					'personal_info',
					'biomarkers',
					'symptoms',
					'lifestyle',
					'medications',
				),
				'max_versions' => 5,
				'retention_days' => 7,
			),
			'biomarker_form' => array(
				'interval' => 15000, // 15 seconds
				'enabled' => true,
				'fields' => array(
					'biomarker_values',
					'measurement_date',
					'notes',
				),
				'max_versions' => 10,
				'retention_days' => 30,
			),
			'profile_form' => array(
				'interval' => 20000, // 20 seconds
				'enabled' => true,
				'fields' => array(
					'personal_info',
					'preferences',
					'notifications',
				),
				'max_versions' => 3,
				'retention_days' => 14,
			),
			'goal_form' => array(
				'interval' => 25000, // 25 seconds
				'enabled' => true,
				'fields' => array(
					'goal_type',
					'target_value',
					'timeline',
					'action_plan',
				),
				'max_versions' => 5,
				'retention_days' => 21,
			),
		);
	}

	/**
	 * Enqueue auto-save assets
	 *
	 * @since 3.37.14
	 */
	public function enqueue_auto_save_assets() {
		wp_enqueue_script(
			'ennu-auto-save',
			plugins_url( 'assets/js/auto-save.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			ENNU_LIFE_VERSION,
			true
		);

		wp_enqueue_style(
			'ennu-auto-save',
			plugins_url( 'assets/css/auto-save.css', dirname( __FILE__ ) ),
			array(),
			ENNU_LIFE_VERSION
		);

		// Localize script with auto-save data
		wp_localize_script(
			'ennu-auto-save',
			'ennuAutoSave',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'ennu_auto_save_nonce' ),
				'config'     => $this->auto_save_config,
				'strings'    => array(
					'auto_saved' => __( 'Auto-saved', 'ennulifeassessments' ),
					'saving'     => __( 'Saving...', 'ennulifeassessments' ),
					'saved'      => __( 'Saved', 'ennulifeassessments' ),
					'error'      => __( 'Save failed', 'ennulifeassessments' ),
					'restore'    => __( 'Restore', 'ennulifeassessments' ),
					'clear'      => __( 'Clear', 'ennulifeassessments' ),
				),
			)
		);
	}

	/**
	 * Auto-save form data via AJAX
	 *
	 * @since 3.37.14
	 */
	public function auto_save_form() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_auto_save_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( __( 'User not logged in', 'ennulifeassessments' ) );
		}

		$form_type = sanitize_text_field( wp_unslash( $_POST['form_type'] ) );
		$form_data = sanitize_text_field( wp_unslash( $_POST['form_data'] ) );
		$form_data_array = json_decode( stripslashes( $form_data ), true );

		if ( ! is_array( $form_data_array ) ) {
			wp_send_json_error( __( 'Invalid form data', 'ennulifeassessments' ) );
		}

		$result = $this->save_form_data( $user_id, $form_type, $form_data_array );
		
		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Form auto-saved successfully', 'ennulifeassessments' ),
				'timestamp' => current_time( 'mysql' ),
			) );
		} else {
			wp_send_json_error( __( 'Failed to auto-save form', 'ennulifeassessments' ) );
		}
	}

	/**
	 * Restore form data via AJAX
	 *
	 * @since 3.37.14
	 */
	public function restore_form_data() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_auto_save_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( __( 'User not logged in', 'ennulifeassessments' ) );
		}

		$form_type = sanitize_text_field( wp_unslash( $_POST['form_type'] ) );
		$version = absint( $_POST['version'] );

		$form_data = $this->get_form_data( $user_id, $form_type, $version );
		
		if ( $form_data ) {
			wp_send_json_success( array(
				'form_data' => $form_data,
				'message' => __( 'Form data restored successfully', 'ennulifeassessments' ),
			) );
		} else {
			wp_send_json_error( __( 'No saved data found', 'ennulifeassessments' ) );
		}
	}

	/**
	 * Clear auto-save data via AJAX
	 *
	 * @since 3.37.14
	 */
	public function clear_auto_save() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_auto_save_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( __( 'User not logged in', 'ennulifeassessments' ) );
		}

		$form_type = sanitize_text_field( wp_unslash( $_POST['form_type'] ) );

		$result = $this->clear_form_data( $user_id, $form_type );
		
		if ( $result ) {
			wp_send_json_success( __( 'Auto-save data cleared successfully', 'ennulifeassessments' ) );
		} else {
			wp_send_json_error( __( 'Failed to clear auto-save data', 'ennulifeassessments' ) );
		}
	}

	/**
	 * Apply auto-save to forms
	 *
	 * @since 3.37.14
	 * @param array $form_data Form data.
	 * @param string $form_type Form type.
	 * @return array
	 */
	public function apply_auto_save( $form_data, $form_type ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $form_data;
		}

		$saved_data = $this->get_latest_form_data( $user_id, $form_type );
		if ( $saved_data ) {
			return array_merge( $form_data, $saved_data );
		}

		return $form_data;
	}

	/**
	 * Save form data
	 *
	 * @since 3.37.14
	 * @param int    $user_id User ID.
	 * @param string $form_type Form type.
	 * @param array  $form_data Form data.
	 * @return bool
	 */
	private function save_form_data( $user_id, $form_type, $form_data ) {
		$auto_save_key = '_ennu_auto_save_' . $form_type;
		$auto_save_data = get_user_meta( $user_id, $auto_save_key, true );

		if ( ! is_array( $auto_save_data ) ) {
			$auto_save_data = array();
		}

		// Add new version
		$version = array(
			'data' => $form_data,
			'timestamp' => current_time( 'mysql' ),
			'version' => count( $auto_save_data ) + 1,
		);

		$auto_save_data[] = $version;

		// Limit versions based on config
		if ( isset( $this->auto_save_config[ $form_type ]['max_versions'] ) ) {
			$max_versions = $this->auto_save_config[ $form_type ]['max_versions'];
			if ( count( $auto_save_data ) > $max_versions ) {
				$auto_save_data = array_slice( $auto_save_data, -$max_versions );
			}
		}

		$result = update_user_meta( $user_id, $auto_save_key, $auto_save_data );
		return $result;
	}

	/**
	 * Get form data
	 *
	 * @since 3.37.14
	 * @param int    $user_id User ID.
	 * @param string $form_type Form type.
	 * @param int    $version Version number.
	 * @return array|false
	 */
	private function get_form_data( $user_id, $form_type, $version = 0 ) {
		$auto_save_key = '_ennu_auto_save_' . $form_type;
		$auto_save_data = get_user_meta( $user_id, $auto_save_key, true );

		if ( ! is_array( $auto_save_data ) || empty( $auto_save_data ) ) {
			return false;
		}

		if ( $version > 0 ) {
			// Get specific version
			foreach ( $auto_save_data as $data ) {
				if ( $data['version'] === $version ) {
					return $data['data'];
				}
			}
		} else {
			// Get latest version
			$latest = end( $auto_save_data );
			return $latest['data'];
		}

		return false;
	}

	/**
	 * Get latest form data
	 *
	 * @since 3.37.14
	 * @param int    $user_id User ID.
	 * @param string $form_type Form type.
	 * @return array|false
	 */
	private function get_latest_form_data( $user_id, $form_type ) {
		return $this->get_form_data( $user_id, $form_type, 0 );
	}

	/**
	 * Clear form data
	 *
	 * @since 3.37.14
	 * @param int    $user_id User ID.
	 * @param string $form_type Form type.
	 * @return bool
	 */
	private function clear_form_data( $user_id, $form_type ) {
		$auto_save_key = '_ennu_auto_save_' . $form_type;
		return delete_user_meta( $user_id, $auto_save_key );
	}

	/**
	 * Get auto-save versions
	 *
	 * @since 3.37.14
	 * @param int    $user_id User ID.
	 * @param string $form_type Form type.
	 * @return array
	 */
	private function get_auto_save_versions( $user_id, $form_type ) {
		$auto_save_key = '_ennu_auto_save_' . $form_type;
		$auto_save_data = get_user_meta( $user_id, $auto_save_key, true );

		if ( ! is_array( $auto_save_data ) ) {
			return array();
		}

		return $auto_save_data;
	}

	/**
	 * Render auto-save notifications
	 *
	 * @since 3.37.14
	 */
	public function render_auto_save_notifications() {
		?>
		<div id="ennu-auto-save-notifications" class="ennu-auto-save-notifications">
			<div class="ennu-auto-save-notification ennu-auto-save-saving" id="ennu-auto-save-saving" style="display: none;">
				<div class="ennu-auto-save-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
						<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
						<polyline points="7,10 12,15 17,10"></polyline>
						<line x1="12" y1="15" x2="12" y2="3"></line>
					</svg>
				</div>
				<span class="ennu-auto-save-text"><?php esc_html_e( 'Saving...', 'ennulifeassessments' ); ?></span>
			</div>
			<div class="ennu-auto-save-notification ennu-auto-save-saved" id="ennu-auto-save-saved" style="display: none;">
				<div class="ennu-auto-save-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
						<polyline points="20,6 9,17 4,12"></polyline>
					</svg>
				</div>
				<span class="ennu-auto-save-text"><?php esc_html_e( 'Auto-saved', 'ennulifeassessments' ); ?></span>
			</div>
			<div class="ennu-auto-save-notification ennu-auto-save-error" id="ennu-auto-save-error" style="display: none;">
				<div class="ennu-auto-save-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="15" y1="9" x2="9" y2="15"></line>
						<line x1="9" y1="9" x2="15" y2="15"></line>
					</svg>
				</div>
				<span class="ennu-auto-save-text"><?php esc_html_e( 'Save failed', 'ennulifeassessments' ); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render auto-save restore dialog
	 *
	 * @since 3.37.14
	 * @param string $form_type Form type.
	 * @return string
	 */
	public function render_auto_save_restore_dialog( $form_type ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return '';
		}

		$versions = $this->get_auto_save_versions( $user_id, $form_type );
		if ( empty( $versions ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="ennu-auto-save-restore-dialog" data-form-type="<?php echo esc_attr( $form_type ); ?>">
			<div class="ennu-auto-save-restore-header">
				<h4><?php esc_html_e( 'Restore Auto-Saved Data', 'ennulifeassessments' ); ?></h4>
				<p><?php esc_html_e( 'Choose a version to restore:', 'ennulifeassessments' ); ?></p>
			</div>
			<div class="ennu-auto-save-restore-content">
				<div class="ennu-auto-save-versions">
					<?php foreach ( $versions as $version ) : ?>
					<div class="ennu-auto-save-version" data-version="<?php echo esc_attr( $version['version'] ); ?>">
						<div class="ennu-auto-save-version-info">
							<span class="ennu-auto-save-version-number"><?php echo esc_html( sprintf( __( 'Version %d', 'ennulifeassessments' ), $version['version'] ) ); ?></span>
							<span class="ennu-auto-save-version-time"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $version['timestamp'] ) ) ); ?></span>
						</div>
						<button class="ennu-auto-save-restore-btn" data-version="<?php echo esc_attr( $version['version'] ); ?>">
							<?php esc_html_e( 'Restore', 'ennulifeassessments' ); ?>
						</button>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="ennu-auto-save-restore-footer">
				<button class="ennu-auto-save-clear-btn" data-form-type="<?php echo esc_attr( $form_type ); ?>">
					<?php esc_html_e( 'Clear All', 'ennulifeassessments' ); ?>
				</button>
				<button class="ennu-auto-save-cancel-btn">
					<?php esc_html_e( 'Cancel', 'ennulifeassessments' ); ?>
				</button>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render auto-save trigger
	 *
	 * @since 3.37.14
	 * @param string $form_type Form type.
	 * @return string
	 */
	public function render_auto_save_trigger( $form_type ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return '';
		}

		$versions = $this->get_auto_save_versions( $user_id, $form_type );
		if ( empty( $versions ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="ennu-auto-save-trigger" data-form-type="<?php echo esc_attr( $form_type ); ?>">
			<button class="ennu-auto-save-trigger-btn" id="ennu-auto-save-trigger" aria-label="<?php esc_attr_e( 'Restore auto-saved data', 'ennulifeassessments' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
					<path d="M3 15v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4"></path>
					<polyline points="17,8 12,3 7,8"></polyline>
					<line x1="12" y1="3" x2="12" y2="15"></line>
				</svg>
				<?php esc_html_e( 'Restore', 'ennulifeassessments' ); ?>
				<span class="ennu-auto-save-count"><?php echo esc_html( count( $versions ) ); ?></span>
			</button>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get auto-save statistics
	 *
	 * @since 3.37.14
	 * @return array
	 */
	public function get_auto_save_stats() {
		$stats = array(
			'total_auto_saves' => 0,
			'most_saved_forms' => array(),
			'average_save_frequency' => 0,
		);

		// Get auto-save statistics
		global $wpdb;
		
		$auto_save_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				'_ennu_auto_save_%'
			)
		);
		$stats['total_auto_saves'] = (int) $auto_save_count;

		return $stats;
	}

	/**
	 * Clean up old auto-save data
	 *
	 * @since 3.37.14
	 */
	public function cleanup_old_auto_save_data() {
		global $wpdb;

		$retention_days = 30; // Default retention period
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		// Get all auto-save meta keys
		$auto_save_keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_key FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				'_ennu_auto_save_%'
			)
		);

		foreach ( $auto_save_keys as $meta_key ) {
			$form_type = str_replace( '_ennu_auto_save_', '', $meta_key );
			
			if ( isset( $this->auto_save_config[ $form_type ]['retention_days'] ) ) {
				$retention_days = $this->auto_save_config[ $form_type ]['retention_days'];
				$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );
			}

			// Get auto-save data for this form type
			$auto_save_data = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s",
					$meta_key
				)
			);

			if ( $auto_save_data ) {
				$data = maybe_unserialize( $auto_save_data );
				if ( is_array( $data ) ) {
					$cleaned_data = array();
					
					foreach ( $data as $version ) {
						if ( isset( $version['timestamp'] ) && $version['timestamp'] > $cutoff_date ) {
							$cleaned_data[] = $version;
						}
					}

					// Update with cleaned data
					if ( empty( $cleaned_data ) ) {
						$wpdb->delete(
							$wpdb->usermeta,
							array( 'meta_key' => $meta_key ),
							array( '%s' )
						);
					} else {
						$wpdb->update(
							$wpdb->usermeta,
							array( 'meta_value' => maybe_serialize( $cleaned_data ) ),
							array( 'meta_key' => $meta_key ),
							array( '%s' ),
							array( '%s' )
						);
					}
				}
			}
		}
	}

	/**
	 * Add custom auto-save configuration
	 *
	 * @since 3.37.14
	 * @param string $form_type Form type.
	 * @param array  $config Auto-save configuration.
	 */
	public function add_auto_save_config( $form_type, $config ) {
		$this->auto_save_config[ $form_type ] = $config;
	}

	/**
	 * Remove auto-save configuration
	 *
	 * @since 3.37.14
	 * @param string $form_type Form type.
	 */
	public function remove_auto_save_config( $form_type ) {
		if ( isset( $this->auto_save_config[ $form_type ] ) ) {
			unset( $this->auto_save_config[ $form_type ] );
		}
	}
}

// Initialize the auto-save system
if ( class_exists( 'ENNU_Auto_Save' ) ) {
	new ENNU_Auto_Save();
} 