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
        'frequency' => array(
            'category' => 'Symptom Pattern',
            'weight' => 2.5,
            'answers' => array(
                'rarely' => 8,
                'sometimes' => 6,
                'often' => 4,
                'always' => 2
            )
        ),
        'duration' => array(
            'category' => 'Timeline',
            'weight' => 2,
            'answers' => array(
                'recent' => 8,
                'months' => 6,
                'years' => 4,
                'lifelong' => 3
            )
        ),
        'health_conditions' => array(
            'category' => 'Medical Factors',
            'weight' => 2.5,
            'answers' => array(
                'none' => 9,
                'diabetes' => 4,
                'heart_disease' => 3,
                'multiple' => 2
            )
        ),
        'exercise' => array(
            'category' => 'Physical Health',
            'weight' => 1.5,
            'answers' => array(
                'daily' => 9,
                'regularly' => 8,
                'rarely' => 5,
                'never' => 3
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
                'lose_weight' => 8,
                'build_muscle' => 7,
                'improve_health' => 9,
                'look_better' => 6
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
                'daily' => 9,
                'frequent' => 8,
                'occasional' => 5,
                'rare' => 3,
                'never' => 1
            )
        ),
        'diet_quality' => array(
            'category' => 'Nutrition',
            'weight' => 3,
            'answers' => array(
                'excellent' => 9,
                'good' => 7,
                'fair' => 5,
                'poor' => 2
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
                'excellent' => 9,
                'good' => 7,
                'fair' => 5,
                'poor' => 3
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
                'strong' => 9,
                'moderate' => 7,
                'limited' => 5,
                'none' => 3
            )
        ),
        'previous_attempts' => array(
            'category' => 'Weight Loss History',
            'weight' => 1,
            'answers' => array(
                'none' => 7,
                'few' => 6,
                'many' => 4,
                'yo_yo' => 3
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
                'high' => 9,
                'moderate' => 7,
                'low' => 4,
                'very_low' => 2
            )
        ),
        'exercise_frequency' => array(
            'category' => 'Physical Activity',
            'weight' => 2.5,
            'answers' => array(
                'daily' => 9,
                'frequent' => 8,
                'occasional' => 5,
                'rare' => 3,
                'never' => 1
            )
        ),
        'diet_quality' => array(
            'category' => 'Nutrition',
            'weight' => 2.5,
            'answers' => array(
                'excellent' => 9,
                'good' => 7,
                'fair' => 5,
                'poor' => 2
            )
        ),
        'sleep_quality' => array(
            'category' => 'Sleep & Recovery',
            'weight' => 2,
            'answers' => array(
                'excellent' => 9,
                'good' => 7,
                'fair' => 5,
                'poor' => 3
            )
        ),
        'stress_management' => array(
            'category' => 'Stress & Mental Health',
            'weight' => 2,
            'answers' => array(
                'excellent' => 9,
                'good' => 7,
                'fair' => 5,
                'poor' => 3
            )
        ),
        'preventive_care' => array(
            'category' => 'Preventive Health',
            'weight' => 1.5,
            'answers' => array(
                'regular' => 9,
                'occasional' => 6,
                'rare' => 4,
                'never' => 2
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
        'skin_type' => array(
            'category' => 'Skin Characteristics',
            'weight' => 2,
            'answers' => array(
                'normal' => 8,
                'dry' => 6,
                'oily' => 6,
                'combination' => 7,
                'sensitive' => 5
            )
        ),
        'primary_concern' => array(
            'category' => 'Primary Skin Issue',
            'weight' => 3,
            'answers' => array(
                'acne' => 3,
                'aging' => 4,
                'pigmentation' => 5,
                'redness' => 4,
                'dullness' => 6
            )
        ),
        'sun_exposure' => array(
            'category' => 'Environmental Factors',
            'weight' => 2.5,
            'answers' => array(
                'minimal' => 9,
                'moderate' => 6,
                'high' => 3
            )
        ),
        'skincare_routine' => array(
            'category' => 'Current Regimen',
            'weight' => 1,
            'answers' => array(
                'none' => 4,
                'basic' => 6,
                'advanced' => 8
            )
        ),
        'lifestyle_factors' => array(
            'category' => 'Lifestyle & Diet',
            'weight' => 2,
            'answers' => array(
                'healthy' => 9,
                'average' => 6,
                'unhealthy' => 3
            )
        ),
        'stress_level' => array(
            'category' => 'Psychological Factors',
            'weight' => 1.5,
            'answers' => array(
                'low' => 9,
                'moderate' => 6,
                'high' => 3
            )
        ),
        'hydration' => array(
            'category' => 'Internal Health',
            'weight' => 1.5,
            'answers' => array(
                'good' => 9,
                'fair' => 6,
                'poor' => 3
            )
        ),
        'allergies' => array(
            'category' => 'Sensitivities',
            'weight' => 1,
            'answers' => array(
                'none' => 9,
                'some' => 5,
                'many' => 3
            )
        )
    ),
); 