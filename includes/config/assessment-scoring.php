<?php
/**
 * ENNU Life Assessment Scoring Configuration - MASTER
 *
 * This file centralizes all assessment scoring rules.
 *
 * @package ENNU_Life
 * @version 28.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'hair_assessment' => array(
        'gender' => array('male' => 2, 'female' => 1),
        'hair_concerns' => array('thinning' => 2, 'receding' => 3, 'bald_spots' => 4, 'overall_loss' => 5),
        'duration' => array('recent' => 1, 'moderate' => 2, 'long' => 3, 'very_long' => 4),
        'speed' => array('slow' => 1, 'moderate' => 2, 'fast' => 3, 'very_fast' => 4),
        'family_history' => array('none' => 0, 'mother' => 2, 'father' => 2, 'both' => 4),
        'stress_level' => array('low' => 1, 'moderate' => 2, 'high' => 3, 'very_high' => 4),
        'diet_quality' => array('excellent' => 0, 'good' => 1, 'fair' => 2, 'poor' => 3),
        'previous_treatments' => array('none' => 0, 'otc' => 1, 'prescription' => 2, 'procedures' => 3),
        'goals' => array('stop_loss' => 1, 'regrow' => 2, 'thicken' => 1, 'improve' => 1)
    ),
    'ed_treatment_assessment' => array(
        'relationship_status' => array('single' => 1, 'dating' => 2, 'married' => 3, 'divorced' => 1),
        'severity' => array('mild' => 1, 'moderate' => 2, 'severe' => 3, 'complete' => 4),
        'duration' => array('recent' => 1, 'moderate' => 2, 'long' => 3, 'very_long' => 4),
        'health_conditions' => array('none' => 0, 'diabetes' => 3, 'heart' => 4, 'hypertension' => 3),
        'previous_treatments' => array('none' => 0, 'oral' => 1, 'injections' => 2, 'devices' => 1),
        'smoking_status' => array('no' => 0, 'yes_daily' => 3, 'yes_occasionally' => 2, 'former' => 1),
        'exercise' => array('never' => 3, 'rarely' => 2, 'regularly' => 1, 'daily' => 0),
        'stress_level' => array('low' => 1, 'moderate' => 2, 'high' => 3, 'very_high' => 4),
        'goals' => array('restore' => 2, 'confidence' => 1, 'performance' => 2, 'relationship' => 1)
    ),
    'weight_loss_assessment' => array(
        'gender' => array('male' => 1, 'female' => 1),
        'age' => array('under_30' => 1, '30_45' => 2, '46_60' => 3, 'over_60' => 4),
        'current_weight_status' => array('slightly' => 1, 'moderately' => 2, 'significantly' => 3, 'obese' => 4),
        'activity_level' => array('sedentary' => 4, 'lightly' => 3, 'moderately' => 2, 'very' => 1),
        'dietary_habits' => array('healthy' => 1, 'balanced' => 2, 'unhealthy' => 3, 'very_unhealthy' => 4),
        'motivation_level' => array('low' => 4, 'moderate' => 3, 'high' => 2, 'very_high' => 1),
        'previous_attempts' => array('none' => 1, 'one' => 2, 'several' => 3, 'many' => 4),
        'health_conditions' => array('none' => 0, 'diabetes' => 3, 'hypertension' => 3, 'cholesterol' => 3),
        'goals' => array('lose_5_10' => 1, 'lose_10_20' => 2, 'lose_20_plus' => 3, 'lifestyle' => 1)
    ),
    'health_assessment' => array(
        'energy_levels' => array('high' => 1, 'moderate' => 2, 'low' => 3, 'exhausted' => 4),
        'sleep_quality' => array('good' => 1, 'fair' => 2, 'poor' => 3, 'insomnia' => 4),
        'stress_level' => array('low' => 1, 'moderate' => 2, 'high' => 3, 'very_high' => 4),
        'diet_quality' => array('excellent' => 1, 'good' => 2, 'fair' => 3, 'poor' => 4),
        'exercise_frequency' => array('daily' => 1, 'few_times_week' => 2, 'rarely' => 3, 'never' => 4),
        'mental_clarity' => array('sharp' => 1, 'clear' => 2, 'foggy' => 3, 'very_foggy' => 4),
        'known_conditions' => array('none' => 1, 'one' => 2, 'multiple' => 3, 'chronic' => 4),
        'goals' => array('optimize' => 1, 'preventive' => 2, 'address_symptoms' => 3, 'major_change' => 4)
    ),
    'skin_assessment' => array(
        'skin_type' => array('oily' => 2, 'dry' => 2, 'combination' => 2, 'normal' => 1),
        'primary_concern' => array('acne' => 3, 'aging' => 3, 'pigmentation' => 4, 'redness' => 2),
        'sun_exposure' => array('low' => 1, 'moderate' => 2, 'high' => 3, 'very_high' => 4),
        'skincare_routine' => array('consistent' => 1, 'inconsistent' => 2, 'minimal' => 3, 'none' => 4),
        'stress_level' => array('low' => 1, 'moderate' => 2, 'high' => 3, 'very_high' => 4),
        'diet_quality' => array('excellent' => 1, 'good' => 2, 'fair' => 3, 'poor' => 4),
        'previous_treatments' => array('none' => 1, 'otc' => 2, 'prescription' => 3, 'professional' => 4),
        'goals' => array('clear_acne' => 2, 'reduce_wrinkles' => 2, 'even_tone' => 3, 'calm_skin' => 1)
    )
); 