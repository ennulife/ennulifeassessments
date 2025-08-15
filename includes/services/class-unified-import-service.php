<?php
/**
 * ENNU Unified Import Service
 *
 * Consolidates all import systems into a single, authoritative service
 *
 * @package ENNU_Life_Assessments
 * @since 64.13.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Unified Import Service Class
 *
 * @since 64.13.0
 */
class ENNU_Unified_Import_Service {
	
	/**
	 * Database instance
	 *
	 * @var ENNU_Enhanced_Database
	 */
	private $database;
	
	/**
	 * Biomarker service instance
	 *
	 * @var ENNU_Biomarker_Service
	 */
	private $biomarker_service;
	
	/**
	 * Supported import types
	 *
	 * @var array
	 */
	private $supported_types = array(
		'lab_data' => array(
			'name' => 'Lab Data Import',
			'description' => 'Import lab results with comprehensive validation',
			'file_formats' => array( 'csv', 'pdf' ),
			'providers' => array( 'labcorp', 'quest', 'sonora', 'generic' ),
		),
		'biomarker_csv' => array(
			'name' => 'Biomarker CSV Import',
			'description' => 'Import current biomarker values from CSV',
			'file_formats' => array( 'csv' ),
			'providers' => array( 'generic' ),
		),
		'user_csv' => array(
			'name' => 'User CSV Import',
			'description' => 'User self-service biomarker import',
			'file_formats' => array( 'csv' ),
			'providers' => array( 'generic' ),
		),
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->database = ENNU_Life_Enhanced_Plugin::get_instance()->get_database();
		$this->biomarker_service = new ENNU_Biomarker_Service();
	}
	
	/**
	 * Initialize the unified import service
	 */
	public function init() {
		// Register AJAX handlers
		$this->register_ajax_handlers();
		
		// Register admin hooks
		$this->register_admin_hooks();
		
		// Register shortcode
		$this->register_shortcode();
		
		// REMOVED: // REMOVED DEBUG LOG: error_log( 'ENNU Unified Import Service: Initialized successfully' );
	}
	
	/**
	 * Register AJAX handlers
	 */
	private function register_ajax_handlers() {
		// Lab data import
		add_action( 'wp_ajax_ennu_unified_import_lab_data', array( $this, 'handle_lab_data_import' ) );
		add_action( 'wp_ajax_ennu_unified_import_biomarker_csv', array( $this, 'handle_biomarker_csv_import' ) );
		add_action( 'wp_ajax_ennu_unified_import_user_csv', array( $this, 'handle_user_csv_import' ) );
		add_action( 'wp_ajax_nopriv_ennu_unified_import_user_csv', array( $this, 'handle_user_csv_import' ) );
		
		// Validation and template endpoints
		add_action( 'wp_ajax_ennu_unified_validate_import', array( $this, 'handle_import_validation' ) );
		add_action( 'wp_ajax_ennu_unified_get_template', array( $this, 'handle_get_template' ) );
	}
	
	/**
	 * Register admin hooks
	 */
	private function register_admin_hooks() {
		add_action( 'admin_menu', array( $this, 'add_import_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}
	
	/**
	 * Register shortcode
	 */
	private function register_shortcode() {
		add_shortcode( 'ennu_unified_import', array( $this, 'render_import_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}
	
	/**
	 * Add import admin page
	 */
	public function add_import_admin_page() {
		add_submenu_page(
			'ennu-life',
			__( 'Unified Import', 'ennulifeassessments' ),
			__( 'Import Data', 'ennulifeassessments' ),
			'manage_options',
			'ennu-unified-import',
			array( $this, 'render_admin_page' )
		);
	}
	
	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'ennu-unified-import' ) === false ) {
			return;
		}
		
		wp_enqueue_style( 'ennu-unified-import-admin', ENNU_LIFE_PLUGIN_URL . 'assets/css/unified-import-admin.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_script( 'ennu-unified-import-admin', ENNU_LIFE_PLUGIN_URL . 'assets/js/unified-import-admin.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
		
		wp_localize_script(
			'ennu-unified-import-admin',
			'ennuUnifiedImport',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ennu_unified_import_nonce' ),
			)
		);
	}
	
	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Only enqueue if shortcode is present
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu_unified_import' ) ) {
			wp_enqueue_style( 'ennu-unified-import-frontend', ENNU_LIFE_PLUGIN_URL . 'assets/css/unified-import-frontend.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_script( 'ennu-unified-import-frontend', ENNU_LIFE_PLUGIN_URL . 'assets/js/unified-import-frontend.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
			
			wp_localize_script(
				'ennu-unified-import-frontend',
				'ennuUnifiedImportFrontend',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ennu_unified_import_frontend_nonce' ),
				)
			);
		}
	}
	
	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		$import_types = $this->get_supported_import_types();
		$users = get_users( array( 'meta_key' => 'ennu_life_score' ) );
		
		include ENNU_LIFE_PLUGIN_PATH . 'templates/admin/unified-import-page.php';
	}
	
