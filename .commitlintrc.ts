import { RuleConfigSeverity, UserConfig } from '@commitlint/types';

const Configuration: UserConfig = {
    /*
     * Resolve and load @commitlint/config-conventional from node_modules.
     * Referenced packages must be installed
     */
    extends: ['@commitlint/config-conventional'],
    /*
     * Resolve and load conventional-changelog-atom from node_modules.
     * Referenced packages must be installed
     */
    parserPreset: 'conventional-changelog-conventionalcommits',
    /*
     * Resolve and load @commitlint/format from node_modules.
     * Referenced package must be installed
     */
    formatter: '@commitlint/format',
    /*
     * Any rules defined here will override rules from @commitlint/config-conventional
     * @see https://commitlint.js.org/reference/rules.html
     */
    rules: {
        // ------------------------------------------
        // HEADER
        // ------------------------------------------
        'header-max-length': [RuleConfigSeverity.Error, 'always', 100],
        'header-trim': [RuleConfigSeverity.Error, 'always'],

        // ------------------------------------------
        // TYPE
        // ------------------------------------------
        'type-enum': [
            RuleConfigSeverity.Error,
            'always',
            [
                'chore',
                'ci',
                'docs',
                'feat',
                'fix',
                'perf',
                'refactor',
                'revert',
                'style',
                'test',
                'wip',
            ],
        ],

        // ------------------------------------------
        // SCOPE
        // ------------------------------------------
        'scope-delimiter-style': [RuleConfigSeverity.Error, 'always', [',']],
        'scope-empty': [RuleConfigSeverity.Warning, 'never'],

        // ------------------------------------------
        // SUBJECT
        // ------------------------------------------
        'subject-max-length': [RuleConfigSeverity.Error, 'always', 72],

        // ------------------------------------------
        // BODY
        // ------------------------------------------
        'body-max-line-length': [RuleConfigSeverity.Error, 'always', 200],
        'body-max-length': [RuleConfigSeverity.Error, 'always', 2000],

        // ------------------------------------------
        // FOOTER
        // ------------------------------------------
        'footer-max-line-length': [RuleConfigSeverity.Error, 'always', 100],
        'footer-max-length': [RuleConfigSeverity.Error, 'always', 500],
    },
    /*
     * Array of functions that return true if commitlint should ignore the given message.
     * Given array is merged with predefined functions, which consist of matchers like:
     *
     * - 'Merge pull request', 'Merge X into Y' or 'Merge branch X'
     * - 'Revert X'
     * - 'v1.2.3' (ie semver matcher)
     * - 'Automatic merge X' or 'Auto-merged X into Y'
     *
     * To see full list, check https://github.com/conventional-changelog/commitlint/blob/master/%40commitlint/is-ignored/src/defaults.ts.
     * To disable those ignores and run rules always, set `defaultIgnores: false` as shown below.
     */
    ignores: [],
    /*
     * Whether commitlint uses the default ignore rules, see the description above.
     */
    defaultIgnores: true,
    /*
     * Custom URL to show upon failure
     */
    helpUrl:
        'https://github.com/conventional-changelog/commitlint/#what-is-commitlint',
};

export default Configuration;
