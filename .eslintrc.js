module.exports = {
    env: {
        browser: true,
        es2021: true,
        jquery: false
    },
    extends: [
        'eslint:recommended'
    ],
    parserOptions: {
        ecmaVersion: 12,
        sourceType: 'module'
    },
    globals: {
        wp: 'readonly',
        ajaxurl: 'readonly',
        ennuBiomarkerAdmin: 'readonly',
        ennuAdminEnhanced: 'readonly',
        ennuUserDashboard: 'readonly'
    },
    rules: {
        'no-console': 'warn',
        'no-unused-vars': 'error',
        'no-undef': 'error',
        'prefer-const': 'error',
        'no-var': 'error',
        'arrow-spacing': 'error',
        'comma-dangle': ['error', 'never'],
        'indent': ['error', 4],
        'quotes': ['error', 'single'],
        'semi': ['error', 'always']
    }
};
