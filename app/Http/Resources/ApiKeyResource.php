<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiKeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'applicationId' => $this->application_id,
            'name' => $this->name,
            'key' => $this->when($request->routeIs('api-keys.store'), $this->key, config('api-keys.hidden_placeholder')),
            'lastUsedAt' => $this->last_used_at,
            'expiresAt' => $this->expires_at,
            'revokedAt' => $this->revoked_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
