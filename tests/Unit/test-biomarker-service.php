<?php
/**
 * Unit Tests for ENNU Biomarker Service
 *
 * @package ENNU_Life_Assessments
 * @since 64.11.0
 */

/**
 * Test class for ENNU Biomarker Service
 */
class Test_ENNU_Biomarker_Service extends ENNU_TestCase {
	
	/**
	 * Test biomarker service initialization
	 */
	public function test_biomarker_service_initialization() {
		// Test that the service class exists
		$this->assertTrue( class_exists( 'ENNU_Biomarker_Service' ) );
		
		// Test service instantiation
		$service = new ENNU_Biomarker_Service();
		$this->assertInstanceOf( 'ENNU_Biomarker_Service', $service );
	}
	
	/**
	 * Test biomarker validation
	 */
	public function test_biomarker_validation() {
		$service = new ENNU_Biomarker_Service();
		
		// Test valid biomarker data
		$valid_biomarker = array(
			'name' => 'Testosterone',
			'value' => '500',
			'unit' => 'ng/dL',
			'reference_range' => '300-1000',
			'category' => 'Hormones',
		);
		
		$result = $service->validate_biomarker( $valid_biomarker );
		$this->assertTrue( $result['valid'] );
		
		// Test invalid biomarker data
		$invalid_biomarker = array(
			'name' => '',
			'value' => 'invalid',
			'unit' => '',
			'reference_range' => 'invalid',
			'category' => '',
		);
		
		$result = $service->validate_biomarker( $invalid_biomarker );
		$this->assertFalse( $result['valid'] );
		$this->assertNotEmpty( $result['errors'] );
	}
	
	/**
	 * Test biomarker reference range checking
	 */
	public function test_biomarker_reference_range() {
		$service = new ENNU_Biomarker_Service();
		
		// Test normal range
		$biomarker = array(
			'name' => 'Testosterone',
			'value' => '500',
			'unit' => 'ng/dL',
			'reference_range' => '300-1000',
		);
		
		$status = $service->check_reference_range( $biomarker );
		$this->assertEquals( 'normal', $status );
		
		// Test low range
		$biomarker['value'] = '250';
		$status = $service->check_reference_range( $biomarker );
		$this->assertEquals( 'low', $status );
		
		// Test high range
		$biomarker['value'] = '1100';
		$status = $service->check_reference_range( $biomarker );
		$this->assertEquals( 'high', $status );
	}
	
	/**
	 * Test biomarker category classification
	 */
	public function test_biomarker_category_classification() {
		$service = new ENNU_Biomarker_Service();
		
		$categories = array(
			'Testosterone' => 'Hormones',
			'Glucose' => 'Metabolic',
			'Cholesterol' => 'Lipids',
			'Blood Pressure' => 'Cardiovascular',
		);
		
		foreach ( $categories as $biomarker_name => $expected_category ) {
			$category = $service->classify_biomarker( $biomarker_name );
			$this->assertEquals( $expected_category, $category );
		}
	}
	
	/**
	 * Test biomarker data persistence
	 */
	public function test_biomarker_data_persistence() {
		$service = new ENNU_Biomarker_Service();
		
		$biomarker_data = array(
			'user_id' => $this->test_user_id,
			'name' => 'Testosterone',
			'value' => '500',
			'unit' => 'ng/dL',
			'reference_range' => '300-1000',
			'category' => 'Hormones',
		);
		
		// Test saving biomarker
		$result = $service->save_biomarker( $biomarker_data );
		$this->assertTrue( $result['success'] );
		$this->assertNotEmpty( $result['id'] );
		
		// Test retrieving biomarker
		$retrieved = $service->get_biomarker( $result['id'] );
		$this->assertEquals( $biomarker_data['name'], $retrieved['name'] );
		$this->assertEquals( $biomarker_data['value'], $retrieved['value'] );
	}
	
	/**
	 * Test biomarker data retrieval by user
	 */
	public function test_get_user_biomarkers() {
		$service = new ENNU_Biomarker_Service();
		
		// Create multiple biomarkers for the test user
		$biomarkers = array(
			array(
				'user_id' => $this->test_user_id,
				'name' => 'Testosterone',
				'value' => '500',
				'unit' => 'ng/dL',
				'reference_range' => '300-1000',
				'category' => 'Hormones',
			),
			array(
				'user_id' => $this->test_user_id,
				'name' => 'Glucose',
				'value' => '95',
				'unit' => 'mg/dL',
				'reference_range' => '70-100',
				'category' => 'Metabolic',
			),
		);
		
		// Save biomarkers
		foreach ( $biomarkers as $biomarker ) {
			$service->save_biomarker( $biomarker );
		}
		
		// Test retrieving user biomarkers
		$user_biomarkers = $service->get_user_biomarkers( $this->test_user_id );
		$this->assertCount( 2, $user_biomarkers );
		
		// Verify biomarker names
		$names = array_column( $user_biomarkers, 'name' );
		$this->assertContains( 'Testosterone', $names );
		$this->assertContains( 'Glucose', $names );
	}
	
	/**
	 * Test biomarker data update
	 */
	public function test_update_biomarker() {
		$service = new ENNU_Biomarker_Service();
		
		// Create initial biomarker
		$biomarker_data = array(
			'user_id' => $this->test_user_id,
			'name' => 'Testosterone',
			'value' => '500',
			'unit' => 'ng/dL',
			'reference_range' => '300-1000',
			'category' => 'Hormones',
		);
		
		$result = $service->save_biomarker( $biomarker_data );
		$biomarker_id = $result['id'];
		
		// Update biomarker
		$update_data = array(
			'value' => '600',
			'unit' => 'ng/dL',
		);
		
		$update_result = $service->update_biomarker( $biomarker_id, $update_data );
		$this->assertTrue( $update_result['success'] );
		
		// Verify update
		$updated_biomarker = $service->get_biomarker( $biomarker_id );
		$this->assertEquals( '600', $updated_biomarker['value'] );
	}
	
	/**
	 * Test biomarker data deletion
	 */
	public function test_delete_biomarker() {
		$service = new ENNU_Biomarker_Service();
		
		// Create biomarker
		$biomarker_data = array(
			'user_id' => $this->test_user_id,
			'name' => 'Testosterone',
			'value' => '500',
			'unit' => 'ng/dL',
			'reference_range' => '300-1000',
			'category' => 'Hormones',
		);
		
		$result = $service->save_biomarker( $biomarker_data );
		$biomarker_id = $result['id'];
		
		// Delete biomarker
		$delete_result = $service->delete_biomarker( $biomarker_id );
		$this->assertTrue( $delete_result['success'] );
		
		// Verify deletion
		$deleted_biomarker = $service->get_biomarker( $biomarker_id );
		$this->assertNull( $deleted_biomarker );
	}
} 