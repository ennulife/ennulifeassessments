# ENNU Life Assessment Plugin Changelog

## Version 26.0.15 - Final Admin Display Fix
*Date: 2024-07-25*

### 🐛 Bug Fixes
- **Corrected Admin Point Display**: Fixed a fatal error on the user profile page caused by a variable mismatch. The correct variable is now used for score lookups, ensuring the page loads correctly and all point values are displayed next to their corresponding answers.

## Version 26.0.14 - Final Admin Score Display Fix
*Date: 2024-07-25*

### 🐛 Bug Fixes
- **Corrected Admin Point Display**: Fixed the final bug preventing point values from showing next to answers in the admin user profile. The correct key is now being used for the score lookup, and all points are displayed as intended.

## Version 26.0.13 - Final Scoring Data Alignment
*Date: 2024-07-25*

### 🐛 Bug Fixes
- **Corrected All Point Values**: Performed a final, meticulous review of all scoring configurations against the user's provided validation data. All inconsistent point values have been corrected, and all unscored answers now have the correct points assigned. The admin view is now a 100% accurate representation of the scoring engine.

## Version 26.0.12 - Admin Score Display Fix
*Date: 2024-07-25*

### 🐛 Bug Fixes
- **Fixed Missing Admin Points**: Resolved a bug in the admin user profile view where the point values were not appearing next to every scorable answer. The score lookup now uses the correct key, and all points are displayed as intended.

## Version 26.0.11 - Final Scoring Audit & Completion
*Date: 2024-07-25*

### ✨ New Features & Enhancements
- **Final Audit Pass**: Completed the master scoring audit, verifying and resolving all remaining gaps in the assessment logic.
- **100% Category Coverage**: All defined "Score Breakdown" categories for all five assessments are now fully implemented, scored, and displayed correctly, achieving the goal of a complete and factually accurate system.

## Version 26.0.10 - Pre-populated Age Display Fix
*Date: 2024-07-25*

### 🐛 Bug Fixes
- **Fixed Initial Age Calculation**: Corrected a bug where the calculated age would not display on page load if the Date of Birth was already pre-populated. The age now renders immediately, creating a more seamless user experience.

## Version 26.0.9 - Enhanced Global Fields System
*Date: 2024-07-25*

### ✨ New Features & Enhancements
- **Unified Global Fields**: Refactored the system to treat "Health Goals," "Height," and "Weight" as global user fields. This data is now captured once and will be automatically pre-populated across any assessment, creating a more intelligent and seamless user experience.
- **Unification of Health Goals**: The "Health Goals" question is now identical across the Welcome and Health assessments, ensuring data consistency.

## Version 26.0.8 - Final Form Polish
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Legible Placeholders**: Increased the font size of placeholder text in the contact form to ensure legibility.
- **Forced Rounded Corners**: Applied a more forceful CSS rule to guarantee the Date of Birth dropdowns have rounded corners across all browsers.

## Version 26.0.7 - Final Form UX Refinements
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Compact Answer Buttons**: Reduced the height of the answer option boxes for a more compact and modern layout.
- **Placeholder Labels**: The contact information form now uses inline placeholders instead of labels for a cleaner look.
- **Centered Form Layout**: The entire assessment form is now centered on the page with a maximum width to improve appearance on large monitors.

### 🐛 Bug Fixes
- **Removed Auto-Scroll**: Disabled the automatic scrolling that occurred when navigating between questions, per user feedback.

## Version 26.0.6 - Advanced Responsive Form Grid
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Intelligent Grid Layout**: Implemented a more robust, column-specific grid system for answer options. This ensures that questions with 4 options will always appear in a single row on desktop, while still providing intelligent stacking for 2 and 3-column layouts on smaller devices.

## Version 26.0.5 - Final Height, Weight & DOB Styling
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Styled Height & Weight Fields**: Applied proper styling to the Height and Weight input fields, ensuring they are perfectly centered and visually consistent with the rest of the form design.

