<?php
/**
 * ENNU REST API Controller
 *
 * Handles REST API endpoints for external integrations and mobile app support.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_REST_API_Controller extends WP_REST_Controller {

	/**
	 * API namespace
	 *
	 * @var string
	 */
	protected $namespace = 'ennu/v1';

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/assessments',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_assessments' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/assessments/(?P<type>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_assessment' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'type' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/users/(?P<id>\d+)/scores',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_user_scores' ),
				'permission_callback' => array( $this, 'check_user_permissions' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/assessments/(?P<type>[a-zA-Z0-9_-]+)/submit',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'submit_assessment' ),
				'permission_callback' => array( $this, 'check_user_permissions' ),
				'args'                => array(
					'type'      => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'responses' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_array( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/users/(?P<id>\d+)/biomarkers',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_user_biomarkers' ),
				'permission_callback' => array( $this, 'check_user_permissions' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/users/(?P<id>\d+)/goals',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_user_goals' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_user_goals' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
					'args'                => array(
						'goals' => array(
							'required'          => true,
							'validate_callback' => function( $param ) {
								return is_array( $param );
							},
						),
					),
				),
				'args' => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);
	}

	/**
	 * Get all available assessments
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function get_assessments( $request ) {
		$assessments = ENNU_Assessment_Scoring::get_all_definitions();

		$formatted_assessments = array();
		foreach ( $assessments as $type => $definition ) {
			$formatted_assessments[] = array(
				'type'           => $type,
				'title'          => isset( $definition['title'] ) ? $definition['title'] : ucwords( str_replace( '_', ' ', $type ) ),
				'description'    => isset( $definition['description'] ) ? $definition['description'] : '',
				'question_count' => isset( $definition['questions'] ) ? count( $definition['questions'] ) : 0,
			);
		}

		return rest_ensure_response( $formatted_assessments );
	}

	/**
	 * Get specific assessment details
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function get_assessment( $request ) {
		$type        = $request->get_param( 'type' );
		$assessments = ENNU_Assessment_Scoring::get_all_definitions();

		if ( ! isset( $assessments[ $type ] ) ) {
			return new WP_Error( 'assessment_not_found', 'Assessment type not found', array( 'status' => 404 ) );
		}

		return rest_ensure_response( $assessments[ $type ] );
	}

	/**
	 * Get user scores
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function get_user_scores( $request ) {
		$user_id = $request->get_param( 'id' );

		$scores       = ENNU_Immediate_Score_Calculator::get_dashboard_scores( $user_id );
		$progression  = ENNU_Goal_Progression_Tracker::get_progression_summary( $user_id );
		$completeness = ENNU_Profile_Completeness_Tracker::get_completeness_status( $user_id );

		return rest_ensure_response(
			array(
				'scores'       => $scores,
				'progression'  => $progression,
				'completeness' => $completeness,
			)
		);
	}

	/**
	 * Submit assessment
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function submit_assessment( $request ) {
		$type      = $request->get_param( 'type' );
		$responses = $request->get_param( 'responses' );
		$user_id   = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'unauthorized', 'User must be logged in', array( 'status' => 401 ) );
		}

		try {
			$scores = ENNU_Assessment_Scoring::calculate_scores_for_assessment( $type, $responses );

			update_user_meta( $user_id, 'ennu_' . $type . '_scores', $scores );
			update_user_meta( $user_id, 'ennu_' . $type . '_responses', $responses );

			$immediate_scores = ENNU_Immediate_Score_Calculator::trigger_after_assessment( $user_id, $type, $responses );

			return rest_ensure_response(
				array(
					'success'          => true,
					'scores'           => $scores,
					'immediate_scores' => $immediate_scores,
				)
			);

		} catch ( Exception $e ) {
			return new WP_Error( 'submission_failed', $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * Get user biomarkers
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function get_user_biomarkers( $request ) {
		$user_id = $request->get_param( 'id' );

		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		$flags          = ENNU_Biomarker_Flag_Manager::get_user_flags( $user_id );

		return rest_ensure_response(
			array(
				'biomarkers' => $biomarker_data ? $biomarker_data : array(),
				'flags'      => $flags,
			)
		);
	}

	/**
	 * Get user health goals
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function get_user_goals( $request ) {
		$user_id = $request->get_param( 'id' );

		$goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );

		return rest_ensure_response(
			array(
				'goals' => $goals ? $goals : array(),
			)
		);
	}

	/**
	 * Update user health goals
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function update_user_goals( $request ) {
		$user_id = $request->get_param( 'id' );
		$goals   = $request->get_param( 'goals' );

		$result = update_user_meta( $user_id, 'ennu_global_health_goals', $goals );

		if ( $result ) {
			ENNU_Goal_Progression_Tracker::track_progression( $user_id );

			return rest_ensure_response(
				array(
					'success' => true,
					'goals'   => $goals,
				)
			);
		}

		return new WP_Error( 'update_failed', 'Failed to update goals', array( 'status' => 500 ) );
	}

	/**
	 * Check basic permissions
	 *
	 * @return bool Permission status
	 */
	public function check_permissions() {
		return current_user_can( 'read' );
	}

	/**
	 * Check user-specific permissions
	 *
	 * @param WP_REST_Request $request Request object
	 * @return bool Permission status
	 */
	public function check_user_permissions( $request ) {
		$user_id         = $request->get_param( 'id' );
		$current_user_id = get_current_user_id();

		if ( ! $current_user_id ) {
			return false;
		}

		if ( $user_id && $user_id !== $current_user_id ) {
			return current_user_can( 'edit_users' );
		}

		return true;
	}
}
