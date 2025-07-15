# The Qualitative Engine: Symptom-Based Scoring

**Document Version:** 1.0
**Plugin Version:** 55.0.0 (Conceptual)
**Author:** The World's Greatest Developer

---

## 1.0 Overview: The "Pillar Integrity" System

This document provides a deep dive into the architecture of the **Qualitative Engine**, the second engine in the ENNU Life four-part scoring symphony. Its purpose is to assess a user's current health *reality* based on their self-reported symptoms and to apply an intelligent, targeted penalty to their quantitative Pillar Scores.

This system is not a simple point deduction. It is a nuanced "Pillar Integrity" test, designed to identify contradictions between a user's potential (their quantitative score) and their current, real-world experience.

---

## 2.0 Core Components

### 2.1 The "Health Optimization" Assessment

This is the user-facing component, a 25-question assessment where the user selects any and all symptoms they are currently experiencing. This assessment is flagged internally as `'assessment_engine' => 'qualitative'` to ensure it is processed by this engine.

### 2.2 Symptom-to-Category Mapping

The first layer of logic maps each of the 52 possible symptoms to one or more of the eight "Health Optimization" categories. This is defined in `includes/config/health-optimization-mappings.php`.

*Example:*
*   `'Erectile Dysfunction'` maps to `['Hormones', 'Heart Health', 'Libido']`.

### 2.3 Category Severity Tiers & Pillar Impact

This is the heart of the "Pillar Integrity" logic. Each of the eight categories is assigned a clinical severity and mapped to a primary Health Pillar that it impacts.

| Symptom Category | Severity Tier | Pillar Impacted | Penalty Value |
| :--- | :--- | :--- | :--- |
| **Heart Health** | **Critical** | **Body** | **-20%** |
| **Cognitive Health**| **Critical** | **Mind** | **-20%** |
| **Hormones** | Moderate | **Body** | -10% |
| **Weight Loss** | Moderate | **Lifestyle** | -10% |
| **Strength** | Moderate | **Body** | -10% |
| **Longevity** | Moderate | **Lifestyle** | -10% |
| **Energy** | Minor | **Lifestyle** | -5% |
| **Libido** | Minor | **Mind** | -5% |

---

## 3.0 The Calculation Flow

The process is executed by the `calculate_pillar_integrity_penalties()` method within the `ENNU_Assessment_Scoring` class.

1.  **Receive Symptoms**: The method receives the list of symptoms selected by the user.

2.  **Identify Triggered Categories**: It iterates through the `symptom_to_category` map to compile a unique list of all Health Optimization categories that have been triggered by the user's symptoms.

3.  **Determine Highest Severity per Pillar**: The system then looks at the **Category Severity & Pillar Impact Map**. For each of the four core Health Pillars (`Mind`, `Body`, `Lifestyle`, `Aesthetics`), it determines the *highest* severity level of any category that impacts it.
    *   **Example:** A user's symptoms trigger both `Strength` (Moderate, impacts Body) and `Heart Health` (Critical, impacts Body). The system sees that for the `Body` pillar, the highest triggered severity is **Critical**.

4.  **Apply a Single Penalty per Pillar**: The final, crucial step is that only **one penalty** is applied per pillar, corresponding to the highest severity level found. This prevents unfair "penalty stacking."
    *   Continuing the example: Even though two categories impacting the `Body` pillar were triggered, only the single -20% penalty from the "Critical" `Heart Health` category is applied to the user's Body score. Their Lifestyle and Mind scores remain untouched (unless they were impacted by other symptoms).

5.  **Return Penalty Array**: The method returns a simple array of multipliers that will be applied to the user's baseline Pillar Scores.
    *   *Example Output:* `['Mind' => 1.0, 'Body' => 0.80, 'Lifestyle' => 1.0, 'Aesthetics' => 1.0]`

This architecture ensures that a user's subjective feelings provide a powerful, intelligent, and fair adjustment to their overall health score, creating a more honest and accurate reflection of their true health reality. 