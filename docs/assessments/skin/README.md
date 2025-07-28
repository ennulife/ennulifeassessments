# Skin Assessment Documentation

**Assessment Type:** Skin Assessment
**Assessment Engine:** Quantitative
**Purpose:** Comprehensive skin health and aesthetic evaluation
**Version:** 62.11.0
**Total Questions:** 11
**Gender Filter:** All users

---

## Overview

The Skin Assessment is designed to comprehensive skin health and aesthetic evaluation. This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.

### Key Characteristics
- **Assessment Type:** Quantitative (scored)
- **Primary Purpose:** comprehensive skin health and aesthetic evaluation
- **Scoring System:** Point-based with category weighting
- **Gender Filter:** All users
- **Categories:** Multiple distinct scoring categories

---

## Questions & Answers

### Question 1: Skin q gender
- **Field ID:** `skin_q_gender`
- **Question:** What is your gender?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Global Key:** `gender`
- **Answer Options:**
  - `female` - FEMALE
  - `male` - MALE
- **Description:** Collects relevant information for assessment evaluation.

### Question 2: Skin q dob
- **Field ID:** `skin_q_dob`
- **Question:** What is your date of birth?
- **Type:** `dob_dropdowns` (Date of birth dropdown selectors)
- **Required:** Yes
- **Global Key:** `date_of_birth`
- **Scoring:**
  - **Category:** Age Factors
  - **Weight:** 1.5
- **Description:** Collects relevant information for assessment evaluation.

### Question 3: Skin q1
- **Field ID:** `skin_q1`
- **Question:** What is your skin type?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `oily` - Oily - Shiny, prone to breakouts
  - `dry` - Dry - Flaky, tight feeling
  - `combination` - Combination - Oily T-zone, dry elsewhere
  - `normal` - Normal - Balanced, not too oily or dry
  - `sensitive` - Sensitive - Easily irritated, reactive
- **Scoring:**
  - **Category:** Skin Characteristics
  - **Weight:** 2
  - **Answers:**
    - `oily` - 4 points
    - `dry` - 4 points
    - `combination` - 5 points
    - `normal` - 7 points
    - `sensitive` - 3 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 4: Skin q2
- **Field ID:** `skin_q2`
- **Question:** What are your primary skin concerns?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `acne` - Acne and breakouts
  - `aging` - Fine lines and wrinkles
  - `pigmentation` - Dark spots and hyperpigmentation
  - `redness` - Redness and rosacea
  - `dullness` - Dullness and lack of radiance
  - `uneven_texture` - Uneven skin texture
  - `large_pores` - Large pores
  - `dark_circles` - Dark circles under eyes
  - `none` - None of the above
- **Scoring:**
  - **Category:** Skin Issues
  - **Weight:** 2.5
  - **Answers:**
    - `acne` - 3 points
    - `aging` - 4 points
    - `pigmentation` - 4 points
    - `redness` - 3 points
    - `dullness` - 5 points
    - `uneven_texture` - 4 points
    - `large_pores` - 4 points
    - `dark_circles` - 5 points
    - `none` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 5: Skin q3
- **Field ID:** `skin_q3`
- **Question:** How much time do you spend in the sun on an average day?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - Hardly any
  - `minimal` - Less than 30 minutes
  - `moderate` - Between 30 minutes and 2 hours
  - `high` - More than 2 hours
- **Scoring:**
  - **Category:** Environmental Factors
  - **Weight:** 2
  - **Answers:**
    - `none` - 7 points
    - `minimal` - 6 points
    - `moderate` - 4 points
    - `high` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 6: Skin q4
