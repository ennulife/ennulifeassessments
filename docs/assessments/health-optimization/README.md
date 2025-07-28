# Health Optimization Assessment Documentation

**Assessment Type:** Health Optimization Assessment
**Assessment Engine:** Qualitative
**Purpose:** Symptom-based health optimization evaluation
**Version:** 62.11.0
**Total Questions:** 25
**Gender Filter:** All users

---

## Overview

The Health Optimization Assessment is designed to symptom-based health optimization evaluation. This assessment focuses on gathering qualitative data to understand user needs and preferences.

### Key Characteristics
- **Assessment Type:** Qualitative (not scored)
- **Primary Purpose:** symptom-based health optimization evaluation
- **Scoring System:** Qualitative data collection
- **Gender Filter:** All users
- **Categories:** Data collection categories

---

## Questions & Answers

### Question 1: Health q gender
- **Field ID:** `health_q_gender`
- **Question:** What is your gender?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Global Key:** `gender`
- **Answer Options:**
  - `female` - FEMALE
  - `male` - MALE
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Health q dob
- **Field ID:** `health_q_dob`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Scoring:**
  - **Category:** Age Factors
  - **Weight:** 1.5
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Symptom q1
- **Field ID:** `symptom_q1`
- **Question:** Please select any symptoms you are experiencing related to Heart Health.
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** No
- **Answer Options:**
  - `chest_pain` - Chest Pain or Discomfort
  - `shortness_breath` - Shortness of Breath
  - `palpitations` - Heart Palpitations or Irregular Heartbeat
  - `lightheadedness` - Lightheadedness or Dizziness
  - `swelling` - Swelling in Legs, Ankles, or Feet
  - `poor_exercise_tolerance` - Poor Exercise Tolerance
  - `fatigue` - Unexplained Fatigue
  - `nausea` - Nausea or Indigestion
  - `sweating` - Cold Sweats
  - `none` - None of the above
- **Scoring:**
  - **Category:** Cardiovascular Symptoms
  - **Weight:** 3
  - **Answers:**
    - `chest_pain` - 2 points
    - `shortness_breath` - 2 points
    - `palpitations` - 3 points
    - `lightheadedness` - 3 points
    - `swelling` - 2 points
    - `poor_exercise_tolerance` - 3 points
    - `fatigue` - 4 points
    - `nausea` - 3 points
    - `sweating` - 2 points
    - `none` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Symptom q1 severity
- **Field ID:** `symptom_q1_severity`
- **Question:** How severe are your Heart Health symptoms?
- **Type:** `radio` (Single selection)
- **Required:** No
- **Answer Options:**
  - `none` - None
  - `mild` - Mild - Noticeable but not interfering with daily activities
  - `moderate` - Moderate - Some interference with daily activities
  - `severe` - Severe - Significant interference with daily activities
- **Scoring:**
  - **Category:** Symptom Severity
  - **Weight:** 2.5
  - **Answers:**
    - `none` - 8 points
    - `mild` - 6 points
    - `moderate` - 4 points
    - `severe` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Symptom q1 frequency
- **Field ID:** `symptom_q1_frequency`
- **Question:** How often do you experience these symptoms?
- **Type:** `radio` (Single selection)
- **Required:** No
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely (few times per year)
  - `monthly` - Monthly
  - `weekly` - Weekly
  - `daily` - Daily
- **Scoring:**
  - **Category:** Symptom Frequency
  - **Weight:** 2
  - **Answers:**
    - `never` - 8 points
    - `rarely` - 7 points
    - `monthly` - 5 points
    - `weekly` - 3 points
    - `daily` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Symptom q2
