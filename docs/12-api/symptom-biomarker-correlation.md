# 🎯 **ENNU LIFE: SYMPTOM-TO-BIOMARKER CORRELATION SYSTEM - COMPREHENSIVE DOCUMENTATION**

**Document Version:** 1.0  
**Date:** 2025-07-18
**Author:** Luis Escobar  
**Classification:** CRITICAL BUSINESS SYSTEM ARCHITECTURE  
**Status:** COMPREHENSIVE IMPLEMENTATION BLUEPRINT  

---

## 📋 **EXECUTIVE SUMMARY**

This document provides the **DEFINITIVE ARCHITECTURE** for ENNU Life's symptom-to-biomarker correlation system—a revolutionary feature that transforms subjective symptom reporting into actionable biomarker recommendations, creating a **COMPETITIVE MOAT** and **REVENUE MULTIPLIER** in the health optimization market.

### **Business Impact Assessment**
- **Revenue Potential:** 3-5x increase in lab testing orders
- **User Engagement:** 40-60% increase in consultation bookings
- **Competitive Advantage:** Unique market position with no direct competitors
- **Customer Lifetime Value:** 2-3x increase through integrated health journey

### **Technical Complexity**
- **Implementation Timeline:** 5 weeks (phased approach)
- **Code Complexity:** High (requires new data architecture)
- **Integration Points:** 8 major system components
- **Data Dependencies:** 40+ biomarkers, 52 symptoms, 8 health vectors

---

## 🔍 **CURRENT SYSTEM STATE ANALYSIS**

### **✅ EXISTING FOUNDATION COMPONENTS**

#### **1.1 Symptom-to-Vector Mapping System**
**File:** `includes/config/health-optimization/symptom-map.php`
**Status:** FULLY IMPLEMENTED
**Coverage:** 52 symptoms mapped to 8 health vectors

**Key Mappings:**
```php
'Fatigue' => array(
    'Energy' => array('weight' => 0.8),
    'Heart Health' => array('weight' => 0.5),
    'Weight Loss' => array('weight' => 0.5),
    'Strength' => array('weight' => 0.6)
),
'Low Libido' => array(
    'Hormones' => array('weight' => 0.8),
    'Libido' => array('weight' => 1.0)
),
'Brain Fog' => array(
    'Energy' => array('weight' => 0.7),
    'Cognitive Health' => array('weight' => 0.8)
)
```

#### **1.2 Vector-to-Biomarker Mapping System**
**File:** `includes/config/health-optimization/biomarker-map.php`
**Status:** FULLY IMPLEMENTED
**Coverage:** 8 health vectors mapped to 40+ biomarkers

**Key Mappings:**
```php
'Heart Health' => array(
    'ApoB', 'Lp(a)', 'Homocysteine', 'hs-CRP', 
    'Total Cholesterol', 'HDL', 'LDL', 'Triglycerides'
),
'Hormones' => array(
    'Testosterone (Total & Free)', 'Estradiol (E2)', 
    'Progesterone', 'DHEA-S', 'Cortisol', 'TSH', 
    'Free T3', 'Free T4'
),
'Energy' => array(
    'Ferritin', 'Vitamin B12', 'Vitamin D', 
    'Cortisol', 'TSH', 'Free T3'
)
```

#### **1.3 Health Optimization Calculator**
**File:** `includes/class-health-optimization-calculator.php`
**Status:** FULLY IMPLEMENTED
**Key Methods:**
- `get_biomarker_recommendations()` - Returns recommended biomarkers
- `get_triggered_vectors()` - Returns triggered health vectors
- `calculate_pillar_penalties()` - Calculates symptom-based penalties

#### **1.4 Symptom Data Collection**
**File:** `includes/class-scoring-system.php`
**Status:** FULLY IMPLEMENTED
**Coverage:** All qualitative assessments
- Health Optimization Assessment (25 symptom questions)
- Testosterone Assessment symptoms
- Hormone Assessment symptoms
- Menopause Assessment symptoms
- ED Treatment Assessment symptoms
- Skin Assessment symptoms
- Hair Assessment symptoms
- Sleep Assessment symptoms
- Weight Loss Assessment symptoms

### **❌ CRITICAL MISSING COMPONENTS**

