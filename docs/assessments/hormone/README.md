# Hormone Assessment Documentation

**Assessment Type:** Hormone Assessment
**Assessment Engine:** Quantitative
**Purpose:** Hormonal balance and endocrine health evaluation
**Version:** 62.11.0
**Total Questions:** 11
**Gender Filter:** All users

---

## Overview

The Hormone Assessment is designed to hormonal balance and endocrine health evaluation. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** hormonal balance and endocrine health evaluation
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** All users
- **Categories:** Multiple distinct scoring categories

---

## Questions & Answers

### Question 1: Hormone q gender
- **Field ID:** `hormone_q_gender`
- **Question:** What is your gender?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Global Key:** `gender`
- **Answer Options:**
  - `female` - FEMALE
  - `male` - MALE
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Hormone q dob
- **Field ID:** `hormone_q_dob`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Scoring:**
  - **Category:** Age Factors
  - **Weight:** 1.5
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Hormone q1
- **Field ID:** `hormone_q1`
- **Question:** Which of the following symptoms of hormonal imbalance are you experiencing?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `fatigue` - Unexplained fatigue or energy loss
  - `mood_swings` - Mood swings, irritability, or anxiety
  - `weight_gain` - Unexplained weight gain, especially abdominal
  - `low_libido` - Low libido or sexual dysfunction
  - `sleep_issues` - Difficulty sleeping or night sweats
  - `brain_fog` - Brain fog or difficulty concentrating
  - `hot_flashes` - Hot flashes or temperature sensitivity
  - `hair_loss` - Hair loss or thinning
  - `skin_changes` - Skin changes (dryness, acne, aging)
  - `heart_palpitations` - Heart palpitations or irregular heartbeat
  - `digestive_issues` - Digestive issues or bloating
  - `joint_pain` - Joint pain or muscle weakness
  - `memory_problems` - Memory problems or forgetfulness
  - `depression` - Depression or low mood
  - `none` - None of the above
- **Scoring:**
  - **Category:** Symptom Severity
  - **Weight:** 3
  - **Answers:**
    - `fatigue` - 2 points
    - `mood_swings` - 2 points
    - `weight_gain` - 1 points
    - `low_libido` - 2 points
    - `sleep_issues` - 2 points
    - `brain_fog` - 2 points
    - `hot_flashes` - 1 points
    - `hair_loss` - 1 points
    - `skin_changes` - 2 points
    - `heart_palpitations` - 1 points
    - `digestive_issues` - 2 points
    - `joint_pain` - 1 points
    - `memory_problems` - 2 points
    - `depression` - 1 points
    - `none` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Hormone q2
- **Field ID:** `hormone_q2`
- **Question:** How would you rate your daily stress levels?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `low` - Low - I rarely feel stressed
  - `moderate` - Moderate - I experience some stress but manage it well
  - `high` - High - I frequently feel stressed and overwhelmed
  - `very_high` - Very High - I feel constantly stressed and struggle to cope
- **Scoring:**
  - **Category:** Stress & Cortisol
  - **Weight:** 2.5
  - **Answers:**
    - `low` - 9 points
    - `moderate` - 7 points
    - `high` - 4 points
    - `very_high` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Hormone q3
- **Field ID:** `hormone_q3`
- **Question:** How would you describe your energy levels throughout the day?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `high_consistent` - High and consistent throughout the day
  - `moderate_consistent` - Moderate and consistent
  - `morning_high` - High in morning, lower in afternoon
  - `afternoon_crash` - Good in morning, crash in afternoon
  - `consistently_low` - Consistently low throughout the day
- **Scoring:**
  - **Category:** Energy Patterns
  - **Weight:** 2
  - **Answers:**
    - `high_consistent` - 9 points
    - `moderate_consistent` - 7 points
    - `morning_high` - 5 points
    - `afternoon_crash` - 3 points
    - `consistently_low` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Hormone q4
- **Field ID:** `hormone_q4`
- **Question:** How would you describe your ability to focus and concentrate?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - No issues with focus
  - `good` - Good - Minor focus issues
  - `moderate` - Moderate - Some difficulty concentrating
  - `poor` - Poor - Significant focus problems
  - `very_poor` - Very Poor - Severe concentration issues
- **Scoring:**
  - **Category:** Cognitive Function
  - **Weight:** 2
  - **Answers:**
    - `excellent` - 9 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Hormone q5
- **Field ID:** `hormone_q5`
- **Question:** How many servings of cruciferous vegetables do you eat per week?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `0_1` - 0-1 servings
  - `2_3` - 2-3 servings
  - `4_6` - 4-6 servings
  - `7_10` - 7-10 servings
  - `more_than_10` - More than 10 servings
- **Scoring:**
  - **Category:** Diet & Nutrition
  - **Weight:** 1.5
  - **Answers:**
    - `0_1` - 3 points
    - `2_3` - 5 points
    - `4_6` - 7 points
    - `7_10` - 8 points
    - `more_than_10` - 9 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Hormone q6
