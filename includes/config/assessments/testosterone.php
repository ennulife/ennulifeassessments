<?php
/**
 * Assessment Definition: Testosterone Assessment
 * Enhanced by Dr. Elena Harmonix (Endocrinology Master)
 * Evidence-based endocrinology implementation
 *
 * @package ENNU_Life
 * @version 62.11.0
 */

return array(
	'title'             => 'Testosterone Assessment',
	'assessment_engine' => 'quantitative',
	'questions'         => array(
		'testosterone_q_dob' => array(
			'title'      => 'What is your date of birth?',
			'type'       => 'dob_dropdowns',
			'required'   => true,
			'global_key' => 'date_of_birth',
			'scoring'    => array(
				'category' => 'Age Factors',
				'weight'   => 1.5,
				'calculation' => 'age_from_dob',
				'age_scores' => array(
					'18-25' => 8,
					'26-35' => 7,
					'36-45' => 6,
					'46-55' => 4,
					'56-65' => 3,
					'66-75' => 2,
					'76+'   => 1,
				),
			),
		),
		'testosterone_q1'    => array(
			'title'    => 'Which of the following symptoms, often associated with low testosterone, are you experiencing?',
			'type'     => 'multiselect',
			'options'  => array(
				'low_libido'           => 'Low libido or decreased sexual desire',
				'fatigue'              => 'Fatigue and decreased energy levels',
				'muscle_loss'          => 'Loss of muscle mass or strength',
				'increased_fat'        => 'Increased body fat, especially around the midsection',
				'mood_changes'         => 'Mood changes, irritability, or depression',
				'erectile_dysfunction' => 'Erectile dysfunction',
				'decreased_motivation' => 'Decreased motivation and drive',
				'poor_concentration'   => 'Poor concentration and memory',
				'sleep_issues'         => 'Sleep problems or insomnia',
				'hot_flashes'          => 'Hot flashes or night sweats',
				'none'                 => 'None of the above',
			),
			'scoring'  => array(
				'category' => 'Symptom Severity',
				'weight'   => 3,
				'answers'  => array(
					'low_libido'           => 2,
					'fatigue'              => 2,
					'muscle_loss'          => 1,
					'increased_fat'        => 1,
					'mood_changes'         => 2,
					'erectile_dysfunction' => 1,
					'decreased_motivation' => 2,
					'poor_concentration'   => 2,
					'sleep_issues'         => 2,
					'hot_flashes'          => 1,
					'none'                 => 8,
				),
			),
			'required' => true,
		),
		'testosterone_q2'    => array(
			'title'    => 'How many days per week do you engage in resistance training (weight lifting)?',
			'type'     => 'radio',
			'options'  => array(
				'5_plus' => '5 or more days',
				'3_4'    => '3-4 days',
				'1_2'    => '1-2 days',
				'none'   => 'I do not do resistance training',
			),
			'scoring'  => array(
				'category' => 'Exercise & Lifestyle',
				'weight'   => 2.5,
				'answers'  => array(
					'5_plus' => 8,
					'3_4'    => 7,
					'1_2'    => 5,
					'none'   => 2,
				),
			),
			'required' => true,
		),
		'testosterone_q3'    => array(
			'title'    => 'How would you rate your current stress levels?',
			'type'     => 'radio',
			'options'  => array(
				'very_low'  => 'Very Low - I rarely feel stressed',
				'low'       => 'Low - I experience minimal stress',
				'moderate'  => 'Moderate - I experience some stress but manage it well',
				'high'      => 'High - I frequently feel stressed and overwhelmed',
				'very_high' => 'Very High - I feel constantly stressed and struggle to cope',
			),
			'scoring'  => array(
				'category' => 'Stress & Cortisol',
				'weight'   => 2.5,
				'answers'  => array(
					'very_low'  => 8,
					'low'       => 7,
					'moderate'  => 5,
					'high'      => 3,
					'very_high' => 2,
				),
			),
			'required' => true,
		),
		'testosterone_q4'    => array(
			'title'    => 'How would you describe your sleep quality?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - Deep, restorative sleep every night',
				'good'      => 'Good - Generally restful sleep most nights',
				'moderate'  => 'Moderate - Some sleep issues but mostly restful',
				'poor'      => 'Poor - Frequent sleep problems affecting daily life',
				'very_poor' => 'Very Poor - Severe sleep issues',
			),
			'scoring'  => array(
				'category' => 'Sleep Quality',
				'weight'   => 2,
				'answers'  => array(
					'excellent' => 8,
					'good'      => 7,
					'moderate'  => 5,
					'poor'      => 3,
					'very_poor' => 2,
				),
			),
			'required' => true,
		),
		'testosterone_q5'    => array(
			'title'    => 'How would you describe your body composition and muscle mass?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - High muscle mass, low body fat',
				'good'      => 'Good - Good muscle mass, healthy body fat',
				'moderate'  => 'Moderate - Some muscle mass, moderate body fat',
				'poor'      => 'Poor - Low muscle mass, high body fat',
				'very_poor' => 'Very Poor - Very low muscle mass, high body fat',
			),
			'scoring'  => array(
				'category' => 'Body Composition',
				'weight'   => 2,
				'answers'  => array(
					'excellent' => 8,
					'good'      => 7,
					'moderate'  => 5,
					'poor'      => 3,
					'very_poor' => 2,
				),
			),
			'required' => true,
		),
		'testosterone_q6'    => array(
			'title'    => 'Do you have a family history of hormonal disorders?',
			'type'     => 'multiselect',
			'options'  => array(
				'none'             => 'None known',
				'diabetes'         => 'Diabetes (Type 1 or Type 2)',
				'thyroid'          => 'Thyroid disorders',
				'low_testosterone' => 'Low testosterone',
				'infertility'      => 'Infertility issues',
				'other'            => 'Other hormonal disorders',
			),
			'scoring'  => array(
				'category' => 'Family History',
				'weight'   => 1.5,
				'answers'  => array(
					'none'             => 8,
					'diabetes'         => 4,
					'thyroid'          => 5,
					'low_testosterone' => 3,
					'infertility'      => 5,
					'other'            => 4,
				),
			),
			'required' => true,
		),
		'testosterone_q7'    => array(
			'title'    => 'Have you had any recent blood tests for testosterone levels?',
			'type'     => 'radio',
			'options'  => array(
				'none'          => 'No recent tests',
				'basic'         => 'Basic testosterone test',
				'comprehensive' => 'Comprehensive hormone panel',
				'regular'       => 'Regular monitoring',
			),
			'scoring'  => array(
				'category' => 'Biomarker Monitoring',
				'weight'   => 1.5,
				'answers'  => array(
					'none'          => 5,
					'basic'         => 6,
					'comprehensive' => 7,
					'regular'       => 8,
				),
			),
			'required' => true,
		),
		'testosterone_q8'    => array(
			'title'    => 'How would you rate your overall testosterone health?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - No testosterone-related issues',
				'good'      => 'Good - Minor testosterone fluctuations',
				'moderate'  => 'Moderate - Some testosterone symptoms',
				'poor'      => 'Poor - Significant testosterone issues',
				'very_poor' => 'Very Poor - Severe testosterone problems',
			),
			'scoring'  => array(
				'category' => 'Self-Assessment',
				'weight'   => 1.5,
				'answers'  => array(
					'excellent' => 8,
					'good'      => 7,
					'moderate'  => 5,
					'poor'      => 3,
					'very_poor' => 2,
				),
			),
			'required' => true,
		),
	),
);
