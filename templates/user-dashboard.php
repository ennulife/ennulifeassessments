<?php
/**
 * Template for the user assessment dashboard - "The Bio-Metric Canvas"
 */

// Ensure biomarker auto-sync is triggered for weight and BMI
if ( class_exists( 'ENNU_Biomarker_Auto_Sync' ) && is_user_logged_in() ) {
	$auto_sync = new ENNU_Biomarker_Auto_Sync();
	$auto_sync->ensure_biomarker_sync();
}

// Add inline JavaScript for toggle functions to ensure they're available immediately
?>
<script type="text/javascript">
// Inline toggle functions - available immediately
window.togglePanel = function(panelKey) {
    console.log('togglePanel called with:', panelKey);
    const panelContent = document.getElementById('panel-content-' + panelKey);
    if (!panelContent) {
        console.error('Panel content not found for:', panelKey);
        return;
    }
    const panelHeader = panelContent.previousElementSibling;
    if (!panelHeader) {
        console.error('Panel header not found for:', panelKey);
        return;
    }
    const expandIcon = panelHeader.querySelector('.panel-expand-icon');
    if (!expandIcon) {
        console.error('Panel expand icon not found for:', panelKey);
        return;
    }
    
    if (panelContent.style.display === 'none' || panelContent.style.display === '') {
        panelContent.style.display = 'block';
        expandIcon.textContent = '▼';
        panelHeader.classList.add('expanded');
    } else {
        panelContent.style.display = 'none';
        expandIcon.textContent = '▶';
        panelHeader.classList.remove('expanded');
    }
};

window.toggleBiomarkerMeasurements = function(panelKey, vectorCategory, biomarkerKey) {
    console.log('toggleBiomarkerMeasurements called with:', panelKey, vectorCategory, biomarkerKey);
    const containerId = 'biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey;
    const container = document.getElementById(containerId);
    if (!container) {
        console.error('Biomarker container not found for:', containerId);
        return;
    }
    const listItem = container.previousElementSibling;
    if (!listItem) {
        console.error('Biomarker list item not found for:', containerId);
        return;
    }
    const expandIcon = listItem.querySelector('.biomarker-list-expand');
    if (!expandIcon) {
        console.error('Biomarker expand icon not found for:', containerId);
        return;
    }
    
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        listItem.classList.add('expanded');
        expandIcon.textContent = '▼';
    } else {
        container.style.display = 'none';
        listItem.classList.remove('expanded');
        expandIcon.textContent = '▶';
    }
};

window.toggleVectorCategory = function(panelKey, vectorCategory) {
    console.log('toggleVectorCategory called with:', panelKey, vectorCategory);
    const vectorContainer = document.querySelector('[data-panel="' + panelKey + '"][data-vector="' + vectorCategory + '"]');
    if (!vectorContainer) {
        console.error('Vector container not found for:', panelKey, vectorCategory);
        return;
    }
    const biomarkerItems = vectorContainer.querySelectorAll('.biomarker-list-item');
    const isExpanded = biomarkerItems[0] && biomarkerItems[0].classList.contains('expanded');
    
    biomarkerItems.forEach(function(item) {
        const biomarkerKey = item.getAttribute('data-biomarker');
        const container = document.getElementById('biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey);
        const expandIcon = item.querySelector('.biomarker-list-expand');
        
        if (isExpanded) {
            // Collapse all
            container.style.display = 'none';
            item.classList.remove('expanded');
            expandIcon.textContent = '▶';
        } else {
            // Expand all
            container.style.display = 'block';
            item.classList.add('expanded');
            expandIcon.textContent = '▼';
        }
    });
};

console.log('Inline toggle functions loaded successfully');
</script>
<?php

// Helper function to get category icons (SVG Line Icons)
if (!function_exists('get_category_icon')) {
function get_category_icon($category) {
    $icons = array(
        'Physical Measurements' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>',
        'Basic Metabolic Panel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>',
        'Electrolytes & Minerals' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
        'Protein Panel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
        'Liver Function' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/></svg>',
        'Complete Blood Count' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"/><path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"/><path d="M12 3c0 1-1 2-2 2s-2-1-2-2 1-2 2-2 2 1 2 2z"/><path d="M12 21c0-1 1-2 2-2s2 1 2 2-1 2-2 2-2-1-2-2z"/></svg>',
        'Lipid Panel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
        'Hormones' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'Thyroid' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/><path d="M8 14h8"/></svg>',
        'Advanced Cardiovascular' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/><path d="M12 8v8M8 12h8"/></svg>',
        'Advanced Longevity' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>',
        'Advanced Performance' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
        'Advanced Cognitive' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><circle cx="12" cy="12" r="10"/><path d="M12 2v20"/><path d="M2 12h20"/></svg>',
        'Advanced Energy' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><path d="M12 8v8M8 12h8"/></svg>',
        'Advanced Metabolic' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/><path d="M12 8v8M8 12h8"/></svg>',
        'Performance' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>'
    );
    
    return isset($icons[$category]) ? $icons[$category] : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>';
}

function get_biomarker_category($biomarker_key) {
    $categories = array(
        // Physical Measurements (8 biomarkers)
        'weight' => 'Physical Measurements',
        'bmi' => 'Physical Measurements',
        'body_fat_percent' => 'Physical Measurements',
        'waist_measurement' => 'Physical Measurements',
        'neck_measurement' => 'Physical Measurements',
        'blood_pressure' => 'Physical Measurements',
        'heart_rate' => 'Physical Measurements',
        'temperature' => 'Physical Measurements',
        'grip_strength' => 'Physical Measurements',
        'body_composition' => 'Physical Measurements',
        
        // Basic Metabolic Panel (8 biomarkers)
        'glucose' => 'Basic Metabolic Panel',
        'hba1c' => 'Basic Metabolic Panel',
        'bun' => 'Basic Metabolic Panel',
        'creatinine' => 'Basic Metabolic Panel',
        'gfr' => 'Basic Metabolic Panel',
        'bun_creatinine_ratio' => 'Basic Metabolic Panel',
        'alt' => 'Basic Metabolic Panel',
        'ast' => 'Basic Metabolic Panel',
        'alkaline_phosphatase' => 'Basic Metabolic Panel',
        'egfr' => 'Basic Metabolic Panel',
        
        // Electrolytes & Minerals (4 biomarkers)
        'sodium' => 'Electrolytes & Minerals',
        'potassium' => 'Electrolytes & Minerals',
        'chloride' => 'Electrolytes & Minerals',
        'carbon_dioxide' => 'Electrolytes & Minerals',
        'calcium' => 'Electrolytes & Minerals',
        'magnesium' => 'Electrolytes & Minerals',
        'phosphorus' => 'Electrolytes & Minerals',
        
        // Protein Panel (2 biomarkers)
        'protein' => 'Protein Panel',
        'albumin' => 'Protein Panel',
        
        // Lipid Panel (5 biomarkers)
        'cholesterol' => 'Lipid Panel',
        'hdl' => 'Lipid Panel',
        'ldl' => 'Lipid Panel',
        'vldl' => 'Lipid Panel',
        'triglycerides' => 'Lipid Panel',
        'total_cholesterol' => 'Lipid Panel',
        
        // Advanced Cardiovascular (7 biomarkers)
        'apob' => 'Advanced Cardiovascular',
        'hs_crp' => 'Advanced Cardiovascular',
        'homocysteine' => 'Advanced Cardiovascular',
        'lp_a' => 'Advanced Cardiovascular',
        'omega_3_index' => 'Advanced Cardiovascular',
        'tmao' => 'Advanced Cardiovascular',
        'nmr_lipoprofile' => 'Advanced Cardiovascular',
        'crp' => 'Advanced Cardiovascular',
        'lipoprotein_a' => 'Advanced Cardiovascular',
        'esr' => 'Advanced Cardiovascular',
        'il_6' => 'Advanced Cardiovascular',
        'tnf_alpha' => 'Advanced Cardiovascular',
        'fibrinogen' => 'Advanced Cardiovascular',
        
        // Hormones (6 biomarkers)
        'testosterone_total' => 'Hormones',
        'testosterone_free' => 'Hormones',
        'free_testosterone' => 'Hormones',
        'estradiol' => 'Hormones',
        'progesterone' => 'Hormones',
        'fsh' => 'Hormones',
        'lh' => 'Hormones',
        'cortisol' => 'Hormones',
        'dhea_s' => 'Hormones',
        'dhea' => 'Hormones',
        'prolactin' => 'Hormones',
        'shbg' => 'Hormones',
        'igf_1' => 'Hormones',
        'testosterone' => 'Hormones',
        
        // Thyroid (3 biomarkers)
        'tsh' => 'Thyroid',
        't3' => 'Thyroid',
        't4' => 'Thyroid',
        'free_t3' => 'Thyroid',
        'free_t4' => 'Thyroid',
        
        // Complete Blood Count (8 biomarkers)
        'wbc' => 'Complete Blood Count',
        'rbc' => 'Complete Blood Count',
        'hemoglobin' => 'Complete Blood Count',
        'hematocrit' => 'Complete Blood Count',
        'mcv' => 'Complete Blood Count',
        'mch' => 'Complete Blood Count',
        'mchc' => 'Complete Blood Count',
        'rdw' => 'Complete Blood Count',
        'platelets' => 'Complete Blood Count',
        'neutrophils' => 'Complete Blood Count',
        'lymphocytes' => 'Complete Blood Count',
        'monocytes' => 'Complete Blood Count',
        'eosinophils' => 'Complete Blood Count',
        'basophils' => 'Complete Blood Count',
        
        // Advanced Metabolic (7 biomarkers)
        'vitamin_d' => 'Advanced Metabolic',
        'vitamin_b12' => 'Advanced Metabolic',
        'folate' => 'Advanced Metabolic',
        'iron' => 'Advanced Metabolic',
        'ferritin' => 'Advanced Metabolic',
        'zinc' => 'Advanced Metabolic',
        'copper' => 'Advanced Metabolic',
        'selenium' => 'Advanced Metabolic',
        'b12' => 'Advanced Metabolic',
        'blood_sugar' => 'Advanced Metabolic',
        'insulin' => 'Advanced Metabolic',
        'fasting_insulin' => 'Advanced Metabolic',
        'homa_ir' => 'Advanced Metabolic',
        'glycomark' => 'Advanced Metabolic',
        'uric_acid' => 'Advanced Metabolic',
        'leptin' => 'Advanced Metabolic',
        'ghrelin' => 'Advanced Metabolic',
        'adiponectin' => 'Advanced Metabolic',
        'one_five_ag' => 'Advanced Metabolic',
        '1_5_ag' => 'Advanced Metabolic',
        
        // Advanced Cognitive (4 biomarkers)
        'apoe_genotype' => 'Advanced Cognitive',
        'ptau_217' => 'Advanced Cognitive',
        'beta_amyloid_42_40_ratio' => 'Advanced Cognitive',
        'beta_amyloid_ratio' => 'Advanced Cognitive',
        'gfap' => 'Advanced Cognitive',
        'omega_3' => 'Advanced Cognitive',
        'bdnf' => 'Advanced Cognitive',
        'cognitive_function' => 'Advanced Cognitive',
        
        // Advanced Longevity (4 biomarkers)
        'telomere_length' => 'Advanced Longevity',
        'nad' => 'Advanced Longevity',
        'nad_plus' => 'Advanced Longevity',
        'mitochondrial_function' => 'Advanced Longevity',
        'oxidative_stress' => 'Advanced Longevity',
        
        // Advanced Performance (3 biomarkers)
        'creatine_kinase' => 'Advanced Performance',
        'lactate_dehydrogenase' => 'Advanced Performance',
        'vo2_max' => 'Advanced Performance',
        'lactate_threshold' => 'Advanced Performance',
        'muscle_mass' => 'Advanced Performance',
        'bone_density' => 'Advanced Performance',
        
        // Advanced Energy (3 biomarkers)
        'coq10' => 'Advanced Energy',
        'atp_levels' => 'Advanced Energy',
        'mitochondrial_biomarkers' => 'Advanced Energy',
        'energy_metabolism' => 'Advanced Energy',
        
        // Heavy Metals & Toxicity (4 biomarkers)
        'arsenic' => 'Heavy Metals & Toxicity',
        'lead' => 'Heavy Metals & Toxicity',
        'mercury' => 'Heavy Metals & Toxicity',
        'heavy_metals_panel' => 'Heavy Metals & Toxicity',
        
        // Performance (1 biomarker)
        'performance' => 'Performance'
    );
    
    return isset($categories[$biomarker_key]) ? $categories[$biomarker_key] : 'Other';
}

// Helper function for relative time formatting
function get_relative_time($timestamp) {
    $time_diff = time() - strtotime($timestamp);
    
    if ($time_diff < 60) {
        return 'Just now';
    } elseif ($time_diff < 3600) {
        $minutes = floor($time_diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time_diff < 86400) {
        $hours = floor($time_diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time_diff < 604800) {
        $days = floor($time_diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($time_diff < 2592000) {
        $weeks = floor($time_diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($time_diff < 31536000) {
        $months = floor($time_diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time_diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

// Helper function to get assessment name from type
function get_assessment_name($assessment_type) {
    $assessment_names = array(
        'ennu-menopause' => 'Menopause Assessment',
        'ennu-skin' => 'Skin Health Assessment',
        'ennu-sleep' => 'Sleep Quality Assessment',
        'ennu-testosterone' => 'Testosterone Assessment',
        'ennu-weight-loss' => 'Weight Loss Assessment',
        'ennu-welcome' => 'Welcome Assessment',
        'ennu-hair-consultation' => 'Hair Consultation',
        'ennu-ed-treatment-consultation' => 'ED Treatment Consultation',
        'ennu-weight-loss-consultation' => 'Weight Loss Consultation',
        'ennu-health-optimization-consultation' => 'Health Optimization Consultation',
        'ennu-skin-consultation' => 'Skin Consultation',
        'ennu-health-consultation' => 'Health Consultation',
        'ennu-hormone-consultation' => 'Hormone Consultation',
        'ennu-menopause-consultation' => 'Menopause Consultation',
        'ennu-testosterone-consultation' => 'Testosterone Consultation',
        'ennu-sleep-consultation' => 'Sleep Consultation'
    );
    
    return isset($assessment_names[$assessment_type]) ? $assessment_names[$assessment_type] : ucwords(str_replace('-', ' ', $assessment_type));
}

// Helper function to get assessment display name from source
function get_assessment_display_name_from_source($assessment_source) {
	$assessment_names = array(
		// Core assessments
		'health_optimization_assessment' => 'Health Optimization Assessment',
		'testosterone_assessment' => 'Testosterone Assessment',
		'hormone_assessment' => 'Hormone Assessment',
		'menopause_assessment' => 'Menopause Assessment',
		'ed_treatment_assessment' => 'ED Treatment Assessment',
		'skin_assessment' => 'Skin Assessment',
		'hair_assessment' => 'Hair Assessment',
		'sleep_assessment' => 'Sleep Assessment',
		'weight_loss_assessment' => 'Weight Loss Assessment',
		'welcome_assessment' => 'Welcome Assessment',
		
		// Flag types
		'auto_flagged' => 'Lab Results Analysis',
		'manual' => 'Manual Flag',
		'critical' => 'Critical Alert',
		'symptom_assessment' => 'Health Optimization Assessment',
		'symptom_triggered' => 'Health Optimization Assessment',
		'unknown_assessment' => 'Unknown Assessment',
		'' => 'Unknown Assessment',
		
		// Health vector categories (fallback)
		'Physical Measurements' => 'Physical Measurements',
		'Basic Metabolic Panel' => 'Basic Metabolic Panel',
		'Electrolytes & Minerals' => 'Electrolytes & Minerals',
		'Lipid Panel' => 'Lipid Panel',
		'Advanced Cardiovascular' => 'Advanced Cardiovascular',
		'Hormones' => 'Hormones',
		'Thyroid' => 'Thyroid',
		'Complete Blood Count' => 'Complete Blood Count',
		'Advanced Metabolic' => 'Advanced Metabolic',
		'Advanced Cognitive' => 'Advanced Cognitive',
		'Advanced Longevity' => 'Advanced Longevity',
		'Advanced Performance' => 'Advanced Performance',
		'Performance' => 'Performance',
		'Advanced Energy' => 'Advanced Energy',
		'Heavy Metals & Toxicity' => 'Heavy Metals & Toxicity',
		'Protein Panel' => 'Protein Panel',
		'Other' => 'Other',
	);
	
	return $assessment_names[$assessment_source] ?? ucwords(str_replace('_', ' ', $assessment_source));
}

// Helper function to get assessment display info (icon and color) - SVG Line Icons
function get_assessment_display_info($assessment_name) {
	$assessment_info = array(
		// Core assessments
		'Health Optimization Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'color' => '#3b82f6'),
		'Testosterone Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>', 'color' => '#ef4444'),
		'Hormone Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>', 'color' => '#8b5cf6'),
		'Menopause Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>', 'color' => '#ec4899'),
		'ED Treatment Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>', 'color' => '#dc2626'),
		'Skin Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>', 'color' => '#f59e0b'),
		'Hair Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>', 'color' => '#059669'),
		'Sleep Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/></svg>', 'color' => '#6366f1'),
		'Weight Loss Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>', 'color' => '#10b981'),
		'Welcome Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8M8 13h6"/></svg>', 'color' => '#6b7280'),
		
		// Flag types
		'Lab Results Analysis' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'color' => '#059669'),
		'Manual Flag' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>', 'color' => '#f59e0b'),
		'Critical Alert' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>', 'color' => '#dc2626'),

		'Unknown Assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>', 'color' => '#6b7280'),
		
		// Health vector categories
		'Physical Measurements' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>', 'color' => '#6366f1'),
		'Basic Metabolic Panel' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'color' => '#14b8a6'),
		'Electrolytes & Minerals' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>', 'color' => '#06b6d4'),
		'Lipid Panel' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>', 'color' => '#dc2626'),
		'Advanced Cardiovascular' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/><path d="M12 8v8M8 12h8"/></svg>', 'color' => '#ef4444'),
		'Hormones' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>', 'color' => '#10b981'),
		'Thyroid' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/><path d="M8 14h8"/></svg>', 'color' => '#7c3aed'),
		'Complete Blood Count' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"/><path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"/><path d="M12 3c0 1-1 2-2 2s-2-1-2-2 1-2 2-2 2 1 2 2z"/><path d="M12 21c0-1 1-2 2-2s2 1 2 2-1 2-2 2-2-1-2-2z"/></svg>', 'color' => '#3b82f6'),
		'Advanced Metabolic' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/><path d="M12 8v8M8 12h8"/></svg>', 'color' => '#f59e0b'),
		'Advanced Cognitive' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><circle cx="12" cy="12" r="10"/><path d="M12 2v20"/><path d="M2 12h20"/></svg>', 'color' => '#8b5cf6'),
		'Advanced Longevity' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>', 'color' => '#ec4899'),
		'Advanced Performance' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>', 'color' => '#6366f1'),
		'Performance' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>', 'color' => '#059669'),
		'Advanced Energy' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><path d="M12 8v8M8 12h8"/></svg>', 'color' => '#b45309'),
		'Heavy Metals & Toxicity' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>', 'color' => '#dc2626'),
		'Protein Panel' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>', 'color' => '#059669'),
		'Other' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>', 'color' => '#6b7280'),
		
		// Assessment source mappings (fallback)
		'auto_flagged' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'color' => '#059669'),
		'manual' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>', 'color' => '#f59e0b'),
		'critical' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>', 'color' => '#dc2626'),
		'symptom_assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'color' => '#3b82f6'),
		'symptom_triggered' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'color' => '#3b82f6'),
		'unknown_assessment' => array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>', 'color' => '#6b7280'),
	);
	
	return $assessment_info[$assessment_name] ?? array('icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'color' => '#6366f1');
}
}

