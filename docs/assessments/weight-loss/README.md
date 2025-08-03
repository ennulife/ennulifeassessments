# Weight Loss Assessment Documentation

**Assessment Type:** Weight Loss Assessment
**Assessment Engine:** Quantitative
**Purpose:** Comprehensive weight management and metabolic health evaluation
**Version:** 62.11.0
**Total Questions:** 13
**Gender Filter:** All users

---

## Overview

The Weight Loss Assessment is designed to comprehensive weight management and metabolic health evaluation. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** comprehensive weight management and metabolic health evaluation
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** All users
- **Categories:** Multiple distinct scoring categories

---

## Questions & Answers

### Question 1: Wl q gender
- **Field ID:** `wl_q_gender`
- **Question:** What is your gender?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Global Key:** `gender`
- **Answer Options:**
  - `female` - FEMALE
  - `male` - MALE
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Wl q dob
- **Field ID:** `wl_q_dob`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Wl q1
- **Field ID:** `wl_q1`
- **Question:** What is your current height and weight?
- **Type:** `height_weight` (Custom input type)
- **Required:** Yes
- **Global Key:** `height_weight`
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Wl q2
- **Field ID:** `wl_q2`
- **Question:** What is your primary weight loss goal?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `lose_10_20` - Lose 10-20 lbs
  - `lose_20_50` - Lose 20-50 lbs
  - `lose_50_plus` - Lose 50+ lbs
  - `maintain` - Maintain current weight
- **Scoring:**
  - **Category:** Motivation & Goals
  - **Weight:** 2.5
  - **Answers:**
    - `lose_10_20` - 5 points
    - `lose_20_50` - 4 points
    - `lose_50_plus` - 3 points
    - `maintain` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Wl q3
- **Field ID:** `wl_q3`
- **Question:** How would you describe your typical diet?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `balanced` - Balanced and nutrient-rich
  - `processed` - High in processed foods
  - `low_carb` - Low-carb or ketogenic
  - `vegetarian` - Vegetarian or vegan
  - `intermittent_fasting` - Intermittent fasting
- **Scoring:**
  - **Category:** Nutrition
  - **Weight:** 2.5
  - **Answers:**
    - `balanced` - 5 points
    - `processed` - 1 points
    - `low_carb` - 4 points
    - `vegetarian` - 4 points
    - `intermittent_fasting` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Wl q4
- **Field ID:** `wl_q4`
- **Question:** How often do you exercise per week?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `5_plus` - 5 or more times
  - `3_4` - 3-4 times
  - `1_2` - 1-2 times
  - `none` - I rarely or never exercise
- **Scoring:**
  - **Category:** Physical Activity
  - **Weight:** 2
  - **Answers:**
    - `5_plus` - 5 points
    - `3_4` - 4 points
    - `1_2` - 2 points
    - `none` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Wl q5
- **Field ID:** `wl_q5`
- **Question:** How many hours of sleep do you typically get per night?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `less_than_5` - Less than 5 hours
  - `5_6` - 5-6 hours
  - `7_8` - 7-8 hours
  - `more_than_8` - More than 8 hours
- **Scoring:**
  - **Category:** Lifestyle Factors
  - **Weight:** 1.5
  - **Answers:**
    - `less_than_5` - 1 points
    - `5_6` - 2 points
    - `7_8` - 5 points
    - `more_than_8` - 4 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Wl q6
- **Field ID:** `wl_q6`
- **Question:** How would you rate your daily stress levels?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `low` - Low
  - `moderate` - Moderate
  - `high` - High
  - `very_high` - Very High
- **Scoring:**
  - **Category:** Psychological Factors
  - **Weight:** 2
  - **Answers:**
    - `low` - 5 points
    - `moderate` - 4 points
    - `high` - 2 points
    - `very_high` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Wl q7
