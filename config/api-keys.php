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
    | Api Keys Duration (in seconds)
    |--------------------------------------------------------------------------
    |
    | This option defines the duration (in seconds) for generated API keys.
    */
    'duration' => env('API_KEY_DURATION', 31_536_000),

    /*
    |--------------------------------------------------------------------------
    | Api Keys Max Duration (in seconds)
    |--------------------------------------------------------------------------
    |
    | This option defines the max duration (in seconds) for generated API keys.
    | Use 0 or negative value for unlimited duration.
    */
    'max_duration' => env('API_KEY_MAX_DURATION', 315_360_000),

    /*
    |--------------------------------------------------------------------------
    | Api Keys Hidden Placeholder
    |--------------------------------------------------------------------------
    |
    | This option defines the placeholder for hidden API keys.
    */
    'hidden_placeholder' => env('API_KEY_HIDDEN_PLACEHOLDER', '*****'),
];