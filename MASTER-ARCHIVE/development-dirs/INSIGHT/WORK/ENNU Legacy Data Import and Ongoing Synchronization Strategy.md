# ENNU Legacy Data Import and Ongoing Synchronization Strategy

**Document Version:** 1.0  
**Date:** June 20, 2025  
**Purpose:** Complete mapping of legacy system data to HubSpot destinations with ongoing sync specifications  
**Classification:** Technical Implementation Guide  

---

## Executive Summary

This document provides the definitive mapping of all ENNU legacy system data to HubSpot destinations, including detailed field-by-field import specifications and ongoing synchronization strategies. The architecture ensures 100% data preservation while establishing efficient, real-time synchronization between all systems.

**Key Legacy Systems:**
- **OM Aggregation Database** - 165 fields, 16M+ patient records
- **WooCommerce E-commerce** - Products, orders, customers, subscriptions
- **Open Medical (OM)** - Clinical data, appointments, treatments
- **Open Dental (OD)** - Dental services, scheduling, billing
- **Legacy CRM** - Marketing data, lead sources, campaigns

**Target HubSpot Architecture:**
- **7 Standard Objects** enhanced with custom fields
- **3 Custom Objects** for specialized medical data
- **Bidirectional synchronization** with real-time and batch processing
- **Complete audit trails** and conflict resolution

---

## 1. OM Aggregation Database Import Mapping

### 1.1 Patient Demographics and Contact Information (15 Fields)

**Source:** OM Aggregation Database  
**Destination:** HubSpot Contacts Object  
**Import Method:** Initial bulk import + ongoing sync every 5 minutes  

| OM Aggregation Field | HubSpot Destination | Field Type | Sync Priority | Notes |
|---------------------|-------------------|------------|---------------|-------|
| `PatNum` | `open_medical_patient_id` | Number | Critical | Primary OM identifier |
| `AggregationKeyID` | `aggregation_key_id` | Single-line text | Critical | Unique aggregation key |
| `FName` | `firstname` | Single-line text | Critical | Standard HubSpot field |
| `LName` | `lastname` | Single-line text | Critical | Standard HubSpot field |
| `Email` | `email` | Email | Critical | Standard HubSpot field |
| `HmPhone` | `phone` | Phone number | Critical | Standard HubSpot field |
| `WirelessPhone` | `mobilephone` | Phone number | Critical | Standard HubSpot field |
| `WkPhone` | `work_phone` | Phone number | Medium | Custom field |
| `Address` | `address` | Multi-line text | High | Standard HubSpot field |
| `Address2` | `address_2` | Single-line text | Medium | Custom field |
| `City` | `city` | Single-line text | High | Standard HubSpot field |
| `State` | `state` | Dropdown | Critical | Standard HubSpot field |
| `Zip` | `zip` | Single-line text | High | Standard HubSpot field |
| `Birthdate` | `date_of_birth` | Date | High | Custom field |
| `Gender` | `gender` | Dropdown | High | Standard HubSpot field |

**Ongoing Sync:** Bidirectional every 5 minutes  
**Conflict Resolution:** OM Aggregation is master for clinical data, HubSpot is master for contact preferences

### 1.2 Patient Status and Lifecycle (8 Fields)

**Source:** OM Aggregation Database  
**Destination:** HubSpot Contacts Object  
**Import Method:** Initial bulk import + real-time sync  

| OM Aggregation Field | HubSpot Destination | Field Type | Sync Priority | Sync Frequency |
|---------------------|-------------------|------------|---------------|----------------|
| `StatusOM` | `patient_status_om` | Dropdown | Critical | Real-time |
| `SubStatusOM` | `sub_status_om` | Dropdown | High | Real-time |
| `PatStatus` | `patient_status_od` | Dropdown | Medium | 15 minutes |
| `CRMtoOMSync` | `crm_to_om_sync` | Checkbox | High | Real-time |
| `PatCreationDate` | `patient_creation_date` | Date | High | One-time import |
| `DateFirstVisit` | `first_visit_date` | Date | High | 15 minutes |
| `LCAppointment` | `last_appointment_date` | Date & time | Critical | Real-time |
| `CRMUserDeleted` | `crm_user_deleted` | Checkbox | Low | Daily |

