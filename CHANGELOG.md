## [79.0.0] - 2025-01-15

### Enhanced User Experience & Target Score System Improvements

This version significantly improves the assessment submission experience and implements a dynamic target scoring system for personalized health goals.

#### **🎯 Dynamic Target Score System**
- **Individual Pillar Targets**: Each pillar (Mind, Body, Lifestyle, Aesthetics) now calculates its own target based on current score
- **Evidence-Based Algorithm**: Progressive improvement targets adapt to performance level
  - Low scores (0-2.9): +2.0 improvement potential
  - Average scores (3.0-5.9): +1.5 improvement goals  
  - Good scores (6.0-7.9): +1.0 steady progress
  - High scores (8.0+): +0.5 fine-tuning targets
- **Consistent Display**: Eliminated random target value changes on page refresh
- **Personalized Goals**: Each pillar shows realistic, achievable targets based on individual performance

#### **📱 Improved Assessment Modal Experience**  
- **Dynamic Messaging**: Modal text adapts based on user login status
- **Logged-In Users**: "Processing Your Assessment" with "Validating Your Responses"
- **New Users**: "Creating Your Personalized Health Profile" with "Creating Your Account"
- **Enhanced Flow**: Extended modal timing with proper success feedback before redirect
- **Bulletproof Redirects**: Sequential processing ensures modal completion before navigation

#### **🔧 Technical Improvements**
- **JavaScript Optimization**: Removed conflicting target calculations that caused refresh inconsistencies
- **Template Updates**: Unified target calculation system across user-dashboard.php and modern-scoring-section.php
- **Progress Tracking**: Enhanced modal progress indicators with appropriate timing
- **Error Handling**: Improved modal cleanup on submission errors

#### **💡 User Experience Enhancements**
- **Visual Consistency**: Target values no longer change unexpectedly on page refresh
- **Clear Feedback**: Users see appropriate messaging during assessment processing
- **Smooth Transitions**: Proper timing between modal success and results page navigation
- **Contextual Messaging**: Assessment submission flow adapts to user account status

---

## [77.0.0] - 2025-01-14

### Critical Release: Peptide Therapy Assessment Complete Integration & System Verification

This version completes the full integration of the Peptide Therapy Assessment with comprehensive system verification, fixing all integration points and ensuring 100% compatibility with existing assessment infrastructure.

#### **✅ Complete Integration Fixes**
- **Shortcode Registration**: Fixed dynamic shortcode loading for all 4 peptide therapy shortcodes
- **Scoring System Fix**: Changed `require` to `include` in class-scoring-system.php to prevent file reload issues
- **Dashboard Display**: Added peptide-therapy to "My Assessments" tab for all users (male/female)
- **Admin Settings**: Added to all page mapping dropdowns and SEO configuration

#### **🔬 Biomarker Auto-Flagging Enhancement**
- **Symptom Mappings Added**: 
  - `slow_recovery` → growth_hormone, igf1, testosterone
  - `poor_healing` → growth_hormone, igf1, vitamin_d
  - `muscle_loss` → testosterone, growth_hormone, igf1
  - `low_libido` → testosterone, estradiol, dhea_s
  - `cognitive_decline` → vitamin_b12, thyroid_tsh, growth_hormone

#### **📊 System Integration Completions**
- **Analytics Service**: Added peptide-therapy to all tracking arrays
- **Data Export Service**: Verified automatic inclusion via wildcards
- **Teams/n8n Integration**: Special handling for peptide therapy notifications
- **HubSpot CRM**: Field mappings fully configured
- **REST API**: Added to assessment types array
- **Progressive Data Collector**: Auto-save fields configured
- **Email Automation**: Completion emails functional

#### **✅ Verification & Testing**
- **Unit Tests Created**: Comprehensive test suite with 8/8 tests passing
- **Four-Engine Scoring**: All engines verified operational
- **AJAX Handlers**: All handlers registered and functional
- **Templates**: Both assessment and dashboard templates working
- **Data Persistence**: User meta storage verified

#### **📝 Documentation Updates**
- **PEPTIDE-THERAPY-FINAL-VERIFICATION.md**: Complete verification report
- **Test Suite**: /tests/test-peptide-therapy.php for ongoing validation
- **README Updates**: Added peptide therapy to feature list

## [76.0.0] - 2025-01-14

### Major Release: Peptide Therapy Assessment Integration

