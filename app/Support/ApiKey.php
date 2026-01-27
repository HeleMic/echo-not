<?php

namespace App\Support;

use Illuminate\Support\Str;

class ApiKey
{
    /**
     * Generate a new API key.
     *
     * @return string
     */
    public static function generate(): string
    {
        $settings = self::getSettings();

        return $settings['prefix'] . Str::random($settings['length']);
    }

    /**
     * Get the API key settings.
     *
     * @return array<string, int|string>
     */
    protected static function getSettings(): array
    {
        return [
            'prefix' => config('application.api_key.prefix'),
            'length' => config('application.api_key.length'),
        ];
    }
}
