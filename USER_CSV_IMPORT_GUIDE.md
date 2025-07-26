# ENNU Life User CSV Import Shortcode Guide

## Overview

The `[ennu_user_csv_import]` shortcode allows users to import their own biomarker data directly from the frontend of your WordPress site. This provides a self-service solution for users to upload their lab results without requiring admin intervention.

## Quick Start

### Basic Usage

Add this shortcode to any WordPress page or post:

```
[ennu_user_csv_import]
```

### Customized Usage

```
[ennu_user_csv_import show_instructions="false" show_sample="false"]
```

## Features

### ✅ Core Functionality
- **User Authentication**: Only logged-in users can import data
- **File Upload**: Drag-and-drop CSV file upload
- **Data Validation**: Comprehensive biomarker validation (40+ supported)
- **Error Handling**: Real-time feedback and error messages
- **Import History**: Tracks all import activity
- **Score Updates**: Automatically updates life scores after import

### ✅ User Experience
- **Modern Design**: Clean, responsive interface
- **Mobile-Friendly**: Works on all device types
- **Accessibility**: Keyboard navigation and screen reader support
- **Progress Indicators**: Visual feedback during import
- **Sample Data**: Downloadable sample CSV file

### ✅ Security
- **Nonce Verification**: Prevents CSRF attacks
- **File Validation**: Type and size restrictions
- **Permission Checks**: User authentication required
- **Data Sanitization**: All input is properly sanitized

## Shortcode Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `show_instructions` | `true` | Show/hide the "How to Import Your Data" section |
| `show_sample` | `true` | Show/hide the sample CSV format section |
| `max_file_size` | `5` | Maximum file size in MB |
| `allowed_types` | `csv` | Allowed file types (currently only CSV) |

### Examples

```php
// Basic usage
[ennu_user_csv_import]

// Hide instructions
[ennu_user_csv_import show_instructions="false"]

// Hide sample data
[ennu_user_csv_import show_sample="false"]

// Custom file size limit (10MB)
[ennu_user_csv_import max_file_size="10"]

// Minimal form
[ennu_user_csv_import show_instructions="false" show_sample="false"]
```

## CSV Format

### Required Format
```csv
biomarker_name,value,unit,date
glucose,95,mg/dL,2024-01-15
hba1c,5.2,%,2024-01-15
testosterone,650,ng/dL,2024-01-15
```

### Supported Biomarkers

The system supports 40+ common biomarkers including:

- **Metabolic**: Glucose, HbA1c, Insulin
- **Hormonal**: Testosterone, Cortisol, TSH, T3, T4
- **Lipid**: Total Cholesterol, HDL, LDL, Triglycerides
- **Kidney**: Creatinine, eGFR, BUN
- **Liver**: AST, ALT, GGT, Albumin
- **Blood**: WBC, RBC, Hemoglobin, Platelets
- **Vitamins**: Vitamin D, Vitamin B12, Folate
- **Minerals**: Calcium, Magnesium, Iron, Sodium, Potassium

## Technical Implementation

### Files Created

1. **`includes/class-user-csv-import-shortcode.php`**
   - Main shortcode class
   - Handles form rendering and AJAX processing
   - Manages user authentication and permissions

2. **`assets/css/user-csv-import.css`**
   - Frontend styling
   - Responsive design
   - Accessibility features

3. **`assets/js/user-csv-import.js`**
   - Frontend JavaScript functionality
   - AJAX form submission
   - Real-time validation and feedback

### AJAX Endpoints

- `wp_ajax_ennu_user_csv_import` - For logged-in users
- `wp_ajax_nopriv_ennu_user_csv_import` - For non-logged-in users

### User Meta Fields

- `ennu_biomarker_data` - Stored biomarker data
- `ennu_last_user_csv_import` - Last import timestamp
- `ennu_user_csv_import_history` - Import history log

## Installation & Setup

### 1. Plugin Integration

The shortcode is automatically loaded when the ENNU Life plugin is active. No additional setup required.

### 2. Testing

1. Create a new WordPress page
2. Add the shortcode: `[ennu_user_csv_import]`
3. Publish the page
4. Test with both logged-in and non-logged-in users

### 3. Demo Page

Visit: `http://localhost/wp-content/plugins/ennulifeassessments/shortcode-demo.php`

## User Workflow

### For Logged-In Users

1. **Upload File**: Select or drag-and-drop a CSV file
2. **Configure Options**: Choose to overwrite existing data and update scores
3. **Import**: Click "Import Biomarkers" button
4. **Review Results**: See imported data, warnings, and errors
5. **Verify**: Check their profile for updated biomarker data

### For Non-Logged-In Users

1. **See Login Message**: Redirected to login/register
2. **Authenticate**: Login or create account
3. **Return**: Automatically redirected back to import page
4. **Import**: Follow logged-in user workflow

## Error Handling

### Common Errors

- **File Size**: Files larger than 5MB (or custom limit)
- **File Type**: Non-CSV files
- **Invalid Data**: Non-numeric values or missing columns
- **Unknown Biomarkers**: Biomarkers not in supported list
- **Date Format**: Invalid date formats (uses current date as fallback)

### User Feedback

- **Success Messages**: Green confirmation with import details
- **Warning Messages**: Yellow warnings for non-critical issues
- **Error Messages**: Red errors for critical problems
- **Progress Indicators**: Loading spinners and progress bars

## Security Considerations

### Data Protection

- All file uploads are validated and sanitized
- User data is isolated to their own account
- Import history is maintained for audit purposes
- Nonce verification prevents unauthorized requests

### Best Practices

- Monitor import logs for unusual activity
- Regularly backup user biomarker data
- Test with various file formats and sizes
- Ensure proper server file upload limits

## Troubleshooting

### Common Issues

1. **Shortcode Not Displaying**
   - Check if plugin is active
   - Verify shortcode syntax
   - Check for JavaScript errors

2. **File Upload Fails**
   - Check server file size limits
   - Verify file is valid CSV
   - Check user permissions

3. **Import Errors**
   - Review CSV format
   - Check biomarker names
   - Verify date formats

### Debug Tools

- **Test Script**: `test-user-shortcode.php`
- **Demo Page**: `shortcode-demo.php`
- **Debug Log**: Check WordPress debug log
- **Browser Console**: Check for JavaScript errors

## Performance Considerations

### Optimization

- CSS and JS are only loaded when shortcode is present
- File size limits prevent large uploads
- Import history is limited to last 10 entries
- Efficient database queries for user data

### Monitoring

- Track import success rates
- Monitor file upload sizes
- Check for common error patterns
- Review user feedback

## Future Enhancements

### Planned Features

- **Batch Import**: Multiple files at once
- **Data Export**: Download user's biomarker data
- **Advanced Validation**: Custom biomarker ranges
- **Integration**: Connect with lab APIs
- **Analytics**: Import statistics and trends

### Customization Options

- **Custom Biomarkers**: Add site-specific biomarkers
- **Branding**: Customize colors and styling
- **Workflows**: Custom import processes
- **Notifications**: Email confirmations

## Support

### Documentation

- This guide provides comprehensive usage information
- Demo page shows live examples
- Test scripts verify functionality

### Testing

- Test with various user roles
- Verify mobile responsiveness
- Check accessibility compliance
- Validate security measures

### Maintenance

- Regular plugin updates
- Monitor for WordPress compatibility
- Review security best practices
- Backup user data regularly

---

**Version**: 64.4.4  
**Last Updated**: July 25, 2025  
**Plugin**: ENNU Life Assessments 