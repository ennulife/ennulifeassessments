# ENNU Life Assessment Plugin - Shortcode Documentation

**Version**: 24.12.3

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

These are the primary shortcodes that display the assessment forms. At present, these shortcodes do not support any additional attributes.

### 1. Welcome Assessment
```
[ennu-welcome-assessment]
```
**Purpose**: Initial welcome assessment for new users.

### 2. Hair Health Assessment
```
[ennu-hair-assessment]
```
**Purpose**: Comprehensive hair health evaluation.

### 3. ED Treatment Assessment
```
[ennu-ed-treatment-assessment]
```
**Purpose**: Erectile dysfunction treatment evaluation.

### 4. Weight Loss Assessment
```
[ennu-weight-loss-assessment]
```
**Purpose**: Weight management and fitness evaluation.

### 5. Health Assessment
```
[ennu-health-assessment]
```
**Purpose**: General health and wellness evaluation.

### 6. Skin Assessment
```
[ennu-skin-assessment]
```
**Purpose**: Skin health and skincare evaluation.

---

## Usage Examples

### Basic Usage
Place any shortcode directly in a page or post using the WordPress editor.
```
[ennu-health-assessment]
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