**Status Mapping:**
- `StatusOM` values: Lead, Prospect, Opportunity, Current, Former, Promotional
- Maps to HubSpot Lifecycle Stages: Lead, Marketing Qualified Lead, Sales Qualified Lead, Customer, Evangelist

### 1.3 Financial Information (8 Fields)

**Source:** OM Aggregation Database  
**Destination:** HubSpot Contacts Object  
**Import Method:** Initial bulk import + sync every 15 minutes  

| OM Aggregation Field | HubSpot Destination | Field Type | Sync Priority | Business Logic |
|---------------------|-------------------|------------|---------------|----------------|
| `BalTotal` | `account_balance` | Number | Critical | Triggers billing workflows |
| `EstBalance` | `estimated_balance` | Number | High | Used for financial planning |
| `Bal_0_30` | `balance_0_30` | Number | High | Collections automation |
| `Bal_31_60` | `balance_31_60` | Number | High | Collections automation |
| `Bal_61_90` | `balance_61_90` | Number | High | Collections automation |
| `BalOver90` | `balance_over_90` | Number | High | Collections automation |
| `PayPlanDue` | `payment_plan_due` | Number | High | Payment plan automation |
| `FeeSched` | `fee_schedule` | Dropdown | Medium | Pricing tier management |

**Automation Triggers:**
- Balance > $500: Create high-priority ticket for collections
- Payment plan due: Automated payment reminder emails
- Overdue > 90 days: Escalate to collections workflow

### 1.4 Current Health Metrics (24 Fields)

**Source:** OM Aggregation Database  
**Destination:** HubSpot Contacts Object (current values) + Measurement History Custom Object (historical data)  
**Import Method:** Current values to Contacts, historical data to Measurement History  

#### Current Values → Contacts Object

| OM Aggregation Field | HubSpot Destination | Field Type | Sync Priority | Update Frequency |
|---------------------|-------------------|------------|---------------|------------------|
| `weight_mr` | `current_weight` | Number | High | Real-time |
| `bmi_mr` | `current_bmi` | Number | High | Real-time |
| `waist_mr` | `current_waist` | Number | High | Real-time |
| `neck_mr` | `current_neck` | Number | Medium | Real-time |
| `bodyfat_mr` | `current_body_fat` | Number | Medium | Real-time |
| `weight_mr_date` | `last_measurement_date` | Date | High | Real-time |
| `weight_best` | `best_weight_achieved` | Number | Medium | When improved |
| `bmi_best` | `best_bmi_achieved` | Number | Medium | When improved |
| `waist_best` | `best_waist_achieved` | Number | Medium | When improved |

#### Historical Data → Measurement History Custom Object

**All measurement fields with date stamps create individual Measurement History records:**

| Measurement Type | OM Fields | HubSpot Custom Object Fields |
|-----------------|-----------|------------------------------|
| Weight Tracking | `weight_mr`, `weight_mr_date`, `weight_best`, `weight_goal` | `measurement_type`: "Weight", `measurement_value`, `measurement_date`, `reference_range_low`, `reference_range_high` |
| BMI Tracking | `bmi_mr`, `bmi_best`, `bmi_goal` | `measurement_type`: "BMI", `measurement_value`, `measurement_date` |
| Body Composition | `bodyfat_mr`, `waist_mr`, `neck_mr` | `measurement_type`: "Body Fat %", "Waist", "Neck", `measurement_value`, `measurement_date` |

### 1.5 Health Goals and Programs (12 Fields)

**Source:** OM Aggregation Database  
**Destination:** HubSpot Contacts Object + Deals Object (for program enrollments)  
**Import Method:** Goals to Contacts, active programs to Deals  

