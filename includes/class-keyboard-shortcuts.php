<?php
/**
 * ENNU Life Assessments - Keyboard Shortcuts System
 *
 * Provides power user keyboard navigation, quick actions,
 * and accessibility features for improved user efficiency.
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
 * ENNU Keyboard Shortcuts System
 *
 * Manages keyboard shortcuts, power user navigation,
 * and accessibility features for optimal user experience.
 *
 * @since 3.37.14
 */
class ENNU_Keyboard_Shortcuts {

	/**
	 * Keyboard shortcuts configuration
	 *
	 * @var array
	 */
	private $shortcuts = array();

	/**
	 * User shortcut preferences
	 *
	 * @var array
	 */
	private $user_preferences = array();

	/**
	 * Initialize the keyboard shortcuts system
	 *
	 * @since 3.37.14
	 */
	public function __construct() {
		$this->init_hooks();
		$this->setup_shortcuts();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 3.37.14
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_keyboard_shortcuts_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_keyboard_shortcuts_assets' ) );
		add_action( 'wp_ajax_ennu_save_shortcut_preferences', array( $this, 'save_shortcut_preferences' ) );
		add_action( 'wp_ajax_nopriv_ennu_save_shortcut_preferences', array( $this, 'save_shortcut_preferences' ) );
		add_action( 'wp_footer', array( $this, 'render_shortcuts_overlay' ) );
		add_action( 'admin_footer', array( $this, 'render_shortcuts_overlay' ) );
		add_action( 'wp_footer', array( $this, 'render_shortcuts_help' ) );
		add_action( 'admin_footer', array( $this, 'render_shortcuts_help' ) );
	}