/**
 * Render biomarker measurement component
 *
 * @param array $measurement_data Measurement data from ENNU_Biomarker_Manager
 * @return string HTML output
 */
if (!function_exists('render_biomarker_measurement')) {
function render_biomarker_measurement($measurement_data) {
    // Debug: Function is being called
    error_log("ENNU DEBUG: render_biomarker_measurement called for biomarker: " . ($measurement_data['biomarker_id'] ?? 'unknown'));
    
    if (isset($measurement_data['error'])) {
        return '<div class="biomarker-error">' . esc_html($measurement_data['error']) . '</div>';
    }
    
    // Use null coalescing operator to handle missing array keys with defaults
    $biomarker_id = $measurement_data['biomarker_id'] ?? '';
    $current_value = $measurement_data['current_value'] ?? 0;
    $target_value = $measurement_data['target_value'] ?? null;
    $unit = $measurement_data['unit'] ?? '';
    $optimal_min = $measurement_data['optimal_min'] ?? 0;
    $optimal_max = $measurement_data['optimal_max'] ?? 100;
    $percentage_position = $measurement_data['percentage_position'] ?? 50;
    $target_position = $measurement_data['target_position'] ?? null;
    $status = $measurement_data['status'] ?? array('status' => 'normal', 'status_text' => 'Normal', 'status_class' => 'normal');
    $has_flags = $measurement_data['has_flags'] ?? false;
    $flags = $measurement_data['flags'] ?? array();
    $achievement_status = $measurement_data['achievement_status'] ?? array('status' => 'normal', 'text' => 'Normal', 'icon_class' => 'normal');
    $health_vector = $measurement_data['health_vector'] ?? '';
    $has_admin_override = $measurement_data['has_admin_override'] ?? false;
    $display_name = $measurement_data['display_name'] ?? ($measurement_data['biomarker_name'] ?? '');
    $has_user_data = $measurement_data['has_user_data'] ?? false;
    
    // Get complete range data for ruler display
    $range_manager = new ENNU_Recommended_Range_Manager();
    $user_data = array(
        'age' => get_user_meta(get_current_user_id(), 'ennu_global_exact_age', true) ?: 35,
        'gender' => get_user_meta(get_current_user_id(), 'ennu_global_gender', true) ?: 'male'
    );
    $complete_range = $range_manager->get_recommended_range($biomarker_id, $user_data);
    
    // Calculate target value using the target algorithm if not set
    if (!$target_value && class_exists('ENNU_Biomarker_Target_Calculator')) {
        // Ensure we have valid range data for the target calculator
        if (isset($complete_range['optimal_min']) && isset($complete_range['optimal_max']) && 
            $complete_range['optimal_min'] !== null && $complete_range['optimal_max'] !== null) {
            
            // Use current value if available, otherwise use midpoint of optimal range
            $value_for_calculation = $current_value ?: ($optimal_min + (($optimal_max - $optimal_min) / 2));
            
            $target_data = ENNU_Biomarker_Target_Calculator::calculate_personalized_target(
                $biomarker_id,
                $value_for_calculation,
                $complete_range,
                $user_data['age'],
                $user_data['gender']
            );
            if ($target_data && isset($target_data['target_value'])) {
                $target_value = $target_data['target_value'];
                $target_position = calculate_percentage_position($target_value, $optimal_min, $optimal_max);
            }
        } else {
            // Fallback to midpoint of optimal range if range data is invalid
            $optimal_min = is_numeric($optimal_min) ? (float)$optimal_min : 0;
            $optimal_max = is_numeric($optimal_max) ? (float)$optimal_max : 1;
            $target_value = (float)$optimal_min + (((float)$optimal_max - (float)$optimal_min) / 2);
            $target_position = 50; // Middle position
            error_log("ENNU DEBUG: Using fallback target for $biomarker_id - range data invalid");
        }
    }
    
    // If still no target value, provide a default optimal target for educational purposes
    if (!$target_value && isset($complete_range['optimal_min']) && isset($complete_range['optimal_max'])) {
        $optimal_min = is_numeric($optimal_min) ? (float)$optimal_min : 0;
        $optimal_max = is_numeric($optimal_max) ? (float)$optimal_max : 1;
        $target_value = (float)$optimal_min + (((float)$optimal_max - (float)$optimal_min) / 2);
        $target_position = 50; // Middle position
    }
    
    // FINAL SAFETY CHECK: Ensure target_value is always a valid number
    if ($target_value === null || !is_numeric($target_value)) {
        if (isset($complete_range['optimal_min']) && isset($complete_range['optimal_max']) && 
            is_numeric($complete_range['optimal_min']) && is_numeric($complete_range['optimal_max'])) {
            $target_value = (float)$complete_range['optimal_min'] + (((float)$complete_range['optimal_max'] - (float)$complete_range['optimal_min']) / 2);
        } else {
            $target_value = 0; // Ultimate fallback
        }
        $target_position = 50; // Middle position
        error_log("ENNU DEBUG: Applied final safety fallback for $biomarker_id - target_value: $target_value");
    }
    
    // Get range boundaries for ruler - use the complete range from AI Medical Team
    $optimal_min = is_numeric($optimal_min) ? (float)$optimal_min : 0;
    $optimal_max = is_numeric($optimal_max) ? (float)$optimal_max : 1;
    $normal_min = isset($complete_range['normal_min']) && is_numeric($complete_range['normal_min']) ? (float)$complete_range['normal_min'] : $optimal_min;
    $normal_max = isset($complete_range['normal_max']) && is_numeric($complete_range['normal_max']) ? (float)$complete_range['normal_max'] : $optimal_max;
    $critical_min = isset($complete_range['critical_min']) && is_numeric($complete_range['critical_min']) ? (float)$complete_range['critical_min'] : $normal_min;
    $critical_max = isset($complete_range['critical_max']) && is_numeric($complete_range['critical_max']) ? (float)$complete_range['critical_max'] : $normal_max;
    
    // Calculate initial range width
    $range_width = $critical_max - $critical_min;
    
    // Ensure we have valid range values and handle narrow ranges
    if ($critical_min === $critical_max || $range_width <= 0) {
        // Fallback to a reasonable range if values are invalid
        $optimal_range_width = $optimal_max - $optimal_min;
        if ($optimal_range_width <= 0) {
            // If optimal range is also invalid, create a minimum range
            $optimal_range_width = 1;
            $optimal_min = is_numeric($optimal_min) ? (float)$optimal_min : 0;
            $optimal_max = $optimal_min + $optimal_range_width;
        }
        
        $critical_min = $optimal_min - ($optimal_range_width * 0.5);
        $critical_max = $optimal_max + ($optimal_range_width * 0.5);
        $range_width = $critical_max - $critical_min;
    }
    
    // Ensure optimal range has minimum visibility (at least 5% of total range)
    $optimal_range_width = $optimal_max - $optimal_min;
    $min_optimal_width_percent = 5; // 5% minimum width
    $min_optimal_width = ($range_width * $min_optimal_width_percent) / 100;
    
    if ($optimal_range_width < $min_optimal_width) {
        $optimal_center = ($optimal_min + $optimal_max) / 2;
        $optimal_min = $optimal_center - ($min_optimal_width / 2);
        $optimal_max = $optimal_center + ($min_optimal_width / 2);
        error_log("ENNU DEBUG: Expanded optimal range for biomarker $biomarker_id to ensure visibility");
    }
    
    // Recalculate positions with adjusted ranges
    $range_width = $critical_max - $critical_min;
    
    // Final safety check for division by zero
    if ($range_width <= 0) {
        // Use fallback values to prevent division by zero
        $range_width = 1;
        $critical_min = $optimal_min - 0.5;
        $critical_max = $optimal_max + 0.5;
        $range_width = $critical_max - $critical_min;
        error_log("ENNU DEBUG: Fixed division by zero for biomarker $biomarker_id - using fallback range");
    }
    
    $optimal_start_pos = (($optimal_min - $critical_min) / $range_width) * 100;
    $optimal_end_pos = (($optimal_max - $critical_min) / $range_width) * 100;
    $normal_start_pos = (($normal_min - $critical_min) / $range_width) * 100;
    $normal_end_pos = (($normal_max - $critical_min) / $range_width) * 100;
    
    // Calculate current and target positions on the full range ruler
    $current_ruler_position = $has_user_data && $current_value && is_numeric($current_value) ? (($current_value - $critical_min) / $range_width) * 100 : null;
    $target_ruler_position = $target_value && is_numeric($target_value) ? (($target_value - $critical_min) / $range_width) * 100 : null;
    
    // Clamp positions to 0-100 range
    $current_ruler_position = $current_ruler_position !== null ? max(0, min(100, $current_ruler_position)) : null;
    $target_ruler_position = $target_ruler_position !== null ? max(0, min(100, $target_ruler_position)) : null;
    
    // Debug output for troubleshooting
    error_log("ENNU DEBUG: Biomarker $biomarker_id - Critical: $critical_min-$critical_max, Normal: $normal_min-$normal_max, Optimal: $optimal_min-$optimal_max, Current: $current_value, Target: $target_value, Range Width: $range_width");
    
    // Also output to browser for immediate debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo "<!-- DEBUG: Biomarker $biomarker_id - Critical: $critical_min-$critical_max, Normal: $normal_min-$normal_max, Optimal: $optimal_min-$optimal_max -->";
    }
    
    // Add visible debug info for troubleshooting ruler positioning
    $debug_info = array(
        'biomarker_id' => $biomarker_id,
        'critical_range' => "$critical_min - $critical_max",
        'normal_range' => "$normal_min - $normal_max",
        'optimal_range' => "$optimal_min - $optimal_max",
        'current_value' => $current_value,
        'target_value' => $target_value,
        'range_width' => $range_width,
        'current_position' => $current_ruler_position,
        'target_position' => $target_ruler_position
    );
    
    ob_start();
    ?>
    <div class="biomarker-measurement <?php echo $has_user_data ? 'has-data' : 'no-data'; ?>" data-biomarker-id="<?php echo esc_attr($biomarker_id); ?>">
        <!-- Header -->
        <div class="biomarker-measurement-header">
            <h4 class="biomarker-measurement-title"><?php echo esc_html($display_name); ?></h4>
            <div class="biomarker-measurement-icons">
                <?php if ($has_flags): ?>
                    								<span class="biomarker-flag-icon" title="Flagged for review">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
										<path d="M3 3v18h18"/>
										<path d="M18 17V9"/>
										<path d="M13 17V5"/>
										<path d="M8 17v-3"/>
									</svg>
								</span>
                <?php endif; ?>
                <span class="biomarker-info-icon" title="View details"></span>
            </div>
        </div>
        
        <!-- Enhanced Range Ruler -->
        <div class="biomarker-range-ruler-container" 
             data-critical-min="<?php echo esc_attr($critical_min); ?>"
             data-critical-max="<?php echo esc_attr($critical_max); ?>"
             data-normal-min="<?php echo esc_attr($normal_min); ?>"
             data-normal-max="<?php echo esc_attr($normal_max); ?>"
             data-optimal-min="<?php echo esc_attr($optimal_min); ?>"
             data-optimal-max="<?php echo esc_attr($optimal_max); ?>"
             data-unit="<?php echo esc_attr($unit); ?>"
             data-biomarker-id="<?php echo esc_attr($biomarker_id); ?>"
             data-debug="<?php echo esc_attr(json_encode($debug_info)); ?>">
            
            <!-- Dynamic 2-Line Tooltip -->
            <div class="biomarker-dynamic-tooltip" style="display: none;">
                <div class="tooltip-range-line"></div>
                <div class="tooltip-value-line"></div>
            </div>
            
            <!-- Ruler Bar with Notch Marks -->
            <div class="biomarker-range-ruler">
                <!-- Critical Range (Red) -->
                <div class="biomarker-range-segment critical-range" style="left: 0%; width: 100%;"></div>
                
                <!-- Normal Range (Yellow) -->
                <div class="biomarker-range-segment normal-range" 
                     style="left: <?php echo esc_attr($normal_start_pos); ?>%; width: <?php echo esc_attr($normal_end_pos - $normal_start_pos); ?>%;"></div>
                
                <!-- Optimal Range (Green) -->
                <div class="biomarker-range-segment optimal-range" 
                     style="left: <?php echo esc_attr($optimal_start_pos); ?>%; width: <?php echo esc_attr($optimal_end_pos - $optimal_start_pos); ?>%;"></div>
                
                <!-- Ruler Notch Marks -->
                <div class="biomarker-ruler-notches">
                    <div class="biomarker-notch critical-min" style="left: 0%;" title="Critical Min: <?php echo esc_attr($critical_min . ' ' . $unit); ?>"></div>
                    <div class="biomarker-notch normal-min" style="left: <?php echo esc_attr($normal_start_pos); ?>%;" title="Normal Min: <?php echo esc_attr($normal_min . ' ' . $unit); ?>"></div>
                    <div class="biomarker-notch optimal-min" style="left: <?php echo esc_attr($optimal_start_pos); ?>%;" title="Optimal Min: <?php echo esc_attr($optimal_min . ' ' . $unit); ?>"></div>
                    <div class="biomarker-notch optimal-max" style="left: <?php echo esc_attr($optimal_end_pos); ?>%;" title="Optimal Max: <?php echo esc_attr($optimal_max . ' ' . $unit); ?>"></div>
                    <div class="biomarker-notch normal-max" style="left: <?php echo esc_attr($normal_end_pos); ?>%;" title="Normal Max: <?php echo esc_attr($normal_max . ' ' . $unit); ?>"></div>
                    <div class="biomarker-notch critical-max" style="left: 100%;" title="Critical Max: <?php echo esc_attr($critical_max . ' ' . $unit); ?>"></div>
                </div>
                
                <!-- X-axis Interval Labels -->
                <div style="position: absolute; bottom: -25px; left: 0; width: 100%; height: 20px; pointer-events: none;">
                    <div style="position: absolute; font-size: 10px; color: #dc2626; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: 0%;"><?php echo esc_html($critical_min . ' ' . $unit); ?></div>
                    <div style="position: absolute; font-size: 10px; color: #ea580c; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: <?php echo esc_attr($normal_start_pos); ?>%;"><?php echo esc_html($normal_min . ' ' . $unit); ?></div>
                    <div style="position: absolute; font-size: 10px; color: #16a34a; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: <?php echo esc_attr($optimal_start_pos); ?>%;"><?php echo esc_html($optimal_min . ' ' . $unit); ?></div>
                    <div style="position: absolute; font-size: 10px; color: #16a34a; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: <?php echo esc_attr($optimal_end_pos); ?>%;"><?php echo esc_html($optimal_max . ' ' . $unit); ?></div>
                    <div style="position: absolute; font-size: 10px; color: #ea580c; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: <?php echo esc_attr($normal_end_pos); ?>%;"><?php echo esc_html($normal_max . ' ' . $unit); ?></div>
                    <div style="position: absolute; font-size: 10px; color: #dc2626; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: 100%;"><?php echo esc_html($critical_max . ' ' . $unit); ?></div>
                </div>
                
                <!-- Debug markers for testing -->
                <?php if (true): // Always show for debugging ?>
                <div style="position: absolute; top: -40px; left: 0; width: 100%; font-size: 9px; color: #999;">
                    <div style="position: absolute; left: 0%;">0%</div>
                    <div style="position: absolute; left: 25%;">25%</div>
                    <div style="position: absolute; left: 50%;">50%</div>
                    <div style="position: absolute; left: 75%;">75%</div>
                    <div style="position: absolute; left: 100%;">100%</div>
                    <div style="position: absolute; left: <?php echo esc_attr($target_ruler_position); ?>%; color: purple; font-weight: bold;">T:<?php echo round($target_ruler_position, 1); ?>%</div>
                </div>
                <?php endif; ?>
                
                <!-- Current Value Dot -->
                <?php if ($has_user_data && $current_ruler_position !== null): ?>
                    <div class="biomarker-current-dot" 
                         style="left: <?php echo esc_attr($current_ruler_position); ?>%;"
                         data-biomarker-name="<?php echo esc_attr($display_name); ?>"
                         data-value="<?php echo esc_attr($current_value); ?>"
                         data-unit="<?php echo esc_attr($unit); ?>">
                        <div class="biomarker-dot-label"><?php echo esc_html($current_value); ?></div>
                        <div class="biomarker-dot-tooltip">
                            <div class="tooltip-line-1">Your Current <?php echo esc_html($display_name); ?></div>
                            <div class="tooltip-line-2"><?php echo esc_html($current_value . ' ' . $unit); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Target Value Dot - Always show if target exists -->
                <?php if ($target_value && $target_ruler_position !== null): ?>
                    <div class="biomarker-target-dot" 
                         style="left: <?php echo esc_attr($target_ruler_position); ?>%;"
                         data-biomarker-name="<?php echo esc_attr($display_name); ?>"
                         data-value="<?php echo esc_attr($target_value); ?>"
                         data-unit="<?php echo esc_attr($unit); ?>">
                        <div class="biomarker-dot-label"><?php echo esc_html($target_value); ?></div>
                        <div class="biomarker-dot-tooltip">
                            <div class="tooltip-line-1">Your Target <?php echo esc_html($display_name); ?></div>
                            <div class="tooltip-line-2"><?php echo esc_html($target_value . ' ' . $unit); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Range Labels - Hidden by default, shown as tooltips -->
            <div class="biomarker-range-labels" style="display: none;">
                <span class="biomarker-range-label critical"><?php echo esc_html($critical_min . ' ' . $unit); ?></span>
                <span class="biomarker-range-label normal"><?php echo esc_html($normal_min . ' ' . $unit); ?></span>
                <span class="biomarker-range-label optimal"><?php echo esc_html($optimal_min . ' ' . $unit); ?></span>
                <span class="biomarker-range-label optimal"><?php echo esc_html($optimal_max . ' ' . $unit); ?></span>
                <span class="biomarker-range-label normal"><?php echo esc_html($normal_max . ' ' . $unit); ?></span>
                <span class="biomarker-range-label critical"><?php echo esc_html($critical_max . ' ' . $unit); ?></span>
            </div>
        </div>
        
        <!-- Compact Status Bar -->
        <div class="biomarker-compact-status">
            <!-- Status Badge -->
            <div class="biomarker-status-badge <?php echo esc_attr($status['status_class']); ?>">
                								<span class="status-icon">
									<?php if ($status['status'] === 'optimal'): ?>
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
											<polyline points="20,6 9,17 4,12"></polyline>
										</svg>
									<?php elseif ($status['status'] === 'suboptimal'): ?>
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
											<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
											<line x1="12" y1="9" x2="12" y2="13"/>
											<line x1="12" y1="17" x2="12.01" y2="17"/>
										</svg>
									<?php else: ?>
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
											<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
										</svg>
									<?php endif; ?>
								</span>
                <span class="status-text"><?php echo esc_html($status['status_text']); ?></span>
            </div>
            
            <!-- Health Vector Badge -->
            <div class="biomarker-health-badge">
                <span class="health-text"><?php echo esc_html($health_vector); ?></span>
        </div>
        
            <!-- Achievement Indicator -->
            <div class="biomarker-achievement-badge <?php echo esc_attr($achievement_status['icon_class']); ?>">
                								<span class="achievement-icon">
									<?php if ($achievement_status['status'] === 'achieved'): ?>
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
											<circle cx="12" cy="12" r="10"/>
											<path d="M12 6v6l4 2"/>
										</svg>
									<?php elseif ($achievement_status['status'] === 'educational'): ?>
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
											<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
											<polyline points="14,2 14,8 20,8"/>
											<line x1="16" y1="13" x2="8" y2="13"/>
											<line x1="16" y1="17" x2="8" y2="17"/>
											<polyline points="10,9 9,9 8,9"/>
										</svg>
									<?php else: ?>
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
											<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
											<path d="M21 3v5h-5"/>
											<path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
											<path d="M3 21v-5h5"/>
										</svg>
									<?php endif; ?>
								</span>
                <span class="achievement-text"><?php echo esc_html($achievement_status['text']); ?></span>
        </div>
        </div>
        
        <!-- Values Row (only when user has data) -->
        <?php if ($has_user_data): ?>
        <div class="biomarker-values-row">
            <div class="biomarker-current-display">
                <span class="value-label">Current:</span>
                <span class="value-number"><?php echo esc_html($current_value . ' ' . $unit); ?></span>
        </div>
            <div class="biomarker-target-display">
                <span class="value-label">Target:</span>
                <span class="value-number"><?php echo esc_html($target_value . ' ' . $unit); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Admin Override Indicator -->
        <?php if ($has_admin_override): ?>
            <div class="biomarker-override-indicator">
                <span class="biomarker-override-text">Custom Range Set</span>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Helper function to calculate percentage position
function calculate_percentage_position($value, $min_range, $max_range) {
    if ($max_range <= $min_range) {
        return 50; // Default to middle if range is invalid
    }
    
    $position = (($value - $min_range) / ($max_range - $min_range)) * 100;
    
    // Clamp to 0-100 range
    return max(0, min(100, $position));
}
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( isset( $template_args ) && is_array( $template_args ) ) {
	extract( $template_args, EXTR_SKIP ); }

// Defensive checks for required variables
if ( ! isset( $current_user ) || ! is_object( $current_user ) ) {
	echo '<div class="error-message">' . esc_html__( 'ERROR: $current_user is not set or not an object.', 'ennulifeassessments' ) . '</div>';
	return;
}
$age                        = $age ?? '';
$gender                     = $gender ?? '';
$height                     = $height ?? null;
$weight                     = $weight ?? null;
$bmi                        = $bmi ?? null;
$user_assessments           = $user_assessments ?? array();
$insights                   = $insights ?? array();
$health_optimization_report = $health_optimization_report ?? array();
$shortcode_instance         = $shortcode_instance ?? null;

// Define missing variables to prevent warnings
$is_female = ( strtolower( trim( $gender ?? '' ) ) === 'female' );
$is_completed = ! empty( $user_assessments );
$health_opt_assessment = isset( $user_assessments['health_optimization_assessment'] ) ? $user_assessments['health_optimization_assessment'] : null;
if ( ! $shortcode_instance ) {
	echo '<div class="error-message">' . esc_html__( 'ERROR: $shortcode_instance is not set.', 'ennulifeassessments' ) . '</div>';
	return;
}

// Define user ID and display name
$user_id      = $current_user->ID ?? 0;
$first_name   = isset( $current_user->first_name ) ? $current_user->first_name : '';
$last_name    = isset( $current_user->last_name ) ? $current_user->last_name : '';
$display_name = trim( $first_name . ' ' . $last_name );
if ( empty( $display_name ) ) {
	$display_name = $current_user->display_name ?? $current_user->user_login ?? 'User';
}
?>
<div class="ennu-user-dashboard">


	<!-- Light/Dark Mode Toggle -->
	<div class="theme-toggle-container">
		<button class="theme-toggle" id="theme-toggle" aria-label="Toggle light/dark mode">
			<div class="toggle-track">
				<div class="toggle-thumb">
					<svg class="toggle-icon sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="5"/>
						<line x1="12" y1="1" x2="12" y2="3"/>
						<line x1="12" y1="21" x2="12" y2="23"/>
						<line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
						<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
						<line x1="1" y1="12" x2="3" y2="12"/>
						<line x1="21" y1="12" x2="23" y2="12"/>
						<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
						<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
					</svg>
					<svg class="toggle-icon moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
					</svg>
				</div>
			</div>
		</button>
	</div>





	<div class="dashboard-main-grid ennu-logo-pattern-bg">
		<?php
		if ( function_exists( 'ennu_render_logo' ) ) {
			echo '<div class="ennu-logo-container">';
			ennu_render_logo(
				array(
					'color' => 'white',
					'size'  => 'medium',
					'link'  => home_url( '/' ),
					'alt'   => 'ENNU Life',
					'class' => '',
				)
			);
			echo '</div>';
		}
		?>

		<main class="dashboard-main-content">


			<!-- Welcome Section -->
			<div class="dashboard-welcome-section">
				<!-- Logo above title -->
				<div class="dashboard-logo-container">
					<img src="<?php echo esc_url( plugins_url( 'assets/img/ennu-logo-black.png', dirname( __FILE__ ) ) ); ?>" 
						 alt="ENNU Life Logo" 
						 class="dashboard-logo light-mode-logo">
					<img src="<?php echo esc_url( plugins_url( 'assets/img/ennu-logo-white.png', dirname( __FILE__ ) ) ); ?>" 
						 alt="ENNU Life Logo" 
						 class="dashboard-logo dark-mode-logo">
				</div>
				<h1 class="dashboard-title dashboard-title-large" id="dynamic-greeting"><?php echo esc_html( $display_name ); ?>'s Biometric Canvas</h1>
				<script>
				// Dynamic greeting system using user's local time
				(function() {
					function getLocalTimeGreeting(displayName) {
						const hour = new Date().getHours();
						
						// Morning variations (5 AM - 12 PM)
						const morningGreetings = [
							`Good morning, ${displayName}`,
							`Morning, ${displayName}`,
							`Rise and shine, ${displayName}`,
							`Good morning, ${displayName}`,
							`Morning, ${displayName}`
						];
						
						// Afternoon variations (12 PM - 5 PM)
						const afternoonGreetings = [
							`Good afternoon, ${displayName}`,
							`Afternoon, ${displayName}`,
							`Good afternoon, ${displayName}`,
							`Afternoon, ${displayName}`,
							`Good afternoon, ${displayName}`
						];
						
						// Evening variations (5 PM - 9 PM)
						const eveningGreetings = [
							`Good evening, ${displayName}`,
							`Evening, ${displayName}`,
							`Good evening, ${displayName}`,
							`Evening, ${displayName}`,
							`Good evening, ${displayName}`
						];
						
						// Night variations (9 PM - 12 AM)
						const nightGreetings = [
							`Good evening, ${displayName}`,
							`Evening, ${displayName}`,
							`Good evening, ${displayName}`,
							`Evening, ${displayName}`,
							`Good evening, ${displayName}`
						];
						
						// Late night variations (12 AM - 5 AM)
						const lateNightGreetings = [
							`Still up, ${displayName}?`,
							`Late night, ${displayName}`,
							`Night owl, ${displayName}`,
							`Late night, ${displayName}`,
							`Still awake, ${displayName}?`
						];
						
						// Select greeting based on user's local time
						let greetings;
						if (hour >= 5 && hour < 12) {
							greetings = morningGreetings;
						} else if (hour >= 12 && hour < 17) {
							greetings = afternoonGreetings;
						} else if (hour >= 17 && hour < 21) {
							greetings = eveningGreetings;
						} else if (hour >= 21 && hour < 24) {
							greetings = nightGreetings;
						} else {
							greetings = lateNightGreetings;
						}
						
						// Randomly select one greeting
						return greetings[Math.floor(Math.random() * greetings.length)];
					}
					
					// Update the greeting when page loads
					document.addEventListener('DOMContentLoaded', function() {
						const greetingElement = document.getElementById('dynamic-greeting');
						if (greetingElement) {
							const displayName = '<?php echo esc_js( $display_name ); ?>';
							greetingElement.textContent = getLocalTimeGreeting(displayName);
						}
					});
				})();
				</script>
				<p class="dashboard-subtitle">Track your progress and discover personalized insights for optimal health.</p>
				
				<!-- Vital Statistics Display -->
				<?php if ( ! empty( $age ) || ! empty( $gender ) || ! empty( $height ) || ! empty( $weight ) || ! empty( $bmi ) ) : ?>
				<div class="vital-stats-display">
					<?php if ( ! empty( $age ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
								<circle cx="12" cy="7" r="4"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html( $age ); ?> years</span>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $gender ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<circle cx="12" cy="12" r="10"/>
								<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html( $gender ); ?></span>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $height ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
								<path d="M3 6h18M3 12h18M3 18h18"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html( $height ); ?></span>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $weight ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
								<path d="M12 6v6M8 12h8"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html( $weight ); ?></span>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $bmi ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M3 3h18v18H3z"/>
								<path d="M9 9h6v6H9z"/>
								<path d="M12 3v18M3 12h18"/>
							</svg>
						</span>
						<span class="vital-stat-value">BMI: <?php echo esc_html( $bmi ); ?></span>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- Scores Row -->
			<details class="dashboard-scores-accordion">
				<summary class="scores-title-container">
					<h2 class="scores-title">MY LIFE SCORES</h2>
				</summary>

				<!-- Enhanced Score Presentations -->
				<div class="enhanced-scores-container">
					<!-- ENNU Life Score Presentation -->
					<div class="main-score-presentation">
						<?php echo do_shortcode( '[ennu_score_presentation type="lifescore" show_pillars="true" show_history="true" show_interpretation="true" animation="true" size="large"]' ); ?>
					</div>
					
					<!-- Individual Pillar Score Presentations -->
					<div class="pillar-scores-grid">
						<?php
						if ( is_array( $average_pillar_scores ) ) {
							$pillar_map = array(
								'mind' => 'pmind',
								'body' => 'pbody', 
								'lifestyle' => 'plifestyle',
								'aesthetics' => 'paesthetics'
							);
							
							foreach ( $average_pillar_scores as $pillar => $score ) {
								$pillar_type = $pillar_map[ strtolower( $pillar ) ] ?? 'pmind';
								?>
								<div class="pillar-score-presentation">
									<?php echo do_shortcode( '[ennu_score_presentation type="' . $pillar_type . '" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="small"]' ); ?>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>

				<!-- Contextual Text Container -->
				<div class="contextual-text-container">
					<div class="contextual-text" id="contextual-text">
						<!-- This will be populated by JavaScript -->
					</div>
				</div>
			</details>

			<!-- My Health Goals Section -->
			<details class="health-goals-accordion">
				<summary class="scores-title-container">
					<h2 class="scores-title">MY HEALTH GOALS</h2>
				</summary>
				<div class="health-goals-grid" role="group" aria-label="Select your health goals">
					<?php
					if ( isset( $health_goals_data ) && isset( $health_goals_data['all_goals'] ) ) :
						foreach ( $health_goals_data['all_goals'] as $goal_id => $goal ) :
							?>
							<div class="goal-pill <?php echo $goal['selected'] ? 'selected' : ''; ?>" 
								 data-goal-id="<?php echo esc_attr( $goal_id ); ?>"
								 role="button" 
								 tabindex="0"
								 aria-pressed="<?php echo $goal['selected'] ? 'true' : 'false'; ?>"
								 aria-label="<?php echo esc_attr( $goal['label'] . ( $goal['selected'] ? ' - Currently selected' : ' - Click to select' ) ); ?>"
								 title="<?php echo esc_attr( $goal['description'] ?? $goal['label'] ); ?>">
								<div class="goal-pill-icon" aria-hidden="true">
									<?php echo $goal['icon']; ?>
								</div>
								<span class="goal-pill-text"><?php echo esc_html( $goal['label'] ); ?></span>
								<span class="goal-pill-check <?php echo $goal['selected'] ? 'visible' : ''; ?>" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
										<polyline points="20,6 9,17 4,12"></polyline>
									</svg>
								</span>
								<div class="goal-pill-loading" aria-hidden="true">
									<div class="loading-spinner"></div>
								</div>
							</div>
							<?php
						endforeach;

						// Add goals summary
						$selected_count = count(
							array_filter(
								$health_goals_data['all_goals'],
								function( $goal ) {
									return $goal['selected'];
								}
							)
						);
						$total_count    = count( $health_goals_data['all_goals'] );
						?>
						<div class="goals-summary">
							<div class="goals-counter">
								<span class="selected-count"><?php echo esc_html( $selected_count ); ?></span> of 
								<span class="total-count"><?php echo esc_html( $total_count ); ?></span> goals selected
							</div>
							<?php if ( $selected_count > 0 ) : ?>
								<div class="goals-boost-indicator">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
										<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
									</svg>
									<span>Boost applied to matching assessments</span>
								</div>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<div class="no-goals-message">
							<div class="no-goals-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
									<circle cx="12" cy="12" r="10"/>
									<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
									<line x1="12" y1="17" x2="12.01" y2="17"/>
								</svg>
							</div>
							<h3>No Health Goals Available</h3>
							<p>Complete your first assessment to unlock personalized health goals that will boost your scoring in relevant areas.</p>
							<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'assessments' ) ); ?>" class="btn btn-primary">
								Start an Assessment
							</a>
						</div>
						<?php
					endif;
					?>
				</div>
			</details>

			<!-- My Story Tabbed Section -->
			<div class="my-story-section">
				<div class="scores-title-container">
					<h2 class="scores-title">MY STORY</h2>
				</div>
				
				<div class="my-story-tabs">
					<nav class="my-story-tab-nav">
						<ul>
							<li><a href="#tab-my-assessments">My Assessments</a></li>
							<li><a href="#tab-my-biomarkers" class="my-story-tab-active">My Biomarkers</a></li>
							<li><a href="#tab-my-symptoms">My Symptoms</a></li>
						</ul>
					</nav>
					
					<!-- Tab 1: My Assessments -->
					<div id="tab-my-assessments" class="my-story-tab-content">
						<!-- Header Section - Matching My Story Styling -->
						<div class="scores-title-container">
							<h2 class="scores-title">MY ASSESSMENTS</h2>
						</div>
						
						<div class="assessment-cards-container">
							<?php
							// Define the ordered assessment pairs with inclusive logic
							$assessment_pairs = array(
								array( 'health', 'weight-loss' ),
								array( 'hormone', 'testosterone' ), // Now gender-inclusive
								array( 'hair', 'skin' ),
								array( 'sleep', 'ed-treatment' ), // ED treatment remains gender-specific for medical reasons
							);

							// Count assessments (gender-inclusive)
							$completed_count = 0;
							$total_count     = 0;

							foreach ( $assessment_pairs as $pair ) {
								foreach ( $pair as $assessment_key ) {
									// Skip if assessment doesn't exist
									if ( ! isset( $user_assessments[ $assessment_key ] ) ) {
										continue;
									}

									if ( $assessment_key === 'ed-treatment' && $is_female ) {
										continue; // Skip ED treatment for females
									}

									$total_count++;
									if ( $user_assessments[ $assessment_key ]['completed'] ) {
										$completed_count++;
									}
								}
							}
							?>
							
							<!-- Progress Summary -->
							<div class="progress-summary">
								<div class="progress-stats">
									<div class="stat-item">
										<span class="stat-number"><?php echo esc_html( $completed_count ); ?></span>
										<span class="stat-label">Completed</span>
									</div>
									<div class="stat-item">
										<span class="stat-number"><?php echo esc_html( $total_count - $completed_count ); ?></span>
										<span class="stat-label">Remaining</span>
									</div>
									<div class="stat-item">
										<span class="stat-number"><?php echo esc_html( $total_count > 0 ? round( ( $completed_count / $total_count ) * 100 ) : 0 ); ?>%</span>
										<span class="stat-label">Progress</span>
									</div>
								</div>
								<div class="progress-bar">
									<div class="progress-fill" style="width: <?php echo esc_attr( $total_count > 0 ? ( $completed_count / $total_count ) * 100 : 0 ); ?>%"></div>
								</div>
							</div>
							
							<div class="assessment-cards-grid">
								<?php
								// Define the ordered assessment pairs with inclusive logic
								$assessment_pairs = array(
									array( 'health', 'weight-loss' ),
									array( 'hormone', 'testosterone' ), // Now gender-inclusive
									array( 'hair', 'skin' ),
									array( 'sleep', 'ed-treatment' ), // ED treatment remains gender-specific for medical reasons
								);

								// Gender-based assessment filtering
								$user_gender = strtolower( trim( $gender ?? '' ) );
								$is_male     = ( $user_gender === 'male' );
								$is_female   = ( $user_gender === 'female' );

								// Filter and order assessments
								$ordered_assessments = array();
								$card_index          = 0;

								foreach ( $assessment_pairs as $pair ) {
									$pair_assessments = array();

									foreach ( $pair as $assessment_key ) {
										// Skip if assessment doesn't exist
										if ( ! isset( $user_assessments[ $assessment_key ] ) ) {
											continue;
										}

										if ( $assessment_key === 'ed-treatment' && $is_female ) {
											continue; // Skip ED treatment for females
										}

										$pair_assessments[] = array(
											'key'   => $assessment_key,
											'data'  => $user_assessments[ $assessment_key ],
											'index' => ++$card_index,
										);
									}

									// Add valid pair to ordered assessments
									if ( ! empty( $pair_assessments ) ) {
										$ordered_assessments = array_merge( $ordered_assessments, $pair_assessments );
									}
								}

								// Define assessment icons using the same style as "Speak With Expert" button
								$assessment_icons = array(
									'hair'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
									'skin'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
									'health'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
									'weight-loss'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11h4"/><path d="M10 16h4"/></svg>',
									'hormone'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
									'testosterone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
									'sleep'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>',
									'ed-treatment' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
									'menopause'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
								);

								// Render the ordered assessments
								foreach ( $ordered_assessments as $assessment_item ) :
									$assessment_key = $assessment_item['key'];
									$assessment     = $assessment_item['data'];
									$card_index     = $assessment_item['index'];
									?>
									<div class="assessment-card <?php echo $assessment['completed'] ? 'completed' : 'incomplete'; ?> animate-card" style="animation-delay: <?php echo $card_index * 0.1; ?>s;">
										<div class="assessment-card-header">
											<?php
											// Get assessment icon
											$assessment_icon = '';
											if ( isset( $assessment_icons[ $assessment_key ] ) ) {
												$assessment_icon = $assessment_icons[ $assessment_key ];
											}
											?>
											<?php if ( ! empty( $assessment_icon ) ) : ?>
												<div class="assessment-icon">
													<?php echo $assessment_icon; ?>
												</div>
											<?php endif; ?>
											<h3 class="assessment-title"><?php echo esc_html( $assessment['label'] ?? ucwords( str_replace( '_', ' ', $assessment['key'] ?? 'Assessment' ) ) ); ?></h3>
											<div class="assessment-status">
												<?php if ( $assessment['completed'] && isset( $assessment['score'] ) ) : ?>
													<?php if ( $assessment['key'] === 'health_optimization_assessment' ) : ?>
														<div class="assessment-analysis-display">
															<span class="analysis-value"><?php echo esc_html( $assessment['score'] ); ?></span>
															<span class="analysis-label">Analysis</span>
														</div>
													<?php else : ?>
													<div class="assessment-score-display">
														<span class="score-value"><?php echo esc_html( number_format( $assessment['score'], 1 ) ); ?></span>
														<span class="score-label">Score</span>
													</div>
													<?php endif; ?>
												<?php endif; ?>
												<?php if ( $assessment['completed'] ) : ?>
													<div class="status-completed-container">
														<span class="status-badge completed">
															<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
																<polyline points="20,6 9,17 4,12"></polyline>
															</svg>
															<span class="completed-text">Completed</span>
														</span>
														<?php if ( ! empty( $assessment['date'] ) ) : ?>
															<div class="assessment-timestamp">
																<?php
																$timestamp = $assessment['date'];
																// Format the timestamp - if it's a Unix timestamp, convert it
																if ( is_numeric( $timestamp ) ) {
																	$date_obj = new DateTime();
																	$date_obj->setTimestamp( $timestamp );
																} else {
																	// If it's already a formatted date string, try to parse and reformat
																	$date_obj = DateTime::createFromFormat( 'Y-m-d H:i:s', $timestamp );
																	if ( ! $date_obj ) {
																		$date_obj = new DateTime( $timestamp );
																	}
																}

																if ( $date_obj ) {
																	// Get day of week
																	$day_of_week = $date_obj->format( 'l' );

																	// Get month
																	$month = $date_obj->format( 'F' );

																	// Get day with ordinal suffix
																	$day            = $date_obj->format( 'j' );
																	$ordinal_suffix = '';
																	if ( $day >= 11 && $day <= 13 ) {
																		$ordinal_suffix = 'th';
																	} else {
																		switch ( $day % 10 ) {
																			case 1:
																				$ordinal_suffix = 'st';
																				break;
																			case 2:
																				$ordinal_suffix = 'nd';
																				break;
																			case 3:
																				$ordinal_suffix = 'rd';
																				break;
																			default:
																				$ordinal_suffix = 'th';
																		}
																	}

																	// Get year
																	$year = $date_obj->format( 'Y' );

																	// Get time in 12-hour format with lowercase am/pm
																	$time = $date_obj->format( 'g:i' ) . strtolower( $date_obj->format( 'A' ) );

																	// Combine all parts
																	$formatted_date = $day_of_week . ' ' . $month . ' ' . $day . $ordinal_suffix . ', ' . $year . ' @ ' . $time;
																} else {
																	$formatted_date = $timestamp; // Fallback to original
																}
																echo esc_html( $formatted_date );
																?>
															</div>
														<?php endif; ?>
													</div>
												<?php else : ?>
													<span class="status-badge incomplete">
														<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
															<circle cx="12" cy="12" r="10"></circle>
															<path d="M12 6v6l4 2"></path>
														</svg>
														Pending
													</span>
												<?php endif; ?>
											</div>
										</div>
										
										<div class="assessment-card-body">
											<p class="assessment-description">
												<?php
												if ( $assessment['completed'] ) {
													// For completed assessments, show primary recommendation/status
													$primary_recommendation = '';
													$score                  = $assessment['score'];

													// Generate status based on score
													if ( $score >= 8.0 ) {
														$primary_recommendation = sprintf( __( 'Excellent results! Your %s score indicates optimal health in this area.', 'ennulifeassessments' ), $assessment['label'] ?? 'assessment' );
													} elseif ( $score >= 6.5 ) {
														$primary_recommendation = sprintf( __( 'Good progress! Your %s shows positive indicators with room for optimization.', 'ennulifeassessments' ), $assessment['label'] ?? 'assessment' );
													} elseif ( $score >= 5.0 ) {
														$primary_recommendation = sprintf( __( 'Moderate results. Your %s suggests several areas for targeted improvement.', 'ennulifeassessments' ), $assessment['label'] ?? 'assessment' );
													} else {
														$primary_recommendation = sprintf( __( 'Significant opportunities for improvement identified in your %s results.', 'ennulifeassessments' ), $assessment['label'] ?? 'assessment' );
													}

													echo esc_html( $primary_recommendation );
												} else {
													// For incomplete assessments, show the default message
													$label       = $assessment['label'] ?? ucwords( str_replace( '_', ' ', $assessment['key'] ?? 'Assessment' ) );
													$description = sprintf( __( 'Complete your %s to get personalized insights and recommendations.', 'ennulifeassessments' ), $label );
													echo esc_html( $description );
												}
												?>
											</p>
											
											<?php if ( $assessment['completed'] ) : ?>
												<!-- Speak With Expert Link - moved to top of completed section -->
												<div class="expert-consultation-link">
													<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'call' ) ); ?>" class="speak-expert-link">
														<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
															<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
															<circle cx="9" cy="7" r="4"/>
															<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
															<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
														</svg>
														Speak With Expert
													</a>
												</div>
												
												<!-- Recommendations Section (Hidden by default) -->
												<div class="assessment-section recommendations-section hidden">
													<h4 class="scores-title">RECOMMENDATIONS</h4>
													<div class="recommendations-content">
														<p>Your personalized recommendations will appear here based on your assessment results.</p>
														<!-- This can be populated with actual recommendations data -->
													</div>
												</div>
												
												<!-- Breakdown Section (Hidden by default) -->
												<?php if ( ! empty( $assessment['categories'] ) ) : ?>
													<div class="assessment-section breakdown-section hidden">
														<h4 class="scores-title">CATEGORY SCORES</h4>
														<div class="category-scores">
															<?php foreach ( $assessment['categories'] as $category => $score ) : ?>
																<div class="category-score-item">
																	<span class="category-name"><?php echo esc_html( $category ); ?></span>
																	<div class="category-score-bar">
																		<div class="category-score-fill" style="width: <?php echo esc_attr( $score * 10 ); ?>%"></div>
																	</div>
																	<span class="category-score-value"><?php echo esc_html( number_format( $score, 1 ) ); ?></span>
																</div>
															<?php endforeach; ?>
														</div>
													</div>
												<?php endif; ?>
											<?php endif; ?>
											
											<div class="assessment-card-actions">
												<?php if ( $assessment['completed'] ) : ?>
													<button type="button" class="btn btn-recommendations" data-assessment="<?php echo esc_attr( $assessment['key'] ); ?>">Recommendations</button>
													<button type="button" class="btn btn-breakdown" data-assessment="<?php echo esc_attr( $assessment['key'] ); ?>">Breakdown</button>
													<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( $shortcode_instance->get_assessment_page_slug( $assessment['key'] ) . '-assessment-details' ) ); ?>" class="btn btn-history">History</a>
												<?php else : ?>
													<!-- Expert consultation and Start Assessment for incomplete assessments -->
													<div class="incomplete-actions-row">
														<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'call' ) ); ?>" class="btn btn-expert-incomplete">
															<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
																<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
																<circle cx="9" cy="7" r="4"/>
																<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
																<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
															</svg>
															Speak With Expert
														</a>
														<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( $shortcode_instance->get_assessment_page_slug( $assessment['key'] ) ) ); ?>" class="btn btn-primary btn-pill">Start Assessment</a>
													</div>
												<?php endif; ?>
											</div>
											
											<?php if ( $assessment['completed'] ) : ?>
												<div class="assessment-retake-link">
													<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( $shortcode_instance->get_assessment_page_slug( $assessment['key'] ) ) ); ?>">Retake Assessment</a>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					
					<!-- Tab 2: My Symptoms -->
					<div id="tab-my-symptoms" class="my-story-tab-content">
						<div class="symptoms-container">
							<!-- Header Section - Matching My Story Styling -->
							<div class="scores-title-container">
								<h2 class="scores-title">MY SYMPTOMS</h2>
							</div>
							
							<div class="symptoms-overview">
								<?php
								// Get centralized symptoms from the manager
								$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
								$current_symptoms = $centralized_symptoms['symptoms'] ?? array();
								$symptoms_by_assessment = $centralized_symptoms['by_assessment'] ?? array();
								
								// Get symptom history
								$symptom_history = get_user_meta($user_id, 'ennu_symptom_history', true);
								$symptom_history = is_array($symptom_history) ? $symptom_history : array();
								
								// Get flagged biomarkers
								$flag_manager = new ENNU_Biomarker_Flag_Manager();
								$flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id, 'active');
								
								// Calculate statistics
								$total_symptoms = count($current_symptoms);
								$active_symptoms = count(array_filter($current_symptoms, function($symptom) {
									return isset($symptom['status']) && $symptom['status'] === 'active';
								}));
								$biomarker_correlations = count($flagged_biomarkers);
								$trending_symptoms = count(array_filter($current_symptoms, function($symptom) {
									return isset($symptom['occurrence_count']) && $symptom['occurrence_count'] > 1;
								}));
								?>
								
								<!-- Clean Stats Row -->
								<div class="biomarkers-stats-row">
									<div class="stat-item">
										<span class="stat-number"><?php echo $total_symptoms; ?></span>
										<span class="stat-label">Symptoms Logged</span>
									</div>
									<div class="stat-item">
										<span class="stat-number"><?php 
											$flag_manager = new ENNU_Biomarker_Flag_Manager();
											$flagged_count = count($flag_manager->get_flagged_biomarkers($user_id, 'active'));
											echo $flagged_count;
										?></span>
										<span class="stat-label">Biomarkers Flagged</span>
									</div>
									<div class="stat-item">
										<span class="stat-number"><?php echo $active_symptoms; ?></span>
										<span class="stat-label">Active Symptoms</span>
									</div>
								</div>
								
								<!-- Current Symptoms Section -->
								<div class="current-symptoms-section">
									<div class="section-header">
										<h4>Current Symptoms</h4>
										<p>Active symptoms extracted from your assessments and health data</p>
									</div>
									
									<?php if (!empty($current_symptoms)) : ?>
										<div class="symptoms-grid">
											<?php foreach ($current_symptoms as $symptom_key => $symptom_data) : ?>
												<div class="symptom-card" data-symptom="<?php echo esc_attr($symptom_key); ?>">
													<div class="symptom-header">
														<div class="symptom-icon">🩺</div>
														<div class="symptom-info">
															<h5 class="symptom-name"><?php echo esc_html($symptom_data['name'] ?? ucwords(str_replace('_', ' ', $symptom_key))); ?></h5>
															<div class="symptom-meta">
																<?php 
																$severity = isset($symptom_data['severity']) ? $symptom_data['severity'] : 'moderate';
																$frequency = isset($symptom_data['frequency']) ? $symptom_data['frequency'] : 'daily';
																
																// Ensure we have strings, not arrays
																$severity = is_string($severity) ? $severity : 'moderate';
																$frequency = is_string($frequency) ? $frequency : 'daily';
																
																// Additional safety check for arrays
																if (is_array($severity)) {
																	$severity = 'moderate';
																}
																if (is_array($frequency)) {
																	$frequency = 'daily';
																}
																?>
																<span class="symptom-severity"><?php echo esc_html(ucfirst($severity)); ?></span>
																<span class="symptom-frequency"><?php echo esc_html(ucfirst($frequency)); ?></span>
															</div>
														</div>
														<div class="symptom-status">
															<span class="status-badge active">Active</span>
														</div>
													</div>
													
													<?php if (isset($symptom_data['assessments']) && !empty($symptom_data['assessments'])) : ?>
														<div class="symptom-sources">
															<small>Sources: <?php echo esc_html(implode(', ', $symptom_data['assessments'])); ?></small>
														</div>
													<?php endif; ?>
													
													<?php if (isset($symptom_data['last_reported'])) : ?>
														<div class="symptom-timeline">
															<small>Last reported: <?php echo esc_html(date('M j, Y', strtotime($symptom_data['last_reported']))); ?></small>
														</div>
													<?php endif; ?>
													
													<?php 
													// Get duration information
													$duration_info = ENNU_Centralized_Symptoms_Manager::get_symptom_duration_info($symptom_data);
													if ($duration_info['status'] === 'active' && $duration_info['days_remaining'] > 0) : ?>
														<div class="symptom-duration">
															<small class="duration-info">
																<span class="duration-label">Expires in:</span>
																<span class="duration-value <?php echo $duration_info['days_remaining'] <= 7 ? 'urgent' : ($duration_info['days_remaining'] <= 14 ? 'warning' : 'normal'); ?>">
																	<?php echo esc_html($duration_info['days_remaining']); ?> days
																</span>
															</small>
														</div>
													<?php elseif ($duration_info['status'] === 'expired') : ?>
														<div class="symptom-duration">
															<small class="duration-info expired">
																<span class="duration-label">Status:</span>
																<span class="duration-value expired">Expired</span>
															</small>
														</div>
													<?php endif; ?>
												</div>
											<?php endforeach; ?>
										</div>
									<?php else : ?>
										<div class="no-symptoms-message">
											<div class="empty-state">
												<div class="empty-icon">📋</div>
												<h5>No Current Symptoms</h5>
												<p>Complete assessments to start tracking your symptoms and biomarker correlations.</p>
												<button class="btn-primary" onclick="window.location.href='#tab-my-assessments'">Take Assessments</button>
											</div>
										</div>
									<?php endif; ?>
								</div>
								
								<!-- Symptom History Section -->
								<div class="symptom-history-section">
									<div class="section-header">
										<h4>Symptom History</h4>
										<p>Timeline of your reported symptoms and health changes</p>
									</div>
									
									<?php if (!empty($symptom_history)) : ?>
										<div class="symptom-timeline">
											<?php foreach (array_reverse($symptom_history) as $entry) : ?>
												<?php if (isset($entry['date'], $entry['symptoms'])) : ?>
													<div class="timeline-entry">
														<div class="timeline-date">
															<?php echo esc_html(date('M j, Y @ g:i a', strtotime($entry['date']))); ?>
															<div class="relative-time">
																<?php echo esc_html(get_relative_time($entry['date'])); ?>
															</div>
															<?php if (isset($entry['assessment_type'])) : ?>
																<div class="assessment-source">
																	📋 <?php echo esc_html(get_assessment_name($entry['assessment_type'])); ?>
																</div>
															<?php endif; ?>
														</div>
														<div class="timeline-content">
															<?php
															// Handle symptoms array properly
															$symptoms_display = array();
															if (is_array($entry['symptoms'])) {
																foreach ($entry['symptoms'] as $symptom) {
																	if (is_array($symptom) && isset($symptom['name'])) {
																		$symptoms_display[] = $symptom['name'];
																	} elseif (is_string($symptom)) {
																		$symptoms_display[] = $symptom;
																	}
																}
															} elseif (is_string($entry['symptoms'])) {
																$symptoms_display = explode(',', $entry['symptoms']);
															}
															?>
															<div class="symptoms-list">
																<?php foreach ($symptoms_display as $symptom) : ?>
																	<span class="symptom-tag"><?php echo esc_html(trim($symptom)); ?></span>
																<?php endforeach; ?>
															</div>
														</div>
													</div>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									<?php else : ?>
										<div class="no-history-message">
											<p>No symptom history available. Complete assessments to start tracking.</p>
										</div>
									<?php endif; ?>
								</div>
								
								<!-- Biomarker Correlations Section -->
								<div class="biomarker-correlations-section">
									<div class="section-header">
										<h4>Biomarker Correlations</h4>
										<p>Biomarkers flagged based on your reported symptoms</p>
									</div>
									
									<?php if (!empty($flagged_biomarkers)) : ?>
										<div class="biomarker-flags-grid">
											<?php foreach ($flagged_biomarkers as $flag_id => $flag_data) : ?>
												<div class="biomarker-flag-card">
													<div class="flag-header">
														<div class="flag-icon">⚠️</div>
														<div class="flag-info">
															<h5 class="biomarker-name"><?php echo esc_html(ucwords(str_replace('_', ' ', $flag_data['biomarker_name']))); ?></h5>
															<div class="flag-reason"><?php echo esc_html($flag_data['reason']); ?></div>
														</div>
														<div class="flag-status">
															<span class="status-badge flagged">Flagged</span>
														</div>
													</div>
													
													<?php if (isset($flag_data['symptom'])) : ?>
														<div class="flag-details">
															<small>Triggered by: <strong><?php echo esc_html($flag_data['symptom']); ?></strong></small>
														</div>
													<?php endif; ?>
													
													<?php if (isset($flag_data['health_vector'])) : ?>
														<div class="flag-category">
															<small>Health Vector: <strong><?php echo esc_html(ucfirst($flag_data['health_vector'])); ?></strong></small>
														</div>
													<?php endif; ?>
												</div>
											<?php endforeach; ?>
										</div>
									<?php else : ?>
										<div class="no-correlations-message">
											<div class="empty-state">
												<div class="empty-icon">🔬</div>
												<h5>No Biomarker Correlations</h5>
												<p>Complete assessments to generate biomarker correlations based on your symptoms.</p>
											</div>
										</div>
									<?php endif; ?>
								</div>
								
								<!-- Action Buttons -->
								<div class="symptoms-actions">
									<button type="button" class="btn-secondary" id="update-symptoms" data-user-id="<?php echo esc_attr($user_id); ?>">
										Update Symptoms
									</button>
									<button type="button" class="btn-secondary" id="populate-symptoms" data-user-id="<?php echo esc_attr($user_id); ?>">
										Extract from Assessments
									</button>
									<button type="button" class="btn-secondary" id="clear-symptoms" data-user-id="<?php echo esc_attr($user_id); ?>">
										Clear Symptom History
									</button>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Tab 3: My Biomarkers -->
					<div id="tab-my-biomarkers" class="my-story-tab-content my-story-tab-active">
						<div class="biomarkers-container">
							
							<!-- Header Section - Matching My Story Styling -->
							<div class="scores-title-container">
								<h2 class="scores-title">MY BIOMARKERS</h2>
								</div>
								
							<!-- Clean Stats Row -->
							<div class="biomarkers-stats-row">
								<div class="stat-item">
									<span class="stat-number">50</span>
									<span class="stat-label">Core Biomarkers</span>
									</div>
								<div class="stat-item">
									<span class="stat-number">0</span>
									<span class="stat-label">Tested</span>
									</div>
								<div class="stat-item">
									<span class="stat-number"><?php 
										$flag_manager = new ENNU_Biomarker_Flag_Manager();
										$flagged_count = count($flag_manager->get_flagged_biomarkers($user_id, 'active'));
										echo $flagged_count;
									?></span>
									<span class="stat-label">Flagged</span>
									</div>
								</div>
								
							<!-- Action Buttons - Widened and Clean -->
								<div class="biomarkers-actions">
									<button class="btn btn-primary upload-lab-btn" onclick="uploadLabResults()">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
											<polyline points="7,10 12,15 17,10"/>
											<line x1="12" y1="15" x2="12" y2="3"/>
										</svg>
										Upload Lab Results
									</button>
									<button class="btn btn-secondary schedule-lab-btn" onclick="scheduleLabTest()">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<circle cx="12" cy="12" r="10"/>
											<polyline points="12,6 12,12 16,14"/>
										</svg>
										Schedule Lab Test
									</button>
							</div>
						</details>
						
						<!-- MY FLAGGED BIOMARKERS SECTION -->
							<div class="flagged-biomarkers-section">
								<div class="scores-title-container">
									<h2 class="scores-title">MY FLAGGED BIOMARKERS</h2>
								</div>
								
								<?php
								// Get flagged biomarkers from the manager
								$flag_manager = new ENNU_Biomarker_Flag_Manager();
								$flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id, 'active');
								
								// Convert flag data to display format
								$display_flagged_biomarkers = array();
								foreach ($flagged_biomarkers as $flag_id => $flag_data) {
									$biomarker_name = $flag_data['biomarker_name'];
									$category = get_biomarker_category($biomarker_name);
									
									// Debug output
									error_log("Biomarker: $biomarker_name, Category: $category");
									
									$display_flagged_biomarkers[$flag_id] = array(
										'biomarker_name' => $biomarker_name,
										'name' => ucwords(str_replace('_', ' ', $biomarker_name)),
										'category' => $category,
										'reason' => $flag_data['reason'] ?: 'Flagged for medical attention',
										'severity' => $flag_data['flag_type'] === 'critical' ? 'high' : 'moderate',
										'current_value' => 'N/A',
										'unit' => '',
										'normal_range' => '',
										'status' => 'flagged',
										'flagged_at' => $flag_data['flagged_at'],
										'flag_type' => $flag_data['flag_type']
									);
								}
								
								if (!empty($display_flagged_biomarkers)) :
									// Health vector category mapping - must match categories from get_biomarker_category()
									$health_vector_categories = array(
										// Basic categories
										'Physical Measurements' => array('name' => 'Physical Measurements', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>', 'color' => '#6366f1'),
										'Basic Metabolic Panel' => array('name' => 'Basic Metabolic Panel', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>', 'color' => '#14b8a6'),
										'Electrolytes & Minerals' => array('name' => 'Electrolytes & Minerals', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>', 'color' => '#06b6d4'),
										'Lipid Panel' => array('name' => 'Lipid Panel', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>', 'color' => '#dc2626'),
										'Advanced Cardiovascular' => array('name' => 'Advanced Cardiovascular', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/><path d="M12 8v8M8 12h8"/></svg>', 'color' => '#ef4444'),
										'Hormones' => array('name' => 'Hormones', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>', 'color' => '#10b981'),
										'Thyroid' => array('name' => 'Thyroid', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/><path d="M8 14h8"/></svg>', 'color' => '#7c3aed'),
										'Complete Blood Count' => array('name' => 'Complete Blood Count', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"/><path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"/><path d="M12 3c0 1-1 2-2 2s-2-1-2-2 1-2 2-2 2 1 2 2z"/><path d="M12 21c0-1 1-2 2-2s2 1 2 2-1 2-2 2-2-1-2-2z"/></svg>', 'color' => '#3b82f6'),
										'Advanced Metabolic' => array('name' => 'Advanced Metabolic', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/><path d="M12 8v8M8 12h8"/></svg>', 'color' => '#f59e0b'),
										'Advanced Cognitive' => array('name' => 'Advanced Cognitive', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><circle cx="12" cy="12" r="10"/><path d="M12 2v20"/><path d="M2 12h20"/></svg>', 'color' => '#8b5cf6'),
										'Advanced Longevity' => array('name' => 'Advanced Longevity', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>', 'color' => '#ec4899'),
										'Advanced Performance' => array('name' => 'Advanced Performance', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>', 'color' => '#6366f1'),
										'Performance' => array('name' => 'Performance', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>', 'color' => '#059669'),
										'Advanced Energy' => array('name' => 'Advanced Energy', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><path d="M12 8v8M8 12h8"/></svg>', 'color' => '#b45309'),
										'Heavy Metals & Toxicity' => array('name' => 'Heavy Metals & Toxicity', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>', 'color' => '#dc2626'),
										'Protein Panel' => array('name' => 'Protein Panel', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>', 'color' => '#059669'),
										'Other' => array('name' => 'Other', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>', 'color' => '#6b7280')
									);
									
									// Get flagged biomarkers with their actual assessment sources
									$flag_manager = new ENNU_Biomarker_Flag_Manager();
									$flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id, 'active');
									$grouped_flagged = array();
									
									foreach ($flagged_biomarkers as $flag_id => $flag_data) {
										// Use the actual assessment source from the flag data
										$assessment_source = $flag_data['assessment_source'] ?? '';
										
										// Enhanced assessment source detection
										if (empty($assessment_source)) {
											// Try to determine from flag type
											switch ($flag_data['flag_type']) {
												case 'auto_flagged':
													$assessment_source = 'auto_flagged';
													break;
												case 'manual':
													$assessment_source = 'manual';
													break;
												case 'critical':
													$assessment_source = 'critical';
													break;
												case 'symptom_triggered':
													// For symptom triggered flags, try to determine from symptom trigger
													if (!empty($flag_data['symptom_trigger'])) {
														// Map symptom triggers to assessment types
														$symptom_assessment_map = array(
															'fatigue' => 'health_optimization_assessment',
															'low_energy' => 'health_optimization_assessment',
															'weight_gain' => 'weight_loss_assessment',
															'difficulty_losing_weight' => 'weight_loss_assessment',
															'poor_sleep' => 'sleep_assessment',
															'insomnia' => 'sleep_assessment',
															'low_libido' => 'testosterone_assessment',
															'ed' => 'ed_treatment_assessment',
															'hot_flashes' => 'menopause_assessment',
															'mood_swings' => 'hormone_assessment',
															'hair_loss' => 'hair_assessment',
															'skin_issues' => 'skin_assessment',
															'anxiety' => 'health_optimization_assessment',
															'depression' => 'health_optimization_assessment',
															'brain_fog' => 'health_optimization_assessment',
															'memory_loss' => 'health_optimization_assessment',
															'joint_pain' => 'health_optimization_assessment',
															'muscle_weakness' => 'health_optimization_assessment'
														);
														
														$symptom_key = strtolower(str_replace(' ', '_', $flag_data['symptom_trigger']));
														$assessment_source = $symptom_assessment_map[$symptom_key] ?? 'symptom_assessment';
													} else {
														// If no symptom trigger, try to determine from biomarker category
														$biomarker_name = $flag_data['biomarker_name'] ?? '';
														if (!empty($biomarker_name)) {
															$category = get_biomarker_category($biomarker_name);
															// Map biomarker categories to assessment types
															$category_assessment_map = array(
																'Hormones' => 'hormone_assessment',
																'Thyroid' => 'hormone_assessment',
																'Advanced Cardiovascular' => 'health_optimization_assessment',
																'Complete Blood Count' => 'health_optimization_assessment',
																'Basic Metabolic Panel' => 'health_optimization_assessment',
																'Advanced Metabolic' => 'health_optimization_assessment',
																'Advanced Cognitive' => 'health_optimization_assessment',
																'Advanced Longevity' => 'health_optimization_assessment',
																'Advanced Performance' => 'health_optimization_assessment',
																'Performance' => 'health_optimization_assessment',
																'Advanced Energy' => 'health_optimization_assessment',
																'Physical Measurements' => 'health_optimization_assessment',
																'Lipid Panel' => 'health_optimization_assessment',
																'Electrolytes & Minerals' => 'health_optimization_assessment',
																'Heavy Metals & Toxicity' => 'health_optimization_assessment',
																'Protein Panel' => 'health_optimization_assessment'
															);
															$assessment_source = $category_assessment_map[$category] ?? 'health_optimization_assessment';
														} else {
															$assessment_source = 'health_optimization_assessment';
														}
													}
													break;
												default:
													$assessment_source = 'unknown_assessment';
													break;
											}
										}
										
										// Get the display name for the assessment source
										$assessment_display_name = get_assessment_display_name_from_source($assessment_source);
										
										// Ensure we have a valid display name and avoid generic names
										if (empty($assessment_display_name) || 
											$assessment_display_name === 'Unknown Assessment' || 
											$assessment_display_name === 'Symptom Assessment') {
											
											// Try to get assessment name from biomarker category
											$biomarker_name = $flag_data['biomarker_name'] ?? '';
											if (!empty($biomarker_name)) {
												$category = get_biomarker_category($biomarker_name);
												if (!empty($category) && $category !== 'Other') {
													$assessment_display_name = $category;
												} else {
													$assessment_display_name = 'Lab Results Analysis';
												}
											} else {
												$assessment_display_name = 'Lab Results Analysis';
											}
										}
										
										// Only add to grouped array if we have a meaningful assessment name
										if (!empty($assessment_display_name) && 
											$assessment_display_name !== 'Unknown Assessment' && 
											$assessment_display_name !== 'Symptom Assessment') {
											
											if (!isset($grouped_flagged[$assessment_display_name])) {
												$grouped_flagged[$assessment_display_name] = array();
											}
											$grouped_flagged[$assessment_display_name][$flag_id] = $flag_data;
										}
									}
									?>
									
									<div class="flagged-biomarkers-panel">
										<div class="biomarker-panel-header flagged-panel-header" onclick="togglePanel('flagged-biomarkers')">
											<div class="biomarker-panel-icon">
												<span>⚠️</span>
											</div>
											<div class="biomarker-panel-info">
												<div class="biomarker-panel-title">Symptom-Flagged Biomarkers</div>
												<div class="biomarker-panel-description">Biomarkers flagged based on your reported symptoms</div>
											</div>
											<div class="biomarker-panel-meta">
												<div class="biomarker-panel-count"><?php echo count($display_flagged_biomarkers); ?> flagged</div>
												<div class="biomarker-panel-toggle">
													<span class="panel-expand-icon">▼</span>
												</div>
											</div>
										</div>
										
										<div id="panel-content-flagged-biomarkers" class="biomarker-panel-content" style="display: none;">
											<?php foreach ($grouped_flagged as $assessment_name => $biomarkers) : 
												// Skip sections with empty assessment names only
												if (empty($assessment_name)) {
													error_log("Warning: Skipping flagged biomarkers with empty assessment name");
													continue;
												}
												
												// Get assessment icon and color - handle both assessment names and health vector categories
												$assessment_info = get_assessment_display_info($assessment_name);
												
												// If not a valid assessment name, treat as health vector category
												if (!isset($assessment_info['icon']) || ($assessment_info['icon'] === '🔬' && $assessment_name !== 'Unknown Assessment')) {
													// Use category icon instead
													$category_icon = get_category_icon($assessment_name);
													$assessment_info = array(
														'icon' => $category_icon['icon'] ?? '🔬',
														'color' => $category_icon['color'] ?? '#6366f1'
													);
												}
											?>
												<div class="biomarker-vector-category" data-panel="flagged-biomarkers" data-vector="<?php echo esc_attr($assessment_name); ?>">
													<div class="biomarker-vector-header" onclick="toggleVectorCategory('flagged-biomarkers', '<?php echo esc_attr($assessment_name); ?>')">
																											<div class="biomarker-vector-icon">
														<?php echo wp_kses_post($assessment_info['icon']); ?>
													</div>
														<div class="biomarker-vector-info">
															<div class="biomarker-vector-title"><?php echo esc_html($assessment_name); ?></div>
															<div class="biomarker-vector-count"><?php echo count($biomarkers); ?> flagged</div>
														</div>
														<div class="biomarker-vector-toggle">
															<span class="vector-expand-icon">▼</span>
														</div>
													</div>
													
													<div class="biomarker-vector-list">
														<?php foreach ($biomarkers as $flag_id => $biomarker_data) : 
															$biomarker_name = $biomarker_data['biomarker_name'];
														?>
															<div class="biomarker-list-item" data-biomarker="<?php echo esc_attr($biomarker_name); ?>" onclick="toggleBiomarkerMeasurements('flagged-biomarkers', '<?php echo esc_attr($assessment_name); ?>', '<?php echo esc_attr($biomarker_name); ?>')">
																<div class="biomarker-list-name">
																	<?php echo esc_html($biomarker_data['name'] ?? $biomarker_data['biomarker_name'] ?? 'Unknown Biomarker'); ?>
																	<span class="flagged-badge">⚠️ Flagged</span>
																	<?php if (!empty($biomarker_data['symptom_trigger'])) : ?>
																		<span class="symptom-trigger-badge"><?php echo esc_html($biomarker_data['symptom_trigger']); ?></span>
																	<?php endif; ?>
																	<?php if (!empty($biomarker_data['reason'])) : ?>
																		<span class="flag-reason-badge"><?php echo esc_html($biomarker_data['reason']); ?></span>
																	<?php endif; ?>
																</div>
																<div class="biomarker-list-meta">
																	<div class="biomarker-list-unit"><?php echo esc_html($biomarker_data['unit'] ?? ''); ?></div>
																	<div class="biomarker-list-flag-type" data-type="<?php 
																		$flag_type = $biomarker_data['flag_type'] ?? '';
																		$flag_type_display = '';
																		switch ($flag_type) {
																			case 'symptom_triggered':
																				$flag_type_display = 'symptom';
																				break;
																			case 'auto_flagged':
																				$flag_type_display = 'lab';
																				break;
																			case 'manual':
																				$flag_type_display = 'manual';
																				break;
																			case 'critical':
																				$flag_type_display = 'critical';
																				break;
																			default:
																				$flag_type_display = strtolower($flag_type);
																		}
																		echo esc_attr($flag_type_display);
																	?>">
																		<?php 
																		$flag_type = $biomarker_data['flag_type'] ?? '';
																		$flag_type_display = '';
																		switch ($flag_type) {
																			case 'symptom_triggered':
																				$flag_type_display = 'Symptom';
																				break;
																			case 'auto_flagged':
																				$flag_type_display = 'Lab';
																				break;
																			case 'manual':
																				$flag_type_display = 'Manual';
																				break;
																			case 'critical':
																				$flag_type_display = 'Critical';
																				break;
																			default:
																				$flag_type_display = ucfirst($flag_type);
																		}
																		echo esc_html($flag_type_display);
																		?>
																	</div>
																</div>
																<div class="biomarker-list-expand">▼</div>
															</div>
															
															<div id="biomarker-measurement-flagged-biomarkers-<?php echo esc_attr($assessment_name); ?>-<?php echo esc_attr($biomarker_name); ?>" class="biomarker-measurement-container" style="display: none;">
																<div class="biomarker-measurement-content">
																	<?php
																	// Get measurement data using the same method as regular biomarkers
																	$measurement_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data($biomarker_name, $user_id);
																	
																	// Render the measurement component
																	echo render_biomarker_measurement($measurement_data);
																	?>
																</div>
															</div>
														<?php endforeach; ?>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
									
								<?php else : ?>
									<div class="no-flagged-biomarkers">
										<div class="no-flagged-icon">✅</div>
										<h3>No Flagged Biomarkers</h3>
										<p>Great news! No biomarkers have been flagged based on your reported symptoms.</p>
									</div>
								<?php endif; ?>
							</div>
							
							<!-- MY BIOMARKER PANELS SECTION -->
							<div class="biomarker-panels-section">
								<div class="scores-title-container">
									<h2 class="scores-title">MY BIOMARKER PANELS</h2>
								</div>
								
								<!-- BIOMARKER PANELS - ENHANCED ORGANIZATION -->
									<?php
							// Load biomarker panels configuration
							$panels_config = include ENNU_LIFE_PLUGIN_PATH . 'includes/config/biomarker-panels.php';
							$panels = $panels_config['panels'];
							
							// Load core biomarkers from the Recommended Range Manager (AI Medical Team source)
							if (!isset($core_biomarkers) || empty($core_biomarkers)) {
								error_log("ENNU DEBUG: Loading biomarker data from Recommended Range Manager");
								$range_manager = new ENNU_Recommended_Range_Manager();
								$user_data = array(
									'age' => $user_age ?? 35,
									'gender' => $user_gender ?? 'male'
								);
								
								// Get all biomarker configurations from AI Medical Team
								$biomarker_config = $range_manager->get_biomarker_configuration();
								error_log("ENNU DEBUG: Loaded " . count($biomarker_config) . " biomarker configurations");
								
								// Convert to dashboard format
								$core_biomarkers = array();
								foreach ($biomarker_config as $biomarker_key => $biomarker_data) {
									// Get personalized range for this user
									$personalized_range = $range_manager->get_recommended_range($biomarker_key, $user_data);
									
									if (isset($personalized_range['error'])) {
										continue; // Skip biomarkers with errors
									}
									
									// Determine category based on biomarker type
									$category = get_biomarker_category($biomarker_key);
									
									if (!isset($core_biomarkers[$category])) {
										$core_biomarkers[$category] = array();
									}
									
									// Convert to dashboard format using personalized ranges
									$core_biomarkers[$category][$biomarker_key] = array(
										'unit' => $personalized_range['unit'] ?? '',
										'range' => $personalized_range['normal_min'] . '-' . $personalized_range['normal_max'],
										'optimal_range' => $personalized_range['optimal_min'] . '-' . $personalized_range['optimal_max'],
										'suboptimal_range' => ($personalized_range['normal_min'] ?? 0) . '-' . ($personalized_range['optimal_min'] ?? 0),
										'poor_range' => '0-' . ($personalized_range['normal_min'] ?? 0),
										'source' => 'ENNU AI Medical Team',
										'collection_method' => 'lab_test',
										'health_vectors' => array('General Health' => 0.8),
										'pillar_impact' => array('Body'),
										'frequency' => 'quarterly',
										'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
									);
								}
							}
									
							// Enhanced Panel Organization
							echo '<div class="biomarker-panels-container">';
							
							// Panel color mapping
							$panel_colors = array(
								'foundation_panel' => array('bg' => 'rgba(16, 185, 129, 0.1)', 'border' => 'rgba(16, 185, 129, 0.3)', 'accent' => '#10b981', 'icon' => '🏥'),
								'guardian_panel' => array('bg' => 'rgba(59, 130, 246, 0.1)', 'border' => 'rgba(59, 130, 246, 0.3)', 'accent' => '#3b82f6', 'icon' => '🧠'),
								'protector_panel' => array('bg' => 'rgba(239, 68, 68, 0.1)', 'border' => 'rgba(239, 68, 68, 0.3)', 'accent' => '#ef4444', 'icon' => '❤️'),
								'catalyst_panel' => array('bg' => 'rgba(245, 158, 11, 0.1)', 'border' => 'rgba(245, 158, 11, 0.3)', 'accent' => '#f59e0b', 'icon' => '⚡'),
								'detoxifier_panel' => array('bg' => 'rgba(34, 197, 94, 0.1)', 'border' => 'rgba(34, 197, 94, 0.3)', 'accent' => '#22c55e', 'icon' => '🌿'),
								'timekeeper_panel' => array('bg' => 'rgba(168, 85, 247, 0.1)', 'border' => 'rgba(168, 85, 247, 0.3)', 'accent' => '#a855f7', 'icon' => '⏰'),
								'hormone_optimization_panel' => array('bg' => 'rgba(236, 72, 153, 0.1)', 'border' => 'rgba(236, 72, 153, 0.3)', 'accent' => '#ec4899', 'icon' => '⚖️'),
								'cardiovascular_health_panel' => array('bg' => 'rgba(239, 68, 68, 0.1)', 'border' => 'rgba(239, 68, 68, 0.3)', 'accent' => '#ef4444', 'icon' => '💓'),
								'longevity_performance_panel' => array('bg' => 'rgba(14, 165, 233, 0.1)', 'border' => 'rgba(14, 165, 233, 0.3)', 'accent' => '#0ea5e9', 'icon' => '🏃'),
								'cognitive_energy_panel' => array('bg' => 'rgba(251, 146, 60, 0.1)', 'border' => 'rgba(251, 146, 60, 0.3)', 'accent' => '#fb923c', 'icon' => '💡'),
								'metabolic_optimization_panel' => array('bg' => 'rgba(99, 102, 241, 0.1)', 'border' => 'rgba(99, 102, 241, 0.3)', 'accent' => '#6366f1', 'icon' => '📊')
								);
								
							// Health vector category mapping
							$health_vector_categories = array(
								'cardiovascular' => array('name' => 'Cardiovascular Health', 'icon' => '❤️', 'color' => '#ef4444'),
								'endocrine' => array('name' => 'Endocrine System', 'icon' => '⚡', 'color' => '#10b981'),
								'immune' => array('name' => 'Immune Function', 'icon' => '🛡️', 'color' => '#3b82f6'),
								'nutritional' => array('name' => 'Nutritional Status', 'icon' => '🥗', 'color' => '#f59e0b'),
								'physical' => array('name' => 'Physical Performance', 'icon' => '💪', 'color' => '#a855f7'),
								'cognitive' => array('name' => 'Cognitive Health', 'icon' => '🧠', 'color' => '#ec4899'),
								'longevity' => array('name' => 'Longevity Markers', 'icon' => '⏰', 'color' => '#22c55e'),
								'performance' => array('name' => 'Performance Optimization', 'icon' => '🏃', 'color' => '#0ea5e9'),
								'inflammatory' => array('name' => 'Inflammatory Markers', 'icon' => '🔥', 'color' => '#fb923c'),
								'metabolic' => array('name' => 'Metabolic Health', 'icon' => '📊', 'color' => '#6366f1'),
								'comprehensive' => array('name' => 'Comprehensive Health', 'icon' => '🔬', 'color' => '#8b5cf6')
							);
							
							foreach ($panels as $panel_key => $panel_data) {
								$panel_colors_data = $panel_colors[$panel_key] ?? $panel_colors['foundation_panel'];
								
								echo '<div class="biomarker-panel biomarker-panel-' . esc_attr($panel_key) . '">';
								
								// Panel Header with Toggle
								echo '<div class="biomarker-panel-header" style="background: ' . esc_attr($panel_colors_data['bg']) . '; border: 1px solid ' . esc_attr($panel_colors_data['border']) . ';" onclick="togglePanel(\'' . esc_attr($panel_key) . '\')">';
								echo '<div class="biomarker-panel-icon">';
								echo wp_kses_post($panel_colors_data['icon']);
								echo '</div>';
								echo '<div class="biomarker-panel-info">';
								echo '<h3 class="biomarker-panel-title">' . esc_html($panel_data['name']) . '</h3>';
								echo '<p class="biomarker-panel-description">' . esc_html($panel_data['description']) . '</p>';
								echo '<div class="biomarker-panel-meta">';
								echo '<span class="biomarker-panel-count">' . esc_html($panel_data['biomarker_count']) . ' biomarkers</span>';
								echo '</div>';
								echo '</div>';
								echo '<div class="biomarker-panel-toggle">';
								echo '<span class="panel-expand-icon">▶</span>';
								echo '</div>';
									echo '</div>';
									
								// Panel Content - Group by Health Vectors
								echo '<div id="panel-content-' . esc_attr($panel_key) . '" class="biomarker-panel-content" style="display: none;">';
								
								// Get biomarkers for this panel
								$panel_biomarkers = isset($panel_data['biomarkers']) ? $panel_data['biomarkers'] : array();
									
								// Group biomarkers by health vector categories
								$vector_groups = array();
								foreach ($panel_biomarkers as $biomarker_key) {
									$category = get_biomarker_category($biomarker_key);
									if (!isset($vector_groups[$category])) {
										$vector_groups[$category] = array();
									}
									$vector_groups[$category][] = $biomarker_key;
								}
								
								// Display health vector categories
								foreach ($vector_groups as $vector_category => $biomarker_keys) {
									// Use biomarker categories instead of health vectors for display
									$category_display_name = $vector_category;
									$category_icon = get_category_icon($vector_category);
									$category_color = '#8b5cf6'; // Default purple
									
									// Map category names to display names and icons
									$category_display_map = array(
										'Physical Measurements' => array('name' => 'Physical Measurements', 'icon' => '📏', 'color' => '#3b82f6'),
										'Basic Metabolic Panel' => array('name' => 'Basic Metabolic Panel', 'icon' => '🩸', 'color' => '#ef4444'),
										'Electrolytes & Minerals' => array('name' => 'Electrolytes & Minerals', 'icon' => '⚡', 'color' => '#f59e0b'),
										'Lipid Panel' => array('name' => 'Lipid Panel', 'icon' => '❤️', 'color' => '#dc2626'),
										'Advanced Cardiovascular' => array('name' => 'Advanced Cardiovascular', 'icon' => '🫀', 'color' => '#b91c1c'),
										'Hormones' => array('name' => 'Hormones', 'icon' => '⚖️', 'color' => '#ec4899'),
										'Thyroid' => array('name' => 'Thyroid', 'icon' => '🦋', 'color' => '#8b5cf6'),
										'Complete Blood Count' => array('name' => 'Complete Blood Count', 'icon' => '🔬', 'color' => '#06b6d4'),
										'Advanced Metabolic' => array('name' => 'Advanced Metabolic', 'icon' => '🧬', 'color' => '#10b981'),
										'Advanced Cognitive' => array('name' => 'Advanced Cognitive', 'icon' => '🧠', 'color' => '#6366f1'),
										'Advanced Longevity' => array('name' => 'Advanced Longevity', 'icon' => '⏰', 'color' => '#22c55e'),
										'Advanced Performance' => array('name' => 'Advanced Performance', 'icon' => '🏃', 'color' => '#0ea5e9'),
										'Performance' => array('name' => 'Performance', 'icon' => '💪', 'color' => '#a855f7'),
										'Advanced Energy' => array('name' => 'Advanced Energy', 'icon' => '⚡', 'color' => '#fbbf24'),
										'Heavy Metals & Toxicity' => array('name' => 'Heavy Metals & Toxicity', 'icon' => '☠️', 'color' => '#dc2626'),
										'Protein Panel' => array('name' => 'Protein Panel', 'icon' => '🥚', 'color' => '#059669')
									);
									
									// Get display data for this category
									$display_data = isset($category_display_map[$vector_category]) ? $category_display_map[$vector_category] : array('name' => $vector_category, 'icon' => '🔬', 'color' => '#8b5cf6');
									
									echo '<div class="biomarker-vector-category">';
									echo '<div class="biomarker-vector-header">';
									echo '<div class="biomarker-vector-icon">';
									echo wp_kses_post($display_data['icon']);
									echo '</div>';
									echo '<div class="biomarker-vector-info">';
									echo '<h4 class="biomarker-vector-title">' . esc_html($display_data['name']) . '</h4>';
									echo '<p class="biomarker-vector-count">' . count($biomarker_keys) . ' biomarkers</p>';
									echo '</div>';
									echo '</div>';
									
									// Biomarkers list
									echo '<div class="biomarker-vector-list">';
									
									foreach ($biomarker_keys as $biomarker_key) {
										$biomarker_name = str_replace('_', ' ', $biomarker_key);
										$biomarker_name = ucwords($biomarker_name);
									
										echo '<div class="biomarker-list-item" onclick="toggleBiomarkerMeasurements(\'' . esc_attr($panel_key) . '\', \'' . esc_attr($vector_category) . '\', \'' . esc_attr($biomarker_key) . '\')">';
										echo '<span class="biomarker-list-name">' . esc_html($biomarker_name) . '</span>';
										
										// Get biomarker data
										if (isset($core_biomarkers[$vector_category][$biomarker_key])) {
											$biomarker_data = $core_biomarkers[$vector_category][$biomarker_key];
											echo '<span class="biomarker-list-unit">' . esc_html($biomarker_data['unit'] ?? '') . '</span>';
										}
										
									echo '<span class="biomarker-list-expand">▶</span>';
									echo '</div>';
									
										// Individual biomarker measurement container
										echo '<div id="biomarker-measurement-' . esc_attr($panel_key) . '-' . esc_attr($vector_category) . '-' . esc_attr($biomarker_key) . '" class="biomarker-measurement-container" style="display: none;">';
									echo '<div class="biomarker-measurement-content">';
									
									// Get measurement data for this specific biomarker
									$measurement_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data($biomarker_key, $user_id);
										
											// Render the measurement component
											echo render_biomarker_measurement($measurement_data);
									
									echo '</div>';
									echo '</div>';
								}
								
									echo '</div>'; // Close biomarker-vector-list
									echo '</div>'; // Close biomarker-vector-category
								}
								
								echo '</div>'; // Close biomarker-panel-content
								echo '</div>'; // Close biomarker-panel
							}
							
							echo '</div>'; // Close biomarker-panels-container
							?>
							

											

								
								<?php
								// Symptom name mapping for correlation system
								$symptom_name_mapping = array(
									'erectile_dysfunction' => 'Erectile Dysfunction',
									'weight_gain' => 'Weight Gain',
									'weight_loss' => 'Weight Loss',
									'hot_flashes' => 'Hot Flashes',
									'night_sweats' => 'Night Sweats',
									'irritability' => 'Irritability',
									'concentration_issues' => 'Concentration Issues',
									'headaches' => 'Headaches',
									'migraines' => 'Migraines',
									'joint_pain' => 'Joint Pain',
									'muscle_pain' => 'Muscle Pain',
									'back_pain' => 'Back Pain',
									'chest_pain' => 'Chest Pain',
									'palpitations' => 'Palpitations',
									'shortness_of_breath' => 'Shortness of Breath',
									'dizziness' => 'Dizziness',
									'lightheadedness' => 'Lightheadedness',
									'nausea' => 'Nausea',
									'vomiting' => 'Vomiting',
									'diarrhea' => 'Diarrhea',
									'constipation' => 'Constipation',
									'bloating' => 'Bloating',
									'gas' => 'Gas',
									'heartburn' => 'Heartburn',
									'acid_reflux' => 'Acid Reflux',
									'frequent_urination' => 'Frequent Urination',
									'urinary_incontinence' => 'Urinary Incontinence',
									'edema' => 'Edema',
									'swelling' => 'Swelling',
									'bruising' => 'Bruising',
									'slow_healing' => 'Slow Healing',
									'frequent_infections' => 'Frequent Infections',
									'fever' => 'Fever',
									'chills' => 'Chills',
									'nightmares' => 'Nightmares',
									'sleep_apnea' => 'Sleep Apnea',
									'restless_legs' => 'Restless Legs',
									'teeth_grinding' => 'Teeth Grinding',
									'jaw_pain' => 'Jaw Pain',
									'tinnitus' => 'Tinnitus',
									'hearing_loss' => 'Hearing Loss',
									'vision_changes' => 'Vision Changes',
									'blurred_vision' => 'Blurred Vision',
									'dry_eyes' => 'Dry Eyes',
									'eye_pain' => 'Eye Pain',
									'light_sensitivity' => 'Light Sensitivity',
									'noise_sensitivity' => 'Noise Sensitivity',
									'taste_changes' => 'Taste Changes',
									'smell_changes' => 'Smell Changes',
									'numbness' => 'Numbness',
									'tingling' => 'Tingling',
									'weakness' => 'Weakness',
									'tremors' => 'Tremors',
									'seizures' => 'Seizures',
									'confusion' => 'Confusion',
									'disorientation' => 'Disorientation',
									'hallucinations' => 'Hallucinations',
									'paranoia' => 'Paranoia',
									'mania' => 'Mania',
									'panic_attacks' => 'Panic Attacks',
									'phobias' => 'Phobias',
									'obsessive_thoughts' => 'Obsessive Thoughts',
									'compulsive_behaviors' => 'Compulsive Behaviors',
									'suicidal_thoughts' => 'Suicidal Thoughts',
									'self_harm' => 'Self Harm',
									'violence' => 'Violence',
									'aggression' => 'Aggression',
									'rage' => 'Rage',
									'impulsivity' => 'Impulsivity',
									'risk_taking' => 'Risk Taking'
								);
										
										// Initialize variables to prevent undefined variable warnings
										$user_symptoms = isset($user_symptoms) ? $user_symptoms : array();
										$symptom_correlations = isset($symptom_correlations) ? $symptom_correlations : array();
										
										// Load core biomarkers from configuration file
										if (!isset($core_biomarkers) || empty($core_biomarkers)) {
											$core_biomarkers = include( plugin_dir_path( __FILE__ ) . '../includes/config/ennu-life-core-biomarkers.php' );
										}
										
										$biomarker_variations = isset($biomarker_variations) ? $biomarker_variations : array();
										
										// Convert user symptom names to correlation file format
										$normalized_user_symptoms = array();
										foreach ( $user_symptoms as $symptom ) {
											if ( isset( $symptom_name_mapping[ $symptom ] ) ) {
												$normalized_user_symptoms[] = $symptom_name_mapping[ $symptom ];
											} else {
												// Try to convert underscore format to proper format
												$converted = str_replace( '_', ' ', $symptom );
												$converted = ucwords( $converted );
												$normalized_user_symptoms[] = $converted;
											}
										}
										
										// Use normalized symptoms for correlation matching
										$user_symptoms = $normalized_user_symptoms;
										
										// Create reverse mapping: biomarker -> symptoms that recommend it
										$biomarker_to_symptoms = array();
										foreach ( $symptom_correlations as $symptom => $recommended_biomarkers ) {
											foreach ( $recommended_biomarkers as $biomarker ) {
												if ( ! isset( $biomarker_to_symptoms[ $biomarker ] ) ) {
													$biomarker_to_symptoms[ $biomarker ] = array();
												}
												$biomarker_to_symptoms[ $biomarker ][] = $symptom;
											}
										}
										
										// REMOVED: Original biomarker-categories section (moved to top)
										
										// REMOVED: Duplicate biomarker display section (consolidated to top)
									echo '</div>';
								?>
											</div>
						</div>
					</div>
					
					</div>





</main>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		console.log('ENNU Dashboard: DOM loaded, initializing tabs...');
		
		// Theme system is now handled by the centralized ENNUThemeManager
		console.log('ENNU Dashboard: Theme management delegated to ENNUThemeManager');
		
		// Tab switching functionality
		const tabLinks = document.querySelectorAll('.my-story-tab-nav a');
		const tabContents = document.querySelectorAll('.my-story-tab-content');
		
		console.log('ENNU Dashboard: Found', tabLinks.length, 'tab links and', tabContents.length, 'tab contents');
		
		// Debug: Log all tab IDs
		tabContents.forEach((content, index) => {
			console.log('ENNU Dashboard: Tab content', index + 1, 'ID:', content.id);
		});
		
		tabLinks.forEach((link, index) => {
			console.log('ENNU Dashboard: Tab link', index + 1, 'href:', link.getAttribute('href'));
			link.addEventListener('click', function(e) {
				e.preventDefault();
				console.log('ENNU Dashboard: Tab clicked:', this.getAttribute('href'));
				
				// Remove active class from all tabs and contents
				tabLinks.forEach(l => l.classList.remove('my-story-tab-active'));
				tabContents.forEach(c => c.classList.remove('my-story-tab-active'));
				
				// Add active class to clicked tab
				this.classList.add('my-story-tab-active');
				console.log('ENNU Dashboard: Added active class to tab link');
				
				// Show corresponding content
				const targetId = this.getAttribute('href').substring(1);
				const targetContent = document.getElementById(targetId);
				console.log('ENNU Dashboard: Looking for target content with ID:', targetId);
				console.log('ENNU Dashboard: Target content found:', targetContent);
				
				if (targetContent) {
					targetContent.classList.add('my-story-tab-active');
					console.log('ENNU Dashboard: Activated tab content:', targetId);
					
					// Debug: Check if content is visible
					const computedStyle = window.getComputedStyle(targetContent);
					console.log('ENNU Dashboard: Tab content display style:', computedStyle.display);
					console.log('ENNU Dashboard: Tab content visibility:', computedStyle.visibility);
				} else {
					console.error('ENNU Dashboard: Target content not found:', targetId);
				}
			});
		});
		
		// Show My Biomarkers tab by default
		const biomarkersTabLink = document.querySelector('a[href="#tab-my-biomarkers"]');
		if (biomarkersTabLink) {
			biomarkersTabLink.click();
		} else if (tabLinks.length > 0) {
			// Fallback to first tab if biomarkers tab not found
			tabLinks[0].click();
		}
		
		// Initialize scroll reveal animations
		initializeScrollReveal();
		
		// Enhanced hover effects
		document.querySelectorAll('.animated-card, .program-card, .recommendation-card').forEach(card => {
			card.classList.add('hover-lift');
		});
		
		// Add focus-ring class to interactive elements
		document.querySelectorAll('.btn, .collapsible-header').forEach(element => {
			element.classList.add('focus-ring');
		});
		
		// Modern symptom item hover effects
		document.querySelectorAll('.symptom-item').forEach(item => {
			item.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '1';
			});
			
			item.addEventListener('mouseleave', function() {
				this.style.transform = 'translateY(0)';
				this.style.boxShadow = 'none';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '0';
			});
		});
		
		// Modern symptom stat cards hover effects
		document.querySelectorAll('.symptom-stat-card').forEach(card => {
			card.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '1';
			});
			
			card.addEventListener('mouseleave', function() {
				this.style.transform = 'translateY(0)';
				this.style.boxShadow = 'none';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '0';
			});
		});
		
		// Modern biomarker stat cards hover effects
		document.querySelectorAll('.biomarker-stat-card').forEach(card => {
			card.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '1';
			});
			
			card.addEventListener('mouseleave', function() {
				this.style.transform = 'translateY(0)';
				this.style.boxShadow = 'none';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '0';
			});
		});
		
		// Modern biomarker item hover effects
		document.querySelectorAll('.biomarker-item').forEach(item => {
			item.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '1';
			});
			
			item.addEventListener('mouseleave', function() {
				this.style.transform = 'translateY(0)';
				this.style.boxShadow = 'none';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '0';
			});
		});
		
		// Biomarker Measurement Component Functionality
		initializeBiomarkerMeasurements();
		
		// Initialize Chart.js charts (with error handling)
		try {
			// Charts are now initialized by the user-dashboard.js file
			// This prevents conflicts with the main dashboard initialization
			console.log('ENNU Dashboard: Chart initialization delegated to user-dashboard.js');
			
			// Initialize assessment charts with a slight delay to ensure DOM is ready
			setTimeout(function() {
				initializeAssessmentCharts();
			}, 500);
		} catch (chartError) {
			console.error('ENNU Dashboard: Error initializing charts:', chartError);
			// Continue execution - don't let chart errors break other functionality
		}
	});
	
	// Initialize biomarker measurement components
	function initializeBiomarkerMeasurements() {
		console.log('ENNU Dashboard: Initializing biomarker measurements...');
		
		// Add click handlers for info icons
		document.querySelectorAll('.biomarker-info-icon').forEach(icon => {
			icon.addEventListener('click', function(e) {
				e.preventDefault();
				const measurement = this.closest('.biomarker-measurement');
				const biomarkerId = measurement.dataset.biomarkerId;
				showBiomarkerDetails(biomarkerId);
			});
		});
		
		// Add click handlers for flag icons
		document.querySelectorAll('.biomarker-flag-icon').forEach(icon => {
			icon.addEventListener('click', function(e) {
				e.preventDefault();
				const measurement = this.closest('.biomarker-measurement');
				const biomarkerId = measurement.dataset.biomarkerId;
				showBiomarkerFlags(biomarkerId);
			});
		});
		
		// Add hover effects for markers
		document.querySelectorAll('.biomarker-current-marker, .biomarker-target-marker').forEach(marker => {
			marker.addEventListener('mouseenter', function() {
				this.style.transform = this.style.transform.replace('scale(1)', 'scale(1.2)');
			});
			
			marker.addEventListener('mouseleave', function() {
				this.style.transform = this.style.transform.replace('scale(1.2)', 'scale(1)');
			});
		});
		
		            // Initialize dynamic tooltips for biomarker rulers
            initializeBiomarkerRulerTooltips();
            
            // Add hover handlers for dots to hide dynamic tooltip
            initializeDotTooltipHandlers();
		
		// Add click handlers for measurement containers
		document.querySelectorAll('.biomarker-measurement').forEach(measurement => {
			measurement.addEventListener('click', function(e) {
				// Don't trigger if clicking on interactive elements
				if (e.target.closest('.biomarker-info-icon, .biomarker-flag-icon, .biomarker-current-marker, .biomarker-target-marker')) {
					return;
				}
				
				const biomarkerId = this.dataset.biomarkerId;
				showBiomarkerDetails(biomarkerId);
			});
		});
	}
	
	// Initialize assessment charts
	function initializeAssessmentCharts() {
		console.log('ENNU Dashboard: Initializing assessment charts...');
		
		// Check if Chart.js is available
		if (typeof Chart === 'undefined') {
			console.error('ENNU Dashboard: Chart.js is not loaded!');
			return;
		}
		
		console.log('ENNU Dashboard: Chart.js is available');
		
		// Find all assessment chart canvases
		const assessmentCharts = document.querySelectorAll('canvas[id^="assessmentChart_"]');
		console.log('ENNU Dashboard: Found', assessmentCharts.length, 'assessment chart canvases');
		
		assessmentCharts.forEach((canvas, index) => {
			console.log('ENNU Dashboard: Processing chart', index + 1, 'with ID:', canvas.id);
			const assessmentKey = canvas.id.replace('assessmentChart_', '');
			const chartCard = canvas.closest('.assessment-chart-card');
			
			if (!chartCard) return;
			
			// Show fallback while loading
			const fallback = document.getElementById('chartFallback_' + assessmentKey);
			if (fallback) {
				fallback.style.display = 'block';
			}
			
			// Get assessment data from the card
			const scoreElement = chartCard.querySelector('.overall-score-display span');
			const score = scoreElement ? parseFloat(scoreElement.textContent) : 0;
			
			// Get category data
			const categoryBars = chartCard.querySelectorAll('.category-bar-item');
			const categories = [];
			const scores = [];
			
			categoryBars.forEach(bar => {
				const categoryName = bar.querySelector('span').textContent;
				const categoryScore = parseFloat(bar.querySelector('span:last-child').textContent);
				categories.push(categoryName);
				scores.push(categoryScore);
			});
			
			// Create chart data
			const chartData = {
				labels: categories.length > 0 ? categories : ['Overall Score'],
				datasets: [{
					label: 'Assessment Scores',
					data: categories.length > 0 ? scores : [score],
					backgroundColor: categories.length > 0 ? 
						scores.map(s => s >= 8 ? 'rgba(16, 185, 129, 0.8)' : s >= 6 ? 'rgba(245, 158, 11, 0.8)' : 'rgba(239, 68, 68, 0.8)') :
						[score >= 8 ? 'rgba(16, 185, 129, 0.8)' : score >= 6 ? 'rgba(245, 158, 11, 0.8)' : 'rgba(239, 68, 68, 0.8)'],
					borderColor: categories.length > 0 ? 
						scores.map(s => s >= 8 ? 'rgba(16, 185, 129, 1)' : s >= 6 ? 'rgba(245, 158, 11, 1)' : 'rgba(239, 68, 68, 1)') :
						[score >= 8 ? 'rgba(16, 185, 129, 1)' : score >= 6 ? 'rgba(245, 158, 11, 1)' : 'rgba(239, 68, 68, 1)'],
					borderWidth: 2,
					borderRadius: 8,
					hoverBackgroundColor: categories.length > 0 ? 
						scores.map(s => s >= 8 ? 'rgba(16, 185, 129, 1)' : s >= 6 ? 'rgba(245, 158, 11, 1)' : 'rgba(239, 68, 68, 1)') :
						[score >= 8 ? 'rgba(16, 185, 129, 1)' : score >= 6 ? 'rgba(245, 158, 11, 1)' : 'rgba(239, 68, 68, 1)']
				}]
			};
			
			// Chart configuration
			const chartConfig = {
				type: categories.length > 0 ? 'bar' : 'doughnut',
				data: chartData,
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false
						},
						tooltip: {
							backgroundColor: 'rgba(0, 0, 0, 0.8)',
							titleColor: '#ffffff',
							bodyColor: '#ffffff',
							borderColor: '#e5e7eb',
							borderWidth: 1,
							cornerRadius: 8,
							displayColors: false,
							callbacks: {
								label: function(context) {
									return context.parsed.y || context.parsed + '/10';
								}
							}
						}
					},
					scales: categories.length > 0 ? {
						y: {
							beginAtZero: true,
							max: 10,
							ticks: {
								stepSize: 2,
								color: '#6b7280'
							},
							grid: {
								color: '#e5e7eb'
							}
						},
						x: {
							ticks: {
								color: '#6b7280',
								font: {
									size: 10
								}
							},
							grid: {
								display: false
							}
						}
					} : undefined,
					elements: {
						arc: {
							borderWidth: 0
						}
					}
				}
			};
			
			// Create the chart
			try {
				console.log('ENNU Dashboard: Creating chart for', assessmentKey, 'with config:', chartConfig);
				new Chart(canvas, chartConfig);
				console.log('ENNU Dashboard: Assessment chart initialized for', assessmentKey);
				
				// Hide fallback on success
				if (fallback) {
					fallback.style.display = 'none';
				}
			} catch (error) {
				console.error('ENNU Dashboard: Error creating assessment chart for', assessmentKey, error);
				console.error('ENNU Dashboard: Error details:', error.message);
				
				// Show error in fallback
				if (fallback) {
					fallback.innerHTML = '<div style="font-size: 2rem; margin-bottom: 0.5rem;">❌</div><div>Chart error</div>';
				}
			}
		});
	}
	
	// Dynamic tooltip functionality for biomarker rulers
	function initializeBiomarkerRulerTooltips() {
		document.querySelectorAll('.biomarker-range-ruler-container').forEach(container => {
			const tooltip = container.querySelector('.biomarker-dynamic-tooltip');
			const rangeLine = tooltip.querySelector('.tooltip-range-line');
			const valueLine = tooltip.querySelector('.tooltip-value-line');
			const ruler = container.querySelector('.biomarker-range-ruler');
			
			// Get range data from data attributes
			const criticalMin = parseFloat(container.dataset.criticalMin);
			const criticalMax = parseFloat(container.dataset.criticalMax);
			const normalMin = parseFloat(container.dataset.normalMin);
			const normalMax = parseFloat(container.dataset.normalMax);
			const optimalMin = parseFloat(container.dataset.optimalMin);
			const optimalMax = parseFloat(container.dataset.optimalMax);
			const unit = container.dataset.unit || '';
			
			                container.addEventListener('mousemove', function(e) {
                    // Don't show dynamic tooltip if hovering over a dot
                    if (container.getAttribute('data-dot-hover') === 'true') {
                        tooltip.style.display = 'none';
                        return;
                    }
                    
                    // Prevent any default tooltip behavior
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const rect = ruler.getBoundingClientRect();
                    const mouseX = e.clientX - rect.left;
                    const rulerWidth = rect.width;
                    const percentage = Math.max(0, Math.min(100, (mouseX / rulerWidth) * 100));
				
				// Calculate the biomarker value at this position
				const totalRange = criticalMax - criticalMin;
				const valueAtPosition = criticalMin + (percentage / 100) * totalRange;
				
				// Determine which range zone we're in
				let rangeType = 'Critical';
				let rangeClass = 'critical-range';
				
				if (valueAtPosition >= normalMin && valueAtPosition <= normalMax) {
					if (valueAtPosition >= optimalMin && valueAtPosition <= optimalMax) {
						rangeType = 'Optimal';
						rangeClass = 'optimal-range';
					} else {
						rangeType = 'Normal';
						rangeClass = 'normal-range';
					}
				}
				
				// Update tooltip content
				rangeLine.textContent = `Within ${rangeType} Range`;
				valueLine.textContent = `${valueAtPosition.toFixed(1)} ${unit}`;
				
				// Update tooltip styling
				tooltip.className = `biomarker-dynamic-tooltip ${rangeClass}`;
				
				                    // Position tooltip at mouse cursor relative to ruler
                    const tooltipX = mouseX;
                    const tooltipY = -25; // 25px above the ruler (lowered by 15px)
				
				tooltip.style.left = `${tooltipX}px`;
				tooltip.style.top = `${tooltipY}px`;
				tooltip.style.display = 'block';
			});
			
			                container.addEventListener('mouseleave', function() {
                    tooltip.style.display = 'none';
                });
                
                // Store reference to dynamic tooltip for dot hover handling
                container.dynamicTooltip = tooltip;
            });
        }
        
        // Handle dot tooltip interactions to prevent overlap
        function initializeDotTooltipHandlers() {
            // For current value dots
            document.querySelectorAll('.biomarker-current-dot').forEach(dot => {
                const container = dot.closest('.biomarker-range-ruler-container');
                
                dot.addEventListener('mouseenter', function() {
                    // Hide dynamic tooltip when hovering dot
                    if (container && container.dynamicTooltip) {
                        container.dynamicTooltip.style.display = 'none';
                        container.setAttribute('data-dot-hover', 'true');
                    }
                });
                
                dot.addEventListener('mouseleave', function() {
                    // Remove flag when leaving dot
                    if (container) {
                        container.removeAttribute('data-dot-hover');
                    }
                });
            });
            
            // For target value dots
            document.querySelectorAll('.biomarker-target-dot').forEach(dot => {
                const container = dot.closest('.biomarker-range-ruler-container');
                
                dot.addEventListener('mouseenter', function() {
                    // Hide dynamic tooltip when hovering dot
                    if (container && container.dynamicTooltip) {
                        container.dynamicTooltip.style.display = 'none';
                        container.setAttribute('data-dot-hover', 'true');
                    }
                });
                
                dot.addEventListener('mouseleave', function() {
                    // Remove flag when leaving dot
                    if (container) {
                        container.removeAttribute('data-dot-hover');
                    }
                });
            });
        }
	
	// Show biomarker details modal
	function showBiomarkerDetails(biomarkerId) {
		console.log('ENNU Dashboard: Showing details for biomarker:', biomarkerId);
		
		// Create modal content
		const modalContent = `
			<div class="biomarker-details-modal">
				<div class="modal-header">
					<h3>${biomarkerId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h3>
					<button class="modal-close" onclick="closeBiomarkerModal()">&times;</button>
				</div>
				<div class="modal-content">
					<p>Detailed information for ${biomarkerId} will be displayed here.</p>
					<p>This could include:</p>
					<ul>
						<li>Historical trends</li>
						<li>Health implications</li>
						<li>Optimization recommendations</li>
						<li>Related symptoms</li>
					</ul>
				</div>
			</div>
		`;
		
		// Show modal
		showModal(modalContent);
	}
	
			// Show biomarker flags modal
		function showBiomarkerFlags(biomarkerId) {
			console.log('ENNU Dashboard: Showing flags for biomarker:', biomarkerId);
			
			// Create modal content
			const modalContent = `
				<div class="biomarker-flags-modal">
					<div class="modal-header">
						<h3>Flags for ${biomarkerId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h3>
						<button class="modal-close" onclick="closeBiomarkerModal()">&times;</button>
					</div>
					<div class="modal-content">
						<p>Flagged biomarkers require attention:</p>
						<ul>
							<li>Review with healthcare provider</li>
							<li>Consider lifestyle changes</li>
							<li>Monitor for improvements</li>
							<li>Set appropriate targets</li>
						</ul>
					</div>
				</div>
			`;
			
			// Show modal
			showModal(modalContent);
		}

		// Toggle biomarker measurements visibility - REMOVED: Function now defined in user-dashboard.js

		// Master toggle for all biomarker categories - REMOVED: Function now defined in user-dashboard.js

	// Generic modal functionality
	function showModal(content) {
		// Remove existing modal
		const existingModal = document.querySelector('.biomarker-modal-overlay');
		if (existingModal) {
			existingModal.remove();
		}
		
		// Create modal overlay
		const modalOverlay = document.createElement('div');
		modalOverlay.className = 'biomarker-modal-overlay';
		modalOverlay.innerHTML = content;
		
		// Add to page
		document.body.appendChild(modalOverlay);
		
		// Add event listeners
		modalOverlay.addEventListener('click', function(e) {
			if (e.target === this) {
				closeBiomarkerModal();
			}
		});
		
		// Add escape key handler
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				closeBiomarkerModal();
			}
		});
		
		// Animate in
		setTimeout(() => {
			modalOverlay.style.opacity = '1';
			modalOverlay.querySelector('.biomarker-details-modal, .biomarker-flags-modal').style.transform = 'scale(1)';
		}, 10);
	}
	
	// Close biomarker modal
	function closeBiomarkerModal() {
		const modalOverlay = document.querySelector('.biomarker-modal-overlay');
		if (modalOverlay) {
			modalOverlay.style.opacity = '0';
			modalOverlay.querySelector('.biomarker-details-modal, .biomarker-flags-modal').style.transform = 'scale(0.9)';
			
			setTimeout(() => {
				modalOverlay.remove();
			}, 300);
		}
	}
	
	// Collapsible section functionality
	function toggleCollapsible(header) {
		const section = header.parentElement;
		const content = section.querySelector('.collapsible-content');
		const icon = header.querySelector('.collapsible-icon');
		
		if (section.classList.contains('expanded')) {
			// Collapse
			section.classList.remove('expanded');
			content.style.maxHeight = '0';
			content.style.opacity = '0';
			content.style.padding = '0 1.5rem';
		} else {
			// Expand
			section.classList.add('expanded');
			content.style.maxHeight = content.scrollHeight + 'px';
			content.style.opacity = '1';
			content.style.padding = '1.5rem';
		}
	}
	
	// Scroll reveal functionality
	function initializeScrollReveal() {
		const observerOptions = {
			threshold: 0.1,
			rootMargin: '0px 0px -50px 0px'
		};
		
		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					entry.target.classList.add('revealed');
				}
			});
		}, observerOptions);
		
		// Observe all scroll-reveal elements
		document.querySelectorAll('.scroll-reveal').forEach(el => {
			observer.observe(el);
		});
	}
