# Menopause Assessment Documentation

**Assessment Type:** Menopause Assessment
**Assessment Engine:** Quantitative
**Purpose:** Menopausal symptoms and hormone transition evaluation
**Version:** 62.11.0
**Total Questions:** 10
**Gender Filter:** Female only

---

## Overview

The Menopause Assessment is designed to menopausal symptoms and hormone transition evaluation. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** menopausal symptoms and hormone transition evaluation
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** Female only
- **Categories:** Multiple distinct scoring categories

---

## Questions & Answers

### Question 1: Menopause q dob
- **Field ID:** `menopause_q_dob`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Scoring:**
  - **Category:** Age Factors
  - **Weight:** 1.5
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Menopause q1
- **Field ID:** `menopause_q1`
- **Question:** Which of the following menopausal symptoms are you experiencing?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `hot_flashes` - Hot flashes
  - `night_sweats` - Night sweats
  - `mood_swings` - Mood swings or irritability
  - `sleep_disturbances` - Sleep disturbances
  - `vaginal_dryness` - Vaginal dryness
  - `low_libido` - Low libido
  - `fatigue` - Fatigue and low energy
  - `brain_fog` - Brain fog or memory issues
  - `weight_gain` - Weight gain, especially around midsection
  - `hair_loss` - Hair loss or thinning
  - `skin_changes` - Skin changes (dryness, aging)
  - `none` - None of the above
- **Scoring:**
  - **Category:** Symptom Severity
  - **Weight:** 3
  - **Answers:**
    - `hot_flashes` - 2 points
    - `night_sweats` - 2 points
    - `mood_swings` - 3 points
    - `sleep_disturbances` - 2 points
    - `vaginal_dryness` - 2 points
    - `low_libido` - 2 points
    - `fatigue` - 3 points
    - `brain_fog` - 3 points
    - `weight_gain` - 2 points
    - `hair_loss` - 2 points
    - `skin_changes` - 2 points
    - `none` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Menopause q2
- **Field ID:** `menopause_q2`
- **Question:** Which stage of menopause do you believe you are in?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `premenopause` - Premenopause - Regular periods
  - `perimenopause` - Perimenopause - Irregular periods
  - `menopause` - Menopause - No periods for 12+ months
  - `postmenopause` - Postmenopause - 5+ years since last period
  - `unsure` - Unsure
- **Scoring:**
  - **Category:** Menopause Stage
  - **Weight:** 2
  - **Answers:**
    - `premenopause` - 8 points
    - `perimenopause` - 5 points
    - `menopause` - 4 points
    - `postmenopause` - 6 points
    - `unsure` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Menopause q3
- **Field ID:** `menopause_q3`
- **Question:** How would you rate the severity of your menopausal symptoms?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - None - No symptoms
  - `mild` - Mild - Noticeable but manageable
  - `moderate` - Moderate - Some interference with daily life
  - `severe` - Severe - Significant impact on daily life
  - `very_severe` - Very Severe - Major impact on quality of life
- **Scoring:**
  - **Category:** Symptom Impact
  - **Weight:** 2.5
  - **Answers:**
    - `none` - 8 points
    - `mild` - 6 points
    - `moderate` - 4 points
    - `severe` - 2 points
    - `very_severe` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Menopause q4
- **Field ID:** `menopause_q4`
- **Question:** How would you describe your current stress levels?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very_low` - Very Low - I rarely feel stressed
  - `low` - Low - I experience minimal stress
  - `moderate` - Moderate - I experience some stress but manage it well
  - `high` - High - I frequently feel stressed and overwhelmed
  - `very_high` - Very High - I feel constantly stressed and struggle to cope
- **Scoring:**
  - **Category:** Stress & Cortisol
  - **Weight:** 2
  - **Answers:**
    - `very_low` - 8 points
    - `low` - 7 points
    - `moderate` - 5 points
    - `high` - 3 points
    - `very_high` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Menopause q5
- **Field ID:** `menopause_q5`
- **Question:** How would you describe your sleep quality?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - Deep, restorative sleep every night
  - `good` - Good - Generally restful sleep most nights
  - `moderate` - Moderate - Some sleep issues but mostly restful
  - `poor` - Poor - Frequent sleep problems affecting daily life
  - `very_poor` - Very Poor - Severe sleep issues
- **Scoring:**
  - **Category:** Sleep Quality
  - **Weight:** 2
  - **Answers:**
    - `excellent` - 8 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Menopause q6
- **Field ID:** `menopause_q6`
- **Question:** Do you have a family history of hormonal disorders?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `none` - None known
  - `early_menopause` - Early menopause
  - `diabetes` - Diabetes (Type 1 or Type 2)
  - `thyroid` - Thyroid disorders
  - `pcos` - Polycystic Ovary Syndrome (PCOS)
  - `infertility` - Infertility issues
  - `other` - Other hormonal disorders
- **Scoring:**
  - **Category:** Family History
  - **Weight:** 1.5
  - **Answers:**
    - `none` - 8 points
    - `early_menopause` - 4 points
    - `diabetes` - 4 points
    - `thyroid` - 5 points
    - `pcos` - 4 points
    - `infertility` - 5 points
    - `other` - 4 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Menopause q7
- **Field ID:** `menopause_q7`
- **Question:** Have you had any recent blood tests for hormone levels?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - No recent tests
  - `basic` - Basic hormone panel
  - `comprehensive` - Comprehensive hormone panel
  - `regular` - Regular monitoring
- **Scoring:**
  - **Category:** Biomarker Monitoring
  - **Weight:** 1.5
  - **Answers:**
    - `none` - 5 points
    - `basic` - 6 points
    - `comprehensive` - 7 points
    - `regular` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Menopause q8
- **Field ID:** `menopause_q8`
- **Question:** How would you rate your overall hormonal health during menopause?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - Managing symptoms well
  - `good` - Good - Some symptoms but manageable
  - `moderate` - Moderate - Significant symptoms but coping
  - `poor` - Poor - Difficult symptoms affecting quality of life
  - `very_poor` - Very Poor - Severe symptoms significantly impacting life
- **Scoring:**
  - **Category:** Self-Assessment
  - **Weight:** 1.5
  - **Answers:**
    - `excellent` - 8 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

---

## Data Storage

### Assessment-Specific Meta Keys
- `ennu_menopause_q1` through `ennu_menopause_q10` - Individual question responses
- `ennu_menopause_overall_score` - Overall assessment score
- `ennu_menopause_category_scores` - Category-specific scores
- `ennu_menopause_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Menopause Assessment',
'assessment_engine' => 'quantitative',
'questions'         => array(
    // 10 comprehensive questions...
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

1. **Introduction:** Overview of Menopause Assessment purpose
2. **Demographics:** Age and gender collection
3. **Assessment Questions:** Sequential completion of 10 questions
4. **Data Processing:** Scoring and analysis
5. **Results:** Personalized score and recommendations

---

## Integration Points

### Assessment Availability
- Available to Female only
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
