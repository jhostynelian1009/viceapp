<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Planning;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view any comments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['docente', 'vicerrectorado']);
    }

    /**
     * Determine whether the user can create comments on a given planning.
     */
    public function create(User $user, Planning $planning): bool
    {
        if ($user->hasRole('docente')) {
            return $planning->user_id === $user->id;
        }

        if ($user->hasRole('vicerrectorado')) {
            return true;
        }

        // Secretaría cannot comment on plannings.
        return false;
    }

    /**
     * Determine whether the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $user->hasRole('vicerrectorado');
    }
}
