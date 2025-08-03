# Testosterone Assessment: Complete Question-to-Symptom-to-Biomarker Mapping

**Assessment Type:** Quantitative (Scored)  
**Total Questions:** 6  
**Engine:** Quantitative Engine (Scored)  
**Gender Filter:** Male Only  
**Purpose:** Testosterone deficiency evaluation and scoring  

---

## 📋 ASSESSMENT OVERVIEW

The Testosterone Assessment is a specialized quantitative assessment designed to evaluate testosterone-related symptoms and provide scored results. This assessment focuses on male-specific health concerns including libido, energy, performance, and hormonal balance.

---

## 🔍 QUESTION-BY-QUESTION BREAKDOWN

### **Question 1: Sexual Health Symptoms**
**Question:** "In the last month, have you frequently experienced:"  
**Type:** Multiselect  
**Options:** Low Libido, Erectile Dysfunction, Irritability, Lack of Motivation, "Brain Fog"  

**Symptom Mapping:**
- **Low Libido** → Hormones (0.8), Libido (1.0)
- **Erectile Dysfunction** → Hormones (0.8), Heart Health (0.7), Libido (0.9)
- **Irritability** → Hormones (0.7), Cognitive Health (0.6)
- **Lack of Motivation** → Energy (0.6), Cognitive Health (0.5)
- **Brain Fog** → Energy (0.7), Cognitive Health (0.8)

**Biomarker Correlations:**
- **Primary Sexual Health Biomarkers:** testosterone_total, testosterone_free, estradiol, prolactin, shbg
- **Secondary Hormonal Biomarkers:** cortisol, tsh, free_t3, free_t4, vitamin_d
- **Cognitive Support Biomarkers:** vitamin_b12, vitamin_d, homocysteine, tsh, free_t3, free_t4
- **Energy Biomarkers:** ferritin, vitamin_b12, vitamin_d, cortisol, tsh
- **Cardiovascular Biomarkers:** apob, lp_a, homocysteine, hs_crp, total_cholesterol, hdl, ldl, triglycerides
- **Metabolic Biomarkers:** glucose, hba1c, insulin

**Clinical Rationale:** Sexual health symptoms are strongly associated with testosterone levels, but also correlate with thyroid function, cardiovascular health, and nutritional status. Erectile dysfunction specifically requires cardiovascular evaluation.

**Scoring Weight:** 4.0 (High Impact)

---

### **Question 2: Physical Performance**
**Question:** "How would you describe your physical endurance or stamina?"  
**Type:** Radio  
**Options:** Decreased significantly (3 points), Somewhat decreased (5 points), Has not changed (8 points)  

**Symptom Mapping:**
- **Decreased significantly** → Strength (0.9), Energy (0.8), Longevity (0.6)
- **Somewhat decreased** → Strength (0.7), Energy (0.6), Longevity (0.4)
- **Has not changed** → Strength (0.3), Energy (0.3), Longevity (0.2)

**Biomarker Correlations:**
- **Performance Biomarkers:** testosterone_free, testosterone_total, igf_1, creatine_kinase, grip_strength
- **Energy Biomarkers:** ferritin, vitamin_d, vitamin_b12, cortisol, tsh, free_t3, free_t4
- **Support Biomarkers:** vitamin_d, vitamin_b12, ferritin
- **Physical Indicators:** weight, bmi, body_fat_percent

**Clinical Rationale:** Physical performance is directly related to testosterone levels, IGF-1, and overall energy status. Decreased performance often indicates hormonal deficiencies or nutritional issues.

**Scoring Weight:** 3.0 (Moderate Impact)

---

### **Question 3: Muscle Building Ability**
**Question:** "How would you describe your ability to build muscle?"  
**Type:** Radio  
**Options:** Very difficult (2 points), Somewhat difficult (4 points), Moderate (6 points), Relatively easy (8 points)  

**Symptom Mapping:**
- **Very difficult** → Strength (1.0), Anabolic Response (1.0)
- **Somewhat difficult** → Strength (0.8), Anabolic Response (0.8)
- **Moderate** → Strength (0.6), Anabolic Response (0.6)
- **Relatively easy** → Strength (0.4), Anabolic Response (0.4)