| OM Aggregation Field | HubSpot Destination | Field Type | Sync Priority | Business Logic |
|---------------------|-------------------|------------|---------------|----------------|
| `weight_goal` | `weight_goal` | Number | High | Used for progress tracking |
| `bmi_goal` | `bmi_goal` | Number | High | Used for progress tracking |
| `waist_goal` | `waist_goal` | Number | High | Used for progress tracking |
| `goal_date` | `goal_target_date` | Date | High | Milestone tracking |
| `program_status` | `current_program_status` | Dropdown | Critical | Active, Completed, Paused, Cancelled |
| `program_start_date` | `program_start_date` | Date | High | Program timeline tracking |
| `program_end_date` | `program_end_date` | Date | High | Program completion tracking |

**Program Enrollment Logic:**
- Active programs create Deal records in "ENNU Program Enrollment" pipeline
- Program status maps to Deal stages
- Program completion triggers outcome measurement workflows

### 1.6 Marketing and Attribution (15 Fields)

**Source:** OM Aggregation Database + Legacy CRM  
**Destination:** HubSpot Contacts Object  
**Import Method:** Initial bulk import + ongoing sync every 30 minutes  

| OM Aggregation Field | HubSpot Destination | Field Type | Sync Priority | Marketing Use |
|---------------------|-------------------|------------|---------------|---------------|
| `lead_source` | `original_source` | Dropdown | High | Attribution tracking |
| `lead_source_detail` | `original_source_detail` | Single-line text | High | Campaign tracking |
| `referral_source` | `referral_source` | Single-line text | High | Referral program |
| `marketing_campaign` | `first_conversion_event` | Single-line text | High | Campaign ROI |
| `utm_source` | `hs_analytics_source` | Single-line text | High | Digital attribution |
| `utm_medium` | `hs_analytics_source_data_1` | Single-line text | High | Channel tracking |
| `utm_campaign` | `hs_analytics_source_data_2` | Single-line text | High | Campaign tracking |

---

## 2. WooCommerce E-commerce Data Import Mapping

### 2.1 Customer Data Synchronization

**Source:** WooCommerce Customer Database  
**Destination:** HubSpot Contacts Object  
**Sync Method:** WP Fusion real-time bidirectional sync  

| WooCommerce Field | HubSpot Destination | Sync Direction | Sync Priority | Conflict Resolution |
|------------------|-------------------|----------------|---------------|-------------------|
| `customer_email` | `email` | Bidirectional | Critical | HubSpot master |
| `first_name` | `firstname` | Bidirectional | Critical | Most recent update |
| `last_name` | `lastname` | Bidirectional | Critical | Most recent update |
| `billing_phone` | `phone` | Bidirectional | High | Most recent update |
| `billing_address_1` | `address` | WooCommerce → HubSpot | High | WooCommerce master |
| `billing_city` | `city` | WooCommerce → HubSpot | High | WooCommerce master |
| `billing_state` | `state` | WooCommerce → HubSpot | High | WooCommerce master |
| `billing_postcode` | `zip` | WooCommerce → HubSpot | High | WooCommerce master |
| `customer_registration_date` | `woocommerce_registration_date` | WooCommerce → HubSpot | Medium | One-time import |
| `customer_lifetime_value` | `woocommerce_lifetime_value` | WooCommerce → HubSpot | High | Real-time calculation |

### 2.2 Product Catalog Synchronization

**Source:** WooCommerce Products  
**Destination:** HubSpot Products Object  
**Sync Method:** WP Fusion automatic product sync  

| WooCommerce Field | HubSpot Destination | Sync Direction | Sync Frequency | Notes |
|------------------|-------------------|----------------|----------------|-------|
| `product_name` | `name` | WooCommerce → HubSpot | Real-time | Auto-created by WP Fusion |
| `product_description` | `description` | WooCommerce → HubSpot | Real-time | Full product details |
| `product_price` | `price` | WooCommerce → HubSpot | Real-time | Dynamic pricing updates |
| `product_sku` | `hs_sku` | WooCommerce → HubSpot | Real-time | Inventory tracking |
| `product_category` | `hs_product_type` | WooCommerce → HubSpot | Real-time | Product categorization |
| `stock_quantity` | `hs_recurring_billing_period` | WooCommerce → HubSpot | Real-time | Inventory management |

