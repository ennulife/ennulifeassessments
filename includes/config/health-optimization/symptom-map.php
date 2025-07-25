<?php
/**
 * Health Optimization: Symptom-to-Vector Map
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
	'Abdominal Fat Gain'           => array( 'Weight Loss' => array( 'weight' => 0.7 ) ),
	'Anxiety'                      => array( 'Hormones' => array( 'weight' => 0.6 ) ),
	'Blood Glucose Dysregulation'  => array( 'Weight Loss' => array( 'weight' => 0.8 ) ),
	'Brain Fog'                    => array(
		'Energy'           => array( 'weight' => 0.7 ),
		'Cognitive Health' => array( 'weight' => 0.8 ),
	),
	'Change in Personality'        => array( 'Cognitive Health' => array( 'weight' => 0.9 ) ),
	'Chest Pain'                   => array( 'Heart Health' => array( 'weight' => 1.0 ) ),
	'Chronic Fatigue'              => array( 'Longevity' => array( 'weight' => 0.8 ) ),
	'Cognitive Decline'            => array( 'Longevity' => array( 'weight' => 0.9 ) ),
	'Confusion'                    => array( 'Cognitive Health' => array( 'weight' => 0.9 ) ),
	'Decreased Mobility'           => array( 'Strength' => array( 'weight' => 0.7 ) ),
	'Decreased Physical Activity'  => array( 'Longevity' => array( 'weight' => 0.6 ) ),
	'Depression'                   => array( 'Hormones' => array( 'weight' => 0.7 ) ),
	'Erectile Dysfunction'         => array(
		'Hormones'     => array( 'weight' => 0.8 ),
		'Heart Health' => array( 'weight' => 0.7 ),
		'Libido'       => array( 'weight' => 0.9 ),
	),
	'Fatigue'                      => array(
		'Energy'       => array( 'weight' => 0.8 ),
		'Heart Health' => array( 'weight' => 0.5 ),
		'Weight Loss'  => array( 'weight' => 0.5 ),
		'Strength'     => array( 'weight' => 0.6 ),
	),
	'Frequent Illness'             => array(
		'Energy'    => array( 'weight' => 0.6 ),
		'Longevity' => array( 'weight' => 0.7 ),
	),
	'High Blood Pressure'          => array(
		'Weight Loss'  => array( 'weight' => 0.8 ),
		'Heart Health' => array( 'weight' => 0.9 ),
	),
	'Hot Flashes'                  => array( 'Hormones' => array( 'weight' => 0.9 ) ),
	'Increased Body Fat'           => array( 'Weight Loss' => array( 'weight' => 0.8 ) ),
	'Infertility'                  => array(
		'Hormones' => array( 'weight' => 0.9 ),
		'Libido'   => array( 'weight' => 0.7 ),
	),
	'Irritability'                 => array( 'Hormones' => array( 'weight' => 0.6 ) ),
	'Itchy Skin'                   => array( 'Longevity' => array( 'weight' => 0.4 ) ),
	'Joint Pain'                   => array(
		'Weight Loss' => array( 'weight' => 0.6 ),
		'Strength'    => array( 'weight' => 0.7 ),
	),
	'Lack of Motivation'           => array( 'Energy' => array( 'weight' => 0.7 ) ),
	'Language Problems'            => array( 'Cognitive Health' => array( 'weight' => 0.9 ) ),
	'Lightheadedness'              => array( 'Heart Health' => array( 'weight' => 0.8 ) ),
	'Low Libido'                   => array(
		'Hormones' => array( 'weight' => 0.8 ),
		'Libido'   => array( 'weight' => 1.0 ),
	),
	'Low Self-Esteem'              => array( 'Libido' => array( 'weight' => 0.5 ) ),
	'Memory Loss'                  => array( 'Cognitive Health' => array( 'weight' => 0.9 ) ),
	'Mood Changes'                 => array( 'Cognitive Health' => array( 'weight' => 0.7 ) ),
	'Mood Swings'                  => array( 'Hormones' => array( 'weight' => 0.7 ) ),
	'Muscle Loss'                  => array(
		'Strength'  => array( 'weight' => 0.8 ),
		'Longevity' => array( 'weight' => 0.9 ),
	),
	'Muscle Mass Loss'             => array( 'Strength' => array( 'weight' => 0.8 ) ),
	'Muscle Weakness'              => array( 'Energy' => array( 'weight' => 0.7 ) ),
	'Night Sweats'                 => array( 'Hormones' => array( 'weight' => 0.8 ) ),
	'Palpitations'                 => array( 'Heart Health' => array( 'weight' => 0.9 ) ),
	'Poor Balance'                 => array( 'Strength' => array( 'weight' => 0.6 ) ),
	'Poor Concentration'           => array( 'Cognitive Health' => array( 'weight' => 0.8 ) ),
	'Poor Coordination'            => array( 'Cognitive Health' => array( 'weight' => 0.7 ) ),
	'Poor Exercise Tolerance'      => array( 'Heart Health' => array( 'weight' => 0.7 ) ),
	'Poor Sleep'                   => array( 'Energy' => array( 'weight' => 0.8 ) ),
	'Prolonged Soreness'           => array( 'Strength' => array( 'weight' => 0.7 ) ),
	'Reduced Physical Performance' => array(
		'Energy'      => array( 'weight' => 0.7 ),
		'Weight Loss' => array( 'weight' => 0.6 ),
	),
	'Shortness of Breath'          => array( 'Heart Health' => array( 'weight' => 1.0 ) ),
	'Sleep Disturbance'            => array( 'Cognitive Health' => array( 'weight' => 0.6 ) ),
	'Sleep Problems'               => array( 'Weight Loss' => array( 'weight' => 0.5 ) ),
	'Slow Healing Wounds'          => array( 'Longevity' => array( 'weight' => 0.8 ) ),
	'Slow Metabolism'              => array( 'Weight Loss' => array( 'weight' => 0.7 ) ),
	'Slow Recovery'                => array( 'Strength' => array( 'weight' => 0.7 ) ),
	'Swelling'                     => array( 'Heart Health' => array( 'weight' => 0.8 ) ),
	'Vaginal Dryness'              => array(
		'Hormones' => array( 'weight' => 0.8 ),
		'Libido'   => array( 'weight' => 0.7 ),
	),
	'Weakness'                     => array( 'Strength' => array( 'weight' => 0.7 ) ),
	'Weight Changes'               => array( 'Longevity' => array( 'weight' => 0.6 ) ),
);
