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
		error_log( 'ENNU Enhanced Admin: Constructor called' );
		
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 10 );
		add_action( 'admin_init', array( $this, 'initialize_csrf_protection' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		
		// User profile hooks - BOTH methods to ensure compatibility
		add_action( 'show_user_profile', array( $this, 'add_ennu_profile_section' ) );
		add_action( 'edit_user_profile', array( $this, 'add_ennu_profile_section' ) );
		add_action( 'show_user_profile', array( $this, 'show_user_assessment_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'show_user_assessment_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_user_assessment_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_assessment_fields' ) );
		
		// AJAX handlers
		add_action( 'wp_ajax_populate_symptoms', array( $this, 'ajax_populate_symptoms' ) );
		add_action( 'wp_ajax_delete_ennu_user_data', array( $this, 'ajax_delete_ennu_user_data' ) );
		
		// Automatic symptom population on assessment completion
		add_action( 'ennu_assessment_completed', array( $this, 'auto_populate_symptoms_on_assessment_completion' ), 15, 2 );
		
		error_log( 'ENNU Enhanced Admin: Constructor completed, hooks registered' );
		
		// Test method to verify the class is working
		add_action( 'admin_notices', array( $this, 'test_admin_notice' ) );
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
			'assessments/hair' => array( 'title' => 'Hair Loss Assessment', 'shortcode' => '[ennu-hair]' ),
			'assessments/ed-treatment' => array( 'title' => 'ED Treatment Assessment', 'shortcode' => '[ennu-ed-treatment]' ),
			'assessments/weight-loss' => array( 'title' => 'Weight Loss Assessment', 'shortcode' => '[ennu-weight-loss]' ),
			'assessments/health' => array( 'title' => 'Health Assessment', 'shortcode' => '[ennu-health]' ),
			'assessments/health-optimization' => array( 'title' => 'Health Optimization Assessment', 'shortcode' => '[ennu-health-optimization]' ),
			'assessments/skin' => array( 'title' => 'Skin Health Assessment', 'shortcode' => '[ennu-skin]' ),
			'assessments/hormone' => array( 'title' => 'Hormone Assessment', 'shortcode' => '[ennu-hormone]' ),
			'assessments/testosterone' => array( 'title' => 'Testosterone Assessment', 'shortcode' => '[ennu-testosterone]' ),
			'assessments/menopause' => array( 'title' => 'Menopause Assessment', 'shortcode' => '[ennu-menopause]' ),
			'assessments/sleep' => array( 'title' => 'Sleep Assessment', 'shortcode' => '[ennu-sleep]' ),
		);
		
		foreach ( $missing_pages as $path => $page_info ) {
			if ( empty( $page_mappings[ $path ] ) ) {
				$page_id = $this->create_page( $path, $page_info['title'], $page_info['shortcode'] );
				if ( $page_id ) {
					$page_mappings[ $path ] = $page_id;
					$updated = true;
					error_log( "ENNU Auto-Detect: Created missing page for '{$path}' with ID {$page_id}" );
				}
			}
		}
		
		if ( $updated ) {
			$settings['page_mappings'] = $page_mappings;
			update_option( 'ennu_life_settings', $settings );
			update_option( 'ennu_created_pages', $page_mappings );
		}
		
		// Log summary
		$mapped_count = count( array_filter( $page_mappings ) );
		$total_count = count( $missing_pages );
		error_log( "ENNU Auto-Detect: Complete - {$mapped_count}/{$total_count} pages mapped" );
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
		
		echo '<div class="ennu-page-item">';
		echo '<label for="page_mapping_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		echo '<select name="page_mappings[' . esc_attr( $key ) . ']" id="page_mapping_' . esc_attr( $key ) . '">';
		echo '<option value="">' . esc_html__( '-- Select a page --', 'ennulifeassessments' ) . '</option>';
		
		foreach ( $page_options as $page_id => $page_title ) {
			$selected = selected( $current_value, $page_id, false );
			echo '<option value="' . esc_attr( $page_id ) . '"' . $selected . '>' . esc_html( $page_title ) . '</option>';
		}
		
		echo '</select>';
		
		// Show page status
		if ( ! empty( $current_value ) ) {
			$page = get_post( $current_value );
			if ( $page ) {
				$page_url = get_permalink( $page->ID );
				echo '<div class="ennu-page-status">';
				echo '<div class="page-info">';
				echo '<strong>' . esc_html__( 'Current:', 'ennulifeassessments' ) . '</strong> ';
				echo '<a href="' . esc_url( $page_url ) . '" target="_blank">' . esc_html( $page->post_title ) . '</a>';
				echo '<br><small>' . esc_html__( 'URL:', 'ennulifeassessments' ) . ' ' . esc_url( $page_url ) . '</small>';
				echo '</div>';
				echo '</div>';
			}
		} else {
			echo '<div class="ennu-page-status">';
			echo '<div class="page-info" style="color: #e53e3e;">';
			echo '<strong>' . esc_html__( 'Not set', 'ennulifeassessments' ) . '</strong>';
			echo '<br><small>' . esc_html__( 'Expected URL:', 'ennulifeassessments' ) . ' ' . esc_html( $expected_url ) . '</small>';
			echo '</div>';
			echo '</div>';
		}
		
		echo '</div>';
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
		// Handle form submissions
		if ( isset( $_POST['ennu_setup_pages_submit'] ) && wp_verify_nonce( $_POST['ennu_setup_pages_nonce'], 'ennu_setup_pages' ) ) {
			$this->auto_detect_existing_pages();
			echo '<div class="notice notice-success"><p>✅ Pages created and mapped successfully!</p></div>';
		}
		
		if ( isset( $_POST['ennu_detect_pages_submit'] ) && wp_verify_nonce( $_POST['ennu_detect_pages_nonce'], 'ennu_detect_pages' ) ) {
			$this->auto_detect_existing_pages();
			echo '<div class="notice notice-success"><p>✅ Existing pages detected and mapped successfully!</p></div>';
		}
		
		// Auto-sync existing page mappings on page load
		$this->sync_existing_page_mappings();
		
		// Enhanced admin page with modern design and organization
		echo '<div class="wrap ennu-admin-wrapper">';

		// Page Header
		echo '<div class="ennu-admin-header">';
		echo '<h1><span class="ennu-logo">🎯</span> ENNU Life Settings</h1>';
		echo '<p class="ennu-subtitle">Manage your health assessment system configuration</p>';
		echo '</div>';

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
		if ( isset( $_POST['ennu_setup_pages_submit'] ) && isset( $_POST['ennu_setup_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_setup_pages_nonce'], 'ennu_setup_pages' ) ) {
			$this->setup_pages();
			$message = __( 'Assessment pages have been created and assigned successfully!', 'ennulifeassessments' );
		}
		if ( isset( $_POST['ennu_delete_pages_submit'] ) && isset( $_POST['ennu_delete_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_delete_pages_nonce'], 'ennu_delete_pages' ) ) {
			$this->delete_pages();
			$message = __( 'Assessment pages have been deleted successfully!', 'ennulifeassessments' );
		}
		if ( isset( $_POST['ennu_detect_pages_submit'] ) && isset( $_POST['ennu_detect_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_detect_pages_nonce'], 'ennu_detect_pages' ) ) {
			$this->auto_detect_existing_pages();
			$message = __( 'Existing pages have been detected and mapped successfully!', 'ennulifeassessments' );
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
			'assessments/hair', 'assessments/ed-treatment', 'assessments/weight-loss',
			'assessments/health', 'assessments/health-optimization', 'assessments/skin',
			'assessments/hormone', 'assessments/testosterone', 'assessments/menopause', 'assessments/sleep'
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
			'hair'                => 'hair',
			'ed-treatment'        => 'ed-treatment',
			'weight-loss'         => 'weight-loss',
			'health'              => 'health',
			'health-optimization' => 'health-optimization',
			'skin'                => 'skin',
			'hormone'             => 'hormone',
			'testosterone'        => 'testosterone',
			'menopause'           => 'menopause',
			'sleep'               => 'sleep',
		);

		// Assessment Form Pages - SIMPLE PAGE ID APPROACH
		foreach ( $assessment_keys as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$simple_key = $key . '_assessment_page_id';
			$this->render_enhanced_page_dropdown( $simple_key, $label . " Assessment Page ID", $settings['page_mappings'], $page_options, "page_id" );
		}
		echo '</div></div>';

		// Assessment Results Pages Section - SIMPLE PAGE ID APPROACH
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Results Pages</h3>';
		echo '<p>Select which page to redirect to after each assessment completion</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$simple_key = $key . '_results_page_id';
			$this->render_enhanced_page_dropdown( $simple_key, $label . " Results Page ID", $settings['page_mappings'], $page_options, "page_id" );
		}
		echo '</div></div>';

		// Assessment Details Pages Section - SIMPLE PAGE ID APPROACH
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Details Pages</h3>';
		echo '<p>Select which page to use for treatment options and detailed information</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$simple_key = $key . '_details_page_id';
			$this->render_enhanced_page_dropdown( $simple_key, $label . " Details Page ID", $settings['page_mappings'], $page_options, "page_id" );
		}
		echo '</div></div>';

		// Assessment Consultation Pages Section - SIMPLE PAGE ID APPROACH
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Consultation Pages</h3>';
		echo '<p>Select which page to use for consultation booking</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$simple_key = $key . '_consultation_page_id';
			$this->render_enhanced_page_dropdown( $simple_key, $label . " Consultation Page ID", $settings['page_mappings'], $page_options, "page_id" );
		}
		echo '</div></div>';

		echo '<p><input type="submit" name="submit" class="button button-primary" value="' . esc_attr__( 'Save Page Settings', 'ennulifeassessments' ) . '"></p>';
		echo '</form>';

		// Quick Actions Section
		echo '<div class="ennu-section" style="margin-top: 2rem; background: #f0f8ff; border-left-color: #4CAF50;">';
		echo '<h3 style="color: #2E7D32;">🚀 Quick Actions</h3>';
		echo '<p style="color: #388E3C;">Automated tools to manage your assessment system</p>';
		
		echo '<form method="post" action="" style="margin-bottom: 1rem;">';
		wp_nonce_field( 'ennu_setup_pages', 'ennu_setup_pages_nonce' );
		echo '<button type="submit" name="ennu_setup_pages_submit" class="button button-primary" style="font-size: 1.1rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%) !important;">🚀 Create Missing Assessment Pages</button>';
		echo '<p style="margin-top: 0.5rem; color: #2E7D32; font-weight: 500;">Creates any missing pages and automatically assigns them in the dropdowns above.</p>';
		echo '</form>';
		
		echo '<form method="post" action="" style="margin-bottom: 1rem;">';
		wp_nonce_field( 'ennu_detect_pages', 'ennu_detect_pages_nonce' );
		echo '<button type="submit" name="ennu_detect_pages_submit" class="button" style="font-size: 1.1rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%) !important; color: white !important;">🔍 Auto-Detect Existing Pages</button>';
		echo '<p style="margin-top: 0.5rem; color: #1976D2; font-weight: 500;">Scans for existing pages and automatically maps them to the correct fields.</p>';
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
		error_log( 'ENNU Enhanced Admin: show_user_assessment_fields called with user: ' . ( $user ? $user->ID : 'null' ) );
		
		try {
			// Enhanced error handling and validation
			if ( ! $user || ! is_object( $user ) || ! isset( $user->ID ) ) {
				error_log( 'ENNU Enhanced Admin: Invalid user object provided to show_user_assessment_fields' );
				return;
			}

			$user_id = intval( $user->ID );
			if ( $user_id <= 0 ) {
				error_log( 'ENNU Enhanced Admin: Invalid user ID: ' . $user_id );
				return;
			}

			error_log( 'ENNU Enhanced Admin: show_user_assessment_fields called for user ID: ' . $user_id );
			
			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				error_log( 'ENNU Enhanced Admin: User does not have edit_user capability' );
				return;
			}
			
			error_log( 'ENNU Enhanced Admin: User has edit_user capability, proceeding with function' );

			// Get user assessment data
			$ennu_life_score = get_user_meta( $user_id, 'ennu_life_score', true );
			$average_pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
			
			// Ensure ennu_life_score is a number
			if ( ! is_numeric( $ennu_life_score ) ) {
				$ennu_life_score = 0;
			}
			
			// Ensure average_pillar_scores is always an array
			if ( ! is_array( $average_pillar_scores ) ) {
				$average_pillar_scores = array(
					'Mind'       => 0,
					'Body'       => 0,
					'Lifestyle'  => 0,
					'Aesthetics' => 0,
				);
			}

			// Render the assessment interface
			echo '<h2>' . esc_html__( 'ENNU Life Assessment Data', 'ennulifeassessments' ) . '</h2>';
			echo '<div class="ennu-admin-assessment-data">';
			
			// Display ENNU Life Score
			echo '<div class="ennu-score-section">';
			echo '<h3>' . esc_html__( 'ENNU Life Score', 'ennulifeassessments' ) . '</h3>';
			echo '<p><strong>' . esc_html__( 'Current Score:', 'ennulifeassessments' ) . '</strong> ' . esc_html( number_format( $ennu_life_score ?? 0, 1 ) ) . '</p>';
			echo '</div>';
			
			// Display Pillar Scores
			echo '<div class="ennu-pillar-scores-section">';
			echo '<h3>' . esc_html__( 'Pillar Scores', 'ennulifeassessments' ) . '</h3>';
			echo '<ul>';
			foreach ( $average_pillar_scores as $pillar => $score ) {
				// Ensure score is numeric
				if ( ! is_numeric( $score ) ) {
					$score = 0;
				}
				echo '<li><strong>' . esc_html( $pillar ) . ':</strong> ' . esc_html( number_format( $score, 1 ) ) . '</li>';
			}
			echo '</ul>';
			echo '</div>';
			
			echo '</div>'; // Close ennu-admin-assessment-data
			
			// ORGANIZED TABS: All ENNU Plugin Fields (Complete List)
			echo '<tr><th colspan="2"><h4>' . esc_html__( 'ALL ENNU Plugin Fields (Organized by Category)', 'ennulifeassessments' ) . '</h4></th></tr>';
			echo '<tr><td colspan="2">';
			
			// Define ALL possible ENNU fields organized by tabs
			$ennu_fields_by_tab = array(
				'global' => array(
					'title' => 'Global Information',
					'fields' => array(
						'ennu_global_billing_phone',
						'ennu_global_gender',
						'ennu_global_date_of_birth',
						'ennu_global_health_goals',
						'ennu_global_height_weight',
						'ennu_global_first_name',
						'ennu_global_last_name',
						'ennu_global_email',
					)
				),
				'assessments' => array(
					'title' => 'Assessment Data',
					'fields' => array(
						'ennu_assessments',
						'ennu_life_score',
						'ennu_average_pillar_scores',
						'ennu_pillar_score_data',
						'ennu_assessment_definitions',
						'ennu_assessment_results',
						'ennu_assessment_scores',
						'ennu_assessment_responses',
						'ennu_assessment_submission_date',
						'ennu_assessment_completion_status',
					)
				),
				'weight_loss' => array(
					'title' => 'Weight Loss Assessment',
					'fields' => array(
						'ennu_weight_loss_overall_score',
						'ennu_weight_loss_first_name',
						'ennu_weight_loss_last_name',
						'ennu_weight_loss_email',
						'ennu_weight_loss_responses',
						'ennu_weight_loss_submitted_at',
						'ennu_weight_loss_score',
					)
				),
				'hair' => array(
					'title' => 'Hair Assessment',
					'fields' => array(
						'ennu_hair_overall_score',
						'ennu_hair_first_name',
						'ennu_hair_last_name',
						'ennu_hair_email',
						'ennu_hair_responses',
						'ennu_hair_submitted_at',
						'ennu_hair_score',
					)
				),
				'ed_treatment' => array(
					'title' => 'ED Treatment Assessment',
					'fields' => array(
						'ennu_ed_treatment_overall_score',
						'ennu_ed_treatment_first_name',
						'ennu_ed_treatment_last_name',
						'ennu_ed_treatment_email',
						'ennu_ed_treatment_responses',
						'ennu_ed_treatment_submitted_at',
						'ennu_ed_treatment_score',
					)
				),
				'testosterone' => array(
					'title' => 'Testosterone Assessment',
					'fields' => array(
						'ennu_testosterone_overall_score',
						'ennu_testosterone_first_name',
						'ennu_testosterone_last_name',
						'ennu_testosterone_email',
						'ennu_testosterone_responses',
						'ennu_testosterone_submitted_at',
						'ennu_testosterone_score',
					)
				),
				'health' => array(
					'title' => 'Health Assessment',
					'fields' => array(
						'ennu_health_overall_score',
						'ennu_health_first_name',
						'ennu_health_last_name',
						'ennu_health_email',
						'ennu_health_responses',
						'ennu_health_submitted_at',
						'ennu_health_score',
					)
				),
				'hormone' => array(
					'title' => 'Hormone Assessment',
					'fields' => array(
						'ennu_hormone_overall_score',
						'ennu_hormone_first_name',
						'ennu_hormone_last_name',
						'ennu_hormone_email',
						'ennu_hormone_responses',
						'ennu_hormone_submitted_at',
						'ennu_hormone_score',
					)
				),
				'skin' => array(
					'title' => 'Skin Assessment',
					'fields' => array(
						'ennu_skin_overall_score',
						'ennu_skin_first_name',
						'ennu_skin_last_name',
						'ennu_skin_email',
						'ennu_skin_responses',
						'ennu_skin_submitted_at',
						'ennu_skin_score',
					)
				),
				'sleep' => array(
					'title' => 'Sleep Assessment',
					'fields' => array(
						'ennu_sleep_overall_score',
						'ennu_sleep_first_name',
						'ennu_sleep_last_name',
						'ennu_sleep_email',
						'ennu_sleep_responses',
						'ennu_sleep_submitted_at',
						'ennu_sleep_score',
					)
				),
				'menopause' => array(
					'title' => 'Menopause Assessment',
					'fields' => array(
						'ennu_menopause_overall_score',
						'ennu_menopause_first_name',
						'ennu_menopause_last_name',
						'ennu_menopause_email',
						'ennu_menopause_responses',
						'ennu_menopause_submitted_at',
						'ennu_menopause_score',
					)
				),
				'health_optimization' => array(
					'title' => 'Health Optimization Assessment',
					'fields' => array(
						'ennu_health_optimization_overall_score',
						'ennu_health_optimization_first_name',
						'ennu_health_optimization_last_name',
						'ennu_health_optimization_email',
						'ennu_health_optimization_responses',
						'ennu_health_optimization_submitted_at',
						'ennu_health_optimization_score',
					)
				),
				'welcome' => array(
					'title' => 'Welcome Assessment',
					'fields' => array(
						'ennu_welcome_overall_score',
						'ennu_welcome_first_name',
						'ennu_welcome_last_name',
						'ennu_welcome_email',
						'ennu_welcome_responses',
						'ennu_welcome_submitted_at',
						'ennu_welcome_score',
					)
				),
				'biomarkers' => array(
					'title' => 'Biomarkers',
					'fields' => array(
						'ennu_user_biomarkers',
						'ennu_biomarker_data',
						'ennu_lab_results',
						'ennu_biomarker_history',
						'ennu_biomarker_reference_ranges',
					)
				),
				'symptoms' => array(
					'title' => 'Symptoms',
					'fields' => array(
						'ennu_symptoms',
						'ennu_symptom_triggers',
						'ennu_symptom_severity',
						'ennu_symptom_frequency',
						'ennu_centralized_symptoms',
						'ennu_symptom_categories',
					)
				),
				'system' => array(
					'title' => 'System & Cache',
					'fields' => array(
						'ennu_cache_assessment_definitions',
						'ennu_cache_user_data',
						'ennu_cache_biomarkers',
						'ennu_cache_scores',
						'ennu_transient_assessment_data',
						'ennu_transient_user_scores',
						'ennu_transient_biomarker_data',
					)
				),
				'security' => array(
					'title' => 'Security & Validation',
					'fields' => array(
						'ennu_nonce_data',
						'ennu_validation_status',
						'ennu_submission_verified',
						'ennu_data_integrity_check',
					)
				),
				'hubspot' => array(
					'title' => 'HubSpot Integration',
					'fields' => array(
						'ennu_hubspot_contact_id',
						'ennu_hubspot_sync_status',
						'ennu_hubspot_last_sync',
						'ennu_hubspot_field_mappings',
					)
				),
				'analytics' => array(
					'title' => 'Analytics & Tracking',
					'fields' => array(
						'ennu_assessment_completion_count',
						'ennu_last_assessment_date',
						'ennu_total_assessments_completed',
						'ennu_user_engagement_score',
						'ennu_profile_completion_percentage',
					)
				),
				'notifications' => array(
					'title' => 'Notifications',
					'fields' => array(
						'ennu_email_notifications_enabled',
						'ennu_notification_preferences',
						'ennu_last_notification_sent',
					)
				),
				'custom' => array(
					'title' => 'Custom Fields',
					'fields' => array(
						'ennu_custom_field_1',
						'ennu_custom_field_2',
						'ennu_custom_field_3',
						'ennu_custom_field_4',
						'ennu_custom_field_5',
					)
				),
				'hidden' => array(
					'title' => 'Hidden System Fields',
					'fields' => array(
						'ennu_system_version',
						'ennu_data_version',
						'ennu_last_data_update',
						'ennu_data_export_status',
						'ennu_backup_created',
						'ennu_restore_point',
					)
				),
				'performance' => array(
					'title' => 'Performance & Debug',
					'fields' => array(
						'ennu_debug_log',
						'ennu_error_log',
						'ennu_performance_metrics',
						'ennu_cache_hits',
						'ennu_cache_misses',
					)
				),
				'preferences' => array(
					'title' => 'User Preferences',
					'fields' => array(
						'ennu_user_preferences',
						'ennu_ui_preferences',
						'ennu_dashboard_layout',
						'ennu_notification_settings',
					)
				),
				'progress' => array(
					'title' => 'Assessment Progress',
					'fields' => array(
						'ennu_assessment_progress',
						'ennu_current_assessment',
						'ennu_assessment_start_time',
						'ennu_assessment_timeout',
					)
				),
				'reports' => array(
					'title' => 'Results & Reports',
					'fields' => array(
						'ennu_assessment_results_token',
						'ennu_results_shared',
						'ennu_report_generated',
						'ennu_pdf_report_url',
					)
				),
				'integrations' => array(
					'title' => 'Integrations',
					'fields' => array(
						'ennu_slack_notifications',
						'ennu_webhook_data',
						'ennu_api_integrations',
						'ennu_third_party_sync',
					)
				),
				'compliance' => array(
					'title' => 'Compliance & Privacy',
					'fields' => array(
						'ennu_gdpr_consent',
						'ennu_data_retention_policy',
						'ennu_privacy_settings',
						'ennu_data_export_request',
					)
				),
				'quality' => array(
					'title' => 'Quality Assurance',
					'fields' => array(
						'ennu_data_quality_score',
						'ennu_validation_errors',
						'ennu_data_completeness',
						'ennu_consistency_checks',
					)
				),
			);
			
			// Temporarily disable question integration to fix tabs
			// $ennu_fields_by_tab = $this->add_questions_to_assessment_tabs($ennu_fields_by_tab, $user_id);
			
			// Get existing meta to show current values
			global $wpdb;
			$existing_meta = $wpdb->get_results( $wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s ORDER BY meta_key",
				$user_id,
				'ennu_%'
			) );
			
			// Create lookup array for existing values
			$existing_values = array();
			foreach ( $existing_meta as $meta ) {
				$existing_values[$meta->meta_key] = $meta->meta_value;
			}
			
			// Enhanced CSS for robust tabs
			echo '<style>
			/* ENNU Enhanced Tab System */
			.ennu-tabs-wrapper {
				margin: 20px 0;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
			
			.ennu-tabs-nav {
				display: flex;
				flex-wrap: wrap;
				gap: 2px;
				border-bottom: 2px solid #2271b1;
				margin-bottom: 0;
				background: #f0f0f1;
				padding: 0 8px;
				border-radius: 4px 4px 0 0;
			}
			
			.ennu-tab-button {
				background: #f6f7f7;
				border: 1px solid #c3c4c7;
				border-bottom: none;
				padding: 8px 12px;
				cursor: pointer;
				border-radius: 4px 4px 0 0;
				font-size: 13px;
				font-weight: 500;
				color: #50575e;
				transition: all 0.2s ease;
				position: relative;
				margin: 0;
				min-width: 80px;
				text-align: center;
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 6px;
				user-select: none;
			}
			
			.ennu-tab-button:hover {
				background: #fff;
				color: #2271b1;
				border-color: #2271b1;
			}
			
			.ennu-tab-button.active {
				background: #fff;
				color: #2271b1;
				border-color: #2271b1;
				border-bottom: 2px solid #fff;
				margin-bottom: -2px;
				z-index: 10;
				box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			}
			
			.ennu-tab-button:focus {
				outline: 2px solid #2271b1;
				outline-offset: -2px;
			}
			
			.ennu-tab-button:disabled {
				opacity: 0.5;
				cursor: not-allowed;
			}
			
			.ennu-field-count {
				background: #2271b1;
				color: white;
				border-radius: 12px;
				padding: 2px 6px;
				font-size: 10px;
				font-weight: 600;
				min-width: 16px;
				text-align: center;
				line-height: 1;
			}
			
			.ennu-tab-content {
				display: none;
				padding: 20px;
				border: 1px solid #c3c4c7;
				border-top: none;
				background: #fff;
				border-radius: 0 0 4px 4px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			}
			
			.ennu-tab-content.active {
				display: block;
				animation: fadeIn 0.3s ease-in-out;
			}
			
			@keyframes fadeIn {
				from { opacity: 0; transform: translateY(5px); }
				to { opacity: 1; transform: translateY(0); }
			}
			
			.ennu-tab-header {
				margin: 0 0 15px 0;
				padding: 0 0 10px 0;
				border-bottom: 1px solid #dcdcde;
				color: #1d2327;
				font-size: 16px;
				font-weight: 600;
			}
			
			.ennu-empty-field {
				color: #646970;
				font-weight: 400;
				font-style: italic;
			}
			
			.ennu-field-row {
				transition: background-color 0.2s ease;
			}
			
			.ennu-field-row:hover {
				background-color: #f6f7f7;
			}
			
			.ennu-field-label {
				font-weight: 500;
				color: #1d2327;
			}
			
			.ennu-field-input {
				width: 100%;
				max-width: 400px;
			}
			
			.ennu-field-textarea {
				font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
				font-size: 11px;
				line-height: 1.4;
				resize: vertical;
				min-height: 80px;
			}
			
			/* Responsive Design */
			@media (max-width: 768px) {
				.ennu-tabs-nav {
					flex-direction: column;
					gap: 1px;
				}
				
				.ennu-tab-button {
					border-radius: 0;
					text-align: left;
					justify-content: flex-start;
				}
				
				.ennu-tab-button.active {
					border-radius: 0;
				}
			}
			
			/* Loading State */
			.ennu-tabs-loading {
				opacity: 0.6;
				pointer-events: none;
			}
			
			/* Error State */
			.ennu-tab-error {
				color: #d63638;
				background: #fcf0f1;
				border: 1px solid #d63638;
				padding: 10px;
				border-radius: 4px;
				margin: 10px 0;
			}
			
			/* Question Field Styling */
			.ennu-question-container {
				margin: 10px 0;
				padding: 15px;
				background: #f8f9fa;
				border: 1px solid #e9ecef;
				border-radius: 6px;
			}
			
			.ennu-question-text {
				font-weight: 600;
				color: #1d2327;
				margin-bottom: 10px;
				font-size: 14px;
				line-height: 1.4;
			}
			
			.ennu-checkbox-label {
				display: block;
				margin: 8px 0;
				padding: 8px;
				background: #fff;
				border: 1px solid #ddd;
				border-radius: 4px;
				cursor: pointer;
				transition: background-color 0.2s ease;
			}
			
			.ennu-checkbox-label:hover {
				background: #f0f0f1;
			}
			
			.ennu-checkbox-label input[type="checkbox"] {
				margin-right: 8px;
			}
			
			.ennu-current-answer {
				margin-top: 10px;
				padding: 8px 12px;
				background: #d1ecf1;
				border: 1px solid #bee5eb;
				border-radius: 4px;
				color: #0c5460;
				font-size: 13px;
			}
			
			.ennu-current-answer strong {
				color: #0a4b53;
			}
			</style>';
			
			// Create robust tab navigation
			echo '<div class="ennu-tabs-wrapper" id="ennu-tabs-wrapper">';
			echo '<div class="ennu-tabs-nav" role="tablist" aria-label="' . esc_attr__( 'ENNU Plugin Fields', 'ennulifeassessments' ) . '">';
			
			$first_tab = true;
			$tab_index = 0;
			foreach ( $ennu_fields_by_tab as $tab_key => $tab_data ) {
				$field_count = count( $tab_data['fields'] );
				$active_class = $first_tab ? ' active' : '';
				$aria_selected = $first_tab ? 'true' : 'false';
				$tab_id = 'tab-' . esc_attr( $tab_key );
				$panel_id = 'panel-' . esc_attr( $tab_key );
				
				echo '<button type="button" 
					class="ennu-tab-button' . $active_class . '" 
					data-tab="' . esc_attr( $tab_key ) . '" 
					id="' . $tab_id . '"
					role="tab"
					aria-selected="' . $aria_selected . '"
					aria-controls="' . $panel_id . '"
					tabindex="' . ( $first_tab ? '0' : '-1' ) . '">';
				echo '<span class="ennu-tab-title">' . esc_html( $tab_data['title'] ) . '</span>';
				echo '<span class="ennu-field-count" aria-label="' . sprintf( esc_attr__( '%d fields', 'ennulifeassessments' ), $field_count ) . '">' . $field_count . '</span>';
				echo '</button>';
				$first_tab = false;
				$tab_index++;
			}
			echo '</div>';
			
			// Create tab content panels
			$first_tab = true;
			foreach ( $ennu_fields_by_tab as $tab_key => $tab_data ) {
				$active_class = $first_tab ? ' active' : '';
				$panel_id = 'panel-' . esc_attr( $tab_key );
				$tab_id = 'tab-' . esc_attr( $tab_key );
				
				echo '<div id="' . $panel_id . '" 
					class="ennu-tab-content' . $active_class . '" 
					role="tabpanel" 
					aria-labelledby="' . $tab_id . '"
					tabindex="0">';
				
				echo '<h3 class="ennu-tab-header">' . esc_html( $tab_data['title'] ) . ' <span class="ennu-field-count">' . count( $tab_data['fields'] ) . '</span></h3>';
				echo '<table class="form-table" role="presentation">';
				
				foreach ( $tab_data['fields'] as $field_key ) {
					// Store current tab key for field rendering
					$current_tab_key = $tab_key;
					$current_value = isset( $existing_values[$field_key] ) ? $existing_values[$field_key] : '';
					$has_value = ! empty( $current_value );
					$field_type = $this->determine_field_type( $field_key, $current_value );
					
					echo '<tr class="ennu-field-row">';
					echo '<th scope="row"><label for="' . esc_attr( $field_key ) . '" class="ennu-field-label">' . esc_html( $field_key );
					if ( ! $has_value ) {
						echo ' <span class="ennu-empty-field">(' . esc_html__( 'empty', 'ennulifeassessments' ) . ')</span>';
					}
					echo '</label></th>';
					
										// Enhanced field rendering with better error handling
					echo '<td>';
					try {
						switch ( $field_type ) {
							case 'json':
							case 'array':
								$display_value = is_string( $current_value ) ? $current_value : json_encode( $current_value, JSON_PRETTY_PRINT );
								echo '<textarea 
									name="' . esc_attr( $field_key ) . '" 
									id="' . esc_attr( $field_key ) . '" 
									rows="4" 
									cols="60" 
									class="ennu-field-input ennu-field-textarea"
									placeholder="' . esc_attr__( 'Enter JSON data...', 'ennulifeassessments' ) . '">' . esc_textarea( $display_value ) . '</textarea>';
								break;
								
							case 'boolean':
								echo '<select name="' . esc_attr( $field_key ) . '" id="' . esc_attr( $field_key ) . '" class="ennu-field-input">';
								echo '<option value="">' . esc_html__( 'Not Set', 'ennulifeassessments' ) . '</option>';
								echo '<option value="1"' . selected( $current_value, '1', false ) . '>' . esc_html__( 'True', 'ennulifeassessments' ) . '</option>';
								echo '<option value="0"' . selected( $current_value, '0', false ) . '>' . esc_html__( 'False', 'ennulifeassessments' ) . '</option>';
								echo '</select>';
								break;
								
							case 'date':
								echo '<input type="date" 
									name="' . esc_attr( $field_key ) . '" 
									id="' . esc_attr( $field_key ) . '" 
									value="' . esc_attr( $current_value ) . '" 
									class="ennu-field-input" />';
								break;
								
							case 'email':
								echo '<input type="email" 
									name="' . esc_attr( $field_key ) . '" 
									id="' . esc_attr( $field_key ) . '" 
									value="' . esc_attr( $current_value ) . '" 
									class="ennu-field-input" 
									placeholder="' . esc_attr__( 'Enter email address...', 'ennulifeassessments' ) . '" />';
								break;
								
							case 'phone':
								echo '<input type="tel" 
									name="' . esc_attr( $field_key ) . '" 
									id="' . esc_attr( $field_key ) . '" 
									value="' . esc_attr( $current_value ) . '" 
									class="ennu-field-input" 
									placeholder="' . esc_attr__( 'Enter phone number...', 'ennulifeassessments' ) . '" />';
								break;
								
							default:
								echo '<input type="text" 
									name="' . esc_attr( $field_key ) . '" 
									id="' . esc_attr( $field_key ) . '" 
									value="' . esc_attr( $current_value ) . '" 
									class="ennu-field-input" />';
								break;
						}
					} catch ( Exception $e ) {
						echo '<div class="ennu-tab-error">' . esc_html__( 'Error rendering field:', 'ennulifeassessments' ) . ' ' . esc_html( $e->getMessage() ) . '</div>';
					}
					echo '</td>';
			echo '</tr>';
				}
				
				echo '</table>';
				echo '</div>';
				$first_tab = false;
			}
			echo '</div>';
			
			// Simple jQuery tab system
			echo '<script type="text/javascript">
			jQuery(document).ready(function($) {
				console.log("ENNU Tab System: Initializing with jQuery");
				
				// Check if tabs exist
				var tabs = $(".ennu-tab-button");
				var panels = $(".ennu-tab-content");
				
				console.log("ENNU Tab System: Found", tabs.length, "tabs and", panels.length, "panels");
				
				if (tabs.length === 0) {
					console.error("ENNU Tab System: No tabs found!");
					return;
				}
				
				// Add click handlers
				$(".ennu-tab-button").on("click", function(e) {
					e.preventDefault();
					var tabId = $(this).data("tab");
					console.log("ENNU Tab System: Tab clicked:", tabId);
					
					// Remove active class from all tabs and content
					$(".ennu-tab-button").removeClass("active");
					$(".ennu-tab-content").removeClass("active");
					
					// Add active class to clicked tab and corresponding content
					$(this).addClass("active");
					$("#panel-" + tabId).addClass("active");
					
					console.log("ENNU Tab System: Switched to tab:", tabId);
				});
				
				console.log("ENNU Tab System: Tab system initialized successfully");
			});
			</script>';
			
			echo '</td></tr>';
			
			echo '</table>';
			
			// Assessment Status Summary
			echo '<h3>' . esc_html__( 'Assessment Completion Status', 'ennulifeassessments' ) . '</h3>';
			echo '<div class="ennu-assessment-status-section">';
			
			if ( ! empty( $assessments ) && is_array( $assessments ) ) {
				echo '<ul>';
				foreach ( $assessments as $assessment_type => $data ) {
					echo '<li><strong>' . esc_html( ucfirst( str_replace( '-', ' ', $assessment_type ) ) ) . '</strong>';
					if ( isset( $data['submitted_at'] ) && is_string( $data['submitted_at'] ) ) {
						echo '<br><small>' . esc_html__( 'Submitted:', 'ennulifeassessments' ) . ' ' . esc_html( $data['submitted_at'] ) . '</small>';
					}
					if ( isset( $data['score'] ) ) {
						echo '<br><small>' . esc_html__( 'Score:', 'ennulifeassessments' ) . ' ' . esc_html( $data['score'] ) . '</small>';
					}
					echo '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p>' . esc_html__( 'No assessments completed yet.', 'ennulifeassessments' ) . '</p>';
			}
			echo '</div>';
			
			// Biomarkers Section
			echo '<h3>' . esc_html__( 'Biomarkers', 'ennulifeassessments' ) . '</h3>';
			echo '<div class="ennu-biomarkers-section">';
			
			// Get user biomarkers
			$user_biomarkers = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
			
			if ( ! empty( $user_biomarkers ) && is_array( $user_biomarkers ) ) {
				echo '<table class="widefat striped">';
				echo '<thead>';
				echo '<tr>';
				echo '<th>' . esc_html__( 'Biomarker', 'ennulifeassessments' ) . '</th>';
				echo '<th>' . esc_html__( 'Value', 'ennulifeassessments' ) . '</th>';
				echo '<th>' . esc_html__( 'Unit', 'ennulifeassessments' ) . '</th>';
				echo '<th>' . esc_html__( 'Reference Range', 'ennulifeassessments' ) . '</th>';
				echo '<th>' . esc_html__( 'Date', 'ennulifeassessments' ) . '</th>';
				echo '<th>' . esc_html__( 'Status', 'ennulifeassessments' ) . '</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				
				foreach ( $user_biomarkers as $biomarker_key => $biomarker_data ) {
					if ( is_array( $biomarker_data ) ) {
						echo '<tr>';
						echo '<td><strong>' . esc_html( $biomarker_data['name'] ?? $biomarker_key ) . '</strong></td>';
						echo '<td>' . esc_html( $biomarker_data['value'] ?? '-' ) . '</td>';
						echo '<td>' . esc_html( $biomarker_data['unit'] ?? '-' ) . '</td>';
						echo '<td>' . esc_html( $biomarker_data['reference_range'] ?? '-' ) . '</td>';
						echo '<td>' . esc_html( $biomarker_data['date'] ?? '-' ) . '</td>';
						
						// Status with color coding
						$status = $biomarker_data['status'] ?? 'normal';
						$status_class = '';
						if ( $status === 'high' || $status === 'critical_high' ) {
							$status_class = 'color: #d63638;'; // Red
						} elseif ( $status === 'low' || $status === 'critical_low' ) {
							$status_class = 'color: #e26f56;'; // Orange
						} elseif ( $status === 'optimal' ) {
							$status_class = 'color: #00a32a;'; // Green
						}
						
						echo '<td style="' . esc_attr( $status_class ) . '">' . esc_html( ucfirst( str_replace( '_', ' ', $status ) ) ) . '</td>';
						echo '</tr>';
					}
				}
				
				echo '</tbody>';
				echo '</table>';
			} else {
				echo '<p>' . esc_html__( 'No biomarker data available.', 'ennulifeassessments' ) . '</p>';
			}
			
			// Check for flagged biomarkers
			if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
				$flag_manager = new ENNU_Biomarker_Flag_Manager();
				$flagged_biomarkers = $flag_manager->get_flagged_biomarkers( $user_id );
				
				if ( ! empty( $flagged_biomarkers ) ) {
					echo '<h4>' . esc_html__( 'Flagged Biomarkers', 'ennulifeassessments' ) . '</h4>';
					echo '<ul class="ennu-flagged-biomarkers">';
					foreach ( $flagged_biomarkers as $flag ) {
						echo '<li>';
						echo '<strong>' . esc_html( $flag['biomarker_name'] ?? 'Unknown Biomarker' ) . '</strong>: ';
						echo esc_html( $flag['reason'] ?? 'No reason provided' );
						if ( isset( $flag['value'] ) ) {
							echo ' (Value: ' . esc_html( $flag['value'] ) . ')';
						}
						if ( isset( $flag['explanation'] ) ) {
							echo ' - ' . esc_html( $flag['explanation'] );
						}
						echo '</li>';
					}
					echo '</ul>';
				}
			}
			
			echo '</div>'; // Close biomarkers section
			
			// Symptoms Section
			echo '<h3>' . esc_html__( 'Centralized Symptoms', 'ennulifeassessments' ) . '</h3>';
			echo '<div class="ennu-symptoms-section">';
			
			// Get centralized symptoms
			if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
				$symptoms_data = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
				
				if ( ! empty( $symptoms_data['symptoms'] ) && is_array( $symptoms_data['symptoms'] ) ) {
					echo '<table class="widefat striped">';
					echo '<thead>';
					echo '<tr>';
					echo '<th>' . esc_html__( 'Symptom', 'ennulifeassessments' ) . '</th>';
					echo '<th>' . esc_html__( 'Severity', 'ennulifeassessments' ) . '</th>';
					echo '<th>' . esc_html__( 'Category', 'ennulifeassessments' ) . '</th>';
					echo '<th>' . esc_html__( 'Source Assessment', 'ennulifeassessments' ) . '</th>';
					echo '<th>' . esc_html__( 'Date Reported', 'ennulifeassessments' ) . '</th>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					
					foreach ( $symptoms_data['symptoms'] as $symptom_key => $symptom ) {
						if ( is_array( $symptom ) ) {
							echo '<tr>';
							echo '<td><strong>' . esc_html( $symptom['name'] ?? $symptom_key ) . '</strong></td>';
							echo '<td>' . esc_html( $symptom['severity'] ?? 'Unknown' ) . '</td>';
							echo '<td>' . esc_html( $symptom['category'] ?? '-' ) . '</td>';
							echo '<td>' . esc_html( ucfirst( str_replace( '-', ' ', $symptom['source'] ?? 'Unknown' ) ) ) . '</td>';
							echo '<td>' . esc_html( $symptom['date'] ?? '-' ) . '</td>';
							echo '</tr>';
						}
					}
					
					echo '</tbody>';
					echo '</table>';
					
					// Show symptom triggers if available
					$triggers = get_user_meta( $user_id, 'ennu_symptom_triggers', true );
					if ( ! empty( $triggers ) ) {
						echo '<h4>' . esc_html__( 'Symptom Triggers', 'ennulifeassessments' ) . '</h4>';
						echo '<p class="description">' . esc_html__( 'What questions/assessments triggered each symptom:', 'ennulifeassessments' ) . '</p>';
						echo '<pre style="background: #f0f0f0; padding: 10px; overflow: auto;">';
						echo esc_html( json_encode( $triggers, JSON_PRETTY_PRINT ) );
						echo '</pre>';
					}
				} else {
					echo '<p>' . esc_html__( 'No symptoms reported.', 'ennulifeassessments' ) . '</p>';
				}
				
				// Last updated info
				if ( isset( $symptoms_data['last_updated'] ) ) {
					echo '<p class="description">';
					echo esc_html__( 'Last Updated:', 'ennulifeassessments' ) . ' ' . esc_html( $symptoms_data['last_updated'] );
					echo '</p>';
				}
			} else {
				echo '<p>' . esc_html__( 'Centralized Symptoms Manager not available.', 'ennulifeassessments' ) . '</p>';
			}
			
			echo '</div>'; // Close symptoms section
			
		} catch ( Exception $e ) {
			error_log( 'ENNU Enhanced Admin: Fatal error in show_user_assessment_fields: ' . $e->getMessage() );
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Error loading assessment data. Please try again.', 'ennulifeassessments' ) . '</p></div>';
		}
	}
	
	/**
	 * Save user assessment fields
	 *
	 * @param int $user_id User ID
	 */
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
			'ennu_global_billing_phone',
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
		if ( strpos( $field_key, '_responses' ) !== false || strpos( $field_key, '_data' ) !== false || strpos( $field_key, '_scores' ) !== false || 
			 strpos( $field_key, '_goals' ) !== false || strpos( $field_key, '_assessments' ) !== false || strpos( $field_key, '_biomarkers' ) !== false ||
			 strpos( $field_key, '_symptoms' ) !== false || strpos( $field_key, '_preferences' ) !== false || strpos( $field_key, '_settings' ) !== false ||
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
			 strpos( $field_key, '_count' ) !== false || strpos( $field_key, '_percentage' ) !== false || strpos( $field_key, '_score' ) !== false ||
			 strpos( $field_key, '_height_weight' ) !== false ) {
			return 'json';
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
					$ennu_fields_by_tab[$tab_key]['fields'] = array_merge(
						$ennu_fields_by_tab[$tab_key]['fields'],
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
		$question_text = isset($question_data['question']) ? $question_data['question'] : '';
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
				'ennu_global_billing_phone',
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
				'ennu_hair_ennu_global_date_of_birth',
				'ennu_hair_ennu_global_date_of_birth_month',
				'ennu_hair_ennu_global_date_of_birth_day',
				'ennu_hair_ennu_global_date_of_birth_year',
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
				'ennu_weight-loss_ennu_global_gender',
				'ennu_weight-loss_ennu_global_date_of_birth_month',
				'ennu_weight-loss_ennu_global_date_of_birth_day',
				'ennu_weight-loss_ennu_global_date_of_birth_year',
				'ennu_weight-loss_ennu_global_date_of_birth',
				'ennu_weight-loss_ennu_global_height_weight_ft',
				'ennu_weight-loss_ennu_global_height_weight_in',
				'ennu_weight-loss_ennu_global_height_weight_lbs',
				
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
}
