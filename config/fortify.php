<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'limiters' => ['login' => 'login'],
    'views' => true,

    // This is an institutional application. Accounts are provisioned by
    // authorized administrators; public self-registration is intentionally off.
    'features' => [
        Features::resetPasswords(),
        Features::emailVerification(),
    ],
];
