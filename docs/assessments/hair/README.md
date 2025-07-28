# Hair Assessment Documentation

**Assessment Type:** Hair Assessment
**Assessment Engine:** Quantitative
**Purpose:** Comprehensive evaluation of hair health and hair loss concerns
**Version:** 62.11.0
**Total Questions:** 10
**Gender Filter:** All users

---

## Overview

The Hair Assessment is designed to comprehensive evaluation of hair health and hair loss concerns. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** comprehensive evaluation of hair health and hair loss concerns
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** All users
- **Categories:** Multiple distinct scoring categories

---

## Questions & Answers

### Question 1: Hair q1
- **Field ID:** `hair_q1`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Hair q2
- **Field ID:** `hair_q2`
- **Question:** What is your gender?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Global Key:** `gender`
- **Answer Options:**
  - `female` - FEMALE
  - `male` - MALE
- **Scoring:**
  - **Category:** Genetic Factors
  - **Weight:** 2
  - **Answers:**
    - `female` - 5 points
    - `male` - 3 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Hair q3
- **Field ID:** `hair_q3`
- **Question:** What are your main hair concerns?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `thinning` - Thinning hair
  - `receding` - Receding hairline
  - `bald_spots` - Bald spots or patches
  - `overall_loss` - Overall hair loss and shedding
  - `dandruff` - Dandruff or flaky scalp
  - `brittleness` - Dryness or brittleness
- **Scoring:**
  - **Category:** Hair Health Status
  - **Weight:** 2.5
  - **Answers:**
    - `thinning` - 4 points
    - `receding` - 3 points
    - `bald_spots` - 2 points
    - `overall_loss` - 1 points
    - `dandruff` - 4 points
    - `brittleness` - 3 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Hair q4
- **Field ID:** `hair_q4`
- **Question:** How long have you been experiencing these concerns?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `less_than_6_months` - Less than 6 months
  - `6_to_12_months` - 6 to 12 months
  - `1_to_3_years` - 1 to 3 years
  - `more_than_3_years` - More than 3 years
- **Scoring:**
  - **Category:** Progression Timeline
  - **Weight:** 2
  - **Answers:**
    - `less_than_6_months` - 8 points
    - `6_to_12_months` - 6 points
    - `1_to_3_years` - 4 points
    - `more_than_3_years` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Hair q5
- **Field ID:** `hair_q5`
- **Question:** Does anyone in your immediate family have a history of hair loss?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `yes_both` - Yes, on both sides of the family
  - `yes_maternal` - Yes, on my mother's side
  - `yes_paternal` - Yes, on my father's side
  - `no` - No, not to my knowledge
- **Scoring:**
  - **Category:** Genetic Factors
  - **Weight:** 2.5
  - **Answers:**
    - `yes_both` - 1 points
    - `yes_maternal` - 3 points
    - `yes_paternal` - 2 points
    - `no` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Hair q6
- **Field ID:** `hair_q6`
- **Question:** How would you describe your current stress level?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `low` - Low
  - `moderate` - Moderate
  - `high` - High
  - `very_high` - Very High
- **Scoring:**
  - **Category:** Lifestyle Factors
  - **Weight:** 1.5
  - **Answers:**
    - `low` - 5 points
    - `moderate` - 4 points
    - `high` - 2 points
    - `very_high` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Hair q7
- **Field ID:** `hair_q7`
- **Question:** How does your hair concern affect your confidence or social life?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `not_at_all` - Not at all
  - `slightly` - Slightly
  - `moderately` - Moderately
  - `significantly` - Significantly
- **Scoring:**
  - **Category:** Psychological Factors
  - **Weight:** 2
  - **Answers:**
    - `not_at_all` - 5 points
    - `slightly` - 4 points
    - `moderately` - 2 points
    - `significantly` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Hair q8
- **Field ID:** `hair_q8`
- **Question:** How would you describe your diet's focus on hair-healthy nutrients (e.g., biotin, iron, zinc)?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very_good` - Very good, I focus on these nutrients
  - `good` - Good, I eat a balanced diet
  - `average` - Average, I don't pay much attention
  - `poor` - Poor, my diet lacks variety
- **Scoring:**
  - **Category:** Nutritional Support
  - **Weight:** 1.5
  - **Answers:**
    - `very_good` - 5 points
    - `good` - 4 points
    - `average` - 2 points
    - `poor` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Hair q9
- **Field ID:** `hair_q9`
- **Question:** Have you tried any hair loss treatments in the past?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `yes_effective` - Yes, and they were effective
  - `yes_ineffective` - Yes, but they were not effective
  - `no` - No, I have not tried any treatments
- **Scoring:**
  - **Category:** Treatment History
  - **Weight:** 1
  - **Answers:**
    - `yes_effective` - 5 points
    - `yes_ineffective` - 1 points
    - `no` - 3 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 10: Hair q10
- **Field ID:** `hair_q10`
- **Question:** Do you frequently use heat styling tools (e.g., blow dryers, straighteners) or chemical treatments?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely (once a month)
  - `sometimes` - Sometimes (once a week)
  - `often` - Often (most days)
- **Scoring:**
  - **Category:** Lifestyle Factors
  - **Weight:** 1
  - **Answers:**
    - `never` - 5 points
    - `rarely` - 4 points
    - `sometimes` - 2 points
    - `often` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

---

## Data Storage

### Assessment-Specific Meta Keys
- `ennu_hair_q1` through `ennu_hair_q10` - Individual question responses
- `ennu_hair_overall_score` - Overall assessment score
- `ennu_hair_category_scores` - Category-specific scores
- `ennu_hair_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Hair Assessment',
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

1. **Introduction:** Overview of Hair Assessment purpose
2. **Demographics:** Age and gender collection
3. **Assessment Questions:** Sequential completion of 10 questions
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
