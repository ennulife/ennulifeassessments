<?php
/**
 * Assessment Definition: Weight Loss Assessment
 *
 * @package ENNU_Life
 * @version 62.11.0
 */

return array(
	'title'             => 'Weight Loss Assessment',
	'assessment_engine' => 'quantitative',
	'questions'         => array(
		'wl_q_gender' => array(
			'title'      => 'What is your gender?',
			'type'       => 'radio',
			'options'    => array(
				'male'   => 'Male',
				'female' => 'Female',
				'other'  => 'Other / Prefer not to say',
			),
			'required'   => true,
			'global_key' => 'gender',
		),
		'wl_q_dob' => array(
			'title'      => 'What is your date of birth?',
			'type'       => 'dob_dropdowns',
			'required'   => true,
			'global_key' => 'date_of_birth',
		),
		'wl_q1'       => array(
			'title'      => 'What is your current height and weight?',
			'type'       => 'height_weight',
			'required'   => true,
			'global_key' => 'height_weight',
		),
		'wl_q2'       => array(
			'title'    => 'What is your primary weight loss goal?',
			'type'     => 'radio',
			'options'  => array(
				'lose_10_20'   => 'Lose 10-20 lbs',
				'lose_20_50'   => 'Lose 20-50 lbs',
				'lose_50_plus' => 'Lose 50+ lbs',
				'maintain'     => 'Maintain current weight',
			),
			'scoring'  => array(
				'category' => 'Motivation & Goals',
				'weight'   => 2.5,
				'answers'  => array(
					'lose_10_20'   => 5,
					'lose_20_50'   => 4,
					'lose_50_plus' => 3,
					'maintain'     => 5,
				),
			),
			'required' => true,
		),
		'wl_q3'       => array(
			'title'    => 'How would you describe your typical diet?',
			'type'     => 'radio',
			'options'  => array(
				'balanced'             => 'Balanced and nutrient-rich',
				'processed'            => 'High in processed foods',
				'low_carb'             => 'Low-carb or ketogenic',
				'vegetarian'           => 'Vegetarian or vegan',
				'intermittent_fasting' => 'Intermittent fasting',
			),
			'scoring'  => array(
				'category' => 'Nutrition',
				'weight'   => 2.5,
				'answers'  => array(
					'balanced'             => 5,
					'processed'            => 1,
					'low_carb'             => 4,
					'vegetarian'           => 4,
					'intermittent_fasting' => 5,
				),
			),
			'required' => true,
		),
		'wl_q4'       => array(
			'title'    => 'How often do you exercise per week?',
			'type'     => 'radio',
			'options'  => array(
				'5_plus' => '5 or more times',
				'3_4'    => '3-4 times',
				'1_2'    => '1-2 times',
				'none'   => 'I rarely or never exercise',
			),
			'scoring'  => array(
				'category' => 'Physical Activity',
				'weight'   => 2,
				'answers'  => array(
					'5_plus' => 5,
					'3_4'    => 4,
					'1_2'    => 2,
					'none'   => 1,
				),
			),
			'required' => true,
		),
		'wl_q5'       => array(
			'title'    => 'How many hours of sleep do you typically get per night?',
			'type'     => 'radio',
			'options'  => array(
				'less_than_5' => 'Less than 5 hours',
				'5_6'         => '5-6 hours',
				'7_8'         => '7-8 hours',
				'more_than_8' => 'More than 8 hours',
			),
			'scoring'  => array(
				'category' => 'Lifestyle Factors',
				'weight'   => 1.5,
				'answers'  => array(
					'less_than_5' => 1,
					'5_6'         => 2,
					'7_8'         => 5,
					'more_than_8' => 4,
				),
			),
			'required' => true,
		),
		'wl_q6'       => array(
			'title'    => 'How would you rate your daily stress levels?',
			'type'     => 'radio',
			'options'  => array(
				'low'       => 'Low',
				'moderate'  => 'Moderate',
				'high'      => 'High',
				'very_high' => 'Very High',
			),
			'scoring'  => array(
				'category' => 'Psychological Factors',
				'weight'   => 2,
				'answers'  => array(
					'low'       => 5,
					'moderate'  => 4,
					'high'      => 2,
					'very_high' => 1,
				),
			),
			'required' => true,
		),
		'wl_q7'       => array(
			'title'    => 'What has been your experience with weight loss in the past?',
			'type'     => 'radio',
			'options'  => array(
				'never_success' => 'Never had lasting success',
				'some_success'  => 'Some success, but gained it back',
				'good_success'  => 'Good success, maintained for a while',
				'first_attempt' => 'This is my first serious attempt',
			),
			'scoring'  => array(
				'category' => 'Weight Loss History',
				'weight'   => 1.5,
				'answers'  => array(
					'never_success' => 2,
					'some_success'  => 3,
					'good_success'  => 4,
					'first_attempt' => 5,
				),
			),
			'required' => true,
		),
		'wl_q8'       => array(
			'title'    => 'Do you find yourself eating due to stress, boredom, or other emotional cues?',
			'type'     => 'radio',
			'options'  => array(
				'often'     => 'Often',
				'sometimes' => 'Sometimes',
				'rarely'    => 'Rarely',
				'never'     => 'Never',
			),
			'scoring'  => array(
				'category' => 'Behavioral Patterns',
				'weight'   => 2,
				'answers'  => array(
					'often'     => 1,
					'sometimes' => 2,
					'rarely'    => 4,
					'never'     => 5,
				),
			),
			'required' => true,
		),
		'wl_q9'       => array(
			'title'    => 'Have you been diagnosed with any conditions that can affect weight?',
			'type'     => 'multiselect',
			'options'  => array(
				'thyroid'            => 'Thyroid Issues (e.g., Hypothyroidism)',
				'pcos'               => 'Polycystic Ovary Syndrome (PCOS)',
				'insulin_resistance' => 'Insulin Resistance or Pre-diabetes',
				'none'               => 'None of the above',
			),
			'scoring'  => array(
				'category' => 'Medical Factors',
				'weight'   => 2.5,
				'answers'  => array(
					'thyroid'            => 2,
					'pcos'               => 2,
					'insulin_resistance' => 2,
					'none'               => 5,
				),
			),
			'required' => true,
		),
		'wl_q10'      => array(
			'title'    => 'How motivated are you to make lifestyle changes to achieve your weight goals?',
			'type'     => 'radio',
			'options'  => array(
				'very'     => 'Very motivated',
				'somewhat' => 'Somewhat motivated',
				'not_very' => 'Not very motivated',
				'unsure'   => 'I\'m not sure',
			),
			'scoring'  => array(
				'category' => 'Motivation & Goals',
				'weight'   => 2,
				'answers'  => array(
					'very'     => 5,
					'somewhat' => 3,
					'not_very' => 1,
					'unsure'   => 2,
				),
			),
			'required' => true,
		),
		'wl_q11'      => array(
			'title'    => 'What is your primary body composition goal?',
			'type'     => 'radio',
			'options'  => array(
				'lose_fat'     => 'Lose Fat',
				'build_muscle' => 'Build Muscle',
				'both'         => 'Both',
			),
			'scoring'  => array(
				'category' => 'Aesthetics',
				'weight'   => 1,
				'answers'  => array(
					'lose_fat'     => 4,
					'build_muscle' => 4,
					'both'         => 5,
				),
			),
			'required' => true,
		),
		'wl_q12'      => array(
			'title'    => 'Do you have a strong support system (family, friends) for your health journey?',
			'type'     => 'radio',
			'options'  => array(
				'yes'      => 'Yes, very supportive',
				'somewhat' => 'Somewhat supportive',
				'no'       => 'No, I am mostly on my own',
			),
			'scoring'  => array(
				'category' => 'Social Support',
				'weight'   => 1,
				'answers'  => array(
					'yes'      => 5,
					'somewhat' => 3,
					'no'       => 1,
				),
			),
			'required' => true,
		),
		'wl_q13'      => array(
			'title'    => 'How confident are you in your ability to achieve your weight loss goals?',
			'type'     => 'radio',
			'options'  => array(
				'very'     => 'Very confident',
				'somewhat' => 'Somewhat confident',
				'not_very' => 'Not very confident',
			),
			'scoring'  => array(
				'category' => 'Psychological Factors',
				'weight'   => 1.5,
				'answers'  => array(
					'very'     => 5,
					'somewhat' => 3,
					'not_very' => 1,
				),
			),
			'required' => true,
		),
	),
);
