<?php
/**
 * Assessment Definition: Peptide Therapy Assessment
 * Personalized peptide recommendations based on health goals
 *
 * @package ENNU_Life
 * @version 1.0.0
 */

return array(
	'title'             => 'Peptide Therapy Assessment',
	'assessment_engine' => 'quantitative',
	'questions'         => array(
		'pep_q1' => array(
			'title'      => 'What are your primary health optimization goals?',
			'type'       => 'checkbox',
			'options'    => array(
				'weight_loss' => 'Lose weight and reduce body fat',
				'muscle_gain' => 'Build lean muscle mass',
				'energy_boost' => 'Increase energy levels',
				'cognitive_enhancement' => 'Improve mental clarity and focus',
				'anti_aging' => 'Anti-aging and longevity',
				'hormone_optimization' => 'Optimize hormone levels',
				'sexual_health' => 'Enhance sexual health and vitality',
				'recovery' => 'Improve recovery from exercise/injury',
				'sleep_quality' => 'Better sleep quality',
				'immune_support' => 'Strengthen immune system',
			),
			'scoring' => array(
				'category' => 'Weight Management & Metabolism',
				'weight'   => 3.0,
			),
		),
		'pep_q2' => array(
			'title'      => 'How would you rate your current energy levels?',
			'type'       => 'radio',
			'options'    => array(
				'very_low' => 'Very Low (1-2)',
				'low' => 'Low (3-4)',
				'moderate' => 'Moderate (5-6)',
				'good' => 'Good (7-8)',
				'excellent' => 'Excellent (9-10)',
			),
			'scoring' => array(
				'category' => 'Recovery & Performance',
				'weight'   => 2.5,
				'answers'  => array(
					'very_low' => 2,
					'low' => 4,
					'moderate' => 6,
					'good' => 8,
					'excellent' => 10,
				),
			),
		),
		'pep_q3' => array(
			'title'      => 'What best describes your current weight management situation?',
			'type'       => 'radio',
			'options'    => array(
				'need_significant_loss' => 'Need to lose 30+ lbs',
				'need_moderate_loss' => 'Need to lose 10-30 lbs',
				'need_minor_loss' => 'Need to lose less than 10 lbs',
				'maintain_current' => 'Happy with current weight',
				'need_gain' => 'Need to gain weight/muscle',
			),
			'scoring' => array(
				'category' => 'Weight Management & Metabolism',
				'weight'   => 2.5,
				'answers'  => array(
					'need_significant_loss' => 3,
					'need_moderate_loss' => 5,
					'need_minor_loss' => 7,
					'maintain_current' => 9,
					'need_gain' => 6,
				),
			),
		),
		'pep_q4' => array(
			'title'      => 'How would you describe your metabolism?',
			'type'       => 'radio',
			'options'    => array(
				'very_slow' => 'Very slow - gain weight easily',
				'slow' => 'Slower than average',
				'normal' => 'Normal/average',
				'fast' => 'Fast - difficulty gaining weight',
				'very_fast' => 'Very fast metabolism',
			),
			'scoring' => array(
				'category' => 'Weight Management & Metabolism',
				'weight'   => 2.0,
				'answers'  => array(
					'very_slow' => 2,
					'slow' => 4,
					'normal' => 6,
					'fast' => 8,
					'very_fast' => 10,
				),
			),
		),
		'pep_q5' => array(
			'title'      => 'Do you experience stubborn fat areas that resist diet and exercise?',
			'type'       => 'radio',
			'options'    => array(
				'yes_multiple' => 'Yes, multiple areas',
				'yes_one' => 'Yes, one specific area',
				'somewhat' => 'Somewhat',
				'no' => 'No',
			),
			'scoring' => array(
				'category' => 'Weight Management & Metabolism',
				'weight'   => 2.0,
				'answers'  => array(
					'yes_multiple' => 2,
					'yes_one' => 4,
					'somewhat' => 6,
					'no' => 10,
				),
			),
		),
		'pep_q6' => array(
			'title'      => 'How often do you exercise or engage in physical activity?',
			'type'       => 'radio',
			'options'    => array(
				'daily' => 'Daily',
				'5_6_weekly' => '5-6 times per week',
				'3_4_weekly' => '3-4 times per week',
				'1_2_weekly' => '1-2 times per week',
				'rarely' => 'Rarely or never',
			),
			'scoring' => array(
				'category' => 'Recovery & Performance',
				'weight'   => 2.0,
				'answers'  => array(
					'daily' => 10,
					'5_6_weekly' => 8,
					'3_4_weekly' => 6,
					'1_2_weekly' => 4,
					'rarely' => 2,
				),
			),
		),
		'pep_q7' => array(
			'title'      => 'How would you rate your recovery after physical activity?',
			'type'       => 'radio',
			'options'    => array(
				'very_poor' => 'Very poor - takes many days',
				'poor' => 'Poor - longer than expected',
				'average' => 'Average',
				'good' => 'Good - recover quickly',
				'excellent' => 'Excellent - minimal recovery needed',
			),
			'scoring' => array(
				'category' => 'Recovery & Performance',
				'weight'   => 2.5,
				'answers'  => array(
					'very_poor' => 2,
					'poor' => 4,
					'average' => 6,
					'good' => 8,
					'excellent' => 10,
				),
			),
		),
		'pep_q8' => array(
			'title'      => 'Do you experience muscle or joint pain?',
			'type'       => 'radio',
			'options'    => array(
				'chronic_severe' => 'Chronic and severe',
				'chronic_mild' => 'Chronic but mild',
				'occasional' => 'Occasional',
				'rare' => 'Rarely',
				'never' => 'Never',
			),
			'scoring' => array(
				'category' => 'Recovery & Performance',
				'weight'   => 2.0,
				'answers'  => array(
					'chronic_severe' => 2,
					'chronic_mild' => 4,
					'occasional' => 6,
					'rare' => 8,
					'never' => 10,
				),
			),
		),
		'pep_q9' => array(
			'title'      => 'Have you experienced symptoms of hormonal imbalance?',
			'type'       => 'checkbox',
			'options'    => array(
				'fatigue' => 'Persistent fatigue',
				'mood_swings' => 'Mood swings',
				'low_libido' => 'Low libido',
				'weight_gain' => 'Unexplained weight gain',
				'hair_loss' => 'Hair loss',
				'sleep_issues' => 'Sleep disturbances',
				'hot_flashes' => 'Hot flashes',
				'muscle_loss' => 'Muscle loss',
				'brain_fog' => 'Brain fog',
				'none' => 'None of the above',
			),
			'scoring' => array(
				'category' => 'Hormonal Optimization',
				'weight'   => 2.5,
			),
		),
		'pep_q10' => array(
			'title'      => 'For men: Have you experienced symptoms of low testosterone?',
			'type'       => 'radio',
			'options'    => array(
				'yes_multiple' => 'Yes, multiple symptoms',
				'yes_some' => 'Yes, some symptoms',
				'possibly' => 'Possibly',
				'no' => 'No',
				'not_applicable' => 'Not applicable',
			),
			'scoring' => array(
				'category' => 'Hormonal Optimization',
				'weight'   => 2.5,
				'answers'  => array(
					'yes_multiple' => 2,
					'yes_some' => 4,
					'possibly' => 6,
					'no' => 10,
					'not_applicable' => 0,
				),
			),
		),
		'pep_q11' => array(
			'title'      => 'For women: What is your current life stage?',
			'type'       => 'radio',
			'options'    => array(
				'reproductive' => 'Reproductive years',
				'perimenopause' => 'Perimenopause',
				'menopause' => 'Menopause',
				'post_menopause' => 'Post-menopause',
				'not_applicable' => 'Prefer not to say',
			),
			'scoring' => array(
				'category' => 'Hormonal Optimization',
				'weight'   => 2.5,
				'answers'  => array(
					'reproductive' => 8,
					'perimenopause' => 6,
					'menopause' => 4,
					'post_menopause' => 4,
					'not_applicable' => 0,
				),
			),
		),
		'pep_q12' => array(
			'title'      => 'How would you rate your mental clarity and focus?',
			'type'       => 'radio',
			'options'    => array(
				'very_poor' => 'Very poor',
				'poor' => 'Poor',
				'average' => 'Average',
				'good' => 'Good',
				'excellent' => 'Excellent',
			),
			'scoring' => array(
				'category' => 'Cognitive Enhancement',
				'weight'   => 2.5,
				'answers'  => array(
					'very_poor' => 2,
					'poor' => 4,
					'average' => 6,
					'good' => 8,
					'excellent' => 10,
				),
			),
		),
		'pep_q13' => array(
			'title'      => 'Do you experience brain fog or difficulty concentrating?',
			'type'       => 'radio',
			'options'    => array(
				'daily' => 'Daily',
				'frequently' => 'Frequently',
				'sometimes' => 'Sometimes',
				'rarely' => 'Rarely',
				'never' => 'Never',
			),
			'scoring' => array(
				'category' => 'Cognitive Enhancement',
				'weight'   => 2.0,
				'answers'  => array(
					'daily' => 2,
					'frequently' => 4,
					'sometimes' => 6,
					'rarely' => 8,
					'never' => 10,
				),
			),
		),
		'pep_q14' => array(
			'title'      => 'How is your memory and recall?',
			'type'       => 'radio',
			'options'    => array(
				'declining' => 'Noticeably declining',
				'somewhat_declining' => 'Somewhat declining',
				'stable' => 'Stable',
				'good' => 'Good',
				'excellent' => 'Excellent',
			),
			'scoring' => array(
				'category' => 'Cognitive Enhancement',
				'weight'   => 2.0,
				'answers'  => array(
					'declining' => 2,
					'somewhat_declining' => 4,
					'stable' => 6,
					'good' => 8,
					'excellent' => 10,
				),
			),
		),
		'pep_q15' => array(
			'title'      => 'How important is anti-aging to your health goals?',
			'type'       => 'radio',
			'options'    => array(
				'extremely' => 'Extremely important',
				'very' => 'Very important',
				'moderately' => 'Moderately important',
				'slightly' => 'Slightly important',
				'not' => 'Not important',
			),
			'scoring' => array(
				'category' => 'Anti-Aging & Longevity',
				'weight'   => 2.0,
				'answers'  => array(
					'extremely' => 10,
					'very' => 8,
					'moderately' => 6,
					'slightly' => 4,
					'not' => 2,
				),
			),
		),
	),
);