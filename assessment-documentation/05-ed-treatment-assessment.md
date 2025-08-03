# ED Treatment Assessment: Complete Question-to-Symptom-to-Biomarker Mapping

**Assessment Type:** Quantitative (Scored)  
**Total Questions:** 6  
**Engine:** Quantitative Engine (Scored)  
**Gender Filter:** Male Only  
**Purpose:** Erectile dysfunction evaluation and treatment planning  

---

## 📋 ASSESSMENT OVERVIEW

The ED Treatment Assessment is a specialized quantitative assessment designed to evaluate erectile dysfunction symptoms and related health factors. This assessment focuses on male-specific health concerns including sexual function, cardiovascular health, and hormonal balance.

---

## 🔍 QUESTION-BY-QUESTION BREAKDOWN

### **Question 1: ED Severity**
**Question:** "How would you rate the severity of your erectile dysfunction?"  
**Type:** Radio (10-point scale)  
**Options:** 1 (Severe) to 10 (No ED)  

**Symptom Mapping:**
- **1-3 (Severe)** → Hormones (1.0), Heart Health (0.9), Libido (1.0)
- **4-6 (Moderate)** → Hormones (0.8), Heart Health (0.7), Libido (0.8)
- **7-9 (Mild)** → Hormones (0.5), Heart Health (0.4), Libido (0.5)
- **10 (None)** → Hormones (0.2), Heart Health (0.2), Libido (0.2)

**Biomarker Correlations:**
- **Primary Sexual Health Biomarkers:** testosterone_total, testosterone_free, estradiol, prolactin, shbg
- **Cardiovascular Biomarkers:** apob, lp_a, homocysteine, hs_crp, total_cholesterol, hdl, ldl, triglycerides
- **Hormonal Biomarkers:** cortisol, tsh, free_t3, free_t4, vitamin_d
- **Metabolic Biomarkers:** glucose, hba1c, insulin
- **Physical Indicators:** weight, bmi, body_fat_percent

**Clinical Rationale:** ED severity directly correlates with hormonal balance, cardiovascular health, and metabolic function. Severe ED often indicates underlying cardiovascular or hormonal issues.

**Scoring Weight:** 4.0 (High Impact)

---

### **Question 2: Related Medical Conditions**
**Question:** "Do you have any of the following medical conditions?"  
**Type:** Multiselect  
**Options:** Diabetes, High Blood Pressure, Heart Disease, Depression, Anxiety  

**Symptom Mapping:**
- **Diabetes** → Weight Loss (0.9), Heart Health (0.8), Energy (0.6)
- **High Blood Pressure** → Weight Loss (0.7), Heart Health (0.9), Energy (0.4)
- **Heart Disease** → Heart Health (1.0), Energy (0.6), Longevity (0.8)
- **Depression** → Hormones (0.7), Cognitive Health (0.8), Energy (0.6)
- **Anxiety** → Hormones (0.7), Cognitive Health (0.7), Energy (0.5)

**Biomarker Correlations:**
- **Cardiovascular Biomarkers:** apob, lp_a, homocysteine, hs_crp, total_cholesterol, hdl, ldl, triglycerides
- **Metabolic Biomarkers:** glucose, hba1c, insulin, leptin, ghrelin, adiponectin
- **Hormonal Biomarkers:** cortisol, testosterone, estradiol, tsh, free_t3, free_t4
- **Mental Health Biomarkers:** vitamin_d, vitamin_b12, magnesium, omega_3
- **Physical Indicators:** weight, bmi, body_fat_percent, blood_pressure, heart_rate

**Clinical Rationale:** Medical conditions significantly impact ED through cardiovascular, metabolic, and hormonal pathways. Each condition requires specific biomarker evaluation.

**Scoring Weight:** 3.0 (Moderate Impact)

---

### **Question 3: Lifestyle Factors**
**Question:** "How would you describe your current lifestyle factors?"  
**Type:** Radio  
**Options:** Very unhealthy (2 points), Somewhat unhealthy (4 points), Moderate (6 points), Healthy (8 points), Very healthy (10 points)  

