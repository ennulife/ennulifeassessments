<?php
/**
 * Biomarkers Only Template
 *
 * @package ENNU Life Assessments
 * @since 64.5.20
 * 
 * This template shares all resources with the main user dashboard:
 * - Same CSS and JavaScript files (loaded via enqueue_frontend_scripts)
 * - Same helper functions (defined in user-dashboard.php)
 * - Same inline JavaScript for toggle functionality
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Debug: Confirm template is loading
error_log( 'ENNU Biomarkers Template: Template is loading for user ' . get_current_user_id() );

// Ensure user_id is available
if ( ! isset( $user_id ) ) {
	$user_id = get_current_user_id();
}

// Debug: Check if biomarkers manager is available
if ( ! class_exists( 'ENNU_Biomarker_Manager' ) ) {
	error_log( 'ENNU Biomarkers Template: ENNU_Biomarker_Manager lookclass not found' );
} else {
	error_log( 'ENNU Biomarkers Template: ENNU_Biomarker_Manager class found' );
}

// Debug: Add a simple test output to confirm template is executing
echo '<!-- ENNU Biomarkers Template: Template is executing for user ' . $user_id . ' -->';

// Ensure biomarker auto-sync is triggered for weight and BMI (same as user-dashboard.php)
if ( class_exists( 'ENNU_Biomarker_Auto_Sync' ) && is_user_logged_in() ) {
	$auto_sync = new ENNU_Biomarker_Auto_Sync();
	$auto_sync->ensure_biomarker_sync();
}

// Add inline JavaScript for toggle functions to ensure they're available immediately (same as user-dashboard.php)
?>
<style>
/* Ensure basic biomarkers styling is applied */
.biomarkers-container {
    margin: 0 auto;
    max-width: 1200px;
    width: 100%;
    padding: 0;
}

.biomarker-panel {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: var(--rounded-lg, 8px);
    margin-bottom: 1rem;
    overflow: hidden;
}

