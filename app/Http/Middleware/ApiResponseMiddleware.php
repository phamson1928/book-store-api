<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Nếu response đã là JSON, không cần format lại
        if ($response->headers->get('Content-Type') === 'application/json') {
            return $response;
        }

        // Format response thành JSON nếu cần
        if ($request->expectsJson()) {
            $data = $response->getContent();
            
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }

        return $response;
    }
} 