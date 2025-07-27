# HubSpot Bulk Field Creator

**Version:** 64.2.1  
**Last Updated:** January 2025

## Overview

The HubSpot Bulk Field Creator is a comprehensive solution for efficiently creating custom object fields in HubSpot CRM. This tool provides both a web-based admin interface and command-line capabilities for bulk field creation, validation, and management.

## Features

### 🎯 **Core Functionality**
- **Bulk Field Creation**: Create multiple custom object fields simultaneously
- **Field Validation**: Comprehensive schema validation before creation
- **Template System**: Pre-built templates for common field types
- **Real-time Progress**: Visual progress tracking during field creation
- **Error Handling**: Detailed error reporting and recovery options

### 🛠️ **Supported Field Types**
- **String Fields**: Text, textarea
- **Numeric Fields**: Number inputs
- **Date Fields**: Date pickers
- **Enumeration Fields**: Select dropdowns, radio buttons, checkboxes
- **Boolean Fields**: True/false checkboxes

### 📋 **Pre-built Templates**
- **Biomarker Fields**: Complete biomarker tracking system
- **Symptom Fields**: Symptom severity and frequency tracking
- **Assessment Fields**: Health assessment completion and scoring
- **Health Goals Fields**: Goal setting and progress tracking

## Installation & Setup

### 1. **Prerequisites**
- WordPress 5.0+
- PHP 7.4+
- HubSpot CRM account with API access
- WP Fusion plugin (optional, for enhanced integration)

### 2. **HubSpot API Configuration**
```php
// Configure HubSpot access token
update_option('ennu_hubspot_access_token', 'your_access_token_here');
```

### 3. **Access the Interface**
Navigate to: **WordPress Admin → ENNU Life Assessments → HubSpot Fields**

## Usage Guide

### **Web Interface**

#### Step 1: Select Custom Object
1. Choose the target custom object from the dropdown
2. Click "Refresh Objects" if needed
3. Ensure the object exists in your HubSpot account

#### Step 2: Configure Fields
**Option A: Use Templates**
- Click on a template button (Biomarkers, Symptoms, etc.)
- Fields will be automatically populated
- Customize field names and labels as needed

**Option B: Manual Configuration**
- Click "+ Add Field" to add individual fields
- Fill in field details:
  - **Field Name**: Internal identifier (lowercase, underscores)
  - **Field Label**: Display name
  - **Type**: Data type (string, number, date, etc.)
  - **Field Type**: Input type (text, select, etc.)

#### Step 3: Validate & Create
1. Click "Validate Schema" to check field configuration
2. Review validation results
3. Click "Create Fields" to proceed
4. Monitor progress and review results

### **Command Line Interface**

#### **List Custom Objects**
```bash
# List all custom objects
wp ennu hubspot list-objects

# Export as JSON
wp ennu hubspot list-objects --format=json
```

#### **Create Custom Object**
```bash
# Create a new custom object
wp ennu hubspot create-object biomarkers "Biomarker" "Biomarkers" \
  --primary-property=biomarker_name \
  --associated-objects=CONTACT
```

#### **Create Fields from Template**
```bash
# Create biomarker fields using template
wp ennu hubspot create-fields p_biomarkers --template=biomarkers

# Dry run to validate without creating
wp ennu hubspot create-fields p_biomarkers --template=biomarkers --dry-run
```

#### **Create Fields from JSON File**
```bash
# Create fields from custom JSON file
wp ennu hubspot create-fields p_biomarkers /path/to/fields.json

# Skip validation (use with caution)
wp ennu hubspot create-fields p_biomarkers /path/to/fields.json --force
```

#### **List Object Fields**
```bash
# List all fields for an object
wp ennu hubspot list-fields p_biomarkers

# Export as CSV
wp ennu hubspot list-fields p_biomarkers --format=csv
```

## Field Templates

### **Biomarker Template**
```json
[
  {
    "name": "biomarker_name",
    "label": "Biomarker Name",
    "type": "string",
    "fieldType": "text"
  },
  {
    "name": "biomarker_value",
    "label": "Biomarker Value",
    "type": "number",
    "fieldType": "number"
  },
  {
    "name": "biomarker_status",
    "label": "Status",
    "type": "enumeration",
    "fieldType": "select",
    "options": [
      {"label": "Normal", "value": "normal"},
      {"label": "Low", "value": "low"},
      {"label": "High", "value": "high"}
    ]
  }
]
```

