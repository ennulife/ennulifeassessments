<?php
/**
 * Plugin Name: ENNU Life Assessments
 * Plugin URI: https://ennulife.com
 * Description: Advanced health assessment system with comprehensive user scoring
 * Version: 62.2.9
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Author: Luis Escobar
 * Author URI: https://ennulife.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ennulifeassessments
 * Domain Path: /languages
 * Network: false
 * 
 * @package ENNU_Life
 * @version 62.2.9
 * 
 * CHANGELOG:
 * 
 * 62.2.9 - AI EMPLOYEE SYSTEM IMPLEMENTATION - COMPREHENSIVE SPECIALIST ROUTING
 * - IMPLEMENTED: Complete AI employee system with 41 specialized domain experts
 * - CREATED: Individual rule files for each AI employee with unique personalities and expertise
 * - ESTABLISHED: Auto-selector router for intelligent keyword-based specialist routing
 * - DEVELOPED: JavaScript rule selector for advanced keyword matching and autonomous selection
 * - IMPLEMENTED: Health specialists (10): Endocrinology, Cardiology, Neurology, Sports Medicine, etc.
 * - CREATED: Technical specialists (11): WordPress, Full Stack, UX/UI, Data Science, etc.
 * - ESTABLISHED: Business specialists (6): Sales, Marketing, Project Management, etc.
 * - IMPLEMENTED: Scientific specialists (4): Research, Mathematics, Psychology, etc.
 * - CREATED: Support specialists (6): Legal, Quality Assurance, Customer Success, etc.
 * - DEVELOPED: Comprehensive keyword matching system with 500+ specific terms
 * - IMPLEMENTED: Automatic routing based on input context and domain expertise
 * - ESTABLISHED: Manual rule attachment system for specific specialist selection
 * - CREATED: Complete documentation and usage guide for the AI employee system
 * - ACHIEVED: World-class specialist routing system for comprehensive domain expertise
 * 
 * 62.2.8 - COMPREHENSIVE VERSION & AUTHOR UPDATE - JULY 18, 2025
 * - UPDATED: Plugin version to 62.2.8 across all documentation files
 * - UPDATED: All author references to Luis Escobar throughout the codebase
 * - UPDATED: All date references to July 18, 2025 where applicable
 * - STANDARDIZED: Version numbering consistency across all documentation
 * - MAINTAINED: Complete changelog history and development tracking
 * - ENSURED: All documentation reflects current plugin status and authorship
 * - ACHIEVED: Complete version and author consistency across entire codebase
 * 
 * 62.2.7 - AI ONBOARDING INSTRUCTIONS - COMPREHENSIVE DEVELOPMENT GUIDELINES
 * - CREATED: Official AI onboarding instructions with comprehensive development guidelines
 * - DOCUMENTED: Complete plugin architecture overview and component relationships
 * - MAPPED: Current functional status with working components and known issues
 * - ESTABLISHED: Critical development workflow with testing and deployment procedures
 * - DEFINED: Security requirements and WordPress standards compliance guidelines
 * - DOCUMENTED: Performance considerations and optimization strategies
 * - CREATED: Emergency procedures and troubleshooting guides
 * - ESTABLISHED: Success metrics for functional, performance, and user experience
 * - DOCUMENTED: Business logic understanding and revenue model integration
 * - CREATED: Essential file references and debugging tools documentation
 * - ENABLED: Seamless AI handoff with comprehensive knowledge transfer
 * - ACHIEVED: World-class development standards for production health assessment system
 * 
 * 62.2.5 - COMPREHENSIVE BIOMARKER DASHBOARD ENHANCEMENT - WORLD-CLASS HEALTH MONITORING
 * - ENHANCED: Biomarker dashboard with 75+ biomarkers across 12 comprehensive categories
 * - IMPLEMENTED: Priority-based biomarker classification (Critical, High, Medium, Normal)
 * - ADDED: Advanced biomarker categories: Thyroid Function, Vitamins & Nutrients, Cardiovascular Risk, Advanced Markers
 * - ENHANCED: Priority badges with visual indicators for critical and high-priority biomarkers
 * - IMPLEMENTED: Comprehensive biomarker data with values, ranges, trends, and status indicators
 * - ADDED: Enhanced biomarker summary dashboard with abnormal and critical issue tracking
 * - ENHANCED: Elegant biomarker card design with priority-based color coding and hover effects
 * - IMPLEMENTED: Advanced biomarker categories including specialized markers (Telomere Length, BDNF, Myostatin)
 * - ADDED: Thyroid function biomarkers (TSH, Free T3, Free T4, Reverse T3, TPO Antibodies)
 * - ENHANCED: Vitamin and nutrient markers (Vitamin D, B12, Folate, Omega-3, Homocysteine, CRP)
 * - IMPLEMENTED: Cardiovascular risk markers (Troponin I, BNP, Uric Acid, Creatine Kinase)
 * - ADDED: Advanced metabolic markers (Insulin, Leptin, Adiponectin, Myostatin, Telomere Length)
 * - ENHANCED: Responsive design for biomarker display across all device sizes
 * - IMPLEMENTED: Light/dark mode compatibility for all biomarker elements
 * - ADDED: Premium animations and interactive features for enhanced user experience
 * - ACHIEVED: World's most comprehensive biomarker monitoring and visualization system
 * 
 * 62.2.4 - ENHANCED SYMPTOM-TO-BIOMARKER MAPPING - COMPREHENSIVE HEALTH INSIGHTS
 * - ENHANCED: Complete symptom-to-biomarker mapping with 52 symptoms across 8 categories
 * - IMPLEMENTED: Specific biomarker recommendations for each symptom (10+ biomarkers per symptom)
 * - ADDED: Elegant biomarker tag display with hover effects and clean styling
 * - ENHANCED: Symptom layout with improved visual hierarchy and reduced boxy appearance
 * - IMPLEMENTED: Comprehensive biomarker coverage including hormones, vitamins, minerals, and specialized markers
 * - ADDED: Critical biomarkers for energy symptoms (Cortisol, TSH, Vitamin D, B12, Iron, Testosterone)
 * - ENHANCED: Heart health biomarkers (Troponin, BNP, CRP, Lipid Panel, Homocysteine)
 * - IMPLEMENTED: Hormonal biomarkers (Testosterone, Estradiol, Progesterone, DHEA-S, Thyroid Panel)
 * - ADDED: Weight management biomarkers (Insulin, Leptin, Adiponectin, Blood Sugar, Lipid Panel)
 * - ENHANCED: Strength biomarkers (IGF-1, Creatine Kinase, Myostatin, Protein Markers)
 * - IMPLEMENTED: Longevity biomarkers (Telomere Length, BDNF, ApoE, Collagen Markers)
 * - ADDED: Cognitive health biomarkers (Omega-3, Homocysteine, Neurotransmitter Panel)
 * - ENHANCED: Libido biomarkers (Free Testosterone, Prolactin, Serotonin)
 * - IMPLEMENTED: Responsive design for biomarker tags across all device sizes
 * - ADDED: Light/dark mode compatibility for all new biomarker elements
 * - ENHANCED: User experience with clear biomarker recommendations for each symptom
 * - ACHIEVED: World's most comprehensive symptom-to-biomarker mapping system
 * 
 * 62.2.3 - ELEGANT DESIGN RESTORATION - CLEAN, MODERN INTERFACE
 * - RESTORED: Elegant, clean design that was previously achieved
 * - REMOVED: Excessive borders, heavy box shadows, and boxy appearance
 * - IMPLEMENTED: Subtle backgrounds and minimal visual noise throughout
 * - ENHANCED: Visual hierarchy using spacing and typography instead of heavy borders
 * - OPTIMIZED: Assessment cards with clean styling and minimal borders
 * - IMPLEMENTED: Elegant biomarker categories with subtle backgrounds and no borders
 * - ENHANCED: Status indicators with clean styling and better contrast
 * - OPTIMIZED: Biomarker items with minimal borders and smooth hover effects
 * - IMPLEMENTED: Clean new life overview with gradient backgrounds and no heavy borders
 * - ENHANCED: Overall user experience with reduced visual noise and improved readability
 * - MAINTAINED: Complete theme system functionality for both dark and light modes
 * - ACHIEVED: Elegant, modern interface that maintains functionality while reducing clutter
 * 
 * 62.2.2 - REDUCED BOX LOOK - CLEANER, MODERN INTERFACE
 * - REDUCED: "Box look" by removing unnecessary borders and heavy box shadows
 * - IMPLEMENTED: Cleaner design with subtle backgrounds and minimal visual noise
 * - ENHANCED: Visual hierarchy using spacing and typography instead of heavy borders
 * - OPTIMIZED: Card designs with transparent backgrounds and minimal borders
 * - IMPLEMENTED: Subtle hover effects with background changes instead of heavy shadows
 * - ENHANCED: Status indicators with clean styling and better contrast
 * - OPTIMIZED: Progress bars with streamlined appearance and smooth animations
 * - IMPLEMENTED: Clean tab navigation with subtle styling instead of heavy borders
 * - ENHANCED: Goal pills with minimal borders and improved visual feedback
 * - OPTIMIZED: Biomarker and symptom items with clean styling instead of boxes
 * - IMPLEMENTED: Streamlined CTA sections with gradient backgrounds and no borders
 * - ENHANCED: Button designs with subtle backgrounds and improved hover states
 * - OPTIMIZED: Score circles with minimal borders and clean visual presentation
 * - IMPLEMENTED: Clean milestone timeline with subtle connecting lines
 * - ENHANCED: Program cards with minimal backgrounds and improved spacing
 * - OPTIMIZED: Coach section with gradient backgrounds and no heavy borders
 * - IMPLEMENTED: Responsive design improvements for cleaner mobile experience
 * - ENHANCED: Focus states with subtle outlines instead of heavy borders
 * - OPTIMIZED: Notification system with clean, minimal styling
 * - IMPLEMENTED: Loading states with subtle shimmer effects
 * - ENHANCED: Overall user experience with reduced visual noise and improved readability
 * - MAINTAINED: Complete theme system functionality for both dark and light modes
 * - ACHIEVED: Modern, clean interface that maintains functionality while reducing clutter
 * 
 * 62.2.1 - DARK/LIGHT MODE FIX - PIXEL PERFECT THEME SYSTEM
 * - FIXED: Critical dark/light mode system that was broken by previous changes
 * - IMPLEMENTED: Pixel-perfect theme-aware design system with proper CSS variables
 * - ENHANCED: All components now properly respect both dark and light themes
 * - OPTIMIZED: Card designs with theme-appropriate backgrounds and borders
 * - IMPLEMENTED: Proper contrast ratios and color schemes for both themes
 * - ENHANCED: Status indicators with theme-aware styling and colors
 * - OPTIMIZED: Progress bars with theme-appropriate backgrounds and shadows
 * - IMPLEMENTED: Tab navigation with proper theme switching functionality
 * - ENHANCED: Goal pills with theme-aware borders and hover states
 * - OPTIMIZED: Biomarker and symptom items with theme-appropriate styling
 * - IMPLEMENTED: CTA sections with gradient backgrounds that work in both themes
 * - ENHANCED: Button designs with proper theme-aware colors and shadows
 * - OPTIMIZED: Score circles with theme-appropriate borders and effects
 * - IMPLEMENTED: Milestone timeline with theme-aware connecting lines
 * - ENHANCED: Program cards with proper theme switching functionality
 * - OPTIMIZED: Coach section with theme-appropriate gradient backgrounds
 * - IMPLEMENTED: Responsive design that maintains theme consistency
 * - ENHANCED: Focus states with theme-aware outline colors
 * - OPTIMIZED: Notification system with proper theme colors
 * - IMPLEMENTED: Loading states with theme-appropriate shimmer effects
 * - ENHANCED: Overall user experience with consistent theme switching
 * - ACHIEVED: Complete theme system restoration with pixel-perfect design
 * 
 * 62.2.0 - STREAMLINED DASHBOARD DESIGN - CLEANER, MODERN INTERFACE
 * - REDESIGNED: Complete dashboard interface with reduced visual clutter
 * - REMOVED: Heavy borders, box shadows, and excessive card backgrounds
 * - IMPLEMENTED: Clean, modern design with subtle backgrounds and minimal borders
 * - ENHANCED: Visual hierarchy using spacing, typography, and subtle color variations
 * - OPTIMIZED: Card designs with transparent backgrounds and minimal visual noise
 * - IMPLEMENTED: Subtle hover effects without heavy borders or shadows
 * - ENHANCED: Status indicators with clean, minimal styling and better contrast
 * - OPTIMIZED: Progress bars with streamlined appearance and smooth animations
 * - IMPLEMENTED: Clean tab navigation with subtle underlines instead of heavy borders
 * - ENHANCED: Goal pills with minimal borders and improved visual feedback
 * - OPTIMIZED: Biomarker and symptom items with clean separators instead of boxes
 * - IMPLEMENTED: Streamlined CTA sections with gradient backgrounds and no borders
 * - ENHANCED: Button designs with subtle backgrounds and improved hover states
 * - OPTIMIZED: Score circles with minimal borders and clean visual presentation
 * - IMPLEMENTED: Clean milestone timeline with subtle connecting lines
 * - ENHANCED: Program cards with minimal backgrounds and improved spacing
 * - OPTIMIZED: Coach section with gradient backgrounds and no heavy borders
 * - IMPLEMENTED: Responsive design improvements for cleaner mobile experience
 * - ENHANCED: Focus states with subtle outlines instead of heavy borders
 * - OPTIMIZED: Notification system with clean, minimal styling
 * - IMPLEMENTED: Loading states with subtle shimmer effects
 * - ENHANCED: Overall user experience with reduced visual noise and improved readability
 * - ACHIEVED: Modern, clean interface that maintains functionality while reducing clutter
 * 
 * 62.1.25 - USER DASHBOARD TAB ENHANCEMENTS - PREMIUM UX TRANSFORMATION
 * - ENHANCED: "My Symptoms" tab with realistic dummy data and severity indicators
 * - IMPLEMENTED: Symptom categorization (Energy & Fatigue, Sleep Quality, Hormonal, Physical)
 * - ADDED: Symptom analysis with priority recommendations and conversion CTAs
 * - ENHANCED: "My Biomarkers" tab with comprehensive dummy biomarker data
 * - IMPLEMENTED: 40+ biomarkers across 8 categories with realistic values and status indicators
 * - ADDED: Biomarker summary dashboard with abnormal/critical issue tracking
 * - IMPLEMENTED: Critical health issues alert system for urgent consultation
 * - ENHANCED: "My New Life" tab with transformation overview and progress tracking
 * - ADDED: Enhanced life coach section with features and pricing ($197/month)
 * - IMPLEMENTED: Pillar optimization pathway with priority indicators and action buttons
 * - ADDED: Transformation programs (Starter $497, Premium $1,497, Elite $2,997)
 * - ENHANCED: Journey milestones with status indicators and progress tracking
 * - IMPLEMENTED: Comprehensive conversion CTAs with pricing and success metrics
 * - ADDED: Realistic dummy data throughout all three tabs for demonstration
 * - ENHANCED: Visual design with status indicators, progress bars, and interactive elements
 * - IMPLEMENTED: Conversion optimization with multiple pricing tiers and urgency messaging
 * - ADDED: Success metrics (95% success rate, 12-18 months timeline, 24/7 support)
 * - ENABLED: Premium user experience with engaging, conversion-focused interface
 * - COMPLETED: Complete transformation of three critical dashboard tabs
 * 
 * 62.1.24 - FRONTEND UX PRIORITY ROADMAP - PREMIUM UX TRANSFORMATION PLAN
 * - CREATED: Comprehensive business model integration with ENNU Life Core biomarkers (50 total)
 * - IMPLEMENTED: Advanced biomarker addon system (30 total) for specialized optimization
 * - DESIGNED: Tiered membership structure (Basic $99/month, Comprehensive $599, Premium $199/month)
 * - CREATED: Addon package system with 6 specialized packages ($299-$1,999)
 * - MAPPED: Complete biomarker structure from physical measurements to advanced longevity markers
 * - DESIGNED: Business model with recurring revenue, one-time diagnostics, and addon revenue streams
 * - IMPLEMENTED: System architecture for core vs addon biomarker management
 * - CREATED: User journey optimization from basic membership to advanced optimization
 * - DESIGNED: Complete health ecosystem serving users at every level of health optimization
 * - MAPPED: Revenue streams with clear upgrade paths and specialized offerings
 * - IMPLEMENTED: Comprehensive biomarker configuration for all 80 total biomarkers
 * - CREATED: Business model integration with payment processing and consultation recommendations
 * - DESIGNED: Market expansion strategy from accessible entry to advanced optimization
 * - MAPPED: Complete implementation roadmap for world's most comprehensive health platform
 * - ENABLED: Transformative business opportunity with sustainable revenue model
 * - CREATED: Comprehensive codebase analysis covering 50+ files and 15,000+ lines of code
 * - IDENTIFIED: Critical performance issues including 4,426-line monolithic shortcode class
 * - DISCOVERED: Security vulnerabilities in AJAX handlers and database queries
 * - ANALYZED: Architectural problems with tight coupling and missing abstraction layers
 * - DESIGNED: 12-week strategic transformation roadmap with 4 implementation phases
 * - PLANNED: Critical infrastructure overhaul addressing performance, security, and scalability
 * - MAPPED: Business logic enhancement with dynamic biomarker and assessment systems
 * - CREATED: Advanced features roadmap including REST API and analytics integration
 * - DESIGNED: Comprehensive testing and quality assurance strategy
 * - MAPPED: Success metrics targeting 300% performance improvement and 50% technical debt reduction
 * - ENABLED: Enterprise-ready health optimization platform with world-class architecture
 * - CREATED: Functionality priority roadmap focusing on actual working features
 * - IDENTIFIED: Working functionality (assessments, dashboard, admin panel) vs broken (scoring, health goals)
 * - PRIORITIZED: Critical fixes for scoring system and "My New Life" tab conversion
 * - DESIGNED: 12-week implementation plan with functionality-first approach
 * - MAPPED: Revenue optimization with booking system and lab testing integration
 * - PLANNED: User experience optimization for assessment completion and engagement
 * - CREATED: Business intelligence dashboard for revenue tracking and analytics
 * - DESIGNED: Automation system for user engagement and follow-up
 * - MAPPED: Performance optimization and security hardening for system reliability
 * - ENABLED: Fully functional, revenue-generating health transformation platform
 * - CREATED: Frontend UX priority roadmap focusing on premium user experience
 * - IDENTIFIED: UX strengths (dashboard design, assessment flow) vs critical issues (conversion, navigation)
 * - PRIORITIZED: "My New Life" tab conversion optimization and assessment flow enhancement
 * - DESIGNED: 12-week UX implementation plan with conversion-focused approach
 * - MAPPED: Visual design system with modern UI, micro-interactions, and animations
 * - PLANNED: Mobile-first design with touch-friendly interfaces and responsive layouts
 * - CREATED: Gamification system with achievements, progress tracking, and engagement elements
 * - DESIGNED: Accessibility implementation with WCAG compliance and inclusive design
 * - MAPPED: Performance optimization with lazy loading, animations, and mobile excellence
 * - ENABLED: Premium, engaging, and highly converting user experience platform
 * 
 * 62.1.16 - ENNU RESEARCH INTEGRATION ANALYSIS - COMPREHENSIVE SYSTEM ENHANCEMENT PLAN
 * - CREATED: Comprehensive research integration analysis comparing user research with current system
 * - IDENTIFIED: Perfect alignment of 52 symptoms, 8 health vectors, and 25 basic biomarkers
 * - DISCOVERED: System advantage with 22 advanced biomarkers not in research
 * - IDENTIFIED: Critical gaps - 25 ENNU biomarkers missing (7 physical + 18 standard lab tests)
 * - ANALYZED: Perfect questionnaire structure alignment (25 questions, same processing logic)
 * - CREATED: 5-phase implementation roadmap for comprehensive system enhancement
 * - DESIGNED: Enhanced biomarker data structure with 72 total biomarkers (47 advanced + 25 standard)
 * - PLANNED: Enhanced symptom-to-biomarker correlation system with physical indicators
 * - DESIGNED: Enhanced user interface with physical measurements and standard lab displays
 * - CREATED: Tiered business model with basic to comprehensive testing packages
 * - PLANNED: Enhanced consultation recommendations based on symptom/biomarker thresholds
 * - MAPPED: Complete integration benefits from basic health screening to advanced optimization
 * - IDENTIFIED: Transformative opportunity to create world's most comprehensive health platform
 * - DOCUMENTED: Complete implementation strategy for critical business opportunity
 * - ENABLED: System enhancement from research-focused to clinically-relevant comprehensive platform
 * 
 * 62.1.15 - PRECISE USER EXPERIENCE FLOW DOCUMENTATION - COMPREHENSIVE UX MAPPING
 * - CREATED: Comprehensive user experience flow documentation (PRECISE_USER_EXPERIENCE_FLOW_DOCUMENTATION.md)
 * - MAPPED: Complete user journey from initial encounter to dashboard interaction
 * - DOCUMENTED: Precise display logic for every dashboard component and section
 * - ANALYZED: What users see, when they see it, why they see it, and how they see it
 * - IDENTIFIED: Critical scoring system conflicts between simple average and complex four-engine system
 * - MAPPED: Business model integration points and conversion funnel optimization
 * - DOCUMENTED: "My New Life" tab as critical business conversion driver
 * - ANALYZED: Score gap creation psychology and realistic improvement path strategy
 * - MAPPED: All CTA locations and strategic placement for maximum conversion
 * - DOCUMENTED: Four-engine scoring symphony architecture and mathematical flow
 * - IDENTIFIED: Data completeness issues and personalization optimization opportunities
 * - MAPPED: Gender-based assessment filtering and dynamic content rendering
 * - ANALYZED: Health goals section and Intentionality Engine activation flow
 * - DOCUMENTED: Tabbed interface structure and content organization strategy
 * - MAPPED: Assessment card display logic and completion status handling
 * - ANALYZED: Symptom tracking and biomarker upsell integration points
 * - DOCUMENTED: Transformation journey visualization and milestone progression
 * - MAPPED: Chart data sources and progress tracking implementation
 * - ANALYZED: Quick actions section and conversion funnel optimization
 * - IDENTIFIED: Implementation priorities for score system unification and realistic goals
 * - DOCUMENTED: Complete user experience precision mapping for business optimization
 * - ENABLED: Data-driven personalization strategy and conversion optimization
 * - COMPLETED: Comprehensive UX analysis for maximum business impact and user satisfaction
 * 
 * 62.1.14 - HEALTH GOALS CONFIGURATION FILE CREATION - INTENTIONALITY ENGINE COMPLETION
 * - CREATED: Missing health goals configuration file (includes/config/scoring/health-goals.php)
 * - IMPLEMENTED: Complete goal-to-pillar mapping for all 11 health goals
 * - ENABLED: Intentionality Engine with +5% non-cumulative boost system
 * - ADDED: Goal definitions with rich metadata (icons, descriptions, categories)
 * - IMPLEMENTED: Boost rules configuration (max 5% per pillar, non-cumulative)
 * - ADDED: Goal categories organization (Wellness, Fitness, Health, Beauty)
 * - ENABLED: Goal-based scoring that actually works and affects ENNU LIFE SCORE
 * - COMPLETED: Fourth engine in the "Scoring Symphony" - Intentionality Engine
 * - ACHIEVED: 100% completion of the four-engine scoring system
 * - IMPLEMENTED: Complete goal alignment boost system as documented
 * - ADDED: Validation rules for goal selection and boost application
 * - ENABLED: User health goals now have direct mathematical impact on scoring
 * - COMPLETED: Full implementation of documented goal-based personalization
 * - ACHIEVED: Complete four-engine scoring symphony functionality
 * - ENABLED: Goal-based motivational feedback loop for user engagement
 * - IMPLEMENTED: Non-cumulative boost system preventing score inflation
 * - ADDED: Rich goal metadata for enhanced user interface display
 * - COMPLETED: Missing configuration that was blocking 100% system functionality
 * 
 * 62.1.13 - COMPREHENSIVE DOCUMENTATION REVIEW & SYSTEM UNDERSTANDING
 * - COMPLETED: Comprehensive review of all documentation files in documentation/ folder
 * - ANALYZED: Master Assessment & Scoring Guide (508 lines) - complete scoring symphony architecture
 * - REVIEWED: Scoring Architecture & Strategy - four-tier health intelligence system
 * - EXAMINED: Scoring System Deep Dive - dynamic multi-faceted engine design
 * - STUDIED: Refactoring & Maintenance Guide - configuration-over-code principles
 * - ANALYZED: Biomarker Reference Guide - 100+ biomarkers with clinical impact mapping
 * - REVIEWED: Engine documentation (Intentionality, Objective, Qualitative) - four-engine symphony
 * - EXAMINED: Symptom Assessment Questions - 25-question health optimization assessment
 * - STUDIED: Individual assessment scoring guides (Health, Weight Loss, Hair, Hormone, etc.)
 * - UNDERSTOOD: Complete system purpose, goals, and capabilities through documentation analysis
 * - IDENTIFIED: Four-engine scoring symphony: Quantitative (Potential), Qualitative (Reality), Objective (Actuality), Intentionality (Alignment)
 * - COMPREHENDED: Health pillar system: Mind, Body, Lifestyle, Aesthetics with weighted scoring
 * - ANALYZED: Biomarker integration with 100+ lab markers for objective health validation
 * - UNDERSTOOD: Goal-based intentionality scoring with alignment boost system
 * - COMPREHENDED: Symptom qualification engine with severity/frequency mapping
 * - ANALYZED: Complete assessment ecosystem with 10+ specialized health assessments
 * - UNDERSTOOD: Configuration-driven architecture for maintainability and scalability
 * - COMPREHENDED: User journey from assessment to personalized health optimization
 * - ANALYZED: Data flow from raw answers to final ENNU LIFE SCORE calculation
 * - UNDERSTOOD: Strategic importance of proprietary scoring algorithm and data platform
 * - COMPREHENDED: Complete system capabilities for health assessment and optimization
 * 
 * 62.1.12 - COMPREHENSIVE DATA SAVING AUDIT REPORT
 * - CREATED: Comprehensive audit report of all fields saving and assessment submission processes
 * - IDENTIFIED: Critical data integrity issues including health goals data inconsistency (RESOLVED)
 * - ANALYZED: Form submission data validation insufficiency creating major security risks
 * - DISCOVERED: Inconsistent meta key naming conventions causing data fragmentation
 * - IDENTIFIED: Inefficient database query patterns leading to performance degradation
 * - FOUND: Inadequate error handling and recovery mechanisms causing system instability
 * - REVEALED: Insufficient data sanitization creating security vulnerabilities
 * - PROVIDED: Complete execution plan with 3-phase implementation roadmap
 * - INCLUDED: Technical implementation details for all critical fixes
 * - ADDED: Performance optimization strategies for database query efficiency
 * - CREATED: Data integrity monitoring and verification systems
 * - PROVIDED: Security enhancement recommendations with code examples
 * - INCLUDED: Success metrics and implementation checklist for systematic improvement
 * - ENHANCED: System documentation with comprehensive technical analysis
 * - IMPROVED: Development roadmap with prioritized implementation matrix
 * - ADDED: Critical warnings and rollback procedures for safe implementation
 * - ENHANCED: Overall system understanding and maintenance capabilities
 * 
 * 62.1.11 - USER DASHBOARD HEALTH GOALS LEGIBILITY FIX & LEGACY SECTION REMOVAL
 * - FIXED: Health goals text not legible in light mode due to hardcoded white color
 * - ENHANCED: Goal pill text now uses CSS variables for proper light/dark mode contrast
 * - IMPROVED: Health goals text visibility in both light and dark themes
 * - REMOVED: Legacy health optimizations section from user dashboard for cleaner interface
 * - ENHANCED: User dashboard now has cleaner, more focused layout without outdated content
 * - IMPROVED: Overall user experience with better text readability and streamlined interface
 * - ENHANCED: Consistent theming support across all health goals elements
 * - IMPROVED: Visual hierarchy and content organization in user dashboard
 * 
 * 62.1.10 - COMPREHENSIVE PAGE LISTING FIX
 * - FIXED: Page listing now shows ALL required pages, not just currently mapped ones
 * - IMPLEMENTED: get_all_required_pages() method to generate complete page list
 * - ADDED: Dynamic assessment page generation based on actual assessment definitions
 * - ENHANCED: Shows every single page that should exist in the system (40+ pages)
 * - IMPLEMENTED: Complete page breakdown by category with accurate counts
 * - ADDED: Expected URLs and content for all missing pages
 * - ENHANCED: Visual indicators for assigned vs missing pages
 * - IMPLEMENTED: Comprehensive assessment page coverage (Form, Results, Details, Consultation)
 * - ADDED: Real-time page requirement calculation based on assessment definitions
 * - ENHANCED: Professional page organization with clear categorization
 * - IMPLEMENTED: Complete transparency of all system page requirements
 * - ADDED: Missing page detection with expected slugs and URLs
 * - ENHANCED: User experience with complete system overview
 * 
 * 62.1.9 - COMPREHENSIVE PAGE LISTING SYSTEM
 * - IMPLEMENTED: Complete listing of every single page in the system
 * - ADDED: Comprehensive page breakdown by category (Core, Consultation, Assessment types)
 * - ENHANCED: Assessment pages grouped by type with all sub-pages (Form, Results, Details, Consultation)
 * - ADDED: Page count displays for each category showing total pages
 * - IMPLEMENTED: Detailed page information for every single page including ID, title, status, and URL
 * - ENHANCED: Visual organization with category headers and page grouping
 * - ADDED: Complete assessment page breakdown showing all 4 page types per assessment
 * - IMPLEMENTED: Missing page indicators for unassigned pages with expected slugs
 * - ENHANCED: Professional layout with clear visual hierarchy and organization
 * - ADDED: Comprehensive system overview showing total page count and breakdown
 * - IMPLEMENTED: Clickable URLs for every assigned page in the complete listing
 * - ENHANCED: Status indicators for all pages with color-coded borders
 * - ADDED: Page type labels (Form, Results, Details, Consultation) for clarity
 * - IMPLEMENTED: Organized display making it easy to find and manage any page
 * - ENHANCED: User experience with complete transparency of all system pages
 * 
 * 62.1.8 - ADMIN PAGE ENHANCEMENTS WITH CLICKABLE URLS & PAGE IDS
 * - ENHANCED: Page dropdown options now show page IDs in parentheses for easy identification
 * - ADDED: Clickable URLs for all assigned pages with external link icons
 * - IMPLEMENTED: Detailed page status display showing page ID, title, status, and URL
 * - ENHANCED: Visual status indicators with color-coded borders (green for published, red for drafts)
 * - ADDED: Hover effects and animations for better user interaction
 * - IMPLEMENTED: Enhanced system status overview with 6 detailed statistics cards
 * - ADDED: Published vs Draft page counts in system statistics
 * - ENHANCED: Detailed page breakdown showing individual page information
 * - IMPLEMENTED: Sample assessment pages display with clickable URLs
 * - ADDED: Gradient backgrounds and subtle patterns for visual appeal
 * - ENHANCED: Mobile responsiveness for all new elements
 * - IMPLEMENTED: Improved button styling with hover animations
 * - ADDED: Loading animations and smooth transitions throughout interface
 * - ENHANCED: Error states and missing page indicators with clear visual feedback
 * - IMPLEMENTED: Professional admin interface with modern design patterns
 * - ADDED: Comprehensive page information display for better management
 * - ENHANCED: User experience with immediate visual feedback and clear status indicators
 * 
 * 62.1.7 - ADMIN PAGE COMPLETE REORGANIZATION & MODERNIZATION
 * - COMPLETELY REDESIGNED: Admin settings page with modern tabbed interface
 * - ADDED: Visual tab navigation (Page Management, Quick Actions, System Status, Danger Zone)
 * - ENHANCED: Modern CSS styling with gradients, cards, and hover effects
 * - IMPLEMENTED: Responsive grid layout for page assignments
 * - ADDED: Enhanced page status overview with statistics and visual indicators
 * - IMPLEMENTED: Color-coded status cards showing assigned vs missing pages
 * - ENHANCED: Page assignment interface with current page display and URL previews
 * - ADDED: Quick actions tab for automated page creation and menu updates
 * - IMPLEMENTED: System status tab with comprehensive page and menu statistics
 * - ADDED: Danger zone tab with clear warnings for destructive actions
 * - ENHANCED: Visual hierarchy with icons, colors, and proper spacing
 * - IMPLEMENTED: Mobile-responsive design for all admin interface elements
 * - ADDED: Interactive tab switching with smooth transitions
 * - ENHANCED: Error handling and success messaging with visual feedback
 * - IMPLEMENTED: Comprehensive page categorization (Core, Consultation, Assessment)
 * - ADDED: Real-time status indicators for all page assignments
 * - ENHANCED: User experience with clear visual feedback and intuitive navigation
 * - IMPLEMENTED: Professional admin interface matching modern WordPress standards
 * 
 * 62.1.6 - SIGNUP PAGE IMPLEMENTATION COMPLETION
 * - COMPLETED: Signup page creation in WordPress admin with proper menu integration
 * - ADDED: Signup page to menu structure with optimal positioning (order 2)
 * - ENHANCED: Comprehensive CSS styling for signup page with modern design system
 * - ADDED: JavaScript functionality for signup page interactions and animations
 * - IMPLEMENTED: Product selection modal with confirmation flow
 * - ADDED: Contact form handling with validation and success notifications
 * - ENHANCED: Smooth scrolling and scroll-triggered animations
 * - ADDED: Mobile-responsive design for all signup page elements
 * - IMPLEMENTED: Analytics tracking for product selections and conversions
 * - ADDED: Accessibility features including focus states and reduced motion support
 * - ENHANCED: Loading states and error handling for better user experience
 * - IMPLEMENTED: Form validation with real-time feedback
 * - ADDED: Intersection Observer for performance-optimized animations
 * - ENHANCED: Modal system for product selection confirmation
 * - IMPLEMENTED: Notification system for user feedback
 * - ADDED: Debounced resize handling for responsive behavior
 * - ENHANCED: Page visibility handling for animation optimization
 * - IMPLEMENTED: Comprehensive error handling and fallback systems
 * - ADDED: Development logging for debugging and analytics tracking
 * 
 * 62.1.5 - SIGNUP SHORTCODE WITH PREMIUM PRODUCT SELECTION
 * - ADDED: New [ennu-signup] shortcode for premium product selection page
 * - ADDED: ENNU Life Membership product card with comprehensive benefits list
 * - ADDED: ENNU Life Comprehensive Diagnostics product card with detailed features
 * - ENHANCED: Animated hero section with sliding text effect for "Your First Step Towards Optimization"
 * - ADDED: 5-step process explanation with numbered icons and hover effects
 * - ENHANCED: Contact section with team avatars and call-to-action button
 * - ADDED: Premium pricing display with yearly/monthly options for membership
 * - ENHANCED: Modern card-based design with glass morphism effects and hover animations
 * - ADDED: Responsive design for mobile and desktop viewing
 * - ENHANCED: Professional styling with gradient backgrounds and modern typography
 * - IMPROVED: User experience for signup flow with clear product differentiation
 * - ADDED: Clear call-to-action buttons for each product option
 * - ENHANCED: Visual hierarchy with proper spacing and color coding
 * - IMPROVED: Accessibility with proper contrast and semantic HTML structure
 * 
 * 62.1.4 - ASSESSMENT RESULTS SHORTCODE PREMIUM STYLING
 * - ENHANCED: All [ennu-assessment-results] shortcodes (generic and specific) now use premium dashboard styling
 * - ADDED: Starfield background, glass morphism cards, grid layout, and theme toggle to results pages
 * - ENHANCED: Responsive, accessible, and visually unified markup for all results pages
 * - IMPROVED: All error/debug states styled consistently with premium design system
 * - MOVED: All results-specific styles to unified CSS for maintainability
 * - ENHANCED: Overall user experience and visual consistency across all assessment results pages
 * 
 * 57.3.8 - CONSULTATION SHORTCODE CSS LOADING FIX
 * - FIXED: Consultation shortcodes not loading unified design CSS system
 * - ADDED: Consultation shortcodes to CSS loading trigger list in enqueue_results_styles()
 * - ENHANCED: All consultation shortcodes now properly load premium starfield and glass morphism styling
 * - ADDED: Consultation-specific CSS styles to unified design system
 * - ENHANCED: Theme toggle functionality for consultation pages with localStorage persistence
 * - IMPROVED: Consultation page styling with proper glass morphism cards and animations
 * - ADDED: Consultation benefits list styling with enhanced checkmark design
 * - ENHANCED: Booking embed styling with improved border radius and shadow effects
 * - IMPROVED: Contact information section with better icon alignment and hover effects
 * - ADDED: Mobile-responsive design for all consultation elements
 * - ENHANCED: Overall consultation page user experience with consistent premium styling
 * 
 * 57.3.7 - CONSULTATION SHORTCODES PREMIUM STYLING FINALIZATION
 * - ENHANCED: All consultation shortcodes now feature premium starfield background and glass morphism design
 * - ADDED: Light/dark mode toggle to all consultation pages with persistent theme preference
 * - ENHANCED: Consultation icons with appropriate SVG graphics for each consultation type
 * - IMPROVED: Consultation card styling with enhanced glass morphism effects and hover animations
 * - ADDED: Theme toggle functionality with localStorage persistence for user preference
 * - ENHANCED: Mobile-responsive design for all consultation elements including theme toggle
 * - IMPROVED: Consultation benefits list with enhanced checkmark styling and better typography
 * - ENHANCED: Booking embed styling with improved border radius and shadow effects
 * - IMPROVED: Contact information section with better icon alignment and hover effects
 * - ADDED: Enhanced card hover animations and transform effects for premium feel
 * - ENHANCED: Overall consultation page user experience with consistent premium styling
 * 
 * 57.3.6 - ASSESSMENT ARRAY STRUCTURE FIX
 * - FIXED: "Undefined array key 'key'" and "Undefined array key 'label'" errors in assessments listing
 * - FIXED: Assessment array structure mismatch between logged-in and logged-out user data
 * - ENHANCED: Proper handling of different assessment array structures for different user states
 * - IMPROVED: Assessment title extraction logic for both user_assessments and all_definitions arrays
 * - ENHANCED: Robust fallback handling for assessment data structure variations
 * - IMPROVED: Error-free assessment listing display for both logged-in and logged-out users
 * 
 * 57.3.5 - LOGGED-OUT USER EXPERIENCE ENHANCEMENT
 * - ENHANCED: [ennu-assessments] shortcode now provides beautiful experience for logged-out users
 * - ADDED: Compelling call-to-action section with "Create Free Account" and "Sign In" buttons
 * - ADDED: Assessment descriptions for logged-out users to explain each assessment type
 * - ENHANCED: Assessment cards show all available assessments for logged-out users
 * - IMPROVED: "Start Free Assessment" buttons that direct to registration page
 * - ADDED: Premium CTA card with glass morphism design and gradient icon
 * - ENHANCED: Responsive design for CTA section on mobile devices
 * - IMPROVED: Light/dark mode support for all new logged-out user elements
 * - ENHANCED: Overall user experience to encourage account creation and engagement
 * 
 * 57.3.4 - ASSESSMENTS SHORTCODE PREMIUM STYLING
 * - ENHANCED: [ennu-assessments] shortcode now uses premium dashboard styling
 * - ADDED: Starfield background animation to assessments listing page
 * - ADDED: Light/dark mode toggle to assessments page
 * - ENHANCED: Assessment cards with glass morphism design and proper icons
 * - IMPROVED: Assessment status display with scores above completed badges
 * - ENHANCED: Assessment icons with appropriate SVG graphics for each type
 * - IMPROVED: CSS loading logic to include assessments shortcode styling
 * - ENHANCED: Overall visual consistency across all assessment-related pages
 * 
 * 57.3.3 - LIGHT MODE TEXT AND LOGO FIXES
 * - FIXED: Assessment title text not visible in light mode due to poor contrast
 * - FIXED: Logo images not loading due to incorrect plugin URL path
 * - ENHANCED: Light mode styling for assessment titles with proper contrast
 * - IMPROVED: Logo path resolution using plugins_url() for reliable loading
 * - ENHANCED: Overall light mode readability and visual consistency
 * 
 * 57.3.2 - ASSESSMENT CARD LAYOUT IMPROVEMENTS
 * - IMPROVED: Assessment card layout with better score positioning above completed badge
 * - ENHANCED: Assessment title max-width for better two-line text display
 * - UPDATED: Assessment icons to be more appropriate for each assessment type
 * - IMPROVED: Score display styling and positioning for better visual hierarchy
 * - ENHANCED: Overall card layout consistency and readability
 * 
 * 57.3.1 - ASSESSMENT ICON DISPLAY FIX
 * - FIXED: Assessment icons not rendering on user dashboard due to key mismatch
 * - FIXED: Icon array keys now match actual assessment keys (health, weight-loss, hormone, etc.)
 * - ENHANCED: Assessment icons now display properly with consistent styling
 * - IMPROVED: Icon display logic with proper fallback handling
 * - ADDED: Debug logging to identify icon rendering issues
 * 
 * 57.3.0 - DASHBOARD STYLING AND URL CONSISTENCY OVERHAUL
 * 
 * 57.2.5 - GLOBAL FIELDS AND TABS COMPREHENSIVE FIX
 * - FIXED: Critical bug where gender and DOB fields were not being saved
 * - FIXED: Missing global fields in save_user_assessment_fields function
 * - FIXED: Tabs not switching due to missing field save functionality
 * - ENHANCED: Global fields array now includes all required fields (gender, DOB, health_goals, height_weight)
 * - ENHANCED: Added debug logging for global field saving operations
 * - ENHANCED: Comprehensive test script to verify both tabs and field saving
 * - IMPROVED: Field validation and sanitization for all global fields
 * - ADDED: Test form to verify tab functionality and field saving
 * - ADDED: JavaScript debugging and console logging for tab operations
 * - IMPROVED: Error handling and user feedback for save operations
 * 
 * 57.2.4 - CRITICAL FATAL ERROR FIX
 * - FIXED: Critical PHP fatal error "Cannot redeclare ENNU_Enhanced_Admin::display_global_fields_section()"
 * - FIXED: Duplicate function declaration that was breaking all admin pages
 * - FIXED: Missing save button on /wp-admin/profile.php due to fatal error
 * - FIXED: Admin tabs not working due to fatal error preventing page load
 * - FIXED: Global fields (gender, DOB, height, weight) not displaying due to fatal error
 * - ENHANCED: Asset loading with fallback detection for profile.php and user-edit.php
 * - ENHANCED: Debug logging and error handling for admin asset enqueuing
 * - ENHANCED: Global fields rendering with proper pre-population of saved values
 * - ADDED: Visible debug markers to confirm plugin output on profile pages
 * - IMPROVED: Hook registration to ensure admin functionality works in all environments
 * 
 * 57.2.3 - COMPREHENSIVE ADMIN TABS FIX
 * - FIXED: Critical admin tabs navigation issue that prevented tab switching
 * - FIXED: CSS file loading mismatch - now loads correct admin-tabs-enhanced.css
 * - FIXED: JavaScript conflicts between old and new admin scripts
 * - REMOVED: Conflicting ennu-admin.js file to prevent event listener conflicts
 * - ENHANCED: Tab initialization with multiple fallback methods
 * - ENHANCED: Global initialization function for external access
 * - ENHANCED: Event listener cleanup to prevent duplicate handlers
 * - ENHANCED: Better error handling and debugging for tab functionality
 * - IMPROVED: Asset loading to include both tabs and scores CSS files
 * - IMPROVED: Tab styling with high specificity selectors
 * - ADDED: Comprehensive debugging and console logging
 * 
 * 57.2.2 - WORDPRESS USER PROFILE INTEGRATION TEST
 * - ADDED: Comprehensive WordPress user profile integration test suite
 * - ADDED: Test for global fields (gender, DOB, height, weight) saving and retrieval
 * - ADDED: Test for data persistence across sessions
 * - ADDED: Test for ENNU Life plugin integration with user profiles
 * - ADDED: Admin interface for running tests and viewing results
 * - ENHANCED: Error handling and validation testing
 * - IMPROVED: Debugging capabilities for user profile issues
 * 
 * 57.2.1 - CRITICAL FIXES & ENHANCEMENTS
 * - FIXED: Critical assessment pre-population issue where users had to re-enter gender, DOB, height, and weight
 * - FIXED: Global fields (age, gender, height, weight, BMI) not displaying on user dashboard
 * - FIXED: Score readability issues in light mode - enhanced contrast and visibility
 * - FIXED: Contextual text readability in light mode - improved background and text contrast
 * - ENHANCED: Assessment form pre-population logic to properly use saved user data
 * - ENHANCED: Light mode CSS for better readability across all components
 * - ENHANCED: Template data passing to ensure all user data is properly displayed
 * - ADDED: Debug logging to identify data flow issues
 * - IMPROVED: Error handling and fallback logic for missing user data
 * - UPDATED: All documentation to reflect current functionality
 * 
 * 57.2.0 - Previous version with health goals and assessment improvements
||||||| f31b4df
 * Description: Advanced health assessment system with comprehensive user scoring
 * Version: 61.5.1
 * Author: ENNU Life Development Team
 * License: GPLv2 or later
 * Text Domain: ennulifeassessments
 * Domain Path:       /languages
=======
 * Description: Comprehensive health assessment system with advanced scoring, user dashboards, health goal tracking, and biomarker management.
 * Version: 62.7.3
 * Author: ENNU Life
 * Author URI: https://ennulife.com
 * Text Domain: ennu-life
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
>>>>>>> origin/main
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
<<<<<<< HEAD
define( 'ENNU_LIFE_VERSION', '62.1.24' );
||||||| f31b4df
define( 'ENNU_LIFE_VERSION', '61.5.1' );
=======
define( 'ENNU_LIFE_VERSION', '62.7.3' );
>>>>>>> origin/main
// Plugin paths - with safety checks
if ( function_exists( 'plugin_dir_path' ) ) {
	define( 'ENNU_LIFE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
} else {
	define( 'ENNU_LIFE_PLUGIN_PATH', dirname( __FILE__ ) . '/' );
}

if ( function_exists( 'plugin_dir_url' ) ) {
	define( 'ENNU_LIFE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
} else {
	define( 'ENNU_LIFE_PLUGIN_URL', '' );
}

// Biomarker management classes are now loaded in load_dependencies()

// Main plugin class - with class existence check
if ( ! class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {

	class ENNU_Life_Enhanced_Plugin {

		/**
		 * Single instance of the plugin
		 */
		private static $instance = null;

		/**
		 * Plugin components
		 */
		private $database     = null;
		private $admin        = null;
		private $form_handler = null;
		private $shortcodes   = null;
		private $health_goals_ajax = null;

		/**
		 * Get single instance
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			// Register activation/deactivation hooks
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
			register_uninstall_hook( __FILE__, array( 'ENNU_Life_Enhanced_Plugin', 'uninstall' ) );

			// Load dependencies and initialize the plugin on `plugins_loaded`
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		/**
		 * Initialize plugin
		 */
		public function init() {
			// Load dependencies and textdomain first
            error_log('ENNU Life Plugin: Initializing...');
			$this->load_dependencies();
			// $this->load_textdomain(); // This is called too early. It will be moved to the init hook.

			// Initialize components
			$this->init_components();

			// Setup all hooks
			$this->setup_hooks();
            error_log('ENNU Life Plugin: Initialization Complete.');
		}

		/**
		 * Load all dependencies
		 */
		private function load_dependencies() {
			$includes = array(
                // Core Infrastructure
				'class-enhanced-database.php',
				'class-enhanced-admin.php',
                'class-ajax-security.php',
				'class-compatibility-manager.php',
				'class-security-validator.php',
				'class-data-access-control.php',
				'class-template-security.php',
				'class-input-sanitizer.php',
				'class-csrf-protection.php',
                
                // Biomarker Management System
                'class-biomarker-manager.php',
                'class-lab-import-manager.php',
                'class-smart-recommendation-engine.php',
                
                // New Scoring Engine Architecture
                'class-assessment-calculator.php',
                'class-category-score-calculator.php',
                'class-pillar-score-calculator.php',
                'class-health-optimization-calculator.php',
                'class-potential-score-calculator.php',
                'class-new-life-score-calculator.php',
                'class-recommendation-engine.php',
                'class-score-completeness-calculator.php',
                'class-ennu-life-score-calculator.php',
                'class-biomarker-admin.php',
                'class-wp-fusion-integration.php',
                
<<<<<<< HEAD
                // Four-Engine Scoring Symphony Implementation
                'class-intentionality-engine.php',
                'class-qualitative-engine.php',
                'class-objective-engine.php',
                'class-biomarker-manager.php',
                'class-biomarker-ajax.php',
                'class-health-goals-ajax.php',
                'migrations/health-goals-migration.php',
                
||||||| f31b4df
=======
                // Intentionality Engine (Phase 1 Implementation)
                'class-intentionality-engine.php',
                'class-health-goals-ajax.php',
                'migrations/health-goals-migration.php',
                
>>>>>>> origin/main
                // Main Orchestrator and Frontend Classes
				'class-scoring-system.php',
				'class-assessment-shortcodes.php',
				'class-comprehensive-assessment-display.php',
				'class-score-cache.php',
				'class-centralized-symptoms-manager.php',
			);

			foreach ( $includes as $file ) {
				$file_path = ENNU_LIFE_PLUGIN_PATH . 'includes/' . $file;
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
                    error_log('ENNU Life Plugin: Loaded dependency: ' . $file);
				} else {
                    error_log('ENNU Life Plugin: FAILED to load dependency: ' . $file);
                }
			}
		}

		/**
		 * Initialize components
		 */
		private function init_components() {
			// Initialize database - with class existence check
			if ( class_exists( 'ENNU_Enhanced_Database' ) ) {
				$this->database = new ENNU_Enhanced_Database();
			}

			// Initialize admin - with class existence check
			if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
				$this->admin = new ENNU_Enhanced_Admin();
			}

			// Initialize Health Goals AJAX handlers - NEW
			if ( class_exists( 'ENNU_Health_Goals_Ajax' ) ) {
				$this->health_goals_ajax = new ENNU_Health_Goals_Ajax();
				error_log('ENNU Life Plugin: Initialized Health Goals AJAX handlers');
			} else {
				error_log('ENNU Life Plugin: WARNING - ENNU_Health_Goals_Ajax class not found');
			}

			// Initialize shortcodes on init hook to ensure proper timing
			add_action( 'init', array( $this, 'init_shortcodes' ), 5 ); // Priority 5 to run before shortcode registration
		}

		/**
		 * Initialize shortcodes after WordPress functions are loaded
		 */
		public function init_shortcodes() {
			if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
				$this->shortcodes = new ENNU_Assessment_Shortcodes();
				error_log('ENNU Life Plugin: Initialized ENNU_Assessment_Shortcodes on plugins_loaded hook.');
			} else {
				error_log('ENNU Life Plugin: ERROR - ENNU_Assessment_Shortcodes class not found!');
			}
		}

		/**
		 * Setup shortcode hooks after shortcodes are initialized
		 */
		public function setup_shortcode_hooks() {
			if ( $this->shortcodes ) {
				error_log('ENNU Life Plugin: Setting up shortcode AJAX and frontend hooks.');
				add_action( 'wp_ajax_ennu_submit_assessment', array( $this->shortcodes, 'handle_assessment_submission' ) );
				add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this->shortcodes, 'handle_assessment_submission' ) );
				add_action( 'wp_ajax_ennu_check_email', array( $this->shortcodes, 'ajax_check_email_exists' ) );
				add_action( 'wp_ajax_nopriv_ennu_check_email', array( $this->shortcodes, 'ajax_check_email_exists' ) );
				add_action( 'wp_ajax_ennu_check_auth_state', array( $this->shortcodes, 'ajax_check_auth_state' ) );
				add_action( 'wp_ajax_nopriv_ennu_check_auth_state', array( $this->shortcodes, 'ajax_check_auth_state' ) );
				add_action( 'wp_enqueue_scripts', array( $this->shortcodes, 'enqueue_chart_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( $this->shortcodes, 'enqueue_results_styles' ) );
			} else {
				error_log('ENNU Life Plugin: ERROR - shortcodes object is still null during setup_shortcode_hooks!');
			}
		}

		/**
		 * Setup hooks
		 */
		private function setup_hooks() {
			// Load textdomain on the correct hook
			add_action( 'init', array( $this, 'load_textdomain' ) );

			// Frontend Asset Hooks
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

			// Admin Hooks - ALWAYS register these hooks
			if ( $this->admin ) {
				error_log('ENNU Life Plugin: Setting up admin hooks...');
				
				add_action( 'admin_menu', array( $this->admin, 'add_admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_admin_assets' ) );
				add_action( 'show_user_profile', array( $this->admin, 'show_user_assessment_fields' ) );
				add_action( 'edit_user_profile', array( $this->admin, 'show_user_assessment_fields' ) );
				add_action( 'personal_options_update', array( $this->admin, 'save_user_assessment_fields' ) );
				add_action( 'edit_user_profile_update', array( $this->admin, 'save_user_assessment_fields' ) );
<<<<<<< HEAD
				
				// v57.1.0: Admin AJAX actions for user profile page
||||||| f31b4df
			}

					// Shortcode and AJAX Hooks will be set up after shortcodes are initialized
		add_action( 'init', array( $this, 'setup_shortcode_hooks' ), 10 ); // Priority 10 to run after shortcode init (priority 5)

			// v57.1.0: Admin AJAX actions for user profile page
			if ( is_admin() && $this->admin ) {
=======
				
				// v62.7.0: Biomarker Management Tab Integration
				add_action( 'show_user_profile', array( $this->admin, 'add_biomarker_management_tab' ) );
				add_action( 'edit_user_profile', array( $this->admin, 'add_biomarker_management_tab' ) );
				
				// v57.1.0: Admin AJAX actions for user profile page
>>>>>>> origin/main
				add_action( 'wp_ajax_ennu_recalculate_all_scores', array( $this->admin, 'handle_recalculate_all_scores' ) );
				add_action( 'wp_ajax_ennu_clear_all_assessment_data', array( $this->admin, 'handle_clear_all_assessment_data' ) );
				add_action( 'wp_ajax_ennu_clear_single_assessment_data', array( $this->admin, 'handle_clear_single_assessment_data' ) );
<<<<<<< HEAD
				
				error_log('ENNU Life Plugin: Admin hooks registered successfully');
			} else {
				error_log('ENNU Life Plugin: ERROR - Admin instance is null, cannot register admin hooks!');
||||||| f31b4df
=======
				
				// v62.5.0: Centralized Symptoms Management AJAX actions
				add_action( 'wp_ajax_ennu_update_centralized_symptoms', array( $this->admin, 'handle_update_centralized_symptoms' ) );
				add_action( 'wp_ajax_ennu_populate_centralized_symptoms', array( $this->admin, 'handle_populate_centralized_symptoms' ) );
				add_action( 'wp_ajax_ennu_clear_symptom_history', array( $this->admin, 'handle_clear_symptom_history' ) );
				add_action( 'wp_ajax_ennu_test_ajax', array( $this->admin, 'handle_test_ajax' ) );
				
				// v62.7.0: Biomarker Management AJAX actions
				add_action( 'wp_ajax_ennu_import_lab_results', array( $this->admin, 'ajax_import_lab_results' ) );
				add_action( 'wp_ajax_ennu_save_biomarker', array( $this->admin, 'ajax_save_biomarker' ) );
				
				error_log('ENNU Life Plugin: Admin hooks registered successfully');
			} else {
				error_log('ENNU Life Plugin: ERROR - Admin instance is null, cannot register admin hooks!');
>>>>>>> origin/main
			}

			// Shortcode and AJAX Hooks will be set up after shortcodes are initialized
			add_action( 'init', array( $this, 'setup_shortcode_hooks' ), 10 ); // Priority 10 to run after shortcode init (priority 5)
		}

		/**
		 * Enqueue frontend scripts and styles.
		 */
		public function enqueue_frontend_scripts() {
			global $post;

			$has_assessment_shortcode = false;
			if ( is_a( $post, 'WP_Post' ) ) {
				$assessment_shortcodes = array(
					'ennu-welcome',
					'ennu-hair',
					'ennu-ed-treatment',
					'ennu-weight-loss',
					'ennu-health',
					'ennu-skin',
					'ennu-sleep',
					'ennu-hormone',
					'ennu-menopause',
					'ennu-testosterone',
					'ennu-health-optimization',
				);
				foreach ( $assessment_shortcodes as $shortcode ) {
					if ( has_shortcode( $post->post_content, $shortcode ) ) {
						$has_assessment_shortcode = true;
						break;
					}
				}
			}

			if ( $has_assessment_shortcode ) {
				wp_enqueue_style( 'ennu-frontend-forms', ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-frontend-forms.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_script( 'ennu-frontend-forms', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-frontend-forms.js', array(), ENNU_LIFE_VERSION, true );
				wp_localize_script(
					'ennu-frontend-forms',
					'ennu_ajax',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'nonce'    => wp_create_nonce( 'ennu_ajax_nonce' ),
					)
				);
			}

			// --- PHASE 3 REFACTOR: Enqueue Dashboard Styles ---
			$has_dashboard_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu-user-dashboard' );
			$has_assessments_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu-assessments' );

			// --- v57.0.0 Refactor: Check for details page shortcodes ---
			$has_details_shortcode = false;
			if ( is_a( $post, 'WP_Post' ) ) {
				$content = $post->post_content;
				if ( strpos( $content, 'ennu-' ) !== false && strpos( $content, '-assessment-details' ) !== false ) {
					$has_details_shortcode = true;
				}
			}

			// v57.0.3: UNIFIED ASSET LOADING. Load dashboard assets if ANY relevant shortcode is present.
			if ( $has_dashboard_shortcode || $has_details_shortcode || $has_assessment_shortcode || $has_assessments_shortcode ) {
				// Enqueue Font Awesome for icons
				wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4' );

				wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION . '.' . time() );
				wp_enqueue_script( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
			}
			// --- END PHASE 3 REFACTOR ---
		}

		/**
		 * Load textdomain
		 */
		public function load_textdomain() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'ennulifeassessments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			}
		}

		/**
		 * Plugin activation
		 */
		public function activate() {
			// The main 'init' hook has already run at this point, so all dependencies
			// are loaded and components are initialized. We can directly access the
			// database object from the main plugin instance.
			if ( $this->database && method_exists( $this->database, 'create_tables' ) ) {
				$this->database->create_tables();
			}

			// Set default options - with safety checks
			if ( function_exists( 'add_option' ) ) {
				add_option( 'ennu_life_version', ENNU_LIFE_VERSION );
				add_option( 'ennu_life_activated', time() );
			}

			// Flush rewrite rules - with safety check
			if ( function_exists( 'flush_rewrite_rules' ) ) {
				flush_rewrite_rules();
			}
		}

		/**
		 * Plugin deactivation
		 */
		public function deactivate() {
			// Flush rewrite rules - with safety check
			if ( function_exists( 'flush_rewrite_rules' ) ) {
				flush_rewrite_rules();
			}
		}

		/**
		 * Plugin uninstallation
		 */
		public static function uninstall() {
			global $wpdb;

			// Delete options
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'ennu_%'" );

			// Delete user meta
			$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'ennu_%'" );

			// Flush rewrite rules
			if ( function_exists( 'flush_rewrite_rules' ) ) {
				flush_rewrite_rules();
			}
		}

		/**
		 * Get database instance
		 */
		public function get_database() {
			return $this->database;
		}

		/**
		 * Get admin instance
		 */
		public function get_admin() {
			return $this->admin;
		}

		/**
		 * Get shortcodes instance
		 */
		public function get_shortcodes() {
			return $this->shortcodes;
		}

		/**
		 * Getter for the shortcode handler instance.
		 * This is the definitive fix for the admin panel fatal errors.
		 *
		 * @return ENNU_Assessment_Shortcodes
		 */
		public function get_shortcode_handler() {
			return $this->shortcodes;
		}

		/**
		 * Check if plugin is compatible
		 */
		public static function is_compatible() {
			// Check PHP version
			if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
				return false;
			}

			// Check WordPress version
			if ( function_exists( 'get_bloginfo' ) ) {
				$wp_version = get_bloginfo( 'version' );
				if ( version_compare( $wp_version, '5.0', '<' ) ) {
					return false;
				}
			}

			return true;
		}
	}

} // End class_exists check

