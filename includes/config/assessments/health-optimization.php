<?php
/**
 * Assessment Definition: Health Optimization Assessment
 * Enhanced by Dr. Victor Pulse (Cardiology Master)
 * Evidence-based cardiology implementation
 *
 * @package ENNU_Life
 * @version 62.3.1
 */

return array(
    'title' => 'Health Optimization Assessment',
    'assessment_engine' => 'qualitative',
    'questions' => array(
        'health_q_gender' => array(
            'title' => 'What is your gender?',
            'type' => 'radio',
            'options' => array(
                'male' => 'Male',
                'female' => 'Female',
                'other' => 'Other / Prefer not to say',
            ),
            'required' => true,
            'global_key' => 'gender'
        ),
        'health_q_age' => array(
            'title' => 'What is your age?',
            'type' => 'radio',
            'options' => array(
                'under_25' => 'Under 25',
                '25_35' => '25-35',
                '36_45' => '36-45',
                '46_55' => '46-55',
                'over_55' => 'Over 55',
            ),
            'scoring' => array(
                'category' => 'Age Factors',
                'weight' => 1.5,
                'answers' => array(
                    'under_25' => 8,
                    '25_35' => 7,
                    '36_45' => 6,
                    '46_55' => 5,
                    'over_55' => 4,
                ),
            ),
            'required' => true,
        ),
        'symptom_q1' => array(
            'title' => 'Please select any symptoms you are experiencing related to Heart Health.',
            'type' => 'multiselect',
            'options' => array(
                'chest_pain' => 'Chest Pain or Discomfort',
                'shortness_breath' => 'Shortness of Breath',
                'palpitations' => 'Heart Palpitations or Irregular Heartbeat',
                'lightheadedness' => 'Lightheadedness or Dizziness',
                'swelling' => 'Swelling in Legs, Ankles, or Feet',
                'poor_exercise_tolerance' => 'Poor Exercise Tolerance',
                'fatigue' => 'Unexplained Fatigue',
                'nausea' => 'Nausea or Indigestion',
                'sweating' => 'Cold Sweats',
                'none' => 'None of the above',
            ),
            'scoring' => array(
                'category' => 'Cardiovascular Symptoms',
                'weight' => 3,
                'answers' => array(
                    'chest_pain' => 2,
                    'shortness_breath' => 2,
                    'palpitations' => 3,
                    'lightheadedness' => 3,
                    'swelling' => 2,
                    'poor_exercise_tolerance' => 3,
                    'fatigue' => 4,
                    'nausea' => 3,
                    'sweating' => 2,
                    'none' => 8,
                ),
            ),
            'required' => false,
        ),
        'symptom_q1_severity' => array(
            'title' => 'How severe are your Heart Health symptoms?',
            'type' => 'radio',
            'options' => array(
                'none' => 'None',
                'mild' => 'Mild - Noticeable but not interfering with daily activities',
                'moderate' => 'Moderate - Some interference with daily activities',
                'severe' => 'Severe - Significant interference with daily activities',
            ),
            'scoring' => array(
                'category' => 'Symptom Severity',
                'weight' => 2.5,
                'answers' => array(
                    'none' => 8,
                    'mild' => 6,
                    'moderate' => 4,
                    'severe' => 2,
                ),
            ),
            'required' => false,
        ),
        'symptom_q1_frequency' => array(
            'title' => 'How often do you experience these symptoms?',
            'type' => 'radio',
            'options' => array(
                'never' => 'Never',
                'rarely' => 'Rarely (few times per year)',
                'monthly' => 'Monthly',
                'weekly' => 'Weekly',
                'daily' => 'Daily',
            ),
            'scoring' => array(
                'category' => 'Symptom Frequency',
                'weight' => 2,
                'answers' => array(
                    'never' => 8,
                    'rarely' => 7,
                    'monthly' => 5,
                    'weekly' => 3,
                    'daily' => 2,
                ),
            ),
            'required' => false,
        ),
        'symptom_q2' => array(
            'title' => 'Please select any symptoms you are experiencing related to Cognitive Health.',
            'type' => 'multiselect',
            'options' => array(
                'brain_fog' => 'Brain Fog or Mental Clarity Issues',
                'memory_loss' => 'Memory Loss or Forgetfulness',
                'poor_concentration' => 'Poor Concentration or Focus',
                'mental_fatigue' => 'Mental Fatigue',
                'mood_changes' => 'Mood Changes or Irritability',
                'sleep_issues' => 'Sleep Issues',
                'none' => 'None of the above',
            ),
            'scoring' => array(
                'category' => 'Cognitive Symptoms',
                'weight' => 2.5,
                'answers' => array(
                    'brain_fog' => 3,
                    'memory_loss' => 2,
                    'poor_concentration' => 3,
                    'mental_fatigue' => 4,
                    'mood_changes' => 4,
                    'sleep_issues' => 4,
                    'none' => 8,
                ),
            ),
            'required' => false,
        ),
        'symptom_q2_severity' => array(
            'title' => 'How severe are your Cognitive Health symptoms?',
            'type' => 'radio',
            'options' => array(
                'none' => 'None',
                'mild' => 'Mild - Noticeable but not interfering with daily activities',
                'moderate' => 'Moderate - Some interference with daily activities',
                'severe' => 'Severe - Significant interference with daily activities',
            ),
            'scoring' => array(
                'category' => 'Symptom Severity',
                'weight' => 2,
                'answers' => array(
                    'none' => 8,
                    'mild' => 6,
                    'moderate' => 4,
                    'severe' => 2,
                ),
            ),
            'required' => false,
        ),
        'symptom_q2_frequency' => array(
            'title' => 'How often do you experience these symptoms?',
            'type' => 'radio',
            'options' => array(
                'never' => 'Never',
                'rarely' => 'Rarely (few times per year)',
                'monthly' => 'Monthly',
                'weekly' => 'Weekly',
                'daily' => 'Daily',
            ),
            'scoring' => array(
                'category' => 'Symptom Frequency',
                'weight' => 1.5,
                'answers' => array(
                    'never' => 8,
                    'rarely' => 7,
                    'monthly' => 5,
                    'weekly' => 3,
                    'daily' => 2,
                ),
            ),
            'required' => false,
        ),
        'symptom_q3' => array(
            'title' => 'Please select any symptoms you are experiencing related to Metabolic Health.',
            'type' => 'multiselect',
            'options' => array(
                'weight_gain' => 'Unexplained Weight Gain',
                'fatigue' => 'Chronic Fatigue',
                'thirst' => 'Increased Thirst or Frequent Urination',
                'slow_healing' => 'Slow Wound Healing',
                'blurred_vision' => 'Blurred Vision',
                'numbness' => 'Numbness or Tingling in Extremities',
                'none' => 'None of the above',
            ),
            'scoring' => array(
                'category' => 'Metabolic Symptoms',
                'weight' => 2.5,
                'answers' => array(
                    'weight_gain' => 3,
                    'fatigue' => 4,
                    'thirst' => 2,
                    'slow_healing' => 3,
                    'blurred_vision' => 2,
                    'numbness' => 2,
                    'none' => 8,
                ),
            ),
            'required' => false,
        ),
        'symptom_q3_severity' => array(
            'title' => 'How severe are your Metabolic Health symptoms?',
            'type' => 'radio',
            'options' => array(
                'none' => 'None',
                'mild' => 'Mild - Noticeable but not interfering with daily activities',
                'moderate' => 'Moderate - Some interference with daily activities',
                'severe' => 'Severe - Significant interference with daily activities',
            ),
            'scoring' => array(
                'category' => 'Symptom Severity',
                'weight' => 2,
                'answers' => array(
                    'none' => 8,
                    'mild' => 6,
                    'moderate' => 4,
                    'severe' => 2,
                ),
            ),
            'required' => false,
        ),
        'symptom_q3_frequency' => array(
            'title' => 'How often do you experience these symptoms?',
            'type' => 'radio',
            'options' => array(
                'never' => 'Never',
                'rarely' => 'Rarely (few times per year)',
                'monthly' => 'Monthly',
                'weekly' => 'Weekly',
                'daily' => 'Daily',
            ),
            'scoring' => array(
                'category' => 'Symptom Frequency',
                'weight' => 1.5,
                'answers' => array(
                    'never' => 8,
                    'rarely' => 7,
                    'monthly' => 5,
                    'weekly' => 3,
                    'daily' => 2,
                ),
            ),
            'required' => false,
        ),
        'symptom_q4' => array(
            'title' => 'Please select any symptoms you are experiencing related to Immune Health.',
            'type' => 'multiselect',
            'options' => array(
                'frequent_illness' => 'Frequent Illness or Infections',
                'slow_recovery' => 'Slow Recovery from Illness',
                'allergies' => 'Allergies or Sensitivities',
                'inflammation' => 'Chronic Inflammation or Pain',
                'autoimmune' => 'Autoimmune Symptoms',
                'none' => 'None of the above',
            ),
            'scoring' => array(
                'category' => 'Immune Symptoms',
                'weight' => 2,
                'answers' => array(
                    'frequent_illness' => 2,
                    'slow_recovery' => 3,
                    'allergies' => 4,
                    'inflammation' => 3,
                    'autoimmune' => 2,
                    'none' => 8,
                ),
            ),
            'required' => false,
        ),
        'symptom_q4_severity' => array(
            'title' => 'How severe are your Immune Health symptoms?',
            'type' => 'radio',
            'options' => array(
                'none' => 'None',
                'mild' => 'Mild - Noticeable but not interfering with daily activities',
                'moderate' => 'Moderate - Some interference with daily activities',
                'severe' => 'Severe - Significant interference with daily activities',
            ),
            'scoring' => array(
                'category' => 'Symptom Severity',
                'weight' => 1.5,
                'answers' => array(
                    'none' => 8,
                    'mild' => 6,
                    'moderate' => 4,
                    'severe' => 2,
                ),
            ),
            'required' => false,
        ),
        'symptom_q4_frequency' => array(
            'title' => 'How often do you experience these symptoms?',
            'type' => 'radio',
            'options' => array(
                'never' => 'Never',
                'rarely' => 'Rarely (few times per year)',
                'monthly' => 'Monthly',
                'weekly' => 'Weekly',
                'daily' => 'Daily',
            ),
            'scoring' => array(
                'category' => 'Symptom Frequency',
                'weight' => 1.5,
                'answers' => array(
                    'never' => 8,
                    'rarely' => 7,
                    'monthly' => 5,
                    'weekly' => 3,
                    'daily' => 2,
                ),
            ),
            'required' => false,
        ),
    ),
); 