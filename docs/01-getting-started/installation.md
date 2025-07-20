# ENNU Life Plugin v62.2.9 - Installation Guide

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
2. Choose ennu-life-v62.2.9.zip
3. Click "Install Now"
4. Click "Activate Plugin"

# Method 2: FTP/cPanel Upload
1. Extract zip file to get ennulifeassessments folder
2. Upload folder to /wp-content/plugins/
3. Go to WordPress Admin > Plugins
4. Activate "ENNU Life Assessments"
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
   - Select `ennu-life-v62.2.9.zip`
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
   - Find "ENNU Life Assessments"
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

## Uninstallation

To completely remove the ENNU Life Assessment Plugin and all of its data, simply deactivate and delete the plugin from the WordPress admin dashboard. The plugin includes an uninstallation hook that will automatically remove all of the following data:

*   All plugin options from the `wp_options` table.
*   All user meta data associated with the plugin from the `wp_usermeta` table.
*   Any custom database tables created by the plugin.

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
```
```
