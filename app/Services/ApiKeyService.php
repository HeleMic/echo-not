<?php

namespace App\Services;

use Str;
use App\Models\User;
use App\Models\ApiKey;
use App\DTO\ApiKeys\StoreApiKeyDTO;
use App\DTO\ApiKeys\UpdateApiKeyDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiKeyService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all api keys.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll(User $user): LengthAwarePaginator
    {
        $query = ApiKey::with('application.user');

        if ($user->isAdmin()) {
            return $query->paginate(config('constants.pagination.elements_for_page'));
        }

        $applicationIds = $user->applications()->pluck('id');

        return $query
            ->whereIn('application_id', $applicationIds)
            ->paginate(config('constants.pagination.elements_for_page'));
    }

    /**
     * Create a new api key.
     *
     * @param  \App\DTO\ApiKeys\StoreApiKeyDTO $dto
     * @return \App\Models\ApiKey
     */
    public function create(StoreApiKeyDTO $dto): ApiKey
    {
        // Generate a new key
        $plainKey = \App\Support\ApiKey::generate();

        // Set default expiration if not provided
        $expiresAt = $dto->expiresAt
            ?? now()->addSeconds((int) config('api-keys.duration'))->toDateTimeString();

        $apiKey = ApiKey::create([
            'application_id' => $dto->applicationId,
            'name' => $dto->name,
            'key_prefix' => \App\Support\ApiKey::getPrefix($plainKey),
            'key' => \App\Support\ApiKey::hash($plainKey),
            'expires_at' => $expiresAt,
        ]);

        $apiKey->plainKey = $plainKey;
        return $apiKey;
    }

    /**
     * Update an existing api key.
     *
     * @param  \App\Models\ApiKey $apiKey
     * @param  \App\DTO\ApiKeys\UpdateApiKeyDTO $dto
     * @return \App\Models\ApiKey
     */
    public function update(ApiKey $apiKey, UpdateApiKeyDTO $dto): ApiKey
    {
        $apiKey->update([
            'name' => $dto->name,
        ]);
        return $apiKey;
    }

    /**
     * Delete an existing api key.
     *
     * @param  \App\Models\ApiKey $apiKey
     * @return void
     */
    public function delete(ApiKey $apiKey): void
    {
        $apiKey->delete();
    }

    /**
     * Revoke an existing api key.
     *
     * @param  \App\Models\ApiKey $apiKey
     * @return \App\Models\ApiKey
     */
    public function revoke(ApiKey $apiKey): ApiKey
    {
        $apiKey->update([
            'revoked_at' => now(),
        ]);
        return $apiKey;
    }
}