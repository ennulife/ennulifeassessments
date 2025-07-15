<?php
/**
 * ENNU Life Enhanced Admin Class - Definitive Rebuild
 *
 * This file has been programmatically rebuilt from scratch to resolve all
 * fatal errors and restore all necessary functionality. It is the single,
 * correct source of truth for the admin class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Enhanced_Admin {

	public function __construct() {
		// Hooks are managed from the main plugin file.
	}

	/**
	 * The single, correct function to build the entire admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'ENNU Life', 'ennulifeassessments' ),
			__( 'ENNU Life', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life',
			array( $this, 'render_admin_dashboard_page' ),
			'dashicons-heart',
			30
		);

		add_submenu_page(
			'ennu-life',
			__( 'Dashboard', 'ennulifeassessments' ),
			__( 'Dashboard', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life',
			array( $this, 'render_admin_dashboard_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'Analytics', 'ennulifeassessments' ),
			__( 'Analytics', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life-analytics',
			array( $this, 'render_analytics_dashboard_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'Assessments', 'ennulifeassessments' ),
			__( 'Assessments', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life-assessments',
			array( $this, 'assessments_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'Settings', 'ennulifeassessments' ),
			__( 'Settings', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life-settings',
			array( $this, 'settings_page' )
		);
	}

	public function render_admin_dashboard_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'ENNU Life Dashboard', 'ennulifeassessments' ) . '</h1>';
		$stats = $this->get_assessment_statistics();
		echo '<div class="ennu-dashboard-stats" style="display:flex;gap:20px;margin:20px 0;">';
		echo '<div class="ennu-stat-card" style="flex:1;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);"><h3>' . esc_html__( 'Total Users with Assessments', 'ennulifeassessments' ) . '</h3><span class="ennu-stat-number" style="font-size:2em;font-weight:bold;">' . esc_html( $stats['active_users'] ) . '</span></div>';
		echo '<div class="ennu-stat-card" style="flex:1;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);"><h3>' . esc_html__( 'Submissions This Month', 'ennulifeassessments' ) . '</h3><span class="ennu-stat-number" style="font-size:2em;font-weight:bold;">' . esc_html( $stats['monthly_assessments'] ) . '</span></div>';
		echo '</div>';
		echo '<h2>' . esc_html__( 'Recent Assessments', 'ennulifeassessments' ) . '</h2>';
		$this->display_recent_assessments_table();
		echo '</div>';
	}

	public function render_analytics_dashboard_page() {
		if ( ! is_user_logged_in() ) {
			return; }
		$user_id               = get_current_user_id();
		$current_user          = wp_get_current_user();
		$ennu_life_score       = get_user_meta( $user_id, 'ennu_life_score', true );
		$average_pillar_scores = ENNU_Assessment_Scoring::calculate_average_pillar_scores( $user_id );
		$dob_combined          = get_user_meta( $user_id, 'ennu_global_user_dob_combined', true );
		$age                   = $dob_combined ? ( new DateTime() )->diff( new DateTime( $dob_combined ) )->y : 'N/A';
		$gender                = ucfirst( get_user_meta( $user_id, 'ennu_global_gender', true ) );
		include ENNU_LIFE_PLUGIN_PATH . 'templates/admin/analytics-dashboard.php';
	}

	public function assessments_page() {
		echo '<div class="wrap"><h1>' . __( 'ENNU Life Assessments', 'ennulifeassessments' ) . '</h1>';
		$this->display_all_assessments_table();
		echo '</div>';
	}

	public function settings_page() {
		echo '<div class="wrap"><h1>' . __( 'ENNU Life Settings', 'ennulifeassessments' ) . '</h1>';

		// Add some inline CSS for better layout
		echo '<style>.ennu-admin-permalink { margin-left: 10px; } .ennu-admin-permalink code { background: #f0f0f1; padding: 3px 6px; border-radius: 3px; margin-left: 5px; }</style>';

		$message = '';
		if ( isset( $_POST['submit'] ) && isset( $_POST['ennu_settings_nonce'] ) && wp_verify_nonce( $_POST['ennu_settings_nonce'], 'ennu_settings_update' ) ) {
			$this->save_settings();
			$message = __( 'Settings saved successfully!', 'ennulifeassessments' );
		}
		if ( isset( $_POST['ennu_setup_pages_submit'] ) && isset( $_POST['ennu_setup_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_setup_pages_nonce'], 'ennu_setup_pages' ) ) {
			$this->setup_pages();
			// Force a re-fetch of settings after page creation
			$message = __( 'Assessment pages have been created and assigned successfully!', 'ennulifeassessments' );
		}
		if ( isset( $_POST['ennu_delete_pages_submit'] ) && isset( $_POST['ennu_delete_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_delete_pages_nonce'], 'ennu_delete_pages' ) ) {
			$this->delete_pages();
			$message = __( 'Assessment pages have been deleted successfully!', 'ennulifeassessments' );
		}

		if ( $message ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
		}

		$settings        = $this->get_plugin_settings();
		$assessment_keys = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		$pages           = get_pages();
		$page_options    = array();
		foreach ( $pages as $page ) {
			$page_options[ $page->ID ] = $page->post_title;
		}

		echo '<form method="post" action=""><table class="form-table" role="presentation">';
		wp_nonce_field( 'ennu_settings_update', 'ennu_settings_nonce' );

		echo '<h2>' . esc_html__( 'Core Pages', 'ennulifeassessments' ) . '</h2>';
		$this->render_page_dropdown( 'user-dashboard', 'User Dashboard Page', $settings['page_mappings'], $page_options );
		$this->render_page_dropdown( 'assessment-results', 'Generic Results Page', $settings['page_mappings'], $page_options );

		echo '<h2>' . esc_html__( 'Assessment Form Pages', 'ennulifeassessments' ) . '</h2>';
		foreach ( $assessment_keys as $key ) {
			$slug  = str_replace( '_', '-', $key );
			$label = ucwords( str_replace( '_', ' ', $key ) );
			$this->render_page_dropdown( $slug, $label, $settings['page_mappings'], $page_options );
		}

		echo '<h2>' . esc_html__( 'Assessment Results Pages', 'ennulifeassessments' ) . '</h2>';
		foreach ( $assessment_keys as $key ) {
			if ( 'welcome_assessment' === $key ) {
				continue;
			}
			$slug  = str_replace( '_assessment', '-results', $key );
			$label = ucwords( str_replace( '_', ' ', $key ) ) . ' Results';
			$this->render_page_dropdown( $slug, $label, $settings['page_mappings'], $page_options );
		}

		echo '<h2>' . esc_html__( 'Assessment Details Pages (Dossier)', 'ennulifeassessments' ) . '</h2>';
		foreach ( $assessment_keys as $key ) {
			if ( 'welcome_assessment' === $key ) {
				continue;
			}
			$slug  = str_replace( '_assessment', '-assessment-details', $key );
			$label = ucwords( str_replace( '_', ' ', $key ) ) . ' Details';
			$this->render_page_dropdown( $slug, $label, $settings['page_mappings'], $page_options );
		}

		submit_button();
		echo '</table></form>';

		echo '<h2>' . esc_html__( 'Automated Page Setup', 'ennulifeassessments' ) . '</h2>';
		echo '<p>' . esc_html__( 'Use this button to automatically create any missing assessment pages. The created pages will be automatically selected in the dropdowns above.', 'ennulifeassessments' ) . '</p>';
		echo '<form method="post" action="" style="margin-bottom: 20px;">';
		wp_nonce_field( 'ennu_setup_pages', 'ennu_setup_pages_nonce' );
		submit_button( __( 'Create Missing Assessment Pages', 'ennulifeassessments' ), 'primary', 'ennu_setup_pages_submit' );
		echo '</form>';

		if ( ! empty( $settings['page_mappings'] ) ) {
			echo '<h2>' . esc_html__( 'Automated Page Deletion', 'ennulifeassessments' ) . '</h2>';
			echo '<p>' . esc_html__( 'Use this button to delete all pages currently mapped in the settings above. This cannot be undone.', 'ennulifeassessments' ) . '</p>';
			echo '<form method="post" action="" onsubmit="return confirm(\'Are you sure you want to delete all mapped pages? This cannot be undone.\');">';
			wp_nonce_field( 'ennu_delete_pages', 'ennu_delete_pages_nonce' );
			submit_button( __( 'Delete Mapped Assessment Pages', 'ennulifeassessments' ), 'delete', 'ennu_delete_pages_submit' );
			echo '</form>';
		}
	}

	private function render_page_dropdown( $slug, $label, $current_mappings, $page_options ) {
		$current_page_id = $current_mappings[ $slug ] ?? 0;
		$page_is_valid = $current_page_id && get_post( $current_page_id ) && get_post_status( $current_page_id ) === 'publish';
		?>
		<tr valign="top">
			<th scope="row"><label for="page_for_<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<select name="ennu_pages[<?php echo esc_attr( $slug ); ?>]" id="page_for_<?php echo esc_attr( $slug ); ?>" style="width: 25em;">
					<option value="">-- Select a Page --</option>
					<?php foreach ( $page_options as $page_id => $page_title ) : ?>
						<option value="<?php echo esc_attr( $page_id ); ?>" <?php selected( $current_page_id, $page_id ); ?>>
							<?php echo esc_html( $page_title ); ?> (ID: <?php echo esc_html( $page_id ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
				<?php if ( $page_is_valid ) : ?>
					<span class="ennu-admin-permalink">
						<a href="<?php echo esc_url( get_permalink( $current_page_id ) ); ?>" target="_blank" class="button button-secondary button-small">View Page</a>
						<code><?php echo esc_html( wp_make_link_relative( get_permalink( $current_page_id ) ) ); ?></code>
					</span>
				<?php elseif ( $current_page_id > 0 ) : ?>
					<span class="ennu-admin-warning" style="color: #d63638; font-weight: bold; margin-left: 10px;">
						<?php esc_html_e( 'Page missing or not published!', 'ennulifeassessments' ); ?>
					</span>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}


	public function show_user_assessment_fields( $user ) {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return; }

		// --- Render Health Summary Component ---
		$ennu_life_score       = get_user_meta( $user->ID, 'ennu_life_score', true );
		$average_pillar_scores = ENNU_Assessment_Scoring::calculate_average_pillar_scores( $user->ID );

		include ENNU_LIFE_PLUGIN_PATH . 'templates/admin/user-health-summary.php';
		// --- END Health Summary Component ---

		wp_nonce_field( 'ennu_user_profile_update_' . $user->ID, 'ennu_assessment_nonce' );

		$assessments = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );

		echo '<h2>' . esc_html__( 'User Assessment Data', 'ennulifeassessments' ) . '</h2><div class="ennu-admin-tabs">';
		echo '<nav class="ennu-admin-tab-nav"><ul>';
		echo '<li><a href="#tab-global-metrics" class="ennu-admin-tab-active">' . esc_html__( 'Global & Health Metrics', 'ennulifeassessments' ) . '</a></li>';
		foreach ( $assessments as $key ) {
			if ( 'welcome_assessment' === $key ) {
				continue;
			}
			$label = ucwords( str_replace( array( '_', 'assessment' ), ' ', $key ) );
			echo '<li><a href="#tab-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</a></li>';
		}
		echo '</ul></nav>';

		// Tab for Global Metrics
		echo '<div id="tab-global-metrics" class="ennu-admin-tab-content ennu-admin-tab-active">';
		$this->display_global_fields_section( $user->ID );
		echo '</div>';

		// Tabs for each assessment
		foreach ( $assessments as $assessment_key ) {
			if ( 'welcome_assessment' === $assessment_key ) {
				continue;
			}
			echo '<div id="tab-' . esc_attr( $assessment_key ) . '" class="ennu-admin-tab-content">';
			echo '<table class="form-table">';
			$this->display_assessment_fields_editable( $user->ID, $assessment_key );
			echo '</table>';
			// v57.1.0: Add per-assessment clear button
			echo '<p><button type="button" class="button button-secondary ennu-clear-single-assessment-data" data-assessment-key="' . esc_attr( $assessment_key ) . '" data-user-id="' . esc_attr( $user->ID ) . '">' . esc_html__( 'Clear Data for This Assessment', 'ennulifeassessments' ) . '</button></p>';
			echo '</div>';
		}
		echo '</div>'; // close ennu-admin-tabs

		// v57.1.0: Administrative Actions Section
		echo '<div class="ennu-admin-actions-section">';
		echo '<h3>' . esc_html__( 'Administrative Actions', 'ennulifeassessments' ) . '</h3>';
		echo '<p>' . esc_html__( 'Use these actions to manage this user\'s assessment data. These actions are immediate and cannot be undone.', 'ennulifeassessments' ) . '</p>';
		echo '<div class="ennu-admin-action-buttons">';
		echo '<button type="button" id="ennu-recalculate-scores" class="button button-primary" data-user-id="' . esc_attr( $user->ID ) . '">' . esc_html__( 'Recalculate All Scores', 'ennulifeassessments' ) . '</button>';
		echo '<button type="button" id="ennu-clear-all-data" class="button button-delete" data-user-id="' . esc_attr( $user->ID ) . '">' . esc_html__( 'Clear All Assessment Data', 'ennulifeassessments' ) . '</button>';
		echo '<span class="spinner"></span>';
		echo '</div></div>';
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'profile.php' !== $hook && 'user-edit.php' !== $hook ) {
						return;
		}
		wp_enqueue_style( 'ennu-admin-styles', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-scores-enhanced.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_script( 'ennu-admin-scripts', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
		wp_localize_script( 'ennu-admin-scripts', 'ennuAdmin', array( 'nonce' => wp_create_nonce( 'ennu_admin_nonce' ) ) );
	}

	// --- v57.1.0: AJAX Handlers for Admin Actions ---

	public function handle_recalculate_all_scores() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		// Recalculate ENNU LIFE SCORE which depends on individual scores
		$new_score = ENNU_Assessment_Scoring::calculate_ennu_life_score( $user_id, true ); // Pass true to force recalc

		// Optionally, recalculate BMI if height/weight exists
		$height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		if ( ! empty( $height_weight['ft'] ) && ! empty( $height_weight['lbs'] ) ) {
			$height_in_total = ( intval( $height_weight['ft'] ) * 12 ) + intval( $height_weight['in'] );
			$weight_lbs      = intval( $height_weight['lbs'] );
			if ( $height_in_total > 0 && $weight_lbs > 0 ) {
				$bmi = ( $weight_lbs / ( $height_in_total * $height_in_total ) ) * 703;
				update_user_meta( $user_id, 'ennu_calculated_bmi', round( $bmi, 1 ) );
			}
		}

		wp_send_json_success( array( 'message' => 'All scores recalculated successfully.' ) );
	}

	public function handle_clear_all_assessment_data() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		global $wpdb;
		$wpdb->delete( $wpdb->usermeta, array( 'user_id' => $user_id, 'meta_key' => 'ennu_%' ), array( '%d', '%s' ) );
		// A more direct, powerful approach requires a LIKE comparison
		$meta_keys_to_delete = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s", $user_id, 'ennu_%' ) );
		foreach ( $meta_keys_to_delete as $meta_key ) {
			delete_user_meta( $user_id, $meta_key );
		}

		wp_send_json_success( array( 'message' => 'All assessment data cleared.' ) );
	}

	public function handle_clear_single_assessment_data() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$assessment_key = isset( $_POST['assessment_key'] ) ? sanitize_key( $_POST['assessment_key'] ) : '';

		if ( ! $user_id || empty( $assessment_key ) ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID or assessment key.' ), 400 );
		}

		global $wpdb;
		$meta_key_pattern = 'ennu_' . $assessment_key . '_%';
		$meta_keys_to_delete = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s", $user_id, $meta_key_pattern ) );

		foreach ( $meta_keys_to_delete as $meta_key ) {
			delete_user_meta( $user_id, $meta_key );
		}

		// After clearing, we must recalculate the master ENNU LIFE SCORE
		ENNU_Assessment_Scoring::calculate_ennu_life_score( $user_id, true );

		wp_send_json_success( array( 'message' => 'Assessment data cleared.' ) );
	}


	public function save_user_assessment_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['ennu_assessment_nonce'] ) || ! wp_verify_nonce( $_POST['ennu_assessment_nonce'], 'ennu_user_profile_update_' . $user_id ) ) {
			return;
		}

		// --- Save Global Fields ---
		$global_keys = array( 'ennu_global_health_goals', 'ennu_global_height_weight' );
		foreach ( $global_keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value_to_save = is_array( $_POST[ $key ] ) ? array_map( 'sanitize_text_field', $_POST[ $key ] ) : sanitize_text_field( $_POST[ $key ] );
				update_user_meta( $user_id, $key, $value_to_save );
			} elseif ( $key === 'ennu_global_health_goals' ) { // If no checkboxes are checked, save an empty array.
				update_user_meta( $user_id, $key, array() );
			}
		}


		$assessments = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		foreach ( $assessments as $assessment_type ) {
			$all_questions = $this->get_direct_assessment_questions( $assessment_type );
			
			// This is the definitive fix, aligning the save logic with the display logic.
			$questions = isset( $all_questions['questions'] ) ? $all_questions['questions'] : $all_questions;

			foreach ( $questions as $question_id => $q_data ) {
				if ( ! is_array( $q_data ) || isset( $q_data['global_key'] ) ) {
					continue;
				}
				$meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
				if ( isset( $_POST[ $meta_key ] ) ) {
					$value_to_save = is_array( $_POST[ $meta_key ] ) ? array_map( 'sanitize_text_field', $_POST[ $meta_key ] ) : sanitize_text_field( $_POST[ $meta_key ] );
					update_user_meta( $user_id, $meta_key, $value_to_save );
				} elseif ( isset($q_data['type']) && $q_data['type'] === 'multiselect' ) {
					update_user_meta( $user_id, $meta_key, array() );
				}
			}
		}
	}

	// --- PRIVATE HELPER METHODS ---

	private function get_assessment_statistics() {
		global $wpdb;
		$stats            = array(
			'total_assessments'   => 0,
			'monthly_assessments' => 0,
			'active_users'        => 0,
		);
		$assessment_types = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		$meta_keys        = array();
		foreach ( $assessment_types as $type ) {
			if ( 'welcome_assessment' === $type ) {
				continue;
			}
			$meta_keys[] = 'ennu_' . $type . '_calculated_score';
		}
		if ( ! empty( $meta_keys ) ) {
			$placeholders          = implode( ', ', array_fill( 0, count( $meta_keys ), '%s' ) );
			$query                 = $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key IN ($placeholders) AND meta_value != ''", $meta_keys );
			$stats['active_users'] = (int) $wpdb->get_var( $query );
		}
		$stats['monthly_assessments'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ennu_%_calculated_score' AND meta_value != '' AND CAST(meta_value AS SIGNED) > 0 AND user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_registered >= DATE_SUB(NOW(), INTERVAL 1 MONTH))" );
		return $stats;
	}

	private function display_recent_assessments_table() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT u.ID, u.user_login, u.user_email, um.meta_value as score, REPLACE(REPLACE(um.meta_key, 'ennu_', ''), '_calculated_score', '') as assessment_type FROM {$wpdb->usermeta} um JOIN {$wpdb->users} u ON um.user_id = u.ID WHERE um.meta_key LIKE 'ennu_%_calculated_score' AND um.meta_value != '' ORDER BY um.umeta_id DESC LIMIT 10" );
		if ( empty( $results ) ) {
			echo '<p>' . esc_html__( 'No recent assessment submissions found.', 'ennulifeassessments' ) . '</p>';
			return; }
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>User</th><th>Email</th><th>Assessment Type</th><th>Score</th></tr></thead><tbody>';
		foreach ( $results as $result ) {
			echo '<tr>';
			echo '<td><a href="' . esc_url( get_edit_user_link( $result->ID ) ) . '">' . esc_html( $result->user_login ) . '</a></td>';
			echo '<td>' . esc_html( $result->user_email ) . '</td>';
			echo '<td>' . esc_html( ucwords( str_replace( '_', ' ', $result->assessment_type ) ) ) . '</td>';
			echo '<td>' . esc_html( number_format( floatval( $result->score ), 1 ) ) . '/10</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	private function get_assessment_count( $assessment_type ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value != ''", 'ennu_' . $assessment_type . '_calculated_score' ) );
	}

	private function display_all_assessments_table() {
		global $wpdb;
		$user_ids = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ennu_%_calculated_score' ORDER BY user_id DESC LIMIT 50" );
		if ( empty( $user_ids ) ) {
			echo '<p>' . __( 'No assessment data found.', 'ennulifeassessments' ) . '</p>';
			return; }
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>User</th><th>Email</th><th>Assessments Completed</th><th>Date Registered</th></tr></thead><tbody>';
		foreach ( $user_ids as $user_id ) {
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				continue;
			}
			$assessments     = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s", $user_id, '%_calculated_score' ) );
			$assessment_list = array();
			if ( ! empty( $assessments ) ) {
				foreach ( $assessments as $assessment ) {
					$type              = str_replace( array( 'ennu_', '_calculated_score' ), '', $assessment->meta_key );
					$label             = ucwords( str_replace( '_', ' ', $type ) );
					$assessment_list[] = esc_html( $label . ' (' . number_format( floatval( $assessment->meta_value ), 1 ) . '/10)' );
				}
			}
			echo '<tr><td><a href="' . esc_url( get_edit_user_link( $user_id ) ) . '">' . esc_html( $user->user_login ) . '</a></td><td>' . esc_html( $user->user_email ) . '</td><td>' . wp_kses_post( implode( '<br>', $assessment_list ) ) . '</td><td>' . esc_html( date( 'M j, Y', strtotime( $user->user_registered ) ) ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	private function get_plugin_settings() {
		$main_settings = get_option(
			'ennu_plugin_settings',
			array(
				'admin_email'         => get_option( 'admin_email' ),
				'email_notifications' => 1,
			)
		);

		// Manually fetch and merge the page mappings
		$main_settings['page_mappings'] = get_option( 'ennu_created_pages', array() );

		return $main_settings;
	}

	private function save_settings() {
		$settings['admin_email'] = isset( $_POST['admin_email'] ) ? sanitize_email( $_POST['admin_email'] ) : '';
		update_option( 'ennu_plugin_settings', $settings );

		if ( isset( $_POST['ennu_pages'] ) && is_array( $_POST['ennu_pages'] ) ) {
			$page_mappings = array_map( 'intval', $_POST['ennu_pages'] );
			update_option( 'ennu_created_pages', $page_mappings );
		}
	}

	private function display_system_status() {
		echo '<table class="form-table"><tbody>';
		echo '<tr><th>' . esc_html__( 'WordPress Version', 'ennulifeassessments' ) . '</th><td>' . esc_html( get_bloginfo( 'version' ) ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Plugin Version', 'ennulifeassessments' ) . '</th><td>' . esc_html( ENNU_LIFE_VERSION ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'PHP Version', 'ennulifeassessments' ) . '</th><td>' . esc_html( PHP_VERSION ) . '</td></tr>';
		echo '</tbody></table>';
	}

	private function display_global_fields_section( $user_id ) {
		echo '<table class="form-table">';

		// Define all global fields, including those that will be editable.
		$global_fields = array(
			'first_name'                    => array( 'label' => 'First Name' ),
			'last_name'                     => array( 'label' => 'Last Name' ),
			'user_email'                    => array( 'label' => 'Email' ),
			'ennu_global_gender'            => array( 'label' => 'Gender' ),
			'ennu_global_user_dob_combined' => array( 'label' => 'DOB' ),
			'ennu_global_health_goals'      => array(
				'label'   => 'Health Goals',
				'type'    => 'multiselect',
				'options' => $this->get_health_goal_options(),
			),
			'ennu_global_height_weight'     => array(
				'label' => 'Height & Weight',
				'type'  => 'height_weight',
			),
			'ennu_calculated_bmi'           => array( 'label' => 'Calculated BMI' ),
			'ennu_life_score_history'       => array( 'label' => 'ENNU LIFE SCORE History' ),
			'ennu_bmi_history'              => array( 'label' => 'BMI History' ),
		);

		foreach ( $global_fields as $key => $data ) {
			$label         = $data['label'];
			$current_value = ( in_array( $key, array( 'first_name', 'last_name', 'user_email' ), true ) ) ? get_the_author_meta( $key, $user_id ) : get_user_meta( $user_id, $key, true );

			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
			$this->render_global_field_for_admin( $key, $data, $current_value );
			echo '</td></tr>';
		}
		echo '</table>';
	}

	private function render_global_field_for_admin( $key, $data, $current_value ) {
		$type = $data['type'] ?? 'static';

		switch ( $type ) {
			case 'multiselect':
				$options        = $data['options'] ?? array();
				$current_values = is_array( $current_value ) ? $current_value : array();
				echo '<div class="ennu-admin-checkbox-group">';
				foreach ( $options as $value => $label ) {
					echo '<label><input type="checkbox" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $value ) . '" ' . checked( in_array( $value, $current_values, true ), true, false ) . '> ' . esc_html( $label ) . '</label>';
				}
				echo '</div>';
				break;

			case 'height_weight':
				$height_ft = $current_value['ft'] ?? '';
				$height_in = $current_value['in'] ?? '';
				$weight_lbs = $current_value['lbs'] ?? '';
				echo '<div class="ennu-admin-hw-group">';
				echo '<span>Height: <input type="number" name="' . esc_attr( $key ) . '[ft]" value="' . esc_attr( $height_ft ) . '" class="small-text"> ft </span>';
				echo '<span><input type="number" name="' . esc_attr( $key ) . '[in]" value="' . esc_attr( $height_in ) . '" class="small-text"> in</span>';
				echo '<span style="margin-left: 20px;">Weight: <input type="number" name="' . esc_attr( $key ) . '[lbs]" value="' . esc_attr( $weight_lbs ) . '" class="small-text"> lbs</span>';
				echo '</div>';
				break;

			default:
				if ( $key === 'ennu_life_score_history' || $key === 'ennu_bmi_history' ) {
					if ( ! empty( $current_value ) && is_array( $current_value ) ) {
						echo '<ul style="margin:0; padding:0; list-style:none;">';
						foreach ( array_reverse( $current_value ) as $entry ) {
							$value_key = ($key === 'ennu_bmi_history') ? 'bmi' : 'score';
							if ( isset( $entry['date'], $entry[$value_key] ) ) {
								echo '<li><strong>' . esc_html( number_format( (float) $entry[$value_key], 1 ) ) . '</strong> on ' . esc_html( date( 'M j, Y @ g:i a', strtotime( $entry['date'] ) ) ) . '</li>';
							}
						}
						echo '</ul>';
					}
				} else {
					echo esc_html( $current_value );
				}
				break;
		}
	}

	private function get_health_goal_options() {
		$definitions = ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions();
		// v57.0.9: Add BMI history to the global fields list
		if ( ! isset( $global_fields['ennu_bmi_history'] ) ) {
			$global_fields['ennu_bmi_history'] = array( 'label' => 'BMI History' );
		}
		return $definitions['welcome_assessment']['welcome_q3']['options'] ?? array();
	}


	private function display_assessment_fields_editable( $user_id, $assessment_type ) {
		$all_questions = $this->get_direct_assessment_questions( $assessment_type );
		
		// This is the definitive fix. The new Health Optimization assessment has its questions
		// nested under a 'questions' key. We must check for this structure.
		$questions = isset( $all_questions['questions'] ) ? $all_questions['questions'] : $all_questions;

		if ( empty( $questions ) ) {
			return;
		}
		foreach ( $questions as $question_id => $q_data ) {
			// A key without an array value is metadata (e.g. 'title'), not a question. Skip it.
			if ( ! is_array( $q_data ) ) {
				continue;
			}
			if ( isset( $q_data['global_key'] ) ) {
				continue;
			}
			$meta_key      = 'ennu_' . $assessment_type . '_' . $question_id;
			$current_value = get_user_meta( $user_id, $meta_key, true );
			echo '<tr><th><label for="' . esc_attr( $meta_key ) . '">' . esc_html( $q_data['title'] ) . '</label></th><td>';
			$this->render_field_for_admin( $meta_key, $q_data['type'] ?? 'text', $q_data['options'] ?? array(), $current_value, $assessment_type, $question_id );
			echo '</td></tr>';
		}

		// --- System Data Section ---
		echo '<tr><td colspan="2"><hr><h4>' . esc_html__( 'System Data', 'ennulifeassessments' ) . '</h4></td></tr>';

		$system_fields = array(
			'ennu_' . $assessment_type . '_score_calculated_at' => 'Timestamp',
			'ennu_' . $assessment_type . '_score_interpretation' => 'Interpretation',
			'ennu_' . $assessment_type . '_pillar_scores'        => 'Pillar Scores',
		);

		foreach ( $system_fields as $key => $label ) {
			$value = get_user_meta( $user_id, $key, true );
			if ( empty( $value ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$value_str = '<ul style="margin:0; padding:0; list-style:none;">';
				foreach ( $value as $k => $v ) {
					$value_str .= '<li><strong>' . esc_html( ucwords( str_replace( '_', ' ', $k ) ) ) . ':</strong> ' . esc_html( is_array($v) ? json_encode($v) : $v ) . '</li>';
				}
				$value_str .= '</ul>';
				$value = $value_str;
			} elseif ( strpos( $key, '_score_calculated_at' ) !== false ) {
				$value = date( 'M j, Y @ g:i a', strtotime( $value ) );
			}
			echo '<tr><th><label>' . esc_html( $label ) . '</label></th><td>' . wp_kses_post( $value ) . '</td></tr>';
		}
	}

	private function get_direct_assessment_questions( $assessment_type ) {
		return ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_assessment_questions( $assessment_type );
	}

	private function render_field_for_admin( $meta_key, $type, $options, $current_value, $assessment_type, $question_id ) {
		switch ( $type ) {
			case 'multiselect':
				$this->render_checkbox_field( $meta_key, $current_value, $options, $assessment_type, $question_id );
				break;
			case 'radio':
				$this->render_radio_field( $meta_key, $current_value, $options, $assessment_type, $question_id );
				break;
			default:
				echo '<input type="text" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $current_value ) . '" class="regular-text" />';
				break;
		}
	}

	private function render_radio_field( $meta_key, $current_value, $options, $assessment_type, $question_id ) {
		// This is the definitive fix. If a field was once a multiselect, the old data
		// might be an array. We must gracefully handle this to prevent fatal errors.
		if ( is_array( $current_value ) ) {
			$current_value = reset( $current_value );
		}

		foreach ( $options as $value => $label ) {
			$score         = ENNU_Assessment_Scoring::get_answer_score( $assessment_type, $question_id, $value );
			$score_display = null !== $score ? ' (' . esc_html( $score ) . ' pts)' : '';
			echo '<label><input type="radio" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '" ' . checked( $current_value, $value, false ) . '> ' . esc_html( $label ) . wp_kses_post( $score_display ) . '</label><br>';
		}
	}

	private function render_checkbox_field( $meta_key, $current_values, $options, $assessment_type, $question_id ) {
		$current_values = is_array( $current_values ) ? $current_values : array();
		foreach ( $options as $value => $label ) {
			$score         = ENNU_Assessment_Scoring::get_answer_score( $assessment_type, $question_id, $value );
			$score_display = null !== $score ? ' (' . esc_html( $score ) . ' pts)' : '';
			echo '<label><input type="checkbox" name="' . esc_attr( $meta_key ) . '[]" value="' . esc_attr( $value ) . '" ' . checked( in_array( $value, $current_values, true ), true, false ) . '> ' . esc_html( $label ) . wp_kses_post( $score_display ) . '</label><br>';
		}
	}

	private function setup_pages() {
		$all_definitions = ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions();
		$assessment_keys = array_keys( $all_definitions );
		
		$pages_to_create = array();

		// Core Pages
		$pages_to_create['user-dashboard'] = array( 'title' => 'Dashboard', 'content' => '[ennu-user-dashboard]' );
		$pages_to_create['assessment-results'] = array( 'title' => 'Assessment Results', 'content' => '[ennu-assessment-results]' );

		// Dynamically build page list from definitions
		foreach ($assessment_keys as $key) {
			$slug = str_replace('_', '-', $key);
			$title = $all_definitions[$key]['title'] ?? ucwords(str_replace('_', ' ', $key));

			// Form Page
			$pages_to_create[$slug] = array('title' => $title, 'content' => "[ennu-{$slug}]");
			
			if ( 'welcome_assessment' !== $key ) {
				// Results Page
				$results_slug = str_replace('_assessment', '-results', $key);
				$pages_to_create[$results_slug] = array('title' => $title . ' Results', 'content' => "[ennu-{$results_slug}]");

				// Details Page
				$details_slug = str_replace('_assessment', '-assessment-details', $key);
				$pages_to_create[$details_slug] = array('title' => $title . ' Details', 'content' => "[ennu-{$details_slug}]");
			}
		}

		$page_mappings = get_option( 'ennu_created_pages', array() );

		foreach ( $pages_to_create as $slug => $page_data ) {
			// Only act if the page isn't already mapped and has a valid ID
			if ( empty( $page_mappings[ $slug ] ) || ! get_post( $page_mappings[ $slug ] ) ) {
				// First, check if a page with this slug already exists
				$existing_page = get_page_by_path( $slug, OBJECT, 'page' );

				if ( $existing_page ) {
					// If it exists, just map it
					$page_mappings[ $slug ] = $existing_page->ID;
				} else {
					// If it doesn't exist, create it
					$page_id = wp_insert_post(
						array(
							'post_title'   => $page_data['title'],
							'post_name'    => $slug,
							'post_content' => $page_data['content'],
							'post_status'  => 'publish',
							'post_type'    => 'page',
						)
					);
					if ( $page_id > 0 ) {
						$page_mappings[ $slug ] = $page_id;
					}
				}
			}
		}
		update_option( 'ennu_created_pages', $page_mappings );
	}

	private function delete_pages() {
		$page_mappings = get_option( 'ennu_created_pages', array() );
		if ( ! empty( $page_mappings ) ) {
			foreach ( $page_mappings as $page_id ) {
				if ( get_post_type( $page_id ) === 'page' ) {
					wp_delete_post( $page_id, true );
				}
			}
			// Clear the mappings after deletion
			update_option( 'ennu_created_pages', array() );
		}
	}

	private function display_created_pages() {
		$created_pages = get_option( 'ennu_created_pages', array() );
		if ( empty( $created_pages ) ) {
			return;
		}
		echo '<h3>' . esc_html__( 'Managed Pages', 'ennulifeassessments' ) . '</h3>';
		echo '<p>' . esc_html__( 'The following pages are managed by the plugin. Deleting them from here will remove them permanently.', 'ennulifeassessments' ) . '</p>';
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Page Title</th><th>Page ID</th><th>Shortcode</th><th>View</th></tr></thead><tbody>';
		foreach ( $created_pages as $slug => $page_id ) {
			$page = get_post( $page_id );
			if ( $page ) {
				echo '<tr>';
				echo '<td>' . esc_html( $page->post_title ) . '</td>';
				echo '<td>' . esc_html( $page_id ) . '</td>';
				echo '<td><code>' . esc_html( $page->post_content ) . '</code></td>';
				echo '<td><a href="' . esc_url( get_permalink( $page_id ) ) . '" target="_blank">View Page</a></td>';
				echo '</tr>';
			}
		}
		echo '</tbody></table>';
	}
}
