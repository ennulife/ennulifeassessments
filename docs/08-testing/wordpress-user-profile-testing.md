# ENNU Life Plugin - WordPress User Profile Integration Test Documentation

**Version:** 62.2.8
**Author:** Luis Escobar  
**Last Updated:** January 2025

---

## 1.0 Overview

The WordPress User Profile Integration Test is a comprehensive testing suite designed to verify that the ENNU Life plugin properly integrates with WordPress user profiles and that all global fields (gender, DOB, height, weight) are being saved and retrieved correctly.

### **Purpose**
- Verify WordPress user profile page functionality
- Test global fields data persistence
- Ensure ENNU Life plugin integration works correctly
- Validate data retrieval and display
- Test error handling and validation

---

## 2.0 Test File Location

**File:** `test-wp-user-profile-integration.php`  
**Location:** Plugin root directory  
**Access:** Admin → Tools → ENNU Profile Test

---

## 3.0 Test Features

### **3.1 Test Controls**
- **Run Profile Integration Test**: Executes the main test suite
- **Test Data Persistence**: Tests data survival across sessions
- **Clear Test Data**: Removes all test data from the system

### **3.2 Test Data Used**
```php
$test_data = [
    'gender' => 'male',
    'dob' => '1990-05-15',
    'height' => [
        'ft' => '5',
        'in' => '10'
    ],
    'weight' => '175'
];
```

### **3.3 Test Coverage**

#### **✅ User Profile Page Accessibility**
- Verifies profile page can be accessed
- Checks if ENNU Life fields are present
- Tests user data retrieval

#### **✅ Global Fields Saving**
- Tests gender field saving
- Tests DOB field saving
- Tests height/weight field saving
- Verifies data persistence in user meta

#### **✅ Data Retrieval**
- Tests direct user meta retrieval
- Tests ENNU Life plugin method retrieval
- Verifies data consistency

#### **✅ ENNU Life Integration**
- Tests user dashboard rendering
- Verifies data appears in dashboard
- Tests shortcode functionality

#### **✅ Validation**
- Tests invalid data handling
- Verifies error handling
- Tests data sanitization

---

## 4.0 How to Run the Test

### **4.1 Access the Test**
1. Log into WordPress admin
2. Navigate to **Tools → ENNU Profile Test**
3. Click **Run Profile Integration Test**

### **4.2 Understanding Results**

#### **Success Indicators (✅)**
- Test user created successfully
- Profile page accessible
- Global fields saved correctly
- Data retrieval working
- ENNU Life integration functional
- Validation working properly

#### **Error Indicators (❌)**
- User creation failed
- Profile page inaccessible
- Data not saving
- Retrieval methods failing
- Integration issues
- Validation failures

#### **Warning Indicators (⚠️)**
- Non-critical issues
- Missing optional features
- Performance concerns

---

## 5.0 Test Methods

### **5.1 Main Test Methods**

#### **`run_profile_test()`**
- Main test orchestrator
- Executes all test steps in sequence
- Handles exceptions and errors
- Displays comprehensive results

#### **`test_data_persistence()`**
- Tests data survival across sessions
- Simulates cache clearing
- Verifies data consistency

### **5.2 Individual Test Methods**

#### **`create_test_user()`**
```php
private function create_test_user() {
    $test_email = 'ennu-test-user@example.com';
    $this->test_user_id = email_exists($test_email);
    
    if (!$this->test_user_id) {
        $this->test_user_id = wp_create_user(
            'ennu_test_user',
            'test_password_123',
            $test_email
        );
    }
}
```

#### **`test_profile_page_access()`**
- Verifies user profile accessibility
- Tests ENNU Life field presence
- Checks hook registration

#### **`test_global_fields_saving()`**
- Saves test data to user meta
- Verifies data was saved correctly
- Tests all global field types

#### **`test_data_retrieval()`**
- Tests direct user meta retrieval
- Tests ENNU Life plugin methods
- Verifies data consistency

#### **`test_ennu_integration()`**
- Tests user dashboard rendering
- Verifies data appears correctly
- Tests shortcode functionality

#### **`test_validation()`**
- Tests invalid data handling
- Verifies error handling
- Tests data sanitization

---

## 6.0 Test Results Interpretation

### **6.1 Success Criteria**
All tests should return **✅ Success** for a fully functional system:

1. **Test user created/verified** ✅
2. **Profile page accessible** ✅
3. **Global fields saved correctly** ✅
4. **Data retrieval working** ✅
5. **ENNU Life integration functional** ✅
6. **Validation working properly** ✅

### **6.2 Common Issues and Solutions**

#### **Issue: "ENNU Life plugin not active"**
**Solution:** Ensure the ENNU Life plugin is activated

#### **Issue: "Profile page inaccessible"**
**Solution:** Check user permissions and WordPress configuration

#### **Issue: "Global fields not saving"**
**Solution:** Verify database permissions and user meta functionality

#### **Issue: "Data retrieval failed"**
**Solution:** Check ENNU Life plugin methods and data structure

---

## 7.0 Integration with ENNU Life Plugin

### **7.1 Global Fields Tested**
- `ennu_global_user_gender`
- `ennu_global_user_dob_combined`
- `ennu_global_height_weight`

### **7.2 Plugin Methods Tested**
- `get_user_global_data()`
- `render_user_dashboard()`
- Assessment pre-population methods

### **7.3 Data Flow Verification**
1. **Input**: User enters data in assessment
2. **Storage**: Data saved to user meta
3. **Retrieval**: Data retrieved by plugin methods
4. **Display**: Data shown in dashboard/profile

---

## 8.0 Troubleshooting

### **8.1 Test Fails to Run**
- Check WordPress AJAX functionality
- Verify user permissions
- Check for JavaScript errors

### **8.2 Data Not Saving**
- Check database permissions
- Verify user meta functionality
- Check for plugin conflicts

### **8.3 Integration Issues**
- Verify ENNU Life plugin is active
- Check method availability
- Review error logs

---

## 9.0 Security Considerations

### **9.1 Nonce Verification**
All AJAX requests use WordPress nonces for security:
```php
if (!wp_verify_nonce($_POST['nonce'], 'ennu_profile_test')) {
    wp_die('Security check failed');
}
```

### **9.2 User Permissions**
Tests require `manage_options` capability:
```php
'manage_options',
'ennu-profile-test',
```

### **9.3 Data Isolation**
Test data is isolated and can be cleared:
- Test user created with unique email
- Test data stored in user meta
- Clear functionality available

---

## 10.0 Future Enhancements

### **10.1 Planned Improvements**
- Automated testing on plugin updates
- Performance benchmarking
- Extended validation testing
- Integration with CI/CD pipeline

### **10.2 Additional Test Scenarios**
- Multi-user testing
- Concurrent access testing
- Data migration testing
- Plugin update testing

---

## 11.0 Support and Maintenance

### **11.1 Regular Testing**
- Run tests after plugin updates
- Test after WordPress updates
- Verify after theme changes
- Test after adding new plugins

### **11.2 Maintenance Tasks**
- Clear test data regularly
- Update test data as needed
- Monitor test results
- Update documentation

---

## 12.0 Conclusion

The WordPress User Profile Integration Test provides comprehensive verification that the ENNU Life plugin properly integrates with WordPress user profiles and maintains data integrity across all operations. Regular use of this test ensures the plugin continues to function correctly as the system evolves.

**For support or questions about this test, please contact the ENNU Life Development Team.** 