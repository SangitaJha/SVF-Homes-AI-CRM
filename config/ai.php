<?php

declare(strict_types=1);

return [
    'openai_api_key' => env('OPENAI_API_KEY', ''),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'temperature' => (float)env('OPENAI_TEMPERATURE', 0.3),
];