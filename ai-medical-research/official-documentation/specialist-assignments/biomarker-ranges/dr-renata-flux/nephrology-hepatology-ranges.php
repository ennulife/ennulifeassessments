<?php
/**
 * Nephrology/Hepatology Biomarker Reference Ranges
 * 
 * @package ENNU_Life
 * @specialist Dr. Renata Flux
 * @domain Nephrology/Hepatology
 * @biomarkers 7
 * @version 1.0.0
 * @date 2025-01-27
 */

return array(
    'gfr' => array(
        'display_name' => 'Glomerular Filtration Rate',
        'unit' => 'mL/min/1.73m²',
        'description' => 'Estimates kidney function and filtration capacity, the gold standard for renal assessment',
        'ranges' => array(
            'optimal_min' => 90,
            'optimal_max' => 120,
            'normal_min' => 60,
            'normal_max' => 120,
            'critical_min' => 15,
            'critical_max' => 120,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 100, 'optimal_max' => 120),
            'adult' => array('optimal_min' => 90, 'optimal_max' => 120),
            'senior' => array('optimal_min' => 70, 'optimal_max' => 110),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 90, 'optimal_max' => 120),
            'female' => array('optimal_min' => 85, 'optimal_max' => 115),
        ),
        'clinical_significance' => 'GFR is the gold standard for assessing kidney function and filtration capacity. It reflects the kidneys\' ability to filter waste products from the blood.',
        'risk_factors' => array('Chronic kidney disease', 'Diabetes', 'Hypertension', 'Family history of kidney disease', 'Nephrotoxic medications', 'Aging'),
        'optimization_recommendations' => array('Control blood pressure', 'Manage blood sugar', 'Avoid nephrotoxic drugs', 'Stay hydrated', 'Monitor kidney function regularly'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'swelling', 'decreased_urine_output', 'nausea', 'weakness'),
            'range_triggers' => array('below_60_ml_min' => 'moderate', 'below_30_ml_min' => 'high', 'above_120_ml_min' => 'moderate'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain GFR 90-120 mL/min/1.73m²', 'Optimize kidney function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'NKF 2023 Guidelines for CKD',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Kidney Int 2023: GFR and Renal Function', 'AASLD 2023: Liver and Kidney Biomarkers'),
            'evidence_level' => 'A',
        ),
    ),
    'bun' => array(
        'display_name' => 'Blood Urea Nitrogen',
        'unit' => 'mg/dL',
        'description' => 'Kidney function marker indicating protein metabolism and waste clearance',
        'ranges' => array(
            'optimal_min' => 7,
            'optimal_max' => 20,
            'normal_min' => 7,
            'normal_max' => 25,
            'critical_min' => 5,
            'critical_max' => 40,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 7, 'optimal_max' => 18),
            'adult' => array('optimal_min' => 7, 'optimal_max' => 20),
            'senior' => array('optimal_min' => 8, 'optimal_max' => 22),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 7, 'optimal_max' => 20),
            'female' => array('optimal_min' => 7, 'optimal_max' => 20),
        ),
        'clinical_significance' => 'BUN reflects kidney function and protein metabolism. Elevated levels indicate kidney dysfunction, dehydration, or high protein intake.',
        'risk_factors' => array('Kidney disease', 'Dehydration', 'High protein diet', 'Medications', 'Chronic illness', 'Aging'),
        'optimization_recommendations' => array('Adequate hydration', 'Balanced protein intake', 'Kidney-healthy diet', 'Regular monitoring', 'Medical consultation if elevated'),
        'flag_criteria' => array(
            'symptom_triggers' => array('kidney_issues', 'dehydration', 'fatigue', 'toxin_buildup', 'nausea'),
            'range_triggers' => array('above_25_mg_dl' => 'moderate', 'above_40_mg_dl' => 'high', 'below_7_mg_dl' => 'moderate', 'below_5_mg_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain BUN 7-20 mg/dL', 'Optimize kidney function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'NKF 2023 Kidney Function Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Am Soc Nephrol 2023: BUN Assessment', 'Kidney Int 2023: Kidney Biomarkers'),
            'evidence_level' => 'A',
        ),
    ),
    'creatinine' => array(
        'display_name' => 'Creatinine',
        'unit' => 'mg/dL',
        'description' => 'Muscle metabolism byproduct indicating kidney function and muscle mass',
        'ranges' => array(
            'optimal_min' => 0.6,
            'optimal_max' => 1.2,
            'normal_min' => 0.6,
            'normal_max' => 1.3,
            'critical_min' => 0.5,
            'critical_max' => 2.0,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 0.6, 'optimal_max' => 1.2),
            'adult' => array('optimal_min' => 0.6, 'optimal_max' => 1.2),
            'senior' => array('optimal_min' => 0.7, 'optimal_max' => 1.3),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 0.7, 'optimal_max' => 1.3),
            'female' => array('optimal_min' => 0.6, 'optimal_max' => 1.1),
        ),
        'clinical_significance' => 'Creatinine reflects kidney function and muscle mass. Elevated levels indicate kidney dysfunction, while low levels may indicate muscle loss.',
        'risk_factors' => array('Kidney disease', 'Muscle loss', 'Dehydration', 'Medications', 'Chronic illness', 'Aging'),
        'optimization_recommendations' => array('Regular exercise', 'Adequate protein intake', 'Hydration', 'Kidney-healthy diet', 'Regular monitoring'),
        'flag_criteria' => array(
            'symptom_triggers' => array('kidney_issues', 'muscle_loss', 'fatigue', 'weakness', 'swelling'),
            'range_triggers' => array('above_1_3_mg_dl' => 'moderate', 'above_2_mg_dl' => 'high', 'below_0_6_mg_dl' => 'moderate', 'below_0_5_mg_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain creatinine 0.6-1.2 mg/dL', 'Optimize kidney function and muscle mass'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'NKF 2023 Kidney Function Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Am Soc Nephrol 2023: Creatinine Assessment', 'Kidney Int 2023: Kidney Health'),
            'evidence_level' => 'A',
        ),
    ),
    'alt' => array(
        'display_name' => 'Alanine Aminotransferase',
        'unit' => 'U/L',
        'description' => 'Liver enzyme indicating liver cell damage and function',
        'ranges' => array(
            'optimal_min' => 7,
            'optimal_max' => 35,
            'normal_min' => 7,
            'normal_max' => 55,
            'critical_min' => 5,
            'critical_max' => 100,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 7, 'optimal_max' => 35),
            'adult' => array('optimal_min' => 7, 'optimal_max' => 35),
            'senior' => array('optimal_min' => 8, 'optimal_max' => 40),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 7, 'optimal_max' => 35),
            'female' => array('optimal_min' => 7, 'optimal_max' => 35),
        ),
        'clinical_significance' => 'ALT is a liver-specific enzyme that indicates liver cell damage. Elevated levels suggest liver injury, inflammation, or disease.',
        'risk_factors' => array('Alcohol consumption', 'Obesity', 'Medications', 'Viral hepatitis', 'Fatty liver disease', 'Toxins'),
        'optimization_recommendations' => array('Limit alcohol', 'Maintain healthy weight', 'Liver-healthy diet', 'Avoid hepatotoxic medications', 'Regular monitoring'),
        'flag_criteria' => array(
            'symptom_triggers' => array('liver_issues', 'fatigue', 'abdominal_pain', 'jaundice', 'nausea'),
            'range_triggers' => array('above_55_u_l' => 'moderate', 'above_100_u_l' => 'high', 'below_7_u_l' => 'moderate', 'below_5_u_l' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain ALT 7-35 U/L', 'Optimize liver function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'AASLD 2023 Liver Function Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Hepatology 2023: ALT Assessment', 'J Hepatol 2023: Liver Enzymes'),
            'evidence_level' => 'A',
        ),
    ),
    'ast' => array(
        'display_name' => 'Aspartate Aminotransferase',
        'unit' => 'U/L',
        'description' => 'Liver and muscle enzyme indicating tissue damage and function',
        'ranges' => array(
            'optimal_min' => 8,
            'optimal_max' => 40,
            'normal_min' => 8,
            'normal_max' => 60,
            'critical_min' => 5,
            'critical_max' => 120,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 8, 'optimal_max' => 40),
            'adult' => array('optimal_min' => 8, 'optimal_max' => 40),
            'senior' => array('optimal_min' => 10, 'optimal_max' => 45),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 8, 'optimal_max' => 40),
            'female' => array('optimal_min' => 8, 'optimal_max' => 40),
        ),
        'clinical_significance' => 'AST is found in liver, heart, and muscle cells. Elevated levels indicate tissue damage, particularly liver injury or muscle damage.',
        'risk_factors' => array('Alcohol consumption', 'Obesity', 'Medications', 'Viral hepatitis', 'Muscle injury', 'Heart disease'),
        'optimization_recommendations' => array('Limit alcohol', 'Maintain healthy weight', 'Liver-healthy diet', 'Avoid hepatotoxic medications', 'Regular monitoring'),
        'flag_criteria' => array(
            'symptom_triggers' => array('liver_issues', 'muscle_pain', 'fatigue', 'abdominal_pain', 'chest_pain'),
            'range_triggers' => array('above_60_u_l' => 'moderate', 'above_120_u_l' => 'high', 'below_8_u_l' => 'moderate', 'below_5_u_l' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain AST 8-40 U/L', 'Optimize liver and tissue function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'AASLD 2023 Liver Function Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Hepatology 2023: AST Assessment', 'J Hepatol 2023: Liver Biomarkers'),
            'evidence_level' => 'A',
        ),
    ),
    'alkaline_phosphate' => array(
        'display_name' => 'Alkaline Phosphatase',
        'unit' => 'U/L',
        'description' => 'Enzyme indicating liver, bone, and bile duct function',
        'ranges' => array(
            'optimal_min' => 44,
            'optimal_max' => 147,
            'normal_min' => 44,
            'normal_max' => 147,
            'critical_min' => 30,
            'critical_max' => 200,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 44, 'optimal_max' => 147),
            'adult' => array('optimal_min' => 44, 'optimal_max' => 147),
            'senior' => array('optimal_min' => 50, 'optimal_max' => 160),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 44, 'optimal_max' => 147),
            'female' => array('optimal_min' => 44, 'optimal_max' => 147),
        ),
        'clinical_significance' => 'Alkaline phosphatase is found in liver, bones, and bile ducts. Elevated levels can indicate liver disease, bone disorders, or bile duct obstruction.',
        'risk_factors' => array('Liver disease', 'Bone disorders', 'Bile duct obstruction', 'Medications', 'Pregnancy', 'Aging'),
        'optimization_recommendations' => array('Liver-healthy diet', 'Bone health maintenance', 'Regular monitoring', 'Medical consultation if elevated', 'Avoid hepatotoxic medications'),
        'flag_criteria' => array(
            'symptom_triggers' => array('liver_issues', 'bone_pain', 'abdominal_pain', 'jaundice', 'fatigue'),
            'range_triggers' => array('above_147_u_l' => 'moderate', 'above_200_u_l' => 'high', 'below_44_u_l' => 'moderate', 'below_30_u_l' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain ALP 44-147 U/L', 'Optimize liver and bone health'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'AASLD 2023 Liver Function Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Hepatology 2023: ALP Assessment', 'J Hepatol 2023: Liver Enzymes'),
            'evidence_level' => 'A',
        ),
    ),
    'albumin' => array(
        'display_name' => 'Albumin',
        'unit' => 'g/dL',
        'description' => 'Liver-produced protein indicating liver function and nutritional status',
        'ranges' => array(
            'optimal_min' => 3.5,
            'optimal_max' => 5.0,
            'normal_min' => 3.4,
            'normal_max' => 5.4,
            'critical_min' => 2.8,
            'critical_max' => 6.0,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 3.5, 'optimal_max' => 5.0),
            'adult' => array('optimal_min' => 3.5, 'optimal_max' => 5.0),
            'senior' => array('optimal_min' => 3.2, 'optimal_max' => 4.8),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 3.5, 'optimal_max' => 5.0),
            'female' => array('optimal_min' => 3.5, 'optimal_max' => 5.0),
        ),
        'clinical_significance' => 'Albumin is the main protein produced by the liver. Low levels indicate liver dysfunction, malnutrition, or protein loss. High levels suggest dehydration.',
        'risk_factors' => array('Liver disease', 'Malnutrition', 'Protein loss', 'Chronic illness', 'Dehydration', 'Aging'),
        'optimization_recommendations' => array('Adequate protein intake', 'Liver-healthy diet', 'Stay hydrated', 'Regular monitoring', 'Medical consultation if low'),
        'flag_criteria' => array(
            'symptom_triggers' => array('liver_issues', 'malnutrition', 'swelling', 'fatigue', 'weakness'),
            'range_triggers' => array('below_3_4_g_dl' => 'moderate', 'below_2_8_g_dl' => 'high', 'above_5_4_g_dl' => 'moderate', 'above_6_g_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain albumin 3.5-5.0 g/dL', 'Optimize liver function and nutrition'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'AASLD 2023 Liver Function Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Hepatology 2023: Albumin Assessment', 'J Hepatol 2023: Liver Proteins'),
            'evidence_level' => 'A',
        ),
    ),
); 