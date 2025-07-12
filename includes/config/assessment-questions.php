<?php
/**
 * ENNU Life Assessment Questions Configuration
 *
 * This file centralizes all assessment questions, making them easier to manage
 * and separating them from the plugin's logic.
 *
 * @package ENNU_Life
 * @version 24.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'welcome_assessment' => array(
        array(
            'title'       => __( 'What gender were you assigned at birth?', 'ennulifeassessments' ),
            'description' => __( 'This helps us tailor our recommendations.', 'ennulifeassessments' ),
            'type'        => 'single',
            'global_key'  => 'gender', // Global Field
            'options'     => array(
                array( 'value' => 'female', 'label' => __( 'Female', 'ennulifeassessments' ) ),
                array( 'value' => 'male', 'label' => __( 'Male', 'ennulifeassessments' ) )
            )
        ),
        array(
            'title'       => __( 'What is your age?', 'ennulifeassessments' ),
            'description' => __( 'Your age helps us understand your health profile.', 'ennulifeassessments' ),
            'type' => 'dob_dropdowns',
            'global_key' => 'user_dob_combined', // Global Field
            'field_name' => 'date_of_birth',
            'required' => true,
            'show_age' => true
        ),
        array(
            'title'       => __( 'What are your health goals?', 'ennulifeassessments' ),
            'description' => __( 'Select all that apply. This helps us personalize your journey.', 'ennulifeassessments' ),
            'type'        => 'multiselect',
            'options'     => array(
                array( 'value' => 'live_longer', 'label' => __( 'Live longer', 'ennulifeassessments' ) ),
                array( 'value' => 'boost_energy', 'label' => __( 'Boost energy', 'ennulifeassessments' ) ),
                array( 'value' => 'improve_sleep', 'label' => __( 'Improve sleep', 'ennulifeassessments' ) ),
                array( 'value' => 'lose_weight', 'label' => __( 'Lose weight', 'ennulifeassessments' ) ),
                array( 'value' => 'build_muscle', 'label' => __( 'Build muscle', 'ennulifeassessments' ) ),
                array( 'value' => 'sharpen_focus', 'label' => __( 'Sharpen focus & memory', 'ennulifeassessments' ) ),
                array( 'value' => 'balance_hormones', 'label' => __( 'Balance hormones', 'ennulifeassessments' ) ),
                array( 'value' => 'improve_mood', 'label' => __( 'Improve mood', 'ennulifeassessments' ) ),
                array( 'value' => 'boost_libido', 'label' => __( 'Boost libido & performance', 'ennulifeassessments' ) ),
                array( 'value' => 'support_heart', 'label' => __( 'Support heart health', 'ennulifeassessments' ) ),
                array( 'value' => 'manage_menopause', 'label' => __( 'Manage menopause', 'ennulifeassessments' ) ),
                array( 'value' => 'increase_testosterone', 'label' => __( 'Increase testosterone', 'ennulifeassessments' ) )
            )
        ),
        array(
            'title'       => __( 'What\'s your first and last name?', 'ennulifeassessments' ),
            'type'        => 'contact_info',
            'fields'      => array(
                array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text' ),
                array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text' )
            )
        ),
        array(
            'title'       => __( 'What\'s your email address?', 'ennulifeassessments' ),
            'type'        => 'contact_info',
            'fields'      => array(
                array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email' )
            )
        ),
        array(
            'title'       => __( 'What\'s your mobile number?', 'ennulifeassessments' ),
            'type'        => 'contact_info',
            'fields'      => array(
                array( 'name' => 'billing_phone', 'label' => 'Mobile Number', 'type' => 'tel' )
            ),
            'button_text' => 'Create My Account'
        )
    ),
    'hair_assessment' => array(
        array(
            'title' => __( 'What\'s your date of birth?', 'ennulifeassessments' ),
            'description' => __( 'This helps us recommend age-appropriate hair treatments.', 'ennulifeassessments' ),
            'type' => 'dob_dropdowns',
            'global_key'  => 'user_dob_combined', // Global Field
            'field_name' => 'date_of_birth',
            'required' => true,
            'show_age' => true
        ),
        array(
            'title' => __( 'What\'s your gender?', 'ennulifeassessments' ),
            'description' => __( 'Hair loss patterns can vary by gender.', 'ennulifeassessments' ),
            'global_key'  => 'gender', // Global Field
            'options' => array(
                array( 'value' => 'male', 'label' => 'Male' ),
                array( 'value' => 'female', 'label' => 'Female' ),
            )
        ),
        array(
            'title' => __( 'What are your main hair concerns?', 'ennulifeassessments' ),
            'description' => __( 'Select your primary hair issue.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'thinning', 'label' => 'Thinning Hair' ),
                array( 'value' => 'receding', 'label' => 'Receding Hairline' ),
                array( 'value' => 'bald_spots', 'label' => 'Bald Spots' ),
                array( 'value' => 'overall_loss', 'label' => 'Overall Hair Loss' )
            )
        ),
        array(
            'title' => __( 'How long have you noticed hair changes?', 'ennulifeassessments' ),
            'description' => __( 'Duration helps determine treatment approach.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'recent', 'label' => 'Less than 6 months' ),
                array( 'value' => 'moderate', 'label' => '6 months - 2 years' ),
                array( 'value' => 'long', 'label' => '2-5 years' ),
                array( 'value' => 'very_long', 'label' => 'More than 5 years' )
            )
        ),
        array(
            'title' => __( 'How would you rate the speed of hair loss?', 'ennulifeassessments' ),
            'description' => __( 'This helps determine urgency of treatment.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'slow', 'label' => 'Very Slow' ),
                array( 'value' => 'moderate', 'label' => 'Moderate' ),
                array( 'value' => 'fast', 'label' => 'Fast' ),
                array( 'value' => 'very_fast', 'label' => 'Very Fast' )
            )
        ),
        array(
            'title' => __( 'Do you have a family history of hair loss?', 'ennulifeassessments' ),
            'description' => __( 'Genetics play a major role in hair loss.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'none', 'label' => 'No Family History' ),
                array( 'value' => 'mother', 'label' => 'Mother\'s Side' ),
                array( 'value' => 'father', 'label' => 'Father\'s Side' ),
                array( 'value' => 'both', 'label' => 'Both Sides' )
            )
        ),
        array(
            'title' => __( 'What\'s your current stress level?', 'ennulifeassessments' ),
            'description' => __( 'Stress can significantly impact hair health.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'low', 'label' => 'Low Stress' ),
                array( 'value' => 'moderate', 'label' => 'Moderate Stress' ),
                array( 'value' => 'high', 'label' => 'High Stress' ),
                array( 'value' => 'very_high', 'label' => 'Very High Stress' )
            )
        ),
        array(
            'title' => __( 'How would you describe your diet quality?', 'ennulifeassessments' ),
            'description' => __( 'Nutrition affects hair growth and strength.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'excellent', 'label' => 'Excellent' ),
                array( 'value' => 'good', 'label' => 'Good' ),
                array( 'value' => 'fair', 'label' => 'Fair' ),
                array( 'value' => 'poor', 'label' => 'Poor' )
            )
        ),
        array(
            'title' => __( 'Have you tried any hair loss treatments?', 'ennulifeassessments' ),
            'description' => __( 'Previous treatments help guide recommendations.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'none', 'label' => 'No Treatments' ),
                array( 'value' => 'otc', 'label' => 'Over-the-Counter' ),
                array( 'value' => 'prescription', 'label' => 'Prescription Meds' ),
                array( 'value' => 'procedures', 'label' => 'Medical Procedures' )
            )
        ),
        array(
            'title' => __( 'What are your hair restoration goals?', 'ennulifeassessments' ),
            'description' => __( 'Understanding your goals helps create the right plan.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'stop_loss', 'label' => 'Stop Hair Loss' ),
                array( 'value' => 'regrow', 'label' => 'Regrow Hair' ),
                array( 'value' => 'thicken', 'label' => 'Thicken Hair' ),
                array( 'value' => 'improve', 'label' => 'Overall Improvement' )
            )
        ),
        array(
            'title' => __( 'Contact Information', 'ennulifeassessments' ),
            'description' => __( 'Please provide your contact details to receive your personalized assessment results.', 'ennulifeassessments' ),
            'type' => 'contact_info',
            'field_name' => 'contact_info',
            'required' => true,
            'fields' => array(
                array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
            ),
            'button_text' => 'View My Assessment Results'
        )
    ),
    'ed_treatment_assessment' => array(
        array(
            'title' => __( 'What\'s your date of birth?', 'ennulifeassessments' ),
            'description' => __( 'Age helps determine the most appropriate treatment approach.', 'ennulifeassessments' ),
            'type' => 'dob_dropdowns',
            'global_key'  => 'user_dob_combined', // Global Field
            'field_name' => 'date_of_birth',
            'required' => true,
            'show_age' => true
        ),
        array(
            'title' => __( 'What\'s your relationship status?', 'ennulifeassessments' ),
            'description' => __( 'This helps us understand your treatment priorities.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'single', 'label' => 'Single' ),
                array( 'value' => 'dating', 'label' => 'Dating' ),
                array( 'value' => 'married', 'label' => 'Married/Partnered' ),
                array( 'value' => 'divorced', 'label' => 'Divorced/Separated' )
            )
        ),
        array(
            'title' => __( 'How would you describe the severity of your ED?', 'ennulifeassessments' ),
            'description' => __( 'This helps determine the most effective treatment options.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'mild', 'label' => 'Mild' ),
                array( 'value' => 'moderate', 'label' => 'Moderate' ),
                array( 'value' => 'severe', 'label' => 'Severe' ),
                array( 'value' => 'complete', 'label' => 'Complete' )
            )
        ),
        array(
            'title' => __( 'How long have you been experiencing symptoms?', 'ennulifeassessments' ),
            'description' => __( 'Duration affects treatment approach and expectations.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'recent', 'label' => 'Less than 6 months' ),
                array( 'value' => 'moderate', 'label' => '6 months - 2 years' ),
                array( 'value' => 'long', 'label' => '2-5 years' ),
                array( 'value' => 'very_long', 'label' => 'More than 5 years' )
            )
        ),
        array(
            'title' => __( 'Do you have any of these health conditions?', 'ennulifeassessments' ),
            'description' => __( 'Certain conditions affect treatment options and safety.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'none', 'label' => 'None of these' ),
                array( 'value' => 'diabetes', 'label' => 'Diabetes' ),
                array( 'value' => 'heart', 'label' => 'Heart Disease' ),
                array( 'value' => 'hypertension', 'label' => 'High Blood Pressure' )
            )
        ),
        array(
            'title' => __( 'Have you tried any ED treatments before?', 'ennulifeassessments' ),
            'description' => __( 'Previous treatments help guide our recommendations.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'none', 'label' => 'No previous treatments' ),
                array( 'value' => 'oral', 'label' => 'Oral medications' ),
                array( 'value' => 'injections', 'label' => 'Injections' ),
                array( 'value' => 'devices', 'label' => 'Vacuum devices' )
            )
        ),
        array(
            'title' => __( 'Do you smoke or use tobacco?', 'ennulifeassessments' ),
            'description' => __( 'Smoking significantly affects blood flow and treatment effectiveness.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'never', 'label' => 'Never smoked' ),
                array( 'value' => 'former', 'label' => 'Former smoker' ),
                array( 'value' => 'occasional', 'label' => 'Occasional smoker' ),
                array( 'value' => 'regular', 'label' => 'Regular smoker' )
            )
        ),
        array(
            'title' => __( 'How often do you exercise?', 'ennulifeassessments' ),
            'description' => __( 'Physical fitness affects blood flow and overall sexual health.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'never', 'label' => 'Never' ),
                array( 'value' => 'rarely', 'label' => 'Rarely' ),
                array( 'value' => 'regularly', 'label' => 'Regularly' ),
                array( 'value' => 'daily', 'label' => 'Daily' )
            )
        ),
        array(
            'title' => __( 'What\'s your current stress level?', 'ennulifeassessments' ),
            'description' => __( 'Stress is a major factor in erectile dysfunction.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'low', 'label' => 'Low' ),
                array( 'value' => 'moderate', 'label' => 'Moderate' ),
                array( 'value' => 'high', 'label' => 'High' ),
                array( 'value' => 'very_high', 'label' => 'Very High' )
            )
        ),
        array(
            'title' => __( 'What\'s your primary treatment goal?', 'ennulifeassessments' ),
            'description' => __( 'Understanding your goals helps create the right treatment plan.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'restore', 'label' => 'Restore function' ),
                array( 'value' => 'confidence', 'label' => 'Boost confidence' ),
                array( 'value' => 'performance', 'label' => 'Improve performance' ),
                array( 'value' => 'relationship', 'label' => 'Improve relationship' )
            )
        ),
        array(
            'title' => __( 'Are you currently taking any medications?', 'ennulifeassessments' ),
            'description' => __( 'Some medications can affect ED treatment options.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'none', 'label' => 'No medications' ),
                array( 'value' => 'blood_pressure', 'label' => 'Blood pressure meds' ),
                array( 'value' => 'antidepressants', 'label' => 'Antidepressants' ),
                array( 'value' => 'other', 'label' => 'Other medications' )
            )
        ),
        array(
            'title' => __( 'Contact Information', 'ennulifeassessments' ),
            'description' => __( 'Please provide your contact details to receive your personalized assessment results.', 'ennulifeassessments' ),
            'type' => 'contact_info',
            'field_name' => 'contact_info',
            'required' => true,
            'fields' => array(
                array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
            ),
            'button_text' => 'View My Assessment Results'
        )
    ),
    'weight_loss_assessment' => array(
        array(
            'title' => __( 'What\'s your date of birth?', 'ennulifeassessments' ),
            'description' => __( 'Age affects metabolism and weight loss approach.', 'ennulifeassessments' ),
            'type' => 'dob_dropdowns',
            'global_key'  => 'user_dob_combined', // Global Field
            'field_name' => 'date_of_birth',
            'required' => true,
            'show_age' => true
        ),
        array(
            'title' => __( 'What\'s your gender?', 'ennulifeassessments' ),
            'description' => __( 'Weight loss strategies can vary by gender.', 'ennulifeassessments' ),
            'global_key'  => 'gender', // Global Field
            'options' => array(
                array( 'value' => 'male', 'label' => 'Male' ),
                array( 'value' => 'female', 'label' => 'Female' )
            )
        ),
        array(
            'title' => __( 'What\'s your primary weight loss goal?', 'ennulifeassessments' ),
            'description' => __( 'Understanding your goals helps create the right plan.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'lose_10', 'label' => 'Lose 10-20 lbs' ),
                array( 'value' => 'lose_30', 'label' => 'Lose 20-50 lbs' ),
                array( 'value' => 'lose_50', 'label' => 'Lose 50+ lbs' ),
                array( 'value' => 'maintain', 'label' => 'Maintain current weight' )
            )
        ),
        array(
            'title' => __( 'What motivates you most to lose weight?', 'ennulifeassessments' ),
            'description' => __( 'Understanding motivation helps maintain long-term success.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'health', 'label' => 'Improve Health' ),
                array( 'value' => 'appearance', 'label' => 'Improve Appearance' ),
                array( 'value' => 'energy', 'label' => 'Increase Energy' ),
                array( 'value' => 'confidence', 'label' => 'Boost Confidence' )
            )
        ),
        array(
            'title' => __( 'How often do you exercise?', 'ennulifeassessments' ),
            'description' => __( 'Physical activity is key to weight management.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'never', 'label' => 'Never' ),
                array( 'value' => 'rarely', 'label' => '1-2 times/week' ),
                array( 'value' => 'often', 'label' => '3-4 times/week' ),
                array( 'value' => 'daily', 'label' => '5+ times/week' )
            )
        ),
        array(
            'title' => __( 'How would you describe your diet?', 'ennulifeassessments' ),
            'description' => __( 'Your eating habits are crucial for weight loss.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'unhealthy', 'label' => 'Mostly Unhealthy' ),
                array( 'value' => 'balanced', 'label' => 'Generally Balanced' ),
                array( 'value' => 'healthy', 'label' => 'Very Healthy' ),
                array( 'value' => 'strict', 'label' => 'Strict Diet (Keto, Paleo, etc.)' )
            )
        ),
        array(
            'title' => __( 'How much sleep do you get on average?', 'ennulifeassessments' ),
            'description' => __( 'Sleep quality affects hormones that regulate appetite.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'less_5', 'label' => 'Less than 5 hours' ),
                array( 'value' => '5_6', 'label' => '5-6 hours' ),
                array( 'value' => '7_8', 'label' => '7-8 hours' ),
                array( 'value' => 'more_8', 'label' => 'More than 8 hours' )
            )
        ),
        array(
            'title' => __( 'What\'s your current stress level?', 'ennulifeassessments' ),
            'description' => __( 'Stress can lead to weight gain.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'low', 'label' => 'Low' ),
                array( 'value' => 'moderate', 'label' => 'Moderate' ),
                array( 'value' => 'high', 'label' => 'High' ),
                array( 'value' => 'very_high', 'label' => 'Very High' )
            )
        ),
        array(
            'title' => __( 'Have you had success with weight loss before?', 'ennulifeassessments' ),
            'description' => __( 'Past experiences can help shape your future plan.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'no_success', 'label' => 'Never had lasting success' ),
                array( 'value' => 'some_success', 'label' => 'Some success, but gained it back' ),
                array( 'value' => 'good_success', 'label' => 'Good success, maintained for a while' ),
                array( 'value' => 'first_time', 'label' => 'This is my first serious attempt' )
            )
        ),
        array(
            'title' => __( 'Do you have any of these health conditions?', 'ennulifeassessments' ),
            'description' => __( 'Certain conditions can affect weight management.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'none', 'label' => 'None of these' ),
                array( 'value' => 'thyroid', 'label' => 'Thyroid Issues' ),
                array( 'value' => 'diabetes', 'label' => 'Diabetes or Pre-diabetes' ),
                array( 'value' => 'pcos', 'label' => 'PCOS (for women)' )
            )
        ),
        array(
            'title' => __( 'Are you taking any of these medications?', 'ennulifeassessments' ),
            'description' => __( 'Some medications can cause weight gain.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'none', 'label' => 'No' ),
                array( 'value' => 'antidepressants', 'label' => 'Antidepressants' ),
                array( 'value' => 'steroids', 'label' => 'Steroids' ),
                array( 'value' => 'birth_control', 'label' => 'Hormonal Birth Control' )
            )
        ),
        array(
            'title' => __( 'What kind of support do you have?', 'ennulifeassessments' ),
            'description' => __( 'A strong support system can make a big difference.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'none', 'label' => 'I\'m on my own' ),
                array( 'value' => 'partner', 'label' => 'Partner/Spouse' ),
                array( 'value' => 'family', 'label' => 'Family and Friends' ),
                array( 'value' => 'professional', 'label' => 'Professional (Coach, Doctor, etc.)' )
            )
        ),
        array(
            'title' => __( 'Contact Information', 'ennulifeassessments' ),
            'description' => __( 'Provide your details to get your personalized weight loss plan.', 'ennulifeassessments' ),
            'type' => 'contact_info',
            'field_name' => 'contact_info',
            'required' => true,
            'fields' => array(
                array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
            ),
            'button_text' => 'Get My Weight Loss Plan'
        )
    ),
    'health_assessment' => array(
        array(
            'title' => __( 'What\'s your date of birth?', 'ennulifeassessments' ),
            'description' => __( 'Age is a key factor in overall health assessment.', 'ennulifeassessments' ),
            'type' => 'dob_dropdowns',
            'global_key'  => 'user_dob_combined', // Global Field
            'field_name' => 'date_of_birth',
            'required' => true,
            'show_age' => true
        ),
        array(
            'title' => __( 'What\'s your gender?', 'ennulifeassessments' ),
            'description' => __( 'Health priorities can vary by gender.', 'ennulifeassessments' ),
            'global_key'  => 'gender', // Global Field
            'options' => array(
                array( 'value' => 'male', 'label' => 'Male' ),
                array( 'value' => 'female', 'label' => 'Female' ),
            )
        ),
        array(
            'title' => __( 'How would you rate your overall health?', 'ennulifeassessments' ),
            'description' => __( 'Be honest about your current health status.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'poor', 'label' => 'Poor' ),
                array( 'value' => 'fair', 'label' => 'Fair' ),
                array( 'value' => 'good', 'label' => 'Good' ),
                array( 'value' => 'excellent', 'label' => 'Excellent' )
            )
        ),
        array(
            'title' => __( 'How are your energy levels throughout the day?', 'ennulifeassessments' ),
            'description' => __( 'Energy can be an indicator of underlying health issues.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'low', 'label' => 'Consistently Low' ),
                array( 'value' => 'crash', 'label' => 'I crash in the afternoon' ),
                array( 'value' => 'moderate', 'label' => 'Generally Okay' ),
                array( 'value' => 'high', 'label' => 'High and Stable' )
            )
        ),
        array(
            'title' => __( 'How frequently do you exercise?', 'ennulifeassessments' ),
            'description' => __( 'Physical activity is crucial for long-term health.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'rarely', 'label' => 'Rarely or Never' ),
                array( 'value' => 'sometimes', 'label' => '1-2 times a week' ),
                array( 'value' => 'often', 'label' => '3-5 times a week' ),
                array( 'value' => 'daily', 'label' => 'Almost every day' )
            )
        ),
        array(
            'title' => __( 'How would you describe your typical diet?', 'ennulifeassessments' ),
            'description' => __( 'Nutrition is a cornerstone of good health.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'processed', 'label' => 'High in processed foods' ),
                array( 'value' => 'average', 'label' => 'A typical Western diet' ),
                array( 'value' => 'healthy', 'label' => 'Mostly whole foods' ),
                array( 'value' => 'very_healthy', 'label' => 'Very clean, whole foods diet' )
            )
        ),
        array(
            'title' => __( 'How is your sleep quality?', 'ennulifeassessments' ),
            'description' => __( 'Sleep is vital for recovery and overall health.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'poor', 'label' => 'Poor, I wake up tired' ),
                array( 'value' => 'fair', 'label' => 'Fair, could be better' ),
                array( 'value' => 'good', 'label' => 'Good, usually restful' ),
                array( 'value' => 'excellent', 'label' => 'Excellent, I wake up refreshed' )
            )
        ),
        array(
            'title' => __( 'How do you manage stress?', 'ennulifeassessments' ),
            'description' => __( 'Chronic stress can negatively impact health.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'poorly', 'label' => 'I don\'t manage it well' ),
                array( 'value' => 'somewhat', 'label' => 'I have some coping methods' ),
                array( 'value' => 'well', 'label' => 'I manage it well' ),
                array( 'value' => 'proactively', 'label' => 'I have a proactive stress management routine' )
            )
        ),
        array(
            'title' => __( 'Do you have regular check-ups with a doctor?', 'ennulifeassessments' ),
            'description' => __( 'Preventive care is important for long-term health.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'never', 'label' => 'Never or rarely' ),
                array( 'value' => 'sometimes', 'label' => 'Only when I have a problem' ),
                array( 'value' => 'regularly', 'label' => 'I have regular annual check-ups' )
            )
        ),
        array(
            'title' => __( 'What are your main health goals?', 'ennulifeassessments' ),
            'description' => __( 'This helps us understand what you want to achieve.', 'ennulifeassessments' ),
            'type'        => 'multiselect',
            'options' => array(
                array( 'value' => 'longevity', 'label' => 'Improve Longevity' ),
                array( 'value' => 'energy', 'label' => 'Increase Energy' ),
                array( 'value' => 'weight', 'label' => 'Manage Weight' ),
                array( 'value' => 'mental_clarity', 'label' => 'Improve Mental Clarity' ),
                array( 'value' => 'athletic_performance', 'label' => 'Enhance Athletic Performance' )
            )
        ),
        array(
            'title' => __( 'Contact Information', 'ennulifeassessments' ),
            'description' => __( 'Please provide your details to receive your personalized health report.', 'ennulifeassessments' ),
            'type' => 'contact_info',
            'field_name' => 'contact_info',
            'required' => true,
            'fields' => array(
                array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
            ),
            'button_text' => 'Get My Health Report'
        )
    ),
    'skin_assessment' => array(
        array(
            'title' => __( 'What\'s your date of birth?', 'ennulifeassessments' ),
            'description' => __( 'Age helps determine the most appropriate skin care.', 'ennulifeassessments' ),
            'type' => 'dob_dropdowns',
            'global_key'  => 'user_dob_combined', // Global Field
            'field_name' => 'date_of_birth',
            'required' => true,
            'show_age' => true
        ),
        array(
            'title' => __( 'What is your primary skin concern?', 'ennulifeassessments' ),
            'description' => __( 'Select the issue that bothers you the most.', 'ennulifeassessments' ),
            'global_key'  => 'gender', // Global Field
            'options' => array(
                array( 'value' => 'acne', 'label' => 'Acne & Blemishes' ),
                array( 'value' => 'wrinkles', 'label' => 'Fine Lines & Wrinkles' ),
                array( 'value' => 'dark_spots', 'label' => 'Dark Spots & Hyperpigmentation' ),
                array( 'value' => 'redness', 'label' => 'Redness & Rosacea' ),
                array( 'value' => 'dryness', 'label' => 'Dryness & Dehydration' )
            )
        ),
        array(
            'title' => __( 'How would you describe your skin type?', 'ennulifeassessments' ),
            'description' => __( 'This helps determine the best product formulations for you.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'oily', 'label' => 'Oily' ),
                array( 'value' => 'dry', 'label' => 'Dry' ),
                array( 'value' => 'combination', 'label' => 'Combination' ),
                array( 'value' => 'normal', 'label' => 'Normal' ),
                array( 'value' => 'sensitive', 'label' => 'Sensitive' )
            )
        ),
        array(
            'title' => __( 'How often are you exposed to the sun?', 'ennulifeassessments' ),
            'description' => __( 'Sun exposure is a major factor in skin aging.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'rarely', 'label' => 'Rarely, I\'m mostly indoors' ),
                array( 'value' => 'sometimes', 'label' => 'Sometimes, on weekends' ),
                array( 'value' => 'daily', 'label' => 'Daily, but I use sunscreen' ),
                array( 'value' => 'daily_no_spf', 'label' => 'Daily, without sunscreen' )
            )
        ),
        array(
            'title' => __( 'What does your current skincare routine look like?', 'ennulifeassessments' ),
            'description' => __( 'Understanding what you\'re currently doing is key.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'minimal', 'label' => 'Minimal (cleanse, maybe moisturize)' ),
                array( 'value' => 'basic', 'label' => 'Basic (cleanse, moisturize, SPF)' ),
                array( 'value' => 'advanced', 'label' => 'Advanced (serums, exfoliants, etc.)' ),
                array( 'value' => 'none', 'label' => 'I don\'t have a routine' )
            )
        ),
        array(
            'title' => __( 'Have you ever had a reaction to skincare products?', 'ennulifeassessments' ),
            'description' => __( 'This helps us identify potential sensitivities.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'yes', 'label' => 'Yes' ),
                array( 'value' => 'no', 'label' => 'No' ),
                array( 'value' => 'unsure', 'label' => 'I\'m not sure' )
            )
        ),
        array(
            'title' => __( 'Do any of these lifestyle factors apply to you?', 'ennulifeassessments' ),
            'description' => __( 'Select all that apply.', 'ennulifeassessments' ),
            'type'        => 'multiselect',
            'options' => array(
                array( 'value' => 'smoker', 'label' => 'I smoke' ),
                array( 'value' => 'high_stress', 'label' => 'I have high stress levels' ),
                array( 'value' => 'poor_sleep', 'label' => 'I have poor sleep quality' ),
                array( 'value' => 'high_sugar_diet', 'label' => 'My diet is high in sugar/processed foods' )
            )
        ),
        array(
            'title' => __( 'Have you used prescription skincare in the past?', 'ennulifeassessments' ),
            'description' => __( 'E.g., Retin-A, Tretinoin, Accutane.', 'ennulifeassessments' ),
            'options' => array(
                array( 'value' => 'yes_oral', 'label' => 'Yes, oral medications' ),
                array( 'value' => 'yes_topical', 'label' => 'Yes, topical creams' ),
                array( 'value' => 'no', 'label' => 'No' )
            )
        ),
        array(
            'title' => __( 'Contact Information', 'ennulifeassessments' ),
            'description' => __( 'Please provide your details to receive your personalized skin care plan.', 'ennulifeassessments' ),
            'type' => 'contact_info',
            'field_name' => 'contact_info',
            'required' => true,
            'fields' => array(
                array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
            ),
            'button_text' => 'Get My Skin Plan'
        )
    ),
); 