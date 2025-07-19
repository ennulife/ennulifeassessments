<?php
/**
 * Pillar Map Configuration
 * Maps assessment categories to their corresponding pillars
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'Mind' => array(
        'stress_management',
        'mental_clarity',
        'cognitive_function',
        'emotional_wellbeing',
        'anxiety_levels',
        'depression_indicators',
        'mood_stability',
        'mental_resilience',
        'focus_concentration',
        'memory_function',
        'psychological_health'
    ),
    
    'Body' => array(
        'physical_fitness',
        'cardiovascular_health',
        'strength_endurance',
        'energy_levels',
        'metabolic_health',
        'hormonal_balance',
        'immune_function',
        'digestive_health',
        'sexual_health',
        'physical_symptoms',
        'body_composition',
        'vital_signs',
        'biomarkers'
    ),
    
    'Lifestyle' => array(
        'sleep_quality',
        'nutrition_habits',
        'exercise_routine',
        'work_life_balance',
        'social_connections',
        'stress_factors',
        'daily_habits',
        'environmental_factors',
        'substance_use',
        'lifestyle_choices',
        'time_management',
        'self_care_practices'
    ),
    
    'Aesthetics' => array(
        'skin_health',
        'hair_health',
        'body_image',
        'appearance_satisfaction',
        'aesthetic_concerns',
        'cosmetic_health',
        'physical_appearance',
        'beauty_wellness',
        'aesthetic_goals',
        'visual_health_indicators'
    )
);
