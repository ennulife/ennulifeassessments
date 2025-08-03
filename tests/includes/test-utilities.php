<?php
/**
 * Test Utilities for ENNU Life Assessments Plugin
 *
 * @package ENNU_Life_Assessments
 * @since 64.11.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Test Environment Class
 *
 * Handles test environment setup and teardown.
 */
class ENNU_Test_Environment {
	
	/**
	 * Initialize the test environment
	 */
	public static function init() {
		// Set up test data
		self::setup_test_data();
		
		// Register cleanup hooks
		add_action( 'shutdown', array( __CLASS__, 'cleanup' ) );
	}
	
	/**
	 * Set up test data
	 */
	private static function setup_test_data() {
		// Create test users if they don't exist
		if ( ! get_user_by( 'login', 'test_admin' ) ) {
			wp_insert_user( array(
				'user_login' => 'test_admin',
				'user_pass'  => 'test_password',
				'user_email' => 'test@ennu.life',
				'role'       => 'administrator',
			) );
		}
		
		// Create test assessment data
		self::create_test_assessments();
	}
	
	/**
	 * Create test assessment data
	 */
	private static function create_test_assessments() {
		global $wpdb;
		
		// Test biomarker data
		$test_biomarkers = array(
			array(
				'name' => 'Testosterone',
				'value' => '500',
				'unit' => 'ng/dL',
				'reference_range' => '300-1000',
				'category' => 'Hormones',
			),
			array(
				'name' => 'Glucose',
				'value' => '95',
				'unit' => 'mg/dL',
				'reference_range' => '70-100',
				'category' => 'Metabolic',
			),
		);
		
		// Store test data in options for tests to access
		update_option( 'ennu_test_biomarkers', $test_biomarkers );
	}
	
	/**
	 * Clean up test data
	 */
	public static function cleanup() {
		// Remove test users
		$test_user = get_user_by( 'login', 'test_admin' );
		if ( $test_user ) {
			wp_delete_user( $test_user->ID );
		}
		
		// Clean up test options
		delete_option( 'ennu_test_biomarkers' );
	}
}

/**
 * Base Test Case for ENNU Plugin
 */
abstract class ENNU_TestCase extends WP_UnitTestCase {
	
	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Reset plugin state
		$this->reset_plugin_state();
		
		// Set up test data
		$this->setup_test_data();
	}
	
	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up test data
		$this->cleanup_test_data();
		
		parent::tearDown();
	}
	
	/**
	 * Reset plugin state
	 */
	protected function reset_plugin_state() {
		// Clear any cached data
		wp_cache_flush();
		
		// Reset plugin options to defaults
		$this->reset_plugin_options();
	}
	
	/**
	 * Reset plugin options to defaults
	 */
	protected function reset_plugin_options() {
		// Reset core options
		update_option( 'ennu_assessment_enabled', true );
		update_option( 'ennu_debug_mode', true );
		update_option( 'ennu_test_mode', true );
	}
	
	/**
	 * Set up test data
	 */
	protected function setup_test_data() {
		// Create test user
		$this->test_user_id = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		
		// Create test assessment
		$this->test_assessment_id = $this->create_test_assessment();
	}
	
	/**
	 * Create a test assessment
	 */
	protected function create_test_assessment() {
		$assessment_data = array(
			'user_id' => $this->test_user_id,
			'assessment_type' => 'comprehensive',
			'biomarkers' => array(
				'testosterone' => array(
					'value' => '500',
					'unit' => 'ng/dL',
					'reference_range' => '300-1000',
				),
			),
			'symptoms' => array(
				'fatigue' => true,
				'low_libido' => false,
			),
			'created_at' => current_time( 'mysql' ),
		);
		
		return wp_insert_post( array(
			'post_type' => 'ennu_assessment',
			'post_title' => 'Test Assessment',
			'post_status' => 'publish',
			'meta_input' => $assessment_data,
		) );
	}
	
	/**
	 * Clean up test data
	 */
	protected function cleanup_test_data() {
		// Remove test assessment
		if ( isset( $this->test_assessment_id ) ) {
			wp_delete_post( $this->test_assessment_id, true );
		}
		
		// Remove test user
		if ( isset( $this->test_user_id ) ) {
			wp_delete_user( $this->test_user_id );
		}
	}
	
	/**
	 * Assert that a biomarker value is within reference range
	 */
	protected function assertBiomarkerInRange( $value, $reference_range ) {
		list( $min, $max ) = explode( '-', $reference_range );
		$this->assertGreaterThanOrEqual( $min, $value );
		$this->assertLessThanOrEqual( $max, $value );
	}
	
	/**
	 * Assert that an assessment has the expected structure
	 */
	protected function assertAssessmentStructure( $assessment ) {
		$this->assertIsArray( $assessment );
		$this->assertArrayHasKey( 'user_id', $assessment );
		$this->assertArrayHasKey( 'assessment_type', $assessment );
		$this->assertArrayHasKey( 'biomarkers', $assessment );
		$this->assertArrayHasKey( 'symptoms', $assessment );
		$this->assertArrayHasKey( 'created_at', $assessment );
	}
}

