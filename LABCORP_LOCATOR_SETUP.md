# LabCorp Locator Setup Instructions

## Overview
The LabCorp Locator feature has been successfully integrated into your ENNU Life Assessments plugin. It provides a 2-column layout in the LabCorp upload tab with:

- **Left Column**: LabCorp location finder with interactive map
- **Right Column**: Existing LabCorp PDF upload functionality

## Google Maps API Setup

To enable the location finder, you need to configure a Google Maps API key:

### 1. Get a Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - Maps JavaScript API
   - Places API (for location search)
4. Create credentials (API key)
5. Restrict the API key to your domain for security

### 2. Configure the API Key in WordPress

1. Log into your WordPress admin
2. Go to **ENNU Life → LabCorp Settings**
3. Enter your Google Maps API key
4. Save settings

### 3. Test the Feature

1. Go to your dashboard page
2. Click on the "LabCorp Upload" tab
3. You should see:
   - Left column: LabCorp locator with search and "Use My Location" buttons
   - Right column: PDF upload functionality

## Files Added/Modified

### New Files Created:
- `/includes/class-labcorp-locator.php` - Main locator class with Google Maps integration
- `/assets/js/labcorp-locator.js` - JavaScript for map functionality and location search
- `/assets/css/labcorp-locator.css` - Comprehensive styling for 2-column layout

### Modified Files:
- `/ennulifeassessments.php` - Added LabCorp locator class include
- `/templates/user-dashboard.php` - Updated LabCorp upload tab with 2-column layout
- `/includes/class-labcorp-locator.php` - Added CSS enqueue for proper styling

## Features

### LabCorp Locator (Left Column):
- **Search by Address**: Enter ZIP code or full address
- **Geolocation**: "Use My Location" button for automatic location detection
- **Interactive Map**: Google Maps with custom markers
- **Location Details**: Shows distance, hours, phone, ratings for each location
- **Actions**: Get directions and visit website links for each location
- **Responsive Design**: Mobile-friendly layout

### PDF Upload (Right Column):
- **Existing Functionality**: All previous upload features preserved
- **Consistent Styling**: Updated to match the new 2-column design
- **Benefits Display**: Clear list of upload benefits
- **Instructions**: Step-by-step upload guidance

## Technical Details

- **Google Maps Integration**: Uses Maps JavaScript API and Places API
- **Security**: Proper nonce validation and input sanitization
- **Performance**: Efficient caching and optimized API calls
- **Accessibility**: Screen reader support and keyboard navigation
- **Responsive**: Mobile-first design with breakpoints at 768px and 480px

## Settings Page

Access the LabCorp locator settings via:
**WordPress Admin → ENNU Life → LabCorp Settings**

Here you can:
- Configure Google Maps API key
- View setup instructions and documentation links

## Troubleshooting

### Map Not Loading:
1. Check that Google Maps API key is configured
2. Verify APIs are enabled in Google Cloud Console
3. Check browser console for JavaScript errors

### Location Search Not Working:
1. Ensure Places API is enabled
2. Check API key restrictions and quotas
3. Verify geolocation permissions in browser

### Styling Issues:
1. Clear browser cache
2. Check that `labcorp-locator.css` is loading
3. Verify no CSS conflicts with theme

## Support

If you encounter any issues:
1. Check the browser console for errors
2. Verify API key configuration
3. Test with different browsers and devices
4. Contact support with specific error messages

## Version

- **Version**: 78.1.0+
- **Compatibility**: WordPress 5.0+, PHP 7.4+
- **Dependencies**: jQuery, Google Maps API