- **Field ID:** `hormone_q6`
- **Question:** Are you currently taking any medications that could affect hormones?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `none` - None
  - `birth_control` - Birth control pills or hormonal contraceptives
  - `thyroid_meds` - Thyroid medications
  - `diabetes_meds` - Diabetes medications
  - `steroids` - Corticosteroids or steroids
  - `antidepressants` - Antidepressants
  - `hrt` - Hormone replacement therapy (HRT)
  - `testosterone` - Testosterone therapy
  - `other` - Other medications
- **Scoring:**
  - **Category:** Medication Impact
  - **Weight:** 2
  - **Answers:**
    - `none` - 9 points
    - `birth_control` - 6 points
    - `thyroid_meds` - 5 points
    - `diabetes_meds` - 4 points
    - `steroids` - 3 points
    - `antidepressants` - 6 points
    - `hrt` - 5 points
    - `testosterone` - 5 points
    - `other` - 4 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Hormone q7
- **Field ID:** `hormone_q7`
- **Question:** Do you have a family history of hormonal disorders?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `none` - None known
  - `diabetes` - Diabetes (Type 1 or Type 2)
  - `thyroid` - Thyroid disorders (hypothyroidism, hyperthyroidism)
  - `pcos` - Polycystic Ovary Syndrome (PCOS)
  - `menopause_early` - Early menopause
  - `infertility` - Infertility issues
  - `adrenal` - Adrenal disorders
  - `pituitary` - Pituitary disorders
  - `other` - Other hormonal disorders
- **Scoring:**
  - **Category:** Family History
  - **Weight:** 1.5
  - **Answers:**
    - `none` - 9 points
    - `diabetes` - 4 points
    - `thyroid` - 5 points
    - `pcos` - 4 points
    - `menopause_early` - 5 points
    - `infertility` - 6 points
    - `adrenal` - 3 points
    - `pituitary` - 3 points
    - `other` - 4 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 10: Hormone q8
- **Field ID:** `hormone_q8`
- **Question:** Have you had any recent blood tests for hormone levels?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - No recent tests
  - `basic` - Basic hormone panel
  - `comprehensive` - Comprehensive hormone panel
  - `specific` - Specific hormone tests (thyroid, testosterone, etc.)
  - `regular` - Regular monitoring
- **Scoring:**
  - **Category:** Biomarker Monitoring
  - **Weight:** 1.5
  - **Answers:**
    - `none` - 5 points
    - `basic` - 6 points
    - `comprehensive` - 8 points
    - `specific` - 7 points
    - `regular` - 9 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 11: Hormone q9
- **Field ID:** `hormone_q9`
- **Question:** Which of the following lifestyle factors apply to you?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `regular_exercise` - Regular exercise (3+ times per week)
  - `adequate_sleep` - Adequate sleep (7-9 hours per night)
  - `stress_management` - Stress management practices
  - `healthy_diet` - Healthy, balanced diet
  - `smoking` - Smoking or tobacco use
  - `excessive_alcohol` - Excessive alcohol consumption
  - `sedentary` - Sedentary lifestyle
  - `poor_sleep` - Poor sleep habits
  - `high_stress` - High stress lifestyle
  - `none` - None of the above
- **Scoring:**
  - **Category:** Lifestyle Factors
  - **Weight:** 2
  - **Answers:**
    - `regular_exercise` - 8 points
    - `adequate_sleep` - 8 points
    - `stress_management` - 7 points
    - `healthy_diet` - 8 points
    - `smoking` - 2 points
    - `excessive_alcohol` - 3 points
    - `sedentary` - 3 points
    - `poor_sleep` - 3 points
    - `high_stress` - 3 points
    - `none` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 12: Hormone q10
- **Field ID:** `hormone_q10`
- **Question:** How would you rate your overall hormonal health?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - No hormonal issues
  - `good` - Good - Minor hormonal fluctuations
  - `moderate` - Moderate - Some hormonal symptoms
  - `poor` - Poor - Significant hormonal issues
  - `very_poor` - Very Poor - Severe hormonal problems
- **Scoring:**
  - **Category:** Self-Assessment
  - **Weight:** 1.5
  - **Answers:**
    - `excellent` - 9 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

---

## Data Storage

### Assessment-Specific Meta Keys
- `ennu_hormone_q1` through `ennu_hormone_q11` - Individual question responses
- `ennu_hormone_overall_score` - Overall assessment score
- `ennu_hormone_category_scores` - Category-specific scores
- `ennu_hormone_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Hormone Assessment',
'assessment_engine' => 'quantitative',
'questions'         => array(
    // 11 comprehensive questions...
)
```

### Scoring Algorithm
- Weighted category scoring
- Overall score calculation
- Category-specific recommendations
- Historical trend analysis

### Validation Rules
- All fields are required
- Valid date of birth
- Appropriate gender selection
- Multiple selections allowed where applicable

---

## User Experience Flow

1. **Introduction:** Overview of Hormone Assessment purpose
2. **Demographics:** Age and gender collection
3. **Assessment Questions:** Sequential completion of 11 questions
4. **Data Processing:** Scoring and analysis
5. **Results:** Personalized score and recommendations

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
