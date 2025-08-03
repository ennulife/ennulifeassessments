# Health Assessment Documentation

**Assessment Type:** Health Assessment
**Assessment Engine:** Quantitative
**Purpose:** Holistic health evaluation and wellness assessment
**Version:** 62.11.0
**Total Questions:** 11
**Gender Filter:** All users

---

## Overview

The Health Assessment is designed to holistic health evaluation and wellness assessment. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** holistic health evaluation and wellness assessment
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** All users
- **Categories:** Multiple distinct scoring categories

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

### Question 3: Health q1
- **Field ID:** `health_q1`
- **Question:** How would you rate your current overall health?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - I feel great and have no health concerns
  - `good` - Good - I feel generally healthy with minor issues
  - `fair` - Fair - I have some health concerns that affect me
  - `poor` - Poor - I have significant health issues
  - `very_poor` - Very Poor - I have serious health problems
- **Scoring:**
  - **Category:** Current Health Status
  - **Weight:** 3
  - **Answers:**
    - `excellent` - 9 points
    - `good` - 7 points
    - `fair` - 5 points
    - `poor` - 3 points
    - `very_poor` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Health q2
- **Field ID:** `health_q2`
- **Question:** How are your energy levels throughout the day?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `high_consistent` - High and consistent throughout the day
  - `moderate_consistent` - Moderate and consistent
  - `morning_high` - High in morning, lower in afternoon
  - `afternoon_crash` - Good in morning, crash in afternoon
  - `consistently_low` - Consistently low throughout the day
- **Scoring:**
  - **Category:** Vitality & Energy
  - **Weight:** 2.5
  - **Answers:**
    - `high_consistent` - 9 points
    - `moderate_consistent` - 7 points
    - `morning_high` - 5 points
    - `afternoon_crash` - 3 points
    - `consistently_low` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Health q3
- **Field ID:** `health_q3`
- **Question:** How many days per week do you engage in moderate to vigorous exercise?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `6_plus` - 6 or more days
  - `4_5` - 4-5 days
  - `2_3` - 2-3 days
  - `1` - 1 day
  - `none` - I rarely or never exercise
- **Scoring:**
  - **Category:** Physical Activity
  - **Weight:** 2.5
  - **Answers:**
    - `6_plus` - 9 points
    - `4_5` - 7 points
    - `2_3` - 5 points
    - `1` - 3 points
    - `none` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Health q4
- **Field ID:** `health_q4`
- **Question:** How would you describe your typical diet?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `whole_foods` - Primarily whole foods, rich in fruits and vegetables
  - `balanced` - Generally balanced with some processed foods
  - `mixed` - Mixed - some healthy, some processed foods
  - `processed` - High in processed foods and sugar
  - `irregular` - Irregular or often skip meals
- **Scoring:**
  - **Category:** Nutrition
  - **Weight:** 2.5
  - **Answers:**
    - `whole_foods` - 9 points
    - `balanced` - 7 points
    - `mixed` - 5 points
    - `processed` - 3 points
    - `irregular` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Health q5
- **Field ID:** `health_q5`
- **Question:** How would you rate your mental health and emotional well-being?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - I feel mentally strong and emotionally stable
  - `good` - Good - Generally positive mood with minor fluctuations
  - `moderate` - Moderate - Some mood swings but manageable
  - `poor` - Poor - Frequent mood issues affecting daily life
  - `very_poor` - Very Poor - Significant mental health struggles
- **Scoring:**
  - **Category:** Mental Health
  - **Weight:** 3
  - **Answers:**
    - `excellent` - 9 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Health q6
