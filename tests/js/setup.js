/**
 * Jest setup file for ENNU Life Assessments testing
 */

global.wp = {
    ajax: {
        post: jest.fn(),
        send: jest.fn()
    },
    i18n: {
        __: jest.fn((text) => text),
        _e: jest.fn((text) => text),
        _n: jest.fn((single, plural, number) => number === 1 ? single : plural)
    }
};

global.$ = global.jQuery = jest.fn(() => ({
    ready: jest.fn(),
    on: jest.fn(),
    off: jest.fn(),
    trigger: jest.fn(),
    val: jest.fn(),
    text: jest.fn(),
    html: jest.fn(),
    addClass: jest.fn(),
    removeClass: jest.fn(),
    show: jest.fn(),
    hide: jest.fn(),
    fadeIn: jest.fn(),
    fadeOut: jest.fn(),
    ajax: jest.fn()
}));

global.console = {
    ...console,
    log: jest.fn(),
    debug: jest.fn(),
    info: jest.fn(),
    warn: jest.fn(),
    error: jest.fn()
};

const localStorageMock = {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn()
};
global.localStorage = localStorageMock;

const sessionStorageMock = {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn()
};
global.sessionStorage = sessionStorageMock;

global.ajaxurl = '/wp-admin/admin-ajax.php';

Object.defineProperty(window, 'location', {
    value: {
        href: 'http://localhost'
    },
    writable: true
});

global.fetch = jest.fn();
