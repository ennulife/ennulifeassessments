# Sleep Assessment Documentation

**Assessment Type:** Sleep Assessment
**Assessment Engine:** Quantitative
**Purpose:** Sleep quality and pattern evaluation
**Version:** 62.11.0
**Total Questions:** 11
**Gender Filter:** All users

---

## Overview

The Sleep Assessment is designed to sleep quality and pattern evaluation. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** sleep quality and pattern evaluation
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** All users
- **Categories:** Multiple distinct scoring categories

---

## Questions & Answers

### Question 1: Sleep q gender
- **Field ID:** `sleep_q_gender`
- **Question:** What is your gender?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Global Key:** `gender`
- **Answer Options:**
  - `female` - FEMALE
  - `male` - MALE
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Sleep q dob
- **Field ID:** `sleep_q_dob`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Scoring:**
  - **Category:** Age Factors
  - **Weight:** 1.5
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Sleep q1
- **Field ID:** `sleep_q1`
- **Question:** On average, how many hours of sleep do you get per night?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `less_than_5` - Less than 5 hours
  - `5_6` - 5-6 hours
  - `7_8` - 7-8 hours (Recommended)
  - `8_9` - 8-9 hours
  - `more_than_9` - More than 9 hours
- **Scoring:**
  - **Category:** Sleep Duration
  - **Weight:** 2.5
  - **Answers:**
    - `less_than_5` - 2 points
    - `5_6` - 4 points
    - `7_8` - 9 points
    - `8_9` - 7 points
    - `more_than_9` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Sleep q2
- **Field ID:** `sleep_q2`
- **Question:** How would you rate the quality of your sleep?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - Deep, restorative sleep
  - `very_good` - Very Good - Generally restful
  - `good` - Good - Mostly restful
  - `fair` - Fair - Somewhat restful
  - `poor` - Poor - Rarely feel rested
- **Scoring:**
  - **Category:** Sleep Quality
  - **Weight:** 3
  - **Answers:**
    - `excellent` - 9 points
    - `very_good` - 7 points
    - `good` - 5 points
    - `fair` - 3 points
    - `poor` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Sleep q3
- **Field ID:** `sleep_q3`
- **Question:** How often do you wake up during the night?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never - Sleep through the night
  - `rarely` - Rarely (1-2 times per week)
  - `sometimes` - Sometimes (1-2 times per night)
  - `often` - Often (3+ times per night)
  - `every_night` - Almost every night (multiple times)
- **Scoring:**
  - **Category:** Sleep Continuity
  - **Weight:** 2.5
  - **Answers:**
    - `never` - 9 points
    - `rarely` - 7 points
    - `sometimes` - 5 points
    - `often` - 3 points
    - `every_night` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Sleep q4
- **Field ID:** `sleep_q4`
- **Question:** How long does it typically take you to fall asleep?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `under_15` - Under 15 minutes
  - `15_30` - 15-30 minutes
  - `30_60` - 30-60 minutes
  - `over_60` - Over 60 minutes
- **Scoring:**
  - **Category:** Sleep Latency
  - **Weight:** 2
  - **Answers:**
    - `under_15` - 9 points
    - `15_30` - 7 points
    - `30_60` - 4 points
    - `over_60` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Sleep q5
- **Field ID:** `sleep_q5`
- **Question:** How do you feel when you wake up in the morning?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `refreshed` - Refreshed and energetic
  - `okay` - Okay, but could be better
  - `groggy` - Groggy and unrested
  - `exhausted` - Exhausted
- **Scoring:**
  - **Category:** Daytime Function
  - **Weight:** 2.5
  - **Answers:**
    - `refreshed` - 9 points
    - `okay` - 6 points
    - `groggy` - 3 points
    - `exhausted` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Sleep q6
- **Field ID:** `sleep_q6`
- **Question:** How often do you feel drowsy or have the urge to nap during the day?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely
  - `sometimes` - Sometimes
  - `often` - Often
  - `daily` - Daily
- **Scoring:**
  - **Category:** Daytime Sleepiness
  - **Weight:** 2
  - **Answers:**
    - `never` - 9 points
    - `rarely` - 7 points
    - `sometimes` - 5 points
    - `often` - 3 points
    - `daily` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Sleep q7
- **Field ID:** `sleep_q7`
- **Question:** Do you snore loudly or have been told you snore?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely
  - `sometimes` - Sometimes
  - `often` - Often
  - `every_night` - Every night
- **Scoring:**
  - **Category:** Sleep Apnea Risk
  - **Weight:** 2.5
  - **Answers:**
    - `never` - 9 points
    - `rarely` - 7 points
    - `sometimes` - 5 points
    - `often` - 3 points
    - `every_night` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 10: Sleep q8
- **Field ID:** `sleep_q8`
- **Question:** Have you been told you stop breathing or gasp for air during sleep?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely
  - `sometimes` - Sometimes
  - `often` - Often
  - `every_night` - Every night