- **Field ID:** `symptom_q2`
- **Question:** Please select any symptoms you are experiencing related to Cognitive Health.
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** No
- **Answer Options:**
  - `brain_fog` - Brain Fog or Mental Clarity Issues
  - `memory_loss` - Memory Loss or Forgetfulness
  - `poor_concentration` - Poor Concentration or Focus
  - `mental_fatigue` - Mental Fatigue
  - `mood_changes` - Mood Changes or Irritability
  - `sleep_issues` - Sleep Issues
  - `none` - None of the above
- **Scoring:**
  - **Category:** Cognitive Symptoms
  - **Weight:** 2.5
  - **Answers:**
    - `brain_fog` - 3 points
    - `memory_loss` - 2 points
    - `poor_concentration` - 3 points
    - `mental_fatigue` - 4 points
    - `mood_changes` - 4 points
    - `sleep_issues` - 4 points
    - `none` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Symptom q2 severity
- **Field ID:** `symptom_q2_severity`
- **Question:** How severe are your Cognitive Health symptoms?
- **Type:** `radio` (Single selection)
- **Required:** No
- **Answer Options:**
  - `none` - None
  - `mild` - Mild - Noticeable but not interfering with daily activities
  - `moderate` - Moderate - Some interference with daily activities
  - `severe` - Severe - Significant interference with daily activities
- **Scoring:**
  - **Category:** Symptom Severity
  - **Weight:** 2
  - **Answers:**
    - `none` - 8 points
    - `mild` - 6 points
    - `moderate` - 4 points
    - `severe` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Symptom q2 frequency
- **Field ID:** `symptom_q2_frequency`
- **Question:** How often do you experience these symptoms?
- **Type:** `radio` (Single selection)
- **Required:** No
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely (few times per year)
  - `monthly` - Monthly
  - `weekly` - Weekly
  - `daily` - Daily
- **Scoring:**
  - **Category:** Symptom Frequency
  - **Weight:** 1.5
  - **Answers:**
    - `never` - 8 points
    - `rarely` - 7 points
    - `monthly` - 5 points
    - `weekly` - 3 points
    - `daily` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Symptom q3
- **Field ID:** `symptom_q3`
- **Question:** Please select any symptoms you are experiencing related to Metabolic Health.
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** No
- **Answer Options:**
  - `weight_gain` - Unexplained Weight Gain
  - `fatigue` - Chronic Fatigue
  - `thirst` - Increased Thirst or Frequent Urination
  - `slow_healing` - Slow Wound Healing
  - `blurred_vision` - Blurred Vision
  - `numbness` - Numbness or Tingling in Extremities
  - `none` - None of the above
- **Scoring:**
  - **Category:** Metabolic Symptoms
  - **Weight:** 2.5
  - **Answers:**
    - `weight_gain` - 3 points
    - `fatigue` - 4 points
    - `thirst` - 2 points
    - `slow_healing` - 3 points
    - `blurred_vision` - 2 points
    - `numbness` - 2 points
    - `none` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 10: Symptom q3 severity
- **Field ID:** `symptom_q3_severity`
- **Question:** How severe are your Metabolic Health symptoms?
- **Type:** `radio` (Single selection)
- **Required:** No
- **Answer Options:**
  - `none` - None
  - `mild` - Mild - Noticeable but not interfering with daily activities
  - `moderate` - Moderate - Some interference with daily activities
  - `severe` - Severe - Significant interference with daily activities
- **Scoring:**
  - **Category:** Symptom Severity
  - **Weight:** 2
  - **Answers:**
    - `none` - 8 points
    - `mild` - 6 points
    - `moderate` - 4 points
    - `severe` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 11: Symptom q3 frequency
- **Field ID:** `symptom_q3_frequency`
- **Question:** How often do you experience these symptoms?
- **Type:** `radio` (Single selection)
- **Required:** No
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely (few times per year)
  - `monthly` - Monthly
  - `weekly` - Weekly
  - `daily` - Daily
- **Scoring:**
  - **Category:** Symptom Frequency
  - **Weight:** 1.5
  - **Answers:**
    - `never` - 8 points
    - `rarely` - 7 points
    - `monthly` - 5 points
    - `weekly` - 3 points
    - `daily` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 12: Symptom q4
