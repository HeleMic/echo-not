<?php

namespace App\DTO;

use Illuminate\Foundation\Http\FormRequest;

trait FormRequestDTO
{
    /**
     * Returns a new class instance from a form request.
     *
     * @param  \Illuminate\Foundation\Http\FormRequest $request
     * @return static
     */
    public static function fromFormRequest(FormRequest $request): static
    {
        $validated = $request->validated();
        return static::fromArray($validated);
    }
}