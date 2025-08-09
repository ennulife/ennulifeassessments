<?php
/**
 * Assessment Definition: Welcome Assessment
 *
 * @package ENNU_Life
 * @version 62.11.0
 */

return array(
	'title'             => 'Welcome Assessment',
	'assessment_engine' => 'qualitative',
	'questions'         => array(
		'ennu_global_date_of_birth' => array(
			'title'      => 'What is your date of birth?',
			'type'       => 'dob_dropdowns',
			'required'   => true,
			'global_key' => 'date_of_birth',
		),
		'ennu_global_gender' => array(
			'title'      => 'What is your gender?',
			'type'       => 'radio',
			'options'    => array(
				'female' => 'FEMALE',
				'male'   => 'MALE',
			),
			'required'   => true,
			'global_key' => 'gender',
		),
		'ennu_global_height_weight' => array(
			'title'      => 'What is your height and weight?',
			'type'       => 'height_weight',
			'required'   => true,
			'global_key' => 'height_weight',
		),
		'ennu_global_health_goals' => array(
			'title'      => 'What are your primary health goals?',
			'type'       => 'multiselect',
			'options'    => array(
				'longevity'        => 'Longevity & Healthy Aging',
				'energy'           => 'Improve Energy & Vitality',
				'strength'         => 'Build Strength & Muscle',
				'libido'           => 'Enhance Libido & Sexual Health',
				'weight_loss'      => 'Achieve & Maintain Healthy Weight',
				'hormonal_balance' => 'Hormonal Balance',
				'cognitive_health' => 'Sharpen Cognitive Function',
				'heart_health'     => 'Support Heart Health',
				'aesthetics'       => 'Improve Hair, Skin & Nails',
				'sleep'            => 'Improve Sleep Quality',
				'stress'           => 'Reduce Stress & Improve Resilience',
			),
			'required'   => true,
			'global_key' => 'health_goals',
		),
	),
);
