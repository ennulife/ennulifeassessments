<?php
/**
 * Test ENNU AJAX Security
 */

class Test_ENNU_AJAX_Security extends WP_UnitTestCase {

	public function test_nonce_verification() {
		$security = new ENNU_AJAX_Security();

		$_POST['nonce']  = wp_create_nonce( 'ennu_ajax_nonce' );
		$_POST['action'] = 'ennu_test_action';

		$result = $security->validate_ajax_request(
			array(
				'action'     => 'ennu_test_action',
				'capability' => 'read',
			)
		);

		$this->assertNotInstanceOf( 'WP_Error', $result );
	}

	public function test_rate_limiting() {
		$security = new ENNU_AJAX_Security();

		$_POST['nonce']  = wp_create_nonce( 'ennu_ajax_nonce' );
		$_POST['action'] = 'ennu_test_action';

		for ( $i = 0; $i < 15; $i++ ) {
			$result = $security->validate_ajax_request(
				array(
					'action'     => 'ennu_test_action',
					'capability' => 'read',
					'rate_limit' => array(
						'requests' => 10,
						'window'   => 60,
					),
				)
			);

			if ( $i >= 10 ) {
				$this->assertInstanceOf( 'WP_Error', $result );
				$this->assertEquals( 'RATE_LIMIT_EXCEEDED', $result->get_error_code() );
				break;
			}
		}
	}

	public function test_capability_check() {
		$security = new ENNU_AJAX_Security();

		$_POST['nonce']  = wp_create_nonce( 'ennu_ajax_nonce' );
		$_POST['action'] = 'ennu_test_action';

		$result = $security->validate_ajax_request(
			array(
				'action'     => 'ennu_test_action',
				'capability' => 'manage_options',
			)
		);

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'INSUFFICIENT_PERMISSIONS', $result->get_error_code() );
	}
}
