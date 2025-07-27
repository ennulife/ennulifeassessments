=== ENNU Life Assessments ===
Contributors: luisescobar
Donate link: https://ennulife.com/donate
Tags: health, assessment, biomarkers, wellness, scoring, healthcare, medical, symptoms, goals
Requires at least: 5.0
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 64.2.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Comprehensive health assessment system with Four-Engine scoring, biomarker integration, and personalized health insights.

== Description ==

ENNU Life Assessments is a comprehensive health assessment plugin that provides advanced scoring algorithms, biomarker integration, and personalized health insights for WordPress websites.

= Key Features =

* **Four-Engine Scoring Symphony**: Advanced scoring system with Quantitative, Qualitative, Objective, and Intentionality engines
* **11 Assessment Types**: Weight Loss, Sleep, Hormone, Nutrition, Fitness, Stress, Energy, Recovery, Longevity, Performance, and Wellness assessments
* **Biomarker Integration**: Complete lab results import and analysis system
* **Symptom Tracking**: Comprehensive symptom-to-biomarker correlation system
* **Health Goals**: Personalized goal setting with alignment scoring
* **Professional Dashboard**: Medical-grade user interface with detailed analytics
* **Security First**: Enterprise-grade security with CSRF protection and input validation
* **Performance Optimized**: Advanced caching and database optimization

= Four-Engine Scoring System =

1. **Quantitative Engine**: Base pillar scores from assessment responses
2. **Qualitative Engine**: Symptom-based penalty system for realistic scoring
3. **Objective Engine**: Biomarker-based adjustments from lab results
4. **Intentionality Engine**: Goal alignment boosts for personalized scoring

= Assessment Types =

* Weight Loss Assessment
* Sleep Quality Assessment
* Hormone Balance Assessment
* Nutrition Assessment
* Fitness Assessment
* Stress Management Assessment
* Energy Optimization Assessment
* Recovery Assessment
* Longevity Assessment
* Performance Assessment
* General Wellness Assessment

= Professional Features =

* Lab results import and management
* Doctor recommendations system
* Biomarker tracking and analysis
* Symptom correlation mapping
* Health goal alignment scoring
* Comprehensive reporting dashboard
* User progress tracking
* Professional medical interface

= Developer Features =

