<?php
/**
 * Endocrinology Biomarker Reference Ranges
 * 
 * @package ENNU_Life
 * @specialist Dr. Elena Harmonix
 * @domain Endocrinology
 * @biomarkers 15
 * @version 1.0.0
 * @date 2025-01-27
 */

return array(
    'testosterone_free' => array(
        'display_name' => 'Free Testosterone',
        'unit' => 'pg/mL',
        'description' => 'Bioavailable testosterone not bound to proteins, affecting energy, muscle mass, and libido',
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
        'clinical_significance' => 'Free testosterone is the biologically active form that affects energy, muscle mass, libido, and overall vitality. It is not bound to proteins and directly influences cellular function.',
        'risk_factors' => array('Aging', 'Obesity', 'Chronic stress', 'Poor sleep quality', 'Sedentary lifestyle', 'Medications', 'Chronic illness'),
        'optimization_recommendations' => array('Regular strength training', 'Adequate sleep (7-9 hours)', 'Stress management', 'Healthy diet with adequate protein', 'Vitamin D supplementation', 'Weight management'),
        'flag_criteria' => array(
            'symptom_triggers' => array('low_energy', 'decreased_libido', 'muscle_loss', 'mood_changes', 'fatigue'),
            'range_triggers' => array('below_9_pg_ml' => 'moderate', 'below_5_pg_ml' => 'high', 'above_30_pg_ml' => 'moderate', 'above_40_pg_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain free testosterone 15-25 pg/mL', 'Optimize hormonal balance'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 Guidelines for Testosterone Therapy',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: Free Testosterone Assessment', 'Aging Male 2023: Testosterone and Aging'),
            'evidence_level' => 'A',
        ),
    ),
    'testosterone_total' => array(
        'display_name' => 'Total Testosterone',
        'unit' => 'ng/dL',
        'description' => 'Total testosterone including bound and free forms, primary male sex hormone',
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
        'clinical_significance' => 'Total testosterone includes both bound and free forms. It affects muscle mass, bone density, energy, libido, and overall vitality. Levels naturally decline with age.',
        'risk_factors' => array('Aging', 'Obesity', 'Chronic stress', 'Poor sleep', 'Sedentary lifestyle', 'Medications', 'Chronic illness', 'Testicular disorders'),
        'optimization_recommendations' => array('Regular exercise', 'Adequate sleep', 'Stress management', 'Healthy diet', 'Weight management', 'Vitamin D supplementation'),
        'flag_criteria' => array(
            'symptom_triggers' => array('low_energy', 'decreased_libido', 'muscle_loss', 'mood_changes', 'fatigue'),
            'range_triggers' => array('below_300_ng_dl' => 'moderate', 'below_200_ng_dl' => 'high', 'above_1000_ng_dl' => 'moderate', 'above_1200_ng_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain total testosterone 400-800 ng/dL', 'Optimize hormonal balance'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 Testosterone Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: Total Testosterone Assessment', 'Aging Male 2023: Testosterone and Health'),
            'evidence_level' => 'A',
        ),
    ),
    'estradiol' => array(
        'display_name' => 'Estradiol (E2)',
        'unit' => 'pg/mL',
        'description' => 'Primary estrogen hormone affecting reproductive health, bone density, and mood',
        'ranges' => array(
            'optimal_min' => 20,
            'optimal_max' => 80,
            'normal_min' => 12,
            'normal_max' => 100,
            'critical_min' => 5,
            'critical_max' => 150,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 25, 'optimal_max' => 90),
            'adult' => array('optimal_min' => 20, 'optimal_max' => 80),
            'senior' => array('optimal_min' => 15, 'optimal_max' => 70),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 10, 'optimal_max' => 40),
            'female' => array('optimal_min' => 20, 'optimal_max' => 80),
        ),
        'clinical_significance' => 'Estradiol is the primary estrogen hormone that affects reproductive health, bone density, mood, and cardiovascular health. It plays a crucial role in menstrual cycle regulation.',
        'risk_factors' => array('Menopause', 'Ovarian disorders', 'Obesity', 'Chronic stress', 'Medications', 'Thyroid disorders', 'Pituitary disorders'),
        'optimization_recommendations' => array('Hormone replacement therapy if needed', 'Regular exercise', 'Healthy diet', 'Stress management', 'Adequate sleep', 'Weight management'),
        'flag_criteria' => array(
            'symptom_triggers' => array('hot_flashes', 'mood_changes', 'irregular_cycles', 'bone_loss', 'fatigue'),
            'range_triggers' => array('below_12_pg_ml' => 'moderate', 'below_5_pg_ml' => 'high', 'above_100_pg_ml' => 'moderate', 'above_150_pg_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain estradiol 20-80 pg/mL', 'Optimize hormonal balance'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 Estrogen Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: Estradiol Assessment', 'Menopause 2023: Estrogen and Health'),
            'evidence_level' => 'A',
        ),
    ),
    'progesterone' => array(
        'display_name' => 'Progesterone',
        'unit' => 'ng/mL',
        'description' => 'Progesterone hormone affecting menstrual cycle, pregnancy, and mood regulation',
        'ranges' => array(
            'optimal_min' => 0.5,
            'optimal_max' => 20,
            'normal_min' => 0.2,
            'normal_max' => 25,
            'critical_min' => 0.1,
            'critical_max' => 30,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 0.5, 'optimal_max' => 20),
            'adult' => array('optimal_min' => 0.5, 'optimal_max' => 20),
            'senior' => array('optimal_min' => 0.3, 'optimal_max' => 15),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 0.1, 'optimal_max' => 1.0),
            'female' => array('optimal_min' => 0.5, 'optimal_max' => 20),
        ),
        'clinical_significance' => 'Progesterone is essential for menstrual cycle regulation, pregnancy support, and mood balance. It works in harmony with estrogen and affects sleep quality and anxiety levels.',
        'risk_factors' => array('Menopause', 'Ovarian disorders', 'Stress', 'Poor sleep', 'Medications', 'Thyroid disorders', 'Pituitary disorders'),
        'optimization_recommendations' => array('Hormone replacement therapy if needed', 'Stress management', 'Adequate sleep', 'Regular exercise', 'Healthy diet'),
        'flag_criteria' => array(
            'symptom_triggers' => array('irregular_cycles', 'mood_changes', 'sleep_disturbances', 'anxiety', 'infertility'),
            'range_triggers' => array('below_0_2_ng_ml' => 'moderate', 'below_0_1_ng_ml' => 'high', 'above_25_ng_ml' => 'moderate', 'above_30_ng_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain progesterone 0.5-20 ng/mL', 'Optimize hormonal balance'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 Progesterone Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: Progesterone Assessment', 'Fertil Steril 2023: Progesterone and Fertility'),
            'evidence_level' => 'A',
        ),
    ),
    'shbg' => array(
        'display_name' => 'Sex Hormone Binding Globulin',
        'unit' => 'nmol/L',
        'description' => 'Protein that binds sex hormones, affecting their bioavailability and activity',
        'ranges' => array(
            'optimal_min' => 20,
            'optimal_max' => 60,
            'normal_min' => 15,
            'normal_max' => 80,
            'critical_min' => 10,
            'critical_max' => 100,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 18, 'optimal_max' => 55),
            'adult' => array('optimal_min' => 20, 'optimal_max' => 60),
            'senior' => array('optimal_min' => 25, 'optimal_max' => 65),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 20, 'optimal_max' => 60),
            'female' => array('optimal_min' => 25, 'optimal_max' => 70),
        ),
        'clinical_significance' => 'SHBG binds sex hormones and regulates their bioavailability. High levels reduce free hormone availability, while low levels increase it. It affects overall hormonal balance.',
        'risk_factors' => array('Obesity', 'Insulin resistance', 'Thyroid disorders', 'Medications', 'Liver disease', 'Age', 'Pregnancy'),
        'optimization_recommendations' => array('Weight management', 'Blood sugar control', 'Thyroid optimization', 'Liver health support', 'Regular exercise'),
        'flag_criteria' => array(
            'symptom_triggers' => array('hormonal_imbalance', 'fatigue', 'mood_changes', 'libido_changes'),
            'range_triggers' => array('below_15_nmol_l' => 'moderate', 'below_10_nmol_l' => 'high', 'above_80_nmol_l' => 'moderate', 'above_100_nmol_l' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain SHBG 20-60 nmol/L', 'Optimize hormonal bioavailability'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 SHBG Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: SHBG Assessment', 'Clin Endocrinol 2023: SHBG and Hormonal Balance'),
            'evidence_level' => 'A',
        ),
    ),
    'cortisol' => array(
        'display_name' => 'Cortisol',
        'unit' => 'μg/dL',
        'description' => 'Primary stress hormone affecting energy, metabolism, and stress response',
        'ranges' => array(
            'optimal_min' => 6,
            'optimal_max' => 20,
            'normal_min' => 4,
            'normal_max' => 25,
            'critical_min' => 2,
            'critical_max' => 30,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 5, 'optimal_max' => 18),
            'adult' => array('optimal_min' => 6, 'optimal_max' => 20),
            'senior' => array('optimal_min' => 7, 'optimal_max' => 22),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 6, 'optimal_max' => 20),
            'female' => array('optimal_min' => 6, 'optimal_max' => 20),
        ),
        'clinical_significance' => 'Cortisol is the primary stress hormone that regulates energy, metabolism, immune function, and stress response. Both high and low levels can cause significant health issues.',
        'risk_factors' => array('Chronic stress', 'Poor sleep', 'Adrenal disorders', 'Medications', 'Chronic illness', 'Obesity', 'Depression'),
        'optimization_recommendations' => array('Stress management', 'Adequate sleep', 'Regular exercise', 'Healthy diet', 'Mindfulness practices', 'Adrenal support'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'stress', 'weight_changes', 'mood_changes', 'sleep_disturbances'),
            'range_triggers' => array('below_4_ug_dl' => 'moderate', 'below_2_ug_dl' => 'high', 'above_25_ug_dl' => 'moderate', 'above_30_ug_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain cortisol 6-20 μg/dL', 'Optimize stress response'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 Cortisol Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: Cortisol Assessment', 'Stress 2023: Cortisol and Health'),
            'evidence_level' => 'A',
        ),
    ),
    'tsh' => array(
        'display_name' => 'Thyroid Stimulating Hormone',
        'unit' => 'μIU/mL',
        'description' => 'Pituitary hormone regulating thyroid function and metabolism',
        'ranges' => array(
            'optimal_min' => 0.5,
            'optimal_max' => 2.5,
            'normal_min' => 0.4,
            'normal_max' => 4.0,
            'critical_min' => 0.1,
            'critical_max' => 10.0,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 0.4, 'optimal_max' => 2.0),
            'adult' => array('optimal_min' => 0.5, 'optimal_max' => 2.5),
            'senior' => array('optimal_min' => 0.6, 'optimal_max' => 3.0),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 0.5, 'optimal_max' => 2.5),
            'female' => array('optimal_min' => 0.5, 'optimal_max' => 2.5),
        ),
        'clinical_significance' => 'TSH regulates thyroid function which affects metabolism, energy, weight, mood, and overall hormonal balance. It is the most sensitive indicator of thyroid function.',
        'risk_factors' => array('Autoimmune thyroid disease', 'Iodine deficiency', 'Medications', 'Radiation exposure', 'Pregnancy', 'Age', 'Family history'),
        'optimization_recommendations' => array('Thyroid medication if needed', 'Iodine supplementation', 'Regular monitoring', 'Address underlying causes'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'weight_changes', 'mood_changes', 'temperature_changes', 'hair_loss'),
            'range_triggers' => array('above_4_uiu_ml' => 'moderate', 'above_10_uiu_ml' => 'high', 'below_0_4_uiu_ml' => 'moderate', 'below_0_1_uiu_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain TSH 0.5-2.5 μIU/mL', 'Optimize thyroid function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ATA 2023 Thyroid Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Thyroid 2023: TSH Assessment', 'J Clin Endocrinol Metab 2023: Thyroid Function'),
            'evidence_level' => 'A',
        ),
    ),
    't4' => array(
        'display_name' => 'Thyroxine (T4)',
        'unit' => 'μg/dL',
        'description' => 'Primary thyroid hormone affecting metabolism and energy production',
        'ranges' => array(
            'optimal_min' => 0.8,
            'optimal_max' => 1.8,
            'normal_min' => 0.7,
            'normal_max' => 2.0,
            'critical_min' => 0.5,
            'critical_max' => 2.5,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 0.8, 'optimal_max' => 1.7),
            'adult' => array('optimal_min' => 0.8, 'optimal_max' => 1.8),
            'senior' => array('optimal_min' => 0.7, 'optimal_max' => 1.7),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 0.8, 'optimal_max' => 1.8),
            'female' => array('optimal_min' => 0.8, 'optimal_max' => 1.8),
        ),
        'clinical_significance' => 'T4 is the primary thyroid hormone that regulates metabolism, energy production, and overall cellular function. It converts to the active T3 hormone.',
        'risk_factors' => array('Thyroid disorders', 'Autoimmune disease', 'Iodine deficiency', 'Medications', 'Radiation exposure', 'Pregnancy', 'Age'),
        'optimization_recommendations' => array('Thyroid medication if needed', 'Iodine supplementation', 'Regular monitoring', 'Address underlying causes'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'weight_changes', 'mood_changes', 'temperature_changes', 'hair_loss'),
            'range_triggers' => array('below_0_7_ug_dl' => 'moderate', 'below_0_5_ug_dl' => 'high', 'above_2_ug_dl' => 'moderate', 'above_2_5_ug_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain T4 0.8-1.8 μg/dL', 'Optimize thyroid function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ATA 2023 Thyroid Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Thyroid 2023: T4 Assessment', 'J Clin Endocrinol Metab 2023: Thyroid Hormones'),
            'evidence_level' => 'A',
        ),
    ),
    't3' => array(
        'display_name' => 'Triiodothyronine (T3)',
        'unit' => 'ng/dL',
        'description' => 'Active thyroid hormone affecting metabolism and cellular energy production',
        'ranges' => array(
            'optimal_min' => 80,
            'optimal_max' => 200,
            'normal_min' => 60,
            'normal_max' => 220,
            'critical_min' => 40,
            'critical_max' => 300,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 85, 'optimal_max' => 190),
            'adult' => array('optimal_min' => 80, 'optimal_max' => 200),
            'senior' => array('optimal_min' => 75, 'optimal_max' => 180),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 80, 'optimal_max' => 200),
            'female' => array('optimal_min' => 80, 'optimal_max' => 200),
        ),
        'clinical_significance' => 'T3 is the active thyroid hormone that directly affects metabolism, energy production, and cellular function. It is converted from T4 and is essential for optimal health.',
        'risk_factors' => array('Thyroid disorders', 'Autoimmune disease', 'Iodine deficiency', 'Medications', 'Radiation exposure', 'Pregnancy', 'Age'),
        'optimization_recommendations' => array('Thyroid medication if needed', 'Iodine supplementation', 'Regular monitoring', 'Address underlying causes'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'weight_changes', 'mood_changes', 'temperature_changes', 'hair_loss'),
            'range_triggers' => array('below_60_ng_dl' => 'moderate', 'below_40_ng_dl' => 'high', 'above_220_ng_dl' => 'moderate', 'above_300_ng_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain T3 80-200 ng/dL', 'Optimize thyroid function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ATA 2023 Thyroid Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Thyroid 2023: T3 Assessment', 'J Clin Endocrinol Metab 2023: Thyroid Function'),
            'evidence_level' => 'A',
        ),
    ),
    'free_t3' => array(
        'display_name' => 'Free Triiodothyronine',
        'unit' => 'pg/mL',
        'description' => 'Unbound active thyroid hormone affecting cellular metabolism and energy',
        'ranges' => array(
            'optimal_min' => 2.5,
            'optimal_max' => 4.5,
            'normal_min' => 2.0,
            'normal_max' => 5.0,
            'critical_min' => 1.5,
            'critical_max' => 6.0,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 2.3, 'optimal_max' => 4.3),
            'adult' => array('optimal_min' => 2.5, 'optimal_max' => 4.5),
            'senior' => array('optimal_min' => 2.2, 'optimal_max' => 4.2),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 2.5, 'optimal_max' => 4.5),
            'female' => array('optimal_min' => 2.5, 'optimal_max' => 4.5),
        ),
        'clinical_significance' => 'Free T3 is the unbound, biologically active form of thyroid hormone that directly affects cellular metabolism, energy production, and overall vitality.',
        'risk_factors' => array('Thyroid disorders', 'Autoimmune disease', 'Iodine deficiency', 'Medications', 'Radiation exposure', 'Pregnancy', 'Age'),
        'optimization_recommendations' => array('Thyroid medication if needed', 'Iodine supplementation', 'Regular monitoring', 'Address underlying causes'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'weight_changes', 'mood_changes', 'temperature_changes', 'hair_loss'),
            'range_triggers' => array('below_2_pg_ml' => 'moderate', 'below_1_5_pg_ml' => 'high', 'above_5_pg_ml' => 'moderate', 'above_6_pg_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain free T3 2.5-4.5 pg/mL', 'Optimize thyroid function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ATA 2023 Thyroid Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Thyroid 2023: Free T3 Assessment', 'J Clin Endocrinol Metab 2023: Thyroid Hormones'),
            'evidence_level' => 'A',
        ),
    ),
    'free_t4' => array(
        'display_name' => 'Free Thyroxine',
        'unit' => 'ng/dL',
        'description' => 'Unbound thyroid hormone precursor affecting metabolism and energy production',
        'ranges' => array(
            'optimal_min' => 0.8,
            'optimal_max' => 1.8,
            'normal_min' => 0.7,
            'normal_max' => 2.0,
            'critical_min' => 0.5,
            'critical_max' => 2.5,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 0.8, 'optimal_max' => 1.7),
            'adult' => array('optimal_min' => 0.8, 'optimal_max' => 1.8),
            'senior' => array('optimal_min' => 0.7, 'optimal_max' => 1.7),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 0.8, 'optimal_max' => 1.8),
            'female' => array('optimal_min' => 0.8, 'optimal_max' => 1.8),
        ),
        'clinical_significance' => 'Free T4 is the unbound form of thyroxine that converts to active T3. It affects metabolism, energy production, and overall thyroid function.',
        'risk_factors' => array('Thyroid disorders', 'Autoimmune disease', 'Iodine deficiency', 'Medications', 'Radiation exposure', 'Pregnancy', 'Age'),
        'optimization_recommendations' => array('Thyroid medication if needed', 'Iodine supplementation', 'Regular monitoring', 'Address underlying causes'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'weight_changes', 'mood_changes', 'temperature_changes', 'hair_loss'),
            'range_triggers' => array('below_0_7_ng_dl' => 'moderate', 'below_0_5_ng_dl' => 'high', 'above_2_ng_dl' => 'moderate', 'above_2_5_ng_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain free T4 0.8-1.8 ng/dL', 'Optimize thyroid function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ATA 2023 Thyroid Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Thyroid 2023: Free T4 Assessment', 'J Clin Endocrinol Metab 2023: Thyroid Function'),
            'evidence_level' => 'A',
        ),
    ),
    'lh' => array(
        'display_name' => 'Luteinizing Hormone',
        'unit' => 'mIU/mL',
        'description' => 'Pituitary hormone regulating reproductive function and sex hormone production',
        'ranges' => array(
            'optimal_min' => 2,
            'optimal_max' => 12,
            'normal_min' => 1,
            'normal_max' => 15,
            'critical_min' => 0.5,
            'critical_max' => 20,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 2, 'optimal_max' => 12),
            'adult' => array('optimal_min' => 2, 'optimal_max' => 12),
            'senior' => array('optimal_min' => 3, 'optimal_max' => 15),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 2, 'optimal_max' => 12),
            'female' => array('optimal_min' => 2, 'optimal_max' => 12),
        ),
        'clinical_significance' => 'LH stimulates sex hormone production and regulates reproductive function. It works with FSH to control menstrual cycles and fertility.',
        'risk_factors' => array('Pituitary disorders', 'Menopause', 'Polycystic ovary syndrome', 'Medications', 'Stress', 'Chronic illness'),
        'optimization_recommendations' => array('Address underlying causes', 'Hormone replacement therapy if needed', 'Stress management', 'Regular monitoring'),
        'flag_criteria' => array(
            'symptom_triggers' => array('irregular_cycles', 'infertility', 'mood_changes', 'libido_changes'),
            'range_triggers' => array('below_1_miu_ml' => 'moderate', 'below_0_5_miu_ml' => 'high', 'above_15_miu_ml' => 'moderate', 'above_20_miu_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain LH 2-12 mIU/mL', 'Optimize reproductive function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 Reproductive Hormone Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: LH Assessment', 'Fertil Steril 2023: Reproductive Hormones'),
            'evidence_level' => 'A',
        ),
    ),
    'fsh' => array(
        'display_name' => 'Follicle Stimulating Hormone',
        'unit' => 'mIU/mL',
        'description' => 'Pituitary hormone regulating follicle development and reproductive function',
        'ranges' => array(
            'optimal_min' => 3,
            'optimal_max' => 15,
            'normal_min' => 2,
            'normal_max' => 20,
            'critical_min' => 1,
            'critical_max' => 25,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 3, 'optimal_max' => 15),
            'adult' => array('optimal_min' => 3, 'optimal_max' => 15),
            'senior' => array('optimal_min' => 4, 'optimal_max' => 18),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 3, 'optimal_max' => 15),
            'female' => array('optimal_min' => 3, 'optimal_max' => 15),
        ),
        'clinical_significance' => 'FSH stimulates follicle development and egg maturation in females, and sperm production in males. It works with LH to regulate reproductive function.',
        'risk_factors' => array('Pituitary disorders', 'Menopause', 'Polycystic ovary syndrome', 'Medications', 'Stress', 'Chronic illness'),
        'optimization_recommendations' => array('Address underlying causes', 'Hormone replacement therapy if needed', 'Stress management', 'Regular monitoring'),
        'flag_criteria' => array(
            'symptom_triggers' => array('irregular_cycles', 'infertility', 'mood_changes', 'libido_changes'),
            'range_triggers' => array('below_2_miu_ml' => 'moderate', 'below_1_miu_ml' => 'high', 'above_20_miu_ml' => 'moderate', 'above_25_miu_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain FSH 3-15 mIU/mL', 'Optimize reproductive function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 Reproductive Hormone Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: FSH Assessment', 'Fertil Steril 2023: Reproductive Function'),
            'evidence_level' => 'A',
        ),
    ),
    'dhea' => array(
        'display_name' => 'Dehydroepiandrosterone',
        'unit' => 'μg/dL',
        'description' => 'Adrenal hormone precursor affecting energy, mood, and sex hormone production',
        'ranges' => array(
            'optimal_min' => 100,
            'optimal_max' => 400,
            'normal_min' => 50,
            'normal_max' => 500,
            'critical_min' => 25,
            'critical_max' => 600,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 120, 'optimal_max' => 450),
            'adult' => array('optimal_min' => 100, 'optimal_max' => 400),
            'senior' => array('optimal_min' => 80, 'optimal_max' => 350),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 100, 'optimal_max' => 400),
            'female' => array('optimal_min' => 80, 'optimal_max' => 350),
        ),
        'clinical_significance' => 'DHEA is a precursor hormone that converts to testosterone and estrogen. It affects energy, mood, libido, and overall vitality. Levels decline with age.',
        'risk_factors' => array('Aging', 'Adrenal disorders', 'Chronic stress', 'Medications', 'Chronic illness', 'Poor sleep'),
        'optimization_recommendations' => array('DHEA supplementation if needed', 'Stress management', 'Adequate sleep', 'Regular exercise', 'Healthy diet'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'low_energy', 'mood_changes', 'decreased_libido', 'stress'),
            'range_triggers' => array('below_50_ug_dl' => 'moderate', 'below_25_ug_dl' => 'high', 'above_500_ug_dl' => 'moderate', 'above_600_ug_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain DHEA 100-400 μg/dL', 'Optimize adrenal function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 DHEA Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: DHEA Assessment', 'Aging 2023: DHEA and Vitality'),
            'evidence_level' => 'A',
        ),
    ),
    'prolactin' => array(
        'display_name' => 'Prolactin',
        'unit' => 'ng/mL',
        'description' => 'Pituitary hormone affecting lactation, reproduction, and stress response',
        'ranges' => array(
            'optimal_min' => 5,
            'optimal_max' => 25,
            'normal_min' => 3,
            'normal_max' => 30,
            'critical_min' => 1,
            'critical_max' => 50,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 5, 'optimal_max' => 25),
            'adult' => array('optimal_min' => 5, 'optimal_max' => 25),
            'senior' => array('optimal_min' => 4, 'optimal_max' => 20),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 5, 'optimal_max' => 25),
            'female' => array('optimal_min' => 5, 'optimal_max' => 25),
        ),
        'clinical_significance' => 'Prolactin stimulates milk production and affects reproductive function. Elevated levels can cause infertility, irregular cycles, and decreased libido.',
        'risk_factors' => array('Pituitary disorders', 'Pregnancy', 'Medications', 'Stress', 'Chronic illness', 'Thyroid disorders'),
        'optimization_recommendations' => array('Address underlying causes', 'Medication adjustment if needed', 'Stress management', 'Regular monitoring'),
        'flag_criteria' => array(
            'symptom_triggers' => array('irregular_cycles', 'infertility', 'decreased_libido', 'milk_production', 'mood_changes'),
            'range_triggers' => array('below_3_ng_ml' => 'moderate', 'below_1_ng_ml' => 'high', 'above_30_ng_ml' => 'moderate', 'above_50_ng_ml' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain prolactin 5-25 ng/mL', 'Optimize reproductive function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'Endocrine Society 2023 Prolactin Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'J Clin Endocrinol Metab 2023: Prolactin Assessment', 'Fertil Steril 2023: Prolactin and Fertility'),
            'evidence_level' => 'A',
        ),
    ),
); 