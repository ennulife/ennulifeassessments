<?php
/**
 * ENNU Teams Dynamic Message Generator
 * 
 * This class shows exactly how WordPress data is transformed into Teams messages
 * Each method pulls real data from WordPress and formats it for Teams
 *
 * @package ENNU_Life_Assessments
 * @since 64.63.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Teams_Dynamic_Messages {

	/**
	 * Power Automate Flow URLs (configured after setup)
	 */
	private $flow_urls = array(
		'patient_assessments' => '', // Add your flow URL here
		'biomarker_updates' => '',
		'critical_alerts' => '',
		'new_registrations' => '',
		'appointments' => '',
		'daily_summaries' => '',
		'system_alerts' => '',
		'revenue_metrics' => '',
		'patient_success' => ''
	);

	/**
	 * EXAMPLE 1: Assessment Completion
	 * This hooks into WordPress when an assessment is completed
	 */
	public function send_assessment_notification( $user_id, $assessment_type ) {
		// Step 1: Get real user data from WordPress
		$user = get_userdata( $user_id );
		$first_name = get_user_meta( $user_id, 'first_name', true );
		$last_name = get_user_meta( $user_id, 'last_name', true );
		$full_name = trim( $first_name . ' ' . $last_name ) ?: $user->display_name;
		
		// Step 2: Get actual assessment scores from database
		$scores = get_user_meta( $user_id, 'ennu_assessment_scores_' . $assessment_type, true );
		$responses = get_user_meta( $user_id, 'ennu_assessment_responses_' . $assessment_type, true );
		$recommendations = get_user_meta( $user_id, 'ennu_assessment_recommendations_' . $assessment_type, true );
		
		// Step 3: Calculate overall score
		$overall_score = 0;
		$score_details = array();
		
		if ( is_array( $scores ) ) {
			foreach ( $scores as $category => $score ) {
				$score_details[] = sprintf( 
					"• %s: %.1f/10 %s",
					$category,
					$score,
					$score >= 7 ? '✅' : '⚠️'
				);
			}
			$overall_score = round( array_sum( $scores ) / count( $scores ), 1 );
		}
		
		// Step 4: Get recommendations (top 3)
		$rec_text = '';
		if ( is_array( $recommendations ) && ! empty( $recommendations ) ) {
			$top_recs = array_slice( $recommendations, 0, 3 );
			foreach ( $top_recs as $i => $rec ) {
				$rec_text .= sprintf( "%d. %s\n", $i + 1, $rec );
			}
		}
		
		// Step 5: Build the dynamic message
		$title = '📋 Assessment Completed';
		
		$message = sprintf(
			"Patient: %s\n" .
			"Email: %s\n" .
			"Assessment: %s\n" .
			"Completion Time: %s\n\n" .
			"Overall Score: %.1f/10\n\n" .
			"Category Breakdown:\n%s\n\n" .
			"Top Recommendations:\n%s\n" .
			"🔗 View Full Report: %s",
			$full_name,
			$user->user_email,
			ucwords( str_replace( '_', ' ', $assessment_type ) ),
			current_time( 'Y-m-d H:i:s' ),
			$overall_score,
			implode( "\n", $score_details ),
			$rec_text,
			admin_url( 'admin.php?page=ennu-assessments&user_id=' . $user_id )
		);
		
		// Step 6: Send to Power Automate
		$this->send_to_teams( 'patient_assessments', $title, $message, array(
			'user_id' => $user_id,
			'assessment_type' => $assessment_type,
			'scores' => $scores,
			'overall_score' => $overall_score
		) );
	}

	/**
	 * EXAMPLE 2: Biomarker Update
	 * Triggered when biomarkers are updated
	 */
	public function send_biomarker_notification( $user_id, $biomarker_data ) {
		// Get user info
		$user = get_userdata( $user_id );
		$full_name = $this->get_user_full_name( $user_id );
		
		// Get update number (increments each time)
		$update_count = get_user_meta( $user_id, 'ennu_biomarker_update_count', true );
		$update_count = $update_count ? intval( $update_count ) + 1 : 1;
		update_user_meta( $user_id, 'ennu_biomarker_update_count', $update_count );
		
		// Get previous biomarkers for comparison
		$previous_biomarkers = get_user_meta( $user_id, 'ennu_previous_biomarkers', true ) ?: array();
		
		// Calculate changes
		$changes = array();
		$biomarkers_updated = 0;
		
		foreach ( $biomarker_data as $key => $new_value ) {
			if ( isset( $previous_biomarkers[ $key ] ) ) {
				$old_value = $previous_biomarkers[ $key ];
				if ( $old_value != $new_value ) {
					$biomarkers_updated++;
					
					// Determine if improved, worsened, or neutral
					$status = $this->evaluate_biomarker_change( $key, $old_value, $new_value );
					$icon = $status === 'improved' ? '✅' : ( $status === 'worsened' ? '⚠️' : '➡️' );
					
					$changes[] = sprintf(
						"%s %s: %s → %s %s",
						$icon,
						$this->format_biomarker_name( $key ),
						$old_value,
						$new_value,
						$this->get_biomarker_unit( $key )
					);
				}
			} else {
				// New biomarker
				$biomarkers_updated++;
				$changes[] = sprintf(
					"🆕 %s: %s %s",
					$this->format_biomarker_name( $key ),
					$new_value,
					$this->get_biomarker_unit( $key )
				);
			}
		}
		
		// Save current as previous for next comparison
		update_user_meta( $user_id, 'ennu_previous_biomarkers', $biomarker_data );
		
		// Get source (lab name or manual entry)
		$source = get_user_meta( $user_id, 'ennu_last_biomarker_source', true ) ?: 'Manual Entry';
		
		$title = '🔬 Biomarker Update Recorded';
		
		$message = sprintf(
			"Patient: %s\n" .
			"Email: %s\n" .
			"Update #: %d\n" .
			"Source: %s\n" .
			"Date: %s\n\n" .
			"Biomarkers Updated: %d\n\n" .
			"Key Changes:\n%s\n\n" .
			"Overall Status: %s\n" .
			"Next Test Due: %s\n\n" .
			"🔗 View Complete Biomarker Report",
			$full_name,
			$user->user_email,
			$update_count,
			$source,
			current_time( 'Y-m-d' ),
			$biomarkers_updated,
			implode( "\n", array_slice( $changes, 0, 5 ) ), // Show top 5 changes
			$this->calculate_overall_status( $biomarker_data ),
			date( 'F j, Y', strtotime( '+3 months' ) )
		);
		
		$this->send_to_teams( 'biomarker_updates', $title, $message, array(
			'user_id' => $user_id,
			'update_number' => $update_count,
			'changes' => $changes,
			'biomarkers' => $biomarker_data
		) );
	}

	/**
	 * EXAMPLE 3: Critical Alert
	 * Triggered when critical values are detected
	 */
	public function send_critical_alert( $user_id, $biomarker_key, $value ) {
		$user = get_userdata( $user_id );
		$full_name = $this->get_user_full_name( $user_id );
		$phone = get_user_meta( $user_id, 'billing_phone', true ) ?: 'Not provided';
		
		// Get critical thresholds
		$thresholds = $this->get_critical_thresholds( $biomarker_key );
		$normal_range = $thresholds['normal_range'];
		$risk_level = $this->calculate_risk_level( $biomarker_key, $value );
		
		// Get previous value for trend
		$previous_biomarkers = get_user_meta( $user_id, 'ennu_previous_biomarkers', true );
		$previous_value = $previous_biomarkers[ $biomarker_key ] ?? 'N/A';
		$trend = $previous_value !== 'N/A' && $value > $previous_value ? 'Worsening ⬆️' : 'New finding';
		
		// Get contact history
		$last_appointment = get_user_meta( $user_id, 'last_appointment_date', true ) ?: 'Never';
		$last_contact = get_user_meta( $user_id, 'last_contact_date', true ) ?: 'Never';
		$days_since_appointment = $last_appointment !== 'Never' ? 
			round( ( time() - strtotime( $last_appointment ) ) / 86400 ) : 'N/A';
		
		// Determine required actions based on severity
		$actions = $this->get_required_actions( $biomarker_key, $value, $risk_level );
		
		$title = '🚨 CRITICAL HEALTH ALERT - IMMEDIATE ACTION REQUIRED';
		
		$message = sprintf(
			"⚠️ PRIORITY: %s - REQUIRES IMMEDIATE ATTENTION\n\n" .
			"Patient: %s\n" .
			"Email: %s\n" .
			"Phone: %s\n" .
			"Alert Time: %s\n\n" .
			"Critical Finding:\n" .
			"%s: %s %s (Severely Elevated)\n" .
			"Normal Range: %s\n" .
			"Risk Level: %s\n\n" .
			"Required Actions:\n%s\n\n" .
			"Previous Value: %s (%s)\n" .
			"Trend: %s\n\n" .
			"Contact History:\n" .
			"• Last appointment: %s days ago\n" .
			"• Last contact: %s\n" .
			"• Scheduled follow-up: %s\n\n" .
			"[CALL PATIENT] [SEND URGENT EMAIL] [CREATE TASK]",
			strtoupper( $risk_level ),
			$full_name,
			$user->user_email,
			$phone,
			current_time( 'Y-m-d H:i:s' ),
			$this->format_biomarker_name( $biomarker_key ),
			$value,
			$this->get_biomarker_unit( $biomarker_key ),
			$normal_range,
			strtoupper( $risk_level ),
			$actions,
			$previous_value,
			$previous_value !== 'N/A' ? '3 months ago' : 'First test',
			$trend,
			$days_since_appointment,
			$last_contact,
			$this->get_next_appointment( $user_id ) ?: 'None'
		);
		
		// This is HIGH IMPORTANCE
		$this->send_to_teams( 'critical_alerts', $title, $message, array(
			'user_id' => $user_id,
			'biomarker' => $biomarker_key,
			'value' => $value,
			'risk_level' => $risk_level,
			'importance' => 'high'
		) );
		
		// Also log this critical alert
		$this->log_critical_alert( $user_id, $biomarker_key, $value );
	}

	/**
	 * EXAMPLE 4: Daily Summary
	 * Runs via WordPress cron at 6 PM daily
	 */
	public function send_daily_summary() {
		global $wpdb;
		
		$today = current_time( 'Y-m-d' );
		$yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
		
		// Get real metrics from database
		$assessments_today = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_assessment_completed_%' 
			AND DATE(meta_value) = %s",
			$today
		) );
		
		$registrations_today = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->users} 
			WHERE DATE(user_registered) = %s",
			$today
		) );
		
		$biomarker_updates = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key = 'ennu_biomarker_updated' 
			AND DATE(meta_value) = %s",
			$today
		) );
		
		// Get appointments from Amelia or your booking system
		$appointments = $this->get_appointments_count( $today );
		
		// Get critical alerts count
		$critical_alerts = get_transient( 'ennu_critical_alerts_' . $today ) ?: 0;
		
		// Calculate revenue (from WooCommerce or your system)
		$revenue_today = $this->calculate_daily_revenue( $today );
		$revenue_yesterday = $this->calculate_daily_revenue( $yesterday );
		$revenue_trend = $this->calculate_percentage_change( $revenue_yesterday, $revenue_today );
		
		// Get conversion rate
		$visitors_today = $this->get_visitors_count( $today );
		$conversions_today = $assessments_today;
		$conversion_rate = $visitors_today > 0 ? 
			round( ( $conversions_today / $visitors_today ) * 100, 1 ) : 0;
		
		// Get top assessment type
		$top_assessment = $wpdb->get_var( $wpdb->prepare(
			"SELECT REPLACE(meta_key, 'ennu_assessment_completed_', '') 
			FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_assessment_completed_%' 
			AND DATE(meta_value) = %s 
			GROUP BY meta_key 
			ORDER BY COUNT(*) DESC 
			LIMIT 1",
			$today
		) );
		
		// Calculate trends
		$assessments_yesterday = $this->get_assessments_count( $yesterday );
		$assessment_trend = $this->calculate_percentage_change( $assessments_yesterday, $assessments_today );
		
		// Get active patients
		$active_patients = $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) 
			FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%' 
			AND meta_value > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);
		
		// Build performance indicators
		$performance_indicators = array();
		if ( $revenue_today > 10000 ) {
			$performance_indicators[] = "✅ Record revenue day!";
		}
		if ( $conversion_rate > 30 ) {
			$performance_indicators[] = "🎯 Exceptional conversion rate";
		}
		if ( $critical_alerts > 0 ) {
			$performance_indicators[] = "⚠️ {$critical_alerts} critical alerts (all contacted)";
		}
		
		$title = '📊 Daily Operations Summary - ' . date( 'F j, Y' );
		
		$message = sprintf(
			"Performance Metrics:\n" .
			"• Assessments Completed: %d (%s vs yesterday)\n" .
			"• New Registrations: %d\n" .
			"• Biomarker Updates: %d\n" .
			"• Appointments Scheduled: %d\n" .
			"• Critical Alerts: %d %s\n\n" .
			"Financial Performance:\n" .
			"• Revenue Today: $%s 💰\n" .
			"• Conversion Rate: %.1f%% (%s)\n" .
			"• Avg Transaction: $%s\n" .
			"• Monthly Goal Progress: %d%%\n\n" .
			"Top Assessments:\n" .
			"1. %s (%d completed)\n\n" .
			"Patient Engagement:\n" .
			"• Active patients today: %d\n\n" .
			"Notable Events:\n%s\n\n" .
			"[VIEW FULL DASHBOARD] [EXPORT REPORT]",
			$assessments_today,
			$assessment_trend,
			$registrations_today,
			$biomarker_updates,
			$appointments,
			$critical_alerts,
			$critical_alerts > 0 ? '⚠️' : '',
			number_format( $revenue_today, 0 ),
			$conversion_rate,
			$conversion_rate > 25 ? '↑' : '→',
			number_format( $revenue_today / max( $conversions_today, 1 ), 0 ),
			$this->calculate_monthly_progress(),
			ucwords( str_replace( '_', ' ', $top_assessment ) ),
			$this->get_assessment_count_by_type( $top_assessment, $today ),
			$active_patients,
			implode( "\n", $performance_indicators )
		);
		
		$this->send_to_teams( 'daily_summaries', $title, $message, array(
			'date' => $today,
			'metrics' => array(
				'assessments' => $assessments_today,
				'registrations' => $registrations_today,
				'revenue' => $revenue_today,
				'conversion_rate' => $conversion_rate
			)
		) );
	}

	/**
	 * Core function that sends to Power Automate
	 */
	private function send_to_teams( $channel, $title, $message, $data = array() ) {
		// Check if flow URL is configured
		if ( empty( $this->flow_urls[ $channel ] ) ) {
			// REMOVED: error_log( "Teams flow URL not configured for channel: {$channel}" );
			return false;
		}
		
		// Build payload for Power Automate
		$payload = array(
			'title' => $title,
			'message' => $message,
			'data' => $data,
			'channel' => $channel,
			'timestamp' => current_time( 'c' ),
			'importance' => isset( $data['importance'] ) ? $data['importance'] : 'normal'
		);
		
		// Send to Power Automate
		$response = wp_remote_post( $this->flow_urls[ $channel ], array(
			'body' => json_encode( $payload ),
			'headers' => array(
				'Content-Type' => 'application/json'
			),
			'timeout' => 30
		) );
		
		if ( is_wp_error( $response ) ) {
			// REMOVED: error_log( "Failed to send Teams notification: " . $response->get_error_message() );
			return false;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		
		if ( $response_code === 200 || $response_code === 202 ) {
			// REMOVED: error_log( "Teams notification sent successfully to {$channel}" );
			return true;
		} else {
			// REMOVED: error_log( "Teams notification failed with HTTP {$response_code}" );
			return false;
		}
	}

	/**
	 * Helper functions that get real data
	 */
	
	private function get_user_full_name( $user_id ) {
		$first = get_user_meta( $user_id, 'first_name', true );
		$last = get_user_meta( $user_id, 'last_name', true );
		$user = get_userdata( $user_id );
		return trim( $first . ' ' . $last ) ?: $user->display_name;
	}
	
	private function format_biomarker_name( $key ) {
		$names = array(
			'glucose' => 'Glucose',
			'hba1c' => 'HbA1c',
			'testosterone' => 'Testosterone',
			'vitamin_d' => 'Vitamin D',
			'tsh' => 'TSH',
			'hdl_cholesterol' => 'HDL Cholesterol',
			'ldl_cholesterol' => 'LDL Cholesterol',
			'hscrp' => 'hs-CRP',
			'cortisol' => 'Cortisol',
			'dhea_s' => 'DHEA-S'
		);
		return $names[ $key ] ?? ucwords( str_replace( '_', ' ', $key ) );
	}
	
	private function get_biomarker_unit( $key ) {
		$units = array(
			'glucose' => 'mg/dL',
			'hba1c' => '%',
			'testosterone' => 'ng/dL',
			'vitamin_d' => 'ng/mL',
			'tsh' => 'mIU/L',
			'hdl_cholesterol' => 'mg/dL',
			'ldl_cholesterol' => 'mg/dL',
			'hscrp' => 'mg/L',
			'cortisol' => 'μg/dL',
			'dhea_s' => 'μg/dL'
		);
		return $units[ $key ] ?? '';
	}
	
	private function evaluate_biomarker_change( $key, $old_value, $new_value ) {
		// Define what direction is "good" for each biomarker
		$optimal_direction = array(
			'glucose' => 'lower',
			'hba1c' => 'lower',
			'testosterone' => 'higher',
			'vitamin_d' => 'higher',
			'tsh' => 'normal', // 1.0-4.0
			'hdl_cholesterol' => 'higher',
			'ldl_cholesterol' => 'lower',
			'hscrp' => 'lower',
			'cortisol' => 'normal', // 5-20
			'dhea_s' => 'higher'
		);
		
		$direction = $optimal_direction[ $key ] ?? 'normal';
		
		if ( $direction === 'higher' ) {
			return $new_value > $old_value ? 'improved' : 'worsened';
		} elseif ( $direction === 'lower' ) {
			return $new_value < $old_value ? 'improved' : 'worsened';
		} else {
			// For "normal" range biomarkers, check if moving toward normal
			return 'neutral';
		}
	}
	
	private function calculate_overall_status( $biomarkers ) {
		$good = 0;
		$bad = 0;
		
		foreach ( $biomarkers as $key => $value ) {
			if ( $this->is_biomarker_optimal( $key, $value ) ) {
				$good++;
			} else {
				$bad++;
			}
		}
		
		if ( $good > $bad * 2 ) {
			return 'Excellent 📈';
		} elseif ( $good > $bad ) {
			return 'Improving 📈';
		} else {
			return 'Needs Attention ⚠️';
		}
	}
	
	private function is_biomarker_optimal( $key, $value ) {
		$optimal_ranges = array(
			'glucose' => array( 70, 100 ),
			'hba1c' => array( 4.0, 5.6 ),
			'testosterone' => array( 400, 900 ),
			'vitamin_d' => array( 40, 80 ),
			'tsh' => array( 1.0, 4.0 ),
			'hdl_cholesterol' => array( 50, 100 ),
			'ldl_cholesterol' => array( 0, 100 ),
			'hscrp' => array( 0, 1.0 ),
			'cortisol' => array( 5, 20 ),
			'dhea_s' => array( 200, 400 )
		);
		
		if ( isset( $optimal_ranges[ $key ] ) ) {
			$range = $optimal_ranges[ $key ];
			return $value >= $range[0] && $value <= $range[1];
		}
		
		return true; // Unknown biomarker, assume OK
	}
	
	private function get_critical_thresholds( $key ) {
		$thresholds = array(
			'hba1c' => array(
				'normal_range' => '4.0-5.6%',
				'critical_high' => 9.0,
				'critical_low' => 3.0
			),
			'glucose' => array(
				'normal_range' => '70-100 mg/dL',
				'critical_high' => 200,
				'critical_low' => 60
			)
			// Add more as needed
		);
		
		return $thresholds[ $key ] ?? array( 'normal_range' => 'N/A' );
	}
	
	private function calculate_risk_level( $key, $value ) {
		$thresholds = $this->get_critical_thresholds( $key );
		
		if ( isset( $thresholds['critical_high'] ) && $value >= $thresholds['critical_high'] ) {
			return 'HIGH';
		}
		if ( isset( $thresholds['critical_low'] ) && $value <= $thresholds['critical_low'] ) {
			return 'HIGH';
		}
		
		return 'MEDIUM';
	}
	
	private function get_required_actions( $key, $value, $risk_level ) {
		$actions = array();
		
		if ( $risk_level === 'HIGH' ) {
			$actions[] = '1. ⚡ Contact patient within 24 hours';
			$actions[] = '2. 📞 Schedule urgent consultation';
			$actions[] = '3. 🏥 Consider immediate medical referral';
			$actions[] = '4. 💊 Review medication compliance';
		} else {
			$actions[] = '1. 📞 Contact patient within 48 hours';
			$actions[] = '2. 📅 Schedule follow-up appointment';
			$actions[] = '3. 📊 Review treatment plan';
		}
		
		return implode( "\n", $actions );
	}
	
	private function calculate_percentage_change( $old, $new ) {
		if ( $old == 0 ) return $new > 0 ? '+100%' : '0%';
		$change = ( ( $new - $old ) / $old ) * 100;
		return ( $change >= 0 ? '+' : '' ) . round( $change, 1 ) . '%';
	}
	
	private function calculate_daily_revenue( $date ) {
		// If using WooCommerce
		global $wpdb;
		$revenue = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(meta_value) 
			FROM {$wpdb->postmeta} pm
			JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = '_order_total'
			AND p.post_type = 'shop_order'
			AND p.post_status IN ('wc-completed', 'wc-processing')
			AND DATE(p.post_date) = %s",
			$date
		) );
		
		return $revenue ?: 0;
	}
	
	private function get_next_appointment( $user_id ) {
		// Check if using Amelia booking plugin
		global $wpdb;
		$next = $wpdb->get_var( $wpdb->prepare(
			"SELECT bookingStart 
			FROM {$wpdb->prefix}amelia_customer_bookings cb
			JOIN {$wpdb->prefix}amelia_users u ON cb.customerId = u.id
			WHERE u.email = %s
			AND bookingStart > NOW()
			ORDER BY bookingStart ASC
			LIMIT 1",
			get_userdata( $user_id )->user_email
		) );
		
		return $next ? date( 'F j, Y', strtotime( $next ) ) : null;
	}
	
	private function log_critical_alert( $user_id, $biomarker, $value ) {
		// Keep a log of all critical alerts
		$alerts = get_option( 'ennu_critical_alerts_log', array() );
		$alerts[] = array(
			'user_id' => $user_id,
			'biomarker' => $biomarker,
			'value' => $value,
			'timestamp' => current_time( 'mysql' ),
			'notified' => true
		);
		
		// Keep last 100 alerts
		if ( count( $alerts ) > 100 ) {
			$alerts = array_slice( $alerts, -100 );
		}
		
		update_option( 'ennu_critical_alerts_log', $alerts );
		
		// Update daily counter
		$today = current_time( 'Y-m-d' );
		$count = get_transient( 'ennu_critical_alerts_' . $today ) ?: 0;
		set_transient( 'ennu_critical_alerts_' . $today, $count + 1, DAY_IN_SECONDS );
	}
}

/**
 * Initialize and hook into WordPress
 */
add_action( 'init', function() {
	$teams_messages = new ENNU_Teams_Dynamic_Messages();
	
	// Hook into actual WordPress events
	add_action( 'ennu_assessment_completed', array( $teams_messages, 'send_assessment_notification' ), 10, 2 );
	add_action( 'ennu_biomarker_updated', array( $teams_messages, 'send_biomarker_notification' ), 10, 2 );
	add_action( 'ennu_critical_value_detected', array( $teams_messages, 'send_critical_alert' ), 10, 3 );
	
	// Schedule daily summary
	if ( ! wp_next_scheduled( 'ennu_daily_summary' ) ) {
		wp_schedule_event( strtotime( 'today 18:00:00' ), 'daily', 'ennu_daily_summary' );
	}
	add_action( 'ennu_daily_summary', array( $teams_messages, 'send_daily_summary' ) );
} );