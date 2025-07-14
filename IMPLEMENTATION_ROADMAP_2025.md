# ENNU Life Implementation Roadmap (Updated: July 19, 2024)

This document reflects the current state of the plugin at **v29.0.45**, after the "Phoenix Protocol" refactor, which unified the entire data and scoring architecture into a single, consistent, and maintainable system.

---

## ✅ Phase 1: Foundational Architecture & Data Integrity (Completed)

**Objective:** To resolve all architectural flaws, unify the data structure, and establish a perfect, stable, and maintainable codebase. This phase is **100% complete** as of the Phoenix Protocol (v29.0.45).

### Key Milestones Achieved:

-   **[✓] Unified Data Architecture**: Executed the "Phoenix Protocol," a comprehensive refactoring that replaced the old, separate configuration files with a single source of truth: `includes/config/assessment-definitions.php`.
-   **[✓] Simplified Scoring Engine**: Rewrote the entire scoring engine (`class-scoring-system.php`) to be simpler, more robust, and directly compatible with the new unified data structure.
-   **[✓] Code Consistency**: Refactored all classes that touched assessment data (`class-assessment-shortcodes.php`, `class-enhanced-admin.php`) to use the new single source of truth.
-   **[✓] Definitive Bug Fixes**: The architectural unification permanently resolved all persistent bugs related to the admin points display, frontend rendering, and form submission processes.
-   **[✓] New Assessment Scaffolding**: Added the complete architectural framework for four new assessments (Sleep, Hormones, Menopause, Testosterone), proving the scalability of the new system.

---

## 🔵 Phase 2: Architect Pillar Scores as a Permanent Data Point (Next Up)

**Objective:** To evolve the scoring engine to calculate and permanently save the four Health Quad-Pillar scores (`Mind`, `Body`, `Lifestyle`, `Aesthetics`) every time an assessment is completed. This is the critical next step to enable advanced, cross-assessment analytics and build the foundation for the ENNU LIFE SCORE.

### Initiatives & Granular Details:

1.  **Enhance the Scoring Engine**
    *   **Task**: Upgrade the `calculate_scores` function in `class-scoring-system.php`. After it calculates the category scores, it will immediately perform the next step: using the pillar map to calculate the four pillar scores.
    *   **Technical Details**: The function will be enhanced to return not just the `overall_score` and `category_scores`, but also a new `pillar_scores` array.

2.  **Save Pillar Scores to the Database**
    *   **Task**: Update the `handle_assessment_submission` function to save this new `pillar_scores` array to the user's meta data (`ennu_{assessment_type}_pillar_scores`) upon every successful submission.
    *   **Technical Details**: This makes the pillar scores a permanent, historical data point, which is essential for tracking progress and for the ENNU LIFE SCORE calculation.

3.  **Optimize the Health Dossier Page**
    *   **Task**: Refactor the `templates/assessment-details-page.php` to be more efficient.
    *   **Technical Details**: Instead of calculating pillar scores on every page load, the template will now fetch the pre-calculated, permanently saved `_pillar_scores` array directly from the database, improving performance and reliability.

---

## Phase 3: Implement the ENNU LIFE SCORE & Advanced Analytics

**Objective:** To build the proprietary, top-level ENNU LIFE SCORE and provide administrators with high-level insights.

### Initiatives & Granular Details:

1.  **Create the Master ENNU LIFE SCORE Algorithm**
    *   **Task**: Build a new, dedicated function to calculate the ENNU LIFE SCORE.
    *   **Technical Details**: This algorithm will fetch the most recent Pillar Scores from *every* assessment a user has completed, calculate a weighted average based on the four pillars, and save the result to user meta.

2.  **Display the ENNU LIFE SCORE**
    *   **Task**: Enhance the `[ennu-user-dashboard]` to display the new ENNU LIFE SCORE as the primary, "north star" metric for the user.

3.  **Build Admin Analytics Dashboard**
    *   **Task**: Create a new admin page featuring visual charts (e.g., Chart.js) to display aggregate data, such as completions by assessment type and submissions over time.

---

## Phase 4: The Interactive Health Engine (Future)

**Objective**: To build out advanced, user-facing features that increase engagement and provide deeper insights.

-   **"What-If" Simulator**: An interactive module on the Health Dossier to see how changing answers affects scores in real-time.
-   **Gamification**: An achievements and goal-setting system to motivate users.

---

## Phase 5: The Proprietary Intelligence Platform (Future 2026)

**Objective**: To transform the plugin into a world-class Health Intelligence Platform.

-   **Digital Coach AI**: An automated system to send personalized "missions" based on user results.
-   **Anonymous Cohort Analysis**: Provide users with comparative data against their peers to contextualize their results.

