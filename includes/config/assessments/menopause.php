<?php
/**
 * Assessment Definition: Menopause Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'Menopause Assessment',
    'assessment_engine' => 'quantitative',
    'gender_filter' => 'female',
    'questions' => array(
        'menopause_q1' => array(
            'title' => 'Which of the following menopausal symptoms are you experiencing?',
            'type' => 'multiselect',
            'options' => array(
                'hot_flashes' => 'Hot flashes',
                'night_sweats' => 'Night sweats',
                'mood_swings' => 'Mood swings or irritability',
                'sleep_disturbances' => 'Sleep disturbances',
                'vaginal_dryness' => 'Vaginal dryness',
                'low_libido' => 'Low libido',
            ),
            'scoring' => array(
                'category' => 'Symptom Severity',
                'weight' => 3,
                'answers' => array(
                    'hot_flashes' => 2,
                    'night_sweats' => 2,
                    'mood_swings' => 3,
                    'sleep_disturbances' => 2,
                    'vaginal_dryness' => 2,
                    'low_libido' => 2,
                ),
            ),
            'required' => true,
        ),
        'menopause_q2' => array(
            'title' => 'Which stage of menopause do you believe you are in?',
            'type' => 'radio',
            'options' => array(
                'perimenopause' => 'Perimenopause',
                'menopause' => 'Menopause',
                'postmenopause' => 'Postmenopause',
                'unsure' => 'Unsure',
            ),
            'scoring' => array(
                'category' => 'Menopause Stage',
                'weight' => 2,
                'answers' => array(
                    'perimenopause' => 3,
                    'menopause' => 2,
                    'postmenopause' => 4,
                    'unsure' => 3,
                ),
            ),
            'required' => true,
        ),
    ),
); 