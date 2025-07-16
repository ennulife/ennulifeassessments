<?php
/**
 * Base Test Case for ENNU Life Assessments
 * 
 * The ultimate foundation for WordPress plugin testing excellence,
 * crafted by the supreme master of testing methodologies.
 */

class ENNU_Test_Case extends WP_UnitTestCase {

    /**
     * Test user ID
     * @var int
     */
    protected $test_user_id;

    /**
     * Plugin instance
     * @var ENNU_Life_Enhanced_Plugin
     */
    protected $plugin;

    /**
     * Set up test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Get plugin instance
        $this->plugin = ENNU_Life_Enhanced_Plugin::get_instance();
        
        // Create test user
        $this->test_user_id = $this->factory->user->create(
            array(
                'role' => 'subscriber',
                'user_login' => 'ennu_test_user_' . uniqid(),
                'user_email' => 'test_' . uniqid() . '@ennulife.test',
            )
        );
        
        // Set current user
        wp_set_current_user( $this->test_user_id );
    }

    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        // Clean up user meta
        $this->clean_ennu_user_meta( $this->test_user_id );
        
        // Reset current user
        wp_set_current_user( 0 );
        
        parent::tearDown();
    }

    /**
     * Clean all ENNU-related user meta
     */
    protected function clean_ennu_user_meta( $user_id ) {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE 'ennu_%'",
                $user_id
            )
        );
    }

    /**
     * Create mock assessment data
     */
    protected function create_mock_assessment_data( $assessment_type = 'hair_assessment', $score = 75 ) {
        $base_data = array(
            'age_range' => '30-39',
            'gender' => 'male',
            'overall_score' => $score,
            'completion_timestamp' => current_time( 'mysql' ),
        );

        // Assessment-specific data
        switch ( $assessment_type ) {
            case 'hair_assessment':
                $specific_data = array(
                    'hair_concerns' => array( 'thinning', 'balding' ),
                    'hair_loss_pattern' => 'crown',
                    'category_scores' => array(
                        'hair_health' => $score - 5,
                        'scalp_condition' => $score + 5,
                    ),
                );
                break;
                
            case 'weight_loss_assessment':
                $specific_data = array(
                    'current_weight' => '180',
                    'target_weight' => '160',
                    'activity_level' => 'moderate',
                    'category_scores' => array(
                        'nutrition' => $score - 10,
                        'exercise' => $score + 5,
                        'metabolism' => $score,
                    ),
                );
                break;
                
            default:
                $specific_data = array(
                    'category_scores' => array(
                        'general_health' => $score,
                    ),
                );
        }

        $data = array_merge( $base_data, $specific_data );

        // Save to user meta
        foreach ( $data as $key => $value ) {
            update_user_meta( $this->test_user_id, "ennu_{$assessment_type}_{$key}", $value );
        }

        return $data;
    }

    /**
     * Assert that user meta exists with expected value
     */
    protected function assertUserMetaEquals( $meta_key, $expected_value, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = $this->test_user_id;
        }

        $actual_value = get_user_meta( $user_id, $meta_key, true );
        $this->assertEquals( $expected_value, $actual_value );
    }

    /**
     * Assert that user meta exists
     */
    protected function assertUserMetaExists( $meta_key, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = $this->test_user_id;
        }

        $value = get_user_meta( $user_id, $meta_key, true );
        $this->assertNotEmpty( $value, "User meta '{$meta_key}' should exist but was empty." );
    }

    /**
     * Assert that user meta does not exist
     */
    protected function assertUserMetaNotExists( $meta_key, $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = $this->test_user_id;
        }

        $value = get_user_meta( $user_id, $meta_key, true );
        $this->assertEmpty( $value, "User meta '{$meta_key}' should not exist but had value: " . print_r( $value, true ) );
    }

    /**
     * Mock WordPress AJAX request
     */
    protected function mock_ajax_request( $action, $data = array(), $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = $this->test_user_id;
        }

        $_POST = array_merge(
            array(
                'action' => $action,
                'nonce' => wp_create_nonce( 'ennu_ajax_nonce' ),
            ),
            $data
        );

        wp_set_current_user( $user_id );
    }

    /**
     * Assert AJAX response format
     */
    protected function assertValidAjaxResponse( $response ) {
        $this->assertIsArray( $response );
        $this->assertArrayHasKey( 'success', $response );
        
        if ( $response['success'] ) {
            $this->assertArrayHasKey( 'data', $response );
        } else {
            $this->assertArrayHasKey( 'error', $response );
        }
    }

    /**
     * Helper to simulate time passage
     */
    protected function advance_time( $seconds ) {
        $current_time = current_time( 'timestamp' );
        $new_time = $current_time + $seconds;
        
        // Mock the current_time function
        \Brain\Monkey\Functions\when( 'current_time' )->justReturn( $new_time );
    }
}