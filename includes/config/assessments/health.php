<?php
/**
 * Assessment Definition: Health Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'Health Assessment',
    'assessment_engine' => 'quantitative',
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
        'health_q1' => array(
            'title' => 'How would you rate your current health?',
            'type' => 'radio',
            'options' => array(
                'excellent' => 'Excellent',
                'good' => 'Good',
                'fair' => 'Fair',
                'poor' => 'Poor',
            ),
            'scoring' => array(
                'category' => 'Current Health Status',
                'weight' => 3,
                'answers' => array(
                    'excellent' => 5,
                    'good' => 4,
                    'fair' => 2,
                    'poor' => 1,
                ),
            ),
            'required' => true,
        ),
        'health_q2' => array(
            'title' => 'How are your energy levels throughout the day?',
            'type' => 'radio',
            'options' => array(
                'high' => 'Consistently High',
                'moderate' => 'Generally Moderate',
                'low' => 'Consistently Low',
                'fluctuating' => 'Fluctuating',
            ),
            'scoring' => array(
                'category' => 'Vitality & Energy',
                'weight' => 2,
                'answers' => array(
                    'high' => 5,
                    'moderate' => 4,
                    'low' => 1,
                    'fluctuating' => 2,
                ),
            ),
            'required' => true,
        ),
        'health_q3' => array(
            'title' => 'How many days per week do you engage in moderate to vigorous exercise?',
            'type' => 'radio',
            'options' => array(
                '5_plus' => '5 or more days',
                '3_4' => '3-4 days',
                '1_2' => '1-2 days',
                'none' => 'I rarely or never exercise',
            ),
            'scoring' => array(
                'category' => 'Physical Activity',
                'weight' => 2,
                'answers' => array(
                    '5_plus' => 5,
                    '3_4' => 4,
                    '1_2' => 2,
                    'none' => 1,
                ),
            ),
            'required' => true,
        ),
        'health_q4' => array(
            'title' => 'How would you describe your typical diet?',
            'type' => 'radio',
            'options' => array(
                'whole_foods' => 'Primarily whole foods, rich in fruits and vegetables',
                'balanced' => 'Generally balanced',
                'processed' => 'High in processed foods and sugar',
                'irregular' => 'Irregular or often skip meals',
            ),
            'scoring' => array(
                'category' => 'Nutrition',
                'weight' => 2.5,
                'answers' => array(
                    'whole_foods' => 5,
                    'balanced' => 4,
                    'processed' => 1,
                    'irregular' => 2,
                ),
            ),
            'required' => true,
        ),
        'health_q5' => array(
            'title' => 'How would you rate your sleep quality?',
            'type' => 'radio',
            'options' => array(
                'excellent' => 'Excellent, I wake up refreshed',
                'good' => 'Good, most of the time',
                'fair' => 'Fair, it could be better',
                'poor' => 'Poor, I rarely feel rested',
            ),
            'scoring' => array(
                'category' => 'Sleep & Recovery',
                'weight' => 2,
                'answers' => array(
                    'excellent' => 5,
                    'good' => 4,
                    'fair' => 2,
                    'poor' => 1,
                ),
            ),
            'required' => true,
        ),
        'health_q6' => array(
            'title' => 'How would you describe your current stress level?',
            'type' => 'radio',
            'options' => array(
                'low' => 'Low',
                'moderate' => 'Moderate',
                'high' => 'High',
                'very_high' => 'Very High',
            ),
            'scoring' => array(
                'category' => 'Stress & Mental Health',
                'weight' => 2,
                'answers' => array(
                    'low' => 5,
                    'moderate' => 4,
                    'high' => 2,
                    'very_high' => 1,
                ),
            ),
            'required' => true,
        ),
        'health_q7' => array(
            'title' => 'How satisfied are you with your current physical appearance (hair, skin, body composition)?',
            'type' => 'radio',
            'options' => array(
                'very_satisfied' => 'Very Satisfied',
                'satisfied' => 'Satisfied',
                'neutral' => 'Neutral',
                'dissatisfied' => 'Dissatisfied',
            ),
            'scoring' => array(
                'category' => 'Aesthetics',
                'weight' => 1,
                'answers' => array(
                    'very_satisfied' => 5,
                    'satisfied' => 4,
                    'neutral' => 3,
                    'dissatisfied' => 1,
                ),
            ),
            'required' => true,
        ),
        'health_q8' => array(
            'title' => 'Do you get regular preventive health check-ups (e.g., annual physicals)?',
            'type' => 'radio',
            'options' => array(
                'yes' => 'Yes, every year',
                'sometimes' => 'Sometimes, but not consistently',
                'no' => 'No, I do not',
            ),
            'scoring' => array(
                'category' => 'Preventive Health',
                'weight' => 1.5,
                'answers' => array(
                    'yes' => 5,
                    'sometimes' => 3,
                    'no' => 1,
                ),
            ),
            'required' => true,
        ),
        'health_q9' => array(
            'title' => 'How motivated are you to actively improve your health?',
            'type' => 'radio',
            'options' => array(
                'very' => 'Very motivated',
                'somewhat' => 'Somewhat motivated',
                'not_very' => 'Not very motivated',
            ),
            'scoring' => array(
                'category' => 'Health Motivation',
                'weight' => 2,
                'answers' => array(
                    'very' => 5,
                    'somewhat' => 3,
                    'not_very' => 1,
                ),
            ),
            'required' => true,
        ),
        'health_q10' => array(
            'title' => 'How would you rate your social connections and relationships?',
            'type' => 'radio',
            'options' => array(
                'strong' => 'Strong and supportive',
                'good' => 'Good, I have several close connections',
                'fair' => 'Fair, I wish I had more social interaction',
                'poor' => 'Poor, I often feel isolated',
            ),
            'scoring' => array(
                'category' => 'Social Support',
                'weight' => 1.5,
                'answers' => array(
                    'strong' => 5,
                    'good' => 4,
                    'fair' => 2,
                    'poor' => 1,
                ),
            ),
            'required' => true,
        ),
    ),
); 