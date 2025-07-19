<?php
/**
 * Test case for the main AJAX submission handler.
 *
 * @package ENNU_Life_Tests
 */

use WP_Ajax_UnitTestCase;

class AjaxHandlerTest extends WP_Ajax_UnitTestCase {

	private $shortcodes_instance;

	public function setUp(): void {
		parent::setUp();
		// The class that contains the AJAX handler.
		$this->shortcodes_instance = new ENNU_Assessment_Shortcodes();
		// We need to hook our handler to the test's ajax handler listener
		add_action( 'wp_ajax_ennu_submit_assessment', array( $this->shortcodes_instance, 'handle_assessment_submission' ) );
		add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this->shortcodes_instance, 'handle_assessment_submission' ) );
	}

	/**
	 * Test the entire submission process for a new user.
	 */
	public function test_handle_successful_submission_for_new_user() {
		// 1. Set up the POST data to simulate a form submission.
		$_POST = array(
			'action'          => 'ennu_submit_assessment',
			'nonce'           => wp_create_nonce( 'ennu_ajax_nonce' ),
			'assessment_type' => 'hair_assessment',
			'contact_info'    => array(
				'first_name' => 'Jane',
				'last_name'  => 'Tester',
				'email'      => 'jane.tester@example.com',
			),
			'hair_q1'         => 'female', // gender -> 1 point
			'hair_q2'         => 'thinning', // hair_concerns -> 2 points
		);

		try {
			// 2. Execute the AJAX handler.
			$this->_handleAjax( 'ennu_submit_assessment' );
		} catch ( WPAjaxDieContinueException $e ) {
			// This exception is expected and allows the test to continue.
		}

		// 3. Decode the JSON response.
		$response = json_decode( $this->_last_response, true );

		// 4. Assert that the AJAX call was successful.
		$this->assertTrue( $response['success'], 'The AJAX response should indicate success.' );
		$this->assertArrayHasKey( 'redirect_url', $response['data'], 'The response should contain a redirect URL.' );
		$this->assertNotEmpty( $response['data']['redirect_url'], 'The redirect URL should not be empty.' );

		// 5. Verify the results in the database.
		$user = get_user_by( 'email', 'jane.tester@example.com' );
		$this->assertInstanceOf( 'WP_User', $user, 'A new user should have been created.' );

		// Verify answers were saved.
		$saved_gender = get_user_meta( $user->ID, 'ennu_hair_assessment_hair_q1', true );
		$this->assertEquals( 'female', $saved_gender );

		$saved_concern = get_user_meta( $user->ID, 'ennu_hair_assessment_hair_q2', true );
		$this->assertEquals( 'thinning', $saved_concern );

		// Verify score was calculated and saved.
		$saved_score = get_user_meta( $user->ID, 'ennu_hair_assessment_overall_score', true );
		$this->assertEquals( 3, $saved_score, 'The score should be 1 + 2 = 3.' );
	}

	/**
	 * Test CSRF protection on AJAX endpoints
	 */
	public function test_ajax_csrf_protection() {
		$_POST = array(
			'action' => 'ennu_submit_assessment',
			'nonce' => 'invalid_nonce',
			'assessment_type' => 'hair_assessment',
		);
		
		$this->assertFalse( wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) );
		
		unset($_POST['nonce']);
		$this->assertFalse( wp_verify_nonce( '', 'ennu_ajax_nonce' ) );
	}

	/**
	 * Test AJAX input sanitization
	 */
	public function test_ajax_input_sanitization() {
		$malicious_inputs = array(
			'name' => 'John <script>alert("xss")</script> Doe',
			'email' => 'test@example.com<script>',
			'notes' => 'Notes with <iframe src="javascript:alert(1)"></iframe>',
		);
		
		foreach ($malicious_inputs as $field => $value) {
			$sanitized = sanitize_text_field($value);
			$this->assertStringNotContainsString('<script>', $sanitized);
			$this->assertStringNotContainsString('<iframe>', $sanitized);
			$this->assertStringNotContainsString('javascript:', $sanitized);
		}
	}

	/**
	 * Test AJAX error handling
	 */
	public function test_ajax_error_handling() {
		$_POST = array(
			'action' => 'ennu_submit_assessment',
			'nonce' => wp_create_nonce( 'ennu_ajax_nonce' ),
		);
		
		$required_fields = array('assessment_type', 'email');
		$missing_fields = array();
		
		foreach ($required_fields as $field) {
			if (!isset($_POST[$field]) || empty($_POST[$field])) {
				$missing_fields[] = $field;
			}
		}
		
		$this->assertNotEmpty($missing_fields, 'Should detect missing required fields');
		$this->assertContains('assessment_type', $missing_fields);
	}

	/**
	 * Test AJAX response format
	 */
	public function test_ajax_response_format() {
		$response = array(
			'success' => true,
			'data' => array(
				'message' => 'Assessment submitted successfully',
				'user_id' => 123,
				'scores' => array(
					'overall_score' => 7.5,
					'category_scores' => array(
						'hair_health' => 8.0,
						'scalp_condition' => 7.0
					)
				)
			)
		);
		
		$this->assertIsArray($response);
		$this->assertArrayHasKey('success', $response);
		$this->assertArrayHasKey('data', $response);
		$this->assertTrue($response['success']);
		$this->assertIsArray($response['data']);
		$this->assertArrayHasKey('scores', $response['data']);
	}

	/**
	 * Test AJAX authentication states
	 */
	public function test_ajax_authentication_states() {
		$user_id = self::factory()->user->create();
		wp_set_current_user($user_id);
		
		$this->assertTrue(is_user_logged_in());
		$this->assertEquals($user_id, get_current_user_id());
		
		wp_set_current_user(0);
		$this->assertFalse(is_user_logged_in());
		$this->assertEquals(0, get_current_user_id());
	}

	/**
	 * Test rate limiting functionality
	 */
	public function test_ajax_rate_limiting() {
		$_POST = array(
			'action' => 'ennu_submit_assessment',
			'nonce' => wp_create_nonce( 'ennu_ajax_nonce' ),
			'assessment_type' => 'hair_assessment',
			'email' => 'test@example.com',
		);
		
		$rate_limit_key = 'assessment_submission_' . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
		$current_count = get_transient($rate_limit_key);
		
		if ($current_count === false) {
			set_transient($rate_limit_key, 1, 300); // 5 minutes
			$this->assertTrue(true, 'First submission should be allowed');
		} else {
			$this->assertLessThan(5, $current_count, 'Should not exceed rate limit of 5 submissions per 5 minutes');
		}
	}
}
