<?php
/**
 * Assessment Definition: Menopause Assessment
 * Enhanced by Dr. Elena Harmonix (Endocrinology Master)
 * Evidence-based endocrinology implementation
 *
 * @package ENNU_Life
 * @version 62.3.2
 */

return array(
    'title' => 'Menopause Assessment',
    'assessment_engine' => 'quantitative',
    'gender_filter' => 'female',
    'questions' => array(
        'menopause_q_age' => array(
            'title' => 'What is your age?',
            'type' => 'radio',
            'options' => array(
                'under_35' => 'Under 35',
                '35_40' => '35-40',
                '41_45' => '41-45',
                '46_50' => '46-50',
                '51_55' => '51-55',
                'over_55' => 'Over 55',
            ),
            'scoring' => array(
                'category' => 'Age Factors',
                'weight' => 1.5,
                'answers' => array(
                    'under_35' => 8,
                    '35_40' => 7,
                    '41_45' => 6,
                    '46_50' => 5,
                    '51_55' => 4,
                    'over_55' => 3,
                ),
            ),
            'required' => true,
        ),
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
                'fatigue' => 'Fatigue and low energy',
                'brain_fog' => 'Brain fog or memory issues',
                'weight_gain' => 'Weight gain, especially around midsection',
                'hair_loss' => 'Hair loss or thinning',
                'skin_changes' => 'Skin changes (dryness, aging)',
                'none' => 'None of the above',
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
                    'fatigue' => 3,
                    'brain_fog' => 3,
                    'weight_gain' => 2,
                    'hair_loss' => 2,
                    'skin_changes' => 2,
                    'none' => 8,
                ),
            ),
            'required' => true,
        ),
        'menopause_q2' => array(
            'title' => 'Which stage of menopause do you believe you are in?',
            'type' => 'radio',
            'options' => array(
                'premenopause' => 'Premenopause - Regular periods',
                'perimenopause' => 'Perimenopause - Irregular periods',
                'menopause' => 'Menopause - No periods for 12+ months',
                'postmenopause' => 'Postmenopause - 5+ years since last period',
                'unsure' => 'Unsure',
            ),
            'scoring' => array(
                'category' => 'Menopause Stage',
                'weight' => 2,
                'answers' => array(
                    'premenopause' => 8,
                    'perimenopause' => 5,
                    'menopause' => 4,
                    'postmenopause' => 6,
                    'unsure' => 5,
                ),
            ),
            'required' => true,
        ),
        'menopause_q3' => array(
            'title' => 'How would you rate the severity of your menopausal symptoms?',
            'type' => 'radio',
            'options' => array(
                'none' => 'None - No symptoms',
                'mild' => 'Mild - Noticeable but manageable',
                'moderate' => 'Moderate - Some interference with daily life',
                'severe' => 'Severe - Significant impact on daily life',
                'very_severe' => 'Very Severe - Major impact on quality of life',
            ),
            'scoring' => array(
                'category' => 'Symptom Impact',
                'weight' => 2.5,
                'answers' => array(
                    'none' => 8,
                    'mild' => 6,
                    'moderate' => 4,
                    'severe' => 2,
                    'very_severe' => 1,
                ),
            ),
            'required' => true,
        ),
        'menopause_q4' => array(
            'title' => 'How would you describe your current stress levels?',
            'type' => 'radio',
            'options' => array(
                'very_low' => 'Very Low - I rarely feel stressed',
                'low' => 'Low - I experience minimal stress',
                'moderate' => 'Moderate - I experience some stress but manage it well',
                'high' => 'High - I frequently feel stressed and overwhelmed',
                'very_high' => 'Very High - I feel constantly stressed and struggle to cope',
            ),
            'scoring' => array(
                'category' => 'Stress & Cortisol',
                'weight' => 2,
                'answers' => array(
                    'very_low' => 8,
                    'low' => 7,
                    'moderate' => 5,
                    'high' => 3,
                    'very_high' => 2,
                ),
            ),
            'required' => true,
        ),
        'menopause_q5' => array(
            'title' => 'How would you describe your sleep quality?',
            'type' => 'radio',
            'options' => array(
                'excellent' => 'Excellent - Deep, restorative sleep every night',
                'good' => 'Good - Generally restful sleep most nights',
                'moderate' => 'Moderate - Some sleep issues but mostly restful',
                'poor' => 'Poor - Frequent sleep problems affecting daily life',
                'very_poor' => 'Very Poor - Severe sleep issues',
            ),
            'scoring' => array(
                'category' => 'Sleep Quality',
                'weight' => 2,
                'answers' => array(
                    'excellent' => 8,
                    'good' => 7,
                    'moderate' => 5,
                    'poor' => 3,
                    'very_poor' => 2,
                ),
            ),
            'required' => true,
        ),
        'menopause_q6' => array(
            'title' => 'Do you have a family history of hormonal disorders?',
            'type' => 'multiselect',
            'options' => array(
                'none' => 'None known',
                'early_menopause' => 'Early menopause',
                'diabetes' => 'Diabetes (Type 1 or Type 2)',
                'thyroid' => 'Thyroid disorders',
                'pcos' => 'Polycystic Ovary Syndrome (PCOS)',
                'infertility' => 'Infertility issues',
                'other' => 'Other hormonal disorders',
            ),
            'scoring' => array(
                'category' => 'Family History',
                'weight' => 1.5,
                'answers' => array(
                    'none' => 8,
                    'early_menopause' => 4,
                    'diabetes' => 4,
                    'thyroid' => 5,
                    'pcos' => 4,
                    'infertility' => 5,
                    'other' => 4,
                ),
            ),
            'required' => true,
        ),
        'menopause_q7' => array(
            'title' => 'Have you had any recent blood tests for hormone levels?',
            'type' => 'radio',
            'options' => array(
                'none' => 'No recent tests',
                'basic' => 'Basic hormone panel',
                'comprehensive' => 'Comprehensive hormone panel',
                'regular' => 'Regular monitoring',
            ),
            'scoring' => array(
                'category' => 'Biomarker Monitoring',
                'weight' => 1.5,
                'answers' => array(
                    'none' => 5,
                    'basic' => 6,
                    'comprehensive' => 7,
                    'regular' => 8,
                ),
            ),
            'required' => true,
        ),
        'menopause_q8' => array(
            'title' => 'How would you rate your overall hormonal health during menopause?',
            'type' => 'radio',
            'options' => array(
                'excellent' => 'Excellent - Managing symptoms well',
                'good' => 'Good - Some symptoms but manageable',
                'moderate' => 'Moderate - Significant symptoms but coping',
                'poor' => 'Poor - Difficult symptoms affecting quality of life',
                'very_poor' => 'Very Poor - Severe symptoms significantly impacting life',
            ),
            'scoring' => array(
                'category' => 'Self-Assessment',
                'weight' => 1.5,
                'answers' => array(
                    'excellent' => 8,
                    'good' => 7,
                    'moderate' => 5,
                    'poor' => 3,
                    'very_poor' => 2,
                ),
            ),
            'required' => true,
        ),
    ),
); 