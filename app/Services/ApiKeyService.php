<?php

namespace App\Services;

use App\Models\User;
use App\DTO\ApiKeyDTO;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Hash;
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
        if ($user->isAdmin()) {
            return ApiKey::paginate(config('constants.pagination.elements_for_page'));
        }

        return ApiKey::paginate(config('constants.pagination.elements_for_page'));
    }

    /**
     * Create a new api key.
     *
     * @param  \App\DTO\ApiKeyDTO $dto
     * @return \App\Models\ApiKey
     */
    public function create(ApiKeyDTO $dto): ApiKey
    {
        // Generate a new hashed key
        $dto = $dto->withKey(Hash::make(\App\Support\ApiKey::generate()));

        // Set default expiration if not provided
        if ($dto->expiresAt === null) {
            $dto = $dto->withExpiresAt(now()->addSeconds(config('api-keys.duration')));
        }

        return ApiKey::create([
            'application_id' => $dto->applicationId,
            'name' => $dto->name,
            'key' => $dto->key,
            'expires_at' => $dto->expiresAt?->format(config('constants.date.format')),
        ]);
    }

    /**
     * Update an existing api key.
     *
     * @param  \App\Models\ApiKey $apiKey
     * @param  \App\DTO\ApiKeyDTO $dto
     * @return \App\Models\ApiKey
     */
    public function update(ApiKey $apiKey, ApiKeyDTO $dto): ApiKey
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
            'revokedAt' => now(),
        ]);
        return $apiKey;
    }
}