**Symptom Mapping:**
- **Very unhealthy** → Weight Loss (0.9), Heart Health (0.8), Energy (0.7)
- **Somewhat unhealthy** → Weight Loss (0.7), Heart Health (0.6), Energy (0.5)
- **Moderate** → Weight Loss (0.5), Heart Health (0.4), Energy (0.3)
- **Healthy** → Weight Loss (0.3), Heart Health (0.2), Energy (0.2)
- **Very healthy** → Weight Loss (0.1), Heart Health (0.1), Energy (0.1)

**Biomarker Correlations:**
- **Metabolic Biomarkers:** glucose, hba1c, insulin, leptin, ghrelin, adiponectin
- **Cardiovascular Biomarkers:** cholesterol, triglycerides, hdl, ldl, hs_crp
- **Hormonal Biomarkers:** cortisol, testosterone, estradiol, tsh, free_t3, free_t4
- **Physical Indicators:** weight, bmi, body_fat_percent, blood_pressure, heart_rate
- **Support Biomarkers:** vitamin_d, vitamin_b12, ferritin

**Clinical Rationale:** Lifestyle factors directly impact cardiovascular health, metabolic function, and hormonal balance, all of which affect erectile function.

**Scoring Weight:** 2.5 (High Impact)

---

### **Question 4: Contact Information**
**Question:** "Let's get your contact information."  
**Type:** Contact Info  
**Options:** N/A  

**Symptom Mapping:**
- **N/A** → No symptom mapping (administrative question)

**Biomarker Correlations:**
- **N/A** → No biomarker correlations (administrative question)

**Clinical Rationale:** Administrative question for user data collection.

**Scoring Weight:** N/A (No scoring)

---

## 📊 SYMPTOM-TO-BIOMARKER CORRELATION MATRIX

### **Primary ED-Related Symptoms:**

| Symptom | Primary Biomarkers | Secondary Biomarkers | Clinical Priority |
|---------|-------------------|---------------------|------------------|
| **Severe ED** | testosterone_total, testosterone_free, estradiol, prolactin, shbg | apob, lp_a, homocysteine, hs_crp, cholesterol | High |
| **Moderate ED** | testosterone_total, testosterone_free, estradiol, prolactin, shbg | apob, lp_a, homocysteine, hs_crp, cholesterol | High |
| **Mild ED** | testosterone_total, testosterone_free, estradiol, prolactin, shbg | apob, lp_a, homocysteine, hs_crp, cholesterol | Medium |
| **Diabetes** | glucose, hba1c, insulin, leptin, ghrelin, adiponectin | cholesterol, triglycerides, hdl, ldl, hs_crp | High |
| **High Blood Pressure** | blood_pressure, heart_rate, cholesterol, triglycerides, hdl, ldl | cortisol, catecholamines, renin, aldosterone | High |
| **Heart Disease** | apob, lp_a, homocysteine, hs_crp, cholesterol, triglycerides | glucose, hba1c, insulin, cortisol | High |
| **Depression** | cortisol, testosterone, estradiol, tsh, free_t3, free_t4 | vitamin_d, vitamin_b12, magnesium, omega_3 | Medium |
| **Anxiety** | cortisol, testosterone, estradiol, tsh, free_t3, free_t4 | vitamin_d, vitamin_b12, magnesium, omega_3 | Medium |
| **Unhealthy Lifestyle** | glucose, hba1c, insulin, cholesterol, triglycerides, cortisol | testosterone, estradiol, tsh, free_t3, free_t4 | Medium |

---

## 🎯 HEALTH VECTOR IMPACT ANALYSIS

### **Primary Health Vectors Affected:**

1. **Hormones (0.7-1.0 weight)**
   - **Primary Biomarkers:** testosterone_total, testosterone_free, estradiol, prolactin, shbg
   - **Secondary Biomarkers:** cortisol, tsh, free_t3, free_t4, vitamin_d
   - **Impact:** Direct hormonal balance assessment for sexual function

