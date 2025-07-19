# Official AI Onboarding Instructions - ENNU Life Assessments Plugin

## 🎯 **MISSION STATEMENT**

You are now working on the **ENNU Life Assessments WordPress Plugin** - a comprehensive health assessment system with advanced scoring, user dashboards, and health goal tracking. This is a **production system** serving real users, so every change must be **bulletproof**.

## 📋 **CRITICAL RULES - NEVER BREAK THESE**

### **1. ALWAYS Update Version & Changelog**
- **EVERY** change requires version bump in `ennu-life-plugin.php`
- **EVERY** change requires changelog entry
- **NEVER** skip this step - it's mandatory

### **2. Functional First Approach**
- **Fix broken functionality** before optimization
- **Test everything** before assuming it works
- **User experience** trumps technical perfection
- **Business value** over code elegance

### **3. WordPress Standards Compliance**
- Follow WordPress coding standards
- Use proper hooks and filters
- Sanitize ALL user input
- Validate ALL data
- Use nonces for security

### **4. Error Handling & Logging**
- **NEVER** let errors crash the system
- **ALWAYS** log errors with context
- **ALWAYS** provide user-friendly error messages
- **NEVER** expose system internals to users

## 🏗️ **PLUGIN ARCHITECTURE OVERVIEW**

### **Core Components:**
```
ennu-life-plugin.php (Main Plugin File)
├── includes/
│   ├── class-enhanced-database.php (Database Operations)
│   ├── class-assessment-shortcodes.php (Assessment Forms & Logic)
│   ├── class-scoring-system.php (Score Calculations)
│   ├── class-health-goals-ajax.php (Health Goals Management)
│   ├── class-enhanced-admin.php (Admin Interface)
│   └── [Other specialized classes]
├── templates/
│   ├── user-dashboard.php (Main User Interface)
│   ├── assessment-results.php (Results Display)
│   └── [Other templates]
├── assets/
│   ├── css/ (Stylesheets)
│   ├── js/ (JavaScript)
│   └── img/ (Images)
└── config/
    ├── assessments/ (Assessment Definitions)
    └── scoring/ (Scoring Rules)
```

### **Key Workflows:**
1. **Assessment Submission** → Data Validation → Score Calculation → Results Storage → Email Notification
2. **Dashboard Display** → Data Aggregation → Score Retrieval → Template Rendering
3. **Health Goals** → AJAX Update → Score Recalculation → Dashboard Refresh

## 🔧 **CURRENT FUNCTIONAL STATUS**

### **✅ Working Components:**
- Assessment form rendering and submission
- User creation and authentication
- Basic score calculations
- Health goals AJAX updates
- Email notifications (with error handling)
- Dashboard display
- Admin interface

### **⚠️ Known Issues:**
- Assessment results stored in transients (can expire)
- Complex scoring system with multiple engines
- Performance issues with heavy database operations
- Asset loading logic is overly complex
- Email delivery not tracked/reported

### **🚨 Critical Areas:**
- **Assessment submission flow** - overly complex dual-engine system
- **Scoring calculations** - multiple layers that could conflict
- **Database performance** - heavy operations without optimization
- **Frontend assets** - complex loading logic

## 📝 **DEVELOPMENT WORKFLOW**

### **Before Making ANY Changes:**

1. **Read the existing code** thoroughly
2. **Understand the business logic** behind the feature
3. **Check the changelog** for recent changes
4. **Test the current functionality** to establish baseline
5. **Plan your approach** with minimal disruption

### **When Making Changes:**

1. **Start with the functional test** (`test-functional-simple.php`)
2. **Make incremental changes** - never rewrite entire systems
3. **Test each change** before moving to the next
4. **Update version and changelog** immediately
5. **Document your changes** clearly

### **After Making Changes:**

1. **Test the complete user journey**
2. **Verify no regressions** in existing functionality
3. **Check error logs** for any issues
4. **Update documentation** if needed
5. **Commit with clear commit message**

## 🛠️ **ESSENTIAL FILES TO KNOW**

### **Core Files (Never Delete/Replace):**
- `ennu-life-plugin.php` - Main plugin file (version control)
- `includes/class-assessment-shortcodes.php` - Assessment logic (4,426 lines)
- `includes/class-scoring-system.php` - Score calculations
- `includes/class-enhanced-database.php` - Database operations
- `templates/user-dashboard.php` - Main user interface (2,346 lines)

### **Configuration Files:**
- `includes/config/assessments/` - Assessment definitions
- `includes/config/scoring/` - Scoring rules and mappings
- `includes/config/business-model.php` - Business logic

### **Documentation:**
- `docs/` - Comprehensive documentation
- `roadmap-code/` - Technical roadmap
- `CHANGELOG.md` - Version history

## 🔍 **DEBUGGING & TESTING**

### **Testing Tools:**
- `test-functional-simple.php` - Basic functionality test
- `test-functional-quick.php` - Comprehensive test (if PHP available)
- WordPress debug logs - Check for errors
- Browser developer tools - Frontend issues

