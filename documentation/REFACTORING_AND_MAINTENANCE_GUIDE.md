# ENNU Life Plugin: Refactoring & Maintenance Guide
**Version: 24.12.3**
**Date: July 12, 2025**

---

## 1. Overview of Architectural Changes

The ENNU Life Assessment plugin has undergone a critical refactoring to address significant stability, security, and maintainability issues. The core of this work was to move from a codebase with hardcoded, duplicated data to a centralized, configuration-based architecture.

This guide outlines the new architecture and provides instructions for common maintenance tasks, such as updating assessments.

---

## 2. New Project Structure

The most important change is the introduction of the `includes/config/` directory. This directory now holds the "source of truth" for all assessment-related data.

- **/includes/config/assessment-questions.php**: Contains all questions, answer options, and field types for every assessment.
- **/includes/config/assessment-scoring.php**: Contains all the scoring rules, weights, and category mappings for every assessment.

**Key Principle**: *Never* modify plugin logic to change an assessment. All changes to questions or scoring should be done in these configuration files.

---

## 3. How to Update or Add an Assessment

This new architecture makes updating assessments dramatically simpler and safer.

### To Modify an Existing Assessment:

1.  **Edit Questions**:
    -   Open `includes/config/assessment-questions.php`.
    -   Find the appropriate assessment key (e.g., `'hair_assessment'`).
    -   Add, remove, or modify the question arrays as needed. Each question is an associative array with a `title`, `description`, `type`, and `options`.

2.  **Edit Scoring**:
    -   Open `includes/config/assessment-scoring.php`.
    -   Find the corresponding assessment key.
    -   Add, remove, or modify the scoring rules for each question. The key of each rule must match the `value` of the answer option in the questions file.

### To Add a New Assessment:

1.  **Define Questions**:
    -   In `includes/config/assessment-questions.php`, add a new top-level key for your assessment (e.g., `'new_assessment_v2'`).
    -   Define its questions using the same array structure as the others.

2.  **Define Scoring Rules**:
    -   In `includes/config/assessment-scoring.php`, add a corresponding top-level key for the new assessment.
    -   Define its scoring rules.

3.  **Register the Shortcode**:
    -   Open `includes/class-assessment-shortcodes.php`.
    -   In the `register_shortcodes` method, add the new assessment key and its corresponding shortcode tag to the `$core_assessments` array.

4.  **Add to Admin Display**:
    -   Open `includes/class-enhanced-admin.php`.
    -   In the `show_user_assessment_fields` method, add the new assessment key and its display title to the `$assessments` array.

---

## 4. Summary of Key Code Improvements

Beyond the configuration changes, several other key improvements were made:

-   **Consolidated Classes**: All "original" and "fixed" versions of classes have been removed. The plugin now uses a single, reliable set of "enhanced" classes (`ENNU_Enhanced_Admin`, `ENNU_Enhanced_Database`, etc.).
-   **Security Patches**:
    -   Removed the `extract()` function to prevent variable injection.
    -   Removed a dangerous `error_log` polyfill.
    -   Standardized AJAX security nonces to `ennu_ajax_nonce`.
-   **Performance Fixes**:
    -   Disabled the problematic security log that was writing to the `wp_options` table to prevent database bloat. Critical events are still logged to the standard server error log.
-   **Dead Code Removal**: Removed numerous unnecessary polyfills and redundant AJAX hooks, making the code lighter and easier to read.
-   **WooCommerce Decoupling**: The hardcoded product list was removed from the integration class. Products should now be managed in the WooCommerce admin, though the programmatic creation logic is still available if needed.

---

This refactoring has established a stable and maintainable foundation for the plugin. Adhering to the new architecture will ensure that future development is efficient and does not re-introduce the instability of previous versions. 