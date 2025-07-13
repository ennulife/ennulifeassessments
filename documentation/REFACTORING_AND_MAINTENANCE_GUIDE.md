# ENNU Life Plugin: Refactoring & Maintenance Guide
**Version: 28.0.0**
**Date: 2024-07-18**

---

## 1. Overview of Architectural Changes

The ENNU Life Assessment plugin has undergone critical refactoring to enhance stability, performance, and maintainability. The core of this work focused on solidifying the plugin's architecture, optimizing database interactions, and improving code clarity.

This guide outlines the current architecture and provides instructions for common maintenance tasks.

---

## 2. Project Structure

The "source of truth" for all assessment-related data is centralized in configuration files within the `includes/config/` directory.

-   `includes/config/assessment-questions.php`: Contains a single, comprehensive PHP array with all question definitions for every assessment.
-   `includes/config/assessment-scoring.php`: Contains the complete scoring map for all assessments.
-   `includes/config/results-content.php`: Defines the content displayed on the results pages.

**Key Principle:** To edit assessment content (questions, scoring, or results), modify the appropriate centralized configuration file. Avoid making content changes directly within the core PHP classes.

---

## 3. How to Update or Add an Assessment

### To Modify an Existing Assessment:

1.  **Edit Questions**:
    -   Open `includes/config/assessment-questions.php`.
    -   Locate the array for the specific assessment (e.g., `'hair'`) and update the question definitions.
2.  **Edit Scoring**:
    -   Open `includes/config/assessment-scoring.php`.
    -   Locate the scoring rules for the assessment and modify them as needed. The key of each rule must match the `value` of the answer option in the questions file.

### To Add a New Assessment:

1.  **Define Questions**:
    -   In `includes/config/assessment-questions.php`, add a new top-level key for your assessment (e.g., `'new_assessment'`) and populate it with an array of question arrays.
2.  **Define Scoring Rules**:
    -   In `includes/config/assessment-scoring.php`, add a corresponding key (`'new_assessment'`) with its scoring map.
3.  **Register the Shortcode**:
    -   Open `includes/class-assessment-shortcodes.php`.
    -   In the `register_shortcodes` method, add the new assessment key and its shortcode tag to the `$core_assessments` array.
4.  **Add to Admin Display** (if needed):
    -   Open `includes/class-enhanced-admin.php`.
    -   In the `show_user_assessment_fields` method, add the new assessment key and its display title to the `$assessments` array.

---

## 4. Summary of Key Code Improvements

Beyond the configuration changes, several other key improvements were made:

-   **Refactored Question Rendering**: The `render_question` method in `includes/class-assessment-shortcodes.php` has been significantly refactored for clarity and maintainability. The previous complex `if/else` block was replaced with a clean `switch` statement. Logic for each question type (e.g., `radio`, `select`, `checkbox`) is now encapsulated in its own private helper method (e.g., `_render_radio_question`). This makes the code easier to read, debug, and extend.

-   **Performance Optimization (N+1 Query Prevention)**: A performance bottleneck on the admin user profile page was resolved. The code now pre-fetches all user meta for the displayed user in a single query before looping through the assessments. This primes the WordPress object cache and prevents the "N+1" problem, where a separate database query was being made for every single piece of metadata. This is a model for how data should be fetched efficiently elsewhere in the plugin.

-   **Consolidated Hook Management**: All WordPress action and filter hooks are now managed from a central location. Initialization hooks (`init`) are set in the main plugin file (`ennu-life-plugin.php`), ensuring a predictable load order and preventing conflicts from duplicate hook registrations that were previously scattered in different classes.

-   **Security and Stability**: The plugin has undergone a comprehensive audit. All AJAX actions are protected with nonces, all output is properly escaped, and database queries are prepared to prevent SQL injection. A critical bug in the age calculation was also fixed.