# Testosterone Assessment Documentation

**Assessment Type:** Testosterone Assessment
**Assessment Engine:** Quantitative
**Purpose:** Testosterone levels and related symptoms evaluation
**Version:** 62.11.0
**Total Questions:** 10
**Gender Filter:** Male only

---

## Overview

The Testosterone Assessment is designed to testosterone levels and related symptoms evaluation. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** testosterone levels and related symptoms evaluation
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** Male only
- **Categories:** Multiple distinct scoring categories

---

## Questions & Answers

### Question 1: Testosterone q dob
- **Field ID:** `testosterone_q_dob`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Scoring:**
  - **Category:** Age Factors
  - **Weight:** 1.5
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Testosterone q1
- **Field ID:** `testosterone_q1`
- **Question:** Which of the following symptoms, often associated with low testosterone, are you experiencing?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `low_libido` - Low libido or decreased sexual desire
  - `fatigue` - Fatigue and decreased energy levels
  - `muscle_loss` - Loss of muscle mass or strength
  - `increased_fat` - Increased body fat, especially around the midsection
  - `mood_changes` - Mood changes, irritability, or depression
  - `erectile_dysfunction` - Erectile dysfunction
  - `decreased_motivation` - Decreased motivation and drive
  - `poor_concentration` - Poor concentration and memory
  - `sleep_issues` - Sleep problems or insomnia
  - `hot_flashes` - Hot flashes or night sweats
  - `none` - None of the above
- **Scoring:**
  - **Category:** Symptom Severity
  - **Weight:** 3
  - **Answers:**
    - `low_libido` - 2 points
    - `fatigue` - 2 points
    - `muscle_loss` - 1 points
    - `increased_fat` - 1 points
    - `mood_changes` - 2 points
    - `erectile_dysfunction` - 1 points
    - `decreased_motivation` - 2 points
    - `poor_concentration` - 2 points
    - `sleep_issues` - 2 points
    - `hot_flashes` - 1 points
    - `none` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Testosterone q2
- **Field ID:** `testosterone_q2`
- **Question:** How many days per week do you engage in resistance training (weight lifting)?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `5_plus` - 5 or more days
  - `3_4` - 3-4 days
  - `1_2` - 1-2 days
  - `none` - I do not do resistance training
- **Scoring:**
  - **Category:** Exercise & Lifestyle
  - **Weight:** 2.5
  - **Answers:**
    - `5_plus` - 8 points
    - `3_4` - 7 points
    - `1_2` - 5 points
    - `none` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Testosterone q3
- **Field ID:** `testosterone_q3`
- **Question:** How would you rate your current stress levels?
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
  - **Weight:** 2.5
  - **Answers:**
    - `very_low` - 8 points
    - `low` - 7 points
    - `moderate` - 5 points
    - `high` - 3 points
    - `very_high` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Testosterone q4
- **Field ID:** `testosterone_q4`
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

### Question 6: Testosterone q5
- **Field ID:** `testosterone_q5`
- **Question:** How would you describe your body composition and muscle mass?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - High muscle mass, low body fat
  - `good` - Good - Good muscle mass, healthy body fat
  - `moderate` - Moderate - Some muscle mass, moderate body fat
  - `poor` - Poor - Low muscle mass, high body fat
  - `very_poor` - Very Poor - Very low muscle mass, high body fat
- **Scoring:**
  - **Category:** Body Composition
  - **Weight:** 2
  - **Answers:**
    - `excellent` - 8 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Testosterone q6
- **Field ID:** `testosterone_q6`
- **Question:** Do you have a family history of hormonal disorders?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `none` - None known
  - `diabetes` - Diabetes (Type 1 or Type 2)
  - `thyroid` - Thyroid disorders
  - `low_testosterone` - Low testosterone
  - `infertility` - Infertility issues
  - `other` - Other hormonal disorders
- **Scoring:**
  - **Category:** Family History
  - **Weight:** 1.5
  - **Answers:**
    - `none` - 8 points
    - `diabetes` - 4 points
    - `thyroid` - 5 points
    - `low_testosterone` - 3 points
    - `infertility` - 5 points
    - `other` - 4 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Testosterone q7
- **Field ID:** `testosterone_q7`
- **Question:** Have you had any recent blood tests for testosterone levels?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - No recent tests
  - `basic` - Basic testosterone test
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

### Question 9: Testosterone q8
- **Field ID:** `testosterone_q8`
- **Question:** How would you rate your overall testosterone health?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - No testosterone-related issues
  - `good` - Good - Minor testosterone fluctuations
  - `moderate` - Moderate - Some testosterone symptoms
  - `poor` - Poor - Significant testosterone issues
  - `very_poor` - Very Poor - Severe testosterone problems
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
- `ennu_testosterone_q1` through `ennu_testosterone_q10` - Individual question responses
- `ennu_testosterone_overall_score` - Overall assessment score
- `ennu_testosterone_category_scores` - Category-specific scores
- `ennu_testosterone_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Testosterone Assessment',
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

1. **Introduction:** Overview of Testosterone Assessment purpose
2. **Demographics:** Age and gender collection
3. **Assessment Questions:** Sequential completion of 10 questions
4. **Data Processing:** Scoring and analysis
5. **Results:** Personalized score and recommendations

---

## Integration Points

### Assessment Availability
- Available to Male only
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
