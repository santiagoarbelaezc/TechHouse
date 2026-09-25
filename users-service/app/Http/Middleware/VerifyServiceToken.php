<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyServiceToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Service-Token');
        $expected = config('services.internal_token');

        if (!$token || !$expected || $token !== $expected) {
            return response()->json([
                'message' => 'Invalid or missing service token',
            ], 401);
        }

        return $next($request);
    }
}
