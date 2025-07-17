<?php
/**
 * Assessment Definition: Hair Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'Hair Assessment',
    'assessment_engine' => 'quantitative',
    'questions' => array(
        'hair_q1' => array(
            'title' => 'What is your date of birth?',
            'type' => 'dob_dropdowns',
            'required' => true,
            'global_key' => 'user_dob_combined'
        ),
        'hair_q2' => array(
            'title' => 'What is your gender?',
            'type' => 'radio',
            'options' => array(
                'male' => 'Male',
                'female' => 'Female',
                'other' => 'Other / Prefer not to say',
            ),
            'scoring' => array(
                'category' => 'Genetic Factors',
                'weight' => 2,
                'answers' => array(
                    'male' => 3,
                    'female' => 5,
                    'other' => 4,
                ),
            ),
            'required' => true,
            'global_key' => 'gender'
        ),
        'hair_q3' => array(
            'title' => 'What are your main hair concerns?',
            'type' => 'multiselect',
            'options' => array(
                'thinning' => 'Thinning hair',
                'receding' => 'Receding hairline',
                'bald_spots' => 'Bald spots or patches',
                'overall_loss' => 'Overall hair loss and shedding',
                'dandruff' => 'Dandruff or flaky scalp',
                'brittleness' => 'Dryness or brittleness',
            ),
            'scoring' => array(
                'category' => 'Hair Health Status',
                'weight' => 2.5,
                'answers' => array(
                    'thinning' => 4,
                    'receding' => 3,
                    'bald_spots' => 2,
                    'overall_loss' => 1,
                    'dandruff' => 4,
                    'brittleness' => 3,
                ),
            ),
            'required' => true,
        ),
        'hair_q4' => array(
            'title' => 'How long have you been experiencing these concerns?',
            'type' => 'radio',
            'options' => array(
                'less_than_6_months' => 'Less than 6 months',
                '6_to_12_months' => '6 to 12 months',
                '1_to_3_years' => '1 to 3 years',
                'more_than_3_years' => 'More than 3 years',
            ),
            'scoring' => array(
                'category' => 'Progression Timeline',
                'weight' => 2,
                'answers' => array(
                    'less_than_6_months' => 8,
                    '6_to_12_months' => 6,
                    '1_to_3_years' => 4,
                    'more_than_3_years' => 2,
                ),
            ),
            'required' => true,
        ),
        'hair_q5' => array(
            'title' => 'Does anyone in your immediate family have a history of hair loss?',
            'type' => 'radio',
            'options' => array(
                'yes_both' => 'Yes, on both sides of the family',
                'yes_maternal' => 'Yes, on my mother\'s side',
                'yes_paternal' => 'Yes, on my father\'s side',
                'no' => 'No, not to my knowledge',
            ),
            'scoring' => array(
                'category' => 'Genetic Factors',
                'weight' => 2.5,
                'answers' => array(
                    'yes_both' => 1,
                    'yes_maternal' => 3,
                    'yes_paternal' => 2,
                    'no' => 5,
                ),
            ),
            'required' => true,
        ),
        'hair_q6' => array(
            'title' => 'How would you describe your current stress level?',
            'type' => 'radio',
            'options' => array(
                'low' => 'Low',
                'moderate' => 'Moderate',
                'high' => 'High',
                'very_high' => 'Very High',
            ),
            'scoring' => array(
                'category' => 'Lifestyle Factors',
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
        'hair_q7' => array(
            'title' => 'How does your hair concern affect your confidence or social life?',
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
        'hair_q8' => array(
            'title' => 'How would you describe your diet\'s focus on hair-healthy nutrients (e.g., biotin, iron, zinc)?',
            'type' => 'radio',
            'options' => array(
                'very_good' => 'Very good, I focus on these nutrients',
                'good' => 'Good, I eat a balanced diet',
                'average' => 'Average, I don\'t pay much attention',
                'poor' => 'Poor, my diet lacks variety',
            ),
            'scoring' => array(
                'category' => 'Nutritional Support',
                'weight' => 1.5,
                'answers' => array(
                    'very_good' => 5,
                    'good' => 4,
                    'average' => 2,
                    'poor' => 1,
                ),
            ),
            'required' => true,
        ),
        'hair_q9' => array(
            'title' => 'Have you tried any hair loss treatments in the past?',
            'type' => 'radio',
            'options' => array(
                'yes_effective' => 'Yes, and they were effective',
                'yes_ineffective' => 'Yes, but they were not effective',
                'no' => 'No, I have not tried any treatments',
            ),
            'scoring' => array(
                'category' => 'Treatment History',
                'weight' => 1,
                'answers' => array(
                    'yes_effective' => 5,
                    'yes_ineffective' => 1,
                    'no' => 3,
                ),
            ),
            'required' => true,
        ),
        'hair_q10' => array(
            'title' => 'Do you frequently use heat styling tools (e.g., blow dryers, straighteners) or chemical treatments?',
            'type' => 'radio',
            'options' => array(
                'never' => 'Never',
                'rarely' => 'Rarely (once a month)',
                'sometimes' => 'Sometimes (once a week)',
                'often' => 'Often (most days)',
            ),
            'scoring' => array(
                'category' => 'Lifestyle Factors',
                'weight' => 1,
                'answers' => array(
                    'never' => 5,
                    'rarely' => 4,
                    'sometimes' => 2,
                    'often' => 1,
                ),
            ),
            'required' => true,
        ),
    ),
); 