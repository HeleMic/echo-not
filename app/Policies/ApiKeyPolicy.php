<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ApiKey;

class ApiKeyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApiKey $apiKey): bool
    {
        return $this->isAdminOrOwner($user, $apiKey);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->applications()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApiKey $apiKey): bool
    {
        return $this->isAdminOrOwner($user, $apiKey);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApiKey $apiKey): bool
    {
        return $this->isAdminOrOwner($user, $apiKey);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ApiKey $apiKey): bool
    {
        return $this->isAdminOrOwner($user, $apiKey);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ApiKey $apiKey): bool
    {
        return $this->isAdminOrOwner($user, $apiKey);
    }

    /**
     * Determine whether the user can revoke the API key.
     */
    public function revoke(User $user, ApiKey $apiKey): bool
    {
        return $this->isAdminOrOwner($user, $apiKey);
    }

    /**
     * Check if the user is an admin or owns the API key.
     */
    private function isAdminOrOwner(User $user, ApiKey $apiKey): bool
    {
        return $user->isAdmin() || $this->ownsApiKey($user, $apiKey);
    }

    /**
     * Check if the user owns the API key.
     */
    private function ownsApiKey(User $user, ApiKey $apiKey): bool
    {
        // Commented out original line to avoid N+1 query issue
        // return $apiKey->application->user->is($user);
        return $apiKey->application->user_id === $user->id;
    }
}