2. **Heart Health (0.7-1.0 weight)**
   - **Primary Biomarkers:** apob, lp_a, homocysteine, hs_crp, total_cholesterol, hdl, ldl, triglycerides
   - **Secondary Biomarkers:** glucose, hba1c, insulin, cortisol
   - **Impact:** Cardiovascular health assessment (critical for ED)

3. **Libido (0.8-1.0 weight)**
   - **Primary Biomarkers:** testosterone_total, testosterone_free, estradiol, prolactin, shbg
   - **Secondary Biomarkers:** cortisol, tsh, free_t3, free_t4, vitamin_d
   - **Impact:** Sexual health and desire assessment

4. **Weight Loss (0.7-0.9 weight)**
   - **Primary Biomarkers:** glucose, hba1c, insulin, leptin, ghrelin, adiponectin
   - **Secondary Biomarkers:** cholesterol, triglycerides, hdl, ldl, hs_crp
   - **Impact:** Metabolic health assessment

5. **Energy (0.4-0.7 weight)**
   - **Primary Biomarkers:** ferritin, vitamin_d, vitamin_b12, cortisol, tsh, free_t3, free_t4
   - **Secondary Biomarkers:** glucose, hba1c, insulin
   - **Impact:** Vitality and daily function

6. **Cognitive Health (0.7-0.8 weight)**
   - **Primary Biomarkers:** vitamin_d, vitamin_b12, magnesium, omega_3
   - **Secondary Biomarkers:** cortisol, testosterone, estradiol, tsh, free_t3, free_t4
   - **Impact:** Mental health and mood assessment

---

## 📈 SCORING SYSTEM ANALYSIS

### **Scoring Weights by Question:**
1. **Question 1 (ED Severity):** 4.0 points (High Impact)
2. **Question 2 (Medical Conditions):** 3.0 points (Moderate Impact)
3. **Question 3 (Lifestyle Factors):** 2.5 points (High Impact)
4. **Question 4 (Contact Info):** N/A (Administrative)

### **Total Possible Score:** 9.5 points

### **Scoring Categories:**
- **Excellent (7.5-9.5 points):** Optimal sexual function
- **Good (6-7.4 points):** Mild ED concerns
- **Fair (4.5-5.9 points):** Moderate ED issues
- **Poor (3-4.4 points):** Significant ED problems
- **Very Poor (0-2.9 points):** Severe ED requiring immediate attention

---

## 🏥 CLINICAL INTEGRATION GUIDELINES

### **High Priority Biomarkers (Test Immediately):**
1. **testosterone_total** - Primary androgen assessment
2. **testosterone_free** - Bioavailable testosterone
3. **estradiol** - Estrogen balance (important for men)
4. **prolactin** - Can suppress testosterone
5. **shbg** - Sex hormone binding globulin
6. **apob, lp_a, homocysteine** - Cardiovascular health
7. **glucose, hba1c** - Metabolic health
8. **blood_pressure, heart_rate** - Cardiovascular function

### **Medium Priority Biomarkers (Test if Symptoms Persist):**
1. **cortisol** - Stress hormone impact
2. **tsh, free_t3, free_t4** - Thyroid function
3. **vitamin_d** - Supports testosterone production
4. **cholesterol, triglycerides, hdl, ldl** - Lipid profile
5. **hs_crp** - Inflammation marker
6. **insulin** - Metabolic function

### **Special Considerations:**
- **Severe ED:** Requires immediate cardiovascular evaluation
- **Diabetes:** Strongly associated with ED through vascular damage
- **High Blood Pressure:** Can cause vascular ED
- **Heart Disease:** Often presents with ED as first symptom
- **Depression/Anxiety:** Can cause or worsen ED
- **Unhealthy Lifestyle:** Major contributor to ED

---

## 🎯 ASSESSMENT UTILITY

This ED Treatment Assessment provides:
1. **Quantitative Scoring** for objective evaluation of ED severity
2. **Symptom-Based Biomarker Flagging** for targeted testing
3. **Health Vector Analysis** for comprehensive evaluation
4. **Clinical Priority Assessment** for treatment planning
5. **Baseline Establishment** for progress tracking

The assessment serves as a critical tool for identifying ED-related health issues and guiding personalized biomarker testing and treatment strategies. 