### 2.3 Order and Deal Synchronization

**Source:** WooCommerce Orders  
**Destination:** HubSpot Deals Object  
**Sync Method:** WP Fusion real-time order sync  

| WooCommerce Field | HubSpot Destination | Sync Direction | Deal Pipeline | Business Logic |
|------------------|-------------------|----------------|---------------|----------------|
| `order_id` | `woocommerce_order_id` | WooCommerce → HubSpot | ENNU Program Enrollment | Creates new deal |
| `order_total` | `amount` | WooCommerce → HubSpot | ENNU Program Enrollment | Deal value |
| `order_status` | `dealstage` | WooCommerce → HubSpot | ENNU Program Enrollment | Status mapping |
| `order_date` | `createdate` | WooCommerce → HubSpot | ENNU Program Enrollment | Deal creation date |
| `payment_method` | `payment_method` | WooCommerce → HubSpot | ENNU Program Enrollment | Payment tracking |

**Order Status to Deal Stage Mapping:**
- `pending` → "Enrollment Pending"
- `processing` → "Enrollment Processing"
- `completed` → "Enrollment Active"
- `cancelled` → "Enrollment Cancelled"
- `refunded` → "Enrollment Refunded"

### 2.4 Subscription Management

**Source:** WooCommerce Subscriptions  
**Destination:** HubSpot Deals Object (recurring deals)  
**Sync Method:** WP Fusion subscription sync with recurring deal creation  

| WooCommerce Field | HubSpot Destination | Sync Direction | Sync Frequency | Automation |
|------------------|-------------------|----------------|----------------|------------|
| `subscription_status` | `subscription_status` | WooCommerce → HubSpot | Real-time | Deal stage updates |
| `next_payment_date` | `closedate` | WooCommerce → HubSpot | Real-time | Deal close date |
| `subscription_total` | `amount` | WooCommerce → HubSpot | Real-time | Recurring revenue |
| `billing_interval` | `billing_frequency` | WooCommerce → HubSpot | Real-time | Payment scheduling |

---

## 3. Clinical System Data Import Mapping

### 3.1 Appointment Data Synchronization

**Source:** Open Medical + Open Dental Appointment Systems  
**Destination:** HubSpot Appointments Object (Meetings)  
**Import Method:** Initial bulk import + real-time sync every 5 minutes  

| Source Field | HubSpot Destination | Field Type | Sync Priority | Business Logic |
|-------------|-------------------|------------|---------------|----------------|
| `appointment_id` | `open_medical_appointment_id` | Single-line text | Critical | Primary identifier |
| `patient_id` | Contact Association | Association | Critical | Links to patient record |
| `provider_id` | `hs_meeting_owner` | User | Critical | Provider assignment |
| `appointment_date` | `hs_meeting_start_time` | Date & time | Critical | Scheduling |
| `appointment_duration` | `hs_meeting_end_time` | Date & time | Critical | Calculated end time |
| `appointment_type` | `hs_meeting_type` | Dropdown | High | In-person, Telehealth, Consultation |
| `appointment_status` | `hs_meeting_outcome` | Dropdown | High | Scheduled, Completed, Cancelled, No-show |
| `clinic_location` | `meeting_location` | Single-line text | High | Physical location |
| `appointment_notes` | `hs_meeting_body` | Multi-line text | Medium | Clinical notes |

### 3.2 Treatment and Procedure Data

**Source:** Open Medical + Open Dental Treatment Records  
**Destination:** HubSpot Services Object + Tickets Object (for follow-up)  
**Import Method:** Initial bulk import + sync every 15 minutes  

| Source Field | HubSpot Destination | Object | Sync Priority | Notes |
|-------------|-------------------|--------|---------------|-------|
| `treatment_id` | `treatment_record_id` | Services | Critical | Treatment identifier |
| `treatment_code` | `service_code` | Services | High | CPT/procedure codes |
| `treatment_description` | `name` | Services | High | Service description |
| `treatment_date` | `service_date` | Services | High | When performed |
| `provider_id` | `service_provider` | Services | High | Performing provider |
| `treatment_cost` | `cost` | Services | High | Service pricing |
| `follow_up_required` | Creates Ticket | Tickets | High | Automated follow-up |

