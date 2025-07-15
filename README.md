# ENNU Life Assessments WordPress Plugin

**Version:** 59.0.0  
**Last Updated:** December 18, 2024  
**WordPress:** 5.0+ required, 6.8+ recommended  
**PHP:** 7.4+ required, 8.0+ recommended  
**License:** Proprietary

## Overview

The ENNU Life Assessments plugin is a comprehensive health assessment system for WordPress that provides multiple health evaluations with sophisticated scoring algorithms, user dashboards, and administrative tools. It features a proprietary four-tier scoring hierarchy culminating in the master ENNU LIFE SCORE.

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
├── documentation/   # Detailed docs
└── tests/          # Test files (minimal coverage currently)
```

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
1. Check the `/documentation` folder
2. Review the changelog for recent changes
3. Ensure you're running compatible versions of WordPress and PHP
4. Check browser console for JavaScript errors

## Contributing

This is a proprietary plugin. All contributions must be approved by the ENNU Life development team.

## License

Proprietary - All rights reserved by ENNU Life.

---

*Built with excellence by the World's Greatest Developer*

