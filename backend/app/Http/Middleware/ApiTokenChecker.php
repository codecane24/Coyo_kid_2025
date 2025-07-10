<?php

namespace App\Http\Middleware;

use App\Models\DeviceToken;
use Closure;
use Illuminate\Support\Facades\Auth;

class ApiTokenChecker
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
        $valid_types = ['superadmin', 'admin', 'teacher', 'parent', 'student'];
        $token = get_header_auth_token();

        if ($token) {
            $is_login = DeviceToken::where('token', $token)
                ->with('user')
                ->has('user')
                ->first();

            if ($is_login) {
                $user = $is_login->user;

                if ($user->status === "active") {
                    if (in_array($user->type, $valid_types)) {
                        Auth::loginUsingId($user->id);
                        return $next($request);
                    } else {
                        return send_response(401, __('api.err_not_allowed_task'));
                    }
                } else {
                    return send_response(401, __('api.err_account_ban'));
                }
            }
        }

        return send_response(401, __('api.err_please_login'));
    }
}

