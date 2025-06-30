<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckFirstLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated via 'client' guard
        if (Auth::guard('client')->check()) {
            $user = Auth::guard('client')->user();
            session()->put('is_first_login', true);
            // If it's the first login and the user is NOT already on the dashboard
            if ($user->is_first_login == 0) {
                return redirect('/user/change-my-password')->with('showPasswordPopup', true);
            }
        }

        return $next($request);
    }
}
