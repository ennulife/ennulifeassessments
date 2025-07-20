<?php
/**
 * ENNU Life Objective Engine
 * Applies biomarker-based actuality adjustments
 * Implements the third engine in the "Scoring Symphony"
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Objective_Engine {
    
    private $user_biomarkers;
    private $biomarker_profiles;
    private $adjustment_log;
    
    public function __construct( $user_biomarkers ) {
        $this->user_biomarkers = is_array( $user_biomarkers ) ? $user_biomarkers : array();
        $this->load_biomarker_profiles();
        $this->adjustment_log = array();
        
        error_log( 'ENNU Objective Engine: Initialized with ' . count( $this->user_biomarkers ) . ' biomarkers' );
    }
    
    private function load_biomarker_profiles() {
        $biomarker_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/ennu-life-core-biomarkers.php';
        $this->biomarker_profiles = file_exists( $biomarker_file ) ? require $biomarker_file : array();
    }
    
    public function apply_biomarker_actuality_adjustments( $base_pillar_scores ) {
        if ( empty( $this->user_biomarkers ) || empty( $this->biomarker_profiles ) ) {
            error_log( 'ENNU Objective Engine: No biomarkers or profiles available' );
            return $base_pillar_scores;
        }
        
        $adjusted_scores = $base_pillar_scores;
        $pillar_adjustments = array();
        
        foreach ( $this->user_biomarkers as $biomarker_name => $biomarker_data ) {
            $adjustment = $this->calculate_biomarker_adjustment( $biomarker_name, $biomarker_data );
            
            if ( $adjustment !== null ) {
                foreach ( $adjustment['pillar_impacts'] as $pillar => $impact_weight ) {
                    if ( ! isset( $pillar_adjustments[$pillar] ) ) {
                        $pillar_adjustments[$pillar] = 1.0;
                    }
                    
                    $pillar_adjustments[$pillar] *= (1 + ($adjustment['adjustment_value'] * $impact_weight));
                }
                
                $this->adjustment_log[] = $adjustment;
            }
        }
        
        foreach ( $pillar_adjustments as $pillar => $multiplier ) {
            if ( isset( $adjusted_scores[$pillar] ) ) {
                $original_score = $adjusted_scores[$pillar];
                $adjusted_scores[$pillar] = min( $original_score * $multiplier, 10.0 );
                
                error_log( "ENNU Objective Engine: Applied {$multiplier}x multiplier to {$pillar} pillar ({$original_score} -> {$adjusted_scores[$pillar]})" );
            }
        }
        
        return $adjusted_scores;
    }
    
    private function calculate_biomarker_adjustment( $biomarker_name, $biomarker_data ) {
        $profile = $this->find_biomarker_profile( $biomarker_name );
        
        if ( ! $profile ) {
            return null;
        }
        
        $value = $biomarker_data['value'] ?? null;
        $range_classification = $this->classify_biomarker_range( $value, $profile );
        $adjustment_value = $this->get_adjustment_value( $range_classification, $profile );
        
        return array(
            'biomarker_name' => $biomarker_name,
            'value' => $value,
            'range_classification' => $range_classification,
            'adjustment_value' => $adjustment_value,
            'pillar_impacts' => $this->get_pillar_impacts_from_profile( $profile ),
            'impact_weight' => $profile['impact_weight'] ?? 'moderate'
        );
    }
    
    private function find_biomarker_profile( $biomarker_name ) {
        foreach ( $this->biomarker_profiles as $category => $biomarkers ) {
            if ( is_array( $biomarkers ) ) {
                foreach ( $biomarkers as $key => $profile ) {
                    if ( $key === $biomarker_name || 
                         (isset( $profile['name'] ) && $profile['name'] === $biomarker_name) ) {
                        return $profile;
                    }
                }
            }
        }
        
        return null;
    }
    
    private function get_pillar_impacts_from_profile( $profile ) {
        $pillar_impacts = array();
        
        if ( isset( $profile['pillar_impact'] ) ) {
            if ( is_array( $profile['pillar_impact'] ) ) {
                foreach ( $profile['pillar_impact'] as $pillar ) {
                    $pillar_impacts[$pillar] = 1.0;
                }
            } else {
                $pillar_impacts[$profile['pillar_impact']] = 1.0;
            }
        }
        
        if ( isset( $profile['health_vectors'] ) && is_array( $profile['health_vectors'] ) ) {
            $vector_to_pillar_map = array(
                'Weight Loss' => 'Lifestyle',
                'Strength' => 'Body',
                'Heart Health' => 'Body',
                'Hormones' => 'Body',
                'Energy' => 'Lifestyle',
                'Cognitive Health' => 'Mind',
                'Longevity' => 'Lifestyle',
                'Libido' => 'Mind'
            );
            
            foreach ( $profile['health_vectors'] as $vector => $weight ) {
                if ( isset( $vector_to_pillar_map[$vector] ) ) {
                    $pillar = $vector_to_pillar_map[$vector];
                    $pillar_impacts[$pillar] = $weight;
                }
            }
        }
        
        return $pillar_impacts;
    }
    
    private function classify_biomarker_range( $value, $profile ) {
        if ( $value === null ) {
            return 'unknown';
        }
        
        $optimal_range = $profile['optimal_range'] ?? null;
        $suboptimal_range = $profile['suboptimal_range'] ?? null;
        $poor_range = $profile['poor_range'] ?? null;
        
        if ( $this->value_in_range( $value, $optimal_range ) ) {
            return 'optimal';
        } elseif ( $this->value_in_range( $value, $suboptimal_range ) ) {
            return 'suboptimal';
        } elseif ( $this->value_in_range( $value, $poor_range ) ) {
            return 'poor';
        }
        
        return 'unknown';
    }
    
    private function value_in_range( $value, $range ) {
        if ( ! $range ) {
            return false;
        }
        
        if ( is_string( $range ) ) {
            if ( strpos( $range, '-' ) !== false ) {
                $parts = explode( '-', $range );
                if ( count( $parts ) === 2 ) {
                    $min = floatval( trim( $parts[0] ) );
                    $max = floatval( trim( $parts[1] ) );
                    return $value >= $min && $value <= $max;
                }
            } elseif ( strpos( $range, '<' ) === 0 ) {
                return $value < floatval( substr( $range, 1 ) );
            } elseif ( strpos( $range, '>' ) === 0 ) {
                return $value > floatval( substr( $range, 1 ) );
            } elseif ( strpos( $range, '≥' ) !== false ) {
                return $value >= floatval( str_replace( '≥', '', $range ) );
            } elseif ( strpos( $range, '≤' ) !== false ) {
                return $value <= floatval( str_replace( '≤', '', $range ) );
            }
        }
        
        return false;
    }
    
    private function get_adjustment_value( $range_classification, $profile ) {
        $impact_weight = $profile['impact_weight'] ?? 'moderate';
        
        $adjustment_matrix = array(
            'optimal' => array(
                'critical' => 0.05,
                'significant' => 0.025,
                'moderate' => 0.01
            ),
            'suboptimal' => array(
                'critical' => -0.10,
                'significant' => -0.05,
                'moderate' => -0.025
            ),
            'poor' => array(
                'critical' => -0.15,
                'significant' => -0.10,
                'moderate' => -0.05
            )
        );
        
        return $adjustment_matrix[$range_classification][$impact_weight] ?? 0;
    }
    
    public function get_adjustment_log() {
        return $this->adjustment_log;
    }
    
    public function get_adjustment_summary() {
        $summary = array(
            'total_biomarkers' => count( $this->user_biomarkers ),
            'adjustments_applied' => count( $this->adjustment_log ),
            'pillars_adjusted' => array(),
            'total_adjustment_value' => 0
        );
        
        foreach ( $this->adjustment_log as $log_entry ) {
            foreach ( $log_entry['pillar_impacts'] as $pillar => $impact ) {
                $summary['pillars_adjusted'][] = $pillar;
            }
            $summary['total_adjustment_value'] += abs( $log_entry['adjustment_value'] );
        }
        
        $summary['pillars_adjusted'] = array_unique( $summary['pillars_adjusted'] );
        
        return $summary;
    }
    
    public function get_user_explanation() {
        if ( empty( $this->user_biomarkers ) ) {
            return 'No biomarker data available. Upload your lab results to unlock objective biomarker-based scoring adjustments.';
        }
        
        $summary = $this->get_adjustment_summary();
        
        if ( $summary['adjustments_applied'] === 0 ) {
            return 'Your biomarker data was analyzed but no significant adjustments were applied to your pillar scores.';
        }
        
        $pillars_text = implode( ', ', $summary['pillars_adjusted'] );
        $adjustment_percentage = round( $summary['total_adjustment_value'] * 100, 1 );
        
        return "Your {$summary['total_biomarkers']} biomarkers provided objective adjustments to your {$pillars_text} pillar scores, with a total adjustment impact of {$adjustment_percentage}%.";
    }
}
