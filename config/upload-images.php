<?php

/**
 * Recommended image sizes for admin / vendor uploads (storefront display).
 */
return [
    'hero_banner' => [
        'size' => '1920 × 500 px',
        'ratio' => 'Wide ~4:1',
        'format' => 'JPG, PNG or WebP',
        'max' => '2 MB',
        'tip' => 'Homepage carousel (desktop height ~420px). Keep logos/text in the center; edges may crop on mobile.',
    ],

    'site_ad' => [
        'size' => '800 × 450 px',
        'ratio' => '16:9',
        'format' => 'JPG, PNG or WebP',
        'max' => '2 MB',
        'tip' => 'Used in site-wide ad slots (side image + text). Clear product/offer photo works best.',
    ],

    'vendor_promotion_ad' => [
        'size' => '800 × 450 px',
        'ratio' => '16:9',
        'format' => 'JPG, PNG or WebP',
        'max' => '2 MB',
        'tip' => 'Vendor wallet promotions shown on the storefront.',
    ],

    'product_primary' => [
        'size' => '1000 × 1000 px',
        'ratio' => 'Square 1:1',
        'format' => 'JPG, PNG or WebP',
        'max' => '2 MB',
        'tip' => 'Main photo on product page, cards & cart. White/light background recommended.',
    ],

    'product_gallery' => [
        'size' => '1000 × 1000 px',
        'ratio' => 'Square 1:1',
        'format' => 'JPG, PNG or WebP',
        'max' => '2 MB each',
        'tip' => 'Extra angles/details. Same size as primary for consistent zoom/gallery.',
    ],

    'vendor_document' => [
        'size' => '1200 × 1600 px (if photo)',
        'ratio' => 'Portrait or PDF',
        'format' => 'JPG, PNG or PDF',
        'max' => '2 MB',
        'tip' => 'Aadhaar/PAN/GST — readable scan; all corners visible.',
    ],
];
