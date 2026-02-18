<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        if (!in_array($user->type, $types)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. User type not authorized.',
            ], 403);
        }

        return $next($request);
    }
}