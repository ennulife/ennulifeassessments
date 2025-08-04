<?php
/**
 * Assessment Definition: ED Treatment Assessment
 * Enhanced by Dr. Victor Pulse (Cardiology Master)
 * Evidence-based cardiology and urology implementation
 *
 * @package ENNU_Life
 * @version 62.11.0
 */

return array(
	'title'             => 'ED Treatment Assessment',
	'assessment_engine' => 'quantitative',
	'gender_filter'     => 'male',
	'questions'         => array(
		'ed_q_dob' => array(
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
		'ed_q_gender' => array(
			'title'      => 'What is your gender?',
			'type'       => 'radio',
			'options'    => array(
				'female' => 'FEMALE',
				'male'   => 'MALE',
			),
			'required'   => true,
			'global_key' => 'gender',
		),
		'ed_q_height_weight' => array(
			'title'      => 'What is your height and weight?',
			'type'       => 'height_weight',
			'required'   => true,
			'global_key' => 'height_weight',
		),
		'ed_q1'    => array(
			'title'    => 'How would you describe your ability to achieve and maintain an erection sufficient for satisfactory sexual performance?',
			'type'     => 'radio',
			'options'  => array(
				'always'    => 'Almost always or always',
				'usually'   => 'Usually (more than half the time)',
				'sometimes' => 'Sometimes (about half the time)',
				'rarely'    => 'Rarely or never',
			),
			'scoring'  => array(
				'category' => 'Condition Severity',
				'weight'   => 3,
				'answers'  => array(
					'always'    => 9,
					'usually'   => 7,
					'sometimes' => 4,
					'rarely'    => 1,
				),
			),
			'required' => true,
		),
		'ed_q2'    => array(
			'title'    => 'How would you rate your sexual desire and libido?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - High sexual desire',
				'good'      => 'Good - Normal sexual desire',
				'moderate'  => 'Moderate - Somewhat reduced desire',
				'low'       => 'Low - Significantly reduced desire',
				'very_low'  => 'Very Low - Minimal sexual desire',
			),
			'scoring'  => array(
				'category' => 'Sexual Desire',
				'weight'   => 2.5,
				'answers'  => array(
					'excellent' => 8,
					'good'      => 7,
					'moderate'  => 5,
					'low'       => 3,
					'very_low'  => 2,
				),
			),
			'required' => true,
		),
		'ed_q3'    => array(
			'title'    => 'Have you been diagnosed with any of the following medical conditions?',
			'type'     => 'multiselect',
			'options'  => array(
				'diabetes'            => 'Diabetes (Type 1 or Type 2)',
				'heart_disease'       => 'Heart Disease',
				'high_blood_pressure' => 'High Blood Pressure',
				'high_cholesterol'    => 'High Cholesterol',
				'obesity'             => 'Obesity',
				'prostate_issues'     => 'Prostate Issues',
				'thyroid_disorders'   => 'Thyroid Disorders',
				'none'                => 'None of the above',
			),
			'scoring'  => array(
				'category' => 'Medical Factors',
				'weight'   => 2.5,
				'answers'  => array(
					'diabetes'            => 2,
					'heart_disease'       => 1,
					'high_blood_pressure' => 2,
					'high_cholesterol'    => 3,
					'obesity'             => 2,
					'prostate_issues'     => 1,
					'thyroid_disorders'   => 3,
					'none'                => 7,
				),
			),
			'required' => true,
		),
		'ed_q4'    => array(
			'title'    => 'How would you describe your current stress levels?',
			'type'     => 'radio',
			'options'  => array(
				'very_low'  => 'Very Low - I rarely feel stressed',
				'low'       => 'Low - I experience minimal stress',
				'moderate'  => 'Moderate - I experience some stress but manage it well',
				'high'      => 'High - I frequently feel stressed and overwhelmed',
				'very_high' => 'Very High - I feel constantly stressed and struggle to cope',
			),
			'scoring'  => array(
				'category' => 'Psychological Factors',
				'weight'   => 2,
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
		'ed_q5'    => array(
			'title'    => 'How would you describe your relationship satisfaction?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - Very satisfied with relationship',
				'good'      => 'Good - Generally satisfied',
				'moderate'  => 'Moderate - Some relationship issues',
				'poor'      => 'Poor - Significant relationship problems',
				'very_poor' => 'Very Poor - Major relationship issues',
			),
			'scoring'  => array(
				'category' => 'Relationship Factors',
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
		'ed_q6'    => array(
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
		'ed_q7'    => array(
			'title'    => 'How would you describe your overall cardiovascular health?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - No cardiovascular issues',
				'good'      => 'Good - Minor cardiovascular concerns',
				'moderate'  => 'Moderate - Some cardiovascular issues',
				'poor'      => 'Poor - Significant cardiovascular problems',
				'very_poor' => 'Very Poor - Severe cardiovascular issues',
			),
			'scoring'  => array(
				'category' => 'Cardiovascular Health',
				'weight'   => 2.5,
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
		'ed_q8'    => array(
			'title'    => 'How would you rate your overall ED treatment readiness?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - Ready for comprehensive treatment',
				'good'      => 'Good - Ready for treatment with some concerns',
				'moderate'  => 'Moderate - Somewhat ready for treatment',
				'poor'      => 'Poor - Not very ready for treatment',
				'very_poor' => 'Very Poor - Not ready for treatment',
			),
			'scoring'  => array(
				'category' => 'Treatment Readiness',
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
