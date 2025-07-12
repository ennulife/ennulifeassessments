# ENNU Life Assessment Plugin - Comprehensive User Experience Documentation

**Version**: 24.12.3
**Author:** ENNU Life Development Team
**Date:** July 24, 2024

---

## 1. Overview

This document provides a complete and accurate overview of the user experience for the ENNU Life Assessment Plugin. It details the journey for both logged-in and guest users, the functionality of the forms, and the data that is displayed to administrators in the WordPress user profile.

---

## 2. User Journeys

### A. Logged-In User Experience

Authenticated WordPress users receive a seamless, personalized experience.

*   **Global Field Pre-population**: When a logged-in user starts any assessment, all global fields (First Name, Last Name, Email, Phone, Date of Birth, Gender) that have been previously saved are automatically pre-filled in the form.
*   **Data Persistence**: All answers are saved directly to the user's WordPress profile upon submission.

### B. Guest (Logged-Out) User Experience

Guest users can fully complete assessments, with a focus on lead capture and account creation.

*   **Full Assessment Access**: Guests can access and complete all questions in any assessment.
*   **Mandatory Account Creation**: Upon submitting an assessment, an account is **automatically and mandatorily created** for the user using the email address they provide. There is no option to opt-out.
*   **Existing User Check**: If a guest provides an email address that already belongs to an existing user, the submission is halted, and they are shown a message prompting them to log in to continue.

---

## 3. The Assessment Form Journey

This describes the step-by-step experience of taking an assessment.

*   **Phase 1: Starting the Assessment**: The form displays the assessment title and a real-time progress bar (e.g., "Question 1 of 11").
*   **Phase 2: Answering Questions**: Questions are presented one at a time.
    *   **Single-Choice Questions**: Selecting a radio button automatically advances the user to the next question.
    *   **Multi-Select Questions**: Users can select multiple checkboxes.
    *   **Navigation**: "Next" and "Previous" buttons allow for manual navigation.
*   **Phase 3: Submission & Redirect**: Upon completing the final question, the data is submitted via AJAX. On success, the user is redirected to the appropriate "Thank You" or "Results" page for that assessment.

---

## 4. Admin User Profile View

When an administrator views a user's profile, they have access to a detailed and organized breakdown of all their assessment data.

### Section 1: Global User Data

This section displays all the global fields that are shared across assessments.

*   **Fields Displayed**: First Name, Last Name, Email, Phone Number, Date of Birth, Gender.
*   **Data Sources**: The display correctly indicates whether the data is sourced from the native WordPress user object (for name/email) or from user meta.

### Section 2: Assessment Scores

For each assessment the user has completed, a prominent block displays:

*   The final calculated score.
*   The text-based interpretation of that score (e.g., "Excellent", "Good").

### Section 3: Individual Assessment Answers

For each assessment, a separate section displays the questions and the user's saved answers.

*   **Interactive Fields**: The questions are rendered as editable form fields (radio buttons, checkboxes, text fields).
*   **Pre-selected Answers**: The user's saved answer is correctly pre-selected.
*   **Admin Editing**: Administrators can change the user's answers and click "Update Profile" to save the changes.
*   **Field ID Reference**: The simple field ID (e.g., `health_q1`) is displayed beneath each question for easy reference.

---

**NOTE ON ADVANCED ANALYTICS:** Previous versions of this document described extensive "Hidden System Fields" for tracking analytics (UTM tags, IP addresses, etc.). This functionality is **not implemented** in the current version of the plugin. The code for capturing and displaying these fields has been removed to focus on the core user experience.