- **Scoring:**
  - **Category:** Sleep Apnea Risk
  - **Weight:** 3
  - **Answers:**
    - `never` - 9 points
    - `rarely` - 6 points
    - `sometimes` - 4 points
    - `often` - 2 points
    - `every_night` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 11: Sleep q9
- **Field ID:** `sleep_q9`
- **Question:** Do you experience restless legs or an urge to move your legs at night?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely
  - `sometimes` - Sometimes
  - `often` - Often
  - `every_night` - Every night
- **Scoring:**
  - **Category:** Restless Leg Syndrome
  - **Weight:** 2
  - **Answers:**
    - `never` - 9 points
    - `rarely` - 7 points
    - `sometimes` - 5 points
    - `often` - 3 points
    - `every_night` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 12: Sleep q10
- **Field ID:** `sleep_q10`
- **Question:** Do you experience vivid dreams, nightmares, or sleep paralysis?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely
  - `sometimes` - Sometimes
  - `often` - Often
  - `every_night` - Every night
- **Scoring:**
  - **Category:** Sleep Disorders
  - **Weight:** 2
  - **Answers:**
    - `never` - 9 points
    - `rarely` - 7 points
    - `sometimes` - 5 points
    - `often` - 3 points
    - `every_night` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 13: Sleep q11
- **Field ID:** `sleep_q11`
- **Question:** What do you typically do within 1 hour before bedtime?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `screen_time` - Use phone, computer, or watch TV
  - `caffeine` - Consume caffeine (coffee, tea, energy drinks)
  - `alcohol` - Consume alcohol
  - `large_meal` - Eat a large meal
  - `exercise` - Engage in vigorous exercise
  - `work` - Work or study
  - `relaxation` - Relaxation techniques (meditation, reading)
  - `nothing` - Nothing specific
- **Scoring:**
  - **Category:** Sleep Hygiene
  - **Weight:** 1.5
  - **Answers:**
    - `screen_time` - 3 points
    - `caffeine` - 2 points
    - `alcohol` - 4 points
    - `large_meal` - 4 points
    - `exercise` - 4 points
    - `work` - 3 points
    - `relaxation` - 8 points
    - `nothing` - 6 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 14: Sleep q12
- **Field ID:** `sleep_q12`
- **Question:** What is your typical sleep schedule?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `consistent` - Very consistent (same time every night)
  - `mostly_consistent` - Mostly consistent (within 1 hour)
  - `somewhat_consistent` - Somewhat consistent (within 2 hours)
  - `irregular` - Irregular (varies by 2+ hours)
  - `very_irregular` - Very irregular (no set schedule)
- **Scoring:**
  - **Category:** Sleep Schedule
  - **Weight:** 2
  - **Answers:**
    - `consistent` - 9 points
    - `mostly_consistent` - 7 points
    - `somewhat_consistent` - 5 points
    - `irregular` - 3 points
    - `very_irregular` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 15: Sleep q13
- **Field ID:** `sleep_q13`
- **Question:** Do you use any sleep aids or medications?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - None
  - `melatonin` - Melatonin (natural)
  - `herbal` - Herbal supplements (valerian, chamomile)
  - `otc` - Over-the-counter sleep aids
  - `prescription` - Prescription sleep medication
- **Scoring:**
  - **Category:** Sleep Dependency
  - **Weight:** 1.5
  - **Answers:**
    - `none` - 9 points
    - `melatonin` - 6 points
    - `herbal` - 5 points
    - `otc` - 3 points
    - `prescription` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 16: Sleep q14
- **Field ID:** `sleep_q14`
- **Question:** How would you describe your bedroom environment?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `dark` - Very dark
  - `quiet` - Very quiet
  - `cool` - Cool temperature (65-68°F)
  - `comfortable` - Comfortable mattress and pillows
  - `bright` - Bright or light
  - `noisy` - Noisy
  - `warm` - Warm temperature
  - `uncomfortable` - Uncomfortable mattress or pillows
- **Scoring:**
  - **Category:** Sleep Environment
  - **Weight:** 1.5
  - **Answers:**
    - `dark` - 8 points
    - `quiet` - 8 points
    - `cool` - 8 points
    - `comfortable` - 8 points
    - `bright` - 3 points
    - `noisy` - 3 points
    - `warm` - 4 points
    - `uncomfortable` - 3 points
- **Description:** Collects relevant information for assessment evaluation.

---

## Data Storage

### Assessment-Specific Meta Keys
- `ennu_sleep_q1` through `ennu_sleep_q11` - Individual question responses
- `ennu_sleep_overall_score` - Overall assessment score
- `ennu_sleep_category_scores` - Category-specific scores
- `ennu_sleep_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Sleep Assessment',
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

1. **Introduction:** Overview of Sleep Assessment purpose
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
