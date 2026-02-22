/** @type {import('stylelint').Config} */
export default {
  plugins: [
    './stylelint-terra/index.mjs', 
    'stylelint-scss'
  ],
  customSyntax: 'postcss-scss',
  rules: {
    // Terra custom rules
    'terra/selector-naming': true,

    // Best practices for z-index (should have position)
    // Note: This is informational - stylelint can't enforce position with z-index directly

    // Allow !important
    'declaration-no-important': null,

    // Nesting depth is handled by terra/selector-naming rule
    // which respects flexible components (content, table, etc.)
    'max-nesting-depth': null,

    // Disallow unknown units
    'unit-no-unknown': true,

    // Disallow duplicate selectors
    'no-duplicate-selectors': true,

    // Allow empty blocks (components may start as scaffolds)
    'block-no-empty': null,

    // Color format consistency
    'color-no-invalid-hex': true,

    // No descending specificity
    'no-descending-specificity': null, // Disabled - can be too strict for BEM

    // Selector specificity
    // ID selectors are handled by terra/selector-naming rule
    // which respects flexible components (content, table, etc.)
    'selector-max-id': null,
    'selector-max-universal': 1, // Limit universal selectors
  },
  overrides: [
    {
      // Only apply Terra naming rules to component files
      files: [
          'src/scss/framework/components/**/*.scss',
          'src/scss/global-components/**/*.scss',
          'src/scss/style.scss'
      ],
      rules: {
        'terra/selector-naming': true,
      },
    },
    {
      // Disable Terra naming for utilities, foundation, and other non-component files
      files: [
        'src/scss/framework/utilities/**/*.scss',
        'src/scss/framework/foundation/**/*.scss',
        'src/scss/framework/_vars/**/*.scss',
        'src/scss/debug/**/*.scss',
      ],
      rules: {
        'terra/selector-naming': null,
      },
    },
    {
      // Allow !important in debug and utilities (required for these use cases)
      files: ['src/scss/debug/**/*.scss', 'src/scss/framework/utilities/**/*.scss'],
      rules: {
        'declaration-no-important': null,
      },
    },
    {
      // Allow !important in style.scss for critical overrides
      files: ['src/scss/style.scss'],
      rules: {
        'declaration-no-important': null,
        'terra/selector-naming': true,
      },
    },
  ],
  ignoreFiles: [
    'node_modules/**',
    'dist/**',
    '**/vendor/**',
    'src/scss/backend/_app-backend.scss',
     'src/scss/framework/foundation/reset/_reset.scss',
    // Temporarily ignore files that need refactoring
    // Remove these as you fix them
  ],
};
