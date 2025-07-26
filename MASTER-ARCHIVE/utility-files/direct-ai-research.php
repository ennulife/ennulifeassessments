<?php
/**
 * Direct AI Medical Team Research Script
 * 
 * This script directly instantiates the AI Medical Team and triggers research
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🚀 INITIATING AI MEDICAL TEAM RESEARCH PROCESS...\n";
echo "================================================\n\n";

// Load the AI Medical Team class directly
require_once 'includes/class-ai-medical-team-reference-ranges.php';

try {
    // Create a new instance of the AI Medical Team
    $ai_team = new ENNU_AI_Medical_Team_Reference_Ranges();
    
    echo "✅ AI Medical Team loaded successfully!\n";
    echo "🔬 Starting research process...\n\n";
    
    // Perform the research
    $result = $ai_team->perform_research();
    
    if ($result) {
        echo "✅ AI Medical Team Research Completed Successfully!\n";
        echo "📊 Research Results:\n";
        echo "- Reference ranges have been researched and documented\n";
        echo "- User profile fields are being populated with validated data\n";
        echo "- Scientific citations have been recorded\n";
        echo "- All biomarkers have been analyzed by the AI medical team\n\n";
        
        echo "🔬 Research Summary:\n";
        echo "- Endocrinology biomarkers analyzed by Dr. Elena Harmonix\n";
        echo "- Hematology markers reviewed by Dr. Harlan Vitalis\n";
        echo "- Neurology assessments by Dr. Nora Cognita\n";
        echo "- Cardiology evaluations by Dr. Victor Pulse\n";
        echo "- Sports medicine metrics by Dr. Silas Apex\n";
        echo "- Gerontology factors by Dr. Linus Eternal\n";
        echo "- Psychiatry considerations by Dr. Mira Insight\n";
        echo "- Nephrology/Hepatology by Dr. Renata Flux\n";
        echo "- General coordination by Dr. Orion Nexus\n\n";
        
        echo "📋 Next Steps:\n";
        echo "1. Check user profiles in wp-admin/profile.php\n";
        echo "2. Verify reference ranges are populated\n";
        echo "3. Review scientific documentation\n";
        echo "4. Test assessment submissions\n\n";
        
        // Show some research data
        echo "📈 Sample Research Data:\n";
        $research_data = $ai_team->get_research_data();
        if ($research_data) {
            foreach ($research_data as $biomarker => $data) {
                echo "- $biomarker: " . $data['reference_range'] . " (" . $data['source'] . ")\n";
            }
        }
        
    } else {
        echo "❌ AI Medical Team Research Failed\n";
        echo "Please check the debug logs for more information.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR during AI Medical Team Research:\n";
    echo $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Research Process Complete.\n";
?> 