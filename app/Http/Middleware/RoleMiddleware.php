<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        foreach ($roles as $role) {
            $subRoles = explode('|', $role);
            foreach ($subRoles as $r) {
                if ($user->hasRole(trim($r))) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Acción no autorizada.');
    }
}
