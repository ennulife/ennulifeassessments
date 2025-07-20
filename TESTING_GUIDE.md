# ENNU Life Assessments - Testing Guide

## Overview
Comprehensive testing infrastructure for WordPress plugin with focus on security, performance, and reliability.

## Prerequisites
- Node.js 16+ and npm/pnpm
- WordPress testing environment via @wordpress/env
- PHPUnit for PHP testing
- Jest for JavaScript testing

## Installation & Setup

### 1. Install Dependencies
```bash
npm install
```

### 2. Start WordPress Testing Environment
```bash
npm run env:start
```

### 3. Install WordPress Test Suite
```bash
npx wp-env run tests-wordpress wp core install --url=http://localhost:8889 --title="Test Site" --admin_user=admin --admin_password=password --admin_email=admin@example.com
```

## Running Tests

### JavaScript Tests (Jest)
```bash
# Run all JavaScript tests
npm test

# Run tests with coverage
npm run test:coverage

# Run tests in watch mode
npm run test:watch
```

### PHP Tests (PHPUnit)
```bash
# Run all PHP tests
npm run test:php

# Run PHP tests with coverage
npm run test:php:coverage

# Run specific test file
npx wp-env run tests-wordpress vendor/bin/phpunit tests/backend/security-validator-test.php
```

### WordPress Environment Management
```bash
# Start environment
npm run env:start

# Stop environment
npm run env:stop

# Run commands in WordPress environment
npm run env:run tests-wordpress wp --info
```

## Test Coverage Areas

### 1. Security Testing
- **Input Sanitization**: XSS prevention, SQL injection protection
- **CSRF Protection**: Nonce verification, token validation
- **Rate Limiting**: Abuse prevention, request throttling
- **Data Access Control**: User permission validation

**Files:**
- `tests/backend/security-validator-test.php`
- `tests/backend/input-sanitizer-test.php`

### 2. Performance Testing
- **Scoring Calculations**: < 100ms per assessment
- **Database Queries**: N+1 optimization verification
- **Memory Usage**: Efficient resource utilization
- **AJAX Endpoints**: < 1 second response times

**Files:**
- `tests/backend/performance-benchmarks-test.php`

### 3. Functional Testing
- **Scoring System**: Algorithm accuracy, edge cases
- **AJAX Handlers**: Request/response validation
- **Data Persistence**: User data storage/retrieval
- **Shortcode Registration**: WordPress integration

**Files:**
- `tests/backend/scoring-system-test.php`
- `tests/backend/ajax-handler-test.php`
- `tests/backend/data-persistence-test.php`
- `tests/backend/shortcode-registration-test.php`

### 4. JavaScript Testing
- **User Interface**: Form validation, user interactions
- **AJAX Functionality**: Frontend-backend communication
- **Security Features**: Client-side input sanitization
- **Accessibility**: Keyboard navigation, ARIA labels

**Files:**
- `tests/js/user-dashboard.test.js`
- `tests/js/ennu-admin.test.js`

## Performance Benchmarks

### Expected Performance Metrics
- **Single Assessment Scoring**: < 100ms
- **Complete User Scoring**: < 2 seconds
- **Database Queries**: < 15 queries per operation
- **Memory Usage**: < 10MB for multiple users
- **AJAX Response Time**: < 1 second

### Running Performance Tests
```bash
# Run performance benchmark suite
npx wp-env run tests-wordpress vendor/bin/phpunit tests/backend/performance-benchmarks-test.php

# Run with detailed output
npx wp-env run tests-wordpress vendor/bin/phpunit --verbose tests/backend/performance-benchmarks-test.php
```

## Security Testing

### Security Test Categories
1. **XSS Prevention**: Script injection attempts
2. **CSRF Protection**: Invalid nonce handling
3. **Input Validation**: Malicious data sanitization
4. **Rate Limiting**: Abuse prevention testing
5. **Data Access**: Permission boundary testing

### Running Security Tests
```bash
# Run all security tests
npx wp-env run tests-wordpress vendor/bin/phpunit tests/backend/security-validator-test.php tests/backend/input-sanitizer-test.php

# Test specific security feature
npx wp-env run tests-wordpress vendor/bin/phpunit --filter test_rate_limit_functionality tests/backend/security-validator-test.php
```

## Test Data & Fixtures

### Creating Test Users
```php
$user_id = self::factory()->user->create(array(
    'user_email' => 'test@example.com',
    'user_login' => 'testuser'
));
```

### Sample Assessment Data
```php
$assessment_data = array(
    'hair_q1' => 'male',
    'hair_q2' => 'receding',
    'hair_q6' => 'high'
);
```

## Continuous Integration

### GitHub Actions Integration
The testing infrastructure is designed to work with GitHub Actions for automated testing on pull requests.

### Local CI Simulation
```bash
# Run full test suite (simulates CI)
npm test && npm run test:php
```

## Troubleshooting

### Common Issues

#### WordPress Environment Not Starting
```bash
# Reset environment
npm run env:stop
docker system prune -f
npm run env:start
```

#### PHPUnit Tests Failing
```bash
# Check WordPress installation
npx wp-env run tests-wordpress wp core version

# Verify plugin activation
npx wp-env run tests-wordpress wp plugin list
```

#### Jest Tests Not Running
```bash
# Clear Jest cache
npx jest --clearCache

# Run with verbose output
npm test -- --verbose
```

### Test Database Issues
```bash
# Reset test database
npx wp-env run tests-wordpress wp db reset --yes
npx wp-env run tests-wordpress wp core install --url=http://localhost:8889 --title="Test Site" --admin_user=admin --admin_password=password --admin_email=admin@example.com
```

## Writing New Tests

### PHP Test Template
```php
<?php
class MyNewTest extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        // Setup code
    }
    
    public function test_my_functionality() {
        // Test implementation
        $this->assertTrue(true);
    }
}
```

### JavaScript Test Template
```javascript
describe('My Component', () => {
    beforeEach(() => {
        // Setup DOM or mocks
    });
    
    test('should work correctly', () => {
        // Test implementation
        expect(true).toBe(true);
    });
});
```

## Coverage Reports

### Viewing Coverage
- **JavaScript**: Open `coverage/lcov-report/index.html`
- **PHP**: Open `coverage-php/index.html`

### Coverage Targets
- **Overall Coverage**: > 80%
- **Critical Functions**: > 95%
- **Security Functions**: 100%
- **Performance Functions**: > 90%

## Best Practices

### Test Organization
- Group related tests in describe blocks
- Use descriptive test names
- Keep tests focused and atomic
- Mock external dependencies

### Performance Testing
- Use realistic data sets
- Test edge cases and limits
- Monitor memory usage
- Verify optimization effectiveness

### Security Testing
- Test all input vectors
- Verify sanitization effectiveness
- Check permission boundaries
- Test rate limiting thresholds

---

## Quick Reference

### Essential Commands
```bash
# Start testing environment
npm run env:start

# Run all tests
npm test && npm run test:php

# Generate coverage reports
npm run test:coverage && npm run test:php:coverage

# Stop environment
npm run env:stop
```

### Test File Locations
- PHP Tests: `tests/backend/`
- JavaScript Tests: `tests/js/`
- Test Configuration: `phpunit.xml.dist`, `jest.config.js`
- WordPress Environment: `.wp-env.json`

For additional help, refer to the WordPress testing documentation and Jest documentation.
