<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view reports.
     */
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['secretaria', 'vicerrectorado']);
    }

    /**
     * Determine whether the user can export reports (PDF/Word).
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['secretaria', 'vicerrectorado']);
    }
}
