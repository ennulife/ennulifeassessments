# The Intentionality Engine: Goal-Based Scoring

**Document Version:** 1.0
**Plugin Version:** 62.2.8
**Author:** Luis Escobar

---

## 1.0 Overview: The "Alignment Boost" System

This document provides a deep dive into the architecture of the **Intentionality Engine**, the fourth and final engine in the ENNU Life scoring symphony. Its purpose is to provide the final, crucial layer of personalization by rewarding a user's focused intent.

This system answers the question: **"How is the user's health aligned with their own stated goals?"** It acts as a positive modulator, applying a small but meaningful "Alignment Boost" to the Pillar Scores that a user has explicitly chosen to improve. This transforms the final ENNU LIFE SCORE from a passive measurement into an active, motivational tool.

---

## 2.0 Core Components

### 2.1 Health Goal Data Input

The engine's input is the array of health goals selected by the user, which is collected in both the Welcome Assessment and the General Health Assessment and stored in the `ennu_global_health_goals` user meta field.

### 2.2 The Goal-to-Pillar Map

The heart of this engine is a new mapping dictionary stored in `includes/config/health-optimization-mappings.php`. This map creates a direct link between each selectable health goal and the primary Health Pillar it affects.

*   **Goal-to-Pillar Map (Example):**
    | Health Goal | Pillar Boosted |
    | :--- | :--- |
    | `lose_weight` | Lifestyle |
    | `sharpen_focus` | Mind |
    | `build_muscle` | Body |
    | `improve_sleep` | Lifestyle |
    | `balance_hormones`| Body |
    | `boost_libido` | Mind |
    | *...and so on for all goals.* | |

---

## 3.0 The Calculation Flow

The process is executed by the `calculate_goal_alignment_boost()` method within the `ENNU_Assessment_Scoring` class. It is the **final step** in the calculation, running *after* all penalties and adjustments have been applied to the Pillar Scores.

1.  **Receive Adjusted Pillar Scores**: The method receives the Pillar Scores after they have already been modified by the Qualitative (Symptom) and Objective (Biomarker) engines.

2.  **Fetch User's Goals**: It retrieves the user's saved `health_goals` array from their user meta.

3.  **Apply a Non-Cumulative Boost**: The engine iterates through the user's goals. For each goal, it looks up the corresponding Pillar in the Goal-to-Pillar map.
    *   It then applies a single, fixed **+5% Alignment Boost** to that Pillar's score.
    *   **Crucially, this boost is not cumulative per pillar.** If a user selects multiple goals that map to the same Pillar (e.g., "Build muscle" and "Balance hormones" both mapping to `Body`), the 5% boost is only applied **once**. This ensures the boost remains a subtle, motivational nudge rather than an overpowering factor in the score.

4.  **Return Final Pillar Scores**: The method returns the final, boosted Pillar Scores, which are then used to calculate the ultimate ENNU LIFE SCORE.
    *   *Example:* If a user's `Body` score was 6.1 after all penalties, and they had a goal of "Build muscle," their final score passed to the ENNU LIFE SCORE calculation would be `6.4` (6.1 * 1.05).

This architecture provides a powerful motivational feedback loop. It tells the user that the system recognizes their goals and that their focused intention is a valid and important component of their overall health picture. It is the perfect, humanizing touch to a data-driven system. 