#### **2.1 Symptom-to-Biomarker Direct Correlation Display**
**Current State:** Symptoms displayed without biomarker context
**Missing:** Direct correlation explanations and review badges
**Impact:** Users don't understand why specific biomarkers matter

#### **2.2 Lab Data Import System**
**Current State:** No lab data import functionality
**Missing:** Admin interface for uploading lab results
**Impact:** Biomarker data cannot be stored or displayed

#### **2.3 Biomarker Results Storage**
**Current State:** No biomarker data storage structure
**Missing:** User meta fields for biomarker data
**Impact:** Cannot track or display lab results

#### **2.4 Review Badge System**
**Current State:** No review flags for biomarkers
**Missing:** System to flag biomarkers needing review
**Impact:** No urgency created for lab testing

#### **2.5 Official Measurement Display**
**Current State:** Static biomarker list only
**Missing:** Real lab data display with ranges and status
**Impact:** Users see generic information, not personal data

---

## 🏗️ **PROPOSED COMPREHENSIVE SOLUTION ARCHITECTURE**

### **3.1 Enhanced "My Symptoms" Tab - Symptom-to-Biomarker Correlation**

#### **3.1.1 Current Display (Basic)**
```php
// Current Implementation (templates/user-dashboard.php:720-800)
foreach ($user_symptoms as $symptom) {
    echo '<div class="symptom-item">';
    echo '<div class="symptom-icon">...</div>';
    echo '<span class="symptom-text">' . esc_html($symptom) . '</span>';
    echo '</div>';
}
```

#### **3.1.2 Proposed Enhanced Display**
```php
// Enhanced Implementation with Biomarker Correlations
foreach ($user_symptoms as $symptom) {
    $correlated_biomarkers = $this->get_biomarker_correlations($symptom);
    $user_biomarker_data = $this->get_user_biomarker_data($user_id);
    $symptom_severity = $this->get_symptom_severity($symptom, $user_id);
    
    echo '<div class="symptom-item enhanced">';
    
    // Symptom Header with Severity
    echo '<div class="symptom-header">';
    echo '<div class="symptom-icon">...</div>';
    echo '<span class="symptom-name">' . esc_html($symptom) . '</span>';
    echo '<span class="symptom-severity ' . esc_attr($symptom_severity) . '">' . esc_html($symptom_severity) . '</span>';
    echo '</div>';
    
    // Biomarker Correlations Section
    if (!empty($correlated_biomarkers)) {
        echo '<div class="biomarker-correlations">';
        echo '<h5 class="correlation-title">Related Biomarkers to Check:</h5>';
        echo '<div class="correlation-reason">';
        echo '<small>These biomarkers are commonly associated with "' . esc_html($symptom) . '" symptoms</small>';
        echo '</div>';
        
        foreach ($correlated_biomarkers as $biomarker) {
            $has_data = isset($user_biomarker_data[$biomarker]);
            $needs_review = $this->biomarker_needs_review($biomarker, $symptom);
            $data_status = $has_data ? $user_biomarker_data[$biomarker]['status'] : 'no_data';
            
            echo '<div class="biomarker-correlation-item ' . ($has_data ? 'has-data' : 'no-data') . '">';
            echo '<div class="biomarker-info">';
            echo '<span class="biomarker-name">' . esc_html($biomarker) . '</span>';
            echo '<span class="biomarker-unit">' . esc_html($this->get_biomarker_unit($biomarker)) . '</span>';
            echo '</div>';
            
            echo '<div class="biomarker-status">';
            if ($needs_review) {
                echo '<span class="review-badge urgent">Review Needed</span>';
            }
            if ($has_data) {
                echo '<span class="data-status ' . esc_attr($data_status) . '">' . esc_html(ucfirst($data_status)) . '</span>';
                echo '<span class="data-value">' . esc_html($user_biomarker_data[$biomarker]['value']) . '</span>';
            } else {
                echo '<span class="data-status no-data">No Data</span>';
                echo '<a href="' . esc_url($this->get_lab_ordering_url()) . '" class="order-test-btn">Order Test</a>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    echo '</div>';
}
```

