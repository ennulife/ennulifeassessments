<?php
/**
 * Health Goals Configuration
 * Defines health goals, their pillar mappings, boost rules, and definitions
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'goal_to_pillar_map' => array(
        'energy' => array(
            'primary_pillar' => 'Body',
            'secondary_pillar' => 'Lifestyle',
            'boost_percentage' => 0.05
        ),
        'strength' => array(
            'primary_pillar' => 'Body',
            'secondary_pillar' => 'Mind',
            'boost_percentage' => 0.05
        ),
        'sleep' => array(
            'primary_pillar' => 'Lifestyle',
            'secondary_pillar' => 'Body',
            'boost_percentage' => 0.05
        ),
        'stress' => array(
            'primary_pillar' => 'Mind',
            'secondary_pillar' => 'Lifestyle',
            'boost_percentage' => 0.05
        ),
        'focus' => array(
            'primary_pillar' => 'Mind',
            'secondary_pillar' => 'Lifestyle',
            'boost_percentage' => 0.05
        ),
        'confidence' => array(
            'primary_pillar' => 'Mind',
            'secondary_pillar' => 'Aesthetics',
            'boost_percentage' => 0.05
        ),
        'weight' => array(
            'primary_pillar' => 'Body',
            'secondary_pillar' => 'Aesthetics',
            'boost_percentage' => 0.05
        ),
        'skin' => array(
            'primary_pillar' => 'Aesthetics',
            'secondary_pillar' => 'Body',
            'boost_percentage' => 0.05
        ),
        'hair' => array(
            'primary_pillar' => 'Aesthetics',
            'secondary_pillar' => 'Body',
            'boost_percentage' => 0.05
        ),
        'libido' => array(
            'primary_pillar' => 'Body',
            'secondary_pillar' => 'Mind',
            'boost_percentage' => 0.05
        ),
        'longevity' => array(
            'primary_pillar' => 'Body',
            'secondary_pillar' => 'Lifestyle',
            'boost_percentage' => 0.05
        )
    ),

    'boost_rules' => array(
        'max_boost_per_pillar' => 0.15,
        'cumulative' => false,
        'application_method' => 'multiplicative'
    ),

    'goal_definitions' => array(
        'energy' => array(
            'label' => 'Energy & Vitality',
            'description' => 'Increase daily energy levels and reduce fatigue',
            'icon' => 'energy-icon',
            'category' => 'physical',
            'pillar_bonus' => array(
                'Body' => 0.05,
                'Lifestyle' => 0.03
            )
        ),
        'strength' => array(
            'label' => 'Strength & Fitness',
            'description' => 'Build muscle strength and improve physical fitness',
            'icon' => 'strength-icon',
            'category' => 'physical',
            'pillar_bonus' => array(
                'Body' => 0.05,
                'Mind' => 0.02
            )
        ),
        'sleep' => array(
            'label' => 'Sleep Quality',
            'description' => 'Improve sleep quality and duration',
            'icon' => 'sleep-icon',
            'category' => 'lifestyle',
            'pillar_bonus' => array(
                'Lifestyle' => 0.05,
                'Body' => 0.03
            )
        ),
        'stress' => array(
            'label' => 'Stress Management',
            'description' => 'Reduce stress levels and improve mental resilience',
            'icon' => 'stress-icon',
            'category' => 'mental',
            'pillar_bonus' => array(
                'Mind' => 0.05,
                'Lifestyle' => 0.03
            )
        ),
        'focus' => array(
            'label' => 'Mental Focus',
            'description' => 'Enhance concentration and cognitive performance',
            'icon' => 'focus-icon',
            'category' => 'mental',
            'pillar_bonus' => array(
                'Mind' => 0.05,
                'Lifestyle' => 0.02
            )
        ),
        'confidence' => array(
            'label' => 'Confidence & Self-Esteem',
            'description' => 'Build confidence and improve self-image',
            'icon' => 'confidence-icon',
            'category' => 'mental',
            'pillar_bonus' => array(
                'Mind' => 0.05,
                'Aesthetics' => 0.03
            )
        ),
        'weight' => array(
            'label' => 'Weight Management',
            'description' => 'Achieve and maintain optimal body weight',
            'icon' => 'weight-icon',
            'category' => 'physical',
            'pillar_bonus' => array(
                'Body' => 0.05,
                'Aesthetics' => 0.03
            )
        ),
        'skin' => array(
            'label' => 'Skin Health',
            'description' => 'Improve skin appearance and health',
            'icon' => 'skin-icon',
            'category' => 'aesthetic',
            'pillar_bonus' => array(
                'Aesthetics' => 0.05,
                'Body' => 0.02
            )
        ),
        'hair' => array(
            'label' => 'Hair Health',
            'description' => 'Enhance hair growth and appearance',
            'icon' => 'hair-icon',
            'category' => 'aesthetic',
            'pillar_bonus' => array(
                'Aesthetics' => 0.05,
                'Body' => 0.02
            )
        ),
        'libido' => array(
            'label' => 'Sexual Health',
            'description' => 'Improve libido and sexual wellness',
            'icon' => 'libido-icon',
            'category' => 'physical',
            'pillar_bonus' => array(
                'Body' => 0.05,
                'Mind' => 0.03
            )
        ),
        'longevity' => array(
            'label' => 'Longevity & Anti-Aging',
            'description' => 'Promote healthy aging and longevity',
            'icon' => 'longevity-icon',
            'category' => 'lifestyle',
            'pillar_bonus' => array(
                'Body' => 0.05,
                'Lifestyle' => 0.03
            )
        )
    )
);
