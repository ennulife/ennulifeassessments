<?php
/**
 * ENNU Symptom to Biomarker Mapping
 * Maps symptoms reported in assessments to biomarkers that should be flagged
 * 
 * @package ENNU_Life
 * @version 64.55.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Symptom to Biomarker Mapping Array
 * 
 * Key: symptom identifier (as used in assessment questions)
 * Value: array of biomarker keys that should be flagged when this symptom is reported
 */
return array(
    // General Symptoms
    'fatigue' => array(
        'thyroid_tsh',
        'thyroid_t3',
        'thyroid_t4',
        'iron_ferritin',
        'vitamin_d',
        'vitamin_b12',
        'cortisol',
        'testosterone_total',
        'hemoglobin',
        'glucose_fasting'
    ),
    
    'brain_fog' => array(
        'thyroid_tsh',
        'thyroid_t3',
        'thyroid_t4',
        'vitamin_b12',
        'vitamin_d',
        'cortisol',
        'glucose_fasting',
        'inflammation_crp',
        'testosterone_total'
    ),
    
    'weight_gain' => array(
        'thyroid_tsh',
        'thyroid_t3',
        'thyroid_t4',
        'insulin_fasting',
        'glucose_fasting',
        'cortisol',
        'testosterone_total',
        'estradiol',
        'leptin'
    ),
    
    'anxiety' => array(
        'cortisol',
        'thyroid_tsh',
        'thyroid_t3',
        'vitamin_d',
        'vitamin_b12',
        'magnesium',
        'testosterone_total'
    ),
    
    'depression' => array(
        'vitamin_d',
        'vitamin_b12',
        'thyroid_tsh',
        'testosterone_total',
        'cortisol',
        'inflammation_crp',
        'folate'
    ),
    
    // Peptide Therapy Related Symptoms
    'slow_recovery' => array(
        'growth_hormone',
        'igf1',
        'testosterone_total',
        'cortisol',
        'inflammation_crp'
    ),
    
    'poor_healing' => array(
        'growth_hormone',
        'igf1',
        'vitamin_d',
        'zinc',
        'inflammation_crp'
    ),
    
    'muscle_loss' => array(
        'testosterone_total',
        'growth_hormone',
        'igf1',
        'dhea_s',
        'cortisol'
    ),
    
    'low_libido' => array(
        'testosterone_total',
        'testosterone_free',
        'estradiol',
        'dhea_s',
        'prolactin'
    ),
    
    'cognitive_decline' => array(
        'vitamin_b12',
        'thyroid_tsh',
        'testosterone_total',
        'growth_hormone',
        'inflammation_crp'
    ),
    
    // Sleep-Related Symptoms
    'insomnia' => array(
        'cortisol',
        'melatonin',
        'thyroid_tsh',
        'magnesium',
        'vitamin_d',
        'testosterone_total'
    ),
    
    'sleep_issues' => array(
        'cortisol',
        'melatonin',
        'thyroid_tsh',
        'magnesium',
        'testosterone_total'
    ),
    
    'night_sweats' => array(
        'estradiol',
        'testosterone_total',
        'thyroid_tsh',
        'cortisol',
        'glucose_fasting'
    ),
    
    'restless_legs' => array(
        'iron_ferritin',
        'magnesium',
        'vitamin_d',
        'folate',
        'vitamin_b12'
    ),
    
    // Hormonal Symptoms
    'low_libido' => array(
        'testosterone_total',
        'testosterone_free',
        'estradiol',
        'dhea_s',
        'thyroid_tsh',
        'prolactin'
    ),
    
    'hot_flashes' => array(
        'estradiol',
        'fsh',
        'lh',
        'testosterone_total',
        'thyroid_tsh'
    ),
    
    'mood_swings' => array(
        'estradiol',
        'progesterone',
        'testosterone_total',
        'cortisol',
        'thyroid_tsh'
    ),
    
    'irregular_periods' => array(
        'estradiol',
        'progesterone',
        'fsh',
        'lh',
        'testosterone_total',
        'thyroid_tsh',
        'prolactin'
    ),
    
    // Metabolic Symptoms
    'high_blood_pressure' => array(
        'sodium',
        'potassium',
        'magnesium',
        'cortisol',
        'thyroid_tsh',
        'kidney_egfr'
    ),
    
    'diabetes' => array(
        'glucose_fasting',
        'hemoglobin_a1c',
        'insulin_fasting',
        'c_peptide'
    ),
    
    'high_cholesterol' => array(
        'cholesterol_total',
        'cholesterol_ldl',
        'cholesterol_hdl',
        'triglycerides',
        'apolipoprotein_b',
        'lipoprotein_a'
    ),
    
    // Skin/Hair Symptoms
    'hair_loss' => array(
        'iron_ferritin',
        'thyroid_tsh',
        'thyroid_t3',
        'thyroid_t4',
        'testosterone_total',
        'dhea_s',
        'vitamin_d',
        'zinc'
    ),
    
    'brittle_hair' => array(
        'thyroid_tsh',
        'iron_ferritin',
        'zinc',
        'biotin',
        'protein_total'
    ),
    
    'dry_skin' => array(
        'thyroid_tsh',
        'thyroid_t3',
        'thyroid_t4',
        'vitamin_d',
        'omega_3_index'
    ),
    
    'acne' => array(
        'testosterone_total',
        'testosterone_free',
        'dhea_s',
        'insulin_fasting',
        'igf_1'
    ),
    
    // Digestive Symptoms
    'bloating' => array(
        'thyroid_tsh',
        'inflammation_crp',
        'vitamin_b12',
        'folate',
        'magnesium'
    ),
    
    'constipation' => array(
        'thyroid_tsh',
        'thyroid_t3',
        'magnesium',
        'vitamin_d'
    ),
    
    // Pain/Inflammation Symptoms
    'joint_pain' => array(
        'inflammation_crp',
        'inflammation_esr',
        'rheumatoid_factor',
        'vitamin_d',
        'uric_acid'
    ),
    
    'muscle_weakness' => array(
        'vitamin_d',
        'testosterone_total',
        'thyroid_tsh',
        'potassium',
        'magnesium',
        'creatine_kinase'
    ),
    
    'headaches' => array(
        'magnesium',
        'vitamin_b12',
        'vitamin_d',
        'iron_ferritin',
        'thyroid_tsh'
    ),
    
    // Cardiovascular Symptoms
    'poor_circulation' => array(
        'hemoglobin',
        'iron_ferritin',
        'thyroid_tsh',
        'vitamin_b12',
        'homocysteine'
    ),
    
    'chest_pain' => array(
        'troponin',
        'creatine_kinase',
        'cholesterol_ldl',
        'inflammation_crp',
        'homocysteine'
    ),
    
    // Cognitive Symptoms
    'memory_issues' => array(
        'vitamin_b12',
        'folate',
        'thyroid_tsh',
        'testosterone_total',
        'vitamin_d',
        'homocysteine'
    ),
    
    'difficulty_concentrating' => array(
        'thyroid_tsh',
        'thyroid_t3',
        'iron_ferritin',
        'vitamin_b12',
        'testosterone_total',
        'cortisol'
    ),
    
    // Energy Symptoms
    'low_energy' => array(
        'thyroid_tsh',
        'thyroid_t3',
        'thyroid_t4',
        'iron_ferritin',
        'vitamin_d',
        'vitamin_b12',
        'testosterone_total',
        'cortisol'
    ),
    
    // Immune Symptoms
    'frequent_infections' => array(
        'vitamin_d',
        'zinc',
        'white_blood_cells',
        'immunoglobulin_a',
        'immunoglobulin_g'
    ),
    
    'slow_healing' => array(
        'glucose_fasting',
        'hemoglobin_a1c',
        'zinc',
        'vitamin_c',
        'protein_total'
    ),
    
    // Thyroid-Specific Symptoms
    'thyroid_issues' => array(
        'thyroid_tsh',
        'thyroid_t3',
        'thyroid_t4',
        'thyroid_antibodies_tpo',
        'thyroid_antibodies_tg'
    ),
    
    // Stress-Related Symptoms
    'stress' => array(
        'cortisol',
        'dhea_s',
        'magnesium',
        'vitamin_b_complex',
        'inflammation_crp'
    ),
    
    'high_stress' => array(
        'cortisol',
        'dhea_s',
        'magnesium',
        'vitamin_b_complex',
        'inflammation_crp',
        'testosterone_total'
    ),
    
    // Nutrition-related symptoms
    'digestive_issues' => array(
        'vitamin_b12',
        'folate',
        'iron_ferritin',
        'magnesium',
        'zinc',
        'vitamin_d',
        'inflammation_crp',
        'thyroid_tsh'
    ),
    
    'nutrient_deficiencies' => array(
        'vitamin_b12',
        'vitamin_d',
        'iron_ferritin',
        'folate',
        'magnesium',
        'zinc',
        'vitamin_a',
        'vitamin_e',
        'omega_3'
    ),
    
    'food_sensitivities' => array(
        'inflammation_crp',
        'inflammation_hscrp',
        'immunoglobulin_e',
        'eosinophils',
        'histamine',
        'vitamin_d',
        'zinc'
    )
);