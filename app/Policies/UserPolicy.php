<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the actor can view any users.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasAnyRole(['secretaria', 'vicerrectorado']);
    }

    /**
     * Determine whether the actor can view a user.
     */
    public function view(User $actor, User $target): bool
    {
        return $actor->hasAnyRole(['secretaria', 'vicerrectorado']);
    }

    /**
     * Determine whether the actor can create users.
     */
    public function create(User $actor): bool
    {
        return $actor->hasAnyRole(['secretaria', 'vicerrectorado']);
    }

    /**
     * Determine whether the actor can update a user.
     */
    public function update(User $actor, User $target): bool
    {
        return $actor->hasAnyRole(['secretaria', 'vicerrectorado']);
    }

    /**
     * Determine whether the actor can deactivate/activate a target user.
     */
    public function toggleActive(User $actor, User $target): bool
    {
        if (! $actor->hasAnyRole(['secretaria', 'vicerrectorado'])) {
            return false;
        }

        // Self-deactivation is prohibited
        if ($actor->id === $target->id && $target->is_active) {
            return false;
        }

        // If target is being deactivated, check if they are the last active user in a critical role
        if ($target->is_active) {
            foreach (['secretaria', 'vicerrectorado'] as $criticalRole) {
                if ($target->hasRole($criticalRole)) {
                    $activeCount = User::role($criticalRole)->where('is_active', true)->count();
                    if ($activeCount <= 1) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Physical deletion is disabled to preserve institutional history.
     */
    public function delete(User $actor, User $target): bool
    {
        return false;
    }
}
