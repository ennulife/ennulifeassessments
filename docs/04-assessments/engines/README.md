# Assessment Engines Documentation

This directory contains documentation for the various assessment engines that power the ENNU Life Assessments plugin.

## Engine Types

### 1. Intentionality Goals Engine
- **File**: [engine-intentionality-goals.md](engine-intentionality-goals.md)
- **Purpose**: Handles goal-setting and intentionality assessment logic
- **Key Features**: Goal prioritization, progress tracking, motivation analysis

### 2. Objective Biomarkers Engine
- **File**: [engine-objective-biomarkers.md](engine-objective-biomarkers.md)
- **Purpose**: Processes quantitative biomarker data
- **Key Features**: Data validation, trend analysis, threshold monitoring

### 3. Qualitative Symptoms Engine
- **File**: [engine-qualitative-symptoms.md](engine-qualitative-symptoms.md)
- **Purpose**: Analyzes subjective symptom reports
- **Key Features**: Symptom correlation, severity assessment, pattern recognition

## Engine Architecture

All engines follow a consistent architecture pattern:
- **Input Processing**: Validates and normalizes incoming data
- **Analysis Layer**: Applies business logic and scoring algorithms
- **Output Generation**: Produces structured results and recommendations
- **Integration Interface**: Connects with other system components

## Integration Points

- **Scoring System**: All engines feed into the central scoring calculator
- **Recommendation Engine**: Results influence personalized recommendations
- **Dashboard**: Engine outputs are displayed in user dashboards
- **Reporting**: Engine data contributes to comprehensive health reports

---

*For detailed implementation information, see the individual engine documentation files.* 