#### **3.1.3 CSS Styling for Enhanced Display**
```css
/* Enhanced Symptom Item Styling */
.symptom-item.enhanced {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.symptom-item.enhanced:hover {
    border-color: var(--accent-primary);
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
}

.symptom-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.symptom-severity {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.symptom-severity.severe {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.symptom-severity.moderate {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.symptom-severity.mild {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.biomarker-correlations {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 15px;
}

.correlation-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--accent-primary);
    margin: 0 0 8px 0;
}

.correlation-reason {
    margin-bottom: 12px;
    opacity: 0.8;
}

.biomarker-correlation-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 8px;
    background: rgba(255, 255, 255, 0.02);
}

.biomarker-correlation-item:last-child {
    margin-bottom: 0;
}

.review-badge.urgent {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.order-test-btn {
    background: var(--accent-primary);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.order-test-btn:hover {
    background: var(--accent-secondary);
    transform: translateY(-1px);
}
```

### **3.2 Enhanced "My Biomarkers" Tab - Complete Lab Integration**

#### **3.2.1 Current Display (Static)**
```php
// Current Implementation (templates/user-dashboard.php:815-950)
foreach ($biomarkers as $biomarker => $unit) {
    echo '<div class="biomarker-item">';
    echo '<div class="biomarker-name">' . esc_html($biomarker) . '</div>';
    echo '<div class="biomarker-unit">' . esc_html($unit) . '</div>';
    echo '</div>';
}
```

#### **3.2.2 Proposed Enhanced Display**
```php
// Enhanced Implementation with Real Data and Review Status
foreach ($biomarkers as $biomarker => $unit) {
    $user_data = $this->get_user_biomarker_data($user_id, $biomarker);
    $symptom_correlations = $this->get_symptom_correlations_for_biomarker($biomarker);
    $needs_review = $this->biomarker_needs_review($biomarker);
    $review_priority = $this->get_review_priority($biomarker);
    
    echo '<div class="biomarker-item enhanced ' . ($user_data ? 'has-data' : 'no-data') . '">';
    
    // Biomarker Header with Review Status
    echo '<div class="biomarker-header">';
    echo '<div class="biomarker-info">';
    echo '<div class="biomarker-name">' . esc_html($biomarker) . '</div>';
    echo '<div class="biomarker-unit">' . esc_html($unit) . '</div>';
    echo '</div>';
    
    echo '<div class="biomarker-status-indicators">';
    if ($needs_review) {
        echo '<span class="review-badge ' . esc_attr($review_priority) . '">Review Needed</span>';
    }
    if ($user_data) {
        echo '<span class="data-status ' . esc_attr($user_data['status']) . '">' . esc_html(ucfirst($user_data['status'])) . '</span>';
    } else {
        echo '<span class="data-status no-data">No Data</span>';
    }
    echo '</div>';
    echo '</div>';
    
    // Biomarker Data Section
    if ($user_data) {
        echo '<div class="biomarker-data">';
        echo '<div class="official-measurement">';
        echo '<div class="measurement-value">';
        echo '<span class="value">' . esc_html($user_data['value']) . '</span>';
        echo '<span class="unit">' . esc_html($user_data['unit']) . '</span>';
        echo '</div>';
        echo '<div class="measurement-range">';
        echo '<span class="range-label">Reference Range:</span>';
        echo '<span class="range-value">' . esc_html($user_data['range']) . '</span>';
        echo '</div>';
        echo '<div class="measurement-status ' . esc_attr($user_data['status']) . '">';
        echo '<span class="status-icon">' . $this->get_status_icon($user_data['status']) . '</span>';
        echo '<span class="status-text">' . esc_html(ucfirst($user_data['status'])) . '</span>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="measurement-meta">';
        echo '<div class="test-date">Tested: ' . esc_html($user_data['date']) . '</div>';
        echo '<div class="lab-source">Lab: ' . esc_html($user_data['lab']) . '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="biomarker-no-data">';
        echo '<div class="no-data-message">';
        echo '<span class="no-data-icon">📊</span>';
        echo '<span class="no-data-text">No lab data available</span>';
        echo '</div>';
        echo '<a href="' . esc_url($this->get_lab_ordering_url()) . '" class="order-test-btn primary">Order Test</a>';
        echo '</div>';
    }
    
    // Symptom Correlations Section
    if (!empty($symptom_correlations)) {
        echo '<div class="symptom-correlations">';
        echo '<div class="correlation-header">';
        echo '<span class="correlation-icon">🔗</span>';
        echo '<span class="correlation-title">Related to Your Symptoms:</span>';
        echo '</div>';
        echo '<div class="correlation-symptoms">';
        foreach ($symptom_correlations as $symptom) {
            echo '<span class="correlation-symptom-tag">' . esc_html($symptom) . '</span>';
        }
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}
```

