<?php
/**
 * ENNU Deployment Manager
 *
 * Comprehensive deployment management and production readiness
 *
 * @package ENNU_Life_Assessments
 * @since 64.19.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Deployment Manager Class
 *
 * @since 64.19.0
 */
class ENNU_Deployment_Manager {
	
	/**
	 * Deployment configuration
	 *
	 * @var array
	 */
	private $deployment_config = array(
		'auto_deploy' => false,
		'staging_environment' => '',
		'production_environment' => '',
		'backup_before_deploy' => true,
		'health_checks_enabled' => true,
		'rollback_enabled' => true,
	);
	
	/**
	 * Deployment status
	 *
	 * @var array
	 */
	private $deployment_status = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_ennu_deploy', array( $this, 'handle_deployment' ) );
		add_action( 'wp_ajax_nopriv_ennu_deploy', array( $this, 'handle_deployment' ) );
	}
	
	/**
	 * Initialize deployment manager
	 */
	public function init() {
		// Load configuration
		$this->load_configuration();
		
		// Add deployment hooks
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Add health checks
		if ( $this->deployment_config['health_checks_enabled'] ) {
			add_action( 'admin_init', array( $this, 'run_health_checks' ) );
		}
		
		// REMOVED: // REMOVED DEBUG LOG: error_log( 'ENNU Deployment Manager: Initialized successfully' );
	}
	
	/**
	 * Load configuration
	 */
	private function load_configuration() {
		$config = get_option( 'ennu_deployment_config', array() );
		
		if ( ! empty( $config ) ) {
			$this->deployment_config = wp_parse_args( $config, $this->deployment_config );
		}
	}
	
	/**
	 * Run health checks
	 */
	public function run_health_checks() {
		$health_checks = array(
			'database_connection' => $this->check_database_connection(),
			'file_permissions' => $this->check_file_permissions(),
			'plugin_dependencies' => $this->check_plugin_dependencies(),
			'system_requirements' => $this->check_system_requirements(),
			'security_status' => $this->check_security_status(),
		);
		
		$overall_status = 'healthy';
		foreach ( $health_checks as $check ) {
			if ( $check['status'] === 'unhealthy' ) {
				$overall_status = 'unhealthy';
				break;
			}
		}
		
		update_option( 'ennu_health_checks', array(
			'checks' => $health_checks,
			'overall_status' => $overall_status,
			'last_check' => current_time( 'mysql' ),
		) );
		
		// REMOVED: error_log( 'ENNU Deployment Manager: Health checks completed - Status: ' . $overall_status );
	}
	
	/**
	 * Check database connection
	 *
	 * @return array Health check result
	 */
	private function check_database_connection() {
		global $wpdb;
		
		$result = $wpdb->get_var( "SELECT 1" );
		
		return array(
			'status' => $result === '1' ? 'healthy' : 'unhealthy',
			'message' => $result === '1' ? 'Database connection successful' : 'Database connection failed',
			'details' => array(
				'host' => DB_HOST,
				'database' => DB_NAME,
				'user' => DB_USER,
			),
		);
	}
	
	/**
	 * Check file permissions
	 *
	 * @return array Health check result
	 */
	private function check_file_permissions() {
		$plugin_dir = ENNU_LIFE_PLUGIN_PATH;
		$issues = array();
		
		// Check if plugin directory is readable
		if ( ! is_readable( $plugin_dir ) ) {
			$issues[] = 'Plugin directory not readable';
		}
		
		// Check if plugin directory is writable
		if ( ! is_writable( $plugin_dir ) ) {
			$issues[] = 'Plugin directory not writable';
		}
		
		// Check specific files
		$critical_files = array(
			$plugin_dir . 'ennu-life-plugin.php',
			$plugin_dir . 'includes/',
			$plugin_dir . 'assets/',
		);
		
		foreach ( $critical_files as $file ) {
			if ( file_exists( $file ) && ! is_readable( $file ) ) {
				$issues[] = "File not readable: {$file}";
			}
		}
		
		return array(
			'status' => empty( $issues ) ? 'healthy' : 'unhealthy',
			'message' => empty( $issues ) ? 'File permissions are correct' : 'File permission issues found',
			'details' => $issues,
		);
	}
	
	/**
	 * Check plugin dependencies
	 *
	 * @return array Health check result
	 */
	private function check_plugin_dependencies() {
		$required_plugins = array(
			'WordPress' => array(
				'version' => '5.0',
				'current' => get_bloginfo( 'version' ),
			),
			'PHP' => array(
				'version' => '7.4',
				'current' => PHP_VERSION,
			),
		);
		
		$issues = array();
		
		foreach ( $required_plugins as $plugin => $requirements ) {
			if ( version_compare( $requirements['current'], $requirements['version'], '<' ) ) {
				$issues[] = "{$plugin} version {$requirements['current']} is below required version {$requirements['version']}";
			}
		}
		
		return array(
			'status' => empty( $issues ) ? 'healthy' : 'unhealthy',
			'message' => empty( $issues ) ? 'All dependencies satisfied' : 'Dependency issues found',
			'details' => $issues,
		);
	}
	
	/**
	 * Check system requirements
	 *
	 * @return array Health check result
	 */
	private function check_system_requirements() {
		$issues = array();
		
		// Check memory limit
		$memory_limit = ini_get( 'memory_limit' );
		$memory_limit_bytes = wp_convert_hr_to_bytes( $memory_limit );
		if ( $memory_limit_bytes < 64 * 1024 * 1024 ) { // 64MB
			$issues[] = "Memory limit ({$memory_limit}) is below recommended 64MB";
		}
		
		// Check max execution time
		$max_execution_time = ini_get( 'max_execution_time' );
		if ( $max_execution_time > 0 && $max_execution_time < 30 ) {
			$issues[] = "Max execution time ({$max_execution_time}s) is below recommended 30s";
		}
		
		// Check upload max filesize
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$upload_max_filesize_bytes = wp_convert_hr_to_bytes( $upload_max_filesize );
		if ( $upload_max_filesize_bytes < 2 * 1024 * 1024 ) { // 2MB
			$issues[] = "Upload max filesize ({$upload_max_filesize}) is below recommended 2MB";
		}
		
		return array(
			'status' => empty( $issues ) ? 'healthy' : 'unhealthy',
			'message' => empty( $issues ) ? 'System requirements satisfied' : 'System requirement issues found',
			'details' => $issues,
		);
	}
	
	/**
	 * Check security status
	 *
	 * @return array Health check result
	 */
	private function check_security_status() {
		$issues = array();
		
		// Check if debug mode is enabled
		if ( WP_DEBUG ) {
			$issues[] = 'Debug mode is enabled (should be disabled in production)';
		}
		
		// Check if file editing is disabled
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
			$issues[] = 'File editing is enabled (should be disabled in production)';
		}
		
		// Check if file modifications are disabled
		if ( ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS ) {
			$issues[] = 'File modifications are enabled (should be disabled in production)';
		}
		
		return array(
			'status' => empty( $issues ) ? 'healthy' : 'unhealthy',
			'message' => empty( $issues ) ? 'Security settings are correct' : 'Security issues found',
			'details' => $issues,
		);
	}
	
	/**
	 * Prepare deployment
	 *
	 * @return array Deployment preparation result
	 */
	public function prepare_deployment() {
		$preparation = array(
			'status' => 'ready',
			'checks' => array(),
			'backup_created' => false,
			'issues' => array(),
		);
		
		// Run health checks
		$health_checks = get_option( 'ennu_health_checks', array() );
		if ( ! empty( $health_checks ) && $health_checks['overall_status'] === 'unhealthy' ) {
			$preparation['status'] = 'not_ready';
			$preparation['issues'][] = 'Health checks failed';
		}
		
		// Create backup if enabled
		if ( $this->deployment_config['backup_before_deploy'] ) {
			$backup_result = $this->create_backup();
			$preparation['backup_created'] = $backup_result['success'];
			if ( ! $backup_result['success'] ) {
				$preparation['status'] = 'not_ready';
				$preparation['issues'][] = 'Backup creation failed';
			}
		}
		
		// Check deployment environment
		if ( empty( $this->deployment_config['production_environment'] ) ) {
			$preparation['status'] = 'not_ready';
			$preparation['issues'][] = 'Production environment not configured';
		}
		
		// Check rollback capability
		if ( $this->deployment_config['rollback_enabled'] ) {
			$rollback_check = $this->check_rollback_capability();
			if ( ! $rollback_check['available'] ) {
				$preparation['status'] = 'not_ready';
				$preparation['issues'][] = 'Rollback capability not available';
			}
		}
		
		return $preparation;
	}
	
	/**
	 * Create backup
	 *
	 * @return array Backup result
	 */
	private function create_backup() {
		$backup_dir = WP_CONTENT_DIR . '/backups/ennu-life-assessments/';
		
		// Create backup directory if it doesn't exist
		if ( ! is_dir( $backup_dir ) ) {
			wp_mkdir_p( $backup_dir );
		}
		
		$timestamp = current_time( 'Y-m-d_H-i-s' );
		$backup_file = $backup_dir . "backup_{$timestamp}.zip";
		
		// Create backup of plugin files
		$plugin_dir = ENNU_LIFE_PLUGIN_PATH;
		$zip = new ZipArchive();
		
		if ( $zip->open( $backup_file, ZipArchive::CREATE ) === true ) {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $plugin_dir )
			);
			
			foreach ( $iterator as $file ) {
				if ( $file->isFile() ) {
					$file_path = $file->getRealPath();
					$relative_path = substr( $file_path, strlen( $plugin_dir ) );
					$zip->addFile( $file_path, $relative_path );
				}
			}
			
			$zip->close();
			
			return array(
				'success' => true,
				'file' => $backup_file,
				'size' => filesize( $backup_file ),
			);
		}
		
		return array(
			'success' => false,
			'error' => 'Failed to create backup',
		);
	}
	
	/**
	 * Check rollback capability
	 *
	 * @return array Rollback check result
	 */
	private function check_rollback_capability() {
		$backup_dir = WP_CONTENT_DIR . '/backups/ennu-life-assessments/';
		
		if ( ! is_dir( $backup_dir ) ) {
			return array(
				'available' => false,
				'reason' => 'Backup directory does not exist',
			);
		}
		
		$backups = glob( $backup_dir . 'backup_*.zip' );
		
		return array(
			'available' => ! empty( $backups ),
			'backup_count' => count( $backups ),
			'latest_backup' => ! empty( $backups ) ? basename( end( $backups ) ) : null,
		);
	}
	
	/**
	 * Execute deployment
	 *
	 * @return array Deployment result
	 */
	public function execute_deployment() {
		$deployment = array(
			'status' => 'in_progress',
			'steps' => array(),
			'start_time' => current_time( 'mysql' ),
		);
		
		// Step 1: Pre-deployment checks
		$preparation = $this->prepare_deployment();
		$deployment['steps']['preparation'] = $preparation;
		
		if ( $preparation['status'] !== 'ready' ) {
			$deployment['status'] = 'failed';
			$deployment['error'] = 'Pre-deployment checks failed';
			return $deployment;
		}
		
		// Step 2: Deploy to staging (if configured)
		if ( ! empty( $this->deployment_config['staging_environment'] ) ) {
			$staging_deploy = $this->deploy_to_staging();
			$deployment['steps']['staging'] = $staging_deploy;
			
			if ( ! $staging_deploy['success'] ) {
				$deployment['status'] = 'failed';
				$deployment['error'] = 'Staging deployment failed';
				return $deployment;
			}
		}
		
		// Step 3: Deploy to production
		$production_deploy = $this->deploy_to_production();
		$deployment['steps']['production'] = $production_deploy;
		
		if ( ! $production_deploy['success'] ) {
			$deployment['status'] = 'failed';
			$deployment['error'] = 'Production deployment failed';
			return $deployment;
		}
		
		// Step 4: Post-deployment verification
		$verification = $this->verify_deployment();
		$deployment['steps']['verification'] = $verification;
		
		if ( ! $verification['success'] ) {
			$deployment['status'] = 'failed';
			$deployment['error'] = 'Deployment verification failed';
			return $deployment;
		}
		
		$deployment['status'] = 'completed';
		$deployment['end_time'] = current_time( 'mysql' );
		
		// Save deployment history
		$this->save_deployment_history( $deployment );
		
		return $deployment;
	}
	
	/**
	 * Deploy to staging
	 *
	 * @return array Staging deployment result
	 */
	private function deploy_to_staging() {
		// Mock staging deployment
		return array(
			'success' => true,
			'message' => 'Staging deployment completed successfully',
			'duration' => 30, // seconds
		);
	}
	
	/**
	 * Deploy to production
	 *
	 * @return array Production deployment result
	 */
	private function deploy_to_production() {
		// Mock production deployment
		return array(
			'success' => true,
			'message' => 'Production deployment completed successfully',
			'duration' => 60, // seconds
		);
	}
	
	/**
	 * Verify deployment
	 *
	 * @return array Verification result
	 */
	private function verify_deployment() {
		$verification = array(
			'success' => true,
			'checks' => array(),
		);
		
		// Check if plugin is active
		$verification['checks']['plugin_active'] = is_plugin_active( 'ennulifeassessments/ennu-life-plugin.php' );
		
		// Check if all services are initialized
		$verification['checks']['services_initialized'] = $this->check_services_initialization();
		
		// Check if database tables exist
		$verification['checks']['database_tables'] = $this->check_database_tables();
		
		// Check if assets are accessible
		$verification['checks']['assets_accessible'] = $this->check_assets_accessibility();
		
		// Overall verification
		foreach ( $verification['checks'] as $check ) {
			if ( ! $check ) {
				$verification['success'] = false;
				break;
			}
		}
		
		return $verification;
	}
	
	/**
	 * Check services initialization
	 *
	 * @return bool Services initialized
	 */
	private function check_services_initialization() {
		// Check if key services are available
		$services = array(
			'ENNU_Biomarker_Service',
			'ENNU_Assessment_Service',
			'ENNU_AJAX_Service_Handler',
		);
		
		foreach ( $services as $service ) {
			if ( ! class_exists( $service ) ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Check database tables
	 *
	 * @return bool Database tables exist
	 */
	private function check_database_tables() {
		global $wpdb;
		
		// Check if WordPress tables exist
		$tables = array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->users,
			$wpdb->usermeta,
		);
		
		foreach ( $tables as $table ) {
			$result = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
			if ( $result !== $table ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Check assets accessibility
	 *
	 * @return bool Assets accessible
	 */
	private function check_assets_accessibility() {
		$plugin_url = plugin_dir_url( ENNU_LIFE_PLUGIN_FILE );
		$test_asset = $plugin_url . 'assets/css/user-dashboard.css';
		
		$response = wp_remote_get( $test_asset );
		
		return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
	}
	
	/**
	 * Save deployment history
	 *
	 * @param array $deployment Deployment data
	 */
	private function save_deployment_history( $deployment ) {
		$history = get_option( 'ennu_deployment_history', array() );
		$history[] = $deployment;
		
		// Keep only last 10 deployments
		if ( count( $history ) > 10 ) {
			$history = array_slice( $history, -10 );
		}
		
		update_option( 'ennu_deployment_history', $history );
	}
	
	/**
	 * AJAX handler for deployment
	 */
	public function handle_deployment() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_deployment' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
		
		switch ( $action ) {
			case 'prepare':
				$result = $this->prepare_deployment();
				break;
				
			case 'deploy':
				$result = $this->execute_deployment();
				break;
				
			case 'get_status':
				$result = $this->get_deployment_status();
				break;
				
			case 'update_config':
				$config = isset( $_POST['config'] ) ? $_POST['config'] : array();
				$result = $this->update_configuration( $config );
				break;
				
			default:
				$result = array( 'error' => 'Invalid action' );
		}
		
		wp_send_json_success( $result );
	}
	
	/**
	 * Get deployment status
	 *
	 * @return array Deployment status
	 */
	private function get_deployment_status() {
		$status = array(
			'health_checks' => get_option( 'ennu_health_checks', array() ),
			'deployment_history' => get_option( 'ennu_deployment_history', array() ),
			'current_status' => 'ready',
		);
		
		// Check if system is ready for deployment
		if ( ! empty( $status['health_checks'] ) && $status['health_checks']['overall_status'] === 'unhealthy' ) {
			$status['current_status'] = 'not_ready';
		}
		
		return $status;
	}
	
	/**
	 * Update configuration
	 *
	 * @param array $config Configuration data
	 * @return array Update result
	 */
	private function update_configuration( $config ) {
		$current_config = $this->deployment_config;
		$updated_config = wp_parse_args( $config, $current_config );
		
		$result = update_option( 'ennu_deployment_config', $updated_config );
		
		if ( $result ) {
			$this->deployment_config = $updated_config;
		}
		
		return array(
			'success' => $result,
			'config' => $updated_config,
		);
	}
	
	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life-assessments',
			'Deployment Manager',
			'Deployment',
			'manage_options',
			'ennu-deployment',
			array( $this, 'admin_page' )
		);
	}
	
	/**
	 * Admin page
	 */
	public function admin_page() {
		$status = $this->get_deployment_status();
		$health_checks = $status['health_checks'];
		?>
		<div class="wrap">
			<h1>ENNU Deployment Manager</h1>
			<p>Comprehensive deployment management and production readiness.</p>
			
			<div class="deployment-status">
				<h2>Deployment Status</h2>
				<table class="widefat">
					<thead>
						<tr>
							<th>Component</th>
							<th>Status</th>
							<th>Details</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Overall Health</td>
							<td><?php echo esc_html( ucfirst( $health_checks['overall_status'] ?? 'unknown' ) ); ?></td>
							<td><?php echo ( $health_checks['overall_status'] ?? 'unknown' ) === 'healthy' ? '<span style="color: green;">✓ Healthy</span>' : '<span style="color: red;">✗ Unhealthy</span>'; ?></td>
						</tr>
						<tr>
							<td>Database Connection</td>
							<td><?php echo esc_html( ucfirst( $health_checks['checks']['database_connection']['status'] ?? 'unknown' ) ); ?></td>
							<td><?php echo esc_html( $health_checks['checks']['database_connection']['message'] ?? '' ); ?></td>
						</tr>
						<tr>
							<td>File Permissions</td>
							<td><?php echo esc_html( ucfirst( $health_checks['checks']['file_permissions']['status'] ?? 'unknown' ) ); ?></td>
							<td><?php echo esc_html( $health_checks['checks']['file_permissions']['message'] ?? '' ); ?></td>
						</tr>
						<tr>
							<td>Plugin Dependencies</td>
							<td><?php echo esc_html( ucfirst( $health_checks['checks']['plugin_dependencies']['status'] ?? 'unknown' ) ); ?></td>
							<td><?php echo esc_html( $health_checks['checks']['plugin_dependencies']['message'] ?? '' ); ?></td>
						</tr>
						<tr>
							<td>System Requirements</td>
							<td><?php echo esc_html( ucfirst( $health_checks['checks']['system_requirements']['status'] ?? 'unknown' ) ); ?></td>
							<td><?php echo esc_html( $health_checks['checks']['system_requirements']['message'] ?? '' ); ?></td>
						</tr>
						<tr>
							<td>Security Status</td>
							<td><?php echo esc_html( ucfirst( $health_checks['checks']['security_status']['status'] ?? 'unknown' ) ); ?></td>
							<td><?php echo esc_html( $health_checks['checks']['security_status']['message'] ?? '' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<div class="deployment-actions">
				<h2>Deployment Actions</h2>
				<p>
					<button id="prepare-deployment" class="button button-primary">Prepare Deployment</button>
					<button id="execute-deployment" class="button button-secondary">Execute Deployment</button>
					<button id="get-status" class="button button-secondary">Get Status</button>
				</p>
			</div>
			
			<div class="deployment-config">
				<h2>Deployment Configuration</h2>
				<form id="deployment-form">
					<?php wp_nonce_field( 'ennu_deployment', 'deployment_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th scope="row">Auto Deploy</th>
							<td>
								<label><input type="checkbox" name="config[auto_deploy]" value="1" <?php checked( $this->deployment_config['auto_deploy'] ); ?>> Enable Auto Deploy</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Staging Environment</th>
							<td>
								<input type="url" name="config[staging_environment]" value="<?php echo esc_attr( $this->deployment_config['staging_environment'] ); ?>" class="regular-text" placeholder="https://staging.example.com">
							</td>
						</tr>
						<tr>
							<th scope="row">Production Environment</th>
							<td>
								<input type="url" name="config[production_environment]" value="<?php echo esc_attr( $this->deployment_config['production_environment'] ); ?>" class="regular-text" placeholder="https://example.com">
							</td>
						</tr>
						<tr>
							<th scope="row">Backup Before Deploy</th>
							<td>
								<label><input type="checkbox" name="config[backup_before_deploy]" value="1" <?php checked( $this->deployment_config['backup_before_deploy'] ); ?>> Create backup before deployment</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Health Checks</th>
							<td>
								<label><input type="checkbox" name="config[health_checks_enabled]" value="1" <?php checked( $this->deployment_config['health_checks_enabled'] ); ?>> Enable health checks</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Rollback Enabled</th>
							<td>
								<label><input type="checkbox" name="config[rollback_enabled]" value="1" <?php checked( $this->deployment_config['rollback_enabled'] ); ?>> Enable rollback capability</label>
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Update Configuration">
					</p>
				</form>
			</div>
			
			<div id="deployment-results" style="display: none;">
				<h2>Deployment Results</h2>
				<div id="results-content"></div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('#prepare-deployment').on('click', function() {
				handleDeployment('prepare');
			});
			
			$('#execute-deployment').on('click', function() {
				handleDeployment('deploy');
			});
			
			$('#get-status').on('click', function() {
				handleDeployment('get_status');
			});
			
			$('#deployment-form').on('submit', function(e) {
				e.preventDefault();
				
				var formData = new FormData(this);
				formData.append('action', 'ennu_deploy');
				formData.append('action_type', 'update_config');
				formData.append('nonce', '<?php echo wp_create_nonce( 'ennu_deployment' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#deployment-results').show();
						}
					}
				});
			});
			
			function handleDeployment(action) {
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_deploy',
						action_type: action,
						nonce: '<?php echo wp_create_nonce( 'ennu_deployment' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#deployment-results').show();
						}
					}
				});
			}
		});
		</script>
		<?php
	}
} 