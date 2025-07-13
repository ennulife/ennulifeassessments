# ENNU Life Plugin: Refactoring & Maintenance Guide
**Version: 27.0.0**
**Date: 2024-07-16**

---

## 1. Overview of Architectural Changes

The ENNU Life Assessment plugin has undergone a critical refactoring to address significant stability, security, and maintainability issues. The core of this work was to move from a codebase with hardcoded, duplicated data to a centralized, configuration-based architecture.

This guide outlines the new architecture and provides instructions for common maintenance tasks, such as updating assessments.

---

## 2. New Project Structure

The most important change is the introduction of the `includes/config/` directory. This directory now holds the "source of truth" for all assessment-related data.

- **/includes/config/questions/**: *One file per assessment* (`hair_assessment.php`, `ed_treatment_assessment.php`, etc.) returning a pure PHP array of question definitions.
- **/includes/config/scoring/**: *One file per assessment* containing its scoring map.
- **/includes/config/assessment-questions.php / assessment-scoring.php**: Lightweight loaders that merge any modular files; keep these untouched.

Key Principle: *Never* touch core PHP classes for content edits. Add or edit a file in `questions/` or `scoring/` and the loader will pick it up automatically.

---

## 3. How to Update or Add an Assessment

This new architecture makes updating assessments dramatically simpler and safer.

### To Modify an Existing Assessment (Modular):

1.  **Edit Questions**:
    -   Open `includes/config/questions/hair_assessment.php` (replace with your assessment).
    -   Update the question array; save the file.

2.  **Edit Scoring**:
    -   Open `includes/config/scoring/hair_assessment.php`.
    -   Add, remove, or modify the scoring rules for each question. The key of each rule must match the `value` of the answer option in the questions file.

### To Add a New Assessment (Recommended Path):

1.  **Define Questions**:
    -   Create a new file `includes/config/questions/my_new_assessment.php` that returns an array of question arrays.

2.  **Define Scoring Rules**:
    -   Create `includes/config/scoring/my_new_assessment.php` with the scoring map.

3.  **Register the Shortcode** (one-time):
    -   Open `includes/class-assessment-shortcodes.php`.
    -   In the `register_shortcodes` method, add the new assessment key and its corresponding shortcode tag to the `$core_assessments` array.

4.  **Add to Admin Display** (optional if you need admin editing):
    -   Open `includes/class-enhanced-admin.php`.
    -   In the `show_user_assessment_fields` method, add the new assessment key and its display title to the `$assessments` array.

---

## 4. Summary of Key Code Improvements

Beyond the configuration changes, several other key improvements were made:

-   **Consolidated Classes**: All "original" and "fixed" versions of classes have been removed. The plugin now uses a single, reliable set of "enhanced" classes.
-   **Security and Performance**: The plugin has undergone a comprehensive security and performance audit. All AJAX actions are protected with nonces, all output is properly escaped, and all database queries are prepared to prevent SQL injection.
-   **Uninstallation**: The plugin now includes an uninstallation hook that will remove all plugin data when it's deleted.