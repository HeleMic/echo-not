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
        $settins = self::getSettings();

        return $settins['prefix'] . Str::random($settins['length']);
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
