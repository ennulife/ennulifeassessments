<?php
/**
 * Health Goals Configuration
 * Defines available health goals and their properties for the Intentionality Engine
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'goal_definitions' => array(
        'weight_loss' => array(
            'name' => 'Weight Loss',
            'description' => 'Achieve and maintain healthy weight',
            'pillar_boosts' => array(
                'Body' => 0.15,
                'Lifestyle' => 0.10
            ),
            'priority' => 1
        ),
        'muscle_gain' => array(
            'name' => 'Muscle Gain',
            'description' => 'Build lean muscle mass and strength',
            'pillar_boosts' => array(
                'Body' => 0.20,
                'Lifestyle' => 0.05
            ),
            'priority' => 1
        ),
        'energy_boost' => array(
            'name' => 'Energy Boost',
            'description' => 'Increase daily energy and vitality',
            'pillar_boosts' => array(
                'Body' => 0.10,
                'Mind' => 0.10,
                'Lifestyle' => 0.15
            ),
            'priority' => 2
        ),
        'better_sleep' => array(
            'name' => 'Better Sleep',
            'description' => 'Improve sleep quality and duration',
            'pillar_boosts' => array(
                'Mind' => 0.15,
                'Lifestyle' => 0.10
            ),
            'priority' => 2
        ),
        'stress_reduction' => array(
            'name' => 'Stress Reduction',
            'description' => 'Manage and reduce daily stress levels',
            'pillar_boosts' => array(
                'Mind' => 0.20,
                'Lifestyle' => 0.05
            ),
            'priority' => 1
        ),
        'improved_focus' => array(
            'name' => 'Improved Focus',
            'description' => 'Enhance mental clarity and concentration',
            'pillar_boosts' => array(
                'Mind' => 0.15,
                'Body' => 0.05
            ),
            'priority' => 2
        ),
        'hormone_balance' => array(
            'name' => 'Hormone Balance',
            'description' => 'Optimize hormonal health and function',
            'pillar_boosts' => array(
                'Body' => 0.15,
                'Mind' => 0.10,
                'Aesthetics' => 0.05
            ),
            'priority' => 1
        ),
        'heart_health' => array(
            'name' => 'Heart Health',
            'description' => 'Improve cardiovascular health and function',
            'pillar_boosts' => array(
                'Body' => 0.20,
                'Lifestyle' => 0.10
            ),
            'priority' => 1
        ),
        'immune_support' => array(
            'name' => 'Immune Support',
            'description' => 'Strengthen immune system function',
            'pillar_boosts' => array(
                'Body' => 0.15,
                'Lifestyle' => 0.05
            ),
            'priority' => 2
        ),
        'longevity' => array(
            'name' => 'Longevity',
            'description' => 'Promote healthy aging and longevity',
            'pillar_boosts' => array(
                'Body' => 0.10,
                'Mind' => 0.10,
                'Lifestyle' => 0.15,
                'Aesthetics' => 0.05
            ),
            'priority' => 3
        ),
        'aesthetic_improvement' => array(
            'name' => 'Aesthetic Improvement',
            'description' => 'Enhance physical appearance and confidence',
            'pillar_boosts' => array(
                'Aesthetics' => 0.20,
                'Body' => 0.05
            ),
            'priority' => 2
        ),
        'sexual_health' => array(
            'name' => 'Sexual Health',
            'description' => 'Improve sexual function and satisfaction',
            'pillar_boosts' => array(
                'Body' => 0.15,
                'Mind' => 0.05,
                'Aesthetics' => 0.05
            ),
            'priority' => 1
        )
    ),
    'boost_limits' => array(
        'max_per_pillar' => 0.25,
        'max_total_boost' => 0.50,
        'non_cumulative' => true
    ),
    'goal_priorities' => array(
        1 => 'Primary Goals',
        2 => 'Secondary Goals', 
        3 => 'Long-term Goals'
    )
);
