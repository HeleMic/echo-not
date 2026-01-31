<?php

namespace App\DTO;

use App\Models\User;
use App\Http\Requests\StoreNotificationRequest;

class NotificationDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $userId,
        public readonly ?string $applicationId,
        public readonly string $type,
        public readonly string $title,
        public readonly string $html,
        public readonly string $text,
        public readonly array $recipient,
        public readonly array $options,
    ) {
        //
    }

    public function with(array $attributes): self
    {
        return new self(
            id: $attributes['id'] ?? $this->id,
            userId: $attributes['user_id'] ?? $this->userId,
            applicationId: $attributes['application_id'] ?? $this->applicationId,
            type: $attributes['type'] ?? $this->type,
            title: $attributes['title'] ?? $this->title,
            html: $attributes['html'] ?? $this->html,
            text: $attributes['text'] ?? $this->text,
            recipient: $attributes['recipient'] ?? $this->recipient,
            options: $attributes['options'] ?? $this->options,
        );
    }

    /**
     * Returns a new class instance with the given user ID.
     *
     * @param  string $userId
     * @return \App\DTO\NotificationDTO
     */
    public function withUser(string $userId): self
    {
        return new self(
            id: $this->id,
            userId: $userId,
            applicationId: $this->applicationId,
            type: $this->type,
            title: $this->title,
            html: $this->html,
            text: $this->text,
            recipient: $this->recipient,
            options: $this->options,
        );
    }

    /**
     * Returns a new class instance from `StoreApplicationRequest`.
     *
     * @param  \App\Http\Requests\StoreNotificationRequest $request
     * @return \App\DTO\NotificationDTO
     */
    public static function fromStoreRequest(StoreNotificationRequest $request): self
    {
        $validated = $request->validated();
        return self::fromArray($validated);
    }

    /**
     * Returns a new class instance from an array.
     *
     * @param  array $data
     * @return \App\DTO\NotificationDTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            applicationId: $data['application_id'],
            type: $data['type'],
            title: $data['title'],
            html: $data['html'],
            text: $data['text'],
            recipient: $data['recipient'],
            options: $data['options'] ?? [],
        );
    }
}