**Biomarker Correlations:**
- **Anabolic Biomarkers:** testosterone_free, testosterone_total, igf_1, creatine_kinase
- **Performance Biomarkers:** grip_strength, weight, bmi, body_fat_percent
- **Support Biomarkers:** vitamin_d, vitamin_b12, ferritin
- **Thyroid Biomarkers:** tsh, free_t3, free_t4

**Clinical Rationale:** Muscle building ability is directly related to anabolic hormone levels, particularly testosterone and IGF-1. Difficulty building muscle often indicates hormonal deficiencies.

**Scoring Weight:** 2.0 (Moderate Impact)

---

### **Question 4: Energy & Motivation**
**Question:** "How would you describe your energy levels and motivation?"  
**Type:** Radio  
**Options:** Very low (2 points), Lower than usual (4 points), Normal (7 points), High (9 points)  

**Symptom Mapping:**
- **Very low** → Vitality & Drive (1.0), Energy (0.9)
- **Lower than usual** → Vitality & Drive (0.8), Energy (0.7)
- **Normal** → Vitality & Drive (0.5), Energy (0.4)
- **High** → Vitality & Drive (0.2), Energy (0.2)

**Biomarker Correlations:**
- **Vitality Biomarkers:** testosterone_free, testosterone_total, cortisol, tsh, free_t3, free_t4
- **Energy Biomarkers:** ferritin, vitamin_d, vitamin_b12, cortisol, tsh, free_t3, free_t4
- **Support Biomarkers:** vitamin_d, vitamin_b12, ferritin
- **Metabolic Biomarkers:** glucose, hba1c, insulin

**Clinical Rationale:** Energy and motivation are strongly influenced by testosterone levels, thyroid function, and nutritional status. Low energy often indicates hormonal or nutritional deficiencies.

**Scoring Weight:** 2.5 (High Impact)

---

### **Question 5: Contact Information**
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

### **Primary Testosterone-Related Symptoms:**

| Symptom | Primary Biomarkers | Secondary Biomarkers | Clinical Priority |
|---------|-------------------|---------------------|------------------|
| **Low Libido** | testosterone_total, testosterone_free, estradiol, prolactin, shbg | cortisol, tsh, vitamin_d | High |
| **Erectile Dysfunction** | testosterone_total, testosterone_free, estradiol, prolactin, shbg | apob, lp_a, homocysteine, hs_crp, cholesterol | High |
| **Irritability** | cortisol, testosterone, estradiol, tsh, free_t3, free_t4 | vitamin_d, vitamin_b12, magnesium | Medium |
| **Lack of Motivation** | cortisol, testosterone, vitamin_d, tsh, free_t3, free_t4 | vitamin_b12, ferritin, glucose, hba1c | Medium |
| **Brain Fog** | vitamin_b12, vitamin_d, homocysteine, tsh, free_t3, free_t4 | ferritin, coq10, heavy_metals_panel, glucose, hba1c, cortisol | Medium |
| **Decreased Physical Performance** | testosterone_free, testosterone_total, igf_1, creatine_kinase, grip_strength | ferritin, vitamin_d, vitamin_b12, cortisol, tsh | High |
| **Difficulty Building Muscle** | testosterone_free, testosterone_total, igf_1, creatine_kinase | vitamin_d, vitamin_b12, ferritin, tsh, free_t3, free_t4 | High |
| **Low Energy** | testosterone_free, testosterone_total, cortisol, tsh, free_t3, free_t4 | ferritin, vitamin_d, vitamin_b12, glucose, hba1c, insulin | High |

---

## 🎯 HEALTH VECTOR IMPACT ANALYSIS

### **Primary Health Vectors Affected:**

1. **Hormones (0.8-1.0 weight)**
   - **Primary Biomarkers:** testosterone_total, testosterone_free, estradiol, prolactin, shbg
   - **Secondary Biomarkers:** cortisol, tsh, free_t3, free_t4, vitamin_d
   - **Impact:** Direct hormonal balance assessment

