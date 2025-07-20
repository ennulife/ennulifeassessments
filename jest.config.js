module.exports = {
    preset: '@wordpress/jest-preset-default',
    setupFilesAfterEnv: ['<rootDir>/tests/js/setup.js'],
    testMatch: ['<rootDir>/tests/js/**/*.test.js'],
    collectCoverageFrom: [
        'assets/js/**/*.js',
        '!assets/js/**/*.min.js',
        '!assets/js/vendor/**'
    ],
    coverageDirectory: 'coverage',
    coverageReporters: ['text', 'lcov', 'html'],
    moduleNameMapper: {
        '^@/(.*)$': '<rootDir>/assets/js/$1',
        '\\.(css|less|scss|sass)$': 'identity-obj-proxy'
    },
    testTimeout: 10000,
    transform: {
        '^.+\\.[jt]sx?$': 'babel-jest',
    }
};     