	/**
	 * Render import shortcode
	 */
	public function render_import_shortcode( $atts ) {
		if ( ! is_user_logged_in() ) {
			return $this->render_login_message();
		}
		
		$atts = shortcode_atts(
			array(
				'type' => 'user_csv',
				'show_instructions' => 'true',
				'show_sample' => 'true',
			),
			$atts,
			'ennu_unified_import'
		);
		
		$current_user = wp_get_current_user();
		$import_type = $this->get_import_type_config( $atts['type'] );
		
		ob_start();
		include ENNU_LIFE_PLUGIN_PATH . 'templates/frontend/unified-import-form.php';
		return ob_get_clean();
	}
	
	/**
	 * Handle lab data import
	 */
	public function handle_lab_data_import() {
		// Verify nonce and permissions
		if ( ! $this->verify_admin_permissions() ) {
			wp_die( 'Unauthorized' );
		}
		
		$file = $_FILES['lab_data_file'] ?? null;
		$provider = sanitize_text_field( $_POST['provider'] ?? 'generic' );
		$user_id = absint( $_POST['user_id'] ?? 0 );
		
		$result = $this->import_lab_data( $file, $provider, $user_id );
		
		wp_send_json( $result );
	}
	
	/**
	 * Handle biomarker CSV import
	 */
	public function handle_biomarker_csv_import() {
		// Verify nonce and permissions
		if ( ! $this->verify_admin_permissions() ) {
			wp_die( 'Unauthorized' );
		}
		
		$file = $_FILES['biomarker_csv'] ?? null;
		$user_id = absint( $_POST['user_id'] ?? 0 );
		
		$result = $this->import_biomarker_csv( $file, $user_id );
		
		wp_send_json( $result );
	}
	
