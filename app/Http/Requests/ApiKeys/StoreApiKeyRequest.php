<?php

namespace App\Http\Requests\ApiKeys;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // authorization is handled in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'application_id' => ['required', 'uuid', 'exists:applications,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:api_keys',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
                'before_or_equal:' . now()->addSeconds((int) config('api-keys.max_duration'))->toDateTimeString()
            ],
        ];
    }
}
