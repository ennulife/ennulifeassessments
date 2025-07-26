<?php
/**
 * Assessment Definition: Health Assessment
 * Enhanced by Dr. Mira Insight (Psychiatry Master)
 * Evidence-based psychiatry implementation
 *
 * @package ENNU_Life
 * @version 62.11.0
 */

return array(
	'title'             => 'Health Assessment',
	'assessment_engine' => 'quantitative',
	'questions'         => array(
		'health_q_gender' => array(
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
		'health_q_dob'    => array(
			'title'      => 'What is your date of birth?',
			'type'       => 'dob_dropdowns',
			'required'   => true,
			'global_key' => 'date_of_birth',
			'scoring'    => array(
				'category' => 'Age Factors',
				'weight'   => 1.5,
				'calculation' => 'age_from_dob',
				'age_scores' => array(
					'18-25' => 7,
					'26-35' => 6,
					'36-45' => 5,
					'46-55' => 4,
					'56-65' => 3,
					'66-75' => 2,
					'76+'   => 1,
				),
			),
		),
		'health_q1'       => array(
			'title'    => 'How would you rate your current overall health?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - I feel great and have no health concerns',
				'good'      => 'Good - I feel generally healthy with minor issues',
				'fair'      => 'Fair - I have some health concerns that affect me',
				'poor'      => 'Poor - I have significant health issues',
				'very_poor' => 'Very Poor - I have serious health problems',
			),
			'scoring'  => array(
				'category' => 'Current Health Status',
				'weight'   => 3,
				'answers'  => array(
					'excellent' => 9,
					'good'      => 7,
					'fair'      => 5,
					'poor'      => 3,
					'very_poor' => 1,
				),
			),
			'required' => true,
		),
		'health_q2'       => array(
			'title'    => 'How are your energy levels throughout the day?',
			'type'     => 'radio',
			'options'  => array(
				'high_consistent'     => 'High and consistent throughout the day',
				'moderate_consistent' => 'Moderate and consistent',
				'morning_high'        => 'High in morning, lower in afternoon',
				'afternoon_crash'     => 'Good in morning, crash in afternoon',
				'consistently_low'    => 'Consistently low throughout the day',
			),
			'scoring'  => array(
				'category' => 'Vitality & Energy',
				'weight'   => 2.5,
				'answers'  => array(
					'high_consistent'     => 9,
					'moderate_consistent' => 7,
					'morning_high'        => 5,
					'afternoon_crash'     => 3,
					'consistently_low'    => 2,
				),
			),
			'required' => true,
		),
		'health_q3'       => array(
			'title'    => 'How many days per week do you engage in moderate to vigorous exercise?',
			'type'     => 'radio',
			'options'  => array(
				'6_plus' => '6 or more days',
				'4_5'    => '4-5 days',
				'2_3'    => '2-3 days',
				'1'      => '1 day',
				'none'   => 'I rarely or never exercise',
			),
			'scoring'  => array(
				'category' => 'Physical Activity',
				'weight'   => 2.5,
				'answers'  => array(
					'6_plus' => 9,
					'4_5'    => 7,
					'2_3'    => 5,
					'1'      => 3,
					'none'   => 1,
				),
			),
			'required' => true,
		),
		'health_q4'       => array(
			'title'    => 'How would you describe your typical diet?',
			'type'     => 'radio',
			'options'  => array(
				'whole_foods' => 'Primarily whole foods, rich in fruits and vegetables',
				'balanced'    => 'Generally balanced with some processed foods',
				'mixed'       => 'Mixed - some healthy, some processed foods',
				'processed'   => 'High in processed foods and sugar',
				'irregular'   => 'Irregular or often skip meals',
			),
			'scoring'  => array(
				'category' => 'Nutrition',
				'weight'   => 2.5,
				'answers'  => array(
					'whole_foods' => 9,
					'balanced'    => 7,
					'mixed'       => 5,
					'processed'   => 3,
					'irregular'   => 2,
				),
			),
			'required' => true,
		),
		'health_q5'       => array(
			'title'    => 'How would you rate your mental health and emotional well-being?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - I feel mentally strong and emotionally stable',
				'good'      => 'Good - Generally positive mood with minor fluctuations',
				'moderate'  => 'Moderate - Some mood swings but manageable',
				'poor'      => 'Poor - Frequent mood issues affecting daily life',
				'very_poor' => 'Very Poor - Significant mental health struggles',
			),
			'scoring'  => array(
				'category' => 'Mental Health',
				'weight'   => 3,
				'answers'  => array(
					'excellent' => 9,
					'good'      => 7,
					'moderate'  => 5,
					'poor'      => 3,
					'very_poor' => 1,
				),
			),
			'required' => true,
		),
		'health_q6'       => array(
			'title'    => 'How would you describe your stress levels?',
			'type'     => 'radio',
			'options'  => array(
				'very_low'  => 'Very Low - I rarely feel stressed',
				'low'       => 'Low - I experience minimal stress',
				'moderate'  => 'Moderate - I experience some stress but manage it well',
				'high'      => 'High - I frequently feel stressed and overwhelmed',
				'very_high' => 'Very High - I feel constantly stressed and struggle to cope',
			),
			'scoring'  => array(
				'category' => 'Stress Management',
				'weight'   => 2.5,
				'answers'  => array(
					'very_low'  => 9,
					'low'       => 7,
					'moderate'  => 5,
					'high'      => 3,
					'very_high' => 1,
				),
			),
			'required' => true,
		),
		'health_q7'       => array(
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
				'weight'   => 2.5,
				'answers'  => array(
					'excellent' => 9,
					'good'      => 7,
					'moderate'  => 5,
					'poor'      => 3,
					'very_poor' => 1,
				),
			),
			'required' => true,
		),
		'health_q8'       => array(
			'title'    => 'How would you describe your social connections and relationships?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - Strong, supportive relationships',
				'good'      => 'Good - Generally positive relationships',
				'moderate'  => 'Moderate - Some good relationships, some challenges',
				'poor'      => 'Poor - Limited or strained relationships',
				'very_poor' => 'Very Poor - Isolated or toxic relationships',
			),
			'scoring'  => array(
				'category' => 'Social Health',
				'weight'   => 2,
				'answers'  => array(
					'excellent' => 9,
					'good'      => 7,
					'moderate'  => 5,
					'poor'      => 3,
					'very_poor' => 1,
				),
			),
			'required' => true,
		),
		'health_q9'       => array(
			'title'    => 'How would you describe your ability to focus and concentrate?',
			'type'     => 'radio',
			'options'  => array(
				'excellent' => 'Excellent - No issues with focus or concentration',
				'good'      => 'Good - Minor focus issues, easily managed',
				'moderate'  => 'Moderate - Some difficulty concentrating at times',
				'poor'      => 'Poor - Significant focus problems affecting work/life',
				'very_poor' => 'Very Poor - Severe concentration issues',
			),
			'scoring'  => array(
				'category' => 'Cognitive Function',
				'weight'   => 2,
				'answers'  => array(
					'excellent' => 9,
					'good'      => 7,
					'moderate'  => 5,
					'poor'      => 3,
					'very_poor' => 1,
				),
			),
			'required' => true,
		),
		'health_q10'      => array(
			'title'    => 'How would you describe your overall mood?',
			'type'     => 'radio',
			'options'  => array(
				'very_positive' => 'Very Positive - Optimistic and happy most of the time',
				'positive'      => 'Positive - Generally good mood with occasional lows',
				'neutral'       => 'Neutral - Stable mood, neither particularly high nor low',
				'negative'      => 'Negative - Often feeling down or irritable',
				'very_negative' => 'Very Negative - Frequently depressed or anxious',
			),
			'scoring'  => array(
				'category' => 'Mood Assessment',
				'weight'   => 2.5,
				'answers'  => array(
					'very_positive' => 9,
					'positive'      => 7,
					'neutral'       => 5,
					'negative'      => 3,
					'very_negative' => 1,
				),
			),
			'required' => true,
		),
		'health_q11'      => array(
			'title'    => 'How would you describe your motivation and drive?',
			'type'     => 'radio',
			'options'  => array(
				'very_high' => 'Very High - Highly motivated and driven',
				'high'      => 'High - Generally motivated with clear goals',
				'moderate'  => 'Moderate - Some motivation but could be stronger',
				'low'       => 'Low - Often struggle with motivation',
				'very_low'  => 'Very Low - Frequently lack motivation and drive',
			),
			'scoring'  => array(
				'category' => 'Motivation & Drive',
				'weight'   => 2,
				'answers'  => array(
					'very_high' => 9,
					'high'      => 7,
					'moderate'  => 5,
					'low'       => 3,
					'very_low'  => 1,
				),
			),
			'required' => true,
		),
		'health_q12'      => array(
			'title'    => 'How would you describe your overall life satisfaction?',
			'type'     => 'radio',
			'options'  => array(
				'very_satisfied'    => 'Very Satisfied - I am very happy with my life',
				'satisfied'         => 'Satisfied - I am generally happy with my life',
				'neutral'           => 'Neutral - I am neither particularly satisfied nor dissatisfied',
				'dissatisfied'      => 'Dissatisfied - I am often unhappy with my life',
				'very_dissatisfied' => 'Very Dissatisfied - I am very unhappy with my life',
			),
			'scoring'  => array(
				'category' => 'Life Satisfaction',
				'weight'   => 2.5,
				'answers'  => array(
					'very_satisfied'    => 9,
					'satisfied'         => 7,
					'neutral'           => 5,
					'dissatisfied'      => 3,
					'very_dissatisfied' => 1,
				),
			),
			'required' => true,
		),
	),
);
