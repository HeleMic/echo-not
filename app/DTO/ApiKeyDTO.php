<?php

namespace App\DTO;

use App\Http\Requests\ApiKeys\StoreApiKeyRequest;
use App\Http\Requests\ApiKeys\UpdateApiKeyRequest;

class ApiKeyDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $applicationId,
        public readonly string $name,
        public readonly string $key,
        public readonly string $lastUsedAt,
        public readonly string $expiresAt,
        public readonly string $revokedAt,
    ) {
        //
    }

    /**
     * Returns a new class instance from `StoreApiKeyRequest`.
     *
     * @param  \App\Http\Requests\ApiKeys\StoreApiKeyRequest $request
     * @return \App\DTO\ApiKeyDTO
     */
    public static function fromStoreRequest(StoreApiKeyRequest $request): self
    {
        $validated = $request->validated();
        return self::fromArray($validated);
    }

    /**
     * Returns a new class instance from `UpdateApiKeyRequest`.
     *
     * @param  \App\Http\Requests\ApiKeys\UpdateApiKeyRequest $request
     * @return \App\DTO\ApiKeyDTO
     */
    public static function fromUpdateRequest(UpdateApiKeyRequest $request): self
    {
        $validated = $request->validated();
        return self::fromArray($validated);
    }

    /**
     * Returns a new class instance from an array.
     *
     * @param  array $data
     * @return \App\DTO\ApiKeyDTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            applicationId: $data['application_id'] ?? null,
            name: $data['name'],
            key: $data['key'],
            lastUsedAt: $data['last_used_at'],
            expiresAt: $data['expires_at'],
            revokedAt: $data['revoked_at'],
        );
    }
}