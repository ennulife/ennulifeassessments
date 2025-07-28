# Welcome Assessment Documentation

**Assessment Type:** Welcome Assessment
**Assessment Engine:** Qualitative
**Purpose:** Foundation data gathering and user onboarding
**Version:** 62.11.0
**Total Questions:** 3
**Gender Filter:** All users

---

## Overview

The Welcome Assessment is designed to foundation data gathering and user onboarding. This assessment focuses on gathering qualitative data to understand user needs and preferences.

### Key Characteristics
- **Assessment Type:** Qualitative (not scored)
- **Primary Purpose:** foundation data gathering and user onboarding
- **Scoring System:** Qualitative data collection
- **Gender Filter:** All users
- **Categories:** Data collection categories

---

## Questions & Answers

### Question 1: Welcome q1
- **Field ID:** `welcome_q1`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Welcome q2
- **Field ID:** `welcome_q2`
- **Question:** What is your gender?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Global Key:** `gender`
- **Answer Options:**
  - `female` - FEMALE
  - `male` - MALE
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Welcome q3
- **Field ID:** `welcome_q3`
- **Question:** What are your primary health goals?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Global Key:** `health_goals`
- **Answer Options:**
  - `longevity` - Longevity & Healthy Aging
  - `energy` - Improve Energy & Vitality
  - `strength` - Build Strength & Muscle
  - `libido` - Enhance Libido & Sexual Health
  - `weight_loss` - Achieve & Maintain Healthy Weight
  - `hormonal_balance` - Hormonal Balance
  - `cognitive_health` - Sharpen Cognitive Function
  - `heart_health` - Support Heart Health
  - `aesthetics` - Improve Hair, Skin & Nails
  - `sleep` - Improve Sleep Quality
  - `stress` - Reduce Stress & Improve Resilience
- **Description:** Collects relevant information for assessment evaluation.

---

## Data Storage

### Assessment-Specific Meta Keys
- `ennu_welcome_q1` through `ennu_welcome_q3` - Individual question responses
- `ennu_welcome_overall_score` - Overall assessment score
- `ennu_welcome_category_scores` - Category-specific scores
- `ennu_welcome_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Welcome Assessment',
'assessment_engine' => 'qualitative',
'questions'         => array(
    // 3 comprehensive questions...
)
```

### Validation Rules
- All fields are required
- Valid date of birth
- Appropriate gender selection
- Multiple selections allowed where applicable

---

## User Experience Flow

1. **Introduction:** Overview of Welcome Assessment purpose
2. **Demographics:** Age and gender collection
3. **Assessment Questions:** Sequential completion of 3 questions
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
