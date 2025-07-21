<?php
/**
 * ENNU Life Business Model Configuration
 *
 * @package ENNU_Life
 * @version 62.1.17
 * @description Complete business model structure with membership tiers, addon packages, and pricing
 */

return array(
    'membership_tiers' => array(
        'basic' => array(
            'id' => 'ennu_life_basic',
            'name' => 'ENNU Life Basic Membership',
            'price' => 99,
            'billing' => 'monthly',
            'billing_cycle' => 'monthly',
            'biomarkers' => 'physical_measurements_only',
            'biomarker_count' => 8,
            'features' => array(
                'dashboard_access' => true,
                'basic_assessments' => true,
                'symptom_tracking' => true,
                'basic_recommendations' => true,
                'physical_measurements' => true,
                'monthly_health_reports' => true,
                'email_support' => true
            ),
            'description' => 'Essential health tracking with physical measurements and basic assessments',
            'target_audience' => 'Health-conscious individuals starting their wellness journey',
            'upgrade_path' => 'comprehensive_diagnostic',
            'cancellation_policy' => 'Cancel anytime with 30-day notice',
            'trial_period' => 14,
            'setup_fee' => 0
        ),
        'comprehensive_diagnostic' => array(
            'id' => 'ennu_life_comprehensive',
            'name' => 'ENNU Life Comprehensive Diagnostic',
            'price' => 599,
            'billing' => 'one_time',
            'billing_cycle' => 'one_time',
            'biomarkers' => 'all_ennu_core',
            'biomarker_count' => 50,
            'features' => array(
                'complete_lab_panel' => true,
                'comprehensive_health_report' => true,
                'personalized_recommendations' => true,
                'consultation_access' => true,
                'consultation_duration' => 30,
                'all_core_biomarkers' => true,
                'priority_support' => true,
                'detailed_health_analysis' => true
            ),
            'description' => 'Complete health snapshot with all 50 ENNU Life core biomarkers',
            'target_audience' => 'Individuals seeking comprehensive health assessment',
            'upgrade_path' => 'premium_membership',
            'cancellation_policy' => 'Non-refundable after lab draw',
            'trial_period' => 0,
            'setup_fee' => 0
        ),
        'premium' => array(
            'id' => 'ennu_life_premium',
            'name' => 'ENNU Life Premium Membership',
            'price' => 199,
            'billing' => 'monthly',
            'billing_cycle' => 'monthly',
            'biomarkers' => 'all_ennu_core',
            'biomarker_count' => 50,
            'features' => array(
                'quarterly_lab_updates' => true,
                'advanced_dashboard_features' => true,
                'priority_consultation_access' => true,
                'custom_health_optimization_plans' => true,
                'all_core_biomarkers' => true,
                'advanced_analytics' => true,
                'dedicated_health_coach' => true,
                'monthly_consultation_credits' => 1,
                'priority_support' => true,
                'progress_tracking' => true
            ),
            'description' => 'Ongoing health optimization with quarterly updates and dedicated support',
            'target_audience' => 'Individuals committed to long-term health optimization',
            'upgrade_path' => 'addon_packages',
            'cancellation_policy' => 'Cancel anytime with 30-day notice',
            'trial_period' => 14,
            'setup_fee' => 0
        )
    ),
    
    'addon_packages' => array(
        'hormone_optimization' => array(
            'id' => 'hormone_optimization_package',
            'name' => 'Hormone Optimization Package',
            'price' => 399,
            'billing' => 'one_time',
            'biomarkers' => array('estradiol_e2', 'progesterone', 'shbg', 'cortisol', 'free_t3', 'free_t4'),
            'biomarker_count' => 6,
            'description' => 'Complete hormone optimization panel for comprehensive hormonal health assessment',
            'target_audience' => 'Individuals with hormonal imbalances or optimization goals',
            'features' => array(
                'comprehensive_hormone_panel' => true,
                'hormone_optimization_plan' => true,
                'consultation_with_hormone_specialist' => true,
                'detailed_hormone_analysis' => true,
                'follow_up_recommendations' => true
            ),
            'consultation_included' => true,
            'consultation_duration' => 45,
            'validity_period' => 365,
            'prerequisites' => array('comprehensive_diagnostic', 'premium_membership')
        ),
        'cardiovascular_health' => array(
            'id' => 'cardiovascular_health_package',
            'name' => 'Cardiovascular Health Package',
            'price' => 399,
            'billing' => 'one_time',
            'biomarkers' => array('apob', 'hs_crp', 'homocysteine', 'lp_a', 'omega_3_index'),
            'biomarker_count' => 5,
            'description' => 'Advanced cardiovascular risk assessment with cutting-edge biomarkers',
            'target_audience' => 'Individuals with cardiovascular concerns or family history',
            'features' => array(
                'advanced_cardiovascular_panel' => true,
                'cardiovascular_risk_assessment' => true,
                'consultation_with_cardiology_specialist' => true,
                'detailed_cardiovascular_analysis' => true,
                'prevention_recommendations' => true
            ),
            'consultation_included' => true,
            'consultation_duration' => 45,
            'validity_period' => 365,
            'prerequisites' => array('comprehensive_diagnostic', 'premium_membership')
        ),
        'longevity_performance' => array(
            'id' => 'longevity_performance_package',
            'name' => 'Longevity & Performance Package',
            'price' => 899,
            'billing' => 'one_time',
            'biomarkers' => array('telomere_length', 'nad_plus', 'tac', 'uric_acid', 'gut_microbiota_diversity', 'mirna_486', 'creatine_kinase', 'il_6', 'grip_strength', 'il_18'),
            'biomarker_count' => 10,
            'description' => 'Comprehensive longevity and performance optimization with cutting-edge biomarkers',
            'target_audience' => 'Individuals focused on longevity and peak performance',
            'features' => array(
                'comprehensive_longevity_panel' => true,
                'performance_optimization_plan' => true,
                'consultation_with_longevity_specialist' => true,
                'detailed_longevity_analysis' => true,
                'performance_tracking' => true,
                'longevity_roadmap' => true
            ),
            'consultation_included' => true,
            'consultation_duration' => 60,
            'validity_period' => 365,
            'prerequisites' => array('comprehensive_diagnostic', 'premium_membership')
        ),
        'cognitive_energy' => array(
            'id' => 'cognitive_energy_package',
            'name' => 'Cognitive & Energy Package',
            'price' => 499,
            'billing' => 'one_time',
            'biomarkers' => array('apoe_genotype', 'coq10', 'heavy_metals_panel', 'ferritin', 'folate'),
            'biomarker_count' => 5,
            'description' => 'Advanced cognitive health and energy optimization assessment',
            'target_audience' => 'Individuals with cognitive concerns or energy optimization goals',
            'features' => array(
                'cognitive_health_panel' => true,
                'energy_optimization_plan' => true,
                'consultation_with_cognitive_specialist' => true,
                'detailed_cognitive_analysis' => true,
                'brain_health_recommendations' => true
            ),
            'consultation_included' => true,
            'consultation_duration' => 45,
            'validity_period' => 365,
            'prerequisites' => array('comprehensive_diagnostic', 'premium_membership')
        ),
        'metabolic_optimization' => array(
            'id' => 'metabolic_optimization_package',
            'name' => 'Metabolic Optimization Package',
            'price' => 299,
            'billing' => 'one_time',
            'biomarkers' => array('fasting_insulin', 'homa_ir', 'leptin', 'ghrelin'),
            'biomarker_count' => 4,
            'description' => 'Advanced metabolic health assessment for weight management and energy optimization',
            'target_audience' => 'Individuals with metabolic concerns or weight management goals',
            'features' => array(
                'metabolic_health_panel' => true,
                'metabolic_optimization_plan' => true,
                'consultation_with_metabolic_specialist' => true,
                'detailed_metabolic_analysis' => true,
                'weight_management_recommendations' => true
            ),
            'consultation_included' => true,
            'consultation_duration' => 30,
            'validity_period' => 365,
            'prerequisites' => array('comprehensive_diagnostic', 'premium_membership')
        ),
        'complete_advanced_panel' => array(
            'id' => 'complete_advanced_panel',
            'name' => 'Complete Advanced Panel',
            'price' => 1999,
            'billing' => 'one_time',
            'biomarkers' => 'all_advanced_biomarkers',
            'biomarker_count' => 30,
            'description' => 'Complete health optimization ecosystem with all advanced biomarkers',
            'target_audience' => 'Individuals seeking comprehensive health optimization',
            'features' => array(
                'all_advanced_biomarkers' => true,
                'comprehensive_health_optimization_plan' => true,
                'consultation_with_multiple_specialists' => true,
                'detailed_comprehensive_analysis' => true,
                'ongoing_optimization_support' => true,
                'priority_access_to_new_biomarkers' => true
            ),
            'consultation_included' => true,
            'consultation_duration' => 90,
            'validity_period' => 365,
            'prerequisites' => array('comprehensive_diagnostic', 'premium_membership')
        )
    ),
    
    'individual_addons' => array(
        'pricing_tiers' => array(
            'basic' => array(
                'range' => '49-79',
                'examples' => array('ferritin', 'folate', 'uric_acid'),
                'description' => 'Essential biomarkers for basic health optimization'
            ),
            'standard' => array(
                'range' => '79-129',
                'examples' => array('estradiol_e2', 'progesterone', 'shbg', 'cortisol', 'apob', 'hs_crp'),
                'description' => 'Standard advanced biomarkers for targeted optimization'
            ),
            'premium' => array(
                'range' => '199-399',
                'examples' => array('telomere_length', 'gut_microbiota_diversity', 'apoe_genotype'),
                'description' => 'Premium biomarkers for advanced health optimization'
            )
        )
    ),
    
    'consultation_services' => array(
        'quick_check' => array(
            'id' => 'quick_check_consultation',
            'name' => 'Quick Health Check',
            'price' => 99,
            'duration' => 15,
            'description' => 'Brief health review and general recommendations',
            'trigger' => 'low_priority_symptoms_or_mild_biomarkers',
            'symptom_threshold' => 1,
            'biomarker_threshold' => 1,
            'includes' => array('Basic review', 'General recommendations', 'Next steps')
        ),
        'focused_review' => array(
            'id' => 'focused_review_consultation',
            'name' => 'Focused Health Review',
            'price' => 199,
            'duration' => 30,
            'description' => 'Targeted analysis of specific health concerns',
            'trigger' => 'medium_priority_symptoms_or_moderate_biomarkers',
            'symptom_threshold' => 3,
            'biomarker_threshold' => 2,
            'includes' => array('Targeted analysis', 'Specific recommendations', 'Action plan')
        ),
        'comprehensive_review' => array(
            'id' => 'comprehensive_review_consultation',
            'name' => 'Comprehensive Health Review',
            'price' => 299,
            'duration' => 60,
            'description' => 'Complete health analysis and optimization plan',
            'trigger' => 'high_priority_symptoms_or_critical_biomarkers',
            'symptom_threshold' => 5,
            'biomarker_threshold' => 3,
            'includes' => array('Complete analysis', 'Comprehensive recommendations', 'Detailed action plan', 'Follow-up support')
        )
    ),
    
    'business_rules' => array(
        'membership_upgrades' => array(
            'basic_to_comprehensive' => array(
                'discount' => 50,
                'discount_type' => 'percentage',
                'description' => '50% discount on comprehensive diagnostic for basic members'
            ),
            'comprehensive_to_premium' => array(
                'discount' => 99,
                'discount_type' => 'fixed',
                'description' => '$99 credit toward premium membership for comprehensive diagnostic customers'
            )
        ),
        'addon_discounts' => array(
            'premium_member_discount' => array(
                'discount' => 20,
                'discount_type' => 'percentage',
                'description' => '20% discount on all addon packages for premium members'
            ),
            'package_bundle_discount' => array(
                'discount' => 15,
                'discount_type' => 'percentage',
                'description' => '15% discount when purchasing multiple addon packages'
            )
        ),
        'consultation_thresholds' => array(
            'symptom_thresholds' => array(
                'high_priority' => 5,
                'medium_priority' => 3,
                'low_priority' => 1
            ),
            'biomarker_thresholds' => array(
                'critical' => 3,
                'moderate' => 2,
                'mild' => 1
            )
        ),
        'cancellation_policies' => array(
            'membership_cancellation' => '30-day notice required',
            'addon_refunds' => 'No refunds after lab draw',
            'consultation_refunds' => '24-hour cancellation policy'
        )
    ),
    
    'payment_processing' => array(
        'supported_methods' => array('credit_card', 'debit_card', 'bank_transfer'),
        'billing_cycles' => array('monthly', 'quarterly', 'annually', 'one_time'),
        'currency' => 'USD',
        'tax_rates' => array(
            'default' => 0.0,
            'by_state' => array()
        ),
        'processing_fees' => array(
            'credit_card' => 2.9,
            'debit_card' => 2.9,
            'bank_transfer' => 1.0
        )
    ),
    
    'analytics_tracking' => array(
        'conversion_points' => array(
            'membership_signup' => true,
            'addon_purchase' => true,
            'consultation_booking' => true,
            'assessment_completion' => true,
            'biomarker_testing' => true
        ),
        'revenue_tracking' => array(
            'membership_revenue' => true,
            'addon_revenue' => true,
            'consultation_revenue' => true,
            'lifetime_value' => true,
            'churn_rate' => true
        ),
        'user_behavior' => array(
            'dashboard_usage' => true,
            'assessment_completion' => true,
            'biomarker_viewing' => true,
            'upgrade_attempts' => true,
            'support_requests' => true
        )
    )
); 