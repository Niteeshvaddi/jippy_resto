<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Social Login Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the social login providers for your application.
    | You can enable/disable providers and configure their credentials.
    |
    */

    'providers' => [
        'google' => [
            'enabled' => env('GOOGLE_LOGIN_ENABLED', false),
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('APP_URL') . '/auth/google/callback',
        ],

        'facebook' => [
            'enabled' => env('FACEBOOK_LOGIN_ENABLED', false),
            'client_id' => env('FACEBOOK_CLIENT_ID'),
            'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
            'redirect' => env('APP_URL') . '/auth/facebook/callback',
        ],

        'github' => [
            'enabled' => env('GITHUB_LOGIN_ENABLED', false),
            'client_id' => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
            'redirect' => env('APP_URL') . '/auth/github/callback',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Login Settings
    |--------------------------------------------------------------------------
    |
    | General settings for social login functionality.
    |
    */

    'settings' => [
        'auto_verify_email' => true,
        'require_email_verification' => false,
        'allow_multiple_providers' => true,
        'default_avatar' => null,
    ],
];
