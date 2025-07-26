<?php
/**
 * Sports Medicine Biomarker Reference Ranges
 * 
 * @package ENNU_Life
 * @specialist Dr. Silas Apex
 * @domain Sports Medicine
 * @biomarkers 8
 * @version 1.0.0
 * @date 2025-01-27
 */

return array(
    'testosterone_free' => array(
        'display_name' => 'Free Testosterone',
        'unit' => 'pg/mL',
        'description' => 'Bioavailable testosterone affecting muscle mass, strength, and athletic performance',
        'ranges' => array(
            'optimal_min' => 15,
            'optimal_max' => 25,
            'normal_min' => 9,
            'normal_max' => 30,
            'critical_min' => 5,
            'critical_max' => 40,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 18, 'optimal_max' => 28),
            'adult' => array('optimal_min' => 15, 'optimal_max' => 25),
            'senior' => array('optimal_min' => 12, 'optimal_max' => 22),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 15, 'optimal_max' => 25),
            'female' => array('optimal_min' => 1.5, 'optimal_max' => 2.5),
        ),
        'clinical_significance' => 'Free testosterone is critical for muscle protein synthesis, strength gains, and athletic performance. It directly influences training adaptation and recovery.',
        'risk_factors' => array('Overtraining', 'Inadequate recovery', 'Poor sleep quality', 'Chronic stress', 'Inadequate nutrition', 'Age-related decline'),
        'optimization_recommendations' => array('Progressive resistance training', 'Adequate protein intake (1.6-2.2g/kg)', 'Quality sleep (7-9 hours)', 'Stress management', 'Balanced training program'),
        'flag_criteria' => array(
            'symptom_triggers' => array('decreased_performance', 'muscle_loss', 'fatigue', 'decreased_libido', 'reduced_strength'),
            'range_triggers' => array('below_9_pg_ml' => 'moderate', 'below_5_pg_ml' => 'high', 'above_30_pg_ml' => 'moderate', 'above_40_pg_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain free testosterone 15-25 pg/mL', 'Optimize strength and performance'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ACSM 2023 Guidelines for Exercise Testing and Prescription',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Sports Med 2023: Testosterone and Performance', 'J Strength Cond Res 2023: Hormonal Biomarkers'),
            'evidence_level' => 'A',
        ),
    ),
    'testosterone_total' => array(
        'display_name' => 'Total Testosterone',
        'unit' => 'ng/dL',
        'description' => 'Total testosterone levels including bound and free forms affecting overall performance',
        'ranges' => array(
            'optimal_min' => 400,
            'optimal_max' => 800,
            'normal_min' => 300,
            'normal_max' => 1000,
            'critical_min' => 200,
            'critical_max' => 1200,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 450, 'optimal_max' => 850),
            'adult' => array('optimal_min' => 400, 'optimal_max' => 800),
            'senior' => array('optimal_min' => 350, 'optimal_max' => 750),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 400, 'optimal_max' => 800),
            'female' => array('optimal_min' => 15, 'optimal_max' => 70),
        ),
        'clinical_significance' => 'Total testosterone reflects overall androgen status and is essential for muscle development, bone health, and athletic performance optimization.',
        'risk_factors' => array('Overtraining', 'Inadequate recovery', 'Poor sleep', 'Chronic stress', 'Nutritional deficiencies', 'Age-related decline'),
        'optimization_recommendations' => array('Strength training', 'Adequate protein intake', 'Quality sleep', 'Stress management', 'Vitamin D supplementation'),
        'flag_criteria' => array(
            'symptom_triggers' => array('decreased_performance', 'muscle_loss', 'fatigue', 'decreased_libido', 'reduced_strength'),
            'range_triggers' => array('below_300_ng_dl' => 'moderate', 'below_200_ng_dl' => 'high', 'above_1000_ng_dl' => 'moderate', 'above_1200_ng_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain total testosterone 400-800 ng/dL', 'Optimize hormonal balance for performance'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ACSM 2023 Hormonal Assessment Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Sports Med 2023: Total Testosterone Assessment', 'J Strength Cond Res 2023: Androgen Status'),
            'evidence_level' => 'A',
        ),
    ),
    'dhea' => array(
        'display_name' => 'Dehydroepiandrosterone',
        'unit' => 'μg/dL',
        'description' => 'Precursor hormone supporting energy, recovery, and overall performance',
        'ranges' => array(
            'optimal_min' => 100,
            'optimal_max' => 300,
            'normal_min' => 80,
            'normal_max' => 400,
            'critical_min' => 50,
            'critical_max' => 500,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 120, 'optimal_max' => 350),
            'adult' => array('optimal_min' => 100, 'optimal_max' => 300),
            'senior' => array('optimal_min' => 80, 'optimal_max' => 250),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 100, 'optimal_max' => 300),
            'female' => array('optimal_min' => 80, 'optimal_max' => 280),
        ),
        'clinical_significance' => 'DHEA is a precursor hormone that supports energy production, recovery, and overall performance. It declines with age and stress.',
        'risk_factors' => array('Chronic stress', 'Overtraining', 'Poor sleep', 'Age-related decline', 'Nutritional deficiencies', 'Chronic illness'),
        'optimization_recommendations' => array('Stress management', 'Adequate recovery', 'Quality sleep', 'Balanced nutrition', 'Regular exercise'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'poor_recovery', 'decreased_energy', 'stress_symptoms'),
            'range_triggers' => array('below_80_ug_dl' => 'moderate', 'below_50_ug_dl' => 'high', 'above_400_ug_dl' => 'moderate', 'above_500_ug_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain DHEA 100-300 μg/dL', 'Optimize energy and recovery'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ACSM 2023 Recovery Biomarker Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Sports Med 2023: DHEA Assessment', 'J Strength Cond Res 2023: Recovery Biomarkers'),
            'evidence_level' => 'A',
        ),
    ),
    'igf_1' => array(
        'display_name' => 'Insulin-like Growth Factor 1',
        'unit' => 'ng/mL',
        'description' => 'Growth factor essential for muscle growth, recovery, and performance optimization',
        'ranges' => array(
            'optimal_min' => 150,
            'optimal_max' => 300,
            'normal_min' => 100,
            'normal_max' => 350,
            'critical_min' => 80,
            'critical_max' => 400,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 180, 'optimal_max' => 350),
            'adult' => array('optimal_min' => 150, 'optimal_max' => 300),
            'senior' => array('optimal_min' => 120, 'optimal_max' => 250),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 150, 'optimal_max' => 300),
            'female' => array('optimal_min' => 140, 'optimal_max' => 280),
        ),
        'clinical_significance' => 'IGF-1 is essential for muscle protein synthesis, tissue repair, and performance optimization. It mediates the effects of growth hormone.',
        'risk_factors' => array('Poor nutrition', 'Inadequate protein intake', 'Overtraining', 'Poor sleep', 'Chronic stress', 'Age-related decline'),
        'optimization_recommendations' => array('Adequate protein intake', 'Quality sleep', 'Stress management', 'Regular exercise', 'Balanced nutrition'),
        'flag_criteria' => array(
            'symptom_triggers' => array('poor_recovery', 'muscle_loss', 'decreased_performance', 'fatigue'),
            'range_triggers' => array('below_100_ng_ml' => 'moderate', 'below_80_ng_ml' => 'high', 'above_350_ng_ml' => 'moderate', 'above_400_ng_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain IGF-1 150-300 ng/mL', 'Optimize muscle growth and recovery'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ACSM 2023 Growth Factor Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Sports Med 2023: IGF-1 Assessment', 'J Strength Cond Res 2023: Growth Factors'),
            'evidence_level' => 'A',
        ),
    ),
    'creatine_kinase' => array(
        'display_name' => 'Creatine Kinase',
        'unit' => 'U/L',
        'description' => 'Muscle enzyme indicating muscle damage and recovery status',
        'ranges' => array(
            'optimal_min' => 30,
            'optimal_max' => 200,
            'normal_min' => 20,
            'normal_max' => 300,
            'critical_min' => 10,
            'critical_max' => 500,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 30, 'optimal_max' => 200),
            'adult' => array('optimal_min' => 30, 'optimal_max' => 200),
            'senior' => array('optimal_min' => 35, 'optimal_max' => 250),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 30, 'optimal_max' => 200),
            'female' => array('optimal_min' => 25, 'optimal_max' => 180),
        ),
        'clinical_significance' => 'Creatine kinase indicates muscle damage and recovery status. Elevated levels suggest muscle stress requiring adequate recovery time.',
        'risk_factors' => array('Overtraining', 'Inadequate recovery', 'High-intensity exercise', 'Muscle injury', 'Dehydration', 'Poor nutrition'),
        'optimization_recommendations' => array('Adequate recovery time', 'Proper hydration', 'Anti-inflammatory nutrition', 'Gradual training progression', 'Rest days'),
        'flag_criteria' => array(
            'symptom_triggers' => array('muscle_soreness', 'fatigue', 'decreased_performance', 'muscle_pain'),
            'range_triggers' => array('above_300_u_l' => 'moderate', 'above_500_u_l' => 'high', 'below_20_u_l' => 'moderate', 'below_10_u_l' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain CK 30-200 U/L', 'Optimize recovery and prevent overtraining'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ACSM 2023 Muscle Damage Assessment Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Sports Med 2023: CK Assessment', 'J Strength Cond Res 2023: Muscle Damage'),
            'evidence_level' => 'A',
        ),
    ),
    'grip_strength' => array(
        'display_name' => 'Grip Strength',
        'unit' => 'kg',
        'description' => 'Hand grip strength indicating overall strength and functional capacity',
        'ranges' => array(
            'optimal_min' => 40,
            'optimal_max' => 80,
            'normal_min' => 30,
            'normal_max' => 90,
            'critical_min' => 20,
            'critical_max' => 100,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 45, 'optimal_max' => 85),
            'adult' => array('optimal_min' => 40, 'optimal_max' => 80),
            'senior' => array('optimal_min' => 35, 'optimal_max' => 70),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 45, 'optimal_max' => 85),
            'female' => array('optimal_min' => 30, 'optimal_max' => 60),
        ),
        'clinical_significance' => 'Grip strength is a reliable indicator of overall strength, functional capacity, and performance potential. It correlates with total body strength.',
        'risk_factors' => array('Sedentary lifestyle', 'Poor nutrition', 'Inadequate training', 'Age-related decline', 'Injury', 'Chronic illness'),
        'optimization_recommendations' => array('Progressive resistance training', 'Grip-specific exercises', 'Adequate protein intake', 'Regular strength training', 'Proper technique'),
        'flag_criteria' => array(
            'symptom_triggers' => array('weakness', 'decreased_performance', 'functional_limitations', 'fatigue'),
            'range_triggers' => array('below_30_kg' => 'moderate', 'below_20_kg' => 'high', 'above_90_kg' => 'moderate', 'above_100_kg' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain grip strength 40-80 kg', 'Optimize overall strength and function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ACSM 2023 Functional Assessment Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Sports Med 2023: Grip Strength Assessment', 'J Strength Cond Res 2023: Functional Strength'),
            'evidence_level' => 'A',
        ),
    ),
    'vitamin_d' => array(
        'display_name' => 'Vitamin D (25-OH)',
        'unit' => 'ng/mL',
        'description' => 'Vitamin D levels affecting muscle function, bone health, and performance',
        'ranges' => array(
            'optimal_min' => 30,
            'optimal_max' => 80,
            'normal_min' => 20,
            'normal_max' => 100,
            'critical_min' => 10,
            'critical_max' => 150,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 30, 'optimal_max' => 80),
            'adult' => array('optimal_min' => 30, 'optimal_max' => 80),
            'senior' => array('optimal_min' => 35, 'optimal_max' => 85),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 30, 'optimal_max' => 80),
            'female' => array('optimal_min' => 30, 'optimal_max' => 80),
        ),
        'clinical_significance' => 'Vitamin D is essential for muscle function, bone health, immune function, and athletic performance. Deficiency can impair strength and recovery.',
        'risk_factors' => array('Limited sun exposure', 'Dark skin', 'Obesity', 'Poor diet', 'Geographic location', 'Age'),
        'optimization_recommendations' => array('Sun exposure (15-20 minutes daily)', 'Vitamin D supplementation', 'Fatty fish consumption', 'Fortified foods', 'Regular testing'),
        'flag_criteria' => array(
            'symptom_triggers' => array('muscle_weakness', 'bone_pain', 'fatigue', 'decreased_performance'),
            'range_triggers' => array('below_20_ng_ml' => 'moderate', 'below_10_ng_ml' => 'high', 'above_100_ng_ml' => 'moderate', 'above_150_ng_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain vitamin D 30-80 ng/mL', 'Optimize muscle and bone health'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ACSM 2023 Vitamin D Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Sports Med 2023: Vitamin D Assessment', 'J Strength Cond Res 2023: Micronutrients'),
            'evidence_level' => 'A',
        ),
    ),
    'ferritin' => array(
        'display_name' => 'Ferritin',
        'unit' => 'ng/mL',
        'description' => 'Iron storage protein essential for oxygen transport and performance',
        'ranges' => array(
            'optimal_min' => 50,
            'optimal_max' => 200,
            'normal_min' => 30,
            'normal_max' => 300,
            'critical_min' => 15,
            'critical_max' => 400,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 50, 'optimal_max' => 200),
            'adult' => array('optimal_min' => 50, 'optimal_max' => 200),
            'senior' => array('optimal_min' => 60, 'optimal_max' => 250),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 50, 'optimal_max' => 200),
            'female' => array('optimal_min' => 40, 'optimal_max' => 180),
        ),
        'clinical_significance' => 'Ferritin indicates iron stores and is essential for oxygen transport, energy production, and athletic performance. Low levels can cause fatigue and decreased performance.',
        'risk_factors' => array('Iron deficiency', 'Poor diet', 'Blood loss', 'Intense training', 'Vegetarian diet', 'Chronic illness'),
        'optimization_recommendations' => array('Iron-rich foods', 'Vitamin C with iron', 'Iron supplementation if needed', 'Regular monitoring', 'Balanced diet'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'decreased_performance', 'shortness_of_breath', 'pale_skin'),
            'range_triggers' => array('below_30_ng_ml' => 'moderate', 'below_15_ng_ml' => 'high', 'above_300_ng_ml' => 'moderate', 'above_400_ng_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain ferritin 50-200 ng/mL', 'Optimize oxygen transport and energy'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ACSM 2023 Iron Status Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Sports Med 2023: Ferritin Assessment', 'J Strength Cond Res 2023: Iron and Performance'),
            'evidence_level' => 'A',
        ),
    ),
); 