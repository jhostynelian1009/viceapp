<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    public function markAsRead(DatabaseNotification $notification)
    {
        Gate::authorize('markAsRead', $notification);

        $notification->markAsRead();

        return back();
    }
}
