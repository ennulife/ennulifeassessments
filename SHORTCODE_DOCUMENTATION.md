# ENNU Life Assessment Plugin - Shortcode Documentation

**Version:** 57.2.0
**Author:** ENNU Life Development Team

---

## 1.0 Introduction

This document provides a comprehensive guide to all shortcodes available in the ENNU Life Assessment Plugin. Each shortcode is a building block for creating a seamless and powerful user experience.

---

## 2.0 Assessment Form Shortcodes

These shortcodes are used to render the main, multi-step assessment forms on any page or post. Each form is a complete, self-contained experience.

*   `[ennu-welcome-assessment]`
*   `[ennu-hair-assessment]`
*   `[ennu-ed-treatment-assessment]`
*   `[ennu-skin-assessment]`
*   `[ennu-sleep-assessment]`
*   `[ennu-hormone-assessment]`
*   `[ennu-menopause-assessment]`
*   `[ennu-testosterone-assessment]`
*   `[ennu-weight-loss-assessment]`

---

## 3.0 User Account & Results Shortcodes

These shortcodes render the pages that constitute the core user journey after completing an assessment.

### 3.1 Main User Dashboard

*   `[ennu-user-dashboard]`
    *   **Renders**: The main "Bio-Metric Canvas" user dashboard.
    *   **Behavior**:
        *   If the user is not logged in, it will show a beautifully styled login/registration prompt.
        *   If the user is logged in, it displays a card for each assessment relevant to their gender. Each card shows their latest score and provides links to retake the assessment or view the full "Health Dossier".
        *   The dashboard also renders two historical trend charts: one for the user's **ENNU LIFE SCORE History** and a new one for their **BMI Over Time**.

### 3.2 Post-Assessment Results Pages

After a user submits any assessment, they are redirected to a unique results page. These pages are designed to provide a beautiful, one-time summary of their performance.

*   **Generic Fallback**: `[ennu-assessment-results]`
    *   This shortcode is primarily a fallback. The system will always try to redirect to a specific results page first.

*   **Specific Results Pages**:
    *   `[ennu-hair-results]`
    *   `[ennu-ed-results]`
    *   `[ennu-skin-results]`
    *   `[ennu-sleep-results]`
    *   `[ennu-hormone-results]`
    *   `[ennu-menopause-results]`
    *   `[ennu-testosterone-results]`
    *   `[ennu-weight-loss-results]`
    *   **Behavior**:
        *   These pages are accessed via a secure, one-time-use token in the URL immediately after an assessment is completed.
        *   They display a "Bio-Metric Canvas" style summary of the user's score for the assessment they just took.
        *   They provide three clear next steps: "View Assessment Results" (which links to the permanent "Health Dossier"), "View My ENNU LIFE Dashboard", and "Take Test Again".
        *   If a user tries to access the URL after the token has been used, it will gracefully inform them that the link has expired and direct them to their main dashboard.

---

## 4.0 Detailed Results Page Shortcodes (The Health Dossier)

These shortcodes are used on dedicated pages to display the full, permanent results for a specific assessment. These are the pages linked to from the `[ennu-user-dashboard]` and the post-assessment results summary.

*   `[ennu-hair-assessment-details]`
*   `[ennu-ed-treatment-assessment-details]`
*   `[ennu-skin-assessment-details]`
*   `[ennu-sleep-assessment-details]`
*   `[ennu-hormone-assessment-details]`
*   `[ennu-menopause-assessment-details]`
*   `[ennu-testosterone-assessment-details]`
*   `[ennu-weight-loss-assessment-details]`
    *   **Renders**: A comprehensive, visually rich "Health Dossier" for the specified assessment, styled to match the "Bio-Metric Canvas" aesthetic.
    *   **Behavior**:
        *   Grants access to logged-in users to view their own data at any time.
        *   Also grants temporary, one-time access to guest users who have just completed an assessment and arrive with a valid `results_token`.
        *   Requires a login for any subsequent views.
        *   Displays a historical score timeline, a breakdown of scores by category, and other personalized data visualizations.

