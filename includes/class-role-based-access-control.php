<?php
/**
 * ENNU Role-Based Access Control System
 *
 * Implements comprehensive role-based access control for security and compliance
 * Provides user edit page access control, lab data import restrictions, and audit trail
 *
 * @package ENNU_Life_Assessments
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Role_Based_Access_Control {

	/**
	 * Initialize the Role-Based Access Control System
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_access_control' ) );
		add_action( 'admin_init', array( $this, 'restrict_admin_access' ) );
		add_action( 'wp_ajax_ennu_check_access_permissions', array( $this, 'ajax_check_access_permissions' ) );
		add_filter( 'user_has_cap', array( $this, 'filter_user_capabilities' ), 10, 4 );
		add_action( 'wp_login', array( $this, 'log_user_access' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'log_user_logout' ) );
	}

	/**
	 * Initialize access control system
	 */
	public function init_access_control() {
		add_action( 'load-user-edit.php', array( $this, 'check_user_edit_access' ) );
		add_action( 'load-users.php', array( $this, 'check_users_page_access' ) );
		add_action( 'load-user-new.php', array( $this, 'check_user_creation_access' ) );

		add_action( 'wp_ajax_ennu_import_lab_data', array( $this, 'check_lab_import_access' ), 1 );
		add_action( 'wp_ajax_ennu_manage_biomarkers', array( $this, 'check_biomarker_management_access' ), 1 );

		add_filter( 'ennu_can_view_user_data', array( $this, 'check_user_data_access' ), 10, 2 );
		add_filter( 'ennu_can_edit_user_data', array( $this, 'check_user_data_edit_access' ), 10, 2 );
	}

	/**
	 * Check if user can access user edit pages
	 *
	 * @param int $target_user_id User ID being edited
	 * @param int $current_user_id Current user ID (optional)
	 * @return bool Access allowed
	 */
	public function can_access_user_edit( $target_user_id, $current_user_id = null ) {
		if ( ! $current_user_id ) {
			$current_user_id = get_current_user_id();
		}

		if ( ! $current_user_id ) {
			return false;
		}

		if ( $current_user_id === $target_user_id ) {
			$this->log_access_attempt( $current_user_id, 'user_edit_self', $target_user_id, 'allowed' );
			return true;
		}

		$current_user = get_user_by( 'ID', $current_user_id );
		if ( ! $current_user ) {
			return false;
		}

		$access_allowed = false;
		$reason         = 'insufficient_permissions';

		if ( user_can( $current_user, 'manage_options' ) ) {
			$access_allowed = true;
			$reason         = 'administrator_access';
		} elseif ( user_can( $current_user, 'ennu_manage_all_patients' ) ) {
			$access_allowed = true;
			$reason         = 'medical_director_access';
		} elseif ( user_can( $current_user, 'ennu_manage_assigned_patients' ) ) {
			$access_allowed = $this->is_patient_assigned( $target_user_id, $current_user_id );
			$reason         = $access_allowed ? 'assigned_patient_access' : 'not_assigned_patient';
		} elseif ( user_can( $current_user, 'ennu_view_patient_data' ) ) {
			$access_allowed = $this->can_view_patient_data( $target_user_id, $current_user_id );
			$reason         = $access_allowed ? 'read_only_access' : 'no_view_permission';
		}

		$this->log_access_attempt( $current_user_id, 'user_edit_other', $target_user_id, $access_allowed ? 'allowed' : 'denied', $reason );

		return $access_allowed;
	}

	/**
	 * Check if user can import lab data
	 *
	 * @param int $user_id User ID (optional)
	 * @return bool Import allowed
	 */
	public function can_import_lab_data( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return false;
		}

		$access_allowed = false;
		$reason         = 'insufficient_permissions';

		if ( user_can( $user, 'manage_options' ) ) {
			$access_allowed = true;
			$reason         = 'administrator_access';
		} elseif ( user_can( $user, 'ennu_import_lab_data' ) ) {
			$access_allowed = true;
			$reason         = 'lab_import_permission';
		}

		$this->log_access_attempt( $user_id, 'lab_data_import', null, $access_allowed ? 'allowed' : 'denied', $reason );

		return $access_allowed;
	}

	/**
	 * Check if user can manage biomarker data
	 *
	 * @param int $target_user_id Target user whose biomarkers are being managed
	 * @param int $current_user_id Current user ID (optional)
	 * @return bool Management allowed
	 */
	public function can_manage_biomarkers( $target_user_id, $current_user_id = null ) {
		if ( ! $current_user_id ) {
			$current_user_id = get_current_user_id();
		}

		if ( ! $current_user_id ) {
			return false;
		}

		if ( $current_user_id === $target_user_id ) {
			$this->log_access_attempt( $current_user_id, 'biomarker_management_self', $target_user_id, 'allowed' );
			return true;
		}

		$current_user = get_user_by( 'ID', $current_user_id );
		if ( ! $current_user ) {
			return false;
		}

		$access_allowed = false;
		$reason         = 'insufficient_permissions';

		if ( user_can( $current_user, 'manage_options' ) ) {
			$access_allowed = true;
			$reason         = 'administrator_access';
		} elseif ( user_can( $current_user, 'ennu_manage_biomarkers' ) ) {
			$access_allowed = true;
			$reason         = 'biomarker_management_permission';
		} elseif ( user_can( $current_user, 'ennu_manage_assigned_patients' ) ) {
			$access_allowed = $this->is_patient_assigned( $target_user_id, $current_user_id );
			$reason         = $access_allowed ? 'assigned_patient_biomarkers' : 'not_assigned_patient';
		}

		$this->log_access_attempt( $current_user_id, 'biomarker_management_other', $target_user_id, $access_allowed ? 'allowed' : 'denied', $reason );

		return $access_allowed;
	}

	/**
	 * Check if patient is assigned to medical provider
	 *
	 * @param int $patient_id Patient user ID
	 * @param int $provider_id Provider user ID
	 * @return bool Patient is assigned
	 */
	private function is_patient_assigned( $patient_id, $provider_id ) {
		$assigned_patients = get_user_meta( $provider_id, 'ennu_assigned_patients', true );
		if ( ! is_array( $assigned_patients ) ) {
			$assigned_patients = array();
		}

		return in_array( $patient_id, $assigned_patients );
	}

	/**
	 * Check if user can view patient data
	 *
	 * @param int $patient_id Patient user ID
	 * @param int $viewer_id Viewer user ID
	 * @return bool View access allowed
	 */
	private function can_view_patient_data( $patient_id, $viewer_id ) {
		$viewer = get_user_by( 'ID', $viewer_id );
		if ( ! $viewer ) {
			return false;
		}

		if ( ! user_can( $viewer, 'ennu_view_patient_data' ) ) {
			return false;
		}

		$patient_privacy_level = get_user_meta( $patient_id, 'ennu_privacy_level', true );
		if ( $patient_privacy_level === 'restricted' ) {
			return $this->is_patient_assigned( $patient_id, $viewer_id );
		}

		return true;
	}

	/**
	 * Restrict admin access based on roles
	 */
	public function restrict_admin_access() {
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			return;
		}

		$current_screen = get_current_screen();
		if ( ! $current_screen ) {
			return;
		}

		$restricted_pages = array(
			'users.php'           => 'ennu_manage_users',
			'user-edit.php'       => 'ennu_edit_users',
			'user-new.php'        => 'ennu_create_users',
			'options-general.php' => 'manage_options',
			'tools.php'           => 'manage_options',
		);

		$current_page = $current_screen->parent_file;
		if ( isset( $restricted_pages[ $current_page ] ) ) {
			$required_capability = $restricted_pages[ $current_page ];

			if ( ! current_user_can( $required_capability ) ) {
				$this->log_access_attempt( $current_user_id, 'admin_page_access', null, 'denied', $current_page );
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'ennu-life-assessments' ) );
			}
		}
	}

	/**
	 * Check user edit page access
	 */
	public function check_user_edit_access() {
		$user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : get_current_user_id();

		if ( ! $this->can_access_user_edit( $user_id ) ) {
			wp_die( __( 'You do not have permission to edit this user.', 'ennu-life-assessments' ) );
		}
	}

	/**
	 * Check users page access
	 */
	public function check_users_page_access() {
		if ( ! current_user_can( 'ennu_manage_users' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to access user management.', 'ennu-life-assessments' ) );
		}
	}

	/**
	 * Check user creation access
	 */
	public function check_user_creation_access() {
		if ( ! current_user_can( 'ennu_create_users' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to create new users.', 'ennu-life-assessments' ) );
		}
	}

	/**
	 * Check lab import access
	 */
	public function check_lab_import_access() {
		if ( ! $this->can_import_lab_data() ) {
			wp_send_json_error( 'Insufficient permissions for lab data import' );
			wp_die();
		}
	}

	/**
	 * Check biomarker management access
	 */
	public function check_biomarker_management_access() {
		$target_user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : get_current_user_id();

		if ( ! $this->can_manage_biomarkers( $target_user_id ) ) {
			wp_send_json_error( 'Insufficient permissions for biomarker management' );
			wp_die();
		}
	}

	/**
	 * Filter user capabilities based on role-based access control
	 *
	 * @param array $allcaps All capabilities
	 * @param array $caps Required capabilities
	 * @param array $args Arguments
	 * @param WP_User $user User object
	 * @return array Filtered capabilities
	 */
	public function filter_user_capabilities( $allcaps, $caps, $args, $user ) {
		if ( ! isset( $args[0] ) ) {
			return $allcaps;
		}

		$capability = $args[0];
		$user_id    = $user->ID;

		switch ( $capability ) {
			case 'ennu_edit_user_data':
				$target_user_id         = isset( $args[2] ) ? $args[2] : $user_id;
				$allcaps[ $capability ] = $this->can_access_user_edit( $target_user_id, $user_id );
				break;

			case 'ennu_import_lab_data':
				$allcaps[ $capability ] = $this->can_import_lab_data( $user_id );
				break;

			case 'ennu_manage_biomarkers':
				$target_user_id         = isset( $args[2] ) ? $args[2] : $user_id;
				$allcaps[ $capability ] = $this->can_manage_biomarkers( $target_user_id, $user_id );
				break;

			case 'ennu_view_patient_data':
				$target_user_id         = isset( $args[2] ) ? $args[2] : $user_id;
				$allcaps[ $capability ] = $this->can_view_patient_data( $target_user_id, $user_id );
				break;
		}

		return $allcaps;
	}

	/**
	 * Log access attempt
	 *
	 * @param int $user_id User ID making the attempt
	 * @param string $action Action being attempted
	 * @param int $target_id Target user/resource ID
	 * @param string $result Result (allowed/denied)
	 * @param string $reason Reason for result
	 */
	private function log_access_attempt( $user_id, $action, $target_id, $result, $reason = '' ) {
		$log_entry = array(
			'timestamp'  => current_time( 'mysql' ),
			'user_id'    => $user_id,
			'action'     => $action,
			'target_id'  => $target_id,
			'result'     => $result,
			'reason'     => $reason,
			'ip_address' => $this->get_client_ip(),
			'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
		);

		$access_log   = get_option( 'ennu_access_log', array() );
		$access_log[] = $log_entry;

		if ( count( $access_log ) > 1000 ) {
			$access_log = array_slice( $access_log, -1000 );
		}

		update_option( 'ennu_access_log', $access_log );

		if ( $result === 'denied' ) {
			// REMOVED: error_log( "ENNU Access Denied: User {$user_id} attempted {$action} on target {$target_id}. Reason: {$reason}" );
		}
	}

	/**
	 * Get client IP address
	 *
	 * @return string Client IP address
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}

		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
	}

	/**
	 * Log user login
	 *
	 * @param string $user_login User login
	 * @param WP_User $user User object
	 */
	public function log_user_access( $user_login, $user ) {
		$this->log_access_attempt( $user->ID, 'user_login', null, 'allowed', 'successful_login' );
	}

	/**
	 * Log user logout
	 *
	 * @param int $user_id User ID
	 */
	public function log_user_logout( $user_id ) {
		$this->log_access_attempt( $user_id, 'user_logout', null, 'allowed', 'user_logout' );
	}

	/**
	 * AJAX handler for checking access permissions
	 */
	public function ajax_check_access_permissions() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			wp_send_json_error( 'User not authenticated' );
			return;
		}

		$action         = sanitize_text_field( $_POST['action_type'] ?? '' );
		$target_user_id = intval( $_POST['target_user_id'] ?? 0 );

		$permissions = array();

		switch ( $action ) {
			case 'user_edit':
				$permissions['can_edit'] = $this->can_access_user_edit( $target_user_id, $current_user_id );
				break;

			case 'lab_import':
				$permissions['can_import'] = $this->can_import_lab_data( $current_user_id );
				break;

			case 'biomarker_management':
				$permissions['can_manage'] = $this->can_manage_biomarkers( $target_user_id, $current_user_id );
				break;

			default:
				wp_send_json_error( 'Invalid action type' );
				return;
		}

		wp_send_json_success( $permissions );
	}

	/**
	 * Get access log for admin review
	 *
	 * @param array $filters Optional filters
	 * @return array Access log entries
	 */
	public function get_access_log( $filters = array() ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array();
		}

		$access_log = get_option( 'ennu_access_log', array() );

		if ( ! empty( $filters ) ) {
			$access_log = array_filter(
				$access_log,
				function( $entry ) use ( $filters ) {
					foreach ( $filters as $key => $value ) {
						if ( isset( $entry[ $key ] ) && $entry[ $key ] !== $value ) {
							return false;
						}
					}
					return true;
				}
			);
		}

		usort(
			$access_log,
			function( $a, $b ) {
				return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
			}
		);

		return $access_log;
	}

	/**
	 * Check user data access filter
	 *
	 * @param bool $can_view Current permission
	 * @param int $target_user_id Target user ID
	 * @return bool Updated permission
	 */
	public function check_user_data_access( $can_view, $target_user_id ) {
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			return false;
		}

		return $this->can_view_patient_data( $target_user_id, $current_user_id );
	}

	/**
	 * Check user data edit access filter
	 *
	 * @param bool $can_edit Current permission
	 * @param int $target_user_id Target user ID
	 * @return bool Updated permission
	 */
	public function check_user_data_edit_access( $can_edit, $target_user_id ) {
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			return false;
		}

		return $this->can_access_user_edit( $target_user_id, $current_user_id );
	}

	/**
	 * Assign patient to medical provider
	 *
	 * @param int $patient_id Patient user ID
	 * @param int $provider_id Provider user ID
	 * @return bool Assignment successful
	 */
	public function assign_patient_to_provider( $patient_id, $provider_id ) {
		if ( ! current_user_can( 'ennu_manage_all_patients' ) && ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$assigned_patients = get_user_meta( $provider_id, 'ennu_assigned_patients', true );
		if ( ! is_array( $assigned_patients ) ) {
			$assigned_patients = array();
		}

		if ( ! in_array( $patient_id, $assigned_patients ) ) {
			$assigned_patients[] = $patient_id;
			update_user_meta( $provider_id, 'ennu_assigned_patients', $assigned_patients );

			$this->log_access_attempt( get_current_user_id(), 'patient_assignment', $patient_id, 'allowed', "assigned_to_provider_{$provider_id}" );

			return true;
		}

		return false;
	}

	/**
	 * Remove patient from medical provider
	 *
	 * @param int $patient_id Patient user ID
	 * @param int $provider_id Provider user ID
	 * @return bool Removal successful
	 */
	public function remove_patient_from_provider( $patient_id, $provider_id ) {
		if ( ! current_user_can( 'ennu_manage_all_patients' ) && ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$assigned_patients = get_user_meta( $provider_id, 'ennu_assigned_patients', true );
		if ( ! is_array( $assigned_patients ) ) {
			return false;
		}

		$key = array_search( $patient_id, $assigned_patients );
		if ( $key !== false ) {
			unset( $assigned_patients[ $key ] );
			$assigned_patients = array_values( $assigned_patients ); // Re-index array
			update_user_meta( $provider_id, 'ennu_assigned_patients', $assigned_patients );

			$this->log_access_attempt( get_current_user_id(), 'patient_removal', $patient_id, 'allowed', "removed_from_provider_{$provider_id}" );

			return true;
		}

		return false;
	}

	/**
	 * Get assigned patients for a provider
	 *
	 * @param int $provider_id Provider user ID
	 * @return array Assigned patient IDs
	 */
	public function get_assigned_patients( $provider_id ) {
		$current_user_id = get_current_user_id();

		if ( $current_user_id !== $provider_id && ! current_user_can( 'ennu_manage_all_patients' ) && ! current_user_can( 'manage_options' ) ) {
			return array();
		}

		$assigned_patients = get_user_meta( $provider_id, 'ennu_assigned_patients', true );
		return is_array( $assigned_patients ) ? $assigned_patients : array();
	}

	/**
	 * Set patient privacy level
	 *
	 * @param int $patient_id Patient user ID
	 * @param string $privacy_level Privacy level (normal, restricted)
	 * @return bool Update successful
	 */
	public function set_patient_privacy_level( $patient_id, $privacy_level ) {
		if ( ! $this->can_access_user_edit( $patient_id ) ) {
			return false;
		}

		$valid_levels = array( 'normal', 'restricted' );
		if ( ! in_array( $privacy_level, $valid_levels ) ) {
			return false;
		}

		update_user_meta( $patient_id, 'ennu_privacy_level', $privacy_level );

		$this->log_access_attempt( get_current_user_id(), 'privacy_level_change', $patient_id, 'allowed', "set_to_{$privacy_level}" );

		return true;
	}

	/**
	 * Get security summary for admin dashboard
	 *
	 * @return array Security summary data
	 */
	public function get_security_summary() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array();
		}

		$access_log = get_option( 'ennu_access_log', array() );
		$recent_log = array_slice( $access_log, -100 ); // Last 100 entries

		$summary = array(
			'total_access_attempts'  => count( $access_log ),
			'recent_access_attempts' => count( $recent_log ),
			'denied_attempts'        => count(
				array_filter(
					$recent_log,
					function( $entry ) {
						return $entry['result'] === 'denied';
					}
				)
			),
			'unique_users'           => count( array_unique( array_column( $recent_log, 'user_id' ) ) ),
			'most_common_actions'    => array(),
			'recent_denials'         => array(),
		);

		$actions       = array_column( $recent_log, 'action' );
		$action_counts = array_count_values( $actions );
		arsort( $action_counts );
		$summary['most_common_actions'] = array_slice( $action_counts, 0, 5, true );

		$denials                   = array_filter(
			$recent_log,
			function( $entry ) {
				return $entry['result'] === 'denied';
			}
		);
		$summary['recent_denials'] = array_slice( $denials, 0, 10 );

		return $summary;
	}
}
