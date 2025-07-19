# ENNU Life Score Calculator Analysis

**File**: `includes/class-ennu-life-score-calculator.php`  
**Version**: 60.0.0 (vs main plugin 62.2.6)  
**Lines**: 86  
**Class**: `ENNU_Life_Score_Calculator`

## File Overview

This class calculates the final, adjusted ENNU LIFE SCORE by taking base pillar scores and applying penalties from health optimization data. It's the core calculator that produces the main health score used throughout the system.

## Line-by-Line Analysis

### File Header and Security (Lines 1-13)
```php
<?php
/**
 * ENNU Life Score Calculator
 *
 * This class is responsible for calculating the final, adjusted ENNU LIFE SCORE.
 * It takes the base Pillar Scores and applies penalties from the Health Optimization data.
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
- **Documentation**: Clear description of main score calculation functionality

### Class Definition and Properties (Lines 15-22)
```php
class ENNU_Life_Score_Calculator {

    private $user_id;
    private $base_pillar_scores;
    private $all_definitions;
    private $health_goals;
    private $goal_definitions;
```

**Analysis**:
- **Instance-Based Design**: Uses dependency injection pattern
- **Encapsulation**: Private properties properly encapsulate data
- **Dependencies**: Requires user ID, base pillar scores, definitions, and health goals

### Constructor (Lines 24-30)
```php
public function __construct( $user_id, $base_pillar_scores, $all_definitions, $health_goals = array(), $goal_definitions = array() ) {
    $this->user_id = $user_id;
    $this->base_pillar_scores = $base_pillar_scores;
    $this->all_definitions = $all_definitions;
    $this->health_goals = $health_goals;
    $this->goal_definitions = $goal_definitions;
    error_log("EnnuLifeScoreCalculator: Instantiated for user ID {$user_id}.");
}
```

**Analysis**:
- **Dependency Injection**: Properly accepts required dependencies
- **Optional Parameters**: Health goals and goal definitions are optional
- **User-Specific Logging**: Logs user ID for tracking
- **Data Assignment**: Direct property assignment without validation

### Main Calculation Method (Lines 32-86)
```php
public function calculate() {
    error_log("EnnuLifeScoreCalculator: Starting calculation.");
    // 1. Calculate Pillar Integrity Penalties
    $health_opt_defs = $this->all_definitions['health_optimization_assessment'] ?? array();
    $health_opt_calculator = new ENNU_Health_Optimization_Calculator( $this->user_id, array( 'health_optimization_assessment' => $health_opt_defs ) );
    $pillar_penalties = $health_opt_calculator->calculate_pillar_penalties();
    error_log("EnnuLifeScoreCalculator: Calculated pillar penalties: " . print_r($pillar_penalties, true));

    // 2. Apply Penalties to get the Final Adjusted Pillar Scores
    $final_pillar_scores = array();
    $pillar_score_data = array();
    foreach($this->base_pillar_scores as $pillar_name => $base_score) {
        $penalty = $pillar_penalties[$pillar_name] ?? 0;
        $final_score = $base_score * (1 - $penalty);
        $final_pillar_scores[$pillar_name] = $final_score;

        $pillar_score_data[$pillar_name] = array(
            'base' => round($base_score, 1),
            'penalty' => round($penalty * 100, 0), // store as percentage
            'final' => round($final_score, 1),
        );
    }
    error_log("EnnuLifeScoreCalculator: Calculated final adjusted pillar scores: " . print_r($final_pillar_scores, true));

    // 3. Apply strategic weights to the FINAL scores
    $weights = array(
        'mind'       => 0.3,
        'body'       => 0.3,
        'lifestyle'  => 0.3,
        'aesthetics' => 0.1,
    );

    $ennu_life_score = 0;
    foreach ( $final_pillar_scores as $pillar_name => $final_score ) {
        if ( isset( $weights[ $pillar_name ] ) ) {
            $ennu_life_score += $final_score * $weights[ $pillar_name ];
        }
    }
    error_log("EnnuLifeScoreCalculator: Final ENNU LIFE SCORE calculated: " . round($ennu_life_score, 1));

    // The calculator should only calculate. It should not save.
    // The orchestrator will be responsible for saving this data.
    $capitalized_pillar_scores = array();
	foreach ( $final_pillar_scores as $pillar_name => $score ) {
		$capitalized_pillar_scores[ ucfirst( $pillar_name ) ] = round( $score, 1 );
	}

    return array(
        'ennu_life_score' => round( $ennu_life_score, 1 ),
        'pillar_score_data' => $pillar_score_data,
        'average_pillar_scores' => $capitalized_pillar_scores,
    );
}
```

**Analysis**:
- **Three-Phase Calculation**: Penalty calculation, penalty application, weighted averaging
- **Health Optimization Integration**: Creates health optimization calculator instance
- **Penalty Application**: Applies penalties multiplicatively to base scores
- **Detailed Data Structure**: Returns comprehensive score breakdown
- **Weighted Averaging**: Uses hardcoded weights for final score
- **Verbose Logging**: Excessive logging exposes penalty and score data
- **Data Formatting**: Converts pillar names to capitalized format
- **Single Responsibility**: Only calculates, doesn't save data

## Issues Found

### Critical Issues
1. **Version Inconsistency**: Class version (60.0.0) doesn't match main plugin (62.2.6)
2. **Data Exposure**: Verbose logging exposes penalty and score data
3. **No Input Validation**: Constructor doesn't validate input parameters
4. **Hardcoded Weights**: Pillar weights are hardcoded

### Security Issues
1. **Sensitive Data Logging**: Penalty and score data logged
2. **No Sanitization**: Input data not sanitized before processing
3. **User Meta Access**: Indirect access through health optimization calculator

### Performance Issues
1. **Excessive Logging**: Multiple log statements impact performance
2. **Object Creation**: Creates new health optimization calculator instance
3. **Hardcoded Values**: Weights hardcoded, reducing flexibility

### Architecture Issues
1. **Tight Coupling**: Depends on specific data structure formats
2. **No Error Handling**: No try-catch blocks for robust error handling
3. **Hardcoded Values**: Pillar weights hardcoded
4. **Dependency Creation**: Creates health optimization calculator instead of receiving it

## Dependencies

### Files This Code Depends On
- Health optimization assessment definitions
- Base pillar scores (from pillar score calculator)
- Health optimization calculator class

### Functions This Code Uses
- `error_log()` - For debugging and data exposure
- `print_r()` - For debug output
- `round()` - For score rounding
- `ucfirst()` - For pillar name capitalization

### Classes This Code Depends On
- `ENNU_Health_Optimization_Calculator` - For penalty calculation

## Recommendations

### Immediate Fixes
1. **Fix Version Inconsistency**: Update class version to 62.2.6
2. **Remove Verbose Logging**: Replace with structured logging without sensitive data
3. **Add Input Validation**: Validate constructor parameters

### Security Improvements
1. **Sanitize Input**: Add input sanitization for all parameters
2. **Structured Logging**: Use structured logging without sensitive data
3. **Error Handling**: Add try-catch blocks for robust error handling

### Performance Optimizations
1. **Reduce Logging**: Minimize log statements in production
2. **Dependency Injection**: Receive health optimization calculator as dependency
3. **Caching**: Cache penalty calculations

### Architecture Improvements
1. **Interface Definition**: Create interface for calculator classes
2. **Configuration**: Move hardcoded values to configuration
3. **Validation**: Add comprehensive input validation
4. **Error Reporting**: Return structured error objects
5. **Separation of Concerns**: Separate penalty calculation from score calculation

## Integration Points

### Used By
- `ENNU_Scoring_System` - Main scoring orchestrator
- Assessment calculation pipeline

### Uses
- Base pillar scores from pillar score calculator
- Health optimization penalties from health optimization calculator
- Assessment definitions for health optimization data

## Code Quality Assessment

**Overall Rating**: 6/10

**Strengths**:
- Clear single responsibility
- Proper encapsulation
- Comprehensive data structure output
- Single responsibility principle (only calculates)

**Weaknesses**:
- Version inconsistency
- Excessive logging
- No input validation
- Tight coupling to data structures
- No error handling
- Creates dependencies instead of receiving them
- Hardcoded weights

**Maintainability**: Moderate - needs refactoring for production use
**Security**: Poor - exposes penalty and score data in logs
**Performance**: Good - efficient algorithm but excessive logging
**Testability**: Good - instance-based design allows easy testing 