// Initialize the plugin - with safety checks
if ( class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
	// Check compatibility first
	if ( ENNU_Life_Enhanced_Plugin::is_compatible() ) {
		ENNU_Life_Enhanced_Plugin::get_instance();
	} else {
		// Show admin notice for incompatibility
		if ( function_exists( 'add_action' ) ) {
			add_action(
				'admin_notices',
				function() {
					if ( function_exists( 'current_user_can' ) && current_user_can( 'activate_plugins' ) ) {
						echo '<div class="notice notice-error"><p>';
						echo '<strong>ENNU Life Plugin:</strong> This plugin requires PHP 7.4+ and WordPress 5.0+.';
						echo '</p></div>';
					}
				}
			);
		}
	}
}

// Helper function to get plugin instance
if ( ! function_exists( 'ennu_life' ) ) {
	function ennu_life() {
		if ( class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
			return ENNU_Life_Enhanced_Plugin::get_instance();
		}
		return null;
	}
}

/**
 * A centralized, secure template loader for the plugin.
 *
 * This function handles loading template files and makes passed data
 * available to the template as local variables.
 *
 * @param string $template_name The name of the template file to load.
 * @param array  $data          An associative array of data to be extracted into variables.
 */
function ennu_load_template( $template_name, $data = array() ) {
	// Ensure the template name is a valid file name.
	$template_name = basename( $template_name );
	$template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_name;

	if ( file_exists( $template_path ) ) {
		// This is a safe, controlled use of extract for templating purposes.
		// It turns the keys of the $data array into variables for the template.
		extract( $data, EXTR_SKIP );
		include $template_path;
	}
}

