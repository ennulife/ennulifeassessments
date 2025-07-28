<?php
/**
 * ENNU Shortcode Manager
 * Dedicated class for handling all shortcode registration and rendering
 * 
 * @package ENNU_Life_Assessments
 * @version 64.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages all assessment shortcodes with clean separation of concerns
 */
class ENNU_Shortcode_Manager {

	private $renderer;

	public function __construct() {
		// The legacy shortcode file is now repurposed as a renderer
		if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
			$this->renderer = new ENNU_Assessment_Shortcodes();
		}
		$this->init_shortcodes();
	}

	/**
	 * Initialize all assessment shortcodes
	 */
	private function init_shortcodes() {
		if ( ! $this->renderer ) {
			return;
		}

		$definitions = $this->renderer->get_all_assessment_definitions();

		foreach ( $definitions as $assessment_key => $config ) {
			add_shortcode( "ennu-{$assessment_key}", array( $this, 'render_assessment_shortcode_dynamic' ) );
			add_shortcode( "ennu-{$assessment_key}-results", array( $this, 'render_results_shortcode_dynamic' ) );
			add_shortcode( "ennu-{$assessment_key}-assessment-details", array( $this, 'render_details_shortcode_dynamic' ) );
		}

		// Core shortcodes
		add_shortcode( 'ennu-user-dashboard', array( $this->renderer, 'render_user_dashboard' ) );
		add_shortcode( 'ennu-assessment-results', array( $this->renderer, 'render_thank_you_page' ) ); // Generic fallback
		add_shortcode( 'ennu-assessments', array( $this->renderer, 'render_assessments_listing' ) );
		add_shortcode( 'scorepresentation', array( $this->renderer, 'render_score_presentation' ) );

	}

	/**
	 * Dynamically handles rendering for any assessment shortcode.
	 */
	public function render_assessment_shortcode_dynamic( $atts, $content = '', $tag = '' ) {
		if ( $this->renderer ) {
			return $this->renderer->render_assessment_shortcode( $atts, $content, $tag );
		}
		return '';
	}

	/**
	 * Dynamically handles rendering for any results page shortcode.
	 */
	public function render_results_shortcode_dynamic( $atts, $content = '', $tag = '' ) {
		if ( $this->renderer ) {
			// The legacy renderer uses the same function for all thank you/results pages
			return $this->renderer->render_thank_you_page( $atts, $content, $tag );
		}
		return '';
	}

	/**
	 * Dynamically handles rendering for any detailed results page shortcode.
	 */
	public function render_details_shortcode_dynamic( $atts, $content = '', $tag = '' ) {
		if ( $this->renderer ) {
			$assessment_type = str_replace( '-assessment-details', '', str_replace('ennu-', '', $tag) );
			return $this->renderer->render_detailed_results_page( $atts, $content, $assessment_type );
		}
		return '';
	}

	/**
	 * Get all assessment definitions
	 * Required for compatibility with admin interface
	 */
	public function get_all_assessment_definitions() {
		if ( $this->renderer ) {
			return $this->renderer->get_all_assessment_definitions();
		}
		return array();
	}
}

// NOTE: The ENNU_Form_Renderer class has been removed from this file as its
// logic was part of the incomplete refactoring. We are now using the slimmed-down
// legacy class as the renderer. 