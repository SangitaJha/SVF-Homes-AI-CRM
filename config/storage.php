<?php

declare(strict_types=1);

return [
    'driver' => env('STORAGE_DRIVER', 'local'),
    'local' => [
        'path' => __DIR__ . '/../uploads',
    ],
    's3' => [
        'key' => env('AWS_ACCESS_KEY_ID', ''),
        'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
        'region' => env('AWS_DEFAULT_REGION', ''),
        'bucket' => env('AWS_BUCKET', ''),
    ],
    'azure' => [
        'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING', ''),
        'container' => env('AZURE_STORAGE_CONTAINER', ''),
    ],
];