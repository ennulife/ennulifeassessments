# ENNU Life Plugin v24.0.0 - Installation Guide

## 🚀 **Quick Installation**

### **Step 1: Backup Current Plugin**
```bash
# If upgrading from existing version
1. Go to WordPress Admin > Plugins
2. Deactivate "ENNU Life Assessments" plugin
3. Download backup of current plugin folder (optional)
```

### **Step 2: Upload New Plugin**
```bash
# Method 1: WordPress Admin Upload
1. Go to Plugins > Add New > Upload Plugin
2. Choose ennu-life-v24.0.0-FRONTEND-BACKEND-FIXED.zip
3. Click "Install Now"
4. Click "Activate Plugin"

# Method 2: FTP/cPanel Upload
1. Extract zip file to get ennulifeassessments folder
2. Upload folder to /wp-content/plugins/
3. Go to WordPress Admin > Plugins
4. Activate "ENNU Life Assessments Enhanced"
```

### **Step 3: Verify Installation**
```bash
1. Check admin menu for "ENNU Life" 
2. Visit ENNU Life > Dashboard
3. Check ENNU Life > Settings
4. Test frontend forms on pages with shortcodes
```

## 🔧 **Detailed Installation Steps**

### **Pre-Installation Checklist**
- [ ] WordPress 5.0 or higher
- [ ] PHP 7.4 or higher  
- [ ] MySQL 5.6 or higher
- [ ] Admin access to WordPress
- [ ] FTP/cPanel access (if manual upload)

### **Installation Methods**

#### **Method 1: WordPress Admin (Recommended)**
1. **Login to WordPress Admin**
   - Navigate to your WordPress admin dashboard
   - Ensure you have administrator privileges

2. **Access Plugin Upload**
   - Go to `Plugins > Add New`
   - Click `Upload Plugin` button at top

3. **Upload Plugin File**
   - Click `Choose File`
   - Select `ennu-life-v24.0.0-FRONTEND-BACKEND-FIXED.zip`
   - Click `Install Now`

4. **Activate Plugin**
   - After installation completes, click `Activate Plugin`
   - Plugin will appear in your plugins list as activated

#### **Method 2: FTP/cPanel Upload**
1. **Extract Plugin Files**
   - Download and extract the zip file
   - You should see `ennulifeassessments` folder

2. **Upload via FTP**
   ```bash
   # Connect to your server via FTP
   # Navigate to /wp-content/plugins/
   # Upload the entire ennulifeassessments folder
   ```

3. **Upload via cPanel File Manager**
   - Login to cPanel
   - Open File Manager
   - Navigate to `/public_html/wp-content/plugins/`
   - Upload and extract the zip file

4. **Activate in WordPress**
   - Go to WordPress Admin > Plugins
   - Find "ENNU Life Assessments Enhanced"
   - Click "Activate"

### **Post-Installation Configuration**

#### **Step 1: Access Admin Dashboard**
1. Go to WordPress Admin
2. Look for "ENNU Life" in the admin menu
3. Click "ENNU Life" to access dashboard

#### **Step 2: Configure Settings**
1. Navigate to `ENNU Life > Settings`
2. Configure the following:

   **Email Settings:**
   - Admin Email: Enter your admin email address
   - Enable Email Notifications: Check to receive notifications

   **Integration Settings:**
   - WP Fusion: Enable if you have WP Fusion installed
   - HubSpot API Key: Enter your HubSpot API key (optional)

   **Cache Settings:**
   - Cache Results: Enable for better performance

3. Click "Save Changes"

#### **Step 3: Test Frontend Forms**
1. **Create Test Page**
   - Go to `Pages > Add New`
   - Add title: "Test Assessment"
   - Add shortcode: `[ennu-welcome-assessment]`
   - Publish page

2. **Test Form Functionality**
   - Visit the test page on frontend
   - Verify form displays correctly
   - Test navigation between questions
   - Test form submission

#### **Step 4: Verify Admin Functionality**
1. **Check Dashboard**
   - Go to `ENNU Life > Dashboard`
   - Verify statistics display
   - Check recent assessments table

