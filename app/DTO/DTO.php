<?php

namespace App\DTO;

class DTO
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Returns a new class instance from an array.
     *
     * @param  array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static();
    }
}