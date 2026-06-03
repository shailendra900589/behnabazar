<?php

return [
    'enabled' => env('SEO_ENABLED', true),

    /** Rebuild sitemap + ping Google/Bing/IndexNow when products change and on schedule */
    'auto_index' => env('SEO_AUTO_INDEX', true),

    'sitemap_cache_minutes' => 60,

    'indexnow_max_urls' => 200,

    'default_locale' => 'en-IN',
    'default_country' => 'IN',
    'currency' => 'INR',

    /** GEO defaults (override via admin settings: seo_city, seo_state, etc.) */
    'geo' => [
        'locality' => env('SEO_LOCALITY', 'India'),
        'region' => env('SEO_REGION', 'IN'),
        'latitude' => env('SEO_LATITUDE', '28.6139'),
        'longitude' => env('SEO_LONGITUDE', '77.2090'),
        'area_served' => 'IN',
    ],

    'title_separator' => ' | ',
    'max_description_length' => 160,
    'max_keywords' => 12,

    'robots' => [
        'index' => true,
        'follow' => true,
    ],

    /** Routes that should not be indexed */
    'noindex_routes' => [
        'login',
        'register',
        'dashboard',
        'checkout',
        'cart',
        'profile',
        'wishlist',
        'orders',
        'account.verify.show',
        'password.request',
        'vendor.register.create',
        'vendor.payment.show',
        'manage.',
    ],

    'static_pages' => [
        'contact' => [
            'title' => 'Contact & Help Center',
            'description' => 'Contact Behna Bazar support for orders, returns, vendor onboarding, and marketplace help.',
        ],
        'local-delivery' => [
            'title' => 'Local Delivery Information',
            'description' => 'Learn about local delivery areas, timelines, and shipping coverage on Behna Bazar.',
        ],
        'returns-policy' => [
            'title' => 'Returns & Refund Policy',
            'description' => 'Read our returns, refunds, and exchange policy for marketplace orders.',
        ],
    ],
];
