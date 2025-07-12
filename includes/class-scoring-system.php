<?php
/**
 * ENNU Life Assessment Scoring System
 * 
 * Comprehensive scoring logic for all 5 assessment types
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Assessment_Scoring {
    
    /**
     * All scoring configurations, loaded from a config file.
     *
     * @var array
     */
    private static $all_scoring_configs = array();

    /**
     * Load all scoring rules from the centralized config file.
     */
    private static function load_all_scoring_configs() {
        if ( empty( self::$all_scoring_configs ) ) {
            $scoring_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessment-scoring.php';
            if ( file_exists( $scoring_file ) ) {
                self::$all_scoring_configs = require $scoring_file;
            }
        }
    }
    
    /**
     * Calculate assessment scores
     */
    public static function calculate_scores( $assessment_type, $responses ) {
        $scoring_config = self::get_scoring_config( $assessment_type );
        $category_scores = array();
        $total_score = 0;
        $total_weight = 0;
        
        foreach ( $responses as $question_key => $answer ) {
            if ( isset( $scoring_config[$question_key] ) ) {
                $question_config = $scoring_config[$question_key];
                $score = isset( $question_config['answers'][$answer] ) ? $question_config['answers'][$answer] : 5;
                $category = $question_config['category'];
                $weight = isset( $question_config['weight'] ) ? $question_config['weight'] : 1;
                
                if ( ! isset( $category_scores[$category] ) ) {
                    $category_scores[$category] = array( 'total' => 0, 'weight' => 0 );
                }
                
                $category_scores[$category]['total'] += $score * $weight;
                $category_scores[$category]['weight'] += $weight;
                $total_score += $score * $weight;
                $total_weight += $weight;
            }
        }
        
        // Calculate averages
        $final_category_scores = array();
        foreach ( $category_scores as $category => $data ) {
            $final_category_scores[$category] = $data['weight'] > 0 ? $data['total'] / $data['weight'] : 5;
        }
        
        $overall_score = $total_weight > 0 ? $total_score / $total_weight : 5;
        
        return array(
            'overall_score' => round( $overall_score, 1 ),
            'category_scores' => $final_category_scores,
            'total_questions' => count( $responses ),
            'assessment_type' => $assessment_type
        );
    }
    
    /**
     * Get scoring configuration for assessment type
     */
    private static function get_scoring_config( $assessment_type ) {
        self::load_all_scoring_configs();
        return self::$all_scoring_configs[ $assessment_type ] ?? array();
    }
    
    /**
     * Get category definitions for assessment type
     */
    public static function get_category_definitions( $assessment_type ) {
        switch ( $assessment_type ) {
            case 'hair_assessment':
                return array(
                    'Hair Health Status' => 'Current condition and severity of hair concerns',
                    'Progression Timeline' => 'How long hair changes have been occurring',
                    'Progression Rate' => 'Speed of hair loss or changes',
                    'Genetic Factors' => 'Family history and genetic predisposition',
                    'Lifestyle Factors' => 'Stress levels and lifestyle impact',
                    'Nutritional Support' => 'Diet quality and nutritional factors',
                    'Treatment History' => 'Previous treatments and experiences',
                    'Treatment Expectations' => 'Goals and realistic expectations'
                );
                
            case 'ed_treatment_assessment':
                return array(
                    'Condition Severity' => 'Severity and frequency of erectile dysfunction',
                    'Medical Factors' => 'Underlying health conditions affecting ED',
                    'Physical Health' => 'Exercise habits and cardiovascular fitness',
                    'Psychological Factors' => 'Stress levels and mental health impact',
                    'Psychosocial Factors' => 'Relationship status and social factors',
                    'Treatment Motivation' => 'Goals and motivation for treatment',
                    'Drug Interactions' => 'Current medications and potential interactions'
                );
                
            case 'weight_loss_assessment':
                return array(
                    'Current Status' => 'Current weight status and starting point',
                    'Physical Activity' => 'Exercise frequency and activity levels',
                    'Nutrition' => 'Diet quality and eating habits',
                    'Behavioral Patterns' => 'Eating behaviors and patterns',
                    'Lifestyle Factors' => 'Sleep quality and lifestyle choices',
                    'Psychological Factors' => 'Stress levels and mental health',
                    'Social Support' => 'Support system and environment',
                    'Motivation & Goals' => 'Motivation level and weight loss goals',
                    'Weight Loss History' => 'Previous attempts and experiences'
                );
                
            case 'health_assessment':
                return array(
                    'Current Health Status' => 'Overall health and wellness baseline',
                    'Vitality & Energy' => 'Energy levels and daily vitality',
                    'Physical Activity' => 'Exercise habits and fitness level',
                    'Nutrition' => 'Diet quality and nutritional habits',
                    'Sleep & Recovery' => 'Sleep quality and recovery patterns',
                    'Stress & Mental Health' => 'Stress management and mental wellness',
                    'Preventive Health' => 'Preventive care and health monitoring',
                    'Health Motivation' => 'Health goals and motivation for improvement'
                );
                
            case 'skin_assessment':
                return array(
                    'Skin Characteristics' => 'Natural skin type and characteristics',
                    'Skin Issues' => 'Current skin concerns and conditions',
                    'Environmental Factors' => 'Sun exposure and environmental impact',
                    'Skincare Habits' => 'Current skincare routine and practices',
                    'Skin Reactivity' => 'Sensitivity and product reactions',
                    'Lifestyle Impact' => 'Lifestyle factors affecting skin health',
                    'Treatment Accessibility' => 'Budget and treatment options',
                    'Treatment Goals' => 'Skincare goals and expectations'
                );
                
            default:
                return array();
        }
    }
    
    /**
     * Get score interpretation
     */
    public static function get_score_interpretation( $score ) {
        if ( $score >= 8.5 ) {
            return array(
                'level' => 'Excellent',
                'color' => '#10b981',
                'description' => 'Outstanding results expected with minimal intervention needed.'
            );
        } elseif ( $score >= 7.0 ) {
            return array(
                'level' => 'Good',
                'color' => '#3b82f6',
                'description' => 'Good foundation with some areas for optimization.'
            );
        } elseif ( $score >= 5.5 ) {
            return array(
                'level' => 'Fair',
                'color' => '#f59e0b',
                'description' => 'Moderate intervention recommended for best results.'
            );
        } elseif ( $score >= 3.5 ) {
            return array(
                'level' => 'Needs Attention',
                'color' => '#ef4444',
                'description' => 'Significant improvements recommended for optimal outcomes.'
            );
        } else {
            return array(
                'level' => 'Critical',
                'color' => '#dc2626',
                'description' => 'Immediate comprehensive intervention strongly recommended.'
            );
        }
    }
}

