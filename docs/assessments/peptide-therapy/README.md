# Peptide Therapy Assessment Documentation

**Version:** 1.0.0  
**Plugin Version:** 76.0.0  
**Status:** Production Ready  
**Shortcode:** `[ennu-peptide-therapy]`

## Overview

The Peptide Therapy Assessment is a comprehensive evaluation tool designed to provide personalized peptide therapy recommendations based on individual health goals, symptoms, and current health status. This assessment integrates seamlessly with the ENNU Life unified assessment system.

## Assessment Structure

### Basic Information
- **Total Questions:** 15
- **Estimated Completion Time:** 5-7 minutes
- **Scoring Categories:** 5
- **Four-Pillar Distribution:**
  - Mind: 20%
  - Body: 45%
  - Lifestyle: 25%
  - Aesthetics: 10%

### Scoring Categories

1. **Weight Management & Metabolism** (45% of Body pillar)
   - Questions focus on weight goals, metabolism, and stubborn fat areas
   - Influences recommendations for GLP-1 agonists and metabolic peptides

2. **Recovery & Performance** (25% of Lifestyle pillar)
   - Evaluates exercise frequency, recovery time, and physical pain
   - Guides recommendations for BPC-157, TB-500, and recovery peptides

3. **Hormonal Optimization** (20% of Mind pillar)
   - Assesses hormonal imbalance symptoms and life stages
   - Determines need for hormone-optimizing peptides

4. **Cognitive Enhancement** (10% of Mind pillar)
   - Measures mental clarity, focus, and memory
   - Influences recommendations for nootropic peptides

5. **Anti-Aging & Longevity** (10% of Aesthetics pillar)
   - Evaluates anti-aging priorities
   - Guides recommendations for longevity peptides

## Question Details

### Q1: Primary Health Optimization Goals
- **Type:** Checkbox (Multiple Selection)
- **Purpose:** Identify user's main health objectives
- **Options:** Weight loss, muscle gain, energy, cognitive enhancement, anti-aging, hormone optimization, sexual health, recovery, sleep, immune support

### Q2: Current Energy Levels
- **Type:** Radio (1-10 scale)
- **Scoring:** Very Low (2) to Excellent (10)
- **Category:** Recovery & Performance

### Q3: Weight Management Situation
- **Type:** Radio
- **Scoring:** Need significant loss (3) to Happy with current (9)
- **Category:** Weight Management & Metabolism

### Q4: Metabolism Description
- **Type:** Radio
- **Scoring:** Very slow (2) to Very fast (10)
- **Category:** Weight Management & Metabolism

### Q5: Stubborn Fat Areas
- **Type:** Radio
- **Scoring:** Multiple areas (2) to None (10)
- **Category:** Weight Management & Metabolism

### Q6: Exercise Frequency
- **Type:** Radio
- **Scoring:** Rarely (2) to Daily (10)
- **Category:** Recovery & Performance

### Q7: Recovery After Physical Activity
- **Type:** Radio
- **Scoring:** Very poor (2) to Excellent (10)
- **Category:** Recovery & Performance

### Q8: Muscle or Joint Pain
- **Type:** Radio
- **Scoring:** Chronic severe (2) to Never (10)
- **Category:** Recovery & Performance

### Q9: Hormonal Imbalance Symptoms
- **Type:** Checkbox (Multiple Selection)
- **Category:** Hormonal Optimization
- **Options:** Fatigue, mood swings, low libido, weight gain, hair loss, sleep issues, hot flashes, muscle loss, brain fog

### Q10: Low Testosterone Symptoms (Men)
- **Type:** Radio
- **Scoring:** Multiple symptoms (2) to No symptoms (10)
- **Category:** Hormonal Optimization

### Q11: Current Life Stage (Women)
- **Type:** Radio
- **Scoring:** Varies by life stage
- **Category:** Hormonal Optimization

### Q12: Mental Clarity and Focus
- **Type:** Radio
- **Scoring:** Very poor (2) to Excellent (10)
- **Category:** Cognitive Enhancement

### Q13: Brain Fog Frequency
- **Type:** Radio
- **Scoring:** Daily (2) to Never (10)
- **Category:** Cognitive Enhancement

### Q14: Memory and Recall
- **Type:** Radio
- **Scoring:** Declining (2) to Excellent (10)
- **Category:** Cognitive Enhancement

### Q15: Anti-Aging Importance
- **Type:** Radio
- **Scoring:** Not important (2) to Extremely important (10)
- **Category:** Anti-Aging & Longevity

## Peptide Recommendations

### Weight Management Peptides
- **Semaglutide/Tirzepatide:** For significant weight loss needs (30+ lbs)
- **AOD-9604:** For stubborn fat areas resistant to diet/exercise
- **CJC-1295/Ipamorelin:** For metabolism boost and growth hormone support
- **MOTS-c:** For metabolic optimization and energy

