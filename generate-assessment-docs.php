<?php
/**
 * Assessment Documentation Generator
 * 
 * This script reads all assessment configuration files and generates
 * comprehensive markdown documentation for each assessment.
 * 
 * @package ENNU_Life
 * @version 62.11.0
 */

// Define the assessments directory
$assessments_dir = __DIR__ . '/includes/config/assessments/';
$docs_dir = __DIR__ . '/docs/assessments/';

// Assessment metadata
$assessment_metadata = array(
    'welcome' => array(
        'title' => 'Welcome Assessment',
        'engine' => 'qualitative',
        'purpose' => 'Foundation data gathering and user onboarding',
        'questions' => 3,
        'gender_filter' => 'All users'
    ),
    'hair' => array(
        'title' => 'Hair Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Comprehensive evaluation of hair health and hair loss concerns',
        'questions' => 10,
        'gender_filter' => 'All users'
    ),
    'ed-treatment' => array(
        'title' => 'ED Treatment Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Evaluation of erectile dysfunction and treatment options',
        'questions' => 12,
        'gender_filter' => 'Male only'
    ),
    'weight-loss' => array(
        'title' => 'Weight Loss Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Comprehensive weight management and metabolic health evaluation',
        'questions' => 13,
        'gender_filter' => 'All users'
    ),
    'health' => array(
        'title' => 'Health Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Holistic health evaluation and wellness assessment',
        'questions' => 11,
        'gender_filter' => 'All users'
    ),
    'skin' => array(
        'title' => 'Skin Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Comprehensive skin health and aesthetic evaluation',
        'questions' => 11,
        'gender_filter' => 'All users'
    ),
    'sleep' => array(
        'title' => 'Sleep Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Sleep quality and pattern evaluation',
        'questions' => 11,
        'gender_filter' => 'All users'
    ),
    'testosterone' => array(
        'title' => 'Testosterone Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Testosterone levels and related symptoms evaluation',
        'questions' => 10,
        'gender_filter' => 'Male only'
    ),
    'hormone' => array(
        'title' => 'Hormone Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Hormonal balance and endocrine health evaluation',
        'questions' => 11,
        'gender_filter' => 'All users'
    ),
    'menopause' => array(
        'title' => 'Menopause Assessment',
        'engine' => 'quantitative',
        'purpose' => 'Menopausal symptoms and hormone transition evaluation',
        'questions' => 10,
        'gender_filter' => 'Female only'
    ),
    'health-optimization' => array(
        'title' => 'Health Optimization Assessment',
        'engine' => 'qualitative',
        'purpose' => 'Symptom-based health optimization evaluation',
        'questions' => 25,
        'gender_filter' => 'All users'
    )
);

/**
 * Generate markdown documentation for an assessment
 */