	/**
	 * Setup keyboard shortcuts configuration
	 *
	 * @since 3.37.14
	 */
	private function setup_shortcuts() {
		$this->shortcuts = array(
			'navigation' => array(
				'dashboard' => array(
					'key' => 'g d',
					'description' => __( 'Go to Dashboard', 'ennulifeassessments' ),
					'action' => 'navigate_to_dashboard',
					'category' => 'navigation',
				),
				'assessments' => array(
					'key' => 'g a',
					'description' => __( 'Go to Assessments', 'ennulifeassessments' ),
					'action' => 'navigate_to_assessments',
					'category' => 'navigation',
				),
				'biomarkers' => array(
					'key' => 'g b',
					'description' => __( 'Go to Biomarkers', 'ennulifeassessments' ),
					'action' => 'navigate_to_biomarkers',
					'category' => 'navigation',
				),
				'progress' => array(
					'key' => 'g p',
					'description' => __( 'Go to Progress', 'ennulifeassessments' ),
					'action' => 'navigate_to_progress',
					'category' => 'navigation',
				),
				'help' => array(
					'key' => '?',
					'description' => __( 'Show Help', 'ennulifeassessments' ),
					'action' => 'show_help',
					'category' => 'navigation',
				),
			),
			'actions' => array(
				'new_assessment' => array(
					'key' => 'n',
					'description' => __( 'Start New Assessment', 'ennulifeassessments' ),
					'action' => 'start_new_assessment',
					'category' => 'actions',
				),
				'save' => array(
					'key' => 'ctrl+s',
					'description' => __( 'Save Current Form', 'ennulifeassessments' ),
					'action' => 'save_current_form',
					'category' => 'actions',
				),
				'search' => array(
					'key' => 'ctrl+k',
					'description' => __( 'Open Search', 'ennulifeassessments' ),
					'action' => 'open_search',
					'category' => 'actions',
				),
				'export' => array(
					'key' => 'ctrl+e',
					'description' => __( 'Export Data', 'ennulifeassessments' ),
					'action' => 'export_data',
					'category' => 'actions',
				),
			),
			'forms' => array(
				'next_field' => array(
					'key' => 'tab',
					'description' => __( 'Next Field', 'ennulifeassessments' ),
					'action' => 'next_field',
					'category' => 'forms',
				),
				'previous_field' => array(
					'key' => 'shift+tab',
					'description' => __( 'Previous Field', 'ennulifeassessments' ),
					'action' => 'previous_field',
					'category' => 'forms',
				),
				'submit_form' => array(
					'key' => 'ctrl+enter',
					'description' => __( 'Submit Form', 'ennulifeassessments' ),
					'action' => 'submit_form',
					'category' => 'forms',
				),
				'clear_form' => array(
					'key' => 'ctrl+shift+c',
					'description' => __( 'Clear Form', 'ennulifeassessments' ),
					'action' => 'clear_form',
					'category' => 'forms',
				),
			),
			'accessibility' => array(
				'increase_font' => array(
					'key' => 'ctrl+plus',
					'description' => __( 'Increase Font Size', 'ennulifeassessments' ),
					'action' => 'increase_font_size',
					'category' => 'accessibility',
				),
				'decrease_font' => array(
					'key' => 'ctrl+minus',
					'description' => __( 'Decrease Font Size', 'ennulifeassessments' ),
					'action' => 'decrease_font_size',
					'category' => 'accessibility',
				),
				'high_contrast' => array(
					'key' => 'ctrl+h',
					'description' => __( 'Toggle High Contrast', 'ennulifeassessments' ),
					'action' => 'toggle_high_contrast',
					'category' => 'accessibility',
				),
				'focus_mode' => array(
					'key' => 'ctrl+f',
					'description' => __( 'Toggle Focus Mode', 'ennulifeassessments' ),
					'action' => 'toggle_focus_mode',
					'category' => 'accessibility',
				),
			),
			'quick_actions' => array(
				'quick_assessment' => array(
					'key' => 'q',
					'description' => __( 'Quick Assessment', 'ennulifeassessments' ),
					'action' => 'start_quick_assessment',
					'category' => 'quick_actions',
				),
				'add_biomarker' => array(
					'key' => 'b',
					'description' => __( 'Add Biomarker', 'ennulifeassessments' ),
					'action' => 'add_biomarker',
					'category' => 'quick_actions',
				),
				'view_reports' => array(
					'key' => 'r',
					'description' => __( 'View Reports', 'ennulifeassessments' ),
					'action' => 'view_reports',
					'category' => 'quick_actions',
				),
				'settings' => array(
					'key' => 's',
					'description' => __( 'Open Settings', 'ennulifeassessments' ),
					'action' => 'open_settings',
					'category' => 'quick_actions',
				),
			),
		);
	}

