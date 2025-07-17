<?php
/**
 * Assessment Definition: Hormone Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'Hormone Assessment',
    'assessment_engine' => 'quantitative',
    'questions' => array(
        'hormone_q_gender' => array(
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
        'hormone_q1' => array(
            'title' => 'Which of the following symptoms of hormonal imbalance are you experiencing?',
            'type' => 'multiselect',
            'options' => array(
                'fatigue' => 'Unexplained fatigue or energy loss',
                'mood_swings' => 'Mood swings, irritability, or anxiety',
                'weight_gain' => 'Unexplained weight gain, especially abdominal',
                'low_libido' => 'Low libido or sexual dysfunction',
                'sleep_issues' => 'Difficulty sleeping or night sweats',
                'brain_fog' => 'Brain fog or difficulty concentrating',
            ),
            'scoring' => array(
                'category' => 'Symptom Severity',
                'weight' => 3,
                'answers' => array(
                    'fatigue' => 2,
                    'mood_swings' => 2,
                    'weight_gain' => 1,
                    'low_libido' => 2,
                    'sleep_issues' => 2,
                    'brain_fog' => 2,
                ),
            ),
            'required' => true,
        ),
        'hormone_q2' => array(
            'title' => 'How would you rate your daily stress levels?',
            'type' => 'radio',
            'options' => array(
                'low' => 'Low',
                'moderate' => 'Moderate',
                'high' => 'High',
            ),
            'scoring' => array(
                'category' => 'Lifestyle Factors',
                'weight' => 2,
                'answers' => array(
                    'low' => 5,
                    'moderate' => 3,
                    'high' => 1,
                ),
            ),
            'required' => true,
        ),
    ),
); 