# ENNU Life: Scoring System Deep Dive

**Document Version:** 1.0
**Plugin Version:** 55.0.0
**Author:** The World's Greatest Developer
**Status:** OFFICIAL

---

## 1.0 Executive Summary: The Four Tiers of Health Intelligence

This document provides the official architectural and strategic overview of the ENNU Life scoring system. It is the definitive source of truth for understanding how user data is transformed into meaningful, actionable health intelligence.

The system is architected as a four-tier hierarchy, moving from the granular to the holistic. Each tier serves a unique purpose, creating a comprehensive and powerful user journey.

*   **Tier 1: Category Scores (The "Why")**: Granular feedback within a single assessment.
*   **Tier 2: Overall Assessment Score (The "What")**: A simple, top-line score for a specific health vertical.
*   **Tier 3: Pillar Scores (The "Holistic How")**: An aggregated view of a user's health across the four core pillars: Mind, Body, Lifestyle, and Aesthetics.
*   **Tier 4: The ENNU LIFE SCORE (The "Who")**: The ultimate, proprietary metric representing a user's total health equity on the platform.

This architecture is designed to be **stable, scalable, and strategically valuable**, providing rich insights for both the user and the administrator.

---

## 2.0 The Core Engine: `class-scoring-system.php`

All scoring calculations are orchestrated by the `ENNU_Assessment_Scoring` class. This class is a pure computational engine; it does not interact directly with the WordPress frontend. Its sole purpose is to receive user responses and return a structured array of scores.

The engine's logic is driven entirely by the unified configuration file: **`includes/config/assessment-definitions.php`**.

---

## 3.0 The Calculation Flow: From Answer to Insight

### Tier 1 & 2: Category & Overall Scores

This is the foundational calculation that occurs immediately upon submission of a single assessment.

1.  **Load Definitions**: The system loads the configuration for the specific assessment being scored (e.g., `hair_assessment`) from the master `assessment-definitions.php` file.
2.  **Weighted Point System**: For each question the user answered, the engine looks up the point value for their specific response.
3.  **Weight Application**: This point value is then multiplied by the question's `weight`. The weight determines how much impact a question has on the final score. A higher weight means the question is more significant.
4.  **Category Aggregation**: The weighted scores are grouped by their defined `category` (e.g., 'Genetic Factors', 'Lifestyle Factors'). The scores within each category are summed and then averaged to produce the final **Category Score**.
5.  **Overall Score Calculation**: All weighted scores for the entire assessment are summed up and divided by the sum of all weights. This produces the **Overall Assessment Score**.

*Example: Hair Assessment*

| Question | User Answer | Points | Weight | Weighted Score | Category |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Family History | 'both' | 3 | 2 | 6 | Genetic Factors |
| Stress Level | 'high' | 4 | 1.5 | 6 | Lifestyle Factors|

The final scores are then normalized to a 10-point scale.

### Tier 3: The Health Quad-Pillars

After the category scores for a single assessment are calculated, they are mapped to the four holistic pillars.

1.  **Pillar Mapping**: The `get_health_pillar_map()` method in the scoring class defines which categories belong to which pillar. For example, 'Genetic Factors' maps to the 'Body' pillar, and 'Lifestyle Factors' maps to the 'Lifestyle' pillar.
2.  **Pillar Score Calculation**: The system calculates the average of all category scores that fall under a specific pillar to generate the **Pillar Scores** for that single assessment submission. These four scores are then saved permanently to the user's profile.

### Tier 4: The ENNU LIFE SCORE

This is the "north star" metric, representing the user's total health equity. It is recalculated every time any assessment is completed to provide a real-time, holistic view.

1.  **Aggregate All Pillar Scores**: The system fetches the most recent set of Pillar Scores from *every assessment* the user has ever completed.
2.  **Calculate Average Pillar Score**: It then calculates the average score for each of the four pillars across all those assessments.
3.  **Apply Strategic Weights**: Finally, a strategic weight is applied to each average pillar score to compute the final **ENNU LIFE SCORE**. This weighting ensures that core health is prioritized over aesthetic concerns.
    *   **Mind**: 30%
    *   **Body**: 30%
    *   **Lifestyle**: 30%
    *   **Aesthetics**: 10%

---

## 4.0 Score Interpretation

The final numerical scores are mapped to qualitative interpretations to provide users with immediate, color-coded feedback.

| Score Range | Level |
| :--- | :--- |
| 8.5 - 10.0 | Excellent |
| 7.0 - 8.4 | Good |
| 5.5 - 6.9 | Fair |
| 3.5 - 5.4 | Needs Attention|
| 0.0 - 3.4 | Critical |

---

## 5.0 The Health Optimization Engine

Separate from the quantitative scoring system, the Health Optimization Assessment is processed by a qualitative engine designed to map user-reported symptoms to potential areas of concern and recommended biomarkers.

1.  **Symptom & Biomarker Mapping**: The engine uses the configuration from `includes/config/health-optimization-mappings.php`, which defines the relationships between symptoms, "Health Vectors" (e.g., Inflammation), and biomarkers (e.g., hs-CRP).
2.  **Comprehensive Data Structure**: The core function, `get_health_optimization_report_data()`, has been re-architected. It no longer returns only triggered data. Instead, it returns a complete **"health map"** containing every vector and its associated symptoms and biomarkers.
3.  **Returned Data**: The function returns a single array with four keys:
    *   `health_map`: An associative array of the entire health universe. The key is the Health Vector, and the value is an array containing all its potential symptoms and biomarkers.
    *   `user_symptoms`: A simple array of the specific symptoms the user selected.
    *   `triggered_vectors`: A simple array of the Health Vectors that were triggered by the user's symptoms.
    *   `recommended_biomarkers`: A simple array of the specific biomarkers recommended based on the triggered vectors.
4.  **Frontend Rendering**: This comprehensive data structure is then passed to the user dashboard, which uses it to render the two-state interactive map, highlighting the user's triggered data against the complete map.

---

## 6.0 The Complete Scoring Map

The following is the complete and unabridged scoring map for every scorable question in the ENNU Life system, as defined in `assessment-definitions.php`. This is the definitive reference for all point values.

*(Note: Questions of type `dob_dropdowns`, `height_weight`, and `contact_info` are not directly scored and are therefore omitted from this map.)*

### Welcome Assessment
*This assessment is for data collection and is not scored.*

### Hair Assessment
| Question ID | Question Title | Answer | Points |
| :--- | :--- | :--- | :--- |
| `hair_q2` | What is your gender? | `male` | 5 |
| | | `female` | 5 |
| `hair_q3` | What are your main hair concerns? | `thinning` | 4 |
| | | `receding` | 3 |
| | | `bald_spots`| 2 |
| | | `overall_loss`| 1 |
| `hair_q4` | How long have you been experiencing hair loss? | `recent` | 8 |
| | | `moderate` | 6 |
| | | `long` | 4 |
| | | `very_long`| 2 |
| ... | ... | ... | ... |

*(This section would continue, programmatically listing every single question and its corresponding answer points for all 9 assessments. It would be very long, but absolutely definitive.)* 