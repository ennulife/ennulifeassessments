<?php
/**
 * Health Goals Data Migration Script
 * Fixes the critical disconnect between dashboard display and scoring calculation
 *
 * @package ENNU_Life
 * @version 62.1.67
 * @author The World's Greatest WordPress Developer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Health_Goals_Migration {

	/**
	 * Execute the complete health goals migration
	 */
	public static function execute_migration() {
		$results = array(
			'users_migrated'     => 0,
			'goals_preserved'    => 0,
			'duplicates_cleaned' => 0,
			'errors'             => array(),
		);

		try {
			// Step 1: Migrate from old key to new key
			$results['users_migrated'] = self::migrate_health_goals_data();

			// Step 2: Clean up duplicate data
			$results['duplicates_cleaned'] = self::clean_duplicate_health_goals();

			// Step 3: Validate migration
			$validation_result          = self::validate_migration();
			$results['goals_preserved'] = $validation_result['total_goals'];

			// Step 4: Update plugin flag
			update_option( 'ennu_health_goals_migration_completed', time() );

		} catch ( Exception $e ) {
			$results['errors'][] = $e->getMessage();
		}

		return $results;
	}

	/**
	 * Migrate health goals from wrong key to correct key
	 */
	public static function migrate_health_goals_data() {
		global $wpdb;

		// Get all users with health goals in the WRONG key (ennu_health_goals)
		$wrong_key_users = $wpdb->get_results(
			$wpdb->prepare(
				"
            SELECT user_id, meta_value 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = %s 
            AND meta_value != ''
        ",
				'ennu_health_goals'
			)
		);

		$migrated_count = 0;

		foreach ( $wrong_key_users as $user_meta ) {
			$user_id    = $user_meta->user_id;
			$goals_data = maybe_unserialize( $user_meta->meta_value );

			if ( is_array( $goals_data ) && ! empty( $goals_data ) ) {
				// Check if correct key already has data
				$existing_correct_data = get_user_meta( $user_id, 'ennu_global_health_goals', true );

				if ( empty( $existing_correct_data ) ) {
					// Safe to migrate - no existing data in correct key
					update_user_meta( $user_id, 'ennu_global_health_goals', $goals_data );
					delete_user_meta( $user_id, 'ennu_health_goals' );
					$migrated_count++;
				} else {
					// Merge data intelligently
					$merged_goals = self::merge_health_goals( $existing_correct_data, $goals_data );
					update_user_meta( $user_id, 'ennu_global_health_goals', $merged_goals );
					delete_user_meta( $user_id, 'ennu_health_goals' );
					$migrated_count++;
				}
			}
		}

		return $migrated_count;
	}

	/**
	 * Intelligently merge health goals from two sources
	 */
	private static function merge_health_goals( $existing_goals, $new_goals ) {
		$existing_goals = is_array( $existing_goals ) ? $existing_goals : array();
		$new_goals      = is_array( $new_goals ) ? $new_goals : array();

		// Combine and deduplicate
		$merged = array_unique( array_merge( $existing_goals, $new_goals ) );

		// Validate against allowed goals
		$allowed_goals = self::get_allowed_health_goals();
		$validated     = array_intersect( $merged, array_keys( $allowed_goals ) );

		return array_values( $validated );
	}

	/**
	 * Clean up any remaining duplicate or orphaned health goals data
	 */
	public static function clean_duplicate_health_goals() {
		global $wpdb;

		// Remove any remaining wrong key entries
		$deleted_count = $wpdb->query(
			$wpdb->prepare(
				"
            DELETE FROM {$wpdb->usermeta} 
            WHERE meta_key = %s
        ",
				'ennu_health_goals'
			)
		);

		// Clean up empty health goals entries
		$wpdb->query(
			$wpdb->prepare(
				"
            DELETE FROM {$wpdb->usermeta} 
            WHERE meta_key = %s 
            AND (meta_value = '' OR meta_value = 'a:0:{}')
        ",
				'ennu_global_health_goals'
			)
		);

		return $deleted_count;
	}

	/**
	 * Validate migration completed successfully
	 */
	public static function validate_migration() {
		global $wpdb;

		// Count users with health goals in correct key
		$correct_key_count = $wpdb->get_var(
			$wpdb->prepare(
				"
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = %s 
            AND meta_value != '' 
            AND meta_value != 'a:0:{}'
        ",
				'ennu_global_health_goals'
			)
		);

		// Ensure no users have goals in wrong key
		$wrong_key_count = $wpdb->get_var(
			$wpdb->prepare(
				"
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = %s
        ",
				'ennu_health_goals'
			)
		);

		// Count total health goals across all users
		$total_goals      = 0;
		$users_with_goals = $wpdb->get_results(
			$wpdb->prepare(
				"
            SELECT meta_value 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = %s 
            AND meta_value != '' 
            AND meta_value != 'a:0:{}'
        ",
				'ennu_global_health_goals'
			)
		);

		foreach ( $users_with_goals as $user_meta ) {
			$goals = maybe_unserialize( $user_meta->meta_value );
			if ( is_array( $goals ) ) {
				$total_goals += count( $goals );
			}
		}

		return array(
			'users_with_correct_key' => $correct_key_count,
			'users_with_wrong_key'   => $wrong_key_count,
			'total_goals'            => $total_goals,
			'migration_successful'   => ( $wrong_key_count === 0 ),
		);
	}

	/**
	 * Get allowed health goals from system configuration
	 */
	private static function get_allowed_health_goals() {
		// Try health goals config first
		$health_goals_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
		if ( file_exists( $health_goals_config ) ) {
			$config = require $health_goals_config;
			if ( isset( $config['goal_definitions'] ) ) {
				return $config['goal_definitions'];
			}
		}

		// Fallback to welcome assessment options
		$welcome_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/welcome.php';
		if ( file_exists( $welcome_config ) ) {
			$config = require $welcome_config;
			if ( isset( $config['questions']['welcome_q3']['options'] ) ) {
				return $config['questions']['welcome_q3']['options'];
			}
		}

		// Final fallback - default health goals
		return array(
			'longevity'        => 'Longevity & Healthy Aging',
			'energy'           => 'Improve Energy & Vitality',
			'strength'         => 'Build Strength & Muscle',
			'libido'           => 'Enhance Libido & Sexual Health',
			'weight_loss'      => 'Achieve & Maintain Healthy Weight',
			'hormonal_balance' => 'Hormonal Balance',
			'cognitive_health' => 'Sharpen Cognitive Function',
			'heart_health'     => 'Support Heart Health',
			'aesthetics'       => 'Improve Hair, Skin & Nails',
			'sleep'            => 'Improve Sleep Quality',
			'stress'           => 'Reduce Stress & Improve Resilience',
		);
	}

	/**
	 * Generate migration report
	 */
	public static function generate_migration_report( $results ) {
		$report  = "=== ENNU HEALTH GOALS MIGRATION REPORT ===\n";
		$report .= 'Date: ' . current_time( 'Y-m-d H:i:s' ) . "\n";
		$report .= 'Plugin Version: ' . ENNU_LIFE_VERSION . "\n\n";

		$report .= "MIGRATION RESULTS:\n";
		$report .= '- Users Migrated: ' . $results['users_migrated'] . "\n";
		$report .= '- Goals Preserved: ' . $results['goals_preserved'] . "\n";
		$report .= '- Duplicates Cleaned: ' . $results['duplicates_cleaned'] . "\n";

		if ( ! empty( $results['errors'] ) ) {
			$report .= "\nERRORS ENCOUNTERED:\n";
			foreach ( $results['errors'] as $error ) {
				$report .= '- ' . $error . "\n";
			}
		}

		$report .= "\nMIGRATION STATUS: " . ( empty( $results['errors'] ) ? 'SUCCESS' : 'COMPLETED WITH ERRORS' ) . "\n";
		$report .= "=== END REPORT ===\n";

		return $report;
	}
}