/*
 * CHANGELOG
 * 
 * ## [62.6.0] - 2024-01-15
 * 
 * ### Changed
 * - **Simplified Admin Interface**: Replaced multiple management buttons with single "Recalculate Centralized Symptoms" button
 * - **Automatic Symptom Centralization**: Symptoms are now automatically centralized when assessments are completed (both quantitative and qualitative)
 * - **No Admin Intervention Required**: Removed manual populate, update, and clear history buttons - system works automatically
 * 
 * ### Added
 * - **Automatic Processing**: Added automatic symptom centralization for qualitative assessments
 * - **Streamlined UI**: Cleaner admin interface with only essential recalculation functionality
 * - **Assessment Completion Hooks**: Added hooks for other systems to respond to assessment completion
 * 
 * ## [62.5.0] - 2024-01-15
 * 
 * ### Added
 * - **Admin Symptoms Tab**: Added comprehensive centralized symptoms tab to WordPress admin user profile
 * - **Symptom Management**: Added populate, update, and clear history buttons for symptom management
 * - **Enhanced UI**: Professional admin interface with summary statistics, analytics, and organized symptom display
 * - **AJAX Handlers**: Added secure AJAX handlers for symptom management operations
 * - **JavaScript Integration**: Enhanced admin JavaScript with proper event handling and error management
 * - **Debug Tools**: Created comprehensive debugging scripts for troubleshooting admin functionality
 */

