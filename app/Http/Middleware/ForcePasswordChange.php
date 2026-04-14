<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If not logged in, let Laravel handle it
        if (!$user) {
            return $next($request);
        }

        // Allow access to logout + change password routes
        if (
            $user->must_change_password &&
            !$request->is('change-password') &&
            !$request->is('logout')
        ) {
            return redirect()->route('password.change.form');
        }

        return $next($request);
    }
}