<?php
/**
 * ENNU Biomarker Flag Manager
 *
 * Manages biomarker flagging system for medical provider workflow.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Biomarker_Flag_Manager {

	/**
	 * Flag a biomarker for a user
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker Biomarker name
	 * @param string $reason Flag reason
	 * @param int $flagged_by User ID of person flagging
	 * @return bool Success status
	 */
	public static function flag_biomarker( $user_id, $biomarker, $reason, $flagged_by = null ) {
		if ( ! $flagged_by ) {
			$flagged_by = get_current_user_id();
		}

		$flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
		if ( ! is_array( $flags ) ) {
			$flags = array();
		}

		$flag_data = array(
			'biomarker'  => sanitize_text_field( $biomarker ),
			'reason'     => sanitize_textarea_field( $reason ),
			'flagged_by' => $flagged_by,
			'flagged_at' => current_time( 'mysql' ),
			'timestamp'  => time(),
			'status'     => 'active',
			'priority'   => self::determine_flag_priority( $biomarker, $reason ),
		);

		$flags[ $biomarker ] = $flag_data;

		$result = update_user_meta( $user_id, 'ennu_biomarker_flags', $flags );

		if ( $result ) {
			self::log_flag_action( $user_id, 'flag_added', $flag_data );
			self::notify_medical_team( $user_id, $flag_data );
		}

		return $result;
	}

	/**
	 * Remove a flag from a biomarker
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker Biomarker name
	 * @param string $removal_reason Reason for removal
	 * @param int $removed_by User ID of person removing flag
	 * @return bool Success status
	 */
	public static function remove_flag( $user_id, $biomarker, $removal_reason, $removed_by = null ) {
		if ( ! $removed_by ) {
			$removed_by = get_current_user_id();
		}

		$flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
		if ( ! is_array( $flags ) || ! isset( $flags[ $biomarker ] ) ) {
			return false;
		}

		$flag_data                   = $flags[ $biomarker ];
		$flag_data['removed_by']     = $removed_by;
		$flag_data['removed_at']     = current_time( 'mysql' );
		$flag_data['removal_reason'] = sanitize_textarea_field( $removal_reason );
		$flag_data['status']         = 'removed';

		self::log_flag_action( $user_id, 'flag_removed', $flag_data );

		unset( $flags[ $biomarker ] );

		return update_user_meta( $user_id, 'ennu_biomarker_flags', $flags );
	}

	/**
	 * Get all flags for a user
	 *
	 * @param int $user_id User ID
	 * @param string $status Flag status filter
	 * @return array Flags data
	 */
	public static function get_user_flags( $user_id, $status = 'active' ) {
		$flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );

		if ( ! is_array( $flags ) ) {
			return array();
		}

		if ( 'all' === $status ) {
			return $flags;
		}

		return array_filter(
			$flags,
			function( $flag ) use ( $status ) {
				return isset( $flag['status'] ) && $flag['status'] === $status;
			}
		);
	}

	/**
	 * Get flag history for a user
	 *
	 * @param int $user_id User ID
	 * @param int $limit Number of entries to return
	 * @return array Flag history
	 */
	public static function get_flag_history( $user_id, $limit = 50 ) {
		$history = get_user_meta( $user_id, 'ennu_biomarker_flag_history', true );

		if ( ! is_array( $history ) ) {
			return array();
		}

		usort(
			$history,
			function( $a, $b ) {
				return $b['timestamp'] - $a['timestamp'];
			}
		);

		return array_slice( $history, 0, $limit );
	}

	/**
	 * Determine flag priority based on biomarker and reason
	 *
	 * @param string $biomarker Biomarker name
	 * @param string $reason Flag reason
	 * @return string Priority level
	 */
	private static function determine_flag_priority( $biomarker, $reason ) {
		$high_priority_biomarkers = array(
			'Glucose',
			'HbA1c',
			'Total Cholesterol',
			'LDL',
			'Blood Pressure',
			'Creatinine',
			'eGFR',
		);

		$critical_keywords = array(
			'critical',
			'urgent',
			'immediate',
			'dangerous',
			'severe',
			'emergency',
			'abnormal',
		);

		if ( in_array( $biomarker, $high_priority_biomarkers, true ) ) {
			return 'high';
		}

		$reason_lower = strtolower( $reason );
		foreach ( $critical_keywords as $keyword ) {
			if ( strpos( $reason_lower, $keyword ) !== false ) {
				return 'high';
			}
		}

		return 'medium';
	}

	/**
	 * Log flag action to history
	 *
	 * @param int $user_id User ID
	 * @param string $action Action type
	 * @param array $flag_data Flag data
	 */
	private static function log_flag_action( $user_id, $action, $flag_data ) {
		$history = get_user_meta( $user_id, 'ennu_biomarker_flag_history', true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$history_entry = array(
			'action'    => $action,
			'biomarker' => $flag_data['biomarker'],
			'timestamp' => time(),
			'date'      => current_time( 'mysql' ),
			'user_id'   => $user_id,
			'actor_id'  => isset( $flag_data['flagged_by'] ) ? $flag_data['flagged_by'] : $flag_data['removed_by'],
			'details'   => $flag_data,
		);

		$history[] = $history_entry;

		if ( count( $history ) > 200 ) {
			$history = array_slice( $history, -200 );
		}

		update_user_meta( $user_id, 'ennu_biomarker_flag_history', $history );
	}

	/**
	 * Notify medical team of new flag
	 *
	 * @param int $user_id User ID
	 * @param array $flag_data Flag data
	 */
	private static function notify_medical_team( $user_id, $flag_data ) {
		if ( 'high' === $flag_data['priority'] ) {
			$user    = get_userdata( $user_id );
			$subject = 'High Priority Biomarker Flag: ' . $flag_data['biomarker'];
			$message = sprintf(
				"A high priority biomarker has been flagged for user %s (%s):\n\nBiomarker: %s\nReason: %s\nFlagged at: %s",
				$user->display_name,
				$user->user_email,
				$flag_data['biomarker'],
				$flag_data['reason'],
				$flag_data['flagged_at']
			);

			$medical_team_emails = self::get_medical_team_emails();
			foreach ( $medical_team_emails as $email ) {
				wp_mail( $email, $subject, $message );
			}
		}
	}

	/**
	 * Get medical team email addresses
	 *
	 * @return array Email addresses
	 */
	private static function get_medical_team_emails() {
		$medical_roles = array( 'medical_director', 'medical_provider' );
		$emails        = array();

		$users = get_users(
			array(
				'role__in' => $medical_roles,
				'fields'   => array( 'user_email' ),
			)
		);

		foreach ( $users as $user ) {
			$emails[] = $user->user_email;
		}

		if ( empty( $emails ) ) {
			$admin_email = get_option( 'admin_email' );
			if ( $admin_email ) {
				$emails[] = $admin_email;
			}
		}

		return $emails;
	}

	/**
	 * Get flag statistics for admin dashboard
	 *
	 * @return array Flag statistics
	 */
	public static function get_flag_statistics() {
		global $wpdb;

		$stats = array(
			'total_active_flags'  => 0,
			'high_priority_flags' => 0,
			'flags_by_biomarker'  => array(),
			'recent_flags'        => array(),
		);

		$users_with_flags = $wpdb->get_results(
			"SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'ennu_biomarker_flags'"
		);

		foreach ( $users_with_flags as $user_meta ) {
			$flags = maybe_unserialize( $user_meta->meta_value );
			if ( is_array( $flags ) ) {
				foreach ( $flags as $biomarker => $flag_data ) {
					if ( isset( $flag_data['status'] ) && 'active' === $flag_data['status'] ) {
						$stats['total_active_flags']++;

						if ( isset( $flag_data['priority'] ) && 'high' === $flag_data['priority'] ) {
							$stats['high_priority_flags']++;
						}

						if ( ! isset( $stats['flags_by_biomarker'][ $biomarker ] ) ) {
							$stats['flags_by_biomarker'][ $biomarker ] = 0;
						}
						$stats['flags_by_biomarker'][ $biomarker ]++;

						$stats['recent_flags'][] = array(
							'user_id'    => $user_meta->user_id,
							'biomarker'  => $biomarker,
							'priority'   => $flag_data['priority'],
							'flagged_at' => $flag_data['flagged_at'],
						);
					}
				}
			}
		}

		usort(
			$stats['recent_flags'],
			function( $a, $b ) {
				return strtotime( $b['flagged_at'] ) - strtotime( $a['flagged_at'] );
			}
		);

		$stats['recent_flags'] = array_slice( $stats['recent_flags'], 0, 10 );

		return $stats;
	}

	/**
	 * Auto-flag biomarkers based on values and ranges
	 *
	 * @param int $user_id User ID
	 * @param array $biomarker_data Biomarker data
	 * @return array Auto-flagged biomarkers
	 */
	public static function auto_flag_biomarkers( $user_id, $biomarker_data ) {
		$auto_flags = array();

		$flag_rules = array(
			'Glucose'           => array(
				'min'  => 70,
				'max'  => 100,
				'unit' => 'mg/dL',
			),
			'HbA1c'             => array(
				'min'  => 4.0,
				'max'  => 5.6,
				'unit' => '%',
			),
			'Total Cholesterol' => array(
				'min'  => 125,
				'max'  => 200,
				'unit' => 'mg/dL',
			),
			'LDL'               => array(
				'min'  => 0,
				'max'  => 100,
				'unit' => 'mg/dL',
			),
			'HDL'               => array(
				'min'  => 40,
				'max'  => 999,
				'unit' => 'mg/dL',
			),
			'Triglycerides'     => array(
				'min'  => 0,
				'max'  => 150,
				'unit' => 'mg/dL',
			),
		);

		foreach ( $biomarker_data as $biomarker => $value ) {
			if ( isset( $flag_rules[ $biomarker ] ) ) {
				$rule          = $flag_rules[ $biomarker ];
				$numeric_value = floatval( $value );

				if ( $numeric_value < $rule['min'] || $numeric_value > $rule['max'] ) {
					$reason = sprintf(
						'Value %s %s is outside normal range (%s-%s %s)',
						$numeric_value,
						$rule['unit'],
						$rule['min'],
						$rule['max'],
						$rule['unit']
					);

					$flagged = self::flag_biomarker( $user_id, $biomarker, $reason, 0 );
					if ( $flagged ) {
						$auto_flags[] = $biomarker;
					}
				}
			}
		}

		return $auto_flags;
	}

	/**
	 * Get dashboard widget data for flags
	 *
	 * @return array Widget data
	 */
	public static function get_dashboard_widget_data() {
		$stats = self::get_flag_statistics();

		return array(
			'total_flags'    => $stats['total_active_flags'],
			'high_priority'  => $stats['high_priority_flags'],
			'recent_flags'   => array_slice( $stats['recent_flags'], 0, 5 ),
			'top_biomarkers' => array_slice( $stats['flags_by_biomarker'], 0, 5, true ),
		);
	}
}
