# Weight Loss Assessment: Scoring Deep Dive

**Version**: 24.12.4
**Date**: July 12, 2025

---

## 1. Scoring Philosophy & Rationale

This document provides a complete breakdown of the scoring logic for the **Weight Loss Assessment**. The system's philosophy is to assess a user's readiness and potential for successful, sustainable weight management. A higher score indicates a stronger foundation for success.

The scoring is weighted based on the key pillars of modern weight management programs, balancing behavioral, psychological, and lifestyle factors:

*   **High Weight**: The most critical factors are `Nutrition` and `Physical Activity`, as these are the primary drivers of energy balance and metabolic health.
*   **Medium Weight**: `Psychological Factors` (stress), `Lifestyle Factors` (sleep), and `Behavioral Patterns` are given significant weight, as these are often the root causes of weight gain and the biggest barriers to success.
*   **Low Weight**: `Motivation`, `Social Support`, and `Weight Loss History` are included to provide a complete profile but are weighted less heavily than the core behavioral and lifestyle indicators.

The goal is to create a score that goes beyond simple calorie counting to assess the user's entire ecosystem, providing a more accurate picture of their readiness for change.

## 2. Scoring Table

This table details every scorable question in the assessment, its category, weight, and the point value for each possible answer.

| Question ID | Scoring Key | Category | Weight | Answer | Answer ID | Points |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| weight_loss_q2 | gender | Demographics | 0.5 | Male | `male` | 5 |
| | | | | Female | `female` | 5 |
| weight_loss_q3 | primary_goal | Motivation & Goals | 2 | Lose 10-20 lbs | `lose_10` | 8 |
| | | | | Lose 20-50 lbs | `lose_30` | 7 |
| | | | | Lose 50+ lbs | `lose_50` | 6 |
| | | | | Maintain current weight | `maintain` | 9 |
| weight_loss_q5 | exercise_frequency| Physical Activity | 2.5 | Never | `never` | 1 |
| | | | | 1-2 times/week | `rarely` | 3 |
| | | | | 3-4 times/week | `often` | 8 |
| | | | | 5+ times/week | `daily` | 9 |
| weight_loss_q6 | diet_quality | Nutrition | 3 | Mostly Unhealthy | `unhealthy` | 2 |
| | | | | Generally Balanced | `balanced` | 6 |
| | | | | Very Healthy | `healthy` | 8 |
| | | | | Strict Diet | `strict` | 7 |
| weight_loss_q7 | sleep_quality | Lifestyle Factors | 1.5 | Less than 5 hours | `less_5` | 3 |
| | | | | 5-6 hours | `5_6` | 5 |
| | | | | 7-8 hours | `7_8` | 9 |
| | | | | More than 8 hours | `more_8` | 8 |
| weight_loss_q8 | stress_level | Psychological Factors | 1.5 | Low | `low` | 9 |
| | | | | Moderate | `moderate` | 7 |
| | | | | High | `high` | 4 |
| | | | | Very High | `very_high` | 2 |
| weight_loss_q9 | previous_attempts | Weight Loss History | 1 | Never had lasting success | `no_success` | 3 |
| | | | | Some success, but gained it back | `some_success` | 4 |
| | | | | Good success, maintained for a while | `good_success` | 6 |
| | | | | This is my first serious attempt | `first_time` | 7 |
| weight_loss_q12 | support_system | Social Support | 1 | I'm on my own | `none` | 3 |
| | | | | Partner/Spouse | `partner` | 7 |
| | | | | Family and Friends | `family` | 8 |
| | | | | Professional | `professional` | 9 |

---
*Note: The Date of Birth (weight_loss_q1) and other non-scorable questions do not contribute to the final score.* 