<?php

// app/Http/Middleware/Cors.php
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
            'https://coyokid.abbangles.com/storage/uploads',
            'http://coyokid.abbangles.com/storage/uploads',
        ];

        // Get the origin from the request
        $origin = $request->header('Origin');

        // Check if the request's origin is allowed
        $corsOrigin = in_array($origin, $allowedOrigins) ? $origin : null;

        // Build the response
        $response = $next($request);

        // Set CORS headers if the origin is allowed
        if ($corsOrigin) {
            
            $response->header('Access-Control-Allow-Origin', $corsOrigin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization,mytoken,branchId,companyId')
                    ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}