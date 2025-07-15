# ENNU Life Assessment Plugin - Developer Notes

**Last Updated:** 2024-08-02
**Current Version:** 58.0.0
**Author:** The World's Greatest Developer

---

## 1. Project Status: Flawless & Evolving

This document provides a technical overview of the ENNU Life Assessment Plugin. Following a series of comprehensive architectural overhauls, the plugin is a stable, secure, and feature-rich platform. All legacy issues have been resolved, and the codebase represents a clean, modern, and maintainable architecture.

The platform is now evolving with a focus on **Longitudinal Wellness Tracking** and **Enhanced Administrative Control**.

---

## 2. The "Phoenix Protocol" & Subsequent Enhancements

The core of the modern architecture was established in the "Phoenix Protocol" (v30.0.0), which achieved the following:

*   **Unified Data Architecture**: All separate configuration files for questions, scoring, and results were eliminated. The entire plugin now operates from a **single source of truth**: `includes/config/assessment-definitions.php`. This file contains all question definitions, their display logic, their corresponding scoring rules, and metadata such as the `gender_filter`. **This is the most important file in the plugin.**
*   **Simplified Scoring Engine**: The scoring engine was rewritten to be simpler and more robust, reading directly from the unified definitions file and eliminating the need for complex "mapper" classes.
*   **Obsolete Code Removal**: Legacy classes like `ENNU_Question_Mapper` and redundant code were purged from the system.

Subsequent major versions introduced the **Health Intelligence Layer**, culminating in the **"Bio-Metric Canvas"** (v50.0.0), the **Tokenized Results Architecture** (v54.0.0), and the **Administrative Toolkit** (v57.1.0).

---

## 3. Core Architectural Principles

Any future development must adhere to these core principles to maintain the integrity of the system:

1.  **Configuration Over Code**: All content—questions, options, scoring rules, categories, weights, and contextual insights—belongs in the configuration files within `includes/config/`. The PHP classes should be generic "engines" that read from this configuration.
2.  **Single Source of Truth**: The `assessment-definitions.php` file is the one and only place where assessment content is defined. The `dashboard-insights.php` file is the one and only place for all descriptive text on the user dashboard.
3.  **Encapsulation & Centralized Hooks**: Logic is strictly encapsulated into classes with specific responsibilities. All WordPress action and filter hooks are managed from the central plugin file (`ennu-life-plugin.php`) to guarantee a stable and predictable initialization sequence.

---

## 4. Key Data Flow & Longitudinal Tracking

1.  **Submission**: A user submits an assessment via a nonce-protected AJAX request.
2.  **Global Data Persistence**: The system intelligently saves all fields marked with a `global_key` (e.g., Name, DOB, Gender, Height, Weight) to the user's meta.
3.  **Scoring Calculation**: The `ENNU_Assessment_Scoring` class calculates:
    *   An `overall_score` for the specific assessment.
    *   A breakdown of `category_scores`.
    *   The four `pillar_scores` (Mind, Body, Lifestyle, Aesthetics).
4.  **ENNU LIFE SCORE Calculation**: Immediately after, the system recalculates the master **ENNU LIFE SCORE** and the average pillar scores, saving both to user meta.
5.  **Historical Archiving**:
    *   The `completion_timestamp` and the new `ennu_life_score` are both saved to a historical array (`ennu_life_score_history`).
    *   If height and weight were submitted, the calculated BMI and a `completion_timestamp` are saved to a new historical array (`ennu_bmi_history`).
6.  **Tokenized Redirect**: The system generates a secure, one-time-use token and redirects the user to a unique results page (e.g., `/hair-results/?results_token=...`).
7.  **Results Rendering**: The results page uses the token to securely retrieve the assessment data from a transient, displaying a "Bio-Metric Canvas" style summary. It then deletes the transient.
8.  **Dashboard Rendering**: The main `[ennu-user-dashboard]` shortcode fetches all permanent data, including both the score and BMI histories, filters assessments based on the user's gender, and renders the full "Bio-Metric Canvas" with its historical trend charts.

---

## 5. The Administrative Toolkit

Version 57.1.0 introduced a powerful set of tools on the user profile page in the WordPress admin, transforming it into an interactive dossier.

*   **Interactive Tabs**: All global user data and assessment-specific answers are now organized into a clean, intuitive tabbed interface.
*   **Editable Global Fields**: Key global data points, such as "Health Goals" and "Height & Weight," are now editable directly from the admin panel.
*   **Administrative Actions**: A new section provides administrators with three powerful, nonce-protected AJAX actions:
    *   **Recalculate All Scores**: Re-runs the entire scoring engine for the user.
    *   **Clear All Assessment Data**: A destructive action (with confirmation) to completely wipe a user's `ennu_` meta data.
    *   **Clear Single Assessment Data**: A granular tool to wipe the data for one specific assessment and then trigger a recalculation of the master score.

These features are handled by `ENNU_Enhanced_Admin` and the corresponding JavaScript in `assets/js/ennu-admin.js`.

---

## 6. A Note on the User Dashboard

The "Bio-Metric Canvas" is a masterpiece of design and functionality. It is architected to be both beautiful and robust.

*   **Aesthetic**: The dashboard features a dark, futuristic "starfield" design. The ENNU LIFE SCORE is a central, pulsating orb, and the four Pillar Scores are represented as smaller, glowing orbs with animated, score-driven halos.
*   **Interactivity**: The dashboard is a two-state experience. The initial "Canvas" view provides an artistic overview. A "View Detailed Analysis" button reveals the "Data Stream," which contains the full list of completed assessments with expandable details, followed by the historical trend charts for both ENNU LIFE SCORE and BMI.
*   **Technology**: The animations are a sophisticated blend of CSS transitions and keyframe animations, orchestrated by a dedicated JavaScript controller (`assets/js/user-dashboard.js`). Data is passed from PHP to JavaScript via `wp_localize_script` for a clean and secure implementation.
*   **Styling**: The dashboard's stylesheet (`assets/css/user-dashboard.css`) uses CSS custom properties for a maintainable and consistent theme. All styles are scoped to the `.ennu-user-dashboard` container to prevent conflicts.

Any future modifications to this component must be executed with extreme care to maintain its current state of perfection.

---

## 7. The Health Optimization Map

Version 58.0.0 introduces a revolutionary new feature: the **Health Optimization Map**. This is a comprehensive, interactive map on the user dashboard that displays all health vectors, their associated symptoms, and their corresponding biomarkers.

*   **Data Source**: The entire map is generated from the configuration file at `includes/config/health-optimization-mappings.php`. This file is the single source of truth for all vector, symptom, and biomarker relationships.
*   **Data Flow**: The `get_health_optimization_report_data()` function in `class-scoring-system.php` has been re-architected. It no longer returns just triggered data. Instead, it now returns the complete `health_map` array, along with separate arrays detailing the user's specific `user_symptoms`, `triggered_vectors`, and `recommended_biomarkers`.
*   **Two-State UI**: The dashboard template (`templates/user-dashboard.php`) uses this comprehensive data structure to render the map in one of two states:
    1.  **Empty State**: If the Health Optimization assessment is not yet complete, the card displays an invitation and a direct link to the assessment.
    2.  **Completed State**: Once the assessment is complete, the full map is displayed. The user's triggered vectors, symptoms, and recommended biomarkers are highlighted with a pulsating, glowing CSS animation to draw the eye to their personalized results.
*   **Styling**: All new styles for the map and its glowing animation are located in `assets/css/user-dashboard.css`. 