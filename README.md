# ENNU Life Assessment Plugin

**Version:** 62.2.8
**Author:** Luis Escobar  
**License:** GPL v2 or later
**Repository Access:** Verified by Devin AI

## 🚀 Latest Updates (v57.2.1)

### Critical Fixes Implemented:
- ✅ **FIXED**: Assessment pre-population - users no longer need to re-enter gender, DOB, height, weight
- ✅ **FIXED**: Global fields display - Age, Gender, Height, Weight, BMI now properly show on dashboard  
- ✅ **FIXED**: Light mode readability - enhanced contrast for scores and contextual text
- ✅ **ENHANCED**: Data persistence and error handling across all assessment types
- ✅ **IMPROVED**: User experience with seamless data flow and visual clarity

## Overview

The ENNU Life Assessment Plugin is an enterprise-grade WordPress solution that provides comprehensive health assessments with advanced scoring algorithms, personalized insights, and a beautiful user interface.

## Key Features

### Assessment Types
- **Welcome Assessment** - User onboarding and data collection
- **Hair Assessment** - Hair health evaluation
- **ED Treatment Assessment** - Erectile dysfunction assessment
- **Weight Loss Assessment** - Weight management evaluation
- **Health Assessment** - General health status
- **Skin Assessment** - Skin health analysis
- **Sleep Assessment** - Sleep quality evaluation
- **Hormone Assessment** - Hormonal health check
- **Menopause Assessment** (Female only)
- **Testosterone Assessment** (Male only)
- **Health Optimization Assessment** - Symptom-based qualitative assessment

### Scoring System
- **Four-Tier Hierarchy:**
  1. Category Scores - Granular feedback within assessments
  2. Overall Assessment Score - Per health vertical
  3. Pillar Scores - Mind, Body, Lifestyle, Aesthetics
  4. ENNU LIFE SCORE - Master proprietary health metric

### User Features
- **Bio-Metric Canvas Dashboard** - Stunning visualization of health metrics
- **Historical Tracking** - Score and BMI trends over time
- **Tokenized Results** - Secure post-assessment flow
- **Gender-Specific Filtering** - Appropriate assessments per user
- **Health Optimization Map** - Interactive symptom and biomarker visualization

### Administrative Features
- **Enhanced User Profiles** - Comprehensive assessment data view
- **Score Recalculation** - Manual score refresh capability
- **Data Management** - Clear individual or all assessment data
- **Analytics Dashboard** - Aggregate insights (Phase 2 - planned)

## Installation

1. **Upload Plugin**
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   git clone [repository-url] ennulifeassessments
   ```

2. **Activate Plugin**
   - Navigate to WordPress Admin → Plugins
   - Find "ENNU Life Assessments" and click "Activate"

3. **Create Assessment Pages**
   - Go to ENNU Life → Settings
   - Click "Create Assessment Pages" button
   - This auto-generates all required pages with proper shortcodes

4. **Configure Settings**
   - Set your timezone
   - Configure email notifications
   - Set up any integrations (WP Fusion, HubSpot)

## Usage

### Shortcodes

**Assessment Forms:**
- `[ennu-welcome-assessment]` - Welcome/onboarding form
- `[ennu-hair-assessment]` - Hair assessment form
- `[ennu-health-optimization-assessment]` - Symptom assessment form
- (Similar pattern for all assessment types)

**Results Pages:**
- `[ennu-hair-results]` - Hair assessment results
- `[ennu-health-optimization-results]` - Health optimization results
- (Similar pattern for all assessment types)

**Dashboard:**
- `[ennu-user-dashboard]` - Main user dashboard

**Details Pages:**
- `[ennu-hair-assessment-details]` - Detailed assessment history
- (Similar pattern for all assessment types)

### User Flow
1. User completes an assessment form
2. System calculates scores and saves data
3. User receives token-based redirect to results page
4. Dashboard updates with new scores and history
5. User can view detailed reports and track progress

## Development

### File Structure
```
ennulifeassessments/
├── assets/
│   ├── css/         # Stylesheets
│   └── js/          # JavaScript files
├── includes/
│   ├── config/      # Assessment definitions and mappings
│   └── *.php        # Core PHP classes
├── templates/       # Display templates
├── docs/            # Comprehensive documentation (NEW!)
└── tests/          # Test files (minimal coverage currently)
```

### Documentation
📚 **NEW**: All documentation has been reorganized into a comprehensive structure. See the [Documentation Index](docs/README.md) for complete navigation.

**Quick Access:**
- [Installation Guide](docs/01-getting-started/installation.md)
- [Developer Notes](docs/01-getting-started/developer-notes.md)
- [System Architecture](docs/02-architecture/system-architecture.md)
- [Assessment Guide](docs/04-assessments/master-assessment-guide.md)
- [Scoring System](docs/05-scoring/architecture/scoring-architecture.md)

### Key Files
- `includes/config/assessment-definitions.php` - Single source of truth for all assessments
- `includes/class-scoring-system.php` - Core scoring engine
- `includes/class-assessment-shortcodes.php` - Shortcode handlers
- `templates/user-dashboard.php` - Main dashboard template

### Code Standards
- Follow WordPress Coding Standards
- Use proper sanitization and escaping
- Implement nonce verification for all forms
- Add meaningful code comments
- Test across different user states

## Current State (v59.0.0)

### Recent Fixes
- ✅ Assessment toggle functionality restored
- ✅ Pillar scores display correctly
- ✅ Health optimization counts calculate properly
- ✅ Progress charts work on all pages
- ✅ Main score animations function properly
- ✅ Health optimization section always visible

### Known Limitations
- Heavy jQuery dependency (modernization planned)
- Limited automated testing (expansion planned)
- Manual build process (automation planned)
- Basic API endpoints (REST API planned)

## Roadmap

### Q1 2025 - Testing & Modernization
- Implement comprehensive test suite
- Remove jQuery dependencies
- Add build pipeline
- Security hardening

### Q2 2025 - API & Mobile
- Full REST API
- Mobile app support
- Advanced caching
- Performance optimization

### Q3 2025 - Enterprise Features
- Multi-tenant support
- Advanced analytics
- HIPAA compliance
- Bulk operations

### Q4 2025 - AI Integration
- Predictive scoring
- Natural language processing
- Computer vision features
- Machine learning insights

## Support

For issues or questions:
1. Check the [Documentation Index](docs/README.md) for comprehensive guides
2. Review the [changelog](CHANGELOG.md) for recent changes
3. Ensure you're running compatible versions of WordPress and PHP
4. Check browser console for JavaScript errors

## Contributing

This is a proprietary plugin. All contributions must be approved by the ENNU Life development team.

## License

Proprietary - All rights reserved by ENNU Life.

---

*Built with excellence by the World's Greatest Developer*

