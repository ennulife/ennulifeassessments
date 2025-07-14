<?php
/**
 * Configuration file for contextual dashboard insights.
 * This provides the "why" behind the scores.
 */
return [
    'ennu_life_score' => 'Your ENNU Life Score is a holistic measure of your overall health and wellness, calculated from your pillar scores across all completed assessments.',

    'pillars' => [
        'Mind' => 'Measures psychological and emotional wellbeing, including stress, motivation, and mental health.',
        'Body' => 'Reflects your physical health, including medical history, genetic predispositions, and physiological factors.',
        'Lifestyle' => 'Assesses your daily habits, including diet, exercise, sleep, and other behavioral patterns.',
        'Aesthetics' => 'Represents your primary outward-facing concerns, such as hair, skin, and weight management goals.',
    ],

    'categories' => [
        // General
        'Default' => 'This category measures a key aspect of your health and wellness.',

        // Hair Assessment
        'Hair Health Status' => 'Your current condition and the severity of your hair concerns.',
        'Progression Timeline' => 'How long hair changes have been occurring, which can influence treatment options.',
        'Progression Rate' => 'The speed of hair loss or changes, a key indicator of urgency.',
        'Genetic Factors' => 'How your family history may influence your hair health.',
        'Lifestyle Factors' => 'The impact of stress and other lifestyle choices on your hair.',
        'Nutritional Support' => 'The role of diet quality in providing essential nutrients for hair growth.',
        'Treatment History' => 'Your past experiences with hair loss treatments.',
        'Treatment Expectations' => 'Your goals and desired outcomes for treatment.',

        // Skin Assessment
        'Skin Characteristics' => 'Your natural skin type, which forms the baseline for any skincare regimen.',
        'Primary Skin Issue' => 'The main skin concern you wish to address.',
        'Environmental Factors' => 'How external factors like sun exposure are impacting your skin.',
        'Current Regimen' => 'The effectiveness of your current skincare habits.',
        'Skin Reactivity' => 'How sensitive your skin is to new products or treatments.',
        'Lifestyle & Diet' => 'The connection between your diet, stress, sleep, and skin health.',

        // Weight Loss
        'Current Status' => 'Your starting point, including factors like BMI.',
        'Physical Activity' => 'Your current exercise frequency and activity levels.',
        'Nutrition' => 'The quality of your diet and typical eating habits.',
        'Behavioral Patterns' => 'Habits such as emotional or late-night eating that can affect progress.',
        'Psychological Factors' => 'The impact of stress and mental wellbeing on weight management.',
        'Motivation & Goals' => 'Your readiness and specific goals for your weight loss journey.',
        'Weight Loss History' => 'Your past experiences with attempting to lose weight.',

        // Add other assessment categories here...
    ]
]; 