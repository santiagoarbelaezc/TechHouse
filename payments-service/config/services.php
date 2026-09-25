<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | TechHouse Internal Microservices Configuration
    |--------------------------------------------------------------------------
    */
    'internal_token' => env('SERVICE_TOKEN'),

    'microservices' => [
        'users' => env('USERS_SERVICE_URL', 'http://localhost:8001'),
        'products' => env('PRODUCTS_SERVICE_URL', 'http://localhost:8002'),
        'payments' => env('PAYMENTS_SERVICE_URL', 'http://localhost:8003'),
        'assistant' => env('ASSISTANT_SERVICE_URL', 'http://localhost:8004'),
    ],

    'ai_api_key' => env('AI_API_KEY'),

];
