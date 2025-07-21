<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // List of allowed origins
        $allowedOrigins = [
            'http://localhost:3000',
            'https://localhost:3000',
            'https://coyokid.abbangles.com',
            'http://coyokid.abbangles.com:3000',
        ];

        // Get the origin from the request
        $origin = $request->header('Origin');

        // Check if the request's origin is allowed
        $corsOrigin = in_array($origin, $allowedOrigins) ? $origin : null;

        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            $response = response()->json([], 204);
        } else {
            $response = $next($request);
        }

        // Set CORS headers if the origin is allowed
        if ($corsOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $corsOrigin)
                ->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
                // Add headers needed for file uploads
                ->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, mytoken, branchId, companyId, X-Requested-With, X-CSRF-TOKEN')
                ->set('Access-Control-Allow-Credentials', 'true')
                // Important for file uploads with progress
                ->set('Access-Control-Expose-Headers', 'Content-Disposition');
        }

        return $response;
    }
}