#### **3.2.3 CSS Styling for Enhanced Biomarker Display**
```css
/* Enhanced Biomarker Item Styling */
.biomarker-item.enhanced {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.biomarker-item.enhanced:hover {
    border-color: var(--accent-primary);
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
}

.biomarker-item.enhanced.has-data {
    border-left: 4px solid var(--accent-primary);
}

.biomarker-item.enhanced.no-data {
    border-left: 4px solid var(--border-color);
}

.biomarker-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.biomarker-info {
    flex: 1;
}

.biomarker-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.biomarker-unit {
    font-size: 0.8rem;
    color: var(--text-light);
    opacity: 0.8;
}

.biomarker-status-indicators {
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: flex-end;
}

.review-badge.high {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    animation: pulse 2s infinite;
}

.review-badge.medium {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
}

.review-badge.low {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
}

.biomarker-data {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.official-measurement {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 15px;
    align-items: center;
}

.measurement-value {
    text-align: center;
}

.measurement-value .value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
    display: block;
}

.measurement-value .unit {
    font-size: 0.8rem;
    color: var(--text-light);
    opacity: 0.8;
}

.measurement-range {
    text-align: center;
}

.range-label {
    font-size: 0.7rem;
    color: var(--text-light);
    display: block;
    margin-bottom: 4px;
}

.range-value {
    font-size: 0.9rem;
    color: var(--text-dark);
    font-weight: 500;
}

.measurement-status {
    text-align: center;
    padding: 8px;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.measurement-status.optimal {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.measurement-status.suboptimal {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.measurement-status.poor {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-icon {
    font-size: 1.2rem;
}

.status-text {
    font-size: 0.8rem;
    font-weight: 600;
}

.measurement-meta {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 0.8rem;
    color: var(--text-light);
}

.biomarker-no-data {
    text-align: center;
    padding: 30px 20px;
    background: rgba(255, 255, 255, 0.02);
    border: 2px dashed rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-bottom: 15px;
}

.no-data-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.no-data-icon {
    font-size: 2rem;
    opacity: 0.5;
}

.no-data-text {
    color: var(--text-light);
    font-size: 0.9rem;
}

.order-test-btn.primary {
    background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.order-test-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.symptom-correlations {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 12px;
}

.correlation-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.correlation-icon {
    font-size: 1rem;
}

.correlation-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--accent-primary);
}

.correlation-symptoms {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.correlation-symptom-tag {
    background: rgba(16, 185, 129, 0.1);
    color: var(--accent-primary);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
}
```

---

## 🗄️ **DATA ARCHITECTURE & STORAGE SYSTEM**

### **4.1 Biomarker Data Storage Structure**

