<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Application;

class ApplicationPolicy
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
    public function view(User $user, Application $application): bool
    {
        return $this->isAdminOrOwner($user, $application);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Application $application): bool
    {
        return $this->isAdminOrOwner($user, $application);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Application $application): bool
    {
        return $this->isAdminOrOwner($user, $application);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Application $application): bool
    {
        return $this->isAdminOrOwner($user, $application);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Application $application): bool
    {
        return $this->isAdminOrOwner($user, $application);
    }

    /**
     * Check if the user is an admin or owns the application.
     */
    private function isAdminOrOwner(User $user, Application $application): bool
    {
        return $user->isAdmin() || $this->ownsApplication($user, $application);
    }

    /**
     * Check if the user owns the application.
     */
    private function ownsApplication(User $user, Application $application): bool
    {
        return $application->user_id === $user->id;
    }
}