### Recovery & Performance Peptides
- **BPC-157:** For injury recovery, joint pain, and gut health
- **TB-500:** For muscle recovery and tissue repair
- **IGF-1 LR3:** For muscle growth and recovery
- **Thymosin Alpha-1:** For immune support and recovery

### Hormonal Optimization Peptides
- **Kisspeptin:** For hormonal balance and reproductive health
- **PT-141:** For sexual health and libido enhancement
- **DSIP:** For sleep quality and hormonal regulation
- **Gonadorelin:** For testosterone optimization

### Cognitive Enhancement Peptides
- **Semax:** For mental clarity and cognitive performance
- **Selank:** For anxiety reduction and focus improvement
- **Cerebrolysin:** For neuroprotection and brain health
- **P21:** For neurogenesis and memory enhancement

### Anti-Aging & Longevity Peptides
- **Epithalon:** For telomerase activation and longevity
- **GHK-Cu:** For skin health, hair growth, and anti-aging
- **NAD+:** For cellular regeneration and energy production
- **Thymosin Beta-4:** For tissue regeneration and healing

## Scoring Algorithm

### Four-Engine Scoring System

1. **Quantitative Engine**
   - Base scores calculated from question responses
   - Weighted averages based on category importance
   - Score range: 0-10 for each category

2. **Qualitative Engine**
   - Symptom-based penalties applied
   - Reduces scores based on symptom severity and frequency
   - Maximum penalty: -30% per category

3. **Objective Engine**
   - Biomarker adjustments when lab data available
   - Boosts or penalties based on optimal/suboptimal/poor ranges
   - Maximum adjustment: ±20% per category

4. **Intentionality Engine**
   - Goal alignment boosts
   - Non-cumulative 5% boosts for aligned goals
   - Maximum boost: 5% per pillar

### Final Score Calculation
```
Final Score = (Base Score × Qualitative Multiplier × Objective Multiplier) + Intentionality Boost
```

## Implementation Details

### Technical Integration
- **File Location:** `/includes/class-peptide-therapy-shortcode.php`
- **Template:** `/templates/assessment-peptide-therapy.php`
- **Config:** `/includes/config/assessments/peptide_therapy.php`
- **Scoring Weights:** `/config/scoring-weights.json`

### Data Storage
- **User Meta Keys:**
  - `ennu_assessment_responses_peptide_therapy` - Raw responses
  - `ennu_peptide_therapy_scores` - Calculated scores
  - `ennu_peptide_therapy_recommendations` - Personalized recommendations

### AJAX Actions
- `ennu_submit_assessment` - Form submission
- `ennu_save_assessment_progress` - Auto-save functionality

### Hooks and Filters
- `ennu_assessment_completed` - Fired after submission
- `ennu_peptide_therapy_recommendations` - Filter for customizing recommendations
- `ennu_peptide_therapy_scores` - Filter for adjusting scores

## User Experience Flow

1. **Assessment Start**
   - User navigates to page with `[ennu-peptide-therapy]` shortcode
   - Assessment loads with progress indicator

2. **Question Navigation**
   - Previous/Next buttons for navigation
   - Auto-save after each question
   - Progress bar shows completion status

3. **Submission**
   - Validation ensures all required questions answered
   - AJAX submission with loading state
   - Score calculation in background

4. **Results Display**
   - Four-pillar scores visualization
   - Personalized peptide recommendations
   - Option to book consultation
   - Results saved to user profile

## Admin Features

### User Profile Integration
- View assessment responses in user edit screen
- See calculated scores and recommendations
- Track completion date and time
- Export data capability

### Reporting
- Assessment completion tracking
- Average scores by category
- Popular peptide recommendations
- Conversion to consultation metrics

### Configuration
- Customize scoring weights via admin interface
- Adjust recommendation thresholds
- Configure results page mapping
- Enable/disable specific peptides

## Testing & Quality Assurance

### Unit Tests
- Score calculation accuracy
- Recommendation algorithm logic
- Data persistence verification
- AJAX handler responses

### Integration Tests
- Full assessment flow
- User registration during assessment
- Email notifications
- HubSpot synchronization

### Edge Cases Handled
- Incomplete submissions
- Browser refresh during assessment
- Multiple simultaneous sessions
- Invalid data inputs

## Support & Troubleshooting

### Common Issues

1. **Assessment Not Displaying**
   - Verify shortcode spelling
   - Check plugin activation
   - Review JavaScript console for errors

2. **Submission Failures**
   - Check AJAX endpoint accessibility
   - Verify nonce generation
   - Review server error logs

3. **Scores Not Calculating**
   - Ensure scoring weights configured
   - Check for missing question responses
   - Verify calculation service running

### Debug Mode
Enable debug logging in WordPress:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

### Support Contact
- Technical Team: ENNU Life Development
- Documentation: This file and PEPTIDE-THERAPY-ASSESSMENT-COMPLETE.md
- Version: 76.0.0