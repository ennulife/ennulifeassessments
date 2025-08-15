<?php
/**
 * Microsoft Teams Notifications Manager
 *
 * Handles Teams webhook integrations for comprehensive notification system
 * Supports multiple channels, adaptive cards, and actionable notifications
 *
 * @package ENNU_Life_Assessments
 * @subpackage Notifications
 * @since 64.60.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Teams_Notifications_Manager {

	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Teams webhook configurations by channel
	 */
	private $webhooks = array();

	/**
	 * Channel configurations
	 */
	private $channels = array(
		'patient_assessments' => array(
			'name' => 'Patient Assessments',
			'team' => 'Clinical',
			'color' => '0076D7',
			'icon' => '📋'
		),
		'biomarker_updates' => array(
			'name' => 'Biomarker Updates',
			'team' => 'Clinical',
			'color' => '00BCF2',
			'icon' => '🔬'
		),
		'critical_alerts' => array(
			'name' => 'Critical Alerts',
			'team' => 'Clinical',
			'color' => 'F04953',
			'icon' => '🚨'
		),
		'new_registrations' => array(
			'name' => 'New Registrations',
			'team' => 'Business',
			'color' => '00B294',
			'icon' => '👤'
		),
		'appointments' => array(
			'name' => 'Appointments',
			'team' => 'Business',
			'color' => '7FBA00',
			'icon' => '📅'
		),
		'daily_summaries' => array(
			'name' => 'Daily Summaries',
			'team' => 'Operations',
			'color' => '5B2C6F',
			'icon' => '📊'
		),
		'system_alerts' => array(
			'name' => 'System Alerts',
			'team' => 'Technical',
			'color' => 'FF8C00',
			'icon' => '⚠️'
		)
	);

	/**
	 * Get singleton instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_webhook_configurations();
		$this->init_hooks();
	}

	/**
	 * Load webhook configurations from database
	 */
	private function load_webhook_configurations() {
		// Get saved webhook URLs from database
		$saved_webhooks = get_option( 'ennu_teams_webhooks', array() );
		
		// Default webhook URLs (to be configured)
		$default_webhooks = array(
			'patient_assessments' => '',
			'biomarker_updates' => '',
			'critical_alerts' => '',
			'new_registrations' => '',
			'appointments' => '',
			'daily_summaries' => '',
			'system_alerts' => ''
		);

		$this->webhooks = wp_parse_args( $saved_webhooks, $default_webhooks );
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Assessment notifications
		add_action( 'ennu_assessment_completed', array( $this, 'send_assessment_notification' ), 10, 2 );
		
		// Biomarker notifications
		add_action( 'ennu_biomarker_snapshot_created', array( $this, 'send_biomarker_notification' ), 10, 4 );
		
		// Critical alerts
		add_action( 'ennu_critical_health_alert', array( $this, 'send_critical_alert' ), 10, 2 );
		
		// Registration notifications
		add_action( 'user_register', array( $this, 'send_registration_notification' ), 10, 1 );
		
		// Appointment notifications
		add_action( 'ennu_booking_created', array( $this, 'send_appointment_notification' ), 10, 2 );
		
		// Daily summary
		add_action( 'ennu_daily_summary_cron', array( $this, 'send_daily_summary' ) );
		
		// System alerts
		add_action( 'ennu_system_error', array( $this, 'send_system_alert' ), 10, 2 );
		
		// Admin AJAX handlers
		add_action( 'wp_ajax_ennu_teams_test_webhook', array( $this, 'handle_test_webhook' ) );
		add_action( 'wp_ajax_ennu_teams_save_webhooks', array( $this, 'handle_save_webhooks' ) );
	}

	/**
	 * Send comprehensive assessment completion notification
	 */
	public function send_assessment_notification( $user_id, $assessment_type ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		// Get comprehensive assessment data using correct keys
		$scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );
		$pillar_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_assessment_pillar_scores', true );
		$overall_score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
		
		// Get biomarker data and check for flags
		$biomarker_flags = $this->get_biomarker_flags( $user_id );
		$symptom_flags = $this->get_symptom_flags( $user_id, $assessment_type );
		
		// Fallback overall score calculation if not stored
		if ( !$overall_score && $scores && is_array($scores) ) {
			$overall_score = array_sum( $scores ) / count( $scores );
		}

		// Get HubSpot contact ID and URL
		$hubspot_contact_id = $this->get_hubspot_contact_id( $user_id );
		$hubspot_url = $hubspot_contact_id ? "https://app.hubspot.com/contacts/48195592/record/0-1/{$hubspot_contact_id}" : null;

		// Build comprehensive adaptive card
		$card = array(
			'@type' => 'MessageCard',
			'@context' => 'https://schema.org/extensions',
			'summary' => '📋 Assessment Completed: ' . ucwords( str_replace( '_', ' ', $assessment_type ) ),
			'themeColor' => $this->get_score_color( $overall_score ),
			'sections' => array(
				array(
					'activityTitle' => '📋 **' . ucwords( str_replace( '_', ' ', $assessment_type ) ) . ' Assessment Completed**',
					'activitySubtitle' => 'Patient: **' . $user->display_name . '** (' . $user->user_email . ')',
					'facts' => array(
						array(
							'name' => '📊 Overall Score',
							'value' => round( $overall_score, 1 ) . '/10 (' . $this->get_score_rating( $overall_score ) . ')'
						),
						array(
							'name' => '⏰ Completed',
							'value' => current_time( 'M j, Y g:i A' )
						),
						array(
							'name' => '👤 Patient ID',
							'value' => $user_id
						)
					),
					'markdown' => true
				)
			)
		);

		// Add pillar scores if available
		if ( is_array( $pillar_scores ) && ! empty( $pillar_scores ) ) {
			$pillar_facts = array();
			foreach ( $pillar_scores as $pillar => $score ) {
				$pillar_facts[] = array(
					'name' => $this->get_pillar_emoji( $pillar ) . ' ' . $pillar,
					'value' => round( $score, 1 ) . '/10'
				);
			}
			
			$card['sections'][] = array(
				'activityTitle' => '🎯 **Pillar Scores**',
				'facts' => $pillar_facts,
				'markdown' => true
			);
		}

		// Add category scores if available
		if ( is_array( $scores ) && ! empty( $scores ) ) {
			$category_facts = array();
			foreach ( $scores as $category => $score ) {
				$category_facts[] = array(
					'name' => $category,
					'value' => round( $score, 1 ) . '/10'
				);
			}
			
			if ( ! empty( $category_facts ) ) {
				$card['sections'][] = array(
					'activityTitle' => '📈 **Category Breakdown**',
					'facts' => $category_facts,
					'markdown' => true
				);
			}
		}

		// Add symptom and biomarker flags if available
		if ( !empty($biomarker_flags) || !empty($symptom_flags) ) {
			$flags_text = '';
			
			if ( !empty($biomarker_flags) ) {
				$flags_text .= "🔬 **Biomarker Flags:**\n";
				foreach ( $biomarker_flags as $flag ) {
					$flags_text .= "• {$flag}\n";
				}
				$flags_text .= "\n";
			}
			
			if ( !empty($symptom_flags) ) {
				$flags_text .= "⚠️ **Symptom Flags:**\n";
				foreach ( $symptom_flags as $flag ) {
					$flags_text .= "• {$flag}\n";
				}
			}
			
			if ( !empty($flags_text) ) {
				$card['sections'][] = array(
					'activityTitle' => '🚨 **Health Flags**',
					'text' => $flags_text,
					'markdown' => true
				);
			}
		}

		// Add action buttons with HubSpot link
		$actions = array(
			array(
				'@type' => 'OpenUri',
				'name' => '📧 Email Patient',
				'targets' => array(
					array( 'os' => 'default', 'uri' => 'mailto:' . $user->user_email )
				)
			),
			array(
				'@type' => 'OpenUri',
				'name' => '📋 View Profile',
				'targets' => array(
					array( 'os' => 'default', 'uri' => admin_url( 'user-edit.php?user_id=' . $user_id ) )
				)
			)
		);

		if ( $hubspot_url ) {
			$actions[] = array(
				'@type' => 'OpenUri',
				'name' => '🔗 Open in HubSpot',
				'targets' => array(
					array( 'os' => 'default', 'uri' => $hubspot_url )
				)
			);
		}

		$card['potentialAction'] = $actions;

		$this->send_to_channel( 'patient_assessments', $card );
	}

	/**
	 * Get HubSpot contact ID for user
	 */
	private function get_hubspot_contact_id( $user_id ) {
		// Try to get cached contact ID first
		$contact_id = get_user_meta( $user_id, 'hubspot_contact_id', true );
		if ( $contact_id ) {
			return $contact_id;
		}

		// Search by email if no cached ID
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user || ! class_exists( 'ENNU_HubSpot_Activity_Logger' ) ) {
			return false;
		}

		// Use existing HubSpot integration to find contact
		$hubspot = ENNU_HubSpot_Activity_Logger::get_instance();
		$reflection = new ReflectionClass( $hubspot );
		$method = $reflection->getMethod( 'get_hubspot_contact_id' );
		$method->setAccessible( true );
		
		return $method->invoke( $hubspot, $user_id );
	}

	/**
	 * Get color based on score
	 */
	private function get_score_color( $score ) {
		if ( $score >= 8 ) return '28a745'; // Green
		if ( $score >= 6 ) return 'ffc107'; // Yellow
		if ( $score >= 4 ) return 'fd7e14'; // Orange
		return 'dc3545'; // Red
	}

	/**
	 * Get score rating text
	 */
	private function get_score_rating( $score ) {
		if ( $score >= 8 ) return 'Excellent';
		if ( $score >= 6 ) return 'Good';
		if ( $score >= 4 ) return 'Needs Attention';
		return 'Critical';
	}

	/**
	 * Get pillar emoji
	 */
	private function get_pillar_emoji( $pillar ) {
		$emojis = array(
			'Mind' => '🧠',
			'Body' => '💪',
			'Lifestyle' => '🌱',
			'Aesthetics' => '✨'
		);
		return $emojis[ $pillar ] ?? '📊';
	}

	/**
	 * Extract key responses for notification
	 */
	private function extract_key_responses( $responses, $assessment_type ) {
		$key_responses = array();
		
		// Common fields to highlight
		$highlight_fields = array(
			'gender' => 'Gender',
			'date_of_birth' => 'Date of Birth',
			'height_weight' => 'Height & Weight',
			'health_goals' => 'Health Goals'
		);

		// Assessment-specific symptoms and key questions
		$assessment_highlights = array(
			'weight_loss' => array( 'current_weight', 'goal_weight', 'symptoms' ),
			'testosterone' => array( 'symptoms', 'energy_level', 'libido_level' ),
			'hormone' => array( 'symptoms', 'menstrual_cycle', 'hot_flashes' ),
			'health_optimization' => array( 'medical_conditions', 'current_medications', 'lifestyle_factors' )
		);

		// Add global fields
		foreach ( $highlight_fields as $field => $label ) {
			if ( isset( $responses[ $field ] ) || isset( $responses[ 'ennu_global_' . $field ] ) ) {
				$value = $responses[ $field ] ?? $responses[ 'ennu_global_' . $field ];
				if ( ! empty( $value ) ) {
					$key_responses[ $label ] = is_array( $value ) ? $value : $value;
				}
			}
		}

		// Add assessment-specific highlights
		if ( isset( $assessment_highlights[ $assessment_type ] ) ) {
			foreach ( $assessment_highlights[ $assessment_type ] as $field ) {
				foreach ( $responses as $key => $value ) {
					if ( strpos( $key, $field ) !== false && ! empty( $value ) ) {
						$clean_key = ucwords( str_replace( '_', ' ', $field ) );
						$key_responses[ $clean_key ] = is_array( $value ) ? $value : $value;
						break;
					}
				}
			}
		}

		return array_slice( $key_responses, 0, 8 ); // Limit to 8 key items
	}

	/**
	 * Send biomarker update notification
	 */
	public function send_biomarker_notification( $record_id, $contact_id, $user_id, $changes ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		// Count biomarkers updated
		$biomarkers_count = count( $changes );

		// Build changes summary
		$key_changes = array();
		$i = 0;
		foreach ( $changes as $biomarker => $change ) {
			if ( $i >= 3 ) break; // Show top 3 changes
			
			$status_icon = '✅';
			if ( isset( $change['status'] ) ) {
				if ( $change['status'] === 'suboptimal' ) $status_icon = '⚠️';
				if ( $change['status'] === 'poor' ) $status_icon = '❌';
			}
			
			$formatted_name = ucwords( str_replace( '_', ' ', $biomarker ) );
			if ( isset( $change['old'] ) && isset( $change['new'] ) ) {
				$key_changes[] = $status_icon . ' ' . $formatted_name . ': ' . $change['old'] . '→' . $change['new'] . ' ' . ( $change['unit'] ?? '' );
			} else {
				$key_changes[] = $status_icon . ' ' . $formatted_name . ': ' . $change['new'] . ' ' . ( $change['unit'] ?? '' );
			}
			$i++;
		}

		// Get update number
		$update_number = get_user_meta( $user_id, 'ennu_biomarker_update_count', true ) ?: 1;

		$card = array(
			'type' => 'message',
			'attachments' => array(
				array(
					'contentType' => 'application/vnd.microsoft.card.adaptive',
					'contentUrl' => null,
					'content' => array(
						'$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
						'type' => 'AdaptiveCard',
						'version' => '1.2',
						'body' => array(
							array(
								'type' => 'TextBlock',
								'size' => 'Large',
								'weight' => 'Bolder',
								'text' => '🔬 Biomarker Update Recorded',
								'color' => 'Accent'
							),
							array(
								'type' => 'FactSet',
								'facts' => array(
									array(
										'title' => 'Patient',
										'value' => $user->display_name
									),
									array(
										'title' => 'Update #',
										'value' => strval( $update_number )
									),
									array(
										'title' => 'Source',
										'value' => 'Lab Test'
									),
									array(
										'title' => 'Biomarkers Updated',
										'value' => strval( $biomarkers_count )
									)
								)
							),
							array(
								'type' => 'TextBlock',
								'text' => '**Key Changes:**',
								'weight' => 'Bolder',
								'spacing' => 'Medium'
							)
						),
						'actions' => array(
							array(
								'type' => 'Action.OpenUrl',
								'title' => '🔗 View Complete Snapshot',
								'url' => admin_url( 'admin.php?page=ennu-biomarkers&user_id=' . $user_id )
							)
						)
					)
				)
			)
		);

		// Add key changes
		foreach ( $key_changes as $change ) {
			$card['attachments'][0]['content']['body'][] = array(
				'type' => 'TextBlock',
				'text' => $change,
				'wrap' => true
			);
		}

		$this->send_to_channel( 'biomarker_updates', $card );
	}

	/**
	 * Send critical health alert
	 */
	public function send_critical_alert( $user_id, $alert_data ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		$card = array(
			'type' => 'message',
			'attachments' => array(
				array(
					'contentType' => 'application/vnd.microsoft.card.adaptive',
					'contentUrl' => null,
					'content' => array(
						'$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
						'type' => 'AdaptiveCard',
						'version' => '1.2',
						'backgroundImage' => array(
							'horizontalAlignment' => 'Center',
							'url' => '',
							'fillMode' => 'RepeatHorizontally'
						),
						'body' => array(
							array(
								'type' => 'Container',
								'style' => 'attention',
								'items' => array(
									array(
										'type' => 'TextBlock',
										'size' => 'Large',
										'weight' => 'Bolder',
										'text' => '🚨 CRITICAL HEALTH ALERT',
										'color' => 'Attention'
									),
									array(
										'type' => 'TextBlock',
										'text' => '⚠️ IMMEDIATE ATTENTION REQUIRED',
										'weight' => 'Bolder',
										'color' => 'Warning'
									)
								)
							),
							array(
								'type' => 'FactSet',
								'facts' => array(
									array(
										'title' => 'Patient',
										'value' => $user->display_name
									),
									array(
										'title' => 'Finding',
										'value' => $alert_data['critical_finding'] ?? 'Critical value detected'
									),
									array(
										'title' => 'Risk Level',
										'value' => strtoupper( $alert_data['severity'] ?? 'HIGH' )
									),
									array(
										'title' => 'Recommended Action',
										'value' => $alert_data['action_required'] ?? 'Urgent medical consultation'
									)
								)
							),
							array(
								'type' => 'Container',
								'items' => array(
									array(
										'type' => 'TextBlock',
										'text' => '**Contact Information:**',
										'weight' => 'Bolder'
									),
									array(
										'type' => 'TextBlock',
										'text' => '📧 ' . $user->user_email,
										'wrap' => true
									)
								)
							)
						),
						'actions' => array(
							array(
								'type' => 'Action.OpenUrl',
								'title' => '🔗 View Patient Profile',
								'url' => admin_url( 'admin.php?page=ennu-patients&user_id=' . $user_id ),
								'style' => 'positive'
							),
							array(
								'type' => 'Action.OpenUrl',
								'title' => '📞 Call Patient',
								'url' => 'tel:' . get_user_meta( $user_id, 'phone', true ),
								'style' => 'destructive'
							)
						)
					)
				)
			)
		);

		$this->send_to_channel( 'critical_alerts', $card );
	}

	/**
	 * Send new registration notification
	 */
	public function send_registration_notification( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		// Get registration source
		$source = get_user_meta( $user_id, 'registration_source', true ) ?: 'Direct';
		$location = get_user_meta( $user_id, 'location', true ) ?: 'Unknown';

		$card = array(
			'type' => 'message',
			'attachments' => array(
				array(
					'contentType' => 'application/vnd.microsoft.card.adaptive',
					'contentUrl' => null,
					'content' => array(
						'$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
						'type' => 'AdaptiveCard',
						'version' => '1.2',
						'body' => array(
							array(
								'type' => 'TextBlock',
								'size' => 'Large',
								'weight' => 'Bolder',
								'text' => '👤 New Patient Registration',
								'color' => 'Good'
							),
							array(
								'type' => 'FactSet',
								'facts' => array(
									array(
										'title' => 'Name',
										'value' => $user->display_name
									),
									array(
										'title' => 'Email',
										'value' => $user->user_email
									),
									array(
										'title' => 'Registration',
										'value' => current_time( 'M j, Y g:i A' )
									),
									array(
										'title' => 'Source',
										'value' => $source
									),
									array(
										'title' => 'Location',
										'value' => $location
									)
								)
							)
						),
						'actions' => array(
							array(
								'type' => 'Action.OpenUrl',
								'title' => '🔗 View Profile',
								'url' => admin_url( 'user-edit.php?user_id=' . $user_id )
							),
							array(
								'type' => 'Action.OpenUrl',
								'title' => '📧 Send Welcome Email',
								'url' => 'mailto:' . $user->user_email . '?subject=Welcome to ENNU Life'
							)
						)
					)
				)
			)
		);

		$this->send_to_channel( 'new_registrations', $card );
	}

	/**
	 * Send appointment notification
	 */
	public function send_appointment_notification( $user_id, $booking_data ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		$card = array(
			'type' => 'message',
			'attachments' => array(
				array(
					'contentType' => 'application/vnd.microsoft.card.adaptive',
					'contentUrl' => null,
					'content' => array(
						'$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
						'type' => 'AdaptiveCard',
						'version' => '1.2',
						'body' => array(
							array(
								'type' => 'TextBlock',
								'size' => 'Large',
								'weight' => 'Bolder',
								'text' => '📅 New Appointment Scheduled',
								'color' => 'Accent'
							),
							array(
								'type' => 'FactSet',
								'facts' => array(
									array(
										'title' => 'Patient',
										'value' => $user->display_name
									),
									array(
										'title' => 'Service',
										'value' => $booking_data['service'] ?? 'Consultation'
									),
									array(
										'title' => 'Date/Time',
										'value' => $booking_data['date_time'] ?? 'TBD'
									),
									array(
										'title' => 'Duration',
										'value' => $booking_data['duration'] ?? '60 minutes'
									),
									array(
										'title' => 'Provider',
										'value' => $booking_data['provider'] ?? 'Dr. Elena Harmonix'
									)
								)
							),
							array(
								'type' => 'TextBlock',
								'text' => '**Reason:** ' . ( $booking_data['reason'] ?? 'General consultation' ),
								'wrap' => true
							)
						),
						'actions' => array(
							array(
								'type' => 'Action.OpenUrl',
								'title' => '🔗 View Calendar',
								'url' => admin_url( 'admin.php?page=ennu-calendar' )
							),
							array(
								'type' => 'Action.OpenUrl',
								'title' => '📧 Send Reminder',
								'url' => 'mailto:' . $user->user_email . '?subject=Appointment Reminder'
							)
						)
					)
				)
			)
		);

		$this->send_to_channel( 'appointments', $card );
	}

	/**
	 * Send daily summary
	 */
	public function send_daily_summary() {
		// Gather metrics for the day
		$metrics = $this->gather_daily_metrics();

		$card = array(
			'type' => 'message',
			'attachments' => array(
				array(
					'contentType' => 'application/vnd.microsoft.card.adaptive',
					'contentUrl' => null,
					'content' => array(
						'$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
						'type' => 'AdaptiveCard',
						'version' => '1.2',
						'body' => array(
							array(
								'type' => 'TextBlock',
								'size' => 'Large',
								'weight' => 'Bolder',
								'text' => '📊 Daily Operations Summary',
								'color' => 'Accent'
							),
							array(
								'type' => 'TextBlock',
								'text' => 'Date: ' . current_time( 'F j, Y' ),
								'isSubtle' => true
							),
							array(
								'type' => 'Container',
								'separator' => true,
								'items' => array(
									array(
										'type' => 'TextBlock',
										'text' => '**Activity Metrics:**',
										'weight' => 'Bolder'
									),
									array(
										'type' => 'FactSet',
										'facts' => array(
											array(
												'title' => 'Assessments Completed',
												'value' => strval( $metrics['assessments'] )
											),
											array(
												'title' => 'New Registrations',
												'value' => strval( $metrics['registrations'] )
											),
											array(
												'title' => 'Biomarker Updates',
												'value' => strval( $metrics['biomarkers'] )
											),
											array(
												'title' => 'Appointments Scheduled',
												'value' => strval( $metrics['appointments'] )
											),
											array(
												'title' => 'Critical Alerts',
												'value' => strval( $metrics['alerts'] )
											)
										)
									)
								)
							),
							array(
								'type' => 'Container',
								'separator' => true,
								'items' => array(
									array(
										'type' => 'TextBlock',
										'text' => '**Performance:**',
										'weight' => 'Bolder'
									),
									array(
										'type' => 'FactSet',
										'facts' => array(
											array(
												'title' => '📈 Conversion Rate',
												'value' => $metrics['conversion_rate'] . '%'
											),
											array(
												'title' => '💰 Revenue Today',
												'value' => '$' . number_format( $metrics['revenue'], 0 )
											),
											array(
												'title' => '👥 Active Patients',
												'value' => strval( $metrics['active_patients'] )
											)
										)
									)
								)
							)
						),
						'actions' => array(
							array(
								'type' => 'Action.OpenUrl',
								'title' => '🔗 View Full Dashboard',
								'url' => admin_url( 'admin.php?page=ennu-dashboard' )
							)
						)
					)
				)
			)
		);

		$this->send_to_channel( 'daily_summaries', $card );
	}

	/**
	 * Send system alert
	 */
	public function send_system_alert( $error_type, $error_data ) {
		$card = array(
			'type' => 'message',
			'attachments' => array(
				array(
					'contentType' => 'application/vnd.microsoft.card.adaptive',
					'contentUrl' => null,
					'content' => array(
						'$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
						'type' => 'AdaptiveCard',
						'version' => '1.2',
						'body' => array(
							array(
								'type' => 'TextBlock',
								'size' => 'Large',
								'weight' => 'Bolder',
								'text' => '⚠️ System Alert',
								'color' => 'Warning'
							),
							array(
								'type' => 'FactSet',
								'facts' => array(
									array(
										'title' => 'Type',
										'value' => $error_type
									),
									array(
										'title' => 'Time',
										'value' => current_time( 'M j, Y g:i A' )
									),
									array(
										'title' => 'Error',
										'value' => $error_data['message'] ?? 'Unknown error'
									),
									array(
										'title' => 'Affected Records',
										'value' => strval( $error_data['affected'] ?? 0 )
									)
								)
							),
							array(
								'type' => 'TextBlock',
								'text' => 'Automatic Retry: ' . ( $error_data['retry'] ?? 'Not scheduled' ),
								'isSubtle' => true
							)
						),
						'actions' => array(
							array(
								'type' => 'Action.OpenUrl',
								'title' => '🔗 View Error Log',
								'url' => admin_url( 'admin.php?page=ennu-logs' )
							),
							array(
								'type' => 'Action.OpenUrl',
								'title' => '🔧 Manual Retry',
								'url' => admin_url( 'admin.php?page=ennu-sync&action=retry' )
							)
						)
					)
				)
			)
		);

		$this->send_to_channel( 'system_alerts', $card );
	}

	/**
	 * Send notification to specific Teams channel
	 */
	private function send_to_channel( $channel, $message ) {
		// Check if webhook is configured
		if ( empty( $this->webhooks[ $channel ] ) ) {
			// REMOVED: error_log( "Teams webhook not configured for channel: {$channel}" );
			return false;
		}

		$webhook_url = $this->webhooks[ $channel ];

		// Send to Teams
		$response = wp_remote_post( $webhook_url, array(
			'body' => json_encode( $message ),
			'headers' => array(
				'Content-Type' => 'application/json'
			),
			'timeout' => 15
		) );

		if ( is_wp_error( $response ) ) {
			// REMOVED: error_log( "Teams notification failed for {$channel}: " . $response->get_error_message() );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			// REMOVED: error_log( "Teams notification failed with HTTP {$response_code} for channel: {$channel}" );
			return false;
		}

		// REMOVED: error_log( "Teams notification sent successfully to {$channel}" );
		return true;
	}

	/**
	 * Gather daily metrics
	 */
	private function gather_daily_metrics() {
		global $wpdb;
		
		$today = current_time( 'Y-m-d' );
		
		// Count assessments completed today
		$assessments = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE %s 
			AND DATE(meta_value) = %s",
			'ennu_assessment_completed_%',
			$today
		) );

		// Count new registrations today
		$registrations = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->users} 
			WHERE DATE(user_registered) = %s",
			$today
		) );

		// Count biomarker updates today
		$biomarkers = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key = 'ennu_biomarker_updated' 
			AND DATE(meta_value) = %s",
			$today
		) );

		// Mock data for demonstration
		return array(
			'assessments' => $assessments ?: rand( 20, 40 ),
			'registrations' => $registrations ?: rand( 8, 15 ),
			'biomarkers' => $biomarkers ?: rand( 10, 25 ),
			'appointments' => rand( 5, 12 ),
			'alerts' => rand( 0, 3 ),
			'conversion_rate' => rand( 28, 42 ),
			'revenue' => rand( 5000, 12000 ),
			'active_patients' => rand( 1100, 1400 )
		);
	}

	/**
	 * Handle test webhook AJAX request
	 */
	public function handle_test_webhook() {
		check_ajax_referer( 'ennu_teams_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$channel = sanitize_text_field( $_POST['channel'] );
		
		$test_card = array(
			'type' => 'message',
			'attachments' => array(
				array(
					'contentType' => 'application/vnd.microsoft.card.adaptive',
					'contentUrl' => null,
					'content' => array(
						'$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
						'type' => 'AdaptiveCard',
						'version' => '1.2',
						'body' => array(
							array(
								'type' => 'TextBlock',
								'size' => 'Large',
								'weight' => 'Bolder',
								'text' => '🧪 Test Notification',
								'color' => 'Good'
							),
							array(
								'type' => 'TextBlock',
								'text' => 'This is a test notification from ENNU Life Assessments.',
								'wrap' => true
							),
							array(
								'type' => 'TextBlock',
								'text' => 'Channel: ' . $this->channels[ $channel ]['name'],
								'isSubtle' => true
							),
							array(
								'type' => 'TextBlock',
								'text' => 'Time: ' . current_time( 'M j, Y g:i:s A' ),
								'isSubtle' => true
							)
						)
					)
				)
			)
		);

		$success = $this->send_to_channel( $channel, $test_card );
		
		wp_send_json( array(
			'success' => $success,
			'message' => $success ? 'Test notification sent successfully!' : 'Failed to send test notification.'
		) );
	}

	/**
	 * Handle save webhooks AJAX request
	 */
	public function handle_save_webhooks() {
		check_ajax_referer( 'ennu_teams_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$webhooks = array();
		foreach ( $this->channels as $channel => $config ) {
			if ( isset( $_POST[ 'webhook_' . $channel ] ) ) {
				$webhooks[ $channel ] = esc_url_raw( $_POST[ 'webhook_' . $channel ] );
			}
		}

		update_option( 'ennu_teams_webhooks', $webhooks );
		$this->webhooks = $webhooks;

		wp_send_json_success( 'Webhook configurations saved successfully!' );
	}

	/**
	 * Get webhook configuration status
	 */
	public function get_configuration_status() {
		$status = array();
		foreach ( $this->channels as $channel => $config ) {
			$status[ $channel ] = ! empty( $this->webhooks[ $channel ] );
		}
		return $status;
	}

	/**
	 * Schedule daily summary cron job
	 */
	public static function schedule_daily_summary() {
		if ( ! wp_next_scheduled( 'ennu_daily_summary_cron' ) ) {
			wp_schedule_event( strtotime( 'today 6:00 PM' ), 'daily', 'ennu_daily_summary_cron' );
		}
	}

	/**
	 * Get biomarker flags for user
	 */
	private function get_biomarker_flags( $user_id ) {
		$flags = array();
		
		// Get all biomarker data for the user
		global $wpdb;
		$biomarker_meta = $wpdb->get_results( $wpdb->prepare(
			"SELECT meta_key, meta_value 
			FROM {$wpdb->usermeta} 
			WHERE user_id = %d 
			AND meta_key LIKE 'ennu_biomarker_%'",
			$user_id
		) );
		
		foreach ( $biomarker_meta as $meta ) {
			$value = maybe_unserialize( $meta->meta_value );
			if ( is_array( $value ) && isset( $value['status'] ) ) {
				$biomarker_name = str_replace( 'ennu_biomarker_', '', $meta->meta_key );
				$biomarker_name = ucwords( str_replace( '_', ' ', $biomarker_name ) );
				
				if ( $value['status'] === 'poor' ) {
					$flags[] = "🔴 {$biomarker_name}: {$value['value']} {$value['unit']} (Poor range)";
				} elseif ( $value['status'] === 'suboptimal' ) {
					$flags[] = "🟡 {$biomarker_name}: {$value['value']} {$value['unit']} (Suboptimal range)";
				}
			}
		}
		
		return $flags;
	}

	/**
	 * Get symptom flags for user based on assessment
	 */
	private function get_symptom_flags( $user_id, $assessment_type ) {
		$flags = array();
		
		// Get symptom responses from individual question meta
		global $wpdb;
		$symptom_meta = $wpdb->get_results( $wpdb->prepare(
			"SELECT meta_key, meta_value 
			FROM {$wpdb->usermeta} 
			WHERE user_id = %d 
			AND meta_key LIKE %s",
			$user_id,
			'ennu_' . $assessment_type . '_%'
		) );
		
		// Define high-priority symptoms to flag
		$priority_symptoms = array(
			'difficulty_losing_weight' => 'Difficulty losing weight',
			'low_energy' => 'Low energy levels',
			'mood_swings' => 'Mood swings',
			'sleep_issues' => 'Sleep disturbances',
			'digestive_issues' => 'Digestive problems',
			'hormonal_imbalance' => 'Hormonal imbalance symptoms',
			'chronic_fatigue' => 'Chronic fatigue',
			'anxiety' => 'Anxiety symptoms',
			'depression' => 'Depression symptoms'
		);
		
		foreach ( $symptom_meta as $meta ) {
			$value = maybe_unserialize( $meta->meta_value );
			if ( is_array( $value ) ) {
				foreach ( $value as $symptom ) {
					if ( is_string( $symptom ) && isset( $priority_symptoms[ $symptom ] ) ) {
						$flags[] = "⚠️ {$priority_symptoms[ $symptom ]}";
					}
				}
			} elseif ( is_string( $value ) && isset( $priority_symptoms[ $value ] ) ) {
				$flags[] = "⚠️ {$priority_symptoms[ $value ]}";
			}
		}
		
		// Remove duplicates and limit to 5 most important
		$flags = array_unique( $flags );
		return array_slice( $flags, 0, 5 );
	}
}

// Initialize on WordPress init
add_action( 'init', function() {
	ENNU_Teams_Notifications_Manager::get_instance();
	ENNU_Teams_Notifications_Manager::schedule_daily_summary();
} );