### 3.3 Laboratory Results Integration

**Source:** Laboratory Information Systems  
**Destination:** Measurement History Custom Object  
**Import Method:** Automated import every 5 minutes for new results  

| Lab System Field | HubSpot Custom Object Field | Field Type | Sync Priority | Clinical Logic |
|-----------------|----------------------------|------------|---------------|----------------|
| `test_id` | `lab_test_id` | Single-line text | Critical | Unique test identifier |
| `patient_id` | Contact Association | Association | Critical | Links to patient |
| `test_name` | `measurement_type` | Dropdown | Critical | Test type classification |
| `test_result` | `measurement_value` | Number | Critical | Numeric result |
| `test_units` | `measurement_unit` | Dropdown | Critical | Units of measure |
| `reference_range_low` | `reference_range_low` | Number | High | Normal range minimum |
| `reference_range_high` | `reference_range_high` | Number | High | Normal range maximum |
| `test_date` | `measurement_date` | Date | Critical | When test was performed |
| `ordering_provider` | `ordering_provider` | Single-line text | High | Provider who ordered |
| `critical_flag` | `critical_value_flag` | Checkbox | Critical | Triggers alerts |

**Critical Value Automation:**
- Critical values automatically create high-priority Tickets
- Provider notifications sent immediately
- Patient safety protocols triggered

---

## 4. Telehealth Platform Integration

### 4.1 Virtual Session Data

**Source:** Telehealth Platforms (Zoom, Teams, Doxy.me)  
**Destination:** Telehealth Sessions Custom Object  
**Import Method:** Real-time webhook integration  

| Platform Field | HubSpot Custom Object Field | Sync Method | Sync Priority | Compliance |
|---------------|----------------------------|-------------|---------------|------------|
| `session_id` | `platform_session_id` | Webhook | Critical | Session tracking |
| `meeting_url` | `session_url` | Webhook | Critical | Patient access |
| `start_time` | `session_start_time` | Webhook | Critical | Session timing |
| `end_time` | `session_end_time` | Webhook | Critical | Duration tracking |
| `participant_count` | `participant_count` | Webhook | High | Attendance |
| `recording_url` | `recording_url` | Webhook | High | Documentation |
| `session_quality` | `session_quality_score` | Webhook | High | Quality monitoring |
| `technical_issues` | `technical_issues` | Webhook | Medium | Issue tracking |

### 4.2 Compliance and Quality Tracking

**Source:** Telehealth Platform APIs + Manual Entry  
**Destination:** Telehealth Sessions Custom Object  
**Import Method:** Real-time API + manual updates  

| Compliance Field | HubSpot Field | Data Source | Sync Priority | Regulatory |
|-----------------|---------------|-------------|---------------|------------|
| `state_licensing_verified` | `state_licensing_compliant` | Manual/API | Critical | HIPAA compliance |
| `patient_consent_obtained` | `patient_consent_documented` | Manual | Critical | Legal requirement |
| `identity_verified` | `patient_identity_verified` | Manual | Critical | Security |
| `emergency_protocol_reviewed` | `emergency_protocol_compliant` | Manual | Critical | Safety |
| `session_privacy_confirmed` | `privacy_environment_verified` | Manual | High | HIPAA compliance |

---

## 5. Ongoing Synchronization Strategy

### 5.1 Sync Frequency and Priority Matrix

