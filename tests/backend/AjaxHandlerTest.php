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
        add_action('wp_ajax_ennu_submit_assessment', array($this->shortcodes_instance, 'handle_assessment_submission'));
        add_action('wp_ajax_nopriv_ennu_submit_assessment', array($this->shortcodes_instance, 'handle_assessment_submission'));
    }

    /**
     * Test the entire submission process for a new user.
     */
    public function test_handle_successful_submission_for_new_user() {
        // 1. Set up the POST data to simulate a form submission.
        $_POST = [
            'action' => 'ennu_submit_assessment',
            'nonce' => wp_create_nonce('ennu_ajax_nonce'),
            'assessment_type' => 'hair_assessment',
            'contact_info' => [
                'first_name' => 'Jane',
                'last_name' => 'Tester',
                'email' => 'jane.tester@example.com'
            ],
            'hair_q1' => 'female', // gender -> 1 point
            'hair_q2' => 'thinning', // hair_concerns -> 2 points
        ];

        try {
            // 2. Execute the AJAX handler.
            $this->_handleAjax('ennu_submit_assessment');
        } catch (WPAjaxDieContinueException $e) {
            // This exception is expected and allows the test to continue.
        }

        // 3. Decode the JSON response.
        $response = json_decode($this->_last_response, true);

        // 4. Assert that the AJAX call was successful.
        $this->assertTrue($response['success'], 'The AJAX response should indicate success.');
        $this->assertArrayHasKey('redirect_url', $response['data'], 'The response should contain a redirect URL.');
        $this->assertNotEmpty($response['data']['redirect_url'], 'The redirect URL should not be empty.');

        // 5. Verify the results in the database.
        $user = get_user_by('email', 'jane.tester@example.com');
        $this->assertInstanceOf('WP_User', $user, 'A new user should have been created.');

        // Verify answers were saved.
        $saved_gender = get_user_meta($user->ID, 'ennu_hair_assessment_hair_q1', true);
        $this->assertEquals('female', $saved_gender);

        $saved_concern = get_user_meta($user->ID, 'ennu_hair_assessment_hair_q2', true);
        $this->assertEquals('thinning', $saved_concern);

        // Verify score was calculated and saved.
        $saved_score = get_user_meta($user->ID, 'ennu_hair_assessment_overall_score', true);
        $this->assertEquals(3, $saved_score, 'The score should be 1 + 2 = 3.');
    }
} 