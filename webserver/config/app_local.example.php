<?php
return [
    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    'Security' => [
        'salt' => env('SECURITY_SALT', '__SALT__'),
    ],
    'Datasources' => [
        'default' => [
            'database' => TMP.'database.sqlite', // path to SQLite3 file
        ],
        'test' => [
            'database' => TMP.'database-test.sqlite', // path to SQLite3 file
        ],
    ],
    // This app NEVER sends emails, so there is no configuration.
];
