<?php

namespace App\DTO;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use App\Http\Requests\ApiKeys\StoreApiKeyRequest;
use App\Http\Requests\ApiKeys\UpdateApiKeyRequest;

class ApiKeyDTO
{
    public readonly CarbonInterface|null $lastUsedAt;
    public readonly CarbonInterface|null $expiresAt;
    public readonly CarbonInterface|null $revokedAt;

    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $applicationId,
        public readonly string $name,
        public readonly ?string $key,
        protected CarbonInterface|string|null $lastUsedAtRaw,
        protected CarbonInterface|string|null $expiresAtRaw,
        protected CarbonInterface|string|null $revokedAtRaw,
    ) {
        if ($lastUsedAtRaw instanceof CarbonInterface) {
            $this->lastUsedAt = $lastUsedAtRaw;
        } else {
            $this->lastUsedAt = \is_string($lastUsedAtRaw) ? Date::createFromFormat(config('constants.date.format'), $lastUsedAtRaw) : null;
        }

        if ($expiresAtRaw instanceof CarbonInterface) {
            $this->expiresAt = $expiresAtRaw;
        } else {
            $this->expiresAt = \is_string($expiresAtRaw) ? Date::createFromFormat(config('constants.date.format'), $expiresAtRaw) : null;
        }

        if ($revokedAtRaw instanceof CarbonInterface) {
            $this->revokedAt = $revokedAtRaw;
        } else {
            $this->revokedAt = \is_string($revokedAtRaw) ? Date::createFromFormat(config('constants.date.format'), $revokedAtRaw) : null;
        }
    }

    /**
     * Returns a new class instance with the given key.
     *
     * @param  string $key
     * @return \App\DTO\ApiKeyDTO
     */
    public function withKey(string $key): self
    {
        return new self(
            id: $this->id,
            applicationId: $this->applicationId,
            name: $this->name,
            key: $key,
            lastUsedAtRaw: $this->lastUsedAt,
            expiresAtRaw: $this->expiresAt,
            revokedAtRaw: $this->revokedAt,
        );
    }

    /**
     * Returns a new class instance with the given expiration date.
     *
     * @param  \Carbon\CarbonInterface|string $expiresAt
     * @return \App\DTO\ApiKeyDTO
     */
    public function withExpiresAt(CarbonInterface|string $expiresAt): self
    {
        return new self(
            id: $this->id,
            applicationId: $this->applicationId,
            name: $this->name,
            key: $this->key,
            lastUsedAtRaw: $this->lastUsedAt,
            expiresAtRaw: $expiresAt,
            revokedAtRaw: $this->revokedAt,
        );
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
            key: $data['key'] ?? null,
            lastUsedAtRaw: $data['last_used_at'] ?? null,
            expiresAtRaw: $data['expires_at'] ?? null,
            revokedAtRaw: $data['revoked_at'] ?? null,
        );
    }
}