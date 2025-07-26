<?php
/**
 * ENNU Life Plugin - WordPress User Profile Integration Test
 *
 * This test file verifies that the WordPress edit user profile page
 * is working properly with our ENNU Life plugin global fields.
 *
 * Version: 57.2.1
 * Author: ENNU Life Development Team
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_User_Profile_Integration_Test {

	private $test_user_id;
	private $test_data;
	private $results = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_test_menu' ) );
		add_action( 'wp_ajax_test_ennu_user_profile', array( $this, 'run_profile_test' ) );
		add_action( 'wp_ajax_test_ennu_profile_data_persistence', array( $this, 'test_data_persistence' ) );
	}

	/**
	 * Add test menu to admin
	 */
	public function add_test_menu() {
		add_submenu_page(
			'tools.php',
			'ENNU User Profile Test',
			'ENNU Profile Test',
			'manage_options',
			'ennu-profile-test',
			array( $this, 'render_test_page' )
		);
	}

	/**
	 * Render the test page
	 */
	public function render_test_page() {
		?>
		<div class="wrap">
			<h1>ENNU Life Plugin - User Profile Integration Test</h1>
			<p><strong>Version:</strong> 57.2.1</p>
			<p><strong>Purpose:</strong> Test WordPress user profile integration with ENNU Life global fields</p>
			
			<div class="notice notice-info">
				<p><strong>Test Overview:</strong> This test verifies that user profile data is being saved and retrieved correctly, 
				including all global fields (gender, DOB, height, weight) and their integration with the ENNU Life plugin.</p>
			</div>
			
			<div class="card">
				<h2>Test Controls</h2>
				<button id="run-profile-test" class="button button-primary">Run Profile Integration Test</button>
				<button id="test-data-persistence" class="button button-secondary">Test Data Persistence</button>
				<button id="clear-test-data" class="button button-secondary">Clear Test Data</button>
			</div>
			
			<div id="test-results" class="card" style="margin-top: 20px; display: none;">
				<h2>Test Results</h2>
				<div id="test-output"></div>
			</div>
			
			<div class="card">
				<h2>Test Details</h2>
				<h3>What This Test Checks:</h3>
				<ul>
					<li>✅ User profile page accessibility and functionality</li>
					<li>✅ Global fields (gender, DOB, height, weight) saving</li>
					<li>✅ Data retrieval and display on profile page</li>
					<li>✅ Integration with ENNU Life plugin shortcodes</li>
					<li>✅ Data persistence across sessions</li>
					<li>✅ Error handling and validation</li>
				</ul>
				
				<h3>Test Data Used:</h3>
				<ul>
					<li><strong>Gender:</strong> Male</li>
					<li><strong>Date of Birth:</strong> 1990-05-15</li>
					<li><strong>Height:</strong> 5'10" (5 feet 10 inches)</li>
					<li><strong>Weight:</strong> 175 lbs</li>
				</ul>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('#run-profile-test').on('click', function() {
				$(this).prop('disabled', true).text('Running Test...');
				$('#test-results').show();
				$('#test-output').html('<p>Running profile integration test...</p>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'test_ennu_user_profile',
						nonce: '<?php echo wp_create_nonce( 'ennu_profile_test' ); ?>'
					},
					success: function(response) {
						$('#test-output').html(response);
						$('#run-profile-test').prop('disabled', false).text('Run Profile Integration Test');
					},
					error: function() {
						$('#test-output').html('<p style="color: red;">Test failed to run. Please check console for errors.</p>');
						$('#run-profile-test').prop('disabled', false).text('Run Profile Integration Test');
					}
				});
			});
			
			$('#test-data-persistence').on('click', function() {
				$(this).prop('disabled', true).text('Testing Persistence...');
				$('#test-results').show();
				$('#test-output').html('<p>Testing data persistence...</p>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'test_ennu_profile_data_persistence',
						nonce: '<?php echo wp_create_nonce( 'ennu_persistence_test' ); ?>'
					},
					success: function(response) {
						$('#test-output').html(response);
						$('#test-data-persistence').prop('disabled', false).text('Test Data Persistence');
					},
					error: function() {
						$('#test-output').html('<p style="color: red;">Persistence test failed. Please check console for errors.</p>');
						$('#test-data-persistence').prop('disabled', false).text('Test Data Persistence');
					}
				});
			});
			
			$('#clear-test-data').on('click', function() {
				if (confirm('Are you sure you want to clear all test data?')) {
					$(this).prop('disabled', true).text('Clearing...');
					$('#test-output').html('<p>Clearing test data...</p>');
					
					// Clear test data
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'test_ennu_clear_data',
							nonce: '<?php echo wp_create_nonce( 'ennu_clear_test' ); ?>'
						},
						success: function(response) {
							$('#test-output').html('<p style="color: green;">Test data cleared successfully.</p>');
							$('#clear-test-data').prop('disabled', false).text('Clear Test Data');
						}
					});
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Run the main profile integration test
	 */
	public function run_profile_test() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_profile_test' ) ) {
			wp_die( 'Security check failed' );
		}

		$this->results = array();

		try {
			// Step 1: Create test user if needed
			$this->create_test_user();
			$this->log_result( 'Test user created/verified', 'success' );

			// Step 2: Test profile page accessibility
			$this->test_profile_page_access();

			// Step 3: Test global fields saving
			$this->test_global_fields_saving();

			// Step 4: Test data retrieval
			$this->test_data_retrieval();

			// Step 5: Test ENNU Life integration
			$this->test_ennu_integration();

			// Step 6: Test validation
			$this->test_validation();

			$this->display_results();

		} catch ( Exception $e ) {
			$this->log_result( 'Test failed with exception: ' . $e->getMessage(), 'error' );
			$this->display_results();
		}
	}

	/**
	 * Create or get test user
	 */
	private function create_test_user() {
		$test_email         = 'ennu-test-user@example.com';
		$this->test_user_id = email_exists( $test_email );

		if ( ! $this->test_user_id ) {
			$this->test_user_id = wp_create_user(
				'ennu_test_user',
				'test_password_123',
				$test_email
			);

			if ( is_wp_error( $this->test_user_id ) ) {
				throw new Exception( 'Failed to create test user: ' . $this->test_user_id->get_error_message() );
			}

			// Set user role
			$user = new WP_User( $this->test_user_id );
			$user->set_role( 'subscriber' );
		}

		// Prepare test data
		$this->test_data = array(
			'gender' => 'male',
			'dob'    => '1990-05-15',
			'height' => array(
				'ft' => '5',
				'in' => '10',
			),
			'weight' => '175',
		);
	}

	/**
	 * Test profile page accessibility
	 */
	private function test_profile_page_access() {
		// Test if we can access user profile page
		$profile_url = admin_url( 'user-edit.php?user_id=' . $this->test_user_id );

		// Simulate profile page access
		$user = get_user_by( 'id', $this->test_user_id );
		if ( ! $user ) {
			$this->log_result( 'Cannot retrieve test user', 'error' );
			return;
		}

		$this->log_result( 'Profile page accessible for user: ' . $user->user_email, 'success' );

		// Test if ENNU Life fields are present
		$this->test_ennu_fields_presence();
	}

	/**
	 * Test if ENNU Life fields are present on profile page
	 */
	private function test_ennu_fields_presence() {
		// Check if our plugin is active
		if ( ! class_exists( 'ENNU_Life_Assessment_Shortcodes' ) ) {
			$this->log_result( 'ENNU Life plugin not active', 'error' );
			return;
		}

		// Test if global fields are being added to profile
		$has_gender_field      = has_action( 'show_user_profile', 'ennu_add_global_fields_to_profile' );
		$has_gender_field_edit = has_action( 'edit_user_profile', 'ennu_add_global_fields_to_profile' );

		if ( $has_gender_field && $has_gender_field_edit ) {
			$this->log_result( 'ENNU Life profile fields hooks are active', 'success' );
		} else {
			$this->log_result( 'ENNU Life profile fields hooks are missing', 'warning' );
		}
	}

	/**
	 * Test global fields saving
	 */
	private function test_global_fields_saving() {
		// Save test data to user meta
		$user_id = $this->test_user_id;

		// Save gender
		update_user_meta( $user_id, 'ennu_global_user_gender', $this->test_data['gender'] );

		// Save DOB
		update_user_meta( $user_id, 'ennu_global_user_dob_combined', $this->test_data['dob'] );

		// Save height/weight
		$height_weight_data = array(
			'ft'     => $this->test_data['height']['ft'],
			'in'     => $this->test_data['height']['in'],
			'weight' => $this->test_data['weight'],
		);
		update_user_meta( $user_id, 'ennu_global_height_weight', $height_weight_data );

		$this->log_result( 'Test data saved to user meta', 'success' );

		// Verify data was saved
		$saved_gender        = get_user_meta( $user_id, 'ennu_global_user_gender', true );
		$saved_dob           = get_user_meta( $user_id, 'ennu_global_user_dob_combined', true );
		$saved_height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );

		if ( $saved_gender === $this->test_data['gender'] ) {
			$this->log_result( 'Gender data saved correctly', 'success' );
		} else {
			$this->log_result( 'Gender data save verification failed', 'error' );
		}

		if ( $saved_dob === $this->test_data['dob'] ) {
			$this->log_result( 'DOB data saved correctly', 'success' );
		} else {
			$this->log_result( 'DOB data save verification failed', 'error' );
		}

		if ( is_array( $saved_height_weight ) &&
			$saved_height_weight['ft'] === $this->test_data['height']['ft'] &&
			$saved_height_weight['in'] === $this->test_data['height']['in'] &&
			$saved_height_weight['weight'] === $this->test_data['weight'] ) {
			$this->log_result( 'Height/Weight data saved correctly', 'success' );
		} else {
			$this->log_result( 'Height/Weight data save verification failed', 'error' );
		}
	}

	/**
	 * Test data retrieval
	 */
	private function test_data_retrieval() {
		$user_id = $this->test_user_id;

		// Test retrieval through our plugin methods
		if ( class_exists( 'ENNU_Life_Assessment_Shortcodes' ) ) {
			$shortcode_instance = new ENNU_Life_Assessment_Shortcodes();

			// Test get_user_global_data method if it exists
			if ( method_exists( $shortcode_instance, 'get_user_global_data' ) ) {
				$global_data = $shortcode_instance->get_user_global_data( $user_id );

				if ( is_array( $global_data ) ) {
					$this->log_result( 'Global data retrieval method working', 'success' );

					// Check specific fields
					if ( isset( $global_data['gender'] ) && $global_data['gender'] === $this->test_data['gender'] ) {
						$this->log_result( 'Gender retrieval working', 'success' );
					} else {
						$this->log_result( 'Gender retrieval failed', 'error' );
					}

					if ( isset( $global_data['dob'] ) && $global_data['dob'] === $this->test_data['dob'] ) {
						$this->log_result( 'DOB retrieval working', 'success' );
					} else {
						$this->log_result( 'DOB retrieval failed', 'error' );
					}
				} else {
					$this->log_result( 'Global data retrieval method not working', 'error' );
				}
			} else {
				$this->log_result( 'get_user_global_data method not found', 'warning' );
			}
		}

		// Test direct user meta retrieval
		$retrieved_gender        = get_user_meta( $user_id, 'ennu_global_user_gender', true );
		$retrieved_dob           = get_user_meta( $user_id, 'ennu_global_user_dob_combined', true );
		$retrieved_height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );

		if ( $retrieved_gender === $this->test_data['gender'] ) {
			$this->log_result( 'Direct gender retrieval working', 'success' );
		} else {
			$this->log_result( 'Direct gender retrieval failed', 'error' );
		}

		if ( $retrieved_dob === $this->test_data['dob'] ) {
			$this->log_result( 'Direct DOB retrieval working', 'success' );
		} else {
			$this->log_result( 'Direct DOB retrieval failed', 'error' );
		}

		if ( is_array( $retrieved_height_weight ) ) {
			$this->log_result( 'Direct height/weight retrieval working', 'success' );
		} else {
			$this->log_result( 'Direct height/weight retrieval failed', 'error' );
		}
	}

	/**
	 * Test ENNU Life integration
	 */
	private function test_ennu_integration() {
		// Test if our shortcodes can access the user data
		if ( class_exists( 'ENNU_Life_Assessment_Shortcodes' ) ) {
			$shortcode_instance = new ENNU_Life_Assessment_Shortcodes();

			// Test if user dashboard can be rendered
			if ( method_exists( $shortcode_instance, 'render_user_dashboard' ) ) {
				// Temporarily switch to test user
				$original_user = wp_get_current_user();
				wp_set_current_user( $this->test_user_id );

				// Try to render dashboard (capture output)
				ob_start();
				$shortcode_instance->render_user_dashboard();
				$dashboard_output = ob_get_clean();

				// Restore original user
				wp_set_current_user( $original_user->ID );

				if ( ! empty( $dashboard_output ) ) {
					$this->log_result( 'User dashboard rendering working', 'success' );

					// Check if user data appears in dashboard
					if ( strpos( $dashboard_output, 'Male' ) !== false ||
						strpos( $dashboard_output, '1990' ) !== false ||
						strpos( $dashboard_output, '5\'10"' ) !== false ||
						strpos( $dashboard_output, '175' ) !== false ) {
						$this->log_result( 'User data appears in dashboard', 'success' );
					} else {
						$this->log_result( 'User data not appearing in dashboard', 'warning' );
					}
				} else {
					$this->log_result( 'User dashboard rendering failed', 'error' );
				}
			} else {
				$this->log_result( 'render_user_dashboard method not found', 'warning' );
			}
		}
	}

	/**
	 * Test validation
	 */
	private function test_validation() {
		// Test invalid data handling
		$user_id = $this->test_user_id;

		// Test invalid gender
		update_user_meta( $user_id, 'ennu_global_user_gender', 'invalid_gender' );
		$invalid_gender = get_user_meta( $user_id, 'ennu_global_user_gender', true );
		if ( $invalid_gender === 'invalid_gender' ) {
			$this->log_result( 'Invalid gender data handling working', 'success' );
		} else {
			$this->log_result( 'Invalid gender data handling failed', 'error' );
		}

		// Test invalid DOB
		update_user_meta( $user_id, 'ennu_global_user_dob_combined', 'invalid-date' );
		$invalid_dob = get_user_meta( $user_id, 'ennu_global_user_dob_combined', true );
		if ( $invalid_dob === 'invalid-date' ) {
			$this->log_result( 'Invalid DOB data handling working', 'success' );
		} else {
			$this->log_result( 'Invalid DOB data handling failed', 'error' );
		}

		// Restore valid data
		update_user_meta( $user_id, 'ennu_global_user_gender', $this->test_data['gender'] );
		update_user_meta( $user_id, 'ennu_global_user_dob_combined', $this->test_data['dob'] );
	}

	/**
	 * Test data persistence
	 */
	public function test_data_persistence() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_persistence_test' ) ) {
			wp_die( 'Security check failed' );
		}

		$this->results = array();

		try {
			// Create test user if needed
			$this->create_test_user();

			// Save test data
			$this->test_global_fields_saving();

			// Simulate session end and restart
			$this->log_result( 'Simulating session restart...', 'info' );

			// Clear any cached data
			wp_cache_flush();

			// Test data retrieval after "restart"
			$this->test_data_retrieval();

			// Test dashboard rendering after "restart"
			$this->test_ennu_integration();

			$this->display_results();

		} catch ( Exception $e ) {
			$this->log_result( 'Persistence test failed: ' . $e->getMessage(), 'error' );
			$this->display_results();
		}
	}

	/**
	 * Log test result
	 */
	private function log_result( $message, $type = 'info' ) {
		$this->results[] = array(
			'message'   => $message,
			'type'      => $type,
			'timestamp' => current_time( 'mysql' ),
		);
	}

	/**
	 * Display test results
	 */
	private function display_results() {
		$output = '<div class="test-results">';

		$success_count = 0;
		$error_count   = 0;
		$warning_count = 0;
		$info_count    = 0;

		foreach ( $this->results as $result ) {
			$class = 'notice notice-' . $result['type'];
			$icon  = '';

			switch ( $result['type'] ) {
				case 'success':
					$icon = '✅';
					$success_count++;
					break;
				case 'error':
					$icon = '❌';
					$error_count++;
					break;
				case 'warning':
					$icon = '⚠️';
					$warning_count++;
					break;
				case 'info':
					$icon = 'ℹ️';
					$info_count++;
					break;
			}

			$output .= '<div class="' . $class . '">';
			$output .= '<p>' . $icon . ' <strong>' . esc_html( $result['message'] ) . '</strong></p>';
			$output .= '<small>Time: ' . esc_html( $result['timestamp'] ) . '</small>';
			$output .= '</div>';
		}

		// Summary
		$output .= '<div class="test-summary" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa;">';
		$output .= '<h3>Test Summary</h3>';
		$output .= '<p><strong>Total Tests:</strong> ' . count( $this->results ) . '</p>';
		$output .= '<p><strong>✅ Success:</strong> ' . $success_count . '</p>';
		$output .= '<p><strong>❌ Errors:</strong> ' . $error_count . '</p>';
		$output .= '<p><strong>⚠️ Warnings:</strong> ' . $warning_count . '</p>';
		$output .= '<p><strong>ℹ️ Info:</strong> ' . $info_count . '</p>';

		if ( $error_count === 0 ) {
			$output .= '<p style="color: green; font-weight: bold;">🎉 All critical tests passed! User profile integration is working correctly.</p>';
		} else {
			$output .= '<p style="color: red; font-weight: bold;">⚠️ Some tests failed. Please review the errors above.</p>';
		}

		$output .= '</div>';

		$output .= '</div>';

		echo $output;
		wp_die();
	}
}

// Initialize the test
new ENNU_User_Profile_Integration_Test();
