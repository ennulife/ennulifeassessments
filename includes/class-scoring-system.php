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
        switch ( $assessment_type ) {
            case 'hair_assessment':
                return array(
                    // Question 1: DOB (not scored)
                    'gender' => array(
                        'category' => 'Demographics',
                        'weight' => 0.5,
                        'answers' => array(
                            'male' => 5,
                            'female' => 5,
                            'other' => 5
                        )
                    ),
                    'hair_concerns' => array(
                        'category' => 'Hair Health Status',
                        'weight' => 3,
                        'answers' => array(
                            'thinning' => 4,      // Moderate concern
                            'receding' => 3,      // Higher concern
                            'bald_spots' => 2,    // Significant concern
                            'overall_loss' => 1   // Most severe
                        )
                    ),
                    'duration' => array(
                        'category' => 'Progression Timeline',
                        'weight' => 2,
                        'answers' => array(
                            'recent' => 8,        // Early intervention possible
                            'moderate' => 6,      // Still treatable
                            'long' => 4,          // More challenging
                            'very_long' => 2      // Advanced stage
                        )
                    ),
                    'speed' => array(
                        'category' => 'Progression Rate',
                        'weight' => 2.5,
                        'answers' => array(
                            'slow' => 8,          // Good prognosis
                            'moderate' => 6,      // Manageable
                            'fast' => 3,          // Urgent intervention needed
                            'very_fast' => 1      // Critical
                        )
                    ),
                    'family_history' => array(
                        'category' => 'Genetic Factors',
                        'weight' => 2,
                        'answers' => array(
                            'none' => 9,          // Best genetic profile
                            'mother' => 6,        // Moderate genetic risk
                            'father' => 5,        // Higher genetic risk
                            'both' => 3           // Highest genetic risk
                        )
                    ),
                    'stress_level' => array(
                        'category' => 'Lifestyle Factors',
                        'weight' => 1.5,
                        'answers' => array(
                            'low' => 9,           // Optimal for hair health
                            'moderate' => 7,      // Manageable impact
                            'high' => 4,          // Negative impact
                            'very_high' => 2      // Severe impact
                        )
                    ),
                    'diet_quality' => array(
                        'category' => 'Nutritional Support',
                        'weight' => 1.5,
                        'answers' => array(
                            'excellent' => 9,     // Optimal nutrition
                            'good' => 7,          // Good foundation
                            'fair' => 5,          // Room for improvement
                            'poor' => 2           // Nutritional deficiency likely
                        )
                    ),
                    'previous_treatments' => array(
                        'category' => 'Treatment History',
                        'weight' => 1,
                        'answers' => array(
                            'none' => 7,          // Fresh start
                            'otc' => 6,           // Some experience
                            'prescription' => 5,   // Previous medical treatment
                            'procedures' => 4      // Advanced treatments tried
                        )
                    ),
                    'goals' => array(
                        'category' => 'Treatment Expectations',
                        'weight' => 1,
                        'answers' => array(
                            'stop_loss' => 8,     // Realistic goal
                            'regrow' => 6,        // Moderate expectation
                            'thicken' => 7,       // Achievable goal
                            'improve' => 8        // Realistic overall goal
                        )
                    )
                );
                
            case 'ed_treatment_assessment':
                return array(
                    // Question 1: DOB (not scored)
                    'relationship_status' => array(
                        'category' => 'Psychosocial Factors',
                        'weight' => 1,
                        'answers' => array(
                            'single' => 6,        // Less relationship pressure
                            'dating' => 7,        // Moderate motivation
                            'married' => 8,       // High motivation, support
                            'divorced' => 5       // Potential stress factor
                        )
                    ),
                    'severity' => array(
                        'category' => 'Condition Severity',
                        'weight' => 3,
                        'answers' => array(
                            'mild' => 8,          // Easily treatable
                            'moderate' => 6,      // Standard treatment
                            'severe' => 3,        // Requires intensive treatment
                            'complete' => 1       // Most challenging
                        )
                    ),
                    'frequency' => array(
                        'category' => 'Symptom Pattern',
                        'weight' => 2.5,
                        'answers' => array(
                            'rarely' => 8,        // Occasional issue
                            'sometimes' => 6,     // Intermittent problem
                            'often' => 4,         // Regular issue
                            'always' => 2         // Persistent problem
                        )
                    ),
                    'duration' => array(
                        'category' => 'Timeline',
                        'weight' => 2,
                        'answers' => array(
                            'recent' => 8,        // Recent onset, better prognosis
                            'months' => 6,        // Developing issue
                            'years' => 4,         // Established condition
                            'lifelong' => 3       // Chronic condition
                        )
                    ),
                    'health_conditions' => array(
                        'category' => 'Medical Factors',
                        'weight' => 2.5,
                        'answers' => array(
                            'none' => 9,          // No complicating factors
                            'diabetes' => 4,      // Significant impact
                            'heart_disease' => 3, // Major complication
                            'multiple' => 2       // Complex medical situation
                        )
                    ),
                    'exercise' => array(
                        'category' => 'Physical Health',
                        'weight' => 1.5,
                        'answers' => array(
                            'daily' => 9,         // Excellent cardiovascular health
                            'regularly' => 8,     // Good fitness level
                            'rarely' => 5,        // Poor fitness
                            'never' => 3          // Sedentary lifestyle
                        )
                    ),
                    'stress_level' => array(
                        'category' => 'Psychological Factors',
                        'weight' => 2,
                        'answers' => array(
                            'low' => 9,           // Minimal psychological impact
                            'moderate' => 7,      // Some stress factor
                            'high' => 4,          // Significant stress
                            'very_high' => 2      // Major psychological barrier
                        )
                    ),
                    'goals' => array(
                        'category' => 'Treatment Motivation',
                        'weight' => 1,
                        'answers' => array(
                            'restore' => 8,       // Clear medical goal
                            'confidence' => 7,    // Psychological benefit
                            'performance' => 6,   // Performance focus
                            'relationship' => 8   // Relationship improvement
                        )
                    ),
                    'medications' => array(
                        'category' => 'Drug Interactions',
                        'weight' => 1.5,
                        'answers' => array(
                            'none' => 8,          // No contraindications
                            'blood_pressure' => 5, // Potential interactions
                            'antidepressants' => 4, // May worsen ED
                            'other' => 6          // Case-by-case evaluation
                        )
                    )
                );
                
            case 'weight_loss_assessment':
                return array(
                    // Question 1: DOB (not scored)
                    'gender' => array(
                        'category' => 'Demographics',
                        'weight' => 0.5,
                        'answers' => array(
                            'male' => 5,
                            'female' => 5,
                            'other' => 5
                        )
                    ),
                    'primary_goal' => array(
                        'category' => 'Motivation & Goals',
                        'weight' => 2,
                        'answers' => array(
                            'lose_weight' => 8,   // Clear, achievable goal
                            'build_muscle' => 7,  // Positive body composition goal
                            'improve_health' => 9, // Best motivation for success
                            'look_better' => 6    // Aesthetic motivation
                        )
                    ),
                    'current_weight' => array(
                        'category' => 'Current Status',
                        'weight' => 2.5,
                        'answers' => array(
                            'normal' => 8,        // Maintenance/optimization
                            'overweight' => 6,    // Moderate intervention needed
                            'obese' => 3,         // Significant intervention required
                            'underweight' => 7    // Different approach needed
                        )
                    ),
                    'exercise_frequency' => array(
                        'category' => 'Physical Activity',
                        'weight' => 2.5,
                        'answers' => array(
                            'daily' => 9,         // Excellent activity level
                            'frequent' => 8,      // Good activity level
                            'occasional' => 5,    // Inconsistent activity
                            'rare' => 3,          // Sedentary lifestyle
                            'never' => 1          // No physical activity
                        )
                    ),
                    'diet_quality' => array(
                        'category' => 'Nutrition',
                        'weight' => 3,
                        'answers' => array(
                            'excellent' => 9,     // Optimal nutrition
                            'good' => 7,          // Good foundation
                            'fair' => 5,          // Needs improvement
                            'poor' => 2           // Major dietary issues
                        )
                    ),
                    'eating_habits' => array(
                        'category' => 'Behavioral Patterns',
                        'weight' => 2,
                        'answers' => array(
                            'regular_meals' => 8, // Structured eating
                            'skip_meals' => 4,    // Irregular patterns
                            'emotional_eating' => 3, // Psychological factors
                            'binge_eating' => 2   // Disordered eating
                        )
                    ),
                    'sleep_quality' => array(
                        'category' => 'Lifestyle Factors',
                        'weight' => 1.5,
                        'answers' => array(
                            'excellent' => 9,     // Optimal recovery
                            'good' => 7,          // Adequate sleep
                            'fair' => 5,          // Sleep issues
                            'poor' => 3           // Chronic sleep deprivation
                        )
                    ),
                    'stress_level' => array(
                        'category' => 'Psychological Factors',
                        'weight' => 1.5,
                        'answers' => array(
                            'low' => 9,           // Minimal stress impact
                            'moderate' => 7,      // Manageable stress
                            'high' => 4,          // Stress affects weight
                            'very_high' => 2      // Chronic stress
                        )
                    ),
                    'support_system' => array(
                        'category' => 'Social Support',
                        'weight' => 1,
                        'answers' => array(
                            'strong' => 9,        // Excellent support
                            'moderate' => 7,      // Some support
                            'limited' => 5,       // Minimal support
                            'none' => 3           // No support system
                        )
                    ),
                    'previous_attempts' => array(
                        'category' => 'Weight Loss History',
                        'weight' => 1,
                        'answers' => array(
                            'none' => 7,          // Fresh start
                            'few' => 6,           // Some experience
                            'many' => 4,          // Multiple failed attempts
                            'yo_yo' => 3          // Cycle of weight loss/gain
                        )
                    ),
                    'motivation_level' => array(
                        'category' => 'Readiness for Change',
                        'weight' => 2,
                        'answers' => array(
                            'very_high' => 9,     // Highly motivated
                            'high' => 8,          // Good motivation
                            'moderate' => 6,      // Some motivation
                            'low' => 3            // Low motivation
                        )
                    ),
                    'final_goals' => array(
                        'category' => 'Long-term Vision',
                        'weight' => 1,
                        'answers' => array(
                            'health' => 9,        // Health-focused
                            'confidence' => 7,    // Self-esteem focused
                            'performance' => 8,   // Performance focused
                            'look_better' => 6    // Appearance focused
                        )
                    )
                );
                
            case 'health_assessment':
                return array(
                    // Question 1: DOB (not scored)
                    'gender' => array(
                        'category' => 'Demographics',
                        'weight' => 0.5,
                        'answers' => array(
                            'male' => 5,
                            'female' => 5,
                            'other' => 5
                        )
                    ),
                    'overall_health' => array(
                        'category' => 'Current Health Status',
                        'weight' => 3,
                        'answers' => array(
                            'excellent' => 9,     // Optimal health
                            'good' => 7,          // Good health foundation
                            'fair' => 5,          // Some health concerns
                            'poor' => 2           // Significant health issues
                        )
                    ),
                    'energy_levels' => array(
                        'category' => 'Vitality & Energy',
                        'weight' => 2,
                        'answers' => array(
                            'high' => 9,          // Excellent energy
                            'moderate' => 7,      // Good energy levels
                            'low' => 4,           // Energy deficiency
                            'very_low' => 2       // Chronic fatigue
                        )
                    ),
                    'exercise_frequency' => array(
                        'category' => 'Physical Activity',
                        'weight' => 2.5,
                        'answers' => array(
                            'daily' => 9,         // Excellent activity
                            'frequent' => 8,      // Good activity level
                            'occasional' => 5,    // Inconsistent activity
                            'rare' => 3,          // Minimal activity
                            'never' => 1          // Sedentary
                        )
                    ),
                    'diet_quality' => array(
                        'category' => 'Nutrition',
                        'weight' => 2.5,
                        'answers' => array(
                            'excellent' => 9,     // Optimal nutrition
                            'good' => 7,          // Good dietary habits
                            'fair' => 5,          // Average diet
                            'poor' => 2           // Poor nutrition
                        )
                    ),
                    'sleep_quality' => array(
                        'category' => 'Sleep & Recovery',
                        'weight' => 2,
                        'answers' => array(
                            'excellent' => 9,     // Optimal sleep
                            'good' => 7,          // Good sleep quality
                            'fair' => 5,          // Sleep issues
                            'poor' => 3           // Chronic sleep problems
                        )
                    ),
                    'stress_management' => array(
                        'category' => 'Stress & Mental Health',
                        'weight' => 2,
                        'answers' => array(
                            'excellent' => 9,     // Great stress management
                            'good' => 7,          // Good coping skills
                            'fair' => 5,          // Some stress issues
                            'poor' => 3           // Poor stress management
                        )
                    ),
                    'preventive_care' => array(
                        'category' => 'Preventive Health',
                        'weight' => 1.5,
                        'answers' => array(
                            'regular' => 9,       // Proactive healthcare
                            'occasional' => 6,    // Some preventive care
                            'rare' => 4,          // Minimal preventive care
                            'never' => 2          // No preventive care
                        )
                    ),
                    'health_goals' => array(
                        'category' => 'Health Motivation',
                        'weight' => 1,
                        'answers' => array(
                            'optimize' => 9,      // Optimization focus
                            'prevent' => 8,       // Prevention focus
                            'improve' => 7,       // Improvement focus
                            'maintain' => 6       // Maintenance focus
                        )
                    ),
                    'lifestyle_factors' => array(
                        'category' => 'Lifestyle Choices',
                        'weight' => 1.5,
                        'answers' => array(
                            'healthy' => 9,       // Healthy lifestyle
                            'mostly_healthy' => 7, // Generally healthy
                            'mixed' => 5,         // Mixed lifestyle
                            'unhealthy' => 2      // Unhealthy lifestyle
                        )
                    )
                );
                
            case 'skin_assessment':
                return array(
                    // Question 1: DOB (not scored)
                    'gender' => array(
                        'category' => 'Demographics',
                        'weight' => 0.5,
                        'answers' => array(
                            'male' => 5,
                            'female' => 5,
                            'other' => 5
                        )
                    ),
                    'skin_type' => array(
                        'category' => 'Skin Characteristics',
                        'weight' => 2,
                        'answers' => array(
                            'normal' => 8,        // Balanced skin
                            'dry' => 6,           // Needs hydration
                            'oily' => 6,          // Needs oil control
                            'combination' => 7,   // Mixed needs
                            'sensitive' => 5      // Requires gentle care
                        )
                    ),
                    'main_concerns' => array(
                        'category' => 'Skin Issues',
                        'weight' => 3,
                        'answers' => array(
                            'acne' => 4,          // Active skin condition
                            'aging' => 6,         // Natural aging process
                            'pigmentation' => 5,  // Discoloration issues
                            'texture' => 6,       // Surface irregularities
                            'sensitivity' => 5    // Reactive skin
                        )
                    ),
                    'sun_exposure' => array(
                        'category' => 'Environmental Factors',
                        'weight' => 2,
                        'answers' => array(
                            'minimal' => 8,       // Good sun protection
                            'moderate' => 6,      // Some sun exposure
                            'frequent' => 4,      // High sun exposure
                            'excessive' => 2      // Dangerous sun exposure
                        )
                    ),
                    'current_routine' => array(
                        'category' => 'Skincare Habits',
                        'weight' => 2,
                        'answers' => array(
                            'extensive' => 8,     // Comprehensive routine
                            'moderate' => 7,      // Good routine
                            'basic' => 5,         // Minimal routine
                            'none' => 2           // No skincare routine
                        )
                    ),
                    'product_sensitivity' => array(
                        'category' => 'Skin Reactivity',
                        'weight' => 1.5,
                        'answers' => array(
                            'none' => 8,          // No sensitivities
                            'mild' => 6,          // Minor sensitivities
                            'moderate' => 4,      // Some sensitivities
                            'severe' => 2         // Multiple sensitivities
                        )
                    ),
                    'lifestyle_factors' => array(
                        'category' => 'Lifestyle Impact',
                        'weight' => 1.5,
                        'answers' => array(
                            'healthy' => 9,       // Skin-healthy lifestyle
                            'mostly_healthy' => 7, // Generally good
                            'mixed' => 5,         // Some negative factors
                            'unhealthy' => 3      // Multiple negative factors
                        )
                    ),
                    'budget' => array(
                        'category' => 'Treatment Accessibility',
                        'weight' => 1,
                        'answers' => array(
                            'luxury' => 8,        // All options available
                            'premium' => 7,       // Most options available
                            'moderate' => 6,      // Good options available
                            'budget' => 5         // Limited options
                        )
                    ),
                    'skin_goals' => array(
                        'category' => 'Treatment Goals',
                        'weight' => 1,
                        'answers' => array(
                            'clear_skin' => 8,    // Achievable goal
                            'anti_aging' => 7,    // Long-term goal
                            'glow' => 8,          // Achievable goal
                            'maintenance' => 6    // Conservative goal
                        )
                    )
                );
                
            default:
                return array();
        }
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