/**
 * Integration Test Case for ENNU Plugin
 */
abstract class ENNU_Integration_TestCase extends ENNU_TestCase {
	
	/**
	 * Set up integration test environment
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Set up database tables
		$this->setup_database_tables();
		
		// Initialize plugin services
		$this->initialize_plugin_services();
	}
	
	/**
	 * Set up database tables
	 */
	protected function setup_database_tables() {
		global $wpdb;
		
		// Create custom tables if they don't exist
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ennu_biomarkers (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			name varchar(255) NOT NULL,
			value decimal(10,2) NOT NULL,
			unit varchar(50) NOT NULL,
			reference_range varchar(100) NOT NULL,
			category varchar(100) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY category (category)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
	
	/**
	 * Initialize plugin services
	 */
	protected function initialize_plugin_services() {
		// Initialize core services
		if ( class_exists( 'ENNU_Biomarker_Service' ) ) {
			$this->biomarker_service = new ENNU_Biomarker_Service();
		}
		
		if ( class_exists( 'ENNU_Assessment_Service' ) ) {
			$this->assessment_service = new ENNU_Assessment_Service();
		}
		
		if ( class_exists( 'ENNU_Scoring_Service' ) ) {
			$this->scoring_service = new ENNU_Scoring_Service();
		}
	}
}

/**
 * Test Listener for ENNU Plugin
 */
class ENNU_Test_Listener implements PHPUnit\Framework\TestListener {
	
	/**
	 * Log file path
	 */
	private $log_file;
	
	/**
	 * Constructor
	 */
	public function __construct( array $options = array() ) {
		$this->log_file = isset( $options['log_file'] ) ? $options['log_file'] : 'tests/logs/test-results.log';
	}
	
	/**
	 * Log test results
	 */
	private function log( $message ) {
		$timestamp = date( 'Y-m-d H:i:s' );
		$log_entry = "[{$timestamp}] {$message}" . PHP_EOL;
		file_put_contents( $this->log_file, $log_entry, FILE_APPEND | LOCK_EX );
	}
	
	/**
	 * Test started
	 */
	public function startTest( PHPUnit\Framework\Test $test ): void {
		$this->log( "START: " . $test->getName() );
	}
	
	/**
	 * Test ended
	 */
	public function endTest( PHPUnit\Framework\Test $test, float $time ): void {
		$this->log( "END: " . $test->getName() . " (Time: {$time}s)" );
	}
	
	/**
	 * Test failed
	 */
	public function addFailure( PHPUnit\Framework\Test $test, PHPUnit\Framework\AssertionFailedError $e, float $time ): void {
		$this->log( "FAILURE: " . $test->getName() . " - " . $e->getMessage() );
	}
	
	/**
	 * Test error
	 */
	public function addError( PHPUnit\Framework\Test $test, Throwable $e, float $time ): void {
		$this->log( "ERROR: " . $test->getName() . " - " . $e->getMessage() );
	}
	
	/**
	 * Test skipped
	 */
	public function addSkippedTest( PHPUnit\Framework\Test $test, Throwable $e, float $time ): void {
		$this->log( "SKIPPED: " . $test->getName() . " - " . $e->getMessage() );
	}
	
	/**
	 * Test incomplete
	 */
	public function addIncompleteTest( PHPUnit\Framework\Test $test, Throwable $e, float $time ): void {
		$this->log( "INCOMPLETE: " . $test->getName() . " - " . $e->getMessage() );
	}
	
	/**
	 * Test warning
	 */
	public function addWarning( PHPUnit\Framework\Test $test, PHPUnit\Framework\Warning $e, float $time ): void {
		$this->log( "WARNING: " . $test->getName() . " - " . $e->getMessage() );
	}
	
	/**
	 * Test suite started
	 */
	public function startTestSuite( PHPUnit\Framework\TestSuite $suite ): void {
		$this->log( "SUITE START: " . $suite->getName() );
	}
	
	/**
	 * Test suite ended
	 */
	public function endTestSuite( PHPUnit\Framework\TestSuite $suite ): void {
		$this->log( "SUITE END: " . $suite->getName() );
	}
	
	/**
	 * Risky test
	 */
	public function addRiskyTest( PHPUnit\Framework\Test $test, Throwable $e, float $time ): void {
		$this->log( "RISKY: " . $test->getName() . " - " . $e->getMessage() );
	}
} 