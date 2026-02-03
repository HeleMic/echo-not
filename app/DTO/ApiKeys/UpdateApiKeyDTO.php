<?php

namespace App\DTO\ApiKeys;

readonly class UpdateApiKeyDTO
{
    use \App\DTO\FormRequestDTO;

    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
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