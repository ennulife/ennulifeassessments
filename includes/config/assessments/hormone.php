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
        'hormone_q_age' => array(
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
                    'under_25' => 5,
                    '25_35' => 4,
                    '36_45' => 3,
                    '46_55' => 2,
                    'over_55' => 1,
                ),
            ),
            'required' => true,
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
                'hot_flashes' => 'Hot flashes or temperature sensitivity',
                'hair_loss' => 'Hair loss or thinning',
                'skin_changes' => 'Skin changes (dryness, acne, aging)',
                'heart_palpitations' => 'Heart palpitations or irregular heartbeat',
                'digestive_issues' => 'Digestive issues or bloating',
                'joint_pain' => 'Joint pain or muscle weakness',
                'memory_problems' => 'Memory problems or forgetfulness',
                'depression' => 'Depression or low mood',
                'none' => 'None of the above',
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
                    'hot_flashes' => 1,
                    'hair_loss' => 1,
                    'skin_changes' => 2,
                    'heart_palpitations' => 1,
                    'digestive_issues' => 2,
                    'joint_pain' => 1,
                    'memory_problems' => 2,
                    'depression' => 1,
                    'none' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q2' => array(
            'title' => 'How would you rate your daily stress levels?',
            'type' => 'radio',
            'options' => array(
                'low' => 'Low - I rarely feel stressed',
                'moderate' => 'Moderate - I experience some stress but manage it well',
                'high' => 'High - I frequently feel stressed and overwhelmed',
                'very_high' => 'Very High - I feel constantly stressed and struggle to cope',
            ),
            'scoring' => array(
                'category' => 'Stress & Cortisol',
                'weight' => 2.5,
                'answers' => array(
                    'low' => 5,
                    'moderate' => 3,
                    'high' => 1,
                    'very_high' => 1,
                ),
            ),
            'required' => true,
        ),
        'hormone_q3' => array(
            'title' => 'How would you describe your energy levels throughout the day?',
            'type' => 'radio',
            'options' => array(
                'consistently_low' => 'Consistently low - I struggle with energy all day',
                'afternoon_crash' => 'I crash in the afternoon - good morning, tired evening',
                'generally_stable' => 'Generally stable - consistent energy levels',
                'high_consistent' => 'High and consistent - I have good energy all day',
            ),
            'scoring' => array(
                'category' => 'Energy & Vitality',
                'weight' => 2,
                'answers' => array(
                    'consistently_low' => 1,
                    'afternoon_crash' => 2,
                    'generally_stable' => 4,
                    'high_consistent' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q4' => array(
            'title' => 'How many hours of sleep do you typically get per night?',
            'type' => 'radio',
            'options' => array(
                'less_than_5' => 'Less than 5 hours',
                '5_6' => '5-6 hours',
                '7_8' => '7-8 hours',
                'more_than_8' => 'More than 8 hours',
            ),
            'scoring' => array(
                'category' => 'Sleep Quality',
                'weight' => 2,
                'answers' => array(
                    'less_than_5' => 1,
                    '5_6' => 2,
                    '7_8' => 5,
                    'more_than_8' => 3,
                ),
            ),
            'required' => true,
        ),
        'hormone_q5' => array(
            'title' => 'How would you describe your appetite and eating patterns?',
            'type' => 'radio',
            'options' => array(
                'poor_appetite' => 'Poor appetite - I often forget to eat',
                'emotional_eating' => 'Emotional eating - I eat when stressed or emotional',
                'cravings' => 'Strong cravings - especially for sugar or carbs',
                'stable_appetite' => 'Stable appetite - I eat regular meals when hungry',
            ),
            'scoring' => array(
                'category' => 'Metabolic Health',
                'weight' => 2,
                'answers' => array(
                    'poor_appetite' => 2,
                    'emotional_eating' => 1,
                    'cravings' => 1,
                    'stable_appetite' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q6' => array(
            'title' => 'How would you rate your ability to handle temperature changes?',
            'type' => 'radio',
            'options' => array(
                'very_sensitive' => 'Very sensitive - I am always too hot or too cold',
                'somewhat_sensitive' => 'Somewhat sensitive - temperature affects me more than others',
                'moderately_tolerant' => 'Moderately tolerant - I handle temperature changes okay',
                'very_tolerant' => 'Very tolerant - temperature changes do not bother me',
            ),
            'scoring' => array(
                'category' => 'Thyroid Function',
                'weight' => 1.5,
                'answers' => array(
                    'very_sensitive' => 1,
                    'somewhat_sensitive' => 2,
                    'moderately_tolerant' => 4,
                    'very_tolerant' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q7' => array(
            'title' => 'How would you describe your menstrual cycle? (Females only)',
            'type' => 'radio',
            'options' => array(
                'irregular' => 'Irregular - unpredictable timing',
                'heavy_painful' => 'Heavy and painful periods',
                'light_infrequent' => 'Light or infrequent periods',
                'regular_normal' => 'Regular and normal',
                'menopause' => 'I am in menopause',
                'not_applicable' => 'Not applicable',
            ),
            'scoring' => array(
                'category' => 'Reproductive Health',
                'weight' => 2,
                'answers' => array(
                    'irregular' => 2,
                    'heavy_painful' => 1,
                    'light_infrequent' => 2,
                    'regular_normal' => 5,
                    'menopause' => 3,
                    'not_applicable' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q8' => array(
            'title' => 'How would you rate your muscle strength and recovery?',
            'type' => 'radio',
            'options' => array(
                'decreasing_strength' => 'Decreasing strength - I feel weaker than before',
                'slow_recovery' => 'Slow recovery - I take longer to recover from exercise',
                'maintaining_strength' => 'Maintaining strength - I can maintain my current level',
                'increasing_strength' => 'Increasing strength - I can build muscle and strength',
            ),
            'scoring' => array(
                'category' => 'Muscle & Strength',
                'weight' => 1.5,
                'answers' => array(
                    'decreasing_strength' => 1,
                    'slow_recovery' => 2,
                    'maintaining_strength' => 4,
                    'increasing_strength' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q9' => array(
            'title' => 'How would you describe your skin and hair quality?',
            'type' => 'radio',
            'options' => array(
                'deteriorating' => 'Deteriorating - skin and hair quality is declining',
                'dry_brittle' => 'Dry and brittle - skin is dry, hair is brittle',
                'maintaining' => 'Maintaining - skin and hair quality is stable',
                'improving' => 'Improving - skin and hair quality is getting better',
            ),
            'scoring' => array(
                'category' => 'Skin & Hair Health',
                'weight' => 1.5,
                'answers' => array(
                    'deteriorating' => 1,
                    'dry_brittle' => 2,
                    'maintaining' => 4,
                    'improving' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q10' => array(
            'title' => 'How would you rate your overall mood and emotional well-being?',
            'type' => 'radio',
            'options' => array(
                'depressed_anxious' => 'Depressed or anxious - I struggle with mood regularly',
                'irritable_moody' => 'Irritable or moody - my mood changes frequently',
                'generally_stable' => 'Generally stable - my mood is usually good',
                'positive_optimistic' => 'Positive and optimistic - I have a good outlook',
            ),
            'scoring' => array(
                'category' => 'Mental Health',
                'weight' => 2,
                'answers' => array(
                    'depressed_anxious' => 1,
                    'irritable_moody' => 2,
                    'generally_stable' => 4,
                    'positive_optimistic' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q11' => array(
            'title' => 'How would you describe your exercise routine and physical activity?',
            'type' => 'radio',
            'options' => array(
                'sedentary' => 'Sedentary - I rarely exercise or move much',
                'light_activity' => 'Light activity - I do some walking or light exercise',
                'moderate_exercise' => 'Moderate exercise - I exercise 3-4 times per week',
                'active_lifestyle' => 'Active lifestyle - I exercise regularly and stay active',
            ),
            'scoring' => array(
                'category' => 'Physical Activity',
                'weight' => 2,
                'answers' => array(
                    'sedentary' => 1,
                    'light_activity' => 2,
                    'moderate_exercise' => 4,
                    'active_lifestyle' => 5,
                ),
            ),
            'required' => true,
        ),
        'hormone_q12' => array(
            'title' => 'How motivated are you to optimize your hormonal health?',
            'type' => 'radio',
            'options' => array(
                'not_motivated' => 'Not very motivated - I am not concerned about hormones',
                'somewhat_motivated' => 'Somewhat motivated - I would like to improve if it is easy',
                'very_motivated' => 'Very motivated - I want to optimize my hormonal health',
                'extremely_motivated' => 'Extremely motivated - I am committed to hormone optimization',
            ),
            'scoring' => array(
                'category' => 'Motivation & Goals',
                'weight' => 1.5,
                'answers' => array(
                    'not_motivated' => 1,
                    'somewhat_motivated' => 3,
                    'very_motivated' => 4,
                    'extremely_motivated' => 5,
                ),
            ),
            'required' => true,
        ),
    ),
); 