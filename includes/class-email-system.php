<?php
/**
 * ENNU Life Email Notification System
 * Enterprise-grade email handling for all plugin communications
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Life_Email_System {

	private $from_email;
	private $from_name;
	private $templates;

	public function __construct() {
		$this->from_email = get_option( 'admin_email' );
		$this->from_name  = get_bloginfo( 'name' ) . ' - ENNU Life';
		$this->init_email_templates();

		// Hook into form submissions
		add_action( 'ennu_form_submitted', array( $this, 'send_form_notification' ), 10, 3 );
		add_action( 'ennu_booking_created', array( $this, 'send_booking_confirmation' ), 10, 2 );
		add_action( 'ennu_assessment_completed', array( $this, 'send_assessment_results' ), 10, 3 );
	}

	private function init_email_templates() {
		$this->templates = array(
			'form_submission_admin' => array(
				'subject'  => 'New {form_type} Submission - ENNU Life',
				'template' => 'admin-form-notification.php',
			),
			'form_submission_user'  => array(
				'subject'  => 'Thank you for your submission - ENNU Life',
				'template' => 'user-form-confirmation.php',
			),
			'booking_confirmation'  => array(
				'subject'  => 'Appointment Booking Confirmation - ENNU Life',
				'template' => 'booking-confirmation.php',
			),
			'booking_admin'         => array(
				'subject'  => 'New Appointment Booking - ENNU Life',
				'template' => 'admin-booking-notification.php',
			),
			'assessment_results'    => array(
				'subject'  => 'Your {assessment_type} Results - ENNU Life',
				'template' => 'assessment-results.php',
			),
			'welcome_email'         => array(
				'subject'  => 'Welcome to ENNU Life Health Platform',
				'template' => 'welcome-email.php',
			),
		);
	}

	public function send_form_notification( $form_type, $form_data, $submission_id ) {
		// Send notification to admin
		$this->send_admin_form_notification( $form_type, $form_data, $submission_id );

		// Send confirmation to user
		if ( isset( $form_data['email'] ) && is_email( $form_data['email'] ) ) {
			$this->send_user_form_confirmation( $form_type, $form_data, $submission_id );
		}
	}

	public function send_booking_confirmation( $booking_data, $booking_id ) {
		// Send confirmation to user
		if ( isset( $booking_data['email'] ) && is_email( $booking_data['email'] ) ) {
			$this->send_user_booking_confirmation( $booking_data, $booking_id );
		}

		// Send notification to admin
		$this->send_admin_booking_notification( $booking_data, $booking_id );
	}

	public function send_assessment_results( $assessment_type, $results, $user_email ) {
		if ( ! is_email( $user_email ) ) {
			return false;
		}

		$subject = str_replace(
			'{assessment_type}',
			ucwords( str_replace( '-', ' ', $assessment_type ) ),
			$this->templates['assessment_results']['subject']
		);

		$message = $this->build_assessment_results_email( $assessment_type, $results );

		return $this->send_email( $user_email, $subject, $message, 'assessment_results' );
	}

	private function send_admin_form_notification( $form_type, $form_data, $submission_id ) {
		$subject = str_replace(
			'{form_type}',
			ucwords( str_replace( '-', ' ', $form_type ) ),
			$this->templates['form_submission_admin']['subject']
		);

		$message = $this->build_admin_form_notification( $form_type, $form_data, $submission_id );

		return $this->send_email( $this->from_email, $subject, $message, 'form_submission_admin' );
	}

	private function send_user_form_confirmation( $form_type, $form_data, $submission_id ) {
		$subject = $this->templates['form_submission_user']['subject'];
		$message = $this->build_user_form_confirmation( $form_type, $form_data, $submission_id );

		return $this->send_email( $form_data['email'], $subject, $message, 'form_submission_user' );
	}

	private function send_user_booking_confirmation( $booking_data, $booking_id ) {
		$subject = $this->templates['booking_confirmation']['subject'];
		$message = $this->build_booking_confirmation( $booking_data, $booking_id );

		return $this->send_email( $booking_data['email'], $subject, $message, 'booking_confirmation' );
	}

	private function send_admin_booking_notification( $booking_data, $booking_id ) {
		$subject = $this->templates['booking_admin']['subject'];
		$message = $this->build_admin_booking_notification( $booking_data, $booking_id );

		return $this->send_email( $this->from_email, $subject, $message, 'booking_admin' );
	}

	private function build_admin_form_notification( $form_type, $form_data, $submission_id ) {
		$message  = 'New form submission received on ' . get_bloginfo( 'name' ) . "\n\n";
		$message .= 'Form Type: ' . ucwords( str_replace( '-', ' ', $form_type ) ) . "\n";
		$message .= 'Submission ID: ' . $submission_id . "\n";
		$message .= 'Submitted: ' . current_time( 'F j, Y g:i A' ) . "\n";
		$message .= 'IP Address: ' . ( $_SERVER['REMOTE_ADDR'] ?? 'Unknown' ) . "\n\n";

		$message .= "SUBMISSION DETAILS:\n";
		$message .= str_repeat( '-', 40 ) . "\n";

		foreach ( $form_data as $key => $value ) {
			$label = ucwords( str_replace( '_', ' ', $key ) );

			if ( is_array( $value ) ) {
				$message .= $label . ': ' . implode( ', ', $value ) . "\n";
			} else {
				$message .= $label . ': ' . $value . "\n";
			}
		}

		$message .= "\n" . str_repeat( '-', 40 ) . "\n";
		$message .= "View submission details in WordPress admin:\n";
		$message .= admin_url( 'admin.php?page=ennu-life&tab=submissions&id=' . $submission_id ) . "\n\n";

		$message .= "Best regards,\n";
		$message .= 'ENNU Life Plugin System';

		return $message;
	}

	private function build_user_form_confirmation( $form_type, $form_data, $submission_id ) {
		$name = isset( $form_data['name'] ) ? $form_data['name'] : 'Valued Client';

		$message  = 'Dear ' . $name . ",\n\n";
		$message .= 'Thank you for submitting your ' . ucwords( str_replace( '-', ' ', $form_type ) ) . " form.\n\n";

		$message .= 'We have received your information and our team will review it shortly. ';
		$message .= "You can expect to hear from us within 24-48 hours.\n\n";

		$message .= 'Your submission reference ID is: ' . $submission_id . "\n\n";

		// Add specific next steps based on form type
		switch ( $form_type ) {
			case 'ed-treatment-assessment':
				$message .= "Next Steps:\n";
				$message .= "• Our medical team will review your assessment\n";
				$message .= "• We'll contact you to schedule a confidential consultation\n";
				$message .= "• Treatment options will be discussed during your appointment\n\n";
				break;

			case 'weight-loss-assessment':
				$message .= "Next Steps:\n";
				$message .= "• Our nutritionist will analyze your goals and current situation\n";
				$message .= "• We'll create a personalized weight loss plan for you\n";
				$message .= "• A consultation will be scheduled to discuss your program\n\n";
				break;

			case 'health-assessment':
				$message .= "Next Steps:\n";
				$message .= "• Our healthcare team will review your comprehensive assessment\n";
				$message .= "• We'll schedule a detailed consultation to discuss your results\n";
				$message .= "• Personalized health recommendations will be provided\n\n";
				break;
		}

		$message .= "If you have any immediate questions, please don't hesitate to contact us.\n\n";
		$message .= "Best regards,\n";
		$message .= "The ENNU Life Team\n";
		$message .= get_bloginfo( 'url' );

		return $message;
	}

	private function build_booking_confirmation( $booking_data, $booking_id ) {
		$name = $booking_data['name'] ?? 'Valued Client';

		$message  = 'Dear ' . $name . ",\n\n";
		$message .= "Thank you for booking an appointment with ENNU Life!\n\n";

		$message .= "BOOKING DETAILS:\n";
		$message .= str_repeat( '-', 30 ) . "\n";
		$message .= 'Booking ID: ' . $booking_id . "\n";
		$message .= 'Service: ' . ( $booking_data['service'] ?? 'Consultation' ) . "\n";
		$message .= 'Date: ' . ( $booking_data['date'] ?? 'To be confirmed' ) . "\n";
		$message .= 'Time: ' . ( $booking_data['time'] ?? 'To be confirmed' ) . "\n";
		$message .= 'Location: ' . ( $booking_data['location'] ?? 'Virtual/Online' ) . "\n";
		$message .= str_repeat( '-', 30 ) . "\n\n";

		$message .= "We will contact you within 24 hours to confirm your appointment details.\n\n";
		$message .= "Please save this email for your records.\n\n";

		$message .= "Best regards,\n";
		$message .= "The ENNU Life Team\n";
		$message .= get_bloginfo( 'url' );

		return $message;
	}

	private function build_admin_booking_notification( $booking_data, $booking_id ) {
		$message  = 'New appointment booking received on ' . get_bloginfo( 'name' ) . "\n\n";
		$message .= 'Booking ID: ' . $booking_id . "\n";
		$message .= 'Submitted: ' . current_time( 'F j, Y g:i A' ) . "\n\n";

		$message .= "CLIENT INFORMATION:\n";
		$message .= str_repeat( '-', 40 ) . "\n";

		foreach ( $booking_data as $key => $value ) {
			$label    = ucwords( str_replace( '_', ' ', $key ) );
			$message .= $label . ': ' . $value . "\n";
		}

		$message .= "\n" . str_repeat( '-', 40 ) . "\n";
		$message .= "Please contact the client to confirm appointment details.\n\n";

		return $message;
	}

	private function build_assessment_results_email( $assessment_type, $results ) {
		$message = 'Your ' . ucwords( str_replace( '-', ' ', $assessment_type ) ) . " Results\n\n";

		if ( isset( $results['score'] ) ) {
			$message .= 'Overall Score: ' . $results['score'] . "/100\n\n";
		}

		if ( isset( $results['recommendations'] ) && is_array( $results['recommendations'] ) ) {
			$message .= "RECOMMENDATIONS:\n";
			$message .= str_repeat( '-', 30 ) . "\n";
			foreach ( $results['recommendations'] as $recommendation ) {
				$message .= '• ' . $recommendation . "\n";
			}
			$message .= "\n";
		}

		if ( isset( $results['next_steps'] ) ) {
			$message .= "NEXT STEPS:\n";
			$message .= str_repeat( '-', 30 ) . "\n";
			$message .= $results['next_steps'] . "\n\n";
		}

		$message .= 'For detailed consultation and personalized treatment plans, ';
		$message .= "please schedule an appointment with our specialists.\n\n";

		$message .= "Best regards,\n";
		$message .= "The ENNU Life Team\n";
		$message .= get_bloginfo( 'url' );

		return $message;
	}

	private function send_email( $to, $subject, $message, $template_type = '' ) {
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $this->from_name . ' <' . $this->from_email . '>',
		);

		// Convert plain text to HTML
		$html_message = $this->convert_to_html( $message, $template_type );

		$sent = wp_mail( $to, $subject, $html_message, $headers );

		return $sent;
	}

	private function convert_to_html( $message, $template_type = '' ) {
		// Basic HTML wrapper for plain text emails
		$html  = '<!DOCTYPE html>';
		$html .= '<html><head><meta charset="UTF-8">';
		$html .= '<title>ENNU Life</title>';
		$html .= '<style>';
		$html .= 'body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }';
		$html .= '.header { background: linear-gradient(135deg, #d4af37, #f4e4a6); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }';
		$html .= '.content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }';
		$html .= '.footer { text-align: center; margin-top: 20px; padding: 20px; background: #e9ecef; border-radius: 8px; }';
		$html .= 'h1, h2, h3 { color: #d4af37; }';
		$html .= '.button { background: #d4af37; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin: 10px 0; }';
		$html .= '</style></head><body>';

		$html .= '<div class="header">';
		$html .= '<h1>ENNU Life Health Platform</h1>';
		$html .= '</div>';

		$html .= '<div class="content">';
		$html .= nl2br( esc_html( $message ) );
		$html .= '</div>';

		$html .= '<div class="footer">';
		$html .= '<p><small>This email was sent from ' . get_bloginfo( 'name' ) . '<br>';
		$html .= 'Visit us at: <a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'url' ) . '</a></small></p>';
		$html .= '</div>';

		$html .= '</body></html>';

		return $html;
	}

	public function send_welcome_email( $user_email, $user_name = '' ) {
		if ( ! is_email( $user_email ) ) {
			return false;
		}

		$name    = $user_name ? $user_name : 'Valued Member';
		$subject = $this->templates['welcome_email']['subject'];

		$message  = 'Dear ' . $name . ",\n\n";
		$message .= "Welcome to the ENNU Life Health Platform!\n\n";
		$message .= "We're excited to have you join our community of health-conscious individuals ";
		$message .= "who are taking control of their wellness journey.\n\n";

		$message .= "WHAT'S NEXT:\n";
		$message .= str_repeat( '-', 30 ) . "\n";
		$message .= "• Complete your health assessments to get personalized recommendations\n";
		$message .= "• Schedule consultations with our specialists\n";
		$message .= "• Access exclusive health resources and programs\n";
		$message .= "• Track your progress with our comprehensive tools\n\n";

		$message .= 'Get started by visiting your dashboard: ' . get_bloginfo( 'url' ) . "/my-account/\n\n";

		$message .= "If you have any questions, our support team is here to help.\n\n";

		$message .= "To your health and wellness,\n";
		$message .= 'The ENNU Life Team';

		return $this->send_email( $user_email, $subject, $message, 'welcome_email' );
	}

	public function send_reminder_email( $user_email, $reminder_type, $data = array() ) {
		if ( ! is_email( $user_email ) ) {
			return false;
		}

		$subject = '';
		$message = '';

		switch ( $reminder_type ) {
			case 'assessment_incomplete':
				$subject = 'Complete Your Health Assessment - ENNU Life';
				$message = $this->build_assessment_reminder( $data );
				break;

			case 'appointment_reminder':
				$subject = 'Appointment Reminder - ENNU Life';
				$message = $this->build_appointment_reminder( $data );
				break;

			case 'follow_up':
				$subject = 'Follow-up: Your Health Journey - ENNU Life';
				$message = $this->build_follow_up_reminder( $data );
				break;
		}

		if ( $subject && $message ) {
			return $this->send_email( $user_email, $subject, $message, 'reminder' );
		}

		return false;
	}

	private function build_assessment_reminder( $data ) {
		$name = $data['name'] ?? 'Valued Member';

		$message  = 'Dear ' . $name . ",\n\n";
		$message .= "We noticed you started a health assessment but haven't completed it yet.\n\n";
		$message .= 'Your health is important to us, and completing your assessment will help us ';
		$message .= "provide you with personalized recommendations and care.\n\n";
		$message .= 'Complete your assessment: ' . ( $data['assessment_url'] ?? get_bloginfo( 'url' ) ) . "\n\n";
		$message .= "It only takes a few minutes and could make a significant difference in your health journey.\n\n";
		$message .= "Best regards,\n";
		$message .= 'The ENNU Life Team';

		return $message;
	}

	private function build_appointment_reminder( $data ) {
		$name = $data['name'] ?? 'Valued Client';

		$message  = 'Dear ' . $name . ",\n\n";
		$message .= "This is a friendly reminder about your upcoming appointment:\n\n";
		$message .= 'Date: ' . ( $data['date'] ?? 'TBD' ) . "\n";
		$message .= 'Time: ' . ( $data['time'] ?? 'TBD' ) . "\n";
		$message .= 'Service: ' . ( $data['service'] ?? 'Consultation' ) . "\n";
		$message .= 'Location: ' . ( $data['location'] ?? 'Virtual/Online' ) . "\n\n";
		$message .= "Please arrive 10 minutes early and bring any relevant medical records.\n\n";
		$message .= "If you need to reschedule, please contact us as soon as possible.\n\n";
		$message .= "We look forward to seeing you!\n\n";
		$message .= "Best regards,\n";
		$message .= 'The ENNU Life Team';

		return $message;
	}

	private function build_follow_up_reminder( $data ) {
		$name = $data['name'] ?? 'Valued Member';

		$message  = 'Dear ' . $name . ",\n\n";
		$message .= "We hope you're doing well on your health journey with ENNU Life.\n\n";
		$message .= "It's been a while since your last interaction, and we wanted to check in ";
		$message .= "to see how you're progressing with your health goals.\n\n";
		$message .= "Consider scheduling a follow-up consultation to:\n";
		$message .= "• Review your progress\n";
		$message .= "• Adjust your treatment plan if needed\n";
		$message .= "• Address any new concerns\n";
		$message .= "• Optimize your health strategy\n\n";
		$message .= 'Schedule your follow-up: ' . get_bloginfo( 'url' ) . "/book-appointment/\n\n";
		$message .= "Your health is our priority, and we're here to support you every step of the way.\n\n";
		$message .= "Best regards,\n";
		$message .= 'The ENNU Life Team';

		return $message;
	}

	private function build_user_booking_confirmation( $booking_data, $booking_id ) {
		$name = $booking_data['name'] ?? 'Valued Client';

		$message  = 'Dear ' . $name . ",\n\n";
		$message .= "Thank you for booking your appointment with ENNU Life!\n\n";
		$message .= "APPOINTMENT DETAILS:\n";
		$message .= str_repeat( '-', 30 ) . "\n";
		$message .= 'Booking ID: ' . $booking_id . "\n";
		$message .= 'Service: ' . ( $booking_data['service_type'] ?? 'General Consultation' ) . "\n";
		$message .= 'Preferred Date: ' . ( $booking_data['preferred_date'] ?? 'To be confirmed' ) . "\n";
		$message .= 'Preferred Time: ' . ( $booking_data['preferred_time'] ?? 'To be confirmed' ) . "\n\n";

		if ( ! empty( $booking_data['notes'] ) ) {
			$message .= 'Special Notes: ' . $booking_data['notes'] . "\n";
		}

		$message .= str_repeat( '-', 30 ) . "\n\n";

		$message .= "WHAT HAPPENS NEXT:\n";
		$message .= "• Our scheduling team will contact you within 24 hours\n";
		$message .= "• We'll confirm your appointment date and time\n";
		$message .= "• You'll receive detailed preparation instructions\n";
		$message .= "• A calendar invitation will be sent to you\n\n";

		$message .= "IMPORTANT REMINDERS:\n";
		$message .= "• Please arrive 15 minutes early for your appointment\n";
		$message .= "• Bring a valid ID and insurance information\n";
		$message .= "• If you need to reschedule, please give us 24 hours notice\n\n";

		$message .= "We look forward to seeing you soon!\n\n";
		$message .= "Best regards,\n";
		$message .= "The ENNU Life Team\n";
		$message .= 'Phone: ' . get_option( 'ennu_phone', '(555) 123-4567' ) . "\n";
		$message .= 'Email: ' . $this->from_email;

		return $message;
	}
}