2. **Check Assessments Page**
   - Go to `ENNU Life > Assessments`
   - Verify assessment type cards
   - Check assessment data tables

## 🔍 **Troubleshooting Installation**

### **Common Issues**

#### **Plugin Not Appearing in Admin Menu**
```bash
Possible Causes:
- Plugin not activated
- User doesn't have admin privileges
- Plugin files not uploaded correctly

Solutions:
1. Check Plugins page - ensure plugin is activated
2. Check user role - must be Administrator
3. Re-upload plugin files
4. Check file permissions (755 for folders, 644 for files)
```

#### **Frontend Forms Not Displaying**
```bash
Possible Causes:
- Shortcode not added correctly
- Theme conflicts
- JavaScript errors

Solutions:
1. Verify shortcode spelling: [ennu-welcome-assessment]
2. Check browser console for JavaScript errors
3. Test with default WordPress theme
4. Ensure jQuery is loaded
```

#### **Admin Pages Showing Errors**
```bash
Possible Causes:
- Database permissions
- PHP version compatibility
- Memory limits

Solutions:
1. Check PHP error logs
2. Increase PHP memory limit to 256MB
3. Verify database user has CREATE/ALTER permissions
4. Check PHP version (7.4+ required)
```

#### **AJAX Submissions Failing**
```bash
Possible Causes:
- WordPress AJAX not working
- Security plugins blocking requests
- Server configuration issues

Solutions:
1. Test with security plugins disabled
2. Check .htaccess file for conflicts
3. Verify admin-ajax.php is accessible
4. Check server error logs
```

### **File Permissions**
```bash
# Correct file permissions
Folders: 755 (drwxr-xr-x)
Files: 644 (-rw-r--r--)

# Set permissions via command line
find /path/to/ennulifeassessments -type d -exec chmod 755 {} \;
find /path/to/ennulifeassessments -type f -exec chmod 644 {} \;
```

### **Database Issues**
```bash
# If database tables not created
1. Deactivate plugin
2. Reactivate plugin (triggers table creation)
3. Check database for ennu_* tables
4. Verify user has CREATE TABLE permissions
```

## 🔧 **Advanced Configuration**

### **Custom CSS/JS**
```css
/* Add to theme's style.css or custom CSS */
.ennu-assessment {
    /* Custom styling */
}
```

### **Hooks and Filters**
```php
// Add to theme's functions.php
add_filter('ennu_assessment_config', 'custom_assessment_config');
function custom_assessment_config($config) {
    // Customize assessment configuration
    return $config;
}
```

### **Database Optimization**
```sql
-- Optimize assessment tables
OPTIMIZE TABLE wp_usermeta;
ANALYZE TABLE wp_usermeta;
```

## 📊 **Verification Checklist**

After installation, verify these items:

### **Admin Functionality**
- [ ] ENNU Life menu appears in admin
- [ ] Dashboard shows statistics
- [ ] Assessments page displays data
- [ ] Settings page loads and saves
- [ ] User profiles show assessment data

### **Frontend Functionality**
- [ ] Assessment forms display correctly
- [ ] Multi-step navigation works
- [ ] Form validation functions
- [ ] AJAX submission works
- [ ] Success messages appear
- [ ] Mobile responsive design

### **Integration Testing**
- [ ] WP Fusion integration (if enabled)
- [ ] HubSpot integration (if configured)
- [ ] Email notifications (if enabled)
- [ ] Caching system working
- [ ] Security features active

## 🆘 **Getting Help**

### **Documentation**
- Check `documentation/` folder for detailed reports
- Review README.md for feature overview
- Check phase audit reports for technical details

### **Common Solutions**
1. **Clear Cache** - Clear any caching plugins
2. **Check Logs** - Review PHP and WordPress error logs
3. **Test Environment** - Try on staging site first
4. **Plugin Conflicts** - Deactivate other plugins to test

### **Support Information**
- Plugin Version: v24.0.0 Enhanced
- WordPress Compatibility: 5.0+
- PHP Compatibility: 7.4+
- Last Updated: Current date

---

**🎉 Installation Complete! Your ENNU Life plugin is ready to use.**

*For technical support, refer to the comprehensive documentation included in the package.*

