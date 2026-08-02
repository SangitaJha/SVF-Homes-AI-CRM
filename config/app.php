<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'SVF Homes AI CRM'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    'url' => env('APP_URL', ''),
    'timezone' => 'Asia/Kolkata',
    'currency' => 'INR',
    'currency_symbol' => '₹',
];
