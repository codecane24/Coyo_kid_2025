<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {   
        // Check if the 'fyid' session key does not exist
        if (!$request->session()->has('fyid')) {
            // If the user is currently authenticated, log them out
            if ($request->user()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to the admin login route
                return redirect()->route('admin.login');
            } else {
                // If the user is not authenticated and 'fyid' is missing,
                // they should be redirected to the admin login anyway.
                return redirect()->route('admin.login');
            }
        }

        if (! $request->expectsJson()) {
            return route('admin.login');
        }
    }
}
