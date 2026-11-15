<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Presentation mode
    |--------------------------------------------------------------------------
    |
    | Presentation mode is the only environment in which the deterministic
    | demonstration accounts and portfolio may be seeded. It must never be
    | enabled for an institutional production deployment.
    |
    */
    'demo_mode' => (bool) env('DEMO_MODE', false),

    'timezone' => env('INSTITUTION_TIMEZONE', 'Africa/Lagos'),

    'demo_account_emails' => [
        'admin@example.com',
        'management@example.com',
        'legal@example.com',
        'officer@example.com',
        'viewer@example.com',
    ],

    'partner_categories' => [
        'Academic Institution',
        'Government Agency',
        'Private Sector',
        'NGO / Foundation',
        'International Organization',
        'Research Institution',
        'Professional Body',
        'Other',
    ],
];
