# ENNU Life Assessments

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/plugins/)
[![Version](https://img.shields.io/badge/Version-64.53.3-green.svg)](https://github.com/ennulife/ennulifeassessments)
[![License](https://img.shields.io/badge/License-GPL--2.0+-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![Build Status](https://img.shields.io/badge/Build-Passing-brightgreen.svg)](https://github.com/ennulife/ennulifeassessments/actions)
[![Security](https://img.shields.io/badge/Security-Audited-brightgreen.svg)](https://github.com/ennulife/ennulifeassessments/security)

**Comprehensive health assessment and biomarker management system for WordPress**

[Live Demo](https://ennulife.com) • [Documentation](https://docs.ennulife.com) • [Support](https://support.ennulife.com) • [Community](https://community.ennulife.com)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Demo & Screenshots](#demo--screenshots)
- [Complete User Journey](#complete-user-journey)
- [Complete Admin Journey](#complete-admin-journey)
- [Technical Architecture](#technical-architecture)
- [Advanced Features](#advanced-features)
- [Installation & Setup](#installation--setup)
- [Configuration](#configuration)
- [Usage & Features](#usage--features)
- [API Reference](#api-reference)
- [Integration Ecosystem](#integration-ecosystem)
- [Security & Compliance](#security--compliance)
- [Performance & Optimization](#performance--optimization)
- [Troubleshooting](#troubleshooting)
- [Development](#development)
- [Contributing](#contributing)
- [Support & Community](#support--community)
- [Changelog](#changelog)
- [License](#license)

---

## 🎯 Overview

ENNU Life Assessments transforms WordPress into a comprehensive health assessment platform, enabling healthcare providers to deliver personalized wellness evaluations, track biomarkers, and manage patient journeys through an integrated ecosystem.

### What This Plugin Does
ENNU Life Assessments is a sophisticated WordPress plugin that creates a complete health assessment ecosystem. It provides:

1. **Interactive Health Assessments**: 11 specialized assessment types with dynamic form rendering
2. **Advanced Scoring System**: Multi-engine scoring combining quantitative, qualitative, objective, and intentionality factors
3. **Biomarker Management**: Comprehensive lab result tracking with reference ranges and trend analysis
4. **CRM Integration**: Seamless HubSpot synchronization with custom field mapping
5. **User Dashboard**: Personalized health insights, progress tracking, and trend visualization
6. **Admin Management**: Comprehensive backend for healthcare providers with analytics and reporting
7. **Consultation Booking**: Integrated booking system with pre-filled assessment data
8. **Data Export/Import**: Full data portability for compliance and analysis
9. **Multi-language Support**: Internationalization ready for global deployment
10. **White-label Capabilities**: Complete branding customization for agencies
11. **Slack Notifications**: Real-time notifications for assessment completions and health alerts
12. **HIPAA Compliance**: Full medical data encryption and audit trails
13. **REST API**: Comprehensive API endpoints for all functionality
14. **Trends Visualization**: Advanced charts and data visualization system
15. **Advanced Analytics**: User behavior tracking, conversion analysis, and business intelligence
16. **AI/ML Manager**: Personalized recommendations and predictive analytics
17. **Performance Optimization**: Caching, database optimization, and asset management

### Core Value Proposition
- **11 Specialized Health Assessments**: From hair loss to hormone optimization, each with unique scoring algorithms
- **Multi-Engine Scoring System**: Quantitative (assessment responses), qualitative (symptom penalties), objective (biomarker adjustments), and intentionality (goal-based boosts) factors
- **Biomarker Management**: Lab result tracking with reference ranges, trend analysis, and health insights
- **HubSpot Integration**: Seamless CRM synchronization with automatic contact creation and field mapping
- **User Dashboard**: Personalized health insights, progress tracking, and consultation booking
- **Admin Management**: Comprehensive backend for healthcare providers with user management, analytics, and reporting
- **Security & Compliance**: GDPR-ready with data export/deletion, HIPAA-compliant data handling
- **Performance Optimized**: Caching, database optimization, and scalable architecture
- **Developer Friendly**: Extensive hooks, filters, and API endpoints for customization
- **Slack Notifications**: Real-time alerts for assessment completions and critical health events
- **REST API**: Comprehensive API endpoints for third-party integrations and mobile apps
- **Trends Visualization**: Advanced charts and data visualization for health tracking
- **Advanced Analytics**: User behavior tracking, conversion analysis, and business intelligence
- **AI/ML Manager**: Personalized recommendations and predictive health analytics

### Target Users & Use Cases

#### **Healthcare Providers**
- **Medical Clinics**: Comprehensive patient assessment and tracking
- **Telemedicine Platforms**: Remote health evaluations and monitoring
- **Wellness Centers**: Holistic health assessments and progress tracking
- **Specialized Practices**: Dermatology, endocrinology, cardiology, etc.
- **Health Coaches**: Client assessment and progress monitoring
- **Nutritionists**: Dietary assessment and biomarker tracking

#### **Patients/Users**
- **Health-Conscious Individuals**: Self-assessment and wellness tracking
- **Chronic Condition Patients**: Ongoing health monitoring and assessment
- **Preventive Care Seekers**: Early detection and health optimization
- **Fitness Enthusiasts**: Performance tracking and health optimization
- **Aging Population**: Comprehensive health monitoring and assessment

#### **Administrators**
- **Practice Managers**: User management, reporting, and system administration
- **IT Staff**: Technical implementation, customization, and maintenance
- **Support Teams**: User assistance, troubleshooting, and system monitoring
- **Marketing Teams**: Lead generation, conversion tracking, and campaign management
- **Compliance Officers**: Data management, privacy, and regulatory compliance

### Industry Applications

#### **Healthcare & Medical**
- Primary care practices
- Specialist clinics (dermatology, endocrinology, cardiology)
- Telemedicine platforms
- Health screening services
- Preventive care programs
- Chronic disease management

#### **Wellness & Fitness**
- Wellness centers
- Fitness studios
- Nutrition consulting
- Health coaching
- Corporate wellness programs
- Spa and wellness resorts

#### **Technology & Platforms**
- Health tech startups
- Digital health platforms
- Mobile health apps
- Wearable device integration
- Health analytics platforms
- Medical device companies

#### **Education & Research**
- Medical schools
- Research institutions
- Clinical trials
- Health education programs
- Public health initiatives
- Academic research projects

---

## 🎥 Demo & Screenshots

> _Add your own GIFs or screenshots here for maximum impact!_

- **Assessment Flow Demo**
  ![Assessment Demo](assets/demo/assessment-flow.gif)
- **User Dashboard Walkthrough**
  ![Dashboard Demo](assets/demo/dashboard-walkthrough.gif)
- **Admin Panel Overview**
  ![Admin Demo](assets/demo/admin-panel.gif)

---

## 👤 Complete User Journey

### **Scenario 1: First-Time User (Anonymous)**

#### **Step 1: Discovery & Landing**
- **Entry Point**: User arrives via search engine, social media, direct link, or referral
- **Landing Page**: Sees homepage with assessment options, value proposition, and testimonials
- **Assessment Selection**: Clicks on specific assessment (e.g., "Hair Loss Assessment")
- **Page Load**: Lands on assessment page with `[ennu-hair]` shortcode rendered
- **Initial Experience**: Sees professional form interface with progress indicator

#### **Step 2: Assessment Initiation**
- **Form Display**: Assessment form loads with first question visible
- **Progress Tracking**: Progress bar shows completion percentage
- **Real-time Validation**: Form validates input as user types
- **Auto-save**: Progress automatically saved every 30 seconds
- **Navigation**: User can move between questions using previous/next buttons

#### **Step 3: Question-by-Question Flow**
- **Question Types**: Radio buttons, checkboxes, text inputs, dropdowns, date pickers
- **Conditional Logic**: Questions appear/disappear based on previous answers
- **Help Text**: Contextual help and explanations for complex questions
- **Mobile Responsive**: Form adapts to mobile devices with touch-friendly interface
- **Accessibility**: Screen reader compatible with proper ARIA labels

#### **Step 4: Assessment Completion**
- **Final Review**: User reviews all answers before submission
- **Validation Check**: System ensures all required fields are completed
- **Submission Process**: User clicks submit, data is processed
- **Score Calculation**: Four-engine scoring system processes responses
- **Results Generation**: Personalized results page is generated

#### **Step 5: Results & Registration**
- **Immediate Results**: User sees instant assessment results with scores
- **Personalized Insights**: Health insights and recommendations displayed
- **Registration Prompt**: System prompts user to create account to save results
- **Account Creation**: User creates account with email/password or social login
- **Data Preservation**: Assessment data is linked to new account

#### **Step 6: Account Setup**
- **Welcome Email**: User receives welcome email with login credentials
- **Dashboard Access**: User can access personalized dashboard
- **Data Sync**: Assessment data is synced to user profile
- **HubSpot Integration**: Contact record created in HubSpot automatically
- **Next Steps**: User guided to complete additional assessments or book consultation

### **Scenario 2: Returning User (Logged In)**

#### **Step 1: Dashboard Access**
- **Login Process**: User logs in via WordPress login or social authentication
- **Dashboard Redirect**: Automatically redirected to personalized dashboard
- **Overview Display**: Dashboard shows completed assessments, scores, and recommendations
- **Quick Actions**: Prominent buttons for new assessments, consultation booking, profile editing

#### **Step 2: Assessment Management**
- **Assessment History**: View all completed assessments with dates and scores
- **Retake Assessments**: Option to retake assessments to track progress
- **Comparison View**: Side-by-side comparison of previous and current results
- **Trend Analysis**: Visual charts showing health trends over time
- **Progress Tracking**: Achievement badges and progress indicators

#### **Step 3: Biomarker Management**
- **Upload Interface**: User can upload lab results via `[ennu-biomarkers]` shortcode
- **File Support**: Accepts PDF, CSV, and image formats
- **Data Extraction**: System extracts biomarker data from uploaded files
- **Reference Ranges**: Results compared against normal reference ranges
- **Trend Visualization**: Charts showing biomarker trends over time
- **Health Insights**: Personalized insights based on biomarker data

#### **Step 4: Consultation Booking**
- **Booking Interface**: Access to consultation booking via `[ennu-{type}-consultation]` shortcodes
- **Pre-filled Forms**: Assessment data automatically pre-fills booking forms
- **Calendar Integration**: Integration with Google Calendar, Outlook, or booking systems
- **Confirmation Process**: Email confirmations and calendar invites sent
- **Reminder System**: Automated reminders before scheduled consultations

#### **Step 5: Profile Management**
- **Personal Information**: Edit name, email, phone, address
- **Health Goals**: Set and track personal health goals
- **Preferences**: Notification preferences, privacy settings
- **Data Export**: Download personal health data for backup or transfer
- **Account Deletion**: Option to delete account and all associated data

### **Scenario 3: Premium User**

#### **Step 1: Enhanced Features**
- **Advanced Analytics**: Detailed health reports with professional insights
- **Priority Booking**: Expedited consultation booking with preferred providers
- **Detailed Reports**: Comprehensive health reports with actionable recommendations
- **Custom Assessments**: Access to specialized assessments not available to basic users
- **White-label Experience**: Customized branding and user experience

#### **Step 2: Integration Benefits**
- **CRM Sync**: Seamless integration with HubSpot, Salesforce, or other CRMs
- **Automated Follow-ups**: Automated email sequences based on assessment results
- **Advanced Reporting**: Detailed analytics and reporting for health professionals
- **API Access**: Programmatic access to assessment data and results
- **Custom Workflows**: Tailored user journeys and assessment flows

### **Scenario 4: Health Professional User**

#### **Step 1: Professional Dashboard**
- **Client Management**: View and manage client assessments and progress
- **Assessment Creation**: Create custom assessments for specific client needs
- **Result Analysis**: Detailed analysis of client assessment results
- **Treatment Planning**: Create treatment plans based on assessment data
- **Progress Monitoring**: Track client progress over time

#### **Step 2: Client Communication**
- **Secure Messaging**: Built-in secure messaging system with clients
- **Assessment Sharing**: Share assessment results with clients
- **Treatment Updates**: Send treatment updates and recommendations
- **Appointment Scheduling**: Integrated appointment scheduling system
- **Document Management**: Secure storage and sharing of health documents

### **Scenario 5: Administrator User**

#### **Step 1: System Management**
- **User Management**: View, edit, and manage all user accounts
- **Assessment Configuration**: Configure assessment types, questions, and scoring
- **System Settings**: Manage plugin settings, integrations, and security
- **Data Export**: Export user data for analysis or compliance
- **System Monitoring**: Monitor system performance and usage

#### **Step 2: Analytics & Reporting**
- **Usage Analytics**: Track assessment completion rates and user engagement
- **Performance Metrics**: Monitor system performance and optimization
- **Compliance Reporting**: Generate reports for regulatory compliance
- **Custom Reports**: Create custom reports for specific business needs
- **Data Visualization**: Interactive charts and graphs for data analysis

### **Scenario 6: Technical User (Developer)**

#### **Step 1: API Integration**
- **REST API Access**: Programmatic access to all plugin functionality
- **Webhook Integration**: Real-time data synchronization with external systems
- **Custom Development**: Extend plugin functionality with custom code
- **Third-party Integrations**: Integrate with external health systems and platforms
- **Data Migration**: Tools for migrating data from other systems

#### **Step 2: Customization**
- **Theme Integration**: Customize appearance to match brand guidelines
- **Workflow Customization**: Modify assessment flows and user journeys
- **Scoring Algorithm**: Customize scoring algorithms for specific use cases
- **Integration Development**: Develop custom integrations with other systems
- **Performance Optimization**: Optimize plugin performance for high-traffic sites

### **Scenario 7: Compliance Officer**

#### **Step 1: Data Management**
- **Data Retention**: Configure data retention policies and schedules
- **Privacy Controls**: Manage user privacy settings and data access
- **Audit Logging**: Comprehensive audit logs for all system activities
- **Compliance Reporting**: Generate reports for regulatory compliance
- **Data Protection**: Implement data protection measures and encryption

#### **Step 2: Regulatory Compliance**
- **GDPR Compliance**: Tools for data export, deletion, and consent management
- **HIPAA Compliance**: Secure handling of health information
- **Audit Trails**: Complete audit trails for all data access and modifications
- **Security Monitoring**: Monitor system security and detect potential threats
- **Incident Response**: Procedures for handling data breaches and security incidents

---

## 👨‍⚕️ Complete Admin Journey

### **Scenario 1: Initial Setup**

#### **Step 1: Plugin Installation**
- **Download Process**: Download plugin ZIP from official source or GitHub
- **Upload Method**: WordPress Admin → Plugins → Add New → Upload Plugin
- **Activation Process**: Click "Activate Plugin" and see welcome screen
- **Database Setup**: Plugin automatically creates necessary database tables
- **File Permissions**: System checks and sets appropriate file permissions
- **Dependency Check**: Verifies WordPress version, PHP version, and required extensions

#### **Step 2: Initial Configuration**
- **Welcome Wizard**: Guided setup process for first-time installation
- **HubSpot Integration**: Configure HubSpot API credentials and field mapping
- **Assessment Setup**: Configure default assessment types and scoring weights
- **Branding Configuration**: Upload logo, set colors, customize appearance
- **Email Templates**: Configure welcome emails, assessment completion emails
- **User Roles**: Set up custom user roles and permissions
- **Security Settings**: Configure CSRF protection, rate limiting, and access controls

#### **Step 3: Page Setup & SEO**
- **Automatic Page Creation**: System creates assessment pages, dashboard, results pages
- **SEO Optimization**: Configure meta titles, descriptions, and structured data
- **Content Customization**: Edit page content, add custom text and images
- **URL Structure**: Configure clean URLs for assessments and results
- **Sitemap Generation**: Generate XML sitemap for search engines
- **Schema Markup**: Add structured data for rich snippets

#### **Step 4: Integration Setup**
- **HubSpot Configuration**: Set up API keys, webhooks, and field mapping
- **Email Service**: Configure SMTP settings for transactional emails
- **Analytics Integration**: Set up Google Analytics, Facebook Pixel tracking
- **Payment Processing**: Configure payment gateways for premium features
- **Booking System**: Integrate with calendar systems and booking platforms
- **CRM Integration**: Set up additional CRM integrations beyond HubSpot

### **Scenario 2: Daily Operations**

#### **Step 1: User Management**
- **User Dashboard**: View all users with assessment completion status
- **User Profiles**: Edit user information, reset passwords, manage roles
- **Assessment Data**: View individual user assessment results and history
- **Score Recalculation**: Manually recalculate scores for specific users
- **Data Export**: Export user data for analysis or compliance purposes
- **Bulk Operations**: Perform bulk actions on multiple users
- **User Analytics**: Track user engagement, completion rates, and trends

#### **Step 2: Assessment Management**
- **Submission Monitoring**: Real-time view of assessment submissions
- **Result Analysis**: Analyze assessment results and identify trends
- **Configuration Editing**: Modify assessment questions, scoring, and flow
- **Custom Assessments**: Create new assessment types for specific needs
- **Report Generation**: Generate detailed reports on assessment performance
- **Quality Control**: Review and approve assessment results
- **A/B Testing**: Test different assessment configurations

#### **Step 3: Biomarker Management**
- **Data Upload**: Upload lab results for individual users or bulk import
- **Reference Ranges**: Manage normal reference ranges for different biomarkers
- **Trend Analysis**: Analyze biomarker trends across user population
- **Alert System**: Set up alerts for abnormal biomarker values
- **Data Validation**: Validate uploaded biomarker data for accuracy
- **Export Capabilities**: Export biomarker data for external analysis
- **Integration**: Sync biomarker data with external lab systems

#### **Step 4: HubSpot Integration Management**
- **Sync Monitoring**: Monitor real-time sync status with HubSpot
- **Field Mapping**: Configure custom field mappings between systems
- **Contact Management**: Manage HubSpot contacts and deal creation
- **Workflow Automation**: Set up automated workflows based on assessment results
- **Error Resolution**: Troubleshoot sync errors and data inconsistencies
- **Performance Optimization**: Optimize sync performance for large datasets
- **Backup & Recovery**: Backup HubSpot data and sync history

#### **Step 5: Content Management**
- **Assessment Content**: Edit assessment questions, options, and scoring
- **Results Content**: Customize result pages and recommendations
- **Email Templates**: Manage all email templates and messaging
- **Help Content**: Create and manage help documentation and FAQs
- **Multilingual Support**: Manage translations for international users
- **Media Management**: Upload and manage images, videos, and documents
- **SEO Content**: Optimize content for search engines

### **Scenario 3: Advanced Administration**

#### **Step 1: Analytics & Reporting**
- **Dashboard Analytics**: Comprehensive analytics dashboard with key metrics
- **Custom Reports**: Create custom reports for specific business needs
- **Data Visualization**: Interactive charts and graphs for data analysis
- **Export Capabilities**: Export data in various formats (CSV, Excel, PDF)
- **Scheduled Reports**: Set up automated report generation and delivery
- **Performance Metrics**: Track system performance and user experience
- **Conversion Analytics**: Analyze conversion rates and user journeys

#### **Step 2: System Optimization**
- **Performance Monitoring**: Monitor system performance and identify bottlenecks
- **Database Optimization**: Optimize database queries and table structure
- **Caching Management**: Configure and monitor caching systems
- **Backup Management**: Set up automated backups and disaster recovery
- **Security Monitoring**: Monitor security events and potential threats
- **Update Management**: Manage plugin updates and compatibility testing
- **Load Testing**: Test system performance under high traffic conditions

#### **Step 3: Customization & Development**
- **Theme Integration**: Customize appearance to match brand guidelines
- **Workflow Customization**: Modify assessment flows and user journeys
- **API Development**: Develop custom API endpoints for external integrations
- **Plugin Extensions**: Create custom extensions and add-ons
- **Third-party Integrations**: Integrate with external systems and platforms
- **Custom Scoring**: Develop custom scoring algorithms for specific use cases
- **White-label Solutions**: Create white-label versions for resale

### **Scenario 4: Compliance & Security Administration**

#### **Step 1: Data Protection**
- **Privacy Controls**: Manage user privacy settings and data access
- **Data Retention**: Configure data retention policies and schedules
- **Encryption Management**: Implement and manage data encryption
- **Access Controls**: Manage user access and permission levels
- **Audit Logging**: Monitor and review system audit logs
- **Incident Response**: Handle security incidents and data breaches
- **Compliance Reporting**: Generate reports for regulatory compliance

#### **Step 2: Regulatory Compliance**
- **GDPR Compliance**: Implement data protection and privacy controls
- **HIPAA Compliance**: Secure handling of health information
- **Audit Trails**: Maintain complete audit trails for all activities
- **Data Export**: Provide data export capabilities for user requests
- **Data Deletion**: Implement secure data deletion procedures
- **Consent Management**: Manage user consent and preferences
- **Compliance Monitoring**: Monitor compliance with regulatory requirements

### **Scenario 5: Technical Administration**

#### **Step 1: API Management**
- **API Configuration**: Configure API endpoints and authentication
- **Rate Limiting**: Set up rate limiting and usage monitoring
- **Webhook Management**: Configure webhooks for real-time data sync
- **Documentation**: Maintain API documentation and examples
- **Testing**: Test API endpoints and integration functionality
- **Monitoring**: Monitor API usage and performance
- **Security**: Implement API security measures and access controls

#### **Step 2: Integration Management**
- **Third-party Integrations**: Manage integrations with external systems
- **Data Synchronization**: Monitor and troubleshoot data sync issues
- **Error Handling**: Implement error handling and recovery procedures
- **Performance Optimization**: Optimize integration performance
- **Testing**: Test integration functionality and data accuracy
- **Documentation**: Maintain integration documentation and procedures
- **Support**: Provide technical support for integration issues

### **Scenario 6: Business Intelligence Administration**

#### **Step 1: Data Analytics**
- **Business Intelligence**: Implement BI tools and dashboards
- **Predictive Analytics**: Develop predictive models for user behavior
- **Market Analysis**: Analyze market trends and user demographics
- **Competitive Analysis**: Monitor competitor activities and market position
- **ROI Analysis**: Analyze return on investment for different features
- **User Behavior Analysis**: Analyze user behavior and engagement patterns
- **Performance Optimization**: Optimize business performance based on data

#### **Step 2: Strategic Planning**
- **Feature Planning**: Plan new features based on user feedback and data
- **Roadmap Development**: Develop product roadmap and release schedule
- **Resource Planning**: Plan resource allocation and capacity management
- **Risk Management**: Identify and mitigate business risks
- **Growth Strategy**: Develop strategies for business growth and expansion
- **Partnership Development**: Develop partnerships and strategic alliances
- **Market Expansion**: Plan for market expansion and international growth

---

## 🏗️ Technical Architecture

### **Core Components**

#### **1. Assessment Engine (`class-assessment-shortcodes.php`)**
```php
class ENNU_Assessment_Shortcodes {
    // Handles form rendering and data collection
    public function render_assessment_shortcode($atts, $content, $tag)
    public function handle_assessment_submission()
    public function render_user_dashboard($atts)
    public function render_thank_you_page($atts)
    public function render_detailed_results_page($atts)
}
```
**Responsibilities:**
- Dynamic form rendering based on assessment configuration
- Real-time validation and error handling
- Progress tracking and auto-save functionality
- Mobile-responsive design implementation
- Accessibility compliance (WCAG 2.1 AA)
- Multi-language support and internationalization

#### **2. Four-Engine Scoring System**

**Quantitative Engine (`class-scoring-system.php` - 917 lines)**
```php
class ENNU_Scoring_System {
    public static function calculate_and_save_all_user_scores($user_id, $force_recalc = false)
    public static function calculate_scores_for_assessment($assessment_type, $form_data)
    public static function get_all_definitions() // Loads 11 assessment configs
    public static function get_health_pillar_map() // Mind, Body, Lifestyle, Aesthetics
    public static function get_health_goal_definitions() // Goal-based scoring
}
```
**Features:**
- Base scores from assessment responses
- Category-based scoring with weighted calculations
- Pillar mapping (Mind 25%, Body 35%, Lifestyle 25%, Aesthetics 15%)
- Score interpretation and caching (12-hour transients)
- Support for 11 assessment types

**Qualitative Engine (`class-qualitative-engine.php` - 162 lines)**
```php
class ENNU_Qualitative_Engine {
    public function apply_pillar_integrity_penalties($base_pillar_scores)
    private function identify_triggered_categories()
    private function calculate_pillar_penalties($triggered_categories)
    private function determine_category_severity($category, $symptoms)
}
```
**Features:**
- Symptom-based penalty system
- Penalty matrix with severity/frequency calculations
- Category-to-pillar impact mapping
- Non-cumulative penalty application
- Comprehensive penalty logging

**Objective Engine (`class-objective-engine.php` - 260 lines)**
```php
class ENNU_Objective_Engine {
    public function apply_biomarker_actuality_adjustments($base_pillar_scores)
    private function calculate_biomarker_adjustment($biomarker_name, $biomarker_data)
    private function classify_biomarker_range($value, $profile)
    private function get_adjustment_value($range_classification, $profile)
}
```
**Features:**
- Biomarker-based adjustments
- Range classification (optimal, suboptimal, critical)
- Pillar impact calculations
- Adjustment value determination
- Support for 50+ biomarkers

**Intentionality Engine (`class-intentionality-engine.php` - 276 lines)**
```php
class ENNU_Intentionality_Engine {
    public function apply_goal_alignment_boost()
    private function normalize_pillar_name($pillar_name)
    public function calculate_potential_boost($all_possible_goals = null)
    public static function goals_affect_scoring($user_goals, $goal_definitions)
}
```
**Features:**
- Goal alignment boosts (+5% non-cumulative)
- Health goal validation
- Boost rule configuration
- Maximum score capping (10.0)
- Comprehensive boost logging

#### **3. Assessment Types (11 Total)**

**Welcome Assessment** (`welcome.php` - 50 lines)
```php
return array(
    'title' => 'Welcome Assessment',
    'assessment_engine' => 'qualitative',
    'questions' => array(
        'welcome_q1' => array('type' => 'dob_dropdowns', 'global_key' => 'date_of_birth'),
        'welcome_q2' => array('type' => 'radio', 'global_key' => 'gender'),
        'welcome_q3' => array('type' => 'multiselect', 'global_key' => 'health_goals')
    )
);
```

**Weight Loss Assessment** (`weight-loss.php` - 285 lines)
```php
return array(
    'title' => 'Weight Loss Assessment',
    'assessment_engine' => 'quantitative',
    'questions' => array(
        'wl_q1' => array('type' => 'height_weight', 'global_key' => 'height_weight'),
        'wl_q2' => array('type' => 'radio', 'scoring' => array('category' => 'Motivation & Goals')),
        'wl_q3' => array('type' => 'radio', 'scoring' => array('category' => 'Nutrition')),
        // ... 8 more questions with scoring
    )
);
```

**Other Assessments:**
- **Sleep Assessment** (`sleep.php` - 377 lines)
- **Hormone Assessment** (`hormone.php` - 321 lines)
- **Health Optimization** (`health-optimization.php` - 335 lines)
- **ED Treatment** (`ed-treatment.php` - 226 lines)
- **Hair Assessment** (`hair.php` - 209 lines)
- **Skin Assessment** (`skin.php` - 385 lines)
- **Menopause Assessment** (`menopause.php` - 238 lines)
- **Testosterone Assessment** (`testosterone.php` - 231 lines)
- **Health Assessment** (`health.php` - 323 lines)

#### **4. HubSpot Integration (`class-hubspot-bulk-field-creator.php` - 8385 lines)**
```php
class ENNU_HubSpot_Bulk_Field_Creator {
    private $api_base_url = 'https://api.hubapi.com';
    private $supported_field_types = array('string', 'number', 'date', 'enumeration', 'boolean');
    
    public function bulk_create_fields($object_type, $fields)
    public function sync_assessment_to_hubspot($user_id, $assessment_type, $form_data)
    public function create_custom_object_record($user_id, $assessment_type, $form_data)
    public function get_assessment_specific_fields_from_form($user_id, $assessment_type, $form_data)
}
```
**Field Infrastructure:**
- **312 Total Fields**: 256 custom object + 56 contact fields
- **Custom Object (2-47128703)**: Detailed assessment data storage
- **Contact Object**: Core assessment data and contact enrichment
- **Property Groups**: Assessment-specific organization

**Field Types:**
- Text fields (255 character limit)
- Number fields (with validation)
- Date fields (with formatting)
- Enumeration fields (dropdowns, radio, checkbox)
- Boolean fields (true/false)

**API Integration:**
- OAuth 2.0 authentication
- Private App support
- Rate limiting (1 second delays)
- Error handling and retry mechanisms
- Real-time field creation and validation

**Assessment Field Mapping:**
- Question fields: `wl_q1`, `sleep_q2`, `hormone_q3`
- Score fields: `wl_score`, `sleep_score`, `hormone_score`
- Metadata fields: `wl_assessment_type`, `wl_user_email`
- Global fields: `ennu_global_gender`, `ennu_global_date_of_birth`

#### **5. Biomarker Management System**

**Biomarker Service** (`class-biomarker-service.php`)
```php
class ENNU_Biomarker_Service {
    public function validate_biomarker($biomarker_data)
    public function check_reference_range($biomarker)
    public function calculate_trends($user_id, $biomarker_name)
    public function get_biomarkers_by_category($category)
}
```

**Biomarker Categories (50+ markers):**
- **Endocrinology**: Glucose, HbA1c, Testosterone, Cortisol, Vitamin D
- **Hematology**: WBC, RBC, Hemoglobin, Platelets, MCV
- **Cardiology**: Blood Pressure, Cholesterol, ApoB, LDL, HDL
- **Neurology**: ApoE Genotype, Homocysteine, B12, Omega-3
- **Sports Medicine**: Weight, BMI, Grip Strength, VO2 Max
- **Gerontology**: Telomere Length, NAD+, Inflammation Markers
- **Psychiatry**: Serotonin, Dopamine, GABA, Melatonin
- **Nephrology/Hepatology**: BUN, Creatinine, GFR, ALT, AST
- **General Practice**: Coordination, interdisciplinary care

**Features:**
- Reference range validation
- Age/gender-specific ranges
- Trend analysis and visualization
- Flag management system
- CSV import functionality
- PDF processing (LabCorp)

#### **6. AI Medical Research System**

**Research Coordinator** (`research-coordinator.php` - 415 lines)
```php
class ENNU_Research_Coordinator {
    public function coordinate_research_efforts()
    public function validate_reference_ranges()
    public function cross_specialist_review()
    public function generate_research_reports()
}
```

**Specialist Teams (10 Specialists):**
- **Dr. Elena Harmonix** (Endocrinology): Hormonal health, metabolic optimization
- **Dr. Harlan Vitalis** (Hematology): Blood health, immune function
- **Dr. Nora Cognita** (Neurology): Cognitive health, brain function
- **Dr. Victor Pulse** (Cardiology): Cardiovascular health, heart disease prevention
- **Dr. Silas Apex** (Sports Medicine): Performance optimization, athletic enhancement
- **Dr. Linus Eternal** (Gerontology): Aging biomarkers, longevity optimization
- **Dr. Mira Insight** (Psychiatry): Mental health, mood optimization
- **Dr. Renata Flux** (Nephrology/Hepatology): Organ function, kidney/liver health
- **Dr. Orion Nexus** (General Practice): Coordination, interdisciplinary care
- **Coach Aria Vital** (Health Coaching): Lifestyle integration, wellness optimization

**Research Standards:**
- Evidence-based research with peer-reviewed sources
- Clinical guidelines and expert consensus
- Citation Requirements: Multiple peer-reviewed sources per biomarker
- Validation Protocol: Cross-specialist review and clinical validation

#### **7. Admin Interface (`class-enhanced-admin.php`)**
```php
class ENNU_Enhanced_Admin {
    public function add_admin_menu()
    public function render_admin_dashboard_page()
    public function handle_user_management()
    public function generate_reports()
}
```
**Features:**
- Comprehensive user management
- Assessment configuration and editing
- Analytics and reporting dashboard
- System settings and optimization
- Security and compliance tools

### **Database Architecture**

#### **Core Tables**
```sql
-- User assessment data
CREATE TABLE wp_ennu_assessments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    assessment_type VARCHAR(50) NOT NULL,
    form_data JSON,
    scores JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_assessment (user_id, assessment_type),
    INDEX idx_created_at (created_at)
);

-- Biomarker data
CREATE TABLE wp_ennu_biomarkers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    biomarker_name VARCHAR(100) NOT NULL,
    value DECIMAL(10,2),
    unit VARCHAR(20),
    reference_range VARCHAR(50),
    test_date DATE,
    lab_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_biomarker (user_id, biomarker_name),
    INDEX idx_test_date (test_date)
);

-- Score history
CREATE TABLE wp_ennu_score_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    assessment_type VARCHAR(50),
    pillar_scores JSON,
    overall_score DECIMAL(5,2),
    calculation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, calculation_date)
);

-- User sessions and progress
CREATE TABLE wp_ennu_user_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    assessment_type VARCHAR(50),
    progress_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_user_session (user_id, session_id)
);

-- System logs and audit trail
CREATE TABLE wp_ennu_audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    action VARCHAR(100) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created_at (created_at)
);
```

#### **WordPress User Meta Integration**
```php
// Assessment-specific user meta
'ennu_hair_assessment_data' => JSON assessment responses
'ennu_hair_category_scores' => JSON category scores
'ennu_hair_overall_score' => Numeric overall score
'ennu_hair_completion_date' => Timestamp of completion

// Global user meta
'ennu_global_health_goals' => JSON health goals
'ennu_biomarker_data' => JSON biomarker information
'ennu_life_score_data' => JSON comprehensive scoring
'ennu_pillar_scores' => JSON pillar-specific scores
'ennu_assessment_history' => JSON assessment history
'ennu_user_preferences' => JSON user preferences
'ennu_consent_data' => JSON consent and privacy settings
```

### **File Structure & Organization**

```
ennulifeassessments/
├── ennu-life-plugin.php              # Main plugin file
├── includes/
│   ├── class-assessment-shortcodes.php    # Assessment rendering
│   ├── class-scoring-system.php           # Scoring algorithms
│   ├── class-enhanced-admin.php           # Admin interface
│   ├── class-enhanced-database.php        # Database operations
│   ├── class-biomarker-manager.php        # Biomarker handling
│   ├── class-hubspot-oauth-handler.php    # HubSpot integration
│   ├── class-ajax-handler.php             # AJAX operations
│   ├── class-csrf-protection.php          # Security
│   ├── class-hipaa-compliance.php         # Compliance
│   ├── services/
│   │   ├── class-biomarker-service.php    # Biomarker API
│   │   ├── class-assessment-service.php   # Assessment API
│   │   ├── class-ajax-handler.php         # AJAX API
│   │   ├── class-unified-scoring-service.php # Scoring API
│   │   ├── class-assessment-rendering-service.php # Rendering API
│   │   ├── class-data-validation-service.php # Validation API
│   │   ├── class-progressive-data-collector.php # Data collection
│   │   ├── class-unified-api-service.php  # API gateway
│   │   ├── class-pdf-processor.php        # PDF processing
│   │   ├── class-configuration-manager.php # Configuration
│   │   ├── class-unified-security-service.php # Security
│   │   ├── class-performance-optimization-service.php # Performance
│   │   ├── class-advanced-database-optimizer.php # Database optimization
│   │   ├── class-advanced-asset-optimizer.php # Asset optimization
│   │   ├── class-code-quality-manager.php # Code quality
│   │   ├── class-documentation-manager.php # Documentation
│   │   ├── class-comprehensive-testing-framework.php # Testing
│   │   └── class-deployment-manager.php   # Deployment
│   ├── config/
│   │   ├── assessments/                   # Assessment configurations
│   │   │   ├── hair.php
│   │   │   ├── ed-treatment.php
│   │   │   ├── weight-loss.php
│   │   │   ├── hormone.php
│   │   │   ├── health-optimization.php
│   │   │   ├── skin.php
│   │   │   ├── sleep.php
│   │   │   ├── menopause.php
│   │   │   ├── testosterone.php
│   │   │   ├── health.php
│   │   │   └── welcome.php
│   │   ├── scoring/                       # Scoring configurations
│   │   ├── biomarkers/                    # Biomarker configurations
│   │   └── integrations/                  # Integration configurations
│   └── ai-medical-research/              # Medical research data
│       ├── specialists/                   # AI specialist modules
│       │   ├── dr-elena-harmonix/        # Endocrinology
│       │   ├── dr-victor-pulse/          # Cardiology
│       │   ├── dr-renata-flux/           # Nephrology/Hepatology
│       │   ├── dr-harlan-vitalis/        # Hematology
│       │   ├── dr-nora-cognita/          # Neurology
│       │   ├── dr-linus-eternal/         # Gerontology
│       │   ├── dr-silas-apex/            # Sports Medicine
│       │   ├── dr-mira-insight/          # Psychiatry/Psychology
│       │   ├── coach-aria-vital/         # Health Coaching
│       │   └── dr-orion-nexus/           # General Practice
│       ├── biomarker-ranges/              # Reference ranges
│       ├── official-documentation/        # Medical documentation
│       └── shared-resources/              # Shared medical resources
├── assets/
│   ├── css/
│   │   ├── ennu-frontend-forms.css       # Assessment styling
│   │   ├── user-dashboard.css            # Dashboard styling
│   │   ├── theme-system.css              # Theme management
│   │   ├── my-story-insights-styling.css # Story/insights styling
│   │   └── amelia-light-mode.css         # Booking system styling
│   ├── js/
│   │   ├── ennu-frontend-forms.js        # Assessment functionality
│   │   ├── user-dashboard.js             # Dashboard functionality
│   │   ├── theme-manager.js              # Theme switching
│   │   └── chart-adapter-date-fns.js     # Chart functionality
│   └── img/
│       ├── ennu-logo-black.png           # Brand assets
│       ├── ennu-logo-white.png           # Brand assets
│       └── assessment-icons/              # Assessment icons
├── templates/                            # Template files
│   ├── assessment-form.php               # Assessment form template
│   ├── results-page.php                  # Results page template
│   ├── user-dashboard.php                # Dashboard template
│   └── admin-panel.php                   # Admin panel template
├── languages/                            # Translation files
│   ├── ennu-life-assessments.pot         # Translation template
│   ├── ennu-life-assessments-en_US.po    # English translations
│   └── ennu-life-assessments-es_ES.po    # Spanish translations
├── documentation/                        # Documentation
│   ├── api-reference.md                  # API documentation
│   ├── user-guide.md                     # User guide
│   ├── admin-guide.md                    # Admin guide
│   ├── developer-guide.md                # Developer guide
│   └── integration-guide.md              # Integration guide
└── tests/                               # Test files
    ├── unit/                             # Unit tests
    ├── integration/                      # Integration tests
    └── e2e/                             # End-to-end tests
```

### **API Architecture**

#### **REST API Endpoints**
```http
# Assessment endpoints
GET /wp-json/ennu/v1/assessments?user_id={user_id}
POST /wp-json/ennu/v1/assessments
PUT /wp-json/ennu/v1/assessments/{id}
DELETE /wp-json/ennu/v1/assessments/{id}

# Biomarker endpoints
GET /wp-json/ennu/v1/biomarkers?user_id={user_id}
POST /wp-json/ennu/v1/biomarkers
PUT /wp-json/ennu/v1/biomarkers/{id}
DELETE /wp-json/ennu/v1/biomarkers/{id}

# User endpoints
GET /wp-json/ennu/v1/users/{user_id}/profile
PUT /wp-json/ennu/v1/users/{user_id}/profile
GET /wp-json/ennu/v1/users/{user_id}/scores
GET /wp-json/ennu/v1/users/{user_id}/history

# Admin endpoints
GET /wp-json/ennu/v1/admin/users
GET /wp-json/ennu/v1/admin/analytics
POST /wp-json/ennu/v1/admin/reports
```

#### **Webhook System**
```php
// Webhook endpoints for real-time integrations
add_action('ennu_assessment_completed', 'webhook_assessment_completed');
add_action('ennu_score_calculated', 'webhook_score_calculated');
add_action('ennu_biomarker_uploaded', 'webhook_biomarker_uploaded');
add_action('ennu_user_registered', 'webhook_user_registered');
```

### **Security Architecture**

#### **Authentication & Authorization**
```php
// CSRF Protection
class ENNU_CSRF_Protection {
    public function verify_nonce($nonce, $action)
    public function create_nonce($action)
}

// Role-based Access Control
class ENNU_Role_Based_Access_Control {
    public function check_permission($user_id, $capability)
    public function assign_role($user_id, $role)
}
```

#### **Data Protection**
```php
// Data encryption
class ENNU_Data_Encryption {
    public function encrypt($data)
    public function decrypt($encrypted_data)
}

// Audit logging
class ENNU_Audit_Logger {
    public function log_action($user_id, $action, $details)
    public function get_audit_trail($user_id, $date_range)
}
```

### **Performance Architecture**

#### **Caching System**
```php
// Score caching
class ENNU_Score_Cache {
    public function get_cached_score($user_id, $assessment_type)
    public function cache_score($user_id, $assessment_type, $score_data)
    public function invalidate_cache($user_id, $assessment_type)
}

// Database query optimization
class ENNU_Database_Optimizer {
    public function optimize_queries()
    public function create_indexes()
    public function clean_old_data()
}
```

#### **Asset Optimization**
```php
// Asset minification and compression
class ENNU_Asset_Optimizer {
    public function minify_css($css_files)
    public function minify_js($js_files)
    public function optimize_images($image_files)
}
```

### **Integration Architecture**

#### **HubSpot Integration**
```php
// OAuth 2.0 authentication
class ENNU_HubSpot_OAuth_Handler {
    public function authenticate()
    public function refresh_token()
    public function sync_data($user_id, $data)
}

// Field mapping
class ENNU_HubSpot_Field_Mapper {
    public function map_assessment_fields($assessment_type)
    public function map_biomarker_fields($biomarker_data)
    public function map_user_fields($user_data)
}
```

#### **Third-party Integrations**
```php
// Payment processing
class ENNU_Payment_Processor {
    public function process_payment($amount, $user_id)
    public function refund_payment($payment_id)
}

// Email service
class ENNU_Email_Service {
    public function send_welcome_email($user_id)
    public function send_assessment_results($user_id, $assessment_type)
    public function send_consultation_reminder($user_id, $consultation_id)
}
```

### **Scalability Architecture**

#### **Horizontal Scaling**
- Database read replicas for high-traffic scenarios
- CDN integration for static asset delivery
- Load balancing for multiple server instances
- Microservices architecture for specific functions

#### **Vertical Scaling**
- Database query optimization
- Caching at multiple levels (application, database, CDN)
- Asset optimization and compression
- Memory and CPU optimization

### **Monitoring & Analytics**

#### **Performance Monitoring**
```php
class ENNU_Performance_Monitor {
    public function start_timer($operation)
    public function end_timer($operation)
    public function log_performance_metrics($metrics)
}
```

#### **Error Tracking**
```php
class ENNU_Error_Tracker {
    public function log_error($error, $context)
    public function send_error_report($error_data)
    public function get_error_summary($date_range)
}
```

### **Development Architecture**

#### **Testing Framework**
```php
// Unit tests
class ENNU_Unit_Tests {
    public function test_scoring_algorithm()
    public function test_biomarker_validation()
    public function test_assessment_rendering()
}

// Integration tests
class ENNU_Integration_Tests {
    public function test_hubspot_integration()
    public function test_database_operations()
    public function test_api_endpoints()
}
```

#### **Code Quality**
```php
// Code quality management
class ENNU_Code_Quality_Manager {
    public function run_linting()
    public function run_unit_tests()
    public function generate_code_coverage_report()
}
```

---

## 🔥 Advanced Features

### **1. Slack Notifications System**
**File**: `class-slack-notifications.php` (749 lines)
```php
class ENNU_Slack_Notifications_Manager {
    public function send_assessment_completion_notification($user_id, $assessment_type)
    public function send_critical_health_alert($user_id, $alert_data)
    public function send_appointment_notification($user_id, $booking_data)
    public function send_daily_summary($summary_data)
}
```
**Features:**
- Real-time assessment completion notifications
- Critical health alert system
- Appointment booking notifications
- Daily summary reports
- HIPAA-compliant data handling
- Rate limiting and error handling
- Webhook integration with Slack channels

### **2. HIPAA Compliance System**
**File**: `class-hipaa-compliance.php` (349 lines)
```php
class ENNU_HIPAA_Compliance {
    public static function encrypt_biomarker_data($data)
    public static function decrypt_biomarker_data($encrypted_data)
    public static function log_audit_trail($user_id, $action, $details)
    public static function secure_file_upload($file)
}
```
**Features:**
- AES-256-CBC encryption for medical data
- Comprehensive audit trail logging
- Secure file upload handling
- Medical data access controls
- Audit log export and cleanup
- HIPAA-compliant data handling

### **3. REST API System**
**File**: `class-ennu-rest-api.php` (724 lines)
```php
class ENNU_REST_API {
    public static function get_assessments($request)
    public static function submit_assessment($request)
    public static function get_user_scores($request)
    public static function get_user_biomarkers($request)
    public static function update_user_biomarkers($request)
}
```
**Features:**
- Complete REST API endpoints for all functionality
- Assessment submission and retrieval
- User scores and biomarker management
- Goal tracking and trend analysis
- Analytics and reporting endpoints
- Authentication and permission controls

### **4. Trends Visualization System**
**File**: `class-trends-visualization-system.php` (1065 lines)
```php
class ENNU_Trends_Visualization_System {
    public static function get_comprehensive_trend_data($user_id, $time_range, $category)
    public static function get_life_score_trends($user_id, $time_range)
    public static function get_pillar_score_trends($user_id, $time_range)
    public static function get_biomarker_trend_data($user_id, $biomarker, $time_range)
}
```
**Features:**
- Advanced Chart.js integration
- Life score trend analysis
- Pillar score visualization
- Biomarker trend tracking
- Goal progress visualization
- Interactive dashboard charts
- Time-range filtering

### **5. Advanced Analytics System**
**File**: `class-advanced-analytics.php` (1099 lines)
```php
class ENNU_Advanced_Analytics {
    public function track_assessment_completion($assessment_type, $user_id, $score)
    public function track_biomarker_update($biomarker_type, $user_id)
    public function track_health_goal_creation($goal_type, $user_id)
    public function get_analytics_data()
}
```
**Features:**
- User behavior tracking
- Conversion analysis
- Business intelligence reporting
- A/B testing framework
- Performance metrics
- Retention analysis
- Custom event tracking

### **6. AI/ML Manager**
**File**: `class-ai-ml-manager.php` (1926 lines)
```php
class ENNU_AI_ML_Manager {
    public function process_assessment_ai($user_id, $assessment_data)
    public function get_personalized_recommendations($recommendations, $user_id)
    public function generate_biomarker_insights($insights, $biomarker_data)
    public function update_health_predictions($user_id, $data)
}
```
**Features:**
- Personalized health recommendations
- Predictive analytics
- Anomaly detection
- Trend forecasting
- Sentiment analysis
- Model training and optimization
- AI-powered insights

### **7. Performance Optimization**
**Files**: Multiple optimization classes
- **Caching**: Redis integration, transient management
- **Database**: Query optimization, indexing
- **Assets**: Minification, compression
- **Memory**: Memory optimization, garbage collection
- **Mobile**: Mobile-specific optimizations

---

## 🚀 Installation & Setup

### **Requirements**
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- 256MB RAM (recommended)

### **Quick Install**
1. Download plugin ZIP
2. WordPress Admin → Plugins → Add New → Upload Plugin
3. Activate plugin
4. Configure in ENNU Life → Settings

### **Manual Install**
```bash
git clone https://github.com/ennulife/ennulifeassessments.git
cp -r ennulifeassessments wp-content/plugins/
```

---

## ⚙️ Configuration

- **General Settings**: Version, debug, cache, analytics
- **Assessment Settings**: Timeout, auto-save, registration
- **Scoring Settings**: Weights for each engine
- **HubSpot Integration**: API keys, field mapping
- **Email**: Templates, SMTP
- **Custom Assessments**: Add via filters

---

## 📖 Usage & Features

### **Shortcodes**
```php
[ennu-hair]
[ennu-ed-treatment]
[ennu-weight-loss]
[ennu-hormone]
[ennu-health-optimization]
[ennu-skin]
[ennu-sleep]
[ennu-menopause]
[ennu-testosterone]
[ennu-health]
[ennu-user-dashboard]
[ennu-assessment-results]
[ennu-biomarkers]
[ennu-assessments]
[scorepresentation]
```

### **Assessment Types**
| Assessment      | Shortcode              | Description                        |
|----------------|------------------------|------------------------------------|
| Hair Loss      | `[ennu-hair]`          | Hair loss evaluation               |
| ED Treatment   | `[ennu-ed-treatment]`  | Erectile dysfunction assessment    |
| Weight Loss    | `[ennu-weight-loss]`   | Weight management                  |
| Hormone        | `[ennu-hormone]`       | Hormone balance                    |
| Health Opt.    | `[ennu-health-optimization]` | General wellness             |
| Skin           | `[ennu-skin]`          | Dermatological assessment          |
| Sleep          | `[ennu-sleep]`         | Sleep quality                      |
| Menopause      | `[ennu-menopause]`     | HRT suitability                    |
| Testosterone   | `[ennu-testosterone]`  | Male hormone assessment            |
| General Health | `[ennu-health]`        | Comprehensive health check         |

---

## 🧩 API Reference

### **REST API**
```http
GET /wp-json/ennu/v1/assessments?user_id={user_id}
POST /wp-json/ennu/v1/assessments
GET /wp-json/ennu/v1/biomarkers?user_id={user_id}
POST /wp-json/ennu/v1/biomarkers
```

### **Hooks & Filters**
```php
do_action('ennu_assessment_completed', $user_id, $assessment_type);
apply_filters('ennu_assessment_types', $types);
apply_filters('ennu_score_calculation', $scores, $user_id);
```

---

## 🔗 Integration Ecosystem

- **HubSpot**: Contact creation, field mapping, data sync
- **Salesforce**: API-ready
- **WP Fusion**: Tag users by results
- **Amelia/Calendly**: Booking integration
- **Google Analytics/MailChimp**: Analytics & marketing

---

## 🛡️ Security & Compliance

- **CSRF Protection**: Nonce on all forms
- **Input Sanitization**: All user input
- **Output Escaping**: All output
- **Role-Based Access**: Admin/user separation
- **GDPR**: Data export/deletion
- **HIPAA**: Secure storage (when configured)
- **Audit Logging**: All critical actions

---

## ⚡ Performance & Optimization

- **Caching**: Scores/results cached
- **Database Indexing**: Optimized tables
- **Asset Optimization**: Minified, conditional loading
- **Scalability**: Handles thousands of users

---

## 🛠️ Troubleshooting

| Issue                 | Symptoms         | Solution                        |
|-----------------------|-----------------|---------------------------------|
| Assessment not loading| Blank page      | Check shortcode, JS, conflicts  |
| HubSpot sync failing  | Data missing    | Verify API, check logs          |
| Scores not updating   | Old results     | Clear cache, recalc scores      |
| Admin panel blank     | 500 error       | Check permissions, PHP errors   |
| Slow performance      | Long load times | Enable cache, optimize DB       |

---

## 🧑‍💻 Development

```bash
git clone https://github.com/ennulife/ennulifeassessments.git
cd ennulifeassessments
composer install
npm install
npm run build
```
- Use LocalWP or DevKinsta for local WordPress
- Enable `WP_DEBUG` in `wp-config.php`
- Run PHPUnit for tests

---

## 🤝 Contributing

- Fork, branch from `main`, submit PRs
- Write clear, documented code
- Add/update tests
- See [CONTRIBUTING.md](CONTRIBUTING.md)

---

## 👥 Support & Community

- [Documentation](https://docs.ennulife.com)
- [Support Forum](https://support.ennulife.com)
- [Discord](https://discord.gg/ennulife)
- [Email](mailto:support@ennulife.com)

---

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for full history.

---

## 📄 License

GPL v2 or later. See [LICENSE](LICENSE) for details.

---

## 🏆 Acknowledgments

- WordPress community
- HubSpot
- All contributors, testers, and users