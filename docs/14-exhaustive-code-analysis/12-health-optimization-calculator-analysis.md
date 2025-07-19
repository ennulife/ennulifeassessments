# Health Optimization Calculator Analysis

**File**: `includes/class-health-optimization-calculator.php`  
**Version**: 60.0.0 (vs main plugin 62.2.6)  
**Lines**: 166  
**Class**: `ENNU_Health_Optimization_Calculator`

## File Overview

This class implements the Health Optimization feature, calculating Pillar Integrity Penalties and biomarker recommendations based on user symptom data. It processes symptom-to-vector mappings, applies penalty matrices, and generates personalized biomarker recommendations.

## Line-by-Line Analysis

### File Header and Security (Lines 1-13)
```php
<?php
/**
 * ENNU Life Health Optimization Calculator
 *
 * This class is responsible for all calculations related to the Health Optimization feature,
 * including Pillar Integrity Penalties and biomarker recommendations.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

**Analysis**:
- **Version Inconsistency**: Class version (60.0.0) doesn't match main plugin (62.2.6)
- **Security**: Proper ABSPATH check prevents direct file access
- **Documentation**: Clear description of health optimization functionality

### Class Definition and Properties (Lines 15-21)
```php
class ENNU_Health_Optimization_Calculator {

    private $user_id;
    private $mappings;
    private $all_definitions;
```

**Analysis**:
- **Instance-Based Design**: Uses dependency injection pattern
- **Encapsulation**: Private properties properly encapsulate data
- **Dependencies**: Requires user ID, assessment definitions, and mappings

### Constructor (Lines 23-28)
```php
public function __construct( $user_id, $all_definitions ) {
    $this->user_id = $user_id;
    $this->all_definitions = $all_definitions;
    $this->mappings = $this->get_health_optimization_mappings();
    error_log("HealthOptimizationCalculator: Instantiated for user ID {$user_id}.");
}
```

**Analysis**:
- **Dependency Injection**: Properly accepts required dependencies
- **Dynamic Mapping Loading**: Loads mappings in constructor
- **User-Specific Logging**: Logs user ID for tracking
- **Data Assignment**: Direct property assignment without validation

### Pillar Penalty Calculation (Lines 30-75)
```php
public function calculate_pillar_penalties() {
    error_log("HealthOptimizationCalculator: Starting pillar penalty calculation.");
    $triggered_vectors = $this->get_triggered_vectors();
    $penalty_matrix = $this->mappings['pillar_integrity_penalty_matrix'] ?? array();
    
    $pillar_penalties = array( 'mind' => 0, 'body' => 0, 'lifestyle' => 0, 'aesthetics' => 0 );

    foreach($triggered_vectors as $vector => $vector_data) {
        error_log("HealthOptimizationCalculator: Processing triggered vector '{$vector}'.");
        if (isset($penalty_matrix[$vector])) {
            $vector_config = $penalty_matrix[$vector];
            $pillar_impact = $vector_config['pillar_impact'];
            
			$highest_impact_instance = null;
			foreach($vector_data['instances'] as $instance) {
				if ($highest_impact_instance === null) {
					$highest_impact_instance = $instance;
				} else {
                    $severity_map = ['Severe' => 3, 'Moderate' => 2, 'Mild' => 1];
                    $frequency_map = ['Daily' => 3, 'Weekly' => 2, 'Monthly' => 1];

                    if (($severity_map[$instance['severity']] ?? 0) > ($severity_map[$highest_impact_instance['severity']] ?? 0)) {
                        $highest_impact_instance = $instance;
                    } elseif (($frequency_map[$instance['frequency']] ?? 0) > ($frequency_map[$highest_impact_instance['frequency']] ?? 0)) {
                         $highest_impact_instance = $instance;
                    }
				}
            }
            
            if($highest_impact_instance) {
                $severity = $highest_impact_instance['severity'] ?? 'Mild';
                $frequency = $highest_impact_instance['frequency'] ?? 'Monthly';
                $base_penalty = $vector_config['penalties'][$severity][$frequency] ?? 0;
                
                $trigger_score_multiplier = min(1.5, 1 + ($vector_data['score'] - 1) * 0.1);
                $final_penalty = $base_penalty * $trigger_score_multiplier;
                error_log("HealthOptimizationCalculator: Vector '{$vector}' produced a final penalty of {$final_penalty} for pillar '{$pillar_impact}'.");
                
                if ($final_penalty > $pillar_penalties[$pillar_impact]) {
                    $pillar_penalties[$pillar_impact] = $final_penalty;
                    error_log("HealthOptimizationCalculator: New max penalty for pillar '{$pillar_impact}' is {$final_penalty}.");
                }
            }
		}
	}
    error_log("HealthOptimizationCalculator: Final pillar penalties: " . print_r($pillar_penalties, true));
	return $pillar_penalties;
}
```

**Analysis**:
- **Complex Algorithm**: Multi-step penalty calculation with severity/frequency weighting
- **Highest Impact Logic**: Selects most severe/frequent instance for each vector
- **Score Multiplier**: Applies trigger score multiplier to base penalties
- **Max Penalty Tracking**: Tracks highest penalty per pillar
- **Verbose Logging**: Excessive logging exposes penalty calculations
- **Hardcoded Values**: Severity and frequency mappings hardcoded

### Biomarker Recommendations (Lines 77-92)
```php
public function get_biomarker_recommendations() {
    error_log("HealthOptimizationCalculator: Starting biomarker recommendation.");
    $triggered_vectors = $this->get_triggered_vectors();
    $biomarker_map = $this->mappings['vector_to_biomarker_map'] ?? array();

    $recommended_biomarkers = array();
    foreach (array_keys($triggered_vectors) as $vector) {
        if (isset($biomarker_map[$vector])) {
            $recommended_biomarkers = array_merge($recommended_biomarkers, $biomarker_map[$vector]);
            error_log("HealthOptimizationCalculator: Vector '{$vector}' recommends biomarkers: " . implode(', ', $biomarker_map[$vector]));
        }
    }

    $final_recommendations = array_unique($recommended_biomarkers);
    error_log("HealthOptimizationCalculator: Final unique biomarker recommendations: " . print_r($final_recommendations, true));
    return $final_recommendations;
}
```

**Analysis**:
- **Vector Mapping**: Maps triggered vectors to biomarker recommendations
- **Deduplication**: Removes duplicate biomarkers using array_unique
- **Verbose Logging**: Logs vector recommendations and final results
- **Simple Logic**: Straightforward mapping without complex scoring

### Triggered Vectors Calculation (Lines 94-120)
```php
public function get_triggered_vectors() {
    $symptom_data = $this->get_symptom_data_for_user();
    $vector_map = $this->mappings['symptom_to_vector_map'] ?? array();
    
    $triggered_vectors = array();

    foreach($symptom_data as $q_id => $data) {
        if (empty($data['selection'])) continue;

        $symptoms = is_array($data['selection']) ? $data['selection'] : array($data['selection']);
        foreach($symptoms as $symptom) {
            if (isset($vector_map[$symptom])) {
                foreach($vector_map[$symptom] as $vector => $vector_data) {
                    $weight = $vector_data['weight'] ?? 0.5;
                    if (!isset($triggered_vectors[$vector])) {
                        $triggered_vectors[$vector] = array( 'score' => 0, 'instances' => array() );
                    }
                    $triggered_vectors[$vector]['score'] += $weight;
                    $triggered_vectors[$vector]['instances'][] = array(
                        'severity' => $data['severity'],
                        'frequency' => $data['frequency']
                    );
                }
            }
        }
    }
    return $triggered_vectors;
}
```

**Analysis**:
- **Symptom Processing**: Processes user symptom data with severity/frequency
- **Vector Mapping**: Maps symptoms to health vectors with weights
- **Score Accumulation**: Accumulates weighted scores for each vector
- **Instance Tracking**: Tracks all instances with severity/frequency data
- **Flexible Data**: Handles both single and array symptom selections

### Symptom Data Retrieval (Lines 122-142)
```php
private function get_symptom_data_for_user() {
	$symptom_data = array();
	$symptom_questions = $this->all_definitions['health_optimization_assessment']['questions'] ?? array();

	foreach ( $symptom_questions as $q_id => $q_def ) {
		if ( strpos( $q_id, 'symptom_' ) === 0 ) {
			$severity_key = str_replace( '_q', '_severity_q', $q_id );
			$frequency_key = str_replace( '_q', '_frequency_q', $q_id );
			
			$symptom_value = get_user_meta( $this->user_id, 'ennu_health_optimization_assessment_' . $q_id, true );
			if ( !empty($symptom_value) ) {
				$symptom_data[ $q_id ] = array(
					'selection' => $symptom_value,
					'severity' => get_user_meta( $this->user_id, 'ennu_health_optimization_assessment_' . $severity_key, true ),
					'frequency' => get_user_meta( $this->user_id, 'ennu_health_optimization_assessment_' . $frequency_key, true ),
				);
			}
		}
	}
	return $symptom_data;
}
```

**Analysis**:
- **Dynamic Question Processing**: Processes symptom questions dynamically
- **Meta Key Construction**: Constructs severity and frequency meta keys
- **User Meta Retrieval**: Uses WordPress user meta API
- **Conditional Loading**: Only loads data for non-empty symptom values
- **String Manipulation**: Uses str_replace for key construction

### Mapping File Loading (Lines 144-166)
```php
private function get_health_optimization_mappings() {
    // Load the modularized mapping files
    $symptom_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/symptom-map.php';
    $penalty_matrix_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/penalty-matrix.php';
    $biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';

    $mappings = array();
    if ( file_exists( $symptom_map_file ) ) {
        $mappings['symptom_to_vector_map'] = require $symptom_map_file;
    }
    if ( file_exists( $penalty_matrix_file ) ) {
        $mappings['pillar_integrity_penalty_matrix'] = require $penalty_matrix_file;
    }
    if ( file_exists( $biomarker_map_file ) ) {
        $mappings['vector_to_biomarker_map'] = require $biomarker_map_file;
    }

    return $mappings;
}
```

**Analysis**:
- **Modular Configuration**: Loads separate mapping files for different aspects
- **File Existence Checks**: Validates file existence before loading
- **Require Statements**: Uses require for immediate loading
- **Structured Loading**: Loads each mapping type into separate array keys
- **Path Construction**: Uses plugin path constant for file locations

## Issues Found

### Critical Issues
1. **Version Inconsistency**: Class version (60.0.0) doesn't match main plugin (62.2.6)
2. **Data Exposure**: Verbose logging exposes user symptoms, penalties, and recommendations
3. **No Input Validation**: Constructor doesn't validate input parameters

### Security Issues
1. **Sensitive Data Logging**: User symptoms, penalties, and biomarker data logged
2. **No Sanitization**: Input data not sanitized before processing
3. **User Meta Access**: Direct access to user meta without capability checks

### Performance Issues
1. **Excessive Logging**: Multiple log statements impact performance
2. **Multiple Database Queries**: get_user_meta called multiple times in loops
3. **File Loading**: Multiple file_exists and require calls in constructor

### Architecture Issues
1. **Tight Coupling**: Depends on specific data structure formats
2. **No Error Handling**: No try-catch blocks for robust error handling
3. **Hardcoded Values**: Severity/frequency mappings and pillar names hardcoded
4. **Complex Algorithm**: Pillar penalty calculation is overly complex

## Dependencies

### Files This Code Depends On
- `includes/config/health-optimization/symptom-map.php`
- `includes/config/health-optimization/penalty-matrix.php`
- `includes/config/health-optimization/biomarker-map.php`
- Assessment definitions (health_optimization_assessment)

### Functions This Code Uses
- `error_log()` - For debugging and data exposure
- `get_user_meta()` - For retrieving user symptom data
- `file_exists()` - For checking mapping file existence
- `require` - For loading mapping files
- `strpos()` - For string pattern matching
- `str_replace()` - For key construction
- `array_merge()` - For combining biomarker arrays
- `array_unique()` - For deduplication
- `implode()` - For logging biomarker lists
- `print_r()` - For debug output
- `min()` - For score multiplier calculation

### Classes This Code Depends On
- None directly (instance-based design)

## Recommendations

### Immediate Fixes
1. **Fix Version Inconsistency**: Update class version to 62.2.6
2. **Remove Verbose Logging**: Replace with structured logging without sensitive data
3. **Add Input Validation**: Validate constructor parameters

### Security Improvements
1. **Sanitize Input**: Add input sanitization for all parameters
2. **Structured Logging**: Use structured logging without sensitive data
3. **Capability Checks**: Add user capability checks for meta access
4. **Error Handling**: Add try-catch blocks for robust error handling

### Performance Optimizations
1. **Reduce Logging**: Minimize log statements in production
2. **Batch Meta Queries**: Use get_user_meta with multiple keys
3. **Caching**: Cache mapping files and user symptom data
4. **Lazy Loading**: Load mappings only when needed

### Architecture Improvements
1. **Interface Definition**: Create interface for calculator classes
2. **Configuration**: Move hardcoded values to configuration
3. **Simplified Algorithm**: Break down complex penalty calculation
4. **Error Reporting**: Return structured error objects
5. **Validation**: Add comprehensive input validation

## Integration Points

### Used By
- `ENNU_Scoring_System` - Main scoring orchestrator
- Health optimization assessment processing

### Uses
- User symptom data from health optimization assessment
- Mapping configurations from health optimization config files
- Assessment definitions for question structure

## Code Quality Assessment

**Overall Rating**: 5/10

**Strengths**:
- Clear single responsibility
- Proper encapsulation
- Modular configuration loading
- Complex health optimization logic

**Weaknesses**:
- Version inconsistency
- Excessive logging
- No input validation
- Tight coupling to data structures
- No error handling
- Performance issues with multiple queries
- Overly complex penalty calculation

**Maintainability**: Moderate - needs refactoring for production use
**Security**: Poor - exposes sensitive health data in logs
**Performance**: Fair - multiple database queries and file operations
**Testability**: Good - instance-based design allows easy testing 