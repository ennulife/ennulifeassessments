<?php
/**
 * ENNU Assessment Rendering Service
 * Dedicated service for rendering assessment forms and results
 * 
 * @package ENNU_Life_Assessments
 * @version 3.37.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assessment rendering service that handles all form and result rendering
 * Extracted from the monolithic assessment shortcodes class
 */
class ENNU_Assessment_Rendering_Service {

	private static $instance = null;
	private $unified_scoring_service;
	private $logger;

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
		$this->unified_scoring_service = ENNU_Unified_Scoring_Service::get_instance();
		$this->logger = class_exists( 'ENNU_Logger' ) ? new ENNU_Logger() : null;
	}

	/**
	 * Render assessment form
	 * 
	 * @param string $assessment_type Assessment type
	 * @param array $user_data User data for pre-filling
	 * @return string Rendered form HTML
	 */
	public function render_assessment_form( $assessment_type, $user_data = array() ) {
		$form_html = '';
		
		// Get assessment definitions
		$definitions = $this->get_assessment_definitions( $assessment_type );
		if ( empty( $definitions ) ) {
			return $this->render_error_message( 'Assessment type not found: ' . $assessment_type );
		}

		// Start form
		$form_html .= $this->render_form_start( $assessment_type );
		
		// Render sections
		foreach ( $definitions['sections'] as $section ) {
			$form_html .= $this->render_section( $section, $user_data );
		}
		
		// End form
		$form_html .= $this->render_form_end();
		
		return $form_html;
	}

	/**
	 * Render assessment results
	 * 
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return string Rendered results HTML
	 */
	public function render_assessment_results( $user_id, $assessment_type ) {
		// Get user scores
		$scores = $this->unified_scoring_service->get_user_scores( $user_id, $assessment_type );
		if ( empty( $scores ) ) {
			return $this->render_error_message( 'No results found for this assessment.' );
		}

		$results_html = '';
		
		// Render overall score
		$results_html .= $this->render_overall_score( $scores['overall_score'] );
		
		// Render pillar scores
		$results_html .= $this->render_pillar_scores( $scores['pillar_scores'] );
		
		// Render recommendations
		$results_html .= $this->render_recommendations( $scores, $assessment_type );
		
		return $results_html;
	}

	/**
	 * Render form start
	 */
	private function render_form_start( $assessment_type ) {
		$nonce = wp_create_nonce( 'ennu_assessment_' . $assessment_type );
		
		return sprintf(
			'<form id="ennu-assessment-form" class="ennu-assessment-form" data-assessment-type="%s">
				<input type="hidden" name="ennu_nonce" value="%s">
				<input type="hidden" name="assessment_type" value="%s">',
			esc_attr( $assessment_type ),
			esc_attr( $nonce ),
			esc_attr( $assessment_type )
		);
	}

	/**
	 * Render form end
	 */
	private function render_form_end() {
		return sprintf(
			'<div class="ennu-form-submit">
				<button type="submit" class="ennu-submit-btn">Submit Assessment</button>
			</div>
			</form>'
		);
	}

	/**
	 * Render section
	 */
	private function render_section( $section, $user_data ) {
		$section_html = sprintf(
			'<div class="ennu-section" id="section-%s">
				<h3 class="ennu-section-title">%s</h3>
				<div class="ennu-section-description">%s</div>',
			esc_attr( $section['id'] ),
			esc_html( $section['title'] ),
			wp_kses_post( $section['description'] )
		);

		// Render questions
		foreach ( $section['questions'] as $question ) {
			$section_html .= $this->render_question( $question, $user_data );
		}

		$section_html .= '</div>';
		
		return $section_html;
	}

	/**
	 * Render question
	 */
	private function render_question( $question, $user_data ) {
		$question_html = sprintf(
			'<div class="ennu-question" id="question-%s">
				<label class="ennu-question-label">%s</label>',
			esc_attr( $question['id'] ),
			esc_html( $question['text'] )
		);

		// Render based on question type
		switch ( $question['type'] ) {
			case 'radio':
				$question_html .= $this->render_radio_question( $question, $user_data );
				break;
			case 'range':
				$question_html .= $this->render_range_question( $question, $user_data );
				break;
			case 'checkbox':
				$question_html .= $this->render_checkbox_question( $question, $user_data );
				break;
			case 'text':
				$question_html .= $this->render_text_question( $question, $user_data );
				break;
		}

		$question_html .= '</div>';
		
		return $question_html;
	}

	/**
	 * Render radio question
	 */
	private function render_radio_question( $question, $user_data ) {
		$options_html = '';
		$current_value = isset( $user_data[ $question['id'] ] ) ? $user_data[ $question['id'] ] : '';
		
		foreach ( $question['options'] as $option ) {
			$checked = ( $current_value === $option['value'] ) ? ' checked' : '';
			$options_html .= sprintf(
				'<label class="ennu-radio-option">
					<input type="radio" name="%s" value="%s"%s>
					<span class="ennu-radio-text">%s</span>
				</label>',
				esc_attr( $question['id'] ),
				esc_attr( $option['value'] ),
				$checked,
				esc_html( $option['text'] )
			);
		}
		
		return sprintf(
			'<div class="ennu-options">%s</div>',
			$options_html
		);
	}

	/**
	 * Render range question
	 */
	private function render_range_question( $question, $user_data ) {
		$current_value = isset( $user_data[ $question['id'] ] ) ? $user_data[ $question['id'] ] : $question['default'];
		
		return sprintf(
			'<div class="ennu-range-container">
				<input type="range" name="%s" min="%d" max="%d" value="%d" class="ennu-range-input">
				<span class="ennu-range-value">%d</span>
			</div>',
			esc_attr( $question['id'] ),
			$question['min'],
			$question['max'],
			$current_value,
			$current_value
		);
	}

	/**
	 * Render checkbox question
	 */
	private function render_checkbox_question( $question, $user_data ) {
		$options_html = '';
		$current_values = isset( $user_data[ $question['id'] ] ) ? (array) $user_data[ $question['id'] ] : array();
		
		foreach ( $question['options'] as $option ) {
			$checked = in_array( $option['value'], $current_values ) ? ' checked' : '';
			$options_html .= sprintf(
				'<label class="ennu-checkbox-option">
					<input type="checkbox" name="%s[]" value="%s"%s>
					<span class="ennu-checkbox-text">%s</span>
				</label>',
				esc_attr( $question['id'] ),
				esc_attr( $option['value'] ),
				$checked,
				esc_html( $option['text'] )
			);
		}
		
		return sprintf(
			'<div class="ennu-options">%s</div>',
			$options_html
		);
	}

	/**
	 * Render text question
	 */
	private function render_text_question( $question, $user_data ) {
		$current_value = isset( $user_data[ $question['id'] ] ) ? $user_data[ $question['id'] ] : '';
		
		return sprintf(
			'<textarea name="%s" class="ennu-text-input" placeholder="%s">%s</textarea>',
			esc_attr( $question['id'] ),
			esc_attr( $question['placeholder'] ?? '' ),
			esc_textarea( $current_value )
		);
	}

	/**
	 * Render overall score
	 */
	private function render_overall_score( $score ) {
		$score_class = $this->get_score_class( $score );
		
		return sprintf(
			'<div class="ennu-overall-score">
				<h2>Overall Score</h2>
				<div class="ennu-score-display %s">
					<span class="ennu-score-value">%d</span>
					<span class="ennu-score-label">%s</span>
				</div>
			</div>',
			esc_attr( $score_class ),
			$score,
			esc_html( $this->get_score_label( $score ) )
		);
	}

	/**
	 * Render pillar scores
	 */
	private function render_pillar_scores( $pillar_scores ) {
		$pillars_html = '';
		
		foreach ( $pillar_scores as $pillar => $score ) {
			$score_class = $this->get_score_class( $score );
			$pillars_html .= sprintf(
				'<div class="ennu-pillar-score">
					<h3>%s</h3>
					<div class="ennu-score-display %s">
						<span class="ennu-score-value">%d</span>
						<span class="ennu-score-label">%s</span>
					</div>
				</div>',
				esc_html( $this->get_pillar_name( $pillar ) ),
				esc_attr( $score_class ),
				$score,
				esc_html( $this->get_score_label( $score ) )
			);
		}
		
		return sprintf(
			'<div class="ennu-pillar-scores">
				<h2>Pillar Scores</h2>
				<div class="ennu-pillars-grid">%s</div>
			</div>',
			$pillars_html
		);
	}

	/**
	 * Render recommendations
	 */
	private function render_recommendations( $scores, $assessment_type ) {
		$recommendations = $this->get_recommendations( $scores, $assessment_type );
		
		if ( empty( $recommendations ) ) {
			return '';
		}
		
		$recommendations_html = '';
		foreach ( $recommendations as $recommendation ) {
			$recommendations_html .= sprintf(
				'<div class="ennu-recommendation">
					<h4>%s</h4>
					<p>%s</p>
				</div>',
				esc_html( $recommendation['title'] ),
				esc_html( $recommendation['description'] )
			);
		}
		
		return sprintf(
			'<div class="ennu-recommendations">
				<h2>Recommendations</h2>
				<div class="ennu-recommendations-list">%s</div>
			</div>',
			$recommendations_html
		);
	}

	/**
	 * Render error message
	 */
	private function render_error_message( $message ) {
		return sprintf(
			'<div class="ennu-error-message">
				<p>%s</p>
			</div>',
			esc_html( $message )
		);
	}

	/**
	 * Get assessment definitions
	 */
	private function get_assessment_definitions( $assessment_type ) {
		// Use the unified scoring service to get definitions
		return $this->unified_scoring_service->get_assessment_definitions( $assessment_type );
	}

	/**
	 * Get score class
	 */
	private function get_score_class( $score ) {
		if ( $score >= 80 ) {
			return 'excellent';
		} elseif ( $score >= 60 ) {
			return 'good';
		} elseif ( $score >= 40 ) {
			return 'fair';
		} else {
			return 'poor';
		}
	}

	/**
	 * Get score label
	 */
	private function get_score_label( $score ) {
		if ( $score >= 80 ) {
			return 'Excellent';
		} elseif ( $score >= 60 ) {
			return 'Good';
		} elseif ( $score >= 40 ) {
			return 'Fair';
		} else {
			return 'Poor';
		}
	}

	/**
	 * Get pillar name
	 */
	private function get_pillar_name( $pillar ) {
		$pillar_names = array(
			'nutrition' => 'Nutrition',
			'fitness' => 'Fitness',
			'sleep' => 'Sleep',
			'stress' => 'Stress Management',
			'energy' => 'Energy',
			'recovery' => 'Recovery',
			'longevity' => 'Longevity',
			'performance' => 'Performance',
			'wellness' => 'Overall Wellness'
		);
		
		return isset( $pillar_names[ $pillar ] ) ? $pillar_names[ $pillar ] : ucfirst( $pillar );
	}

	/**
	 * Get recommendations
	 */
	private function get_recommendations( $scores, $assessment_type ) {
		// This would be implemented based on the assessment type and scores
		// For now, return basic recommendations
		return array(
			array(
				'title' => 'Continue Current Practices',
				'description' => 'Your scores indicate good health practices. Keep up the excellent work!'
			),
			array(
				'title' => 'Consider Professional Guidance',
				'description' => 'For personalized recommendations, consider consulting with a health professional.'
			)
		);
	}
} 