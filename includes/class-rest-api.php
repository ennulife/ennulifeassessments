<?php
/**
 * ENNU REST API
 * Modern API endpoints for external integrations
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_REST_API {

	private $namespace = 'ennu/v1';
	private $security;
	private $user_manager;
	private $analytics_service;

	public function __construct() {
		$this->security          = new ENNU_AJAX_Security();
		$this->user_manager      = new ENNU_User_Manager();
		$this->analytics_service = new ENNU_Analytics_Service();

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/assessments',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_assessments' ),
				'permission_callback' => array( $this, 'check_read_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/assessments/(?P<assessment_type>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_assessment_details' ),
				'permission_callback' => array( $this, 'check_read_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/users/(?P<id>\d+)/scores',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_user_scores' ),
				'permission_callback' => array( $this, 'check_user_permission' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/users/(?P<id>\d+)/health-goals',
			array(
				'methods'             => array( 'GET', 'POST', 'PUT' ),
				'callback'            => array( $this, 'handle_health_goals' ),
				'permission_callback' => array( $this, 'check_user_permission' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/users/(?P<id>\d+)/biomarkers',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_user_biomarkers' ),
				'permission_callback' => array( $this, 'check_user_permission' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/users/(?P<id>\d+)/biomarkers',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_user_biomarkers' ),
				'permission_callback' => array( $this, 'check_manage_permission' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/users/(?P<id>\d+)/scores/recalculate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'recalculate_user_scores' ),
				'permission_callback' => array( $this, 'check_user_permission' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/analytics/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_analytics_stats' ),
				'permission_callback' => array( $this, 'check_manage_permission' ),
			)
		);
	}

	/**
	 * Get all available assessments
	 */
	public function get_assessments( $request ) {
		$assessments = array(
			'welcome'             => array(
				'name'        => 'Welcome Assessment',
				'description' => 'Initial health assessment',
				'status'      => 'active',
			),
			'hair'                => array(
				'name'        => 'Hair Assessment',
				'description' => 'Hair health and scalp analysis',
				'status'      => 'active',
			),
			'health'              => array(
				'name'        => 'General Health Assessment',
				'description' => 'Comprehensive health evaluation',
				'status'      => 'active',
			),
			'weight_loss'         => array(
				'name'        => 'Weight Loss Assessment',
				'description' => 'Weight management evaluation',
				'status'      => 'active',
			),
			'skin'                => array(
				'name'        => 'Skin Assessment',
				'description' => 'Skin health analysis',
				'status'      => 'active',
			),
			'sleep'               => array(
				'name'        => 'Sleep Assessment',
				'description' => 'Sleep quality evaluation',
				'status'      => 'active',
			),
			'hormone'             => array(
				'name'        => 'Hormone Assessment',
				'description' => 'Hormonal health evaluation',
				'status'      => 'active',
			),
			'menopause'           => array(
				'name'        => 'Menopause Assessment',
				'description' => 'Menopause-specific health evaluation',
				'status'      => 'active',
			),
			'testosterone'        => array(
				'name'        => 'Testosterone Assessment',
				'description' => 'Testosterone level evaluation',
				'status'      => 'active',
			),
			'ed_treatment'        => array(
				'name'        => 'ED Treatment Assessment',
				'description' => 'Erectile dysfunction evaluation',
				'status'      => 'active',
			),
			'health_optimization' => array(
				'name'        => 'Health Optimization Assessment',
				'description' => 'Comprehensive optimization evaluation',
				'status'      => 'active',
			),
		);

		return rest_ensure_response( $assessments );
	}

	/**
	 * Get assessment details
	 */
	public function get_assessment_details( $request ) {
		$assessment_type = $request->get_param( 'assessment_type' );

		$assessment_details = array(
			'type'               => $assessment_type,
			'questions'          => array(),
			'scoring_categories' => array(),
			'pillar_mappings'    => array(),
		);

		return rest_ensure_response( $assessment_details );
	}

	/**
	 * Handle health goals
	 */
	public function handle_health_goals( $request ) {
		$user_id = (int) $request['id'];
		$method  = $request->get_method();

		if ( $method === 'GET' ) {
			$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
			if ( ! $health_goals ) {
				$health_goals = array();
			}
			return rest_ensure_response( $health_goals );
		}

		if ( $method === 'POST' || $method === 'PUT' ) {
			$health_goals_data = $request->get_json_params();

			if ( ! $health_goals_data ) {
				return new WP_Error( 'invalid_data', 'Invalid health goals data provided', array( 'status' => 400 ) );
			}

			$updated = update_user_meta( $user_id, 'ennu_global_health_goals', $health_goals_data );

			if ( $updated ) {
				do_action( 'ennu_health_goals_updated', $user_id, $health_goals_data );
				return rest_ensure_response(
					array(
						'success' => true,
						'message' => 'Health goals updated successfully',
					)
				);
			} else {
				return new WP_Error( 'update_failed', 'Failed to update health goals', array( 'status' => 500 ) );
			}
		}

		return new WP_Error( 'method_not_allowed', 'Method not allowed', array( 'status' => 405 ) );
	}

	/**
	 * Get user biomarkers
	 */
	public function get_user_biomarkers( $request ) {
		$user_id = (int) $request['id'];

		$biomarkers = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! $biomarkers ) {
			$biomarkers = array();
		}

		return rest_ensure_response( $biomarkers );
	}

	/**
	 * Update user biomarkers
	 */
	public function update_user_biomarkers( $request ) {
		$user_id        = (int) $request['id'];
		$biomarker_data = $request->get_json_params();

		if ( ! $biomarker_data ) {
			return new WP_Error( 'invalid_data', 'Invalid biomarker data provided', array( 'status' => 400 ) );
		}

		$updated = update_user_meta( $user_id, 'ennu_biomarker_data', $biomarker_data );

		if ( $updated ) {
			do_action( 'ennu_biomarkers_updated', $user_id, $biomarker_data );
			return rest_ensure_response(
				array(
					'success' => true,
					'message' => 'Biomarkers updated successfully',
				)
			);
		} else {
			return new WP_Error( 'update_failed', 'Failed to update biomarkers', array( 'status' => 500 ) );
		}
	}

	/**
	 * Recalculate user scores
	 */
	public function recalculate_user_scores( $request ) {
		$user_id = (int) $request['id'];

		if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
			$scoring_system = new ENNU_Assessment_Scoring();
			$result         = $scoring_system->calculate_and_save_all_user_scores( $user_id );

			if ( $result ) {
				return rest_ensure_response(
					array(
						'success' => true,
						'message' => 'Scores recalculated successfully',
						'scores'  => $result,
					)
				);
			} else {
				return new WP_Error( 'calculation_failed', 'Failed to recalculate scores', array( 'status' => 500 ) );
			}
		}

		return new WP_Error( 'scoring_unavailable', 'Scoring system not available', array( 'status' => 503 ) );
	}

	/**
	 * Get user scores
	 */
	public function get_user_scores( $request ) {
		$user_id = (int) $request['id'];

		$scores = array(
			'life_score'    => get_user_meta( $user_id, 'ennu_life_score', true ),
			'pillar_scores' => array(
				'mind'       => get_user_meta( $user_id, 'ennu_mind_pillar_score', true ),
				'body'       => get_user_meta( $user_id, 'ennu_body_pillar_score', true ),
				'lifestyle'  => get_user_meta( $user_id, 'ennu_lifestyle_pillar_score', true ),
				'aesthetics' => get_user_meta( $user_id, 'ennu_aesthetics_pillar_score', true ),
			),
		);

		return rest_ensure_response( $scores );
	}

	/**
	 * Get analytics stats
	 */
	public function get_analytics_stats( $request ) {
		$stats = $this->analytics_service->get_system_stats();
		return rest_ensure_response( $stats );
	}

	/**
	 * Check read permission
	 */
	public function check_read_permission( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Check user permission
	 */
	public function check_user_permission( $request ) {
		$user_id = (int) $request['id'];
		return current_user_can( 'edit_user', $user_id ) || get_current_user_id() === $user_id;
	}

	/**
	 * Check manage permission
	 */
	public function check_manage_permission( $request ) {
		return current_user_can( 'manage_options' );
	}
}

new ENNU_REST_API();
