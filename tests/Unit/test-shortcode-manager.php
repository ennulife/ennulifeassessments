<?php
/**
 * Unit Tests for ENNU Shortcode Manager
 *
 * @package ENNU_Life_Assessments
 * @since 64.12.0
 */

/**
 * Test class for ENNU Shortcode Manager
 */
class Test_ENNU_Shortcode_Manager extends ENNU_TestCase {
	
	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->reset_plugin_state();
	}
	
	/**
	 * Test shortcode manager initialization
	 */
	public function test_shortcode_manager_initialization() {
		// Test that the manager class exists
		$this->assertTrue( class_exists( 'ENNU_Shortcode_Manager' ) );
		
		// Test manager instantiation
		$manager = new ENNU_Shortcode_Manager();
		$this->assertInstanceOf( 'ENNU_Shortcode_Manager', $manager );
	}
	
	/**
	 * Test shortcode registration
	 */
	public function test_shortcode_registration() {
		// Get all registered shortcodes
		global $shortcode_tags;
		
		// Check that core shortcodes are registered
		$core_shortcodes = array(
			'ennu-user-dashboard',
			'ennu-assessment-results',
			'ennu-assessments',
			'scorepresentation',
			'ennu-biomarkers'
		);
		
		foreach ( $core_shortcodes as $shortcode ) {
			$this->assertArrayHasKey( $shortcode, $shortcode_tags, "Shortcode {$shortcode} should be registered" );
		}
	}
	
	/**
	 * Test assessment shortcode rendering
	 */
	public function test_assessment_shortcode_rendering() {
		$manager = new ENNU_Shortcode_Manager();
		
		// Test basic shortcode rendering
		$output = do_shortcode( '[ennu-weight-loss]' );
		$this->assertNotEmpty( $output, 'Weight loss shortcode should render content' );
		$this->assertStringContainsString( 'form', $output, 'Output should contain form elements' );
	}
	
	/**
	 * Test results shortcode rendering
	 */
	public function test_results_shortcode_rendering() {
		// Test results shortcode
		$output = do_shortcode( '[ennu-weight-loss-results]' );
		$this->assertNotEmpty( $output, 'Results shortcode should render content' );
	}
	
	/**
	 * Test dashboard shortcode rendering
	 */
	public function test_dashboard_shortcode_rendering() {
		// Create a test user
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		
		// Test dashboard shortcode
		$output = do_shortcode( '[ennu-user-dashboard]' );
		$this->assertNotEmpty( $output, 'Dashboard shortcode should render content' );
		$this->assertStringContainsString( 'dashboard', $output, 'Output should contain dashboard elements' );
	}
	
	/**
	 * Test shortcode definitions retrieval
	 */
	public function test_shortcode_definitions() {
		$manager = new ENNU_Shortcode_Manager();
		$definitions = $manager->get_all_assessment_definitions();
		
		$this->assertIsArray( $definitions, 'Definitions should be an array' );
		$this->assertNotEmpty( $definitions, 'Definitions should not be empty' );
		
		// Check for expected assessment types
		$expected_assessments = array(
			'weight_loss',
			'hormone',
			'health_optimization',
			'hair',
			'skin'
		);
		
		foreach ( $expected_assessments as $assessment ) {
			$this->assertArrayHasKey( $assessment, $definitions, "Assessment {$assessment} should be defined" );
		}
	}
} 