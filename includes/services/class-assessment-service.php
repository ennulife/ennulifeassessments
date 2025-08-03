<?php
/**
 * ENNU Assessment Service
 *
 * Handles assessment data management, processing, and scoring.
 *
 * @package ENNU_Life_Assessments
 * @since 64.11.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Assessment Service Class
 *
 * @since 64.11.0
 */
class ENNU_Assessment_Service {
	
	/**
	 * Database instance
	 *
	 * @var ENNU_Enhanced_Database
	 */
	private $database;
	
	/**
	 * Biomarker service instance
	 *
	 * @var ENNU_Biomarker_Service
	 */
	private $biomarker_service;
	
	/**
	 * Assessment types
	 *
	 * @var array
	 */
	private $assessment_types = array(
		'comprehensive' => 'Comprehensive Health Assessment',
		'hormone' => 'Hormone Assessment',
		'metabolic' => 'Metabolic Assessment',
		'cardiovascular' => 'Cardiovascular Assessment',
		'immune' => 'Immune System Assessment',
		'cognitive' => 'Cognitive Health Assessment',
		'weight_loss' => 'Weight Loss Assessment',
		'performance' => 'Performance Assessment',
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->database = ENNU_Life_Enhanced_Plugin::get_instance()->get_database();
		$this->biomarker_service = new ENNU_Biomarker_Service();
	}
	
	/**
	 * Create a new assessment
	 *
	 * @param array $assessment_data Assessment data.
	 * @return array Result with 'success' boolean and 'id' if successful.
	 */
	public function create_assessment( $assessment_data ) {
		// Validate required fields
		$required_fields = array( 'user_id', 'assessment_type' );
		foreach ( $required_fields as $field ) {
			if ( empty( $assessment_data[ $field ] ) ) {
				return array(
					'success' => false,
					'errors' => array( "Missing required field: {$field}" ),
				);
			}
		}
		
		// Validate assessment type
		if ( ! array_key_exists( $assessment_data['assessment_type'], $this->assessment_types ) ) {
			return array(
				'success' => false,
				'errors' => array( 'Invalid assessment type' ),
			);
		}
		
		// Prepare assessment data
		$assessment_post_data = array(
			'post_type' => 'ennu_assessment',
			'post_title' => $this->assessment_types[ $assessment_data['assessment_type'] ] . ' - ' . date( 'Y-m-d H:i:s' ),
			'post_status' => 'publish',
			'post_author' => $assessment_data['user_id'],
			'meta_input' => array(
				'user_id' => intval( $assessment_data['user_id'] ),
				'assessment_type' => sanitize_text_field( $assessment_data['assessment_type'] ),
				'biomarkers' => isset( $assessment_data['biomarkers'] ) ? $assessment_data['biomarkers'] : array(),
				'symptoms' => isset( $assessment_data['symptoms'] ) ? $assessment_data['symptoms'] : array(),
				'goals' => isset( $assessment_data['goals'] ) ? $assessment_data['goals'] : array(),
				'created_at' => current_time( 'mysql' ),
				'status' => 'in_progress',
			),
		);
		
		// Create the assessment post
		$assessment_id = wp_insert_post( $assessment_post_data );
		
		if ( is_wp_error( $assessment_id ) ) {
			return array(
				'success' => false,
				'errors' => array( 'Failed to create assessment: ' . $assessment_id->get_error_message() ),
			);
		}
		
		return array(
			'success' => true,
			'id' => $assessment_id,
		);
	}
	
	/**
	 * Get assessment by ID
	 *
	 * @param int $assessment_id Assessment ID.
	 * @return array|null Assessment data or null if not found.
	 */
	public function get_assessment( $assessment_id ) {
		$post = get_post( $assessment_id );
		
		if ( ! $post || $post->post_type !== 'ennu_assessment' ) {
			return null;
		}
		
		$assessment_data = array(
			'id' => $assessment_id,
			'title' => $post->post_title,
			'user_id' => get_post_meta( $assessment_id, 'user_id', true ),
			'assessment_type' => get_post_meta( $assessment_id, 'assessment_type', true ),
			'biomarkers' => get_post_meta( $assessment_id, 'biomarkers', true ) ?: array(),
			'symptoms' => get_post_meta( $assessment_id, 'symptoms', true ) ?: array(),
			'goals' => get_post_meta( $assessment_id, 'goals', true ) ?: array(),
			'created_at' => get_post_meta( $assessment_id, 'created_at', true ),
			'status' => get_post_meta( $assessment_id, 'status', true ),
			'scores' => get_post_meta( $assessment_id, 'scores', true ) ?: array(),
		);
		
		return $assessment_data;
	}
	