- **Field ID:** `health_q6`
- **Question:** How would you describe your stress levels?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very_low` - Very Low - I rarely feel stressed
  - `low` - Low - I experience minimal stress
  - `moderate` - Moderate - I experience some stress but manage it well
  - `high` - High - I frequently feel stressed and overwhelmed
  - `very_high` - Very High - I feel constantly stressed and struggle to cope
- **Scoring:**
  - **Category:** Stress Management
  - **Weight:** 2.5
  - **Answers:**
    - `very_low` - 9 points
    - `low` - 7 points
    - `moderate` - 5 points
    - `high` - 3 points
    - `very_high` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Health q7
- **Field ID:** `health_q7`
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
  - **Weight:** 2.5
  - **Answers:**
    - `excellent` - 9 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 10: Health q8
- **Field ID:** `health_q8`
- **Question:** How would you describe your social connections and relationships?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - Strong, supportive relationships
  - `good` - Good - Generally positive relationships
  - `moderate` - Moderate - Some good relationships, some challenges
  - `poor` - Poor - Limited or strained relationships
  - `very_poor` - Very Poor - Isolated or toxic relationships
- **Scoring:**
  - **Category:** Social Health
  - **Weight:** 2
  - **Answers:**
    - `excellent` - 9 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 11: Health q9
- **Field ID:** `health_q9`
- **Question:** How would you describe your ability to focus and concentrate?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - No issues with focus or concentration
  - `good` - Good - Minor focus issues, easily managed
  - `moderate` - Moderate - Some difficulty concentrating at times
  - `poor` - Poor - Significant focus problems affecting work/life
  - `very_poor` - Very Poor - Severe concentration issues
- **Scoring:**
  - **Category:** Cognitive Function
  - **Weight:** 2
  - **Answers:**
    - `excellent` - 9 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 12: Health q10
- **Field ID:** `health_q10`
- **Question:** How would you describe your overall mood?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very_positive` - Very Positive - Optimistic and happy most of the time
  - `positive` - Positive - Generally good mood with occasional lows
  - `neutral` - Neutral - Stable mood, neither particularly high nor low
  - `negative` - Negative - Often feeling down or irritable
  - `very_negative` - Very Negative - Frequently depressed or anxious
- **Scoring:**
  - **Category:** Mood Assessment
  - **Weight:** 2.5
  - **Answers:**
    - `very_positive` - 9 points
    - `positive` - 7 points
    - `neutral` - 5 points
    - `negative` - 3 points
    - `very_negative` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 13: Health q11
- **Field ID:** `health_q11`
- **Question:** How would you describe your motivation and drive?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very_high` - Very High - Highly motivated and driven
  - `high` - High - Generally motivated with clear goals
  - `moderate` - Moderate - Some motivation but could be stronger
  - `low` - Low - Often struggle with motivation
  - `very_low` - Very Low - Frequently lack motivation and drive
- **Scoring:**
  - **Category:** Motivation & Drive
  - **Weight:** 2
  - **Answers:**
    - `very_high` - 9 points
    - `high` - 7 points
    - `moderate` - 5 points
    - `low` - 3 points
    - `very_low` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 14: Health q12
- **Field ID:** `health_q12`
- **Question:** How would you describe your overall life satisfaction?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very_satisfied` - Very Satisfied - I am very happy with my life
  - `satisfied` - Satisfied - I am generally happy with my life
  - `neutral` - Neutral - I am neither particularly satisfied nor dissatisfied
  - `dissatisfied` - Dissatisfied - I am often unhappy with my life
  - `very_dissatisfied` - Very Dissatisfied - I am very unhappy with my life
- **Scoring:**
  - **Category:** Life Satisfaction
  - **Weight:** 2.5
  - **Answers:**
    - `very_satisfied` - 9 points
    - `satisfied` - 7 points
    - `neutral` - 5 points
    - `dissatisfied` - 3 points
    - `very_dissatisfied` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

---

## Data Storage

### Assessment-Specific Meta Keys
- `ennu_health_q1` through `ennu_health_q11` - Individual question responses
- `ennu_health_overall_score` - Overall assessment score
- `ennu_health_category_scores` - Category-specific scores
- `ennu_health_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Health Assessment',
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

1. **Introduction:** Overview of Health Assessment purpose
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
