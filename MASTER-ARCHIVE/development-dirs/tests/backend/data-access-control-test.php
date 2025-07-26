<?php
/**
 * Test case for ENNU_Data_Access_Control class
 *
 * Tests data access permissions, user data filtering,
 * and audit logging functionality.
 *
 * @package ENNU_Life_Tests
 */

use WP_UnitTestCase;

class DataAccessControlTest extends WP_UnitTestCase {

	private $data_access_control;
	private $test_user_id;
	private $admin_user_id;

	public function setUp(): void {
		parent::setUp();

		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-data-access-control.php';
		$this->data_access_control = ENNU_Data_Access_Control::get_instance();

		$this->test_user_id = self::factory()->user->create(
			array(
				'user_email' => 'testuser@example.com',
				'user_login' => 'testuser',
				'role'       => 'subscriber',
			)
		);

		$this->admin_user_id = self::factory()->user->create(
			array(
				'user_email' => 'admin@example.com',
				'user_login' => 'adminuser',
				'role'       => 'administrator',
			)
		);
	}

	/**
	 * Test user data access permissions
	 */
	public function test_user_data_access_permissions() {
		wp_set_current_user( $this->test_user_id );
		$can_access_own = $this->data_access_control->can_access_user_data( $this->test_user_id );
		$this->assertTrue( $can_access_own, 'User should be able to access their own data' );

		$other_user_id    = self::factory()->user->create();
		$can_access_other = $this->data_access_control->can_access_user_data( $other_user_id );
		$this->assertFalse( $can_access_other, 'User should not be able to access other user data' );

		wp_set_current_user( $this->admin_user_id );
		$admin_can_access = $this->data_access_control->can_access_user_data( $this->test_user_id );
		$this->assertTrue( $admin_can_access, 'Admin should be able to access any user data' );
	}

	/**
	 * Test sensitive data filtering
	 */
	public function test_sensitive_data_filtering() {
		$user_data = array(
			'first_name'        => 'John',
			'last_name'         => 'Doe',
			'email'             => 'john.doe@example.com',
			'phone'             => '555-123-4567',
			'ssn'               => '123-45-6789',
			'credit_card'       => '4111-1111-1111-1111',
			'assessment_scores' => array(
				'hair_health' => 7.5,
				'skin_health' => 8.2,
			),
		);

		wp_set_current_user( $this->test_user_id );
		$filtered_data = $this->data_access_control->filter_sensitive_data( $user_data, $this->test_user_id );

		$this->assertEquals( 'John', $filtered_data['first_name'] );
		$this->assertEquals( 'john.doe@example.com', $filtered_data['email'] );
		$this->assertArrayHasKey( 'assessment_scores', $filtered_data );

		if ( isset( $filtered_data['ssn'] ) ) {
			$this->assertStringContainsString( '***', $filtered_data['ssn'] );
		}
		if ( isset( $filtered_data['credit_card'] ) ) {
			$this->assertStringContainsString( '****', $filtered_data['credit_card'] );
		}
	}

	/**
	 * Test data access logging
	 */
	public function test_data_access_logging() {
		wp_set_current_user( $this->test_user_id );

		$access_context = array(
			'user_id'          => $this->test_user_id,
			'accessed_user_id' => $this->test_user_id,
			'data_type'        => 'assessment_scores',
			'action'           => 'view',
		);

		$logged = $this->data_access_control->log_data_access( $access_context );
		$this->assertTrue( $logged, 'Data access should be logged successfully' );

		$recent_logs = $this->data_access_control->get_recent_access_logs( $this->test_user_id, 1 );
		$this->assertIsArray( $recent_logs );
		$this->assertNotEmpty( $recent_logs );
	}

	/**
	 * Test assessment data access control
	 */
	public function test_assessment_data_access_control() {
		update_user_meta(
			$this->test_user_id,
			'ennu_hair_assessment_scores',
			array(
				'overall_score'   => 7.5,
				'category_scores' => array(
					'hair_health'     => 8.0,
					'scalp_condition' => 7.0,
				),
			)
		);

		wp_set_current_user( $this->test_user_id );
		$assessment_data = $this->data_access_control->get_user_assessment_data( $this->test_user_id, 'hair_assessment' );

		$this->assertIsArray( $assessment_data );
		$this->assertArrayHasKey( 'overall_score', $assessment_data );
		$this->assertEquals( 7.5, $assessment_data['overall_score'] );

		$other_user_id         = self::factory()->user->create();
		$other_assessment_data = $this->data_access_control->get_user_assessment_data( $other_user_id, 'hair_assessment' );

		$this->assertFalse( $other_assessment_data, 'User should not access other user assessment data' );
	}

