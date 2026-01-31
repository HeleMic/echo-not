<?php

namespace App\Services;

use App\DTO\NotificationDTO;
use App\Models\Notification;

class NotificationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    /**
     * Create a new notification.
     *
     * @param  \App\DTO\NotificationDTO $dto
     * @return \App\Models\Notification
     */
    public function create(NotificationDTO $dto): Notification
    {
        return Notification::create([
            'user_id' => $dto->userId,
            'application_id' => $dto->applicationId,
            'type' => $dto->type,
            'title' => $dto->title,
            'html' => $dto->html,
            'text' => $dto->text,
            'recipient' => $dto->recipient,
            'options' => $dto->options,
        ]);
    }
}