### **Symptom Template**
```json
[
  {
    "name": "symptom_name",
    "label": "Symptom Name",
    "type": "string",
    "fieldType": "text"
  },
  {
    "name": "symptom_severity",
    "label": "Severity",
    "type": "enumeration",
    "fieldType": "select",
    "options": [
      {"label": "Mild", "value": "mild"},
      {"label": "Moderate", "value": "moderate"},
      {"label": "Severe", "value": "severe"}
    ]
  }
]
```

## API Reference

### **Field Schema Validation**
```php
// Validate field schema
$validation = $this->validate_field_schema($fields);
if (is_wp_error($validation)) {
    // Handle validation error
}
```

### **Bulk Field Creation**
```php
// Create fields in bulk
$results = $this->bulk_create_fields($object_type, $fields);
if (is_wp_error($results)) {
    // Handle creation error
}
```

### **Single Field Creation**
```php
// Create a single field
$field_data = array(
    'name' => 'field_name',
    'label' => 'Field Label',
    'type' => 'string',
    'fieldType' => 'text'
);
$result = $this->create_single_field($object_type, $field_data);
```

## Error Handling

### **Common Error Types**
- **API Authentication**: Invalid or expired access token
- **Field Validation**: Invalid field schema or naming
- **Object Not Found**: Custom object doesn't exist
- **Permission Denied**: Insufficient API permissions
- **Rate Limiting**: Too many API requests

### **Error Recovery**
1. **Check API Token**: Verify HubSpot access token is valid
2. **Validate Schema**: Use dry-run mode to test field configuration
3. **Check Permissions**: Ensure API has custom object write permissions
4. **Rate Limiting**: Implement delays between bulk operations

## Best Practices

### **Field Naming**
- Use lowercase with underscores: `biomarker_value`
- Avoid special characters and spaces
- Keep names descriptive but concise
- Use consistent naming conventions

### **Field Organization**
- Group related fields together
- Use consistent field types for similar data
- Include proper descriptions for all fields
- Set appropriate validation rules

### **Bulk Operations**
- Test with small batches first
- Use dry-run mode for validation
- Monitor API rate limits
- Keep backup of field configurations

### **Template Usage**
- Start with pre-built templates
- Customize templates for your specific needs
- Document custom field configurations
- Version control your field schemas

## Troubleshooting

### **Field Creation Fails**
1. Check HubSpot API permissions
2. Verify custom object exists
3. Validate field schema
4. Check for duplicate field names

### **Validation Errors**
1. Review field naming conventions
2. Check field type compatibility
3. Verify required field properties
4. Test with dry-run mode

### **API Connection Issues**
1. Verify access token
2. Check network connectivity
3. Review HubSpot API status
4. Check rate limiting

## Integration Examples

### **With WP Fusion**
```php
// Sync fields with WP Fusion
if (function_exists('wp_fusion')) {
    $contact_fields = wp_fusion()->settings->get('contact_fields', array());
    // Add custom fields to WP Fusion mapping
}
```

### **With Custom Workflows**
```php
// Trigger workflows on field creation
add_action('ennu_hubspot_field_created', function($field_name, $object_type) {
    // Custom workflow logic
}, 10, 2);
```

## Security Considerations

### **API Security**
- Store access tokens securely
- Use environment variables for production
- Implement proper access controls
- Monitor API usage

### **Data Validation**
- Sanitize all input data
- Validate field schemas
- Implement proper error handling
- Log all operations

## Performance Optimization

### **Bulk Operations**
- Use batch processing for large datasets
- Implement progress tracking
- Add appropriate delays between requests
- Monitor memory usage

### **Caching**
- Cache object schemas
- Store field configurations
- Implement result caching
- Use transient storage for temporary data

## Support & Maintenance

### **Regular Maintenance**
- Monitor API usage and limits
- Update field schemas as needed
- Review and clean up unused fields
- Backup field configurations

### **Support Resources**
- HubSpot API Documentation
- WordPress Developer Resources
- Plugin Support Documentation
- Community Forums

## Changelog

### **Version 64.2.1**
- Initial release of bulk field creator
- Web interface implementation
- Command-line interface
- Pre-built field templates
- Comprehensive validation system

---

**For technical support or feature requests, please contact the ENNU Life development team.** 