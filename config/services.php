<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'plesk' => [
        'api' => env('PLESK_API'),
        'panel_password' => env('DEFAULT_PLESK_PASSWORD'),
        'wordpress_password' => env('DEFAULT_WP_PASSWORD')
    ],

    'vultr' => [
        'api' => env('VULTR_API'),
        'api_endpoint' => env('VULTR_API_ENDPOINT', 'https://api.vultr.com/v2'),
        'ssh-id' => env('VULTR_SSH_ID')
    ],

    'cloudflare' => [
        'username' => env('CLOUDFLARE_USER'),
        'api_key' => env('CLOUDFLARE_API')
    ]
];
