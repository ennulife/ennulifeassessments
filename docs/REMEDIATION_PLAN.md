### **Official Remediation Plan: Restoring the Intended User Experience**

**Preamble:** My exhaustive analysis of the codebase has confirmed the critical issues outlined in the original `system-architecture.md` audit. It has also revealed a clear path to resolving them by completing an in-progress architectural refactoring. This plan will not add new features, but will instead activate the modern, superior, and partially-built systems to deliver the stable and intuitive user experience that was originally intended.

**Primary Goal:** Stabilize the plugin and deliver the expected user experience by resolving all critical data integrity and user interface bugs.

---

**Phase 1: Stabilize the Foundation (COMPLETE)**

This phase addresses the most critical backend bugs that prevent the plugin from functioning reliably.

*   **Task 1.1: Resolve Health Goals Data Inconsistency.**
    *   **User Experience Impact:** Fixes the bug where users' selected health goals on the dashboard were ignored by the scoring engine, resulting in inaccurate scores.
    *   **Status:** ✅ **COMPLETE.** The legacy code has been corrected, and the data migration script is in place. All parts of the plugin now use a single, consistent data source for health goals.

*   **Task 1.2: Consolidate the Scoring Engine.**
    *   **User Experience Impact:** Fixes the bug where multiple, conflicting scoring calculators could produce different scores for the same user, leading to confusion and a lack of trust in the system.
    *   **Status:** ✅ **COMPLETE.** The redundant legacy calculators (`ENNU_Potential_Score_Calculator`, `ENNU_New_Life_Score_Calculator`) have been removed. The `ENNU_Scoring_System` is now the single, authoritative orchestrator for all score calculations.

*   **Task 1.3: Reroute Assessment Submission Handler.**
    *   **User Experience Impact:** Switches the assessment submission process from the old, complex monolithic class to the modern, robust, and more maintainable `ENNU_Form_Handler`. This improves the reliability and stability of the most critical user interaction in the plugin.
    *   **Status:** ✅ **COMPLETE.** The legacy AJAX handler has been deactivated and the modern handler has been activated.

---

**Phase 2: Deliver the Intended Interactive Dashboard**

This phase focuses on activating the "ghost features" that are essential for the intended user dashboard experience. These are not new features, but rather the fully-designed replacements for the current static and non-functional dashboard components.

*   **Task 2.1: Activate Interactive Health Goals.**
    *   **User Experience Impact:** This will replace the current, non-functional health goals display with the intended interactive experience, allowing users to click to select/deselect goals and update their profile with a single "Update" button, all without a page reload.
    *   **Status:** ✅ **COMPLETE.** The necessary JavaScript file (`health-goals-manager.js`) has been created and the backend has been wired up to load it.

*   **Task 2.2: Activate "My Health Trends" Visualization.**
    *   **User Experience Impact:** This will activate the currently dormant "My Trends" tab on the dashboard. Users will be able to see beautiful, interactive charts of their Life Score, Pillar Scores, and biomarker history, allowing them to track their progress over time, which is a core part of the intended user journey.
    *   **Required Action:** Create the frontend JavaScript file (`assets/js/trends-visualization.js`) to make AJAX calls to the existing, fully-functional backend (`ENNU_Trends_Visualization_System`) and render the charts.

---

**Phase 3: Finalize the Architectural Transition**

This phase involves cleaning up the remaining architectural issues to ensure long-term stability and maintainability.

*   **Task 3.1: Decommission the Legacy Shortcode Monolith.**
    *   **User Experience Impact:** While not a direct UX change, this is a critical stability improvement. It replaces the fragile, 6,200-line `class-assessment-shortcodes.php` with the modern, inactive `ENNU_Shortcode_Manager` and `ENNU_Form_Renderer`. This will make the frontend rendering of assessments and dashboards more reliable and easier to update in the future.
    *   **Required Action:** Activate the `ENNU_Shortcode_Manager` and migrate the rendering logic from the old monolith to the new `ENNU_Form_Renderer` templates.

*   **Task 3.2: Unify Biomarker Data Storage.**
    *   **User Experience Impact:** Fixes potential bugs where biomarker data (like weight) might not appear consistently across the dashboard because it's being read from two different database locations (`ennu_biomarker_data` and `ennu_user_biomarkers`).
    *   **Required Action:** Choose a single source of truth for biomarker data and refactor all relevant classes (`ENNU_Biomarker_Manager`, `ENNU_Lab_Data_Landing_System`, `ENNU_Biomarker_Auto_Sync`) to use it exclusively.

*   **Task 3.3: Unify Biomarker Range Configuration.**
    *   **User Experience Impact:** Ensures that the biomarker ranges used for scoring and display are always consistent.
    *   **Required Action:** Decide whether to use the file-based configuration or the more advanced database-driven "AI Medical Team" system. Refactor all classes to use only one of these systems and remove the other. 