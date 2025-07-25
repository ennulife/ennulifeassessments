module.exports = {
    extends: ['stylelint-config-standard'],
    rules: {
        'indentation': 4,
        'string-quotes': 'single',
        'no-duplicate-selectors': true,
        'color-hex-case': 'lower',
        'color-hex-length': 'short',
        'color-named': 'never',
        'selector-combinator-space-after': 'always',
        'selector-attribute-quotes': 'always',
        'selector-attribute-operator-space-before': 'never',
        'selector-attribute-operator-space-after': 'never',
        'selector-attribute-brackets-space-inside': 'never',
        'declaration-block-trailing-semicolon': 'always',
        'declaration-no-important': true,
        'declaration-colon-space-before': 'never',
        'declaration-colon-space-after': 'always',
        'number-leading-zero': 'always',
        'function-url-quotes': 'always',
        'font-weight-notation': 'numeric',
        'comment-whitespace-inside': 'always',
        'rule-empty-line-before': 'always-multi-line'
    }
};
