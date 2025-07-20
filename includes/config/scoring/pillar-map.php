<?php
/**
 * Pillar Mapping Configuration
 * Maps assessment categories to health pillars for scoring calculations
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'Mind' => array(
        'categories' => array(
            'cognitive_function',
            'mental_clarity',
            'focus_concentration',
            'memory_recall',
            'mood_stability',
            'stress_management',
            'anxiety_levels',
            'depression_indicators',
            'emotional_regulation',
            'sleep_quality',
            'brain_fog',
            'mental_energy'
        ),
        'weight' => 0.25,
        'description' => 'Cognitive function, mental clarity, mood, and psychological well-being'
    ),
    
    'Body' => array(
        'categories' => array(
            'cardiovascular_health',
            'metabolic_function',
            'hormonal_balance',
            'immune_function',
            'digestive_health',
            'muscle_strength',
            'bone_density',
            'joint_health',
            'energy_levels',
            'physical_endurance',
            'recovery_rate',
            'inflammation_markers',
            'blood_sugar_regulation',
            'thyroid_function',
            'adrenal_function',
            'reproductive_health'
        ),
        'weight' => 0.35,
        'description' => 'Physical health, organ function, hormonal balance, and bodily systems'
    ),
    
    'Lifestyle' => array(
        'categories' => array(
            'exercise_frequency',
            'nutrition_quality',
            'sleep_patterns',
            'stress_levels',
            'social_connections',
            'work_life_balance',
            'substance_use',
            'environmental_toxins',
            'daily_habits',
            'self_care_practices',
            'time_management',
            'goal_achievement',
            'lifestyle_satisfaction'
        ),
        'weight' => 0.25,
        'description' => 'Daily habits, lifestyle choices, and environmental factors'
    ),
    
    'Aesthetics' => array(
        'categories' => array(
            'skin_health',
            'hair_quality',
            'body_composition',
            'physical_appearance',
            'self_confidence',
            'body_image',
            'aesthetic_satisfaction',
            'aging_indicators',
            'facial_health',
            'posture_alignment',
            'muscle_definition',
            'weight_management'
        ),
        'weight' => 0.15,
        'description' => 'Physical appearance, skin health, body composition, and aesthetic confidence'
    ),
    
    'calculation_settings' => array(
        'normalization_method' => 'weighted_average',
        'min_categories_required' => 2,
        'category_weight_distribution' => 'equal',
        'outlier_handling' => 'cap_at_percentile_95'
    ),
    
    'fallback_mappings' => array(
        'general_health' => 'Body',
        'wellness' => 'Lifestyle',
        'appearance' => 'Aesthetics',
        'mental_health' => 'Mind',
        'default' => 'Body'
    )
);
