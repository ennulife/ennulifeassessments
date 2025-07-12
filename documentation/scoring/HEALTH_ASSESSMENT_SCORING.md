# General Health Assessment: Scoring Deep Dive

**Version**: 24.12.4
**Date**: July 12, 2025

---

## 1. Scoring Philosophy & Rationale

This document provides a complete breakdown of the scoring logic for the **General Health Assessment**. The system's philosophy is to create a holistic score that reflects a user's overall state of wellness, with an emphasis on modifiable lifestyle factors. A higher score indicates a stronger foundation of health.

The scoring is weighted based on the foundational pillars of preventative and lifestyle medicine:

*   **High Weight**: The most critical factors are the user's self-reported `Current Health Status` and their core lifestyle habits, including `Physical Activity` and `Nutrition`. These are the strongest indicators of current and future health outcomes.
*   **Medium Weight**: `Vitality & Energy`, `Sleep & Recovery`, and `Stress & Mental Health` are given significant weight, as these factors are deeply intertwined with physical health and are often the first indicators of underlying issues.
*   **Low Weight**: `Preventive Care` habits and stated `Health Motivation` are included to provide a complete picture but are weighted less heavily than the primary clinical and lifestyle indicators.

The goal is to provide a comprehensive wellness score that empowers users by showing them which modifiable behaviors have the greatest impact on their health.

## 2. Scoring Table

This table details every scorable question in the assessment, its category, weight, and the point value for each possible answer.

| Question ID | Scoring Key | Category | Weight | Answer | Answer ID | Points |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| health_q2 | gender | Demographics | 0.5 | Male | `male` | 5 |
| | | | | Female | `female` | 5 |
| health_q3 | overall_health | Current Health Status | 3 | Poor | `poor` | 2 |
| | | | | Fair | `fair` | 5 |
| | | | | Good | `good` | 7 |
| | | | | Excellent | `excellent` | 9 |
| health_q4 | energy_levels | Vitality & Energy | 2 | Consistently Low | `low` | 2 |
| | | | | I crash in the afternoon | `crash` | 4 |
| | | | | Generally Okay | `moderate` | 7 |
| | | | | High and Stable | `high` | 9 |
| health_q5 | exercise_frequency| Physical Activity | 2.5 | Rarely or Never | `rarely` | 1 |
| | | | | 1-2 times a week | `sometimes` | 5 |
| | | | | 3-5 times a week | `often` | 8 |
| | | | | Almost every day | `daily` | 9 |
| health_q6 | diet_quality | Nutrition | 2.5 | High in processed foods | `processed` | 2 |
| | | | | A typical Western diet | `average` | 5 |
| | | | | Mostly whole foods | `healthy` | 7 |
| | | | | Very clean, whole foods diet | `very_healthy` | 9 |
| health_q7 | sleep_quality | Sleep & Recovery | 2 | Poor, I wake up tired| `poor` | 3 |
| | | | | Fair, could be better | `fair` | 5 |
| | | | | Good, usually restful| `good` | 7 |
| | | | | Excellent, I wake up refreshed | `excellent` | 9 |
| health_q8 | stress_management | Stress & Mental Health | 2 | I don't manage it well | `poorly` | 3 |
| | | | | I have some coping methods | `somewhat` | 5 |
| | | | | I manage it well | `well` | 7 |
| | | | | I have a proactive routine | `proactively`| 9 |
| health_q9 | preventive_care | Preventive Health | 1.5 | Never or rarely | `never` | 2 |
| | | | | Only when I have a problem| `sometimes` | 6 |
| | | | | I have regular annual check-ups | `regularly` | 9 |
| health_q10 | health_goals | Health Motivation | 1 | Improve Longevity | `longevity`| 9 |
| | | | | Increase Energy | `energy` | 8 |
| | | | | Manage Weight | `weight` | 7 |
| | | | | Improve Mental Clarity | `mental_clarity`| 8 |
| | | | | Enhance Athletic Performance | `athletic_performance` | 7 |

---
*Note: The Date of Birth (health_q1) and Contact Information (health_q11) questions are not scorable.* 