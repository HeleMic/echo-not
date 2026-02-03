<?php

namespace App\DTO\ApiKeys;

use App\DTO\FormRequestDTO;

class UpdateApiKeyDTO
{
    use FormRequestDTO;

    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $name,
    ) {
        //
    }

    /**
     * Returns a new class instance from an array.
     *
     * @param  array $data
     * @return \App\DTO\ApiKeys\UpdateApiKeyDTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
        );
    }
}