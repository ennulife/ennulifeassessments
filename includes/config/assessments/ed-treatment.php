<?php
/**
 * Assessment Definition: ED Treatment Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'ED Treatment Assessment',
    'assessment_engine' => 'quantitative',
    'gender_filter' => 'male',
    'questions' => array(
        'ed_q1' => array(
            'title' => 'How would you describe your ability to achieve and maintain an erection sufficient for satisfactory sexual performance?',
            'type' => 'radio',
            'options' => array(
                'always' => 'Almost always or always',
                'usually' => 'Usually (more than half the time)',
                'sometimes' => 'Sometimes (about half the time)',
                'rarely' => 'Rarely or never',
            ),
            'scoring' => array(
                'category' => 'Condition Severity',
                'weight' => 3,
                'answers' => array(
                    'always' => 10,
                    'usually' => 7,
                    'sometimes' => 4,
                    'rarely' => 1,
                ),
            ),
            'required' => true,
        ),
        'ed_q2' => array(
            'title' => 'Have you been diagnosed with any of the following medical conditions?',
            'type' => 'multiselect',
            'options' => array(
                'diabetes' => 'Diabetes',
                'heart_disease' => 'Heart Disease',
                'high_blood_pressure' => 'High Blood Pressure',
                'high_cholesterol' => 'High Cholesterol',
                'none' => 'None of the above',
            ),
            'scoring' => array(
                'category' => 'Medical Factors',
                'weight' => 2.5,
                'answers' => array(
                    'diabetes' => 2,
                    'heart_disease' => 1,
                    'high_blood_pressure' => 2,
                    'high_cholesterol' => 3,
                    'none' => 5,
                ),
            ),
            'required' => true,
        ),
        'ed_q3' => array(
            'title' => 'How would you describe your current stress levels?',
            'type' => 'radio',
            'options' => array(
                'low' => 'Low',
                'moderate' => 'Moderate',
                'high' => 'High',
                'very_high' => 'Very High',
            ),
            'scoring' => array(
                'category' => 'Psychological Factors',
                'weight' => 1.5,
                'answers' => array(
                    'low' => 5,
                    'moderate' => 4,
                    'high' => 2,
                    'very_high' => 1,
                ),
            ),
            'required' => true,
        ),
    ),
); 