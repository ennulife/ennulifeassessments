# ENNU Life Assessment Plugin - Shortcode Documentation

**Version**: 26.0.54

This document provides documentation for all available shortcodes in the ENNU Life Assessment Plugin.

## Table of Contents
- [Overview](#overview)
- [Available Shortcodes](#available-shortcodes)
- [Usage Examples](#usage-examples)
- [Styling and Customization](#styling-and-customization)

---

## Overview

The ENNU Life Assessment Plugin uses shortcodes to display the various health and wellness assessments on your WordPress site. Simply place the desired shortcode on any page or post to render the interactive, multi-step form.

### Key Features
- **Multi-step forms** with smooth navigation and a progress bar.
- **Auto-advance functionality** for single-choice questions.
- **Data Persistence**: All responses are saved to the user's WordPress profile. For guest users, an account is created automatically.
- **Global Field Sync**: Core user information (name, email, etc.) is synchronized across all assessments for a seamless user experience.
- **Modern & Responsive Design**.

---

## Available Shortcodes

These are the primary shortcodes that display the assessment forms.

### Assessment Shortcodes

The following shortcodes are used to display the assessment forms. They all support the same set of attributes.

*   `[ennu-welcome-assessment]`
*   `[ennu-hair-assessment]`
*   `[ennu-ed-treatment-assessment]`
*   `[ennu-weight-loss-assessment]`
*   `[ennu-health-assessment]`
*   `[ennu-skin-assessment]`

**Attributes:**

*   `theme`: (string) The theme to use for the assessment. Currently, only `default` is supported.
*   `show_progress`: (string) Whether to show the progress bar. Can be `true` or `false`. Default is `true`.
*   `auto_advance`: (string) Whether to automatically advance to the next question after a selection is made. Can be `true` or `false`. Default is `true`.

### Results and Dashboard Shortcodes

*   `[ennu-assessment-results]`: Displays the results of the most recently completed assessment.
*   `[ennu-assessment-chart]`: Displays a chart of the results for a specific assessment.
*   `[ennu-user-dashboard]`: Displays the user's dashboard with an overview of their completed assessments.
*   `[ennu-hair-assessment-details]`: Displays a detailed breakdown of the hair assessment results. Similar shortcodes exist for the other assessments (e.g., `[ennu-ed-treatment-assessment-details]`, etc.).

---

## Usage Examples

### Basic Usage

Place any shortcode directly in a page or post using the WordPress editor.
```
[ennu-health-assessment]
```

### With Attributes

You can use attributes to customize the behavior of the assessment shortcodes.
```
[ennu-hair-assessment show_progress="false" auto_advance="false"]
```

### In PHP Templates

You can also render shortcodes directly in your theme's template files.
```php
echo do_shortcode('[ennu-hair-assessment]');
```

---

## Styling and Customization

All assessment forms use consistent CSS classes for easy customization. You can add custom CSS to your theme's `style.css` file to override the default styles.

#### Main Container
- `.ennu-assessment-form`

#### Questions & Answers
- `.question-slide`
- `.question-title`
- `.answer-options`
- `.answer-option`

#### Navigation
- `.navigation-buttons`
- `.nav-button`

---
For support, please refer to the main plugin documentation or contact the ENNU Life development team.

