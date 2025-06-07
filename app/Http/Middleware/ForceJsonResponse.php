<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        
        $response = $next($request);

        if ($response->getStatusCode() === 401 && !$request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $response;
    }
}
