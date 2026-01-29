<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Configurations
    |--------------------------------------------------------------------------
    |
    | This options defines the configuration settings for applications.
    */
    'api_key' => [
        'prefix' => env('APPLICATION_API_KEY_PREFIX', ''),
        'length' => env('APPLICATION_API_KEY_LENGTH', 64),
        'hidden_placeholder' => env('APPLICATION_API_KEY_HIDDEN_PLACEHOLDER', '*****'),
    ],
];