#### **4.1.1 User Meta Fields Schema**
```php
// Primary biomarker data storage
'ennu_biomarker_data' => array(
    'total_testosterone' => array(
        'value' => 850,
        'unit' => 'ng/dL',
        'range' => '800-1200',
        'status' => 'optimal', // optimal, suboptimal, poor
        'date' => '2025-01-15',
        'lab' => 'ENNU Life Labs',
        'lab_report_id' => 'LAB-2025-001',
        'correlation_symptoms' => array('Low Libido', 'Fatigue', 'Muscle Loss'),
        'last_updated' => '2025-01-27 14:30:00',
        'data_source' => 'manual_import' // manual_import, api_import, user_entry
    ),
    'vitamin_d' => array(
        'value' => 25,
        'unit' => 'ng/mL',
        'range' => '50-80',
        'status' => 'poor',
        'date' => '2025-01-15',
        'lab' => 'ENNU Life Labs',
        'lab_report_id' => 'LAB-2025-001',
        'correlation_symptoms' => array('Fatigue', 'Brain Fog', 'Poor Sleep'),
        'last_updated' => '2025-01-27 14:30:00',
        'data_source' => 'manual_import'
    )
)

// Biomarker review flags and priorities
'ennu_biomarker_review_flags' => array(
    'total_testosterone' => array(
        'flagged_by_symptoms' => array('Low Libido', 'Fatigue'),
        'flag_date' => '2025-01-27',
        'priority' => 'high', // high, medium, low
        'flag_reason' => 'symptom_correlation',
        'reviewed' => false,
        'review_date' => null,
        'reviewed_by' => null
    ),
    'vitamin_d' => array(
        'flagged_by_symptoms' => array('Fatigue', 'Brain Fog'),
        'flag_date' => '2025-01-27',
        'priority' => 'medium',
        'flag_reason' => 'symptom_correlation',
        'reviewed' => false,
        'review_date' => null,
        'reviewed_by' => null
    )
)

// Biomarker correlation mapping (system-wide)
'ennu_biomarker_symptom_correlations' => array(
    'total_testosterone' => array(
        'symptoms' => array('Low Libido', 'Fatigue', 'Muscle Loss', 'Depression'),
        'correlation_strength' => 'strong',
        'clinical_evidence' => 'high',
        'recommendation_priority' => 'high'
    ),
    'vitamin_d' => array(
        'symptoms' => array('Fatigue', 'Brain Fog', 'Poor Sleep', 'Muscle Weakness'),
        'correlation_strength' => 'moderate',
        'clinical_evidence' => 'high',
        'recommendation_priority' => 'medium'
    )
)

// Lab import history and audit trail
'ennu_lab_import_history' => array(
    array(
        'import_date' => '2025-01-27 14:30:00',
        'import_type' => 'manual_upload',
        'file_name' => 'lab_results_2025_01_15.csv',
        'biomarkers_imported' => 15,
        'imported_by' => 'admin_user_id',
        'validation_errors' => array(),
        'processing_status' => 'completed'
    )
)
```

#### **4.1.2 Biomarker Reference Ranges Configuration**
**File:** `includes/config/biomarker-reference-ranges.php`
```php
<?php
return array(
    'total_testosterone' => array(
        'name' => 'Total Testosterone',
        'unit' => 'ng/dL',
        'ranges' => array(
            'optimal' => array('min' => 800, 'max' => 1200),
            'suboptimal' => array('min' => 400, 'max' => 799),
            'poor' => array('min' => 0, 'max' => 399)
        ),
        'gender_specific' => true,
        'male_ranges' => array(
            'optimal' => array('min' => 800, 'max' => 1200),
            'suboptimal' => array('min' => 400, 'max' => 799),
            'poor' => array('min' => 0, 'max' => 399)
        ),
        'female_ranges' => array(
            'optimal' => array('min' => 15, 'max' => 70),
            'suboptimal' => array('min' => 8, 'max' => 14),
            'poor' => array('min' => 0, 'max' => 7)
        ),
        'impact_weight' => 'critical',
        'pillar_impact' => array(
            'Body' => 0.8,
            'Mind' => 0.2
        )
    ),
    'vitamin_d' => array(
        'name' => 'Vitamin D (25-OH)',
        'unit' => 'ng/mL',
        'ranges' => array(
            'optimal' => array('min' => 50, 'max' => 80),
            'suboptimal' => array('min' => 30, 'max' => 49),
            'poor' => array('min' => 0, 'max' => 29)
        ),
        'gender_specific' => false,
        'impact_weight' => 'significant',
        'pillar_impact' => array(
            'Lifestyle' => 0.6,
            'Body' => 0.4
        )
    )
    // ... 40+ more biomarkers
);
```

### **4.2 Lab Data Import System**

