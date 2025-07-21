<?php
/**
 * Test case for ENNU_Security_Validator class
 *
 * Tests security validation functionality including rate limiting,
 * input sanitization, and CSRF protection.
 *
 * @package ENNU_Life_Tests
 */

use WP_UnitTestCase;

class SecurityValidatorTest extends WP_UnitTestCase {

	private $security_validator;

	public function setUp(): void {
		parent::setUp();

		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-security-validator.php';
		$this->security_validator = ENNU_Security_Validator::get_instance();
	}

	/**
	 * Test rate limiting functionality
	 */
	public function test_rate_limit_functionality() {
		$action = 'test_action_' . time();

		for ( $i = 0; $i < 3; $i++ ) {
			$result = $this->security_validator->rate_limit_check( $action, 5, 60 );
			$this->assertTrue( $result, "Request $i should pass rate limit" );
		}

		for ( $i = 0; $i < 3; $i++ ) {
			$this->security_validator->rate_limit_check( $action, 5, 60 );
		}

		$result = $this->security_validator->rate_limit_check( $action, 5, 60 );
		$this->assertFalse( $result, 'Request should fail due to rate limiting' );
	}

	/**
	 * Test input sanitization
	 */
	public function test_input_sanitization() {
		$malicious_inputs = array(
			'<script>alert("xss")</script>',
			'javascript:alert("xss")',
			'<img src="x" onerror="alert(1)">',
			'<?php echo "malicious"; ?>',
			'SELECT * FROM users WHERE 1=1',
		);

		foreach ( $malicious_inputs as $input ) {
			$sanitized = $this->security_validator->sanitize_input( $input );
			$this->assertStringNotContainsString( '<script>', $sanitized );
			$this->assertStringNotContainsString( 'javascript:', $sanitized );
			$this->assertStringNotContainsString( 'onerror=', $sanitized );
			$this->assertStringNotContainsString( '<?php', $sanitized );
		}
	}

	/**
	 * Test CSRF token generation and validation
	 */
	public function test_csrf_token_validation() {
		$action = 'test_csrf_action';

		$token = $this->security_validator->generate_csrf_token( $action );
		$this->assertNotEmpty( $token );
		$this->assertIsString( $token );

		$is_valid = $this->security_validator->validate_csrf_token( $token, $action );
		$this->assertTrue( $is_valid );

		$invalid_token = 'invalid_token_123';
		$is_valid      = $this->security_validator->validate_csrf_token( $invalid_token, $action );
		$this->assertFalse( $is_valid );

		$is_valid = $this->security_validator->validate_csrf_token( $token, 'different_action' );
		$this->assertFalse( $is_valid );
	}

	/**
	 * Test security event logging
	 */
	public function test_security_event_logging() {
		$event_type = 'test_security_event';
		$event_data = array(
			'user_id'    => 123,
			'ip_address' => '192.168.1.1',
			'action'     => 'test_action',
		);

		$result = $this->security_validator->log_security_event( $event_type, $event_data );
		$this->assertTrue( $result );

		$recent_events = $this->security_validator->get_recent_security_events( 1 );
		$this->assertIsArray( $recent_events );
	}

	/**
	 * Test IP address validation
	 */
	public function test_ip_address_validation() {
		$valid_ips   = array( '192.168.1.1', '10.0.0.1', '127.0.0.1', '8.8.8.8' );
		$invalid_ips = array( '999.999.999.999', 'not.an.ip', '', '192.168.1' );

		foreach ( $valid_ips as $ip ) {
			$is_valid = $this->security_validator->is_valid_ip( $ip );
			$this->assertTrue( $is_valid, "IP $ip should be valid" );
		}

		foreach ( $invalid_ips as $ip ) {
			$is_valid = $this->security_validator->is_valid_ip( $ip );
			$this->assertFalse( $is_valid, "IP $ip should be invalid" );
		}
	}

	/**
	 * Test user agent validation
	 */
	public function test_user_agent_validation() {
		$suspicious_agents = array(
			'sqlmap',
			'nikto',
			'nessus',
			'burp',
			'nmap',
		);

		foreach ( $suspicious_agents as $agent ) {
			$is_suspicious = $this->security_validator->is_suspicious_user_agent( $agent );
			$this->assertTrue( $is_suspicious, "User agent $agent should be flagged as suspicious" );
		}

		$normal_agents = array(
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
		);

		foreach ( $normal_agents as $agent ) {
			$is_suspicious = $this->security_validator->is_suspicious_user_agent( $agent );
			$this->assertFalse( $is_suspicious, 'Normal user agent should not be flagged' );
		}
	}

	/**
	 * Test rate limit cleanup functionality
	 */
	public function test_rate_limit_cleanup() {
		$action = 'cleanup_test_' . time();
		for ( $i = 0; $i < 3; $i++ ) {
			$this->security_validator->rate_limit_check( $action, 10, 1 ); // 1 second window
		}

		sleep( 2 );

		$cleaned = $this->security_validator->cleanup_expired_rate_limits();
		$this->assertIsInt( $cleaned );
		$this->assertGreaterThanOrEqual( 0, $cleaned );
	}
}
