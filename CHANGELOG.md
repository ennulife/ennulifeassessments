# ENNU Life Assessment Plugin Changelog

## Version 25.0.0 - Core Feature Complete & Documentation Release
*Date: July 12, 2025*

This major version marks the completion of the plugin's core feature set and a full documentation overhaul. The plugin is now stable, fully functional, and ready for production use.

### ✨ New Features
- **User-Facing Results Page**: Implemented a dynamic, personalized results page that displays the user's overall score, a detailed interpretation of their results, and a breakdown of scores by category.
- **Comprehensive Scoring Documentation**: Created a full suite of documentation for the scoring system, including a deep-dive guide and individual verification reports for each assessment.

### 🐛 Bug Fixes & Enhancements
- **Completed Scoring Logic**: Fully implemented the category scoring calculations and data flow, ensuring the new results page has the data it needs.
- **Final Documentation Polish**: Updated all documentation files to be consistent with the final `25.0.0` version.

## Version 24.13.0 - Comprehensive Documentation Release
*Date: July 12, 2025*

This release introduces a full suite of comprehensive documentation, including detailed scoring guides and audits for every assessment, providing complete transparency into the system's logic.

### 📝 Documentation
- **Scoring System Deep Dive**: Created a central document explaining the architecture, data flow, and logic of the scoring engine.
- **Individual Scoring Guides**: Created separate, detailed scoring guides for all five core assessments, each with a full breakdown of questions, categories, weights, and point values.
- **Scoring Audits**: Created separate, formal audit and verification documents for each assessment, confirming the correctness and clinical soundness of the scoring logic.
- **General Cleanup**: Updated version numbers and dates across all documentation to ensure consistency.

## Version 24.12.4 - Admin UX Enhancement
*Date: July 12, 2025*

This release enhances the admin user profile page to provide greater clarity and control over assessment data.

### ✨ Admin UI/UX Enhancements
- **Score and ID Display**: The admin view for assessment answers now displays the point value and the answer ID next to each radio and checkbox option, providing immediate insight into the scoring system.
- **Improved Field ID Visibility**: The unique field ID for each question (e.g., `health_q1`) is now clearly displayed under the question title for easy reference.

## Version 24.12.3 - Final Hotfix
*Date: July 12, 2025*

This release provides the definitive fix for a persistent bug related to saving multiselect/checkbox fields from both the frontend assessments and the backend admin user profile.

### 🐛 Critical Bug Fix
- **Fixed Multiselect/Checkbox Saving**: Resolved the root cause of the checkbox saving issue. The frontend JavaScript now correctly collects and sends the array of selected values, and the backend save handlers (for both form submissions and admin profile updates) now correctly process this array, including cases where all options are deselected. This ensures data is saved with 100% accuracy.

## Version 24.12.2 - Admin & Frontend UI Polish
*Date: 2024-07-24*

This release focuses on significant UI/UX improvements for both the admin user profile page and the frontend assessment forms, resolving all known visual and functional bugs.

### ✨ UI/UX Enhancements
- **Interactive Admin Fields**: Assessment answers in the user profile are now fully interactive. They display as their proper field type (radio, checkbox, text) with all options visible and the user's answer correctly selected. Administrators can now edit and update user answers directly from this page.
- **Improved Admin Layout**: Cleaned up the "Complete Field Reference" sections by integrating field descriptions directly into the main row, creating a more compact and readable layout.
- **Consistent Frontend Styling**: Multiselect/checkbox questions on the frontend now have the exact same modern styling as the radio button questions, providing a consistent user experience.

### 🐛 Bug Fixes
- **Fixed Missing Assessment Data**: Resolved a critical bug where the assessment answer sections were not appearing at all on the user profile page.
- **Corrected DOB/Age Sync**: Fixed a data inconsistency where a separate "Age" field was displayed. Age is now correctly calculated from the Date of Birth and is not an editable field.
- **Fixed "Clear Data" Button**: Repaired the non-functional "Clear All ENNU Data" button by implementing its backend AJAX handler.
- **Resolved Minor UI Glitches**: Cleaned up spacing and formatting in the admin dashboard metrics.

## Version 24.12.1 - Hotfix Release
*Date: July 12, 2025*

This is a critical hotfix release that resolves a fatal error and a form submission error introduced in version 24.12.0.

### 🔒 Critical Fixes
- **Resolved Fatal Error**: Fixed a fatal error (`Call to undefined method ENNU_Assessment_Shortcodes::setup_hooks()`) caused by an incorrect refactoring that removed a required method. The method has been restored, and the plugin is now stable.
- **Fixed Form Submission Error**: Corrected a JavaScript nonce mismatch that was causing all form submissions to fail with a "Security check failed" error. The script localization and nonce handling are now centralized and correct.