</script>


<script>
// Symptoms Tab JavaScript
jQuery(document).ready(function($) {
	
	// Update symptoms button
	$('#update-symptoms').on('click', function() {
		const userId = $(this).data('user-id');
		const button = $(this);
		const originalText = button.text();
		
		button.text('Updating...').prop('disabled', true);
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ennu_update_symptoms',
				user_id: userId,
				nonce: ennu_ajax.nonce
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert('Error updating symptoms: ' + (response.data || 'Unknown error'));
				}
			},
			error: function() {
				alert('Error updating symptoms. Please try again.');
			},
			complete: function() {
				button.text(originalText).prop('disabled', false);
			}
		});
	});
	
	// Populate symptoms from assessments
	$('#populate-symptoms').on('click', function() {
		const userId = $(this).data('user-id');
		const button = $(this);
		const originalText = button.text();
		
		button.text('Extracting...').prop('disabled', true);
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ennu_populate_symptoms',
				user_id: userId,
				nonce: ennu_ajax.nonce
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert('Error extracting symptoms: ' + (response.data || 'Unknown error'));
				}
			},
			error: function() {
				alert('Error extracting symptoms. Please try again.');
			},
			complete: function() {
				button.text(originalText).prop('disabled', false);
			}
		});
	});
	
	// Clear symptom history
	$('#clear-symptoms').on('click', function() {
		if (!confirm('Are you sure you want to clear all symptom history? This action cannot be undone.')) {
			return;
		}
		
		const userId = $(this).data('user-id');
		const button = $(this);
		const originalText = button.text();
		
		button.text('Clearing...').prop('disabled', true);
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ennu_clear_symptom_history',
				user_id: userId,
				nonce: ennu_ajax.nonce
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert('Error clearing symptoms: ' + (response.data || 'Unknown error'));
				}
			},
			error: function() {
				alert('Error clearing symptoms. Please try again.');
			},
			complete: function() {
				button.text(originalText).prop('disabled', false);
			}
		});
	});
	
			// Auto-refresh symptoms when tab is shown
		$('a[href="#tab-my-symptoms"]').on('click', function() {
			// Refresh symptoms data every 30 seconds when tab is active
			setInterval(function() {
				if ($('#tab-my-symptoms').is(':visible')) {
					refreshSymptomsData();
				}
			}, 30000);
		});
		

	
	function refreshSymptomsData() {
		const userId = $('.symptoms-actions button').first().data('user-id');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ennu_get_symptoms_data',
				user_id: userId,
				nonce: ennu_ajax.nonce
			},
			success: function(response) {
				if (response.success) {
					updateSymptomsDisplay(response.data);
				}
			}
		});
	}
	
	function updateSymptomsDisplay(data) {
		// Update statistics
		$('#total-symptoms-count').text(data.total_symptoms || 0);
		$('#active-symptoms-count').text(data.active_symptoms || 0);
		$('#biomarker-correlations').text(data.biomarker_correlations || 0);
		$('#trending-symptoms').text(data.trending_symptoms || 0);
	}
	
});
</script>
