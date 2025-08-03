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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 10 );
		add_action( 'admin_init', array( $this, 'initialize_csrf_protection' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		
		// Automatic symptom population on assessment completion
		add_action( 'ennu_assessment_completed', array( $this, 'auto_populate_symptoms_on_assessment_completion' ), 15, 2 );
		
		// User profile hooks are registered in the main plugin file
		// Removed duplicate hooks to prevent conflicts
		// add_action( 'show_user_profile', array( $this, 'add_ennu_profile_section' ) );
		// add_action( 'edit_user_profile', array( $this, 'add_ennu_profile_section' ) );
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
	 * Auto-detect existing pages and add them to mappings if they're not already there
	 */
	private function auto_detect_existing_pages() {
		$settings = $this->get_plugin_settings();
		$page_mappings = $settings['page_mappings'];
		$updated = false;
		
		// Expected page paths and their shortcodes
		$expected_pages = array(
			'dashboard' => '[ennu-user-dashboard]',
			'assessments' => '[ennu-assessments]',
			'registration' => '[ennu-welcome]',
			'signup' => '[ennu-signup]',
			'assessment-results' => '[ennu-assessment-results]',
			'call' => '',
			'ennu-life-score' => '',
			// Assessment pages
			'assessments/hair' => '[ennu-hair]',
			'assessments/ed-treatment' => '[ennu-ed-treatment]',
			'assessments/weight-loss' => '[ennu-weight-loss]',
			'assessments/health' => '[ennu-health]',
			'assessments/health-optimization' => '[ennu-health-optimization]',
			'assessments/skin' => '[ennu-skin]',
			'assessments/hormone' => '[ennu-hormone]',
			'assessments/testosterone' => '[ennu-testosterone]',
			'assessments/menopause' => '[ennu-menopause]',
			'assessments/sleep' => '[ennu-sleep]',
		);
		
		foreach ( $expected_pages as $path => $shortcode ) {
			if ( empty( $page_mappings[ $path ] ) ) {
				// Try to find existing page by path
				$existing_page = get_page_by_path( $path, OBJECT, 'page' );
				if ( $existing_page ) {
					$page_mappings[ $path ] = $existing_page->ID;
					$updated = true;
					error_log( "ENNU Auto-Detect: Found existing page for path '{$path}' with ID {$existing_page->ID}" );
				} else {
					// Create the page if it doesn't exist
					$page_title = $this->get_page_title_from_path( $path );
					$page_id = $this->create_page( $path, $page_title, $shortcode );
					if ( $page_id ) {
						$page_mappings[ $path ] = $page_id;
						$updated = true;
						error_log( "ENNU Auto-Detect: Created new page for path '{$path}' with ID {$page_id}" );
					}
				}
			}
		}
		
		if ( $updated ) {
			$settings['page_mappings'] = $page_mappings;
			update_option( 'ennu_life_settings', $settings );
			update_option( 'ennu_created_pages', $page_mappings );
		}
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

		// Assessment Form Pages
		foreach ( $assessment_keys as $slug => $key ) {
			$label             = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " (/assessments/{$slug}/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/" );
		}
		echo '</div></div>';

		// Assessment Results Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Results Pages</h3>';
		echo '<p>Results pages for each assessment type</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $key ) {
			$label             = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}/results";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " Results (/assessments/{$slug}/results/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/results/" );
		}
		echo '</div></div>';

		// Assessment Details Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Details Pages</h3>';
		echo '<p>Treatment options and detailed information pages</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $key ) {
			$label             = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}/details";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " Details (/assessments/{$slug}/details/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/details/" );
		}
		echo '</div></div>';

		// Assessment Consultation Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Consultation Pages</h3>';
		echo '<p>Consultation booking pages for each assessment type</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $assessment_keys as $slug => $key ) {
			$label             = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}/consultation";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " Consultation (/assessments/{$slug}/consultation/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/consultation/" );
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
			
			// NEW: Add comprehensive editable fields section
			echo '<h3>' . esc_html__( 'ENNU Life User Information (Editable)', 'ennulifeassessments' ) . '</h3>';
			echo '<table class="form-table" role="presentation">';
			
			// Global Fields Section
			echo '<tr><th colspan="2"><h4 style="margin-top: 0;">' . esc_html__( 'Global Information', 'ennulifeassessments' ) . '</h4></th></tr>';
			
			// First Name
			$first_name = get_user_meta( $user_id, 'ennu_global_first_name', true );
			echo '<tr>';
			echo '<th><label for="ennu_global_first_name">' . esc_html__( 'First Name', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><input type="text" name="ennu_global_first_name" id="ennu_global_first_name" value="' . esc_attr( $first_name ) . '" class="regular-text" /></td>';
			echo '</tr>';
			
			// Last Name
			$last_name = get_user_meta( $user_id, 'ennu_global_last_name', true );
			echo '<tr>';
			echo '<th><label for="ennu_global_last_name">' . esc_html__( 'Last Name', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><input type="text" name="ennu_global_last_name" id="ennu_global_last_name" value="' . esc_attr( $last_name ) . '" class="regular-text" /></td>';
			echo '</tr>';
			
			// Email
			$email = get_user_meta( $user_id, 'ennu_global_email', true );
			echo '<tr>';
			echo '<th><label for="ennu_global_email">' . esc_html__( 'ENNU Email', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><input type="email" name="ennu_global_email" id="ennu_global_email" value="' . esc_attr( $email ) . '" class="regular-text" /></td>';
			echo '</tr>';
			
			// Phone
			$phone = get_user_meta( $user_id, 'ennu_global_billing_phone', true );
			echo '<tr>';
			echo '<th><label for="ennu_global_billing_phone">' . esc_html__( 'Phone Number', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><input type="tel" name="ennu_global_billing_phone" id="ennu_global_billing_phone" value="' . esc_attr( $phone ) . '" class="regular-text" /></td>';
			echo '</tr>';
			
			// Gender
			$gender = get_user_meta( $user_id, 'ennu_global_gender', true );
			echo '<tr>';
			echo '<th><label for="ennu_global_gender">' . esc_html__( 'Gender', 'ennulifeassessments' ) . '</label></th>';
			echo '<td>';
			echo '<select name="ennu_global_gender" id="ennu_global_gender">';
			echo '<option value="">' . esc_html__( 'Select Gender', 'ennulifeassessments' ) . '</option>';
			echo '<option value="male"' . selected( $gender, 'male', false ) . '>' . esc_html__( 'Male', 'ennulifeassessments' ) . '</option>';
			echo '<option value="female"' . selected( $gender, 'female', false ) . '>' . esc_html__( 'Female', 'ennulifeassessments' ) . '</option>';
			echo '<option value="other"' . selected( $gender, 'other', false ) . '>' . esc_html__( 'Other', 'ennulifeassessments' ) . '</option>';
			echo '</select>';
			echo '</td>';
			echo '</tr>';
			
			// Date of Birth
			$dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
			echo '<tr>';
			echo '<th><label for="ennu_global_date_of_birth">' . esc_html__( 'Date of Birth', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><input type="date" name="ennu_global_date_of_birth" id="ennu_global_date_of_birth" value="' . esc_attr( $dob ) . '" class="regular-text" /></td>';
			echo '</tr>';
			
			// Height & Weight
			$height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
			if ( ! is_array( $height_weight ) ) {
				$height_weight = array( 'ft' => '', 'in' => '', 'weight' => '' );
			}
			echo '<tr>';
			echo '<th><label>' . esc_html__( 'Height & Weight', 'ennulifeassessments' ) . '</label></th>';
			echo '<td>';
			echo '<span>Height: <input type="number" name="ennu_global_height_weight[ft]" value="' . esc_attr( $height_weight['ft'] ?? '' ) . '" style="width: 60px;" min="0" max="8" /> ft </span>';
			echo '<input type="number" name="ennu_global_height_weight[in]" value="' . esc_attr( $height_weight['in'] ?? '' ) . '" style="width: 60px;" min="0" max="11" /> in<br />';
			echo '<span style="margin-top: 5px; display: inline-block;">Weight: <input type="number" name="ennu_global_height_weight[weight]" value="' . esc_attr( $height_weight['weight'] ?? '' ) . '" style="width: 80px;" min="0" /> lbs</span>';
			echo '</td>';
			echo '</tr>';
			
			// Assessment Specific Fields Section
			echo '<tr><th colspan="2"><h4>' . esc_html__( 'Assessment-Specific Data', 'ennulifeassessments' ) . '</h4></th></tr>';
			
			// Get all assessments
			$assessments = get_user_meta( $user_id, 'ennu_assessments', true );
			$assessment_types = array(
				'weight-loss' => 'Weight Loss',
				'hair' => 'Hair Loss',
				'ed-treatment' => 'ED Treatment',
				'testosterone' => 'Testosterone',
				'health' => 'General Health',
				'hormone' => 'Hormone',
				'skin' => 'Skin Health',
				'sleep' => 'Sleep Quality',
				'menopause' => 'Menopause'
			);
			
			foreach ( $assessment_types as $type => $label ) {
				$has_data = false;
				
				// Check if user has completed this assessment
				if ( ! empty( $assessments ) && isset( $assessments[$type] ) ) {
					$has_data = true;
				}
				
				// Display section for this assessment type
				echo '<tr><th colspan="2"><strong>' . esc_html( $label ) . ' Assessment</strong>';
				if ( $has_data && isset( $assessments[$type]['submitted_at'] ) ) {
					echo ' <small>(Submitted: ' . esc_html( $assessments[$type]['submitted_at'] ) . ')</small>';
				} else {
					echo ' <small>(Not completed)</small>';
				}
				echo '</th></tr>';
				
				// Show relevant fields for each assessment type
				$prefix = 'ennu_' . str_replace( '-', '_', $type );
				
				// Common fields for all assessments
				$common_fields = array(
					'_first_name' => 'First Name',
					'_last_name' => 'Last Name',
					'_email' => 'Email',
					'_overall_score' => 'Overall Score',
					'_responses' => 'Responses (JSON)'
				);
				
				foreach ( $common_fields as $field_suffix => $field_label ) {
					$field_name = $prefix . $field_suffix;
					$field_value = get_user_meta( $user_id, $field_name, true );
					
					if ( ! empty( $field_value ) || $has_data ) {
						echo '<tr>';
						echo '<th style="padding-left: 40px;"><label for="' . esc_attr( $field_name ) . '">' . esc_html( $field_label ) . '</label></th>';
						
						if ( $field_suffix === '_responses' && is_array( $field_value ) ) {
							echo '<td><textarea name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_name ) . '" rows="3" cols="50" readonly>' . esc_textarea( json_encode( $field_value, JSON_PRETTY_PRINT ) ) . '</textarea></td>';
						} else {
							echo '<td><input type="text" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_name ) . '" value="' . esc_attr( $field_value ) . '" class="regular-text" /></td>';
						}
						echo '</tr>';
					}
				}
			}
			
			// Additional Meta Fields
			echo '<tr><th colspan="2"><h4>' . esc_html__( 'Additional User Meta', 'ennulifeassessments' ) . '</h4></th></tr>';
			
			// Display all ENNU-related user meta
			global $wpdb;
			$all_meta = $wpdb->get_results( $wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s ORDER BY meta_key",
				$user_id,
				'ennu_%'
			) );
			
			if ( ! empty( $all_meta ) ) {
				$displayed_keys = array();
				foreach ( $all_meta as $meta ) {
					// Skip if we've already displayed this field above
					if ( in_array( $meta->meta_key, $displayed_keys ) ) {
						continue;
					}
					
					// Skip certain system fields
					if ( strpos( $meta->meta_key, '_transient' ) !== false ) {
						continue;
					}
					
					$displayed_keys[] = $meta->meta_key;
					
					echo '<tr>';
					echo '<th><label for="' . esc_attr( $meta->meta_key ) . '">' . esc_html( $meta->meta_key ) . '</label></th>';
					
					$value = maybe_unserialize( $meta->meta_value );
					if ( is_array( $value ) || is_object( $value ) ) {
						echo '<td><textarea name="' . esc_attr( $meta->meta_key ) . '" id="' . esc_attr( $meta->meta_key ) . '" rows="3" cols="50" readonly>' . esc_textarea( json_encode( $value, JSON_PRETTY_PRINT ) ) . '</textarea></td>';
					} else {
						echo '<td><input type="text" name="' . esc_attr( $meta->meta_key ) . '" id="' . esc_attr( $meta->meta_key ) . '" value="' . esc_attr( $value ) . '" class="regular-text" /></td>';
					}
					echo '</tr>';
				}
			}
			
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
		
		// Save global fields
		$global_fields = array(
			'ennu_global_first_name',
			'ennu_global_last_name',
			'ennu_global_email',
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
				$prefix . '_first_name',
				$prefix . '_last_name',
				$prefix . '_email',
				$prefix . '_overall_score'
			);
			
			foreach ( $fields as $field ) {
				if ( isset( $_POST[$field] ) ) {
					update_user_meta( $user_id, $field, sanitize_text_field( $_POST[$field] ) );
				}
			}
		}
		
		// Handle any other ENNU meta fields that were posted
		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, 'ennu_' ) === 0 && ! in_array( $key, $global_fields ) ) {
				// Skip array values that were already handled
				if ( ! is_array( $value ) ) {
					update_user_meta( $user_id, $key, sanitize_text_field( $value ) );
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
}
