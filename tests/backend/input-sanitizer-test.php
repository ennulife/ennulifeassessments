<?php
/**
 * Test case for ENNU_Input_Sanitizer class
 *
 * Tests comprehensive input sanitization and validation functionality.
 *
 * @package ENNU_Life_Tests
 */

use WP_UnitTestCase;

class InputSanitizerTest extends WP_UnitTestCase {

    private $input_sanitizer;

    public function setUp(): void {
        parent::setUp();
        
        require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-input-sanitizer.php';
        $this->input_sanitizer = ENNU_Input_Sanitizer::get_instance();
    }

    /**
     * Test form data sanitization
     */
    public function test_sanitize_form_data() {
        $test_data = array(
            'email' => 'test@example.com',
            'name' => 'John <script>alert("xss")</script> Doe',
            'age' => '25',
            'weight_lbs' => '150.5',
            'notes' => 'Some notes with <b>bold</b> text',
            'malicious_field' => '<script>document.cookie</script>',
        );

        $sanitized = $this->input_sanitizer->sanitize_form_data($test_data, 'assessment');

        $this->assertEquals('test@example.com', $sanitized['email']);
        $this->assertStringNotContainsString('<script>', $sanitized['name']);
        $this->assertEquals('25', $sanitized['age']);
        $this->assertEquals('150.5', $sanitized['weight_lbs']);
        $this->assertStringNotContainsString('<script>', $sanitized['malicious_field']);
    }

    /**
     * Test email validation
     */
    public function test_validate_email_field() {
        $valid_emails = array(
            'test@example.com',
            'user.name@domain.co.uk',
            'test+tag@example.org'
        );

        $invalid_emails = array(
            'not-an-email',
            '@example.com',
            'test@',
            '',
            'test@.com'
        );

        foreach ($valid_emails as $email) {
            $result = $this->input_sanitizer->validate_email_field($email);
            $this->assertTrue($result, "Email $email should be valid");
        }

        foreach ($invalid_emails as $email) {
            $result = $this->input_sanitizer->validate_email_field($email);
            $this->assertInstanceOf('WP_Error', $result, "Email $email should be invalid");
        }
    }

    /**
     * Test numeric range validation
     */
    public function test_validate_numeric_range() {
        $result = $this->input_sanitizer->validate_numeric_range(25, 18, 65, 'age');
        $this->assertTrue($result);

        $result = $this->input_sanitizer->validate_numeric_range(150.5, 50, 500, 'weight');
        $this->assertTrue($result);

        $result = $this->input_sanitizer->validate_numeric_range(15, 18, 65, 'age');
        $this->assertInstanceOf('WP_Error', $result);

        $result = $this->input_sanitizer->validate_numeric_range(600, 50, 500, 'weight');
        $this->assertInstanceOf('WP_Error', $result);

        $result = $this->input_sanitizer->validate_numeric_range('not-a-number', 0, 100, 'score');
        $this->assertInstanceOf('WP_Error', $result);
    }

    /**
     * Test assessment data validation
     */
    public function test_validate_assessment_data() {
        $valid_data = array(
            'assessment_type' => 'hair_assessment',
            'email' => 'test@example.com',
            'age' => '30',
            'weight_lbs' => '160',
            'height_ft' => '5',
            'height_in' => '10'
        );

        $result = $this->input_sanitizer->validate_assessment_data($valid_data);
        $this->assertTrue($result);

        $invalid_data = $valid_data;
        unset($invalid_data['assessment_type']);
        $result = $this->input_sanitizer->validate_assessment_data($invalid_data);
        $this->assertInstanceOf('WP_Error', $result);

        $invalid_data = $valid_data;
        $invalid_data['email'] = 'not-an-email';
        $result = $this->input_sanitizer->validate_assessment_data($invalid_data);
        $this->assertInstanceOf('WP_Error', $result);

        $invalid_data = $valid_data;
        $invalid_data['age'] = '150';
        $result = $this->input_sanitizer->validate_assessment_data($invalid_data);
        $this->assertInstanceOf('WP_Error', $result);
    }

    /**
     * Test required fields validation
     */
    public function test_validate_required_fields() {
        $data = array(
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => ''
        );

        $required_fields = array('field1', 'field2');
        $result = $this->input_sanitizer->validate_required_fields($data, $required_fields);
        $this->assertTrue($result);

        $required_fields = array('field1', 'field2', 'field3');
        $result = $this->input_sanitizer->validate_required_fields($data, $required_fields);
        $this->assertInstanceOf('WP_Error', $result);

        $required_fields = array('field1', 'field2', 'missing_field');
        $result = $this->input_sanitizer->validate_required_fields($data, $required_fields);
        $this->assertInstanceOf('WP_Error', $result);
    }

    /**
     * Test context-specific sanitization
     */
    public function test_context_specific_sanitization() {
        $test_cases = array(
            array('context' => 'email', 'input' => 'TEST@EXAMPLE.COM', 'expected' => 'test@example.com'),
            array('context' => 'int', 'input' => '25.7', 'expected' => 25),
            array('context' => 'float', 'input' => '25.7', 'expected' => 25.7),
            array('context' => 'phone', 'input' => '(555) 123-4567 ext. 890', 'expected' => '(555) 123-4567  890'),
            array('context' => 'key', 'input' => 'Test Key With Spaces', 'expected' => 'testkeyWithspaces'),
        );

        foreach ($test_cases as $case) {
            $result = $this->input_sanitizer->sanitize_form_data($case['input'], $case['context']);
            $this->assertEquals($case['expected'], $result, "Context {$case['context']} sanitization failed");
        }
    }

    /**
     * Test XSS prevention
     */
    public function test_xss_prevention() {
        $xss_attempts = array(
            '<script>alert("xss")</script>',
            'javascript:alert("xss")',
            '<img src="x" onerror="alert(1)">',
            '<iframe src="javascript:alert(1)"></iframe>',
            '<svg onload="alert(1)">',
            'onclick="alert(1)"',
        );

        foreach ($xss_attempts as $xss) {
            $sanitized = $this->input_sanitizer->sanitize_form_data($xss, 'general');
            $this->assertStringNotContainsString('<script>', $sanitized);
            $this->assertStringNotContainsString('javascript:', $sanitized);
            $this->assertStringNotContainsString('onerror=', $sanitized);
            $this->assertStringNotContainsString('onload=', $sanitized);
            $this->assertStringNotContainsString('onclick=', $sanitized);
        }
    }
}
