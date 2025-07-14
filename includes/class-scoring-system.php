<?php
/**
 * ENNU Life Assessment Scoring System
 *
 * This class is responsible for calculating scores based on the unified
 * assessment definitions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Assessment_Scoring {
    
    private static $all_definitions = array();

    private static function load_all_definitions() {
        if ( empty( self::$all_definitions ) ) {
            $definitions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessment-definitions.php';
            if ( file_exists( $definitions_file ) ) {
                self::$all_definitions = require $definitions_file;
            }
        }
    }

    public static function calculate_scores( $assessment_type, $responses ) {
        self::load_all_definitions();
        $assessment_questions = self::$all_definitions[$assessment_type] ?? [];

        if (empty($assessment_questions)) {
            return false;
        }

        $category_scores = array();
        $total_score = 0;
        $total_weight = 0;
        
        foreach ( $responses as $question_key => $answer ) {
            $question_def = $assessment_questions[$question_key] ?? null;

            if ( $question_def && isset($question_def['scoring']) ) {
                $scoring_rules = $question_def['scoring'];
                $category = $scoring_rules['category'] ?? 'General';
                $weight = $scoring_rules['weight'] ?? 1;

                $answers_to_process = is_array($answer) ? $answer : array($answer);

                foreach ($answers_to_process as $single_answer) {
                    $score = $scoring_rules['answers'][$single_answer] ?? 0;

                    if ( ! isset( $category_scores[$category] ) ) {
                        $category_scores[$category] = array( 'total' => 0, 'weight' => 0, 'count' => 0 );
                    }

                    $category_scores[$category]['total'] += $score * $weight;
                    $category_scores[$category]['weight'] += $weight;
                    $category_scores[$category]['count']++;

                    if ($weight > 0) {
                        $total_score += $score * $weight;
                        $total_weight += $weight;
                    }
                }
            }
        }
        
        $final_category_scores = array();
        foreach ( $category_scores as $category => $data ) {
            if ($data['weight'] > 0) {
                $final_category_scores[$category] = round($data['total'] / $data['weight'], 1);
            }
        }
        
        $overall_score = $total_weight > 0 ? round($total_score / $total_weight, 1) : 0;
        
        // --- PHASE 2: CALCULATE PILLAR SCORES ---
        $pillar_map = self::get_health_pillar_map();
        $pillar_scores = [];
        $pillar_totals = [];
        $pillar_counts = [];

        foreach ($pillar_map as $pillar_name => $categories) {
            $pillar_totals[$pillar_name] = 0;
            $pillar_counts[$pillar_name] = 0;
        }

        foreach ($final_category_scores as $category => $score) {
            foreach ($pillar_map as $pillar_name => $categories) {
                if (in_array($category, $categories)) {
                    $pillar_totals[$pillar_name] += $score;
                    $pillar_counts[$pillar_name]++;
                    break; // Move to the next category once pillar is found
                }
            }
        }

        foreach ($pillar_totals as $pillar_name => $total) {
            if ($pillar_counts[$pillar_name] > 0) {
                $pillar_scores[$pillar_name] = round($total / $pillar_counts[$pillar_name], 1);
            } else {
                $pillar_scores[$pillar_name] = 0; // Default to 0 if no categories for this pillar
            }
        }
        // --- END PHASE 2 ---

        return [
            'overall_score' => $overall_score,
            'category_scores' => $final_category_scores,
            'pillar_scores' => $pillar_scores, // Add pillar scores to the return value
        ];
    }
    
    public static function get_answer_score($assessment_type, $question_key, $answer_value) {
        self::load_all_definitions();
        $assessment_questions = self::$all_definitions[$assessment_type] ?? [];
        $question_def = $assessment_questions[$question_key] ?? null;

        if ($question_def && isset($question_def['scoring']['answers'][$answer_value])) {
            return $question_def['scoring']['answers'][$answer_value];
        }

        return null;
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

    public static function get_health_pillar_map() {
        return [
            'mind' => [
                'Psychological Factors',
                'Treatment Motivation',
                'Stress & Mental Health',
                'Readiness for Change',
                'Treatment Expectations',
                'Social Support',
                'Motivation & Goals',
                'Health Motivation',
                'Vitality & Drive',
                'Mental Clarity',
                'Mood & Wellbeing',
            ],
            'body' => [
                'Condition Severity',
                'Medical Factors',
                'Drug Interactions',
                'Genetic Factors',
                'Nutritional Support',
                'Internal Health',
                'Current Health Status',
                'Vitality & Energy',
                'Sleep & Recovery',
                'Anabolic Response',
                'Symptom Severity',
                'Menopause Stage',
                'Physical Symptoms',
            ],
            'lifestyle' => [
                'Physical Health',
                'Treatment History',
                'Progression Timeline',
                'Symptom Pattern',
                'Preventive Health',
                'Lifestyle Choices',
                'Environmental Factors',
                'Skincare Habits',
                'Weight Loss History',
                'Behavioral Patterns',
                'Physical Activity',
                'Nutrition',
                'Sleep Duration',
                'Sleep Quality',
                'Sleep Continuity',
                'Sleep Dependency',
                'Current Regimen',
                'Lifestyle & Diet',
            ],
            'aesthetics' => [
                'Hair Health Status',
                'Primary Skin Issue',
                'Skin Characteristics',
                'Current Status',
                'Progression Rate',
                'Skin Reactivity',
            ]
        ];
    }

    public static function calculate_ennu_life_score($user_id) {
        self::load_all_definitions();
        
        $assessment_types = array_keys(self::$all_definitions);
        $all_pillar_scores = [];

        // Initialize accumulators for each pillar
        foreach (self::get_health_pillar_map() as $pillar_key => $categories) {
            $all_pillar_scores[$pillar_key] = [];
        }

        // 1. Collect all pillar scores from all completed assessments
        foreach ($assessment_types as $assessment_type) {
            $pillar_scores = get_user_meta($user_id, 'ennu_' . $assessment_type . '_pillar_scores', true);
            if (is_array($pillar_scores) && !empty($pillar_scores)) {
                foreach ($pillar_scores as $pillar_name => $score) {
                    if (isset($all_pillar_scores[$pillar_name])) {
                        $all_pillar_scores[$pillar_name][] = $score;
                    }
                }
            }
        }

        // 2. Calculate the average for each pillar
        $average_pillar_scores = [];
        foreach ($all_pillar_scores as $pillar_name => $scores) {
            if (!empty($scores)) {
                $average_pillar_scores[$pillar_name] = array_sum($scores) / count($scores);
            } else {
                $average_pillar_scores[$pillar_name] = 0;
            }
        }
        
        // 3. Apply strategic weights
        $weights = [
            'mind' => 0.3,
            'body' => 0.3,
            'lifestyle' => 0.3,
            'aesthetics' => 0.1
        ];

        $ennu_life_score = 0;
        foreach ($average_pillar_scores as $pillar_name => $avg_score) {
            if (isset($weights[$pillar_name])) {
                $ennu_life_score += $avg_score * $weights[$pillar_name];
            }
        }

        return round($ennu_life_score, 1);
    }

    public static function calculate_average_pillar_scores($user_id) {
        self::load_all_definitions();
        
        $assessment_types = array_keys(self::$all_definitions);
        $all_pillar_scores = [];

        foreach (self::get_health_pillar_map() as $pillar_key => $categories) {
            $all_pillar_scores[$pillar_key] = [];
        }

        foreach ($assessment_types as $assessment_type) {
            $pillar_scores = get_user_meta($user_id, 'ennu_' . $assessment_type . '_pillar_scores', true);
            if (is_array($pillar_scores) && !empty($pillar_scores)) {
                foreach ($pillar_scores as $pillar_name => $score) {
                    if (isset($all_pillar_scores[$pillar_name])) {
                        $all_pillar_scores[$pillar_name][] = $score;
                    }
                }
            }
        }

        $average_pillar_scores = [];
        foreach ($all_pillar_scores as $pillar_name => $scores) {
            if (!empty($scores)) {
                $average_pillar_scores[ucfirst($pillar_name)] = round(array_sum($scores) / count($scores), 1);
            } else {
                $average_pillar_scores[ucfirst($pillar_name)] = 0;
            }
        }
        
        return $average_pillar_scores;
    }
}

