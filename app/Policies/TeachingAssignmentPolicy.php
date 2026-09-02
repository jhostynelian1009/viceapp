<?php

namespace App\Policies;

use App\Models\User;

class TeachingAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['secretaria', 'vicerrectorado']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['secretaria', 'vicerrectorado']);
    }

    public function update(User $user): bool
    {
        return $user->hasAnyRole(['secretaria', 'vicerrectorado']);
    }

    public function delete(User $user): bool
    {
        return $user->hasAnyRole(['secretaria', 'vicerrectorado']);
    }
}