function generate_assessment_docs($assessment_key, $metadata, $config) {
    $title = $metadata['title'];
    $engine = $metadata['engine'];
    $purpose = $metadata['purpose'];
    $questions = $metadata['questions'];
    $gender_filter = $metadata['gender_filter'];
    
    $content = "# {$title} Documentation\n\n";
    $content .= "**Assessment Type:** {$title}\n";
    $content .= "**Assessment Engine:** " . ucfirst($engine) . "\n";
    $content .= "**Purpose:** {$purpose}\n";
    $content .= "**Version:** 62.11.0\n";
    $content .= "**Total Questions:** {$questions}\n";
    $content .= "**Gender Filter:** {$gender_filter}\n\n";
    
    $content .= "---\n\n";
    
    // Overview section
    $content .= "## Overview\n\n";
    $content .= "The {$title} is designed to " . strtolower($purpose) . ". ";
    
    if ($engine === 'quantitative') {
        $content .= "This assessment uses a comprehensive scoring system to evaluate various factors and provide personalized recommendations.\n\n";
    } else {
        $content .= "This assessment focuses on gathering qualitative data to understand user needs and preferences.\n\n";
    }
    
    $content .= "### Key Characteristics\n";
    $content .= "- **Assessment Type:** " . ucfirst($engine) . " (" . ($engine === 'quantitative' ? 'scored' : 'not scored') . ")\n";
    $content .= "- **Primary Purpose:** " . strtolower($purpose) . "\n";
    $content .= "- **Scoring System:** " . ($engine === 'quantitative' ? 'Point-based with category weighting' : 'Qualitative data collection') . "\n";
    $content .= "- **Gender Filter:** {$gender_filter}\n";
    $content .= "- **Categories:** " . ($engine === 'quantitative' ? 'Multiple distinct scoring categories' : 'Data collection categories') . "\n\n";
    
    $content .= "---\n\n";
    
    // Questions & Answers section
    $content .= "## Questions & Answers\n\n";
    
    if (isset($config['questions'])) {
        $question_num = 1;
        foreach ($config['questions'] as $field_id => $question_config) {
            $content .= "### Question {$question_num}: " . str_replace('_', ' ', ucfirst($field_id)) . "\n";
            $content .= "- **Field ID:** `{$field_id}`\n";
            $content .= "- **Question:** " . $question_config['title'] . "\n";
            $content .= "- **Type:** `" . $question_config['type'] . "` (" . get_type_description($question_config['type']) . ")\n";
            $content .= "- **Required:** " . ($question_config['required'] ? 'Yes' : 'No') . "\n";
            
            if (isset($question_config['global_key'])) {
                $content .= "- **Global Key:** `" . $question_config['global_key'] . "`\n";
            }
            
            if (isset($question_config['options'])) {
                $content .= "- **Answer Options:**\n";
                foreach ($question_config['options'] as $key => $value) {
                    $content .= "  - `{$key}` - {$value}\n";
                }
            }
            
            if (isset($question_config['scoring'])) {
                $content .= "- **Scoring:**\n";
                $content .= "  - **Category:** " . $question_config['scoring']['category'] . "\n";
                $content .= "  - **Weight:** " . $question_config['scoring']['weight'] . "\n";
                if (isset($question_config['scoring']['answers'])) {
                    $content .= "  - **Answers:**\n";
                    foreach ($question_config['scoring']['answers'] as $key => $value) {
                        $content .= "    - `{$key}` - {$value} points\n";
                    }
                }
            }
            
            $content .= "- **Description:** " . get_question_description($field_id, $question_config) . "\n\n";
            $question_num++;
        }
    }
    
    $content .= "---\n\n";
    
    // Data Storage section
    $content .= "## Data Storage\n\n";
    $content .= "### Assessment-Specific Meta Keys\n";
    $content .= "- `ennu_{$assessment_key}_q1` through `ennu_{$assessment_key}_q{$questions}` - Individual question responses\n";
    $content .= "- `ennu_{$assessment_key}_overall_score` - Overall assessment score\n";
    $content .= "- `ennu_{$assessment_key}_category_scores` - Category-specific scores\n";
    $content .= "- `ennu_{$assessment_key}_historical_scores` - Historical assessment data\n\n";
    
    $content .= "### Global Data Integration\n";
    $content .= "- Date of birth and gender are stored globally\n";
    $content .= "- Assessment results influence personalized recommendations\n";
    $content .= "- Data is used for treatment planning and progress tracking\n\n";
    
    $content .= "---\n\n";
    
    // Technical Implementation section
    $content .= "## Technical Implementation\n\n";
    $content .= "### Assessment Configuration\n";
    $content .= "```php\n";
    $content .= "'title'             => '{$title}',\n";
    $content .= "'assessment_engine' => '{$engine}',\n";
    $content .= "'questions'         => array(\n";
    $content .= "    // {$questions} comprehensive questions...\n";
    $content .= ")\n";
    $content .= "```\n\n";
    
    if ($engine === 'quantitative') {
        $content .= "### Scoring Algorithm\n";
        $content .= "- Weighted category scoring\n";
        $content .= "- Overall score calculation\n";
        $content .= "- Category-specific recommendations\n";
        $content .= "- Historical trend analysis\n\n";
    }
    
    $content .= "### Validation Rules\n";
    $content .= "- All fields are required\n";
    $content .= "- Valid date of birth\n";
    $content .= "- Appropriate gender selection\n";
    $content .= "- Multiple selections allowed where applicable\n\n";
    
    $content .= "---\n\n";
    
    // User Experience Flow section
    $content .= "## User Experience Flow\n\n";
    $content .= "1. **Introduction:** Overview of {$title} purpose\n";
    $content .= "2. **Demographics:** Age and gender collection\n";
    $content .= "3. **Assessment Questions:** Sequential completion of {$questions} questions\n";
    $content .= "4. **Data Processing:** " . ($engine === 'quantitative' ? 'Scoring and analysis' : 'Data collection and analysis') . "\n";
    $content .= "5. **Results:** Personalized " . ($engine === 'quantitative' ? 'score and ' : '') . "recommendations\n\n";
    
    $content .= "---\n\n";
    
    // Integration Points section
    $content .= "## Integration Points\n\n";
    $content .= "### Assessment Availability\n";
    $content .= "- Available to {$gender_filter}\n";
    $content .= "- Age-appropriate modifications\n";
    $content .= "- Goal-aligned recommendations\n\n";
    
    $content .= "### Data Synchronization\n";
    $content .= "- Global data integration\n";
    $content .= "- Cross-assessment correlations\n";
    $content .= "- Treatment history tracking\n\n";
    
    $content .= "### Recommendation Engine\n";
    $content .= "- Personalized treatment plans\n";
    $content .= "- Product recommendations\n";
    $content .= "- Specialist referrals\n\n";
    
    $content .= "---\n\n";
    
    // Future Enhancements section
    $content .= "## Future Enhancements\n\n";
    $content .= "### Potential Additions\n";
    $content .= "- Enhanced assessment questions\n";
    $content .= "- Advanced scoring algorithms\n";
    $content .= "- Integration with external data sources\n";
    $content .= "- Real-time progress tracking\n";
    $content .= "- Community support features\n\n";
    
    $content .= "### Optimization Opportunities\n";
    $content .= "- Streamlined user experience\n";
    $content .= "- Enhanced data visualization\n";
    $content .= "- Mobile optimization\n";
    $content .= "- Advanced analytics dashboard\n\n";
    
    $content .= "---\n\n";
    
    // Related Documentation section
    $content .= "## Related Documentation\n\n";
    $content .= "- [Assessment Configuration Guide](../config/assessment-configuration.md)\n";
    $content .= "- [Data Management](../data/data-management.md)\n";
    $content .= "- [User Experience Flow](../user-experience/flow.md)\n";
    $content .= "- [Security & Privacy](../security/privacy.md)\n";
    
    return $content;
}

