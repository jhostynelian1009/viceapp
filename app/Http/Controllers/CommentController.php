<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Planning;
use App\Notifications\CommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Planning $planning)
    {
        Gate::authorize('create', [Comment::class, $planning]);

        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $planning->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        if ($planning->user_id !== Auth::id()) {
            $planning->user->notify(new CommentNotification($comment));
        }

        return back()->with('success', 'Comentario añadido exitosamente.');
    }

    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comentario eliminado exitosamente.');
    }
}
