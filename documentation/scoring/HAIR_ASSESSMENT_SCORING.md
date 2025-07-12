# Hair Health Assessment: Scoring Deep Dive

**Version**: 24.12.4
**Date**: July 12, 2025

---

## 1. Scoring Philosophy & Rationale

This document provides a complete breakdown of the scoring logic for the **Hair Health Assessment**. The system's philosophy is to quantify the key factors that contribute to hair loss and thinning, providing a clear, actionable score where higher numbers indicate better hair health and a more favorable prognosis.

The scoring is weighted to reflect established clinical priorities in dermatology and trichology:

*   **High Weight**: The most critical factors are the user's current `Hair Health Status` (the severity and speed of loss) and `Genetic Factors`, as these are the strongest predictors of future hair loss.
*   **Medium Weight**: `Lifestyle Factors` and `Nutritional Support` are given significant weight, as these are modifiable factors that have a major impact on hair health and treatment efficacy.
*   **Low Weight**: `Treatment History` and `Treatment Expectations` are included to provide a complete picture but are weighted less heavily, as they are secondary to the primary clinical indicators.

The goal is to produce a score that is not just a number, but a meaningful reflection of the user's current situation and their potential for successful intervention.

## 2. Scoring Table

This table details every scorable question in the assessment, its category, weight, and the point value for each possible answer.

| Question ID | Scoring Key | Category | Weight | Answer | Answer ID | Points |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| hair_q2 | gender | Demographics | 0.5 | Male | `male` | 5 |
| | | | | Female | `female` | 5 |
| hair_q3 | hair_concerns | Hair Health Status | 3 | Thinning Hair | `thinning` | 4 |
| | | | | Receding Hairline | `receding` | 3 |
| | | | | Bald Spots | `bald_spots` | 2 |
| | | | | Overall Hair Loss | `overall_loss` | 1 |
| hair_q4 | duration | Progression Timeline | 2 | Less than 6 months | `recent` | 8 |
| | | | | 6 months - 2 years | `moderate` | 6 |
| | | | | 2-5 years | `long` | 4 |
| | | | | More than 5 years | `very_long` | 2 |
| hair_q5 | speed | Progression Rate | 2.5 | Very Slow | `slow` | 8 |
| | | | | Moderate | `moderate` | 6 |
| | | | | Fast | `fast` | 3 |
| | | | | Very Fast | `very_fast` | 1 |
| hair_q6 | family_history | Genetic Factors | 2 | No Family History | `none` | 9 |
| | | | | Mother's Side | `mother` | 6 |
| | | | | Father's Side | `father` | 5 |
| | | | | Both Sides | `both` | 3 |
| hair_q7 | stress_level | Lifestyle Factors | 1.5 | Low Stress | `low` | 9 |
| | | | | Moderate Stress | `moderate` | 7 |
| | | | | High Stress | `high` | 4 |
| | | | | Very High Stress | `very_high` | 2 |
| hair_q8 | diet_quality | Nutritional Support | 1.5 | Excellent | `excellent` | 9 |
| | | | | Good | `good` | 7 |
| | | | | Fair | `fair` | 5 |
| | | | | Poor | `poor` | 2 |
| hair_q9 | previous_treatments | Treatment History | 1 | No Treatments | `none` | 7 |
| | | | | Over-the-Counter | `otc` | 6 |
| | | | | Prescription Meds | `prescription` | 5 |
| | | | | Medical Procedures | `procedures` | 4 |
| hair_q10 | goals | Treatment Expectations | 1 | Stop Hair Loss | `stop_loss` | 8 |
| | | | | Regrow Hair | `regrow` | 6 |
| | | | | Thicken Hair | `thicken` | 7 |
| | | | | Overall Improvement | `improve` | 8 |

---
*Note: The Date of Birth (hair_q1) and Contact Information (hair_q11) questions are not scorable and do not contribute to the final score.* 