<?php

namespace App\DTO\Applications;

readonly class StoreApplicationDTO
{
    use \App\DTO\FormRequestDTO;

    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $userId = null,
    ) {
        //
    }

    /**
     * Returns a new class instance with the given user ID.
     *
     * @param  string $userId
     * @return \App\DTO\Applications\StoreApplicationDTO
     */
    public function withUserId(string $userId): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            userId: $userId,
        );
    }

    /**
     * Returns a new class instance from an array.
     *
     * @param  array $data
     * @return \App\DTO\Applications\StoreApplicationDTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            userId: $data['user_id'] ?? null,
        );
    }
}