* Comprehensive REST API
* Extensive hook system
* Modular architecture
* Performance monitoring
* Security validation
* Automated testing suite
* Detailed documentation

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ennu-life-assessments` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->ENNU Life screen to configure the plugin.
4. Add assessment forms to your pages using the provided shortcodes.

= Shortcodes =

* `[ennu_assessment_form type="weight_loss"]` - Display assessment form
* `[ennu_assessment_results]` - Display assessment results
* `[ennu_user_dashboard]` - Display user dashboard
* `[ennu_assessment_details]` - Display detailed assessment information

== Frequently Asked Questions ==

= What is the Four-Engine Scoring Symphony? =

The Four-Engine Scoring Symphony is our advanced scoring system that combines four different engines to provide the most accurate health assessment scores:

1. Quantitative Engine: Calculates base scores from assessment responses
2. Qualitative Engine: Applies symptom-based penalties for realistic scoring
3. Objective Engine: Incorporates lab results for objective adjustments
4. Intentionality Engine: Adds goal alignment boosts for personalized results

= How do I import lab results? =

Lab results can be imported through the admin interface using our biomarker management system. The system supports JSON import format and includes validation for all major biomarkers.

= Is the plugin HIPAA compliant? =

The plugin includes enterprise-grade security features and follows healthcare data protection best practices. For full HIPAA compliance, additional server-level configurations may be required.

= Can I customize the assessment questions? =

Yes, the plugin includes a comprehensive configuration system that allows customization of assessment questions, scoring algorithms, and user interface elements.

== Screenshots ==

1. User Dashboard with Four-Engine scoring breakdown
2. Assessment form with professional medical interface
3. Biomarker management and lab results import
4. Comprehensive reporting and analytics
5. Admin interface for system configuration

== Changelog ==

= 64.5.19 =
* FIXED: Fatal error in database optimizer due to incorrect cache interface usage
* ENHANCED: Updated database optimizer to use WordPress transients instead of custom cache object
* IMPROVED: Cache implementation now uses standard WordPress caching methods
* FIXED: get_user_meta_batch() method now handles empty meta_keys array properly
* UPDATED: Plugin version to 64.5.19

= 64.5.18 =
* FIXED: Critical performance issue causing hundreds of slow database queries
* ENHANCED: Optimized centralized symptoms manager to use batch user meta retrieval
* ENHANCED: Replaced individual get_user_meta() calls with get_user_meta_batch() for better performance
* IMPROVED: Reduced database query count from hundreds to single digits for symptom processing
* IMPROVED: Eliminated N+1 query problem in symptom aggregation methods
* UPDATED: Plugin version to 64.5.18

= 64.2.6 =
* REORDERED: "My Biomarkers" tab moved to center position in user dashboard navigation
* ENHANCED: Improved visual hierarchy with biomarkers as the focal point
* IMPROVED: Better user experience with logical tab ordering (Assessments → Biomarkers → Symptoms)
* UPDATED: Plugin version to 64.2.6

= 64.2.5 =
* REMOVED: "My Trends" tab from user dashboard shortcode
* ENHANCED: Streamlined user dashboard interface with 3 core tabs (My Assessments, My Symptoms, My Biomarkers)
* IMPROVED: User experience by removing trends tab for cleaner, more focused navigation
* UPDATED: Plugin version to 64.2.5

= 64.2.4 =
* REMOVED: "My Profile" and "My New Life" tabs from user dashboard shortcode
* ENHANCED: Streamlined user dashboard interface with 4 core tabs (My Assessments, My Symptoms, My Biomarkers, My Trends)
* IMPROVED: User experience by removing non-essential tabs for cleaner navigation
* UPDATED: Plugin version to 64.2.4

= 64.4.0 =
* MAJOR: Complete code refactoring and architecture modernization
* NEW: Dedicated Form Handler class with clean separation of concerns
* NEW: Dedicated AJAX Handler class with comprehensive security
* NEW: Dedicated Shortcode Manager class for better organization
* NEW: Unified data persistence system with transaction management
* ENHANCED: Robust error handling and validation throughout
* ENHANCED: Improved code maintainability and readability
* ENHANCED: Better separation of business logic from presentation
* FIXED: Critical data saving issues with unified persistence layer
* UPDATED: Plugin version to 64.4.0
* IMPROVED: Overall code quality and architecture following WordPress best practices

= 64.3.10 =
* CLEANED: Removed non-functional "Save All Changes", "Import Lab Data", and "Export Data" buttons
* FIXED: Eliminated undefined method calls that were causing linter errors
* IMPROVED: User interface now only shows functional elements
* ENHANCED: Code maintainability by removing broken functionality

= 64.3.9 =
* FIXED: Fatal error "Call to undefined method ENNU_AI_Medical_Team_Reference_Ranges::get_medical_specialists()"
* IMPROVED: User profile pages now load without errors
* ENHANCED: Replaced dynamic method call with direct category mapping for better stability

= 64.3.8 =
* FIXED: Array to string conversion warnings in centralized symptoms display
* IMPROVED: Proper handling of nested array structures in symptoms by assessment
* ENHANCED: Support for multiple symptom data formats (arrays with name keys, indexed arrays, strings)

= 64.3.4 =
* ENHANCED: Weight and BMI auto-sync from global fields to biomarker display
* ENHANCED: Biomarker Manager now merges auto-sync data with primary biomarker data
* ENHANCED: Auto-sync triggered when user dashboard loads to ensure current values
* ENHANCED: Weight and BMI now show current values from global fields in biomarker panels
* NEW: Target Weight Calculator - automatically calculates target weight from weight loss assessment
* NEW: Target weight calculated from current weight (wl_q1) and weight loss goal (wl_q2)
* NEW: Target weight displayed on biomarker ruler with target dot indicator
* UPDATED: Weight biomarker configuration to use pounds (lbs) instead of kilograms
* UPDATED: Weight ranges converted to pounds for better user experience
* FIXED: Data synchronization between global fields and biomarker display system
* UPDATED: Plugin version to 64.3.4
* IMPROVED: User experience with real-time weight and BMI values in biomarker dashboard

= 64.3.3 =
* FIXED: PHP warnings for undefined array keys in Enhanced Dashboard Manager
* FIXED: foreach() argument must be of type array|object, null given errors
* FIXED: Undefined array key "section_details" and "icon" warnings
* ENHANCED: Comprehensive error handling in Profile Completeness Tracker
* ENHANCED: Safe defaults and fallback values for all array operations
* ENHANCED: Type checking and validation for completeness data structures
* ENHANCED: Exception handling with proper error logging
* UPDATED: Plugin version to 64.3.3
* IMPROVED: Robustness and stability of profile completeness display

= 64.3.2 =
* COMPLETED: Profile Completeness Tracker with comprehensive functionality
* ENHANCED: Complete profile completeness calculation with weighted scoring
* ENHANCED: Advanced section tracking for demographics, goals, assessments, symptoms, and biomarkers
* ENHANCED: Intelligent recommendation system with priority-based suggestions
* ENHANCED: Visual profile completeness display with progress circles and section breakdowns
* ENHANCED: Data accuracy level assessment (excellent, high, medium, moderate, low)
* ENHANCED: Real-time completeness tracking and user guidance
* FIXED: My Profile tab loading issues with enhanced error handling
* UPDATED: Plugin version to 64.3.2
* IMPROVED: User experience with actionable recommendations and progress visualization

= 64.3.1 =
* FIXED: Critical PHP 8.1+ compatibility issues in user dashboard template
* FIXED: Unsupported operand types: null - string errors in biomarker calculations
* FIXED: Unsupported operand types: int - string errors in mathematical operations
* FIXED: Cannot access offset of type array in isset or empty errors in enhanced admin
* ENHANCED: Comprehensive type safety with proper null coalescing and type casting
* ENHANCED: Added extensive error handling for biomarker range calculations
* ENHANCED: Improved mathematical operation safety with float type casting
* ENHANCED: Added fallback values for all biomarker calculations
* UPDATED: Plugin version to 64.3.1
* IMPROVED: User dashboard tab functionality now working properly
* IMPROVED: Enhanced admin biomarker management stability

= 64.4.4 =
* NEW: User-facing CSV import shortcode [ennu_user_csv_import] for self-service biomarker uploads
* NEW: Frontend import interface with modern, responsive design
* NEW: User authentication and permission handling for secure imports
* NEW: Comprehensive frontend validation and error handling
* NEW: Drag-and-drop file upload support for enhanced UX
* NEW: Real-time import feedback and progress indicators
* ENHANCED: Shortcode attributes for customization (show_instructions, show_sample, max_file_size)
* ENHANCED: Mobile-responsive design for all device types
* ENHANCED: Accessibility features and keyboard navigation
* FIXED: Improved security with proper nonce verification
* UPDATED: Plugin version to 64.4.4

= 64.4.3 =
* NEW: Streamlined CSV biomarker import functionality for current values only
* NEW: Dedicated CSV import interface with drag-and-drop support
* NEW: Comprehensive biomarker validation and error handling
* NEW: Import history tracking and logging system
* ENHANCED: User-friendly import interface with real-time feedback
* ENHANCED: Support for 40+ common biomarkers with proper units
* ENHANCED: Overwrite protection and score update options
* FIXED: Improved data validation and sanitization
* UPDATED: Plugin version to 64.4.3
* IMPROVED: Streamlined import process focused on current biomarker values

= 64.2.7 =
* NEW: Beautiful score presentation system with interactive orbs and animations
* NEW: Enhanced assessment results page with modern score displays
* NEW: Updated user dashboard with improved score presentations
* NEW: Score presentation shortcode with customizable attributes
* ENHANCED: Score interpretation with color-coded levels and descriptions
* ENHANCED: Pillar score breakdowns with individual orb displays
* ENHANCED: Score history tracking with interactive charts
* ENHANCED: Responsive design for all score presentation components
* ENHANCED: AJAX-powered score refresh functionality
* ENHANCED: Smooth animations and hover effects
* FIXED: Score display consistency across all templates
* UPDATED: Plugin version to 64.2.7
* IMPROVED: User experience with modern, engaging score presentations

= 64.3.0 =
* MAJOR: Completely redesigned symptom tracking system
* ENHANCED: Symptoms now only move to history when biomarkers are unflagged by admin
* ENHANCED: Added automatic symptom-to-history movement when biomarkers are unflagged
* ENHANCED: Symptoms no longer automatically expire based on time
* ENHANCED: Added biomarker flag removal hook integration with symptom management
* FIXED: Critical array access error in admin biomarker display (PHP 8.1+ compatibility)
* ENHANCED: Improved symptom-biomarker association logic
* ENHANCED: Added comprehensive symptom history tracking with admin action reasons
* UPDATED: Plugin version to 64.3.0
* IMPROVED: Admin control over symptom lifecycle through biomarker flag management

= 64.2.4 =
* FIXED: Critical data synchronization issue between admin profile and user dashboard
* ENHANCED: Admin centralized symptoms section now uses same data source as user dashboard
* ENHANCED: Admin symptoms display now shows severity and frequency information
* ENHANCED: Added symptoms by assessment source breakdown in admin
* UPDATED: Plugin version to 64.2.4
* IMPROVED: Data consistency between admin and user interfaces

= 64.2.3 =
* FIXED: Critical PHP fatal errors in user dashboard template (ucfirst() receiving arrays)
* FIXED: Critical PHP fatal errors in centralized symptoms manager (strtolower() receiving arrays)
* ENHANCED: Type safety checks for symptom severity and frequency data
* ENHANCED: Array validation before string function calls
* UPDATED: Plugin version to 64.2.3
* IMPROVED: Error handling and data validation throughout symptom system

= 64.2.0 =
* NEW: AI-Powered Target Value Calculator with personalized biomarker targets
* NEW: Enhanced save button functionality with profile default updates
* NEW: Linear transformation methods for biomarker value conversions
* NEW: Comprehensive target value validation and safety bounds
* NEW: Bulk user profile target value updates from range management
* ENHANCED: Biomarker range management with demographic adjustments
* ENHANCED: Save button UI with profile update warnings
* ENHANCED: Target value calculation based on current values and optimal ranges
* ENHANCED: Confidence scoring for AI-generated target values
* ENHANCED: Biomarker-specific adjustments for hormones, cardiovascular, and other markers
* FIXED: Target value population in user profiles
* UPDATED: Plugin version to 64.2.0
* DOCUMENTED: Complete target value calculation methodology

= 62.2.9 =
* MAJOR: Implemented complete Four-Engine Scoring Symphony
* NEW: Added Qualitative Engine with symptom penalty system
* NEW: Added Objective Engine with biomarker integration
* ENHANCED: Comprehensive biomarker management system
* ENHANCED: Advanced security with CSRF protection
* ENHANCED: Performance optimization with caching
* ENHANCED: Complete testing infrastructure
* FIXED: Database query optimization (N+1 problem resolved)
* UPDATED: Modern JavaScript implementation
* UPDATED: Enhanced user dashboard with real-time updates

= 62.2.8 =
* UPDATED: Plugin version and author information
* UPDATED: Documentation consistency across codebase
* MAINTAINED: Complete changelog history

= 62.2.7 =
* CREATED: Comprehensive AI onboarding instructions
* DOCUMENTED: Complete plugin architecture overview
* ESTABLISHED: Development guidelines and best practices

= 62.2.6 =
* IMPLEMENTED: Advanced health goal system
* ENHANCED: User dashboard functionality
* IMPROVED: Assessment calculation accuracy

= 62.2.5 =
* ADDED: Comprehensive biomarker definitions
* ENHANCED: Lab results processing
* IMPROVED: Data validation and security

== Upgrade Notice ==

= 62.2.9 =
Major update with complete Four-Engine Scoring Symphony implementation. Includes advanced biomarker integration, enhanced security, and performance optimizations. Backup recommended before upgrade.

= 62.2.8 =
Version consistency update. Safe to upgrade.

== Support ==

For support, please visit https://ennulife.com/support or contact support@ennulife.com.

== Privacy Policy ==

This plugin collects and processes health assessment data to provide personalized health insights. All data is stored securely and is not shared with third parties without explicit consent. For full privacy policy, visit https://ennulife.com/privacy.

== Credits ==

Developed by Luis Escobar and the ENNU Life team.
Special thanks to the WordPress community for their continued support and contributions.
