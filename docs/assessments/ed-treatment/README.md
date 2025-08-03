# ED Treatment Assessment Documentation

**Assessment Type:** ED Treatment Assessment
**Assessment Engine:** Quantitative
**Purpose:** Evaluation of erectile dysfunction and treatment options
**Version:** 62.11.0
**Total Questions:** 12
**Gender Filter:** Male only

---

## Overview

The ED Treatment Assessment is designed to evaluation of erectile dysfunction and treatment options. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** evaluation of erectile dysfunction and treatment options
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** Male only
- **Categories:** Multiple distinct scoring categories

---

## Questions & Answers

### Question 1: Ed q dob
- **Field ID:** `ed_q_dob`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Scoring:**
  - **Category:** Age Factors
  - **Weight:** 1.5
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Ed q1
- **Field ID:** `ed_q1`
- **Question:** How would you describe your ability to achieve and maintain an erection sufficient for satisfactory sexual performance?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `always` - Almost always or always
  - `usually` - Usually (more than half the time)
  - `sometimes` - Sometimes (about half the time)
  - `rarely` - Rarely or never
- **Scoring:**
  - **Category:** Condition Severity
  - **Weight:** 3
  - **Answers:**
    - `always` - 9 points
    - `usually` - 7 points
    - `sometimes` - 4 points
    - `rarely` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Ed q2
- **Field ID:** `ed_q2`
- **Question:** How would you rate your sexual desire and libido?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - High sexual desire
  - `good` - Good - Normal sexual desire
  - `moderate` - Moderate - Somewhat reduced desire
  - `low` - Low - Significantly reduced desire
  - `very_low` - Very Low - Minimal sexual desire
- **Scoring:**
  - **Category:** Sexual Desire
  - **Weight:** 2.5
  - **Answers:**
    - `excellent` - 8 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `low` - 3 points
    - `very_low` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Ed q3
- **Field ID:** `ed_q3`
- **Question:** Have you been diagnosed with any of the following medical conditions?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `diabetes` - Diabetes (Type 1 or Type 2)
  - `heart_disease` - Heart Disease
  - `high_blood_pressure` - High Blood Pressure
  - `high_cholesterol` - High Cholesterol
  - `obesity` - Obesity
  - `prostate_issues` - Prostate Issues
  - `thyroid_disorders` - Thyroid Disorders
  - `none` - None of the above
- **Scoring:**
  - **Category:** Medical Factors
  - **Weight:** 2.5
  - **Answers:**
    - `diabetes` - 2 points
    - `heart_disease` - 1 points
    - `high_blood_pressure` - 2 points
    - `high_cholesterol` - 3 points
    - `obesity` - 2 points
    - `prostate_issues` - 1 points
    - `thyroid_disorders` - 3 points
    - `none` - 7 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Ed q4
- **Field ID:** `ed_q4`
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
  - **Category:** Psychological Factors
  - **Weight:** 2
  - **Answers:**
    - `very_low` - 8 points
    - `low` - 7 points
    - `moderate` - 5 points
    - `high` - 3 points
    - `very_high` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Ed q5
- **Field ID:** `ed_q5`
- **Question:** How would you describe your relationship satisfaction?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - Very satisfied with relationship
  - `good` - Good - Generally satisfied
  - `moderate` - Moderate - Some relationship issues
  - `poor` - Poor - Significant relationship problems
  - `very_poor` - Very Poor - Major relationship issues
- **Scoring:**
  - **Category:** Relationship Factors
  - **Weight:** 2
  - **Answers:**
    - `excellent` - 8 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Ed q6
- **Field ID:** `ed_q6`
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
  - **Weight:** 1.5
  - **Answers:**
    - `excellent` - 8 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Ed q7
- **Field ID:** `ed_q7`
- **Question:** How would you describe your overall cardiovascular health?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - No cardiovascular issues
  - `good` - Good - Minor cardiovascular concerns
  - `moderate` - Moderate - Some cardiovascular issues
  - `poor` - Poor - Significant cardiovascular problems
  - `very_poor` - Very Poor - Severe cardiovascular issues
- **Scoring:**
  - **Category:** Cardiovascular Health
  - **Weight:** 2.5
  - **Answers:**
    - `excellent` - 8 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Ed q8
- **Field ID:** `ed_q8`
- **Question:** How would you rate your overall ED treatment readiness?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - Ready for comprehensive treatment
  - `good` - Good - Ready for treatment with some concerns
  - `moderate` - Moderate - Somewhat ready for treatment
  - `poor` - Poor - Not very ready for treatment
  - `very_poor` - Very Poor - Not ready for treatment
- **Scoring:**
  - **Category:** Treatment Readiness
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
- `ennu_ed-treatment_q1` through `ennu_ed-treatment_q12` - Individual question responses
- `ennu_ed-treatment_overall_score` - Overall assessment score
- `ennu_ed-treatment_category_scores` - Category-specific scores
- `ennu_ed-treatment_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'ED Treatment Assessment',
'assessment_engine' => 'quantitative',
'questions'         => array(
    // 12 comprehensive questions...
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

1. **Introduction:** Overview of ED Treatment Assessment purpose
2. **Demographics:** Age and gender collection
3. **Assessment Questions:** Sequential completion of 12 questions
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
