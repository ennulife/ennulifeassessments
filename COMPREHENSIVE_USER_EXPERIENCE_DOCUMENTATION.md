# ENNU Life Plugin - Comprehensive User Experience Documentation

**Version:** 24.1.0  
**Author:** Luis Escobar 
**Date:** July 11, 2025

---

## Table of Contents

1. [Public User Experience (Logged Out)](#public-user-experience-logged-out)
2. [Authenticated User Experience (Logged In)](#authenticated-user-experience-logged-in)
3. [Step-by-Step Assessment Journey](#step-by-step-assessment-journey)
4. [Admin User Profile View](#admin-user-profile-view)
5. [Hidden System Fields Reference](#hidden-system-fields-reference)
6. [Data Flow Architecture](#data-flow-architecture)
7. [Field Mapping Reference](#field-mapping-reference)

---

## Public User Experience (Logged Out)

### What Public Users See

When visitors access assessment pages without being logged into WordPress, they experience a **limited but functional assessment system** designed to capture leads and encourage account creation.

#### Assessment Form Display
- **Full Assessment Access**: All assessment questions are visible and functional
- **Multi-Step Navigation**: Questions appear one at a time with smooth transitions
- **Progress Tracking**: Progress bar shows "Question X of Y" 
- **Auto-Advance**: Radio button selections automatically advance to next question
- **Modern Styling**: Neutral grey color scheme (#495057, #6c757d) with responsive design

#### Data Handling for Public Users
- **Temporary Storage**: Form responses are held in browser session storage
- **No Permanent Saving**: Data is NOT saved to WordPress user profiles
- **Lead Capture Focus**: Contact information questions are emphasized
- **Conversion Prompts**: Clear calls-to-action to create an account for data persistence

#### What Public Users Cannot Access
- **No Data Persistence**: Responses are lost when browser session ends
- **No Assessment History**: Cannot view previous assessment results
- **No Personalized Recommendations**: Generic recommendations only
- **No Profile Management**: Cannot update or manage personal information

#### Conversion Flow
1. **Assessment Completion**: User completes all questions
2. **Contact Information**: Required fields (name, email, phone) collected
3. **Account Creation Prompt**: Clear messaging about benefits of creating account
4. **Lead Generation**: Contact information captured for follow-up
5. **Generic Results**: Basic recommendations provided without personalization

---

## Authenticated User Experience (Logged In)

### What Logged-In Users See

WordPress authenticated users receive a **premium, personalized experience** with full data persistence and enhanced functionality.

#### Enhanced Assessment Experience
- **Pre-Filled Forms**: Global fields automatically populated from previous assessments
- **Seamless Continuation**: Can start assessment on one device, finish on another
- **Real-Time Saving**: Responses saved immediately to WordPress user profile
- **Progress Persistence**: Can leave and return to assessments without losing progress

#### Global Field Auto-Population
The following fields are automatically filled across ALL assessments:

**Core Identity Fields:**
- `ennu_global_first_name` → First Name
- `ennu_global_last_name` → Last Name  
- `ennu_global_email` → Email Address
- `ennu_global_billing_phone` → Phone Number

**Date of Birth Fields:**
- `ennu_global_dob_month` → Birth Month
- `ennu_global_dob_day` → Birth Day
- `ennu_global_dob_year` → Birth Year
- `ennu_global_calculated_age` → Calculated Age

**Demographics:**
- `ennu_global_gender` → Gender

#### Personalized Features
- **Assessment History**: View all completed assessments
- **Personalized Recommendations**: AI-driven suggestions based on complete profile
- **Progress Tracking**: See improvement over time
- **Account Management**: Update personal information anytime

#### Data Persistence Benefits
- **Cross-Device Sync**: Access data from any device
- **Long-Term Tracking**: Historical data for trend analysis
- **Personalized Communications**: Targeted emails and recommendations
- **Premium Support**: Access to specialized consultations

---

## Step-by-Step Assessment Journey

### Phase 1: Assessment Discovery
**Page Load Experience:**
- Assessment title and description prominently displayed
- Progress bar initialized showing "Question 1 of X"
- Modern, professional styling with neutral grey theme
- Mobile-responsive design adapts to screen size

### Phase 2: Question Navigation
**Multi-Step Flow:**
1. **Question Display**: Single question visible with clear title and description
2. **Answer Selection**: Radio buttons or input fields based on question type
3. **Auto-Advance**: Immediate progression to next question (for radio buttons)
4. **Manual Navigation**: Previous/Next buttons for user control
5. **Progress Update**: Progress bar and counter update in real-time

### Phase 3: Special Question Types

#### Date of Birth Questions
**User Experience:**
- Three dropdown menus: Month, Day, Year
- Real-time age calculation displayed
- Validation ensures realistic date ranges
- Combined date automatically saved to `dob_combined` field

#### Contact Information Questions
**User Experience:**
- Multiple input fields in single question
- Real-time validation (email format, phone format)
- Required field indicators
- Auto-completion from browser if available

#### Multi-Select Questions
**User Experience:**
- Multiple checkbox options
- "Select all that apply" instructions
- Visual feedback for selected items
- Ability to deselect options

### Phase 4: Assessment Completion
**Completion Flow:**
1. **Final Question**: Last question completed
2. **Success Animation**: Checkmark animation and success message
3. **Next Steps**: Clear explanation of what happens next
4. **Data Confirmation**: Confirmation that responses were saved
5. **Redirect Options**: Links to results page or additional assessments

---

## Admin User Profile View

### What Administrators See in WordPress User Profiles

When administrators view user profiles in WordPress Admin (Users → Edit User), they see **comprehensive assessment data** organized in multiple sections.

#### Section 1: Global User Data
**Display Format:**
```
Global User Data (Persistent Across All Assessments)
├── Core Identity
│   ├── First Name: [value] | ennu_global_first_name
│   ├── Last Name: [value] | ennu_global_last_name
│   ├── Email Address: [value] | ennu_global_email
│   └── Phone Number: [value] | ennu_global_billing_phone
├── Date of Birth
│   ├── Birth Month: [value] | ennu_global_dob_month
│   ├── Birth Day: [value] | ennu_global_dob_day
│   ├── Birth Year: [value] | ennu_global_dob_year
│   ├── Combined DOB: [value] | ennu_global_dob_combined
│   └── Calculated Age: [value] | ennu_global_calculated_age
└── Demographics
    └── Gender: [value] | ennu_global_gender
```

#### Section 2: Assessment-Specific Data
**For Each Assessment Type (Hair, Weight Loss, Health, Skin, ED Treatment):**

**Assessment Metadata:**
- Completion Status: `ennu_{type}_completion_status`
- Completion Date: `ennu_{type}_completion_date`
- Calculated Score: `ennu_{type}_calculated_score`
- Score Interpretation: `ennu_{type}_score_interpretation`

**All Questions & Responses:**
- Q1: [Question Title] | `{type}_q1` | [Answer or "Not answered"]
- Q2: [Question Title] | `{type}_q2` | [Answer or "Not answered"]
- [... continues for all questions]

**Available Options Display:**
For each question, admins see all possible answer choices with their values.

#### Section 3: Hidden System Fields
**Assessment-Specific System Data:**
```
Hidden System Fields (Assessment-Specific)
├── Technical Data
│   ├── IP Address: [value] | ennu_{type}_ip_address
│   ├── User Agent: [value] | ennu_{type}_user_agent
│   ├── Browser: [value] | ennu_{type}_browser
│   └── Operating System: [value] | ennu_{type}_os
├── Marketing Attribution
│   ├── UTM Source: [value] | ennu_{type}_utm_source
│   ├── UTM Medium: [value] | ennu_{type}_utm_medium
│   └── UTM Campaign: [value] | ennu_{type}_utm_campaign
├── Engagement Metrics
│   ├── Time Spent: [value] | ennu_{type}_total_time_spent
│   ├── Error Count: [value] | ennu_{type}_error_count
│   └── Retry Attempts: [value] | ennu_{type}_retry_count
└── Analytics Data
    ├── A/B Test Group: [value] | ennu_{type}_a_b_test_group
    ├── Lead Score: [value] | ennu_{type}_lead_score
    └── Engagement Score: [value] | ennu_{type}_engagement_score
```

#### Section 4: Global System Fields
**Cross-Assessment Analytics:**
```
Global System Fields (All Assessments)
├── User Tracking
│   ├── First Visit: [timestamp] | ennu_system_first_visit
│   ├── Last Activity: [timestamp] | ennu_system_last_activity
│   ├── Total Sessions: [count] | ennu_system_total_sessions
│   └── Total Page Views: [count] | ennu_system_total_page_views
├── Device & Browser
│   ├── Primary Device: [type] | ennu_system_primary_device
│   ├── Primary Browser: [browser] | ennu_system_primary_browser
│   └── Primary OS: [os] | ennu_system_primary_os
├── Marketing Attribution
│   ├── Original UTM Source: [source] | ennu_system_original_utm_source
│   ├── Original UTM Medium: [medium] | ennu_system_original_utm_medium
│   ├── Original UTM Campaign: [campaign] | ennu_system_original_utm_campaign
│   └── Original Referrer: [url] | ennu_system_original_referrer
└── Engagement Metrics
    ├── Overall Engagement Score: [score] | ennu_system_engagement_score
    ├── Conversion Probability: [percentage] | ennu_system_conversion_probability
    ├── Lead Score: [score] | ennu_system_lead_score
    └── Assessment Completion Rate: [percentage] | ennu_system_completion_rate
```

### Admin Interface Features

#### Visual Organization
- **Color-Coded Sections**: Different background colors for field types
- **Expandable Categories**: Collapsible sections for better organization
- **Developer-Friendly IDs**: Monospace font for field IDs
- **Empty Field Indicators**: Clear marking of unfilled fields

#### Field Status Indicators
- **Filled Fields**: Normal text display
- **Empty Fields**: Italicized "Not provided" or "Not tracked"
- **System Fields**: Yellow background highlighting
- **Global Fields**: Blue background highlighting

#### Search and Filter
- **Field Search**: Quick search through field names and IDs
- **Category Filters**: Show/hide specific field categories
- **Export Options**: Export user data in various formats

---


## Hidden System Fields Reference

### Purpose and Usage
Hidden system fields capture **behind-the-scenes data** that users never see but provide valuable insights for administrators, marketers, and developers.

### Global System Fields (Cross-Assessment)

#### User Tracking Fields
| Field ID | Field Name | Data Type | Purpose |
|----------|------------|-----------|---------|
| `ennu_system_first_visit` | First Visit Timestamp | DateTime | Track when user first discovered site |
| `ennu_system_last_activity` | Last Activity Timestamp | DateTime | Monitor user engagement recency |
| `ennu_system_total_sessions` | Total Sessions Count | Integer | Measure user return behavior |
| `ennu_system_total_page_views` | Total Page Views | Integer | Track overall site engagement |
| `ennu_system_session_duration_avg` | Average Session Duration | Integer (seconds) | Measure engagement depth |

#### Device & Browser Analytics
| Field ID | Field Name | Data Type | Purpose |
|----------|------------|-----------|---------|
| `ennu_system_primary_device` | Primary Device Type | String | Optimize for user's preferred device |
| `ennu_system_primary_browser` | Primary Browser | String | Ensure compatibility |
| `ennu_system_primary_os` | Primary Operating System | String | Technical support insights |
| `ennu_system_screen_resolutions` | Screen Resolution History | Array | UI/UX optimization data |
| `ennu_system_mobile_usage_percentage` | Mobile Usage Percentage | Float | Mobile-first design decisions |

#### Marketing Attribution
| Field ID | Field Name | Data Type | Purpose |
|----------|------------|-----------|---------|
| `ennu_system_original_utm_source` | Original UTM Source | String | First-touch attribution |
| `ennu_system_original_utm_medium` | Original UTM Medium | String | Channel performance analysis |
| `ennu_system_original_utm_campaign` | Original UTM Campaign | String | Campaign ROI measurement |
| `ennu_system_original_referrer` | Original Referrer URL | String | Traffic source analysis |
| `ennu_system_attribution_model` | Attribution Model Used | String | Marketing analytics methodology |

#### Engagement & Conversion
| Field ID | Field Name | Data Type | Purpose |
|----------|------------|-----------|---------|
| `ennu_system_engagement_score` | Overall Engagement Score | Float (0-100) | Composite engagement metric |
| `ennu_system_conversion_probability` | Conversion Probability | Float (0-1) | Predictive analytics |
| `ennu_system_lead_score` | Lead Score | Integer | Sales prioritization |
| `ennu_system_customer_lifetime_value` | Predicted CLV | Float | Revenue forecasting |
| `ennu_system_churn_risk_score` | Churn Risk Score | Float (0-1) | Retention strategy |

### Assessment-Specific System Fields

#### Technical Tracking (Per Assessment)
| Field Pattern | Field Name | Data Type | Purpose |
|---------------|------------|-----------|---------|
| `ennu_{type}_ip_address` | IP Address at Submission | String | Security and location tracking |
| `ennu_{type}_user_agent` | User Agent String | String | Browser/device identification |
| `ennu_{type}_session_id` | Session ID | String | Session correlation |
| `ennu_{type}_form_version` | Form Version Used | String | A/B testing and updates |
| `ennu_{type}_submission_method` | Submission Method | String | AJAX vs form post tracking |

#### Performance Metrics (Per Assessment)
| Field Pattern | Field Name | Data Type | Purpose |
|---------------|------------|-----------|---------|
| `ennu_{type}_page_load_time` | Page Load Time (ms) | Integer | Performance optimization |
| `ennu_{type}_form_interaction_time` | Form Interaction Time | Integer (seconds) | User experience analysis |
| `ennu_{type}_total_time_spent` | Total Time Spent | Integer (seconds) | Engagement measurement |
| `ennu_{type}_question_times` | Time Per Question | Array | Question difficulty analysis |
| `ennu_{type}_abandonment_points` | Abandonment Points | Array | Form optimization insights |

#### User Behavior (Per Assessment)
| Field Pattern | Field Name | Data Type | Purpose |
|---------------|------------|-----------|---------|
| `ennu_{type}_retry_count` | Number of Retries | Integer | User experience issues |
| `ennu_{type}_error_count` | Error Count | Integer | Form validation problems |
| `ennu_{type}_validation_failures` | Validation Failures | Array | Specific error tracking |
| `ennu_{type}_back_button_usage` | Back Button Usage Count | Integer | Navigation pattern analysis |
| `ennu_{type}_help_requests` | Help Requests | Integer | Support need indicators |

#### Marketing & Personalization (Per Assessment)
| Field Pattern | Field Name | Data Type | Purpose |
|---------------|------------|-----------|---------|
| `ennu_{type}_a_b_test_group` | A/B Test Group | String | Experiment tracking |
| `ennu_{type}_personalization_profile` | Personalization Profile ID | String | Customization tracking |
| `ennu_{type}_recommendation_engine_version` | Recommendation Engine Version | String | Algorithm versioning |
| `ennu_{type}_content_variant` | Content Variant Shown | String | Content testing |
| `ennu_{type}_conversion_funnel_stage` | Conversion Funnel Stage | String | Sales process tracking |

---

## Data Flow Architecture

### Visual Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    ENNU LIFE DATA FLOW ARCHITECTURE             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   PUBLIC USER   │    │ LOGGED-IN USER  │    │  ADMINISTRATOR  │
│   (Logged Out)  │    │ (Authenticated) │    │   (Full Access) │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          ▼                      ▼                      ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ FRONTEND FORMS  │    │ FRONTEND FORMS  │    │ ADMIN INTERFACE │
│ • All questions │    │ • Pre-filled    │    │ • All fields    │
│ • No saving     │    │ • Auto-save     │    │ • Field IDs     │
│ • Lead capture  │    │ • Persistent    │    │ • System data   │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          ▼                      ▼                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    WORDPRESS DATABASE LAYER                     │
│                                                                 │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │ GLOBAL FIELDS   │  │ ASSESSMENT DATA │  │ SYSTEM FIELDS   │ │
│  │ • Identity      │  │ • Question/Answer│  │ • Analytics     │ │
│  │ • Demographics  │  │ • Scores        │  │ • Tracking      │ │
│  │ • Contact Info  │  │ • Metadata      │  │ • Attribution   │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
          │                      │                      │
          ▼                      ▼                      ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ LEAD GENERATION │    │ PERSONALIZATION │    │ BUSINESS INTEL  │
│ • Email lists   │    │ • Recommendations│    │ • Reports       │
│ • Follow-up     │    │ • Custom content │    │ • Analytics     │
│ • Nurturing     │    │ • User journey   │    │ • Optimization  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Data Storage Strategy

#### 1. Global Fields (Cross-Assessment)
**Storage Location:** WordPress `wp_usermeta` table  
**Prefix:** `ennu_global_`  
**Purpose:** Persistent user data that applies to all assessments

**Data Flow:**
1. User completes first assessment
2. Global fields extracted and saved with `ennu_global_` prefix
3. Subsequent assessments auto-populate from global fields
4. Updates to global fields propagate across all assessments

#### 2. Assessment-Specific Fields
**Storage Location:** WordPress `wp_usermeta` table  
**Prefix:** `ennu_{assessment_type}_`  
**Purpose:** Assessment-specific responses and metadata

**Data Flow:**
1. User completes assessment questions
2. Each response saved with assessment-specific prefix
3. Scores calculated and stored
4. Metadata (completion date, time spent) recorded

#### 3. System Fields
**Storage Location:** WordPress `wp_usermeta` table  
**Prefix:** `ennu_system_`  
**Purpose:** Hidden analytics and tracking data

**Data Flow:**
1. System automatically captures technical data
2. Marketing attribution tracked on first visit
3. Engagement metrics calculated continuously
4. Performance data logged for optimization

### Data Synchronization

#### Real-Time Sync
- **AJAX Submissions:** Immediate saving to database
- **Progress Tracking:** Real-time updates during assessment
- **Global Field Updates:** Instant propagation across assessments

#### Batch Processing
- **Analytics Calculations:** Hourly engagement score updates
- **Lead Scoring:** Daily recalculation of lead scores
- **Data Quality Checks:** Weekly validation and cleanup

---

## Field Mapping Reference

### Complete Field Inventory

#### Global User Fields (12 Total)
| Display Name | Field ID | Data Type | Source | Auto-Populate |
|--------------|----------|-----------|--------|---------------|
| First Name | `ennu_global_first_name` | String | User Input | Yes |
| Last Name | `ennu_global_last_name` | String | User Input | Yes |
| Email Address | `ennu_global_email` | Email | User Input | Yes |
| Phone Number | `ennu_global_billing_phone` | Phone | User Input | Yes |
| Birth Month | `ennu_global_dob_month` | String (01-12) | User Input | Yes |
| Birth Day | `ennu_global_dob_day` | String (01-31) | User Input | Yes |
| Birth Year | `ennu_global_dob_year` | String (YYYY) | User Input | Yes |
| Combined DOB | `ennu_global_dob_combined` | Date (YYYY-MM-DD) | Calculated | Yes |
| Calculated Age | `ennu_global_calculated_age` | Integer | Calculated | Yes |
| Gender | `ennu_global_gender` | String | User Input | Yes |
| Profile Created | `ennu_global_profile_created` | DateTime | System | No |
| Last Updated | `ennu_global_last_updated` | DateTime | System | No |

#### Assessment Question Fields (Per Assessment Type)

**Welcome Assessment (6 Questions):**
| Question | Field ID | Data Type | Required |
|----------|----------|-----------|----------|
| Q1: Gender | `welcome_q1` | String | Yes |
| Q2: Date of Birth | `welcome_q2` | Date | Yes |
| Q3: Health Goals | `welcome_q3` | Array | Yes |
| Q4: Name | `welcome_q4` | Object | Yes |
| Q5: Email | `welcome_q5` | Email | Yes |
| Q6: Phone | `welcome_q6` | Phone | Yes |

**Hair Assessment (11 Questions):**
| Question | Field ID | Data Type | Required |
|----------|----------|-----------|----------|
| Q1: Date of Birth | `hair_q1` | Date | Yes |
| Q2: Gender | `hair_q2` | String | Yes |
| Q3: Hair Concerns | `hair_q3` | String | Yes |
| Q4: Duration | `hair_q4` | String | Yes |
| Q5: Speed of Loss | `hair_q5` | String | Yes |
| Q6: Family History | `hair_q6` | String | Yes |
| Q7: Stress Level | `hair_q7` | String | Yes |
| Q8: Diet Quality | `hair_q8` | String | Yes |
| Q9: Previous Treatments | `hair_q9` | String | Yes |
| Q10: Goals | `hair_q10` | String | Yes |
| Q11: Contact Info | `hair_q11` | Object | Yes |

**Weight Loss Assessment (13 Questions):**
| Question | Field ID | Data Type | Required |
|----------|----------|-----------|----------|
| Q1: Date of Birth | `weight_q1` | Date | Yes |
| Q2: Gender | `weight_q2` | String | Yes |
| Q3: Weight Goal | `weight_q3` | String | Yes |
| Q4: Motivation | `weight_q4` | String | Yes |
| Q5: Timeline | `weight_q5` | String | Yes |
| Q6: Eating Habits | `weight_q6` | String | Yes |
| Q7: Exercise Frequency | `weight_q7` | String | Yes |
| Q8: Previous Attempts | `weight_q8` | String | Yes |
| Q9: Health Conditions | `weight_q9` | String | Yes |
| Q10: Medications | `weight_q10` | String | Yes |
| Q11: Support System | `weight_q11` | String | Yes |
| Q12: Biggest Challenge | `weight_q12` | String | Yes |
| Q13: Contact Info | `weight_q13` | Object | Yes |

**Health Assessment (11 Questions):**
| Question | Field ID | Data Type | Required |
|----------|----------|-----------|----------|
| Q1: Date of Birth | `health_q1` | Date | Yes |
| Q2: Gender | `health_q2` | String | Yes |
| Q3: Health Goals | `health_q3` | Array | Yes |
| Q4: Current Health | `health_q4` | String | Yes |
| Q5: Energy Level | `health_q5` | String | Yes |
| Q6: Sleep Quality | `health_q6` | String | Yes |
| Q7: Stress Level | `health_q7` | String | Yes |
| Q8: Exercise Habits | `health_q8` | String | Yes |
| Q9: Diet Quality | `health_q9` | String | Yes |
| Q10: Health Concerns | `health_q10` | Array | Yes |
| Q11: Contact Info | `health_q11` | Object | Yes |

**Skin Assessment (10 Questions):**
| Question | Field ID | Data Type | Required |
|----------|----------|-----------|----------|
| Q1: Date of Birth | `skin_q1` | Date | Yes |
| Q2: Gender | `skin_q2` | String | Yes |
| Q3: Skin Type | `skin_q3` | String | Yes |
| Q4: Skin Concerns | `skin_q4` | Array | Yes |
| Q5: Current Routine | `skin_q5` | String | Yes |
| Q6: Product Usage | `skin_q6` | String | Yes |
| Q7: Sun Exposure | `skin_q7` | String | Yes |
| Q8: Lifestyle Factors | `skin_q8` | Array | Yes |
| Q9: Previous Treatments | `skin_q9` | String | Yes |
| Q10: Contact Info | `skin_q10` | Object | Yes |

**ED Treatment Assessment (12 Questions):**
| Question | Field ID | Data Type | Required |
|----------|----------|-----------|----------|
| Q1: Date of Birth | `ed_q1` | Date | Yes |
| Q2: Relationship Status | `ed_q2` | String | Yes |
| Q3: Severity | `ed_q3` | String | Yes |
| Q4: Duration | `ed_q4` | String | Yes |
| Q5: Health Conditions | `ed_q5` | Array | Yes |
| Q6: Previous Treatments | `ed_q6` | String | Yes |
| Q7: Smoking Status | `ed_q7` | String | Yes |
| Q8: Exercise Frequency | `ed_q8` | String | Yes |
| Q9: Stress Level | `ed_q9` | String | Yes |
| Q10: Treatment Goals | `ed_q10` | String | Yes |
| Q11: Medications | `ed_q11` | String | Yes |
| Q12: Contact Info | `ed_q12` | Object | Yes |

#### System Fields (80+ Total)

**Global System Fields (25 Fields):**
- User tracking: 5 fields
- Device/browser: 5 fields  
- Marketing attribution: 5 fields
- Engagement metrics: 5 fields
- Assessment completion: 5 fields

**Per-Assessment System Fields (20 Fields × 6 Assessments = 120 Fields):**
- Technical tracking: 5 fields per assessment
- Performance metrics: 5 fields per assessment
- User behavior: 5 fields per assessment
- Marketing/personalization: 5 fields per assessment

### Total Field Count: 200+ Fields

**Breakdown:**
- Global User Fields: 12
- Assessment Questions: 63 (across 6 assessments)
- Assessment Metadata: 30 (5 per assessment × 6)
- Global System Fields: 25
- Assessment System Fields: 120
- **Total: 250 Fields**

---

## Developer Implementation Notes

### Field Naming Conventions
- **Global Fields:** `ennu_global_{field_name}`
- **Assessment Fields:** `ennu_{assessment_type}_{field_name}` or `{assessment_prefix}_q{number}`
- **System Fields:** `ennu_system_{field_name}` or `ennu_{assessment_type}_{system_field}`

### Data Validation Rules
- **Required Fields:** All contact information and demographic data
- **Format Validation:** Email, phone, date formats strictly enforced
- **Range Validation:** Age ranges, score ranges validated
- **Sanitization:** All user input sanitized before storage

### Performance Considerations
- **Lazy Loading:** System fields calculated on-demand
- **Caching:** Frequently accessed data cached for performance
- **Batch Operations:** Bulk updates processed efficiently
- **Database Optimization:** Indexed fields for fast queries

### Security Measures
- **Data Encryption:** Sensitive fields encrypted at rest
- **Access Control:** Role-based access to different field types
- **Audit Logging:** All data access and modifications logged
- **GDPR Compliance:** Data retention and deletion policies enforced

---

**End of Comprehensive User Experience Documentation**

*This document provides complete visibility into the ENNU Life plugin's user experience, data architecture, and field management system. All field IDs and data flows are documented for developer reference and system maintenance.*