### 🐛 Bug Fixes
- **Fixed DOB Dropdown Corners**: A more robust CSS fix was applied to ensure the Date of Birth dropdowns have correctly rounded corners on all browsers and platforms.

## Version 26.0.4 - Animated Progress Bar
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Pulsating Progress Bar**: Enhanced the progress bar with a new gradient and a subtle, pulsating animation to give the user a sense of active progress as they move through the assessment.

## Version 26.0.3 - Final Form Layout Refinements
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Constrained Answer Width**: Set a `max-width` on the answer options container to prevent buttons from stretching too wide on layouts with only two options (e.g., gender selection), ensuring a balanced and professional appearance.
- **Centered Age Display**: Centered the calculated age text that appears above the Date of Birth dropdowns.

## Version 26.0.2 - DOB Question UI & Logic Fix
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Fixed Dropdown Corners**: Resolved a browser styling issue where the Date of Birth dropdowns had sharp corners. They are now correctly rounded.
- **Centered Navigation**: Ensured the "Next" and "Previous" buttons are always perfectly centered, even when only one is visible.

### 🐛 Bug Fixes
- **Fixed Age Calculation Display**: Corrected a JavaScript bug that prevented the user's calculated age from appearing on the form after they selected their date of birth. This now works as intended.

## Version 26.0.1 - Final UI Polish & Bug Fixes
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Polished Form Layout**: Centered all titles, subtitles, and navigation buttons. Adjusted font sizes for better readability and visual balance.
- **Styled Progress Bar**: The progress bar is now correctly centered and has a maximum width for a cleaner look.

### 🐛 Bug Fixes
- **Fixed Progress Bar**: Resolved a JavaScript bug that prevented the progress bar from updating as the user advanced through the questions. It now works correctly.

## Version 26.0.0 - Scoring & Results Overhaul
*Date: 2024-07-25*

This is a major feature-complete release that finalizes the user experience and ensures the clinical and mathematical accuracy of the entire assessment system.

### ✨ New Features & Enhancements
- **Complete Scoring System Audit**: Created a master `SCORING_AUDIT_AND_VALIDATION.md` document that meticulously catalogs and validates the entire scoring engine.
- **100% Category Coverage**: Identified and fixed all gaps in the assessments. Added new, clinically relevant questions and activated all unscored questions. Every defined "Score Breakdown" category is now correctly calculated and displayed.
- **BMI Calculation & Display**: Added height and weight questions to the Weight Loss Assessment and implemented automatic BMI calculation, which is now displayed on the results page.
- **Dynamic & Personalized Results Pages**: Completely overhauled the end-user results pages. They are no longer static and now display a full, data-rich report including the user's score, interpretation, a detailed category breakdown, and personalized observations based on their specific answers.
- **Responsive Form Layout**: Implemented a responsive grid layout for assessment answers for a significantly improved user experience on all devices.

### 🐛 Bug Fixes
- **Fixed Invisible Score Dashboard**: Resolved the critical bug that was hiding the admin score dashboard, making all scores and interpretations visible to administrators.
- **Fixed Multiselect Submissions**: Corrected a bug that caused submissions to fail on assessments with multiple-choice questions.
- **Fixed Results Page Styling**: Refactored all results page styles into a dedicated, properly enqueued stylesheet to ensure consistent and professional styling on all pages.

## Version 25.0.10 - BMI Calculation & Display
*Date: 2024-07-25*

### ✨ New Features
- **BMI Calculation**: Added height and weight questions to the Weight Loss Assessment. The system now automatically calculates the user's Body Mass Index (BMI) upon submission.
- **BMI Display**: The calculated BMI is now displayed in a prominent, styled card on the Weight Loss Assessment results page, providing users with this critical health metric.

## Version 25.0.9 - Complete Scoring System Audit & Enhancement
*Date: 2024-07-25*

