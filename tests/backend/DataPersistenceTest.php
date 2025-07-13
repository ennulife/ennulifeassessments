<?php
/**
 * Test case for data persistence logic.
 *
 * Verifies that assessment data is correctly saved to and retrieved
 * from the usermeta table.
 *
 * @package ENNU_Life_Tests
 */

use WP_UnitTestCase;

class DataPersistenceTest extends WP_UnitTestCase {

    private $shortcodes_instance;

    public function setUp(): void {
        parent::setUp();
        // The class that contains the data-saving methods we need to test.
        $this->shortcodes_instance = new ENNU_Assessment_Shortcodes();
    }

    /**
     * Test saving and retrieving assessment-specific metadata.
     */
    public function test_save_and_retrieve_assessment_specific_meta() {
        // 1. Create a new dummy user for this test.
        $user_id = self::factory()->user->create(array('role' => 'subscriber'));
        $this->assertIsInt($user_id);

        // 2. Define the data we want to save.
        $assessment_data = array(
            'assessment_type' => 'skin_assessment',
            'skin_q1' => 'oily',
            'skin_q2' => 'aging',
        );

        // 3. Call the method responsible for saving the data.
        // This is a private method, so we use reflection to test it directly.
        $method = new ReflectionMethod('ENNU_Assessment_Shortcodes', 'save_assessment_specific_meta');
        $method->setAccessible(true);
        $method->invoke($this->shortcodes_instance, $user_id, $assessment_data);

        // 4. Retrieve the data we just saved.
        $saved_q1 = get_user_meta($user_id, 'ennu_skin_assessment_skin_q1', true);
        $saved_q2 = get_user_meta($user_id, 'ennu_skin_assessment_skin_q2', true);

        // 5. Assert that the retrieved data matches what we saved.
        $this->assertEquals('oily', $saved_q1);
        $this->assertEquals('aging', $saved_q2);
    }

    /**
     * Test saving and retrieving global user data.
     */
    public function test_save_and_retrieve_global_user_data() {
        $user_id = self::factory()->user->create();

        $contact_data = array(
            'assessment_type' => 'hair_assessment', // Type doesn't matter for global data
            'contact_info' => array(
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com'
            ),
        );

        // Call the method responsible for saving core user data.
        $method = new ReflectionMethod('ENNU_Assessment_Shortcodes', 'update_core_user_data');
        $method->setAccessible(true);
        $method->invoke($this->shortcodes_instance, $user_id, $contact_data);

        // Retrieve the global meta.
        $first_name = get_user_meta($user_id, 'ennu_global_contact_info_first_name', true);
        $last_name = get_user_meta($user_id, 'ennu_global_contact_info_last_name', true);
        
        // Also check standard WordPress fields.
        $user_data = get_userdata($user_id);
        $user_email = $user_data->user_email;

        $this->assertEquals('John', $first_name);
        $this->assertEquals('Doe', $last_name);
        $this->assertEquals('john.doe@example.com', $user_email);
    }
} 