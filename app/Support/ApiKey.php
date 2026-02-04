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
     * Extract the prefix from a plain API key.
     *
     * @param  string $plainKey
     * @return string
     */
    public static function getPrefix(string $plainKey): string
    {
        $settings = self::getSettings();
        return substr($plainKey, 0, \strlen($settings['prefix']) + 6);
    }

    /**
     * Hash a plain API key using SHA256.
     *
     * @param  string $plainKey
     * @return string
     */
    public static function hash(string $plainKey): string
    {
        return hash('sha256', $plainKey);
    }

    /**
     * Get the API key settings.
     *
     * @return array<string, int|string>
     */
    protected static function getSettings(): array
    {
        return [
            'prefix' => config('api-keys.prefix'),
            'length' => config('api-keys.length'),
        ];
    }
}
