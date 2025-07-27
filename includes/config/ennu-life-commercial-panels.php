<?php
/**
 * ENNU Life Commercial Panels Configuration
 * 
 * This file defines all the commercial panels that ENNU Life sells to customers,
 * including biomarkers and business details based on official documentation.
 * 
 * @package ENNU Life Assessments
 * @version 62.52.0
 */

return array(
    // TIER 1: Foundation Panel (Included in Membership)
    'Foundation Panel' => array(
        'biomarker_count' => 50,
        'membership_status' => 'included',
        'description' => 'Complete health foundation and ENNU Life Score calculation',
        'icon' => 'dashicons-star-filled',
        'color' => '#0073aa',
        'biomarkers' => array(
            // Physical Measurements (8 biomarkers)
            'weight', 'bmi', 'body_fat_percent', 'waist_measurement', 'neck_measurement', 'blood_pressure', 'heart_rate', 'temperature',
            // Basic Metabolic Panel (8 biomarkers)
            'glucose', 'hba1c', 'bun', 'creatinine', 'gfr', 'bun_creatinine_ratio', 'sodium', 'potassium',
            // Electrolytes (4 biomarkers)
            'chloride', 'carbon_dioxide', 'calcium', 'magnesium',
            // Protein Panel (2 biomarkers)
            'protein', 'albumin',
            // Liver Function (3 biomarkers)
            'alkaline_phosphate', 'ast', 'alt',
            // Complete Blood Count (8 biomarkers)
            'wbc', 'rbc', 'hemoglobin', 'hematocrit', 'mcv', 'mch', 'mchc', 'rdw', 'platelets',
            // Lipid Panel (5 biomarkers)
            'cholesterol', 'triglycerides', 'hdl', 'vldl', 'ldl',
            // Hormones (6 biomarkers)
            'testosterone_free', 'testosterone_total', 'lh', 'fsh', 'dhea', 'prolactin',
            // Thyroid (3 biomarkers)
            'vitamin_d', 'tsh', 't4', 't3',
            // Performance (1 biomarker)
            'igf_1'
        )
    ),
    
    // TIER 2: Specialized Add-On Panels
    'Guardian Panel' => array(
        'biomarker_count' => 4,
        'membership_status' => 'addon',
        'description' => 'Brain health and cognitive optimization',
        'icon' => 'dashicons-admin-users',
        'color' => '#9c27b0',
        'biomarkers' => array('apoe_genotype', 'ptau_217', 'beta_amyloid_ratio', 'gfap')
    ),
    
    'Protector Panel' => array(
        'biomarker_count' => 4,
        'membership_status' => 'addon',
        'description' => 'Heart health and cardiovascular risk assessment',
        'icon' => 'dashicons-heart',
        'color' => '#f44336',
        'biomarkers' => array('tmao', 'nmr_lipoprofile', 'ferritin', 'one_five_ag')
    ),
    
    'Catalyst Panel' => array(
        'biomarker_count' => 4,
        'membership_status' => 'addon',
        'description' => 'Metabolic health and energy optimization',
        'icon' => 'dashicons-performance',
        'color' => '#ff9800',
        'biomarkers' => array('insulin', 'glycomark', 'uric_acid', 'adiponectin')
    ),
    
    'Detoxifier Panel' => array(
        'biomarker_count' => 3,
        'membership_status' => 'addon',
        'description' => 'Toxicity assessment and detoxification guidance',
        'icon' => 'dashicons-filter',
        'color' => '#4caf50',
        'biomarkers' => array('arsenic', 'lead', 'mercury')
    ),
    
    'Timekeeper Panel' => array(
        'biomarker_count' => 8,
        'membership_status' => 'addon',
        'description' => 'Biological age calculation and longevity optimization',
        'icon' => 'dashicons-clock',
        'color' => '#607d8b',
        'biomarkers' => array('chronological_age', 'gender', 'height', 'weight', 'systolic_blood_pressure', 'diastolic_blood_pressure', 'fasting_glucose', 'hba1c')
    ),
    
    // TIER 3: Advanced Optimization Panels
    'Hormone Optimization Panel' => array(
        'biomarker_count' => 6,
        'membership_status' => 'addon',
        'description' => 'Advanced hormonal health and optimization',
        'icon' => 'dashicons-admin-network',
        'color' => '#e91e63',
        'biomarkers' => array('estradiol', 'progesterone', 'shbg', 'cortisol', 'free_t3', 'free_t4')
    ),
    
    'Cardiovascular Health Panel' => array(
        'biomarker_count' => 5,
        'membership_status' => 'addon',
        'description' => 'Advanced cardiovascular risk assessment and heart health optimization',
        'icon' => 'dashicons-heart',
        'color' => '#d32f2f',
        'biomarkers' => array('apob', 'hs_crp', 'homocysteine', 'lp_a', 'omega_3_index')
    ),
    
    'Longevity & Performance Panel' => array(
        'biomarker_count' => 10,
        'membership_status' => 'addon',
        'description' => 'Biological aging assessment and performance optimization',
        'icon' => 'dashicons-chart-line',
        'color' => '#673ab7',
        'biomarkers' => array('telomere_length', 'nad', 'tac', 'gut_microbiota_diversity', 'mirna_486', 'creatine_kinase', 'il_6', 'grip_strength', 'il_18', 'uric_acid')
    ),
    
    'Cognitive & Energy Panel' => array(
        'biomarker_count' => 5,
        'membership_status' => 'addon',
        'description' => 'Brain health and energy optimization',
        'icon' => 'dashicons-lightbulb',
        'color' => '#ffc107',
        'biomarkers' => array('apoe_genotype', 'coq10', 'heavy_metals_panel', 'ferritin', 'folate')
    ),
    
    'Metabolic Optimization Panel' => array(
        'biomarker_count' => 4,
        'membership_status' => 'addon',
        'description' => 'Advanced metabolic health and insulin optimization',
        'icon' => 'dashicons-chart-area',
        'color' => '#795548',
        'biomarkers' => array('fasting_insulin', 'homa_ir', 'leptin', 'ghrelin')
    )
); 