/**
 * Get type description
 */
function get_type_description($type) {
    $descriptions = array(
        'radio' => 'Single selection',
        'multiselect' => 'Multiple selection allowed',
        'dob_dropdowns' => 'Date of birth dropdown selectors',
        'text' => 'Text input',
        'textarea' => 'Multi-line text input',
        'select' => 'Dropdown selection'
    );
    
    return isset($descriptions[$type]) ? $descriptions[$type] : 'Custom input type';
}

/**
 * Get question description
 */
function get_question_description($field_id, $config) {
    $descriptions = array(
        'gender' => 'Determines gender-specific assessment availability and recommendations.',
        'date_of_birth' => 'Collects age information for age-appropriate evaluations.',
        'health_goals' => 'Identifies primary health objectives to guide personalized recommendations.',
        'hair_concerns' => 'Identifies specific hair health issues and their severity.',
        'timeline' => 'Determines the progression timeline of health concerns.',
        'family_history' => 'Evaluates genetic predisposition to health conditions.',
        'stress_level' => 'Assesses stress impact on health and wellness.',
        'diet_quality' => 'Evaluates nutritional support for health goals.',
        'exercise_frequency' => 'Assesses physical activity levels and fitness.',
        'sleep_quality' => 'Evaluates sleep patterns and quality.',
        'current_health' => 'Assesses overall health status and concerns.',
        'treatment_history' => 'Evaluates previous treatment experiences and effectiveness.',
        'lifestyle_factors' => 'Identifies modifiable risk factors and behaviors.',
        'psychological_impact' => 'Evaluates emotional and mental health impact.',
        'symptoms' => 'Identifies specific symptoms and their severity.'
    );
    
    // Extract key from field_id
    $key = str_replace(array('_q1', '_q2', '_q3', '_q4', '_q5', '_q6', '_q7', '_q8', '_q9', '_q10', '_q11', '_q12', '_q13'), '', $field_id);
    $key = str_replace(array('welcome_', 'hair_', 'ed_', 'weight_loss_', 'health_', 'skin_', 'sleep_', 'testosterone_', 'hormone_', 'menopause_', 'health_optimization_'), '', $key);
    
    return isset($descriptions[$key]) ? $descriptions[$key] : 'Collects relevant information for assessment evaluation.';
}

// Process each assessment
foreach ($assessment_metadata as $assessment_key => $metadata) {
    $config_file = $assessments_dir . $assessment_key . '.php';
    
    if (file_exists($config_file)) {
        $config = include $config_file;
        
        $docs_file = $docs_dir . $assessment_key . '/README.md';
        
        // Create directory if it doesn't exist
        $docs_dir_path = dirname($docs_file);
        if (!is_dir($docs_dir_path)) {
            mkdir($docs_dir_path, 0755, true);
        }
        
        $content = generate_assessment_docs($assessment_key, $metadata, $config);
        
        file_put_contents($docs_file, $content);
        
        echo "Generated documentation for {$metadata['title']}\n";
    } else {
        echo "Warning: Config file not found for {$assessment_key}\n";
    }
}

echo "\nDocumentation generation complete!\n";
echo "All assessment documentation has been created in: {$docs_dir}\n";
?> 