<?php
/**
 * ENNU Medical Role Manager
 * Manages Medical Director and Medical Provider roles with appropriate capabilities
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Medical_Role_Manager {

	/**
	 * Initialize medical role manager
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'create_medical_roles' ) );
		add_action( 'wp_ajax_ennu_assign_medical_role', array( __CLASS__, 'handle_assign_medical_role' ) );
		add_action( 'wp_ajax_ennu_remove_medical_role', array( __CLASS__, 'handle_remove_medical_role' ) );
		add_action( 'wp_ajax_ennu_get_medical_staff', array( __CLASS__, 'handle_get_medical_staff' ) );

		error_log( 'ENNU Medical Role Manager: Initialized' );
	}

	/**
	 * Create medical roles with appropriate capabilities
	 */
	public static function create_medical_roles() {
		$medical_director = get_role( 'ennu_medical_director' );
		if ( ! $medical_director ) {
			add_role(
				'ennu_medical_director',
				'Medical Director',
				array(
					'read'                        => true,
					'edit_posts'                  => false,
					'delete_posts'                => false,
					'publish_posts'               => false,
					'upload_files'                => false,

					'ennu_view_patient_data'      => true,
					'ennu_edit_patient_data'      => true,
					'ennu_import_lab_data'        => true,
					'ennu_manage_biomarkers'      => true,
					'ennu_flag_biomarkers'        => true,
					'ennu_view_all_patients'      => true,
					'ennu_manage_medical_staff'   => true,
					'ennu_access_medical_reports' => true,
					'ennu_export_patient_data'    => true,
					'ennu_manage_lab_templates'   => true,
					'ennu_audit_medical_actions'  => true,
				)
			);
		}

		$medical_provider = get_role( 'ennu_medical_provider' );
		if ( ! $medical_provider ) {
			add_role(
				'ennu_medical_provider',
				'Medical Provider',
				array(
					'read'                           => true,
					'edit_posts'                     => false,
					'delete_posts'                   => false,
					'publish_posts'                  => false,
					'upload_files'                   => false,

					'ennu_view_patient_data'         => true,
					'ennu_edit_patient_data'         => true,
					'ennu_manage_biomarkers'         => true,
					'ennu_flag_biomarkers'           => true,
					'ennu_view_assigned_patients'    => true,
					'ennu_access_medical_reports'    => true,
					'ennu_set_biomarker_targets'     => true,
					'ennu_review_flagged_biomarkers' => true,
				)
			);
		}

		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_capabilities = array(
				'ennu_view_patient_data',
				'ennu_edit_patient_data',
				'ennu_import_lab_data',
				'ennu_manage_biomarkers',
				'ennu_flag_biomarkers',
				'ennu_view_all_patients',
				'ennu_manage_medical_staff',
				'ennu_access_medical_reports',
				'ennu_export_patient_data',
				'ennu_manage_lab_templates',
				'ennu_audit_medical_actions',
				'ennu_set_biomarker_targets',
				'ennu_review_flagged_biomarkers',
			);

			foreach ( $admin_capabilities as $capability ) {
				$admin_role->add_cap( $capability );
			}
		}
	}

	/**
	 * Check if user has medical access
	 *
	 * @param int $user_id User ID
	 * @return bool Whether user has medical access
	 */
	public static function user_has_medical_access( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		$medical_roles = array( 'administrator', 'ennu_medical_director', 'ennu_medical_provider' );
		$user_roles    = $user->roles;

		return ! empty( array_intersect( $medical_roles, $user_roles ) );
	}

	/**
	 * Check if user can import lab data
	 *
	 * @param int $user_id User ID
	 * @return bool Whether user can import lab data
	 */
	public static function user_can_import_lab_data( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return user_can( $user_id, 'ennu_import_lab_data' ) || user_can( $user_id, 'manage_options' );
	}

	/**
	 * Check if user can manage medical staff
	 *
	 * @param int $user_id User ID
	 * @return bool Whether user can manage medical staff
	 */
	public static function user_can_manage_medical_staff( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return user_can( $user_id, 'ennu_manage_medical_staff' ) || user_can( $user_id, 'manage_options' );
	}

	/**
	 * Get medical staff members
	 *
	 * @return array Medical staff members
	 */
	public static function get_medical_staff() {
		$medical_roles = array( 'ennu_medical_director', 'ennu_medical_provider' );

		$medical_staff = array();

		foreach ( $medical_roles as $role ) {
			$users = get_users( array( 'role' => $role ) );

			foreach ( $users as $user ) {
				$medical_staff[] = array(
					'id'                 => $user->ID,
					'name'               => $user->display_name,
					'email'              => $user->user_email,
					'role'               => $role,
					'role_display'       => $role === 'ennu_medical_director' ? 'Medical Director' : 'Medical Provider',
					'last_login'         => get_user_meta( $user->ID, 'ennu_last_login', true ),
					'patients_assigned'  => self::get_user_assigned_patients_count( $user->ID ),
					'actions_this_month' => self::get_user_medical_actions_count( $user->ID, 30 ),
				);
			}
		}

		return $medical_staff;
	}

	/**
	 * Assign medical role to user
	 *
	 * @param int $user_id User ID
	 * @param string $role Role to assign
	 * @return bool Success status
	 */
	public static function assign_medical_role( $user_id, $role ) {
		$valid_roles = array( 'ennu_medical_director', 'ennu_medical_provider' );

		if ( ! in_array( $role, $valid_roles, true ) ) {
			return false;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		foreach ( $valid_roles as $existing_role ) {
			$user->remove_role( $existing_role );
		}

		$user->add_role( $role );

		self::log_medical_action(
			get_current_user_id(),
			'role_assigned',
			array(
				'target_user_id' => $user_id,
				'role'           => $role,
				'timestamp'      => current_time( 'mysql' ),
			)
		);

		return true;
	}

	/**
	 * Remove medical role from user
	 *
	 * @param int $user_id User ID
	 * @return bool Success status
	 */
	public static function remove_medical_role( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		$medical_roles = array( 'ennu_medical_director', 'ennu_medical_provider' );
		$removed_roles = array();

		foreach ( $medical_roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				$user->remove_role( $role );
				$removed_roles[] = $role;
			}
		}

		if ( ! empty( $removed_roles ) ) {
			self::log_medical_action(
				get_current_user_id(),
				'role_removed',
				array(
					'target_user_id' => $user_id,
					'removed_roles'  => $removed_roles,
					'timestamp'      => current_time( 'mysql' ),
				)
			);
		}

		return ! empty( $removed_roles );
	}

	/**
	 * Get user assigned patients count
	 *
	 * @param int $user_id User ID
	 * @return int Assigned patients count
	 */
	private static function get_user_assigned_patients_count( $user_id ) {
		$assigned_patients = get_user_meta( $user_id, 'ennu_assigned_patients', true );
		if ( ! is_array( $assigned_patients ) ) {
			return 0;
		}

		return count( $assigned_patients );
	}

	/**
	 * Get user medical actions count for time period
	 *
	 * @param int $user_id User ID
	 * @param int $days Number of days
	 * @return int Actions count
	 */
	private static function get_user_medical_actions_count( $user_id, $days ) {
		$actions_log = get_user_meta( $user_id, 'ennu_medical_actions_log', true );
		if ( ! is_array( $actions_log ) ) {
			return 0;
		}

		$cutoff_date    = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		$recent_actions = array_filter(
			$actions_log,
			function( $action ) use ( $cutoff_date ) {
				return $action['timestamp'] >= $cutoff_date;
			}
		);

		return count( $recent_actions );
	}

	/**
	 * Log medical action
	 *
	 * @param int $user_id User ID performing action
	 * @param string $action Action type
	 * @param array $details Action details
	 */
	private static function log_medical_action( $user_id, $action, $details ) {
		$actions_log = get_user_meta( $user_id, 'ennu_medical_actions_log', true );
		if ( ! is_array( $actions_log ) ) {
			$actions_log = array();
		}

		$log_entry = array(
			'action'     => $action,
			'details'    => $details,
			'timestamp'  => current_time( 'mysql' ),
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
		);

		$actions_log[] = $log_entry;

		$actions_log = array_slice( $actions_log, -100 );

		update_user_meta( $user_id, 'ennu_medical_actions_log', $actions_log );

		self::log_to_global_audit( $user_id, $action, $details );
	}

	/**
	 * Log to global medical audit
	 *
	 * @param int $user_id User ID
	 * @param string $action Action type
	 * @param array $details Action details
	 */
	private static function log_to_global_audit( $user_id, $action, $details ) {
		$global_audit = get_option( 'ennu_medical_audit_log', array() );

		$audit_entry = array(
			'user_id'    => $user_id,
			'action'     => $action,
			'details'    => $details,
			'timestamp'  => current_time( 'mysql' ),
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
		);

		$global_audit[] = $audit_entry;

		$global_audit = array_slice( $global_audit, -1000 );

		update_option( 'ennu_medical_audit_log', $global_audit );
	}

	/**
	 * Handle assign medical role AJAX request
	 */
	public static function handle_assign_medical_role() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! self::user_can_manage_medical_staff() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$user_id = intval( $_POST['user_id'] ?? 0 );
		$role    = sanitize_text_field( $_POST['role'] ?? '' );

		if ( ! $user_id || ! $role ) {
			wp_send_json_error( 'Invalid user ID or role' );
		}

		$success = self::assign_medical_role( $user_id, $role );

		if ( $success ) {
			wp_send_json_success( array( 'message' => 'Medical role assigned successfully' ) );
		} else {
			wp_send_json_error( 'Failed to assign medical role' );
		}
	}

	/**
	 * Handle remove medical role AJAX request
	 */
	public static function handle_remove_medical_role() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! self::user_can_manage_medical_staff() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$user_id = intval( $_POST['user_id'] ?? 0 );

		if ( ! $user_id ) {
			wp_send_json_error( 'Invalid user ID' );
		}

		$success = self::remove_medical_role( $user_id );

		if ( $success ) {
			wp_send_json_success( array( 'message' => 'Medical role removed successfully' ) );
		} else {
			wp_send_json_error( 'Failed to remove medical role' );
		}
	}

	/**
	 * Handle get medical staff AJAX request
	 */
	public static function handle_get_medical_staff() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! self::user_can_manage_medical_staff() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$medical_staff = self::get_medical_staff();

		wp_send_json_success( array( 'medical_staff' => $medical_staff ) );
	}

	/**
	 * Get medical audit log
	 *
	 * @param int $limit Number of entries to return
	 * @return array Audit log entries
	 */
	public static function get_medical_audit_log( $limit = 50 ) {
		$global_audit = get_option( 'ennu_medical_audit_log', array() );

		usort(
			$global_audit,
			function( $a, $b ) {
				return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
			}
		);

		return array_slice( $global_audit, 0, $limit );
	}

	/**
	 * Clean up medical roles on plugin deactivation
	 */
	public static function cleanup_medical_roles() {
		remove_role( 'ennu_medical_director' );
		remove_role( 'ennu_medical_provider' );

		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_capabilities = array(
				'ennu_view_patient_data',
				'ennu_edit_patient_data',
				'ennu_import_lab_data',
				'ennu_manage_biomarkers',
				'ennu_flag_biomarkers',
				'ennu_view_all_patients',
				'ennu_manage_medical_staff',
				'ennu_access_medical_reports',
				'ennu_export_patient_data',
				'ennu_manage_lab_templates',
				'ennu_audit_medical_actions',
				'ennu_set_biomarker_targets',
				'ennu_review_flagged_biomarkers',
			);

			foreach ( $admin_capabilities as $capability ) {
				$admin_role->remove_cap( $capability );
			}
		}
	}
}