This major version introduces a comprehensive Peptide Therapy Assessment, fully integrated with the unified assessment system, providing personalized peptide recommendations based on individual health goals and symptoms.

#### **💉 New Peptide Therapy Assessment**
- **15 Comprehensive Questions**: Covering weight management, recovery, hormones, cognition, and anti-aging
- **Intelligent Recommendations**: AI-powered peptide suggestions based on assessment responses
- **Five Scoring Categories**: Weight Management & Metabolism, Recovery & Performance, Hormonal Optimization, Cognitive Enhancement, Anti-Aging & Longevity
- **Four-Pillar Integration**: Mind (20%), Body (45%), Lifestyle (25%), Aesthetics (10%)

#### **🎯 Assessment Features**
- **Unified UX/UI**: Identical flow and experience as all other assessments
- **Shortcode Ready**: `[ennu-peptide-therapy]` for easy embedding
- **Dashboard Integration**: Results display with medical icon and #3498db theme
- **Progressive Data Collection**: Auto-save and resume capabilities

#### **🔧 Technical Implementation**
- **Complete System Integration**: Added to all assessment arrays and switch statements
- **Scoring Configuration**: Full four-engine scoring in scoring-weights.json
- **Data Persistence**: Saves to user meta with proper namespacing
- **Admin Visibility**: Viewable in user profiles and assessment viewer
- **API Ready**: REST endpoints and HubSpot field mapping configured

#### **📊 Peptide Recommendations Include**
- **Weight Management**: GLP-1 agonists, AOD-9604, CJC-1295/Ipamorelin
- **Recovery**: BPC-157, TB-500, IGF-1 LR3
- **Hormones**: Kisspeptin, PT-141, DSIP
- **Cognitive**: Semax, Selank, Cerebrolysin
- **Anti-Aging**: Epithalon, GHK-Cu, NAD+

#### **📝 Documentation**
- **Comprehensive Guide**: PEPTIDE-THERAPY-ASSESSMENT-COMPLETE.md with full details
- **Updated CLAUDE.md**: Latest plugin capabilities documented
- **Version Update File**: Complete changelog and upgrade instructions

## [72.0.0] - 2025-01-13

### Major Release: Unified LabCorp Upload System

This major version consolidates all biomarker upload methods into a single, streamlined LabCorp PDF processing system with both user-facing and admin interfaces.

#### **📤 Upload System Consolidation**
- **Single Upload Method**: Replaced multiple CSV importers with unified LabCorp PDF processor
- **Admin Interface**: New menu item under ENNU Life → LabCorp Upload for admins
- **Frontend Shortcode**: `[ennu_labcorp_upload]` for embedding upload forms anywhere
- **Dashboard Integration**: Fixed and optimized "Upload Labs" tab functionality

#### **🔧 Technical Improvements**
- **JavaScript Fix**: Corrected AJAX action mismatch (`ennu_upload_lab_pdf` → `ennu_upload_pdf`)
- **Field Name Fix**: Fixed form field name (`pdf_file` → `labcorp_pdf`)
- **Error Handling**: Improved upload validation and user feedback
- **Code Cleanup**: Removed deprecated CSV import classes

#### **👤 Admin Features**
- **User Selection**: Upload PDFs for any user via dropdown
- **Audit Trail**: Track who uploaded what and when
- **Lab Date**: Optional collection date specification
- **Admin Notes**: Add notes about uploads for record keeping

#### **🚀 User Benefits**
- **Simplified Process**: One consistent upload method across platform
- **Better Reliability**: Fixed longstanding upload issues
- **Automatic Extraction**: PDF processing with instant biomarker updates
- **Progress Feedback**: Clear status messages during upload

## [71.0.0] - 2025-01-13

### Major Release: Enhanced User Experience Design

This version introduces significant UX improvements with a complete redesign of the progress modal from dark to light theme, with carefully tuned animations for optimal user perception.

#### **🎨 Visual Redesign**
- **Light Theme**: Converted from dark gradient to clean white/light gray design
- **Improved Contrast**: Dark text on light background for better readability
- **Modern Aesthetics**: Subtle shadows and professional gradient effects
- **Green Accents**: Success-oriented color scheme with green progress indicators

#### **⏱️ Animation Timing Overhaul**
- **Modal Fade**: Increased from 0.3s to 0.6s for smoother entrance
- **Step Transitions**: Slowed from 0.3s to 0.8s for better perception
- **Spinner Speed**: Reduced from 1s to 2s per rotation
- **Progress Bar**: Extended from 0.5s to 1s fill animation
- **Total Duration**: Increased from ~5s to ~10s for complete experience