	/**
	 * Handle user CSV import
	 */
	public function handle_user_csv_import() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ennu_unified_import_frontend_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}
		
		$file = $_FILES['user_csv'] ?? null;
		$user_id = get_current_user_id();
		
		$result = $this->import_user_csv( $file, $user_id );
		
		wp_send_json( $result );
	}
	
	/**
	 * Import lab data
	 */
	private function import_lab_data( $file, $provider, $user_id ) {
		try {
			// Validate file
			$validation = $this->validate_import_file( $file, 'lab_data' );
			if ( ! $validation['valid'] ) {
				return array(
					'success' => false,
					'message' => 'File validation failed',
					'errors' => $validation['errors'],
				);
			}
			
			// Process lab data import
			$import_result = $this->process_lab_data_import( $file['tmp_name'], $provider, $user_id );
			
			// Log import
			$this->log_import( 'lab_data', $user_id, $import_result );
			
			return $import_result;
			
		} catch ( Exception $e ) {
			error_log( 'ENNU Unified Import Service Error: ' . $e->getMessage() );
			return array(
				'success' => false,
				'message' => $e->getMessage(),
				'errors' => array( $e->getMessage() ),
			);
		}
	}
	
	/**
	 * Import biomarker CSV
	 */
	private function import_biomarker_csv( $file, $user_id ) {
		try {
			// Validate file
			$validation = $this->validate_import_file( $file, 'biomarker_csv' );
			if ( ! $validation['valid'] ) {
				return array(
					'success' => false,
					'message' => 'File validation failed',
					'errors' => $validation['errors'],
				);
			}
			
			// Process biomarker CSV import
			$import_result = $this->process_biomarker_csv_import( $file['tmp_name'], $user_id );
			
			// Log import
			$this->log_import( 'biomarker_csv', $user_id, $import_result );
			
			return $import_result;
			
		} catch ( Exception $e ) {
			error_log( 'ENNU Unified Import Service Error: ' . $e->getMessage() );
			return array(
				'success' => false,
				'message' => $e->getMessage(),
				'errors' => array( $e->getMessage() ),
			);
		}
	}
	
	/**
	 * Import user CSV
	 */
	private function import_user_csv( $file, $user_id ) {
		try {
			// Validate file
			$validation = $this->validate_import_file( $file, 'user_csv' );
			if ( ! $validation['valid'] ) {
				return array(
					'success' => false,
					'message' => 'File validation failed',
					'errors' => $validation['errors'],
				);
			}
			
			// Process user CSV import
			$import_result = $this->process_user_csv_import( $file['tmp_name'], $user_id );
			
			// Log import
			$this->log_import( 'user_csv', $user_id, $import_result );
			
			return $import_result;
			
		} catch ( Exception $e ) {
			error_log( 'ENNU Unified Import Service Error: ' . $e->getMessage() );
			return array(
				'success' => false,
				'message' => $e->getMessage(),
				'errors' => array( $e->getMessage() ),
			);
		}
	}
	
	/**
	 * Validate import file
	 */
	private function validate_import_file( $file, $import_type ) {
		$errors = array();
		
		// Check if file exists
		if ( ! $file || ! isset( $file['tmp_name'] ) || ! file_exists( $file['tmp_name'] ) ) {
			$errors[] = 'No file uploaded or file not found';
			return array( 'valid' => false, 'errors' => $errors );
		}
		
		// Check file size
		$max_size = 5 * 1024 * 1024; // 5MB
		if ( $file['size'] > $max_size ) {
			$errors[] = 'File size exceeds maximum allowed size (5MB)';
		}
		
		// Check file type
		$allowed_types = $this->supported_types[ $import_type ]['file_formats'] ?? array( 'csv' );
		$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		
		if ( ! in_array( $file_extension, $allowed_types ) ) {
			$errors[] = 'File type not supported. Allowed types: ' . implode( ', ', $allowed_types );
		}
		
		// Check for upload errors
		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			$errors[] = 'File upload error: ' . $file['error'];
		}
		
		return array(
			'valid' => empty( $errors ),
			'errors' => $errors,
		);
	}
	
	/**
	 * Process lab data import
	 */
	private function process_lab_data_import( $file_path, $provider, $user_id ) {
		// Use the existing lab import manager functionality
		if ( class_exists( 'ENNU_Lab_Import_Manager' ) ) {
			$lab_import_manager = new ENNU_Lab_Import_Manager();
			return $lab_import_manager->import_lab_results( $user_id, $file_path, $provider );
		}
		
		// Fallback to basic CSV processing
		return $this->process_basic_csv_import( $file_path, $user_id );
	}
	
	/**
	 * Process biomarker CSV import
	 */
	private function process_biomarker_csv_import( $file_path, $user_id ) {
		$csv_data = $this->parse_csv_file( $file_path );
		$imported_count = 0;
		$errors = array();
		
		foreach ( $csv_data as $row_index => $row ) {
			try {
				$biomarker_data = $this->parse_biomarker_row( $row );
				$result = $this->biomarker_service->save_biomarker( $biomarker_data );
				
				if ( $result['success'] ) {
					$imported_count++;
				} else {
					$errors[] = "Row " . ( $row_index + 1 ) . ": " . implode( ', ', $result['errors'] );
				}
			} catch ( Exception $e ) {
				$errors[] = "Row " . ( $row_index + 1 ) . ": " . $e->getMessage();
			}
		}
		
		return array(
			'success' => $imported_count > 0,
			'imported' => $imported_count,
			'errors' => $errors,
			'message' => "Imported {$imported_count} biomarkers successfully",
		);
	}
	
	/**
	 * Process user CSV import
	 */
	private function process_user_csv_import( $file_path, $user_id ) {
		$csv_data = $this->parse_csv_file( $file_path );
		$imported_count = 0;
		$errors = array();
		
		foreach ( $csv_data as $row_index => $row ) {
			try {
				$biomarker_data = $this->parse_biomarker_row( $row );
				$biomarker_data['user_id'] = $user_id;
				
				$result = $this->biomarker_service->save_biomarker( $biomarker_data );
				
				if ( $result['success'] ) {
					$imported_count++;
				} else {
					$errors[] = "Row " . ( $row_index + 1 ) . ": " . implode( ', ', $result['errors'] );
				}
			} catch ( Exception $e ) {
				$errors[] = "Row " . ( $row_index + 1 ) . ": " . $e->getMessage();
			}
		}
		
		return array(
			'success' => $imported_count > 0,
			'imported' => $imported_count,
			'errors' => $errors,
			'message' => "Imported {$imported_count} biomarkers successfully",
		);
	}
	
	/**
	 * Parse CSV file
	 */
	private function parse_csv_file( $file_path ) {
		$data = array();
		
		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			$header = fgetcsv( $handle );
			
			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				if ( count( $row ) === count( $header ) ) {
					$data[] = array_combine( $header, $row );
				}
			}
			
			fclose( $handle );
		}
		
		return $data;
	}
	
	/**
	 * Parse biomarker row
	 */
	private function parse_biomarker_row( $row ) {
		$biomarker_data = array(
			'name' => sanitize_text_field( $row['biomarker_name'] ?? $row['name'] ?? '' ),
			'value' => floatval( $row['value'] ?? 0 ),
			'unit' => sanitize_text_field( $row['unit'] ?? '' ),
			'date' => sanitize_text_field( $row['date'] ?? $row['test_date'] ?? date( 'Y-m-d' ) ),
			'reference_range' => sanitize_text_field( $row['reference_range'] ?? '' ),
		);
		
		// Validate required fields
		if ( empty( $biomarker_data['name'] ) ) {
			throw new Exception( 'Biomarker name is required' );
		}
		
		if ( empty( $biomarker_data['value'] ) ) {
			throw new Exception( 'Biomarker value is required' );
		}
		
		return $biomarker_data;
	}
	
	/**
	 * Process basic CSV import (fallback)
	 */
	private function process_basic_csv_import( $file_path, $user_id ) {
		$csv_data = $this->parse_csv_file( $file_path );
		$imported_count = 0;
		$errors = array();
		
		foreach ( $csv_data as $row_index => $row ) {
			try {
				$biomarker_data = $this->parse_biomarker_row( $row );
				$biomarker_data['user_id'] = $user_id;
				
				$result = $this->biomarker_service->save_biomarker( $biomarker_data );
				
				if ( $result['success'] ) {
					$imported_count++;
				} else {
					$errors[] = "Row " . ( $row_index + 1 ) . ": " . implode( ', ', $result['errors'] );
				}
			} catch ( Exception $e ) {
				$errors[] = "Row " . ( $row_index + 1 ) . ": " . $e->getMessage();
			}
		}
		
		return array(
			'success' => $imported_count > 0,
			'imported' => $imported_count,
			'errors' => $errors,
			'message' => "Imported {$imported_count} records successfully",
		);
	}
	
	/**
	 * Log import activity
	 */
	private function log_import( $import_type, $user_id, $result ) {
		$log_data = array(
			'import_type' => $import_type,
			'user_id' => $user_id,
			'success' => $result['success'] ?? false,
			'imported_count' => $result['imported'] ?? 0,
			'errors' => $result['errors'] ?? array(),
			'timestamp' => current_time( 'mysql' ),
		);
		
		// REMOVED: error_log( 'ENNU Unified Import Service: ' . json_encode( $log_data ) );
	}
	
	/**
	 * Verify admin permissions
	 */
	private function verify_admin_permissions() {
		return wp_verify_nonce( $_POST['nonce'] ?? '', 'ennu_unified_import_nonce' ) &&
			   current_user_can( 'manage_options' );
	}
	
	/**
	 * Get supported import types
	 */
	public function get_supported_import_types() {
		return $this->supported_types;
	}
	
	/**
	 * Get import type configuration
	 */
	public function get_import_type_config( $type ) {
		return $this->supported_types[ $type ] ?? null;
	}
	
	/**
	 * Render login message
	 */
	private function render_login_message() {
		return '<div class="ennu-login-required"><p>' . __( 'Please log in to import your biomarker data.', 'ennulifeassessments' ) . '</p></div>';
	}
} 