2. **Libido (0.9-1.0 weight)**
   - **Primary Biomarkers:** testosterone_total, testosterone_free, estradiol, prolactin, shbg
   - **Secondary Biomarkers:** cortisol, tsh, free_t3, free_t4, vitamin_d
   - **Impact:** Sexual health and performance

3. **Strength (0.7-1.0 weight)**
   - **Primary Biomarkers:** testosterone_free, testosterone_total, igf_1, creatine_kinase, grip_strength
   - **Secondary Biomarkers:** vitamin_d, vitamin_b12, ferritin, tsh, free_t3, free_t4
   - **Impact:** Physical performance and muscle building

4. **Energy (0.6-0.9 weight)**
   - **Primary Biomarkers:** ferritin, vitamin_d, vitamin_b12, cortisol, tsh, free_t3, free_t4
   - **Secondary Biomarkers:** glucose, hba1c, insulin
   - **Impact:** Vitality and daily function

5. **Cognitive Health (0.5-0.8 weight)**
   - **Primary Biomarkers:** vitamin_b12, vitamin_d, homocysteine, tsh, free_t3, free_t4
   - **Secondary Biomarkers:** ferritin, coq10, heavy_metals_panel, glucose, hba1c, cortisol
   - **Impact:** Mental clarity and focus

6. **Heart Health (0.7 weight)**
   - **Primary Biomarkers:** apob, lp_a, homocysteine, hs_crp, total_cholesterol, hdl, ldl, triglycerides
   - **Secondary Biomarkers:** glucose, hba1c, insulin
   - **Impact:** Cardiovascular health (especially for ED)

---

## 📈 SCORING SYSTEM ANALYSIS

### **Scoring Weights by Question:**
1. **Question 1 (Sexual Health):** 4.0 points (High Impact)
2. **Question 2 (Physical Performance):** 3.0 points (Moderate Impact)
3. **Question 3 (Muscle Building):** 2.0 points (Moderate Impact)
4. **Question 4 (Energy & Motivation):** 2.5 points (High Impact)
5. **Question 5 (Contact Info):** N/A (Administrative)

### **Total Possible Score:** 11.5 points

### **Scoring Categories:**
- **Excellent (9-11.5 points):** Optimal testosterone function
- **Good (7-8.9 points):** Mild testosterone concerns
- **Fair (5-6.9 points):** Moderate testosterone issues
- **Poor (2-4.9 points):** Significant testosterone problems
- **Very Poor (0-1.9 points):** Severe testosterone deficiency

---

## 🏥 CLINICAL INTEGRATION GUIDELINES

### **High Priority Biomarkers (Test Immediately):**
1. **testosterone_total** - Primary androgen assessment
2. **testosterone_free** - Bioavailable testosterone
3. **estradiol** - Estrogen balance (important for men)
4. **prolactin** - Can suppress testosterone
5. **shbg** - Sex hormone binding globulin
6. **tsh, free_t3, free_t4** - Thyroid function
7. **cortisol** - Stress hormone impact

### **Medium Priority Biomarkers (Test if Symptoms Persist):**
1. **vitamin_d** - Supports testosterone production
2. **vitamin_b12** - Energy and cognitive support
3. **ferritin** - Iron status for energy
4. **apob, lp_a, homocysteine** - Cardiovascular health (if ED present)
5. **glucose, hba1c** - Metabolic health
6. **igf_1** - Growth factor assessment

### **Special Considerations:**
- **Erectile Dysfunction:** Requires cardiovascular evaluation
- **Brain Fog:** May indicate thyroid or nutritional issues
- **Low Energy:** Could indicate multiple deficiencies
- **Difficulty Building Muscle:** Directly related to anabolic hormones

---

## 🎯 ASSESSMENT UTILITY

This Testosterone Assessment provides:
1. **Quantitative Scoring** for objective evaluation
2. **Symptom-Based Biomarker Flagging** for targeted testing
3. **Health Vector Analysis** for comprehensive evaluation
4. **Clinical Priority Assessment** for treatment planning
5. **Baseline Establishment** for progress tracking

The assessment serves as a critical tool for identifying testosterone-related health issues and guiding personalized biomarker testing and treatment strategies. 