	/**
	 * Test data export functionality
	 */
	public function test_data_export_functionality() {
		update_user_meta( $this->test_user_id, 'ennu_hair_assessment_scores', array( 'overall_score' => 7.5 ) );
		update_user_meta( $this->test_user_id, 'ennu_skin_assessment_scores', array( 'overall_score' => 8.2 ) );
		update_user_meta( $this->test_user_id, 'ennu_user_preferences', array( 'notifications' => true ) );

		wp_set_current_user( $this->test_user_id );

		$exported_data = $this->data_access_control->export_user_data( $this->test_user_id );

		$this->assertIsArray( $exported_data );
		$this->assertArrayHasKey( 'assessments', $exported_data );
		$this->assertArrayHasKey( 'preferences', $exported_data );
		$this->assertArrayHasKey( 'export_date', $exported_data );

		$this->assertArrayHasKey( 'hair_assessment', $exported_data['assessments'] );
		$this->assertArrayHasKey( 'skin_assessment', $exported_data['assessments'] );
	}

	/**
	 * Test data deletion functionality
	 */
	public function test_data_deletion_functionality() {
		update_user_meta( $this->test_user_id, 'ennu_hair_assessment_scores', array( 'overall_score' => 7.5 ) );
		update_user_meta( $this->test_user_id, 'ennu_test_data', 'test_value' );

		wp_set_current_user( $this->test_user_id );

		$deleted = $this->data_access_control->delete_user_assessment_data( $this->test_user_id, 'hair_assessment' );
		$this->assertTrue( $deleted, 'Assessment data should be deleted successfully' );

		$remaining_data = get_user_meta( $this->test_user_id, 'ennu_hair_assessment_scores', true );
		$this->assertEmpty( $remaining_data, 'Assessment data should be removed' );

		$other_data = get_user_meta( $this->test_user_id, 'ennu_test_data', true );
		$this->assertEquals( 'test_value', $other_data, 'Other data should remain intact' );
	}

	/**
	 * Test permission boundary enforcement
	 */
	public function test_permission_boundary_enforcement() {
		$editor_user_id = self::factory()->user->create(
			array(
				'role' => 'editor',
			)
		);

		wp_set_current_user( $editor_user_id );

		$can_view_user_data = $this->data_access_control->can_access_user_data( $this->test_user_id );
		$can_export_data    = $this->data_access_control->can_export_user_data( $this->test_user_id );
		$can_delete_data    = $this->data_access_control->can_delete_user_data( $this->test_user_id );

		$this->assertFalse( $can_view_user_data, 'Editor should not access arbitrary user data' );
		$this->assertFalse( $can_export_data, 'Editor should not export user data' );
		$this->assertFalse( $can_delete_data, 'Editor should not delete user data' );
	}

	/**
	 * Test audit trail functionality
	 */
	public function test_audit_trail_functionality() {
		wp_set_current_user( $this->test_user_id );

		$this->data_access_control->get_user_assessment_data( $this->test_user_id, 'hair_assessment' );
		$this->data_access_control->export_user_data( $this->test_user_id );

		$audit_trail = $this->data_access_control->get_audit_trail( $this->test_user_id );

		$this->assertIsArray( $audit_trail );
		$this->assertGreaterThan( 0, count( $audit_trail ), 'Audit trail should contain entries' );

		$first_entry = $audit_trail[0];
		$this->assertArrayHasKey( 'timestamp', $first_entry );
		$this->assertArrayHasKey( 'user_id', $first_entry );
		$this->assertArrayHasKey( 'action', $first_entry );
		$this->assertArrayHasKey( 'data_type', $first_entry );
	}

	/**
	 * Test data anonymization
	 */
	public function test_data_anonymization() {
		$personal_data = array(
			'first_name' => 'John',
			'last_name'  => 'Doe',
			'email'      => 'john.doe@example.com',
			'phone'      => '555-123-4567',
			'ip_address' => '192.168.1.100',
		);

		$anonymized_data = $this->data_access_control->anonymize_personal_data( $personal_data );

		$this->assertNotEquals( 'John', $anonymized_data['first_name'] );
		$this->assertNotEquals( 'Doe', $anonymized_data['last_name'] );
		$this->assertNotEquals( 'john.doe@example.com', $anonymized_data['email'] );
		$this->assertNotEquals( '555-123-4567', $anonymized_data['phone'] );
		$this->assertNotEquals( '192.168.1.100', $anonymized_data['ip_address'] );

		$this->assertArrayHasKey( 'first_name', $anonymized_data );
		$this->assertArrayHasKey( 'email', $anonymized_data );
		$this->assertIsString( $anonymized_data['first_name'] );
		$this->assertIsString( $anonymized_data['email'] );
	}
}
