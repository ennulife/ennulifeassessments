<?php
/**
 * Assessment Definition: Sleep Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'Sleep Assessment',
    'assessment_engine' => 'quantitative',
    'questions' => array(
        'sleep_q_gender' => array(
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
        'sleep_q1' => array(
            'title' => 'On average, how many hours of sleep do you get per night?',
            'type' => 'radio',
            'options' => array(
                'less_than_5' => 'Less than 5 hours',
                '5_6' => '5-6 hours',
                '7_8' => '7-8 hours',
                'more_than_8' => 'More than 8 hours',
            ),
            'scoring' => array(
                'category' => 'Sleep Duration',
                'weight' => 2,
                'answers' => array(
                    'less_than_5' => 1,
                    '5_6' => 2,
                    '7_8' => 5,
                    'more_than_8' => 4,
                ),
            ),
            'required' => true,
        ),
        'sleep_q2' => array(
            'title' => 'How would you rate the quality of your sleep?',
            'type' => 'radio',
            'options' => array(
                'very_good' => 'Very Good',
                'good' => 'Good',
                'fair' => 'Fair',
                'poor' => 'Poor',
            ),
            'scoring' => array(
                'category' => 'Sleep Quality',
                'weight' => 3,
                'answers' => array(
                    'very_good' => 5,
                    'good' => 4,
                    'fair' => 2,
                    'poor' => 1,
                ),
            ),
            'required' => true,
        ),
        'sleep_q3' => array(
            'title' => 'How often do you wake up during the night?',
            'type' => 'radio',
            'options' => array(
                'rarely' => 'Rarely or never',
                'sometimes' => 'Sometimes',
                'often' => 'Often',
                'every_night' => 'Almost every night',
            ),
            'scoring' => array(
                'category' => 'Sleep Continuity',
                'weight' => 2.5,
                'answers' => array(
                    'rarely' => 5,
                    'sometimes' => 4,
                    'often' => 2,
                    'every_night' => 1,
                ),
            ),
            'required' => true,
        ),
        'sleep_q4' => array(
            'title' => 'How do you feel when you wake up in the morning?',
            'type' => 'radio',
            'options' => array(
                'refreshed' => 'Refreshed and energetic',
                'okay' => 'Okay, but could be better',
                'groggy' => 'Groggy and unrested',
                'exhausted' => 'Exhausted',
            ),
            'scoring' => array(
                'category' => 'Daytime Function',
                'weight' => 2,
                'answers' => array(
                    'refreshed' => 5,
                    'okay' => 4,
                    'groggy' => 2,
                    'exhausted' => 1,
                ),
            ),
            'required' => true,
        ),
    ),
); 