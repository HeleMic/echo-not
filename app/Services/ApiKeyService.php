<?php

namespace App\Services;

use App\Models\User;
use App\DTO\ApiKeyDTO;
use App\Models\ApiKey;
use App\Models\Application;
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
        $key = \App\Support\ApiKey::generate();

        $hashedKey = Hash::make($key);

        return ApiKey::create([
            'applicationId' => $dto->applicationId,
            'name' => $dto->name,
            'key' => $hashedKey,
            'expiresAt' => $dto->expiresAt,
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
            'expiresAt' => $dto->expiresAt,
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