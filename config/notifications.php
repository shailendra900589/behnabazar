<?php

return [
    'sms' => [
        'enabled' => env('SMS_ENABLED', false),
        'driver' => env('SMS_DRIVER', 'log'),
        'default_country_code' => env('SMS_DEFAULT_COUNTRY_CODE', '91'),
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
        'msg91' => [
            'auth_key' => env('MSG91_AUTH_KEY'),
            'sender' => env('MSG91_SENDER', 'BBAZAR'),
            'route' => env('MSG91_ROUTE', '4'),
        ],
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        // auto = Meta Cloud if configured, else CallMeBot, else outbox jugad
        'driver' => env('WHATSAPP_DRIVER', 'auto'),
        'cloud' => [
            'token' => env('WHATSAPP_CLOUD_TOKEN', ''),
            'phone_number_id' => env('WHATSAPP_CLOUD_PHONE_ID', ''),
            'api_version' => env('WHATSAPP_CLOUD_API_VERSION', 'v21.0'),
            'template_name' => env('WHATSAPP_CLOUD_TEMPLATE', ''),
        ],
        'business_phone' => env('WHATSAPP_BUSINESS_PHONE', ''),
        'admin_phone' => env('WHATSAPP_ADMIN_PHONE', ''),
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '91'),
        'webhook_url' => env('WHATSAPP_WEBHOOK_URL', ''),
        'callmebot' => [
            'api_key' => env('WHATSAPP_CALLMEBOT_API_KEY', ''),
        ],
    ],

    'abandoned_cart' => [
        'enabled' => env('ABANDONED_CART_ENABLED', true),
        'idle_hours' => (int) env('ABANDONED_CART_IDLE_HOURS', 24),
        'cooldown_hours' => (int) env('ABANDONED_CART_COOLDOWN_HOURS', 72),
    ],

    'stock_alert' => [
        'enabled' => env('STOCK_ALERT_ENABLED', true),
    ],
];
