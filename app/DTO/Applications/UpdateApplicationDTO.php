<?php

namespace App\DTO\Applications;

readonly class UpdateApplicationDTO
{
    use \App\DTO\FormRequestDTO;

    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {
        //
    }

    /**
     * Returns a new class instance from an array.
     *
     * @param  array $data
     * @return \App\DTO\Applications\UpdateApplicationDTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
        );
    }
}
