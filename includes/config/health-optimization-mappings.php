<?php
/**
 * Health Optimization Mappings
 *
 * This file contains the core logic for the Symptom Qualification Engine.
 * It defines how symptoms are categorized and the precise penalty matrix
 * used to adjust a user's Pillar Scores based on their real-world experience.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(

    /**
     * Symptom-to-Vector Map
     * Maps each individual symptom to one or more Health Optimization Vectors.
     */
    'symptom_to_vector_map' => array(
        'Abdominal Fat Gain' => array( 'Weight Loss' ),
        'Anxiety' => array( 'Hormones' ),
        'Blood Glucose Dysregulation' => array( 'Weight Loss' ),
        'Brain Fog' => array( 'Energy', 'Cognitive Health' ),
        'Change in Personality' => array( 'Cognitive Health' ),
        'Chest Pain' => array( 'Heart Health' ),
        'Chronic Fatigue' => array( 'Longevity' ),
        'Cognitive Decline' => array( 'Longevity' ),
        'Confusion' => array( 'Cognitive Health' ),
        'Decreased Mobility' => array( 'Strength' ),
        'Decreased Physical Activity' => array( 'Longevity' ),
        'Depression' => array( 'Hormones' ),
        'Erectile Dysfunction' => array( 'Hormones', 'Heart Health', 'Libido' ),
        'Fatigue' => array( 'Energy', 'Heart Health', 'Weight Loss', 'Strength' ),
        'Frequent Illness' => array( 'Energy', 'Longevity' ),
        'High Blood Pressure' => array( 'Weight Loss' ),
        'Hot Flashes' => array( 'Hormones' ),
        'Increased Body Fat' => array( 'Weight Loss' ),
        'Infertility' => array( 'Hormones', 'Libido' ),
        'Irritability' => array( 'Hormones' ),
        'Itchy Skin' => array( 'Longevity' ),
        'Joint Pain' => array( 'Weight Loss', 'Strength' ),
        'Lack of Motivation' => array( 'Energy' ),
        'Language Problems' => array( 'Cognitive Health' ),
        'Lightheadedness' => array( 'Heart Health' ),
        'Low Libido' => array( 'Hormones', 'Libido' ),
        'Low Self-Esteem' => array( 'Libido' ),
        'Memory Loss' => array( 'Cognitive Health' ),
        'Mood Changes' => array( 'Cognitive Health' ),
        'Mood Swings' => array( 'Hormones' ),
        'Muscle Loss' => array( 'Strength', 'Longevity' ),
        'Muscle Mass Loss' => array( 'Strength' ),
        'Muscle Weakness' => array( 'Energy' ),
        'Night Sweats' => array( 'Hormones' ),
        'Palpitations' => array( 'Heart Health' ),
        'Poor Balance' => array( 'Strength' ),
        'Poor Concentration' => array( 'Cognitive Health' ),
        'Poor Coordination' => array( 'Cognitive Health' ),
        'Poor Exercise Tolerance' => array( 'Heart Health' ),
        'Poor Sleep' => array( 'Energy' ),
        'Prolonged Soreness' => array( 'Strength' ),
        'Reduced Physical Performance' => array( 'Energy', 'Weight Loss' ),
        'Shortness of Breath' => array( 'Heart Health' ),
        'Sleep Disturbance' => array( 'Cognitive Health' ),
        'Sleep Problems' => array( 'Weight Loss' ),
        'Slow Healing Wounds' => array( 'Longevity' ),
        'Slow Metabolism' => array( 'Weight Loss' ),
        'Slow Recovery' => array( 'Strength' ),
        'Swelling' => array( 'Heart Health' ),
        'Vaginal Dryness' => array( 'Hormones', 'Libido' ),
        'Weakness' => array( 'Strength' ),
        'Weight Changes' => array( 'Longevity' ),
    ),

    /**
     * Pillar Integrity Penalty Matrix
     * Defines the penalty value based on the Vector, Severity, and Frequency.
     * The engine finds the highest penalty per pillar and applies only that one.
     */
    'pillar_integrity_penalty_matrix' => array(
        'Heart Health' => array(
            'pillar_impact' => 'Body',
            'penalties' => array(
                'Severe'   => array( 'Daily' => 0.20, 'Weekly' => 0.18, 'Monthly' => 0.16 ),
                'Moderate' => array( 'Daily' => 0.15, 'Weekly' => 0.12, 'Monthly' => 0.10 ),
                'Mild'     => array( 'Daily' => 0.08, 'Weekly' => 0.06, 'Monthly' => 0.04 ),
            ),
        ),
        'Cognitive Health' => array(
            'pillar_impact' => 'Mind',
            'penalties' => array(
                'Severe'   => array( 'Daily' => 0.20, 'Weekly' => 0.18, 'Monthly' => 0.16 ),
                'Moderate' => array( 'Daily' => 0.15, 'Weekly' => 0.12, 'Monthly' => 0.10 ),
                'Mild'     => array( 'Daily' => 0.08, 'Weekly' => 0.06, 'Monthly' => 0.04 ),
            ),
        ),
        'Hormones' => array(
            'pillar_impact' => 'Body',
            'penalties' => array(
                'Severe'   => array( 'Daily' => 0.10, 'Weekly' => 0.08, 'Monthly' => 0.06 ),
                'Moderate' => array( 'Daily' => 0.05, 'Weekly' => 0.04, 'Monthly' => 0.03 ),
                'Mild'     => array( 'Daily' => 0.02, 'Weekly' => 0.01, 'Monthly' => 0.01 ),
            ),
        ),
        'Weight Loss' => array(
            'pillar_impact' => 'Lifestyle',
            'penalties' => array(
                'Severe'   => array( 'Daily' => 0.10, 'Weekly' => 0.08, 'Monthly' => 0.06 ),
                'Moderate' => array( 'Daily' => 0.05, 'Weekly' => 0.04, 'Monthly' => 0.03 ),
                'Mild'     => array( 'Daily' => 0.02, 'Weekly' => 0.01, 'Monthly' => 0.01 ),
            ),
        ),
        'Strength' => array(
            'pillar_impact' => 'Body',
            'penalties' => array(
                'Severe'   => array( 'Daily' => 0.10, 'Weekly' => 0.08, 'Monthly' => 0.06 ),
                'Moderate' => array( 'Daily' => 0.05, 'Weekly' => 0.04, 'Monthly' => 0.03 ),
                'Mild'     => array( 'Daily' => 0.02, 'Weekly' => 0.01, 'Monthly' => 0.01 ),
            ),
        ),
        'Longevity' => array(
            'pillar_impact' => 'Lifestyle',
            'penalties' => array(
                'Severe'   => array( 'Daily' => 0.10, 'Weekly' => 0.08, 'Monthly' => 0.06 ),
                'Moderate' => array( 'Daily' => 0.05, 'Weekly' => 0.04, 'Monthly' => 0.03 ),
                'Mild'     => array( 'Daily' => 0.02, 'Weekly' => 0.01, 'Monthly' => 0.01 ),
            ),
        ),
        'Energy' => array(
            'pillar_impact' => 'Lifestyle',
            'penalties' => array(
                'Severe'   => array( 'Daily' => 0.08, 'Weekly' => 0.06, 'Monthly' => 0.04 ),
                'Moderate' => array( 'Daily' => 0.05, 'Weekly' => 0.03, 'Monthly' => 0.02 ),
                'Mild'     => array( 'Daily' => 0.01, 'Weekly' => 0.01, 'Monthly' => 0.00 ),
            ),
        ),
        'Libido' => array(
            'pillar_impact' => 'Mind',
            'penalties' => array(
                'Severe'   => array( 'Daily' => 0.08, 'Weekly' => 0.06, 'Monthly' => 0.04 ),
                'Moderate' => array( 'Daily' => 0.05, 'Weekly' => 0.03, 'Monthly' => 0.02 ),
                'Mild'     => array( 'Daily' => 0.01, 'Weekly' => 0.01, 'Monthly' => 0.00 ),
            ),
        ),
    ),

    /**
     * Vector-to-Biomarker Map
     * Maps each Health Optimization Vector to a list of recommended biomarkers.
     */
    'vector_to_biomarker_map' => array(
        'Heart Health' => array('ApoB', 'Lp(a)', 'Homocysteine', 'hs-CRP', 'Total Cholesterol', 'HDL', 'LDL', 'Triglycerides'),
        'Cognitive Health' => array('Homocysteine', 'hs-CRP', 'Vitamin D', 'Vitamin B12', 'Folate', 'TSH'),
        'Hormones' => array('Testosterone (Total & Free)', 'Estradiol (E2)', 'Progesterone', 'DHEA-S', 'Cortisol', 'TSH', 'Free T3', 'Free T4'),
        'Weight Loss' => array('Insulin', 'Glucose', 'HbA1c', 'Leptin', 'Cortisol', 'TSH'),
        'Strength' => array('Testosterone (Total & Free)', 'DHEA-S', 'IGF-1', 'Vitamin D'),
        'Longevity' => array('Lp(a)', 'Homocysteine', 'hs-CRP', 'HbA1c', 'IGF-1', 'ApoB'),
        'Energy' => array('Ferritin', 'Vitamin B12', 'Vitamin D', 'Cortisol', 'TSH', 'Free T3'),
        'Libido' => array('Testosterone (Total & Free)', 'Estradiol (E2)', 'Prolactin', 'SHBG'),
    ),
); 