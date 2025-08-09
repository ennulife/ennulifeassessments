<?php
/**
 * ENNU Life Enhanced Admin Class
 *
 * @package   ENNU Life Assessments
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     3.37.17
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Enhanced_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_ennu_detect_pages_submit', array( $this, 'ajax_detect_pages_submit' ) );
		add_action( 'wp_ajax_ennu_remove_biomarker_flag', array( $this, 'ajax_remove_biomarker_flag' ) );
		add_action( 'wp_ajax_ennu_clear_all_user_data', array( $this, 'ajax_clear_all_user_data' ) );
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'ennu_life_settings', 'ennu_life_settings' );
		register_setting( 'ennu_life_settings', 'ennu_created_pages' );
		register_setting( 'ennu_life_settings', 'ennu_auto_select_pages' );
	}

	/**
	 * Test method to verify the admin class is working
	 */
	public function test_admin_notice() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'ennu-life-settings' ) {
			echo '<div class="notice notice-success is-dismissible"><p><strong>ENNU Enhanced Admin Test:</strong> The enhanced admin class is working correctly!</p></div>';
		}
	}

	public function initialize_csrf_protection() {
		if ( class_exists( 'ENNU_CSRF_Protection' ) ) {
			ENNU_CSRF_Protection::get_instance();
		}
	}

	/**
	 * Setup pages - Create all necessary assessment pages
	 */
	private function setup_pages() {
		$all_definitions = array(
			'hair' => array('title' => 'Hair Loss Assessment'),
			'ed-treatment' => array('title' => 'Erectile Dysfunction Assessment'),
			'weight-loss' => array('title' => 'Weight Loss Assessment'),
			'health' => array('title' => 'General Health Assessment'),
			'health-optimization' => array('title' => 'Health Optimization Assessment'),
			'skin' => array('title' => 'Skin Health Assessment'),
			'hormone' => array('title' => 'Hormone Assessment'),
			'testosterone' => array('title' => 'Testosterone Assessment'),
			'menopause' => array('title' => 'Menopause Assessment'),
			'sleep' => array('title' => 'Sleep Quality Assessment'),
		);
		$assessment_keys = array_keys( $all_definitions );

		$pages_to_create = array();

		// Parent Pages (created first) - SEO Optimized Titles with Short Menu Labels
		$pages_to_create['dashboard']   = array(
			'title'      => 'Health Assessment Dashboard | Track Your Wellness Journey | ENNU Life',
			'menu_label' => 'Dashboard',
			'content'    => '[ennu-user-dashboard]',
			'parent'     => 0,
		);
		$pages_to_create['assessments'] = array(
			'title'      => 'Free Health Assessments | Comprehensive Wellness Evaluations | ENNU Life',
			'menu_label' => 'Assessments',
			'content'    => '[ennu-assessments]',
			'parent'     => 0,
		);

		// Core utility page - SEO Optimized
		$pages_to_create['assessment-results'] = array(
			'title'      => 'Health Assessment Results | Personalized Wellness Insights | ENNU Life',
			'menu_label' => 'Results',
			'content'    => '[ennu-assessment-results]',
			'parent'     => 0,
		);

		// Registration page (Welcome Assessment) - Root level for better UX
		$pages_to_create['registration'] = array(
			'title'      => 'Health Registration | Start Your Wellness Journey | ENNU Life',
			'menu_label' => 'Registration',
			'content'    => '[ennu-welcome]',
			'parent'     => 0,
		);

		// Signup page - Premium product selection
		$pages_to_create['signup'] = array(
			'title'      => 'Sign Up | Premium Health Services | ENNU Life',
			'menu_label' => 'Sign Up',
			'content'    => '[ennu-signup]',
			'parent'     => 0,
		);

		// Consultation & Call Pages - SEO Optimized
		$pages_to_create['call']            = array(
			'title'      => 'Schedule a Call | Free Health Consultation | ENNU Life',
			'menu_label' => 'Schedule Call',
			'content'    => 'Schedule your free health consultation with our experts.',
			'parent'     => 0,
		);
		$pages_to_create['ennu-life-score'] = array(
			'title'      => 'Get Your ENNU Life Score | Personalized Health Assessment | ENNU Life',
			'menu_label' => 'ENNU Life Score',
			'content'    => 'Discover your personalized ENNU Life Score and health insights.',
			'parent'     => 0,
		);

		// SEO-Optimized Assessment-Specific Titles with Short Menu Labels
		$seo_assessment_titles = array(
			'hair'                => array(
				'main'       => 'Hair Loss Assessment | Male Pattern Baldness Evaluation | ENNU Life',
				'menu_label' => 'Hair Loss',
				'results'    => 'Hair Loss Assessment Results | Personalized Hair Health Analysis | ENNU Life',
				'details'    => 'Hair Loss Treatment Options | Detailed Hair Health Dossier | ENNU Life',
				'booking'    => 'Hair Treatment Consultation | Hair Loss Solutions | ENNU Life',
			),
			'ed-treatment'        => array(
				'main'       => 'Erectile Dysfunction Assessment | ED Treatment Evaluation | ENNU Life',
				'menu_label' => 'ED Treatment',
				'results'    => 'ED Assessment Results | Erectile Dysfunction Analysis | ENNU Life',
				'details'    => 'ED Treatment Options | Erectile Dysfunction Solutions Dossier | ENNU Life',
				'booking'    => 'ED Treatment Consultation | Erectile Dysfunction Solutions | ENNU Life',
			),
			'health-optimization' => array(
				'main'       => 'Health Optimization Assessment | Comprehensive Wellness Check | ENNU Life',
				'menu_label' => 'Health Optimization',
				'results'    => 'Health Optimization Results | Personalized Wellness Plan | ENNU Life',
				'details'    => 'Health Optimization Solutions | Detailed Wellness Improvement Plan | ENNU Life',
				'booking'    => 'Health Consultation | Comprehensive Wellness Evaluation | ENNU Life',
			),
			'health'              => array(
				'main'       => 'General Health Assessment | Complete Wellness Evaluation | ENNU Life',
				'menu_label' => 'General Health',
				'results'    => 'Health Assessment Results | Comprehensive Wellness Analysis | ENNU Life',
				'details'    => 'Health Improvement Plan | Detailed Wellness Solutions Dossier | ENNU Life',
				'booking'    => 'Health Consultation | Comprehensive Wellness Evaluation | ENNU Life',
			),
			'hormone'             => array(
				'main'       => 'Hormone Assessment | Testosterone & Hormone Level Evaluation | ENNU Life',
				'menu_label' => 'Hormone Balance',
				'results'    => 'Hormone Assessment Results | Hormone Balance Analysis | ENNU Life',
				'details'    => 'Hormone Therapy Options | Detailed Hormone Balance Solutions | ENNU Life',
				'booking'    => 'Hormone Consultation | Hormone Balance Specialists | ENNU Life',
			),
			'menopause'           => array(
				'main'       => 'Menopause Assessment | Hormone Replacement Therapy Evaluation | ENNU Life',
				'menu_label' => 'Menopause',
				'results'    => 'Menopause Assessment Results | HRT Suitability Analysis | ENNU Life',
				'details'    => 'Menopause Treatment Options | HRT Solutions Dossier | ENNU Life',
				'booking'    => 'Menopause Consultation | HRT Specialists | ENNU Life',
			),
			'skin'                => array(
				'main'       => 'Skin Health Assessment | Anti-Aging Skincare Evaluation | ENNU Life',
				'menu_label' => 'Skin Health',
				'results'    => 'Skin Assessment Results | Personalized Skincare Analysis | ENNU Life',
				'details'    => 'Skin Treatment Options | Anti-Aging Skincare Solutions | ENNU Life',
				'booking'    => 'Skin Treatment Consultation | Anti-Aging Skincare | ENNU Life',
			),
			'sleep'               => array(
				'main'       => 'Sleep Quality Assessment | Insomnia & Sleep Disorder Evaluation | ENNU Life',
				'menu_label' => 'Sleep Quality',
				'results'    => 'Sleep Assessment Results | Sleep Quality Analysis | ENNU Life',
				'details'    => 'Sleep Improvement Solutions | Detailed Sleep Optimization Plan | ENNU Life',
				'booking'    => 'Sleep Consultation | Sleep Optimization Specialists | ENNU Life',
			),
			'testosterone'        => array(
				'main'       => 'Testosterone Assessment | Low T Evaluation & TRT Screening | ENNU Life',
				'menu_label' => 'Testosterone',
				'results'    => 'Testosterone Assessment Results | Low T Analysis & TRT Evaluation | ENNU Life',
				'details'    => 'Testosterone Replacement Therapy | TRT Options & Solutions | ENNU Life',
				'booking'    => 'Testosterone Consultation | TRT Specialists | ENNU Life',
			),
			'weight-loss'         => array(
				'main'       => 'Weight Loss Assessment | Medical Weight Management Evaluation | ENNU Life',
				'menu_label' => 'Weight Loss',
				'results'    => 'Weight Loss Assessment Results | Personalized Weight Management Plan | ENNU Life',
				'details'    => 'Weight Loss Solutions | Medical Weight Management Options | ENNU Life',
				'booking'    => 'Weight Loss Consultation | Medical Weight Management | ENNU Life',
			),
		);

		// Assessment Form Pages (children of /assessments/)
		foreach ( $assessment_keys as $key ) {
			// Assessment keys are already in the correct format (e.g., 'hair', 'ed-treatment', 'weight-loss')
			$slug = $key;

			// Skip welcome assessment - it's now at root level
			if ( 'welcome' === $key ) {
				continue;
			}

			// Use SEO-optimized title if available, otherwise fallback to definition title with SEO enhancement
			if ( isset( $seo_assessment_titles[ $slug ]['main'] ) ) {
				$title = $seo_assessment_titles[ $slug ]['main'];
			} else {
				$base_title = $all_definitions[ $key ]['title'] ?? ucwords( str_replace( '-', ' ', $key ) );
				$title      = $base_title . ' | Professional Health Evaluation | ENNU Life';
			}

			// Form Page (child of assessments)
			$pages_to_create[ "assessments/{$slug}" ] = array(
				'title'      => $title,
				'menu_label' => $seo_assessment_titles[ $slug ]['menu_label'] ?? ucwords( str_replace( '-', ' ', $key ) ),
				'content'    => "[ennu-{$slug}]",
				'parent'     => 'assessments',
			);

			// Results Page (child of specific assessment) - SEO Optimized
			$results_slug  = $slug . '-results';
			$results_title = isset( $seo_assessment_titles[ $slug ]['results'] )
				? $seo_assessment_titles[ $slug ]['results']
				: ucwords( str_replace( '-', ' ', $key ) ) . ' Results | Personalized Health Analysis | ENNU Life';

			$pages_to_create[ "assessments/{$slug}/results" ] = array(
				'title'      => $results_title,
				'menu_label' => 'Results',
				'content'    => "[ennu-{$results_slug}]",
				'parent'     => "assessments/{$slug}",
			);

			// Details Page (child of specific assessment) - SEO Optimized
			$details_slug  = $slug . '-assessment-details';
			$details_title = isset( $seo_assessment_titles[ $slug ]['details'] )
				? $seo_assessment_titles[ $slug ]['details']
				: ucwords( str_replace( '-', ' ', $key ) ) . ' Treatment Options | Detailed Health Solutions | ENNU Life';

			$pages_to_create[ "assessments/{$slug}/details" ] = array(
				'title'      => $details_title,
				'menu_label' => 'Treatment Options',
				'content'    => "[ennu-{$details_slug}]",
				'parent'     => "assessments/{$slug}",
			);

			// Booking Page (child of specific assessment) - SEO Optimized
			$booking_slug  = $slug . '-consultation';
			$booking_title = isset( $seo_assessment_titles[ $slug ]['booking'] )
			? $seo_assessment_titles[ $slug ]['booking']
			: ucwords( str_replace( '-', ' ', $key ) ) . ' Consultation | Professional Health Consultation | ENNU Life';

			$pages_to_create[ "assessments/{$slug}/consultation" ] = array(
				'title'      => $booking_title,
				'menu_label' => 'Book Consultation',
				'content'    => "[ennu-{$booking_slug}]",
				'parent'     => "assessments/{$slug}",
			);
		}

		$page_mappings   = get_option( 'ennu_created_pages', array() );
		$created_parents = array(); // Track parent page IDs

		// Sort pages to create parents first
		$sorted_pages = array();
		foreach ( $pages_to_create as $slug => $page_data ) {
			if ( $page_data['parent'] === 0 ) {
				$sorted_pages[ $slug ] = $page_data; // Parent pages first
			}
		}
		foreach ( $pages_to_create as $slug => $page_data ) {
			if ( $page_data['parent'] !== 0 ) {
				$sorted_pages[ $slug ] = $page_data; // Child pages after
			}
		}

		foreach ( $sorted_pages as $slug => $page_data ) {
			// Validate page data before creation
			if ( empty( $page_data['title'] ) ) {
				error_log( 'ENNU Page Creation: Missing title for slug: ' . $slug );
				continue;
			}

			if ( empty( $page_data['content'] ) ) {
				error_log( 'ENNU Page Creation: Missing content for slug: ' . $slug );
				continue;
			}

			// Only act if the page isn't already mapped and has a valid ID
			if ( empty( $page_mappings[ $slug ] ) || ! get_post( $page_mappings[ $slug ] ) ) {

				// Determine parent ID
				$parent_id = 0;
				if ( $page_data['parent'] !== 0 ) {
					if ( isset( $created_parents[ $page_data['parent'] ] ) ) {
						$parent_id = $created_parents[ $page_data['parent'] ];
					} elseif ( isset( $page_mappings[ $page_data['parent'] ] ) ) {
						$parent_id = $page_mappings[ $page_data['parent'] ];
					}
				}

				// Extract the final slug (last part after /)
				$final_slug = basename( $slug );

				// First, check if a page with this path already exists
				$existing_page = get_page_by_path( $slug, OBJECT, 'page' );

				if ( $existing_page ) {
					// If it exists, just map it
					$page_mappings[ $slug ] = $existing_page->ID;
					if ( $page_data['parent'] === 0 ) {
						$created_parents[ $slug ] = $existing_page->ID;
					}
				} else {
					// If it doesn't exist, create it
					$page_id = wp_insert_post(
						array(
							'post_title'   => $page_data['title'],
							'post_name'    => $final_slug,
							'post_content' => $page_data['content'],
							'post_status'  => 'publish',
							'post_type'    => 'page',
							'post_parent'  => $parent_id,
						)
					);

					// Handle page creation success/failure
					if ( $page_id > 0 ) {
						// Set Elementor Canvas template for clean, distraction-free layout
						update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );

						$page_mappings[ $slug ] = $page_id;
						if ( $page_data['parent'] === 0 ) {
							$created_parents[ $slug ] = $page_id;
						}

						// Store menu label as post meta for navigation
						if ( isset( $page_data['menu_label'] ) ) {
							update_post_meta( $page_id, '_ennu_menu_label', $page_data['menu_label'] );
						}

						error_log( 'ENNU Page Creation: Successfully created page "' . $page_data['title'] . '" with ID ' . $page_id );
					} else {
						error_log( 'ENNU Page Creation: Failed to create page "' . $page_data['title'] . '" for slug: ' . $slug );
					}
				}
			} else {
				// Page already exists, track it if it's a parent
				if ( $page_data['parent'] === 0 ) {
					$created_parents[ $slug ] = $page_mappings[ $slug ];
				}

				// Update menu label if it exists
				if ( isset( $page_data['menu_label'] ) ) {
					update_post_meta( $page_mappings[ $slug ], '_ennu_menu_label', $page_data['menu_label'] );
				}
			}
		}
		update_option( 'ennu_created_pages', $page_mappings );
		
		// Also update the settings with page mappings
		$settings = $this->get_plugin_settings();
		$settings['page_mappings'] = $page_mappings;
		update_option( 'ennu_life_settings', $settings );

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Assessment pages have been created and menu updated successfully!', 'ennulifeassessments' ) . '</p></div>';
	}

	/**
	 * Delete all mapped assessment pages
	 */
	private function delete_pages() {
		$page_mappings = get_option( 'ennu_created_pages', array() );
		if ( ! empty( $page_mappings ) ) {
			foreach ( $page_mappings as $page_id ) {
				if ( get_post_type( $page_id ) === 'page' ) {
					wp_delete_post( $page_id, true );
				}
			}
			// Clear the mappings after deletion
			update_option( 'ennu_created_pages', array() );
			
			// Also clear from settings
			$settings = $this->get_plugin_settings();
			$settings['page_mappings'] = array();
			update_option( 'ennu_life_settings', $settings );
		}
	}

	/**
	 * Get plugin settings
	 */
	private function get_plugin_settings() {
		$defaults = array(
			'page_mappings' => array(),
		);
		
		$settings = get_option( 'ennu_life_settings', $defaults );
		
		// Sync page mappings from ennu_created_pages to settings
		$created_pages = get_option( 'ennu_created_pages', array() );
		if ( ! empty( $created_pages ) && empty( $settings['page_mappings'] ) ) {
			$settings['page_mappings'] = $created_pages;
			update_option( 'ennu_life_settings', $settings );
		}
		
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Sync existing page mappings from ennu_created_pages to settings
	 */
	private function sync_existing_page_mappings() {
		$created_pages = get_option( 'ennu_created_pages', array() );
		$settings = get_option( 'ennu_life_settings', array() );
		
		if ( ! empty( $created_pages ) && empty( $settings['page_mappings'] ) ) {
			$settings['page_mappings'] = $created_pages;
			update_option( 'ennu_life_settings', $settings );
		}
		
		// Auto-detect and map existing pages that might not be in the mappings
		$this->auto_detect_existing_pages();
	}
	
	/**
	 * Auto-detect existing pages by scanning for ENNU shortcodes
	 */
	private function auto_detect_existing_pages() {
		$settings = $this->get_plugin_settings();
		$page_mappings = $settings['page_mappings'];
		$updated = false;
		
		// Get all published pages
		$pages = get_pages( array(
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		// Define shortcode to mapping key relationships
		$shortcode_mappings = array(
			'[ennu-user-dashboard]' => 'dashboard',
			'[ennu-assessments]' => 'assessments',
			'[ennu-welcome]' => 'registration',
			'[ennu-signup]' => 'signup',
			'[ennu-assessment-results]' => 'assessment-results',
			'[ennu-hair]' => 'assessments/hair',
			'[ennu-ed-treatment]' => 'assessments/ed-treatment',
			'[ennu-weight-loss]' => 'assessments/weight-loss',
			'[ennu-health]' => 'assessments/health',
			'[ennu-health-optimization]' => 'assessments/health-optimization',
			'[ennu-skin]' => 'assessments/skin',
			'[ennu-hormone]' => 'assessments/hormone',
			'[ennu-testosterone]' => 'assessments/testosterone',
			'[ennu-menopause]' => 'assessments/menopause',
			'[ennu-sleep]' => 'assessments/sleep',
			// CRITICAL FIX: Add shortcode mappings for sub-pages
			'[ennu-hair-results]' => 'assessments/hair/results',
			'[ennu-hair-details]' => 'assessments/hair/details',
			'[ennu-hair-consultation]' => 'assessments/hair/consultation',
			'[ennu-ed-treatment-results]' => 'assessments/ed-treatment/results',
			'[ennu-ed-treatment-details]' => 'assessments/ed-treatment/details',
			'[ennu-ed-treatment-consultation]' => 'assessments/ed-treatment/consultation',
			'[ennu-weight-loss-results]' => 'assessments/weight-loss/results',
			'[ennu-weight-loss-details]' => 'assessments/weight-loss/details',
			'[ennu-weight-loss-consultation]' => 'assessments/weight-loss/consultation',
			'[ennu-health-results]' => 'assessments/health/results',
			'[ennu-health-details]' => 'assessments/health/details',
			'[ennu-health-consultation]' => 'assessments/health/consultation',
			'[ennu-health-optimization-results]' => 'assessments/health-optimization/results',
			'[ennu-health-optimization-details]' => 'assessments/health-optimization/details',
			'[ennu-health-optimization-consultation]' => 'assessments/health-optimization/consultation',
			'[ennu-skin-results]' => 'assessments/skin/results',
			'[ennu-skin-details]' => 'assessments/skin/details',
			'[ennu-skin-consultation]' => 'assessments/skin/consultation',
			'[ennu-hormone-results]' => 'assessments/hormone/results',
			'[ennu-hormone-details]' => 'assessments/hormone/details',
			'[ennu-hormone-consultation]' => 'assessments/hormone/consultation',
			'[ennu-testosterone-results]' => 'assessments/testosterone/results',
			'[ennu-testosterone-details]' => 'assessments/testosterone/details',
			'[ennu-testosterone-consultation]' => 'assessments/testosterone/consultation',
			'[ennu-menopause-results]' => 'assessments/menopause/results',
			'[ennu-menopause-details]' => 'assessments/menopause/details',
			'[ennu-menopause-consultation]' => 'assessments/menopause/consultation',
			'[ennu-sleep-results]' => 'assessments/sleep/results',
			'[ennu-sleep-details]' => 'assessments/sleep/details',
			'[ennu-sleep-consultation]' => 'assessments/sleep/consultation',
		);
		
		// Scan each page for shortcodes
		foreach ( $pages as $page ) {
			$content = $page->post_content;
			
			// Check for each shortcode
			foreach ( $shortcode_mappings as $shortcode => $mapping_key ) {
				if ( strpos( $content, $shortcode ) !== false ) {
					// Found a shortcode, check if it's not already mapped
					if ( empty( $page_mappings[ $mapping_key ] ) ) {
						$page_mappings[ $mapping_key ] = $page->ID;
						$updated = true;
						error_log( "ENNU Auto-Detect: Found page '{$page->post_title}' (ID: {$page->ID}) with shortcode '{$shortcode}' - mapped to '{$mapping_key}'" );
					} else {
						// Already mapped, but log if it's a different page
						$existing_page_id = $page_mappings[ $mapping_key ];
						if ( $existing_page_id != $page->ID ) {
							error_log( "ENNU Auto-Detect: Warning - shortcode '{$shortcode}' found on page '{$page->post_title}' (ID: {$page->ID}) but '{$mapping_key}' is already mapped to page ID {$existing_page_id}" );
						}
					}
				}
			}
		}
		
		// Create missing pages for unmapped keys
		$missing_pages = array(
			'dashboard' => array( 'title' => 'Health Dashboard', 'shortcode' => '[ennu-user-dashboard]' ),
			'assessments' => array( 'title' => 'Health Assessments', 'shortcode' => '[ennu-assessments]' ),
			'registration' => array( 'title' => 'Health Registration', 'shortcode' => '[ennu-welcome]' ),
			'signup' => array( 'title' => 'Sign Up', 'shortcode' => '[ennu-signup]' ),
			'assessment-results' => array( 'title' => 'Assessment Results', 'shortcode' => '[ennu-assessment-results]' ),
			// Assessment pages - SIMPLE FORMAT
			'hair_assessment_page_id' => array( 'title' => 'Hair Loss Assessment', 'shortcode' => '[ennu-hair]' ),
			'ed-treatment_assessment_page_id' => array( 'title' => 'ED Treatment Assessment', 'shortcode' => '[ennu-ed-treatment]' ),
			'weight-loss_assessment_page_id' => array( 'title' => 'Weight Loss Assessment', 'shortcode' => '[ennu-weight-loss]' ),
			'health_assessment_page_id' => array( 'title' => 'Health Assessment', 'shortcode' => '[ennu-health]' ),
			'health-optimization_assessment_page_id' => array( 'title' => 'Health Optimization Assessment', 'shortcode' => '[ennu-health-optimization]' ),
			'skin_assessment_page_id' => array( 'title' => 'Skin Health Assessment', 'shortcode' => '[ennu-skin]' ),
			'hormone_assessment_page_id' => array( 'title' => 'Hormone Assessment', 'shortcode' => '[ennu-hormone]' ),
			'testosterone_assessment_page_id' => array( 'title' => 'Testosterone Assessment', 'shortcode' => '[ennu-testosterone]' ),
			'menopause_assessment_page_id' => array( 'title' => 'Menopause Assessment', 'shortcode' => '[ennu-menopause]' ),
			'sleep_assessment_page_id' => array( 'title' => 'Sleep Assessment', 'shortcode' => '[ennu-sleep]' ),
			// Results pages - SIMPLE FORMAT
			'hair_results_page_id' => array( 'title' => 'Hair Loss Assessment Results', 'shortcode' => '[ennu-hair-results]' ),
			'ed-treatment_results_page_id' => array( 'title' => 'ED Treatment Assessment Results', 'shortcode' => '[ennu-ed-treatment-results]' ),
			'weight-loss_results_page_id' => array( 'title' => 'Weight Loss Assessment Results', 'shortcode' => '[ennu-weight-loss-results]' ),
			'health_results_page_id' => array( 'title' => 'Health Assessment Results', 'shortcode' => '[ennu-health-results]' ),
			'health-optimization_results_page_id' => array( 'title' => 'Health Optimization Results', 'shortcode' => '[ennu-health-optimization-results]' ),
			'skin_results_page_id' => array( 'title' => 'Skin Health Assessment Results', 'shortcode' => '[ennu-skin-results]' ),
			'hormone_results_page_id' => array( 'title' => 'Hormone Assessment Results', 'shortcode' => '[ennu-hormone-results]' ),
			'testosterone_results_page_id' => array( 'title' => 'Testosterone Assessment Results', 'shortcode' => '[ennu-testosterone-results]' ),
			'menopause_results_page_id' => array( 'title' => 'Menopause Assessment Results', 'shortcode' => '[ennu-menopause-results]' ),
			'sleep_results_page_id' => array( 'title' => 'Sleep Assessment Results', 'shortcode' => '[ennu-sleep-results]' ),
			// Details pages - SIMPLE FORMAT
			'hair_details_page_id' => array( 'title' => 'Hair Loss Treatment Details', 'shortcode' => '[ennu-hair-assessment-details]' ),
			'ed-treatment_details_page_id' => array( 'title' => 'ED Treatment Details', 'shortcode' => '[ennu-ed-treatment-assessment-details]' ),
			'weight-loss_details_page_id' => array( 'title' => 'Weight Loss Treatment Details', 'shortcode' => '[ennu-weight-loss-assessment-details]' ),
			'health_details_page_id' => array( 'title' => 'Health Treatment Details', 'shortcode' => '[ennu-health-assessment-details]' ),
			'health-optimization_details_page_id' => array( 'title' => 'Health Optimization Details', 'shortcode' => '[ennu-health-optimization-assessment-details]' ),
			'skin_details_page_id' => array( 'title' => 'Skin Health Treatment Details', 'shortcode' => '[ennu-skin-assessment-details]' ),
			'hormone_details_page_id' => array( 'title' => 'Hormone Treatment Details', 'shortcode' => '[ennu-hormone-assessment-details]' ),
			'testosterone_details_page_id' => array( 'title' => 'Testosterone Treatment Details', 'shortcode' => '[ennu-testosterone-assessment-details]' ),
			'menopause_details_page_id' => array( 'title' => 'Menopause Treatment Details', 'shortcode' => '[ennu-menopause-assessment-details]' ),
			'sleep_details_page_id' => array( 'title' => 'Sleep Treatment Details', 'shortcode' => '[ennu-sleep-assessment-details]' ),
			// Consultation pages - SIMPLE FORMAT
			'hair_consultation_page_id' => array( 'title' => 'Hair Loss Consultation', 'shortcode' => '[ennu-hair-consultation]' ),
			'ed-treatment_consultation_page_id' => array( 'title' => 'ED Treatment Consultation', 'shortcode' => '[ennu-ed-treatment-consultation]' ),
			'weight-loss_consultation_page_id' => array( 'title' => 'Weight Loss Consultation', 'shortcode' => '[ennu-weight-loss-consultation]' ),
			'health_consultation_page_id' => array( 'title' => 'Health Consultation', 'shortcode' => '[ennu-health-consultation]' ),
			'health-optimization_consultation_page_id' => array( 'title' => 'Health Optimization Consultation', 'shortcode' => '[ennu-health-optimization-consultation]' ),
			'skin_consultation_page_id' => array( 'title' => 'Skin Health Consultation', 'shortcode' => '[ennu-skin-consultation]' ),
			'hormone_consultation_page_id' => array( 'title' => 'Hormone Consultation', 'shortcode' => '[ennu-hormone-consultation]' ),
			'testosterone_consultation_page_id' => array( 'title' => 'Testosterone Consultation', 'shortcode' => '[ennu-testosterone-consultation]' ),
			'menopause_consultation_page_id' => array( 'title' => 'Menopause Consultation', 'shortcode' => '[ennu-menopause-consultation]' ),
			'sleep_consultation_page_id' => array( 'title' => 'Sleep Consultation', 'shortcode' => '[ennu-sleep-consultation]' ),
		);
		
		$created_pages = array();
		foreach ( $missing_pages as $path => $page_info ) {
			if ( empty( $page_mappings[ $path ] ) ) {
				$page_id = $this->create_page( $path, $page_info['title'], $page_info['shortcode'] );
				if ( $page_id ) {
					$page_mappings[ $path ] = $page_id;
					$created_pages[ $path ] = $page_id;
					$updated = true;
					error_log( "ENNU Auto-Detect: Created missing page for '{$path}' with ID {$page_id}" );
				}
			}
		}
		
		if ( $updated ) {
			$settings['page_mappings'] = $page_mappings;
			update_option( 'ennu_life_settings', $settings );
			update_option( 'ennu_created_pages', $page_mappings );
			
			// CRITICAL FIX: Store created pages for JavaScript to auto-select
			if ( ! empty( $created_pages ) ) {
				update_option( 'ennu_auto_select_pages', $created_pages );
				error_log( "ENNU Auto-Detect: Stored auto-select pages: " . json_encode( $created_pages ) );
			}
			
			// CRITICAL FIX: Force refresh of page options cache
			wp_cache_delete( 'ennu_page_options', 'ennu_life' );
		}
		
		// Log summary
		$mapped_count = count( array_filter( $page_mappings ) );
		$total_count = count( $missing_pages );
		$created_count = count( $created_pages );
		error_log( "ENNU Auto-Detect: Complete - {$mapped_count}/{$total_count} pages mapped, {$created_count} pages created" );
		
		return array(
			'updated' => $updated,
			'mapped_count' => $mapped_count,
			'total_count' => $total_count,
			'created_pages' => $created_pages
		);
	}

	/**
	 * Get page title from path
	 */
	private function get_page_title_from_path( $path ) {
		$titles = array(
			'dashboard' => 'Health Dashboard',
			'assessments' => 'Health Assessments',
			'registration' => 'Health Registration',
			'signup' => 'Sign Up',
			'assessment-results' => 'Assessment Results',
			'call' => 'Schedule a Call',
			'ennu-life-score' => 'ENNU Life Score',
			'assessments/hair' => 'Hair Loss Assessment',
			'assessments/ed-treatment' => 'ED Treatment Assessment',
			'assessments/weight-loss' => 'Weight Loss Assessment',
			'assessments/health' => 'Health Assessment',
			'assessments/health-optimization' => 'Health Optimization Assessment',
			'assessments/skin' => 'Skin Health Assessment',
			'assessments/hormone' => 'Hormone Assessment',
			'assessments/testosterone' => 'Testosterone Assessment',
			'assessments/menopause' => 'Menopause Assessment',
			'assessments/sleep' => 'Sleep Assessment',
		);
		
		return isset( $titles[ $path ] ) ? $titles[ $path ] : ucfirst( str_replace( '-', ' ', $path ) );
	}

	/**
	 * Create a new page
	 */
	private function create_page( $path, $title, $content = '' ) {
		$page_data = array(
			'post_title'    => $title,
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_name'     => $path,
			'post_author'   => 1,
		);
		
		$page_id = wp_insert_post( $page_data );
		
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			// Set the page slug to match the path
			wp_update_post( array(
				'ID'        => $page_id,
				'post_name' => $path,
			) );
			return $page_id;
		}
		
		return false;
	}

	/**
	 * Save settings
	 */
	private function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->get_plugin_settings();
		
		// Save page mappings
		if ( isset( $_POST['page_mappings'] ) && is_array( $_POST['page_mappings'] ) ) {
			$settings['page_mappings'] = array_map( 'sanitize_text_field', $_POST['page_mappings'] );
		}

		update_option( 'ennu_life_settings', $settings );
		
		// Also sync to ennu_created_pages for backward compatibility
		if ( ! empty( $settings['page_mappings'] ) ) {
			update_option( 'ennu_created_pages', $settings['page_mappings'] );
		}
	}

	/**
	 * Render enhanced page dropdown
	 */
	private function render_enhanced_page_dropdown( $key, $label, $current_mappings, $page_options, $expected_url ) {
		$current_value = isset( $current_mappings[ $key ] ) ? $current_mappings[ $key ] : '';
		$fresh_page_options = $this->get_fresh_page_options(); // CRITICAL FIX: Get fresh page options
		echo '<div class="ennu-page-item">';
		echo '<label for="page_mapping_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		echo '<select name="page_mappings[' . esc_attr( $key ) . ']" id="page_mapping_' . esc_attr( $key ) . '" class="ennu-page-dropdown">';
		echo '<option value="">' . esc_html__( '-- Select a page --', 'ennulifeassessments' ) . '</option>';
		foreach ( $fresh_page_options as $page_id => $page_title ) { // Use fresh options
			$selected = selected( $current_value, $page_id, false );
			echo '<option value="' . esc_attr( $page_id ) . '"' . $selected . '>' . esc_html( $page_title ) . '</option>';
		}
		echo '</select>';
		echo '<div class="ennu-page-status">';
		if ( $current_value ) {
			$page = get_post( $current_value );
			if ( $page ) {
				echo '<span class="ennu-page-info">' . esc_html__( 'Current: ', 'ennulifeassessments' ) . esc_html( $page->post_title ) . '</span>';
			}
		} else {
			echo '<span class="ennu-page-info">' . esc_html__( 'No page selected', 'ennulifeassessments' ) . '</span>';
		}
		echo '</div>';
		echo '</div>';
	}
	
	/**
	 * Get fresh page options including newly created pages
	 */
	private function get_fresh_page_options() {
		$pages = get_pages( array(
			'post_status' => 'publish',
			'numberposts' => -1,
			'sort_column' => 'post_title',
			'sort_order' => 'ASC'
		) );
		
		$page_options = array();
		foreach ( $pages as $page ) {
			$page_options[ $page->ID ] = $page->post_title;
		}
		
		return $page_options;
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_admin_assets( $hook ) {
		wp_enqueue_style( 'ennu-admin-styles', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-scores-enhanced.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_style( 'ennu-admin-tabs', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-tabs-enhanced.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_style( 'ennu-admin-user-profile', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-user-profile.css', array(), ENNU_LIFE_VERSION );
		
		wp_enqueue_script( 'ennu-admin-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin-enhanced.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );

		// Localize script with AJAX data
		wp_localize_script(
			'ennu-admin-enhanced',
			'ennu_admin_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ennu_admin_nonce' ),
				'strings'  => array(
					'confirm_clear_data'  => __( 'Are you sure you want to clear all assessment data for this user? This action cannot be undone.', 'ennulifeassessments' ),
					'confirm_recalculate' => __( 'Are you sure you want to recalculate all scores? This may take a moment.', 'ennulifeassessments' ),
					'updating'            => __( 'Updating...', 'ennulifeassessments' ),
					'success'             => __( 'Success!', 'ennulifeassessments' ),
					'error'               => __( 'Error occurred. Please try again.', 'ennulifeassessments' ),
				),
			)
		);
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'ENNU Life', 'ennulifeassessments' ),
			__( 'ENNU Life', 'ennulifeassessments' ),
			'edit_posts',
			'ennu-life',
			array( $this, 'render_admin_dashboard_page' ),
			'dashicons-heart',
			30
		);

		add_submenu_page(
			'ennu-life',
			__( 'Dashboard', 'ennulifeassessments' ),
			__( 'Dashboard', 'ennulifeassessments' ),
			'edit_posts',
			'ennu-life',
			array( $this, 'render_admin_dashboard_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'Settings', 'ennulifeassessments' ),
			__( 'Settings', 'ennulifeassessments' ),
			'edit_posts',
			'ennu-life-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Render admin dashboard page
	 */
	public function render_admin_dashboard_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'ENNU Life Dashboard', 'ennulifeassessments' ) . '</h1>';
		echo '<p>' . esc_html__( 'Welcome to ENNU Life Assessment System', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Render settings page
	 */
	public function settings_page() {
		$auto_select_pages = array();
		
		// Handle form submissions - CONSOLIDATED INTO ONE BUTTON
		if ( isset( $_POST['ennu_detect_pages_submit'] ) && wp_verify_nonce( $_POST['ennu_detect_pages_nonce'], 'ennu_detect_pages' ) ) {
			$result = $this->auto_detect_existing_pages();
			if ( $result['updated'] ) {
				echo '<div class="notice notice-success"><p>✅ Pages detected, created, and mapped successfully!</p></div>';
				$auto_select_pages = $result['created_pages'];
			} else {
				echo '<div class="notice notice-info"><p>ℹ️ No changes needed - all pages are already mapped.</p></div>';
			}
		}
		
		// Auto-sync existing page mappings on page load
		$this->sync_existing_page_mappings();
		
		// Debug information (remove this in production)
		if ( isset( $_GET['debug'] ) ) {
			$this->debug_page_mappings();
		}
		
		// Enhanced admin page with modern design and organization
		echo '<div class="wrap ennu-admin-wrapper">';

		// Page Header
		echo '<div class="ennu-admin-header">';
		echo '<h1><span class="ennu-logo">🎯</span> ENNU Life Settings</h1>';
		echo '<p class="ennu-subtitle">Manage your health assessment system configuration</p>';
		echo '</div>';

		// CRITICAL FIX: Enhanced auto-selection JavaScript with forced selection
		if ( ! empty( $auto_select_pages ) ) {
			echo '<script>
			jQuery(document).ready(function($) {
				console.log("ENNU Auto-Select: Starting auto-selection for pages:", ' . json_encode( $auto_select_pages ) . ');
				
				// Auto-select newly created pages
				var autoSelectPages = ' . json_encode( $auto_select_pages ) . ';
				
				// Function to auto-select pages
				function autoSelectPages() {
					var selectedCount = 0;
					
					$.each(autoSelectPages, function(key, pageId) {
						var dropdown = $("#page_mapping_" + key);
						if (dropdown.length) {
							// Force select the page
							dropdown.val(pageId);
							dropdown.trigger("change");
							selectedCount++;
							console.log("ENNU Auto-Select: Selected page " + pageId + " for key " + key);
						}
					});
					
					console.log("ENNU Auto-Select: Successfully selected " + selectedCount + " pages");
					return selectedCount;
				}
				
				// Try auto-selection immediately
				var immediateCount = autoSelectPages();
				
				// Also try after a short delay to ensure dropdowns are loaded
				setTimeout(function() {
					var delayedCount = autoSelectPages();
					console.log("ENNU Auto-Select: Delayed selection completed - " + delayedCount + " pages selected");
					
					// Show success message
					if (delayedCount > 0) {
						alert("ENNU Auto-Select: Successfully selected " + delayedCount + " pages!");
					}
				}, 1000);
			});
			</script>';
		}

		// Add comprehensive CSS for modern admin design
		echo '<style>
			.ennu-admin-wrapper { max-width: 1200px; margin: 0 auto; }
			.ennu-admin-header { 
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: white;
				padding: 2rem;
				border-radius: 10px;
				margin-bottom: 2rem;
				text-align: center;
				position: relative;
				overflow: hidden;
			}
			.ennu-admin-header h1 { 
				margin: 0 0 0.5rem 0; 
				font-size: 2.5rem;
				font-weight: 700;
			}
			.ennu-subtitle { 
				margin: 0; 
				opacity: 0.9; 
				font-size: 1.1rem;
			}
			.ennu-logo { font-size: 2rem; margin-right: 0.5rem; }
			
			.ennu-section { 
				margin-bottom: 2rem; 
				padding: 1.5rem; 
				background: #f8f9fa; 
				border-radius: 8px; 
				border-left: 4px solid #667eea;
			}
			.ennu-section h3 { 
				margin: 0 0 1rem 0; 
				color: #2d3748; 
				font-size: 1.3rem;
				display: flex;
				align-items: center;
			}
			.ennu-section h3::before { 
				content: "📋"; 
				margin-right: 0.5rem; 
				font-size: 1.2rem;
			}
			.ennu-section p { 
				margin: 0 0 1rem 0; 
				color: #718096; 
				font-style: italic;
			}
			
			.ennu-page-grid { 
				display: grid; 
				grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
				gap: 1rem; 
				margin-top: 1rem;
			}
			.ennu-page-item { 
				background: white; 
				padding: 1.5rem; 
				border-radius: 8px; 
				border: 1px solid #e2e8f0;
				transition: all 0.3s ease;
			}
			.ennu-page-item:hover { 
				box-shadow: 0 8px 25px rgba(0,0,0,0.15); 
				transform: translateY(-3px);
			}
			.ennu-page-item label { 
				font-weight: 600; 
				color: #2d3748; 
				display: block; 
				margin-bottom: 0.75rem;
				font-size: 1rem;
			}
			.ennu-page-item select { 
				width: 100%; 
				padding: 0.75rem; 
				border: 2px solid #e2e8f0; 
				border-radius: 6px;
				font-size: 0.9rem;
				transition: all 0.3s ease;
			}
			.ennu-page-item select:focus {
				outline: none;
				border-color: #667eea;
				box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
			}
			
			.ennu-page-status {
				margin-top: 1rem;
			}
			.ennu-page-status .page-info {
				border-radius: 6px;
				transition: all 0.3s ease;
			}
			.ennu-page-status .page-info:hover {
				transform: translateX(5px);
			}
			.ennu-page-status a {
				transition: all 0.3s ease;
			}
			.ennu-page-status a:hover {
				color: #764ba2 !important;
				text-decoration: underline !important;
			}
			
			/* Enhanced button styles */
			.button-primary {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
				border: none !important;
				color: white !important;
				font-weight: 600 !important;
				padding: 0.75rem 1.5rem !important;
				border-radius: 6px !important;
				transition: all 0.3s ease !important;
			}
			.button-primary:hover {
				transform: translateY(-2px) !important;
				box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
			}
			
			@media (max-width: 768px) {
				.ennu-page-grid { grid-template-columns: 1fr; }
				.ennu-admin-header h1 { font-size: 2rem; }
				.ennu-page-item { padding: 1rem; }
			}
		</style>';

		// Handle form submissions
		$message = '';
		if ( isset( $_POST['submit'] ) && isset( $_POST['ennu_settings_nonce'] ) && wp_verify_nonce( $_POST['ennu_settings_nonce'], 'ennu_settings_update' ) ) {
			$this->save_settings();
			$message = __( 'Settings saved successfully!', 'ennulifeassessments' );
		}
		if ( isset( $_POST['ennu_delete_pages_submit'] ) && isset( $_POST['ennu_delete_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_delete_pages_nonce'], 'ennu_delete_pages' ) ) {
			$this->delete_pages();
			$message = __( 'Assessment pages have been deleted successfully!', 'ennulifeassessments' );
		}
		if ( isset( $_POST['ennu_detect_pages_submit'] ) && isset( $_POST['ennu_detect_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_detect_pages_nonce'], 'ennu_detect_pages' ) ) {
			$this->auto_detect_existing_pages();
			$message = __( 'Pages detected, created, and mapped successfully!', 'ennulifeassessments' );
		}

		if ( $message ) {
			echo '<div class="notice notice-success is-dismissible"><p><strong>✅ ' . $message . '</strong></p></div>';
		}

		$settings        = $this->get_plugin_settings();
		$pages           = get_pages();
		$page_options    = array();
		foreach ( $pages as $page ) {
			$page_options[ $page->ID ] = $page->post_title;
		}

		// Page Status Overview
		echo '<div class="ennu-section" style="background: #f8f9fa; border-left-color: #17a2b8;">';
		echo '<h3 style="color: #17a2b8;">📊 Page Mapping Status</h3>';
		echo '<p style="color: #6c757d;">Overview of which pages are properly mapped and which need attention</p>';
		
		$mapped_count = 0;
		$total_count = 0;
		$expected_pages = array(
			'dashboard', 'assessments', 'registration', 'signup', 'assessment-results',
			'call', 'ennu-life-score', 'health-optimization-results',
			// Assessment pages - SIMPLE FORMAT
			'hair_assessment_page_id', 'ed-treatment_assessment_page_id', 'weight-loss_assessment_page_id',
			'health_assessment_page_id', 'health-optimization_assessment_page_id', 'skin_assessment_page_id',
			'hormone_assessment_page_id', 'testosterone_assessment_page_id', 'menopause_assessment_page_id', 'sleep_assessment_page_id',
			// Results pages - SIMPLE FORMAT
			'hair_results_page_id', 'ed-treatment_results_page_id', 'weight-loss_results_page_id',
			'health_results_page_id', 'health-optimization_results_page_id', 'skin_results_page_id',
			'hormone_results_page_id', 'testosterone_results_page_id', 'menopause_results_page_id', 'sleep_results_page_id',
			// Details pages - SIMPLE FORMAT
			'hair_details_page_id', 'ed-treatment_details_page_id', 'weight-loss_details_page_id',
			'health_details_page_id', 'health-optimization_details_page_id', 'skin_details_page_id',
			'hormone_details_page_id', 'testosterone_details_page_id', 'menopause_details_page_id', 'sleep_details_page_id',
			// Consultation pages - SIMPLE FORMAT
			'hair_consultation_page_id', 'ed-treatment_consultation_page_id', 'weight-loss_consultation_page_id',
			'health_consultation_page_id', 'health-optimization_consultation_page_id', 'skin_consultation_page_id',
			'hormone_consultation_page_id', 'testosterone_consultation_page_id', 'menopause_consultation_page_id', 'sleep_consultation_page_id'
		);
		
		foreach ( $expected_pages as $page_key ) {
			$total_count++;
			if ( ! empty( $settings['page_mappings'][ $page_key ] ) ) {
				$mapped_count++;
			}
		}
		
		$percentage = $total_count > 0 ? round( ( $mapped_count / $total_count ) * 100, 1 ) : 0;
		$status_color = $percentage >= 80 ? '#28a745' : ( $percentage >= 50 ? '#ffc107' : '#dc3545' );
		
		echo '<div style="background: white; padding: 1rem; border-radius: 8px; margin-top: 1rem;">';
		echo '<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">';
		echo '<div>';
		echo '<strong style="color: ' . $status_color . ';">' . $mapped_count . ' of ' . $total_count . ' pages mapped</strong>';
		echo '<br><small style="color: #6c757d;">' . $percentage . '% complete</small>';
		echo '</div>';
		echo '<div style="text-align: right;">';
		echo '<div style="width: 100px; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">';
		echo '<div style="width: ' . $percentage . '%; height: 100%; background: ' . $status_color . '; transition: width 0.3s ease;"></div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		if ( $percentage < 100 ) {
			echo '<div style="color: #dc3545; font-size: 0.9rem;">';
			echo '<strong>⚠️ Action Required:</strong> Some pages are not properly mapped. Use the "Auto-Detect Existing Pages" button below to automatically find and map existing pages.';
			echo '</div>';
		} else {
			echo '<div style="color: #28a745; font-size: 0.9rem;">';
			echo '<strong>✅ All Good:</strong> All expected pages are properly mapped!';
			echo '</div>';
		}
		echo '</div></div>';

		// Debug Information (only for administrators)
		if ( current_user_can( 'manage_options' ) ) {
			echo '<div class="ennu-section" style="background: #fff3cd; border-left-color: #ffc107;">';
			echo '<h3 style="color: #856404;">🔧 Debug Information</h3>';
			echo '<p style="color: #856404;">Technical details for troubleshooting</p>';
			
			echo '<div style="background: white; padding: 1rem; border-radius: 8px; margin-top: 1rem; font-family: monospace; font-size: 0.9rem;">';
			echo '<strong>Current Settings:</strong><br>';
			echo '<pre>' . esc_html( print_r( $settings, true ) ) . '</pre>';
			
			echo '<strong>Created Pages Option:</strong><br>';
			$created_pages = get_option( 'ennu_created_pages', array() );
			echo '<pre>' . esc_html( print_r( $created_pages, true ) ) . '</pre>';
			echo '</div>';
			echo '</div>';
		}

		// Page Management Form
		echo '<form method="post" action="">';
		wp_nonce_field( 'ennu_settings_update', 'ennu_settings_nonce' );

		// Core Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Core Pages</h3>';
		echo '<p>Essential pages for user registration, dashboard, and assessments</p>';
		echo '<div class="ennu-page-grid">';
		$this->render_enhanced_page_dropdown( 'dashboard', 'Dashboard Page', $settings['page_mappings'], $page_options, '/dashboard/' );
		$this->render_enhanced_page_dropdown( 'assessments', 'Assessments Landing', $settings['page_mappings'], $page_options, '/assessments/' );
		$this->render_enhanced_page_dropdown( 'registration', 'Registration Page', $settings['page_mappings'], $page_options, '/registration/' );
		$this->render_enhanced_page_dropdown( 'signup', 'Sign Up Page', $settings['page_mappings'], $page_options, '/signup/' );
		$this->render_enhanced_page_dropdown( 'assessment-results', 'Generic Results', $settings['page_mappings'], $page_options, '/assessment-results/' );
		$this->render_enhanced_page_dropdown( 'welcome-assessment-details', 'Welcome Assessment Results', $settings['page_mappings'], $page_options, '/welcome-assessment-details/' );
		echo '</div></div>';

		// Consultation Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Consultation & Call Pages</h3>';
		echo '<p>Pages for scheduling calls and getting ENNU Life scores</p>';
		echo '<div class="ennu-page-grid">';
		$this->render_enhanced_page_dropdown( 'call', 'Schedule a Call', $settings['page_mappings'], $page_options, '/call/' );
		$this->render_enhanced_page_dropdown( 'ennu-life-score', 'ENNU Life Score', $settings['page_mappings'], $page_options, '/ennu-life-score/' );
		$this->render_enhanced_page_dropdown( 'health-optimization-results', 'Health Optimization Results', $settings['page_mappings'], $page_options, '/health-optimization-results/' );
		echo '</div></div>';

		// Assessment Form Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Form Pages</h3>';
		echo '<p>Individual assessment forms with URLs like /assessments/hair/, /assessments/ed-treatment/, etc.</p>';
		echo '<div class="ennu-page-grid">';

		$assessment_keys = array(
			'hair' => 'Hair Assessment',
			'ed-treatment' => 'ED Treatment Assessment',
			'weight-loss' => 'Weight Loss Assessment',
			'health' => 'Health Assessment',
			'health-optimization' => 'Health Optimization Assessment',
			'skin' => 'Skin Assessment',
			'hormone' => 'Hormone Assessment',
			'testosterone' => 'Testosterone Assessment',
			'menopause' => 'Menopause Assessment',
			'sleep' => 'Sleep Assessment',
		);

		// Assessment Form Pages - SIMPLE PAGE ID APPROACH
		foreach ( $assessment_keys as $slug => $label ) {
			$simple_key = $slug . '_assessment_page_id';
			$this->render_enhanced_page_dropdown( $simple_key, $label, $settings['page_mappings'], $page_options, $simple_key );
		}
		echo '</div></div>';

		// Assessment Results Pages Section - SIMPLE PAGE ID APPROACH
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Results Pages</h3>';
		echo '<p>Select which page to redirect to after each assessment completion</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $label ) {
			$simple_key = $slug . '_results_page_id';
			$results_label = str_replace( ' Assessment', ' Assessment Results', $label );
			$this->render_enhanced_page_dropdown( $simple_key, $results_label, $settings['page_mappings'], $page_options, $simple_key );
		}
		echo '</div></div>';

		// Assessment Details Pages Section - SIMPLE PAGE ID APPROACH
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Details Pages</h3>';
		echo '<p>Select which page to use for treatment options and detailed information</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $label ) {
			$simple_key = $slug . '_details_page_id';
			$details_label = str_replace( ' Assessment', ' Assessment Details', $label );
			$this->render_enhanced_page_dropdown( $simple_key, $details_label, $settings['page_mappings'], $page_options, $simple_key );
		}
		echo '</div></div>';

		// Assessment Consultation Pages Section - SIMPLE PAGE ID APPROACH
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Consultation Pages</h3>';
		echo '<p>Select which page to use for consultation booking</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $label ) {
			$simple_key = $slug . '_consultation_page_id';
			$consultation_label = str_replace( ' Assessment', ' Consultation', $label );
			$this->render_enhanced_page_dropdown( $simple_key, $consultation_label, $settings['page_mappings'], $page_options, $simple_key );
		}
		echo '</div></div>';

		echo '<p><input type="submit" name="submit" class="button button-primary" value="' . esc_attr__( 'Save Page Settings', 'ennulifeassessments' ) . '"></p>';
		echo '</form>';

		// Quick Actions Section
		echo '<div class="ennu-section" style="margin-top: 2rem; background: #f0f8ff; border-left-color: #4CAF50;">';
		echo '<h3 style="color: #2E7D32;">🚀 Quick Actions</h3>';
		echo '<p style="color: #388E3C;">Automated tools to manage your assessment system</p>';
		
		echo '<form method="post" action="" style="margin-bottom: 1rem;">';
		wp_nonce_field( 'ennu_detect_pages', 'ennu_detect_pages_nonce' );
		echo '<button type="submit" name="ennu_detect_pages_submit" class="button button-primary" style="font-size: 1.1rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%) !important; color: white !important;">🔍 Auto-Detect & Create Pages</button>';
		echo '<p style="margin-top: 0.5rem; color: #1976D2; font-weight: 500;">Scans for existing pages, creates missing ones, and automatically maps them to the correct fields.</p>';
		echo '</form>';

		// Danger Zone Section
		echo '<div style="margin-top: 2rem; background: #fff5f5; border-left: 4px solid #f56565; padding: 1.5rem; border-radius: 8px;">';
		echo '<h3 style="color: #c53030;">⚠️ Danger Zone</h3>';
		echo '<p style="color: #742a2a;">These actions cannot be undone. Use with extreme caution.</p>';

		if ( ! empty( $settings['page_mappings'] ) ) {
			echo '<form method="post" action="" onsubmit="return confirm(\'⚠️ WARNING: This will permanently delete all mapped assessment pages. This action cannot be undone. Are you absolutely sure?\');">';
			wp_nonce_field( 'ennu_delete_pages', 'ennu_delete_pages_nonce' );
			echo '<button type="submit" name="ennu_delete_pages_submit" class="button" style="background: #f56565; border-color: #f56565; color: white; font-weight: bold;">🗑️ Delete All Mapped Assessment Pages</button>';
			echo '<p style="margin-top: 0.5rem; color: #742a2a;">This will permanently delete all pages currently mapped in the settings above.</p>';
			echo '</form>';
		} else {
			echo '<p style="color: #742a2a;">No pages are currently mapped, so deletion is not available.</p>';
		}
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Add ENNU data section to user profile
	 */
	public function add_ennu_profile_section( $user ) {
		?>
		<h2><?php esc_html_e( 'ENNU Life Assessment Data', 'ennulifeassessments' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Assessment Data', 'ennulifeassessments' ); ?></th>
				<td>
					<?php
					$assessments = get_user_meta( $user->ID, 'ennu_assessments', true );
					if ( ! empty( $assessments ) && is_array( $assessments ) ) {
						echo '<ul>';
						foreach ( $assessments as $assessment_type => $data ) {
							echo '<li><strong>' . esc_html( ucfirst( str_replace( '-', ' ', $assessment_type ) ) ) . '</strong>';
							echo '<br><small>Submitted: ' . esc_html( $data['submitted_at'] ?? 'Unknown' ) . '</small>';
							echo '</li>';
						}
						echo '</ul>';
					} else {
						echo '<p>' . esc_html__( 'No assessment data found.', 'ennulifeassessments' ) . '</p>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Global Data', 'ennulifeassessments' ); ?></th>
				<td>
					<?php
					$global_fields = array(
						'ennu_global_first_name' => 'First Name',
						'ennu_global_last_name' => 'Last Name',
						'ennu_global_email' => 'Email',
						'ennu_global_gender' => 'Gender',
						'ennu_global_date_of_birth' => 'Date of Birth',
						'ennu_global_exact_age' => 'Age',
						'ennu_global_height_weight' => 'Height & Weight'
					);
					
					$has_global_data = false;
					foreach ( $global_fields as $meta_key => $label ) {
						$value = get_user_meta( $user->ID, $meta_key, true );
						if ( ! empty( $value ) ) {
							if ( ! $has_global_data ) {
								echo '<ul>';
								$has_global_data = true;
							}
							echo '<li><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $value ) . '</li>';
						}
					}
					
					if ( $has_global_data ) {
						echo '</ul>';
					} else {
						echo '<p>' . esc_html__( 'No global data found.', 'ennulifeassessments' ) . '</p>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Weight Loss Assessment', 'ennulifeassessments' ); ?></th>
				<td>
					<?php
					$weight_loss_fields = array(
						'ennu_weight_loss_first_name' => 'First Name',
						'ennu_weight_loss_last_name' => 'Last Name',
						'ennu_weight_loss_email' => 'Email',
						'ennu_weight_loss_overall_score' => 'Overall Score',
						'ennu_weight_loss_category_motivation_goals_score' => 'Motivation & Goals Score',
						'ennu_weight_loss_category_lifestyle_habits_score' => 'Lifestyle & Habits Score',
						'ennu_weight_loss_category_medical_factors_score' => 'Medical Factors Score',
						'ennu_weight_loss_category_aesthetics_score' => 'Aesthetics Score'
					);
					
					$has_weight_loss_data = false;
					foreach ( $weight_loss_fields as $meta_key => $label ) {
						$value = get_user_meta( $user->ID, $meta_key, true );
						if ( ! empty( $value ) ) {
							if ( ! $has_weight_loss_data ) {
								echo '<ul>';
								$has_weight_loss_data = true;
							}
							echo '<li><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $value ) . '</li>';
						}
					}
					
					if ( $has_weight_loss_data ) {
						echo '</ul>';
					} else {
						echo '<p>' . esc_html__( 'No weight loss assessment data found.', 'ennulifeassessments' ) . '</p>';
					}
					?>
				</td>
			</tr>
		</table>
		
		<!-- Delete All User Data Section -->
		<h3><?php esc_html_e( 'Danger Zone', 'ennulifeassessments' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Delete All ENNU Data', 'ennulifeassessments' ); ?></th>
				<td>
					<p class="description">
						<?php esc_html_e( 'This will permanently delete ALL ENNU data for this user including:', 'ennulifeassessments' ); ?>
					</p>
					<ul style="margin-left: 20px; margin-bottom: 15px;">
						<li><?php esc_html_e( 'All assessment data and scores', 'ennulifeassessments' ); ?></li>
						<li><?php esc_html_e( 'Global user data (except name/email)', 'ennulifeassessments' ); ?></li>
						<li><?php esc_html_e( 'Assessment-specific meta fields', 'ennulifeassessments' ); ?></li>
						<li><?php esc_html_e( 'Transient data and cached results', 'ennulifeassessments' ); ?></li>
						<li><?php esc_html_e( 'Symptom data and health goals', 'ennulifeassessments' ); ?></li>
					</ul>
					
					<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
						<p style="margin: 0; color: #856404;">
							<strong><?php esc_html_e( '⚠️ Warning:', 'ennulifeassessments' ); ?></strong> 
							<?php esc_html_e( 'This action cannot be undone. The user will need to retake all assessments.', 'ennulifeassessments' ); ?>
						</p>
					</div>
					
					<button type="button" id="delete-ennu-data-btn" class="button button-secondary" 
							style="background: #dc3545; border-color: #dc3545; color: white;"
							data-user-id="<?php echo esc_attr( $user->ID ); ?>"
							data-nonce="<?php echo wp_create_nonce( 'delete_ennu_data_' . $user->ID ); ?>">
						<?php esc_html_e( '🗑️ Delete All ENNU Data', 'ennulifeassessments' ); ?>
					</button>
					
					<div id="delete-ennu-data-status" style="margin-top: 10px; display: none;"></div>
				</td>
			</tr>
		</table>
		
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#delete-ennu-data-btn').on('click', function() {
				if (!confirm('<?php echo esc_js( __( 'Are you sure you want to delete ALL ENNU data for this user? This action cannot be undone.', 'ennulifeassessments' ) ); ?>')) {
					return;
				}
				
				var button = $(this);
				var statusDiv = $('#delete-ennu-data-status');
				
				button.prop('disabled', true).text('<?php echo esc_js( __( 'Deleting...', 'ennulifeassessments' ) ); ?>');
				statusDiv.html('<div style="color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px;"><?php echo esc_js( __( 'Deleting user data...', 'ennulifeassessments' ) ); ?></div>').show();
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'delete_ennu_user_data',
						user_id: button.data('user-id'),
						nonce: button.data('nonce')
					},
					success: function(response) {
						if (response.success) {
							statusDiv.html('<div style="color: #155724; background: #d4edda; padding: 10px; border-radius: 4px;"><?php echo esc_js( __( '✅ All ENNU data deleted successfully!', 'ennulifeassessments' ) ); ?></div>');
							button.text('<?php echo esc_js( __( '🗑️ Data Deleted', 'ennulifeassessments' ) ); ?>').prop('disabled', true);
							// Reload page after 2 seconds to show updated data
							setTimeout(function() {
								location.reload();
							}, 2000);
						} else {
							statusDiv.html('<div style="color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px;"><?php echo esc_js( __( '❌ Error: ', 'ennulifeassessments' ) ); ?>' + (response.data ? response.data.message : '<?php echo esc_js( __( 'Unknown error occurred', 'ennulifeassessments' ) ); ?>') + '</div>');
							button.prop('disabled', false).text('<?php echo esc_js( __( '🗑️ Delete All ENNU Data', 'ennulifeassessments' ) ); ?>');
						}
					},
					error: function() {
						statusDiv.html('<div style="color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px;"><?php echo esc_js( __( '❌ Network error occurred', 'ennulifeassessments' ) ); ?></div>');
						button.prop('disabled', false).text('<?php echo esc_js( __( '🗑️ Delete All ENNU Data', 'ennulifeassessments' ) ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Show user assessment fields in user profile
	 *
	 * @param WP_User $user User object
	 */
	public function show_user_assessment_fields( $user ) {
		if ( ! $user || ! is_object( $user ) || ! isset( $user->ID ) || ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		$user_id = intval( $user->ID );
		
		echo '<h2>' . esc_html__( 'ENNU Life Assessment Data', 'ennulifeassessments' ) . '</h2>';
		
		// Add Clear All Data button
		echo '<div class="ennu-admin-actions" style="margin: 15px 0;">';
		echo '<button type="button" id="ennu-clear-all-user-data" class="button button-secondary" style="background: #dc3545; color: white; border-color: #dc3545;">';
		echo esc_html__( '🗑️ Clear All ENNU Data for This User', 'ennulifeassessments' );
		echo '</button>';
		echo '<span id="ennu-clear-data-status" style="margin-left: 10px;"></span>';
		echo '</div>';
		
		echo '<div class="ennu-admin-assessment-data">';
		
		// Get all user meta to find completed assessments
		$all_meta = get_user_meta( $user_id );
		$completed_assessments = array();
		
		// Get ALL available assessment types from config files
		$assessment_dir = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/';
		$assessment_files = glob( $assessment_dir . '*.php' );
		$available_assessments = array();
		
		foreach ( $assessment_files as $file ) {
			$assessment_type = basename( $file, '.php' );
			// Skip welcome assessment as it's not a real assessment
			if ( $assessment_type !== 'welcome' ) {
				$available_assessments[ $assessment_type ] = array(
					'type' => $assessment_type,
					'title' => ucwords( str_replace( '-', ' ', $assessment_type ) ) . ' Assessment',
					'status' => 'Available'
				);
			}
		}
		
		// Check which assessments have user data and update status
		foreach ( $available_assessments as $assessment_type => &$assessment_info ) {
			$calculated_score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
			if ( ! empty( $calculated_score ) && is_numeric( $calculated_score ) ) {
				$assessment_info['status'] = "Completed (Score: $calculated_score)";
			} else {
				// Check for partial data - look for ANY question field with content
				$question_count = 0;
				foreach ( $all_meta as $meta_key => $meta_value ) {
					// More comprehensive question detection
					if ( ( strpos( $meta_key, 'ennu_' . $assessment_type . '_q' ) === 0 || 
					      strpos( $meta_key, 'ennu_' . str_replace('-', '_', $assessment_type) . '_' ) === 0 ||
					      strpos( $meta_key, 'ennu_' . str_replace('_', '-', $assessment_type) . '_' ) === 0 ) &&
					     ! empty( $meta_value[0] ) ) {
						// Skip metadata fields
						if ( ! in_array( substr( $meta_key, strrpos( $meta_key, '_' ) + 1 ), array( 'assessment_type', 'first_name', 'last_name', 'email', 'billing_phone', 'auto_submit_ready', 'calculated_score', 'score_breakdown' ) ) ) {
							$question_count++;
						}
					}
				}
				if ( $question_count > 0 ) {
					$assessment_info['status'] = "Partial ($question_count answers)";
				}
			}
		}
		
		$completed_assessments = $available_assessments;
		
		// Display comprehensive user health overview
		echo '<div class="ennu-comprehensive-overview" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">';
		
		// Life Score and Pillar Scores Section
		echo '<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 20px;">';
		
		// Life Score
		echo '<div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">';
		echo '<h4 style="margin: 0 0 10px 0;">🎯 Life Score</h4>';
		$life_score = get_user_meta( $user_id, 'ennu_life_score', true );
		if ( ! empty( $life_score ) && is_numeric( $life_score ) ) {
			echo '<div style="font-size: 32px; font-weight: bold; color: #28a745;">' . number_format( $life_score, 1 ) . '</div>';
		} else {
			echo '<div style="color: #666; font-style: italic;">Not calculated</div>';
		}
		echo '</div>';
		
		// Pillar Scores
		echo '<div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #6f42c1;">';
		echo '<h4 style="margin: 0 0 10px 0;">🏛️ Overall Pillar Scores</h4>';
		$pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
		if ( ! empty( $pillar_scores ) && is_array( $pillar_scores ) ) {
			echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;">';
			foreach ( $pillar_scores as $pillar => $score ) {
				echo '<div style="text-align: center; padding: 8px; background: #f8f9fa; border-radius: 3px;">';
				echo '<div style="font-weight: bold; color: #6f42c1;">' . number_format( $score, 1 ) . '</div>';
				echo '<div style="font-size: 12px; color: #666;">' . esc_html( ucwords( str_replace( '_', ' ', $pillar ) ) ) . '</div>';
				echo '</div>';
			}
			echo '</div>';
		} else {
			echo '<div style="color: #666; font-style: italic;">No pillar scores calculated</div>';
		}
		echo '</div>';
		
		echo '</div>'; // Close grid
		
		// Health Goals, Symptoms, and Biomarkers Section
		echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">';
		
		// Health Goals
		echo '<div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #fd7e14;">';
		echo '<h4 style="margin: 0 0 10px 0;">🎯 Health Goals</h4>';
		$this->display_health_goals_summary( $user_id );
		echo '</div>';
		
		// Symptoms Summary
		echo '<div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">';
		echo '<h4 style="margin: 0 0 10px 0;">⚠️ Symptoms</h4>';
		$this->display_symptoms_summary( $user_id );
		echo '</div>';
		
		// Biomarkers Summary
		echo '<div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #20c997;">';
		echo '<h4 style="margin: 0 0 10px 0;">🧪 Biomarkers</h4>';
		$this->display_biomarkers_summary( $user_id );
		echo '</div>';
		
		echo '</div>'; // Close grid
		
		// Assessment Status Overview
		echo '<div style="background: white; padding: 15px; border-radius: 5px; margin-bottom: 10px;">';
		echo '<h4 style="margin: 0 0 15px 0;">📋 Assessment Status Overview</h4>';
		echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;">';
		
		foreach ( $completed_assessments as $assessment_type => $assessment_info ) {
			echo '<div style="background: #f8f9fa; padding: 8px 12px; border-radius: 3px; border-left: 3px solid #0073aa;">';
			echo '<div style="font-weight: bold; font-size: 14px;">' . esc_html( $assessment_info['title'] ) . '</div>';
			echo '<div style="color: #666; font-size: 12px;">' . esc_html( $assessment_info['status'] ) . '</div>';
			echo '</div>';
		}
		
		echo '</div></div>'; // Close assessment status and comprehensive overview
		echo '</div>'; // Close comprehensive overview
		
		// Create tabs for each assessment
		echo '<div id="ennu-assessment-tabs">';
		echo '<ul class="ennu-tab-nav">';
		
		// Global fields tab
		echo '<li class="ennu-tab-link" data-tab="global">' . esc_html__( 'Global Information', 'ennulifeassessments' ) . '</li>';
		
		// Health Goals tab
		echo '<li class="ennu-tab-link" data-tab="health-goals">' . esc_html__( 'Health Goals', 'ennulifeassessments' ) . '</li>';
		
		// Symptoms tab
		echo '<li class="ennu-tab-link" data-tab="symptoms">' . esc_html__( 'Symptoms', 'ennulifeassessments' ) . '</li>';
		
		// Biomarkers tab
		echo '<li class="ennu-tab-link" data-tab="biomarkers">' . esc_html__( 'Biomarkers', 'ennulifeassessments' ) . '</li>';
		
		// Assessment-specific tabs
		foreach ( $completed_assessments as $assessment_type => $assessment_info ) {
			echo '<li class="ennu-tab-link" data-tab="' . esc_attr( $assessment_type ) . '">' . esc_html( $assessment_info['title'] ) . '</li>';
		}
		
		echo '</ul>';
		
		// Global Information Tab
		echo '<div id="global" class="ennu-tab-content">';
		echo '<h4>' . esc_html__( 'Global Information', 'ennulifeassessments' ) . '</h4>';
		$this->display_global_fields( $user_id );
		echo '</div>';
		
		// Health Goals Tab
		echo '<div id="health-goals" class="ennu-tab-content">';
		echo '<h4>' . esc_html__( 'Health Goals', 'ennulifeassessments' ) . '</h4>';
		$this->display_health_goals_tab( $user_id );
		echo '</div>';
		
		// Symptoms Tab
		echo '<div id="symptoms" class="ennu-tab-content">';
		echo '<h4>' . esc_html__( 'Symptoms', 'ennulifeassessments' ) . '</h4>';
		$this->display_symptoms_tab( $user_id );
		echo '</div>';
		
		// Biomarkers Tab
		echo '<div id="biomarkers" class="ennu-tab-content">';
		echo '<h4>' . esc_html__( 'Biomarkers', 'ennulifeassessments' ) . '</h4>';
		$this->display_biomarkers_tab( $user_id );
		echo '</div>';
		
		// Assessment-specific tabs with questions and answers
		foreach ( $completed_assessments as $assessment_type => $assessment_info ) {
			echo '<div id="' . esc_attr( $assessment_type ) . '" class="ennu-tab-content">';
			echo '<h4>' . esc_html( $assessment_info['title'] ) . '</h4>';
			
			// Display status and metadata
			echo '<div class="assessment-meta" style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
			echo '<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">';
			
			// Left column - Basic info
			echo '<div>';
			echo '<strong>' . esc_html__( 'Status:', 'ennulifeassessments' ) . '</strong> ' . esc_html( $assessment_info['status'] ) . '<br>';
			
			// Get submission date if available
			$submission_date = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_submitted_at', true );
			if ( $submission_date ) {
				echo '<strong>' . esc_html__( 'Submitted:', 'ennulifeassessments' ) . '</strong> ' . esc_html( $submission_date ) . '<br>';
			}
			
			echo '</div>';
			
			// Right column - Assessment-specific pillar scores
			echo '<div>';
			echo '<strong>' . esc_html__( 'Assessment Pillar Scores:', 'ennulifeassessments' ) . '</strong><br>';
			$this->display_assessment_pillar_scores( $user_id, $assessment_type );
			echo '</div>';
			
			echo '</div>'; // Close grid
			echo '</div>'; // Close assessment-meta
			
			// Display questions and answers
			$this->display_assessment_questions_and_answers( $user_id, $assessment_type );
			
			echo '</div>';
		}
		
		echo '</div>'; // Close tabs container
		
		// Add styling and JavaScript
		$this->add_user_profile_styles_and_scripts( $user_id );
		
		echo '</div>'; // Close ennu-admin-assessment-data
	}
	
	/**
	 * Display global fields for the user
	 */
	private function display_global_fields( $user_id ) {
		$global_fields = array(
			'ennu_global_date_of_birth' => 'Date of Birth',
			'ennu_global_gender' => 'Gender',
			'ennu_global_height_weight' => 'Height & Weight',
			'billing_phone' => 'Phone Number',
			'ennu_global_email' => 'Email',
		);
		
		echo '<table class="form-table">';
		foreach ( $global_fields as $field_key => $field_label ) {
			$value = get_user_meta( $user_id, $field_key, true );
			if ( ! empty( $value ) ) {
				echo '<tr>';
				echo '<th scope="row">' . esc_html( $field_label ) . '</th>';
				echo '<td>';
				
				if ( $field_key === 'ennu_global_height_weight' && is_array( $value ) ) {
					$height = isset( $value['ft'], $value['in'] ) ? $value['ft'] . "' " . $value['in'] . '"' : '';
					$weight = isset( $value['lbs'] ) ? $value['lbs'] . ' lbs' : ( isset( $value['weight'] ) ? $value['weight'] . ' lbs' : '' );
					echo esc_html( $height . ' / ' . $weight );
				} elseif ( is_array( $value ) ) {
					echo '<pre>' . esc_html( json_encode( $value, JSON_PRETTY_PRINT ) ) . '</pre>';
				} else {
					echo esc_html( $value );
				}
				
				echo '</td>';
				echo '</tr>';
			}
		}
		echo '</table>';
	}
	
	/**
	 * Display questions and answers for a specific assessment
	 */
	private function display_assessment_questions_and_answers( $user_id, $assessment_type ) {
		// Load assessment configuration
		$config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/' . $assessment_type . '.php';
		$assessment_config = null;
		$has_config = false;
		
		if ( file_exists( $config_file ) ) {
			$assessment_config = include $config_file;
			if ( isset( $assessment_config['questions'] ) ) {
				$has_config = true;
			}
		}
		
		if ( ! $has_config ) {
			// If no config file, show raw user meta data for this assessment
			echo '<p><em>Configuration file not found. Showing raw assessment data:</em></p>';
			$this->display_raw_assessment_data( $user_id, $assessment_type );
			return;
		}
		
		echo '<table class="form-table ennu-questions-table">';
		
		foreach ( $assessment_config['questions'] as $question_key => $question_data ) {
			// Skip global fields in assessment-specific tabs
			if ( isset( $question_data['global_key'] ) ) {
				continue;
			}
			
			// Get the answer from user meta
			$answer_key = 'ennu_' . $assessment_type . '_' . str_replace( $assessment_type . '_', '', $question_key );
			$answer = get_user_meta( $user_id, $answer_key, true );
			
			echo '<tr>';
			echo '<th scope="row" style="width: 30%; vertical-align: top;">';
			echo '<strong>' . esc_html( $question_data['title'] ?? $question_key ) . '</strong><br>';
			echo '<small style="color: #666; font-weight: normal;">Field ID: ' . esc_html( $answer_key ) . '</small>';
			echo '</th>';
			echo '<td style="width: 70%;">';
			
			// Show available options and highlight selected ones
			if ( isset( $question_data['options'] ) && is_array( $question_data['options'] ) ) {
				echo '<div class="ennu-question-options">';
				
				// Handle different question types
				$question_type = $question_data['type'] ?? 'radio';
				$user_answers = is_array( $answer ) ? $answer : ( ! empty( $answer ) ? array( $answer ) : array() );
				
				foreach ( $question_data['options'] as $option_key => $option_label ) {
					$is_selected = in_array( $option_key, $user_answers );
					$style = $is_selected ? 'background: #e7f3ff; border: 2px solid #0073aa; padding: 5px 8px; border-radius: 3px; margin: 2px; display: inline-block;' : 'background: #f9f9f9; border: 1px solid #ddd; padding: 5px 8px; border-radius: 3px; margin: 2px; display: inline-block;';
					
					echo '<span style="' . $style . '">';
					if ( $is_selected ) {
						echo '<strong>✓ ' . esc_html( $option_label ) . '</strong>';
					} else {
						echo esc_html( $option_label );
					}
					echo '</span> ';
				}
				
				echo '</div>';
				
				// Show raw answer if available
				if ( ! empty( $answer ) ) {
					echo '<div style="margin-top: 8px; font-size: 12px; color: #666;">';
					echo '<strong>Raw Answer:</strong> ';
					if ( is_array( $answer ) ) {
						echo esc_html( json_encode( $answer ) );
					} else {
						echo esc_html( $answer );
					}
					echo '</div>';
				}
			} else {
				// For non-option questions (text, etc.)
				echo '<div class="ennu-question-field">';
				if ( ! empty( $answer ) ) {
					echo '<strong>Answer:</strong> ';
					if ( is_array( $answer ) ) {
						echo '<pre>' . esc_html( json_encode( $answer, JSON_PRETTY_PRINT ) ) . '</pre>';
					} else {
						echo esc_html( $answer );
					}
				} else {
					echo '<em style="color: #999;">No answer provided</em>';
				}
				echo '</div>';
			}
			
			echo '</td>';
			echo '</tr>';
		}
		
		echo '</table>';
	}
	
	/**
	 * Add styles and scripts for the user profile display
	 *
	 * @param int $user_id User ID
	 */
	private function add_user_profile_styles_and_scripts( $user_id ) {
		?>
		<style>
		#ennu-assessment-tabs {
			margin-top: 20px;
		}
		.ennu-tab-nav {
			list-style: none;
			margin: 0;
			padding: 0;
			display: flex;
			flex-wrap: wrap;
			border-bottom: 2px solid #0073aa;
			background: #f8f9fa;
		}
		.ennu-tab-link {
			margin: 0;
			padding: 12px 20px;
			cursor: pointer !important;
			background: #f1f1f1;
			border: 1px solid #ddd;
			border-bottom: none;
			margin-right: 2px;
			border-radius: 4px 4px 0 0;
			transition: all 0.2s ease;
			user-select: none;
			position: relative;
			top: 2px;
		}
		.ennu-tab-link:hover {
			background: #e0e0e0;
			transform: translateY(-1px);
		}
		.ennu-tab-link.active {
			background: #0073aa;
			color: white;
			border-color: #0073aa;
			top: 2px;
			z-index: 10;
		}
		.ennu-tab-link.active:hover {
			background: #0073aa;
			transform: none;
		}
		.ennu-tab-content {
			display: none;
			padding: 20px;
			border: 1px solid #ddd;
			background: #fff;
			border-top: none;
		}
		.ennu-tab-content.active {
			display: block;
		}
		.ennu-questions-table th {
			background: #f8f9fa;
			font-weight: 600;
		}
		.ennu-questions-table tr:nth-child(even) {
			background: #f9f9f9;
		}
		.assessment-meta {
			font-size: 14px;
		}
		.ennu-summary-section h3 {
			margin-top: 0;
			color: #0073aa;
		}
		</style>

		<script type="text/javascript">
		(function() {
			// Wait for jQuery to be available
			function initENNUTabs() {
				if (typeof jQuery === 'undefined') {
					console.log('ENNU Admin: Waiting for jQuery...');
					setTimeout(initENNUTabs, 100);
					return;
				}
				
				jQuery(document).ready(function($) {
					// Debug log
					console.log('ENNU Admin: Initializing tabs');
					console.log('Tab links found:', $('.ennu-tab-link').length);
					console.log('Tab content found:', $('.ennu-tab-content').length);
					
					// Make first tab active by default
					$('.ennu-tab-link').first().addClass('active');
					$('.ennu-tab-content').first().addClass('active').show();
					
					// Tab switching functionality
					$(document).on('click', '.ennu-tab-link', function(e) {
						e.preventDefault();
						e.stopPropagation();
						
						var $this = $(this);
						var tab_id = $this.attr('data-tab');
						
						console.log('ENNU Admin: Switching to tab:', tab_id);
						
						// Don't do anything if already active
						if ($this.hasClass('active')) {
							return false;
						}
						
						// Remove active class from all tabs and content
						$('.ennu-tab-link').removeClass('active');
						$('.ennu-tab-content').removeClass('active').hide();
						
						// Add active class to clicked tab and show content
						$this.addClass('active');
						$("#" + tab_id).addClass('active').show();
						
						return false;
					});
					
					// Clear all user data button
					$('#ennu-clear-all-user-data').click(function() {
						if (!confirm('⚠️ WARNING: This will permanently delete ALL ENNU assessment data, scores, biomarkers, symptoms, and cached data for this user. This action cannot be undone. Are you sure?')) {
							return;
						}
						
						if (!confirm('🔴 FINAL CONFIRMATION: Delete all ENNU data for user #<?php echo esc_js($user_id); ?>?')) {
							return;
						}
						
						var button = $(this);
						var statusSpan = $('#ennu-clear-data-status');
						
						button.prop('disabled', true).text('Clearing data...');
						statusSpan.html('<span style="color: orange;">Processing...</span>');
						
						$.post(ajaxurl, {
							action: 'ennu_clear_all_user_data',
							user_id: <?php echo intval($user_id); ?>,
							nonce: '<?php echo wp_create_nonce('ennu_clear_user_data_' . $user_id); ?>'
						}, function(response) {
							if (response.success) {
								statusSpan.html('<span style="color: green;">✓ ' + response.data.message + '</span>');
								button.text('✓ Data Cleared');
								// Reload page after 2 seconds to show cleared state
								setTimeout(function() {
									location.reload();
								}, 2000);
							} else {
								statusSpan.html('<span style="color: red;">✗ ' + response.data + '</span>');
								button.prop('disabled', false).html('🗑️ Clear All ENNU Data for This User');
							}
						}).fail(function() {
							statusSpan.html('<span style="color: red;">✗ Request failed</span>');
							button.prop('disabled', false).html('🗑️ Clear All ENNU Data for This User');
						});
					});
				});
			}
			
			initENNUTabs();
		})();
		</script>
		<?php
	}


	public function save_user_assessment_fields( $user_id ) {
		// Check permissions
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		
		// Verify nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
			return;
		}
		
		// Note: First Name, Last Name, and Email are handled by WordPress core profile fields
		// We don't save them to global meta to avoid conflicts
		
		// Save global fields (excluding name and email)
		$global_fields = array(
			'billing_phone',
			'ennu_global_gender',
			'ennu_global_date_of_birth'
		);
		
		foreach ( $global_fields as $field ) {
			if ( isset( $_POST[$field] ) ) {
				update_user_meta( $user_id, $field, sanitize_text_field( $_POST[$field] ) );
			}
		}
		
		// Save height and weight
		if ( isset( $_POST['ennu_global_height_weight'] ) && is_array( $_POST['ennu_global_height_weight'] ) ) {
			$height_weight = array(
				'ft' => absint( $_POST['ennu_global_height_weight']['ft'] ?? 0 ),
				'in' => absint( $_POST['ennu_global_height_weight']['in'] ?? 0 ),
				'weight' => absint( $_POST['ennu_global_height_weight']['weight'] ?? 0 )
			);
			update_user_meta( $user_id, 'ennu_global_height_weight', $height_weight );
		}
		
		// Save health goals
		if ( isset( $_POST['ennu_global_health_goals'] ) && is_array( $_POST['ennu_global_health_goals'] ) ) {
			$health_goals = array_map( 'sanitize_text_field', $_POST['ennu_global_health_goals'] );
			update_user_meta( $user_id, 'ennu_global_health_goals', $health_goals );
		} else {
			// If no health goals selected, save empty array
			update_user_meta( $user_id, 'ennu_global_health_goals', array() );
		}
		
		// Save assessment-specific fields
		$assessment_types = array(
			'weight-loss',
			'hair',
			'ed-treatment',
			'testosterone',
			'health',
			'hormone',
			'skin',
			'sleep',
			'menopause'
		);
		
		foreach ( $assessment_types as $type ) {
			$prefix = 'ennu_' . str_replace( '-', '_', $type );
			$fields = array(
				$prefix . '_overall_score'
			);
			
			foreach ( $fields as $field ) {
				if ( isset( $_POST[$field] ) ) {
					update_user_meta( $user_id, $field, sanitize_text_field( $_POST[$field] ) );
				}
			}
		}
		
		// Handle ALL ENNU meta fields that were posted (including empty ones)
		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, 'ennu_' ) === 0 ) {
				// Skip array values that were already handled above
				if ( ! is_array( $value ) ) {
					// Handle different field types
					$field_type = $this->determine_field_type( $key, $value );
					
					if ( $field_type === 'boolean' ) {
						// Convert to proper boolean
						$bool_value = ( $value === '1' || $value === 'true' || $value === 'yes' ) ? '1' : '0';
						update_user_meta( $user_id, $key, $bool_value );
					} elseif ( $field_type === 'date' ) {
						// Validate date format
						$date_value = sanitize_text_field( $value );
						if ( ! empty( $date_value ) && strtotime( $date_value ) ) {
							update_user_meta( $user_id, $key, $date_value );
						}
					} elseif ( $field_type === 'email' ) {
						// Validate email
						$email_value = sanitize_email( $value );
						if ( ! empty( $email_value ) && is_email( $email_value ) ) {
							update_user_meta( $user_id, $key, $email_value );
						}
					} elseif ( $field_type === 'phone' ) {
						// Sanitize phone number
						$phone_value = sanitize_text_field( $value );
						update_user_meta( $user_id, $key, $phone_value );
					} elseif ( $field_type === 'json' ) {
						// Handle JSON fields
						if ( ! empty( $value ) ) {
							// Try to decode and re-encode to validate JSON
							$decoded = json_decode( $value, true );
							if ( json_last_error() === JSON_ERROR_NONE ) {
								update_user_meta( $user_id, $key, $value );
							} else {
								// If not valid JSON, save as text
								update_user_meta( $user_id, $key, sanitize_textarea_field( $value ) );
							}
						} else {
							// Save empty JSON field
							update_user_meta( $user_id, $key, '' );
						}
					} else {
						// Default text field
					update_user_meta( $user_id, $key, sanitize_text_field( $value ) );
					}
				}
			}
		}
		
		// Save biomarker data if posted
		if ( isset( $_POST['ennu_user_biomarkers'] ) && is_array( $_POST['ennu_user_biomarkers'] ) ) {
			$biomarkers = array();
			foreach ( $_POST['ennu_user_biomarkers'] as $key => $biomarker ) {
				if ( is_array( $biomarker ) && ! empty( $biomarker['name'] ) ) {
					$biomarkers[$key] = array(
						'name' => sanitize_text_field( $biomarker['name'] ),
						'value' => sanitize_text_field( $biomarker['value'] ?? '' ),
						'unit' => sanitize_text_field( $biomarker['unit'] ?? '' ),
						'reference_range' => sanitize_text_field( $biomarker['reference_range'] ?? '' ),
						'date' => sanitize_text_field( $biomarker['date'] ?? '' ),
						'status' => sanitize_text_field( $biomarker['status'] ?? 'normal' )
					);
				}
			}
			if ( ! empty( $biomarkers ) ) {
				update_user_meta( $user_id, 'ennu_user_biomarkers', $biomarkers );
			}
		}
		
		// Update centralized symptoms if needed
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id );
		}
		
		error_log( 'ENNU Enhanced Admin: Saved assessment fields for user ID: ' . $user_id );
	}
	
	/**
	 * Determine the field type based on field key and value
	 *
	 * @param string $field_key The field key
	 * @param mixed $value The field value
	 * @return string The field type
	 */
	private function determine_field_type( $field_key, $value ) {
		// Check for specific field patterns
		if ( strpos( $field_key, '_email' ) !== false ) {
			return 'email';
		}
		if ( strpos( $field_key, '_phone' ) !== false || strpos( $field_key, 'billing_phone' ) !== false ) {
			return 'phone';
		}
		if ( strpos( $field_key, '_date' ) !== false || strpos( $field_key, '_at' ) !== false || strpos( $field_key, '_time' ) !== false ) {
			return 'date';
		}
		if ( strpos( $field_key, '_enabled' ) !== false || strpos( $field_key, '_verified' ) !== false || strpos( $field_key, '_generated' ) !== false ) {
			return 'boolean';
		}
		// Check for specific user-friendly field types first
		if ( strpos( $field_key, '_goals' ) !== false ) {
			return 'goals_list';
		}
		if ( strpos( $field_key, '_symptoms' ) !== false ) {
			return 'symptoms_list';
		}
		if ( strpos( $field_key, '_biomarkers' ) !== false ) {
			return 'biomarkers_list';
		}
		if ( strpos( $field_key, '_responses' ) !== false ) {
			return 'responses_list';
		}
		if ( strpos( $field_key, '_scores' ) !== false ) {
			return 'scores_list';
		}
		
		// Check for other complex data types that should be json
		if ( strpos( $field_key, '_data' ) !== false || strpos( $field_key, '_assessments' ) !== false ||
			 strpos( $field_key, '_preferences' ) !== false || strpos( $field_key, '_settings' ) !== false ||
			 strpos( $field_key, '_mappings' ) !== false || strpos( $field_key, '_integrations' ) !== false || strpos( $field_key, '_history' ) !== false ||
			 strpos( $field_key, '_ranges' ) !== false || strpos( $field_key, '_categories' ) !== false || strpos( $field_key, '_triggers' ) !== false ||
			 strpos( $field_key, '_severity' ) !== false || strpos( $field_key, '_frequency' ) !== false || strpos( $field_key, '_metrics' ) !== false ||
			 strpos( $field_key, '_layout' ) !== false || strpos( $field_key, '_progress' ) !== false || strpos( $field_key, '_webhook' ) !== false ||
			 strpos( $field_key, '_policy' ) !== false || strpos( $field_key, '_consent' ) !== false || strpos( $field_key, '_errors' ) !== false ||
			 strpos( $field_key, '_checks' ) !== false || strpos( $field_key, '_completeness' ) !== false || strpos( $field_key, '_consistency' ) !== false ||
			 strpos( $field_key, '_cache' ) !== false || strpos( $field_key, '_transient' ) !== false || strpos( $field_key, '_log' ) !== false ||
			 strpos( $field_key, '_token' ) !== false || strpos( $field_key, '_url' ) !== false || strpos( $field_key, '_sync' ) !== false ||
			 strpos( $field_key, '_export' ) !== false || strpos( $field_key, '_backup' ) !== false || strpos( $field_key, '_restore' ) !== false ||
			 strpos( $field_key, '_hits' ) !== false || strpos( $field_key, '_misses' ) !== false || strpos( $field_key, '_shared' ) !== false ||
			 strpos( $field_key, '_point' ) !== false || strpos( $field_key, '_timeout' ) !== false || strpos( $field_key, '_start_time' ) !== false ||
			 strpos( $field_key, '_last_' ) !== false || strpos( $field_key, '_total_' ) !== false || strpos( $field_key, '_current_' ) !== false ||
			 strpos( $field_key, '_version' ) !== false || strpos( $field_key, '_status' ) !== false || strpos( $field_key, '_id' ) !== false ||
			 strpos( $field_key, '_count' ) !== false || strpos( $field_key, '_percentage' ) !== false || strpos( $field_key, '_score' ) !== false ) {
			return 'json';
		}
		
		// Check for custom field types
		if ( strpos( $field_key, '_height_weight' ) !== false ) {
			return 'height_weight';
		}
		if ( strpos( $field_key, '_dob' ) !== false || strpos( $field_key, '_date_of_birth' ) !== false ) {
			return 'dob_dropdowns';
		}
		if ( strpos( $field_key, '_first_last_name' ) !== false || strpos( $field_key, '_name' ) !== false ) {
			return 'first_last_name';
		}
		if ( strpos( $field_key, '_email_phone' ) !== false ) {
			return 'email_phone';
		}
		if ( strpos( $field_key, '_contact_info' ) !== false ) {
			return 'contact_info';
		}
		if ( strpos( $field_key, '_severity_scale' ) !== false ) {
			return 'severity_scale';
		}
		
		// Check if value is already an array or object
		if ( is_array( $value ) || is_object( $value ) ) {
			return 'json';
		}
		
		// Check if value looks like JSON
		if ( is_string( $value ) && !empty($value) && ( $value[0] === '{' || $value[0] === '[' ) ) {
			$decoded = json_decode( $value, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return 'json';
			}
		}
		
		// Default to text
		return 'text';
	}
	
	/**
	 * Add assessment questions to existing assessment tabs
	 *
	 * @param array $ennu_fields_by_tab Existing tabs array
	 * @param int $user_id User ID
	 * @return array Modified tabs array with questions added
	 */
	private function add_questions_to_assessment_tabs($ennu_fields_by_tab, $user_id) {
		// Define assessment types that have corresponding tabs
		$assessment_types = array(
			'hair' => 'hair',
			'weight_loss' => 'weight_loss',
			'ed_treatment' => 'ed_treatment',
			'testosterone' => 'testosterone',
			'health' => 'health',
			'hormone' => 'hormone',
			'skin' => 'skin',
			'sleep' => 'sleep',
			'menopause' => 'menopause',
			'health_optimization' => 'health_optimization',
			'welcome' => 'welcome'
		);
		
		foreach ($assessment_types as $assessment_type => $tab_key) {
			// Check if the assessment tab exists
			if (isset($ennu_fields_by_tab[$tab_key])) {
				$questions = $this->get_assessment_questions($assessment_type);
				if (!empty($questions)) {
					// Add question fields to the existing assessment tab
					$question_fields = $this->format_question_fields($questions, $assessment_type, $user_id);
					
					// Add a section header for individual questions
					$question_fields['assessment_questions_header'] = array(
						'type' => 'section_header',
						'question_data' => array('title' => 'Individual Assessment Questions'),
						'current_value' => '',
						'field_type' => 'section_header'
					);
					
					// Convert existing fields to the new format if they're strings
					$existing_fields = $ennu_fields_by_tab[$tab_key]['fields'];
					$converted_fields = array();
					
					foreach ($existing_fields as $field_key => $field_data) {
						if (is_string($field_data)) {
							// Convert string field name to field data object
							$converted_fields[$field_data] = array(
								'type' => 'field',
								'field_key' => $field_data,
								'current_value' => '',
								'field_type' => $this->determine_field_type($field_data, '')
							);
						} else {
							// Keep existing field data as is
							$converted_fields[$field_key] = $field_data;
						}
					}
					
					// Merge the converted fields with question fields
					$ennu_fields_by_tab[$tab_key]['fields'] = array_merge(
						$converted_fields,
						$question_fields
					);
				}
			}
		}
		
		return $ennu_fields_by_tab;
	}
	
	/**
	 * Get assessment questions from config files
	 *
	 * @param string $assessment_type Assessment type
	 * @return array Questions array
	 */
	private function get_assessment_questions($assessment_type) {
		$config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/' . $assessment_type . '.php';
		
		if (!file_exists($config_file)) {
			return array();
		}
		
		try {
			$config = require $config_file;
			return isset($config['questions']) ? $config['questions'] : array();
		} catch (Exception $e) {
			error_log('ENNU Enhanced Admin: Error loading assessment config for ' . $assessment_type . ': ' . $e->getMessage());
			return array();
		}
	}
	
	/**
	 * Format question fields for display
	 *
	 * @param array $questions Questions array
	 * @param string $assessment_type Assessment type
	 * @param int $user_id User ID
	 * @return array Formatted fields
	 */
	private function format_question_fields($questions, $assessment_type, $user_id) {
		$fields = array();
		
		// Get user responses for this assessment
		$responses_key = 'ennu_' . $assessment_type . '_responses';
		$user_responses = get_user_meta($user_id, $responses_key, true);
		if (!is_array($user_responses)) {
			$user_responses = array();
		}
		
		foreach ($questions as $question_key => $question_data) {
			$field_key = $responses_key . '[' . $question_key . ']';
			$current_value = isset($user_responses[$question_key]) ? $user_responses[$question_key] : '';
			
			// Store question data for rendering
			$fields[$field_key] = array(
				'type' => 'question',
				'question_data' => $question_data,
				'question_key' => $question_key,
				'current_value' => $current_value,
				'field_type' => $this->determine_question_field_type($question_data)
			);
		}
		
		return $fields;
	}
	
	/**
	 * Determine field type for question based on question data
	 *
	 * @param array $question_data Question configuration
	 * @return string Field type
	 */
	private function determine_question_field_type($question_data) {
		if (isset($question_data['type'])) {
			switch ($question_data['type']) {
				case 'radio':
				case 'select':
					return 'select';
				case 'checkbox':
				case 'multiselect':
					return 'multiselect';
				case 'text':
				case 'input':
					return 'text';
				case 'textarea':
					return 'textarea';
				case 'number':
				case 'range':
					return 'number';
				case 'date':
					return 'date';
				case 'email':
					return 'email';
				case 'tel':
				case 'phone':
					return 'phone';
				case 'height_weight':
					return 'height_weight';
				case 'dob_dropdowns':
					return 'dob_dropdowns';
				default:
					return 'text';
			}
		}
		
		// Default based on options
		if (isset($question_data['options']) && is_array($question_data['options'])) {
			return count($question_data['options']) > 1 ? 'select' : 'text';
		}
		
		return 'text';
	}
	
	/**
	 * Render a question field with proper input type and options
	 *
	 * @param array $field_data Field data containing question information
	 * @param string $field_key Field key
	 */
	private function render_question_field($field_data, $field_key) {
		$question_data = $field_data['question_data'];
		$current_value = $field_data['current_value'];
		$field_type = $field_data['field_type'];
		$question_text = isset($question_data['title']) ? $question_data['title'] : (isset($question_data['question']) ? $question_data['question'] : '');
		$options = isset($question_data['options']) ? $question_data['options'] : array();
		
		// Display question text
		echo '<div class="ennu-question-container">';
		echo '<div class="ennu-question-text">' . esc_html($question_text) . '</div>';
		
		// Render based on field type
		switch ($field_type) {
			case 'select':
				echo '<select name="' . esc_attr($field_key) . '" id="' . esc_attr($field_key) . '" class="ennu-field-input">';
				echo '<option value="">' . esc_html__('Select an option...', 'ennulifeassessments') . '</option>';
				foreach ($options as $option_value => $option_label) {
					$selected = ($current_value == $option_value) ? 'selected' : '';
					echo '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . esc_html($option_label) . '</option>';
				}
				echo '</select>';
				break;
				
			case 'multiselect':
				$selected_values = is_array($current_value) ? $current_value : array();
				foreach ($options as $option_value => $option_label) {
					$checked = in_array($option_value, $selected_values) ? 'checked' : '';
					echo '<label class="ennu-checkbox-label">';
					echo '<input type="checkbox" name="' . esc_attr($field_key) . '[]" value="' . esc_attr($option_value) . '" ' . $checked . ' /> ';
					echo esc_html($option_label);
					echo '</label><br>';
				}
				break;
				
			case 'textarea':
				echo '<textarea name="' . esc_attr($field_key) . '" id="' . esc_attr($field_key) . '" rows="3" class="ennu-field-input" placeholder="' . esc_attr__('Enter your answer...', 'ennulifeassessments') . '">' . esc_textarea($current_value) . '</textarea>';
				break;
				
			case 'number':
				$min = isset($question_data['min']) ? $question_data['min'] : '';
				$max = isset($question_data['max']) ? $question_data['max'] : '';
				echo '<input type="number" name="' . esc_attr($field_key) . '" id="' . esc_attr($field_key) . '" value="' . esc_attr($current_value) . '" class="ennu-field-input"';
				if ($min !== '') echo ' min="' . esc_attr($min) . '"';
				if ($max !== '') echo ' max="' . esc_attr($max) . '"';
				echo ' />';
				break;
				
			case 'date':
				echo '<input type="date" name="' . esc_attr($field_key) . '" id="' . esc_attr($field_key) . '" value="' . esc_attr($current_value) . '" class="ennu-field-input" />';
				break;
				
			case 'email':
				echo '<input type="email" name="' . esc_attr($field_key) . '" id="' . esc_attr($field_key) . '" value="' . esc_attr($current_value) . '" class="ennu-field-input" placeholder="' . esc_attr__('Enter email address...', 'ennulifeassessments') . '" />';
				break;
				
			case 'phone':
				echo '<input type="tel" name="' . esc_attr($field_key) . '" id="' . esc_attr($field_key) . '" value="' . esc_attr($current_value) . '" class="ennu-field-input" placeholder="' . esc_attr__('Enter phone number...', 'ennulifeassessments') . '" />';
				break;
				
			case 'height_weight':
				// Parse height/weight data
				$height_weight_data = is_array($current_value) ? $current_value : array();
				$ft = isset($height_weight_data['ft']) ? $height_weight_data['ft'] : '';
				$in = isset($height_weight_data['in']) ? $height_weight_data['in'] : '';
				$weight = isset($height_weight_data['weight']) ? $height_weight_data['weight'] : '';
				
				echo '<div class="height-weight-container">';
				echo '<div class="height-section">';
				echo '<label>' . esc_html__('Height:', 'ennulifeassessments') . '</label>';
				echo '<div class="height-inputs">';
				echo '<input type="number" name="' . esc_attr($field_key) . '[ft]" value="' . esc_attr($ft) . '" min="0" max="8" placeholder="ft" class="ennu-field-input" />';
				echo '<input type="number" name="' . esc_attr($field_key) . '[in]" value="' . esc_attr($in) . '" min="0" max="11" placeholder="in" class="ennu-field-input" />';
				echo '</div>';
				echo '</div>';
				echo '<div class="weight-section">';
				echo '<label>' . esc_html__('Weight (lbs):', 'ennulifeassessments') . '</label>';
				echo '<input type="number" name="' . esc_attr($field_key) . '[weight]" value="' . esc_attr($weight) . '" min="0" step="0.1" placeholder="lbs" class="ennu-field-input" />';
				echo '</div>';
				echo '</div>';
				break;
				
			case 'dob_dropdowns':
				// Parse date of birth data
				$dob_data = is_array($current_value) ? $current_value : array();
				$month = isset($dob_data['month']) ? $dob_data['month'] : '';
				$day = isset($dob_data['day']) ? $dob_data['day'] : '';
				$year = isset($dob_data['year']) ? $dob_data['year'] : '';
				
				echo '<div class="dob-container">';
				echo '<div class="dob-section">';
				echo '<label>' . esc_html__('Date of Birth:', 'ennulifeassessments') . '</label>';
				echo '<div class="dob-inputs">';
				
				// Month dropdown
				echo '<select name="' . esc_attr($field_key) . '[month]" class="ennu-field-input">';
				echo '<option value="">' . esc_html__('Month', 'ennulifeassessments') . '</option>';
				for ($m = 1; $m <= 12; $m++) {
					$selected = ($month == $m) ? 'selected' : '';
					echo '<option value="' . $m . '" ' . $selected . '>' . date('F', mktime(0, 0, 0, $m, 1)) . '</option>';
				}
				echo '</select>';
				
				// Day dropdown
				echo '<select name="' . esc_attr($field_key) . '[day]" class="ennu-field-input">';
				echo '<option value="">' . esc_html__('Day', 'ennulifeassessments') . '</option>';
				for ($d = 1; $d <= 31; $d++) {
					$selected = ($day == $d) ? 'selected' : '';
					echo '<option value="' . $d . '" ' . $selected . '>' . $d . '</option>';
				}
				echo '</select>';
				
				// Year dropdown
				echo '<select name="' . esc_attr($field_key) . '[year]" class="ennu-field-input">';
				echo '<option value="">' . esc_html__('Year', 'ennulifeassessments') . '</option>';
				$current_year = date('Y');
				for ($y = $current_year - 100; $y <= $current_year - 13; $y++) {
					$selected = ($year == $y) ? 'selected' : '';
					echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
				}
				echo '</select>';
				
				echo '</div>';
				echo '</div>';
				echo '</div>';
				break;
				
			case 'first_last_name':
				// Parse first/last name data
				$name_data = is_array($current_value) ? $current_value : array();
				$first_name = isset($name_data['first_name']) ? $name_data['first_name'] : '';
				$last_name = isset($name_data['last_name']) ? $name_data['last_name'] : '';
				
				echo '<div class="name-container">';
				echo '<div class="name-section">';
				echo '<label>' . esc_html__('First Name:', 'ennulifeassessments') . '</label>';
				echo '<input type="text" name="' . esc_attr($field_key) . '[first_name]" value="' . esc_attr($first_name) . '" class="ennu-field-input" placeholder="' . esc_attr__('First Name', 'ennulifeassessments') . '" />';
				echo '</div>';
				echo '<div class="name-section">';
				echo '<label>' . esc_html__('Last Name:', 'ennulifeassessments') . '</label>';
				echo '<input type="text" name="' . esc_attr($field_key) . '[last_name]" value="' . esc_attr($last_name) . '" class="ennu-field-input" placeholder="' . esc_attr__('Last Name', 'ennulifeassessments') . '" />';
				echo '</div>';
				echo '</div>';
				break;
				
			case 'email_phone':
				// Parse email/phone data
				$contact_data = is_array($current_value) ? $current_value : array();
				$email = isset($contact_data['email']) ? $contact_data['email'] : '';
				$phone = isset($contact_data['phone']) ? $contact_data['phone'] : '';
				
				echo '<div class="contact-container">';
				echo '<div class="contact-section">';
				echo '<label>' . esc_html__('Email:', 'ennulifeassessments') . '</label>';
				echo '<input type="email" name="' . esc_attr($field_key) . '[email]" value="' . esc_attr($email) . '" class="ennu-field-input" placeholder="' . esc_attr__('Email Address', 'ennulifeassessments') . '" />';
				echo '</div>';
				echo '<div class="contact-section">';
				echo '<label>' . esc_html__('Phone:', 'ennulifeassessments') . '</label>';
				echo '<input type="tel" name="' . esc_attr($field_key) . '[phone]" value="' . esc_attr($phone) . '" class="ennu-field-input" placeholder="' . esc_attr__('Phone Number', 'ennulifeassessments') . '" />';
				echo '</div>';
				echo '</div>';
				break;
				
			case 'contact_info':
				// Parse contact info data
				$contact_data = is_array($current_value) ? $current_value : array();
				$name = isset($contact_data['name']) ? $contact_data['name'] : '';
				$email = isset($contact_data['email']) ? $contact_data['email'] : '';
				$phone = isset($contact_data['phone']) ? $contact_data['phone'] : '';
				
				echo '<div class="contact-info-container">';
				echo '<div class="contact-section">';
				echo '<label>' . esc_html__('Name:', 'ennulifeassessments') . '</label>';
				echo '<input type="text" name="' . esc_attr($field_key) . '[name]" value="' . esc_attr($name) . '" class="ennu-field-input" placeholder="' . esc_attr__('Full Name', 'ennulifeassessments') . '" />';
				echo '</div>';
				echo '<div class="contact-section">';
				echo '<label>' . esc_html__('Email:', 'ennulifeassessments') . '</label>';
				echo '<input type="email" name="' . esc_attr($field_key) . '[email]" value="' . esc_attr($email) . '" class="ennu-field-input" placeholder="' . esc_attr__('Email Address', 'ennulifeassessments') . '" />';
				echo '</div>';
				echo '<div class="contact-section">';
				echo '<label>' . esc_html__('Phone:', 'ennulifeassessments') . '</label>';
				echo '<input type="tel" name="' . esc_attr($field_key) . '[phone]" value="' . esc_attr($phone) . '" class="ennu-field-input" placeholder="' . esc_attr__('Phone Number', 'ennulifeassessments') . '" />';
				echo '</div>';
				echo '</div>';
				break;
				
			case 'severity_scale':
				// Parse severity scale data
				$severity_data = is_array($current_value) ? $current_value : array();
				$severity = isset($severity_data['severity']) ? $severity_data['severity'] : '';
				
				echo '<div class="severity-scale-container">';
				echo '<label>' . esc_html__('Severity Level:', 'ennulifeassessments') . '</label>';
				echo '<select name="' . esc_attr($field_key) . '[severity]" class="ennu-field-input">';
				echo '<option value="">' . esc_html__('Select Severity', 'ennulifeassessments') . '</option>';
				$severity_levels = array(
					'1' => 'Very Mild',
					'2' => 'Mild',
					'3' => 'Moderate',
					'4' => 'Moderately Severe',
					'5' => 'Severe',
					'6' => 'Very Severe',
					'7' => 'Extremely Severe'
				);
				foreach ($severity_levels as $level => $label) {
					$selected = ($severity == $level) ? 'selected' : '';
					echo '<option value="' . esc_attr($level) . '" ' . $selected . '>' . esc_html($label) . '</option>';
				}
				echo '</select>';
				echo '</div>';
				break;
				
			case 'goals_list':
				// Parse goals data
				$goals_data = is_array($current_value) ? $current_value : array();
				if (is_string($current_value)) {
					$goals_data = json_decode($current_value, true) ?: array();
				}
				
				echo '<div class="goals-list-container">';
				echo '<label>' . esc_html__('Health Goals:', 'ennulifeassessments') . '</label>';
				if (!empty($goals_data)) {
					echo '<ul class="ennu-goals-list">';
					foreach ($goals_data as $goal) {
						if (is_string($goal)) {
							echo '<li>' . esc_html($goal) . '</li>';
						} elseif (is_array($goal) && isset($goal['name'])) {
							echo '<li>' . esc_html($goal['name']) . '</li>';
						}
					}
					echo '</ul>';
				} else {
					echo '<p class="ennu-no-data">' . esc_html__('No goals set', 'ennulifeassessments') . '</p>';
				}
				echo '</div>';
				break;
				
			case 'symptoms_list':
				// Parse symptoms data
				$symptoms_data = is_array($current_value) ? $current_value : array();
				if (is_string($current_value)) {
					$symptoms_data = json_decode($current_value, true) ?: array();
				}
				
				echo '<div class="symptoms-list-container">';
				echo '<label>' . esc_html__('Symptoms:', 'ennulifeassessments') . '</label>';
				if (!empty($symptoms_data) && isset($symptoms_data['symptoms'])) {
					echo '<ul class="ennu-symptoms-list">';
					foreach ($symptoms_data['symptoms'] as $symptom_key => $symptom_data) {
						$symptom_name = is_array($symptom_data) ? ($symptom_data['name'] ?? $symptom_key) : $symptom_data;
						$severity = is_array($symptom_data) ? ($symptom_data['severity'] ?? '') : '';
						echo '<li>';
						echo '<strong>' . esc_html($symptom_name) . '</strong>';
						if ($severity) {
							echo ' <span class="severity">(' . esc_html($severity) . ')</span>';
						}
						echo '</li>';
					}
					echo '</ul>';
				} else {
					echo '<p class="ennu-no-data">' . esc_html__('No symptoms recorded', 'ennulifeassessments') . '</p>';
				}
				echo '</div>';
				break;
				
			case 'biomarkers_list':
				// Parse biomarkers data
				$biomarkers_data = is_array($current_value) ? $current_value : array();
				if (is_string($current_value)) {
					$biomarkers_data = json_decode($current_value, true) ?: array();
				}
				
				echo '<div class="biomarkers-list-container">';
				echo '<label>' . esc_html__('Biomarkers:', 'ennulifeassessments') . '</label>';
				if (!empty($biomarkers_data)) {
					echo '<ul class="ennu-biomarkers-list">';
					foreach ($biomarkers_data as $biomarker_key => $biomarker_data) {
						if (is_array($biomarker_data)) {
							$name = $biomarker_data['name'] ?? $biomarker_key;
							$value = $biomarker_data['value'] ?? '';
							$unit = $biomarker_data['unit'] ?? '';
							$status = $biomarker_data['status'] ?? '';
							
							echo '<li>';
							echo '<strong>' . esc_html($name) . '</strong>';
							if ($value) {
								echo ': ' . esc_html($value);
								if ($unit) echo ' ' . esc_html($unit);
							}
							if ($status) {
								echo ' <span class="status-' . esc_attr($status) . '">(' . esc_html(ucfirst($status)) . ')</span>';
							}
							echo '</li>';
						} else {
							echo '<li>' . esc_html($biomarker_data) . '</li>';
						}
					}
					echo '</ul>';
				} else {
					echo '<p class="ennu-no-data">' . esc_html__('No biomarkers recorded', 'ennulifeassessments') . '</p>';
				}
				echo '</div>';
				break;
				
			case 'responses_list':
				// Parse responses data
				$responses_data = is_array($current_value) ? $current_value : array();
				if (is_string($current_value)) {
					$responses_data = json_decode($current_value, true) ?: array();
				}
				
				echo '<div class="responses-list-container">';
				echo '<label>' . esc_html__('Assessment Responses:', 'ennulifeassessments') . '</label>';
				if (!empty($responses_data)) {
					echo '<div class="ennu-responses-detailed">';
					foreach ($responses_data as $question_key => $response) {
						echo '<div class="ennu-response-item">';
						
						// Format question name
						$question_name = str_replace('_', ' ', ucfirst($question_key));
						$question_name = preg_replace('/\b\w/', 'strtoupper("$0")', $question_name);
						
						echo '<div class="ennu-question-name">' . esc_html($question_name) . '</div>';
						echo '<div class="ennu-response-value">';
						
						// Format response value
						if (is_array($response)) {
							if (isset($response['ft']) && isset($response['in']) && isset($response['weight'])) {
								// Height/weight format
								echo esc_html($response['ft'] . "' " . $response['in'] . '" / ' . $response['weight'] . ' lbs');
							} elseif (isset($response['month']) && isset($response['day']) && isset($response['year'])) {
								// Date format
								$months = array(
									1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
									5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
									9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
								);
								echo esc_html($months[$response['month']] . ' ' . $response['day'] . ', ' . $response['year']);
							} else {
								// Array format
								echo esc_html(implode(', ', $response));
							}
						} elseif (is_string($response)) {
							// String format - make it more readable
							$formatted_response = str_replace('_', ' ', $response);
							$formatted_response = ucwords($formatted_response);
							echo esc_html($formatted_response);
						} else {
							echo esc_html($response);
						}
						
						echo '</div>';
						echo '</div>';
					}
					echo '</div>';
				} else {
					echo '<p class="ennu-no-data">' . esc_html__('No responses recorded', 'ennulifeassessments') . '</p>';
				}
				echo '</div>';
				break;
				
			case 'scores_list':
				// Parse scores data
				$scores_data = is_array($current_value) ? $current_value : array();
				if (is_string($current_value)) {
					$scores_data = json_decode($current_value, true) ?: array();
				}
				
				echo '<div class="scores-list-container">';
				echo '<label>' . esc_html__('Assessment Scores:', 'ennulifeassessments') . '</label>';
				if (!empty($scores_data)) {
					echo '<ul class="ennu-scores-list">';
					foreach ($scores_data as $score_key => $score_value) {
						$score_name = str_replace('_', ' ', ucfirst($score_key));
						echo '<li>';
						echo '<strong>' . esc_html($score_name) . '</strong>: ';
						if (is_numeric($score_value)) {
							echo esc_html(number_format($score_value, 1));
						} else {
							echo esc_html($score_value);
						}
						echo '</li>';
					}
					echo '</ul>';
				} else {
					echo '<p class="ennu-no-data">' . esc_html__('No scores recorded', 'ennulifeassessments') . '</p>';
				}
				echo '</div>';
				break;
				
			case 'section_header':
				echo '<div class="ennu-section-header">';
				echo '<h3>' . esc_html($question_text) . '</h3>';
				echo '</div>';
				break;
				
			default:
				echo '<input type="text" name="' . esc_attr($field_key) . '" id="' . esc_attr($field_key) . '" value="' . esc_attr($current_value) . '" class="ennu-field-input" placeholder="' . esc_attr__('Enter your answer...', 'ennulifeassessments') . '" />';
				break;
		}
		
		// Show current answer if exists
		if (!empty($current_value)) {
			echo '<div class="ennu-current-answer">';
			echo '<strong>' . esc_html__('Current Answer:', 'ennulifeassessments') . '</strong> ';
			if (is_array($current_value)) {
				echo esc_html(implode(', ', $current_value));
			} else {
				echo esc_html($current_value);
			}
			echo '</div>';
		}
		
		echo '</div>';
	}

	/**
	 * AJAX handler for populating symptoms from assessments
	 * This function is called when users click "Extract from Assessments" on their dashboard
	 * It triggers the centralized symptoms manager to update symptoms automatically
	 *
	 * @since 64.49.0
	 */
	public function ajax_populate_symptoms() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		// Get current user ID
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'User not logged in' ) );
		}

		// Check user permissions
		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		try {
			error_log( "ENNU Enhanced Admin: Starting ajax_populate_symptoms for user {$user_id}" );

			// Check if centralized symptoms manager exists
			if ( ! class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
				error_log( 'ENNU Enhanced Admin: ERROR - ENNU_Centralized_Symptoms_Manager class not found!' );
				wp_send_json_error( array( 'message' => 'Symptom management system not available' ) );
			}

			// Trigger the centralized symptoms update (this is the same as automatic process)
			$success = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id );

			if ( $success ) {
				// Get updated symptoms for response
				$symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
				
				$response_data = array(
					'success' => true,
					'message' => 'Symptoms extracted and updated successfully',
					'symptoms_count' => isset( $symptoms['symptoms'] ) ? count( $symptoms['symptoms'] ) : 0,
					'timestamp' => current_time( 'mysql' )
				);

				error_log( "ENNU Enhanced Admin: ajax_populate_symptoms completed successfully for user {$user_id}" );
				wp_send_json_success( $response_data );
			} else {
				error_log( "ENNU Enhanced Admin: ajax_populate_symptoms failed for user {$user_id}" );
				wp_send_json_error( array( 'message' => 'Failed to update symptoms' ) );
			}

		} catch ( Exception $e ) {
			error_log( 'ENNU Enhanced Admin: Exception in ajax_populate_symptoms: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => 'An error occurred while processing symptoms' ) );
		}
	}

	/**
	 * Automatically trigger symptom population when assessment is completed
	 * This ensures the system works without any manual intervention
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 */
	public function auto_populate_symptoms_on_assessment_completion( $user_id, $assessment_type ) {
		if ( ! class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			error_log( 'ENNU Enhanced Admin: ERROR - ENNU_Centralized_Symptoms_Manager class not found for auto-population!' );
			return;
		}

		error_log( "ENNU Enhanced Admin: Auto-populating symptoms for user {$user_id} after {$assessment_type} assessment completion" );
		
		// Trigger the centralized symptoms update automatically
		$success = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $assessment_type );
		
		if ( $success ) {
			error_log( "ENNU Enhanced Admin: Auto-population completed successfully for user {$user_id}" );
		} else {
			error_log( "ENNU Enhanced Admin: Auto-population failed for user {$user_id}" );
		}
	}

	/**
	 * AJAX handler for deleting all ENNU user data
	 * This function completely removes all ENNU-related data for a user
	 *
	 * @since 1.0.0
	 */
	public function ajax_delete_ennu_user_data() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed' ) );
		}

		$user_id = intval( $_POST['user_id'] );
		
		// Verify nonce is specific to this user
		if ( ! wp_verify_nonce( $_POST['nonce'], 'delete_ennu_data_' . $user_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		// Check user permissions - only admins can delete user data
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		// Verify user exists
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			wp_send_json_error( array( 'message' => 'User not found' ) );
		}

		try {
			error_log( "ENNU Enhanced Admin: Starting ajax_delete_ennu_user_data for user {$user_id}" );

			$deleted_count = 0;
			$errors = array();

			// 1. Delete all ENNU user meta fields
			$ennu_meta_keys = array(
				// Global fields
				'ennu_global_gender',
				'ennu_global_date_of_birth',
				'ennu_global_exact_age',
				'ennu_global_age_range',
				'ennu_global_age_category',
				'ennu_global_height_weight',
				'ennu_global_health_goals',
				'billing_phone',
				'ennu_global_email',
				
				// Assessment-specific fields
				'ennu_hair_completed',
				'ennu_hair_calculated_score',
				'ennu_hair_score_interpretation',
				'ennu_hair_category_scores',
				'ennu_hair_pillar_scores',
				'ennu_hair_auto_submit_ready',
				'ennu_hair_assessment_type',
				'ennu_hair_last_updated',
				'ennu_hair_score_calculated_at',
				'ennu_global_date_of_birth',
				'ennu_global_date_of_birth_month',
				'ennu_global_date_of_birth_day',
				'ennu_global_date_of_birth_year',
				'ennu_hair_first_name',
				'ennu_hair_last_name',
				'ennu_hair_hair_q2',
				'ennu_hair_hair_q4',
				'ennu_hair_hair_q5',
				'ennu_hair_hair_q6',
				'ennu_hair_hair_q7',
				'ennu_hair_hair_q8',
				'ennu_hair_hair_q9',
				'ennu_hair_hair_q10',
				
				'ennu_weight-loss_completed',
				'ennu_weight-loss_calculated_score',
				'ennu_weight-loss_score_interpretation',
				'ennu_weight-loss_category_scores',
				'ennu_weight-loss_pillar_scores',
				'ennu_weight-loss_auto_submit_ready',
				'ennu_weight-loss_assessment_type',
				'ennu_weight-loss_last_updated',
				'ennu_weight-loss_score_calculated_at',
				'ennu_weight-loss_first_name',
				'ennu_weight-loss_last_name',
				'ennu_weight-loss_billing_phone',
				'ennu_global_gender',
				'ennu_global_date_of_birth_month',
				'ennu_global_date_of_birth_day',
				'ennu_global_date_of_birth_year',
				'ennu_global_date_of_birth',
				'ennu_global_height_weight_ft',
				'ennu_global_height_weight_in',
				'ennu_global_height_weight_lbs',
				
				'ennu_health-optimization_completed',
				'ennu_health-optimization_calculated_score',
				'ennu_health-optimization_score_interpretation',
				'ennu_health-optimization_category_scores',
				'ennu_health-optimization_pillar_scores',
				'ennu_health-optimization_auto_submit_ready',
				'ennu_health-optimization_assessment_type',
				'ennu_health-optimization_last_updated',
				'ennu_health-optimization_score_calculated_at',
				
				// Other assessment types
				'ennu_ed-treatment_completed',
				'ennu_testosterone_completed',
				'ennu_health_completed',
				'ennu_hormone_completed',
				'ennu_skin_completed',
				'ennu_sleep_completed',
				'ennu_menopause_completed',
				
				// Centralized symptoms
				'ennu_centralized_symptoms',
				
				// User scores and metrics
				'ennu_average_pillar_scores',
				'ennu_completed_assessments_count',
				'ennu_health_metrics_updated_at',
				
				// BMI and health data
				'ennu_calculated_bmi',
				'ennu_bmi_history',
				
				// Assessment lists
				'ennu_assessments',
				
				// Historical scores for all assessment types
				'ennu_hair_historical_scores',
				'ennu_weight-loss_historical_scores',
				'ennu_health-optimization_historical_scores',
				'ennu_ed-treatment_historical_scores',
				'ennu_testosterone_historical_scores',
				'ennu_health_historical_scores',
				'ennu_hormone_historical_scores',
				'ennu_skin_historical_scores',
				'ennu_sleep_historical_scores',
				'ennu_menopause_historical_scores',
				
				// Biomarkers
				'ennu_user_biomarkers'
			);

			// Delete each meta field
			foreach ( $ennu_meta_keys as $meta_key ) {
				$result = delete_user_meta( $user_id, $meta_key );
				if ( $result ) {
					$deleted_count++;
				} else {
					$errors[] = "Failed to delete {$meta_key}";
				}
			}

			// 2. Delete any other ENNU meta fields (catch-all)
			global $wpdb;
			$additional_ennu_meta = $wpdb->get_col( $wpdb->prepare(
				"SELECT meta_key FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE 'ennu_%'",
				$user_id
			) );

			foreach ( $additional_ennu_meta as $meta_key ) {
				$result = delete_user_meta( $user_id, $meta_key );
				if ( $result ) {
					$deleted_count++;
				} else {
					$errors[] = "Failed to delete {$meta_key}";
				}
			}

			// 3. Delete transient data
			$transient_keys = array(
				'ennu_results_' . $user_id,
				'ennu_assessment_' . $user_id,
				'ennu_scores_' . $user_id
			);

			foreach ( $transient_keys as $transient_key ) {
				$result = delete_transient( $transient_key );
				if ( $result ) {
					$deleted_count++;
				}
			}

			// 4. Delete any other transients for this user
			$user_transients = $wpdb->get_col( $wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_ennu_%' AND option_value LIKE %s",
				'%user_id":' . $user_id . '%'
			) );

			foreach ( $user_transients as $transient_name ) {
				$transient_key = str_replace( '_transient_', '', $transient_name );
				$result = delete_transient( $transient_key );
				if ( $result ) {
					$deleted_count++;
				}
			}

			// 5. Clear any cached data
			wp_cache_delete( $user_id, 'user_meta' );

			error_log( "ENNU Enhanced Admin: ajax_delete_ennu_user_data completed for user {$user_id}. Deleted: {$deleted_count} items. Errors: " . count( $errors ) );

			$response_data = array(
				'success' => true,
				'message' => "Successfully deleted {$deleted_count} ENNU data items",
				'deleted_count' => $deleted_count,
				'errors' => $errors,
				'user_id' => $user_id,
				'timestamp' => current_time( 'mysql' )
			);

			wp_send_json_success( $response_data );

		} catch ( Exception $e ) {
			error_log( 'ENNU Enhanced Admin: Exception in ajax_delete_ennu_user_data: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => 'An error occurred while deleting user data: ' . $e->getMessage() ) );
		}
	}



	/**
	 * Debug function to check page creation and mapping status
	 */
	public function debug_page_mappings() {
		$settings = $this->get_plugin_settings();
		$page_mappings = $settings['page_mappings'];
		
		echo '<div class="notice notice-info">';
		echo '<h3>Debug Information:</h3>';
		echo '<p><strong>Current Page Mappings:</strong></p>';
		echo '<pre>' . print_r( $page_mappings, true ) . '</pre>';
		
		// Check which pages actually exist
		$pages = get_pages( array(
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		echo '<p><strong>Published Pages:</strong></p>';
		foreach ( $pages as $page ) {
			echo '<p>ID: ' . $page->ID . ' | Title: ' . $page->post_title . ' | Content: ' . substr( $page->post_content, 0, 100 ) . '...</p>';
		}
		
		// Check auto-select pages
		$auto_select_pages = get_option( 'ennu_auto_select_pages', array() );
		echo '<p><strong>Auto-Select Pages:</strong></p>';
		echo '<pre>' . print_r( $auto_select_pages, true ) . '</pre>';
		
		echo '</div>';
	}

	/**
	 * AJAX handler for page detection and creation
	 */
	public function ajax_detect_pages_submit() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_detect_pages' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ) );
			return;
		}

		// Run the auto-detection
		$result = $this->auto_detect_existing_pages();
		
		if ( $result ) {
			wp_send_json_success( array(
				'message' => 'Pages detected and created successfully!',
				'result' => $result
			) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to detect or create pages.' ) );
		}
	}
	
	/**
	 * AJAX handler for removing biomarker flags
	 */
	public function ajax_remove_biomarker_flag() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_biomarker_flag' ) ) {
			wp_send_json_error( 'Security check failed.' );
			return;
		}
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions.' );
			return;
		}
		
		$user_id = intval( $_POST['user_id'] );
		$biomarker = sanitize_text_field( $_POST['biomarker'] );
		
		if ( empty( $user_id ) || empty( $biomarker ) ) {
			wp_send_json_error( 'Missing required parameters.' );
			return;
		}
		
		// Remove the flag using the biomarker flag manager
		if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
			try {
				$flag_manager = new ENNU_Biomarker_Flag_Manager();
				$result = $flag_manager->remove_flag( $user_id, $biomarker, 'Admin removal', get_current_user_id() );
				if ( $result ) {
					wp_send_json_success( 'Flag removed successfully.' );
				} else {
					wp_send_json_error( 'Failed to remove flag.' );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( 'Error: ' . $e->getMessage() );
			}
		} else {
			wp_send_json_error( 'Biomarker flag manager not available.' );
		}
	}
	
	/**
	 * Display raw assessment data for assessments without config files
	 */
	private function display_raw_assessment_data( $user_id, $assessment_type ) {
		$all_meta = get_user_meta( $user_id );
		$assessment_fields = array();
		
		// Find all fields for this assessment type
		foreach ( $all_meta as $meta_key => $meta_value ) {
			if ( strpos( $meta_key, 'ennu_' . $assessment_type . '_' ) === 0 ) {
				$assessment_fields[ $meta_key ] = $meta_value[0];
			}
		}
		
		if ( empty( $assessment_fields ) ) {
			echo '<p><em>No data found for this assessment.</em></p>';
			return;
		}
		
		echo '<table class="form-table ennu-questions-table">';
		foreach ( $assessment_fields as $field_key => $field_value ) {
			// Skip empty values
			if ( empty( $field_value ) && $field_value !== '0' && $field_value !== 0 ) {
				continue;
			}
			
			// Create a readable label from field key
			$label = str_replace( 'ennu_' . $assessment_type . '_', '', $field_key );
			$label = ucwords( str_replace( '_', ' ', $label ) );
			
			echo '<tr>';
			echo '<th scope="row" style="width: 30%; vertical-align: top;">';
			echo '<strong>' . esc_html( $label ) . '</strong>';
			echo '</th>';
			echo '<td style="width: 70%;">';
			
			// Format the value
			if ( is_array( $field_value ) ) {
				echo '<pre>' . esc_html( json_encode( $field_value, JSON_PRETTY_PRINT ) ) . '</pre>';
			} elseif ( is_serialized( $field_value ) ) {
				$unserialized = maybe_unserialize( $field_value );
				if ( is_array( $unserialized ) ) {
					echo '<pre>' . esc_html( json_encode( $unserialized, JSON_PRETTY_PRINT ) ) . '</pre>';
				} else {
					echo esc_html( $field_value );
				}
			} else {
				echo esc_html( $field_value );
			}
			
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	/**
	 * Display health goals summary
	 */
	private function display_health_goals_summary( $user_id ) {
		$health_goals = array();
		
		// Check various possible health goal fields
		$goal_fields = array(
			'ennu_global_health_goals',
			'ennu_health_goals',
			'health_goals'
		);
		
		foreach ( $goal_fields as $field ) {
			$value = get_user_meta( $user_id, $field, true );
			if ( ! empty( $value ) ) {
				if ( is_array( $value ) ) {
					$health_goals = array_merge( $health_goals, $value );
				} else {
					$health_goals[] = $value;
				}
			}
		}
		
		// Also check assessment-specific global health goals
		$all_meta = get_user_meta( $user_id );
		foreach ( $all_meta as $key => $value ) {
			if ( strpos( $key, 'health_goal' ) !== false && ! empty( $value[0] ) ) {
				if ( is_array( $value[0] ) ) {
					$health_goals = array_merge( $health_goals, $value[0] );
				} else {
					$health_goals[] = $value[0];
				}
			}
		}
		
		if ( ! empty( $health_goals ) ) {
			// Filter out non-string values
			$filtered_goals = array();
			foreach ( $health_goals as $goal ) {
				if ( is_string( $goal ) && ! empty( $goal ) ) {
					$filtered_goals[] = $goal;
				}
			}
			
			if ( ! empty( $filtered_goals ) ) {
				$filtered_goals = array_unique( $filtered_goals );
				foreach ( $filtered_goals as $goal ) {
					echo '<div style="background: #fff3cd; padding: 5px 8px; margin: 2px 0; border-radius: 3px; font-size: 12px;">';
					echo esc_html( ucwords( str_replace( '_', ' ', $goal ) ) );
					echo '</div>';
				}
			} else {
				echo '<div style="color: #666; font-style: italic; font-size: 12px;">No health goals specified</div>';
			}
		} else {
			echo '<div style="color: #666; font-style: italic; font-size: 12px;">No health goals specified</div>';
		}
	}
	
	/**
	 * Display symptoms summary
	 */
	private function display_symptoms_summary( $user_id ) {
		$all_symptoms = array();
		$all_meta = get_user_meta( $user_id );
		
		// Look for symptom-related fields in assessments
		foreach ( $all_meta as $key => $value ) {
			$val = is_array( $value ) ? $value[0] : $value;
			
			// Check for symptom fields or questions that might contain symptoms
			if ( ( strpos( $key, 'symptom' ) !== false || 
			      strpos( $key, '_q1' ) !== false || // Often symptom questions
			      strpos( $key, '_q9' ) !== false ) && // Weight-loss symptoms
			     ! empty( $val ) ) {
				
				if ( is_serialized( $val ) ) {
					$symptoms = unserialize( $val );
					if ( is_array( $symptoms ) ) {
						$all_symptoms = array_merge( $all_symptoms, $symptoms );
					}
				} elseif ( is_array( $val ) ) {
					$all_symptoms = array_merge( $all_symptoms, $val );
				} elseif ( strpos( $val, '_' ) !== false ) {
					// Likely symptom codes
					$all_symptoms[] = $val;
				}
			}
		}
		
		if ( ! empty( $all_symptoms ) ) {
			// Filter out non-string values and flatten any nested arrays
			$filtered_symptoms = array();
			foreach ( $all_symptoms as $symptom ) {
				if ( is_string( $symptom ) && ! empty( $symptom ) ) {
					$filtered_symptoms[] = $symptom;
				} elseif ( is_array( $symptom ) ) {
					foreach ( $symptom as $nested_symptom ) {
						if ( is_string( $nested_symptom ) && ! empty( $nested_symptom ) ) {
							$filtered_symptoms[] = $nested_symptom;
						}
					}
				}
			}
			
			if ( ! empty( $filtered_symptoms ) ) {
				$filtered_symptoms = array_unique( $filtered_symptoms );
				$symptom_count = 0;
				foreach ( $filtered_symptoms as $symptom ) {
					if ( $symptom_count >= 5 ) {
						echo '<div style="color: #666; font-size: 11px;">... and ' . ( count( $filtered_symptoms ) - 5 ) . ' more</div>';
						break;
					}
					echo '<div style="background: #f8d7da; padding: 4px 6px; margin: 2px 0; border-radius: 3px; font-size: 11px;">';
					echo esc_html( ucwords( str_replace( '_', ' ', $symptom ) ) );
					echo '</div>';
					$symptom_count++;
				}
			} else {
				echo '<div style="color: #666; font-style: italic; font-size: 12px;">No symptoms reported</div>';
			}
		} else {
			echo '<div style="color: #666; font-style: italic; font-size: 12px;">No symptoms reported</div>';
		}
	}
	
	/**
	 * Display biomarkers summary
	 */
	private function display_biomarkers_summary( $user_id ) {
		$biomarkers = array();
		
		// Try to get biomarkers using the service
		if ( class_exists( 'ENNU_Biomarker_Service' ) ) {
			try {
				$service = new ENNU_Biomarker_Service();
				if ( method_exists( $service, 'get_user_biomarkers' ) ) {
					$biomarkers = $service->get_user_biomarkers( $user_id );
				}
			} catch ( Exception $e ) {
				// Continue with alternative methods
			}
		}
		
		// Try static method if service didn't work
		if ( empty( $biomarkers ) && class_exists( 'ENNU_Biomarker_Manager' ) ) {
			try {
				if ( method_exists( 'ENNU_Biomarker_Manager', 'get_user_biomarkers' ) ) {
					$biomarkers = ENNU_Biomarker_Manager::get_user_biomarkers( $user_id );
				}
			} catch ( Exception $e ) {
				// Continue with manual approach
			}
		}
		
		// Manual approach - check user meta for biomarker fields
		if ( empty( $biomarkers ) ) {
			$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
			if ( ! empty( $biomarker_data ) && is_array( $biomarker_data ) ) {
				$biomarkers = $biomarker_data;
			}
		}
		
		if ( ! empty( $biomarkers ) && is_array( $biomarkers ) ) {
			$biomarker_count = 0;
			foreach ( $biomarkers as $name => $data ) {
				if ( $biomarker_count >= 4 ) {
					echo '<div style="color: #666; font-size: 11px;">... and ' . ( count( $biomarkers ) - 4 ) . ' more</div>';
					break;
				}
				
				$value = '';
				$unit = '';
				if ( is_array( $data ) ) {
					$value = $data['value'] ?? '';
					$unit = $data['unit'] ?? '';
				}
				
				echo '<div style="background: #d1ecf1; padding: 4px 6px; margin: 2px 0; border-radius: 3px; font-size: 11px;">';
				echo '<strong>' . esc_html( ucwords( str_replace( '_', ' ', $name ) ) ) . ':</strong> ';
				echo esc_html( $value . ' ' . $unit );
				echo '</div>';
				$biomarker_count++;
			}
		} else {
			echo '<div style="color: #666; font-style: italic; font-size: 12px;">No biomarker data</div>';
		}
	}
	
	/**
	 * Display assessment-specific pillar scores
	 */
	private function display_assessment_pillar_scores( $user_id, $assessment_type ) {
		$score_breakdown = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_score_breakdown', true );
		
		if ( ! empty( $score_breakdown ) ) {
			if ( is_serialized( $score_breakdown ) ) {
				$score_breakdown = unserialize( $score_breakdown );
			}
			
			if ( is_array( $score_breakdown ) && isset( $score_breakdown['pillar_scores'] ) ) {
				$pillar_scores = $score_breakdown['pillar_scores'];
				
				if ( ! empty( $pillar_scores ) && is_array( $pillar_scores ) ) {
					echo '<div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px;">';
					foreach ( $pillar_scores as $pillar => $score ) {
						echo '<div style="background: #e7e3ff; padding: 4px 8px; border-radius: 3px; font-size: 12px;">';
						echo '<strong>' . esc_html( ucwords( str_replace( '_', ' ', $pillar ) ) ) . ':</strong> ';
						echo '<span style="color: #6f42c1; font-weight: bold;">' . number_format( $score, 1 ) . '</span>';
						echo '</div>';
					}
					echo '</div>';
				} else {
					echo '<div style="color: #666; font-style: italic; font-size: 12px;">No pillar scores for this assessment</div>';
				}
			} else {
				echo '<div style="color: #666; font-style: italic; font-size: 12px;">Score breakdown not available</div>';
			}
		} else {
			echo '<div style="color: #666; font-style: italic; font-size: 12px;">No scoring data available</div>';
		}
	}
	
	/**
	 * Display Health Goals tab with all available options
	 */
	private function display_health_goals_tab( $user_id ) {
		// Standard health goals options
		$health_goal_options = array(
			'longevity' => 'Longevity & Healthy Aging',
			'energy' => 'Improve Energy & Vitality',
			'strength' => 'Build Strength & Muscle',
			'libido' => 'Enhance Libido & Sexual Health',
			'weight_loss' => 'Achieve & Maintain Healthy Weight',
			'hormonal_balance' => 'Hormonal Balance',
			'cognitive_health' => 'Sharpen Cognitive Function',
			'heart_health' => 'Support Heart Health',
			'aesthetics' => 'Improve Hair, Skin & Nails',
			'sleep' => 'Improve Sleep Quality',
			'stress' => 'Reduce Stress & Improve Resilience'
		);
		
		// Get user's selected health goals from various sources
		$user_selected_goals = array();
		$all_meta = get_user_meta( $user_id );
		
		foreach ( $all_meta as $key => $value ) {
			if ( strpos( $key, 'health_goal' ) !== false || strpos( $key, '_goals' ) !== false ) {
				$val = is_array( $value ) ? $value[0] : $value;
				if ( ! empty( $val ) ) {
					if ( is_serialized( $val ) ) {
						$goals = unserialize( $val );
						if ( is_array( $goals ) ) {
							$user_selected_goals = array_merge( $user_selected_goals, $goals );
						}
					} elseif ( is_array( $val ) ) {
						$user_selected_goals = array_merge( $user_selected_goals, $val );
					} else {
						$user_selected_goals[] = $val;
					}
				}
			}
		}
		
		$user_selected_goals = array_unique( $user_selected_goals );
		
		echo '<div class="ennu-options-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 20px 0;">';
		
		foreach ( $health_goal_options as $goal_key => $goal_label ) {
			$is_selected = in_array( $goal_key, $user_selected_goals );
			$style = $is_selected ? 
				'background: #e7f3ff; border: 2px solid #0073aa; padding: 15px; border-radius: 5px;' : 
				'background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px;';
			
			echo '<div style="' . $style . '">';
			if ( $is_selected ) {
				echo '<div style="color: #0073aa; font-weight: bold; margin-bottom: 5px;">✓ SELECTED</div>';
			}
			echo '<div style="font-weight: bold; margin-bottom: 5px;">' . esc_html( $goal_label ) . '</div>';
			echo '<div style="font-size: 12px; color: #666;">Field ID: ' . esc_html( $goal_key ) . '</div>';
			echo '</div>';
		}
		
		echo '</div>';
		
		// Show raw data if any
		if ( ! empty( $user_selected_goals ) ) {
			echo '<div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">';
			echo '<strong>Raw Selected Goals Data:</strong><br>';
			foreach ( $user_selected_goals as $goal ) {
				echo '• ' . esc_html( $goal ) . '<br>';
			}
			echo '</div>';
		}
	}
	
	/**
	 * Display Symptoms tab with all available options organized by category
	 */
	private function display_symptoms_tab( $user_id ) {
		// Organize symptoms by category
		$symptom_categories = array(
			'Hormonal Symptoms' => array(
				'fatigue' => 'Unexplained fatigue or energy loss',
				'mood_swings' => 'Mood swings, irritability, or anxiety',
				'weight_gain' => 'Unexplained weight gain',
				'low_libido' => 'Low libido or sexual dysfunction',
				'sleep_issues' => 'Difficulty sleeping or night sweats',
				'brain_fog' => 'Brain fog or difficulty concentrating',
				'hot_flashes' => 'Hot flashes or temperature sensitivity',
				'hair_loss' => 'Hair loss or thinning',
				'skin_changes' => 'Skin changes (dryness, acne, aging)',
				'heart_palpitations' => 'Heart palpitations or irregular heartbeat',
				'digestive_issues' => 'Digestive issues or bloating',
				'joint_pain' => 'Joint pain or muscle weakness',
				'memory_problems' => 'Memory problems or forgetfulness',
				'depression' => 'Depression or low mood'
			),
			'Cardiovascular Symptoms' => array(
				'chest_pain' => 'Chest Pain or Discomfort',
				'shortness_breath' => 'Shortness of Breath',
				'palpitations' => 'Heart Palpitations or Irregular Heartbeat',
				'lightheadedness' => 'Lightheadedness or Dizziness',
				'swelling' => 'Swelling in Legs, Ankles, or Feet',
				'poor_exercise_tolerance' => 'Poor Exercise Tolerance',
				'nausea' => 'Nausea or Indigestion',
				'sweating' => 'Cold Sweats'
			),
			'Metabolic Symptoms' => array(
				'thirst' => 'Increased Thirst or Frequent Urination',
				'slow_healing' => 'Slow Wound Healing',
				'blurred_vision' => 'Blurred Vision',
				'numbness' => 'Numbness or Tingling in Extremities'
			),
			'Cognitive Symptoms' => array(
				'memory_loss' => 'Memory Loss or Forgetfulness',
				'poor_concentration' => 'Poor Concentration or Focus',
				'mental_fatigue' => 'Mental Fatigue'
			),
			'Immune Symptoms' => array(
				'frequent_illness' => 'Frequent Illness or Infections',
				'slow_recovery' => 'Slow Recovery from Illness',
				'allergies' => 'Allergies or Sensitivities',
				'inflammation' => 'Chronic Inflammation or Pain',
				'autoimmune' => 'Autoimmune Symptoms'
			)
		);
		
		// Get user's reported symptoms
		$user_symptoms = array();
		$all_meta = get_user_meta( $user_id );
		
		foreach ( $all_meta as $key => $value ) {
			$val = is_array( $value ) ? $value[0] : $value;
			
			if ( ( strpos( $key, 'symptom' ) !== false || 
			      strpos( $key, '_q1' ) !== false ) && 
			     ! empty( $val ) ) {
				
				if ( is_serialized( $val ) ) {
					$symptoms = unserialize( $val );
					if ( is_array( $symptoms ) ) {
						$user_symptoms = array_merge( $user_symptoms, $symptoms );
					}
				} elseif ( is_array( $val ) ) {
					$user_symptoms = array_merge( $user_symptoms, $val );
				} else {
					$user_symptoms[] = $val;
				}
			}
		}
		
		// Flatten any nested arrays and ensure all elements are strings
		$flattened_symptoms = array();
		foreach ( $user_symptoms as $symptom ) {
			if ( is_array( $symptom ) ) {
				// If it's an array, merge its values
				$flattened_symptoms = array_merge( $flattened_symptoms, $symptom );
			} else {
				// If it's a string, add it directly
				$flattened_symptoms[] = $symptom;
			}
		}
		$user_symptoms = array_unique( array_filter( $flattened_symptoms ) );
		
		foreach ( $symptom_categories as $category_name => $symptoms ) {
			echo '<div style="margin-bottom: 30px;">';
			echo '<h5 style="color: #dc3545; border-bottom: 2px solid #dc3545; padding-bottom: 5px;">' . esc_html( $category_name ) . '</h5>';
			echo '<div class="ennu-symptoms-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 10px; margin: 15px 0;">';
			
			foreach ( $symptoms as $symptom_key => $symptom_label ) {
				$is_selected = in_array( $symptom_key, $user_symptoms );
				$style = $is_selected ? 
					'background: #f8d7da; border: 2px solid #dc3545; padding: 10px; border-radius: 3px;' : 
					'background: #f9f9f9; border: 1px solid #ddd; padding: 10px; border-radius: 3px;';
				
				echo '<div style="' . $style . '">';
				if ( $is_selected ) {
					echo '<div style="color: #dc3545; font-weight: bold; font-size: 12px;">⚠️ REPORTED</div>';
				}
				echo '<div style="font-size: 13px; margin: 3px 0;">' . esc_html( $symptom_label ) . '</div>';
				echo '<div style="font-size: 11px; color: #666;">ID: ' . esc_html( $symptom_key ) . '</div>';
				echo '</div>';
			}
			
			echo '</div></div>';
		}
		
		// Show additional symptoms found
		if ( ! empty( $user_symptoms ) ) {
			echo '<div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px;">';
			echo '<strong>All Reported Symptoms:</strong><br>';
			foreach ( $user_symptoms as $symptom ) {
				// Safety check: ensure $symptom is a string
				if ( is_string( $symptom ) && ! empty( $symptom ) ) {
					echo '• ' . esc_html( ucwords( str_replace( '_', ' ', $symptom ) ) ) . '<br>';
				}
			}
			echo '</div>';
		}
	}
	
	/**
	 * Display Biomarkers tab with all available biomarkers and admin controls
	 */
	private function display_biomarkers_tab( $user_id ) {
		// Load complete biomarker map from config
		$biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';
		$biomarker_categories = array();
		
		if ( file_exists( $biomarker_map_file ) ) {
			$biomarker_map = require $biomarker_map_file;
			$biomarker_labels = $this->get_biomarker_labels();
			
			// Convert biomarker map to display format with proper labels
			foreach ( $biomarker_map as $category_name => $biomarkers ) {
				$category_biomarkers = array();
				foreach ( $biomarkers as $biomarker_key ) {
					$label = isset( $biomarker_labels[ $biomarker_key ] ) 
						? $biomarker_labels[ $biomarker_key ] 
						: ucfirst( str_replace( '_', ' ', $biomarker_key ) );
					$category_biomarkers[ $biomarker_key ] = $label;
				}
				$biomarker_categories[ $category_name ] = $category_biomarkers;
			}
		} else {
			// Fallback to original categories if config file doesn't exist
			$biomarker_categories = array(
				'Hormones' => array(
					'testosterone' => 'Testosterone',
					'estradiol' => 'Estradiol',
					'cortisol' => 'Cortisol',
					'tsh' => 'TSH (Thyroid)',
					't3' => 'T3 (Triiodothyronine)',
					't4' => 'T4 (Thyroxine)'
				),
				'Metabolic' => array(
					'glucose' => 'Glucose',
					'insulin' => 'Insulin',
					'hba1c' => 'HbA1c'
				),
				'Lipids' => array(
					'cholesterol' => 'Total Cholesterol',
					'triglycerides' => 'Triglycerides',
					'ldl' => 'LDL (Bad Cholesterol)',
					'hdl' => 'HDL (Good Cholesterol)'
				),
				'Cardiovascular' => array(
					'blood_pressure' => 'Blood Pressure',
					'heart_rate' => 'Heart Rate'
				),
				'Blood Panel' => array(
					'wbc' => 'White Blood Cells',
					'rbc' => 'Red Blood Cells',
					'hemoglobin' => 'Hemoglobin',
					'platelets' => 'Platelets'
				)
			);
		}
		
		// Get user's biomarker data
		$user_biomarkers = array();
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! empty( $biomarker_data ) && is_array( $biomarker_data ) ) {
			$user_biomarkers = $biomarker_data;
		}
		
		// Get biomarker flags with enhanced information
		$flagged_biomarkers = array();
		if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
			try {
				$flag_manager = new ENNU_Biomarker_Flag_Manager();
				
				// Get all flagged biomarkers
				$all_flagged = $flag_manager->get_flagged_biomarkers( $user_id, 'active' );
				
				foreach ( $all_flagged as $biomarker_name => $flag_data ) {
					$flagged_biomarkers[ $biomarker_name ] = $flag_data;
				}
			} catch ( Exception $exception ) {
				error_log( 'ENNU Admin: Error getting biomarker flags: ' . $exception->getMessage() );
			}
		}
		
		// Display all biomarker categories
		foreach ( $biomarker_categories as $category_name => $biomarkers ) {
			echo '<div style="margin-bottom: 30px;">';
			echo '<h5 style="color: #20c997; border-bottom: 2px solid #20c997; padding-bottom: 5px;">' . esc_html( $category_name ) . '</h5>';
			echo '<div class="ennu-biomarkers-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 15px 0;">';
			
			foreach ( $biomarkers as $biomarker_key => $biomarker_label ) {
				$has_data = isset( $user_biomarkers[ $biomarker_key ] );
				$is_flagged = isset( $flagged_biomarkers[ $biomarker_key ] );
				
				$style = 'padding: 15px; border-radius: 5px; ';
				if ( $is_flagged ) {
					$style .= 'background: #f8d7da; border: 2px solid #dc3545;';
				} elseif ( $has_data ) {
					$style .= 'background: #d1ecf1; border: 2px solid #20c997;';
				} else {
					$style .= 'background: #f9f9f9; border: 1px solid #ddd;';
				}
				
				echo '<div style="' . $style . '">';
				echo '<div style="display: flex; justify-content: between; align-items: start; margin-bottom: 10px;">';
				echo '<div style="flex-grow: 1;">';
				echo '<div style="font-weight: bold; margin-bottom: 5px;">' . esc_html( $biomarker_label ) . '</div>';
				echo '<div style="font-size: 12px; color: #666;">ID: ' . esc_html( $biomarker_key ) . '</div>';
				echo '</div>';
				
				// Flag management controls for admins
				if ( current_user_can( 'manage_options' ) && $is_flagged ) {
					echo '<div style="margin-left: 10px;">';
					echo '<button type="button" class="button button-small" onclick="removeFlag(\'' . esc_attr( $user_id ) . '\', \'' . esc_attr( $biomarker_key ) . '\')" style="background: #dc3545; color: white; border: none;">Remove Flag</button>';
					echo '</div>';
				}
				echo '</div>';
				
				// Enhanced flag display with assessment answers
				if ( $is_flagged ) {
					$flag_data = $flagged_biomarkers[ $biomarker_key ];
					echo '<div style="color: #dc3545; font-weight: bold; margin-bottom: 5px;">🚩 FLAGGED</div>';
					
					// Show reason
					if ( isset( $flag_data['reason'] ) ) {
						echo '<div style="font-size: 12px; color: #dc3545; margin-bottom: 8px;"><strong>Reason:</strong> ' . esc_html( $flag_data['reason'] ) . '</div>';
					}
					
					// Show assessment source and answers that triggered the flag
					if ( isset( $flag_data['assessment_source'] ) && ! empty( $flag_data['assessment_source'] ) ) {
						echo '<div style="font-size: 12px; color: #dc3545; margin-bottom: 8px;"><strong>Triggered by:</strong> ' . esc_html( $this->get_assessment_display_name( $flag_data['assessment_source'] ) ) . '</div>';
						
						// Show the specific answers that triggered this flag
						$trigger_answers = $this->get_assessment_answers_for_flag( $user_id, $flag_data );
						if ( ! empty( $trigger_answers ) ) {
							echo '<div style="font-size: 11px; color: #dc3545; margin-bottom: 8px;"><strong>Triggering Answers:</strong></div>';
							echo '<div style="font-size: 11px; color: #dc3545; margin-left: 10px; margin-bottom: 8px;">';
							foreach ( $trigger_answers as $answer ) {
								echo '<div>• ' . esc_html( $answer ) . '</div>';
							}
							echo '</div>';
						}
					}
					
					// Show multiple assessments if applicable
					if ( isset( $flag_data['multiple_assessments'] ) && $flag_data['multiple_assessments'] ) {
						echo '<div style="font-size: 11px; color: #dc3545; margin-bottom: 8px;"><strong>Also flagged by:</strong> ' . esc_html( implode( ', ', $flag_data['all_assessments'] ) ) . '</div>';
					}
					
					// Show flag date
					if ( isset( $flag_data['flagged_at'] ) ) {
						echo '<div style="font-size: 11px; color: #666;">Flagged: ' . esc_html( date( 'M j, Y', strtotime( $flag_data['flagged_at'] ) ) ) . '</div>';
					}
				}
				
				if ( $has_data ) {
					$data = $user_biomarkers[ $biomarker_key ];
					echo '<div style="color: #20c997; font-weight: bold; margin-bottom: 5px;">📊 HAS DATA</div>';
					if ( isset( $data['value'] ) ) {
						echo '<div style="font-size: 13px;"><strong>Value:</strong> ' . esc_html( $data['value'] );
						if ( isset( $data['unit'] ) ) {
							echo ' ' . esc_html( $data['unit'] );
						}
						echo '</div>';
					}
					if ( isset( $data['test_date'] ) ) {
						echo '<div style="font-size: 12px; color: #666;">Date: ' . esc_html( $data['test_date'] ) . '</div>';
					}
				} else {
					echo '<div style="color: #999; font-style: italic; font-size: 12px;">No data available</div>';
				}
				
				echo '</div>';
			}
			
			echo '</div></div>';
		}
		
		// Add JavaScript for flag removal
		if ( current_user_can( 'manage_options' ) ) {
			echo '<script>
			function removeFlag(userId, biomarkerKey) {
				if (confirm("Are you sure you want to remove the flag for " + biomarkerKey + "?")) {
					fetch(ajaxurl, {
						method: "POST",
						headers: {"Content-Type": "application/x-www-form-urlencoded"},
						body: "action=ennu_remove_biomarker_flag&user_id=" + userId + "&biomarker=" + biomarkerKey + "&nonce=" + "' . wp_create_nonce( 'ennu_biomarker_flag' ) . '"
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							location.reload();
						} else {
							alert("Error removing flag: " + data.data);
						}
					});
				}
			}
			</script>';
		}
	}
	
	/**
	 * AJAX handler to clear all user ENNU data
	 */
	public function ajax_clear_all_user_data() {
		// Check nonce
		$user_id = intval($_POST['user_id']);
		if (!wp_verify_nonce($_POST['nonce'], 'ennu_clear_user_data_' . $user_id)) {
			wp_die('Security check failed');
		}
		
		// Check permissions
		if (!current_user_can('edit_user', $user_id)) {
			wp_die('Permission denied');
		}
		
		// Get all user meta
		$all_meta = get_user_meta($user_id);
		$deleted_count = 0;
		$cleared_transients = 0;
		$cleared_cache = 0;
		
		// Delete all ENNU-related user meta
		foreach ($all_meta as $meta_key => $meta_value) {
			if (strpos($meta_key, 'ennu_') === 0 || 
			    strpos($meta_key, '_ennu_') === 0 ||
			    strpos($meta_key, 'wl_q') === 0 ||
			    strpos($meta_key, 'health_q') === 0 ||
			    strpos($meta_key, 'hormone_q') === 0 ||
			    strpos($meta_key, 'skin_q') === 0 ||
			    strpos($meta_key, 'hair_q') === 0 ||
			    strpos($meta_key, 'sleep_q') === 0 ||
			    strpos($meta_key, 'ed_q') === 0 ||
			    strpos($meta_key, 'testosterone_q') === 0 ||
			    strpos($meta_key, 'menopause_q') === 0 ||
			    strpos($meta_key, 'symptom_q') === 0 ||
			    strpos($meta_key, 'welcome_q') === 0) {
				delete_user_meta($user_id, $meta_key);
				$deleted_count++;
			}
		}
		
		// Clear transients related to this user
		global $wpdb;
		$transient_keys = $wpdb->get_col($wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} 
			WHERE option_name LIKE %s 
			OR option_name LIKE %s",
			'%_transient_ennu_%' . $user_id . '%',
			'%_transient_timeout_ennu_%' . $user_id . '%'
		));
		
		foreach ($transient_keys as $transient) {
			$key = str_replace('_transient_', '', $transient);
			$key = str_replace('_transient_timeout_', '', $key);
			delete_transient($key);
			$cleared_transients++;
		}
		
		// Clear any cached scores
		wp_cache_delete($user_id, 'ennu_user_scores');
		wp_cache_delete($user_id, 'ennu_assessment_scores');
		wp_cache_delete($user_id, 'ennu_biomarker_data');
		wp_cache_delete($user_id, 'ennu_symptom_data');
		wp_cache_delete($user_id, 'user_meta');
		$cleared_cache = 5;
		
		// Clear assessment-specific caches
		$assessment_types = array('weight-loss', 'health', 'skin', 'hair', 'sleep', 
		                         'hormone', 'testosterone', 'menopause', 'ed-treatment', 
		                         'health-optimization', 'welcome');
		foreach ($assessment_types as $type) {
			wp_cache_delete('ennu_' . $type . '_' . $user_id, 'ennu_assessments');
			$cleared_cache++;
		}
		
		// Log the action
		error_log("ENNU Admin: Cleared all data for user $user_id - Deleted $deleted_count meta entries, $cleared_transients transients, $cleared_cache cache entries");
		
		wp_send_json_success(array(
			'message' => "Successfully cleared all ENNU data. Deleted $deleted_count database entries, $cleared_transients transients, and $cleared_cache cache entries."
		));
	}

	/**
	 * Get biomarker labels for display
	 *
	 * @return array Array of biomarker keys to display labels
	 */
	private function get_biomarker_labels() {
		return array(
			// Heart Health
			'blood_pressure' => 'Blood Pressure',
			'heart_rate' => 'Heart Rate',
			'cholesterol' => 'Total Cholesterol',
			'triglycerides' => 'Triglycerides',
			'hdl' => 'HDL (Good Cholesterol)',
			'ldl' => 'LDL (Bad Cholesterol)',
			'vldl' => 'VLDL Cholesterol',
			'apob' => 'Apolipoprotein B',
			'hs_crp' => 'High-Sensitivity C-Reactive Protein',
			'homocysteine' => 'Homocysteine',
			'lp_a' => 'Lipoprotein(a)',
			'omega_3_index' => 'Omega-3 Index',
			'tmao' => 'Trimethylamine N-Oxide',
			'nmr_lipoprofile' => 'NMR Lipoprotein Profile',
			'glucose' => 'Fasting Glucose',
			'hba1c' => 'Hemoglobin A1c',
			'insulin' => 'Fasting Insulin',
			'uric_acid' => 'Uric Acid',
			'one_five_ag' => '1,5-Anhydroglucitol',
			'automated_or_manual_cuff' => 'Blood Pressure Method',
			'apoe_genotype' => 'ApoE Genotype',
			'hemoglobin' => 'Hemoglobin',
			'hematocrit' => 'Hematocrit',
			'rbc' => 'Red Blood Cell Count',
			'wbc' => 'White Blood Cell Count',
			'platelets' => 'Platelet Count',
			'mch' => 'Mean Corpuscular Hemoglobin',
			'mchc' => 'Mean Corpuscular Hemoglobin Concentration',
			'mcv' => 'Mean Corpuscular Volume',
			'rdw' => 'Red Cell Distribution Width',
			
			// Cognitive Health
			'ptau_217' => 'Phosphorylated Tau 217',
			'beta_amyloid_ratio' => 'Beta-Amyloid Ratio',
			'gfap' => 'Glial Fibrillary Acidic Protein',
			'vitamin_d' => 'Vitamin D (25-OH)',
			'vitamin_b12' => 'Vitamin B12',
			'folate' => 'Folate',
			'tsh' => 'Thyroid Stimulating Hormone',
			'free_t3' => 'Free T3',
			'free_t4' => 'Free T4',
			'ferritin' => 'Ferritin',
			'coq10' => 'Coenzyme Q10',
			'heavy_metals_panel' => 'Heavy Metals Panel',
			'arsenic' => 'Arsenic',
			'lead' => 'Lead',
			'mercury' => 'Mercury',
			'genotype' => 'Genetic Profile',
			
			// Hormones
			'testosterone_free' => 'Free Testosterone',
			'testosterone_total' => 'Total Testosterone',
			'estradiol' => 'Estradiol',
			'progesterone' => 'Progesterone',
			'shbg' => 'Sex Hormone Binding Globulin',
			'cortisol' => 'Cortisol',
			't4' => 'Total T4',
			't3' => 'Total T3',
			'lh' => 'Luteinizing Hormone',
			'fsh' => 'Follicle Stimulating Hormone',
			'dhea' => 'DHEA-S',
			'prolactin' => 'Prolactin',
			
			// Weight Loss
			'fasting_insulin' => 'Fasting Insulin',
			'homa_ir' => 'HOMA-IR',
			'glycomark' => 'GlycoMark',
			'leptin' => 'Leptin',
			'ghrelin' => 'Ghrelin',
			'adiponectin' => 'Adiponectin',
			'weight' => 'Weight',
			'bmi' => 'Body Mass Index',
			'body_fat_percent' => 'Body Fat Percentage',
			'waist_measurement' => 'Waist Circumference',
			'neck_measurement' => 'Neck Circumference',
			'bioelectrical_impedance_or_caliper' => 'Body Composition Method',
			'kg' => 'Weight Unit',
			
			// Strength
			'igf_1' => 'Insulin-like Growth Factor 1',
			'creatine_kinase' => 'Creatine Kinase',
			'grip_strength' => 'Grip Strength',
			
			// Longevity
			'telomere_length' => 'Telomere Length',
			'nad' => 'NAD+',
			'tac' => 'Total Antioxidant Capacity',
			'mirna_486' => 'miRNA-486',
			'gut_microbiota_diversity' => 'Gut Microbiota Diversity',
			'il_6' => 'Interleukin-6',
			'il_18' => 'Interleukin-18',
			'gfr' => 'Glomerular Filtration Rate',
			'bun' => 'Blood Urea Nitrogen',
			'creatinine' => 'Creatinine',
			'once_lifetime' => 'Lifetime Testing',
			
			// Energy
			'temperature' => 'Body Temperature',
			'oral_or_temporal_thermometer' => 'Temperature Method',
			'alt' => 'Alanine Aminotransferase',
			'ast' => 'Aspartate Aminotransferase',
			'alkaline_phosphate' => 'Alkaline Phosphatase',
			'albumin' => 'Albumin',
			'sodium' => 'Sodium',
			'potassium' => 'Potassium',
			'chloride' => 'Chloride',
			'calcium' => 'Calcium',
			'magnesium' => 'Magnesium',
			'carbon_dioxide' => 'Carbon Dioxide',
			'protein' => 'Total Protein',
		);
	}
	
	/**
	 * Get assessment display name
	 *
	 * @param string $assessment_type Assessment type key
	 * @return string Assessment display name
	 */
	private function get_assessment_display_name( $assessment_type ) {
		$assessment_names = array(
			'hair' => 'Hair Loss Assessment',
			'weight-loss' => 'Weight Loss Assessment',
			'health' => 'General Health Assessment',
			'health-optimization' => 'Health Optimization Assessment',
			'skin' => 'Skin Health Assessment',
			'hormone' => 'Hormone Assessment',
			'testosterone' => 'Testosterone Assessment',
			'menopause' => 'Menopause Assessment',
			'sleep' => 'Sleep Quality Assessment',
			'ed-treatment' => 'Erectile Dysfunction Assessment',
			'welcome' => 'Welcome Assessment',
		);
		
		return isset( $assessment_names[ $assessment_type ] ) 
			? $assessment_names[ $assessment_type ] 
			: ucfirst( str_replace( '-', ' ', $assessment_type ) ) . ' Assessment';
	}
	
	/**
	 * Get assessment answers that triggered a flag
	 *
	 * @param int $user_id User ID
	 * @param array $flag_data Flag data
	 * @return array Array of triggering answers
	 */
	private function get_assessment_answers_for_flag( $user_id, $flag_data ) {
		$trigger_answers = array();
		
		if ( isset( $flag_data['assessment_source'] ) && ! empty( $flag_data['assessment_source'] ) ) {
			$assessment_type = $flag_data['assessment_source'];
			
			// Get assessment responses using existing patterns
			$assessment_responses = get_user_meta( $user_id, "ennu_{$assessment_type}_responses", true );
			
			if ( ! empty( $assessment_responses ) ) {
				// Map responses to symptom-triggering answers
				$symptom_map = require ENNU_LIFE_PLUGIN_PATH . 'includes/config/symptom-biomarker-correlations.php';
				
				foreach ( $assessment_responses as $question_id => $response ) {
					if ( $this->is_symptom_triggering_answer( $question_id, $response, $symptom_map ) ) {
						$trigger_answers[] = $this->format_triggering_answer( $question_id, $response );
					}
				}
			}
		}
		
		return $trigger_answers;
	}
	
	/**
	 * Check if an answer is symptom-triggering
	 *
	 * @param string $question_id Question ID
	 * @param string $response User response
	 * @param array $symptom_map Symptom mapping
	 * @return bool True if triggering
	 */
	private function is_symptom_triggering_answer( $question_id, $response, $symptom_map ) {
		// Check if this question/response combination indicates a symptom
		$symptom_indicators = array(
			'fatigue', 'tired', 'exhausted', 'low_energy',
			'anxiety', 'depression', 'mood_swings', 'irritability',
			'brain_fog', 'memory_problems', 'concentration',
			'weight_gain', 'weight_loss', 'slow_metabolism',
			'hot_flashes', 'night_sweats', 'low_libido',
			'insomnia', 'poor_sleep', 'sleep_apnea',
			'muscle_weakness', 'joint_pain', 'hair_loss',
			'digestive_issues', 'bloating', 'constipation'
		);
		
		$response_lower = strtolower( $response );
		foreach ( $symptom_indicators as $indicator ) {
			if ( strpos( $response_lower, $indicator ) !== false ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Format triggering answer for display
	 *
	 * @param string $question_id Question ID
	 * @param string $response User response
	 * @return string Formatted answer
	 */
	private function format_triggering_answer( $question_id, $response ) {
		// Convert question ID to readable format
		$question_text = ucfirst( str_replace( '_', ' ', $question_id ) );
		
		// Format the response
		$response_text = ucfirst( str_replace( '_', ' ', $response ) );
		
		return "{$question_text}: {$response_text}";
	}
}