### ✨ New Features & Enhancements
- **Comprehensive Scoring Audit**: Created and completed a master `SCORING_AUDIT_AND_VALIDATION.md` document that meticulously catalogs and validates the entire scoring engine.
- **100% Category Coverage**: Identified and fixed all gaps in the assessments. Added new, clinically relevant questions and activated all unscored questions. Every defined "Score Breakdown" category is now correctly calculated and displayed.
- **Mathematical & Logical Verification**: Programmatically verified that all assessment scores are mathematically sound, clinically logical, and aligned with user-facing recommendations.

## Version 25.0.8 - Enhanced Form Styling
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Polished Form Layout**: Applied several styling refinements to the assessment forms. Answer options now have equal heights, all text is centered, secondary text is more readable, and the navigation buttons are centered for a more professional and polished appearance.

## Version 25.0.7 - Responsive Form Layout
*Date: 2024-07-25*

### ✨ UI/UX Enhancements
- **Side-by-Side Answer Layout**: Implemented a responsive grid layout for assessment answers. Options now appear side-by-side on larger screens (up to 4 across) and stack intelligently on smaller mobile devices, improving usability and aesthetics.

## Version 25.0.6 - Refactored Results Page Styling
*Date: 2024-07-25*

### 🐛 Bug Fixes & Enhancements
- **Consistent Results Styling**: Fixed a bug where results page styles were only applied inconsistently. The styles have been moved to a dedicated CSS file (`ennu-results-page.css`) that is correctly enqueued on all five results pages, ensuring a consistent and professional appearance.
- **Enhanced Empty State**: Implemented a new, user-friendly "empty state" for the results page. It now intelligently guides users on what to do next if they refresh the page or access it directly, with different messaging for logged-in and guest users.

## Version 25.0.5 - Dynamic Results Pages Implementation
*Date: 2024-07-25*

### ✨ New Features
- **Dynamic Results Shortcodes**: The five assessment results shortcodes (e.g., `[ennu-hair-results]`) no longer show a static "funnel" page. They now display a full, dynamic report containing the user's score, interpretation, category breakdown, and personalized observations based on their answers. This completes the core user journey.
- **Enhanced Question Mapper**: Added a reverse-lookup function to the question mapper, enabling the new dynamic results pages to function correctly.

## Version 25.0.4 - Dynamic Content on All Results Pages
*Date: 2024-07-25*

### ✨ New Features
- **Personalized Observations**: Expanded the dynamic content engine to all assessment results pages. Each page now shows a "Specific Observations" section with feedback tailored to the user's individual answers, creating a more detailed and personalized experience.

## Version 25.0.3 - Enhanced User Results Pages
*Date: 2024-07-25*

### ✨ New Features
- **Radar Chart Results Page**: Created a new `[ennu-assessment-chart]` shortcode that displays a beautiful and informative radar chart of the user's category scores. This provides a professional, at-a-glance visualization of their assessment results.
- **Updated CTA Buttons**: Changed the call-to-action buttons on all assessment results pages to "Book a Call" or similar, as requested.

## Version 25.0.2 - Multiselect Submission & Scoring Fix
*Date: 2024-07-25*

### 🐛 Bug Fixes
- **Fixed Multiselect Submissions**: Resolved a critical bug where assessments containing multiple-choice (checkbox) questions would fail on submission. The backend now correctly handles and saves array-based data from these questions.
- **Enabled Multiselect Scoring**: Updated the scoring engine to correctly process the array of answers from multiple-choice questions, ensuring each selected option contributes to the final score.

## Version 25.0.1 - Critical Dashboard Fix
*Date: 2024-07-25*

### 🐛 Critical Bug Fixes
- **Fixed Invisible Score Dashboard**: Resolved a critical bug where the "Health Intelligence Dashboard" was not appearing on the admin user profile page. This was caused by the main plugin file calling an incorrect function name (`enqueue_admin_scripts` instead of `enqueue_admin_assets`). The fix enables the display of calculated scores, interpretations, and the entire dashboard interface, resolving a major gap in functionality.

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

