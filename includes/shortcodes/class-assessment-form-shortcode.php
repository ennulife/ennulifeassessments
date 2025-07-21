<?php
/**
 * Assessment Form Shortcode Class
 *
 * Handles assessment form rendering and submission
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ENNU_LIFE_PLUGIN_PATH . 'includes/shortcodes/class-base-shortcode.php';

class ENNU_Assessment_Form_Shortcode extends ENNU_Base_Shortcode {

	protected $shortcode_name = 'ennu_assessment_form';

	protected $default_attributes = array(
		'assessment_type'     => '',
		'show_progress'       => 'true',
		'allow_save_progress' => 'true',
		'redirect_after'      => '',
	);

	public function render( $atts, $content = null ) {
		if ( ! $this->validate_user_permissions() ) {
			return $this->render_error( 'You must be logged in to take assessments.' );
		}

		$atts = $this->parse_attributes( $atts );

		if ( empty( $atts['assessment_type'] ) ) {
			return $this->render_error( 'Assessment type is required.' );
		}

		$assessment_type = $this->sanitize_input( $atts['assessment_type'] );
		$user_id         = get_current_user_id();

		$this->enqueue_assets();

		$assessment_data = $this->get_assessment_data( $assessment_type, $user_id );

		if ( ! $assessment_data ) {
			return $this->render_error( 'Assessment type not found: ' . $assessment_type );
		}

		return $this->get_template(
			'assessment-form',
			array(
				'assessment_type' => $assessment_type,
				'assessment_data' => $assessment_data,
				'user_id'         => $user_id,
				'attributes'      => $atts,
			)
		);
	}

	protected function enqueue_assets() {
		wp_enqueue_script(
			'ennu-assessment-form',
			ENNU_LIFE_PLUGIN_URL . 'assets/js/assessment-form.js',
			array(),
			ENNU_LIFE_PLUGIN_VERSION,
			true
		);

		wp_enqueue_style(
			'ennu-assessment-form',
			ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-form.css',
			array(),
			ENNU_LIFE_PLUGIN_VERSION
		);

		wp_localize_script(
			'ennu-assessment-form',
			'ennuAssessmentAjax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ennu_ajax_nonce' ),
			)
		);
	}

	private function get_assessment_data( $assessment_type, $user_id ) {
		$all_definitions = ENNU_Scoring_System::get_all_definitions();

		if ( ! isset( $all_definitions[ $assessment_type ] ) ) {
			return false;
		}

		$definition = $all_definitions[ $assessment_type ];

		$saved_responses   = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_responses', true );
		$completion_status = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_completed', true );

		return array(
			'definition'      => $definition,
			'saved_responses' => $saved_responses ?: array(),
			'completed'       => ! empty( $completion_status ),
			'questions'       => $definition['questions'] ?? array(),
			'categories'      => $definition['categories'] ?? array(),
		);
	}
}

new ENNU_Assessment_Form_Shortcode();