#### **📱 Mobile Enhancements**
- **Responsive Padding**: Optimized spacing for mobile devices
- **Touch-Friendly**: Larger tap targets and better mobile interactions
- **Smooth Scrolling**: Improved performance on mobile browsers
- **Adaptive Layout**: Automatic adjustments for smaller screens

## [70.0.0] - 2025-01-13

### Major Release: Enhanced First-Time User Experience

This major version introduces an engaging progress modal for first-time users during account creation, providing visual feedback and reducing perceived wait times during the registration and scoring process.

#### **✨ Progress Modal Features**
- **Visual Progress Tracking**: Four-step animated progress indicator
- **Real-time Status Updates**: Dynamic messages for each processing stage
- **Smooth Animations**: Professional transitions and loading spinners
- **Auto-Detection**: Automatically shows for new user registrations
- **Graceful Degradation**: Seamlessly handles errors with modal cleanup

#### **🎯 User Experience Benefits**
- **Reduced Anxiety**: Clear visual feedback during account creation
- **Professional Feel**: Polished interface for critical first impression
- **Transparent Process**: Users see exactly what's happening
- **Faster Perceived Speed**: Animation makes wait time feel shorter

#### **🔧 Technical Implementation**
- **Smart Detection**: Shows only for non-logged-in users with email
- **Progress Steps**: Account Creation → Data Saving → Score Calculation → Results Preparation
- **Responsive Design**: Optimized for both desktop and mobile devices
- **Error Handling**: Automatic cleanup on errors or completion
- **Performance**: Lightweight implementation with CSS animations

## [69.0.0] - 2025-08-12

### Major Release: Gender-Specific Landing Page Support

This major version introduces intelligent gender preselection for assessment shortcodes, enabling targeted landing pages with streamlined user experiences for gender-specific marketing campaigns.

#### **🎯 Gender Preset Feature**
- **Shortcode Parameter**: New `gender` attribute for all assessment shortcodes
- **Auto-Selection**: Automatically sets gender to "male" or "female" based on parameter
- **Hidden Question**: Gender question completely skipped when preset is used
- **Sequential Numbering**: Questions remain numbered 1, 2, 3... despite skipped question

#### **💼 Marketing Benefits**
- **Targeted Campaigns**: Create gender-specific landing pages for better conversion
- **Reduced Friction**: One less question for users to answer
- **Cleaner UX**: Users only see relevant options for their demographic
- **Seamless Experience**: No indication that a question was skipped

#### **🔧 Implementation Details**
- **Usage Examples**: `[ennu-welcome gender="male"]` or `[ennu-assessment-form type="health" gender="female"]`
- **Hidden Input**: Gender value stored and submitted automatically
- **Progress Tracking**: Correct question count displayed to users
- **Backend Compatible**: Gender data still captured for all processing

## [68.0.0] - 2025-08-12

### Major Release: Platform Stability & Multi-Instance Support

This major version confirms platform stability with support for multiple assessment instances across different pages and comprehensive session management improvements from previous releases.

#### **✅ Platform Capabilities**
- **Multi-Page Support**: Confirmed support for multiple assessment shortcodes on different pages
- **Independent Instances**: Each shortcode creates its own isolated form instance
- **No Conflicts**: Multiple assessments can run simultaneously without interference
- **Flexible Deployment**: Same or different assessments can be used across multiple pages

#### **🔧 Technical Improvements**
- **Instance Isolation**: Each form maintains its own JavaScript object and state
- **Progress Tracking**: Form-specific progress tracking per instance
- **Data Integrity**: User data properly tied to accounts, not page instances
- **Session Management**: Continued improvements from v67.0.0

## [67.0.0] - 2025-08-12

### Major Release: Session Management & Data Isolation

This major version delivers critical improvements to session management, preventing data contamination between users and ensuring clean experiences for multi-user environments.

#### **🔒 Session & Data Management**
- **localStorage Cleanup**: Comprehensive clearing after successful submissions
- **Email Change Detection**: Automatic data clearing when email changes mid-form
- **Logged-Out User Protection**: No prefilling or auto-loading for non-authenticated users
- **Multi-User Device Support**: Clean slate for each new user on same browser

