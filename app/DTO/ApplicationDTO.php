<?php

namespace App\DTO;

use App\Http\Requests\Applications\StoreApplicationRequest;
use App\Http\Requests\Applications\UpdateApplicationRequest;

final class ApplicationDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $userId,
        public readonly string $name,
        public readonly ?string $description,
    ) {
        //
    }

    /**
     * Returns a new class instance with the given user ID.
     *
     * @param  string $userId
     * @return \App\DTO\ApplicationDTO
     */
    public function withUserId(string $userId): self
    {
        return new self(
            id: $this->id,
            userId: $userId,
            name: $this->name,
            description: $this->description,
        );
    }

    /**
     * Returns a new class instance from `StoreApplicationRequest`.
     *
     * @param  \App\Http\Requests\Applications\StoreApplicationRequest $request
     * @return \App\DTO\ApplicationDTO
     */
    public static function fromStoreRequest(StoreApplicationRequest $request): self
    {
        $validated = $request->validated();
        return self::fromArray($validated);
    }

    /**
     * Returns a new class instance from `UpdateApplicationRequest`.
     *
     * @param  \App\Http\Requests\Applications\UpdateApplicationRequest $request
     * @return \App\DTO\ApplicationDTO
     */
    public static function fromUpdateRequest(UpdateApplicationRequest $request): self
    {
        $validated = $request->validated();
        return self::fromArray($validated);
    }

    /**
     * Returns a new class instance from an array.
     *
     * @param  array $data
     * @return \App\DTO\ApplicationDTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['userId'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
        );
    }
}
