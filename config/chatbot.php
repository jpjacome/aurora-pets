<?php

return [
    // Known model limits (requests per day). Null means unknown/unlimited.
    'model_limits' => [
        'gemini-2.5-flash' => [
            'requests' => 20,
            'description' => 'Gemini 2.5 Flash free tier (very small requests/day)'
        ],
        'gemini-flash-lite-latest' => [
            'requests' => null,
            'description' => 'Flash-Lite (low-cost/free tier, larger quotas)'
        ],
        'gemini-flash-latest' => [
            'requests' => null,
            'description' => 'Flash Latest (higher quota)'
        ],
        'llama-3.3-70b-versatile' => [
            'requests' => null,
            'description' => 'Groq Llama 3.3 (token-based quotas)'
        ],
    ],

    // Default timezone used for greeting and time-based behavior (Bogotá / Quito)
    'default_timezone' => env('CHATBOT_DEFAULT_TIMEZONE', 'America/Bogota'),
];