- **Field ID:** `wl_q7`
- **Question:** What has been your experience with weight loss in the past?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never_success` - Never had lasting success
  - `some_success` - Some success, but gained it back
  - `good_success` - Good success, maintained for a while
  - `first_attempt` - This is my first serious attempt
- **Scoring:**
  - **Category:** Weight Loss History
  - **Weight:** 1.5
  - **Answers:**
    - `never_success` - 2 points
    - `some_success` - 3 points
    - `good_success` - 4 points
    - `first_attempt` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 10: Wl q8
- **Field ID:** `wl_q8`
- **Question:** Do you find yourself eating due to stress, boredom, or other emotional cues?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `often` - Often
  - `sometimes` - Sometimes
  - `rarely` - Rarely
  - `never` - Never
- **Scoring:**
  - **Category:** Behavioral Patterns
  - **Weight:** 2
  - **Answers:**
    - `often` - 1 points
    - `sometimes` - 2 points
    - `rarely` - 4 points
    - `never` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 11: Wl q9
- **Field ID:** `wl_q9`
- **Question:** Have you been diagnosed with any conditions that can affect weight?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `thyroid` - Thyroid Issues (e.g., Hypothyroidism)
  - `pcos` - Polycystic Ovary Syndrome (PCOS)
  - `insulin_resistance` - Insulin Resistance or Pre-diabetes
  - `none` - None of the above
- **Scoring:**
  - **Category:** Medical Factors
  - **Weight:** 2.5
  - **Answers:**
    - `thyroid` - 2 points
    - `pcos` - 2 points
    - `insulin_resistance` - 2 points
    - `none` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 12: Wl q10
- **Field ID:** `wl_q10`
- **Question:** How motivated are you to make lifestyle changes to achieve your weight goals?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very` - Very motivated
  - `somewhat` - Somewhat motivated
  - `not_very` - Not very motivated
  - `unsure` - I'm not sure
- **Scoring:**
  - **Category:** Motivation & Goals
  - **Weight:** 2
  - **Answers:**
    - `very` - 5 points
    - `somewhat` - 3 points
    - `not_very` - 1 points
    - `unsure` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 13: Wl q11
- **Field ID:** `wl_q11`
- **Question:** What is your primary body composition goal?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `lose_fat` - Lose Fat
  - `build_muscle` - Build Muscle
  - `both` - Both
- **Scoring:**
  - **Category:** Aesthetics
  - **Weight:** 1
  - **Answers:**
    - `lose_fat` - 4 points
    - `build_muscle` - 4 points
    - `both` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 14: Wl q12
- **Field ID:** `wl_q12`
- **Question:** Do you have a strong support system (family, friends) for your health journey?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `yes` - Yes, very supportive
  - `somewhat` - Somewhat supportive
  - `no` - No, I am mostly on my own
- **Scoring:**
  - **Category:** Social Support
  - **Weight:** 1
  - **Answers:**
    - `yes` - 5 points
    - `somewhat` - 3 points
    - `no` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 15: Wl q13
- **Field ID:** `wl_q13`
- **Question:** How confident are you in your ability to achieve your weight loss goals?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very` - Very confident
  - `somewhat` - Somewhat confident
  - `not_very` - Not very confident
- **Scoring:**
  - **Category:** Psychological Factors
  - **Weight:** 1.5
  - **Answers:**
    - `very` - 5 points
    - `somewhat` - 3 points
    - `not_very` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

---

## Data Storage

### Assessment-Specific Meta Keys
- `ennu_weight-loss_q1` through `ennu_weight-loss_q13` - Individual question responses
- `ennu_weight-loss_overall_score` - Overall assessment score
- `ennu_weight-loss_category_scores` - Category-specific scores
- `ennu_weight-loss_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Weight Loss Assessment',
'assessment_engine' => 'quantitative',
'questions'         => array(
    // 13 comprehensive questions...
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

1. **Introduction:** Overview of Weight Loss Assessment purpose
2. **Demographics:** Age and gender collection
3. **Assessment Questions:** Sequential completion of 13 questions
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
