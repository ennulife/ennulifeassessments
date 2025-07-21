<?php
/**
 * Assessment Definition: Testosterone Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'Testosterone Assessment',
    'assessment_engine' => 'quantitative',
    'gender_filter' => 'male',
    'questions' => array(
        'testosterone_q1' => array(
            'title' => 'Which of the following symptoms, often associated with low testosterone, are you experiencing?',
            'type' => 'multiselect',
            'options' => array(
                'low_libido' => 'Low libido or decreased sexual desire',
                'fatigue' => 'Fatigue and decreased energy levels',
                'muscle_loss' => 'Loss of muscle mass or strength',
                'increased_fat' => 'Increased body fat, especially around the midsection',
                'mood_changes' => 'Mood changes, irritability, or depression',
                'erectile_dysfunction' => 'Erectile dysfunction',
            ),
            'scoring' => array(
                'category' => 'Symptom Severity',
                'weight' => 3,
                'answers' => array(
                    'low_libido' => 2,
                    'fatigue' => 2,
                    'muscle_loss' => 1,
                    'increased_fat' => 1,
                    'mood_changes' => 2,
                    'erectile_dysfunction' => 1,
                ),
            ),
            'required' => true,
        ),
        'testosterone_q2' => array(
            'title' => 'How many days per week do you engage in resistance training (weight lifting)?',
            'type' => 'radio',
            'options' => array(
                '4_plus' => '4 or more days',
                '2_3' => '2-3 days',
                '1' => '1 day',
                'none' => 'I do not do resistance training',
            ),
            'scoring' => array(
                'category' => 'Lifestyle Factors',
                'weight' => 2,
                'answers' => array(
                    '4_plus' => 5,
                    '2_3' => 4,
                    '1' => 2,
                    'none' => 1,
                ),
            ),
            'required' => true,
        ),
    ),
); 