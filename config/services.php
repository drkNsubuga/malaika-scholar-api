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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pesapal Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Pesapal payment processing. Supports both sandbox
    | and live environments. Make sure to register IPN URLs and get
    | notification IDs from Pesapal dashboard.
    |
    */

    'pesapal' => [
        'environment' => env('PESAPAL_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'live'
        'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
        'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
        'default_notification_id' => env('PESAPAL_DEFAULT_NOTIFICATION_ID'),
        
        // Callback URLs
        'callback_url' => env('PESAPAL_CALLBACK_URL', env('APP_URL') . '/api/payments/pesapal/callback'),
        'ipn_url' => env('PESAPAL_IPN_URL', env('APP_URL') . '/api/payments/pesapal/ipn'),
        
        // Default currency
        'default_currency' => env('PESAPAL_DEFAULT_CURRENCY', 'KES'),
        
        // Supported currencies
        'supported_currencies' => ['KES', 'USD', 'EUR', 'GBP'],
        
        // Payment methods configuration
        'payment_methods' => [
            'card' => env('PESAPAL_ENABLE_CARD', true),
            'mobile_money' => env('PESAPAL_ENABLE_MOBILE_MONEY', true),
            'bank_transfer' => env('PESAPAL_ENABLE_BANK_TRANSFER', true),
        ],
        
        // Timeout settings (in seconds)
        'timeout' => [
            'connection' => env('PESAPAL_CONNECTION_TIMEOUT', 30),
            'request' => env('PESAPAL_REQUEST_TIMEOUT', 60),
        ],
        
        // Retry settings
        'retry' => [
            'max_attempts' => env('PESAPAL_MAX_RETRY_ATTEMPTS', 3),
            'delay_seconds' => env('PESAPAL_RETRY_DELAY', 5),
        ],
        
        // Webhook security
        'webhook_secret' => env('PESAPAL_WEBHOOK_SECRET'),
        
        // Logging
        'log_requests' => env('PESAPAL_LOG_REQUESTS', true),
        'log_responses' => env('PESAPAL_LOG_RESPONSES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for email services used for notifications and receipts.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SMS services used for notifications.
    |
    */

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'africastalking' => [
        'username' => env('AFRICASTALKING_USERNAME'),
        'api_key' => env('AFRICASTALKING_API_KEY'),
        'from' => env('AFRICASTALKING_FROM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage Services
    |--------------------------------------------------------------------------
    |
    | Configuration for cloud storage services.
    |
    */

    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
        'secure' => env('CLOUDINARY_SECURE', true),
    ],

];