<?php
/**
 * Test Notification System
 * 
 * Tests the detailed notification system with biomarker information
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WP_DEBUG', true );

// Mock WordPress functions
if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type = 'mysql' ) {
        return date( 'Y-m-d H:i:s' );
    }
}

// Include required files
require_once 'includes/services/class-pdf-processor.php';

// Set content type
header( 'Content-Type: text/html; charset=utf-8' );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎯 Notification System Test</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .test-button {
            background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin: 10px;
            transition: transform 0.2s;
        }
        .test-button:hover {
            transform: translateY(-2px);
        }
        .notification-demo {
            margin: 20px 0;
            padding: 20px;
            background: #e3f2fd;
            border-radius: 10px;
            border-left: 5px solid #2196f3;
        }
        .biomarker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .biomarker-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Notification System Test</h1>
        <p>Testing the detailed notification system with biomarker information display.</p>
        
        <div class="test-section">
            <h2>🧪 Test Notifications</h2>
            <p>Click the buttons below to test different notification types:</p>
            
            <button class="test-button" onclick="testSuccessNotification()">
                ✅ Test Success Notification
            </button>
            
            <button class="test-button" onclick="testErrorNotification()">
                ❌ Test Error Notification
            </button>
            
            <button class="test-button" onclick="testWarningNotification()">
                ⚠️ Test Warning Notification
            </button>
            
            <button class="test-button" onclick="testInfoNotification()">
                ℹ️ Test Info Notification
            </button>
        </div>
        
        <div class="test-section">
            <h2>📊 Biomarker Data Demo</h2>
            <div class="notification-demo">
                <h3>Sample Biomarker Data (15 biomarkers)</h3>
                <div class="biomarker-grid">
                    <div class="biomarker-card">
                        <strong>Total Cholesterol</strong><br>
                        <span class="success">180 mg/dL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>LDL Cholesterol</strong><br>
                        <span class="success">100 mg/dL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>HDL Cholesterol</strong><br>
                        <span class="success">50 mg/dL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>Triglycerides</strong><br>
                        <span class="success">150 mg/dL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>Glucose</strong><br>
                        <span class="success">95 mg/dL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>HbA1c</strong><br>
                        <span class="success">5.7%</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>Testosterone</strong><br>
                        <span class="success">600 ng/dL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>TSH</strong><br>
                        <span class="success">2.5 mIU/L</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>Vitamin D</strong><br>
                        <span class="success">30 ng/mL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>ApoB</strong><br>
                        <span class="success">80 mg/dL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>Lp(a)</strong><br>
                        <span class="success">15 mg/dL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>Insulin</strong><br>
                        <span class="success">8 μIU/mL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>C-Peptide</strong><br>
                        <span class="success">2.5 ng/mL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>Estradiol</strong><br>
                        <span class="success">25 pg/mL</span>
                    </div>
                    <div class="biomarker-card">
                        <strong>Progesterone</strong><br>
                        <span class="success">0.5 ng/mL</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <h2>🔧 Notification Functions</h2>
            <p>The notification system includes these features:</p>
            <ul>
                <li><strong>Detailed Biomarker Display:</strong> Shows each biomarker with name, value, and unit</li>
                <li><strong>Success/Error/Warning/Info Types:</strong> Different visual styles for different message types</li>
                <li><strong>Auto-dismiss:</strong> Notifications automatically disappear after 10-15 seconds</li>
                <li><strong>Manual Close:</strong> Users can close notifications manually</li>
                <li><strong>Timestamp:</strong> Shows when the notification was created</li>
                <li><strong>Responsive Design:</strong> Adapts to different screen sizes</li>
            </ul>
        </div>
    </div>

    <script>
        // Enhanced notification system (copied from user-dashboard.php)
        function showDetailedNotification(data) {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.ennu-notification');
            existingNotifications.forEach(notification => notification.remove());
            
            // Create notification container
            const notificationContainer = document.createElement('div');
            notificationContainer.className = 'ennu-notification-container';
            notificationContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 500px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            `;
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `ennu-notification ennu-notification-${data.notification?.type || 'info'}`;
            
            // Set notification styles based on type
            const styles = {
                success: {
                    background: 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)',
                    border: '2px solid #28a745',
                    color: '#155724'
                },
                error: {
                    background: 'linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%)',
                    border: '2px solid #dc3545',
                    color: '#721c24'
                },
                warning: {
                    background: 'linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%)',
                    border: '2px solid #ffc107',
                    color: '#856404'
                },
                info: {
                    background: 'linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%)',
                    border: '2px solid #17a2b8',
                    color: '#0c5460'
                }
            };
            
            const notificationType = data.notification?.type || 'info';
            const style = styles[notificationType];
            
            notification.style.cssText = `
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                margin-bottom: 15px;
                background: ${style.background};
                border: ${style.border};
                color: ${style.color};
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-height: 80vh;
                overflow-y: auto;
            `;
            
            // Create notification content
            let notificationContent = '';
            
            if (data.notification) {
                notificationContent += `
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="flex-shrink: 0; font-size: 24px;">
                            ${getNotificationIcon(data.notification.type)}
                        </div>
                        <div style="flex-grow: 1;">
                            <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">
                                ${data.notification.title || 'Notification'}
                            </h3>
                            <p style="margin: 0 0 15px 0; line-height: 1.5;">
                                ${data.notification.message || data.message || 'Operation completed.'}
                            </p>
                `;
                
                // Add biomarker details if available
                if (data.notification.biomarker_details && Object.keys(data.notification.biomarker_details).length > 0) {
                    notificationContent += `
                        <div style="background: rgba(255,255,255,0.3); padding: 15px; border-radius: 8px; margin-top: 15px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">
                                📊 Imported Biomarkers (${data.notification.biomarker_count})
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    `;
                    
                    Object.entries(data.notification.biomarker_details).forEach(([key, details]) => {
                        notificationContent += `
                            <div style="background: rgba(255,255,255,0.5); padding: 10px; border-radius: 6px; border-left: 3px solid #28a745;">
                                <div style="font-weight: 600; font-size: 14px;">${getBiomarkerDisplayName(key)}</div>
                                <div style="font-size: 16px; font-weight: 700; color: #28a745;">${details.display}</div>
                            </div>
                        `;
                    });
                    
                    notificationContent += `
                            </div>
                        </div>
                    `;
                }
                
                // Add timestamp
                if (data.notification.timestamp) {
                    notificationContent += `
                        <div style="margin-top: 15px; font-size: 12px; opacity: 0.7;">
                            🕒 ${new Date(data.notification.timestamp).toLocaleString()}
                        </div>
                    `;
                }
                
                notificationContent += `
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" style="
                            background: none;
                            border: none;
                            font-size: 20px;
                            cursor: pointer;
                            color: inherit;
                            opacity: 0.7;
                            padding: 0;
                            width: 24px;
                            height: 24px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        " title="Close">×</button>
                    </div>
                `;
            } else {
                // Fallback for simple messages
                notificationContent = `
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="flex-grow: 1;">
                            <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">
                                ${data.success ? 'Success' : 'Error'}
                            </h3>
                            <p style="margin: 0; line-height: 1.5;">
                                ${data.message || 'Operation completed.'}
                            </p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" style="
                            background: none;
                            border: none;
                            font-size: 20px;
                            cursor: pointer;
                            color: inherit;
                            opacity: 0.7;
                            padding: 0;
                            width: 24px;
                            height: 24px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        " title="Close">×</button>
                    </div>
                `;
            }
            
            notification.innerHTML = notificationContent;
            notificationContainer.appendChild(notification);
            document.body.appendChild(notificationContainer);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto-remove after 10 seconds for success, 15 seconds for errors
            const autoRemoveTime = data.notification?.type === 'success' ? 10000 : 15000;
            setTimeout(() => {
                if (notificationContainer.parentElement) {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notificationContainer.parentElement) {
                            notificationContainer.remove();
                        }
                    }, 300);
                }
            }, autoRemoveTime);
        }
        
        // Helper function to get notification icon
        function getNotificationIcon(type) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            return icons[type] || icons.info;
        }
        
        // Helper function to get biomarker display name
        function getBiomarkerDisplayName(key) {
            const names = {
                'total_cholesterol': 'Total Cholesterol',
                'ldl_cholesterol': 'LDL Cholesterol',
                'hdl_cholesterol': 'HDL Cholesterol',
                'triglycerides': 'Triglycerides',
                'glucose': 'Glucose',
                'hba1c': 'HbA1c',
                'testosterone': 'Testosterone',
                'tsh': 'TSH',
                'vitamin_d': 'Vitamin D',
                'apob': 'ApoB',
                'lp_a': 'Lp(a)',
                'insulin': 'Insulin',
                'c_peptide': 'C-Peptide',
                'estradiol': 'Estradiol',
                'progesterone': 'Progesterone',
                'dhea_s': 'DHEA-S',
                'cortisol': 'Cortisol',
                'free_t4': 'Free T4',
                'free_t3': 'Free T3',
                'vitamin_b12': 'Vitamin B12',
                'folate': 'Folate',
                'iron': 'Iron',
                'ferritin': 'Ferritin',
                'zinc': 'Zinc',
                'magnesium': 'Magnesium',
                'crp': 'CRP',
                'hs_crp': 'hs-CRP',
                'esr': 'ESR',
                'creatinine': 'Creatinine',
                'bun': 'BUN',
                'egfr': 'eGFR',
                'alt': 'ALT',
                'ast': 'AST',
                'alkaline_phosphatase': 'Alkaline Phosphatase',
                'bilirubin': 'Bilirubin',
                'hemoglobin': 'Hemoglobin',
                'hematocrit': 'Hematocrit',
                'wbc': 'WBC',
                'rbc': 'RBC',
                'platelets': 'Platelets'
            };
            return names[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }
        
        // Test functions
        function testSuccessNotification() {
            const sampleBiomarkers = {
                'total_cholesterol': { value: 180, unit: 'mg/dL', display: '180 mg/dL' },
                'ldl_cholesterol': { value: 100, unit: 'mg/dL', display: '100 mg/dL' },
                'hdl_cholesterol': { value: 50, unit: 'mg/dL', display: '50 mg/dL' },
                'triglycerides': { value: 150, unit: 'mg/dL', display: '150 mg/dL' },
                'glucose': { value: 95, unit: 'mg/dL', display: '95 mg/dL' },
                'hba1c': { value: 5.7, unit: '%', display: '5.7%' },
                'testosterone': { value: 600, unit: 'ng/dL', display: '600 ng/dL' },
                'tsh': { value: 2.5, unit: 'mIU/L', display: '2.5 mIU/L' },
                'vitamin_d': { value: 30, unit: 'ng/mL', display: '30 ng/mL' },
                'apob': { value: 80, unit: 'mg/dL', display: '80 mg/dL' },
                'lp_a': { value: 15, unit: 'mg/dL', display: '15 mg/dL' },
                'insulin': { value: 8, unit: 'μIU/mL', display: '8 μIU/mL' },
                'c_peptide': { value: 2.5, unit: 'ng/mL', display: '2.5 ng/mL' },
                'estradiol': { value: 25, unit: 'pg/mL', display: '25 pg/mL' },
                'progesterone': { value: 0.5, unit: 'ng/mL', display: '0.5 ng/mL' }
            };
            
            showDetailedNotification({
                success: true,
                message: 'Successfully processed PDF and imported 15 biomarkers.',
                biomarkers_imported: 15,
                notification: {
                    type: 'success',
                    title: '✅ Successfully imported 15 biomarkers',
                    message: 'Your LabCorp results have been processed and saved to your profile. The following biomarkers were imported: Total Cholesterol, LDL Cholesterol, HDL Cholesterol, Triglycerides, Glucose, HbA1c, Testosterone, TSH, Vitamin D, ApoB, Lp(a), Insulin, C-Peptide, Estradiol, Progesterone',
                    timestamp: new Date().toISOString(),
                    biomarkers: sampleBiomarkers,
                    biomarker_count: 15,
                    biomarker_details: sampleBiomarkers
                }
            });
        }
        
        function testErrorNotification() {
            showDetailedNotification({
                success: false,
                message: 'Failed to process PDF. No valid biomarker data found.',
                notification: {
                    type: 'error',
                    title: '❌ Processing Failed',
                    message: 'The uploaded PDF does not contain recognizable LabCorp biomarker data. Please ensure you are uploading a valid LabCorp results document.',
                    timestamp: new Date().toISOString()
                }
            });
        }
        
        function testWarningNotification() {
            showDetailedNotification({
                success: true,
                message: 'PDF processed with warnings. Some biomarkers could not be extracted.',
                biomarkers_imported: 8,
                notification: {
                    type: 'warning',
                    title: '⚠️ Partial Import Completed',
                    message: 'Your PDF was processed but only 8 out of 15 expected biomarkers were found. Some data may be missing or in an unrecognized format.',
                    timestamp: new Date().toISOString(),
                    biomarker_count: 8
                }
            });
        }
        
        function testInfoNotification() {
            showDetailedNotification({
                success: true,
                message: 'PDF processing in progress. This may take up to 30 seconds.',
                notification: {
                    type: 'info',
                    title: 'ℹ️ Processing Information',
                    message: 'Your LabCorp PDF is being analyzed for biomarker data. This process includes text extraction, pattern matching, and data validation.',
                    timestamp: new Date().toISOString()
                }
            });
        }
    </script>
</body>
</html> 