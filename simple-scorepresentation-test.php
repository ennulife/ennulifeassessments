<?php
/**
 * Simple test for scorepresentation shortcode
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Simple test shortcode
add_shortcode( 'simple_scorepresentation', function( $atts ) {
	$atts = shortcode_atts( array(
		'type' => 'lifescore',
	), $atts );
	
	$type = sanitize_text_field( $atts['type'] );
	$user_id = get_current_user_id();
	
	// Simple test output
	$output = '<div style="border: 2px solid #10b981; padding: 20px; margin: 20px 0; background: #f0fdf4;">';
	$output .= '<h3>Score Presentation Test</h3>';
	$output .= '<p><strong>Type:</strong> ' . esc_html( $type ) . '</p>';
	$output .= '<p><strong>User ID:</strong> ' . $user_id . '</p>';
	$output .= '<p><strong>ENNU Life Score:</strong> ' . get_user_meta( $user_id, 'ennu_life_score', true ) . '</p>';
	$output .= '<p><strong>Mind Score:</strong> ' . get_user_meta( $user_id, 'ennu_pillar_mind_score', true ) . '</p>';
	$output .= '<p><strong>Body Score:</strong> ' . get_user_meta( $user_id, 'ennu_pillar_body_score', true ) . '</p>';
	$output .= '<p><strong>Lifestyle Score:</strong> ' . get_user_meta( $user_id, 'ennu_pillar_lifestyle_score', true ) . '</p>';
	$output .= '<p><strong>Aesthetics Score:</strong> ' . get_user_meta( $user_id, 'ennu_pillar_aesthetics_score', true ) . '</p>';
	
	// If it's an assessment type, show assessment-specific data
	if ( $type !== 'lifescore' && ! in_array( $type, array( 'pmind', 'pbody', 'plifestyle', 'paesthetics' ) ) ) {
		$output .= '<hr style="margin: 15px 0; border: 1px solid #10b981;">';
		$output .= '<h4>Assessment-Specific Data:</h4>';
		
		// Check historical scores (most recent)
		$historical_scores = get_user_meta( $user_id, 'ennu_' . $type . '_historical_scores', true );
		$output .= '<p><strong>Historical Scores:</strong> ' . ( is_array( $historical_scores ) ? 'Array found (' . count( $historical_scores ) . ' entries)' : 'Not found' ) . '</p>';
		if ( is_array( $historical_scores ) && ! empty( $historical_scores ) ) {
			$most_recent = end( $historical_scores );
			$output .= '<p><strong>Most Recent Score:</strong> ' . ( isset( $most_recent['score'] ) ? $most_recent['score'] : 'Not set' ) . '</p>';
			$output .= '<p><strong>Most Recent Date:</strong> ' . ( isset( $most_recent['date'] ) ? $most_recent['date'] : 'Not set' ) . '</p>';
		}
		
		// Check pillar scores from assessment
		$assessment_pillar_scores = get_user_meta( $user_id, 'ennu_' . $type . '_pillar_scores', true );
		$output .= '<p><strong>Assessment Pillar Scores:</strong> ' . ( is_array( $assessment_pillar_scores ) ? 'Array found' : 'Not found' ) . '</p>';
		if ( is_array( $assessment_pillar_scores ) ) {
			$output .= '<p><strong>Mind Score:</strong> ' . ( isset( $assessment_pillar_scores['mind'] ) ? $assessment_pillar_scores['mind'] : 'Not set' ) . '</p>';
			$output .= '<p><strong>Body Score:</strong> ' . ( isset( $assessment_pillar_scores['body'] ) ? $assessment_pillar_scores['body'] : 'Not set' ) . '</p>';
			$output .= '<p><strong>Lifestyle Score:</strong> ' . ( isset( $assessment_pillar_scores['lifestyle'] ) ? $assessment_pillar_scores['lifestyle'] : 'Not set' ) . '</p>';
			$output .= '<p><strong>Aesthetics Score:</strong> ' . ( isset( $assessment_pillar_scores['aesthetics'] ) ? $assessment_pillar_scores['aesthetics'] : 'Not set' ) . '</p>';
		}
		
		// Show full historical data
		if ( is_array( $historical_scores ) ) {
			$output .= '<p><strong>Full Historical Data:</strong> <pre>' . print_r( $historical_scores, true ) . '</pre></p>';
		}
	}
	
	$output .= '</div>';
	
	return $output;
} );

// Test if the main shortcode class method exists
add_action( 'wp_footer', function() {
	if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
		$shortcodes = new ENNU_Assessment_Shortcodes();
		if ( method_exists( $shortcodes, 'render_score_presentation' ) ) {
			echo '<!-- Score presentation method exists -->';
		} else {
			echo '<!-- Score presentation method does NOT exist -->';
		}
	} else {
		echo '<!-- ENNU_Assessment_Shortcodes class does NOT exist -->';
	}
} ); 