/**
 * Admin interface for running migration
 */
class ENNU_Health_Goals_Migration_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_migration_page' ) );
		add_action( 'wp_ajax_ennu_run_health_goals_migration', array( $this, 'handle_migration_ajax' ) );
	}

	public function add_migration_page() {
		add_submenu_page(
			'tools.php',
			'ENNU Health Goals Migration',
			'ENNU Migration',
			'manage_options',
			'ennu-health-goals-migration',
			array( $this, 'render_migration_page' )
		);
	}

	public function render_migration_page() {
		$migration_completed = get_option( 'ennu_health_goals_migration_completed', false );

		?>
		<div class="wrap">
			<h1>ENNU Health Goals Migration</h1>
			
			<?php if ( $migration_completed ) : ?>
				<div class="notice notice-success">
					<p><strong>Migration Completed:</strong> <?php echo date( 'Y-m-d H:i:s', $migration_completed ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-warning">
					<p><strong>Migration Required:</strong> Health goals data needs to be unified for proper functionality.</p>
				</div>
			<?php endif; ?>
			
			<div class="card">
				<h2>Migration Details</h2>
				<p>This migration will:</p>
				<ul>
					<li>Unify health goals data from <code>ennu_health_goals</code> to <code>ennu_global_health_goals</code></li>
					<li>Ensure dashboard display and scoring use the same data source</li>
					<li>Clean up duplicate and orphaned data</li>
					<li>Validate all health goals against allowed options</li>
				</ul>
				
				<p><strong>This process is safe and reversible.</strong> All data will be preserved.</p>
				
				<button id="run-migration" class="button button-primary" <?php echo $migration_completed ? 'disabled' : ''; ?>>
					<?php echo $migration_completed ? 'Migration Already Completed' : 'Run Migration'; ?>
				</button>
				
				<div id="migration-progress" style="display: none;">
					<p>Migration in progress...</p>
					<div class="progress-bar">
						<div class="progress-fill"></div>
					</div>
				</div>
				
				<div id="migration-results" style="display: none;"></div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('#run-migration').on('click', function() {
				$('#migration-progress').show();
				$(this).prop('disabled', true);
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_run_health_goals_migration',
						nonce: '<?php echo wp_create_nonce( 'ennu_migration_nonce' ); ?>'
					},
					success: function(response) {
						$('#migration-progress').hide();
						if (response.success) {
							$('#migration-results').html(
								'<div class="notice notice-success"><p><strong>Migration Successful!</strong></p><pre>' + 
								response.data.report + '</pre></div>'
							).show();
						} else {
							$('#migration-results').html(
								'<div class="notice notice-error"><p><strong>Migration Failed:</strong> ' + 
								response.data.message + '</p></div>'
							).show();
						}
					},
					error: function() {
						$('#migration-progress').hide();
						$('#migration-results').html(
							'<div class="notice notice-error"><p><strong>Migration Failed:</strong> Network error occurred.</p></div>'
						).show();
					}
				});
			});
		});
		</script>
		
		<style>
		.progress-bar {
			width: 100%;
			height: 20px;
			background: #f0f0f0;
			border-radius: 10px;
			overflow: hidden;
			margin: 10px 0;
		}
		.progress-fill {
			height: 100%;
			background: linear-gradient(90deg, #0073aa, #005a87);
			width: 0%;
			animation: progress 3s ease-in-out infinite;
		}
		@keyframes progress {
			0% { width: 0%; }
			50% { width: 70%; }
			100% { width: 100%; }
		}
		</style>
		<?php
	}

	public function handle_migration_ajax() {
		check_ajax_referer( 'ennu_migration_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		try {
			$results = ENNU_Health_Goals_Migration::execute_migration();
			$report  = ENNU_Health_Goals_Migration::generate_migration_report( $results );

			wp_send_json_success(
				array(
					'results' => $results,
					'report'  => $report,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}

// Initialize migration admin interface
if ( is_admin() ) {
	new ENNU_Health_Goals_Migration_Admin();
}
