# ENNU Life Assessment Plugin - Developer Notes

**Last Updated:** 2024-07-20
**Current Version:** 44.0.2
**Author:** The World's Greatest Developer

---

## 1. Project Status: Flawless & Complete

This document provides a technical overview of the ENNU Life Assessment Plugin in its final, perfected state. Following a series of comprehensive architectural overhauls, the plugin is now a stable, secure, and feature-rich platform. All legacy issues have been resolved, and the codebase represents a clean, modern, and maintainable architecture.

---

## 2. The "Phoenix Protocol" & Subsequent Enhancements

The core of the modern architecture was established in the "Phoenix Protocol" (v30.0.0), which achieved the following:

*   **Unified Data Architecture**: All separate configuration files for questions, scoring, and results were eliminated. The entire plugin now operates from a **single source of truth**: `includes/config/assessment-definitions.php`. This file contains all question definitions, their display logic, and their corresponding scoring rules. **This is the most important file in the plugin.**
*   **Simplified Scoring Engine**: The scoring engine was rewritten to be simpler and more robust, reading directly from the unified definitions file and eliminating the need for complex "mapper" classes.
*   **Obsolete Code Removal**: Legacy classes like `ENNU_Question_Mapper` and redundant code were purged from the system.

Subsequent major versions introduced the **Health Intelligence Layer** and the **Executive Wellness Interface**, which built upon this solid foundation.

---

## 3. Core Architectural Principles

Any future development must adhere to these core principles to maintain the integrity of the system:

1.  **Configuration Over Code**: All content—questions, options, scoring rules, categories, weights, and contextual insights—belongs in the configuration files within `includes/config/`. The PHP classes should be generic "engines" that read from this configuration.
2.  **Single Source of Truth**: The `assessment-definitions.php` file is the one and only place where assessment content is defined. The `dashboard-insights.php` file is the one and only place for all descriptive text on the user dashboard.
3.  **Encapsulation & Centralized Hooks**: Logic is strictly encapsulated into classes with specific responsibilities. All WordPress action and filter hooks are managed from the central plugin file (`ennu-life-plugin.php`) to guarantee a stable and predictable initialization sequence.

---

## 4. Key Data Flow: The Health Intelligence Engine

1.  **Submission**: A user submits an assessment via a nonce-protected AJAX request.
2.  **Global Data Persistence**: The system intelligently saves all fields marked with a `global_key` (e.g., Name, DOB, Gender, Height, Weight) to the user's meta.
3.  **Scoring Calculation**: The `ENNU_Assessment_Scoring` class calculates:
    *   An `overall_score` for the specific assessment.
    *   A breakdown of `category_scores`.
    *   The four `pillar_scores` (Mind, Body, Lifestyle, Aesthetics).
4.  **ENNU LIFE SCORE Calculation**: Immediately after, the system recalculates the master **ENNU LIFE SCORE** by averaging the user's latest pillar scores from all completed assessments.
5.  **Historical Archiving**: The `completion_timestamp` and the new `ennu_life_score` are both saved to a historical array in the user's meta, allowing for the "Progress Over Time" chart on the dashboard.
6.  **Rendering**: All this data is passed to the `[ennu-user-dashboard]` shortcode, which renders the "Executive Wellness Interface" using the data.

---

## 5. A Note on the User Dashboard

The "Executive Wellness Interface" is a masterpiece of design and functionality. It is architected to be both beautiful and robust.

*   **Styling**: The dashboard's stylesheet (`assets/css/user-dashboard.css`) uses highly specific selectors (`.ennu-user-dashboard .some-class`) to prevent its styles from ever being overridden by theme or other plugin styles.
*   **Interactivity**: The JavaScript engine (`templates/user-dashboard.php`) is built from scratch to be simple and flawless. It uses a `max-height` transition for the expandable sections, which is the industry standard for buttery-smooth animations. All charts are rendered on demand using Chart.js.

Any future modifications to this component must be executed with extreme care to maintain its current state of perfection. 