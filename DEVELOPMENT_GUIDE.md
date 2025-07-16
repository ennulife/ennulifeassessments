# ENNU Life Assessments - Development Guide

**The Ultimate WordPress Plugin Development Experience**  
*Created by the undisputed master of WordPress development*

---

## 🚀 Quick Start

### Prerequisites
- **Node.js 18+** and **npm 9+**
- **PHP 7.4+** with **Composer**
- **WordPress 5.0+** development environment

### Installation
```bash
# Install dependencies
npm install
composer install

# Set up development environment
npm run dev

# Run tests
npm run test:js
composer test
```

---

## 🔧 Development Workflow

### 1. Frontend Development

#### Modern JavaScript Development
```bash
# Development mode (with file watching)
npm run dev

# Production build
npm run build

# Analyze bundle size
npm run analyze
```

#### Code Quality
```bash
# Lint JavaScript/TypeScript
npm run lint:js

# Type checking
npm run type-check

# Lint CSS
npm run lint:css
```

### 2. Backend Development

#### Testing
```bash
# Run PHP unit tests
composer test

# Run with coverage
composer test:coverage

# Run specific test suite
vendor/bin/phpunit tests/scoring/
```

#### Code Standards
```bash
# Check PHP coding standards
composer phpcs

# Analyze with PHPStan
composer phpstan
```

---

## 📁 Project Structure

```
ennu-life-plugin/
├── assets/
│   ├── css/              # Source CSS files
│   ├── js/               # Source JavaScript files
│   └── dist/             # Built/minified assets (auto-generated)
├── includes/
│   ├── config/           # Configuration files
│   └── *.php             # Core PHP classes
├── templates/            # PHP template files
├── tests/
│   ├── unit/             # Unit tests
│   ├── integration/      # Integration tests
│   ├── scoring/          # Scoring system tests
│   └── includes/         # Test utilities
├── cypress/              # E2E tests
├── composer.json         # PHP dependencies
├── package.json          # Node.js dependencies
├── vite.config.js        # Build configuration
├── tsconfig.json         # TypeScript configuration
└── phpunit.xml.dist      # Testing configuration
```

---

## 🧪 Testing Strategy

### PHP Testing with PHPUnit

#### Base Test Class
```php
class MyTest extends ENNU_Test_Case {
    public function test_assessment_scoring() {
        // Create test data
        $this->create_mock_assessment_data('hair_assessment', 75);
        
        // Assert expectations
        $this->assertUserMetaExists('ennu_hair_assessment_overall_score');
    }
}
```

#### Running Tests
```bash
# All tests
composer test

# Specific test file
vendor/bin/phpunit tests/scoring/test-scoring-system.php

# With coverage
composer test:coverage
```

### JavaScript Testing with Jest

#### Example Test
```javascript
import { validateFormData } from '@/form-validation';

describe('Form Validation', () => {
    test('validates required fields', () => {
        const result = validateFormData({ age_range: '' });
        expect(result.isValid).toBe(false);
        expect(result.errors).toContain('Age range is required');
    });
});
```

#### Running JavaScript Tests
```bash
# All JS tests
npm run test:js

# Watch mode
npm run test:js:watch
```

### End-to-End Testing with Cypress

```bash
# Open Cypress UI
npm run test:e2e:open

# Run headless
npm run test:e2e
```

---

## 🎯 Asset Management

### Intelligent Asset Loading

The plugin automatically chooses between development and production assets:

```php
// In production (WP_DEBUG = false)
'assets/dist/ennu-frontend-forms.min.js'

// In development (WP_DEBUG = true)  
'assets/js/ennu-frontend-forms.js'
```

### Building Assets

```bash
# Development build (with source maps)
npm run dev

# Production build (minified, optimized)
npm run build:prod

# Watch for changes during development
npm run dev
```

---

## 📝 Code Standards

### PHP Standards
- **PSR-12** coding standard
- **WordPress Coding Standards**
- **PHPStan** level 8 analysis
- **Comprehensive docblocks**

### JavaScript Standards
- **ES2020+** features
- **TypeScript** for type safety
- **ESLint** with WordPress rules
- **Prettier** for formatting

### CSS Standards
- **Stylelint** with standard config
- **BEM methodology** preferred
- **CSS Custom Properties** for theming

---

## 🔍 Debugging

### PHP Debugging
```php
// Enable WordPress debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Use error_log for debugging
error_log('Debug data: ' . print_r($data, true));
```

### JavaScript Debugging
```javascript
// Debug mode detection
if (window.ennu_ajax?.debug) {
    console.log('Debug data:', data);
}
```

---

## 🚀 Performance Optimization

### Asset Optimization
- **Tree shaking** removes unused code
- **Code splitting** for better caching
- **Legacy support** for older browsers
- **Asset inlining** for small files

### PHP Optimization
- **Object caching** for expensive operations
- **Transient caching** for temporary data
- **Database query optimization**
- **Memory usage monitoring**

---

## 🔐 Security Best Practices

### AJAX Security
```php
// Always verify nonces
if (!wp_verify_nonce($_POST['nonce'], 'ennu_ajax_nonce')) {
    wp_die('Security check failed');
}

// Sanitize all input
$email = sanitize_email($_POST['email']);
```

### Data Validation
```javascript
// Client-side validation
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}
```

---

## 📊 Monitoring & Analytics

### Performance Monitoring
```bash
# Bundle size analysis
npm run analyze

# Lighthouse CI integration
npm run lighthouse
```

### Error Tracking
```php
// Log errors properly
if (is_wp_error($result)) {
    error_log('ENNU Error: ' . $result->get_error_message());
}
```

---

## 🎨 Customization

### Theme Integration
```css
/* CSS Custom Properties for easy theming */
:root {
    --ennu-primary-color: #5A67D8;
    --ennu-secondary-color: #667eea;
    --ennu-border-radius: 8px;
}
```

### Hook System
```php
// Custom action hooks
do_action('ennu_before_assessment_render', $assessment_type);
do_action('ennu_after_score_calculation', $score_data);

// Filter hooks
$score = apply_filters('ennu_modify_assessment_score', $score, $assessment_type);
```

---

## 🔄 Deployment

### Production Checklist
- [ ] Run full test suite: `composer test && npm run test:js`
- [ ] Build production assets: `npm run build:prod`
- [ ] Update version numbers in plugin file and changelog
- [ ] Verify all assets are minified and optimized
- [ ] Test on staging environment
- [ ] Deploy with zero downtime

### Automated Deployment
```bash
# CI/CD pipeline example
npm ci
composer install --no-dev --optimize-autoloader
npm run build:prod
npm run test:e2e
```

---

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Install dependencies: `npm install && composer install`
4. Make changes with tests
5. Run quality checks: `npm run lint:js && composer phpcs`
6. Submit pull request

### Code Review Checklist
- [ ] All tests passing
- [ ] Code follows standards
- [ ] Documentation updated
- [ ] Performance impact considered
- [ ] Security implications reviewed

---

## 📚 Additional Resources

- [WordPress Plugin Development Handbook](https://developer.wordpress.org/plugins/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Vite Guide](https://vitejs.dev/guide/)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)

---

**Remember**: You're working with the world's most advanced WordPress assessment plugin, created by the supreme master of web development. Maintain the same level of excellence in all your contributions!