.biomarker-panel-header {
    background: var(--panel-header-bg, #f8fafc);
    padding: 1rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.biomarker-panel-content {
    padding: 1rem;
}

.biomarker-list-item {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.biomarker-measurement {
    padding: 1rem;
    background: var(--measurement-bg, #f9fafb);
    border-radius: var(--rounded-md, 6px);
    margin-top: 0.5rem;
}

.measurement-value {
    font-size: 1.25rem;
    font-weight: bold;
    color: var(--text-primary, #1f2937);
}

.measurement-range {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-secondary, #6b7280);
}
</style>
<script type="text/javascript">
// Inline toggle functions - available immediately (shared with user-dashboard.php)
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

// Essential helper functions for biomarkers display
if ( ! function_exists( 'get_biomarker_category' ) ) {
	function get_biomarker_category($biomarker_key) {
		$categories = array(
			// Physical Measurements
			'height' => 'Physical Measurements',
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
			
			// Basic Metabolic Panel
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
			
			// Electrolytes & Minerals
			'sodium' => 'Electrolytes & Minerals',
			'potassium' => 'Electrolytes & Minerals',
			'chloride' => 'Electrolytes & Minerals',
			'carbon_dioxide' => 'Electrolytes & Minerals',
			'calcium' => 'Electrolytes & Minerals',
			'magnesium' => 'Electrolytes & Minerals',
			'phosphorus' => 'Electrolytes & Minerals',
			
			// Protein Panel
			'protein' => 'Protein Panel',
			'albumin' => 'Protein Panel',
			
			// Lipid Panel
			'cholesterol' => 'Lipid Panel',
			'hdl' => 'Lipid Panel',
			'ldl' => 'Lipid Panel',
			'vldl' => 'Lipid Panel',
			'triglycerides' => 'Lipid Panel',
			'total_cholesterol' => 'Lipid Panel',
			
			// Advanced Cardiovascular
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
			
			// Hormones
			'testosterone_total' => 'Hormones',
			'testosterone_free' => 'Hormones',
			'free_testosterone' => 'Hormones',
			'estradiol' => 'Hormones',
			'progesterone' => 'Hormones',
			'cortisol' => 'Hormones',
			'dhea_s' => 'Hormones',
			'shbg' => 'Hormones',
			'fsh' => 'Hormones',
			'lh' => 'Hormones',
			'prolactin' => 'Hormones',
			'insulin' => 'Hormones',
			'c_peptide' => 'Hormones',
			
			// Thyroid
			'tsh' => 'Thyroid',
			'free_t3' => 'Thyroid',
			'free_t4' => 'Thyroid',
			'reverse_t3' => 'Thyroid',
			'tpo_antibodies' => 'Thyroid',
			'tg_antibodies' => 'Thyroid',
			
			// Complete Blood Count
			'hemoglobin' => 'Complete Blood Count',
			'hematocrit' => 'Complete Blood Count',
			'wbc' => 'Complete Blood Count',
			'rbc' => 'Complete Blood Count',
			'platelets' => 'Complete Blood Count',
			'mcv' => 'Complete Blood Count',
			'mch' => 'Complete Blood Count',
			'mchc' => 'Complete Blood Count',
			'rdw' => 'Complete Blood Count',
			'mpv' => 'Complete Blood Count',
			'neutrophils' => 'Complete Blood Count',
			'lymphocytes' => 'Complete Blood Count',
			'monocytes' => 'Complete Blood Count',
			'eosinophils' => 'Complete Blood Count',
			'basophils' => 'Complete Blood Count',
			
			// Advanced markers
			'vitamin_d' => 'Advanced Metabolic',
			'vitamin_b12' => 'Advanced Metabolic',
			'folate' => 'Advanced Metabolic',
			'iron' => 'Advanced Metabolic',
			'ferritin' => 'Advanced Metabolic',
			'tibc' => 'Advanced Metabolic',
			'uibc' => 'Advanced Metabolic',
			'transferrin_saturation' => 'Advanced Metabolic',
			'zinc' => 'Electrolytes & Minerals',
			'copper' => 'Electrolytes & Minerals',
			'selenium' => 'Electrolytes & Minerals',
			'iodine' => 'Electrolytes & Minerals',
			'chromium' => 'Electrolytes & Minerals',
			'manganese' => 'Electrolytes & Minerals',
			'molybdenum' => 'Electrolytes & Minerals',
			'vanadium' => 'Electrolytes & Minerals',
			'boron' => 'Electrolytes & Minerals',
			'silicon' => 'Electrolytes & Minerals',
			'nickel' => 'Electrolytes & Minerals',
			'cobalt' => 'Electrolytes & Minerals',
			'strontium' => 'Electrolytes & Minerals',
			'barium' => 'Electrolytes & Minerals',
			'rubidium' => 'Electrolytes & Minerals',
			'cesium' => 'Electrolytes & Minerals',
			'lithium' => 'Electrolytes & Minerals',
			'germanium' => 'Electrolytes & Minerals',
			'titanium' => 'Electrolytes & Minerals',
			'aluminum' => 'Heavy Metals & Toxicity',
			'arsenic' => 'Heavy Metals & Toxicity',
			'cadmium' => 'Heavy Metals & Toxicity',
			'lead' => 'Heavy Metals & Toxicity',
			'mercury' => 'Heavy Metals & Toxicity',
			'thallium' => 'Heavy Metals & Toxicity',
			'uranium' => 'Heavy Metals & Toxicity',
			'plutonium' => 'Heavy Metals & Toxicity',
			'americium' => 'Heavy Metals & Toxicity',
			'curium' => 'Heavy Metals & Toxicity',
			'berkelium' => 'Heavy Metals & Toxicity',
			'californium' => 'Heavy Metals & Toxicity',
			'einsteinium' => 'Heavy Metals & Toxicity',
			'fermium' => 'Heavy Metals & Toxicity',
			'mendelevium' => 'Heavy Metals & Toxicity',
			'nobelium' => 'Heavy Metals & Toxicity',
			'lawrencium' => 'Heavy Metals & Toxicity',
			'rutherfordium' => 'Heavy Metals & Toxicity',
			'dubnium' => 'Heavy Metals & Toxicity',
			'seaborgium' => 'Heavy Metals & Toxicity',
			'bohrium' => 'Heavy Metals & Toxicity',
			'hassium' => 'Heavy Metals & Toxicity',
			'meitnerium' => 'Heavy Metals & Toxicity',
			'darmstadtium' => 'Heavy Metals & Toxicity',
			'roentgenium' => 'Heavy Metals & Toxicity',
			'copernicium' => 'Heavy Metals & Toxicity',
			'nihonium' => 'Heavy Metals & Toxicity',
			'flerovium' => 'Heavy Metals & Toxicity',
			'moscovium' => 'Heavy Metals & Toxicity',
			'livermorium' => 'Heavy Metals & Toxicity',
			'tennessine' => 'Heavy Metals & Toxicity',
			'oganesson' => 'Heavy Metals & Toxicity'
		);
		
		return isset($categories[$biomarker_key]) ? $categories[$biomarker_key] : 'Other';
	}
}

if ( ! function_exists( 'get_category_icon' ) ) {
	function get_category_icon($category) {
		$icons = array(
			'Physical Measurements' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>',
			'Basic Metabolic Panel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>',
			'Electrolytes & Minerals' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
			'Protein Panel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
			'Lipid Panel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
			'Hormones' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
			'Thyroid' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/><path d="M8 14h8"/></svg>',
			'Complete Blood Count' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"/><path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"/><path d="M12 3c0 1-1 2-2 2s-2-1-2-2 1-2 2-2 2 1 2 2z"/><path d="M12 21c0-1 1-2 2-2s2 1 2 2-1 2-2 2-2-1-2-2z"/></svg>',
			'Advanced Cardiovascular' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/><path d="M12 8v8M8 12h8"/></svg>',
			'Advanced Metabolic' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/><path d="M12 8v8M8 12h8"/></svg>',
			'Heavy Metals & Toxicity' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
		);
		
		return isset($icons[$category]) ? $icons[$category] : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>';
	}
}

if ( ! function_exists( 'get_assessment_display_name_from_source' ) ) {
	function get_assessment_display_name_from_source($assessment_source) {
		$display_names = array(
			'health_optimization_assessment' => 'Health Optimization',
			'weight_loss_assessment' => 'Weight Loss',
			'hormone_assessment' => 'Hormone Optimization',
			'sleep_assessment' => 'Sleep Optimization',
			'skin_assessment' => 'Skin Health',
			'hair_assessment' => 'Hair Health',
			'ed_treatment_assessment' => 'ED Treatment',
			'menopause_assessment' => 'Menopause',
			'testosterone_assessment' => 'Testosterone',
			'auto_flagged' => 'Lab Results Analysis',
			'manual' => 'Manual Review',
			'critical' => 'Critical Alert',
			'symptom_assessment' => 'Symptom Assessment',
			'unknown_assessment' => 'Lab Results Analysis'
		);
		
		return isset($display_names[$assessment_source]) ? $display_names[$assessment_source] : 'Lab Results Analysis';
	}
}

if ( ! function_exists( 'get_assessment_display_info' ) ) {
	function get_assessment_display_info($assessment_name) {
		$assessment_info = array(
			'Health Optimization' => array('icon' => '🔬', 'color' => '#6366f1'),
			'Weight Loss' => array('icon' => '⚖️', 'color' => '#10b981'),
			'Hormone Optimization' => array('icon' => '⚖️', 'color' => '#ec4899'),
			'Sleep Optimization' => array('icon' => '😴', 'color' => '#8b5cf6'),
			'Skin Health' => array('icon' => '✨', 'color' => '#f59e0b'),
			'Hair Health' => array('icon' => '💇', 'color' => '#059669'),
			'ED Treatment' => array('icon' => '💊', 'color' => '#dc2626'),
			'Menopause' => array('icon' => '🌡️', 'color' => '#ec4899'),
			'Testosterone' => array('icon' => '💪', 'color' => '#3b82f6'),
			'Lab Results Analysis' => array('icon' => '🔬', 'color' => '#6366f1'),
			'Manual Review' => array('icon' => '👁️', 'color' => '#f59e0b'),
			'Critical Alert' => array('icon' => '🚨', 'color' => '#dc2626'),
			'Symptom Assessment' => array('icon' => '🏥', 'color' => '#10b981')
		);
		
		return isset($assessment_info[$assessment_name]) ? $assessment_info[$assessment_name] : array('icon' => '🔬', 'color' => '#6366f1');
	}
}

// Helper function to calculate percentage position for biomarker ruler
if ( ! function_exists( 'calculate_percentage_position' ) ) {
	function calculate_percentage_position($value, $min_range, $max_range) {
		// Validate inputs - ensure they are numeric and convert to float
		if (!is_numeric($value) || !is_numeric($min_range) || !is_numeric($max_range)) {
			return 50; // Default to middle if any value is not numeric
		}
		
		// Convert to float to ensure proper numeric operations
		$value = (float)$value;
		$min_range = (float)$min_range;
		$max_range = (float)$max_range;
		
		// Additional safety check after conversion
		if (!is_finite($value) || !is_finite($min_range) || !is_finite($max_range)) {
			return 50; // Default to middle if any value is not finite
		}
		
		if ($max_range <= $min_range) {
			return 50; // Default to middle if range is invalid
		}
		
		$position = (($value - $min_range) / ($max_range - $min_range)) * 100;
		
		// Clamp to 0-100 range
		return max(0, min(100, $position));
	}
}

if ( ! function_exists( 'render_biomarker_measurement' ) ) {
	function render_biomarker_measurement($measurement_data) {
		if (empty($measurement_data) || !is_array($measurement_data)) {
			return '<div class="biomarker-measurement-fallback">No measurement data available</div>';
		}
		
		// Check for error in measurement data
		if (isset($measurement_data['error'])) {
			return '<div class="biomarker-measurement-error">Error: ' . esc_html($measurement_data['error']) . '</div>';
		}
		
		// Get biomarker data for ruler calculation
		$biomarker_id = $measurement_data['biomarker_id'] ?? '';
		
		// Skip processing if biomarker_id is empty, null, or "unknown"
		if (empty($biomarker_id) || $biomarker_id === 'unknown' || trim($biomarker_id) === '') {
			return '<div class="biomarker-error">Invalid biomarker identifier</div>';
		}
		
		// Get proper display name using fallback system
		$display_name = $measurement_data['display_name'] ?? ENNU_Biomarker_Manager::get_fallback_display_name($biomarker_id) ?? ucwords(str_replace('_', ' ', $biomarker_id));
		$current_value = $measurement_data['current_value'] ?? null;
		$target_value = $measurement_data['target_value'] ?? null;
		$unit = $measurement_data['unit'] ?? '';
		
		// Enhanced data retrieval for ALL biomarkers from user meta fields
		$user_id = get_current_user_id();
		
		// First, try to get data from the biomarker manager (primary source)
		if (class_exists('ENNU_Biomarker_Manager')) {
			$biomarker_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data($user_id, $biomarker_id);
			if ($biomarker_data && !isset($biomarker_data['error'])) {
				// Use data from biomarker manager if available
				$current_value = $biomarker_data['current_value'] ?? $current_value;
				$target_value = $biomarker_data['target_value'] ?? $target_value;
				$unit = $biomarker_data['unit'] ?? $unit;
			}
		}
		
		// For physical measurements, also check global fields as backup
		if (in_array($biomarker_id, ['weight', 'height', 'bmi', 'age', 'gender'])) {
			switch ($biomarker_id) {
				case 'weight':
					// Get weight from various sources
					if (empty($current_value)) {
						$current_value = get_user_meta($user_id, 'ennu_global_weight', true);
					}
					if (empty($current_value)) {
						$height_weight_data = get_user_meta($user_id, 'ennu_global_height_weight', true);
						if (!empty($height_weight_data)) {
							$parsed_data = maybe_unserialize($height_weight_data);
							if (is_array($parsed_data) && isset($parsed_data['weight'])) {
								$current_value = floatval($parsed_data['weight']);
							}
						}
					}
					
					// Get target weight from weight loss assessment
					if (empty($target_value) && class_exists('ENNU_Target_Weight_Calculator')) {
						$target_data = ENNU_Target_Weight_Calculator::calculate_target_weight($user_id);
						if ($target_data && isset($target_data['target_weight'])) {
							$target_value = $target_data['target_weight'];
						}
					}
					break;
					
				case 'height':
					if (empty($current_value)) {
						$current_value = get_user_meta($user_id, 'ennu_global_height', true);
					}
					if (empty($current_value)) {
						$height_weight_data = get_user_meta($user_id, 'ennu_global_height_weight', true);
						if (!empty($height_weight_data)) {
							$parsed_data = maybe_unserialize($height_weight_data);
							if (is_array($parsed_data) && isset($parsed_data['height'])) {
								$current_value = floatval($parsed_data['height']);
							}
						}
					}
					break;
					
				case 'bmi':
					if (empty($current_value)) {
						$current_value = get_user_meta($user_id, 'ennu_global_bmi', true);
					}
					if (empty($current_value)) {
						// Calculate BMI from height and weight
						$height = get_user_meta($user_id, 'ennu_global_height', true);
						$weight = get_user_meta($user_id, 'ennu_global_weight', true);
						if (!empty($height) && !empty($weight)) {
							$current_value = ($weight / ($height * $height)) * 703; // Convert to BMI
						}
					}
					break;
					
				case 'age':
					if (empty($current_value)) {
						$current_value = get_user_meta($user_id, 'ennu_global_exact_age', true);
					}
					if (empty($current_value)) {
						$dob = get_user_meta($user_id, 'ennu_global_date_of_birth', true);
						if (!empty($dob)) {
							$birth_date = new DateTime($dob);
							$current_date = new DateTime();
							$current_value = $current_date->diff($birth_date)->y;
						}
					}
					break;
					
				case 'gender':
					if (empty($current_value)) {
						$current_value = get_user_meta($user_id, 'ennu_global_gender', true);
					}
					break;
			}
		}
		
		// For all other biomarkers, check additional sources
		if (empty($current_value)) {
			// Check ennu_biomarker_data
			$biomarker_data = get_user_meta($user_id, 'ennu_biomarker_data', true);
			if (is_array($biomarker_data) && isset($biomarker_data[$biomarker_id])) {
				$current_value = $biomarker_data[$biomarker_id]['value'] ?? null;
				$unit = $biomarker_data[$biomarker_id]['unit'] ?? $unit;
			}
		}
		
		if (empty($current_value)) {
			// Check ennu_user_biomarkers (auto-sync data)
			$auto_sync_data = get_user_meta($user_id, 'ennu_user_biomarkers', true);
			if (is_array($auto_sync_data) && isset($auto_sync_data[$biomarker_id])) {
				$current_value = $auto_sync_data[$biomarker_id]['value'] ?? null;
				$unit = $auto_sync_data[$biomarker_id]['unit'] ?? $unit;
			}
		}
		
		// Check for admin overrides
		if (empty($current_value)) {
			$admin_override = get_user_meta($user_id, "ennu_biomarker_override_{$biomarker_id}", true);
			if (!empty($admin_override)) {
				$current_value = $admin_override;
			}
		}
		
		// Get recommended ranges for ruler
		$range_manager = new ENNU_Recommended_Range_Manager();
		$user_data = array(
			'age' => get_user_meta(get_current_user_id(), 'ennu_global_exact_age', true) ?: 35,
			'gender' => get_user_meta(get_current_user_id(), 'ennu_global_gender', true) ?: 'male'
		);
		$recommended_range = $range_manager->get_recommended_range($biomarker_id, $user_data);
		
		if (isset($recommended_range['error'])) {
			// Fallback to simple display if ranges not available
			return render_simple_biomarker_measurement($measurement_data);
		}
		
		// Extract range values
		$critical_min = $recommended_range['critical_min'] ?? 0;
		$critical_max = $recommended_range['critical_max'] ?? 100;
		$normal_min = $recommended_range['normal_min'] ?? 25;
		$normal_max = $recommended_range['normal_max'] ?? 75;
		$optimal_min = $recommended_range['optimal_min'] ?? 40;
		$optimal_max = $recommended_range['optimal_max'] ?? 60;
		
		// Calculate percentage positions for ruler segments
		$normal_start_pos = calculate_percentage_position($normal_min, $critical_min, $critical_max);
		$normal_end_pos = calculate_percentage_position($normal_max, $critical_min, $critical_max);
		$optimal_start_pos = calculate_percentage_position($optimal_min, $critical_min, $critical_max);
		$optimal_end_pos = calculate_percentage_position($optimal_max, $critical_min, $critical_max);
		
		// Calculate current and target positions
		$current_ruler_position = null;
		$target_ruler_position = null;
		$has_user_data = false;
		
		if ($current_value !== null && is_numeric($current_value) && $current_value !== '') {
			$current_ruler_position = calculate_percentage_position($current_value, $critical_min, $critical_max);
			$has_user_data = true;
		}
		
		if ($target_value !== null && is_numeric($target_value) && $target_value !== '') {
			$target_ruler_position = calculate_percentage_position($target_value, $critical_min, $critical_max);
		}
		
		// Debug info for troubleshooting
		$debug_info = array(
			'biomarker_id' => $biomarker_id,
			'current_value' => $current_value,
			'target_value' => $target_value,
			'current_position' => $current_ruler_position,
			'target_position' => $target_ruler_position,
			'ranges' => $recommended_range
		);
		
		$output = '<div class="biomarker-measurement">';
		$output .= '<div class="measurement-header">';
		$output .= '<h4>' . esc_html($display_name) . '</h4>';
		$output .= '</div>';
		
		// Enhanced Range Ruler
		$output .= '<div class="biomarker-range-ruler-container" 
			data-critical-min="' . esc_attr($critical_min) . '"
			data-critical-max="' . esc_attr($critical_max) . '"
			data-normal-min="' . esc_attr($normal_min) . '"
			data-normal-max="' . esc_attr($normal_max) . '"
			data-optimal-min="' . esc_attr($optimal_min) . '"
			data-optimal-max="' . esc_attr($optimal_max) . '"
			data-unit="' . esc_attr($unit) . '"
			data-biomarker-id="' . esc_attr($biomarker_id) . '"
			data-debug="' . esc_attr(json_encode($debug_info)) . '">';
		
		// Dynamic 2-Line Tooltip
		$output .= '<div class="biomarker-dynamic-tooltip" style="display: none;">';
		$output .= '<div class="tooltip-range-line"></div>';
		$output .= '<div class="tooltip-value-line"></div>';
		$output .= '</div>';
		
		// Ruler Bar with Notch Marks
		$output .= '<div class="biomarker-range-ruler">';
		
		// Critical Range (Red)
		$output .= '<div class="biomarker-range-segment critical-range" style="left: 0%; width: 100%;"></div>';
		
		// Normal Range (Yellow)
		$output .= '<div class="biomarker-range-segment normal-range" 
			style="left: ' . esc_attr($normal_start_pos) . '%; width: ' . esc_attr($normal_end_pos - $normal_start_pos) . '%;"></div>';
		
		// Optimal Range (Green)
		$output .= '<div class="biomarker-range-segment optimal-range" 
			style="left: ' . esc_attr($optimal_start_pos) . '%; width: ' . esc_attr($optimal_end_pos - $optimal_start_pos) . '%;"></div>';
		
		// Ruler Notch Marks
		$output .= '<div class="biomarker-ruler-notches">';
		$output .= '<div class="biomarker-notch critical-min" style="left: 0%;" title="Critical Min: ' . esc_attr($critical_min . ' ' . $unit) . '"></div>';
		$output .= '<div class="biomarker-notch normal-min" style="left: ' . esc_attr($normal_start_pos) . '%;" title="Normal Min: ' . esc_attr($normal_min . ' ' . $unit) . '"></div>';
		$output .= '<div class="biomarker-notch optimal-min" style="left: ' . esc_attr($optimal_start_pos) . '%;" title="Optimal Min: ' . esc_attr($optimal_min . ' ' . $unit) . '"></div>';
		$output .= '<div class="biomarker-notch optimal-max" style="left: ' . esc_attr($optimal_end_pos) . '%;" title="Optimal Max: ' . esc_attr($optimal_max . ' ' . $unit) . '"></div>';
		$output .= '<div class="biomarker-notch normal-max" style="left: ' . esc_attr($normal_end_pos) . '%;" title="Normal Max: ' . esc_attr($normal_max . ' ' . $unit) . '"></div>';
		$output .= '<div class="biomarker-notch critical-max" style="left: 100%;" title="Critical Max: ' . esc_attr($critical_max . ' ' . $unit) . '"></div>';
		$output .= '</div>';
		
		// X-axis Interval Labels
		$output .= '<div style="position: absolute; bottom: -25px; left: 0; width: 100%; height: 20px; pointer-events: none;">';
		$output .= '<div style="position: absolute; font-size: 10px; color: #dc2626; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: 0%;">' . esc_html($critical_min . ' ' . $unit) . '</div>';
		$output .= '<div style="position: absolute; font-size: 10px; color: #ea580c; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: ' . esc_attr($normal_start_pos) . '%;">' . esc_html($normal_min . ' ' . $unit) . '</div>';
		$output .= '<div style="position: absolute; font-size: 10px; color: #16a34a; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: ' . esc_attr($optimal_start_pos) . '%;">' . esc_html($optimal_min . ' ' . $unit) . '</div>';
		$output .= '<div style="position: absolute; font-size: 10px; color: #16a34a; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: ' . esc_attr($optimal_end_pos) . '%;">' . esc_html($optimal_max . ' ' . $unit) . '</div>';
		$output .= '<div style="position: absolute; font-size: 10px; color: #ea580c; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: ' . esc_attr($normal_end_pos) . '%;">' . esc_html($normal_max . ' ' . $unit) . '</div>';
		$output .= '<div style="position: absolute; font-size: 10px; color: #dc2626; font-weight: 600; text-align: center; transform: translateX(-50%); white-space: nowrap; left: 100%;">' . esc_html($critical_max . ' ' . $unit) . '</div>';
		$output .= '</div>';
		
		// Current Value Dot
		if ($has_user_data && $current_ruler_position !== null) {
			$output .= '<div class="biomarker-current-dot" 
				style="left: ' . esc_attr($current_ruler_position) . '%;"
				data-biomarker-name="' . esc_attr($display_name) . '"
				data-value="' . esc_attr($current_value) . '"
				data-unit="' . esc_attr($unit) . '">';
			$output .= '<div class="biomarker-dot-label">' . esc_html($current_value) . '</div>';
			$output .= '<div class="biomarker-dot-tooltip">';
			$output .= '<div class="tooltip-line-1">Your Current ' . esc_html($display_name) . '</div>';
			$output .= '<div class="tooltip-line-2">' . esc_html($current_value . ' ' . $unit) . '</div>';
			$output .= '</div>';
			$output .= '</div>';
		}
		
		// Target Value Dot
		if ($target_value && $target_ruler_position !== null) {
			$output .= '<div class="biomarker-target-dot" 
				style="left: ' . esc_attr($target_ruler_position) . '%;"
				data-biomarker-name="' . esc_attr($display_name) . '"
				data-value="' . esc_attr($target_value) . '"
				data-unit="' . esc_attr($unit) . '">';
			$output .= '<div class="biomarker-dot-label">' . esc_html($target_value) . '</div>';
			$output .= '<div class="biomarker-dot-tooltip">';
			$output .= '<div class="tooltip-line-1">Your Target ' . esc_html($display_name) . '</div>';
			$output .= '<div class="tooltip-line-2">' . esc_html($target_value . ' ' . $unit) . '</div>';
			$output .= '</div>';
			$output .= '</div>';
		}
		
		$output .= '</div>'; // Close biomarker-range-ruler
		$output .= '</div>'; // Close biomarker-range-ruler-container
		
		// Show status if available
		if (isset($measurement_data['status']) && is_array($measurement_data['status'])) {
			$status = $measurement_data['status'];
			$output .= '<div class="measurement-status">';
			$output .= '<span class="status-label">Status:</span> ';
			$output .= '<span class="status-value ' . esc_attr($status['status_class'] ?? '') . '">' . esc_html($status['status_text'] ?? 'Unknown') . '</span>';
			$output .= '</div>';
		}
		
		$output .= '</div>'; // Close biomarker-measurement
		
		return $output;
	}
	
	// Fallback function for simple display when ranges are not available
	function render_simple_biomarker_measurement($measurement_data) {
		$output = '<div class="biomarker-measurement">';
		$output .= '<div class="measurement-header">';
		
		$display_name = $measurement_data['display_name'] ?? ENNU_Biomarker_Manager::get_fallback_display_name($measurement_data['biomarker_id']) ?? ucwords(str_replace('_', ' ', $measurement_data['biomarker_id']));
		$output .= '<h4>' . esc_html($display_name) . '</h4>';
		$output .= '</div>';
		
		// Show current value if available
		if (isset($measurement_data['current_value']) && $measurement_data['current_value'] !== null) {
			$output .= '<div class="measurement-value">';
			$output .= '<span class="value">' . esc_html($measurement_data['current_value']) . '</span>';
			if (!empty($measurement_data['unit'])) {
				$output .= '<span class="unit">' . esc_html($measurement_data['unit']) . '</span>';
			}
			$output .= '</div>';
		} else {
			$output .= '<div class="measurement-value">';
			$output .= '<span class="value no-data">Awaiting Lab Results</span>';
			$output .= '</div>';
		}
		
		// Show optimal range if available
		if (isset($measurement_data['optimal_min']) && isset($measurement_data['optimal_max'])) {
			$output .= '<div class="measurement-range">';
			$output .= '<span class="range-label">Optimal Range:</span> ';
			$output .= '<span class="range-value">' . esc_html($measurement_data['optimal_min']) . ' - ' . esc_html($measurement_data['optimal_max']);
			if (!empty($measurement_data['unit'])) {
				$output .= ' ' . esc_html($measurement_data['unit']);
			}
			$output .= '</span>';
			$output .= '</div>';
		}
		
		// Show status if available
		if (isset($measurement_data['status']) && is_array($measurement_data['status'])) {
			$status = $measurement_data['status'];
			$output .= '<div class="measurement-status">';
			$output .= '<span class="status-label">Status:</span> ';
			$output .= '<span class="status-value ' . esc_attr($status['status_class'] ?? '') . '">' . esc_html($status['status_text'] ?? 'Unknown') . '</span>';
			$output .= '</div>';
		}
		
		// Show target value if available
		if (isset($measurement_data['target_value']) && $measurement_data['target_value'] !== null) {
			$output .= '<div class="measurement-target">';
			$output .= '<span class="target-label">Target:</span> ';
			$output .= '<span class="target-value">' . esc_html($measurement_data['target_value']);
			if (!empty($measurement_data['unit'])) {
				$output .= ' ' . esc_html($measurement_data['unit']);
			}
			$output .= '</span>';
			$output .= '</div>';
		}
		
		$output .= '</div>';
		
		return $output;
	}
}
?>

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
			// Group flagged biomarkers by assessment source
			$grouped_flagged = array();
			
			foreach ($flagged_biomarkers as $flag_id => $flag_data) {
				$assessment_source = $flag_data['assessment_source'] ?? '';
				
				// Enhanced assessment source detection
				if (empty($assessment_source)) {
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
							$assessment_source = 'symptom_assessment';
							break;
						default:
							$assessment_source = 'unknown_assessment';
							break;
					}
				}
				
				// Get the display name for the assessment source
				$assessment_display_name = get_assessment_display_name_from_source($assessment_source);
				
				// Ensure we have a valid display name
				if (empty($assessment_display_name) || 
					$assessment_display_name === 'Unknown Assessment' || 
					$assessment_display_name === 'Symptom Assessment') {
					
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
						if (empty($assessment_name)) {
							continue;
						}
						
						// Get assessment icon and color
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
											<?php 
											$biomarker_display_name = $biomarker_data['name'] ?? $biomarker_data['biomarker_name'] ?? ENNU_Biomarker_Manager::get_fallback_display_name($biomarker_data['biomarker_name']) ?? ucwords(str_replace('_', ' ', $biomarker_data['biomarker_name'])); 
											echo esc_html($biomarker_display_name); 
											?>
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
											// Get biomarker measurement data for display
											$biomarker_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data($user_id, $biomarker_name);
											
											// Render the measurement component
											echo render_biomarker_measurement($biomarker_data);
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
		// Debug: Check if biomarker panels config exists
		$panels_config_path = ENNU_LIFE_PLUGIN_PATH . 'includes/config/biomarker-panels.php';
		if ( ! file_exists( $panels_config_path ) ) {
			error_log( 'ENNU Biomarkers Template: Biomarker panels config not found at: ' . $panels_config_path );
			$panels = array();
		} else {
			error_log( 'ENNU Biomarkers Template: Loading biomarker panels config from: ' . $panels_config_path );
			$panels_config = include $panels_config_path;
			$panels = $panels_config['panels'];
			error_log( 'ENNU Biomarkers Template: Loaded ' . count( $panels ) . ' biomarker panels' );
		}
		
		// Debug: Check if Recommended Range Manager is available
		if ( ! class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
			error_log( 'ENNU Biomarkers Template: ENNU_Recommended_Range_Manager class not found' );
			$core_biomarkers = array();
		} else {
			error_log( 'ENNU Biomarkers Template: ENNU_Recommended_Range_Manager class found' );
			
			// Get user demographic data to personalize ranges
			$user_biomarkers = ENNU_Biomarker_Manager::get_user_biomarkers( $user_id );
			$user_gender     = get_user_meta( $user_id, 'ennu_global_gender', true );
			$age_data        = ENNU_Age_Management_System::get_user_age_data( $user_id );
			$user_age        = isset( $age_data['exact_age'] ) ? $age_data['exact_age'] : null;

			// Check if we have the necessary data to proceed
			if ( empty( $user_biomarkers ) || empty( $user_gender ) || empty( $user_age ) ) {
				// You can add a fallback message here if needed, e.g.:
				// echo '<p>Essential user data is missing to display biomarker panels.</p>';
			}
			
			// Re-define user_data with the correct age
			$user_data = array(
				'age'    => $user_age,
				'gender' => $user_gender,
			);
			
			$range_manager   = new ENNU_Recommended_Range_Manager();
			$core_biomarkers = array();

			// Get the full biomarker configuration
			$biomarker_config = $range_manager->get_biomarker_configuration();
			
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
					$measurement_data = ENNU_Biomarker_Manager::get_biomarker_measurement_data($user_id, $biomarker_key);
						
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
	</div>
</div>

<script>
// Biomarkers shortcode specific JavaScript
// Initialize immediately or on DOMContentLoaded, whichever is appropriate
function initializeBiomarkerFeatures() {
	// Panel toggle functionality
	window.togglePanel = function(panelId) {
		const panelContent = document.getElementById('panel-content-' + panelId);
		const toggleIcon = document.querySelector('[onclick="togglePanel(\'' + panelId + '\')"] .panel-expand-icon');
		
		if (panelContent.style.display === 'none') {
			panelContent.style.display = 'block';
			toggleIcon.textContent = '▼';
		} else {
			panelContent.style.display = 'none';
			toggleIcon.textContent = '▶';
		}
	};
	
	// Vector category toggle functionality
	window.toggleVectorCategory = function(panelId, vectorName) {
		const vectorCategory = document.querySelector('[data-panel="' + panelId + '"][data-vector="' + vectorName + '"]');
		const vectorList = vectorCategory.querySelector('.biomarker-vector-list');
		const toggleIcon = vectorCategory.querySelector('.vector-expand-icon');
		
		if (vectorList.style.display === 'none') {
			vectorList.style.display = 'block';
			toggleIcon.textContent = '▼';
		} else {
			vectorList.style.display = 'none';
			toggleIcon.textContent = '▶';
		}
	};
	
	// Biomarker measurements toggle functionality
	window.toggleBiomarkerMeasurements = function(panelId, vectorCategory, biomarkerName) {
		const measurementId = 'biomarker-measurement-' + panelId + '-' + vectorCategory + '-' + biomarkerName;
		const measurementContainer = document.getElementById(measurementId);
		const listItem = measurementContainer.previousElementSibling;
		const expandIcon = listItem.querySelector('.biomarker-list-expand');
		
		if (measurementContainer.style.display === 'none') {
			measurementContainer.style.display = 'block';
			expandIcon.textContent = '▼';
		} else {
			measurementContainer.style.display = 'none';
			expandIcon.textContent = '▶';
		}
	};
	
	// Lab upload functionality
	window.uploadLabResults = function() {
		alert('Lab upload functionality would be implemented here.');
	};
	
	// Lab scheduling functionality
	window.scheduleLabTest = function() {
		alert('Lab scheduling functionality would be implemented here.');
	};
	
	// Initialize dynamic tooltips for biomarker rulers
	initializeBiomarkerRulerTooltips();
	initializeDotTooltipHandlers();
	
	// Initialize toggle animations and smart label clustering
	initializeToggleAnimations();
	initializeSmartLabelClustering();
	initializeRangeSegmentAnimations();
}

// Initialize features immediately if DOM is ready, otherwise wait for DOMContentLoaded
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initializeBiomarkerFeatures);
} else {
	// DOM is already ready, initialize immediately
	initializeBiomarkerFeatures();
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
			const tooltipY = -25; // 25px above the ruler
			
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

// Toggle animations for biomarker ruler containers
function initializeToggleAnimations() {
	// Add entrance animation to existing rulers
	document.querySelectorAll('.biomarker-range-ruler-container').forEach((container, index) => {
		// Add staggered delay for multiple rulers
		container.style.animationDelay = `${index * 0.1}s`;
	});
	
	// Handle toggle animations for new rulers
	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			mutation.addedNodes.forEach((node) => {
				if (node.nodeType === Node.ELEMENT_NODE) {
					// Check if the added node is a ruler container
					if (node.classList && node.classList.contains('biomarker-range-ruler-container')) {
						node.style.animation = 'rulerSlideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards';
					}
					
					// Check for ruler containers within the added node
					const rulers = node.querySelectorAll ? node.querySelectorAll('.biomarker-range-ruler-container') : [];
					rulers.forEach((ruler, index) => {
						ruler.style.animationDelay = `${index * 0.1}s`;
						ruler.style.animation = 'rulerSlideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards';
					});
				}
			});
			
			mutation.removedNodes.forEach((node) => {
				if (node.nodeType === Node.ELEMENT_NODE) {
					if (node.classList && node.classList.contains('biomarker-range-ruler-container')) {
						// Add exit animation before removal
						node.classList.add('removing');
						setTimeout(() => {
							if (node.parentNode) {
								node.parentNode.removeChild(node);
							}
						}, 400); // Match the exit animation duration
					}
				}
			});
		});
	});
	
	// Observe the document body for changes
	observer.observe(document.body, {
		childList: true,
		subtree: true
	});
}

// Smart label clustering detection and prevention
function initializeSmartLabelClustering() {
	function checkLabelClustering() {
		document.querySelectorAll('.biomarker-range-labels').forEach(labelContainer => {
			const labels = labelContainer.querySelectorAll('.biomarker-range-label');
			
			// Reset all labels to normal positioning
			labels.forEach(label => {
				label.classList.remove('clustered');
				label.style.position = '';
				label.style.top = '';
				label.style.zIndex = '';
			});
			
			// Check for overlapping labels
			labels.forEach((label, index) => {
				const labelRect = label.getBoundingClientRect();
				const labelWidth = labelRect.width;
				const labelHeight = labelRect.height;
				
				// Check if this label overlaps with any previous labels
				for (let i = 0; i < index; i++) {
					const prevLabel = labels[i];
					const prevRect = prevLabel.getBoundingClientRect();
					
					// Calculate overlap
					const horizontalOverlap = Math.max(0, 
						Math.min(labelRect.right, prevRect.right) - 
						Math.max(labelRect.left, prevRect.left)
					);
					
					const verticalOverlap = Math.max(0,
						Math.min(labelRect.bottom, prevRect.bottom) - 
						Math.max(labelRect.top, prevRect.top)
					);
					
					// If there's significant overlap, mark as clustered
					if (horizontalOverlap > labelWidth * 0.3 || verticalOverlap > labelHeight * 0.5) {
						label.classList.add('clustered');
						prevLabel.classList.add('clustered');
					}
				}
			});
		}
		
		// Check clustering on page load
		checkLabelClustering();
		
		// Check clustering on window resize
		let resizeTimeout;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(checkLabelClustering, 100);
		});
		
		// Check clustering when new rulers are added
		const observer = new MutationObserver((mutations) => {
			mutations.forEach((mutation) => {
				if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
					setTimeout(checkLabelClustering, 50);
				}
			});
		});
		
		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	}
	
	// Initialize the clustering detection
	checkLabelClustering();
}