### **Common Issues:**
1. **Assessment submission fails** - Check AJAX handlers and nonces
2. **Dashboard doesn't load** - Check user authentication and data retrieval
3. **Scores don't calculate** - Check scoring system dependencies
4. **Health goals don't update** - Check AJAX handlers and user meta
5. **Emails don't send** - Check email configuration and error logs

### **Debug Commands:**
```php
// Enable WordPress debug logging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Check plugin status
is_plugin_active('ennu-life-assessments/ennu-life-plugin.php');

// Test database connection
global $wpdb;
$wpdb->get_results("SELECT 1");
```

## 📊 **PERFORMANCE CONSIDERATIONS**

### **Database Optimization:**
- Use proper indexing on user meta queries
- Implement caching for frequently accessed data
- Batch database operations when possible
- Monitor query performance

### **Frontend Optimization:**
- Minimize CSS/JS file sizes
- Use proper asset versioning (not `time()`)
- Implement lazy loading for dashboard data
- Optimize image assets

### **Memory Management:**
- Clean up transients and temporary data
- Limit data retrieval to necessary fields
- Use pagination for large datasets
- Monitor memory usage in admin

## 🔒 **SECURITY REQUIREMENTS**

### **Input Validation:**
- **ALWAYS** sanitize user input with `sanitize_text_field()`, `sanitize_email()`, etc.
- **ALWAYS** validate data types and ranges
- **NEVER** trust user input

### **AJAX Security:**
- **ALWAYS** verify nonces with `wp_verify_nonce()`
- **ALWAYS** check user capabilities
- **ALWAYS** validate user authentication

### **Database Security:**
- **ALWAYS** use prepared statements
- **NEVER** use direct SQL queries with user input
- **ALWAYS** escape output with `esc_html()`, `esc_attr()`, etc.

### **File Security:**
- **ALWAYS** check file permissions
- **NEVER** allow direct file access
- **ALWAYS** validate file uploads

## 📈 **BUSINESS LOGIC UNDERSTANDING**

### **Core Business Model:**
- **Health assessments** for various conditions (hair, ED, weight loss, etc.)
- **Scoring system** with multiple engines (quantitative, qualitative, intentionality)
- **User dashboards** with progress tracking
- **Health goals** with score impact
- **Consultation recommendations** based on scores

### **User Journey:**
1. User takes assessment → 2. Gets scored → 3. Views results → 4. Sets goals → 5. Tracks progress → 6. Gets recommendations

### **Revenue Streams:**
- Assessment consultations
- Health optimization programs
- Lab testing recommendations
- Coaching services

## 🚀 **DEPLOYMENT CHECKLIST**

### **Before Deployment:**
- [ ] All tests pass
- [ ] Version updated
- [ ] Changelog updated
- [ ] Error logging enabled
- [ ] Backup created
- [ ] Rollback plan ready

### **After Deployment:**
- [ ] Monitor error logs
- [ ] Test user journeys
- [ ] Verify email delivery
- [ ] Check performance
- [ ] Monitor user feedback

## 📞 **EMERGENCY PROCEDURES**

### **If Plugin Breaks:**
1. **Immediately** check error logs
2. **Disable** problematic features if needed
3. **Rollback** to previous version if critical
4. **Notify** stakeholders
5. **Document** the issue

### **If Database Issues:**
1. **Check** database connectivity
2. **Verify** table structure
3. **Run** database repair if needed
4. **Restore** from backup if necessary

### **If Security Breach:**
1. **Immediately** disable plugin
2. **Investigate** the breach
3. **Patch** security holes
4. **Notify** users if data compromised
5. **Document** incident

## 🎯 **SUCCESS METRICS**

### **Functional Success:**
- All assessment types work end-to-end
- Dashboard displays correctly
- Health goals system functions
- Email notifications work
- Results display properly
- Forms submit successfully

### **Performance Success:**
- Dashboard loads in <3 seconds
- Assessment submission completes in <5 seconds
- No database timeout errors
- Memory usage stays reasonable
- No PHP fatal errors

### **User Experience Success:**
- No broken functionality
- Clear error messages
- Responsive design works
- Intuitive navigation
- Positive user feedback

## 📚 **RESOURCES & REFERENCES**

### **WordPress Documentation:**
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Database Schema](https://developer.wordpress.org/reference/database/)

### **Plugin-Specific Documentation:**
- `docs/` - Comprehensive documentation
- `roadmap-code/` - Technical roadmap
- `CHANGELOG.md` - Version history
- `README.md` - Plugin overview

### **Testing Resources:**
- WordPress testing framework
- Browser developer tools
- Database monitoring tools
- Performance profiling tools

---

## 🎯 **FINAL REMINDER**

**You are working on a production system that serves real users.** Every change you make affects real people's health assessments and recommendations. **Be thorough, be careful, and always prioritize user experience over technical perfection.**

**Remember: Functional first, then optimize. Never break what's working.**

---

*Last Updated: Version 62.2.7 - AI Onboarding Instructions*
*Maintained by: ENNU Life Development Team* 