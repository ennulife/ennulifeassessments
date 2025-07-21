<?php
/**
 * Assessment Definition: Skin Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'Skin Assessment',
    'assessment_engine' => 'quantitative',
    'questions' => array(
        'skin_q_gender' => array(
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
        'skin_q1' => array(
            'title' => 'What is your skin type?',
            'type' => 'radio',
            'options' => array(
                'oily' => 'Oily',
                'dry' => 'Dry',
                'combination' => 'Combination',
                'normal' => 'Normal',
                'sensitive' => 'Sensitive',
            ),
            'scoring' => array(
                'category' => 'Skin Characteristics',
                'weight' => 2,
                'answers' => array(
                    'oily' => 3,
                    'dry' => 3,
                    'combination' => 4,
                    'normal' => 5,
                    'sensitive' => 2,
                ),
            ),
            'required' => true,
        ),
        'skin_q2' => array(
            'title' => 'What are your primary skin concerns?',
            'type' => 'multiselect',
            'options' => array(
                'acne' => 'Acne and breakouts',
                'aging' => 'Fine lines and wrinkles',
                'pigmentation' => 'Dark spots and hyperpigmentation',
                'redness' => 'Redness and rosacea',
                'dullness' => 'Dullness and lack of radiance',
                'uneven_texture' => 'Uneven skin texture',
                'none' => 'None of the above',
            ),
            'scoring' => array(
                'category' => 'Skin Issues',
                'weight' => 2.5,
                'answers' => array(
                    'acne' => 2,
                    'aging' => 3,
                    'pigmentation' => 3,
                    'redness' => 2,
                    'dullness' => 4,
                    'uneven_texture' => 3,
                    'none' => 5,
                ),
            ),
            'required' => true,
        ),
        'skin_q3' => array(
            'title' => 'How much time do you spend in the sun on an average day?',
            'type' => 'radio',
            'options' => array(
                'none' => 'Hardly any',
                'minimal' => 'Less than 30 minutes',
                'moderate' => 'Between 30 minutes and 2 hours',
                'high' => 'More than 2 hours',
            ),
            'scoring' => array(
                'category' => 'Environmental Factors',
                'weight' => 2,
                'answers' => array(
                    'none' => 5,
                    'minimal' => 4,
                    'moderate' => 2,
                    'high' => 1,
                ),
            ),
            'required' => true,
        ),
        'skin_q4' => array(
            'title' => 'How would you describe your daily skincare routine?',
            'type' => 'radio',
            'options' => array(
                'none' => 'None (I don\'t have a regular routine)',
                'basic' => 'Basic (e.g., cleanser, moisturizer)',
                'intermediate' => 'Intermediate (e.g., includes toner, eye cream)',
                'advanced' => 'Advanced (e.g., includes serums, masks, exfoliants)',
                'clinical' => 'Clinical-Grade (e.g., prescription topicals, professional treatments)',
            ),
            'scoring' => array(
                'category' => 'Skincare Habits',
                'weight' => 1.5,
                'answers' => array(
                    'none' => 1,
                    'basic' => 2,
                    'intermediate' => 3,
                    'advanced' => 4,
                    'clinical' => 5,
                ),
            ),
            'required' => true,
        ),
        'skin_q5' => array(
            'title' => 'How do your skin concerns affect your confidence or social life?',
            'type' => 'radio',
            'options' => array(
                'not_at_all' => 'Not at all',
                'slightly' => 'Slightly',
                'moderately' => 'Moderately',
                'significantly' => 'Significantly',
            ),
            'scoring' => array(
                'category' => 'Psychological Factors',
                'weight' => 2,
                'answers' => array(
                    'not_at_all' => 5,
                    'slightly' => 4,
                    'moderately' => 2,
                    'significantly' => 1,
                ),
            ),
            'required' => true,
        ),
        'skin_q6' => array(
            'title' => 'How would you describe your typical diet?',
            'type' => 'radio',
            'options' => array(
                'whole_foods' => 'Primarily whole foods, low in sugar and dairy',
                'balanced' => 'Generally balanced',
                'processed' => 'High in processed foods, sugar, or dairy',
                'irregular' => 'Irregular or often skip meals',
            ),
            'scoring' => array(
                'category' => 'Nutrition',
                'weight' => 2,
                'answers' => array(
                    'whole_foods' => 5,
                    'balanced' => 4,
                    'processed' => 1,
                    'irregular' => 2,
                ),
            ),
            'required' => true,
        ),
        'skin_q7' => array(
            'title' => 'How much water do you drink per day?',
            'type' => 'radio',
            'options' => array(
                'more_than_8' => 'More than 8 glasses',
                '6_8' => '6-8 glasses',
                '3_5' => '3-5 glasses',
                'less_than_3' => 'Less than 3 glasses',
            ),
            'scoring' => array(
                'category' => 'Hydration',
                'weight' => 1.5,
                'answers' => array(
                    'more_than_8' => 5,
                    '6_8' => 4,
                    '3_5' => 2,
                    'less_than_3' => 1,
                ),
            ),
            'required' => true,
        ),
        'skin_q8' => array(
            'title' => 'How would you rate your sleep quality?',
            'type' => 'radio',
            'options' => array(
                'excellent' => 'Excellent',
                'good' => 'Good',
                'fair' => 'Fair',
                'poor' => 'Poor',
            ),
            'scoring' => array(
                'category' => 'Lifestyle Factors',
                'weight' => 1.5,
                'answers' => array(
                    'excellent' => 5,
                    'good' => 4,
                    'fair' => 2,
                    'poor' => 1,
                ),
            ),
            'required' => true,
        ),
        'skin_q9' => array(
            'title' => 'How would you describe your current stress level?',
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