<?php

namespace App\Policies;

use App\Models\Planning;
use App\Models\User;

class PlanningPolicy
{
    /**
     * Determine whether the user can view any plannings index/metadata list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['docente', 'secretaria', 'vicerrectorado']);
    }

    /**
     * Determine whether the user can view a specific planning details, preview or comments.
     */
    public function view(User $user, Planning $planning): bool
    {
        if ($user->hasRole('docente')) {
            return $planning->user_id === $user->id;
        }

        if ($user->hasRole('vicerrectorado')) {
            return true;
        }

        // Secretaría cannot view academic detail views, previews, or comments.
        return false;
    }

    /**
     * Determine whether the user can download a planning document.
     */
    public function download(User $user, Planning $planning): bool
    {
        if ($user->hasRole('docente')) {
            return $planning->user_id === $user->id;
        }

        if ($user->hasRole('vicerrectorado')) {
            return true;
        }

        // Secretaría does NOT have permission to download physical planning documents.
        return false;
    }

    /**
     * Determine whether the user can create plannings.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('docente');
    }

    /**
     * Determine whether the user can update a planning.
     */
    public function update(User $user, Planning $planning): bool
    {
        return $user->hasRole('docente')
            && $planning->user_id === $user->id
            && in_array($planning->status, ['borrador', 'rechazado']);
    }

    /**
     * Determine whether the user can delete a planning draft.
     */
    public function delete(User $user, Planning $planning): bool
    {
        return $user->hasRole('docente')
            && $planning->user_id === $user->id
            && $planning->status === 'borrador';
    }

    /**
     * Determine whether the user can submit a planning for review.
     */
    public function submit(User $user, Planning $planning): bool
    {
        return $user->hasRole('docente')
            && $planning->user_id === $user->id
            && in_array($planning->status, ['borrador', 'rechazado']);
    }

    /**
     * Determine whether the user can view the review list.
     */
    public function review(User $user): bool
    {
        return $user->hasRole('vicerrectorado');
    }

    /**
     * Determine whether the user can approve a planning.
     */
    public function approve(User $user, Planning $planning): bool
    {
        return $user->hasRole('vicerrectorado') && $planning->status === 'revisión';
    }

    /**
     * Determine whether the user can reject a planning.
     */
    public function reject(User $user, Planning $planning): bool
    {
        return $user->hasRole('vicerrectorado') && $planning->status === 'revisión';
    }

    /**
     * Determine whether the user can comment on a planning.
     */
    public function comment(User $user, Planning $planning): bool
    {
        if ($user->hasRole('docente')) {
            return $planning->user_id === $user->id;
        }

        if ($user->hasRole('vicerrectorado')) {
            return true;
        }

        // Secretaría CANNOT comment on academic plannings.
        return false;
    }
}
