# ENNU Life Plugin: Maintenance & Extension Guide
**Version:** 55.0.0
**Date:** 2024-07-27
**Author:** The World's Greatest Developer

---

## 1.0 Core Principle: The Single Source of Truth

This plugin operates on a "Configuration over Code" principle. All assessment content—questions, answers, scoring, and metadata—is managed in a single, unified configuration file:

*   `includes/config/assessment-definitions.php`

To perform any maintenance or extension of the assessment content, **this is the primary file you will edit.** The PHP classes are designed as generic "engines" that read from this configuration, meaning you should rarely, if ever, need to modify the PHP class files to add or change assessment content.

---

## 2.0 How to Add a New Assessment

Follow these steps precisely to ensure the new assessment integrates perfectly with all aspects of the system, from the frontend forms to the user dashboard and admin panels.

### Step 1: Define the Assessment Content

1.  Open `includes/config/assessment-definitions.php`.
2.  At the end of the main array, add a new top-level key for your assessment. The key should be descriptive and end with `_assessment` (e.g., `'nutrition_assessment'`).
3.  Define the assessment's metadata and questions within this new array. You must include a `title` and a `gender_filter` if applicable. Each question must have a unique ID within the assessment (e.g., `nutrition_q1`), a `title`, `type`, `options`, and a `scoring` array.

    *Example Structure:*
    ```php
    'nutrition_assessment' => array(
        'title'         => 'Nutrition Assessment',
        'gender_filter' => 'all', // or 'male', 'female'
        'nutrition_q1'  => array(
            'id'       => 'nutrition_q1',
            'title'    => 'How many servings of vegetables do you eat per day?',
            'type'     => 'radio',
            'options'  => array(
                '0-1' => '0-1 servings',
                '2-3' => '2-3 servings',
                '4+'  => '4+ servings',
            ),
            'required' => true,
            'scoring'  => array(
                'category' => 'Dietary Habits',
                'weight'   => 2,
                'answers'  => array(
                    '0-1' => 2,
                    '2-3' => 6,
                    '4+'  => 9,
                ),
            ),
        ),
        // ... more questions
    ),
    ```

### Step 2: Register the Assessment Shortcodes

1.  Open `includes/class-assessment-shortcodes.php`.
2.  **Add the Assessment Form Shortcode**: Locate the `$core_assessments` array inside the `register_shortcodes` method. Add a new entry for your assessment.
    *   *Example:* `'nutrition_assessment' => 'ennu-nutrition-assessment',`
3.  **Add the Results Page Shortcode**: Locate the `$thank_you_shortcodes` array in the same method. Add a new entry for your assessment's results page.
    *   *Example:* `'ennu-nutrition-results' => 'nutrition_assessment',`
4.  **Add the Details Page Shortcode**: Locate the `$details_shortcodes` array. Add a new entry for your assessment's "Health Dossier" page.
    *   *Example:* `'ennu-nutrition-assessment-details' => 'nutrition_assessment',`

### Step 3: Add a Dashboard Icon

1.  While still in `includes/class-assessment-shortcodes.php`, find the `get_user_assessments_data` method.
2.  Locate the `$dashboard_icons` array.
3.  Add a new entry for your assessment, choosing an appropriate emoji icon.
    *   *Example:* `'nutrition_assessment' => '🥗',`

### Step 4: Update the Admin "Automated Setup"

1.  Open `includes/class-enhanced-admin.php`.
2.  Find the `setup_pages` method.
3.  Locate the `$pages_to_create` array. Add three new entries for your assessment's form page, results page, and details page, following the existing structure. This ensures the "Create Assessment Pages" button in the admin settings will work for your new assessment.

    *Example:*
    ```php
    'nutrition-assessment' => array( 'title' => 'Nutrition Assessment', 'content' => '[ennu-nutrition-assessment]' ),
    'nutrition-results' => array( 'title' => 'Nutrition Results', 'content' => '[ennu-nutrition-results]' ),
    'nutrition-assessment-details' => array( 'title' => 'Nutrition Assessment Details', 'content' => '[ennu-nutrition-assessment-details]' ),
    ```

---

## 3.0 Final Step: Verification

After making these changes, navigate to **ENNU Life -> Settings** in the WordPress admin and click the **"Create Assessment Pages"** button. This will generate the necessary pages for your new assessment, and it will be fully integrated into the system.