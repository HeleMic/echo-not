<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Api Keys Prefix
    |--------------------------------------------------------------------------
    |
    | This option defines the prefix for generated API keys.
    */
    'prefix' => env('API_KEY_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Api Keys Length
    |--------------------------------------------------------------------------
    |
    | This option defines the length for generated API keys.
    */
    'length' => env('API_KEY_LENGTH', 64),

    /*
    |--------------------------------------------------------------------------
    | Api Keys Hidden Placeholder
    |--------------------------------------------------------------------------
    |
    | This option defines the placeholder for hidden API keys.
    */
    'hidden_placeholder' => env('API_KEY_HIDDEN_PLACEHOLDER', '*****'),
];