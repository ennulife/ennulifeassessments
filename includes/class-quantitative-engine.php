<?php
/**
 * ENNU Quantitative Assessment Engine - CRITICAL FIX
 * 
 * Processes biomarker data and calculates quantitative scores
 * 
 * @package ENNU_Life_Assessments
 * @since 1.0.0
 */

class ENNU_Quantitative_Engine {
	
	private static $instance = null;
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Calculate scores based on biomarker data
	 */
	public function calculate_scores( $biomarker_data, $assessment_type ) {
		$scores = array(
			'overall_score' => 0,
			'category_scores' => array(),
			'pillar_scores' => array(),
			'biomarker_flags' => array()
		);
		
		// Process each biomarker
		foreach ( $biomarker_data as $biomarker => $value ) {
			$flag_result = $this->flag_biomarker( $biomarker, $value, $assessment_type );
			if ( $flag_result ) {
				$scores['biomarker_flags'][] = $flag_result;
			}
		}
		
		// Calculate category scores
		$scores['category_scores'] = $this->calculate_category_scores( $biomarker_data, $assessment_type );
		
		// Calculate pillar scores
		$scores['pillar_scores'] = $this->calculate_pillar_scores( $scores['category_scores'] );
		
		// Calculate overall score
		$scores['overall_score'] = $this->calculate_overall_score( $scores['pillar_scores'] );
		
		return $scores;
	}
	
	/**
	 * Flag abnormal biomarkers
	 */
	private function flag_biomarker( $biomarker, $value, $assessment_type ) {
		$ranges = $this->get_biomarker_ranges( $assessment_type );
		
		if ( ! isset( $ranges[ $biomarker ] ) ) {
			return false;
		}
		
		$range = $ranges[ $biomarker ];
		$flag = null;
		
		if ( $value < $range['low'] ) {
			$flag = 'low';
		} elseif ( $value > $range['high'] ) {
			$flag = 'high';
		}
		
		if ( $flag ) {
			return array(
				'biomarker' => $biomarker,
				'value' => $value,
				'flag' => $flag,
				'range' => $range
			);
		}
		
		return false;
	}
	
	/**
	 * Get biomarker reference ranges
	 */
	private function get_biomarker_ranges( $assessment_type ) {
		$ranges = array();
		
		switch ( $assessment_type ) {
			case 'health-optimization':
				$ranges = array(
					'glucose' => array( 'low' => 70, 'high' => 100 ),
					'hba1c' => array( 'low' => 4.0, 'high' => 5.7 ),
					'testosterone' => array( 'low' => 300, 'high' => 1000 ),
					'tsh' => array( 'low' => 0.4, 'high' => 4.0 ),
					'vitamin_d' => array( 'low' => 30, 'high' => 100 ),
					'b12' => array( 'low' => 200, 'high' => 900 ),
					'ferritin' => array( 'low' => 20, 'high' => 250 ),
					'cortisol' => array( 'low' => 6, 'high' => 20 )
				);
				break;
		}
		
		return $ranges;
	}
	
	/**
	 * Calculate category scores
	 */
	private function calculate_category_scores( $biomarker_data, $assessment_type ) {
		$categories = array(
			'metabolic' => array( 'glucose', 'hba1c', 'insulin' ),
			'hormonal' => array( 'testosterone', 'tsh', 'cortisol' ),
			'nutritional' => array( 'vitamin_d', 'b12', 'ferritin' ),
			'cardiovascular' => array( 'cholesterol', 'triglycerides', 'hdl' )
		);
		
		$category_scores = array();
		
		foreach ( $categories as $category => $biomarkers ) {
			$score = 0;
			$count = 0;
			
			foreach ( $biomarkers as $biomarker ) {
				if ( isset( $biomarker_data[ $biomarker ] ) ) {
					$flag = $this->flag_biomarker( $biomarker, $biomarker_data[ $biomarker ], $assessment_type );
					if ( $flag ) {
						$score += 1; // Penalty for abnormal values
					}
					$count++;
				}
			}
			
			if ( $count > 0 ) {
				$category_scores[ $category ] = max( 0, 100 - ( $score / $count ) * 100 );
			} else {
				$category_scores[ $category ] = 0;
			}
		}
		
		return $category_scores;
	}
	
	/**
	 * Calculate pillar scores from category scores
	 */
	private function calculate_pillar_scores( $category_scores ) {
		$pillar_scores = array(
			'mind' => 0,
			'body' => 0,
			'lifestyle' => 0,
			'aesthetics' => 0
		);
		
		// Map categories to pillars
		$category_to_pillar = array(
			'metabolic' => 'body',
			'hormonal' => 'body',
			'nutritional' => 'lifestyle',
			'cardiovascular' => 'body'
		);
		
		foreach ( $category_scores as $category => $score ) {
			if ( isset( $category_to_pillar[ $category ] ) ) {
				$pillar = $category_to_pillar[ $category ];
				$pillar_scores[ $pillar ] = $score;
			}
		}
		
		return $pillar_scores;
	}
	
	/**
	 * Calculate overall score from pillar scores
	 */
	private function calculate_overall_score( $pillar_scores ) {
		$total = 0;
		$count = 0;
		
		foreach ( $pillar_scores as $score ) {
			$total += $score;
			$count++;
		}
		
		return $count > 0 ? round( $total / $count, 1 ) : 0;
	}
} 