#### **4.2.1 Admin Import Interface Class**
**File:** `includes/class-lab-data-importer.php`
```php
<?php
/**
 * ENNU Life Lab Data Importer
 * Handles all lab data import functionality
 */
class ENNU_Lab_Data_Importer {
    
    private $reference_ranges;
    private $correlation_mappings;
    
    public function __construct() {
        $this->reference_ranges = require ENNU_LIFE_PLUGIN_PATH . 'includes/config/biomarker-reference-ranges.php';
        $this->correlation_mappings = require ENNU_LIFE_PLUGIN_PATH . 'includes/config/biomarker-symptom-correlations.php';
    }
    
    /**
     * Render the admin import interface
     */
    public function render_import_interface() {
        ?>
        <div class="wrap">
            <h1>ENNU Life Lab Data Import</h1>
            
            <!-- Import Methods Tabs -->
            <nav class="nav-tab-wrapper">
                <a href="#csv-upload" class="nav-tab nav-tab-active">CSV Upload</a>
                <a href="#manual-entry" class="nav-tab">Manual Entry</a>
                <a href="#bulk-import" class="nav-tab">Bulk Import</a>
            </nav>
            
            <!-- CSV Upload Section -->
            <div id="csv-upload" class="import-section">
                <h2>Upload Lab Results CSV</h2>
                <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('ennu_lab_import', 'ennu_lab_import_nonce'); ?>
                    <input type="hidden" name="action" value="ennu_import_lab_data">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Select User</th>
                            <td>
                                <select name="user_id" required>
                                    <option value="">Choose a user...</option>
                                    <?php
                                    $users = get_users(array('orderby' => 'display_name'));
                                    foreach ($users as $user) {
                                        echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Lab Results File</th>
                            <td>
                                <input type="file" name="lab_file" accept=".csv,.xlsx,.xls" required>
                                <p class="description">Upload CSV or Excel file with lab results</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Lab Source</th>
                            <td>
                                <input type="text" name="lab_source" value="ENNU Life Labs" required>
                                <p class="description">Name of the lab that performed the tests</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Test Date</th>
                            <td>
                                <input type="date" name="test_date" required>
                                <p class="description">Date when the tests were performed</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Import Lab Data">
                    </p>
                </form>
            </div>
            
            <!-- Manual Entry Section -->
            <div id="manual-entry" class="import-section" style="display: none;">
                <h2>Manual Biomarker Entry</h2>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('ennu_lab_import', 'ennu_lab_import_nonce'); ?>
                    <input type="hidden" name="action" value="ennu_import_lab_data">
                    <input type="hidden" name="import_type" value="manual">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Select User</th>
                            <td>
                                <select name="user_id" required>
                                    <option value="">Choose a user...</option>
                                    <?php
                                    foreach ($users as $user) {
                                        echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="biomarker-entry-grid">
                        <?php
                        foreach ($this->reference_ranges as $biomarker_key => $biomarker_config) {
                            $this->render_biomarker_entry_field($biomarker_key, $biomarker_config);
                        }
                        ?>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Biomarker Data">
                    </p>
                </form>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Tab switching functionality
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                $('.import-section').hide();
                $($(this).attr('href')).show();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render individual biomarker entry field
     */
    private function render_biomarker_entry_field($biomarker_key, $biomarker_config) {
        ?>
        <div class="biomarker-entry-field">
            <label for="<?php echo esc_attr($biomarker_key); ?>">
                <?php echo esc_html($biomarker_config['name']); ?>
            </label>
            <div class="biomarker-input-group">
                <input type="number" 
                       step="0.01" 
                       name="biomarkers[<?php echo esc_attr($biomarker_key); ?>][value]" 
                       id="<?php echo esc_attr($biomarker_key); ?>"
                       placeholder="Value">
                <span class="unit-display"><?php echo esc_html($biomarker_config['unit']); ?></span>
            </div>
            <div class="reference-range">
                Reference: <?php echo esc_html($this->format_reference_range($biomarker_config)); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle lab data import
     */
    public function handle_lab_import() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['ennu_lab_import_nonce'], 'ennu_lab_import')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $user_id = intval($_POST['user_id']);
        $import_type = $_POST['import_type'] ?? 'csv';
        
        try {
            if ($import_type === 'csv') {
                $this->import_csv_data($user_id, $_FILES['lab_file'], $_POST);
            } else {
                $this->import_manual_data($user_id, $_POST['biomarkers'], $_POST);
            }
            
            // Update review flags based on user symptoms
            $this->update_review_flags($user_id);
            
            // Recalculate ENNU LIFE SCORE with biomarker adjustments
            ENNU_Assessment_Scoring::calculate_and_save_all_user_scores($user_id);
            
            // Log import history
            $this->log_import_history($user_id, $import_type, $_POST);
            
            wp_redirect(admin_url('admin.php?page=ennu-lab-import&import=success'));
            exit;
            
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=ennu-lab-import&import=error&message=' . urlencode($e->getMessage())));
            exit;
        }
    }
    
    /**
     * Import CSV data
     */
    private function import_csv_data($user_id, $file, $post_data) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $lab_source = sanitize_text_field($post_data['lab_source']);
        $test_date = sanitize_text_field($post_data['test_date']);
        
        $biomarker_data = array();
        
        if ($file_extension === 'csv') {
            $biomarker_data = $this->parse_csv_file($file['tmp_name']);
        } elseif (in_array($file_extension, ['xlsx', 'xls'])) {
            $biomarker_data = $this->parse_excel_file($file['tmp_name']);
        } else {
            throw new Exception('Unsupported file format');
        }
        
        $this->save_biomarker_data($user_id, $biomarker_data, $lab_source, $test_date);
    }
    
    /**
     * Import manual data
     */
    private function import_manual_data($user_id, $biomarkers, $post_data) {
        $lab_source = sanitize_text_field($post_data['lab_source'] ?? 'Manual Entry');
        $test_date = sanitize_text_field($post_data['test_date'] ?? date('Y-m-d'));
        
        $biomarker_data = array();
        
        foreach ($biomarkers as $biomarker_key => $biomarker_info) {
            if (!empty($biomarker_info['value'])) {
                $biomarker_data[$biomarker_key] = array(
                    'value' => floatval($biomarker_info['value']),
                    'unit' => $this->reference_ranges[$biomarker_key]['unit']
                );
            }
        }
        
        $this->save_biomarker_data($user_id, $biomarker_data, $lab_source, $test_date);
    }
    
    /**
     * Save biomarker data to user meta
     */
    private function save_biomarker_data($user_id, $biomarker_data, $lab_source, $test_date) {
        $existing_data = get_user_meta($user_id, 'ennu_biomarker_data', true);
        if (!is_array($existing_data)) {
            $existing_data = array();
        }
        
        foreach ($biomarker_data as $biomarker_key => $data) {
            if (!isset($this->reference_ranges[$biomarker_key])) {
                continue; // Skip unknown biomarkers
            }
            
            $reference_config = $this->reference_ranges[$biomarker_key];
            $status = $this->determine_biomarker_status($data['value'], $reference_config, $user_id);
            
            $existing_data[$biomarker_key] = array(
                'value' => $data['value'],
                'unit' => $data['unit'],
                'range' => $this->format_reference_range($reference_config),
                'status' => $status,
                'date' => $test_date,
                'lab' => $lab_source,
                'lab_report_id' => 'LAB-' . date('Y-m-d-H-i-s'),
                'correlation_symptoms' => $this->get_correlation_symptoms($biomarker_key),
                'last_updated' => current_time('mysql'),
                'data_source' => 'manual_import'
            );
        }
        
        update_user_meta($user_id, 'ennu_biomarker_data', $existing_data);
    }
    
    /**
     * Determine biomarker status based on value and reference ranges
     */
    private function determine_biomarker_status($value, $reference_config, $user_id) {
        $user_gender = get_user_meta($user_id, 'ennu_global_gender', true);
        $ranges = $reference_config['ranges'];
        
        if ($reference_config['gender_specific'] && $user_gender) {
            $ranges = $user_gender === 'male' ? $reference_config['male_ranges'] : $reference_config['female_ranges'];
        }
        
        if ($value >= $ranges['optimal']['min'] && $value <= $ranges['optimal']['max']) {
            return 'optimal';
        } elseif ($value >= $ranges['suboptimal']['min'] && $value <= $ranges['suboptimal']['max']) {
            return 'suboptimal';
        } else {
            return 'poor';
        }
    }
    
    /**
     * Update review flags based on user symptoms
     */
    private function update_review_flags($user_id) {
        $user_symptoms = $this->get_user_symptoms($user_id);
        $existing_flags = get_user_meta($user_id, 'ennu_biomarker_review_flags', true);
        if (!is_array($existing_flags)) {
            $existing_flags = array();
        }
        
        foreach ($this->correlation_mappings as $biomarker_key => $correlation) {
            $matching_symptoms = array_intersect($user_symptoms, $correlation['symptoms']);
            
            if (!empty($matching_symptoms)) {
                $existing_flags[$biomarker_key] = array(
                    'flagged_by_symptoms' => array_values($matching_symptoms),
                    'flag_date' => date('Y-m-d'),
 