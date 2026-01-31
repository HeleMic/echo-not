<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
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
            'type' => ['required', 'string', 'max:100', 'in:email,push,sms'],
            'title' => ['required', 'string', 'max:255'],
            'html' => ['nullable', 'string'],
            'text' => ['required', 'string'],
            'recipient' => ['required', 'array'],
            'recipient.email' => ['required_if:type,email', 'email', 'max:255'],
            'recipient.phone' => ['required_if:type,sms', 'string', 'max:15'],
            'recipient.entity_id' => ['required_if:type,push', 'string', 'max:100'],
            'options' => ['nullable', 'array'],
            'options.scheduled_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'options.retry' => ['nullable', 'integer', 'min:0', 'max:10'],
            'options.priority' => ['nullable', 'string', 'in:low,normal,high'],
        ];
    }
}
