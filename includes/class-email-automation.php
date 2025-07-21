<?php
/**
 * ENNU Email Automation System
 * Enhanced email system with templates and scheduling
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Email_Automation {

	private static $instance = null;
	private $templates       = array();

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'init_templates' ) );
		add_action( 'ennu_assessment_completed', array( $this, 'send_completion_email' ), 10, 2 );
		add_action( 'ennu_send_scheduled_email', array( $this, 'send_scheduled_email' ), 10, 3 );
	}

	/**
	 * Initialize email templates
	 */
	public function init_templates() {
		$this->templates = array(
			'assessment_completion' => array(
				'subject'   => __( 'Your ENNU Life Assessment Results Are Ready', 'ennu-life-assessments' ),
				'template'  => 'assessment-completion',
				'variables' => array( 'user_name', 'assessment_type', 'score', 'results_url' ),
			),
			'welcome_series_1'      => array(
				'subject'   => __( 'Welcome to Your Health Journey', 'ennu-life-assessments' ),
				'template'  => 'welcome-series-1',
				'variables' => array( 'user_name', 'dashboard_url' ),
			),
			'biomarker_reminder'    => array(
				'subject'   => __( 'Time for Your Biomarker Testing', 'ennu-life-assessments' ),
				'template'  => 'biomarker-reminder',
				'variables' => array( 'user_name', 'recommended_biomarkers', 'booking_url' ),
			),
			'consultation_followup' => array(
				'subject'   => __( 'Your Consultation Follow-up', 'ennu-life-assessments' ),
				'template'  => 'consultation-followup',
				'variables' => array( 'user_name', 'specialist_name', 'next_steps' ),
			),
		);
	}

	/**
	 * Send assessment completion email
	 */
	public function send_completion_email( $user_id, $assessment_data ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return false;
		}

		$assessment_type = is_array( $assessment_data ) ? $assessment_data['assessment_type'] : $assessment_data;
		$score           = get_user_meta( $user_id, "ennu_{$assessment_type}_score", true );

		$variables = array(
			'user_name'       => $user->display_name,
			'assessment_type' => ucwords( str_replace( '_', ' ', $assessment_type ) ),
			'score'           => number_format( $score, 1 ),
			'results_url'     => home_url( '/dashboard' ),
		);

		return $this->send_template_email( $user->user_email, 'assessment_completion', $variables );
	}

	/**
	 * Send templated email
	 */
	public function send_template_email( $to, $template_key, $variables = array() ) {
		if ( ! isset( $this->templates[ $template_key ] ) ) {
			return false;
		}

		$template = $this->templates[ $template_key ];
		$subject  = $template['subject'];
		$content  = $this->render_template( $template['template'], $variables );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ENNU Life <noreply@ennulife.com>',
		);

		return wp_mail( $to, $subject, $content, $headers );
	}

	/**
	 * Render email template
	 */
	private function render_template( $template_name, $variables = array() ) {
		$template_path = plugin_dir_path( __FILE__ ) . "../templates/emails/{$template_name}.php";

		if ( ! file_exists( $template_path ) ) {
			return $this->get_default_template( $variables );
		}

		extract( $variables );
		ob_start();
		include $template_path;
		return ob_get_clean();
	}

	/**
	 * Get default email template
	 */
	private function get_default_template( $variables = array() ) {
		$user_name = $variables['user_name'] ?? __( 'Valued Member', 'ennu-life-assessments' );

		return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #2c3e50;">ENNU Life</h1>
                <p>' . sprintf( __( 'Hello %s,', 'ennu-life-assessments' ), esc_html( $user_name ) ) . '</p>
                <p>' . __( 'Thank you for using ENNU Life Assessments. Your personalized health insights are ready.', 'ennu-life-assessments' ) . '</p>
                <p>' . __( 'Visit your dashboard to view your results and recommendations.', 'ennu-life-assessments' ) . '</p>
                <p>' . __( 'Best regards,<br>The ENNU Life Team', 'ennu-life-assessments' ) . '</p>
            </div>
        </body>
        </html>';
	}

	/**
	 * Schedule email
	 */
	public function schedule_email( $to, $template_key, $variables, $delay_seconds = 0 ) {
		$timestamp = time() + $delay_seconds;

		return wp_schedule_single_event( $timestamp, 'ennu_send_scheduled_email', array( $to, $template_key, $variables ) );
	}

	/**
	 * Send scheduled email
	 */
	public function send_scheduled_email( $to, $template_key, $variables ) {
		return $this->send_template_email( $to, $template_key, $variables );
	}

	/**
	 * Schedule welcome email series
	 */
	public function schedule_welcome_series( $user_id ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return false;
		}

		$variables = array(
			'user_name'     => $user->display_name,
			'dashboard_url' => home_url( '/dashboard' ),
		);

		$this->send_template_email( $user->user_email, 'welcome_series_1', $variables );

		$this->schedule_email( $user->user_email, 'biomarker_reminder', $variables, 7 * DAY_IN_SECONDS );

		return true;
	}

	/**
	 * Get email statistics
	 */
	public function get_email_stats() {
		return array(
			'total_sent'          => get_option( 'ennu_emails_sent_total', 0 ),
			'sent_today'          => get_option( 'ennu_emails_sent_today', 0 ),
			'last_sent'           => get_option( 'ennu_last_email_sent', '' ),
			'templates_available' => count( $this->templates ),
		);
	}

	/**
	 * Track email sending
	 */
	private function track_email_sent() {
		$total = get_option( 'ennu_emails_sent_total', 0 );
		update_option( 'ennu_emails_sent_total', $total + 1 );

		$today     = get_option( 'ennu_emails_sent_today', 0 );
		$last_date = get_option( 'ennu_last_email_date', date( 'Y-m-d' ) );

		if ( $last_date !== date( 'Y-m-d' ) ) {
			$today = 0;
			update_option( 'ennu_last_email_date', date( 'Y-m-d' ) );
		}

		update_option( 'ennu_emails_sent_today', $today + 1 );
		update_option( 'ennu_last_email_sent', current_time( 'mysql' ) );
	}
}

ENNU_Email_Automation::get_instance();
