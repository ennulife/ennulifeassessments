<?php
/**
 * ENNU Life Assessment Scoring Configuration
 *
 * This file centralizes all assessment scoring rules, making them easier to manage
 * and separating them from the plugin's logic.
 *
 * @package ENNU_Life
 * @version 24.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'hair_assessment' => array(
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
                'thinning' => 4,
                'receding' => 3,
                'bald_spots' => 2,
                'overall_loss' => 1
            )
        ),
        'duration' => array(
            'category' => 'Progression Timeline',
            'weight' => 2,
            'answers' => array(
                'recent' => 8,
                'moderate' => 6,
                'long' => 4,
                'very_long' => 2
            )
        ),
        'speed' => array(
            'category' => 'Progression Rate',
            'weight' => 2.5,
            'answers' => array(
                'slow' => 8,
                'moderate' => 6,
                'fast' => 3,
                'very_fast' => 1
            )
        ),
        'family_history' => array(
            'category' => 'Genetic Factors',
            'weight' => 2,
            'answers' => array(
                'none' => 9,
                'mother' => 6,
                'father' => 5,
                'both' => 3
            )
        ),
        'stress_level' => array(
            'category' => 'Lifestyle Factors',
            'weight' => 1.5,
            'answers' => array(
                'low' => 9,
                'moderate' => 7,
                'high' => 4,
                'very_high' => 2
            )
        ),
        'diet_quality' => array(
            'category' => 'Nutritional Support',
            'weight' => 1.5,
            'answers' => array(
                'excellent' => 9,
                'good' => 7,
                'fair' => 5,
                'poor' => 2
            )
        ),
        'previous_treatments' => array(
            'category' => 'Treatment History',
            'weight' => 1,
            'answers' => array(
                'none' => 7,
                'otc' => 6,
                'prescription' => 5,
                'procedures' => 4
            )
        ),
        'goals' => array(
            'category' => 'Treatment Expectations',
            'weight' => 1,
            'answers' => array(
                'stop_loss' => 8,
                'regrow' => 6,
                'thicken' => 7,
                'improve' => 8
            )
        )
    ),
    'ed_treatment_assessment' => array(
        'relationship_status' => array(
            'category' => 'Psychosocial Factors',
            'weight' => 1,
            'answers' => array(
                'single' => 6,
                'dating' => 7,
                'married' => 8,
                'divorced' => 5
            )
        ),
        'severity' => array(
            'category' => 'Condition Severity',
            'weight' => 3,
            'answers' => array(
                'mild' => 8,
                'moderate' => 6,
                'severe' => 3,
                'complete' => 1
            )
        ),
        'duration' => array(
            'category' => 'Timeline',
            'weight' => 2,
            'answers' => array(
                'recent' => 8,
                'moderate' => 6,
                'long' => 4,
                'very_long' => 2
            )
        ),
        'health_conditions' => array(
            'category' => 'Medical Factors',
            'weight' => 2.5,
            'answers' => array(
                'none' => 9,
                'diabetes' => 4,
                'heart' => 3,
                'hypertension' => 4
            )
        ),
        'previous_treatments' => array(
            'category' => 'Treatment History',
            'weight' => 1,
            'answers' => array(
                'none' => 8,
                'oral' => 6,
                'injections' => 4,
                'devices' => 5
            )
        ),
        'smoking_status' => array(
            'category' => 'Medical Factors',
            'weight' => 2,
            'answers' => array(
                'never' => 9,
                'former' => 7,
                'occasional' => 4,
                'regular' => 2
            )
        ),
        'exercise' => array(
            'category' => 'Physical Health',
            'weight' => 1.5,
            'answers' => array(
                'never' => 3,
                'rarely' => 5,
                'regularly' => 8,
                'daily' => 9
            )
        ),
        'stress_level' => array(
            'category' => 'Psychological Factors',
            'weight' => 2,
            'answers' => array(
                'low' => 9,
                'moderate' => 7,
                'high' => 4,
                'very_high' => 2
            )
        ),
        'goals' => array(
            'category' => 'Treatment Motivation',
            'weight' => 1,
            'answers' => array(
                'restore' => 8,
                'confidence' => 7,
                'performance' => 6,
                'relationship' => 8
            )
        ),
        'medications' => array(
            'category' => 'Drug Interactions',
            'weight' => 1.5,
            'answers' => array(
                'none' => 8,
                'blood_pressure' => 5,
                'antidepressants' => 4,
                'other' => 6
            )
        )
    ),
    'weight_loss_assessment' => array(
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
                'lose_10' => 8,
                'lose_30' => 7,
                'lose_50' => 6,
                'maintain' => 9
            )
        ),
        'current_weight' => array(
            'category' => 'Current Status',
            'weight' => 2.5,
            'answers' => array(
                'normal' => 8,
                'overweight' => 6,
                'obese' => 3,
                'underweight' => 7
            )
        ),
        'exercise_frequency' => array(
            'category' => 'Physical Activity',
            'weight' => 2.5,
            'answers' => array(
                'never' => 1,
                'rarely' => 3,
                'often' => 8,
                'daily' => 9
            )
        ),
        'diet_quality' => array(
            'category' => 'Nutrition',
            'weight' => 3,
            'answers' => array(
                'unhealthy' => 2,
                'balanced' => 6,
                'healthy' => 8,
                'strict' => 7
            )
        ),
        'eating_habits' => array(
            'category' => 'Behavioral Patterns',
            'weight' => 2,
            'answers' => array(
                'regular_meals' => 8,
                'skip_meals' => 4,
                'emotional_eating' => 3,
                'binge_eating' => 2
            )
        ),
        'sleep_quality' => array(
            'category' => 'Lifestyle Factors',
            'weight' => 1.5,
            'answers' => array(
                'less_5' => 3,
                '5_6' => 5,
                '7_8' => 9,
                'more_8' => 8
            )
        ),
        'stress_level' => array(
            'category' => 'Psychological Factors',
            'weight' => 1.5,
            'answers' => array(
                'low' => 9,
                'moderate' => 7,
                'high' => 4,
                'very_high' => 2
            )
        ),
        'support_system' => array(
            'category' => 'Social Support',
            'weight' => 1,
            'answers' => array(
                'none' => 3,
                'partner' => 7,
                'family' => 8,
                'professional' => 9
            )
        ),
        'previous_attempts' => array(
            'category' => 'Weight Loss History',
            'weight' => 1,
            'answers' => array(
                'no_success' => 3,
                'some_success' => 4,
                'good_success' => 6,
                'first_time' => 7
            )
        ),
        'motivation_level' => array(
            'category' => 'Readiness for Change',
            'weight' => 2,
            'answers' => array(
                'very_high' => 9,
                'high' => 8,
                'moderate' => 6,
                'low' => 3
            )
        ),
        'final_goals' => array(
            'category' => 'Long-term Vision',
            'weight' => 1,
            'answers' => array(
                'health' => 9,
                'confidence' => 7,
                'performance' => 8,
                'look_better' => 6
            )
        ),
        'current_weight_status' => array(
            'category' => 'Current Status',
            'weight' => 2.5,
            'answers' => array(
                'underweight' => 7,
                'normal' => 9,
                'overweight' => 6,
                'obese' => 3
            )
        ),
        'motivation' => array(
            'category' => 'Motivation & Goals',
            'weight' => 1.5,
            'answers' => array(
                'health' => 9,
                'appearance' => 6,
                'energy' => 8,
                'confidence' => 7
            )
        ),
        'health_conditions' => array(
            'category' => 'Medical Factors',
            'weight' => 2,
            'answers' => array(
                'none' => 9,
                'thyroid' => 5,
                'diabetes' => 4,
                'pcos' => 5
            )
        ),
        'medications' => array(
            'category' => 'Drug Interactions',
            'weight' => 1,
            'answers' => array(
                'none' => 8,
                'antidepressants' => 5,
                'steroids' => 4,
                'birth_control' => 6
            )
        )
    ),
    'health_assessment' => array(
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
                'excellent' => 9,
                'good' => 7,
                'fair' => 5,
                'poor' => 2
            )
        ),
        'energy_levels' => array(
            'category' => 'Vitality & Energy',
            'weight' => 2,
            'answers' => array(
                'low' => 2,
                'crash' => 4,
                'moderate' => 7,
                'high' => 9
            )
        ),
        'exercise_frequency' => array(
            'category' => 'Physical Activity',
            'weight' => 2.5,
            'answers' => array(
                'rarely' => 1,
                'sometimes' => 5,
                'often' => 8,
                'daily' => 9
            )
        ),
        'diet_quality' => array(
            'category' => 'Nutrition',
            'weight' => 2.5,
            'answers' => array(
                'processed' => 2,
                'average' => 5,
                'healthy' => 7,
                'very_healthy' => 9
            )
        ),
        'sleep_quality' => array(
            'category' => 'Sleep & Recovery',
            'weight' => 2,
            'answers' => array(
                'poor' => 3,
                'fair' => 5,
                'good' => 7,
                'excellent' => 9
            )
        ),
        'stress_management' => array(
            'category' => 'Stress & Mental Health',
            'weight' => 2,
            'answers' => array(
                'poorly' => 3,
                'somewhat' => 5,
                'well' => 7,
                'proactively' => 9
            )
        ),
        'preventive_care' => array(
            'category' => 'Preventive Health',
            'weight' => 1.5,
            'answers' => array(
                'never' => 2,
                'sometimes' => 6,
                'regularly' => 9
            )
        ),
        'health_goals' => array(
            'category' => 'Health Motivation',
            'weight' => 1,
            'answers' => array(
                'optimize' => 9,
                'prevent' => 8,
                'improve' => 7,
                'maintain' => 6
            )
        ),
        'lifestyle_factors' => array(
            'category' => 'Lifestyle Choices',
            'weight' => 1.5,
            'answers' => array(
                'healthy' => 9,
                'mostly_healthy' => 7,
                'mixed' => 5,
                'unhealthy' => 2
            )
        )
    ),
    'skin_assessment' => array(
        'gender' => array(
            'category' => 'Demographics',
            'weight' => 0.5,
            'answers' => array(
                'male' => 5,
                'female' => 5,
                'other' => 5
            )
        ),
        'primary_concern' => array(
            'category' => 'Primary Skin Issue',
            'weight' => 3,
            'answers' => array(
                'acne' => 3,
                'wrinkles' => 4,
                'dark_spots' => 5,
                'redness' => 4,
                'dryness' => 6
            )
        ),
        'sun_exposure' => array(
            'category' => 'Environmental Factors',
            'weight' => 2.5,
            'answers' => array(
                'rarely' => 9,
                'sometimes' => 6,
                'daily' => 3,
                'daily_no_spf' => 1
            )
        ),
        'skincare_routine' => array(
            'category' => 'Current Regimen',
            'weight' => 1,
            'answers' => array(
                'minimal' => 4,
                'basic' => 6,
                'advanced' => 8,
                'none' => 2
            )
        ),
        'skin_reactivity' => array(
            'category' => 'Skin Reactivity',
            'weight' => 1.5,
            'answers' => array(
                'never' => 9,
                'sometimes' => 6,
                'often' => 3
            )
        ),
        'skin_type' => array(
            'category' => 'Skin Characteristics',
            'weight' => 2,
            'answers' => array(
                'oily' => 6,
                'dry' => 6,
                'combination' => 7,
                'normal' => 8,
                'sensitive' => 5
            )
        ),
        'treatment_accessibility' => array(
            'category' => 'Treatment Accessibility',
            'weight' => 0.5,
            'answers' => array(
                'under_50' => 8,
                '50_100' => 7,
                '100_200' => 6,
                'over_200' => 5
            )
        )
    ),
); 