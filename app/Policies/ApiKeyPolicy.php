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
        return $user->isAdmin() || $apiKey->application->user->is($user);
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
        return $user->isAdmin() || $apiKey->application->user->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApiKey $apiKey): bool
    {
        return $user->isAdmin() || $apiKey->application->user->is($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ApiKey $apiKey): bool
    {
        return $user->isAdmin() || $apiKey->application->user->is($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ApiKey $apiKey): bool
    {
        return $user->isAdmin() || $apiKey->application->user->is($user);
    }

    /**
     * Determine whether the user can revoke the API key.
     */
    public function revoke(User $user, ApiKey $apiKey): bool
    {
        return $user->isAdmin() || $apiKey->application->user->is($user);
    }
}
