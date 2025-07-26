<?php
/**
 * ENNU Life Biomarker Panels Configuration
 *
 * @package ENNU_Life
 * @version 2.1
 * @description Complete panel structure with pricing, biomarkers, and business model alignment
 * @date 2025-07-22
 */

return array(
    'panels' => array(
        'foundation_panel' => array(
            'name' => 'The Foundation Panel',
            'display_name' => 'The Foundation Panel',
            'description' => 'Complete health foundation with 50 biomarkers',
            'price' => 599,
            'included_in_membership' => true,
            'membership_tier' => 'basic',
            'biomarker_count' => 50,
            'purpose' => 'Complete health foundation and actual ENNU Life Score calculation',
            'long_description' => 'The Foundation Panel is the cornerstone of the ENNU Life health assessment system, providing a comprehensive baseline evaluation of your overall health status. This panel includes essential physical measurements, complete metabolic profiling, cardiovascular risk assessment, hormonal balance evaluation, and immune system analysis. Unlike questionnaire-based health assessments, the Foundation Panel delivers actual biomarker data that enables precise calculation of your ENNU Life Score and provides actionable insights for health optimization.',
            'biomarkers' => array(
                // Physical Measurements (8 biomarkers)
                'weight', 'bmi', 'body_fat_percent', 'waist_measurement',
                'neck_measurement', 'blood_pressure', 'heart_rate', 'temperature',
                
                // Basic Metabolic Panel (8 biomarkers)
                'glucose', 'hba1c', 'bun', 'creatinine', 'gfr', 'bun_creatinine_ratio',
                'sodium', 'potassium',
                
                // Electrolytes & Minerals (4 biomarkers)
                'chloride', 'carbon_dioxide', 'calcium', 'magnesium',
                
                // Protein Panel (2 biomarkers)
                'protein', 'albumin',
                
                // Liver Function (3 biomarkers)
                'alkaline_phosphatase', 'ast', 'alt',
                
                // Complete Blood Count (8 biomarkers)
                'wbc', 'rbc', 'hemoglobin', 'hematocrit', 'mcv', 'mch', 'mchc', 'rdw',
                
                // Lipid Panel (5 biomarkers)
                'cholesterol', 'triglycerides', 'hdl', 'vldl', 'ldl',
                
                // Hormones (6 biomarkers)
                'testosterone_free', 'testosterone_total', 'estradiol', 'progesterone',
                'fsh', 'lh',
                
                // Thyroid (3 biomarkers)
                'tsh', 't4', 't3',
                
                // Performance (1 biomarker)
                'igf_1',
                
                // Additional Core Biomarkers (2 biomarkers)
                'dhea', 'prolactin'
            ),
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership'),
            'collection_method' => 'Standard blood draw + physical measurements',
            'frequency' => 'Quarterly',
            'source' => 'ENNU Life Labs + Physical Assessment'
        ),
        
        'guardian_panel' => array(
            'name' => 'The Guardian Panel',
            'display_name' => 'The Guardian Panel',
            'description' => 'Advanced neurological biomarkers for brain health',
            'price' => 199,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 4,
            'purpose' => 'Brain health and cognitive optimization',
            'long_description' => 'The Guardian Panel focuses specifically on brain health and cognitive function, utilizing cutting-edge biomarkers to assess neurological health and identify early signs of cognitive decline. This panel includes genetic risk assessment (ApoE genotype), advanced protein markers for Alzheimer\'s disease (pTau-217, Beta-Amyloid), and glial fibrillary acidic protein (GFAP) for neuroinflammation assessment.',
            'biomarkers' => array(
                'apoe_genotype', 'ptau_217', 'beta_amyloid_42_40_ratio', 'gfap'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw',
            'frequency' => 'Annually',
            'source' => 'Advanced Lab Partners'
        ),
        
        'protector_panel' => array(
            'name' => 'The Protector Panel',
            'display_name' => 'The Protector Panel',
            'description' => 'Advanced cardiovascular risk assessment',
            'price' => 149,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 4,
            'purpose' => 'Heart health and cardiovascular risk assessment',
            'long_description' => 'The Protector Panel provides advanced cardiovascular risk assessment beyond standard lipid testing, focusing on emerging biomarkers that offer superior predictive value for heart disease. This panel includes TMAO (trimethylamine N-oxide) for gut microbiome assessment, NMR LipoProfile® for detailed lipoprotein particle analysis, ferritin for iron status evaluation, and 1,5-anhydroglucitol for glycemic control assessment.',
            'biomarkers' => array(
                'tmao', 'nmr_lipoprofile', 'ferritin', '1_5_ag'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw',
            'frequency' => 'Quarterly',
            'source' => 'Advanced Lab Partners'
        ),
        
        'catalyst_panel' => array(
            'name' => 'The Catalyst Panel',
            'display_name' => 'The Catalyst Panel',
            'description' => 'Metabolic health optimization',
            'price' => 149,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 4,
            'purpose' => 'Metabolic health and energy optimization',
            'long_description' => 'The Catalyst Panel focuses on metabolic health and energy optimization, providing insights into insulin sensitivity, glucose metabolism, and metabolic efficiency. This panel includes fasting insulin for insulin resistance assessment, Glycomark for short-term glycemic control evaluation, uric acid for metabolic health and inflammation assessment, and adiponectin for fat metabolism and insulin sensitivity evaluation.',
            'biomarkers' => array(
                'insulin', 'glycomark', 'uric_acid', 'adiponectin'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw',
            'frequency' => 'Quarterly',
            'source' => 'Advanced Lab Partners'
        ),
        
        'detoxifier_panel' => array(
            'name' => 'The Detoxifier Panel',
            'display_name' => 'The Detoxifier Panel',
            'description' => 'Heavy metals and environmental toxins',
            'price' => 99,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 3,
            'purpose' => 'Toxicity assessment and detoxification guidance',
            'long_description' => 'The Detoxifier Panel assesses exposure to heavy metals and environmental toxins that can significantly impact health and longevity. This panel includes arsenic, lead, and mercury testing to identify potential toxic exposures that may be contributing to health issues or preventing optimal function.',
            'biomarkers' => array(
                'arsenic', 'lead', 'mercury'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw',
            'frequency' => 'Annually',
            'source' => 'Advanced Lab Partners'
        ),
        
        'timekeeper_panel' => array(
            'name' => 'The Timekeeper Panel',
            'display_name' => 'The Timekeeper Panel',
            'description' => 'Biological age and longevity assessment',
            'price' => 249,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 8,
            'purpose' => 'Biological age calculation and longevity optimization',
            'long_description' => 'The Timekeeper Panel provides comprehensive biological age assessment using advanced biomarkers that correlate with aging and longevity. This panel combines chronological data with biological markers to calculate your true biological age and identify areas for longevity optimization.',
            'biomarkers' => array(
                'chronological_age', 'gender', 'height', 'weight', 'systolic',
                'diastolic', 'fasting_glucose', 'hba1c'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Physical measurements + blood draw',
            'frequency' => 'Annually',
            'source' => 'ENNU Physical Assessment + Advanced Lab Partners'
        ),
        
        'hormone_optimization_panel' => array(
            'name' => 'Hormone Optimization Panel',
            'display_name' => 'Hormone Optimization Panel',
            'description' => 'Advanced hormonal health and optimization',
            'price' => 484,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 6,
            'purpose' => 'Advanced hormonal health and optimization',
            'long_description' => 'The Hormone Optimization Panel provides comprehensive hormonal assessment for both men and women, focusing on key hormones that impact energy, mood, libido, and overall vitality. This panel includes advanced hormone markers not covered in the Foundation Panel.',
            'biomarkers' => array(
                'estradiol_e2', 'progesterone', 'shbg', 'cortisol', 'free_t3', 'free_t4'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw',
            'frequency' => 'Quarterly',
            'source' => 'Advanced Lab Partners'
        ),
        
        'cardiovascular_health_panel' => array(
            'name' => 'Cardiovascular Health Panel',
            'display_name' => 'Cardiovascular Health Panel',
            'description' => 'Advanced cardiovascular risk assessment',
            'price' => 565,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 5,
            'purpose' => 'Comprehensive cardiovascular health assessment',
            'long_description' => 'The Cardiovascular Health Panel provides advanced cardiovascular risk assessment beyond standard lipid testing, including emerging biomarkers that offer superior predictive value for heart disease and cardiovascular health optimization.',
            'biomarkers' => array(
                'apob', 'hs_crp', 'homocysteine', 'lp_a', 'omega_3_index'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw',
            'frequency' => 'Quarterly',
            'source' => 'Advanced Lab Partners'
        ),
        
        'longevity_performance_panel' => array(
            'name' => 'Longevity & Performance Panel',
            'display_name' => 'Longevity & Performance Panel',
            'description' => 'Advanced longevity and performance biomarkers',
            'price' => 1234,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 10,
            'purpose' => 'Comprehensive longevity and performance assessment',
            'long_description' => 'The Longevity & Performance Panel provides the most comprehensive assessment of aging biomarkers and performance indicators, including telomere length, NAD+ levels, and advanced performance markers.',
            'biomarkers' => array(
                'telomere_length', 'nad_plus', 'tac', 'uric_acid', 'gut_microbiota_diversity',
                'mirna_486', 'creatine_kinase', 'il_6', 'grip_strength', 'il_18'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw + physical testing',
            'frequency' => 'Annually',
            'source' => 'Advanced Lab Partners'
        ),
        
        'cognitive_energy_panel' => array(
            'name' => 'Cognitive & Energy Panel',
            'display_name' => 'Cognitive & Energy Panel',
            'description' => 'Cognitive health and energy optimization',
            'price' => 486,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 5,
            'purpose' => 'Cognitive health and energy optimization',
            'long_description' => 'The Cognitive & Energy Panel focuses on brain health and energy optimization, including genetic risk assessment, energy production markers, and cognitive function indicators.',
            'biomarkers' => array(
                'apoe_genotype', 'coq10', 'heavy_metals_panel', 'ferritin', 'folate'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw',
            'frequency' => 'Quarterly',
            'source' => 'Advanced Lab Partners'
        ),
        
        'metabolic_optimization_panel' => array(
            'name' => 'Metabolic Optimization Panel',
            'display_name' => 'Metabolic Optimization Panel',
            'description' => 'Advanced metabolic health assessment',
            'price' => 376,
            'included_in_membership' => false,
            'membership_tier' => 'premium',
            'biomarker_count' => 4,
            'purpose' => 'Advanced metabolic health assessment',
            'long_description' => 'The Metabolic Optimization Panel provides comprehensive metabolic health assessment, including insulin sensitivity, metabolic efficiency, and appetite regulation markers.',
            'biomarkers' => array(
                'fasting_insulin', 'homa_ir', 'leptin', 'ghrelin'
            ),
            'required_for' => array('Premium Membership'),
            'collection_method' => 'Specialized blood draw',
            'frequency' => 'Quarterly',
            'source' => 'Advanced Lab Partners'
        )
    ),
    
    'pricing_summary' => array(
        'total_biomarkers' => 103,
        'total_value' => 4489,
        'foundation_panel_value' => 599,
        'additional_panels_value' => 3890,
        'membership_includes' => 'Foundation Panel',
        'membership_price' => 147
    ),
    
    'business_model' => array(
        'membership_tiers' => array(
            'foundation_only' => array(
                'name' => 'Foundation Only',
                'monthly_price' => 99,
                'annual_price' => 1188,
                'includes' => array('foundation_panel', 'ai_recommendations'),
                'target' => 'Data-focused customers'
            ),
            'complete' => array(
                'name' => 'Complete',
                'monthly_price' => 147,
                'annual_price' => 1764,
                'includes' => array('foundation_panel', 'consultation', 'ongoing_optimization'),
                'target' => 'Transformation-focused customers'
            ),
            'premium' => array(
                'name' => 'Premium',
                'monthly_price' => 197,
                'annual_price' => 2364,
                'includes' => array('foundation_panel', 'consultation', 'ongoing_optimization', '2_additional_panels'),
                'target' => 'High-value customers'
            )
        )
    )
); 