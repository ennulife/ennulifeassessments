# Skin Health Assessment: Scoring Deep Dive

**Version**: 24.12.4
**Date**: July 12, 2025

---

## 1. Scoring Philosophy & Rationale

This document provides a complete breakdown of the scoring logic for the **Skin Health Assessment**. The system's philosophy is to quantify the primary indicators of skin health and damage, resulting in a score where higher numbers indicate healthier, more resilient skin.

The scoring is weighted based on well-established dermatological principles:

*   **High Weight**: The most critical factors are the user's `Primary Skin Issue` and the impact of `Environmental Factors` (primarily sun exposure), as these are the most significant contributors to skin aging and health complications.
*   **Medium Weight**: A user's `Skin Type` and `Lifestyle & Diet` are given moderate weight, as they represent the underlying condition of the skin and the modifiable factors that influence it.
*   **Low Weight**: The user's `Current Regimen` is weighted less, as it is an indicator of current effort but less critical than the underlying clinical factors.

The goal of the score is to provide a holistic view of a user's skin health, balancing genetic predispositions with environmental and behavioral factors to guide effective treatment recommendations.

## 2. Scoring Table

This table details every scorable question in the assessment, its category, weight, and the point value for each possible answer.

| Question ID | Scoring Key | Category | Weight | Answer | Answer ID | Points |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| skin_q2 | gender | Demographics | 0.5 | Male | `male` | 5 |
| | | | | Female | `female` | 5 |
| skin_q3 | skin_type | Skin Characteristics| 2 | Normal | `normal` | 8 |
| | | | | Dry | `dry` | 6 |
| | | | | Oily | `oily` | 6 |
| | | | | Combination | `combination` | 7 |
| | | | | Sensitive | `sensitive` | 5 |
| skin_q4 | primary_concern | Primary Skin Issue | 3 | Acne & Blemishes | `acne` | 3 |
| | | | | Fine Lines & Wrinkles | `aging` | 4 |
| | | | | Dark Spots & Hyperpigmentation | `pigmentation` | 5 |
| | | | | Redness & Rosacea | `redness` | 4 |
| | | | | Dryness & Dehydration | `dullness` | 6 |
| skin_q5 | sun_exposure | Environmental Factors | 2.5 | Rarely, I'm mostly indoors | `minimal` | 9 |
| | | | | Sometimes, on weekends | `moderate` | 6 |
| | | | | Daily, but I use sunscreen | `high` | 3 |
| skin_q6 | skincare_routine | Current Regimen | 1 | Minimal (cleanse, maybe moisturize) | `none` | 4 |
| | | | | Basic (cleanse, moisturize, SPF) | `basic` | 6 |
| | | | | Advanced (serums, exfoliants, etc.) | `advanced` | 8 |
| skin_q8 | lifestyle_factors | Lifestyle & Diet | 2 | I smoke | `smoker` | 3 |
| | | | | I have high stress levels | `high_stress` | 4 |
| | | | | I have poor sleep quality | `poor_sleep` | 4 |
| | | | | My diet is high in sugar/processed foods | `high_sugar_diet` | 3 |

---
*Note: The Date of Birth (skin_q1) and other non-scorable questions do not contribute to the final score.* 