#### **🚀 User Flow Improvements**
- **Auto-Redirect for Welcome Assessment**: Logged-in users automatically redirected to signup/results page
- **Prevents Duplicate Submissions**: Logged-in users can't retake welcome assessment
- **Smart Session Recognition**: Proper cache clearing and session initialization for new users
- **Clean Form Experience**: Each submission starts with completely clean data

#### **🛡️ Data Integrity**
- **No Cross-Contamination**: Previous user data never persists for new users
- **Email-Specific Isolation**: Data tied to specific email sessions
- **Automatic Cleanup**: All assessment data cleared from localStorage on completion
- **Session State Management**: Proper handling of logged-in vs logged-out states

## [66.0.0] - 2025-08-12

### Major Release: Streamlined User Experience & Enhanced Security

This major version focuses on optimizing the user journey from assessment to consultation, with significant improvements to results presentation, security enhancements, and Teams integration for better operational visibility.

#### **🎯 User Experience Improvements**
- **Streamlined Results Page**: Removed 40% of redundant content for clearer focus on booking consultations
- **Personalized Messaging**: Added first-name personalization to results and details pages
- **Strategic CTA Placement**: Integrated HubSpot calendar directly on results page for immediate booking
- **Simplified Navigation**: Removed duplicate buttons and conflicting calls-to-action

#### **🔒 Security & Authentication**
- **Login Protection**: Existing users now required to authenticate before submitting assessments
- **Session Management**: Enhanced cache clearing and session recognition for new registrations
- **Rate Limiting**: Improved protection against registration abuse
- **Data Integrity**: Fixed user meta caching issues for consistent data across all pages

#### **📊 Content Optimization**
- **Removed Biomarkers**: Eliminated technical biomarker sections from results and details pages
- **Focused Details View**: Details page now emphasizes progress tracking over technical data
- **Clear Page Distinction**: Results page for quick action, Details page for deep analysis
- **Reduced Cognitive Load**: Removed benefits sections, duplicate headers, and category breakdowns

#### **🔔 Teams Integration**
- **Direct Teams Notifications**: New user registrations now send immediate notifications to Teams
- **Native Teams Webhook**: Implemented direct webhook for "New Registrations" channel
- **Rich Card Format**: Professional message cards with user details and profile links

## [65.0.0] - 2025-08-11

### Major Version Release: Complete Platform Modernization

This major version release represents a significant milestone in the ENNU Life Assessments platform evolution, introducing fundamental architectural improvements, comprehensive UI modernization, and enhanced data reliability systems.

#### **🚀 Platform Evolution**
- **Major Version Milestone**: Transition to version 65.0.0 marking platform maturity
- **Production-Ready Architecture**: Enterprise-grade reliability with comprehensive testing
- **Future-Proof Foundation**: Scalable systems ready for next-generation features
- **Performance Baseline**: Established new performance benchmarks for all operations

## [64.65.0] - 2025-08-11

### Major Updates: Scoring Accuracy & UI Modernization

#### **🎯 Database-First Architecture**
- **BREAKING IMPROVEMENT**: All scoring data now prioritizes database values over transients
- **Cross-Platform Consistency**: Assessment results, details pages, and WordPress admin now show identical scores
- **Data Integrity**: Single source of truth established with proper fallback hierarchy (Database → Transient → Calculated)
- **Backward Compatibility**: Dual storage system maintains both new format (primary) and old format (compatibility)

#### **🔧 Scoring System Enhancements**
- **Assessment-Specific Scoring**: Templates now display actual assessment scores instead of averaged data
- **Meta Key Harmonization**: Unified `ENNU_Assessment_Constants::get_full_meta_key()` usage across all retrieval
- **Enhanced Storage**: Scores saved to both new format meta keys and legacy keys for seamless transition
- **Admin Profile Sync**: WordPress user profile displays now match template scores exactly

#### **🎨 Template Modernization**
- **Modern Card-Based UI**: Replaced orb-based scoring displays with intuitive card layouts
- **Three-Column Hero Layout**: Assessment info, score display with circular progress, and action buttons
- **Progress Bar Animations**: Staggered loading animations with CSS transitions and JavaScript-driven width updates
- **Assessment-Specific Context**: Dynamic titles, status indicators, and contextual information per assessment type

#### **💾 Technical Improvements**
- **Priority Fallback System**: `get_user_meta(new_format) → get_user_meta(old_format) → transient_data → calculated_fallback`
- **Template Data Flow**: Results and details pages use identical data sources and processing logic
- **Performance Optimization**: Reduced transient dependency, improved cache efficiency
- **Code Maintainability**: Centralized meta key management via constants class

