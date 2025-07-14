# ENNU Life Assessment Plugin - Shortcode Documentation

**Version**: 44.0.2

This document provides documentation for all available shortcodes in the ENNU Life Assessment Plugin.

## Table of Contents
- [Overview](#overview)
- [Assessment Shortcodes](#assessment-shortcodes)
- [Dashboard & Results Shortcodes](#dashboard--results-shortcodes)
- [Usage Examples](#usage-examples)

---

## Overview

The ENNU Life Assessment Plugin uses shortcodes to display health and wellness assessments on your WordPress site. Simply place the desired shortcode on any page or post.

---

## Assessment Shortcodes

These shortcodes render the interactive, multi-step assessment forms. They all support the same set of attributes.

*   `[ennu-welcome-assessment]`
*   `[ennu-hair-assessment]`
*   `[ennu-ed-treatment-assessment]`
*   `[ennu-weight-loss-assessment]`
*   `[ennu-health-assessment]`
*   `[ennu-skin-assessment]`
*   `[ennu-sleep-assessment]`
*   `[ennu-hormone-assessment]`
*   `[ennu-menopause-assessment]`
*   `[ennu-testosterone-assessment]`

**Attributes:**
*   `show_progress="false"`: (Optional) Hides the progress bar.
*   `auto_advance="false"`: (Optional) Disables advancing to the next question automatically after a selection is made.

---

## Dashboard & Results Shortcodes

### The Executive Wellness Interface
*   `[ennu-user-dashboard]`
    *   **Description**: Renders the new "Executive Wellness Interface," a premium, jaw-dropping dashboard that is the central hub for the user's health journey.
    *   **Features**:
        *   An animated radial progress bar for the master **ENNU LIFE SCORE**.
        *   Elegant progress bars for the four **Pillar Scores**, with insightful tooltips.
        *   A historical line chart to visualize the user's progress over time.
        *   A list of all assessments, with expandable sections to view detailed category scores.
    *   **Note**: This shortcode should be placed on a dedicated "Dashboard" page.

### Assessment Results Pages
*   `[ennu-assessment-results]`: A generic results page that displays a summary of the most recently completed assessment.
*   `[ennu-hair-results]`, `[ennu-skin-results]`, etc.: Each assessment has a corresponding results shortcode for creating dedicated "Thank You" or results pages.

### Detailed Report Pages
*   `[ennu-hair-assessment-details]`, `[ennu-skin-assessment-details]`, etc.: Each assessment has a corresponding "details" shortcode that renders a full, in-depth report page, including historical data and visualizations. These are the pages linked to from the "View Full Report" buttons on the main dashboard.

---

## Usage Examples

### Basic Usage
Place any shortcode directly in a page or post.
```
[ennu-health-assessment]
```

### With Attributes
Customize the behavior of an assessment.
```
[ennu-hair-assessment show_progress="false"]
```

### In PHP Templates
Render shortcodes directly in your theme's template files.
```php
echo do_shortcode('[ennu-user-dashboard]');
```