- **Field ID:** `symptom_q4`
- **Question:** Please select any symptoms you are experiencing related to Immune Health.
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** No
- **Answer Options:**
  - `frequent_illness` - Frequent Illness or Infections
  - `slow_recovery` - Slow Recovery from Illness
  - `allergies` - Allergies or Sensitivities
  - `inflammation` - Chronic Inflammation or Pain
  - `autoimmune` - Autoimmune Symptoms
  - `none` - None of the above
- **Scoring:**
  - **Category:** Immune Symptoms
  - **Weight:** 2
  - **Answers:**
    - `frequent_illness` - 2 points
    - `slow_recovery` - 3 points
    - `allergies` - 4 points
    - `inflammation` - 3 points
    - `autoimmune` - 2 points
    - `none` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 13: Symptom q4 severity
- **Field ID:** `symptom_q4_severity`
- **Question:** How severe are your Immune Health symptoms?
- **Type:** `radio` (Single selection)
- **Required:** No
- **Answer Options:**
  - `none` - None
  - `mild` - Mild - Noticeable but not interfering with daily activities
  - `moderate` - Moderate - Some interference with daily activities
  - `severe` - Severe - Significant interference with daily activities
- **Scoring:**
  - **Category:** Symptom Severity
  - **Weight:** 1.5
  - **Answers:**
    - `none` - 8 points
    - `mild` - 6 points
    - `moderate` - 4 points
    - `severe` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 14: Symptom q4 frequency
- **Field ID:** `symptom_q4_frequency`
- **Question:** How often do you experience these symptoms?
- **Type:** `radio` (Single selection)
- **Required:** No
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely (few times per year)
  - `monthly` - Monthly
  - `weekly` - Weekly
  - `daily` - Daily
- **Scoring:**
  - **Category:** Symptom Frequency
  - **Weight:** 1.5
  - **Answers:**
    - `never` - 8 points
    - `rarely` - 7 points
    - `monthly` - 5 points
    - `weekly` - 3 points
    - `daily` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

---

## Data Storage

### Assessment-Specific Meta Keys
- `ennu_health-optimization_q1` through `ennu_health-optimization_q25` - Individual question responses
- `ennu_health-optimization_overall_score` - Overall assessment score
- `ennu_health-optimization_category_scores` - Category-specific scores
- `ennu_health-optimization_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Health Optimization Assessment',
'assessment_engine' => 'qualitative',
'questions'         => array(
    // 25 comprehensive questions...
)
```

### Validation Rules
- All fields are required
- Valid date of birth
- Appropriate gender selection
- Multiple selections allowed where applicable

---

## User Experience Flow

1. **Introduction:** Overview of Health Optimization Assessment purpose
2. **Demographics:** Age and gender collection
3. **Assessment Questions:** Sequential completion of 25 questions
4. **Data Processing:** Data collection and analysis
5. **Results:** Personalized recommendations

---

## Integration Points

### Assessment Availability
- Available to All users
- Age-appropriate modifications
- Goal-aligned recommendations

### Data Synchronization
- Global data integration
- Cross-assessment correlations
- Treatment history tracking

### Recommendation Engine
- Personalized treatment plans
- Product recommendations
- Specialist referrals

---

## Future Enhancements

### Potential Additions
- Enhanced assessment questions
- Advanced scoring algorithms
- Integration with external data sources
- Real-time progress tracking
- Community support features

### Optimization Opportunities
- Streamlined user experience
- Enhanced data visualization
- Mobile optimization
- Advanced analytics dashboard

---

## Related Documentation

- [Assessment Configuration Guide](../config/assessment-configuration.md)
- [Data Management](../data/data-management.md)
- [User Experience Flow](../user-experience/flow.md)
- [Security & Privacy](../security/privacy.md)
