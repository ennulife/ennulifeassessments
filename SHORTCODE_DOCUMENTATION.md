# ENNU Life Assessment Plugin - Shortcode Documentation

This document provides comprehensive documentation for all shortcodes available in the ENNU Life Assessment Plugin v24.1.0.

## Table of Contents
- [Overview](#overview)
- [Core Assessment Shortcodes](#core-assessment-shortcodes)
- [Results and Thank You Shortcodes](#results-and-thank-you-shortcodes)
- [Shortcode Attributes](#shortcode-attributes)
- [Usage Examples](#usage-examples)
- [Styling and Customization](#styling-and-customization)
- [Troubleshooting](#troubleshooting)

---

## Overview

The ENNU Life Assessment Plugin provides a comprehensive set of shortcodes for displaying health and wellness assessments on your WordPress site. All shortcodes are designed to be mobile-responsive, accessible, and fully integrated with the plugin's data management system.

### Key Features
- **Multi-step forms** with smooth navigation
- **Auto-advance functionality** for radio button selections
- **Progress tracking** with visual progress bars
- **Global user data** persistence across assessments
- **Modern neutral grey** color scheme
- **Mobile-first responsive** design
- **WCAG 2.1 accessibility** compliance

---

## Core Assessment Shortcodes

These are the primary assessment shortcodes that display interactive multi-step forms.

### 1. Welcome Assessment
```
[ennu-welcome-assessment]
```
**Purpose**: Initial welcome assessment for new users  
**Questions**: General health and wellness overview  
**Use Case**: Landing pages, welcome flows, initial user onboarding

### 2. Hair Health Assessment
```
[ennu-hair-assessment]
```
**Purpose**: Comprehensive hair health evaluation  
**Questions**: Hair type, concerns, treatments, goals  
**Use Case**: Hair care product recommendations, treatment planning

### 3. ED Treatment Assessment
```
[ennu-ed-treatment-assessment]
```
**Purpose**: Erectile dysfunction treatment evaluation  
**Questions**: Symptoms, severity, medical history, preferences  
**Use Case**: Treatment recommendation, medical consultation preparation

### 4. Weight Loss Assessment
```
[ennu-weight-loss-assessment]
```
**Purpose**: Weight management and fitness evaluation  
**Questions**: Current weight, goals, lifestyle, dietary preferences  
**Use Case**: Weight loss program recommendations, fitness planning

### 5. Health Assessment
```
[ennu-health-assessment]
```
**Purpose**: General health and wellness evaluation  
**Questions**: Overall health, symptoms, lifestyle, medical history  
**Use Case**: General health recommendations, wellness planning

### 6. Skin Assessment
```
[ennu-skin-assessment]
```
**Purpose**: Skin health and skincare evaluation  
**Questions**: Skin type, concerns, current routine, goals  
**Use Case**: Skincare product recommendations, treatment planning

---

## Results and Thank You Shortcodes

These shortcodes display assessment results and thank you pages after form completion.

### General Results Page
```
[ennu-assessment-results]
```
**Purpose**: Display comprehensive assessment results  
**Features**: 
- Shows completed assessments
- Displays user profile data
- Provides recommendations
- Links to relevant products/services

### Individual Thank You Pages

#### Hair Assessment Results
```
[ennu-hair-results]
```
**Purpose**: Hair-specific results and recommendations

#### ED Treatment Results
```
[ennu-ed-results]
```
**Purpose**: ED treatment-specific results and next steps

#### Weight Loss Results
```
[ennu-weight-loss-results]
```
**Purpose**: Weight loss-specific results and program recommendations

#### Health Assessment Results
```
[ennu-health-results]
```
**Purpose**: General health results and wellness recommendations

#### Skin Assessment Results
```
[ennu-skin-results]
```
**Purpose**: Skin-specific results and product recommendations

---

## Shortcode Attributes

All assessment shortcodes support the following optional attributes:

### Common Attributes

#### `theme`
Controls the visual theme of the assessment form.
```
[ennu-health-assessment theme="light"]
[ennu-health-assessment theme="dark"]
[ennu-health-assessment theme="neutral"] (default)
```

#### `redirect_url`
Custom URL to redirect to after assessment completion.
```
[ennu-health-assessment redirect_url="/thank-you-health/"]
```

#### `show_progress`
Controls whether to display the progress bar.
```
[ennu-health-assessment show_progress="true"] (default)
[ennu-health-assessment show_progress="false"]
```

#### `auto_advance`
Controls auto-advance functionality for radio buttons.
```
[ennu-health-assessment auto_advance="true"] (default)
[ennu-health-assessment auto_advance="false"]
```

#### `save_data`
Controls whether to save assessment data to user profile.
```
[ennu-health-assessment save_data="true"] (default)
[ennu-health-assessment save_data="false"]
```

#### `require_login`
Controls whether user must be logged in to take assessment.
```
[ennu-health-assessment require_login="true"]
[ennu-health-assessment require_login="false"] (default)
```

---

## Usage Examples

### Basic Usage
Place any shortcode directly in a page or post:
```
[ennu-health-assessment]
```

### Advanced Usage with Attributes
```
[ennu-weight-loss-assessment 
    theme="light" 
    redirect_url="/weight-loss-results/" 
    show_progress="true" 
    auto_advance="true" 
    require_login="true"]
```

### In PHP Templates
```php
echo do_shortcode('[ennu-hair-assessment]');
```

### With WordPress Blocks
In the WordPress block editor, use the "Shortcode" block and enter:
```
[ennu-skin-assessment]
```

### Multiple Assessments on One Page
```
<div class="assessment-section">
    <h2>Start Your Health Journey</h2>
    [ennu-welcome-assessment]
</div>

<div class="assessment-section">
    <h2>Detailed Health Assessment</h2>
    [ennu-health-assessment]
</div>
```

---

## Styling and Customization

### CSS Classes
All assessment forms use consistent CSS classes for easy customization:

#### Main Container
```css
.ennu-assessment-form {
    /* Main form container */
}
```

#### Progress Bar
```css
.progress-container {
    /* Progress bar container */
}
.progress-bar {
    /* Progress bar background */
}
.progress-fill {
    /* Progress bar fill */
}
```

#### Questions
```css
.question-slide {
    /* Individual question container */
}
.question-title {
    /* Question title styling */
}
.answer-options {
    /* Answer options container */
}
.answer-option {
    /* Individual answer option */
}
```

#### Navigation
```css
.navigation-buttons {
    /* Navigation button container */
}
.nav-button {
    /* Navigation buttons */
}
.nav-button.prev {
    /* Previous button */
}
.nav-button.next {
    /* Next button */
}
```

### Custom CSS Example
```css
/* Custom styling for assessments */
.ennu-assessment-form {
    max-width: 900px;
    margin: 40px auto;
    padding: 50px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.question-title {
    color: #2c3e50;
    font-size: 32px;
    text-align: center;
    margin-bottom: 40px;
}

.answer-option input[type="radio"]:checked + label {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}
```

---

## Data Storage

### Global User Fields
These fields are saved once and persist across all assessments:
- `ennu_global_first_name`
- `ennu_global_last_name`
- `ennu_global_email`
- `ennu_global_billing_phone`
- `ennu_global_calculated_age`
- `ennu_global_gender`
- `ennu_global_dob_month`
- `ennu_global_dob_day`
- `ennu_global_dob_year`

### Assessment-Specific Fields
Each assessment saves data with its own prefix:
- Health Assessment: `ennu_health_assessment_*`
- Weight Loss: `ennu_weight_assessment_*`
- Hair Assessment: `ennu_hair_assessment_*`
- ED Treatment: `ennu_ed_treatment_assessment_*`
- Skin Assessment: `ennu_skin_assessment_*`

### Completion Tracking
- `ennu_[assessment_type]_completed`: "yes" when completed
- `ennu_[assessment_type]_completed_date`: Completion timestamp

---

## Integration with WordPress

### User Profile Integration
Assessment data automatically appears in WordPress user profiles under "ENNU Assessment Data" sections.

### WooCommerce Integration
Assessment results can trigger:
- Product recommendations
- Targeted email campaigns
- Custom pricing
- Personalized checkout experiences

### HubSpot Integration
Assessment data syncs with HubSpot for:
- Lead scoring
- Automated workflows
- Personalized marketing
- Sales team notifications

---

## Troubleshooting

### Common Issues

#### Shortcode Not Displaying
**Problem**: Shortcode appears as text instead of form  
**Solution**: 
1. Verify plugin is activated
2. Check for PHP errors in WordPress debug log
3. Ensure shortcode name is spelled correctly

#### Form Not Saving Data
**Problem**: Assessment data not appearing in user profile  
**Solution**:
1. Check WordPress error logs for ENNU entries
2. Verify user is logged in (if required)
3. Test with different user account
4. Check database permissions

#### Styling Issues
**Problem**: Form doesn't look right  
**Solution**:
1. Clear any caching plugins
2. Check for CSS conflicts with theme
3. Verify frontend CSS file is loading
4. Use browser developer tools to inspect elements

#### Mobile Display Problems
**Problem**: Form not responsive on mobile  
**Solution**:
1. Ensure viewport meta tag is present
2. Check for CSS conflicts
3. Test on actual mobile devices
4. Verify touch events are working

### Debug Mode
Enable WordPress debug mode to see detailed error messages:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Getting Help
1. Check WordPress error logs
2. Review plugin documentation
3. Contact ENNU Life support team
4. Provide specific error messages and steps to reproduce

---

## Version History

### v24.1.0 (Current)
- Complete shortcode system overhaul
- Added neutral grey color scheme
- Enhanced mobile responsiveness
- Improved accessibility features
- Fixed data logging issues

### v22.8 (Previous)
- Basic shortcode functionality
- Purple color scheme
- Limited mobile support

---

## License

This plugin and its shortcodes are proprietary software developed for ENNU Life. All rights reserved.

For technical support or customization requests, please contact the ENNU Life development team.

