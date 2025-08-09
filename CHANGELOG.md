## [64.70.1] - 2025-08-09
### Fixed
- Redirects consistently prefer results pages post-submit; details only for deep dives
- Pillar scores persisted per assessment; details/results read real stored values
- Details page chart uses unified canvas `assessmentTimelineChart` and external JS boot
- Global field auto-skip: added data marker for contact form so JS never re-prompts when saved
- Biomarkers auto-sync from globals (weight/height/BMI/age) remains universal across assessments
- Performance: removed transient force-clears in scoring to respect caching
- Stability: removed duplicate legacy AJAX handler require

# Changelog

All notable changes to ENNU Life Assessments plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [64.56.0] - 2025-01-08

### Added
- Comprehensive diagnostic scripts for verification
- Multiple key format checking for category scores
- Expanded symptom-biomarker mappings
- Proper empty state messages for missing data
- White text CSS for selected options visibility

### Changed
- Dashboard template loader from `basename()` to `ltrim()`
- CSS overflow from hidden to visible for expandable sections
- Assessment form fallback redirect to dashboard instead of hardcoded results page

### Fixed
- Blank dashboard display at page_id=3732
- Category score retrieval for weight_loss, testosterone, and ed_treatment assessments
- Recommendations and Breakdown sections not expanding
- Selected options visibility (black text on dark blue background)
- Key mismatch issues in dashboard data retrieval
- PHP warnings in diagnostic scripts
- JavaScript syntax error in user-dashboard.js
- Dashboard scripts loading on non-dashboard pages causing conflicts
- Fatal error with undefined get_user_meta_batch() method across ALL assessment types
- Replaced all Database Optimizer batch calls with standard WordPress functions

### Removed
- Debug box from My Biomarkers tab
- Mock category score generation
- All dummy data generation functions

### Technical Details
- **Files Modified:**
  - `templates/user-dashboard.php` - Removed debug box, fixed empty states
  - `includes/class-assessment-shortcodes.php` - Added multiple key format checking
  - `includes/class-centralized-symptoms-manager.php` - Expanded symptom mappings
  - `assets/css/ennu-frontend-forms.css` - Added white text for selected options
  - `assets/css/user-dashboard.css` - Changed overflow to visible
  - `assets/js/assessment-form.js` - Updated fallback redirect logic

## [64.55.0] - 2025-01-07

### Added
- Biomarker auto-flagging system based on assessment symptoms
- Symptom extraction from all 11 assessment types
- Centralized symptoms manager integration
- Healthcare provider dashboard flagged biomarker display

### Enhanced
- Results page handles both legacy and new data formats
- Pillar score calculation added to all assessment submission handlers
- Symptom-to-biomarker mapping with 35+ symptom definitions

### Technical
- Implemented reflection to access private pillar mapping methods
- Added `extract_symptoms_from_assessment()` method

## [64.54.0] - 2025-01-06

### Added
- Global field system for cross-assessment data persistence
- Smart field mapping and automatic detection
- Contact form integration
- HubSpot synchronization for global fields

### Changed
- User edit page with comprehensive global field management
- Health goals multi-select interface in admin

### Fixed
- Duplicate first name and last name fields in WordPress admin
- Global field persistence across all assessments

## [64.53.24] - 2025-01-05

### Changed
- Complete migration to simple page_id approach across all components
- Admin interface shows simple "Page ID" dropdowns for all assessment pages
- Next Steps Widget and UI Constants use simple page ID mapping

### Fixed
- Assessment redirects properly use admin-configured page IDs
- Redirect system uses direct page_id=# format for cleaner URLs

### Removed
- Complex slug-based URL generation patterns

## Previous Versions

For version history prior to 64.53.24, please refer to the git commit history or contact the development team.

---

## Version Numbering

The ENNU Life Assessments plugin uses a three-part version numbering system:
- **Major**: Significant architectural changes or breaking changes
- **Minor**: New features, enhancements, or non-breaking changes
- **Patch**: Bug fixes and minor improvements

Example: 64.56.0
- 64 = Major version
- 56 = Minor version
- 0 = Patch version

---

## Support

For questions about specific versions or changes, contact:
- **Lead Developer**: Luis Escobar (CTO)
- **Repository**: Internal ENNU Life repository
- **Documentation**: See README.md and README-codebase.md