## Version 24.12.0 - Core User Experience Fixes
*Date: July 12, 2025*

This release focuses on critical bug fixes that fully implement the required user experience for global data synchronization and user registration, ensuring a seamless and logical journey for all users.

### ✨ Core Functionality Implemented
- **True Global Fields**: Implemented a robust system for synchronizing global fields (`first_name`, `last_name`, `email`, `phone`, `gender`, `dob`). Data is now correctly shared across native WordPress user profiles, WooCommerce billing fields, and all assessments.
- **Seamless Logged-In Experience**: Logged-in users will now see all global fields correctly pre-populated when they start any assessment, creating a consistent and intelligent user journey.
- **Correct Guest User Flow**:
    - **New Users**: When a guest user submits an assessment, a full WordPress account is automatically and correctly created with all their provided details.
    - **Existing Users**: If a guest user's email is already in the database, they are now correctly prompted to log in to continue, preventing duplicate accounts and confusion.

### 🐛 Bug Fixes
- **Corrected Data Saving**: Fixed a critical bug where global fields were being saved with incorrect keys. All data is now saved with the proper global or assessment-specific prefixes.
- **Fixed Pre-filling Logic**: Repaired the form rendering logic to fetch data from the correct sources (native user object and global meta), ensuring fields are pre-filled reliably.
- **Resolved Fatal Error Risk**: Removed dangling references to non-existent AJAX handlers in the WooCommerce integration class, preventing a potential fatal error.

## Version 24.11.0 - Major Refactoring and Stabilization
*Date: July 12, 2025*

This is a critical update that addresses architectural flaws, security vulnerabilities, and numerous bugs. The plugin is now significantly more stable, secure, and maintainable.

### ✨ New Features & Major Improvements
- **Architectural Overhaul**: Centralized all assessment data (questions, scoring rules) into dedicated configuration files located in `includes/config/`. This removes all hardcoded data from the PHP classes, making the plugin dramatically easier to update and maintain.
- **Code Consolidation**: Removed old and redundant classes (`class-admin.php`, `class-database.php`) to resolve conflicts and prevent fatal errors. The codebase now uses a single, consistent set of 'enhanced' classes.
- **Improved Stability**: Replaced fragile Reflection calls with direct, reliable method calls, improving both performance and stability.

### 🔒 Security Fixes
- **Patched `extract()` Vulnerability**: Removed a dangerous call to `extract()` in the template loader to prevent variable injection vulnerabilities.
- **Removed Insecure Polyfill**: Deleted an unnecessary and insecure polyfill for the `error_log()` function that could write logs to a publicly accessible file.
- **Standardized AJAX Nonces**: Corrected inconsistent AJAX nonce handling between the frontend and backend to ensure all AJAX requests are properly secured.

### 🐛 Bug Fixes & Performance
- **Fixed Activation Bug**: Repaired the plugin activation hook to ensure that the necessary database tables are correctly created on installation.
- **Resolved Duplicate Initialization**: Removed a redundant call that was causing the entire plugin to load twice, which could lead to unpredictable behavior.
- **Fixed Performance Bottleneck**: Disabled a feature that was writing a large security log to the `wp_options` table, preventing database bloat and improving site-wide performance.
- **Cleaned Up Dead Code**: Removed multiple unused AJAX hooks and unnecessary function polyfills to streamline the codebase.
- **Decoupled WooCommerce Data**: Removed the hardcoded product list from the WooCommerce integration class, encouraging the standard practice of managing products in the WordPress admin.

## Version 24.10.1
*Date: 2024-06-15*
- Fix: Corrected issue where user meta was not saving correctly from the frontend assessment forms. The meta keys were mismatched between the form and the database handler.
- Enhancement: Added error logging for AJAX form submissions to better capture issues.
- Fix: Resolved a JavaScript conflict with the Pixfort theme that prevented the form from advancing.

## Version 23.5.0
*Date: 2024-05-20*
- Feature: Added Comprehensive Health Assessment.
- Enhancement: Improved admin UI for viewing assessment results.
- Fix: Addressed a bug where multiselect fields were not saving properly.

## Version 22.8.0
*Date: 2024-04-10*
- Initial enhanced version with separate classes for admin and database.
- Added 5 core assessments.
- Implemented basic shortcode functionality.

---
*For older versions, please refer to internal version control history.*

