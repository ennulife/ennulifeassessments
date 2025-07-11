# ENNU Life Assessment Plugin - User Experience & Field Reference

**Version**: 24.1.0  
**Last Updated**: January 7, 2025  
**Purpose**: Complete documentation of user flows, admin views, and all system fields

---

## 📋 **TABLE OF CONTENTS**

1. [Public User Experience (Logged Out)](#public-user-experience-logged-out)
2. [Authenticated User Experience (Logged In)](#authenticated-user-experience-logged-in)
3. [Step-by-Step Assessment Journey](#step-by-step-assessment-journey)
4. [Admin User Profile View](#admin-user-profile-view)
5. [Complete Field Reference](#complete-field-reference)
6. [Hidden System Fields](#hidden-system-fields)
7. [Developer Quick Reference](#developer-quick-reference)

---

## 🌐 **PUBLIC USER EXPERIENCE (LOGGED OUT)**

### **What Public Users See**

#### **Assessment Pages**
- **Access**: Can view and start any assessment
- **Form Display**: Full multi-step assessment form
- **Progress Tracking**: Shows "Question X of Y" 
- **Navigation**: Previous/Next buttons work normally
- **Auto-advance**: Radio buttons auto-advance to next question

#### **Data Collection Limitations**
- **Global Fields**: Still collected (name, email, phone, DOB, gender)
- **Assessment Responses**: Collected but not permanently saved
- **User Profile**: No permanent user profile created
- **Data Persistence**: Data lost after session ends

#### **What Happens on Submission**
```
1. User completes assessment
2. Data temporarily stored in session
3. Results page shows recommendations
4. No permanent WordPress user account created
5. Data not saved to database permanently
```

#### **Messaging to Public Users**
- **Call-to-Action**: "Create account to save your results"
- **Benefits**: "Track progress across multiple assessments"
- **Registration Prompt**: Appears after assessment completion

### **Public User Limitations**
- ❌ Cannot save assessment results permanently
- ❌ Cannot view previous assessment history
- ❌ Cannot access personalized dashboard
- ❌ Cannot receive follow-up emails
- ❌ Cannot track progress over time

---

## 🔐 **AUTHENTICATED USER EXPERIENCE (LOGGED IN)**

### **What Logged-In Users See**

#### **Enhanced Assessment Experience**
- **Pre-filled Forms**: Global fields auto-populate from profile
- **Progress Saving**: Can pause and resume assessments
- **History Access**: Can view previous assessment results
- **Personalized Dashboard**: Shows assessment completion status

#### **Data Persistence**
- **Permanent Storage**: All responses saved to WordPress user profile
- **Cross-Assessment Data**: Global fields shared across all assessments
- **Progress Tracking**: Completion status tracked per assessment
- **Historical Data**: Previous responses accessible

#### **What Happens on Submission**
```
1. User completes assessment
2. Data permanently saved to WordPress user_meta
3. Global fields updated if changed
4. Completion status marked as "yes"
5. Timestamp recorded
6. Results page shows personalized recommendations
7. Email notifications sent (if configured)
```

### **Logged-In User Benefits**
- ✅ Permanent data storage
- ✅ Pre-filled forms for faster completion
- ✅ Assessment history and progress tracking
- ✅ Personalized recommendations
- ✅ Email follow-ups and reminders
- ✅ Cross-assessment data correlation

---

## 📝 **STEP-BY-STEP ASSESSMENT JOURNEY**

### **Step 1: Landing on Assessment Page**

#### **Public User**
```
Page Load → Assessment Form Renders → Global Fields Empty
↓
User sees: Clean form with neutral grey styling
User sees: "Question 1 of [X]" in progress bar
User sees: First question with answer options
```

#### **Logged-In User**
```
Page Load → Assessment Form Renders → Global Fields Pre-filled
↓
User sees: Form with name, email, phone already filled
User sees: "Question 1 of [X]" in progress bar  
User sees: First question with answer options
User sees: "Welcome back, [First Name]" message
```

### **Step 2: Completing Assessment Questions**

#### **Both User Types**
```
Question Display → User Selects Answer → Auto-advance to Next
↓
Progress bar updates: "Question 2 of [X]"
Previous button becomes available
Smooth transition animation plays
```

#### **Navigation Options**
- **Next Button**: Advances to next question
- **Previous Button**: Returns to previous question (available after Q1)
- **Auto-advance**: Automatic progression on radio button selection
- **Progress Bar**: Visual indicator of completion percentage

### **Step 3: Final Question & Submission**

#### **Public User**
```
Last Question → Submit Button → Processing → Results Page
↓
Data stored in session only
Results show generic recommendations
Call-to-action to create account
```

#### **Logged-In User**
```
Last Question → Submit Button → Processing → Database Save → Results Page
↓
Data permanently saved to user profile
Results show personalized recommendations
Thank you message with next steps
```

### **Step 4: Results & Follow-up**

#### **Public User Results Page**
- Generic recommendations based on responses
- Call-to-action to register for personalized results
- Limited access to detailed analysis
- No follow-up emails or tracking

#### **Logged-In User Results Page**
- Personalized recommendations with user's name
- Detailed analysis based on profile history
- Links to relevant products/services
- Follow-up email scheduled
- Progress tracking updated

---

## 👨‍💼 **ADMIN USER PROFILE VIEW**

### **What Admins See in WordPress User Profiles**

When an admin views a user's profile in WordPress Admin → Users → Edit User, they see:

#### **Standard WordPress Fields**
- Username, Email, First Name, Last Name
- Role, Registration Date, Last Login
- Biographical Info, Contact Info

#### **ENNU Assessment Data Sections**

### **1. Global User Fields Section**
```
📋 ENNU Global User Data
├── Personal Information
│   ├── First Name: [value] (ennu_global_first_name)
│   ├── Last Name: [value] (ennu_global_last_name)
│   ├── Email: [value] (ennu_global_email)
│   └── Phone: [value] (ennu_global_billing_phone)
├── Demographics
│   ├── Date of Birth: [MM/DD/YYYY] (ennu_global_dob_*)
│   ├── Calculated Age: [value] (ennu_global_calculated_age)
│   └── Gender: [value] (ennu_global_gender)
└── System Data
    ├── Profile Created: [timestamp] (ennu_global_profile_created)
    ├── Last Updated: [timestamp] (ennu_global_last_updated)
    └── Data Source: [value] (ennu_global_data_source)
```

### **2. Assessment-Specific Sections**

#### **Hair Assessment Section**
```
💇‍♀️ Hair Assessment Data
├── Completion Status: ✅ Completed on [date] (ennu_hair_assessment_completed)
├── Questions & Answers:
│   ├── Q1: Hair Concern → [answer] (ennu_hair_assessment_question_1)
│   ├── Q2: Hair Type → [answer] (ennu_hair_assessment_question_2)
│   ├── Q3: Wash Frequency → [answer] (ennu_hair_assessment_question_3)
│   └── [... all 10 questions]
├── System Data:
│   ├── Started: [timestamp] (ennu_hair_assessment_started)
│   ├── Completed: [timestamp] (ennu_hair_assessment_completed_date)
│   ├── Time Taken: [duration] (ennu_hair_assessment_duration)
│   ├── IP Address: [IP] (ennu_hair_assessment_ip_address)
│   ├── User Agent: [browser] (ennu_hair_assessment_user_agent)
│   └── Form Version: [version] (ennu_hair_assessment_form_version)
└── Analytics:
    ├── Score: [calculated] (ennu_hair_assessment_score)
    ├── Recommendations: [generated] (ennu_hair_assessment_recommendations)
    └── Follow-up Status: [status] (ennu_hair_assessment_followup_status)
```

#### **Weight Loss Assessment Section**
```
⚖️ Weight Loss Assessment Data
├── Completion Status: ✅ Completed on [date] (ennu_weight_assessment_completed)
├── Questions & Answers:
│   ├── Q1: Weight Goal → [answer] (ennu_weight_assessment_question_1)
│   ├── Q2: Target Loss → [answer] (ennu_weight_assessment_question_2)
│   ├── Q3: Activity Level → [answer] (ennu_weight_assessment_question_3)
│   └── [... all 13 questions]
├── System Data:
│   ├── Started: [timestamp] (ennu_weight_assessment_started)
│   ├── Completed: [timestamp] (ennu_weight_assessment_completed_date)
│   ├── Time Taken: [duration] (ennu_weight_assessment_duration)
│   ├── IP Address: [IP] (ennu_weight_assessment_ip_address)
│   ├── User Agent: [browser] (ennu_weight_assessment_user_agent)
│   └── Form Version: [version] (ennu_weight_assessment_form_version)
└── Analytics:
    ├── BMI Calculated: [value] (ennu_weight_assessment_bmi)
    ├── Target Timeline: [calculated] (ennu_weight_assessment_timeline)
    └── Risk Assessment: [level] (ennu_weight_assessment_risk_level)
```

#### **Health Assessment Section**
```
🏥 Health Assessment Data
├── Completion Status: ○ Not Completed (ennu_health_assessment_completed)
├── Partial Progress: 3/11 questions answered
├── Questions & Answers:
│   ├── Q1: Overall Health → [answer] (ennu_health_assessment_question_1)
│   ├── Q2: Exercise → [answer] (ennu_health_assessment_question_2)
│   ├── Q3: Sleep → [answer] (ennu_health_assessment_question_3)
│   ├── Q4: Chronic Conditions → Not answered (ennu_health_assessment_question_4)
│   └── [... remaining questions not answered]
└── System Data:
    ├── Started: [timestamp] (ennu_health_assessment_started)
    ├── Last Activity: [timestamp] (ennu_health_assessment_last_activity)
    ├── Completion: 27% (ennu_health_assessment_progress)
    └── Abandonment Point: Question 4 (ennu_health_assessment_abandon_point)
```

### **3. Hidden System Fields Section**

```
🔧 System & Analytics Data (Admin Only)
├── User Behavior:
│   ├── Total Assessments Started: [count] (ennu_system_assessments_started)
│   ├── Total Assessments Completed: [count] (ennu_system_assessments_completed)
│   ├── Completion Rate: [percentage] (ennu_system_completion_rate)
│   ├── Average Time per Assessment: [duration] (ennu_system_avg_time)
│   └── Last Assessment Activity: [timestamp] (ennu_system_last_activity)
├── Technical Data:
│   ├── Plugin Version on Registration: [version] (ennu_system_plugin_version)
│   ├── Browser Fingerprint: [hash] (ennu_system_browser_fingerprint)
│   ├── Device Type: [mobile/desktop/tablet] (ennu_system_device_type)
│   ├── Referrer Source: [URL] (ennu_system_referrer)
│   └── UTM Parameters: [data] (ennu_system_utm_data)
├── Business Intelligence:
│   ├── Lead Score: [calculated] (ennu_system_lead_score)
│   ├── Engagement Level: [high/medium/low] (ennu_system_engagement)
│   ├── Recommended Products: [list] (ennu_system_product_recommendations)
│   ├── A/B Test Group: [group] (ennu_system_ab_test_group)
│   └── Customer Segment: [segment] (ennu_system_customer_segment)
└── Integration Data:
    ├── HubSpot Contact ID: [ID] (ennu_system_hubspot_id)
    ├── WooCommerce Customer ID: [ID] (ennu_system_woocommerce_id)
    ├── Email Marketing Status: [subscribed/unsubscribed] (ennu_system_email_status)
    └── CRM Sync Status: [synced/pending/failed] (ennu_system_crm_sync)
```

---

## 📊 **COMPLETE FIELD REFERENCE**

### **Global User Fields**

| Field Name | Official ID | Type | Visibility | Purpose |
|------------|-------------|------|------------|---------|
| First Name | `ennu_global_first_name` | text | User/Admin | User identification |
| Last Name | `ennu_global_last_name` | text | User/Admin | User identification |
| Email Address | `ennu_global_email` | email | User/Admin | Communication |
| Phone Number | `ennu_global_billing_phone` | text | User/Admin | Contact information |
| Birth Month | `ennu_global_dob_month` | select | User/Admin | Age calculation |
| Birth Day | `ennu_global_dob_day` | select | User/Admin | Age calculation |
| Birth Year | `ennu_global_dob_year` | select | User/Admin | Age calculation |
| Calculated Age | `ennu_global_calculated_age` | number | Admin | Demographics |
| Gender | `ennu_global_gender` | select | User/Admin | Demographics |
| Profile Created | `ennu_global_profile_created` | timestamp | Admin | System tracking |
| Last Updated | `ennu_global_last_updated` | timestamp | Admin | System tracking |
| Data Source | `ennu_global_data_source` | text | Admin | Data origin tracking |

### **Hair Assessment Fields**

| Field Name | Official ID | Type | Visibility | Purpose |
|------------|-------------|------|------------|---------|
| Primary Hair Concern | `ennu_hair_assessment_question_1` | select | User/Admin | Assessment response |
| Hair Type Description | `ennu_hair_assessment_question_2` | select | User/Admin | Assessment response |
| Wash Frequency | `ennu_hair_assessment_question_3` | select | User/Admin | Assessment response |
| Hair Loss Experience | `ennu_hair_assessment_question_4` | select | User/Admin | Assessment response |
| Previous Treatments | `ennu_hair_assessment_question_5` | select | User/Admin | Assessment response |
| Heat Styling Usage | `ennu_hair_assessment_question_6` | select | User/Admin | Assessment response |
| Hair Care Budget | `ennu_hair_assessment_question_7` | select | User/Admin | Assessment response |
| Current Hair Length | `ennu_hair_assessment_question_8` | select | User/Admin | Assessment response |
| Chemical Treatments | `ennu_hair_assessment_question_9` | select | User/Admin | Assessment response |
| Hair Goals | `ennu_hair_assessment_question_10` | select | User/Admin | Assessment response |
| Completion Status | `ennu_hair_assessment_completed` | boolean | Admin | Progress tracking |
| Completion Date | `ennu_hair_assessment_completed_date` | timestamp | Admin | Progress tracking |
| Assessment Started | `ennu_hair_assessment_started` | timestamp | Admin | System tracking |
| Time Taken | `ennu_hair_assessment_duration` | number | Admin | Analytics |
| IP Address | `ennu_hair_assessment_ip_address` | text | Admin | Security/Analytics |
| User Agent | `ennu_hair_assessment_user_agent` | text | Admin | Technical data |
| Form Version | `ennu_hair_assessment_form_version` | text | Admin | Version tracking |
| Calculated Score | `ennu_hair_assessment_score` | number | Admin | Business logic |
| Recommendations | `ennu_hair_assessment_recommendations` | text | Admin | Business logic |
| Follow-up Status | `ennu_hair_assessment_followup_status` | text | Admin | CRM integration |

### **Weight Loss Assessment Fields**

| Field Name | Official ID | Type | Visibility | Purpose |
|------------|-------------|------|------------|---------|
| Current Weight Goal | `ennu_weight_assessment_question_1` | select | User/Admin | Assessment response |
| Target Weight Loss | `ennu_weight_assessment_question_2` | select | User/Admin | Assessment response |
| Activity Level | `ennu_weight_assessment_question_3` | select | User/Admin | Assessment response |
| Diet Description | `ennu_weight_assessment_question_4` | select | User/Admin | Assessment response |
| Previous Programs | `ennu_weight_assessment_question_5` | select | User/Admin | Assessment response |
| Dietary Restrictions | `ennu_weight_assessment_question_6` | select | User/Admin | Assessment response |
| Eating Out Frequency | `ennu_weight_assessment_question_7` | select | User/Admin | Assessment response |
| Food Tracking | `ennu_weight_assessment_question_8` | select | User/Admin | Assessment response |
| Weight Loss Motivation | `ennu_weight_assessment_question_9` | select | User/Admin | Assessment response |
| Exercise Time Available | `ennu_weight_assessment_question_10` | select | User/Admin | Assessment response |
| Physical Limitations | `ennu_weight_assessment_question_11` | select | User/Admin | Assessment response |
| Target Timeline | `ennu_weight_assessment_question_12` | select | User/Admin | Assessment response |
| Support System | `ennu_weight_assessment_question_13` | select | User/Admin | Assessment response |
| Completion Status | `ennu_weight_assessment_completed` | boolean | Admin | Progress tracking |
| Completion Date | `ennu_weight_assessment_completed_date` | timestamp | Admin | Progress tracking |
| BMI Calculated | `ennu_weight_assessment_bmi` | number | Admin | Health metrics |
| Risk Assessment | `ennu_weight_assessment_risk_level` | text | Admin | Health analysis |

### **Health Assessment Fields**

| Field Name | Official ID | Type | Visibility | Purpose |
|------------|-------------|------|------------|---------|
| Overall Health Rating | `ennu_health_assessment_question_1` | select | User/Admin | Assessment response |
| Exercise Frequency | `ennu_health_assessment_question_2` | select | User/Admin | Assessment response |
| Sleep Hours | `ennu_health_assessment_question_3` | select | User/Admin | Assessment response |
| Chronic Conditions | `ennu_health_assessment_question_4` | select | User/Admin | Assessment response |
| Stress Level | `ennu_health_assessment_question_5` | select | User/Admin | Assessment response |
| Tobacco Use | `ennu_health_assessment_question_6` | select | User/Admin | Assessment response |
| Alcohol Consumption | `ennu_health_assessment_question_7` | select | User/Admin | Assessment response |
| Supplement Usage | `ennu_health_assessment_question_8` | select | User/Admin | Assessment response |
| Energy Level | `ennu_health_assessment_question_9` | select | User/Admin | Assessment response |
| Health Goals | `ennu_health_assessment_question_10` | select | User/Admin | Assessment response |
| Regular Checkups | `ennu_health_assessment_question_11` | select | User/Admin | Assessment response |
| Completion Status | `ennu_health_assessment_completed` | boolean | Admin | Progress tracking |
| Progress Percentage | `ennu_health_assessment_progress` | number | Admin | Progress tracking |
| Abandonment Point | `ennu_health_assessment_abandon_point` | text | Admin | Analytics |

### **Skin Assessment Fields**

| Field Name | Official ID | Type | Visibility | Purpose |
|------------|-------------|------|------------|---------|
| Skin Type | `ennu_skin_assessment_question_1` | select | User/Admin | Assessment response |
| Main Skin Concerns | `ennu_skin_assessment_question_2` | select | User/Admin | Assessment response |
| Skin Sensitivity | `ennu_skin_assessment_question_3` | select | User/Admin | Assessment response |
| Current Routine | `ennu_skin_assessment_question_4` | select | User/Admin | Assessment response |
| Sunscreen Usage | `ennu_skin_assessment_question_5` | select | User/Admin | Assessment response |
| Previous Treatments | `ennu_skin_assessment_question_6` | select | User/Admin | Assessment response |
| Skin Allergies | `ennu_skin_assessment_question_7` | select | User/Admin | Assessment response |
| Skincare Budget | `ennu_skin_assessment_question_8` | select | User/Admin | Assessment response |
| Breakout Frequency | `ennu_skin_assessment_question_9` | select | User/Admin | Assessment response |
| Skincare Goals | `ennu_skin_assessment_question_10` | select | User/Admin | Assessment response |
| Completion Status | `ennu_skin_assessment_completed` | boolean | Admin | Progress tracking |

### **ED Treatment Assessment Fields**

| Field Name | Official ID | Type | Visibility | Purpose |
|------------|-------------|------|------------|---------|
| Symptom Duration | `ennu_ed_treatment_assessment_question_1` | select | User/Admin | Assessment response |
| Severity Rating | `ennu_ed_treatment_assessment_question_2` | select | User/Admin | Assessment response |
| Previous Treatments | `ennu_ed_treatment_assessment_question_3` | select | User/Admin | Assessment response |
| Health Conditions | `ennu_ed_treatment_assessment_question_4` | select | User/Admin | Assessment response |
| Current Medications | `ennu_ed_treatment_assessment_question_5` | select | User/Admin | Assessment response |
| Relationship Impact | `ennu_ed_treatment_assessment_question_6` | select | User/Admin | Assessment response |
| Stress Level | `ennu_ed_treatment_assessment_question_7` | select | User/Admin | Assessment response |
| Exercise Frequency | `ennu_ed_treatment_assessment_question_8` | select | User/Admin | Assessment response |
| Sleep Quality | `ennu_ed_treatment_assessment_question_9` | select | User/Admin | Assessment response |
| Treatment Goals | `ennu_ed_treatment_assessment_question_10` | select | User/Admin | Assessment response |
| Completion Status | `ennu_ed_treatment_assessment_completed` | boolean | Admin | Progress tracking |

### **Welcome Assessment Fields**

| Field Name | Official ID | Type | Visibility | Purpose |
|------------|-------------|------|------------|---------|
| Primary Interest | `ennu_welcome_assessment_question_1` | select | User/Admin | Assessment response |
| Health Goals | `ennu_welcome_assessment_question_2` | select | User/Admin | Assessment response |
| Current Health Rating | `ennu_welcome_assessment_question_3` | select | User/Admin | Assessment response |
| Medication Usage | `ennu_welcome_assessment_question_4` | select | User/Admin | Assessment response |
| Known Allergies | `ennu_welcome_assessment_question_5` | select | User/Admin | Assessment response |
| Completion Status | `ennu_welcome_assessment_completed` | boolean | Admin | Progress tracking |

---

## 🔒 **HIDDEN SYSTEM FIELDS**

These fields are never shown to users but are visible to admins for system management:

### **User Behavior Analytics**

| Field Name | Official ID | Type | Purpose |
|------------|-------------|------|---------|
| Total Assessments Started | `ennu_system_assessments_started` | number | Engagement tracking |
| Total Assessments Completed | `ennu_system_assessments_completed` | number | Conversion tracking |
| Completion Rate | `ennu_system_completion_rate` | percentage | Performance metrics |
| Average Assessment Time | `ennu_system_avg_time` | duration | UX analytics |
| Last Assessment Activity | `ennu_system_last_activity` | timestamp | Engagement tracking |
| Session Count | `ennu_system_session_count` | number | User engagement |
| Page Views | `ennu_system_page_views` | number | Site analytics |
| Bounce Rate | `ennu_system_bounce_rate` | percentage | UX metrics |

### **Technical Tracking**

| Field Name | Official ID | Type | Purpose |
|------------|-------------|------|---------|
| Plugin Version on Registration | `ennu_system_plugin_version` | text | Version tracking |
| Browser Fingerprint | `ennu_system_browser_fingerprint` | hash | Security/Analytics |
| Device Type | `ennu_system_device_type` | text | UX optimization |
| Screen Resolution | `ennu_system_screen_resolution` | text | Design optimization |
| Operating System | `ennu_system_operating_system` | text | Compatibility tracking |
| Referrer Source | `ennu_system_referrer` | URL | Marketing attribution |
| UTM Campaign | `ennu_system_utm_campaign` | text | Marketing tracking |
| UTM Source | `ennu_system_utm_source` | text | Traffic source |
| UTM Medium | `ennu_system_utm_medium` | text | Marketing channel |
| UTM Content | `ennu_system_utm_content` | text | Ad content tracking |
| UTM Term | `ennu_system_utm_term` | text | Keyword tracking |

### **Business Intelligence**

| Field Name | Official ID | Type | Purpose |
|------------|-------------|------|---------|
| Lead Score | `ennu_system_lead_score` | number | Sales qualification |
| Engagement Level | `ennu_system_engagement` | text | Customer segmentation |
| Customer Lifetime Value | `ennu_system_clv` | currency | Business metrics |
| Recommended Products | `ennu_system_product_recommendations` | array | Personalization |
| Purchase Probability | `ennu_system_purchase_probability` | percentage | Sales forecasting |
| A/B Test Group | `ennu_system_ab_test_group` | text | Experimentation |
| Customer Segment | `ennu_system_customer_segment` | text | Marketing segmentation |
| Risk Assessment | `ennu_system_risk_assessment` | text | Business intelligence |
| Churn Probability | `ennu_system_churn_probability` | percentage | Retention analytics |

### **Integration Data**

| Field Name | Official ID | Type | Purpose |
|------------|-------------|------|---------|
| HubSpot Contact ID | `ennu_system_hubspot_id` | text | CRM integration |
| HubSpot Sync Status | `ennu_system_hubspot_sync` | text | Integration monitoring |
| WooCommerce Customer ID | `ennu_system_woocommerce_id` | number | E-commerce integration |
| Email Marketing Status | `ennu_system_email_status` | text | Communication preferences |
| SMS Marketing Status | `ennu_system_sms_status` | text | Communication preferences |
| CRM Sync Status | `ennu_system_crm_sync` | text | Data synchronization |
| Last CRM Sync | `ennu_system_last_crm_sync` | timestamp | Integration monitoring |
| API Key Used | `ennu_system_api_key` | text | Integration tracking |
| Webhook Status | `ennu_system_webhook_status` | text | Real-time integration |

### **Compliance & Security**

| Field Name | Official ID | Type | Purpose |
|------------|-------------|------|---------|
| GDPR Consent | `ennu_system_gdpr_consent` | boolean | Legal compliance |
| GDPR Consent Date | `ennu_system_gdpr_consent_date` | timestamp | Legal compliance |
| Data Retention Period | `ennu_system_data_retention` | number | Legal compliance |
| Privacy Policy Version | `ennu_system_privacy_version` | text | Legal compliance |
| Terms Accepted | `ennu_system_terms_accepted` | boolean | Legal compliance |
| Terms Version | `ennu_system_terms_version` | text | Legal compliance |
| Data Export Requested | `ennu_system_data_export` | timestamp | GDPR compliance |
| Data Deletion Requested | `ennu_system_data_deletion` | timestamp | GDPR compliance |
| Security Flags | `ennu_system_security_flags` | array | Security monitoring |

---

## 🛠️ **DEVELOPER QUICK REFERENCE**

### **Field Naming Convention**
```
ennu_[scope]_[assessment_type]_[field_name]

Examples:
- ennu_global_first_name (global user data)
- ennu_hair_assessment_question_1 (assessment response)
- ennu_system_lead_score (system analytics)
```

### **Field Retrieval**
```php
// Get global field
$first_name = get_user_meta($user_id, 'ennu_global_first_name', true);

// Get assessment field
$hair_concern = get_user_meta($user_id, 'ennu_hair_assessment_question_1', true);

// Get system field
$lead_score = get_user_meta($user_id, 'ennu_system_lead_score', true);
```

### **Field Storage**
```php
// Save global field
update_user_meta($user_id, 'ennu_global_first_name', $value);

// Save assessment field
update_user_meta($user_id, 'ennu_hair_assessment_question_1', $value);

// Save system field
update_user_meta($user_id, 'ennu_system_lead_score', $value);
```

### **Bulk Field Operations**
```php
// Get all ENNU fields for a user
$all_ennu_fields = get_user_meta($user_id);
$ennu_fields = array_filter($all_ennu_fields, function($key) {
    return strpos($key, 'ennu_') === 0;
}, ARRAY_FILTER_USE_KEY);

// Get all fields for specific assessment
$hair_fields = array_filter($all_ennu_fields, function($key) {
    return strpos($key, 'ennu_hair_assessment_') === 0;
}, ARRAY_FILTER_USE_KEY);
```

---

## 📋 **SUMMARY**

This documentation provides complete visibility into:

- **User Experience**: Clear distinction between public and authenticated user flows
- **Admin Interface**: Comprehensive view of all user data and system fields
- **Field Reference**: Every field with official ID and purpose
- **Hidden Data**: System fields that users never see but admins need
- **Developer Tools**: Quick reference for field manipulation

**Total Fields Documented**: 150+ fields across all categories
**Field Categories**: Global (12), Assessment-specific (60+), System/Hidden (80+)
**User Visibility Levels**: Public, User, Admin, System-only

This ensures complete transparency and easy reference for system development and administration.

