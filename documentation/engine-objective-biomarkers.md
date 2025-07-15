# The Objective Engine: Biomarker-Based Scoring

**Document Version:** 1.0
**Plugin Version:** 55.0.0 (Conceptual)
**Author:** The World's Greatest Developer

---

## 1.0 Overview: The "Actuality Adjustment" System

This document provides a deep dive into the architecture of the **Objective Engine**, the third engine in the ENNU Life four-part scoring symphony. Its purpose is to take the user's hard, scientific biomarker data and use it to apply the final, most authoritative adjustments to their Pillar Scores.

This system answers the question: **"What is *really* happening inside my body?"** It serves as the ultimate ground truth, capable of overriding or validating the conclusions drawn from the other, more subjective engines.

---

## 2.0 Core Components

### 2.1 Biomarker Data Input

The engine is designed to receive a structured data set of over 100 biomarkers from the user's ENNU LIFE MEMBERSHIP lab test. This data will be stored against the user's profile in the database.

### 2.2 The Master Biomarker Profile Map

The heart of this engine is a new, comprehensive mapping dictionary stored in `includes/config/health-optimization-mappings.php`. Every single biomarker will have a "profile" that defines its scoring logic.

*   **The Biomarker Profile (Example Data Structure):**
    ```json
    "LDL_Cholesterol": {
      "name": "LDL Cholesterol",
      "units": "mg/dL",
      "optimal_range": [0, 99],
      "suboptimal_range": [100, 159],
      "poor_range": [160, 1000],
      "pillar_impact": {
        "Body": 0.8,
        "Lifestyle": 0.2
      },
      "impact_weight": "critical"
    }
    ```
    *   **Ranges:** Define the precise boundaries for `optimal`, `sub-optimal`, and `poor` results.
    *   **Pillar Impact:** Defines which Pillar(s) this biomarker affects and by what proportion. This allows a single biomarker like cortisol to influence multiple pillars.
    *   **Impact Weight:** A qualitative descriptor (`critical`, `significant`, `moderate`) that determines the magnitude of the score adjustment.

---

## 3.0 The Calculation Flow

The process is executed by the `calculate_biomarker_actuality_adjustments()` method within the `ENNU_Assessment_Scoring` class. It runs *after* the Symptom Penalties have been applied.

1.  **Receive Biomarker Data**: The method receives the user's complete set of lab results.

2.  **Iterate and Analyze**: The engine iterates through each biomarker result and compares it to its profile in the master map.

3.  **Calculate Adjustments**:
    *   **Negative Adjustments (Penalties):** If a biomarker falls into the `sub-optimal` or `poor` range, a negative adjustment is calculated. The magnitude of this penalty is determined by a matrix of its `impact_weight` and its range. For example:
        *   A `critical` biomarker in the `poor` range might apply a -15% adjustment.
        *   A `moderate` biomarker in the `sub-optimal` range might apply a -5% adjustment.
    *   **Positive Adjustments (Validation Bonuses):** If a biomarker falls within the `optimal` range, it can apply a small positive adjustment (e.g., +2.5% or +5%).
    *   **Cumulative Application**: Unlike symptom penalties, biomarker adjustments **are cumulative**. If a user has three "poor" biomarkers that all impact the `Body` pillar, all three negative adjustments will be applied to that pillar's score. This reflects the compounding nature of physiological reality.

4.  **Return Adjustment Array**: The method returns an array of final multipliers for each pillar, which includes the sum of all penalties and bonuses.
    *   *Example Output:* `['Mind' => 1.05, 'Body' => 0.75, 'Lifestyle' => 0.9, 'Aesthetics' => 1.0]`

This architecture ensures that the final ENNU LIFE SCORE is not just a reflection of lifestyle or feelings, but is deeply grounded in objective, scientific, and undeniable biological truth. 