	/**
	 * Get assessments by user ID
	 *
	 * @param int $user_id User ID.
	 * @return array Array of assessment data.
	 */
	public function get_user_assessments( $user_id ) {
		$args = array(
			'post_type' => 'ennu_assessment',
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => 'user_id',
					'value' => $user_id,
					'compare' => '=',
				),
			),
			'orderby' => 'date',
			'order' => 'DESC',
			'posts_per_page' => -1,
		);
		
		$query = new WP_Query( $args );
		$assessments = array();
		
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$assessment = $this->get_assessment( get_the_ID() );
				if ( $assessment ) {
					$assessments[] = $assessment;
				}
			}
		}
		
		wp_reset_postdata();
		
		return $assessments;
	}
	
	/**
	 * Update assessment data
	 *
	 * @param int   $assessment_id Assessment ID.
	 * @param array $update_data Data to update.
	 * @return array Result with 'success' boolean.
	 */
	public function update_assessment( $assessment_id, $update_data ) {
		$assessment = $this->get_assessment( $assessment_id );
		
		if ( ! $assessment ) {
			return array(
				'success' => false,
				'errors' => array( 'Assessment not found' ),
			);
		}
		
		// Update allowed fields
		$allowed_fields = array( 'biomarkers', 'symptoms', 'goals', 'status', 'scores' );
		
		foreach ( $allowed_fields as $field ) {
			if ( isset( $update_data[ $field ] ) ) {
				update_post_meta( $assessment_id, $field, $update_data[ $field ] );
			}
		}
		
		// Update title if assessment type changed
		if ( isset( $update_data['assessment_type'] ) && array_key_exists( $update_data['assessment_type'], $this->assessment_types ) ) {
			update_post_meta( $assessment_id, 'assessment_type', sanitize_text_field( $update_data['assessment_type'] ) );
			
			// Update post title
			$new_title = $this->assessment_types[ $update_data['assessment_type'] ] . ' - ' . date( 'Y-m-d H:i:s', strtotime( $assessment['created_at'] ) );
			wp_update_post( array(
				'ID' => $assessment_id,
				'post_title' => $new_title,
			) );
		}
		
		return array(
			'success' => true,
		);
	}
	
	/**
	 * Delete assessment
	 *
	 * @param int $assessment_id Assessment ID.
	 * @return array Result with 'success' boolean.
	 */
	public function delete_assessment( $assessment_id ) {
		$assessment = $this->get_assessment( $assessment_id );
		
		if ( ! $assessment ) {
			return array(
				'success' => false,
				'errors' => array( 'Assessment not found' ),
			);
		}
		
		$result = wp_delete_post( $assessment_id, true );
		
		if ( ! $result ) {
			return array(
				'success' => false,
				'errors' => array( 'Failed to delete assessment' ),
			);
		}
		
		return array(
			'success' => true,
		);
	}
	
	/**
	 * Add biomarker to assessment
	 *
	 * @param int   $assessment_id Assessment ID.
	 * @param array $biomarker_data Biomarker data.
	 * @return array Result with 'success' boolean.
	 */
	public function add_biomarker_to_assessment( $assessment_id, $biomarker_data ) {
		$assessment = $this->get_assessment( $assessment_id );
		
		if ( ! $assessment ) {
			return array(
				'success' => false,
				'errors' => array( 'Assessment not found' ),
			);
		}
		
		// Validate biomarker data
		$validation = $this->biomarker_service->validate_biomarker( $biomarker_data );
		if ( ! $validation['valid'] ) {
			return array(
				'success' => false,
				'errors' => $validation['errors'],
			);
		}
		
		// Add user_id to biomarker data
		$biomarker_data['user_id'] = $assessment['user_id'];
		
		// Save biomarker
		$biomarker_result = $this->biomarker_service->save_biomarker( $biomarker_data );
		
		if ( ! $biomarker_result['success'] ) {
			return $biomarker_result;
		}
		
		// Add biomarker to assessment
		$biomarkers = $assessment['biomarkers'];
		$biomarkers[] = array(
			'id' => $biomarker_result['id'],
			'name' => $biomarker_data['name'],
			'value' => $biomarker_data['value'],
			'unit' => $biomarker_data['unit'],
			'reference_range' => $biomarker_data['reference_range'],
			'category' => $biomarker_data['category'],
		);
		
		update_post_meta( $assessment_id, 'biomarkers', $biomarkers );
		
		return array(
			'success' => true,
			'biomarker_id' => $biomarker_result['id'],
		);
	}
	
	/**
	 * Add symptoms to assessment
	 *
	 * @param int   $assessment_id Assessment ID.
	 * @param array $symptoms Symptoms data.
	 * @return array Result with 'success' boolean.
	 */
	public function add_symptoms_to_assessment( $assessment_id, $symptoms ) {
		$assessment = $this->get_assessment( $assessment_id );
		
		if ( ! $assessment ) {
			return array(
				'success' => false,
				'errors' => array( 'Assessment not found' ),
			);
		}
		
		// Validate symptoms data
		if ( ! is_array( $symptoms ) ) {
			return array(
				'success' => false,
				'errors' => array( 'Symptoms must be an array' ),
			);
		}
		
		// Merge with existing symptoms
		$existing_symptoms = $assessment['symptoms'];
		$updated_symptoms = array_merge( $existing_symptoms, $symptoms );
		
		update_post_meta( $assessment_id, 'symptoms', $updated_symptoms );
		
		return array(
			'success' => true,
		);
	}
	
	/**
	 * Calculate assessment scores
	 *
	 * @param int $assessment_id Assessment ID.
	 * @return array Result with 'success' boolean and scores.
	 */
	public function calculate_assessment_scores( $assessment_id ) {
		$assessment = $this->get_assessment( $assessment_id );
		
		if ( ! $assessment ) {
			return array(
				'success' => false,
				'errors' => array( 'Assessment not found' ),
			);
		}
		
		$scores = array();
		
		// Calculate biomarker scores
		if ( ! empty( $assessment['biomarkers'] ) ) {
			$scores['biomarker'] = $this->calculate_biomarker_score( $assessment['biomarkers'] );
		}
		
		// Calculate symptom scores
		if ( ! empty( $assessment['symptoms'] ) ) {
			$scores['symptom'] = $this->calculate_symptom_score( $assessment['symptoms'] );
		}
		
		// Calculate overall score
		$scores['overall'] = $this->calculate_overall_score( $scores );
		
		// Save scores to assessment
		update_post_meta( $assessment_id, 'scores', $scores );
		
		// Update assessment status
		update_post_meta( $assessment_id, 'status', 'completed' );
		
		return array(
			'success' => true,
			'scores' => $scores,
		);
	}
	
	/**
	 * Calculate biomarker score
	 *
	 * @param array $biomarkers Biomarker data.
	 * @return array Score data.
	 */
	private function calculate_biomarker_score( $biomarkers ) {
		$total_biomarkers = count( $biomarkers );
		$normal_count = 0;
		$abnormal_count = 0;
		
		foreach ( $biomarkers as $biomarker ) {
			$status = $this->biomarker_service->check_reference_range( $biomarker );
			if ( $status === 'normal' ) {
				$normal_count++;
			} else {
				$abnormal_count++;
			}
		}
		
		$score = $total_biomarkers > 0 ? ( $normal_count / $total_biomarkers ) * 100 : 0;
		
		return array(
			'score' => round( $score, 2 ),
			'total' => $total_biomarkers,
			'normal' => $normal_count,
			'abnormal' => $abnormal_count,
		);
	}
	
	/**
	 * Calculate symptom score
	 *
	 * @param array $symptoms Symptoms data.
	 * @return array Score data.
	 */
	private function calculate_symptom_score( $symptoms ) {
		$total_symptoms = count( $symptoms );
		$present_symptoms = 0;
		
		foreach ( $symptoms as $symptom => $present ) {
			if ( $present ) {
				$present_symptoms++;
			}
		}
		
		// Lower score for more symptoms (worse health)
		$score = $total_symptoms > 0 ? ( ( $total_symptoms - $present_symptoms ) / $total_symptoms ) * 100 : 100;
		
		return array(
			'score' => round( $score, 2 ),
			'total' => $total_symptoms,
			'present' => $present_symptoms,
			'absent' => $total_symptoms - $present_symptoms,
		);
	}
	
	/**
	 * Calculate overall score
	 *
	 * @param array $scores Individual scores.
	 * @return array Overall score data.
	 */
	private function calculate_overall_score( $scores ) {
		$total_score = 0;
		$weight_sum = 0;
		
		// Weight biomarker score more heavily (70%)
		if ( isset( $scores['biomarker'] ) ) {
			$total_score += $scores['biomarker']['score'] * 0.7;
			$weight_sum += 0.7;
		}
		
		// Weight symptom score less heavily (30%)
		if ( isset( $scores['symptom'] ) ) {
			$total_score += $scores['symptom']['score'] * 0.3;
			$weight_sum += 0.3;
		}
		
		$overall_score = $weight_sum > 0 ? $total_score / $weight_sum : 0;
		
		return array(
			'score' => round( $overall_score, 2 ),
			'components' => $scores,
		);
	}
	
	/**
	 * Get assessment types
	 *
	 * @return array Array of assessment types.
	 */
	public function get_assessment_types() {
		return $this->assessment_types;
	}
	
	/**
	 * Get assessment statistics
	 *
	 * @param int $user_id User ID.
	 * @return array Statistics data.
	 */
	public function get_assessment_statistics( $user_id ) {
		$assessments = $this->get_user_assessments( $user_id );
		
		$stats = array(
			'total_assessments' => count( $assessments ),
			'completed_assessments' => 0,
			'in_progress_assessments' => 0,
			'average_overall_score' => 0,
			'assessment_types' => array(),
		);
		
		$total_score = 0;
		$score_count = 0;
		
		foreach ( $assessments as $assessment ) {
			if ( $assessment['status'] === 'completed' ) {
				$stats['completed_assessments']++;
				
				if ( isset( $assessment['scores']['overall']['score'] ) ) {
					$total_score += $assessment['scores']['overall']['score'];
					$score_count++;
				}
			} else {
				$stats['in_progress_assessments']++;
			}
			
			// Count assessment types
			$type = $assessment['assessment_type'];
			if ( ! isset( $stats['assessment_types'][ $type ] ) ) {
				$stats['assessment_types'][ $type ] = 0;
			}
			$stats['assessment_types'][ $type ]++;
		}
		
		if ( $score_count > 0 ) {
			$stats['average_overall_score'] = round( $total_score / $score_count, 2 );
		}
		
		return $stats;
	}
} 