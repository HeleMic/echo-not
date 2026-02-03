<?php

namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

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
     * Compare a plain API key with a hashed one.
     *
     * @param  string $plainKey
     * @param  string $hashedKey
     * @return bool
     */
    public static function compare(string $plainKey, string $hashedKey): bool
    {
        return Hash::check($plainKey, $hashedKey);
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