| Data Type | Source System | Destination | Sync Frequency | Method | Priority |
|-----------|---------------|-------------|----------------|--------|----------|
| **Patient Safety Data** | OM Aggregation | Contacts | Real-time (<5 sec) | Webhook | Critical |
| **Appointment Changes** | OM/OD | Appointments | Real-time (<5 sec) | Webhook | Critical |
| **Payment Processing** | WooCommerce | Deals | Real-time (<5 sec) | WP Fusion | Critical |
| **Lab Results** | Lab Systems | Measurement History | 5 minutes | API Poll | Critical |
| **Patient Demographics** | OM Aggregation | Contacts | 5 minutes | API Poll | High |
| **Financial Data** | OM Aggregation | Contacts | 15 minutes | API Poll | High |
| **Clinical Notes** | OM/OD | Tickets/Services | 15 minutes | API Poll | Medium |
| **Marketing Data** | Various | Contacts | 30 minutes | API Poll | Medium |
| **Historical Data** | OM Aggregation | Various | Daily | Batch | Low |

### 5.2 Conflict Resolution Rules

| Data Category | Master System | Conflict Resolution | Escalation |
|---------------|---------------|-------------------|------------|
| **Clinical Data** | OM Aggregation | OM wins, log conflict | Provider review |
| **Contact Preferences** | HubSpot | HubSpot wins, sync back | Marketing review |
| **Financial Data** | OM Aggregation | OM wins, alert billing | Billing team |
| **Appointment Data** | Most recent update | Timestamp comparison | Provider confirmation |
| **E-commerce Data** | WooCommerce | WooCommerce wins | Customer service |

### 5.3 Error Handling and Recovery

| Error Type | Detection Method | Recovery Action | Escalation Time |
|------------|------------------|-----------------|-----------------|
| **Sync Failure** | API monitoring | Automatic retry (3x) | 15 minutes |
| **Data Corruption** | Validation checks | Rollback + manual review | Immediate |
| **Missing Records** | Audit reports | Re-sync from source | 1 hour |
| **Duplicate Records** | Duplicate detection | Merge + audit trail | 4 hours |
| **Critical Value Alerts** | Real-time monitoring | Immediate notification | Immediate |

### 5.4 Performance Monitoring

| Metric | Target | Monitoring Method | Alert Threshold |
|--------|--------|------------------|-----------------|
| **Sync Latency** | <30 seconds | Real-time dashboard | >60 seconds |
| **Success Rate** | >99.5% | Automated reporting | <99% |
| **Data Quality** | >99.9% | Validation scripts | <99.5% |
| **System Uptime** | >99.9% | Health checks | <99% |
| **Error Rate** | <0.1% | Error logging | >0.5% |

---

## 6. Implementation Timeline

### Phase 1: Initial Data Import (Weeks 1-3)
- **Week 1:** OM Aggregation patient demographics and contact info
- **Week 2:** Financial data, health metrics, and goals
- **Week 3:** Historical measurement data and marketing attribution

### Phase 2: E-commerce Integration (Weeks 4-5)
- **Week 4:** WooCommerce customer and product sync setup
- **Week 5:** Order history import and subscription management

### Phase 3: Clinical System Integration (Weeks 6-7)
- **Week 6:** Appointment and treatment data import
- **Week 7:** Laboratory system integration and telehealth setup

### Phase 4: Ongoing Sync Activation (Weeks 8-9)
- **Week 8:** Real-time sync activation and testing
- **Week 9:** Performance optimization and monitoring setup

---

## 7. Success Metrics and Validation

### Data Migration Success Criteria
- **99.9% data migration accuracy** - All 165 OM fields successfully imported
- **Zero data loss** - Complete audit trail of all imported records
- **<24 hour migration time** - Full import completed within one business day
- **100% field mapping validation** - All fields correctly mapped and validated

### Ongoing Sync Performance Targets
- **<30 second sync latency** for critical data
- **99.5% sync success rate** across all integrations
- **<0.1% error rate** for all synchronization operations
- **100% critical value alert delivery** within 5 seconds

### Business Impact Measurements
- **40% improvement** in data accessibility and reporting
- **60% reduction** in manual data entry and reconciliation
- **25% improvement** in patient care coordination
- **50% enhancement** in marketing attribution accuracy

---

**Document Classification:** Technical Implementation Guide  
**Author:** Manus AI  
**Version:** 1.0  
**Date:** June 20, 2025  
**Distribution:** ENNU Technical Team, Implementation Partners

