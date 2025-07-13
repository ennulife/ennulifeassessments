<?php
/**
 * ENNU Life Assessment Questions Configuration - MASTER
 *
 * This file centralizes all assessment questions.
 *
 * @package ENNU_Life
 * @version 25.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'hair_assessment' => array(
        array(
            'id' => 'hair_q1',
            'type' => 'dob_dropdowns',
            'title' => 'What is your date of birth?',
            'scoring_key' => 'dob',
            'global_key' => 'dob'
        ),
        array(
            'id' => 'hair_q2',
            'type' => 'radio',
            'title' => 'What is your gender?',
            'scoring_key' => 'gender',
            'global_key' => 'gender',
            'options' => array(
                'male' => 'Male',
                'female' => 'Female'
            )
        ),
        array(
            'id' => 'hair_q3',
            'type' => 'radio',
            'title' => 'What are your main hair concerns?',
            'scoring_key' => 'hair_concerns',
            'options' => array(
                'thinning' => 'Thinning Hair',
                'receding' => 'Receding Hairline',
                'bald_spots' => 'Bald Spots',
                'overall_loss' => 'Overall Hair Loss'
            )
        ),
        array(
            'id' => 'hair_q4',
            'type' => 'radio',
            'title' => 'How long have you been experiencing hair loss?',
            'scoring_key' => 'duration',
            'options' => array(
                'recent' => 'Less than 6 months',
                'moderate' => '6 months - 2 years',
                'long' => '2-5 years',
                'very_long' => 'More than 5 years'
            )
        ),
        array(
            'id' => 'hair_q5',
            'type' => 'radio',
            'title' => 'How would you describe the speed of your hair loss?',
            'scoring_key' => 'speed',
            'options' => array(
                'slow' => 'Very Slow',
                'moderate' => 'Moderate',
                'fast' => 'Fast',
                'very_fast' => 'Very Fast'
            )
        ),
        array(
            'id' => 'hair_q6',
            'type' => 'radio',
            'title' => 'Is there a history of hair loss in your family?',
            'scoring_key' => 'family_history',
            'options' => array(
                'none' => 'No Family History',
                'mother' => 'Mother\'s Side',
                'father' => 'Father\'s Side',
                'both' => 'Both Sides'
            )
        ),
        array(
            'id' => 'hair_q7',
            'type' => 'radio',
            'title' => 'What is your current stress level?',
            'scoring_key' => 'stress_level',
            'options' => array(
                'low' => 'Low Stress',
                'moderate' => 'Moderate Stress',
                'high' => 'High Stress',
                'very_high' => 'Very High Stress'
            )
        ),
        array(
            'id' => 'hair_q8',
            'type' => 'radio',
            'title' => 'How would you rate your current diet quality?',
            'scoring_key' => 'diet_quality',
            'options' => array(
                'excellent' => 'Excellent',
                'good' => 'Good',
                'fair' => 'Fair',
                'poor' => 'Poor'
            )
        ),
        array(
            'id' => 'hair_q9',
            'type' => 'radio',
            'title' => 'Have you tried any hair loss treatments before?',
            'scoring_key' => 'previous_treatments',
            'options' => array(
                'none' => 'No Treatments',
                'otc' => 'Over-the-Counter',
                'prescription' => 'Prescription Meds',
                'procedures' => 'Medical Procedures'
            )
        ),
        array(
            'id' => 'hair_q10',
            'type' => 'checkbox',
            'title' => 'What are your hair goals?',
            'scoring_key' => 'goals',
            'options' => array(
                'stop_loss' => 'Stop Hair Loss',
                'regrow' => 'Regrow Hair',
                'thicken' => 'Thicken Hair',
                'improve' => 'Overall Improvement'
            )
        ),
        array(
            'id' => 'hair_q11',
            'type' => 'contact_info',
            'title' => 'Your Contact Information'
        )
    ),
    'ed_treatment_assessment' => array(
        array(
            'id' => 'ed_treatment_q1',
            'type' => 'dob_dropdowns',
            'title' => 'What is your date of birth?',
            'scoring_key' => 'dob',
            'global_key' => 'dob'
        ),
        array(
            'id' => 'ed_treatment_q2',
            'type' => 'radio',
            'title' => 'What is your relationship status?',
            'scoring_key' => 'relationship_status',
            'options' => array(
                'single' => 'Single',
                'dating' => 'Dating',
                'married' => 'Married/Partnered',
                'divorced' => 'Divorced/Separated'
            )
        ),
        array(
            'id' => 'ed_treatment_q3',
            'type' => 'radio',
            'title' => 'How would you describe the severity of your ED?',
            'scoring_key' => 'severity',
            'options' => array(
                'mild' => 'Mild',
                'moderate' => 'Moderate',
                'severe' => 'Severe',
                'complete' => 'Complete'
            )
        ),
        array(
            'id' => 'ed_treatment_q4',
            'type' => 'radio',
            'title' => 'How long have you been experiencing symptoms of ED?',
            'scoring_key' => 'duration',
            'options' => array(
                'recent' => 'Less than 6 months',
                'moderate' => '6 months - 2 years',
                'long' => '2-5 years',
                'very_long' => 'More than 5 years'
            )
        ),
        array(
            'id' => 'ed_treatment_q5',
            'type' => 'checkbox',
            'title' => 'Do you have any of the following health conditions?',
            'scoring_key' => 'health_conditions',
            'options' => array(
                'none' => 'None of these',
                'diabetes' => 'Diabetes',
                'heart' => 'Heart Disease',
                'hypertension' => 'High Blood Pressure'
            )
        ),
        array(
            'id' => 'ed_treatment_q6',
            'type' => 'radio',
            'title' => 'Have you tried any ED treatments before?',
            'scoring_key' => 'previous_treatments',
            'options' => array(
                'none' => 'No previous treatments',
                'oral' => 'Oral medications',
                'injections' => 'Injections',
                'devices' => 'Vacuum devices'
            )
        ),
        array(
            'id' => 'ed_treatment_q7',
            'type' => 'radio',
            'title' => 'Do you smoke or use tobacco products?',
            'scoring_key' => 'smoking_status',
            'options' => array(
                'no' => 'No',
                'yes_daily' => 'Yes, daily',
                'yes_occasionally' => 'Yes, occasionally',
                'former' => 'I am a former smoker'
            )
        ),
        array(
            'id' => 'ed_treatment_q8',
            'type' => 'radio',
            'title' => 'How often do you exercise?',
            'scoring_key' => 'exercise',
            'options' => array(
                'never' => 'Never',
                'rarely' => 'Rarely',
                'regularly' => 'Regularly',
                'daily' => 'Daily'
            )
        ),
        array(
            'id' => 'ed_treatment_q9',
            'type' => 'radio',
            'title' => 'How would you describe your current stress level?',
            'scoring_key' => 'stress_level',
            'options' => array(
                'low' => 'Low',
                'moderate' => 'Moderate',
                'high' => 'High',
                'very_high' => 'Very High'
            )
        ),
        array(
            'id' => 'ed_treatment_q10',
            'type' => 'checkbox',
            'title' => 'What are your primary goals for seeking treatment?',
            'scoring_key' => 'goals',
            'options' => array(
                'restore' => 'Restore function',
                'confidence' => 'Boost confidence',
                'performance' => 'Improve performance',
                'relationship' => 'Improve relationship'
            )
        ),
        array(
            'id' => 'ed_treatment_q11',
            'type' => 'checkbox',
            'title' => 'Are you currently taking any of the following types of medications?',
            'scoring_key' => 'medications',
            'options' => array(
                'none' => 'No medications',
                'blood_pressure' => 'Blood pressure meds',
                'antidepressants' => 'Antidepressants',
                'other' => 'Other medications'
            )
        ),
        array(
            'id' => 'ed_treatment_q12',
            'type' => 'contact_info',
            'title' => 'Your Contact Information'
        )
    ),
    'weight_loss_assessment' => array(
        array(
            'id' => 'weight_loss_q1',
            'type' => 'dob_dropdowns',
            'title' => 'What is your date of birth?',
            'scoring_key' => 'dob',
            'global_key' => 'dob'
        ),
        array(
            'id' => 'weight_loss_q2',
            'type' => 'radio',
            'title' => 'What is your gender?',
            'scoring_key' => 'gender',
            'global_key' => 'gender',
            'options' => array(
                'male' => 'Male',
                'female' => 'Female'
            )
        ),
        array(
            'id' => 'weight_loss_q3',
            'type' => 'radio',
            'title' => 'What is your primary weight loss goal?',
            'scoring_key' => 'primary_goal',
            'options' => array(
                'lose_10' => 'Lose 10-20 lbs',
                'lose_30' => 'Lose 20-50 lbs',
                'lose_50' => 'Lose 50+ lbs',
                'maintain' => 'Maintain current weight'
            )
        ),
        array(
            'id' => 'weight_loss_q4',
            'type' => 'height_weight',
            'title' => 'What is your height and weight?',
            'scoring_key' => 'bmi'
        ),
        array(
            'id' => 'weight_loss_q5',
            'type' => 'radio',
            'title' => 'How often do you exercise?',
            'scoring_key' => 'exercise_frequency',
            'options' => array(
                'never' => 'Never',
                'rarely' => '1-2 times/week',
                'often' => '3-4 times/week',
                'daily' => '5+ times/week'
            )
        ),
        array(
            'id' => 'weight_loss_q6',
            'type' => 'radio',
            'title' => 'How would you describe your typical diet?',
            'scoring_key' => 'diet_quality',
            'options' => array(
                'unhealthy' => 'Mostly Unhealthy',
                'balanced' => 'Generally Balanced',
                'healthy' => 'Very Healthy',
                'strict' => 'Strict Diet'
            )
        ),
        array(
            'id' => 'weight_loss_q7',
            'type' => 'radio',
            'title' => 'How many hours of sleep do you get per night?',
            'scoring_key' => 'sleep_quality',
            'options' => array(
                'less_5' => 'Less than 5 hours',
                '5_6' => '5-6 hours',
                '7_8' => '7-8 hours',
                'more_8' => 'More than 8 hours'
            )
        ),
        array(
            'id' => 'weight_loss_q8',
            'type' => 'radio',
            'title' => 'How would you rate your daily stress levels?',
            'scoring_key' => 'stress_level',
            'options' => array(
                'low' => 'Low',
                'moderate' => 'Moderate',
                'high' => 'High',
                'very_high' => 'Very High'
            )
        ),
        array(
            'id' => 'weight_loss_q9',
            'type' => 'radio',
            'title' => 'What has been your experience with weight loss in the past?',
            'scoring_key' => 'previous_attempts',
            'options' => array(
                'no_success' => 'Never had lasting success',
                'some_success' => 'Some success, but gained it back',
                'good_success' => 'Good success, maintained for a while',
                'first_time' => 'This is my first serious attempt'
            )
        ),
        array(
            'id' => 'weight_loss_q10',
            'type' => 'checkbox',
            'title' => 'Do you have any of these eating habits?',
            'scoring_key' => 'eating_habits',
            'options' => array(
                'emotional' => 'Emotional eating',
                'late_night' => 'Late-night snacking',
                'fast_food' => 'Frequent fast food',
                'sugary_drinks' => 'Sugary drinks'
            )
        ),
        array(
            'id' => 'weight_loss_q11',
            'type' => 'radio',
            'title' => 'How motivated are you to make a change?',
            'scoring_key' => 'motivation_level',
            'options' => array(
                'low' => 'Not very motivated',
                'somewhat' => 'Somewhat motivated',
                'very' => 'Very motivated',
                'committed' => 'Committed and ready'
            )
        ),
        array(
            'id' => 'weight_loss_q12',
            'type' => 'radio',
            'title' => 'What kind of support system do you have?',
            'scoring_key' => 'support_system',
            'options' => array(
                'none' => 'I\'m on my own',
                'partner' => 'Partner/Spouse',
                'family' => 'Family and Friends',
                'professional' => 'Professional (coach, etc.)'
            )
        ),
        array(
            'id' => 'weight_loss_q13',
            'type' => 'contact_info',
            'title' => 'Your Contact Information'
        )
    ),
    'health_assessment' => array(
        array(
            'id' => 'health_q1',
            'type' => 'dob_dropdowns',
            'title' => 'What is your date of birth?',
            'scoring_key' => 'dob',
            'global_key' => 'dob'
        ),
        array(
            'id' => 'health_q2',
            'type' => 'radio',
            'title' => 'What is your gender?',
            'scoring_key' => 'gender',
            'global_key' => 'gender',
            'options' => array(
                'male' => 'Male',
                'female' => 'Female'
            )
        ),
        array(
            'id' => 'health_q3',
            'type' => 'radio',
            'title' => 'How would you rate your overall health?',
            'scoring_key' => 'overall_health',
            'options' => array(
                'poor' => 'Poor',
                'fair' => 'Fair',
                'good' => 'Good',
                'excellent' => 'Excellent'
            )
        ),
        array(
            'id' => 'health_q4',
            'type' => 'radio',
            'title' => 'How would you describe your energy levels throughout the day?',
            'scoring_key' => 'energy_levels',
            'options' => array(
                'low' => 'Consistently Low',
                'crash' => 'I crash in the afternoon',
                'moderate' => 'Generally Okay',
                'high' => 'High and Stable'
            )
        ),
        array(
            'id' => 'health_q5',
            'type' => 'radio',
            'title' => 'How often do you engage in moderate to intense physical activity?',
            'scoring_key' => 'exercise_frequency',
            'options' => array(
                'rarely' => 'Rarely or Never',
                'sometimes' => '1-2 times a week',
                'often' => '3-5 times a week',
                'daily' => 'Almost every day'
            )
        ),
        array(
            'id' => 'health_q6',
            'type' => 'radio',
            'title' => 'How would you describe your typical diet?',
            'scoring_key' => 'diet_quality',
            'options' => array(
                'processed' => 'High in processed foods',
                'average' => 'A typical Western diet',
                'healthy' => 'Mostly whole foods',
                'very_healthy' => 'Very clean, whole foods diet'
            )
        ),
        array(
            'id' => 'health_q7',
            'type' => 'radio',
            'title' => 'How would you rate your sleep quality?',
            'scoring_key' => 'sleep_quality',
            'options' => array(
                'poor' => 'Poor, I wake up tired',
                'fair' => 'Fair, could be better',
                'good' => 'Good, usually restful',
                'excellent' => 'Excellent, I wake up refreshed'
            )
        ),
        array(
            'id' => 'health_q8',
            'type' => 'radio',
            'title' => 'How well do you manage stress?',
            'scoring_key' => 'stress_management',
            'options' => array(
                'poorly' => 'I don\'t manage it well',
                'somewhat' => 'I have some coping methods',
                'well' => 'I manage it well',
                'proactively' => 'I have a proactive routine'
            )
        ),
        array(
            'id' => 'health_q9',
            'type' => 'radio',
            'title' => 'Do you get regular preventive care (e.g., check-ups)?',
            'scoring_key' => 'preventive_care',
            'options' => array(
                'never' => 'Never or rarely',
                'sometimes' => 'Only when I have a problem',
                'regularly' => 'I have regular annual check-ups'
            )
        ),
        array(
            'id' => 'health_q10',
            'type' => 'checkbox',
            'title' => 'What are your top health goals?',
            'scoring_key' => 'health_goals',
            'options' => array(
                'longevity' => 'Improve Longevity',
                'energy' => 'Increase Energy',
                'weight' => 'Manage Weight',
                'mental_clarity' => 'Improve Mental Clarity',
                'athletic_performance' => 'Enhance Athletic Performance'
            )
        ),
        array(
            'id' => 'health_q11',
            'type' => 'contact_info',
            'title' => 'Your Contact Information'
        )
    ),
    'skin_assessment' => array(
        array(
            'id' => 'skin_q1',
            'type' => 'dob_dropdowns',
            'title' => 'What is your date of birth?',
            'scoring_key' => 'dob',
            'global_key' => 'dob'
        ),
        array(
            'id' => 'skin_q2',
            'type' => 'radio',
            'title' => 'What is your gender?',
            'scoring_key' => 'gender',
            'global_key' => 'gender',
            'options' => array(
                'male' => 'Male',
                'female' => 'Female'
            )
        ),
        array(
            'id' => 'skin_q3',
            'type' => 'radio',
            'title' => 'What is your skin type?',
            'scoring_key' => 'skin_type',
            'options' => array(
                'normal' => 'Normal',
                'dry' => 'Dry',
                'oily' => 'Oily',
                'combination' => 'Combination',
                'sensitive' => 'Sensitive'
            )
        ),
        array(
            'id' => 'skin_q4',
            'type' => 'checkbox',
            'title' => 'What is your primary skin concern?',
            'scoring_key' => 'primary_concern',
            'options' => array(
                'acne' => 'Acne & Blemishes',
                'aging' => 'Fine Lines & Wrinkles',
                'pigmentation' => 'Dark Spots & Hyperpigmentation',
                'redness' => 'Redness & Rosacea',
                'dullness' => 'Dryness & Dehydration'
            )
        ),
        array(
            'id' => 'skin_q5',
            'type' => 'radio',
            'title' => 'How much sun exposure do you get?',
            'scoring_key' => 'sun_exposure',
            'options' => array(
                'minimal' => 'Rarely, I\'m mostly indoors',
                'moderate' => 'Sometimes, on weekends',
                'high' => 'Daily, but I use sunscreen'
            )
        ),
        array(
            'id' => 'skin_q6',
            'type' => 'radio',
            'title' => 'What is your current skincare routine?',
            'scoring_key' => 'skincare_routine',
            'options' => array(
                'none' => 'Minimal (cleanse, maybe moisturize)',
                'basic' => 'Basic (cleanse, moisturize, SPF)',
                'advanced' => 'Advanced (serums, exfoliants, etc.)'
            )
        ),
        array(
            'id' => 'skin_q7',
            'type' => 'checkbox',
            'title' => 'How does your skin typically react to new products?',
            'scoring_key' => 'skin_reactivity',
            'options' => array(
                'calm' => 'It\'s usually calm',
                'redness' => 'Gets red or flushed',
                'breakouts' => 'I get breakouts',
                'dryness' => 'Becomes dry and tight'
            )
        ),
        array(
            'id' => 'skin_q8',
            'type' => 'checkbox',
            'title' => 'Which of these lifestyle factors apply to you?',
            'scoring_key' => 'lifestyle_factors',
            'options' => array(
                'smoker' => 'I smoke',
                'high_stress' => 'I have high stress levels',
                'poor_sleep' => 'I have poor sleep quality',
                'high_sugar_diet' => 'My diet is high in sugar/processed foods'
            )
        ),
        array(
            'id' => 'skin_q9',
            'type' => 'contact_info',
            'title' => 'Your Contact Information'
        )
    )
); 