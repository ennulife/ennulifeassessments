<?php
/**
 * ENNU Database Migration Service
 *
 * Handles database schema creation and migrations for the refactored services.
 *
 * @package ENNU_Life_Assessments
 * @since 64.12.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Database Migration Service Class
 *
 * @since 64.12.0
 */
class ENNU_Database_Migration_Service {
	
	/**
	 * Database version option name
	 *
	 * @var string
	 */
	private $db_version_option = 'ennu_db_version';
	
	/**
	 * Current database version
	 *
	 * @var string
	 */
	private $current_version = '64.12.0';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		// Register activation hook for database setup
		add_action( 'ennu_plugin_activated', array( $this, 'setup_database' ) );
		
		// Check for database updates on plugin load
		add_action( 'init', array( $this, 'check_database_updates' ) );
	}
	
	/**
	 * Setup database tables
	 */
	public function setup_database() {
		$installed_version = get_option( $this->db_version_option, '0.0.0' );
		
		if ( version_compare( $installed_version, $this->current_version, '<' ) ) {
			$this->create_tables();
			$this->update_database_version();
		}
	}
	
	/**
	 * Check for database updates
	 */
	public function check_database_updates() {
		$installed_version = get_option( $this->db_version_option, '0.0.0' );
		
		if ( version_compare( $installed_version, $this->current_version, '<' ) ) {
			$this->setup_database();
		}
	}
	
	/**
	 * Create database tables
	 */
	private function create_tables() {
		global $wpdb;
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		$charset_collate = $wpdb->get_charset_collate();
		
		// Create biomarkers table
		$biomarkers_table = $wpdb->prefix . 'ennu_biomarkers';
		$biomarkers_sql = "CREATE TABLE {$biomarkers_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			name varchar(255) NOT NULL,
			value decimal(10,2) NOT NULL,
			unit varchar(50) NOT NULL,
			reference_range varchar(100) NOT NULL,
			category varchar(100) NOT NULL,
			status varchar(20) DEFAULT 'normal',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY category (category),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";
		
		// Create assessment_scores table
		$scores_table = $wpdb->prefix . 'ennu_assessment_scores';
		$scores_sql = "CREATE TABLE {$scores_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			assessment_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			assessment_type varchar(100) NOT NULL,
			biomarker_score decimal(5,2) DEFAULT 0.00,
			symptom_score decimal(5,2) DEFAULT 0.00,
			overall_score decimal(5,2) DEFAULT 0.00,
			score_data longtext,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY assessment_id (assessment_id),
			KEY user_id (user_id),
			KEY assessment_type (assessment_type),
			KEY created_at (created_at)
		) {$charset_collate};";
		
		// Create assessment_trends table
		$trends_table = $wpdb->prefix . 'ennu_assessment_trends';
		$trends_sql = "CREATE TABLE {$trends_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			biomarker_name varchar(255) NOT NULL,
			trend_direction varchar(20) NOT NULL,
			change_percent decimal(5,2) NOT NULL,
			first_value decimal(10,2) NOT NULL,
			last_value decimal(10,2) NOT NULL,
			data_points int(11) NOT NULL,
			analysis_period int(11) DEFAULT 30,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY biomarker_name (biomarker_name),
			KEY trend_direction (trend_direction),
			KEY created_at (created_at)
		) {$charset_collate};";
		
		// Create service_logs table for debugging and monitoring
		$logs_table = $wpdb->prefix . 'ennu_service_logs';
		$logs_sql = "CREATE TABLE {$logs_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			service_name varchar(100) NOT NULL,
			action varchar(100) NOT NULL,
			user_id bigint(20) DEFAULT NULL,
			status varchar(20) NOT NULL,
			message text,
			error_data longtext,
			execution_time decimal(10,4) DEFAULT 0.0000,
			memory_usage bigint(20) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY service_name (service_name),
			KEY action (action),
			KEY user_id (user_id),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";
		
		// Execute table creation
		$results = array();
		$results['biomarkers'] = dbDelta( $biomarkers_sql );
		$results['scores'] = dbDelta( $scores_sql );
		$results['trends'] = dbDelta( $trends_sql );
		$results['logs'] = dbDelta( $logs_sql );
		
		// Log the migration
		$this->log_service_action( 'database_migration', 'create_tables', array(
			'version' => $this->current_version,
			'results' => $results,
		) );
		
		return $results;
	}
	
	/**
	 * Update database version
	 */
	private function update_database_version() {
		update_option( $this->db_version_option, $this->current_version );
	}
	
	/**
	 * Get database version
	 *
	 * @return string Database version.
	 */
	public function get_database_version() {
		return get_option( $this->db_version_option, '0.0.0' );
	}
	
	/**
	 * Check if table exists
	 *
	 * @param string $table_name Table name.
	 * @return bool True if table exists.
	 */
	public function table_exists( $table_name ) {
		global $wpdb;
		
		$table = $wpdb->prefix . $table_name;
		$result = $wpdb->get_var( $wpdb->prepare(
			"SHOW TABLES LIKE %s",
			$table
		) );
		
		return $result === $table;
	}
	
	/**
	 * Get table structure
	 *
	 * @param string $table_name Table name.
	 * @return array Table structure.
	 */
	public function get_table_structure( $table_name ) {
		global $wpdb;
		
		$table = $wpdb->prefix . $table_name;
		$result = $wpdb->get_results( "DESCRIBE {$table}" );
		
		return $result ?: array();
	}
	
	/**
	 * Migrate existing data to new structure
	 *
	 * @return array Migration results.
	 */
	public function migrate_existing_data() {
		$results = array();
		
		// Migrate existing biomarker data from post meta
		$results['biomarkers'] = $this->migrate_biomarker_data();
		
		// Migrate existing assessment data
		$results['assessments'] = $this->migrate_assessment_data();
		
		// Log the migration
		$this->log_service_action( 'database_migration', 'migrate_existing_data', array(
			'results' => $results,
		) );
		
		return $results;
	}
	
	/**
	 * Migrate biomarker data from post meta to new table
	 *
	 * @return array Migration results.
	 */
	private function migrate_biomarker_data() {
		global $wpdb;
		
		$results = array(
			'total_records' => 0,
			'migrated_records' => 0,
			'errors' => array(),
		);
		
		// Get all posts with biomarker meta data
		$posts = get_posts( array(
			'post_type' => 'any',
			'meta_query' => array(
				array(
					'key' => '_ennu_biomarker_data',
					'compare' => 'EXISTS',
				),
			),
			'posts_per_page' => -1,
		) );
		
		foreach ( $posts as $post ) {
			$biomarker_data = get_post_meta( $post->ID, '_ennu_biomarker_data', true );
			
			if ( ! is_array( $biomarker_data ) ) {
				continue;
			}
			
			$results['total_records']++;
			
			try {
				// Insert into new biomarkers table
				$insert_result = $wpdb->insert(
					$wpdb->prefix . 'ennu_biomarkers',
					array(
						'user_id' => $post->post_author,
						'name' => sanitize_text_field( $biomarker_data['name'] ?? '' ),
						'value' => floatval( $biomarker_data['value'] ?? 0 ),
						'unit' => sanitize_text_field( $biomarker_data['unit'] ?? '' ),
						'reference_range' => sanitize_text_field( $biomarker_data['reference_range'] ?? '' ),
						'category' => sanitize_text_field( $biomarker_data['category'] ?? 'Other' ),
						'status' => sanitize_text_field( $biomarker_data['status'] ?? 'normal' ),
						'created_at' => $post->post_date,
					),
					array( '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s' )
				);
				
				if ( $insert_result !== false ) {
					$results['migrated_records']++;
				} else {
					$results['errors'][] = "Failed to migrate biomarker for post {$post->ID}: " . $wpdb->last_error;
				}
			} catch ( Exception $e ) {
				$results['errors'][] = "Exception migrating biomarker for post {$post->ID}: " . $e->getMessage();
			}
		}
		
		return $results;
	}
	
	/**
	 * Migrate assessment data to new structure
	 *
	 * @return array Migration results.
	 */
	private function migrate_assessment_data() {
		global $wpdb;
		
		$results = array(
			'total_assessments' => 0,
			'migrated_assessments' => 0,
			'errors' => array(),
		);
		
		// Get all assessment posts
		$assessments = get_posts( array(
			'post_type' => 'ennu_assessment',
			'posts_per_page' => -1,
		) );
		
		foreach ( $assessments as $assessment ) {
			$results['total_assessments']++;
			
			try {
				// Get assessment scores from meta
				$scores = get_post_meta( $assessment->ID, 'scores', true );
				
				if ( ! empty( $scores ) && is_array( $scores ) ) {
					// Insert into new scores table
					$insert_result = $wpdb->insert(
						$wpdb->prefix . 'ennu_assessment_scores',
						array(
							'assessment_id' => $assessment->ID,
							'user_id' => $assessment->post_author,
							'assessment_type' => get_post_meta( $assessment->ID, 'assessment_type', true ),
							'biomarker_score' => floatval( $scores['biomarker']['score'] ?? 0 ),
							'symptom_score' => floatval( $scores['symptom']['score'] ?? 0 ),
							'overall_score' => floatval( $scores['overall']['score'] ?? 0 ),
							'score_data' => wp_json_encode( $scores ),
							'created_at' => $assessment->post_date,
						),
						array( '%d', '%d', '%s', '%f', '%f', '%f', '%s', '%s' )
					);
					
					if ( $insert_result !== false ) {
						$results['migrated_assessments']++;
					} else {
						$results['errors'][] = "Failed to migrate scores for assessment {$assessment->ID}: " . $wpdb->last_error;
					}
				}
			} catch ( Exception $e ) {
				$results['errors'][] = "Exception migrating assessment {$assessment->ID}: " . $e->getMessage();
			}
		}
		
		return $results;
	}
	
	/**
	 * Log service action for debugging and monitoring
	 *
	 * @param string $service_name Service name.
	 * @param string $action Action performed.
	 * @param array  $data Additional data.
	 */
	public function log_service_action( $service_name, $action, $data = array() ) {
		global $wpdb;
		
		$start_time = microtime( true );
		$memory_start = memory_get_usage();
		
		$wpdb->insert(
			$wpdb->prefix . 'ennu_service_logs',
			array(
				'service_name' => sanitize_text_field( $service_name ),
				'action' => sanitize_text_field( $action ),
				'user_id' => get_current_user_id(),
				'status' => 'success',
				'message' => 'Service action completed successfully',
				'error_data' => null,
				'execution_time' => 0,
				'memory_usage' => 0,
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s', '%f', '%d' )
		);
		
		$end_time = microtime( true );
		$memory_end = memory_get_usage();
		
		// Update with actual performance data
		$wpdb->update(
			$wpdb->prefix . 'ennu_service_logs',
			array(
				'execution_time' => ( $end_time - $start_time ) * 1000, // Convert to milliseconds
				'memory_usage' => $memory_end - $memory_start,
			),
			array( 'id' => $wpdb->insert_id ),
			array( '%f', '%d' ),
			array( '%d' )
		);
	}
	
	/**
	 * Get service logs
	 *
	 * @param array $args Query arguments.
	 * @return array Service logs.
	 */
	public function get_service_logs( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'service_name' => '',
			'action' => '',
			'status' => '',
			'limit' => 100,
			'offset' => 0,
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$where_conditions = array();
		$where_values = array();
		
		if ( ! empty( $args['service_name'] ) ) {
			$where_conditions[] = 'service_name = %s';
			$where_values[] = $args['service_name'];
		}
		
		if ( ! empty( $args['action'] ) ) {
			$where_conditions[] = 'action = %s';
			$where_values[] = $args['action'];
		}
		
		if ( ! empty( $args['status'] ) ) {
			$where_conditions[] = 'status = %s';
			$where_values[] = $args['status'];
		}
		
		$where_clause = '';
		if ( ! empty( $where_conditions ) ) {
			$where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
		}
		
		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}ennu_service_logs 
			{$where_clause} 
			ORDER BY created_at DESC 
			LIMIT %d OFFSET %d",
			array_merge( $where_values, array( $args['limit'], $args['offset'] ) )
		);
		
		return $wpdb->get_results( $sql, ARRAY_A );
	}
	
	/**
	 * Clean old service logs
	 *
	 * @param int $days_to_keep Number of days to keep logs.
	 * @return int Number of deleted records.
	 */
	public function clean_old_logs( $days_to_keep = 30 ) {
		global $wpdb;
		
		$deleted = $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}ennu_service_logs 
			WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
			$days_to_keep
		) );
		
		return $deleted;
	}
} 