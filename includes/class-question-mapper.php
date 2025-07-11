<?php
/**
 * ENNU Life Question Mapper
 * 
 * Maps form question numbers to scoring system semantic keys
 * This is the missing link between forms and scoring system
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Question_Mapper {
    
    /**
     * Question mapping configurations
     */
    private static $question_mappings = array(
        'hair_assessment' => array(
            'hair_q1' => 'gender',
            'hair_q2' => 'hair_concerns', 
            'hair_q3' => 'duration',
            'hair_q4' => 'speed',
            'hair_q5' => 'family_history',
            'hair_q6' => 'stress_level',
            'hair_q7' => 'diet_quality',
            'hair_q8' => 'previous_treatments',
            'hair_q9' => 'goals'
        ),
        'ed_treatment_assessment' => array(
            'ed_treatment_q1' => 'relationship_status',
            'ed_treatment_q2' => 'severity',
            'ed_treatment_q3' => 'frequency', 
            'ed_treatment_q4' => 'duration',
            'ed_treatment_q5' => 'health_conditions',
            'ed_treatment_q6' => 'medications',
            'ed_treatment_q7' => 'lifestyle_factors',
            'ed_treatment_q8' => 'psychological_factors',
            'ed_treatment_q9' => 'previous_treatments'
        ),
        'weight_loss_assessment' => array(
            'weight_loss_q1' => 'current_weight',
            'weight_loss_q2' => 'target_weight',
            'weight_loss_q3' => 'weight_history',
            'weight_loss_q4' => 'diet_history',
            'weight_loss_q5' => 'exercise_frequency',
            'weight_loss_q6' => 'motivation',
            'weight_loss_q7' => 'barriers',
            'weight_loss_q8' => 'support_system',
            'weight_loss_q9' => 'previous_attempts',
            'weight_loss_q10' => 'health_conditions',
            'weight_loss_q11' => 'medications',
            'weight_loss_q12' => 'lifestyle_factors',
            'weight_loss_q13' => 'goals'
        ),
        'health_assessment' => array(
            'health_q1' => 'overall_health',
            'health_q2' => 'energy_levels',
            'health_q3' => 'sleep_quality',
            'health_q4' => 'stress_levels',
            'health_q5' => 'exercise_frequency',
            'health_q6' => 'diet_quality',
            'health_q7' => 'health_conditions',
            'health_q8' => 'medications',
            'health_q9' => 'preventive_care',
            'health_q10' => 'lifestyle_factors',
            'health_q11' => 'health_goals'
        ),
        'skin_assessment' => array(
            'skin_q1' => 'skin_type',
            'skin_q2' => 'skin_concerns',
            'skin_q3' => 'sun_exposure',
            'skin_q4' => 'skincare_routine',
            'skin_q5' => 'product_sensitivities',
            'skin_q6' => 'lifestyle_factors',
            'skin_q7' => 'hormonal_factors',
            'skin_q8' => 'previous_treatments',
            'skin_q9' => 'skin_goals'
        )
    );

    /**
     * Map form responses to scoring system keys
     */
    public static function map_responses($assessment_type, $responses) {
        if (!isset(self::$question_mappings[$assessment_type])) {
            throw new Exception("Unknown assessment type: $assessment_type");
        }
        
        $mapped = array();
        $mapping = self::$question_mappings[$assessment_type];
        
        foreach ($responses as $question_key => $value) {
            if (isset($mapping[$question_key])) {
                $mapped[$mapping[$question_key]] = $value;
            }
        }
        
        return $mapped;
    }
}