// Range segment growth animations
function initializeRangeSegmentAnimations() {
	// Add entrance animation to existing range segments
	document.querySelectorAll('.biomarker-range-segment').forEach((segment, index) => {
		// Reset any existing animations
		segment.style.animation = 'none';
		segment.offsetHeight; // Trigger reflow
		
		// Apply the growth animation with staggered delay
		const delay = index * 0.2; // 0.2s delay between each segment
		segment.style.animation = `rangeGrowIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) ${delay}s forwards`;
	});
	
	// Handle animations for new range segments
	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			mutation.addedNodes.forEach((node) => {
				if (node.nodeType === Node.ELEMENT_NODE) {
					// Check if the added node is a range segment
					if (node.classList && node.classList.contains('biomarker-range-segment')) {
						// Apply growth animation
						node.style.animation = 'rangeGrowIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards';
					}
					
					// Check for range segments within the added node
					const segments = node.querySelectorAll ? node.querySelectorAll('.biomarker-range-segment') : [];
					segments.forEach((segment, index) => {
						const delay = index * 0.2;
						segment.style.animation = `rangeGrowIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) ${delay}s forwards`;
					});
				}
			});
			
			mutation.removedNodes.forEach((node) => {
				if (node.nodeType === Node.ELEMENT_NODE) {
					if (node.classList && node.classList.contains('biomarker-range-segment')) {
						// Add exit animation before removal
						node.classList.add('removing');
						setTimeout(() => {
							if (node.parentNode) {
								node.parentNode.removeChild(node);
							}
						}, 600); // Match the exit animation duration
					}
				}
			});
		});
	});
	
	// Observe the document body for changes
	observer.observe(document.body, {
		childList: true,
		subtree: true
	});
}
</script> 