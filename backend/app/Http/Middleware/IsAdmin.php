<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()) {
            return redirect()->route('admin.login');
        }

        // Check if branch_id is missing in session
        if (!session()->has('branch_id')) {
            // Optional: log or flash message before redirect
            session()->flash('error', 'Branch information missing. Please log in again.');
            return redirect()->route('admin.login');
        }

        // Allow only specific user types
        if (in_array($request->user()->type, ['admin', 'local_admin', 'user', 'superadmin'])) {
            return $next($request);
        }

        return redirect()->route(getDashboardRouteName());
    }

}
