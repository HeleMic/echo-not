<?php

namespace App\DTO\ApiKeys;

use App\DTO\FormRequestDTO;
use Carbon\CarbonInterface;

class StoreApiKeyDTO
{
    use FormRequestDTO;

    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $applicationId,
        public readonly string $name,
        public readonly ?string $expiresAt,
    ) {
        //
    }

    /**
     * Returns a new class instance with the given expiration date.
     *
     * @param  string $expiresAt
     * @return \App\DTO\ApiKeys\StoreApiKeyDTO
     */
    public function withExpiresAt(string $expiresAt): self
    {
        return new self(
            applicationId: $this->applicationId,
            name: $this->name,
            expiresAt: $expiresAt,
        );
    }

    /**
     * Returns a new class instance from an array.
     *
     * @param  array $data
     * @return \App\DTO\ApiKeys\StoreApiKeyDTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            applicationId: $data['application_id'],
            name: $data['name'],
            expiresAt: $data['expires_at'] ?? null,
        );
    }
}