- **Field ID:** `skin_q4`
- **Question:** How would you describe your daily skincare routine?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - None (I don't have a regular routine)
  - `basic` - Basic (e.g., cleanser, moisturizer)
  - `moderate` - Moderate (includes sunscreen, treatment products)
  - `comprehensive` - Comprehensive (multiple steps, targeted treatments)
  - `professional` - Professional-grade products and treatments
- **Scoring:**
  - **Category:** Skincare Routine
  - **Weight:** 2
  - **Answers:**
    - `none` - 2 points
    - `basic` - 4 points
    - `moderate` - 6 points
    - `comprehensive` - 8 points
    - `professional` - 9 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 7: Skin q5
- **Field ID:** `skin_q5`
- **Question:** Do you use sunscreen regularly?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely
  - `sometimes` - Sometimes
  - `often` - Often
  - `always` - Always (daily, year-round)
- **Scoring:**
  - **Category:** Sun Protection
  - **Weight:** 2.5
  - **Answers:**
    - `never` - 1 points
    - `rarely` - 3 points
    - `sometimes` - 5 points
    - `often` - 7 points
    - `always` - 9 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 8: Skin q6
- **Field ID:** `skin_q6`
- **Question:** Have you ever had a severe sunburn?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `once` - Once
  - `few_times` - A few times
  - `many_times` - Many times
  - `regularly` - Regularly as a child/teen
- **Scoring:**
  - **Category:** Sun Damage History
  - **Weight:** 2
  - **Answers:**
    - `never` - 9 points
    - `once` - 7 points
    - `few_times` - 5 points
    - `many_times` - 3 points
    - `regularly` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 9: Skin q7
- **Field ID:** `skin_q7`
- **Question:** Do you have a family history of skin cancer?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - None known
  - `distant` - Distant relative
  - `close` - Close relative (parent, sibling)
  - `multiple` - Multiple family members
  - `personal` - Personal history of skin cancer
- **Scoring:**
  - **Category:** Skin Cancer Risk
  - **Weight:** 2.5
  - **Answers:**
    - `none` - 8 points
    - `distant` - 6 points
    - `close` - 4 points
    - `multiple` - 2 points
    - `personal` - 1 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 10: Skin q8
- **Field ID:** `skin_q8`
- **Question:** How would you describe your skin tone and sun sensitivity?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `very_fair` - Very fair - Always burns, never tans
  - `fair` - Fair - Usually burns, tans minimally
  - `medium` - Medium - Sometimes burns, tans gradually
  - `olive` - Olive - Rarely burns, tans easily
  - `dark` - Dark - Very rarely burns, tans very easily
- **Scoring:**
  - **Category:** Skin Tone & Sensitivity
  - **Weight:** 2
  - **Answers:**
    - `very_fair` - 3 points
    - `fair` - 4 points
    - `medium` - 6 points
    - `olive` - 7 points
    - `dark` - 8 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 11: Skin q9
- **Field ID:** `skin_q9`
- **Question:** Have you noticed any new or changing moles or skin lesions?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `none` - None
  - `stable` - Stable moles, no changes
  - `some_changes` - Some changes in existing moles
  - `new_moles` - New moles appearing
  - `concerning` - Concerning changes (irregular, growing)
- **Scoring:**
  - **Category:** Mole Monitoring
  - **Weight:** 2.5
  - **Answers:**
    - `none` - 8 points
    - `stable` - 7 points
    - `some_changes` - 5 points
    - `new_moles` - 4 points
    - `concerning` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 12: Skin q10
- **Field ID:** `skin_q10`
- **Question:** Do you have any skin conditions or allergies?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `none` - None
  - `eczema` - Eczema or atopic dermatitis
  - `psoriasis` - Psoriasis
  - `rosacea` - Rosacea
  - `allergies` - Skin allergies or sensitivities
  - `acne_scars` - Acne scars
  - `vitiligo` - Vitiligo
  - `other` - Other skin conditions
- **Scoring:**
  - **Category:** Skin Conditions
  - **Weight:** 2
  - **Answers:**
    - `none` - 8 points
    - `eczema` - 4 points
    - `psoriasis` - 4 points
    - `rosacea` - 5 points
    - `allergies` - 4 points
    - `acne_scars` - 5 points
    - `vitiligo` - 6 points
    - `other` - 4 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 13: Skin q11
- **Field ID:** `skin_q11`
- **Question:** How would you rate your skin hydration and moisture levels?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - Well hydrated, supple
  - `good` - Good - Generally well hydrated
  - `moderate` - Moderate - Sometimes dry
  - `poor` - Poor - Often dry, tight feeling
  - `very_poor` - Very Poor - Constantly dry, flaky
- **Scoring:**
  - **Category:** Skin Hydration
  - **Weight:** 2
  - **Answers:**
    - `excellent` - 9 points
    - `good` - 7 points
    - `moderate` - 5 points
    - `poor` - 3 points
    - `very_poor` - 2 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 14: Skin q12
- **Field ID:** `skin_q12`
- **Question:** How often do you exfoliate your skin?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `never` - Never
  - `rarely` - Rarely
  - `weekly` - Weekly
  - `twice_week` - 2-3 times per week
  - `daily` - Daily
- **Scoring:**
  - **Category:** Exfoliation
  - **Weight:** 1.5
  - **Answers:**
    - `never` - 3 points
    - `rarely` - 4 points
    - `weekly` - 6 points
    - `twice_week` - 7 points
    - `daily` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 15: Skin q13
- **Field ID:** `skin_q13`
- **Question:** Have you had any professional skin treatments?
- **Type:** `multiselect` (Multiple selection allowed)
- **Required:** Yes
- **Answer Options:**
  - `none` - None
  - `facials` - Regular facials
  - `chemical_peels` - Chemical peels
  - `microdermabrasion` - Microdermabrasion
  - `laser_treatments` - Laser treatments
  - `injectables` - Botox or fillers
  - `other` - Other treatments
- **Scoring:**
  - **Category:** Professional Treatments
  - **Weight:** 1.5
  - **Answers:**
    - `none` - 5 points
    - `facials` - 6 points
    - `chemical_peels` - 7 points
    - `microdermabrasion` - 6 points
    - `laser_treatments` - 7 points
    - `injectables` - 6 points
    - `other` - 5 points
- **Description:** Collects relevant information for assessment evaluation.

### Question 16: Skin q14
- **Field ID:** `skin_q14`
- **Question:** How would you rate your overall skin health and appearance?
- **Type:** `radio` (Single selection)
- **Required:** Yes
- **Answer Options:**
  - `excellent` - Excellent - Healthy, glowing skin
  - `good` - Good - Generally healthy appearance
  - `moderate` - Moderate - Some concerns but manageable
  - `poor` - Poor - Multiple concerns affecting appearance
  - `very_poor` - Very Poor - Significant skin health issues
- **Scoring:**
  - **Category:** Overall Assessment
  - **Weight:** 2
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
- `ennu_skin_q1` through `ennu_skin_q11` - Individual question responses
- `ennu_skin_overall_score` - Overall assessment score
- `ennu_skin_category_scores` - Category-specific scores
- `ennu_skin_historical_scores` - Historical assessment data

### Global Data Integration
- Date of birth and gender are stored globally
- Assessment results influence personalized recommendations
- Data is used for treatment planning and progress tracking

---

## Technical Implementation

### Assessment Configuration
```php
'title'             => 'Skin Assessment',
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

1. **Introduction:** Overview of Skin Assessment purpose
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
