# ENNU Life Assessments - Refactoring & Maintenance Guide

## Overview

This guide provides comprehensive instructions for maintaining and extending the ENNU Life Assessments plugin. It covers refactoring procedures, adding new assessments, and maintaining the dynamic page linking system.

## 1.0 Dynamic Page Linking System

### 1.1 Overview

The plugin now implements a dynamic page linking system that pulls directly from admin page settings for:
- **History Buttons**: Link to assessment details pages
- **Expert Buttons**: Link to assessment-specific consultation pages

### 1.2 Key Methods

#### `get_assessment_details_page_id($assessment_key)`
Retrieves the details page ID for an assessment from admin settings.

**Usage:**
```php
$details_page_id = $shortcode_instance->get_assessment_details_page_id('ed_treatment_assessment');
if ($details_page_id) {
    $url = home_url("/?page_id={$details_page_id}");
}
```

#### `get_assessment_consultation_page_id($assessment_key)`
Retrieves the consultation page ID for an assessment from admin settings.

**Usage:**
```php
$consultation_page_id = $shortcode_instance->get_assessment_consultation_page_id('ed_treatment_assessment');
if ($consultation_page_id) {
    $url = home_url("/?page_id={$consultation_page_id}");
}
```

### 1.3 Admin Page Mapping

The system uses the `ennu_created_pages` option to store page mappings:

```php
$page_mappings = get_option('ennu_created_pages', array());
// Example mappings:
// 'assessments/ed-treatment/details' => 2763
// 'assessments/ed-treatment/consultation' => 2765
// 'assessments/hair/details' => 2771
// 'assessments/hair/consultation' => 2773
```

### 1.4 Fallback System

Both methods include comprehensive fallback systems:
1. Try complex format: `assessments/{slug}/details` or `assessments/{slug}/consultation`
2. Try simple format: `{slug}-details` or `{slug}-consultation`
3. Direct page lookup by path
4. Generic fallback (call page for consultations)

## 2.0 Adding New Assessments

### Step 1: Create Assessment Definition

1.  Open `includes/class-assessment-shortcodes.php`.
2.  Find the `$assessments` array in the constructor.
3.  Add a new entry for your assessment, choosing an appropriate emoji icon.
    *   *Example:* `'nutrition_assessment' => '🥗',`

### Step 2: Update the Admin "Automated Setup"

1.  Open `includes/class-enhanced-admin.php`.
2.  Find the `setup_pages` method.
3.  Locate the `$pages_to_create` array. Add three new entries for your assessment's form page, results page, and details page, following the existing structure. This ensures the "Create Assessment Pages" button in the admin settings will work for your new assessment.

    *Example:*
    ```php
    'nutrition-assessment' => array( 'title' => 'Nutrition Assessment', 'content' => '[ennu-nutrition-assessment]' ),
    'nutrition-results' => array( 'title' => 'Nutrition Results', 'content' => '[ennu-nutrition-results]' ),
    'nutrition-assessment-details' => array( 'title' => 'Nutrition Assessment Details', 'content' => '[ennu-nutrition-assessment-details]' ),
    ```

### Step 3: Update Page Slug Mapping

1.  Open `includes/class-assessment-shortcodes.php`.
2.  Find the `get_assessment_page_slug()` method.
3.  Add a mapping for your assessment key to slug.
    *   *Example:* `'nutrition_assessment' => 'nutrition',`

### Step 4: Update Dynamic Page Linking

The new assessment will automatically work with the dynamic page linking system. The `get_assessment_details_page_id()` and `get_assessment_consultation_page_id()` methods will automatically detect and use the correct page IDs from admin settings.

## 3.0 Final Step: Verification

After making these changes, navigate to **ENNU Life -> Settings** in the WordPress admin and click the **"Create Assessment Pages"** button. This will generate the necessary pages for your new assessment, and it will be fully integrated into the system.

## 4.0 Maintenance Procedures

### 4.1 Updating Page Mappings

When you change page assignments in the admin:
1. The History buttons will automatically use the new details page
2. The Expert buttons will automatically use the new consultation page
3. No code changes are required

### 4.2 Troubleshooting Page Links

If page links aren't working:
1. Check the `ennu_created_pages` option in the database
2. Verify page IDs exist and are published
3. Use the fallback system which will use generic pages if specific ones aren't found

### 4.3 Testing Dynamic Links

Test the dynamic linking system:
```php
$shortcode = new ENNU_Assessment_Shortcodes();
$details_id = $shortcode->get_assessment_details_page_id('ed_treatment_assessment');
$consultation_id = $shortcode->get_assessment_consultation_page_id('ed_treatment_assessment');
echo "Details Page ID: $details_id\n";
echo "Consultation Page ID: $consultation_id\n";
```

## 5.0 Best Practices

1. **Always use admin settings**: Configure page mappings through the admin interface
2. **Test fallbacks**: Ensure the system works even if specific pages aren't configured
3. **Document changes**: Update this guide when adding new assessments
4. **Version control**: Update plugin version and changelog for all changes