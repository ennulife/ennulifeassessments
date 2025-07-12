# ENNU Life: Scoring System Deep Dive

**Version**: 25.0.0
**Date**: July 12, 2025

---

## 1. Overview

This document provides a comprehensive, technical deep dive into the ENNU Life Assessment scoring system. It is intended for developers, administrators, and data analysts who need to understand, verify, or modify the scoring logic.

The scoring system is designed to be **centralized and easily maintainable**. All scoring rules are kept in a single configuration file, completely separate from the plugin's PHP logic. This allows for rapid and safe updates to point values without touching the core codebase.

The system calculates two primary metrics:
*   **An `overall_score`** for each assessment, which is a weighted average of all answers.
*   **A `category_scores` array**, which provides a score breakdown for different aspects of a user's health (e.g., "Lifestyle Factors", "Genetic Factors").

---

## 2. Data Flow: From Answer to Score

Here is the step-by-step data flow that occurs when a user's assessment is scored:

1.  **Submission**: A user submits an assessment. The form data is a list of simple question IDs and their answers (e.g., `hair_q5: 'father'`).
2.  **Scoring Trigger**: The `handle_assessment_submission` function calls `ENNU_Assessment_Scoring::calculate_scores()`, passing in the assessment type and the user's raw answers.
3.  **Configuration Loading**: The `calculate_scores` method loads the two central configuration files:
    *   `includes/config/assessment-questions.php`
    *   `includes/config/assessment-scoring.php`
4.  **Question-to-Score Mapping**: For each answer, the system performs a critical mapping step:
    *   It takes the simple question ID (e.g., `hair_q5`).
    *   It uses the `ENNU_Question_Mapper` class to look up that question in the questions config file.
    *   It finds the question's `scoring_key` (e.g., `'family_history'`). This key is the bridge between the question and its scoring rules.
5.  **Score Calculation**:
    *   Using the `scoring_key` (`'family_history'`), it looks up the rules in the scoring config file.
    *   It finds the user's answer (`'father'`) in the `answers` array for that rule.
    *   It retrieves the corresponding point value (e.g., `5`).
    *   It retrieves the question's `weight` (e.g., `2`) and `category` (e.g., `'Genetic Factors'`).
6.  **Aggregation**: The calculated score for each question is multiplied by its weight and added to both the total score and its respective category's score.
7.  **Finalization**: After all questions are processed, the `overall_score` and all `category_scores` are finalized by dividing the total weighted points by the total weight, and the results are returned.

---

## 3. Core Components

The entire scoring system is powered by four key files:

*   **`includes/config/assessment-questions.php`**: This file defines the questions themselves. The most important property for scoring is the **`scoring_key`**. This key links a question to its rules in the scoring file.
*   **`includes/config/assessment-scoring.php`**: This is the master configuration for all scoring. It defines the point values, weights, and categories for every possible answer to every scorable question. The top-level keys here **must match** the `scoring_key` from the questions file.
*   **`includes/class-question-mapper.php`**: This class acts as the "translator" between the simple form submission data and the scoring engine. Its only job is to convert a simple ID like `hair_q5` into a semantic key like `family_history`.
*   **`includes/class-scoring-system.php`**: This class contains the PHP logic that executes the data flow described above. It loads the configs, uses the mapper, calculates the scores, and returns the final results.

---

## 4. Complete Scoring Tables

The following tables provide a full, human-readable breakdown of every scorable question in the system.

### Hair Health Assessment

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

### Skin Health Assessment

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

### General Health Assessment

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
| | | | | Manage Weight | `weight`