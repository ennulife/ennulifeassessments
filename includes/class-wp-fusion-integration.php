<?php
/**
 * ENNU Life WP Fusion Integration
 * Handles contact creation, workflow triggers, and data synchronization
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_WP_Fusion_Integration {

	private $enabled;

	public function __construct() {
		$this->enabled = $this->is_wp_fusion_enabled();

		if ( $this->enabled ) {
			add_action( 'ennu_assessment_completed', array( $this, 'create_hubspot_contact' ), 10, 2 );
			add_action( 'ennu_biomarker_imported', array( $this, 'trigger_biomarker_workflow' ), 10, 2 );
			add_action( 'ennu_consultation_booked', array( $this, 'trigger_booking_workflow' ), 10, 2 );
			add_action( 'ennu_health_goals_updated', array( $this, 'sync_health_goals' ), 10, 2 );
			add_action( 'ennu_score_calculated', array( $this, 'sync_score_data' ), 10, 2 );
		}

		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_filter( 'ennu_hubspot_booking_settings', array( $this, 'add_wp_fusion_settings' ) );
	}

	public function is_wp_fusion_enabled() {
		if ( ! function_exists( 'wp_fusion' ) ) {
			return false;
		}

		return get_option( 'ennu_wp_fusion_enabled', false );
	}

	public function add_settings() {
		register_setting( 'ennu_hubspot_booking_settings', 'ennu_wp_fusion_enabled' );
		register_setting( 'ennu_hubspot_booking_settings', 'ennu_wp_fusion_assessment_tag' );
		register_setting( 'ennu_hubspot_booking_settings', 'ennu_wp_fusion_biomarker_tag' );
		register_setting( 'ennu_hubspot_booking_settings', 'ennu_wp_fusion_booking_tag' );
	}

	public function add_wp_fusion_settings( $settings ) {
		$settings['wp_fusion'] = array(
			'title'  => __( 'WP Fusion Integration', 'ennulifeassessments' ),
			'fields' => array(
				'ennu_wp_fusion_enabled'        => array(
					'type'        => 'checkbox',
					'label'       => __( 'Enable WP Fusion Integration', 'ennulifeassessments' ),
					'description' => __( 'Automatically sync user data and trigger workflows in HubSpot via WP Fusion.', 'ennulifeassessments' ),
				),
				'ennu_wp_fusion_assessment_tag' => array(
					'type'        => 'text',
					'label'       => __( 'Assessment Completion Tag', 'ennulifeassessments' ),
					'description' => __( 'Tag to apply when user completes an assessment.', 'ennulifeassessments' ),
					'default'     => 'ennu-assessment-completed',
				),
				'ennu_wp_fusion_biomarker_tag'  => array(
					'type'        => 'text',
					'label'       => __( 'Biomarker Results Tag', 'ennulifeassessments' ),
					'description' => __( 'Tag to apply when biomarker results are imported.', 'ennulifeassessments' ),
					'default'     => 'ennu-biomarker-results',
				),
				'ennu_wp_fusion_booking_tag'    => array(
					'type'        => 'text',
					'label'       => __( 'Consultation Booking Tag', 'ennulifeassessments' ),
					'description' => __( 'Tag to apply when user books a consultation.', 'ennulifeassessments' ),
					'default'     => 'ennu-consultation-booked',
				),
			),
		);

		return $settings;
	}

	public function create_hubspot_contact( $user_id, $assessment_data ) {
		if ( ! $this->enabled ) {
			return;
		}

		error_log( "WP Fusion Integration: Creating/updating HubSpot contact for user {$user_id}" );

		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return;
		}

		$contact_data = $this->prepare_contact_data( $user_id, $assessment_data );

		if ( function_exists( 'wp_fusion' ) ) {
			$contact_id = wp_fusion()->user->get_contact_id( $user_id );

			if ( ! $contact_id ) {
				$contact_id = wp_fusion()->crm->add_contact( $contact_data );
				if ( $contact_id ) {
					wp_fusion()->user->update_contact_id( $user_id, $contact_id );
				}
			} else {
				wp_fusion()->crm->update_contact( $contact_id, $contact_data );
			}

			$assessment_tag = get_option( 'ennu_wp_fusion_assessment_tag', 'ennu-assessment-completed' );
			if ( $assessment_tag ) {
				wp_fusion()->user->apply_tags( array( $assessment_tag ), $user_id );
			}

			error_log( "WP Fusion Integration: Contact synced for user {$user_id}, contact ID: {$contact_id}" );
		}
	}

	public function trigger_biomarker_workflow( $user_id, $biomarker_data ) {
		if ( ! $this->enabled ) {
			return;
		}

		error_log( "WP Fusion Integration: Triggering biomarker workflow for user {$user_id}" );

		$biomarker_tag = get_option( 'ennu_wp_fusion_biomarker_tag', 'ennu-biomarker-results' );
		if ( $biomarker_tag && function_exists( 'wp_fusion' ) ) {
			wp_fusion()->user->apply_tags( array( $biomarker_tag ), $user_id );
		}

		$this->sync_biomarker_data( $user_id, $biomarker_data );
		$this->trigger_workflow( 'biomarker_results_received', $user_id, $biomarker_data );
	}

	public function trigger_booking_workflow( $user_id, $booking_data ) {
		if ( ! $this->enabled ) {
			return;
		}

		error_log( "WP Fusion Integration: Triggering booking workflow for user {$user_id}" );

		$booking_tag = get_option( 'ennu_wp_fusion_booking_tag', 'ennu-consultation-booked' );
		if ( $booking_tag && function_exists( 'wp_fusion' ) ) {
			wp_fusion()->user->apply_tags( array( $booking_tag ), $user_id );
		}

		$this->sync_booking_data( $user_id, $booking_data );
		$this->trigger_workflow( 'consultation_booked', $user_id, $booking_data );
	}

	public function sync_health_goals( $user_id, $health_goals ) {
		if ( ! $this->enabled ) {
			return;
		}

		$contact_data = array(
			'ennu_health_goals'         => implode( ';', $health_goals ),
			'ennu_health_goals_count'   => count( $health_goals ),
			'ennu_health_goals_updated' => current_time( 'mysql' ),
		);

		$this->sync_contact_data( $user_id, $contact_data );
	}

	public function sync_score_data( $user_id, $score_data ) {
		if ( ! $this->enabled ) {
			return;
		}

		$contact_data = array(
			'ennu_life_score'       => $score_data['ennu_life_score'] ?? '',
			'ennu_mind_score'       => $score_data['pillar_scores']['Mind'] ?? '',
			'ennu_body_score'       => $score_data['pillar_scores']['Body'] ?? '',
			'ennu_lifestyle_score'  => $score_data['pillar_scores']['Lifestyle'] ?? '',
			'ennu_aesthetics_score' => $score_data['pillar_scores']['Aesthetics'] ?? '',
			'ennu_score_updated'    => current_time( 'mysql' ),
		);

		$new_life_score = get_user_meta( $user_id, 'ennu_new_life_score', true );
		if ( $new_life_score ) {
			$contact_data['ennu_new_life_score'] = $new_life_score;
		}

		$this->sync_contact_data( $user_id, $contact_data );
	}

	private function prepare_contact_data( $user_id, $assessment_data ) {
		$user         = get_user_by( 'ID', $user_id );
		$contact_data = array(
			'email'      => $user->user_email,
			'first_name' => get_user_meta( $user_id, 'first_name', true ) ?: '',
			'last_name'  => get_user_meta( $user_id, 'last_name', true ) ?: '',
			'phone'      => get_user_meta( $user_id, 'ennu_global_phone', true ) ?: '',
		);

		$global_data = array(
			'ennu_gender'       => get_user_meta( $user_id, 'ennu_global_gender', true ),
			'ennu_dob'          => get_user_meta( $user_id, 'ennu_global_date_of_birth', true ),
			'ennu_health_goals' => implode( ';', get_user_meta( $user_id, 'ennu_global_health_goals', true ) ?: array() ),
		);

		$height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		if ( is_array( $height_weight ) ) {
			$global_data['ennu_height_ft']  = $height_weight['ft'] ?? '';
			$global_data['ennu_height_in']  = $height_weight['in'] ?? '';
			// Handle both 'weight' and 'lbs' keys for backward compatibility
			$global_data['ennu_weight_lbs'] = isset( $height_weight['weight'] ) ? $height_weight['weight'] : ( isset( $height_weight['lbs'] ) ? $height_weight['lbs'] : '' );
		}

		if ( isset( $assessment_data['assessment_type'] ) ) {
			$global_data['ennu_last_assessment']      = $assessment_data['assessment_type'];
			$global_data['ennu_last_assessment_date'] = current_time( 'mysql' );
		}

		return array_merge( $contact_data, $global_data );
	}

	private function sync_contact_data( $user_id, $contact_data ) {
		if ( ! function_exists( 'wp_fusion' ) ) {
			return;
		}

		$contact_id = wp_fusion()->user->get_contact_id( $user_id );
		if ( $contact_id ) {
			wp_fusion()->crm->update_contact( $contact_id, $contact_data );
		}
	}

	private function sync_biomarker_data( $user_id, $biomarker_data ) {
		$contact_data = array(
			'ennu_biomarkers_imported'    => count( $biomarker_data ),
			'ennu_biomarkers_last_import' => current_time( 'mysql' ),
		);

		foreach ( $biomarker_data as $biomarker => $data ) {
			$field_name                              = 'ennu_biomarker_' . $biomarker;
			$contact_data[ $field_name ]             = $data['value'] . ' ' . $data['unit'];
			$contact_data[ $field_name . '_status' ] = $data['status'] ?? 'unknown';
		}

		$this->sync_contact_data( $user_id, $contact_data );
	}

	private function sync_booking_data( $user_id, $booking_data ) {
		$contact_data = array(
			'ennu_last_booking_type' => $booking_data['consultation_type'] ?? '',
			'ennu_last_booking_date' => current_time( 'mysql' ),
			'ennu_booking_source'    => $booking_data['source'] ?? 'ennu_plugin',
		);

		$this->sync_contact_data( $user_id, $contact_data );
	}

	private function trigger_workflow( $workflow_type, $user_id, $data ) {
		if ( ! function_exists( 'wp_fusion' ) ) {
			return;
		}

		$workflow_tags = array(
			'biomarker_results_received' => 'ennu-biomarker-workflow',
			'consultation_booked'        => 'ennu-booking-workflow',
			'assessment_completed'       => 'ennu-assessment-workflow',
		);

		$tag = $workflow_tags[ $workflow_type ] ?? null;
		if ( $tag ) {
			wp_fusion()->user->apply_tags( array( $tag ), $user_id );
			error_log( "WP Fusion Integration: Applied workflow tag '{$tag}' for user {$user_id}" );
		}

		do_action( 'ennu_wp_fusion_workflow_triggered', $workflow_type, $user_id, $data );
	}
}

new ENNU_WP_Fusion_Integration();