	/**
	 * Enqueue keyboard shortcuts assets
	 *
	 * @since 3.37.14
	 */
	public function enqueue_keyboard_shortcuts_assets() {
		wp_enqueue_script(
			'ennu-keyboard-shortcuts',
			plugins_url( 'assets/js/keyboard-shortcuts.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			ENNU_LIFE_VERSION,
			true
		);

		wp_enqueue_style(
			'ennu-keyboard-shortcuts',
			plugins_url( 'assets/css/keyboard-shortcuts.css', dirname( __FILE__ ) ),
			array(),
			ENNU_LIFE_VERSION
		);

		// Localize script with shortcuts data
		wp_localize_script(
			'ennu-keyboard-shortcuts',
			'ennuKeyboardShortcuts',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'ennu_keyboard_shortcuts_nonce' ),
				'shortcuts'  => $this->shortcuts,
				'userPreferences' => $this->get_user_preferences(),
				'strings'    => array(
					'shortcuts' => __( 'Keyboard Shortcuts', 'ennulifeassessments' ),
					'help'      => __( 'Help', 'ennulifeassessments' ),
					'close'     => __( 'Close', 'ennulifeassessments' ),
					'customize' => __( 'Customize', 'ennulifeassessments' ),
					'reset'     => __( 'Reset to Defaults', 'ennulifeassessments' ),
				),
			)
		);
	}

	/**
	 * Save shortcut preferences via AJAX
	 *
	 * @since 3.37.14
	 */
	public function save_shortcut_preferences() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_keyboard_shortcuts_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( __( 'User not logged in', 'ennulifeassessments' ) );
		}

		$preferences = sanitize_text_field( wp_unslash( $_POST['preferences'] ) );
		$preferences_array = json_decode( stripslashes( $preferences ), true );

		if ( is_array( $preferences_array ) ) {
			update_user_meta( $user_id, '_ennu_shortcut_preferences', $preferences_array );
			wp_send_json_success( __( 'Shortcut preferences saved', 'ennulifeassessments' ) );
		} else {
			wp_send_json_error( __( 'Invalid preferences data', 'ennulifeassessments' ) );
		}
	}

	/**
	 * Render shortcuts overlay
	 *
	 * @since 3.37.14
	 */
	public function render_shortcuts_overlay() {
		?>
		<div id="ennu-shortcuts-overlay" class="ennu-shortcuts-overlay" style="display: none;">
			<div class="ennu-shortcuts-backdrop"></div>
			<div class="ennu-shortcuts-modal" id="ennu-shortcuts-modal">
				<div class="ennu-shortcuts-header">
					<h3 class="ennu-shortcuts-title"><?php esc_html_e( 'Keyboard Shortcuts', 'ennulifeassessments' ); ?></h3>
					<button class="ennu-shortcuts-close" id="ennu-shortcuts-close" aria-label="<?php esc_attr_e( 'Close shortcuts', 'ennulifeassessments' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>
				<div class="ennu-shortcuts-content">
					<div class="ennu-shortcuts-categories" id="ennu-shortcuts-categories">
						<!-- Categories will be populated by JavaScript -->
					</div>
					<div class="ennu-shortcuts-list" id="ennu-shortcuts-list">
						<!-- Shortcuts will be populated by JavaScript -->
					</div>
				</div>
				<div class="ennu-shortcuts-footer">
					<button class="ennu-shortcuts-btn ennu-shortcuts-btn-secondary" id="ennu-shortcuts-customize">
						<?php esc_html_e( 'Customize', 'ennulifeassessments' ); ?>
					</button>
					<button class="ennu-shortcuts-btn ennu-shortcuts-btn-primary" id="ennu-shortcuts-close-btn">
						<?php esc_html_e( 'Close', 'ennulifeassessments' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render shortcuts help
	 *
	 * @since 3.37.14
	 */
	public function render_shortcuts_help() {
		?>
		<div class="ennu-shortcuts-help" id="ennu-shortcuts-help" style="display: none;">
			<div class="ennu-shortcuts-help-content">
				<div class="ennu-shortcuts-help-header">
					<h4><?php esc_html_e( 'Keyboard Shortcuts', 'ennulifeassessments' ); ?></h4>
					<button class="ennu-shortcuts-help-close" id="ennu-shortcuts-help-close" aria-label="<?php esc_attr_e( 'Close help', 'ennulifeassessments' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>
				<div class="ennu-shortcuts-help-body">
					<div class="ennu-shortcuts-help-section">
						<h5><?php esc_html_e( 'Navigation', 'ennulifeassessments' ); ?></h5>
						<ul class="ennu-shortcuts-help-list">
							<li><kbd>g d</kbd> <?php esc_html_e( 'Go to Dashboard', 'ennulifeassessments' ); ?></li>
							<li><kbd>g a</kbd> <?php esc_html_e( 'Go to Assessments', 'ennulifeassessments' ); ?></li>
							<li><kbd>g b</kbd> <?php esc_html_e( 'Go to Biomarkers', 'ennulifeassessments' ); ?></li>
							<li><kbd>g p</kbd> <?php esc_html_e( 'Go to Progress', 'ennulifeassessments' ); ?></li>
						</ul>
					</div>
					<div class="ennu-shortcuts-help-section">
						<h5><?php esc_html_e( 'Quick Actions', 'ennulifeassessments' ); ?></h5>
						<ul class="ennu-shortcuts-help-list">
							<li><kbd>n</kbd> <?php esc_html_e( 'Start New Assessment', 'ennulifeassessments' ); ?></li>
							<li><kbd>q</kbd> <?php esc_html_e( 'Quick Assessment', 'ennulifeassessments' ); ?></li>
							<li><kbd>b</kbd> <?php esc_html_e( 'Add Biomarker', 'ennulifeassessments' ); ?></li>
							<li><kbd>r</kbd> <?php esc_html_e( 'View Reports', 'ennulifeassessments' ); ?></li>
						</ul>
					</div>
					<div class="ennu-shortcuts-help-section">
						<h5><?php esc_html_e( 'Form Actions', 'ennulifeassessments' ); ?></h5>
						<ul class="ennu-shortcuts-help-list">
							<li><kbd>Ctrl+S</kbd> <?php esc_html_e( 'Save Form', 'ennulifeassessments' ); ?></li>
							<li><kbd>Ctrl+Enter</kbd> <?php esc_html_e( 'Submit Form', 'ennulifeassessments' ); ?></li>
							<li><kbd>Tab</kbd> <?php esc_html_e( 'Next Field', 'ennulifeassessments' ); ?></li>
							<li><kbd>Shift+Tab</kbd> <?php esc_html_e( 'Previous Field', 'ennulifeassessments' ); ?></li>
						</ul>
					</div>
					<div class="ennu-shortcuts-help-section">
						<h5><?php esc_html_e( 'Accessibility', 'ennulifeassessments' ); ?></h5>
						<ul class="ennu-shortcuts-help-list">
							<li><kbd>Ctrl++</kbd> <?php esc_html_e( 'Increase Font Size', 'ennulifeassessments' ); ?></li>
							<li><kbd>Ctrl+-</kbd> <?php esc_html_e( 'Decrease Font Size', 'ennulifeassessments' ); ?></li>
							<li><kbd>Ctrl+H</kbd> <?php esc_html_e( 'Toggle High Contrast', 'ennulifeassessments' ); ?></li>
							<li><kbd>Ctrl+F</kbd> <?php esc_html_e( 'Toggle Focus Mode', 'ennulifeassessments' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render shortcuts trigger
	 *
	 * @since 3.37.14
	 * @return string
	 */
	public function render_shortcuts_trigger() {
		ob_start();
		?>
		<div class="ennu-shortcuts-trigger">
			<button class="ennu-shortcuts-trigger-btn" id="ennu-shortcuts-trigger" aria-label="<?php esc_attr_e( 'Show keyboard shortcuts', 'ennulifeassessments' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
					<rect x="2" y="4" width="20" height="16" rx="2" ry="2"></rect>
					<line x1="6" y1="8" x2="6" y2="8"></line>
					<line x1="10" y1="8" x2="10" y2="8"></line>
					<line x1="14" y1="8" x2="14" y2="8"></line>
					<line x1="18" y1="8" x2="18" y2="8"></line>
					<line x1="6" y1="12" x2="6" y2="12"></line>
					<line x1="10" y1="12" x2="10" y2="12"></line>
					<line x1="14" y1="12" x2="14" y2="12"></line>
					<line x1="18" y1="12" x2="18" y2="12"></line>
				</svg>
				<?php esc_html_e( 'Shortcuts', 'ennulifeassessments' ); ?>
			</button>
		</div>
		<?php
		return ob_get_clean();
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

		$preferences = get_user_meta( $user_id, '_ennu_shortcut_preferences', true );
		return is_array( $preferences ) ? $preferences : array();
	}

	/**
	 * Get shortcut by key
	 *
	 * @since 3.37.14
	 * @param string $key Shortcut key.
	 * @return array|false
	 */
	public function get_shortcut_by_key( $key ) {
		foreach ( $this->shortcuts as $category => $shortcuts ) {
			foreach ( $shortcuts as $shortcut_id => $shortcut ) {
				if ( $shortcut['key'] === $key ) {
					return $shortcut;
				}
			}
		}
		return false;
	}

	/**
	 * Get shortcuts by category
	 *
	 * @since 3.37.14
	 * @param string $category Category name.
	 * @return array
	 */
	public function get_shortcuts_by_category( $category ) {
		return isset( $this->shortcuts[ $category ] ) ? $this->shortcuts[ $category ] : array();
	}

	/**
	 * Add custom shortcut
	 *
	 * @since 3.37.14
	 * @param string $category Category.
	 * @param string $shortcut_id Shortcut ID.
	 * @param array  $shortcut Shortcut configuration.
	 */
	public function add_shortcut( $category, $shortcut_id, $shortcut ) {
		if ( ! isset( $this->shortcuts[ $category ] ) ) {
			$this->shortcuts[ $category ] = array();
		}

		$this->shortcuts[ $category ][ $shortcut_id ] = $shortcut;
	}

	/**
	 * Remove shortcut
	 *
	 * @since 3.37.14
	 * @param string $category Category.
	 * @param string $shortcut_id Shortcut ID.
	 */
	public function remove_shortcut( $category, $shortcut_id ) {
		if ( isset( $this->shortcuts[ $category ][ $shortcut_id ] ) ) {
			unset( $this->shortcuts[ $category ][ $shortcut_id ] );
		}
	}

	/**
	 * Get shortcuts statistics
	 *
	 * @since 3.37.14
	 * @return array
	 */
	public function get_shortcuts_stats() {
		$stats = array(
			'total_shortcuts' => 0,
			'most_used_shortcuts' => array(),
			'user_customizations' => 0,
		);

		// Count total shortcuts
		foreach ( $this->shortcuts as $category => $shortcuts ) {
			$stats['total_shortcuts'] += count( $shortcuts );
		}

		// Get user customizations
		global $wpdb;
		
		$customizations = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s",
				'_ennu_shortcut_preferences'
			)
		);
		$stats['user_customizations'] = (int) $customizations;

		return $stats;
	}

	/**
	 * Track shortcut usage
	 *
	 * @since 3.37.14
	 * @param string $shortcut_id Shortcut ID.
	 */
	public function track_shortcut_usage( $shortcut_id ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$usage = get_user_meta( $user_id, '_ennu_shortcut_usage', true );
		if ( ! is_array( $usage ) ) {
			$usage = array();
		}

		$usage[] = array(
			'shortcut_id' => $shortcut_id,
			'timestamp' => current_time( 'mysql' ),
		);

		update_user_meta( $user_id, '_ennu_shortcut_usage', $usage );
	}

	/**
	 * Check if shortcuts are enabled for user
	 *
	 * @since 3.37.14
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public function are_shortcuts_enabled( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$enabled = get_user_meta( $user_id, '_ennu_shortcuts_enabled', true );
		return $enabled !== 'disabled';
	}

	/**
	 * Enable shortcuts for user
	 *
	 * @since 3.37.14
	 * @param int $user_id User ID.
	 */
	public function enable_shortcuts( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( $user_id ) {
			update_user_meta( $user_id, '_ennu_shortcuts_enabled', 'enabled' );
		}
	}

	/**
	 * Disable shortcuts for user
	 *
	 * @since 3.37.14
	 * @param int $user_id User ID.
	 */
	public function disable_shortcuts( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( $user_id ) {
			update_user_meta( $user_id, '_ennu_shortcuts_enabled', 'disabled' );
		}
	}
}

// Initialize the keyboard shortcuts system
if ( class_exists( 'ENNU_Keyboard_Shortcuts' ) ) {
	new ENNU_Keyboard_Shortcuts();
} 