### Files Modified
- `ennulifeassessments.php` - Version bump and core improvements
- `includes/class-assessment-shortcodes.php` - Database-first scoring retrieval and dual-format storage
- `includes/class-enhanced-admin.php` - Admin profile score display synchronization
- `templates/assessment-results.php` - Modern card-based UI implementation
- `templates/assessment-details-page.php` - Modern card-based UI implementation with assessment-specific context
- `assets/css/modern-scoring-ui.css` - Enhanced styling for new card layouts

### Impact
- **100% Score Accuracy**: Eliminates discrepancies between results page, details page, and admin profile
- **Enhanced User Experience**: Modern, intuitive interface with consistent data presentation
- **Improved Data Reliability**: Database values always supersede temporary transient data as requested
- **Future-Proof Architecture**: Scalable scoring system with backward compatibility

---

## [64.64.0] - 2025-08-10

### Added
- **Microsoft Teams Integration**: Complete real-time notification system
  - 9 specialized notification channels (Patient Assessments, Biomarker Updates, Critical Alerts, New Registrations, Appointments, Patient Success, Revenue Metrics, System Alerts, Daily Summaries)
  - n8n middleware platform for seamless Teams connectivity
  - Smart channel routing based on notification type
  - Rich message formatting with proper line breaks, emojis, and structured data
  - OAuth 2.0 Microsoft Graph API authentication
  - Real-time webhook processing triggered by WordPress hooks

### Technical Implementation
- **New Class**: `ENNU_N8N_Integration` - Complete WordPress to n8n Teams integration
- **Webhook Architecture**: Single master webhook with intelligent routing to appropriate Teams channels
- **Data Processing**: Flattened data structure optimized for n8n compatibility
- **Error Handling**: Comprehensive logging and fallback mechanisms
- **Performance**: Async webhook calls with 30-second timeout
- **Security**: HIPAA-compliant data transmission with proper authentication

### Files Added
- `includes/class-n8n-integration.php` - WordPress integration class
- `n8n-workflows/TEAMS-NATIVE-FORMAT.json` - Final working n8n workflow
- `ennulife-teams-config.json` - Teams channel configuration

### Business Value
- Real-time team awareness for critical patient events
- Immediate alerts enabling faster patient care response
- Enhanced collaboration through centralized Teams communication
- Live revenue and performance tracking visibility
- Team recognition of patient transformation achievements

## [64.70.2] - 2025-08-09
### Changed
- Admin Docs: Biomarkers Docs and Biomarker Ranges Docs now programmatically generate from live `ENNU_Recommended_Range_Manager` configuration (units, normal/optimal/critical, descriptions, vectors)
- Admin Docs: Scoring Docs now programmatically generate from live `ENNU_Scoring_System` (pillar map, assessments→categories, engines present, health goal boosts, caching notes)
- Admin Docs: Symptom Flagging Docs now programmatically generate mapping table from `ENNU_Centralized_Symptoms_Manager::get_symptom_biomarker_mapping()` and describe ONE LOG resolution and flag flow
- Admin Docs: Comprehensive Docs now programmatically summarize live Global Fields, Scoring pillars/engines, Ranges/Targets, and Centralized Symptoms
- Admin Docs: Assessments Docs now programmatically list all assessments from live definitions with shortcodes, question counts, and whether globals are used

### Fixed
- Documentation accuracy: removed hardcoded examples; tables reflect current code configuration

## [64.70.1] - 2025-08-09
### Fixed
- Redirects consistently prefer results pages post-submit; details only for deep dives
- Pillar scores persisted per assessment; details/results read real stored values
- Details page chart uses unified canvas `assessmentTimelineChart` and external JS boot
- Global field auto-skip: added data marker for contact form so JS never re-prompts when saved
- Biomarkers auto-sync from globals (weight/height/BMI/age) remains universal across assessments
- Performance: removed transient force-clears in scoring to respect caching
- Stability: removed duplicate legacy AJAX handler require

### Added
- Admin: Nine documentation submenus under ENNU Life →
  - Comprehensive Docs
  - Biomarkers Docs
  - Biomarker Ranges Docs
  - Symptom Flagging Docs
  - Scoring Docs
  - Assessments Docs
  - Labcorp Upload Docs
  - HubSpot Syncing Docs
  - SOP Docs

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