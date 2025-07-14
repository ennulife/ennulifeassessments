# ENNU Life Plugin: Refactoring & Maintenance Guide
**Version: 30.0.0**
**Date: 2024-07-19**

---

## 1. Overview of Architectural Changes

The ENNU Life Assessment plugin has undergone a complete architectural overhaul (The "Phoenix Protocol") to create a single, unified data structure that is stable, scalable, and easy to maintain. This guide outlines the current, perfected architecture.

---

## 2. Project Structure

The single "source of truth" for all assessment-related data is the centralized configuration file:

-   `includes/config/assessment-definitions.php`: This file contains a comprehensive PHP array with all question definitions, display options, and their corresponding scoring rules (category, weight, and points) for every assessment in the system.

**Key Principle:** To edit any aspect of an assessment (questions, options, or scoring), you only need to modify this single, unified file.

---

## 3. How to Update or Add an Assessment

### To Modify an Existing Assessment:

1.  **Edit Questions & Scoring**:
    -   Open `includes/config/assessment-definitions.php`.
    -   Locate the array for the specific assessment (e.g., `'hair_assessment'`).
    -   Modify the question's `title`, `options`, or the embedded `scoring` array directly within the question's definition.

### To Add a New Assessment:

1.  **Define Questions & Scoring**:
    -   In `includes/config/assessment-definitions.php`, add a new top-level key for your assessment (e.g., `'new_assessment'`) and populate it with an associative array of question definitions. Ensure each question has a unique ID as its key (e.g., `'new_q1'`) and includes its own `scoring` array.
2.  **Register the Shortcode**:
    -   Open `includes/class-assessment-shortcodes.php`.
    -   In the `register_shortcodes` method, add the new assessment key and its shortcode tag to the `$core_assessments` array.
3.  **Add to Admin Display**:
    -   Open `includes/class-enhanced-admin.php`.
    -   In the `show_user_assessment_fields` method, add the new assessment key and its display title to the `$assessments` array.

---

## 4. Summary of Key Architectural Decisions

-   **Unified Configuration**: The single `assessment-definitions.php` file eliminates data fragmentation and the need for complex "mapper" classes, making the system dramatically more robust and easier to debug.
-   **Encapsulated Scoring Logic**: By embedding scoring rules within each question, the relationship between a question and its score is explicit and clear.
-   **Simplified Engine**: The core scoring engine (`class-scoring-system.php`) is now significantly simpler, as it no longer needs to reconcile data from multiple sources.
# ENNU Life: Scoring Architecture & Strategy

**Document Version:** 2.0
**Date:** July 19, 2024
**Author:** The World's Greatest WordPress Developer
**Status:** OFFICIAL - Reflects v30.0.0 Architecture

---

## 1.0 Executive Summary: The Four Tiers of Health Intelligence

This document provides the official architectural and strategic overview of the ENNU Life scoring system. It is the definitive source of truth for understanding how user data is transformed into meaningful, actionable health intelligence.

The system is architected as a four-tier hierarchy, moving from the granular to the holistic. Each tier serves a unique purpose, creating a comprehensive and powerful user journey.

*   **Tier 1: Category Scores (The "Why")** - Granular feedback within a single assessment.
*   **Tier 2: Overall Assessment Score (The "What")** - A simple, top-line score for a specific health vertical.
*   **Tier 3: Pillar Scores (The "Holistic How")** - An aggregated view of a user's health across the four core pillars: Mind, Body, Lifestyle, and Aesthetics.
*   **Tier 4: The ENNU LIFE SCORE (The "Who")** - The ultimate, proprietary metric representing a user's total health equity on the platform.

This architecture is **stable, scalable, and strategically valuable**, providing rich insights for the user and creating a powerful, proprietary data asset for the business. The completion of the "Phoenix Protocol" in v30.0.0 has solidified this structure. 